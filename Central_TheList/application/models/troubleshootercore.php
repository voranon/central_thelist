<?php

//exception codes 7000-7099

class thelist_model_troubleshootercore
{
	public function __construct()
	{	
	}

	public function get_task_problems($task_obj)
	{
		//find out if this task is for an install
		
		$sql = "SELECT * FROM service_plan_quote_task_mapping spqtm
				WHERE spqtm.task_id='".$task_obj->get_task_id()."'
				";
			
		$is_service_plan_map = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		if (isset($is_service_plan_map['service_plan_quote_map_id'])) {
		
			$interface_paths			= new Thelist_Model_interfacepaths();
			
			$service_plan_quote_map 			= new Thelist_Model_serviceplanquotemap($is_service_plan_map['service_plan_quote_map_id']);	
			$paths								= $interface_paths->get_service_paths_from_service_plan_quote_map($service_plan_quote_map);

			$number_of_service_paths	= count($paths);
			
			if ($number_of_service_paths > 0) {
				$i=0;
				foreach ($paths as $path) {
					
					$problems	= $this->locate_path_equipment_issues($path);
					
					if ($problems != false) {

						$return[$i]['path'] 	= $path;
						$return[$i]['issues'] 	= $problems;
						$i++;
					}
				}
			}
		
		} else {
			//some other kind of task
		}
		
		if (isset($return)) {
			//return issues
			return $return;
		} else {
			//no issues
			return false;
		}
	}

	public function locate_path_equipment_issues($path_obj)
	{

		$path_equipments 				= $path_obj->get_path_equipment();
		$number_of_equipments_in_path	= count($path_equipments);

		if ($number_of_equipments_in_path > 0) {
		
			$i=0;
			foreach ($path_equipments as $path_equipment) {

				$eq_id	= $path_equipment['equipment']->get_eq_id();
				
				if ($path_equipment['equipment']->get_apis() != false) {
					
					try {
					
						$device = $path_equipment['equipment']->get_device();
					
					} catch (Exception $e) {
					
						switch($e->getCode()){
							case 1202;
							//unreachable
							$device = false;
							$return['equipment'][$eq_id]['equipment_obj']				= $path_equipment['equipment'];
							$return['equipment'][$eq_id]['status']['reachable']			= false;
							break;
							default;
							throw $e;
								
						}
					}
					
					
				} else {
					$device = false;
				}
					
				if (isset($path_equipment['inbound_interface'])) {
					
					$if_id	= $path_equipment['inbound_interface']->get_if_id();
					
					if ($device != false) {
						
						//interface operational status
						if ($device->get_interface_operational_status($path_equipment['inbound_interface']) === false) {
							$return['equipment'][$eq_id]['interfaces'][$if_id]['operational_status'] = 0;
						}
						
						$issues = $this->get_interface_config_mismatches($device, $path_equipment['inbound_interface']);
						
						if ($issues != false) {
							$return['equipment'][$eq_id]['interfaces'][$if_id]						= $issues;
							$return['equipment'][$eq_id]['interfaces'][$if_id]['interface_obj']		= $path_equipment['inbound_interface'];
						}
					}
				}
				
				if (isset($path_equipment['outbound_interface'])) {
						
					$if_id	= $path_equipment['outbound_interface']->get_if_id();
						
					if ($device != false) {

						//interface operational status
						if ($device->get_interface_operational_status($path_equipment['outbound_interface']) === false) {
							$return['equipment'][$eq_id]['interfaces'][$if_id]['operational_status'] = 0;
						}
						
						$issues = $this->get_interface_config_mismatches($device, $path_equipment['outbound_interface']);
				
						if ($issues != false) {
							$return['equipment'][$eq_id]['interfaces'][$if_id]						= $issues;
							$return['equipment'][$eq_id]['interfaces'][$if_id]['interface_obj']		= $path_equipment['outbound_interface'];
						}
					}
				}
				
				$i++;
			}
			
			if (isset($return)) {
				//return issues
				return $return;
			} else {
				//no issues
				return false;
			}

		} else {
			//no equipment in path, so it is clean, no problems
			return false;
		}	
	}
	
