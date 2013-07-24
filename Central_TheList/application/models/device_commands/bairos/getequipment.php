<?php

//exception codes 16700-16799

class thelist_bairos_command_getequipment implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	
	private $_equipment=null;

	
	public function __construct($device)
	{
		$this->_device = $device;
	}
	
	public function execute()
	{
		$get_serial			= new Thelist_Bairos_command_getserialnumber($this->_device);
		$serial_number		= $get_serial->get_serial_number();
			
		$sql2 = 	"SELECT eq_id FROM equipments
					WHERE eq_serial_number='".$serial_number."'
					";
			
		$eq_id2  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql2);

		if (isset($eq_id2['eq_id'])) {
			
			$this->_equipment	= new Thelist_Model_equipments($eq_id2['eq_id']);
			
		} else {
			
			$equipment_type		= new Thelist_Bairos_command_getequipmenttype($this->_device);
			$equipment_type_obj = $equipment_type->get_eq_type_obj();
			
			//create the equipment
			$data = array(
			
					'eq_type_id'   			=>  $equipment_type_obj->get_eq_type_id(),
					'eq_serial_number' 		=>  $serial_number,
					'eq_fqdn'     			=>  $this->_device->get_fqdn(),								
			);
			
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
			
			$new_eq_id 	= Zend_Registry::get('database')->insert_single_row('equipments',$data,$class,$method);
			
			$this->_equipment	= new Thelist_Model_equipments($new_eq_id);

		}
		
		if ($this->_equipment != null) {
			
			//get software
			$get_software			= new Thelist_Bairos_command_getsoftware($this->_device);
			$software_package		= $get_software->get_running_software_obj();

			$this->_equipment->set_current_software_package($software_package);
			$this->_equipment->update_static_interfaces();
			$this->_equipment->create_api_auth($this->_device->get_device_authentication_credentials(), true);
			$this->_equipment->set_eq_fqdn($this->_device->get_fqdn());
			
			//set all interface mac addresses
			if ($this->_equipment->get_interfaces() != null) {
				foreach($this->_equipment->get_interfaces() as $interface) {
					$get_mac = new Thelist_Bairos_command_getinterfacemacaddress($this->_device, $interface);
					$interface->set_if_mac_address($get_mac->get_mac_address());
				}
			}
			
		} else {
			throw new exception('we could not create equipment from the device, odd since we are logged into the device', 15800);
		}
	}
	
	public function get_equipment()
	{
		if ($this->_equipment == null) {
			$this->execute();
		}
		
		return $this->_equipment;
	}
	
}