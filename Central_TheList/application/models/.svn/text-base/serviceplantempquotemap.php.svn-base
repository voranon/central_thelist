<?php
// created by non 9/25/20012
// exception number 100100-100199

class thelist_model_serviceplantempquotemap
{
	
	private $user_session;
	
	/////////
	private $_service_plan_temp_quote_map_id=null;
	private $_service_plan_id=null;
	private $_service_plan_temp_quote_option_mappings=null;
	private $_service_plan_temp_quote_eq_type_mappings=null;
	private $_end_user_service_id=null;
	
	private $_service_plan_temp_quote_actual_mrc=null;
	private $_service_plan_temp_quote_actual_nrc=null;
	private $_service_plan_temp_quote_actual_mrc_term=null;
	private $_service_plan_temp_discount=null;
	
	private $_service_plan=null;

	public function __construct($service_plan_temp_quote_map_id)
	{
		$this->user_session								= new Zend_Session_Namespace('userinfo');

		$sql="SELECT * FROM service_plan_temp_quote_mapping
			  WHERE service_plan_temp_quote_map_id = ".$service_plan_temp_quote_map_id;
		
		
		$service_plan_temp_quote = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		
		$this->_service_plan_temp_quote_map_id 			= $service_plan_temp_quote_map_id;
		$this->_end_user_service_id						= $service_plan_temp_quote['end_user_service_id'];
		$this->_service_plan_id							= $service_plan_temp_quote['service_plan_id'];
		$this->_service_plan_temp_quote_actual_mrc       = $service_plan_temp_quote['service_plan_temp_quote_actual_mrc'];
		$this->_service_plan_temp_quote_actual_nrc       = $service_plan_temp_quote['service_plan_temp_quote_actual_nrc'];
		$this->_service_plan_temp_quote_actual_mrc_term  = $service_plan_temp_quote['service_plan_temp_quote_actual_mrc_term'];
		$this->_service_plan_temp_discount				= $service_plan_temp_quote['service_plan_temp_discount'];
			
	}
	
	public function get_service_plan()
	{
		if ($this->_service_plan == null) {
			$this->_service_plan		= new Thelist_Model_serviceplan($this->_service_plan_id);
		}
		return $this->_service_plan;
	}
	
	
	public function get_service_plan_temp_quote_map_id()
	{
		return $this->_service_plan_temp_quote_map_id;
	}
	
	public function get_end_user_service_id()
	{
		return $this->_end_user_service_id;
	}
	
	public function get_service_plan_id()
	{
		return $this->_service_plan_id;	
	}
	
	public function get_service_plan_temp_quote_actual_mrc()
	{
		return $this->_service_plan_temp_quote_actual_mrc;
	}
	
	public function get_service_plan_temp_quote_actual_nrc()
	{
		return $this->_service_plan_temp_quote_actual_nrc;
	}
	
	public function get_service_plan_temp_quote_actual_mrc_term()
	{
		return $this->_service_plan_temp_quote_actual_mrc_term;
	}
	
	public function get_service_plan_temp_discount()
	{
		return $this->_service_plan_temp_discount;
	}
	
	
	/////////////////////////////////////////////////////////
	//options
	
	public function get_service_plan_temp_quote_options()
	{
		if ($this->_service_plan_temp_quote_option_mappings == null) {
			//add all the service plan options
			$sql2 =  "SELECT * FROM service_plan_temp_quote_option_mapping
						  WHERE service_plan_temp_quote_map_id ='".$this->_service_plan_temp_quote_map_id."'";
				
				
			$service_plan_temp_quote_option_mappings = Zend_Registry:: get('database')->get_thelist_adapter()->fetchAll($sql2);
				
			if (isset($service_plan_temp_quote_option_mappings['0'])) {
	
				foreach ($service_plan_temp_quote_option_mappings as $service_plan_temp_quote_option_mapping) {
	
					$serviceplan_quote_option_obj = new Thelist_Model_serviceplantempquoteoptionmap( $service_plan_temp_quote_option_mapping['service_plan_temp_quote_option_map_id'] );
					$this->_service_plan_temp_quote_option_mappings[ $service_plan_temp_quote_option_mapping['service_plan_temp_quote_option_map_id'] ] = $serviceplan_quote_option_obj;
						
				}
			}
		}
			
		return $this->_service_plan_temp_quote_option_mappings;
	}
	
