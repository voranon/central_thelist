<?php

//by martin
//exception codes 1300-1399 
class thelist_model_ipflows
{
	private $database;
	private $user_session;
	private $log;
	private $_ip_flow_id;
	
	public function __construct($ip_flow_id)
	{
		
		$this->_ip_flow_id		= $ip_flow_id;

		$this->log				= Zend_Registry::get('logs');
		$this->user_session 	= new Zend_Session_Namespace('userinfo');
		
	}
	
	public function get_dest_domain()
	{
		$result	= gethostbyaddr($ip_address);
		
		if ($result == $ip_address) {
			
			//ip is returned on no result
			return false;
			
		} elseif ($result == false) {
			
			throw new exception('input is not an ip address', 1300);
			
		} else {
			
			return $result;
			
		}
	}
	
	public function get_dest_full_fqdn()
	{
	
		$result	= gethostbyaddr($ip_address);
	
		if ($result == $ip_address) {
				
			//ip is returned on no result
			return false;
				
		} elseif ($result == false) {
				
			throw new exception('input is not an ip address', 1300);
				
		} else {
				
			return $result;
				
		}
	}
}
?>