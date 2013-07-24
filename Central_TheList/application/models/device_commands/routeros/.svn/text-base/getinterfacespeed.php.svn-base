<?php

//exception codes 18700-18799

class thelist_routeros_command_getinterfacespeed implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	
	private $_configured_speed=null;
	private $_configured_speed_metrics=null;
	private $_running_speed=null;
	
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
		//reset the vars
		$this->_configured_speed 			= null;
		$this->_configured_speed_metrics 	= null;
		$this->_running_speed				= null;	
		
		if (is_object($this->_interface)) {
			$interface_name		= $this->_interface->get_if_name();
			$interface			= $this->_interface;
		} else {
			$interface_name		= $this->_interface;
			$interface			= $this->_interface;
		}
		
		$get_if_type	= new Thelist_Routeros_command_getinterfacetype($this->_device, $interface);
		$if_type		= $get_if_type->get_routeros_specific_if_type_name();

		if ($if_type != 'vlan') {
		
			//get admin status for the interface
			$get_if_status				= new Thelist_Routeros_command_getinterfacestatus($this->_device, $this->_interface);
			$configured_admin_status	= $get_if_status->get_configured_admin_status(false);
			
			if ($if_type == 'ethernet') {
				
				//ethernet
				$command 			= "/interface ethernet print detail where name=\"".$interface_name."\"";
	
				$reg_ex_1			= "auto-negotiation=(yes|no)";
				$reg_ex_2			= "speed=(1|10|100)(Mbps|Gbps)";
				
				$device_reply = $this->_device->execute_command($command);
				
				preg_match("/".$reg_ex_1."/", $device_reply->get_message(), $raw_negotiation_status);
				
				if (isset($raw_negotiation_status['1'])) {
					
					if ($raw_negotiation_status['1'] == 'yes') {
						$this->_configured_speed[]	= 'auto';
					} else {
						
						preg_match("/".$reg_ex_2."/", $device_reply->get_message(), $raw_configured_speed);

						if (isset($raw_configured_speed['1'])) {
							
							if ($raw_configured_speed['1'] == 10 && $raw_configured_speed['2'] == 'Mbps') {
								$this->_configured_speed[]	= 10;
							} elseif ($raw_configured_speed['1'] == 100 && $raw_configured_speed['2'] == 'Mbps') {
								$this->_configured_speed[]	= 100;
							} elseif ($raw_configured_speed['1'] == 1 && $raw_configured_speed['2'] == 'Gbps') {
								$this->_configured_speed[]	= 1000;
							}
							
						} else {
							throw new exception("we we could not determine the configured speed for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."'", 18703);
						}
					}
	
					$operational_status		= $get_if_status->get_operational_status(false);
					
					if ($operational_status == 1) {
						
						$command2 			= "/interface ethernet monitor [find where name=\"".$interface_name."\"] once";
						$device_reply2 		= $this->_device->execute_command($command2);
						$reg_ex_3			= "rate: (1|10|100)(Mbps|Gbps)";
						
						preg_match("/".$reg_ex_3."/", $device_reply2->get_message(), $raw_running_speed);
						
						if (isset($raw_running_speed['1'])) {
						
							if ($raw_running_speed['1'] == 10 && $raw_running_speed['2'] == 'Mbps') {
								$this->_running_speed[]	= 10;
							} elseif ($raw_running_speed['1'] == 100 && $raw_running_speed['2'] == 'Mbps') {
								$this->_running_speed[]	= 100;
							} elseif ($raw_running_speed['1'] == 1 && $raw_running_speed['2'] == 'Gbps') {
								$this->_running_speed[]	= 1000;
							}
						
						} else {
							throw new exception("we we could not determine the running speed for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."'", 18704);
						}
	
					} else {
						$this->_running_speed	= null;
					}
	
				} else {
					throw new exception("we we could not determine if interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."' is autonegotiating or not", 18705);
				}
	
			} elseif ($if_type == 'wireless') {
					
				//wireless
				$main_config_reply 	= $this->_device->execute_command("/interface wireless export");
	
				//interface rate set
				preg_match("/ name=".$interface_name." .* rate-set=(default|configured) /", $main_config_reply->get_message(), $raw_interface_speed_config);
				
				if (isset($raw_interface_speed_config['1'])) {
				
					if ($raw_interface_speed_config['1'] == 'default') {
						$this->_configured_speed[]		 		= 'auto';
					} else {
						
						//if not auto negotiating
						
						//get the supported protocols
						$get_supported_protocols				= new Thelist_Routeros_command_getinterfacesupportedwirelessprotocols($this->_device, $this->_interface);
						$supported_protocols					= $get_supported_protocols->get_supported_wireless_protocols(false);
						
						//get the channel widths
						$get_wireless_tx_channel_width			= new Thelist_Routeros_command_getinterfacewirelesstxchannelwidth($this->_device, $this->_interface);
						$wireless_tx_widths						= $get_wireless_tx_channel_width->get_configured_tx_channel_widths(false);
						
						//configured speeds
						$converter = new Thelist_Utility_multiplierconverter();
						
						
						foreach($supported_protocols as $supported_protocol) {
								
							//a and g rates are the same
							$i=0;
							if ($supported_protocol == '802.11b') {
								preg_match("/ name=".$interface_name." .* supported-rates-b=(.*) tdma-period/", $main_config_reply->get_message(), $raw_interface_speed1);
								
								if (isset($raw_interface_speed1['1'])) {
								
									$configured_speeds = preg_replace("/\"/", '', $raw_interface_speed1['1']);
									$exploded_speeds = explode(',', $configured_speeds);
								
									foreach($wireless_tx_widths as $channel_width) {
										foreach($exploded_speeds as $single_speed) {
											$this->_configured_speed[$i] = $converter->convert_wireless_rate_to_int($single_speed, $channel_width, '802.11b');
											$this->_configured_speed_metrics[$i]['channel_width'] 	= $channel_width;
											$this->_configured_speed_metrics[$i]['protocol'] 		= '802.11b';
											$this->_configured_speed_metrics[$i]['mcs']				= null;
											$i++;
										}
									}
								} else {
									throw new exception("we we could not determine configured rates for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."'", 18706);
								}
								
							} elseif ($supported_protocol == '802.11g' || $supported_protocol == '802.11a') {
								preg_match("/ name=".$interface_name." .* supported-rates-a\/g=(.*) supported-rates-b/", $main_config_reply->get_message(), $raw_interface_speed2);
								
								if ($supported_protocol == '802.11g') {
									
									if (isset($raw_interface_speed2['1'])) {
											
										$configured_speeds = preg_replace("/\"/", '', $raw_interface_speed2['1']);
										$exploded_speeds = explode(',', $configured_speeds);
											
										foreach($wireless_tx_widths as $channel_width) {
												
											foreach($exploded_speeds as $single_speed) {
												$this->_configured_speed[$i] = $converter->convert_wireless_rate_to_int($single_speed, $channel_width, '802.11g');
												$this->_configured_speed_metrics[$i]['channel_width'] 	= $channel_width;
												$this->_configured_speed_metrics[$i]['protocol'] 		= '802.11g';
												$this->_configured_speed_metrics[$i]['mcs']				= null;
												$i++;
											}
										}
									} else {
										throw new exception("we we could not determine configured rates for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."'", 18707);
									}
									
								} elseif($supported_protocol == '802.11a') {
									
									if (isset($raw_interface_speed2['1'])) {
									
										$configured_speeds = preg_replace("/\"/", '', $raw_interface_speed2['1']);
										$exploded_speeds = explode(',', $configured_speeds);
									
										foreach($wireless_tx_widths as $channel_width) {
												
											foreach($exploded_speeds as $single_speed) {
												$this->_configured_speed[$i] = $converter->convert_wireless_rate_to_int($single_speed, $channel_width, '802.11a');
												$this->_configured_speed_metrics[$i]['channel_width'] 	= $channel_width;
												$this->_configured_speed_metrics[$i]['protocol'] 		= '802.11a';
												$this->_configured_speed_metrics[$i]['mcs']				= null;
												$i++;
											}
										}
									} else {
										throw new exception("we we could not determine configured rates for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."'", 18708);
									}
								}
	
							} elseif ($supported_protocol == '802.11n') {
								preg_match("/ ht-supported-mcs=(.*) ht-txchains.* name=".$interface_name." /", $main_config_reply->get_message(), $raw_interface_speed3);
								
								if (isset($raw_interface_speed3['1'])) {
										
									$configured_speeds = preg_replace("/\"/", '', $raw_interface_speed3['1']);
									$exploded_speeds = explode(',', $configured_speeds);
								
									foreach($wireless_tx_widths as $channel_width) {
								
										foreach($exploded_speeds as $single_speed) {
								
											//currently mikrotik shows mcs-16-23 regardless of them being available, so we filter them out
											if (
											$single_speed != 'mcs-16'
											&& $single_speed != 'mcs-17'
											&& $single_speed != 'mcs-18'
											&& $single_speed != 'mcs-19'
											&& $single_speed != 'mcs-20'
											&& $single_speed != 'mcs-21'
											&& $single_speed != 'mcs-22'
											&& $single_speed != 'mcs-23'
											) {
												$this->_configured_speed[$i] 							= $converter->convert_wireless_rate_to_int($single_speed, $channel_width, '802.11n');
												$this->_configured_speed_metrics[$i]['channel_width'] 	= $channel_width;
												$this->_configured_speed_metrics[$i]['protocol'] 		= '802.11n';
												preg_match("/([0-9]+)/", $single_speed, $mcs_raw);
												$this->_configured_speed_metrics[$i]['mcs']				= $mcs_raw['1'];
												$i++;
											}
										}
									}
								} else {
									throw new exception("we we could not determine configured rates for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."'", 18709);
								}
							}
						}
					}
					
				} else {
					throw new exception("we we could not determine if rates are configured or default for interface name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."'", 18708);
				}
	
			} elseif ($if_type == 'nstreme_dual') {
				
				//nstreme dual
				$this->_configured_speed[]	= 'full';
				
				if ($configured_admin_status == 1) {
					$this->_running_speed[]	= 'full';
				} else {
					$this->_running_speed[]	= null;
				}
			}
		} 
	}
	

	public function get_running_speed($refresh=true) 
	{
		if($this->_running_speed == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to, reset the variable and run the function
			$this->execute();
		}
		
		return $this->_running_speed;
	}
	
	public function get_configured_speed($refresh=true)
	{
		if($this->_configured_speed == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to, reset the variable and run the function
			$this->execute();
		}

		return $this->_configured_speed;
	}
	
 	public function get_configured_speed_metrics($refresh=false)
	{
		if($this->_configured_speed_metrics == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to, reset the variable and run the function
			$this->execute();
		}
	
		return $this->_configured_speed_metrics;
	}
	
	
	
}