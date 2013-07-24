<?php

//exception codes 22900-22999

class thelist_model_note
{

	//checked by martin
	private $_note_id;
	
	private $_end_user_note_map_id=null;
	private $_end_user_service_id=null;
	
	private $_note_content;
	private $_createdate;
	private $_creator;
	private $_user=null;
	
	public function __construct($note_id)
	{
		$this->_note_id			= $note_id;
		
		$note = Zend_Registry::get('database')->get_notes()->fetchRow("note_id=".$this->_note_id);
		
		$this->_note_content 	= $note['note_content'];
		$this->_createdate		= $note['createdate'];
		$this->_creator			= $note['creator'];

	}
	
	public function get_note_id()
	{
		return $this->_note_id;
	}
	
	public function get_note_content()
	{
		return $this->_note_content;
	}
	
	public function get_createdate()
	{
		return $this->_createdate;
	}
	
	public function get_creator()
	{
		return $this->_creator;
	}
	
	public function get_user()
	{
		if ($this->_user == null) {
			$this->_user = new Thelist_Model_users($this->_creator);
		}
		return $this->_user;
	}
	
	public function set_end_user_service_id($end_user_service_id)
	{
		if ($this->_end_user_note_map_id == null) {

			$sql = "SELECT * FROM end_user_note_mapping
					WHERE end_user_service_id ='".$end_user_service_id."'
					AND note_id='".$this->_note_id."'
					";
				
			$mapping_detail = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
			if (isset($mapping_detail['end_user_note_map_id'])) {
					
				$this->_end_user_note_map_id = $mapping_detail['end_user_note_map_id'];
				$this->_end_user_service_id = $end_user_service_id;
					
			} else {
				throw new exception("you are trying to set the end user map for note id: '".$this->_note_id."' name to end user service id: '".$end_user_service_id."', but that mapping does not exist", 22900);
			}
		}
	}
	
	/*
	public function set_note_content($note_content){
		$data	= array(
						'note_content'	=> $note_content
				       );
		
		$where  = array(
						'note_id'		=> $this->_note_id
					   );
		
		Zend_Registry::get('database')->get_notes()->update($data,$where);
		$this->_note_content=$note_content;
	}
	
	
	public function get_attachment_path(){
		return $this->attachment_paths;
	}
	*/
	
}
?>