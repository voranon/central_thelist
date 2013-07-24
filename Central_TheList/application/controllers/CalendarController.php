<?php
class CalendarController extends Zend_controller_Action
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
			$layout_manager->set_layout();
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
	
	public function indexAction(){
		
	}
	
	public function makeappointmentAction(){
		
		$this->_helper->layout->disableLayout();
		$this->view->sales_quote_id = $_GET['sales_quote_id'];
		
		if( isset( $_GET['date'] ) )
		{
			$this->view->date = $_GET['date'];
		}else{ // set today date
			$this->view->date = $this->time->get_current_date_time_date_picker();
		}
		
		
		
		$sale_quote = new Thelist_Model_salesquote( $_GET['sales_quote_id'] );
		$unit		= new Thelist_Model_units( $sale_quote->get_unit_id() );
		$calendar   = new Thelist_Model_calendar($this->view->date);
		
		$this->view->calendar			= $calendar;
		$this->view->unit				= $unit;
		$this->view->installation_time	= $sale_quote->get_install_time();
		
		$this->view->address = $unit->get_building_name().": ".$unit->get_streetnumber()." ".$unit->get_streetname()." #".$unit->get_number();
		
		$date = explode('/',$this->view->date);
		
		$installer_ids = $unit->get_installsers($date[2],$date[0],$date[1]);

		$installers='';
		
		foreach(is_array($installer_ids) || is_object($installer_ids) ? $installer_ids : array() as $installer_id){
			$installers[$installer_id] = new Thelist_Model_users($installer_id);
		}
		
		$this->view->installers = $installers;
		
	}
	
	public function calendarAction(){
		$this->_helper->layout->disableLayout();
	}
	

	public function scheduledetailAction(){
		
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		
		$this->_helper->layout->disableLayout();
		
		$this->view->sale_quote_id = $_GET['sales_quote_id'];
		$uid					   = $_GET['uid'];
		$date					   = $_GET['date'];
		
		
		
		$sale_quote = new Thelist_Model_salesquote($_GET['sales_quote_id']);
		
		$installation_time = $sale_quote->get_install_time();
		
		// for testing only
		//$installation_time =45;
		
		
		$this->view->start 				= $_GET['start'];
		$this->view->stop				= $this->time->add_minute($_GET['start'],$installation_time);
		 
	
		$this->view->installation_time 	= $installation_time;
		
		
		
		$sql_starttime	=	$this->time->convert_am_pm_to_mysql_datetime($date.' '.$_GET['start']);
		$sql_stoptime	=	$this->time->convert_am_pm_to_mysql_datetime($date.' '.$this->time->add_minute( $_GET['start'],$installation_time-1) );
		
		
		
		if( isset( $_POST['save'] ) ){
					
			//$service_plans = $sale_quote->get_service_plans();
			
			$service_plan_quote_maps = $sale_quote->get_service_plan_quote_maps();
			
			$sql="SELECT queue_id
				  FROM queues
				  WHERE queue_name = 'pending installation'";
			
			$queue_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			

			$calendar_status 	= new Thelist_Utility_items('install_calendar_status');
			$task_priority 		= new Thelist_Utility_items('task_priority');
			$task_status   		= new Thelist_Utility_items('task_status');
			
			
		
			
			$data = array(
							'scheduled_start_time'			=>	$sql_starttime,
							'scheduled_end_time'			=>  $sql_stoptime,
							'calendar_appointment_status'	=>  $calendar_status->get_id('tentative'),
							'scheduled_time'				=>  $this->time->get_current_date_time()
						);
						
			$calendar_appointment_id = Zend_Registry::get('database')->insert_single_row('calendar_appointments',$data,get_class($this),$method);
			
			
			
			
			foreach($service_plan_quote_maps as $service_plan_quote_map)
			{
				
				$data = array(
								'task_name' 	=> 'install '.$service_plan_quote_map->get_service_plan()->get_service_plan_name(),
								'task_owner' 	=> $uid,
								'task_queue_id' => $queue_id,
								'task_priority' => $task_priority->get_id('Low'),
								'task_status'	=> $task_status->get_id('Open'),
								'creator'		=> 0
							 );
				
				///		create a new task
				$task_id = Zend_Registry::get('database')->insert_single_row('tasks',$data,get_class($this),$method);
				
				
				
				$data = array(
								'service_plan_quote_map_id'	=> $service_plan_quote_map->get_service_plan_quote_map_id(),
								'task_id'					=> $task_id
							);
				
				///		map task to service plan quote task mapping
				
				$service_plan_quote_task_map_id = Zend_Registry::get('database')->insert_single_row('service_plan_quote_task_mapping',$data,get_class($this),$method);
				
				
				$data = array(
								'task_id'					=> 	$task_id,
								'calendar_appointment_id'	=>  $calendar_appointment_id
							);
				
				$calendar_appointment_task_map_id = Zend_Registry::get('database')->insert_single_row('calendar_appointment_task_mapping',$data,get_class($this),$method);
				
				
				
				$data = array(
								'end_user_service_id'		=>  $sale_quote->get_end_user_service()->get_end_user_service_id(),
								'task_id'					=>  $task_id
							);
				
				$end_user_task_map_id =  Zend_Registry::get('database')->insert_single_row('end_user_task_mapping',$data,get_class($this),$method);
				
				
				
				
					
 			}
			
			echo "<script>
					//window.opener.location.href = window.opener.location.href;
					window.opener.location.href = '/enduser/index';
					window.close();
				 </script>";
		
		}else{
			echo 'did not post';
		}
		
		
		
		
		
	
		
	}
	
	public function scheduledetailajaxAction(){
		
		$this->_helper->viewRenderer->setNoRender(true);
		$appointment_id = $_GET['appointment_id'];
		
		$appointment = new Thelist_Model_calendarappointment( $appointment_id );


		
		
 		$building = new Thelist_Model_buildings( $appointment->get_building_id(),true );
 		
 		$unit	 = new Thelist_Model_units( $appointment->get_unit_id() );
 		$enduser	 = new Thelist_Model_enduserservice( $appointment->get_enduser_id() );
 		
 		$service_plan_groups = $appointment->get_service_plan_groups();
		
		
		
		$output ="<tr><td align='center'>".$enduser->get_primary_contact()->get_firstname()."</td></tr>
				  <tr><td align='center'>".$building->get_name()."</td></tr>
				  <tr><td align='center'>#".$unit->get_number()."</td></tr>";
		$message='';
		foreach($service_plan_groups as $service_plan_group){ 
			$message.=$service_plan_group.'/';
		
		}
		$message = substr($message, 0, -1);
		$output.="<tr><td align='center'>".$message."</td></tr>";
				
		
	
		
		
		$debug  = "<tr><td>".$appointment->get_unit_id()."</td></tr>
				   <tr><td>".$appointment->get_enduser_id()."</td></tr>
				   <tr><td>".$appointment->get_building_id()."</td></tr>
				   <tr><td align='center'>".$appointment_id."</td></tr>";
		
		echo $output;
		//echo $debug;
	}
	
	
	
	
	
	
}
?>