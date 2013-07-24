<?php

//exception codes 1200-1299

//the connectors we use are not throwing exceptions that can be handled so we convert them here.


class thelist_model_device
{
	private $_fqdn;
	private $_auth_obj;
	private $_api_name=null;
	private $_api_id=null;
	private $_device_connection;
	private $_device_type;
	private $_command_return=null;
	private $_command_return_count='0';
	private $_specific_connect_class=null;

	public function __construct($fqdn, $device_authentication_credentials)
	{
		
		$this->_fqdn 						= $fqdn;
		$this->_auth_obj 					= $device_authentication_credentials;
		$this->_specific_connect_class		= $device_authentication_credentials->get_specific_connect_class();
	
		$this->_api_name = $this->_auth_obj->get_device_api_name();
		$this->_api_id	 = $this->_auth_obj->get_api_id();

		try {
		
			if ($this->_api_name == 'ssh' || $this->_api_id == 1) {
				
				//test if port 22 is up
				$fsock = @fsockopen($this->_fqdn, 22, $errno, $errstr, 0.2);
				
				if (!$fsock) {
					throw new exception("Host Unreachable for Device: '".$this->_fqdn."' using sockconn test", 1202);
				} else {
					$this->_device_connection = $this->ssh_enabled_device_classes();
				}

			} elseif ($this->_api_name == 'telnet' || $this->_api_id == 2) {
				
				//test if port 23 is up
				$fsock = @fsockopen($this->_fqdn, 23, $errno, $errstr, 0.2);
				
				if (!$fsock) {
					throw new exception("Host Unreachable for Device: '".$this->_fqdn."' using sockconn test", 1202);
				} else {
					$this->_device_connection = $this->telnet_enabled_device_classes();
				}

			} elseif ($this->_api_name == 'http' || $this->_api_id == 4) {
				$this->_device_connection = $this->http_enabled_device_classes();
			} elseif ($this->_api_name == 'sftp' || $this->_api_id == 7) {
				$this->_device_connection = $this->sftp_enabled_device_classes();
			} elseif ($this->_api_name == 'scp' || $this->_api_id == 8) {
				
				//test if port 22 is up
				$fsock = @fsockopen($this->_fqdn, 22, $errno, $errstr, 0.2);
				
				if (!$fsock) {
					throw new exception("Host Unreachable for Device: '".$this->_fqdn."' using sockconn test", 1202);
				} else {
					$this->_device_connection = $this->scp_enabled_device_classes();
				}

				
			} elseif ($this->_api_name == 'mikrotik_api' || $this->_api_id == 3) {
				$this->_device_connection = new Thelist_Model_devicerouteros($this->_fqdn, $this->_auth_obj);
			} else {
				throw new exception("we dont know the api used to connect to this device", 1201);
			}
			
		
			$this->_device_type = $this->_device_connection->get_device_type();

		} catch (Exception $e) {
		
			//these are the catch all errors and convert them to a simple error that can be handled in the source code
			//1202 is host unreachable, 1203 is authentication error, 1204 is file does not exist
			
			switch($e->getCode()){
				
				case 7101;
				//error is caught before the handler can be restored
				//so we restore here 
				restore_error_handler();
				throw new exception("Host Unreachable for Device: '".$this->_fqdn."' ", 1202);
				break;
				case 7102;
				//error is caught before the handler can be restored
				//so we restore here
				restore_error_handler();
				//ssh connection timed out
				throw new exception("Host Unreachable for Device: '".$this->_fqdn."' ", 1202);
				break;
				case 7104;
				//error is caught before the handler can be restored
				//so we restore here
				restore_error_handler();
				//telnet connection timed out
				throw new exception("Host Unreachable for Device: '".$this->_fqdn."' ", 1202);
				break;
				case 7105;
				//error is caught before the handler can be restored
				//so we restore here
				restore_error_handler();
				//telnet connection timed out
				throw new exception("Host Unreachable for Device: '".$this->_fqdn."' ", 1202);
				break;
				case 7110;
				//error is caught before the handler can be restored
				//so we restore here
				restore_error_handler();
				//local file does not exist
				throw new exception("Cannot upload. File does not exist: '".$this->_fqdn."' ", 1204);
				break;
				case 16600;
				//error is caught but only after we restore the handler
				//so no need to restore it here
				//ssh phpseclib auth failure
				throw new exception("Authentication Failure for Device: '".$this->_fqdn."' ", 1203);
				case 21900;
				//error is caught but only after we restore the handler
				//so no need to restore it here
				//ssh phpseclib auth failure
				throw new exception("Authentication Failure for Device: '".$this->_fqdn."' ", 1203);
				case 3706;
				//error is caught but only after we restore the handler
				//so no need to restore it here
				//telnet cisco wrong telnet pass
				throw new exception("Authentication Failure for Device: '".$this->_fqdn."' ", 1203);
				break;
				default;
				throw $e;
			
			}
		}	
	}
	
	public function get_fqdn()
	{
		return $this->_fqdn;
	}
	
	public function get_device_authentication_credentials()
	{
		return $this->_auth_obj;
	}
	
	public function get_connection_status()
	{
		if ($this->_device_connection == false) {
			return false;
		} else {
			return true;
		}
		
	}
	
	//device commands
	
