<?php 

class thelist_model_fieldxmlapi
{
	
	private $_soapserver;
	
	public function __construct()
	{

		$this->logs				= Zend_Registry::get('logs');
		$this->_time			= Zend_Registry::get('time');
		$this->user_session 	= new Zend_Session_Namespace('userinfo');		
		$this->_soapserver		= new Zend_Soap_Server();
	}
	
	public function get_service_point_interfaces($service_plan_quote_map_obj, $options)
	{
		//create request for data
		$xml_request = new DOMDocument();
			
		$root = $xml_request->appendChild(
		$xml_request->createElement("Requesting_Data"));
			
		$sales_quote = $root->appendChild(
		$xml_request->createElement("service_point"));
			
		$sales_quote->appendChild(
		$xml_request->createElement("requested_data", 'service_plan_map_install'));
		
		if (isset($options['calendar_based_install'])) {
			$sales_quote->appendChild(
			$xml_request->createElement("calendar_based_install", $options['calendar_based_install']));
		}
		
		if (isset($options['if_ids'])) {
			$sales_quote->appendChild(
			$xml_request->createElement("if_ids", $options['if_ids']));
		}
			
		$sales_quote->appendChild(
		$xml_request->createElement("id", $service_plan_quote_map_obj->get_service_plan_quote_map_id()));
			
		$xml_request->formatOutput = true;
	
		//for testing
		$publicfieldxmlapi	= new Thelist_Model_publicfieldxmlapi();
		$post_install_validation	= new SimpleXMLElement($publicfieldxmlapi->getInformation($xml_request->saveXML()));
		return $post_install_validation;
				
				
				
		$client = new Zend_Soap_Client("http://martin-zend-dev.belairinternet.com/wsdl/fieldinstaller.wsdl");
		$return_data = $client->getInformation($xml_request->saveXML());

		$service_point_data	= new SimpleXMLElement($return_data);

		return $service_point_data;
			
	}
	
