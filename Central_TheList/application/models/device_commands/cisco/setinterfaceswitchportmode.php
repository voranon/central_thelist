<?php

//exception codes 10600-10699

class thelist_cisco_command_setinterfaceswitchportmode implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	private $_switch_port_mode;
	
	public function __construct($device, $interface, $switch_port_mode)
	{
		//$interface
		//object	= interface_obj
		//string	= interface name
		
		//$switch_port_mode
		//object	= new switch_port_mode string
		//string	= new switch_port_mode string
		
		$this->_device 					= $device;
		$this->_interface 				= $interface;
		$this->_switch_port_mode		= $switch_port_mode;
	}
	
	public function execute()
	{
		if (is_object($this->_interface)) {
			$interface_name			= $this->_interface->get_if_name();
			$get_interface_switch_port_mode	= new Thelist_Cisco_command_getinterfaceswitchportmode($this->_device, $this->_interface);
		} else {
			$interface_name			= $this->_interface;
			$get_interface_switch_port_mode	= new Thelist_Cisco_command_getinterfaceswitchportmode($this->_device, $interface_name);
		}
		
		$current_switch_port_mode			= $get_interface_switch_port_mode->get_configured_switch_port_mode();
		
		if ($current_switch_port_mode != $this->_switch_port_mode) {
			
			//we cannot set trunk mode if the encapsulation is not set
			//we can change the mode no probelm, but if encapsulation is not set then the mode= trunk fails
			//this is where the object model comes in handy
			if ($this->_switch_port_mode == 'trunk') {
				
				$get_interface_encapsulation		= new Thelist_Cisco_command_getinterfaceswitchportencapsulation($this->_device, $interface_name);
				$current_interface_encapsulation	= $get_interface_encapsulation->get_configured_trunk_encapsulation();
	
				//if the encapsulation is not set
				if ($current_interface_encapsulation == null) {
					
					//additional help for object model
					if (is_object($this->_interface)) {
						
						$current_encapsulation	= $this->_interface->get_interface_configuration(31);
						
						if ($current_encapsulation != false) {
	
							//there is a simple ordering issue and the set mode is just being issued before the encapsulation is changed
							//so we fix that, before implementing the new trunk mode
							$set_interface_encapsulation	= new Thelist_Cisco_command_setinterfaceswitchportencapsulation($this->_device, $this->_interface, $current_encapsulation['0']->get_mapped_configuration_value_1());
							$set_interface_encapsulation->execute();
							
						} else {
							//we have a logic error in the input area, it should not be possible to have a 
							//trunk port with no encapsulation
							throw new exception('interface trunk mode cannot be implemented because port does not have encapsulation set', 10601);
						}
						
					} else {
						//string model fails
						throw new exception('interface trunk mode cannot be implemented because port does not have encapsulation set', 10602);
					}
				}
			}

			//get the root folder
			$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
			$get_connection_root_folder->execute();
			
			$this->_device->execute_command("configure terminal");
			$this->_device->execute_command("interface ".$interface_name."");
			$this->_device->execute_command("switchport mode ".$this->_switch_port_mode."");
			$this->_device->execute_command("end");
			
			$verify			= $get_interface_switch_port_mode->get_configured_switch_port_mode();
			
			if ($verify != $this->_switch_port_mode) {
				throw new exception('interface switch_port_mode was not updated correctly', 10600);
			}
		}
	}
}