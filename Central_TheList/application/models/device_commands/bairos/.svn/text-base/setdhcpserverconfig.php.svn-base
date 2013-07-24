<?php

//exception codes 14000-14099

class thelist_bairos_command_setdhcpserverconfig implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_application;

	public function __construct($device, $application)
	{
		//$application
		//object	= mapped application_obj
		//string	= ['new_config']
		//string	= ['interface_name']
		
		$this->_device 						= $device;
		$this->_application 				= $application;
	}
	
	public function execute()
	{
		
		//change so if any errors are encountered we roll bak to the old config from the device
		if (is_object($this->_application)) {
			
			$equipment				= new Thelist_Model_equipments($this->_application->get_mapped_eq_id());
			
			$config_generator		= new Thelist_Bairos_config_dhcpserver($equipment, $this->_application);
			$new_config_array		= $config_generator->generate_config_array();
			
			if ($equipment->get_interfaces() != null) {
				
				foreach ($equipment->get_interfaces() as $single_interface) {
					
					if ($single_interface->get_if_name() == $new_config_array['configuration']['interface_name']) {
						$interface 			= $single_interface;
						$interface_name		= $single_interface->get_if_name();
					}
				}
				
			} else {
				throw new exception("we are setting the dhcp server for ".$this->_device->get_fqdn().", but in the object model that equipment does not have any interfaces", 14000);
			}

		} elseif (is_array($this->_application)) {

			$config_generator 		= new Thelist_Bairos_config_dhcpserver(null, null);
			$new_config_array		= $this->_application['new_config'];
			
			$interface			= $this->_application['interface_name'];
			$interface_name		= $this->_application['interface_name'];
		}
		
		//now we need to get the current running config on the device interface
		$device_config			= new Thelist_Bairos_command_getdhcpserverconfig($this->_device, $interface);
		$device_config_array	= $device_config->get_dhcp_configuration();
		
		//if there is no config on the device at all then we get a false back
		//but the diff class is expecting an array named configuration
		if ($device_config_array == false) {
			$device_config_array['configuration'] = array();
		}
		
		//the compare_multi_dimentional_arrays method is not cleaning up after itself
		//currently it will not return a false even if the arrays are a perfect match
		//this will be fixed, it is just a matter of removing empty indexes
		$array_tools = new Thelist_Utility_arraytools();
		$config_diffs = $array_tools->compare_multi_dimentional_arrays($new_config_array, $device_config_array);

		if ($config_diffs != false) {
			
			$conf_file_path = Thelist_Utility_staticvariables::get_bairouter_root_config_path()."/dhcp_server/".$interface_name;
			
			if (count($new_config_array['networks']) > 0) {

				$dhcp_server_config_file	= $config_generator->generate_config_device_syntax($new_config_array);
				
				if ($dhcp_server_config_file != false) {

					$make_conf_file	= new Thelist_Bairos_command_setfilecontent($this->_device, $conf_file_path, $dhcp_server_config_file, 'override');
					$make_conf_file->execute();

					if (is_object($this->_application)) {
						$include_originating_interface = 'yes';
					}
				
				} else {
					throw new exception("dhcp config has changes but, the config file is empty for device: ".$this->_device->get_fqdn()." ", 14002);
				}
				
				
			} else {
				//if there are no networks in the config it is because there are no ips in a range or any reservations for this interface
				if (is_object($this->_application)) {
					$include_originating_interface = 'no';
				}
				
				//remove the original file if it is on the device
				$remove_old_conf_file = new Thelist_Bairos_command_removefile($this->_device, $conf_file_path);
				$remove_old_conf_file->execute();
			}
			
			//rewrite the main config file, if this is string model we have to rely on the content of the config folder
			//in the object model we can use the database
			
			$get_dhcp_configs	= new Thelist_Bairos_command_getfilelist($this->_device, "".Thelist_Utility_staticvariables::get_bairouter_root_config_path()."/dhcp_server/");
			$files = $get_dhcp_configs->get_files();
			
			if ($files != false) {
			
				foreach($files['files'] as $file) {
			
					$files_to_include[]	= $file['file_name'];
				}
			
			} else {
				
				if (is_object($this->_application)) {
					
					//extra check in the object model, if we uploaded a file and now find nothing in the folder there is a problem
					if ($include_originating_interface == 'yes') {
						throw new exception("we just uploaded a file using the string model but the dhcp config folder has no files in it for device: ".$this->_device->get_fqdn()." ", 14001);
					}
				}
			}

			if (is_object($this->_application)) {
				
				//get all active dhcp servers
				$sql	= 	"SELECT GROUP_CONCAT(eamm.equipment_application_map_id) AS active_dhcp_server_app_map_ids FROM equipment_application_mapping eam
							INNER JOIN equipment_application_metric_mapping eamm ON eamm.equipment_application_map_id=eam.equipment_application_map_id
							WHERE eam.eq_id='".$equipment->get_eq_id()."'
							AND eam.equipment_application_id='1'
							AND eamm.equipment_application_metric_id='9'
							AND eamm.equipment_application_metric_value='1'
							";
				
				if ($include_originating_interface == 'no') {
					//if there are no networks in the config then we dont include this interface to be enabled
					$sql .= " AND eamm.equipment_application_map_id!='".$this->_application->get_equipment_application_map_id()."'";
				}
				
				$active_dhcp_servers  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
				
				if ($active_dhcp_servers['active_dhcp_server_app_map_ids'] != null) {
					
					$sql2	= 	"SELECT eamm.equipment_application_metric_value FROM equipment_application_metric_mapping eamm
								WHERE eamm.equipment_application_metric_id='13'
								AND eamm.equipment_application_map_id IN (".$active_dhcp_servers['active_dhcp_server_app_map_ids'].")
								";
				
					$interfaces_to_include  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
					
				}

				if (isset($interfaces_to_include['0']) && isset($files_to_include)) {
						
					//turn in into a standard array with numeric indexes
					foreach($interfaces_to_include as $interface_name) {

						//we know there is atleast one file in the array from the check above
						foreach($files_to_include as $conf_file_name) {
							
							if ($conf_file_name == $interface_name['equipment_application_metric_value']) {
								$active_dhcp_configs[]	= $interface_name['equipment_application_metric_value'];								
							}
						}
					}
					
					//was there any overlap?
					if (isset($active_dhcp_configs)) {
						$files_to_include = $active_dhcp_configs;
					} else {
						
						//no overlap so get rid of the variables
						if (isset($files_to_include)) {
							unset($files_to_include);
						}
					}
					
				
				} else {
					//there are no active servers currently
					//get rid of any files that may have been picked up in the folder
					if (isset($files_to_include)) {
						unset($files_to_include);
					}
				}
			}
			
			//create the main config file
			$time_obj = new Thelist_Utility_time();
				
			//set the variable for the return
			$static_header = "\n### DHCP Config Generated by setdhcpserverconfig: " . $time_obj->get_current_date_time_as_am_pm() . "\nddns-update-style none;\n";
			$main_file_path	= "/etc/dhcpd.conf";
			
			$make_main_file	= new Thelist_Bairos_command_setfilecontent($this->_device, $main_file_path, $static_header, 'override');
			$make_main_file->execute();
			
			//there may not be any servers that are active, this is really only relevant for the object model as the string model will upload a file for sure
			//and therefore have at least one file to include
			if (isset($files_to_include)) {
				
				//now appendeach of the config files (notice the 2x ">>" not just once, this appends a file in linux)
				
				foreach($files_to_include as $file_name) {
						
					//we must escape the \ (backslash) on the commandline this is why we use \\\ first escape in php then linux
					$include_statement	= "include \\\"".Thelist_Utility_staticvariables::get_bairouter_root_config_path()."/dhcp_server/".$file_name."\\\";";
						
					$append_main_file	= new Thelist_Bairos_command_setfilecontent($this->_device, $main_file_path, $include_statement, 'append');
					$append_main_file->execute();
					
				}
				
				if (is_object($this->_application)) {
					
					if ($include_originating_interface == 'yes') {
						
						$set_op_status	= new Thelist_Bairos_command_setdhcpserverstatus($this->_device, $interface, 'reload');
						$set_op_status->execute();
						
					} elseif ($include_originating_interface == 'no') {
						
						$set_op_status	= new Thelist_Bairos_command_setdhcpserverstatus($this->_device, $interface, 'stop');
						$set_op_status->execute();
					}

				} else {
					
					//string model
					//now make sure the server is reloaded
					$set_op_status	= new Thelist_Bairos_command_setdhcpserverstatus($this->_device, $interface, 'reload');
					$set_op_status->execute();
				}
				
				
				
				
			} else {
				//there are no files included in the setup, so we stop the server all together
				$set_op_status	= new Thelist_Bairos_command_setdhcpserverstatus($this->_device, $interface, 'stop');
				$set_op_status->execute();
			}
		}
	}
}