	public function get_install_task_device_install_status($task_obj)
	{
		//when we get a tablet application this will need to be rewritten to soap and xml
		
		
		//these are residential installs, so we are looking for a cpe device
		//either cpe router or cpe phone or CPE TV Receiver
		$sql = 	"SELECT em.eq_id FROM service_plan_quote_task_mapping spqtm
				INNER JOIN service_plan_quote_eq_type_mapping spqetm ON spqetm.service_plan_quote_map_id=spqtm.service_plan_quote_map_id
				INNER JOIN sales_quote_eq_type_map_equipment_mapping sqetmem ON sqetmem.service_plan_quote_eq_type_map_id=spqetm.service_plan_quote_eq_type_map_id
				INNER JOIN equipment_mapping em ON em.equipment_map_id=sqetmem.equipment_map_id
				WHERE spqtm.task_id='".$task_obj->get_task_id()."'
				";
		
		$installed_eq_ids  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		if (isset($installed_eq_ids['0'])) {
			
			$interface_connections 	= new Thelist_Model_interfaceconnections();
			$interfacepaths			= new Thelist_Model_interfacepaths();
			
			foreach ($installed_eq_ids as $eq_id) {
				
				$equipment[$eq_id['eq_id']]['equipment'] = new Thelist_Model_equipments($eq_id['eq_id']);

					if ($equipment[$eq_id['eq_id']]['equipment']->get_equipment_roles() != null) {
						
						if ($equipment[$eq_id['eq_id']]['equipment']->get_interfaces() != null) {
						
							foreach($equipment[$eq_id['eq_id']]['equipment']->get_interfaces() as $interface) {
									
								//does this interface have any interface connections
								if ($interface_connections->get_interface_connections($interface) != false) {
						
									$equipment[$eq_id['eq_id']]['wan_interface'] = $interface;
								}
							}
						}
						
					foreach ($equipment[$eq_id['eq_id']]['equipment']->get_equipment_roles() as $eq_role) {
				
						if ($eq_role->get_equipment_role_id() == 4) {
							//cpe router
							$equipment[$eq_id['eq_id']]['paths']	= $interfacepaths->get_cpe_wan_to_border_router_paths($equipment[$eq_id['eq_id']]['wan_interface']);
				
						} elseif ($eq_role->get_equipment_role_id() == 5) {
							//cpe receiver
								
						} elseif ($eq_role->get_equipment_role_id() == 6) {
							//cpe phone
						}
					}
				}
			}
			
			foreach ($equipment as $eq_live) {
				
				$path_tt = new Thelist_Model_troubleshooterpath();
				
				if (isset($eq_live['paths'])) {
					
					foreach($eq_live['paths'] as $path) {
						
						$path_tt->get_path_status($path);
						
					}
				}
			}

		} else {
			$return['status']	= 0;
		}
	}
	
		
	public function validate_install_post_and_provision($posted_data)
	{
		//create request for data

		$xml_request = new DOMDocument();
			
		$root = $xml_request->appendChild(
		$xml_request->createElement("Requesting_Data"));
			
		$task = $root->appendChild(
		$xml_request->createElement("task"));
			
		$task->appendChild(
		$xml_request->createElement("requested_data", 'validate_install_post_provision_equipment'));
			
		$task->appendChild(
		$xml_request->createElement("id", $posted_data['task_id']));
		$task->appendChild(
		$xml_request->createElement("if_id", $posted_data['if_id']));
		$task->appendChild(
		$xml_request->createElement("action", $posted_data['action']));
	
		if (isset($posted_data['unfulfilled_serial'])) {
			foreach($posted_data['unfulfilled_serial'] as $service_plan_id => $serial_number) {
				
				if (!isset($service_plan_map[$service_plan_id])) {
					
					$service_plan_map[$service_plan_id] = $task->appendChild(
					$xml_request->createElement("service_plan_quote_map"));
					
					$service_plan_map[$service_plan_id]->appendChild(
					$xml_request->createElement("service_plan_quote_map_id", $service_plan_id));
					
				}
				
				$service_plan_map[$service_plan_id]->appendChild(
				$xml_request->createElement("unfulfilled_serial", $serial_number));

			}
		}
		
		if (isset($posted_data['unfulfilled_receiver_id'])) {
			foreach($posted_data['unfulfilled_receiver_id'] as $service_plan_id => $receiver_id) {
		
				if (!isset($service_plan_map[$service_plan_id])) {
						
					$service_plan_map[$service_plan_id] = $task->appendChild(
					$xml_request->createElement("service_plan_quote_map"));
					
					$service_plan_map[$service_plan_id]->appendChild(
					$xml_request->createElement("service_plan_quote_map_id", $service_plan_id));
						
				}
		
				$service_plan_map[$service_plan_id]->appendChild(
				$xml_request->createElement("unfulfilled_receiver_id", $receiver_id));
		
			}
		}
		
		if (isset($posted_data['unfulfilled_access_card'])) {
			foreach($posted_data['unfulfilled_access_card'] as $service_plan_id => $access_card) {
		
				if (!isset($service_plan_map[$service_plan_id])) {
		
					$service_plan_map[$service_plan_id] = $task->appendChild(
					$xml_request->createElement("service_plan_quote_map"));
					
					$service_plan_map[$service_plan_id]->appendChild(
					$xml_request->createElement("service_plan_quote_map_id", $service_plan_id));
		
				}
		
				$service_plan_map[$service_plan_id]->appendChild(
				$xml_request->createElement("unfulfilled_access_card", $access_card));
		
			}
		}
		
		if (isset($posted_data['unfulfilled_model'])) {
			foreach($posted_data['unfulfilled_model'] as $service_plan_id => $eq_type_id) {
		
				if (!isset($service_plan_map[$service_plan_id])) {
		
					$service_plan_map[$service_plan_id] = $task->appendChild(
					$xml_request->createElement("service_plan_quote_map"));
					
					$service_plan_map[$service_plan_id]->appendChild(
					$xml_request->createElement("service_plan_quote_map_id", $service_plan_id));
		
				}
		
				$service_plan_map[$service_plan_id]->appendChild(
				$xml_request->createElement("unfulfilled_model", $eq_type_id));
		
			}
		}
		
		if (isset($posted_data['unfulfilled_use_other_device'])) {
			foreach($posted_data['unfulfilled_use_other_device'] as $service_plan_id => $unknown_device) {
		
				if (!isset($service_plan_map[$service_plan_id])) {
		
					$service_plan_map[$service_plan_id] = $task->appendChild(
					$xml_request->createElement("service_plan_quote_map"));
					
					$service_plan_map[$service_plan_id]->appendChild(
					$xml_request->createElement("service_plan_quote_map_id", $service_plan_id));
		
				}
		
				$service_plan_map[$service_plan_id]->appendChild(
				$xml_request->createElement("unfulfilled_use_other_device", $unknown_device));
		
			}
		}
		
		$i=0;
		if (isset($posted_data['unknown_device'])) {
			
			foreach($posted_data['unknown_device'] as $device_type => $device) {
		
				if ($device_type == 'others') {
					
					foreach ($device as $single_device) {
					
						$single_unknown_device[$i] = $task->appendChild(
						$xml_request->createElement("unknown_device"));
						
						$single_unknown_device[$i]->appendChild(
						$xml_request->createElement("mac_address", $single_device['mac_address']));
						
						$single_unknown_device[$i]->appendChild(
						$xml_request->createElement("ip_address", $single_device['ip_address']));
					
						$i++;
					}
					
				}
				
				if ($device_type == 'receivers') {
					
					foreach ($device as $accesscard_index => $single_device) {
							
						$single_unknown_device[$i] = $task->appendChild(
						$xml_request->createElement("unknown_receiver"));
					
						$single_unknown_device[$i]->appendChild(
						$xml_request->createElement("access_card", $single_device['access_card']));
					
						$single_unknown_device[$i]->appendChild(
						$xml_request->createElement("receiver_id", $single_device['receiver_id']));
						
						$single_unknown_device[$i]->appendChild(
						$xml_request->createElement("ip_address", $single_device['ip_address']));
							
						$i++;
					}
				}
			}
		}
			
		$xml_request->formatOutput = true;
		
		//for testing
		$publicfieldxmlapi			= new Thelist_Model_publicfieldxmlapi();
		$post_install_validation	= new SimpleXMLElement($publicfieldxmlapi->getInformation($xml_request->saveXML()));
		return $post_install_validation;
		
		$client = new Zend_Soap_Client("http://martin-zend-dev.belairinternet.com/wsdl/fieldinstaller.wsdl");
		$return_data = $client->getInformation($xml_request->saveXML());
		$post_install_validation	= new SimpleXMLElement($return_data);
			
		return $post_install_validation;
	}
	
	public function verify_equipment_on_sp_interface($task_obj, $interface_obj, $use_caching)
	{
		//create request for data
		$xml_request = new DOMDocument();
			
		$root = $xml_request->appendChild(
		$xml_request->createElement("Requesting_Data"));
			
		$sales_quote = $root->appendChild(
		$xml_request->createElement("task"));
			
		$sales_quote->appendChild(
		$xml_request->createElement("requested_data", 'verify_equipment_on_interface'));
			
		$sales_quote->appendChild(
		$xml_request->createElement("id", $task_obj->get_task_id()));
		$sales_quote->appendChild(
		$xml_request->createElement("interface", $interface_obj->get_if_id()));
		
		$sales_quote->appendChild(
		$xml_request->createElement("caching", $use_caching));
			
		$xml_request->formatOutput = true;
		
		

		//for testing
		$publicfieldxmlapi	= new Thelist_Model_publicfieldxmlapi();
		$post_install_validation	= new SimpleXMLElement($publicfieldxmlapi->getInformation($xml_request->saveXML()));
		return $post_install_validation;
	
		$client = new Zend_Soap_Client("http://martin-zend-dev.belairinternet.com/wsdl/fieldinstaller.wsdl");
		$return_data = $client->getInformation($xml_request->saveXML());

		$task_validation	= new SimpleXMLElement($return_data);
			
		return $task_validation;
			
	}
	
	
	
