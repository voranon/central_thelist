<?php

//exception codes 11500-11599

class thelist_cisco_command_getinterfacedescription implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	
	private $_configured_description=null;
	
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
				
			preg_match("/description (.*)\r/", $device_reply1->get_message(), $result1);
			
			if (isset($result1['1'])) {
				$this->_configured_description	= $result1['1'];
			} else {
				$this->_configured_description	= null;
			}
		
		} else {
			throw new exception('interface configured description could not be determined for interface on device', 11501);
		}
	}
	

	public function get_configured_description() 
	{
		//used for validation, must be a fresh result
		$this->execute();
		return $this->_configured_description;
	}

}