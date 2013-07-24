<?php

//exception codes 20400-20499

class thelist_routeros_config_interface implements Thelist_Commander_pattern_interface_ideviceconfiguration
{
	private $_equipment;
	private $_interface;
	
	public function __construct($equipment, $interface)
	{
		$this->_equipment 						= $equipment;
		$this->_interface						= $interface;
	}

	public function generate_config_array()
	{
		//interface configs are much the same because they are driven by the interface configuration system
		//a class has been setup to drive all common config array generation
		//if there are specific attributes for this type of device that will need to be brought in they should be added to the common
		//result here
		$config = new Thelist_Multipledevice_config_interface($this->_equipment, $this->_interface);
		
		$return = $config->generate_config_array();
		
		//because vlan interfaces have L2 mtu on the device but no abillity to change it, because it is implied by the mtu of the parent 
		//interface, we add the l2 mtu conig here if we are dealing with a vlan interface. this is the only way we can get i.e. 
		//config differences to return false if the config really is the same. the alternative would be not to return l2 mtu
		//for vlan interfaces, but thats is not a reflection of reallity, they do have l2 mtu.
		if ($this->_interface->get_if_type()->get_if_type_id() == 95 && !isset($return['configuration']['l2_mtu'])) {
			
			//vlans must only have a single master,, and they must have a master
			$master_if = $this->_interface->get_slave_relationships();
			
			$l2_mtu_config	= $master_if['0']->get_interface_configuration(30);
			
			if ($l2_mtu_config != false) {

				//set the vlan l2mtu the same as the parent interface minus the 4 bytes for the vlan tag
				//there can only be a single l2 mtu
				$return['configuration']['l2_mtu'] = $l2_mtu_config['0']->get_mapped_configuration_value_1() - 4;
				
			} else {
				throw new exception("parent interface for interface name: '".$this->_interface->get_if_name()."' on equipment: '".$this->_equipment->get_eq_fqdn()."', does not have a, l2mtu config, that is mandetory for mikrotik interfaces", 20410);
			}
		}

		
		return $return;
	}
	
