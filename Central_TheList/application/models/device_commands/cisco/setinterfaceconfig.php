<?php

//exception codes 5400-5499

class thelist_cisco_command_setinterfaceconfig implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_options;

	public function __construct($device, $options)
	{
		//$options
		//object	= interface_obj
		//string	= ['interface_name']
		//string	= ['new_config']
		
		$this->_device 					= $device;
		$this->_options 				= $options;
	}
	
	public function execute()
	{
		if (is_object($this->_options)) {
			
			$interface				= $this->_options;
			
			$config_generator		= new Thelist_Cisco_config_interface(new Thelist_model_equipments($this->_options->get_eq_id()), $interface);
			$new_config_array		= $config_generator->generate_config_array();
			
			//now we need to get the current running config on the device interface
			$device_config			= new Thelist_Cisco_command_getinterfaceconfig($this->_device, $interface);
			$device_config_array	= $device_config->execute();
			
		} elseif (is_array($this->_options)) {

			$interface				= $this->_options['interface_name'];
			
			$config_generator 				= new Thelist_Cisco_config_interface(null, null);
			$new_config_array				= $this->_options['new_config'];
			
			$get_config_options['interface_name']	= $this->_options['interface_name'];
			
			if (preg_match("/FastEthernet|GigabitEthernet/", $this->_options['interface_name'])) {
				$get_config_options['interface_type']	= null;
			} else {
				$get_config_options['interface_type']	= 'SVI';
			}
			
			//now we need to get the current running config on the device interface
			$device_config			= new Thelist_Cisco_command_getinterfaceconfig($this->_device, $get_config_options);
			$device_config_array	= $device_config->execute();
		}
		
		$diff			= new Thelist_Multipledevice_config_configdifferences($new_config_array, $device_config_array);
		$config_diffs	= $diff->generate_config_array();
		
		if ($config_diffs != false) {
		
			//first we remove, then we add, sometimes commands depend on each other
			//i.e. cant remove admin status or speed/duple, but in that case there should be a corrosponding
			//new value in the add section 
			
			if (isset($config_diffs['remove_configuration'])) {
			
				//interface speed
				if (isset($config_diffs['remove_configuration']['speed'])) {
					
					//because the speed is changing there should be a speed in the 
					//add config array
					if (isset($config_diffs['configuration']['speed'])) {
						//cisco can only have one speed on the interface
						//so no need to go through array
						$set_interface_speed	= new Thelist_Cisco_command_setinterfacespeed($this->_device, $interface, $config_diffs['configuration']['speed']['0']);
						$set_interface_speed->execute();
					} else {

						throw new exception('old config had a change to the interface speed but no new speed was given in the new config', 5400);
					}
				}
				
				//interface admin status
				if (isset($config_diffs['remove_configuration']['administrative_status'])) {
						
					//because the administrative_status is changing there should be a administrative_status in the
					//add config array
					if (isset($config_diffs['configuration']['administrative_status'])) {

						$set_interface_admin_status	= new Thelist_Cisco_command_setinterfaceadminstatus($this->_device, $interface, $config_diffs['configuration']['administrative_status']);
						$set_interface_admin_status->execute();

					} else {
						throw new exception('old config had a change to the administrative status but no new administrative status was given in the new config', 5401);
					}
				}
				
				//interface duplex
				if (isset($config_diffs['remove_configuration']['duplex'])) {
				
					//because the duplex is changing there should be a duplex in the, duplex cannot be removed
					//add config array
					if (isset($config_diffs['configuration']['duplex'])) {
				
						$set_interface_duplex	= new Thelist_Cisco_command_setinterfaceduplex($this->_device, $interface, $config_diffs['configuration']['duplex']);
						$set_interface_duplex->execute();
				
					} else {
						throw new exception('old config had a change to the duplex status but no new duplex was given in the new config', 5402);
					}
				}
				
				//interface mode trunk, access or dynamic
				if (isset($config_diffs['remove_configuration']['interface_mode'])) {
				
					//because the mode is changing there should be a mode in the config, mode cannot be removed even if its not showing on the 3500 series 
					//it is still access
					//add config array
					if (isset($config_diffs['configuration']['interface_mode'])) {
						
						//we have the new mode, but if we are going from access to trunk we need to set the encapsulation first
						if ($config_diffs['remove_configuration']['interface_mode'] == 'access' && $config_diffs['configuration']['interface_mode'] == 'trunk') {
							
							//if the encapsulation is set we execute that before the mode is changed
							if (isset($config_diffs['configuration']['switch_port_encapsulation'])) {
								$set_interface_switch_port_encapsulation	= new Thelist_Cisco_command_setinterfaceswitchportencapsulation($this->_device, $interface, $config_diffs['configuration']['switch_port_encapsulation']);
								$set_interface_switch_port_encapsulation->execute();
							}
						}

						$set_interface_mode	= new Thelist_Cisco_command_setinterfaceswitchportmode($this->_device, $interface, $config_diffs['configuration']['interface_mode']);
						$set_interface_mode->execute();
				
					} else {
						throw new exception('old config had a change to the mode but no new mode was given in the new config', 5403);
					}
				}
				
				//remove the native vlan
				if (isset($config_diffs['remove_configuration']['switch_port_native_vlan'])) {
					$remove_interface_native_vlan	= new Thelist_Cisco_command_removeinterfacenativevlan($this->_device, $interface);
					$remove_interface_native_vlan->execute();
				}
				
				//remove the trunk encapsulation make sure this is done AFTER the switchport mode is changed
				//you cannot remove encapsulation if the port is set to trunk
				if (isset($config_diffs['remove_configuration']['switch_port_encapsulation'])) {
					$remove_interface_switch_port_encapsulation	= new Thelist_Cisco_command_removeinterfaceswitchportencapsulation($this->_device, $interface);
					$remove_interface_switch_port_encapsulation->execute();
				}
				
				//remove vlans to the allowed to trunk list
				if (isset($config_diffs['remove_configuration']['switch_port_vlans_allowed_trunking'])) {
					$remove_interface_allowed_vlans	= new Thelist_Cisco_command_removeinterfaceswitchportallowedvlans($this->_device, $interface, $config_diffs['remove_configuration']['switch_port_vlans_allowed_trunking']);
					$remove_interface_allowed_vlans->execute();
				}
				
				//remove description
				if (isset($config_diffs['remove_configuration']['interface_description'])) {
					$remove_interface_description	= new Thelist_Cisco_command_removeinterfacedescription($this->_device, $interface);
					$remove_interface_description->execute();
				}
				
				//remove transit vlans
				if (isset($config_diffs['remove_configuration']['switch_allowed_transit_vlans'])) {
					$remove_switch_transit_vlans	= new Thelist_Cisco_command_removetransitvlans($this->_device, $config_diffs['remove_configuration']['switch_allowed_transit_vlans']);
					$remove_switch_transit_vlans->execute();
				}
			}

			//ADD CONFIGS
			//set new added configs
			if (isset($config_diffs['configuration'])) {
				
				//set the native vlan
				if (isset($config_diffs['configuration']['switch_port_native_vlan'])) {
						
					$set_interface_native_vlan	= new Thelist_Cisco_command_setinterfacenativevlan($this->_device, $interface, $config_diffs['configuration']['switch_port_native_vlan']);
					$set_interface_native_vlan->execute();
				}
				
				//set the encapsulation
				if (isset($config_diffs['configuration']['switch_port_encapsulation'])) {
					$set_interface_switch_port_encapsulation	= new Thelist_Cisco_command_setinterfaceswitchportencapsulation($this->_device, $interface, $config_diffs['configuration']['switch_port_encapsulation']);
					$set_interface_switch_port_encapsulation->execute();
				}

				//add vlans to the allowed trunk list
				if (isset($config_diffs['configuration']['switch_port_vlans_allowed_trunking'])) {
					$set_interface_allowed_vlans	= new Thelist_Cisco_command_addinterfaceswitchportallowedvlans($this->_device, $interface, $config_diffs['configuration']['switch_port_vlans_allowed_trunking']);
					$set_interface_allowed_vlans->execute();
				}
				
				//add interface description
				if (isset($config_diffs['configuration']['interface_description'])) {
					$set_interface_description	= new Thelist_Cisco_command_setinterfacedescription($this->_device, $interface, $config_diffs['configuration']['interface_description']);
					$set_interface_description->execute();
				}
				
				//add transit vlans
				if (isset($config_diffs['configuration']['switch_allowed_transit_vlans'])) {
					$add_switch_transit_vlans	= new Thelist_Cisco_command_addtransitvlans($this->_device, $config_diffs['configuration']['switch_allowed_transit_vlans']);
					$add_switch_transit_vlans->execute();
				}
			}
		}
	}
}