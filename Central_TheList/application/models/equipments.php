<?php

//exception codes 400-499

class thelist_model_equipments
{
	private $eq_id;
	private $eq_master_id;
	private $eq_type_id;
	private $_eq_type;
	private $eq_fqdn=null;
	private $_eq_second_serial_number=null;
	private $po_item_id;
	private $uid;
	private $_interfaces=null;
	private $_eq_serial_number=null;
	private $_device=null;
	private $_monitoring_guids=null;
	private $_equipment_roles=null;
	private $_subnet_mappings=null;
	private $_ip_routes=null;
	private $_ip_traffic_rules=null;
	private $_application_mappings=null;
	private $_all_reachable_host_ips=null;
	
	public function __construct($eq_id)
	{
		$this->logs			= Zend_Registry::get('logs');
	
		$this->_time			= Zend_Registry::get('time');
	
		$this->user_session = new Zend_Session_Namespace('userinfo');
		
		$eq = Zend_Registry::get('database')->get_equipments()->fetchRow('eq_id='.$eq_id);
		
		$this->eq_id						=$eq_id;
		$this->eq_master_id					=$eq['eq_master_id'];
		$this->eq_type_id					=$eq['eq_type_id'];
		$this->_eq_serial_number			=$eq['eq_serial_number'];
		$this->eq_fqdn						=$eq['eq_fqdn'];
		$this->po_item_id					=$eq['po_item_id'];
		$this->uid            				=$eq['uid'];
		$this->_eq_second_serial_number		=$eq['eq_second_serial_number'];
		
		//the type
		$this->_eq_type = new Thelist_Model_equipmenttype($this->eq_type_id);
		
		$interfaces 			= Zend_Registry::get('database')->get_interfaces()->fetchAll('eq_id='.$this->eq_id);
		
		if (isset($interfaces['0'])) {
			foreach ($interfaces as $interface) {
				$this->_interfaces[$interface['if_id']] = new Thelist_Model_equipmentinterface($interface['if_id']);
			
			}
		}
		
		//the equipment roles
		$equipment_roles 			= Zend_Registry::get('database')->get_equipment_role_mapping()->fetchAll('eq_id='.$this->eq_id);

		if (isset($equipment_roles['0'])) {
			foreach ($equipment_roles as $equipment_role) {

				
				$this->_equipment_roles[$equipment_role['equipment_role_id']] = new Thelist_Model_equipmentrole($equipment_role['equipment_role_id']);
				$this->_equipment_roles[$equipment_role['equipment_role_id']]->set_equipment_role_map_id($equipment_role['equipment_role_map_id']);
			
			}
		}
		
		//standard monitoring methods
		$sql2=	"SELECT * FROM monitoring_guids
				WHERE table_name='equipments'
				AND primary_key='".$this->eq_id."'
				";
		
		$monitoring_guids  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
		if (isset($monitoring_guids['0'])) {
			foreach($monitoring_guids as $monitoring_guid){
				$this->_monitoring_guids[$monitoring_guid['monitoring_guid_id']] = new Thelist_Model_monitoringguid($monitoring_guid['monitoring_guid_id']);
			}
		}
	}
	
	public function get_provisioning_ip_range()
	{
		if ($this->_interfaces != null) {
			
			foreach ($this->_interfaces as $interface) {
				
				if ($interface->get_vlan_id() == 20 && $interface->get_vlan_type() == 'trunk') {
					
					$ipaddresses = $interface->get_ip_addresses();
					
					if ($ipaddresses != null) {
						
						foreach ($ipaddresses as $ipaddress) {
							
							if ($ipaddress->get_ip_address_map_type() == 90) {
								
								//return something
							}
							
						}
					}
				}
			}
		}
	}
	
	public function get_metric_details_for_application_id($equipment_application_metric_id, $equipment_application_id)
	{
		//get details of a type of metric from all the applications and metrics that are mapped to this equipment
		//this is reusable to get i.e. interface names for dhcp servers already running on the equipment
		
		$applications = $this->get_application_mappings();
		
		if ($applications != null) {
		
			foreach ($applications as $application) {
					
				//is this a
				if ($application->get_equipment_application_id() == $equipment_application_id) {
		
					$app_metrics = $application->get_metric_mappings();
		
					if ($app_metrics != null) {
							
						foreach ($app_metrics as $app_metric) {
							
							if ($app_metric->get_equipment_application_metric_id() == $equipment_application_metric_id) {
								$return[] = $app_metric;
							}
						}
					}
				}
			}
		} 
		
		if (isset($return)) {
			return $return;
		} else {
			return false;
		}
		
	}
	
	public function get_application_mappings()
	{
		if ($this->_application_mappings == null) {
			
			$sql = "SELECT * FROM equipment_application_mapping
					WHERE eq_id='".$this->eq_id."'
					";
			
			$application_mappings  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			if (isset($application_mappings['0'])) {
				
				foreach($application_mappings as $application_mapping) {
					
					$this->_application_mappings[$application_mapping['equipment_application_map_id']] = new Thelist_Model_equipmentapplication($application_mapping['equipment_application_id']);
					$this->_application_mappings[$application_mapping['equipment_application_map_id']]->fill_mapped_values($application_mapping['equipment_application_map_id']);
				}
			}
		} 
		
		return $this->_application_mappings;		
	}
	
	public function get_application_mapping($equipment_application_map_id)
	{
		if ($this->_application_mappings == null) {
			$this->get_application_mappings();
		}
		
		if (isset($this->_application_mappings[$equipment_application_map_id])) {
			return $this->_application_mappings[$equipment_application_map_id];
		} else {
			return false;
		}

	}
	
	public function create_application_mapping($equipment_application_id)
	{
		
		//is this application defined for this eq_type?
		
		$sql = "SELECT COUNT(eta.eq_type_application_id) FROM eq_type_applications eta
				WHERE eta.eq_type_id='".$this->eq_type_id."'
				AND eta.equipment_application_id='".$equipment_application_id."'	
				";
		
		$app_for_eq_type = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		if ($app_for_eq_type == 1) {
			
			if ($this->_application_mappings == null) {
				$this->get_application_mappings();
			}
			
			//first we map the application
			$data = array(
				
				'eq_id'    						=>  $this->eq_id,
				'equipment_application_id'   	=>  $equipment_application_id,
			
			);
			
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
			
			$equipment_application_map_id = Zend_Registry::get('database')->insert_single_row('equipment_application_mapping',$data,$class,$method);
			
			$this->_application_mappings[$equipment_application_map_id] = new Thelist_Model_equipmentapplication($equipment_application_id);
			$this->_application_mappings[$equipment_application_map_id]->fill_mapped_values($equipment_application_map_id);
			
			//next we get all the mandetory metrics and map them
			$sql2 = "SELECT etam.* FROM eq_type_applications eta
					INNER JOIN equipment_type_application_metrics etam ON etam.eq_type_application_id=eta.eq_type_application_id
					WHERE eta.eq_type_id='".$this->eq_type_id."'
					AND (etam.eq_type_metric_default_map='1' OR etam.eq_type_metric_mandetory='1')
					AND equipment_application_id='".$equipment_application_id."'
					";
			
			$default_metrics = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
			
			if (isset($default_metrics['0'])) {
				
				foreach ($default_metrics as $default_metric) {
					
					$this->_application_mappings[$equipment_application_map_id]->create_application_metric_mapping($default_metric['equipment_application_metric_id'], 0, $default_metric['eq_type_metric_default_value_1'], null);
				}
			}
			
			return $this->_application_mappings[$equipment_application_map_id];
			
		} else {
			throw new exception('create, that application mapping is not allowed for this equipment', 429);
		}
	}
	
