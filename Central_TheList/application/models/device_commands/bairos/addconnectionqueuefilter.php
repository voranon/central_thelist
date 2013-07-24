<?php

//exception codes 15300-15399

class thelist_bairos_command_addconnectionqueuefilter implements Thelist_Commander_pattern_interface_idevicecommand 
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
		//i.e $k is numerical index for frame matching
		//['configuration']['queue_parent_name']
		//['configuration']['queue_filter_priority']
		//['configuration']['queue_filter_name'] (must be the base 16 representation of the filter name)
		//['configuration']['frame_match'][$k]['header']
		//['configuration']['frame_match'][$k]['match_value_1']
		//['configuration']['frame_match'][$k]['match_value_2']
	
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
				
			$connection_queue			= new Thelist_Model_connectionqueue($this->_connection_queue_filter->get_connection_queue_id());
				
			$connection_queue_name		= $connection_queue->get_connection_queue_name();
			$filter_priority			= $this->_connection_queue_filter->get_connection_queue_filter_priority();
			$filter_name				= base_convert($this->_connection_queue_filter->get_connection_queue_filter_name(), 10, 16);
			
			if (is_object($this->_interface)) {
				$config_generator		= new Thelist_Bairos_config_interfaceconnectionqueuefilters($this->_interface);
				$config_generator->add_single_filter_config($this->_connection_queue_filter, new Thelist_Model_connectionqueue($this->_connection_queue_filter->get_connection_queue_id()));
				$single_filter_config	= $config_generator->get_filter_configs();
				$new_config_array		= $single_filter_config['0'];
				
			} else {
				throw new exception("in this case you cannot provide a mixed enviroment, either both as strings or both as objects ", 15300);
			}

		} else {

			$connection_queue			= $this->_connection_queue_filter['configuration']['queue_parent_name'];
			$connection_queue_name		= $this->_connection_queue_filter['configuration']['queue_parent_name'];
			$filter_priority			= $this->_connection_queue_filter['configuration']['queue_filter_priority'];
			$filter_name				= $this->_connection_queue_filter['configuration']['queue_filter_name'];
			$new_config_array			= $this->_connection_queue_filter;
		}
		
		//object model only check, we may not have enough information to do this in the string model
		//now check if the parent queue is running if it is not we cannot setup the filter
		if (is_object($this->_connection_queue_filter)) {
		
			$running_queues		= new Thelist_Bairos_command_getinterfaceconnectionqueues($this->_device, $this->_interface);
			$active				= $running_queues->is_queue_active($connection_queue_name, true);
		
			if ($active == false) {
				
				//since we are bringing up the queue here it will bring up the filter we are slated to setup so we will cruise through the next few steps
				$add_queue	= new Thelist_Bairos_command_addconnectionqueue($this->_device, $this->_interface, $connection_queue);
				$add_queue->execute();
			}
		}
	
		//get current running filters
		$device_filters				= new Thelist_Bairos_command_getinterfacequeuefilters($this->_device, $interface);
		$filter_running				= $device_filters->is_filter_active($filter_name, true);

		if ($filter_running != false) {

			//there can be many filters with the same name, because there are 2 ways of creating them
			//either adding matches to a single filter or creating a seperate filter entry with same name, priority etc and matches
			//we do not consider the second a seperate filter because they have the same name, but because filters can be added outside
			//the object model we have to make sure that the filter that was found is equal to the one we are tasked with setting up.
			//so we do a more strict check.
			
			//first we check the easy return, does the single filter returned but the is_filter_active method match the new filter?
			
			//class to find diffs
			$diff			= new Thelist_Multipledevice_config_configdifferences($new_config_array, $filter_running);
			$config_diffs	= $diff->generate_config_array();
			
			if ($config_diffs != false) {

				//if the first returned does not match we move on to the big check and bring in all the filters and check agains all that match the name
				//no need to refresh
				$all_running_filters				= $device_filters->get_queue_filters(false, false);
				
				//we know there is at least one filter so no chance its empty
				foreach($all_running_filters as $running_filter) {
					
					$diff2			= new Thelist_Multipledevice_config_configdifferences($new_config_array, $running_filter);
					$compare_config	= $diff2->generate_config_array();
					
					if ($compare_config == false) {
						
						//perfect match, no need to override the filter it already exists, now change the $filter_running response
						$filter_running = true;
						
					}
				}
			} else {
				//perfect match, no need to override the filter it already exists, now change the $filter_running response
				$filter_running = true;
			}
		}

		//if filter is not running
		if ($filter_running !== true) {

			//filter is not running so we add it. but first we remove it, if the $filter_running is an array, because that means that 
			//there is a filter running with the same name just not the correct config
			if (is_array($filter_running)) {
				$remove_filter	= new Thelist_Bairos_command_removeconnectionqueuefilter($this->_device, $this->_interface, $this->_connection_queue_filter);
				$remove_filter->execute();
			}
			
			//base command, currently everything is IP but the protocol should be implied from the frame matches as they carry protocol information
			$base_command = "sudo /sbin/tc filter add dev ".$interface_name." parent ".$interface_qdisc_id.": handle ::".$filter_name." prio ".$filter_priority." protocol ip u32";

			if (isset($new_config_array['configuration']['frame_match'])) {
				
				foreach ($new_config_array['configuration']['frame_match'] as $frame_match) {
					
					if ($frame_match['header'] == 'ipv4_dst_ip' || $frame_match['header'] == 'ipv4_src_ip') {
						
						if ($frame_match['header'] == 'ipv4_dst_ip') {
							$direction = 'dst';
						} elseif ($frame_match['header'] == 'ipv4_src_ip') {
							$direction = 'src';
						}
						
						//calculate the subnets
						if (isset($frame_match['match_value_2'])) {
							
							$ipconverter	= new Thelist_Utility_ipconverter();
							//get all ips in the range
							$ips_in_range	= $ipconverter->get_all_ips_in_range($frame_match['match_value_1'], $frame_match['match_value_2']);
							//convert this range into cidr mask
							$subnets		= $ipconverter->get_all_possible_subnets_from_ips($ips_in_range);
							
							foreach ($subnets as $index => $subnet) {
							
								//the subnet function creates a pool of ips that could not be agregated. if needed
								//this must be very strict, == will result in a match on everything we need ===
								if ($index === 'standalone') {
									foreach ($subnet as $single_ip) {
										$appended_commands[] = $base_command . " match ip ".$direction . " " . $single_ip . "/32";
									}
										
								} else {
									$appended_commands[] = $base_command . " match ip ".$direction . " " . $subnet['subnet_address'] . "/" . $subnet['subnet_cidr'];
								}
							}
							
						} else {

							//standalone address
							$appended_commands[] = $base_command . " match ip ".$direction . " " . $frame_match['match_value_1'] . "/32";
						}
					}
				}
				
				//we are done with all the frame matches, now we add the flow id that will place packets that match in the correct class
				//this must be the last adition after all frame matches
				foreach ($appended_commands as $command) {
						$completed_commands[] = $command . " flowid ".$interface_qdisc_id.":".$connection_queue_name;
				}

			} else {
				throw new exception("new config is missing frame matches, you must provide at least one ", 15301);
			}

			//now execute this filter on the device
			foreach ($completed_commands as $single_new_filter_component) {
				$device_reply = $this->_device->execute_command($single_new_filter_component);
				
				//we check if the command errors
				if ($device_reply->get_message() != '') {
					throw new exception("we got an error when trying to add a new filter", 15302);
				}
			}
			
			//we check if the filter is running

			$filter_running				= $device_filters->is_filter_active($filter_name, true);
			
			if ($filter_running['configuration']['queue_filter_priority'] != 1) {
				echo "\n <pre> vsdvav  \n ";
				print_r($filter_running);
				echo "\n 2222 \n ";
				print_r($new_config_array);
				echo "\n 3333 \n ";
				print_r($base_command);
				echo "\n 4444 </pre> \n ";
				die;
			}
			
			
			if ($filter_running == false) {
				
				//sometimes it takes the router a few seconds to implement the filter
				sleep(2);
				$filter_running				= $device_filters->is_filter_active($filter_name, true);
					
				if ($filter_running == false) {
					throw new exception('we are adding a filter, but after trying we failed', 15303);
				}
				
			}
			
		}
	}
}