	public function getcalendartasks_xml($uid=null, $start_date_time=null, $end_date_time=null, $task_status=null, $task_id=null)
	{
		$xml_request = new DOMDocument();
			
		$root = $xml_request->appendChild(
		$xml_request->createElement("Requesting_Data"));
			
		$tasks = $root->appendChild(
		$xml_request->createElement("task"));	
		$tasks->appendChild(
		$xml_request->createElement("requested_data", 'calendartasks'));
		$tasks->appendChild(
		$xml_request->createElement("uid", $uid));
		$tasks->appendChild(
		$xml_request->createElement("start_date_time", $start_date_time));
		$tasks->appendChild(
		$xml_request->createElement("end_date_time", $end_date_time));
		$tasks->appendChild(
		$xml_request->createElement("task_status", $task_status));
		$tasks->appendChild(
		$xml_request->createElement("task_id", $task_id));
			
		$xml_request->formatOutput = true;
		
		//for testing
		$publicfieldxmlapi	= new Thelist_Model_publicfieldxmlapi();
		$post_install_validation	= new SimpleXMLElement($publicfieldxmlapi->getInformation($xml_request->saveXML()));
		return $post_install_validation;

		$client = new Zend_Soap_Client("http://martin-zend-dev.belairinternet.com/wsdl/fieldinstaller.wsdl");
		$return_data = $client->getInformation($xml_request->saveXML());

		$calendar_appointments = new SimpleXMLElement($return_data);
		
		return $calendar_appointments;

	}
	
	public function get_service_plan_install_help($task_obj)
	{
		//create request for data
		$xml_request = new DOMDocument();
			
		$root = $xml_request->appendChild(
		$xml_request->createElement("Requesting_Data"));
			
		$sales_quote = $root->appendChild(
		$xml_request->createElement("task"));
			
		$sales_quote->appendChild(
		$xml_request->createElement("requested_data", 'service_plan_install_help'));
			
		$sales_quote->appendChild(
		$xml_request->createElement("id", $task_obj->get_task_id()));

		$xml_request->formatOutput = true;
		
		//for testing
		$publicfieldxmlapi	= new Thelist_Model_publicfieldxmlapi();
		$post_install_validation	= new SimpleXMLElement($publicfieldxmlapi->getInformation($xml_request->saveXML()));
		//return $post_install_validation;
	
// 		$client = new Zend_Soap_Client("http://martin-zend-dev.belairinternet.com/wsdl/fieldinstaller.wsdl");
// 		$return_data = $client->getInformation($xml_request->saveXML());

	//	$task_detail	= new SimpleXMLElement($return_data);
		
		if ($post_install_validation->xpath("/Returned_Requested_Data/no_data_available") != false) {
			
			return false;
			
		} else {
			
			return $post_install_validation;
			
		}
	
	}

	public function get_end_user_detail($enduser_obj)
	{
			//create request for data
			$xml_request = new DOMDocument();
			
			$root = $xml_request->appendChild(
			$xml_request->createElement("Requesting_Data"));
			
			$sales_quote = $root->appendChild(
			$xml_request->createElement("end_user"));
			
			$sales_quote->appendChild(
			$xml_request->createElement("requested_data", 'from_end_user_service_id'));
			
			$sales_quote->appendChild(
			$xml_request->createElement("id", $enduser_obj->get_end_user_service_id()));
			
			$xml_request->formatOutput = true;
			
			//for testing
			$publicfieldxmlapi	= new Thelist_Model_publicfieldxmlapi();
			$post_install_validation	= new SimpleXMLElement($publicfieldxmlapi->getInformation($xml_request->saveXML()));
			return $post_install_validation;

			$client = new Zend_Soap_Client("http://martin-zend-dev.belairinternet.com/wsdl/fieldinstaller.wsdl");
			$return_data = $client->getInformation($xml_request->saveXML());

			$end_user_detail	= new SimpleXMLElement($return_data);
			
			return $end_user_detail;
			
	}
	
	public function get_service_point_interface_for_task($task_obj)
	{
		//create request for data
		$xml_request = new DOMDocument();
			
		$root = $xml_request->appendChild(
		$xml_request->createElement("Requesting_Data"));
			
		$sales_quote = $root->appendChild(
		$xml_request->createElement("service_point"));
			
		$sales_quote->appendChild(
		$xml_request->createElement("requested_data", 'task_service_point_interface'));
			
		$sales_quote->appendChild(
		$xml_request->createElement("id", $task_obj->get_task_id()));
			
		$xml_request->formatOutput = true;
		
		//for testing
		$publicfieldxmlapi	= new Thelist_Model_publicfieldxmlapi();
		$post_install_validation	= new SimpleXMLElement($publicfieldxmlapi->getInformation($xml_request->saveXML()));
		return $post_install_validation;
	
		$client = new Zend_Soap_Client("http://martin-zend-dev.belairinternet.com/wsdl/fieldinstaller.wsdl");
		$return_data = $client->getInformation($xml_request->saveXML());
	
		$service_point_detail	= new SimpleXMLElement($return_data);
			
		return $service_point_detail;
			
	}
	
