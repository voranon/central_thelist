<?php

//exception codes 12300-12399

class thelist_bairos_command_getinterfaceconfig implements Thelist_Commander_pattern_interface_idevicecommand 
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
		
		//if we are dealing with an object
		if (is_object($this->_interface)) {
			$interface_name			= $this->_interface->get_if_name();
			$interface				= $this->_interface;
		} else {
			$interface_name			= $this->_interface;
			$interface				= $this->_interface;
		}
		
		//on linux we need to treat eth1.10 and eth1.10:0 as a single interface
		//they are just the implementation of adding ips to interfaces but they 
		//have their own config file and show up seperately 
		//so this is purely a matter of getting all the ip addresses, since we will
		//be returning a single config.

		
		//if the file does not exist there is no reason to try and get the config
		$get_network_configs	= new Thelist_Bairos_command_getfilelist($this->_device, '/etc/sysconfig/network-scripts/');
		$main_file_exist	= $get_network_configs->get_file("ifcfg-".$interface_name."");
		
		if ($main_file_exist != false) {
			
			//add the original interface to the array
			$all_interface_names[]	= $interface_name;
			
			//dont refresh the result set, nothing has changed so no need to
			$files = $get_network_configs->get_files(false);
			
			if ($files != false) {
				
				foreach($files['files'] as $file) {
					
					if(preg_match("/ifcfg-(".$interface_name.":[0-9]+)/", $file['file_name'], $result)) {
						$all_interface_names[]	= $result['1'];
					}
				}
			}

			//everything else is dictated by the main interface
			
			//admin status
			$status = new Thelist_Bairos_command_getinterfacestatus($this->_device, $interface);
			$return['configuration']['administrative_status'] = $status->get_configured_admin_status();
			
			//vlan_id 
			$vlan_id = new Thelist_Bairos_command_getinterfacevlanid($this->_device, $interface);
			if($vlan_id->get_vlan_id() != null) {
				$return['configuration']['interface_vlan_id'] = $vlan_id->get_vlan_id();
			} else {
				
				//if this is not a vlan then it can have attributes such as speed and duplex maybe
				
				//speed
				$speed = new Thelist_Bairos_command_getinterfacespeed($this->_device, $interface);
				$return['configuration']['speed'][] = $speed->get_configured_speed();
					
				//duplex
				$duplex = new Thelist_Bairos_command_getinterfaceduplex($this->_device, $interface);
				$return['configuration']['duplex'] = $duplex->get_configured_duplex();
				
			}
				
			//boot protocol
			$interface_boot_protocol = new Thelist_Bairos_command_getinterfacebootprotocol($this->_device, $interface);
			if($interface_boot_protocol->get_boot_protocol() != null) {
				$return['configuration']['interface_boot_protocol'] = $interface_boot_protocol->get_boot_protocol();
			}

			//layer 3 mtu
			$l3mtu = new Thelist_Bairos_command_getinterfacelayer3mtu($this->_device, $interface);
			if($l3mtu->get_configured_layer3mtu() != null) {
				$return['configuration']['l3_mtu'] = $l3mtu->get_configured_layer3mtu();
			}
			
			//interface description
			$interface_description = new Thelist_Bairos_command_getinterfacedescription($this->_device, $interface);
			if ($interface_description->get_configured_description() != null) {
				$return['configuration']['interface_description'] = $interface_description->get_configured_description();
			}
			
			foreach ($all_interface_names as $single_interface_name) {
				//ip adress
				$interface_ips = new Thelist_Bairos_command_getinterfaceipaddresses($this->_device, $single_interface_name);
				if($interface_ips->get_ip_addresses() != null) {
					
					if (!isset($return['configuration']['interface_ip_addresses'])) {
						$return['configuration']['interface_ip_addresses'] = $interface_ips->get_ip_addresses();
					} else {
						$return['configuration']['interface_ip_addresses'] = array_merge($return['configuration']['interface_ip_addresses'], $interface_ips->get_ip_addresses());
					}
				}
			}
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

