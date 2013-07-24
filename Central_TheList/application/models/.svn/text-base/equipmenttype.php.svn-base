<?php


//by martin
//exception codes 100-199

class thelist_model_equipmenttype
{
	
	private $_eq_type_id;
	private $_eq_model_name;
	private $_eq_manufacturer;
	private $_eq_type_name;
	private $_eq_type_desc;
	private $_numberofstaticifs;
	
	// by martin
	private $_static_if_types=null;
	private $_eq_type_protected;
	private $eq_type_friendly_name;
	private $_eq_type_serialized=null;
	private $_equipment_type_application_maps=null;

	//common variables
	private $log;
	private $user_session;
	private $database;
	
	public function __construct($eq_type_id){
		$this->_eq_type_id		= $eq_type_id;	

		$this->log			= Zend_Registry::get('logs');
		$this->user_session = new Zend_Session_Namespace('userinfo');
		
		$sql="SELECT * FROM equipment_types
				WHERE eq_type_id='".$this->_eq_type_id."'
				";
		
		$eq_type = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
	
		$this->_eq_model_name			=$eq_type['eq_model_name'];
		$this->_eq_manufacturer			=$eq_type['eq_manufacturer'];
		$this->_eq_type_name			=$eq_type['eq_type_name'];
		$this->_eq_type_desc			=$eq_type['eq_type_desc'];
		$this->_eq_type_protected		=$eq_type['eq_type_protected'];
		$this->_eq_type_friendly_name	=$eq_type['eq_type_friendly_name'];
		$this->_eq_type_serialized		=$eq_type['eq_type_serialized'];

	}
	public function get_id(){
		return $this->_eq_type_id;
	}
	public function get_eq_type_id()
	{
		return $this->_eq_type_id;
	}	
	public function get_eq_model_name(){
		return $this->_eq_model_name;
	}
	public function get_eq_manufacturer(){
		return $this->_eq_manufacturer;
	}
	public function get_eq_type_name(){
		return $this->_eq_type_name;
	}
	public function get_eq_type_desc(){
		return $this->_eq_type_desc;
	}
	public function get_eq_type_protected(){
		return $this->_eq_type_protected;
	}
	public function get_eq_type_friendly_name(){
		return $this->_eq_type_friendly_name;
	}
	public function get_eq_type_serialized()
	{
		
		if ($this->_eq_type_serialized == '0') {
			
			return false;
			
		} elseif ($this->_eq_type_serialized == '1') {
			
			return true;
			
		} else {
			
			throw new exception("eq_type_id: ".$this->_eq_type_id." is not defined as serialized or not, this is mandetory");
			
		}
	}
	public function get_numberofstaticifs(){
		return $this->_numberofstaticifs;
	}
	public function get_static_if_types()
	{
		if ($this->_static_if_types == null) {
			
			//static ifs
			$sql2 = "SELECT sit.static_if_type_id, sit.if_index_number, sit.if_default_name, it.if_type_name FROM static_if_types sit
					LEFT OUTER JOIN interface_types it ON it.if_type_id=sit.if_type_id
					WHERE eq_type_id='".$this->_eq_type_id."'
					ORDER BY sit.if_index_number ASC
					";
			
			$static_if_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
			
			if (isset($static_if_types['0'])) {
			
				foreach($static_if_types as $static_if_type){
					$this->_numberofstaticifs++;

					//duplicate metod for now while migrating,
					$this->_static_if_types[$static_if_type['static_if_type_id']] = new Thelist_Model_staticiftype($static_if_type['static_if_type_id']);
						
				}
			}
		}

		return $this->_static_if_types;
	}

	public function get_serial_regex(){
		
		$sql = "SELECT * FROM eq_type_serial_match
				WHERE eq_type_id='".$this->_eq_type_id."'
				";
		
		$serial_regexs = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		return $serial_regexs;
	}
	public function get_service_plan_eq_type_additional_install_time(){
		return $this->_service_plan_eq_type_additional_install_time;
	}
	public function get_service_plan_eq_type_default_mrc(){
		return $this->_service_plan_eq_type_default_mrc;
	}
	public function get_service_plan_eq_type_default_nrc(){
		return $this->_service_plan_eq_type_default_nrc;
	}
	public function get_service_plan_eq_type_default_mrc_term(){
		return $this->_service_plan_eq_type_default_mrc_term;
	}
	public function get_service_plan_eq_type_map_id(){
		return $this->_service_plan_eq_type_map_id;
	}
	public function get_service_plan_eq_type_group_id(){
		return $this->_service_plan_eq_type_group_id;
	}
	public function get_service_plan_eq_type_map_master_id(){
		return $this->_service_plan_eq_type_map_master_id;
	}
	public function get_eq_type_software_map_id($software_package_id)
	{
		
		//get the software map id
		$sql = "SELECT * FROM equipment_type_software_mapping
				WHERE eq_type_id='".$this->_eq_type_id."'
				AND software_package_id='".$software_package_id."'
				";
		
		$eq_type_software_map_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		if ($eq_type_software_map_id != null) {
			
			return $eq_type_software_map_id;
			
		} else {
			
			throw new exception("'there is no software - eq_type map defined for Software: ".$software_package_id." AND eq_type_id: ".$this->_eq_type_id."'");
			
		}
		
		
	}
	
