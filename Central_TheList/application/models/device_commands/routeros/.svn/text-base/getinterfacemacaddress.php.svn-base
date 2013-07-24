<?php

//exception codes 7700-7799

class thelist_routeros_command_getinterfacemacaddress implements Thelist_Commander_pattern_interface_idevicecommand
{
	private $_device;
	private $_interface;
	
	private $_mac_address=null;
	
	public function __construct($device, $interface)
	{
		$this->_device 		= $device;
		$this->_interface	= $interface;
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
		
		$get_if_type	= new Thelist_Routeros_command_getinterfacetype($this->_device, $interface);
		$if_type		= $get_if_type->get_routeros_specific_if_type_name();
		
		if ($if_type == 'vlan') {
			$command	= "/interface vlan print detail where name=\"".$interface_name."\"";
		} elseif ($if_type == 'ethernet') {
			$command	= "/interface ethernet print detail where name=\"".$interface_name."\"";
		} elseif ($if_type == 'wireless') {
			$command	= "/interface wireless print detail where name=\"".$interface_name."\"";
		} elseif ($if_type == 'nstreme_dual') {
			$command	= "/interface wireless nstreme-dual print detail where name=\"".$interface_name."\"";
		} elseif ($if_type == 'bridge') {
			$command	= "/interface bridge print detail where name=\"".$interface_name."\"";
		} else {
			throw new exception("interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' could not handle interface type ".$if_type.", expand method ", 7702);
		}

		$device_reply = $this->_device->execute_command($command);

		preg_match("/(\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2})/", $device_reply->get_message(), $mac_address_raw);

		if (isset($mac_address_raw['1'])) {
			
			$clean_mac_address		= strtoupper(preg_replace("/:/", "", $mac_address_raw['1']));
			$this->_mac_address = new Thelist_Deviceinformation_macaddressinformation($clean_mac_address);
			
		} else {

			throw new exception("we could not get the interface mac address from interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' and interface type".$if_type."  ", 7701);
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