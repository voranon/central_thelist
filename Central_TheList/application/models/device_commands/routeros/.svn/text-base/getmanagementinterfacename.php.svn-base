<?php

//exception codes 19900-19999

class thelist_routeros_command_getmanagementinterfacename implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_management_interface_name=null;
	
	public function __construct($device)
	{
		$this->_device = $device;
	}
	
	public function execute()
	{
		//get the arp table
		$get_arp_table			= new Thelist_Routeros_command_getarptable($this->_device);
		$arp_table_array 		= $get_arp_table->get_arp_table();
		
		$get_routing_table		= new Thelist_Routeros_command_getroutingtable($this->_device);
		$routing_table_array 	= $get_routing_table->get_routing_table();

		if (isset($routing_table_array['0'])) {
			
			foreach($routing_table_array as $route) {
				
				if ($route->get_subnet_prefix() == '0.0.0.0' && $route->get_prefix_bitmask() == '0') {
					
					if (isset($arp_table_array['0'])) {
						
						foreach($arp_table_array as $arp_entry) {
							
							if ($arp_entry->get_ipaddress() == $route->get_gateway()) {

								$this->_management_interface_name	= $arp_entry->get_interface_name();
							}
						}
					} else {
						throw new exception("routeros device: '".$this->_device->get_fqdn()."' did not return an arp table", 19901);
					}
				}
			}
			
		} else {
			throw new exception("routeros device: '".$this->_device->get_fqdn()."' did not return a routing table", 19900);
		}
		
		if ($this->_management_interface_name == null) {
			throw new exception("routeros device: '".$this->_device->get_fqdn()."' after comparing arp and routing table we did not find a management interface", 19902);
		}
	}
	
	public function get_management_interface_name($refresh=true)
	{
		if($this->_management_interface_name == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		return $this->_management_interface_name;
	}

}