	public function get_task_equipment($task_obj)
	{
		//create request for data
		$xml_request = new DOMDocument();
			
		$root = $xml_request->appendChild(
		$xml_request->createElement("Requesting_Data"));
			
		$sales_quote = $root->appendChild(
		$xml_request->createElement("equipment"));
			
		$sales_quote->appendChild(
		$xml_request->createElement("requested_data", 'task_required_equipment'));
			
		$sales_quote->appendChild(
		$xml_request->createElement("id", $task_obj->get_task_id()));
			
		$xml_request->formatOutput = true;
		
		//for testing
		$publicfieldxmlapi	= new Thelist_Model_publicfieldxmlapi();
		$post_install_validation	= new SimpleXMLElement($publicfieldxmlapi->getInformation($xml_request->saveXML()));
		return $post_install_validation;
	
		$client = new Zend_Soap_Client("http://martin-zend-dev.belairinternet.com/wsdl/fieldinstaller.wsdl");
		$return_data = $client->getInformation($xml_request->saveXML());

		$task_equipment	= new SimpleXMLElement($return_data);
			
		return $task_equipment;
			
	}
	
	public function get_calendar_appointment_detail($calendar_appointment_obj)
	{

			$xml_request = new DOMDocument();
			
			$root = $xml_request->appendChild(
			$xml_request->createElement("Requesting_Data"));
			
			$calendar_appointment1 = $root->appendChild(
			$xml_request->createElement("equipment"));
			
			$calendar_appointment1->appendChild(
			$xml_request->createElement("requested_data", 'calendar_appointment_equipment'));
			
			$calendar_appointment1->appendChild(
			$xml_request->createElement("id", $calendar_appointment_obj->get_calendar_appointment_id()));
			
			$calendar_appointment2 = $root->appendChild(
			$xml_request->createElement("end_user"));
				
			$calendar_appointment2->appendChild(
			$xml_request->createElement("requested_data", 'calendar_appointment_end_users'));
			
			$calendar_appointment2->appendChild(
			$xml_request->createElement("id", $calendar_appointment_obj->get_calendar_appointment_id()));
			
			$calendar_appointment3 = $root->appendChild(
			$xml_request->createElement("task"));
			
			$calendar_appointment3->appendChild(
			$xml_request->createElement("requested_data", 'calendar_appointment_tasks'));
			
			$calendar_appointment3->appendChild(
			$xml_request->createElement("id", $calendar_appointment_obj->get_calendar_appointment_id()));
				
			$xml_request->formatOutput = true;
			
			//for testing
			$publicfieldxmlapi	= new Thelist_Model_publicfieldxmlapi();
			$post_install_validation	= new SimpleXMLElement($publicfieldxmlapi->getInformation($xml_request->saveXML()));
			return $post_install_validation;

			$client = new Zend_Soap_Client("http://martin-zend-dev.belairinternet.com/wsdl/fieldinstaller.wsdl");
			$return_data = $client->getInformation($xml_request->saveXML());

			$calendar_appointment_detail_xml_obj		= new SimpleXMLElement($return_data);
			
			return $calendar_appointment_detail_xml_obj;
		
	}
	
