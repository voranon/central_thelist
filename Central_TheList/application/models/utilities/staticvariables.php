<?php

class thelist_utility_staticvariables
{
	
	public static $primary_ntp_server;
	public static $secondary_ntp_server;
	
	public static $snmp_contact;
	public static $snmp_trap_community;
	public static $snmp_ro_community;
	
	public static $company_name;
	public static $company_domain;
	
	public static $primary_syslog_server;
	
	public static $repository_server_fqdn;
	
	public static $recursive_dns_server1;
	public static $recursive_dns_server2;
	
	public static $http_config_server1;
	public static $http_config_server1_ssh_user;
	public static $http_config_server1_ssh_pass;
	public static $http_config_server1_base_url;
	public static $http_config_server1_folder_path;
	
	public static $bairouter_root_config_path;
	
	private static function init()
	{
		$config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/static_variables.ini','thelist');

		self::$primary_ntp_server 				= $config->sv->primary_ntp_server;
		self::$secondary_ntp_server 			= $config->sv->secondary_ntp_server;
		
		self::$snmp_contact 					= $config->sv->snmp_contact;
		self::$snmp_trap_community 				= $config->sv->snmp_trap_community;
		self::$snmp_ro_community 				= $config->sv->snmp_ro_community;
		
		self::$company_name 					= $config->sv->company_name;
		self::$company_domain 					= $config->sv->company_domain;
		
		self::$primary_syslog_server 			= $config->sv->primary_syslog_server;
		
		self::$repository_server_fqdn			= $config->sv->repository_server_fqdn;
		
		self::$recursive_dns_server1 			= $config->sv->recursive_dns_server1;
		self::$recursive_dns_server2 			= $config->sv->recursive_dns_server2;
		
		//config servers 
		self::$http_config_server1 				= $config->sv->http_config_server1;
		self::$http_config_server1_ssh_user 	= $config->sv->http_config_server1_ssh_user;
		self::$http_config_server1_ssh_pass 	= $config->sv->http_config_server1_ssh_pass;
		self::$http_config_server1_base_url 	= $config->sv->http_config_server1_base_url;
		self::$http_config_server1_folder_path 	= $config->sv->http_config_server1_folder_path;
		
		self::$bairouter_root_config_path		= $config->sv->bairouter_root_config_path;
		

	}
	
	public static function get_bairouter_root_config_path()
	{
		self::init();
		return self::$bairouter_root_config_path;
	}
	
	public static function get_primary_ntp_server()
	{
		self::init();
		return self::$primary_ntp_server;
	}
	
	public static function get_secondary_ntp_server()
	{
		self::init();
		return self::$secondary_ntp_server;
	}
	
	public static function get_snmp_contact()
	{
		self::init();
		return self::$snmp_contact;
	}
	
	public static function get_snmp_trap_community()
	{
		self::init();
		return self::$snmp_trap_community;
	}
	
	public static function get_snmp_ro_community()
	{
		self::init();
		return self::$snmp_ro_community;
	}
	
	public static function get_company_name()
	{
		self::init();
		return self::$company_name;
	}
	
	public static function get_company_domain()
	{
		self::init();
		return self::$company_domain;
	}
	
	public static function get_primary_syslog_server()
	{
		self::init();
		return self::$primary_syslog_server;
	}
	
	public static function get_recursive_dns_server1()
	{
		self::init();
		return self::$recursive_dns_server1;
	}
	
	public static function get_recursive_dns_server2()
	{
		self::init();
		return self::$recursive_dns_server2;
	}
	
	public static function get_http_config_server1()
	{
		self::init();
		return self::$http_config_server1;
	}
	
	public static function get_http_config_server1_ssh_user()
	{
		self::init();
		return self::$http_config_server1_ssh_user;
	}
	
	public static function get_http_config_server1_ssh_pass()
	{
		self::init();
		return self::$http_config_server1_ssh_pass;
	}
	
	public static function get_http_config_server1_base_url()
	{
		self::init();
		return self::$http_config_server1_base_url;
	}
	
	public static function get_http_config_server1_folder_path()
	{
		self::init();
		return self::$http_config_server1_folder_path;
	}
	
	public static function get_repository_server_fqdn()
	{
		self::init();
		return self::$repository_server_fqdn;
	}

}
?>