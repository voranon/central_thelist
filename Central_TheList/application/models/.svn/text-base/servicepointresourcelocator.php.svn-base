<?php 

//exception codes 2100-2199

class thelist_model_servicepointresourcelocator
{

	private $database;
	private $_pathfinder;
	private $_time;
	private $_service_plans_serviced=null;
	private $_interface_current_status=null;
	private $_interfaces_to_be_disconnected=null;
	private $_paths=null;
	
	public function __construct()
	{
		$this->_time					= Zend_Registry::get('time');

		$this->_pathfinder				= new Thelist_Model_pathfinder();
		
		
		
		//much of the data in this class is being moved around without using objects, the reason is that 
		//none of the data collected or manipulated is attributes of any one object. the objective for this class
		//is to return interface objects that fullfill some requirement.
		
	}
	
	public function set_paths($paths=null)
	{
		$this->_paths		= $paths;
		
	}
	
	public function get_paths()
	{
		return $this->_paths;
	}
	
	public function get_edge_switches($interface_obj)
	{
		//if you need new paths to find this particular feature then set paths === null first
		if($this->_paths == null) {
			
			$equipment_role											= new Thelist_Model_equipmentrole('7');
			$interface_feature_objs['0']							= new Thelist_Model_equipmentinterfacefeature('3');
			$interface_objs['0']									= $interface_obj;
			$path_limitation										= new Thelist_Model_pathlimitation();
			//limit the paths
			
			$path_limitation->set_first_interface_in_servicepoint_allowed('1');
			$path_limitation->set_interfaces_in_servicepoint_allowed('0');
			$path_limitation->deny_path_through_all_service_patch_panels();
			$path_limitation->set_check_if_first_interface_is_originator('0');
			$path_limitation->set_verify_first_interface_equipment_eq_type('0');
			$path_limitation->set_check_first_interface_equipment_role('0');
			$path_limitation->set_equipment_unit_groups_allowed('3');
			
			//now find the paths for this single interface
			$this->_paths			= $this->_pathfinder->get_paths_to_equipment_role($equipment_role, $interface_objs, $interface_feature_objs, $path_limitation);
		}
		
		if ($this->_paths != null) {
			
			foreach($this->_paths as $path) {
				
				$equipment_in_path	= $path->get_path_equipment();
	
				foreach($equipment_in_path as $eq_id => $equipment) {
					
					if (!isset($border_device_array[$eq_id]['equipment']) && $equipment['equipment']->get_equipment_role('7') != false) {
						
						$border_device_array[$eq_id]['equipment'] = $equipment['equipment'];
						
						if (isset($equipment['inbound_interface'])) {
							
							$border_device_array[$eq_id]['inbound_interface'] = $equipment['inbound_interface'];
							
						} 
						
						if(isset($equipment['outbound_interface'])) {
							
							$border_device_array[$eq_id]['outbound_interface'] = $equipment['outbound_interface'];
							
						}
					}
				}
			}
		}
			
		if (isset($border_device_array)) {
			
			return $border_device_array;
		
		} else {
			
			return false;
			
		}
	}
	
	public function get_border_routers_allow_first_if($interface_obj)
	{

		$equipment_role											= new Thelist_Model_equipmentrole('1');
		$interface_feature_objs['0']							= new Thelist_Model_equipmentinterfacefeature('3');
		$interface_objs['0']									= $interface_obj;
		$path_limitation										= new Thelist_Model_pathlimitation();
		//limit the paths

		$path_limitation->set_first_interface_in_servicepoint_allowed('1');
		$path_limitation->set_interfaces_in_servicepoint_allowed('0');
		$path_limitation->set_check_if_first_interface_is_originator('1');
		$path_limitation->set_verify_first_interface_equipment_eq_type('0');
		$path_limitation->set_check_first_interface_equipment_role('1');
		$path_limitation->set_equipment_unit_groups_allowed('3');

		//now find the paths for this single interface
		$this->_paths			= $this->_pathfinder->get_paths_to_equipment_role($equipment_role, $interface_objs, $interface_feature_objs, $path_limitation);

		if ($this->_paths != null) {
	
			foreach($this->_paths as $path) {
					
				$equipment_in_path	= $path->get_path_equipment();
	
				foreach($equipment_in_path as $eq_id => $equipment) {
	
					if ($equipment['equipment']->get_equipment_role('1') != false) {
	
						$border_device_array[] = $equipment['equipment'];

					}
				}
			}
		}
	
		if (isset($border_device_array)) {
	
			return $border_device_array;
	
		} else {
	
			return false;
	
		}
	
	
	}
	
	public function get_border_routers($interface_obj)
	{
		//if you need new paths to find this particular feature then set paths === null first
		if($this->_paths == null) {
				
			$equipment_role											= new Thelist_Model_equipmentrole('1');
			$interface_feature_objs['0']							= new Thelist_Model_equipmentinterfacefeature('3');
			$interface_objs['0']									= $interface_obj;
			$path_limitation										= new Thelist_Model_pathlimitation();
			//limit the paths
				
			$path_limitation->set_first_interface_in_servicepoint_allowed('1');
			$path_limitation->set_interfaces_in_servicepoint_allowed('0');
			$path_limitation->deny_path_through_all_service_patch_panels();
			$path_limitation->set_check_if_first_interface_is_originator('0');
			$path_limitation->set_verify_first_interface_equipment_eq_type('0');
			$path_limitation->set_check_first_interface_equipment_role('0');
			$path_limitation->set_equipment_unit_groups_allowed('3');
				
			//now find the paths for this single interface
			$this->_paths			= $this->_pathfinder->get_paths_to_equipment_role($equipment_role, $interface_objs, $interface_feature_objs, $path_limitation);
		}
		
		if ($this->_paths != null) {
		
			foreach($this->_paths as $path) {
			
				$equipment_in_path	= $path->get_path_equipment();
				
				foreach($equipment_in_path as $eq_id => $equipment) {
	
				if (!isset($border_device_array[$eq_id]['equipment']) && $equipment['equipment']->get_equipment_role('1') != false) {
						
						$border_device_array[$eq_id]['equipment'] = $equipment['equipment'];
						
						if (isset($equipment['inbound_interface'])) {
							
							$border_device_array[$eq_id]['inbound_interface'] = $equipment['inbound_interface'];
							
						} 
						
						if(isset($equipment['outbound_interface'])) {
							
							$border_device_array[$eq_id]['outbound_interface'] = $equipment['outbound_interface'];
							
						}
					}
				}
			}
		}
				
		if (isset($border_device_array)) {
		
			return $border_device_array;
				
		} else {
		
			return false;
		
		}
	}
	
