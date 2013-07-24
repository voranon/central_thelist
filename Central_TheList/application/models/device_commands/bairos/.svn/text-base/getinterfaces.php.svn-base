<?php

//exception codes 17800-17899

class thelist_bairos_command_getinterfaces implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_running_interface_names=null;
	private $_configured_interface_names=null;
	
	public function __construct($device)
	{
		$this->_device = $device;
	}
	
	public function execute()
	{
		$this->configured_interfaces();
		$this->running_interfaces();
		
	}
	
	private function configured_interfaces()
	{
		$get_network_config_files	= new Thelist_Bairos_command_getfilelist($this->_device, '/etc/sysconfig/network-scripts/');
		$files						= $get_network_config_files->get_files();
		
		if (isset($files['files'])) {
			$i=0;
			foreach ($files['files'] as $file) {
					
				if (preg_match("/^ifcfg-(eth|lo)([0-9]+)?(\.|:)?([0-9]+)?(\.|:)?([0-9]+)?/", $file['file_name'], $interface_detail)) {
		
					if (!isset($interface_detail['2'])) {
		
						//only loopback fulfills this
						$return_interfaces[$i]['interface_name'] 			= $interface_detail['1'];
						//20 is loopback
						$return_interfaces[$i]['if_type_id'] 				= 20;
		
						$return_interfaces[$i]['name_components']['name']	= $interface_detail['1'];
		
						$i++;
					} elseif (!isset($interface_detail['3'])) {
		
						$return_interfaces[$i]['interface_name'] 		= $interface_detail['1'] . $interface_detail['2'];
		
						//find the type
						$device_reply = $this->_device->execute_command("lspci -vb | grep 'Ethernet controller'");
		
						if (preg_match("/82574L/", $device_reply->get_message())) {
							$return_interfaces[$i]['if_type_id'] 			= 3;
						} else {
							//expand as we get new bai router types
							throw new exception("we tried figure out the if_type_id for bai router: ".$this->_device->get_fqdn()." for interface: ".$interface_detail['0']." ", 17800);
						}
		
						$return_interfaces[$i]['name_components']['name']	= $interface_detail['1'];
						$return_interfaces[$i]['name_components']['index']	= $interface_detail['2'];
		
						//physical interface, get the config
						$if_config								= new Thelist_Bairos_command_getinterfaceconfig($this->_device, $return_interfaces[$i]['interface_name']);
						$if_configuration						= $if_config->get_interface_config();
						$return_interfaces[$i]['configuration'] = $if_configuration['configuration'];
		
						$i++;
		
					} elseif (!isset($interface_detail['5'])) {
							
						if (isset($interface_detail['4']) && isset($interface_detail['3'])) {
		
							if ($interface_detail['3'] == '.') {
		
								//this is a vlan
		
								$return_interfaces[$i]['interface_name'] 		= $interface_detail['1'] . $interface_detail['2'] . $interface_detail['3'] . $interface_detail['4'];
								$return_interfaces[$i]['if_type_id'] 			= 95;
		
								$return_interfaces[$i]['name_components']['name']		= $interface_detail['1'];
								$return_interfaces[$i]['name_components']['index']		= $interface_detail['2'];
								$return_interfaces[$i]['name_components']['vlan_id']	= $interface_detail['4'];
		
								//vlans interface, get the config
								$if_config								= new Thelist_Bairos_command_getinterfaceconfig($this->_device, $return_interfaces[$i]['interface_name']);
								$if_configuration						= $if_config->get_interface_config();
								$return_interfaces[$i]['configuration'] = $if_configuration['configuration'];
		
								$i++;
		
							} elseif ($interface_detail['3'] == ':') {
								$aliases[] = array('name' => $interface_detail['1'], 'index' => $interface_detail['2'], 'alias_number' => $interface_detail['4']);
							}
		
						} else {
							throw new exception("we encountered an interface that should be either alias or vlan, but we are missing information. bai router: ".$this->_device->get_fqdn()." for interface: ".$interface_detail['0']." ", 17801);
						}
		
					} elseif (isset($interface_detail['6'])) {
		
						//this is a vlan alias
						$aliases[] = array('name' => $interface_detail['1'], 'index' => $interface_detail['2'], 'alias_number' => $interface_detail['6'], 'vlan_id' => $interface_detail['4']);
		
					}
				}
			}
				
				
			if (isset($aliases) && isset($return_interfaces)) {
		
				foreach ($aliases as $alias_index => $alias) {
						
					//vlans or not vlans
					if (isset($alias['vlan_id'])) {
		
						foreach ($return_interfaces as $return_index => $interface) {
								
							if (isset($interface['name_components']['vlan_id'])) {
		
								if ($interface['name_components']['vlan_id'] == $alias['vlan_id'] && $interface['name_components']['name'] == $alias['name'] && $interface['name_components']['index'] == $alias['index']) {
									$return_interfaces[$return_index]['aliases'][] 	= array('alias_number' => $alias['alias_number']);
										
									unset($aliases[$alias_index]);
								}
							}
						}
		
					} else {
		
						foreach ($return_interfaces as $return_index => $interface) {
		
							if (!isset($interface['name_components']['vlan_id'])) {
									
								if ($interface['name_components']['name'] == $alias['name'] && $interface['name_components']['index'] == $alias['index']) {
									$return_interfaces[$return_index]['aliases'][] 	= array('alias_number' => $alias['alias_number']);
		
									unset($aliases[$alias_index]);
								}
							}
						}
					}
				}
			}
				
				
				
			if(isset($aliases)) {
		
				if (count($aliases) > 0) {
						
					$t=0;
					foreach ($aliases as $alias) {
							
						if (isset($alias['vlan_id'])) {
		
							$return_interfaces['homeless_aliases'][$t]['interface_name'] = $alias['name'] . $alias['index'] . "." . $alias['vlan_id'] . ":" . $alias['alias_number'];
							$return_interfaces['homeless_aliases'][$t]['name_components']['vlan_id']	= $alias['vlan_id'];
								
						} else {
							$return_interfaces['homeless_aliases'][$t]['interface_name'] = $alias['name'] . $alias['index'] . ":" . $alias['alias_number'];
						}
		
						$return_interfaces['homeless_aliases'][$t]['aliases'][] 					= array('alias_number' => $alias['alias_number']);
						$return_interfaces['homeless_aliases'][$t]['name_components']['name']		= $alias['name'];
						$return_interfaces['homeless_aliases'][$t]['name_components']['index']		= $alias['index'];
		
						$t++;
					}
				}
			}
				
			if (isset($return_interfaces)) {
				$this->_configured_interface_names = $return_interfaces;
			}
		}
	}
	
	private function running_interfaces()
	{
		//all running interfaces
		
		$device_reply = $this->_device->execute_command("ifconfig");
		
		preg_match_all("/(eth|lo)([0-9]+)?(\.|:)?([0-9]+)?(\.|:)?([0-9]+)?/", $device_reply->get_message(), $running_if_details);
			
		if (isset($running_if_details['0'])) {
			$i=0;
			foreach ($running_if_details['0'] as  $run_index => $running_if_detail) {
		
				if ($running_if_details['2'][$run_index] == '') {
		
					//only loopback fulfills this
					$return_running_interfaces[$i]['interface_name'] 			= $running_if_details['1'][$run_index];
					//20 is loopback
					$return_running_interfaces[$i]['if_type_id'] 				= 20;
		
					$return_running_interfaces[$i]['name_components']['name']	= $running_if_details['1'][$run_index];
		
					$i++;
				} elseif ($running_if_details['3'][$run_index] == '') {
		
					$return_running_interfaces[$i]['interface_name'] 		= $running_if_details['0'][$run_index];
		
					//find the type
					$device_reply = $this->_device->execute_command("lspci -vb | grep 'Ethernet controller'");
		
					if (preg_match("/82574L/", $device_reply->get_message())) {
						$return_running_interfaces[$i]['if_type_id'] 			= 3;
					} else {
						//expand as we get new bai router types
						throw new exception("we tried figure out the if_type_id for bai router: ".$this->_device->get_fqdn()." for interface: ".$running_if_detail['0']." ", 17802);
					}
		
					$return_running_interfaces[$i]['name_components']['name']	= $running_if_details['1'][$run_index];
					$return_running_interfaces[$i]['name_components']['index']	= $running_if_details['2'][$run_index];
		
					//physical interface, get the config
					$if_config										= new Thelist_Bairos_command_getinterfaceconfig($this->_device, $return_running_interfaces[$i]['interface_name']);
					$if_configuration								= $if_config->get_interface_config();
					$return_running_interfaces[$i]['configuration'] = $if_configuration['configuration'];
						
					$i++;
		
				} elseif ($running_if_details['5'][$run_index] == '') {
		
					if ($running_if_details['4'][$run_index] != '' && $running_if_details['3'][$run_index] != '') {
		
						if ($running_if_details['3'][$run_index] == '.') {
		
							//this is a vlan
		
							$return_running_interfaces[$i]['interface_name'] 		= $running_if_details['0'][$run_index];
							$return_running_interfaces[$i]['if_type_id'] 			= 95;
		
							$return_running_interfaces[$i]['name_components']['name']		= $running_if_details['1'][$run_index];
							$return_running_interfaces[$i]['name_components']['index']		= $running_if_details['2'][$run_index];
							$return_running_interfaces[$i]['name_components']['vlan_id']	= $running_if_details['4'][$run_index];
		
							//vlan get config
							$if_config										= new Thelist_Bairos_command_getinterfaceconfig($this->_device, $return_running_interfaces[$i]['interface_name']);
							$if_configuration								= $if_config->get_interface_config();
							$return_running_interfaces[$i]['configuration'] = $if_configuration['configuration'];
								
							$i++;
		
						} elseif ($running_if_details['3'][$run_index] == ':') {
							$running_aliases[] = array('name' => $running_if_detail['1'], 'index' => $running_if_detail['2'], 'alias_number' => $running_if_details['4'][$run_index]);
						}
		
					} else {
						throw new exception("we encountered an interface that should be either alias or vlan, but we are missing information. bai router: ".$this->_device->get_fqdn()." for interface: ".$running_if_details['0'][$run_index]." ", 17803);
					}
		
				} elseif ($running_if_details['6'][$run_index] != '') {
		
					//this is a vlan alias
					$running_aliases[] = array('name' => $running_if_details['1'][$run_index], 'index' => $running_if_details['2'][$run_index], 'alias_number' => $running_if_details['6'][$run_index], 'vlan_id' => $running_if_details['4'][$run_index]);
		
				}
			}
				
			if (isset($running_aliases) && isset($return_running_interfaces)) {
					
				foreach ($running_aliases as $alias_index => $alias) {
		
					//vlans or not vlans
					if (isset($alias['vlan_id'])) {
							
						foreach ($return_running_interfaces as $return_index => $interface) {
		
							if (isset($interface['name_components']['vlan_id'])) {
									
								if ($interface['name_components']['vlan_id'] == $alias['vlan_id'] && $interface['name_components']['name'] == $alias['name'] && $interface['name_components']['index'] == $alias['index']) {
									$return_running_interfaces[$return_index]['aliases'][] 	= array('alias_number' => $alias['alias_number']);
		
									unset($running_aliases[$alias_index]);
								}
							}
						}
							
					} else {
							
						foreach ($return_running_interfaces as $return_index => $interface) {
								
							if (!isset($interface['name_components']['vlan_id'])) {
									
								if ($interface['name_components']['name'] == $alias['name'] && $interface['name_components']['index'] == $alias['index']) {
									$return_running_interfaces[$return_index]['aliases'][] 	= array('alias_number' => $alias['alias_number']);
										
									unset($running_aliases[$alias_index]);
								}
							}
						}
					}
				}
			}

			if(isset($running_aliases)) {
					
				if (count($running_aliases) > 0) {
		
					$t=0;
					foreach ($running_aliases as $alias) {
							
						if (isset($alias['vlan_id'])) {
								
							$return_running_interfaces['homeless_aliases'][$t]['interface_name'] = $alias['name'] . $alias['index'] . "." . $alias['vlan_id'] . ":" . $alias['alias_number'];
							$return_running_interfaces['homeless_aliases'][$t]['name_components']['vlan_id']	= $alias['vlan_id'];
		
						} else {
							$return_running_interfaces['homeless_aliases'][$t]['interface_name'] = $alias['name'] . $alias['index'] . ":" . $alias['alias_number'];
						}
							
						$return_running_interfaces['homeless_aliases'][$t]['aliases'][] 					= array('alias_number' => $alias['alias_number']);
						$return_running_interfaces['homeless_aliases'][$t]['name_components']['name']		= $alias['name'];
						$return_running_interfaces['homeless_aliases'][$t]['name_components']['index']		= $alias['index'];
							
						$t++;
					}
				}
			}
		
			if (isset($return_running_interfaces)) {
				$this->_running_interface_names = $return_running_interfaces;
			}
		}
	}
	
	public function get_running_interfaces($refresh=true)
	{
		if($this->_running_interface_names == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->running_interfaces();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->running_interfaces();
		}
		return $this->_running_interface_names;
	}
	
	public function get_configured_interfaces($refresh=true)
	{
		if($this->_configured_interface_names == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->configured_interfaces();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->configured_interfaces();
		}
		return $this->_configured_interface_names;
	}
}