<?php

//exception codes 2200-2299

class thelist_model_serviceplanquotemap
{
	

	private $database;
	private $logs;
	private $user_session;
	private $_time;
	
	private $_service_plan_quote_map_id;
	private $_service_plan_quote_master_map_id;
	private $_sales_quote_id;
	private $_service_plan_id;
	private $_service_plan_suspension;
	private $_service_plan_quote_actual_mrc;
	private $_service_plan_quote_actual_nrc;
	private $_service_plan_quote_actual_mrc_term;
	private $_activation;
	private $_deactivation;
	
	private $_service_plan_quote_option_maps=null;
	private $_service_plan_quote_eq_type_maps=null;
	private $_service_plan=null;
	private $_service_plan_quote_tasks_map=null;
	private $_service_plan_quote_ip_address_maps=null;
	private $_service_plan_quote_connection_queue_filter_maps=null;
	

	public function __construct($service_plan_quote_map_id)
	{
		$this->_service_plan_quote_map_id				= $service_plan_quote_map_id;	

		$this->logs										= Zend_Registry::get('logs');
		$this->user_session								= new Zend_Session_Namespace('userinfo');
		$this->_time									= Zend_Registry::get('time');
		
		$sql=	"SELECT * FROM service_plan_quote_mapping
				WHERE service_plan_quote_map_id='".$this->_service_plan_quote_map_id."'
				";
		
		$service_plan_quote = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		$this->_service_plan_quote_master_map_id		= $service_plan_quote['service_plan_quote_master_map_id'];
		$this->_sales_quote_id							= $service_plan_quote['sales_quote_id'];
		$this->_service_plan_id							= $service_plan_quote['service_plan_id'];
		$this->_service_plan_suspension					= $service_plan_quote['service_plan_suspension'];
		$this->_service_plan_quote_actual_mrc			= $service_plan_quote['service_plan_quote_actual_mrc'];
		$this->_service_plan_quote_actual_nrc			= $service_plan_quote['service_plan_quote_actual_nrc'];
		$this->_service_plan_quote_actual_mrc_term		= $service_plan_quote['service_plan_quote_actual_mrc_term'];
		$this->_activation								= $service_plan_quote['activation'];
		$this->_deactivation							= $service_plan_quote['deactivation'];
		
	}
	
	
	public function set_activation($activation_date, $override=false)
	{
		//once a service plan has been actvated it is because it is live in the field
		//after that it should never be touched.
		
		if ($this->_activation == null || $override != false) {
			
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
			
			Zend_Registry::get('database')->set_single_attribute($this->_service_plan_quote_map_id, 'service_plan_quote_mapping', 'activation', $activation_date,$class,$method);
			
		} else {
			throw new exception("you are attempting to update the activation date without override for service_plan_quote_map_id: ".$this->_service_plan_quote_map_id.", but the activation is already set", 2202);
		}
	}
	
	public function get_service_plan_quote_map_id()
	{
		return $this->_service_plan_quote_map_id;
	}
	public function get_sales_quote_id()
	{
		return $this->_sales_quote_id;
	}
	public function get_service_plan()
	{
		if ($this->_service_plan == null) {
			$this->_service_plan		= new Thelist_Model_serviceplan($this->_service_plan_id);
		}
		
		return $this->_service_plan;	
	}
	public function get_service_plan_suspension()
	{
		return $this->_service_plan_suspension;
	}
	public function get_service_plan_quote_actual_mrc()
	{
		return $this->_service_plan_quote_actual_mrc;
	}
	public function get_service_plan_quote_actual_nrc()
	{
		return $this->_service_plan_quote_actual_nrc;
	}
	public function get_service_plan_quote_actual_mrc_term()
	{
		return $this->_service_plan_quote_actual_mrc_term;
	}
	public function get_activation()
	{
		return $this->_activation;
	}
	public function get_deactivation()
	{
		return $this->_deactivation;
	}
	public function get_service_plan_quote_option_maps()
	{
		if ($this->_service_plan_quote_option_maps == null) {
			//add all the service plan options
			$sql2 = 	"SELECT * FROM service_plan_quote_option_mapping
						WHERE service_plan_quote_map_id='".$this->_service_plan_quote_map_id."'
						";
			
			$service_plan_quote_option_maps = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
			
			if (isset($service_plan_quote_option_maps['0'])) {
				
				foreach ($service_plan_quote_option_maps as $service_plan_quote_option_map) {	
					
					$serviceplan_quote_option_map_obj = new Thelist_Model_serviceplanquoteoptionmap($service_plan_quote_option_map['service_plan_quote_option_map_id']);
					$this->_service_plan_quote_option_maps[$service_plan_quote_option_map['service_plan_quote_option_map_id']] = $serviceplan_quote_option_map_obj;
			
				}
			}
		}
		
		return $this->_service_plan_quote_option_maps;
	}
	
