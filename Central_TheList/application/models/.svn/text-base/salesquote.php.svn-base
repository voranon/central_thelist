<?php

//exception codes 17100-17199

class thelist_model_salesquote
{
	private $logs;
	private $user_session;
	private $_time;

	private $_sales_quote_id;
	private $_end_user_service_id;
	private $_created;
	private $_last_emailed_date;
	private $_sales_quote_accepted;
	private $_unit_id;
	
	private $_end_user_service=null;
	
	private $_service_plan_quote_maps=null;

	public function __construct($sales_quote_id)
	{
		$this->_sales_quote_id		= $sales_quote_id;

		$this->logs					= Zend_Registry::get('logs');
		$this->user_session			= new Zend_Session_Namespace('userinfo');
		$this->_time				= Zend_Registry::get('time');

		$sql = 	"SELECT * FROM sales_quotes
				WHERE sales_quote_id='".$this->_sales_quote_id."'
				";

		$sales_quote = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

		$this->_end_user_service_id						= $sales_quote['end_user_service_id'];
		$this->_created									= $sales_quote['created'];
		$this->_last_emailed_date						= $sales_quote['last_emailed_date'];
		$this->_sales_quote_accepted					= $sales_quote['sales_quote_accepted'];

		$sql3 = "SELECT u.unit_id
				 FROM sales_quotes sq
				 LEFT OUTER JOIN end_user_services eus ON sq.end_user_service_id = eus.end_user_service_id
				 LEFT OUTER JOIN units u ON eus.unit_id = u.unit_id
				 WHERE sales_quote_id=".$this->_sales_quote_id;
		
		$this->_unit_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql3);

	}

	public function get_end_user_service_id()
	{
		return $this->_end_user_service_id;
	}
	
	public function get_end_user_service()
	{
		if ($this->_end_user_service == null) {
			$this->_end_user_service						= new Thelist_Model_enduserservice($this->_end_user_service_id);
		}
	
		return $this->_end_user_service;
	}
	
	public function get_service_plan_quote_maps()
	{
		if ($this->_service_plan_quote_maps == null) {
			
			$sql2 = 	"SELECT * FROM service_plan_quote_mapping
						WHERE sales_quote_id='".$this->_sales_quote_id."'
						";
			
			$service_plan_quote_maps = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
			
			if (isset($service_plan_quote_maps['0'])) {
				
				foreach ($service_plan_quote_maps as $service_plan_quote_map) {
					
					$this->_service_plan_quote_maps[$service_plan_quote_map['service_plan_quote_map_id']]	= new Thelist_Model_serviceplanquotemap($service_plan_quote_map['service_plan_quote_map_id']);
				}
			}
		}
		return $this->_service_plan_quote_maps;
	}
	
// 	public function get_service_plan_maps()
// 	{
		
// 		if ($this->_service_plan_maps == null) {
			
// 			$sql4=		"SELECT * FROM service_plan_quote_mapping
// 					  	WHERE sales_quote_id=".$this->_sales_quote_id;
			
// 			$service_plans = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql4);
			
// 			if( isset( $service_plans[0] ) ){
					
