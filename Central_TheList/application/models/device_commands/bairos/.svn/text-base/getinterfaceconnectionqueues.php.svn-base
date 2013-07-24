<?php

//exception codes 5200-5299

class thelist_bairos_command_getinterfaceconnectionqueues implements Thelist_Commander_pattern_interface_idevicecommand
{

	private $_device;
	private $_interface=null;
	private $_queues=null;
	
	//keep the processing limited
	private $_get_stats=false;
	
	public function __construct($device, $interface)
	{
		//$interface
		//object	= interface_obj
		//string	= interface name
		
		$this->_device 		= $device;
		$this->_interface 	= $interface;
	}
	
	public function execute()
	{
		if (is_object($this->_interface)) {
			$interface_name		= $this->_interface->get_if_name();
		} else {
			$interface_name		= $this->_interface;
		}
		
		$mconverter = new Thelist_Utility_multiplierconverter();

		$device_reply = $this->_device->execute_command("tc -s -d class show dev ".$interface_name."");
		
		
		//get an index of all the queues
		preg_match_all("/class (htb) ([0-9]+):([0-9]+) (root|parent)/",$device_reply->get_message(), $connection_queue_index_raw);
		
		//get root queues, root queues are attached directly to the qdisc
		preg_match_all("/class (htb) ([0-9]+):([0-9]+) root rate ([0-9]+.*) ceil ([0-9]+.*) burst ([0-9]+.*)\/([0-9]+) mpu ([0-9]+.*) overhead ([0-9]+.*) cburst ([0-9]+.*)\/([0-9]+) mpu ([0-9]+.*) overhead ([0-9]+.*) level ([0-9]+)/",$device_reply->get_message(), $root_with_child_queues_raw);

		//get root queues, root queues are attached directly to the qdisc, if they do not have any children, then ty look a bit different
		preg_match_all("/class (htb) ([0-9]+):([0-9]+) root prio ([0-9]+) quantum ([0-9]+) rate ([0-9]+.*) ceil ([0-9]+.*) burst ([0-9]+.*)\/([0-9]+) mpu ([0-9]+.*) overhead ([0-9]+.*) cburst ([0-9]+.*)\/([0-9]+) mpu ([0-9]+.*) overhead ([0-9]+.*) level ([0-9]+)/",$device_reply->get_message(), $root_without_child_queues_raw);	
		
		//get children of the root or other parent queues
		preg_match_all("/class (htb) ([0-9]+):([0-9]+) parent ([0-9]+):([0-9]+) prio ([0-9]+) quantum ([0-9]+) rate ([0-9]+.*) ceil ([0-9]+.*) burst ([0-9]+.*)\/([0-9]+) mpu ([0-9]+.*) overhead ([0-9]+.*) cburst ([0-9]+.*)\/([0-9]+) mpu ([0-9]+.*) overhead ([0-9]+.*) level ([0-9]+)/",$device_reply->get_message(), $child_connection_queues_raw);
		
		//get child queues that have child queues of their own
		//when a child queue is a parent for another queue, it has slightly different attributes that a queue without children
		//child with own child queue:		class htb 145:999 parent 145:888 rate 6600Kbit ceil 6600Kbit burst 2424b/8 mpu 0b overhead 0b cburst 2424b/8 mpu 0b overhead 0b level 6
		//child with no child of its own:	class htb 145:45 parent 145:999 prio 0 quantum 2750 rate 9900Kbit ceil 9900Kbit burst 2836b/8 mpu 0b overhead 0b cburst 2836b/8 mpu 0b overhead 0b level 0	
		preg_match_all("/class (htb) ([0-9]+):([0-9]+) parent ([0-9]+):([0-9]+) rate ([0-9]+.*) ceil ([0-9]+.*) burst ([0-9]+.*)\/([0-9]+) mpu ([0-9]+.*) overhead ([0-9]+.*) cburst ([0-9]+.*)\/([0-9]+) mpu ([0-9]+.*) overhead ([0-9]+.*) level ([0-9]+)/",$device_reply->get_message(), $child_with_child_queues_raw);
		
		if ($this->_get_stats == true) {
			//get first line of stats
			preg_match_all("/Sent ([0-9]+) bytes ([0-9]+) pkt \(dropped ([0-9]+), overlimits ([0-9]+) requeues ([0-9]+)\)/",$device_reply->get_message(), $connection_queue_stats1_raw);
			
			//get second line of stats
			preg_match_all("/rate ([0-9]+.*) ([0-9]+)pps backlog ([0-9]+.*) ([0-9]+)p requeues ([0-9]+)/",$device_reply->get_message(), $connection_queue_stats2_raw);
				
			//get third line of stats
			preg_match_all("/lended: ([0-9]+) borrowed: ([0-9]+) giants: ([0-9]+)/",$device_reply->get_message(), $connection_queue_stats3_raw);
			
			//get fourth line of stats
			preg_match_all("/ tokens: ([0-9]+|-[0-9]+) ctokens: ([0-9]+|-[0-9]+)/",$device_reply->get_message(), $connection_queue_stats4_raw);
		}

		$i=0;
		//here is the loop that adds all attributes to the array that are common for all queues
		//i.e. where the index from $connection_queue_index_raw can be used because all the other preg_match_all
		//will have the same index, since all queues are a match
		if (isset($connection_queue_index_raw['3']['0'])) {
			
			foreach($connection_queue_index_raw['3'] as $queue_index => $queue_name) {

					//config
					$this->_queues[$i]['configuration']['queue_interface_name']				= $interface_name;
					$this->_queues[$i]['configuration']['queue_method']						= $connection_queue_index_raw['1'][$queue_index];
					
					//type is misleading, when the word parent appears in the first line that indicates that the queue has a parent
					//it may also have child queues, but that word means it is not a root, but a child 
					if ($connection_queue_index_raw['4'][$queue_index] == 'parent') {
						$queue_type	= 'child';
					} elseif ($connection_queue_index_raw['4'][$queue_index] == 'root') {
						$queue_type	= 'root';
					} else {
						throw new exception('queue type could not be determined', 5200);
					}
					
					$this->_queues[$i]['configuration']['queue_type']						= $queue_type;
					$this->_queues[$i]['configuration']['queue_name']						= $connection_queue_index_raw['3'][$queue_index];
					$this->_queues[$i]['configuration']['queue_qdisc_parent_name']			= $connection_queue_index_raw['2'][$queue_index];
	
				if ($this->_get_stats == true) {
					
					//just stats
					$this->_queues[$i]['stats']['queue_sent_bytes']					= $connection_queue_stats1_raw['1'][$queue_index];
					$this->_queues[$i]['stats']['queue_sent_packets']				= $connection_queue_stats1_raw['2'][$queue_index];
					$this->_queues[$i]['stats']['queue_dropped_packets']			= $connection_queue_stats1_raw['3'][$queue_index];
					$this->_queues[$i]['stats']['queue_overlimit_packets']			= $connection_queue_stats1_raw['4'][$queue_index];
					$this->_queues[$i]['stats']['queue_requeue_packets']			= $connection_queue_stats1_raw['5'][$queue_index];
				
					
					//there are many different multipliers we need clean ints.					
					if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $connection_queue_stats2_raw['1'][$queue_index], $split_value)) {
						$queue_current_rate = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
					} else {
						$queue_current_rate = $connection_queue_stats2_raw['1'][$queue_index];
					}
					
					$this->_queues[$i]['stats']['queue_current_rate']				= $queue_current_rate;
					
					if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $connection_queue_stats2_raw['3'][$queue_index], $split_value)) {
						$queue_current_bytes_backlog = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
					} else {
						$queue_current_bytes_backlog = $connection_queue_stats2_raw['3'][$queue_index];
					}
					
