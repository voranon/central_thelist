<?php

//exception codes 7200-7299

class thelist_model_synchronizeequipmenttodevice
{

	private $_equipment=null;
	private $_device=null;

	public function __construct()
	{
		
	}
	
	private function reset_variables_in_class()
	{	
		if ($this->_device != null) {
			
			$this->_device->save_config_on_device();
			$this->_device->logout_of_device();
			$this->_device = null;
		} 
		
		if ($this->_equipment != null) {
			$this->_equipment = null;
		}
	}
	
	private function get_device()
	{
		if ($this->_equipment != null) {
			
			if ($this->_device == null) {
					
				if ($this->_equipment->get_eq_type()->get_eq_manufacturer() == 'Mikrotik') {
					$device_credential = $this->_equipment->get_credential(1);
				} elseif ($this->_equipment->get_eq_type()->get_eq_manufacturer() == 'Cisco') {
					$device_credential = $this->_equipment->get_credential(2);
				} elseif ($this->_equipment->get_eq_type()->get_eq_manufacturer() == 'Bel Air Internet') {
					$device_credential = $this->_equipment->get_credential(1);
				} else {
					throw new exception("we are missing credential type for eq_id: ".$this->_equipment->get_eq_id()."", 7202);
				}
					
				//open a connection to the device, using the prefered API
				$this->_device = new Thelist_Model_device($this->_equipment->get_eq_fqdn(), $device_credential);
			}
			
			return $this->_device;
		} else {
			throw new exception("you must set the equipment first", 7203);
		}
	}
	
	public function sync_path_to_database_config($path)
	{
		//provide a path and this method will enforce the database config for all interfaces
		//in the path as well as the equipment specific attributes like SVI on cisco switches
		//it should use the far interface and work its way back so as never to lose connectivity
		//to a device before it is completely configured.
		
		//for now there is no validation of the order of the path so please 
		//make sure to deliver a path object where first interface is closest to the edge of the network
		$equipment_in_path	= $path->get_path_equipment();
		
		if (isset($equipment_in_path['0'])) {

			foreach($equipment_in_path as $equipment_index => $equipment) {

				//set the equipment
				$this->_equipment = $equipment['equipment'];
				
				//sometimes a function loop inside this main loop can finish the job for a specific piece of equipment
				//and in order not to just issue a break (bad manners), then we use a single variable $equipment_config_complete
				//to tell the rest of the loops that they do not need to engage because the equipment config is done
				$equipment_config_complete = 'no';

				//we only care about equipment that has api access
				//an rf splitter or diplexer or any other piece of equipment that is not managed
				//cannot be aligned with the database. if there is not a login then just move on.
				$eq_apis = $this->_equipment->get_apis();
				
				if ($eq_apis != false) {
					
					//get the device 
					
					//testing 
					if ($this->_equipment->get_eq_type()->get_eq_manufacturer() != 'Mikrotik') {
						$this->get_device();
					}
					
					
					//if the equipment has role "CPE Router", then we completely override its config
					//it is as close to the edge as we get and running induvidual commands to align the config with the
					//database is not feasable at this point
					//maybe in the future all equipment can be treated the same, but for now we issue a complete override and
					//since the override does not affect any other customers many times that involves a reboot
						
					//equipment roles
					$eq_roles = $this->_equipment->get_equipment_roles();
						
					if ($eq_roles != null) {
					
						foreach($eq_roles as $eq_role) {

							//mikrotik cpe router
							if ($eq_role->get_equipment_role_id() == 4 && $this->_equipment->get_eq_type()->get_eq_manufacturer() == 'Mikrotik') {
					
								//override the entire config of the mikrotik cpe router
								//$this->override_device_configuration($this->_equipment);
								$equipment_config_complete = 'yes';
							}
						}
					}

					if ($equipment_config_complete == 'no' && isset($equipment['inbound_interface'])) {
						//remember we are moving from the edge inwards
						//inbound interface is the furthest away from any management
						//access so we start here.
						
						//sync the inbound interface to the database
						$this->_device->configure_interface($equipment['inbound_interface']);
						
					}

					if ($equipment_config_complete == 'no') {
						
						//now check if the equipment has a SVI interface, if it does that means we update the
						//vlan transit database
						if ($this->_equipment->get_interfaces() != null) {
							
							foreach($this->_equipment->get_interfaces() as $interface) {
								
								if($interface->get_if_type()->get_if_type_id() == 28) {
									
									//sync the device transit vlan database to our database
									$this->_device->configure_interface($interface);
								}
							}
						}
					}
					
					if ($equipment_config_complete == 'no' && isset($equipment['outbound_interface'])) {
						//next up is outbound interface
						//sync the outbound interface to the database
						$this->_device->configure_interface($equipment['outbound_interface']);
					}
					

					//after all interfaces have been configured we check if any applications will need an update
					
					if ($eq_roles != null) {
							
						foreach($eq_roles as $eq_role) {
					
							//border router
							if ($eq_role->get_equipment_role_id() == 1) {
									
								//update all relevant applications
								$this->update_application($this->_equipment);
								
								//update all speed caps
								$this->update_bandwidth_control($this->_equipment);
							}
						}
					}
					
					
					//we are done with this equipment
					$equipment_config_complete = 'yes';
					
					//complete the configuration and reset the variables for the next equipment
					$this->reset_variables_in_class();
					
				} else {
					//no apis at all we are done
					$equipment_config_complete = 'yes';
				}
			}
			
		} else {
			throw new exception("the provided path have no interfaces", 7201);
		}
	}
	
