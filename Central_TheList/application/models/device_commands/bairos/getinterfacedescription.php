<?php

//exception codes 13000-13099

class thelist_bairos_command_getinterfacedescription implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	
	private $_configured_description=null;
	
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
				
			//description
			if (preg_match("/^#(.*)/", $device_reply1->get_message(), $result1)) {
				
				$this->_configured_description = $result1['1'];

			} else {
				//nothing there is no description on the interface and thats ok
			}
		
		} else {
			throw new exception("interface config file on device ".$this->_device->get_fqdn().", interface ".$interface_name."  does not exist, interface not setup, cannot get ip addresses", 13000);
		}
	}
	

	public function get_configured_description() 
	{
		//used for validation, must be a fresh result
		$this->execute();
		return $this->_configured_description;
	}

}