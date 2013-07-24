<?php

//exception codes 18000-18099

class thelist_bairos_command_getsystemtime implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	
	private $_system_time=null;

	public function __construct($device)
	{
		$this->_device 					= $device;
	}
	
	public function execute()
	{		
		$device_reply1 = $this->_device->execute_command("date");
		
		$time					= new Thelist_Utility_time();
		$this->_system_time		= $time->convert_string_to_epoch_time($device_reply1->get_message());
	}
	
	public function get_system_time($refresh=true)
	{
		if($this->_system_time == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		return $this->_system_time;
	}
	
	
}