<?php

class projectController extends Zend_controller_Action{
	
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
	
	public function postDispatch(){
		
	}
	
	
	public function indexAction()
	{
		
		
		$projects=Zend_Registry::get('database')->get_thelist_adapter()->fetchAll("SELECT project_id,project_name,note1,DATE_FORMAT(createdate,'%m/%d/%Y %h:%i %p' )AS 'date'
															  FROM projects"
												   			);
		
		
		
		
		foreach($projects as $project){
			$this->view->project_table.="<tr>
											 <td class='display'><a href='/project/edit?project_id=".$project['project_id']."'>".$project['project_name']."</a>
											 </td>
											 <td class='display'>".$project['note1']."</td>
											 <td class='display'>".$project['date']."</td>
										 </tr>";
		}
		
			
	}
	public function createAction(){
		$this->_helper->layout->disableLayout();
		
		$createprojectform = new Thelist_Projectform_createproject();
		$createprojectform->setAction('/project/create');
		$createprojectform->setMethod('post');
		$this->view->createprojectform=$createprojectform;
		
		if($this->getRequest()->isPost()){
			if ($createprojectform->isValid($_POST)) { // it's valid
				
				$data = array(
								'project_name'  	=>  $_POST['project_name'],
								'note1'				=>  $_POST['project_address'],
								'note2'				=>  $_POST['project_mustknow'],
								'project_entity_id' =>	$_POST['project_entity']  
							  );
				$project_id = Zend_Registry::get('database')->get_projects()->insert($data);	

				$project = new Thelist_Model_projects($project_id);
				$project->add_project_entity($_POST['project_entity']);
				
							
				echo "<script>
					  window.opener.location.href =	window.opener.location.href;
					  window.close();
					  </script>";
			
				
			}		
		}
	}
	public function editAction(){
		$project_id				= $_GET['project_id'];
		
		$project				= new Thelist_Model_projects($project_id);
		$this->view->project_id = $project_id;
		
		$this->view->project_name	=$project->get_name();
		$this->view->project_note1	=$project->get_note1();
		$this->view->project_note2	=$project->get_note2();
		
		$buildings=$project->get_buildings();
		if(is_array($buildings)){
			foreach($buildings as $building)
			{
			$this->view->building_list.="<tr>
											<td class='display'>".$building->get_name()."</td>
											<td class='display'>".$building->get_numberofunit()."</td>
											<td class='display'>
												<input class='button' type='button' id='edit_building' building_id='".$building->get_building_id()."' project_id='".$project_id."' value='Edit'></input>
											</td>
										 </tr>";
			
			}
		}
		$contacts=$project->get_contacts();
		if(is_array($contacts)){
			foreach($contacts as $contact){
			
			$this->view->contact_list.="<tr>
											<td class='display'>".$contact->get_titlename()."</td>
											<td class='display'>".$contact->get_firstname().' '.$contact->get_lastname()."</td>
											<td class='display'>".$contact->get_cellphone()."</td>
											<td class='display'>".$contact->get_email()."</td>
											<td class='display'>
												<input class='button' type='button' id='edit_contact' contact_type='project' contact_type_id='".$project_id."' contact_id='".$contact->get_contact_id()."' value='Edit' ></input>
											</td>
										</tr>";
			}
		}
		$tasks=$project->get_tasks();
		if(is_array($tasks))
		{
			foreach($tasks as $task){
			$this->view->task_list.="<tr>
											<td class='display'>".$task->get_name()."</td>
											<td class='display'>
												<input class='button' type='button' id='edit_task' task_id='".$task->get_task_id()."' value='Edit'></input>
											</td>
									</tr>";
			}
		}
		
		$project_entities=$project->get_project_entities();
		if(is_array($project_entities)){
			foreach($project_entities as $project_entity){
				$this->view->project_entitiy_list.="<tr>
														<td class='display'>".$project_entity->get_project_entity_name()."</td>
														<td class='display'>
															<input class='button' project_entity_id='".$project_entity->get_project_entity_id()."' type='button' id='edit_entity' value='Edit'></input>
														</td>
													</tr>";
			}
		}
		
		
	}
	public function deleteAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		echo 'delete';
	}
	
	
	
	/*
	public function addcontactAction(){
		$this->_helper->layout->disableLayout();
		
		$project_id = $_GET['project_id'];
		
		$addcontactform = new Thelist_Contactform_addcontact(null,'project');
		$addcontactform->setAction('/project/addcontact?project_id='.$project_id);
		$addcontactform->setMethod('post');
		
		$this->view->addcontactform =$addcontactform;
		
		if($this->getRequest()->isPost()){
			
			if ($addcontactform->isValid($_POST)) { // it's valid
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
																			'officephone'   => $_POST['office'],
																			'fax'			=> $_POST['fax'],
																			'email'			=> $_POST['email'],
																			'creator'       => $this->_user_session->uid
																			
															)
					);
						
				
				}else{ // select existing contact
					
					$contact_id=$_POST['contact'];
				}
				
				$project= new Thelist_Model_projects($project_id);
				$project->add_contact($contact_id, $_POST['title']);
				
				echo "<script>
						 window.opener.location.href = window.opener.location.href;
						 window.close();
					  </script>";
				
			}else{
				// form is invalid
				
			} 
			
		}
	}
	
	public function editcontactAction(){
		$this->_helper->layout->disableLayout();
		
		$project_id=$_GET['project_id'];
		$contact_id=$_GET['contact_id'];
		
		$mode = array('project',$project_id);
		
		$editcontactform = new Thelist_Contactform_editcontact(null,$mode,$contact_id);
		
		$editcontactform->setAction('/project/editcontact?project_id='.$project_id.'&contact_id='.$contact_id);
		$editcontactform->setMethod('post');
		
		$this->view->editcontactform =$editcontactform;
		
		$project	= new Thelist_Model_projects($project_id);
		$contacts	= $project->get_contacts();
		
		if($this->getRequest()->isPost()){
			if(isset($_POST['save_contact'])){
				
				$project->set_contact_title($contact_id,$_POST['title']);
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
				
			}else if(isset($_POST['delete_contact'])){
				$project->delete_contact($contact_id);	
			}
			echo "<script>
					window.opener.location.href = window.opener.location.href;
					window.close();
				  </script>";
		}
		
		
		
		
	}
	
	public function gettaskajaxAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		
		$task = Zend_Registry::get('database')->get_tasks()->fetchRow("task_id=".$_GET['task_id']);
		echo $task['task_name'].'!'.$task['task_priority'].'!'.$task['task_status'].'!'.$task['task_queue_id'];
	}
	*/
	public function getqueuememberAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		$sql="SELECT u.uid,u.firstname,u.lastname
			  FROM user_queue_mapping uqm
			  LEFT OUTER JOIN users u ON uqm.uid=u.uid
			  WHERE queue_id=".$_GET['queue_id']."
			  AND u.uid IS NOT NULL";
		
		$users = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
		
		foreach($users as $user){
			$output.="<option value='".$user['uid']."'>".$user['firstname']."</option>";
		}
		$output.="<option value='0'>---no one---</option>";
		echo $output;
		
	}
	
	
	/*    3/5/2012
	public function getbuildingajaxAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		$building = Zend_Registry::get('database')->get_buildings()->fetchRow('building_id='.$_GET['building_id']);
		echo $building['building_name'].'!'.$building['building_alias'];
	}
	*/
	
	public function addentityAction(){
		$project_id = $_GET['project_id'];
				$this->_helper->layout->disableLayout();
			
		
		$addentityform = new Thelist_Entityform_addentity_to_project($project_id);
		
		$addentityform->setAction('/project/addentity?project_id='.$project_id);
		$addentityform->setMethod('post');
		$this->view->addentityform=$addentityform;
		
		if($this->getRequest()->isPost()){
			if ($addentityform->isValid($_POST)){
				
				$project = new Thelist_Model_projects($project_id);
				$project->add_project_entity($_POST['existentity']);
				
				echo "<script>
									  window.opener.location.href =	window.opener.location.href;
									  window.close();
									  </script>";
			}
		}
	}
	public function editentityAction(){
		$this->_helper->layout->disableLayout();
		$project_id 		= $_GET['project_id'];
		$project_entity_id  = $_GET['project_entity_id'];
		
		$editentityform = new Thelist_Entityform_editentity_to_project(null,$project_entity_id);
		
		$editentityform->setAction('/project/editentity?project_id='.$project_id.'&project_entity_id='.$project_entity_id);
		$editentityform->setMethod('post');
		$this->view->editentityform=$editentityform;
		
		if($this->getRequest()->isPost()){
			if ($editentityform->isValid($_POST)){
				
				
				$project = new Thelist_Model_projects($project_id);
				if(isset($_POST['delete_entity'])){
					$project->delete_project_entity($project_entity_id);
				}
				echo "<script>
					  window.opener.location.href =	window.opener.location.href;
					  window.close();
					  </script>";
					  
			}
		}
		
	}
	
	
} 
?>