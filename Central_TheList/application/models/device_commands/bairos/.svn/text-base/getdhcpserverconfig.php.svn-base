<?php

//exception codes 5500-5599

class thelist_bairos_command_getdhcpserverconfig implements Thelist_Commander_pattern_interface_idevicecommand
{

	private $_device;
	private $_interface;
	private $_dhcp_configuration=null;
	
	public function __construct($device, $interface)
	{
		//$interface
		//object	= interface_obj
		//string	= interface name
		
		$this->_device 			= $device;
		$this->_interface 		= $interface;
	}
	
	public function execute()
	{
		
		if (is_object($this->_interface)) {
			$interface_name			= $this->_interface->get_if_name();
		} else {
			$interface_name			= $this->_interface;
		}

		$ip_converter 	= new Thelist_Utility_ipconverter();

		$device_reply = $this->_device->execute_command("cat ".Thelist_Utility_staticvariables::get_bairouter_root_config_path()."/dhcp_server/".$interface_name."");

		if (!preg_match("/No such file or directory/", $device_reply->get_message())) {
		
			//get the domain-name
			preg_match("/option domain-name \"(.*)\";/", $device_reply->get_message(), $dhcp_domain_name_raw);
			
			//get the dns_servers
			preg_match("/option domain-name-servers (.*);/", $device_reply->get_message(), $dhcp_dns_servers_raw);
	
			//get the default-lease-time
			preg_match("/default-lease-time ([0-9]+);/", $device_reply->get_message(), $dhcp_default_lease_time_raw);
			
			//get the max-lease-time
			preg_match("/max-lease-time ([0-9]+);/", $device_reply->get_message(), $dhcp_max_lease_time_raw);
			
			//get the authoritative
			preg_match("/(authoritative);/", $device_reply->get_message(), $dhcp_authoritative_raw);
			
			//get the log-facility
			preg_match("/log-facility (.*);/", $device_reply->get_message(), $dhcp_log_facility_raw);
			
			//get the ntp_servers
			preg_match("/option ntp-servers (.*);/", $device_reply->get_message(), $dhcp_ntp_servers_raw);
			
			//get shared networks
			preg_match_all("/shared-network (.*) \{   ###NET([0-9]+)/",$device_reply->get_message(), $dhcp_shared_networks_raw);
			
			//get shared network subnets
			preg_match_all("/subnet ([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}) netmask ([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}) \{   ###NET([0-9]+)/",$device_reply->get_message(), $dhcp_shared_network_subnets_raw);
			
			//get shared network subnet ranges
			preg_match_all("/range ([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}) ([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3});   ###NET([0-9]+)/",$device_reply->get_message(), $dhcp_shared_network_ranges_raw);
				
			//get shared network subnet ranges
			preg_match_all("/option routers ([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3});   ###NET([0-9]+)/",$device_reply->get_message(), $dhcp_shared_network_routers_raw);
			
			//get shared network subnet ranges
			preg_match_all("/host (.*) \{   ###HOST([0-9]+)/",$device_reply->get_message(), $dhcp_static_host_name_raw);
			
			//get shared network subnet mac
			preg_match_all("/hardware ethernet (\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2});   ###HOST([0-9]+)/",$device_reply->get_message(), $dhcp_static_host_mac_raw);
			
			//get shared network subnet mac
			preg_match_all("/fixed-address ([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3});\}   ###HOST([0-9]+)/",$device_reply->get_message(), $dhcp_static_host_ip_raw);
			
			//create return array
			$return['configuration'] 	= array();
			$return['networks'] 		= array();
			$return['hosts'] 			= array();
			
			//global onfiguration
			
			//get administrative status
			$dhcp_status			= new Thelist_Bairos_command_getdhcpserverstatus($this->_device, $this->_interface);
			
			$return['configuration']['administrative_status'] =  $dhcp_status->get_dhcp_server_admin_status();
			
			//domain name
			if (isset($dhcp_domain_name_raw['1'])) {
				$return['configuration']['domain_name'] = $dhcp_domain_name_raw['1'];
			}
			
			//interface name
			if (isset($dhcp_domain_name_raw['1'])) {
				$return['configuration']['interface_name'] = $interface_name;
			}
			
			//default_lease_time
			if (isset($dhcp_default_lease_time_raw['1'])) {
				$return['configuration']['default_lease_time'] = $dhcp_default_lease_time_raw['1'];
			}
			
			//max_lease_time
			if (isset($dhcp_max_lease_time_raw['1'])) {
				$return['configuration']['max_lease_time'] = $dhcp_max_lease_time_raw['1'];
			}
			
			//authoritative_status
			if (isset($dhcp_authoritative_raw['1'])) {
				$return['configuration']['authoritative_status'] = $dhcp_authoritative_raw['1'];
			}
			
			//logging facilities
			if (isset($dhcp_log_facility_raw['1'])) {
				$return['configuration']['logging_facility'] = $dhcp_log_facility_raw['1'];
			}
			
			//ntp servers
			if (isset($dhcp_ntp_servers_raw['1'])) {
	
				$exploded_array =	explode(',', $dhcp_ntp_servers_raw['1']);
	
				foreach($exploded_array as $ntp_server_ip) {
					$return['configuration']['ntp_servers'][] = $ntp_server_ip;
				}
			}
			
			//domain_name_servers
			if (isset($dhcp_dns_servers_raw['1'])) {
			
				$exploded_array =	explode(',', $dhcp_dns_servers_raw['1']);
			
				foreach($exploded_array as $dns_server_ip) {
					$return['configuration']['domain_name_servers'][] = $dns_server_ip;
				}
			}
	
			//networks
			//the subnet
			if (isset($dhcp_shared_network_subnets_raw['1'])) {
				foreach($dhcp_shared_network_subnets_raw['1'] as $network_index => $dhcp_shared_network_subnet) {
					
					$return['networks'][$dhcp_shared_network_subnets_raw['3'][$network_index]]['subnet'] = $dhcp_shared_network_subnet;
					$return['networks'][$dhcp_shared_network_subnets_raw['3'][$network_index]]['cidr_mask'] = $ip_converter->convert_dotted_subnet_to_cidr($dhcp_shared_network_subnets_raw['2'][$network_index]);
	
				}
			}
			
			//the router
			if (isset($dhcp_shared_network_routers_raw['1'])) {
				foreach($dhcp_shared_network_routers_raw['1'] as $network_index => $dhcp_shared_network_router_ip) {
			
					$return['networks'][$dhcp_shared_network_routers_raw['2'][$network_index]]['router'] = $dhcp_shared_network_router_ip;
			
				}
			}
			
			//ranges
			$i=0;
			if (isset($dhcp_shared_network_ranges_raw['1'])) {
				foreach($dhcp_shared_network_ranges_raw['1'] as $network_index => $dhcp_shared_network_range_start) {
						
					$return['networks'][$dhcp_shared_network_ranges_raw['3'][$network_index]]['ranges'][$i]['start']	= $dhcp_shared_network_range_start;
					$return['networks'][$dhcp_shared_network_ranges_raw['3'][$network_index]]['ranges'][$i]['end'] 	= $dhcp_shared_network_ranges_raw['2'][$network_index];
					$i++;
				}
			}
			
			//hosts
			//the name
			if (isset($dhcp_static_host_name_raw['1'])) {
				foreach($dhcp_static_host_name_raw['1'] as $host_index => $dhcp_static_host_name) {
						
					$return['hosts'][$dhcp_static_host_name_raw['2'][$host_index]]['name'] = $dhcp_static_host_name;
				}
			}
			
			//the mac address
			if (isset($dhcp_static_host_mac_raw['1'])) {
				foreach($dhcp_static_host_mac_raw['1'] as $host_index => $dhcp_static_host_mac) {
	
					$return['hosts'][$dhcp_static_host_mac_raw['2'][$host_index]]['mac_address'] = strtoupper(str_replace(':', '', $dhcp_static_host_mac));
				}
			}
	
			//the ip address
			if (isset($dhcp_static_host_ip_raw['1'])) {
				foreach($dhcp_static_host_ip_raw['1'] as $host_index => $dhcp_static_host_ip) {
			
					$return['hosts'][$dhcp_static_host_ip_raw['2'][$host_index]]['ip_address'] = $dhcp_static_host_ip;
				}
			}
	
			//reset the index in the arrays
			$return['networks'] = array_values($return['networks']);
			$return['hosts'] = array_values($return['hosts']);
	
			//there must be a network if the config is valid
			if (isset($return['networks']['0'])) {
				$this->_dhcp_configuration = $return;
			}
		} else {
			//nothing file does not exist there is no config
		}
	}
	
	public function get_dhcp_configuration($refresh=true)
	{
		if ($this->_dhcp_configuration == null) {
			$this->execute();
		} elseif($refresh == false) {
			//nothing dont refresh
		} else {
			//default is to refresh
			$this->execute();
		}
		
		return $this->_dhcp_configuration;
	}
	
	public function host_reservation_active($mac_address, $refresh=true)
	{

	if ($this->_dhcp_configuration == null) {
			$this->execute();
		} elseif($refresh == false) {
			//nothing dont refresh
		} else {
			//default is to refresh
			$this->execute();
		}

		if ($this->_dhcp_configuration != null) {

			foreach($this->_dhcp_configuration as $dhcp_server) {
				
				foreach($dhcp_server['hosts'] as $host) {
					
					if ($host['mac_address'] == $mac_address) {
						
						return $host['ip_address'];
					}
				}
			}
		}

		//if there are no matches above
		return false;
	}
	

}