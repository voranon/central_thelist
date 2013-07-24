<?php
//by martin

class thelist_model_devicecommand
{
	//common		
	private $database;
	private $logs;
	
	//for this class
	private $_device_command_id;
	private $_base_command;
	private $_device_command_parameters;
	private $_device_command_read_only;
	private $_command_regexs=null;
	private $_api_id;
				
	public function __construct($device_command_id)
	{
		$this->_device_command_id = $device_command_id;
		

		$this->logs			= Zend_Registry::get('logs');
		
		//get the command from the database
		$sql = "SELECT * FROM device_commands
				WHERE device_command_id='".$this->_device_command_id."'
				";
		
		$device_command = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		$this->_base_command 				= $device_command['base_command'];
		$this->_api_id 						= $device_command['api_id'];
		
		//get all the parameters used in the base command
		$sql2 = "SELECT dcp.device_command_parameter_id, dcp.device_command_parameter_name, dcpc.device_command_parameter_column_id, dcpc.column_name, dcpt.device_command_parameter_table_id, dcpt.table_name FROM device_command_parameters dcp
				INNER JOIN device_command_parameter_columns dcpc ON dcpc.device_command_parameter_column_id=dcp.device_command_parameter_column_id
				INNER JOIN device_command_parameter_tables dcpt ON dcpt.device_command_parameter_table_id=dcpc.device_command_parameter_table_id
				WHERE dcp.device_command_id='".$this->_device_command_id."'
				";
		
		$this->_device_command_parameters = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
	
		//find out if the command is in use more than once, that means locked as a change could have unintentional concequences
		$sql3 = "SELECT COUNT(device_command_map_id) FROM device_command_mapping
				WHERE device_command_id='".$this->_device_command_id."'
				";
		
		$device_command_read_only = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql3);
				
		if ($device_command_read_only > 1) {
			
			$this->_device_command_read_only = '1';
			
		} else {
			
			$this->_device_command_read_only = '0';
			
		}
		
		//get all the regex used to validate this base command
		$sql2 = "SELECT * FROM command_regex_mapping
				WHERE device_command_id='".$this->_device_command_id."'
				";
		
