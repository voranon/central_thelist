<?php 

//exception codes 2400-2499

class thelist_model_connectionqueue
{
	private $_connection_queue_id;
	private $_if_id;
	private $_connection_queue_max_rate;
	private $_connection_queue_sla_rate;	
	private $_connection_queue_name;
	private $_connection_queue_filters=null;
	private $_child_relationships=null;
	private $_parent_relationships=null;
	
	public function __construct($connection_queue_id)
	{
		$this->_connection_queue_id = $connection_queue_id;

		$sql = 	"SELECT * FROM connection_queues
				WHERE connection_queue_id='".$this->_connection_queue_id."'
				";
		
		$connection_queue_detail  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

		$this->_if_id								= $connection_queue_detail['if_id'];
		$this->_connection_queue_max_rate			= $connection_queue_detail['connection_queue_max_rate'];
		$this->_connection_queue_sla_rate			= $connection_queue_detail['connection_queue_sla_rate'];
		$this->_connection_queue_name				= $connection_queue_detail['connection_queue_name'];

	}
	
	public function get_connection_queue_filters()
	{
		if ($this->_connection_queue_filters == null) {
			
			$sql = 	"SELECT connection_queue_filter_id FROM connection_queue_filters
					WHERE connection_queue_id='".$this->_connection_queue_id."'
					";
		
			$connection_filters  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			if (isset($connection_filters['0'])) {
				
				foreach($connection_filters as $connection_filter) {
					$this->_connection_queue_filters[$connection_filter['connection_queue_filter_id']] = new Thelist_Model_connectionqueuefilter($connection_filter['connection_queue_filter_id']);
				}
			}
		}
		
		return $this->_connection_queue_filters;
	}
	
	public function create_connection_queue_filter($connection_queue_filter_priority=null, $connection_queue_filter_name=null)
	{
		//all variables MUST be optional, we need to be able to create filters for any kind of traffic
		
		//most filter names must be unique, either for interface or just for the queue, but requirements differ
		//from software to software, so no validations are done here.
		
		//priorities can overlap, however if we dont specify a priority we assume it will be lower than the existing 
		//filters, priority 1 is highest priority, max will depend on software implementation on device
		
		if ($connection_queue_filter_priority == null) {
				
			$sql = "SELECT MAX(connection_queue_filter_priority) AS max_priority FROM connection_queue_filters
					WHERE connection_queue_id='".$this->_connection_queue_id."'
					";
				
			$current_max_priority  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
				
			if (isset($current_max_priority['max_priority'])) {
		
				$connection_queue_filter_priority = $current_max_priority + 1;
		
			} else {
				$connection_queue_filter_priority = 1;
			}
		} else {
			
			if ($connection_queue_filter_priority == 0) {
				throw new exception("filter priority cannot be 0", 2401);
			} elseif (!is_numeric($connection_queue_filter_priority)) {
				throw new exception("filter priority must be numeric", 2400);
			}
		}
	
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		$class	= get_class($this);
			
		$data = array(
						'connection_queue_id'									=>  $this->_connection_queue_id,
						'connection_queue_filter_name'							=>  $connection_queue_filter_name,
						'connection_queue_filter_priority'						=>  $connection_queue_filter_priority,

		);
			
		$new_filter_id = Zend_Registry::get('database')->insert_single_row('connection_queue_filters',$data,$class,$method);

		if ($this->_connection_queue_filters == null) {
			$this->get_connection_queue_filters();
		}
		
		$this->_connection_queue_filters[$new_filter_id] = new thelist_model_connectionqueuefilter($new_filter_id);
		
		return $this->_connection_queue_filters[$new_filter_id];
	}
	
	public function get_connection_queue_id()
	{
		return $this->_connection_queue_id;		
	}
	
	public function get_if_id()
	{
		return $this->_if_id;
	}
	
	public function get_connection_queue_max_rate()
	{
		return $this->_connection_queue_max_rate;
	}
	
	public function get_connection_queue_sla_rate()
	{
		return $this->_connection_queue_sla_rate;
	}
	
	public function get_connection_queue_name()
	{
		return $this->_connection_queue_name;
	}
	
	
	public function get_child_relationships()
	{
	
		if ($this->_child_relationships == null) {
	
			$sql =	"SELECT * FROM connection_queue_relationships
					WHERE connection_queue_id_parent='".$this->_connection_queue_id."'
					";
	
			$relationships  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
	
			//get all queues that this queue is the master
			if (isset($relationships['0'])) {
	
				foreach ($relationships as $relationship) {
	
					$child_queue	= new Thelist_Model_connectionqueue($relationship['connection_queue_id_child']);
					$this->_child_relationships[]	= $child_queue;
				}
			}
		}
	
		return $this->_child_relationships;
	}
	
	public function get_parent_relationships()
	{
	
		if ($this->_parent_relationships == null) {
	
			$sql =	"SELECT * FROM connection_queue_relationships
					WHERE connection_queue_id_child='".$this->_connection_queue_id."'
					";
	
			$relationships  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
	
			//get all queues that this queue is a child off
			if (isset($relationships['0'])) {
	
				foreach ($relationships as $relationship) {
						
					$parent_queue	= new Thelist_Model_connectionqueue($relationship['connection_queue_id_parent']);
					$this->_parent_relationships[]	= $parent_queue;
				}
			}
		}
	
		return $this->_parent_relationships;
	}
	
	
	
	
}
?>