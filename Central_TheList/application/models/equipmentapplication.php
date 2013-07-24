<?php 

//exception codes 8600-8699

class thelist_model_equipmentapplication
{
	private $_equipment_application_id;
	private $_equipment_application_name;
	
	private $_equipment_application_map_id=null;
	private $_metric_mappings=null;
	private $_mapped_eq_id=null;
	
	private $_mapped_eq_type_id=null;
	private $_eq_type_application_id=null;
	private $_equipment_type_application_metrics=null;
	
	public function __construct($equipment_application_id)
	{
		$this->_equipment_application_id = $equipment_application_id;
		
		$sql = "SELECT * FROM equipment_applications
				WHERE equipment_application_id='".$this->_equipment_application_id."'
				";
			
		$equipment_application  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		$this->_equipment_application_name = $equipment_application['equipment_application_name'];
		
	}
	
	public function set_eq_type_application_id($eq_type_application_id)
	{
		$sql = "SELECT * FROM eq_type_applications eta
				WHERE eta.eq_type_application_id='".$eq_type_application_id."'
				";
		
		$equipment_type_application_map  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

		if (isset($equipment_type_application_map['eq_type_application_id'])) {
			
			$this->_mapped_eq_type_id 					= $equipment_type_application_map['eq_type_id'];
			$this->_eq_type_application_id 				= $equipment_type_application_map['eq_type_application_id'];

		} else {
			throw new exception('this application is not mapped to this equipment type', 8604);
		}
	}
	
	
	public function get_eq_type_application_id()
	{
		return $this->_eq_type_application_id;
	}
	
	public function get_mapped_eq_type_id()
	{
		return $this->_mapped_eq_type_id;
	}
	
	public function remove_eq_type_metric_map($equipment_type_application_metric_id)
	{
		if ($this->_eq_type_application_id != null) {
		
			$this->get_equipment_type_application_metrics();
		
			if ($this->_equipment_type_application_metrics != null) {
		
				foreach ($this->_equipment_type_application_metrics as $current_metric) {
		
					if ($current_metric->get_equipment_type_application_metric_id() == $equipment_type_application_metric_id) {
		
						//we found the one we want to remove
						//first we need to get rid of all allowed values
						$sql = "SELECT * FROM eq_type_allowed_metric_values
								WHERE equipment_type_application_metric_id='".$equipment_type_application_metric_id."'
								";
		
						$eq_type_allowed_metric_value_ids = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
						if (isset($eq_type_allowed_metric_value_ids['0'])) {
		
							foreach ($eq_type_allowed_metric_value_ids as $eq_type_allowed_metric_value_id) {
		
								//remove allowed values
								$current_metric->remove_eq_type_allowed_metric_value($equipment_type_application_metric_id, $eq_type_allowed_metric_value_id['eq_type_allowed_metric_value_id']);
							}
						}
							
						//now all allowed values are deleted, then delete the config map itself
						$trace 		= debug_backtrace();
						$method 	= $trace[0]["function"];
						$class		= get_class($this);
							
						Zend_Registry::get('database')->delete_single_row($equipment_type_application_metric_id, 'equipment_type_application_metrics', $class, $method);
		
						//remove it from this class
						unset($this->_equipment_type_application_metrics[$equipment_type_application_metric_id]);
						//we are done, return so we dont get to the exception
						return;
					}
				}
			}
		
			//if we make it to here we did find it and delete it
			throw new exception("you are trying to remove equipment_type_application_metric_id: ".$equipment_type_application_metric_id." from eq app: ".$this->_eq_type_application_id.", but that metric is not mapped to app type ", 8606);
			
		} else {
			throw new exception('you can not map eq type metrics to this application unless you have first filled the map', 8607);
		}
	}
	