	public function get_service_plan_temp_quote_option($service_plan_temp_quote_option_map_id)
	{
		$this->get_service_plan_temp_quote_options();
		
		if (isset($this->_service_plan_temp_quote_option_mappings[$service_plan_temp_quote_option_map_id])) {
			return $this->_service_plan_temp_quote_option_mappings[$service_plan_temp_quote_option_map_id];
		} else {
			return false;
		}
	}
	
	public function add_service_plan_temp_quote_option($service_plan_option_map_id, $service_plan_temp_quote_option_actual_mrc_term, $service_plan_temp_quote_option_actual_mrc=null, $service_plan_temp_quote_option_actual_nrc=null)
	{
		
		$service_plan_option_map_obj = new Thelist_Model_serviceplanoptionmap($service_plan_option_map_id);
			
		//is the option map made for the same service plan id as this temp object is using
		if ($service_plan_option_map_obj->get_service_plan_id() != $this->_service_plan_id) {
			throw new exception("the service plan option map id: '".$service_plan_option_map_obj->get_service_plan_option_map_id()."' does not belong to the same service plan as this temp service plan id '".$this->_service_plan_temp_quote_map_id."'", 100103);
		} else {

			//get the max map count for the sp option map
			$fulfilled = $this->service_plan_option_map_requirement_fulfilled($service_plan_option_map_id);
			
			//as long as we are not at max
			if ($fulfilled == 'no' || $fulfilled == 'yes') {
				
				if ($service_plan_temp_quote_option_actual_mrc != null && $service_plan_temp_quote_option_actual_nrc != null) {
					throw new exception("mapping service plan option map id: '".$service_plan_option_map_obj->get_service_plan_option_map_id()."' to service plan temp quote map id '".$this->_service_plan_temp_quote_map_id."', currently we dont allow both nrc and mrc for the same option, this may change", 100104);
				} elseif ($service_plan_temp_quote_option_actual_mrc != null && $service_plan_temp_quote_option_actual_nrc == null) {
					
					//validate that the mrc is a valid doller amount
					if (!preg_match("/^\d+(\.\d{1,2})?$/", $service_plan_temp_quote_option_actual_mrc)) {
						throw new exception("MRC amount is not a valid doller amount: '".$service_plan_temp_quote_option_actual_mrc."' .when mapping service plan option map id: '".$service_plan_option_map_id."' to service plan temp quote map id '".$this->_service_plan_temp_quote_map_id."'", 100105);
					}
					
				} elseif ($service_plan_temp_quote_option_actual_mrc == null && $service_plan_temp_quote_option_actual_nrc != null) {
					
					//validate that the nrc is a valid doller amount
					if (!preg_match("/^\d+(\.\d{1,2})?$/", $service_plan_temp_quote_option_actual_nrc)) {
						throw new exception("NRC amount is not a valid doller amount: '".$service_plan_temp_quote_option_actual_nrc."' .when mapping service plan option map id: '".$service_plan_option_map_id."' to service plan temp quote map id '".$this->_service_plan_temp_quote_map_id."'", 100106);
					}
					
				}
				
				//validate that term is a whole number
				if (!preg_match("/^[0-9]+$/", $service_plan_temp_quote_option_actual_mrc_term)) {
					throw new exception("Term whole number: '".$service_plan_temp_quote_option_actual_mrc_term."' .when mapping service plan option map id: '".$service_plan_option_map_id."' to service plan temp quote map id '".$this->_service_plan_temp_quote_map_id."'", 100107);
				}
				
				
				//after all that we can now add the new option
				$data = array(
				                     'service_plan_option_map_id'                  		                 =>  $service_plan_option_map_obj->get_service_plan_option_map_id(),
				                     'service_plan_temp_quote_map_id'                                    =>  $this->_service_plan_temp_quote_map_id,
				                     'service_plan_temp_quote_option_actual_mrc'                         =>  $service_plan_temp_quote_option_actual_mrc,
				                     'service_plan_temp_quote_option_actual_nrc'                         =>  $service_plan_temp_quote_option_actual_nrc,
				                     'service_plan_temp_quote_option_actual_mrc_term'                    =>  $service_plan_temp_quote_option_actual_mrc_term,
				);
				
				$trace	= debug_backtrace();
				$method	= $trace[0][ "function"];
				$class	= get_class($this);
				
				$new_service_plan_temp_quote_option_map_id = Zend_Registry:: get('database')->insert_single_row('service_plan_temp_quote_option_mapping', $data, $class, $method);

				//append it to the top
				$service_plan_temp_quote_option_map_obj = new Thelist_Model_serviceplantempquoteoptionmap($new_service_plan_temp_quote_option_map_id);
				$this->_service_plan_temp_quote_option_mappings[$new_service_plan_temp_quote_option_map_id] = $service_plan_temp_quote_option_map_obj;
				
				//return the new object
				return $service_plan_temp_quote_option_map_obj;
				
			} else {
				throw new exception("you cannot map service_plan_option_map_id: '".$service_plan_option_map_obj->get_service_plan_option_map_id()."' to temp service plan id '".$this->_service_plan_temp_quote_map_id."'. adding it would exceed the max allowed maps", 100109);
			}
		}
	}
	
