<?php

//exception codes 17500-17599

class thelist_cisco_command_getinterfacespanningtreestatus implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_interface;
	
	private $_native_vlan_spanning_tree_status=null;
	
	//everything in a single variable
	private $_spanning_tree_status=null;
	
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

		$get_op_status			= new Thelist_Cisco_command_getinterfacestatus($this->_device, $this->_interface);
		$interface_op_status	= $get_op_status->get_operational_status();
		
		if ($interface_op_status == 1) {

			//get the root folder
			$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
			$get_connection_root_folder->execute();
	
			if (is_object($this->_interface)) {
				$interface_name		= $this->_interface->get_if_name();
			} else {
				$interface_name		= $this->_interface;
			}
			
			$get_software		= new Thelist_Cisco_command_getsoftware($this->_device);
			$architecture		= $get_software->get_software_package_architecture();
	
			$get_native_vlan	= new Thelist_Cisco_command_getinterfacenativevlan($this->_device, $this->_interface);
			$native_vlan_id		= $get_native_vlan->get_configured_native_vlan_id();
	
			if ($native_vlan_id != null) {
				$stp_vlan_id = $native_vlan_id;
			} else {
				//expand to include access vlans
			}
			
			
			$i=0;
			while ($i < 5 && !isset($got_stp_info)) {
			
				if ($architecture == 'C3550') {
					$device_reply = $this->_device->execute_command("show spanning-tree vlan ".$stp_vlan_id." interface ".$interface_name." detail");
				} elseif ($architecture == 'C3500XL' || $architecture == 'C2900XL') {
					$device_reply = $this->_device->execute_command("show spanning-tree vlan ".$stp_vlan_id." interface ".$interface_name."");
				} elseif ($architecture == 'C2960S') {
					throw new exception('we have not yet coded the commands for stp on 2960 series', 17501);
				}
				
				
				if (preg_match("/no spanning tree info available for/", $device_reply->get_message())) {
					//if an interface has just been switched from access to trunk, we get this answer
					//wait a few sec and we will get a proper response
					sleep(2);
				} else {
					$got_stp_info = 1;
				}
				$i++;
			}
			
			if ($architecture == 'C3550') {
				
				if (preg_match("/Port +([0-9]+) +(\(.*)\) +of +VLAN([0-9]+) +is +designated (listening|learning|forwarding)/", $device_reply->get_message(), $result1)) {
						
					if ($result1['4'] == 'learning') {
						$this->_native_vlan_spanning_tree_status				= 'learning';
						$this->_spanning_tree_status['native_vlan_status']		= 'learning';
					} elseif ($result1['4'] == 'forwarding') {
						$this->_native_vlan_spanning_tree_status				= 'forwarding';
						$this->_spanning_tree_status['native_vlan_status']		= 'forwarding';
					} elseif ($result1['4'] == 'listening') {
						$this->_native_vlan_spanning_tree_status				= 'listening';
						$this->_spanning_tree_status['native_vlan_status']		= 'listening';
					}
				
				} else {
					throw new exception('interface spanningtree learning status could not be determined for interface on device', 17500);
				}
				
			} elseif ($architecture == 'C3500XL' || $architecture == 'C2900XL') {
	
				if (preg_match("/Interface +(.*) +(.*) +in +Spanning +tree +".$native_vlan_id." is (FORWARDING|LEARNING|LISTENING)", $device_reply->get_message(), $result1)) {
						
					if ($result1['3'] == 'LEARNING') {
						$this->_native_vlan_spanning_tree_status				= 'learning';
						$this->_spanning_tree_status['native_vlan_status']		= 'learning';
					} elseif ($result1['3'] == 'FORWARDING') {
						$this->_native_vlan_spanning_tree_status				= 'forwarding';
						$this->_spanning_tree_status['native_vlan_status']		= 'forwarding';
					} elseif ($result1['3'] == 'LISTENING') {
						$this->_native_vlan_spanning_tree_status				= 'listening';
						$this->_spanning_tree_status['native_vlan_status']		= 'listening';
					}
						
				} else {
					throw new exception('interface spanningtree learning status could not be determined for interface on device', 17502);
				}
				
			} elseif ($architecture == 'C2960S') {
				throw new exception('we have not yet coded the commands for stp on 2960 series', 17503);
			}
		} else {
			throw new exception("you are trying to get spanning tree status for interface ".$interface_name." on device ".$this->_device->get_fqdn().", but the interface is down, so no status can be provided" , 17504);
		}

		//there are many more details in the ouptput of this command
		//expand as needed
	}
	
	public function get_native_vlan_spanning_tree_status($refresh=true) 
	{
		if($this->_native_vlan_spanning_tree_status == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		
		return $this->_native_vlan_spanning_tree_status;
	}
	
	public function get_spanning_tree_status($refresh=true)
	{
		//append new vaiables to this as we add them
		
		if($this->_spanning_tree_status == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
	
		return $this->_spanning_tree_status;
	}

}