<?php

//exception codes 12800-12899

class thelist_bairos_command_setinterfaceconfig implements Thelist_Commander_pattern_interface_idevicecommand 
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
			
			$config_generator		= new Thelist_Bairos_config_interface(new Thelist_model_equipments($this->_options->get_eq_id()), $this->_options);
			$new_config_array		= $config_generator->generate_config_array();
			$interface_name			= $this->_options->get_if_name();
			
			//now we need to get the current running config on the device interface
			$device_config			= new Thelist_Bairos_command_getinterfaceconfig($this->_device, $this->_options);
			$device_config_array	= $device_config->get_interface_config();
			
		} elseif (is_array($this->_options)) {

			$config_generator 		= new Thelist_Bairos_config_interface(null, $this->_options['interface_name']);
			$new_config_array		= $this->_options['new_config'];
			$interface_name			= $this->_options['interface_name'];
			
			//now we need to get the current running config on the device interface
			$device_config			= new Thelist_Bairos_command_getinterfaceconfig($this->_device, $this->_options['interface_name']);
			$device_config_array	= $device_config->get_interface_config();
		}
		
		//if there is no config on the device at all then we get a false back
		//but the diff class is expecting an array named configuration
		if ($device_config_array == false) {
			$device_config_array['configuration'] = array();
		}

		$diff			= new Thelist_Multipledevice_config_configdifferences($new_config_array, $device_config_array);
		$config_diffs	= $diff->generate_config_array();

		if ($config_diffs != false) {

			if (isset($new_config_array['configuration']['interface_ip_addresses'])) {
				
				$copy_of_new_array	= $new_config_array;
				//unset the interface_ip_addresses index
				unset($copy_of_new_array['configuration']['interface_ip_addresses']);
				
				//we always want the parent interface to NOT have ANY ip information
				//this allows us to restart ip carrying interfaces without touching the parent physical interface
				$new_config_files[$interface_name]	= $config_generator->generate_config_device_syntax($copy_of_new_array);
				
				$i=0;
				foreach ($new_config_array['configuration']['interface_ip_addresses'] as $ip_address) {
					
					//insert only a single ip address per config
					$copy_of_new_array['configuration']['interface_ip_addresses']['0'] = $ip_address;
					
					$new_interface_name = $interface_name . ":" . $i;
					$new_config_files[$new_interface_name]	= preg_replace("/".$interface_name."/", $new_interface_name, $config_generator->generate_config_device_syntax($copy_of_new_array));
					
					$i++;
				}
				
			} else {
				//if no ips are set then we can just get a standard config because this is either the physical interface
				//or a vlan that is used for an L2 bridge, in this case there is no reason to modify the device name
				$new_config_files[$interface_name]	= $config_generator->generate_config_device_syntax($new_config_array);
			}
			
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