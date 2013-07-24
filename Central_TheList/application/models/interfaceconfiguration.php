<?php 

//exception codes 7800-7899

class thelist_model_interfaceconfiguration
{
	private $_if_conf_id;
	private $_if_conf_name;
	private $_if_conf_desc;
	private $_if_conf_value_datatype;
		
	private $_mapped_if_conf_map_id=null;
	private $_mapped_if_id=null;
	private $_mapped_if_conf_value_1=null;

	public function __construct($if_conf_id)
	{
		$this->_if_conf_id = $if_conf_id;

		$if_config = Zend_Registry::get('database')->get_interface_configurations()->fetchRow('if_conf_id='.$this->_if_conf_id);

		$this->_if_conf_name					= $if_config['if_conf_name'];
		$this->_if_conf_desc					= $if_config['if_conf_desc'];
		$this->_if_conf_value_datatype			= $if_config['if_conf_value_datatype'];
		
	}
	
	public function get_if_conf_id()
	{
		return $this->_if_conf_id;
	}
	public function get_if_conf_name()
	{
		return $this->_if_conf_name;
	}
	public function get_if_conf_desc()
	{
		return $this->_conf_desc;
	}
	public function get_if_conf_value_datatype()
	{
		return $this->_if_conf_value_datatype;
	}
	
	public function fill_mapped_values($if_conf_map_id)
	{
		$if_map_config = Zend_Registry::get('database')->get_interface_configuration_mapping()->fetchRow('if_conf_map_id='.$if_conf_map_id);

		if (isset($if_map_config['if_conf_map_id'] )) {
		
			if ($if_map_config['if_conf_id'] == $this->_if_conf_id) {
				
				$this->_mapped_if_conf_map_id			= $if_map_config['if_conf_map_id'];
				$this->_mapped_if_id					= $if_map_config['if_id'];
				$this->_mapped_if_conf_value_1			= $if_map_config['if_conf_value_1'];
				
			} else {
				
				throw new exception('you are trying to fill the interfaceconfiguration with a map that is not of this conf id', 7800);
				
			}
		} else {
			
			throw new exception('mapped if conf id does not exist', 7801);
			
		}
	}
	
	public function get_mapped_if_conf_map_id()
	{
		if ($this->_mapped_if_conf_map_id != null) {
			return $this->_mapped_if_conf_map_id;
		} else {
			return false;
		}
	}
	
	public function get_mapped_if_id()
	{
		if ($this->_mapped_if_id != null) {
			return $this->_mapped_if_id;
		} else {
			return false;
		}
	}
	
	public function get_mapped_configuration_value_1()
	{
		if ($this->_mapped_if_conf_map_id != null) {
			return $this->_mapped_if_conf_value_1;
		} else {
			throw new exception('interface config must first be mapped', 7803);
		}
	}
	
