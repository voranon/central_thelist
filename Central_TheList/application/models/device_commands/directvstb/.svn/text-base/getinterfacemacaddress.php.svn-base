<?php

class thelist_multipledevice_command_getactiveprovisioningipsonservicepointinterface implements Thelist_Commander_pattern_interface_idevicecommand
{

	private $_interface;

	public function __construct($sp_interface)
	{
		$this->_interface = $sp_interface;

	}

	public function execute()
	{

		$resource_locator			= new Thelist_Model_servicepointresourcelocator();
		
		//must do routers first because we dont want to reset the paths var in pathfinder, in order to get to the routers
		//we must go through the edge switches, so we can reuse the paths.
		$border_routers				= $resource_locator->get_border_routers($this->_interface);
		$edge_switches				= $resource_locator->get_edge_switches($this->_interface);
		
		//combine all boarder router arp tables
		if ($border_routers != false) {

			$arp_table = array();
			foreach($border_routers as $border_router){
				
				//clear the mac addresses from the cam before sweeping
				//this avoids the system seeing mac addresses that may have been seen 
				//during the installation process.
				if ($edge_switches != false) {
					foreach($edge_switches as $edge_switch){
							
						$switch_options['deviceinformation'] = 'clearinterfacemacaddresses';
						$switch_options['clear_mac_interface'] = $edge_switch['inbound_interface'];
						$edge_switch['equipment']->read_from_device($switch_options);
					}
				}

				//sweep the provisioning subnet first
				$router_options['deviceinformation'] 	= 'icmpsweepsubnet';
				$sweep									= $border_router['equipment']->read_from_device($router_options);
				
				$router_options['deviceinformation'] = 'getarptable';
				$arp_table = array_merge($arp_table, $border_router['equipment']->read_from_device($router_options));

			}
		}
			
		//combine all edge switch cam tables
		if ($edge_switches != false) {
				
			$cam_table = array();
			foreach($edge_switches as $edge_switch){
					
				$switch_options['deviceinformation'] = 'getcamtable';
				$cam_table = array_merge($cam_table, $edge_switch['equipment']->read_from_device($switch_options));
				
				$switch_options['deviceinformation'] = 'clearinterfacemacaddresses';
				$edge_switch['equipment']->read_from_device($switch_options);
				
				$switch_edge_ports[]	= $edge_switch['inbound_interface'];
			}
		}
		
		//now find the matches
		if (isset($cam_table) && isset($arp_table)) {

			$i=0;
			foreach($arp_table as $arp_entry) {
					
				foreach($cam_table as $cam_entry) {
					
					//filter out all the entries that are not from the edge ports
					$cam_sp_interface = 'no';

					//is this a cisco switch interface?
					if (preg_match("/(^Fa|^Gi).*\/([0-9]+)$/", $cam_entry->get_interface(), $match)) {
					
						foreach ($switch_edge_ports as $switch_edge_port) {
						//the index starts at 0 the interfaceid starts at 1.
						$interface_id	= ($switch_edge_port->get_if_index() + 1);

							if (preg_match("/(^".$match['1'].").*\/(".$match['2'].")$/", $switch_edge_port->get_if_name(), $match2)) {

								//this should be included because it is located to one of the edge interfaces
								$cam_sp_interface = 'yes';
								
							}
						}
					} else {
						
						throw new exception('looks like the interface belongs to an unknown switch type, please extend this method to avoid surprises');
						
					}
					
					if ($cam_entry->get_macaddress() == $arp_entry->get_macaddress() && $cam_sp_interface == 'yes') {
						
						$arp_matches['arp_entries'][$i]	= $arp_entry;
						$arp_matches['cam_entries'][$i]	= $cam_entry;
						
						$i++;
					}	
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