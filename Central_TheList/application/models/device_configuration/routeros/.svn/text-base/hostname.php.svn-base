<?php
class thelist_routeros_config_hostname implements Thelist_Commander_pattern_interface_ideviceconfiguration
{
	private $_equipment;
	
	public function __construct($equipment)
	{
		$this->_equipment 						= $equipment;
	}

	public function generate_config_array()
	{
		//no empty fqdns
		if ($this->_equipment->get_eq_fqdn() != null && $this->_equipment->get_eq_fqdn() != '') {
			$return['configuration']['hostname'] = $this->_equipment->get_eq_fqdn();
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
		$return_conf = "/system identity";
		
		if (isset($config_array['configuration']['hostname'])) {
			$return_conf .= "\nset name=\"".$config_array['configuration']['hostname']."\"";
		}
	
		return $return_conf;
	}
}