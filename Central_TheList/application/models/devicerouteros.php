<?php 

//by martin
//exception codes 8400-8499

class thelist_model_devicerouteros implements Thelist_Commander_pattern_interface_idevice
{
	private $_connection;
	private $_fqdn;
	private $_auth_obj;

	public function __construct($fqdn, $device_authentication_credentials)
	{
		$this->_fqdn = $fqdn;
		$this->_auth_obj = $device_authentication_credentials;
		
		//if the device is not running routeros then throw exception 8401 every time
		
		
		if ($this->_auth_obj->get_device_api_name() == 'ssh') {

			$this->_connection = new Thelist_Utility_sshconnector($this->_fqdn, $this->_auth_obj->get_device_username(), $this->_auth_obj->get_device_password(), $this->_auth_obj->get_specific_connect_implentation());
			$cmd_return = $this->execute_command('/system routerboard print');
			if (preg_match("/(routerboard)/", $cmd_return->get_message(), $matches) == false) {
				throw new exception('routeros class: device not running routeros', 8401);
			}

		} else if ($this->_auth_obj->get_device_api_name() == 'sftp') {
			$this->_connection = new Thelist_Utility_sftpconnector($this->_fqdn, $this->_auth_obj->get_device_username(), $this->_auth_obj->get_device_password());
		} elseif ($this->_auth_obj->get_device_api_name() == 'scp') {
			$this->_connection = new Thelist_Utility_scpconnector($this->_fqdn, $this->_auth_obj->get_device_username(), $this->_auth_obj->get_device_password(), 'ssh2lib');
		} elseif ($this->_auth_obj->get_device_api_name() == 'mikrotik_api') {
			$this->_connection = new Thelist_Utility_routerosapiconnector($this->_fqdn, $this->_auth_obj->get_device_username(), $this->_auth_obj->get_device_password());
		} elseif ($this->_auth_obj->get_device_api_name() == 'telnet') {

			$this->_connection = new Thelist_Utility_telnetconnector($this->_fqdn, $this->_auth_obj->get_device_username(), $this->_auth_obj->get_device_password(), null, 'routeros');
			$cmd_return = $this->execute_command('/system routerboard print');
			
			echo "\n <pre> 554hr  \n ";
			print_r($cmd_return);
			echo "\n 2222 \n ";
			print_r($cmd_return->get_message());
			echo "\n 3333 \n ";
			print_r($this->_auth_obj->get_device_api_name());
			echo "\n 4444 </pre> \n ";
			die;
			
			 
			if (!preg_match("/(routerboard)/", $cmd_return->get_message(), $matches)) {
				throw new exception('routeros class: device not running routeros', 8401);
			}

		} else {
			throw new exception('routeros class cannot connect using the given API', 8400);
		}
	}
	
	public function upload_file($local_file_path, $remote_file_path)
	{
		//trace errors
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		$class	= get_class($this);
	
		$error_handler		= new Thelist_Utility_customerrorhandler();
		$error_handler->set_error_class_method($class, $method);
	
		//during connect use error handle
		set_error_handler(array($error_handler, 'device_error_handler'));
			
		$devicereply_obj = $this->_connection->upload_file($local_file_path, $remote_file_path);
		//code '1' is success
		$devicereply_obj->set_code('1');
			
		restore_error_handler();
			
		return $devicereply_obj;
			
	}
	
