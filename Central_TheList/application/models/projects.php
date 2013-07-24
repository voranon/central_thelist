<?php

//require_once APPLICATION_PATH.'/models/buildings.php';
//require APPLICATION_PATH.'/models/contacts.php';
//require APPLICATION_PATH.'/models/tasks.php';
require APPLICATION_PATH.'/models/project_entities.php';
class thelist_model_projects
{
	private $database;
	
	
	private $project_id;
	private $project_name;
	private $note1;
	private $note2;
	private $createdate;
	private $contacts;
	private $tasks;
	private $buildings;
	private $project_entities;
	private $user_session;
	private $log;
	
	public function __construct($project_id){
		
		$this->project_id=$project_id;
		$this->log		= Zend_Registry::get('logs');

		$this->user_session 	= new Zend_Session_Namespace('userinfo');
		$project=Zend_Registry::get('database')->get_projects()->fetchRow('project_id='.$this->project_id);
		$this->name			=$project['project_name'];
		$this->note1		=$project['note1'];
		$this->note2		=$project['note2'];
		$this->createdate	=$project['createdate'];
		
		$buildings=Zend_Registry::get('database')->get_buildings()->fetchAll("project_id=".$project_id);
		
		foreach($buildings as $building){
			$this->buildings[$building['building_id']] = new Thelist_Model_buildings($building['building_id']);	
				
		}
		
		$contacts=Zend_Registry::get('database')->get_project_contact_mapping()->fetchAll("project_id=".$project_id);
		foreach($contacts as $contact){
			$this->contacts[$contact['contact_id']] = new contacts($contact['contact_id'],$contact['contact_title']);
			
		}
		
		$tasks=Zend_Registry::get('database')->get_project_task_mapping()->fetchAll("project_id=".$project_id);
		foreach($tasks as $task){
			$this->tasks[$task['task_id']] = new tasks($task['task_id']);
		}
		
		$project_entities=Zend_Registry::get('database')->get_project_entity_mapping()->fetchAll("project_id=".$project_id);
		
		foreach($project_entities as $project_entity){
			$this->project_entities[$project_entity['project_entity_id']] = new Thelist_Model_project_entities($project_entity['project_entity_id']);
		}
		
		
	}
	
	public function get_name(){
		return $this->name;
	}
	public function get_note1(){
		return $this->note1;
	}
	public function get_note2(){
		return $this->note2;
	}
	
	public function get_buildings(){
		return $this->buildings;
	}
	
	public function set_name($project_name){
		
		$trace = debug_backtrace();
		$method = $trace[0]["function"];
		Zend_Registry::get('database')->set_single_attribute($this->project_id, 'projects', 'project_name', $project_name,get_class($this),$method);
		
		/*  /2/29/2012
		$old=Zend_Registry::get('database')->get_projects()->fetchRow('project_id='.$this->project_id);
		
		$data = array(
									'project_name' => $project_name
		);
		Zend_Registry::get('database')->get_projects()->update($data,'project_id='.$this->project_id);
		
		$new=Zend_Registry::get('database')->get_projects()->fetchRow('project_id='.$this->project_id);
		
		
		
		$trace = debug_backtrace();
		$method = $trace[0]["function"];
		
		$this->log->user_log('change project name',
							 get_class($this),
							 $method,
							 'project_id',
							 $this->project_id,
							 $old,
							 $new,
							 'change project name from '.$this->name.' to '.$project_name,
							 ''
							);
		*/
		$this->name=$project_name;
	}
	
	public function set_note1($note1){
		$trace = debug_backtrace();
		$method = $trace[0]["function"];
		Zend_Registry::get('database')->set_single_attribute($this->project_id, 'projects', 'note1', $note1,get_class($this),$method);
		/*
		$old=Zend_Registry::get('database')->get_projects()->fetchRow('project_id='.$this->project_id);
		
		$data = array(
						'note1' => $note1
					 );
		Zend_Registry::get('database')->get_projects()->update($data,'project_id='.$this->project_id);
		
		$new=Zend_Registry::get('database')->get_projects()->fetchRow('project_id='.$this->project_id);
		
		$trace = debug_backtrace();
		$method = $trace[0]["function"];
		
		$this->log->user_log('change project note1',
							 get_class($this),
							 $method,
							 'project_id',
							 $this->project_id,
							 $old,
							 $new,
							 'change project note1 from '.$this->note1.' to '.$note1,
							 ''
							);
		*/
		$this->note1=$note1;
	}
	
