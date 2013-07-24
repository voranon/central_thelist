<?php

//exception codes 18500-18599

class thelist_model_tasks
{
	private $task_id;
	private $name;
	private $notes=null;
	private $task_queue_id;
	private $task_queueowner_id;
	private $task_owner;
	private $task_priority;
	private $task_status;
	private $task_subtype;
	private $creator;
	private $childrens_task;
	private $user_session;
	private $log;
	private $master_task_id;
	private $_calendar_appointments=null;
	private $_mapped_service_plan_quote_task_map_id=null;
	private $_mapped_service_plan_quote_map_id=null;
	private $_mapped_service_plan_quote_task_progress=null;
	private $_mapped_service_plan_quote_task_progress_resolved=null;
	private $_interface_task_map_id=null;
	
	
	
	public function __construct($task_id)
	{
		$this->task_id		=	$task_id;

		$this->log			=   Zend_Registry::get('logs');
		$this->user_session 	= new Zend_Session_Namespace('userinfo');
		
		$task					=Zend_Registry::get('database')->get_tasks()->fetchRow("task_id=".$this->task_id);
		$this->name				=$task['task_name'];
		$this->task_queue_id	=$task['task_queue_id'];
		$this->task_status  	=$task['task_status'];
		$this->task_subtype		=$task['task_subtype'];
		$this->task_priority	=$task['task_priority'];
		$this->master_task_id	=$task['master_task_id'];
		   
		
		$this->user_session 	= new Zend_Session_Namespace('userinfo');
		
		
		$children=Zend_Registry::get('database')->get_tasks()->fetchRow("master_task_id=".$this->task_id);
		if(is_array($children)){
			
			foreach($children as $key => $child){
				$this->childrens_task[$child['task_id']]	= new Thelist_Model_tasks($child['task_id']);
			}
		}
		
		$notes=Zend_Registry::get('database')->get_task_note_mapping()->fetchAll("task_id=".$this->task_id);

		foreach($notes as $note){
			$this->notes[$note['note_id']] = new Thelist_Model_notes($note['note_id']);
		}
		
		//calendar appointments
		$appointments=Zend_Registry::get('database')->get_calendar_appointment_task_mapping()->fetchAll("task_id=".$this->task_id);
		
		if(isset($appointments['0'])) {
			
			foreach($appointments as $appointment){
				
				$this->_calendar_appointments[$appointment['calendar_appointment_id']]	= new Thelist_Model_calendarappointment($appointment['calendar_appointment_id']);
				$this->_calendar_appointments[$appointment['calendar_appointment_id']]->set_mapped_calendar_appointment_task_map_id($appointment['calendar_appointment_task_map_id']);
			}
		}
		
	}
	
	
	public function get_task_id(){
		return $this->task_id;
	}
	
	public function get_name(){
		return $this->name;
	}
	
	public function get_task_queue_id(){
		return $this->task_queue_id;
	}
	
	public function get_task_queue_resolved()
	{

		//a task must have a queue so no need for a check		
		$sql =	"SELECT queue_name FROM queues
				WHERE queue_id='".$this->task_queue_id."'
				";
			
		$queue_name = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
		return $queue_name;
				
	}
	
	public function get_task_queueowner_id()
	{
		return $this->task_queueowner_id;
	}
	
	public function get_task_queueowner_resolved()
	{
		if ($this->task_queueowner_id != null) {
			
			$sql =	"SELECT queue_name FROM queues
					WHERE queue_id='".$this->task_queueowner_id."'
					";
			
			$queue_name = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
			return $queue_name;
			
		} else {
			return false;
		}
	}
	
	public function get_task_owner(){
		return $this->task_owner;
	}
	public function get_master_task_id(){
		return $this->master_task_id;
	}
	
	public function get_status()
	{
		return $this->task_status;
	}
	
	public function get_task_priority(){
		return $this->task_priority;
	}
	
	public function get_task_subtype(){
		return $this->task_subtype;
	}
	
	public function get_children(){
		return $this->childrens_task;
	}
	
	public function get_notes(){
		return $this->notes;
	}
	
