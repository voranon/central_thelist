<?php
class thelist_routeros_config_ipaddress implements Thelist_Commander_pattern_interface_ideviceconfiguration
{
	private $_interface;
	private $_ip_address;
	
	public function __construct($interface, $ip_address)
	{
		$this->_interface 						= $interface;
		$this->_ip_address						= $ip_address;
	}

	public function generate_config_array()
	{
		$return['configuration']['ip_address'] 		= $this->_ip_address->get_ip_address();
		$return['configuration']['subnet_mask'] 	= $this->_ip_address->get_ip_subnet_cidr_mask();

		if (isset($return)) {
			return $return;
		} else {
			return false;
		}
	}
	
	public function generate_config_device_syntax($config_array)
	{
		//set the variable for the return
		$return_conf = "/ip address\n";
		$return_conf .= "add";

		if (isset($config_array['configuration']['ip_address'])) {
			$return_conf .= " address=\"".$config_array['configuration']['ip_address']."\"";
		}
		
		if (isset($config_array['configuration']['subnet_mask'])) {
			
			$ip_convert = new Thelist_Utility_ipconverter();
			$return_conf .= " netmask=\"".$ip_convert->convert_cidr_subnet_to_dotted($config_array['configuration']['subnet_mask'])."\"";
		}

		$return_conf .= " interface=\"".$this->_interface->get_if_name()."\"";

		return $return_conf;
	}
}