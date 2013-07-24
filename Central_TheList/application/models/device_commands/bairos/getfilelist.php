<?php

//exception codes 13200-13299

class thelist_bairos_command_getfilelist implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_folder_path=null;
	
	private $_files=null;
	
	public function __construct($device, $folder_path)
	{
		$this->_device 						= $device;
		$this->_folder_path 				= $folder_path;
	}

	public function execute()
	{
		//reset
		$this->_files = null;
		
		//get the list of files
		$device_reply = $this->_device->execute_command("ls -ashl1 --full-time ".$this->_folder_path."");

		//expand this class to also return directories
		preg_match_all("/([0-9]+|[0-9]+\.[0-9]+)(K|M|G|T|P|E)? +(.{10}) +([0-9]+) +(\w*) +(\w*) +([0-9]+|[0-9]+\.[0-9]+)(K|M|G|T|P|E)? +([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}).* +(-[0-9]{4}) +(.*)/", $device_reply->get_message(), $files_raw);

		$return_array 	= array();
		$time 			= new Thelist_Utility_time();
		$multiplier		= new Thelist_Utility_multiplierconverter();
		
		if (isset($files_raw['0']['0'])) {

			foreach ($files_raw['3'] as $index => $access_credential) {
				
				if ($files_raw['11'][$index] != '.' && $files_raw['11'][$index] != '..') {
					if ($files_raw['8'][$index] != null) {
						$file_size = $multiplier->convert_to_bytes($files_raw['7'][$index], $files_raw['8'][$index]);
					} else {
						$file_size = $files_raw['7'][$index];
					}
					
					if (preg_match("/^l/", $files_raw['3'][$index])) {
						$type = 'symlink';
					} elseif (preg_match("/^d/", $files_raw['3'][$index])) {
						$type = 'directory';
					} else {
						$type = 'file';
					}
					
					if (preg_match("/(.*) -> (.*)/", $files_raw['11'][$index], $result11)) {
						$file_name = $result11['1'];
					} else {
						$file_name = $files_raw['11'][$index];
					}
					
					if ($type == 'file') {
						$return_array['files'][$index]['file_name'] 						= $file_name;
						$return_array['files'][$index]['bytes'] 							= $file_size;
						$return_array['files'][$index]['last modification_datetime'] 		= $time->convert_string_to_mysql_datetime($files_raw['9'][$index]);
						
					} elseif ($type == 'directory') {

						//not complete
						//$return_array['directories'][$index]['directory_name'] 					= $file_name;
					
					} elseif ($type == 'symlink') {

						//not complete
						//$return_array['symlinks'][$index]['file_name'] 						= $file_name;
						//$return_array['symlinks'][$index]['bytes'] 							= $file_size;
						//$return_array['symlinks'][$index]['last modification_datetime'] 	= $time->convert_string_to_mysql_datetime($files_raw['9'][$index]);
					}
				}
			}
		}

		if (isset($return_array)) {
			
			//reset the array values
			if (isset($return_array['files'])) {
				$this->_files['files'] 			= array_values($return_array['files']);
			}
			
			if (isset($return_array['directories'])) {
				$this->_files['directories'] 	= array_values($return_array['directories']);
			}
			
			if (isset($return_array['symlinks'])) {
				$this->_files['symlinks'] 		= array_values($return_array['symlinks']);
			}
			
		} else {
			$this->_files = null;
		}
	}
	
	public function get_file($file_name, $refresh=true)
	{
		if($this->_files == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		
		if ($this->_files != null) {
		
			foreach ($this->_files['files'] as $file) {
					
				//does the file exist?
				if ($file['file_name'] == $file_name) {
		
					return $file;
				}
			}
		}
		
		return false;
	}
	
	public function get_directory($directory_name, $refresh=true)
	{
		//see above, not ready
		if($this->_files == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
	
		if ($this->_files != null) {
	
			foreach ($this->_files['directories'] as $directory) {
					
				//does the file exist?
				if ($directory['directory_name'] == $directory_name) {
	
					return $directory;
				}
			}
		}
	
		return false;
	}
	
	public function get_files($refresh=true)
	{
		if($this->_files == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		
		return $this->_files;
	}
}
	