<?php

//exception codes 10800-10899

class thelist_cisco_command_getinterfacenativevlan implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	
	private $_configured_native_vlan_id=null;

	
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
		//get the root folder
		$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
		$get_connection_root_folder->execute();

		if (is_object($this->_interface)) {
			$interface_name		= $this->_interface->get_if_name();
		} else {
			$interface_name		= $this->_interface;
		}

		$device_reply1 = $this->_device->execute_command("show running-config interface ".$interface_name."");
		
		//check if command failed
		if (preg_match("/Current configuration/", $device_reply1->get_message())) {
				
			preg_match("/switchport trunk native vlan ([0-9]+)\r/", $device_reply1->get_message(), $result1);
			
			if (isset($result1['1'])) {
				$this->_configured_native_vlan_id	= $result1['1'];
			} else {
				
				$get_switch_port_mode	= new Thelist_Cisco_command_getinterfaceswitchportmode($this->_device, $this->_interface);
				$interface_mode			= $get_switch_port_mode->get_configured_switch_port_mode();
				
				if ($interface_mode == 'trunk') {
					//by default cisco uses vlan 1 and that will not show in the running config
					$this->_configured_native_vlan_id	= 1;
				} else {
					//no native vlan, maybe we are in access mode
					$this->_configured_native_vlan_id	= null;
				}
				
			}

		} else {
			throw new exception('interface configured native vlan could not be determined for interface on device', 10800);
		}
	}
	
	public function get_configured_native_vlan_id($refresh=true)
	{
		if($this->_configured_native_vlan_id == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		return $this->_configured_native_vlan_id;
	}
}