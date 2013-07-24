<?php

//exception codes 6800-6899

class FieldinstallationController extends Zend_controller_Action
{
	private $_user_session;
	
	public function init()
	{
		$this->_user_session 	= new Zend_Session_Namespace('userinfo');
		
		if($this->_user_session->uid == '') {
			
			//no uid, user not logged in
			Zend_Registry::get('logs')->get_app_logger()->log("User not logged in, return to index", Zend_Log::ERR);
			header('Location: /');
			exit;
			
		} else {
			//not a perspective controller
			$layout_manager = new Thelist_Utility_layoutmanager($this->_user_session->current_perspective, $this->_helper);
			$layout_manager->set_layout();		}
	}
	
	public function preDispatch()
	{
		$permission			= new Thelist_Utility_acl($this->_user_session->role_id);
		$controller 		= $this->getRequest()->getControllerName();
		$action 			= $this->getRequest()->getActionName();
		
		$clearance 			= $permission->acl_clearance($action, $controller);
		
		//log the page request
		$report	= array(
							'uid'					=> $this->_user_session->uid,
							'page_name'				=> $this->view->url(),
							'message_1'				=> '',
							'message_2'				=> '',
		);

		if ($clearance === true) {

			$report['event']	= 'page_change';
			Zend_Registry::get('database')->insert_single_row('user_event_logs', $report, $controller, $action);

		} else {
			
			$report['event']	= 'acl_deny';
			Zend_Registry::get('database')->insert_single_row('user_event_logs', $report, $controller, $action);
			
			throw new exception("'".$this->_user_session->firstname." ".$this->_user_session->lastname."'. You are trying to access controller name: '".$controller."' using Action name: '".$action."', but you are not allowed to access this page", 22500);
		}
	}

	public function postDispatch(){
	
	}
	
	public function installerdashboardAction()
	{
		//nothing to see here yet all is in the phtml
		
	}
	
	public function getavailableservicepointinterfacesAction()
	{
	
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
	
		if (isset($_GET['service_point_id']) && isset($_GET['unit_id'])) {
	
			$interfaces = "<OPTION value='0'>--- SELECT ONE ---</OPTION>";
				
			$service_point			= new Thelist_Model_servicepoint($_GET['service_point_id']);
			$sp_unused_interfaces	= $service_point->get_service_point_unused_interfaces();
			$sp_resource_locator	= new Thelist_Model_servicepointresourcelocator();
			$unit_obj				= new Thelist_Model_units($_GET['unit_id']);
			
			$current_sp_interfaces	= $sp_resource_locator->get_unit_current_service_point_interfaces($unit_obj);

			if ($current_sp_interfaces != false) {
				
				$interfaces .= "<OPTION value=\"0\">-- CURRENTLY CONNECTED START --</OPTION>";
				
				foreach($current_sp_interfaces as $current_sp_interface){
					$units .= "<OPTION value=\"".$current_sp_interface->get_if_id()."\">".$current_sp_interface->get_if_name()."</OPTION>";
				}
				
				$interfaces .= "<OPTION value=\"0\">-- CURRENTLY CONNECTED END --</OPTION>";
				$interfaces .= "<OPTION value=\"0\"></OPTION>";
				
			}
			
			if ($sp_unused_interfaces != false) {
				$interfaces .= "<OPTION value=\"0\">-- Unused Interfaces --</OPTION>";
				foreach($sp_unused_interfaces as $sp_unused_interface){
					
					//we dont want any interfaces that have open tasks, they have trouble
					if ($sp_unused_interface->get_tasks('open') == null) {
						$interfaces .= "<OPTION value=\"".$sp_unused_interface->get_if_id()."\">".$sp_unused_interface->get_if_name()."</OPTION>";
					}
				}
			}
				
			echo $interfaces;
	
		} else {
			//must have service point id and unit_id
		}
	}
	
