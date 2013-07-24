<?php

//exception codes 9800-9899

class thelist_cisco_config_interface implements Thelist_Commander_pattern_interface_ideviceconfiguration
{
	private $_equipment;
	private $_interface;
	
	public function __construct($equipment, $interface)
	{
		
		//only used to generate new config from databse, instanciate with null, null in string based model
		$this->_equipment 						= $equipment;
		$this->_interface						= $interface;
	}

	public function generate_config_array()
	{
		//interface configs are much the same because they are driven by the interface configuration system
		//a class has been setup to drive all common config array generation
		//if there are specific attributes for this type of device that will need to be brought in they should be added to the common
		//result her
		//there is no purpose running this method in a string based model, only objects can generate a config
		$config = new Thelist_Multipledevice_config_interface($this->_equipment, $this->_interface);
		return $config->generate_config_array();
	}
	
	public function generate_config_device_syntax($config_array)
	{
		
		//this method is not done, i did not see much purpose in a blanket config file generator for cisco 
		//as most items are changed one at a time.
		//however if we get to a point where we need to start creating files that are tftp to the switch
		//because there are config options that could conflict and cut off access to the switch then 
		//this method will have a purpose again
		
		//but doing single changes, by using a blanket config negates the purpose of the commander pattern
		throw new exception('stop right there this method is not finished and may never be', 9800);
		
		////set the variable for the return
	////	$return_conf = "";
		
		//always remove before enable, that way we dont negate the changes we just made
		
		//REMOVE SECTION
		
// 		if (isset($config_array['remove_configuration'])) {
				
			////interface speed
// 			if (isset($config_array['remove_configuration']['speed'])) {
			////	cisco can only have one speed on the interface
			////	so no need to go through array
// 				$return_conf .= "\nno speed ".$config_array['configuration']['speed']['0']."";
// 			}
			
		////	interface duplex
// 			if (isset($config_array['remove_configuration']['duplex'])) {
// 				$return_conf .= "\nno duplex ".$config_array['configuration']['duplex']."";
// 			}

		////	switchport mode
// 			if (isset($config_array['remove_configuration']['switch_port_mode'])) {
// 				$return_conf .= "\nno switchport mode ".$config_array['configuration']['switch_port_mode']."";
// 			}
			
		////	switchport native vlan 
// 			if (isset($config_array['remove_configuration']['switch_port_native_vlan'])) {
// 				$return_conf .= "\nno switchport trunk native vlan ".$config_array['configuration']['switch_port_native_vlan']."";
// 			}
			
		////	switchport administrative_status
// 			if (isset($config_array['remove_configuration']['administrative_status'])) {
			////	nothing i can do there, cant remove this config, if there is a change it will 
		////		be set later
// 			}
			
		////	switchport encapsulation, make sure this is done AFTER the switchport mode is changed
		////	you cannot remove the encapsulation if the interface is hardcoded to trunk
// 			if (isset($config_array['remove_configuration']['switch_port_encapsulation'])) {
// 				$return_conf .= "\nno switchport trunk encapsulation ".$config_array['configuration']['switch_port_encapsulation']."";
// 			}

// 		}
	}
}