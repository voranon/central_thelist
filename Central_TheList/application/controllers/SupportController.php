<?php

//by martin
//exception codes 17700-17799 
class SupportController extends Zend_controller_Action
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
						
			//nothing not a perspective controller
			$layout_manager = new Thelist_Utility_layoutmanager($this->_user_session->current_perspective, $this->_helper);
			$layout_manager->set_layout();
			
			//create the head
			$main_menu     		= new Thelist_Html_element_mainmenu();
			$perspective_menu 	= new Thelist_Html_element_perspectivemenu();
			
			$main_menu->set_htmlmainmenu($this->_user_session->current_perspective);
			$perspective_menu->set_htmlperspectivemenu($this->_user_session->current_perspective);
				
			// create menu for main and perspective
			$this->view->placeholder('mainmenu')->append($main_menu->get_htmlmainmenu());
			$this->view->placeholder('perspective_menu')->append($perspective_menu->get_htmlperspectivemenu());
				
			// create homelink
			$this->view->placeholder('homelink')->append($this->_user_session->perspective);
		}
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
	
	public function postDispatch()
	{
	
	}
	
	public function indexAction()
	{
		
	}
	
	public function getofficeinternetusageAction()
	{
		
	}
	
	public function troubleshootpathAction()
	{
		$this->_helper->layout->disableLayout();
		
		if (isset($_GET['path']) && !$this->getRequest()->isPost()) {
			
			$path_obj = new Thelist_Model_path($_GET['path']);
			
			$path_equipments 				= $path_obj->get_path_equipment();
			$number_of_equipments_in_path 	= count($path_equipments);
			
			$i=0;
			foreach ($path_equipments as $path_equipment) {

				//make connection to the device if it is capable
				if ($path_equipment['equipment']->get_apis() != false) {
					try {
							
						$device = $path_equipment['equipment']->get_device();
						$view_return[$i]['eq_status']	= true;
							
					} catch (Exception $e) {
							
						switch($e->getCode()){
							case 1202;
							//unreachable
							$device = false;
							$view_return[$i]['eq_status']	= false;
							break;
							default;
							throw $e;
					
						}
					}

				} else {
					$device = false;
					$view_return[$i]['eq_status']	= null;
				}

				$view_return[$i]['eq_fqdn']								= $path_equipment['equipment']->get_eq_fqdn();
				$view_return[$i]['eq_manufacturer']						= $path_equipment['equipment']->get_eq_type()->get_eq_manufacturer();
				$view_return[$i]['eq_model_name']						= $path_equipment['equipment']->get_eq_type()->get_eq_model_name();
				$view_return[$i]['eq_serial_number']					= $path_equipment['equipment']->get_eq_serial_number();
				
				if (isset($path_equipment['inbound_interface'])) {
					
					$view_return[$i]['inbound_interface_name']			= $path_equipment['inbound_interface']->get_if_name();
					$view_return[$i]['inbound_if_id']					= $path_equipment['inbound_interface']->get_if_id();
					
					if ($device != false) {
						
						//config_sync
						$view_return[$i]['inbound_sync_status']		= $device->get_interface_db_sync_status($path_equipment['inbound_interface']);
						
						//get the interface operational status
						$if_op_status			= $device->get_interface_operational_status($path_equipment['inbound_interface']);
						
						if ($if_op_status == 0) {
							
							$if_admin_status	= $device->get_interface_administrative_status($path_equipment['inbound_interface']);
							
							if ($if_admin_status == 0) {
								//interface admin down
								$view_return[$i]['inbound_op_status']	= 'shut';
							} elseif ($if_admin_status == 1) {
								$view_return[$i]['inbound_op_status']	= false;
							}
							
						} elseif ($if_op_status == 1) {
							$view_return[$i]['inbound_op_status']		= true;
						} else {
							//unknown
							$view_return[$i]['inbound_op_status']		= null;
						}

					} else {
						$view_return[$i]['inbound_op_status']			= null;
						$view_return[$i]['inbound_sync_status']			= null;
					}
				}
				
				if (isset($path_equipment['outbound_interface'])) {
					
					$view_return[$i]['outbound_interface_name']			= $path_equipment['outbound_interface']->get_if_name();
					$view_return[$i]['outbound_if_id']					= $path_equipment['outbound_interface']->get_if_id();
					
					if ($device != false) {
					
						//config_sync
						$view_return[$i]['outbound_sync_status']		= $device->get_interface_db_sync_status($path_equipment['outbound_interface']);
						
						//get the interface operational status
						$if_op_status			= $device->get_interface_operational_status($path_equipment['outbound_interface']);
					
						if ($if_op_status == 0) {
								
							$if_admin_status	= $device->get_interface_administrative_status($path_equipment['outbound_interface']);
								
							if ($if_admin_status == 0) {
								//interface admin down
								$view_return[$i]['outbound_op_status']	= 'shut';
							} elseif ($if_admin_status == 1) {
								$view_return[$i]['outbound_op_status']	= false;
							}
								
						} elseif ($if_op_status == 1) {
							$view_return[$i]['outbound_op_status']		= true;
						} else {
							//unknown
							$view_return[$i]['outbound_op_status']		= null;
						}
					
					} else {
						$view_return[$i]['outbound_op_status']			= null;
						$view_return[$i]['outbound_sync_status']			= null;
					}
				}
				
				$i++;
			}
			
			if (isset($view_return)) {
				$this->view->path_equipments 		= $view_return;
				$this->view->no_of_equipments 		= $number_of_equipments_in_path;
				$this->view->path 					= $_GET['path'];
			}
		}
	}
	
	public function verifydeviceprovisioningAction()
	{
		$this->_helper->layout->disableLayout();
		
		//if this is for a service plan quote map
		if (isset($_GET['service_plan_quote_map_id'])) {
			
			$sql = "SELECT task_id FROM service_plan_quote_task_mapping spqtm
					WHERE spqtm.service_plan_quote_map_id='".$_GET['service_plan_quote_map_id']."'
					";
			
			$task_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
			if (isset($task_id['task_id'])) {

				$task_obj = new Thelist_Model_tasks($task_id);
				
				$trouble_core		= new Thelist_Model_troubleshootercore();
				$issues				= $trouble_core->get_task_problems($task_obj);
				
				echo "\n <pre> support control  \n ";
				print_r($issues);
				echo "\n 2222 \n ";
				//print_r($return_array);
				echo "\n 3333 \n ";
				//print_r();
				echo "\n 4444 </pre> \n ";
				die;
				if ($issues != false) {
					$this->view->issues = $issues;
				} else {
					
					//false means there are no issues, so we change the installation progress to
					//pending verification
					$sql = "SELECT item_id FROM items
							WHERE item_name='pending_verification'
							AND item_type='service_plan_quote_task_progress'
							";
						
					$new_installation_status  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
					
					$task_obj->set_task_install_progress($new_installation_status);
					
					$this->view->allverified = 1;
				}
				
			} else {
				throw new exception("service_plan_quote_map_id: ".$_GET['service_plan_quote_map_id']." does not have a task, we cant validate the service plan map without a task", 17701);
			}
		}
	}
	
	public function reportbrokenservicepointinterfaceAction()
	{
		$this->_helper->layout->disableLayout();
		
		if (isset($_GET['task_id']) && isset($_GET['if_id']) && !$this->getRequest()->isPost()) {
			
			$if_obj = new Thelist_Model_equipmentinterface($_GET['if_id']);
			$eq_obj = new Thelist_Model_equipments($if_obj->get_eq_id());
			
			//maybe in the future bring old tasks to the front so the tech can see if the issue
			//has been handled in the past
			$currently_open_tasks	= $if_obj->get_tasks('open');
			
			if ($currently_open_tasks != null) {
				$this->view->old_task = "There is already an open task for this interface";
			}
				
				if ($if_obj->get_service_point_id() == null) {
					$this->view->error = "Interface: '".$if_obj->get_if_name()."' on equipment '".$eq_obj->get_eq_serial_number()."' is not in a service point and cannot be reported broken as such";
				} else {
					$this->view->eq_id 				= $eq_obj->get_eq_id();
					$this->view->eq_serial 			= $eq_obj->get_eq_serial_number();
					$this->view->if_id 				= $if_obj->get_if_id();
					$this->view->if_name			= $if_obj->get_if_name();
					$this->view->task_id			= $_GET['task_id'];
					$this->view->tech_firstname		=$this->user_session->firstname;
					$this->view->tech_lastname 		=$this->user_session->lastname;
				}

		} elseif ($this->getRequest()->isPost()) {

			$if_obj = new Thelist_Model_equipmentinterface($_GET['if_id']);
			
			if (isset($_POST['if_id']) && isset($_POST['eq_id']) && isset($_POST['task_id'])) {

				if (isset($_POST['report_txt'])) {
						
					$tt_obj	= new Thelist_Utility_troubletaskcreator('Engineering', 'Service Interface Problem', 'Medium');
					$interface_problem_task = $tt_obj->create_task($this->user_session->uid);
					
					//append report txt
					$report_txt = "Reported By: ".$this->user_session->firstname." ".$this->user_session->lastname.", while working on task_id: ".$_POST['task_id'].". Report TXT: \n
								 ".$_POST['report_txt']."";
					
					$interface_problem_task->add_note($report_txt);
					
					//tie the task to the interface
					$if_obj->map_new_task($interface_problem_task);

				} else {
					$this->view->error = "You must provide a report description";
				}
			} else {
				throw new exception('validation missing while submitting a broken service point interface report', 17700);
			}

			echo "<script>
			window.close();
			window.opener.close();
			window.opener.opener.location.reload();
			</script>";
		}
		
	}

}
?>