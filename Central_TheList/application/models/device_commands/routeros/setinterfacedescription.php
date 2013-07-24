<?php

//exception codes 21300-21399

class thelist_routeros_command_setinterfacedescription implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	
	private $_description=null;
	
	public function __construct($device, $interface, $description)
	{
		//$interface
		//object	= interface_obj
		//string	= interface name
		
		//$description
		//both string without line breaks, if null value is given comment is removed
		
		$this->_device 					= $device;
		$this->_interface 				= $interface;
		
		//remove any line breaks, these would result in execution of the command before time
		$patterns = array("\r", "\r\n", "\n");
		$this->_description = str_replace($patterns, ' line break removed ', $description);
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
		
		
		$get_description		= new Thelist_Routeros_command_getinterfacedescription($this->_device, $interface);
		$current_description	= $get_description->get_configured_description(true);

		if ($current_description != $this->_description) {
			
			$get_if_type	= new Thelist_Routeros_command_getinterfacetype($this->_device, $this->_interface);
			$if_type		= $get_if_type->get_routeros_specific_if_type_name();
			
			if ($if_type == 'vlan') {
				$command = "/interface vlan set [find where name=\"".$interface_name."\"] comment=\"".$this->_description."\"";
			} elseif ($if_type == 'ethernet') {
				$command = "/interface ethernet set [find where name=\"".$interface_name."\"] comment=\"".$this->_description."\"";
			} elseif ($if_type == 'wireless') {
				$command = "/interface wireless set [find where name=\"".$interface_name."\"] comment=\"".$this->_description."\"";
			} elseif ($if_type == 'nstreme_dual') {
				$command = "/interface wireless nstreme_dual set [find where name=\"".$interface_name."\"] comment=\"".$this->_description."\"";
			} else {
				throw new exception("expand method set comment - description to handle interface type ".$if_type." for interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."'", 21300);
			}
			
			$device_reply = $this->_device->execute_command($command);

			$verify	= $get_description->get_configured_description(true);
			
			if ($verify != $this->_description) {
				throw new exception("we failed to set description for interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."'", 21301);
			}
			
		} else {
			//no change we are done
		}
	}
}