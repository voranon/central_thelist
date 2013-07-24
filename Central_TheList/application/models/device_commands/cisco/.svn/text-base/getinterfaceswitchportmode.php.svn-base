<?php

//exception codes 10700-10799

class thelist_cisco_command_getinterfaceswitchportmode implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	
	private $_configured_switch_port_mode=null;

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

		$device_reply1 = $this->_device->execute_command("show running-config interface ".$interface_name."");

		//check if command failed
		if (preg_match("/Current configuration/", $device_reply1->get_message())) {
				
			preg_match("/switchport mode (trunk|access|dynamic)/", $device_reply1->get_message(), $result1);
			
			if (isset($result1['1'])) {
				$this->_configured_switch_port_mode	= $result1['1'];
			} else {
				//if nothing is shown (3500 series), then the port is in access mode
				$this->_configured_switch_port_mode	= 'access';
			}
		
		} else {
			throw new exception('interface configured mode could not be determined for interface on device', 10700);
		}
	}
	

	public function get_configured_switch_port_mode() 
	{
		//used for validation, must be a fresh result
		$this->execute();
		return $this->_configured_switch_port_mode;
	}

}