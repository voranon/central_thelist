<?php 

//exception codes 1000-1099

class thelist_model_equipmentinterface
{
	private $_if_id;
	private $_eq_id;
	private $_if_index;
	private $_if_name;
	private $_if_type_id;
	private $_if_type=null;
	private $_if_mac_address;
	private $_service_point_id=null;
	
	private $_if_features=null;
	//private $_if_configurations=null;
	private $_tasks=null;
	private $_ip_addresses=null;
	private $_monitoring_guids=null;
	private $_slave_relationships=null;
	private $_master_relationships=null;
	private $_connection_queues=null;
	private $_interface_configurations=null;
	private $_reachable_host_ips=null;
	
	//when mapped to ip traffic rule
	private $_ip_traffic_rule_if_map_id=null;
	private $_ip_traffic_rule_if_role_id=null;
	private $_ip_traffic_rule_if_role_name=null;
	
	
	
	
	public function __construct($if_id)
	{
		$this->_if_id = $if_id;

		$if = Zend_Registry::get('database')->get_interfaces()->fetchRow('if_id='.$this->_if_id);

		$this->_eq_id				= $if['eq_id'];
		$this->_if_index			= $if['if_index'];
		$this->_if_name				= $if['if_name'];
		$this->_if_type_id			= $if['if_type_id'];
		$this->_if_mac_address		= $if['if_mac_address'];
		$this->_service_point_id	= $if['service_point_id'];
	}
	
	public function get_ip_addresses()
	{
		
		if ($this->_ip_addresses == null) {

			$sql=	"SELECT * FROM ip_address_mapping
					WHERE if_id='".$this->_if_id."'
					";
			
			$ip_addresses  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			//get all the ips that belong to this interface
			if (isset($ip_addresses['0'])) {
			
				foreach($ip_addresses as $ip_addresse){
						
					$this->_ip_addresses[$ip_addresse['ip_address_id']] = new Thelist_Model_ipaddress($ip_addresse['ip_address_id']);
					$this->_ip_addresses[$ip_addresse['ip_address_id']]->set_ip_address_map_id($this->_if_id);
						
				}
			}
		}
		
		return $this->_ip_addresses;
		
	}
	
	public function get_reachable_host_ips($refresh=false)
	{
		if ($this->_ip_addresses == null) {
			$this->get_ip_addresses();
		}
		
		if ($this->_ip_addresses != null && ($this->_reachable_host_ips == null || $refresh == true)) {
			
			if ($this->_ip_addresses != null) {
					
				foreach ($this->_ip_addresses as $interface_ip) {
			
					$interface_subnet	= new Thelist_Model_ipsubnet($interface_ip->get_ip_subnet_id());
					$mapped_ips	= $interface_subnet->get_mapped_ips();
			
					if ($mapped_ips != null) {
							
						foreach ($mapped_ips as $mapped_ip) {
			
							if ($mapped_ip->get_mapped_if_id() != $this->_if_id) {
									
								$this->_reachable_host_ips[]	= $mapped_ip;
							}
						}
							
					} else {
						throw new exception("problem, we have an interface if_id: ".$this->_if_id." with an ip mapped from a subnet, but the subnet reports that no ips are mapped? ", 1077);
					}
						
				}
			}
		}
		
		return $this->_reachable_host_ips;
	}
	
	public function get_ip_address($ip_address_id)
	{
	
		if ($this->_ip_addresses == null) {
			$this->get_ip_addresses();
		}
			
		return $this->_ip_addresses[$ip_address_id];
	}

	public function get_slave_relationships()
	{
	
		//interfaces where this interface is a slave
		if ($this->_slave_relationships == null) {
	
			$sql =	"SELECT * FROM interface_relationships
					WHERE if_id_slave='".$this->_if_id."'
					";
				
			$relationships  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);

			if (isset($relationships['0'])) {

				foreach ($relationships as $relationship) {
						
					$master_interface	= new Thelist_Model_equipmentinterface($relationship['if_id_master']);
					$this->_slave_relationships[]	= $master_interface;
				}
			}
		}
	