	public function add_equipment_type_application_metric($equipment_application_metric_id)
	{
		if ($this->_eq_type_application_id != null) {
			
			$this->get_equipment_type_application_metrics();
			
			if ($this->_equipment_type_application_metrics != null) {
				
				foreach ($this->_equipment_type_application_metrics as $current_metric) {
					
					if ($current_metric->get_equipment_application_metric_id() == $equipment_application_metric_id) {
						//already exists
						return $current_metric;
					}
				}
			}

			$data = array(
		
						'eq_type_application_id'    			=>  $this->_eq_type_application_id,
						'equipment_application_metric_id'   	=>  $equipment_application_metric_id,
						'eq_type_metric_max_maps'   			=>  1,
						'eq_type_metric_default_value_1'		=>  null,
						'eq_type_metric_default_map'   			=>  0,
						'eq_type_metric_mandetory'   			=>  0,
						'eq_type_metric_allow_edit'   			=>  1,

			);
		
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
		
			$equipment_type_application_metric_id = Zend_Registry::get('database')->insert_single_row('equipment_type_application_metrics',$data,$class,$method);
		
			$this->_equipment_type_application_metrics[$equipment_type_application_metric_id] = new Thelist_Model_equipmentapplicationmetric($equipment_application_metric_id);
			$this->_equipment_type_application_metrics[$equipment_type_application_metric_id]->fill_eq_type_mapped_values($equipment_type_application_metric_id);
		
			return $this->_equipment_type_application_metrics[$equipment_type_application_metric_id];
				
		} else {
			throw new exception('you can not map eq type metrics to this application unless you have first filled the map', 8605);
		}
	}
	
	public function get_equipment_type_application_metrics()
	{
		if ($this->_mapped_eq_type_id != null) {
			
			if ($this->_equipment_type_application_metrics == null) {

				$sql = "SELECT eta.eq_type_application_id FROM eq_type_applications eta
						WHERE eta.eq_type_application_id='".$this->_eq_type_application_id."'
						AND eta.eq_type_id='".$this->_mapped_eq_type_id."'
						";
				
				$equipment_type_application_map_id  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

				
				if (isset($equipment_type_application_map_id['eq_type_application_id'])) {
					
					$sql = "SELECT etam.* FROM equipment_type_application_metrics etam
							WHERE etam.eq_type_application_id='".$equipment_type_application_map_id['eq_type_application_id']."'
							";
						
					$eq_type_app_metrics  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
					
					if (isset($eq_type_app_metrics['0'])) {
						
						foreach ($eq_type_app_metrics as $eq_type_app_metric) {
							
							$this->_equipment_type_application_metrics[$eq_type_app_metric['equipment_type_application_metric_id']] = new Thelist_Model_equipmentapplicationmetric($eq_type_app_metric['equipment_application_metric_id']);
							$this->_equipment_type_application_metrics[$eq_type_app_metric['equipment_type_application_metric_id']]->fill_eq_type_mapped_values($eq_type_app_metric['equipment_type_application_metric_id']);
						}
					}
				}
			} 
			
			return $this->_equipment_type_application_metrics;

		} else {
			throw new exception('you must first map an equipment type id to the application, before you can get the metrics', 8602);
		}
	}
	
	public function get_equipment_type_application_metric($equipment_type_application_metric_id)
	{
		$this->get_equipment_type_application_metrics();
				
		if (isset($this->_equipment_type_application_metrics[$equipment_type_application_metric_id])) {
			return $this->_equipment_type_application_metrics[$equipment_type_application_metric_id];
		} else {
			return false;
		}
	}
	
	
	public function fill_mapped_values($equipment_application_map_id)
	{
		
		$this->_equipment_application_map_id = $equipment_application_map_id;
		
		//make sure that the map provided is mapping to this type of application
		$sql = "SELECT * FROM equipment_application_mapping
				WHERE equipment_application_map_id='".$this->_equipment_application_map_id."'
				";
		
		$application_map  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		$this->_mapped_eq_id	= $application_map['eq_id'];
		
		if ($application_map['equipment_application_id'] == $this->_equipment_application_id) {
		
			$sql = "SELECT * FROM equipment_application_metric_mapping
					WHERE equipment_application_map_id='".$this->_equipment_application_map_id."'
					";
			
			$metric_maps  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			if (isset($metric_maps['0'])) {
					
				foreach($metric_maps as $metric_map) {
			
					$this->_metric_mappings[$metric_map['equipment_application_metric_map_id']] = new Thelist_Model_equipmentapplicationmetric($metric_map['equipment_application_metric_id']);
					$this->_metric_mappings[$metric_map['equipment_application_metric_map_id']]->fill_mapped_values($metric_map['equipment_application_metric_map_id']);
			
				}
			}

		} else {
			throw new exception('you are mapping using a application map id that is not tied to application of this type', 8600);
		}
	}
	
