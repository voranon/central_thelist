<?php

class contactController extends Zend_controller_Action{
	
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
	
	public function postDispatch(){
	
	}
	
	
	public function addcontactpopupAction(){
		$this->_helper->layout->disableLayout();
		
		$contact_type 		= $_GET['contact_type'];
		$contact_type_id	= $_GET['contact_type_id'];
		
		$addcontactform = new Thelist_Contactform_addcontact(null,$contact_type);
		$addcontactform->setAction('/contact/addcontactpopup?contact_type='.$contact_type.'&contact_type_id='.$contact_type_id);
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
	
		
				if($_GET['contact_type']=='project'){
					$project  = new Thelist_Model_projects($_GET['contact_type_id']);
					$project->add_contact($contact_id, $_POST['title']);
				}else if($_GET['contact_type']=='building'){
					$building = Thelist_Model_buildings($_GET['contact_type_id']);
					$building->add_contact($contact_id,$_POST['title']);
				}
				echo "	<script>
							 window.opener.location.href = window.opener.location.href;
							 window.close();
						</script>";
				
				
	
			}else{
				// form is invalid
	
			}
				
		}
	}
	
	
	//// ajax for contacts
	public function getcontactajaxAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		
	
		$contacts=Zend_Registry::get('database')->get_contacts()->fetchAll("contact_id=".$_GET['contactid']);
	
	
		foreach($contacts as $contact)
		{
			$firstname		=$contact['firstname'];
			$lastname 		=$contact['lastname'];
			$streetnumber 	=$contact['streetnumber'];
			$streetname		=$contact['streetname'];
			$streettype     =$contact['streettype'];
			$city		    =$contact['city'];
			$state		    =$contact['state'];
			$zip	  		=$contact['zip'];
			$cellphone		=$contact['cellphone'];
			$homephone		=$contact['homephone'];
			$officephone    =$contact['officephone'];
			$fax	  		=$contact['fax'];
			$email	  		=$contact['email'];
		}
	
		echo $firstname.'!'.$lastname.'!'.$streetnumber.'!'.$streetname.'!'.$streettype.'!'.$city.'!'.$state.'!'.$zip.'!'.$cellphone.'!'.$homephone.'!'.$officephone.'!'.$fax.'!'.$email;
	
	}
	
	public function editcontactpopupAction(){
		$this->_helper->layout->disableLayout();
		
		$contact_id			= $_GET['contact_id'];
		$contact_type 		= $_GET['contact_type'];
		$contact_type_id 	= $_GET['contact_type_id'];
		$contact			= new Thelist_Contactform_editcontact($contact_id);
	
		$mode = array($contact_type,$contact_type_id);
	
		$editcontactform = new Thelist_Contactform_editcontact(null,$mode,$contact_id);
	
		$editcontactform->setAction('/contact/editcontactpopup?contact_type='.$contact_type.'&contact_type_id='.$contact_type_id.'&contact_id='.$contact_id);
		$editcontactform->setMethod('post');
	
		$this->view->editcontactform =$editcontactform;
	
		
		if($this->getRequest()->isPost()){
			if(isset($_POST['save_contact'])){
				
				if($contact_type=='project'){
					$project	= new Thelist_Model_projects($contact_type_id);
					$project->set_contact_title($contact_id,$_POST['title']);
				}else if($contact_type=='building'){
					$building	= Thelist_Model_buildings($contact_type_id);
					$building->set_contact_title($contact_id,$_POST['title']);
				}
				
				$contact->set_firstname($_POST['firstname']);
				$contact->set_lastname($_POST['lastname']);
				$contact->set_streetnumber($_POST['streetnumber']);
				$contact->set_streetname($_POST['streetname']);
				$contact->set_streettype($_POST['streettype']);
				$contact->set_city($_POST['city']);
				$contact->set_state($_POST['state']);
				$contact->set_zip($_POST['zip']);
				$contact->set_cellphone($_POST['cellphone']);
				$contact->set_homephone($_POST['homephone']);
				$contact->set_officephone($_POST['officephone']);
				$contact->set_fax($_POST['fax']);
				$contact->set_email($_POST['email']);
				
				/*
				$contacts[$contact_id]->set_firstname($_POST['firstname']);
				$contacts[$contact_id]->set_lastname($_POST['lastname']);
				$contacts[$contact_id]->set_streetnumber($_POST['streetnumber']);
				$contacts[$contact_id]->set_streetname($_POST['streetname']);
				$contacts[$contact_id]->set_streettype($_POST['streettype']);
				$contacts[$contact_id]->set_city($_POST['city']);
				$contacts[$contact_id]->set_state($_POST['state']);
				$contacts[$contact_id]->set_zip($_POST['zip']);
				$contacts[$contact_id]->set_cellphone($_POST['cellphone']);
				$contacts[$contact_id]->set_homephone($_POST['homephone']);
				$contacts[$contact_id]->set_officephone($_POST['officephone']);
				$contacts[$contact_id]->set_fax($_POST['fax']);
				$contacts[$contact_id]->set_email($_POST['email']);
				*/
			}else if(isset($_POST['delete_contact'])){
				$project->delete_contact($contact_id);
			}
			echo "<script>
						window.opener.location.href = window.opener.location.href;
						window.close();
					  </script>";
		}
	}
	
}
?>