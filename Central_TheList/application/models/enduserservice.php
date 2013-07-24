<?php

//exception codes 100000-100099

class thelist_model_enduserservice
{
	
	private $_created;
	private $_deactivated;
	
	

	//checked by martin
	private $_end_user_service_id;
	private $_unit_id;
	private $_unit=null;
	private $_contacts=null;
	private $_primary_contact=null;
	private $_service_plan_temp_quote_mappings=null;
	private $_sales_quotes=null;
	private $_notes=null;
	
	public function __construct($end_user_service_id=null)
	{
		$this->_end_user_service_id		= $end_user_service_id;
		
		$sql =	"SELECT * FROM end_user_services
				 WHERE end_user_service_id=".$this->_end_user_service_id."
				";
		
		$end_user_service = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		$this->_unit_id					= $end_user_service['unit_id'];
		$this->_created					= $end_user_service['created'];
       
	}
	
	public function get_end_user_service_id()
	{
		return $this->_end_user_service_id;
	}

	public function get_unit_id()
	{
		return $this->_unit_id;
	}
	
	public function add_end_user_service_note($content)
	{
		$add_note_obj								= new Thelist_Utility_addnote($content);
		$new_note									= $add_note_obj->add_end_user_note($this->_end_user_service_id);
		$this->_notes[$new_note->get_note_id()] 	= $new_note;

		return $new_note;
	}
	
	public function get_notes()
	{
		if ($this->_notes == null) {
			
			$sql =	"SELECT note_id FROM end_user_note_mapping
					WHERE end_user_service_id ='".$this->_end_user_service_id."'
					";
			
			$notes = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			if (isset($notes['0'])) {
					
				foreach($notes as $note){
					$this->_notes[$note['note_id']] = new Thelist_Model_note($note['note_id']);
					$this->_notes[$note['note_id']]->set_end_user_service_id($this->_end_user_service_id);
				}
			}
		}
		
		return $this->_notes;
	}
	
	
// 	public function get_created()
// 	{
// 		return $this->_created;
// 	}
// 	public function get_deactivated()
// 	{
// 		return $this->_deactivated;
// 	}
	public function get_unit()
	{
		if ($this->_unit == null) {
			$this->_unit = new Thelist_Model_unit($this->_unit_id);
		}
		
		return $this->_unit;
	}
	
	public function add_end_user_service_contact($title, $first_name=null, $last_name=null, $make_primary=false)
	{
		//make sure we know if this this should be the primary contact
		if ($make_primary !== false && $make_primary !== true) {
			throw new exception("adding new contact to end user service id: '".$this->_end_user_service_id."', but i have no idea if you want this contact to be primary or not", 100016);
		} else {

			$add_contact_obj									= new Thelist_Utility_addcontact($first_name, $last_name);
			$new_contact										= $add_contact_obj->add_end_user_contact($this->_end_user_service_id, $title);
			
			try {
			
				$contacts = $this->get_contacts();
			
			} catch (Exception $e) {
			
				switch($e->getCode()){
						
					case 100015;
					//100015, no primary contact assigned, we now force this contact to be primary regardless of user choice
					$make_primary = true;
					break;
					default;
					throw $e;
				}
			}

			$this->_contacts[$new_contact->get_end_user_service_contact_map_id()] = $new_contact;

			if ($make_primary === true) {
				//make the contact primary
				$this->set_primary_contact($new_contact->get_end_user_service_contact_map_id());
			}
			
			return $new_contact;
		}
	}
	
	public function set_primary_contact($end_user_service_contact_map_id)
	{
		$already_primary = 'no';
		
		try {
				
			$contacts = $this->get_contacts();
			
			foreach ($contacts as $contact) {
					
				if ($contact->get_end_user_service_primary_contact() == 1) {
						
					if ($contact->get_end_user_service_contact_map_id() == $end_user_service_contact_map_id) {
			
						//it is already primary, we are done, but we let the loop finish
						$already_primary = 'yes';
			
					} else {
						$contact->set_end_user_service_primary_contact(0);
					}
				}
			}

		} catch (Exception $e) {
				
			switch($e->getCode()){
					
				case 100015;
				//100015, no primary contact assigned, thats ok, we are trying to set one
				break;
				default;
				throw $e;
			}
		}
		
		
		if ($already_primary == 'no') {
			
			//this will not fail even if there is no current primary even though get_contact runs get all contacts again, because the contacts variable is filled now
			$new_primary 				= $this->get_contact($end_user_service_contact_map_id);
			$new_primary->set_end_user_service_primary_contact(1);
			$this->_primary_contact 	= $new_primary;
		}
	}

