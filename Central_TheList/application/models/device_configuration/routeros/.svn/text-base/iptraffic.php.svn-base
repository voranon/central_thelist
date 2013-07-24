<?php
class thelist_routeros_config_iptraffic implements Thelist_Commander_pattern_interface_ideviceconfiguration
{
	private $_equipment;
	private $_ip_traffic_rule;
	
	public function __construct($equipment, $ip_traffic_rule)
	{
		$this->_equipment 						= $equipment;
		$this->_ip_traffic_rule					= $ip_traffic_rule;
	}

	public function generate_config_array()
	{
		
		if ($this->_ip_traffic_rule->get_ip_traffic_rule_chain_name() != null) {
			$return['configuration']['chain_name'] = $this->_ip_traffic_rule->get_ip_traffic_rule_chain_name();
		}
		
		if ($this->_ip_traffic_rule->get_ip_traffic_rule_priority() != null) {
			$return['configuration']['priority'] = $this->_ip_traffic_rule->get_ip_traffic_rule_priority();
		}
		
		if ($this->_ip_traffic_rule->get_ip_traffic_rule_mark() != null) {
			$return['configuration']['mark'] = $this->_ip_traffic_rule->get_ip_traffic_rule_mark();
		}
		
		if ($this->_ip_traffic_rule->get_ip_traffic_rule_action_name() != null) {
			$return['configuration']['action_name'] = $this->_ip_traffic_rule->get_ip_traffic_rule_action_name();
		}
		
		if ($this->_ip_traffic_rule->get_ip_traffic_rule_ip_ports() != null) {
			
			foreach ($this->_ip_traffic_rule->get_ip_traffic_rule_ip_ports() as $ip_port) {
				
				if ($ip_port['ip_protocol_port_map_match'] == 1) {
					$return['configuration']['ip_ports']['match_protocol'][$ip_port['ip_protocol_name']]['ip_protocol_name'] = $ip_port['ip_protocol_name'];
				} elseif ($ip_port['ip_protocol_port_map_match'] == 0) {
					$return['configuration']['ip_ports']['unmatch_protocol'][$ip_port['ip_protocol_name']]['ip_protocol_name'] = $ip_port['ip_protocol_name'];
				}
				
				if ($ip_port['ip_protocol_port_number'] != 0 && $ip_port['ip_protocol_port_map_match'] == 1 && $ip_port['ip_protocol_port_direction'] != 'none') {
					$return['configuration']['ip_ports']['match_protocol'][$ip_port['ip_protocol_name']][$ip_port['ip_protocol_port_direction']][] = $ip_port['ip_protocol_port_number'];
				} elseif ($ip_port['ip_protocol_port_number'] != 0 && $ip_port['ip_protocol_port_map_match'] == 0 && $ip_port['ip_protocol_port_direction'] != 'none') {
					$return['configuration']['ip_ports']['unmatch_protocol'][$ip_port['ip_protocol_name']][$ip_port['ip_protocol_port_direction']][] = $ip_port['ip_protocol_port_number'];
				}
			}
		}
		
		if ($this->_ip_traffic_rule->get_ip_traffic_rule_ip_subnets() != null) {
				
			$i=0;
			foreach ($this->_ip_traffic_rule->get_ip_traffic_rule_ip_subnets() as $ip_subnet) {
		
				if ($ip_subnet['ip_traffic_rule_ip_subnet_map_match'] == 1) {
					$return['configuration']['ip_subnets']['match_subnet'][$i]['subnet_address'] 		= $ip_subnet['ip_traffic_rule_ip_subnet_address'];
					$return['configuration']['ip_subnets']['match_subnet'][$i]['subnet_mask'] 			= $ip_subnet['ip_traffic_rule_ip_subnet_cidr'];
					$return['configuration']['ip_subnets']['match_subnet'][$i]['direction'] 			= $ip_subnet['ip_traffic_rule_ip_subnet_map_direction'];
				} else {
					$return['configuration']['ip_subnets']['unmatch_subnet'][$i]['subnet_address'] 		= $ip_subnet['ip_traffic_rule_ip_subnet_address'];
					$return['configuration']['ip_subnets']['unmatch_subnet'][$i]['subnet_mask'] 		= $ip_subnet['ip_traffic_rule_ip_subnet_cidr'];
					$return['configuration']['ip_subnets']['unmatch_subnet'][$i]['direction'] 			= $ip_subnet['ip_traffic_rule_ip_subnet_map_direction'];
				}
				$i++;
			}
		}
		
		if ($this->_ip_traffic_rule->get_interfaces() != null) {

			$j=0;
			foreach ($this->_ip_traffic_rule->get_interfaces() as $interface) {

				$return['configuration']['interfaces'][$j]['interface_name'] 		= $interface->get_if_name();
				$return['configuration']['interfaces'][$j]['interface_role'] 		= $interface->get_ip_traffic_rule_if_role_name();
				$j++;
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
		//declare the var
		$return_conf = "";
		
		//if there are subnets in the array we need to generate an acl first
		if (isset($config_array['configuration']['ip_subnets'])) {
			$return_conf .= "/ip firewall address-list";
			
			//included subnets
			if (isset($config_array['configuration']['ip_subnets']['match_subnet'])) {
				
				foreach($config_array['configuration']['ip_subnets']['match_subnet'] as $subnet_match) {
					
					$address_list_name = "match_".$config_array['configuration']['chain_name']."_".$subnet_match['direction']."_".$config_array['configuration']['priority']."";
					
					$return_conf .= "\nadd list=\"".$address_list_name."\" address=\"".$subnet_match['subnet_address']."/".$subnet_match['subnet_mask']."\"";
				
					$address_lists['match'][$subnet_match['direction']] = $address_list_name;
				}
			}
			
			//excluded subnets
			if (isset($config_array['configuration']['ip_subnets']['unmatch_subnet'])) {
			
				foreach($config_array['configuration']['ip_subnets']['unmatch_subnet'] as $subnet_no_match) {
					
					$address_list_name = "unmatch_".$config_array['configuration']['chain_name']."_".$subnet_no_match['direction']."_".$config_array['configuration']['priority']."";
					
					$return_conf .= "\nadd list=\"".$address_list_name."\" address=\"".$subnet_no_match['subnet_address']."/".$subnet_no_match['subnet_mask']."\"";
					
					$address_lists['unmatch'][$subnet_no_match['direction']] = $address_list_name;
				}
			}
			$return_conf .= "\n\n";
		}
		
		
		if ($config_array['configuration']['chain_name'] == 'input') {
			$return_conf .= "/ip firewall filter";
		} elseif ($config_array['configuration']['chain_name'] == 'output') {
			$return_conf .= "/ip firewall filter";
		} elseif ($config_array['configuration']['chain_name'] == 'srcnat') {
			$return_conf .= "/ip firewall nat";
		} elseif ($config_array['configuration']['chain_name'] == 'dstnat') {
			$return_conf .= "/ip firewall nat";
		}
		
		$return_conf .= "\nadd disabled=\"no\"";
		
		//chain
		if (isset($config_array['configuration']['chain_name'])) {
			$return_conf .= " chain=\"".$config_array['configuration']['chain_name']."\"";
		}
		
		//action
		if (isset($config_array['configuration']['action_name'])) {
			$return_conf .= " action=\"".$config_array['configuration']['action_name']."\"";
		}
		
		//match ip ports and protocols
		if (isset($config_array['configuration']['ip_ports'])) {
			
			if (isset($config_array['configuration']['ip_ports']['match_protocol'])) {
				foreach($config_array['configuration']['ip_ports']['match_protocol'] as $match_protocol) {
					
					$return_conf .= " protocol=\"".$match_protocol['ip_protocol_name']."\"";
					
					if (isset($match_protocol['dst'])) {
						foreach($match_protocol['dst'] as $match_dst_port) {
							$return_conf .= " dst-port=\"".$match_dst_port."\"";
						}
					}
					
					if (isset($match_protocol['src'])) {
						foreach($match_protocol['src'] as $match_src_port) {
							$return_conf .= " src-port=\"".$match_src_port."\"";
						}
					}
				}
			}
			
			if (isset($config_array['configuration']['ip_ports']['unmatch_protocol'])) {
				foreach($config_array['configuration']['ip_ports']['unmatch_protocol'] as $unmatch_protocol) {
						
					$return_conf .= " protocol=\"".$unmatch_protocol['ip_protocol_name']."\"";
						
					if (isset($unmatch_protocol['dst'])) {
						foreach($unmatch_protocol['dst'] as $unmatch_dst_port) {
							$return_conf .= " dst-port=\"!".$unmatch_dst_port."\"";
						}
					}
						
					if (isset($unmatch_protocol['src'])) {
						foreach($unmatch_protocol['src'] as $unmatch_src_port) {
							$return_conf .= " src-port=\"!".$unmatch_src_port."\"";
						}
					}
				}
			}
		}
		
		//interfaces
		if (isset($config_array['configuration']['interfaces'])) {
			
			foreach ($config_array['configuration']['interfaces'] as $interface) {
				
				if ($interface['interface_role'] == 'outbound_interface') {
					$return_conf .= " out-interface=\"".$interface['interface_name']."\"";
					
				} elseif ($interface['interface_role'] == 'inbound_interface') {
					$return_conf .= " in-interface=\"".$interface['interface_name']."\"";
				}
			}
		}

		//any address lists included?
		
		if (isset($address_lists['match']['dst'])) {
			$return_conf .= " dst-address-list=\"".$address_lists['match']['dst']."\"";
		}
		if (isset($address_lists['match']['src'])) {
			$return_conf .= " src-address-list=\"".$address_lists['match']['src']."\"";
		}
		if (isset($address_lists['unmatch']['dst'])) {
			$return_conf .= " dst-address-list=\"!".$address_lists['unmatch']['dst']."\"";
		}
		if (isset($address_lists['unmatch']['src'])) {
			$return_conf .= " dst-address-list=\"!".$address_lists['unmatch']['src']."\"";
		}

		return $return_conf;
	}
}