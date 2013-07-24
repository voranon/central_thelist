<?php
require_once 'Net/SFTP.php';

//exception codes 21900-21999

class thelist_utility_sftpconnector
{
	private $_sftp_connection;
	private $_sftp_connector_type=null;
	
	private $_eq_fqdn;
	private $_eq_username;
	private $_eq_password;
	private $_device_verification_connection;

	
	public function __construct($eq_fqdn, $eq_sftp_username, $eq_sftp_password)
	{	
		$this->_eq_fqdn 		= $eq_fqdn;
		$this->_eq_username 	= $eq_sftp_username;
		$this->_eq_password 	= $eq_sftp_password;

		$this->get_phpseclib_connection();

	}
	
	private function get_phpseclib_connection()
	{
		define('NET_SFTP_LOGGING', NET_SFTP_LOG_COMPLEX);
		//in own method to make room for others
		//trace errors
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		$class	= get_class($this);
		
		$error_handler		= new Thelist_Utility_customerrorhandler();
		$error_handler->set_error_class_method($class, $method);
		
		//during connect use error handle
		set_error_handler(array($error_handler, 'device_error_handler'));
		
		$this->_sftp_connection = new Net_SFTP($this->_eq_fqdn);
		
		$login = $this->_sftp_connection->login($this->_eq_username, $this->_eq_password);
		
		if (!$login) {
			restore_error_handler();
			throw new exception("sftp authentication failed for host: '".$this->_eq_fqdn."'", 21900);
		} else {
			$this->_sftp_connector_type = 'phpseclib';
		}
		//back to normal error handle
		restore_error_handler();

	}
	
	public function set_sftp_connector_type($sftp_connector_type)
	{
		$this->_sftp_connector_type = $sftp_connector_type;
	}

	public function upload_file($local_file, $remote_file)
	{
		if ($this->_sftp_connector_type = 'phpseclib') {

			$result = $this->_sftp_connection->put($remote_file, $local_file, NET_SFTP_LOCAL_FILE);
			$device_reply	= new Thelist_Model_devicereply(null, $result);
			
			return $device_reply;

		} 
	}
	
}
?>