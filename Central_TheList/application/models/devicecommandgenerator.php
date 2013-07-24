<?php
//by martin
//exception codes 7400-7499

class thelist_model_devicecommandgenerator
{
	//common		
	private $database;
	private $logs;
	
	//for this class

				
	public function __construct()
	{
		

		$this->logs			= Zend_Registry::get('logs');
		
	}
	
// 	public function get_commands_by_function_name_xml($device_function_name, $pri_key)
// 	{
////	currently there is nothing that says 2 commands cannot have the same name, so this method is less pricise
		
// 		$sql = 		"SELECT device_function_id FROM device_functions
// 					WHERE device_function_name='".$device_function_name."'
// 					";
		
// 		$device_function_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
// 		return $this->generate_commands($device_function_id, $pri_key);

// 	}
	
	public function get_commands_without_eq_id_as_xml($source_software_package_id, $eq_type_id, $device_function_id)
	{
		
		//the purpose of this method is to execute simple "no parameter" commands
		//this method is very limited, if any of the requested commands require parameter replacement it throws an exception.
		$device_commands = $this->get_base_commands($source_software_package_id, $eq_type_id, $device_function_id);
		
		$xmlDoc = new DOMDocument();
		
		$head = $xmlDoc->appendChild(
		$xmlDoc->createElement("equipment_commands"));
		
		$progress_tracking_array = array();
		
		foreach ($device_commands as $device_command ){

			if (!isset($progress_tracking_array[$device_command['command_exe_order']])) {

				$progress_tracking_array[$device_command['command_exe_order']] = '1';
				
				//create the command object
				$device_command_obj = new devicecommand($device_command['device_command_id']);
								
				//add a command element to the XML.
				$command = $head->appendChild(
				$xmlDoc->createElement("command_element"));
				
				//add the execution order
				$command->appendChild(
				$xmlDoc->createElement("command_order_number", $device_command['command_exe_order']));
				

				//if there are no parmeters then $base_command is the final command, else replace all the parameters
				if ($device_command_obj->get_device_command_parameters() == false) {
					
					$base_command = $device_command_obj->get_base_command();
										
				} else {

					throw new exception('the devicefunction you called requires command parameter replacements, this is not possible with this method', 7403);

				}
				//there is an issue with '&' not being escaped in XML (https://bugs.php.net/bug.php?id=36795&edit=1) 
				$safe_base_command = preg_replace('/&(?!\w+;)/', '&amp;', $base_command);
				
				//sitting here to avoid duplicates 
				$command->appendChild(
				$xmlDoc->createElement("device_command", $safe_base_command));
				
				$command->appendChild(
				$xmlDoc->createElement("api_name", $device_command_obj->get_api_name()));
				$command->appendChild(
				$xmlDoc->createElement("api_id", $device_command_obj->get_api_id()));
				
				//now add the regexs.
				if ($device_command_obj->get_command_regexs() != null) {
					
					$command_regexs = $device_command_obj->get_command_regexs();
					
					foreach ($command_regexs as $command_regex) {

						$regex = $command->appendChild(
						$xmlDoc->createElement("regex"));
						
						//if there are no parmeters then $base_command is the final command, else replace all the parameters
						if ($command_regex->get_command_regex_parameters() == false) {
								
							$base_regex = $command_regex->get_base_regex();
						
						} else {
							
							throw new exception('the devicefunction you called requires regex parameter replacements, this is not possible with this method', 7405);
						
						}	
						
						//sitting here to avoid duplicates
						$regex->appendChild(
						$xmlDoc->createElement("command_regex", $base_regex));
						$regex->appendChild(
						$xmlDoc->createElement("command_regex_match", $command_regex->get_command_regex_match()));
						$regex->appendChild(
						$xmlDoc->createElement("command_regex_replace", $command_regex->get_command_regex_replace()));
	
					}
				}
			}
		}
		
		//make it look nice
		$xmlDoc->formatOutput = true;
		//return it
		return $xmlDoc->saveXML();
		
	}
	
	public function get_commands_in_xml($device_function_obj, $pri_key)
	{
		return $this->generate_commands($device_function_obj, $pri_key);
	}
	
