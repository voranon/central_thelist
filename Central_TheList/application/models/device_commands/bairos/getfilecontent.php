<?php

//exception codes 14500-14599

class thelist_bairos_command_getfilecontent implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_file_path=null;
	private $_patterns=null;
	private $_content_array=null;
	private $_content=null;
	
	public function __construct($device, $file_path, $patterns=null)
	{
		//$file_path
		//both models = string with file path
		
		//$patterns
		//both models = ['patterns'] of patterns to match
		//both models = ['regexes'] of regex to match to match

		$this->_device 						= $device;
		$this->_file_path 					= $file_path;
		$this->_patterns 					= $patterns;
	}

	public function execute()
	{
		//set the variable
		$content 			= '';
		$content_in_array 	= array();

		if ($this->_patterns == null) {
		
			$device_reply = $this->_device->execute_command("cat ".$this->_file_path."");
			$content_in_array = explode("\n", $device_reply->get_message());
			$this->_content = $device_reply->get_message();
		
		} elseif (is_array($this->_patterns)) {
			
			if (isset($this->_patterns['patterns'])) {
				
				$this->_content = '';
				//let the bairos do some of the work by running grep on the commandline
				foreach ($this->_patterns['patterns'] as $pattern) {
					
					$command	= "cat ".$this->_file_path." | grep \"".$pattern."\"";
					$device_reply = $this->_device->execute_command($command);
					
					//append the content
					$this->_content .= $device_reply->get_message();

					//make sure the file exists
					if (preg_match("/cat:.*: No such file or directory/", $device_reply->get_message())) {
						throw new exception("the requested file does not exist", 14500);
					}
					
					$content .= $device_reply->get_message();
				}
				
				$content_in_array = explode("\n", $content);
			}
			
			if (isset($this->_patterns['regexes'])) {

				//regex requires entire content
				$device_reply 		= $this->_device->execute_command("cat ".$this->_file_path."");
				
				if ($this->_content == null) {
					$this->_content 	= $device_reply->get_message();
				} else {
					$this->_content 	.= $device_reply->get_message();
				}
				
				
				//make sure the file exists
				if (preg_match("/No such file or directory/", $device_reply->get_message())) {
					throw new exception("the requested file does not exist", 14500);
				}
				
				foreach ($this->_patterns['regexes'] as $regex) {

					preg_match_all("/".$regex."/", $device_reply->get_message(), $results);
					
					if (isset($results['0']['0'])) {
						$content_in_array = array_merge($content_in_array, $results['0']);
					}
				}				
			}
		}

		if (isset($content_in_array)) {
			
			if (count($content_in_array) == 0) {
				$this->_content_array 	= null;
			} elseif ($content_in_array['0'] == '' && !isset($content_in_array['1'])) {
				//many times we get a single empty line, that is a null result
				$this->_content_array 	= null;
			} else {
				//if there is more than a single empty line
				$this->_content_array 	= $content_in_array;
			}
			
		} else {
			$this->_content_array 	= null;
		
		}
	}
	
	public function get_content_array($refresh=true)
	{
		if($this->_content_array == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		
		return $this->_content_array;
	}
	
	public function get_content($refresh=true)
	{
		if($this->_content == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
	
		return $this->_content;
	}
}
	