	public function get_sp_eq_type_group_detail()
	{
		$service_plan_eq_type_group_detail	=	Zend_Registry::get('database')->get_service_plan_eq_type_groups()->fetchRow('service_plan_eq_type_group_id='.$this->_service_plan_eq_type_group_id);
	
		$spet_detail = array('required_amount' => $service_plan_eq_type_group_detail['service_plan_eq_type_required_quantity'], 'max_amount' => $service_plan_eq_type_group_detail['service_plan_eq_type_max_quantity'], 'name' => $service_plan_eq_type_group_detail['service_plan_eq_type_group_name']);
	
		return $spet_detail;
	
	}
	
	
	public function validate_serial_number_format($serial_number)
	{
		//regex on serial
		$sql = "SELECT * FROM eq_type_serial_match
				WHERE eq_type_id='".$this->_eq_type_id."'
				";
		
		$serial_regexs = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		
		if ($serial_regexs != null) {

			foreach ($serial_regexs as $single_regex) {
					
				if (preg_match("/$single_regex[regex]/", $serial_number)) {
			
					//match and return true
					return true;
				}
			}
			//if no match throw exception
			throw new exception('The serial number provided does not match any known serial number patterns for this equipment type', 121);
			
		} else {
			//if there is nothing to match against it must be a match
			return true;
		} 
	}
	
	public function set_eq_model_name($new_value){

		if ($this->_eq_type_protected == 1) {
			
			return false;
			
		}
		
		$return = Zend_Registry::get('database')->set_single_attribute($this->_eq_type_id, 'equipment_types', 'eq_model_name', $new_value);
		if ($return != false) {
			
			$this->$return['0'] = $return['1'];
		}

	}
	public function set_eq_manufacturer($new_value){
		
		if ($this->_eq_type_protected == 1) {
				
			return false;
				
		}
		$return = Zend_Registry::get('database')->set_single_attribute($this->_eq_type_id, 'equipment_types', 'eq_manufacturer', $new_value);
		if ($return != false) {
				
			$this->$return['0'] = $return['1'];
		}
	}
	public function set_eq_type_name($new_value){
		
		if ($this->_eq_type_protected == 1) {
				
			return false;
				
		}
		$return = Zend_Registry::get('database')->set_single_attribute($this->_eq_type_id, 'equipment_types', 'eq_type_name', $new_value);
		if ($return != false) {
			
			$this->$return['0'] = $return['1'];
		}
	}
	public function set_eq_type_desc($new_value){
		
		$return = Zend_Registry::get('database')->set_single_attribute($this->_eq_type_id, 'equipment_types', 'eq_type_desc', $new_value);
		if ($return != false) {
			
			$this->$return['0'] = $return['1'];
		}
	}
	
	public function set_eq_type_protected($new_value){
	
		$return = Zend_Registry::get('database')->set_single_attribute($this->_eq_type_id, 'equipment_types', 'eq_type_protected', $new_value);
		if ($return != false) {
			
			$this->$return['0'] = $return['1'];
		}
	}

	public function set_eq_type_friendly_name($new_value){

		$return = Zend_Registry::get('database')->set_single_attribute($this->_eq_type_id, 'equipment_types', 'eq_type_friendly_name', $new_value);
		if ($return != false) {
			
			$this->$return['0'] = $return['1'];
		}
	}
	
	public function set_eq_type_serialized($new_value){
		if ($this->_eq_type_protected == 1) {
				
			return false;
				
		}
		$return = Zend_Registry::get('database')->set_single_attribute($this->_eq_type_id, 'equipment_types', 'eq_type_serialized', $new_value);
		if ($return != false) {
			
			$this->$return['0'] = $return['1'];
		}
	}
	
		public function map_eq_type_to_software($software_package_id)
		{
		
		//check if the map already exists.
		$sql = 	"SELECT COUNT(eq_type_software_map_id) FROM equipment_type_software_mapping
				WHERE eq_type_id='".$this->_eq_type_id."'
				AND software_package_id='".$software_package_id."'
				";
		$map_exists = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		if ($map_exists > 0) {
			//return id of existing row
			$sql2 = 	"SELECT eq_type_software_map_id FROM equipment_type_software_mapping
						WHERE eq_type_id='".$this->_eq_type_id."'
						AND software_package_id='".$software_package_id."'
						LIMIT 1
						";
			
			return Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
			
			} else {
				//insert new row and return the id
				
				$data = array(
				
							'software_package_id'		=>  $software_package_id,
							'eq_type_id'				=>  $this->_eq_type_id,
				
				);
			
				return Zend_Registry::get('database')->insert_single_row('equipment_type_software_mapping',$data,'equipment_types','map_type_to_software');
			
			}
		}
		