	public function remove_service_plan_temp_quote_option($service_plan_temp_quote_option_map_id)
	{	
		if ($this->get_service_plan_temp_quote_option($service_plan_temp_quote_option_map_id) != false) {
			
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
				
			//delete in database
			Zend_Registry::get('database')->delete_single_row($service_plan_temp_quote_option_map_id, 'service_plan_temp_quote_option_mapping', $class, $method);
			
			//unset from class
			unset($this->_service_plan_temp_quote_option_mappings[$service_plan_temp_quote_option_map_id]);
			
		} else {
			throw new exception("you are trying to remove an service_plan_temp_quote_option: '".$service_plan_temp_quote_option_map_id."' from service_plan_temp_quote_map_id: '".$this->_service_plan_temp_quote_map_id."', but that option is not mapped to this service_plan_temp_quote_map, bad, bad, bad", 100119);
		}
	}
	
	public function service_plan_option_map_requirement_fulfilled($service_plan_option_map_id)
	{
		
		$service_plan_option_map_obj = new Thelist_Model_serviceplanoptionmap($service_plan_option_map_id);
		
		$all_option_maps		= $this->get_service_plan()->get_service_plan_option_maps();
		$current_option_maps	= $this->get_service_plan_temp_quote_options();

		if(isset($all_option_maps[$service_plan_option_map_obj->get_service_plan_option_map_id()])) {
			
			$option_group_obj	= $all_option_maps[$service_plan_option_map_obj->get_service_plan_option_map_id()]->get_service_plan_option_group();
				
			$minimum_count		= $option_group_obj->get_service_plan_option_required_quantity();
			$maximum_count		= $option_group_obj->get_service_plan_option_max_quantity();
			
			$current_map_count = 0;
			if ($current_option_maps != null) {

				foreach ($current_option_maps as $current_option_map) {
					
					if ($service_plan_option_map_obj->get_service_plan_option_map_id() == $current_option_map->get_service_plan_option_map_id()) {
						$current_map_count++;
					}
				}
			}

			//we need 3 returns: yes, max reached and no
			//would love to use a boolan, but not possible unless null is used. 
			//then type matching may become an issue, when we forget to do a strict check
			
			if ($maximum_count == $current_map_count) {
				//report max before trying 'yes'
				return 'max';
			} elseif ($minimum_count > $current_map_count) {
				return 'no';
			} elseif ($maximum_count < $current_map_count) {
				throw new exception("service_plan_option_map_id: '".$service_plan_option_map_obj->get_service_plan_option_map_id()."' to temp service plan id '".$this->_service_plan_temp_quote_map_id."'. too many maps, we have a problem, there is a second input function somewhere", 100113);
			} elseif ($maximum_count > $current_map_count && $minimum_count <= $current_map_count) {
				return 'yes';
			} else {
				throw new exception("hmm, i thought i caovered all eventuallities", 100115);
			}

		} else {
			throw new exception("testing if service_plan_option_map_id: '".$service_plan_option_map_obj->get_service_plan_option_map_id()."' to temp service plan id '".$this->_service_plan_temp_quote_map_id."'. requirement is fulfilled, but the service plan does not have this option mapped", 100120);
		}
	}