	public function get_interface_config_mismatches($device, $interface)
	{
		//induvidual configs
		if ($interface->get_interface_configurations() != null) {
			
			foreach ($interface->get_interface_configurations() as $interface_config) {
				
				//interface admin status
				if ($interface_config->get_if_conf_id() == 8) {
					
					$device_result 	= $device->get_interface_administrative_status($interface);
					$db_result 		= $interface_config->get_mapped_configuration_value_1();
					
					if ($device_result != $db_result) {
						$return['administrative_status'] = $interface_config;
					}
				}
				
				//interface duplex
				if ($interface_config->get_if_conf_id() == 12) {
					
					$device_result 	= $device->get_interface_duplex($interface);
					$db_result 		= $interface_config->get_mapped_configuration_value_1();
						
					if ($device_result != $db_result) {
						$return['duplex'] = $interface_config;
					}
				}
				
				//interface l3 mtu
				if ($interface_config->get_if_conf_id() == 1) {
					
					$device_result 	= $device->get_interface_l3_mtu($interface);
					$db_result 		= $interface_config->get_mapped_configuration_value_1();
					
					if ($device_result != $db_result) {
						$return['l3_mtu'] = $interface_config;
					}
				}
				
				//interface l2 mtu
				if ($interface_config->get_if_conf_id() == 30) {
					
					$device_result 	= $device->get_interface_l2_mtu($interface);
					$db_result 		= $interface_config->get_mapped_configuration_value_1();
					
					if ($device_result != $db_result) {
						$return['l2_mtu'] = $interface_config;
					}
				}
				
				//interface speed
				if ($interface_config->get_if_conf_id() == 11) {
					
					$device_result 	= $device->get_interface_speed($interface);
					$db_result 		= $interface_config->get_mapped_configuration_value_1();
					
					$result = $this->compare_variables_that_may_be_single_dimentional_arrays($device_result, $db_result);
					
					if ($result === false) {
						$return['speed'] = $interface_config;
					}
				}
				
				//interface SSID
				if ($interface_config->get_if_conf_id() == 2) {
					
					$device_result 	= $device->get_interface_ssid($interface);
					$db_result 		= $interface_config->get_mapped_configuration_value_1();
					
					if ($device_result != $db_result) {
						$return['ssid'] = $interface_config;
					}
				}
				
				//interface Wireless Mode or switch port mode trunk or access
				if ($interface_config->get_if_conf_id() == 3) {
					
					$device_result 	= $device->get_interface_mode($interface);
					$db_result 		= $interface_config->get_mapped_configuration_value_1();
					
					if ($device_result != $db_result) {
						$return['interface_mode'] = $interface_config;
					}
				}
				
				//interface wireless protocol
				if ($interface_config->get_if_conf_id() == 4) {
					
					$device_result 	= $device->get_interface_wireless_protocol($interface);
					$db_result 		= $interface_config->get_mapped_configuration_value_1();
					
					if ($device_result != $db_result) {
						$return['wireless_band'] = $interface_config;
					}
				}
				
				//interface wireless tx frequency
				if ($interface_config->get_if_conf_id() == 6) {
					
					$device_result 	= $device->get_interface_wireless_tx_frequency($interface);
					$db_result 		= $interface_config->get_mapped_configuration_value_1();
					
					if ($device_result != $db_result) {
						$return['wireless_tx_frequency'] = $interface_config;
					}
				}
				
				//interface wireless rx frequency
				if ($interface_config->get_if_conf_id() == 28) {
					
					$device_result 	= $device->get_interface_wireless_rx_frequency($interface);
					$db_result 		= $interface_config->get_mapped_configuration_value_1();
					
					if ($device_result != $db_result) {
						$return['wireless_rx_frequency'] = $interface_config;
					}
				}
				
				//interface wireless tx channel width
				if ($interface_config->get_if_conf_id() == 7) {
					
					$device_result 	= $device->get_interface_wireless_tx_channel_width($interface);
					$db_result 		= $interface_config->get_mapped_configuration_value_1();
					
					$result = $this->compare_variables_that_may_be_single_dimentional_arrays($device_result, $db_result);
					
					if ($result === false) {
						$return['wireless_tx_channel_width'] = $interface_config;
					}
				}
				
				//interface wireless rx channel width
				if ($interface_config->get_if_conf_id() == 29) {
					
					$device_result 	= $device->get_interface_wireless_rx_channel_width($interface);
					$db_result 		= $interface_config->get_mapped_configuration_value_1();
					
					$result = $this->compare_variables_that_may_be_single_dimentional_arrays($device_result, $db_result);
					
					if ($result === false) {
						$return['wireless_rx_channel_width'] = $interface_config;
					}
				}
				
				//interface wireless authentication type
				if ($interface_config->get_if_conf_id() == 20) {
					
					$device_result 	= $device->get_interface_wireless_authentication_type($interface);
					$db_result 		= $interface_config->get_mapped_configuration_value_1();

					$result = $this->compare_variables_that_may_be_single_dimentional_arrays($device_result, $db_result);
						
					if ($result === false) {
						$return['wireless_authentication_type'] = $interface_config;
					}
				}
				
				//interface wireless Encryption Unicast Ciphers
				if ($interface_config->get_if_conf_id() == 18) {
					
					$device_result 	= $device->get_interface_wireless_unicast_ciphers($interface);
					$db_result 		= $interface_config->get_mapped_configuration_value_1();
					
					$result = $this->compare_variables_that_may_be_single_dimentional_arrays($device_result, $db_result);
					
					if ($result === false) {
						$return['wireless_unicast_ciphers'] = $interface_config;
					}
				}
				
				//interface wireless Encryption Group Ciphers
				if ($interface_config->get_if_conf_id() == 19) {
					
					$device_result 	= $device->get_interface_wireless_group_ciphers($interface);
					$db_result 		= $interface_config->get_mapped_configuration_value_1();

					$result = $this->compare_variables_that_may_be_single_dimentional_arrays($device_result, $db_result);
					
					if ($result === false) {
						$return['wireless_group_ciphers'] = $interface_config;
					}
				}
				
				//interface wireless Shared Encryption Key
				if ($interface_config->get_if_conf_id() == 21) {
					
					$device_result 	= $device->get_interface_wireless_encryption_key($interface);
					$db_result 		= $interface_config->get_mapped_configuration_value_1();
					
					if ($device_result != $db_result) {
						$return['wireless_encryption_key'] = $interface_config;
					}
				}
				
				//interface vlan id
				if ($interface_config->get_if_conf_id() == 22) {
					
					$device_result 	= $device->get_interface_vlan_id($interface);
					$db_result 		= $interface_config->get_mapped_configuration_value_1();
					
					if ($device_result != $db_result) {
						$return['interface_vlan_id'] = $interface_config;
					}
				}
				
				//switch transit vlan
				if ($interface_config->get_if_conf_id() == 24) {
					
					$device_result 	= $device->get_interface_switch_allowed_transit_vlans($interface);
					$db_result 		= $interface_config->get_mapped_configuration_value_1();
					
					$result = $this->compare_variables_that_may_be_single_dimentional_arrays($device_result, $db_result);
					
					if ($result === false) {
						$return['switch_allowed_transit_vlans'] = $interface_config;
					}
				}
				
				//switch port native vlan
				if ($interface_config->get_if_conf_id() == 25) {
					
					$device_result 	= $device->get_interface_switch_port_native_vlan($interface);
					$db_result 		= $interface_config->get_mapped_configuration_value_1();
					
					if ($device_result != $db_result) {
						$return['switch_port_native_vlan'] = $interface_config;
					}
				}
				
				//switch port allow vlan id to trunk
				if ($interface_config->get_if_conf_id() == 26) {
					
					$device_result 	= $device->get_interface_switch_port_vlans_allowed_trunking($interface);
					$db_result 		= $interface_config->get_mapped_configuration_value_1();
					
					$result = $this->compare_variables_that_may_be_single_dimentional_arrays($device_result, $db_result);
					
					if ($result === false) {
						$return['switch_port_vlans_allowed_trunking'] = $interface_config;
					}
				}
				
				//switch port deny vlan id to trunk
				if ($interface_config->get_if_conf_id() == 27) {
					
					$device_result 	= $device->get_interface_switch_port_vlans_deny_trunking($interface);
					$db_result 		= $interface_config->get_mapped_configuration_value_1();
					
					$result = $this->compare_variables_that_may_be_single_dimentional_arrays($device_result, $db_result);
					
					if ($result === false) {
						$return['switch_port_vlans_deny_trunking'] = $interface_config;
					}
				}
				
				//switch port encapsulation
				if ($interface_config->get_if_conf_id() == 31) {
					
					$device_result 	= $device->get_interface_switch_port_encapsulation($interface);
					$db_result 		= $interface_config->get_mapped_configuration_value_1();
					
					if ($device_result != $db_result) {
						$return['switch_port_encapsulation'] = $interface_config;
					}
				}
				
				//interface description
				if ($interface_config->get_if_conf_id() == 32) {
					
					$device_result 	= $device->get_interface_description($interface);
					$db_result 		= $interface_config->get_mapped_configuration_value_1();
					
					if ($device_result != $db_result) {
						$return['interface_description'] = $interface_config;
					}
				}
				
				//interface boot protocol
				if ($interface_config->get_if_conf_id() == 33) {
					
					$device_result 	= $device->get_interface_boot_protocol($interface);
					$db_result 		= $interface_config->get_mapped_configuration_value_1();
					
					if ($device_result != $db_result) {
						$return['interface_boot_protocol'] = $interface_config;
					}
				}
			}
		}
		
		if (isset($return)) {
			//return issues
			return $return;
		} else {
			//no issues
			return false;
		}
		
	}
	
	
	private function compare_variables_that_may_be_single_dimentional_arrays($var_1, $var_2)
	{
		if (is_array($var_1)) {
	
			if (is_array($var_2)) {
	
				$result = count(array_diff($var_1, $var_2)) + count(array_diff($var_2, $var_1));
					
				if ($result == 0) {
					return true;
				} else {
					return false;
				}
					
			} else {
				return false;
			}
	
		} else {
	
			if (is_array($var_2)) {
				return false;
			} else {
					
				if ($var_1 != $var_2) {
					return false;
				} else {
					return true;
				}
			}
		}
	}
}
?>