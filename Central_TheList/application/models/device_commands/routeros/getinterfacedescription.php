<?php

//exception codes 19600-19699

class thelist_routeros_command_getinterfacedescription implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	
	private $_configured_description=null;
	
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
		
		
		$get_if_type	= new Thelist_Routeros_command_getinterfacetype($this->_device, $this->_interface);
		$if_type		= $get_if_type->get_routeros_specific_if_type_name();
			
		if ($if_type == 'wireless' || $if_type == 'ethernet' || $if_type == 'vlan' || $if_type == 'nstreme_dual') {
			//append as we add commands for interface types
		} else {
			throw new exception("we dont know how to get description for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' ", 19600);
		}

		if ($if_type == 'ethernet') {
				
			$command	 		= "/interface ethernet export";
			$reg_ex				= "comment=\"?(.*?)\"? disabled=.* name=\"?".$interface_name."\"? ";
			
				
		} elseif ($if_type == 'wireless') {
				
			$command			= "/interface wireless export";
			$reg_ex				= "comment=\"?(.*?)\"? compression.* name=\"?".$interface_name."\"? ";
				
		} elseif ($if_type == 'vlan') {
				
			$command 			= "/interface vlan export";
			$reg_ex				= "comment=\"?(.*?)\"? disabled=.* name=\"?".$interface_name."\"? ";
				
		} elseif ($if_type == 'nstreme_dual') {

			$command 			= "/interface wireless nstreme-dual export";
			$reg_ex				= "comment=\"?(.*?)\"? disabled=.* name=\"?".$interface_name."\"? ";
				
		}
		
		$device_reply = $this->_device->execute_command($command);

		//is there the word comment= in the line with the interface name, if we dont do this check we cannot throw exception, because if the interface has no comment
		//then we would be throwing the exception all the time
		if (preg_match("/comment=.* name=\"?".$interface_name."\"?/", $device_reply->get_message())) {
			
			preg_match("/".$reg_ex."/", $device_reply->get_message(), $raw_description);
			
			if (isset($raw_description['1'])) {
				$this->_configured_description = $raw_description['1'];
			} else {
				throw new exception("we we could not determine the description for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' ", 19600);
			}
		}
	}

	public function get_configured_description($refresh=true)
	{
		if($this->_configured_description == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		
		return $this->_configured_description;
	}

}