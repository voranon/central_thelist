<?php

//exception codes 3200-3299

class thelist_cisco_command_removetransitvlans implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_vlan_ids;
	private $_override_management_vlan=0;
	
	public function __construct($device, $vlan_ids, $override_management_vlan=0)
	{
		//this will not reset the vlan database
		//this will only remove the specified vlans from the database
		
		//$vlan_ids
		//array	of vlan ids to be removed
		
		//$override_management_vlan
		//string 0 or 1 // allows the user to kill off management functions on the interface
		
		$this->_device 									= $device;
		$this->_vlan_ids								= $vlan_ids;
		$this->_override_management_vlan				= $override_management_vlan;

	}
	
	public function execute()
	{		
		//get the current config
		$transit_vlans	= new Thelist_Cisco_command_gettransitvlans($this->_device);
		$current_transit_vlans	= $transit_vlans->get_transit_vlans();

		if ($current_transit_vlans != null) {
			
			foreach($current_transit_vlans as $current_transit_vlan) {
				
				foreach ($this->_vlan_ids as $remove_vlan) {
					
					if ($current_transit_vlan == $remove_vlan) {
						$vlans_to_remove[]	= $remove_vlan;
					}
				}
			}

			if (isset($vlans_to_remove)) {
				
				//get the running software version
				$get_software	= new Thelist_Cisco_command_getsoftware($this->_device);
				$get_software->execute();
				$running_version	= $get_software->get_running_software_version();
				
				//get the root folder
				$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
				$get_connection_root_folder->execute();

				if ($running_version == '12.2(44)SE6') {
					//3550 series
					$this->_device->execute_command("configure terminal");
	
				} elseif ($running_version == '12.0(5)WC17') {
					//3500 series switch
					$this->_device->execute_command("vlan database");
	
				} else {
					throw new exception("software is not known to this remove class, so we cannot remove transit vlans", 3200);
				}
				
				//command is the same regardless of vlan database or terminal config
				//lets delete the vlans
				foreach($vlans_to_remove as $del_transit_vlan) {
					
					if ($del_transit_vlan != 1000) {
						$this->_device->execute_command("no vlan ".$del_transit_vlan."");
					} else {
						
						//if this is vlan 1000 we are removing
						if ($this->_override_management_vlan == 1) {
							$this->_device->execute_command("no vlan ".$del_transit_vlan."");
						} else {
							//bad bad bad
							throw new exception("you are trying to remove management transit vlan on ".$this->_device->get_fqdn()." without override, bad,bad,bad ", 3202);
						}
					}
				}
	
				//back or apply
				if ($running_version == '12.2(44)SE6') {
					//3550 series
					$this->_device->execute_command("end");
				
				} elseif ($running_version == '12.0(5)WC17') {
					//3500 series switch
					$this->_device->execute_command("apply");
					$this->_device->execute_command("exit");
				}
				
				//now validate
				$still_active_transit_vlans	= $transit_vlans->get_transit_vlans();
				
				if ($still_active_transit_vlans != null) {
						
					foreach($still_active_transit_vlans as $current_transit_vlan) {
				
						foreach ($vlans_to_remove as $removed_vlan) {
								
							//cant get rid of vlan 1
							if (
							$current_transit_vlan == $removed_vlan
							&& $current_transit_vlan != 1
							) {
								throw new exception("we tried to remove transit vlans, but failed", 3201);
							}
						}
					}
				} else {
					//unlikely, but possible that we removed all vlans.
				}
			}
			
		} else {
			//there are no transit vlans on the switch so we do nothing since the vlan we are supposed to remove does not exist
		}
	}
}