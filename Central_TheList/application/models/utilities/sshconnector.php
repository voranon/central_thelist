<?php
require_once 'Net/SSH2.php';

//exception codes 16600-16699

class thelist_utility_sshconnector
{
	private $_ssh_connection=null;
	private $_ssh_connector_type=null;
	private $_ssh_shell=null;
	
	private $_eq_fqdn;
	private $_eq_username;
	private $_eq_password;
	private $_device_verification_connection;

	
	public function __construct($eq_fqdn, $eq_ssh_username, $eq_ssh_password, $ssh_connector_type=null)
	{	
		$this->_eq_fqdn 				= $eq_fqdn;
		$this->_ssh_username 			= $eq_ssh_username;
		$this->_ssh_password 			= $eq_ssh_password;
		$this->_ssh_connector_type 		= $ssh_connector_type;

		if ($this->_ssh_connector_type == 'ssh2lib') {
			
			$this->get_ssh2lib_connection();
			
		} elseif ($this->_ssh_connector_type == 'ssh2lib_shell') {
			
			//type 2 is a shell connection, its not working right now
			$this->get_ssh2lib_shell_connection();
			
		} elseif ($this->_ssh_connector_type == null) {
			
			//default connector
			$this->get_phpseclib_connection();
		}
	}

	private function get_phpseclib_connection()
	{
		//trace errors
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		$class	= get_class($this);
		
		$error_handler		= new Thelist_Utility_customerrorhandler();
		$error_handler->set_error_class_method($class, $method);
		
		//during connect use error handle
		set_error_handler(array($error_handler, 'device_error_handler'));
		
		if($this->_ssh_connection = new Net_SSH2($this->_eq_fqdn)) {
		
			if (!$this->_ssh_connection->login($this->_ssh_username, $this->_ssh_password)) {
				//we are throwing our own exception, no need for custom error handle
				restore_error_handler();
				throw new exception("authentication failed", 16600);
			}
			
		} else {
			//we are throwing our own exception, no need for custom error handle
			restore_error_handler();
			throw new exception("connection failed", 16601);
		}
		
		restore_error_handler();
	}

	private function get_ssh2lib_connection()
	{

		//trace errors
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		$class	= get_class($this);
	
		$error_handler		= new Thelist_Utility_customerrorhandler();
		$error_handler->set_error_class_method($class, $method);
	
		//during connect use error handle
		set_error_handler(array($error_handler, 'device_error_handler'));
		
		//$ssh_parameters = array ('kex' => 'diffie-hellman-group1-sha1');
		
		$ssh_parameters = array("kex" => "diffie-hellman-group1-sha1",
                            "client_to_server" => array("crypt" => "3des-cbc",
                                                        "comp" => "none"),
                            "server_to_client" => array("crypt" => "aes256-cbc,aes192-cbc,aes128-cbc",
                                                        "comp" => "none")
		);
		
		if($this->_ssh_connection = ssh2_connect($this->_eq_fqdn, 22, $ssh_parameters)) {
		
			if(!ssh2_auth_password($this->_ssh_connection, $this->_ssh_username, $this->_ssh_password)) {
				//we are throwing our own exception, no need for custom error handle
				restore_error_handler();
				throw new exception("authentication failed", 16603);
			}
			
		} else {
			//we are throwing our own exception, no need for custom error handle
			restore_error_handler();
			throw new exception("connection failed", 16604);
		}

		restore_error_handler();
	}
	
	private function get_ssh2lib_shell_connection()
	{
		
		//type 2 is a shell connection, its not working right now
		//BROKEN!!! DO NOT USE
		
		//trace errors
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		$class	= get_class($this);
	
		$error_handler		= new Thelist_Utility_customerrorhandler();
		$error_handler->set_error_class_method($class, $method);
	
		//during connect use error handle
		set_error_handler(array($error_handler, 'device_error_handler'));
		
		
		$ssh_parameters = array("kex" => "diffie-hellman-group1-sha1",
		                            "client_to_server" => array("crypt" => "3des-cbc",
		                                                        "comp" => "none"),
		                            "server_to_client" => array("crypt" => "aes256-cbc,aes192-cbc,aes128-cbc",
		                                                        "comp" => "none")
		);

		
		if($this->_ssh_connection = ssh2_connect($this->_eq_fqdn, 22, $ssh_parameters)) {
				
			if(!ssh2_auth_password($this->_ssh_connection, $this->_ssh_username . "+c80w", $this->_ssh_password)) {

				//we are throwing our own exception, no need for custom error handle
				restore_error_handler();
				throw new Exception('SSH authentication failed', 16605);
			}
			
		} else {
			//we are throwing our own exception, no need for custom error handle
			restore_error_handler();
			throw new Exception('SSH connection failed', 16606);
		}
		
		if (($shell = ssh2_shell($this->_ssh_connection, 'vt102', null, 80, 40, SSH2_TERM_UNIT_CHARS))) {
			$this->_ssh_shell = $shell;
		}
		
		//fwrite($this->_ssh_shell, "\r\n");
		//fwrite($this->_ssh_shell, "\r\n");
	
		restore_error_handler();
	}
	
