<?php

//exception codes 18300-18399

class thelist_routeros_command_getinterfacestatus implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_interface;
	
	//set on boot
	private $_configured_admin_status=null;
	private $_operational_status=null;
	
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
		
		//this also ensures the interface exists, since we bypass any commands on some wireless interfaces
		$get_if_type	= new Thelist_Routeros_command_getinterfacetype($this->_device, $this->_interface);
		$if_type		= $get_if_type->get_routeros_specific_if_type_name();
		
		if ($if_type == 'ethernet') {
			
			$command_op 		= "/interface ethernet print detail where name=\"".$interface_name."\"";
			$reg_ex_op			= "([0-9]+)( +)(R|X|) +(name|;;;)";
			$reg_ex_admin		= "([0-9]+)( +)(R|X|) +(name|;;;)";
			
		} elseif ($if_type == 'wireless') {
			
			$command_op 		= "/interface wireless print detail where name=\"".$interface_name."\"";
			$reg_ex_op			= "([0-9]+)( +)(R|X|) +(name|;;;)";
			$reg_ex_admin		= "([0-9]+)( +)(R|X|) +(name|;;;)";
			
		} elseif ($if_type == 'vlan') {
			
			$command_op 		= "/interface vlan print detail where name=\"".$interface_name."\"";
			$reg_ex_op			= "([0-9]+)( +)(R|X|) +(name|;;;)";
			$reg_ex_admin		= "([0-9]+)( +)(R|X|) +(name|;;;)";
			
		} else {
			throw new exception("we dont know how to handle interface status for type: '".$if_type."' on interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' ", 18300);
		}
		
		$device_reply_op = $this->_device->execute_command($command_op);
		
		preg_match("/".$reg_ex_op."/", $device_reply_op->get_message(), $raw_op_status);

		if (isset($raw_op_status['1'])) {
			
			if ($raw_op_status['3'] == 'R') {
				$this->_operational_status = 1;
			} elseif ($raw_op_status['3'] == 'X') {
				$this->_operational_status = null;
			} elseif ($raw_op_status['3'] == '') {
				$this->_operational_status = 0;
			}
		} else {
			throw new exception("we we could not determine the operational status for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' ", 18302);
		}
		
		preg_match("/".$reg_ex_admin."/", $device_reply_op->get_message(), $raw_admin_status);
		
		if (isset($raw_admin_status['1'])) {
				
			if ($raw_admin_status['3'] == 'X') {
				$this->_configured_admin_status = 0;
			} else {
				$this->_configured_admin_status = 1;
			}
		} else {
			throw new exception("we we could not determine the administrative status for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' ", 18303);
		}
	}
	
	public function get_operational_status($refresh=true) 
	{
		if($this->_operational_status == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		
		return $this->_operational_status;
	}
	
	public function get_configured_admin_status($refresh=true)
	{
		if($this->_configured_admin_status == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		
		return $this->_configured_admin_status;
	}
	
	public function get_interface_exist()
	{
		//we need to know if the interface exists
		try {
			
			if (is_object($this->_interface)) {
				$interface_name		= $this->_interface->get_if_name();
			} else {
				$interface_name		= $this->_interface;
			}
			
			//try ethernet
			$command	= "/interface print detail where name=\"".$interface_name."\"";
			$device_reply = $this->_device->execute_command($command);
			
			if (preg_match("/(name=\"?".$interface_name."\"?)/", $device_reply->get_message())) {
				//do nothing, interface exists
			} else {
				throw new exception("interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' does not exist ", 18304);
			}
			
			$interface_exists = 'yes';
			
		} catch (Exception $e) {
	
			switch($e->getCode()){
		
				case 18304;
				//18400, this means the interface does not exist using the object model
				//to locate the interface type
				$interface_exists = 'no';
				break;
				default;
				throw $e;
			}
		}
		
		if ($interface_exists == 'yes') {
			return true;
		} else {
			return false;
		}
	}
}