	public function get_service_plan_quote_option_map($option_map_id)
	{

		$this->get_service_plan_quote_options();

		if (isset($this->_service_plan_quote_option_maps[$option_map_id])) {
			return $this->_service_plan_quote_option_maps[$option_map_id];
		} else {
			return false;
		}
	}
	
	public function get_service_plan_quote_eq_type_maps()
	{
		if ($this->_service_plan_quote_eq_type_maps == null) {
			//add all the service plan eq_types
			$sql2 = 	"SELECT * FROM service_plan_quote_eq_type_mapping
								 WHERE service_plan_quote_map_id='".$this->_service_plan_quote_map_id."'
								";
			
			$service_plan_quote_eq_type_maps = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
			
			if (isset($service_plan_quote_eq_type_maps['0'])) {

				foreach ($service_plan_quote_eq_type_maps as $service_plan_quote_eq_type_map) {
						
					$service_plan_quote_eq_type_obj = new Thelist_Model_serviceplanquoteeqtypemap($service_plan_quote_eq_type_map['service_plan_quote_eq_type_map_id']);
					$this->_service_plan_quote_eq_type_maps[$service_plan_quote_eq_type_map['service_plan_quote_eq_type_map_id']] = $service_plan_quote_eq_type_obj;
				}
			}			
		}

		return $this->_service_plan_quote_eq_type_maps;
	}
	
	public function get_service_plan_quote_tasks_map()
	{
		if ($this->_service_plan_quote_tasks_map == null) {
			//add all the service plan eq_types
			$sql3 = 	"SELECT * FROM service_plan_quote_task_mapping
						WHERE service_plan_quote_map_id='".$this->_service_plan_quote_map_id."'
						";
			
			$service_plan_quote_tasks_map = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql3);
			
			if (isset($service_plan_quote_tasks_map['1'])) {
				//there can only be a single task
				throw new exception("service_plan_quote_map_id: ".$this->_service_plan_quote_map_id.", has more than one task, that should not be possible", 2203);
			} elseif (isset($service_plan_quote_tasks_map['0'])) {
						
				$this->_service_plan_quote_tasks_map = new Thelist_Model_tasks($service_plan_quote_tasks_map['0']['task_id']);
				$this->_service_plan_quote_tasks_map->fill_mapped_service_plan_quote_task_map();
				$this->_mapped_service_plan_quote_task_map_id = $this->_service_plan_quote_tasks_map->get_mapped_service_plan_quote_task_map_id();
			}
		}

