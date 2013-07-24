<?php 

class thelist_model_pathlimitation
{

	private $database;
	private $_allow_first_interface_in_service_point='0';
	private $_allow_interfaces_in_servicepoint='0';
	private $_deny_path_through_these_eq_types=null;
	private $_check_if_first_interface_is_originator='0';
	private $_verify_first_interface_equipment_eq_type='0';
	private $_check_first_interface_equipment_role='0';
	private $_equipment_unit_groups_allowed=null;
	
	
	public function __construct()
	{


		
	}
	
	public function set_verify_first_interface_equipment_eq_type($boolean)
	{
		//do we test the equipment type of the provided interface against the deny eq_type_list?
		//to be in a service point? default no(0)
		$this->_verify_first_interface_equipment_eq_type	= $boolean;
		
	}
	
	public function set_check_first_interface_equipment_role($boolean)
	{
		//do we test the equipment type of the provided interface against the equipment role we are looking for??
		//default no(0)
		$this->_check_first_interface_equipment_role	= $boolean;
	
	}
	
	public function set_first_interface_in_servicepoint_allowed($boolean)
	{
		//do we allow the interface we supply to the path finder
		//to be in a service point? default no(0)
		$this->_allow_first_interface_in_service_point	= $boolean;
	}
	public function set_check_if_first_interface_is_originator($boolean)
	{
		//do we want to check if the interface we supply to the path finder
		//originates a service we are looking for?? default no(0)
		$this->_check_if_first_interface_is_originator	= $boolean;
	}
	public function set_interfaces_in_servicepoint_allowed($boolean)
	{
		//do we allow interface in the path to be part of a service point?
		//this should be overruled by "set_first_interface_in_servicepoint_allowed" for the fist interface only
		//default no(0)
		$this->_allow_interfaces_in_servicepoint	= $boolean;
	}
	
	public function set_equipment_unit_groups_allowed($unit_group_id)
	{
		//what units do we allow the equipment in the path to reside in
		//default null=all
		
		if($this->_equipment_unit_groups_allowed == null){
			
			$this->_equipment_unit_groups_allowed = array();
			
		}

		$this->_equipment_unit_groups_allowed[]	= $unit_group_id;
	}

	public function get_first_interface_in_servicepoint_allowed()
	{
		return $this->_allow_first_interface_in_service_point;
	}
	public function get_check_if_first_interface_is_originator()
	{
		return $this->_check_if_first_interface_is_originator;
	}
	public function get_interfaces_in_servicepoint_allowed()
	{
		return $this->_allow_interfaces_in_servicepoint;
	}
	public function get_deny_path_through_these_eq_types()
	{
		return $this->_deny_path_through_these_eq_types;
	}
	public function get_verify_first_interface_equipment_eq_type()
	{
		return $this->_verify_first_interface_equipment_eq_type;
	}
	public function get_check_first_interface_equipment_role()
	{
		return $this->_check_first_interface_equipment_role;
	}
	public function get_equipment_unit_groups_allowed()
	{
		return $this->_equipment_unit_groups_allowed;
	}

	public function add_deny_path_through_eq_type($eq_type_id)
	{
		//array of eq_types that we do not allow in the path
		//this behaiviour can be over ridden by "set_verify_first_interface_equipment_eq_type"
		//for the interface we supply
		if($this->_deny_path_through_these_eq_types == null) {
				
			$this->_deny_path_through_these_eq_types = array();
				
		}
	
		$eq_type_obj											= new Thelist_Model_equipmenttype($eq_type_id);
	
		$this->_deny_path_through_these_eq_types[$eq_type_id] 	= $eq_type_obj;
	}
	
	public function deny_path_through_all_service_patch_panels()
	{
	
		//this is a shortcut because we most times do not want to traverse patchpanels
		$sql = "SELECT et.eq_type_id FROM equipments e
						INNER JOIN equipment_types et ON et.eq_type_id=e.eq_type_id
						INNER JOIN equipment_role_mapping erm ON erm.eq_id=e.eq_id
						WHERE erm.equipment_role_id='2'
						GROUP BY et.eq_type_id
						";
	
		$eq_type_ids	= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
	
		if (isset($eq_type_ids['0'])) {
	
			foreach ($eq_type_ids as $eq_type_id) {
					
				$this->add_deny_path_through_eq_type($eq_type_id['eq_type_id']);
					
			}
		}
	}

}
?>