// 				foreach( $service_plans as $service_plan){
// 					$this->_service_plan_maps[$service_plan['service_plan_id']] = new Thelist_Model_serviceplan($service_plan['service_plan_id']);
// 				}
// 			}
// 		}
// 		return $this->_service_plan_maps;	
// 	}
	
	public function get_service_plan_quote_map($service_plan_quote_map_id)
	{
		$this->get_service_plan_quote_maps();
		
		if (isset($this->_service_plan_quote_maps[$service_plan_quote_map_id])) {
			return $this->_service_plan_quote_maps[$service_plan_quote_map_id];
		} else {
			return false;
		}
	}
	public function get_sales_quote_id()
	{
		return $this->_sales_quote_id;
	}
	
	public function get_unit_id(){
		return $this->_unit_id;
	}
	
	public function add_service_plan_quote_map_from_temp_service_plan($service_plan_temp_quote_map_id)
	{
		$temp_quote_obj = new Thelist_Model_serviceplantempquotemap($service_plan_temp_quote_map_id);
		
		if ($temp_quote_obj->get_end_user_service_id() == $this->_end_user_service_id) {
			
			$new_service_plan_quote_map_obj = $this->add_service_plan_quote_map($temp_quote_obj->get_service_plan_id(), $temp_quote_obj->get_service_plan_temp_quote_actual_mrc_term(), $temp_quote_obj->get_service_plan_temp_quote_actual_mrc(), $temp_quote_obj->get_service_plan_temp_quote_actual_nrc(), $temp_quote_obj->get_service_plan_temp_discount());
			
			//first add all the options and equipment
			
			//add all option maps
			$temp_option_maps	= $temp_quote_obj->get_service_plan_temp_quote_options();
			if ($temp_option_maps != null) {
				
				foreach ($temp_option_maps as $temp_option_map) {

					$new_service_plan_quote_map_obj->add_service_plan_quote_option_map($temp_option_map->get_service_plan_option_map_id(), $temp_option_map->get_service_plan_temp_quote_option_actual_mrc_term(), $temp_option_map->get_service_plan_temp_quote_option_actual_mrc(), $temp_option_map->get_service_plan_temp_quote_option_actual_nrc());
				}
			}
		
			//add all eq type maps
			$temp_eq_type_maps	= $temp_quote_obj->get_service_plan_temp_quote_eq_types();
			if ($temp_eq_type_maps != null) {
			
				foreach ($temp_eq_type_maps as $temp_eq_type_map) {
			
					$new_service_plan_quote_map_obj->add_service_plan_quote_eq_type($temp_eq_type_map->get_service_plan_eq_type_map_id(), $temp_eq_type_map->get_service_plan_temp_quote_eq_type_actual_mrc_term(), $temp_eq_type_map->get_service_plan_temp_quote_eq_type_actual_mrc(), $temp_eq_type_map->get_service_plan_temp_quote_eq_type_actual_nrc());
				}
			}
			
			//then validate that it was done right
			
			//check all temp option maps
			$all_option_maps = $temp_quote_obj->get_service_plan_temp_quote_options();
			if ($all_option_maps != null) {
					
				foreach ($all_option_maps as $option_map) {
						
					$option_result	= $new_service_plan_quote_map_obj->service_plan_option_map_requirement_fulfilled($option_map->get_service_plan_option_map_id());
						
					if ($option_result == 'no') {
						
						$sql = "SELECT * FROM service_plan_quote_option_mapping";
						
						$something = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);

						throw new exception("option map: '".$option_map->get_service_plan_option_map_id()."' requirement not fulfilled service_plan_quote_map_id '".$new_service_plan_quote_map_obj->get_service_plan_id()."'", 17108);
					}
				}
			}
			
			//check all temp equipment maps
			$all_eq_type_maps = $temp_quote_obj->get_service_plan_temp_quote_eq_types();
			if ($all_eq_type_maps != null) {
					
				foreach ($all_eq_type_maps as $eq_type_map) {
						
					$eq_type_result	= $new_service_plan_quote_map_obj->service_plan_eq_type_map_requirement_fulfilled($eq_type_map->get_service_plan_eq_type_map_id());
						
					if ($eq_type_result == 'no') {
						throw new exception("eq type map: '".$eq_type_map->get_service_plan_eq_type_map_id()."' requirement not fulfilled service_plan_temp_quote_map_id '".$temp_quote_obj->get_service_plan_id()."'", 17109);
					}
				}
			}
			
			
		} else {
			throw new exception("you are trying to map service plan temp quote map id: '".$temp_quote_obj->get_service_plan_temp_quote_map_id()."' to sales quote id '".$this->_sales_quote_id."', but the temp is not mapped to the same end user service id as this sales quote", 17107);
		}
	}
	
	public function add_service_plan_quote_map($service_plan_id, $service_plan_quote_actual_mrc_term, $service_plan_quote_actual_mrc, $service_plan_quote_actual_nrc, $service_plan_temp_discount=null){
		
		if ($this->_sales_quote_accepted == 0) {

			if(!is_numeric($service_plan_id)) {
				throw new exception("service_plan_id must be numeric, you provided: '".$service_plan_id."' when mapping a new service plan to end user service id: '".$this->_end_user_service_id."' ", 17101);
			} elseif (!preg_match("/^\d+(\.\d{1,2})?$/", $service_plan_quote_actual_mrc)) {
				throw new exception("MRC amount is not a valid doller amount: '".$service_plan_quote_actual_mrc."' when mapping service plan id: '".$service_plan_id."' to end user service id '".$this->_end_user_service_id."'", 17102);
			} elseif (!preg_match("/^\d+(\.\d{1,2})?$/", $service_plan_quote_actual_nrc)) {
				throw new exception("NRC amount is not a valid doller amount: '".$service_plan_quote_actual_nrc."' when mapping service plan id: '".$service_plan_id."' to end user service id '".$this->_end_user_service_id."'", 17103);
			} elseif (!preg_match("/^[0-9]+$/", $service_plan_quote_actual_mrc_term)) {
				throw new exception("Term, is not a whole number: '".$service_plan_quote_actual_mrc_term."' when mapping service plan id: '".$service_plan_id."' to end user service id '".$this->_end_user_service_id."'", 17104);
			} else {
	
				$active_service_plan_maps = $this->get_end_user_service()->get_unit()->get_active_service_plan_maps();
				
				if(isset($active_service_plan_maps[$service_plan_id])) {
			
					$trace  = debug_backtrace();
					$method = $trace[0]["function"];
					$class		= get_class($this);
					
					$this->get_service_plan_quote_maps();
					
										
					$data = array(
									'service_plan_quote_master_map_id'		=> null,
									'sales_quote_id'						=> $this->_sales_quote_id,
									'service_plan_id'						=> $service_plan_id,
									'service_plan_suspension'    			=> null,
									'service_plan_quote_actual_mrc'			=> $service_plan_quote_actual_mrc,
									'service_plan_quote_actual_nrc'			=> $service_plan_quote_actual_nrc,
									'service_plan_quote_actual_mrc_term'	=> $service_plan_quote_actual_mrc_term,
									'activation'							=> null,
									'deactivation'							=> null,
								 );
		
					$new_service_plan_quote_map_id = Zend_Registry::get('database')->insert_single_row('service_plan_quote_mapping', $data, $class, $method);
					$this->_service_plan_quote_maps[$new_service_plan_quote_map_id] 	= new Thelist_Model_serviceplanquotemap($new_service_plan_quote_map_id);
					
					return $this->_service_plan_quote_maps[$new_service_plan_quote_map_id];
					
				} else {
					throw new exception("when mapping service plan with id: '".$service_plan_id."' to end user service id '".$this->_end_user_service_id."' the unit that the end user occupies is not allowed to have this service plan mapped", 17105);
				}
			}
		} else {
			throw new exception("you are trying to map a service plan quote map to sales quote id '".$this->_sales_quote_id."', but this quote is already accepted, bad, bad,bad", 17106);
		}
	}
	
