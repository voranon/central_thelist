<?php

//exception codes 6200-6299

class thelist_cisco_command_removeinterfacecamentries implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	
	public function __construct($device, $interface)
	{
		//$interface
		//object	= interface_obj
		//string	= interface name
		
		$this->_device 			= $device;
		$this->_interface	 	= $interface;
	}
	
	public function execute()
	{
		if (is_object($this->_interface)) {
			$interface_name		= $this->_interface->get_if_name();
		} else {
			$interface_name		= $this->_interface;
		}
		
		//get the root folder
		$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
		$get_connection_root_folder->execute();

		//clear cam entries for this port
		$device_reply = $this->_device->execute_command("clear mac-address-table dynamic interface ".$interface_name."");
		
	}
}

