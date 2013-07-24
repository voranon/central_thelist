<?php

//exception codes 2000-2099

//DO NOT RELY ON METHODS from THIS CLASS
//they WILL CHANGE

class thelist_model_equipmentdefaultprovisioningplan
{
	private $_equipment=null;

	private $_configured_resources=null;
	private $_download_queue_interface=null;
	private $_upload_queue_interface=null;

	private $_eq_default_prov_plan_id;
	private $_time;
	
	private $_customer_wan_interface=null;
	private $_border_routers=null;
	private $_edge_switches=null;
	private $_distribution_switches=null;
	private $_equipment_updates=array();
	private $_monitoring_updates=array();
	private $_border_router_this_install=null;
	
	public function __construct($eq_default_prov_plan_id)
	{
		$this->_eq_default_prov_plan_id	= $eq_default_prov_plan_id;
		$this->_time				= Zend_Registry::get('time');

	}
	
	public function provision_equipment_in_database($equipment_obj)
	{
		$this->_equipment = $equipment_obj;
	
		if ($this->_eq_default_prov_plan_id == 1) {
			
			//cpe routers
			$this->cpe_router_db_config();
			
		} elseif ($this->_eq_default_prov_plan_id == 2) {

		} else {
			
			throw new exception('we dont know how to provision that plan', 2004);
			
		}
	}

	private function get_cpe_border_and_dist_equipment($cpe_interface_obj)
	{
		//get the service point interface
		$servicepointresourcelocator = new Thelist_Model_servicepointresourcelocator();
		$sp_interface	= $servicepointresourcelocator->get_service_point_interface_from_cpe($cpe_interface_obj);

		if ($sp_interface != false ) {

			//reset the paths
			$servicepointresourcelocator->set_paths(null);
			//locate the border routers
			$border_routers						= $servicepointresourcelocator->get_border_routers($sp_interface['inbound_interface']);
			if($border_routers != false) {
				$return_array['border_routers'] = $border_routers;
			}
			
			//find edge switches
			$edge_switches				= $servicepointresourcelocator->get_edge_switches($sp_interface['inbound_interface']);
			if($edge_switches != false) {
				$return_array['edge_switches'] = $edge_switches;
			
			}
			
			//find distribution switches
			$distribution_switches		= $servicepointresourcelocator->get_intermediate_switches($sp_interface['inbound_interface']);
			if($distribution_switches != false) {
				$return_array['distribution_switches'] = $distribution_switches;	
			}
			
			if (isset($return_array)) {
				return $return_array;				
			} else {
				return false;
			}
			
		} else {
			throw new exception('cant find the interface in the service point', 2003);
		}

	}
	
	private function default_mikrotik_cpe_db_config($equipment_obj)
	{
		//get the wireless interface
		$wireless_interface = $this->get_cpe_wireless_interface($equipment_obj);
		
		//all lan interfaces, we know the equipment has interfaces because we already found 2
		foreach($equipment_obj->get_interfaces() as $single_interface) {
				
			//cannot be WAN 
			if ($single_interface->get_if_id() != $this->_customer_wan_interface->get_if_id()) {
				$lan_interfaces[]	= $single_interface;
			}

			//cannot set duplex for wireless
			if ($single_interface->get_if_id() != $wireless_interface->get_if_id()) {
				$single_interface->add_new_interface_configuration(12, 'auto');
			}
			
			//set l3 mtu
			$single_interface->add_new_interface_configuration(1, 1500);
			//set interface admin status
			$single_interface->add_new_interface_configuration(8, 1);
			
			//set interface speed
			$single_interface->add_new_interface_configuration(11, 'auto');
		}
		
		$new_credential = new Thelist_Model_deviceauthenticationcredential();
		
		//now we need to generate a random SSID key using credential class
		$wireless_interface->add_new_interface_configuration(2, $new_credential->get_random_word());
		
		//set interface mode
		$wireless_interface->add_new_interface_configuration(3, 'accesspoint');
		//set interface band
		$wireless_interface->add_new_interface_configuration(4, '802.11bgn');
		//set interface tx frequency
		$wireless_interface->add_new_interface_configuration(6, 2462);
		//set interface tx_channel_width
		$wireless_interface->add_new_interface_configuration(7, 20);
		//set interface authentication_type
		$wireless_interface->add_new_interface_configuration(20, 'wpa-psk');
		//set interface authentication_type
		$wireless_interface->add_new_interface_configuration(20, 'wpa2-psk');
		//set interface unicast_encryption_cipher
		$wireless_interface->add_new_interface_configuration(18, 'tkip');
		//set interface unicast_encryption_cipher
		$wireless_interface->add_new_interface_configuration(18, 'aes');
		//set interface group_encryption_cipher
		$wireless_interface->add_new_interface_configuration(19, 'tkip');
		//set interface group_encryption_cipher
		$wireless_interface->add_new_interface_configuration(19, 'aes');

		//now we need to generate a random encryption key using credential class
		
		$encryption_key = $new_credential->get_random_string_value(10, 'wpa_encryption_key');
		//set interface shared encryption key
		$wireless_interface->add_new_interface_configuration(21, $encryption_key);
		

		//create bridge with all lan interfaces
		$lan_bridge_name = 'Switch';
		$lan_bridge	= $equipment_obj->create_bridge_interface($lan_interfaces, $lan_bridge_name);
		//set interface admin status
		$lan_bridge->add_new_interface_configuration(8, 1);
		
		//create vlan 10 interface for wan
		$wan_vlan_interface	= $equipment_obj->add_new_interface(null, "".$this->_customer_wan_interface->get_if_name().".10", 95, 'na', null, $this->_customer_wan_interface->get_if_id(), false);
		
		//use the same mac address as the parent
		$wan_vlan_interface->set_if_mac_address($this->_customer_wan_interface->get_if_mac_address());
		
		//this is a sub interface and it requires vlan id
		$wan_vlan_interface->add_new_interface_configuration(22, 10);
		//set interface l3 mtu
		$wan_vlan_interface->add_new_interface_configuration(1, 1500);
		//set interface admin status
		$wan_vlan_interface->add_new_interface_configuration(8, 1);
		
		//the default pe configuration assigns the ipadress to the physical interface
		//we need to move it to the vlan we just created
		
		//since this is a vlan on the physical wan interface, it has the physical interface as its master
		$master_interface = $wan_vlan_interface->get_slave_relationships();
		
		//we need to maintain the object model so we need to use the interface that is part of our equipment object and not
		//the master interface, because it was instanciated by the interface class and is NOT part of the cpe equipment object.
		//it can also only have one master because we just created it.
		$physical_wan_if = $equipment_obj->get_interface($master_interface['0']->get_if_id());
		
		$ips_on_physical_wan = $physical_wan_if->get_ip_addresses();
		
		//because any interface can only have a single dhcp client, capable of receiving a single dhcp lease we can be sure that if the 
		//physical interface has an ip address that is assigned as a dhcp lease thats the one we want to move
		//if we i.e. had emergency access ips on the interfaces as well they would be hardcoded.
		foreach ($ips_on_physical_wan as $wan_ip) {
			
			if ($wan_ip->get_ip_address_map_type() == 91) {
				//because the ip object is destroyed once we unmap it from the old interface
				//we will need to save the id so we can reinstanciate it
				$wan_ip_address_id = $wan_ip->get_ip_address_id();
				
				//remove from physical interface
				$physical_wan_if->remove_ip_address_map($wan_ip);
				
				//now map to vlan interface
				$recreated_wan_ip = new Thelist_Model_ipaddress($wan_ip_address_id);
				$wan_vlan_interface->map_new_ip_address($recreated_wan_ip, 91);

			}
		}

		//get a subnet range for this mt board, of private ip space
		
		
		$sql66 = 	"SELECT item_id FROM items
					WHERE item_type='ip_address_type'
					AND item_name='private'
					";
			
		$private_subnet_item_id	= Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql66);
		
