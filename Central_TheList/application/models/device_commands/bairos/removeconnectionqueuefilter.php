<?php

//exception codes 15200-15299

class thelist_bairos_command_removeconnectionqueuefilter implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	private $_connection_queue_filter;
	
	public function __construct($device, $interface, $connection_queue_filter)
	{
		//this class only deals with ip based u32 filters so far
		
		//$interface
		//object	= interface_obj
		//string	= interface_name
		
		//$connection_queue
		//object	= connection_queue_obj
		//string	= queue_filter array
		//i.e 
			//['queue_parent_name']
			//['queue_filter_priority']
			//['queue_filter_name'] (must be the base 16 representation of the filter name)

		$this->_device 							= $device;
		$this->_interface 						= $interface;
		$this->_connection_queue_filter 		= $connection_queue_filter;
	}
	
	public function execute()
	{

		if (is_object($this->_interface)) {
			$interface				= $this->_interface;
			$interface_name			= $this->_interface->get_if_name();
			$interface_qdisc_id		= $this->_interface->get_if_id();
		} else {
			$interface				= $this->_interface;
			$interface_name			= $this->_interface;
			preg_match("/eth([0-9]+)/", $interface_name, $result);
			$interface_qdisc_id		= $result['1'];
		}
		
		if (is_object($this->_connection_queue_filter)) {
			
			$connection_queue_obj		= new Thelist_Model_connectionqueue($this->_connection_queue_filter->get_connection_queue_id());
			
			$connection_queue_name		= $connection_queue_obj->get_connection_queue_name();
			$filter_priority			= $this->_connection_queue_filter->get_connection_queue_filter_priority();
			$filter_name				= base_convert($this->_connection_queue_filter->get_connection_queue_filter_name(), 10, 16);
			
		} else {
			
			$connection_queue_name		= $this->_connection_queue_filter['queue_parent_name'];
			$filter_priority			= $this->_connection_queue_filter['queue_filter_priority'];
			$filter_name				= $this->_connection_queue_filter['queue_filter_name'];
		}
		
		//get current running filters
		$device_filters				= new Thelist_Bairos_command_getinterfacequeuefilters($this->_device, $interface);
		$filter_running				= $device_filters->is_filter_active($filter_name, true);
		
		if ($filter_running != false) {
	
			//there can be many filters with the same name, because there are 2 ways of creating them
			//either adding matches to a single filter or creating a seperate filter entry with same name, priority etc and matches
			//we do not consider the second a seperate filter because they have the same name, so we delete all filters that have the name we are looking for
			$command = "sudo /sbin/tc filter del dev ".$interface_name." parent ".$interface_qdisc_id.": handle ".$filter_running['dynamic_configuration']['queue_filter_run_time_name']."::".$filter_name." prio ".$filter_priority." protocol ip u32";
			
			//find out potentially how many times we have to remove the filter
			if (isset($filter_running['configuration']['frame_match'])) {
				$max_number_of_tries = count($filter_running['configuration']['frame_match']);
			} else {
				$max_number_of_tries = 1;
			}
			
			$filter_exists = true;
	
			$i=0;
			while ($filter_exists === true) {
				
				//first run needs to happen we already know there is atleast one entry
				if ($i > 0) {
					
					//check if the queue is still running
					$filter_still_running = $device_filters->is_filter_active($filter_name, true);
					
					if ($filter_still_running == false) {
						$filter_exists = false;
					} else {
						$this->_device->execute_command($command);
					}
						
				} else {
					//first time we run the command
					$this->_device->execute_command($command);
				}
	
				//we we have to try more than the max number of matches we have a problem
				if ($i > $max_number_of_tries) {
					throw new exception("we tried to remove the filter name ".$filter_name." in if_id: ".$interface_qdisc_id.", this many times:".$i.", and its still there ", 15200);
				}
				
				$i++;
			}
			
			//now check if we can remove the filter parent (not sure what it is)
			$remaining_filters = $device_filters->get_queue_filters(true, false);
			
			$parent_still_used	= 'no';
			
			if ($remaining_filters != null) {
				
				foreach ($remaining_filters as $remaining_filter) {
					
					//are there any filters that share the running name?
					if ($remaining_filter['dynamic_configuration']['queue_filter_run_time_name'] == $filter_running['dynamic_configuration']['queue_filter_run_time_name']) {
						
						//we found a match so we let it be
						$parent_still_used = 'yes';
					}
				}
			}
			
			//the parent is created one per active priority
			//so if there are no more filters that are using the priority
			//then we can be sure that it can be removed
			if ($parent_still_used == 'no') {

				$command_remove_parent = "sudo /sbin/tc filter del dev ".$interface_name." parent ".$interface_qdisc_id.": prio ".$filter_priority." protocol ip u32";
				$this->_device->execute_command($command_remove_parent);
				
				//check that it was removed
				$device_reply = $this->_device->execute_command("tc filter show dev ".$interface_name." | grep ".$filter_running['dynamic_configuration']['queue_filter_run_time_name'].":");
				
				if ($device_reply->get_message() != '') {
					throw new exception("we removed the filter, and then tried to remove the parent filter but its still there ", 15201);
				}
			}
		}
	}
}