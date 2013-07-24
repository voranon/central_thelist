<?php

//exception codes 300-399

class thelist_model_publicfieldxmlapi
{
	private $database;
	private $logs;
	private $user_session;
	private $_time;
	private $_xml_response=null;
	private $_root_return_element=null;
	private $_cache_14400;
	
	
	public function __construct()
	{

		$this->logs				= Zend_Registry::get('logs');
		$this->_time			= Zend_Registry::get('time');
		$this->_cache_14400 	= Zend_Registry::get('filecache14400');
		
		//temp. should not be here once testing is complete
		//only SOAP controller should perform this
		$autoLoader = Zend_Loader_Autoloader::getInstance();
		$autoLoader->setFallbackAutoloader(true);
		Zend_Session::start();
		
	}
	
	public function postInformation($xml_post)
	{
	
		$xml_input_data = new SimpleXMLElement($xml_post);
	
		$this->_xml_response = new DOMDocument();
	
		$this->_root_return_element = $this->_xml_response->appendChild(
		$this->_xml_response->createElement("Response_Data"));
	
		//for location requests.
		if ($xml_input_data->xpath("/Providing_Data/location") != false) {
				
			foreach($xml_input_data->xpath("/Providing_Data/location") as $location) {
	
				$provided_data = $location->xpath("provided_data");
	
				if ("$provided_data[0]" == 'user_login_screen') {
	
					$this->saveLocation('user_login_screen', $location);
	
				} 
			}
		}
	}
	
	public function getInformation($xml_request)
	{
		
		$xml_input_data = new SimpleXMLElement($xml_request);
		
		$this->_xml_response = new DOMDocument();
		
		$this->_root_return_element = $this->_xml_response->appendChild(
		$this->_xml_response->createElement("Returned_Requested_Data"));

		//for all enduser related requests.
		if ($xml_input_data->xpath("/Requesting_Data/end_user") != false) {
			
			foreach($xml_input_data->xpath("/Requesting_Data/end_user") as $end_user) {
				
				$id = $end_user->xpath("id");
				$requested_data = $end_user->xpath("requested_data");
				
				if ("$requested_data[0]" == 'calendar_appointment_end_users') {
				
					$this->getEnduserDetail('calendar_appointment_end_users', "$id[0]");
				
				} elseif ("$requested_data[0]" == 'from_end_user_service_id') {
					
					$this->getEnduserDetail('from_end_user_service_id', "$id[0]");
					
				}
			}
		} 
		
		
	
		
		
		//equipment requests
		if ($xml_input_data->xpath("/Requesting_Data/equipment") != false) {
			
			foreach($xml_input_data->xpath("/Requesting_Data/equipment") as $equipment) {
			
				$id = $equipment->xpath("id");
				$requested_data = $equipment->xpath("requested_data");
				
				if ("$requested_data[0]" == 'task_required_equipment') {
				
					$this->getCalendarAppointmentEquipment('task_required_equipment', "$id[0]");
				
				} 
			}
		}
		
		
		
		//task requests
		if ($xml_input_data->xpath("/Requesting_Data/task") != false) {
				
			foreach($xml_input_data->xpath("/Requesting_Data/task") as $equipment) {
					
				$requested_data = $equipment->xpath("requested_data");
				$id = $equipment->xpath("id");
					
				if ("$requested_data[0]" == 'calendartasks') {
					
					$uid 					= $equipment->xpath("uid");
					$start_date_time 		= $equipment->xpath("start_date_time");
					$end_date_time 			= $equipment->xpath("end_date_time");
					$task_status 			= $equipment->xpath("task_status");
					$task_id				= $equipment->xpath("task_id");
		
					$this->getTaskDetail('calendartasks', "$uid[0]", "$start_date_time[0]", "$end_date_time[0]", "$task_status[0]", "$task_id[0]");
		
				} elseif ("$requested_data[0]" == 'calendar_appointment_tasks') {
		
					$this->getTaskDetail('calendar_appointment_tasks', null, null, null, null, null, "$id[0]");
		
				} elseif ("$requested_data[0]" == 'service_plan_install_help') {
		
					$this->getTaskHelp('service_plan_install_help', "$id[0]");
		
				} elseif ("$requested_data[0]" == 'task_with_notes') {
		
					$this->getTaskDetail('task_with_notes', null, null, null, null, null, "$id[0]");
		
				} elseif ("$requested_data[0]" == 'verify_equipment_on_interface') {
					
					$interface = $equipment->xpath("interface");
					$caching = $equipment->xpath("caching");
					
					$this->verifyEquipmentOnInterface('verify_equipment_on_interface', "$interface[0]", "$id[0]", "$caching[0]");

				} elseif ("$requested_data[0]" == 'validate_install_post_provision_equipment') {
					
					$this->validateInstallProvisionEquipment('validate_install_post_provision_equipment', $equipment);

				}
			}
		}
		
		//service point requests
		if ($xml_input_data->xpath("/Requesting_Data/service_point") != false) {
		
			foreach($xml_input_data->xpath("/Requesting_Data/service_point") as $service_point) {
					
				$requested_data = $service_point->xpath("requested_data");
				$id = $service_point->xpath("id");
				
				$options = false;
				
				if ($service_point->xpath("calendar_based_install") != false) {
					 $calendar_based_install = $service_point->xpath("calendar_based_install");
					 $options['calendar_based_install'] = "$calendar_based_install[0]";
				}
				
				if ($service_point->xpath("if_ids") != false) {
					$if_ids = $service_point->xpath("if_ids");
					 $interface_ids_string	= "$if_ids[0]";
					
					if (preg_match("/,/", $interface_ids_string)) {
						$options['if_ids'] 	= explode(",", $interface_ids_string);
					} else {
						$options['if_ids']['0']	= $interface_ids_string;
					}
				}

				if ("$requested_data[0]" == 'service_plan_map_install') {
		
					$this->servicePoint('service_plan_map_install', "$id[0]", $options);
				}
			}
		}

		$this->_xml_response->formatOutput = true;
		return $this->_xml_response->saveXML();
	}
	
