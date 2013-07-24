<?php

class thelist_bairos_command_logout implements Thelist_Commander_pattern_interface_idevicecommand
{

	private $_device;
	
	public function __construct($device)
	{
		$this->_device = $device;
	
	}
	
	public function execute()
	{
		//log out
		$device_reply = $this->_device->execute_command("exit");

	}
}