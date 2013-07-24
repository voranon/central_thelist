<?php
// created by non 9/26/20012


class thelist_model_serviceplantempquoteoptionmap{
	
	private $logs;
	private $user_session;
	private $_time;
	
	private $service_plan_temp_quote_option_map_id=null;
	private $service_plan_option_map_id=null;
	private $service_plan_temp_quote_map_id=null;
	private $service_plan_temp_quote_option_actual_mrc=null;
	private $service_plan_temp_quote_option_actual_nrc=null;
	private $service_plan_temp_quote_option_actual_mrc_term=null;
	
	private $_service_plan_option_map=null;
	
	public function __construct($service_plan_temp_quote_option_map_id)
	{
		
		$this->logs										= Zend_Registry::get('logs');
		$this->user_session								= new Zend_Session_Namespace('userinfo');
		$this->_time									= Zend_Registry::get('time');
		
		$sql="SELECT *
			  FROM service_plan_temp_quote_option_mapping
			  WHERE service_plan_temp_quote_option_map_id =".$service_plan_temp_quote_option_map_id;
		
		$service_plan_quote_option_mapping_temp = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		$this->service_plan_temp_quote_option_map_id 			= $service_plan_temp_quote_option_map_id;
		$this->service_plan_option_map_id						= $service_plan_quote_option_mapping_temp['service_plan_option_map_id'];
		$this->service_plan_temp_quote_map_id					= $service_plan_quote_option_mapping_temp['service_plan_temp_quote_map_id'];
		$this->service_plan_temp_quote_option_actual_mrc		= $service_plan_quote_option_mapping_temp['service_plan_temp_quote_option_actual_mrc'];
		$this->service_plan_temp_quote_option_actual_nrc		= $service_plan_quote_option_mapping_temp['service_plan_temp_quote_option_actual_nrc'];
		$this->service_plan_temp_quote_option_actual_mrc_term	= $service_plan_quote_option_mapping_temp['service_plan_temp_quote_option_actual_mrc_term'];
	
	}
	
	public function get_service_plan_option_map()
	{
		if ($this->_service_plan_option_map == null) {
			$this->_service_plan_option_map	= new Thelist_Model_serviceplanoptionmap($this->service_plan_option_map_id);
		}
		return $this->_service_plan_option_map;
	}
	
	
	public function get_service_plan_temp_quote_option_map_id(){
		return $this->service_plan_temp_quote_option_map_id; 
	}
	
	public function get_service_plan_option_map_id(){
		return $this->service_plan_option_map_id;
	}
	
	public function get_service_plan_temp_quote_map_id(){
		return $this->service_plan_temp_quote_map_id; 
	}
	
	public function get_service_plan_temp_quote_option_actual_mrc()
	{
		return $this->service_plan_temp_quote_option_actual_mrc;
	}
	
	public function get_service_plan_temp_quote_option_actual_nrc()
	{
		return $this->service_plan_temp_quote_option_actual_nrc;
	}
	
	public function get_service_plan_temp_quote_option_actual_mrc_term()
	{
		return $this->service_plan_temp_quote_option_actual_mrc_term;
	}
	
	///////////////////////////
	
// 	public function set_service_plan_option_map_id($new_service_plan_option_map_id){
		
// 		if ($this->service_plan_option_map_id != $new_service_plan_option_map_id) {
		
// 			Zend_Registry::get('database')->set_single_attribute($this->service_plan_temp_quote_option_map_id, 'service_plan_temp_quote_option_mapping', 'service_plan_option_map_id',$new_service_plan_option_map_id);
// 			$this->service_plan_option_map_id = $new_service_plan_option_map_id;
		
// 		}
// 		return true;
// 	}
	
// 	public function set_service_plan_temp_quote_map_id($new_service_plan_temp_quote_map_id)
// 	{
		
// 		if($this->service_plan_temp_quote_map_id != $new_service_plan_temp_quote_map_id){
		
// 			Zend_Registry::get('database')->set_single_attribute($this->service_plan_temp_quote_option_map_id, 'service_plan_temp_quote_option_mapping', 'service_plan_temp_quote_map_id',$new_service_plan_temp_quote_map_id);
// 			$this->service_plan_temp_quote_map_id = $new_service_plan_temp_quote_map_id;
		
// 		}
		
// 		return true;
		
		
// 	}
	
// 	public function set_service_plan_temp_quote_option_actual_mrc($new_service_plan_temp_quote_option_actual_mrc){
		
// 		if($this->service_plan_temp_quote_option_actual_mrc != $new_service_plan_temp_quote_option_actual_mrc){
		
// 			Zend_Registry::get('database')->set_single_attribute($this->service_plan_temp_quote_option_map_id, 'service_plan_temp_quote_option_mapping', 'service_plan_temp_quote_option_actual_mrc',$new_service_plan_temp_quote_option_actual_mrc);
// 			$this->service_plan_temp_quote_option_actual_mrc = $new_service_plan_temp_quote_option_actual_mrc;
		
// 		}
		
// 		return true;
// 	}
	
// 	public function set_service_plan_temp_quote_option_actual_nrc($new_service_plan_temp_quote_option_actual_nrc){
		
// 		if($this->service_plan_temp_quote_option_actual_nrc != $new_service_plan_temp_quote_option_actual_nrc){
		
// 			Zend_Registry::get('database')->set_single_attribute($this->service_plan_temp_quote_option_map_id, 'service_plan_temp_quote_option_mapping', 'service_plan_temp_quote_option_actual_nrc',$new_service_plan_temp_quote_option_actual_nrc);
// 			$this->service_plan_temp_quote_option_actual_nrc = $new_service_plan_temp_quote_option_actual_nrc;
		
// 		}
		
// 		return true;
// 	}
	
// 	public function set_service_plan_temp_quote_option_actual_mrc_term($new_service_plan_temp_quote_option_actual_mrc_term){
		
// 		if($this->service_plan_temp_quote_option_actual_mrc_term != $new_service_plan_temp_quote_option_actual_mrc_term){
		
// 			Zend_Registry::get('database')->set_single_attribute($this->service_plan_temp_quote_option_map_id, 'service_plan_temp_quote_option_mapping', 'service_plan_temp_quote_option_actual_mrc_term',$new_service_plan_temp_quote_option_actual_mrc_term);
// 			$this->service_plan_temp_quote_option_actual_mrc_term = $new_service_plan_temp_quote_option_actual_mrc_term;
		
// 		}
		
// 		return true;
// 	}
	
	
	
}