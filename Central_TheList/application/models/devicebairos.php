<?php 
//by martin

class thelist_model_devicebairos implements Thelist_Commander_pattern_interface_idevice
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
					
					$cmd_return = $this->execute_command('lspci -vb | grep "Micro-Star International"');
					
					if (preg_match("/(Micro-Star International)/", $cmd_return->get_message(), $matches) == false) {

						throw new exception('bairos class: device not running bairos', 2);

					}
					
				} else if ($this->_auth_obj->get_device_api_name() == 'telnet') {
					
					//something else
					
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
			
			public function get_device_type(){
			
				return 'bairos';
			}
			
}
?>