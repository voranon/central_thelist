<?php

//exception codes 600-699

class thelist_model_unit
{
	
	
	//martin checked
	private $_unit_id;
	private $_building_id;
	private $_unit_name;
	private $_unit_street_number;
	private $_unit_street_name;
	private $_unit_street_type;
	private $_unit_city;
	private $_unit_state;
	private $_unit_zip;
	private $_unit_create_date;
	
	private $_service_points=null;
	private $_unused_permanent_installed_equipment=null;
	private $_mapped_service_plans=null;
	private $_unit_groups=null;
	private $_end_user_services=null;
	
	private $_available_service_technicians=null;
	private $_available_install_technicians=null;
	

	public function __construct($unit_id)
	{ 
		$this->_unit_id			= $unit_id;

		$sql =	"SELECT * FROM units
				WHERE unit_id='".$this->_unit_id."'
				";
		
		$unit_detail = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
	
		$this->_building_id  			=$unit_detail['building_id'];
		$this->_unit_name				=$unit_detail['unit_name'];
		$this->_unit_street_number		=$unit_detail['unit_street_number'];
		$this->_unit_street_name		=$unit_detail['unit_street_name'];
		$this->_unit_street_type   		=$unit_detail['unit_street_type'];
		$this->_unit_city				=$unit_detail['unit_city'];
		$this->_unit_state        		=$unit_detail['unit_state'];
		$this->_unit_zip				=$unit_detail['unit_zip'];
		$this->_unit_create_date		=$unit_detail['unit_create_date'];
	}
	
	public function get_unit_id()
	{
		return $this->_unit_id;
	}
	public function get_building_id()
	{
		return $this->_building_id;
	}
	public function get_unit_name()
	{
		return $this->_unit_name;
	}

	public function get_street_number()
	{
		return $this->_unit_street_number;
	}
	public function get_street_name()
	{
		return $this->_unit_street_name;
	}
	public function get_street_type()
	{
		return $this->_unit_street_type;
	}
	public function get_city()
	{
		return $this->_unit_city;
	}
	public function get_state()
	{
		return $this->_unit_state;
	}
	public function get_zip()
	{
		return $this->_unit_zip;
	}
	public function get_createdate()
	{
		return $this->_unit_create_date;
	}
	
	
	public function map_new_unit_group($unit_group_id)
	{
		if (is_numeric($unit_group_id)) {
			
			$unit_groups	= $this->get_unit_groups();
			
			if ($unit_groups != null) {
				
				foreach ($unit_groups as $unit_group) {
					
					if ($unit_group->get_unit_group_id() == $unit_group_id) {
						//already exist
						return $unit_group;
					}
				}
			}
			
		//group map does not already exist create it

			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
			
			$data = array(
			   				'unit_id' 					=> 	$this->_unit_id,
			   				'unit_group_id'				=>  $unit_group_id,
			);
				
			$new_unit_group_map_id	= Zend_Registry::get('database')->insert_single_row('unit_group_mapping', $data, $class, $method);
				
			//instaciate the new group
			$unit_group_obj 	= new Thelist_Model_unitgroup($unit_group_id);
			$unit_group_obj->fill_unit_mapping($this->_unit_id);

			$this->_unit_groups[$group['unit_group_id']]		= $unit_group_obj;
			
			return $unit_group_obj;
		}
	}
	

	public function get_unit_groups($group_type=null)
	{
		if ($this->_unit_groups == null) {
			
			$sql = "SELECT * FROM unit_group_mapping
					WHERE unit_id='".$this->_unit_id."'
					";
			
			$all_groups	= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			if (isset($all_groups['0'])) {
				
				foreach($all_groups as $group) {
					
					$unit_group_obj 									= new Thelist_Model_unitgroup($group['unit_group_id']);
					$unit_group_obj->fill_unit_mapping($this->_unit_id);
					
					$this->_unit_groups[$group['unit_group_id']]		= $unit_group_obj;
				}
			}
		}
		
		if ($this->_unit_groups != null) {
			
			foreach ($this->_unit_groups as $single_group) {
				
				if ($group_type == null) {
					$return[$single_group->get_unit_group_id()]	= $single_group;
				} elseif ($group_type == $single_group->get_unit_group_type_resolved()) {
					$return[$single_group->get_unit_group_id()]	= $single_group;
				}
			}
		}
		
		if (isset($return)) {
			return $return;
		} else {
			return null;
		}
	}
	