	public function get_contacts()
	{
		if ($this->_contacts == null) {
			
			$sql2 = "SELECT * FROM end_user_service_contact_mapping
					WHERE end_user_service_id='".$this->_end_user_service_id."'
					";
			
			$contacts = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
			
			if (isset($contacts['0'])) {
				
				foreach($contacts as $contact){
						
					$this->_contacts[$contact['end_user_service_contact_map_id']] = new Thelist_Model_contact($contact['contact_id']);
					$this->_contacts[$contact['end_user_service_contact_map_id']]->set_end_user_service_id($this->_end_user_service_id);
					$this->_contacts[$contact['end_user_service_contact_map_id']]->set_title($contact['end_user_service_contact_title']);
						
					if ($contact['end_user_service_primary_contact'] == 1) {
						$this->_primary_contact = $this->_contacts[$contact['end_user_service_contact_map_id']];
					}
				}
				
			}
			
			if ($this->_primary_contact == null) {
				throw new exception("end user service id '".$this->_end_user_service_id."' does not have a primary contact this should not be possible", 100015);
			}
			
		}
		
		return $this->_contacts;
	}
	
	public function get_contact($end_user_services_contact_map_id)
	{
		$this->get_contacts();
		if (isset($this->_contacts[$end_user_services_contact_map_id])) {
			return $this->_contacts[$end_user_services_contact_map_id];
		} else {
			return false;
		}
	}
	
	public function get_primary_contact()
	{
		$this->get_contacts();
		return $this->_primary_contact;
	}
	
	public function get_service_plan_temp_quote_mappings()
	{
		
		if($this->_service_plan_temp_quote_mappings == null){
			$sql = "SELECT * FROM service_plan_temp_quote_mapping
					WHERE end_user_service_id =".$this->_end_user_service_id;
			
			$service_plan_temp_quote_mappings = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			if( isset($service_plan_temp_quote_mappings['0']) ){
				
				foreach( $service_plan_temp_quote_mappings as $service_plan_temp_quote_mapping ){
					$this->_service_plan_temp_quote_mappings[$service_plan_temp_quote_mapping['service_plan_temp_quote_map_id']] = new Thelist_Model_serviceplantempquotemap($service_plan_temp_quote_mapping['service_plan_temp_quote_map_id']);
				}
			}
		}	
		
		return $this->_service_plan_temp_quote_mappings;
	}
	
	public function get_service_plan_temp_quote_map($service_plan_temp_quote_map_id)
	{
		$this->get_service_plan_temp_quote_mappings();

		if(isset($this->_service_plan_temp_quote_mappings[$service_plan_temp_quote_map_id])){
			return $this->_service_plan_temp_quote_mappings[$service_plan_temp_quote_map_id];
		} else {
			return false;
		}
	}
	
	public function add_service_plan_temp_quote_map($service_plan_id, $service_plan_temp_quote_actual_mrc_term, $service_plan_temp_quote_actual_mrc, $service_plan_temp_quote_actual_nrc, $service_plan_temp_discount=null){
		
		if(!is_numeric($service_plan_id)) {
			throw new exception("service_plan_id must be numeric, you provided: '".$service_plan_id."' when mapping a new service plan to unit_id: '".$this->unit_id."' ", 100001);
		} elseif (!preg_match("/^\d+(\.\d{1,2})?$/", $service_plan_temp_quote_actual_mrc)) {
			throw new exception("MRC amount is not a valid doller amount: '".$service_plan_temp_quote_actual_mrc."' when mapping temp service plan with id: '".$service_plan_id."' to end user service id '".$this->_end_user_service_id."'", 100005);
		} elseif (!preg_match("/^\d+(\.\d{1,2})?$/", $service_plan_temp_quote_actual_nrc)) {
			throw new exception("NRC amount is not a valid doller amount: '".$service_plan_temp_quote_actual_nrc."' when mapping temp service plan with id: '".$service_plan_id."' to end user service id '".$this->_end_user_service_id."'", 100006);
		} elseif (!preg_match("/^[0-9]+$/", $service_plan_temp_quote_actual_mrc_term)) {
			throw new exception("Term, is not a whole number: '".$service_plan_temp_quote_actual_mrc_term."' when mapping temp service plan with id: '".$service_plan_id."' to end user service id '".$this->_end_user_service_id."'", 100007);
		} else {

			$active_service_plan_maps = $this->get_unit()->get_active_service_plan_maps();
			
			if(isset($active_service_plan_maps[$service_plan_id])) {
				
					$trace  = debug_backtrace();
					$method = $trace[0]["function"];
					$class		= get_class($this);
					
					$this->get_service_plan_temp_quote_mappings();
					
										
					$data = array(
									'end_user_service_id'						=> $this->_end_user_service_id,
									'service_plan_id'							=> $service_plan_id,
									'service_plan_temp_quote_actual_mrc'		=> $service_plan_temp_quote_actual_mrc,
									'service_plan_temp_quote_actual_nrc'    	=> $service_plan_temp_quote_actual_nrc,
									'service_plan_temp_quote_actual_mrc_term'	=> $service_plan_temp_quote_actual_mrc_term,
									'service_plan_temp_discount'				=> $service_plan_temp_discount
								 );

					$new_service_plan_temp_quote_map_id = Zend_Registry::get('database')->insert_single_row('service_plan_temp_quote_mapping', $data, $class, $method);

					$this->_service_plan_temp_quote_mappings[$new_service_plan_temp_quote_map_id] = new Thelist_Model_serviceplantempquotemap($new_service_plan_temp_quote_map_id);
					
					return $this->_service_plan_temp_quote_mappings[$new_service_plan_temp_quote_map_id];
				
			} else {
				throw new exception("when mapping temp service plan with id: '".$service_plan_id."' to end user service id '".$this->_end_user_service_id."' the unit is not allowed to have this service plan mapped", 100008);
			}
		}
	}
	