	private function saveLocation($provided_data, $location_xml_obj)
	{
		if ($provided_data == 'user_login_screen') {
				
			$latitude						= $location_xml_obj->xpath("latitude");
			$longitude						= $location_xml_obj->xpath("longitude");
			$accuracy						= $location_xml_obj->xpath("accuracy");
			$altitude						= $location_xml_obj->xpath("altitude");
			$altitudeaccuracy				= $location_xml_obj->xpath("altitudeaccuracy");
			$heading						= $location_xml_obj->xpath("heading");
			$speed							= $location_xml_obj->xpath("speed");
			$ip_address						= $location_xml_obj->xpath("ip_address");
			
			$sql = 	"SELECT item_id FROM items 
					WHERE item_type='user_location_type'
					AND item_name='login_screen'
					";
			
			$user_location_type  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
			$this->logs->loglocation($user_location_type, "$latitude[0]", "$longitude[0]", "$accuracy[0]", "$altitude[0]", "$altitudeaccuracy[0]", "$heading[0]", "$speed[0]", "$ip_address[0]");
			
			
		} 
	}
	
	
	private function validateInstallProvisionEquipment($request_type, $input_xml_obj)
	{

		if ($request_type == 'validate_install_post_provision_equipment') {

			//dig out the common elements
			$task_id 					= $input_xml_obj->xpath("id");
			$if_id 						= $input_xml_obj->xpath("if_id");
			
			$task_obj			= new Thelist_Model_tasks("$task_id[0]");
			$interface_obj		= new Thelist_Model_equipmentinterface("$if_id[0]");
			
			//build the array for validation
			if ($input_xml_obj->xpath("service_plan_quote_map") != false) {
				
				foreach($input_xml_obj->xpath("service_plan_quote_map") as $service_plan_map) {
					
					$service_plan_quote_map_id			= $service_plan_map->xpath("service_plan_quote_map_id");
					$unfulfilled_serial					= $service_plan_map->xpath("unfulfilled_serial");
					$unfulfilled_receiver_id			= $service_plan_map->xpath("unfulfilled_receiver_id");
					$unfulfilled_access_card			= $service_plan_map->xpath("unfulfilled_access_card");
					$unfulfilled_model					= $service_plan_map->xpath("unfulfilled_model");
					$unfulfilled_use_other_device		= $service_plan_map->xpath("unfulfilled_use_other_device");
					
					$validation_array['unfulfilled_serial']["$service_plan_quote_map_id[0]"]				= "$unfulfilled_serial[0]";
					$validation_array['unfulfilled_receiver_id']["$service_plan_quote_map_id[0]"]			= "$unfulfilled_receiver_id[0]";
					$validation_array['unfulfilled_access_card']["$service_plan_quote_map_id[0]"]			= "$unfulfilled_access_card[0]";
					$validation_array['unfulfilled_model']["$service_plan_quote_map_id[0]"]					= "$unfulfilled_model[0]";
					$validation_array['unfulfilled_use_other_device']["$service_plan_quote_map_id[0]"]		= "$unfulfilled_use_other_device[0]";
				}

			}
			
			if ($input_xml_obj->xpath("unknown_receiver") != false) {
				
				foreach($input_xml_obj->xpath("unknown_receiver") as $unknown_receiver) {
					
					$access_card					= $unknown_receiver->xpath("access_card");
					$receiver_id					= $unknown_receiver->xpath("receiver_id");
					$ip_address						= $unknown_receiver->xpath("ip_address");
					
					$validation_array['unknown_device']['receivers']["$access_card[0]"]['receiver_id']		= "$receiver_id[0]";
					$validation_array['unknown_device']['receivers']["$access_card[0]"]['access_card']		= "$access_card[0]";
					$validation_array['unknown_device']['receivers']["$access_card[0]"]['ip_address']		= "$ip_address[0]";
					
				}
				
			}
			
			if ($input_xml_obj->xpath("unknown_device") != false) {
			
				$i=0;
				foreach($input_xml_obj->xpath("unknown_device") as $unknown_receiver) {
						
					$mac_address					= $unknown_receiver->xpath("mac_address");
					$ip_address						= $unknown_receiver->xpath("ip_address");
						
					$validation_array['unknown_device']['others']["$i"]['mac_address']		= "$mac_address[0]";
					$validation_array['unknown_device']['others']["$i"]['ip_address']		= "$ip_address[0]";
					
					$i++;
				}
			}
			
			//create the first element of the return XML
			$status = $this->_root_return_element->appendChild(
			$this->_xml_response->createElement("status"));
			
			try {

				$inventory_obj					= new Thelist_Model_inventory();
				$equipment_to_be_connected 		= array();
				
				if (isset($validation_array)) {
						
					//first validate the user inputted information
					$inventory_obj->validate_connect_post_create_equipment($validation_array);

					//then turn that into new equipment
					$equipment_to_be_connected = $inventory_obj->create_equipment_from_unknown_devices($validation_array);
					
				}

				//at this point we need to bring back all the equipment that was detected first time the port was scanned if it is still cached
				//because now that every requirement is turned into equipment we can for the first time get a nice array of all the equipment 
				//that will be installed in the unit on this interface. 
				//we use caching for this fetch
				
				$requirements		= $inventory_obj->verify_task_service_point_install($task_obj, $interface_obj, true);

				if ($requirements != false) {
					
					$equipment_to_be_connected	= $this->append_install_equipment_to_new_equipment_array($requirements, $equipment_to_be_connected);

				}

				//we do not want to use auto commit for this transaction, so if we fail we can roll back the changes
				Zend_Registry::get('database')->get_thelist_adapter()->beginTransaction();
			
				//get the service plan that belongs to this task
				$service_plan_quote_map_row	=	Zend_Registry::get('database')->get_service_plan_quote_task_mapping()->fetchRow("task_id=".$task_obj->get_task_id());
				
				//get the service plan quote map obj
				$service_plan_quote_map_obj	= new Thelist_Model_serviceplanquotemap($service_plan_quote_map_row['service_plan_quote_map_id']);
				
				$cache_id1 = "servicepointresourcelocator_class_" . $service_plan_quote_map_obj->get_service_plan_quote_map_id();
				$cache_id2 = "servicepointresourcelocator_sp_interface_" . $service_plan_quote_map_obj->get_service_plan_quote_map_id();
				
				//if there is a cache value use it, otherwise create it again.
				if ($this->_cache_14400->load($cache_id1) &&  $this->_cache_14400->load($cache_id2)) {
				
					$spclass			= $this->_cache_14400->load($cache_id1);
					$spinterface		= $this->_cache_14400->load($cache_id2);
						
				} else {
						
					$spclass		= new Thelist_Model_servicepointresourcelocator();
					$spinterface	= $spclass->get_service_point_interface_for_service_plan_quote_map($service_plan_quote_map_obj);
						
				}
				
				//verify that the interface is still the same
				if ($interface_obj->get_if_id() != $spinterface->get_if_id()) {
					
					throw new exception('the service point interface scanned for equipment and the one located here is not the same, rescan interface', 301);
					
				}
				
				//create all the non serialized equipment for the install task and append it to the array
				$equipment_to_be_connected = $inventory_obj->create_non_serialized_equipment_from_task($task_obj, $equipment_to_be_connected);

				//get the service plans that are covered by this interface and create an array of the serviceplanquoteeqtypemap requirements and pair them with the equipment 
				//that fulfills that requirement.
				$i=0;
				foreach($spclass->get_service_plans_serviced() as $service_plan_quote) {

					if ($service_plan_quote->get_service_plan_quote_eq_types() != null) {

						foreach ($service_plan_quote->get_service_plan_quote_eq_types() as $equipment_required) {
						
							foreach ($equipment_to_be_connected as $service_plan_quote_eq_type_map_id => $equipment_connected) {
							
								if ($service_plan_quote_eq_type_map_id != 'other') {
									
									if ($equipment_required->get_service_plan_quote_eq_type_map_id() == $service_plan_quote_eq_type_map_id) {
										
										$equipment_for_connect['equipment'][$i]							=  $equipment_connected;
										$equipment_for_connect['service_plan_quote_eq_type_map'][$i] 	= new Thelist_Model_serviceplanquoteeqtypemap($service_plan_quote_eq_type_map_id);
										
										$i++;
									}
								}
							}
						}
					}
				}

				//now connect all this equipment according to the service plan specification
				$interfaceconnections_obj	= new Thelist_Model_interfaceconnections();

				//this method may replace redundant equipment i.e. generic splitters that have already been installed for the unit we need to reuse them and not setup additional
				if (isset($equipment_for_connect)) {
					$equipment_for_connect = $interfaceconnections_obj->connect_equipment_in_task_to_service_point($spinterface, $equipment_for_connect);
				} else {
					throw new exception('there is no equipment to connect', 304);
				}
				
				

				//do the mapping of service plans and equipment to unit
				$inventory_obj->map_equipment_at_end_of_install($equipment_for_connect);

				//now provision the database with all the options from the service plan map
				$i=0;
				foreach ($equipment_for_connect['equipment'] as $equipment_for_provisioning) {
					
					$default_plan	= $equipment_for_connect['service_plan_quote_eq_type_map'][$i]->get_service_plan_eq_type_map()->get_eq_default_prov_plan_id();

					if ($default_plan != null) {
						
						$equipmentdefaultprovisioningplans[$equipment_for_provisioning->get_eq_id()]		= new Thelist_Model_equipmentdefaultprovisioningplan($default_plan);
						$equipmentdefaultprovisioningplans[$equipment_for_provisioning->get_eq_id()]->provision_equipment_in_database($equipment_for_provisioning);

						//add the equipment to an array
						$equipment_for_device_update[]	= $equipment_for_provisioning;

					}
					$i++;
				}
				
				//now we can update the task mapping, Provisioned in database but not implemented on devices or verified
				//we set it failed because now we commit and move on to configuring the devices, if that fails then the 
				//devices will not be configured
				$sql = "SELECT item_id FROM items
						WHERE item_name='provisioned_in_db_device_config_failed'
						AND item_type='service_plan_quote_task_progress'
						";
					
				$current_installation_status  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
				
				$task_obj->set_task_install_progress($current_installation_status);
				
				//if all is well then we commit the database before stating to configure equipment
				//that cannot be rolled back, because it is not database related.
				Zend_Registry::get('database')->get_thelist_adapter()->commit();
				
				//now update the devices by syncronizing the path to the database
				
				
				if (isset($equipment_for_device_update)) {
					
					//common path object
					$interface_paths_obj 			= new Thelist_Model_interfacepaths();
					$sync 							= new Thelist_Model_synchronizeequipmenttodevice();
					$interface_connections			= new Thelist_Model_interfaceconnections();
					
					foreach ($equipment_for_device_update as $equipment_for_update) {
						
						//path sync many times result in a reboot or interface up/down event
						//make sure to order this execution with last device first if there are multiple inline devices
						//to avoid making some devices unreachable by rebooting the one before them so we lose access
						//for now this method is limited to equipment with cpe router role
						if ($equipment_for_update->get_equipment_role(4) != false) {
							
							$default_gateway_interface = $equipment_for_update->get_default_gateway_interfaces();
							
							if ($default_gateway_interface != false) {
									
								//because we often times attach the ips and routes to vlan interfaces and the interface connections 
								//only deal with physical interfaces we need to make sure we get the physical interface
								
								//this is an array, but we are dealing with role id 4 (cpe router), and can therefor be sure there is only one
								$interface = $default_gateway_interface['0'];
								
								
								$sql 			= "CALL find_root_interface('".$interface->get_if_id()."')";
								$root_if_id		= Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
								
								if ($root_if_id != $interface->get_if_id()) {
									$interface	= new Thelist_Model_equipmentinterface($root_if_id);
								}
								

							
								//only one interface on the equipment has connections and that is the WAN interface
								if ($interface_connections->get_interface_connections($interface) != false) {
									
									$paths = $interface_paths_obj->get_cpe_wan_to_border_router_paths($interface);
									
									//this is a cpe device so it shoould only have a single path out
									if (!isset($paths['1'])) {
										$sync->sync_path_to_database_config($paths['0']);
									} else {
										throw new exception('cpe router have multiple paths to border router, thats not possible', 302);
									}
								} else {
									throw new exception('cpe router wan interface does not have any connections', 303);
								}
							}
						}
					}
				}
				
				
				
				//now we can update the task mapping, Provisioned in database and implemented on devices, but not verified or verified
				$sql = "SELECT item_id FROM items
						WHERE item_name='pending_verification'
						AND item_type='service_plan_quote_task_progress'
						";
					
				$current_installation_status  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
				
				$task_obj->set_task_install_progress($current_installation_status);
				
				//because service should be active at this point we set the activation date to now
				//this way if the tech forgets to do verification it will not stop the plan from being active
				//since a plan that is not active cannot be trouble shot 
				$time = new Thelist_Utility_time();
				$service_plan_quote_map_obj->set_activation($time->get_current_date_time_mysql_format());
				
				//report success to the front end
				$status->appendChild(
				$this->_xml_response->createElement("success", 'success'));

				
			} catch (Exception $e) {
					
				switch($e->getCode()) {
						
					case 85;
					//85, an unknown device or receiver was not tied to a required piece of equipment, its floating free, but exists in the unit
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 86;
					//86, an unknown piece of equipment did not have a model provided by the user
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 87;
					//87, not all requirements where matched with an unknown device or receiver. Or serial was not provided
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 88;
					//88, we did not get serial, receiver_id and access card for an unfilfilled requirement reported as a receiver that was not tied to an unknown device
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 89;
					//89, 2 requierments are being filled by the same piece of equipment
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 81;
					//81, this is not reported as a receiver and we did not tie it to an unknown equipment nor did we provide serial
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 80;
					//80, tech did not provide a serialnumber for an unknown receiver
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 82;
					//82, both serial or rid or access card and unknown device was provided, cant be both
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 83;
					//83, we were provided a eq_type_id by the user to support a requirement, but the selection does not support the requirement
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 84;
					//84, a receiver requirement was tied to an unknow receiver, but no serial number was supplied
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 121;
					//121, the eq_type_id serial check failed
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 200;
					//200, missing unit for the requirement fulfilled
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					//rollback the database query cache
					Zend_Registry::get('database')->get_thelist_adapter()->rollBack();
					break;
					case 201;
					//201, service plan not marked as permanent or not
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					//rollback the database query cache
					Zend_Registry::get('database')->get_thelist_adapter()->rollBack();
					break;
					case 202;
					//202, there is a problem with the amount of interfaces on a customer router
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 206;
					//206, we found an unknown receiver where the selected the model is not a receiver type
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 207;
					//207, equipment was marked as phone and attached to detected equipment
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 213;
					//213, fqdn we are trying to create already exists
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 216;
					//216, We are trying to access a routerboard, but it looks like the firewall is keeping us from accessing the device
					//rollback the database query cache
					Zend_Registry::get('database')->get_thelist_adapter()->rollBack();
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 217;
					//217, We are trying to access a routerboard, but it looks like an old arp entry keeping us from accessing the device
					//rollback the database query cache
					Zend_Registry::get('database')->get_thelist_adapter()->rollBack();
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 300;
					//300, there are receivers present in the future equipment pool
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					$inventory_obj->verify_task_service_point_install($task_obj, $interface_obj, 'update', $requirements);
					break;
					case 301;
					//301, service point interface has changed since we scanned for equipment
					//rollback the database query cache
					Zend_Registry::get('database')->get_thelist_adapter()->rollBack();
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 502;
					//502, not all equipment in the install is connected to other equipment
					//rollback the database query cache 
					Zend_Registry::get('database')->get_thelist_adapter()->rollBack();
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 503;
					//503, no interface an any of the equipment provided is marked as facing the service point
					//rollback the database query cache
					Zend_Registry::get('database')->get_thelist_adapter()->rollBack();
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 504;
					//504, service point interface is not connected to anything in the service point
					//rollback the database query cache
					Zend_Registry::get('database')->get_thelist_adapter()->rollBack();
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 505;
					//505, the provided service point interface is connected to more than a single interface
					//rollback the database query cache
					Zend_Registry::get('database')->get_thelist_adapter()->rollBack();
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 203;
					//203, some equipment mapped to more than a single unit
					//rollback the database query cache
					Zend_Registry::get('database')->get_thelist_adapter()->rollBack();
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 204;
					//204, equipment already mapped to a unit and no remap requested. we should not request that as those checks are already being done
					//rollback the database query cache
					Zend_Registry::get('database')->get_thelist_adapter()->rollBack();
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 700;
					//700, trying to map equipment to service plan quote eq type, but service plan quote or equipment already set and no remap requested
					//rollback the database query cache
					Zend_Registry::get('database')->get_thelist_adapter()->rollBack();
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 1003;
					//1003, mac address has wrong format
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 2200;
					//2200, ip address is not mapped when trying to attach it to a service plan quote map
					//rollback the database query cache
					Zend_Registry::get('database')->get_thelist_adapter()->rollBack();
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					case 42000;
					//42000, database query problem
					//rollback the database query cache
					Zend_Registry::get('database')->get_thelist_adapter()->rollBack();
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					default;
					//by default we relay all errors to the front via SOAP, we dont float exceptions via SOAP, they can reveal too much information (passwords).
					//Zend_Registry::get('database')->get_thelist_adapter()->rollBack();
					$status->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					throw $e;
				}
			}
		}
	}