	public function set_note2($note2){
		$trace = debug_backtrace();
		$method = $trace[0]["function"];
		Zend_Registry::get('database')->set_single_attribute($this->project_id, 'projects','note2', $note2,get_class($this),$method);
	
		/*
		$old=Zend_Registry::get('database')->get_projects()->fetchRow('project_id='.$this->project_id);
		
		$data = array(
					'note2' => $note2
					 );
		Zend_Registry::get('database')->get_projects()->update($data,'project_id='.$this->project_id);
		
		$new=Zend_Registry::get('database')->get_projects()->fetchRow('project_id='.$this->project_id);
		
		$trace = debug_backtrace();
		$method = $trace[0]["function"];
		
		$this->log->user_log('change project note2',
							get_class($this),
							$method,
							'project_id',
							$this->project_id,
							$old,
							$new,
							'change project note2 from '.$this->note2.' to '.$note2,
							''
							);
		*/
		$this->note2=$note2;
	}
	
	public function add_building($building_name,$building_alias=null,$building_type){
		
				
		$insert	= array(
											'building_type' =>  '',
											'building_name'	=>  $building_name,
											'building_alias'=>  $building_alias,
											'project_id'    =>  $this->project_id
		);
			
		$building_id = Zend_Registry::get('database')->get_buildings()->insert($insert);
		$old=Zend_Registry::get('database')->get_buildings()->fetchRow('building_id='.$building_id);
		
		
		$trace = debug_backtrace();
		$method = $trace[0]["function"];
		
		$this->log->user_log('add building to project',
					   		  get_class($this),
						 	  $method,
							  'building_id',
							  $building_id,
							  $old,
							  $old,
							  'add building '.$building_id.' to project '.$this->project_id,
							  ''
							);
		$this->buildings[$building_id] = new Thelist_Model_buildings($building_id);
		
	}
	
	
	public function delete_building($building_id){
		$this->buildings[$building_id]->set_project_id(0);
		unset($this->buildings[$building_id]);
	}
	
	
	
	
	public function get_contacts(){
		return $this->contacts;
	}
	
	
	
	public function add_contact($contact_id,$title){
		
		$where= array('project_id=?'=>$this->project_id,
							  'contact_id=?'=>$contact_id
		);
					
		Zend_Registry::get('database')->get_project_contact_mapping()->delete($where);
		
		
		$data = array(
						'project_id' 	=> $this->project_id,
						'contact_id' 	=> $contact_id,
						'contact_title' => $title,
						'creator'		=> $this->user_session->uid
		);
		$title_name= Zend_Registry::get('database')->get_thelist_adapter()->fetchOne('select item_value from items where item_id ='.$title);
		
		$project_contact_id = Zend_Registry::get('database')->get_project_contact_mapping()->insert($data);
		
		$old =  Zend_Registry::get('database')->get_project_contact_mapping()->fetchRow('project_contact_id='.$project_contact_id);
		
		$this->contacts[$contact_id]=new contacts($contact_id,$title_name);
		
		$trace = debug_backtrace();
		$method = $trace[0]["function"];
		
		$this->log->user_log('add contact to project',
							 get_class($this),
							 $method,
							 'project_contact_id',
							 $project_contact_id,
							 $old,
							 $old,
							 'add contact '.$contact_id.' to '.'project '.$this->project_id,
							 ''
							 );
	}
	public function delete_contact($contact_id){
		
		
		
		$where= array(
						'project_id=?'=>$this->project_id,
						'contact_id=?'=>$contact_id
		);
		
		$project_contact = Zend_Registry::get('database')->get_project_contact_mapping()->fetchRow($where);
		$old=$project_contact;
		$project_contact_id =$project_contact['project_contact_id'];
		
		Zend_Registry::get('database')->get_project_contact_mapping()->delete($where);
		unset($this->contacts[$contact_id]);
		
		$trace = debug_backtrace();
		$method = $trace[0]["function"];
			
		$this->log->user_log('delete contact from project',
							 get_class($this),
							 $method,
							 'project_contact_id',
							 $project_contact_id,
							 $old,
							 $old,
							 'delete contact '.$contact_id.' from '.'project '.$this->project_id,
							 ''
							);
	}
	
	public function get_project_entities(){
		return $this->project_entities;
	}
	
