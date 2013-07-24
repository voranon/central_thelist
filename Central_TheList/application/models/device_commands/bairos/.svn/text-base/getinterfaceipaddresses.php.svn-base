<?php

//exception codes 12400-12499

class thelist_bairos_command_getinterfaceipaddresses implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_interface;
	
	private $_ip_addresses=null;

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
		
		//in case martin gets tempted, ip gateway is not an attribute of an ip address, it is a route entry and should be handled as such
		
		//reset the var so it does not build up
		$this->_ip_addresses	= null;
		
		if (is_object($this->_interface)) {
			$interface_name		= $this->_interface->get_if_name();
		} else {
			$interface_name		= $this->_interface;
		}
		
		$device_reply1 = $this->_device->execute_command("cat /etc/sysconfig/network-scripts/ifcfg-".$interface_name."");
		
		//check if interface has a config file, if not then nothing else matters and al commands will fail
		if (!preg_match("/No such file or directory/", $device_reply1->get_message())) {
				
			//vlan_id
			if (preg_match("/IPADDR=([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/", $device_reply1->get_message(), $result1)) {
				
				$ip_address = $result1['1'];
				
				if (preg_match("/NETMASK=([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/", $device_reply1->get_message(), $result2)) {
					
					$converter = new Thelist_Utility_ipconverter();
					$this->_ip_addresses[]	= new Thelist_Deviceinformation_ipaddressentry($ip_address, $converter->convert_dotted_subnet_to_cidr($result2['1']), $interface_name);
					
				} else {
					throw new exception("interface config file on device ".$this->_device->get_fqdn().", interface ".$interface_name." has ip address, but is missing subnet mask", 12400);
				}
				
			} else {
				//nothing there is no ip on the interface and thats ok
			}
		
		} else {
			throw new exception("interface config file on device ".$this->_device->get_fqdn().", interface ".$interface_name."  does not exist, interface not setup, cannot get ip addresses", 12401);
		}
	}
	
	public function get_ip_addresses() 
	{
		//used for validation, must be a fresh result
		$this->execute();
		return $this->_ip_addresses;
	}
}