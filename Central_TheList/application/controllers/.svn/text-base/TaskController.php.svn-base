<?php

class taskController extends Zend_controller_Action
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
	private function createSearchQuery($keywords){
		$columns_to_search		= array(
									'creator_fullname',
									'assigned_to',
									'task_name',
									'task_status_name',
									'queue',
									'task_priority_name'
									);
		$sql					=  "SELECT * from tasks_search ";
		if (!empty($keywords)){
			$sql				.= "WHERE ";
			for ($i = 0; $i < count($columns_to_search); $i++){
				$column_name	 = $columns_to_search[$i];

				for ($x = 0; $x < count($keywords); $x++){
					$keyword			= addslashes($keywords[$x]);
					$where_clause		= "$column_name LIKE '%$keyword%' ";
					if(!(($i == count($columns_to_search) -1) && (($x == count($keywords) -1)))){
						$where_clause	.= ' OR ';
					}
					$sql				.= $where_clause;	
				}
				
			}
			
		}
		return $sql;
		//$tickets				= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		
	}
	public function searchAction(){
		$this->_helper->layout->disableLayout();
		$keywords		= (isset($_GET['keywords']) && !empty($_GET['keywords'])) ? explode(' ',$_GET['keywords']) : false;
		$content		= "<table  class='display' style='width:1120px'>"
						
						. "</table>";
		$sql			= $this->createSearchQuery($keywords);
		$tasks			= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		foreach ($tasks as $task){
			$ticket_row = "
					    	<tr>
		    			 		<td class='display' style='width:240px' id='edit_task' task_id='".$task['task_id']."'>".$task['task_name']."</td>
		    			 		<td class='display' style='width:240px'>".$task['master_task_name']."</td>
		    			 		<td class='display' style='width:240px'>".$task['creator_fullname']."</td>
		    			 		<td class='display' style='width:100px'>".$task['task_priority_name']."</td>
		    			 		<td class='display' style='width:100px'>".$task['task_status_name']."</td>
		    			 		<td class='display' style='width:80px'>".$task['createdate']."</td>
		    			   	</tr>";
			echo $ticket_row;
		}
		
		//echo $content;
	}
	public function mainAction() {
// 		$searchbox = new searchboxform(1);
		//$searchbox->setAction('/search/index');
		//$searchbox->setMethod('post');
// 		$this->view->searchbox = $searchbox;
		
		//$this->_helper->viewRenderer->setNoRender(true);
		$queue_query="SELECT q.queue_id,queue_name
							  FROM user_queue_mapping uqm
							  LEFT OUTER JOIN queues q ON uqm.queue_id=q.queue_id
							  WHERE uid=".$this->_user_session->uid;
		$queues= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($queue_query);
		
		$queue_list='';
		$queue_list.="<li><a href='#0'>My tasks</a></li>";
		foreach($queues as $queue){
			$queue_list.="<li><a href='#".$queue['queue_id']."'>".$queue['queue_name']."</a></li>";
		}
		
		//add search to queue_list
			$queue_list.="<li><a href='#searchresults'>Search Results</a></li>";
		
		
		
		
		$this->view->queue_list=$queue_list;
		
		$task_query="SELECT t1.task_id,t1.task_name,t2.task_name AS 'master_task',q1.queue_name AS 'queue',q2.queue_name AS 'queue_owner',i1.item_name AS 'priority',i2.item_name AS 'status',
							 DATE_FORMAT(t1.createdate,'%m/%d/%Y %h:%i %p' )AS 'date'
							 FROM tasks t1
							 LEFT OUTER JOIN tasks t2 ON t1.master_task_id = t2.task_id
							 LEFT OUTER JOIN queues q1 ON t1.task_queue_id  = q1.queue_id
							 LEFT OUTER JOIN queues q2 ON t1.task_queueowner_id = q2.queue_id
							 LEFT OUTER JOIN items i1 ON t1.task_priority = i1.item_id
							 LEFT OUTER JOIN items i2 ON t1.task_status   = i2.item_id
							 WHERE t1.task_owner=". $this->_user_session->uid."
							 AND i2.item_name='Open'
							 ORDER BY t1.task_id
							";
			
		$tasks = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($task_query);
		
		$content ="<div id='0'>
		    			   <table  class='display' style='width:1120px'>
		    			  	 <tr class='header'>
		    			  		<td class='display' style='width:100px'>Task Name</td>
		    			  		<td class='display' style='width:160px'>Master Task Name</td>
		    			  		<td class='display' style='width:120px'>Queue Name</td>
		    			  		<td class='display' style='width:100px'>Priority</td>
		    			  		<td class='display' style='width:80px'>Createdate</td>
		    			  	 </tr>
		    			  	</table>
		    			  	<div style='overflow: auto;height: 450px; width: 1140px;'>
		  					<table class='display' style='width: 1120px;'>
		    			  ";
		foreach($tasks as $task){
			$content.="
		    			   	<tr>
		    			 		<td class='display' style='width:100px' id='edit_task' task_id='".$task['task_id']."'>".$task['task_name']."</td>
		    			 		<td class='display' style='width:160px'>".$task['master_task']."</td>
		    			 		<td class='display' style='width:120px'>".$task['queue']."</td>
		    			 		<td class='display' style='width:100px'>".$task['priority']."</td>
		    			 		<td class='display' style='width:80px'>".$task['date']."</td>
		    			   	</tr>";
		}
		$content.="</table>
		        		   </div>
		        		   </div>
		  				   ";
		
		foreach($queues as $queue){
			$content.="<div id='".$queue['queue_id']."'>
								
							   <table  class='display' style='width:1120px'>
							  
							   <tr class='header'>
		    			  			<td class='display' style='width:240px'>Task Name</td>
		    			  			<td class='display' style='width:240px'>Master Task Name</td>
		    			  			<td class='display' style='width:220px'>Owner</td>
		    			  			<td class='display' style='width:80px'>Priority</td>
		    			  			<td class='display' style='width:100px'>Status</td>
		    			  			<td class='display'>Createdate</td>
		    			  	   </tr>
		    			  	   
		    			  	   </table>
		    			  	   <div style='overflow: auto;height: 450px; width: 1140px;'>
		  						<table  class='display' style='width: 1120px;'>
							   ";
			$query="SELECT t1.task_id,t1.task_name,CONCAT(u.firstname,' ',u.lastname)as 'name',t2.task_name AS 'master_task',q1.queue_name AS 'queue',q2.queue_name AS 'queue_owner',i1.item_name AS 'priority',i2.item_name AS 'status',DATE_FORMAT(t1.createdate,'%m/%d/%Y %h:%i %p' )AS 'date'
							FROM tasks t1
							LEFT OUTER JOIN tasks t2 ON t1.master_task_id = t2.task_id
							LEFT OUTER JOIN queues q1 ON t1.task_queue_id  = q1.queue_id
							LEFT OUTER JOIN queues q2 ON t1.task_queueowner_id = q2.queue_id
							LEFT OUTER JOIN items i1 ON t1.task_priority = i1.item_id
							LEFT OUTER JOIN items i2 ON t1.task_status   = i2.item_id
							LEFT OUTER JOIN users u ON t1.task_owner = u.uid
							WHERE t1.task_queue_id=".$queue['queue_id'] . " AND t1.task_status = 12";
		
			$tasks = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($query);
		
			foreach($tasks as $task){
				 
				$content.="
		    					<tr>
		    						<td class='display' style='width:240px' id='edit_task' task_id='".$task['task_id']."'>".$task['task_name']."</td>
		    			 			<td class='display' style='width:240px'>".$task['master_task']."</td>
		    			 			<td class='display' style='width:220px'>".$task['name']."</td>
		    			 			
		    			 			<td class='display' style='width:80px'>".$task['priority']."</td>
		    			 			<td class='display' style='width:100px'>".$task['status']."</td>
		    						<td class='display'>".$task['date']."</td>
		    					</tr>";
			}
		
		
			$content.="</table>
							   </div></div>";


		}
		$content.="<div id='searchresults'>
							<table>
														<tr class='header'>
							    			  				<th class='display' style='width:240px'>Task Name</th>
							    			  				<th class='display' style='width:240px'>Master Task Name</th>
							    			  				<th class='display' style='width:220px'>Owner</th>
							    			  				<th class='display' style='width:80px'>Priority</th>
							    			  				<th class='display' style='width:100px'>Status</th>
							    			  				<th class='display'>Createdate</th>
							    			  	   		</tr>
							 </table>
								</div>";
		
		$this->view->content=$content;
	}
	
	
	public function addtaskpopupAction(){
		$this->_helper->layout->disableLayout();
		
		$task_type		= $_GET['task_type'];
		$task_type_id	= $_GET['task_type_id'];
		
		
		$addtaskform = new Thelist_Taskform_addtask(null,array(
														 '$task_type',
														  $task_type_id
												 )
									  );
		$addtaskform->setAction('/task/addtaskpopup?task_type='.$task_type.'&task_type_id='.$task_type_id);
		$addtaskform->setMethod('post');
		
		
		
		$this->view->addtaskform=$addtaskform;
		if($this->getRequest()->isPost()){
				
			if ($addtaskform->isValid($_POST)) {
				// it's valid
		
				//if($_POST['existtask']== 0){
		
					$task_id= Zend_Registry::get('database')->get_tasks()->insert(
					array(
									'task_name' 		=> 	$_POST['taskname'],
									'task_owner'		=>  $_POST['owner'],
									'task_status'		=>  $_POST['status'],
									'task_queue_id' 	=>  $_POST['task_queue'],
									'creator'			=>	$this->_user_session->uid,
									//'task_queueowner_id'=>  $_POST['queueowner'],
									'task_priority' 	=>  $_POST['priority']
					)
					);
				//}else{
				//	$task_id= $_POST['existtask'];
				//}
					
				if($task_type=='project'){
					$project	= new Thelist_Model_projects($task_type_id);
					$project->add_task($task_id);
					if(strlen($_POST['note'])>3){
						$tasks = $project->get_tasks();
						$tasks[$task_id]->add_note($_POST['note']);
					}
				}else if($task_type=='building'){
					$building   = new Thelist_Model_buildings($task_type_id);
					$building->add_task($task_id);
					if(strlen($_POST['note'])>3){
						$tasks = $building->get_tasks();
						$tasks[$task_id]->add_note($_POST['note']);
					}
				}else if($task_type=='none'){
					if(strlen($_POST['note'])>3){
						$task = new Thelist_Model_tasks($task_id);
						$task->add_note($_POST['note']);
					}
					//doing nothing
				}
				
				//send e-mail notification to user if OWNER exists; if not, e-mail the queue

				$first_note			= $_POST['note'];
				$ticket_creator_uid	= $this->_user_session->uid;	
				$task_name			= $_POST['taskname'];
				$assigned_to		= $_POST['owner'];
				$sql_user_creator	= "SELECT CONCAT(firstname, ' ', lastname) AS fullname FROM users WHERE uid = '$ticket_creator_uid'";				
				$user_creator		= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql_user_creator);
				$user_creator_name	= $user_creator[0]['fullname'];
				$task_queue_id		= $_POST['task_queue'];

				
				$email = new Thelist_Utility_email();
				
				if($_POST['owner']){
					$email_content		= "$user_creator_name has assigned you a ticket.  Please open your tasks to review ticket.\n\n";					
					$email_subject		= "$task_name has been assigned to you.";
					$sql_user_assign	= "SELECT email from users where uid = '$assigned_to'";
					$user_assign_to		= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql_user_assign);					
					$user_email			= $user_assign_to[0]['email'];
			
					$email_content		.= "First note:\n\n\"$first_note\"";
					$email->send($user_email, $email_content, $email_subject);
				}else{
					
					$sql_queue_name		= "SELECT queue_name, queue_email FROM queues WHERE queue_id = '$task_queue_id'";
					$queue_records		= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql_queue_name);
					$queue_name			= $queue_records[0]['queue_name'];
					$queue_email		= $queue_records[0]['queue_email'];
					$email_content		= "$user_creator_name has assigned \"$queue_name\" a ticket.  Please open your tasks to review ticket.\n\n";
					$email_content		.= "First note:\n\n\"$first_note\"";
					$email_subject		= "\"$task_name\" has been assigned to $queue_name";
					$email->send($queue_email, $email_content, $email_subject);					
				}
			
		
					
				
					
					
					
				echo "<script>
							 window.opener.location.href = window.opener.location.href;
							 window.close();
						  </script>";
			}else{  // it's invalid
		
			}
				
		}
	}
	
	public function edittaskpopupAction(){
		
		$this->_helper->layout->disableLayout();
		$task_id   = $_GET['task_id'];
		$task      = new Thelist_Model_tasks($task_id);
		
		$edittaskform = new Thelist_Taskform_edittask($task_id);
		$edittaskform->setAction('/task/edittaskpopup?task_id='.$task_id);
		$edittaskform->setMethod('post');
		$this->view->edittaskform=$edittaskform;
		
		if($this->getRequest()->isPost())
		{

			
			
			
			if(isset($_POST['save_task']))
			{
				if(strlen($_POST['newnote'])>3)
				{
					$task->add_note($_POST['newnote']);
				}
				
					$queue_id_before			= $task->get_task_queue_id();
					$owner_before				= $task->get_task_owner();
					
					$task->set_name($_POST['taskname']);
					$task->set_priority($_POST['priority']);
					$task->set_owner($_POST['owner']);
					$task->set_status($_POST['status']);
					$task->set_queue_id($_POST['task_queue']);
					
					
					
					$queue_id_after				= $_POST['task_queue'];
					$owner_new					= $_POST['owner'];
					$session_uid				= $this->_user_session->uid;

					$email = new Thelist_Utility_email();
					
					if($owner_new){
						if(($owner_new != $owner_before) && ($owner_new != $session_uid)){
							//send e-mail to new owner 
							//1. who assigned the ticket
							//2. the name of the task

							
							$session_user_info		= $this->get_user_info($session_uid);
							$email_body				= $session_user_info['fullname'] . " said: \"" . $_POST['newnote'] . "\"";
							$new_owner_info			= $this->get_user_info($owner_new);
							$new_task_name			= $_POST['taskname'];
							$email_subject			= $session_user_info['fullname'] . " has reassigned \"$new_task_name\" to you";
							$to_email_address		= $new_owner_info['email'];
							$email->send($to_email_address, $email_body, $email_subject);
							
							
						}
					}
					elseif($queue_id_after != $queue_id_before){
							
							$session_user_info		= $this->get_user_info($session_uid);
							$email_body				= $session_user_info['fullname'] . " said: \"" . $_POST['newnote'] . "\"";
							$new_queue_info			= $this->get_queue_info($queue_id_after);
							$new_task_name			= $_POST['taskname'];
							$email_subject			= $session_user_info['fullname'] . " has reassigned \"$new_task_name\" to " . $new_queue_info['name'];
							$to_email_address		= $new_queue_info['email'];
							$email->send($to_email_address, $email_body, $email_subject);
							//send e-mail to queue_owner
							//1. who assigned the ticket
							//2. the name of the task
					}
					//$tasks[$task_id]->set_queueowner_id($_POST['queueowner']);
			}
			
			echo "<script>
				window.opener.location.href =	window.opener.location.href;
				window.close();
				</script>";
		}
	}
	private function get_user_info($uid){
		
		$sql					= "SELECT CONCAT(firstname, ' ', lastname) AS fullname, firstname, lastname, email FROM users WHERE uid = '$uid'";
		$user					= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		$user_info				= array();
		$user_info['fullname']	= $user[0]['fullname'];
		$user_info['email']		= $user[0]['email'];
		$user_info['firstname']	= $user[0]['firstname'];
		$user_info['lastname']	= $user[0]['lastname'];
		return $user_info;
				
	}
	private function get_queue_info($task_queue_id){
		$sql_queue_name			= "SELECT queue_name, queue_email FROM queues WHERE queue_id = '$task_queue_id'";
		$queue_records			= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql_queue_name);
		$queue_name				= $queue_records[0]['queue_name'];
		$queue_email			= $queue_records[0]['queue_email'];
		$queue_info				= array();
		$queue_info['name']		= $queue_name;
		$queue_info['email']	= $queue_email;
		return $queue_info;
	}
	public function getqueuememberAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		$sql="SELECT u.uid,u.firstname,u.lastname
				  FROM user_queue_mapping uqm
				  LEFT OUTER JOIN users u ON uqm.uid=u.uid
				  WHERE queue_id=".$_GET['queue_id']."
				  AND u.uid IS NOT NULL";
	
		$users = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
		$output="<option value='0'>---no one---</option>";
		
		foreach($users as $user){
			$output.="<option value='".$user['uid']."'>".$user['firstname']."</option>";
		}
		echo $output;
	
	}

}
?>