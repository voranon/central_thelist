<?php

//exception codes 18800-18899

class thelist_routeros_command_getinterfacewirelesstxchannelwidth implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_interface;
	
	private $_configured_tx_channel_widths=null;
	private $_running_tx_channel_width=null;
	
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
			
			//this also ensures the interface exists, since we bypass any commands on some wireless interfaces
			$get_if_type	= new Thelist_Routeros_command_getinterfacetype($this->_device, $this->_interface);
			$if_type_obj	= $get_if_type->get_interface_type();
			
			if ($if_type_obj->get_if_type() == 'wireless') {
				
				$if_type = 'wireless';
				
			} elseif ($if_type_obj->get_if_type_id() == 92) {
				
				$if_type = 'nstreme_dual';
				
			} else {
				throw new exception("we dont know how to handle wireless interface channel tx width for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' ", 18800);
			}

		} else {
			$interface_name		= $this->_interface;
			
			//need another method for locating the type of interface when dealing with non object
		}
		
		if ($if_type == 'wireless') {
				
			//wireless
			$main_config_reply 	= $this->_device->execute_command("/interface wireless export");
			
			preg_match("/channel-width=(5|10|20|20\/40)mhz(-ht-(below|above))?.* name=".$interface_name." /", $main_config_reply->get_message(), $raw_channel_width);
			
			if (isset($raw_channel_width['1'])) {
					
				if ($raw_channel_width['1'] == '20/40') {
					$this->_configured_tx_channel_widths[]		 = 20;
					$this->_configured_tx_channel_widths[]		 = 40;
				} else {
					$this->_configured_tx_channel_widths[]		 = $raw_channel_width['1'];
				}
			} else {
				throw new exception("we cannot determine wireless interface channel tx width for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' ", 18801);
			}

				
		} elseif ($if_type == 'nstreme_dual') {
			
			//nstreme dual
			//make it
			
		}
	}
	

	public function get_configured_tx_channel_widths($refresh=true) 
	{
		if($this->_configured_tx_channel_widths == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		
		return $this->_configured_tx_channel_widths;
	}
	
	public function get_running_tx_channel_width($refresh=true)
	{		
		
		//not working yet
		throw new exception("we cannot determine running wireless interface channel tx width for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."', because it has not been built ", 18802);
		if($this->_running_tx_channel_width == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}

		return $this->_running_tx_channel_width;
	}
}