<?php

//exception codes 17200-17299

class SalesController extends Zend_controller_Action
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
	
	public function indexAction()
	{
	
	}

	public function tempaddbulksalesquotetoenduserAction()
	{
		$this->_helper->layout->disableLayout();
		//we dont have a working sales system currently.
		//this method will allow a user to select the service 
		//plans and have everything upto the calendar / install completed.
		
		if (isset($_POST['end_user_service_id']) && $this->getRequest()->isPost()) {
				
			$this->view->end_user_service_id	= $_POST['end_user_service_id'];

			if ($_POST['installer_uid'] != '') {
			
				$end_user_obj	= new Thelist_Model_enduserservice($_POST['end_user_service_id']);
				
				//get_service_plan_temp_quote_mappings
				//for right nt to limit input errors, we only allow one of each plan
				if (isset($_POST['create_internet_sps'])) {
					if(count($_POST['create_internet_sps']) > 1) {
						throw new exception("we only allow a single internet plan on a quote fr the time being", 17201);
					} else {
						foreach ($_POST['create_internet_sps'] as $internet_sp_id) {
							$service_plans_to_create[]	= new Thelist_Model_serviceplan($internet_sp_id);
						}
					}
				}
				
				if (isset($_POST['create_phone_sps'])) {
					if(count($_POST['create_phone_sps']) > 1) {
						throw new exception("we only allow a single phone plan on a quote fr the time being", 17202);
					} else {
						foreach ($_POST['create_phone_sps'] as $phone_sp_id) {
							$service_plans_to_create[]	= new Thelist_Model_serviceplan($phone_sp_id);
						}
					}
				}
				
				if (isset($_POST['create_tv_sps'])) {
					if(count($_POST['create_tv_sps']) > 1) {
						throw new exception("we only allow a single tv plan on a quote fr the time being", 17203);
					} else {
						foreach ($_POST['create_tv_sps'] as $tv_sp_id) {
							$service_plans_to_create[]	= new Thelist_Model_serviceplan($tv_sp_id);
						}
					}
				}
				
				//are there any plans that need to be created?
				if (count($service_plans_to_create) > 0) {
					
					foreach ($service_plans_to_create as $service_plan) {
						
						$temp_sp	= $end_user_obj->add_service_plan_temp_quote_map($service_plan->get_service_plan_id(), $service_plan->get_service_plan_default_mrc_term(), $service_plan->get_service_plan_default_mrc(), $service_plan->get_service_plan_default_nrc(), null);
						
						//store the temp service plan in var so we can delete it later
						$new_temp_service_plans[] = $temp_sp;
						
						$eq_types	= $service_plan->get_service_plan_eq_type_maps();
						if ($eq_types != null) {
							
							foreach ($eq_types as $eq_type) {
								
								//only add required equipment items
								if ($eq_type->get_service_plan_eq_type_group()->get_service_plan_eq_type_max_quantity() == 1 && $eq_type->get_service_plan_eq_type_group()->get_service_plan_eq_type_required_quantity() == 1) {
									$temp_sp->add_service_plan_temp_quote_eq_type($eq_type->get_service_plan_eq_type_map_id(), $eq_type->get_service_plan_eq_type_default_mrc_term(), $eq_type->get_service_plan_eq_type_default_mrc(), $eq_type->get_service_plan_eq_type_default_nrc());
								}
							}
						}
						
						$options	= $service_plan->get_service_plan_option_maps();
						if ($options != null) {
						
							foreach ($options as $option) {
									
								//only add required option items
								if ($option->get_service_plan_option_group()->get_service_plan_option_max_quantity() == 1 && $option->get_service_plan_option_group()->get_service_plan_option_required_quantity() == 1) {
									$temp_sp->add_service_plan_temp_quote_option($option->get_service_plan_option_map_id(), $option->get_service_plan_option_default_mrc_term(), $option->get_service_plan_option_default_mrc(), $option->get_service_plan_option_default_nrc());
								}
							}
						}
					}
					
					//we now have all service plans populated with the required options / equipment
					
					//create the sales quote
					$sales_quote = $end_user_obj->create_sales_quote_from_temp_quote();
					
					//now remove all the temp quote service plans
					foreach ($new_temp_service_plans as $temp_service_plan) {
						$end_user_obj->remove_service_plan_temp_quote_map($temp_service_plan->get_service_plan_temp_quote_map_id());
					}

					//use the sales quote to map all the service plan maps to tasks so we can install
					$new_service_plans = $sales_quote->get_service_plan_quote_maps();
					
					//we have already counted the service plans above so we know they are there 
					foreach ($new_service_plans as $new_service_plan) {
						
						$new_service_plan->create_service_plan_quote_installation_task($_POST['installer_uid']);
					}
	
					if (is_object($sales_quote)) {
						echo "<script>
							window.close();
							window.opener.location.reload();
							</script>";	
					} else {
						//some kind of issue, hopefully there is an exception on the screen
					}
					
					
				} else {
					
					//nothing to do no sales quotes selected
					echo "<script>
					window.close();
					</script>";
				}

			} else {
				$this->view->error = 'you must select an installer';
			}
			
		} elseif (isset($_GET['end_user_service_id']) && !$this->getRequest()->isPost()) {
			
			$end_user_obj	= new Thelist_Model_enduserservice($_GET['end_user_service_id']);
			
			$this->view->end_user_service_id	= $_GET['end_user_service_id'];
			
			$active_service_plans	= $end_user_obj->get_unit()->get_active_service_plan_maps();
			
			if ($active_service_plans != null) {
				$i=0;
				foreach ($active_service_plans as $active_service_plan) {
					
					if ($active_service_plan->get_service_plan_group_name() == 'Internet') {
						
						$to_view['internet'][$i]['sp_name']	= $active_service_plan->get_service_plan_name();
						$to_view['internet'][$i]['sp_id']	= $active_service_plan->get_service_plan_id();
						$i++;
						
					} elseif ($active_service_plan->get_service_plan_group_name() == 'Phone') {
						
						$to_view['phone'][$p]['sp_name']	= $active_service_plan->get_service_plan_name();
						$to_view['phone'][$p]['sp_id']		= $active_service_plan->get_service_plan_id();
						$p++;
						
					} elseif ($active_service_plan->get_service_plan_group_name() == 'Tv') {
						
						$to_view['tv'][$t]['sp_name']	= $active_service_plan->get_service_plan_name();
						$to_view['tv'][$t]['sp_id']		= $active_service_plan->get_service_plan_id();
						$t++;
					}
				}
			}
			
			//find all available installers
			//$groups	= $end_user_obj->get_unit()->get_unit_groups('install_tech_area');
			
			//if ($groups != null) {
				
			//	foreach ($groups as $group) {
			//	$group->
				//}
			//}

			if (isset($to_view)) {
				$this->view->available_installers		= "<OPTION value='".$this->_user_session->uid."'>".$this->_user_session->firstname." ".$this->_user_session->lastname."</OPTION>";
				$this->view->available_service_plans	= $to_view;
			}
		}
	}
	
	
	public function acceptsalesquotemanualoverrideAction()
	{
		$this->_helper->layout->disableLayout();
		$this->view->error = '';
		//let a user set the sales quote status to accepted manually if automated accept cannot be done
		if (isset($_GET['sales_quote_id']) && !$this->getRequest()->isPost()) {
			
			$sales_quote						= new Thelist_Model_salesquote($_GET['sales_quote_id']);
			$primary_contact					= $sales_quote->get_end_user_service()->get_primary_contact();
			$this->view->primary_contact_name 	= $primary_contact->get_firstname() . " " . $primary_contact->get_lastname();
			
			$sp_maps	= $sales_quote->get_service_plan_quote_maps();
			$inuse		= 'no';
			
			if ($sp_maps != null) {
					
				foreach ($sp_maps as $sp_map) {
			
					if ($sp_map->get_activation() != null) {
						$inuse = 'yes';
					}
				}
			}
			
			if ($sales_quote->get_sales_quote_accepted() == 0 && $inuse == 'no') {
				
				$this->view->sales_quote_id 		= $sales_quote->get_sales_quote_id();
				$this->view->sales_quote_status 	= $sales_quote->get_sales_quote_accepted();
				
			} elseif ($sales_quote->get_sales_quote_accepted() == 1 && $inuse == 'no') {

				$this->view->sales_quote_id 		= $sales_quote->get_sales_quote_id();
				$this->view->sales_quote_status 	= $sales_quote->get_sales_quote_accepted();
				
			} else {
				$this->view->error	= 'items on the sales quote are in use, cannot change accepted status';
			}
			
			$return_sales_quotes[]	= $sales_quote->toArray();
			
			
		} elseif ($this->getRequest()->isPost()) {
			
			$sales_quote	= new Thelist_Model_salesquote($_POST['sales_quote_id']);
			
			$sp_maps	= $sales_quote->get_service_plan_quote_maps();
			$inuse		= 'no';
			
			if ($sp_maps != null) {
					
				foreach ($sp_maps as $sp_map) {
						
					if ($sp_map->get_activation() != null) {
						$inuse = 'yes';
					}
				}
			}
			
			if ($inuse == 'yes') {
				throw new exception("you are attempting to change the accept status of sales_quote_id : ".$_POST['sales_quote_id']." and it has active service plans, this is a problem", 17200);
			} else {
				$sales_quote->set_sales_quote_accepted($_POST['new_accepted_status']);
				
				echo "<script>
				window.close();
				window.opener.location.reload();
				</script>";	
			}
		}
	}
			
}
?>