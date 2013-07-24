<?php

//by Non
//exception codes 22500-22599
	 
class SalespitchestimateController extends Zend_controller_Action
{
	private $_user_session;
	private $_time;
	
	public function init()
	{
		$this->_user_session 	= new Zend_Session_Namespace('userinfo');
		$this->_time			= new Thelist_Utility_time();
		
		
		if($this->_user_session->uid == '') {
			
			//no uid, user not logged in
			Zend_Registry::get('logs')->get_app_logger()->log("User not logged in, return to index", Zend_Log::ERR);
			header('Location: /');
			exit;
			
		}
		
		$this->_helper->layout->setLayout('salespitchestimate_layout');
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
		//$this->_helper->viewRenderer->setNoRender(true);
		$end_user_service_id = $_GET['end_user_service_id'];
		
		//$end_user =  Zend_Registry::get('database')->get_end_user_services()->fetchRow('end_user_service_id='.$end_user_service_id);
		$end_user 	=  new Thelist_Model_enduserservice($end_user_service_id);
		
		$unit_id 	=  $end_user->get_unit_id();
		$unit		=  new Thelist_Model_unit($unit_id);
		
		$service_plan_objs = $unit->get_active_service_plan_maps();
		
		
		foreach($service_plan_objs as $service_plan_obj)
		{
			
			$service_plans[ $service_plan_obj->get_service_plan_id() ] = $service_plan_obj->toArray();
			
		}
		$this->view->service_plans=$service_plans;
		
		$this->view->notes = $end_user->get_notes();
		
		
		
		$primary_contact = $end_user->get_primary_contact();
		
		$this->view->enduser_firstname = $primary_contact->get_first_name();
		$this->view->enduser_lastname  = $primary_contact->get_last_name();
		$this->view->enduser_title	   = $primary_contact->get_title();
		/*
		$unit 		=  new Thelist_Model_units( $end_user->get_unit_id() );
		
		$building 	=  new Thelist_Model_buildings($unit->get_building_id(),true);
		
		$this->view->buildingname = $building->get_name();
		$this->view->unitnumber   = $unit->get_number();
		$this->view->address	  = $unit->get_streetname().','.$unit->get_streettype().','.$unit->get_city().','.$unit->get_state().','.$unit->get_zip();
		
		$this->view->internet_service_plans = $unit->get_internet_service_plans();
												  
		$this->view->directtv_service_plans = $unit->get_directtv_service_plans();
		
		$this->view->phone_service_plans = $unit->get_phone_service_plans();
		*/
		
								  
	}
	
	
	
	public function notesaveajaxAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		
		$end_user_service_id = $_POST['end_user_service_id'];
		
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		
		
	
		
		$end_user = new Thelist_Model_enduserservice( $end_user_service_id );
		
		$end_user->add_end_user_service_note($_POST['text']);
		
		$notes = $end_user->get_notes();
		
		$output='';
		
		foreach($notes as $note){
			$output.= $note->get_createdate().":".$note->get_creator()."\n".
					  $note->get_note_content()."\n";
		}
		
		
		echo $output;
	}
	public function updatenoteajaxAction(){
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		$end_user_service_id = $_POST['end_user_service_id'];
		
		$end_user = new Thelist_Model_enduserservice( $end_user_service_id );
		
		$notes = $end_user->get_notes();
		
		$output='';
		
		if ($notes != null) {
			foreach($notes as $note){
				$output.= $note->get_createdate().":".$note->get_creator()."\n".
				$note->get_note_content()."\n";
			}
		}

		echo $output;
	}
	
	public function serviceplandropajaxAction()
	{
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		$service_plan_id 		= $_POST['service_plan_id'];
		$enduser_service_id		= $_POST['end_user_service_id'];
		$mrc_term				= $_POST['mrc_term'];
		$mrc					= $_POST['mrc'];
		$nrc					= $_POST['nrc'];
		$enduser_service_obj    =  new Thelist_Model_enduserservice( $enduser_service_id );
		
		
	
		
		try{
					
			$enduser_service_obj->add_service_plan_temp_quote_map($service_plan_id,$mrc_term,$mrc,$nrc);
			
		}catch(Exception $e){
			
			$error[]=
			//$e->getMessage();
			//$e->getCode();
			
			echo $e->getMessage().'---'.$e->getCode();	
		}
		
		$service_plans = $enduser_service_obj->get_service_plan_temp_quote_mappings();
		
		
		
		
		
	}
	
}
?>