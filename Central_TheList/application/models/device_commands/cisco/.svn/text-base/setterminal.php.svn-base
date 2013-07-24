<?php

//exception codes 16200-16299

class thelist_cisco_command_setterminal implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_terminal_length;
	
	public function __construct($device, $terminal_width, $terminal_length)
	{

		//$terminal_length / width
		//object	= new terminal length int
		//string	= new terminal length int
		
		$this->_device 					= $device;
		$this->_terminal_width			= $terminal_width;
		$this->_terminal_length			= $terminal_length;
	}
	
	public function execute()
	{
		//get the root folder
		$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
		$get_connection_root_folder->execute();
		
		//should have a get function here first, but currently in a hurry

		$this->_device->execute_command("terminal width ".$this->_terminal_width."");
		$this->_device->execute_command("terminal length ".$this->_terminal_length."");

		//should have a verify get function here, but currently in a hurry
	}
}