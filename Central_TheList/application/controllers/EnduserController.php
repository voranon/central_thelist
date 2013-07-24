<?php

//exception codes 17300-17399

class EnduserController extends Zend_Controller_Action
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
	
	public function postDispatch(){

	}
	
	public function endusersearchAction()
	{
		$sql = "SELECT building_id, building_name FROM buildings
				";
		
		$all_buildings = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		if (isset($all_buildings['0'])) {
			$this->view->buildings	=  $all_buildings;
		} else {
			$this->view->error	= 'No Buildings in Database';
		}
	}
	
	public function currentservicesAction()
	{
		if (isset($_GET['end_user_service_id'])) {
			
			$end_user			= new Thelist_Model_enduserservice($_GET['end_user_service_id']);
			$sales_quotes		= $end_user->get_sales_quotes();

			if ($sales_quotes != null) {
				
				foreach ($sales_quotes as $sales_quote) {

					if ($sales_quote->get_service_plan_quote_maps() != null) {
						foreach($sales_quote->get_service_plan_quote_maps() as $service_plan_map) {
							$service_plan_map->get_service_plan();
							$service_plan_map->get_service_plan_quote_option_maps();
							$service_plan_map->get_service_plan_quote_eq_type_maps();
						}
					}
					
					$return_sales_quotes[]	= $sales_quote->toArray();
				}
				
				//correct times to american format
				$array_tools			= new Thelist_Utility_arraytools();
				$return_sales_quotes 	= $array_tools->convert_occurrences_of_datetime_in_array($return_sales_quotes, 'american');
				
				$this->view->sales_quotes = $return_sales_quotes;
				
			} else {
				$this->view->sales_quotes = null;
			}
			
			$contacts		= $end_user->get_contacts();
			
			if ($contacts != null) {
			
				foreach ($contacts as $contact) {
					$return_contacts[]	= $contact->toArray();
				}
				
				$this->view->contacts = $return_contacts;
				
			} else {
				$this->view->contacts = null;
			}
		}
	}

	public function unitdropdownajaxAction()
	{
		
		if (isset($_GET['unit_group_id'])) {
			
			$sql = 	"SELECT * FROM units u
					INNER JOIN unit_group_mapping ugm ON ugm.unit_id=u.unit_id
					WHERE u.building_id='".$_GET['building_id']."'
					AND ugm.unit_group_id='".$_GET['unit_group_id']."'
					";
			
			$this->view->units = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
		} else {
			
			$this->view->units = Zend_Registry::get('database')->get_units()->fetchAll('building_id='.$_GET['building_id']);
			
		}
	
		
	}
	
	/*
	
	public function unitajaxAction(){
		$unit_id = $_GET['unit_id'];
	
		$sql_unit="SELECT b.building_name,u.number,u.unit_id
					   FROM units u
					   LEFT OUTER JOIN buildings b ON u.building_id = u.building_id
					   WHERE u.unit_id = ".$unit_id;
	
		$this->view->unit = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql_unit);
	
	
		$sql_contact="SELECT eus.end_user_service_id,euscm.primary_contact, CONCAT(c.firstname,' ',c.lastname) AS 'name'
					  		  FROM end_user_services eus
					  		  LEFT OUTER JOIN end_user_services_contact_mapping euscm ON eus.end_user_service_id = euscm.end_user_service_id
					  		  LEFT OUTER JOIN contacts c ON euscm.contact_id = c.contact_id
					  		  WHERE eus.unit_id=".$unit_id."
					  		  AND c.contact_id IS NOT NULL
					  		  ORDER BY eus.end_user_service_id,euscm.primary_contact DESC";
	
		$this->view->contacts = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql_contact);
	}
	
	public function addnewprospectAction(){
		$this->_helper->layout->disableLayout();
		
		//$contact_type 		= $_GET['contact_type'];
		//$contact_type_id	= $_GET['contact_type_id'];
		
		$addcontactform = new Thelist_Contactform_addcontact(null,'end_user');
		$addcontactform->setAction('');
		$addcontactform->setMethod('post');
		
		
			
		$this->view->addcontactform =$addcontactform;
		
		if($this->getRequest()->isPost()){
			
			if ($addcontactform->isValid($_POST)){
				// it's valid
				if($_POST['contact']==0){
					/// enter new contact
		
					$contact_id=Zend_Registry::get('database')->get_contacts()->insert(
					array(
								'firstname'		=> $_POST['firstname'],
								'lastname'  	=> $_POST['lastname'],
								'streetnumber'  => $_POST['streetnumber'],
								'streetname'  	=> $_POST['streetname'],
								'streettype'  	=> $_POST['streettype'],
								'city'			=> $_POST['city'],
								'state'			=> $_POST['state'],
								'zip'			=> $_POST['zip'],
								'cellphone' 	=> $_POST['cellphone'],
								'homephone' 	=> $_POST['homephone'],
								'officephone'   => $_POST['officephone'],
								'fax'			=> $_POST['fax'],
								'email'			=> $_POST['email'],
								'creator'       => $this->_user_session->uid
		
						)
					);
		
		
				}else{ // select existing contact
					$contact_id=$_POST['contact'];
				}
						
					$unit = new Thelist_Model_units(  $_GET['unit_id'] );
					
					$enduser_service_id = $unit->add_new_endusers();
					
					$enduser_service	= new Thelist_Model_enduserservice( $enduser_service_id );
					$enduser_service->add_contact($contact_id,$_POST['title']);
					
					$enduser_service->set_primary_contact($contact_id);
					
					 
					
				echo "<script type='text/javascript'>
						$(function(){
						 window.opener.location.href = window.opener.location.href;
						window.close();
						});
					  </script>";
				
		
		
			}else{
				// form is invalid
		
			}
		
		}
	}
	*/
}
?>