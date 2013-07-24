<?php

class thelist_model_equipmenttypegroup
{
		
	private $database;
	private $logs;
	private $_time;
	
	private $_service_plan_eq_type_map_id=null;
	private $_eq_type_group_id;
	private $_eq_type_group_name;
	private $_eq_type_group_short_description;
	private $_eq_types;

	public function __construct($eq_type_group_id,$service_plan_eq_type_map_id=null)
	{	
		$this->_service_plan_eq_type_map_id 	= $service_plan_eq_type_map_id;
		$this->_eq_type_group_id		    	= $eq_type_group_id;
		

		$this->logs								= Zend_Registry::get('logs');
		$this->_time							= Zend_Registry::get('time');
				
		$sql =	"SELECT * FROM equipment_type_groups
				WHERE eq_type_group_id='".$this->_eq_type_group_id."'
				";
		
		$eq_type_group = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		$this->_eq_type_group_name					= $eq_type_group['eq_type_group_name'];
		$this->_eq_type_group_short_description		= $eq_type_group['eq_type_group_short_description'];

		$sql2 =	"SELECT * FROM eq_type_group_mapping
				WHERE eq_type_group_id='".$this->_eq_type_group_id."'
				";
		
		$equipment_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
		
		if (isset($equipment_types['0'])) {
			foreach($equipment_types as $equipment_type){
					
				$this->_eq_types[$equipment_type['eq_type_id']] = new Thelist_Model_equipmenttype($equipment_type['eq_type_id']);
			
			}
		}
	}
	
	public function get_eq_type_group_id()
	{
		return $this->_eq_type_group_id;
	}
	
	public function get_service_plan_eq_type_map_id()
	{
		return $this->_service_plan_eq_type_map_id;	
	} 
	
	public function get_eq_type_group_name()
	{
		return $this->_eq_type_group_name;
	}
	
	public function get_eq_type_group_short_description()
	{
		return $this->_eq_type_group_short_description;
	}
	
	public function get_eq_types()
	{
		return $this->_eq_types;
	}
	public function get_eq_type($eq_type_id)
	{
		return $this->_eq_types[$eq_type_id];
	}
	
	public function toArray()
	{
		$obj_content	= print_r($this, 1);
		$class_name		= get_class($this);
	
		//get all private variable names
		preg_match_all("/\[(.*):".$class_name.":private\]/", $obj_content, $matches);
	
		if (isset($matches['0']['0'])) {
			 
			$complete['private_variable_names'] = $matches['1'];
			 
			foreach ($matches['1'] as $index => $private_variable_name) {
	
				$one_variable	= $this->$private_variable_name;
				 
				if (is_array($one_variable)) {
					$complete['private_variable_type'][$index] = 'array';
				} elseif (is_object($one_variable)) {
					$complete['private_variable_type'][$index] = 'object';
				} else {
					$complete['private_variable_type'][$index] = 'string';
				}
			}
	
			foreach ($complete['private_variable_names'] as $private_index => $private_variable) {
					
				if ($complete['private_variable_type'][$private_index] == 'object') {
	
					if (method_exists($this->$private_variable, 'toArray')) {
						$return_array[$private_variable] = $this->$private_variable->toArray();
					} else {
						$return_array[$private_variable] = 'CLASS IS MISSING toArray METHOD';
					}
	
				} elseif ($complete['private_variable_type'][$private_index] == 'string') {
	
					$return_array[$private_variable] = $this->$private_variable;
	
				} elseif ($complete['private_variable_type'][$private_index] == 'array') {
						
					$array_tools	= new Thelist_Utility_arraytools();
					$return_array[$private_variable] = $array_tools->convert_mixed_array_to_strings($this->$private_variable);
	
				}
			}
		}
	
		return $return_array;
	}
	
}
?>