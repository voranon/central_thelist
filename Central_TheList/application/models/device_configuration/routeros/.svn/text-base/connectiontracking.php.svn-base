<?php
class thelist_routeros_config_connectiontracking implements Thelist_Commander_pattern_interface_ideviceconfiguration
{
	private $_equipment;
	private $_equipment_application;
	
	public function __construct($equipment, $equipment_application)
	{
		$this->_equipment 						= $equipment;
		$this->_equipment_application			= $equipment_application;
	}

	public function generate_config_array()
	{
		$metrics 			= $this->_equipment_application->get_metric_mappings();
	
		if ($metrics != null) {
	
			$return['configuration'] 	= array();
	
			foreach($metrics as $metric) {
					
				//service admin status
				if ($metric->get_equipment_application_metric_id() == '9') {
					$return['configuration']['administrative_status'] = $metric->get_equipment_application_metric_value();
				}
				
				//service admin status
				if ($metric->get_equipment_application_metric_id() == '25') {
					$return['configuration']['tracking_timeout'] = $metric->get_equipment_application_metric_value();
				}
			}
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
		$return_conf = "/ip firewall connection tracking\n";
		$return_conf .= "set";
	
		if (isset($config_array['configuration']['administrative_status'])) {
		
			if ($config_array['configuration']['administrative_status'] == 1) {
				$return_conf .= " enabled=\"yes\"";
			} else {
				$return_conf .= " enabled=\"no\"";
			}
		}
		
		//generic-timeout
		if (isset($config_array['configuration']['tracking_timeout'])) {
			
			$time_obj = new Thelist_Utility_time();
			
			$return_conf .= " generic-timeout=\"".$time_obj->convert_seconds_to_mikrotik_time_format($config_array['configuration']['tracking_timeout'])."\"";
		}

		return $return_conf;
	}
}