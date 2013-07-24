<?php

//require_once APPLICATION_PATH.'/models/monitoring_datasources.php';

//by martin

class thelist_model_devicefunction
{
			
	private $database;
	private $logs;
	private $_device_function_id;
	private $_device_function_name;
	private $_device_command_parameter_table_id;
	private $_device_function_desc;
	private $_device_function_maps;
	private $_device_function_command_maps=null;
	private $_monitoring_datasources=null;
				
	public function __construct($device_function_id)
	{

		$this->logs			= Zend_Registry::get('logs');
		
		$this->_device_function_id = $device_function_id;
		
		//get the device function from the database
		$sql = "SELECT * FROM device_functions 
				WHERE device_function_id='".$this->_device_function_id."'
				";
		
		$device_function = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		//set all the attributes of the device function
		
		$this->_device_function_name 				= $device_function['device_function_name'];
		$this->_device_command_parameter_table_id 	= $device_function['device_command_parameter_table_id'];
		$this->_device_function_desc 				= $device_function['device_function_desc'];


		$sql3 = 	"SELECT dfm.device_function_map_id, et.eq_manufacturer, et.eq_model_name, sp.software_package_manufacturer, sp.software_package_name, sp.software_package_architecture, software_package_version FROM device_function_mapping dfm
					INNER JOIN equipment_type_software_mapping etsm ON etsm.eq_type_software_map_id=dfm.eq_type_software_map_id
					INNER JOIN software_packages sp ON sp.software_package_id=etsm.software_package_id
					INNER JOIN equipment_types et ON et.eq_type_id=etsm.eq_type_id
					WHERE device_function_id='".$this->_device_function_id."'
					"; 
		
		$this->_device_function_maps = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql3);

		//all datasources
		$sql4=	"SELECT * FROM monitoring_data_sources
						WHERE device_function_id='".$this->_device_function_id."'
						";
		
		$monitoring_datasources  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql4);
		if (isset($monitoring_datasources['0'])) {
			foreach($monitoring_datasources as $monitoring_datasource){
				$this->_monitoring_datasources[$monitoring_datasource['monitoring_ds_id']] = new Thelist_Model_monitoringdatasource($monitoring_datasource['monitoring_ds_id']);
			}
		}
		
		
	}
	
	
	public function get_device_function_id(){
		return $this->_device_function_id;
	}
	public function get_device_function_name(){
		return $this->_device_function_name;
	}
	public function get_device_function_desc(){
		return $this->_device_function_desc;
	}
	public function get_device_function_maps(){
		return $this->_device_function_maps;
	}
	public function get_device_command_parameter_table_id(){
		return $this->_device_command_parameter_table_id;
	}
	public function get_monitoring_datasources(){
		return $this->_monitoring_datasources;
	}
	public function get_monitoring_datasource($monitoring_ds_id){
		return $this->_monitoring_datasources[$monitoring_ds_id];
	}
	
	
	
	
	public function set_device_function_name($device_function_name)
	{

		Zend_Registry::get('database')->set_single_attribute($this->_device_function_id, 'device_functions', 'device_function_name', $device_function_name,'devicefunction','set_device_function_name');
		$this->_device_function_name = $device_function_name;

	}
	
	public function set_device_function_desc($device_function_desc)
	{

		Zend_Registry::get('database')->set_single_attribute($this->_device_function_id, 'device_functions', 'device_function_desc', $device_function_desc,'devicefunction','set_device_function_desc');
		$this->_device_function_desc = $device_function_desc;

	}
	
	public function set_device_command_parameter_table_id($device_command_parameter_table_id)
	{
	
		Zend_Registry::get('database')->set_single_attribute($this->_device_function_id, 'device_functions', 'device_command_parameter_table_id', $device_command_parameter_table_id,'devicefunction','set_device_command_parameter_table_id');
		$this->_device_command_parameter_table_id = $device_command_parameter_table_id;

	}

	
	public function get_device_function_parameter_table_name(){
		
		$sql =	"SELECT table_name FROM device_command_parameter_tables
				WHERE device_command_parameter_table_id='".$this->_device_command_parameter_table_id."'
				";
		
		return Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
	}
		
	public function map_device_function_to_eq_type_software($eq_type_software_map_id)
	{
	//verify that this function is not already mapped to this eq_type - software combo.
		$sql =	"SELECT COUNT(device_function_map_id) FROM device_function_mapping
				WHERE eq_type_software_map_id='".$eq_type_software_map_id."'
				AND device_function_id='".$this->_device_function_id."'
				";
	
		$map_exists = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		if ($map_exists > 0) {
			//return id of existing row
			$sql2 = 	"SELECT device_function_map_id FROM device_function_mapping
						WHERE eq_type_software_map_id='".$eq_type_software_map_id."'
						AND device_function_id='".$this->_device_function_id."'
						LIMIT 1
						";
			
			return Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
			
			} else {
				//insert new row and return the id
				
				$data = array(
				
							'eq_type_software_map_id'		=>  $eq_type_software_map_id,
							'device_function_id'			=>  $this->_device_function_id,
				
				);
			
				return Zend_Registry::get('database')->insert_single_row('device_function_mapping',$data,'devicefunction','map_device_function_to_eq_type_software');
			
			}
	}
	
	public function get_eq_type_id_of_function_map($device_function_map_id){
		
		$sql =		"SELECT etsm.eq_type_id FROM equipment_type_software_mapping etsm
					INNER JOIN device_function_mapping dfm ON dfm.eq_type_software_map_id=etsm.eq_type_software_map_id
					WHERE dfm.device_function_map_id='".$device_function_map_id."'
					";
	
		return Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
	}
	
	public function get_software_package_id_of_function_map($device_function_map_id){
	
		$sql =		"SELECT etsm.software_package_id FROM equipment_type_software_mapping etsm
					INNER JOIN device_function_mapping dfm ON dfm.eq_type_software_map_id=etsm.eq_type_software_map_id
					WHERE dfm.device_function_map_id='".$device_function_map_id."'
					";
	
		return Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
	
	}
	
	public function get_device_function_command_maps($device_function_map_id){
	
		$sql =		"SELECT dcm.device_command_map_id, dcm.command_exe_order, dc.device_command_id, dc.base_command FROM device_command_mapping dcm
					INNER JOIN device_commands dc ON dc.device_command_id=dcm.device_command_id
					WHERE dcm.device_function_map_id='".$device_function_map_id."'
					ORDER BY dcm.command_exe_order DESC
					";
	
		$this->_device_function_command_maps = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		return $this->_device_function_command_maps;
	
	}

}
?>