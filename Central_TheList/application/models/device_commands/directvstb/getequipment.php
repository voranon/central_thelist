<?php

//exception codes 16100-16199

class thelist_directvstb_command_getequipment implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $database;
	private $_current_arp_entry=null;
	
	private $_equipment=null;
	
	public function __construct($device)
	{
		$this->_device = $device;
	}
	
	public function execute()
	{
		$receiver_serial_obj 		= new Thelist_Directvstb_command_getserialnumber($this->_device, 'receiver');
		$receiver_serial 			= $receiver_serial_obj->execute(); 
		
		$accesscard_serial_obj		= new Thelist_Directvstb_command_getserialnumber($this->_device, 'accesscard');
		$accesscard_serial 			= $accesscard_serial_obj->execute();

		if ($receiver_serial != false && $accesscard_serial != false) {
		
			//receiver find
			$sql = 		"SELECT e2.eq_id AS accesscard_eq_id, e2.eq_serial_number AS accesscard_serial, e.eq_id AS receiver_eq_id, e.eq_second_serial_number AS receiver_id FROM equipments e
						LEFT OUTER JOIN equipments e2 ON e2.eq_master_id=e.eq_id
						WHERE e.eq_second_serial_number='".$receiver_serial."'
						";
		
			$receiver  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
			//access card find
			$sql2 = 	"SELECT e.eq_id AS accesscard_eq_id, e.eq_serial_number AS accesscard_serial, e2.eq_id AS receiver_eq_id, e2.eq_second_serial_number AS receiver_id FROM equipments e
						LEFT OUTER JOIN equipments e2 ON e2.eq_id=e.eq_master_id
						WHERE e.eq_serial_number='".$accesscard_serial."'
						";
		
			$access_card  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql2);
				
			//known receiver senario
			if (isset($receiver['receiver_eq_id'])) {
					
				if ($receiver['accesscard_serial'] == $accesscard_serial) {
					
					$receiver			= new Thelist_Model_equipments($receiver['receiver_eq_id']);
					$receiver->set_eq_fqdn($this->_device->get_fqdn());
					
					$this->_equipment = $receiver;

				} else {
					
					//if the serial information for the access card does not match, clear all old access card master ids that are attached to this receiver
					$sql3 = 		"SELECT eq_id FROM equipments
									WHERE eq_master_id='".$receiver['receiver_eq_id']."'
									";
		
					$old_access_cards  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql3);
					
					if (isset($old_access_cards['0'])) {
							
						foreach($old_access_cards as $old_access_card) {
		
							$old_access_card		= new Thelist_Model_equipments($old_access_card['eq_id']);
							$old_access_card->set_eq_master_id(null);
							$old_access_card->log_equipment_action('access card replaced', "this access card is no longer in eq_id: ".$receiver['receiver_eq_id']." it was replaced by access card: ".$accesscard_serial."");
		
						}
					}
					
					//now we need to find out if the access card currently attached is already in inventory
					if (isset($access_card['accesscard_eq_id'])) {
							
						$new_access_card		= new Thelist_Model_equipments($access_card['accesscard_eq_id']);
						$new_access_card->set_eq_master_id($receiver['receiver_eq_id']);
						
						$get_software			= new Thelist_Directvstb_command_getsoftware($this->_device, $this->_current_arp_entry);
						$software_package		= $get_software->get_running_software_obj();
						
						$receiver				= new Thelist_Model_equipments($receiver['receiver_eq_id']);
						
						$receiver->set_eq_fqdn($this->_device->get_fqdn());
						$receiver->set_current_software_package($software_package);
						$receiver->update_static_interfaces();
						$receiver->create_api_auth($this->_device->get_device_authentication_credentials());
							
						$this->_equipment = $receiver;
		
					} else {
						
						//dident exist so we have to create it first
						$data = array(
													'eq_master_id'    		=>  $receiver['receiver_eq_id'],
													'eq_type_id'   			=>  '66',
													'eq_serial_number' 		=>  $accesscard_serial,
													'eq_fqdn'     			=>  'na',								
						);
		
						$trace 		= debug_backtrace();
						$method 	= $trace[0]["function"];
						$class		= get_class($this);
		
						$new_ac_eq_id = Zend_Registry::get('database')->insert_single_row('equipments',$data,$class,$method);
						
						$get_software			= new Thelist_Directvstb_command_getsoftware($this->_device, $this->_current_arp_entry);
						$software_package		= $get_software->get_running_software_obj();
						$receiver				= new Thelist_Model_equipments($receiver['receiver_eq_id']);
						$receiver->set_eq_fqdn($this->_device->get_fqdn());
						$receiver->set_current_software_package($software_package);
						$receiver->update_static_interfaces();
						$receiver->create_api_auth($this->_device->get_device_authentication_credentials());

						$this->_equipment = $receiver;
							
					}
				}
			} elseif (isset($access_card['accesscard_eq_id'])) {
					
				//known access card senario
				//the senario above captures any instance where both equipments are in the database and matching
					
				//now we need to find out if the receiver currently attached is already in inventory
				//this happens because we do not have the receiver id at the time it is scanned into inventory.
				//the master id would still have been set but we dont yet have the rid.
				if ($access_card['receiver_eq_id'] != false) {

					//now update the second serial of the receiver 
					$get_software			= new Thelist_Directvstb_command_getsoftware($this->_device, $this->_current_arp_entry);
					$software_package		= $get_software->get_running_software_obj();
					$receiver				= new Thelist_Model_equipments($access_card['receiver_eq_id']);
					$receiver->set_eq_fqdn($this->_device->get_fqdn());
					$receiver->set_second_serial_number($receiver_serial);
					$receiver->set_current_software_package($software_package);
					$receiver->update_static_interfaces();
					$receiver->create_api_auth($this->_device->get_device_authentication_credentials());

					$this->_equipment = $receiver;
		
				} else {
		
					
					//we are using the receiver id as serial number for the receivers that are being added through this method
					//find a way to get the real serial number and not the receiver id
					if ($this->_current_arp_entry != null) {

						$get_eq_type		= new Thelist_Directvstb_command_getequipmenttype($this->_device, $this->_current_arp_entry);
						$eq_type_obj		= $get_eq_type->get_eq_type_obj();
						
						$inventory			= new Thelist_Model_inventory();
						$new_receiver		= $inventory->create_equipment_from_type($eq_type_obj, $receiver_serial);
						
						$get_software			= new Thelist_Directvstb_command_getsoftware($this->_device, $this->_current_arp_entry);
						$software_package		= $get_software->get_running_software_obj();

						$new_receiver->set_eq_fqdn($this->_device->get_fqdn());
						$new_receiver->set_second_serial_number($receiver_serial);
						$new_receiver->set_current_software_package($software_package);
						$new_receiver->update_static_interfaces();
						$new_receiver->create_api_auth($this->_device->get_device_authentication_credentials());
						
						//now the receiver is created, we need to attach the access card
						$access_card_obj	= new Thelist_Model_equipments($access_card['accesscard_eq_id']);
						$access_card_obj->set_eq_master_id($new_receiver->get_eq_id());
						
						$this->_equipment = $new_receiver;

					}

					//if the model number is not in the database, then we have to throw an error so the user can enter the model information
					throw new exception('we know the access card, but the receiver is unknown and we need its model number', 64);
						
				}
					
			} else {
					
				//we did not find either access card or receiver, so we start from scratch
				
				//we are using the receiver id as serial number for the receivers that are being added through this method
				//find a way to get the real serial number and not the receiver id
				
				if ($this->_current_arp_entry != null) {
					
					$get_eq_type			= new Thelist_Directvstb_command_getequipmenttype($this->_device, $this->_current_arp_entry);
					$eq_type_obj			= $get_eq_type->get_eq_type_obj();
	
					$inventory				= new Thelist_Model_inventory();
					$new_receiver			= $inventory->create_equipment_from_type($eq_type_obj, $receiver_serial);
						
					$get_software			= new Thelist_Directvstb_command_getsoftware($this->_device, $this->_current_arp_entry);
					$software_package		= $get_software->get_running_software_obj();
						
					$new_receiver->set_eq_fqdn($this->_device->get_fqdn());
					$new_receiver->set_current_software_package($software_package);
					$new_receiver->set_second_serial_number($receiver_serial);
					$new_receiver->update_static_interfaces();
					$new_receiver->create_api_auth($this->_device->get_device_authentication_credentials());
		
					//now the receiver is created, we need to create the access card
					$access_eq_type_obj	= new Thelist_Model_equipmenttype(66);
					$new_access_card	= $inventory->create_equipment_from_type($access_eq_type_obj, $accesscard_serial);
					$new_access_card->set_eq_master_id($new_receiver->get_eq_id());
		
					$this->_equipment = $new_receiver;
									
				} else {
				
					//must have arp entry
					throw new exception('we are missing an arp entry', 16100);
				}
		
			}
		} else {
				
			throw new exception('cannot create equipment, we are missing a serial number', 63);
				
		}
	}
	
	public function set_current_arp_entry($arp_entry_obj)
	{
		$this->_current_arp_entry	= $arp_entry_obj;
	}
	
	public function get_equipment()
	{
		if ($this->_equipment == null) {
			$this->execute();
		}
		
		return $this->_equipment;
	}
}