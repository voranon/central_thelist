<?php


class thelist_taskform_addtask extends Zend_Form{
	
	public function __construct($options = null,$mode=null)
	{
		parent::__construct($options);
		
		
		
		$belongto 		= $mode[0]; // project or others
		$belongto_id 	= $mode[1]; // projectid or others
		
		$existtask = new Zend_Form_Element_Select('existtask',
												array(
													'label'      => 'Existing Tasks:',
													'decorators' => array(
																			new task_decorator($belongto,$belongto_id)
													 					  ),
													  )
												);
		
		$taskname  = new Zend_Form_Element_Text('taskname',
												array(
													'label'      => 'Task Name:',
		   						 					'decorators' => array(
		   						 										  	new task_decorator()
		   						 										 ),
													 )
												);
	
		$taskname->setRequired(true);
		
		
		
		$priority = new Zend_Form_Element_Select('priority',
												array(
													'label'      => 'Priority:',
													'decorators' => array(
																			new task_decorator()
													 					  ),
													  )
												);
		$priority->setRegisterInArrayValidator(false);
		
		$status   = new Zend_Form_Element_Select('status',
												array(
													'label'      => 'Status:',
													'decorators' => array(
																			new task_decorator()
													 					  ),
													  )
												);
		$status->setRegisterInArrayValidator(false);
		
		
		$queue   = new Zend_Form_Element_Select('task_queue',
												array(
													'label'      => 'Queue:',
													'decorators' => array(
																			new task_decorator()
																		  ),
													 )
												);
		$queue->setRegisterInArrayValidator(false);
		$queue->setRequired(true);
		
		/*   3/2/2012
		$queueowner   = new Zend_Form_Element_Select('queueowner',
												array(
													'label'      => 'Queue owner:',
													'decorators' => array(
																		new task_decorator()
																		 ),
													 )
												    );
		$queueowner->setRegisterInArrayValidator(false);
		$queueowner->setRequired(true);
		*/
		
		
		$owner	= new Zend_Form_Element_Select('owner',
												array(
													'label'      => 'Assign to:',
													'decorators' => array(
																			new task_decorator()
																		  ),
													 ));
		$owner->setRegisterInArrayValidator(false);
		
		
		
		$note   = new Zend_Form_Element_Textarea('note',
												array(
													'label'      => 'Note:',
													'decorators' => array(
																			new task_decorator()
																		  ),
													 ));
		
		
		
		$add_task = new Zend_Form_Element_Submit('add_task',
													 array(
													'label'      =>'',
													'value'		 =>'Add',
							   						'decorators' => array(new task_decorator()
																		 ),
															)
												);
												
		
		
		if($mode=='project'){
			
		}
		
		//$this->addElements(array($existtask,$taskname,$priority,$status,$queue,$owner,$note,$add_task));
		//$this->addElements(array($taskname,$status,$queue,$queueowner,$owner,$priority,$note,$add_task));  3/2/2012
		$this->addElements(array($taskname,$status,$queue,$owner,$priority,$note,$add_task));
		
	}
}
class task_decorator extends Zend_Form_Decorator_Abstract
{
	
	private $mode; ///project or others
	private $modeid;  /// primary key for the mode
	private $database;
	
	public function __construct($mode=null,$modeid=null){
		$this->mode=$mode;
		$this->modeid=$modeid;
	}
	
	public function render($content){
		
		
		

		$element = $this->getElement();
		$messages = $element->getMessages();
		$name    = htmlentities($element->getFullyQualifiedName());
		$label   = htmlentities($element->getLabel());
		$id      = htmlentities($element->getId());
		$value   = htmlentities($element->getValue());
		
		$error   = $element->getView()->formErrors($messages);
		
		if($name=='existtask'){
		$format="<fieldset>
				 <legend>Task:</legend>
				 <table>
				 <tr>
				 	<td colspan='All'>
				 		<table>
				 			<tr>
				 				<td>%s</td>
				 				<td>
				 					<select name='%s' id='%s'>";
		
		if($this->mode == 'project'){
			$sql="SELECT t.task_id,t.task_name
				  FROM tasks t
				  LEFT OUTER JOIN (
				  				   SELECT * 
			 		 			   FROM project_task_mapping  
			 					   WHERE project_id=".$this->modeid."
			 					   )pm 
				  ON t.task_id = pm.task_id
				  LEFT OUTER JOIN items ON t.task_status=items.item_id
				  WHERE pm.task_id IS NULL 
				  AND items.item_name != 'Close'
				  AND items.item_type = 'task_status'
				 ";
		}else{
			//for others
		}
	
		$items = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			$format.="<option value='0'>---Select One--</option>";
		foreach($items as $item){
			$format.="<option value='".$item['task_id']."'>".$item['task_name']."</option>";
		}

		
		
		
		$format.= 				   "</select>
				 				</td>
				 			</tr>
				 		</table>
				 	</td>
				 </tr> 
				";	
		return sprintf($format,$label,$name,$name);
		}else if($name=='taskname')
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

		$taskstatuss = Zend_Registry::get('database')->get_items()->fetchAll(array("item_type='task_status'","item_active=1","item_value='Open'" ));
			
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
		
		$format.= 	   "</select>
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
					$format.="<option value='".$queue['queue_id']."'>".$queue['queue_name']."</option>";
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
					$format.="<option value='".$queue['queue_id']."'>".$queue['queue_name']."</option>";
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
		
		$users = Zend_Registry::get('database')->get_users()->fetchAll();
					$format.="<option value='0'>---Select One---</option>";
		/*			
		foreach($users as $user){
					$format.="<option value='".$user['uid']."'>".$user['firstname']."</option>";
		}
		*/
			$format.="	 </select>
				 </td>";
				 
		return sprintf($format,$label,$name,$name);
		}else if($name=='priority'){
			
					
			$format="<td>%s</td>
					<td>
						<select name='%s' id='%s'>";
			
				$task_statuses=Zend_Registry::get('database')->get_items()->fetchAll(array("item_type='task_priority'","item_active=1"));
				foreach($task_statuses as $status){
					$format.="<option value='".$status['item_id']."'>".$status['item_value']."</option>";
				}
			
			
			$format.="	</select>
					</td>
				</tr>";
		return sprintf($format,$label,$name,$name);
		}else if($name=='note'){
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
			
		}else if($name=='add_task'){
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
		}
		return sprintf($format,$name,$value);
	}
}
?>