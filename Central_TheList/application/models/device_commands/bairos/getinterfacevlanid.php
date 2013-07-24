<?php

//exception codes 12200-12299

class thelist_bairos_command_getinterfacevlanid implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_interface;
	
	private $_vlan_id=null;

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
			if (preg_match("/DEVICE=(eth[0-9]+\.([0-9]+))/", $device_reply1->get_message(), $result1)) {
				$this->_vlan_id	= $result1['2'];
			} else {
				//interface does not have a vlan id
				$this->_vlan_id	= null;
			}
		
		} else {
			throw new exception('interface config file does not exist, interface not setup, cannot get vlan id', 12200);
		}
		
		
		if (isset($result1['2'])) {
			
			//further tests for vlans only
			//lets make sure the file name and the vlan id are in sync
			if ($result1['1'] != $interface_name) {
				throw new exception("we got the vlan id from the device: ".$this->_device->get_fqdn()." config but the interface config filename and the device name do not match, filename: ifcfg-".$interface_name." device: ".$result1['1']." ", 12201);
			}
			
			//lets also make sure the vlan=yes is set since this is a vlan interface
			if (!preg_match("/VLAN=yes/", $device_reply1->get_message())) {
				throw new exception("we got the vlan id from the device: ".$this->_device->get_fqdn()." config but the interface config file is missing the VLAN=yes line, even though this is a vlan interface. filename: ifcfg-".$interface_name."  ", 12202);
			}
		} else {
			
			//lets also make sure the vlan=yes is NOT set since this is not vlan interface
			if (preg_match("/(VLAN=yes)/", $device_reply1->get_message())) {
				throw new exception("this interface does not have a vlan id on device: ".$this->_device->get_fqdn()." config but the interface config file has a VLAN=yes line, even though this is NOT a vlan interface. filename: ifcfg-".$interface_name."  ", 12203);
			}
			
		}
	}
	
	public function get_vlan_id() 
	{
		//used for validation, must be a fresh result
		$this->execute();
		return $this->_vlan_id;
	}
}