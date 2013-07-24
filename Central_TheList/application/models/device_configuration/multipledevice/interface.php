<?php

//exception codes 9600-9699

class thelist_multipledevice_config_interface implements Thelist_Commander_pattern_interface_ideviceconfiguration
{
	private $_equipment;
	private $_interface;
	
	public function __construct($equipment, $interface)
	{
		
		//object only
		$this->_equipment 						= $equipment;
		$this->_interface						= $interface;
	}

	public function generate_config_array()
	{
		
		//create the return variable
		$return['configuration'] 	= array();
		

		//configurations
		$interface_configs 			= $this->_interface->get_interface_configurations();

		if ($interface_configs != null) {
	
			foreach($interface_configs as $interface_config) {
					
				//interface admin status
				if ($interface_config->get_if_conf_id() == 8) {
					$return['configuration']['administrative_status'] = $interface_config->get_mapped_configuration_value_1();
				}
				
				//interface duplex
				if ($interface_config->get_if_conf_id() == 12) {
					$return['configuration']['duplex'] = $interface_config->get_mapped_configuration_value_1();
				}
				
				//interface l3 mtu
				if ($interface_config->get_if_conf_id() == 1) {
					$return['configuration']['l3_mtu'] = $interface_config->get_mapped_configuration_value_1();
				}
				
				//interface l2 mtu
				if ($interface_config->get_if_conf_id() == 30) {
					$return['configuration']['l2_mtu'] = $interface_config->get_mapped_configuration_value_1();
				}
				
				//interface speed
				if ($interface_config->get_if_conf_id() == 11) {
					$return['configuration']['speed'][] = $interface_config->get_mapped_configuration_value_1();
				}
				
				//interface SSID
				if ($interface_config->get_if_conf_id() == 2) {
					$return['configuration']['ssid'] = $interface_config->get_mapped_configuration_value_1();
				}
				
				//interface Wireless Mode or switch port mode trunk or access
				if ($interface_config->get_if_conf_id() == 3) {
					$return['configuration']['interface_mode'] = $interface_config->get_mapped_configuration_value_1();
				}
				
				//interface wireless band
				if ($interface_config->get_if_conf_id() == 4) {
					$return['configuration']['wireless_protocols'][] = $interface_config->get_mapped_configuration_value_1();
				}
				
				//interface wireless tx frequency
				if ($interface_config->get_if_conf_id() == 6) {
					$return['configuration']['wireless_tx_frequency'] = $interface_config->get_mapped_configuration_value_1();
				}
				
				//interface wireless rx frequency
				if ($interface_config->get_if_conf_id() == 28) {
					$return['configuration']['wireless_rx_frequency'] = $interface_config->get_mapped_configuration_value_1();
				}
				
				//interface wireless tx channel width
				if ($interface_config->get_if_conf_id() == 7) {
					$return['configuration']['wireless_tx_channel_width'][] = $interface_config->get_mapped_configuration_value_1();
				}
				
				//interface wireless rx channel width
				if ($interface_config->get_if_conf_id() == 29) {
					$return['configuration']['wireless_rx_channel_width'][] = $interface_config->get_mapped_configuration_value_1();
				}
				
				//interface wireless authentication type
				if ($interface_config->get_if_conf_id() == 20) {
					$return['configuration']['wireless_security_profile']['authentication_types'][] = $interface_config->get_mapped_configuration_value_1();
				}
				
				//interface wireless Encryption Unicast Ciphers
				if ($interface_config->get_if_conf_id() == 18) {
					$return['configuration']['wireless_security_profile']['unicast_ciphers'][] = $interface_config->get_mapped_configuration_value_1();
				}
				
				//interface wireless Encryption Group Ciphers
				if ($interface_config->get_if_conf_id() == 19) {
					$return['configuration']['wireless_security_profile']['group_ciphers'][] = $interface_config->get_mapped_configuration_value_1();
				}
				
				//interface wireless WPA Shared Encryption Key
				if ($interface_config->get_if_conf_id() == 21) {
					//WPA is index 0
					$return['configuration']['wireless_security_profile']['encryption_keys']['0']['key'] 		= $interface_config->get_mapped_configuration_value_1();
					$return['configuration']['wireless_security_profile']['encryption_keys']['0']['auth_type']	= 'wpa-psk';
				}
				
				//interface wireless WPA Shared Encryption Key
				if ($interface_config->get_if_conf_id() == 34) {
					//WPA2 is index 1
					$return['configuration']['wireless_security_profile']['encryption_keys']['1']['key'] 		= $interface_config->get_mapped_configuration_value_1();
					$return['configuration']['wireless_security_profile']['encryption_keys']['1']['auth_type'] 	= 'wpa2-psk';
				}
				
				//interface vlan id
				if ($interface_config->get_if_conf_id() == 22) {
					$return['configuration']['interface_vlan_id'] = $interface_config->get_mapped_configuration_value_1();
				}

				//switch transit vlan
				if ($interface_config->get_if_conf_id() == 24) {
					$return['configuration']['switch_allowed_transit_vlans'][] = $interface_config->get_mapped_configuration_value_1();
				}
				
				//switch port native vlan
				if ($interface_config->get_if_conf_id() == 25) {
					$return['configuration']['switch_port_native_vlan'] = $interface_config->get_mapped_configuration_value_1();
				}
				
				//switch port allow vlan id to trunk
				if ($interface_config->get_if_conf_id() == 26) {
					$return['configuration']['switch_port_vlans_allowed_trunking'][] = $interface_config->get_mapped_configuration_value_1();
				}
				
				//switch port allow vlan id to trunk
				if ($interface_config->get_if_conf_id() == 27) {
					$return['configuration']['switch_port_vlans_deny_trunking'][] = $interface_config->get_mapped_configuration_value_1();
				}
				
				//switch port encapsulation
				if ($interface_config->get_if_conf_id() == 31) {
					$return['configuration']['switch_port_encapsulation'] = $interface_config->get_mapped_configuration_value_1();
				}
				
				//interface description
				if ($interface_config->get_if_conf_id() == 32) {
					$return['configuration']['interface_description'] = $interface_config->get_mapped_configuration_value_1();
				}
				
				//interface boot protocol
				if ($interface_config->get_if_conf_id() == 33) {
					$return['configuration']['interface_boot_protocol'] = $interface_config->get_mapped_configuration_value_1();
				}
			}
		}
		
		//ip addresses
		if ($this->_interface->get_ip_addresses() != null) {
				
			foreach($this->_interface->get_ip_addresses() as $ip_address) {
		
				//we only care about connected ips
				if ($ip_address->get_ip_address_map_type() == 88) {
					//we need to convert the ips to ip entries from the ip address objects, so they fit with the config from the device
					$return['configuration']['interface_ip_addresses'][] = new Thelist_Deviceinformation_ipaddressentry($ip_address->get_ip_address(), $ip_address->get_ip_subnet_cidr_mask(), $this->_interface->get_if_name());
				}
			}
		}

		if (isset($return)) {
			return $return;
		} else {
			return false;
		}
	}
	
	public function generate_config_device_syntax($config_array)
	{
		throw new exception('this is a general multi device function, i cannot generate specific syntax', 9600);
	}
}