	public function get_end_user_services()
	{
		if ($this->_end_user_services == null) {
				
			//do not use activateion and deactivation, if
			//an end user is no longer active that should be implied
			//just dont know how yet
			
			$sql = "SELECT * FROM end_user_services
					WHERE unit_id='".$this->_unit_id."'
					";
				
			$all_end_users	= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
				
			if (isset($all_end_users['0'])) {
	
				foreach($all_end_users as $end_user) {
						
					$this->_end_user_services[$end_user['end_user_service_id']]	= new Thelist_Model_enduserservice($end_user['end_user_service_id']);
				}
			}
		}
		
		return $this->_end_user_services;
	}
	
	public function get_unit_service_points()
	{
		if ($this->_service_points == null) {
			
			$sql 	= 	"SELECT * FROM unit_service_point_mapping
						WHERE unit_id='".$this->_unit_id."'
						";
		
			$unit_service_point_maps = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
			if (isset($unit_service_point_maps['0'])) {
					
				foreach ($unit_service_point_maps as $unit_service_point_map) {
		
					$this->_service_points[$unit_service_point_map['service_point_id']]		= new Thelist_Model_servicepoint($unit_service_point_map['service_point_id']);
		
				}
			}
		}
	
		return $this->_service_points;
	}
	
	public function get_mapped_service_plans()
	{
		if ($this->_mapped_service_plans == null) {
				
			$sql = "SELECT * FROM unit_service_plan_mapping
						WHERE unit_id='".$this->_unit_id."'
						";
				
			$mapped_service_plans = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
				
			if (isset($mapped_service_plans['0'])) {
	
				foreach($mapped_service_plans as $mapped_service_plan) {
					$this->_mapped_service_plans[$mapped_service_plan['service_plan_id']] =  new Thelist_Model_serviceplan($mapped_service_plan['service_plan_id']);
				}
			}
		}
	
		return $this->_mapped_service_plans;
	}
	
	
	public function get_active_service_plan_maps($service_group=null)
	{
		//always get all service plans
		$this->get_mapped_service_plans();
	
		if($this->_mapped_service_plans != null) {
				
			if($service_group == 'internet') {
	
				foreach($this->_mapped_service_plans as $service_plan){
						
					//gather all the service plans that belong to internet and are active
					if ($service_plan->get_service_plan_group_name() == 'Internet' && $service_plan->is_active() === true) {
						$return[ $service_plan->get_service_plan_id() ] = $service_plan;
					}
				}
	
			} elseif($service_group == 'tv') {
	
				foreach($this->_mapped_service_plans as $service_plan){
	
					//gather all the service plans that belong to internet and are active
					if ($service_plan->get_service_plan_group_name() == 'Phone' && $service_plan->is_active() === true) {
						$return[ $service_plan->get_service_plan_id() ] = $service_plan;
					}
				}
	
			} elseif($service_group == 'phone') {
	
				foreach($this->_mapped_service_plans as $service_plan){
	
					//gather all the service plans that belong to internet and are active
					if ($service_plan->get_service_plan_group_name() == 'TV' && $service_plan->is_active() === true) {
						$return[ $service_plan->get_service_plan_id() ] = $service_plan;
					}
				}
	
			} else {
	
				foreach( $this->_mapped_service_plans as $service_plan ) {
	
					//gather all the service plans that are active
					if($service_plan->is_active() === true){
	
						$return[$service_plan->get_service_plan_id()] = $service_plan;
					}
				}
			}
		}
	
		if (isset($return)) {
			return $return;
		} else {
			return null;
		}
	}
	