	public function get_intermediate_switches($interface_obj)
	{
		//if you need new paths to find this particular feature then set paths === null first
		if($this->_paths == null) {
	
			$equipment_role											= new Thelist_Model_equipmentrole('1');
			$interface_feature_objs['0']							= new Thelist_Model_equipmentinterfacefeature('3');
			$interface_objs['0']									= $interface_obj;
			$path_limitation										= new Thelist_Model_pathlimitation();
			//limit the paths
	
			$path_limitation->set_first_interface_in_servicepoint_allowed('1');
			$path_limitation->set_interfaces_in_servicepoint_allowed('0');
			$path_limitation->deny_path_through_all_service_patch_panels();
			$path_limitation->set_check_if_first_interface_is_originator('0');
			$path_limitation->set_verify_first_interface_equipment_eq_type('0');
			$path_limitation->set_check_first_interface_equipment_role('0');
			$path_limitation->set_equipment_unit_groups_allowed('3');
	
			//now find the paths for this single interface
			$this->_paths			= $this->_pathfinder->get_paths_to_equipment_role($equipment_role, $interface_objs, $interface_feature_objs, $path_limitation);
		}
	
		if ($this->_paths != null) {
	
			foreach($this->_paths as $path) {
				
				//track if we found the edge switch yet
				$found_edge_switch = 'no';
					
				$equipment_in_path	= $path->get_path_equipment();
	
				foreach($equipment_in_path as $eq_id => $equipment) {
	
					//cannot be border router. 
					//cannot already be in the array
					if (
						!isset($intermediate_switch_array[$eq_id]['equipment']) 
						&& $equipment['equipment']->get_equipment_role('1') == false
						) {
						
						
						// cannot be first edge switch, but if we have already passed an edge switch then its ok to include it even though it is also an edgeswitch role
						//there are times when one edge connect to another for uplink and we need to include them.
						if($found_edge_switch == 'yes') {
							
							$intermediate_switch_array[$eq_id]['equipment'] = $equipment['equipment'];

							if (isset($equipment['inbound_interface'])) {
							
								$intermediate_switch_array[$eq_id]['inbound_interface'] = $equipment['inbound_interface'];
							
							}  else {
							
								throw new exception('intermediate switches should have inbound ports', 2101);
							
							}
							
							if(isset($equipment['outbound_interface'])) {
							
								$intermediate_switch_array[$eq_id]['outbound_interface'] = $equipment['outbound_interface'];
							
							} else {
							
								throw new exception('intermediate switches should have outbound ports', 2102);
							
							}
							
						}

						// cannot be first edge switch, 
						if ($equipment['equipment']->get_equipment_role('7') == true && $found_edge_switch == 'no') {
							
							$found_edge_switch = 'yes';

						}
					}
				}
			}
		}
	
		if (isset($intermediate_switch_array)) {
	
			return $intermediate_switch_array;
	
		} else {
	
			return false;
	
		}
	}
	
	public function get_service_point_interface_from_cpe($interface_obj)
	{
			$equipment_role											= new Thelist_Model_equipmentrole('2');
			$interface_feature_objs['0']							= null;
			$interface_objs['0']									= $interface_obj;
			$path_limitation										= new Thelist_Model_pathlimitation();
			//limit the paths
	
			$path_limitation->set_first_interface_in_servicepoint_allowed('0');
			$path_limitation->set_interfaces_in_servicepoint_allowed('1');
			$path_limitation->set_check_if_first_interface_is_originator('0');
			$path_limitation->set_verify_first_interface_equipment_eq_type('0');
			$path_limitation->set_check_first_interface_equipment_role('0');
			
			//there are senarios where the interface provided is not directly connected to the service point (i.e. deca internet)
			$path_limitation->set_equipment_unit_groups_allowed('3');
			$path_limitation->set_equipment_unit_groups_allowed('2');
			$path_limitation->set_equipment_unit_groups_allowed('1');
	
			//now find the paths for this single interface
			$this->_paths			= $this->_pathfinder->get_paths_to_equipment_role($equipment_role, $interface_objs, $interface_feature_objs, $path_limitation);
			
		if ($this->_paths != null) {
			
			$number_of_paths	= count($this->_paths);
	
			if ($number_of_paths == 1) {
				foreach($this->_paths as $path) {
						
					$equipment_in_path	= $path->get_path_equipment();
		
					foreach($equipment_in_path as $eq_id => $equipment) {
		
						if (!isset($border_device_array['equipment']) && $equipment['equipment']->get_equipment_role('2') != false) {
		
							$border_device_array['equipment'] = $equipment['equipment'];
							$border_device_array['inbound_interface'] = $equipment['inbound_interface'];
						}
					}
				}
			} else {
				
				throw new exception('looking for a service point interface from a cpe interface, but found more than one', 2100);
				
			}
		}
	
		if (isset($border_device_array)) {
	
			return $border_device_array;
	
		} else {
	
			return false;
	
		}
	}
	
	public function get_cpe_routers($interface_obj)
	{
		$interfaces[]	= $interface_obj;
	
		$this->_paths = $this->get_cpe_router_paths($interfaces);
		
		if ($this->_paths != null) {
	
			foreach($this->_paths as $path) {
					
				$equipment_in_path	= $path->get_path_equipment();
	
				foreach($equipment_in_path as $eq_id => $equipment) {
	
					if (!isset($border_device_array[$eq_id]['equipment']) && $equipment['equipment']->get_equipment_role('4') != false) {
	
						$border_device_array[$eq_id]['equipment'] = $equipment['equipment'];
	
						if (isset($equipment['inbound_interface'])) {
								
							$border_device_array[$eq_id]['inbound_interface'] = $equipment['inbound_interface'];
								
						}
	
						if(isset($equipment['outbound_interface'])) {
								
							$border_device_array[$eq_id]['outbound_interface'] = $equipment['outbound_interface'];
								
						}
					}
				}
			}
		}
	
		if (isset($border_device_array)) {
	
			return $border_device_array;
	
		} else {
	
			return false;
	
		}
	}
	

	
	public function get_service_plans_serviced()
	{
		return $this->_service_plans_serviced;
	}
	public function get_interface_current_status()
	{
		return $this->_interface_current_status;
	}
	public function get_interfaces_to_be_disconnected()
	{
		return $this->_interfaces_to_be_disconnected;
	}
	
