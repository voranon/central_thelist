<?php

//exception codes 12900-12999

class thelist_bairos_config_interface implements Thelist_Commander_pattern_interface_ideviceconfiguration
{
	private $_equipment;
	private $_interface;
	
	public function __construct($equipment, $interface)
	{
		//$equipment only used to generate new config from database, instanciate with null in string based model
		
		//$interface
		//object	= interface_obj
		//string	= ['interface_name']
		//string	= ['interface_mac_address'] (no delimiter all caps)
		
		$this->_equipment 						= $equipment;
		$this->_interface						= $interface;
	}

	public function generate_config_array()
	{
		//interface configs are much the same because they are driven by the interface configuration system
		//a class has been setup to drive all common config array generation
		//if there are specific attributes for this type of device that will need to be brought in they should be added to the common
		//result her
		//there is no purpose running this method in a string based model, only objects can generate a config
		$config = new Thelist_Multipledevice_config_interface($this->_equipment, $this->_interface);
		return $config->generate_config_array();
	}
	
	public function generate_config_device_syntax($config_array)
	{
		if (is_object($this->_interface)) {
			$interface_name		= $this->_interface->get_if_name();
			$mac_address		= $this->_interface->get_if_mac_address();
		} else {
			$interface_name		= $this->_interface['interface_name'];
			$mac_address		= $this->_interface['interface_mac_address'];
		}
		
		$mac_obj	= new Thelist_Deviceinformation_macaddressinformation($mac_address);
		$interface_mac_address	= $mac_obj->get_formatted_macaddress(':');
		
		
		//bairos configs are always complete overrides so only a config section is needed

		if (isset($config_array['configuration'])) {
		
			//either description or device name are on top so these are the only 2 we make 
			//set the return variable, if its not already set, it is not needed for any other items because 
			//either of these will ALWAYS be on to and device is mandetory
			
			//interface description on top
			if (isset($config_array['configuration']['interface_description'])) {
				//since description is the first it will always set the return
				$return_conf = "# ".$config_array['configuration']['interface_description']."";
			}

			//interface device name / interface name
			if ($interface_name != null) {
				
				if (isset($return_conf)) {
					$return_conf .= "\nDEVICE=".$interface_name."";
				} else {
					$return_conf = "DEVICE=".$interface_name."";
				}
				
				//mac address is also mandetory
				$return_conf .= "\nHWADDR=".$interface_mac_address."";

			} else {
				throw new exception("device interface config for bairos requires an interface name", 12900);
			}

			//interface boot protocol
			if (isset($config_array['configuration']['interface_boot_protocol'])) {
				
				if ($config_array['configuration']['interface_boot_protocol'] == 'dhcp client') {
					$return_conf .= "\nBOOTPROTO=dhcp";
				} elseif ($config_array['configuration']['interface_boot_protocol'] == 'none') {
					$return_conf .= "\nBOOTPROTO=none";
				} else {
					throw new exception("config contains unknown boot protocol: '".$config_array['configuration']['interface_boot_protocol']."' ", 12905);
				}
			} 
			
			//interface admin status
			if (isset($config_array['configuration']['administrative_status'])) {
				
				if ($config_array['configuration']['administrative_status'] == 0) {
					$return_conf .= "\nONBOOT=no";
				} else {
					$return_conf .= "\nONBOOT=yes";
				}
			}
			
			//interface ip address, each interface config can only hold a single ip address, the set 
			if (isset($config_array['configuration']['interface_ip_addresses'])) {
				$return_conf .= "\nIPADDR=".$config_array['configuration']['interface_ip_addresses']['0']->get_ip_address()."";
				$return_conf .= "\nNETMASK=".$config_array['configuration']['interface_ip_addresses']['0']->get_dotted_ip_subnet_mask()."";
			}

			//interface vlan
			if (isset($config_array['configuration']['interface_vlan_id'])) {
				$return_conf .= "\nVLAN=yes";
			}
			
			$return_conf .= "\nTYPE=Ethernet";
			
			//speed and duplex only one speed can be set for ethernet interfaces therefore the ['0'] on speed
			if (isset($config_array['configuration']['speed'])) {
			
				if (isset($config_array['configuration']['duplex'])) {
			
					if ($config_array['configuration']['speed']['0'] == 'auto' && $config_array['configuration']['duplex'] == 'auto') {
						//do nothing, no config means the interface will auto negotiate
					} elseif ($config_array['configuration']['speed']['0'] != 'auto' && $config_array['configuration']['duplex'] == 'auto') {
						throw new exception("we have a problem with the input validations, speed is set specifially, but duplex is auto", 12903);
					} elseif ($config_array['configuration']['speed']['0'] == 'auto' && $config_array['configuration']['duplex'] != 'auto') {
						throw new exception("we have a problem with the input validations, duplex is set specifially, but speed is auto", 12904);
					} else {
			
						if ($config_array['configuration']['speed']['0'] == 1000) {
							//gigabit speeds for ethernet auto negotiation turned on
							//im not sure why but it could be for flow control
							$return_conf .= "\nETHTOOL_OPTS=\"speed 1000 duplex ".$config_array['configuration']['duplex']." autoneg on\"";
						} else {
							$return_conf .= "\nETHTOOL_OPTS=\"speed ".$config_array['configuration']['speed']['0']." duplex ".$config_array['configuration']['duplex']." autoneg off\"";
						}
					}
						
				} else {
					throw new exception("device interface config for bairos requires speed and duplex in this case duplex is not set, but speed is set", 12901);
				}
			}
				
			//test that duplex is set and if it is that speed is also
			//the validation above will not check for duplex unless speed is set
			if (isset($config_array['configuration']['duplex'])) {
			
				if (!isset($config_array['configuration']['speed'])) {
					throw new exception("device interface config for bairos requires speed and duplex in this case speed is not set, but duplex is set", 12902);
				}
			}
		}
		
		if (isset($return_conf)) {
			return $return_conf;
		} else {
			return false;
		}
	}
}