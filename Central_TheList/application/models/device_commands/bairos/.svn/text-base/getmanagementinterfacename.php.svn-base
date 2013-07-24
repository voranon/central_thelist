<?php

//exception codes 12000-12099

class thelist_bairos_command_getmanagementinterfacename implements Thelist_Commander_pattern_interface_idevicecommand 
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
		$get_arp_table			= new Thelist_Bairos_command_getarptable($this->_device);
		$arp_table_array 		= $get_arp_table->get_arp_table();
		
		//get routing table
		$get_routing_table		= new Thelist_Bairos_command_getroutingtable($this->_device);
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
						throw new exception('bairos did not return an arp table', 12000);
					}
				}
			}
			
		} else {
			throw new exception('bairos switch did not return a routing table', 12001);
		}
	}
	
	public function get_management_interface_name()
	{
		$this->execute();
		return $this->_management_interface_name;
	}

}