	public function get_service_point_interface_for_service_plan_quote_map($service_plan_quote_map_obj, $options)
	{
		
		//find interface for a new service plan
		
		//if we have specified particular interfaces to use
		//it is easier to deal with the array if index is the if_id
		//since it would have to be unique anyways
		if (isset($options['if_ids'])) {
			foreach ($options['if_ids'] as $constraint_if_id) {
				$constraint_interfaces[$constraint_if_id] = $constraint_if_id;
			}
		}

			//get the unit
			$sales_quote_obj 				= new Thelist_Model_salesquote($service_plan_quote_map_obj->get_sales_quote_id());
			$unit							= $sales_quote_obj->get_end_user_service()->get_unit();
			
			//get all service points for the unit
			$unit_service_points			= $unit->get_unit_service_points();
			
			$all_interfaces					= array();
			
			$interfaces_in_use				= array();
			
			foreach($unit_service_points as $unit_service_point) {
				
				//get all interfaces
				if ($interfaces1 = $unit_service_point->get_service_point_interfaces()) {
					foreach($interfaces1 as $single_interface) {
							
						//if we are specifying a particular range of ips
						if (isset($constraint_interfaces)) {
							
							if (isset($constraint_interfaces[$single_interface->get_if_id()])) {
								$all_interfaces[$single_interface->get_if_id()] = $single_interface;
							} else {
								//we dont include it if it is not in the array
							}
							
						} else {
							$all_interfaces[$single_interface->get_if_id()] = $single_interface;
						}
					}
				}
				
				//get all interfaces in this units service points that have open tickets
				if ($interfaces2 = $this->get_all_service_point_interfaces_with_issues($unit_service_point)) {
					foreach($interfaces2 as $single_bad_interface) {
						
						$all_bad_interfaces[$single_bad_interface->get_if_id()] = $single_bad_interface;
						
					}
				}
								
				//get all interfaces in this units service points that are already inuse, we do not want to limit ourselfs to only interfaces that do not have tasks
				//we need all interfaces to make sure we count all used resources, if we dident get all interfaces we could end up with an interface that is sharing 
				//with one of our good interfaces. that would result in the used resources on that interface NOT being counted against the available on the shared.
				if ($interfaces3 = $unit_service_point->get_service_point_in_use_interfaces()) {
					foreach($interfaces3 as $single_inuse_interface) {
					
						$interfaces_in_use[$single_inuse_interface->get_if_id()] = $single_inuse_interface;
						
					}
				}
			}

			if (isset($options['calendar_based_install'])) {

				if ($options['calendar_based_install'] == 0) {
					//if this is a manual install then we include the service plan we are working with regardless of its status on the calendar
					$all_active_service_plan_maps			= $this->get_active_service_plans_for_unit($unit, $service_plan_quote_map_obj);
				} else {
					$all_active_service_plan_maps			= $this->get_active_service_plans_for_unit($unit, false);
				}
				
			} else {
				//get all the service plans that are or should be active,
				$all_active_service_plan_maps			= $this->get_active_service_plans_for_unit($unit, false);
			}

			//get the service point interfaces currently in use for the unit that owns the service plan map we are working with
			$current_sp_interfaces				= $this->get_unit_current_service_point_interfaces($unit);
			
			//now we need to figure out how many of a particular interface feature service is already inuse per sp interface
			$interface_feature_usages			= $this->get_service_point_interface_feature_usage($interfaces_in_use);

			//get the paths for the all the services that are required in this unit
			$unit_feature_requirements = array();
			if ($all_active_service_plan_maps != false) {

				foreach($all_active_service_plan_maps as $active_service_plan_map) {
					
					//put all service plans into an array, so we can use later					
					$all_service_plan_map_objs[] = $active_service_plan_map;
					
					//sum the requirements for all the currently active service plans, excluding the new plan
					if ($active_service_plan_map->get_service_plan_quote_map_id() != $service_plan_quote_map_obj->get_service_plan_quote_map_id()) {
							
						$old_feature_requirements = $active_service_plan_map->get_service_point_if_feature_requirements();
							
						if ($old_feature_requirements != null) {
								
							foreach ($old_feature_requirements as $feature_requirement2) {
									
								if (!isset($unit_feature_requirements[$feature_requirement2->get_if_feature_id()])) {
										
									$unit_feature_requirements[$feature_requirement2->get_if_feature_id()] = $feature_requirement2->get_mapped_if_feature_value();
										
								} else {
										
									$unit_feature_requirements[$feature_requirement2->get_if_feature_id()] = ($unit_feature_requirements[$feature_requirement2->get_if_feature_id()] + $feature_requirement2->get_mapped_if_feature_value());
										
								}
							}
						}
					}
					
					$supported_paths[$active_service_plan_map->get_service_plan_quote_map_id()]			= $this->get_paths_that_support_service_plan_quote_map($active_service_plan_map, $all_interfaces);
					$interface_features[$active_service_plan_map->get_service_plan_quote_map_id()]		= $this->get_service_point_interface_feature_availabillity($supported_paths[$active_service_plan_map->get_service_plan_quote_map_id()]);
					
					//generate a nice array that shows the current resource availabillity
					$resources_array[$active_service_plan_map->get_service_plan_quote_map_id()]			= $this->reconcile_resources($interface_features[$active_service_plan_map->get_service_plan_quote_map_id()], $interface_feature_usages);

				}
			}

			//now lets find out if there is a single good interface that will accomodate all old service plans in the unit
			//or if we need to provide a new interface for this new service plan
			if (isset($resources_array)) {
				$common_interfaces	= $this->get_common_interfaces($resources_array);
			} else {
				throw new exception("we have no resources available to service service_plan_map_id: ".$service_plan_quote_map_obj->get_service_plan_quote_map_id().", most likely because the unit that the service plan map belongs to has no interfaces in its service point or no active service plans", 2104);
			}

			//single variable that hold the interfaces that support the new plan
			//either they support all or there are none of those and we just grabed the result from the new service plan.
			if ($common_interfaces != false) {
				$supported_interface_array		= $common_interfaces;
			} else {
				$supported_interface_array		= $resources_array[$service_plan_quote_map_obj->get_service_plan_quote_map_id()];
			}
			
			//lets remove all interfaces that have tickets or are in use by other units.
			$remove_interfaces	= $interfaces_in_use;

			//first remove any current interfaces this unit is using from the interfaces to be removed
			if ($current_sp_interfaces != false) {

				foreach($current_sp_interfaces as $current_interface) {
					
					if (isset($remove_interfaces[$current_interface->get_if_id()])) {

						unset($remove_interfaces[$current_interface->get_if_id()]);
						
					}
				}
			}
			
			//second lets add all the bad interfaces to the list of interfaces we wish to remove
			//this means that if a current interface is marked as having a ticket then it will not be available
			//this behaiviour may not be the best choice always. we will just have to see how it works out
			if (isset($all_bad_interfaces)) {
				
				foreach ($all_bad_interfaces as $bad_interface) {
				
					if (!isset($remove_interfaces[$bad_interface->get_if_id()])) {
				
						$remove_interfaces[$bad_interface->get_if_id()] = $bad_interface;
				
					}
				}
			}

			
			//now lets remove the interfaces from the list of choices
			if (isset($remove_interfaces)) {
		
				foreach ($remove_interfaces as $remove_interface) {
					
					if (isset($supported_interface_array[$remove_interface->get_if_id()])) {
					
						unset($supported_interface_array[$remove_interface->get_if_id()]);
					
					}
				}
			}
			
			//last we need to locate the interface that will accomodate the new addition best.
			//we give preferance to current interfaces even if they are not the best match
			//because it is much less work for the tech in the field.
			$connect_to_interface 	= null;
			$already_connected		 = null;
			if ($current_sp_interfaces != false) {
				
				foreach($current_sp_interfaces as $single_current_interface) {
					
					if (isset($supported_interface_array[$single_current_interface->get_if_id()])) {
						
						$verification_result	= $this->interface_support_for_service_plan_features($service_plan_quote_map_obj, $supported_interface_array[$single_current_interface->get_if_id()]);	
						
						if ($verification_result != 'not_supported') {
							
							$connect_to_interface 	= $single_current_interface;
							$already_connected		= 'yes';
							
						}
					}
				}	
			}

			//if none of the current interfaces could support the new plan lets another that will
			//if we have any alternatives
			
			if ($connect_to_interface == null && $supported_interface_array != false) {

				foreach ($supported_interface_array as $conn_int_if_id => $conn_int_if_feats) {
					
					$verification_result	= $this->interface_support_for_service_plan_features($service_plan_quote_map_obj, $conn_int_if_feats);
					
					if ($verification_result != 'not_supported') {
						
						if (!isset($current_excess) || $current_excess > $verification_result) {
							
							$current_excess 		= $verification_result;
							$connect_to_interface 	= new Thelist_Model_equipmentinterface($conn_int_if_id);
							
						}
					}
				}	
			}

			if ($connect_to_interface == null) {

				return false;
				
			} else {	

				if ($already_connected == 'yes') {
					
					$this->_interface_current_status		= '1';
				
				} else {
					
					$this->_interface_current_status		= '0';
				}

				//remove all other interfaces if the interface we found handles every service in the unit
				if ($common_interfaces != false && $current_sp_interfaces != false) {
					
					$this->_service_plans_serviced	= $all_service_plan_map_objs;

					foreach($current_sp_interfaces as $single_current_interface) {
						
						if ($single_current_interface->get_if_id() != $connect_to_interface->get_if_id()) {
							
							$this->_interfaces_to_be_disconnected[] = $single_current_interface;
							
						}
					}
					
				} else {
					
					$this->_service_plans_serviced[]		= $service_plan_quote_map_obj;
					
				}
				
				return $connect_to_interface;
			}
	}
	