	public function get_calendar_appointments(){
		return $this->_calendar_appointments;
	}
	
	public function get_calendar_appointment($calendar_appointment_id){
		return $this->_calendar_appointments[$calendar_appointment_id];
	}
	
	public function get_resolved_task_status()
	{
		$item =Zend_Registry::get('database')->get_items()->fetchRow("item_id=".$this->task_status);
		return $item['item_value'];
	}
	public function fill_mapped_service_plan_quote_task_map()
	{
		if ($this->_mapped_service_plan_quote_task_map_id == null) {
			
			//each service plan map can only have a single task
			$sql = 	"SELECT * FROM service_plan_quote_task_mapping spqtm
							INNER JOIN items itm ON itm.item_id=spqtm.service_plan_quote_task_progress
							WHERE spqtm.task_id='".$this->task_id."'
							";
			
			$service_plan_quote_task_details = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
			if (isset($service_plan_quote_task_details['service_plan_quote_map_id'])) {
					
				$this->_mapped_service_plan_quote_task_map_id 					= $service_plan_quote_task_details['service_plan_quote_task_map_id'];
				$this->_mapped_service_plan_quote_map_id						= $service_plan_quote_task_details['service_plan_quote_map_id'];
				$this->_mapped_service_plan_quote_task_progress_resolved		= $service_plan_quote_task_details['item_value'];
				$this->_mapped_service_plan_quote_task_progress					= $service_plan_quote_task_details['service_plan_quote_task_progress'];
					
			} else {
				throw new exception("task id: '".$this->task_id."' is not mapped to any service plan maps", 18503);
			}
		}
	}
	
	public function set_interface_task_map_id($interface_task_map_id)
	{
		$this->_interface_task_map_id = $interface_task_map_id;
	}
	
	public function get_interface_task_map_id()
	{
		return $this->_interface_task_map_id;
	}
	
	public function get_mapped_service_plan_quote_task_map_id()
	{
		$this->fill_mapped_service_plan_quote_task_map();
		//we would never want a task to have more than a single service_plan_quote mapped 
		//because it would not allow for rescheduling of that service_plan_quote and we would 
		//not be able to close the task if only one service_plan_quote was done and the other required rescheduling
		
		return $this->_mapped_service_plan_quote_task_map_id;
	
	}
	
	public function get_mapped_service_plan_quote_map_id()
	{
		$this->fill_mapped_service_plan_quote_task_map();
		return $this->_mapped_service_plan_quote_map_id;
	
	}
	
	public function set_task_install_progress($progress_id)
	{
		$this->fill_mapped_service_plan_quote_task_map();
		
		if ($this->_mapped_service_plan_quote_task_map_id != null) {
		
			$sql = 	"SELECT COUNT(item_id) FROM items 
					WHERE item_id='".$progress_id."'
					AND item_type='service_plan_quote_task_progress'
					";
				
			$is_valid  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
			if ($is_valid == 1) {

				if ($progress_id != $install_progress['service_plan_quote_task_progress']) {
					
					$sql = 	"SELECT item_id FROM items
							WHERE item_name='Open'
							AND item_type='task_status'
							";
						
					$item_id  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
					
					if ($this->task_status == $item_id) {
						
						$trace 		= debug_backtrace();
						$method 	= $trace[0]["function"];
						$class		= get_class($this);
						
						Zend_Registry::get('database')->set_single_attribute($this->_mapped_service_plan_quote_task_map_id, 'service_plan_quote_task_mapping', 'service_plan_quote_task_progress', $progress_id, $class, $method);
						
					} else {
						throw new exception("you cannot set the install progress for task id: '".$this->task_id."' because it is already closed", 18502);
					}
				}
					
			} else {
				throw new exception("progress_id: '".$progress_id."' is not a valid service_plan_quote_task_progress id ", 18500);
			}
		} else {
			throw new exception("you cannot set the install progress for task id: '".$this->task_id."' is not an installation task", 18501);
		}
	}

