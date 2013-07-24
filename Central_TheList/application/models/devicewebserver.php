<?php 

//by martin

class thelist_model_devicewebserver implements Thelist_Commander_pattern_interface_idevice
{

			private $_connection;
			private $_fqdn;
			private $_auth_obj;

			public function __construct($fqdn, $device_authentication_credentials)
			{
		
				$this->_fqdn = $fqdn;
				$this->_auth_obj = $device_authentication_credentials;

				if ($this->_auth_obj->get_device_api_name() == 'http') {
					
					$this->_connection = new Thelist_Utility_httpconnector($this->_fqdn, '80', null, null, null);

				}
			}
			
			public function execute_command($cmd)
			{
				try {
						
					$devicereply_obj = $this->_connection->execute_cmd($cmd);
					
					//code '1' is success
					$devicereply_obj->set_code('1');
				
					return $devicereply_obj;
					
				} catch (Zend_Http_Client_Adapter_Exception $e) {
				
					switch($e->getCode()) {
				
						case 0;
						//111, connection refused
						$devicereply_obj	= new Thelist_Model_devicereply($cmd, 'connection refused');
						$devicereply_obj->set_code('2');
						break;
						default;
						throw $e;
					}
				}
			}
			
			public function get_device_type(){
			
				return 'webserver';
			}
			
}
?>