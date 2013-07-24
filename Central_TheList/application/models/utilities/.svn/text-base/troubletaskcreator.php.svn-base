<?php

//exception codes 7300-7399

class thelist_utility_troubletaskcreator
{
	private $_task_queue_id;
	private $_task_name;
	private $_task_priority;
		
	function __construct($task_queue_name, $task_name, $task_priority)
	{
		//this class abstracts the task priority from the itmes table
		//only supply high, medium, low and not an item_id. this way we will not have alot of
		//sql in the code when interacting with this class.
		if ($task_priority == 'High' || $task_priority == 'Medium' || $task_priority == 'Low') {
			
			$sql = 	"SELECT item_id FROM items
					WHERE item_type='task_priority'
					AND item_name='".$task_priority."'
					";
			
			$item_id	= Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
			
			$sql2 = 	"SELECT queue_id FROM queues
						WHERE queue_name='".$task_queue_name."'
						";
						
			$queue_id	= Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);

			if (isset($item_id['item_id']) && isset($queue_id['queue_id'])) {
				
				$this->_task_queue_id		= $queue_id;
				$this->_task_name			= $task_name;
				$this->_task_priority		= $item_id;
				
			} else {
				throw new exception("task priority or queue did not return a value from the database table, using name:".$task_queue_name." and priority: ".$task_priority."", 7300);
			}

		} else {
			throw new exception('task priority can only be High, Medium or Low', 7301);
		}
   	}
   	
   	public function create_task($creator_uid=0)
   	{
   		
   		$sql = 	"SELECT item_id FROM items
				WHERE item_type='task_status'
				AND item_name='Open'
				";
   			
   		$open_status_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
   		
   		$data = array(
	   				'task_name' 		=> 	$this->_task_name,
	   				'task_status'		=>  $open_status_id,
	   				'task_queue_id' 	=>  $this->_task_queue_id,
	   				'creator'			=>	$creator_uid,
					'task_priority' 	=>  $this->_task_priority
   				);
   		
   		$trace 		= debug_backtrace();
   		$method 	= $trace[0]["function"];
   		$class		= get_class($this);
   		
   		$new_task_id = Zend_Registry::get('database')->insert_single_row('tasks', $data, $class, $method);

   		//return the new task
   		return new Thelist_Model_tasks($new_task_id);
   		
   	}

}
?>