	public function remove_service_plan_temp_quote_map($service_plan_temp_quote_map_id)
	{
		
		if(is_numeric($service_plan_temp_quote_map_id)) {
			
			$temp_service_plan = $this->get_service_plan_temp_quote_map($service_plan_temp_quote_map_id);
			
			//make sure the service plan temp is mapped to this enduser
			if ($temp_service_plan != false) {
				
				$temp_mapped_options	= $temp_service_plan->get_service_plan_temp_quote_options();
				if ($temp_mapped_options != null) {
					//remove all mapped options
					foreach ($temp_mapped_options as $temp_mapped_option) {
						$temp_service_plan->remove_service_plan_temp_quote_option($temp_mapped_option->get_service_plan_temp_quote_option_map_id());
					}
				}
				
				$temp_mapped_eq_types	= $temp_service_plan->get_service_plan_temp_quote_eq_types();
				if ($temp_mapped_eq_types != null) {
					//remove all mapped equipment
					foreach ($temp_mapped_eq_types as $temp_mapped_eq_type) {
						$temp_service_plan->remove_service_plan_temp_quote_eq_type($temp_mapped_eq_type->get_service_plan_temp_quote_eq_type_map_id());
					}
				}
				
				//now that all options and equipment types have been removed, we can delete the temp service plan.
				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);
				
				//delete in database
				Zend_Registry::get('database')->delete_single_row($service_plan_temp_quote_map_id, 'service_plan_temp_quote_mapping', $class, $method);
					
				//unset from class
				unset($this->_service_plan_temp_quote_mappings[$service_plan_temp_quote_map_id]);
				
			} else {
				throw new exception("you are trying to remove service_plan_temp_quote_map_id: '".$service_plan_temp_quote_map_id."' from end user service id '".$this->_end_user_service_id."', but this end user does not have that temp plan mapped", 100014);
			}

		} else {
			throw new exception("you are trying to remove a service_plan_temp_quote_map_id from end user service id '".$this->_end_user_service_id."', but the service_plan_temp_quote_map_id is not numeric: '".$service_plan_temp_quote_map_id."'", 100014);
		} 
	}
	
// 	public function remove_service_plan_temp_quote_mapping($service_plan_id){
		
// 		if( !is_numeric($service_plan_id) )
// 		{
			//if it's not numeric
// 			throw new exception("service_plan_id must be numeric, you provided: '".$service_plan_id."' when mapping a new service plan to unit_id: '".$this->unit_id."' ", 100003);
// 		}
// 		else 
// 		{
// 			$trace  	= debug_backtrace();
// 			$method 	= $trace[0]["function"];
// 			$class		= get_class($this);
			
// 			$service_plan_temp_quote_mappings = $this->get_service_plan_temp_quote_mappings();
			
// 			foreach( $service_plan_temp_quote_mappings as $service_plan )
// 			{
		
// 				if( $service_plan->get_service_plan_id() == $service_plan_id)
// 				{
				//	check see if it has options, force remove