	/////////////////////////////////////////////////////////
	// equipments
	public function get_service_plan_temp_quote_eq_types()
	{
		if ($this->_service_plan_temp_quote_eq_type_mappings == null) {
			//add all the service plan eq types
			$sql2 =  	"SELECT * FROM service_plan_temp_quote_eq_type_mapping
						WHERE service_plan_temp_quote_map_id ='".$this->_service_plan_temp_quote_map_id."'";
	
	
			$service_plan_temp_quote_eq_type_mappings = Zend_Registry:: get('database')->get_thelist_adapter()->fetchAll($sql2);
	
			if (isset($service_plan_temp_quote_eq_type_mappings['0'])) {
	
				foreach ($service_plan_temp_quote_eq_type_mappings as $service_plan_temp_quote_eq_type_map) {
	
					$serviceplan_quote_temp_eq_type_obj = new Thelist_Model_serviceplantempquoteeqtypemap($service_plan_temp_quote_eq_type_map['service_plan_temp_quote_eq_type_map_id'] );
					$this->_service_plan_temp_quote_eq_type_mappings[$service_plan_temp_quote_eq_type_map['service_plan_temp_quote_eq_type_map_id']] = $serviceplan_quote_temp_eq_type_obj;
	
				}
			}
		}
			
		return $this->_service_plan_temp_quote_eq_type_mappings;
	}
	
	public function get_service_plan_temp_quote_eq_type($service_plan_temp_quote_eq_type_map_id)
	{
		$this->get_service_plan_temp_quote_eq_types();
	
		if (isset($this->_service_plan_temp_quote_eq_type_mappings[$service_plan_temp_quote_eq_type_map_id])) {
			return $this->_service_plan_temp_quote_eq_type_mappings[$service_plan_temp_quote_eq_type_map_id];
		} else {
			return false;
		}
	}
	