	private function interface_support_for_service_plan_features($service_plan_quote_map_obj, $array_of_available_features)
	{
		//find out what the new serviceplan will need
		$sp_feature_requirements = array();
			
		$new_feature_requirements = $service_plan_quote_map_obj->get_service_point_if_feature_requirements();
			
		if ($new_feature_requirements != null) {
		
			foreach ($new_feature_requirements as $feature_requirement) {
		
				//we are not concerned with the physical features at this stage, only services
				if ($feature_requirement->get_if_feature_type() == 'service') {
				
					if (!isset($sp_feature_requirements[$feature_requirement->get_if_feature_id()])) {
							
						$sp_feature_requirements[$feature_requirement->get_if_feature_id()] = $feature_requirement->get_mapped_if_feature_value();
							
					} else {
							
						$sp_feature_requirements[$feature_requirement->get_if_feature_id()] = ($sp_feature_requirements[$feature_requirement->get_if_feature_id()] + $feature_requirement->get_mapped_if_feature_value());
							
					}
				}
			}
		}
		
		$interface_support 	= 'yes';
		$total_excess		= '0';
		
		foreach ($sp_feature_requirements as $feature_id => $feature_value){
			
			if (!isset($array_of_available_features[$feature_id])) {
				
				$interface_support = 'no';
				
			} else {
				
				if ($feature_value == null && $array_of_available_features[$feature_id] == 'no_limit'){
					
					$feature_value_remaining	= 'no_limit';
					
				} else {
					
					$feature_value_remaining = ($array_of_available_features[$feature_id] + $feature_value);
					
					//if the resource becomes negative then it is not supported
					if ($feature_value_remaining < '0') {
						
						$interface_support = 'no';
						
					}
				}
			}
			
			if ($feature_value_remaining != 'no_limit') {
				
				$total_excess	= ($total_excess + $feature_value_remaining);
			}
			
		}
		
		if ($interface_support 	== 'yes') {
			
			return $total_excess;
			
		} else {
			
			//cant return false, because total excess can be 0 and that is the same as false
			return 'not_supported';
			
		}

	}
	
	
	private function get_common_interfaces($resources_array)
	{
		//find interfaces that are present in all arrays and agregate them into a single array
		if ($resources_array == false) {
			//first lets colapse the resources into a single array that only contains good interfaces that are available in all the arrays
			$collapsed_resources['flat'] = array();
			foreach($resources_array as $service_plan_map_id => $interface) {
				
				foreach ($interface as $if_id => $feature) {
						
						$collapsed_resources['flat'][$if_id]	= $if_id;
	
				}
			}
			
			$collapsed_resources['in_common'] = $collapsed_resources['flat'];
	
			foreach($collapsed_resources['in_common'] as $if_id) {		
			
				foreach($resources_array as $service_plan_map_id2 => $interface2) {
									
					if (!isset($interface2[$if_id])) {
						
						unset($collapsed_resources['in_common'][$if_id]);
	
					}
				}
			}
			
			//now lets fill the common interfaces with all the features and their values
			
			foreach($collapsed_resources['in_common'] as $if_id3) {
					
				foreach($resources_array as $service_plan_map_id4 => $interface4) {
					
					foreach ($interface4 as $if_id4 => $feature4) {
	
						if($if_id4 == $if_id3) {
							
							if (!isset($common_resources)) {
								
								$common_resources = array();
								
							}
							
							if(!isset($common_resources[$if_id3])) {
								
								$common_resources[$if_id3]	= $feature4;
															
							} else {
								
								foreach($feature4 as $feature_id => $feature_value) {
									
									$common_resources[$if_id3][$feature_id]	= $feature_value;
									
								}
							}
						}
					}
				}	
			}
			
			if (isset($common_resources)) {
					
				return $common_resources;
					
			} else {
					
				return false;
					
			}
			
		} else {
			
			return false;
			
		}
	} 
	
	
	
