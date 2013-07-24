<?php

//exception codes 20300-20399

class thelist_routeros_command_setinterfaceconfig implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_options;

	public function __construct($device, $options)
	{
		//$options
		//object	= interface_obj
		//string	= ['new_config']
		//string	= ['interface_ros_specific_type'] = "ethernet, wireless, nstreme_dual, bridge, vlan, vrrp" are possible values, only one value should be passed
		//['interface_ros_specific_type'] this value will be ignored if the interface exists
		
		//if type is vlan we need a parent interface name as well
		//string	= ['new_config']['configuration']['interface_name']
		//string	= ['new_config']['configuration']['parent_interface_name']
		
		$this->_device 					= $device;
		$this->_options 				= $options;
	}
	
	public function execute()
	{
		if (is_object($this->_options)) {
			$interface		= $this->_options;
		} elseif (is_array($this->_options)) {
			$interface		= $this->_options['configuration']['interface_name'];
		}

		//we need to know if the interface exists before we move on
		//or if this is a brand new interface, like a new vlan
		$status = new Thelist_Routeros_command_getinterfacestatus($this->_device, $interface);
		$interface_exists = $status->get_interface_exist();
		
		if (is_object($this->_options)) {
			
			$config_generator		= new Thelist_Routeros_config_interface(new Thelist_model_equipments($this->_options->get_eq_id()), $this->_options);
			$new_config_array		= $config_generator->generate_config_array();
			$interface_name			= $this->_options->get_if_name();
			
			//the object model can find the if type even if the interface it is not present on the device 
			$get_if_type	= new Thelist_Routeros_command_getinterfacetype($this->_device, $this->_options);
			$if_type		= $get_if_type->get_routeros_specific_if_type_name();
			
			//now we need to get the current running config on the device interface
			if ($interface_exists === true) {
				
				$device_config			= new Thelist_Routeros_command_getinterfaceconfig($this->_device, $this->_options);
				$device_config_array	= $device_config->get_interface_config();
				
			} else {
				//if there is no config on the device at all then we get a false back
				//but the diff class is expecting an array named configuration
				
				$device_config_array['configuration'] = array();
			}
						
		} elseif (is_array($this->_options)) {

			$config_generator 		= new Thelist_Routeros_config_interface(null, $interface);
			$new_config_array		= $this->_options['new_config'];
			$interface_name			= $interface;
			
			//now we need to get the current running config on the device interface
			if ($interface_exists === true) {
			
				$device_config			= new Thelist_Routeros_config_interface($this->_device, $interface_name);
				$device_config_array	= $device_config->get_interface_config();
				
				$get_if_type	= new Thelist_Routeros_command_getinterfacetype($this->_device, $interface_name);
				$if_type		= $get_if_type->get_routeros_specific_if_type_name();
					
			} else {
				//if there is no config on the device at all then we get a false back
				//but the diff class is expecting an array named configuration
				$if_type = $this->_options['interface_ros_specific_type'];
				
				$device_config_array['configuration'] = array();
			}
		}

		if ($if_type == 'wireless' || $if_type == 'ethernet' || $if_type == 'vlan') {
			//append as we add commands for interface types
			
			if ($if_type == 'vlan') {
				
				if (is_object($this->_options)) {
					//vlans only have a single master interface
					$master_interface = $this->_options->get_slave_relationships();
					
					if (isset($master_interface['0'])) {
						$vlan_parent_interface_name = $master_interface['0']->get_if_name();
					} else {
						throw new exception("vlan with interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' does not have a parent, that is a problem ", 20302);
					}
					
				} else {
					
					if (isset($this->_options['parent_interface_name'])) {
						$vlan_parent_interface_name = $this->_options['parent_interface_name'];
					} else {
						throw new exception("vlan with interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' does not have a parent specified, that is a problem ", 20303);
					}
				}
				
				//if this is a vlan interface we need to make sure the parent already exists
				$parent_status 				= new Thelist_Routeros_command_getinterfacestatus($this->_device, $vlan_parent_interface_name);
				$parent_interface_exists 	= $parent_status->get_interface_exist();
				
				if ($parent_interface_exists === false) {
					throw new exception("vlan with interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' does not have a parent that exists on this device", 20304);
				}
			}
			
		} else {
			throw new exception("we dont know how to set interface for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' ", 20300);
		}

		$diff			= new Thelist_Multipledevice_config_configdifferences($new_config_array, $device_config_array);
		$config_diffs	= $diff->generate_config_array();
		
		echo "\n <pre> 1111  \n ";
		//print_r($new_config_array);
		echo "\n 2222 \n ";
		//print_r($device_config_array);
		echo "\n 3333 \n ";
		print_r($config_diffs);
		echo "\n 4444 </pre> \n ";
		die;
		
		
		if ($config_diffs != false) {
			
			if ($interface_exists === true) {
				
				//general config changes for existing interfaces
				
				//interface admin status
				if (isset($config_diffs['remove_configuration']['administrative_status'])) {
						
					//because the administrative status is changing there should be a new admin status
					if (isset($config_diffs['configuration']['administrative_status'])) {
	
						$config	= new Thelist_Routeros_command_setinterfaceadminstatus($this->_device, $interface, $config_diffs['configuration']['administrative_status']);
						$config->execute();
						
					} else {
						throw new exception("Interface Name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."', there is a change to admin status but no new status", 20305);
					}
				}
				
				//vlan id
				if (isset($config_diffs['remove_configuration']['interface_vlan_id'])) {
				
					//because the administrative status is changing there should be a new admin status
					if (isset($config_diffs['configuration']['interface_vlan_id'])) {
				
						//we cannot change vlan ids, maybe expand this to remove the interface and set it up again, redo ips and i.e. dhcp server
						throw new exception("Interface Name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."', there is a change to vlan id but we are not allowed to enforce that, since a change in vlan id constitudes a new interface in our system, you will have to remove the interface and all its ips and teh nset it up again", 20306);
						
						//$config	= new Thelist_Routeros_command_setinterfacevlanid($this->_device, $interface, $config_diffs['configuration']['interface_vlan_id']);
						//$config->execute();
				
					} else {
						throw new exception("Interface Name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."', there is a change to vlan id status but no new status", 20306);
					}
				}
				
				//l3 mtu
				if (isset($config_diffs['remove_configuration']['l3_mtu'])) {
				
					//because the administrative status is changing there should be a new admin status
					if (isset($config_diffs['configuration']['l3_mtu'])) {

						$config	= new Thelist_Routeros_command_setinterfacel3mtu($this->_device, $interface, $config_diffs['configuration']['l3_mtu']);
						$config->execute();
				
					} else {
						throw new exception("there is a change for l3mtu but no new value for interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."'", 20307);
					}
				}
				
				//l2 mtu
				if (isset($config_diffs['remove_configuration']['l2_mtu'])) {

					if (isset($config_diffs['configuration']['l2_mtu'])) {
				
						if ($if_type != 'vlan') {
							$config	= new Thelist_Routeros_command_setinterfacel3mtu($this->_device, $interface, $config_diffs['configuration']['l2_mtu']);
							$config->execute();
						} else {
							
							//maybe sync parent if this situation happens, the device is reporting a differnet l2mtu because the database is out of sync with the device
							//we get vlan mtu from parent interface
							throw new exception("there is a change for l2mtu but we cannot set l2mtu values for vlans on mikrotik, they get l2mtu from parent. Interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."'", 20308);
						}

					} else {
						throw new exception("there is a change for l3mtu but no new value for interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."'", 20307);
					}
				}

			} else {
				
				//we need to create the interface
				if ($if_type != 'nstreme_dual' && $if_type != 'vrrp' && $if_type != 'vlan' && $if_type != 'bridge') {
					//we cannot create physical interfaces, only the ones above
					throw new exception("very sorry but we cannot create a new interface of type: '".$if_type."' with interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."', because it is a physical interface and we can just create those magically on a device ", 20301);
				}
				
				if ($if_type == 'vlan') {

				}
				
				
				
				
			}
			
			echo "\n <pre> 1111  \n ";
			print_r($config_diffs);
			echo "\n 2222 \n ";
			print_r($new_config_array);
			echo "\n 3333 \n ";
			//print_r();
			echo "\n 4444 </pre> \n ";
			die;

			//if no ips are set then we can just get a standard config because this is either the physical interface
			//or a vlan that is used for an L2 bridge, in this case there is no reason to modify the device name
			$device_config	= $config_generator->generate_config_device_syntax($new_config_array);
		
			
			//now remove the old interfaces on the device, in addition if this is using the object model we remove and recreate the slave interfaces as well because 
			//we cannot remove the master interface without first getting rid of the slaves
			if (is_object($this->_options)) {
				
				$slave_interfaces = $this->_options->get_master_relationships();
				
				if ($slave_interfaces != null) {
				
					foreach($slave_interfaces as $slave_interface) {
						$remove_interface_on_device	= new Thelist_Bairos_command_removeinterfaceconfig($this->_device, $slave_interface);
						$remove_interface_on_device->execute();
					}
				}
			}
			
			//now remove the main interface for both string and object models
			$remove_interface_on_device	= new Thelist_Bairos_command_removeinterfaceconfig($this->_device, $this->_options);
			$remove_interface_on_device->execute();

			//and finally create the interfaces again.
			foreach ($new_config_files as $single_interface_name => $single_configuration) {
				$this->_device->execute_command("echo \"".$single_configuration."\" > /etc/sysconfig/network-scripts/ifcfg-".$single_interface_name."");
			}
			
			//after a successful push we now check that all files made it to the bairouter
			$get_network_configs	= new Thelist_Bairos_command_getfilelist($this->_device, '/etc/sysconfig/network-scripts/');
			
			foreach ($new_config_files as $single_interface_name => $single_configuration) {

				//dont refresh the result no need, first time it will refresh on its own
				$file_exist	= $get_network_configs->get_file("ifcfg-".$single_interface_name."", false);
				
				if ($file_exist == false) {

					if (isset($new_config_array['configuration']['administrative_status'])) {
						
						//get as many interfaces up as possible, if this interface is set to admin status 1
						if ($new_config_array['configuration']['administrative_status'] == 1) {
							
							//unless this is the first interface because then nothing will come up and
							//we just end up throwing an error in another class confusing us
							if ($single_interface_name != $interface_name) {
								
								$up_successful = new Thelist_Bairos_command_setinterfaceadminstatus($this->_device, $this->_options, 1);
								$up_successful->execute();
								
								//now throw exception
								throw new exception("we created or replaced an interface config on ".$this->_device->get_fqdn()." but interface name: ".$single_interface_name." never made it to the bai router, we did start the interface, but ips are missing ", 12800);
							} else {
								throw new exception("we created or replaced an interface config on ".$this->_device->get_fqdn()." but interface name: ".$single_interface_name." never made it to the bai router, this is the main interface, so interface is currently down ", 12801);
							}
						} else {
							throw new exception("we created or replaced an interface config on ".$this->_device->get_fqdn()." but interface name: ".$single_interface_name." never made it to the bai router, the interface that was pushed was configured for admin down ", 12802);
						}
					}
					//general error if we dont have admin status
					throw new exception("we created or replaced an interface config on ".$this->_device->get_fqdn()." but interface name: ".$single_interface_name." never made it to the bai router, we could not tell if the interface was configured for admin status up or down, cuently it is down ", 12803);
				}
			}
			
			//if everything went well then we now up the interface if that is what the config wants
			if (isset($new_config_array['configuration']['administrative_status'])) {
				
				if ($new_config_array['configuration']['administrative_status'] == 1) {
					$up_interface = new Thelist_Bairos_command_setinterfaceadminstatus($this->_device, $this->_options, 1);
					$up_interface->execute();
				}
			}
			
			//if we tore down any slave interfaces now recreate them
			if (isset($slave_interfaces)) {
				
				if ($slave_interfaces != null) {
					foreach($slave_interfaces as $slave_interface) {
						$re_create_slave_on_device	= new Thelist_Bairos_command_setinterfaceconfig($this->_device, $slave_interface);
						$re_create_slave_on_device->execute();	
					}
				}
			}
			
			//we are done
		}
	}
}