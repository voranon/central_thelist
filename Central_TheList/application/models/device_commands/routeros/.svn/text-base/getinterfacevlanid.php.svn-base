<?php

//exception codes 20600-20699

class thelist_routeros_command_getinterfacevlanid implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_interface;
	
	private $_vlan_id=null;

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
			$interface_name		= $this->_interface->get_if_name();
			$interface			= $this->_interface;
		} else {
			$interface_name		= $this->_interface;
			$interface			= $this->_interface;
		}
		
		$get_if_type	= new Thelist_Routeros_command_getinterfacetype($this->_device, $interface);
		$if_type		= $get_if_type->get_routeros_specific_if_type_name();
		
		if ($if_type == 'vlan') {
			
			$command 		= "/interface vlan export";
			$device_reply 	= $this->_device->execute_command($command);
		
			preg_match("/ name=\"?".$interface_name."\"? .* vlan-id=([0-9]+)/", $device_reply->get_message(), $raw_vlan_id);

			if (isset($raw_vlan_id['1'])) {
				$this->_vlan_id = $raw_vlan_id['1'];
			} else {
				throw new exception("interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' could not determine vlan id ", 20600);
			}
		}
	}
	
	public function get_vlan_id($refresh=true) 
	{
		if($this->_vlan_id == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		
		return $this->_vlan_id;
	}
}