	private function get_active_service_plans_for_unit($unit, $include_service_plan_map_obj=false)
	{
		//get the service plans that should be / are active in a unit
		$all_end_users						= $unit->get_end_user_services('active');
		
		foreach ($all_end_users as $single_end_user){
		
			$all_sales_quotes	= $single_end_user->get_sales_quotes();

			if ($all_sales_quotes != null) {
				
				foreach($all_sales_quotes as $single_sales_quote) {
						
					$all_service_plan_maps = $single_sales_quote->get_service_plan_quote_maps();
						
					if ($all_service_plan_maps != null) {
	
						foreach($all_service_plan_maps as $service_plan_map) {
				
							$include_service_plan	= 'no';
							//we need to make sure the service plans we find paths for are either active or will become active shortly
							//we do this by getting all the tasks and figuring out if there is an appointment today or if the service
							//has already been activated and that it has not yet been deactivated. 
							
							$sp_activation		= $service_plan_map->get_activation();
							$sp_deactivation	= $service_plan_map->get_deactivation();
							
							if ($include_service_plan_map_obj != false) {
	
								$task		= $include_service_plan_map_obj->get_service_plan_quote_tasks_map();
								
								if ($task == null) {
									throw new exception("we are overriding the service plan requirements with sp_map_id: ".$include_service_plan_map_obj->get_service_plan_quote_map_id().", but this plan does not have a task, that is required even if overiding other validations", 2105);
								}
								
								if ($include_service_plan_map_obj->get_service_plan_quote_map_id() == $service_plan_map->get_service_plan_quote_map_id()) {
									//allow the service plan regardless of validations
									$active_service_plans[] = $service_plan_map;
									$include_service_plan = 'yes';
								}
							}
							
							if ($include_service_plan == 'no') {
								
								if ($sp_activation == null && $sp_deactivation == null) {
	
									$task		= $service_plan_map->get_service_plan_quote_tasks_map();
	
									if ($task != null) {
	
										$task_appointments	= $task->get_calendar_appointments();
										
										if ($task_appointments != null) {
											
											foreach($task_appointments as $task_appointment) {
												
												$appointment_start_time		= $task_appointment->get_scheduled_start_time();
												
												if (
												$this->_time->convert_mysql_datetime_to_epoch($this->_time->get_todays_date_mysql_format()) <= $this->_time->convert_mysql_datetime_to_epoch($appointment_start_time)
												&& $this->_time->convert_mysql_datetime_to_epoch($this->_time->get_tomorrows_date_mysql_format()) >= $this->_time->convert_mysql_datetime_to_epoch($appointment_start_time)
												) {
													//if there is an appointment for today
													$include_service_plan = 'yes';
													
												}
											}
										}
									}
									
								} elseif($sp_deactivation != null) {
									
									if ($this->_time->convert_mysql_datetime_to_epoch($this->_time->get_tomorrows_date_mysql_format()) <= $this->_time->convert_mysql_datetime_to_epoch($sp_deactivation)) {
										
										//if the deactivation in not happening today
										$include_service_plan = 'yes';
										
									}
								} elseif($sp_activation != null && $sp_deactivation == null) {
									
									//if this is an active service
									$include_service_plan = 'yes';
									
								}
							}
		
						if ($include_service_plan == 'yes')
							$active_service_plans[] = $service_plan_map;
						}
					}
				}
			}
		}
		
		if (isset($active_service_plans)) {
			
			return $active_service_plans;
			
		} else {
			
			return false;
			
		}
	}
	
	
	private function reconcile_resources($interface_features, $interface_feature_usages)
	{
		//provide array that shows available resources, per service point interface
			
			//only interfaces already inuse start here
		if ($interface_feature_usages != false) {	
			foreach($interface_feature_usages as $int_id => $interface_feature) {
					
				foreach($interface_feature as $feature_id => $feature_value) {
					
					//$all interface array start here
					foreach($interface_features as $int_id2 => $feature2) {
						
						if ($int_id == $int_id2) {
						
							foreach($feature2 as $feature_id2 => $feature_provider2) {
								
								if ($feature_id == $feature_id2) {
								
									foreach($feature_provider2 as $provider_if_id2 => $feature_value2) {
	
										//we only deal with real numbers
										if ($feature_value2 != null) {
											
											//the new value for the feature
											$new_value		= ($feature_value + $feature_value2);
											
											//now make sure to change it for all other interfaces that have the same provider
											foreach($interface_features as $int_id3 => $feature3) {
											
												foreach($feature3 as $feature_id3 => $feature_provider3) {
											
													foreach($feature_provider3 as $provider_if_id3 => $feature_value3) {
															
														if ($provider_if_id3 == $provider_if_id2) {
																
															//change the value of the provider
															$interface_features[$int_id3][$feature_id3][$provider_if_id3] = $new_value;
																
														}
													}
												}
											}
										}
									}
								}
							}
						}	
					}
				}	
			}
		}
			
			//now strip out the extra information about providers and return a array of interfaces with the feature id and the values
			
		if ($interface_features != false) {
		
			$final_array		= array();
			
			foreach($interface_features as $int_id4 => $feature4) {
	
				$final_array[$int_id4]							= array();
				
				foreach($feature4 as $feature_id4 => $feature_provider4) {
			
					$feature_val_null = 'no';
					foreach($feature_provider4 as $provider_if_id4 => $feature_value4) {
						
						//we cannot just sum the array, because that will produce a 0 and we loose the null information that indicates 
						//that the service is not a limited resource 
						if ($feature_value4 == null) {
							
							$feature_val_null = 'yes';
						}
					}
					
					if ($feature_val_null == 'no') {
						
						$final_array[$int_id4][$feature_id4]		= array_sum($feature_provider4);
					
					} else {
						
						$final_array[$int_id4][$feature_id4]		= 'no_limit';
						
					}
				}
			}
			
			return $final_array;
			
		} else {
						
			return false;
			
		}
	}
	
