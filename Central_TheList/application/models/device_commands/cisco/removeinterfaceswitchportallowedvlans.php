<?php

//exception codes 3500-3599

class thelist_cisco_command_removeinterfaceswitchportallowedvlans implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	private $_vlan_ids;
	private $_override_remove_managment_vlan=0;
	
	public function __construct($device, $interface, $vlan_ids, $override_remove_managment_vlan=0)
	{
		//this function does NOT override the entire pool
		//it simply removes the provided vlans from the allowed list
		
		//$interface
		//object	= interface_obj
		//string	= ['interface_name']
		
		//$vlan_ids
		//array	of vlan ids to be removed
		
		$this->_device 							= $device;
		$this->_interface 						= $interface;
		$this->_vlan_ids		 				= $vlan_ids;
		$this->_override_remove_managment_vlan	= $override_remove_managment_vlan;
	}
	
	public function execute()
	{		
		if (is_object($this->_interface)) {
			$interface_name					= $this->_interface->get_if_name();
			$get_interface_allowed_vlans	= new Thelist_Cisco_command_getinterfaceswitchportallowedvlans($this->_device, $this->_interface);
		} else {
			$interface_name					= $this->_interface;
			$get_interface_allowed_vlans	= new Thelist_Cisco_command_getinterfaceswitchportallowedvlans($this->_device, $interface_name);
		}
		
		//currently allowed vlans
		$currently_allowed		= $get_interface_allowed_vlans->get_allowed_vlans();
		
		//remove all the ones that are already denied from the pool to push
		if ($currently_allowed != null) {
			
			foreach ($currently_allowed as $already_allowed) {
				
				foreach ($this->_vlan_ids as $new_remove_index => $new_remove) {
					
					if ($already_allowed == $new_remove) {
						$vlans_current_allowed_to_be_removed[]	= $new_remove;
					}
				}
			}
		}

		if (isset($vlans_current_allowed_to_be_removed)) {
			
			//get the root folder
			$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
			$get_connection_root_folder->execute();
			
			$this->_device->execute_command("configure terminal");
			$this->_device->execute_command("interface ".$interface_name."");
			
			//remove each remaining vlan id
			foreach ($vlans_current_allowed_to_be_removed as $remove_vlan_id) {
				
				if ($remove_vlan_id != 1000) {
					$this->_device->execute_command("switchport trunk allowed vlan remove ".$remove_vlan_id."");
				} else {
					
					//get the name iof the management interface, if we i.e. remove vlan 1000 from the management
					//interface then we lose connectivity, that requires special validation
					$management_interface = new Thelist_Cisco_command_getmanagementinterfacename($this->_device);
					$management_interface_name = $management_interface->get_management_interface_name();
					
					if ($interface_name == $management_interface_name) {
						
						if ($this->_override_remove_managment_vlan == 1) {
							$this->_device->execute_command("switchport trunk allowed vlan remove ".$remove_vlan_id."");
						} else {
							throw new exception("you are trying to remove management from allowed vlan list on ".$this->_device->get_fqdn()." without override, bad,bad,bad ", 3502);
						}
					}
				}
			}

			$this->_device->execute_command("end");

			$verify			= $get_interface_allowed_vlans->get_allowed_vlans();
			
			//check that all that should have been removed have been
			if ($verify != null) {
					
				foreach ($verify as $already_allowed) {
					
					foreach ($this->_vlan_ids as $remove_index => $remove) {
						
						if ($already_allowed == $remove) {
							$vlans_still_not_removed[]	= $remove;
						}
					}
				}
	
				if (isset($vlans_still_not_removed)) {
					throw new exception('we remove some vlans from the allowed vlans list of a switch interface, but not all where removed sucessfully', 3501);
				}
				
			} else {
				//this is ok, maybe we just removed the last vlan and now nothing is allowed
			}
		}
	}
}