	public function remove_application_mapping($equipment_application_map_id)
	{
		
		$application = $this->get_application_mapping($equipment_application_map_id);

		if ($application != false) {
			
			$metrics = $application->get_metric_mappings();
			
			if ($metrics != null) {
				
				//remove all metrics and allow override of mandetory items
				foreach ($metrics as $metric) {
					
					$application->remove_metric_map($metric->get_equipment_application_metric_map_id(), true);
				}
			}

			//now remove the application
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);

			Zend_Registry::get('database')->delete_single_row($equipment_application_map_id, 'equipment_application_mapping', $class, $method);
				
			//now unset it from this object
			unset($this->_application_mappings[$equipment_application_map_id]);

		} else {
			throw new exception('remove, that application mapping is not set for this equipment', 424);
		}
	}
	
	public function get_ip_routes()
	{
		if($this->_ip_routes == null) {
			
			$sql = "SELECT ip_route_id FROM ip_routes
					WHERE eq_id='".$this->eq_id."'
					";
			
			$ip_routes = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);

			if (isset($ip_routes['0'])) {
				
				foreach($ip_routes as $ip_route) {
					
					$this->_ip_routes[$ip_route['ip_route_id']]	= new Thelist_Model_iproute($ip_route['ip_route_id']);

				}
			}
		}
		
		return $this->_ip_routes;
	}
	
	public function get_equipment_roles()
	{
		return $this->_equipment_roles;
	}
	
	public function get_ip_route_interfaces($ip_route_obj)
	{
		if ($this->_ip_routes == null) {
			$this->get_ip_routes();
		}
		
		if (isset($this->_ip_routes[$ip_route_obj->get_ip_route_id()])) {

			//interfaces are implied by the fact that there is atleast one route on this equipment
			//dont know why this validation is here
			if ($this->_interfaces != null) {

				foreach($this->_interfaces as $interface) {
						
					if ($interface->get_ip_addresses() != null) {
		
						foreach($interface->get_ip_addresses() as $ip_address) {

							foreach ($ip_route_obj->get_ip_route_gateways() as $gateway) {
								
								if ($gateway['ip_address']->get_ip_subnet_id() == $ip_address->get_ip_subnet_id()) {
								
									//could be multible ips from the same subnet on an interface
									//by organizing them by the if id, we ensure that each interface is only returned once
									//in the result
									$gateway_interfaces[$interface->get_if_id()]	= $interface;
								
								}
							}
						}
					}
				}
			}
			
			if (isset($gateway_interfaces)) {
				return array_values($gateway_interfaces);
			}
				
		} else {
				
			throw new exception('the route you are trying to locate a local interface for, does not exist on this equipment', 413);
				
		}

		throw new exception('we are trying to locate the interface that is used to access the gateway for a route and we did not find it, the route is invalid', 414);
	}
	
	public function get_default_gateway_interfaces()
	{
		//upload is controlled on the interface that holds the default gateway
		$eq_routes = $this->get_ip_routes();
			
		if ($eq_routes != null) {
	
			foreach ($eq_routes as $eq_route) {
					
				$route_subnet	= $eq_route->get_ip_subnet();
					
				if ($route_subnet->get_ip_subnet_address() == '0.0.0.0' && $route_subnet->get_ip_subnet_cidr_mask() == '0') {
	
					//get the gateway interface
					return $this->get_ip_route_interfaces($eq_route);
	
				}
			}
		}
	
		return false;
	}
	
	public function set_new_equipment_role($equipmentrole_obj)
	{
		if (isset($this->_equipment_roles[$equipmentrole_obj->get_equipment_role_id()])) {
			return false;
		} else {

			//create the new interface
			$data = array(
			
						'eq_id'    						=>  $this->eq_id,
						'equipment_role_id'   			=>  $equipmentrole_obj->get_equipment_role_id(),
				
			);
				
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
				
			$equipment_role_map_id = Zend_Registry::get('database')->insert_single_row('equipment_role_mapping',$data,$class,$method);
				
			$this->_equipment_roles[$equipmentrole_obj->get_equipment_role_id()] = new Thelist_Model_equipmentrole($equipmentrole_obj->get_equipment_role_id());
			$this->_equipment_roles[$equipmentrole_obj->get_equipment_role_id()]->set_equipment_role_map_id($equipment_role_map_id);
		}
	}
	
	
	
	public function remove_equipment_role($equipment_role_map_id)
	{
		if ($this->_equipment_roles != null) {
			
			foreach($this->_equipment_roles as $role) {

				if ($role->get_equipment_role_map_id() == $equipment_role_map_id) {
					
					//we need this before we kill it off
					$role_id = $role->get_equipment_role_id();
					
					//it exists now remove it
					$trace 		= debug_backtrace();
					$method 	= $trace[0]["function"];
					$class		= get_class($this);
						
					//delete the role map
					Zend_Registry::get('database')->delete_single_row($equipment_role_map_id, 'equipment_role_mapping', $class, $method);
					
					//now unset it from this object
					unset($this->_equipment_roles[$role_id]);
					
					//done
					return;
				}
			}
		} 
		
		//we dident match or there are no roles at all in both cases its bad because we are trying to delete something that does not belong to this class
		throw new exception("you are trying to remove equipment_role_map_id: ".$equipment_role_map_id." from eq_id: ".$this->eq_id." but it does not have this role, something is wrong in the code", 425);
	}
	
	public function get_equipment_role($equipment_role_id)
	{
		if (isset($this->_equipment_roles[$equipment_role_id])) {
			
			return $this->_equipment_roles[$equipment_role_id];
			
		} else {
			
			return false;
			
		}
	}
	
	public function get_all_connected_subnets()
	{
		//this does NOT imply conected addresses
		//these subnets may be connected by a DHCP lease
		//not one a static address, but a connected ROUTE

		if ($this->get_interfaces() != null) {
				
			foreach ($this->_interfaces as $interface) {

				if (($if_subnets = $interface->get_connected_subnets()) != false) {
					
					if (!isset($subnets)) {
						
						$subnets = $if_subnets;
						
					} else {
						$subnets = array_merge($subnets, $if_subnets);
					}
				}
			}
		}
		
		if (isset($subnets)) {
			return $subnets;
		} else {
			return false;
		}
	}
	
	public function get_connected_subnet_interface($ip_subnet_obj)
	{
		if ($this->_interfaces == null) {
			$this->get_interfaces();
		}

		if ($this->_interfaces != null) {
		
			foreach ($this->_interfaces as $interfaces) {
				$interface_ips = $interfaces->get_ip_addresses();

				if ($interface_ips != null) {
						
					foreach ($interface_ips as $ip_address) {
						
						if ($ip_subnet_obj->get_ip_subnet_id() == $ip_address->get_ip_subnet_id()) {
							//this interface has an ip address that is in the same subnet as we are looking for
							return $interfaces;
						}
					}
				}
			}
		}
		
		//we dident find a match
		return false;
	}
	
	
	
	public function get_available_subnets()
	{
		//this method returns the parts of the subnets that are currently routed to this equipment
		//but that are not currently routed out from this equipment or used on any interface
		$routed_out = ',';
		
		if ($this->_ip_routes == null) {
			$this->get_ip_routes();
		}
		
		if ($this->_ip_routes != null) {
			
			foreach ($this->_ip_routes as $ip_route) {
				$routed_out .= $ip_route->get_ip_subnet_id() . ",";
			}
		}
		
		$connected_routes	= $this->get_all_connected_subnets();
		
		if ($connected_routes != false) {
			foreach ($connected_routes as $connected_route) {
				$routed_out .= $connected_route->get_ip_subnet_id() . ",";
			}
		}
		
		$subnet_mappings	= $this->get_subnet_mappings();
		if ($subnet_mappings != null) {
			
			foreach ($subnet_mappings as $subnet_mapping) {
		
				$all_child_subnets = $subnet_mapping->get_child_subnets_recursively();
		
				if ($all_child_subnets != null) {

					foreach ($all_child_subnets as $sorted_subnet) {
						
						//if the subnet has no child subnets and no ips and is not already routed out no problem
						if ($sorted_subnet->get_child_subnets() == null && $sorted_subnet->get_inuse_host_ips() == null && !preg_match("/,".$sorted_subnet->get_ip_subnet_id().",/", $routed_out)) {
							$available_subnets[] = $sorted_subnet;
						}
					}
					
				} else {
					
					//if the master subnet is not in the range of used subnets we can include it
					if (!preg_match("/,".$subnet_mapping->get_ip_subnet_id().",/", $routed_out)) {
						$available_subnets[] = $subnet_mapping;
					}
				}
			}
		}

		if (isset($available_subnets)) {
			return $available_subnets;
		} else {
			return false;
		}
		
	}
	
	public function generate_new_subnet($cidr_mask, $subnet_type)
	{
		$available_subnets = $this->get_available_subnets();
		
		if ($available_subnets != false) {
	
			$array_tools	= new Thelist_Utility_arraytools();
			$sorted_subnets	= array_reverse($array_tools->sort_ip_subnets_by_cidr($available_subnets));
			
			foreach($sorted_subnets as $subnet) {

				if ($subnet->get_ip_subnet_cidr_mask() <= $cidr_mask) {
				
					if ($subnet->get_public_or_private() == $subnet_type) {
						
						//subnet cannot have any ips that have been created, because that means it cannot be carved up further
						if ($subnet->get_ip_addresses(true) == null) {
						
							if ($subnet->get_ip_subnet_cidr_mask() == $cidr_mask) {
								return $subnet;
							} else {
								
								//carve out a subnet with the smallest cidr of what we need
								$new_subnets	= $subnet->set_child_subnets($cidr_mask);
								
								//return is biggest to smallest, so we reverse and then reset values, all to make sure the smallest subnet is on top
								//i.e. the size we asked for
								$new_sorted_subnets	= array_values(array_reverse($array_tools->sort_ip_subnets_by_cidr($new_subnets)));
								return $new_sorted_subnets['0'];
							}
						}
					}
				}
			}
			
			//we did not fid a proper match
			throw new exception('no subnet fulfills the requirement', 428);
	
		} else {
			throw new exception('this equipment has no subnets assigned', 421);
		}
	
	}
	
	public function get_subnet_mappings()
	{
		if ($this->_subnet_mappings == null) {

			//get the subnets that have gateways on interfaces on this device
			//basically subnets that have been routed to this equipment
			
			//ORDER IN THIS ARRAY IS IMPORTENT, do not change
			
			$sql =	"SELECT ipsub.ip_subnet_id FROM ip_routes ir
					INNER JOIN ip_route_gateways iprg ON iprg.ip_route_id=ir.ip_route_id
					INNER JOIN ip_address_mapping iam ON iam.ip_address_map_id=iprg.ip_address_map_id
					INNER JOIN interfaces i ON i.if_id=iam.if_id
					INNER JOIN ip_subnets ipsub ON ipsub.ip_subnet_id=ir.ip_subnet_id
					WHERE i.eq_id='".$this->eq_id."'
					AND ipsub.ip_subnet_cidr_mask!='0'
					ORDER BY ipsub.ip_subnet_cidr_mask DESC
					";
			
			$subnets  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);

			if (isset($subnets['0'])) {
				
				foreach($subnets as $subnet) {
					
					//we do it by subnet id so if there are duplicates because of multiple routes then we only 
					//get them once
					$this->_subnet_mappings[$subnet['ip_subnet_id']] = new Thelist_Model_ipsubnet($subnet['ip_subnet_id']);
					
				}
			}
		}
			
		return $this->_subnet_mappings;
	}
	
	public function get_ip_traffic_rules()
	{
		if ($this->_ip_traffic_rules == null) {
			
			//the order is very importent because we rely on it when implementing the ip traffic rules 
			$sql=	"SELECT ip_traffic_rule_id FROM ip_traffic_rules
					WHERE eq_id='".$this->eq_id."'
					ORDER BY ip_traffic_rule_priority ASC
					";
			
			$ip_traffic_rules = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			if (isset($ip_traffic_rules['0'])) {
				
				foreach ($ip_traffic_rules as $ip_traffic_rule) {
					
					$this->_ip_traffic_rules[$ip_traffic_rule['ip_traffic_rule_id']] = new Thelist_Model_iptrafficrule($ip_traffic_rule['ip_traffic_rule_id']);
					
				}
			}
		}
		
		return $this->_ip_traffic_rules;
	}
	
	public function add_new_ip_traffic_rule($ip_traffic_rule_desc, $ip_traffic_rule_chain_id, $ip_traffic_rule_action_id, $ip_traffic_rule_mark, $ip_traffic_rule_priority)
	{
		if ($this->_ip_traffic_rules == null) {
			$this->get_ip_traffic_rules();
		}
		
		if ($ip_traffic_rule_priority == null) {
			//this implies that the calling app wants us to find the next available priority
			$sql = "SELECT MAX(ip_traffic_rule_priority) AS priority FROM ip_traffic_rules
					WHERE eq_id='".$this->eq_id."'
					AND ip_traffic_rule_chain_id='".$ip_traffic_rule_chain_id."'
					";
				
			$result  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);

			if ($result['priority'] != null) {
				$ip_traffic_rule_priority = $result['priority'] + 1;
			} else {
				$ip_traffic_rule_priority = 0;
			}
		}
		
		$trace 		= debug_backtrace();
		$method 	= $trace[0]["function"];
		$class		= get_class($this);
				
		$data1 = array(
							'ip_traffic_rule_desc'			=> $ip_traffic_rule_desc,
							'ip_traffic_rule_chain_id'		=> $ip_traffic_rule_chain_id,
							'ip_traffic_rule_action_id'		=> $ip_traffic_rule_action_id,
							'ip_traffic_rule_mark'			=> $ip_traffic_rule_mark,
							'ip_traffic_rule_priority'		=> $ip_traffic_rule_priority,
							'eq_id'							=> $this->eq_id,
		);
		
		$new_traffic_rule_id = Zend_Registry::get('database')->insert_single_row('ip_traffic_rules',$data1,$class,$method);

		$this->_ip_traffic_rules[$new_traffic_rule_id] = new Thelist_Model_iptrafficrule($new_traffic_rule_id);
	
		return $this->_ip_traffic_rules[$new_traffic_rule_id];
	}
	
	
	public function get_subnet_mapping($ip_subnet_id)
	{
		if ($this->_subnet_mappings == null) {
			$this->get_subnet_mappings();
		}
		
		if (isset($this->_subnet_mappings[$ip_subnet_id])) {
			return $this->_subnet_mappings[$ip_subnet_id];
		} else {
			return false;
		}
	}
	
	public function get_eq_id()
	{
		return $this->eq_id;
	}
	
	public function get_eq_type_id()
	{
		return $this->eq_type_id;
	}
	public function get_eq_type()
	{
		return $this->_eq_type;
	}
	
	public function get_eq_serial_number()
	{
		return $this->_eq_serial_number;
	}
	public function get_eq_second_serial_number()
	{
		return $this->_eq_second_serial_number;
	}
	
	public function get_child_equipments()
	{
		$sql=	"SELECT eq_id FROM equiments
				WHERE eq_master_id='".$this->eq_id."'
				";

		return Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
	}
	
	public function get_id()
	{
		return $this->eq_id;
	}
	
	public function get_interfaces()
	{
		return $this->_interfaces;
	}
	
	public function get_interface($if_id)
	{
		return $this->_interfaces[$if_id];
	}
			
	public function set_eq_master_id( $eq_master_id )
	{		
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		
		Zend_Registry::get('database')->set_single_attribute($this->eq_id,'equipments','eq_master_id',$eq_master_id,get_class($this),$method);
		
		$this->eq_master_id=$eq_master_id;
	} 
	 
	public function get_eq_master_id()
	{
		return $this->eq_master_id;
	}
	
	public function write_to_device($options)
	{
		//this method carries validations, can be used to read or write
		
		try {
				
			if (isset($options['devicefunction'])) {
	
				if ($device_function_obj->get_device_function_parameter_table_name() == 'equipments'){
	
					$command_generator 	= new Thelist_Model_devicecommandgenerator();
	
					$commands_in_xml	= $command_generator->get_commands_in_xml($options['devicefunction'], $this->eq_id);
	
					if ($commands_in_xml == false) {
	
						return false;
					}
	
					return $this->get_device()->execute_xml_with_command_validation($commands_in_xml);
	
				} else {
	
					throw new exception('the requested device function is not defined for equipment objs');
	
				}
	
			} elseif($options['device_configure'] == 'set_interface_default_config') {
	
				return $this->get_device()->set_default_device_interface_configuration($options['interface_obj']);
	
			} elseif($options['device_configure'] == 'set_connection_queue_on_device') {

				return $this->get_device()->set_connection_queue_on_device($options['connection_queue_obj']);
	
			} elseif($options['device_configure'] == 'remove_connection_queue_from_device') {

				return $this->get_device()->remove_connection_queue_from_device($options['connection_queue_obj']);
	
			} elseif($options['device_configure'] == 'remove_interface_queue_disc_from_device') {
	
				return $this->get_device()->remove_interface_queue_disc_from_device($options['interface_obj']);
	
			} elseif($options['device_configure'] == 'set_interface_queue_disc_on_device') {
	
				return $this->get_device()->set_interface_queue_disc_on_device($options['interface_obj']);
	
			} elseif($options['device_configure'] == 'save_config') {
	
				return $this->get_device()->save_config_on_device();
	
			}
			
			
	
		} catch (Exception $e) {
	
			switch($e->getCode()){
	
				default;
				throw $e;
	
			}
		}
	}


	public function read_from_device($options)
	{
		//only use this method to read data from a device. there are no validations on the return data
		//NEVER set attributes on devices using this method
		//if you need to write to device use $this->write_to_device

		try {
			
			if (isset($options['devicefunction'])) {
				
				if ($device_function_obj->get_device_function_parameter_table_name() == 'equipments'){
				
					$command_generator 	= new Thelist_Model_devicecommandgenerator();
				
					$commands_in_xml	= $command_generator->get_commands_in_xml($options['devicefunction'], $this->eq_id);
				
					if ($commands_in_xml == false) {
				
						return false;
					}
				
					return $this->get_device()->execute_xml_no_command_validation($commands_in_xml);
						
				} else {
						
					throw new exception('the requested device function is not defined for equipment objs');
						
				}
				
			} elseif($options['deviceinformation'] == 'getarptable') {
				
				return $this->get_device()->get_arp_table();
				
			} elseif($options['deviceinformation'] == 'getcamtable') {
				
				return $this->get_device()->get_cam_table();
				
			} elseif($options['deviceinformation'] == 'icmpsweepsubnet') {
			
				return $this->get_device()->icmp_sweep_subnet('10.245.134.0/24');
			
			} elseif($options['deviceinformation'] == 'get_running_operating_system') {
				
				return $this->get_device()->get_running_os_package();
				
			} elseif($options['deviceinformation'] == 'get_device_interface_configuration') {
			
				return $this->get_device()->get_device_interface_configuration($options['interface_obj']);
			
			} elseif($options['deviceinformation'] == 'get_interfaces_from_device') {
			
				return $this->get_device()->get_device_interfaces();
			
			} elseif($options['deviceinformation'] == 'get_device_connection_queues') {
			
				return $this->get_device()->get_device_connection_queues();
			
			} elseif($options['deviceinformation'] == 'get_device_connection_queue_discs') {
			
				return $this->get_device()->get_device_connection_queue_discs();
			
			} elseif($options['deviceinformation'] == 'logout') {
			
				$result = $this->get_device()->logout_of_device();
				//set device null
				$this->_device = null;
				return $result;
				
			
			}  elseif($options['deviceinformation'] == 'device_connection_status') {
			
				return $this->get_device()->get_connection_status();
			}

		} catch (Exception $e) {
		
			switch($e->getCode()){
		
				case 1200;
				//1200, this means the method is not defined for this device type
				throw $e;
				break;
				case 401;
				//401, interface provided is not part of this equipment
				throw $e;
				break;
				default;
				throw $e;
		
			}
		}
	}
	
