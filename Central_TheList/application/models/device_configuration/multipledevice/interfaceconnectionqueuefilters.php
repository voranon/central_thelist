<?php

//exception codes 15000-15099

class thelist_multipledevice_config_interfaceconnectionqueuefilters implements Thelist_Commander_pattern_interface_ideviceconfiguration
{
	private $_interface;
	private $_equipment=null;

	public function __construct($interface)
	{
		$this->_interface 				= $interface;
	}

	public function generate_config_array()
	{
		//nothing here yet
	}
	
	public function generate_config_device_syntax($config_array)
	{
		throw new exception('this is a general multi device function, i cannot generate specific syntax', 15000);
	}
}