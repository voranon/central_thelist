<?php

//exception codes 11900-11999

class thelist_bairos_command_getinterfacestatus implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_interface;
	
	//set on boot 
	private $_configured_admin_boot_status=null;
	private $_configured_admin_status=null;
	private $_operational_status=null;
	
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
				
			//boot admin status
			preg_match("/ONBOOT=(yes|no)/", $device_reply1->get_message(), $result1);
			
			if ($result1['1'] == 'no') {

				//if there is no config
				$this->_configured_admin_boot_status	= 0;
				
			} elseif ($result1['1'] == 'yes') {
				
				$this->_configured_admin_boot_status	= 1;

			} else {
				throw new exception('interface administrative boot status could not be determined', 11900);
			}
		
		} else {
			throw new exception('interface config file does not exist, interface not setup', 11901);
		}
		
		$device_reply2 = $this->_device->execute_command("ifconfig ".$interface_name."");
		
		//current admin status 
		if (preg_match("/".$interface_name.": error fetching interface information: Device not found/", $device_reply2->get_message(), $result2)) {
			
			//for vlans and other non-physical interfaces that are down
			$this->_configured_admin_status	= 0;
			//interface down, that means no operational status, not 0 because that means disconnected, and that is not the case
			$this->_operational_status	= null;
		
		} elseif (preg_match("/(UP|) (LOOPBACK|BROADCAST)/", $device_reply2->get_message(), $result4)) {
			
			//physical interfaces
			if ($result4['1'] == 'UP') {
				$this->_configured_admin_status	= 1;
				
				$device_reply3 = $this->_device->execute_command("ethtool ".$interface_name."");
				
				if (preg_match("/Link detected: (yes|no)/", $device_reply3->get_message(), $result3)) {
						
					if ($result3['1'] == 'yes') {
						$this->_operational_status	= 1;
					} elseif ($result3['1'] == 'no') {
						$this->_operational_status	= 0;
					}
						
				} else {
					throw new exception('interface operational status could not be determined', 11902);
				}
				
			} else {
				
				$this->_configured_admin_status	= 0;
				//interface down, that means no operational status, not 0 because that means disconnected, and that is not the case
				$this->_operational_status	= null;
			}
		
		} else {
			throw new exception('interface admin status could not be determined', 11903);
		}
	}
	
	public function get_operational_status($refresh=true) 
	{
		if($this->_operational_status == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		
		return $this->_operational_status;
	}
	
	public function get_configured_admin_status($refresh=true)
	{
		if($this->_configured_admin_status == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		
		return $this->_configured_admin_status;
	}
	
	public function get_configured_admin_boot_status($refresh=true)
	{
		if($this->_configured_admin_boot_status == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		return $this->_configured_admin_boot_status;
	}
}