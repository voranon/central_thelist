<?php

//exception codes 14700-14799

class thelist_bairos_config_interfaceconnectionqueues implements Thelist_Commander_pattern_interface_ideviceconfiguration
{
	private $_interface;

	public function __construct($interface)
	{
		$this->_interface 				= $interface;
	}

	public function generate_config_array()
	{
		//bandwidth configs are much the same because they are driven by the application configuration system
		//a class has been setup to drive all common config array generation
		//if there are specific attributes for this type of device that will need to be brought in they should be added to the common
		//result here
		$config 		= new Thelist_Multipledevice_config_interfaceconnectionqueues($this->_interface);
		$config_array	= $config->generate_config_array();;
		
		//so far bairos only does htb queue method, but that cannot be part of the general multidevice configure, so we add it here
		if ($config_array != false) {
			
			foreach ($config_array as $index => $queue) {
				$config_array[$index]['configuration']['queue_method'] = 'htb';
			}
		}
		return $config_array;
	}
	
	public function generate_config_device_syntax($config_array)
	{
		
	}
}