// 	public function add_service_plan_option($service_plan_quote_map_id,$service_plan_option_map_id,$price){
// 		$trace  = debug_backtrace();
// 		$method = $trace[0]["function"];
		
// 		$n_m = substr($price,-1,1);
		
// 		if($n_m == 'm'){
// 			$mrc		=   intval( substr($price,0,-1) );
// 			$nrc		=	0;
// 			$mrc_term	=	0;
// 		}else if($n_m == 'n'){
// 			$mrc		=   0;
// 			$nrc		=	intval( substr($price,0,-1) );
// 			$mrc_term	=	0;
// 		}
		
		
// 		$data = array(
// 						'service_plan_option_map_id'				=>	$service_plan_option_map_id,
// 						'service_plan_quote_map_id'					=>  $service_plan_quote_map_id,
// 						'service_plan_quote_option_actual_mrc'		=>  $mrc,
// 						'service_plan_quote_option_actual_nrc'		=>  $nrc,
// 						'service_plan_quote_option_actual_mrc_term'	=>  $mrc_term
// 					 );	
// 		return Zend_Registry::get('database')->insert_single_row('service_plan_quote_option_mapping',$data,get_class($this),$method);
// 	}


	
	
// 	public function add_service_plan_equipment($service_plan_quote_map_id,$service_plan_eq_type_map_id,$price){
// 		$trace  = debug_backtrace();
// 		$method = $trace[0]["function"];
		
