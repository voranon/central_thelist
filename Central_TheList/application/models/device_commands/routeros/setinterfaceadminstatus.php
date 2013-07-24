<?php

//exception codes 20500-20599

class thelist_routeros_command_setinterfaceadminstatus implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	private $_admin_status;
	private $_override;
	
	public function __construct($device, $interface, $admin_status, $override=false)
	{
		//$interface
		//object	= interface_obj
		//string	= interface name
		
		//$admin_status 0 = shut, 1 = enabled
		if ($admin_status != 0 && $admin_status != 1) {
			throw new exception("interface admin status can only be 0 (down) or 1 (up), you provided ".$admin_status." ", 20500);
		}

		$this->_device 					= $device;
		$this->_interface 				= $interface;
		$this->_admin_status			= $admin_status;
		$this->_override				= $override;
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

		$get_interface_status			= new Thelist_Routeros_command_getinterfacestatus($this->_device, $interface);
		$current_admin_status			= $get_interface_status->get_configured_admin_status();
		
		if ($current_admin_status != $this->_admin_status) {

			if ($this->_admin_status == 0) {
				
				if ($this->_override === false) {
					
					$get_management_interface_name			= new Thelist_Routeros_command_getmanagementinterfacename($this->_device);
					$management_interface_name				= $get_management_interface_name->get_management_interface_name();
					
					if ($management_interface_name == $interface_name) {
						throw new exception("you are trying to shutdown the management interface on interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' without override, bad, bad, bad", 20502);
					}
					
					//additional object model validation
					if (is_object($this->_interface)) {
						$slave_ifs = $this->_interface->get_master_relationships();
						
						if ($slave_ifs != null) {
							foreach ($slave_ifs as $slave_if) {
								
								if ($slave_if->get_if_name() == $management_interface_name) {
									throw new exception("you are trying to shutdown the management interface, by disabeling the master interface. on interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' without override, bad, bad, bad", 20503);
								}
							}
						}
					}
				}
				
				//if this is not management or override is set, shut it down
				$command = "/interface disable [find where name=\"".$interface_name."\"]";
				
			} else {
				$command = "/interface enable [find where name=\"".$interface_name."\"]";
			}

			$this->_device->execute_command($command);
			
			$verify			= $get_interface_status->get_configured_admin_status(true);
			
			if ($verify != $this->_admin_status) {
				throw new exception('interface admin status was not updated correctly', 20501);
			}
		} else {
			//all good already has the correct status
		}
	}
}