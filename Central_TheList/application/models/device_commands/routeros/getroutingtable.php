<?php

//exception codes 20000-20099

class thelist_routeros_command_getroutingtable implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_routes=null;
	
	public function __construct($device)
	{
		$this->_device = $device;
	}
	
	public function execute()
	{
		
		//get routing table
		$device_reply = $this->_device->execute_command("/ip route print detail terse without-paging");
		
		
		$reg_ex_other 		= "([0-9]+) +(A S|.*?) +.*dst-address=([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})\/([0-9]+) gateway=([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}) gateway-status=(.*?) (reachable via +(.*?)|unreachable) distance=([0-9]+) scope=([0-9]+)";
		$reg_ex_connected 	= "([0-9]+) +(ADC) +.*dst-address=([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})\/([0-9]+) pref-src=([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}) gateway=(.*?) gateway-status=(.*?) reachable distance=([0-9]+) scope=([0-9]+)";
		
		preg_match_all("/".$reg_ex_other."/", $device_reply->get_message(), $routing_table_other_raw);
		preg_match_all("/".$reg_ex_connected."/", $device_reply->get_message(), $routing_table_connected_raw);
		
		$i=0;
		
		if (isset($routing_table_connected_raw['3'])) {

			foreach ($routing_table_connected_raw['3'] as $conn_index => $conn_route_prefix) {
				
				$this->_routes[$i]	= new Thelist_Deviceinformation_routeentry($conn_route_prefix, $routing_table_connected_raw['4'][$conn_index], 'connected', 0);
				$this->_routes[$i]->set_route_type('connected');
				$this->_routes[$i]->set_route_status('active');
				$i++;
			}
			
		} else {
			throw new exception("routeros device: '".$this->_device->get_fqdn()."' returned an empty connected routing table, that is not possible since we are connecting to the device over ip", 20000);
		}
		
		if (isset($routing_table_other_raw['3'])) {

			foreach ($routing_table_other_raw['3'] as $route_index => $route_prefix) {
				
				$this->_routes[$i]	= new Thelist_Deviceinformation_routeentry($route_prefix, $routing_table_other_raw['4'][$route_index], $routing_table_other_raw['5'][$route_index], $routing_table_other_raw['9'][$route_index]);
				
				if ($routing_table_other_raw['9'][$route_index] == 'ADS') {
					
					$this->_routes[$i]->set_learned_from_protocol('dhcp');
					$this->_routes[$i]->set_route_status('active');
					
				} elseif ($routing_table_other_raw['9'][$route_index] == 'A S') {
					
					$this->_routes[$i]->set_route_type('static');
					$this->_routes[$i]->set_route_status('active');
					
				} elseif ($routing_table_other_raw['9'][$route_index] == 'ADo') {
					
					$this->_routes[$i]->set_learned_from_protocol('ospf');
					$this->_routes[$i]->set_route_status('active');
					
				}

				$i++;
			}
		}
	}
	
	public function get_routing_table($refresh=true)
	{
		if($this->_routes == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		
		return $this->_routes;
	}

}