// 		$n_m = substr($price,-1,1);
		
// 		if($n_m == 'm'){
// 			$mrc		=   intval( substr($price,0,-1) );
// 			$nrc		=	0;
// 			$mrc_term	=	0;
// 		}else if($n_m == 'n'){
// 			$mrc		=   0;
// 			$nrc		=	intval( substr($price,0,-1) );
// 			$mrc_term	=	0;
// 		}
		
					
// 		$data = array(
// 						'service_plan_eq_type_map_id'				=>	$service_plan_eq_type_map_id,
// 						'service_plan_quote_map_id'					=>  $service_plan_quote_map_id,
// 						'service_plan_quote_eq_type_actual_mrc'		=>  $mrc,
// 						'service_plan_quote_eq_type_actual_nrc' 	=>  $nrc,
// 						'service_plan_quote_eq_type_actual_mrc_term'=>  $mrc_term
// 					 );
		
// 		return Zend_Registry::get('database')->insert_single_row('service_plan_quote_eq_type_mapping',$data,get_class($this),$method);
					
// 	}
	
	
	public function get_install_time(){
		
		$sql="SELECT SUM(sp.service_plan_install_required_time)
			  FROM service_plan_quote_mapping spqm
			  LEFT OUTER JOIN service_plans sp ON spqm.service_plan_id = sp.service_plan_id
			  WHERE spqm.sales_quote_id=".$this->_sales_quote_id;
		
		$service_plan_time = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		$sql="SELECT SUM(spom.service_plan_option_additional_install_time)
			  FROM service_plan_quote_mapping spqm
			  LEFT OUTER JOIN service_plan_quote_option_mapping spqom ON spqm.service_plan_quote_map_id = spqom.service_plan_quote_map_id
			  LEFT OUTER JOIN service_plan_option_mapping spom ON spqom.service_plan_option_map_id = spom.service_plan_option_map_id
			  WHERE spqm.sales_quote_id=".$this->_sales_quote_id;
		
		$service_plan_option_time = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		$sql="SELECT SUM(spetm.service_plan_eq_type_additional_install_time)
			  FROM service_plan_quote_mapping spqm
			  LEFT OUTER JOIN service_plan_quote_eq_type_mapping spqetm ON spqm.service_plan_quote_map_id = spqetm.service_plan_quote_map_id
			  LEFT OUTER JOIN service_plan_eq_type_mapping spetm ON spqetm.service_plan_eq_type_map_id = spetm.service_plan_eq_type_map_id
			  WHERE spqm.sales_quote_id=".$this->_sales_quote_id;
		
		$service_plan_eq_time = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		return $service_plan_time+$service_plan_option_time+$service_plan_eq_time;
	}
	
	public function set_sales_quote_accepted($new_status)
	{
		if ($new_status == 1 || $new_status == 0) {
			if ($this->_sales_quote_accepted != $new_status) {
			
				$return = Zend_Registry::get('database')->set_single_attribute($this->_sales_quote_id, 'sales_quotes', 'sales_quote_accepted', $new_status);
				$this->_sales_quote_accepted = $new_status;
			}
		} else {
			throw new exception("this sales quote accepted value is not acceptable ".$new_status." ", 17100);
		}
	}
	
	public function get_sales_quote_accepted()
	{	
		return $this->_sales_quote_accepted;
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