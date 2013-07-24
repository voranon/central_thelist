<?php

//exception codes 16000-16099

class thelist_cisco_command_getserialnumber implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_serial_number=null;
	
	public function __construct($device)
	{
		$this->_device = $device;
	}
	
	public function execute()
	{
		
		//get the root folder
		$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
		$get_connection_root_folder->execute();
		
		//set the terminal so it does not page
		$set_terminal	= new Thelist_Cisco_command_setterminal($this->_device, 80, 0);
		$set_terminal->execute();
		
		//get arp output
		$device_reply = $this->_device->execute_command('show version');
		
		//set the terminal back to standard
		$set_terminal	= new Thelist_Cisco_command_setterminal($this->_device, 80, 25);
		$set_terminal->execute();
		
		preg_match("/System serial number: +(.*)|System serial number +: +(.*)/", $device_reply->get_message(), $ciscoserial);
		
		if (isset($ciscoserial['0'])) {
			
			$patterns = array('"', ' ', "\r", "\r\n", "\n");
			
			if (isset($ciscoserial['2'])) {
				 $this->_serial_number = str_replace($patterns, '', $ciscoserial['2']);
			} else {
				 $this->_serial_number = str_replace($patterns, '', $ciscoserial['1']);
			}

		} else {
			throw new exception("we could not determine the serial number for device: ".$this->_device->get_fqdn()." ", 16000);
		}		
	}
	
	public function get_serial_number()
	{
		if ($this->_serial_number == null) {
			$this->execute();
		}
		
		return $this->_serial_number;
	}

}