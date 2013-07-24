<?php
require_once 'Net/ROSAPI.php';

//exception codes 22200-22299

class thelist_utility_routerosapiconnector
{
	private $_api_connection;
	
	private $_eq_fqdn;
	private $_eq_username;
	private $_eq_password;
	
	public function __construct($eq_fqdn, $eq_username, $eq_password)
	{	
		$this->_eq_fqdn 		= $eq_fqdn;
		$this->_eq_username 	= $eq_username;
		$this->_eq_password 	= $eq_password;

		$this->get_routeros_api_connection();
	}
	
	private function get_routeros_api_connection()
	{
		//trace errors
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		$class	= get_class($this);
	
		$error_handler		= new Thelist_Utility_customerrorhandler();
		$error_handler->set_error_class_method($class, $method);
	
		//during connect use error handle
		set_error_handler(array($error_handler, 'device_error_handler'));

		$this->_api_connection = new RouterOS();
		$this->_api_connection->connect($this->_eq_fqdn, $this->_eq_username, $this->_eq_password);
		//$this->_api_connection->setTimeout(10);

		restore_error_handler();
	}
	
	public function download_file_via_http($url)
	{
		$result = $this->_api_connection->fetchurl("http://".$url."");
		
		return new Thelist_Model_devicereply($url, $result);
	}
}
?>