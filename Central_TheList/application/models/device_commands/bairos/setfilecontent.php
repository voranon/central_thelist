<?php

//exception codes 15400-15499

class thelist_bairos_command_setfilecontent implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_file_path;
	private $_file_content;
	private $_action;
	
	public function __construct($device, $file_path, $file_content, $action)
	{
		//action can be override or append
		$this->_device 						= $device;
		$this->_file_path 					= $file_path;
		$this->_file_content 				= $file_content;
		$this->_action 						= $action;
	}

	public function execute()
	{
		
		if (preg_match("/^\//", $this->_file_path)) {
			
			if (!preg_match("/\/$/", $this->_file_path)) {
				
				//check if file exists
				$device_reply = $this->_device->execute_command("ls ".$this->_file_path."");
					
				//file does not exist, lets find out how much of the path is missing
				if (preg_match("/No such file or directory/", $device_reply->get_message())) {
				
					$directories = explode("/", $this->_file_path);
				
					$nested_depth	= count($directories);
					$file_index 	= $nested_depth - 1;
				
					if ($nested_depth > 1) {

						if ($nested_depth == 2) {
							//this is a file in the root folder so we go straight to creating it	
							$this->put_file_content();
						} else {
							
							$current_path = "/";
							
							$i=0;
							foreach ($directories as $directory) {
								
								//first item in the array is empty
								if ($i > 0) {
									
									if ($i == $file_index) {
										
										//we are at the file location
										//create the file
										$this->put_file_content();
										
									} else {

										$current_path .= $directory . "/";
										$device_reply1 = $this->_device->execute_command("ls ".$current_path."");
										
										if (preg_match("/No such file or directory/", $device_reply1->get_message())) {
											
											//if it does not exist then create the directory
											//could be done faster with mkdir -p path
											$this->_device->execute_command("mkdir ".$current_path."");
										}
									}
								}
								
								$i++;
							}
						}	
							
					} else {
						throw new exception("you have not supplied a file name", 15402);
					}
				} else {
					//file exists push the content
					$this->put_file_content();
				}
				
			} else {
				throw new exception("file paths cannot end with a slash", 15401);
			}

		} else {
			throw new exception("file paths must be absolute", 15400);
		}
	}
	
	
	private function put_file_content()
	{
		if ($this->_action == 'override') {
			
			$device_reply = $this->_device->execute_command("echo '".$this->_file_content."' > ".$this->_file_path."");
			
		} elseif ($this->_action == 'append') {
			
			$device_reply = $this->_device->execute_command("echo '".$this->_file_content."' >> ".$this->_file_path."");
			
		} else {
			throw new exception("action: ".$this->_action." is unknown", 15404);
		}
	}
}
	