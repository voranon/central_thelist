<?php

//exception codes 18200-18299

class thelist_routeros_config_snmp implements Thelist_Commander_pattern_interface_ideviceconfiguration
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
				
				//contact
				if ($metric->get_equipment_application_metric_id() == '15') {
					$return['configuration']['snmp_contact'] = $metric->get_equipment_application_metric_value();
				}
				
				//location
				if ($metric->get_equipment_application_metric_id() == '16') {
					$return['configuration']['snmp_location'] = $metric->get_equipment_application_metric_value();
				}
				
				//snmp version
				if ($metric->get_equipment_application_metric_id() == '17') {
					$return['configuration']['snmp_version'] = $metric->get_equipment_application_metric_value();
				}
				
				//snmp read or read-only or read-write
				if ($metric->get_equipment_application_metric_id() == '18') {
					
					if ($metric->get_equipment_application_metric_value() == 'Read-Only') {
						$return['configuration']['snmp_access'] = 'RO';
					} elseif ($metric->get_equipment_application_metric_value() == 'Read-Write') {
						$return['configuration']['snmp_access'] = 'RW';
					} else {
						throw new exception("routeros snmp authoritative_status value '".$metric->get_equipment_application_metric_value()."' is unknown ", 18200);
					}
				}
			}
			
			//snmp password is a device api credential, not part of the aplication metrics
			try {
				
				$snmp_credential = $this->_equipment->get_credential(6);
				$return['configuration']['snmp_password'] = $snmp_credential->get_device_password();
				
			} catch (Exception $e) {
		
				switch($e->getCode()){
			
					case 402;
					//402, no snmp has been defined, thats ok, but we do need to catch the exception
					break;
					default;
					throw $e;
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
		//currently only allows for a single snmp community
		$return_conf = "/snmp community\n";
		$return_conf .= "set 0";
		
		//snmp version
		if (isset($config_array['configuration']['snmp_access'])) {
		
			if ($config_array['configuration']['snmp_access'] == 'RO') {
				$return_conf .= " read-access=\"yes\" write-access=\"no\"";
			} elseif ($config_array['configuration']['snmp_access'] == 'RW') {
				$return_conf .= " read-access=\"yes\" write-access=\"yes\"";
			} else {
				//if we encounter a not valid access requirement, we shutdown all access
				$return_conf .= " read-access=\"no\" write-access=\"no\"";
			}
		}
		
		//snmp password
		if (isset($config_array['configuration']['snmp_password'])) {
			$return_conf .= " name=\"".$config_array['configuration']['snmp_password']."\"";
		}
		$return_conf .= "\n\n/snmp\n";
		$return_conf .= "set";
		
		if (isset($config_array['configuration']['administrative_status'])) {
		
			if ($config_array['configuration']['administrative_status'] == 1) {
				$return_conf .= " enabled=\"yes\"";
			} else {
				$return_conf .= " enabled=\"no\"";
			}
		}
		
		//snmp contact
		if (isset($config_array['configuration']['snmp_contact'])) {
			$return_conf .= " contact=\"".$config_array['configuration']['snmp_contact']."\"";
		}
		
		//snmp location
		if (isset($config_array['configuration']['snmp_location'])) {
			$return_conf .= " location=\"".$config_array['configuration']['snmp_location']."\"";
		}
		
		//snmp version
		if (isset($config_array['configuration']['snmp_version'])) {
			$return_conf .= " trap-version=\"".$config_array['configuration']['snmp_version']."\"";
		}
		
		//snmp community in use
		if (isset($config_array['configuration']['snmp_password'])) {
			$return_conf .= " trap-community=\"".$config_array['configuration']['snmp_password']."\"";
		}

		return $return_conf;
	}
}