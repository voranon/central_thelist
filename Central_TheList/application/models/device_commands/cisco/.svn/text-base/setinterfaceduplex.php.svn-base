<?php

//exception codes 10400-10499

class thelist_cisco_command_setinterfaceduplex implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	private $_duplex;
	
	public function __construct($device, $interface, $duplex)
	{
		//$interface
		//object	= interface_obj
		//string	= interface name
		
		//$duplex
		//object	= new duplex string
		//string	= new duplex string
		
		$this->_device 					= $device;
		$this->_interface 				= $interface;
		$this->_duplex					= $duplex;
	}
	
	public function execute()
	{
		//get the root folder
		$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
		$get_connection_root_folder->execute();
		
		if (is_object($this->_interface)) {
			$interface_name			= $this->_interface->get_if_name();
			$get_interface_duplex	= new Thelist_Cisco_command_getinterfaceduplex($this->_device, $this->_interface);
		} else {
			$interface_name			= $this->_interface;
			$get_interface_duplex	= new Thelist_Cisco_command_getinterfaceduplex($this->_device, $interface_name);
		}
		
		$current_duplex			= $get_interface_duplex->get_configured_duplex();
		
		if ($current_duplex != $this->_duplex) {
			
			$this->_device->execute_command("configure terminal");
			$this->_device->execute_command("interface ".$interface_name."");
			$this->_device->execute_command("duplex ".$this->_duplex."");
			$this->_device->execute_command("end");
			
			$verify			= $get_interface_duplex->get_configured_duplex();
			
			if ($verify != $this->_duplex) {
				throw new exception('interface duplex was not updated correctly', 10400);
			}
		}
	}
}