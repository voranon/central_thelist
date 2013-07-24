<?php

//exception codes 20700-20799

class thelist_routeros_command_setinterfacevlanid implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_interface;
	
	private $_vlan_id=null;

	public function __construct($device, $interface, $vlan_id)
	{
		//$interface
		//object	= interface_obj
		//string	= interface name
		
		//$vlan_id numerical
		
		$this->_device 					= $device;
		$this->_interface 				= $interface;
		$this->_vlan_id 				= $vlan_id;
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
		
		if (!is_numeric($this->_vlan_id)) {
			throw new exception("interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' vlan id is not numeric ", 20700);
		} elseif ($this->_vlan_id > 4096) {
			throw new exception("interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' vlan id cannot be higher than 4096 ", 20701);
		}
		
		$get_if_type	= new Thelist_Routeros_command_getinterfacetype($this->_device, $interface);
		$if_type		= $get_if_type->get_routeros_specific_if_type_name();
		
		if ($if_type == 'vlan') {
			
			$get_current_vlan_id	= new Thelist_Routeros_command_getinterfacevlanid($this->_device, $interface);
			$vlan_id				= $get_current_vlan_id->get_vlan_id(true);
			
			if ($vlan_id != $this->_vlan_id) {
				
				$command = "/interface vlan set [find where name=\"".$interface_name."\"] vlan-id=\"".$this->_vlan_id."\"";

				$device_reply 	= $this->_device->execute_command($command);
				
				$after_vlan_id				= $get_current_vlan_id->get_vlan_id(true);
					
				if ($after_vlan_id != $this->_vlan_id) {
					throw new exception("interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' could not set new vlan id", 20703);
				}
			}

		} else {
			throw new exception("interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' vlan id can only be set on vlan interfaces, this is not one ", 20702);
		}
	}

}