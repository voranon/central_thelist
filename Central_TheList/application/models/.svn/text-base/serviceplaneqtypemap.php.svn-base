<?php

class thelist_model_serviceplaneqtypemap{

	private $database;
	private $logs;
	private $user_session;
	private $_time;
	
	private $_service_plan_eq_type_map_id;
	private $_service_plan_eq_type_map_master_id;
	private $_service_plan_id;
	
	private $_eq_type_group_id;
	
	private $_service_plan_eq_type_group;
	private $_service_plan_eq_type_group_id;
	
	
	private $_service_plan_eq_type_additional_install_time;
	private $_service_plan_eq_type_default_mrc;
	private $_service_plan_eq_type_default_nrc;
	private $_service_plan_eq_type_default_mrc_term;
	private $_service_plan_eq_type_name;
	private $_eq_default_prov_plan_id=null;
	private $_eq_type_group=null;
	
	public function __construct($service_plan_eq_type_map_id)
	{
		$this->_service_plan_eq_type_map_id				= $service_plan_eq_type_map_id;	

		$this->logs										= Zend_Registry::get('logs');
		$this->user_session								= new Zend_Session_Namespace('userinfo');
		$this->_time									= Zend_Registry::get('time');
		
		$sql=	"SELECT * FROM service_plan_eq_type_mapping
				WHERE service_plan_eq_type_map_id='".$this->_service_plan_eq_type_map_id."'
				";
		
		$service_plan_eq_type_map = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		$this->_service_plan_eq_type_map_master_id				= $service_plan_eq_type_map['service_plan_eq_type_map_master_id'];
		$this->_service_plan_id									= $service_plan_eq_type_map['service_plan_id'];
		
		$this->_service_plan_eq_type_additional_install_time	= $service_plan_eq_type_map['service_plan_eq_type_additional_install_time'];
		$this->_service_plan_eq_type_default_mrc				= $service_plan_eq_type_map['service_plan_eq_type_default_mrc'];
		$this->_service_plan_eq_type_default_nrc				= $service_plan_eq_type_map['service_plan_eq_type_default_nrc'];
		$this->_service_plan_eq_type_default_mrc_term			= $service_plan_eq_type_map['service_plan_eq_type_default_mrc_term'];
		$this->_eq_default_prov_plan_id							= $service_plan_eq_type_map['eq_default_prov_plan_id'];
		
		$this->_eq_type_group_id								= $service_plan_eq_type_map['eq_type_group_id'];
		$this->_service_plan_eq_type_group_id					= $service_plan_eq_type_map['service_plan_eq_type_group_id'];
		
	}
	
	public function get_eq_default_prov_plan_id()
	{
		return $this->_eq_default_prov_plan_id;
	}
	
	public function get_service_plan_eq_type_map_id()
	{
		return $this->_service_plan_eq_type_map_id;
	}
	public function get_service_plan_eq_type_map_master_id()
	{
		return $this->_service_plan_eq_type_map_master_id;
	}
	public function get_service_plan_id()
	{
		return $this->_service_plan_id;
	}
	public function get_service_plan_eq_type_name()
	{
		return $this->_service_plan_eq_type_name;
	}
	
	public function get_service_plan_eq_type_group()
	{
		if ($this->_service_plan_eq_type_group == null) {
			$this->_service_plan_eq_type_group = new Thelist_Model_serviceplaneqtypegroup($this->_service_plan_eq_type_group_id);
		}
		return $this->_service_plan_eq_type_group;
	}
	
	public function get_eq_type_group()
	{
		if ($this->_eq_type_group == null) {
			$this->_eq_type_group = new Thelist_Model_equipmenttypegroup($this->_eq_type_group_id);
		}
		
		return $this->_eq_type_group;
	}
	
	public function get_service_plan_eq_type_additional_install_time(){
		return $this->_service_plan_eq_type_additional_install_time;
	}
	public function get_service_plan_eq_type_default_mrc()
	{
		return $this->_service_plan_eq_type_default_mrc;
	}
	public function get_service_plan_eq_type_default_nrc()
	{
		return $this->_service_plan_eq_type_default_nrc;
	}
	public function get_service_plan_eq_type_default_mrc_term()
	{
		return $this->_service_plan_eq_type_default_mrc_term;
	}
	
	
}
?>