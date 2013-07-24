<?php

//exception codes 20800-20899

class thelist_routeros_command_setinterfacel3mtu implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	private $_l3mtu=null;
	
	public function __construct($device, $interface, $l3mtu)
	{
		//$interface
		//object	= interface_obj
		//string	= interface name
		
		//$l3mtu must be numeric
		$this->_device 					= $device;
		$this->_interface 				= $interface;
		$this->_l3mtu					= $l3mtu;
	}
	
	public function execute()
	{	
		if (is_object($this->_interface)) {
			$interface_name			= $this->_interface->get_if_name();
			$interface				= $this->_interface;
		} else {
			$interface_name			= $this->_interface;
			$interface				= $this->_interface;
		}
		
		$get_current_l3_mtu	= new Thelist_Routeros_command_getinterfacelayer3mtu($this->_device, $interface);
		$current_l3_mtu		= $get_current_l3_mtu->get_configured_layer3mtu(true);
		
		if ($current_l3_mtu != $this->_l3mtu) {
			
			$get_if_type	= new Thelist_Routeros_command_getinterfacetype($this->_device, $interface);
			$if_type		= $get_if_type->get_routeros_specific_if_type_name();
			
			if ($if_type == 'vlan') {
				$command = "/interface vlan set [find where name=\"".$interface_name."\"] mtu=\"".$this->_l3mtu."\"";
			} elseif ($if_type == 'ethernet') {
				$command = "/interface ethernet set [find where name=\"".$interface_name."\"] mtu=\"".$this->_l3mtu."\"";
			} elseif ($if_type == 'wireless') {
				$command = "/interface wireless set [find where name=\"".$interface_name."\"] mtu=\"".$this->_l3mtu."\"";
			} elseif ($if_type == 'nstreme_dual') {
				$command = "/interface wireless nstreme_dual set [find where name=\"".$interface_name."\"] mtu=\"".$this->_l3mtu."\"";
			} else {
				throw new exception("expand method set l3mtu to handle interface type ".$if_type." for interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."'", 20800);
			}
			
			$device_reply 	= $this->_device->execute_command($command);
			
			$verify		= $get_current_l3_mtu->get_configured_layer3mtu(true);
			
			if ($verify != $this->_l3mtu) {
				throw new exception("we could not change the l3 mtu for interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."'", 20800);
			}
		}
	}
}