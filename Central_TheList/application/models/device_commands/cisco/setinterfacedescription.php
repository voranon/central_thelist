<?php

//exception codes 11600-11699

class thelist_cisco_command_setinterfacedescription implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	private $_description;
	
	public function __construct($device, $interface, $description)
	{
		//$interface
		//object	= interface_obj
		//string	= interface name
		
		//$description
		//object	= new description string
		//string	= new description string
		
		$this->_device 					= $device;
		$this->_interface 				= $interface;
		$this->_description				= $description;
	}
	
	public function execute()
	{
		//get the root folder
		$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
		$get_connection_root_folder->execute();
		
		if (is_object($this->_interface)) {
			$interface_name			= $this->_interface->get_if_name();
			$get_interface_description	= new Thelist_Cisco_command_getinterfacedescription($this->_device, $this->_interface);
		} else {
			$interface_name			= $this->_interface;
			$get_interface_description	= new Thelist_Cisco_command_getinterfacedescription($this->_device, $interface_name);
		}
		
		$current_description			= $get_interface_description->get_configured_description();
		
		if ($current_description != $this->_description) {
			
			$this->_device->execute_command("configure terminal");
			$this->_device->execute_command("interface ".$interface_name."");
			$this->_device->execute_command("description ".$this->_description."");
			$this->_device->execute_command("end");
			
			$verify			= $get_interface_description->get_configured_description();
			
			if ($verify != $this->_description) {
				throw new exception('interface description was not updated correctly', 11600);
			}
		}
	}
}