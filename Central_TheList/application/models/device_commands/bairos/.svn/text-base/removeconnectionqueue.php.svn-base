<?php

//exception codes 4700-4799 

class thelist_bairos_command_removeconnectionqueue implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	private $_connection_queue;
	
	private $_get_connection_queues=null;
	private $_original_connection_queue=null;

	public function __construct($device, $interface, $connection_queue)
	{
		//$interface
		//object	= interface_obj
		//string	= interface_name
		
		//$connection_queue
		//object	= connection_queue_obj
		//string	= queue_name
		
		$this->_device 					= $device;
		$this->_interface 				= $interface;
		$this->_connection_queue 		= $connection_queue;
	}
	
	public function execute()
	{
		if (is_object($this->_interface)) {
			$interface_name			= $this->_interface->get_if_name();
			$interface_qdisc_id		= $this->_interface->get_if_id();
		} else {
			$interface_name			= $this->_interface;
			preg_match("/eth([0-9]+)/", $interface_name, $result);
			$interface_qdisc_id		= $result['1'];
		}
		
		if (is_object($this->_connection_queue)) {
			$connection_queue_name			= $this->_connection_queue->get_connection_queue_name();
		} else {
			$connection_queue_name			= $this->_connection_queue;
		}
		
		//figure out if the queue is already running
		$running_queues			= new Thelist_Bairos_command_getinterfaceconnectionqueues($this->_device, $this->_interface);
		$active_queue			= $running_queues->is_queue_active($connection_queue_name, true);
		
		if ($active_queue != false) {

			//object model check only
			if (is_object($this->_connection_queue)) {
				
				//Down all the filters first
				if ($this->_connection_queue->get_connection_queue_filters() != null) {
				
					foreach ($this->_connection_queue->get_connection_queue_filters() as $filter) {
						//now we add the filter
						$remove_filter	= new Thelist_Bairos_command_removeconnectionqueuefilter($this->_device, $this->_interface, $filter);
						$remove_filter->execute();
					}
				}
				
				//if this queue has live child queue on the device we cannot remove it before it has been removed first
				if ($this->_connection_queue->get_child_relationships() != null) {
						
					foreach($this->_connection_queue->get_child_relationships() as $child_queue) {
							
						//is the child queue active?
						$active_or_not	= $running_queues->is_queue_active($child_queue->get_connection_queue_name(), false);
							
						if ($active_or_not != false) {
							//if we have an active child queue, it must be removed before we can continue, recurse
							$remove_child = new Thelist_Bairos_command_removeconnectionqueue($this->_device, $this->_interface, $child_queue);
							$remove_child->execute();
						}
					}
				}
			}
	
			//since the  queue is active, lets remove it
			if ($active_queue['configuration']['queue_type'] == 'child') {
				$command = "sudo /sbin/tc class del dev ".$interface_name." parent ".$interface_qdisc_id.":".$active_queue['configuration']['queue_parent_name']." classid ".$interface_qdisc_id.":".$connection_queue_name."";
			} elseif ($active_queue['configuration']['queue_type'] == 'root') {
				$command = "sudo /sbin/tc class del dev ".$interface_name." classid ".$interface_qdisc_id.":".$connection_queue_name."";
			} else {
				throw new exception('we are trying to remove a queue, but cannot determine its type', 4704);
			}

			//remove it
			$this->_device->execute_command($command);

			//now verify that it was done correctly, make sure to get a fresh set
			$active_or_not_after	= $running_queues->is_queue_active($connection_queue_name, true);
			
			if ($active_or_not_after != false) {
				
				//most times the routers take a few sec before revoking the queue so we wait for 2 sec before trying again
				sleep(2);
				$active_or_not_after2	= $running_queues->is_queue_active($connection_queue_name, true);
				
				if ($active_or_not_after2 != false) {
					throw new exception('we are removeing a queue, but after trying we failed', 4705);
				}
				
			} else {
				//the device now has no queues, the one we just removed must have been the last one
			}

		} else {
			//do nothing there are no active queues at all, so removing the queue is already done
		}
	}
	
	
//obsolete now that the execute function is recursive, bring back to life for string model
// 	private function remove_child_queues_recursively($connection_queue=false)
// 	{
		
		//get the active queues
// 		if ($this->_get_connection_queues == null) {
// 			$this->_get_connection_queues	= new Thelist_Bairos_command_getinterfaceconnectionqueues($this->_device, $this->_interface);
// 		}
			
// 		if ($this->_original_connection_queue == null) {
		
			//because we will be using the execute function to remeve each queue, we need to keep track of the original connection queue that was
			//used to instanciate the class, so when we come back to the original object through recursion we can set it back the original value
			//so the class is intact
// 			$main_queue							= $this->_connection_queue;
// 			$this->_original_connection_queue	= $this->_connection_queue;
		
// 		} else {
// 			$main_queue		= $connection_queue;
// 		}

// 		if (is_object($this->_connection_queue)) {
			
// 			$child_queues	= $main_queue->get_child_relationships();
			
// 			if ($child_queues != null) {
				//we have child queues, so we recurse
// 				foreach ($child_queues as $child_queue) {
// 					$this->remove_child_queues_recursively($child_queue); //recurse
// 				}
// 			} 
			
// 		} 
		
		//in addition to the database check for the object model, both models go throught this removal
		//get the active queues and remove anyone that relates to this master interface asa child
// 		$active_queues	= $this->_get_connection_queues->get_queues(false, false);
		
// 		if ($active_queues != null) {
			
// 			foreach ($active_queues as $active_queue) {
				
// 				if (isset($active_queue['configuration']['queue_parent_name'])) {
// 					if ($active_queue['configuration']['queue_parent_name'] == $main_queue) {
// 						$this->remove_child_queues_recursively($active_queue['configuration']['queue_name']);
// 					}
// 				}
// 			}
// 		}
		
		//now that all child queues have been removed we can remove the queue
		//unless this is the main queue, that we leave alone for its own execution
		//this class only gets rid of the child queues

		//if this is the original object that started it all, then we set it back so the class is intact
// 		if (is_object($this->_connection_queue)) {
			
// 			if ($this->_original_connection_queue->get_connection_queue_id() == $main_queue->get_connection_queue_id()) {
			
// 				$this->_connection_queue 			= $main_queue;
// 				$this->_original_connection_queue	= null;

// 			} else {
				
				//if we have an active child queue, then we remove it.
// 				$this->_connection_queue = $main_queue;
// 				$this->execute();
// 			}
			
// 		} else {
			
// 			if ($this->_original_connection_queue == $main_queue) {
					
// 				$this->_connection_queue 			= $main_queue;
// 				$this->_original_connection_queue	= null;
				
// 			} else {
				
				//if we have an active child queue, then we remove it.
// 				$this->_connection_queue = $main_queue;
// 				$this->execute();
// 			}
// 		}
// 	}
}