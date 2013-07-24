<?php

//exception codes 4300-4399

class thelist_bairos_command_getinterfacemacaddress implements Thelist_Commander_pattern_interface_idevicecommand
{

	private $_device;
	private $_interface;
	private $_mac_address=null;
	
	public function __construct($device, $interface)
	{
		
		//$interface
		//object	= interface_obj
		//string	= interface name
		
		$this->_device		= $device;
		$this->_interface	= $interface;
	
	}
	
	public function execute()
	{
		
		if (is_object($this->_interface)) {
			$interface_name		= $this->_interface->get_if_name();
		} else {
			$interface_name		= $this->_interface;
		}
		//get arp output
	
		$device_reply = $this->_device->execute_command("ifconfig -a | grep HWaddr");

		preg_match("/".$interface_name." +Link encap:Ethernet  HWaddr (\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2})/",$device_reply->get_message(), $mac_address);

		if (isset($mac_address['1'])) {
			$this->_mac_address = new Thelist_Deviceinformation_macaddressinformation($mac_address['1']);
		} else {
			throw new exception('interface mac address could not be determined', 4300);
		}
	}
	
	public function get_mac_address()
	{
		if ($this->_mac_address == null) {
			$this->execute();
		}
		return $this->_mac_address;
	}

}