<?php

//exception codes 9100-9199

class thelist_routeros_command_getfilelist implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_folder_path=null;
	
	private $_files=null;
	
	public function __construct($device, $folder_path=null)
	{
		//currently mikrotik does not support a folder structure, but once they do,
		//we can start using the path
		$this->_device 						= $device;
		$this->_folder_path 				= $folder_path;
	}

	public function execute()
	{
		//get the list of files

		$device_reply = $this->_device->execute_command("/file print terse");

		
		//expand this class to also return directories
		preg_match_all("/name=(.*) type=(.*) size=(.*) creation-time=(.*) ([0-9]{2}:[0-9]{2}:[0-9]{2})/", $device_reply->get_message(), $files_raw);

		$return_array = array();
		$time = new Thelist_Utility_time();
		
		if (isset($files_raw['0']['0'])) {
			
			
			foreach ($files_raw['1'] as $index => $file_name) {
				
				$file_size = str_replace(" ", "", $files_raw['3'][$index]);
				$date_time = $time->convert_string_month_to_number($files_raw['4'][$index]) . " " . $files_raw['5'][$index];

				$return_array['files'][$index]['file_name'] 		= $file_name;
				$return_array['files'][$index]['bytes'] 			= $file_size;
				$return_array['files'][$index]['created_datetime'] 	= $time->convert_string_to_mysql_datetime($date_time);
			}
		}
		
		if (isset($return_array)) {
			$this->_files = $return_array;
			
			return $this->_files;
		} else {
			return false;
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

		if ($this->_files != null) {
			return $this->_files;
		}
	
		return false;
	}
}
	