		return $this->_service_plan_quote_tasks_map;
	}
	
	public function map_to_task($task_id)
	{
		
		//should be expanded everytime we want the service plan quote map to link to a task
		if (is_numeric($task_id)) {
			
			$task_obj = new Thelist_Model_tasks($task_id);

				try {
				
					$current_task_map = $this->get_service_plan_quote_tasks_map();
				
				} catch (Exception $e) {
				
					switch($e->getCode()){
				
						case 18503;
						$current_task_map = null;
						break;
						default;
						throw $e;
				
					}
				}
			
			if ($current_task_map == null && $this->_activation != null) {
				
				throw new exception("you are trying to create a task map using queue name '".$task_obj->get_task_queue_resolved()."', for service plan quote map id: '".$this->_service_plan_quote_map_id."', but the plan is activated but has no installation task", 2224);
								
			} elseif ($current_task_map != null && $task_obj->get_task_queue_resolved() == 'Installation') {
				
				//if this is an installation task, make sure there is not already one created
				return $this->_mapped_service_plan_quote_task_map_id;
				
			} elseif ($task_obj->get_task_queue_resolved() == 'Installation') {

				if ($current_task_map == null) {
					
					$sql = 	"SELECT item_id FROM items
												WHERE item_type='service_plan_quote_task_progress'
												AND item_name='not_provisioned_in_db'
												";
					
					$first_status = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
					
					$trace 		= debug_backtrace();
					$method 	= $trace[0]["function"];
					$class		= get_class($this);
					
					$data = array(
				   				'service_plan_quote_map_id' 		=> 	$this->_service_plan_quote_map_id,
				   				'task_id'							=>  $task_id,
								'service_plan_quote_task_progress' 	=>  $first_status,
					
					);
						
					Zend_Registry::get('database')->insert_single_row('service_plan_quote_task_mapping', $data, $class, $method);
					
					//now fill the mapped values
					$this->get_service_plan_quote_tasks_map();
					
					return $this->_mapped_service_plan_quote_task_map_id;
					
				} else {
					throw new exception("you are trying to create a task map using queue name '".$task_obj->get_task_queue_resolved()."', for service plan quote map id: '".$this->_service_plan_quote_map_id."', but the task already has a service plan map, a task can only be used for a single installation of a single service plan", 2226);
				}

			} else {
				throw new exception("you are trying to create a task map using queue name '".$task_obj->get_task_queue_resolved()."', for service plan quote map id: '".$this->_service_plan_quote_map_id."', but we dont know how to do that, contact software development and give them the error number", 2225);
			}
		}
	}
	
	public function create_service_plan_quote_installation_task($task_owner_uid, $installation_priority=null)
	{
		//there can only be a single task for installation
		if ($this->get_service_plan_quote_tasks_map() == null) {
			
			if ($this->_activation == null) {
				
				$sql = 	"SELECT item_id FROM items
						WHERE item_type='task_status'
						AND item_name='Open'
						";
					
				$open_status_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
					
				if ($installation_priority == null) {
					//set default priority to low
				
					$sql = 	"SELECT item_id FROM items
							WHERE item_type='task_priority'
							AND item_name='Low'
							";
						
					$installation_priority = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
				}
					
				$sql2 =	"SELECT queue_id FROM queues
						WHERE queue_name='Installation'
						";
				
				$installation_queue_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
					
				if (!isset($installation_queue_id['queue_id'])) {
					throw new exception("you are trying to create a task map for service plan quote map id: '".$this->_service_plan_quote_map_id."', however the installation queue does not exist ", 2222);
				}
					
				//we want a queue owner so the task always is associated with installation queue
				
				$data = array(
				   				'task_name' 			=> 	"Installation of " . $this->get_service_plan()->get_service_plan_group_name(),
				   				'task_status'			=>  $open_status_id,
								'task_queueowner_id' 	=>  $installation_queue_id,
				   				'task_queue_id' 		=>  $installation_queue_id,
				   				'creator'				=>	$this->user_session->uid,
								'task_priority' 		=>  $installation_priority,
				);
				
				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);
				
				$new_task_id = Zend_Registry::get('database')->insert_single_row('tasks', $data, $class, $method);
					
				$this->map_to_task($new_task_id);
									
				//return the new task, it will be filled as part of the map_to_task method
				return $this->get_service_plan_quote_tasks_map();
				
			} else {
				throw new exception("you are trying to create a task map for service plan quote map id: '".$this->_service_plan_quote_map_id."', however this service plan quote map is already active ", 2221);
			}

		} else {
			//if task already exists just return that task
			return $this->get_service_plan_quote_tasks_map();
		}
	}
	
	public function get_mapped_service_plan_quote_task_map_id()
	{
		$this->get_service_plan_quote_tasks_map();
		
		return $this->_mapped_service_plan_quote_task_map_id;
	}
	
	public function get_service_point_if_feature_requirements()
	{
		//this function should return the required if_features, with values when dealing with this serviceplan quote map.
		//all features should be mapped to the serviceplan it self but because we are dealing with directv and other equiment that 
		//the requirement will change once the service plan is mapped to a sales quote and equipment / options are mapped.
		$this->get_service_plan();
		$service_point_interface_requirements	= $this->_service_plan->get_features();
		
		if ($service_point_interface_requirements != null) {
			foreach ($service_point_interface_requirements as $service_point_interface_requirement) {
				
				//deal with directv first
				if ($service_point_interface_requirement->get_if_feature_id() == '1') {
					
					//physical swm signalling
					$if_feature_value = null;
					
				} elseif ($service_point_interface_requirement->get_if_feature_id() == '5') {
					
					//service swm tuners
					$this->get_service_plan_quote_eq_types();
					
					if ($this->_service_plan_quote_eq_types != null) {
						foreach($this->_service_plan_quote_eq_types as $eq_type) {
							
							if (!isset($required_tuners)) {
								
								//create a variable to store the tuner count in.
								$required_tuners = '0';
							}
							
							$eq_type_group = $eq_type->get_service_plan_eq_type_map()->get_eq_type_group();
							
							if($eq_type_group->get_eq_type_group_id() == '1' || $eq_type_group->get_eq_type_group_id() == '4') {
								
								$required_tuners = ($required_tuners - 2);
								
							} elseif($eq_type_group->get_eq_type_group_id() == '2' || $eq_type_group->get_eq_type_group_id() == '3') {
								
								$required_tuners = ($required_tuners - 1);
								
							}
						}
					}
					
					$if_feature_value = $required_tuners;
					
				} else {
								
					//this needs to be expanded to cover all other groups
					$if_feature_value = null;			
				}
				
				if ($if_feature_value != null) {
					//set the value of the feature object, if it is anything but null
					$service_point_interface_requirement->set_service_plan_quote_feature_value($if_feature_value);
				}
			}
			
			return $service_point_interface_requirements;
			
		} else {
			
			return false;
		}
	}
	
	public function add_service_plan_quote_eq_type($service_plan_eq_type_map_id, $service_plan_quote_eq_type_actual_mrc_term, $service_plan_quote_eq_type_actual_mrc, $service_plan_quote_eq_type_actual_nrc)
	{
	
		$service_plan_eq_type_map_obj = new Thelist_Model_serviceplaneqtypemap($service_plan_eq_type_map_id);
			
		//is the eq_type map made for the same service plan id as this temp object is using
		if ($service_plan_eq_type_map_obj->get_service_plan_id() != $this->_service_plan_id) {
			throw new exception("the service plan eq type map id: '".$service_plan_eq_type_map_id."' does not belong to the same service plan as this service plan quote map id '".$this->_service_plan_quote_map_id."'", 2212);
		} else {
	
			//get the max map count for the sp eq type map
			$fulfilled = $this->service_plan_eq_type_map_requirement_fulfilled($service_plan_eq_type_map_id);
			//as long as we are not at max we can add more
			if ($fulfilled == 'no' || $fulfilled == 'yes') {
	
				if ($service_plan_quote_eq_type_actual_mrc != null && $service_plan_quote_eq_type_actual_nrc != null) {
	
					throw new exception("mapping service plan option map id: '".$service_plan_eq_type_map_id."' to service plan quote map id '".$this->_service_plan_quote_map_id."', currently we dont allow both nrc and mrc for the same eq type, this may change", 2213);
	
				} elseif ($service_plan_quote_eq_type_actual_mrc != null && $service_plan_quote_eq_type_actual_nrc == null) {
	
					//validate that the mrc is a valid doller amount
					if (!preg_match("/^\d+(\.\d{1,2})?$/", $service_plan_quote_eq_type_actual_mrc)) {
						throw new exception("MRC amount is not a valid doller amount: '".$service_plan_quote_eq_type_actual_mrc."' .when mapping service plan eq type map id: '".$service_plan_eq_type_map_id."' to service plan quote map id '".$this->_service_plan_quote_map_id."'", 2214);
					}
	
				} elseif ($service_plan_quote_eq_type_actual_mrc == null && $service_plan_quote_eq_type_actual_nrc != null) {
	
					//validate that the nrc is a valid doller amount
					if (!preg_match("/^\d+(\.\d{1,2})?$/", $service_plan_quote_eq_type_actual_nrc)) {
						throw new exception("NRC amount is not a valid doller amount: '".$service_plan_quote_eq_type_actual_nrc."' .when mapping service plan eq type map id: '".$service_plan_eq_type_map_id."' to service plan quote map id '".$this->_service_plan_quote_map_id."'", 2215);
					}
	
				}
	
				//validate that term is a whole number
				if (!preg_match("/^[0-9]+$/", $service_plan_quote_eq_type_actual_mrc_term)) {
					throw new exception("Term whole number: '".$service_plan_quote_eq_type_actual_mrc_term."' .when mapping service plan eq type map id: '".$service_plan_eq_type_map_id."' to service plan quote map id '".$this->_service_plan_quote_map_id."'", 2216);
				}
	
				$this->get_service_plan_quote_eq_type_maps();
	
				//after all that we can now add the new option
				$data = array(
									'service_plan_eq_type_map_id'				=>	$service_plan_eq_type_map_id,
									'service_plan_quote_map_id'					=>  $this->_service_plan_quote_map_id,
									'service_plan_quote_eq_type_actual_mrc'		=>  $service_plan_quote_eq_type_actual_mrc,
									'service_plan_quote_eq_type_actual_nrc' 	=>  $service_plan_quote_eq_type_actual_nrc,
									'service_plan_quote_eq_type_actual_mrc_term'=>  $service_plan_quote_eq_type_actual_mrc_term
				);
	
				$trace	= debug_backtrace();
				$method	= $trace[0][ "function"];
				$class	= get_class($this);
	
				$new_service_plan_quote_eq_type_map_id = Zend_Registry:: get('database')->insert_single_row('service_plan_quote_eq_type_mapping', $data, $class, $method);
	
				//append it to the top
				$serviceplan_quote_eq_type_obj = new Thelist_Model_serviceplanquoteeqtypemap($new_service_plan_quote_eq_type_map_id);
				$this->_service_plan_quote_eq_type_maps[$new_service_plan_quote_eq_type_map_id] = $serviceplan_quote_eq_type_obj;
	
				//return the new object
				return $serviceplan_quote_eq_type_obj;
	
			} else {
				throw new exception("you cannot map service_plan_eq_type_map_id: '".$service_plan_eq_type_map_id."' to service plan quote map id '".$this->_service_plan_quote_map_id."'. adding it would exceed the max allowed maps", 2217);
			}
		}
	}
	
	public function service_plan_eq_type_map_requirement_fulfilled($service_plan_eq_type_map_id)
	{
		$service_plan_eq_type_map_obj = new Thelist_Model_serviceplaneqtypemap($service_plan_eq_type_map_id);
	
		$all_eq_type_maps		= $this->get_service_plan()->get_service_plan_eq_type_maps();
		$current_eq_type_maps	= $this->get_service_plan_quote_eq_type_maps();
	
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
				throw new exception("service_plan_eq_type_map_id: '".$service_plan_eq_type_map_id."' to service plan quote map id '".$this->_service_plan_quote_map_id."'. too many maps, we have a problem, there is a second input function somewhere", 2218);
			} elseif ($maximum_count > $current_map_count && $minimum_count <= $current_map_count) {
				return 'yes';
			} else {
				throw new exception("hmm, i thought i caovered all eventuallities", 2220);
			}
	
		} else {
			throw new exception("testing if service_plan_eq_type_map_id: '".$service_plan_eq_type_map_id."' to service plan quote map id '".$this->_service_plan_quote_map_id."'. requirement is fulfilled, but the service plan does not have this eq type mapped", 2219);
		}
	}
	
	public function add_service_plan_quote_option_map($service_plan_option_map_id, $service_plan_quote_option_actual_mrc_term, $service_plan_quote_option_actual_mrc, $service_plan_quote_option_actual_nrc)
	{
	
		$service_plan_option_map_obj = new Thelist_Model_serviceplanoptionmap($service_plan_option_map_id);
			
		//is the option map made for the same service plan id as this object is using
		if ($service_plan_option_map_obj->get_service_plan_id() != $this->_service_plan_id) {
			throw new exception("the service plan option map id: '".$service_plan_option_map_id."' does not belong to the same service plan as this service plan map id '".$this->_service_plan_quote_map_id."'", 2204);
		} else {
	
			//get the max map count for the sp option map
			$fulfilled = $this->service_plan_option_map_requirement_fulfilled($service_plan_option_map_id);
				
			//as long as we are not at max
			if ($fulfilled == 'no' || $fulfilled == 'yes') {
	
				if ($service_plan_quote_option_actual_mrc != null && $service_plan_quote_option_actual_nrc != null) {

					throw new exception("mapping service plan option map id: '".$service_plan_option_map_obj->get_service_plan_option_map_id()."' to service plan quote map id '".$this->_service_plan_quote_map_id."', currently we dont allow both nrc and mrc for the same option, this may change", 2207);
					
				} elseif ($service_plan_quote_option_actual_mrc != null && $service_plan_quote_option_actual_nrc == null) {

					//validate that the mrc is a valid doller amount
					if (!preg_match("/^\d+(\.\d{1,2})?$/", $service_plan_quote_option_actual_mrc)) {
						throw new exception("MRC amount is not a valid doller amount: '".$service_plan_quote_option_actual_mrc."' when mapping service plan option map id: '".$service_plan_option_map_id."' to service plan quote map id '".$this->_service_plan_quote_map_id."'", 2208);
					}
						
				} elseif ($service_plan_quote_option_actual_mrc == null && $service_plan_quote_option_actual_nrc != null) {
						
					//validate that the nrc is a valid doller amount
					if (!preg_match("/^\d+(\.\d{1,2})?$/", $service_plan_quote_option_actual_nrc)) {
						throw new exception("NRC amount is not a valid doller amount: '".$service_plan_quote_option_actual_nrc."' when mapping service plan option map id: '".$service_plan_option_map_id."' to service plan quote map id '".$this->_service_plan_quote_map_id."'", 2209);
					}	
				}
	
				//validate that term is a whole number
				if (!preg_match("/^[0-9]+$/", $service_plan_quote_option_actual_mrc_term)) {
					throw new exception("Term whole number: '".$service_plan_quote_option_actual_mrc_term."' .when mapping service plan option map id: '".$service_plan_option_map_id."' to service plan quote map id '".$this->_service_plan_quote_map_id."'", 2211);
				}
	
	
				//after all that we can now add the new option
				$this->get_service_plan_quote_option_maps();
				
				$data = array(
				
					 		'service_plan_option_map_id'					=>  $service_plan_option_map_id,
					 		'service_plan_quote_map_id'						=>  $this->_service_plan_quote_map_id,
							'service_plan_quote_option_actual_mrc'			=>  $service_plan_quote_option_actual_mrc,
							'service_plan_quote_option_actual_nrc'			=>  $service_plan_quote_option_actual_nrc,
							'service_plan_quote_option_actual_mrc_term'		=>  $service_plan_quote_option_actual_mrc_term,
				
				);
				
				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);
				
				$new_service_plan_option_map_id = Zend_Registry::get('database')->insert_single_row('service_plan_quote_option_mapping', $data, $class, $method);
					
				//append it to the top
				$serviceplan_quote_option_map_obj = new Thelist_Model_serviceplanquoteoptionmap($new_service_plan_option_map_id);
				$this->_service_plan_quote_option_maps[$new_service_plan_option_map_id] = $serviceplan_quote_option_map_obj;

				//return the new object
				return $serviceplan_quote_option_map_obj;
	
			} else {
				throw new exception("you cannot map service_plan_option_map_id: '".$service_plan_option_map_id."' to service plan quote map id '".$this->_service_plan_quote_map_id."'. adding it would exceed the max allowed maps", 2210);
			}
		}
	}
	
	public function service_plan_option_map_requirement_fulfilled($service_plan_option_map_id)
	{
	
		$service_plan_option_map_obj = new Thelist_Model_serviceplanoptionmap($service_plan_option_map_id);
	
		$all_option_maps		= $this->get_service_plan()->get_service_plan_option_maps();
		$current_option_maps	= $this->get_service_plan_quote_option_maps();
	
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
				throw new exception("service_plan_option_map_id: '".$service_plan_option_map_id."' to service plan map id '".$this->_service_plan_quote_map_id."'. too many maps, we have a problem, there is a second input function somewhere", 2204);
			} elseif ($maximum_count > $current_map_count && $minimum_count <= $current_map_count) {
				return 'yes';
			} else {
				throw new exception("hmm, i thought i caovered all eventuallities", 2206);
			}
	
		} else {
			throw new exception("testing if service_plan_option_map_id: '".$service_plan_option_map_obj->get_service_plan_option_map_id()."' to service plan map id '".$this->_service_plan_quote_map_id."'. requirement is fulfilled, but the service plan does not have this option mapped", 2205);
		}
	}
	
