<?php

//exception codes 12900-12999

class thelist_bairos_config_dhcpserver implements Thelist_Commander_pattern_interface_ideviceconfiguration
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
		//because we are using echo and the encapsulation is using ", we must escape in both php and on the shell.
		//make sure to \\\" everytime you want to include a single " in the config file.
		$time_obj = new Thelist_Utility_time();
		
		//set the variable for the return
		$return_conf = "\n### DHCP Config Generated: " . $time_obj->get_current_date_time_as_am_pm() . "\n\n";

		//create the shared subnet
		if (isset($config_array['configuration']['interface_name'])) {
			$return_conf .= "shared-network net_".str_replace('.', '_', $config_array['configuration']['interface_name'])." {\n";
		} else {
			throw new exception("cannot generate dhcp config without an interface name", 12900);
		}

		//domain name
		if (isset($config_array['configuration']['domain_name'])) {
			$return_conf .= "option domain-name \\\"".$config_array['configuration']['domain_name']."\\\";\n";
		}
			
		//default_lease_time
		if (isset($config_array['configuration']['default_lease_time'])) {
			$return_conf .= "default-lease-time ".$config_array['configuration']['default_lease_time'].";\n";
		}
			
		//max_lease_time
		if (isset($config_array['configuration']['max_lease_time'])) {
			$return_conf .= "max-lease-time ".$config_array['configuration']['max_lease_time'].";\n";
		}
			
		//authoritative_status
		if (isset($config_array['configuration']['authoritative_status'])) {
			$return_conf .= "".$config_array['configuration']['authoritative_status'].";\n";
		}
	
		//logging facilities
		if (isset($config_array['configuration']['logging_facility'])) {
			$return_conf .= "log-facility ".$config_array['configuration']['logging_facility'].";\n";
		}
			
		//ntp servers
		if (isset($config_array['configuration']['ntp_servers'])) {
				
			$count_ntp_servers = count($config_array['configuration']['ntp_servers']);
			$return_conf .= "option ntp-servers ";
				
			$i=0;
			foreach($config_array['configuration']['ntp_servers'] as $ntp_server) {
				$i++;
				if ($count_ntp_servers == $i){
					$return_conf .= "".$ntp_server.";\n";
				} else {
					$return_conf .= "".$ntp_server.",";
				}
			}
		}
			
		//domain_name_servers
		if (isset($config_array['configuration']['domain_name_servers'])) {
				
			$count_dns_servers = count($config_array['configuration']['domain_name_servers']);
			$return_conf .= "option domain-name-servers ";
				
			$i=0;
			foreach($config_array['configuration']['domain_name_servers'] as $dns_server) {
				$i++;
				if ($count_dns_servers == $i){
					$return_conf .= "".$dns_server.";\n";
				} else {
					$return_conf .= "".$dns_server.",";
				}
			}
		}
			
		//make some space
		$return_conf .= "\n";

		//network configs
		$ip_converter 	= new Thelist_Utility_ipconverter();

		if (count($config_array['networks']) > 0) {
			//we need to add text after each network statement so we can pull it apart again.
			$net_id=0;
			foreach($config_array['networks'] as $network) {
				$net_id++;
	
				$return_conf .= "subnet ".$network['subnet']." netmask ".$ip_converter->convert_cidr_subnet_to_dotted($network['cidr_mask'])." {   ###NET".$net_id."\n";
		
				//if there are ranges
				if (isset($network['ranges'])) {
					foreach($network['ranges'] as $range) {
						$return_conf .= "range ".$range['start']." ".$range['end'].";   ###NET".$net_id."\n";
					}
				}
				$return_conf .= "option routers ".$network['router'].";   ###NET".$net_id."\n";
				$return_conf .= "}\n";
			}
			
			$return_conf .= "}\n\n";
		} else {
			throw new exception("We are generating a dhcp server config for an interface with name '".$config_array['configuration']['interface_name']."', this config does not have any networks, most likely because the interface has no ips assigned", 12901);
		}

			
		//host configs
			
		//we need to add text after each host statement so we can pull it apart again.
		if (count($config_array['hosts']) > 0) {
			$host_id=0;
			foreach($config_array['hosts'] as $host) {
				$host_id++;
		
				$mac_obj = new Thelist_Deviceinformation_macaddressinformation($host['mac_address']);
		
				$return_conf .= "host ".$host['name']." {   ###HOST".$host_id."\n";
				$return_conf .= "hardware ethernet ".$mac_obj->get_formatted_macaddress(':').";   ###HOST".$host_id."\n";
				$return_conf .= "fixed-address ".$host['ip_address'].";}   ###HOST".$host_id."\n";
		
				//make some space
				$return_conf .= "\n\n";
			}
		}

		if (isset($return_conf)) {
			return $return_conf;
		} else {
			return false;
		}
	}
}