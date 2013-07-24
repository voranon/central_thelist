<?php

//exception codes 6500-6599

class thelist_cisco_command_getmanagementinterfacename implements Thelist_Commander_pattern_interface_idevicecommand 
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
		$get_arp_table			= new Thelist_Cisco_command_getarptable($this->_device);
		$arp_table_array 		= $get_arp_table->get_arp_table();
		
		$get_routing_table		= new Thelist_Cisco_command_getroutingtable($this->_device);
		$routing_table_array 	= $get_routing_table->get_routing_table();
		
		//get the cam table
		$get_cam_table			= new Thelist_Cisco_command_getcamtable($this->_device);
		$cam_table_array 		= $get_cam_table->get_cam_table();
		
		if (isset($routing_table_array['0'])) {
			
			foreach($routing_table_array as $route) {
				
				if ($route->get_subnet_prefix() == '0.0.0.0' && $route->get_prefix_bitmask() == '0') {
					
					if (isset($arp_table_array['0'])) {
						
						foreach($arp_table_array as $arp_entry) {
							
							if ($arp_entry->get_ipaddress() == $route->get_gateway()) {
								
								if (isset($cam_table_array['0'])) {
									
									foreach($cam_table_array as $cam_entry) {
										
										if ($cam_entry->get_macaddress() == $arp_entry->get_macaddress()) {

											$this->_management_interface_name	= $cam_entry->get_interface_name();
										}
									}
								} else {
									throw new exception('6502 cisco switch did not return a cam table', 6502);
								}
							}
						}
					} else {
						throw new exception('6501 cisco switch did not return an arp table', 6501);
					}
				}
			}
			
		} else {
			throw new exception('6500 cisco switch did not return a routing table', 6500);
		}
	}
	
	public function get_management_interface_name()
	{
		$this->execute();
		return $this->_management_interface_name;
	}

}