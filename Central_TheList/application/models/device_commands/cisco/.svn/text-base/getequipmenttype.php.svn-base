<?php

//exception codes 15900-15999

class thelist_cisco_command_getequipmenttype implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	
	private $_model_name=null;

	public function __construct($device)
	{
		$this->_device = $device;
	}
	
	public function execute()
	{
		//set the terminal so it does not page
		$set_terminal	= new Thelist_Cisco_command_setterminal($this->_device, 80, 0);
		$set_terminal->execute();

		//get the root folder
		$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
		$get_connection_root_folder->execute();

		//get model
		$device_reply = $this->_device->execute_command("show version");

		//set the terminal back to standard
		$set_terminal	= new Thelist_Cisco_command_setterminal($this->_device, 80, 25);
		$set_terminal->execute();
		
		preg_match("/Model number: +(.*)|Model number +: +(.*)/", $device_reply->get_message(), $model);
		
		if (isset($model['0'])) {
				
			$patterns = array('\"', ' ', "\r", "\r\n", "\n");
				
			if (isset($model['2'])) {
				$clean_model = str_replace($patterns, '', $model['2']);
			} else {
				$clean_model = str_replace($patterns, '', $model['1']);
			}
			
			$this->_model_name	= $clean_model;

		} else {
			throw new exception("we could not determine the model number for device: ".$this->_device->get_fqdn()." ", 15900);
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
			throw new exception("we could not determine the eq type. model: '".$this->_model_name."' for device: ".$this->_device->get_fqdn()." ", 15901);
		}
	}
	
}