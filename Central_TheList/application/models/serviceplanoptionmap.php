<?php

class thelist_model_serviceplanoptionmap{
	

	private $database;
	private $logs;
	private $user_session;
	private $_time;
	
	private $_service_plan_option_map_id;
	private $_service_plan_option_map_master_id;
	private $_service_plan_id;
	private $_service_plan_option_id;
	private $_service_plan_option_group_id;
	private $_service_plan_option_additional_install_time;
	private $_service_plan_option_default_mrc;
	private $_service_plan_option_default_nrc;
	private $_service_plan_option_default_mrc_term;
	private $_service_plan_option=null;
	
	private $_service_plan_option_group;
	private $_service_plan_option_group_name;

	public function __construct($service_plan_option_map_id)
	{
		$this->_service_plan_option_map_id				= $service_plan_option_map_id;	

		$this->logs										= Zend_Registry::get('logs');
		$this->user_session								= new Zend_Session_Namespace('userinfo');
		$this->_time									= Zend_Registry::get('time');
		
		$sql=	"SELECT * FROM service_plan_option_mapping
				WHERE service_plan_option_map_id='".$this->_service_plan_option_map_id."'
				";
		
		$service_plan_option_map = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		$this->_service_plan_option_map_master_id					= $service_plan_option_map['service_plan_option_map_master_id'];
		$this->_service_plan_id										= $service_plan_option_map['service_plan_id'];
		$this->_service_plan_option_id								= $service_plan_option_map['service_plan_option_id'];
		$this->_service_plan_option_group_id						= $service_plan_option_map['service_plan_option_group_id'];
		$this->_service_plan_option_additional_install_time			= $service_plan_option_map['service_plan_option_additional_install_time'];
		$this->_service_plan_option_default_mrc						= $service_plan_option_map['service_plan_option_default_mrc'];
		$this->_service_plan_option_default_nrc						= $service_plan_option_map['service_plan_option_default_nrc'];
		$this->_service_plan_option_default_mrc_term				= $service_plan_option_map['service_plan_option_default_mrc_term'];
	}
	
	public function get_service_plan_option_map_id()
	{
		return $this->_service_plan_option_map_id;
	}
	public function get_service_plan_option_map_master_id()
	{
		return $this->_service_plan_option_map_master_id;
	}
	public function get_service_plan_id()
	{
		return $this->_service_plan_id;
	}
	public function get_service_plan_option_id()
	{
		return $this->_service_plan_option_id;
	}
	public function get_service_plan_option_group_id()
	{
		return $this->_service_plan_option_group_id;
	}
	public function get_service_plan_option_additional_install_time()
	{
		return $this->_service_plan_option_additional_install_time;
	}
	public function get_service_plan_option_default_mrc()
	{
		return $this->_service_plan_option_default_mrc;
	}
	public function get_service_plan_option_default_nrc()
	{
		return $this->_service_plan_option_default_nrc;
	}
	public function get_service_plan_option_default_mrc_term()
	{
		return $this->_service_plan_option_default_mrc_term;
	}
	public function get_service_plan_option()
	{
		if ($this->_service_plan_option == null) {
			
			$this->_service_plan_option = new Thelist_Model_serviceplanoption($this->_service_plan_option_id);
		}

		return $this->_service_plan_option;
	}
	
	public function get_service_plan_option_group()
	{
		if ($this->_service_plan_option_group == null) {
			$this->_service_plan_option_group = new Thelist_Model_serviceplanoptiongroup($this->_service_plan_option_group_id);
		}
		
		return $this->_service_plan_option_group;
	}
	
	public function get_service_plan_option_group_name()
	{
		if ($this->_service_plan_option_group_name == null) {
			
			$this->get_service_plan_option_group();
			
			$this->_service_plan_option_group_name  = $this->_service_plan_option_group->get_service_plan_option_group_name();
		}
		
		return $this->_service_plan_option_group_name; 
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