<?php

class thelist_model_serviceplanoption{
	
	private $database;
	private $log;
	private $user_session;
	
	private $_service_plan_option_id;
	private $_service_plan_option_type;
	private $_service_plan_option_name;
	private $_service_plan_option_value_1;
	private $_service_plan_option_value_2;
	private $_service_plan_option_value_3;
	private $_short_description;
	private $_description;
	private $_activate;
	private $_deactivate;

	public function __construct($service_plan_option_id)
	{
		
		$this->_service_plan_option_id				=	$service_plan_option_id;
		
		$this->log									= 	Zend_Registry::get('logs');
		$this->user_session 						= 	new Zend_Session_Namespace('userinfo');

		
		$sql="SELECT * FROM service_plan_options
				WHERE service_plan_option_id='".$this->_service_plan_option_id."'
				";
		
		$service_plan_option = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

		//set sp option variables 
		$this->_service_plan_option_type			=	$service_plan_option['service_plan_option_type'];
		$this->_service_plan_option_name			=	$service_plan_option['service_plan_option_name'];
		$this->_service_plan_option_value_1			=	$service_plan_option['service_plan_option_value_1'];
		$this->_service_plan_option_value_2			=	$service_plan_option['service_plan_option_value_2'];
		$this->_service_plan_option_value_3			=	$service_plan_option['service_plan_option_value_3'];
		$this->_short_description					=	$service_plan_option['short_description'];
		$this->_description							=	$service_plan_option['description'];
		$this->_activate							=	$service_plan_option['activate'];
		$this->_deactivate							=	$service_plan_option['deactivate'];

				
	}
	
	public function get_service_plan_option_id(){
		return $this->_service_plan_option_id;
	}	
	public function get_service_plan_option_type()
	{
		return $this->_service_plan_option_type;
	}
	public function get_activate(){
		return $this->_activate;
	}
	public function get_deactivate(){
		return $this->_deactivate;
	}
	public function get_service_plan_option_additional_install_time(){
		return $this->_service_plan_option_additional_install_time;
	}
	public function get_service_plan_option_default_mrc(){
		return $this->_service_plan_option_default_mrc;
	}
	public function get_service_plan_option_default_nrc(){
		return $this->_service_plan_option_default_nrc;
	}
	public function get_service_plan_option_default_mrc_term(){
		return $this->_service_plan_option_default_mrc_term;
	}
	public function get_service_plan_option_map_master_id(){
		return $this->_service_plan_option_map_master_id;
	}
	public function get_service_plan_option_map_id(){
		return $this->_service_plan_option_map_id;
	}
	public function get_service_plan_option_group_id(){
		return $this->_service_plan_option_group_id;
	}
	public function get_service_plan_option_name(){
		return $this->_service_plan_option_name;
	}
	public function get_(){
		return $this->_;
	}
	public function get_service_plan_option_value_1()
	{
		return $this->_service_plan_option_value_1;
	}
	public function get_service_plan_option_value_2()
	{
		return $this->_service_plan_option_value_2;
	}
	public function get_service_plan_option_value_3()
	{
		return $this->_service_plan_option_value_3;
	}
	public function get_short_description()
	{
		return $this->_short_description;
	}
	public function get_description(){
		return $this->_description;
	}

	public function get_sp_option_type_name()
	{
		$sp_type_name	=	Zend_Registry::get('database')->get_items()->fetchRow('item_id='.$this->_service_plan_option_type);
		
		return $sp_type_name['item_value'];
		
	}
	
	public function get_sp_option_group_detail()
	{
		$service_plan_option_group_detail	=	Zend_Registry::get('database')->get_service_plan_option_groups()->fetchRow('service_plan_option_group_id='.$this->_service_plan_option_group_id);

		$spo_detail = array('required_amount' => $service_plan_option_group_detail['service_plan_option_required_quantity'], 'max_amount' => $service_plan_option_group_detail['service_plan_option_max_quantity'], 'name' => $service_plan_option_group_detail['service_plan_option_group_name']);

		return $spo_detail;
	
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