	//martin
	public function execute_cmd($cmd)
	{
		//trace errors
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		$class	= get_class($this);
		
		$error_handler		= new Thelist_Utility_customerrorhandler();
		$error_handler->set_error_class_method($class, $method);
		
		set_error_handler(array($error_handler, 'device_error_handler'));

		//do not trace exe commands with custom error handler
		//they should be caught in the device classses (routerosdevice, bairosdevice etc)
		
		if ($this->_ssh_connector_type == null) {
			$result = $this->execute_using_phpseclib($cmd);
		} elseif ($this->_ssh_connector_type == 'ssh2lib') {
			$result = $this->execute_using_ssh2lib($cmd);
		} elseif ($this->_ssh_connector_type == 'ssh2lib_shell') {
			$result = $this->execute_using_ssh2lib_shell($cmd);
		} else {
			throw new exception("we dont know what connector to use for ".$this->_eq_fqdn." ", 16607);
		}
		
		restore_error_handler();
		
		return new Thelist_Model_devicereply($cmd, $result);
	}

	private function execute_using_phpseclib($cmd)
	{
		return @$this->_ssh_connection->exec($cmd);
	}

	private function execute_using_ssh2lib($cmd)
	{
		$stream1 = ssh2_exec($this->_ssh_connection, $cmd);
		
		if ($stream1 == true) {
	
			stream_set_blocking($stream1, true);
			$data1 = '';
	
			while($buffer1 = fread($stream1, 4096)) {
				$data1 .= $buffer1;
			}
			
			fclose($stream1);
	
			return $data1;
		}
	}

	private function execute_using_ssh2lib_shell($cmd)
	{
		
		stream_set_blocking($this->_ssh_shell, true);
		// send a command
		
		//sleep(1);
		
		//
		
		// & collect returning data
		$data1 = "";
		
		$i=0;
		while ($i < 130000) {
			
			if ($i == 0) {
				fwrite($this->_ssh_shell, "\r\n");
			} elseif ($i == 1) {
				fwrite($this->_ssh_shell, $cmd . "\r\n");
			}
			
			//$return = fgetc($this->_ssh_shell);
			//$return = stream_get_contents($this->_ssh_shell);
			if (!($return = fread($this->_ssh_shell, 64))) {
				$data1 .= $return;
			} elseif ($i > 5) {
				echo "\n <pre> internal  \n ";
				print_r($data1);
				echo "\n 2222 \n ";
				print_r($i);
				echo "\n 4444 </pre> \n ";
				die;
			}
			
			//$return = fgets($this->_ssh_shell);
			
			
			
			if ($i == 100) {
				echo "\n <pre> 1111  \n ";
				print_r($cmd);
				echo "\n 2222 \n ";
				print_r($data1);
				echo "\n 3333 \n ";
				//print_r();
				echo "\n 4444 </pre> \n ";
				die;
			}
			
			$i++;
			
		}
		fclose($this->_ssh_shell);
		
		return $data1;
	}

			
// 		} else {
			
// 			$stream1 = ssh2_exec($this->_ssh_connection, $cmd."; thecommandiscompletelydone;");
				
// 			if ($stream1 == true) {
					
// 				stream_set_blocking($stream1, true);
// 				$time_start = time();
// 				$data1 = '';
			
// 				while (true){
			
// 					$data1 .= fread($stream1, 4096);
						
// 					if (strpos($data1,"thecommandiscompletelydone") !== false) {
// 						break;
// 					}
// 					if ((time() - $time_start) > 10 ) {
// 						break;
// 					}
// 				}
			
// 				fclose($stream1);
					
// 				return $data1;
					
// 			}

// 		}
	

}//end of class
?>