	private function get_service_point_interface_feature_availabillity($paths)
	{
	
		//build array with endpoints for tv
		if (isset($paths['tv'])) {
	
			foreach($paths['tv'] as $tv_path) {
	
				//build array based on the first if_id
				if (!isset($return_array[$tv_path->get_first_interface_id()])) {
	
					$return_array[$tv_path->get_first_interface_id()]	= $tv_path->get_last_interface_id();
	
				} else {
						
					$return_array[$tv_path->get_first_interface_id()]	.= "," .$tv_path->get_last_interface_id();
						
				}
			}
		}
			
		//build array with endpoints internet
		if (isset($paths['internet'])) {
				
			foreach($paths['internet'] as $internet_path) {
	
				//build array based on the first if_id
				if (!isset($return_array[$internet_path->get_first_interface_id()])) {
	
					$return_array[$internet_path->get_first_interface_id()]	= $internet_path->get_last_interface_id();
	
				} else {
						
					$return_array[$internet_path->get_first_interface_id()]	.= "," .$internet_path->get_last_interface_id();
						
				}
			}
		}
			
		//build array with endpoints for phone
		if (isset($paths['phone'])) {
				
			foreach($paths['phone'] as $phone_path) {
					
				//build array based on the first if_id
				if (!isset($return_array[$phone_path->get_first_interface_id()])) {
						
					$return_array[$phone_path->get_first_interface_id()]	= $phone_path->get_last_interface_id();
						
				} else {
						
					$return_array[$phone_path->get_first_interface_id()]	.= "," .$phone_path->get_last_interface_id();
						
				}
			}
		}
			
		if (isset($return_array)) {
	
			$final_array = array();
	
			foreach($return_array as $if_id => $endpoints) {
					
				$sql	= 	"SELECT ifm.if_id, ifm.if_feature_id, ifm.if_feature_value FROM interface_feature_mapping ifm
									INNER JOIN interface_features ifeats ON ifeats.if_feature_id=ifm.if_feature_id
									WHERE ifm.if_id IN (".$endpoints.")
									AND ifeats.if_feature_type='service'
									";
				$sp_if_feat_usages  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);

				if (isset($sp_if_feat_usages['0'])) {
	
					foreach($sp_if_feat_usages as $sp_if_feat_usage) {
							
						if(!isset($final_array[$if_id][$sp_if_feat_usage['if_feature_id']])) {
	
							$final_array[$if_id][$sp_if_feat_usage['if_feature_id']][$sp_if_feat_usage['if_id']]	= $sp_if_feat_usage['if_feature_value'];
	
	
						} 
					}
				}
			}
		}

