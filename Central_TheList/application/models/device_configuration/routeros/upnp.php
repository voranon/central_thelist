<?php
class thelist_routeros_config_upnp implements Thelist_Commander_pattern_interface_ideviceconfiguration
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
				
				if ($metric->get_equipment_application_metric_id() == '33') {
					$return['configuration']['upnp_dummy_rule'] = $metric->get_equipment_application_metric_value();
				}
	
				//ntp access mode
				if ($metric->get_equipment_application_metric_id() == '11') {
					$return['configuration']['upnp_allow_disable_wan'] = $metric->get_equipment_application_metric_value();
				}
	
				if ($metric->get_equipment_application_metric_id() == '26') {
					$return['configuration']['upnp_external_interfaces'][$metric->get_equipment_application_metric_index()] = $metric->get_equipment_application_metric_value();
				}
				
				if ($metric->get_equipment_application_metric_id() == '27') {
					$return['configuration']['upnp_trusted_interfaces'][$metric->get_equipment_application_metric_index()] = $metric->get_equipment_application_metric_value();
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
		$return_conf = "/ip upnp\n";
		$return_conf .= "set";
		
		if (isset($config_array['configuration']['administrative_status'])) {
				
			if ($config_array['configuration']['administrative_status'] == 1) {
				$return_conf .= " enabled=\"yes\"";
			} else {
				$return_conf .= " enabled=\"no\"";
			}
		}
		
		//can upnp disable the wan interface
		if (isset($config_array['configuration']['upnp_allow_disable_wan'])) {
			
			if ($config_array['configuration']['upnp_allow_disable_wan'] == 1) {
				$return_conf .= " allow-disable-external-interface=\"yes\"";
			} else {
				$return_conf .= " allow-disable-external-interface=\"no\"";
			}
		}
		
		//include a dummy rule in upnp
		if (isset($config_array['configuration']['upnp_dummy_rule'])) {
				
			if ($config_array['configuration']['upnp_dummy_rule'] == 1) {
				$return_conf .= " show-dummy-rule=\"yes\"";
			} else {
				$return_conf .= " show-dummy-rule=\"no\"";
			}
		}
		
		$return_conf .= "\n\n/ip upnp interfaces";
		
		if (isset($config_array['configuration']['upnp_external_interfaces'])) {
		
			foreach($config_array['configuration']['upnp_external_interfaces'] as $external_if) {
				$return_conf .= "\nadd disabled=\"no\" interface=\"".$external_if."\" type=\"external\"";
			}
		}
		
		if (isset($config_array['configuration']['upnp_trusted_interfaces'])) {
		
			foreach($config_array['configuration']['upnp_trusted_interfaces'] as $internal_if) {
				$return_conf .= "\nadd disabled=\"no\" interface=\"".$internal_if."\" type=\"internal\"";
			}
		}

		return $return_conf;
	}
}