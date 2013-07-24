<?php

class residentialsaleperspectiveController extends Zend_Controller_Action
{
	private $_user_session;
	private $_controller_default_perspective = 1;
	
	public function init()
	{
		$this->_user_session 	= new Zend_Session_Namespace('userinfo');
		
		if($this->_user_session->uid == '') {
			
			//no uid, user not logged in
			Zend_Registry::get('logs')->get_app_logger()->log("User not logged in, return to index", Zend_Log::ERR);
			header('Location: /');
			exit;
			
		} else {
			
			//create the head
			$main_menu     		= new Thelist_Html_element_mainmenu();
			$perspective_menu 	= new Thelist_Html_element_perspectivemenu();
				
			if ($this->_user_session->current_perspective == null) {
			$this->_user_session->current_perspective = $this->_controller_default_perspective;
			}
				
			$main_menu->set_htmlmainmenu($this->_user_session->current_perspective);
			$perspective_menu->set_htmlperspectivemenu($this->_user_session->current_perspective);
				
			// create menu for main and perspective
			$this->view->placeholder('mainmenu')->append($main_menu->get_htmlmainmenu());
			$this->view->placeholder('perspective_menu')->append($perspective_menu->get_htmlperspectivemenu());
				
			// create homelink
			$this->view->placeholder('homelink')->append($this->_user_session->perspective);
				
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
	
	public function indexAction()
	{
		
		$this->_helper->viewRenderer->setNoRender(true);
		echo $this->view->action('main','task',null);
		
	}
	
	
	
	
}
?>