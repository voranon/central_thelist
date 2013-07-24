<?php

//exception codes 11400-11499

class thelist_cisco_command_logout implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	
	public function __construct($device)
	{
		$this->_device = $device;
	
	}
	
	public function execute()
	{
		//get the root folder
		$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
		$get_connection_root_folder->execute();
		
		//exit
		$device_reply = $this->_device->execute_command("exit");
		
	}

}