	public function set_mapped_configuration_type_default_value_1($if_type_id, $if_conf_default_value_1)
	{
	
		$sql = "SELECT * FROM interface_type_configurations
				WHERE if_type_id='".$if_type_id."'
				AND if_conf_id='".$this->_if_conf_id."'
				";
			
		$interface_type_configuration = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

		if (isset($interface_type_configuration['interface_type_configuration_id'])) {

			if ($interface_type_configuration['if_conf_default_value_1'] != $if_conf_default_value_1) {

				//null is allowed for default value regardless of the allow values
				if ($if_conf_default_value_1 != null) {
				
					$valid_default	= $this->is_value_valid($if_type_id, $if_conf_default_value_1);
						
					if ($valid_default !== true) {
						throw new exception("Default value '".$if_conf_default_value_1."' for Interface Configuration: '".$this->get_if_conf_name()."' is not allowed on interface type id ".$if_type_id." using if_conf_id: ".$this->_if_conf_id."", 7826);
					}
				}
				
				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);
					
				Zend_Registry::get('database')->set_single_attribute($interface_type_configuration['interface_type_configuration_id'], 'interface_type_configurations', 'if_conf_default_value_1', $if_conf_default_value_1, $class, $method);
			}

		} else {
			throw new exception("Configuration is not defined for this interface type", 7825);
		}

	}
	
	public function set_if_conf_max_maps($if_type_id, $if_conf_max_maps)
	{
		$sql = "SELECT * FROM interface_type_configurations
					WHERE if_type_id='".$if_type_id."'
					AND if_conf_id='".$this->_if_conf_id."'
					";
			
		$interface_type_configuration = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
	
		if (isset($interface_type_configuration['interface_type_configuration_id'])) {

			if ($interface_type_configuration['if_conf_max_maps'] != $if_conf_max_maps) {
					
				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);

				Zend_Registry::get('database')->set_single_attribute($interface_type_configuration['interface_type_configuration_id'], 'interface_type_configurations', 'if_conf_max_maps', $if_conf_max_maps, $class, $method);
			}

		} else {
			throw new exception("Configuration is not defined for this interface type", 7826);
		}
	}
	
	public function set_if_conf_default_map($if_type_id, $if_conf_default_map)
	{
		$sql = "SELECT * FROM interface_type_configurations
						WHERE if_type_id='".$if_type_id."'
						AND if_conf_id='".$this->_if_conf_id."'
						";
			
		$interface_type_configuration = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
	
		if ($if_conf_default_map == 0 || $if_conf_default_map == 1) {
			
			if (isset($interface_type_configuration['interface_type_configuration_id'])) {
			
				if ($interface_type_configuration['if_conf_default_map'] != $if_conf_default_map) {
						
					$trace 		= debug_backtrace();
					$method 	= $trace[0]["function"];
					$class		= get_class($this);
			
					Zend_Registry::get('database')->set_single_attribute($interface_type_configuration['interface_type_configuration_id'], 'interface_type_configurations', 'if_conf_default_map', $if_conf_default_map, $class, $method);
				}
			
			} else {
				throw new exception("Configuration is not defined for this interface type", 7826);
			}
		} else {
				throw new exception("if_conf_default_map must be either 0 or 1", 7827);
		}
	}
	
	public function set_if_conf_mandetory($if_type_id, $if_conf_mandetory)
	{
		$sql = "SELECT * FROM interface_type_configurations
				WHERE if_type_id='".$if_type_id."'
				AND if_conf_id='".$this->_if_conf_id."'
				";
			
		$interface_type_configuration = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
	
		if ($if_conf_mandetory == 0 || $if_conf_mandetory == 1) {
				
			if (isset($interface_type_configuration['interface_type_configuration_id'])) {
					
				if ($interface_type_configuration['if_conf_mandetory'] != $if_conf_mandetory) {
					
					$trace 		= debug_backtrace();
					$method 	= $trace[0]["function"];
					$class		= get_class($this);
						
					Zend_Registry::get('database')->set_single_attribute($interface_type_configuration['interface_type_configuration_id'], 'interface_type_configurations', 'if_conf_mandetory', $if_conf_mandetory, $class, $method);
					
					//also make sure the default map is set to 1, since mandetory implies default
					$this->set_if_conf_default_map($if_type_id, 1);
				}
					
			} else {
				throw new exception("Configuration is not defined for this interface type", 7829);
			}
		} else {
			throw new exception("if_conf_mandetory must be either 0 or 1", 7828);
		}
	}
	
	public function set_if_type_conf_allow_edit($if_type_id, $if_type_conf_allow_edit)
	{
		$sql = "SELECT * FROM interface_type_configurations
					WHERE if_type_id='".$if_type_id."'
					AND if_conf_id='".$this->_if_conf_id."'
					";
			
		$interface_type_configuration = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
	
		if ($if_type_conf_allow_edit == 0 || $if_type_conf_allow_edit == 1) {
	
			if (isset($interface_type_configuration['interface_type_configuration_id'])) {
					
				if ($interface_type_configuration['if_type_conf_allow_edit'] != $if_type_conf_allow_edit) {
	
					$trace 		= debug_backtrace();
					$method 	= $trace[0]["function"];
					$class		= get_class($this);
	
					Zend_Registry::get('database')->set_single_attribute($interface_type_configuration['interface_type_configuration_id'], 'interface_type_configurations', 'if_type_conf_allow_edit', $if_type_conf_allow_edit, $class, $method);
				}
					
			} else {
				throw new exception("Configuration is not defined for this interface type", 7830);
			}
		} else {
			throw new exception("if_type_conf_allow_edit must be either 0 or 1", 7831);
		}
	}
	
	public function set_mapped_configuration_value_1($if_conf_value_1)
	{
		if ($this->_mapped_if_conf_map_id != null && $this->_mapped_if_id != null) {

			if ($this->_mapped_if_conf_value_1 != $if_conf_value_1) {
				
				$sql = "SELECT if_type_id FROM interfaces
						WHERE if_id='".$this->_mapped_if_id."'
						";
				
				$if_type_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
				
				$sql2 = "SELECT * FROM interface_type_configurations 
						WHERE if_conf_id='".$this->_if_conf_id."'
						AND if_type_id='".$if_type_id."'
						";
				
				$if_type_conf_detail = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql2);
				
				//we dont enforce no edit on null values, since they have not been filled yet
				if ($if_type_conf_detail['if_type_conf_allow_edit'] == 1 || $this->_mapped_if_conf_value_1 == null) {
					
					$allowed	= $this->is_value_valid($if_type_id, $if_conf_value_1);
					
					if ($allowed === true) {
					
						$trace 		= debug_backtrace();
						$method 	= $trace[0]["function"];
						$class		= get_class($this);
							
						Zend_Registry::get('database')->set_single_attribute($this->_mapped_if_conf_map_id, 'interface_configuration_mapping', 'if_conf_value_1', $if_conf_value_1, $class, $method);
							
						$this->_mapped_if_conf_value_1 = $if_conf_value_1;
							
					} else {
						throw new exception("Configuration is not defined for this interface type or value '".$if_conf_value_1."' for Interface Configuration: '".$this->get_if_conf_name()."' is not allowed on interface type id ".$if_type_id['if_type_id']." if_conf_id: ".$this->_if_conf_id." ", 7804);
					}
					
				} else {
					throw new exception("Interface Configuration: '".$this->get_if_conf_name()."' does not support edit, for if_type_id: ".$if_type_id." and if_conf_id: ".$this->_if_conf_id." ", 7804);
				}
			}
			
		} else {
			throw new exception('interface config must first be mapped', 7802);
		}
	}
	
	public function is_value_valid($if_type_id, $value)
	{
		$valid_configs	= $this->get_valid_configuration_value_1($if_type_id);

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
			throw new exception("un expected result for if_type_id: ".$if_type_id." for if_conf_id: ".$this->_if_conf_id."", 7806);
		}
	}
	
	public function get_max_conf_maps($if_type_id)
	{
		$sql = "SELECT * FROM interface_type_configurations ifc
				WHERE ifc.if_type_id='".$if_type_id."'
				AND ifc.if_conf_id='".$this->_if_conf_id."'
				";
		
		$max_maps = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		if (isset($max_maps['if_conf_max_maps'])) {
			return $max_maps['if_conf_max_maps'];
		} else {
			return false;
		}
	}
	
	public function remove_if_type_config_allowed_value($interface_type_configuration_id, $if_type_allowed_config_value_id)
	{
		$sql = "SELECT COUNT(if_type_allowed_config_value_id) FROM interface_type_configurations ifc
				INNER JOIN interface_type_allowed_config_values itacv ON itacv.interface_type_configuration_id=ifc.interface_type_configuration_id
				WHERE ifc.interface_type_configuration_id='".$interface_type_configuration_id."'
				AND itacv.if_type_allowed_config_value_id='".$if_type_allowed_config_value_id."'
				AND ifc.if_conf_id='".$this->_if_conf_id."'
					";
	
		$exists = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
	
		if ($exists == 1) {
			
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
				
			Zend_Registry::get('database')->delete_single_row($if_type_allowed_config_value_id, 'interface_type_allowed_config_values', $class, $method);
			
		} else {
			throw new exception("you are trying to remove a allowed value that is not mapped. if_type_allowed_config_value_id: '".$if_type_allowed_config_value_id."' for Interface Configuration: '".$this->get_if_conf_name()."' on interface_type_configuration_id: ".$interface_type_configuration_id." for if_conf_id: ".$this->_if_conf_id." ", 7804);
		}
	}
	
	public function update_if_type_config_allowed_value($interface_type_configuration_id, $if_type_allowed_config_value_id, $start_value, $end_value=null)
	{
		
		$sql = "SELECT COUNT(if_type_allowed_config_value_id) FROM interface_type_configurations ifc
				INNER JOIN interface_type_allowed_config_values itacv ON itacv.interface_type_configuration_id=ifc.interface_type_configuration_id
				WHERE ifc.interface_type_configuration_id='".$interface_type_configuration_id."'
				AND itacv.if_type_allowed_config_value_id='".$if_type_allowed_config_value_id."'
				AND ifc.if_conf_id='".$this->_if_conf_id."'
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
						Zend_Registry::get('database')->set_single_attribute($if_type_allowed_config_value_id, 'interface_type_allowed_config_values', 'if_type_allowed_config_value_start', $start_value, $class, $method);
						//update end value
						Zend_Registry::get('database')->set_single_attribute($if_type_allowed_config_value_id, 'interface_type_allowed_config_values', 'if_type_allowed_config_value_end', $end_value, $class, $method);

					} else {
						throw new exception("you are trying to update an allowed value with a range, but the start value is smaller than the end value", 7823);
					}

				} else {
					throw new exception("you are trying to update an allowed value with a range, but the values are not numeric, you must use numeric values for a range", 7821);
				}
			} else {
				
				//no end value
				//update start value
				Zend_Registry::get('database')->set_single_attribute($if_type_allowed_config_value_id, 'interface_type_allowed_config_values', 'if_type_allowed_config_value_start', $start_value, $class, $method);
				//update end value to null
				Zend_Registry::get('database')->set_single_attribute($if_type_allowed_config_value_id, 'interface_type_allowed_config_values', 'if_type_allowed_config_value_end', null, $class, $method);
			}
			
		} else {
			throw new exception("you are trying to update a allowed value that is not mapped. if_type_allowed_config_value_id: '".$if_type_allowed_config_value_id."' for Interface Configuration: '".$this->get_if_conf_name()."' on interface_type_configuration_id: ".$interface_type_configuration_id." for if_conf_id: ".$this->_if_conf_id." ", 7822);
		}
	}
	
	public function add_if_type_config_allowed_value($interface_type_configuration_id, $start_value, $end_value)
	{
	
		$sql = "SELECT COUNT(ifc.interface_type_configuration_id) FROM interface_type_configurations ifc
					WHERE ifc.interface_type_configuration_id='".$interface_type_configuration_id."'
					AND ifc.if_conf_id='".$this->_if_conf_id."'
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
						
									'interface_type_configuration_id'					=>  $interface_type_configuration_id,
									'if_type_allowed_config_value_start'				=>  $start_value,
									'if_type_allowed_config_value_end' 					=>	$end_value,  
						
						);
	
						//update start value
						return Zend_Registry::get('database')->insert_single_row('interface_type_allowed_config_values',$data, $class,$method);
						
					} else {
						throw new exception("you are trying to update an allowed value with a range, but the start value is smaller than the end value", 7824);
					}
	
				} else {
					throw new exception("you are trying to update an allowed value with a range, but the values are not numeric, you must use numeric values for a range", 7825);
				}
			} else {
	
				//no end value
				$data = array(
						
									'interface_type_configuration_id'					=>  $interface_type_configuration_id,
									'if_type_allowed_config_value_start'				=>  $start_value,
									'if_type_allowed_config_value_end' 					=>	null,
						
						);

				return Zend_Registry::get('database')->insert_single_row('interface_type_allowed_config_values',$data, $class,$method);
			}
				
		} else {
			throw new exception("you are trying to add an allowed value to a config that does not exist: Interface Configuration: '".$this->get_if_conf_name()."' on interface_type_configuration_id: ".$interface_type_configuration_id." for if_conf_id: ".$this->_if_conf_id." ", 7826);
		}
	}

	public function get_valid_configuration_value_1($if_type_id)
	{

		$sql = "SELECT * FROM interface_type_configurations ifc
				INNER JOIN interface_type_allowed_config_values itacv ON itacv.interface_type_configuration_id=ifc.interface_type_configuration_id
				WHERE ifc.if_type_id='".$if_type_id."'
				AND ifc.if_conf_id='".$this->_if_conf_id."'
				";
		
		$config_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		if (isset($config_types['0'])) {
				
			foreach ($config_types as $config_type) {
				
				if ($config_type['if_type_allowed_config_value_start'] != null && $config_type['if_type_allowed_config_value_end'] == null) {
					
					if ($config_type['if_type_allowed_config_value_start'] == $config_type['if_conf_default_value_1'] && $config_type['if_conf_default_value_1'] != null) {
						$default_value = $config_type['if_type_allowed_config_value_start'];
					} else {
						
						//else add it to the array
						$return_array[]	=		$config_type['if_type_allowed_config_value_start'];
					}

				} elseif ($config_type['if_type_allowed_config_value_start'] != null && $config_type['if_type_allowed_config_value_end'] != null) {
				
					//we can currently only do ranges from numbers, but expand in the future
					if (is_numeric($config_type['if_type_allowed_config_value_start']) && is_numeric($config_type['if_type_allowed_config_value_end'])) {
						
						$all_allowed_values	= range($config_type['if_type_allowed_config_value_start'], $config_type['if_type_allowed_config_value_end']);
							
							foreach($all_allowed_values as $allowed_value) {
								
								if ($allowed_value == $config_type['if_conf_default_value_1'] && $config_type['if_conf_default_value_1'] != null) {
									$default_value = $allowed_value;
								} else {
									//else add it to the array
									$return_array[]	=		$allowed_value;
								}
							}

					} else {
						throw new exception("start or end configuration values for if_type_id: ".$if_type_id." for if_conf_id: ".$this->_if_conf_id." are not numeric, that is an input issue somewhere", 7805);
					}
					
				} elseif ($config_type['if_type_allowed_config_value_start'] == null && $config_type['if_type_allowed_config_value_end'] == null) {
					//all values are allowed
					return true;
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
	
	
	
	
	//here are the validations, not working yet
	
	
	
	
	public function validate_allow_vlan_id_to_trunk($vlan_id)
	{
		if (is_numeric($vlan_id)) {
	
			//validate that the port is in trunk mode
			$if_mode = $this->get_interface_configuration(23);
			if ($if_mode['0']->get_mapped_configuration_value_1() != 'trunk') {
				throw new exception("you cannot allow vlans to trunk on an interface if it is not in trunk mode first", 7807);
			}
				
		} else {
			throw new exception('vlan ids must be numeric', 7812);
		}
	}
	
	public function validate_native_vlan_id($vlan_id)
	{
		if (is_numeric($vlan_id)) {
	
			//placing validation here so as not to confuse, if interface does not even qualify for native vlans
			$if_mode = $this->get_interface_configuration(23);
			if ($if_mode['0']->get_mapped_configuration_value_1() != 'trunk') {
				throw new exception("you cannot set the native vlan on this interface if it is not in trunk mode first ", 7808);
			}
				
		} else {
			throw new exception('vlan ids must be numeric', 7809);
		}
	}
	
	public function validate_vlan_id($vlan_id)
	{
		if (is_numeric($vlan_id)) {
				
			if ($this->get_if_type()->get_if_type_id() == 95) {
	
			} else {
				throw new exception('vlan ids can only be set on vlan interfaces', 7810);
			}
				
		} else {
			throw new exception('vlan ids must be numeric', 7811);
		}
	}
	
	public function validate_layer_3_mtu($mtu)
	{
		if (is_numeric($mtu)) {
	
			//validate that if this interface has a layer 2 mtu it has not been exceeded
			$if_l2_mtu = $this->get_interface_configuration(30);
			if ($if_l2_mtu == false) {
				//if the l2 mtu is not set, we go ahead and allow the l3 mtu to be set
	
			} elseif ($if_l2_mtu['0']->get_mapped_configuration_value_1() < $mtu) {
				throw new exception("you cannot set the layer 3 mtu that is smaller than the current layer 2 mtu ", 1060);
			}
				
			//now figure out if this interface is a slave to any other interfaces (vlan, nstreame dual, ether channel etc)
			//if it is we need to make sure that we are not exceeding its l3 MTU (or L2 if L3 is not set)
			//L2 is always >= L3
			$slave_relationships = $this->get_slave_relationships();
				
			if ($slave_relationships != null) {
	
				foreach ($slave_relationships as $master_if) {
						
					//get L3 mtu of master
					$master_l3_mtu = $master_if->get_interface_configuration(1);
						
					if ($master_l3_mtu != false) {
	
						if ($master_l3_mtu['0']->get_mapped_configuration_value_1() < $mtu) {
							throw new exception("the L3 MTU of: ".$mtu." will make this slave interface exceed its masters L3 mtu of if_id: ".$master_if->get_if_id().", L3 MTU = ".$master_l3_mtu['0']->get_mapped_configuration_value_1()." ", 1062);
						}
	
					} else {
	
						//get L2 mtu of master if it did not have an L3
						$master_l2_mtu = $master_if->get_interface_configuration(30);
	
						if ($master_l2_mtu != false) {
	
							if ($master_l2_mtu['0']->get_mapped_configuration_value_1() < $mtu) {
								throw new exception("the L3 MTU of: ".$mtu." will make this slave interface exceed its masters L2 mtu of if_id: ".$master_if->get_if_id().", L2 MTU = ".$master_l2_mtu['0']->get_mapped_configuration_value_1()." ", 1063);
							}
						}
					}
				}
			}
				
			//now figure out if this interface is a master to any other interfaces (vlan, nstreame dual, ether channel etc)
			//if it is we need to make sure that we are not setting an mtu that is lower than the minimum of their l2 MTU (or L3 if L2 is not set)
			//L2 is always >= L3
			$master_relationships = $this->get_master_relationships();
				
			if ($master_relationships != null) {
					
				foreach ($master_relationships as $slave_if) {
						
					//get L2 mtu of slave
					$slave_l2_mtu = $slave_if->get_interface_configuration(30);
						
					if ($slave_l2_mtu != false) {
							
						if ($slave_l2_mtu['0']->get_mapped_configuration_value_1() > $mtu) {
							throw new exception("the L3 MTU of: ".$mtu." will make this master interface smaller than its slaves L2 mtu of if_id: ".$slave_if->get_if_id().", L2 MTU = ".$slave_l2_mtu['0']->get_mapped_configuration_value_1()." ", 1064);
						}
							
					} else {
							
						//get L3 mtu of slave if it did not have an L2
						$slave_l3_mtu = $slave_if->get_interface_configuration(1);
							
						if ($slave_l3_mtu != false) {
	
							if ($slave_l3_mtu['0']->get_mapped_configuration_value_1() > $mtu) {
								throw new exception("the L3 MTU of: ".$mtu." will make this master interface smaller its slaves L3 mtu of if_id: ".$slave_if->get_if_id().", L3 MTU = ".$slave_l3_mtu['0']->get_mapped_configuration_value_1()." ", 1065);
							}
						}
					}
				}
			}
	
		} else {
			throw new exception('mtu must be numeric', 1030);
		}
	}
	
	public function validate_layer_2_mtu($mtu)
	{
		if (is_numeric($mtu)) {
	
			$if_l3_mtu = $this->get_interface_configuration(1);
			if ($if_l3_mtu == false) {
				//if the l3 mtu is not set, we go ahead and allow the l2 mtu to be set
					
			} elseif ($if_l3_mtu['0']->get_mapped_configuration_value_1() > $mtu) {
				throw new exception("you cannot set the layer 2 mtu that is smaller than the current layer 3 mtu ", 1061);
			}
				
			//now figure out if this interface is a slave to any other interfaces (vlan, nstreame dual, ether channel etc)
			//if it is we need to make sure that we are not exceeding its l2 MTU (or L3 if L2 is not set)
			//L2 is always >= L3
			$slave_relationships = $this->get_slave_relationships();
				
			if ($slave_relationships != null) {
					
				foreach ($slave_relationships as $master_if) {
						
					//get L2 mtu of master
					$master_l2_mtu = $master_if->get_interface_configuration(30);
						
					if ($master_l2_mtu != false) {
							
						if ($master_l2_mtu['0']->get_mapped_configuration_value_1() < $mtu) {
							throw new exception("the L2 MTU of: ".$mtu." will make this slave interface exceed its masters L2 mtu of if_id: ".$master_if->get_if_id().", L32MTU = ".$master_l2_mtu['0']->get_mapped_configuration_value_1()." ", 1066);
						}
							
					} else {
							
						//get L3 mtu of master if it did not have an L2
						$master_l3_mtu = $master_if->get_interface_configuration(1);
							
						if ($master_l3_mtu != false) {
	
							if ($master_l3_mtu['0']->get_mapped_configuration_value_1() < $mtu) {
								throw new exception("the L2 MTU of: ".$mtu." will make this slave interface exceed its masters L3 mtu of if_id: ".$master_if->get_if_id().", L3 MTU = ".$master_l3_mtu['0']->get_mapped_configuration_value_1()." ", 1067);
							}
						}
					}
				}
			}
				
			//now figure out if this interface is a master to any other interfaces (vlan, nstreame dual, ether channel etc)
			//if it is we need to make sure that we are not setting an mtu that is lower than the minimum of their l3 MTU (or L2 if L3 is not set)
			//L2 is always >= L3
			$master_relationships = $this->get_master_relationships();
				
			if ($master_relationships != null) {
					
				foreach ($master_relationships as $slave_if) {
						
					//get L3 mtu of slave
					$slave_l3_mtu = $slave_if->get_interface_configuration(1);
						
					if ($slave_l3_mtu != false) {
							
						if ($slave_l3_mtu['0']->get_mapped_configuration_value_1() > $mtu) {
							throw new exception("the L2 MTU of: ".$mtu." will make this master interface smaller than its slaves L3 mtu of if_id: ".$slave_if->get_if_id().", L3 MTU = ".$slave_l3_mtu['0']->get_mapped_configuration_value_1()." ", 1068);
						}
							
					} else {
							
						//get L2 mtu of slave if it did not have an L3
						$slave_l2_mtu = $slave_if->get_interface_configuration(30);
							
						if ($slave_l2_mtu != false) {
	
							if ($slave_l2_mtu['0']->get_mapped_configuration_value_1() > $mtu) {
								throw new exception("the L2 MTU of: ".$mtu." will make this master interface smaller its slaves L2 mtu of if_id: ".$slave_if->get_if_id().", L2 MTU = ".$slave_l2_mtu['0']->get_mapped_configuration_value_1()." ", 1069);
							}
						}
					}
				}
			}
				
		} else {
			throw new exception('mtu must be numeric', 1059);
		}
	}
	
	public function validate_ssid($ssid)
	{
		//change this to validate the ssid fulfills the RFC
		if (isset($ssid)) {
	
		} else {
			throw new exception('ssid must have validations, make them', 1031);
		}
	}
	
	
	

}
?>