	public function add_service_plan_temp_quote_eq_type($service_plan_eq_type_map_id, $service_plan_temp_quote_eq_type_actual_mrc_term, $service_plan_temp_quote_eq_type_actual_mrc=null, $service_plan_temp_quote_eq_type_actual_nrc=null)
	{
	
		$service_plan_eq_type_map_obj = new Thelist_Model_serviceplaneqtypemap($service_plan_eq_type_map_id);
			
		//is the eq_type map made for the same service plan id as this temp object is using
		if ($service_plan_eq_type_map_obj->get_service_plan_id() != $this->_service_plan_id) {
			throw new exception("the service plan eq type map id: '".$service_plan_eq_type_map_obj->get_service_plan_eq_type_map_id()."' does not belong to the same service plan as this temp service plan id '".$this->_service_plan_temp_quote_map_id."'", 100110);
		} else {

			//get the max map count for the sp eq type map
			$fulfilled = $this->service_plan_eq_type_map_requirement_fulfilled($service_plan_eq_type_map_id);
			//as long as we are not at max
			if ($fulfilled == 'no' || $fulfilled == 'yes') {
				
				if ($service_plan_temp_quote_eq_type_actual_mrc != null && $service_plan_temp_quote_eq_type_actual_nrc != null) {

					throw new exception("mapping service plan option map id: '".$service_plan_eq_type_map_id."' to service plan temp quote map id '".$this->_service_plan_temp_quote_map_id."', currently we dont allow both nrc and mrc for the same eq type, this may change", 100111);
				
				} elseif ($service_plan_temp_quote_eq_type_actual_mrc != null && $service_plan_temp_quote_eq_type_actual_nrc == null) {
					
					//validate that the mrc is a valid doller amount
					if (!preg_match("/^\d+(\.\d{1,2})?$/", $service_plan_temp_quote_eq_type_actual_mrc)) {
						throw new exception("MRC amount is not a valid doller amount: '".$service_plan_temp_quote_eq_type_actual_mrc."' .when mapping service plan eq type map id: '".$service_plan_eq_type_map_id."' to service plan temp quote map id '".$this->_service_plan_temp_quote_map_id."'", 100112);
					}
					
				} elseif ($service_plan_temp_quote_eq_type_actual_mrc == null && $service_plan_temp_quote_eq_type_actual_nrc != null) {
					
					//validate that the nrc is a valid doller amount
					if (!preg_match("/^\d+(\.\d{1,2})?$/", $service_plan_temp_quote_eq_type_actual_nrc)) {
						throw new exception("NRC amount is not a valid doller amount: '".$service_plan_temp_quote_eq_type_actual_nrc."' .when mapping service plan eq type map id: '".$service_plan_eq_type_map_id."' to service plan temp quote map id '".$this->_service_plan_temp_quote_map_id."'", 100121);
					}
					
				}
				
				//validate that term is a whole number
				if (!preg_match("/^[0-9]+$/", $service_plan_temp_quote_eq_type_actual_mrc_term)) {
					throw new exception("Term whole number: '".$service_plan_temp_quote_eq_type_actual_mrc_term."' .when mapping service plan eq type map id: '".$service_plan_eq_type_map_id."' to service plan temp quote map id '".$this->_service_plan_temp_quote_map_id."'", 100114);
				}
				
				
				//after all that we can now add the new option
				$data = array(
				                     'service_plan_eq_type_map_id'                  		             =>  $service_plan_eq_type_map_obj->get_service_plan_eq_type_map_id(),
				                     'service_plan_temp_quote_map_id'                                    =>  $this->_service_plan_temp_quote_map_id,
				                     'service_plan_temp_quote_eq_type_actual_mrc'                        =>  $service_plan_temp_quote_eq_type_actual_mrc,
				                     'service_plan_temp_quote_eq_type_actual_nrc'                        =>  $service_plan_temp_quote_eq_type_actual_nrc,
				                     'service_plan_temp_quote_eq_type_actual_mrc_term'                   =>  $service_plan_temp_quote_eq_type_actual_mrc_term,
				);
				
				$trace	= debug_backtrace();
				$method	= $trace[0][ "function"];
				$class	= get_class($this);
				
				$new_service_plan_temp_quote_eq_type_map_id = Zend_Registry:: get('database')->insert_single_row('service_plan_temp_quote_eq_type_mapping', $data, $class, $method);

				//append it to the top
				$serviceplan_quote_temp_eq_type_obj = new Thelist_Model_serviceplantempquoteeqtypemap($new_service_plan_temp_quote_eq_type_map_id);
				$this->_service_plan_temp_quote_eq_type_mappings[$new_service_plan_temp_quote_eq_type_map_id] = $serviceplan_quote_temp_eq_type_obj;
	
				//return the new object
				return $serviceplan_quote_temp_eq_type_obj;
				
			} else {
				throw new exception("you cannot map service_plan_eq_type_map_id: '".$service_plan_eq_type_map_id."' to temp service plan id '".$this->_service_plan_temp_quote_map_id."'. adding it would exceed the max allowed maps", 100122);
			}
		}
	}
	
