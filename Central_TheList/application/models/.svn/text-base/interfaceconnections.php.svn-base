<?php

//by martin
//exception codes 500-599

class thelist_model_interfaceconnections
{
		
	private $database;
	private $logs;
	private $_time;
	
	public function __construct()
	{

		
		$this->logs					= Zend_Registry::get('logs');
		$this->_time				= Zend_Registry::get('time');
		
	}
	
	
	
	public function remove_interface_connection($interface_a_obj, $interface_b_obj)
	{
	
		if (is_object($interface_a_obj) && is_object($interface_b_obj)) {
				
			$sql	= 	"SELECT if_conn_id FROM interface_connections
						WHERE (if_id_a='".$interface_a_obj->get_if_id()."' AND if_id_b='".$interface_b_obj->get_if_id()."') 
						OR
						(if_id_a='".$interface_b_obj->get_if_id()."' AND if_id_b='".$interface_a_obj->get_if_id()."') ";
				
			$if_connections = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			if (isset($if_connections['0'])) {
				
				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);
	
				foreach($if_connections as $if_connection) {
					
					Zend_Registry::get('database')->delete_single_row($if_connection['if_conn_id'], 'interface_connections', $class, $method);
					
				}
			}
				
		} 
	}
	

	public function create_interface_connection($interface_a_obj, $interface_b_obj)
	{
		
		if (is_object($interface_a_obj) && is_object($interface_b_obj)) {
			
			$already_exist	= $this->interface_connection_exist($interface_a_obj, $interface_b_obj);
			
			if (!$already_exist) {
				
				$data = array(
				
						'if_id_a'   			=>  $interface_a_obj->get_if_id(),
						'if_id_b' 				=>  $interface_b_obj->get_if_id(),
				
				);

				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);
				
				Zend_Registry::get('database')->insert_single_row('interface_connections',$data,$class,$method);
				
			}
			
		} else {
			
			throw new exception('you must provide interface objects', 501);
			
		}
	}
	
	public function interface_connection_exist($interface_a_obj, $interface_b_obj)
	{
		$sql	= 	"SELECT if_conn_id FROM interface_connections
					WHERE (if_id_a='".$interface_a_obj->get_if_id()."' AND if_id_b='".$interface_b_obj->get_if_id()."') 
					OR
					(if_id_a='".$interface_b_obj->get_if_id()."' AND if_id_b='".$interface_a_obj->get_if_id()."') ";
		
		
		$if_connections = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		if (isset($if_connections['1'])) {
			
			throw new exception('these 2 interfaces have more than a single connection to each other', 500);
			
		} elseif (isset($if_connections['0'])) {
			
			return true;
			
		} else {
			
			return false;
			
		}
	}
	
	public function get_interface_connections($interface_obj)
	{
		$sql3 = 	"SELECT CASE
					WHEN if_id_a='".$interface_obj->get_if_id()."' THEN if_id_b
					ELSE if_id_a
					END AS if_id
					FROM interface_connections
					WHERE (if_id_a='".$interface_obj->get_if_id()."' OR if_id_b='".$interface_obj->get_if_id()."')
					";
	
		$connections = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql3);

		if (isset($connections['0'])) {
			foreach($connections as $connection) {
				$if_connections[]		= new Thelist_Model_equipmentinterface($connection['if_id']);
			}
			
			return $if_connections;
			
		} else {
			return false;
		}	
	}
	
	public function connect_equipment_in_task_to_service_point($sp_interface_obj, $equipment_and_quote_maps)
	{
		//connect the equipment in the array to the sp interface, according to the specification in the service
		//plans.
		
		//check that there is not already a generic splitter on the service point interface and the new equipment has another one to connect
		foreach ($equipment_and_quote_maps['equipment'] as $index => $equipment) {
			
			//is there a generic splitter in the install?
			if ($equipment->get_eq_type()->get_eq_type_id() == 65) {
				
				//get the service plans connections
				$current_sp_connections	= $this->get_interface_connections($sp_interface_obj);
				
				if ($current_sp_connections != false) {
					
					foreach ($current_sp_connections as $current_sp_connection) {
						
						$sp_connect_eq	= new Thelist_Model_equipments($current_sp_connection->get_eq_id());
						
						//is there already a generic 9way on the port
						if ($sp_connect_eq->get_eq_type()->get_eq_type_id() == 65) {
							
							//if there is replace the curent equipment with the old one
							
							$equipment_and_quote_maps['equipment'][$index] = $sp_connect_eq; 
							//we are already connected in the service point
							$if_used_for_sp_connection = $current_sp_connection;
						}
					}
					
				} else {
					
					throw new exception('the provided service point interface is not connected to anything', 504);
					
				}
			}
		}
		
		//connect the service point interface to a piece of equipment, if not already done
		if (!isset($if_used_for_sp_connection)) {
			
			$if_used_for_sp_connection	= $this->map_service_point_to_equipment_interface($sp_interface_obj, $equipment_and_quote_maps);
			
		}
		
		//create a variable that will track the interfaces that have already been used.
		$used_interfaces	= "," . $if_used_for_sp_connection->get_if_id() . ",";
		
		//create a variable for all the eq_ids that have already had atleast one connection.
		$used_equipment	= ",";
		
		//we count the equipment that has interfaces
		$count_eq_with_interfaces = 0;
		
		$i=0;
		foreach ($equipment_and_quote_maps['equipment'] as $equipment) {
			
			if ($equipment->get_interfaces() != null) {
				
				$count_eq_with_interfaces++;
				
				foreach($equipment->get_interfaces() as $eq_interface) {
				
					$sql	= 	"SELECT CASE
								WHEN spitm.if_type_id_a!='".$eq_interface->get_if_type()->get_if_type_id()."' THEN spitm.if_type_id_a
								ELSE spitm.if_type_id_b
								END AS conn_if_type_id FROM service_plan_quote_eq_type_mapping spqetm
								INNER JOIN service_plan_eq_type_mapping spetm ON spetm.service_plan_eq_type_map_id=spqetm.service_plan_eq_type_map_id
								INNER JOIN service_plan_if_type_mapping spitm ON spitm.service_plan_id=spetm.service_plan_id
								WHERE spqetm.service_plan_quote_eq_type_map_id='".$equipment_and_quote_maps['service_plan_quote_eq_type_map'][$i]->get_service_plan_quote_eq_type_map_id()."'
								AND (spitm.if_type_id_a='".$eq_interface->get_if_type()->get_if_type_id()."' OR spitm.if_type_id_b='".$eq_interface->get_if_type()->get_if_type_id()."')
								";
					
					$connect_to_if_type_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
				
					if (isset($connect_to_if_type_id['conn_if_type_id'])) {
						
						foreach($equipment_and_quote_maps['equipment'] as $equipment2) {
							
							if ($equipment2->get_interfaces() != null) {
								
								foreach($equipment2->get_interfaces() as $eq_interface2) {
									
									// make sure the interface has not been used yet and that the type is correct and that we are not attaching the interface to the same equipment  
									if ($eq_interface2->get_if_type()->get_if_type_id() == $connect_to_if_type_id 
									&& !preg_match("/,".$eq_interface2->get_if_id().",/", $used_interfaces)
									&& !preg_match("/,".$eq_interface->get_if_id().",/", $used_interfaces)
									&& $eq_interface->get_eq_id() != $eq_interface2->get_eq_id()
									) {
										
										$used_interfaces	.= $eq_interface->get_if_id() . ",";
										$used_interfaces	.= $eq_interface2->get_if_id() . ",";
										$used_equipment		.= $eq_interface->get_eq_id() . ",";
										$used_equipment		.= $eq_interface2->get_eq_id() . ",";
										
										//now make the connection
										$this->create_interface_connection($eq_interface, $eq_interface2);
										
									}
								}
							}
						}
					}
				}
			}
			
			$i++;
		}

		//if there is only one piece of equipment that has interfaces, there will not be any other equipment to connect to.
		if ($count_eq_with_interfaces > 1) {
		
			foreach ($equipment_and_quote_maps['equipment'] as $equipment) {
					
				//the reason we need to check for interfaces is that there can be brackets / braces and other things that do not have interfaces
				//and cant have connections
				if ($equipment->get_interfaces() != null) {
						
					if (!preg_match("/,".$equipment->get_eq_id().",/", $used_equipment)) {

						//if we find a piece of equipment that has interfaces but no connections throw new
						throw new exception("eq_id =".$equipment->get_eq_id().", after connecting all interfaces according to service plans, we dident find a connection", 502);
					}
				}
			}
		}
		
		return $equipment_and_quote_maps;
	}
	
	
	private function map_service_point_to_equipment_interface($sp_interface_obj, $equipment_and_quote_maps)
	{
		//check that the service point interface only has one connection already. the connection into the service point
		//if dealing with adding equipment in the unit, make sure not to run this method
		
		$current_sp_connections	= $this->get_interface_connections($sp_interface_obj);
		
		if ($current_sp_connections != false && is_array($current_sp_connections)) {
			
			if (!isset($current_sp_connections['1'])) {

				foreach ($equipment_and_quote_maps['service_plan_quote_eq_type_map'] as $index => $service_plan_quote_eq_type_map) {
					
					$sql	= 	"SELECT sit.* FROM service_plan_quote_eq_type_mapping spqetm
								INNER JOIN homerun_group_eq_type_mapping hgetm ON hgetm.service_plan_eq_type_map_id=spqetm.service_plan_eq_type_map_id
								INNER JOIN static_if_types sit ON sit.static_if_type_id=hgetm.static_if_type_id
								WHERE spqetm.service_plan_quote_eq_type_map_id='".$service_plan_quote_eq_type_map->get_service_plan_quote_eq_type_map_id()."'
								";
					
					$connect_to_if_static_type_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
					
					if (isset($connect_to_if_static_type_id['static_if_type_id'])) {
						
						$sql2	= 	"SELECT if_id FROM interfaces 
									WHERE eq_id='".$equipment_and_quote_maps['equipment'][$index]->get_eq_id()."'
									AND if_type_id='".$connect_to_if_static_type_id['if_type_id']."'
									AND if_index='".$connect_to_if_static_type_id['if_index_number']."'
									";
						
						$if_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql2);

						if (!isset($if_id['if_id'])) {
							throw new exception("no if_id was found that matched the requirement for connecting to the service point, you have a wrong mapping in the homerun_group_eq_type_mapping table for service_plan_quote_eq_type_map_id: ".$service_plan_quote_eq_type_map->get_service_plan_quote_eq_type_map_id()." most likely the static interface does not belong to this equipment type", 503);
						}
						
						$sp_faceing_if_obj	= new Thelist_Model_equipmentinterface($if_id['if_id']);
						
						//now make the connection
						$this->create_interface_connection($sp_interface_obj, $sp_faceing_if_obj);
						
						//return the interface that was used
						return $sp_faceing_if_obj;
					} 	
				}
				
				throw new exception('no interface an any of the equipment provided is marked as facing the service point', 503);
		
			} else {
				
				throw new exception('the provided service point interface is connected to more than a single interface', 505);

			}
			
		} else {
			
			//using same exception in $this->connect_equipment_in_task_to_service_point
			throw new exception('the provided service point interface is not connected to anything', 504);
			
		}
	}

}
?>