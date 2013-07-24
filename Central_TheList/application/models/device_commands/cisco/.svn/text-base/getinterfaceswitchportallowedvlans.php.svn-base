<?php

//exception codes 11300-11399

class thelist_cisco_command_getinterfaceswitchportallowedvlans implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	
	private $_allowed_vlans=null;
	
	public function __construct($device, $interface)
	{
		//$interface
		//object	= interface_obj
		//string	= interface name
		
		$this->_device 					= $device;
		$this->_interface 				= $interface;
	}
	
	public function execute()
	{
		//the $_allowed_vlans is an array 
		//if the class is not reinstanciated this keps building up we need to reset it
		$this->_allowed_vlans = null;
		
		//get the root folder
		$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
		$get_connection_root_folder->execute();

		if (is_object($this->_interface)) {
			$interface_name		= $this->_interface->get_if_name();
		} else {
			$interface_name		= $this->_interface;
		}

		$device_reply1 = $this->_device->execute_command("show running-config interface ".$interface_name."");
		
		//check if command failed
		if (preg_match("/Current configuration/", $device_reply1->get_message())) {
			
			//switch port allow vlan id to trunk
			if (preg_match("/switchport trunk allowed vlan (.*)\r/", $device_reply1->get_message(), $result)) {
			
				if (isset($result['1'])) {
			
					//3550 switches can have value 'none'
					if ($result['1'] != 'none') {
			
						if (preg_match("/,/", $result['1'])) {
			
							$groups = explode(',', $result['1']);
			
							foreach($groups as $group) {
									
								if (preg_match("/-/", $group)) {
									$range = explode('-', $group);
			
									foreach(range($range['0'], $range['1']) as $single_vlan_id){
											
										$this->_allowed_vlans[] = $single_vlan_id;
									}
			
								} else {
									$this->_allowed_vlans[] = $group;
								}
							}
			
						} else {
							$this->_allowed_vlans[] = $result['1'];
						}
					} else {
							
						//return nothing since no vlans are allowed
						$this->_allowed_vlans = null;
					}
				} else {
			
					//return nothing since no vlans are allowed
					$this->_allowed_vlans = null;
				}
				
			} else {
				//if the setting is not there it means all are allowed, but it only matters if the port is in trunk or dynamic
				$get_interface_switch_port_mode		= new Thelist_Cisco_command_getinterfaceswitchportmode($this->_device, $interface_name);
				$current_switch_port_mode			= $get_interface_switch_port_mode->get_configured_switch_port_mode();
				
				if ($current_switch_port_mode == 'trunk') {
					
					//we will have to change this piece of the code to consider the model number, in order to be more acurate once we introduce 2960 switches
					//even the 3550 switches go 1-4096, but for now this is an easy solution
					$this->_allowed_vlans	= range(1,1001);
				}
				
			}

		} else {
			throw new exception('interface configured native vlan could not be determined for interface on device', 11300);
		}
	}
	
	public function get_allowed_vlans()
	{
		//used for validation, must be a fresh result
		$this->execute();
		return $this->_allowed_vlans;
	}
}