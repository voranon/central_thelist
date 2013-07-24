<?php

//exception codes 10000-10099

class thelist_multipledevice_config_configdifferences implements Thelist_Commander_pattern_interface_ideviceconfiguration
{
	private $_new_config;
	private $_old_config;
	private $_diff_config=null;
	
	public function __construct($new_config, $old_config)
	{
		$this->_new_config 						= $new_config;
		$this->_old_config						= $old_config;
	}

	public function generate_config_array()
	{

		if (!is_array($this->_new_config) || !is_array($this->_old_config)) {
			throw new exception('old and new config must both be arrays', 10001);
		}

		//is there anything in the new config at all
		$new_config_count = count($this->_new_config['configuration']);
		
		if ($new_config_count > 0) {
		
			foreach ($this->_new_config['configuration'] as $new_config_name => $new_config) {
				
				if (!is_array($new_config)) {
					
					//is this config set on the device?
					if (isset($this->_old_config['configuration'][$new_config_name])) {
						
						//does the current value on the device match our new value
						if ($this->_old_config['configuration'][$new_config_name] == $new_config) {
							//if it matches then do nothing except unset it
							unset($this->_old_config['configuration'][$new_config_name]);
							unset($this->_new_config['configuration'][$new_config_name]);
							
						} else {
							
							//if it does not match then remove the old and in with the new
							$this->_diff_config['remove_configuration'][$new_config_name] = $this->_old_config['configuration'][$new_config_name];
							$this->_diff_config['configuration'][$new_config_name] = $new_config;
							
							//now unset them from their respective arrays, they are dealt with
							unset($this->_old_config['configuration'][$new_config_name]);
							unset($this->_new_config['configuration'][$new_config_name]);
						}
	
					} else {
						//if not set on device, add it to the items we want to push
						$this->_diff_config['configuration'][$new_config_name] = $new_config;
						unset($this->_new_config['configuration'][$new_config_name]);
					}
	
				} else {
	
					//handle the arrays here
					if (isset($this->_old_config['configuration'][$new_config_name])) {
						
						//its implied that there is something left in the device array otherwise the above would return false

						foreach ($new_config as $new_item_index => $new_item_value) {
							
							//maybe there were more new than old in the array and we ended up deleting the array during the last process
							if (isset($this->_old_config['configuration'][$new_config_name])) {

								foreach ($this->_old_config['configuration'][$new_config_name] as $device_item_index => $device_item_value) {
	
									//handle objects
									if (is_object($new_item_value)) {
										
										if (is_object($device_item_value)) {
																					
											if (get_class($new_item_value) == get_class($device_item_value) && get_class($device_item_value) == 'thelist_deviceinformation_ipaddressentry') {
												
												//if the ip and subnet mask are the same then we can remove them from the config
												if ($new_item_value->get_ip_address() == $device_item_value->get_ip_address() && $new_item_value->get_ip_subnet_cidr() == $device_item_value->get_ip_subnet_cidr()) {
												
													$this->clean_up_arrays_on_equal_values($new_config_name, $device_item_index, $new_item_index);
												}
												
											} else {
												throw new exception("in the config array: ".$new_config_name.", device: ".get_class($device_item_value)." database: ".get_class($device_item_value).", we dont handle that currently", 10004);
											}
											
										} else {
											throw new exception("in the config array: ".$new_config_name." the item from the database is an object, while the device config is a string or array", 10003);
										}
										
									} elseif (is_object($device_item_value)) {
										//if both where objects (should be), then this should have been caught above
										//the fact that if makes it to this elseif means that $new_item_value is not an object
										//while $device_item_value that is a problem.
										throw new exception("in the config array: ".$new_config_name." the item from the device is an object, while the database config is a string or array", 10002);
										
									} elseif ($new_item_value == $device_item_value) {
										
										//handle strings
										$this->clean_up_arrays_on_equal_values($new_config_name, $device_item_index, $new_item_index);
									} 
								}
							} else {
								//nothing anything remaining in the new config array will be added to the diff array below
							}
							
							
							if (isset($this->_new_config['configuration'][$new_config_name][$new_item_index])) {
	
								//with any equal values removed, if the item still exists it is because it should be added
								$this->_diff_config['configuration'][$new_config_name][] = $new_item_value;
								
								//and remove it from the original array
								unset($this->_new_config['configuration'][$new_config_name][$new_item_index]);
								
								//figure out if the original array is now empty as this was the last item, in that case remove the entire index
								$items_left3 = count($this->_new_config['configuration'][$new_config_name]);
								
								if ($items_left3 == 0) {
									//array is empty, now remove it
									unset($this->_new_config['configuration'][$new_config_name]);
								} 
							} 
						}
					} else {
						
						//if the array is not even set on the device we include the entire array to the push result
						$this->_diff_config['configuration'][$new_config_name] = $new_config;
						unset($this->_new_config['configuration'][$new_config_name]);
					}
				}
			}
		}
		
		//was there anything in the device config? or is there anything left
		$device_config_count = count($this->_old_config['configuration']);
		
		if ($device_config_count > 0) {
			
			foreach ($this->_old_config['configuration'] as $device_remove_index => $device_remove_value) {
				$this->_diff_config['remove_configuration'][$device_remove_index] = $device_remove_value;
				
				//remove it 
				unset($this->_old_config['configuration'][$device_remove_index]);
			}
		}
		
		//now we should have 2 empty arrays and one with the changes
		$device_config_after_count		= count($this->_old_config['configuration']);
		$new_config_after_count			= count($this->_new_config['configuration']);

		if ($device_config_after_count != 0 || $new_config_after_count != 0) {
			throw new exception("after comparing the new and old arrays there are still items left, this will be a config issue for a device", 10001);
		}
		
		if ($this->_diff_config != null) {
			return $this->_diff_config;
		} else {
			return false;
		}
	}
	
	private function clean_up_arrays_on_equal_values($config_name, $old_index, $new_index)
	{
		//because there are seperate rules for arrays carrying objects 
		//and arrays of strings i have chosen to move the array cleanup to a seperate function
		
		//if we have values that match then just eliminate them from both arrays
		unset($this->_old_config['configuration'][$config_name][$old_index]);
		unset($this->_new_config['configuration'][$config_name][$new_index]);
			
		//figure out if the original array is now empty as this was the last item, in that case remove the entire index
		//for the new config
		$items_left1 = count($this->_new_config['configuration'][$config_name]);
		
		if ($items_left1 == 0) {
			//array is empty, now remove it
			unset($this->_new_config['configuration'][$config_name]);
		}
			
		//figure out if the original array is now empty as this was the last item, in that case remove the entire index
		//for the device config
		$items_left2 = count($this->_old_config['configuration'][$config_name]);
		
		if ($items_left2 == 0) {
			//array is empty, now remove it
			unset($this->_old_config['configuration'][$config_name]);
		}
	}
	
	public function generate_config_device_syntax($config_array)
	{
		throw new exception('this is a general multi device function, i cannot generate specific syntax', 10000);
	}
}