<?php

//exception codes 5100-5199

require_once 'Net/IPv4.php';

class thelist_bairos_command_getinterfacequeuefilters implements Thelist_Commander_pattern_interface_idevicecommand
{

	private $_device;
	private $_interface;
	
	private $_queue_filters=null;
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
		} elseif ($this->_interface == null) {
			throw new exception("interface cannot be null ", 5102);
		} else {
			$interface_name		= $this->_interface;
		}

		$ip_class 		= new Net_IPv4();
		$ip_converter 	= new Thelist_Utility_ipconverter();

		$device_reply = $this->_device->execute_command("tc -s -d filter show dev ".$interface_name."");

		//get global index of the filter config
		$array_of_lines = explode("\n", $device_reply->get_message());
		
		if ($array_of_lines != null) {
			
			$j=0;
			foreach ($array_of_lines as $line) {
				
				if (preg_match("/filter parent ([0-9]+): protocol (.*) pref ([0-9]+) u32 fh ([A-Fa-f0-9]+)::([A-Fa-f0-9]+) order ([0-9]+) key ht ([A-Fa-f0-9]+) bkt ([0-9]+) flowid ([0-9]+):([0-9]+)  \(rule hit ([0-9]+) success ([0-9]+)\)/", $line, $line_filters1_raw)) {
					
					//this remains set until a new filter is matched, so as we are moving line by line all matches
					//are grouped with the filter
					$filter_name		= $line_filters1_raw['5'];
					
					//filter protocol
					$filter_protocol	= $line_filters1_raw['2'];
					
					if (!isset($this->_queue_filters[$filter_name])) {
	
						$this->_queue_filters[$filter_name]['configuration']['queue_filter_interface_name']			= $interface_name;
						$this->_queue_filters[$filter_name]['configuration']['queue_filter_qdisc_parent_name']		= $line_filters1_raw['1'];
						$this->_queue_filters[$filter_name]['configuration']['queue_filter_flowid_qdisc_parent']	= $line_filters1_raw['9'];
						$this->_queue_filters[$filter_name]['configuration']['queue_filter_flowid_queue_parent']	= $line_filters1_raw['10'];
						$this->_queue_filters[$filter_name]['configuration']['queue_filter_priority']				= $line_filters1_raw['3'];
						
						//3 digit base 16 value (base 10 = numbers from 0-4096)
						$this->_queue_filters[$filter_name]['configuration']['queue_filter_name']					= $filter_name;
						$this->_queue_filters[$filter_name]['configuration']['queue_filter_protocol']				= $filter_protocol;
					
						//dynamic_configuration these (this) value has nothing to do with any other configuration database or otherwise
						//its created dynamically at the time of setup, but in order to remove the filter from the running config we need this value
						//it is not part on configuration because it should not be compared to the new incoming config, if thats what the result is used for.
						$this->_queue_filters[$filter_name]['dynamic_configuration']['queue_filter_run_time_name']	= $line_filters1_raw['4'];
							
					}
					
					if ($this->_get_stats == true) {
							
						//these stats are aggreage, because there are 2 ways to add a filter
						//either adding matches to a single filter or creating a seperate filter entry with same name, priority etc and matches
						//we do not consider the second a seperate filter because they have the same name, so we add all the hits and sucesses up
						if (!isset($this->_queue_filters[$filter_name]['stats']['filter']['queue_filter_rule_hit'])) {
							$this->_queue_filters[$filter_name]['stats']['filter']['queue_filter_rule_hit'] = 0;
						}
						if (!isset($this->_queue_filters[$filter_name]['stats']['filter']['queue_filter_rule_success'])) {
							$this->_queue_filters[$filter_name]['stats']['filter']['queue_filter_rule_success'] = 0;
						}
						
						$this->_queue_filters[$filter_name]['stats']['filter']['queue_filter_rule_hit']			+= $line_filters1_raw['11'];
						$this->_queue_filters[$filter_name]['stats']['filter']['queue_filter_rule_success']		+= $line_filters1_raw['12'];
					}
				}
					
				//all ip based filter matches
				if (isset($filter_protocol)) {

					if ($filter_protocol == 'ip') {
						
						if (preg_match("/match ([A-Fa-f0-9]{8})\/([A-Fa-f0-9]{8}) at ([0-9]+) \(success ([0-9]+) \)/", $line, $line_filters2_raw)) {

							//frame matches
							if ($line_filters2_raw['3'] == 16) {
									
								$this->_queue_filters[$filter_name]['configuration']['frame_match'][$j]['header']						= 'ipv4_dst_ip';
								$this->_queue_filters[$filter_name]['configuration']['frame_match'][$j]['match_value_1']				= $ip_class->htoa($line_filters2_raw['1']);
									
								if ($ip_converter->convert_dotted_subnet_to_cidr($ip_class->htoa($line_filters2_raw['2'])) < 32) {
							
									$ip_class->ip = $ip_class->htoa($line_filters2_raw['1']);
									$ip_class->bitmask = $ip_converter->convert_dotted_subnet_to_cidr($ip_class->htoa($line_filters2_raw['2']));
									$problem = $ip_class->calculate();
							
									if (!is_object($problem))
									{
										$this->_queue_filters[$filter_name]['configuration']['frame_match'][$j]['match_value_2']		= $ip_class->broadcast;
									}
								}
									
							} elseif ($line_filters2_raw['3'] == 12) {
								
								$this->_queue_filters[$filter_name]['configuration']['frame_match'][$j]['header']						= 'ipv4_src_ip';
								$this->_queue_filters[$filter_name]['configuration']['frame_match'][$j]['match_value_1']				= $ip_class->htoa($line_filters2_raw['1']);
									
								if ($ip_converter->convert_dotted_subnet_to_cidr($ip_class->htoa($line_filters2_raw['2'])) < 32) {
							
									$ip_class->ip = $ip_class->htoa($line_filters2_raw['1']);
									$ip_class->bitmask = $ip_converter->convert_dotted_subnet_to_cidr($ip_class->htoa($line_filters2_raw['2']));
									$problem = $ip_class->calculate();
							
									if (!is_object($problem))
									{
										$this->_queue_filters[$filter_name]['configuration']['frame_match'][$j]['match_value_2']		= $ip_class->broadcast;
									}
								}
									
							} elseif ($line_filters2_raw['3'] == 0) {
								
								//this is broken
								$this->_queue_filters[$filter_name]['configuration']['frame_match'][$j]['header']						= 'ipv4_tos_ip_BROKEN DONT USE YET';
								//cannot figure out the format of the tos bits
								$this->_queue_filters[$filter_name]['configuration']['frame_match'][$j]['match_value_1']				= base_convert($line_filters2_raw['1'], 16, 16);
								$this->_queue_filters[$filter_name]['configuration']['frame_match'][$j]['match_value_2']				= base_convert($line_filters2_raw['2'], 16, 16);
									
							} else {
								throw new exception("we encountered an unknown offset value: ".$line_filters2_raw['3']." ", 5100);
							}
							
							if ($this->_get_stats == true) {
								$this->_queue_filters[$filter_name]['stats']['frame_match'][$j]['queue_filter_match_success']		= $line_filters2_raw['4'];
							}
						}
					}
				}

				//increment the match index by one
				$j++;
			
			}
		}

		//straighten out the array, the indexes are off
		if ($this->_queue_filters != null) {
			
			foreach($this->_queue_filters as $filter_index => $filter) {
				
				//clean up filter frame match index
				if (isset($filter['configuration']['frame_match'])) {
					$this->_queue_filters[$filter_index]['configuration']['frame_match'] = array_values($this->_queue_filters[$filter_index]['configuration']['frame_match']);
				}
				
				//clean up frame match stats index
				if (isset($filter['stats']['frame_match'])) {
					$this->_queue_filters[$filter_index]['stats']['frame_match'] = array_values($this->_queue_filters[$filter_index]['stats']['frame_match']);
				}
			}
			
			//clean up the filter index
			$this->_queue_filters = array_values($this->_queue_filters);
		}
	}
	
	public function get_queue_filters($refresh=true, $include_stats=false)
	{
		if($this->_queue_filters == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->_get_stats	= $include_stats;
			$this->execute();
		} elseif($refresh == false) {
			
			if ($include_stats == true && $this->_get_stats == false){
				//result needs to change, we have to fetch a new result
				$this->_queue_filters		= null;
				$this->execute();
			} else {
				//do nothing we have a set of results and are asked not to renew it.
			}

		} else {
			//the default is to run the function
			$this->_queue_filters		= null;
			$this->_get_stats	= $include_stats;
			$this->execute();
		}
		
		return $this->_queue_filters;
	}
	
	public function is_filter_active($filter_name, $refresh)
	{
		if ($filter_name == null) {
			throw new exception("you must provide a filter name", 5100);
		} elseif ($this->_queue_filters == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif ($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} elseif ($refresh == true) {
			//the default is to run the function
			$this->_queue_filters		= null;
			$this->execute();
		} else {
			throw new exception('i need to know if you want to refresh the result', 5101);
		}

		if ($this->_queue_filters != null) {
		
			foreach($this->_queue_filters as $filter) {
					
				if ($filter['configuration']['queue_filter_name'] == $filter_name) {
					return $filter;
				}
			}
		}
		
		//if we did not find a match we return false
		return false;
	}
	

}