	public function get_interface_db_sync_status($interface_obj)
	{
		if ($this->_device_type == 'cisco') {
			
			$equipment_obj  = new Thelist_Model_equipments($interface_obj->get_eq_id());
				
			$database_interface_config		= new Thelist_Cisco_config_interface($equipment_obj, $interface_obj);
			$db_config						= $database_interface_config->generate_config_array();
			
			$device_interface_config		= new Thelist_Cisco_command_getinterfaceconfig($this, $interface_obj);
			$dev_config						= $device_interface_config->get_interface_config(true);
				
			$compare						= new Thelist_Multipledevice_config_configdifferences($db_config, $dev_config);
			$compare_result					= $compare->generate_config_array();

			if ($compare_result == false) {
				return true;
			} else {
				return false;
			}

		} elseif ($this->_device_type == 'bairos') {
			
			$equipment_obj  = new Thelist_Model_equipments($interface_obj->get_eq_id());
			
			$database_interface_config		= new Thelist_Bairos_config_interface($equipment_obj, $interface_obj);
			$db_config						= $database_interface_config->generate_config_array();

			$device_interface_config		= new Thelist_Bairos_command_getinterfaceconfig($this, $interface_obj);
			$dev_config						= $device_interface_config->get_interface_config(true);
			
			$compare						= new Thelist_Multipledevice_config_configdifferences($db_config, $dev_config);
			$compare_result					= $compare->generate_config_array();
			
			if ($compare_result == false) {
				return true;
			} else {
				return false;
			}
			
		} elseif ($this->_device_type == 'routeros') {
		
			$equipment_obj  = new Thelist_Model_equipments($interface_obj->get_eq_id());
			
			$database_interface_config		= new Thelist_Routeros_config_interface($equipment_obj, $interface_obj);
			$db_config						= $database_interface_config->generate_config_array();

			$device_interface_config		= new Thelist_Routeros_command_getinterfaceconfig($this, $interface_obj);
			$dev_config						= $device_interface_config->get_interface_config(true);
			
			$compare						= new Thelist_Multipledevice_config_configdifferences($db_config, $dev_config);
			$compare_result					= $compare->generate_config_array();
			
			if ($compare_result == false) {
				return true;
			} else {
				return false;
			}
		
		} else {
		
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
		
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
		
		}
		
	}

	public function configure_interface($interface_obj)
	{
		//allows us to sync the equipment interface to the device interface
		//in case of subinterfaces or software interfaces i.e. vlans if they do not exist then they will be created
		//this method also configures the cisco SVI interface which allows / blocks vlans from transsitting the switch

		if ($this->_device_type == 'cisco') {
	
			$configureinterface = new Thelist_Cisco_command_setinterfaceconfig($this, $interface_obj);
			return $configureinterface->execute();
	
		} elseif ($this->_device_type == 'bairos') {
			
			$configureinterface = new Thelist_Bairos_command_setinterfaceconfig($this, $interface_obj);
			return $configureinterface->execute();
			
		} elseif ($this->_device_type == 'routeros') {
					
			$configureinterface = new Thelist_Routeros_command_setinterfaceconfig($this, $interface_obj);
			return $configureinterface->execute();
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
		}
	}
	
	public function reset_config($file_name=null)
	{
		//allows us to reset a device to a specified config
		//each commander class also has a default config incase the filename is null 
		if ($this->_device_type == 'routeros') {
				
			$reset_config = new Thelist_Routeros_command_resetconfig($this, $file_name);
			return $reset_config->execute();
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
		}
	}
	
	public function configure_application($application_obj)
	{
		//allows us to sync the equipment interface to the device interface
		//in case of subinterfaces or software interfaces i.e. vlans if they do not exist then they will be created
		//this method also configures the cisco SVI interface which allows / blocks vlans from transsitting the switch
	
		if ($application_obj->get_equipment_application_id() == 1) {
			
			
			//DHCP servers
			if ($this->_device_type == 'bairos') {
			
				$configuredhcpserver = new Thelist_Bairos_command_setdhcpserverconfig($this, $application_obj);
				return $configuredhcpserver->execute();
			
			} 
		}

		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
	
		throw new exception("".$method." is not defined for ".$this->_device_type." on equipment_application_id ".$application_obj->get_equipment_application_id()."", 1200);
	
	}

	public function copy_file_to_device($local_file_path, $local_file_name, $destination_file_path, $destination_file_name)
	{	
		//if the file must be exposed through http or other means make sure to delete it after upload
		if ($this->_device_type == 'cisco') {

		} elseif ($this->_device_type == 'bairos') {

		} elseif ($this->_device_type == 'directvstb') {

		} elseif ($this->_device_type == 'routeros') {
			
			$upload_file = new Thelist_Routeros_command_uploadfile($this, $local_file_path, $local_file_name, $destination_file_path, $destination_file_name);
			return $upload_file->execute();

		} elseif ($this->_device_type == 'linuxserver') {
			
			$upload_file = new Thelist_Linuxserver_command_uploadfile($this, $local_file_path, $local_file_name, $destination_file_path, $destination_file_name);
			return $upload_file->execute();

		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
		}
	}

