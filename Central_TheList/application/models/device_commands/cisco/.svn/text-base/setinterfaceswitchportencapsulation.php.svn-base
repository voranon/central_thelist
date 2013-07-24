<?php

//exception codes 6700-6799

class thelist_cisco_command_setinterfaceswitchportencapsulation implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	private $_switch_port_trunk_encapsulation;
	
	public function __construct($device, $interface, $switch_port_trunk_encapsulation)
	{
		//$interface
		//object	= interface_obj
		//string	= interface name
		
		//$switch_port_trunk_encapsulation
		//object	= new switch_port_trunk_encapsulation string
		//string	= new switch_port_trunk_encapsulation string
		
		$this->_device 								= $device;
		$this->_interface 							= $interface;
		$this->_switch_port_trunk_encapsulation		= $switch_port_trunk_encapsulation;
	}
	
	public function execute()
	{
		if (is_object($this->_interface)) {
			$interface_name			= $this->_interface->get_if_name();
			$get_interface_trunk_encapsulation	= new Thelist_Cisco_command_getinterfaceswitchportencapsulation($this->_device, $this->_interface);
		} else {
			$interface_name			= $this->_interface;
			$get_interface_trunk_encapsulation	= new Thelist_Cisco_command_getinterfaceswitchportencapsulation($this->_device, $interface_name);
		}
		
		$current_trunk_encapsulation			= $get_interface_trunk_encapsulation->get_configured_trunk_encapsulation();
		
		if ($current_trunk_encapsulation != $this->_switch_port_trunk_encapsulation) {
			
			//get the root folder
			$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
			$get_connection_root_folder->execute();
			
			//we can always set the encapsulation regardless of the interface mode
			//even if the interface is in access or dynamic
			
			$this->_device->execute_command("configure terminal");
			$this->_device->execute_command("interface ".$interface_name."");
			$mm = $this->_device->execute_command("switchport trunk encapsulation ".$this->_switch_port_trunk_encapsulation."");
			$this->_device->execute_command("end");
			
			$verify			= $get_interface_trunk_encapsulation->get_configured_trunk_encapsulation();
			
			if ($verify != $this->_switch_port_trunk_encapsulation) {
				throw new exception('interface trunk encapsulation was not set', 6700);
			}
		}
	}
}