	public function get_mapped_service_plan_quote_task_progress()
	{
		$this->fill_mapped_service_plan_quote_task_map();
		return $this->_mapped_service_plan_quote_task_progress;
	}
	
	public function get_mapped_service_plan_quote_task_progress_resolved()
	{
		$this->fill_mapped_service_plan_quote_task_map();
		return $this->_mapped_service_plan_quote_task_progress_resolved;
	}
	
	public function add_note($note_content)
	{
		$trace = debug_backtrace();
		$method = $trace[0]["function"];
		
		$data = array(
					'note_content' => htmlentities($note_content),
					'creator'      => $this->user_session->uid,
					 );
		
		$note_id = Zend_Registry::get('database')->get_notes()->insert($data);
		
		$old = Zend_Registry::get('database')->get_notes()->fetchRow('note_id='.$note_id);
		
		$this->log->user_log('create a new note',
							  get_class($this),
							  $method,
							  'note_id',
							  $note_id,
							  $old,
							  $old,
							  'create a new note '.$note_id,
							  ''
							);
		
		//////////////////////////////////
		$data = array(
					'task_id'   => $this->task_id,
					'note_id'   => $note_id,
					'creator'   => $this->user_session->uid,
					);
		
		$task_note_id = Zend_Registry::get('database')->get_task_note_mapping()->insert($data);
		
		$old  = Zend_Registry::get('database')->get_task_note_mapping()->fetchRow('task_note_id='.$task_note_id);
		
		$this->log->user_log('add note to task',
							 get_class($this),
							 $method,
							 'task_note_id',
							 $task_note_id,
							 $old,
							 $old,
							 'add note '.$note_id.' to task '.$this->task_id,
							 ''
							);
		
	
		$this->notes[$note_id] = new Thelist_Model_notes($note_id);
	}
	
	
	public function set_name($name){
		
		
		if($name != $this->name){
			
			$trace = debug_backtrace();
			$method = $trace[0]["function"];
			Zend_Registry::get('database')->set_single_attribute($this->task_id,'tasks','task_name', $name,get_class($this),$method);
			$this->name=$name;
		}
	}
	public function set_priority($priority){
		
		if($priority != $this->task_priority){
			
			$trace = debug_backtrace();
			$method = $trace[0]["function"];
			Zend_Registry::get('database')->set_single_attribute($this->task_id,'tasks','task_priority', $priority,get_class($this),$method);
			
			$this->task_priority=$priority;
		}
	}
	
	public function set_status($status)
	{
		
		if($status != $this->task_status){
			$trace = debug_backtrace();
			$method = $trace[0]["function"];
			Zend_Registry::get('database')->set_single_attribute($this->task_id,'tasks','task_status', $status,get_class($this),$method);
			
			$this->task_status=$status;
		}
	}
	
	public function set_queue_id($queue_id){
		
		if($queue_id != $this->task_queue_id){
			$trace = debug_backtrace();
			$method = $trace[0]["function"];
			Zend_Registry::get('database')->set_single_attribute($this->task_id,'tasks','task_queue_id', $queue_id,get_class($this),$method);
			
		$this->task_queue_id=$queue_id;
		}
	}
	
	
	public function set_queueowner_id($queueowner_id)
	{
		//should not be able to set queueowner_id, if already filled
		//queueowner_id is a permanent relationship created only once letting one queue track the
		//task regardless of the queue / uid that owns the task right then and there
	
		if($this->task_queueowner_id == null){
			
			$trace = debug_backtrace();
			$method = $trace[0]["function"];
			Zend_Registry::get('database')->set_single_attribute($this->task_id,'tasks','task_queueowner_id', $queueowner_id,get_class($this),$method);
			
			
			$this->task_queueowner_id=$queueowner_id;
		}
	}
	public function set_owner($task_owner){
		if($task_owner != $this->task_owner){
			$trace = debug_backtrace();
			$method = $trace[0]["function"];
			Zend_Registry::get('database')->set_single_attribute($this->task_id,'tasks','task_owner', $task_owner,get_class($this),$method);
			
		
			$this->task_owner=$task_owner;
		}
	}
	
	
}
?>