	public function get_unused_permanent_installed_equipment()
	{
		if ($this->_unused_permanent_installed_equipment == null) {
	
			$sql = "SELECT em.equipment_map_id, etgm.eq_type_group_id, e.eq_id, e.eq_type_id FROM equipment_mapping em
							LEFT JOIN sales_quote_eq_type_map_equipment_mapping sqetmem ON sqetmem.equipment_map_id=em.equipment_map_id
							INNER JOIN equipments e ON e.eq_id=em.eq_id
							INNER JOIN eq_type_group_mapping etgm ON etgm.eq_type_id=e.eq_type_id
							WHERE em.unit_id='".$this->_unit_id."'
							AND sqetmem.equipment_map_id IS NULL
							AND (em.eq_map_deactivated IS NULL OR em.eq_map_deactivated > NOW())
							AND em.is_permanent_installation='1'
							";
	
			$equipments =  Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
	
			if (isset($equipments['0'])) {
				$i=0;
				foreach ($equipments as $equipment) {
	
					$this->_unused_permanent_installed_equipment[$i]['equipment_map_id'] 	= $equipment['equipment_map_id'];
	
					//eq_type is acceassable throug the equipment object
					$this->_unused_permanent_installed_equipment[$i]['equipment'] 			= new Thelist_Model_equipments($equipment['eq_id']);
					$this->_unused_permanent_installed_equipment[$i]['equipment_group'] 	= new Thelist_Model_equipmenttypegroup($equipment['eq_type_group_id']);
				}
				$i++;
			}
		}
	
		return $this->_unused_permanent_installed_equipment;
			
	}
	
	public function add_service_plan_map($service_plan_id)
	{
		if (is_numeric($service_plan_id)) {
	
			//always get all service plans
			$this->get_mapped_service_plans();
	
			if($this->_mapped_service_plans != null) {
					
				foreach($this->_mapped_service_plans as $service_plan){
						
					//is the service plan already mapped?
					if ($service_plan->get_service_plan_id() == $service_plan_id) {
							
						//service plan is already mapped so we just return the existing one
						return $service_plan;
					}
				}
			}
	
			//if we dident find an existing service plan we instaciate the new and make a few checks that it is valid.
			$new_service_plan_obj =  new Thelist_Model_Serviceplan($service_plan_id);
	
			//is the new service plan active?
			if ($new_service_plan_obj->is_active() === true) {
					
				//trace parameters
				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);
					
				//it is active so we map it.
				$data = array(
												'service_plan_id'  => $service_plan_id,
												'unit_id'		   => $this->_unit_id
				);
					
				//insert
				Zend_Registry::get('database')->insert_single_row('unit_service_plan_mapping', $data, $class, $method);
					
				//append the private var at the top
				$this->_mapped_service_plans[$service_plan_id] = $new_service_plan_obj;
					
				//now return the new sp object
				return $this->_mapped_service_plans[$service_plan_id];
					
			} else {
				throw new exception("you are trying to map an inactive service plan to a unit. You provided service plan id: '".$service_plan_id."' when mapping to unit_id: '".$this->_unit_id."' ", 603);
			}
	
		} else {
			throw new exception("service_plan_id must be numeric, you provided: '".$service_plan_id."' when mapping a new service plan to unit_id: '".$this->_unit_id."' ", 602);
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
						
					$array_tools	= new Thelist_Utility_arraytools();
					$return_array[$private_variable] = $array_tools->convert_mixed_array_to_strings($this->$private_variable);
	
				}
			}
		}
	
		return $return_array;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/*
	
	
	public function get_available_service_technicians($date, $appointment_duration, $startime_increments=15)
	{
		//method should return all start times that are allowed for an appointment of the given duration
		//using $startime_increments i.e. for 15 we can only start on 0, 15, 30, 45 min, duration must be in minutes
		
		throw new exception("not done yet", 605);
		
		$time = new Thelist_Utility_time();
		
		if ($time->is_date_time($date) == 'mysql_date') {
			
			$unit_groups = get_unit_groups('service_tech_area');
			
			if ($unit_groups != null) {
					
				foreach ($unit_groups as $unit_group) {

					$unit_group->get_unit_group_id();
				}
			}

		} else {
			throw new exception("you are trying to get available service techs for unit_id: '".$this->_unit_id."' on date: '".$date."' but you have not supplied a proper format for date, it must be mysql date format", 604);
		}

		return $this->_available_service_technicians;
	}
	
	
	
	*/
	
	
	
	
	
	
	
	
	
	
	
	

