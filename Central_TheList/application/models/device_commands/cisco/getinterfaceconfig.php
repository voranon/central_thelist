<?php

//exception codes 9700-9799

class thelist_cisco_command_getinterfaceconfig implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	
	private $_interface_config=null;
	
	public function __construct($device, $interface)
	{
		//$interface
		//object	= interface_obj
		//string	= ['interface_name']
		//string	= ['interface_type']
		
		$this->_device 			= $device;
		$this->_interface 		= $interface;
	}
	
	public function execute()
	{		
		//get the root folder
		$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
		$get_connection_root_folder->execute();
		
		//if we are dealing with an object
		if (is_object($this->_interface)) {
			$interface				= $this->_interface;
			$type_of_interface		= $this->_interface->get_if_type_id();
			
			if ($type_of_interface == 28) {
				$if_type = 'SVI';
			} else {
				$if_type = null;
			}
			
		} else {
			
			$interface			= $this->_interface['interface_name'];
			$type_of_interface	= $this->_interface['interface_type'];
			
			if ($type_of_interface == 'SVI') {
				$if_type = 'SVI';
			} else {
				$if_type = null;
			}
		}

		//cisco SVI interface is if_type=28, it must be handled differently than any physical interface 
		if ($if_type == 'SVI') {

			$transit_vlans				= new Thelist_Cisco_command_gettransitvlans($this->_device);
			$device_transit_vlans		= $transit_vlans->get_transit_vlans();

			if ($device_transit_vlans != null) {
				$return['configuration']['switch_allowed_transit_vlans'] = $device_transit_vlans;
			}

		} elseif ($if_type != 'SVI') {

			//admin status
			$status = new Thelist_Cisco_command_getinterfacestatus($this->_device, $interface);
			$return['configuration']['administrative_status'] = $status->get_configured_admin_status();
			
			//duplex
			$duplex = new Thelist_Cisco_command_getinterfaceduplex($this->_device, $interface);
			$return['configuration']['duplex'] = $duplex->get_configured_duplex();
			
			//layer 2 mtu
			$l2mtu = new Thelist_Cisco_command_getinterfacelayer2mtu($this->_device, $interface);
			$return['configuration']['l2_mtu'] = $l2mtu->get_configured_layer2mtu();
			
			//speed
			$speed = new Thelist_Cisco_command_getinterfacespeed($this->_device, $interface);
			$return['configuration']['speed'][] = $speed->get_configured_speed();
			
			//switch port mode
			$interface_mode = new Thelist_Cisco_command_getinterfaceswitchportmode($this->_device, $interface);
			$return['configuration']['interface_mode'] = $interface_mode->get_configured_switch_port_mode();
			
			//switch port native vlan
			$native_vlan = new Thelist_Cisco_command_getinterfacenativevlan($this->_device, $interface);
			if ($native_vlan->get_configured_native_vlan_id() != null) {
				$return['configuration']['switch_port_native_vlan'] = $native_vlan->get_configured_native_vlan_id();
			}
			
			//vlan ids allowed to trunk
			$allowed_vlans = new Thelist_Cisco_command_getinterfaceswitchportallowedvlans($this->_device, $interface);
			if ($allowed_vlans->get_allowed_vlans() != null) {
				$return['configuration']['switch_port_vlans_allowed_trunking'] = $allowed_vlans->get_allowed_vlans();
			}
			
			//switchport encapsulation.
			$trunk_encapsulation = new Thelist_Cisco_command_getinterfaceswitchportencapsulation($this->_device, $interface);
			if ($trunk_encapsulation->get_configured_trunk_encapsulation() != null) {
				$return['configuration']['switch_port_encapsulation'] = $trunk_encapsulation->get_configured_trunk_encapsulation();
			}
			
			//interface description
			$interface_description = new Thelist_Cisco_command_getinterfacedescription($this->_device, $interface);
			if ($interface_description->get_configured_description() != null) {
				$return['configuration']['interface_description'] = $interface_description->get_configured_description();
			}
		}

		if (isset($return)) {
			$this->_interface_config = $return;
			return $return;
		} else {
			$this->_interface_config = null;
			return false;
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