	public function create_application_metric_mapping($equipment_application_metric_id, $equipment_application_metric_index, $equipment_application_metric_value, $equipment_application_metric_group_id=null)
	{
		
		if ($this->_equipment_application_map_id != null) {

			//create the new metric, we do it here so we can use the name in any error message
			$new_metric	= new Thelist_Model_equipmentapplicationmetric($equipment_application_metric_id);
			
			$sql = 	"SELECT etam.* FROM equipment_application_mapping eam
					INNER JOIN equipments e ON e.eq_id=eam.eq_id
					INNER JOIN eq_type_applications eta ON eta.equipment_application_id=eam.equipment_application_id
					INNER JOIN equipment_type_application_metrics etam ON etam.eq_type_application_id=eta.eq_type_application_id
					WHERE eam.equipment_application_map_id='".$this->_equipment_application_map_id."'
					AND etam.equipment_application_metric_id='".$equipment_application_metric_id."'
					AND eta.eq_type_id=e.eq_type_id
					";

					$details  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
					
			if (isset($details['equipment_type_application_metric_id'])) {

				//does this equipment already have an interface config of this type?
				if ($this->_metric_mappings != null && $details['eq_type_metric_max_maps'] == 1) {
					foreach ($this->_metric_mappings as $metric) {
							
						if ($metric->get_equipment_application_metric_id() == $equipment_application_metric_id) {
							
							$metric->set_equipment_application_metric_index($equipment_application_metric_index);
							$metric->set_equipment_application_metric_group_id($equipment_application_metric_group_id);
							$metric->set_equipment_application_metric_value($equipment_application_metric_value);
							
							return $metric;
						}
					}
				}
		
				//even if we allow multiple of the same config, we dont allow them to have the exact same values, they must differ in some way
				//also if there is a metric that has null value, since that is how many of the type requirements are created, we also allow that metric to be overridden
				if ($this->_metric_mappings != null && $details['eq_type_metric_max_maps'] > 1) {
					foreach ($this->_metric_mappings as $metric) {
							
						if ($metric->get_equipment_application_metric_id() == $equipment_application_metric_id && ($metric->get_equipment_application_metric_value() == $equipment_application_metric_value) || $metric->get_equipment_application_metric_value() == null) {
							
							$metric->set_equipment_application_metric_index($equipment_application_metric_index);
							$metric->set_equipment_application_metric_group_id($equipment_application_metric_group_id);
							
							return $metric;
						}
					}
				}
			
				$number_of_existing_metrics=0;
				if ($this->_metric_mappings != null) {
					foreach ($this->_metric_mappings as $metric) {
						if ($metric->get_equipment_application_metric_id() == $equipment_application_metric_id) {
							$number_of_existing_metrics++;
						}
					}
				}
				
				$new_metric 				= new Thelist_Model_equipmentapplicationmetric($equipment_application_metric_id);
				$validate_value_metric		= $new_metric->is_value_valid($details['equipment_type_application_metric_id'], $equipment_application_metric_value);
				
				if ($validate_value_metric !== true && $equipment_application_metric_value != null) {
					throw new exception("the metric value: '".$equipment_application_metric_value."' is not valid for this eq_type_metric '".$details['equipment_type_application_metric_id']."' for application: '".$this->get_equipment_application_name()."' ", 8610);
				}

				//if no match was found we add the config if the config is allowed more than there is currently mapped
				if ($details['eq_type_metric_max_maps'] >  $number_of_existing_metrics) {
			
					$data = array(
						
								'equipment_application_map_id'    		=>  $this->_equipment_application_map_id,
								'equipment_application_metric_id'   	=>  $equipment_application_metric_id,
								'equipment_application_metric_index'   	=>  $equipment_application_metric_index,
								'equipment_application_metric_group_id'	=>  $equipment_application_metric_group_id,
								'equipment_application_metric_value'   	=>  $equipment_application_metric_value,
				
					);
				
					$trace 		= debug_backtrace();
					$method 	= $trace[0]["function"];
					$class		= get_class($this);
				
					$equipment_application_metric_map_id = Zend_Registry::get('database')->insert_single_row('equipment_application_metric_mapping',$data,$class,$method);
				
					$this->_metric_mappings[$equipment_application_metric_map_id] = new Thelist_Model_equipmentapplicationmetric($equipment_application_metric_id);
					$this->_metric_mappings[$equipment_application_metric_map_id]->fill_mapped_values($equipment_application_metric_map_id);
			
					return $this->_metric_mappings[$equipment_application_metric_map_id];
	
				} else {
					throw new exception("you are trying to map another instance of metric name: '".$new_metric->get_equipment_application_metric_name()."' to application name: '".$this->_equipment_application_name."', but the max has been reached. please edit and not add", 8608);
				}
					
			} else {
				throw new exception("this metric name: '".$new_metric->get_equipment_application_metric_name()."' is not defined for this equipment type using application name: '".$this->_equipment_application_name."' ", 8609);
			}

		} else {
			throw new exception('you can not map metrics to this application unless you have first filled the map', 8601);
		}
	}
	
