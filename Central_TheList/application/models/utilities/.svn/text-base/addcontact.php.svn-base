<?php

//exception codes 23000-23099 

class thelist_utility_addcontact
{
	private $_user_session;
	private $_time;
	
	private $_first_name;
	private $_last_name;
	
	function __construct($first_name=null, $last_name=null)
	{
		
		//purpose is to make sure that adding contacts across the different classes is consistent
		//and eventually this class will identify contacts that are identical and only perform map and not create another contact
		$this->_user_session 		= new Zend_Session_Namespace('userinfo');
		$this->_time 				= new Thelist_Utility_time();
		
		$this->_first_name 			= $first_name;
		$this->_last_name 			= $last_name;

   	}
   	
   	public function add_end_user_contact($end_user_service_id, $title)
   	{
		if ($this->is_valid_title($title) === false) {
   			throw new exception("adding new contact to end user service id: '".$end_user_service_id."', but contact title is not a title", 22302);
   		} elseif (!is_numeric($end_user_service_id)) {
   			throw new exception("adding new contact to end user service id: '".$end_user_service_id."', but end user service id is not numeric", 22303);
   		}
   		
   		$trace 		= debug_backtrace();
   		$method 	= $trace[0]["function"];
   		$class		= get_class($this);
   		   		
   		//create the contact
   		$new_contact = $this->create_new_contact();
   		
   		//create the mapping
   		$data = array(
				'contact_id'						=> $new_contact->get_contact_id(),
				'end_user_service_id'				=> $end_user_service_id,
				'end_user_service_contact_title'	=> $title,
   				'end_user_service_primary_contact'	=> 0,
   				'creator'							=> $this->_user_session->uid,
				'createdate'						=> $this->_time->get_current_date_time_mysql_format(),
		);
  			
   		Zend_Registry::get('database')->insert_single_row('end_user_service_contact_mapping', $data, $class, $method);

   		//set the end user service id
  		$new_contact->set_end_user_service_id($end_user_service_id);

  		//set the title
  		$new_contact->set_title($title);

   		return $new_contact;
   	}
   	
   	private function is_valid_title($title)
   	{
   		$sql =	"SELECT COUNT(item_id) FROM items
   				 WHERE item_id=".$title."
   				 AND item_type='contact_titles'
   				";
   		
   		$valid = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
   		
   		if ($valid == 0) {
   			return false;
   		} else {
   			return true;
   		}
  	}
   	
   	
   	
   	private function create_new_contact()
   	{
   		if ($this->_first_name == null && $this->_last_name == null) {
   			throw new exception("adding new contact but no first or last name provided, we need something to go on", 22300);
   		} else {
			
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
			
			$data = array(
			 
							'first_name'	=>	$this->_first_name,
							'last_name'	=>		$this->_last_name,
							'creator'		=>	$this->_user_session->uid,
							'createdate'	=>  $this->_time->get_current_date_time_mysql_format(),
			);
			 
			$new_contact_id = Zend_Registry::get('database')->insert_single_row('contacts', $data, $class, $method);
			
			return new Thelist_Model_contact($new_contact_id);

		}
   	}

}
?>