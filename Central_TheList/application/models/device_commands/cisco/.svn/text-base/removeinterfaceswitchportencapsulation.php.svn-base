<?php

//exception codes 11000-11099

class thelist_cisco_command_removeinterfaceswitchportencapsulation implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	
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
		if (is_object($this->_interface)) {
			$interface_name			= $this->_interface->get_if_name();
			$get_interface_encapsulation	= new Thelist_Cisco_command_getinterfaceswitchportencapsulation($this->_device, $this->_interface);
		} else {
			$interface_name			= $this->_interface;
			$get_interface_encapsulation	= new Thelist_Cisco_command_getinterfaceswitchportencapsulation($this->_device, $interface_name);
		}
		
		$current_trunk_encapsulation			= $get_interface_encapsulation->get_configured_trunk_encapsulation();
		
		if ($current_trunk_encapsulation != null) {

			//we cannot remove the encapsulation if the port is in trunk mode
			//we can remove it if the port is access or dynamic, but not in  trunk
			//this is where the object model comes in handy
			$get_interface_switch_port_mode		= new Thelist_Cisco_command_getinterfaceswitchportmode($this->_device, $interface_name);
			$current_switch_port_mode			= $get_interface_switch_port_mode->get_configured_switch_port_mode();

			if ($current_switch_port_mode == 'trunk') {
				
				//additional help for object model
				if (is_object($this->_interface)) {
					
					$current_mode	= $this->_interface->get_interface_configuration(23);
					
					if ($current_mode != false) {

						if ($current_mode['0']->get_mapped_configuration_value_1() != 'trunk') {
							
							//there is a simple ordering issue and the remove encapsulation is just being issued before the mode is changed
							//so we fix that, before implementing the no encapsulation command
							$set_interface_mode	= new Thelist_Cisco_command_setinterfaceswitchportmode($this->_device, $this->_interface, $current_mode['0']->get_mapped_configuration_value_1());
							$set_interface_mode->execute();
							
						} else {
							//we have a logic error in the input area, it should not be possible to have a 
							//trunk port with no encapsulation
							throw new exception('interface trunk encapsulation cannot be remove because port is in trunk mode ', 11002);
						}
						
					} else {
						//we have a logic error in the input area, it should not be possible to have a 
						//port that does not have a mode
						throw new exception('interface trunk encapsulation cannot be remove because port is in trunk mode ', 11003);
					}

				} else {
					//string model fails
					throw new exception('interface trunk encapsulation cannot be remove because port is in trunk mode', 11001);
				}
			}

			//get the root folder
			$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
			$get_connection_root_folder->execute();
			
			//enter config mode
			$device_reply = $this->_device->execute_command("configure terminal");
			$this->_device->execute_command("interface ".$interface_name."");
			$this->_device->execute_command("no switchport trunk encapsulation ".$current_trunk_encapsulation."");
			$this->_device->execute_command('end');

			$verify			= $get_interface_encapsulation->get_configured_trunk_encapsulation();
			
			if ($verify != null) {
				throw new exception('interface trunk encapsulation was not removed', 11000);
			}
		}
	}
}