	public function get_task_details($task_obj)
	{
		$xml_request = new DOMDocument();
			
		$root = $xml_request->appendChild(
		$xml_request->createElement("Requesting_Data"));
		
		$task = $root->appendChild(
		$xml_request->createElement("task"));
			
		$task->appendChild(
		$xml_request->createElement("requested_data", 'task_with_notes'));
			
		$task->appendChild(
		$xml_request->createElement("id", $task_obj->get_task_id()));
			
		$xml_request->formatOutput = true;
		
		//for testing
		$publicfieldxmlapi	= new Thelist_Model_publicfieldxmlapi();
		$post_install_validation	= new SimpleXMLElement($publicfieldxmlapi->getInformation($xml_request->saveXML()));
		return $post_install_validation;
		
		$client = new Zend_Soap_Client("http://martin-zend-dev.belairinternet.com/wsdl/fieldinstaller.wsdl");
		$return_data = $client->getInformation($xml_request->saveXML());
		
		$task	= new SimpleXMLElement($return_data);
			
		return $task;
	}
	
	
	public function appointments_for_technician_as_array($calendartasks_xml_obj)
	{
	
		$calendar_as_array			= $this->calendar_xml_as_array($calendartasks_xml_obj);
		$final_array				= array();
		foreach($calendar_as_array as $appointment) {
	
			$final_array[$appointment['calendar_appointment_id']]						= array();
	
			$final_array[$appointment['calendar_appointment_id']]['calendar_appointment_id']			= $appointment['calendar_appointment_id'];
			$final_array[$appointment['calendar_appointment_id']]['start_time']							= $this->_time->convert_mysql_datetime_to_am_pm($appointment['start_time']);
			$final_array[$appointment['calendar_appointment_id']]['end_time']							= $this->_time->convert_mysql_datetime_to_am_pm($appointment['end_time']);
	
			if (isset($appointment['mapped_service_plans'])) {
	
				$final_array[$appointment['calendar_appointment_id']]['appointment_type'] = 'Installation';
	
				foreach($appointment['mapped_service_plans'] as $mapped_service_plan) {
	
					$service_plan_quote_map_obj				= new Thelist_Model_serviceplanquotemap($mapped_service_plan);
					$sales_quote							= new Thelist_Model_salesquote($service_plan_quote_map_obj->get_sales_quote_id());
					$enduser_xml_obj						= $this->get_end_user_detail($sales_quote->get_end_user_service());
					$enduser_array							= $this->end_user_as_array($enduser_xml_obj);
					
	
	
					foreach ($enduser_array as $enduser_detail) {
						$final_array[$appointment['calendar_appointment_id']]['unit_number']			= $enduser_detail['unit']['unit_number'];
						$final_array[$appointment['calendar_appointment_id']]['address']				= $enduser_detail['unit']['street_number']." ".$enduser_detail['unit']['street_name']." ".$enduser_detail['unit']['street_type'];
						$final_array[$appointment['calendar_appointment_id']]['address2']				= $enduser_detail['unit']['city'].", ".$enduser_detail['unit']['state']." ".$enduser_detail['unit']['zipcode'];
	
						$final_array[$appointment['calendar_appointment_id']]['pri_contact_name']		= $enduser_detail['primary_contact']['first_name']." ".$enduser_detail['primary_contact']['last_name'];
						$final_array[$appointment['calendar_appointment_id']]['pri_contact_cell']		=$enduser_detail['primary_contact']['cell_phone'];
						$final_array[$appointment['calendar_appointment_id']]['pri_contact_home']		= $enduser_detail['primary_contact']['home_phone'];
						$final_array[$appointment['calendar_appointment_id']]['pri_contact_office']		= $enduser_detail['primary_contact']['office_phone'];
							
	
					}
				}
			}
	
			if (isset($appointment['tickets'])) {
	
				if (!isset($final_array[$appointment['calendar_appointment_id']]['appointment_type'])) {
						
					$final_array[$appointment['calendar_appointment_id']]['appointment_type'] = 'Trouble Ticket';
						
				} else {
						
					$final_array[$appointment['calendar_appointment_id']]['appointment_type'] = 'Trouble & Install';
						
				}
	
	
	
				foreach($appointment['tickets'] as $ticket) {
	
					$end_user_task_map						= Zend_Registry::get('database')->get_end_user_task_mapping()->fetchRow("end_user_task_map_id=".$ticket);
					$enduser_obj							= new Thelist_Model_enduserservice($end_user_task_map['end_user_service_id']);
	
					$enduser_xml_obj						= $this->get_end_user_detail($enduser_obj);
					$enduser_array							= $this->end_user_as_array($enduser_xml_obj);
					$phonenumber_util						= new Thelist_Utility_phonenumber();
	
	
					foreach ($enduser_array as $enduser_detail) {
						$final_array[$appointment['calendar_appointment_id']]['unit_number']			= $enduser_detail['unit']['unit_number'];
						$final_array[$appointment['calendar_appointment_id']]['address']				= $enduser_detail['unit']['street_number']." ".$enduser_detail['unit']['street_name']." ".$enduser_detail['unit']['street_type'];
						$final_array[$appointment['calendar_appointment_id']]['address2']				= $enduser_detail['unit']['city'].", ".$enduser_detail['unit']['state']." ".$enduser_detail['unit']['zipcode'];
	
						$final_array[$appointment['calendar_appointment_id']]['pri_contact_name']		= $enduser_detail['primary_contact']['first_name']." ".$enduser_detail['primary_contact']['last_name'];
						$final_array[$appointment['calendar_appointment_id']]['pri_contact_cell']		= $enduser_detail['primary_contact']['cell_phone'];
						$final_array[$appointment['calendar_appointment_id']]['pri_contact_home']		= $enduser_detail['primary_contact']['home_phone'];
						$final_array[$appointment['calendar_appointment_id']]['pri_contact_office']		= $enduser_detail['primary_contact']['office_phone'];
	
	
					}
				}
			}
		}
	
		return $final_array;
	
	}
	
	
	private function calendar_xml_as_array($xml_obj)
	{
		$appointments			 	= array();
		
		$i=0;
		foreach ($xml_obj->xpath("/Returned_Requested_Data/calendar_appointment") as $appointment) {
			$i++;
			$calendar_appointment_id						= $appointment->xpath("calendar_appointment_id");
			$scheduled_start_time							= $appointment->xpath("scheduled_start_time");
			$scheduled_end_time			 					= $appointment->xpath("scheduled_end_time");
		
			$appointments[$i]['calendar_appointment_id']		=	"$calendar_appointment_id[0]";
			$appointments[$i]['start_time']						=	"$scheduled_start_time[0]";
			$appointments[$i]['end_time']						=	"$scheduled_end_time[0]";
				
			
			//service plans (installs)
			foreach ($appointment->xpath("tasks/task/mapped_service_plans/service_plan_quote_map") as $service_plan_quote_map) {
				
				if (!isset($appointments[$i]['mapped_service_plans'])) {
					
					$appointments[$i]['mapped_service_plans']		=	array();
					
				}
				
				$service_plan_quote_map_id			= $service_plan_quote_map->xpath("service_plan_quote_map_id");
				
				$appointments[$i]['mapped_service_plans'][]	= "$service_plan_quote_map_id[0]";

			}
			
			//tickets (service)
			foreach ($appointment->xpath("tasks/task/tickets/end_user_task_map") as $task) {
			
				if (!isset($appointments[$i]['tickets'])) {
						
					$appointments[$i]['tickets']			=	array();
						
				}
			
				$end_user_task_map_id			= $task->xpath("end_user_task_map_id");
			
				$appointments[$i]['tickets'][]	= "$end_user_task_map_id[0]";
			
			}
		}

		return $appointments;

	}
	
	
	public function task_equipment_validation_as_array($xml_obj)
	{
		if ($xml_obj->xpath("/Returned_Requested_Data/task/error") == false) {
				
			foreach ($xml_obj->xpath("/Returned_Requested_Data/task") as $task) {
		
				$task_id											= $task->xpath("task_id");
				$interface_id										= $task->xpath("interface_id");
				
				$i=0;
				foreach ($task->xpath("equipment_new_task/equipment") as $new_equipment) {
		
					$eq_id													= $new_equipment->xpath("eq_id");
					$equipment_serial_number								= $new_equipment->xpath("equipment_serial_number");
					$equipment_type_name									= $new_equipment->xpath("equipment_type_name");
					$service_plan_quote_eq_type_map_id						= $new_equipment->xpath("service_plan_quote_eq_type_map_id");
					$access_card											= $new_equipment->xpath("access_card");
					$receiver_id											= $new_equipment->xpath("receiver_id");
					$error													= $new_equipment->xpath("error");
					
		
					$return_array["$task_id[0]"]['this_task'][$i]['eq_id']								= "$eq_id[0]";
					$return_array["$task_id[0]"]['this_task'][$i]['equipment_serial_number']			= "$equipment_serial_number[0]";
					$return_array["$task_id[0]"]['this_task'][$i]['equipment_type_name']				= "$equipment_type_name[0]";
					$return_array["$task_id[0]"]['this_task'][$i]['service_plan_quote_eq_type_map_id']	= "$service_plan_quote_eq_type_map_id[0]";
					
					if (isset($access_card[0]) && isset($receiver_id[0])) {
						
						$return_array["$task_id[0]"]['this_task'][$i]['access_card']			= "$access_card[0]";
						$return_array["$task_id[0]"]['this_task'][$i]['receiver_id']			= "$receiver_id[0]";
						
					}
					
					if(isset($error[0])) {
						
						$return_array["$task_id[0]"]['this_task'][$i]['error']			= "$error[0]";
						
					}
					
					$i++;
				}
				
				$i=0;
				foreach ($task->xpath("equipment_old_tasks/equipment") as $new_equipment) {
				
					$eq_id													= $new_equipment->xpath("eq_id");
					$equipment_serial_number								= $new_equipment->xpath("equipment_serial_number");
					$equipment_type_name									= $new_equipment->xpath("equipment_type_name");
					$service_plan_quote_eq_type_map_id						= $new_equipment->xpath("service_plan_quote_eq_type_map_id");
					$access_card											= $new_equipment->xpath("access_card");
					$receiver_id											= $new_equipment->xpath("receiver_id");
					$error													= $new_equipment->xpath("error");
						
				
					$return_array["$task_id[0]"]['old_task'][$i]['eq_id']								= "$eq_id[0]";
					$return_array["$task_id[0]"]['old_task'][$i]['equipment_serial_number']				= "$equipment_serial_number[0]";
					$return_array["$task_id[0]"]['old_task'][$i]['equipment_type_name']					= "$equipment_type_name[0]";
					$return_array["$task_id[0]"]['old_task'][$i]['service_plan_quote_eq_type_map_id']	= "$service_plan_quote_eq_type_map_id[0]";
						
					if (isset($access_card[0]) && isset($receiver_id[0])) {
				
						$return_array["$task_id[0]"]['old_task'][$i]['access_card']			= "$access_card[0]";
						$return_array["$task_id[0]"]['old_task'][$i]['receiver_id']			= "$receiver_id[0]";
				
					}
						
					if(isset($error[0])) {
				
						$return_array["$task_id[0]"]['old_task'][$i]['error']			= "$error[0]";
				
					}
						
					$i++;
				}
				
				$i=0;
				foreach ($task->xpath("equipment_other_tasks/equipment") as $new_equipment) {
				
					$eq_id													= $new_equipment->xpath("eq_id");
					$equipment_serial_number								= $new_equipment->xpath("equipment_serial_number");
					$equipment_type_name									= $new_equipment->xpath("equipment_type_name");
					$access_card											= $new_equipment->xpath("access_card");
					$receiver_id											= $new_equipment->xpath("receiver_id");
					$error													= $new_equipment->xpath("error");
				
					$return_array["$task_id[0]"]['other_task'][$i]['eq_id']						= "$eq_id[0]";
					$return_array["$task_id[0]"]['other_task'][$i]['equipment_serial_number']	= "$equipment_serial_number[0]";
					$return_array["$task_id[0]"]['other_task'][$i]['equipment_type_name']		= "$equipment_type_name[0]";
						
					if (isset($access_card[0]) && isset($receiver_id[0])) {
				
						$return_array["$task_id[0]"]['other_task'][$i]['access_card']			= "$access_card[0]";
						$return_array["$task_id[0]"]['other_task'][$i]['receiver_id']			= "$receiver_id[0]";
				
					}
					
					if(isset($error[0])) {
					
						$return_array["$task_id[0]"]['other_task'][$i]['error']			= "$error[0]";
					
					}
					
					$i++;
				}
				
				$i=0;
				foreach ($task->xpath("unknown_receivers/device") as $new_equipment) {

					$access_card											= $new_equipment->xpath("access_card");
					$receiver_id											= $new_equipment->xpath("receiver_id");
					$ip_address												= $new_equipment->xpath("ip_address");

					$return_array["$task_id[0]"]['unknown_receivers'][$i]['access_card']		= "$access_card[0]";
					$return_array["$task_id[0]"]['unknown_receivers'][$i]['receiver_id']		= "$receiver_id[0]";
					$return_array["$task_id[0]"]['unknown_receivers'][$i]['ip_address']			= "$ip_address[0]";
					$i++;
				}
				
				$i=0;
				foreach ($task->xpath("unknown_devices/unknown_device") as $new_equipment) {
				
					$mac_address									= $new_equipment->xpath("mac_address");
					$ip_address										= $new_equipment->xpath("ip_address");
				
					$return_array["$task_id[0]"]['unknown_device'][$i]['mac_address']		= "$mac_address[0]";
					$return_array["$task_id[0]"]['unknown_device'][$i]['ip_address']		= "$ip_address[0]";
				$i++;
				}
				
				$i=0;
				foreach ($task->xpath("missing_requirements/missing_requirement") as $requirement) {
				
					$equipment_group_name									= $requirement->xpath("equipment_group_name");
					$service_plan_quote_eq_type_map_id						= $requirement->xpath("service_plan_quote_eq_type_map_id");
				
					$return_array["$task_id[0]"]['unfulfilled_requirement'][$i]['equipment_group_name']					= "$equipment_group_name[0]";
					$return_array["$task_id[0]"]['unfulfilled_requirement'][$i]['service_plan_quote_eq_type_map_id']	= "$service_plan_quote_eq_type_map_id[0]";
					$i++;
				}
				
				$i=0;
				foreach ($task->xpath("trouble_devices/trouble_device") as $trouble_equipment) {
				
					$mac_address									= $trouble_equipment->xpath("mac_address");
					$ip_address										= $trouble_equipment->xpath("ip_address");
					$trouble_text									= $trouble_equipment->xpath("trouble_text");
					$exception_id									= $trouble_equipment->xpath("exception_id");
				
					$return_array["$task_id[0]"]['trouble_device'][$i]['mac_address']		= "$mac_address[0]";
					$return_array["$task_id[0]"]['trouble_device'][$i]['ip_address']		= "$ip_address[0]";
					$return_array["$task_id[0]"]['trouble_device'][$i]['trouble_text']		= "$trouble_text[0]";
					$return_array["$task_id[0]"]['trouble_device'][$i]['exception_id']		= "$exception_id[0]";
					$i++;
				}
			}
		} else {

			//if there is an error
			$error_id	= $xml_obj->xpath("/Returned_Requested_Data/task/error");
			$return_array['error']	= "$error_id[0]";
		
		}

		return $return_array;
	}
	