	private function get_command_required_columns($device_command_id)
	{
		$sql = "SELECT GROUP_CONCAT(' ', ecpt.table_alias, '.', ecpc.column_name, ' AS ', ecp.device_command_parameter_name) AS required_columns FROM device_command_parameter_tables ecpt
				INNER JOIN device_command_parameter_columns ecpc ON ecpc.device_command_parameter_table_id=ecpt.device_command_parameter_table_id
				INNER JOIN device_command_parameters ecp ON ecp.device_command_parameter_column_id=ecpc.device_command_parameter_column_id
				INNER JOIN device_commands ec ON ec.device_command_id=ecp.device_command_id
				WHERE ec.device_command_id='".$device_command_id."'
				";
			
		return Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);

	}
	
	private function get_regex_required_columns($command_regex_id)
	{
		$sql = "SELECT GROUP_CONCAT(' ', ecpt.table_alias, '.', ecpc.column_name, ' AS ', crp.command_regex_parameter_name) AS required_columns FROM device_command_parameter_tables ecpt
				INNER JOIN device_command_parameter_columns ecpc ON ecpc.device_command_parameter_table_id=ecpt.device_command_parameter_table_id
				INNER JOIN command_regex_parameters crp ON crp.device_command_parameter_column_id=ecpc.device_command_parameter_column_id
				INNER JOIN command_regexs cr ON cr.command_regex_id=crp.command_regex_id
				WHERE cr.command_regex_id='".$command_regex_id."'
					";
			
		return Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
	
	}
	
	private function get_base_commands($source_software_package, $eq_type_id, $device_function_obj)
	{

		$sql = "SELECT ec.device_command_id, ecm.command_exe_order FROM device_functions ef
				LEFT OUTER JOIN device_command_parameter_tables ecpt ON ecpt.device_command_parameter_table_id=ef.device_command_parameter_table_id
				LEFT OUTER JOIN device_function_mapping efm ON efm.device_function_id=ef.device_function_id
				LEFT OUTER JOIN equipment_type_software_mapping etsm ON etsm.eq_type_software_map_id=efm.eq_type_software_map_id
				LEFT OUTER JOIN device_command_mapping ecm ON ecm.device_function_map_id=efm.device_function_map_id
				LEFT OUTER JOIN device_commands ec ON ec.device_command_id=ecm.device_command_id
				LEFT OUTER JOIN device_command_parameters ecp ON ecp.device_command_id=ec.device_command_id
				LEFT OUTER JOIN device_command_parameter_columns ecpc ON ecpc.device_command_parameter_column_id=ecp.device_command_parameter_column_id
				LEFT OUTER JOIN device_command_parameter_tables ecpt2 ON ecpt2.device_command_parameter_table_id=ecpc.device_command_parameter_table_id
				WHERE etsm.software_package_id='".$source_software_package->get_software_package_id()."'
				AND etsm.eq_type_id='".$eq_type_id."'
				AND ef.device_function_id='".$device_function_obj->get_device_function_id()."'
				ORDER BY ecm.command_exe_order DESC
				";
		
		$base_commands = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		

		
		if (!isset($base_commands['0'])) {
				
			throw new exception("no commands have been defined for software package: ".$source_software_package->get_software_package_id().", eq_type_id: ".$eq_type_id.", device_function_id: ".$device_function_obj->get_device_function_id()."", 7400);
				
		} else {
			
			return $base_commands;
			
		}
		
		
		
		
	}
	
	private function generate_commands($device_function_obj, $pri_key)
	{
				
		//d the primary_key to return the eq_id of the equipment that the pri_key belongs to.
		$sql = 	"SELECT * FROM device_command_parameter_tables ecpt
				WHERE device_command_parameter_table_id='".$device_function_obj->get_device_command_parameter_table_id()."'
				";
		
		$table_info = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

		$primary_eq_id_result = $this->get_command_parameters('eqs.eq_id', "".$table_info['table_alias'].".".$table_info['table_primary_column_name']."='".$pri_key."'");

		//get the equipment object
		$primary_equipment_obj = new Thelist_Model_equipments($primary_eq_id_result['eq_id']);

		//now get source eq_id, regardless of how many parent - child relationships there are in between
		$equipment = $this->get_source_eq($primary_equipment_obj->get_eq_id());
		
		//now get the software version running on the source equipment
		$source_software_package = $equipment->get_running_software_package();
				
		//use all this info to find the correct base commands
		$device_commands = $this->get_base_commands($source_software_package, $primary_equipment_obj->get_eq_type_id(), $device_function_obj);

		$progress_tracking_array = array();
		$xmlDoc = new DOMDocument();
		
		$head = $xmlDoc->appendChild(
		$xmlDoc->createElement("equipment_commands"));

		foreach ($device_commands as $device_command ){

			if (!isset($progress_tracking_array[$device_command['command_exe_order']])) {

				$progress_tracking_array[$device_command['command_exe_order']] = '1';
				
				//create the command object
				$device_command_obj = new Thelist_Model_devicecommand($device_command['device_command_id']);
								
				//add a command element to the XML.
				$command = $head->appendChild(
				$xmlDoc->createElement("command_element"));
				
				//add the execution order
				$command->appendChild(
				$xmlDoc->createElement("command_order_number", $device_command['command_exe_order']));
				

				//if there are no parmeters then $base_command is the final command, else replace all the parameters
				if ($device_command_obj->get_device_command_parameters() == false) {
					
					$base_command = $device_command_obj->get_base_command();
										
				} else {
				$base_command = $device_command_obj->get_base_command();
					
				//get columns that are required in order to fill the parameters, this way we can avoid ambiguity in the result
				$required_columns = $this->get_command_required_columns($device_command_obj->get_device_command_id());

				$command_parameters = $this->get_command_parameters($required_columns, "".$table_info['table_alias'].".".$table_info['table_primary_column_name']."='".$pri_key."'");
					
					if ($command_parameters != '') {
							
						//now replace the parameters in the command
						foreach($command_parameters as $key => $value){
							
							if ($value == 'not_a_command') {
								
								//if a parameter is "not_a_command" that means we never intended for this command set to be executed
								//most times this is because there are invalid place holder values in the database because i.e.
								//allowed vlans on a switch port can be ALL or NONE or a list from the vlans table.
								//if the list is from the vlans table then the configuration value will be 'not_a_command'.
								
								return false;
								
							}

							$base_command = $this->replace_parameters($base_command, $key, $value, $pri_key, $table_info['table_name']);

						}
					
					}

				}
				//there is an issue with '&' not being escaped in XML (https://bugs.php.net/bug.php?id=36795&edit=1) 
				$safe_base_command = preg_replace('/&(?!\w+;)/', '&amp;', $base_command);
				
				//sitting here to avoid duplicates 
				$command->appendChild(
				$xmlDoc->createElement("device_command", $safe_base_command));
				
				$command->appendChild(
				$xmlDoc->createElement("api_name", $device_command_obj->get_api_name()));
				$command->appendChild(
				$xmlDoc->createElement("api_id", $device_command_obj->get_api_id()));
				
				//now add the regexs.
				if ($device_command_obj->get_command_regexs() != null) {
					
					$command_regexs = $device_command_obj->get_command_regexs();
					
					foreach ($command_regexs as $command_regex) {

						$regex = $command->appendChild(
						$xmlDoc->createElement("regex"));
						
						//if there are no parmeters then $base_command is the final command, else replace all the parameters
						if ($command_regex->get_command_regex_parameters() == false) {
								
							$base_regex = $command_regex->get_base_regex();
						
						} else {
							
							$base_regex = $command_regex->get_base_regex();
								
							//get columns that are required in order to fill the parameters, this way we can avoid ambiguity in the result
							$required_regex_columns = $this->get_regex_required_columns($command_regex->get_command_regex_id());
						
							$regex_parameters = $this->get_command_parameters($required_regex_columns, "".$table_info['table_alias'].".".$table_info['table_primary_column_name']."='".$pri_key."'");
								
							if ($regex_parameters != '') {
									
								//now replace the parameters in the command
								foreach($regex_parameters as $key => $value){
									
										$base_regex = $this->replace_parameters($base_regex, $key, $value, $pri_key, $table_info['table_name']);
	
								}
									
							}
						
						}	
						
						//sitting here to avoid duplicates
						$regex->appendChild(
						$xmlDoc->createElement("command_regex", $base_regex));
						$regex->appendChild(
						$xmlDoc->createElement("command_regex_match", $command_regex->get_command_regex_match()));
						$regex->appendChild(
						$xmlDoc->createElement("command_regex_replace", $command_regex->get_command_regex_replace()));
	
					}
				}
			}
		}
		
		//make it look nice
		$xmlDoc->formatOutput = true;
		//return it
		return $xmlDoc->saveXML();
	
	}
	
	private function replace_parameters($base_command, $key, $value, $pri_key, $table_name) 
	{

		//there are special expressions that require a conversion before replacement
		//change the structure of this theis will get messy real quick.
		
		//one idea would be that you choose between functions and database values on the frontend. select the variable and tell the system what 
		//method will be resolving the variable into some data. then this method could use that information to either call a class that resolves the 
		//var or use the database.
		if ($key == 'CONVERT_BETWEEN_CIDR_AND_DOTTED_DECIMAL') {
		
			$mask = $this->convert_between_dotted_decimal_and_cidr($value);
		
			return preg_replace(".$key.", $mask, $base_command);
		
		} elseif (preg_match("/ADDTOIPADDRESS_/", $key, $matches)) {
		
			$number_to_add = explode("_", $key);
		
			$new_ip_address = $this->add_to_ip_address($value, $number_to_add['1']);
		
			return preg_replace(".$key.", $new_ip_address, $base_command);
		
		} elseif ($table_name == 'interfaces' && preg_match("/INTERFACE_MASTER/", $key, $matches)) {
		
			$sql = 	"SELECT i2.* FROM interfaces i
					INNER JOIN interfaces i2 ON i2.if_id=i.if_master_id
					WHERE i.if_id='".$pri_key."'
					";
		
			$master_if_row = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
			if (isset($master_if_row['if_id'])) {
					
				if (preg_match("/INTERFACE_MASTER_NAME/", $key, $matches)) {
		
					return preg_replace(".$key.", $master_if_row['if_name'], $base_command);
		
				} elseif (preg_match("/INTERFACE_MASTER_MAC_ADDRESS/", $key, $matches)) {
		
					return preg_replace(".$key.", $master_if_row['if_mac_address'], $base_command);
		
				}
			}
		
		} elseif (preg_match("/ADDTOIFINDEX_/", $key, $matches)) {
		
			$number_to_add = explode("_", $key);
		
			$new_value = ($value + $number_to_add['1']);
		
			return preg_replace(".$key.", $new_value, $base_command);

		} else {
		
			return preg_replace(".$key.", $value, $base_command);
		
		}

	}
	
	private function convert_between_dotted_decimal_and_cidr($subnet_mask)
	{
	
		if(preg_match("/\./", $subnet_mask, $matches)) {
			
			$cidr = ereg_replace('0.0.0.0', "0", ereg_replace('128.0.0.0', "1", ereg_replace('192.0.0.0', "2", ereg_replace('224.0.0.0', "3", ereg_replace('240.0.0.0', "4", ereg_replace('248.0.0.0', "5", ereg_replace('252.0.0.0', "6", ereg_replace('254.0.0.0', "7", ereg_replace('255.0.0.0', "8", ereg_replace('255.128.0.0', "9", ereg_replace('255.192.0.0', "10", ereg_replace('255.224.0.0', "11", ereg_replace('255.240.0.0', "12", ereg_replace('255.248.0.0', "13", ereg_replace('255.252.0.0', "14", ereg_replace('255.254.0.0', "15", ereg_replace('255.255.0.0', "16", ereg_replace('255.255.128.0', "17", ereg_replace('255.255.192.0', "18", ereg_replace('255.255.224.0', "19", ereg_replace('255.255.240.0', "20", ereg_replace('255.255.248.0', "21", ereg_replace('255.255.252.0', "22", ereg_replace('255.255.254.0', "23", ereg_replace('255.255.255.0', "24", ereg_replace('255.255.255.128', "25", ereg_replace('255.255.255.192', "26", ereg_replace('255.255.255.224', "27", ereg_replace('255.255.255.240', "28", ereg_replace('255.255.255.248', "29", ereg_replace('255.255.255.252', "30", ereg_replace('255.255.255.254', "31", ereg_replace('255.255.255.255', "32",$subnet_mask)))))))))))))))))))))))))))))))));
			
			return $cidr;

		} else {
			
			$dotted_decimal = ereg_replace('/0 ', "0.0.0.0", ereg_replace('/1 ', "128.0.0.0", ereg_replace('/2 ', "192.0.0.0", ereg_replace('/3 ', "224.0.0.0", ereg_replace('/4 ', "240.0.0.0", ereg_replace('/5 ', "248.0.0.0", ereg_replace('/6 ', "252.0.0.0", ereg_replace('/7 ', "254.0.0.0", ereg_replace('/8 ', "255.0.0.0", ereg_replace('/9 ', "255.128.0.0", ereg_replace('/10 ', "255.192.0.0", ereg_replace('/11 ', "255.224.0.0", ereg_replace('/12 ', "255.240.0.0", ereg_replace('/13 ', "255.248.0.0", ereg_replace('/14 ', "255.252.0.0", ereg_replace('/15 ', "255.254.0.0", ereg_replace('/16 ', "255.255.0.0", ereg_replace('/17 ', "255.255.128.0", ereg_replace('/18 ', "/255.255.192.0", ereg_replace('/19 ', "255.255.224.0", ereg_replace('/20 ', "255.255.240.0", ereg_replace('/21 ', "255.255.248.0", ereg_replace('/22 ', "255.255.252.0", ereg_replace('/23 ', "255.255.254.0", ereg_replace('/24 ', "255.255.255.0", ereg_replace('/25 ', "255.255.255.128", ereg_replace('/26 ', "255.255.255.192", ereg_replace('/27 ', "255.255.255.224", ereg_replace('/28 ', "255.255.255.240", ereg_replace('/29 ', "255.255.255.248", ereg_replace('/30 ', "255.255.255.252", ereg_replace('/31 ', "255.255.255.254", ereg_replace('/32 ', "255.255.255.255","/".$subnet_mask." ")))))))))))))))))))))))))))))))));
				
			return $dotted_decimal;
			
		}
	
	}
	
	private function add_to_ip_address($ip_address, $add_number)
	{
		
		if(preg_match("/\./", $ip_address, $matches)) {
			//ipv4
			$octets = explode(".", $ip_address);
			
			$new_4th_octet = $octets['3'] + $add_number;
			
			return "$octets[0].$octets[1].$octets[2].$new_4th_octet";
			
			
		} elseif (preg_match("/:/", $ip_address, $matches)) {
			//ipv6
			explode(":", $ip_address);
			
		}
		
	
	}
	public function resolve_source_eq($table_name, $primary_key) 
	{
		$eq_id = $this->resolve_eq_id($table_name, $primary_key);
		//the master eq_id regardless of the amount of child / parent relationships
		return $this->get_source_eq($eq_id);
	}
	
	public function resolve_eq_id($table_name, $primary_key)
	{		
		
		$sql = 		"SELECT dcpt.device_command_parameter_table_id FROM device_functions df
					INNER JOIN device_command_parameter_tables dcpt ON dcpt.device_command_parameter_table_id=df.device_command_parameter_table_id
					WHERE dcpt.table_name='".$table_name."'
					";
		
		$device_command_parameter_table_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		//then use this and the primary_key to return the eq_id of the equipment that the pri_key belongs to.
		$sql = 	"SELECT * FROM device_command_parameter_tables ecpt
				WHERE device_command_parameter_table_id='".$device_command_parameter_table_id."'
				";
		
		$table_info = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		$primary_eq_id_result = $this->get_command_parameters('eqs.eq_id', "".$table_info['table_alias'].".".$table_info['table_primary_column_name']."='".$primary_key."'");
		
		//result is an array
		return $primary_eq_id_result['eq_id'];

	}
	
	private function get_command_parameters($columns, $where_statement)
	{
		//all the tables that have to be joined together to return a result based on a random table and column goes here, eventually this should be replaced
		//by a method that can dynamically craft a join statement
		if ($columns != '' && $where_statement != '') {

			$sql = "SELECT ".$columns." FROM equipments eqs
					LEFT OUTER JOIN interfaces ifs ON ifs.eq_id=eqs.eq_id
					LEFT OUTER JOIN interface_configuration_mapping icm ON icm.if_id=ifs.if_id
					LEFT OUTER JOIN interface_configurations ic ON ic.if_conf_id=icm.if_conf_id
					LEFT OUTER JOIN interface_feature_mapping if_fm ON if_fm.if_id=ifs.if_id
					LEFT OUTER JOIN vlans vlan ON vlan.if_id=ifs.if_id
					LEFT OUTER JOIN equipment_software_upgrades eq_sp_u ON eq_sp_u.eq_id=eqs.eq_id
					LEFT OUTER JOIN equipment_mapping eq_map ON eq_map.eq_id=eqs.eq_id
					LEFT OUTER JOIN equipment_types et ON et.eq_type_id=eqs.eq_type_id
					LEFT OUTER JOIN connection_queues cq ON cq.if_id=ifs.if_id
					LEFT OUTER JOIN connection_queue_filters cqf ON cqf.connection_queue_id=cq.connection_queue_id
					LEFT OUTER JOIN ip_address_mapping ipamap ON ipamap.if_id=ifs.if_id
					LEFT OUTER JOIN ip_addresses ip_add ON ip_add.ip_address_id=ipamap.ip_address_id
					LEFT OUTER JOIN interface_features if_feat ON if_feat.if_feature_id=if_fm.if_feature_id
					LEFT OUTER JOIN ip_subnets ip_sub ON ip_sub.ip_subnet_id=ip_add.ip_subnet_id
					LEFT OUTER JOIN interface_types ift ON ift.if_type_id=ifs.if_type_id
					WHERE ".$where_statement."
					";
			
			//we limit the result to not trigger on dhcp reservations and dhcp range ip maps.
			//these are only used on the BAI routers but are used for config not to map the ips to the interface
			$sql .= "AND (ipamap.ip_address_map_type NOT IN (89,90) OR ip_address_map_type IS NULL)
					LIMIT 1
					";

			return Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
			
		} else {
			
			
			throw new exception('get_command_parameters is missing a variable', 7401);
			
		}



	}
	
	public function shortest_dynamic_join($start_table, $end_table)
	{
		$sql = 	"SELECT isc.table_name AS primary_table, isc.column_name AS primary_column, isc2.column_name AS foreign_column, isc2.table_name AS foreign_table FROM information_schema.columns isc
				INNER JOIN information_schema.columns isc2 ON isc2.column_name=isc.column_name
				WHERE isc.column_key='PRI'
				AND isc2.column_key!='PRI'
				AND isc.table_schema='thelist'
				AND isc2.table_schema='thelist'
				ORDER BY isc.table_name, isc.column_name DESC
				";
		
		$table_relationships = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		
		$i=0;
		foreach ($table_relationships as $table_relationship) {
		
			if ($table_relationship['primary_table'] ==  $start_table) {
				$i++;
				
				
				
			}
			
		}
		
	}
	
	private function get_source_eq($eq_id)
	{
			$sql = "CALL find_equipment_master($eq_id)";
			
			$eq_id =  Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);

			 if (isset($eq_id['eq_id'])) {
			 	
			 	return new Thelist_model_equipments($eq_id);
			 	
			 } else {
			 	
			 	throw new exception('we for some reason could not find the master eq', 7402);
			 	
			 }
			
	}
	
	private function get_all_eq_id_in_tree($eq_id, $presentation)
	{

		if ($presentation == 'comma_delimited') {
			
			$sql = "CALL get_equipment_tree($eq_id)";
			
			return Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		}

	
	}
}
?>