		return $this->_slave_relationships;
	}
	
	public function get_connected_subnets()
	{
		if ($this->get_ip_addresses() != null) {
				
			foreach ($this->get_ip_addresses() as $ip_address) {

				if ($ip_address->get_ip_address_map_type() == 88 || $ip_address->get_ip_address_map_type() == 91) {
					
					if (!isset($subnets[$ip_address->get_ip_subnet_id()])) {
						$subnets[$ip_address->get_ip_subnet_id()] = new Thelist_Model_ipsubnet($ip_address->get_ip_subnet_id());
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
	
	public function get_connected_ips()
	{
		if ($this->get_ip_addresses() != null) {
	
			foreach ($this->get_ip_addresses() as $ip_address) {
	
				if ($ip_address->get_ip_address_map_type() == 88 || $ip_address->get_ip_address_map_type() == 91) {
					$connected_ips[] = $ip_address;
				}
			}
		}
	
		if (isset($connected_ips)) {
			return $connected_ips;
		} else {
			return false;
		}
	}
	
	public function get_master_relationships()
	{
		//interfaces where this interface is a master
		if ($this->_master_relationships == null) {
	
			$sql =	"SELECT * FROM interface_relationships
						WHERE if_id_master='".$this->_if_id."'
						";
	
			$relationships  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
	
			if (isset($relationships['0'])) {
				
				foreach ($relationships as $relationship) {
					
					$slave_interface	= new Thelist_Model_equipmentinterface($relationship['if_id_slave']);
					$this->_master_relationships[]	= $slave_interface;
				}
			}
		}
	
		return $this->_master_relationships;
	}
	
	public function add_master_relationship($slave_interface)
	{
		if ($this->_master_relationships == null) {
			$this->get_master_relationships();
		}
		
		$this->_master_relationships[] = $slave_interface;
		
	}
	
	public function add_slave_relationship($master_interface)
	{
		if ($this->_slave_relationships == null) {
			$this->get_slave_relationships();
		}
	
		$this->_slave_relationships[] = $master_interface;
	
	}
	
	public function get_service_point_id()
	{
		return $this->_service_point_id;
	}
	
	public function map_new_task($task_obj)
	{
		$current_tasks	= $this->get_tasks();
		
		if ($current_tasks != null) {
			
			foreach ($current_tasks as $current_task) {
				if ($current_task->get_task_id() == $task_obj->get_task_id()) {
					return $current_task->get_interface_task_map_id();
				}
			}
		}
		
		//if the task is not already mapped
		$data = array(
					'task_id'		=> $task_obj->get_task_id(),
					'if_id'			=> $this->_if_id,
		);
			
		$trace 		= debug_backtrace();
		$method 	= $trace[0]["function"];
		$class		= get_class($this);

		//map the task to the interface
		return Zend_Registry::get('database')->insert_single_row('interface_task_mapping',$data,$class,$method);
	}
	
	public function get_tasks($task_status=null)
	{
		
		if ($this->_tasks == null) {
			
			$sql =	"SELECT * FROM interface_task_mapping itm
					INNER JOIN tasks t ON t.task_id=itm.task_id
					WHERE itm.if_id='".$this->_if_id."'
					";
			
			$tasks  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			if (isset($tasks['0'])) {
				foreach($tasks as $task) {
					$this->_tasks[$task['task_id']]		= new Thelist_Model_tasks($task['task_id']);
					$this->_tasks[$task['task_id']]->set_interface_task_map_id($task['interface_task_map_id']);
				}
			}
		}

		if ($this->_tasks != null) {
			
			if ($task_status == 'open') {

				$open_tasks = null;
				
				foreach($this->_tasks as $task) {
						
					if ($task->get_status() == '12') {
						$open_tasks[$task->get_task_id()]	= $task;
					}
				}
				
				return $open_tasks;
				
			} elseif ($task_status == 'closed') {
					
				$closed_tasks = null;
					
				foreach($tasks as $task) {
						
					if ($task->get_status() == '13') {
						$closed_tasks[$task->get_task_id()]	= $task;
					}
				}
				return $closed_tasks;
			}
		}
		
		return $this->_tasks;
	}

	public function get_if_master_id()
	{
		return $this->_if_master_id;
	}
	public function get_index()
	{
		return $this->_if_index;
	}
	public function get_if_index()
	{
		return $this->_if_index;
	}
	public function get_eq_id()
	{
		return $this->_eq_id;
	}
	public function get_if_features()
	{
		if ($this->_if_features == null) {
			//interface features
			$sql3=	"SELECT *  FROM interface_feature_mapping
					WHERE if_id='".$this->_if_id."'
					";
			
			$features  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql3);
			
			if (isset($features['0'])) {
				foreach($features as $feature){
					$this->_if_features[$feature['if_feature_map_id']] = new Thelist_Model_equipmentinterfacefeature($feature['if_feature_id']);
					$this->_if_features[$feature['if_feature_map_id']]->set_if_feature_map($feature['if_feature_map_id']);
				}
			}
		}
		
		return $this->_if_features;
	}
	public function get_if_feature($if_feature_map_id)
	{
		if ($this->_if_features == null) {
			$this->get_if_features();
		}
		
		if (isset($this->_if_features[$if_feature_map_id])) {
			return $this->_if_features[$if_feature_map_id];
		} else {
			return false;
		}
	}

	public function get_if_type_id()
	{
		return $this->_if_type_id;
	}
	
	public function get_if_type()
	{
		$if_type	= new Thelist_Model_interfacetype($this->_if_type_id);
		$this->_if_type	= $if_type;
		
		return $this->_if_type;
	}
	
	public function get_if_name()
	{
		return $this->_if_name;
	}
	
	public function get_if_id()
	{
		return $this->_if_id;
	}
	public function get_id()
	{
		return $this->_if_id;
	}
	public function get_if_mac_address()
	{
		return $this->_if_mac_address;
	}
	public function get_if_type_name()
	{
		$sql = "SELECT if_type_name FROM interface_types
				WHERE if_type_id='".$this->_if_type_id."'
				";
		
		return Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
	}
	
	public function set_if_index($new_if_index)
	{
		
		if ($this->_if_index != $new_if_index) {
				
			if (is_numeric($new_if_index)) {
					
				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);
					
				Zend_Registry::get('database')->set_single_attribute($this->_if_id,'interfaces','if_index',$new_if_index,$class,$method);
					
				$this->_if_index = $new_if_index;
					
			} else {
				
				throw new exception('if index must be numeric', 1006);
				
			}
		}
	}
	
	public function set_if_name($new_if_name)
	{
		if ($this->_if_name != $new_if_name) {
			
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
			
			Zend_Registry::get('database')->set_single_attribute($this->_if_id,'interfaces','if_name',$new_if_name,$class,$method);
			
			$this->_if_name = $new_if_name;
			
		}
	}
	
	public function set_if_type_id($new_if_type_id)
	{
		if ($this->_if_type_id != $new_if_type_id) {
				
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
				
			Zend_Registry::get('database')->set_single_attribute($this->_if_id,'interfaces','if_type_id',$new_if_type_id,$class,$method);
				
			$this->_if_type_id = $new_if_type_id;
				
		}
	}
	
	public function set_if_mac_address($new_mac_address)
	{
		if (!is_object($new_mac_address)) {
			$validate_mac	= new Thelist_Deviceinformation_macaddressinformation($new_mac_address);
			$validation_result	= $validate_mac->is_valid();
		} else {
			//if its already an object
			$validate_mac	= $new_mac_address;
			$validation_result	= $validate_mac->is_valid();
		}
		
		if ($validation_result) {

			if ($validate_mac->get_macaddress() != $this->_if_mac_address) {

				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);
	
				Zend_Registry::get('database')->set_single_attribute($this->_if_id,'interfaces','if_mac_address',$validate_mac->get_macaddress(),$class,$method);
					
				$this->_if_mac_address = $validate_mac->get_macaddress();
			}

		} else {
			throw new exception('mac address is not valid', 1003);	
		}
		
	}
	
	public function set_service_point_id($new_service_point_id)
	{
		if ($this->_service_point_id != $new_service_point_id) {
	
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
				
			Zend_Registry::get('database')->set_single_attribute($this->_if_id,'interfaces','service_point_id',$new_service_point_id,$class,$method);
	
			$this->_service_point_id = $new_service_point_id;
		}
	}
	
// 	public function set_vlan_id($new_vlan_id)
// 	{	
// 		if ($this->_vlan_id != $new_vlan_id) {
			
// 			if (is_numeric($new_vlan_id) && $new_vlan_id < 4097) {
				
// 				$trace 		= debug_backtrace();
// 				$method 	= $trace[0]["function"];
// 				$class		= get_class($this);
				
// 				$foo = Zend_Registry::get('database');
// 				$foo->set_single_attribute($this->_if_id,'interfaces','vlan_id',$new_vlan_id,$class,$method);
				
// 				Zend_Registry::get('database')->set_single_attribute($this->_if_id,'interfaces','vlan_id',$new_vlan_id,$class,$method);
				
// 				$this->_vlan_id = $new_vlan_id;
				
// 			} else {
				
// 				throw new exception('not a valid vlan_id', 1004);
				
// 			}
// 		}
// 	}
	
	
// 	public function set_vlan_type($new_vlan_type)
// 	{
		
// 		if ($this->_vlan_type != $new_vlan_type) {
			
// 			if ($new_vlan_type == 'access' || $new_vlan_type == 'native' || $new_vlan_type == 'trunk') {
				
// 				$trace 		= debug_backtrace();
// 				$method 	= $trace[0]["function"];
// 				$class		= get_class($this);
				
// 				Zend_Registry::get('database')->set_single_attribute($this->_if_id,'interfaces','if_name',$new_vlan_type,$class,$method);
			
// 				$this->_vlan_type = $new_vlan_type;
			
// 			} else {
				
// 				throw new exception('not a valid vlan type', 1005);
				
// 			}
// 		}
// 	}
	
// 	public function get_vlan_id()
// 	{
// 		return $this->_vlan_id;
// 	}
// 	public function get_vlan_type()
// 	{
// 		return $this->_vlan_type;
// 	}
	
	public function remove_ip_address_map($ip_address_obj)
	{
		
		if ($this->_ip_addresses == null) {
			$this->get_ip_addresses();
		}
	
		//verify that the ip is really mapped to this interface.
		if (!isset($this->_ip_addresses[$ip_address_obj->get_ip_address_id()])) {
			throw new exception('ip address is not mapped to this interface', 1010);
		}

		$trace 		= debug_backtrace();
		$method 	= $trace[0]["function"];
		$class		= get_class($this);

		//delete ip address
		Zend_Registry::get('database')->delete_single_row($this->_ip_addresses[$ip_address_obj->get_ip_address_id()]->get_ip_address_map_id(), 'ip_address_mapping', $class, $method);
		
		//remove the ip map from this interface
		unset($this->_ip_addresses[$ip_address_obj->get_ip_address_id()]);

	}
	
	public function remove_interface_configuration($if_conf_map_id, $override_mandetory=false)
	{
		if ($this->_interface_configurations == null) {
			$this->get_interface_configurations();
		}
		
		if (isset($this->_interface_configurations[$if_conf_map_id])) {
			
			$sql = "SELECT itc.* FROM interface_configuration_mapping icm
					INNER JOIN  interface_type_configurations itc ON itc.if_conf_id=icm.if_conf_id
					WHERE icm.if_conf_map_id='".$if_conf_map_id."'
					AND itc.if_type_id='".$this->get_if_type()->get_if_type_id()."'
					";
			
			$if_type_conf_detail = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
			//if its not mandetory or if we are i.e. removing an entire interface, then we override and remove the config
			if ($if_type_conf_detail['if_conf_mandetory'] == 0 || $override_mandetory !== false) {
				
				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);
					
				//delete conf map
				Zend_Registry::get('database')->delete_single_row($if_conf_map_id, 'interface_configuration_mapping', $class, $method);
					
				//remove the conf map from this interface
				unset($this->_interface_configurations[$if_conf_map_id]);
				
			} else {
				throw new exception("you are trying to remove if_conf_map_id: ".$if_conf_map_id." from if_id: ".$this->_if_id.", but that config is mandetory for this type of interface and cannot be removed ", 1012);
			}
			
		} else {
			throw new exception("you are trying to remove if_conf_map_id: ".$if_conf_map_id." from if_id: ".$this->_if_id.", but it is not set on the interface ", 1076);
		}
	}
	
	public function map_new_ip_address($ip_address_obj, $mapping_type_id)
	{		
		//if variable 
		if ($this->_ip_addresses == null) {
			$this->get_ip_addresses();
		}
		
		//is the mapping type valid?
		$sql2 = 	"SELECT item_type FROM items
					WHERE item_id='".$mapping_type_id."'
					";
			
		$item_type  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
		
		if ($item_type != 'ip_address_map_type') {
			throw new exception('the mapping type provided is not an item relating to ip mapping', 1009);
		}
		
		//verify that the new ip is not already mapped on this interface.
	
		if ($this->_ip_addresses != null) {
	
			foreach ($this->_ip_addresses as $ip_address) {
	
				if ($ip_address->get_ip_address_id() == $ip_address_obj->get_ip_address_id()) {
	
					
					//ip is already mapped to this interface, but the provided object may not be a referance of the same object we have 
					//so we use the local object and update both incoming and local copy mappings
					if ($ip_address_obj->get_mapped_if_id() != $this->_if_id) {
						//not mapped to this interface currently since that would also happen below, we map it
						$ip_address_obj->set_ip_address_map_id($this->_if_id);
					}

					//update the type map on both incase they are not the same ref.
					$ip_address->update_mapped_mapping_type($mapping_type_id);
					$ip_address_obj->update_mapped_mapping_type($mapping_type_id);
					//we are done
					return;
				}
			}
		}

		//only allow redundant maps on VRRP interfaces
		if ($this->get_if_type()->get_if_type_id() != 91 && $ip_address_obj->ip_in_use() === true) {
			throw new exception("you are asking to map ip address_id ".$ip_address_obj->get_ip_address_id()." to if_id: ".$this->_if_id.", we cannot do this because the interface is not a VRRP interface and the ip is already mapped", 1078);
		}

		$data = array(
		
			'ip_address_map_type'		=> $mapping_type_id,
			'ip_address_id'				=> $ip_address_obj->get_ip_address_id(),
			'if_id'						=> $this->_if_id,
		
		);
		
		$trace 		= debug_backtrace();
		$method 	= $trace[0]["function"];
		$class		= get_class($this);
		
		
		//map the ip to the interface
		$new_mapping_id = Zend_Registry::get('database')->insert_single_row('ip_address_mapping',$data,$class,$method);

		$this->_ip_addresses[$ip_address_obj->get_ip_address_id()] = $ip_address_obj;
		$this->_ip_addresses[$ip_address_obj->get_ip_address_id()]->set_ip_address_map_id($this->_if_id);
	
		//no need to return the ip_address object as this is php and the object variable is a referance.
		//any updates are already reflected in the class / method that requested the map
	}
	
	//standard monitoring methods
	public function create_new_monitoring_guid()
	{
		$data = array(
				'table_name'							=> 'interfaces',
				'primary_key'							=> $this->_if_id,
		);
	
		$trace 		= debug_backtrace();
		$method 	= $trace[0]["function"];
		$class		= get_class($this);
	
		$new_monitoring_guid_id = Zend_Registry::get('database')->insert_single_row('monitoring_guids',$data,$class,$method);
		$this->_monitoring_guids[$new_monitoring_guid_id] = new Thelist_Model_monitoringguid($new_monitoring_guid_id);
		return $this->_monitoring_guids[$new_monitoring_guid_id];
	}
	
// 	public function update_default_interface_configurations($equipment_type_software_map_id)
// 	{
		
// 		$sql = 	"SELECT * FROM configuration_interface_type_mapping citm
// 				INNER JOIN configuration_values cv ON cv.conf_value_id=citm.conf_value_id
// 				WHERE citm.if_type_id='".$this->_if_type_id."'
// 				AND citm.eq_type_software_map_id='".$equipment_type_software_map_id."'
// 				";
			
// 		$if_configurations = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
// 	if (isset($if_configurations['0'])) {
	
// 			foreach ($if_configurations as $if_configuration) {
	
// 				try {
	
// 					$this->add_new_interface_configuration_map($if_configuration['conf_id'], $if_configuration['conf_value']);
						
// 				} catch (Exception $e) {
						
// 					switch($e->getCode()){
							
// 						case 90;
					////	do nothing
// 						break;
// 						default;
// 						throw $e;
							
// 					}
// 				}
					
// 			}
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
	
	public function add_new_interface_configuration($if_conf_id, $if_conf_value_1)
	{
		//make sure to fill the variable if it has not yet been
		if ($this->_interface_configurations == null) {
			$this->get_interface_configurations();
		}
		
		$interface_config		= new Thelist_Model_interfaceconfiguration($if_conf_id);
		$max_maps				= $interface_config->get_max_conf_maps($this->_if_type_id);
				
		if ($max_maps != false) {
			
			//does this equipment already have an interface config of this type?
			if ($this->_interface_configurations != null && $max_maps == 1) {
				foreach ($this->_interface_configurations as $if_configuration) {
			
					if ($if_configuration->get_if_conf_id() == $if_conf_id) {
							
						//set the new value and return
						$if_configuration->set_mapped_configuration_value_1($if_conf_value_1);
						return $if_configuration;
					}
				}
			}

			//even if we allow multiple of the same config, we dont allow them to have the exact same values, they must differ in some way
			if ($this->_interface_configurations != null && $max_maps > 1) {
				foreach ($this->_interface_configurations as $if_configuration) {
			
					if ($if_configuration->get_if_conf_id() == $if_conf_id && $if_configuration->get_mapped_configuration_value_1() == $if_conf_value_1) {
						return $if_configuration;
					}
				}
			}

			$number_of_existing_configs=0;
			if ($this->_interface_configurations != null) {
				foreach ($this->_interface_configurations as $if_configuration) {
					if ($if_configuration->get_if_conf_id() == $if_conf_id) {
						$number_of_existing_configs++;
					}
				}
			}
			
			//if no match was found we add the config if the config is allowed more than there is currently mapped
			if ($max_maps >  $number_of_existing_configs) {
				
				//if all the above is passed then create the new interface config
				$data = array(
				
								'if_id'							=> $this->_if_id,
								'if_conf_id'					=> $if_conf_id,
								'if_conf_value_1'				=> $if_conf_value_1,
				);
					
				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);
					
				$if_config_map_id = Zend_Registry::get('database')->insert_single_row('interface_configuration_mapping',$data,$class,$method);
					
				$this->_interface_configurations[$if_config_map_id] = $interface_config;
				$this->_interface_configurations[$if_config_map_id]->fill_mapped_values($if_config_map_id);

				return $this->_interface_configurations[$if_config_map_id];
				
				
			} else {
				throw new exception("you are trying to add if_conf_id:".$if_conf_id." to if_id: ".$this->_if_id." but you will need to remove some configs of this type first since there are:".$number_of_existing_configs." mapped and max is:".$max_maps." ", 1081);
			}
			
		} else {
			throw new exception("you are trying to add if_conf_id:".$if_conf_id." to if_id: ".$this->_if_id." but that config is not allowed on this interface", 1080);
		}
	}
		
	public function update_default_interface_features()
	{
	
		$sql = 	"SELECT * FROM interface_type_feature_mapping
				WHERE if_type_id='".$this->_if_type_id."'
				";
		
		$if_type_features = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
	
		if (isset($if_type_features['0'])) {
	
			foreach ($if_type_features as $if_type_feature) {
	
				try {
	
					$this->add_new_interface_feature_map($if_type_feature['if_feature_id'], $if_type_feature['if_type_feature_value']);
						
				} catch (Exception $e) {
						
					switch($e->getCode()){
							
						case 89;
						//do nothing
						break;
						default;
						throw $e;
							
					}
				}
			}
		}
	}
	
	public function update_default_interface_configurations()
	{
	
		$sql = 	"SELECT * FROM interface_type_configurations
				WHERE if_type_id='".$this->_if_type_id."'
				";
	
		$if_type_configurations = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
	
		if (isset($if_type_configurations['0'])) {
	
			foreach ($if_type_configurations as $if_type_configuration) {
	
				//all that are mapped default or mandetory
				if ($if_type_configuration['if_conf_default_map'] == 1 || $if_type_configuration['if_conf_mandetory'] == 1) {
					
					try {
					
						$this->add_new_interface_configuration($if_type_configuration['if_conf_id'], $if_type_configuration['if_conf_default_value_1']);
					
					} catch (Exception $e) {
					
						switch($e->getCode()){
								
							default;
							throw $e;
						}
					}
				}
			}
		}
	}
	
	public function remove_interface_feature_map($if_feature_map_id)
	{
		if ($this->_if_features == null) {
			$this->get_if_features();
		}
		//does this equipment already have an interface by this name?
		if (isset($this->_if_features[$if_feature_map_id])) {
			
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
			
			//delete the map
			Zend_Registry::get('database')->delete_single_row($if_feature_map_id, 'interface_feature_mapping', $class, $method);
			
			//unset it from this object
			unset($this->_if_features[$if_feature_map_id]);
			
		} else {
			throw new exception("you are trying to remove if_feature_map_id:".$if_feature_map_id." from if_id: ".$this->_if_id." but it is not mapped to this interface", 1075);
		}
	}
	
	public function add_new_interface_feature_map($if_feature_id, $if_feature_value)
	{
		if ($this->_if_features == null) {
			$this->get_if_features();
		}
		
		if ($this->_if_features != null) {
			foreach ($this->_if_features as $if_feature) {
	
				if ($if_feature->get_if_feature_id() == $if_feature_id) {
					//same feature is not ok, so we just update the current one and return it
					$if_feature->set_mapped_if_feature_value($if_feature_value);
					return $if_feature;
				}
			}
		}

		//create the new interface
		$data = array(
						'if_id'							=> $this->_if_id,
						'if_feature_id'					=> $if_feature_id,
						'if_feature_value'				=> $if_feature_value,
		);
			
		$trace 		= debug_backtrace();
		$method 	= $trace[0]["function"];
		$class		= get_class($this);
			
		$if_feature_map_id = Zend_Registry::get('database')->insert_single_row('interface_feature_mapping',$data,$class,$method);
			
		$this->_if_features[$if_feature_map_id] = new Thelist_Model_equipmentinterfacefeature($if_feature_id);
	
	}

	public function get_monitoring_guid($monitoring_guid_id)
	{
		if ($this->_monitoring_guids == null) {
			
			//standard monitoring methods
			$sql2=	"SELECT * FROM monitoring_guids
					WHERE table_name='interfaces'
					AND primary_key='".$this->_if_id."'
					";
			
			$monitoring_guids  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
			if (isset($monitoring_guids['0'])) {
				foreach($monitoring_guids as $monitoring_guid){
					$this->_monitoring_guids[$monitoring_guid['monitoring_guid_id']] = new Thelist_Model_monitoringguid($monitoring_guid['monitoring_guid_id']);
				}
			}
		}
		
		return $this->_monitoring_guids[$monitoring_guid_id];
	
	}
	public function get_monitoring_guids()
	{
		
		if ($this->_monitoring_guids == null) {
				
			//standard monitoring methods
			$sql2=	"SELECT * FROM monitoring_guids
					WHERE table_name='interfaces'
					AND primary_key='".$this->_if_id."'
					";
				
			$monitoring_guids  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
			if (isset($monitoring_guids['0'])) {
				foreach($monitoring_guids as $monitoring_guid){
					$this->_monitoring_guids[$monitoring_guid['monitoring_guid_id']] = new Thelist_Model_monitoringguid($monitoring_guid['monitoring_guid_id']);
				}
			}
		}
		
		return $this->_monitoring_guids;
	
	}
	
	public function create_connection_queue($connection_queue_max_rate, $connection_queue_name, $connection_queue_sla_rate=null)
	{
		
		if ($connection_queue_sla_rate != null) {
			
			if ($connection_queue_sla_rate > $connection_queue_max_rate) {

				//if the sla is bigger than the max we have a problem
				throw new exception('sla rate larger than the max rate', 1011);
			}
			
		} else {
			$connection_queue_sla_rate = $connection_queue_max_rate;
		}
		
		$data = array(
					 		'if_id'									=>  $this->_if_id,
					 		'connection_queue_max_rate'				=>  $connection_queue_max_rate,
					 		'connection_queue_sla_rate'				=>	$connection_queue_sla_rate,
							'connection_queue_name'					=>  $connection_queue_name,
		);
		
		$trace 		= debug_backtrace();
		$method 	= $trace[0]["function"];
		$class		= get_class($this);
		
		$new_connection_queues_id = Zend_Registry::get('database')->insert_single_row('connection_queues', $data, $class, $method);
		
		$connection_queues_obj	= new Thelist_Model_connectionqueue($new_connection_queues_id);
		
		$this->_connection_queues[$connection_queues_obj->get_connection_queue_id()]	= $connection_queues_obj;
		
		return $this->_connection_queues[$connection_queues_obj->get_connection_queue_id()];
		
	}
	
	public function get_connection_queues()
	{
		if ($this->_connection_queues == null) {
			
			$sql = "SELECT connection_queue_id FROM connection_queues
					WHERE if_id='".$this->_if_id."'
					";
			
			$queues  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			if (isset($queues['0'])) {
				
				foreach($queues as $queue) {
					
					$this->_connection_queues[] = new Thelist_Model_connectionqueue($queue['connection_queue_id']);
					
				}
			}
		}
		
		return $this->_connection_queues;
	}
	
	public function get_interface_configurations()
	{
		if ($this->_interface_configurations == null) {
			
			$sql = 	"SELECT * FROM interface_configuration_mapping
					WHERE if_id='".$this->_if_id."'
					";
		
			$existing_mappings	= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
			if (isset($existing_mappings['0'])) {
					
				foreach($existing_mappings as $existing_mapping) {
		
					$this->_interface_configurations[$existing_mapping['if_conf_map_id']]	= new Thelist_Model_interfaceconfiguration($existing_mapping['if_conf_id']);
					$this->_interface_configurations[$existing_mapping['if_conf_map_id']]->fill_mapped_values($existing_mapping['if_conf_map_id']);
		
				}
			} 
		}
		
		return $this->_interface_configurations;
	}
	
	public function get_interface_configuration($if_conf_id)
	{
		if ($this->_interface_configurations == null) {
			$this->get_interface_configurations();
		}
		
		if ($this->_interface_configurations != null) {

			foreach($this->_interface_configurations as $single_config) {
				
				if($single_config->get_if_conf_id() == $if_conf_id) {
					$configs_to_return[] = $single_config;
				}
			}
		}
		
		if (isset($configs_to_return)) {
			return $configs_to_return;
		} else {
			return false;
		}
	}

	public function fill_ip_traffic_rule_attributes($ip_traffic_rule_if_map_id, $ip_traffic_rule_if_role_id)
	{
		$this->_ip_traffic_rule_if_map_id		= $ip_traffic_rule_if_map_id;
		$this->_ip_traffic_rule_if_role_id		= $ip_traffic_rule_if_role_id;

	}
	
	public function get_ip_traffic_rule_if_role_id()
	{
		return $this->_ip_traffic_rule_if_role_id;
	}
	
	public function get_ip_traffic_rule_if_role_name()
	{
		if ($this->_ip_traffic_rule_if_role_id != null) {
			
			if ($this->_ip_traffic_rule_if_role_name == null) {
			
				$sql = 	"SELECT ip_traffic_rule_if_role_name FROM ip_traffic_rule_if_roles
						WHERE ip_traffic_rule_if_role_id='".$this->_ip_traffic_rule_if_role_id."'
						";
			
			
				$this->_ip_traffic_rule_if_role_name  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
			}

		} else {
			throw new exception("you must map the interface to a traffic rule before you can get the role name", 1070);
		}

		return $this->_ip_traffic_rule_if_role_name;
	}
	
	public function get_ip_traffic_rule_if_map_id()
	{
		return $this->_ip_traffic_rule_if_map_id;
	}

	public function get_new_filter_name($filter_name=null)
	{
		//the filter names differ from software to software on equipment types.
	

		
		//instanciating a parent witin the child object is bad, but writing a query that circumvents the 
		//validations is worse. we stipulate that this equipment object can never leave this method.
		$equipment = new Thelist_Model_equipments($this->_eq_id);
	
		if ($equipment->get_running_software_package()->get_software_package_name() == 'Centos') {
			
			//for filters on centos installations the filter allows for a 3 digit base 16 value as the
			//name. it has to be unique for the interface or we will be deleting filters in other queues
			//when we really only want to remove one from a single filter in a single queue.
			
			//we dont pick values lower than 10 and higer than 4000 becasue there are reserved values there
			//dont know how many, but to be on the safe side we take off 100 on each side.
			
			//tc on centos wants the value as a 3 digit hex value, but thats not the job of this method
			//that will be converted at the time we push to device
			
			if($this->get_connection_queues() != null) {
				$i=0;
				while ($i < 3899) {
					
					$fail = false;
						
					if ($filter_name == null) {
						$random_filter_name		= rand(100, 3999);
					} else {
						$random_filter_name		= $filter_name;
					}
		
					//check if there is a queue by the same name on this equipment
					foreach($this->get_connection_queues() as $connection_queue){
						
						if($connection_queue->get_connection_queue_filters() != null) {
			
							foreach($connection_queue->get_connection_queue_filters() as $queue_filter){
				
								if ($queue_filter->get_connection_queue_filter_name() == $random_filter_name) {
									
									//if even one filter on one queue matches the name, we fail and try again
									$fail = true;
									break;
								}
							}
						}
					}
						
					if ($fail == false) {
						return $random_filter_name;
					} elseif ($i == 3898){
						throw new exception('there are no more available filter names', 1073);
					} elseif ($filter_name != null && $fail == true) {
						//if we where testing if a particular filtername was in use and it is inuse, then we return false
						return false;
					}
							
				$i++;
				}
			}
			
			//add others than centos
		} else {
			throw new exception('we dont know how to create a filter name for this type of equipment and interface', 1074);
		}
		
		//others than centos
	}
	
}
?>