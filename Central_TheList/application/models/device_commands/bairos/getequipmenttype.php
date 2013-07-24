<?php

//exception codes 16800-16899

class thelist_bairos_command_getequipmenttype implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	
	private $_model_name=null;

	public function __construct($device)
	{
		$this->_device = $device;
	}
	
	public function execute()
	{

		//get model
		$device_reply = $this->_device->execute_command("lspci -vb");

		preg_match("/Micro-Star International Co/", $device_reply->get_message(), $model);
		
		if (isset($model['0'])) {
			
			//cheat
			$clean_model = 'msi-ixp';
		
			$this->_model_name	= $clean_model;

		} else {
			throw new exception("we could not determine the model number for device: ".$this->_device->get_fqdn()." ", 16800);
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
			throw new exception('unknown equipment type', 15901);
		}
	}
	
}