<?php

//exception codes 23100-23199

class thelist_model_contact
{
	
	private $_contact_id;
    private $_first_name;
    private $_last_name;
    private $_creator;
    private $_createdate;
    private $_user;
    
    private $_end_user_service_contact_map_id=null;
    private $_end_user_service_primary_contact=null;
    private $_end_user_service_id=null;
    
    
    private $_title=null;
    private $_title_resolved=null;
	
	public function __construct($contact_id)
	{
		
		$this->_contact_id	= $contact_id;

		$contact = Zend_Registry::get('database')->get_contacts()->fetchRow("contact_id=".$this->_contact_id);
		
		$this->_first_name			= $contact['first_name'];
		$this->_last_name			= $contact['last_name'];
		$this->_creator				= $contact['creator'];
		$this->_createdate			= $contact['createdate'];
	}
	
	public function get_contact_id(){
		return $this->_contact_id;
	}

	public function get_first_name(){
		//return $this->_contact_id;
		return $this->_first_name;
	}
	public function get_last_name(){
		return $this->_last_name;
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
	
	public function set_title($title)
	{
		if ($this->_title == null) {
			
			$sql =	"SELECT * FROM items
	   				 WHERE item_id=".$title."
	   				 AND item_type='contact_titles'
	   				";
			 
			$title_detail = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
			if (isset($title_detail['item_id'])) {
				
				$this->_title				= $title_detail['item_id'];
				$this->_title_resolved		= $title_detail['item_value'];
				
			} else {
				throw new exception("you are trying to set the title for contact id: '".$this->_contact_id."' but title: '".$title."' is not a valid title from items", 22901);
			}
		}
	}
	
	public function get_title()
	{
		return $this->_title;
	}
	
	public function get_title_resolved()
	{
		return $this->_title_resolved;
	}
	
	public function set_end_user_service_id($end_user_service_id)
	{
		if ($this->_end_user_service_contact_map_id == null) {
	
			$sql = "SELECT * FROM end_user_service_contact_mapping
					WHERE end_user_service_id ='".$end_user_service_id."'
					AND contact_id='".$this->_contact_id."'
					";
	
			$mapping_detail = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
				
			if (isset($mapping_detail['end_user_service_contact_map_id'])) {
					
				$this->_end_user_service_contact_map_id 	= $mapping_detail['end_user_service_contact_map_id'];
				$this->_end_user_service_primary_contact 	= $mapping_detail['end_user_service_primary_contact'];
				$this->_end_user_service_id 				= $end_user_service_id;
				
				
					
			} else {
				throw new exception("you are trying to set the end user map for contact id: '".$this->_contact_id."' to end user service id: '".$end_user_service_id."', but that mapping does not exist", 22900);
			}
		}
	}
	
	public function get_end_user_service_primary_contact()
	{
		if ($this->_end_user_service_id != null) {
			return $this->_end_user_service_primary_contact;
		} else {
			throw new exception("you are trying to get end_user_service_primary_contact for contact id: '".$this->_contact_id."' but the contact is not mapped, map it first", 22902);
		}
	}
	
	public function set_end_user_service_primary_contact($status)
	{
		if ($this->_end_user_service_id != null) {
			
			if ($status == 0 || $status == 1) {
				
				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);

				Zend_Registry::get('database')->set_single_attribute($this->_end_user_service_contact_map_id, 'end_user_service_contact_mapping', 'end_user_service_primary_contact', $status, $class, $method);
				
				$this->_end_user_service_primary_contact = $status;
				
			} else {
				throw new exception("you are trying to set end_user_service_primary_contact for contact id: '".$this->_contact_id."' but the status value: '".$status."' is not valid", 22905);
			}

		} else {
			throw new exception("you are trying to set end_user_service_primary_contact for contact id: '".$this->_contact_id."' but the contact is not mapped, map it first", 22904);
		}
	}
	
	public function get_end_user_service_contact_map_id()
	{
		if ($this->_end_user_service_id != null) {
			return $this->_end_user_service_contact_map_id;
		} else {
			throw new exception("you are trying to get end_user_service_contact_map_id for contact id: '".$this->_contact_id."' but the contact is not mapped, map it first", 22903);
		}
	}


	public function toArray()
	{
		$obj_content	= print_r($this, 1);
		$class_name		= get_class($this);
	
		//get all private variable names
		preg_match_all("/\[(.*):".$class_name.":private\]/", $obj_content, $matches);
	
		if (isset($matches['0']['0'])) {
			 
			$complete['private_variable_names'] = $matches['1'];
			 
			foreach ($matches['1'] as $index => $private_variable_name) {
	
				$one_variable	= $this->$private_variable_name;
				 
				if (is_array($one_variable)) {
					$complete['private_variable_type'][$index] = 'array';
				} elseif (is_object($one_variable)) {
					$complete['private_variable_type'][$index] = 'object';
				} else {
					$complete['private_variable_type'][$index] = 'string';
				}
			}
	
			foreach ($complete['private_variable_names'] as $private_index => $private_variable) {
					
				if ($complete['private_variable_type'][$private_index] == 'object') {
	
					if (method_exists($this->$private_variable, 'toArray')) {
						$return_array[$private_variable] = $this->$private_variable->toArray();
					} else {
						$return_array[$private_variable] = 'CLASS IS MISSING toArray METHOD';
					}
	
				} elseif ($complete['private_variable_type'][$private_index] == 'string') {
	
					$return_array[$private_variable] = $this->$private_variable;
	
				} elseif ($complete['private_variable_type'][$private_index] == 'array') {
						
					$array_tools						= new Thelist_Utility_arraytools();
					$return_array[$private_variable] 	= $array_tools->convert_mixed_array_to_strings($this->$private_variable);
	
				}
			}
		}
	
		return $return_array;
	}
	
	
	
	
}
?>