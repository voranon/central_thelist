<?php
class thelist_routeros_config_syslog implements Thelist_Commander_pattern_interface_ideviceconfiguration
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
				
				//The Action
				if ($metric->get_equipment_application_metric_id() == '6') {
					$return['configuration']['syslog_action_facility'] = $metric->get_equipment_application_metric_value();
				}
				
				if ($metric->get_equipment_application_metric_id() == '36') {
					$return['configuration']['syslog_action_type'] = $metric->get_equipment_application_metric_value();
				}
				
				if ($metric->get_equipment_application_metric_id() == '35') {
					$return['configuration']['syslog_action_name'] = $metric->get_equipment_application_metric_value();
				}
				
				if ($metric->get_equipment_application_metric_id() == '22') {
					$return['configuration']['syslog_action_remote_port'] = $metric->get_equipment_application_metric_value();
				}
				
				if ($metric->get_equipment_application_metric_id() == '24') {
					$return['configuration']['syslog_action_remote_server_ip'] = $metric->get_equipment_application_metric_value();
				}
				
				if ($metric->get_equipment_application_metric_id() == '23') {
					$return['configuration']['syslog_action_severity'] = $metric->get_equipment_application_metric_value();
				}
				
				if ($metric->get_equipment_application_metric_id() == '21') {
					$return['configuration']['syslog_action_target'] = $metric->get_equipment_application_metric_value();
				}
				
				//Rules
				
				if ($metric->get_equipment_application_metric_id() == '9') {
					$return['configuration']['rules'][$metric->get_equipment_application_metric_group_id()]['administrative_status'] = $metric->get_equipment_application_metric_value();
				}
				if ($metric->get_equipment_application_metric_id() == '32') {
					$return['configuration']['rules'][$metric->get_equipment_application_metric_group_id()]['syslog_rule_action'] = $metric->get_equipment_application_metric_value();
				}
				if ($metric->get_equipment_application_metric_id() == '20') {
					$return['configuration']['rules'][$metric->get_equipment_application_metric_group_id()]['syslog_rule_exclude_topic'][] = $metric->get_equipment_application_metric_value();
				}
				if ($metric->get_equipment_application_metric_id() == '19') {
					$return['configuration']['rules'][$metric->get_equipment_application_metric_group_id()]['syslog_rule_match_topic'][] = $metric->get_equipment_application_metric_value();
				}
				if ($metric->get_equipment_application_metric_id() == '34') {
					$return['configuration']['rules'][$metric->get_equipment_application_metric_group_id()]['syslog_rule_prefix'] = $metric->get_equipment_application_metric_value();
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
		$return_conf = "/system logging action\n";
		
		//im using the name to assert if there is any new action, or if this is just setting the default ones 
		//default cannot be added or removed, and the name is mandetory
		if (isset($config_array['configuration']['syslog_action_name'])) {
			
			if ($config_array['configuration']['syslog_action_name'] == 'memory') {
				$return_conf .= "set 0";
			} elseif ($config_array['configuration']['syslog_action_name'] == 'disk') {
				$return_conf .= "set 1";
			} elseif ($config_array['configuration']['syslog_action_name'] == 'echo') {
				$return_conf .= "set 2";
			} elseif ($config_array['configuration']['syslog_action_name'] == 'remote') {
				$return_conf .= "set 3";
			} else {
				$return_conf .= "add name=\"".$config_array['configuration']['syslog_action_name']."\"";
			}
		}
		
		//syslog_action_facility
		if (isset($config_array['configuration']['syslog_action_facility'])) {
			$return_conf .= " syslog-facility=\"".$config_array['configuration']['syslog_action_facility']."\"";
		}
		
		//syslog_action_remote_server_ip
		if (isset($config_array['configuration']['syslog_action_remote_server_ip'])) {
			$return_conf .= " remote=\"".$config_array['configuration']['syslog_action_remote_server_ip']."\"";
		}
		
		//syslog_action_target
		if (isset($config_array['configuration']['syslog_action_target'])) {
			$return_conf .= " target=\"".$config_array['configuration']['syslog_action_target']."\"";
		}
		
		//syslog_action_remote_port
		if (isset($config_array['configuration']['syslog_action_remote_port'])) {
			$return_conf .= " remote-port=\"".$config_array['configuration']['syslog_action_remote_port']."\"";
		}
		
		//syslog_action_severity
		if (isset($config_array['configuration']['syslog_action_severity'])) {
			$return_conf .= " syslog-severity=\"".$config_array['configuration']['syslog_action_severity']."\"";
		}
		
		//syslog_action_type
		if (isset($config_array['configuration']['syslog_action_type'])) {
			
			if ($config_array['configuration']['syslog_action_type'] == 'bsd-syslog') {
				$return_conf .= " bsd-syslog=\"yes\"";
			}
		}
		
		//next are the rules that come with the action, if there are any
		//in the newer versions there are default rules that cannot be removed
		//but we have chosen to disable them and if we need a rule that matches one of
		//the defaults then we create it.
		if (isset($config_array['configuration']['rules'])) {
			
			$return_conf .= "\n\n/system logging";
			
			foreach($config_array['configuration']['rules'] as $rule) {
				
				if (isset($topics)) {
					unset($topics);
				}
				
				$return_conf .= "\nadd";
				
				//syslog_action_severity
				if (isset($rule['administrative_status'])) {
					
					if ($rule['administrative_status'] == 1) {
						$return_conf .= " disabled=\"no\"";
					} else {
						$return_conf .= " disabled=\"yes\"";
					}
				}
				
				//any criteria to match
				if (isset($rule['syslog_rule_match_topic'])) {
					
					foreach($rule['syslog_rule_match_topic'] as $match_topic) {
						
						if (!isset($topics)) {
							$topics = $match_topic;
						} else {
							$topics .= "," . $match_topic;
						}
					}
				}
				
				//any criteria to exclude
				if (isset($rule['syslog_rule_exclude_topic'])) {
						
					foreach($rule['syslog_rule_exclude_topic'] as $exclude_topic) {
				
						if (!isset($topics)) {
							$topics = "!" . $exclude_topic;
						} else {
							$topics .= ",!" . $exclude_topic;
						}
					}
				}
				
				if (isset($topics)) {
					$return_conf .= " topics=\"".$topics."\"";
				}
				
				//syslog_rule_prefix
				if (isset($rule['syslog_rule_prefix'])) {
					$return_conf .= " prefix=\"".$rule['syslog_rule_prefix']."\"";
				}	

				//set the action
				if (isset($config_array['configuration']['syslog_action_name'])) {
					$return_conf .= " action=\"".$config_array['configuration']['syslog_action_name']."\"";
				}
			}
		}

		return $return_conf;
	}
}