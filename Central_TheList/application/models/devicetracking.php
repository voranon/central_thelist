<?php 


//exception codes 6900-6999

class thelist_model_devicetracking
{

	public function __construct()
	{
	
	}
	
	public function update_dhcp_request_tracking()
	{	
		$sql = "SELECT * FROM dhcp_request_track_raw";
		
		$dhcp_requests = Zend_Registry::get('database')->get_syslog_adapter()->fetchAll($sql);
		
		$dhcp_requests_cleared = '';
		
		if (isset($dhcp_requests['0'])) {
			
			foreach($dhcp_requests as $dhcp_request) {
				$resolved = 'no';
				
				preg_match("/DHCPREQUEST for ([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}) from (\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2}) \((.*)\) via eth1.20/", $dhcp_request['dhcp_request_track_raw_message'], $main_result_raw);
				
				//if we dont catch it the first time, try these expressions 
				if (!isset($main_result_raw['1'])) {
				
					preg_match("/DHCPREQUEST for ([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}) \([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\) from (\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2}) \((.*)\) via eth1.20/", $dhcp_request['dhcp_request_track_raw_message'], $main_result_raw);
				
				} 
				
				if (!isset($main_result_raw['1'])) {
				
					preg_match("/DHCPREQUEST for ([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}) from (\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2}) via eth1.20/", $dhcp_request['dhcp_request_track_raw_message'], $main_result_raw);
				
				}


				if (isset($main_result_raw['3'])) {
					//if not then we have an unknow device type
					
					//this gives us the following items
					$full_message 			= $main_result_raw['0'];
					$ip_address 			= $main_result_raw['1'];
					$mac_address 			= $main_result_raw['2'];
					$eq_type_raw 			= $main_result_raw['3'];

					//find receivers
					if (preg_match("/DIRECTV-(.*)-\w{8}/", $eq_type_raw, $type_result) && $resolved == 'no') {		

						if ($type_result['1'] == 'H25') {
							$eq_type_id = 40;
							$resolved = 'yes';
						}
						if ($type_result['1'] == 'HR24') {
							$eq_type_id = 39;
							$resolved = 'yes';
						}
						if ($type_result['1'] == 'HR34') {
							$eq_type_id = 41;
							$resolved = 'yes';
						
						}
					}
					
					//find mikrotik routers, this is currently not enough information to setup the device,
					//but we store them anyways
					if (preg_match("/(MikroTik)/", $eq_type_raw, $type_result) && $resolved == 'no') {
						$eq_type_id = 0;
						$resolved = 'yes';
					}
					
					//find customer routers
					if (preg_match("/(WGR[0-9]+|WRT[0-9]+)/", $eq_type_raw, $type_result) && $resolved == 'no') {
						$eq_type_id = 46;
						$resolved = 'yes';
					}
				

					//if we could resolve the request
					if ($resolved == 'yes') {
	
						//clean up the mac address, only have to do this if the type resolves
						$clean_mac = strtoupper(preg_replace("/:/", "", $mac_address));
						
						$sql = "SELECT COUNT(discovered_devices_id) AS counter FROM device_tracking
								WHERE mac_address='".$clean_mac."'
								";
						
						$existing = Zend_Registry::get('database')->get_tracking_adapter()->fetchOne($sql);
	
						if ($existing == 0) {
	
							$data = array(
								
													'eq_type_id'    			=>  $eq_type_id,
													'mac_address'   			=>  $clean_mac,
													'requested_ip_address'    	=>  $ip_address,
													'discover_host'   			=>  $dhcp_request['dhcp_server_host'],
													'request_time'    			=>  $dhcp_request['dhcp_request_received_date_time'],
													'full_syslog_message'   	=>  $dhcp_request['dhcp_request_track_raw_message'],
													
							
							);
							
							$trace 		= debug_backtrace();
							$method 	= $trace[0]["function"];
							$class		= get_class($this);
							
							Zend_Registry::get('database')->get_device_tracking()->insert($data);
							
						}
							//if resolved then add to list of records to be removed
						$dhcp_requests_cleared .= $dhcp_request['dhcp_request_track_raw_id'] . ",";
					}
				}
			}
			
			if ($dhcp_requests_cleared != '') {
				
				//now we delete all the entries we found
				$sql_del = 	"DELETE FROM dhcp_request_track_raw
										WHERE dhcp_request_track_raw_id IN (".substr($dhcp_requests_cleared, 0, -1).")
										";
					
				Zend_Registry::get('database')->get_syslog_adapter()->query($sql_del);
				
			}
		}
	}
	
	

}



?>