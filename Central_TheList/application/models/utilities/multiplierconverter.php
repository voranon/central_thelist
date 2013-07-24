<?php
//exception codes 8500-8599

class thelist_utility_multiplierconverter
{
	
	function __construct()
	{
		
   	}
   	
   	public function convert_wireless_rate_to_int($wireless_rate, $channel_width, $wireless_protocol)
   	{
   		//this function should always return bit/s
   		//$channel_width must be in MHz
   		//by default 802.11 uses a 800 nano sec guard interval
   		
   		if (preg_match("/(([0-9]+)|([0-9]+\.[0-9]+))(Kbps|Mbps|Gbps)/", $wireless_rate, $bitrate_raw)) {

   			if ($wireless_protocol == '802.11a' || $wireless_protocol == '802.11g') {
   				
   				if ($bitrate_raw['1'] == 6) {
   					return $this->calculate_wireless_peak_data_rate('BPSK', '1/2', $channel_width, 1, '800', $wireless_protocol);
   				} elseif ($bitrate_raw['1'] == 9) {
   					return $this->calculate_wireless_peak_data_rate('BPSK', '3/4', $channel_width, 1, '800', $wireless_protocol);
   				} elseif ($bitrate_raw['1'] == 12) {
   					return $this->calculate_wireless_peak_data_rate('QPSK', '1/2', $channel_width, 1, '800', $wireless_protocol);
   				} elseif ($bitrate_raw['1'] == 18) {
   					return $this->calculate_wireless_peak_data_rate('QPSK', '3/4', $channel_width, 1, '800', $wireless_protocol);
   				} elseif ($bitrate_raw['1'] == 24) {
   					return $this->calculate_wireless_peak_data_rate('16QAM', '1/2', $channel_width, 1, '800', $wireless_protocol);
   				} elseif ($bitrate_raw['1'] == 36) {
   					return $this->calculate_wireless_peak_data_rate('16QAM', '3/4', $channel_width, 1, '800', $wireless_protocol);
   				} elseif ($bitrate_raw['1'] == 48) {
   					return $this->calculate_wireless_peak_data_rate('64QAM', '2/3', $channel_width, 1, '800', $wireless_protocol);
   				} elseif ($bitrate_raw['1'] == 54) {
   					return $this->calculate_wireless_peak_data_rate('64QAM', '3/4', $channel_width, 1, '800', $wireless_protocol);
   				}
   			} elseif($wireless_protocol == '802.11b') {

   				if ($bitrate_raw['1'] == 1) {
   					return 1000000;
   				} elseif ($bitrate_raw['1'] == 2) {
   					return 2000000;
   				} elseif ($bitrate_raw['1'] == 5.5) {
   					return 5500000;
   				} elseif ($bitrate_raw['1'] == 11) {
   					return 11000000;
   				}
   			}
   			

   		} elseif (preg_match("/mcs-([0-9]+)/", $wireless_rate, $mcs_rate_raw)) {

   			if ($mcs_rate_raw['1'] == 0) {
   				return $this->calculate_wireless_peak_data_rate('BPSK', '1/2', $channel_width, 1, '800', $wireless_protocol);
   			} elseif ($mcs_rate_raw['1'] == 1) {
   				return $this->calculate_wireless_peak_data_rate('QPSK', '1/2', $channel_width, 1, '800', $wireless_protocol);
   			} elseif ($mcs_rate_raw['1'] == 2) {
   				return $this->calculate_wireless_peak_data_rate('QPSK', '3/4', $channel_width, 1, '800', $wireless_protocol);
   			} elseif ($mcs_rate_raw['1'] == 3) {
   				return $this->calculate_wireless_peak_data_rate('16QAM', '1/2', $channel_width, 1, '800', $wireless_protocol);
   			} elseif ($mcs_rate_raw['1'] == 4) {
   				return $this->calculate_wireless_peak_data_rate('16QAM', '3/4', $channel_width, 1, '800', $wireless_protocol);
   			} elseif ($mcs_rate_raw['1'] == 5) {
   				return $this->calculate_wireless_peak_data_rate('64QAM', '2/3', $channel_width, 1, '800', $wireless_protocol);
   			} elseif ($mcs_rate_raw['1'] == 6) {
   				return $this->calculate_wireless_peak_data_rate('64QAM', '3/4', $channel_width, 1, '800', $wireless_protocol);
   			} elseif ($mcs_rate_raw['1'] == 7) {
   				return $this->calculate_wireless_peak_data_rate('64QAM', '5/6', $channel_width, 1, '800', $wireless_protocol);
   			} elseif ($mcs_rate_raw['1'] == 8) {
   				return $this->calculate_wireless_peak_data_rate('BPSK', '1/2', $channel_width, 2, '800', $wireless_protocol);
   			} elseif ($mcs_rate_raw['1'] == 9) {
   				return $this->calculate_wireless_peak_data_rate('QPSK', '1/2', $channel_width, 2, '800', $wireless_protocol);
   			} elseif ($mcs_rate_raw['1'] == 10) {
   				return $this->calculate_wireless_peak_data_rate('QPSK', '3/4', $channel_width, 2, '800', $wireless_protocol);
   			} elseif ($mcs_rate_raw['1'] == 11) {
   				return $this->calculate_wireless_peak_data_rate('16QAM', '1/2', $channel_width, 2, '800', $wireless_protocol);
   			} elseif ($mcs_rate_raw['1'] == 12) {
   				return $this->calculate_wireless_peak_data_rate('16QAM', '3/4', $channel_width, 2, '800', $wireless_protocol);
   			} elseif ($mcs_rate_raw['1'] == 13) {
   				return $this->calculate_wireless_peak_data_rate('64QAM', '2/3', $channel_width, 2, '800', $wireless_protocol);
   			} elseif ($mcs_rate_raw['1'] == 14) {
   				return $this->calculate_wireless_peak_data_rate('64QAM', '3/4', $channel_width, 2, '800', $wireless_protocol);
   			} elseif ($mcs_rate_raw['1'] == 15) {
   				return $this->calculate_wireless_peak_data_rate('64QAM', '5/6', $channel_width, 2, '800', $wireless_protocol);
   			}
   		}
   	}
   	
