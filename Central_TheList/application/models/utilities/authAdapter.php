<?php
require_once 'Zend/Auth.php';
require_once 'Zend/Auth/Adapter/Ldap.php';

class thelist_utility_authAdapter
{
	public $authLDAPAdapter;
	
	function __construct()
	{
   
		$config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/LDAP.ini','production');
		$options = $config->ldap->toArray();
		$this->authLDAPAdapter = new Zend_Auth_Adapter_Ldap($options);
		
		
    }
	
}
?>