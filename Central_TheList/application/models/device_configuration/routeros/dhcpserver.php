<?php

//exception codes 18100-18199

class thelist_routeros_config_dhcpserver implements Thelist_Commander_pattern_interface_ideviceconfiguration
{
	private $_equipment;
	private $_equipment_application;
	
	public function __construct($equipment, $equipment_application)
	{
		$this->_equipment 						= $equipment;
		$this->_equipment_application			= $equipment_application;
	}

	public function generate_config_array()
	{
		//dhcpserver configs are much the same because they are driven by the application configuration system
		//a class has been setup to drive all common config array generation
		//if there are specific attributes for this type of device that will need to be brought in they should be added to the common
		//result here
		$config = new Thelist_Multipledevice_config_dhcpserver($this->_equipment, $this->_equipment_application);
		return $config->generate_config_array();

	}
	
	public function generate_config_device_syntax($config_array)
	{
		$time_obj = new Thelist_Utility_time();
	
		//set the variable for the return
		$return_conf = "";
	
		//for mikrotik we must define the ip address pool before the server and network

		//get the dns servers
		if (isset($config_array['configuration']['domain_name_servers'])) {
			foreach ($config_array['configuration']['domain_name_servers'] as $dns_recursive_server) {
					
				if (!isset($dns_servers)) {
					$dns_servers = $dns_recursive_server;
				} else {
					$dns_servers .= "," . $dns_recursive_server;
				}
			}
		} else {
			//we need dns so if none are specified then we use our default
			$dns_servers = Thelist_Utility_staticvariables::get_recursive_dns_server1() . "," . Thelist_Utility_staticvariables::get_recursive_dns_server2();
		}
			
		//get the ntp servers
		if (isset($config_array['configuration']['ntp_servers'])) {
			foreach ($config_array['configuration']['ntp_servers'] as $ntp_server) {

				if (!isset($ntp_servers)) {
					$ntp_servers = $ntp_server;
				} else {
					$ntp_servers .= "," . $ntp_server;
				}
			}
		} else {
			//we do not require ntp, so if none are specified then we leave this blank
			$ntp_servers = '';
		}
			
		//since a domain is not mandetory we need to ensure that the variable at least exists if not part of the config array
		if (isset($config_array['configuration']['domain_name'])) {
			$dhcp_net_domain = $config_array['configuration']['domain_name'];
		} else {
			$dhcp_net_domain = '';
		}

		//networks
		foreach($config_array['networks'] as $network) {

			//define the network syntax, we cannot add them to the config just yet, they must be defined after the server is setup
			if (!isset($dhcp_networks)) {
				$dhcp_networks = "add address=\"".$network['subnet']."/".$network['cidr_mask']."\" dns-server=\"".$dns_servers."\" domain=\"".$dhcp_net_domain."\" gateway=\"".$network['router']."\" ntp-server=\"".$ntp_servers."\"";
			} else {
				$dhcp_networks .= "\nadd address=\"".$network['subnet']."/".$network['cidr_mask']."\" dns-server=\"".$dns_servers."\" domain=\"".$dhcp_net_domain."\" gateway=\"".$network['router']."\" ntp-server=\"".$ntp_servers."\"";
			}

			//create the ranges if there are any
			if (isset($network['ranges'])) {
					
				//if this is the first network with a range
				if (!isset($main_pool_name)) {
					$main_pool_name = "pool_".$network['subnet']."_".$network['cidr_mask']."";
				} else {
					//if there are multiple networks covered in the server, we choose a different name
					$main_pool_name = "pool_".$network['subnet']."_".$network['cidr_mask']."_and_more";
				}

				$i=0;
				foreach ($network['ranges'] as $range) {
					$i++;

					if ($i == 1) {
						$range_var = $range['start'] . "-" . $range['end'];
					} else {
						$range_var .= "," . $range['start'] . "-" . $range['end'];
					}
				}
			}
		}
			
		//host configs
		foreach($config_array['hosts'] as $host) {
				
			$mac_obj = new Thelist_Deviceinformation_macaddressinformation($host['mac_address']);
				
			if (!isset($dhcp_lease_reservations)) {
				$dhcp_lease_reservations = "add address=\"".$host['ip_address']."\" disabled=\"no\" mac-address=\"".$mac_obj->get_formatted_macaddress(':')."\" server=\"".$config_array['configuration']['interface_name']."_dhcp_server\" use-src-mac=yes";
			} else {
				$dhcp_lease_reservations .= "\nadd address=\"".$host['ip_address']."\" disabled=\"no\" mac-address=\"".$mac_obj->get_formatted_macaddress(':')."\" server=\"".$config_array['configuration']['interface_name']."_dhcp_server\" use-src-mac=yes";
			}
		}
		
	
		//if there are no ranges in any of the networks then the asssignment must be all static
		if (!isset($main_pool_name)) {
			$main_pool_name = 'static-only';
		} else {
			//if there where ranges then we create the syntax for adding a new pool to the device
			$return_conf .= "/ip pool\n";
			$return_conf .= "add name=\"".$main_pool_name."\" ranges=\"".$range_var."\" next-pool=\"none\"";
		}
			
		//add the server to the config
		$return_conf .= "\n\n/ip dhcp-server\n";
	
		//i have chosen too always add and never set when doing config of mikrotik
		$return_conf .= "add ";

		//service status
		if (isset($config_array['configuration']['administrative_status'])) {

			if ($config_array['configuration']['administrative_status'] == 1) {
				$return_conf .= "disabled=\"no\"";
			} else {
				$return_conf .= "disabled=\"yes\"";
			}
		}
			
		//authoritative_status
		if (isset($config_array['configuration']['authoritative_status'])) {
			
			if ($config_array['configuration']['authoritative_status'] == 'after delay') {
				$return_conf .= " authoritative=\"after-2sec-delay\"";
			} else {
				throw new exception("routeros dhcp server authoritative_status value '".$config_array['configuration']['authoritative_status']."' is unknown ", 18100);
			} 
		}
			
		//set the adress pool name from above
		$return_conf .= " address-pool=\"".$main_pool_name."\"";

		//boot p support type
		if (isset($config_array['configuration']['boot_p_support_type'])) {
			
			if ($config_array['configuration']['boot_p_support_type'] == 'none') {
				$return_conf .= " bootp-support=\"static\"";
			} else {
				throw new exception("routeros dhcp server boot p support value '".$config_array['configuration']['boot_p_support_type']."' is unknown ", 18101);
			}
		}

		//interface name
		if (isset($config_array['configuration']['interface_name'])) {
			$return_conf .= " interface=\"".$config_array['configuration']['interface_name']."\"";
		}
			
		//max_lease_time
		if (isset($config_array['configuration']['max_lease_time'])) {
			$return_conf .= " lease-time=\"".$time_obj->convert_seconds_to_mikrotik_time_format($config_array['configuration']['max_lease_time'])."\"";
		}
			
		//dhcp server name, we are using the interface name for this as well, since it must be unique (only one server per interface)
		if (isset($config_array['configuration']['interface_name'])) {
			$return_conf .= " name=\"".$config_array['configuration']['interface_name']."_dhcp_server\"";
		}
		
	
		//add the networks to the config
		$return_conf .= "\n\n/ip dhcp-server network\n";
		$return_conf .= $dhcp_networks;
	
		//add the lease reservations to the config if there are any
		if (isset($dhcp_lease_reservations)) {
			$return_conf .= "\n\n/ip dhcp-server lease\n";
			$return_conf .= $dhcp_lease_reservations;
		}

		return $return_conf;
	}
}