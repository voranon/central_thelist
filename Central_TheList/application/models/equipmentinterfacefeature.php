<?php 


class thelist_model_equipmentinterfacefeature
{
	private $_if_feature_id;
	private $_if_feature_name;
	private $_if_feature_type;
	private $_if_feature_desc;
	
	private $_mapped_if_feature_value=null;
	private $_mapped_if_feature_map_id=null;
	
	// non 8/28/2012
	private $_serviceplan_if_feature_map_value=null;
	private $_sp_sp_if_feat_map_id=null;

	public function __construct($if_feature_id)
	{
		$this->_if_feature_id = $if_feature_id;
		
		

		
		$if_feature = Zend_Registry::get('database')->get_interface_features()->fetchRow('if_feature_id='.$this->_if_feature_id);

		$this->_if_feature_name					= $if_feature['if_feature_name'];
		$this->_if_feature_type					= $if_feature['if_feature_type'];
		$this->_if_feature_desc					= $if_feature['if_feature_desc'];
	}
	public function get_id()
	{
		return $this->_if_feature_id;
	}
	
	public function get_if_feature_id()
	{
		return $this->_if_feature_id;
	}
	public function get_if_feature_name()
	{
		return $this->_if_feature_name;
	}
	public function get_if_feature_desc()
	{
		return $this->_if_feature_desc;
	}
	public function get_if_feature_type()
	{
		return $this->_if_feature_type;
	}
	public function get_mapped_if_feature_value()
	{
		return $this->_mapped_if_feature_value;
	}
	public function get_mapped_if_feature_map_id()
	{
		return $this->_mapped_if_feature_map_id;
	}
	
	
	public function set_serviceplan_if_feature_map_value($_serviceplan_if_feature_map_value){
		$this->_serviceplan_if_feature_map_value=$_serviceplan_if_feature_map_value;
	}
	public function get_serviceplan_if_feature_map_value(){
		return $this->_serviceplan_if_feature_map_value;
	}
	
	public function set_sp_sp_if_feat_map_id($_sp_sp_if_feat_map_id){
		$this->_sp_sp_if_feat_map_id = $_sp_sp_if_feat_map_id;
	}
	public function get_sp_sp_if_feat_map_id(){
		return $this->_sp_sp_if_feat_map_id;
	}
	
	
	
	public function set_mapped_if_feature_value($new_value)
	{
		if ($this->_mapped_if_feature_map_id != null) {
			
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
			
			Zend_Registry::get('database')->set_single_attribute($this->_mapped_if_feature_map_id, 'interface_feature_mapping', 'if_feature_value', $new_value, $class, $method);
		}

	}
	
	public function set_if_feature_map($if_feature_map_id)
	{
	
		//when mapped to an interface
		$sql = 	"SELECT * FROM interface_feature_mapping
					WHERE if_feature_map_id='".$if_feature_map_id."'
					AND if_feature_id='".$this->_if_feature_id."'
					";
	
		$if_feature_map_detail= Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
	
		if ($if_feature_map_detail['if_feature_value'] != 'null') {
			
			$this->_mapped_if_feature_value = $if_feature_map_detail['if_feature_value'];
		}
		
		$this->_mapped_if_feature_map_id = $if_feature_map_detail['if_feature_map_id'];
	
	}
	public function set_if_type_feature_map($if_type_feature_map_id)
	{
		//when mapped to an interface type
		$sql = 	"SELECT * FROM interface_type_feature_mapping
						WHERE if_type_feature_map_id='".$if_type_feature_map_id."'
						AND if_feature_id='".$this->_if_feature_id."'
						";
	
		$if_type_feature_map_detail= Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

		if ($if_type_feature_map_detail['if_type_feature_value'] != 'null') {
				
			$this->_mapped_if_feature_value = $if_type_feature_map_detail['if_type_feature_value'];
		}
		
		$this->_mapped_if_feature_map_id = $if_type_feature_map_detail['if_type_feature_map_id'];
	
	}
	public function set_service_plan_quote_feature_value($value)
	{
		//this is the dynamic setting of the value, when dealing with service plans
		//this should not be written to the database
		$this->_mapped_if_feature_value = $value;
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