<?php 

//exception codes 1900-1999

class thelist_model_connectionqueuefilter
{
	private $_connection_queue_filter_id;
	private $_connection_queue_id=null;
	private $_connection_queue_filter_priority=null;
	
	private $_frame_matches=null;
	
	public function __construct($connection_queue_filter_id)
	{
		$this->_connection_queue_filter_id = $connection_queue_filter_id;

		$sql = 	"SELECT * FROM connection_queue_filters
				WHERE connection_queue_filter_id='".$this->_connection_queue_filter_id."'
				";
		
		$connection_queue_filter_detail  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

		$this->_connection_queue_id								= $connection_queue_filter_detail['connection_queue_id'];
		$this->_connection_queue_filter_name					= $connection_queue_filter_detail['connection_queue_filter_name'];
		$this->_connection_queue_filter_priority				= $connection_queue_filter_detail['connection_queue_filter_priority'];

	}
	
	public function get_connection_queue_filter_id()
	{
		return $this->_connection_queue_filter_id;
	}
	
	public function get_frame_matches()
	{
		if ($this->_frame_matches == null) {
			
			$sql = 	"SELECT * FROM frame_matches
					WHERE connection_queue_filter_id='".$this->_connection_queue_filter_id."'
					";
			
			$frame_matches  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			if (isset($frame_matches['0'])) {
				
				foreach($frame_matches as $frame_match) {
					$this->_frame_matches[] = new Thelist_Model_framematch($frame_match['frame_match_id']);
				}
			}
		}
		
		return $this->_frame_matches;
		
	}
	
	public function create_frame_match($frame_header_id, $match_or_exclude=1, $match_value_start=null, $match_value_end=null)
	{

		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		$class	= get_class($this);
			
		$data = array(
							'connection_queue_filter_id'				=>  $this->_connection_queue_filter_id,
							'frame_header_id'							=>  $frame_header_id,
							'frame_match_or_exclude'					=>  $match_or_exclude,
							'frame_match_value_1'						=>  $match_value_start,
							'frame_match_value_2'						=>  $match_value_end,
	
		);
			
		$new_frame_match_id = Zend_Registry::get('database')->insert_single_row('frame_matches',$data,$class,$method);
	
		if ($this->_frame_matches == null) {
			$this->get_frame_matches();
		}
	
		$this->_frame_matches[$new_frame_match_id] = new Thelist_Model_framematch($new_frame_match_id);
	
		return $this->_frame_matches[$new_frame_match_id];
	}
	
	public function get_connection_queue_id()
	{
		return $this->_connection_queue_id;
	}
	
	public function get_connection_queue_filter_name()
	{
		return $this->_connection_queue_filter_name;
	}
	
	public function get_connection_queue_filter_priority()
	{
		return $this->_connection_queue_filter_priority;
	}
	
}
?>