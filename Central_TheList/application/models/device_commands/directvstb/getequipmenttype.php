<?php

//exception codes 21600-21699 

class thelist_directvstb_command_getequipmenttype implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_arp_entry;
	
	private $_eq_type_obj=null;
	

	public function __construct($device, $arp_entry)
	{
		$this->_device 		= $device;
		$this->_arp_entry 	= $arp_entry;
	}
	
	public function execute()
	{
		//we track all equipment dhcp requests and for receivers, the model number is in the request
		//bring that database upto date
		
		$devicetracking		= new Thelist_Model_devicetracking();
		$devicetracking->update_dhcp_request_tracking();
		
		//now that it is upto date we can query the database for the information
		$sql23 = 	"SELECT * FROM device_tracking
					WHERE mac_address='".$this->_arp_entry->get_macaddress()."'
					";
		
		$request_detail = Zend_Registry::get('database')->get_tracking_adapter()->fetchRow($sql23);
		
		if (isset($request_detail['discovered_devices_id'])) {
			
			if ($request_detail['eq_type_id'] != 0) {
				
				$this->_eq_type_obj = new Thelist_Model_equipmenttype($request_detail['eq_type_id']);
				
			} else {
				throw new exception("we could not determine the model number for device: ".$this->_device->get_fqdn()." ", 21600);
			}
			
		} else {
			throw new exception("we could not determine the model number for device: ".$this->_device->get_fqdn()." ", 21601);
		}
	}
	
	public function get_model_name($refresh=false)
	{

		if($this->_eq_type_obj == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}

		return $this->_eq_type_obj->get_eq_model_name();
	}
	
	public function get_eq_type_obj($refresh=false)
	{
		if($this->_eq_type_obj == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}

		return $this->_eq_type_obj;
	}
	
}