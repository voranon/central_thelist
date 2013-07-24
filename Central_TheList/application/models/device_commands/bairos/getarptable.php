<?php

//exception codes 11900-11999

class thelist_bairos_command_getarptable implements Thelist_Commander_pattern_interface_idevicecommand
{

	private $_device;
	private $_arp_table=null;
	
	public function __construct($device)
	{
		$this->_device = $device;
	
	}
	
	public function execute()
	{
		//get arp output
	
		$device_reply = $this->_device->execute_command("arp -an");
	
		$arp_array = array();
		preg_match_all("/\((.*)\)\sat\s\<?(incomplete|\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2})\>?\s(on|\[ether\])\s((eth.*)|on\s(eth.*))/",$device_reply->get_message(),$arp_array);
		$ip_array = $arp_array[1];
		$mac_array = $arp_array[2];
		$interface_array = $arp_array[6];
		$interface_array_incomplete = $arp_array[5];

		$arp_table = array();
		for ($i = 0; $i < count($ip_array); $i++){
			
			$interface = "";
			if ($mac_array[$i] == "incomplete")
			{
				$interface = $interface_array_incomplete[$i];
			}
			else {
				$interface = $interface_array[$i];
			}
			
			$single_ip_address		= $ip_array[$i];
			$single_mac_address		= strtoupper(preg_replace("/:/", "", $mac_array[$i]));
			$single_interface		= $interface;
			
			if ($single_mac_address != 'INCOMPLETE') {	
				$this->_arp_table[]		= new Thelist_Deviceinformation_arpentry($single_ip_address, $single_mac_address, $single_interface);
			}
		}
	}
	
	public function get_arp_table()
	{
		$this->execute();
		return $this->_arp_table;
	}

}