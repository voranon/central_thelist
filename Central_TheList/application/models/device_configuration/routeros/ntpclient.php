<?php
class thelist_routeros_config_ntpclient implements Thelist_Commander_pattern_interface_ideviceconfiguration
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
				
				//ntp access mode
				if ($metric->get_equipment_application_metric_id() == '14') {
					$return['configuration']['ntp_access_mode'] = $metric->get_equipment_application_metric_value();
				}
				
				if ($metric->get_equipment_application_metric_id() == '7') {
					$return['configuration']['ntp_servers'][$metric->get_equipment_application_metric_index()] = $metric->get_equipment_application_metric_value();
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
		$return_conf = "/system ntp client\n";
		$return_conf .= "set";

		if (isset($config_array['configuration']['administrative_status'])) {
			
			if ($config_array['configuration']['administrative_status'] == 1) {
				$return_conf .= " enabled=\"yes\"";
			} else {
				$return_conf .= " enabled=\"no\"";
			}
		}
		
		//the way to access the ntp time server
		if (isset($config_array['configuration']['ntp_access_mode'])) {
			$return_conf .= " mode=\"".$config_array['configuration']['ntp_access_mode']."\"";
		}
		
		//the ntp server ips
		if (isset($config_array['configuration']['ntp_servers'])) {
			
			//there is only room for 2 ntp servers in mt config 
			if (isset($config_array['configuration']['ntp_servers']['0'])) {
				$return_conf .= " primary-ntp=\"".$config_array['configuration']['ntp_servers']['0']."\"";
			}
			
			if (isset($config_array['configuration']['ntp_servers']['1'])) {
				$return_conf .= " primary-ntp=\"".$config_array['configuration']['ntp_servers']['1']."\"";
			}
		}
		
		return $return_conf;
	}
}