// 	public function remove_service_plan_quote_option($service_plan_quote_option_map_id)
// 	{
// 		if ($this->_service_plan_quote_options == null) {
// 			$this->get_service_plan_quote_options();
// 		}
		
// 		if (isset($this->_service_plan_quote_options[$service_plan_quote_option_map_id])) {

// 			$trace 		= debug_backtrace();
// 			$method 	= $trace[0]["function"];
// 			$class		= get_class($this);
			
// 			Zend_Registry::get('database')->delete_single_row($service_plan_quote_option_map_id, 'service_plan_quote_option_mapping',$class,$method);
			
			///unset it from memory
// 			unset($this->_service_plan_quote_options[$service_plan_quote_option_map_id]);
			
// 		} else {
// 			throw new exception("you are attempting to remove service_plan_quote_option_map_id: ".$service_plan_quote_option_map_id." from service_plan_quote_map_id: ".$this->_service_plan_quote_map_id.", but its not mapped to this quote map", 2200);
// 		}
// 	}
	
// 	public function add_service_plan_quote_eq_type($service_plan_eq_type_map_id, $service_plan_quote_eq_type_actual_mrc=null, $service_plan_quote_eq_type_actual_nrc=null, $service_plan_quote_eq_type_actual_mrc_term=null)
// 	{
		