	public function update_application($equipment, $equipment_application=null)
	{
		$this->_equipment = $equipment;
		$this->get_device();
		
		if ($equipment_application == null) {
			$applications	= $this->_equipment->get_application_mappings();
		} else {
			$applications[] = $equipment_application;
		}
		
		if (is_array($applications)) {
			
			foreach($applications as $application) {
				
				//dhcp server
				if ($application->get_equipment_application_id() == 1) {
					
					if ($this->_equipment->get_eq_type()->get_eq_manufacturer() == 'Bel Air Internet') {
						
						$command_class = new Thelist_Bairos_command_setdhcpserverconfig($this->_device, $application);
						$command_class->execute();

					}
				}
			}
		}
	}
	
	public function update_bandwidth_control($equipment)
	{
		$this->_equipment = $equipment;
		$this->get_device();
		
		$completed_interfaces = array();

		if ($equipment->get_interfaces() != null) {
				
			foreach($equipment->get_interfaces() as $interface) {

				if ($interface->get_connection_queues() != null) {
					
					foreach ($interface->get_connection_queues() as $queue) {
						
						//check if the interface queue has active filters
						if ($queue->get_connection_queue_filters() != null) {
							
							//we only need to update each interface once
							if (!isset($completed_interfaces[$interface->get_if_id()])) {
								
								//set the array so we dont hit this interface again
								$completed_interfaces[$interface->get_if_id()] = 'done';
								
								//bai routers
								if ($this->_equipment->get_eq_type()->get_eq_manufacturer() == 'Bel Air Internet') {
										
									$command_class = new Thelist_Bairos_command_setinterfacequeuefilters($this->_device, $interface);
									$command_class->execute();
										
								}
							}
						}
					}
				}
			}
		}
	}

