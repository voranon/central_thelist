<?php

//exception codes 6300-6399

class thelist_cisco_command_getroutingtable implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_routes=null;
	
	public function __construct($device)
	{
		$this->_device = $device;
	}
	
	public function execute()
	{
		//get the root folder
		$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
		$get_connection_root_folder->execute();
		
		//get arp output
		$device_reply = $this->_device->execute_command('show running-config | in ip default-gateway');
		
		if ($device_reply->get_code() == '1') {

			preg_match("/([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/", $device_reply->get_message(), $cisco_default_gateway_raw);
			
			if (isset($cisco_default_gateway_raw['1'])) {
				$this->_routes[]	= new Thelist_Deviceinformation_routeentry('0.0.0.0', '0', $cisco_default_gateway_raw['1']);
			} else {
				$this->_routes	= null;
			}

		} else {
			throw new exception('device dident respond correctly to command', 6300);
		}		
	}
	
	public function get_routing_table()
	{
		//used for validation must be fresh result
		$this->execute();
		return $this->_routes;
	}

}