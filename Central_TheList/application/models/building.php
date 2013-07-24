<?php

//exception codes 22700-22799

class thelist_model_building
{
	private $numberofunit=0;

	private $project_id=null;
	private $project_name=null;
	private $contacts=null;
	private $tasks=null;
	//private $contacts;
	
	//martin checked
	private $_building_id;
	private $_building_name;
	private $_building_alias;
	private $_project_id;
	private $_units=null;
	
    
	public function __construct($building_id)
	{
		$this->_building_id	= $building_id;	

		$sql =	"SELECT * FROM buildings
				WHERE building_id='".$this->_building_id."'
				";
		
		$building_detail = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

		$this->_building_name			=$building_detail['building_name'];
		$this->_building_alias			=$building_detail['building_alias'];
		$this->_project_id				=$building_detail['project_id'];

// 		$contacts = Zend_Registry::get('database')->get_building_contact_mapping()->fetchAll("building_id=".$this->_building_id);
// 		foreach($contacts as $contact){
// 			$this->contacts[$contact['contact_id']] = new Thelist_Model_contacts($contact['contact_id'],$contact['contact_title']);
			
// 		}
		
// 		$tasks = Zend_Registry::get('database')->get_building_task_mapping()->fetchAll("building_id=".$this->_building_id);
// 		foreach($tasks as $task){
// 			$this->tasks[$task['task_id']] = new Thelist_Model_tasks($task['task_id']);
// 		}
		
		
// 		$untis = Zend_Registry::get('database')->get_units()->fetchAll("building_id=".$this->_building_id);
// 		foreach($units as $unit){
// 			$this->units[$unit['unit_id']] = new Thelist_Model_units($unit['unit_id']);
// 		}
	}
	
	public function create_unit($unit_name, $unit_street_number=null, $unit_street_name=null, $unit_street_type=null, $unit_city=null, $unit_state=null, $unit_zip=null)
	{
		if ($unit_name != '') {
		
			$units = $this->get_units();

			if ($units != null) {
				
				foreach ($units as $unit) {
					
					if ($unit->get_unit_name() == $unit_name) {
						//dont return the unit with the same name, if a unit is being added it will be used, and that would be a mistake to get a unit
						//that one thinks is brand new, just to get an existing unit
						throw new exception("you are trying to add unit_name: '".$unit_name."' to building id: '".$this->_building_id."', but that unit name already exists", 22701);
					}
				}
			}
			
			$time = new Thelist_Utility_time();
			
			//if there is not already a unit with this name we add it
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
				
			$data = array(
			   				'building_id' 			=> 	$this->_building_id,
			   				'unit_name'				=>  $unit_name,
							'unit_street_number' 	=>  $unit_street_number,
							'unit_street_name' 		=> 	$unit_street_name,
						  	'unit_street_type'		=>  $unit_street_type,
							'unit_city' 			=>  $unit_city,
							'unit_state' 			=> 	$unit_state,
						   	'unit_zip'				=>  $unit_zip,
							'unit_create_date' 		=>  $time->get_current_date_time_mysql_format(),
				
			);
			
			$new_unit_id	= Zend_Registry::get('database')->insert_single_row('units', $data, $class, $method);
			
			$this->_units[$new_unit_id] = new Thelist_Model_unit($new_unit_id);
			
			return $this->_units[$new_unit_id];
			
		} else {
			throw new exception("you are trying to add a unit with no name to building id: '".$this->_building_id."', a unit must have a name", 22700);
		}
	}
	
	public function get_units()
	{
		if ($this->_units == null) {
		
			$sql =	"SELECT * FROM units
					WHERE building_id='".$this->_building_id."'
					";
			
			$units = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			if (isset($units['0'])) {
				
				foreach ($units as $unit) {
					$this->_units[$unit['unit_id']] = new Thelist_Model_unit($unit['unit_id']);
				}
			}
		} 
		
		return $this->_units;
	}

	public function get_building_id(){
		return $this->_building_id;
	}
		
	public function get_building_name(){
		return $this->_building_name;
	}
	public function get_building_alias(){
		return $this->_building_alias;
	}
	public function get_project_id(){
		return $this->project_id;
	}
	
