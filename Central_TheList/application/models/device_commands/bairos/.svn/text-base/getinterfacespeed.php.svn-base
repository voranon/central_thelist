<?php

//exception codes 12600-12699

class thelist_bairos_command_getinterfacespeed implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_interface;
	
	private $_configured_speed=null;
	
	//if negotiated
	private $_running_speed=null;

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
		$is_vlan = 'no';
		
		if (is_object($this->_interface)) {
			$interface_name		= $this->_interface->get_if_name();
			
			//additional check for object model
			if($this->_interface->get_if_type()->get_if_type_id() == 95) {
				//is this a vlan interface?
				$this->_configured_speed	= null;
				$is_vlan = 'yes';
			}
				
		} else {
			$interface_name		= $this->_interface;
		}
		
		
		
		if ($is_vlan == 'no') {
		
			$device_reply1 = $this->_device->execute_command("cat /etc/sysconfig/network-scripts/ifcfg-".$interface_name."");
			
			//check if interface has a config file, if not then nothing else matters and al commands will fail
			if (!preg_match("/No such file or directory/", $device_reply1->get_message())) {
	
				//speed
				if (preg_match("/ETHTOOL_OPTS=\"speed (10|100|1000) duplex (full|half) autoneg (off|on)\"/", $device_reply1->get_message(), $result1)) {
					$this->_configured_speed = $result1['1'];
				} else {
					$this->_configured_speed	= 'auto';
				}
			
			} else {
				throw new exception('interface config file does not exist, interface not setup or cannot get vlan id', 12600);
			}
			
			$device_reply2 = $this->_device->execute_command("ethtool ".$interface_name."");
			
			if (!preg_match("/Cannot get device settings/", $device_reply2->get_message()) && !preg_match("/Link detected: no/", $device_reply2->get_message())) {
				//if the interface is not set down or unplugged
				//then we can get the running speed
				
				if (preg_match("/Speed: (10|100|1000)Mb\/s/", $device_reply2->get_message(), $result2)) {
					$this->_running_speed = $result2['1'];
				} else {
					//could be a vlan interface
					$this->_running_speed = null;
				}
				
			} else {
				$this->_running_speed = null;
			}
		}
	}
	
	public function get_running_speed() 
	{
		//used for validation, must be a fresh result
		$this->execute();
		return $this->_running_speed;
	}
	
	public function get_configured_speed()
	{
		//used for validation, must be a fresh result
		$this->execute();
		return $this->_configured_speed;
	}
}