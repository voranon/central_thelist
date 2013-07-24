<?php

//exception codes 21400-21499

class thelist_routeros_command_setinterfacename implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;

	private $_new_name=null;
	
	public function __construct($device, $interface, $new_name)
	{
		//$interface
		//object	= interface_obj
		//string	= interface name
		
		//$new_name
		//both string
		
		$this->_device 					= $device;
		$this->_interface 				= $interface;
		$this->_new_name 				= $new_name;
	}
	
	public function execute()
	{		
		if (is_object($this->_interface)) {
			$interface_name		= $this->_interface->get_if_name();
			$interface			= $this->_interface;
		} else {
			$interface_name		= $this->_interface;
			$interface			= $this->_interface;
		}
		
		$get_new_status		= new Thelist_Routeros_command_getinterfacestatus($this->_device, $this->_new_name);
		$new_current_exist	= $get_new_status->get_interface_exist();
		
		if ($new_current_exist === true) {
			throw new exception("you wanted to rename interface: '".$interface_name."' to '".$this->_new_name."' on device: '".$this->_device->get_fqdn()."', but the new interface name is already in use", 21400);
		}

		$get_old_status		= new Thelist_Routeros_command_getinterfacestatus($this->_device, $interface);
		$old_current_exist	= $get_old_status->get_interface_exist();

		if ($old_current_exist === true) {
			
			$get_if_type	= new Thelist_Routeros_command_getinterfacetype($this->_device, $interface);
			$if_type		= $get_if_type->get_routeros_specific_if_type_name();
			
			if ($if_type == 'vlan') {
				$command = "/interface vlan set [find where name=\"".$interface_name."\"] name=\"".$this->_new_name."\"";
			} elseif ($if_type == 'ethernet') {
				$command = "/interface ethernet set [find where name=\"".$interface_name."\"] name=\"".$this->_new_name."\"";
			} elseif ($if_type == 'wireless') {
				$command = "/interface wireless set [find where name=\"".$interface_name."\"] name=\"".$this->_new_name."\"";
			} elseif ($if_type == 'nstreme_dual') {
				$command = "/interface wireless nstreme_dual set [find where name=\"".$interface_name."\"] name=\"".$this->_new_name."\"";
			} else {
				throw new exception("expand method set comment - description to handle interface type ".$if_type." for interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."'", 21401);
			}
			
			$device_reply = $this->_device->execute_command($command);

			$verify_new	= $get_new_status->get_interface_exist();
			$verify_old	= $get_old_status->get_interface_exist();
			
			if ($verify_new !== true || $verify_old !== false) {
				throw new exception("we failed to set interface name for interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."'", 21403);
			}
			
		} else {
			throw new exception("you wanted to rename interface: '".$interface_name."' to '".$this->_new_name."' on device: '".$this->_device->get_fqdn()."', but the old interface does not exist", 21402);
		}
	}
}