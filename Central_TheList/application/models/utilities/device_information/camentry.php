<?php

class thelist_deviceinformation_camentry
{
	private $_vlan_id;
	private $_macaddress;
	private $_interface_name;
	private $_mac_address_obj;
	
	public function __construct($vlan_id,$macaddress,$interface_name)
	{
		//make sure the mac address is formatted correctly, this will error if wrong
		$this->_mac_address_obj = new Thelist_Deviceinformation_macaddressinformation($macaddress);

		$this->_vlan_id					= $vlan_id;
		$this->_macaddress				= $this->_mac_address_obj->get_macaddress();
		
		//remove white spaces from beginning and end of interface name
		$this->_interface_name			= trim($interface_name);
	}
	
	public function get_vlan_id()
	{
		return $this->_ipaddress;
	}
	public function get_mac_address_obj()
	{
		return $this->_mac_address_obj;
	}
	public function get_macaddress()
	{
		return $this->_macaddress;	
	}
	public function get_interface_name()
	{
		return $this->_interface_name;
	}

}
?>