	private function append_install_equipment_to_new_equipment_array($requirements, $equipment_to_be_connected)
	{
		//this method combines the 
		
		//used by validateInstallProvisionEquipment to add all equipment found on an interface 
		//during the validation of an install, this method adds all the equipment held in cache
		//and returns the complete array of equipment to be connected.
		
		$inventory_obj		= new Thelist_Model_inventory();
		//create default roles
		
		//all customer routers require role 4 (CPE router)
		$cust_router_role_obj	= new Thelist_Model_equipmentrole('4');
			
		//all receivers require role 5 (CPE receiver)
		$rec_role_obj	= new Thelist_Model_equipmentrole('5');
			
		//all customer phones require role 6 (CPE phone)
		$cust_phone_role_obj	= new Thelist_Model_equipmentrole('6');
		
		if (isset($requirements['install_previous_task'])) {
				
			foreach ($requirements['install_previous_task'] as $old_equipment) {
		
				$equipment_to_be_connected[$old_equipment['service_plan_quote_eq_type_map_id']]	= $old_equipment['equipment_obj'];
		
			}
		}
		
		if (isset($requirements['install_this_task'])) {
		
			foreach ($requirements['install_this_task'] as $new_equipment) {
		
				//receivers and routers from inventory have not had a role assigned yet, we must do that here.
				//at this point we know they are in the unit as cpe equipment
				$is_receiver = $inventory_obj->is_directv_receiver($new_equipment['equipment_obj']->get_eq_type());
		
				if ($is_receiver == true) {
		
					$new_equipment['equipment_obj']->set_new_equipment_role($rec_role_obj);
						
				} else {
		
					$new_equipment['equipment_obj']->set_new_equipment_role($cust_router_role_obj);
						
				}
		
				$equipment_to_be_connected[$new_equipment['service_plan_quote_eq_type_map_id']]	= $new_equipment['equipment_obj'];
		
			}
		}
		
		if (isset($requirements['other_equipment'])) {
		
			foreach ($requirements['other_equipment'] as $index => $other_equipment) {
		
				//we cannot have extra receivers in the pool for future tasks, because the service point port selection did not take
				//the extra tuner usage into account.
				$it_is_a_receiver = $inventory_obj->is_directv_receiver($other_equipment['equipment_obj']->get_eq_type());
		
				if ($it_is_a_receiver == true) {
						
					throw new exception('you cannot have receivers in the pool of future equipment', 300);
						
				} else {
						
					//since this has to be a cpe router we assign it the role
					//also we do not have a service plan quote map id for these devices so we put them in a seperate array.
					$other_equipment['equipment_obj']->set_new_equipment_role($cust_router_role_obj);
					$equipment_to_be_connected['other'][]	= $other_equipment['equipment_obj'];
						
				}
			}
		}
		
		return $equipment_to_be_connected;
	}
	