		$command_regexs = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
		
		
		if (isset($command_regexs['0'])) {
		$this->_command_regexs = array();
			foreach ($command_regexs as $command_regex) {

				$this->_command_regexs[$command_regex['command_regex_map_id']] = new Thelist_Model_commandregex($command_regex['command_regex_id']);
				$this->_command_regexs[$command_regex['command_regex_map_id']]->set_command_regex_map_id($command_regex['command_regex_map_id']);
				
			}
		}
		
		
		
		
	}
	
	public function get_device_command_id() {
		return $this->_device_command_id;
	}
	public function get_base_command() {
		return $this->_base_command;
	}
	public function get_device_command_parameters() {
		return $this->_device_command_parameters;
	}
	public function get_device_command_read_only() {
		return $this->_device_command_read_only;
	}
	public function get_command_regexs() {
		return $this->_command_regexs;
	}
	public function get_api_id() {
		return $this->_api_id;
	}
	public function get_api_name() {
		
		$sql = "SELECT api_name FROM apis
				WHERE api_id='".$this->_api_id."'
				";
		$api_name = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		return $api_name;
	}
	
	public function set_base_command($base_command)
	{

		Zend_Registry::get('database')->set_single_attribute($this->_device_command_id, 'device_commands', 'base_command', $base_command, 'devicecommand', 'set_base_command');
		$this->_base_command = $base_command;

	}
	
	public function remove_command_parameter($device_command_parameter_id)
	{

		Zend_Registry::get('database')->delete_single_row($device_command_parameter_id, 'device_command_parameters', 'devicecommand', 'remove_command_parameter');

	}
	
	public function set_command_api($api_id)
	{

		Zend_Registry::get('database')->set_single_attribute($this->_device_command_id, 'device_commands', 'api_id', $api_id, 'devicecommand', 'set_command_api');
	}
	
	public function set_command_parameter($device_command_parameter_name, $device_command_parameter_column_id, $device_command_parameter_id=null)
	{

		//verify that this parameter is not already mapped to this command, we dont care about the column, but the name must be unique.
		$sql =	"SELECT COUNT(device_command_parameter_id) FROM device_command_parameters
				WHERE device_command_id='".$this->_device_command_id."'
				AND device_command_parameter_name='".$device_command_parameter_name."'
				";
		
		$map_exists = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		if ($map_exists == 0) {
			
			//lets create a new parameter
			$data = array(

								'device_command_id'						=>  $this->_device_command_id,
								'device_command_parameter_name'			=>  $device_command_parameter_name,
								'device_command_parameter_column_id'	=>  $device_command_parameter_column_id,
			);
			
			//if we create a new row because the name changed make sure to remove the original

			if ($device_command_parameter_id != null) {
			
				Zend_Registry::get('database')->delete_single_row($device_command_parameter_id, 'device_command_parameters', 'devicecommand', 'set_command_parameter');
			
			}
			
		return Zend_Registry::get('database')->insert_single_row('device_command_parameters',$data,'devicecommand','set_command_parameter');
		
		} elseif ($map_exists == 1) {
			
			$sql =	"SELECT device_command_parameter_id FROM device_command_parameters
								WHERE device_command_id='".$this->_device_command_id."'
								AND device_command_parameter_name='".$device_command_parameter_name."'
								";
				
			$db_device_command_parameter_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);

			//update existing row
			Zend_Registry::get('database')->set_single_attribute($db_device_command_parameter_id, 'device_command_parameters', 'device_command_parameter_name', $device_command_parameter_name, 'devicecommand', 'set_command_parameter');
			Zend_Registry::get('database')->set_single_attribute($db_device_command_parameter_id, 'device_command_parameters', 'device_command_parameter_column_id', $device_command_parameter_column_id, 'devicecommand', 'set_command_parameter');

			//if we update a different row because the name now matches make sure to remove the original
			if ($db_device_command_parameter_id != $device_command_parameter_id && $device_command_parameter_id != null ) {
			
				Zend_Registry::get('database')->delete_single_row($device_command_parameter_id, 'device_command_parameters', 'devicecommand', 'set_command_parameter');
			
			}
			
			return true;
			
		} else {
		//we have more than a single map on the same variable to the same command this should never be possible

			throw new exception('class = devicecommand, method = set_command_parameter, we have more than a single match for the device_parameter_name mapping');
		
		}
 
	}
		
	public function set_command_exe_order($device_command_map_id, $command_exe_order)
	{
			Zend_Registry::get('database')->set_single_attribute($device_command_map_id, 'device_command_mapping', 'command_exe_order', $command_exe_order, 'devicecommand', 'set_command_exe_order');
			return true;
	}
	
	public function remove_command_to_device_function_map($device_command_map_id)
	{
		
		//consider expanding this method so it removes all cmd parameters, and base command if this is the last map in the database.
		Zend_Registry::get('database')->delete_single_row($device_command_map_id, 'device_command_mapping', 'devicecommand', 'remove_command_to_device_function_map');
		return true;
	}
	
	public function map_command_to_device_function($device_function_map_id, $command_exe_order)
	{
		//verify that this command is not already mapped to this function map with the same priority.
		$sql =	"SELECT COUNT(device_command_map_id) FROM device_command_mapping
					WHERE device_command_id='".$this->_device_command_id."'
					AND command_exe_order='".$command_exe_order."'
					AND device_function_map_id='".$device_function_map_id."'
					";
	
		$map_exists = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
	
		if ($map_exists > 0) {
			//return id of existing row
			$sql2 = 	"SELECT device_command_map_id FROM device_command_mapping
							WHERE device_command_id='".$this->_device_command_id."'
							AND command_exe_order='".$command_exe_order."'
							AND device_function_map_id='".$device_function_map_id."'
							LIMIT 1
							";
	
			return Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
	
		} else {
			//insert new row and return the id
	
			$data = array(
	
									'device_function_map_id'		=>  $device_function_map_id,
									'device_command_id'				=>  $this->_device_command_id,
									'command_exe_order'				=>  $command_exe_order,
			);
	
			return Zend_Registry::get('database')->insert_single_row('device_command_mapping',$data,'devicecommand','map_device_function_to_commands_mapping');
	
		}
	}
	
	public function map_new_regex_to_device_command($command_regex_id)
	{

		$command_regex_obj = new Thelist_Model_commandregex($command_regex_id);
		$command_regex_map_id = $command_regex_obj->map_regex_to_device_command($this->_device_command_id);
		return $command_regex_map_id;
		
	}
	
	public function set_commandregex_parameter($command_regex_id, $command_regex_parameter_name, $device_command_parameter_column_id, $command_regex_parameter_id=null)
	{

		$command_regex_obj = new Thelist_Model_commandregex($command_regex_id);
		$command_regex_return = $command_regex_obj->set_regex_parameter($command_regex_parameter_name, $device_command_parameter_column_id, $command_regex_parameter_id);
	
	}
	
	public function remove_commandregex_parameter($command_regex_id, $command_regex_parameter_id)
	{

		$command_regex_obj = new Thelist_Model_commandregex($command_regex_id);
		$command_regex_return = $command_regex_obj->remove_regex_parameter($command_regex_parameter_id);

	}
	
}
?>