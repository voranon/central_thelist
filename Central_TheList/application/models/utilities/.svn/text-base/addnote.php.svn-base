<?php

//exception codes 22800-22899

class thelist_utility_addnote
{
	private $_user_session;
	private $_note_content;
	private $_time;
	
	function __construct($note_content)
	{
		
		//purpose is to make sure that adding notes across the different classes is consistent
		
		$this->_note_content 	= $note_content;
		$this->_user_session 	= new Zend_Session_Namespace('userinfo');
		$this->_time 			= new Thelist_Utility_time();
		
   	}
   	
   	public function add_end_user_note($end_user_service_id)
   	{
   		$valid = $this->validate_note_content_language();

   		if ($valid === false) {
   			throw new exception("adding new note to end user service id: '".$end_user_service_id."', content has laungauge that is not allowed", 22800);
   		} else {
   			
   			//create the note
   			$new_note = $this->create_new_note();
   			
   			$trace 		= debug_backtrace();
   			$method 	= $trace[0]["function"];
   			$class		= get_class($this);
   				
   			//create the mapping
   			$data = array(
   			
				'end_user_service_id'	=>	$end_user_service_id,
				'note_id'				=>	$new_note->get_note_id(),
	
   			);
   			
   			Zend_Registry::get('database')->insert_single_row('end_user_note_mapping', $data, $class, $method);

   			//set the end user service id
   			$new_note->set_end_user_service_id($end_user_service_id);
   			
   			return $new_note;
   		}
   	}
   	
   	private function validate_note_content_language()
   	{
   		//this should be expanded to a more comprehensive check.
   		if (preg_match("/fuck/", $this->_note_content)) {
   			return false;
   		} else {
   			return true;
   		}
   	}
   	
   	private function create_new_note()
   	{
		if ($this->_note_content != '') {
			
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
			
			$data = array(
			 
							'note_content'	=>	$this->_note_content,
							'creator'		=>	$this->_user_session->uid,
							'createdate'	=>  $this->_time->get_current_date_time_mysql_format(),
			);
			 
			$new_note_id = Zend_Registry::get('database')->insert_single_row('notes', $data, $class, $method);
			
			return new Thelist_Model_note($new_note_id);
			
		} else {
			throw new exception("adding new note but content is empty, not mush point in that", 22801);
		}
   	}

}
?>