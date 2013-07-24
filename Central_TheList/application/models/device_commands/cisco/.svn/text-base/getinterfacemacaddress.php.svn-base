<?php

//exception codes 4400-4499

class thelist_cisco_command_getinterfacemacaddress implements Thelist_Commander_pattern_interface_idevicecommand
{

	private $_device;
	private $_interface;
	private $_mac_address=null;
	
	public function __construct($device, $interface)
	{
		//$interface
		//object	= interface_obj
		//string	= interface name
		
		$this->_device = $device;
		$this->_interface	= $interface;
	
	}
	
	public function execute()
	{
		
		if (is_object($this->_interface)) {
			$interface_name		= $this->_interface->get_if_name();
		} else {
			$interface_name		= $this->_interface;
		}

				//set the terminal so it does not page
		$set_terminal	= new Thelist_Cisco_command_setterminal($this->_device, 80, 0);
		$set_terminal->execute();

		//get the root folder
		$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
		$get_connection_root_folder->execute();

		//get interface detail
		if ($interface_name != 'EtherSVI') {
			$device_reply = $this->_device->execute_command("show interfaces ".$interface_name." | in Hardware is");
			
			//old 2900 software cannot regex
			if (preg_match("/Invalid input detected/", $device_reply->get_message())) {
				$device_reply = $this->_device->execute_command("show interfaces ".$interface_name."");
			}
			
		} else {
			$device_reply = $this->_device->execute_command("show interfaces vlan 1 | in Hardware is");
			
			//old 2900 software cannot regex or may not have vlan 1000
			if (preg_match("/Invalid input detected/", $device_reply->get_message())) {
				$device_reply = $this->_device->execute_command("show interfaces vlan 1");
			}
		}
		
		//set the terminal back to standard
		$set_terminal	= new Thelist_Cisco_command_setterminal($this->_device, 80, 25);
		$set_terminal->execute();
		
		$arp_array = array();
		preg_match("/address is (\w{4}\.\w{4}\.\w{4}) \(bia/", $device_reply->get_message(), $mac_address);
		
		if (isset($mac_address['1'])) {
			$this->_mac_address = new Thelist_Deviceinformation_macaddressinformation($mac_address['1']);
		} else {
			throw new exception('interface mac address could not be determined', 4400);
		}
	}
	
	public function get_mac_address()
	{
		if ($this->_mac_address == null) {
			$this->execute();
		}
		return $this->_mac_address;
	}

}