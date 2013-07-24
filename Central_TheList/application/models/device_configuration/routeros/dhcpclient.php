<?php
class thelist_routeros_config_dhcpclient implements Thelist_Commander_pattern_interface_ideviceconfiguration
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
				
				//interface name
				if ($metric->get_equipment_application_metric_id() == '28') {
					$return['configuration']['dhcp_client_interface'] = $metric->get_equipment_application_metric_value();
				}
				
				//use received dns servers
				if ($metric->get_equipment_application_metric_id() == '29') {
					$return['configuration']['dhcp_client_use_received_dns_servers'] = $metric->get_equipment_application_metric_value();
				}
				
				//use received dns servers
				if ($metric->get_equipment_application_metric_id() == '30') {
					$return['configuration']['dhcp_client_use_received_ntp_servers'] = $metric->get_equipment_application_metric_value();
				}
				
				//use received default route
				if ($metric->get_equipment_application_metric_id() == '31') {
					$return['configuration']['dhcp_client_use_received_default_route'] = $metric->get_equipment_application_metric_value();
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
		$return_conf = "/ip dhcp-client\n";
		$return_conf .= "add";
		if (isset($config_array['configuration']['administrative_status'])) {
		
			if ($config_array['configuration']['administrative_status'] == 1) {
				$return_conf .= " disabled=\"no\"";
			} else {
				$return_conf .= " disabled=\"yes\"";
			}
		}
		
		if (isset($config_array['configuration']['dhcp_client_interface'])) {
			$return_conf .= " interface=\"".$config_array['configuration']['dhcp_client_interface']."\"";
		}
		
		if (isset($config_array['configuration']['dhcp_client_use_received_dns_servers'])) {
			
			if ($config_array['configuration']['dhcp_client_use_received_dns_servers'] == 1) {
				$return_conf .= " use-peer-dns=\"yes\"";
			} else {
				$return_conf .= " use-peer-dns=\"no\"";
			}
		}
		
		if (isset($config_array['configuration']['dhcp_client_use_received_ntp_servers'])) {
				
			if ($config_array['configuration']['dhcp_client_use_received_ntp_servers'] == 1) {
				$return_conf .= " use-peer-ntp=\"yes\"";
			} else {
				$return_conf .= " use-peer-ntp=\"no\"";
			}
		}
		
		if (isset($config_array['configuration']['dhcp_client_use_received_default_route'])) {
		
			if ($config_array['configuration']['dhcp_client_use_received_default_route'] == 1) {
				$return_conf .= " add-default-route=\"yes\"";
			} else {
				$return_conf .= " add-default-route=\"no\"";
			}
		}

		return $return_conf;
	}
}