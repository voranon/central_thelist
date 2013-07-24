<?php

//exception codes 12000-12099

class thelist_cisco_command_getcamtable implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_cam_table=null;
	
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
		$device_reply = $this->_device->execute_command("show mac-address-table");
		
		//set the terminal back to standard
		$set_terminal	= new Thelist_Cisco_command_setterminal($this->_device, 80, 25);
		$set_terminal->execute();
		
		//echo $device_reply->get_message();
		$regular_expression = array(
									0 => '/(?P<vlan>\d+)\s+(?P<macaddress>\w+\.\w+\.\w+)\s+DYNAMIC\s+(?P<interface>.*)/',
									1 => '/(?P<macaddress>\w+\.\w+\.\w+)\s+\s+Dynamic\s+(?P<vlan>\d+)(?P<interface>.*)/'
								);
		
		$is_match = false;
		$matches = array( 'macaddress' => array(), 'interface' => array(), 'vlan' => array());
		
		//for each of the regular expressions, check if there is a match
		//if there, no need to continue checking the remaining regular expressions
		for ($i=0; $i < count($regular_expression);$i++){
			
			//returns true if there is a match
			if (preg_match($regular_expression[$i], $device_reply->get_message())){
				preg_match_all($regular_expression[$i],$device_reply->get_message(),$matches);
				$is_match = true;
				break;
			}
		}

		$mac_addresses 		= $matches['macaddress'];
		$interfaces 		= $matches['interface'];
		$vlans 				= $matches['vlan'];
		//for each of the matches in the regex expression match, create a cam_entry
		
		$length_of_matches = 0;
		$length_of_matches = count($matches['macaddress']);

		for ($i=0; $i<$length_of_matches; $i++){
			
			//format the name of the interface so it conforms to the cisco standard
			if (preg_match("/^(Fa|Gi)([0-9]+)\/([0-9]+)$/", trim($matches['interface'][$i]), $match)) {
					
				if ($match['1'] == 'Gi') {
					$interface_name = "GigabitEthernet" . $match['2'] . "/" . $match['3'];
				} elseif ($match['1'] == 'Fa') {
					$interface_name = "FastEthernet" . $match['2'] . "/" . $match['3'];
				} else {
					throw new exception('the interface name has an unknown format, please extend this method to avoid surprises', 12002);
				}

			} else {
			
				throw new exception('the interface name has an unknown format, please extend this method to avoid surprises', 12001);
			
			}

			$single_mac_address 		= strtoupper(preg_replace("/\./", "", $matches['macaddress'][$i]));
			$single_interface 			= $interface_name;
			$single_vlan 				= $matches['vlan'][$i];
			
			$this->_cam_table[]		= new Thelist_Deviceinformation_camentry($single_vlan, $single_mac_address, trim($single_interface));

		}
	}
	
	public function get_cam_table()
	{
		//used for validation must be fresh result
		$this->execute();
		return $this->_cam_table;
	}

}