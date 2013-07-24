<?php

//exception codes 19140-19499

class thelist_routeros_command_getinterfacewirelesstxcenterfrequency implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	
	private $_wireless_tx_center_frequency=null;

	
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

		$main_config_reply 	= $this->_device->execute_command("/interface wireless print detail where name=\"".$interface_name."\"");
		
		//wireless center tx freq
		preg_match("/ frequency=\"?(.*?)\"? /", $main_config_reply->get_message(), $raw_tx_center);

		if (isset($raw_tx_center['1'])) {
			$this->_wireless_tx_center_frequency = $raw_tx_center['1'];
		} else {
			throw new exception("we could not determine the wireless center frequency for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' ", 19400);
		}
	}

	public function get_wireless_tx_center_frequency($refresh=true)
	{
		if($this->_wireless_tx_center_frequency == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}

		return $this->_wireless_tx_center_frequency;
	}
	
	
}