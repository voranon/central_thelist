<?php


class thelist_taskform_edittask extends Zend_Form{
	
	private $database;
	
	public function __construct($task_id,$options=null)
	{
		
		parent::__construct($options);
		

		
		
		$task = Zend_Registry::get('database')->get_tasks()->fetchRow("task_id=".$task_id);
		
		
		
		$taskname  = new Zend_Form_Element_Text('taskname',
												array(
													'label'      => 'Task Name:',
													'value'      => $task['task_name'],
		   						 					'decorators' => array(
		   						 										  	new edittask_decorator()
		   						 										 ),
													 )
												);
		
		$priority = new Zend_Form_Element_Select('priority',
												array(
													'label'      => 'Priority:',
													'value'      => $task['task_priority'],
													'decorators' => array(
																			new edittask_decorator()
													 					  ),
													  )
												);
		$priority->setRegisterInArrayValidator(false);
		
		$status   = new Zend_Form_Element_Select('status',
												array(
													'label'      => 'Status:',
													'value'      => $task['task_status'],
													'decorators' => array(
																			new edittask_decorator()
													 					  ),
													  )
												);
		$status->setRegisterInArrayValidator(false);
		
		
		$queue   = new Zend_Form_Element_Select('task_queue',
												array(
													'label'      => 'Queue:',
													'value'      => $task['task_queue_id'],
													'decorators' => array(
																			new edittask_decorator()
																		  ),
													 )
												);
		$queue->setRegisterInArrayValidator(false);
		
		/*   3/2/2012
		$queueowner   = new Zend_Form_Element_Select('queueowner',
												array(
													'label'      => 'Queue Owner:',
													'value'      => $task['task_queueowner_id'],
													'decorators' => array(
																			new edittask_decorator()
																		 )
													 )
													);
		$queueowner->setRegisterInArrayValidator(false);
		*/
		
		$owner	= new Zend_Form_Element_Select('owner',
												array(
													'label'      => 'Assign to:',
													'value'      => $task['task_owner'],
													'decorators' => array(
																			new edittask_decorator($task_id,$task['task_queue_id'])
																		  ),
													 ));
		
		$notes = new Zend_Form_Element_Textarea('notes',
												array(
													'label'      => 'Notes:',
													
													'decorators' => array(
																			new edittask_decorator($task_id)
																		  ),
													 ));
		
		
		
		$newnote   = new Zend_Form_Element_Textarea('newnote',
												array(
													'label'      => 'New Note:',
													'decorators' => array(
																			new edittask_decorator()
																		  ),
													 ));
		
		
		$save_task = new Zend_Form_Element_Submit('save_task',
													 array(
													'label'      =>'',
													'value'		 =>'save',
							   						'decorators' => array(new edittask_decorator())
							   						)
												 );
		/*
		$delete_task = new Zend_Form_Element_Submit('delete_task',
														array(
														'label'      =>'',
														'value'		 =>'delete',
							   						 	'decorators' => array(new edittask_decorator())
															 )
													);
												
		*/
		
		
		
		//$this->addElements(array($taskname,$status,$queue,$queueowner,$owner,$priority,$notes,$newnote,$save_task,$delete_task));   3/2/2012
		$this->addElements(array($taskname,$status,$queue,$owner,$priority,$newnote,$notes,$save_task));
		
	}
}

class edittask_decorator extends Zend_Form_Decorator_Abstract
{
	
	
	private $task_id;
	private $task_queue_id;
	private $database;
	
	public function __construct($task_id=null,$queue_id=null){
		$this->task_id		 =	$task_id;
		$this->task_queue_id =	$queue_id; 
	}
	