		$subnet_size_requested = 28;
		
		$new_subnet = $this->_border_router_this_install['equipment']->generate_new_subnet($subnet_size_requested, $private_subnet_item_id);
	
		if ($new_subnet == false) {
			//ip assignment is critical. database will roll back if this happens.
			throw new exception("upstream router does not have enough ip space to assign this router a subnet we requested /".$subnet_mask_requested." of private space from ".$this->$this->_border_router_this_install['equipment']->get_fqdn()." " , 7204);
		}
	
		//turn this subnet into ip addresses
		$new_subnet->create_ip_addresses();
	
		$sql8 = 	"SELECT item_id FROM items
					WHERE item_type='ip_address_map_type'
					AND item_name='connected'
					";
			
		$connected_mapping_item_id	= Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql8);
		
		$sql89 = 	"SELECT item_id FROM items
					WHERE item_type='ip_address_map_type'
					AND item_name='dhcp_range'
					";
			
		$dhcp_range_mapping_item_id	= Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql89);
		
		
		
		//map these addresses to the lan bridge
		$i=0;
		foreach ($new_subnet->get_ip_addresses() as $single_lan_ip) {
				
			if ($single_lan_ip->get_ip_address() != $new_subnet->get_ip_broadcast_address() && $single_lan_ip->get_ip_address() != $new_subnet->get_ip_subnet_address()) {
	
				if ($i == 0) {
					//map the very first ip connected to the lan interface
					$lan_bridge->map_new_ip_address($single_lan_ip, $connected_mapping_item_id);
				} else {
					//map all other ips as dhcp range ips
					$lan_bridge->map_new_ip_address($single_lan_ip, $dhcp_range_mapping_item_id);
				}
	
				$i++;
			}
		}
	
		//create outbound traffic rule for nat
		$nat_traffic_rule = $equipment_obj->add_new_ip_traffic_rule('NAT firewall', 1, 1, null, null);
		//match all with src of new subnet.
		$nat_traffic_rule->map_traffic_ip_subnet($new_subnet->get_ip_subnet_address(), $new_subnet->get_ip_subnet_cidr_mask(), 1, 'src');
		$nat_traffic_rule->map_traffic_interface($wan_vlan_interface, 1);
		$this->_equipment_updates['traffic_rules'][] = $nat_traffic_rule;
		
		//create standard cpe input firewall
		$this->configure_standard_inbound_cpe_firewall($new_subnet);
		
		//create / configure specific services on the equipment
		
		//dhcp_server
		$dhcp_server_app	= $equipment_obj->create_application_mapping(1);
		$dhcp_server_app->create_application_metric_mapping(9, 0, 1);
		$dhcp_server_app->create_application_metric_mapping(5, 0, 'after delay');
		$dhcp_server_app->create_application_metric_mapping(12, 0, 'none');
		$dhcp_server_app->create_application_metric_mapping(1, 0, 'dhcp1.cpe.local');
		$dhcp_server_app->create_application_metric_mapping(13, 0, $lan_bridge_name);

		//7 days of default lease time
		$dhcp_server_app->create_application_metric_mapping(4, 0, 604800);
		$dhcp_server_app->create_application_metric_mapping(8, 0, Thelist_Utility_staticvariables::get_recursive_dns_server1());
		$dhcp_server_app->create_application_metric_mapping(8, 1, Thelist_Utility_staticvariables::get_recursive_dns_server2());
		$dhcp_server_app->create_application_metric_mapping(7, 0, Thelist_Utility_staticvariables::get_primary_ntp_server());
		$this->_equipment_updates['applications'][] = $dhcp_server_app;
		
		//ntp_client
		$ntp_client_app		= $equipment_obj->create_application_mapping(2);
		$ntp_client_app->create_application_metric_mapping(9, 0, 1);
		$ntp_client_app->create_application_metric_mapping(14, 0, 'unicast');
		$ntp_client_app->create_application_metric_mapping(7, 0, Thelist_Utility_staticvariables::get_primary_ntp_server());
		$this->_equipment_updates['applications'][] = $ntp_client_app;

		//upnp
		$upnp_app			= $equipment_obj->create_application_mapping(3);
		$upnp_app->create_application_metric_mapping(9, 0, 1);
		$upnp_app->create_application_metric_mapping(10, 0, 1);
		$upnp_app->create_application_metric_mapping(11, 0, 1);
		$upnp_app->create_application_metric_mapping(26, 0, $wan_vlan_interface->get_if_name());
		$upnp_app->create_application_metric_mapping(27, 0, $lan_bridge_name);
		$this->_equipment_updates['applications'][] = $upnp_app;
		
		//dhcp_client
		$dhcp_client_app		= $equipment_obj->create_application_mapping(4);
		$dhcp_client_app->create_application_metric_mapping(9, 0, 1);
		$dhcp_client_app->create_application_metric_mapping(28, 0, $wan_vlan_interface->get_if_name());
		$dhcp_client_app->create_application_metric_mapping(29, 0, 1);
		$dhcp_client_app->create_application_metric_mapping(30, 0, 1);
		$dhcp_client_app->create_application_metric_mapping(31, 0, 1);
		$this->_equipment_updates['applications'][] = $dhcp_client_app;
		
		//generate the fqdn string
		$cpe_fqdn		= new Thelist_Utility_fqdngenerator();
		$cpe_identity	= $cpe_fqdn->fqdn_from_equipment($equipment_obj);
		
		//create snmp credential
		$device_authentication_credential = new Thelist_Model_deviceauthenticationcredential();
		$device_authentication_credential->set_api_name('snmp');
		$device_authentication_credential->set_device_password(Thelist_Utility_staticvariables::get_snmp_ro_community());
		$equipment_obj->create_api_auth($device_authentication_credential, false);
		
		//snmp
		$snmp_app			= $equipment_obj->create_application_mapping(5);
		$snmp_app->create_application_metric_mapping(9, 0, 1);
		$snmp_app->create_application_metric_mapping(15, 0, Thelist_Utility_staticvariables::get_snmp_contact());
		$snmp_app->create_application_metric_mapping(16, 0, $cpe_identity);
		$snmp_app->create_application_metric_mapping(17, 0, 2);
		$snmp_app->create_application_metric_mapping(18, 0, 'Read-Only');
		$this->_equipment_updates['applications'][] = $snmp_app;
		
		//syslog Action
		$syslog_app			= $equipment_obj->create_application_mapping(6);
		$syslog_app->create_application_metric_mapping(6, 0, 'local2');
		$syslog_app->create_application_metric_mapping(21, 0, 'remote');
		$syslog_app->create_application_metric_mapping(22, 0, 514);
		$syslog_app->create_application_metric_mapping(23, 0, 'notice');
		$syslog_app->create_application_metric_mapping(24, 0, Thelist_Utility_staticvariables::get_primary_syslog_server());
		$syslog_app->create_application_metric_mapping(35, 0, 'centralsyslog');
		$syslog_app->create_application_metric_mapping(36, 0, 'bsd-syslog');
		
		//syslog Rule
		$syslog_app->create_application_metric_mapping(19, 0, 'critical', 1);
		$syslog_app->create_application_metric_mapping(20, 0, 'wireless', 1);
		$syslog_app->create_application_metric_mapping(20, 0, 'debug', 1);
		$syslog_app->create_application_metric_mapping(34, 0, 'MTCPE-CRITICAL', 1);
		
		//syslog Rule
		$syslog_app->create_application_metric_mapping(19, 0, 'info', 2);
		$syslog_app->create_application_metric_mapping(20, 0, 'wireless', 2);
		$syslog_app->create_application_metric_mapping(20, 0, 'debug', 2);
		$syslog_app->create_application_metric_mapping(34, 0, 'MTCPE-INFO', 2);

		$this->_equipment_updates['applications'][] = $syslog_app;
		
		//connection tracking
		$conn_track_app		= $equipment_obj->create_application_mapping(7);
		$conn_track_app->create_application_metric_mapping(9, 0, 1);
		$conn_track_app->create_application_metric_mapping(25, 0, 600);
		$this->_equipment_updates['applications'][] = $conn_track_app;

		//add cpe router to the devices that require updates
		$this->_equipment_updates['cpe_router'] = $equipment_obj;

		//database config is complete, remember that the management ip has not yet been changed for mikrotik in database
		//we need to push config first and then change it to the dhcp lease address
	}
	
	private function configure_standard_inbound_cpe_firewall($subnet_obj)
	{
		//ALLOW ICMP
		$icmp_traffic_rule = $this->_equipment->add_new_ip_traffic_rule('Allow ICMP', 3, 2, null, null);
		//port number 0 means all ports, we need the protocol but not the port
		$icmp_traffic_rule->map_traffic_protocol_port(3, 0, 1, 'none');
		$this->_equipment_updates['traffic_rules'][] = $icmp_traffic_rule;
		
		//ALLOW Trusted Address List
		$allow_trusted_traffic_rule = $this->_equipment->add_new_ip_traffic_rule('Allow Management Address List', 3, 2, null, null);
		$allow_trusted_traffic_rule->map_traffic_ip_subnet('10.202.53.0', 24, 1, 'src');
		$allow_trusted_traffic_rule->map_traffic_ip_subnet('10.245.64.0', 18, 1, 'src');
		$allow_trusted_traffic_rule->map_traffic_ip_subnet('98.159.94.0', 24, 1, 'src');
		$allow_trusted_traffic_rule->map_traffic_ip_subnet('68.170.70.0', 24, 1, 'src');
		$allow_trusted_traffic_rule->map_traffic_ip_subnet('72.37.180.184', 29, 1, 'src');
		$allow_trusted_traffic_rule->map_traffic_ip_subnet('72.37.182.104', 29, 1, 'src');
		$this->_equipment_updates['traffic_rules'][] = $allow_trusted_traffic_rule;
	
		//allow speed tests from trusted subnets
		$allow_speedtest_traffic_rule = $this->_equipment->add_new_ip_traffic_rule('Allow Speed Test Address List', 3, 2, null, null);
		$allow_speedtest_traffic_rule->map_traffic_ip_subnet('98.159.94.0', 24, 1, 'src');
		$allow_speedtest_traffic_rule->map_traffic_ip_subnet('68.170.70.0', 24, 1, 'src');
		$allow_speedtest_traffic_rule->map_traffic_ip_subnet('72.37.180.184', 29, 1, 'src');
		$allow_speedtest_traffic_rule->map_traffic_ip_subnet('72.37.182.104', 29, 1, 'src');
	
		//tcp:2000
		$allow_speedtest_traffic_rule->map_traffic_protocol_port(1, 2000, 1, 'src');
		$this->_equipment_updates['traffic_rules'][] = $allow_speedtest_traffic_rule;
	
		//allow upnp tcp:2828
		$allow_upnp2828_traffic_rule = $this->_equipment->add_new_ip_traffic_rule('Allow UPNP tcp2828', 3, 2, null, null);
		$allow_upnp2828_traffic_rule->map_traffic_ip_subnet($subnet_obj->get_ip_subnet_address(), $subnet_obj->get_ip_subnet_cidr_mask(), 1, 'dst');
		$allow_upnp2828_traffic_rule->map_traffic_protocol_port(1, 2828, 1, 'dst');
		$this->_equipment_updates['traffic_rules'][] = $allow_upnp2828_traffic_rule;
	
	
		//allow upnp udp:1900
		$allow_upnp1900_traffic_rule = $this->_equipment->add_new_ip_traffic_rule('Allow UPNP udp1900', 3, 2, null, null);
		$allow_upnp1900_traffic_rule->map_traffic_ip_subnet($subnet_obj->get_ip_subnet_address(), $subnet_obj->get_ip_subnet_cidr_mask(), 1, 'dst');
		$allow_upnp1900_traffic_rule->map_traffic_protocol_port(2, 1900, 1, 'dst');
		$this->_equipment_updates['traffic_rules'][] = $allow_upnp1900_traffic_rule;
	
		//drop everything not matched
		$drop_all_not_matched_traffic_rule = $this->_equipment->add_new_ip_traffic_rule('Drop all inbound not matched', 3, 3, null, null);
		$this->_equipment_updates['traffic_rules'][] = $drop_all_not_matched_traffic_rule;
	
	}
	
	private function get_cpe_wan_interface()
	{
		$sp_locator	= new Thelist_Model_servicepointresourcelocator();
	
		//get the wan interface
		$interfaces = $this->_equipment->get_interfaces();
	
		if ($interfaces != null) {
				
			foreach($interfaces as $interface) {
	
				$return = $sp_locator->get_service_point_interface_from_cpe($interface);
	
				if ($return != false) {
						
					return $interface;
				}
			}
	
			//please check that all equipment in the path has been mapped to the correct units. most times this is caused 
			//by a patch panel no mapped to an infrastructure unit
			throw new exception('no cpe wan interface was found, please check that all equipment in path is mapped to the correct unit types/units', 2019);
	
		} else {
			throw new exception('cpe equipment has no interfaces', 2020);
		}
	}
	
	private function get_cpe_wireless_interface($equipment_obj)
	{
	
		//get the wireless interface
		$interfaces = $equipment_obj->get_interfaces();
	
		if ($interfaces != null) {
			foreach($interfaces as $interface) {
	
				if ($interface->get_if_type()->get_if_type() == 'wireless') {
	
					return $interface;
				}
			}
	
			throw new exception('no cpe wireless interface was found', 2021);
	
		} else {
			throw new exception('cpe equipment has no interfaces', 2022);
		}
	}

	private function cpe_router_db_config()
	{
		//find the wan cpe interface it will be set 
		$this->_customer_wan_interface = $this->get_cpe_wan_interface();
		
		//find and set the border and distribution devices
		$border_dist_equipment = $this->get_cpe_border_and_dist_equipment($this->_customer_wan_interface);
		
		//there must be a border router and edge switch, dist switch may not be there 
		if (!isset($border_dist_equipment['border_routers']) || !isset($border_dist_equipment['edge_switches'])) {
			
			throw new exception('cpe router is missing edge switch or border router', 2009);
			
		} else {
		
			//set the variables
			$this->_border_routers = $border_dist_equipment['border_routers'];
			$this->_edge_switches = $border_dist_equipment['edge_switches'];
			
			if (isset($border_dist_equipment['distribution_switches'])) {
				
				//include distribution switches if there are any
				$this->_distribution_switches = $border_dist_equipment['distribution_switches'];
				
			}
			
			//find out if this equipment is being provisioned as part of a service_plan_quote_map
			$sql = 	"SELECT sqetm.service_plan_quote_map_id FROM equipment_mapping em
					INNER JOIN sales_quote_eq_type_map_equipment_mapping sqetmem ON sqetmem.equipment_map_id=em.equipment_map_id
					INNER JOIN service_plan_quote_eq_type_mapping sqetm ON sqetm.service_plan_quote_eq_type_map_id=sqetmem.service_plan_quote_eq_type_map_id
					WHERE em.eq_id='".$this->_equipment->get_eq_id()."'
					AND em.eq_map_activated <= NOW()
					AND ((em.eq_map_deactivated < NOW()) OR (em.eq_map_deactivated IS NULL))
					";
			
			$spqm_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
			if ($spqm_id != false) {
				
				//get the requirements
				$service_plan_quote_map_obj	= new Thelist_Model_serviceplanquotemap($spqm_id);
				
				$ip_requirements				= $this->get_ip_address_requirements($service_plan_quote_map_obj);
				$download_requirement	 		= $this->get_download_requirements($service_plan_quote_map_obj);
				$upload_requirement	 			= $this->get_upload_requirements($service_plan_quote_map_obj);
				
				if ($ip_requirements != false) {
					
					$count_ip_requirements 	= count($ip_requirements);
					$count_border_routers	= count($this->_border_routers);
					
						
					foreach($this->_border_routers as $border_router) {

						if ($this->_border_router_this_install == null) {
							
							$slave_interfaces = $border_router['inbound_interface']->get_master_relationships();
		
							if ($slave_interfaces != null) {
								
								foreach ($slave_interfaces as $slave_interface) {
									
									if ($this->_border_router_this_install == null) {
										$ip_objs = array();
										
										foreach($ip_requirements as $ip_requirement_index => $ip_requirement) {
											
											if ($ip_requirement['ip_subnet_size'] == '32') {
												$ip_count = 1;
											} else {
												
												//address or subnet requirement?
												throw new exception('please expand this method to cover the ip requirements that are larger than /32', 2016);
											}
												
											$ips = $this->get_ips_from_border_router_interface($slave_interface, $ip_count, $ip_requirement['ip_address_type']);
												
											if ($ips != false) {
										
												$ip_objs[$ip_requirement_index]['ips']			= $ips;
												$ip_objs[$ip_requirement_index]['interface']	= $slave_interface;
										
											}
										}
										
										$count_ips = count($ip_objs);
										
										if ($count_ip_requirements == $count_ips) {
												
											$this->_border_router_this_install = $border_router;
											break;
										}
									}
								}
							}
						}
					}

					//if we dident find a border router with ips
					if ($this->_border_router_this_install == null) {
						throw new exception('we could not find a border router with enough ips', 2010);
					}
					
					//create new queues
					if ($download_requirement != false) {
					
						$download_queue_name 			= $this->_border_router_this_install['equipment']->get_new_connection_queue_name();
						
						//currently queues are applied to the physical interfaces regardless of the ips residing on a Vlan interface
						//that may change in the future
						$downstream_queue_interface		= $this->_border_router_this_install['inbound_interface'];
						$download_queue_obj 			= $downstream_queue_interface->create_connection_queue($download_requirement, $download_queue_name);
					
					}
						
					if ($upload_requirement != false) {
							
						$upload_queue_name = $this->_border_router_this_install['equipment']->get_new_connection_queue_name();
					
						$upstream_queue_interface = $this->get_upstream_queue_interface($this->_border_router_this_install['equipment']);
						
						if ($upstream_queue_interface != false) {
								
							$upload_queue_obj = $upstream_queue_interface->create_connection_queue($upload_requirement, $upload_queue_name);
								
						} else {	
							throw new exception('we did not get an upstream queue interface', 2012);	
						}
					}

					//map the ips to the interfaces and add filters to the queues
					$i=0;
					foreach($ip_objs as $ip_requirement_index2 => $ip_details) {
						
						foreach($ip_details['ips'] as $ip_address) {

							//add each ip address to the items to update.
							$this->_monitoring_updates['ip_addresses'][] = $ip_address;
							$this->_equipment_updates['ip_addresses'][] = $ip_address;
				
							//since this is a customer router we can set the management ip to the first IP
							if ($i == 0) {
								$this->_equipment_updates['ip_addresses']['management_ip'] = $ip_address;
							}
							$i++;
							
							//now map the ip to the border router if needed i.e. dhcp reservation)
							$sql3 = "SELECT item_name FROM items
									WHERE item_type='ip_address_map_type'
									AND item_id='".$ip_requirements[$ip_requirement_index2]['ip_address_mapping_type']."'
									";
							
							$mapping_req_name	= Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql3);
							
							if ($mapping_req_name == 'dhcp_lease') {
							
								$sql = "SELECT item_id FROM items
										WHERE item_type='ip_address_map_type'
										AND item_name='dhcp_reservation'
										";
								
								$gateway_mapping_id	= Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
							
							} elseif ($mapping_req_name == 'static_assignment') {
							
								$sql = "SELECT item_id FROM items
										WHERE item_type='ip_address_map_type'
										AND item_name='static_assignment'
										";
								$gateway_mapping_id	= Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
								
							} else {
								throw new exception("we cannot determine how the ips should be mapped to the gateway device cpe mapping was '".$mapping_req_name."'  ", 2025);
							}
														
							if ($gateway_mapping_id != false) {
								
								$ip_objs[$ip_requirement_index2]['interface']->map_new_ip_address($ip_address, $gateway_mapping_id);

								//the ip address is now mapped to the border router that will be acting as a gateway for the CPE
								//now we assign a default gateway to the cpe router
								
								//first locate the connected ip address on the interface of the border router that is
								//in the same subnet as the ip that is being provisioned
								$border_router_connected_ips = $ip_objs[$ip_requirement_index2]['interface']->get_connected_ips();
								
								if ($border_router_connected_ips != false) {
									
									foreach ($border_router_connected_ips as $br_conn_ip) {
										
										if ($br_conn_ip->get_ip_subnet_id() == $ip_address->get_ip_subnet_id()) {
											$gateway_ip_address_map_id = $br_conn_ip->get_ip_address_map_id();
										}
									}
									
								} else {
									throw new exception('border router interface that supplied the available host ips for our new cpe router, has no connected ips, we need one for the cpe default route', 2026);
								}

								
								if (isset($gateway_ip_address_map_id)) {
									
									$sql =	"SELECT ip_subnet_id FROM ip_subnets
											WHERE ip_subnet_address='0.0.0.0'
											AND ip_subnet_cidr_mask='0'
											";
									
									$default_ip_v4_subnet_id	= Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
									
									if (isset($default_ip_v4_subnet_id['ip_subnet_id'])) {
										$this->_equipment->add_new_route($default_ip_v4_subnet_id, $gateway_ip_address_map_id, 1);
									} else {
										throw new exception('our database does not have a 0.0.0.0/0 ipv4 subnet', 2027);
									}
									
								} else {
									throw new exception('border router interface that supplied the available host ips for our new cpe router, has no ips that match the subnet_id of the allocation,we need one for the cpe default route', 2026);
								}

								//create the filters for each queue
								if (isset($download_queue_obj)) {
									
									$down_stream_filter_name				= $downstream_queue_interface->get_new_filter_name();
									$download_filter						= $download_queue_obj->create_connection_queue_filter(null, $down_stream_filter_name);
									
									//download is a destination address match
									$download_filter->create_frame_match(2, 1, $ip_address->get_ip_address());
									$this->_equipment_updates['filters'][] = $download_filter;
									
								} else {
									throw new exception('we did not get a downstream queue', 2013);
								}
								
								if(isset($upload_queue_obj)) {
									
									$up_stream_filter_name					= $upstream_queue_interface->get_new_filter_name();
									$upload_filter							= $upload_queue_obj->create_connection_queue_filter(null, $up_stream_filter_name);
										
									//upload is a source address match
									$upload_filter->create_frame_match(1, 1, $ip_address->get_ip_address());
									$this->_equipment_updates['filters'][] = $upload_filter;
									
								} else {
									throw new exception('we did not get a upstream queue', 2014);
								}
							} else {
								throw new exception('we did not get a gateway mapping type, items table lookup failed', 2024);
							}
						}
					}
					
					//assign ips to cpe equipment, this MUST happen after the router has been assigned, otherwise we come out with an cpe equipment object that 
					//have ips that are mapped to the border router. this is a catch 22 because the ip objects are always passed as referances, and they share the 
					//ip object.
					$this->_customer_wan_interface->map_new_ip_address($ip_address, $ip_requirements[$ip_requirement_index2]['ip_address_mapping_type']);

					//provision the edge switches
					$edge_interfaces = array();
					
					foreach($this->_edge_switches as $edge_switch) {

						//we need to allow vlan 10 to trunk on the edge port facing the customer and
						//make sure it is allowed on the uplink as well
						//also enable the edge port, if thats not already done
						
						//allow vlan 10 to trunk
						$edge_switch['inbound_interface']->add_new_interface_configuration(26, 10);
						//set admin status
						$edge_switch['inbound_interface']->add_new_interface_configuration(8, 1);
						//allow vlan 10 to trunk
						$edge_switch['outbound_interface']->add_new_interface_configuration(26, 10);
						
						//set auto speed 
						$edge_switch['inbound_interface']->add_new_interface_configuration(11, 'auto');
						//and duplex on the edge port
						$edge_switch['inbound_interface']->add_new_interface_configuration(12, 'auto');
						
						//get the svi interface from the edge switch
						$edge_svi_if	= $this->get_switch_svi_interface($edge_switch['equipment']);

						//allow the vlan to transit the edge switch
						$edge_svi_if->add_new_interface_configuration(24, 10);
						
						//add to monitoring queue
						$this->_monitoring_updates['interfaces'][]	= $edge_switch['inbound_interface'];
						$this->_monitoring_updates['interfaces'][]	= $edge_switch['outbound_interface'];

					}
					
					//provision the distribution switches if any
					if ($this->_distribution_switches != null) {
					
						foreach($this->_distribution_switches as $distribution_switch){

							//allow each distribution switch in/out ports to trunk the new vlan
							$distribution_switch['inbound_interface']->add_new_interface_configuration(26, 10);
							$distribution_switch['outbound_interface']->add_new_interface_configuration(26, 10);

							//get the svi interface from the edge switch
							$dist_svi_if	= $this->get_switch_svi_interface($distribution_switch['equipment']);
							
							//allow the vlan to transit the dist switch
							$dist_svi_if->add_new_interface_configuration(24, 10);
							
							//add to monitoring queue
							$this->_monitoring_updates['interfaces'][]	= $distribution_switch['inbound_interface'];
							$this->_monitoring_updates['interfaces'][]	= $distribution_switch['outbound_interface'];
						}
					}
				}
				
				//last we update the service plan quote map
				//by mappint the ip adresses and the queue filters
				$this->map_resources_to_service_plan($service_plan_quote_map_obj);
				
				//create monitoring, needs fixing first
				//$this->create_monitoring();
				
				//if this is a mikrotik cpe router additional config is needed
				if ($this->_equipment->get_eq_type()->get_eq_type_id() == 36) {
					
					foreach($this->_edge_switches as $edge_switch) {
					
						//if the interface does not have a native vlan set then we force vlan 30
						//this vlan is going no where and is our way of making sure, untagged traffic stops at the edge
						//if a native vlan is already set, like if they already have DTV and is using the native 
						//then we dont touch it.
						if ($edge_switch['inbound_interface']->get_interface_configuration(25) == false) {
							
							//if there is no native vlan then set it to the locked vlan 30
							$edge_switch['inbound_interface']->add_new_interface_configuration(25, 30);
							$edge_switch['inbound_interface']->add_new_interface_configuration(31, 'dot1q');
						}
					}
					
					$this->default_mikrotik_cpe_db_config($this->_equipment);
					
				} elseif ($cpe_router_equipment->get_eq_type()->get_eq_type_id() == 46) {
					
					foreach($this->_edge_switches as $edge_switch) {
							
						//we dont use access interfaces, its always trunk with native, in the case of a customer router
						//we use a native vlan to transport the internet traffic
						$edge_switch['inbound_interface']->add_new_interface_configuration(25, 10);
					}
					
					//set management ip for customer owned router
					$this->_equipment->set_eq_fqdn($this->_equipment_updates['ip_addresses']['management_ip']->get_ip_address());
					
				}
			}
			
			//return the objects that will need to be updated on the devices
			return $this->_equipment_updates;
		}
	}

	
	private function get_switch_svi_interface($switch_eq_obj)
	{
		//transit vlans are only located on the EtherSVI (software vlan interface) if_type_id = 28 interface for cisco
		if (($interfaces = $switch_eq_obj->get_interfaces()) != null) {
			
			foreach ($interfaces as $interface) {
				
				if ($interface->get_if_type()->get_if_type_id() == 28) {
					return $interface;
				}
			}
			
			throw new exception('switch has no svi interface, maybe this is not a cisco switch', 2028);

		} else {
			throw new exception('switch has no interfaces, what an odd switch', 2015);
		}
	}
	
	private function get_download_requirements($service_plan_quote_map_obj)
	{		
		$total_download = 0;
		
		if (($service_plan_map_quote_options = $service_plan_quote_map_obj->get_service_plan_quote_options()) != null) {
		
			foreach($service_plan_map_quote_options as $service_plan_map_quote_option) {
				
				if ($service_plan_map_quote_option->get_service_plan_option_map()->get_service_plan_option()->get_service_plan_option_type() == 60) {
					//download bandwidth
					//there may be many bandwidth options, we need a total
					$total_download = $total_download + $service_plan_map_quote_option->get_service_plan_option_map()->get_service_plan_option()->get_service_plan_option_value_1();
				
				}	
			}
		}
		
		if ($total_download != 0) {
			return $total_download;
		} else {
			return false;			
		}
		
	}
	
	private function get_upload_requirements($service_plan_quote_map_obj)
	{
	
		$total_upload = 0;
	
		if (($service_plan_map_quote_options = $service_plan_quote_map_obj->get_service_plan_quote_options()) != null) {
	
			foreach($service_plan_map_quote_options as $service_plan_map_quote_option) {
	
				if ($service_plan_map_quote_option->get_service_plan_option_map()->get_service_plan_option()->get_service_plan_option_type() == 95) {
					//upload bandwidth
					//there may be many bandwidth options, we need a total
					$total_upload = $total_upload + $service_plan_map_quote_option->get_service_plan_option_map()->get_service_plan_option()->get_service_plan_option_value_1();
	
				}
			}
		}
	
		if ($total_upload != 0) {
			return $total_upload;
		} else {
			return false;
		}
	
	}
	
	private function get_ip_address_requirements($service_plan_quote_map_obj)
	{
		$i=0;
		if (($service_plan_map_quote_options = $service_plan_quote_map_obj->get_service_plan_quote_options()) != null) {
		
			foreach($service_plan_map_quote_options as $service_plan_map_quote_option) {
				
				if ($service_plan_map_quote_option->get_service_plan_option_map()->get_service_plan_option()->get_service_plan_option_type() == 62) {
			
					$ip_requirements[$i]['ip_subnet_size'] 				= $service_plan_map_quote_option->get_service_plan_option_map()->get_service_plan_option()->get_service_plan_option_value_1();
					$ip_requirements[$i]['ip_address_type'] 			= $service_plan_map_quote_option->get_service_plan_option_map()->get_service_plan_option()->get_service_plan_option_value_2();
					$ip_requirements[$i]['ip_address_mapping_type'] 	= $service_plan_map_quote_option->get_service_plan_option_map()->get_service_plan_option()->get_service_plan_option_value_3();
					$i++;
				}
			}
		}
		
		if (isset($ip_requirements)) {
			return $ip_requirements;
		} else {
			return false;		
		}
	}
	
	private function get_ips_from_border_router_interface($interface, $ip_count, $ip_address_type)
	{
		$sql = 	"SELECT item_id FROM items
				WHERE item_type='ip_address_type'
				AND item_name='public'
				";
		
		$public_ip_type  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		$subnet_objs = $interface->get_connected_subnets();

		if ($subnet_objs != false) {
	
			foreach ($subnet_objs as $subnet_obj) {

				if ($subnet_obj->get_public_or_private() == $ip_address_type) {
						
					//if we are looking for a single public ip
					if ($public_ip_type == $ip_address_type) {

						if (($ip_address_objs = $subnet_obj->get_unused_host_ip_addresses($ip_count)) != false) {
							return $ip_address_objs;
						}
						
					} else {
						
						if (($ip_address_objs = $subnet_obj->get_unused_host_ip_addresses($ip_count)) != false) {
							return $ip_address_objs;
						}
					}
				}
			}
	
		} else {
				
			return false;
		}
	}
	
	private function get_upstream_queue_interface($equipment_obj)
	{

		if ($equipment_obj->get_eq_type()->get_eq_type_id() == 3) {
			
			$gateway_interfaces = $equipment_obj->get_default_gateway_interfaces();

			if ($gateway_interfaces != false) {
				
				if (isset($gateway_interfaces['1'])) {
					//this method is only returning a single interface
					//but there is a chance that equipment can have the default route split between 2 interfaces
					//in that case we error here, if this becomes a problem then fix it at that time
					throw new exception('method does not handle multiple gateways yet, needs to be fixed', 2023);
				}

				//We need the root interface, the interface that does not have a parent
				$sql2 = "CALL find_root_interface('".$gateway_interfaces['0']->get_if_id()."')";
					
				$root_if_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);

				//return the interface
				return $equipment_obj->get_interface($root_if_id);
				
				
			} else {
				throw new exception('we cannot determine the upstream_queue_interface, most likely because the equipment does not have a default route', 2011);
			}
			
		} else {
			
			return false;
		}
	
	}

	private function map_resources_to_service_plan($service_plan_quote_map_obj)
	{
		
		//map the IPs to the service plan
		if (isset($this->_equipment_updates['ip_addresses'])) {
			foreach($this->_equipment_updates['ip_addresses'] as $ip_address) {
				$service_plan_quote_map_obj->map_ip_address($ip_address);
			}
		}
		
		//map the filters to the service plan
		if (isset($this->_equipment_updates['filters'])) {
			foreach($this->_equipment_updates['filters'] as $filter) {
				$service_plan_quote_map_obj->map_connection_queue_filter($filter);
			}
		}
	}
	
	private function create_monitoring()
	{
		//broken because because of the reliance on generate commands 
		
		
		
		//map the IPs to the service plan
		if (isset($this->_monitoring_updates['ip_addresses'])) {
			foreach($this->_monitoring_updates['ip_addresses'] as $ip_address) {

				try {
						
					$new_mon_guid = $ip_address->create_new_monitoring_guid();
					$new_mon_guid->map_default_datasources();
						
				} catch (Exception $e) {
						
					switch($e->getCode()) {
							
						case 7400;
						//device function is used to support this monitoring and we have not defined the
						//function for this combination of eq_type and software. open a ticket for engineering
						$tt_task = new Thelist_Utility_troubletaskcreator('Engineering', 'Missing device function definition', 'Low');
						$task_obj	= $tt_task->create_task();
						$task_obj->add_note("Monitoring ip address ip_address_id ".$ip_address->get_ip_address_id()."");
						$task_obj->add_note($e->getMessage());
						break;
						default;
						throw $e;
							
					}
				}
			}
		}
	
		//map the filters to the service plan
		if (isset($this->_monitoring_updates['interfaces'])) {
			foreach($this->_monitoring_updates['interfaces'] as $interface) {
				
				$sql 			= "CALL find_root_interface('".$interface->get_if_id()."')";
				$root_if_id		= Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
				
				if ($root_if_id != $interface->get_if_id()) {
					$interface	= new Thelist_Model_equipmentinterface($root_if_id);
				}
				
				//no cisco software vlan interfaces should have monitoring
				if ($interface->get_if_type()->get_if_type_id() != 28) {
					
					try {
					
						$new_mon_guid = $interface->create_new_monitoring_guid();
						$new_mon_guid->map_default_datasources();
					
					} catch (Exception $e) {
							
						switch($e->getCode()) {
					
							case 7400;
							//device function is used to support this monitoring and we have not defined the 
							//function for this combination of eq_type and software. open a ticket for engineering
							$tt_task = new Thelist_Utility_troubletaskcreator('Engineering', 'Missing device function definition', 'Low');
							$task_obj	= $tt_task->create_task();
							$task_obj->add_note("Monitoring interface if_id ".$interface->get_if_id()."");
							$task_obj->add_note($e->getMessage());
							break;
							default;
							throw $e;
					
						}
					}	
				}
			}
		}
	}
	

}
?>