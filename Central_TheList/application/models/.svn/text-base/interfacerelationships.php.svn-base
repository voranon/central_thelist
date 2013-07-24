<?php

//by martin
//exception codes 1500-1599

class thelist_model_interfacerelationships
{
		
	private $database;
	private $logs;
	private $_time;
	
	public function __construct()
	{

		
		$this->logs					= Zend_Registry::get('logs');
		
		$this->_time				= Zend_Registry::get('time');
		
	}
	
	
	public function remove_interface_relationship($master_interface, $slave_interface)
	{
	
		if (is_object($master_interface) && is_object($slave_interface)) {
				
			$sql	= 	"SELECT if_relationship_id FROM interface_relationships
						WHERE (if_id_master='".$master_interface->get_if_id()."' AND if_id_slave='".$slave_interface->get_if_id()."') 
						";
				
			$if_relationships = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			if (isset($if_relationships['0'])) {
				
				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);
	
				foreach($if_relationships as $if_relationship) {
					Zend_Registry::get('database')->delete_single_row($if_relationship['if_relationship_id'], 'interface_relationships', $class, $method);
				}
			}	
			
		} else {
			throw new exception('you must provide interface objects', 1503);
		}
	}
	

	public function create_interface_relationship($master_interface, $slave_interface)
	{
		if (is_object($master_interface) && is_object($slave_interface)) {
			
			$already_exist	= $this->interface_relationship_exist($master_interface, $slave_interface);
			
			if (!$already_exist) {
				
				$data = array(
				
						'if_id_master'   			=>  $master_interface->get_if_id(),
						'if_id_slave' 				=>  $slave_interface->get_if_id(),
				
				);

				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);
				
				Zend_Registry::get('database')->insert_single_row('interface_relationships',$data,$class,$method);
				
				//update the objects
				$master_interface->add_master_relationship($slave_interface);
				$slave_interface->add_slave_relationship($master_interface);
				
			}
			
		} else {
			
			throw new exception('you must provide interface objects', 1502);
			
		}
		
		//return the interfaces
		return array('master' => $master_interface, 'slave' => $slave_interface);
	}
	
	public function interface_relationship_exist($master_interface, $slave_interface)
	{
		$sql	= 	"SELECT if_relationship_id FROM interface_relationships
					WHERE (if_id_master='".$master_interface->get_if_id()."' AND if_id_slave='".$slave_interface->get_if_id()."') 
					";
		
		
		$if_relationships = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		if (isset($if_relationships['1'])) {
			
			throw new exception('these 2 interfaces have more than a single relationship with each other', 1501);
			
		} elseif (isset($if_relationships['0'])) {
			
			return true;
			
		} else {
			
			return false;
			
		}
	}
	
	public function get_interface_relationships($interface_obj)
	{
		$sql3 = 	"SELECT CASE
					WHEN if_id_master='".$interface_obj->get_if_id()."' THEN if_id_slave
					ELSE if_id_master
					END AS if_id
					FROM interface_relationships
					WHERE (if_id_master='".$interface_obj->get_if_id()."' OR if_id_slave='".$interface_obj->get_if_id()."')
					";
	
		$relationships = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql3);

		if (isset($relationships['0'])) {
			
			foreach($relationships as $relationship) {
				$if_relationships[]		= new Thelist_Model_equipmentinterface($relationship['if_id']);
			}
			
			return $if_relationships;
		} else {
			return false;
		}	
	}
}
?>