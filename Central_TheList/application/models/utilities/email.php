<?php
require_once('Zend/Mail.php');

class thelist_utility_email
{
	private $servername;
	private $username;
	private $password;
	private $name;
	private $mail;
	private $transport;
	private $config;
	
	function __construct()
	{
	
		$config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/emailserver.ini','thelist');
		 
		$this->servername 	= $config->emailserver->host;
		$this->username   	= $config->emailserver->username;
		$this->password   	= $config->emailserver->password;
		$this->name  	  	= $config->emailserver->name;
		$this->from_name	= $config->emailserver->from->name;
		$this->from_address	= $config->emailserver->from->address;
		 
		
		 
		$this->config = array(
						'auth'     => 'login',
		   				'username' => $this->username,
		   				'password' => $this->password,
		   				'name'	   => $this->name
		 			    );
		 
		$this->transport = new Zend_Mail_Transport_Smtp($this->servername, $this->config);
		
		
			
   	}
   	
   function send($recipient,$content,$subject){
   	
   	$this->mail = new Zend_Mail();
   	$this->mail->setBodyText($content);
   	$this->mail->setFrom($this->from_address, $this->from_name);
   	$this->mail->addTo($recipient, $recipient);
   	$this->mail->setSubject($subject);
   	$this->mail->send($this->transport);
   	}
   
   	
}
?>