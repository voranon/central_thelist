<?php

//exception codes 4000-4099

class thelist_cisco_command_getinterfacelayer2mtu implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	
	private $_configured_layer2mtu=null;
	
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
		
		if (preg_match("/MTU ([0-9]+) bytes, BW/", $device_reply1->get_message(), $result1)) {
			$this->_configured_layer2mtu	= $result1['1'];
		} else {
			throw new exception('interface layer 2 mtu could not be determined for interface on device', 4000);
		}
	}

	public function get_configured_layer2mtu()
	{
		//used for validation, must be a fresh result
		$this->execute();
		return $this->_configured_layer2mtu;
	}
}