<?php

//exception codes 16400-16499

class thelist_routeros_command_getequipmenttype implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	
	private $_model_name=null;

	public function __construct($device)
	{
		$this->_device = $device;
	}
	
	public function execute()
	{
		
		//get the software, because x86 based systems do not have a regular serial number
		$get_equipment_software 				= new Thelist_Routeros_command_getsoftware($this->_device);
		$software_package_architecture 			= $get_equipment_software->get_software_package_architecture();
		
		if ($software_package_architecture == 'x86') {
				
			$device_reply = $this->_device->execute_command("/system note print");
			preg_match("/note: +(PowerRouter 732) +/", $device_reply->get_message(), $model);

			if (isset($model['0'])) {

				$patterns = array('\"', "\r", "\r\n", "\n");
				$model_name = str_replace($patterns, '', $model['1']);
			}
				
		} else {
			
			$device_reply = $this->_device->execute_command("/system routerboard print");
			preg_match("/model: +(.*)/", $device_reply->get_message(), $model);
				
			if (isset($model['0'])) {

				$patterns = array('"', ' ', "\r", "\r\n", "\n");
				$model_name = str_replace($patterns, '', $model['1']);
			}
		}

		if (isset($model_name)) {
			$this->_model_name = $model_name;
			
		} else {
			throw new exception("we could not determine model number for device: ".$this->_device->get_fqdn()." ", 16400);
		}	
	}
	
	public function get_model_name()
	{
		if ($this->_model_name == null) {
			$this->execute();
		}
	
		return $this->_model_name;
	}
	
	public function get_eq_type_obj()
	{
		if ($this->_model_name == null) {
			$this->execute();
		}
	
		$sql = 	"SELECT eq_type_id FROM equipment_types
				WHERE eq_model_name='".$this->_model_name."'
				";
		
		$eq_type_id  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		if (isset($eq_type_id['eq_type_id'])) {
			return new Thelist_Model_equipmenttype($eq_type_id);
		} else {
			throw new exception("unknown equipment type with model name '".$this->_model_name."' for device: ".$this->_device->get_fqdn()."", 16401);
		}
	}
}