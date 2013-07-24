<?php

//exception codes 12100-12199

class thelist_bairos_command_getroutingtable implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_routes=null;
	
	public function __construct($device)
	{
		$this->_device = $device;
	}
	
	public function execute()
	{
		$device_reply = $this->_device->execute_command('route -n');
		
		//the commented out version is more accurate with interface names, but i dont trust that we cover all combinations, so i left it out 
		//preg_match_all("/([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}|default) +([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}|\*) +([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}) +(.*) +([0-9]+) +([0-9]+) +([0-9]+) +(.{3}[0-9]\.[0-9]+|.{3}[0-9]:[0-9]+|.{3}[0-9])/", $device_reply->get_message(), $routes_raw);
		preg_match_all("/([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}|default) +([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}|\*) +([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}) +(.*) +([0-9]+) +([0-9]+) +([0-9]+) +(.*)/", $device_reply->get_message(), $routes_raw);
		
		if (isset($routes_raw['1'])) {
			
			$ip_converter	= new Thelist_Utility_ipconverter();
			
			foreach ($routes_raw['1'] as $route_index => $subnet_address) {
				
				if ($subnet_address == 'default') {
					$this->_routes[]	= new Thelist_Deviceinformation_routeentry('0.0.0.0', $ip_converter->convert_dotted_subnet_to_cidr($routes_raw['3'][$route_index]), $routes_raw['2'][$route_index], $routes_raw['5'][$route_index]);
				} else {
					$this->_routes[]	= new Thelist_Deviceinformation_routeentry($subnet_address, $ip_converter->convert_dotted_subnet_to_cidr($routes_raw['3'][$route_index]), $routes_raw['2'][$route_index], $routes_raw['5'][$route_index]);
				}
			}
			
		} else {
			$this->_routes	= null;
		}
	}
	
	public function get_routing_table()
	{
		//used for validation must be fresh result
		$this->execute();
		return $this->_routes;
	}

}