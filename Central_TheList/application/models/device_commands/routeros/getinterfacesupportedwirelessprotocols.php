<?php

//exception codes 18900-18999

class thelist_routeros_command_getinterfacesupportedwirelessprotocols implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_interface;
	
	private $_supported_wireless_protocols=null;
	private $_running_protocol=null;
	
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
		
		//this also ensures the interface exists, since we bypass any commands on some wireless interfaces
		$get_if_type	= new Thelist_Routeros_command_getinterfacetype($this->_device, $this->_interface);
		$if_type		= $get_if_type->get_routeros_specific_if_type_name();
			
		if ($if_type == 'wireless') {
			//append as we add commands for interface types
		} else {
			throw new exception("we dont know how to handle wireless supported protocols for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' ", 18900);
		}
		
		if ($if_type == 'wireless') {
				
			//wireless
			$main_config_reply 	= $this->_device->execute_command("/interface wireless export");
			
			preg_match("/band=(.*) basic-rates-a\/g.* name=".$this->_interface->get_if_name()." /", $main_config_reply->get_message(), $raw_interface_band);
			
			if (isset($raw_interface_band['1'])) {
				
				preg_match("/^2ghz-b$|^2ghz-onlyg$|^2ghz-b\/g$|^2ghz-onlyn$|^2ghz-b\/g\/n$/", $raw_interface_band['1'], $supported_standard_amendments);
				
				if (isset($supported_standard_amendments['0'])) {
							
					if ($supported_standard_amendments['0'] == '2ghz-onlyg') {
						$this->_supported_wireless_protocols[]	= '802.11g';
					} elseif ($supported_standard_amendments['0'] == '2ghz-onlyn') {
						$this->_supported_wireless_protocols[]	= '802.11n';
					} elseif ($supported_standard_amendments['0'] == '2ghz-b/g/n') {
						$this->_supported_wireless_protocols[]	= '802.11b';
						$this->_supported_wireless_protocols[]	= '802.11g';
						$this->_supported_wireless_protocols[]	= '802.11n';
					} elseif ($supported_standard_amendments['0'] == '5ghz-a/n') {
						$this->_supported_wireless_protocols[]	= '802.11a';
						$this->_supported_wireless_protocols[]	= '802.11n';
					} elseif ($supported_standard_amendments['0'] == '5ghz-a') {
						$this->_supported_wireless_protocols[]	= '802.11a';
					} elseif ($supported_standard_amendments['0'] == '5ghz-onlyn') {
						$this->_supported_wireless_protocols[]	= '802.11n';
					} elseif ($supported_standard_amendments['0'] == '2ghz-b/g') {
						$this->_supported_wireless_protocols[]	= '802.11b';
						$this->_supported_wireless_protocols[]	= '802.11g';
					} elseif ($supported_standard_amendments['0'] == '2ghz-b') {
						$this->_supported_wireless_protocols[]	= '802.11b';
					}
					
				} else {
					throw new exception("we cannot determine wireless protocol for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' ", 18803);
				}
				
			} else {
				throw new exception("we cannot determine wireless protocol for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' ", 18801);
			}
				
		} elseif ($if_type == 'nstreme_dual') {
			
			//nstreme dual
			//make it
			
		}
	}
	

	public function get_supported_wireless_protocols($refresh=true) 
	{
		if($this->_supported_wireless_protocols == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		
		return $this->_supported_wireless_protocols;
	}
	
	public function get_running_wireless_protocol($refresh=true)
	{		
		
		//not working yet
		throw new exception("we cannot determine running wireless protocol for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."', because it has not been built ", 18902);
		if($this->_running_wireless_protocol == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}

		return $this->_running_wireless_protocol;
	}
}