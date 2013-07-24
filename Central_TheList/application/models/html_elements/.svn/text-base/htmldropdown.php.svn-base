<?php

class thelist_html_element_htmldropdown
{
	private $database;
		
	public function __construct()
	{
		

			
	}
	
	
	
	public function service_plan_types_dd($item_id=null)
	{
			
		if ($item_id != null) {
				
			$sql =	"SELECT * FROM items
		 		 	WHERE item_id='".$item_id."'
		 		 	";
			
			$current_item = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
				
			$service_plan_types_dd = '';
			$service_plan_types_dd.="<OPTION VALUE='".$current_item['item_id']."'>".$current_item['item_value']."</OPTION>";
				
		} else {
				
			$service_plan_types_dd = '';
			$service_plan_types_dd.="<OPTION VALUE=''>---SELECT ONE---</OPTION>";
				
				
		}
			
			$sql2 =	"SELECT * FROM items
					WHERE item_type='service_plan_type'
					";
				
			$service_plan_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
				
				
				foreach ($service_plan_types as $service_plan_type) {
	
					$service_plan_types_dd.="<OPTION VALUE='".$service_plan_type['item_id']."'>".$service_plan_type['item_value']."</OPTION>";
				}
				
			return $service_plan_types_dd;
			
	}
			
			
}
?>