// 	public function get_end_user_services($end_user_status=null)
// 	{
// 		if($end_user_status == null) {
	////		find all end users that have been in the unit
// 			$end_user_services	=	Zend_Registry::get('database')->get_end_user_services()->fetchAll('unit_id='.$this->_unit_id);
			
// 		} elseif($end_user_status == 'active') {
			
////			find all end users that have been in the unit and are not yet deactivated
// 			$sql = 	"SELECT * FROM end_user_services
// 					WHERE unit_id='".$this->_unit_id."'
// 					AND created < NOW()
// 					AND deactivated IS NULL
// 					";
			
// 			$end_user_services	=	Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
// 		} elseif($end_user_status == 'inactive') {
			
// 			$sql = 	"SELECT * FROM end_user_services
// 					WHERE unit_id='".$this->_unit_id."'
// 					AND deactivated < NOW()
// 					";
				
// 			$end_user_services	=	Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);

// 		}
		
// 		if (isset($end_user_services['0'])) {
		
// 			$end_users = array();
// 			foreach ($end_user_services as $end_user_service) {
					
// 				$end_users[$end_user_service['end_user_service_id']] = new Thelist_Model_enduserservice($end_user_service['end_user_service_id']);
// 			}
		
// 			return $end_users;
// 		} else {
			
// 			return false;
			
