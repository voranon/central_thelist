<?php

//exception codes 9500-9599

class thelist_model_interfacepaths
{
	
	public function __construct()
	{	
		//purpose is to find commonly used paths using any classes required and always return an array of paths
		//even if only one path is expected return array, it will ensure that we can accomodate the model when 
		//multipath becomes needed
	}

	public function get_cpe_wan_to_border_router_paths($cpe_interface_obj)
	{
		//sp locator
		$sploc_obj 			= new Thelist_Model_servicepointresourcelocator();
		
		//get paths from cpe to patch panel
		$sploc_obj->get_service_point_interface_from_cpe($cpe_interface_obj);
	
		//even though we are not using the return from get_service_point_interface_from_cpe
		//we still get its validations, and that means we can be sure only one path is found
	
		//we dont want the equipment just the pure path
		$cpe_to_sp_paths = $sploc_obj->get_paths();
	
		//reset the paths for next method
		$sploc_obj->set_paths(null);
	
		//use sp interface to get border router path
		$sploc_obj->get_border_routers_allow_first_if($cpe_to_sp_paths['0']->get_last_interface());
	
		if ($sploc_obj->get_paths() != null) {
				
			foreach($sploc_obj->get_paths() as $sp_router_path) {
				
				preg_match("/^[0-9]+,(.*)/", $sp_router_path->get_path_string(), $result);
				$full_path_string = $cpe_to_sp_paths['0']->get_path_string() . "," . $result['1'];
				$cpe_to_border_router_paths[]	= new Thelist_Model_path($full_path_string);
			}
				
		} else {
			throw new exception('no routers are available from this service point interface', 9500);
		}
		
		return $cpe_to_border_router_paths;
	}
	
	public function get_service_paths_from_service_plan_quote_map($service_plan_quote_map_obj)
	{
		$serviceplanquoteeqtypemaps 	= $service_plan_quote_map_obj->get_service_plan_quote_eq_types();
		$service_plan_map_group			= $service_plan_quote_map_obj->get_service_plan()->get_service_plan_group();

		if ($serviceplanquoteeqtypemaps != null) {
		
			$service_paths	= array();
		
			foreach ($serviceplanquoteeqtypemaps as $serviceplanquoteeqtypemap) {
					
				if ($serviceplanquoteeqtypemap->get_active_mapped_equipment() != null) {
		
					$equipment = $serviceplanquoteeqtypemap->get_active_mapped_equipment();
		
					if ($equipment->get_equipment_roles() != null) {
							
						foreach ($equipment->get_equipment_roles() as $role) {
		
							if ($role->get_equipment_role_id() == 4 || $role->get_equipment_role_id() == 5 || $role->get_equipment_role_id() == 6) {
									
								if ($service_plan_map_group['service_plan_group_name'] == 'Internet') {
		
									//this is cpe equipment so it will only have a single interface that is gateway
									$gateway_interfaces = $equipment->get_default_gateway_interfaces();
		
									//we use alot of vlans and connection are made on physical interfaces, so we find the root interface
									$sql2 = "CALL find_root_interface('".$gateway_interfaces['0']->get_if_id()."')";
									$root_if_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
		
									$service_paths = array_merge($service_paths, $this->get_cpe_wan_to_border_router_paths($equipment->get_interface($root_if_id)));
		
								}
							}
						}
					}
				}
			}
			
		} else {
			//service plan has no equipment 
			return false; 
		}
		
		if (isset($service_paths)) {
			if (count($service_paths) == 0) {
				return false;
			} else {
				return $service_paths;
			}
		} else {
			return false;
		}
	}
	
}
?>