	public function get_equipment_application_id()
	{
		return $this->_equipment_application_id;
	}
	
	public function get_equipment_application_name()
	{
		return $this->_equipment_application_name;
	}
	
	public function get_equipment_application_map_id()
	{
		return $this->_equipment_application_map_id;
	}
	
	public function get_metric_mappings()
	{
		if ($this->_equipment_application_map_id != null) {
			return $this->_metric_mappings;
		} else {
			throw new exception('you can not map eq type metrics to this application unless you have first filled the map', 8612);
		}
	}
	
	public function get_mapped_eq_id()
	{
		return $this->_mapped_eq_id;
	}
	
	public function get_metric_mapping($equipment_application_metric_map_id)
	{
		if (isset($this->_metric_mappings[$equipment_application_metric_map_id])) {
			return $this->_metric_mappings[$equipment_application_metric_map_id];
		} else {
			return false;
		}
	}
	
	public function remove_metric_map($equipment_application_metric_map_id, $override_mandetory=false)
	{
		$metric = $this->get_metric_mapping($equipment_application_metric_map_id);
		
		if ($metric != false) {
			
			$sql = "SELECT COUNT(equipment_application_metric_map_id) AS existing FROM equipment_application_metric_mapping
					WHERE equipment_application_metric_id='".$metric->get_equipment_application_metric_id()."'
					AND equipment_application_map_id='".$this->_equipment_application_map_id."'
					";
			
			$existing_count  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
			if ($existing_count == 1 && $override_mandetory !== true) {
				
				$sql2 = "SELECT etam.eq_type_metric_mandetory FROM equipment_application_mapping eam
						INNER JOIN eq_type_applications eta ON eta.equipment_application_id=eam.equipment_application_id
						INNER JOIN equipment_type_application_metrics etam ON etam.eq_type_application_id=eta.eq_type_application_id
						INNER JOIN equipment_application_metric_mapping eamm ON eamm.equipment_application_map_id=eam.equipment_application_map_id
						WHERE eam.equipment_application_map_id='".$this->_equipment_application_map_id."'
						AND eamm.equipment_application_metric_map_id='".$equipment_application_metric_map_id."'
						AND etam.equipment_application_metric_id=eamm.equipment_application_metric_id
						";
				
				$eq_type_metric_mandetory  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
				
				if ($eq_type_metric_mandetory != 0) {
					throw new exception("the metric map id: ".$equipment_application_metric_map_id." you are trying to delete, is the last one mapped to this application and it is mandetory, so it cannot be deleted", 8614);
				}
			}

			//now all allowed values are deleted, then delete the config map itself
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
			
			Zend_Registry::get('database')->delete_single_row($equipment_application_metric_map_id, 'equipment_application_metric_mapping', $class, $method);
			
			//remove it from this class
			unset($this->_metric_mappings[$equipment_application_metric_map_id]);
			
		} else {
			throw new exception("the metric map id: ".$equipment_application_metric_map_id." you are trying to delete is not mapped to this application", 8611);
		}
	}
	
	
}
?>