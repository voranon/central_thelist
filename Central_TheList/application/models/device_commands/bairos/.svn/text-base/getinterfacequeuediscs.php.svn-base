<?php

//exception codes 5300-5399

class thelist_bairos_command_getinterfacequeuediscs implements Thelist_Commander_pattern_interface_idevicecommand
{

	private $_device;
	private $_interface=null;
	private $_queue_discs=null;
	
	private $_get_stats=null;
	
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
		
		$device_reply = $this->_device->execute_command("tc -s -d qdisc show dev ".$interface_name."");
		
		if (!preg_match("/Cannot find device \"".$interface_name."\"/", $device_reply->get_message())) {
			
			//get an index of all the queues
			preg_match_all("/qdisc (htb) ([0-9]+):([0-9]+|) r2q ([0-9]+) default ([0-9]+) direct_packets_stat ([0-9]+) ver ([0-9]+\.[0-9]+)/",$device_reply->get_message(), $connection_queue_discs_raw);
			
			//get first line of stats
			preg_match_all("/Sent ([0-9]+) bytes ([0-9]+) pkt \(dropped ([0-9]+), overlimits ([0-9]+) requeues ([0-9]+)\)/",$device_reply->get_message(), $connection_queue_discs_stats1_raw);
				
			//get second line of stats
			preg_match_all("/rate ([0-9]+.*) ([0-9]+)pps backlog ([0-9]+.*) ([0-9]+)p requeues ([0-9]+)/",$device_reply->get_message(), $connection_queue_discs_stats2_raw);
	
			if (isset($connection_queue_discs_raw['1'])) {
				
				$i=0;
				foreach($connection_queue_discs_raw['1'] as $queue_index => $queue_type) {
	
					$this->_queue_discs[$i]['configuration']['queue_disc_interface_name']			= $interface_name;
					$this->_queue_discs[$i]['configuration']['queue_disc_method']					= $queue_type;
					$this->_queue_discs[$i]['configuration']['queue_disc_type']						= 'qdisc';
					$this->_queue_discs[$i]['configuration']['queue_disc_name']						= $connection_queue_discs_raw['2'][$queue_index];
					$this->_queue_discs[$i]['configuration']['queue_disc_qdisc_parent_name']		= $connection_queue_discs_raw['3'][$queue_index];
					$this->_queue_discs[$i]['configuration']['queue_disc_qdisc_r2q']				= $connection_queue_discs_raw['4'][$queue_index];
					$this->_queue_discs[$i]['configuration']['queue_disc_qdisc_default_class']		= $connection_queue_discs_raw['5'][$queue_index];
					$this->_queue_discs[$i]['configuration']['queue_disc_qdisc_version']			= $connection_queue_discs_raw['7'][$queue_index];
					
					if ($this->_get_stats == true) {
						
						$this->_queue_discs[$i]['stats']['queue_disc_qdisc_direct_packets_stat']	= $connection_queue_discs_raw['6'][$queue_index];
						$this->_queue_discs[$i]['stats']['queue_disc_sent_bytes']				= $connection_queue_discs_stats1_raw['1'][$queue_index];
						$this->_queue_discs[$i]['stats']['queue_disc_sent_packets']				= $connection_queue_discs_stats1_raw['2'][$queue_index];
						$this->_queue_discs[$i]['stats']['queue_disc_dropped_packets']			= $connection_queue_discs_stats1_raw['3'][$queue_index];
						$this->_queue_discs[$i]['stats']['queue_disc_overlimit_packets']			= $connection_queue_discs_stats1_raw['4'][$queue_index];
						$this->_queue_discs[$i]['stats']['queue_disc_requeue_packets']			= $connection_queue_discs_stats1_raw['5'][$queue_index];
							
						//there are many different multipliers we need clean ints.
						if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $connection_queue_discs_stats2_raw['1'][$queue_index], $split_value)) {
							$queue_disc_current_rate = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
						} else {
							$queue_disc_current_rate = $connection_queue_discs_stats2_raw['1'][$queue_index];
						}
							
						if (preg_match("/([0-9]*|\d*\.\d{1}?\d*)(Mbit|Kbit|bit|Kb|b|M|k|G|Gbit|s|m|h|kbit|mbps|gbps|Kbit|Mbps|Gbps)/", $connection_queue_discs_stats2_raw['3'][$queue_index], $split_value)) {
							$queue_qdisc_current_bytes_backlog = $mconverter->convert_to_int($split_value['1'], $split_value['2']);
						} else {
							$queue_qdisc_current_bytes_backlog = $connection_queue_discs_stats2_raw['3'][$queue_index];
						}
						
						$this->_queue_discs[$i]['stats']['queue_disc_current_rate']				= $queue_disc_current_rate;
						$this->_queue_discs[$i]['stats']['queue_disc_current_pps']				= $connection_queue_discs_stats2_raw['2'][$queue_index];
						$this->_queue_discs[$i]['stats']['queue_disc_current_bytes_backlog']		= $queue_qdisc_current_bytes_backlog;
						$this->_queue_discs[$i]['stats']['queue_disc_current_packet_backlog']	= $connection_queue_discs_stats2_raw['4'][$queue_index];
						$this->_queue_discs[$i]['stats']['queue_disc_current_requeue_backlog']	= $connection_queue_discs_stats2_raw['5'][$queue_index];
					}

					$i++;
				}
			}
			
		} else {
			throw new exception("the interface we are trying to get qdiscs from does not exist on the device interface name ".$interface_name." on ".$this->_device->get_fqdn." ", 5300);
		}
	}
	
	public function get_queue_discs($refresh=true, $include_stats=false)
	{
		if($this->_queue_discs == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->_get_stats = $include_stats;
			$this->execute();
		} elseif($refresh == false) {
			
			if ($include_stats == true && $this->_get_stats == false){
				//result needs to change, we have to fetch a new result
				$this->_queue_discs		= null;
				$this->execute();
			} else {
				//do nothing we have a set of results and are asked not to renew it.
			}
			
		} else {
			//the default is to run the function
			$this->_get_stats = $include_stats;
			$this->_queue_discs = null;
			$this->execute();
		}
		
		return $this->_queue_discs;
	}
	
	public function is_qdisc_active($queue_disc_name, $refresh)
	{
		if ($queue_disc_name == null) {
			throw new exception("you must provide a qdisc name", 5301);
		} elseif ($this->_queue_discs == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif ($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} elseif ($refresh == true) {
			//the default is to run the function
			$this->_queue_discs		= null;
			$this->execute();
		} else {
			throw new exception('i need to know if you want to refresh the result', 5302);
		}
	
		if ($this->_queue_discs != null) {
	
			foreach($this->_queue_discs as $qdisc) {
					
				if ($qdisc['configuration']['queue_disc_name'] == $queue_disc_name) {
					return $qdisc;
				}
			}
		}
	
		//if we did not find a match we return false
		return false;
	}
}