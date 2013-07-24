<?php

//exception codes 9900-9999

class thelist_cisco_command_gettransitvlans implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_transit_vlans=null;
	
	public function __construct($device)
	{
		$this->_device 			= $device;
	}
	
	public function execute()
	{		
		//set the terminal so it does not page
		$set_terminal	= new Thelist_Cisco_command_setterminal($this->_device, 80, 0);
		$set_terminal->execute();
		
		//get the root folder
		$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
		$get_connection_root_folder->execute();

		//switch transit vlans
		$device_reply = $this->_device->execute_command("show vlan brief");
		
		//set the terminal back to standard
		$set_terminal	= new Thelist_Cisco_command_setterminal($this->_device, 80, 25);
		$set_terminal->execute();
		
		//find the allowed vlans
		preg_match_all("/([0-9]+) +(VLAN[0-9]+|default)/", $device_reply->get_message(), $transit_vlans_raw);
		
		if (isset($transit_vlans_raw['1']['0'])) {
			$this->_transit_vlans = $transit_vlans_raw['1'];
		} else {
			$this->_transit_vlans = null;
		}
	}
	
	public function get_transit_vlans()
	{
		//used for validation must be fresh
		$this->execute();
		return $this->_transit_vlans;
	}
}

