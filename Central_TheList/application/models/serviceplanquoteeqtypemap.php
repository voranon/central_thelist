<?php

//exception codes 700 - 799

class thelist_model_serviceplanquoteeqtypemap{
	

	private $database;
	private $logs;
	private $user_session;
	private $_time;
	
	private $_service_plan_quote_eq_type_map_id;
	private $_service_plan_eq_type_map_id;
	private $_service_plan_quote_map_id;
	private $_service_plan_quote_eq_type_actual_mrc;
	private $_service_plan_quote_eq_type_actual_nrc;
	private $_service_plan_quote_eq_type_actual_mrc_term;
	private $_service_plan_eq_type_map;
	
	private $_active_mapped_equipment=null;
	
	public function __construct($service_plan_quote_eq_type_map_id)
	{
		
		
		$this->_service_plan_quote_eq_type_map_id			= $service_plan_quote_eq_type_map_id;	

		$this->logs											= Zend_Registry::get('logs');
		$this->user_session									= new Zend_Session_Namespace('userinfo');
		$this->_time										= Zend_Registry::get('time');
		
		$sql=	"SELECT * FROM service_plan_quote_eq_type_mapping
				WHERE service_plan_quote_eq_type_map_id='".$this->_service_plan_quote_eq_type_map_id."'
				";
		
		$service_plan_quote_eq_type_map = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		$this->_service_plan_eq_type_map_id							= $service_plan_quote_eq_type_map['service_plan_eq_type_map_id'];
		$this->_service_plan_quote_map_id							= $service_plan_quote_eq_type_map['service_plan_quote_map_id'];
		$this->_service_plan_quote_eq_type_actual_mrc				= $service_plan_quote_eq_type_map['service_plan_quote_eq_type_actual_mrc'];
		$this->_service_plan_quote_eq_type_actual_nrc				= $service_plan_quote_eq_type_map['service_plan_quote_eq_type_actual_nrc'];
		$this->_service_plan_quote_eq_type_actual_mrc_term			= $service_plan_quote_eq_type_map['service_plan_quote_eq_type_actual_mrc_term'];
		$this->_service_plan_eq_type_map							= new Thelist_Model_serviceplaneqtypemap($this->_service_plan_eq_type_map_id);
	}
	
	
	public function get_service_plan_quote_eq_type_map_id(){
		return $this->_service_plan_quote_eq_type_map_id;
	}
	public function get_service_plan_eq_type_map_id(){
		return $this->_service_plan_eq_type_map_id;
	}
	public function get_service_plan_quote_map_id(){
		return $this->_service_plan_quote_map_id;
	}
	public function get_service_plan_quote_eq_type_actual_mrc(){
		return $this->_service_plan_quote_eq_type_actual_mrc;
	}
	public function get_service_plan_quote_eq_type_actual_nrc(){
		return $this->_service_plan_quote_eq_type_actual_nrc;
	}
	public function get_service_plan_quote_eq_type_actual_mrc_term(){
		return $this->_service_plan_quote_eq_type_actual_mrc_term;
	}
	public function get_service_plan_eq_type_map(){
		return $this->_service_plan_eq_type_map;
	}
	
	public function get_active_mapped_equipment($refresh=false)
	{
		if ($this->_active_mapped_equipment == null || $refresh != false) {
			
			$sql	= 	"SELECT em.eq_id FROM sales_quote_eq_type_map_equipment_mapping sqetmem
						INNER JOIN equipment_mapping em ON em.equipment_map_id=sqetmem.equipment_map_id
						WHERE service_plan_quote_eq_type_map_id='".$this->_service_plan_quote_eq_type_map_id."'
						AND em.eq_map_activated IS NOT NULL
						AND eq_map_deactivated IS NULL
						";

			$eq_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
			if (isset($eq_id['eq_id'])) {
				$this->_active_mapped_equipment = new Thelist_Model_equipments($eq_id);
			}
		}
		
		return $this->_active_mapped_equipment;
	}
	
	public function map_equipment_map($equipment_map_id, $remap_equipment, $remap_sales_quote)
	{

		$trace 		= debug_backtrace();
		$method 	= $trace[0]["function"];
		$class		= get_class($this);
		
		
		$sql2	= 	"SELECT sales_quote_eq_type_map_equipment_map_id FROM sales_quote_eq_type_map_equipment_mapping
					WHERE equipment_map_id='".$equipment_map_id."'
					";
			
		$eqmap_old_mappings = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
		
		$sql	= 	"SELECT sales_quote_eq_type_map_equipment_map_id FROM sales_quote_eq_type_map_equipment_mapping
					WHERE service_plan_quote_eq_type_map_id='".$this->_service_plan_quote_eq_type_map_id."'
					";
		
		$speqtmap_old_mappings = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		//service plan already mapped or equipment already mapped
		if ((isset($speqtmap_old_mappings['sales_quote_eq_type_map_equipment_map_id']) && $remap_sales_quote == false) || (isset($eqmap_old_mappings['sales_quote_eq_type_map_equipment_map_id']) && $remap_equipment == false)) {
				
			throw new exception('trying to map equipment to service plan quote eq type, but service plan quote or equipment already set and no remap requested ', 700);
				
		} 
				
		
		//if we dont fail the remap validation, we delete old and map the equipment new 
		if (isset($eqmap_old_mappings['sales_quote_eq_type_map_equipment_map_id']) && $remap_equipment == true) {
			
			Zend_Registry::get('database')->delete_single_row($eqmap_old_mappings, 'sales_quote_eq_type_map_equipment_mapping',$class,$method);
			
		} 
		
		if (isset($speqtmap_old_mappings['sales_quote_eq_type_map_equipment_map_id']) && $remap_sales_quote == true) {
		
			Zend_Registry::get('database')->delete_single_row($speqtmap_old_mappings, 'sales_quote_eq_type_map_equipment_mapping',$class,$method);
		
		}
		
		//map new
		$data = array(
			
				'service_plan_quote_eq_type_map_id'   	=> $this->_service_plan_quote_eq_type_map_id,
				'equipment_map_id'						=> $equipment_map_id,
										
		);

		return Zend_Registry::get('database')->insert_single_row('sales_quote_eq_type_map_equipment_mapping',$data,$class,$method);

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
	
					$array_tools						= new Thelist_Utility_arraytools();
					$return_array[$private_variable] 	= $array_tools->convert_mixed_array_to_strings($this->$private_variable);
	
				}
			}
		}
	
		return $return_array;
	}

}
?>