	public function remove_service_plan_temp_quote_eq_type($service_plan_temp_quote_eq_type_map_id)
	{
		if ($this->get_service_plan_temp_quote_eq_type($service_plan_temp_quote_eq_type_map_id) != false) {
				
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
	
			//delete in database
			Zend_Registry::get('database')->delete_single_row($service_plan_temp_quote_eq_type_map_id, 'service_plan_temp_quote_eq_type_mapping', $class, $method);
				
			//unset from class
			unset($this->_service_plan_temp_quote_eq_type_mappings[$service_plan_temp_quote_eq_type_map_id]);
				
		} else {
			throw new exception("you are trying to remove an service_plan_temp_quote_eq_type from service_plan_temp_quote_map_id '".$this->_service_plan_temp_quote_map_id."' but that eq_type is not mapped to this service_plan_temp_quote_map, bad, bad, bad", 100123);
		}
	}
	
	public function service_plan_eq_type_map_requirement_fulfilled($service_plan_eq_type_map_id)
	{
		$service_plan_eq_type_map_obj = new Thelist_Model_serviceplaneqtypemap($service_plan_eq_type_map_id);
	
		$all_eq_type_maps		= $this->get_service_plan()->get_service_plan_eq_type_maps();
		$current_eq_type_maps	= $this->get_service_plan_temp_quote_eq_types();
	
		if(isset($all_eq_type_maps[$service_plan_eq_type_map_obj->get_service_plan_eq_type_map_id()])) {
				
			$eq_type_group_obj	= $all_eq_type_maps[$service_plan_eq_type_map_obj->get_service_plan_eq_type_map_id()]->get_service_plan_eq_type_group();
	
			$minimum_count		= $eq_type_group_obj->get_service_plan_eq_type_required_quantity();
			$maximum_count		= $eq_type_group_obj->get_service_plan_eq_type_max_quantity();
				
			$current_map_count = 0;
			if ($current_eq_type_maps != null) {
	
				foreach ($current_eq_type_maps as $current_eq_type_map) {
						
					if ($current_eq_type_map->get_service_plan_eq_type_map_id() == $current_eq_type_map->get_service_plan_eq_type_map_id()) {
						$current_map_count++;
					}
				}
			}
	
			//we need 3 returns: yes, max reached and no
			//would love to use a boolan, but not possible unless null is used.
			//then type matching may become an issue, when we forget to do a strict check
				
			if ($maximum_count == $current_map_count) {
				//report max before trying 'yes'
				return 'max';
			} elseif ($minimum_count > $current_map_count) {
				return 'no';
			} elseif ($maximum_count < $current_map_count) {
				throw new exception("service_plan_eq_type_map_id: '".$service_plan_eq_type_map_id."' to temp service plan id '".$this->_service_plan_temp_quote_map_id."'. too many maps, we have a problem, there is a second input function somewhere", 100116);
			} elseif ($maximum_count > $current_map_count && $minimum_count <= $current_map_count) {
				return 'yes';
			} else {
				throw new exception("hmm, i thought i caovered all eventuallities", 100118);
			}
	
		} else {
			throw new exception("testing if service_plan_eq_type_map_id: '".$service_plan_eq_type_map_id."' to temp service plan id '".$this->_service_plan_temp_quote_map_id."'. requirement is fulfilled, but the service plan does not have this eq type mapped", 100117);
		}
	}
	