	public function serviceplanmapmanualactivationAction()
	{
		$this->_helper->layout->disableLayout();
		
		if (isset($_GET['service_plan_quote_map_id']) && !$this->getRequest()->isPost()) {
			
			//first we figure out the task status of the provided service plan map id
			//that will dictate where in the process we are picking up the install
			$service_plan_quote_map 			= new Thelist_Model_serviceplanquotemap($_GET['service_plan_quote_map_id']);
			$task								= $service_plan_quote_map->get_service_plan_quote_tasks_map();
			
			if ($task == null) {
				throw new exception("The Service plan id: ".$_GET['service_plan_map_id']." has no task mapped to it, without a task assigned we cannot perform any actions, talk to software dev about how this is possible", 6803);
			}
			
			
		} elseif (isset($_GET['task_id']) && !$this->getRequest()->isPost()) {
			
			$task 								= new Thelist_Model_tasks($_GET['task_id']);
			$service_plan_quote_map				= new Thelist_Model_serviceplanquotemap($task->get_mapped_service_plan_quote_map_id());
		}
		
		if (isset($service_plan_quote_map) && isset($service_plan_quote_map)) {

			if ($task->get_status() == 12) {

				//now lets see at what stage in the install process we are
				$sql = "SELECT item_name FROM items
						WHERE item_id='".$task->get_mapped_service_plan_quote_task_progress()."'
						";
				
				$status = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);

				$this->view->progress_status	= $status;
				$this->view->task_id			= $task->get_task_id();
				
				if ($status == 'not_provisioned_in_db') {
					//its not even in the database
					
					//find the unit so we can show the available patch panels
					$sql2 =  "SELECT eus.unit_id FROM sales_quotes sq
							INNER JOIN end_user_services eus ON eus.end_user_service_id=sq.end_user_service_id
							WHERE sq.sales_quote_id='".$service_plan_quote_map->get_sales_quote_id()."'
							AND eus.deactivated IS NULL
							";
					
					$unit_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);

					$this->view->unit_id			= $unit_id;
					
					$unit_obj					= new Thelist_Model_units($unit_id);
					$available_service_points	= $unit_obj->get_unit_service_points();
					
					if ($available_service_points != null) {
						
						$avail_sps					= '';
						foreach ($available_service_points as $service_point) {
							$avail_sps .= "<OPTION value=\"".$service_point->get_service_point_id()."\">" . $service_point->get_service_point_name() . "</OPTION>";
						}
						
						$this->view->service_points	= $avail_sps;
					}

				} elseif ($status == 'provisioned_in_db_device_config_failed') {
					
					//get all edge equipment in in the service plan map
					
					$interface_paths			= new Thelist_Model_interfacepaths();
					
					$paths = $interface_paths->get_service_paths_from_service_plan_quote_map($service_plan_quote_map);
					
					if ($paths != false) {
						
						$i=0;
						foreach ($paths as $path) {
							
							$view_return[$i]['path_string'] 		= $path->get_path_string();
							
							$path_equipments 						= $path->get_path_equipment();
							
							$number_of_equipments_in_path = count($path_equipments);
							
							$j=0;
							foreach ($path_equipments as $path_equipment) {
								$j++;
								
								if ($j == 1 || $j == $number_of_equipments_in_path) {
									
									$view_return[$i]['equipment'][$j]['eq_fqdn']						= $path_equipment['equipment']->get_eq_fqdn();
									$view_return[$i]['equipment'][$j]['eq_manufacturer']				= $path_equipment['equipment']->get_eq_type()->get_eq_manufacturer();
									$view_return[$i]['equipment'][$j]['eq_model_name']					= $path_equipment['equipment']->get_eq_type()->get_eq_model_name();
																				
									if (isset($path_equipment['inbound_interface'])) {
										$view_return[$i]['equipment'][$j]['inbound_interface_name']		= $path_equipment['inbound_interface']->get_if_name();
									}
									
									if (isset($path_equipment['outbound_interface'])) {
										$view_return[$i]['equipment'][$j]['outbound_interface_name']	= $path_equipment['outbound_interface']->get_if_name();
									}
								}
							}
							
							$i++;
						}
					}
					
					if (isset($view_return)) {
						$this->view->paths = $view_return;
					}
					
				} elseif ($status == 'pending_verification') {
					
				}  elseif ($status == 'verification_complete') {
					
				}
				
			} else {
				$this->view->error = "The Service plan id: ".$_GET['service_plan_map_id']." has no open task, you cannot configure it without an open task";
			}
		
		}
		
	}
	
	public function appointmentsAction()
	{
	
		$fieldxmlapi_obj			= new Thelist_Model_fieldxmlapi();
		$calendartasks_xml_obj		= $fieldxmlapi_obj->getcalendartasks_xml($this->_user_session->uid, $this->_time->get_todays_date_mysql_format(), $this->_time->get_tomorrows_date_mysql_format(), '12', null);
	
		$this->view->appointments	= $fieldxmlapi_obj->appointments_for_technician_as_array($calendartasks_xml_obj);
	
	}
	
	public function appointmentAction()
	{
		
		if (isset($_GET['calendar_appointment_id'])) {
			
			$fieldxmlapi_obj				= new Thelist_Model_fieldxmlapi();
			$calendar_appointment			= new Thelist_Model_calendarappointment($_GET['calendar_appointment_id']);
			
			$appointment_detail_xml_obj		= $fieldxmlapi_obj->get_calendar_appointment_detail($calendar_appointment);

			$this->view->appointment	= $fieldxmlapi_obj->get_appoinment_detail_as_array($appointment_detail_xml_obj);
		}
	}
	
	public function serviceplaninstallhelpAction()
	{
		if (isset($_GET['task_id'])) {

			$task		= new Thelist_Model_tasks($_GET['task_id']);
			
			if ($task->get_status() == '12') {
				
				$fieldxmlapi_obj						= new Thelist_Model_fieldxmlapi();
				$task_obj								= new Thelist_Model_tasks($_GET['task_id']);
				$service_plan_help_xml					= $fieldxmlapi_obj->get_service_plan_install_help($task_obj);
				
				if ($service_plan_help_xml != false) {
					
					$this->view->service_plan_help_array	= $fieldxmlapi_obj->get_service_plan_install_help_as_array($service_plan_help_xml);
					
				} else {
					
					$this->view->service_plan_help_array = array();
					
				}
	
				$required_equipment_xml					= $fieldxmlapi_obj->get_task_equipment($task_obj);
				$this->view->required_equipment_array	= $fieldxmlapi_obj->get_required_equipment_as_array($required_equipment_xml);
				
				$task_xml								= $fieldxmlapi_obj->get_task_details($task_obj);
				$this->view->task_array					= $fieldxmlapi_obj->get_tasks_as_array($task_xml);
			
			} else {
				
				$this->view->error	= 'Task Closed';
				
			}
	
		} 
	}
	
	public function installtaskmanualdeviceconfigAction()
	{
	
		//moving forward none of the functions are made via soap calls, we dont have time
		//and there is no certainty it will be used.
		$this->_helper->layout->disableLayout();
	
		if (isset($_GET['task_id'])) {
				
			$task_obj		= new Thelist_Model_tasks($_GET['task_id']);
	
			if ($task_obj->get_status() == '12') {
	
				$sql = 	"SELECT spqtm.service_plan_quote_task_progress FROM service_plan_quote_task_mapping spqtm
						WHERE spqtm.task_id='".$task_obj->get_task_id()."'
						";
					
				$install_progress  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
	
				if ($install_progress == 2) {
						
					$fieldxmlapi_obj	= new Thelist_Model_fieldxmlapi();
					$fieldxmlapi_obj->get_install_task_device_install_status($task_obj);

				} else {
						
					throw new exception('this install is not in a state that allows manual config, how did you even get here?', 6801);
				}
					
			} else {
	
				$this->view->error	= 'Task Closed';
	
			}
		}
	}
	
	public function validateinstallationAction()
	{
		
		$this->_helper->layout->disableLayout();
		
		if (isset($_GET['task_id'])) {

			$sql = 	"SELECT * FROM service_plan_quote_task_mapping spqtm
					INNER JOIN items itm ON itm.item_id=spqtm.service_plan_quote_task_progress
					WHERE spqtm.task_id='".$_GET['task_id']."'
					";
				
			$install_progress  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
			if (isset($install_progress['item_name'])) {

				$task_obj		= new Thelist_Model_tasks($_GET['task_id']);
				
				if ($task_obj->get_status() == '12') {
					
					if ($install_progress['item_name'] == 'pending_verification') {

						
					} else {
						throw new exception('this install is not ready to be validated, how did you even get here?', 6800);
					}
					
				} else {
					$this->view->error	= 'Task Closed';
				}
					
			} else {
				throw new exception('this task is not an installation task, this is a problem', 6804);
				
			}
		}
	}
	
	public function locateportinservicepointAction()
	{
		$this->_helper->layout->disableLayout();
		
		if (isset($_GET['task_id'])) {
			
			$task_obj		= new Thelist_Model_tasks($_GET['task_id']);
			
			if ($task_obj->get_status() == '12') {
			
			//there can only be a single service plan map for a task.
				$service_plan_quote_map = Zend_Registry::get('database')->get_service_plan_quote_task_mapping()->fetchRow("task_id=".$_GET['task_id']);
				
				if (isset($service_plan_quote_map['service_plan_quote_map_id'])) {
					
					$service_plan_quote_map_obj 					= new Thelist_Model_serviceplanquotemap($service_plan_quote_map['service_plan_quote_map_id']);
					$fieldxmlapi_obj								= new Thelist_Model_fieldxmlapi();
					
					
					//add the options
					if (isset($_GET['calendar_based_install'])) {
						$options['calendar_based_install'] = $_GET['calendar_based_install'];
					} 
					if (isset($_GET['if_ids'])) {
						$options['if_ids'] = $_GET['if_ids'];
					}
					
					if (isset($options)) {
						$service_point_interface_xml					= $fieldxmlapi_obj->get_service_point_interfaces($service_plan_quote_map_obj, $options);
					} else {
						$service_point_interface_xml					= $fieldxmlapi_obj->get_service_point_interfaces($service_plan_quote_map_obj, false);
					}

					$this->view->service_point_interfaces_array		= $fieldxmlapi_obj->service_point_interface_xml_as_array($service_point_interface_xml);
	
				} 
			
			} else {
				
				$this->view->error	= 'Task Closed';
				
			}
		}
	}
	
	public function testerAction()
 	{
 		
//  		$enduser = new Thelist_Model_enduserservice(1);

//  		$xml = new Thelist_Utility_jsonconverter();
 		
//  		$error['exception_code']	= 11334;
//  		$error['error_string']		= 'this is a test error';
 		
//  		$return = $xml->convert_service_plan_temp_quotes($enduser->get_service_plan_temp_quote_mappings(), $error);
 		
 		
 		
//  		$ssh_parameters = array ('kex' => 'diffie-hellman-group1-sha1');
//  		$connection = ssh2_connect('mt433.4652v.rt.belairinternet.com', 22, $ssh_parameters);
//  		ssh2_auth_password($connection, 'admin', 'K11ne0ver%');
 		
//  		ssh2_scp_send($connection, '/zend/thelist/public/app_file_store/device_firmware/routeros/routeros-mipsbe-5.20.npk', '/routeros-mipsbe-5.20.npk', 0644);
 		
 		
 		
 		
 		
 		
 		
 		
 		
 		$equipment_obj 				= new Thelist_Model_equipments(350);
 		
 		$api = current($equipment_obj->get_apis());
 		//$api->set_api_id(3);
 		$device 					= new Thelist_Model_device($equipment_obj->get_eq_fqdn(), $api);
 		$options		= 'http://108.60.42.20/app_file_store/device_firmware/routeros/routeros-mipsbe-5.20.npk';
 		
 		$device_reply = $device->execute_command("/tool fetch url=".$options." mode=http");
 		
 		
 		//$device->download_file($options);
 		
 		//$upload_file = new Thelist_Routeros_command_uploadfile($device, '/zend/thelist/public/app_file_store/device_firmware/routeros', 'routeros-mipsbe-5.20.npk', null, 'routeros-mipsbe-5.20.npk');
 		//$upload_file->execute();
 		
 		//$return = new Thelist_Routeros_command_resetconfig($device, 'default_cpe_config.rsc');
 		//$return->execute();
 		echo "\n <pre> 1111  \n ";
 		//print_r($return);
 		echo "\n 2222 \n ";
 		//print_r($return_array);
 		echo "\n 3333 \n ";
 		//print_r();
 		echo "\n 4444 </pre> \n ";
 		die;
 		
 		//$interface_obj 				= new Thelist_Model_equipmentinterface(126);
	  		//$equipment_obj 				= new Thelist_Model_equipments(339);
  			//$device 					= new Thelist_Model_device($equipment_obj->get_eq_fqdn(), current($equipment_obj->get_apis()));
  			
  		//	$software 				= new Thelist_Routeros_command_getmemorystats($device);
  			//$software->get_available_non_volatile_memory();
  			//$device->upgrade_running_os_package($software);
	  		//$equipment_obj->backup_device();
  		
  			//$function_test = new Thelist_Routeros_command_getinterfaces($device);
  			//$function_test = new Thelist_Routeros_command_setinterfacename($device, $interface_obj, 'martin');
  			
  			//echo "\n <pre> filed install control  \n ";
  			//print_r($function_test->get_configured_interfaces());
  			//echo "\n 2222 \n ";
  			//print_r($return_array);
  			//echo "\n 3333 \n ";
  			//print_r();
  			//echo "\n 4444 </pre> \n ";
  			die;
  			
  			

	}
}
?>