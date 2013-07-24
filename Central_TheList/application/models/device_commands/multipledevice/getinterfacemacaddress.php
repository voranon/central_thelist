<?php

class thelist_multipledevice_command_getmacfromip implements Thelist_Commander_pattern_interface_idevicecommand
{

	private $_ip_obj;
	private $database;

	public function __construct($ip_obj)
	{
		$this->_ip_obj = $ip_obj;


	}

	public function execute()
	{

		$sql = 			"SELECT v.if_id FROM ip_address_mapping ipam
						INNER JOIN vlans v ON v.vlan_pri_key_id=ipam.vlan_pri_key_id
						WHERE ipam.ip_address_id='".$this->_ip_obj->get_ip_address_id."'
						";
		
		$if_ids 	= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		if (isset($if_ids['0'])) {
			
			$resource_locator			= new Thelist_Model_servicepointresourcelocator();
			
			foreach ($if_ids as $if_id) {
				
				$interface_obj	= new Thelist_Model_equipmentinterface($if_id);
				
				$border_routers				= $resource_locator->get_border_routers($interface_obj);
				
			}
		}

		
		
		
		//combine all boarder router arp tables
		if ($border_routers != false) {

			$arp_table = array();
			foreach($border_routers as $border_router){

				$router_options['deviceinformation'] = 'getarptable';
				$arp_table = array_merge($arp_table, $border_router['equipment']->read_from_device($router_options));

			}
		}
		
		$equipment	= new Thelist_Model_equipments($interface->get_eq_id);
		
		//if this is an FQDN convert to IP
		if ($dhfgn) {
			
			gethostbyname();
			
			
		} 
		
		//now find the matches
		if (isset($arp_table)) {

			foreach($arp_table as $arp_entry) {
				
				if ($arp_entry->get_ipaddress() == $ip_address {

				
				}
			}
		}

		if (isset($arp_matches)) {
			
			return $arp_matches;
			
		} else {
			
			return false;
			
		}
	}
}