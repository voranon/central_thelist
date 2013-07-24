<?php
 //exception codes 21800-21899
class thelist_utility_savefiletoserver
{
	private $_file_name;
	private $_replace_file;
	
	function __construct($file_name, $replace_file=false)
	{
		if (preg_match("/ /", $file_name)) {
			throw new exception("no spaces allowed in uploaded file names: '".$file_name."'", 21804);
		}
		
		$this->_file_name		= $file_name;
		$this->_replace_file	= $replace_file;
   	}
   	
   	public function create_device_config_file_from_content($device_type, $content)
   	{

   		if ($device_type == 'routeros') {
   			
   			if (!preg_match("/\.rsc$/", $this->_file_name)) {
   				throw new exception("routeros device config files must end with .rsc, yours does not '".$this->_file_name."' ", 21801);
   			}
   			
   			$filepath = APPLICATION_PATH."/configs/device_configs/routeros";
   			
   		} elseif ($device_type == 'bairos') {
   			$filepath = APPLICATION_PATH."/configs/device_configs/bairos";
   		} elseif ($device_type == 'cisco') {
   			$filepath = APPLICATION_PATH."/configs/device_configs/cisco";
   		} else {
   			throw new exception("we do not know how to save a file for device type '".$device_type."' ", 21800);
   		}

   		//check if file exists
   		$full_path = "".$filepath."/".$this->_file_name."";

   		//does file already exist?
   		if (file_exists($full_path) && $this->_replace_file === false) {
   			throw new exception("file '".$full_path."', already exists and you are not overriding", 21802);
   		} else {
   			
   			$result_of_upload = file_put_contents($full_path, $content);
   			
   			//should return number of bytes written or warning
   			if (!is_numeric($result_of_upload)) {
   				throw new exception("we could not save file: '".$full_path."', most likely a permissions issue ", 21803);
   			}
   		}
   	}
}
?>