<?php

//exception codes 3300-3399

class thelist_cisco_command_placedeviceconnectioninrootfolder implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	
	public function __construct($device)
	{
		$this->_device 					= $device;
	}

	public function execute()
	{
		//verify that we are in root privilied mode
		$device_reply = $this->_device->execute_command('show');

		if (!preg_match("/(for a list of subcommands)/", $device_reply->get_message())) {

			if (preg_match("/(VLAN ISL Id)/", $device_reply->get_message())) {
				
				//if we found ourselfs in the vlan database
				$device_reply2 = $this->_device->execute_command('exit');
				$device_reply3 = $this->_device->execute_command('show');
			
			} else {
				
				//if anywhere else
				$device_reply2 = $this->_device->execute_command('end');
				$device_reply3 = $this->_device->execute_command('show');
			}

			if (!preg_match("/(for a list of subcommands)/", $device_reply3->get_message())) {
				
				//end, if the command fails
				$device_reply4 = $this->_device->execute_command('end');
				$device_reply5 = $this->_device->execute_command('show');
				
				//if that failed too there is a problem
				if (!preg_match("/(for a list of subcommands)/", $device_reply5->get_message())) {
				
					throw new exception('cisco switch did not report that we are in priviliged mode', 3300);
				}
				
			}	
		}
	}
}
	