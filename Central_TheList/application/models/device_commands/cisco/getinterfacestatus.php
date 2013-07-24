<?php

//exception codes 10200-10299

class thelist_cisco_command_getinterfacestatus implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_interface;
	
	private $_configured_admin_status=null;
	private $_operational_status=null;
	
	public function __construct($device, $interface)
	{
		if ($interface == null) {
			throw new exception('interface status requires atleast interface name or object', 10202);
		}
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

		if (preg_match("/is (up|down|administratively down), line protocol is (up|down)/", $device_reply1->get_message(), $result1)) {
			
			if ($result1['1'] == 'up' && $result1['2'] == 'up') {
				$this->_operational_status	= 1;
			} else {
				$this->_operational_status	= 0;
			}

		} else {

			throw new exception('interface operational status could not be determined for interface on device', 10200);
		}

		//check if command failed
		if (preg_match("/Current configuration/", $device_reply2->get_message())) {
				
			preg_match("/(shutdown)/", $device_reply2->get_message(), $result2);
			
			if (isset($result2['1'])) {
				$this->_configured_admin_status	= 0;
			} else {
				$this->_configured_admin_status	= 1;
			}
		
		} else {
			throw new exception('interface configured admin status could not be determined for interface on device', 10201);
		}
	}
	
	public function get_operational_status() 
	{
		//used for validation, must be a fresh result
		$this->execute();
		return $this->_operational_status;
	}
	
	public function get_configured_admin_status()
	{
		//used for validation, must be a fresh result
		$this->execute();
		return $this->_configured_admin_status;
	}
}