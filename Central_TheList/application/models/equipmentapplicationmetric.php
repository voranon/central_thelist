<?php 

//exception codes 8700-8799

class thelist_model_equipmentapplicationmetric
{
	
	private $_equipment_application_metric_id;
	private $_equipment_application_metric_name;
	
	private $_equipment_application_metric_map_id=null;
	
	private $_equipment_application_map_id=null;
	private $_equipment_application_metric_index=null;
	private $_equipment_application_metric_group_id=null;
	private $_equipment_application_metric_value=null;
	
	private $_equipment_type_application_metric_id=null;
	
	private $_eq_type_application_id=null;
	private $_eq_type_metric_max_maps=null;
	private $_eq_type_metric_default_value_1=null;
	private $_eq_type_metric_default_map=null;
	private $_eq_type_metric_mandetory=null;
	private $_eq_type_metric_allow_edit=null;
	
	public function __construct($equipment_application_metric_id)
	{
		$this->_equipment_application_metric_id = $equipment_application_metric_id;
		
		$sql = "SELECT * FROM equipment_application_metrics
				WHERE equipment_application_metric_id='".$this->_equipment_application_metric_id."'
				";
			
		$equipment_application_metric  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		$this->_equipment_application_metric_name = $equipment_application_metric['equipment_application_metric_name'];
		
	}
	
	public function set_eq_type_metric_mandetory($equipment_type_application_metric_id, $eq_type_metric_mandetory)
	{

		if ($eq_type_metric_mandetory == 0 || $eq_type_metric_mandetory == 1) {
			
			$sql = 	"SELECT * FROM equipment_type_application_metrics etam
					WHERE etam.equipment_application_metric_id='".$this->_equipment_application_metric_id."'
					AND etam.equipment_type_application_metric_id='".$equipment_type_application_metric_id."'
					";
				
			$metric_detail = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
			if (isset($metric_detail['equipment_type_application_metric_id'])) {

				if ($metric_detail['eq_type_metric_mandetory'] != $eq_type_metric_mandetory) {
		
					$trace 		= debug_backtrace();
					$method 	= $trace[0]["function"];
					$class		= get_class($this);
		
					Zend_Registry::get('database')->set_single_attribute($equipment_type_application_metric_id, 'equipment_type_application_metrics', 'eq_type_metric_mandetory', $eq_type_metric_mandetory, $class, $method);
		
					//also make sure the default map is set to 1, since mandetory implies default
					$this->set_eq_type_metric_default_map($equipment_type_application_metric_id, 1);
				}
			
			} else {
				throw new exception("this metric is not set for the eq type application", 8709);
			}

		} else {
			throw new exception("eq_type_metric_mandetory must be either 0 or 1", 8710);
		}
	}
	
	public function set_eq_type_metric_default_map($equipment_type_application_metric_id, $eq_type_metric_default_map)
	{
		if ($eq_type_metric_default_map == 0 || $eq_type_metric_default_map == 1) {
	
			$sql = 	"SELECT * FROM equipment_type_application_metrics etam
					WHERE etam.equipment_application_metric_id='".$this->_equipment_application_metric_id."'
					AND etam.equipment_type_application_metric_id='".$equipment_type_application_metric_id."'
					";
			
			$metric_detail = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
			
			if (isset($metric_detail['equipment_type_application_metric_id'])) {
					
				if ($metric_detail['eq_type_metric_default_map'] != $eq_type_metric_default_map) {
	
					$trace 		= debug_backtrace();
					$method 	= $trace[0]["function"];
					$class		= get_class($this);
	
					Zend_Registry::get('database')->set_single_attribute($equipment_type_application_metric_id, 'equipment_type_application_metrics', 'eq_type_metric_default_map', $eq_type_metric_default_map, $class, $method);
				}
					
			} else {
				throw new exception("Configuration is not defined for this interface type", 8712);
			}
		} else {
			throw new exception("eq_type_metric_default_map must be either 0 or 1", 8711);
		}
	}
	