	public function service_point_interface_xml_as_array($xml_obj)
	{
		
		$interfaces			 	= array();
		$i=0;
		if ($xml_obj->xpath("/Returned_Requested_Data/service_point/error") == false) {
			
			foreach ($xml_obj->xpath("/Returned_Requested_Data/service_point/patch_panel") as $patch_panel) {

				$serial_number											= $patch_panel->xpath("serial_number");
				$interfaces["$serial_number[0]"] 						= array();
				
				foreach ($patch_panel->xpath("interface") as $sp_interface) {

					$if_id												= $sp_interface->xpath("if_id");
					$if_name											= $sp_interface->xpath("if_name");
					$interface_operation								= $sp_interface->xpath("interface_operation");
					$connect_status										= $sp_interface->xpath("connect_status");

					$interfaces["$serial_number[0]"][$i]['if_id']									= "$if_id[0]";
					$interfaces["$serial_number[0]"][$i]['if_name']									= "$if_name[0]";
					$interfaces["$serial_number[0]"][$i]['operation']								= "$interface_operation[0]";
					$interfaces["$serial_number[0]"][$i]['status']									= "$connect_status[0]";
					
					if ($sp_interface->xpath("service_plan_quote_maps") != false) {
						
						foreach($sp_interface->xpath("service_plan_quote_maps/service_plan_quote_map") as $service_plan_quote_map) {
							
							$service_plan_quote_map_id											= $service_plan_quote_map->xpath("service_plan_quote_map_id");
							$interfaces["$serial_number[0]"][$i]['service_plan_quote_map_id'][]		= "$service_plan_quote_map_id[0]";
							
						}
					}
					$i++;
				}
			}
		}
		
		return $interfaces;
	}
	
