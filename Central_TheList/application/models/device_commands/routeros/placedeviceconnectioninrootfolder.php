<?php

//exception codes 7600-7699

class thelist_routeros_command_placedeviceconnectioninrootfolder implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	
	public function __construct($device)
	{
		$this->_device 					= $device;
	}

	public function execute()
	{

		//verify that we are in root privilied mode
		$device_reply = $this->_device->execute_command('/');

	}
}
	