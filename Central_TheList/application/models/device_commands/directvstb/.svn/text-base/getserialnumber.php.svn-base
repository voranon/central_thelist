<?php

class thelist_directvstb_command_getserialnumber implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_type;
	
	public function __construct($device, $type)
	{
		$this->_device = $device;
		$this->_type = $type;
	
	}
	
	public function execute()
	{
		//get arp output
		$device_reply = $this->_device->execute_command("info/getVersion");
		
		if ($device_reply->get_code() == '1') {

			if ($this->_type == 'accesscard') {
				
				preg_match("/\"accessCardId\": \"([0-9]{4}-[0-9]{4}-[0-9]{4})\",/", $device_reply->get_message(), $accesscard);
				
				$patterns = array('\"', '-', ' ', "\r", "\r\n", "\n");
				
				return str_replace($patterns, '', $accesscard['1']);

			} elseif ($this->_type == 'receiver') {
				
				preg_match("/\"receiverId\": \"([0-9]{4} [0-9]{4} [0-9]{4})\",/", $device_reply->get_message(), $receiverid);
				
				$patterns = array('\"', ' ', "\r", "\r\n", "\n");
				
				return str_replace($patterns, '', $receiverid['1']);
				
			}
		} else {
			
			throw new exception('device dident respond correctly to command', 666);
			
		}		
	}

}