	private function verifyEquipmentOnInterface($request_type, $interface_id, $task_id, $use_caching)
	{
		$task_obj				= new Thelist_Model_tasks($task_id);
		$interface_obj			= new Thelist_Model_equipmentinterface($interface_id);
		//get the unit that owns this install task
		if ($request_type == 'verify_equipment_on_interface') {

			//construct the task element before doing anything, we need it for errors
			$task_element[$task_obj->get_task_id()] = $this->_root_return_element->appendChild(
			$this->_xml_response->createElement("task"));
			
			try { 
			
				$sql = 	"SELECT eu.unit_id FROM service_plan_quote_task_mapping spqtm
						INNER JOIN service_plan_quote_mapping spqm ON spqm.service_plan_quote_map_id=spqtm.service_plan_quote_map_id
						INNER JOIN sales_quotes sq ON sq.sales_quote_id=spqm.sales_quote_id
						INNER JOIN end_user_services eu ON eu.end_user_service_id=sq.end_user_service_id
						WHERE spqtm.task_id='".$task_obj->get_task_id()."'
						";
				
				$task_unit_id  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);

				$unit_obj	=  new Thelist_Model_units($task_unit_id);
				$inventory 		= new Thelist_Model_inventory();
				//null because we never want to use caching here
				$return_array	= $inventory->verify_task_service_point_install($task_obj, $interface_obj, $use_caching);

				$task_element[$task_obj->get_task_id()]->appendChild(
				$this->_xml_response->createElement("task_id", $task_obj->get_task_id()));
				$task_element[$task_obj->get_task_id()]->appendChild(
				$this->_xml_response->createElement("interface_id", $interface_obj->get_if_id()));
				
				if (isset($return_array['install_previous_task'])) {
	
					$old_equipment_element = $task_element[$task_obj->get_task_id()]->appendChild(
					$this->_xml_response->createElement("equipment_old_tasks"));
					
					foreach ($return_array['install_previous_task'] as $old_key => $old_equipment) {
						
						if ($old_key == 'equipment_obj') {
						
							$equipment_element[$old_equipment['equipment_obj']->get_eq_id()] = $old_equipment_element->appendChild(
							$this->_xml_response->createElement("equipment"));
							
							//find out if this equipment belongs in another unit
							$current_unit	= $old_equipment['equipment_obj']->currentEquipmentUnit();
							
							if ($current_unit != false) {
								
								if ($current_unit->get_unit_id() != $unit_obj->get_unit_id()) {
									
									$equipment_element[$old_equipment['equipment_obj']->get_eq_id()]->appendChild(
									$this->_xml_response->createElement("error", "This device belongs to unit ".$current_unit->get_number()." at ".$current_unit->get_streetnumber()." ".$current_unit->get_streetname()." ".$current_unit->get_streettype().", your install has a problem"));
									
								}
							}
							
	
							$equipment_element[$old_equipment['equipment_obj']->get_eq_id()]->appendChild(
							$this->_xml_response->createElement("eq_id", $old_equipment['equipment_obj']->get_eq_id()));
								
							$equipment_element[$old_equipment['equipment_obj']->get_eq_id()]->appendChild(
							$this->_xml_response->createElement("equipment_serial_number", $old_equipment['equipment_obj']->get_eq_serial_number()));
								
							$equipment_element[$old_equipment['equipment_obj']->get_eq_id()]->appendChild(
							$this->_xml_response->createElement("equipment_type_name", $old_equipment['equipment_obj']->get_eq_type()->get_eq_model_name()));
							
							$equipment_element[$old_equipment['equipment_obj']->get_eq_id()]->appendChild(
							$this->_xml_response->createElement("service_plan_quote_eq_type_map_id", $old_equipment['service_plan_quote_eq_type_map_id']));
							
							//if this is a receiver find the access card and add receiver info:
							
							$sql = 	"SELECT eq_id FROM equipments
									WHERE eq_master_id='".$old_equipment['equipment_obj']->get_eq_id()."'
									AND eq_type_id='66'
									";
							
							$access_card_eq_id  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
							if (isset($access_card_eq_id['eq_id'])) {
								
								$access_card = new Thelist_Model_equipments($access_card_eq_id);
								
								$equipment_element[$old_equipment['equipment_obj']->get_eq_id()]->appendChild(
								$this->_xml_response->createElement("access_card", $access_card->get_eq_serial_number()));
								
								$equipment_element[$old_equipment['equipment_obj']->get_eq_id()]->appendChild(
								$this->_xml_response->createElement("receiver_id", $old_equipment['equipment_obj']->get_eq_second_serial_number()));
		
							}
						}
					}
				}
				
				
				if (isset($return_array['install_this_task'])) {
				
					$new_equipment_element = $task_element[$task_obj->get_task_id()]->appendChild(
					$this->_xml_response->createElement("equipment_new_task"));
				
					foreach ($return_array['install_this_task'] as $new_key => $new_equipment) {
	
							
						$equipment_element[$new_equipment['equipment_obj']->get_eq_id()] = $new_equipment_element->appendChild(
						$this->_xml_response->createElement("equipment"));
							
						//find out if this equipment belongs in another unit
						$current_unit	= $new_equipment['equipment_obj']->currentEquipmentUnit();
							
						if ($current_unit != false) {
				
							if ($current_unit->get_unit_id() != $unit_obj->get_unit_id()) {
									
								$equipment_element[$new_equipment['equipment_obj']->get_eq_id()]->appendChild(
								$this->_xml_response->createElement("error", "This device belongs to unit ".$current_unit->get_number()." at ".$current_unit->get_streetnumber()." ".$current_unit->get_streetname()." ".$current_unit->get_streettype().", your install has a problem"));
									
							}
						}
							
				
						$equipment_element[$new_equipment['equipment_obj']->get_eq_id()]->appendChild(
						$this->_xml_response->createElement("eq_id", $new_equipment['equipment_obj']->get_eq_id()));
				
						$equipment_element[$new_equipment['equipment_obj']->get_eq_id()]->appendChild(
						$this->_xml_response->createElement("equipment_serial_number", $new_equipment['equipment_obj']->get_eq_serial_number()));
				
						$equipment_element[$new_equipment['equipment_obj']->get_eq_id()]->appendChild(
						$this->_xml_response->createElement("equipment_type_name", $new_equipment['equipment_obj']->get_eq_type()->get_eq_model_name()));
						
						$equipment_element[$new_equipment['equipment_obj']->get_eq_id()]->appendChild(
						$this->_xml_response->createElement("service_plan_quote_eq_type_map_id", $new_equipment['service_plan_quote_eq_type_map_id']));
							
						//if this is a receiver find the access card and add receiver info:
							
						$sql = 	"SELECT eq_id FROM equipments
								WHERE eq_master_id='".$new_equipment['equipment_obj']->get_eq_id()."'
								AND eq_type_id='66'
								";
							
						$access_card_eq_id  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
				
						if (isset($access_card_eq_id['eq_id'])) {
				
							$access_card = new Thelist_Model_equipments($access_card_eq_id);
				
							$equipment_element[$new_equipment['equipment_obj']->get_eq_id()]->appendChild(
							$this->_xml_response->createElement("access_card", $access_card->get_eq_serial_number()));
				
							$equipment_element[$new_equipment['equipment_obj']->get_eq_id()]->appendChild(
							$this->_xml_response->createElement("receiver_id", $new_equipment['equipment_obj']->get_eq_second_serial_number()));
				
						}
					}
				}
				
				if (isset($return_array['other_equipment'])) {
					
					$other_equipment_element = $task_element[$task_obj->get_task_id()]->appendChild(
					$this->_xml_response->createElement("equipment_other_tasks"));
						
					foreach ($return_array['other_equipment'] as $other_key => $other_equipment) {
	
						$equipment_element[$other_equipment['equipment_obj']->get_eq_id()] = $other_equipment_element->appendChild(
						$this->_xml_response->createElement("equipment"));
							
						//find out if this equipment belongs in another unit
						$current_unit	= $other_equipment['equipment_obj']->currentEquipmentUnit();
							
						if ($current_unit != false) {
			
							if ($current_unit->get_unit_id() != $unit_obj->get_unit_id()) {
									
								$equipment_element[$other_equipment['equipment_obj']->get_eq_id()]->appendChild(
								$this->_xml_response->createElement("error", "This device belongs to unit ".$current_unit->get_number()." at ".$current_unit->get_streetnumber()." ".$current_unit->get_streetname()." ".$current_unit->get_streettype().", your install has a problem"));
									
							}
						}
						
						//we cannot have extra receivers in the future tasks because the port may not support the extra tuners
						if ($current_unit != false) {
						
							if ($current_unit->get_unit_id() != $unit_obj->get_unit_id()) {
						
								$equipment_element[$other_equipment['equipment_obj']->get_eq_id()]->appendChild(
								$this->_xml_response->createElement("error", "This device belongs to unit ".$current_unit->get_number()." at ".$current_unit->get_streetnumber()." ".$current_unit->get_streetname()." ".$current_unit->get_streettype().", your install has a problem"));
						
							}
						}
							
			
						$equipment_element[$other_equipment['equipment_obj']->get_eq_id()]->appendChild(
						$this->_xml_response->createElement("eq_id", $other_equipment['equipment_obj']->get_eq_id()));
			
						$equipment_element[$other_equipment['equipment_obj']->get_eq_id()]->appendChild(
						$this->_xml_response->createElement("equipment_serial_number", $other_equipment['equipment_obj']->get_eq_serial_number()));
			
						$equipment_element[$other_equipment['equipment_obj']->get_eq_id()]->appendChild(
						$this->_xml_response->createElement("equipment_type_name", $other_equipment['equipment_obj']->get_eq_type()->get_eq_model_name()));
							
						//if this is a receiver find the access card and add receiver info:
							
						$sql = 	"SELECT eq_id FROM equipments
								WHERE eq_master_id='".$other_equipment['equipment_obj']->get_eq_id()."'
								AND eq_type_id='66'
								";
							
						$access_card_eq_id  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
						if (isset($access_card_eq_id['eq_id'])) {
			
							$access_card = new Thelist_Model_equipments($access_card_eq_id);
			
							$equipment_element[$other_equipment['equipment_obj']->get_eq_id()]->appendChild(
							$this->_xml_response->createElement("access_card", $access_card->get_eq_serial_number()));
			
							$equipment_element[$other_equipment['equipment_obj']->get_eq_id()]->appendChild(
							$this->_xml_response->createElement("receiver_id", $other_equipment['equipment_obj']->get_eq_second_serial_number()));
			
						}
	
					}
				}
				
				if (isset($return_array['unknown_receivers'])) {
	
					$unknown_receivers_element = $task_element[$task_obj->get_task_id()]->appendChild(
					$this->_xml_response->createElement("unknown_receivers"));
					
					$j=0;	
					foreach ($return_array['unknown_receivers'] as $new_key => $unknown_receiver) {
	
						$unknown_receiver_element[$j] = $unknown_receivers_element->appendChild(
						$this->_xml_response->createElement("device"));
	
						$unknown_receiver_element[$j]->appendChild(
						$this->_xml_response->createElement("access_card", $unknown_receiver['access_card']));
							
						$unknown_receiver_element[$j]->appendChild(
						$this->_xml_response->createElement("receiver_id", $unknown_receiver['receiver_id']));
						
						$unknown_receiver_element[$j]->appendChild(
						$this->_xml_response->createElement("ip_address", $unknown_receiver['ip_address']));
	
						$j++;
					}
				}
				
				if (isset($return_array['unknown_devices'])) {
						
					$unknown_equipment_element = $task_element[$task_obj->get_task_id()]->appendChild(
					$this->_xml_response->createElement("unknown_devices"));
				
					$j=0;
					foreach ($return_array['unknown_devices'] as $unknown_key => $unknown_equipment) {
				
						$unknown_device_element[$j] = $unknown_equipment_element->appendChild(
						$this->_xml_response->createElement("unknown_device"));
				
						$unknown_device_element[$j]->appendChild(
						$this->_xml_response->createElement("mac_address", $unknown_equipment['mac_address']));
				
						$unknown_device_element[$j]->appendChild(
						$this->_xml_response->createElement("ip_address", $unknown_equipment['ip_address']));
				
						$j++;
					}
				}
				
				if (isset($return_array['missing_requirements'])) {
						
					$missing_equipment_element = $task_element[$task_obj->get_task_id()]->appendChild(
					$this->_xml_response->createElement("missing_requirements"));
						
					$j=0;
					foreach ($return_array['missing_requirements'] as $missing_key => $missing_equipment) {
							
						$missing_device_element[$j] = $missing_equipment_element->appendChild(
						$this->_xml_response->createElement("missing_requirement"));
							
						$missing_device_element[$j]->appendChild(
						$this->_xml_response->createElement("equipment_group_name", $missing_equipment->get_service_plan_eq_type_map()->get_eq_type_group()->get_eq_type_group_name()));
							
						$missing_device_element[$j]->appendChild(
						$this->_xml_response->createElement("service_plan_quote_eq_type_map_id", $missing_equipment->get_service_plan_quote_eq_type_map_id()));
							
						$j++;
					}
				}
				
				if (isset($return_array['trouble_devices'])) {
						
					$trouble_equipment_element = $task_element[$task_obj->get_task_id()]->appendChild(
					$this->_xml_response->createElement("trouble_devices"));
						
					$j=0;
					foreach ($return_array['trouble_devices'] as $trouble_key => $trouble_equipment) {
							
						$trouble_device_element[$j] = $trouble_equipment_element->appendChild(
						$this->_xml_response->createElement("trouble_device"));
							
						$trouble_device_element[$j]->appendChild(
						$this->_xml_response->createElement("mac_address", $trouble_equipment['mac_address']));
							
						$trouble_device_element[$j]->appendChild(
						$this->_xml_response->createElement("ip_address", $trouble_equipment['ip_address']));
						
						$trouble_device_element[$j]->appendChild(
						$this->_xml_response->createElement("trouble_text", $trouble_equipment['trouble']));
							
						$trouble_device_element[$j]->appendChild(
						$this->_xml_response->createElement("exception_id", $trouble_equipment['exception_id']));
						
						$j++;
					}
				}

			} catch (Exception $e) {
					
				switch($e->getCode()) {

					case 216;
					//216, We are trying to access a routerboard, but it looks like the firewall is keeping us from accessing the device
					//append the error code to the return
					$task_element[$task_obj->get_task_id()]->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					break;
					default;
					$task_element[$task_obj->get_task_id()]->appendChild(
					$this->_xml_response->createElement("error", $e->getCode()));
					throw $e;
				}
			}
		}
	}

	
	private function servicePoint($request_type, $id, $options)
	{
		
		if ($request_type == 'service_plan_map_install') {
			
		$service_plan_quote_map_obj 					= new Thelist_Model_serviceplanquotemap($id);
		$resourcelocator_obj							= new Thelist_Model_servicepointresourcelocator();

		$sp_interface		= $resourcelocator_obj->get_service_point_interface_for_service_plan_quote_map($service_plan_quote_map_obj, $options);

		//this is a resource intensive procedure, i cache it to call it later
		
		$cache_id1 = "servicepointresourcelocator_class_" . $service_plan_quote_map_obj->get_service_plan_quote_map_id();
		$cache_id2 = "servicepointresourcelocator_sp_interface_" . $service_plan_quote_map_obj->get_service_plan_quote_map_id();
		
		$this->_cache_14400->save($resourcelocator_obj, $cache_id1);
		$this->_cache_14400->save($sp_interface, $cache_id2);

		//all new interfaces to connect
			if ($sp_interface != false){
					
				$service_point	= new Thelist_Model_servicepoint($sp_interface->get_service_point_id());
				
				if (!isset($service_point_element[$service_point->get_service_point_id()])) {
					
					$service_point_element[$service_point->get_service_point_id()] = $this->_root_return_element->appendChild(
					$this->_xml_response->createElement("service_point"));
					
					$service_point_element[$service_point->get_service_point_id()]->appendChild(
					$this->_xml_response->createElement("service_point_id", $service_point->get_service_point_id()));
					
					$service_point_element[$service_point->get_service_point_id()]->appendChild(
					$this->_xml_response->createElement("service_point_name", $service_point->get_service_point_name()));
				}
				
				$patchpanel	= new Thelist_Model_equipments($sp_interface->get_eq_id());
				
				if (!isset($patch_panels[$patchpanel->get_eq_id()])) {

					$patch_panels[$patchpanel->get_eq_id()] = $service_point_element[$service_point->get_service_point_id()]->appendChild(
					$this->_xml_response->createElement("patch_panel"));
					
					$patch_panels[$patchpanel->get_eq_id()]->appendChild(
					$this->_xml_response->createElement("serial_number", $patchpanel->get_eq_serial_number()));
					
				}
				
				
				if (isset($patch_panels[$patchpanel->get_eq_id()])) {
				
					$interface[$sp_interface->get_if_id()] = $patch_panels[$patchpanel->get_eq_id()]->appendChild(
					$this->_xml_response->createElement("interface"));
				
					$interface[$sp_interface->get_if_id()]->appendChild(
					$this->_xml_response->createElement("if_id", $sp_interface->get_if_id()));
					
					$interface[$sp_interface->get_if_id()]->appendChild(
					$this->_xml_response->createElement("if_name", $sp_interface->get_if_name()));
					
					$interface[$sp_interface->get_if_id()]->appendChild(
					$this->_xml_response->createElement("interface_operation", 'Connect'));
					
					if($resourcelocator_obj->get_interface_current_status() != null) {
					
						if ($resourcelocator_obj->get_interface_current_status() == '0') {
								
							$interface[$sp_interface->get_if_id()]->appendChild(
							$this->_xml_response->createElement("connect_status", 'Requires Connection'));
								
						} elseif($resourcelocator_obj->get_interface_current_status() == '1') {
								
							$interface[$sp_interface->get_if_id()]->appendChild(
							$this->_xml_response->createElement("connect_status", 'Already Connected'));
								
						}
					}
					
					if($resourcelocator_obj->get_service_plans_serviced() != null) {

						$service_plans[$sp_interface->get_if_id()] = $interface[$sp_interface->get_if_id()]->appendChild(
						$this->_xml_response->createElement("service_plan_quote_maps"));
						
						foreach($resourcelocator_obj->get_service_plans_serviced() as $service_plan) {
							
							$service_plan_map[$service_plan->get_service_plan_quote_map_id()] = $service_plans[$sp_interface->get_if_id()]->appendChild(
							$this->_xml_response->createElement("service_plan_quote_map"));

							$service_plan_map[$service_plan->get_service_plan_quote_map_id()]->appendChild(
							$this->_xml_response->createElement("service_plan_quote_map_id", $service_plan->get_service_plan_quote_map_id()));
							
						}
					}
				}
				
				//interfaces that should be disconnected
				
				if ($resourcelocator_obj->get_interfaces_to_be_disconnected() != null) {
					
					foreach($resourcelocator_obj->get_interfaces_to_be_disconnected() as $disconn_int) {

						$service_point	= new Thelist_Model_servicepoint($disconn_int->get_service_point_id());
							
						if (!isset($service_point_element[$service_point->get_service_point_id()])) {
					
							$service_point_element[$service_point->get_service_point_id()] = $this->_root_return_element->appendChild(
							$this->_xml_response->createElement("service_point"));
					
							$service_point_element[$service_point->get_service_point_id()]->appendChild(
							$this->_xml_response->createElement("service_point_id", $service_point->get_service_point_id()));
					
							$service_point_element[$service_point->get_service_point_id()]->appendChild(
							$this->_xml_response->createElement("service_point_name", $service_point->get_service_point_name()));
						}
							
						$patchpanel	= new Thelist_Model_equipments($disconn_int->get_eq_id());
							
						if (!isset($patch_panels[$patchpanel->get_eq_id()])) {
					
							$patch_panels[$patchpanel->get_eq_id()] = $service_point_element[$service_point->get_service_point_id()]->appendChild(
							$this->_xml_response->createElement("patch_panel"));
					
							$patch_panels[$patchpanel->get_eq_id()]->appendChild(
							$this->_xml_response->createElement("serial_number", $patchpanel->get_eq_serial_number()));
					
						}
							
							
						if (isset($patch_panels[$patchpanel->get_eq_id()])) {
								
							$interface[$disconn_int->get_if_id()] = $patch_panels[$patchpanel->get_eq_id()]->appendChild(
							$this->_xml_response->createElement("interface"));
								
							$interface[$disconn_int->get_if_id()]->appendChild(
							$this->_xml_response->createElement("if_id", $disconn_int->get_if_id()));
					
							$interface[$disconn_int->get_if_id()]->appendChild(
							$this->_xml_response->createElement("if_name", $disconn_int->get_if_name()));
					
							$interface[$disconn_int->get_if_id()]->appendChild(
							$this->_xml_response->createElement("interface_operation", 'Disconnect'));
							
							$interface[$disconn_int->get_if_id()]->appendChild(
							$this->_xml_response->createElement("connect_status", 'Requires Disconnect'));

							}
						}
					}
					
			} else {
		
				//if we got no interfaces back
				$service_point_element = $this->_root_return_element->appendChild(
				$this->_xml_response->createElement("service_point"));
				
				$service_point_element->appendChild(
				$this->_xml_response->createElement("error", 'no interface'));
				
				$error_desc = 'could be servicepointresourcelocator->get_active_service_plans_for_unit() does not see any active service plans for this unit, because the appointment window is expired';
				
				$service_point_element->appendChild(
				$this->_xml_response->createElement("error_desc", $error_desc));

			}
		}
	}
	
	private function getTaskHelp($request_type, $id)
	{
		if ($request_type == 'service_plan_install_help') {

			$service_plan_quote_task_map			= Zend_Registry::get('database')->get_service_plan_quote_task_mapping()->fetchRow("task_id=".$id);
			
			if (!isset($service_plan_quote_task_map['task_id'])) {
				
				$return_element = $this->_root_return_element->appendChild(
				$this->_xml_response->createElement("no_data_available"));
				
				$return_element->appendChild(
				$this->_xml_response->createElement("reason_for_no_return", 'this task does not have a service plan quote mapped'));
				
			} else {
				
				$task_element = $this->_root_return_element->appendChild(
				$this->_xml_response->createElement("service_plan"));
				
				$service_plan_quote_map				= new Thelist_Model_serviceplanquotemap($service_plan_quote_task_map['service_plan_quote_map_id']);
				
				if ($service_plan_quote_map->get_service_plan()->get_service_plan_help() != null) {
				
					$task_help_images_element = $task_element->appendChild(
					$this->_xml_response->createElement("help_images"));
					
					foreach($service_plan_quote_map->get_service_plan()->get_service_plan_help() as $help) {
						
						if ($help['wiring_image_path'] != '') {
							
							$task_help_images_element->appendChild(
							$this->_xml_response->createElement('help_image', "http://".$_SERVER['SERVER_NAME']."".$help['wiring_image_path'].""));

						}
					}
				}
			}
		}	
	}
	
	
	
	
	private function getCalendarAppointmentEquipment($request_type, $id)
	{
		if ($request_type == 'task_required_equipment') {
			

			//first we get all the equipment groups that belong to the sales quotes
			$sql = 		"SELECT service_plan_quote_map_id FROM service_plan_quote_task_mapping
						WHERE task_id='".$id."'
						";
			
			$task_service_plan_quote_maps  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			
			if (isset($task_service_plan_quote_maps['0'])) {
				$j=0;
				foreach ($task_service_plan_quote_maps as $service_plan_quote_map) {
					
					$service_plan_quote_map_obj			= new Thelist_Model_serviceplanquotemap($service_plan_quote_map['service_plan_quote_map_id']);
					
					if ($service_plan_quote_map_obj->get_service_plan_quote_eq_types() !=null) {
							
						foreach($service_plan_quote_map_obj->get_service_plan_quote_eq_types() as $eqtypemap) {
							$j++;
							$equipment[$j] = $this->_root_return_element->appendChild(
							$this->_xml_response->createElement("required_equipment"));
						
							$equipment[$j]->appendChild(
							$this->_xml_response->createElement("equipment_group_name", $eqtypemap->get_service_plan_eq_type_map()->get_eq_type_group()->get_eq_type_group_name()));
							
							$equipment[$j]->appendChild(
							$this->_xml_response->createElement("eq_type_group_id", $eqtypemap->get_service_plan_eq_type_map()->get_eq_type_group()->get_eq_type_group_id()));
							
						}
					}
				}
			}
			
			//now get any that are attached directly to tasks
			$sql2 = 	"SELECT eq_type_group_id FROM task_to_eq_type_group_mapping
						WHERE task_id='".$id."'
						";
				
			$task_direct_group_maps  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
					
			if (isset($task_direct_group_maps['0'])) {
				$k=0;
				foreach($task_direct_group_maps as $task_direct_group_map) {
					$k++;
					$eq_type_group_obj			= new Thelist_Model_equipmenttypegroup($task_direct_group_map['eq_type_group_id']);
				
					$equipment2[$k] = $this->_root_return_element->appendChild(
					$this->_xml_response->createElement("required_equipment"));
				
					$equipment2[$k]->appendChild(
					$this->_xml_response->createElement("equipment_group_name", $eq_type_group_obj->get_eq_type_group_name()));
					
					$equipment2[$k]->appendChild(
					$this->_xml_response->createElement("eq_type_group_id", $eq_type_group_obj->get_eq_type_group_id()));
					
					
				}
			}
		}
	}
	
	
	private function getTaskDetail($request_type, $uid=null, $start_date_time=null, $end_date_time=null, $task_status=null, $task_id=null, $id=null)
	{
		if ($request_type == 'calendartasks') {
			
			$sql = 	"SELECT t.task_id FROM calendar_appointments ca
					INNER JOIN calendar_appointment_task_mapping catm ON catm.calendar_appointment_id=ca.calendar_appointment_id
					INNER JOIN tasks t ON t.task_id=catm.task_id
					INNER JOIN users u ON u.uid=t.task_owner
					";
			
			$task_vars	=	array('u.uid' => $uid, 'ca.scheduled_start_time' => $start_date_time, 'ca.scheduled_end_time' => $end_date_time, 't.task_status' => $task_status, 't.task_id' => $task_id);
			
			$do_we_need_a_where_clause = 'no';
						
			foreach ($task_vars as $key => $value) {
				
				if($value != '' && $value != null) {
				
					if ($do_we_need_a_where_clause == 'no') {
					
						$sql .= " WHERE ";
					
					}
					
					$do_we_need_a_where_clause = 'yes';
					
				}
			}

			if ($do_we_need_a_where_clause = 'yes') {
				
				$i=0;
				foreach($task_vars as $key => $value) {

					if($value != '') {
						$i++;
						
						if ($i != '1') {
							
							$sql .= " AND ";
							
						} 
						
						if ($key == 'ca.scheduled_start_time') {
							
							$sql .= $key." >= "."'".$value."'";
							
						} elseif ($key == 'ca.scheduled_end_time') {
							
							$sql .= $key." <= "."'".$value."'";
							
						} else {
							
							$sql .= $key."="."'".$value."'";
							
						}
					}
				}
			}		
					
		$task_results  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		if (isset($task_results['0'])) {
			
			foreach($task_results as $task_result) {
				$task_obj	= new Thelist_Model_tasks($task_result['task_id']);
				
				if ($task_obj->get_calendar_appointments() != null) {
					
					foreach($task_obj->get_calendar_appointments() as $calendar_appointment) {
	
						if (!isset($appointment_element[$calendar_appointment->get_calendar_appointment_id()])) {
								
							$appointment_element[$calendar_appointment->get_calendar_appointment_id()] = $this->_root_return_element->appendChild(
							$this->_xml_response->createElement("calendar_appointment"));
							
							$appointment_element[$calendar_appointment->get_calendar_appointment_id()]->appendChild(
							$this->_xml_response->createElement("calendar_appointment_id", $calendar_appointment->get_calendar_appointment_id()));
							$appointment_element[$calendar_appointment->get_calendar_appointment_id()]->appendChild(
							$this->_xml_response->createElement("scheduled_start_time", $calendar_appointment->get_scheduled_start_time()));
							$appointment_element[$calendar_appointment->get_calendar_appointment_id()]->appendChild(
							$this->_xml_response->createElement("scheduled_end_time", $calendar_appointment->get_scheduled_end_time()));
							$appointment_element[$calendar_appointment->get_calendar_appointment_id()]->appendChild(
							$this->_xml_response->createElement("actual_start_time", $calendar_appointment->get_scheduled_end_time()));
							$appointment_element[$calendar_appointment->get_calendar_appointment_id()]->appendChild(
							$this->_xml_response->createElement("actual_end_time", $calendar_appointment->get_scheduled_end_time()));
							$appointment_element[$calendar_appointment->get_calendar_appointment_id()]->appendChild(
							$this->_xml_response->createElement("calendar_appointment_status", $calendar_appointment->get_resolved_appointment_status()));
							$appointment_element[$calendar_appointment->get_calendar_appointment_id()]->appendChild(
							$this->_xml_response->createElement("scheduled_time", $calendar_appointment->get_scheduled_time()));
						
						}

						if (!isset($tasks_element[$calendar_appointment->get_calendar_appointment_id()])) {
							
							$tasks_element[$calendar_appointment->get_calendar_appointment_id()] = $appointment_element[$calendar_appointment->get_calendar_appointment_id()]->appendChild(
							$this->_xml_response->createElement("tasks"));
							
						}
						
						$single_task_element[$task_obj->get_task_id()] = $tasks_element[$calendar_appointment->get_calendar_appointment_id()]->appendChild(
						$this->_xml_response->createElement("task"));
						
						$single_task_element[$task_obj->get_task_id()]->appendChild(
						$this->_xml_response->createElement("task_id", $task_obj->get_task_id()));
							
						$single_task_element[$task_obj->get_task_id()]->appendChild(
						$this->_xml_response->createElement("master_task_id", $task_obj->get_master_task_id()));
							
						$single_task_element[$task_obj->get_task_id()]->appendChild(
						$this->_xml_response->createElement("task_status", $task_obj->get_resolved_task_status()));
							
						$single_task_element[$task_obj->get_task_id()]->appendChild(
						$this->_xml_response->createElement("task_owner_uid", $task_obj->get_task_owner()));
																		
						$sql2 = 	"SELECT service_plan_quote_map_id FROM service_plan_quote_task_mapping
									WHERE task_id='".$task_obj->get_task_id()."'";
						
						$quote_service_plans  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);

						if($quote_service_plans != null) {

							foreach ($quote_service_plans as $quote_service_plan) {
							
							if (!isset($quote_service_plans_element[$task_obj->get_task_id()])) {
										
								$quote_service_plans_element[$task_obj->get_task_id()] = $single_task_element[$task_obj->get_task_id()]->appendChild(
								$this->_xml_response->createElement("mapped_service_plans"));
										
							}
								
							$single_quote_service_plans_element[$task_obj->get_task_id()] = $quote_service_plans_element[$task_obj->get_task_id()]->appendChild(
							$this->_xml_response->createElement("service_plan_quote_map"));
								
								
							$single_quote_service_plans_element[$task_obj->get_task_id()]->appendChild(
							$this->_xml_response->createElement("service_plan_quote_map_id", $quote_service_plan['service_plan_quote_map_id']));
							
							}
						}
						
						$sql3 = 	"SELECT end_user_task_map_id FROM end_user_task_mapping
									WHERE task_id='".$task_obj->get_task_id()."'";
						
						$trouble_tickets  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql3);
						
						if($trouble_tickets != null) {
						
							foreach ($trouble_tickets as $trouble_ticket) {
									
								if (!isset($trouble_ticket_element[$task_obj->get_task_id()])) {
						
									$trouble_ticket_element[$task_obj->get_task_id()] = $single_task_element[$task_obj->get_task_id()]->appendChild(
									$this->_xml_response->createElement("tickets"));
						
								}
						
								$single_trouble_ticket_element[$task_obj->get_task_id()] = $trouble_ticket_element[$task_obj->get_task_id()]->appendChild(
								$this->_xml_response->createElement("end_user_task_map"));
						
						
								$single_trouble_ticket_element[$task_obj->get_task_id()]->appendChild(
								$this->_xml_response->createElement("end_user_task_map_id", $trouble_ticket['end_user_task_map_id']));
									
							}
						}
						
						
		
						}
						
					}
				}
			}
		} elseif ($request_type == 'calendar_appointment_tasks') {
			
			$sql = 	"SELECT catm.task_id FROM calendar_appointments ca
					INNER JOIN calendar_appointment_task_mapping catm ON catm.calendar_appointment_id=ca.calendar_appointment_id
					WHERE ca.calendar_appointment_id='".$id."'
					";
			
			$tasks  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			if (isset($tasks['0'])) {
				
				foreach ($tasks as $task) {
					
					$task_obj	= new Thelist_Model_tasks($task['task_id']);
					
					$task_element[$task_obj->get_task_id()] = $this->_root_return_element->appendChild(
					$this->_xml_response->createElement("task"));
					
					$task_element[$task_obj->get_task_id()]->appendChild(
					$this->_xml_response->createElement("task_id", $task_obj->get_task_id()));
					
					$task_element[$task_obj->get_task_id()]->appendChild(
					$this->_xml_response->createElement("task_name", $task_obj->get_name()));
					
					$task_element[$task_obj->get_task_id()]->appendChild(
					$this->_xml_response->createElement("task_status", $task_obj->get_resolved_task_status()));
					
					
					
					$sql5 = "SELECT service_plan_quote_map_id FROM service_plan_quote_task_mapping
							WHERE task_id='". $task_obj->get_task_id()."'
							";
					$service_plan_quote_maps  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql5);
					
					if (isset($service_plan_quote_maps['0'])) {
						
						$task_sales_quote_maps_element[$task_obj->get_task_id()] = $task_element[$task_obj->get_task_id()]->appendChild(
						$this->_xml_response->createElement("service_plan_quote_maps"));
						
						foreach ($service_plan_quote_maps as $service_plan_quote_map) {
							
							$single_task_sales_quote_map_element[$service_plan_quote_map['service_plan_quote_map_id']] = $task_sales_quote_maps_element[$task_obj->get_task_id()]->appendChild(
							$this->_xml_response->createElement("service_plan_quote_map"));
							
							$single_task_sales_quote_map_element[$service_plan_quote_map['service_plan_quote_map_id']]->appendChild(
							$this->_xml_response->createElement("service_plan_quote_map_id", $service_plan_quote_map['service_plan_quote_map_id']));
							
						}
					}
					
					if ($task_obj->get_notes() != null) {
						
						$task_notes_element[$task_obj->get_task_id()] = $task_element[$task_obj->get_task_id()]->appendChild(
						$this->_xml_response->createElement("notes"));
						
						foreach ($task_obj->get_notes() as $note) {
							
							$note_element[$note->get_note_id()] = $task_notes_element[$task_obj->get_task_id()]->appendChild(
							$this->_xml_response->createElement("note"));
							
							$note_element[$note->get_note_id()]->appendChild(
							$this->_xml_response->createElement("note_text", $note->get_note_content()));
							
						}	
					}	
				}
			}	
		} elseif ($request_type == 'task_with_notes') {
			
			$task_obj	= new Thelist_Model_tasks($id);
			
			//find out if this task has been provisioned yet
			$sql87 = 	"SELECT * FROM service_plan_quote_task_mapping spqtm
						WHERE spqtm.task_id='".$task_obj->get_task_id()."'
						";
				
			$install_progress  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql87);
				
			$task_element[$task_obj->get_task_id()] = $this->_root_return_element->appendChild(
			$this->_xml_response->createElement("task"));
				
			$task_element[$task_obj->get_task_id()]->appendChild(
			$this->_xml_response->createElement("task_id", $task_obj->get_task_id()));
				
			$task_element[$task_obj->get_task_id()]->appendChild(
			$this->_xml_response->createElement("task_name", $task_obj->get_name()));
			
			if (isset($install_progress['task_id'])) {
				
				$task_obj->fill_mapped_service_plan_quote_task_map();

				if ($task_obj->get_status() == 12) {
					$current_installation_status  = $task_obj->get_mapped_service_plan_quote_task_progress_resolved();
				} elseif($task_obj->get_status() == 13) {
					$current_installation_status = 'Installation Complete';
				}
				
				$task_element[$task_obj->get_task_id()]->appendChild(
				$this->_xml_response->createElement("task_install_status", $current_installation_status));
				
				$task_element[$task_obj->get_task_id()]->appendChild(
				$this->_xml_response->createElement("task_install_progress_id", $install_progress['service_plan_quote_task_progress']));
			} 

			$task_element[$task_obj->get_task_id()]->appendChild(
			$this->_xml_response->createElement("task_status", $task_obj->get_resolved_task_status()));

				
			if ($task_obj->get_notes() != null) {
			
				$task_notes_element[$task_obj->get_task_id()] = $task_element[$task_obj->get_task_id()]->appendChild(
				$this->_xml_response->createElement("notes"));
			
				foreach ($task_obj->get_notes() as $note) {
						
					$note_element[$note->get_note_id()] = $task_notes_element[$task_obj->get_task_id()]->appendChild(
					$this->_xml_response->createElement("note"));
						
					$note_element[$note->get_note_id()]->appendChild(
					$this->_xml_response->createElement("note_text", $note->get_note_content()));
						
				}
			}
			
		}
	}

	private function getEnduserDetail($request_type, $id)
	{
			
		if ($request_type == 'from_end_user_service_id') {
			
			$end_user_ids 		= array($id);

		} elseif ($request_type == 'calendar_appointment_end_users') {
			
			$sql = 	"SELECT sq.end_user_service_id AS enduserid FROM calendar_appointments ca
					INNER JOIN calendar_appointment_task_mapping catm ON catm.calendar_appointment_id=ca.calendar_appointment_id
					INNER JOIN service_plan_quote_task_mapping spqtm ON spqtm.task_id=catm.task_id
					INNER JOIN service_plan_quote_mapping spqm ON spqm.service_plan_quote_map_id=spqtm.service_plan_quote_map_id
					INNER JOIN sales_quotes sq ON sq.sales_quote_id=spqm.sales_quote_id
					WHERE ca.calendar_appointment_id='".$id."'
					UNION
					SELECT eutm.end_user_service_id AS enduserid FROM calendar_appointments ca
					INNER JOIN calendar_appointment_task_mapping catm ON catm.calendar_appointment_id=ca.calendar_appointment_id
					INNER JOIN end_user_task_mapping eutm ON eutm.task_id=catm.task_id
					WHERE ca.calendar_appointment_id='".$id."'
					GROUP BY enduserid
					";
			
			$end_user_ids  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);

		}
		
		foreach($end_user_ids as $end_user_id) {
			
			$end_user = new Thelist_Model_enduserservice($end_user_id['enduserid']);
		
			if (is_object($end_user)) {
			
				$end_user_element[$end_user->get_end_user_service_id()] = $this->_root_return_element->appendChild(
				$this->_xml_response->createElement("end_user"));
					
				$end_user_element[$end_user->get_end_user_service_id()]->appendChild(
				$this->_xml_response->createElement("end_user_id", $end_user->get_end_user_service_id()));
	
				$installation_unit = $end_user->get_unit();
					
				$unit = $end_user_element[$end_user->get_end_user_service_id()]->appendChild(
				$this->_xml_response->createElement("unit"));
				
				$unit->appendChild(
				$this->_xml_response->createElement("unit_id", $installation_unit->get_unit_id()));
				
				$unit->appendChild(
				$this->_xml_response->createElement("unit_number", $installation_unit->get_number()));
				
				$unit->appendChild(
				$this->_xml_response->createElement("unit_name", $installation_unit->get_name()));
				
				$unit->appendChild(
				$this->_xml_response->createElement("streetnumber", $installation_unit->get_streetnumber()));
				
				$unit->appendChild(
				$this->_xml_response->createElement("streetname", $installation_unit->get_streetname()));
				
				$unit->appendChild(
				$this->_xml_response->createElement("streettype", $installation_unit->get_streettype()));
				
				$unit->appendChild(
				$this->_xml_response->createElement("city", $installation_unit->get_city()));
				
				$unit->appendChild(
				$this->_xml_response->createElement("state", $installation_unit->get_state()));
				
				$unit->appendChild(
				$this->_xml_response->createElement("zipcode", $installation_unit->get_zip()));
				
				$contacts_element = $end_user_element[$end_user->get_end_user_service_id()]->appendChild(
				$this->_xml_response->createElement("contacts"));
				
				//the contacts
				
				$primary_contact 		= $end_user->get_primary_contact();
				
				$primary_contact_element = $contacts_element->appendChild(
				$this->_xml_response->createElement("primary_contact"));
				
				$primary_contact_element->appendChild(
				$this->_xml_response->createElement("title", $primary_contact->get_titlename()));
				
				$primary_contact_element->appendChild(
				$this->_xml_response->createElement("first_name", $primary_contact->get_firstname()));
				
				$primary_contact_element->appendChild(
				$this->_xml_response->createElement("last_name", $primary_contact->get_lastname()));
				
				$primary_contact_element->appendChild(
				$this->_xml_response->createElement("cell_phone", $primary_contact->get_cellphone()));
				
				$primary_contact_element->appendChild(
				$this->_xml_response->createElement("home_phone", $primary_contact->get_homephone()));
				
				$primary_contact_element->appendChild(
				$this->_xml_response->createElement("office_phone", $primary_contact->get_officephone()));
				
				$contacts 				= $end_user->get_contacts();
				
				$m=0;
				foreach ($contacts as $contact) {
					
					if ($contact->get_contact_id() != $primary_contact->get_contact_id()) {
						$m++;
						$another_contact_element[$m] = $contacts_element->appendChild(
						$this->_xml_response->createElement("other_contact"));
						
						
						$another_contact_element[$m]->appendChild(
						$this->_xml_response->createElement("title", $contact->get_titlename()));
							
						$another_contact_element[$m]->appendChild(
						$this->_xml_response->createElement("first_name", $contact->get_firstname()));
							
						$another_contact_element[$m]->appendChild(
						$this->_xml_response->createElement("last_name", $contact->get_lastname()));
							
						$another_contact_element[$m]->appendChild(
						$this->_xml_response->createElement("cell_phone", $contact->get_cellphone()));
							
						$another_contact_element[$m]->appendChild(
						$this->_xml_response->createElement("home_phone", $contact->get_homephone()));
							
						$another_contact_element[$m]->appendChild(
						$this->_xml_response->createElement("office_phone", $contact->get_officephone()));
						
					}
				}
			}
		}
	}
	
	

	
}

?>