		public function remove_eq_type_application($equipment_application_id)
		{

			$application = $this->get_equipment_type_application_map($equipment_application_id);
		
			if ($application != false) {

				$metrics = $application->get_equipment_type_application_metrics();
				
				//remove the metrics
				if ($metrics != null) {
					foreach ($metrics as $metric) {
						$application->remove_eq_type_metric_map($metric->get_equipment_type_application_metric_id());
					}
				}
				
				//delete the application
				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);
							
				Zend_Registry::get('database')->delete_single_row($application->get_eq_type_application_id(), 'eq_type_applications', $class, $method);
				
				//remove the application from this eq_type
				unset($this->_equipment_type_application_maps[$equipment_application_id]);
				
			} else {
				throw new exception("equipment application id ".$equipment_application_id." is not mapped to eq_type_id: ".$this->_eq_type_id." ", 123);
			}
		}
		
		public function add_eq_type_application($equipment_application_id)
		{

			$this->get_equipment_type_application_maps();

			if ($this->_equipment_type_application_maps == null) {
					
				foreach ($this->_equipment_type_application_maps as $current_application) {
		
					if ($current_application->get_equipment_application_id() == $equipment_application_id) {
							
						//already exist
						return $current_application;
					}
				}
			}
		
			//if config does not already exist, we create it
			$equipment_application = new Thelist_Model_equipmentapplication($equipment_application_id);
		
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
		
			$data = array(
		
					'eq_type_id'							=>  $this->_eq_type_id,
					'equipment_application_id'				=>  $equipment_application_id,
			);
		
			//update start value
			$new_eq_type_application_id = Zend_Registry::get('database')->insert_single_row('eq_type_applications',$data, $class,$method);
		
			$this->_equipment_type_application_maps[$equipment_application_id] = $equipment_application;
			$this->_equipment_type_application_maps[$equipment_application_id]->set_eq_type_application_id($new_eq_type_application_id);

			return $this->_equipment_type_application_maps[$equipment_application_id];
		}
		
		public function map_homerun_static_interface( $homerun_type_group_id, $service_plan_eq_type_map_id, $if_static_id )
		{
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
			
			if(is_numeric($homerun_type_group_id)&&is_numeric( $service_plan_eq_type_map_id )&&is_numeric(  $if_static_id) ){
				
				$exist_sql="SELECT COUNT(*) AS exist
							FROM homerun_group_eq_type_mapping
							WHERE 	homerun_type_group_id=".$homerun_type_group_id."
							AND 	service_plan_eq_type_map_id=".$service_plan_eq_type_map_id."
							AND 	static_if_type_id=".$if_static_id."
						   ";
				
				$exist = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($exist_sql);
				
				if(!$exist){
					$data = array(
						'homerun_type_group_id' 			=> $homerun_type_group_id,
						'service_plan_eq_type_map_id'		=> $service_plan_eq_type_map_id,
						'static_if_type_id'					=> $if_static_id  
					);
					
					Zend_Registry::get('database')->insert_single_row('homerun_group_eq_type_mapping',$data,get_class($this),$method);
				}
								
			
			}else{
				throw new exception("homerun_type_group_id, service_plan_eq_type_map_id, static_if_type_id have to be numeric", 122);
			}
		}
		
		public function get_equipment_type_application_maps()
		{
			if ($this->_equipment_type_application_maps == null) {
				
				$sql = 	"SELECT * FROM eq_type_applications
						WHERE eq_type_id='".$this->_eq_type_id."'
						";
				
				$applications = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);

				if (isset($applications['0'])) {
					foreach ($applications as $application) {
						
						$this->_equipment_type_application_maps[$application['equipment_application_id']] = new Thelist_Model_equipmentapplication($application['equipment_application_id']);
						$this->_equipment_type_application_maps[$application['equipment_application_id']]->set_eq_type_application_id($application['eq_type_application_id']);
					}
				}
			}
			
			return $this->_equipment_type_application_maps;
		}
		
		public function get_equipment_type_application_map($equipment_application_id)
		{
			$this->get_equipment_type_application_maps();
			
			if (isset($this->_equipment_type_application_maps[$equipment_application_id])) {
				return $this->_equipment_type_application_maps[$equipment_application_id];
			} else {
				return false;
			}
		}
	
}
?>