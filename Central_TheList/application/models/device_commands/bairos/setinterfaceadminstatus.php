<?php

//exception codes 11800-11899

class thelist_bairos_command_setinterfaceadminstatus implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	private $_admin_status;
	private $_override_management=0;
	
	public function __construct($device, $interface, $admin_status, $override_management=0)
	{
		//$interface
		//object	= interface_obj
		//string	= interface name
		
		//$admin_status 0 = shut, 1 = enabled

		$this->_device 					= $device;
		$this->_interface 				= $interface;
		$this->_admin_status			= $admin_status;
		$this->_override_management		= $override_management;
	}
	
	public function execute()
	{
		//if we start seeing alot of the "RTNETLINK answers: No such device"
		//then we need to add this line to /etc/sysconfig/network
		//NOZEROCONF=yes
		//it removes the 169.254.0.0/16 route that is there by default
		
		if (is_object($this->_interface)) {
			$interface_name			= $this->_interface->get_if_name();
		} else {
			$interface_name			= $this->_interface;
		}
		
		//get current status
		$interface_status		= new Thelist_Bairos_command_getinterfacestatus($this->_device, $this->_interface);
		$current_admin_status	= $interface_status->get_configured_admin_status();

		if ($current_admin_status != $this->_admin_status) {
			
			//are we shutting down an interface?
			if ($this->_admin_status == 0) {
				//check if this is the managemnt interface
				$management_interface			= new Thelist_Bairos_command_getmanagementinterfacename($this->_device);
				$management_interface_name		= $management_interface->get_management_interface_name();

				if ($management_interface_name == $interface_name && $this->_override_management != 1) {
					throw new exception("you are trying to shutdown management interface on ".$this->_device->get_fqdn()." without override, bad,bad,bad ", 11802);
				} elseif (is_object($this->_interface) && $this->_override_management != 1) {
					//object model only check

					//get slaves to this interface (like vlans)
					$slave_interfaces	= $this->_interface->get_master_relationships();
					
					if ($slave_interfaces != null) {
						
						foreach($slave_interfaces as $slave_interface) {
							
							if ($slave_interface->get_if_name() == $management_interface_name) {
								throw new exception("you are trying to shutdown the parent of the mangement interface on ".$this->_device->get_fqdn()." without override, bad,bad,bad ", 11803);
							}
						}
					}
				}
			}

			//when downing i.e. eth1 then automatically eth1:0 eth1:1 etc are also downed
			//so no need to search for all of them and down the extended interfaces
			
			if ($this->_admin_status == 0) {
				$mm = $this->_device->execute_command("ifdown ".$interface_name."");
			} elseif ($this->_admin_status == 1) {
				$this->_device->execute_command("ifup ".$interface_name."");
			} else {
				throw new exception('interface admin status given is unknown cannot set', 11800);
			}
			
			$verify			= $interface_status->get_configured_admin_status();
			
			if ($verify != $this->_admin_status) {
				throw new exception('interface admin status was not updated correctly', 11801);
			}
		}
	}
}