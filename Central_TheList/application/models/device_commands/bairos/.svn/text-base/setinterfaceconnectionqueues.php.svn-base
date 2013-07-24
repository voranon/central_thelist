<?php

//exception codes 14600-14699

class thelist_bairos_command_setinterfaceconnectionqueues implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_interface;

	public function __construct($device, $interface)
	{
		//$interface
		//object	= interface_obj
		//string	= ['new_configs'][numerical index] 
			//root example
			//[queue_interface_name] => eth1
            //[queue_method] => htb
            //[queue_type] => root
            //[queue_name] => 888
            //[queue_qdisc_parent_name] => 145
            //[queue_sla_rate] => 5500
            //[queue_ceil_rate] => 5500
            
			//child example
			//[queue_interface_name] => eth1
			//[queue_type] => child
			//[queue_parent_name] => 56465
			//[queue_name] => 888
			//[queue_qdisc_parent_name] => 145
			//[queue_sla_rate] => 9000
			//[queue_ceil_rate] => 10000
			//[queue_method] => htb
            
		//string	= ['interface_name'] = interface name
		
		$this->_device 					= $device;
		$this->_interface 				= $interface;
	}
	
	public function execute()
	{
		if (is_object($this->_interface)) {
			
			$interface				= $this->_interface;
			$config_generator		= new Thelist_Bairos_config_interfaceconnectionqueues($interface);
			$new_config_array		= $config_generator->generate_config_array();

			//now we need to get the current running config on the device interface
			$device_config			= new Thelist_Bairos_command_getinterfaceconnectionqueues($this->_device, $interface);
			$device_config_array	= $device_config->get_queues(true, false);
			
		} elseif (is_array($this->_interface)) {

			$interface				= $this->_interface['interface_name'];
			$config_generator		= new Thelist_Bairos_config_interfaceconnectionqueues($interface);
			$new_config_array		= $this->_interface['new_configs'];

			//now we need to get the current running config on the device interface
			$device_config			= new Thelist_Bairos_command_getinterfaceconnectionqueues($this->_device, $interface);
			$device_config_array	= $device_config->get_queues(true, false);
		}

		if ($device_config_array != null) {
			
			if ($new_config_array != false) {
				//find out what must be removed and what should stay
				foreach ($new_config_array as $new_index => $new_queue) {
					
					foreach ($device_config_array as $old_index => $old_queue) {

						//we only try to find differences if the queue_names match
						if ($new_queue['configuration']['queue_name'] == $old_queue['configuration']['queue_name']) {
							
							//class to find diffs
							$diff			= new Thelist_Multipledevice_config_configdifferences($new_queue, $old_queue);
							$config_diffs	= $diff->generate_config_array();
							
							if ($config_diffs != false) {
								
								//reset the variable
								$queue = null;
								
								//object model only, we want the connection queue object
								if (is_object($this->_interface)) {
									$queue = $this->get_queue_object($new_queue['configuration']['queue_name']);
								} else {
									//	string model the name of the queue is enough, since new and old names match we just use new
									$queue = $new_queue['configuration']['queue_name'];
								}

								//remove the old queue
								$remove_queue	= new Thelist_Bairos_command_removeconnectionqueue($this->_device, $interface, $queue);
								$remove_queue->execute();

								//now we add the queues back with the new config 
								$add_queue	= new Thelist_Bairos_command_addconnectionqueue($this->_device, $interface, $queue);
								$add_queue->execute();
								
								//start connection queue, then start all its children recursively
								$add_queue->add_child_queues_recursively();

							} else {
								//do nothing the queue is perfect
							}
							
							//remove the items from the arrays so we can check that the device is in sync at the end
							unset($new_config_array[$new_index]);
							unset($device_config_array[$old_index]);
						}
					}
				}
			}
		}
				
		//now get sort out the queues that did not match anything, they need to be removed or added

		if (count($device_config_array) > 0) {
			
			//all of $device_config_array must be removed on device since there is nothing in the database
			foreach ($device_config_array as $remove_index => $single_dev_conf) {

				//we cannot be sure that any old queues are in the database
				if (is_object($this->_interface)) {
					
					try {
						$queue = $this->get_queue_object($single_dev_conf['configuration']['queue_name']);	
					} catch (Exception $e) {
						switch($e->getCode()){
							case 14602;
							//14602, this queue is not in the database its fine we just use the name then
							$queue = $single_dev_conf['configuration']['queue_name'];
							break;
							default;
							throw $e;
						}
					}

				} else {
					//string model the name of the queue is enough, since new and old names match we just use new
					$queue = $single_new_conf['configuration']['queue_name'];
				}

				//remove the old queue class
				$remove_queue	= new Thelist_Bairos_command_removeconnectionqueue($this->_device, $interface, $queue);
				$remove_queue->execute();
				unset($device_config_array[$remove_index]);
			}
		}

		if (count($new_config_array) > 0) {
			
			//all of $new_config_array must be added on device
			foreach ($new_config_array as $add_index => $single_new_conf) {
					
				if (is_object($this->_interface)) {
					$queue = $this->get_queue_object($single_new_conf['configuration']['queue_name']);
				} else {
					//string model the name of the queue is enough, since new and old names match we just use new
					$queue = $single_new_conf['configuration']['queue_name'];
				}
					
				//add the queue
				$add_queue	= new Thelist_Bairos_command_addconnectionqueue($this->_device, $interface, $queue);
				$add_queue->execute();
				
				//then start all its children recursively
				$add_queue->add_child_queues_recursively();
				unset($new_config_array[$add_index]);
			}
		}
	}
	
	private function get_queue_object($queue_name)
	{
		//object model only, we want the connection queue object
		if (is_object($this->_interface)) {
			
			$queue = null;
				
			$connection_queues = $this->_interface->get_connection_queues();
			//get all the queues on this interface
			if ($connection_queues != null) {
		
				foreach($connection_queues as $connection_queue) {
						
					if ($connection_queue->get_connection_queue_name() == $queue_name) {
		
						if ($queue == null) {
							$queue = $connection_queue;
						} else {
							throw new exception("we have atleast 2 queues on the same interface with the same name, that should not be possible, name should be unique for queues per interface  ", 14601);
						}
					}
				}
		
				//if no queue match was found
				if ($queue == null) {
					throw new exception("we have a queue config with a name that does not exist in the database", 14602);
				} else {
					//return the found object
					return $queue;
				}
		
			} else {
				throw new exception('we have a queue from the new config that needs to be replaced because it changed, but the interface says it has no queues', 14600);
			}

		} else {
			throw new exception("only works for the object model", 14603);
		}
	}
	
}