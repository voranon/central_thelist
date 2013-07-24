<?php

//exception codes 4200-4299

class thelist_cisco_command_addinterfaceswitchportallowedvlans implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	private $_vlan_ids;
	
	public function __construct($device, $interface, $vlan_ids)
	{
		//this function does NOT override the entire pool
		//it simply adds to the existing vlans that are allowed
		
		//$interface
		//object	= interface_obj
		//string	= ['interface_name']
		
		//$vlan_ids
		//array	of vlan ids to be added
		
		$this->_device 					= $device;
		$this->_interface 				= $interface;
		$this->_vlan_ids		 		= $vlan_ids;
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

		//remove all the once that are already allowed
		if ($currently_allowed != null) {
			
			foreach ($currently_allowed as $already_allowed) {
				
				foreach ($this->_vlan_ids as $new_allow_index => $new_allow) {
					
					if ($already_allowed == $new_allow) {
						unset($this->_vlan_ids[$new_allow_index]);
					}
				}
			}
		}
		
		$vlans_left_to_add	= count($this->_vlan_ids);

		if ($vlans_left_to_add > 0) {
			
			//get the root folder
			$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
			$get_connection_root_folder->execute();
			
			$this->_device->execute_command("configure terminal");
			$this->_device->execute_command("interface ".$interface_name."");
			
			//add each remaining vlan id
			foreach ($this->_vlan_ids as $add_vlan_id) {
				$this->_device->execute_command("switchport trunk allowed vlan add ".$add_vlan_id."");
			}

			$this->_device->execute_command("end");

			$verify			= $get_interface_allowed_vlans->get_allowed_vlans();
			
			//remove all the once that are already allowed
			if ($verify != null) {
					
				foreach ($verify as $now_allowed) {
			
					foreach ($this->_vlan_ids as $new_allow_index => $new_allow) {
							
						if ($now_allowed == $new_allow) {
							unset($this->_vlan_ids[$new_allow_index]);
						}
					}
				}
				
				//now all should be live and allowed
				$after_vlans_left_to_add	= count($this->_vlan_ids);
				
				if ($after_vlans_left_to_add > 0) {
					throw new exception('we added allowed vlans to a switch interface, but not all where sucessfully added', 4201);
				}
				
			} else {
				throw new exception('we added allowed vlans to a switch interface, but now nothing is allowed, its a problem', 4200);
			}
		}
	}
}