// 		if ($this->_service_plan_quote_eq_types == null) {
// 			$this->get_service_plan_quote_eq_types();
// 		}
		
// 		$data = array(
// 			 		'service_plan_eq_type_map_id'					=>  $service_plan_eq_type_map_id,
// 			 		'service_plan_quote_map_id'						=>  $this->_service_plan_quote_map_id,
// 					'service_plan_quote_eq_type_actual_mrc'			=>  $service_plan_quote_eq_type_actual_mrc,
// 					'service_plan_quote_eq_type_actual_nrc'			=>  $service_plan_quote_eq_type_actual_nrc,
// 					'service_plan_quote_eq_type_actual_mrc_term'	=>  $service_plan_quote_eq_type_actual_mrc_term,
// 		);
	
// 		$trace 		= debug_backtrace();
// 		$method 	= $trace[0]["function"];
// 		$class		= get_class($this);
	
// 		$new_service_plan_quote_eq_type_map_id = Zend_Registry::get('database')->insert_single_row('service_plan_quote_eq_type_mapping', $data, $class, $method);

// 		$this->_service_plan_quote_eq_types[$new_service_plan_quote_eq_type_map_id] = new Thelist_Model_serviceplanquoteeqtypemap($new_service_plan_quote_eq_type_map_id);
	
