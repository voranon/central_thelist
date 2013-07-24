<?php

//exception codes 17900-17999 

class thelist_bairos_command_removefile implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_file_path;
	
	public function __construct($device, $file_path)
	{
		//action can be override or append
		$this->_device 						= $device;
		$this->_file_path 					= $file_path;
	}

	public function execute()
	{
		
		if (preg_match("/^\//", $this->_file_path)) {
			
			if (!preg_match("/\/$/", $this->_file_path)) {
				
				//turn path into array
				$directories = explode("/", $this->_file_path);
				
				$nested_depth	= count($directories);
				$file_index 	= $nested_depth - 1;

					
				$path = '/';
				
				$i=0;
				foreach ($directories as $directory) {
					
					if ($i != $file_index) {
						//dont include file in path
						$path .= $directory . "/";
					}
					$i++;
				}
				
				$file_list 		= new Thelist_Bairos_command_getfilelist($this->_device, $path);
				$file_exist 	= $file_list->get_file($directories[$file_index], true);
					
				if ($file_exist != false) {
					$device_reply = $this->_device->execute_command("rm -rf ".$this->_file_path."");
				} else {
					//it does not exist we are done
					//make validation that checks directories too and throws error if calls is being used to remove directory
					return;
				}
				
				
				//validate that the file was removed
				$validate 	= $file_list->get_file($directories[$file_index], true);
				
				if ($validate != false) {
					throw new exception("we tried to remove file: '".$directories[$file_index]."' from device and failed ", 17902);
				}
				
			} else {
				throw new exception("file paths cannot end with a slash", 17900);
			}

		} else {
			throw new exception("file paths must be absolute", 17901);
		}
	}
}
	