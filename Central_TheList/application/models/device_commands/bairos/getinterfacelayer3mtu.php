<?php

//exception codes 13100-13199

class thelist_bairos_command_getinterfacelayer3mtu implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_interface;
	
	private $_configured_layer3mtu=null;
	private $_running_layer3mtu=null;
	
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
		
		$device_reply1 = $this->_device->execute_command("ifconfig ".$interface_name."");
		
		if (preg_match("/MTU:([0-9]+) +Metric/", $device_reply1->get_message(), $result1)) {
			$this->_running_layer3mtu	= $result1['1'];
		} else {
			//nothing, its likely the interface is not running, and thats all ok no exception should be thrown
			$this->_running_layer3mtu	= null;
		}
		
		
		$device_reply2 = $this->_device->execute_command("cat /etc/sysconfig/network-scripts/ifcfg-".$interface_name."");
		//check if interface has a config file, if not then nothing else matters and al commands will fail
		if (!preg_match("/No such file or directory/", $device_reply2->get_message())) {
		
			//mtu
			if (preg_match("/MTU=([0-9]+)/", $device_reply2->get_message(), $result2)) {
				$this->_configured_layer3mtu	= $result2['1'];
			} else {
				//nothing there is no mtu configured on the interface and thats ok
				$this->_configured_layer3mtu = null;
			}
		
		} else {
			throw new exception("interface config file on device ".$this->_device->get_fqdn().", interface ".$interface_name."  does not exist, interface not setup, cannot get ip addresses", 13100);
		}
	}

	public function get_configured_layer3mtu()
	{
		//used for validation, must be a fresh result
		$this->execute();
		return $this->_configured_layer3mtu;
	}
	
	public function get_running_layer3mtu()
	{
		//used for validation, must be a fresh result
		$this->execute();
		return $this->_running_layer3mtu;
	}
}