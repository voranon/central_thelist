<?php 

//exception codes 5900-5999

class thelist_model_equipmentapplicationconfiguration
{
	
	private $_equipment_application_map_id;
	
	public function __construct()
	{
		//this is the reasoning for the dual approch (object -> array -> config file), i wrote it down because i keep forgetting

		//because we need the abillity to compare a config from a running service in the 
		//field on a device to the config we have in the database, we have to format the 
		//config as an array first. All our objects are deeply rooted in the database
		//and we therefore cannot create an object of the device service config.
		
		//there are many of the commander classes that format the device config then 
		//pulls the config from the database and compares
		
		//there are also many commander classes that receive orders to remove a specific 
		//item by another class, the requesting class will send the request and the commander class
		//generates the service config as array, finds the item to remove, pushes the array back to
		//this class and gets a corectly formatted config file back, sans the item to be removed.
		//then returns ok to the requesting class that can now remove the item from the database.
		//doing removals on device config any otherway was very complicated.

	}
	
	public function get_configuration_array($equipment_application_map_id)
	{
		$this->_equipment_application_map_id = $equipment_application_map_id;

		$sql = 	"SELECT * FROM equipment_application_mapping eam
				INNER JOIN equipment_applications ea ON ea.equipment_application_id=eam.equipment_application_id
				INNER JOIN equipments e ON e.eq_id=eam.eq_id
				WHERE eam.equipment_application_map_id='".$equipment_application_map_id."'
				";
		
		$result = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		if (isset($result['eq_type_id'])) {
			
			$sql4 = 	"SELECT * FROM equipment_types
						WHERE eq_type_id='".$result['eq_type_id']."'
						";
			
			$eq_type = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql4);
			
			if ($eq_type['eq_type_id'] == 3 && $result['equipment_application_id'] == 1) {
				return $this->generate_bairos_dhcp_server_configuration($result['eq_id']);
			} elseif ($eq_type['eq_manufacturer'] == 'Mikrotik' && $result['equipment_application_id'] == 1) {
				return $this->generate_mikrotik_dhcp_server_configuration($result['eq_id']);
			} elseif ($eq_type['eq_manufacturer'] == 'Mikrotik' && $result['equipment_application_id'] == 2) {
				return $this->generate_mikrotik_ntp_client_configuration($result['eq_id']);
			} elseif ($eq_type['eq_manufacturer'] == 'Mikrotik' && $result['equipment_application_id'] == 3) {
				return $this->generate_mikrotik_upnp_service_configuration($result['eq_id']);
			} elseif ($eq_type['eq_manufacturer'] == 'Mikrotik' && $result['equipment_application_id'] == 4) {
				return $this->generate_mikrotik_snmp_server_configuration($result['eq_id']);
			} elseif ($eq_type['eq_manufacturer'] == 'Mikrotik' && $result['equipment_application_id'] == 5) {
				return $this->generate_mikrotik_syslog_daemon_configuration($result['eq_id']);
			} elseif ($eq_type['eq_manufacturer'] == 'Mikrotik' && $result['equipment_application_id'] == 6) {
				return $this->generate_mikrotik_connection_tracking_service_configuration($result['eq_id']);
			} elseif ($eq_type['eq_manufacturer'] == 'Mikrotik' && $result['equipment_application_id'] == 7) {
				return $this->generate_mikrotik_dhcp_client_configuration($result['eq_id']);
			}
		}
	}
	
	public function create_configuration($equipment_application_map_id, $config_array, $return_format)
	{
		
		//we have this function so other classes can get the current configuration of some equipment
		//remove or add what they need and then return it to this class to get a file created
		$this->_equipment_application_map_id = $equipment_application_map_id;
	
	
		$sql = 	"SELECT * FROM equipment_application_mapping eam
				INNER JOIN equipment_applications ea ON ea.equipment_application_id=eam.equipment_application_id
				INNER JOIN equipments e ON e.eq_id=eam.eq_id
				WHERE eam.equipment_application_map_id='".$equipment_application_map_id."'
				";
	
		$result = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
	
		if (isset($result['eq_type_id'])) {
				
			$sql4 = 	"SELECT * FROM equipment_types
						WHERE eq_type_id='".$result['eq_type_id']."'
						";
			
			$eq_type = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql4);
			
			if ($eq_type['eq_type_id'] == 3 && $result['equipment_application_id'] == 1) {
				return $this->create_bairos_dhcp_server_device_configuration($config_array, $return_format);
			} elseif ($eq_type['eq_manufacturer'] == 'Mikrotik' && $result['equipment_application_id'] == 1) {
				return $this->create_mikrotik_dhcp_server_device_configuration($config_array, $return_format);
			} elseif ($eq_type['eq_manufacturer'] == 'Mikrotik' && $result['equipment_application_id'] == 2) {
				return $this->create_mikrotik_ntp_client_device_configuration($config_array, $return_format);
			} elseif ($eq_type['eq_manufacturer'] == 'Mikrotik' && $result['equipment_application_id'] == 3) {
				return $this->create_mikrotik_upnp_service_device_configuration($config_array, $return_format);
			} elseif ($eq_type['eq_manufacturer'] == 'Mikrotik' && $result['equipment_application_id'] == 4) {
				return $this->create_mikrotik_snmp_server_device_configuration($config_array, $return_format);
			} elseif ($eq_type['eq_manufacturer'] == 'Mikrotik' && $result['equipment_application_id'] == 5) {
				return $this->create_mikrotik_syslog_daemon_device_configuration($config_array, $return_format);
			} elseif ($eq_type['eq_manufacturer'] == 'Mikrotik' && $result['equipment_application_id'] == 6) {
				return $this->create_mikrotik_connection_tracking_service_device_configuration($config_array, $return_format);
			} elseif ($eq_type['eq_manufacturer'] == 'Mikrotik' && $result['equipment_application_id'] == 7) {
				return $this->create_mikrotik_dhcp_client_device_configuration($config_array, $return_format);
			}
		}
	}



	

	private function generate_bairos_dhcp_server_configuration($eq_id)
	{
		
		$sql = 	"SELECT * FROM equipment_application_metric_mapping eamm
				INNER JOIN equipment_application_metrics eame ON eame.equipment_application_metric_id=eamm.equipment_application_metric_id
				WHERE eamm.equipment_application_map_id='".$this->_equipment_application_map_id."'
				ORDER BY eamm.equipment_application_metric_index DESC
				";
		
		$app_metrics = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		if (isset($app_metrics['0'])) {
			
			//create return array
			$return['0']['configuration'] 	= array();
			$return['0']['networks'] 		= array();
			$return['0']['hosts'] 			= array();
			
			//global configuration
			foreach($app_metrics as $app_metric) {

				//domain name
				if ($app_metric['equipment_application_metric_name'] == 'Domain Name') {
					$return['0']['configuration']['domain_name'] = $app_metric['equipment_application_metric_value'];
				}
				
				//ddns_update_style
				if ($app_metric['equipment_application_metric_name'] == 'Dynamic DNS Update Style') {
					$return['0']['configuration']['ddns_update_style'] = $app_metric['equipment_application_metric_value'];
				}
				
				//default_lease_time
				if ($app_metric['equipment_application_metric_name'] == 'Default DHCP Lease Time') {
					$return['0']['configuration']['default_lease_time'] = $app_metric['equipment_application_metric_value'];
				}
				
				//max_lease_time
				if ($app_metric['equipment_application_metric_name'] == 'Max DHCP Lease Time') {
					$return['0']['configuration']['max_lease_time'] = $app_metric['equipment_application_metric_value'];
				}
				
				//authoritative_status
				if ($app_metric['equipment_application_metric_name'] == 'DNS Server Authoritative Status') {
					$return['0']['configuration']['authoritative_status'] = $app_metric['equipment_application_metric_value'];
				}
				
				//logging facilities
				if ($app_metric['equipment_application_metric_name'] == 'Syslog Facility') {
					$return['0']['configuration']['logging_facility'] = $app_metric['equipment_application_metric_value'];
				}
				
				//ntp servers
				if ($app_metric['equipment_application_metric_name'] == 'NTP Server IP') {

					$return['0']['configuration']['ntp_servers'][] = $app_metric['equipment_application_metric_value'];
				}

				//domain_name_servers
				if ($app_metric['equipment_application_metric_name'] == 'DNS Server IP') {
				
					$return['0']['configuration']['domain_name_servers'][] = $app_metric['equipment_application_metric_value'];
				}
			}
			
			$sql2 =	"SELECT * FROM equipments e
					INNER JOIN interfaces i ON i.eq_id=e.eq_id
					INNER JOIN ip_address_mapping ipam ON ipam.if_id=i.if_id
					INNER JOIN ip_addresses ipa ON ipa.ip_address_id=ipam.ip_address_id
					INNER JOIN ip_subnets ipsub ON ipsub.ip_subnet_id=ipa.ip_subnet_id
					WHERE e.eq_id='".$eq_id."'
					AND ipam.ip_address_map_type IN (88,89,90)
					";
			
			$dhcp_network_details = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
			
			
			//isolate the networks that should be part of the dhcp config
			if (isset($dhcp_network_details['0'])) {
				foreach($dhcp_network_details as $dhcp_network_detail) {
					
					//if this is a dhcp address not a connected address, lets make an array that holds the included subnets
					if ($dhcp_network_detail['ip_address_map_type'] == 89 || $dhcp_network_detail['ip_address_map_type'] == 90) {
						
						if (!isset($subnets_in_dhcp[$dhcp_network_detail['ip_subnet_id']])) {
							$subnets_in_dhcp[$dhcp_network_detail['ip_subnet_id']] = $dhcp_network_detail['ip_subnet_id'];
						}
					}
				}
				
				$i=0;
				foreach($dhcp_network_details as $dhcp_network_detail) {
					
					//if the address is part of a subnet that should be setup in dhcp
					if (isset($subnets_in_dhcp[$dhcp_network_detail['ip_subnet_id']])){
						
						//connected addresses are routers
						if ($dhcp_network_detail['ip_address_map_type'] == 88) {
							$return['0']['networks'][$dhcp_network_detail['ip_subnet_id']]['subnet'] 		= $dhcp_network_detail['ip_subnet_address'];
							$return['0']['networks'][$dhcp_network_detail['ip_subnet_id']]['cidr_mask']		= $dhcp_network_detail['ip_subnet_cidr_mask'];
							$return['0']['networks'][$dhcp_network_detail['ip_subnet_id']]['router'] 		= $dhcp_network_detail['ip_address'];
							
						}
						
						//collect the addresses we need to turn into static hosts
						if ($dhcp_network_detail['ip_address_map_type'] == 89) {
							
							if (!isset($ips_with_reservations)) {
								$ip_address_ids_with_reservations = '';
							}
							
							$ip_address_ids_with_reservations .= $dhcp_network_detail['ip_address_id'].",";
							
						}
						
						//are there any ranges
						if ($dhcp_network_detail['ip_address_map_type'] == 90) {
							
							$ip_addresses_in_ranges[$i]['ip_subnet_address'] 		= $dhcp_network_detail['ip_subnet_address'];
							$ip_addresses_in_ranges[$i]['ip_subnet_cidr_mask'] 		= $dhcp_network_detail['ip_subnet_cidr_mask'];
							$ip_addresses_in_ranges[$i]['ip_address'] 				= $dhcp_network_detail['ip_address'];
							
							$i++;
						}
						
					}
				}
				
				if (isset($ip_addresses_in_ranges)) {

					
					//get ranges instead of the induvidual ips
					$ip_converter 	= new Thelist_Utility_ipconverter();
					$ranges = $ip_converter->convert_ips_to_ranges($ip_addresses_in_ranges);
					
					if ($ranges != false) {

						foreach($ranges as $range) {
						
							foreach($return['0']['networks'] as $index => $network) {
								
								if ($network['subnet'] == $range['ip_subnet_address']) {
									
									$return['0']['networks'][$index]['ranges'] = $range['ranges'];
									
								}
							}
						}
					}
				}
			}
			
			if (isset($ip_address_ids_with_reservations)) {
								
				//we have to get all the other devices and their interface mac addresses
				//remove the last ',' from the $ip_address_ids_with_reservations if it is set
				
				$sql3 =	"SELECT * FROM interfaces i 
						INNER JOIN ip_address_mapping ipam ON ipam.if_id=i.if_id
						INNER JOIN ip_addresses ipa ON ipa.ip_address_id=ipam.ip_address_id
						INNER JOIN ip_subnets ipsub ON ipsub.ip_subnet_id=ipa.ip_subnet_id
						WHERE ipa.ip_address_id IN ('".substr($ip_address_ids_with_reservations, 0, -1)."')
						AND ipam.ip_address_map_type='91'
						";
					
				$ip_reservation_details = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql3);
				
				if (isset($ip_reservation_details['0'])) {
					
					$j=0;
					foreach($ip_reservation_details as $ip_reservation) {
						
						$return['0']['hosts'][$j]['name'] 			= "reservation_".str_replace('.', '_', $ip_reservation['ip_address'])."";
						$return['0']['hosts'][$j]['mac_address'] 	= $ip_reservation['if_mac_address'];
						$return['0']['hosts'][$j]['ip_address'] 	= $ip_reservation['ip_address'];
						
						$j++;
					}
				}
			}
			
			
			//before we return we reset the index
			$return['0']['networks'] = array_values($return['0']['networks']);

		} else {
			throw new exception('bairos dhcpserver is missing global configuration', 5900);
		}
		
		if (isset($return)) {
			return $return;
		} else {
			return false;			
		}
	}
	
	
	private function create_bairos_dhcp_server_device_configuration($config_arrays, $return_format)
	{

		
		
		
		if ($return_format == 'string') {
			
			return $return_conf;
			
		} elseif ($return_format == 'file'){
			
			//push to a file and return the absolute path
			
		} else {
			throw new exception('unknown return format for bairos dhcp config', 5901);
		}

	}
}
?>