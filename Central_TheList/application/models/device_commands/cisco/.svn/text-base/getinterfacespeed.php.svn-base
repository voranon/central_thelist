<?php

//exception codes 10100-10199

class thelist_cisco_command_getinterfacespeed implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_interface;
	
	private $_configured_speed=null;
	
	//if negotiated
	private $_running_speed=null;
	
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
			$interface_name		= $this->_interface->get_if_name();
		} else {
			$interface_name		= $this->_interface;
		}
		
		//get the root folder
		$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
		$get_connection_root_folder->execute();
		
		$device_reply1 = $this->_device->execute_command("show interface ".$interface_name."");
		$device_reply2 = $this->_device->execute_command("show running-config interface ".$interface_name."");
		
		if (preg_match("/(Full-duplex|Half-duplex|Auto-duplex), ((10|100|1000)Mb\/s|Auto-speed)(, link type is auto)?, media type is/", $device_reply1->get_message(), $result1)) {
			
			if (isset($result1['3'])) {
				$this->_running_speed	= $result1['3'];
			} else {
				//auto speed is only shown when the interface is not connected to anything
				//instead of 0 we use null to indicate its not running at any speed
				$this->_running_speed	= null;
			}

		} else {
			throw new exception('interface running speed could not be determined for interface on device', 10100);
		}
		
		//check if command failed
		if (preg_match("/Current configuration/", $device_reply2->get_message())) {
				
			preg_match("/speed (1000|100|10)\r/", $device_reply2->get_message(), $result2);
			
			if (isset($result2['1'])) {
				$this->_configured_speed	= $result2['1'];
			} else {
				//auto speed is only shown when the interface is not connected to anything
				//instead of 0 we use null to indicate its not running at any speed
				$this->_configured_speed	= 'auto';
			}

		} else {
			throw new exception('interface configured speed could not be determined for interface on device', 10101);
		}
	}
	
	public function get_running_speed() 
	{
		//used for validation, must be a fresh result
		$this->execute();
		return $this->_running_speed;
	}
	
	public function get_configured_speed()
	{
		//used for validation, must be a fresh result
		$this->execute();
		return $this->_configured_speed;
	}
}