	private function end_user_as_array($xml_obj)
	{
		$i=0;
		foreach ($xml_obj->xpath("/Returned_Requested_Data/end_user") as $end_user_xml) {
			$i++;
		
			$end_user_array[$i] = array();
			$phonenumber_util						= new Thelist_Utility_phonenumber();
		
			$unit_number		= $end_user_xml->xpath("unit/unit_number");
			$unit_name			= $end_user_xml->xpath("unit/unit_name");
		
			$street_number		= $end_user_xml->xpath("unit/streetnumber");
			$street_name		= $end_user_xml->xpath("unit/streetname");
			$street_type		= $end_user_xml->xpath("unit/streettype");
			$city				= $end_user_xml->xpath("unit/city");
			$state				= $end_user_xml->xpath("unit/state");
			$zipcode			= $end_user_xml->xpath("unit/zipcode");
		
			$end_user_array[$i]['unit'] = array();
			$end_user_array[$i]['unit']['unit_number']			= "$unit_number[0]";
			$end_user_array[$i]['unit']['unit_name']			= "$unit_name[0]";
			$end_user_array[$i]['unit']['street_number']		= "$street_number[0]";
			$end_user_array[$i]['unit']['street_name']			= "$street_name[0]";
			$end_user_array[$i]['unit']['street_type']			= "$street_type[0]";
			$end_user_array[$i]['unit']['city']					= "$city[0]";
			$end_user_array[$i]['unit']['state']				= "$state[0]";
			$end_user_array[$i]['unit']['zipcode']				= "$zipcode[0]";
		
			$pri_title		 	= $end_user_xml->xpath("contacts/primary_contact/title");
			$pri_first_name		= $end_user_xml->xpath("contacts/primary_contact/first_name");
			$pri_last_name		= $end_user_xml->xpath("contacts/primary_contact/last_name");
			$pri_cell_phone		= $end_user_xml->xpath("contacts/primary_contact/cell_phone");
			$pri_home_phone		= $end_user_xml->xpath("contacts/primary_contact/home_phone");
			$pri_office_phone	= $end_user_xml->xpath("contacts/primary_contact/office_phone");
		
		
			$end_user_array[$i]['primary_contact'] = array();
			$end_user_array[$i]['primary_contact']['title']						= "$pri_title[0]";
			$end_user_array[$i]['primary_contact']['first_name']				= "$pri_first_name[0]";
			$end_user_array[$i]['primary_contact']['last_name']					= "$pri_last_name[0]";
			$end_user_array[$i]['primary_contact']['cell_phone']				= $phonenumber_util->convert_mysql_number_to_standard_display("$pri_cell_phone[0]");
			$end_user_array[$i]['primary_contact']['home_phone']				= $phonenumber_util->convert_mysql_number_to_standard_display("$pri_home_phone[0]");
			$end_user_array[$i]['primary_contact']['office_phone']				= $phonenumber_util->convert_mysql_number_to_standard_display("$pri_office_phone[0]");

		}

		return $end_user_array;	
	}
	