// 	public function update_equipment_from_device($options)
// 	{
		
		//only use this method to read data from a device and update database.
		//NEVER set attributes on devices using this method
		//if you need to write to device use $this->write_to_device
		
// 		try {
		
// 			if($options['deviceinformation'] == 'updateinterfacemacaddress') {
					
// 				if (isset($this->_interfaces[$options['interface']->get_if_id()])) {
						
// 					$mac_address_obj	= $this->get_device()->get_interface_mac_address($this->_interfaces[$options['interface']->get_if_id()]);
// 					$this->_interfaces[$options['interface']->get_if_id()]->set_if_mac_address($mac_address_obj->get_macaddress());

// 				} else {
						
// 					throw new exception("interface provided does not exist on this equipment = ".$this->eq_id."", 401);
						
// 				}
// 			} elseif($options['deviceinformation'] == 'updateallinterfacemacaddresses') {
				
// 				if ($this->_interfaces != null) {
					
// 					foreach ($this->_interfaces as $interface) {
						
// 						$mac_address_obj	= $this->get_device()->get_interface_mac_address($interface);
// 						$interface->set_if_mac_address($mac_address_obj->get_macaddress());
						
// 					}
// 				}
// 			}
		
// 		} catch (Exception $e) {
		
// 			switch($e->getCode()){
		
// 				case 1200;
			////	1200, this means the method is not defined for this device type
// 				throw $e;
// 				break;
// 				case 800;
				////800, r
// 				throw $e;
// 				break;
// 				default;
// 				throw $e;
		
