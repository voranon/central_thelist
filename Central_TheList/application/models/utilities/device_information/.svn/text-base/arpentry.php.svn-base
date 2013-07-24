<?php

//exception codes 6600-6699

class thelist_deviceinformation_arpentry
{
	private $_ipaddress;
	private $_macaddress;
	private $_interface_name;
	private $_mac_address_obj;
	private $_age=null;
	private $_type=null;
	
	public function __construct($ipaddress, $macaddress, $interface_name)
	{
		//make sure the mac address is formatted correctly, this will error if wrong
		$this->_mac_address_obj = new Thelist_Deviceinformation_macaddressinformation($macaddress);
		
		$this->_ipaddress				= $ipaddress;
		$this->_macaddress				= $this->_mac_address_obj->get_macaddress();
		
		//remove white spaces from beginning and end of interface name
		$this->_interface_name			= trim($interface_name);
	}
	public function get_ipaddress()
	{
		return $this->_ipaddress;
	}
	public function get_macaddress()
	{
		return $this->_macaddress;	
	}
	public function get_macaddress_obj()
	{
		return $this->_mac_address_obj;
	}
	public function get_interface_name()
	{
		return $this->_interface_name;
	}
	public function get_age()
	{
		return $this->_age;
	}
	public function set_age($age_in_minutes)
	{
		if (preg_match("/[0-9]+/", $age_in_minutes) || preg_match("/local/", $age_in_minutes)) {
			$this->_age = $age_in_minutes;
		} else {
			throw new exception('arp age is not corretly formatted', 6501);
		}	
	}	
	
	public function set_arp_type($type)
	{
		if ($type != 'static' && $type != 'dynamic') {
			throw new exception('arp type is not corretly formatted', 6502);
		} else {
			$this->_type	= $type;
		}
	}
	
	public function get_arp_type()
	{
		return $this->_type;
	}
}
?>