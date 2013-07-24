<?php
class thelist_routeros_config_apicredential implements Thelist_Commander_pattern_interface_ideviceconfiguration
{
	private $_equipment;
	private $_apicredential;
	
	public function __construct($equipment, $apicredential)
	{
		$this->_equipment 						= $equipment;
		$this->_apicredential					= $apicredential;
	}	

	public function generate_config_array()
	{
		
		//api_name
		if ($this->_apicredential->get_device_api_name() != null) {
			$return['configuration']['api_name'] = $this->_apicredential->get_device_api_name();
		}
		
		//username
		if ($this->_apicredential->get_device_username() != null) {
			$return['configuration']['username'] = $this->_apicredential->get_device_username();
		}
		
		//password
		if ($this->_apicredential->get_device_password() != null) {
			$return['configuration']['password'] = $this->_apicredential->get_device_password();
		}

		if (isset($return)) {
			return $return;
		} else {
			return false;
		}
	}
	
	public function generate_config_device_syntax($config_array)
	{
		//set the variable for the return
		$return_conf = "/user";
		
		//for non snmp credentials, they for router os all go to the users class
		//while snmp is in snmp and is being handled by the snmp application
		if ($config_array['configuration']['api_name'] != 'snmp') {
			
			if (isset($config_array['configuration']['username'])) {
					
				if ($config_array['configuration']['username'] == 'admin') {
					$return_conf .= "\nset [find where name=\"admin\"]";
				} elseif ($config_array['configuration']['username'] == 'bai_admin') {
					$return_conf .= "\nset [find where name=\"bai_admin\"]";
				} elseif ($config_array['configuration']['username'] == 'bai_tech') {
					$return_conf .= "\nset [find where name=\"bai_tech\"]";
				} else {
					$return_conf .= "\nadd name=\"".$config_array['configuration']['username']."\"]";
				}
			}
			
			if (isset($config_array['configuration']['password'])) {
				$return_conf .= " password=\"".$config_array['configuration']['password']."\"";
			}

		}

		return $return_conf;
	}
}