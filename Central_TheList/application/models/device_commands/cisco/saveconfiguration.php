<?php

//exception codes 3900-3999

class thelist_cisco_command_saveconfiguration implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	
	public function __construct($device)
	{
		$this->_device 					= $device;
	}
	
	public function execute()
	{		
			
			//get the root folder
			$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
			$get_connection_root_folder->execute();
			
			//enter config mode
			$device_reply = $this->_device->execute_command("write");

			//validate
			if (!preg_match("/[OK]/", $device_reply->get_message())) {

				throw new exception('configuration was not saved to switch', 3901);
					
		}
	}

}