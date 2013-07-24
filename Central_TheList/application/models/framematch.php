<?php 

//exception codes 15100-15199

class thelist_model_framematch
{
	private $_frame_match_id;
	private $_connection_queue_filter_id=null;
	private $_frame_header_id=null;
	private $_frame_header_name=null;
	private $_frame_match_or_exclude=null;
	private $_frame_match_value_1=null;
	private $_frame_match_value_2=null;

	
	public function __construct($frame_match_id)
	{
		$this->_frame_match_id = $frame_match_id;

		$sql = 	"SELECT * FROM frame_matches
				WHERE frame_match_id='".$this->_frame_match_id."'
				";
		
		$frame_match_detail  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		$this->_connection_queue_filter_id				= $frame_match_detail['connection_queue_filter_id'];
		$this->_frame_header_id							= $frame_match_detail['frame_header_id'];
		$this->_frame_match_or_exclude					= $frame_match_detail['frame_match_or_exclude'];
		$this->_frame_match_value_1						= $frame_match_detail['frame_match_value_1'];
		$this->_frame_match_value_2						= $frame_match_detail['frame_match_value_2'];

	}
	
	public function get_frame_match_id()
	{
		return $this->_frame_match_id;
	}
	
	public function get_frame_header_id()
	{
		return $this->_frame_header_id;
	}
	
	public function get_frame_header_name()
	{
		if ($this->_frame_header_id != null) {
			
			if ($this->_frame_header_name == null) {
			
				$sql = 	"SELECT * FROM frame_headers
						WHERE frame_header_id='".$this->_frame_header_id."'
						";
				
				$frame_protocol_detail  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
				$this->_frame_header_name = $frame_protocol_detail['frame_header_name'];
				
			}
		}
		
		return $this->_frame_header_name;
	}
	
	public function get_frame_match_or_exclude()
	{
		return $this->_frame_match_or_exclude;
	}
	
	public function get_frame_match_value_1()
	{
		return $this->_frame_match_value_1;
	}
	
	public function get_frame_match_value_2()
	{
		return $this->_frame_match_value_2;
	}
}
?>