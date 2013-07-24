<?php

//exception codes 3400-3499

class thelist_cisco_command_setinterfacespeed implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	private $_speed;
	
	public function __construct($device, $interface, $speed)
	{
		//$interface
		//object	= interface_obj
		//string	= interface name
		
		//$speed
		//object	= new speed string
		//string	= new speed string
		
		$this->_device 					= $device;
		$this->_interface 				= $interface;
		$this->_speed					= $speed;
	}
	
	public function execute()
	{
		//get the root folder
		$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
		$get_connection_root_folder->execute();
		
		if (is_object($this->_interface)) {
			$interface_name			= $this->_interface->get_if_name();
			$get_interface_speed	= new Thelist_Cisco_command_getinterfacespeed($this->_device, $this->_interface);
		} else {
			$interface_name			= $this->_interface;
			$get_interface_speed	= new Thelist_Cisco_command_getinterfacespeed($this->_device, $interface_name);
		}
		
		$current_speed			= $get_interface_speed->get_configured_speed();
		
		if ($current_speed != $this->_speed) {
			
			$this->_device->execute_command("configure terminal");
			$this->_device->execute_command("interface ".$interface_name."");
			$mm = $this->_device->execute_command("speed ".$this->_speed."");
			$this->_device->execute_command("end");
			
			$verify			= $get_interface_speed->get_configured_speed();
			
			if ($verify != $this->_speed) {
				//likely that you are trying to set 100 or 10 for a gigabit interface
				throw new exception('interface speed was not updated correctly', 3400);
			}
		}
	}
}