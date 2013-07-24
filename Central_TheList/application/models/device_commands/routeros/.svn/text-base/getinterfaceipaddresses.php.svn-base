<?php

//exception codes 19800-19899

class thelist_routeros_command_getinterfaceipaddresses implements Thelist_Commander_pattern_interface_idevicecommand 
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
		
		$command	 		= "/ip address export";
		$reg_ex				= "add address=([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})\/?([0-9]+)? .* interface=\"?".$interface_name."\"? ";
		
		$device_reply = $this->_device->execute_command($command);
		
		preg_match_all("/".$reg_ex."/", $device_reply->get_message(), $interface_ips_raw);
				
		if (isset($interface_ips_raw['1'])) {

			foreach ($interface_ips_raw['1'] as $ip_index => $ip_address) {
				$this->_ip_addresses[]	= new Thelist_Deviceinformation_ipaddressentry($ip_address, $interface_ips_raw['2'][$ip_index], $interface_name);
			}
		}
	}
	
	public function get_ip_addresses($refresh=true) 
	{
		if($this->_ip_addresses == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		//used for validation, must be a fresh result
		return $this->_ip_addresses;
	}
}