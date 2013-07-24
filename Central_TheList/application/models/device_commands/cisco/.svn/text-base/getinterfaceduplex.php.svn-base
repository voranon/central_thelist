<?php

//exception codes 10500-10599

class thelist_cisco_command_getinterfaceduplex implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	
	private $_configured_duplex=null;
	
	//if negotiated
	private $_running_duplex=null;
	
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
		
		$device_reply1 = $this->_device->execute_command("show interface ".$interface_name."");
		$device_reply2 = $this->_device->execute_command("show running-config interface ".$interface_name."");
		
		if (preg_match("/(Full-duplex|Half-duplex|Auto-duplex), ((10|100|1000)Mb\/s|Auto-speed)(, link type is auto)?, media type is/", $device_reply1->get_message(), $result1)) {
			
			if ($result1['1'] == 'Full-duplex') {
				$this->_running_duplex	= 'full';
			} elseif ($result1['1'] == 'Half-duplex') {
				$this->_running_duplex	= 'half';
			} else {
				//auto duplex is only shown when the interface is not connected to anything
				//instead of 0 we use null to indicate its not running at any duplex
				$this->_running_duplex	= null;
			}

		} else {

			throw new exception('interface running duplex could not be determined for interface on device', 10500);
		}
		
		//check if command failed
		if (preg_match("/Current configuration/", $device_reply2->get_message())) {
				
			preg_match("/duplex (full|half)\r/", $device_reply2->get_message(), $result2);
			
			if (isset($result2['1'])) {
				$this->_configured_duplex	= $result2['1'];
			} else {
				//auto speed is only shown when the interface is not connected to anything
				//instead of 0 we use null to indicate its not running at any speed
				$this->_configured_duplex	= 'auto';
			}
		
		} else {
			throw new exception('interface configured speed could not be determined for interface on device', 10501);
		}
	}
	

	public function get_running_duplex($refresh=true) 
	{
		if($this->_running_duplex == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		
		return $this->_running_duplex;
	}
	
	public function get_configured_duplex($refresh=true)
	{
		if($this->_configured_duplex == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}

		return $this->_configured_duplex;
	}
}