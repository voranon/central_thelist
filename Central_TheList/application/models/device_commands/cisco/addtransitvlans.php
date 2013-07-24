<?php

//exception codes 3100-3199

class thelist_cisco_command_addtransitvlans implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_vlan_ids;
	
	public function __construct($device, $vlan_ids)
	{
		//this will not reset the vlan database
		//this will only add the specified vlans to the database
		
		//$vlan_ids
		//array	of vlan ids to be added
		
		$this->_device 					= $device;
		$this->_vlan_ids 				= $vlan_ids;

	}
	
	public function execute()
	{		
		//get the current config
		$transit_vlans	= new Thelist_Cisco_command_gettransitvlans($this->_device);
		$current_transit_vlans	= $transit_vlans->get_transit_vlans();
		
		if ($current_transit_vlans != null) {
			
			foreach($current_transit_vlans as $current_transit_vlan) {
				
				foreach ($this->_vlan_ids as $add_vlan_index => $add_vlan) {
					
					if ($current_transit_vlan == $add_vlan) {
						//it already exists, so no need to set it up
						unset($this->_vlan_ids[$add_vlan_index]);
					}
				}
			}
		}
		
		//any vlans left
		$vlans_left = count($this->_vlan_ids);

		if ($vlans_left > 0) {
			
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
				throw new exception("software is not known to this remove class, so we cannot add transit vlans", 3100);
			}
			
			//command is the same regardless of vlan database or terminal config
			//lets add the vlans
			foreach($this->_vlan_ids as $add_transit_vlan) {
				$this->_device->execute_command("vlan ".$add_transit_vlan."");
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
			$active_transit_vlans	= $transit_vlans->get_transit_vlans();
			
			if ($active_transit_vlans != null) {
					
				foreach($active_transit_vlans as $current_transit_vlan) {
			
					foreach ($this->_vlan_ids as $vlan_index => $vlan_to_add) {
							
						if ($current_transit_vlan == $vlan_to_add) {
							//it already exists, so check that one off
							unset($this->_vlan_ids[$vlan_index]);
						}
					}
				}
				
				//any vlans left
				$missing = count($this->_vlan_ids);
				
				if ($missing > 0) {
					throw new exception("we where adding transit vlans. but once we where there was still vlans that had not been setup", 3102);
				}
				
			} else {
				throw new exception("we where adding transit vlans. but once we where done there were no transit vlans at all", 3101);
			}
		}
	}
}