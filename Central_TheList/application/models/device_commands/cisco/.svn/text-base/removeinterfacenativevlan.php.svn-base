<?php

//exception codes 2700-2799

class thelist_cisco_command_removeinterfacenativevlan implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	
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
			$interface_name			= $this->_interface->get_if_name();
			$get_interface_native_vlan	= new Thelist_Cisco_command_getinterfacenativevlan($this->_device, $this->_interface);
		} else {
			$interface_name			= $this->_interface;
			$get_interface_native_vlan	= new Thelist_Cisco_command_getinterfacenativevlan($this->_device, $interface_name);
		}
		
		$current_native_vlan_id			= $get_interface_native_vlan->get_configured_native_vlan_id(true);
		
		if ($current_native_vlan_id != null) {

			//get the root folder
			$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
			$get_connection_root_folder->execute();
			
			//enter config mode
			$device_reply = $this->_device->execute_command("configure terminal");
			$this->_device->execute_command("interface ".$interface_name."");
			$this->_device->execute_command("no switchport trunk native vlan ".$current_native_vlan_id."");
			$this->_device->execute_command('end');

			$verify			= $get_interface_native_vlan->get_configured_native_vlan_id(true);

			//vlan that has been removed, on some switches that means it defaults to 1
			if ($verify != null && $verify != 1) {
				throw new exception('interface native vlan was not removed', 2700);
			}
		}
	}
}