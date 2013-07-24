<?php

//exception codes 4600-4699

class thelist_bairos_command_addconnectionqueue implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_interface;
	private $_connection_queue;
	
	private $count=0;
	
	private $_get_connection_queues=null;
	private $_original_connection_queue=null;

	public function __construct($device, $interface, $connection_queue)
	{
		//$interface
		//object	= interface_obj
		//string	= interface_name
	
		//$connection_queue
		//object	= connection_queue_obj
		//string	= ['new_config']
			//root example
            //[queue_method] => htb
            //[queue_type] => root
            //[queue_name] => 888
            //[queue_sla_rate] => 5500
            //[queue_ceil_rate] => 5500
            
			//child example
			//[queue_interface_name] => eth1
			//[queue_type] => child
			//[queue_parent_name] => 56465
			//[queue_name] => 888
			//[queue_sla_rate] => 9000
			//[queue_ceil_rate] => 10000
			//[queue_method] => htb
	
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
			$queue_parents					= $this->_connection_queue->get_parent_relationships();
			
			//if the queue has a parent, its a child
			if($queue_parents != null) {
				$connection_queue_type			= 'child';
				
				//a queue can only have a single parent, as of now....
				if (isset($queue_parents['1'])) {
					throw new exception('queue has more than a single parent, we cant deal with that yet', 4600);
				} elseif(isset($queue_parents['0'])) {
					$connection_queue_parent_name	= $queue_parents['0']->get_connection_queue_name();
				} else {
					throw new exception('queue is missing index 0 for the parent interfaces', 4601);
				}

			} else {
				//otherwise it is root
				$connection_queue_type			= 'root';
			}
			
			$connection_queue_sla_rate		= $this->_connection_queue->get_connection_queue_sla_rate();
			$connection_queue_ceil_rate		= $this->_connection_queue->get_connection_queue_max_rate();

		} else {
			
			$connection_queue_name			= $this->_connection_queue['queue_name'];
			$connection_queue_type			= $this->_connection_queue['queue_type'];
			$connection_queue_sla_rate		= $this->_connection_queue['queue_sla_rate'];
			$connection_queue_ceil_rate		= $this->_connection_queue['queue_ceil_rate'];
			
			if ($connection_queue_type == 'child') {
				//if this  is a child we need the parent name
				$connection_queue_parent_name	= $this->_connection_queue['queue_parent_name'];
			}
		}
		
		//make sure the qdisc is running
		if ($connection_queue_type == 'root') {
			
			$qdiscs			= new Thelist_Bairos_command_getinterfacequeuediscs($this->_device, $this->_interface);
			$active_qdisc	= $qdiscs->is_qdisc_active($this->_interface->get_if_id(), true);

			//if not the n start it
			if ($active_qdisc == false) {
				
				//since we are bringing up the qdisc here it will bring up the queues we are slated to setup so we will cruise through the next few steps
				$add_qdisc		= new Thelist_Bairos_command_addinterfacequeuedisc($this->_device, $this->_interface);
				$add_qdisc->execute();
			}
		}

		//get the current active queues
		//now we need to get the current running config on the device interface
		$running_queues			= new Thelist_Bairos_command_getinterfaceconnectionqueues($this->_device, $this->_interface);

		//is the queue already active?
		$active_or_not	= $running_queues->is_queue_active($connection_queue_name, false);
		
		if ($active_or_not == false) {
			
			//object model check only
			if (is_object($this->_connection_queue)) {

				//does the queue have a parent? if it does lets make sure that parent is live, because we cannot start a child without its parent being active
				if ($queue_parents != null) {
					
					foreach($queue_parents as $parent_queue) {
						
						$parent_active_or_not	= $running_queues->is_queue_active($parent_queue->get_connection_queue_name(), false);
						
						if ($parent_active_or_not == false){
							//if it is not we start it first, instanciate our selfs and add the parent first
							//this will act as a recursive function, getting all layers of queues up before adding the one we really wanted to activate in the first place
							$add_parent_queue	= new Thelist_Bairos_command_addconnectionqueue($this->_device, $this->_interface, $parent_queue);
							$add_parent_queue->execute();
							
						}
					}
				}
			}
			
			//not that the parent is verified up we need to add the new queue and queue is not running, lets set it up
			if ($connection_queue_type == 'child') {
				$command	= "sudo /sbin/tc class add dev ".$interface_name." parent ".$interface_qdisc_id.":".$connection_queue_parent_name." classid ".$interface_qdisc_id.":".$connection_queue_name." htb rate ".$connection_queue_sla_rate."kbit ceil ".$connection_queue_ceil_rate."kbit";
			} elseif ($connection_queue_type == 'root') {
				$command	= "sudo /sbin/tc class add dev ".$interface_name." parent ".$interface_qdisc_id.":0 classid ".$interface_qdisc_id.":".$connection_queue_name." htb rate ".$connection_queue_sla_rate."kbit ceil ".$connection_queue_ceil_rate."kbit";
			} else {
				throw new exception('queue type is unknown', 4602);
			}
			
			//activate the queue
			$this->_device->execute_command($command);
			
			//now verify that it was done correctly, make sure to get a fresh set
			$active_or_not_after	= $running_queues->is_queue_active($connection_queue_name, true);
				
			if ($active_or_not_after == false) {
			
				//most times the routers take a few sec before invoking the queue so we wait for 2 sec before trying again
				sleep(2);
				$active_or_not_after2	= $running_queues->is_queue_active($connection_queue_name, true);
			
				if ($active_or_not_after2 == false) {
					throw new exception('we are adding a queue, but after trying we failed', 4603);
				}
			
			} else {
				//the device now has the queue
			}
			
			//after the queue has been setup we activate all the filters
			//object model only
			if (is_object($this->_connection_queue)) {
				
				if ($this->_connection_queue->get_connection_queue_filters() != null) {
					
					foreach ($this->_connection_queue->get_connection_queue_filters() as $filter) {
						//now we add the filter
						$add_filter	= new Thelist_Bairos_command_addconnectionqueuefilter($this->_device, $this->_interface, $filter);
						$add_filter->execute();
					}
				}
			}

		} else {
			//queue already running, we dont need to do anything
		}

	}
	
	public function add_child_queues_recursively($connection_queue_obj=false)
	{
		//object model only, can be expanded to to use the getconnection queus and work for the string model as well.
		if (is_object($this->_connection_queue)) {
				
			//get the active queues
			if ($this->_get_connection_queues == null) {
				$this->_get_connection_queues	= new Thelist_Bairos_command_getinterfaceconnectionqueues($this->_device, $this->_interface);
			}
				
			if ($this->_original_connection_queue == null) {
				
				//very first interface
				$is_queue_running	= $this->_get_connection_queues->is_queue_active($this->_connection_queue->get_connection_queue_name(), false);
				if ($is_queue_running == false) {
					throw new exception('the main parent interface is not running, this must be running before we can add its child queues', 4605);
				}
	
				//because we will be using the execute function to add each queue, we need to keep track of the original connection queue that was
				//used to instanciate the class, so when we come back to the original object through recursion we can set it back the original value
				//so the class is intact
				$main_queue							= $this->_connection_queue;
				$this->_original_connection_queue	= $this->_connection_queue;
	
			} else {
				$main_queue		= $connection_queue_obj;
			}
			
			//get any child queues
			$child_queues	= $main_queue->get_child_relationships();
			
			if ($child_queues != null) {
				
				//we have child queues, so we setup each of them
				foreach ($child_queues as $child_queue) {
				
					//lets see if the queue is active first, we dont need to refresh the result everytime
						//once is enough, because we are moving from parent to child, if the child is not running in the beginning it will not be running just because
					//we added its parent
					$is_queue_running	= $this->_get_connection_queues->is_queue_active($child_queue->get_connection_queue_name(), false);
						
					if ($is_queue_running == false) {
					
						//if we have an inactive child queue, then we add it.
						$this->_connection_queue = $child_queue;
						$this->execute();
					}
					
					//check if this child have children of its own
					$this->add_child_queues_recursively($child_queue); //recurse
				}
			}

			//if this is not the original queue then we reset the variables so the class is returned intact
			if ($this->_original_connection_queue->get_connection_queue_id() == $main_queue->get_connection_queue_id()) {

				//if this is the original object that started it all, then we set it back so the class is intact
				$this->_connection_queue 			= $main_queue;
				$this->_original_connection_queue	= null;
			}

		} else {
			throw new exception('in order to add child queues recursively, you must provide an object', 4604);
		}
	
	
	}
	

}