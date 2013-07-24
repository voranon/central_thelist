<?php

//exception codes 10900-10999

class thelist_cisco_command_setinterfacenativevlan implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	private $_native_vlan_id;
	
	
	public function __construct($device, $interface, $native_vlan_id)
	{
		//$interface
		//object	= interface_obj
		//string	= interface name
		
		//$native_vlan_id
		//object	= int vlan id
		//string	= int vlan id
		
		$this->_device 					= $device;
		$this->_interface 				= $interface;
		$this->_native_vlan_id 				= $native_vlan_id;
	}
	
	public function execute()
	{		
		if (is_object($this->_interface)) {
			$interface_name			= $this->_interface->get_if_name();
			$get_interface_native_vlan	= new Thelist_Cisco_command_getinterfacenativevlan($this->_device, $this->_interface);
		} else {
			$interface_name			= $this->_interface;
			$get_interface_native_vlan	= new Thelist_Cisco_command_getinterfacenativevlan($this->_device, $interface_name);
		}
		
		$current_native_vlan_id			= $get_interface_native_vlan->get_configured_native_vlan_id();
		
		if ($current_native_vlan_id != $this->_native_vlan_id) {

			//get the root folder
			$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
			$get_connection_root_folder->execute();
			
			//enter config mode
			$device_reply = $this->_device->execute_command("configure terminal");
			$this->_device->execute_command("interface ".$interface_name."");
			$this->_device->execute_command("switchport trunk native vlan ".$this->_native_vlan_id."");
			$this->_device->execute_command('end');

			$verify			= $get_interface_native_vlan->get_configured_native_vlan_id();
			
			if ($verify != $this->_native_vlan_id) {
				throw new exception('interface native vlan was not set', 10900);
			}
		}
	}
}