// 		return $serviceplan_quote_eq_type_obj;
// 	}
	
// 	public function remove_service_plan_quote_eq_type($service_plan_quote_eq_type_map_id)
// 	{
		
// 		if ($this->_service_plan_quote_eq_types == null) {
// 			$this->get_service_plan_quote_eq_types();
// 		}
		
// 		if (isset($this->_service_plan_quote_eq_types[$service_plan_quote_eq_type_map_id])) {
		
// 			$trace 		= debug_backtrace();
// 			$method 	= $trace[0]["function"];
// 			$class		= get_class($this);
				
// 			Zend_Registry::get('database')->delete_single_row($service_plan_quote_eq_type_map_id, 'service_plan_quote_eq_type_mapping',$class,$method);
				
	////		unset it from memory
// 			unset($this->_service_plan_quote_eq_types[$service_plan_quote_eq_type_map_id]);
				
// 		} else {
// 			throw new exception("you are attempting to remove service_plan_quote_eq_type_map_id: ".$service_plan_quote_eq_type_map_id." from service_plan_quote_map_id: ".$this->_service_plan_quote_map_id.", but its not mapped to this quote map", 2201);
// 		}
// 	}
	
	public function map_equipment_to_unit($equipment_map_id)
	{
		$trace 		= debug_backtrace();
		$method 	= $trace[0]["function"];
		$class		= get_class($this);
	
		$data = array(
	
					'equipment_map_id'				=> $equipment_map_id,
					'service_plan_quote_map_id'		=> $this->_service_plan_quote_map_id,
			
		);
	
		Zend_Registry::get('database')->insert_single_row('sales_quote_equipment_mapping',$data,$class,$method);
	
	}
	
	public function get_service_plan_quote_connection_queue_filter_maps()
	{
		if ($this->_service_plan_quote_connection_queue_filter_maps == null) {
				
			$sql2 = 	"SELECT * FROM service_plan_quote_connection_queue_filter_mapping
						WHERE service_plan_quote_map_id='".$this->_service_plan_quote_map_id."'
						";
				
			$service_plan_filter_maps = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
				
			if (isset($service_plan_filter_maps['0'])) {
				foreach($service_plan_filter_maps as $service_plan_filter_map) {
						
					$conn_filter_obj = new Thelist_Model_connectionqueuefilter($service_plan_filter_map['connection_queue_filter_id']);
						
					$this->_service_plan_quote_connection_queue_filter_maps[$conn_filter_obj->get_connection_queue_filter_id()] = $conn_filter_obj;
						
				}
			}
		}
		
		return $this->_service_plan_quote_connection_queue_filter_maps;
		
	}
	
	public function get_service_plan_quote_ip_address_maps()
	{
		if ($this->_service_plan_quote_ip_address_maps == null) {
			
			$sql2 = 	"SELECT * FROM service_plan_quote_ip_address_mapping
						WHERE service_plan_quote_map_id='".$this->_service_plan_quote_map_id."'
						";
			
			$service_plan_ip_maps = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
			
			if (isset($service_plan_ip_maps['0'])) {
				foreach($service_plan_ip_maps as $service_plan_ip_map) {
					
					$ip_address_obj = new Thelist_Model_ipaddress($service_plan_ip_map['ip_address_id']);
					
					$this->_service_plan_quote_ip_address_maps[$ip_address_obj->get_ip_address_id()] = $ip_address_obj;
					
				}
			}
		}
		
		return $this->_service_plan_quote_ip_address_maps;
	}

	public function map_ip_address($ip_address_obj)
	{
		if ($this->_service_plan_quote_ip_address_maps == null) {
			$this->get_service_plan_quote_ip_address_maps();
		}

		if (!isset($this->_service_plan_quote_ip_address_maps[$ip_address_obj->get_ip_address_id()])) {
			
			//map ipaddresses that are accosiated with this service plan map
			$data = array(
								 		'ip_address_id'									=>  $ip_address_obj->get_ip_address_id(),
								 		'service_plan_quote_map_id'						=>  $this->_service_plan_quote_map_id,
			);
			
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
			
			Zend_Registry::get('database')->insert_single_row('service_plan_quote_ip_address_mapping', $data, $class, $method);
			
			$this->_service_plan_quote_ip_address_maps[$ip_address_obj->get_ip_address_id()]	= $ip_address_obj;

		}

	}
	
	public function map_connection_queue_filter($filter_obj)
	{
	
		if ($this->_service_plan_quote_connection_queue_filter_maps == null) {
			$this->get_service_plan_quote_connection_queue_filter_maps();
		}
		
		if (!isset($this->_service_plan_quote_connection_queue_filter_maps[$filter_obj->get_connection_queue_filter_id()])) {
				
			//map ipaddresses that are accosiated with this service plan map
			$data = array(
									 		'connection_queue_filter_id'					=>  $filter_obj->get_connection_queue_filter_id(),
									 		'service_plan_quote_map_id'						=>  $this->_service_plan_quote_map_id,
			);
				
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
				
			Zend_Registry::get('database')->insert_single_row('service_plan_quote_connection_queue_filter_mapping', $data, $class, $method);
				
			$this->_service_plan_quote_connection_queue_filter_maps[$filter_obj->get_connection_queue_filter_id()]	= $filter_obj;
	
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