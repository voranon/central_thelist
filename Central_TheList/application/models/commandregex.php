<?php
//by martin
class thelist_model_commandregex
{
	//common		
	private $database;
	private $logs;
	
	//for this class
	private $_command_regex_id;
	private $_base_regex;
	private $_command_regex_desc;
	private $_command_regex_parameters;
	private $_command_regex_match;
	private $_command_regex_replace;
	
	//only set if mapped to a device	
	private $_command_regex_map_id;
				
	public function __construct($command_regex_id)
	{
		$this->_command_regex_id = $command_regex_id;
		

		$this->logs			= Zend_Registry::get('logs');
		
		//get the command regex from the database
		$sql = "SELECT * FROM command_regexs
				WHERE command_regex_id='".$this->_command_regex_id."'
				";
		
		$command_regex = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		$this->_base_regex 						= $command_regex['base_regex'];
		$this->_command_regex_desc 				= $command_regex['command_regex_desc'];
		$this->_command_regex_match				= $command_regex['match_yes_or_no'];
		$this->_command_regex_replace			= $command_regex['replacement_regex'];
		
		//get all the parameters used in the base command
		$sql2 = "SELECT crp.command_regex_parameter_id, crp.command_regex_parameter_name, dcpc.device_command_parameter_column_id, dcpc.column_name, dcpt.device_command_parameter_table_id, dcpt.table_name FROM command_regex_parameters crp
				INNER JOIN device_command_parameter_columns dcpc ON dcpc.device_command_parameter_column_id=crp.device_command_parameter_column_id
				INNER JOIN device_command_parameter_tables dcpt ON dcpt.device_command_parameter_table_id=dcpc.device_command_parameter_table_id
				WHERE crp.command_regex_id='".$this->_command_regex_id."'
				";
		
		$this->_command_regex_parameters = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
		
	}
	
	public function get_command_regex_id() {
		return $this->_command_regex_id;
	}
	public function get_command_regex_match() {
		return $this->_command_regex_match;
	}
	public function get_command_regex_replace() {
		return $this->_command_regex_replace;
	}
	public function get_base_regex() {
		return $this->_base_regex;
	}
	public function get_command_regex_desc() {
		return $this->_command_regex_desc;
	}
	public function get_command_regex_parameters() {
		return $this->_command_regex_parameters;
	}
	public function set_command_regex_map_id($command_regex_map_id) {
		$this->_command_regex_map_id = $command_regex_map_id;
	}
	public function get_command_regex_map_id() {
		return $this->_command_regex_map_id;
	}
	
	public function set_base_regex($base_regex)
	{
		Zend_Registry::get('database')->set_single_attribute($this->_command_regex_id, 'command_regexs', 'base_regex', $base_regex, 'commandregex', 'set_base_regex');
		$this->_base_regex = $base_regex;
	}
	
	public function set_command_regex_match($match_yes_or_no)
	{

		Zend_Registry::get('database')->set_single_attribute($this->_command_regex_id, 'command_regexs', 'match_yes_or_no', $match_yes_or_no, 'commandregex', 'set_command_regex_match');
		$this->_command_regex_match = $match_yes_or_no;

	}
	
	public function set_command_regex_replace($replace_yes_or_no)
	{
		Zend_Registry::get('database')->set_single_attribute($this->_command_regex_id, 'command_regexs', 'replacement_regex', $replace_yes_or_no, 'commandregex', 'set_command_regex_match');
		$this->_command_regex_replace = $replace_yes_or_no;
	}
	
	public function remove_regex_to_device_command_map($command_regex_map_id)
	{

		//consider expanding this method so it removes all regex parameters, and base regex if this is the last map in the database.
		Zend_Registry::get('database')->delete_single_row($command_regex_map_id, 'command_regex_mapping', 'commandregex', 'remove_regex_to_device_command_map');

		return true;
	}
	
	public function map_regex_to_device_command($device_command_id)
	{
		//verify that this command is not already mapped to this function map with the same priority.
		$sql =	"SELECT COUNT(command_regex_map_id) FROM command_regex_mapping
				WHERE command_regex_id='".$this->_command_regex_id."'
				AND device_command_id='".$device_command_id."'
				";
		
		$map_exists = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		
	
		if ($map_exists > 0) {
			//return id of existing row
			$sql2 = 	"SELECT command_regex_map_id FROM command_regex_mapping
						WHERE command_regex_id='".$this->_command_regex_id."'
						AND device_command_id='".$device_command_id."'
						LIMIT 1
						";
	
			return Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
	
		} else {
			//insert new row and return the id
	
			$data = array(

											'command_regex_id'				=>  $this->_command_regex_id,
											'device_command_id'				=>  $device_command_id,
			);
	
			return Zend_Registry::get('database')->insert_single_row('command_regex_mapping',$data,'commandregex','map_regex_to_device_command');
	
		}
	}
	
	public function set_regex_parameter($command_regex_parameter_name, $device_command_parameter_column_id, $command_regex_parameter_id=null)
	{

		//verify that this parameter is not already mapped to this regex, we dont care about the column, but the name must be unique.
		$sql =	"SELECT COUNT(command_regex_parameter_id) FROM command_regex_parameters
				WHERE command_regex_id='".$this->_command_regex_id."'
				AND command_regex_parameter_name='".$command_regex_parameter_name."'
				";
			
		$map_exists = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
		if ($map_exists == 0) {

			//lets create a new parameter
			$data = array(

									'command_regex_id'						=>  $this->_command_regex_id,
									'command_regex_parameter_name'			=>  $command_regex_parameter_name,
									'device_command_parameter_column_id'	=>  $device_command_parameter_column_id,
			);

			//if we create a new row because the name changed make sure to remove the original

			if ($command_regex_parameter_id != null) {

				Zend_Registry::get('database')->delete_single_row($command_regex_parameter_id, 'command_regex_parameters', 'commandregex', 'set_regex_parameter');

			}

			return Zend_Registry::get('database')->insert_single_row('command_regex_parameters',$data,'commandregex','set_regex_parameter');
				
		} elseif ($map_exists == 1) {

			$sql =	"SELECT command_regex_parameter_id FROM command_regex_parameters
					WHERE command_regex_id='".$this->_command_regex_id."'
					AND command_regex_parameter_name='".$command_regex_parameter_name."'
					";
				
			$db_command_regex_parameter_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);

			//update existing row
			Zend_Registry::get('database')->set_single_attribute($db_command_regex_parameter_id, 'command_regex_parameters', 'command_regex_parameter_name', $command_regex_parameter_name, 'commandregex', 'set_regex_parameter');
			Zend_Registry::get('database')->set_single_attribute($db_command_regex_parameter_id, 'command_regex_parameters', 'device_command_parameter_column_id', $device_command_parameter_column_id, 'commandregex', 'set_regex_parameter');

			//if we update a different row because the name now matches make sure to remove the original
			if ($db_command_regex_parameter_id != $command_regex_parameter_id && $command_regex_parameter_id != null ) {

				Zend_Registry::get('database')->delete_single_row($command_regex_parameter_id, 'command_regex_parameters', 'commandregex', 'set_regex_parameter');

			}

			return true;

		} else {
			//we have more than a single map on the same variable to the same command this should never be possible

			throw new exception('class = commandregex, method = set_regex_parameter, we have more than a single match for the regex_parameter_name mapping');
				
		}
	}
	
	public function remove_regex_parameter($command_regex_parameter_id)
	{
		Zend_Registry::get('database')->delete_single_row($command_regex_parameter_id, 'command_regex_parameters', 'commandregex', 'remove_regex_parameter');

	}
	
	
	
	
		
		
}
?>	