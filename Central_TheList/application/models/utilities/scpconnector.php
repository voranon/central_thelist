<?php
require_once 'Net/SSH2.php';

//exception codes 22200-22299

class thelist_utility_scpconnector
{
	private $_scp_connection;
	private $_scp_connector_type=null;
	
	private $_eq_fqdn;
	private $_eq_username;
	private $_eq_password;
	private $_device_verification_connection;

	
	public function __construct($eq_fqdn, $eq_scp_username, $eq_scp_password, $scp_connector_type)
	{	
		$this->_eq_fqdn 		= $eq_fqdn;
		$this->_eq_username 	= $eq_scp_username;
		$this->_eq_password 	= $eq_scp_password;

		if ($scp_connector_type == 'ssh2lib') {
			
			$this->get_ssh2lib_connection();
			$this->_scp_connector_type = 'ssh2lib';
		}
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
		
		$ssh_parameters = array ('kex' => 'diffie-hellman-group1-sha1');
		if($this->_scp_connection = ssh2_connect($this->_eq_fqdn, 22, $ssh_parameters)) {
		
			if(!ssh2_auth_password($this->_scp_connection, $this->_eq_username, $this->_eq_password)) {
				//we are throwing our own exception, no need for custom error handle
				restore_error_handler();
				throw new exception("authentication failed", 22000);
			}
			
		} else {
			//we are throwing our own exception, no need for custom error handle
			restore_error_handler();
			throw new exception("connection failed", 22001);
		}

		restore_error_handler();
	}
	
	public function upload_file($local_path, $remote_path)
	{

		if ($this->_scp_connector_type == 'ssh2lib') {
			return $this->upload_using_ssh2lib($local_path, $remote_path);
		} else {
			throw new exception("we dont know what connector to use for ".$this->_eq_fqdn." ", 22002);
		}
	}
	
	private function upload_using_ssh2lib($local_path, $remote_path)
	{
		$result = ssh2_scp_send($this->_scp_connection, $local_path, $remote_path);
		$device_reply	= new Thelist_Model_devicereply(null, $result);
			
		return $device_reply;
	}
	
}
?>