	public function get_appoinment_detail_as_array($xml_obj)
	{

		$final_array 						= array();
		$tasks_array						= $this->get_tasks_as_array($xml_obj);
		$end_user_array						= $this->end_user_as_array($xml_obj);
		$final_array['end_users']			= array();
		$phonenumber_util					= new Thelist_Utility_phonenumber();
		
		foreach ($end_user_array as $enduser) {
		
			$final_array['end_users'][]	= $enduser;
		
		}

		$i=0;
		foreach ($tasks_array as $task) {
			
			
			
			$final_array['tasks'][$i]												= array();
			$final_array['tasks'][$i]['task_id']									= $task['task_id'];
			$final_array['tasks'][$i]['task_name']									= $task['task_name'];
			$final_array['tasks'][$i]['task_status']								= $task['task_status'];
			
			if(isset($task['notes'])) {
				
				$final_array['tasks'][$i]['task_notes']								= array();
				$final_array['tasks'][$i]['task_notes']								= $task['notes'];
				
			}
			
			$task_obj																= new Thelist_Model_tasks($task['task_id']);
			$task_equipment_xml														= $this->get_task_equipment($task_obj);
			
			$equipment_array						= $this->get_required_equipment_as_array($task_equipment_xml);
			
			if (is_array($equipment_array)) {
			
				$final_array['tasks'][$i]['required_equipment']			= array();
			
				foreach($equipment_array['equipment_name'] as $single_equipment) {
			
					$final_array['tasks'][$i]['required_equipment'][]	= $single_equipment;
			
				}
			}

	
			$i++;
		}

		return $final_array;
	}
	
	public function get_required_equipment_as_array($xml_obj)
	{
		
		$required_equipment = array();
		$i=0;
		foreach ($xml_obj->xpath("required_equipment") as $required_equipment_xml) {
			$i++;

			$equipment_name		 		= $required_equipment_xml->xpath("equipment_group_name");
			$eq_type_group_id		 	= $required_equipment_xml->xpath("eq_type_group_id");
		
			if (!preg_match("/Generic/", "$equipment_name[0]", $empty)) {

				$required_equipment['equipment_name'][$i]			= "$equipment_name[0]";
				
				if (isset($eq_type_group_id[0])) {
					
					$required_equipment['eq_type_group_ids'][$i]		= "$eq_type_group_id[0]";
					
				} else {
					
					$required_equipment['eq_type_group_ids'][$i]		= "";
					
				}
				
				
			}
		}

		return $required_equipment;
		
	}
	
	public function validate_install_post_and_provision_as_array($xml_obj)
	{

		if ($xml_obj->xpath("status/error") != false) {
			
			$error_id	= $xml_obj->xpath("status/error");
			
			$return_array['error']	= "$error_id[0]";
			
			return $return_array;

		} elseif ($xml_obj->xpath("status/success") != false) {
			
			$success_id	= $xml_obj->xpath("status/success");

			$return_array['success']	= "$success_id[0]";
			
			return $return_array;
						
		}

	}

	public function get_service_plan_install_help_as_array($xml_obj)
	{
	
		$service_plan_help = array();

		if ($xml_obj->xpath("service_plan/help_images") != false) {
			foreach ($xml_obj->xpath("service_plan/help_images") as $service_plan_help_image_xml) {
	
				$help_image		 					= $service_plan_help_image_xml->xpath("help_image");
				$service_plan_help['images'][]		= "$help_image[0]";
	
				}
				
			return $service_plan_help;
		} else {
			
			return false;
			
		}
	}
	
	public function get_tasks_as_array($xml_obj)
	{

		$i=0;
		foreach ($xml_obj->xpath("task") as $task) {
			$i++;
			
			$task_id	 						= $task->xpath("task_id");
			$task_name	 						= $task->xpath("task_name");
			$task_status	 					= $task->xpath("task_status");
			$task_install_status				= $task->xpath("task_install_status");
			$task_install_progress_id			= $task->xpath("task_install_progress_id");
			
			$tasks_array[$i] = array();
			
			$tasks_array[$i]['task_id']						=	"$task_id[0]";
			$tasks_array[$i]['task_name']					=	"$task_name[0]";
			$tasks_array[$i]['task_status']					=	"$task_status[0]";
			
			if ($task_install_status != false) {
				
				$tasks_array[$i]['task_install_status']			=	"$task_install_status[0]";
				$tasks_array[$i]['task_install_progress_id']	=	"$task_install_progress_id[0]";
				
			} else {
				
				$tasks_array[$i]['task_install_status']			=	'';
				$tasks_array[$i]['task_install_progress_id']	=	'';
				
			}

			foreach($task->xpath("service_plan_quote_maps/service_plan_quote_map") as $service_plan_quote_map) {
			
				if (!isset($tasks_array[$i]['service_plan_quote_maps'])) {
						
					$tasks_array[$i]['service_plan_quote_maps'] = array();
						
				}
			
				$service_plan_quote_map_id	 					= $service_plan_quote_map->xpath("service_plan_quote_map_id");
			
				$tasks_array[$i]['service_plan_quote_maps'][]	= "$service_plan_quote_map_id[0]";
			
			}
			
			
	
			foreach($task->xpath("notes/note") as $note) {
				
				if (!isset($tasks_array[$i]['notes'])) {
					
					$tasks_array[$i]['notes'] = array();
					
				}

				$note_text	 					= $note->xpath("note_text");
				
				$tasks_array[$i]['notes'][]	= "$note_text[0]";
				
			}
		}
	
		return $tasks_array;
	
	}
	
	
	

}
?>