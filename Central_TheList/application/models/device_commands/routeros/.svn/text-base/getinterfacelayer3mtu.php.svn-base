<?php

//exception codes 19200-19299 

class thelist_routeros_command_getinterfacelayer3mtu implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_interface;
	
	private $_configured_layer3mtu=null;
		
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

		//get admin status for the interface
		$get_if_status				= new Thelist_Routeros_command_getinterfacestatus($this->_device, $interface);
		$configured_admin_status	= $get_if_status->get_configured_admin_status(false);
		
		if ($if_type == 'ethernet') {
			
			//ethernet
			$command 			= "/interface ethernet print detail where name=\"".$interface_name."\"";

			$reg_ex_1			= " mtu=([0-9]+) ";
			
			$device_reply = $this->_device->execute_command($command);
			
			preg_match("/".$reg_ex_1."/", $device_reply->get_message(), $raw_mtu_value);
			
			if (isset($raw_mtu_value['1'])) {
				
				$this->_configured_layer3mtu = $raw_mtu_value['1'];

			} else {
				throw new exception("we we could not determine layer 3 mtu for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."'", 19200);
			}
				
		} elseif ($if_type == 'wireless') {
				
			//wireless
			$command 			= "/interface wireless print detail where name=\"".$interface_name."\"";

			$reg_ex_1			= " mtu=([0-9]+) ";
			
			$device_reply = $this->_device->execute_command($command);
			
			preg_match("/".$reg_ex_1."/", $device_reply->get_message(), $raw_mtu_value);
			
			if (isset($raw_mtu_value['1'])) {
				
				$this->_configured_layer3mtu = $raw_mtu_value['1'];

			} else {
				throw new exception("we we could not determine layer 3 mtu for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."'", 19201);
			}
				
		} elseif ($if_type == 'vlan') {
				
			//wireless
			$command 			= "/interface vlan print detail where name=\"".$interface_name."\"";

			$reg_ex_1			= " mtu=([0-9]+) ";
			
			$device_reply = $this->_device->execute_command($command);
			
			preg_match("/".$reg_ex_1."/", $device_reply->get_message(), $raw_mtu_value);
			
			if (isset($raw_mtu_value['1'])) {
				
				$this->_configured_layer3mtu = $raw_mtu_value['1'];

			} else {
				throw new exception("we we could not determine layer 3 mtu for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."'", 19202);
			}
				
		} elseif ($if_type == 'nstreme_dual') {
			
			//nstreme dual
		} 
	}
	
	public function get_configured_layer3mtu($refresh=true)
	{
		if($this->_configured_layer3mtu == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}

		return $this->_configured_layer3mtu;
	}
}