	public function add_project_entity($project_entity_id){
		
		$where= array('project_id=?'=>$this->project_id,
					  'project_entity_id=?'=>$project_entity_id
					 );
		Zend_Registry::get('database')->get_project_entity_mapping()->delete($where);
		
		
		$insert= array(
					'project_entity_id'	=>   $project_entity_id,
					'project_id'		=>   $this->project_id
		);
		$project_entity_entity_id = Zend_Registry::get('database')->get_project_entity_mapping()->insert($insert);
		
		$old = Zend_Registry::get('database')->get_project_entity_mapping()->fetchRow('project_entity_entity_id='.$project_entity_entity_id);
		
		$this->project_entities[$project_entity_id] = new Thelist_Model_project_entities($project_entity_id);
		
		$trace = debug_backtrace();
		$method = $trace[0]["function"];
		
		$this->log->user_log('add entity to project',
							  get_class($this),
							  $method,
							  'project_entity_entity_id',
							  $project_entity_entity_id,
							  $old,
							  $old,
							  'add entity '.$project_entity_id.' to '.'project '.$this->project_id,
							  ''
							);
	}
	
	public function delete_project_entity($project_entity_id){
		$where= array(
					'project_id=?'=>$this->project_id,
					'project_entity_id=?'=>$project_entity_id
					 );
		
		$project_entity = Zend_Registry::get('database')->get_project_entity_mapping()->fetchRow($where);
		$project_entity_entity_id =$project_entity['project_entity_entity_id'];
		
		$old = Zend_Registry::get('database')->get_project_entity_mapping()->fetchRow('project_entity_entity_id='.$project_entity_entity_id);
		
		Zend_Registry::get('database')->get_project_entity_mapping()->delete($where);
		unset($this->project_entities[$project_entity_id]);
		
		$trace = debug_backtrace();
		$method = $trace[0]["function"];
		
		$this->log->user_log('delete entity from project',
							  get_class($this),
							  $method,
							  'project_entity_entity_id',
							  $project_entity_entity_id,
							  $old,
							  $old,
							  'delete entity '.$project_entity_entity_id.' from '.'project '.$this->project_id,
							  ''
							);
	}
	
	public function get_tasks(){
		return $this->tasks;
	}
	public function add_task($task_id){
		
		$data = array(
						'project_id' => $this->project_id,
						'task_id' => $task_id
		);
		$project_task_id = Zend_Registry::get('database')->get_project_task_mapping()->insert($data);
		
		$old = Zend_Registry::get('database')->get_project_task_mapping()->fetchRow('project_task_id='.$project_task_id);
		
		$this->tasks[$task_id]=new tasks($task_id);
		
		$trace = debug_backtrace();
		$method = $trace[0]["function"];
		
		$this->log->user_log('add task to project',
							  get_class($this),
							  $method,
							  'project_task_id',
							  $project_task_id,
							  $old,
							  $old,
							  'add task '.$task_id.' to '.'project '.$this->project_id,
							  ''
							);
	}
	public function delete_task($task_id){
		
		$where = array(
						'task_id=?' => $task_id,
						'project_id=?' => $this->project_id		
					  );
		
		$project_task = Zend_Registry::get('database')->get_project_task_mapping()->fetchRow($where);
		
		$project_task_id = $project_task['project_task_id'];
		
		$old = Zend_Registry::get('database')->get_project_task_mapping()->fetchRow('project_task_id='.$project_task_id);
		
		Zend_Registry::get('database')->get_project_task_mapping()->delete($where);
		unset($this->tasks[$task_id]);
		
		$trace = debug_backtrace();
		$method = $trace[0]["function"];
		
		$this->log->user_log('delete task from project',
							  get_class($this),
							  $method,
							  'project_task_id',
							  $project_task_id,
							  $old,
							  $old,
							  'delete task '.$task_id.' from '.'project '.$this->project_id,
							  ''
							);
	}
	
	
	
	
	public function set_contact_title($contact_id,$title_id){
		$data = array(
					'contact_title' => $title_id
				);
		
		$where= array(
					'project_id=?'  => $this->project_id,
					'contact_id=?'  => $contact_id
				);
		
		$old = Zend_Registry::get('database')->get_project_contact_mapping()->fetchRow($where);
		
		Zend_Registry::get('database')->get_project_contact_mapping()->update($data,$where);
		$this->contacts[$contact_id]->set_title_id($title_id);
		
		$new = Zend_Registry::get('database')->get_project_contact_mapping()->fetchRow($where);
		$project_contact_id = $old['project_contact_id'];
		
		$trace = debug_backtrace();
		$method = $trace[0]["function"];
		
		$this->log->user_log('change contact title for project',
							 get_class($this),
							 $method,
							 'project_contact_id',
							 $project_contact_id,
							 $old,
							 $new,
							 'change contact title to '.$title_id,
							 ''
							);
	}

}
?>