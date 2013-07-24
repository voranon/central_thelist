<?php
class thelist_html_element_htmltable
{
		private $database;
		
			public function __construct()
			{
				

					
			}
			
			public function device_function_dd($device_function_id=null)
			{
			
				if ($device_function_id != null) {
			
					$current_device_function_obj = new devicefunction($device_function_id);
			
					$device_function_dd = '';
					$device_function_dd.="<SELECT NAME='device_function_id'>";
					$device_function_dd.="<OPTION VALUE='".$current_device_function_obj->get_device_function_id()."'>".$current_device_function_obj->get_device_function_name()."</OPTION>";
			
				} else {
			
					$device_function_dd = '';
					$device_function_dd.="<SELECT NAME='device_function_id'>";
					$device_function_dd.="<OPTION VALUE=''>---SELECT ONE---</OPTION>";
			
			
				}
					
				$sql =	"SELECT device_function_id FROM device_functions
			 		 	ORDER BY device_function_name ASC
			 		 	";
			
				$device_functions = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			
				foreach ($device_functions as $device_function) {
			
					$device_function_obj = new Thelist_Model_devicefunction($device_function['device_function_id']);
			
					$device_function_dd.="<OPTION VALUE='".$device_function_obj->get_device_function_id()."'>".$device_function_obj->get_device_function_name()."</OPTION>";
				}
					
				$device_function_dd .= "</SELECT>";
			
				return $device_function_dd;
			
			}
			
			public function eq_type_dd($eq_type_id=null)
			{
			
				if ($eq_type_id != null) {
			
					$current_eq_type_obj = new Thelist_Model_equipmenttype($eq_type_id);
			
					$equipment_type_dd = '';
					$equipment_type_dd.="<SELECT NAME='eq_type_id'>";
					$equipment_type_dd.="<OPTION VALUE='".$current_eq_type_obj->get_eq_type_id()."'>".$current_eq_type_obj->get_eq_manufacturer()." ".$current_eq_type_obj->get_eq_model_name()."</OPTION>";
			
				} else {
			
					$equipment_type_dd = '';
					$equipment_type_dd.="<SELECT NAME='eq_type_id'>";
					$equipment_type_dd.="<OPTION VALUE=''>---SELECT ONE---</OPTION>";
			
			
				}
					
				$sql =	"SELECT eq_type_id FROM equipment_types
			 		 	ORDER BY eq_manufacturer ASC
			 		 	";
			
				$eq_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			
				foreach ($eq_types as $eq_type) {
			
					$eq_type_obj = new Thelist_Model_equipmenttype($eq_type['eq_type_id']);
			
					$equipment_type_dd.="<OPTION VALUE='".$eq_type_obj->get_eq_type_id()."'>".$eq_type_obj->get_eq_manufacturer()." ".$eq_type_obj->get_eq_model_name()."</OPTION>";
				}
					
				$equipment_type_dd .= "</SELECT>";
			
				return $equipment_type_dd;
					
			}
			
			public function configurations_table($order_by=null)
			{
				
				if ($order_by == null) {
					
					$sql = 	"SELECT * FROM configurations";
					
				} else {
					
					$sql = 	"SELECT * FROM configurations
							ORDER BY $order_by
							";
				}
				
				$configurations = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
				
				$configurations_table = '';
				
				$configurations_table .= "
										<tr class='header'>
										<td class='display' style='width: 100px'>Show</td>
										<td class='display' style='width: 100px'>Configuration Name</td>
										</tr>
										";
				
				foreach($configurations as $configuration){
					
					$configuration_obj = new configuration($configuration['conf_id']);

					$configurations_table .= "
											<tr>
											<td class='display'><a href='/inventory/showconfiguration?conf_id=".$configuration_obj->get_conf_id()."' >Show Config</a></td>
											<td class='display'>".$configuration_obj->get_conf_name()."</td>
				 							</tr>
											";
				}
	
				return $configurations_table;
				
			}
			
			public function configurations_menu_table()
			{
				$configurations_menu_table = "
									
									<tr>
									<td><a href='/inventory/configurations'>Show All</a></td>
									<td><a id='addconfig' href=''>Add Configuration</a></td>
									</tr>";
				
				return $configurations_menu_table;

			}
			
			
			
			public function parameter_table_dd($device_command_parameter_table_id=null)
			{
				if ($device_command_parameter_table_id != null) {
						
					$sql =	"SELECT * FROM device_command_parameter_tables
							WHERE device_command_parameter_table_id='".$device_command_parameter_table_id."'
							";
				
					$current_parameter_table = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
						
					$parameter_table_dd = '';
					$parameter_table_dd.="<SELECT NAME='device_command_parameter_table_id'>";
					$parameter_table_dd.="<OPTION VALUE='".$current_parameter_table['device_command_parameter_table_id']."'>".$current_parameter_table['table_name']."</OPTION>";
						
				} else {
						
					$parameter_table_dd = '';
					$parameter_table_dd.="<SELECT NAME='device_command_parameter_table_id'>";
					$parameter_table_dd.="<OPTION VALUE=''>---SELECT ONE---</OPTION>";
						
						
				}
					
				$sql2 =	"SELECT * FROM device_command_parameter_tables
						";
				
				$all_parameter_tables = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
					
					
				foreach ($all_parameter_tables as $all_parameter_table) {
						
						
					$parameter_table_dd.="<OPTION VALUE='".$all_parameter_table['device_command_parameter_table_id']."'>".$all_parameter_table['table_name']."</OPTION>";
				}
					
				$parameter_table_dd .= "</SELECT>";
					
				return $parameter_table_dd;

			}

		
	}
	
?>