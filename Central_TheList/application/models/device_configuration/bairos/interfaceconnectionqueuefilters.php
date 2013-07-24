<?php

//exception codes 14700-14799
require_once 'Net/IPv4.php';

class thelist_bairos_config_interfaceconnectionqueuefilters implements Thelist_Commander_pattern_interface_ideviceconfiguration
{
	private $_interface;
	private $_filter_configs=null;
	private $_filter_count=0;


	public function __construct($interface)
	{
		$this->_interface 				= $interface;
	}

	public function generate_config_array()
	{
		//bandwidth filter configs are much the same because they are driven by the configuration system
		//a class has been setup to drive all common config array generation
		//but in this case the config for the bai routers require that the queue_filter_name be a base 16 value, while the database stores 
		//base 10 values, so we do the create config here.
		
		if ($this->_interface->get_connection_queues() != null) {

			foreach ($this->_interface->get_connection_queues() as $queue) {
		
				//then get all the filters for each queue
				if ($queue->get_connection_queue_filters() != null) {
						
					foreach ($queue->get_connection_queue_filters() as $filter) {
						$this->add_single_filter_config($filter, $queue);
					}
				}
			}
		}
		
		if ($this->_filter_configs != null) {
			return $this->_filter_configs;
		} else {
			return false;
		}
	}
	
	public function generate_config_device_syntax($config_array)
	{
		//this method comes in handy when we need to start writing the config to a file and for upload and execution
	}
	
	public function add_single_filter_config($filter_obj, $queue)
	{
		$this->_filter_configs[$this->_filter_count]['configuration']['queue_filter_interface_name'] 		= $this->_interface->get_if_name();
		$this->_filter_configs[$this->_filter_count]['configuration']['queue_filter_qdisc_parent_name'] 	= $this->_interface->get_if_id();
		$this->_filter_configs[$this->_filter_count]['configuration']['queue_filter_flowid_qdisc_parent'] 	= $this->_interface->get_if_id();
		
		//this is the queue name that the filter is putting matches into
		$this->_filter_configs[$this->_filter_count]['configuration']['queue_filter_flowid_queue_parent'] 	= $queue->get_connection_queue_name();
		$this->_filter_configs[$this->_filter_count]['configuration']['queue_filter_priority'] 				= $filter_obj->get_connection_queue_filter_priority();
			
		
		//database stores base 10 filter names, centos requires base 16, and the numeric representation of the number cannot be larger than 4096
		if ($filter_obj->get_connection_queue_filter_name() > 4096) {
			throw new exception("filter name for bairos exeeds 4096 with value: ".$filter_obj->get_connection_queue_filter_name().", this is not possible to implement", 14700);
		} else {
			$this->_filter_configs[$this->_filter_count]['configuration']['queue_filter_name'] 					= base_convert($filter_obj->get_connection_queue_filter_name(), 10, 16);
		}
		
		//can only do ip so far
		$this->_filter_configs[$this->_filter_count]['configuration']['queue_filter_protocol'] 					= 'ip';
		
		//get filter matches
		$frame_matches = $filter_obj->get_frame_matches();
		
		if ($frame_matches != null) {
			$j=0;
			foreach ($frame_matches as $frame_match) {
				
				if ($frame_match->get_frame_header_id() == 1 || $frame_match->get_frame_header_id() == 2) {
		
					if ($frame_match->get_frame_header_id() == 1) {
						$header = 'ipv4_dst_ip';
					} elseif ($frame_match->get_frame_header_id() == 2) {
						$header = 'ipv4_src_ip';
					}

					if ($frame_match->get_frame_match_value_2() != null) {

					
						$ipconverter	= new Thelist_Utility_ipconverter();
						//get all ips in the range
						$ips_in_range	= $ipconverter->get_all_ips_in_range($frame_match->get_frame_match_value_1(), $frame_match->get_frame_match_value_2());
						//convert this range into cidr masks
						$subnets		= $ipconverter->get_all_possible_subnets_from_ips($ips_in_range);
				
					} else {
				
						//standalone address
						$subnets['standalone']['0']				= $frame_match->get_frame_match_value_1();
					}
					
					foreach ($subnets as $index => $subnet) {
					
						//the subnet function creates a pool of ips that could not be agregated. if needed
						//this must be very strict, == will result in a match on everything we need ===
						if ($index === 'standalone') {
							foreach ($subnet as $single_ip) {
								$this->_filter_configs[$this->_filter_count]['configuration']['frame_match'][$j]['header']			= $header;
								$this->_filter_configs[$this->_filter_count]['configuration']['frame_match'][$j]['match_value_1']	= $single_ip;
								$j++;
							}
								
						} else {
							
							$ip_calc = new Net_IPv4();
							$ip_calc->ip 		= $subnet['subnet_address'];
							$ip_calc->bitmask 	= $subnet['subnet_cidr'];
								
							$error = $ip_calc->calculate();

							if (!is_object($error)) {
								$this->_filter_configs[$this->_filter_count]['configuration']['frame_match'][$j]['header']			= $header;
								$this->_filter_configs[$this->_filter_count]['configuration']['frame_match'][$j]['match_value_1']	= $ip_calc->network;
								$this->_filter_configs[$this->_filter_count]['configuration']['frame_match'][$j]['match_value_2']	= $ip_calc->broadcast;
								$j++;
							} else {
								throw new exception("one of the ranges provided results in an error when calculating the broadcast", 14700);
							}
							
						}
					}
				}
			}
		}
		
		$this->_filter_count++;
	}
	
	public function get_filter_configs()
	{
		return $this->_filter_configs;
	}
}