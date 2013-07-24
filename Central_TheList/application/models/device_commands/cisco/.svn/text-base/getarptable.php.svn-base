<?php

//exception codes 6400-6499

class thelist_cisco_command_getarptable implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_arp_table=null;
	
	public function __construct($device)
	{
		$this->_device = $device;
	
	}
	
	public function execute()
	{
		//set the terminal so it does not page
		$set_terminal	= new Thelist_Cisco_command_setterminal($this->_device, 80, 0);
		$set_terminal->execute();
		
		//get the root folder
		$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
		$get_connection_root_folder->execute();
		
		//get arp output
		$device_reply = $this->_device->execute_command("show arp");
		
		//set the terminal back to standard
		$set_terminal	= new Thelist_Cisco_command_setterminal($this->_device, 80, 25);
		$set_terminal->execute();
		
		preg_match_all("/Internet +([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}) +(([0-9]+)|(-)) +(\w{4}.\w{4}.\w{4}) +ARPA +(.*)/", $device_reply->get_message(), $arp_table_raw);
		
		if (isset($arp_table_raw['1'])) {
			$i=0;
			foreach($arp_table_raw['1'] as $arp_index => $ip_address) {

				$this->_arp_table[$i]		= new Thelist_Deviceinformation_arpentry($ip_address, strtoupper(preg_replace("/\./", "", $arp_table_raw['5'][$arp_index])), trim($arp_table_raw['6'][$arp_index]));
				
				if (preg_match("/[0-9]+/", $arp_table_raw['2'][$arp_index])) {
					$this->_arp_table[$i]->set_age($arp_table_raw['2'][$arp_index]);
				} else {
					$this->_arp_table[$i]->set_age('local');
				}

				$i++;
			}

		} else {
			$this->_arp_table	= null;
		}
	}
	
	public function get_arp_table()
	{
		//used for validation must be fresh result
		$this->execute();
		return $this->_arp_table;
	}

}
?>