   	public function calculate_wireless_peak_data_rate($modulation, $coding_rate, $channel_width, $spatial_streams, $guard_interval, $wireless_protocol)
   	{
  		//$guard_interval and  $symbol_length should be in nano seconds 
  
   		if (preg_match("/^802.11/", $wireless_protocol)) {
   			
   			$symbol_length = 3200;
   			$per_carrier_symbol_rate = 1000000000 / ($symbol_length + $guard_interval);

   			if ($wireless_protocol == '802.11n') {
   				
   				if ($channel_width == 20) {
   					$number_of_carriers 	= 56;
   					$protection_carriers 	= 4;
   					$baud_rate 				= $per_carrier_symbol_rate * ($number_of_carriers - $protection_carriers);
   					
   				} elseif ($channel_width == 40) {
   					$number_of_carriers 	= 114;
   					$protection_carriers 	= 6;
   					$baud_rate 				= $per_carrier_symbol_rate * ($number_of_carriers - $protection_carriers);
   				}
   				
   				
   			} elseif ($wireless_protocol == '802.11a') {
   				
   				if ($channel_width == 20) {
   					$number_of_carriers 	= 52;
   					$protection_carriers 	= 4;
   					$baud_rate = $per_carrier_symbol_rate * ($number_of_carriers - $protection_carriers);
   				} elseif ($channel_width == 40) {
   					$number_of_carriers 	= 104;
   					$protection_carriers 	= 8;
   					$baud_rate 				= $per_carrier_symbol_rate * ($number_of_carriers - $protection_carriers);
   				}
   				
   			} elseif ($wireless_protocol == '802.11g') {
   				
   				if ($channel_width == 20) {
   					$number_of_carriers 	= 52;
   					$protection_carriers 	= 4;
   					$baud_rate 				= $per_carrier_symbol_rate * ($number_of_carriers - $protection_carriers);
   				} elseif ($channel_width == 40) {
   					$number_of_carriers 	= 104;
   					$protection_carriers 	= 8;
   					$baud_rate 				= $per_carrier_symbol_rate * ($number_of_carriers - $protection_carriers);
   				}
   				
   			} elseif ($wireless_protocol == '802.11b') {
   						
   				if ($channel_width == 20) {
					//no support for 802.11b
   				}
   			}
   		}
   		
   		if (preg_match("/\//", $coding_rate)) {
   			$exploded_coding_rate = explode('/', $coding_rate);
   			$codeing_rate_resolved = $exploded_coding_rate['0'] / $exploded_coding_rate['1'];
   		} else {
   			$codeing_rate_resolved = $coding_rate;
   		}

   		$raw_data_rate =  $baud_rate * $this->modulation_bits_per_hz($modulation) * $spatial_streams * $codeing_rate_resolved;

   		return $raw_data_rate;
   		
   	}
   	
   	public function modulation_bits_per_hz($modulation)
   	{
   		
   		$clean_modulation = strtoupper($modulation);

   		if ($clean_modulation == 'BPSK') {
   			$bits_per_hz = 1;
   		} elseif ($clean_modulation == 'QPSK') {
   			$bits_per_hz = 2;
   		} elseif ($clean_modulation == '4QAM') {
   			$bits_per_hz = 2;
   		} elseif ($clean_modulation == '8PSK') {
   			$bits_per_hz = 3;
   		} elseif ($clean_modulation == '16QAM') {
   			$bits_per_hz = 4;
   		} elseif ($clean_modulation == '32QAM') {
   			$bits_per_hz = 5;
   		} elseif ($clean_modulation == '64QAM') {
   			$bits_per_hz = 6;
   		} elseif ($clean_modulation == '128QAM') {
   			$bits_per_hz = 7;
   		} elseif ($clean_modulation == '256QAM') {
   			$bits_per_hz = 8;
   		} elseif ($clean_modulation == '512QAM') {
   			$bits_per_hz = 9;
   		} elseif ($clean_modulation == '1024QAM') {
   			$bits_per_hz = 10;
   		} else {
   			throw new exception("unknown modulation: ".$modulation." ", 8500);
   		}
   		
   		return $bits_per_hz;
   	}
   	