					$this->_queues[$i]['stats']['queue_current_bytes_backlog']		= $queue_current_bytes_backlog;

					
					$this->_queues[$i]['stats']['queue_current_pps']				= $connection_queue_stats2_raw['2'][$queue_index];
					$this->_queues[$i]['stats']['queue_current_packet_backlog']		= $connection_queue_stats2_raw['4'][$queue_index];
					$this->_queues[$i]['stats']['queue_current_requeue_backlog']	= $connection_queue_stats2_raw['5'][$queue_index];
					$this->_queues[$i]['stats']['queue_lended_tokens']				= $connection_queue_stats3_raw['1'][$queue_index];;
					$this->_queues[$i]['stats']['queue_borrowed_tokens']			= $connection_queue_stats3_raw['2'][$queue_index];
					$this->_queues[$i]['stats']['queue_giants']						= $connection_queue_stats3_raw['3'][$queue_index];
					$this->_queues[$i]['stats']['queue_avail_tokens']				= $connection_queue_stats4_raw['1'][$queue_index];
					$this->_queues[$i]['stats']['queue_avail_ceil_tokens']			= $connection_queue_stats4_raw['2'][$queue_index];

				}
				$i++;
			}
		}

		//root queues and add their attributes
		if (isset($root_with_child_queues_raw['3']['0'])) {

			//use the queue index and append the stats
			foreach($this->_queues as $running_queue_index => $running_queue){
				
				foreach($root_with_child_queues_raw['3'] as $root_queue_index => $root_queue_name) {
						
					if (
					$running_queue['configuration']['queue_qdisc_parent_name'] == $root_with_child_queues_raw['2'][$root_queue_index] 
					&& $running_queue['configuration']['queue_name'] == $root_queue_name
					) {
						
						if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $root_with_child_queues_raw['4'][$root_queue_index], $split_value)) {
							$queue_sla_rate = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
						} else {
							$queue_sla_rate =$root_with_child_queues_raw['4'][$root_queue_index];
						}
						
						//we need all config rates in kbit/s
						$this->_queues[$running_queue_index]['configuration']['queue_sla_rate']					= $queue_sla_rate / 1000;
						
						if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $root_with_child_queues_raw['5'][$root_queue_index], $split_value)) {
							$queue_ceil_rate = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
						} else {
							$queue_ceil_rate = $root_with_child_queues_raw['5'][$root_queue_index];
						}
						
						//we need all config rates in kbit/s
						$this->_queues[$running_queue_index]['configuration']['queue_ceil_rate']					= $queue_ceil_rate / 1000;
						
						
						if ($this->_get_stats == true) {
						
							if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $root_with_child_queues_raw['6'][$root_queue_index], $split_value)) {
								$queue_burst_bytes = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
							} else {
								$queue_burst_bytes = $root_with_child_queues_raw['6'][$root_queue_index];
							}
							
							$this->_queues[$running_queue_index]['stats']['queue_burst_bytes']				= $queue_burst_bytes;
							
							if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $root_with_child_queues_raw['8'][$root_queue_index], $split_value)) {
								$queue_burst_mpu_bytes = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
							} else {
								$queue_burst_mpu_bytes = $root_with_child_queues_raw['8'][$root_queue_index];
							}
							
							$this->_queues[$running_queue_index]['stats']['queue_burst_mpu_bytes']			= $queue_burst_mpu_bytes;
							
							if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $root_with_child_queues_raw['9'][$root_queue_index], $split_value)) {
								$queue_burst_overhead_bytes = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
							} else {
								$queue_burst_overhead_bytes = $root_with_child_queues_raw['9'][$root_queue_index];
							}
							
							$this->_queues[$running_queue_index]['stats']['queue_burst_overhead_bytes']		= $queue_burst_overhead_bytes;
							
							if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $root_with_child_queues_raw['10'][$root_queue_index], $split_value)) {
								$queue_cburst_bytes = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
							} else {
								$queue_cburst_bytes = $root_with_child_queues_raw['10'][$root_queue_index];
							}
							
							$this->_queues[$running_queue_index]['stats']['queue_cburst_bytes']				= $queue_cburst_bytes;
								
							if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $root_with_child_queues_raw['12'][$root_queue_index], $split_value)) {
								$queue_cburst_mpu_bytes = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
							} else {
								$queue_cburst_mpu_bytes = $root_with_child_queues_raw['12'][$root_queue_index];
							}
							
							$this->_queues[$running_queue_index]['stats']['queue_cburst_mpu_bytes']			= $queue_cburst_mpu_bytes;
								
							if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $root_with_child_queues_raw['13'][$root_queue_index], $split_value)) {
								$queue_cburst_overhead_bytes = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
							} else {
								$queue_cburst_overhead_bytes = $root_with_child_queues_raw['13'][$root_queue_index];
							}

							$this->_queues[$running_queue_index]['queue_cburst_overhead_bytes']		= $queue_cburst_overhead_bytes;
							
							
							$this->_queues[$running_queue_index]['stats']['queue_burst_bytes_divider']		= $root_with_child_queues_raw['7'][$root_queue_index];
							$this->_queues[$running_queue_index]['stats']['queue_cburst_bytes_divider']		= $root_with_child_queues_raw['11'][$root_queue_index];
							$this->_queues[$running_queue_index]['stats']['queue_level']					= $root_with_child_queues_raw['14'][$root_queue_index];

						}
					}	
				}
			}
		}	

		//root queues without child and add their attributes
		if (isset($root_without_child_queues_raw['3']['0'])) {
		
			//use the queue index and append the stats
			foreach($this->_queues as $running_queue_index => $running_queue){
		
				foreach($root_without_child_queues_raw['3'] as $root_wo_child_queue_index => $root_wo_child_queue_name) {
		
					if (
					$running_queue['configuration']['queue_qdisc_parent_name'] == $root_without_child_queues_raw['2'][$root_wo_child_queue_index]
					&& $running_queue['configuration']['queue_name'] == $root_wo_child_queue_name
					) {
		
						if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $root_without_child_queues_raw['6'][$root_wo_child_queue_index], $split_value)) {
							$queue_sla_rate = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
						} else {
							$queue_sla_rate = $root_without_child_queues_raw['6'][$root_wo_child_queue_index];
						}
		
						//we need all config rates in kbit/s
						$this->_queues[$running_queue_index]['configuration']['queue_sla_rate']					= $queue_sla_rate / 1000;
		
						if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $root_without_child_queues_raw['7'][$root_wo_child_queue_index], $split_value)) {
							$queue_ceil_rate = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
						} else {
							$queue_ceil_rate = $root_without_child_queues_raw['7'][$root_wo_child_queue_index];
						}
		
						//we need all config rates in kbit/s
						$this->_queues[$running_queue_index]['configuration']['queue_ceil_rate']					= $queue_ceil_rate / 1000;
		
		
						if ($this->_get_stats == true) {
		
							if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $root_without_child_queues_raw['8'][$root_wo_child_queue_index], $split_value)) {
								$queue_burst_bytes = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
							} else {
								$queue_burst_bytes = $root_without_child_queues_raw['8'][$root_wo_child_queue_index];
							}
								
							$this->_queues[$running_queue_index]['stats']['queue_burst_bytes']				= $queue_burst_bytes;
								
							if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $root_without_child_queues_raw['10'][$root_wo_child_queue_index], $split_value)) {
								$queue_burst_mpu_bytes = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
							} else {
								$queue_burst_mpu_bytes = $root_without_child_queues_raw['10'][$root_wo_child_queue_index];
							}
								
							$this->_queues[$running_queue_index]['stats']['queue_burst_mpu_bytes']			= $queue_burst_mpu_bytes;
								
							if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $root_without_child_queues_raw['11'][$root_wo_child_queue_index], $split_value)) {
								$queue_burst_overhead_bytes = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
							} else {
								$queue_burst_overhead_bytes = $root_without_child_queues_raw['11'][$root_wo_child_queue_index];
							}
								
							$this->_queues[$running_queue_index]['stats']['queue_burst_overhead_bytes']		= $queue_burst_overhead_bytes;
								
							if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $root_without_child_queues_raw['12'][$root_wo_child_queue_index], $split_value)) {
								$queue_cburst_bytes = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
							} else {
								$queue_cburst_bytes = $root_without_child_queues_raw['12'][$root_wo_child_queue_index];
							}
								
							$this->_queues[$running_queue_index]['stats']['queue_cburst_bytes']				= $queue_cburst_bytes;
		
							if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $root_without_child_queues_raw['14'][$root_wo_child_queue_index], $split_value)) {
								$queue_cburst_mpu_bytes = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
							} else {
								$queue_cburst_mpu_bytes = $root_without_child_queues_raw['14'][$root_wo_child_queue_index];
							}
								
							$this->_queues[$running_queue_index]['stats']['queue_cburst_mpu_bytes']			= $queue_cburst_mpu_bytes;
		
							if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $root_without_child_queues_raw['15'][$root_wo_child_queue_index], $split_value)) {
								$queue_cburst_overhead_bytes = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
							} else {
								$queue_cburst_overhead_bytes = $root_without_child_queues_raw['15'][$root_wo_child_queue_index];
							}
		
							$this->_queues[$running_queue_index]['queue_cburst_overhead_bytes']		= $queue_cburst_overhead_bytes;
								
								
							$this->_queues[$running_queue_index]['stats']['queue_burst_bytes_divider']		= $root_without_child_queues_raw['9'][$root_wo_child_queue_index];
							$this->_queues[$running_queue_index]['stats']['queue_cburst_bytes_divider']		= $root_without_child_queues_raw['13'][$root_wo_child_queue_index];
							$this->_queues[$running_queue_index]['stats']['queue_level']					= $root_without_child_queues_raw['16'][$root_wo_child_queue_index];
		
						}
					}
				}
			}
		}
				
		//finish the child queues
		if (isset($child_connection_queues_raw['3']['0'])) {
		
			foreach($this->_queues as $running_queue_index => $running_queue){
					
				foreach($child_connection_queues_raw['3'] as $child_queue_index => $child_queue_name) {
						
					if (
					$running_queue['configuration']['queue_qdisc_parent_name'] == $child_connection_queues_raw['2'][$child_queue_index]
					&& $running_queue['configuration']['queue_name'] == $child_queue_name
					) {
							
						if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $child_connection_queues_raw['8'][$child_queue_index], $split_value)) {
							$queue_sla_rate = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
						} else {
							$queue_sla_rate = $child_connection_queues_raw['8'][$child_queue_index];
						}
						
						//we need all config rates in kbit/s
						$this->_queues[$running_queue_index]['configuration']['queue_sla_rate']					= $queue_sla_rate / 1000;
							
						if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $child_connection_queues_raw['9'][$child_queue_index], $split_value)) {
							$queue_ceil_rate = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
						} else {
							$queue_ceil_rate = $child_connection_queues_raw['9'][$child_queue_index];
						}
						
						//we need all config rates in kbit/s
						$this->_queues[$running_queue_index]['configuration']['queue_ceil_rate']					= $queue_ceil_rate / 1000;
						
						//include the parent as config
						$this->_queues[$running_queue_index]['configuration']['queue_parent_name']				= $child_connection_queues_raw['5'][$child_queue_index];
							
						if ($this->_get_stats == true) {
						
							if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $child_connection_queues_raw['10'][$child_queue_index], $split_value)) {
								$queue_burst_bytes = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
							} else {
								$queue_burst_bytes = $child_connection_queues_raw['10'][$child_queue_index];
							}
							
							$this->_queues[$running_queue_index]['stats']['queue_burst_bytes']				= $queue_burst_bytes;
								
							if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $child_connection_queues_raw['12'][$child_queue_index], $split_value)) {
								$queue_burst_mpu_bytes = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
							} else {
								$queue_burst_mpu_bytes = $child_connection_queues_raw['12'][$child_queue_index];
							}
							
							$this->_queues[$running_queue_index]['stats']['queue_burst_mpu_bytes']			= $queue_burst_mpu_bytes;
								
							if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $child_connection_queues_raw['13'][$child_queue_index], $split_value)) {
								$queue_burst_overhead_bytes = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
							} else {
								$queue_burst_overhead_bytes = $child_connection_queues_raw['13'][$child_queue_index];
							}
							
							$this->_queues[$running_queue_index]['stats']['queue_burst_overhead_bytes']		= $queue_burst_overhead_bytes;
								
							if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $child_connection_queues_raw['14'][$child_queue_index], $split_value)) {
								$queue_cburst_bytes = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
							} else {
								$queue_cburst_bytes = $child_connection_queues_raw['14'][$child_queue_index];
							}
							
							$this->_queues[$running_queue_index]['stats']['queue_cburst_bytes']				= $queue_cburst_bytes;
			
							if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $child_connection_queues_raw['16'][$child_queue_index], $split_value)) {
								$queue_cburst_mpu_bytes = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
							} else {
								$queue_cburst_mpu_bytes = $child_connection_queues_raw['16'][$child_queue_index];
							}
			
							$this->_queues[$running_queue_index]['stats']['queue_cburst_mpu_bytes']			= $queue_cburst_mpu_bytes;
							
							if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $child_connection_queues_raw['17'][$child_queue_index], $split_value)) {
								$queue_cburst_overhead_bytes = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
							} else {
								$queue_cburst_overhead_bytes = $child_connection_queues_raw['17'][$child_queue_index];
							}

							$this->_queues[$running_queue_index]['stats']['queue_cburst_overhead_bytes']		= $queue_cburst_overhead_bytes;
							
							//stats that do not require calculation
							$this->_queues[$running_queue_index]['stats']['queue_burst_bytes_divider']		= $child_connection_queues_raw['11'][$child_queue_index];
							$this->_queues[$running_queue_index]['stats']['queue_cburst_bytes_divider']		= $child_connection_queues_raw['15'][$child_queue_index];
							$this->_queues[$running_queue_index]['stats']['queue_level']						= $child_connection_queues_raw['18'][$child_queue_index];
							$this->_queues[$running_queue_index]['stats']['queue_priority']					= $child_connection_queues_raw['6'][$child_queue_index];
							$this->_queues[$running_queue_index]['stats']['queue_quantum']					= $child_connection_queues_raw['7'][$child_queue_index];
						}
					}
				}
			}
		}
		
		//finish the child with child queues
		if (isset($child_with_child_queues_raw['3']['0'])) {
		
			foreach($this->_queues as $running_queue_index => $running_queue){
					
				foreach($child_with_child_queues_raw['3'] as $child_wchild_queue_index => $child_wchild_queue_name) {
		
					if (
					$running_queue['configuration']['queue_qdisc_parent_name'] == $child_with_child_queues_raw['2'][$child_wchild_queue_index]
					&& $running_queue['configuration']['queue_name'] == $child_wchild_queue_name
					) {
							
						if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $child_with_child_queues_raw['6'][$child_wchild_queue_index], $split_value)) {
							$queue_sla_rate = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
						} else {
							$queue_sla_rate = $child_with_child_queues_raw['6'][$child_wchild_queue_index];
						}
		
						//we need all config rates in kbit/s
						$this->_queues[$running_queue_index]['configuration']['queue_sla_rate']					= $queue_sla_rate / 1000;
							
						if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $child_with_child_queues_raw['7'][$child_wchild_queue_index], $split_value)) {
							$queue_ceil_rate = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
						} else {
							$queue_ceil_rate = $child_with_child_queues_raw['7'][$child_wchild_queue_index];
						}
		
						//we need all config rates in kbit/s
						$this->_queues[$running_queue_index]['configuration']['queue_ceil_rate']					= $queue_ceil_rate / 1000;
						
						//include the parent as config
						$this->_queues[$running_queue_index]['configuration']['queue_parent_name']				= $child_with_child_queues_raw['5'][$child_wchild_queue_index];
							
						if ($this->_get_stats == true) {
		
							if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $child_with_child_queues_raw['8'][$child_wchild_queue_index], $split_value)) {
								$queue_burst_bytes = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
							} else {
								$queue_burst_bytes = $child_with_child_queues_raw['8'][$child_wchild_queue_index];
							}
								
							$this->_queues[$running_queue_index]['stats']['queue_burst_bytes']				= $queue_burst_bytes;
		
							if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $child_with_child_queues_raw['10'][$child_wchild_queue_index], $split_value)) {
								$queue_burst_mpu_bytes = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
							} else {
								$queue_burst_mpu_bytes = $child_with_child_queues_raw['10'][$child_wchild_queue_index];
							}
								
							$this->_queues[$running_queue_index]['stats']['queue_burst_mpu_bytes']			= $queue_burst_mpu_bytes;
		
							if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $child_with_child_queues_raw['11'][$child_wchild_queue_index], $split_value)) {
								$queue_burst_overhead_bytes = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
							} else {
								$queue_burst_overhead_bytes = $child_with_child_queues_raw['11'][$child_wchild_queue_index];
							}
								
							$this->_queues[$running_queue_index]['stats']['queue_burst_overhead_bytes']		= $queue_burst_overhead_bytes;
		
							if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $child_with_child_queues_raw['12'][$child_wchild_queue_index], $split_value)) {
								$queue_cburst_bytes = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
							} else {
								$queue_cburst_bytes = $child_with_child_queues_raw['12'][$child_wchild_queue_index];
							}
								
							$this->_queues[$running_queue_index]['stats']['queue_cburst_bytes']				= $queue_cburst_bytes;
								
							if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $child_with_child_queues_raw['14'][$child_wchild_queue_index], $split_value)) {
								$queue_cburst_mpu_bytes = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
							} else {
								$queue_cburst_mpu_bytes = $child_with_child_queues_raw['14'][$child_wchild_queue_index];
							}
								
							$this->_queues[$running_queue_index]['stats']['queue_cburst_mpu_bytes']			= $queue_cburst_mpu_bytes;
								
							if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $child_with_child_queues_raw['15'][$child_wchild_queue_index], $split_value)) {
								$queue_cburst_overhead_bytes = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
							} else {
								$queue_cburst_overhead_bytes = $child_with_child_queues_raw['15'][$child_wchild_queue_index];
							}
		
							$this->_queues[$running_queue_index]['stats']['queue_cburst_overhead_bytes']		= $queue_cburst_overhead_bytes;
								
							//stats that do not require calculation
							$this->_queues[$running_queue_index]['stats']['queue_burst_bytes_divider']		= $child_with_child_queues_raw['9'][$child_wchild_queue_index];
							$this->_queues[$running_queue_index]['stats']['queue_cburst_bytes_divider']		= $child_with_child_queues_raw['13'][$child_wchild_queue_index];
							$this->_queues[$running_queue_index]['stats']['queue_level']						= $child_with_child_queues_raw['16'][$child_wchild_queue_index];

						}
					}
				}
			}
		}
	}
	
	public function get_queues($refresh=true, $include_stats=false)
	{
		if($this->_queues == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->_get_stats	= $include_stats;
			$this->execute();
		} elseif($refresh == false) {
			
			if ($include_stats == true && $this->_get_stats == false){
				//result needs to change, we have to fetch a new result
				$this->_queues		= null;
				$this->execute();
			} else {
				//do nothing we have a set of results and are asked not to renew it.
			}

		} else {
			//the default is to run the function
			$this->_queues		= null;
			$this->_get_stats	= $include_stats;
			$this->execute();
		}
		
		return $this->_queues;
	}
	
	public function is_queue_active($queue_name, $refresh)
	{
		if ($queue_name == null) {
			throw new exception("you must provide a queue name", 5202);
		} elseif ($this->_queues == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif ($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} elseif ($refresh == true) {
			//the default is to run the function
			$this->_queues		= null;
			$this->execute();
		} else {
			throw new exception('i need to know if you want to refresh the result', 5201);
		}
			
		if ($this->_queues != null) {
		
			foreach($this->_queues as $queue) {
					
				if ($queue['configuration']['queue_name'] == $queue_name) {
					return $queue;
				}
			}
		}
		
		//if we did not find a match we return false
		return false;
	}

}