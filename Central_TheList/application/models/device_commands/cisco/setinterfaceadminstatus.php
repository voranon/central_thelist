<?php

//exception codes 10300-10399

class thelist_cisco_command_setinterfaceadminstatus implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	private $_admin_status;
	
	public function __construct($device, $interface, $admin_status)
	{
		//$interface
		//object	= interface_obj
		//string	= interface name
		
		//$admin_status 0 = shut, 1 = enabled
		if ($admin_status != 0 && $admin_status != 1) {
			throw new exception("interface admin status can only be 0 (down) or 1 (up), you provided ".$admin_status." ", 10301);
		}
		
		
		
		$this->_device 					= $device;
		$this->_interface 				= $interface;
		$this->_admin_status			= $admin_status;
	}
	
	public function execute()
	{
		//get the root folder
		$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
		$get_connection_root_folder->execute();
		
		if (is_object($this->_interface)) {
			$interface_name			= $this->_interface->get_if_name();
			$get_interface_status	= new Thelist_Cisco_command_getinterfacestatus($this->_device, $this->_interface);
		} else {
			$interface_name			= $this->_interface;
			$get_interface_status	= new Thelist_Cisco_command_getinterfacestatus($this->_device, $interface_name);
		}

		$current_admin_status			= $get_interface_status->get_configured_admin_status();
		
		if ($current_admin_status != $this->_admin_status) {
			
			$this->_device->execute_command("configure terminal");
			$this->_device->execute_command("interface ".$interface_name."");
			
			if ($this->_admin_status == 0) {
				$this->_device->execute_command("shutdown");
			} else {
				$this->_device->execute_command("no shutdown");
			}

			$this->_device->execute_command("end");
			
			$verify			= $get_interface_status->get_configured_admin_status(true);
			
			if ($verify != $this->_admin_status) {
				throw new exception('interface admin status was not updated correctly', 10300);
			}
		}
	}
}