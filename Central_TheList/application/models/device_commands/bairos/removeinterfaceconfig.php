<?php

//exception codes 4500-4599

class thelist_bairos_command_removeinterfaceconfig implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_interface;
	private $_override_management=false;
	
	public function __construct($device, $interface, $override_management=false)
	{
		//$interface
		//object	= interface_obj
		//string	= interface name
		//$override_management 1 allows the removal of management interface
		
		$this->_device 						= $device;
		$this->_interface 					= $interface;
		$this->_override_management			= $override_management;
	}
	
	public function execute()
	{		
		if (is_object($this->_interface)) {
			$interface_name			= $this->_interface->get_if_name();
			
			//get slaves to this interface (like vlans), we need these for later validations
			$slave_interfaces	= $this->_interface->get_master_relationships();
			
		} else {
			$interface_name			= $this->_interface;
		}

		//check if this is the managemnt interface
		$management_interface			= new Thelist_Bairos_command_getmanagementinterfacename($this->_device);
		$management_interface_name		= $management_interface->get_management_interface_name();

		if ($management_interface_name == $interface_name && $this->_override_management != 1) {
			throw new exception("you are trying to remove the management interface on ".$this->_device->get_fqdn()." without override, bad,bad,bad ", 4502);
		} elseif (is_object($this->_interface) && $this->_override_management != 1) {
			//object model only check

			if ($slave_interfaces != null) {
				
				foreach($slave_interfaces as $slave_interface) {
					
					if ($slave_interface->get_if_name() == $management_interface_name) {
						throw new exception("you are trying to remove the parent of the mangement interface on ".$this->_device->get_fqdn()." without override, bad,bad,bad ", 4500);
					}
				}
			}
		}
		
		//object model check only
		if (is_object($this->_interface)) {

			if ($slave_interfaces != null) {
		
				foreach($slave_interfaces as $slave_interface) {
						
					//if the slave interface config file still exists on the device we cannot remove this interface
					try {
		
						$current_status = new Thelist_Bairos_command_getinterfacestatus($this->_device, $slave_interface);
						$current_admin_status	= $current_status->get_configured_admin_status();
		
						//if the above function does not throw an exception then we do, there should not be a config file for the slave interface
						throw new exception("you are trying to remove an interface on ".$this->_device->get_fqdn()." but this interface has slaves that have active config files if_id: ".$this->_interface->get_if_id()." ", 4503);
		
					} catch (Exception $e) {
							
						switch($e->getCode()){
		
							case 11901;
							//11901, this means config file for the interface does not exist, we like that, all slave interfaces should have been removed before this one
							break;
							default;
							throw $e;
		
						}
					}
				}
			}
		}
		
		try {
		
			//get the current status
			$current_status = new Thelist_Bairos_command_getinterfacestatus($this->_device, $this->_interface);
			$current_admin_status	= $current_status->get_configured_admin_status();
		
			//if the interface is currently running we must down it first, before removing the config file
			if ($current_admin_status != 0) {
				$down_interface	= new Thelist_Bairos_command_setinterfaceadminstatus($this->_device, $this->_interface, 0);
				$down_interface->execute();
			}
			
			//on linux we need to treat eth1.10 and eth1.10:0 as a single interface
			//they are just the implementation of adding ips to interfaces but they
			//have their own config file and show up seperately
			//so deleting one file means deleting all
			
			//add the original interface to the array
			$all_interface_names[]	= $interface_name;
			
			$get_network_configs	= new Thelist_Bairos_command_getfilelist($this->_device, '/etc/sysconfig/network-scripts/');
			$files = $get_network_configs->get_files();
			
			if ($files != false) {
					
				foreach($files['files'] as $file) {
			
					if(preg_match("/ifcfg-(".$interface_name.":[0-9]+)/", $file['file_name'], $result)) {
						$all_interface_names[]	= $result['1'];
					}
				}
			}
			
			foreach ($all_interface_names as $single_config_file) {
				//now delete the config file(s)
				$this->_device->execute_command("rm -rf /etc/sysconfig/network-scripts/ifcfg-".$single_config_file."");
			}
			
			//validate
			$after_files = $get_network_configs->get_files();
				
			if ($after_files != false) {
					
				foreach($after_files['files'] as $file) {
						
					if(preg_match("/ifcfg-(".$interface_name.":[0-9]+)/", $file['file_name'], $result)) {
						throw new exception("interface was not removed sucessfully", 4502);
					}
				}
			}
			
		} catch (Exception $e) {
				
			switch($e->getCode()){
					
				case 11901;
				//11901, this means config file for the interface does not exist, we are done
				//(need to validate more and check if the interface is running, even though the file is not there)
				return;
				break;
				default;
				throw $e;
					
			}
		}
	}
}