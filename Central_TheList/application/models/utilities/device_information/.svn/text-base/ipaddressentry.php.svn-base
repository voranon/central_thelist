<?php

//by martin
//exception codes 17400-17499
require_once 'Net/IPv4.php';

class thelist_deviceinformation_ipaddressentry
{
	private $_ip_address;
	private $_ip_subnet_cidr;
	private $_interface_name=null;
	
	private $_ipv4_first_octet=null;
	private $_ipv4_second_octet=null;
	private $_ipv4_third_octet=null;
	private $_ipv4_fourth_octet=null;
	
	private $_ip_subnet_address=null;
	private $_ip_broadcast_address=null;
	
	private $_ip_version=null;
	
	public function __construct($ip_address, $ip_subnet_cidr=null, $interface_name=null)
	{
		//find better regex to make better validations
		if (preg_match("/\./", $ip_address)) {
			//is this an ipv4 address
			$valid_ipv4 = Net_IPv4::validateIP($ip_address);
		
			$this->_ip_version = 4;
			
			$exploded_ip = explode(".",$ip_address);
			
			$this->_ipv4_first_octet	= $exploded_ip['0'];
			$this->_ipv4_second_octet	= $exploded_ip['1'];
			$this->_ipv4_third_octet	= $exploded_ip['2'];
			$this->_ipv4_fourth_octet	= $exploded_ip['3'];
			
			//find subnet mask and broadcast address
			if ($ip_subnet_cidr != null) {

				$cidr = "".$ip_address."/".$ip_subnet_cidr."";
				$net_obj = Net_IPv4::parseAddress($cidr);
				
				$this->_ip_subnet_address		= $net_obj->network;
				$this->_ip_broadcast_address	= $net_obj->broadcast;
			}
			
			
		} elseif (preg_match("/:/", $ip_address)) {
			//is this an ipv4 address
			$valid_ip = Net_IPv4::validateIP($ip_address);
		} else {
			throw new exception(" ".$ip_address.", is not a valid ip address ", 17400);
		}
		
		if (isset($valid_ipv4)) {
			if ($valid_ipv4 == false) {
				throw new exception(" ".$ip_address.", is not a valid ipv4 address ", 17401);
			}
		}
		
		if ($ip_subnet_cidr != null) {
			
			if (!is_numeric($ip_subnet_cidr)) {
				throw new exception(" ".$ip_subnet_cidr.", is not a valid cidr mask", 17402);
			}
		}

		$this->_ip_address			= $ip_address;
		$this->_ip_subnet_cidr		= $ip_subnet_cidr;
		$this->_interface_name		= $interface_name;
	}
	
	public function get_ip_address()
	{
		return $this->_ip_address;
	}
	
	public function get_ip_subnet_address()
	{	
		return $this->_ip_subnet_address;
	}
	
	public function get_ip_broadcast_address()
	{
		return $this->_ip_broadcast_address;
	}
	
	public function get_ip_subnet_cidr()
	{
		return $this->_ip_subnet_cidr;
	}
	public function get_dotted_ip_subnet_mask()
	{
		$converter = new Thelist_Utility_ipconverter();
		return $converter->convert_cidr_subnet_to_dotted($this->_ip_subnet_cidr);
	}
	public function get_interface_name()
	{
		return $this->_interface_name;
	}
	public function get_ip_version()
	{
		return $this->_ip_version;
	} 
	public function get_ipv4_first_octet()
	{
		return $this->_ipv4_first_octet;
	}
	
	public function get_ipv4_second_octet()
	{
		return $this->_ipv4_second_octet;
	}
	
	public function get_ipv4_third_octet()
	{
		return $this->_ipv4_third_octet;
	}
	
	public function get_ipv4_fourth_octet()
	{
		return $this->_ipv4_fourth_octet;
	}
	 
}
?>