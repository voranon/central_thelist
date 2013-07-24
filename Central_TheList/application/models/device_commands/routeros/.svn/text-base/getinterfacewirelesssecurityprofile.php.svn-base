<?php

//exception codes 19500-19599

class thelist_routeros_command_getinterfacewirelesssecurityprofile implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	
	private $_profile_name=null;
	private $_authentication_types=null;
	private $_unicast_ciphers=null;
	private $_group_ciphers=null;
	private $_wpa_shared_key=null;
	private $_wpa2_shared_key=null;

	
	public function __construct($device, $interface)
	{
		//$interface
		//object	= interface_obj
		//string	= interface name
		
		$this->_device 					= $device;
		$this->_interface 				= $interface;
	}
	
	public function execute()
	{		
		if (is_object($this->_interface)) {
			$interface_name		= $this->_interface->get_if_name();
		} else {
			$interface_name		= $this->_interface;
		}

		$main_config_reply 	= $this->_device->execute_command("/interface wireless export");
		
		//get profile name
		preg_match("/name=".$interface_name." .* security-profile=\"?(.*?)\"? ssid/", $main_config_reply->get_message(), $raw_profile_name);

		if (isset($raw_profile_name['1'])) {
			$this->_profile_name		 = $raw_profile_name['1'];
		} else {
			throw new exception("we could not determine the security profile name for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' ", 19500);
		}

		
		//profile details
		$sec_reply 	= $this->_device->execute_command("/interface wireless security-profiles export");

		//auth types
		preg_match("/ authentication-types=(.*) eap-methods.* name=\"?".$this->_profile_name."\"? /", $sec_reply->get_message(), $raw_auth_types);

		if (isset($raw_auth_types['1'])) {
			
			if (preg_match("/,/", $raw_auth_types['1'])) {
				$this->_authentication_types = explode(",", $raw_auth_types['1']);
			} else {
				$this->_authentication_types[] = $raw_auth_types['1'];
			}

		} else {
			throw new exception("we could not determine the security profile auth types for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."', using profile name: '".$this->_profile_name."' ", 19501);
		}
		
		
		//unicast ciphers
		preg_match("/ name=\"?".$this->_profile_name."\"? .* unicast-ciphers=(.*?) wpa/", $sec_reply->get_message(), $raw_unicip_types);

		if (isset($raw_unicip_types['1'])) {
				
			if (preg_match("/,/", $raw_unicip_types['1'])) {
				$this->_unicast_ciphers = explode(",", $raw_unicip_types['1']);
			} else {
				$this->_unicast_ciphers[] = $raw_unicip_types['1'];
			}
		
		} else {
			throw new exception("we could not determine the security profile unicast ciphers for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."', using profile name: '".$this->_profile_name."' ", 19502);
		}
		
		//group ciphers
		preg_match("/ group-ciphers=(.*?) .* name=\"?".$this->_profile_name."\"? /", $sec_reply->get_message(), $raw_groupcip_types);
		
		if (isset($raw_groupcip_types['1'])) {
		
			if (preg_match("/,/", $raw_groupcip_types['1'])) {
				$this->_group_ciphers = explode(",", $raw_groupcip_types['1']);
			} else {
				$this->_group_ciphers[] = $raw_groupcip_types['1'];
			}
		
		} else {
			throw new exception("we could not determine the security profile group ciphers for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."', using profile name: '".$this->_profile_name."' ", 19503);
		}
		
		//_wpa_shared_key
		preg_match("/name=\"?".$this->_profile_name."\"? .* wpa-pre-shared-key=\"?(.*?)\"? wpa2/", $sec_reply->get_message(), $raw_wpa_key);
		
		if (isset($raw_wpa_key['1'])) {
			$this->_wpa_shared_key = $raw_wpa_key['1'];
		} else {
			throw new exception("we could not determine the security profile wpa key for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."', using profile name: '".$this->_profile_name."' ", 19504);
		}
		
		//wpa2_shared_key
		preg_match("/name=\"?".$this->_profile_name."\"? .* wpa2-pre-shared-key=\"?(.*)\"? ?/", $sec_reply->get_message(), $raw_wpa2_key);
		
		if (isset($raw_wpa2_key['1'])) {

			//not great logic, but the regex is too greedy and counts the " if the key is in quotations because of a space
			if (substr($raw_wpa2_key['1'], -2, -1) == '"') {
				$this->_wpa2_shared_key = substr($raw_wpa2_key['1'], 0, -2);
			} else {
				$this->_wpa2_shared_key = $raw_wpa2_key['1'];
			}

		} else {
			throw new exception("we could not determine the security profile wpa2 key for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."', using profile name: '".$this->_profile_name."' ", 19504);
		}
	}
	
	public function get_profile($refresh=true)
	{
		if($this->_profile_name == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}

		$return['profile_name']							= $this->_profile_name;
		$return['authentication_types']					= $this->_authentication_types;
		$return['unicast_ciphers']						= $this->_unicast_ciphers;
		$return['group_ciphers']						= $this->_group_ciphers;
		$return['encryption_keys']['0']['key']			= $this->_wpa_shared_key;
		$return['encryption_keys']['0']['auth_type']	= 'wpa-psk';
		$return['encryption_keys']['1']['key']			= $this->_wpa2_shared_key;
		$return['encryption_keys']['1']['auth_type']	= 'wpa2-psk';
		 
		 return $return;
	}

	public function get_profile_name($refresh=true)
	{
		if($this->_profile_name == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}

		return $this->_profile_name;
	}
	
	public function get_authentication_types($refresh=true)
	{
		if($this->_authentication_types == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
	
		return $this->_authentication_types;
	}
	
	public function get_unicast_ciphers($refresh=true)
	{
		if($this->_unicast_ciphers == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
	
		return $this->_unicast_ciphers;
	}
	
	public function get_group_ciphers($refresh=true)
	{
		if($this->_group_ciphers == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
	
		return $this->_group_ciphers;
	}
	
	public function get_wpa_shared_key($refresh=true)
	{
		if($this->_wpa_shared_key == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		
		return $this->_wpa_shared_key;
	}
	
	public function get_wpa2_shared_key($refresh=true)
	{
		if($this->_wpa2_shared_key == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
	
		return $this->_wpa2_shared_key;
	}
	
	public function get_encryption_keys($refresh=true)
	{
		if($this->_wpa2_shared_key == null || $this->_wpa_shared_key == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
	
		$return['0']['key']			= $this->_wpa_shared_key;
		$return['0']['auth_type']	= 'wpa-psk';
		
		$return['1']['key']			= $this->_wpa2_shared_key;
		$return['1']['auth_type']	= 'wpa2-psk';
		
		return $return;
	}
	
	
	
}