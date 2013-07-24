<?php 
////exception codes 9000-9099

class thelist_model_devicelinuxserver implements Thelist_Commander_pattern_interface_idevice
{

			private $_connection;
			private $_fqdn;
			private $_auth_obj;

			public function __construct($fqdn, $device_authentication_credentials)
			{
				$this->_fqdn = $fqdn;
				$this->_auth_obj = $device_authentication_credentials;
				
				if ($this->_auth_obj->get_device_api_name() == 'ssh') {
					
					$this->_connection = new Thelist_Utility_sshconnector($this->_fqdn, $this->_auth_obj->get_device_username(), $this->_auth_obj->get_device_password());
					
					$cmd_return = $this->execute_command('uname -a');
					
					if (preg_match("/(Linux)/", $cmd_return->get_message(), $matches) == false) {

						throw new exception('linux server class: device not running linux', 9000);

					}
					
				} elseif ($this->_auth_obj->get_device_api_name() == 'sftp') {
					
					$this->_connection = new Thelist_Utility_sftpconnector($this->_fqdn, $this->_auth_obj->get_device_username(), $this->_auth_obj->get_device_password());
					
				} else {

					throw new exception('bairos class cannot connect using the given API');

				}
			}
			
			public function execute_command($cmd)
			{
				set_error_handler(array(new Thelist_Utility_customerrorhandler(), 'device_error_handler'));
	
				$devicereply_obj = $this->_connection->execute_cmd($cmd);
				//code '1' is success
				$devicereply_obj->set_code('1');
				
				restore_error_handler();
			
				return $devicereply_obj;
				
			}
			
			public function get_device_type()
			{
			
				return 'linuxserver';
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
			
}
?>