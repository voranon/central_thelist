<?php 

//require_once APPLICATION_PATH.'/models/equipments.php';
//require_once APPLICATION_PATH.'/models/equipment_interfaces.php';
//require_once APPLICATION_PATH.'/models/monitoring_guids.php';

//wrapper class that allows us to interact with all classes that implement the guid interface (not built) 

//by martin
class thelist_model_monitoringservice
{
	private $_monitoring_guid_id;
	private $_table_name;
	private $_primary_key;
	

	public function __construct($monitoring_guid_id)
	{
		$this->_monitoring_guid_id = $monitoring_guid_id;
		
		$monitoring_obj = new monitoringguid($this->_monitoring_guid_id);
		$this->_table_name = $monitoring_obj->get_table_name();
		$this->_primary_key = $monitoring_obj->get_primary_key();

	}
	
	public function get_monitoring_obj()
	{
		if ($this->_table_name == 'equipments') {
				
			$mon_obj =  new Thelist_Model_equipments($this->_primary_key);
		
		} elseif ($this->_table_name == 'interfaces') {
				
			$mon_obj = new Thelist_Model_equipmentinterface($this->_primary_key);
		
		}
		
		return $mon_obj;
		
	}
	
	
}
?>