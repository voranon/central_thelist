<?php



class thelist_model_serviceplanquoteoptionmap{
	

	private $database;
	private $logs;
	private $user_session;
	private $_time;
	
	private $_service_plan_quote_option_map_id;
	private $_service_plan_option_map_id;
	private $_service_plan_quote_map_id;
	private $_service_plan_quote_option_actual_mrc;
	private $_service_plan_quote_option_actual_nrc;
	private $_service_plan_quote_option_actual_mrc_term;
	private $_service_plan_option_map;
	
	public function __construct($service_plan_quote_option_map_id)
	{
		$this->_service_plan_quote_option_map_id			= $service_plan_quote_option_map_id;	

		$this->logs											= Zend_Registry::get('logs');
		$this->user_session									= new Zend_Session_Namespace('userinfo');
		$this->_time										= Zend_Registry::get('time');
		
		$sql=	"SELECT * FROM service_plan_quote_option_mapping
				WHERE service_plan_quote_option_map_id='".$this->_service_plan_quote_option_map_id."'
				";
		
		$service_plan_quote_option_map = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		$this->_service_plan_option_map_id							= $service_plan_quote_option_map['service_plan_option_map_id'];
		$this->_service_plan_quote_map_id							= $service_plan_quote_option_map['service_plan_quote_map_id'];
		$this->_service_plan_quote_option_actual_mrc				= $service_plan_quote_option_map['service_plan_quote_option_actual_mrc'];
		$this->_service_plan_quote_option_actual_nrc				= $service_plan_quote_option_map['service_plan_quote_option_actual_nrc'];
		$this->_service_plan_quote_option_actual_mrc_term			= $service_plan_quote_option_map['service_plan_quote_option_actual_mrc_term'];
	}
	
	public function get_service_plan_quote_option_map_id()
	{
		return $this->_service_plan_quote_option_map_id;
	}
	public function get_service_plan_option_map_id()
	{
		return $this->_service_plan_option_map_id;
	}
	public function get_service_plan_quote_map_id()
	{
		return $this->_service_plan_quote_map_id;
	}
	public function get_service_plan_quote_option_actual_mrc()
	{
		return $this->_service_plan_quote_option_actual_mrc;
	}
	public function get_service_plan_quote_option_actual_nrc()
	{
		return $this->_service_plan_quote_option_actual_nrc;
	}
	public function get_service_plan_quote_option_actual_mrc_term()
	{
		return $this->_service_plan_quote_option_actual_mrc_term;
	}
	public function get_service_plan_option_map()
	{
		if ($this->_service_plan_option_map == null) {
			$this->_service_plan_option_map	= new Thelist_Model_serviceplanoptionmap($this->_service_plan_option_map_id);
		}
		return $this->_service_plan_option_map;
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