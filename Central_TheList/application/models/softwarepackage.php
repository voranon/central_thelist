<?php

//created by Martin

class thelist_model_softwarepackage
{
	
	private $_software_package_id;
	private $_software_package_manufacturer;
	private $_software_package_name;
	private $_software_package_architecture;
	private $_software_package_version;
	private $_software_package_server;
	private $_software_package_path;
	private $_software_package_file_name;
	
	//common variables
	private $log;
	private $user_session;
	private $database;
	
	public function __construct($software_package_id)
	{
		$this->_software_package_id		= $software_package_id;	

		$this->log						= Zend_Registry::get('logs');
		$this->user_session 			= new Zend_Session_Namespace('userinfo');
		
		$sql =	"SELECT * FROM software_packages
				WHERE software_package_id='".$this->_software_package_id."'
				";
		
		$software_package = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
	
		$this->_software_package_manufacturer			=$software_package['software_package_manufacturer'];
		$this->_software_package_name					=$software_package['software_package_name'];
		$this->_software_package_architecture			=$software_package['software_package_architecture'];
		$this->_software_package_version				=$software_package['software_package_version'];
		$this->_software_package_server					=$software_package['software_package_server'];
		$this->_software_package_path					=$software_package['software_package_path'];
		$this->_software_package_file_name				=$software_package['software_package_file_name'];
		
	}
	
	public function get_software_package_id()
	{
		return $this->_software_package_id;
	}
	public function get_software_package_manufacturer()
	{
		return $this->_software_package_manufacturer;
	}
	public function get_software_package_name()
	{
		return $this->_software_package_name;
	}
	public function get_software_package_architecture()
	{
		return $this->_software_package_architecture;
	}
	public function get_software_package_version()
	{
		return $this->_software_package_version;
	}
	public function get_software_package_server()
	{
		return $this->_software_package_server;
	}
	public function get_software_package_path()
	{
		return $this->_software_package_path;
	}
	public function get_software_package_file_name()
	{
		return $this->_software_package_file_name;
	}
	
}
?>