<?php

//exception codes 18400-18499

class thelist_routeros_command_getinterfacetype implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_interface;

	private $_interface_type=null;
	private $_routeros_specific_if_type_name=null;
	
	//if we are using the get_interface_type method
	private $_get_object=null;

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
		$complete = 'no';
			
		if (is_object($this->_interface)) {
			$interface_name		= $this->_interface->get_if_name();
			
			$this->_interface_type = $this->_interface->get_if_type();
			
			//we have the interface type lets set the ros specific name
			if ($this->_interface_type->get_if_type() == 'ethernet') {	
				$this->_routeros_specific_if_type_name = 'ethernet';
				$complete = 'yes';
			} elseif ($this->_interface_type->get_if_type() == 'wireless') {
				$this->_routeros_specific_if_type_name = 'wireless';
				$complete = 'yes';
			} elseif ($this->_interface_type->get_if_type_id() == 95) {
				$this->_routeros_specific_if_type_name = 'vlan';
				$complete = 'yes';
			} elseif ($this->_interface_type->get_if_type_id() == 92) {
				$this->_routeros_specific_if_type_name = 'nstreme_dual';
				$complete = 'yes';
			} elseif ($this->_interface_type->get_if_type_id() == 91) {
				$this->_routeros_specific_if_type_name = 'vrrp';
				$complete = 'yes';
			} elseif ($this->_interface_type->get_if_type_id() == 90) {
				$this->_routeros_specific_if_type_name = 'bridge';
				$complete = 'yes';
			}

		} else {
			//string model
			$interface_name		= $this->_interface;
		}

		if ($complete == 'no') {
			
			$ros_type_determined = 'no';
			
			//lets try guessing the interface type
			if (preg_match("/\.[0-9]+/", strtoupper($interface_name)) || preg_match("/VLAN/", strtoupper($interface_name))) {
				
				$command_vlan		= "/interface vlan print detail where name=\"".$interface_name."\"";
				$device_reply_vlan 	= $this->_device->execute_command($command_vlan);
				
				if (preg_match("/(\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2})/", $device_reply_vlan->get_message())) {
					$this->_routeros_specific_if_type_name = 'vlan';
					$ros_type_determined = 'yes';
				}
				
			} elseif (preg_match("/ETHER/", strtoupper($interface_name))) {
				
				$command_eth		= "/interface ethernet print detail where name=\"".$interface_name."\"";
				$device_reply_eth 	= $this->_device->execute_command($command_eth);
				
				if (preg_match("/(\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2})/", $device_reply_eth->get_message())) {
					$this->_routeros_specific_if_type_name = 'ethernet';
					$ros_type_determined = 'yes';
				}
				
			} elseif (preg_match("/WLAN/", strtoupper($interface_name))) {
				
				$command_wlan		= "/interface wireless print detail where name=\"".$interface_name."\"";
				$device_reply_wlan 	= $this->_device->execute_command($command_wlan);
				
				if (preg_match("/(\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2})/", $device_reply_wlan->get_message())) {
					$this->_routeros_specific_if_type_name = 'wireless';
					$ros_type_determined = 'yes';
				}
			}

			
			if ($ros_type_determined == 'no') {
				//no luck then we brute force
				
				//try ethernet
				$command_eth		= "/interface ethernet print detail where name=\"".$interface_name."\"";
				$device_reply_eth 	= $this->_device->execute_command($command_eth);
	
				if (preg_match("/(\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2})/", $device_reply_eth->get_message())) {
					$this->_routeros_specific_if_type_name = 'ethernet';
				} else {
	
					//try wireless
					$command	= "/interface wireless print detail where name=\"".$interface_name."\"";
					$device_reply = $this->_device->execute_command($command);
					
					if (preg_match("/(\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2})/", $device_reply->get_message())) {
						$this->_routeros_specific_if_type_name = 'wireless';
					} else {
					
						//try vlan
						$command	= "/interface vlan print detail where name=\"".$interface_name."\"";
						$device_reply = $this->_device->execute_command($command);
					
						if (preg_match("/(\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2})/", $device_reply->get_message())) {
							$this->_routeros_specific_if_type_name = 'vlan';
							
						} else {
							
							//try nstreme dual
							$command	= "/interface wireless nstreme-dual print detail where name=\"".$interface_name."\"";
							$device_reply = $this->_device->execute_command($command);
						
							if (preg_match("/(\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2})/", $device_reply->get_message())) {
								
								$this->_routeros_specific_if_type_name = 'nstreme_dual';
					
							} else {
							
								//try vrrp
								$command	= "/interface vrrp print detail where name=\"".$interface_name."\"";
								$device_reply = $this->_device->execute_command($command);
							
								if (preg_match("/(\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2})/", $device_reply->get_message())) {
							
									$this->_routeros_specific_if_type_name = 'vrrp';
									
								} else {
								
									//try bridge
									$command	= "/interface bridge print detail where name=\"".$interface_name."\"";
									$device_reply = $this->_device->execute_command($command);
								
									if (preg_match("/(\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2})/", $device_reply->get_message())) {
								
										$this->_routeros_specific_if_type_name = 'bridge';
	
									} else {
										throw new exception("interface does not exist on routeros device: ".$this->_device->get_fqdn()." with interface name: '".$interface_name."'", 18400);
									}	
								}
							}
						}
					}	
				}
			}
				
			if (is_object($this->_interface) || $this->_get_object === true) {

				if ($this->_routeros_specific_if_type_name == 'ethernet' && $this->_interface_type == null) {
					
					//get the equipment type
					$get_eq_type		= new Thelist_Routeros_command_getequipmenttype($this->_device);
					$eq_type_obj		= $get_eq_type->get_eq_type_obj();

					//find the type by comparing all the ethernet interfaces and finding the correct 
					//static interface index
					$command_eth					= "/interface ethernet export";
					$device_reply_ethernet 			= $this->_device->execute_command($command_eth);
					
					//get all ethernet interface details
					preg_match_all("/(\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2}) .* name=\"?(.*?)\"? /", $device_reply_ethernet->get_message(), $eth_details);
					
					if (isset($eth_details['1']['0'])) {
						
						foreach ($eth_details['1'] as $eth_index => $eth_mac_address) {
							
							$mac_obj = new Thelist_Deviceinformation_macaddressinformation($eth_mac_address);
							
							//convert the hex 16 value to a base 10 so we can compare the macs
							//the idea is that i.e. ether1 is the lowest mac address
							//this method will have to be expanded if the board has a daughterboard with
							//additional ethernet interfaces on it
							$mac_as_decimal = base_convert($mac_obj->get_macaddress(), 16, 10);
							
							$all_decimal_macs[] = $mac_as_decimal;
							
							
							if ($interface_name == $eth_details['2'][$eth_index]) {
								$main_interface_decimal_mac =  $mac_as_decimal;
							}
						}
						
						//the $smaller_than_main is the index value
						$smaller_than_main=1;
						foreach ($all_decimal_macs as $decimal_mac) {
							
							if ($decimal_mac < $main_interface_decimal_mac) {
								$smaller_than_main++;
							}
						}
						
						$sql = "SELECT * FROM static_if_types sit
								INNER JOIN interface_types ift ON ift.if_type_id=sit.if_type_id
								WHERE sit.eq_type_id='".$eq_type_obj->get_eq_type_id()."'
								AND ift.if_type='ethernet'
								";
						
						$if_type_details = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
						
						if (isset($if_type_details['0'])) {
							foreach ($if_type_details as $if_type_detail) {
								
								if ("ether".$smaller_than_main."" == $if_type_detail['if_default_name']) {
									$if_type_id = $if_type_detail['if_type_id'];
								}
							}
						}

						if (isset($if_type_id['if_type_id'])) {
							$this->_interface_type = new Thelist_Model_interfacetype($if_type_id);
						} else {
							throw new exception("we have an unknown ethernet interface type on routeros device: ".$this->_device->get_fqdn()." with interface name: ".$interface_name." and index: '".$smaller_than_main."', eq_type_id: '".$eq_type_obj->get_eq_type_id()."' ", 18403);
						}
					}
					
				} elseif ($this->_routeros_specific_if_type_name == 'wireless' && $this->_interface_type == null) {
					
					//get pci device ids, this is not yet being used but it yields the model numbers
					//of the radios 
					
// 					$pci_id_replacements			= array(":", ".");
// 					$command_pci					= "/system resource pci print detail";
// 					$device_reply_pci	 			= $this->_device->execute_command($command_pci);

// 					preg_match_all("/device=\"?(\w{2}:\w{2}\.(\w{2}|\w{1}))\"?/", $device_reply_pci->get_message(), $pci_device_details);
// 					preg_match_all("/name=\"?(.*?) .*\"?/", $device_reply_pci->get_message(), $pci_device_models);
					
// 					$pci_devices['pci_raw_device_id']		= $pci_device_details['1'];
					
// 					foreach ($pci_device_details['1'] as $raw_index => $raw_id) {
// 						$pci_devices['pci_device_id_base10'][$raw_index] =	base_convert(str_replace($pci_id_replacements, '', $raw_id), 16, 10);
// 					}
					
// 					$pci_devices['pci_device_model']		= $pci_device_models['1'];
					

					$command	= "/interface wireless print detail where name=\"".$interface_name."\"";
					$device_reply = $this->_device->execute_command($command);
					
					//find the radio type
					preg_match("/interface-type=(.*) mode/", $device_reply->get_message(), $type_raw);
					
					if (isset($type_raw['1'])) {
						
						$sql = "SELECT if_type_id FROM interface_types
								WHERE if_type_name='".$type_raw['1']."'
								AND if_type='wireless'
								";
						
						$if_type_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
						
						if (isset($if_type_id['if_type_id'])) {
							$this->_interface_type = new Thelist_Model_interfacetype($if_type_id);
						} else {
							
							$trace 		= debug_backtrace();
							$method 	= $trace[0]["function"];
							$class		= get_class($this);
							
							$desc = "First seen on device: '".$this->_device->get_fqdn()."' with name: '".$interface_name."'";
							
							$data = array(
							
								'if_type_name'		=> $type_raw['1'],
								'if_type'			=> 'wireless',
								'if_type_desc'		=> $desc,
							
							);
							
							$new_if_type_id = Zend_Registry::get('database')->insert_single_row('interface_types', $data, $class, $method);
							
							$this->_interface_type = new Thelist_Model_interfacetype($new_if_type_id);
						}
						
					} else {
						throw new exception("we have an unknown wireless interface type on routeros device: ".$this->_device->get_fqdn()." with interface name: ".$interface_name." ", 18401);
					}
				} elseif ($this->_routeros_specific_if_type_name == null) {
					throw new exception("we have an unknown interface type on routeros device: ".$this->_device->get_fqdn()." with interface name: ".$interface_name.". most likely we just have to expand the method ", 18402);
				}
			}
			
		}
	}
	
	public function get_interface_type($refresh=true)
	{
		$this->_get_object = true;
		
		if($this->_interface_type == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		
		return $this->_interface_type;
	}
	
	public function get_routeros_specific_if_type_name()
	{
		$this->_get_object = null;
		
		if($this->_routeros_specific_if_type_name == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		
		return $this->_routeros_specific_if_type_name;
		
	}
	
}