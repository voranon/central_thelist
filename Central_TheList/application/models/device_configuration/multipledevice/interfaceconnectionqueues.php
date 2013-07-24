<?php

//exception codes 14800-14899

class thelist_multipledevice_config_interfaceconnectionqueues implements Thelist_Commander_pattern_interface_ideviceconfiguration
{
	private $_interface;

	public function __construct($interface)
	{
		$this->_interface 				= $interface;
	}

	public function generate_config_array()
	{
		$connection_queues 			= $this->_interface->get_connection_queues();
	
		if ($connection_queues != null) {
			
			$i=0;
			foreach ($connection_queues as $queue) {
				
				//get the queue disc parent name
				$return[$i]['configuration']['queue_interface_name'] = $this->_interface->get_if_name();
				
				
				$parents = $queue->get_parent_relationships();
				
				//a queue can only have a single master i think, there may be senarios where this does not hold true (if priority filled then do this senario), then remove this validation
				if (isset($parents['1'])) {
					throw new exception("queue has more than a single parent on if_id: ".$this->_interface->get_if_id().", we have an input problem somewhere", 14801);
				} elseif(isset($parents['0'])) {
					
					//this is a child queue, because it has a parent
					$return[$i]['configuration']['queue_type'] = 'child';
					//get the parent queue name
					$return[$i]['configuration']['queue_parent_name'] = $parents['0']->get_connection_queue_name();
					
				} elseif ($parents == null) {
					
					//this is a root queue, because it does not have any parents
					$return[$i]['configuration']['queue_type'] = 'root';
				} 
				
				//get the queue name
				$return[$i]['configuration']['queue_name'] = $queue->get_connection_queue_name();
				
				//get the queue disc parent name
				$return[$i]['configuration']['queue_qdisc_parent_name'] = $this->_interface->get_if_id();
				
				//we cannot have an SLA that is higher than the max rate, we would never get to the SLA
				//however inside the queue tree a child may have a higher value than the parent queue
				//this is because the resources will be allocated proportional to those values once the parent is congested
				if ($queue->get_connection_queue_sla_rate() > $queue->get_connection_queue_max_rate()) {
					throw new exception("queue sla rate is higher than the max rate on if_id: ".$this->_interface->get_if_id().", queue_id: ".$queue->get_connection_queue_id()." we have an input problem somewhere", 14802);
				}
				
				//get the sla rate
				$return[$i]['configuration']['queue_sla_rate'] 	= $queue->get_connection_queue_sla_rate();
				
				//get the sla rate
				$return[$i]['configuration']['queue_ceil_rate'] = $queue->get_connection_queue_max_rate();
				
				$i++;
			}
		}

		if (isset($return)) {
			return $return;
		} else {
			return false;
		}
	}
	
	public function generate_config_device_syntax($config_array)
	{
		throw new exception('this is a general multi device function, i cannot generate specific syntax', 14800);
	}
}