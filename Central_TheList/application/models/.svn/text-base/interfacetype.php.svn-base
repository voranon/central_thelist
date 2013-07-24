<?php

//created by Martin

//exception codes 17600-17699 

class thelist_model_interfacetype
{
	
	private $_if_type_id;
	private $_if_type_name;
	private $_if_type;
	private $_if_type_features=null;
	private $_if_type_conf_mappings=null;
	private $_if_type_default_datasources=null;
	private $_if_configuration_type_maps=null;
	
	
	//common variables
	private $log;
	private $user_session;
	private $database;
	
	public function __construct($if_type_id){
		$this->_if_type_id		= $if_type_id;	

		$this->log				= Zend_Registry::get('logs');
		$this->user_session 	= new Zend_Session_Namespace('userinfo');
		
		$sql="SELECT * FROM interface_types
				WHERE if_type_id='".$this->_if_type_id."'
				";
		
		$if_type = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
	
		$this->_if_type_name			=$if_type['if_type_name'];
		$this->_if_type					=$if_type['if_type'];
		
		//static ifs
		$sql2 = "SELECT * FROM interface_type_feature_mapping
				WHERE if_type_id='".$this->_if_type_id."'
				";

		$if_type_features = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);

		if (isset($if_type_features['0'])) {
		
			foreach($if_type_features as $if_type_feature){
					
				$this->_if_type_features[$if_type_feature['if_type_feature_map_id']] = new Thelist_Model_equipmentinterfacefeature($if_type_feature['if_feature_id']);
				$this->_if_type_features[$if_type_feature['if_type_feature_map_id']]->set_if_type_feature_map($if_type_feature['if_type_feature_map_id']);
					
			}
		}
		
		//default datasources to be created
		$sql3 = "SELECT * FROM monitoring_interface_type_default_rra_mapping
						WHERE if_type_id='".$this->_if_type_id."'
						";
		
