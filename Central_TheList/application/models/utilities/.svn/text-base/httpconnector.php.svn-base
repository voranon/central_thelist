<?php

class thelist_utility_httpconnector
{

	private $_fqdn;
	private $_port_number;
	private $_authenticationtype;
	private $_httpusername;
	private $_httppassword;

	
	public function __construct($fqdn, $port_number=80, $authenticationtype=null, $httpusername=null, $httppassword=null)
	{	
		$this->_fqdn = $fqdn;
		$this->_port_number = $port_number;
		$this->_authenticationtype = $authenticationtype;
		$this->_httpusername = $httpusername;
		$this->_httppassword = $httppassword;
		
		if ($this->_authenticationtype != null) {
			
			//something
		}
		
	}    
	
	public function execute_cmd($url)
	{

		$client = new Zend_Http_Client("http://".$this->_fqdn.":".$this->_port_number."/".$url."");
		$device_reply	= new Thelist_Model_devicereply($url, $client->request()->getbody());
		$device_reply->append_option('http_headers', $client->request()->getheaders());
		$device_reply->append_option('http_response_code', $client->request()->getstatus());
		$device_reply->append_option('http_response_message', $client->request()->getmessage());
		$device_reply->append_option('http_raw_body', $client->request()->getrawBody());

		return $device_reply;
	
	}
}
?>