// 			}
// 		}
// 	}
	
	public function currentEquipmentUnit()
	{
	
		$sql = "SELECT unit_id FROM equipment_mapping
				WHERE eq_id='".$this->eq_id."'
				AND (eq_map_deactivated IS NULL OR eq_map_deactivated > NOW())
				";
	
		$unit = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		if (!isset($unit['0'])) {
			
			return false;			
			
		} elseif (isset($unit['1'])) {

			throw new exception("this equipment is mapped to more than one unit, thats really bad. eq_id = ".$this->eq_id."");
			
		} else {
			
			return new Thelist_Model_units($unit['0']['unit_id']);
			
		}
	}
	
	
	public function get_device($api_id=null)
	{
	
		//make a connection to the device in the field.
		if ($this->_device == null) {
	
			//get the master equipment that will be the device we execute on as it is the only addressable equipment
		//	Zend_Registry::get('database')->get_thelist_adapter()->fetchOne('LOCK TABLE temp_int_table WRITE');
			$sql5 = 	"CALL find_equipment_master('".$this->eq_id."')";
			$master_eq_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql5);
		//	Zend_Registry::get('database')->get_thelist_adapter()->fetchOne('UNLOCK TABLES');

			if ($this->eq_id != $master_eq_id) {
					
				$master_eq_row 	= Zend_Registry::get('database')->get_equipments()->fetchRow('eq_id='.$master_eq_id);
				$device_eq_id 	= $master_eq_row['eq_id'];
				$device_fqdn	= $master_eq_row['eq_fqdn'];
					
			} else {
					
				$device_eq_id 	= $this->eq_id;
				$device_fqdn 	= $this->eq_fqdn;
					
			}
	
			//get credentials for this equipment
			$credential_obj = new Thelist_Model_deviceauthenticationcredential();
			$credential_obj->get_equipment_credentials($device_eq_id, $api_id);
	
	
			$device 			= new Thelist_Model_device($device_fqdn, $credential_obj);
			$this->_device		= $device;
			
			return $this->_device;
	
		} else {
	
			return $this->_device;
	
		}
	}
	
	public function update_default_interface_configurations()
	{
		$interfaces = $this->get_interfaces();
		
		if ($interfaces != null) {
			foreach ($interfaces as $interface) {
				$interface->update_default_interface_configurations();
			}
		}
	}
	
	
	public function update_static_interfaces()
	{
		
		//this will create all interfaces and update the default features at the same time
		
		$sql = "SELECT * FROM static_if_types
				WHERE eq_type_id='".$this->eq_type_id."'
				";
	
		$static_if_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
	
		if (isset($static_if_types['0'])) {
	
			foreach ($static_if_types as $static_if_type) {
	
				try {
						
					$new_interface = $this->add_new_interface($static_if_type['if_index_number'], $static_if_type['if_default_name'], $static_if_type['if_type_id'], 'na');
					$new_interface->update_default_interface_features();
	
				} catch (Exception $e) {
	
					switch($e->getCode()){
	
						case 407;
						//407,this is an update function, ignore that the interface name already exists
						break;
						case 408;
						//408,this is an update function, ignore that the interface mac already exists
						break;
						default;
						throw $e;
	
					}
				}
			}
		}
	}
	
	public function remove_ip_route($ip_route_id, $transaction_safe=false)
	{
		if ($this->_ip_routes == null) {
			$this->get_ip_routes();
		}
		
		if (isset($this->_ip_routes[$ip_route_id])) {
			
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
			
			if ($transaction_safe == true) {
					
				//because we cannot nest db transactions in php we have to make this optional.
				Zend_Registry::get('database')->get_thelist_adapter()->beginTransaction();
			}
			
			//because we cannot delete a gateway if it is the only one left (must delete the entire route)
			//we dont use the remove route method under the class, in this method we always get rid of the entire route
			//so there is no ambiguity
			
			$route_gateways	= $this->_ip_routes[$ip_route_id]->get_ip_route_gateways();
			
			//first remove all the gateways
			foreach ($route_gateways as $route_gateway) {
				Zend_Registry::get('database')->delete_single_row($route_gateway['ip_route_gateway_id'], 'ip_route_gateways', $class, $method);
				
			}
			
			//then remove the route
			Zend_Registry::get('database')->delete_single_row($ip_route_id, 'ip_routes', $class, $method);
				
			//unset it from here
			unset($this->_ip_routes[$ip_route_id]);
			
			if ($transaction_safe == true) {
				Zend_Registry::get('database')->get_thelist_adapter()->commit();
			}
			
		} else {
			throw new exception("you are removing ip_route_id: ".$ip_route_id." from eq_id: ".$this->eq_id.", but this route does not belong to that equipment ", 428);
		}
	}
	
	public function get_all_reachable_host_ips($refresh=false)
	{
		//method should get all ips that are mapped to subnets that are shared
		//by this equipment. omit our own ip and return all the ips that are 
		//mapped to another interface and reachable from any of this equipments interfaces.
		
		if ($this->_all_reachable_host_ips == null || $refresh == true) {
		
			if ($this->_interfaces != null) {
			
				foreach($this->_interfaces as $interface) {
						
					$host_ips = $interface->get_reachable_host_ips($refresh);
					
					if ($host_ips != null) {
						
						if ($this->_all_reachable_host_ips == null) {
							$this->_all_reachable_host_ips = array();
						}
						
						$this->_all_reachable_host_ips = array_merge($this->_all_reachable_host_ips, $host_ips);
					}
				}
			}
		}
			
		return $this->_all_reachable_host_ips;
	}
	
	public function add_new_route($subnet_id, $gateway_ip_map, $gateway_cost)
	{
		
		if (is_int($subnet_id) && is_int($gateway_ip_map) && is_int($gateway_cost)) {
			
			$sql = 	"SELECT COUNT(ip_subnet_id) ipscount FROM ip_subnets
					WHERE ip_subnet_id='".$subnet_id."'
					";
			
			$ipscount  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
			if ($ipscount != 1) {
				throw new exception('that subnet id does not exist', 422);
			}
			
			$sql2 = 	"SELECT COUNT(ip_address_map_id) ipmapcount FROM ip_address_mapping
						WHERE ip_address_map_id='".$gateway_ip_map."'
						";
				
			$ipmapcount  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
			
			if ($ipmapcount != 1) {
				throw new exception('that ip address map id does not exist', 423);
			}
			
			$sql3 = 	"SELECT ip_route_id FROM ip_routes ipr
						INNER JOIN ip_route_gateways iprg ON iprg.ip_route_id=ipr.ip_route_id
						WHERE ipr.eq_id='".$this->eq_id."'
						AND ipr.ip_subnet_id='".$subnet_id."'
						AND iprg.ip_address_map_id='".$gateway_ip_map."'
						AND iprg.ip_route_gateway_cost='".$gateway_cost."'
						";
		
			$current_route_id  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		}
		if (!isset($current_route_id['ip_route_id'])) {
			
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
			
			//setup the route
			$data = array(
						'eq_id'    					=>  $this->eq_id,
						'ip_subnet_id'   			=>  $subnet_id,
			);
			
			
				
			$ip_route_id = Zend_Registry::get('database')->insert_single_row('ip_routes',$data,$class,$method);
			
			$data2 = array(
								'ip_address_map_id'     	=>  $gateway_ip_map,
								'ip_route_gateway_cost'     =>  $gateway_cost,
								'ip_route_id'    			=>  $ip_route_id,
			);
			
			$ip_route_gateway_id = Zend_Registry::get('database')->insert_single_row('ip_route_gateways',$data2,$class,$method);

			if ($this->_ip_routes == null) {
				$this->get_ip_routes();
			}
			
			$this->_ip_routes[$ip_route_id]	= new Thelist_Model_iproute($ip_route_id);
			
			return $this->_ip_routes[$ip_route_id];
			
		} else {
			
			return $this->_ip_routes[$current_route_id];
			
		}

	
	}
	
	public function remove_interface($if_id, $transaction_safe=false)
	{
		if ($this->_interfaces == null) {
			$this->get_interfaces();
		}
		
		if ($transaction_safe == true) {
			
			//because we cannot nest db transactions in php we have to make this optional.
			Zend_Registry::get('database')->get_thelist_adapter()->beginTransaction();
		}
	
		if (isset($this->_interfaces[$if_id])) {
			
			//now destruct everything that is tied to the interface
			
			//routes pointing to if_maps on this interface
			//this may be a mistake but since the ip map no longer exists the gateway map is invalid
			//rather remove the maps than suffer invalid data in database

			//remove all ip addresses
			$ip_address_maps = $this->_interfaces[$if_id]->get_ip_addresses();
			if ($ip_address_maps != null) {
				foreach ($ip_address_maps as $ip_address) {
					
					//remove all route maps
					$ip_address->remove_ip_route_gateway_maps();
					
					//then remove the ip itself from the interface
					$this->_interfaces[$if_id]->remove_ip_address_map($ip_address);
				}
			}
			
			//remove all interface connections
			$interface_connections	= new Thelist_Model_interfaceconnections();
			$current_connections	= $interface_connections->get_interface_connections($this->_interfaces[$if_id]);
			if ($current_connections != false) {
				foreach ($current_connections as $connection_interface) {
					$interface_connections->remove_interface_connection($this->_interfaces[$if_id], $connection_interface);
				}
			}
			
			//remove all interface relationships
			$interface_relationships	= new Thelist_Model_interfacerelationships();
			$current_masters	= $this->_interfaces[$if_id]->get_slave_relationships();
			$current_slaves		= $this->_interfaces[$if_id]->get_master_relationships();
			
			//remove masters
			if ($current_masters != null) {
				foreach ($current_masters as $master) {
					$interface_relationships->remove_interface_relationship($master, $this->_interfaces[$if_id]);
				}
			}
			
			//remove slaves
			if ($current_slaves != null) {
				foreach ($current_slaves as $slave) {
					$interface_relationships->remove_interface_relationship($this->_interfaces[$if_id], $slave);
				}
			}

			//remove all interface features
			$if_feature_maps = $this->_interfaces[$if_id]->get_if_features();
			if ($if_feature_maps != null) {
				foreach ($if_feature_maps as $if_feature_map) {
					$this->_interfaces[$if_id]->remove_interface_feature_map($if_feature_map->get_mapped_if_feature_map_id());
				}
			}

			//remove all interface configuration maps
			$if_configuration_maps = $this->_interfaces[$if_id]->get_interface_configurations();
			if ($if_configuration_maps != null) {
				foreach ($if_configuration_maps as $if_configuration_map) {
					//invoke override when deleting mandetory configs
					$this->_interfaces[$if_id]->remove_interface_configuration($if_configuration_map->get_mapped_if_conf_map_id(), true);
				}
			}
			
			//on this equipment the interface may particiate in traffic rules, if it does we have to check if it is the only interface in a particular
			//role like outbound interface. if it is the only one in the role we remove the entire traffic rule so as not to break the requirements
			//for traffic rules
			$traffic_rules	= $this->get_ip_traffic_rules();
			
			if ($traffic_rules != null) {
				
				foreach($traffic_rules as $traffic_rule) {
					
					$traf_interfaces	= $traffic_rule->get_interfaces();
					
					if ($traf_interfaces != null) {
						
						foreach($traf_interfaces as $traf_interface) {
							
							//we found the interface as part of a traffic rule
							if ($traf_interface->get_if_id() == $this->_interfaces[$if_id]->get_if_id()) {
									
								$multiple_interfaces_with_same_role	= 'no';
								//now we get the role and see if there are any other interface that also hold this role
								//if there is we just remove the map, if there is not we remove the entire rule
								//the reason is that a nat rule requires an outbound interface and if we just get rid of the interface 
								//then the rule breaks, and we need the database to be consistent above all
								$traf_role_id	= $traf_interface->get_ip_traffic_rule_if_role_id();
								
								foreach($traf_interfaces as $traf_interface2) {
									
									if ($traf_interface2->get_if_id() != $this->_interfaces[$if_id]->get_if_id()) {
										
										if ($traf_interface2->get_ip_traffic_rule_if_role_id() == $traf_role_id) {
											
											//there is a second interface that has the same role, we just remove the map
											$multiple_interfaces_with_same_role	= 'yes';
										}
									}
								}
								
								//the interface is part of this traffic rule
								if ($multiple_interfaces_with_same_role	== 'yes') {
									
									//there are multiple interfaces covering the same role, so we just remove this
									//interface map from the rule
									$traffic_rule->remove_traffic_interface($this->_interfaces[$if_id]);
									
								} else {
									
									//this is the only interface in the role, so we remove the entire rule
									$this->remove_traffic_rule($traffic_rule);
								}
							}
						}
					}
				}
			}
			
			//now eveything the interface is tied to has been removed, so we remove the interface it self
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);

			Zend_Registry::get('database')->delete_single_row($if_id, 'interfaces', $class, $method);
			
			//unset it from here
			unset($this->_interfaces[$if_id]);
			
			if ($transaction_safe == true) {
				Zend_Registry::get('database')->get_thelist_adapter()->commit();
			}
			
		} else {
			throw new exception("you are trying to remove interface if_id: ".$if_id." from eq_id: ".$this->eq_id." but the interface does not belong to that equipment", 426);
		}
		
	}
	
	public function remove_traffic_rule($traffic_rule_obj)
	{
		if ($this->_ip_traffic_rules == null) {
			$this->get_ip_traffic_rules();
		}
		
		$trace 		= debug_backtrace();
		$method 	= $trace[0]["function"];
		$class		= get_class($this);
		
		$ip_traffic_rule_id = $traffic_rule_obj->get_ip_traffic_rule_id();
		
		if (isset($this->_ip_traffic_rules[$ip_traffic_rule_id])) {	
			//first remove the interfaces, we want to use the object from this class not the provided one
			$traf_interfaces	= $this->_ip_traffic_rules[$ip_traffic_rule_id]->get_interfaces();
			if ($traf_interfaces != null) {
				foreach($traf_interfaces as $traf_interface) {
					$this->_ip_traffic_rules[$ip_traffic_rule_id]->remove_traffic_interface($traf_interface);
				}
			}
			
			//remove the subnets
			$traf_subnets	= $this->_ip_traffic_rules[$ip_traffic_rule_id]->get_ip_traffic_rule_ip_subnets();
			if ($traf_subnets != null) {
				foreach($traf_subnets as $traf_subnet) {
					$this->_ip_traffic_rules[$ip_traffic_rule_id]->remove_traffic_ip_subnet($traf_subnet['ip_traffic_rule_ip_subnet_map_id']);
				}
			}

			//remove the ports
			$traf_ports	= $this->_ip_traffic_rules[$ip_traffic_rule_id]->get_ip_traffic_rule_ip_ports();
			if ($traf_ports != null) {
				foreach($traf_ports as $traf_port) {
					$this->_ip_traffic_rules[$ip_traffic_rule_id]->remove_traffic_ip_protocol_port_mapping($traf_port['ip_protocol_port_map_id']);
				}
			}
			
			//last remove the trafic rule it self
			Zend_Registry::get('database')->delete_single_row($ip_traffic_rule_id, 'ip_traffic_rules', $class, $method);
				
			//unset it from here
			unset($this->_ip_traffic_rules[$ip_traffic_rule_id]);

		} else {
			throw new exception("you are trying to remove trafic_rule_id: ".$traffic_rule_obj->get_ip_traffic_rule_id()." from eq_id: ".$this->eq_id." but the rule does not belong to that equipment", 427);
		}
	}
	
	
	public function add_new_interface($if_index, $if_name, $if_type_id, $if_mac_address, $service_point_id=null, $master_if_id=null, $allow_duplicate_mac=false)
	{
		
		//data integrity check
		if ($if_mac_address != 'na') {
			
			//this will throw exception 800 wrong format
			$mac_check	= new Thelist_Deviceinformation_macaddressinformation($if_mac_address);
			
			//override the old var so we are sure it is formatted correctly before we insert
			$if_mac_address = $mac_check->get_macaddress();
		}
		
		
		//if the index is not set we calculate it
		if ($if_index == null || $if_index == '') {
			
			$sql = "SELECT MAX(if_index) AS if_index FROM interfaces
					WHERE eq_id='".$this->eq_id."'
					";
			
			$current_max_if_index  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
			if (isset($current_max_if_index['if_index'])) {
				
				$if_index = $current_max_if_index + 1;
				
			} else {
				$if_index = 0;
			}
		}

		if (!is_numeric($if_index)) {
			throw new exception('if index must be numeric', 410);
		}
		
		if (!is_numeric($if_type_id)) {
			throw new exception('if type does not exist', 410);
		}
		
		if (!is_numeric($service_point_id) && $service_point_id != null) {
			throw new exception('service point does not exist', 411);
		}

		//does this equipment already have an interface by this name?
		if ($this->_interfaces != null) {
			
			foreach ($this->_interfaces as $interface) {
	
				if ($interface->get_if_name() == $if_name) {
					
					//same name is not ok
					throw new exception('the interface name already exists on this equipment', 407);
						
				}
				
				if (($interface->get_if_mac_address() == $if_mac_address && $allow_duplicate_mac == false) && $if_mac_address != 'na') {
					
					throw new exception('the interface mac already exists on this equipment', 408);
				}
			}
		}
		
		
		
	
			
		//create the new interface
		$data = array(
								'eq_id'    				=>  $this->eq_id,
								'if_index'   			=>  $if_index,
								'if_name'     			=>  $if_name,
								'if_type_id'     		=>  $if_type_id,
								'if_mac_address'     	=>  $if_mac_address,
								'service_point_id'     	=>  $service_point_id,
											
		);
			
		$trace 		= debug_backtrace();
		$method 	= $trace[0]["function"];
		$class		= get_class($this);
			
		$if_id = Zend_Registry::get('database')->insert_single_row('interfaces',$data,$class,$method);
			
		$this->_interfaces[$if_id] = new Thelist_Model_equipmentinterface($if_id);
		
		if ($master_if_id != null) {
			
			if (isset($this->_interfaces[$master_if_id])) {
				
				$relationship = new Thelist_Model_interfacerelationships();
				$return_array = $relationship->create_interface_relationship($this->_interfaces[$master_if_id], $this->_interfaces[$if_id]);
					
				//update the interfaces
				$this->_interfaces[$if_id] = $return_array['slave'];
				$this->_interfaces[$master_if_id] = $return_array['master'];
				
			} else {
				
				throw new exception('the master interface is not located on this equipment, you cannot have a master on another device', 418);
				
			}
		}
			
		return $this->_interfaces[$if_id];
	
	}

	public function get_apis()
	{
		
		$sql = "SELECT eq_api_id FROM equipment_apis ea
				WHERE ea.eq_id='".$this->eq_id."'
				";
		
		$auths = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		if (isset($auths['0'])) {
			
			foreach($auths as $auth) {
				
				$credentials[$auth['eq_api_id']]	= new Thelist_Model_deviceauthenticationcredential();
				$credentials[$auth['eq_api_id']]->fill_from_eq_api_id($auth['eq_api_id']);
				
			}
			
			return $credentials;
			
		} else {
			return false;
		}
	}
	
	public function get_credential($api_id)
	{
		$sql = "SELECT eq_api_id FROM equipment_apis ea
				WHERE ea.eq_id='".$this->eq_id."'
				AND ea.api_id='".$api_id."'
				";
		
		$eq_api_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		if (isset($eq_api_id['eq_api_id']))  {
			
			$credential	= new Thelist_Model_deviceauthenticationcredential();
			$credential->fill_from_eq_api_id($eq_api_id);
			
			return $credential;
			
		} else {
			
			throw new exception('API does not exist', 402);
			
		}
	}
	
	private function does_api_exist($api_id)
	{
		$sql = 	"SELECT COUNT(eq_api_id) FROM equipment_apis
					WHERE eq_id='".$this->eq_id."'
					AND api_id='".$api_id."'
					";
	
		$exist = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
	
		if ($exist == '0') {
			return false;
		} elseif ($exist == '1') {
			return true;
		} else {
			throw new exception('this equipment api is in the database more than once');
		}
	}
	
	public function delete_api_auth($eq_api_id)
	{
		$sql = 	"SELECT COUNT(eq_api_id) FROM equipment_apis
					WHERE eq_id='".$this->eq_id."'
					AND eq_api_id='".$eq_api_id."'
					";
			
		$belong_to_this_eq = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
	
		if ($belong_to_this_eq == 1) {
				
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
				
			//delete the eq_api
			Zend_Registry::get('database')->delete_single_row($eq_api_id, 'equipment_apis', $class, $method);
				
			//find all the auth values and delete them
			$sql = 	"SELECT eq_auth_id FROM equipment_auths
					WHERE eq_api_id='".$eq_api_id."'
					";
	
			$auths = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
				
			if (isset($auths['0'])) {
	
				foreach($auths as $auth) {
						
					//delete the eq_api
					Zend_Registry::get('database')->delete_single_row($auth['eq_auth_id'], 'equipment_auths', $class, $method);
						
				}
			}
		} else {
			throw new exception('trying to delete a credential that does not belong to this equipment', 406);
		}
	}

	public function create_api_auth($device_authentication_credentials, $update=false)
	{
	
		$api_id = $device_authentication_credentials->get_api_id();
	
		if ($this->does_api_exist($api_id) && $update == false) {
				
			throw new exception('api already exists for this equipment and we are not told to update', 405);
				
		} elseif ($this->does_api_exist($api_id) && $update == true) {
				
			$sql = 	"SELECT eauth.* FROM equipment_apis ea
						INNER JOIN equipment_auths eauth ON eauth.eq_api_id=ea.eq_api_id
						WHERE ea.eq_id='".$this->eq_id."'
						AND ea.api_id='".$api_id."'
						";
				
			$auths = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
				
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
				
			foreach ($auths as $auth) {
	
				foreach ($auth as $key => $value) {
						
					if ($key == 'auth_type' && $value == 'username') {
	
						Zend_Registry::get('database')->set_single_attribute($auth['eq_auth_id'], 'equipment_auths', 'auth_value', $device_authentication_credentials->get_device_username(), $class, $method);
							
					} elseif ($key == 'auth_type' && $value == 'password') {
	
						Zend_Registry::get('database')->set_single_attribute($auth['eq_auth_id'], 'equipment_auths', 'auth_value', $device_authentication_credentials->get_device_password(), $class, $method);
							
					} elseif ($key == 'auth_type' && $value == 'enablepassword') {
	
						Zend_Registry::get('database')->set_single_attribute($auth['eq_auth_id'], 'equipment_auths', 'auth_value', $device_authentication_credentials->get_device_enablepassword(), $class, $method);
							
					}
				}
			}
		} elseif (!$this->does_api_exist($api_id)) {
				
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
				
			$data = array(
	
								'eq_id'		=> $this->eq_id,
								'api_id'	=> $device_authentication_credentials->get_api_id(),
	
			);
				
			$eq_api_id = Zend_Registry::get('database')->insert_single_row('equipment_apis', $data, $class, $method);
				
			//if there is a username, create it
			if ($device_authentication_credentials->get_device_api_name() == 'ssh') {
	
				if ($device_authentication_credentials->get_device_username() != null && $device_authentication_credentials->get_device_password() != null) {
						
					$data2 = array(
						
														'eq_api_id'							=> $eq_api_id,
														'auth_type'							=> 'username',
														'auth_value'						=> $device_authentication_credentials->get_device_username(),
						
					);
	
					Zend_Registry::get('database')->insert_single_row('equipment_auths', $data2, $class, $method);
						
					$data3 = array(
						
														'eq_api_id'							=> $eq_api_id,
														'auth_type'							=> 'password',
														'auth_value'						=> $device_authentication_credentials->get_device_password(),
						
					);
	
					Zend_Registry::get('database')->insert_single_row('equipment_auths', $data3, $class, $method);
						
						
				} else {
						
					throw new exception('ssh specified but either username or password is missing from the auth object', 404);
						
				}
	
	
			} elseif ($device_authentication_credentials->get_device_api_name() == 'telnet') {
	
				if ($device_authentication_credentials->get_device_password() != null && $device_authentication_credentials->get_device_enablepassword() != null) {
	
					$data2 = array(
	
															'eq_api_id'							=> $eq_api_id,
															'auth_type'							=> 'password',
															'auth_value'						=> $device_authentication_credentials->get_device_password(),
	
					);
	
					Zend_Registry::get('database')->insert_single_row('equipment_auths', $data2, $class, $method);
	
					$data3 = array(
	
															'eq_api_id'							=> $eq_api_id,
															'auth_type'							=> 'enablepassword',
															'auth_value'						=> $device_authentication_credentials->get_device_enablepassword(),
	
					);
	
					Zend_Registry::get('database')->insert_single_row('equipment_auths', $data3, $class, $method);
	
	
				} else {
	
					throw new exception('telnet specified but either password or enablepassword is missing from the auth object', 403);
	
				}
			} else {
				
				//catch all others
				
				//username
				if ($device_authentication_credentials->get_device_username() != null) {
				
					$data1 = array(
				
										'eq_api_id'							=> $eq_api_id,
										'auth_type'							=> 'username',
										'auth_value'						=> $device_authentication_credentials->get_device_username(),
				
					);
				
					Zend_Registry::get('database')->insert_single_row('equipment_auths', $data1, $class, $method);
				}
				
				//password
				if ($device_authentication_credentials->get_device_password() != null) {
				
					$data2 = array(
				
										'eq_api_id'							=> $eq_api_id,
										'auth_type'							=> 'password',
										'auth_value'						=> $device_authentication_credentials->get_device_password(),
				
					);
				
					Zend_Registry::get('database')->insert_single_row('equipment_auths', $data2, $class, $method);
				}
				
				//enable password
				if ($device_authentication_credentials->get_device_enablepassword() != null) {
				
					$data3 = array(
					
										'eq_api_id'							=> $eq_api_id,
										'auth_type'							=> 'enablepassword',
										'auth_value'						=> $device_authentication_credentials->get_device_enablepassword(),
					
					);
				
					Zend_Registry::get('database')->insert_single_row('equipment_auths', $data3, $class, $method);
				
				}
			}
		}
	}
	
	public function create_bridge_interface($interface_objs, $interface_name)
	{
		
		if (is_array($interface_objs)) {
			
			//first make sure all interfaces belong to this device
			foreach($interface_objs as $interface) {
				
				if (!isset($this->_interfaces[$interface->get_if_id()])) {

					throw new exception('all interfaces in the bridge must belong to this equipment', 420);
				}
			}
			
			$new_bridge	= $this->add_new_interface(null, $interface_name, 90, 'na', null, null, null, null, false);
			
			$interface_relationships = new Thelist_Model_interfacerelationships();
			
			//add all the interfaces to the bridge
			foreach($interface_objs as $interface) {
			
				$interface_relationships->create_interface_relationship($new_bridge, $interface);
			}
			
			return $new_bridge;
			
		} else {
			throw new exception('please supply array of interfaces to build a bridge, if you dont want to include any interfaces then use add_interface', 419);
		}
	}
	
	
	
	private function get_device_commands($device_function_id, $pri_key)
	{
		$command_generator = new devicecommandgenerator();
	
		$commands_from_generator = $command_generator->get_commands_by_function_id_xml($device_function_id, $pri_key);
	
		if ($commands_from_generator == false) {
				
			return false;
				
		} else {
				
			$command_xml = new SimpleXMLElement($commands_from_generator);
			return $command_xml->xpath('/equipment_commands/command_element');
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	public function set_eq_fqdn($eq_fqdn)
	{
		
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
				
		Zend_Registry::get('database')->set_single_attribute($this->eq_id,'equipments','eq_fqdn',$eq_fqdn,get_class($this),$method);
		
		$this->log_equipment_action('update fqdn');
		
		$this->eq_fqdn=$eq_fqdn;
	}
	
	public function set_serial_number($serial_number)
	{
	
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
	
		Zend_Registry::get('database')->set_single_attribute($this->eq_id,'equipments','eq_serial_number',$serial_number,get_class($this),$method);
	
		$this->_eq_serial_number=$serial_number;
	}
	
	public function set_second_serial_number($serial_number)
	{
	
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
	
		Zend_Registry::get('database')->set_single_attribute($this->eq_id,'equipments','eq_second_serial_number',$serial_number,get_class($this),$method);
	
		$this->_eq_second_serial_number=$serial_number;
	}
	
	public function get_eq_fqdn()
	{
		return $this->eq_fqdn;
	}
	
	public function log_equipment_action($action, $desc=null, $if_id=null)
	{
		if (is_numeric($action)) {
			$action_id = $action;
		} else {
			
			$sql="SELECT action_id
						  FROM actions
						  WHERE action_name='".$action."'
						  AND action_group='equipments'
						  ";
			
			$action_id  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
		}
		
		
		if ($action_id != '') {
			
			$data = array(
									'eq_id'     =>   $this->eq_id,
									'if_id'     =>   $if_id,
									'uid'	    =>   $this->user_session->uid,
									'action_id' =>   $action_id,
									'desc'		=>   $desc
			);
			
			$this->logs->get_equipment_logger()->insert($data);
			
		}

	}
	
	public function set_eq_second_serial_number($eq_second_serial_number){
		
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		
		Zend_Registry::get('database')->set_single_attribute($this->eq_id,'equipments','eq_second_serial_number',$eq_second_serial_number,get_class($this),$method);
		
		$this->log_equipment_action('update second_serial_number');
		
		$this->_eq_second_serial_number=$eq_second_serial_number;
	}	
	

	public function set_po_item_id($po_item_id){
		
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		
		Zend_Registry::get('database')->set_single_attribute($this->eq_id,'equipments','po_item_id',$po_item_id,get_class($this),$method);
		
		$this->po_item_id=$po_item_id;
	}
	
	public function get_po_item_id(){
		return $this->po_item_id;
	}
	
	public function set_owner($uid){
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		
		Zend_Registry::get('database')->set_single_attribute($this->eq_id,'equipments','uid',$uid,get_class($this),$method);
		
		$this->log_equipment_action('change owner');
	
		$this->uid=$uid;
	}
	
	public function get_owner()
	{
		return $this->uid;
	}
// 	public function update_interface_configuration($if_id)
// 	{
		
// 		$this->get_interface[$if_id]->update_default_interface_configurations($this->_eq_type->get_eq_type_software_map_id($this->get_running_software_package_id()));
		
// 	}
	
	public function update_all_interface_configurations()
	{
		
		//because the software package id 
		if ($this->_interfaces != null) {
			foreach ($this->_interfaces as $interface) {
				$interface->update_default_interface_configurations();
			}
		}
	}
	
	public function set_current_software_package($software_package_obj)
	{

		$data = array(
		
				'eq_id'    							=>  $this->eq_id,
				'software_package_id'   			=>  $software_package_obj->get_software_package_id(),
				'scheduled_upgrade_time' 			=>  $this->_time->get_current_date_time_mysql_format(),
				'result'     						=>  'success',
				'result_timestamp'     				=>  $this->_time->get_current_date_time_mysql_format(),
			
		);
			
		$trace 		= debug_backtrace();
		$method 	= $trace[0]["function"];
		$class		= get_class($this);
			
		Zend_Registry::get('database')->insert_single_row('equipment_software_upgrades',$data,$class,$method);	
	}
	
	//temp function for Joseph, do not use
	public function backup_device()
	{
		if ($this->get_device()->get_device_type() == 'cisco') {
			
			//set the terminal so it does not page
			$set_terminal	= new Thelist_Cisco_command_setterminal($this->get_device(), 80, 0);
			$set_terminal->execute();
			
			//get the root folder
			$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->get_device());
			$get_connection_root_folder->execute();
				
			$reply = $this->get_device()->execute_command('show run');

			//set the terminal back to standard
			$set_terminal	= new Thelist_Cisco_command_setterminal($this->get_device(), 80, 25);
			$set_terminal->execute();
			
			//log it
			$this->log_equipment_action(11, $reply->get_message(), null);
			
		} elseif ($this->get_device()->get_device_type() == 'routeros') {
			
			$reply = $this->get_device()->execute_command('/export');
			
			//log it
			$this->log_equipment_action(11, $reply->get_message(), null);
		
		} elseif ($this->get_device()->get_device_type() == 'bairos') {

			//backup dhcp config
			$dhcp = new Thelist_Bairos_command_getfilecontent($this->get_device(), '/etc/dhcpd.conf');
			$this->log_equipment_action(12, $dhcp->get_content(), null);
			
			//backup speed config
			$speed = new Thelist_Bairos_command_getfilecontent($this->get_device(), '/etc/speed');
			$this->log_equipment_action(13, $speed->get_content(), null);

		} else {
			//dont know how to backup
			return false;
		}
	}
	
// 	public function update_api($auth_obj)
// 	{
		
// 		$trace 		= debug_backtrace();
// 		$method 	= $trace[0]["function"];
// 		$class		= get_class($this);
		
// 		$sql = "SELECT eauth.* FROM equipment_apis ea
// 				INNER JOIN equipment_auths eauth ON eauth.eq_api_id=ea.eq_api_id
// 				WHERE ea.eq_id='".$this->eq_id."'
// 				AND ea.api_id='".$auth_obj->get_api_id()."'
// 				";
		
// 		$auths = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		////update the current eq api if it exists
// 		if (isset($auths['0'])) {
			
			////usernames
// 			if ($auth_obj->get_device_username() != null) {
				
// 				$tracking['username']			= 'no';
				
// 				$i=0;
// 				foreach($auths as $auth) {
					
// 					if ($auth['auth_type'] == 'username') {
						
// 						Zend_Registry::get('database')->set_single_attribute($auth['eq_auth_id'], 'equipment_auths', 'auth_value', $auth_obj->get_device_username(), $class, $method);
// 						$auths[$i]['status'] 			= 'updated';
// 						$tracking['username']			= 'yes';
						
// 					}
				
// 					$i++;
// 				}
				
// 				if ($tracking['username'] == 'no') {
					
// 					$data = array(
					
// 					'eq_api_id'    			=>  $auth_obj->get_api_id(),
// 					'auth_type'   			=>  'username',
// 					'auth_value' 			=>  $auth_obj->get_device_username(),
																							
// 					);
					
// 					Zend_Registry::get('database')->insert_single_row('equipment_auths',$data,$class,$method);
// 				}	
// 			}
			
			
			////passwords
// 			if ($auth_obj->get_device_password() != null) {
					
// 				$tracking['password']			= 'no';
					
// 				$i=0;
// 				foreach($auths as $auth) {
			
// 					if ($auth['auth_type'] == 'password') {
							
// 						Zend_Registry::get('database')->set_single_attribute($auth['eq_auth_id'], 'equipment_auths', 'auth_value', $auth_obj->get_device_password(), $class, $method);
// 						$auths[$i]['status'] 			= 'updated';
// 						$tracking['password']			= 'yes';
							
// 					}
						
// 					$i++;
// 				}
					
// 				if ($tracking['password'] == 'no') {
			
// 					$data = array(
			
// 					'eq_api_id'    			=>  $auth_obj->get_api_id(),
// 					'auth_type'   			=>  'password',
// 					'auth_value' 			=>  $auth_obj->get_device_password(),
			
// 					);
			
// 					Zend_Registry::get('database')->insert_single_row('equipment_auths',$data,$class,$method);
// 				}
// 			}
			
			////enable passwords
// 			if ($auth_obj->get_device_enablepassword() != null) {
			
// 				$tracking['enablepassword']			= 'no';
			
// 				$i=0;
// 				foreach($auths as $auth) {
			
// 					if ($auth['auth_type'] == 'enablepassword') {
			
// 						Zend_Registry::get('database')->set_single_attribute($auth['eq_auth_id'], 'equipment_auths', 'auth_value', $auth_obj->get_device_enablepassword(), $class, $method);
// 						$auths[$i]['status'] 			= 'updated';
// 						$tracking['enablepassword']			= 'yes';
			
// 					}
						
// 					$i++;
// 				}
			
// 				if ($tracking['enablepassword'] == 'no') {
			
// 					$data = array(
			
// 					'eq_api_id'    			=>  $auth_obj->get_api_id(),
// 					'auth_type'   			=>  'enablepassword',
// 					'auth_value' 			=>  $auth_obj->get_device_enablepassword(),
			
// 					);
			
// 					Zend_Registry::get('database')->insert_single_row('equipment_auths',$data,$class,$method);
// 				}
// 			}
				
			////now check if there are elements that dident require updating, then we remove those they are no longer required for a connection to the device.
// 			foreach($auths as $auth2) {
			
// 				if (!isset($auth2['status'])) {
						
// 					Zend_Registry::get('database')->delete_single_row($auth2['eq_auth_id'], 'equipment_auths', $class, $method);
						
// 				}
// 			}	
// 		} else {
			
////			this equipment does not have this api setup, so we create a new one
// 			$data = array(
				
// 			'eq_id'    				=>  $this->eq_id,
// 			'api_id'	   			=>  $auth_obj->get_api_id(),
				
// 			);
				
// 			$new_eq_api_id		= Zend_Registry::get('database')->insert_single_row('equipment_apis',$data,$class,$method);
			
			//setup the username if there is one
// 			if ($auth_obj->get_device_username() != null) {
				
// 				$data = array(
				
// 				'eq_api_id'    			=>  $new_eq_api_id,
// 				'auth_type'	   			=>  'username',
// 				'auth_value'	   		=>  $auth_obj->get_device_username(),
				
// 				);
				
// 				Zend_Registry::get('database')->insert_single_row('equipment_auths',$data,$class,$method);
				
// 			}
			
			////setup the password if there is one
// 			if ($auth_obj->get_device_password() != null) {
			
// 				$data = array(
			
// 				'eq_api_id'    			=>  $new_eq_api_id,
// 				'auth_type'	   			=>  'password',
// 				'auth_value'	   		=>  $auth_obj->get_device_password(),
			
// 				);
			
// 				Zend_Registry::get('database')->insert_single_row('equipment_auths',$data,$class,$method);
			
// 			}
			
			////setup the enable password if there is one
// 			if ($auth_obj->get_device_enablepassword() != null) {
					
// 				$data = array(
					
// 				'eq_api_id'    			=>  $new_eq_api_id,
// 				'auth_type'	   			=>  'enablepassword',
// 				'auth_value'	   		=>  $auth_obj->get_device_enablepassword(),
					
// 				);
					
// 				Zend_Registry::get('database')->insert_single_row('equipment_auths',$data,$class,$method);
					
// 			}
// 		}
// 	}

	public function get_new_connection_queue_name($queue_name=null)
	{
		//the queue names differ from software to software on equipment types.

		//for centos installations we designate queue numbers from 3000-6999 to customer queues
		//it is easier to find a queue name using a random value, if the router has more than 50% 
		//of its available queue ids used we should expand this approch to realize this and pick a 
		//random starting point then move from there in numerical order

		if ($this->get_running_software_package()->get_software_package_name() == 'Centos') {
			$i=0;
			if($this->_interfaces != null) {
				while ($i < 3999) {
					
					$fail = false;
			
					if ($queue_name == null) {
						$random_queue_name		= rand(3000, 6999);
					} else {
						$random_queue_name		= $queue_name;
					}
						
					//check if there is a queue by the same name on this equipment
					foreach($this->_interfaces as $interface){
							
						if($interface->get_connection_queues() != null) {
								
							foreach($interface->get_connection_queues() as $connection_queue){
	
								if ($connection_queue->get_connection_queue_name() == $random_queue_name) {
			
									$fail = true;
									break;
								}
							}
						}
					}
					
					if ($fail == false) {
						return $random_queue_name;	
					} elseif($i == 3998){	
						throw new exception('there are no more available queue names', 412);
					} elseif($queue_name != null && $fail == true ) {
						return false;
					}
					
					$i++;
				}
			}
		} else {
			throw new exception("expand method to cover software package name: ".$this->get_running_software_package()->get_software_package_name()." ", 430);
		}
	}
	
	public function get_all_compatible_software_packages()
	{
		$current_software = $this->get_running_software_package();
		
		$sql = "SELECT software_package_id FROM software_packages
				WHERE software_package_manufacturer='".$current_software->get_software_package_manufacturer()."'
				AND software_package_name='".$current_software->get_software_package_name()."'
				AND software_package_architecture='".$current_software->get_software_package_architecture()."'
				ORDER BY software_package_version ASC
				";

		$software_packages = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		//since we got the current software, then we can assume there is at least one package that matches
		foreach ($software_packages as $software_package) {
			$packages[$software_package['software_package_id']] = new Thelist_Model_softwarepackage($software_package['software_package_id']);
		}
		
		return $packages;
	}
	
	public function get_running_software_package()
	{
		
		try {
			
			//make sure we have a management ip/fqdn and that this is not a customer router.
			if ($this->eq_fqdn != null && $this->_eq_type->get_eq_type_id() != 46) {
		
				$sql = "SELECT software_package_id
										FROM equipment_software_upgrades esu1
										INNER JOIN(
												SELECT MAX(eq_software_upgrade_id) AS eq_software_upgrade_id
												FROM equipment_software_upgrades
												WHERE result='success' 
												AND eq_id='".$this->eq_id."'
												GROUP BY eq_id
											  )AS esu2 ON esu1.eq_software_upgrade_id=esu2.eq_software_upgrade_id
										";
				
				$software_package_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

				if (isset($software_package_id['software_package_id'])) {
					
					$software_package_obj = new Thelist_Model_softwarepackage($software_package_id['software_package_id']);
					
					return $software_package_obj;
				
				} else {
					throw new exception('equipment does not have a current software package, this is mandetory', 415);
				}
								
			} else {
				
				//if there is no management fqdn we do not require a software package, this is also used for all cpe routers
				//that are not managed by BAI
				return new Thelist_Model_softwarepackage(12);
				
			}
			
		} catch (Exception $e) {
		
			switch($e->getCode()){
		
				case 415;

				//415 no running software in the database, so we try the device and see if we can find one.
				return $this->get_device()->get_running_os_package();
				break;
				
				default;
				throw $e;
		
			}
		}
			
			
	}
	

	
// 	public function add_vlan_to_interface_on_device($vlan_obj)
// 	{	
				
// 		$vlan_type = $vlan_obj->get_vlan_type();
		
// 		if ($vlan_type == 'transport' ) {
			
// 			$commands_in_xml = $this->get_device_commands('14', $vlan_obj->get_vlan_pri_key_id());
			
// 		} elseif ($vlan_type == 'native') {
			
// 			$commands_in_xml = $this->get_device_commands('16', $vlan_obj->get_vlan_pri_key_id());
			
// 		} elseif($vlan_type == 'trunk') {
			
// 			if ($this->_eq_type->get_eq_manufacturer() == 'Cisco') {
				
// 				$commands_in_xml = $this->get_device_commands('12', $vlan_obj->get_vlan_pri_key_id());
				
// 			} elseif ($this->_eq_type->get_eq_manufacturer() == 'Bel Air Internet') {
				
// 				$commands_in_xml = $this->get_device_commands('8', $vlan_obj->get_vlan_pri_key_id());
				
// 			} elseif ($this->_eq_type->get_eq_manufacturer() == 'Mikrotik') {
				
// 				$commands_in_xml = $this->get_device_commands('8', $vlan_obj->get_vlan_pri_key_id());
				
// 			}
			
			
			
// 		}
		
		
		
// 		try {
	
// 			$this->get_device()->execute_xml($commands_in_xml);
	
// 		} catch (Exception $e) {
	
// 			switch($e->getCode()){
	
// 				case 1;
// 				$this->log_equipment_action('command_regex_failed', $e->getMessage(), $if_id);
// 				break;
// 				case 2;
// 				$this->log_equipment_action('command_got_return', $e->getMessage(), $if_id);
// 				break;
// 				default;
// 				throw $e;
	
// 			}
// 		}
// 	}

// 	public function deny_vlan_to_trunk_on_device_interface($vlan_pri_key_id)
// 	{
	
// 		$commands_in_xml = $this->get_device_commands('13', $vlan_pri_key_id);
	
// 		try {
	
// 			$this->get_device()->execute_xml($commands_in_xml);
	
// 		} catch (Exception $e) {
	
// 			switch($e->getCode()){
	
// 				case 1;
// 				$this->log_equipment_action('command_regex_failed', $e->getMessage(), $if_id);
// 				break;
// 				case 2;
// 				$this->log_equipment_action('command_got_return', $e->getMessage(), $if_id);
// 				break;
// 				default;
// 				throw $e;
	
// 			}
// 		}
// 	}
	
// 	public function allow_vlan_to_trunk_on_device_interface($vlan_pri_key_id)
// 	{
	
// 		$commands_in_xml = $this->get_device_commands('12', $vlan_pri_key_id);

// 		try {
	
// 			$this->get_device()->execute_xml($commands_in_xml);
	
// 		} catch (Exception $e) {
	
// 			switch($e->getCode()){
	
// 				case 1;
// 				$this->log_equipment_action('command_regex_failed', $e->getMessage(), $if_id);
// 				break;
// 				case 2;
// 				$this->log_equipment_action('command_got_return', $e->getMessage(), $if_id);
// 				break;
// 				default;
// 				throw $e;
	
// 			}
// 		}
// 	}
	
// 	public function allow_vlan_to_transport_on_device($vlan_pri_key_id)
// 	{
	
// 		$commands_in_xml = $this->get_device_commands('14', $vlan_pri_key_id);

// 		try {

// 			$this->get_device()->execute_xml($commands_in_xml);
	
// 		} catch (Exception $e) {
	
// 			switch($e->getCode()){
	
// 				case 1;
// 				$this->log_equipment_action('command_regex_failed', $e->getMessage());
// 				break;
// 				case 2;
// 				$this->log_equipment_action('command_got_return', $e->getMessage());
// 				break;
// 				default;
// 				throw $e;
	
// 			}
// 		}
// 	}
	
// 	public function save_config_on_device()
// 	{
	
// 		$commands_in_xml = $this->get_device_commands('36', $this->eq_id);
	
// 		try {
	
// 			$this->get_device()->execute_xml($commands_in_xml);
	
// 		} catch (Exception $e) {
	
// 			switch($e->getCode()){
	
// 				case 1;
// 				$this->log_equipment_action('command_regex_failed', $e->getMessage());
// 				break;
// 				case 2;
// 				$this->log_equipment_action('command_got_return', $e->getMessage());
// 				break;
// 				default;
// 				throw $e;
	
// 			}
// 		}
// 	}
	
// 	public function deny_vlan_to_transport_on_device($vlan_pri_key_id)
// 	{
	
// 		$commands_in_xml = $this->get_device_commands('15', $vlan_pri_key_id);
	
// 		try {
	
// 			$this->get_device()->execute_xml($commands_in_xml);
	
// 		} catch (Exception $e) {
	
// 			switch($e->getCode()){
	
// 				case 1;
// 				$this->log_equipment_action('command_regex_failed', $e->getMessage(), $if_id);
// 				break;
// 				case 2;
// 				$this->log_equipment_action('command_got_return', $e->getMessage(), $if_id);
// 				break;
// 				default;
// 				throw $e;
	
// 			}
// 		}
// 	}

	
// 	public function update_vlan_config_on_device_interface($if_id)
// 	{

// 		$interface = $this->_interfaces["$if_id"];
// 		$vlans = $interface->get_vlans();
		
// 		$configurations = $interface->get_if_configurations();
		
////		test that this port does not have an allow all policy
// 		$interface_use = 'edge';
// 		$pri_key_for_overall_vlan_config = null;
// 		if ($configurations != null) {
// 					foreach ($configurations as $configuration) {
						
// 						if ($configuration->get_conf_id() == '14') {
							
// 							$pri_key_for_overall_vlan_config = $configuration->get_mapped_conf_map_id();
							
// 							if ($configuration->get_mapped_conf_value() == 'all') {
// 								$interface_use = 'uplink';
// 							}
// 						}
// 					}
// 				}
		
// 		if ($interface_use == 'edge' && $pri_key_for_overall_vlan_config != null) {
			
////				get the original value
// 				$original_vlan_value = $interface->get_if_configuration($pri_key_for_overall_vlan_config)->get_mapped_conf_value();
				
////				this is not a good method to determine the correct value, you should use the available values instead.
////			it works because this is only cisco devices.
// 				$interface->get_if_configuration($pri_key_for_overall_vlan_config)->set_if_mapped_config_value('none');
				
////				lets reset the port completely
			
// 				$commands_in_xml1 = $this->get_device_commands('18', $pri_key_for_overall_vlan_config);
				
////				reset the original value
// 				$interface->get_if_configuration($pri_key_for_overall_vlan_config)->set_if_mapped_config_value($original_vlan_value);
				
////			interface commands
// 			try {

////				execute the commands
// 				$this->get_device()->execute_xml($commands_in_xml1);

// 			} catch (Exception $e) {
					
// 				switch($e->getCode()){
						
// 					case 1;
// 					$this->log_equipment_action('command_regex_failed', $e->getMessage(), $if_id);
// 					break;
// 					case 2;
// 					$this->log_equipment_action('command_got_return', $e->getMessage(), $if_id);
// 					break;
// 					default;
// 					throw $e;
						
// 				}
// 			}
// 		}
		
////		execute all allowed vlans
// 		if ($vlans != null && $interface_use == 'edge') {
			
// 			foreach ($vlans as $vlan) {
				
// 				$commands_in_xml2 = $this->get_device_commands('12', $vlan->get_vlan_pri_key_id());
// 				$commands_in_xml3 = $this->get_device_commands('14', $vlan->get_vlan_pri_key_id());
				
// 				try {

// 					$this->get_device()->execute_xml($commands_in_xml2);
// 					$this->get_device()->execute_xml($commands_in_xml3);
						
// 				} catch (Exception $e) {
				
// 					switch($e->getCode()){
				
// 						case 1;
// 						$this->log_equipment_action('command_regex_failed', $e->getMessage(), $if_id);
// 						break;
// 						case 2;
// 						$this->log_equipment_action('command_got_return', $e->getMessage(), $if_id);
// 						break;
// 						default;
// 						throw $e;
				
// 					}
// 				}
// 			}

// 		}

// 	}
	
// 	public function interface_config_device_actions($if_id, $conf_if_map_id, $action)
// 	{

// 		if ($action == 'update') {

// 			$interface_set_device_function = $this->_interfaces["$if_id"]->get_if_configuration($conf_if_map_id)->get_set_device_function();
// 			$commands_in_xml = $this->get_device_commands("$interface_set_device_function", $conf_if_map_id);
			
// 		}

////		run on device
// 		try {
			
// 			$this->get_device()->execute_xml($commands_in_xml);
			
// 		} catch (Exception $e) {

// 			switch($e->getCode()){
				
// 				case 1;
// 				$this->log_equipment_action('command_regex_failed', $e->getMessage(), $if_id);
// 				break;
// 				case 2;
// 				$this->log_equipment_action('command_got_return', $e->getMessage(), $if_id);
// 				break;
// 				default;
// 				throw $e;

// 			}
// 		}
		
////		after actions
// 		if ($action == 'add') {
				
////			nothing
				
// 		} elseif ($action == 'remove') {
				
// 			$interface->vlan_actions($vlan_id, $vlan_type, 'dbdelete');
				
// 		}
		
		
// 	}
		

	
	public function update_default_monitoring()
	{
		if ($this->_monitoring_guids == null) {
				
			$mon_guid = $this->create_new_monitoring_guid();
	
		} else {
				
			$mon_guid = array_shift(array_values($this->_monitoring_guids));
	
		}
	
		$mon_guid->map_default_datasources();
	
	}
	
	public function create_new_monitoring_guid()
	{
		$data = array(
					'table_name'							=> 'equipments',
					'primary_key'							=> $this->eq_id,
		);
	
		$trace 		= debug_backtrace();
		$method 	= $trace[0]["function"];
		$class		= get_class($this);
	
		$new_monitoring_guid_id = Zend_Registry::get('database')->insert_single_row('monitoring_guids',$data,$class,$method);
		$monitoring_guid_obj = $this->_monitoring_guids[$new_monitoring_guid_id] = new Thelist_Model_monitoringguid($new_monitoring_guid_id);
		return $monitoring_guid_obj;
	}
	
	public function get_monitoring_guid($monitoring_guid_id)
	{
		return $this->_monitoring_guids[$monitoring_guid_id];
	
	}
	public function get_monitoring_guids()
	{
		return $this->_monitoring_guids;
	}
	
	public function upgrade_firmware($software_package_obj)
	{
		$manufacturer = $this->_eq_type->get_eq_manufacturer();
		$eq_type_id = $this->_eq_type->get_eq_type_id();

		$current_software	= $this->get_running_software_package();
		
		if ($current_software->get_software_package_architecture() == $software_package_obj->get_software_package_architecture() 
		&&	$current_software->get_software_package_name() == $software_package_obj->get_software_package_name()) {
			
			if ($manufacturer == 'Mikrotik') {
			
				$this->get_device()->execute_command("/tool fetch url=".$software_package_obj->get_software_package_server()."".$software_package_obj->get_software_package_path_filename()." mode=http");

				}

		} else {
			
			throw new exception('new and old software packages are not using the same architecture or does not have the same name');
			
		}
	}
}
?>