	public function download_file($options)
	{
		//trace errors
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		$class	= get_class($this);
	
		$error_handler		= new Thelist_Utility_customerrorhandler();
		$error_handler->set_error_class_method($class, $method);
	
		//during connect use error handle
		set_error_handler(array($error_handler, 'device_error_handler'));
			
		if (isset($options['url'])) {
			$devicereply_obj = $this->_connection->download_file_via_http($options['url']);
		}

		//code '1' is success
		$devicereply_obj->set_code('1');
			
		restore_error_handler();
			
		return $devicereply_obj;
			
	}
	
	
	public function execute_command($cmd)
	{
		//$track = null;
		$execution_success 	= false;
		$execution_error 	= false;
		$i=0;
		$j=0;
		$k=0;
		while($i < 5 && $execution_success == false && $execution_error == false) {
			$i++;

			try {
				
				//issue the command and append bad syntax so we know the command is complete
				$devicereply_obj = $this->_connection->execute_cmd($cmd ."; /commandcomplete");
				
				//$track[] = $devicereply_obj->get_message();
				
			} catch (Exception $e) {
					
				switch($e->getCode()) {
			
					case 7103;
					//error is caught before the handler can be restored
					//so we restore here
					restore_error_handler();
					
					//earlier versions of routeros have a problem where they will only issue one command per connection
					//so we have to keep renewing the connection
					
					//we reset the normal counter that tries to reissue the command if it is not complete
					$i=0;
					$j++;
					
					if ($j > 20) {
						$execution_error = true;
					}
					
					$this->_connection = new Thelist_Utility_sshconnector($this->_fqdn, $this->_auth_obj->get_device_username(), $this->_auth_obj->get_device_password(), $this->_auth_obj->get_specific_connect_implentation());

					//issue the command and append bad syntax so we know the command is complete
					$devicereply_obj = $this->_connection->execute_cmd($cmd ."; /commandcomplete");

					break;
					default;
					throw $e;
			
				}
			}
			
 			if (preg_match("/commandcomplete/", $devicereply_obj->get_message())) {
 				
 				$execution_success = true; 		
 				
 			//	$track[] = 'trigger success';
 						
 			} elseif (preg_match("/bad command name (.*) \(line [0-9]+ column [0-9]+\)/", $devicereply_obj->get_message())) {
 				
 				//check if the main command because of bad syntax or unknown command
 				$execution_error 	= true;
 				
 			//	$track[] = 'trigger error';
 			}

 			if ($execution_success == false && $execution_error == false) {

 				if ($k == 2) {
 					//we failed 2 times already, try new connection
 					$this->_connection = new Thelist_Utility_sshconnector($this->_fqdn, $this->_auth_obj->get_device_username(), $this->_auth_obj->get_device_password(), $this->_auth_obj->get_specific_connect_implentation());
 				} else {
 					////if we failed wait a bit before trying again. 0.1 wait
 					usleep(100000);
 				}
 				
 				$k++;
	 			
	 		//	$track[] = 'trigger false false';
 			}
		}
		
		
		
		//if ($i == 5) {
			
			
			//throw new exception("error executing command ros, ".$i.", ".$j.", '".$cmd."' ");
// 				echo "\n <pre> error executing command ros  \n ";
// 				print_r($track);
// 				echo "\n 2222 \n ";
// 				print_r($cmd);
// 				echo "\n 3333 \n ";
// 				print_r($i . " / " . $j);
// 				echo "\n 3333 \n ";
// 				print_r($i . " / " . $j);
// 				echo "\n 4444 </pre> \n ";
// 				die;
		//}

		//we dont want to throw exception on a failed command because we want the sourcecode to have a chance to fix the issue based on the response code instead
		//it will be more gracefull this way
		
		//if there are no execution errors we remove the command delimiter and reset the message
		if ($execution_error == false && $execution_success == true) {

			//mikrotik terminal is not wide enough. we have tried appending the username with +tc100w to no avail.
			//so we remove the line breaks here before returning the message
			$array_of_lines 	= explode("\n", $devicereply_obj->get_message());
				
			$return_text = '';
			$r=0;
			foreach($array_of_lines as $line) {
			
				if (!preg_match("/bad command name commandcomplete/", $line)) {
					$r++;
					
					if (preg_match("/\\\\\r$/", $line)) {
						
						//if the line ends in a backslash + linebreak
						$patterns['0'] = '/\\\\\r$/';
						$patterns['1'] = '/^    /';
						$replacements['0'] = '';
						$replacements['1'] = '';
						
						$return_text .= preg_replace($patterns, $replacements, $line);
						
					} else {
						
						//if the line does not end in a backslash + linebreak
						$patterns['0'] = '/^    /';
						$replacements['0'] = '';
						
						$return_text .= preg_replace($patterns, $replacements, $line) . "\n";
					}
					
				}
			}
				
			$devicereply_obj->set_message($return_text);
		}
		
		//attach the amount of tries to execute the command
		$devicereply_obj->append_option('execution_attempts', $i);

		if ($execution_success == true) {
			//code '1' is success
			$devicereply_obj->set_code('1');
		} elseif($execution_success == false && $execution_error == false) {
			//code 2 is incomplete
			$devicereply_obj->set_code('2');
		} elseif ($execution_error == true) {
			//code 3 is error
			$devicereply_obj->set_code('3');
			
		}

		return $devicereply_obj;
	}
	
	public function get_device_type(){
			
		return 'routeros';
	}
	
}
?>