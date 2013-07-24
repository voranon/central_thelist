<?php

//exception codes 3800-3899

class thelist_cisco_command_removeinterfacedescription implements Thelist_Commander_pattern_interface_idevicecommand 
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
			$get_interface_description	= new Thelist_Cisco_command_getinterfacedescription($this->_device, $this->_interface);
		} else {
			$interface_name			= $this->_interface;
			$get_interface_description	= new Thelist_Cisco_command_getinterfacedescription($this->_device, $interface_name);
		}
		
		$current_desc = $get_interface_description->get_configured_description();
		
		if ($current_desc != null) {

			//get the root folder
			$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
			$get_connection_root_folder->execute();
			
			//enter config mode
			$this->_device->execute_command("configure terminal");
			$this->_device->execute_command("interface ".$interface_name."");
			$this->_device->execute_command("no description ".$current_desc."");
			$this->_device->execute_command("end");

			//validate
			$verify	= $get_interface_description->get_configured_description();
			
			if ($verify != null) {
				throw new exception('description was not removed from interface', 3800);
			}
		}
	}
}