	public function render($content){
		
		
		

		$element = $this->getElement();
		$messages = $element->getMessages();
		$name    = htmlentities($element->getFullyQualifiedName());
		$label   = htmlentities($element->getLabel());
		$id      = htmlentities($element->getId());
		$value   = htmlentities($element->getValue());
		
		
		$error   = $element->getView()->formErrors($messages);
		
	    if($name=='taskname')
		{
		$format="<fieldset>              
				 <legend>Task:</legend>  
				 <table style='width:580px'>";  //when not using existing task put this 12/19/2011                 
				 
		$format.="<tr>
						<td colspan='All'>
							<table style='width:580px'>
							<tr>
								<td style='width:90px'>%s</td>
								<td style='width:150px'>
									<input id='task_name' class='text' type='text' name='%s' id='%s' value='%s'></input>
								</td>
								<td>%s</td>
							
							</tr>
							</table>
						</td>
				 </tr>
				 <tr>
					<td colspan='All'>
						<table style='width:570px'>";
		
		$markup  = sprintf($format,$label,$name,$name,$value,$element->getView()->formErrors($messages));
		return $markup;
		}else if($name=='status'){

		$taskstatuss = Zend_Registry::get('database')->get_items()->fetchAll(array("item_type='task_status'","item_active=1" ));
			
		$format="<tr>
					<td style='width:90px'>%s</td>
				 	<td style='width:150px'>
				 		<select name='%s' id='%s'>";
		
		foreach($taskstatuss as $taskstatus){
			if($value == $taskstatus['item_id']){
				$format.="<option value='".$taskstatus['item_id']."' selected>".$taskstatus['item_value']."</option>";
			}else{
				$format.="<option value='".$taskstatus['item_id']."'>".$taskstatus['item_value']."</option>";
			}
		}
		
		$format.="</select>
				  </td>";
		return sprintf($format,$label,$name,$name);
		}else if($name=='task_queue'){
			$format="<tr>
					<td>%s</td>
					<td>
						<select name='%s' id='%s'>";
					$format.="<option value=''>----</option>";
				
				$queues = Zend_Registry::get('database')->get_queues()->fetchAll();
				foreach($queues as $queue){
					if($value == $queue['queue_id']){
				$format.="<option value='".$queue['queue_id']."' selected>".$queue['queue_name']."</option>";
					}else{
				$format.="<option value='".$queue['queue_id']."'>".$queue['queue_name']."</option>";
					}
			
				}
			 $format.="</select>
					</td>
					<td colspan='2'>%s</td>
			  		</tr>";
		return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
		}else if($name=='queueowner'){
		$format="
				<tr>
					<td>%s</td>
					<td>
						<select name='%s' id='%s'>";
					$format.="<option value=''>----</option>";
					
		$queues = Zend_Registry::get('database')->get_queues()->fetchAll();
		
		foreach($queues as $queue){
			if($value == $queue['queue_id']){
				$format.="<option value='".$queue['queue_id']."' selected>".$queue['queue_name']."</option>";
			}else{
				$format.="<option value='".$queue['queue_id']."'>".$queue['queue_name']."</option>";
			}
		}
		$format.=	   "</select>
					</td>
					<td colspan='2'>%s</td>
				</tr>
			   ";
		return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
		}else if($name=='owner'){
		$format="<tr>
					<td>%s</td>
				 	<td>
					 	<select name='%s' id='%s'>";
		
		
		$query="SELECT u.uid, u.firstname
				FROM user_queue_mapping uqm
				LEFT OUTER JOIN users u ON uqm.uid=u.uid
				WHERE queue_id=".$this->task_queue_id; 
								 
		
		$users = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($query);
			$format.="<option value='0'>---Select One---</option>";
		foreach($users as $user){
			if($value == $user['uid']){
				$format.="<option value='".$user['uid']."' selected>".$user['firstname']."</option>";
			}else{
				$format.="<option value='".$user['uid']."'>".$user['firstname']."</option>";
			}
		}
						
		$format.="	 	</select>
				 	</td>";
				 
		return sprintf($format,$label,$name,$name);
		}else if($name=='priority'){
			
		$format="<td>%s</td>
				 <td>
					<select name='%s' id='%s'>";
		
		$task_statuses=Zend_Registry::get('database')->get_items()->fetchAll(array("item_type='task_priority'","item_active=1"));
			foreach($task_statuses as $status){
				if($value == $status['item_id']){
					$format.="<option value='".$status['item_id']."' selected>".$status['item_value']."</option>";
				}else{
					$format.="<option value='".$status['item_id']."'>".$status['item_value']."</option>";
				}
			
			}
		$format.="	</select>
				</td>
				</tr>";
		return sprintf($format,$label,$name,$name);
		
		
		
		}else if($name == 'notes'){
			$notes = Zend_Registry::get('database')
						->get_thelist_adapter()
						->fetchAll("SELECT CONCAT(u.firstname,' ',u.lastname) AS NAME,n.note_content,DATE_FORMAT(n.createdate,'%m/%d/%Y %h:%i %p' )AS 'createdate'
									FROM task_note_mapping tm
									LEFT OUTER JOIN users u ON tm.creator=u.uid
									LEFT OUTER JOIN notes n ON tm.note_id=n.note_id
									WHERE tm.task_id=".$this->task_id." ORDER BY n.createdate DESC");
			
			//$notes = Zend_Registry::get('database')->get_task_note_mapping()->fetchAll("task_id=".$this->task_id);
			$format="
						<tr align='center'>
							<td colspan='6' align='left'>
							%s
							</td>
						</tr>";
			foreach($notes as $note){
			$format.="
						<tr align='center'>
							<td colspan='6' align='left'>
							".$note['NAME']."::".$note['createdate']."
							</td>
						</tr>
						<tr align='center'>
							<td colspan='6'>
								<textarea cols='60' rows='3' style='resize:none;' readonly>".$note['note_content']."</textarea>
							</td>
						</tr>
						";
			}
			
		
		return sprintf($format,$label,$name,$name);
		}else if($name=='newnote'){
		$format="
				<tr align='center'>
					<td colspan='6' align='left'>
						%s
					</td>
				</tr>
				<tr align='center'>
					<td colspan='6'>
						<textarea name='%s' id='%s' cols='60' rows='3' style='resize:none;'></textarea>
					</td>
				</tr>
				";
		return sprintf($format,$label,$name,$name);
			
		}else if($name=='save_task'){
		$format="
				<tr align='center'>
					<td colspan='6'>
						<input type='submit' class='button' name='%s' value='%s'></input>
					</td>
				</tr>";
				
		return sprintf($format,$name,$value);
		}else if($name=='delete_task'){
		$format="
				<tr align='center'>
					<td colspan='6'>
						<input type='submit' class='button' name='%s' value='%s'></input>
					</td>
				</tr>
				</table>
				</td>
				</tr>
				</table>
				</fieldset>";
		return sprintf($format,$name,$value);
		}
		
	}
}
?>