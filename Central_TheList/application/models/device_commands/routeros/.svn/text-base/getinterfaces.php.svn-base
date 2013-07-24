<?php

//exception codes 21100-21199

class thelist_routeros_command_getinterfaces implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_configured_interface_names=null;
	
	private $_include_if_configs=null;
	
	public function __construct($device)
	{
		$this->_device = $device;
	}
	
	public function execute()
	{
		//all interfaces, need to add support for a whole host of interfaces
		$this->_configured_interface_names	= null;
		
		//get the equipment type, use this to figure out what interfaces are static and which are 
		//added i.e. wireless radios, currently only doing ehternet, wireless and vlan
		$get_eq_type		= new Thelist_Routeros_command_getequipmenttype($this->_device);
		$eq_type_obj		= $get_eq_type->get_eq_type_obj();
		
		//these are the minimum interfaces that must be on the device
		//they are integrated so they cannot be removed.
		$static_if_types	= $eq_type_obj->get_static_if_types();
		
		if ($static_if_types != null) {
			
			foreach ($static_if_types as $static_if_type) {
				$static_interfaces[]	= $static_if_type->get_if_type()->get_if_type();
			}
		}
		
		//try ethernet
		$command_eth					= "/interface ethernet export";
		$device_reply_ethernet 			= $this->_device->execute_command($command_eth);

		//try wireless
		$command_wlan					= "/interface wireless export";
		$device_reply_wireless			= $this->_device->execute_command($command_wlan);

		//try vlan
		$command_vlan					= "/interface vlan export";
		$device_reply_vlan				= $this->_device->execute_command($command_vlan);

		//try nstreme dual
		$command_nsd					= "/interface wireless nstreme-dual export";
		//$device_reply_nsd				= $this->_device->execute_command($command_nsd);

		//try vrrp
		$command_vrrp					= "/interface vrrp print detail";
		//$device_reply_vrrp				= $this->_device->execute_command($command_vrrp);

		//try bridge
		$command_bridge					= "/interface bridge print detail";
		//$device_reply_bridge			= $this->_device->execute_command($command_bridge);
		
		//get irqs and pci device ids
		$command_irq					= "/system resource irq print detail";
		$device_reply_irq	 			= $this->_device->execute_command($command_irq);
		
		//get all ethernet interfaces
		preg_match_all("/(\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2}) .* name=\"?(.*?)\"? /", $device_reply_ethernet->get_message(), $eth_details);
		
		//get all wireless interfaces
		preg_match_all("/(\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2}) .* name=\"?(.*?)\"? /", $device_reply_wireless->get_message(), $wlan_details);
		
		//get all vlan interfaces
		preg_match_all("/ interface=\"?(.*?)\"? .*name=\"?(.*?)\"? use.* vlan-id=([0-9]+)/", $device_reply_vlan->get_message(), $vlan_details1);
		
		//get all nsd interfaces
// 		preg_match_all("/name=\"?(.*?)\"? /", $device_reply_nsd->get_message(), $nsd_details1);
// 		preg_match_all("/mac-address=(\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2})/", $device_reply_nsd->get_message(), $nsd_details2);
		
		//get all vrrp interfaces
// 		preg_match_all("/name=\"?(.*?)\"? /", $device_reply_vrrp->get_message(), $vrrp_details1);
// 		preg_match_all("/mac-address=(\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2})/", $device_reply_vrrp->get_message(), $vrrp_detail2);
		
		//get all bridge interfaces
// 		preg_match_all("/name=\"?(.*?)\"? /", $device_reply_bridge->get_message(), $bridge_details1);
// 		preg_match_all("/mac-address=(\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2})/", $device_reply_bridge->get_message(), $bridge_details2);


		//all pre interface processing

		//ethernet macs
		if (isset($eth_details['1']['0'])) {
			
			//arrange all macs as decimals in an array
			foreach ($eth_details['1'] as $eth_index => $eth_mac_address) {
			
				$mac_obj = new Thelist_Deviceinformation_macaddressinformation($eth_mac_address);
			
				//convert the hex 16 value to a base 10 so we can compare the macs
				//the idea is that i.e. ether1 is the lowest mac address
				//this method will have to be expanded if the board has a daughterboard with
				//additional ethernet interfaces on it
				$eth_details['mac_in_decimal'][$eth_index] = base_convert($mac_obj->get_macaddress(), 16, 10);
			
			}
		}
		
		if (isset($wlan_details['2']['0'])) {

			$pci_id_replacements		= array(":", ".");
			
			foreach ($wlan_details['2'] as $cur_index => $interface_name) {
				//get the pci_id
				$command_pci_id					= ":put [/interface wireless info  get ".$interface_name." pci-info]";
				$device_reply_pci_id 			= $this->_device->execute_command($command_pci_id);
				preg_match("/(\w{2}:\w{2}\.(\w{2}|\w{1}))/", $device_reply_pci_id->get_message(), $pci_id_detail);
				
				if (isset($pci_id_detail['1'])) {
					
					$wlan_details['pci_id_raw'][$cur_index] = $pci_id_detail['1'];
					$wlan_details['pci_id_base10'][$cur_index] = base_convert(str_replace($pci_id_replacements, '', $pci_id_detail['1']), 16, 10);

				}
			}
		}
		
		//return interface index
		$i=0;

		//ethernet
		if (isset($eth_details['2']['0'])) {
			
			foreach ($eth_details['2'] as $cur_index => $interface_name) {
				
				$get_interface_type			= new Thelist_Routeros_command_getinterfacetype($this->_device, $interface_name);
				$interface_type_obj			= $get_interface_type->get_interface_type();

				$return_interfaces[$i]['interface_name'] 		= $interface_name;
				$return_interfaces[$i]['if_type_id'] 			= $interface_type_obj->get_if_type_id();

				//find the correct index, the $smaller_than_main is the index value
				$smaller_than_main=1;
				foreach ($eth_details['mac_in_decimal'] as $decimal_mac) {
				
					if ($decimal_mac < $eth_details['mac_in_decimal'][$cur_index]) {
						$smaller_than_main++;
					}
				}
				
				//even though the interface name might be different, the components 
				//should reflect our standard, so we use standad metrics
				$return_interfaces[$i]['name_components']['name']	= 'ether';
				$return_interfaces[$i]['name_components']['index']	= $smaller_than_main;

				if ($this->_include_if_configs === true) {
				
					//physical interface, get the config
					$if_config										= new Thelist_Routeros_command_getinterfaceconfig($this->_device, $interface_name);
					$if_configuration								= $if_config->get_interface_config();
					$return_interfaces[$i]['configuration']			= $if_configuration['configuration'];
				}
				
				$type_match_found = 'no';
				if (count($static_interfaces) > 0) {

					foreach ($static_interfaces as $sit_index => $type_name) {
				
						if ($type_match_found == 'no' && $type_name == 'ethernet') {
							//remove one of the static interfaces
							//this way we can keep count of what is integrated and what is modular
							//this will have to be improved so we can distinguish add on / daughter card ethernet from integrated.
							$type_match_found = 'yes';
							unset($static_interfaces[$sit_index]);
						}
					}
				}
				
				//a non integrated interface is a stand alone piece of equipment
				if ($type_match_found == 'yes') {
					$return_interfaces[$i]['integrated_hardware']	= 1;
				} else {
					$return_interfaces[$i]['integrated_hardware']	= 0;
				}
				
				$i++;
			}
		}

		//wireless
		if (isset($wlan_details['2']['0'])) {
				
			
			foreach ($wlan_details['2'] as $cur_index => $interface_name) {
				
				$get_interface_type			= new Thelist_Routeros_command_getinterfacetype($this->_device, $interface_name);
				$interface_type_obj			= $get_interface_type->get_interface_type();

				$return_interfaces[$i]['interface_name'] 		= $interface_name;
				$return_interfaces[$i]['if_type_id'] 			= $interface_type_obj->get_if_type_id();

				$type_match_found = 'no';
				if (count($static_interfaces) > 0) {
						
					foreach ($static_interfaces as $sit_index => $type_name) {
				
						if ($type_match_found == 'no' && $type_name == 'wireless') {
								
							//remove one of the static interfaces
							//this way we can keep count of what is integrated and what is modular
							//this will have to be improved so we can distinguish add on / daughter card ethernet from integrated.
							$type_match_found = 'yes';
							unset($static_interfaces[$sit_index]);
						}
					}
				}
				
				//a non integrated interface is a stand alone piece of equipment
				if ($type_match_found == 'yes') {
					$return_interfaces[$i]['integrated_hardware']	= 1;
				} else {
					$return_interfaces[$i]['integrated_hardware']	= 0;
				}
				
				
				//find the correct slot number, unless this is an integrated radio
				if ($return_interfaces[$i]['integrated_hardware'] == 0) {
					$smaller_than_main=1;
					foreach ($wlan_details['pci_id_base10'] as $index => $base10pciid) {
					
						if ($base10pciid < $wlan_details['pci_id_base10'][$cur_index]) {
							$smaller_than_main++;
						}
					}
					
				} else {
					
					//integrated radio is index 1, this is in no way fool proof, there can be integrated device that have both
					//address this
					$smaller_than_main = 1;
				}

				//even though the interface name might be different, the components 
				//should reflect our standard, so we use standard metrics: wlan + number
				$return_interfaces[$i]['name_components']['name']	= 'wlan';
				$return_interfaces[$i]['name_components']['index']	= $smaller_than_main;

				if ($this->_include_if_configs === true) {
				
					//physical interface, get the config
					$if_config										= new Thelist_Routeros_command_getinterfaceconfig($this->_device, $interface_name);
					$if_configuration								= $if_config->get_interface_config();
					$return_interfaces[$i]['configuration'] 		= $if_configuration['configuration'];
				}
				

				
				$i++;
			}
		}

		//nsd
		if (isset($nsd_details['1']['0'])) {
		
			
		}
		
		//vrrp
		if (isset($vrrp_details['1']['0'])) {
		
			
		}
		
		//bridge
		if (isset($bridge_details['1']['0'])) {
		
			
		}
		
		//vlan
		if (isset($vlan_details1['1']['0'])) {
		
			foreach ($vlan_details1['2'] as $cur_index => $interface_name) {

				$vlan_components_found = 'no';
				
				$return_interfaces[$i]['interface_name'] 		= $interface_name;
				$return_interfaces[$i]['if_type_id'] 			= 95;
				
				//we have already done all other interface types so the parent interface should already be in the return array
				foreach($return_interfaces as $run_int) {
					
					if ($run_int['interface_name'] == $vlan_details1['1'][$cur_index]) {
						
						$return_interfaces[$i]['name_components']['name']		= $run_int['name_components']['name'];
						$return_interfaces[$i]['name_components']['index']		= $run_int['name_components']['index'];
						$return_interfaces[$i]['name_components']['vlan_id']	= $vlan_details1['3'][$cur_index];
						$vlan_components_found = 'yes';
					}
				}
				
				if ($vlan_components_found == 'no') {
					throw new exception("we cannot find the interface that is parent for device: ".$this->_device->get_fqdn()." for interface: ".$interface_name." ", 21100);
				}

				if ($this->_include_if_configs === true) {
					//vlans interface, get the config
					$if_config										= new Thelist_Routeros_command_getinterfaceconfig($this->_device, $interface_name);
					$if_configuration								= $if_config->get_interface_config();
					$return_interfaces[$i]['configuration'] = $if_configuration['configuration'];
				}
				
				$i++;

			}
		}
		
		if (isset($return_interfaces)) {
			$this->_configured_interface_names	= $return_interfaces;
		} else {
			$this->_configured_interface_names	= null;
		}
	}

	public function get_configured_interfaces($refresh=true, $include_if_configs=null)
	{
		$this->_include_if_configs = $include_if_configs;
		
		if($this->_configured_interface_names == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		return $this->_configured_interface_names;
	}
}