   	public function convert_to_bytes($number, $multiplier)
   	{
   		//bytes are based on a base 1024, its different than the base 10 function below
   		if ($multiplier == 'K') {
   		
   			$new_number = $number * 1024;
   			return $new_number;
   		
   		} else if ($multiplier == 'M') {
   		
   			$new_number = $number * 1048576;
   			return $new_number;
   		
   		} else if ($multiplier == 'G') {
   		
   			$new_number = $number * 1073741824;
   			return $new_number;
   		
   		} else if ($multiplier == 'T') {
   		
   			$new_number = $number * 1099511627776;
   			return $new_number;
   		
   		} else if ($multiplier == 'P') {
   		
   			$new_number = $number * 1125899906842624;
   			return $new_number;
   		} 
   	}

	public function convert_to_int($number, $multiplier)
	{
		
		//only convert the multiplier
		//do not change the unit i.e.
		//DO 100 KiB = 100 * 1000 Bytes = 100.000
		//DO NOT convert bytes to bits and return 100KiB = 100 * 1000 * 8 = 800.000
		//user must be able to rely on the unit being unchanged by this method
		
		if ($multiplier == 'Mbit') {
	
			$new_number = $number * 1000000;
			return $new_number;
	
		} else if ($multiplier == 'Kbit') {
	
			$new_number = $number * 1000;
			return $new_number;
	
		} else if ($multiplier == 'bit') {
	
	
			$new_number = $number * 1;
			return $new_number;
	
		} else if ($multiplier == 'Kb') {
	
			$new_number = $number * 1000;
			return $new_number;
	
		} else if ($multiplier == 'b') {
	
	
			$new_number = $number * 1;
			return $new_number;
	
		} else if ($multiplier == 'M') {
	
	
			$new_number = $number * 1000000;
			return $new_number;
	
		} else if ($multiplier == 'k') {
				
				
			$new_number = $number * 1000;
			return $new_number;
	
		} else if ($multiplier == 'G') {
	
	
			$new_number = $number * 1000000000;
			return $new_number;
	
		} else if ($multiplier == 'Gbit') {
	
	
			$new_number = $number * 1000000000;
			return $new_number;
				
		} else if ($multiplier == 's') {
	
	
			$new_number = $number * 1;
			return $new_number;
				
		} else if ($multiplier == 'm') {
	
	
			$new_number = $number * 60;
			return $new_number;
				
		} else if ($multiplier == 'h') {
	
	
			$new_number = $number * 3600;
			return $new_number;
				
		} else if ($multiplier == 'kbit') {
	
	
			$new_number = $number * 1000;
			return $new_number;
	
		} else if ($multiplier == 'kbps') {
	
	
			$new_number = $number * 1000;
			return $new_number;
	
		} else if ($multiplier == 'mbps') {
	
	
			$new_number = $number * 1000000;
			return $new_number;
	
		} else if ($multiplier == 'gbps') {
	
	
			$new_number = $number * 1000000000;
			return $new_number;
	
		} else if ($multiplier == 'Kbps') {
	
	
			$new_number = $number * 1000;
			return $new_number;
	
		} else if ($multiplier == 'Mbps') {

			$new_number = $number * 1000000;
			return $new_number;
	
		} else if ($multiplier == 'Gbps') {
	
			$new_number = $number * 1000000000;
			return $new_number;
	
		} else if ($multiplier == 'KiB') {

			$new_number = $number * 1000;
			return $new_number;
	
		} elseif ($multiplier == 'kB') {

			$new_number = $number * 1000;
			return $new_number;
	
		} else if ($multiplier == 'MiB') {

			$new_number = $number * 1000000;
			return $new_number;
	
		} else if ($multiplier == 'GiB') {

			$new_number = $number * 1000000000;
			return $new_number;
	
		} else if ($multiplier == '...') {
			//if we get wrong result because the window size is too small. this is temp until we fix teh underlying issue of a too small terminal window
			return $number;
	
		} else if ($multiplier == '..') {
			//if we get wrong result because the window size is too small. this is temp until we fix teh underlying issue of a too small terminal window
			return $number;
	
		} else if ($multiplier == '.') {
			//if we get wrong result because the window size is too small. this is temp until we fix teh underlying issue of a too small terminal window
			return $number;
	
		}
	}
}
?>