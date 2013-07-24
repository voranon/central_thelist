<?php

//exception codes 12500-12599

class thelist_bairos_command_getinterfacebootprotocol implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_interface;
	
	private $_boot_protocol=null;

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
		
		$device_reply1 = $this->_device->execute_command("cat /etc/sysconfig/network-scripts/ifcfg-".$interface_name."");
		
		//check if interface has a config file, if not then nothing else matters and al commands will fail
		if (!preg_match("/No such file or directory/", $device_reply1->get_message())) {
				
			//vlan_id
			if (preg_match("/BOOTPROTO=(dhcp|none)/", $device_reply1->get_message(), $result1)) {
				
				if ($result1['1'] == 'dhcp') {
					$this->_boot_protocol	= 'dhcp client';
				} elseif ($result1['1'] == 'none') {
					$this->_boot_protocol	= 'none';
				}
				
			} else {
				//interface does not have a vlan id
				$this->_boot_protocol	= null;
			}
		
		} else {
			throw new exception('interface config file does not exist, interface not setup, cannot get vlan id', 12200);
		}
	}
	
	public function get_boot_protocol() 
	{
		//used for validation, must be a fresh result
		$this->execute();
		return $this->_boot_protocol;
	}
}