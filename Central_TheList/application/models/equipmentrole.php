<?php

class thelist_model_equipmentrole
{
	private $_database;
	private $_equipment_role_id;
	private $_equipment_role_name;
	private $_equipment_role_map_id=null;
	
	public function __construct($equipment_role_id)
	{
		$this->_equipment_role_id		= $equipment_role_id;
		
		$this->_database				= Zend_Registry::get('database');
		
		$equipment_role			= $this->_database->get_equipment_roles()->fetchRow('equipment_role_id='.$this->_equipment_role_id);
		
		$this->_equipment_role_name		= $equipment_role['equipment_role_name'];
	}

	
	public function get_equipment_role_id()
	{
		return $this->_equipment_role_id;
	}
	public function get_equipment_role_name()
	{
		return $this->_equipment_role_name;
	}
	public function get_equipment_role_map_id()
	{
		return $this->_equipment_role_map_id;
	}
	public function set_equipment_role_map_id($equipment_role_map_id)
	{
		$this->_equipment_role_map_id		= $equipment_role_map_id;
	}
	
	
}
?>