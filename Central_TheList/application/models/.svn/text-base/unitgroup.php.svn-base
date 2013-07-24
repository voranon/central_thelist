<?php

//exception codes 21500-21599

class thelist_model_unitgroup
{
	
	private $_unit_group_id;
	private $_unit_group_name;
	private $_unit_group_type;
	
	private $_unit_group_type_resolved=null;
	private $_mapped_unit_id=null;
	private $_unit_group_type_name=null;
	
	public function __construct($unit_group_id)
	{
		$this->_unit_group_id = $unit_group_id;
		
		$sql =	"SELECT * FROM unit_groups
			  	WHERE unit_group_id=".$this->_unit_group_id;
		
		$unit_group_detail = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		$this->_unit_group_name			= $unit_group_detail['unit_group_name'];
		$this->_unit_group_type			= $unit_group_detail['unit_group_type'];

	}
	
	public function get_unit_group_id()
	{
		return $this->_unit_group_id;
	}
	
	public function get_unit_group_name()
	{
		return $this->_unit_group_name;
	}
	
	public function get_unit_group_type()
	{
		return $this->_unit_group_type;
	}
	
	public function get_unit_group_type_name()
	{
		$this->get_unit_group_type_resolved();
		
		return $this->_unit_group_type_name;
	}
	
	public function get_unit_group_type_resolved()
	{
		if ($this->_unit_group_type_resolved == null) {
			
			$sql = 	"SELECT item_value, item_name FROM items
					WHERE item_type='unit_grp_type'
					AND item_id='".$this->_unit_group_type."'
					";
				
			$unit_type = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
			if (isset($unit_type['item_value'])) {
				
				$this->_unit_group_type_name 		= $unit_type['item_value'];
				$this->_unit_group_type_resolved	= $unit_type['item_name'];

			} else {
				throw new exception("this unit group '".$this->_unit_group_id."' does not have a proper type, trying to lookup in items and failed", 21502);
			}
		}
		
		return $this->_unit_group_type_resolved;
	}
	
	public function fill_unit_mapping($unit_id)
	{
		if (is_numeric($unit_id)) {
			
			if ($this->_mapped_unit_id == null) {
				$this->_mapped_unit_id = $unit_id;
			} else {
				throw new exception("this unit group '".$this->_unit_group_id."' is already mapped to a unit, unit_id: '".$this->_mapped_unit_id."', cant remap", 21500);
			}
			
		} else {
			throw new exception("mapping unit to unitgroup using unit_id: '".$unit_id."' unit id must be numeric to group '".$this->_unit_group_id."'", 21501);
		}
	}
}
?>