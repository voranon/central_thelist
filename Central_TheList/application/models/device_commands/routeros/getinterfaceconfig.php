<?php

//exception codes 8300-8399

class thelist_routeros_command_getinterfaceconfig implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	
	private $_interface_config=null;
	
	public function __construct($device, $interface)
	{
		//$interface
		//object	= interface_obj
		//string	= ['interface_name']
		
		$this->_device 			= $device;
		$this->_interface 		= $interface;
	}
	
	public function execute()
	{		
		if (is_object($this->_interface)) {
			
			$interface_name			= $this->_interface->get_if_name();
			$interface				= $this->_interface;
				
		} else {
			$interface_name			= $this->_interface;
			$interface				= $this->_interface;
		}
		
		//this also ensures the interface exists, since we bypass any commands on some wireless interfaces
		$get_if_type	= new Thelist_Routeros_command_getinterfacetype($this->_device, $this->_interface);
		$if_type		= $get_if_type->get_routeros_specific_if_type_name();
		
		if ($if_type == 'ethernet' || $if_type == 'wireless' || $if_type == 'vlan') {
			//append as we add commands for interface types
		} else {
			throw new exception("we dont know how to get interface config interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."', expand method ", 8301);
		}

		//admin status
		$status = new Thelist_Routeros_command_getinterfacestatus($this->_device, $interface);
		$return['configuration']['administrative_status'] = $status->get_configured_admin_status();
		
		//duplex
		$duplex = new Thelist_Routeros_command_getinterfaceduplex($this->_device, $interface);
		if ($duplex->get_configured_duplex() != null) {
			$return['configuration']['duplex'] = $duplex->get_configured_duplex(false);
		}
		
		//speed
		$speed = new Thelist_Routeros_command_getinterfacespeed($this->_device, $interface);
		if ($speed->get_configured_speed() != null) {
			$return['configuration']['speed'][] = $speed->get_configured_speed(false);
		}
		
		//description
		$interface_description = new Thelist_Routeros_command_getinterfacedescription($this->_device, $interface);
		if ($interface_description->get_configured_description() != null) {
			$return['configuration']['interface_description'] = $interface_description->get_configured_description();
		}
		
		//vlan id
		$interface_vlan_id = new Thelist_Routeros_command_getinterfacevlanid($this->_device, $interface);
		if ($interface_vlan_id->get_vlan_id() != null) {
			$return['configuration']['interface_vlan_id'] = $interface_vlan_id->get_vlan_id();
		}
		
		//ip adress
		$interface_ips = new Thelist_Routeros_command_getinterfaceipaddresses($this->_device, $interface);
		if($interface_ips->get_ip_addresses() != null) {
		
			if (!isset($return['configuration']['interface_ip_addresses'])) {
				$return['configuration']['interface_ip_addresses'] = $interface_ips->get_ip_addresses();
			} else {
				$return['configuration']['interface_ip_addresses'] = array_merge($return['configuration']['interface_ip_addresses'], $interface_ips->get_ip_addresses());
			}
		}
		
		//layer 3 mtu
		$l3mtu = new Thelist_Routeros_command_getinterfacelayer3mtu($this->_device, $interface);
		if($l3mtu->get_configured_layer3mtu() != null) {
			$return['configuration']['l3_mtu'] = $l3mtu->get_configured_layer3mtu(false);
		}
		
		//layer 2 mtu
		$l2mtu = new Thelist_Routeros_command_getinterfacelayer2mtu($this->_device, $interface);
		if($l2mtu->get_configured_layer2mtu() != null) {
			$return['configuration']['l2_mtu'] = $l2mtu->get_configured_layer2mtu(false);
		}
		
		
		//wireless specific configurations
		if ($if_type == 'wireless') {
			
			$ssid = new Thelist_Routeros_command_getinterfacessid($this->_device, $interface);
			$return['configuration']['ssid'] = $ssid->get_ssid();
			
			$wireless_protocols = new Thelist_Routeros_command_getinterfacesupportedwirelessprotocols($this->_device, $interface);
			$return['configuration']['wireless_protocols'] = $wireless_protocols->get_supported_wireless_protocols();
			
			$wireless_mode = new Thelist_Routeros_command_getinterfacewirelessmode($this->_device, $interface);
			$return['configuration']['interface_mode'] = $wireless_mode->get_wireless_mode();
			
			$wireless_security_profile = new Thelist_Routeros_command_getinterfacewirelesssecurityprofile($this->_device, $interface);
			$return['configuration']['wireless_security_profile'] = $wireless_security_profile->get_profile();
			
			$wireless_tx_center_freq = new Thelist_Routeros_command_getinterfacewirelesstxcenterfrequency($this->_device, $interface);
			$return['configuration']['wireless_tx_center_frequency'] = $wireless_tx_center_freq->get_wireless_tx_center_frequency();
			
			$wireless_tx_channel_widths = new Thelist_Routeros_command_getinterfacewirelesstxchannelwidth($this->_device, $interface);
			$return['configuration']['wireless_tx_channel_widths'] = $wireless_tx_channel_widths->get_configured_tx_channel_widths();

		}
		
		if (isset($return)) {
			$this->_interface_config = $return;
		} else {
			$this->_interface_config = null;
		}

	}
	
	public function get_interface_config($refresh=true)
	{
		if($this->_interface_config == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		
		return $this->_interface_config;
	}
}