	/////////////////////////////////////////////////////////
// 	public function set_service_plan_temp_quote_master_map_id($new_service_plan_temp_quote_master_map_id){
		
// 		if ( $this->service_plan_temp_quote_master_map_id != $new_service_plan_temp_quote_master_map_id ) {
			
// 			Zend_Registry::get('database')->set_single_attribute($this->_service_plan_temp_quote_map_id, 'service_plan_temp_quote_mapping', 'service_plan_temp_quote_master_map_id', service_plan_temp_quote_master_map_id);
// 			$this->service_plan_temp_quote_master_map_id = $new_service_plan_temp_quote_master_map_id;
			
// 		}
		
// 		return true;
		
// 	}
	
// 	public function set_end_user_service_id($new_end_user_service_id){
				
// 		if ( $this->_end_user_service_id != $new_end_user_service_id ) {
			
// 			Zend_Registry::get('database')->set_single_attribute($this->_service_plan_temp_quote_map_id, 'service_plan_temp_quote_mapping', 'end_user_service_temp_id', $new_end_user_service_temp_id);
// 			$this->end_user_service_temp_id = $new_end_user_service_temp_id;
						
// 		}
	
// 		return true;
// 	}
	
// 	public function set_service_plan_temp_id($new_service_plan_temp_id){
		
		
// 		if ($this->service_plan_temp_id != $new_service_plan_temp_id ) {
// 			$return = Zend_Registry::get('database')->set_single_attribute($this->_service_plan_temp_quote_map_id, 'service_plan_temp_quote_mapping', 'service_plan_temp_id', $new_service_plan_temp_id);
// 			$this->service_plan_temp_id = $new_service_plan_temp_id;	
// 		}
	
// 		return true;
	
// 	}
	
// 	public function set_service_plan_temp_suspension($new_service_plan_temp_suspension){
		
		
// 		if ( $this->service_plan_temp_suspension != $new_service_plan_temp_suspension ){
// 			$return = Zend_Registry::get('database')->set_single_attribute($this->_service_plan_temp_quote_map_id, 'service_plan_temp_quote_mapping', 'service_plan_temp_suspension', $new_service_plan_temp_suspension);
// 			$this->service_plan_temp_suspension = $new_service_plan_temp_suspension;
// 		}
	
// 		return true;
	
// 	}	
	
// 	public function set_service_plan_temp_quote_actual_mrc($new_service_plan_temp_quote_actual_mrc){
		
		
// 		if ($this->_service_plan_temp_quote_actual_mrc != $new_service_plan_temp_quote_actual_mrc){
			
// 			Zend_Registry::get('database')->set_single_attribute($this->_service_plan_temp_quote_map_id, 'service_plan_temp_quote_mapping', 'service_plan_temp_quote_actual_mrc', $new_service_plan_temp_quote_actual_mrc);
// 			$this->_service_plan_temp_quote_actual_mrc = $new_service_plan_temp_quote_actual_mrc;
			
// 		}
	
// 		return true;
	
// 	}
	
// 	public function set_service_plan_temp_quote_actual_nrc($new_service_plan_temp_quote_actual_nrc){
		
// 		if ($this->_service_plan_temp_quote_actual_nrc != $new_service_plan_temp_quote_actual_nrc) {
			
// 			Zend_Registry::get('database')->set_single_attribute($this->_service_plan_temp_quote_map_id, 'service_plan_temp_quote_mapping', 'service_plan_temp_quote_actual_nrc', $new_service_plan_temp_quote_actual_nrc);
// 			$this->_service_plan_temp_quote_actual_nrc = $new_service_plan_temp_quote_actual_nrc; 			
// 		}
	
// 		return true;
	
// 	}
	
// 	public function set_service_plan_temp_quote_actual_mrc_term($new_service_plan_temp_quote_actual_mrc_term){
		
// 		if ($this->_service_plan_temp_quote_actual_mrc_term != $new_service_plan_temp_quote_actual_mrc_term) {
			
// 			Zend_Registry::get('database')->set_single_attribute($this->_service_plan_temp_quote_map_id, 'service_plan_temp_quote_mapping', 'service_plan_temp_quote_actual_mrc_term', $new_service_plan_temp_quote_actual_mrc_term);
// 			$this->_service_plan_temp_quote_actual_mrc_term = $new_service_plan_temp_quote_actual_mrc_term;
			 			
// 		}
	
// 		return true;
	
// 	}
	
// 	public function set_service_plan_temp_discount($new_service_plan_temp_discount){
		
// 		if ($this->_service_plan_temp_discount != $new_service_plan_temp_discount) {
			
// 			Zend_Registry::get('database')->set_single_attribute($this->_service_plan_temp_quote_map_id, 'service_plan_temp_quote_mapping', 'service_plan_temp_discount', $new_service_plan_temp_discount);
// 			$this->_service_plan_temp_discount = $new_service_plan_temp_discount;
			
// 		}
	
// 		return true;
	
// 	}
	
}