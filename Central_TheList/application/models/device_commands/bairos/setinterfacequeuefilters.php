<?php

//exception codes 5000-5099

class thelist_bairos_command_setinterfacequeuefilters implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	
	public function __construct($device, $interface)
	{
		//$interface
		//object	= interface_obj
		//string	= ['new_configs'][numerical index][configuration]
			//[queue_filter_interface_name] => eth1
          	//[queue_filter_qdisc_parent_name] => 145
            //[queue_filter_flowid_qdisc_parent] => 145
            //[queue_filter_flowid_queue_parent] => 888
            //[queue_filter_match_ip_address] => 10.10.10.0
            //[queue_filter_match_cidr_subnet_mask] => 24
            //[queue_filter_match_direction] => src

		$this->_device 					= $device;
		$this->_interface				= $interface;
	}
	
	public function execute()
	{
		
		if (is_object($this->_interface)) {
				
			$interface				= $this->_interface;
			$config_generator		= new Thelist_Bairos_config_interfaceconnectionqueuefilters($interface);
			$new_config_array		= $config_generator->generate_config_array();
		
			//now we need to get the current running config on the device interface
			$device_config			= new Thelist_Bairos_command_getinterfacequeuefilters($this->_device, $interface);
			$device_config_array	= $device_config->get_queue_filters(true, false);
				
		} elseif (is_array($this->_interface)) {
		
			$interface				= $this->_interface['interface_name'];
			$config_generator 		= new Thelist_Bairos_config_interfaceconnectionqueuefilters($interface);
			$new_config_array		= $this->_interface['new_configs'];
		
			//now we need to get the current running config on the device interface
			$device_config			= new Thelist_Bairos_command_getinterfacequeuefilters($this->_device, $interface);
			$device_config_array	= $device_config->get_queue_filters(true, false);
		}
		
		if ($device_config_array != null) {
				
			if ($new_config_array != false) {
				//find out what must be removed and what should stay
				foreach ($new_config_array as $new_index => $new_filter) {
						
					foreach ($device_config_array as $old_index => $old_filter) {
		
						//we only try to find differences if the filter names match
						if ($new_filter['configuration']['queue_filter_name'] == $old_filter['configuration']['queue_filter_name']) {
								
							//class to find diffs
							$diff			= new Thelist_Multipledevice_config_configdifferences($new_filter, $old_filter);
							$config_diffs	= $diff->generate_config_array();
							
							if ($config_diffs != false) {
								
								//queue parent
								$parent_name = $new_filter['configuration']['queue_filter_flowid_queue_parent'];

								//object model only, we want the connection queue object
								if (is_object($this->_interface)) {
									
									$filter_name	= $new_filter['configuration']['queue_filter_name'];
									$filter 		= $this->get_filter_object($parent_name, base_convert($filter_name, 16, 10));
									
								} else {
									//	string model requires the details since new and old names match we just use new
									$filter['configuration']['queue_parent_name'] = $parent_name;
									
									$k=0;
									foreach ($new_filter['configuration']['frame_match'] as $frame_match) {
										
										if (isset($frame_match['header'])) {
											$filter['configuration']['frame_matches'][$k]['header'] = $frame_match['header'];
										} else {
											throw new exception('we have a filter from the new config but there are no header match, there is an input problem', 5007);
										}
										
										if (isset($frame_match['match_value_1'])) {
											$filter['configuration']['frame_matches'][$k]['match_value_1'] = $frame_match['match_value_1'];
										}
											
										if (isset($frame_match['match_value_2'])) {
											$filter['configuration']['frame_matches'][$k]['match_value_2'] = $frame_match['match_value_2'];
										}
										$k++;
									}
								}

								//remove the old filter
								$remove_filter	= new Thelist_Bairos_command_removeconnectionqueuefilter($this->_device, $interface, $filter);
								$remove_filter->execute();
		
								//now we add the filter back with the new config
								$add_filter	= new Thelist_Bairos_command_addconnectionqueuefilter($this->_device, $interface, $filter);
								$add_filter->execute();
								
							} else {
								//do nothing the filter is perfect
							}
								
							//remove the items from the arrays so we can check that the device is in sync at the end
							unset($new_config_array[$new_index]);
							unset($device_config_array[$old_index]);
						}
					}
				}
			}
		}
		
		//now get sort out the filters that did not match anything, they need to be removed or added
		
		//remove non existing device filters first
		if (count($device_config_array) > 0) {
			
			//all of $device_config_array must be removed on device since there is nothing in the database
			foreach ($device_config_array as $remove_index => $single_dev_conf) {
		
				//we cannot be sure that any old queues are in the database
				if (is_object($this->_interface)) {
						
					try {
						
						$parent_name	 	= $single_dev_conf['configuration']['queue_filter_flowid_queue_parent'];
						$filter_name 		= $single_dev_conf['configuration']['queue_filter_name'];

						$old_filter_remove = $this->get_filter_object($parent_name, base_convert($filter_name, 16, 10));
						
					} catch (Exception $e) {
						switch($e->getCode()){
							case 5005;
							//5005, this filter is not in the database its fine, use a string method then
							$old_filter_remove['queue_parent_name'] 					= $single_dev_conf['configuration']['queue_filter_flowid_queue_parent'];
							$old_filter_remove['queue_filter_priority'] 				= $single_dev_conf['configuration']['queue_filter_priority'];
							$old_filter_remove['queue_filter_name'] 					= $single_dev_conf['configuration']['queue_filter_name'];							
							
							break;
							default;
							throw $e;
						}
					}
		
				} else {
					//string model
					$old_filter_remove['queue_parent_name'] 					= $single_dev_conf['configuration']['queue_filter_flowid_queue_parent'];
					$old_filter_remove['queue_filter_priority'] 				= $single_dev_conf['configuration']['queue_filter_priority'];
					$old_filter_remove['queue_filter_name'] 					= $single_dev_conf['configuration']['queue_filter_name'];						
				}

				//remove the old queue class
				$remove_filter	= new Thelist_Bairos_command_removeconnectionqueuefilter($this->_device, $interface, $old_filter_remove);
				$remove_filter->execute();
				unset($device_config_array[$remove_index]);
			}
		}

		//add all that dident match first
		if (count($new_config_array) > 0) {
				
			//all of $new_config_array must be added on device
			foreach ($new_config_array as $add_index => $single_new_conf) {

				//we only care about getting just one of the frame matches that will provide a match if it exists
				if (is_object($this->_interface)) {
					
					$parent_name 		= $single_new_conf['configuration']['queue_filter_flowid_queue_parent'];
					$filter_name 		= $single_new_conf['configuration']['queue_filter_name'];
					$new_filter_add 	= $this->get_filter_object($parent_name, base_convert($filter_name, 16, 10));

				} else {
					//string model
					if (isset($single_new_conf['configuration']['frame_match']['0'])) {
							
						$i=0;
						foreach ($single_new_conf['configuration']['frame_match'] as $frame_match) {
					
							$single_frame_matches[$i]['header'] = $frame_match['header'];
					
							if (isset($frame_match['match_value_1'])) {
								$single_frame_matches[$i]['match_value_1'] = $frame_match['match_value_1'];
							} 
					
							if (isset($frame_match['match_value_2'])) {
								$single_frame_matches[$i]['match_value_2'] = $frame_match['match_value_2'];
							} 
					
							$i++;
						}

						$new_filter_add['queue_parent_name'] 					= $single_new_conf['configuration']['queue_filter_flowid_queue_parent'];
						$new_filter_add['queue_filter_priority'] 				= $single_new_conf['configuration']['queue_filter_priority'];
						$new_filter_add['queue_filter_name'] 					= $single_new_conf['configuration']['queue_filter_name'];
						$new_filter_add['frame_match'] 							= $single_frame_matches;
						
						
					} else {
						throw new exception('new filter has no frame matches, thats not ok we need at least one match', 5009);
					}
				}
					
				//add the queue
				$add_queue	= new Thelist_Bairos_command_addconnectionqueuefilter($this->_device, $interface, $new_filter_add);
				$add_queue->execute();
				unset($new_config_array[$add_index]);
			}
		}
	}
	
	private function get_filter_object($parent_queue_name, $filter_name)
	{
		//object model only, we want the connection filter object
		if (is_object($this->_interface)) {
				
			$match_filter = null;
	
			$connection_queues = $this->_interface->get_connection_queues();
			//get all the queues on this interface
			if ($connection_queues != null) {
	
				foreach($connection_queues as $connection_queue) {

					//queue must match and the header field, because both have to be unique
					if ($connection_queue->get_connection_queue_name() == $parent_queue_name) {
					
						$filters = $connection_queue->get_connection_queue_filters();
						
						if ($filters != null) {
							
							foreach ($filters as $filter) {
								
								if ($filter->get_connection_queue_filter_name() == $filter_name) {
									//found it now return it
									return $filter;
								}
							}
							
						} else {
							throw new exception('we have a filter from the new config that needs to be replaced because it changed, but the connection queue says it has no filters', 5003);
						}
					}
				}
	
				//if no queue match was found
				throw new exception("we have a filter config that does not exist in the database", 5005);
				
			} else {
				throw new exception('we have a filter from the new config that needs to be replaced because it changed, but the interface says it has no connection_queues and we need that to have filters', 5001);
			}
	
		} else {
			throw new exception("only works for the object model", 5000);
		}
	}
	


}