		 if (isset($final_array)) {
		 	
		 	return $final_array;

		 } else {
	
			return false;
	
		}
	}
	
	private function get_service_point_interface_feature_usage($interfaces)
	{
		//figure out how many of a particular interface feature service is available per sp interface this really is only a matter to checking swm tuners currently add as we go
		$inuse_receiver_paths	= $this->get_cpe_receiver_paths($interfaces);
		$inuse_router_paths		= $this->get_cpe_router_paths($interfaces);
		$inuse_phone_paths		= $this->get_cpe_phone_paths($interfaces);
		
		//build array with endpoints for receivers
			if ($inuse_receiver_paths != false) {
				
				foreach($inuse_receiver_paths as $inuse_receiver_path) {
		
					//build array based on the first if_id
					if (!isset($return_array[$inuse_receiver_path->get_first_interface_id()])) {
								
						$return_array[$inuse_receiver_path->get_first_interface_id()]	= $inuse_receiver_path->get_last_interface_id();
								
					} else {
							
						$return_array[$inuse_receiver_path->get_first_interface_id()]	.= "," .$inuse_receiver_path->get_last_interface_id();
							
					}
				}
			}
			
			//build array with endpoints for routers
			if ($inuse_router_paths != false) {
			
				foreach($inuse_router_paths as $inuse_router_path) {
		
					//build array based on the first if_id
					if (!isset($return_array[$inuse_router_path->get_first_interface_id()])) {
								
						$return_array[$inuse_router_path->get_first_interface_id()]	= $inuse_router_path->get_last_interface_id();
								
					} else {
							
						$return_array[$inuse_router_path->get_first_interface_id()]	.= "," .$inuse_router_path->get_last_interface_id();
							
					}
				}
			}
			
			//build array with endpoints for phone
			if ($inuse_phone_paths != false) {
					
				foreach($inuse_phone_paths as $inuse_phone_path) {
			
					//build array based on the first if_id
					if (!isset($return_array[$inuse_phone_path->get_first_interface_id()])) {
			
						$return_array[$inuse_phone_path->get_first_interface_id()]	= $inuse_phone_path->get_last_interface_id();
			
					} else {
							
						$return_array[$inuse_phone_path->get_first_interface_id()]	.= "," .$inuse_phone_path->get_last_interface_id();
							
					}
				}
			}
			
			if (isset($return_array)) {
				
				$final_array = array();
				
				foreach($return_array as $if_id => $endpoints) {
					
					$sql	= 	"SELECT ifm.if_feature_id, SUM(ifm.if_feature_value) AS if_feature_usage FROM interface_feature_mapping ifm
								INNER JOIN interface_features ifeats ON ifeats.if_feature_id=ifm.if_feature_id
								WHERE ifm.if_id IN (".$endpoints.")
								AND ifeats.if_feature_type='service'
								GROUP BY ifm.if_feature_id
								";
					$sp_if_feat_usages  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
					
					if (isset($sp_if_feat_usages['0'])) {
						
						foreach($sp_if_feat_usages as $sp_if_feat_usage) {
							
							if(!isset($final_array[$if_id][$sp_if_feat_usage['if_feature_id']])) {
								
								$final_array[$if_id][$sp_if_feat_usage['if_feature_id']]	= $sp_if_feat_usage['if_feature_usage'];
								
								
							} else {
								
								$final_array[$if_id][$sp_if_feat_usage['if_feature_id']]	= ($final_array[$if_id][$sp_if_feat_usage['if_feature_id']] + $sp_if_feat_usage['if_feature_usage']);
								
							}
						}
					}
				}
				
				return $final_array;
				
			} else {
				
				return false;
				
			}
	}
	
	private function get_paths_that_support_service_plan_quote_map($service_plan_quote_map_obj, $interfaces)
	{
		
		//find the paths that support the service plan features form the pool of interfaces provided.
		if (is_object($service_plan_quote_map_obj) && is_array($interfaces)) {

			$service_plan_group	= $service_plan_quote_map_obj->get_service_plan()->get_service_plan_group();

			if ($service_plan_group['service_plan_group_id'] == '3') {
				
				$tv_paths = $this->get_tv_border_device_paths($service_plan_quote_map_obj, $interfaces);
				
				if ($tv_paths != null) {
						
					if (!isset($service_paths['tv'])) {
						$service_paths['tv']			= array();
					}
					$service_paths['tv'] = array_merge($service_paths['tv'], $tv_paths);
				}
					
			} elseif ($service_plan_group['service_plan_group_id'] == '2') {
					
				
				$phone_paths = $this->get_border_channel_paths($service_plan_quote_map_obj, $interfaces);
				
				if ($phone_paths != null) {
				
					if (!isset($service_paths['phone'])) {
						$service_paths['phone']			= array();
					}
					$service_paths['phone'] = array_merge($service_paths['phone'], $phone_paths);
				}
		
			} elseif ($service_plan_group['service_plan_group_id'] == '1') {

				$inet_paths = $this->get_border_router_paths($service_plan_quote_map_obj, $interfaces);

				if ($inet_paths != null) {
					
					if (!isset($service_paths['internet'])) {
						$service_paths['internet']			= array();
					}
					$service_paths['internet'] = array_merge($service_paths['internet'], $inet_paths);
				}
			}
	
			if (isset($service_paths)) {
				return $service_paths;
			} else {
				return false;
			}		
			
			
		} else {
			
			throw new exception('only objects and arrays please', 2103);
			
		}

	}
	
	
	public function get_unit_current_service_point_interfaces($unit_obj)
	{
		
		//in order for a unit to attach to a service point interface, there must be some equipment in the unit
		//because every service MUST be connected directly to the service point by some equipment in the unit
		//we dont have to use the crawler we only have to go one connection deep.
		
		$sql = "SELECT
					CASE
					WHEN i2.if_id IS NOT NULL THEN i2.if_id
					ELSE i3.if_id
					END AS sp_if_id
					FROM equipment_mapping em
					INNER JOIN sales_quote_eq_type_map_equipment_mapping sqetmem ON sqetmem.equipment_map_id=em.equipment_map_id
					INNER JOIN interfaces i ON i.eq_id=em.eq_id
					LEFT OUTER JOIN interface_connections ic1 ON ic1.if_id_a=i.if_id
					LEFT OUTER JOIN interface_connections ic2 ON ic2.if_id_b=i.if_id
					LEFT OUTER JOIN interfaces i2 ON i2.if_id=ic1.if_id_b
					LEFT OUTER JOIN interfaces i3 ON i3.if_id=ic2.if_id_a
					WHERE em.unit_id='".$unit_obj->get_unit_id()."'
					AND (i2.service_point_id IS NOT NULL OR i3.service_point_id IS NOT NULL)
					AND em.eq_map_deactivated IS NULL
					";
		
		$sp_if_ids  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		if (isset($sp_if_ids['0'])) {
			foreach($sp_if_ids as $sp_if_id) {
				$sp_interfaces[$sp_if_id['sp_if_id']]			= new Thelist_Model_equipmentinterface($sp_if_id['sp_if_id']);	
			}
			return $sp_interfaces;
		} else {
			return false;
		}
	}

	private function get_all_service_point_interfaces_with_issues($unit_service_point)
	{
		
		//remove all interfaces that have open tasks, this means they are in trouble
		//we dont care to use them.
		//when we find the unit that are using i.e. tuners and they are sharing a resource 
		//then that resource will not match any of the ones available to us because the other interface is not part of our
		//service point interfaces pool.
		$interfaces			= $unit_service_point->get_service_point_interfaces();
		
		if ($interfaces != false) {
			foreach($interfaces as $interface) {
				
				$interface_open_tasks	= $interface->get_tasks('open');
				
				if ($interface_open_tasks != null) {
					
					$service_point_interfaces[$interface->get_if_id()]	= $interface;
					
				}
			}
		} 
		
		if (isset($service_point_interfaces)) {
		
			return $service_point_interfaces;
		
		} else {
			
			return false;
			
		}
	}

	private function get_cpe_receiver_paths($interfaces)
	{
		$equipment_roles['0']						= new Thelist_Model_equipmentrole('5');
			
		//set all the interface features that are required
		$interface_feature_objs['0']				=  new Thelist_Model_equipmentinterfacefeature('5');

		//limit the paths
		$path_limitation							= new Thelist_Model_pathlimitation();

		$path_limitation->set_first_interface_in_servicepoint_allowed('1');
		$path_limitation->set_interfaces_in_servicepoint_allowed('0');
		$path_limitation->deny_path_through_all_service_patch_panels();
		$path_limitation->set_check_if_first_interface_is_originator('0');
		$path_limitation->set_verify_first_interface_equipment_eq_type('0');
		$path_limitation->set_check_first_interface_equipment_role('0');
		$path_limitation->set_equipment_unit_groups_allowed('1');
		$path_limitation->set_equipment_unit_groups_allowed('2');
			
		return $this->_pathfinder->get_paths_to_equipment_role($equipment_roles['0'], $interfaces, $interface_feature_objs, $path_limitation);
				
	}
	
	private function get_cpe_phone_paths($interfaces)
	{
		$equipment_roles['0']						= new Thelist_Model_equipmentrole('6');
			
		//set all the interface features that are required
		$interface_feature_objs['0']				=  new Thelist_Model_equipmentinterfacefeature('6');
	
		//limit the paths
		$path_limitation							= new Thelist_Model_pathlimitation();
	
		$path_limitation->set_first_interface_in_servicepoint_allowed('1');
		$path_limitation->set_interfaces_in_servicepoint_allowed('0');
		$path_limitation->deny_path_through_all_service_patch_panels();
		$path_limitation->set_check_if_first_interface_is_originator('0');
		$path_limitation->set_verify_first_interface_equipment_eq_type('0');
		$path_limitation->set_check_first_interface_equipment_role('0');
		$path_limitation->set_equipment_unit_groups_allowed('1');
		$path_limitation->set_equipment_unit_groups_allowed('2');
			
		return $this->_pathfinder->get_paths_to_equipment_role($equipment_roles['0'], $interfaces, $interface_feature_objs, $path_limitation);
	
	}

	private function get_cpe_router_paths($interfaces)
	{
		$equipment_roles['0']						= new Thelist_Model_equipmentrole('4');
			
		//set all the interface features that are required
		$interface_feature_objs['0']				=  new Thelist_Model_equipmentinterfacefeature('3');
	
		//limit the paths
		$path_limitation							= new Thelist_Model_pathlimitation();
	
		$path_limitation->set_first_interface_in_servicepoint_allowed('1');
		$path_limitation->set_interfaces_in_servicepoint_allowed('0');
		$path_limitation->deny_path_through_all_service_patch_panels();
		$path_limitation->set_check_if_first_interface_is_originator('0');
		$path_limitation->set_verify_first_interface_equipment_eq_type('0');
		$path_limitation->set_check_first_interface_equipment_role('0');
		$path_limitation->set_equipment_unit_groups_allowed('1');
		$path_limitation->set_equipment_unit_groups_allowed('2');
			
		return $this->_pathfinder->get_paths_to_equipment_role($equipment_roles['0'], $interfaces, $interface_feature_objs, $path_limitation);
	
	}
	
	private function get_service_point_paths($interfaces)
	{
		$equipment_roles['0']						= new Thelist_Model_equipmentrole('2');
	
		//limit the paths
		$path_limitation							= new Thelist_Model_pathlimitation();
	
		$path_limitation->set_first_interface_in_servicepoint_allowed('1');
		$path_limitation->set_interfaces_in_servicepoint_allowed('1');
		$path_limitation->set_check_if_first_interface_is_originator('1');
		$path_limitation->set_verify_first_interface_equipment_eq_type('1');
		$path_limitation->set_check_first_interface_equipment_role('1');
			
		return $this->_pathfinder->get_paths_to_equipment_role($equipment_roles['0'], $interfaces, $interface_feature_objs, $path_limitation);
	
	}

	private function get_tv_border_device_paths($sales_quote_map_obj, $interfaces)
	{
		$equipment_roles['0']						= new Thelist_Model_equipmentrole('3');
			
		//get all the interface features that are required
		$interface_feature_objs							= $sales_quote_map_obj->get_service_point_if_feature_requirements();
			
		if ($interface_feature_objs != false) {
		
			//limit the paths
			$path_limitation						= new Thelist_Model_pathlimitation();
				
			$path_limitation->set_first_interface_in_servicepoint_allowed('1');
			$path_limitation->set_interfaces_in_servicepoint_allowed('0');
			$path_limitation->deny_path_through_all_service_patch_panels();
			$path_limitation->set_check_if_first_interface_is_originator('0');
			$path_limitation->set_verify_first_interface_equipment_eq_type('0');
			$path_limitation->set_check_first_interface_equipment_role('0');
			$path_limitation->set_equipment_unit_groups_allowed('3');
			
			return $this->_pathfinder->get_paths_to_equipment_role($equipment_roles['0'], $interfaces, $interface_feature_objs, $path_limitation);
			
		} else {
			
			return false;
			
		}
	}
	
	private function get_border_router_paths($sales_quote_map_obj, $interfaces)
	{
		$equipment_roles['0']						= new Thelist_Model_equipmentrole('1');
			
		//get all the interface features that are required
		$interface_feature_objs							=$sales_quote_map_obj->get_service_point_if_feature_requirements();
			
		if ($interface_feature_objs != false) {
			
			//limit the paths
			$path_limitation						= new Thelist_Model_pathlimitation();
				
			$path_limitation->set_first_interface_in_servicepoint_allowed('1');
			$path_limitation->set_interfaces_in_servicepoint_allowed('0');
			$path_limitation->deny_path_through_all_service_patch_panels();
			$path_limitation->set_check_if_first_interface_is_originator('0');
			$path_limitation->set_verify_first_interface_equipment_eq_type('0');
			$path_limitation->set_check_first_interface_equipment_role('0');
			$path_limitation->set_equipment_unit_groups_allowed('3');
				
			//now find the paths
			return $this->_pathfinder->get_paths_to_equipment_role($equipment_roles['0'], $interfaces, $interface_feature_objs, $path_limitation);;
			
		} else {
			
			return false;
			
		}

	}
	
}
?>