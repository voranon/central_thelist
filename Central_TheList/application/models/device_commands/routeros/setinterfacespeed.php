<?php

//exception codes 20900-20999 

class thelist_routeros_command_setinterfacespeed implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	private $_speed;
	private $_mcs=null;
	
	public function __construct($device, $interface, $speed, $mcs=null)
	{
		//$interface
		//object	= interface_obj
		//string	= interface name
		
		//$speed
		//object	= new speed string or array
		//string	= new speed string or array
		
		//$mcs, matches the mcs value of speed if needed (802.11n). array index match, if array used
		//object	= new speed string or array
		//string	= new speed string or array
		
		$this->_device 					= $device;
		$this->_interface 				= $interface;
		$this->_speed					= $speed;
		
		//mcs is required for 802.11n rates because there are many overlaps i.e. 13Mbit/s (mcs 3,4), 26Mbit/s mcs (8,9)
		$this->_mcs						= $mcs;
	}
	
	public function execute()
	{
		if (is_object($this->_interface)) {
			$interface_name			= $this->_interface->get_if_name();
			$interface				= $this->_interface;
		} else {
			$interface_name			= $this->_interface;
			$interface				= $this->_interface;
		}
		
		$get_current_speed	= new Thelist_Routeros_command_getinterfacespeed($this->_device, $interface);
		
		//current speed always returns array
		$current_speeds			= $get_current_speed->get_configured_speed(true);
		$current_metrics		= $get_current_speed->get_configured_speed_metrics(false);

		//did we get current speeds
		if ($current_speeds == null) {
			throw new exception("you are trying to set speed on interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."', but current speeds returned null, this may be a vlan interface you are trying to set speed on", 20900);
		}
		
		//$this->_speed can be both string and array
		//make both array
		if (is_array($this->_speed)) {
			$new_speeds = $this->_speed;
		} else {
			//string speed
			$new_speeds[] = $this->_speed;
		}

		if ($this->_mcs !== null) {
			
			if (is_array($this->_mcs)) {
				$new_mcs = $this->_mcs;
			} else {
				//string speed
				$new_mcs[] = $this->_mcs;
			}
			
			//convert the current mcs into a single array 
			foreach ($current_metrics as $metric_index => $current_metric) {
				
				if (isset($current_metric['mcs'])) {
					$current_mcs[$metric_index] = $current_metric['mcs'];
				}
			}
			
			$mcs_diff = count(array_diff($current_mcs, $new_mcs)) + count(array_diff($new_mcs, $current_mcs));
		}

		//defaut is not to update
		$update_speed_config = 'no';
		
		
		if (isset($mcs_diff)) {
			
			if ($mcs_diff > 0) {
				$update_speed_config = 'yes';
			} else {
				
				$diff = count(array_diff($current_speeds, $new_speeds)) + count(array_diff($new_speeds, $current_speeds));
					
				if ($diff > 0) {
					$update_speed_config = 'yes';
				}
			}

		} else {
			
			//see if there are any differance between old and new rates, array diff is one way
			$diff = count(array_diff($current_speeds, $new_speeds)) + count(array_diff($new_speeds, $current_speeds));
			
			if ($diff > 0) {
				$update_speed_config = 'yes';
			}
		}

		//if there is a differance
		if ($update_speed_config == 'yes') {
			
			$number_of_new_speeds = count($new_speeds);
			
			//if there is only one new speed it may be auto
			if (count($new_speeds) == 1) {
				
				if ($new_speeds['0'] == 'auto') {
					$auto_negotiation = 'yes';
				} else {
					$auto_negotiation = 'no';
				}
				
			} else {
				$auto_negotiation = 'no';
			}
			
			$get_if_type	= new Thelist_Routeros_command_getinterfacetype($this->_device, $interface);
			$if_type		= $get_if_type->get_routeros_specific_if_type_name();
			
			if ($if_type == 'vlan') {
				throw new exception("you are trying to set speed on interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."', vlans cant have speed", 20901);
			} elseif ($if_type == 'ethernet') {
				
				if ($auto_negotiation == 'yes') {
					$command = "/interface ethernet set [find where name=\"".$interface_name."\"] auto-negotiation=\"yes\"";
				} else {
					
					//ethernet interfaces can only have a single speed either auto or some numeric value
					if ($number_of_new_speeds > 1) {
						throw new exception("you are trying to set speed on interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."', but you have specified more then one speed, ethernet can only have a single speed or auto", 20902);
					} else {

						if ($new_speeds['0'] == 10) {
							$command = "/interface ethernet set [find where name=\"".$interface_name."\"] speed=\"10Mbps\"";
						} elseif ($new_speeds['0'] == 100) {
							$command = "/interface ethernet set [find where name=\"".$interface_name."\"] speed=\"100Mbps\"";
						} elseif ($new_speeds['0'] == 1000) {
							$command = "/interface ethernet set [find where name=\"".$interface_name."\"] speed=\"1Gbps\"";
						} else {
							throw new exception("only 10, 100, 1000 and auto are allowed values, you are trying to set speed: '".$new_speeds['0']."' on interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."'", 20903);
						}
						
						//if the speed is specified, we are not auto negotiation
						$command2 = "/interface ethernet set [find where name=\"".$interface_name."\"] auto-negotiation=\"no\"";
						$device_reply 		= $this->_device->execute_command($command2);
						
					}
				}
				
				//change the speed
				$device_reply 		= $this->_device->execute_command($command);
				
			} elseif ($if_type == 'wireless') {
				
				if ($auto_negotiation == 'yes') {
					$command = "/interface wireless set [find where name=\"".$interface_name."\"] rate-set=\"default\"";
				} else {

					foreach ($new_speeds as $new_speed_index => $new_speed) {
						
						if ($new_speed == 6000000) {
							$new_rate	= 6;
							$rate_type	= 'ag';
						} elseif ($new_speed == 9000000) {
							$new_rate	= 9;
							$rate_type	= 'ag';
						} elseif ($new_speed == 12000000) {
							$new_rate	= 12;
							$rate_type	= 'ag';
						} elseif ($new_speed == 18000000) {
							$new_rate	= 18;
							$rate_type	= 'ag';
						} elseif ($new_speed == 24000000) {
							$new_rate	= 24;
							$rate_type	= 'ag';
						} elseif ($new_speed == 36000000) {
							$new_rate	= 36;
							$rate_type	= 'ag';
						} elseif ($new_speed == 48000000) {
							$new_rate	= 48;
							$rate_type	= 'ag';
						} elseif ($new_speed == 54000000) {
							$new_rate	= 54;
							$rate_type	= 'ag';
						} elseif ($new_speed == 6500000) {
							$rate_type	= 'n';
						} elseif ($new_speed == 13000000 && isset($new_mcs[$new_speed_index])) {
							$rate_type	= 'n';
						} elseif ($new_speed == 19500000 && isset($new_mcs[$new_speed_index])) {
							$rate_type	= 'n';
						} elseif ($new_speed == 26000000 && isset($new_mcs[$new_speed_index])) {
							$rate_type	= 'n';
						} elseif ($new_speed == 39000000 && isset($new_mcs[$new_speed_index])) {
							$rate_type	= 'n';
						} elseif ($new_speed == 52000000 && isset($new_mcs[$new_speed_index])) {
							$rate_type	= 'n';
						} elseif ($new_speed == 58500000 && isset($new_mcs[$new_speed_index])) {
							$rate_type	= 'n';
						} elseif ($new_speed == 65000000 && isset($new_mcs[$new_speed_index])) {
							$rate_type	= 'n';
						} elseif ($new_speed == 13000000 && isset($new_mcs[$new_speed_index])) {
							$rate_type	= 'n';
						} elseif ($new_speed == 26000000 && isset($new_mcs[$new_speed_index])) {
							$rate_type	= 'n';
						} elseif ($new_speed == 39000000 && isset($new_mcs[$new_speed_index])) {
							$rate_type	= 'n';
						} elseif ($new_speed == 52000000 && isset($new_mcs[$new_speed_index])) {
							$rate_type	= 'n';
						} elseif ($new_speed == 78000000 && isset($new_mcs[$new_speed_index])) {
							$rate_type	= 'n';
						} elseif ($new_speed == 104000000 && isset($new_mcs[$new_speed_index])) {
							$rate_type	= 'n';
						} elseif ($new_speed == 117000000 && isset($new_mcs[$new_speed_index])) {
							$rate_type	= 'n';
						} elseif ($new_speed == 130000000 && isset($new_mcs[$new_speed_index])) {
							$rate_type	= 'n';
						} elseif ($new_speed == 1000000) {
							$new_rate	= 1;
							$rate_type	= 'b';
						} elseif ($new_speed == 2000000) {
							$new_rate	= 2;
							$rate_type	= 'b';
						} elseif ($new_speed == 5500000) {
							$new_rate	= '5.5';
							$rate_type	= 'b';
						} elseif ($new_speed == 11000000) {
							$new_rate	= 11;
							$rate_type	= 'b';
						} else {
							throw new exception("unknown wireless speed. you are trying to set speed: '".$new_speed."' on interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."'", 20906);
						}
						
						//802.11a and g use the same MCS
						if ($rate_type == 'ag') {
							
							if (!isset($ag_rates)) {
								$ag_rates = $new_rate . "Mbps";
							} else {
								$ag_rates .= "," . $new_rate . "Mbps";
							}
							
						} elseif ($rate_type == 'n') {
							
							if (!isset($n_rates)) {
								$n_rates = "mcs-" . $new_mcs[$new_speed_index];
							} else {
								$n_rates .= ",mcs-" . $new_mcs[$new_speed_index];
							}
							
						} elseif ($rate_type == 'b') {
								
							if (!isset($b_rates)) {
								$b_rates = $new_rate . "Mbps";
							} else {
								$b_rates .= "," . $new_rate . "Mbps";
							}
						}
					}
					
					if (isset($ag_rates)) {
						
						//set all rates supported before invoking new basic and supported rates, because mt must have supported rates rates for the basic rates that are selected
						$command 			= "/interface wireless set [find where name=\"".$interface_name."\"] supported-rates-a/g=\"6Mbps,9Mbps,12Mbps,18Mbps,24Mbps,36Mbps,48Mbps,54Mbps";
						
						//set ag_rates supported and basic
						$command2 			= "/interface wireless set [find where name=\"".$interface_name."\"] basic-rates-a/g=\"".$ag_rates."\"";
						$command3 			= "/interface wireless set [find where name=\"".$interface_name."\"] supported-rates-a/g=\"".$ag_rates."\"";

						$device_reply 		= $this->_device->execute_command($command);
						$device_reply 		= $this->_device->execute_command($command2);
						$device_reply 		= $this->_device->execute_command($command3);
						
					}
					
					if (isset($n_rates)) {
					
						//set all rates supported before invoking new basic and supported rates, because mt must have supported rates rates for the basic rates that are selected
						$command 			= "/interface wireless set [find where name=\"".$interface_name."\"] ht-supported-mcs=\"mcs-0,mcs-1,mcs-2,mcs-3,mcs-4,mcs-5,mcs-6,mcs-7,mcs-8,mcs-9,mcs-10,mcs-11,mcs-12,mcs-13,mcs-14,mcs-15,mcs-16,mcs-17,mcs-18,mcs-19,mcs-20,mcs-21,mcs-22,mcs-23\"";
						
						//set n_rates supported and basic
						$command2 			= "/interface wireless set [find where name=\"".$interface_name."\"] ht-basic-mcs=\"".$n_rates."\"";
						$command3 			= "/interface wireless set [find where name=\"".$interface_name."\"] ht-supported-mcs=\"".$n_rates."\"";
						
						$device_reply 		= $this->_device->execute_command($command);
						$device_reply 		= $this->_device->execute_command($command2);
						$device_reply 		= $this->_device->execute_command($command3);
					}
					
					if (isset($b_rates)) {

						//set all rates supported before invoking new basic and supported rates, because mt must have supported rates rates for the basic rates that are selected
						$command 			= "/interface wireless set [find where name=\"".$interface_name."\"] supported-rates-b=\"1Mbps,2Mbps,5.5Mbps,11Mbps\"";
						
						//set b_rates supported and basic
						$command2 			= "/interface wireless set [find where name=\"".$interface_name."\"] basic-rates-b=\"".$b_rates."\"";
						$command3 			= "/interface wireless set [find where name=\"".$interface_name."\"] supported-rates-b=\"".$b_rates."\"";
						
						
						$device_reply 		= $this->_device->execute_command($command);
						$device_reply 		= $this->_device->execute_command($command2);
						$device_reply 		= $this->_device->execute_command($command3);
					}
					
					
					//set configured rates
					$command4 			= "/interface wireless set [find where name=\"".$interface_name."\"] rate-set=\"configured\"";
					$device_reply 		= $this->_device->execute_command($command4);
				}

			} else {
				throw new exception("expand method set speed to handle interface type ".$if_type." for interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."'", 20904);
			}
			
			
			
			$after_speeds		= $get_current_speed->get_configured_speed(true);
			$diff 				= count(array_diff($after_speeds, $new_speeds)) + count(array_diff($new_speeds, $after_speeds));
			
			if ($diff > 0) {
				throw new exception("we could not change the speed for interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."'", 20905);
			} else {
				
				if ($this->_mcs !== null) {
				
					$after_metrics		= $get_current_speed->get_configured_speed_metrics(false);
				
					foreach ($after_metrics as $metric_index => $after_metric) {
				
						if (isset($after_metric['mcs'])) {
							$after_mcs[$metric_index] = $after_metric['mcs'];
						}
					}
				
					$mcs_diff = count(array_diff($after_mcs, $new_mcs)) + count(array_diff($new_mcs, $after_mcs));

					if ($mcs_diff > 0) {
						throw new exception("we could not change the speed for interface with name: '".$interface_name."' on device: '".$this->_device->get_fqdn()."'", 20907);
					} 
				}
			}


		} else {
			//no change in rates we are done
		}
	}
}