		$this->_if_type_default_datasources = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql3);
		
	}
	
	public function set_if_type_name($new_name)
	{
		
		$trace 		= debug_backtrace();
		$method 	= $trace[0]["function"];
		$class		= get_class($this);
		
		Zend_Registry::get('database')->set_single_attribute($this->_if_type_id, 'interface_types', 'if_type_name', $new_name, $class, $method);
	}
	public function set_if_type_type($new_if_type_type)
	{
	
		$trace 		= debug_backtrace();
		$method 	= $trace[0]["function"];
		$class		= get_class($this);
	
		Zend_Registry::get('database')->set_single_attribute($this->_if_type_id, 'interface_types', 'if_type', $new_if_type_type, $class, $method);
	}
	
	
	public function get_if_type_id()
	{
		return $this->_if_type_id;
	}
	
	public function get_if_configuration_type_maps()
	{
		if ($this->_if_configuration_type_maps == null) {
			
			$sql = "SELECT * FROM interface_type_configurations
					WHERE if_type_id='".$this->_if_type_id."'
					";
			
			$if_type_conf_ids = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);

			if (isset($if_type_conf_ids['0'])) {
				
				foreach($if_type_conf_ids as $if_type_conf_id) {
					$this->_if_configuration_type_maps[$if_type_conf_id['if_conf_id']] = new Thelist_Model_interfaceconfiguration($if_type_conf_id['if_conf_id']);
				}
			}
		}
		
		return $this->_if_configuration_type_maps;
	}
	
	
	
	
	public function add_if_configuration_type_map($if_conf_id)
	{
		if ($this->_if_configuration_type_maps == null) {
			$this->get_if_configuration_type_maps();
		}
		
		if ($this->_if_configuration_type_maps == null) {
			
			foreach ($this->_if_configuration_type_maps as $current_config) {
				
				if ($current_config->get_if_conf_id() == $if_conf_id) {
					
					//already exist
					$current_config->set_if_conf_max_maps($this->_if_type_id, $if_conf_max_maps);
					return $current_config;
				}
			}
		}
		
		//if config does not already exist, we create it
		$if_conf_obj = new Thelist_Model_interfaceconfiguration($if_conf_id);
		
		$trace 		= debug_backtrace();
		$method 	= $trace[0]["function"];
		$class		= get_class($this);
		
		$data = array(
		
			'if_conf_id'				=>  $if_conf_id,
			'if_type_id'				=>  $this->_if_type_id,
			'if_conf_max_maps' 			=>	1,  
			'if_conf_default_map' 		=>	0,
		
		);
		
		//update start value
		$new_interface_type_configuration_id = Zend_Registry::get('database')->insert_single_row('interface_type_configurations',$data, $class,$method);

		$this->_if_configuration_type_maps[$if_conf_id] = new Thelist_Model_interfaceconfiguration($new_interface_type_configuration_id);
		
		return $this->_if_configuration_type_maps[$if_conf_id];
	}
	
	public function remove_if_configuration_type_map($if_conf_id)
	{
		if ($this->_if_configuration_type_maps == null) {
			$this->get_if_configuration_type_maps();
		}
	
		if ($this->_if_configuration_type_maps != null) {
				
			foreach ($this->_if_configuration_type_maps as $current_config) {
	
				if ($current_config->get_if_conf_id() == $if_conf_id) {

					//we found the one we want to remove
					//first we need to get rid of all allowed values
					
					$sql = "SELECT ifc.interface_type_configuration_id ,itacv.if_type_allowed_config_value_id FROM interface_type_configurations ifc
							INNER JOIN interface_type_allowed_config_values itacv ON itacv.interface_type_configuration_id=ifc.interface_type_configuration_id
							WHERE ifc.if_type_id='".$this->_if_type_id."'
							AND ifc.if_conf_id='".$if_conf_id."'
							";
				
					$if_type_allowed_config_value_ids = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
						
					if (isset($if_type_allowed_config_value_ids['0'])) {
						
						foreach ($if_type_allowed_config_value_ids as $if_type_allowed_config_value_id) {

							//remove allowed values
							$current_config->remove_if_type_config_allowed_value($if_type_allowed_config_value_id['interface_type_configuration_id'], $if_type_allowed_config_value_id['if_type_allowed_config_value_id']);
						}
					}
					
					$sql2 = "SELECT interface_type_configuration_id FROM interface_type_configurations
							WHERE if_type_id='".$this->_if_type_id."'
							AND if_conf_id='".$if_conf_id."'
							";
					
					$interface_type_configuration_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
					
					//now all allowed values are deleted, then delete the config map itself
					$trace 		= debug_backtrace();
					$method 	= $trace[0]["function"];
					$class		= get_class($this);
					
					Zend_Registry::get('database')->delete_single_row($interface_type_configuration_id, 'interface_type_configurations', $class, $method);

					//remove it from this class
					unset($this->_if_configuration_type_maps[$if_conf_id]);
					//we are done, return so we dont get to the exception
					return;
				}
			}
		}
		
		//if we make it to here we did find it and delete it
		throw new exception("you are trying to remove if_conf_id: ".$if_conf_id." from if_type_id: ".$this->_if_type_id.", but that config is not mapped to the interface type", 17600);
		
	}
	
	public function get_if_type_default_datasources()
	{
		return $this->_if_type_default_datasources;
	}
	public function get_if_type_name()
	{
		return $this->_if_type_name;
	}
	public function get_if_type()
	{
		return $this->_if_type;
	}
	public function get_if_type_mapped_features()
	{
		return $this->_if_type_features;
	}
	public function get_if_type_mapped_feature($if_type_feature_map_id)
	{
		return $this->_if_type_features[$if_type_feature_map_id];
	}
	public function map_if_type_feature($if_feature_id, $if_type_feature_value=null)
	{

		if ($this->_if_type_features != null) {
			foreach ($this->_if_type_features as $if_type_feature) {
	
				if ($if_type_feature->get_if_feature_id() == $if_feature_id) {
						
					throw new exception('feature already mapped to this interface');
				
				}
			}
		}

		//a blank value is not accepted, clean it up
		if ($if_type_feature_value == '') {
			$if_type_feature_value = null;
			}
		
			//if not already mapped then create and return the object
			$data = array(
				
								'if_type_id'						=> $this->_if_type_id,
								'if_feature_id'						=> $if_feature_id,
								'if_type_feature_value'				=> $if_type_feature_value,
			
				
			);
				
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
				
			$new_if_type_feature_map_id = Zend_Registry::get('database')->insert_single_row('interface_type_feature_mapping',$data,$class,$method);

			
			$this->_if_type_features[$new_if_type_feature_map_id] = new Thelist_Model_equipmentinterfacefeature($if_feature_id);
			$this->_if_type_features[$new_if_type_feature_map_id]->set_if_type_feature_map($new_if_type_feature_map_id);

			return $this->_if_type_features[$new_if_type_feature_map_id];
	}
	
	public function remove_map_if_type_feature($if_type_feature_map_id)
	{
	
		$trace 		= debug_backtrace();
		$method 	= $trace[0]["function"];
		$class		= get_class($this);
	
		Zend_Registry::get('database')->delete_single_row($if_type_feature_map_id, 'interface_type_feature_mapping',$class,$method);
	
		unset ($this->_if_type_features[$if_type_feature_map_id]);

	}
	
// 	public function get_conf_mappings($eq_type_software_map_id)
// 	{
		//reset the variable on each run to avoid overlap
// 		$this->_if_type_conf_mappings = null;
		//type conf mappings
// 		$sql3 = 	"SELECT * FROM configuration_interface_type_mapping
// 					WHERE if_type_id='".$this->_if_type_id."'
// 					AND eq_type_software_map_id='".$eq_type_software_map_id."'
// 					";
		
// 		$configuration_interface_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql3);
		
// 		if (isset($configuration_interface_types['0'])) {
		
// 			foreach($configuration_interface_types as $configuration_interface_type){
					
// 				$this->_if_type_conf_mappings[$configuration_interface_type['conf_if_type_map_id']] = new Thelist_Model_configurationinterfacetype($configuration_interface_type['conf_if_type_map_id']);
					
// 			}
			
// 			return $this->_if_type_conf_mappings;
		
// 		} else {
			
// 			return false;
			
// 		}

// 	}

}
?>