	public function set_eq_type_metric_allow_edit($equipment_type_application_metric_id, $eq_type_metric_allow_edit)
	{
		if ($eq_type_metric_allow_edit == 0 || $eq_type_metric_allow_edit == 1) {
	
			$sql = 	"SELECT * FROM equipment_type_application_metrics etam
					WHERE etam.equipment_application_metric_id='".$this->_equipment_application_metric_id."'
					AND etam.equipment_type_application_metric_id='".$equipment_type_application_metric_id."'
					";
			
			$metric_detail = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

			if (isset($metric_detail['equipment_type_application_metric_id'])) {
					
				if ($metric_detail['eq_type_metric_allow_edit'] != $eq_type_metric_allow_edit) {
	
					$trace 		= debug_backtrace();
					$method 	= $trace[0]["function"];
					$class		= get_class($this);

					Zend_Registry::get('database')->set_single_attribute($equipment_type_application_metric_id, 'equipment_type_application_metrics', 'eq_type_metric_allow_edit', $eq_type_metric_allow_edit, $class, $method);
				}
					
			} else {
				throw new exception("Configuration is not defined for this interface type", 8714);
			}
			
		} else {
			throw new exception("eq_type_metric_allow_edit must be either 0 or 1", 8713);
		}
	}
	
	public function set_eq_type_metric_max_maps($equipment_type_application_metric_id, $eq_type_metric_max_maps)
	{
		
		if (is_numeric($eq_type_metric_max_maps)) {
	
			$sql = 	"SELECT * FROM equipment_type_application_metrics etam
					WHERE etam.equipment_application_metric_id='".$this->_equipment_application_metric_id."'
					AND etam.equipment_type_application_metric_id='".$equipment_type_application_metric_id."'
					";
			
			$metric_detail = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

			if (isset($metric_detail['equipment_type_application_metric_id'])) {
					
				if ($metric_detail['eq_type_metric_max_maps'] != $eq_type_metric_max_maps) {
	
					$trace 		= debug_backtrace();
					$method 	= $trace[0]["function"];
					$class		= get_class($this);
	
					Zend_Registry::get('database')->set_single_attribute($equipment_type_application_metric_id, 'equipment_type_application_metrics', 'eq_type_metric_max_maps', $eq_type_metric_max_maps, $class, $method);
				}
					
			} else {
				throw new exception("Configuration is not defined for this interface type", 8716);
			}
		} else {
			throw new exception("eq_type_metric_max_maps must be numeric", 8715);
		}
	}
	
	
	public function set_eq_type_metric_default_value_1($equipment_type_application_metric_id, $eq_type_metric_default_value_1)
	{
		$sql = 	"SELECT * FROM equipment_type_application_metrics etam
				WHERE etam.equipment_application_metric_id='".$this->_equipment_application_metric_id."'
				AND etam.equipment_type_application_metric_id='".$equipment_type_application_metric_id."'
				";
			
		$metric_detail = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
	
		if (isset($metric_detail['equipment_type_application_metric_id'])) {
	
			if ($metric_detail['eq_type_metric_default_value_1'] != $eq_type_metric_default_value_1) {
	
				//null is allowed for default value regardless of the allow values
				if ($eq_type_metric_default_value_1 != null) {
	
					$valid_default	= $this->is_value_valid($equipment_type_application_metric_id, $eq_type_metric_default_value_1);
	
					if ($valid_default !== true) {
						throw new exception("Default value '".$eq_type_metric_default_value_1."' for Eq Type Metric: '".$this->get_equipment_application_metric_name()."' is not allowed ", 8717);
					}
				}
	
				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);
					
				Zend_Registry::get('database')->set_single_attribute($equipment_type_application_metric_id, 'equipment_type_application_metrics', 'eq_type_metric_default_value_1', $eq_type_metric_default_value_1, $class, $method);
			}
	
		} else {
			throw new exception("Configuration is not defined for this interface type", 8718);
		}
	}
	
	public function is_value_valid($equipment_type_application_metric_id, $value, $eq_id=null)
	{
		$valid_configs	= $this->get_valid_configuration_value_1($equipment_type_application_metric_id, $eq_id);
	
		if ($valid_configs === false) {
			//make sure its a boolan
			return false;
		} elseif ($valid_configs === true) {
			//make sure its a boolan
			//all values are accepted
			return true;
		} elseif (is_array($valid_configs)) {
				
			foreach($valid_configs as $valid_config) {
	
				if ($valid_config == $value) {
					return true;
				}
			}
				
			//there is no config that matches that type and if_conf_id and value
			return false;
				
		} else {
			throw new exception("un expected result for equipment_type_application_metric_id: ".$equipment_type_application_metric_id." for equipment_application_metric_id: ".$this->_equipment_application_metric_id."", 8719);
		}
	}
	
	public function get_valid_configuration_value_1($equipment_type_application_metric_id, $eq_id=null)
	{
		//set the default value of special flag.
		$special_value = 'no';
		
		if ($eq_id != null) {
			
			//figure out if this is a special metric that should be handled
			$sql = 	"SELECT * FROM eq_type_allowed_metric_values etamv
					INNER JOIN equipment_type_application_metrics etam ON etam.equipment_type_application_metric_id=etamv.equipment_type_application_metric_id
					INNER JOIN eq_type_applications eta ON eta.eq_type_application_id=etam.eq_type_application_id
					WHERE etam.equipment_type_application_metric_id='".$equipment_type_application_metric_id."'
					";
			
			$is_special 	= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
				
			//we fetch row because a special value can only have a single allowed value
			if (count($is_special) == 1) {
			
				//are we looking for valid interfaces on this equipment for use as a  hcp server interface name?
				if ($is_special['0']['eq_type_metric_allow_all_interface_names'] == 1 && $is_special['0']['equipment_application_id'] == 1) {
						
					$special_value = 'yes';
					
					$equipment_obj 	= new Thelist_Model_equipments($eq_id);
						
					$interfaces = $equipment_obj->get_interfaces();
						
					if ($interfaces != null) {
			
						//get all existing dhcp server interface names
						$interface_name_metrics = $equipment_obj->get_metric_details_for_application_id(13, 1);
			
						foreach ($interfaces as $interface) {
								
							$already_in_use = 'no';
								
							if ($interface_name_metrics != false) {
			
								foreach ($interface_name_metrics as $interface_name_metric) {
										
									if ($interface->get_if_name() == $interface_name_metric->get_equipment_application_metric_value()) {
										$already_in_use = 'yes';
									}
								}
							}
								
							if ($already_in_use == 'no') {
								$return_array[] = $interface->get_if_name();
							}
						}
					}
						
					if (!isset($return_array)) {
						//if no valid configs are set it means all interfaces are in use and we want to relay this to the front
						$return_array[] = 'No Unused Values';
					} 
					
					return $return_array;
				}
			}
		} 
		
		//if this is not a special value
		if ($special_value == 'no') {
		
			$sql = "SELECT * FROM eq_type_allowed_metric_values etamv
					INNER JOIN equipment_type_application_metrics etam ON etam.equipment_type_application_metric_id=etamv.equipment_type_application_metric_id
					WHERE etam.equipment_type_application_metric_id='".$equipment_type_application_metric_id."'
					AND etam.equipment_application_metric_id='".$this->_equipment_application_metric_id."'
					";
		
			$allowed_values = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
			if (isset($allowed_values['0'])) {
		
				foreach ($allowed_values as $allowed_value) {
					
					if ($allowed_value['eq_type_metric_allow_all_interface_names'] == 1) {
						
						//all values are allowed, it will depend on the equipment.
						return true;
						
					} elseif ($allowed_value['eq_type_allowed_metric_value_start'] != null && $allowed_value['eq_type_allowed_metric_value_end'] == null) {
							
						if ($allowed_value['eq_type_allowed_metric_value_start'] == $allowed_value['eq_type_metric_default_value_1'] && $allowed_value['eq_type_metric_default_value_1'] != null) {
							$default_value = $allowed_value['eq_type_allowed_metric_value_start'];
						} else {
		
							//else add it to the array
							$return_array[]	=		$allowed_value['eq_type_allowed_metric_value_start'];
						}
		
					} elseif ($allowed_value['eq_type_allowed_metric_value_start'] != null && $allowed_value['eq_type_allowed_metric_value_end'] != null) {
		
						//we can currently only do ranges from numbers, but expand in the future
						if (is_numeric($allowed_value['eq_type_allowed_metric_value_start']) && is_numeric($allowed_value['eq_type_allowed_metric_value_end'])) {
		
							$all_allowed_values	= range($allowed_value['eq_type_allowed_metric_value_start'], $allowed_value['eq_type_allowed_metric_value_end']);
								
							foreach($all_allowed_values as $single_allowed_value) {
		
								if ($single_allowed_value == $allowed_value['eq_type_metric_default_value_1'] && $allowed_value['eq_type_metric_default_value_1'] != null) {
									$default_value = $single_allowed_value;
								} else {
									//else add it to the array
									$return_array[]	=		$single_allowed_value;
								}
							}
		
						} else {
							throw new exception("start or end configuration values for equipment_type_application_metric_id: ".$equipment_type_application_metric_id." for equipment_application_metric_id: ".$this->_equipment_application_metric_id." are not numeric, that is an input issue somewhere", 8720);
						}
							
					} elseif ($allowed_value['eq_type_allowed_metric_value_start'] == null && $allowed_value['eq_type_allowed_metric_value_end'] == null) {
						//all values are allowed
						return true;
					}
				}
			}
				
			//if we dident return then we have gatherd up some values
			//return them after adding anydefault values
			if (isset($default_value) && isset($return_array)) {
				//if this is the default value, it should be on top
				array_unshift($return_array, $default_value);
			} elseif (isset($default_value) && !isset($return_array)) {
				$return_array[] = $default_value;
			}
				
			return $return_array;
				
		} else {
			//this config is not set for this interface type
			return false;
		}
	}

	public function update_eq_type_allowed_metric_value($equipment_type_application_metric_id, $eq_type_allowed_metric_value_id, $start_value, $end_value=null)
	{
	
		$sql = "SELECT COUNT(etamv.eq_type_allowed_metric_value_id) FROM eq_type_allowed_metric_values etamv
				INNER JOIN equipment_type_application_metrics etam ON etam.equipment_type_application_metric_id=etamv.equipment_type_application_metric_id
				WHERE etamv.eq_type_allowed_metric_value_id='".$eq_type_allowed_metric_value_id."'
				AND etam.equipment_application_metric_id='".$this->_equipment_application_metric_id."'
				AND etam.equipment_type_application_metric_id='".$equipment_type_application_metric_id."'
				";
		
		$exists = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
	
		if ($exists == 1) {
	
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
				
			if ($end_value != null) {
	
				if (is_numeric($start_value) && is_numeric($end_value)) {
						
					if ($start_value < $end_value) {
	
						//update start value
						Zend_Registry::get('database')->set_single_attribute($eq_type_allowed_metric_value_id, 'eq_type_allowed_metric_values', 'eq_type_allowed_metric_value_start', $start_value, $class, $method);
						//update end value
						Zend_Registry::get('database')->set_single_attribute($eq_type_allowed_metric_value_id, 'eq_type_allowed_metric_values', 'eq_type_allowed_metric_value_end', $end_value, $class, $method);
	
					} else {
						throw new exception("you are trying to update an allowed value with a range, but the start value is smaller than the end value", 8706);
					}
	
				} else {
					throw new exception("you are trying to update an allowed value with a range, but the values are not numeric, you must use numeric values for a range", 8707);
				}
			} else {
	
				//no end value
				//update start value
				Zend_Registry::get('database')->set_single_attribute($eq_type_allowed_metric_value_id, 'eq_type_allowed_metric_values', 'eq_type_allowed_metric_value_start', $start_value, $class, $method);
				//update end value to null
				Zend_Registry::get('database')->set_single_attribute($eq_type_allowed_metric_value_id, 'eq_type_allowed_metric_values', 'eq_type_allowed_metric_value_end', null, $class, $method);
			}
				
		} else {
			throw new exception("you are trying to update an allowed value that is not mapped. eq_type_allowed_metric_value_id: '".$eq_type_allowed_metric_value_id."' for Metric: '".$this->get_equipment_application_metric_name()."' for equipment_application_metric_id: ".$this->_equipment_application_metric_id." ", 8708);
		}
	}
	
	public function remove_eq_type_allowed_metric_value($equipment_type_application_metric_id, $eq_type_allowed_metric_value_id)
	{

		$sql = "SELECT COUNT(etamv.eq_type_allowed_metric_value_id) FROM eq_type_allowed_metric_values etamv
				INNER JOIN equipment_type_application_metrics etam ON etam.equipment_type_application_metric_id=etamv.equipment_type_application_metric_id
				WHERE etamv.eq_type_allowed_metric_value_id='".$eq_type_allowed_metric_value_id."'
				AND etam.equipment_application_metric_id='".$this->_equipment_application_metric_id."'
				AND etam.equipment_type_application_metric_id='".$equipment_type_application_metric_id."'
				";
		
		$exists = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
	
		if ($exists == 1) {
				
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
	
			Zend_Registry::get('database')->delete_single_row($eq_type_allowed_metric_value_id, 'eq_type_allowed_metric_values', $class, $method);
				
		} else {
			throw new exception("you are trying to remove an allowed value that is not mapped. eq_type_allowed_metric_value_id: '".$eq_type_allowed_metric_value_id."' for Metric: '".$this->get_equipment_application_metric_name()."' for equipment_application_metric_id: ".$this->_equipment_application_metric_id." ", 8705);
		}
	}
	
	public function add_eq_type_allowed_special_metric_value($equipment_type_application_metric_id, $special_value_name)
	{

		$sql = "SELECT COUNT(etam.equipment_type_application_metric_id) FROM equipment_type_application_metrics etam
				WHERE etam.equipment_type_application_metric_id='".$equipment_type_application_metric_id."'
				AND etam.equipment_application_metric_id='".$this->_equipment_application_metric_id."'
				";
	
		$exists = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		if ($exists == 1) {
			
			$sql = "SELECT * FROM eq_type_allowed_metric_values etamv
					INNER JOIN equipment_type_application_metrics etam ON etam.equipment_type_application_metric_id=etamv.equipment_type_application_metric_id
					WHERE etam.equipment_type_application_metric_id='".$equipment_type_application_metric_id."'
					AND etam.equipment_application_metric_id='".$this->_equipment_application_metric_id."'
					";
			
			$allowed_values = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);

			$already_created = 'no';
			
			if (isset($allowed_values['0'])) {
				
				foreach ($allowed_values as $allowed_value) {
					
					if ($special_value_name == 'allow_all_interface_names' && $allowed_value['eq_type_metric_allow_all_interface_names'] == 1) {
						 return $allowed_value['eq_type_allowed_metric_value_id'];
					}
				}
			}
			
			if ($already_created == 'no') {
				
				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);
				
				if ($special_value_name == 'allow_all_interface_names') {
					
					$data = array(
					
						'equipment_type_application_metric_id'				=> $equipment_type_application_metric_id,
						'eq_type_allowed_metric_value_start'				=> 'special value: ALL INTERFACE NAMES',
						'eq_type_allowed_metric_value_end' 					=> 'special value: ALL INTERFACE NAMES',
						'eq_type_metric_allow_all_interface_names'			=> 1,
					);
					
					return Zend_Registry::get('database')->insert_single_row('eq_type_allowed_metric_values',$data, $class,$method);
				}
			}
			
		} else {
			throw new exception("you are trying to add a special allowed value to a eq type application that does not exist: metric: '".$this->get_equipment_application_metric_name()."' on equipment_type_application_metric_id: ".$equipment_type_application_metric_id." for equipment_application_metric_id: ".$this->_equipment_application_metric_id." ", 8714);
		}
	}
	
	public function add_eq_type_allowed_metric_value($equipment_type_application_metric_id, $start_value, $end_value)
	{
	
		$sql = "SELECT COUNT(etam.equipment_type_application_metric_id) FROM equipment_type_application_metrics etam
				WHERE etam.equipment_type_application_metric_id='".$equipment_type_application_metric_id."'
				AND etam.equipment_application_metric_id='".$this->_equipment_application_metric_id."'
				";
	
		$exists = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
	
		if ($exists == 1) {
	
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
	
			if ($end_value != null) {
	
				if (is_numeric($start_value) && is_numeric($end_value)) {
	
					if ($start_value < $end_value) {
	
						$data = array(
	
										'equipment_type_application_metric_id'				=>  $equipment_type_application_metric_id,
										'eq_type_allowed_metric_value_start'				=>  $start_value,
										'eq_type_allowed_metric_value_end' 					=>	$end_value,  
	
						);
	
						//update start value
						return Zend_Registry::get('database')->insert_single_row('eq_type_allowed_metric_values',$data, $class,$method);
	
					} else {
						throw new exception("you are trying to update an allowed value with a range, but the start value is smaller than the end value", 8702);
					}
	
				} else {
					throw new exception("you are trying to update an allowed value with a range, but the values are not numeric, you must use numeric values for a range", 8703);
				}
				
			} else {
	
				//no end value
				$data = array(
										'equipment_type_application_metric_id'				=>  $equipment_type_application_metric_id,
										'eq_type_allowed_metric_value_start'				=>  $start_value,
										'eq_type_allowed_metric_value_end' 					=>	null,
	
				);
	
				return Zend_Registry::get('database')->insert_single_row('eq_type_allowed_metric_values',$data, $class,$method);
			}
	
		} else {
			throw new exception("you are trying to add an allowed value to a eq type application that does not exist: metric: '".$this->get_equipment_application_metric_name()."' on equipment_type_application_metric_id: ".$equipment_type_application_metric_id." for equipment_application_metric_id: ".$this->_equipment_application_metric_id." ", 8704);
		}
	}
	
	public function fill_mapped_values($equipment_application_metric_map_id)
	{
		$this->_equipment_application_metric_map_id = $equipment_application_metric_map_id;
		
		//make sure that the map provided is mapping to this type of metric
		$sql = "SELECT * FROM equipment_application_metric_mapping
				WHERE equipment_application_metric_map_id='".$this->_equipment_application_metric_map_id."'
				";
		
		$metric_map  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		if ($metric_map['equipment_application_metric_id'] == $this->_equipment_application_metric_id) {
		
			$this->_equipment_application_map_id 				= $metric_map['equipment_application_map_id'];
			$this->_equipment_application_metric_index 			= $metric_map['equipment_application_metric_index'];
			$this->_equipment_application_metric_group_id 		= $metric_map['equipment_application_metric_group_id'];
			$this->_equipment_application_metric_value 			= $metric_map['equipment_application_metric_value'];
			
		} else {
			throw new exception('you are mapping using a matric map id that is not tied to metric of this type', 8700);
		}
	}
	
	public function set_equipment_application_metric_index($equipment_application_metric_index)
	{
		if ($this->_equipment_application_map_id != null) {
			
			if (is_numeric($equipment_application_metric_index)) {
			
				if ($equipment_application_metric_index != $this->_equipment_application_metric_index) {

					$trace  = debug_backtrace();
					$method = $trace[0]["function"];
					$class	= get_class($this);
						
					Zend_Registry::get('database')->set_single_attribute($this->_equipment_application_metric_map_id, 'equipment_application_metric_mapping', 'equipment_application_metric_index', $equipment_application_metric_index, $class, $method);
	
					$this->_equipment_application_metric_index = $equipment_application_metric_index;
				}
				
			} else {
				throw new exception("equipment_application_metric_index must be numeric ", 8725);
			}
			
		} else {
			throw new exception("you must first map the metric to an application by using fill_mapped_values ", 8721);
		}
		
	}
	
	public function set_equipment_application_metric_group_id($equipment_application_metric_group_id)
	{
		if ($this->_equipment_application_map_id != null) {
				
			if ($equipment_application_metric_group_id != $this->_equipment_application_metric_group_id) {

				if (is_numeric($equipment_application_metric_group_id)) {
					
					$trace  = debug_backtrace();
					$method = $trace[0]["function"];
					$class	= get_class($this);
						
					Zend_Registry::get('database')->set_single_attribute($this->_equipment_application_metric_map_id, 'equipment_application_metric_mapping', 'equipment_application_metric_group_id', $equipment_application_metric_group_id, $class, $method);
					
					$this->_equipment_application_metric_group_id = $equipment_application_metric_group_id;
					
				} else {
					throw new exception("equipment_application_metric_group_id must be numeric ", 8722);
				}
			}
			
		} else {
			throw new exception("you must first map the metric to an application by using fill_mapped_values ", 8723);
		}
	}
	
	public function set_equipment_application_metric_value($equipment_application_metric_value)
	{
		if ($this->_equipment_application_map_id != null) {
			
			if ($equipment_application_metric_value != $this->_equipment_application_metric_value) {

				if ($equipment_application_metric_value != null) {

					$sql = 	"SELECT etam.equipment_type_application_metric_id, e.eq_id FROM equipment_application_mapping eam
							INNER JOIN equipments e ON e.eq_id=eam.eq_id
							INNER JOIN eq_type_applications eta ON eta.equipment_application_id=eam.equipment_application_id
							INNER JOIN equipment_type_application_metrics etam ON etam.eq_type_application_id=eta.eq_type_application_id
							WHERE eam.equipment_application_map_id='".$this->_equipment_application_map_id."'
							AND etam.equipment_application_metric_id='".$this->_equipment_application_metric_id."'
							AND eta.eq_type_id=e.eq_type_id
							";
					
					$details  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

					if (isset($details['equipment_type_application_metric_id'])) {

						$valid	= $this->is_value_valid($details['equipment_type_application_metric_id'], $equipment_application_metric_value, $details['eq_id']);
						
					} else {
						throw new exception("you are assigning metric name: '".$this->_equipment_application_metric_name."', value: '".$equipment_application_metric_value."', but this metric is not mapped to the application for the type of equipment", 8727);
					}

					if ($valid !== true) {
						throw new exception("new value: '".$equipment_application_metric_value."' is not valid ", 8726);
					}
				}
				
				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);
				
				Zend_Registry::get('database')->set_single_attribute($this->_equipment_application_metric_map_id, 'equipment_application_metric_mapping', 'equipment_application_metric_value', $equipment_application_metric_value, $class, $method);
				
				$this->_equipment_application_metric_value = $equipment_application_metric_value;
			}

		} else {
			throw new exception("you must first map the metric to an application by using fill_mapped_values ", 8724);
		}
	}
	
	public function fill_eq_type_mapped_values($equipment_type_application_metric_id)
	{
		$this->_equipment_type_application_metric_id = $equipment_type_application_metric_id;
	
		//make sure that the map provided is mapping to this type of metric
		$sql = "SELECT * FROM equipment_type_application_metrics
				WHERE equipment_type_application_metric_id='".$this->_equipment_type_application_metric_id."'
				";
	
		$metric_map  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
	
		if ($metric_map['equipment_application_metric_id'] == $this->_equipment_application_metric_id) {
	
			$this->_eq_type_application_id 				= $metric_map['eq_type_application_id'];
			$this->_eq_type_metric_max_maps 			= $metric_map['eq_type_metric_max_maps'];
			$this->_eq_type_metric_default_value_1 		= $metric_map['eq_type_metric_default_value_1'];
			$this->_eq_type_metric_default_map 			= $metric_map['eq_type_metric_default_map'];
			$this->_eq_type_metric_mandetory 			= $metric_map['eq_type_metric_mandetory'];
			$this->_eq_type_metric_allow_edit 			= $metric_map['eq_type_metric_allow_edit'];
				
		} else {
			throw new exception('you are mapping to equipment type using a metric map id that is not tied to metric of this type', 8701);
		}
	}
	
	public function get_equipment_type_application_metric_id()
	{
		return $this->_equipment_type_application_metric_id;
	}
	public function get_eq_type_application_id()
	{
		return $this->_eq_type_application_id;
	}
	public function get_eq_type_metric_max_maps()
	{
		return $this->_eq_type_metric_max_maps;
	}
	public function get_eq_type_metric_default_value_1()
	{
		return $this->_eq_type_metric_default_value_1;
	}
	public function get_eq_type_metric_default_map()
	{
		return $this->_eq_type_metric_default_map;
	}
	public function get_eq_type_metric_mandetory()
	{
		return $this->_eq_type_metric_mandetory;
	}
	public function get_eq_type_metric_allow_edit()
	{
		return $this->_eq_type_metric_allow_edit;
	}
	
	
	public function get_equipment_application_metric_id()
	{
		return $this->_equipment_application_metric_id;
	}
	
	public function get_equipment_application_metric_name()
	{
		return $this->_equipment_application_metric_name;
	}
	
	public function get_equipment_application_metric_map_id()
	{
		return $this->_equipment_application_metric_map_id;
	}
	
	public function get_equipment_application_map_id()
	{
		return $this->_equipment_application_map_id;
	}
	
	public function get_equipment_application_metric_index()
	{
		return $this->_equipment_application_metric_index;
	}
	
	public function get_equipment_application_metric_group_id()
	{
		return $this->_equipment_application_metric_group_id;
	}
	
	public function get_equipment_application_metric_value()
	{
		return $this->_equipment_application_metric_value;
	}

}
?>