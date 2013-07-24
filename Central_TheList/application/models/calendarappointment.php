<?php
class thelist_model_calendarappointment{
	
	private $_calendar_appointment_id;
	private $_scheduled_start_time;
	private $_scheduled_end_time;
	private $_actual_start_time;
	private $_actual_end_time;
	private $_calendar_appointment_status;
	private $_scheduled_time;
	private $_mapped_calendar_appointment_task_map_id=null;
	
	 
	
	
	private $building_id;
	private $unit_id;
	private $end_user_service_id;
	private $tasks;
	private $service_plan_groups;
	
	 
	
	public function __construct($calendar_appointment_id){
		
		$this->_calendar_appointment_id		= $calendar_appointment_id;

		$this->log							= Zend_Registry::get('logs');
		$this->user_session 				= new Zend_Session_Namespace('userinfo');
		
		
		$calendar_appointment 				= Zend_Registry::get('database')->get_calendar_appointments()->fetchRow("calendar_appointment_id=".$this->_calendar_appointment_id);
		
		$this->_scheduled_start_time				= $calendar_appointment['scheduled_start_time'];
		$this->_scheduled_end_time					= $calendar_appointment['scheduled_end_time'];
		$this->_actual_start_time					= $calendar_appointment['actual_start_time'];
		$this->_actual_end_time						= $calendar_appointment['actual_end_time'];
		$this->_calendar_appointment_status			= $calendar_appointment['calendar_appointment_status'];
		$this->_scheduled_time						= $calendar_appointment['scheduled_time'];		

		$sql="SELECT b.building_id,u.unit_id
			  FROM calendar_appointments ca
			  LEFT OUTER JOIN calendar_appointment_task_mapping catm ON ca.calendar_appointment_id = catm.calendar_appointment_id
			  LEFT OUTER JOIN tasks t ON catm.task_id = t.task_id
			  LEFT OUTER JOIN user_unit_group_mapping uugm ON t.task_owner = uugm.user_id
			  LEFT OUTER JOIN unit_group_mapping ugm ON uugm.unit_group_id = ugm.unit_group_id
			  LEFT OUTER JOIN units u ON ugm.unit_id = u.unit_id
			  LEFT OUTER JOIN buildings b ON u.building_id = b.building_id
			  WHERE ca.calendar_appointment_id = ".$this->_calendar_appointment_id."
			  GROUP BY t.task_owner
			 ";
		
		$result = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		$this->building_id 	= $result['building_id'];
		$this->unit_id		= $result['unit_id'];	

		$sql="SELECT catm.task_id,	spg.service_plan_group_name
			  FROM calendar_appointment_task_mapping catm
			  LEFT OUTER JOIN service_plan_quote_task_mapping spqtm ON catm.task_id = spqtm.task_id
			  LEFT OUTER JOIN service_plan_quote_mapping spqm ON spqtm.service_plan_quote_map_id = spqm.service_plan_quote_map_id
			  LEFT OUTER JOIN service_plan_group_mapping spgm ON spqm.service_plan_id = spgm.service_plan_id
			  LEFT OUTER JOIN service_plan_groups spg ON spgm.service_plan_group_id = spg.service_plan_group_id
			  WHERE calendar_appointment_id=".$this->_calendar_appointment_id."
			  GROUP BY spg.service_plan_group_name";
		
		$tasks = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		
		foreach($tasks as $task)
		{
			
				
				//$this->tasks[ $task['task_id'] ] = new Thelist_Model_tasks( 13 );
				$this->service_plan_groups[] 		 = $task['service_plan_group_name'];
			
		}
		
		
		
		
		
		$sql="	SELECT task_id
				FROM calendar_appointment_task_mapping catm
				WHERE catm.calendar_appointment_id = ".$this->_calendar_appointment_id."
				LIMIT 1";
		
		$task_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		
		$sql="	SELECT end_user_service_id
				FROM end_user_task_mapping
				WHERE task_id=".$task_id;
		
		$this->end_user_service_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		
		
		
		
	}
	
	public function get_calendar_appointment_id()
	{
		return $this->_calendar_appointment_id;
	}
	public function get_task_id(){
		return $this->_task_id;
	}
	public function get_scheduled_start_time(){
		return $this->_scheduled_start_time;
	}
	public function get_scheduled_end_time(){
		return $this->_scheduled_end_time;
	}
	public function get_actual_start_time(){
		return $this->_actual_start_time;
	}
	public function get_actual_end_time(){
		return $this->_actual_end_time;
	}
	public function get_calendar_appointment_status(){
		return $this->_calendar_appointment_status;
	}
	public function get_scheduled_time(){
		return $this->_scheduled_time;
	}
	
	public function get_resolved_appointment_status()
	{
		$item =Zend_Registry::get('database')->get_items()->fetchRow("item_id=".$this->_calendar_appointment_status);
		return $item['item_value'];
	}

	public function set_mapped_calendar_appointment_task_map_id($calendar_appointment_task_map_id)
	{
		return $this->_mapped_calendar_appointment_task_map_id;
	}
	
	
	public function schedule_task($task_id){
		
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		
		$data = array(
						'task_id'					=>   $task_id,
						'calendar_appointment_id'	=> 	 $this->_calendar_appointment_id
					 );
		
		return Zend_Registry::get('database')->insert_single_row('calendar_appointment_task_mapping',$data,get_class($this),$method);
						
	}
	
	
	public function get_building_id(){
		return $this->building_id;
	}
	
	public function get_unit_id(){
		return $this->unit_id;
	}
	
	public function get_enduser_id(){
		return $this->end_user_service_id;
	}
	
	public function get_tasks(){
		return $this->tasks;
	}
	
	public function get_service_plan_groups(){
		return $this->service_plan_groups;
	}
}
?>