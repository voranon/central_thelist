<?php

//by martin
//exception codes 7100-7199


class thelist_utility_customerrorhandler
{
	private $_class_name=null;
	private $_method_name=null;
	private $_errno=null;
	private $_errstr=null;
	private $_errfile=null;
	private $_errline=null;
	
	public function device_error_handler($errno, $errstr, $errfile, $errline)
	{
		$this->_errno		= $errno;
		$this->_errstr		= $errstr;
		$this->_errfile		= $errfile;
		$this->_errline		= $errline;
		
		//to make it easy to make the solution we append the class and method name to error string
		if ($this->_class_name != null && $this->_method_name != null) {
			$errstr .= " in class: '".$this->_class_name."' and method: '".$this->_method_name."' original_error_no: '".$errno."' ";
		}

		throw new exception($errstr, $this->get_error_exception_code($errno));
	}
	
	public function set_error_class_method($class, $method)
	{
		$this->_class_name	= $class;
		$this->_method_name	= $method;
	}
	
	
	private function get_error_exception_code($errno)
	{
		//here we recast the error codes as exceptions
		//many of the outside classes we use will use have error numbers that overlap i.e. ssh and telnet, both COULD be
		//using error code no 2 to let us know the authentication failed
		//to make sure the code is unique we recast the code to one that is unique

		if ($this->_class_name != null && $this->_method_name != null) {

			if ($this->_class_name == 'thelist_utility_sshconnector') {
					
				if ($this->_method_name	== 'get_phpseclib_connection') {
						
					if ($errno == 2) {
						
						if (preg_match("/No route to host/", $this->_errstr)) {
							return 7101;
						} elseif (preg_match("/Connection timed out/", $this->_errstr)) {
							return 7102;
						}
					} elseif ($errno == 1024) {
						
						if (preg_match("/Connection closed prematurely/", $this->_errstr)) {
							return 7108;
						}
					}
					
				} elseif ($this->_method_name	== 'execute_cmd') {
			
					if ($errno == 1024) {
						
						if (preg_match("/Connection closed prematurely/", $this->_errstr)) {
							return 7103;
						}
					} elseif ($errno == 2) {
						
						if (preg_match("/Unable to request a channel from remote host/", $this->_errstr)) {
							return 7106;
						}
					}
				}
				
			} elseif ($this->_class_name == 'thelist_utility_telnetconnector') {
					
				if ($this->_method_name	== 'get_cisco_connection') {
						
					if ($errno == 2) {
						
						if (preg_match("/No route to host/", $this->_errstr)) {
							return 7104;
						} elseif (preg_match("/Connection timed out/", $this->_errstr)) {
							return 7105;
						}
					} elseif ($errno == 8) {
						
						if (preg_match("/failed with errno=32 Broken pipe/", $this->_errstr)) {
							return 7107;
						}
					}
				}
				
			} elseif ($this->_class_name == 'thelist_model_devicerouteros') {
					
				if ($this->_method_name	== 'execute_command') {
			
					if ($errno == 1024) {
						
						if (preg_match("/Connection closed prematurely/", $this->_errstr)) {
							return 7103;
						}
					}
					
				} elseif ($this->_method_name	== 'upload_file') {
			
					if ($errno == 2) {
						
						if (preg_match("/No such file or directory/", $this->_errstr)) {
							return 7110;
						} elseif (preg_match("/Failed copying file/", $this->_errstr)) {
							return 7111;
						}
					}
				}
				
			} elseif ($this->_class_name == 'thelist_model_devicelinuxserver') {
					
				if ($this->_method_name	== 'upload_file') {
			
					if ($errno == 1024) {
						
						//the local file is not valid
						if (preg_match("/is not a valid file/", $this->_errstr)) {
							return 7109;
						}
					}
				}
			}
		}

		//if this custom error has not been caught before, lets get that done
		$tt_task = new Thelist_Utility_troubletaskcreator('Engineering', 'Custom error not recast as our Exception id', 'Low');
		$task_obj	= $tt_task->create_task();
		$task_obj->add_note(" Class:".$this->_class_name." Method:".$this->_method_name." Error NO:".$this->_errno." Error String:".$this->_errstr." Error File:".$this->_errfile." Error Line:".$this->_errline." ");
			
		//if we dident match then we return 0
		return 0;
	}
}
?>