	public function routeros_full_config($equipment)
	{

		//this method many times involve a reboot of the device
		//use with caution, it can affect devices that connect through it
		//many times using a specific commander pattern to change settings is far more 
		//accurate. basically only use if there is no way to configure the 
		//changes on the device without loosing connectivity before its complete.
		
		$this->_equipment	= $equipment;
		
		$equipment_type				= $this->_equipment->get_eq_type();
		$interfaces					= $this->_equipment->get_interfaces();
		$application_mappings		= $this->_equipment->get_application_mappings();
		$ip_traffic_rules			= $this->_equipment->get_ip_traffic_rules();
		$api_credentials			= $this->_equipment->get_apis();
		$ip_routes					= $this->_equipment->get_ip_routes();

		$config_file_content = "";
		
		
		//prepend file content
		if ($equipment_type->get_eq_manufacturer() == 'Mikrotik') {
			
			//mikrotik interfaces take a bit to come live, if we dont insert the wait then the interface config fails
			$config_file_content .= ":delay 5s\n";
		}
		
		//interfaces must be in place before ips are assigned 
		
		//INTERFACES 
		if ($interfaces != null) {
		
			foreach($interfaces as $interface) {

				if ($equipment_type->get_eq_manufacturer() == 'Mikrotik') {
					$command_config_class = new Thelist_Routeros_config_interface($this->_equipment, $interface);
					$config_file_content .= $command_config_class->generate_config_device_syntax($command_config_class->generate_config_array());
					$config_file_content .= "\n\n";
				}
				
				
				//get all ip addresses for an interface
				$ip_addresses	= $interface->get_ip_addresses();
				if ($ip_addresses != null) {
					
					foreach ($ip_addresses as $ip_address) {
						
						//we only care about hard coded ips, dhcp range ips is only of value to the dhcp server app
						//and dhcp lease ips are only of interest to the dhcp server on the bai router upstream.
						if ($ip_address->get_ip_address_map_type() == 88) {
							
							if ($equipment_type->get_eq_manufacturer() == 'Mikrotik') {
								
								$command_config_class = new Thelist_Routeros_config_ipaddress($interface, $ip_address);
								$config_file_content .= $command_config_class->generate_config_device_syntax($command_config_class->generate_config_array());
								$config_file_content .= "\n\n";
							}
						}
					}
				}
			}
		}
		
		//interfaces and ips must be in place before applications are loaded

		//APPLICATIONS
		if ($application_mappings != null) {

			foreach($application_mappings as $application_mapping) {
				
				if ($equipment_type->get_eq_manufacturer() == 'Mikrotik' && $application_mapping->get_equipment_application_id() == 1) {
					
					$command_config_class = new Thelist_Routeros_config_dhcpserver($this->_equipment, $application_mapping);
					$config_file_content .= $command_config_class->generate_config_device_syntax($command_config_class->generate_config_array());
					$config_file_content .= "\n\n";					
					
				} elseif ($equipment_type->get_eq_manufacturer() == 'Mikrotik' && $application_mapping->get_equipment_application_id() == 2) {
							
					$command_config_class = new Thelist_Routeros_config_ntpclient($this->_equipment, $application_mapping);
					$config_file_content .= $command_config_class->generate_config_device_syntax($command_config_class->generate_config_array());
					$config_file_content .= "\n\n";
					
				} elseif ($equipment_type->get_eq_manufacturer() == 'Mikrotik' && $application_mapping->get_equipment_application_id() == 3) {
							
					$command_config_class = new Thelist_Routeros_config_upnp($this->_equipment, $application_mapping);
					$config_file_content .= $command_config_class->generate_config_device_syntax($command_config_class->generate_config_array());
					$config_file_content .= "\n\n";
					
				} elseif ($equipment_type->get_eq_manufacturer() == 'Mikrotik' && $application_mapping->get_equipment_application_id() == 4) {
							
					$command_config_class = new Thelist_Routeros_config_snmp($this->_equipment, $application_mapping);
					$config_file_content .= $command_config_class->generate_config_device_syntax($command_config_class->generate_config_array());
					$config_file_content .= "\n\n";
					
				} elseif ($equipment_type->get_eq_manufacturer() == 'Mikrotik' && $application_mapping->get_equipment_application_id() == 5) {
							
					$command_config_class = new Thelist_Routeros_config_syslog($this->_equipment, $application_mapping);
					$config_file_content .= $command_config_class->generate_config_device_syntax($command_config_class->generate_config_array());
					$config_file_content .= "\n\n";
					
				} elseif ($equipment_type->get_eq_manufacturer() == 'Mikrotik' && $application_mapping->get_equipment_application_id() == 6) {
							
					$command_config_class = new Thelist_Routeros_config_connectiontracking($this->_equipment, $application_mapping);
					$config_file_content .= $command_config_class->generate_config_device_syntax($command_config_class->generate_config_array());
					$config_file_content .= "\n\n";
					
				} elseif ($equipment_type->get_eq_manufacturer() == 'Mikrotik' && $application_mapping->get_equipment_application_id() == 7) {
							
					$command_config_class = new Thelist_Routeros_config_dhcpclient($this->_equipment, $application_mapping);
					$config_file_content .= $command_config_class->generate_config_device_syntax($command_config_class->generate_config_array());
					$config_file_content .= "\n\n";
					
				} else {
					throw new exception("we have no instructions on how to override application configurations for application_map_id: ".$application_mapping->get_equipment_application_map_id()." ", 7200);
				}
			}
		}
		
		//interfaces and ips must be in place before ip traffic is loaded
		
		//IP TRAFFIC
		if ($ip_traffic_rules != null) {
				
			//traffic rules must be done in order, if the input general drop clause is before any input
			//the input will be dropped before it reaches the input statement
			//the equipment class is set to order the array of objects based on the priority
			//if any issues check that is still the case
			foreach ($ip_traffic_rules as $ip_traffic_rule) {
					
				if ($equipment_type->get_eq_manufacturer() == 'Mikrotik') {
		
					$command_config_class = new Thelist_Routeros_config_iptraffic($this->_equipment, $ip_traffic_rule);
					$config_file_content .= $command_config_class->generate_config_device_syntax($command_config_class->generate_config_array());
					$config_file_content .= "\n\n";
						
				}
			}
		}
		
		//API Credentials
		if ($api_credentials != null) {
			
			foreach ($api_credentials as $api_credential) {
					
				if ($equipment_type->get_eq_manufacturer() == 'Mikrotik') {
		
					$command_config_class = new Thelist_Routeros_config_apicredential($this->_equipment, $api_credential);
					$config_file_content .= $command_config_class->generate_config_device_syntax($command_config_class->generate_config_array());
					$config_file_content .= "\n\n";
		
				}
			}
		}
		
		//IP Routes
		if ($ip_routes != null) {
			
			foreach ($ip_routes as $ip_route) {
					
				if ($equipment_type->get_eq_manufacturer() == 'Mikrotik') {
		
					$command_config_class = new Thelist_Routeros_config_iproute($this->_equipment, $ip_route);
					$config_file_content .= $command_config_class->generate_config_device_syntax($command_config_class->generate_config_array());
					$config_file_content .= "\n\n";
		
				}
			}
		}
		
		//HOSTNAME
		if ($equipment_type->get_eq_manufacturer() == 'Mikrotik') {
			$command_config_class = new Thelist_Routeros_config_hostname($this->_equipment);
			$config_file_content .= $command_config_class->generate_config_device_syntax($command_config_class->generate_config_array());
			$config_file_content .= "\n\n";
		}
		
		//CONNECTION QUEUES
		//missing

		//return the result
		return $config_file_content;
		
	}
	
	public function override_device_configuration($equipment, $config=null)
	{
		$this->_equipment	= $equipment;

		if ($this->_equipment->get_eq_type()->get_eq_manufacturer() == 'Mikrotik') {
	
			if ($config == null) {
				$config = $this->routeros_full_config($this->_equipment);
			}
			
			$filename = "eq_id_".$equipment->get_eq_id()."_" . time() . ".rsc";
			
			//save config locally
			$save_file	= new Thelist_Utility_savefiletoserver($file_name);
			$save_file->create_device_config_file_from_content('routeros', $config);

			//reset device
			$this->_device->reset_config($filename);
			
			//this function resets the connection to the device by rebooting it, we cant logout or save as mikrotik does this 
			//we null the device because it no longer exists and if we tried to run the save and logout functions we would get exceptions
			//because the device no longer exists
			$this->_device = null;
		} else {
			return false;
		}
	}

}
?>