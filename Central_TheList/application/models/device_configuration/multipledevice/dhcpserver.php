<?php

//exception codes 14100-14199

class thelist_multipledevice_config_dhcpserver implements Thelist_Commander_pattern_interface_ideviceconfiguration
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
	
			//create return array
			$return['configuration'] 	= array();
			$return['networks'] 		= array();
			$return['hosts'] 			= array();
	
			//global configuration
			foreach($metrics as $metric) {
	
				//Domain name
				if ($metric->get_equipment_application_metric_id() == '1') {
					$return['configuration']['domain_name'] = $metric->get_equipment_application_metric_value();
				}
				
				//default_lease_time
				if ($metric->get_equipment_application_metric_id() == '3') {
					$return['configuration']['default_lease_time'] = $metric->get_equipment_application_metric_value();
				}
	
				//max_lease_time
				if ($metric->get_equipment_application_metric_id() == '4') {
					$return['configuration']['max_lease_time'] = $metric->get_equipment_application_metric_value();
				}
	
				//dhcp server interface name
				if ($metric->get_equipment_application_metric_id() == '13') {
					$return['configuration']['interface_name'] = $metric->get_equipment_application_metric_value();
				}
	
				//authoritative_status
				if ($metric->get_equipment_application_metric_id() == '5') {
					$return['configuration']['authoritative_status'] = $metric->get_equipment_application_metric_value();
				}
	
				//boot-p support type
				if ($metric->get_equipment_application_metric_id() == '12') {
					$return['configuration']['boot_p_support_type'] = $metric->get_equipment_application_metric_value();
				}
	
				//service admin status
				if ($metric->get_equipment_application_metric_id() == '9') {
					$return['configuration']['administrative_status'] = $metric->get_equipment_application_metric_value();
				}
	
				//domain_name_servers
				if ($metric->get_equipment_application_metric_id() == '8') {
					$return['configuration']['domain_name_servers'][] = $metric->get_equipment_application_metric_value();
				}
	
				//ntp_servers
				if ($metric->get_equipment_application_metric_id() == '7') {
					$return['configuration']['ntp_servers'][] = $metric->get_equipment_application_metric_value();
				}
			}
			
			$sql =	"SELECT item_id FROM items
					WHERE item_type='ip_address_map_type'
					AND item_name='dhcp_range'
					";
			
			$item_id_range_ip = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
			$sql =	"SELECT item_id FROM items
					WHERE item_type='ip_address_map_type'
					AND item_name='connected'
					";
				
			$item_id_connected_ip = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
			$sql =	"SELECT item_id FROM items
					WHERE item_type='ip_address_map_type'
					AND item_name='dhcp_reservation'
					";
				
			$item_id_reservation_ip = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
			$sql =	"SELECT item_id FROM items
					WHERE item_type='ip_address_map_type'
					AND item_name='dhcp_lease'
					";
			
			$item_id_lease_ip = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
	
	
			$sql2 =	"SELECT * FROM equipments e
					INNER JOIN interfaces i ON i.eq_id=e.eq_id
					INNER JOIN ip_address_mapping ipam ON ipam.if_id=i.if_id
					INNER JOIN ip_addresses ipa ON ipa.ip_address_id=ipam.ip_address_id
					INNER JOIN ip_subnets ipsub ON ipsub.ip_subnet_id=ipa.ip_subnet_id
					WHERE e.eq_id='".$this->_equipment->get_eq_id()."'
					AND i.if_name='".$return['configuration']['interface_name']."'
					AND ipam.ip_address_map_type IN (".$item_id_connected_ip.",".$item_id_reservation_ip.",".$item_id_range_ip.")
					";
	
			$dhcp_network_details = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);

			//isolate the networks that should be part of the dhcp config
			if (isset($dhcp_network_details['0'])) {
				foreach($dhcp_network_details as $dhcp_network_detail) {
	
					//if this is a dhcp address not a connected address, lets make an array that holds the included subnets
					if ($dhcp_network_detail['ip_address_map_type'] == $item_id_reservation_ip || $dhcp_network_detail['ip_address_map_type'] == $item_id_range_ip) {
							
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
						if ($dhcp_network_detail['ip_address_map_type'] == $item_id_connected_ip) {
							$return['networks'][$dhcp_network_detail['ip_subnet_id']]['subnet'] 		= $dhcp_network_detail['ip_subnet_address'];
							$return['networks'][$dhcp_network_detail['ip_subnet_id']]['cidr_mask']		= $dhcp_network_detail['ip_subnet_cidr_mask'];
							$return['networks'][$dhcp_network_detail['ip_subnet_id']]['router'] 		= $dhcp_network_detail['ip_address'];
	
						}
							
						//collect the addresses we need to turn into static hosts
						if ($dhcp_network_detail['ip_address_map_type'] == $item_id_reservation_ip) {
	
							if (!isset($ip_address_ids_with_reservations)) {
								$ip_address_ids_with_reservations = $dhcp_network_detail['ip_address_id'];
							} else {
								$ip_address_ids_with_reservations .= "," . $dhcp_network_detail['ip_address_id'];
							}
						}
							
						//are there any ranges
						if ($dhcp_network_detail['ip_address_map_type'] == $item_id_range_ip) {
	
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
								
							foreach($return['networks'] as $index => $network) {
									
								if ($network['subnet'] == $range['ip_subnet_address']) {
	
									$return['networks'][$index]['ranges'] = $range['ranges'];
	
								}
							}
						}
					}
				}
			}

			if (isset($ip_address_ids_with_reservations)) {
					
				//we have to get all the other devices and their interface mac addresses
				$sql3 =	"SELECT * FROM interfaces i
						INNER JOIN ip_address_mapping ipam ON ipam.if_id=i.if_id
						INNER JOIN ip_addresses ipa ON ipa.ip_address_id=ipam.ip_address_id
						INNER JOIN ip_subnets ipsub ON ipsub.ip_subnet_id=ipa.ip_subnet_id
						WHERE ipa.ip_address_id IN (".$ip_address_ids_with_reservations.")
						AND ipam.ip_address_map_type='".$item_id_lease_ip."'
						";
					
				$ip_reservation_details = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql3);

				if (isset($ip_reservation_details['0'])) {
	
					$j=0;
					foreach($ip_reservation_details as $ip_reservation) {
							
						$return['hosts'][$j]['name'] 			= "reservation_".str_replace('.', '_', $ip_reservation['ip_address'])."";
						$return['hosts'][$j]['mac_address'] 	= $ip_reservation['if_mac_address'];
						$return['hosts'][$j]['ip_address'] 		= $ip_reservation['ip_address'];
							
						$j++;
					}
				}
			}
	
	
			//before we return we reset the index
			$return['networks'] = array_values($return['networks']);
	
		} else {
			throw new exception('dhcpserver is missing global configuration', 14101);
		}
	
	
		if (isset($return)) {
			return $return;
		} else {
			return false;
		}
	}
	
	public function generate_config_device_syntax($config_array)
	{
		throw new exception('this is a general multi device function, i cannot generate specific syntax', 14100);
	}
}