// 					$options  = $service_plan->get_service_plan_temp_quote_options();
					
// 					foreach( $options as $option){
// 						echo $option->get_service_plan_temp_quote_option_map_id().'m';
// 					}	
					
// 					echo count($options).'<br>';
											 
				//	check see if it has eq_type, force remove
// 					$eq_types = $service_plan->get_service_plan_temp_quote_eq_types();
// 					echo count($eq_types).'<br>';
					
// 				}
				
// 			}
			
// 		}
		
// 	}
	
	public function get_sales_quotes() 
	{
		//changed behaivour to verify if objects are already generated after talking to non sep 11 2012
		if ($this->_sales_quotes == null) {

			$sales_quotes	=	Zend_Registry::get('database')->get_sales_quotes()->fetchAll('end_user_service_id='.$this->_end_user_service_id);
			
			if (isset($sales_quotes['0'])) {
				
				foreach($sales_quotes as $sales_quote) {
					
					$this->_sales_quotes[$sales_quote['sales_quote_id']] = new Thelist_Model_salesquote($sales_quote['sales_quote_id']);
				}
			}
		}
		
		return $this->_sales_quotes;
	}
	
	public function get_sales_quote($sales_quote_id)
	{
		$this->get_sales_quotes();

		if (isset($this->_sales_quotes[$sales_quote_id])) {
			return $this->_sales_quotes[$sales_quote_id];
		} else {
			return false;
		}
	}

	public function create_sales_quote_from_temp_quote()
	{
		if ($this->is_service_plan_temp_quote_valid() == true) {

			//if the validation passed then we inset the the new quote
			
			//since there are a second set of validations when inserting to the sales quote structure, we make this transaction safe
			Zend_Registry::get('database')->get_thelist_adapter()->beginTransaction();
			
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
			$class		= get_class($this);
			
			$time = new Thelist_Utility_time();
			
			//create the sales quote
			$data = array(
										'end_user_service_id'		=>		$this->_end_user_service_id,	
										'created'					=>      $time->get_current_date_time(),
										'last_emailed_date'			=>      null,
										'sales_quote_accepted'      =>      '0'
			);
				
			$new_sales_quote_id 						= Zend_Registry::get('database')->insert_single_row('sales_quotes', $data, $class, $method);
			$new_sales_quote_obj						= new Thelist_Model_salesquote($new_sales_quote_id);
			
			//get all existing quotes, fill the var at the top
			$this->get_sales_quotes();
			
			//append the new to the top
			$this->_sales_quotes[$new_sales_quote_id] 	= $new_sales_quote_obj;
			
			//get all temp plans
			$all_temp_service_plans	= $this->get_service_plan_temp_quote_mappings();
	
			foreach ($all_temp_service_plans as $temp_service_plan) {
				$new_sales_quote_obj->add_service_plan_quote_map_from_temp_service_plan($temp_service_plan->get_service_plan_temp_quote_map_id());
			}
	
			//no exceptions, then commit the data
			Zend_Registry::get('database')->get_thelist_adapter()->commit();
			
			return $new_sales_quote_obj;
			
		} else {
			throw new exception("temp service plan did not pass validation for end user service id '".$this->_end_user_service_id."'", 100013);
		}
	}
	
	public function is_service_plan_temp_quote_valid()
	{
		$allowed_service_plans 	= $this->get_unit()->get_active_service_plan_maps();
		$current_service_plans	= $this->get_service_plan_temp_quote_mappings();
		
		if ($current_service_plans != null) {
			
			foreach ($current_service_plans as $current_service_plan) {
			
				//is the service plan allowed for this unit.
				if (!isset($allowed_service_plans[$current_service_plan->get_service_plan_id()])) {
					throw new exception("service_plan_temp_quote_map_id '".$current_service_plan->get_service_plan_id()."', is using a service plan that is not active for unit:'".$this->_unit_id."', end_user_service_id: '".$this->_end_user_service_id."' ", 100004);
				} elseif ($current_service_plan->get_service_plan()->is_active() === false) {
					throw new exception("service_plan_temp_quote_map_id '".$current_service_plan->get_service_plan_id()."', is using a service plan that is active for unit:'".$this->_unit_id."', end_user_service_id: '".$this->_end_user_service_id."', service plan is no longer active ", 100009);
				}

				//get all options
				$all_option_maps = $allowed_service_plans[$current_service_plan->get_service_plan_id()]->get_service_plan_option_maps();
				if ($all_option_maps != null) {
					
					foreach ($all_option_maps as $option_map) {
						
						$option_result	= $current_service_plan->service_plan_option_map_requirement_fulfilled($option_map->get_service_plan_option_map_id());
						
						if ($option_result == 'no') {
							throw new exception("option map: '".$option_map->get_service_plan_option_map_id()."' requirement not fulfilled service_plan_temp_quote_map_id '".$current_service_plan->get_service_plan_id()."'", 100010);
						}
					}
				}
			
				//get all temp equipment
				$all_eq_type_maps = $allowed_service_plans[$current_service_plan->get_service_plan_id()]->get_service_plan_eq_type_maps();
				
			if ($all_eq_type_maps != null) {
					
					foreach ($all_eq_type_maps as $eq_type_map) {
						
						$eq_type_result	= $current_service_plan->service_plan_eq_type_map_requirement_fulfilled($eq_type_map->get_service_plan_eq_type_map_id());
						
						if ($eq_type_result == 'no') {
							throw new exception("eq type map: '".$eq_type_map->get_service_plan_eq_type_map_id()."' requirement not fulfilled service_plan_temp_quote_map_id '".$current_service_plan->get_service_plan_id()."'", 100011);
						}
					}
				}
			}

		} else {
			//no plans mapped then not valid
			throw new exception("validating the temp service plan quote, no service plans mapped for end user service id : '".$this->_end_user_service_id."'", 100012);
		}
			
		//if we make it through, then we return true
		return true;
	}
	
	/*
	public function add_contact($contact_id,$title=null){
		
		$trace = debug_backtrace();
		$method = $trace[0]["function"];
		
		$exist_query="SELECT COUNT(*) AS exist
					  FROM end_user_services_contact_mapping
					  WHERE contact_id=".$contact_id."
					  AND end_user_service_id=".$this->_end_user_service_id;
		
		$exist = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($exist_query);
		
		if(!$exist)
		{
			$data = array(
											'contact_id'			=>		$contact_id,
											'contact_title' 		=>      $title,
											'end_user_service_id'	=>		$this->_end_user_service_id,
											'created'				=>		$this->_time->get_current_date_time(),
			);
			Zend_Registry::get('database')->insert_single_row('end_user_services_contact_mapping',$data,get_class($this),$method);
			return $contact_id;
		}else{

			// do nothing when it's existing
			return 0;
		}
		
		
	
		
	}
	
	
	public function remove_contact($contact_id){
		$trace = debug_backtrace();
		$method = $trace[0]["function"];
		
		$sql="SELECT end_user_services_contact_map_id
			  FROM end_user_services_contact_mapping
			  WHERE end_user_service_id = ".$this->_end_user_service_id."
			  AND contact_id = ".$contact_id;
		
		$end_user_services_contact_map_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		Zend_Registry::get('database')->delete_single_row($end_user_services_contact_map_id,'end_user_services_contact_mapping',get_class($this),$method);
		
	}
	
	public function set_primary_contact($contact_id){
		
		
		$sql="UPDATE end_user_services_contact_mapping
			  SET primary_contact=0
			  WHERE end_user_service_id = ".$this->_end_user_service_id;
		Zend_Registry::get('database')->get_thelist_adapter()->query($sql);
		
		
		$sql="UPDATE end_user_services_contact_mapping
			  SET primary_contact=1
			  WHERE end_user_service_id = ".$this->_end_user_service_id."
			  AND contact_id = ".$contact_id;
		
		Zend_Registry::get('database')->get_thelist_adapter()->query($sql);
		
		
		
	}
	
	public function add_note($note_id){
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		
		$data = array(
						'end_user_service_id'	=>    $this->_end_user_service_id,
						'note_id'				=>	  $note_id    
					);
		
			
		Zend_Registry::get('database')->insert_single_row('end_user_note_mapping',$data,get_class($this),$method);
			
						
	}
	
	public function get_notes(){
		
		$sql="SELECT n.note_id
			  FROM end_user_note_mapping eunm
			  LEFT OUTER JOIN notes n ON eunm.note_id=n.note_id
			  WHERE eunm.end_user_service_id =".$this->_end_user_service_id;
		
		$notes = Zend_Registry::get('database')->get_thelist_adapter()->query($sql);
		
		foreach($notes as $note){
			$this->_notes[ $note['note_id'] ] = new Thelist_Model_notes( $note['note_id'] );
		}

		return $this->_notes;
		
	}
	
	*/

}
?>