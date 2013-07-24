<?php

//exception codes 19000-19099

class thelist_routeros_command_getinterfacessid implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	
	private $_ssid=null;

	
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

		$main_config_reply 	= $this->_device->execute_command("/interface wireless export");
		
		//ssid
		preg_match("/name=".$interface_name." .* ssid=\"?(.*?)\"? station-bridge-clone-mac/", $main_config_reply->get_message(), $raw_ssid);
		
		if (isset($raw_ssid['1'])) {
			$this->_ssid		 = $raw_ssid['1'];
		} else {
			throw new exception("we could not determine the ssid for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' ", 19000);
		}
		
	}

	public function get_ssid($refresh=true)
	{
		if($this->_ssid == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}

		return $this->_ssid;
	}
}