	/*
	public function get_project_name(){
		return $this->project_name;
	}
	public function get_contacts(){
		return $this->contacts;
	}
	
	public function get_tasks(){
		return $this->tasks;
	}
	public function add_contact($contact_id,$title){
		
		$where = array(	
						"building_id=?"   => $this->_building_id,
						"contact_id=?"	  => $contact_id
					  );
		
		$contact = Zend_Registry::get('database')->get_building_contact_mapping()->fetchRow($where);
		
		if($contact['building_contact_id']==''){
			
			$insert= array(	
							'building_id'  =>  $this->_building_id,
							'contact_id'   =>  $contact_id,    
							'contact_title'=>  $title
						  );
			$building_contact_id = Zend_Registry::get('database')->get_building_contact_mapping()->insert($insert);
			$building_contact    = Zend_Registry::get('database')->get_building_contact_mapping()->fetchRow('building_contact_id='.$building_contact_id);

			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
			
			$this->log->user_log('add contact to building',
								 get_class($this),
								 $method,
								 'building_contact_id',
								 $building_contact_id,
								 $building_contact,
								 $building_contact,
								 'add contact '.$contact_id.' to building '.$this->_building_id,
								 ''
								);
			
			
		}
			
		
	}
	
	public function delete_contact($contact_id){
		
		$where = array(
								"building_id=?"   => $this->_building_id,
								"contact_id=?"	  => $contact_id
		);
		
		
		$contact = Zend_Registry::get('database')->get_building_contact_mapping()->fetchRow($where);
		$building_contact_id = $contact['building_contact_id'];
		
		Zend_Registry::get('database')->get_building_contact_mapping()->delete($where);
		
		
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
			
		
		
		$this->log->user_log('delete contact from building',
							get_class($this),
							$method,
							'building_contact_id',
							$building_contact_id,
							$contact,
							$contact,
							'delete contact '.$contact_id.' from building '.$this->_building_id,
							''
		);
		
	}
	
	public function add_task($task_id){
		$data = array(
						'building_id' => $this->_building_id,
						'task_id' => $task_id
					 );
		
		$building_task_id = Zend_Registry::get('database')->get_building_task_mapping()->insert($data);
		
		$building_task = Zend_Registry::get('database')->get_building_task_mapping()->fetchRow('building_task_id='.$building_task_id);
		
		$this->tasks[$task_id]=new Thelist_Model_tasks($task_id);
		
		$trace = debug_backtrace();
		$method = $trace[0]["function"];
				
		$this->log->user_log('add task to building',
							 get_class($this),
							 $method,
							 'building_task_id',
							 $building_task_id,
							 $building_task,
							 $building_task,
							 'add task '.$task_id.' to '.'building '.$this->_building_id,
							 ''
		);
							  
		
		
	}
	
		
	
	public function delete_task($task_id){
		$where = array(
							'task_id=?' => $task_id,
							'building_id=?' => $this->_building_id		
		);
	
		$building_task = Zend_Registry::get('database')->get_building_task_mapping()->fetchRow($where);
	
		$building_task_id = $building_task['building_task_id'];
	
		Zend_Registry::get('database')->get_building_task_mapping()->delete($where);
		unset($this->tasks[$task_id]);
	
		$trace = debug_backtrace();
		$method = $trace[0]["function"];
				
		$this->log->user_log('delete task from building',
							get_class($this),
							$method,
							'building_task_id',
							$building_task_id,
							$building_task,
							$building_task,
							'delete task '.$task_id.' from '.'building '.$this->_building_id,
							''
		);
	}
	
	public function set_project_id($project_id){
		if($this->project_id != $project_id){
		 	$trace = debug_backtrace();
			$method = $trace[0]["function"];
			
			Zend_Registry::get('database')->set_single_attribute($this->_building_id,'buildings','project_id', $project_id,get_class($this),$method);
			
			$this->project_id=$project_id;
		}
	}
	
	public function set_contact_title($contact_id,$title_id)
	{
	
		$data = array(
						'contact_title' => $title_id
		);
	
	
		$where= array(
						'building_id=?'  => $this->_building_id,
						'contact_id=?'   => $contact_id
		);
	
		$old = Zend_Registry::get('database')->get_building_contact_mapping()->fetchRow($where);
		
		Zend_Registry::get('database')->get_building_contact_mapping()->update($data,$where);
		$this->contacts[$contact_id]->set_title_id($title_id);
		
		$new = Zend_Registry::get('database')->get_building_contact_mapping()->fetchRow($where);
		
		$building_contact_id = $old['building_contact_id'];
	
		$trace = debug_backtrace();
		$method = $trace[0]["function"];
		
		$this->log->user_log('change contact title for building',
							 get_class($this),
							 $method,
							 'building_contact_id',
							 $building_contact_id,
							 $old,
							 $new,
							 'change contact title to '.$title_id,
							 ''
							);
	}
	public function set_name($building_name)
	{
		
		if($this->_building_name != $building_name){
			
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
			Zend_Registry::get('database')->set_single_attribute($this->_building_id,'buildings','building_name', $building_name,get_class($this),$method);
			$this->_building_name=$building_name;
		}
	}
	
	
	public function set_alias($building_alias){
		
		if($this->alias != $building_alias){
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
			
			Zend_Registry::get('database')->set_single_attribute($this->_building_id,'buildings','building_alias', $building_alias,get_class($this),$method);
			
			$this->alias=$building_alias;
					
		}
	}
	
	public function set_note1($note1){
		if($this->note1 != $note1){
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
			
			Zend_Registry::get('database')->set_single_attribute($this->_building_id,'buildings','note1', $note1,get_class($this),$method);
			
			$this->note1=$note1;
		}
	}
	
	public function set_note2($note2){
		if($this->note2 != $note2){
			
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
			
			Zend_Registry::get('database')->set_single_attribute($this->_building_id,'buildings','note2', $note2,get_class($this),$method);
			
			$this->note2=$note2;
		}
	}
	
	public function set_note3($note3){
		if($this->note3 != $note3){
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
			
			Zend_Registry::get('database')->set_single_attribute($this->_building_id,'buildings','note3', $note3,get_class($this),$method);
			
			$this->note3=$note3;
		}
	}
	
	
	public function add_unit($unit_id){
		
		
		$old= Zend_Registry::get('database')->get_units()->fetchRow('unit_id='.$unit_id);
		
		$data = array(
						'building_id'	=> $this->_building_id
					 );
		Zend_Registry::get('database')->get_units()->update($data,'unit_id='.$unit_id);
		
		$new= Zend_Registry::get('database')->get_units()->fetchRow('unit_id='.$unit_id);
		
		$this->log->user_log('add unit',
							 get_class($this),
							 $method,
							 'unit_id',
							 $unit_id,
							 $old,
							 $new,
							 'add unit '.$unit_id.' to building '.$this->_building_id,
							 ''
							);
		
		$this->units[$unit_id]=new Thelist_Model_units($unit_id);
	}
	
	public function get_unit($unit_id){
		return $this->units[$unit_id];
	}
	
	public function get_units(){
		return $this->units;
	}
	*/
}
?>