<?php 

//by martin

class thelist_model_devicecisco implements Thelist_Commander_pattern_interface_idevice
{

		private $_connection;
		private $_fqdn;
		private $_auth_obj;

		public function __construct($fqdn, $device_authentication_credentials)
		{
	
			$this->_fqdn = $fqdn;
			$this->_auth_obj = $device_authentication_credentials;
			
			if ($this->_auth_obj->get_device_api_name() == 'telnet') {
				
				$this->_connection = new Thelist_Utility_telnetconnector($this->_fqdn, null, $this->_auth_obj->get_device_password(), $this->_auth_obj->get_device_enablepassword(), 'cisco');
				
				$cmd_return = $this->execute_command('show version');
					
				if (preg_match("/(Cisco)/", $cmd_return->get_message(), $matches) == false) {
						
					//throw new exception('cisco class: device not running IOS', 3);
						
				}
				
			} else if ($this->_auth_obj->get_device_api_name() == 'ssh') {
				
				$this->_connection = new Thelist_Utility_sshconnector($this->_fqdn, $this->_auth_obj->get_device_username(), $this->_auth_obj->get_device_password());
				$this->_connection->execute_command('enable');
				
				$cmd_return = $this->execute_command('show version');
				
				if (preg_match("/(Cisco)/", $cmd_return, $matches) == false) {
				
					throw new exception('cisco class: device not running IOS', 3);
						
				}
				
			} else {
				
				throw new exception('cisco class cannot connect using the given API');
				
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
		
			return 'cisco';
		}
			
}
?>