// 		}
		
		
// 	}
	
	
	/*


	public function get_installers($from_datetime=null, $to_datetime=null){
		
			
			$date = $year.'-'.$month.'-'.$date; 	
		
			$all_installers_sql="SELECT u.uid
							     FROM user_unit_group_mapping uugm
							 	 LEFT OUTER JOIN users u ON uugm.user_id = u.uid
							 	 LEFT OUTER JOIN unit_groups ug ON ug.unit_group_id = uugm.unit_group_id
							 	 LEFT  OUTER JOIN items ON ug.unit_group_type = items.item_id
							 	 LEFT OUTER JOIN unit_group_mapping ugm ON ug.unit_group_id = ugm.unit_group_id
							 	 WHERE unit_id=".$this->_unit_id."
							 	 AND DATE(startdatetime) = '".$date."'
							 	 GROUP BY u.uid";
			
			$installer_ids = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($all_installers_sql);
			foreach($installer_ids as $installer_id){
				$this->installer_ids[ $installer_id['uid'] ] = $installer_id['uid'];
			}
		
			return 	$this->installer_ids;
	}
	
	
	
	
	
	
	

	public function set_name($unit_name){
		if($this->name!=$unit_name){
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
			Zend_Registry::get('database')->set_single_attribute($this->_unit_id,'units','unit_name', $unit_name,get_class($this),$method);
			$this->name=$unit_name;
		}
	}
	public function set_number($number){
		if($this->number!=$number){
			$trace = debug_backtrace();
			$method = $trace[0]["function"];
			Zend_Registry::get('database')->set_single_attribute($this->_unit_id,'units','number', $number,get_class($this),$method);
			$this->number=$number;
		}
	}
	public function set_streetnumber($streetnumber){
		if($this->streetnumber!=$streetnumber){
			$trace = debug_backtrace();
			$method = $trace[0]["function"];
				
			Zend_Registry::get('database')->set_single_attribute($this->_unit_id,'units','streetnumber', $streetnumber,get_class($this),$method);
			
			$this->streetnumber=$streetnumber;
		}
		
	}
	public function set_streetname($streetname){
		
		if($this->streetname!=$streetname){
			$trace = debug_backtrace();
			$method = $trace[0]["function"];
			
			Zend_Registry::get('database')->set_single_attribute($this->_unit_id,'units','streetname', $streetname,get_class($this),$method);
			$this->streetname=$streetname;
		}
		
	}
	public function set_streettype($streettype){
		
		if($this->streettype!=$streettype){
			$trace = debug_backtrace();
			$method = $trace[0]["function"];
				
			Zend_Registry::get('database')->set_single_attribute($this->_unit_id,'units','streettype', $streettype,get_class($this),$method);
			$this->streettype=$streettype;
		}
		
	}
	public function set_city($city){
		
		if($this->city!=$city){
			
			$trace = debug_backtrace();
			$method = $trace[0]["function"];
			Zend_Registry::get('database')->set_single_attribute($this->_unit_id,'units','city', $city,get_class($this),$method);
			$this->city=$city;
		}
	}
	public function set_state($state){
	
		if($this->state!=$state){
						
			$trace = debug_backtrace();
			$method = $trace[0]["function"];
			Zend_Registry::get('database')->set_single_attribute($this->_unit_id,'units','state', $state,get_class($this),$method);
			$this->state=$state;
		}
	}
	public function set_zip($zip){
		
		if($this->zip!=$zip){
					
			$trace = debug_backtrace();
			$method = $trace[0]["function"];
			Zend_Registry::get('database')->set_single_attribute($this->_unit_id,'units','zip', $zip,get_class($this),$method);
			$this->zip=$zip;
		}
	}
	public function set_note($note){
		
		if($this->note!=$note){
				
			$trace = debug_backtrace();
			$method = $trace[0]["function"];
			Zend_Registry::get('database')->set_single_attribute($this->_unit_id,'units','note', $note,get_class($this),$method);
		
			$this->note=$note;
		}
	}
	public function set_createdate($create){
		$data= array(
								'create'	=> $create
		);
		Zend_Registry::get('database')->get_units()->update($data,'unit_id='.$this->_unit_id);
		$this->createdate=$createdate;
	}
		
		public function add_homerun($homerun_type_id,$quantity){
			
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
			
			$query = "SELECT unit_homerun_mapping_id,homerun_type_id,unit_id,homerun_type_quantity
					  FROM unit_homerun_mapping
					  WHERE homerun_type_id=".$homerun_type_id."
					  AND unit_id=".$this->_unit_id."
					 ";
 			$homerun_types =  Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($query);
			
			if( count($homerun_types) > 0){
				
				$new_quantity = $homerun_types[0]['homerun_type_quantity'] + $quantity;
				$data = array(
										'homerun_type_quantity' => $new_quantity 
				);
				
				$where= array(
										'homerun_type_id=?'  => $homerun_type_id,
										'unit_id=?'   	     => $this->_unit_id
				);
							
				
				$unit_homerun_mapping =  Zend_Registry::get('database')->get_unit_homerun_mapping()->fetchRow($where);
				
				Zend_Registry::get('database')->set_single_attribute($unit_homerun_mapping['unit_homerun_mapping_id'],
													  'unit_homerun_mapping',
				 									  'homerun_type_quantity',
													  $new_quantity,
													   get_class($this),
													  $method);
				
					
								
			}else{ // no data exist
				$data = array(
							'homerun_type_id' 		=> $homerun_type_id,
							'unit_id'		  		=> $this->_unit_id,
							'homerun_type_quantity'	=> $quantity
							 );
				
				Zend_Registry::get('database')->insert_single_row('unit_homerun_mapping',$data, get_class($this),$method);
				
				
				
			}
		}
		
		public function remove_homerun($homerun_type_id,$quantity){
			
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
			
			$query = "SELECT unit_homerun_mapping_id,homerun_type_id,unit_id,homerun_type_quantity
					  FROM unit_homerun_mapping
					  WHERE homerun_type_id=".$homerun_type_id."
					  AND unit_id=".$this->_unit_id."
					 ";
 			$homerun_types =  Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($query);
			
			if( count($homerun_types) > 0){
					
				$new_quantity = $homerun_types[0]['homerun_type_quantity'] - $quantity;
				
				$where= array(
												'homerun_type_id=?'  => $homerun_type_id,
												'unit_id=?'   	     => $this->_unit_id
				);
				$unit_homerun_mapping =  Zend_Registry::get('database')->get_unit_homerun_mapping()->fetchRow($where);
				$unit_homerun_mapping_id = $unit_homerun_mapping['unit_homerun_mapping_id'];
				
				
				if($new_quantity <= 0){
					Zend_Registry::get('database')->delete_single_row($unit_homerun_mapping_id,'unit_homerun_mapping',get_class($this),$method);
					
				}else{
					Zend_Registry::get('database')->set_single_attribute($unit_homerun_mapping_id,'unit_homerun_mapping','homerun_type_quantity',$new_quantity,get_class($this),$method);
				}
			}
		}
		
		

		
		public function is_service_plan_mapped($service_plan_id){
			
			//always get all service plans
			$this->get_mapped_service_plans();
			
			if( is_numeric($service_plan_id) )
			{
				
				foreach( $this->_mapped_service_plans as $service_plan){
					if( $service_plan->get_id() == $service_plan_id){
						return true;
					}else{
						return false;
					}
				}	
			}else{
				throw new exception("service_plan_id must be numeric, you provided: '".$service_plan_id."' when mapping a new service plan to unit_id: '".$this->_unit_id."' ", 605);
			}
		}
		
		public function remove_service_plan_map($service_plan_id)
		{
			
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
					
			if( is_numeric($service_plan_id) )
			{
				//always get all service plans
				$this->get_mapped_service_plans();
				
				if( isset( $this->_mapped_service_plans[$service_plan_id] ) ){
					$where	= array(
											'service_plan_id=?'  => $service_plan_id,
											'unit_id=?'		   => $this->_unit_id
					);
					
					$unit_service_plan_mapping    = Zend_Registry::get('database')->get_unit_service_plan_mapping()->fetchRow($where);
					$unit_service_plan_mapping_id = $unit_service_plan_mapping['unit_service_plan_mapping_id'];
					
					Zend_Registry::get('database')->delete_single_row($unit_service_plan_mapping_id,'unit_service_plan_mapping',get_class($this),$method);
					unset( $this->_mapped_service_plans[$service_plan_id] );
				
				}else{
					throw new exception("you are attempting to remove unset service_plan_id: ".$service_plan_id."to unit_id:".$this->_unit_id, 606);
				}
				
			}else{
				throw new exception("service_plan_id must be numeric, you provided: '".$service_plan_id."'", 607);
			}
				
		}
		
		public function add_service_point($service_point_id){
			
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
				
			
			$sql = "SELECT COUNT(*)
					FROM unit_service_point_mapping
					WHERE unit_id		=".$this->_unit_id."
					AND service_point_id =".$service_point_id;
			
			$exist = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
				
			if($exist){
				// don't do anything if there's already a service point for this unit
			}else{
				$data = array(
											'service_point_id'  => $service_point_id,
											'unit_id'		    => $this->_unit_id
				);
				
				
				Zend_Registry::get('database')->insert_single_row('unit_service_point_mapping',$data,get_class($this),$method);
				
			
			}
		}
		
		public function remove_service_point($service_point_id){
				
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
			
			$where	= array(
									'service_point_id=?'  => $service_point_id,
									'unit_id=?'		     => $this->_unit_id
			);
			//Zend_Registry::get('database')->get_unit_service_point_mapping()->delete($where);
				
			
			$unit_service_point_mapping    = Zend_Registry::get('database')->get_unit_service_point_mapping()->fetchRow($where);
											 
			$unit_service_point_mapping_id = $unit_service_point_mapping['unit_service_point_mapping_id'];
											   
			Zend_Registry::get('database')->delete_single_row($unit_service_point_mapping_id,'unit_service_point_mapping',get_class($this),$method);
				
		}
		
		public function add_unit_group($unit_group_id){
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
			
			$sql = "SELECT COUNT(*)
					FROM unit_group_mapping
					WHERE unit_id		 =".$this->_unit_id."
					AND unit_group_id =".$unit_group_id;
				
			$exist = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
			if($exist){
				// don't do anything if there's already a unit group for this unit
			}else{
				$data = array(
							'unit_group_id' => $unit_group_id,
							'unit_id'	  => $this->_unit_id
				);
				
				Zend_Registry::get('database')->insert_single_row('unit_group_mapping',$data,get_class($this),$method);
				
			}
			
		}
		
		public function remove_unit_group($unit_group_id){
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
			
			$where	= array(
									'unit_group_id=?' 	=> $unit_group_id,
									'unit_id=?'		    => $this->_unit_id
			);
			
			$unit_group_mapping    = Zend_Registry::get('database')->get_unit_group_mapping()->fetchRow($where);
			$unit_group_mapping_id = $unit_group_mapping['unit_group_mapping_id'];
			Zend_Registry::get('database')->delete_single_row($unit_group_mapping_id,'unit_group_mapping',get_class($this),$method);
			//Zend_Registry::get('database')->get_unit_grp_mapping()->delete($where);
		}
		
		
		public function add_new_endusers(){
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
			
			$data	= array(
								'unit_id'	=>	$this->_unit_id,
								'created'	=>  $this->_time->get_current_date_time()
						   );
			
			return Zend_Registry::get('database')->insert_single_row('end_user_services',$data,get_class($this),$method);
		}
		
*/

		/*
		public function get_internet_service_plans(){
		
		$internet_query="SELECT sp.service_plan_id
		FROM service_plans sp
		LEFT OUTER JOIN service_plan_group_mapping spgm  ON sp.service_plan_id=spgm.service_plan_id
		LEFT OUTER JOIN service_plan_groups spg ON spgm.service_plan_group_id=spg.service_plan_group_id
		LEFT OUTER JOIN unit_service_plan_mapping uspm ON sp.service_plan_id = uspm.service_plan_id
		WHERE spg.service_plan_group_name='Internet'
		AND ((NOW() BETWEEN sp.activate AND sp.deactivate) OR (NOW() > sp.activate AND sp.deactivate IS NULL))
		AND uspm.unit_id=".$this->_unit_id;
		
		$internet_service_plans = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($internet_query);
		
		foreach( $internet_service_plans as $internet_service_plan ){
		$this->internet_service_plans[ $internet_service_plan['service_plan_id'] ] =  new Thelist_Model_Serviceplan( $internet_service_plan['service_plan_id'] );
		}
		
		return $this->internet_service_plans;
		}
		
		
		public function get_directtv_service_plans(){
		$directtv_query="SELECT sp.service_plan_id
		FROM service_plans sp
		LEFT OUTER JOIN service_plan_group_mapping spgm  ON sp.service_plan_id=spgm.service_plan_id
		LEFT OUTER JOIN service_plan_groups spg ON spgm.service_plan_group_id=spg.service_plan_group_id
		LEFT OUTER JOIN unit_service_plan_mapping uspm ON sp.service_plan_id = uspm.service_plan_id
		WHERE spg.service_plan_group_name='TV'
		AND ((NOW() BETWEEN sp.activate AND sp.deactivate) OR (NOW() > sp.activate AND sp.deactivate IS NULL))
		AND uspm.unit_id=".$this->_unit_id;
		
		$directtv_service_plans = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($directtv_query);
		
		foreach($directtv_service_plans as $directtv_service_plan){
		$this->directtv_service_plans[ $directtv_service_plan['service_plan_id'] ] = new Thelist_Model_Serviceplan( $directtv_service_plan['service_plan_id'] );
		}
		return $this->directtv_service_plans;
		}
		
		public function get_phone_service_plans(){
		
		$phone_query="SELECT sp.service_plan_id
		FROM service_plans sp
		LEFT OUTER JOIN service_plan_group_mapping spgm  ON sp.service_plan_id=spgm.service_plan_id
		LEFT OUTER JOIN service_plan_groups spg ON spgm.service_plan_group_id=spg.service_plan_group_id
		LEFT OUTER JOIN unit_service_plan_mapping uspm ON sp.service_plan_id = uspm.service_plan_id
		WHERE spg.service_plan_group_name='Phone'
		AND ((NOW() BETWEEN sp.activate AND sp.deactivate) OR (NOW() > sp.activate AND sp.deactivate IS NULL))
		AND uspm.unit_id=".$this->_unit_id;
			
		
		
		$phone_service_plans = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($phone_query);
		
		
		foreach($phone_service_plans as $phone_service_plan)
		{
		$this->phone_service_plans[ $phone_service_plan['service_plan_id'] ] = new Thelist_Model_Serviceplan( $phone_service_plan['service_plan_id'] );
		}
		return $this->phone_service_plans;
		
		}
		*/
}
?>