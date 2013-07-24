<?php

//exception codes 20100-20199

class thelist_routeros_command_getarptable implements Thelist_Commander_pattern_interface_idevicecommand 
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
		$device_reply = $this->_device->execute_command("/ip arp print detail without-paging");
		preg_match_all("/[0-9]+ (.) address=([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}) +mac-address=(\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2}) +interface=\"?(.*)\"?/", $device_reply->get_message(), $arp_table_raw);
		
		if (isset($arp_table_raw['2'])) {
			$i=0;
			foreach($arp_table_raw['2'] as $arp_index => $ip_address) {
				
				$this->_arp_table[$i]		= new Thelist_Deviceinformation_arpentry($ip_address, strtoupper(preg_replace("/:/", "", $arp_table_raw['3'][$arp_index])), trim($arp_table_raw['4'][$arp_index]));
				
				if ($arp_table_raw['1'] == 'D') {
					$this->_arp_table[$i]->set_arp_type('dynamic');
				} else {
					$this->_arp_table[$i]->set_arp_type('static');
				}
				$i++;
			}

		} else {
			throw new exception("routeros device: '".$this->_device->get_fqdn()."' returned an empty arp table, that is not possible since at least its gateway should show up.", 20100);
		}
	}
	
	public function get_arp_table($refresh=true)
	{
		if($this->_arp_table == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		
		return $this->_arp_table;
	}

}
?>