	public function set_interface_queue_filter_on_device($connection_queue_filter_obj)
	{
		if ($this->_device_type == 'bairos') {
	
			$device_queue_filter		= new Thelist_Bairos_command_setconnectionqueuefilterondevice($this, $connection_queue_filter_obj);
			return $device_queue_filter->execute();
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	
	public function get_dhcp_server_status($interface_obj)
	{
		if ($this->_device_type == 'bairos') {
	
			$dhcp_server_status		= new Thelist_Bairos_command_getdhcpserverstatus($this, $interface_obj);
			return $dhcp_server_status->get_dhcp_server_operational_status(true);
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	
	public function get_interfaces()
	{
		if ($this->_device_type == 'bairos') {
	
			$get_all_interfaces		= new Thelist_Bairos_command_getinterfaces($this);
			
			$return['running_interfaces'] 		= $get_all_interfaces->get_running_interfaces(true);
			$return['configured_interfaces'] 	= $get_all_interfaces->get_configured_interfaces(true);
	
			return $return;
			
		} elseif ($this->_device_type == 'routeros') {
	
			$get_all_interfaces		= new Thelist_Routeros_command_getinterfaces($this);
			
			$return['running_interfaces'] 		= $get_all_interfaces->get_running_interfaces(true);
			$return['configured_interfaces'] 	= $get_all_interfaces->get_configured_interfaces(true);
	
			return $return;
			
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	
	public function remove_interface($interface, $override_management=false)
	{
		if ($this->_device_type == 'bairos') {
	
			$remove_interface	= new Thelist_Bairos_command_removeinterfaceconfig($this, $interface, $override_management);
			$remove_interface->execute();
				
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	
	public function set_interface_queue_disc_on_device($interface_obj)
	{
		if ($this->_device_type == 'bairos') {
	
			$device_qdisc_queue		= new Thelist_Bairos_command_setinterfacequeuediscondevice($this, $interface_obj);
			return $device_qdisc_queue->execute();
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	
	public function remove_interface_queue_disc_from_device($interface_obj)
	{
		if ($this->_device_type == 'bairos') {
	
			$device_qdisc_queue		= new Thelist_Bairos_command_removeinterfacequeuediscfromdevice($this, $interface_obj);
			return $device_qdisc_queue->execute();
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	
	public function get_device_connection_queue_discs($interface_obj=null)
	{
		if ($this->_device_type == 'bairos') {
	
			$device_connection_queues		= new Thelist_Bairos_command_getconnectionqueuediscsfromdevice($this, $interface_obj);
			$device_connection_queues->execute();
			return $device_connection_queues->get_all_queue_discs();
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	
	public function remove_connection_queue_from_device($connection_queue_obj)
	{
		if ($this->_device_type == 'bairos') {
	
			$device_connection_queue		= new Thelist_Bairos_command_removeconnectionqueuefromdevice($this, $connection_queue_obj);
			return $device_connection_queue->execute();
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	
	public function set_default_provisioning_on_switchport_interface($interface_obj)
	{
		if ($this->_device_type == 'cisco') {
			
			//purpose is to allow us to scan devices that are on ports that are not currently configured for provisioning
			//but not modifying the database
			
			//this is used to override or set the native vlan with vlan 20 as well as append the allowed list of vlans
			//with vlan 20. this will not be reflected in the database so next time the interface is updated
			//using the database the allowed is removed again.
			$old_config				= new Thelist_Cisco_command_getinterfaceconfig($this, $interface_obj);
			$old_if_config			= $old_config->execute();
			
			//rebuild the config with modifications
			$old_if_config['configuration']['switch_port_mode'] 						= 'trunk';
			$old_if_config['configuration']['switch_port_native_vlan'] 					= 20;
			$old_if_config['configuration']['switch_port_encapsulation'] 				= 'dot1q';
			$old_if_config['configuration']['switch_port_vlans_allowed_trunking'][]		= 20;
			
			$new_config['interface_name'] 	= $interface_obj->get_if_name();
			$new_config['new_config'] 		= $old_if_config;
			
			$push_new_config		= new Thelist_Cisco_command_setinterfaceconfig($this, $new_config);
			$push_new_config->execute();

		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	
	public function set_connection_queue_on_device($connection_queue_obj)
	{
		if ($this->_device_type == 'bairos') {
	
			$device_connection_queue		= new Thelist_Bairos_command_setconnectionqueueondevice($this, $connection_queue_obj);
			return $device_connection_queue->execute();
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	
	public function get_device_connection_queues($interface_obj=null)
	{
		if ($this->_device_type == 'bairos') {
	
			$device_connection_queues		= new Thelist_Bairos_command_getconnectionqueuesfromdevice($this, $interface_obj);
			$device_connection_queues->execute();
			return $device_connection_queues->get_all_queues();

		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}	

	
	public function get_cam_table()
	{
		if ($this->_device_type == 'cisco') {
	
			$cam_table		= new Thelist_Cisco_command_getcamtable($this);
			return $cam_table->execute();
				
		} else {
				
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
				
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
				
		}
	}
	
	public function remove_interface_cam_entries($interface_obj)
	{
		if ($this->_device_type == 'cisco') {
	
			$cam_table		= new Thelist_Cisco_command_removeinterfacecamentries($this, $interface_obj);
			return $cam_table->execute();
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	
	
	
	
	public function get_interface_spanning_tree_status($interface_obj)
	{
		if ($this->_device_type == 'cisco') {
	
			$spanning_tree_status		= new Thelist_Cisco_command_getinterfacespanningtreestatus($this, $interface_obj);
			return $spanning_tree_status->get_spanning_tree_status(true);
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	
	public function get_interface_operational_status($interface_obj)
	{
		if ($this->_device_type == 'cisco') {
	
			$cisco_interface_op_status		= new Thelist_Cisco_command_getinterfacestatus($this, $interface_obj);
			return $cisco_interface_op_status->get_operational_status(true);
	
		} elseif ($this->_device_type == 'bairos') {
	
			$bairos_interface_op_status		= new Thelist_Bairos_command_getinterfacestatus($this, $interface_obj);
			return $bairos_interface_op_status->get_operational_status(true);
	
		} elseif ($this->_device_type == 'routeros') {
	
			$routeros_interface_op_status	= new Thelist_Routeros_command_getinterfacestatus($this, $interface_obj);
			return $routeros_interface_op_status->get_operational_status(true);
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	
	public function get_interface_administrative_status($interface_obj)
	{
		if ($this->_device_type == 'cisco') {
	
			$cisco_interface_op_status		= new Thelist_Cisco_command_getinterfacestatus($this, $interface_obj);
			return $cisco_interface_op_status->get_configured_admin_status(true);
	
		} elseif ($this->_device_type == 'bairos') {
	
			$bairos_interface_op_status		= new Thelist_Bairos_command_getinterfacestatus($this, $interface_obj);
			return $bairos_interface_op_status->get_configured_admin_status(true);
	
		} elseif ($this->_device_type == 'routeros') {
	
			$routeros_interface_op_status	= new Thelist_Routeros_command_getinterfacestatus($this, $interface_obj);
			return $routeros_interface_op_status->get_configured_admin_status(true);
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	
	public function get_interface_duplex($interface_obj)
	{
		if ($this->_device_type == 'cisco') {
	
			$cisco_interface_duplex		= new Thelist_Cisco_command_getinterfaceduplex($this, $interface_obj);
			return $cisco_interface_duplex->get_configured_duplex(true);
	
		} elseif ($this->_device_type == 'bairos') {
	
			$bairos_interface_duplex		= new Thelist_Bairos_command_getinterfaceduplex($this, $interface_obj);
			return $bairos_interface_duplex->get_configured_duplex(true);
	
		} elseif ($this->_device_type == 'routeros') {
	
			$routeros_interface_duplex	= new Thelist_Routeros_command_getinterfaceduplex($this, $interface_obj);
			return $routeros_interface_duplex->get_configured_duplex(true);
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	
	public function get_interface_l3_mtu($interface_obj)
	{
		if ($this->_device_type == 'cisco') {
		
			$cisco_config		= new Thelist_Cisco_command_getinterfacelayer3mtu($this, $interface_obj);
			return $cisco_config->get_configured_layer3mtu(true);
		
		} elseif ($this->_device_type == 'bairos') {
		
			$bairos_config		= new Thelist_Bairos_command_getinterfacelayer3mtu($this, $interface_obj);
			return $bairos_config->get_configured_layer3mtu(true);
		
		} elseif ($this->_device_type == 'routeros') {
		
			$routeros_config	= new Thelist_Routeros_command_getinterfacelayer3mtu($this, $interface_obj);
			return $routeros_config->get_configured_layer3mtu(true);
		
		} else {
		
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
		
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
		
		}
	}
	public function get_interface_l2_mtu($interface_obj)
	{
		if ($this->_device_type == 'cisco') {
		
			$cisco_config		= new Thelist_Cisco_command_getinterfacelayer2mtu($this, $interface_obj);
			return $cisco_config->get_configured_layer2mtu(true);
		
		} elseif ($this->_device_type == 'bairos') {
		
			$bairos_config		= new Thelist_Bairos_command_getinterfacelayer2mtu($this, $interface_obj);
			return $bairos_config->get_configured_layer2mtu(true);
		
		} elseif ($this->_device_type == 'routeros') {
		
			$routeros_config	= new Thelist_Routeros_command_getinterfacelayer2mtu($this, $interface_obj);
			return $routeros_config->get_configured_layer2mtu(true);
		
		} else {
		
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
		
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
		
		}
	}
	public function get_interface_speed($interface_obj)
	{
		if ($this->_device_type == 'cisco') {
	
			$cisco_interface_speed		= new Thelist_Cisco_command_getinterfacespeed($this, $interface_obj);
			return $cisco_interface_speed->get_configured_speed(true);
	
		} elseif ($this->_device_type == 'bairos') {
	
			$bairos_interface_speed		= new Thelist_Bairos_command_getinterfacespeed($this, $interface_obj);
			return $bairos_interface_speed->get_configured_speed(true);
	
		} elseif ($this->_device_type == 'routeros') {
	
			$routeros_interface_speed	= new Thelist_Routeros_command_getinterfacespeed($this, $interface_obj);
			return $routeros_interface_speed->get_configured_speed(true);
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	public function get_interface_ssid($interface_obj)
	{
		if ($this->_device_type == 'routeros') {
	
			$routeros_interface_ssid	= new Thelist_Routeros_command_getinterfacessid($this, $interface_obj);
			return $routeros_interface_ssid->get_ssid(true);
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	public function get_interface_mode($interface_obj)
	{
		if ($this->_device_type == 'cisco') {
	
			$cisco_interface_mode		= new Thelist_Cisco_command_getinterfaceswitchportmode($this, $interface_obj);
			return $cisco_interface_mode->get_configured_switch_port_mode(true);
	
		} elseif ($this->_device_type == 'routeros') {
	
			$routeros_interface_wireless_mode	= new Thelist_Routeros_command_getinterfacewirelessmode($this, $interface_obj);
			return $routeros_interface_wireless_mode->get_wireless_mode(true);
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	public function get_interface_supported_wireless_protocols($interface_obj)
	{
		if ($this->_device_type == 'routeros') {
	
			$routeros_interface_supported_wireless_protocols	= new Thelist_Routeros_command_getinterfacesupportedwirelessprotocols($this, $interface_obj);
			return $routeros_interface_supported_wireless_protocols->get_supported_wireless_protocols(true);
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	public function get_interface_wireless_tx_center_frequency($interface_obj)
	{
	if ($this->_device_type == 'routeros') {
	
			$routeros_config	= new Thelist_Routeros_command_getinterfacewirelesstxcenterfrequency($this, $interface_obj);
			return $routeros_config->get_wireless_tx_center_frequency(true);
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	public function get_interface_wireless_rx_frequency($interface_obj)
	{
		//needs to be filled
	}
	public function get_interface_wireless_tx_channel_width($interface_obj)
	{
		if ($this->_device_type == 'routeros') {
	
			$routeros_interface_wireless_tx_width	= new Thelist_Routeros_command_getinterfacewirelesstxchannelwidth($this, $interface_obj);
			return $routeros_interface_wireless_tx_width->get_configured_tx_channel_widths(true);
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	public function get_interface_wireless_rx_channel_width($interface_obj)
	{
		//needs to be filled
	}
	public function get_interface_wireless_authentication_types($interface_obj)
	{
		if ($this->_device_type == 'routeros') {
	
			$routeros_config	= new Thelist_Routeros_command_getinterfacewirelesssecurityprofile($this, $interface_obj);
			return $routeros_config->get_authentication_types(true);
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	public function get_interface_wireless_unicast_ciphers($interface_obj)
	{
		if ($this->_device_type == 'routeros') {
		
			$routeros_config	= new Thelist_Routeros_command_getinterfacewirelesssecurityprofile($this, $interface_obj);
			return $routeros_config->get_unicast_ciphers(true);
		
		} else {
		
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
		
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
		
		}
	}
	
	public function get_interface_wireless_security_profile($interface_obj)
	{
		if ($this->_device_type == 'routeros') {
	
			$routeros_config	= new Thelist_Routeros_command_getinterfacewirelesssecurityprofile($this, $interface_obj);
			return $routeros_config->get_profile(true);
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	
	public function get_interface_wireless_group_ciphers($interface_obj)
	{
		if ($this->_device_type == 'routeros') {
	
			$routeros_config	= new Thelist_Routeros_command_getinterfacewirelesssecurityprofile($this, $interface_obj);
			return $routeros_config->get_group_ciphers(true);
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	public function get_interface_wireless_encryption_keys($interface_obj)
	{
		if ($this->_device_type == 'routeros') {
	
			$routeros_config	= new Thelist_Routeros_command_getinterfacewirelesssecurityprofile($this, $interface_obj);
			return $routeros_config->get_encryption_keys(true);
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	public function get_interface_vlan_id($interface_obj)
	{
		//needs to be filled
	}
	public function get_interface_switch_allowed_transit_vlans($interface_obj)
	{
		//needs to be filled
	}
	public function get_interface_switch_port_native_vlan($interface_obj)
	{
		//needs to be filled
	}
	public function get_interface_switch_port_vlans_allowed_trunking($interface_obj)
	{
		//needs to be filled
	}
	public function get_interface_switch_port_vlans_deny_trunking($interface_obj)
	{
		//needs to be filled
	}
	public function get_interface_switch_port_encapsulation($interface_obj)
	{
		//needs to be filled
	}
	public function get_interface_description($interface_obj)
	{
		if ($this->_device_type == 'cisco') {
		
			$cisco_config		= new Thelist_Cisco_command_getinterfacedescription($this, $interface_obj);
			return $cisco_config->get_configured_description(true);
		
		} elseif ($this->_device_type == 'bairos') {
		
			$bairos_config		= new Thelist_Bairos_command_getinterfacedescription($this, $interface_obj);
			return $bairos_config->get_configured_description(true);
		
		} elseif ($this->_device_type == 'routeros') {
		
			$routeros_config	= new Thelist_Routeros_command_getinterfacedescription($this, $interface_obj);
			return $routeros_config->get_configured_description(true);
		
		} else {
		
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
		
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
		
		}
	}
	
	public function get_interface_ip_addresses($interface_obj)
	{
		if ($this->_device_type == 'cisco') {
	
			$cisco_config		= new Thelist_Cisco_command_getinterfaceipaddresses($this, $interface_obj);
			return $cisco_config->get_ip_addresses(true);
	
		} elseif ($this->_device_type == 'bairos') {
	
			$bairos_config		= new Thelist_Bairos_command_getinterfaceipaddresses($this, $interface_obj);
			return $bairos_config->get_ip_addresses(true);
	
		} elseif ($this->_device_type == 'routeros') {
	
			$routeros_config	= new Thelist_Routeros_command_getinterfaceipaddresses($this, $interface_obj);
			return $routeros_config->get_ip_addresses(true);
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	
	public function get_interface_boot_protocol($interface_obj)
	{
		//needs to be filled
	}

	public function set_interface_administrative_status($interface_obj, $admin_status)
	{
		if ($this->_device_type == 'cisco') {
	
			$cisco_interface_admin_status	= new Thelist_Cisco_command_setinterfaceadminstatus($this, $interface_obj, $admin_status);
			return $cisco_interface_admin_status->execute();
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	
	public function get_running_os_package()
	{
		if ($this->_device_type == 'cisco') {
	
			$software		= new Thelist_Cisco_command_getsoftware($this);
			return $software->get_running_software_obj();
	
		} elseif ($this->_device_type == 'bairos') {
	
			$software		= new Thelist_Bairos_command_getsoftwarefromdevice($this);
			return $software->get_running_software_obj();
	
		} elseif ($this->_device_type == 'directvstb') {
	
			$software		= new Thelist_Directvstb_command_getsoftware($this);
			return $software->get_running_software_obj();
	
		} elseif ($this->_device_type == 'routeros') {
	
			$software		= new Thelist_Routeros_command_getsoftware($this);
			return $software->get_running_software_obj();
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	
	public function change_running_os_package($new_software_package_obj, $force_reinstall=false)
	{
	
		if ($this->_device_type == 'cisco') {
		
			$software		= new Thelist_Cisco_command_setsoftware($this, $new_software_package_obj, $force_reinstall);
			return $software->execute();
		
		} elseif ($this->_device_type == 'bairos') {
		
			$software		= new Thelist_Bairos_command_setsoftware($this, $new_software_package_obj, $force_reinstall);
			return $software->execute();
		
		} elseif ($this->_device_type == 'routeros') {
		
			$software		= new Thelist_Routeros_command_setsoftware($this, $new_software_package_obj, $force_reinstall);
			return $software->execute();
		
		} else {
		
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
		
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
		
		}
	}
	
	public function api_functional()
	{
		//use this after ie a reboot to validate that a router is back up and the api is usable
	
		if ($this->_device_type == 'cisco') {
	
			//nothing yet
	
		} elseif ($this->_device_type == 'bairos') {
	
			//nothing yet
	
		} elseif ($this->_device_type == 'routeros') {
	
				$device_reply = $this->execute_command("/system package print");
	
				if ($device_reply->get_code() == 1) {
					return true;
				} else {
					return false;
				}
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	
	public function logout_of_device()
	{
		if ($this->_device_type == 'cisco') {
	
			$log_out		= new Thelist_Cisco_command_logout($this);
			return $log_out->execute();
	
		} elseif ($this->_device_type == 'bairos') {
	
			$log_out		= new Thelist_Bairos_command_logout($this);
			return $log_out->execute();
	
		} elseif ($this->_device_type == 'routeros') {
	
			$log_out		= new Thelist_Routeros_command_logout($this);
			return $log_out->execute();
	
		}  elseif ($this->_device_type == 'directvstb') {
	
			//http, connection closes on its own
			
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	
	public function save_config_on_device()
	{
		if ($this->_device_type == 'cisco') {
	
			//$save		= new Thelist_Cisco_command_saveconfiguration($this);
			//return $save->execute();
	
		} elseif ($this->_device_type == 'bairos') {
	
			//bairos saves by default
	
		} elseif ($this->_device_type == 'routeros') {
	
			//routeros saves by default
	
		}  elseif ($this->_device_type == 'directvstb') {
	
			//dtv saves by default
				
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	
	
	
	public function get_arp_table()
	{
		if ($this->_device_type == 'bairos') {
	
			$arp_table		= new Thelist_Bairos_command_getarptable($this);
			return $arp_table->get_arp_table();
	
		} elseif ($this->_device_type == 'routeros') {
	
			$arp_table		= new Thelist_Routeros_command_getarptable($this);
			return $arp_table->get_arp_table();
	
		} else {
				
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
				
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
				
		}
	}
	
	public function get_subnet_arp_entries($subnet_address, $subnet_mask)
	{
		if ($this->_device_type == 'bairos') {
	
			$get_arp_entries_for_subnet = new Thelist_Bairos_command_getsubnetarpentries($this, $subnet_address, $subnet_mask);
			return $get_arp_entries_for_subnet->get_arp_entries_for_subnet();
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	
// 	public function icmp_sweep_subnet($subnet)
// 	{
// 		if ($this->_device_type == 'bairos') {
	
// 			$sweep		= new Thelist_Bairos_command_icmpsweepsubnet($this, $subnet);
// 			return $sweep->execute();
	
// 		} else {
	
// 			$trace  = debug_backtrace();
// 			$method = $trace[0]["function"];
	
// 			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
// 		}
// 	}
	
// 	public function clear_interface_mac_addresses($interface_obj)
// 	{
// 		if ($this->_device_type == 'cisco') {
	
// 			$clear_mac_addresses		= new Thelist_Cisco_command_clearinterfacemacaddresses($this, $interface_obj);
// 			return $clear_mac_addresses->execute();
	
// 		} else {
	
// 			$trace  = debug_backtrace();
// 			$method = $trace[0]["function"];
	
// 			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
// 		}
// 	}
	
	public function get_interface_mac_address($interface_obj)
	{
		if ($this->_device_type == 'bairos') {
	
			$get_mac		= new Thelist_Bairos_command_getinterfacemacaddress($this, $interface_obj);
			return $get_mac->get_mac_address();
	
		} elseif ($this->_device_type == 'cisco') {
	
			$get_mac		= new Thelist_Cisco_command_getinterfacemacaddress($this, $interface_obj);
			return $get_mac->get_mac_address();
	
		} elseif ($this->_device_type == 'routeros') {
	
			$get_mac		= new Thelist_Routeros_command_getinterfacemacaddress($this, $interface_obj);
			return $get_mac->get_mac_address();
	
		} else {
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
	
		}
	}
	
	
	public function get_device_type()
	{
		return $this->_device_type;
	}
	
	private function command_return_temp_store($command_return)
	{

		$this->_command_return_count++;
		$key = "'".time()."'_'".$this->_command_return_count."'";
		$this->_command_return .= "$key	##### $command_return \n";

	}
	
	public function execute_command($command)
	{
		//this is the non database related execution function
		//supress notices from prematurely shutdown connections
		return @$this->_device_connection->execute_command($command);
	}
	
	public function download_file($options)
	{	
		if ($this->_device_type == 'routeros') {

			$this->_device_connection->download_file($options);

		} else {
		
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
		
			throw new exception("".$method." is not defined for ".$this->_device_type."", 1200);
		
		}
	}
	
	public function execute_xml_no_command_validation($xml_commands)
	{
		
		//this method does not apply the reqexes to the return of any commands executed
		//it is only meant for getting data from the device and NEVER to set any values
		//setting requires validation and if commands are not executed they should be followed up on
		//this method does not supply this functionallity
		
		$commands = new SimpleXMLElement($xml_commands);
		
		foreach($commands as $command){
	
			$device_command = $command->xpath('device_command');
			$device_exe_order = $command->xpath('command_order_number');
	
			$command_return = $this->_device_connection->execute_command($device_command['0']);

				if ($device_exe_order['0'] == '0') {

					if ($command->xpath('regex/command_regex') != null) {
						
						foreach ($command->xpath('regex') as $regex) {

							$replace_yes_or_no = $regex->xpath('command_regex_replace');
							$match_yes_or_no_all = $regex->xpath('command_regex_match');
							$exe_regex = $regex->xpath('command_regex');

							if ("$replace_yes_or_no[0]" == '0' && "$match_yes_or_no_all[0]" == '1') {

								preg_match("/".$exe_regex[0]."/", $command_return->get_message(), $matches);
								if (isset($matches['1'])){ 
								
									$outputs['1'] = array($matches['1']);
								
								} else {
									
									return false;
									
								}
								

							} elseif ("$replace_yes_or_no[0]" == '0' && "$match_yes_or_no_all[0]" == '2') {
								
								preg_match_all("/".$exe_regex[0]."/", $command_return->get_message(), $matches);
								
								unset($matches['0']);
								
								if (isset($matches['1'])){
								
										$outputs = $matches;
								
								} else {
										
									return false;
										
								}
								
							
							}
						}

						if (isset($outputs['1'])) {
							
							$final_result = array();
							
							foreach ($outputs as $key => $value) {
							
								foreach ($value as $key2 => $value2) {
								
									foreach ($command->xpath('regex') as $regex) {
									
										$replace_yes_or_no2 = $regex->xpath('command_regex_replace');
										$exe_regex2 = $regex->xpath('command_regex');
										
										if ("$replace_yes_or_no2[0]" == '1') {
	
											$replace_result = preg_replace("/".$exe_regex2[0]."/", '', $value2);
	
											if ($replace_result != null) {
												
												$final_result[$key][$key2] = str_replace(array("\r", "\r\n", "\n"), '', $replace_result);
												
											}
										} else {
											
											$final_result[$key][$key2] = str_replace(array("\r", "\r\n", "\n"), '', $value2);
											
										}
									}
								}
							}
						}

						return $final_result;
					}

					return false;
						
				}
			}
	}
	
	
	public function execute_xml_with_command_validation($commands)
	{
		//if we dont get any xml then dont exe.
		if ($commands != false) {	
			foreach($commands as $command){
				
				$device_command = $command->xpath('device_command');
	
				$command_return = $this->_device_connection->execute_command($device_command['0']);
				
				$this->command_return_temp_store('start_command');
				$this->command_return_temp_store("$device_command[0]");
				$this->command_return_temp_store('end_command');
				
				if ($command->xpath('regex/command_regex') != null) {
					
					foreach ($command->xpath('regex/command_regex') as $regex) {
					
						//did we want success on a match or no match
						$match_yes_or_no = $command->xpath('regex/command_regex_match');
						
						if ($match_yes_or_no['0'] == '1' || $match_yes_or_no['0'] == '2') {
							
							if(!preg_match("/".$regex[0]."/", $command_return->get_message(), $matches)) {
							
								$this->command_return_temp_store('failure_regex_match=yes start');
								$this->command_return_temp_store("$device_command[0]");
								$this->command_return_temp_store("$regex[0]");
								$this->command_return_temp_store($command_return->get_message());
								$this->command_return_temp_store('failure_regex_match=yes end');
								$this->command_return_temp_store('##########Commands Start##########');
								foreach ($commands as $command2) {
								
									$device_command2 = $command2->xpath('device_command');
									$this->command_return_temp_store($device_command2[0]);
									
								}
								$this->command_return_temp_store('##########Commands End##########');
								throw new exception("$this->_command_return", 1);
									
							} else {
							
								$this->command_return_temp_store('success_regex_match=yes start');
								$this->command_return_temp_store('success_regex_match=yes end');
							
							}
							
							
						} else {
							
							if(preg_match("/".$regex[0]."/", $command_return->get_message(), $matches)) {
							
								$this->command_return_temp_store('failure_regex_match=no start');
								$this->command_return_temp_store("$regex[0]");
								$this->command_return_temp_store($command_return->get_message());
								$this->command_return_temp_store('failure_regex_match=no end');
								
								$this->command_return_temp_store('##########Commands Start##########');
								foreach ($commands as $command2) {
										
									$device_command2 = $command2->xpath('device_command');
									$this->command_return_temp_store($device_command2[0]);
								
								}
								$this->command_return_temp_store('##########Commands End##########');
							
								throw new exception("$this->_command_return", 1);
							
							} else {
							
								$this->command_return_temp_store('success_regex_match=no start');
								$this->command_return_temp_store('success_regex_match=no end');
							
							}
						}
					}
					
	
				} else {
					
					if($command_return->get_message() != '') {
						
						$this->command_return_temp_store('start_regex_no_return_failure');
						$this->command_return_temp_store($command_return->get_message());
						$this->command_return_temp_store('end_regex_no_return_failure');
							
						$this->command_return_temp_store('##########Commands Start##########');
						foreach ($commands as $command2) {
								
							$device_command2 = $command2->xpath('device_command');
							$this->command_return_temp_store($device_command2[0]);
						
						}
						$this->command_return_temp_store('##########Commands End##########');
						
						throw new exception("$this->_command_return", 2);
							
					} else {
					
						$this->command_return_temp_store('start_regex_no_return_success');
						$this->command_return_temp_store('end_regex_no_return_success');
					
					}
				}
			}
			
			//since everything completed successfully we reset the command return. so it is clean for the next batch.
			$this->_command_return 			= null;
			$this->_command_return_count 	= '0';
		}
		
	}
	
	private function ssh_enabled_device_classes()
	{
		//specific class is here so i can write logic to force a specific class to handle a particular device
		
		if ($this->_specific_connect_class == null) {

			try {
					
				 return new Thelist_Model_devicerouteros($this->_fqdn, $this->_auth_obj);
					
			} catch (Exception $e) {
			
				switch($e->getCode()){
			
					case 8401;
					return new Thelist_Model_devicebairos($this->_fqdn, $this->_auth_obj);
					break;
					default;
					throw $e;
			
				}
			}
			
		} else if ($this->_specific_connect_class == 'routeros') {
				
				$device_routeros = new Thelist_Model_devicerouteros($this->_fqdn, $this->_auth_obj);
				
				if ($device_routeros != false) {
					return $device_routeros;
				} else {
					throw new exception("you specified ".$specific_class." as the class of device ".$this->_fqdn." this failed");
				}
						
		} else if ($this->_specific_connect_class == 'bairos') {
			
			$device_bairos = new Thelist_Model_devicebairos($this->_fqdn, $this->_auth_obj);
			
			if ($device_bairos != false) {
				return $device_bairos;
			} else {
				throw new exception("you specified ".$specific_class." as the class of device ".$this->_fqdn." this failed");	
			}
		}
	}
	
	private function scp_enabled_device_classes()
	{
		//specific class is here so i can write logic to force a specific class to handle a particular device
	
		if ($this->_specific_connect_class == null) {
	
			try {
					
				return new Thelist_Model_devicerouteros($this->_fqdn, $this->_auth_obj);
					
			} catch (Exception $e) {
					
				switch($e->getCode()){
						
					case 1;
					return new Thelist_Model_devicebairos($this->_fqdn, $this->_auth_obj);
					break;
					default;
					throw $e;
						
				}
			}
				
		} else if ($this->_specific_connect_class == 'routeros') {
	
			$device_routeros = new Thelist_Model_devicerouteros($this->_fqdn, $this->_auth_obj);
	
			if ($device_routeros != false) {
				return $device_routeros;
			} else {
				throw new exception("you specified ".$specific_class." as the class of device ".$this->_fqdn." this failed");
			}
	
		} else if ($this->_specific_connect_class == 'bairos') {
				
			$device_bairos = new Thelist_Model_devicebairos($this->_fqdn, $this->_auth_obj);
				
			if ($device_bairos != false) {
				return $device_bairos;
			} else {
				throw new exception("you specified ".$specific_class." as the class of device ".$this->_fqdn." this failed");
			}
		}
	}
	
	
	
	
	
	private function sftp_enabled_device_classes()
	{
		//specific class is here so i can write logic to force a specific class to handle a particular device
	
		if ($this->_specific_connect_class == null) {
	
			try {
					
				return new Thelist_Model_devicelinuxserver($this->_fqdn, $this->_auth_obj);
					
			} catch (Exception $e) {
					
				switch($e->getCode()){
						
					case 9000;
					//not linux server
					throw $e;
					break;
					default;
					throw $e;
						
				}
			}
				
		} else if ($this->_specific_connect_class == 'linuxserver') {
	
			$device_devicelinuxserver = new Thelist_Model_devicelinuxserver($this->_fqdn, $this->_auth_obj);
	
			if ($device_devicelinuxserver != false) {
					
				return $device_devicelinuxserver;
					
			} else {
					
				throw new exception("you specified ".$specific_class." as the class of device ".$this->_fqdn." this failed");
					
			}
			
		} elseif ($this->_specific_connect_class == 'routeros') {

			$device_router_os = new Thelist_Model_devicerouteros($this->_fqdn, $this->_auth_obj);
		
			if ($device_router_os != false) {
					
				return $device_router_os;
					
			} else {
					
				throw new exception("you specified ".$specific_class." as the class of device ".$this->_fqdn." this failed");
					
			}
		}
	}

	private function telnet_enabled_device_classes()
	{
		//specific class is here so i can write logic to force a specific class to handle a particular device
		//specific class is here so i can write logic to force a specific class to handle a particular device
		
		if ($this->_specific_connect_class == null) {
		
			try {
					
				return new Thelist_Model_devicecisco($this->_fqdn, $this->_auth_obj);
					
			} catch (Exception $e) {
					
				switch($e->getCode()){
						
					case 1;
					//return new bairos($this->_fqdn, $this->_auth_obj);
					break;
					case 2;
					//return $this->log_equipment_action('command_got_return', $e->getMessage(), $if_id);
					break;
					default;
					throw $e;
						
				}
			}
				
		} else if ($this->_specific_connect_class == 'cisco') {
			
			$device_cisco = new Thelist_Model_devicecisco($this->_fqdn, $this->_auth_obj);
			if ($device_cisco != false) {
				return $device_cisco;
			} else {
				throw new exception("you specified ".$specific_class." as the class of device ".$this->_fqdn." this failed");	
			}
				
		} else if ($this->_specific_connect_class == 'routeros') {
			
			$device = new Thelist_Model_devicerouteros($this->_fqdn, $this->_auth_obj);
			if ($device != false) {
				return $device;
			} else {
				throw new exception("you specified ".$specific_class." as the class of device ".$this->_fqdn." this failed");
			}	

		}
	
	}
	
	private function http_enabled_device_classes()
	{

		if ($this->_specific_connect_class == null) {
		
			return new Thelist_Model_devicedirectvstb($this->_fqdn, $this->_auth_obj);

		} elseif ($this->_specific_connect_class == 'webserver') {
	
			return new Thelist_Model_devicewebserver($this->_fqdn, $this->_auth_obj);
	
		} elseif ($this->_specific_connect_class == 'somthing') {
	
		
		}

	}

}
?>