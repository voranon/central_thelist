<?php

//exception codes 18600-18699

class thelist_routeros_command_getinterfaceduplex implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	
	private $_configured_duplex=null;
	
	//if negotiated
	private $_running_duplex=null;
	
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
		
		if ($if_type != 'vlan') {

			//get admin status for the interface
			$get_if_status				= new Thelist_Routeros_command_getinterfacestatus($this->_device, $interface);
			$configured_admin_status	= $get_if_status->get_configured_admin_status(false);
			
			if ($if_type == 'ethernet') {
				
				//ethernet
				$command 			= "/interface ethernet print detail where name=\"".$interface_name."\"";
	
				$reg_ex_1			= "auto-negotiation=(yes|no)";
				$reg_ex_2			= "full-duplex=(yes|no)";
				
				$device_reply = $this->_device->execute_command($command);
				
				preg_match("/".$reg_ex_1."/", $device_reply->get_message(), $raw_negotiation_status);
				
				if (isset($raw_negotiation_status['1'])) {
					
					if ($raw_negotiation_status['1'] == 'yes') {
						$this->_configured_duplex	= 'auto';
					} else {
						
						preg_match("/".$reg_ex_2."/", $device_reply->get_message(), $raw_configured_duplex);
						
						if (isset($raw_configured_duplex['1'])) {
							
							if ($raw_configured_duplex['1'] == 'yes') {
								$this->_configured_duplex	= 'full';
							} else {
								$this->_configured_duplex	= 'half';
							}
							
						} else {
							throw new exception("we we could not determine the configured duplex for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."'", 18603);
						}
					}
	
					$operational_status		= $get_if_status->get_operational_status(false);
					
					if ($operational_status == 1) {
						
						$command2 			= "/interface ethernet monitor [find where name=\"".$interface_name."\"] once";
						$device_reply2 		= $this->_device->execute_command($command2);
						$reg_ex_3			= "full-duplex: (yes|no)";
						
						preg_match("/".$reg_ex_3."/", $device_reply2->get_message(), $raw_running_duplex);
						
						if (isset($raw_running_duplex['1'])) {
						
							if ($raw_running_duplex['1'] == 'yes') {
								$this->_running_duplex	= 'full';
							} else {
								$this->_running_duplex	= 'half';
							}
						
						} else {
							throw new exception("we we could not determine the running duplex for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."'", 18604);
						}
	
					} else {
						$this->_running_duplex	= null;
					}
	
				} else {
					throw new exception("we we could not determine if interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' is autonegotiating or not", 18602);
				}
					
			} elseif ($if_type == 'wireless') {
					
				//wireless
								
				//802.11 is half duplex 
				$this->_configured_duplex	= 'half';
				
				if ($configured_admin_status == 1) {
					$this->_running_duplex	= 'half';
				} else {
					$this->_running_duplex	= null;
				}
					
			} elseif ($if_type == 'nstreme_dual') {
				
				//nstreme dual
				$this->_configured_duplex	= 'full';
				
				if ($configured_admin_status == 1) {
					$this->_running_duplex	= 'full';
				} else {
					$this->_running_duplex	= null;
				}
					
			} 
		}
	}
	

	public function get_running_duplex($refresh=true) 
	{
		if($this->_running_duplex == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		
		return $this->_running_duplex;
	}
	
	public function get_configured_duplex($refresh=true)
	{
		if($this->_configured_duplex == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}

		return $this->_configured_duplex;
	}
}