	public function generate_config_device_syntax($config_array)
	{
		//set the variable for the return
		$return_conf = "";
		throw new exception('stop right there this method is not finished and may never be', 20499);
		
		
		//what type are we dealing with
		if (!isset($config_array['configuration']['interface_name'])) {
			throw new exception("you must specify the interface name you want the device syntax for interface on equipment: ".$this->_equipment->get_eq_fqdn()."", 20401);
		}
		
		//what type are we dealing with
		if (!isset($config_array['configuration']['interface_type'])) {
			throw new exception("you must specify the interface type for ".$config_array['configuration']['interface_name']." on equipment: ".$this->_equipment->get_eq_fqdn()."", 20401);
		}
		
		
		//if this is a wireless interface, then we need to construct the security profile first
		
		//wireless aut type
		if (isset($config_array['configuration']['wireless_authentication_type'])) {
				
			//auth type is mandetory so we let it create the header
			$return_conf .= "/interface wireless security-profiles\n";
			$return_conf .= "name=\"".$this->_interface->get_if_name()."_secure\" mode=\"dynamic-keys\"";
			
			foreach ($config_array['configuration']['wireless_authentication_type'] as $wireless_auth_type) {
				
				if (!isset($auth_types_var)) {
					$auth_types_var = $wireless_auth_type;
				} else {
					$auth_types_var .= "," . $wireless_auth_type;
				}
			}
			
			$return_conf .= " authentication-types=\"".$auth_types_var."\"";
		}
		
		//wireless group ciphers
		if (isset($config_array['configuration']['wireless_group_ciphers'])) {
			
			foreach ($config_array['configuration']['wireless_group_ciphers'] as $wireless_group_cipher) {
		
				if ($wireless_group_cipher == 'aes') {
					$wireless_group_cipher = 'aes-ccm';
				}
				
				if (!isset($group_cipher_var)) {
					$group_cipher_var = $wireless_group_cipher;
				} else {
					$group_cipher_var .= "," . $wireless_group_cipher;
				}
			}
				
			$return_conf .= " group-ciphers=\"".$group_cipher_var."\"";
		}
		
		//wireless unicast ciphers
		if (isset($config_array['configuration']['wireless_unicast_ciphers'])) {
				
			foreach ($config_array['configuration']['wireless_unicast_ciphers'] as $wireless_unicast_cipher) {
		
				if ($wireless_unicast_cipher == 'aes') {
					$wireless_unicast_cipher = 'aes-ccm';
				}
				
				if (!isset($unicast_cipher_var)) {
					$unicast_cipher_var = $wireless_unicast_cipher;
				} else {
					$unicast_cipher_var .= "," . $wireless_unicast_cipher;
				}
			}
		
			$return_conf .= " unicast-ciphers=\"".$unicast_cipher_var."\"";
		}
		
		if (isset($config_array['configuration']['wireless_encryption_key'])) {

			$return_conf .= " wpa-pre-shared-key=\"".$config_array['configuration']['wireless_encryption_key']."\" wpa2-pre-shared-key=\"".$config_array['configuration']['wireless_encryption_key']."\"";
			$return_conf .= "\n\n";
		}

		//findout what type of interface this is so we can place it in the correct
		//path, we could try to imply this by looking at the available configurations
		//but because there are many kinds of wireless interfaces (nstreme-dual, wireless etc.)
		//we do a lookup.

		//the fact that there may be nothing to configure for the interface and we still get the set should be fixed
		//but its not a problem for the device, no errors are generated because of it
// 		if ($this->_interface->get_if_type()->get_if_type() == 'wireless') {
// 			$return_conf .= "/interface wireless";
// 		} elseif ($this->_interface->get_if_type()->get_if_type_id() == 92) {
// 			$return_conf .= "/interface wireless nstreme-dual";
// 		} elseif ($this->_interface->get_if_type()->get_if_type() == 'ethernet') {
// 			$return_conf .= "/interface ethernet";
// 		} elseif ($this->_interface->get_if_type()->get_if_type_id() == 90) {
// 			$return_conf .= "/interface bridge";
// 		} elseif ($this->_interface->get_if_type()->get_if_type_id() == 95) {
// 			$return_conf .= "/interface vlan";
// 		} elseif ($this->_interface->get_if_type()->get_if_type_id() == 91) {
// 			$return_conf .= "/interface vrrp";
// 		}
		
		//if we are working with a hardware interface, then we need to set it, not create it
		if ($this->_interface->get_if_type()->get_if_type() == 'ethernet' || $this->_interface->get_if_type()->get_if_type() == 'wireless') {
			$return_conf .= "\nset [find where name=\"".$this->_interface->get_if_name()."\"]";
		} else {
			$return_conf .= "name=\"".$this->_interface->get_if_name()."\"";
		}
		
		//administrative status
		if (isset($config_array['configuration']['administrative_status'])) {
		
			if ($config_array['configuration']['administrative_status'] == 1) {
				$return_conf .= " disabled=\"no\"";
			} else {
				$return_conf .= " disabled=\"yes\"";
			}
		}
		
		//interface vlan id
		if (isset($config_array['configuration']['interface_vlan_id'])) {
			$return_conf .= " vlan-id=\"".$config_array['configuration']['interface_vlan_id']."\"";
		}
		
		//vlan
		if ($config_array['configuration']['interface_type'] == 'vlan') {
			
			if (isset($config_array['configuration']['parent_interface_name'])) {
				//since a vlan can only have a single parent and must be sitting directly on an interface
				//we can assume that index 0 is the correct master
				$return_conf .= " interface=\"".$config_array['configuration']['parent_interface_name']."\"";
				
			} else {
				throw new exception("you must specify a parent interface type for ".$this->_interface->get_if_name()." on equipment: ".$this->_equipment->get_eq_fqdn().", because it is a val", 20402);
			}
			
			
		}
		
		//bridge
		if ($this->_interface->get_if_type()->get_if_type_id() == 90) {
			//since this is a bridge it could be master for a bunch of interfaces
			$slave_ifs = $this->_interface->get_master_relationships();
		
			if ($slave_ifs != null) {
				$return_conf .= "\n\n/interface bridge port";
				
				foreach ($slave_ifs as $bridge_port) {
					$return_conf .= "interface=\"".$bridge_port->get_if_name()."\" bridge=\"".$this->_interface->get_if_name()."\"";
				}
			}
		}

		//wireless_ssid
		if (isset($config_array['configuration']['ssid'])) {
			$return_conf .= " ssid=\"".$config_array['configuration']['ssid']."\"";
		}
		
		//wireless_mode
		if (isset($config_array['configuration']['wireless_mode'])) {
			
			if ($config_array['configuration']['wireless_mode'] == 'client') {
				$formatted_wireless_mode = 'station';
			} elseif ($config_array['configuration']['wireless_mode'] == 'accesspoint') {
				$formatted_wireless_mode = 'ap-bridge';
			} elseif ($config_array['configuration']['wireless_mode'] == 'nstreme_dual_slave') {
				$formatted_wireless_mode = 'nstreme-dual-slave';
			} elseif ($config_array['configuration']['wireless_mode'] == 'bridge') {
				$formatted_wireless_mode = 'bridge';
			} elseif ($config_array['configuration']['wireless_mode'] == 'client_bridge') {
				$formatted_wireless_mode = 'station-bridge';
			} else {
				throw new exception("getting interface device syntax for ".$this->_interface->get_if_name()." on equipment: ".$this->_equipment->get_eq_fqdn().", unknown interface mode ", 20400);
			}
			
			if (isset($formatted_wireless_mode)) {
				$return_conf .= " mode=\"".$formatted_wireless_mode."\"";
			}
		}
		
		//wireless band
		if (isset($config_array['configuration']['wireless_band'])) {
			
			$five = 	",5175,5180,5185,5190,5195,5200,5205,5210,5215,5220,5225,5230,
						5235,5240,5245,5250,5255,5260,5265,5270,5275,5280,5285,5290,
						5295,5300,5305,5310,5315,5320,5325,5330,5335,5340,5345,5350,
						5355,5360,5365,5370,5375,5380,5385,5390,5395,5400,5405,5410,
						5415,5420,5425,5430,5435,5440,5445,5450,5455,5460,5465,5470,
						5475,5480,5485,5490,5495,5500,5505,5510,5515,5520,5525,5530,
						5535,5540,5545,5550,5555,5560,5565,5570,5575,5580,5585,5590,
						5595,5600,5605,5610,5615,5620,5625,5630,5635,5640,5645,5650,
						5655,5660,5665,5670,5675,5680,5685,5690,5695,5700,5705,5710,
						5715,5720,5725,5730,5735,5740,5745,5750,5755,5760,5765,5770,
						5775,5780,5785,5790,5795,5800,5805,5810,5815,5820,5825,5830,5835,
						";
						
			$two = 	",2412,2417,2422,2427,2432,2437,2442,2447,2452,2457,2462,";

			if ($config_array['configuration']['wireless_band'] == '802.11bgn') {
				$formatted_wireless_band = '2ghz-b/g/n';
			} elseif ($config_array['configuration']['wireless_band'] == '802.11a') {
				$formatted_wireless_band = '5ghz-a';
			} elseif ($config_array['configuration']['wireless_band'] == '802.11an') {
				$formatted_wireless_band = '5ghz-a/n';
			} elseif ($config_array['configuration']['wireless_band'] == '802.11b') {
				$formatted_wireless_band = '2ghz-b';
			} elseif ($config_array['configuration']['wireless_band'] == '802.11bg') {
				$formatted_wireless_band = '2ghz-b/g';
			} elseif ($config_array['configuration']['wireless_band'] == '802.11g') {
				$formatted_wireless_band = '2ghz-onlyg';
			} elseif ($config_array['configuration']['wireless_band'] == '802.11n') {
				
				if (isset($config_array['configuration']['wireless_center_frequency'])) {
					
					if (preg_match("/,".$config_array['configuration']['wireless_center_frequency'].",/", $five)) {
						$formatted_wireless_band = '5ghz-onlyn';
						
					}
					
					if (preg_match("/,".$config_array['configuration']['wireless_center_frequency'].",/", $two)) {
						$formatted_wireless_band = '2ghz-onlyn';
					
					}
				}
			}

			if (isset($formatted_wireless_band)) {
				$return_conf .= " band=\"".$formatted_wireless_band."\"";
			}
		}
		
		//wireless tx frequency, we dont care about the rx for half duplex radios
		if (isset($config_array['configuration']['wireless_tx_frequency'])) {
			$return_conf .= " frequency=\"".$config_array['configuration']['wireless_tx_frequency']."\"";
		}
		
		//wireless tx width, we dont care about the rx for half duplex radios
		if (isset($config_array['configuration']['wireless_tx_channel_width'])) {
			
			//currently this function does not handle ht-above and below only one tx width is expected

			if ($config_array['configuration']['wireless_tx_channel_width']['0'] == '20') {
				$formatted_tx_width = '20mhz';
			} elseif ($config_array['configuration']['wireless_tx_channel_width']['0'] == '10') {
				$formatted_tx_width = '10mhz';
			} elseif ($config_array['configuration']['wireless_tx_channel_width']['0'] == '5') {
				$formatted_tx_width = '5mhz';
			}
				
			if (isset($formatted_tx_width)) {
				$return_conf .= " channel-width=\"".$formatted_tx_width."\"";
			} else {
				
				//if no match is found we default to 20mhz
				$return_conf .= " channel-width=\"20mhz\"";
			}
		}
		
		//if wireless encryption was ordered, we need to include the created profile
		if (isset($config_array['configuration']['wireless_authentication_type'])) {
			$return_conf .= " security-profile=\"".$this->_interface->get_if_name()."_secure\"";
		}
		
		//L3 mtu
		if (isset($config_array['configuration']['l3_mtu'])) {
			$return_conf .= " mtu=\"".$config_array['configuration']['l3_mtu']."\"";
		}
		
		//interface speed
		if ($this->_interface->get_if_type()->get_if_type() == 'wireless') {
			
			if (isset($config_array['configuration']['speed'])) {
				
				//more work needs to be put into wireless speeds
				
				if ($config_array['configuration']['speed']['0'] == 'auto') {
					$return_conf .= " rate-set=\"default\"";
				} else {
					$return_conf .= " rate-set=\"configured\"";
				}
			}
			
		} elseif ($this->_interface->get_if_type()->get_if_type() == 'ethernet') {
			
			if (isset($config_array['configuration']['speed']) && isset($config_array['configuration']['duplex'])) {

				//if duplex and speed are bopth auto then the interface auto negotiates
				if ($config_array['configuration']['speed']['0'] == 'auto' && $config_array['configuration']['duplex'] == 'auto') {
					$return_conf .= " auto-negotiation=\"yes\"";
				} elseif ($config_array['configuration']['speed']['0'] != 'auto' && $config_array['configuration']['duplex'] == 'auto') {
					//if config is not valid, one cannot be set to auto, so we auto negotiate
					$return_conf .= " auto-negotiation=\"yes\"";
				} elseif ($config_array['configuration']['speed']['0'] == 'auto' && $config_array['configuration']['duplex'] != 'auto') {
					//if config is not valid, one cannot be set to auto, so we auto negotiate
					$return_conf .= " auto-negotiation=\"yes\"";
				} elseif ($config_array['configuration']['speed']['0'] != 'auto' && $config_array['configuration']['duplex'] == 'half') {
					$return_conf .= " auto-negotiation=\"no\" full-duplex=\"no\" speed=\"".$config_array['configuration']['speed']['0']."Mbps\"";
				} elseif ($config_array['configuration']['speed']['0'] != 'auto' && $config_array['configuration']['duplex'] == 'full') {
					$return_conf .= " auto-negotiation=\"no\" full-duplex=\"yes\" speed=\"".$config_array['configuration']['speed']['0']."Mbps\"";
				}
			}
		}
		
		return $return_conf;
	}
}