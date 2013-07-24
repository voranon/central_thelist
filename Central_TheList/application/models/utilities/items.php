<?php

class thelist_utility_items{
	
	private $item_type;
	private $database;
	
	
	public function __construct($item_type=null)
	{
			$this->item_type = $item_type;

	}
	
	public function get_id($item_name)
	{
		$sql="SELECT item_id
			  FROM items
			  WHERE item_type='".$this->item_type."'
			  ANd item_active = 1
			  AND item_name='".$item_name."'";
		
		return Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
	}
	
	public function get_value($item_name)
	{
		
		$sql="SELECT item_value
			  FROM items
			  WHERE item_type='".$this->item_type."'
			  ANd item_active = 1
			  AND item_name='".$item_name."'";
		
		return Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
	}
	
	public function get_attribute1($item_name)
	{
		$sql="SELECT item_attribute1
			  FROM items
			  WHERE item_type='".$this->item_type."'
			  ANd item_active = 1
			  AND item_name='".$item_name."'";
		
		return Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
	}
	
	public function get_attribute2($item_name)
	{
		$sql="SELECT item_attribute2
			  FROM items
			  WHERE item_type='".$this->item_type."'
			  ANd item_active = 1
			  AND item_name='".$item_name."'";
		
		return Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
	}
	
	public function get_attribute3($item_name)
	{
		$sql="SELECT item_attribute3
			  FROM items
			  WHERE item_type='".$this->item_type."'
			  ANd item_active = 1
			  AND item_name='".$item_name."'";
		
		return Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
	}
	
}


?>