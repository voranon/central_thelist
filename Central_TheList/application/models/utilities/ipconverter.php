<?php

//exception codes 7900-7999

require_once 'Net/IPv4.php';

class thelist_utility_ipconverter
{
	private $_previous_ips_for_processing=null;
	
	function __construct()
	{
		
   	}

	public function convert_dotted_subnet_to_cidr($subnet_mask)
	{
		return 32-log((ip2long($subnet_mask) ^ ip2long('255.255.255.255'))+1,2);
	}
	
	public function convert_cidr_subnet_to_dotted($cidr_mask)
	{
		if (preg_match("/\/([0-9]+)/", $cidr_mask, $cidr_result)) {
			//we need the slash for the replace to work.
			$cidr_mask = $cidr_result['1'];
		}
		
		$ip_calc = new Net_IPv4();
		$ip_calc->bitmask = $cidr_mask;
		$ip_calc->calculate();

		return $ip_calc->netmask;
		
	}
	
	public function convert_ips_to_ranges($ip_array)
	{
		//$ip_array must be 2 dimentional array with the 2nd having 3 indexes: ip_subnet_address, ip_subnet_cidr_mask, ip_address
		//without the subnet and mask we cannot figure out the ranges
		
		if (is_array($ip_array)) {
			
			foreach ($ip_array as $ip_address) {
				
				//create an array with the addresses so we can plot them
				if (!isset($plotting_array[$ip_address['ip_subnet_address']])) {
					
					$net = Net_IPv4::parseAddress($ip_address['ip_subnet_address']."/".$ip_address['ip_subnet_cidr_mask']);
					
					$ips_in_range = $this->get_all_ips_in_range($net->network, $net->broadcast);

					foreach($ips_in_range as $single_ip_address) {
						
						$plotting_array[$ip_address['ip_subnet_address']][$single_ip_address] = '';
					}
				}

				$plotting_array[$ip_address['ip_subnet_address']][$ip_address['ip_address']] = 1;
			}
			
			//now we construct the ranges
			if (isset($plotting_array)) {
				
				//variable to check if we are collecting ips on a range
				//or ideling/closing a range
				$in_range=0;
				
				$k=0;
				foreach($plotting_array as $subnet_address => $the_subnet){
				
					$return_array[$subnet_address]['ip_subnet_address'] = $subnet_address;
					
					foreach($the_subnet as $ip_address => $valid){
						
						if ($in_range == 0 && $valid == 1) {
							
							$return_array[$subnet_address]['ranges'][$k]['start'] = $ip_address;
							$in_range = 1;
						}
						
						if ($in_range == 1 && $valid == 0) {
								
							$return_array[$subnet_address]['ranges'][$k]['end'] = $previous_address;
							$in_range = 0;
							$k++;
						}
						
						$previous_address = $ip_address;

					}
				}
			}
			
			//reset the indexes, the $k valuse keeps incrementing with all subnets
			if (isset($return_array)) {
				
				//remove the ip dotted index and replace it with a regular index
				$return_array = array_values($return_array);

				foreach($return_array as $index => $range) {
					
					$return_array[$index]['ranges'] = array_values($return_array[$index]['ranges']);
					
				}	
			}
			
		} else {
			
			//not array nothing we can do
			
		}
		
		if (isset($return_array)) {
			
			return $return_array;
			
		} else {
			
			return false;
			
		}

	}
	
	public function get_all_possible_subnets_from_ips($single_addresses)
	{
		$confirmed_subnets 		= array();
		
		$r=0;
		while ($r < 10) {

			$subnets	= $this->get_maximum_cidr_subnet_aggregation($single_addresses);

			if (isset($subnets['standalone'])) {

				$diff_one = array_diff($single_addresses, $subnets['standalone']);
				$diff_two = array_diff($subnets['standalone'], $single_addresses);

				$total_diff = array_merge($diff_one, $diff_two);
				$differances = count($total_diff);
				
				$single_addresses = $subnets['standalone'];
				
				//when this gets merged in we dont want the stand alone ips as part of it
				unset($subnets['standalone']);
				
			} else {

				$confirmed_subnets = array_merge($confirmed_subnets, $subnets);
				return $confirmed_subnets;
			}
			
			$confirmed_subnets = array_merge($confirmed_subnets, $subnets);
		
			if ($differances != 0) {
				//empty the array
				$differances = array();

			} else {

				$confirmed_subnets['standalone'] = $single_addresses;
				return $confirmed_subnets;
				
			} 
			
			$r++;
		}
		
		//if we dident get the job done in 10 tries
		throw new exception("ip addresses could for some reason not get turned into subnets. We tried ".$r." times", 7902);

	}
	
	
	private function get_maximum_cidr_subnet_aggregation($ip_address_array)
	{

		//unlike the convert_ips_to_ranges function this only requires the ip addresses in an array
		if (is_array($ip_address_array)) {
			
			foreach($ip_address_array as $master_range_ip) {
				//if this is a valid ip
				if (Net_IPv4::validateIP($master_range_ip)) {
					
					//break up the ip into its octets
					$one_ip =	explode('.', $master_range_ip);

					if (!isset($getting_ordered[$one_ip['0']][$one_ip['1']][$one_ip['2']][$one_ip['3']])) {
						$getting_ordered[$one_ip['0']][$one_ip['1']][$one_ip['2']][] = $one_ip['3'];
					}
					
				} else {
					throw new exception("This is not a valid ip address: ".$master_range_ip." ", 7901);
				}
			}
			
			//order the ips so the ips are sorted numberically 
			ksort($getting_ordered, SORT_NUMERIC);
			$i=0;
			foreach($getting_ordered as $first_octet => $first_array) {
				ksort($first_array, SORT_NUMERIC);
				foreach($first_array as $second_octet => $second_array) {
					ksort($second_array, SORT_NUMERIC);
					foreach($second_array as $third_octet => $third_array) {
						
						//this one should be sorted by value not key
						sort($third_array, SORT_NUMERIC);
						
						foreach($third_array as $fourth_octet) {

							$ordered_ips[$i]['0'] = $first_octet;
							$ordered_ips[$i]['1'] = $second_octet;
							$ordered_ips[$i]['2'] = $third_octet;
							$ordered_ips[$i]['3'] = $fourth_octet;
							
							$i++;
						}
					}
				}
			}
			
			//we need one extra ip address because of the way the app is written
			//without it the loop exits without finishing 
			//so we pick an invalid ip as the last in the array
			$ordered_ips[$i]['0'] = 0;
			$ordered_ips[$i]['1'] = 0;
			$ordered_ips[$i]['2'] = 0;
			$ordered_ips[$i]['3'] = 0;

			$ip_calc = new Net_IPv4();
			
			$in_subnet 				= 0;
			$total_ips				= count($ordered_ips);
			
			$i=0;
			$s=0;
			$r=0;
			foreach($ordered_ips as $ip) {
				
				if ($in_subnet == 0) {

					$current_subnet_size 	= 30;
					
					$ip_calc->ip = "".$ip['0'].".".$ip['1'].".".$ip['2'].".".$ip['3']."";
					$ip_calc->bitmask = $current_subnet_size;
					$ip_calc->calculate();

					if (Net_IPv4::getSubnet($ip_calc->network, $current_subnet_size) == "".$ip['0'].".".$ip['1'].".".$ip['2'].".".$ip['3']."") {
						
						$in_subnet = 1;
						$current_subnet_address 	= $ip;
						$current_broadcast_address	= explode('.', $ip_calc->broadcast);
						
						//This is a valid ip because it is first in subnet
						$valid_next_ip = 'yes';
					}
				}
				
				//if we are still not in network after trying to match this ip against a /30 subnet this is a stand alone address
				if ($in_subnet == 0) {
					$standalone_addresses[] =  "".$ip['0'].".".$ip['1'].".".$ip['2'].".".$ip['3']."";
				}
				//we must be in subnet
				
				if ($in_subnet == 1) {
					
					//the very first ip does not have a previous ip
					if (isset($previous_ip)) {
						
						$oct1plusone	= $previous_ip['0'] + 1;
						$oct2plusone	= $previous_ip['1'] + 1;
						$oct3plusone	= $previous_ip['2'] + 1;
						$oct4plusone	= $previous_ip['3'] + 1;
						
						//is this the next ip in the range?
						if (
							($ip['0'] == $previous_ip['0'] && $ip['1'] == $previous_ip['1'] && $ip['2'] == $previous_ip['2'] && $ip['3'] == $oct4plusone) 
							|| 	($previous_ip['3'] == 255 && ($ip['0'] == $previous_ip['0'] && $ip['1'] == $previous_ip['1'] && $ip['2'] == $oct3plusone && $ip['3'] == 0))
							|| 	($previous_ip['3'] == 255 && $previous_ip['2'] == 255 && ($ip['0'] == $previous_ip['0'] && $ip['1'] == $oct2plusone && $ip['2'] == 0 && $ip['3'] == 0))
							|| 	($previous_ip['3'] == 255 && $previous_ip['2'] == 255 && $previous_ip['1'] == 255 && ($ip['0'] == $oct1plusone && $ip['1'] == 0 && $ip['2'] == 0 && $ip['3'] == 0))
						) {
							$valid_next_ip = 'yes';
						} else {
							$valid_next_ip = 'no';
						}
					}

					//is this a valid next ip 
					if ($valid_next_ip == 'yes') {

						if (isset($previous_ip)) {
							
							//did we reach the broadcast with the previous ip address?
							if (
							$previous_ip['0'] == $current_broadcast_address['0'] 
							&& $previous_ip['1'] == $current_broadcast_address['1'] 
							&& $previous_ip['2'] == $current_broadcast_address['2']
							&& $previous_ip['3'] == $current_broadcast_address['3']
							) {
								
								//we decrement ( /23 is bigger than /24 ) the subnet cidr if last ip == the current broadcast address
								//but only if the new bigger subnet has the same subnet address as the current one, otherwise they are seperate networks.
								//we need to do this on the previous because we dont want to lock in a subnet as complet just to find that the next ip
								//is also part of that same subnet.
								
								$proposed_new_subnet_size = $current_subnet_size - 1;
								
								$ip_calc->ip = "".$ip['0'].".".$ip['1'].".".$ip['2'].".".$ip['3']."";
								$ip_calc->bitmask = $proposed_new_subnet_size;
								$ip_calc->calculate();
	
								//if the the current subnet address is not the same
								//for the new ip address if we make the subnet bigger, then we need to complete the current subnet and start a new one
								if ((Net_IPv4::getSubnet($ip_calc->network, $proposed_new_subnet_size) == "".$previous_subnet_address['0'].".".$previous_subnet_address['1'].".".$previous_subnet_address['2'].".".$previous_subnet_address['3']."") == false) {

									$completed_subnets[$s]['subnet_address'] =  "".$current_subnet_address['0'].".".$current_subnet_address['1'].".".$current_subnet_address['2'].".".$current_subnet_address['3']."";
									$completed_subnets[$s]['subnet_cidr'] = $current_subnet_size;
									$s++;

									//now clean up
									$in_subnet 										= 0;
									$tracking_current_subnet 						= array();
									$tracking_current_subnet['not_complete'][] 		=  $ip;
									
								} else {
									
									//since we reach the end of a particular subnet but we are still going 
									//we reorganize tracking so we know what ips are part of a completed subnet
									//the ips still share the same subnet address, but the move the goal post by setting a new broadcast
									$tracking_current_subnet['current_complete']					= $tracking_current_subnet['not_complete'];
									$tracking_current_subnet['current_complete']['subnet_address']	= $current_subnet_address;
									$tracking_current_subnet['current_complete']['subnet_size']		= $current_subnet_size;
									$tracking_current_subnet['not_complete'][] 						=  $ip;
	
									//make the current subnet bigger
									$current_subnet_size = $current_subnet_size - 1;
									//set a new goal
									$current_broadcast_address	= explode('.', $ip_calc->broadcast);

								}
							} else {
								
								//this is NOT the very first ip but it still need to be tracked
								$tracking_current_subnet['not_complete'][] =  $ip;
								
							}
							
						} else {
							
							//this is the very first ip and it needs to be part of tracking
							$tracking_current_subnet['not_complete'][] =  $ip;
							
						}
						
					} else {
						
						//this means the ip address did not fulfill the requirement of being next in line
						//we need to check if the last address was the broadcast because that means that constitudes a complete subnet
						
						if (
						$previous_ip['0'] == $current_broadcast_address['0'] 
						&& $previous_ip['1'] == $current_broadcast_address['1'] 
						&& $previous_ip['2'] == $current_broadcast_address['2']
						&& $previous_ip['3'] == $current_broadcast_address['3']
						) {

							$completed_subnets[$s]['subnet_address'] =  "".$current_subnet_address['0'].".".$current_subnet_address['1'].".".$current_subnet_address['2'].".".$current_subnet_address['3']."";
							$completed_subnets[$s]['subnet_cidr'] = $current_subnet_size;
							$s++;
							
						} else {
	
							//if the last address was not the last ip needed to complete a full subnet
							//then we check if we reached some size subnet in our persuit 
							if (isset($tracking_current_subnet['current_complete']['subnet_size'])) {
								
								//if we found any size subnet then add it to the list
								$completed_subnets[$s]['subnet_address'] =  "".$current_subnet_address['0'].".".$current_subnet_address['1'].".".$current_subnet_address['2'].".".$current_subnet_address['3']."";
								$completed_subnets[$s]['subnet_cidr'] = $tracking_current_subnet['current_complete']['subnet_size'];
								$s++;
								
								//since there was a subnet we need to remove those good ips (completed subnet) from the range of "not completed" so we can determine the stand alone ips.
								foreach ($tracking_current_subnet['not_complete'] as $nc_index => $not_complete_address) {

									//if the not completed address is not part of the subnet we just finished then it is a stand alone address
									if (!isset($tracking_current_subnet['current_complete'][$nc_index])) {
										
										$standalone_addresses[] =  "".$not_complete_address['0'].".".$not_complete_address['1'].".".$not_complete_address['2'].".".$not_complete_address['3']."";
										
									}
								}
							} else {
								
								//if there are ips in the tracking that are not turned into a subnet they are stand alone
								if (isset($tracking_current_subnet['not_complete']['0'])) {
									
									foreach ($tracking_current_subnet['not_complete'] as $not_complete_address) {
										
										//if the not completed address is not part of the subnet we just finished then it is a stand alone address
										$standalone_addresses[] =  "".$not_complete_address['0'].".".$not_complete_address['1'].".".$not_complete_address['2'].".".$not_complete_address['3']."";
									
										
									}
								}
							}
						}
						
						//now clean up
						$in_subnet 										= 0;
						$tracking_current_subnet 						= array();
						$tracking_current_subnet['not_complete'][] 		=  $ip;
						
					}

					
					//there is a problem in the fact that the ip that is currently rolling through could be the subnet address of the next network, but it will never get a chance because
					//we were in $in_subnet == 1 when we started.
					//we fix this by trying the new address against the opening.
					if ($in_subnet == 0) {

						$current_subnet_size 	= 30;
						
						$ip_calc->ip = "".$ip['0'].".".$ip['1'].".".$ip['2'].".".$ip['3']."";
						$ip_calc->bitmask = $current_subnet_size;
						$ip_calc->calculate();
	
						if (Net_IPv4::getSubnet($ip_calc->network, $current_subnet_size) == "".$ip['0'].".".$ip['1'].".".$ip['2'].".".$ip['3']."") {
							
							$in_subnet = 1;
							$current_subnet_address 	= $ip;
							$current_broadcast_address	= explode('.', $ip_calc->broadcast);
							
							//This is a valid ip because it is first in subnet
							$valid_next_ip = 'yes';
						}
					}
					//record the subnet address
					$previous_subnet_address 	= $current_subnet_address;
				}

				$previous_ip 				= $ip;
					
				$i++;
			}

		} else {
			throw new exception('we need an array to process', 7900);
		}

		if (isset($standalone_addresses)) {
			
			//these ips did not make it  but there is still the chance they constitude
			//i.e. start 10.245.160.0 - now at 10.245.161.128 and next ip is not in the range
			//that means we get 10.245.160.0/24 but 10.245.161.0 - 10.245.161.128 become stand alone
			//so any function that calls this method, should run this method again until no subnets are possible in the standalone return

			$completed_subnets['standalone'] = $standalone_addresses;
			
		}

		if (isset($completed_subnets)) {
			return $completed_subnets;
		} else {
			return false;			
		}
	}
	
	
	public function get_all_ips_in_range($first_ip, $last_ip)
	{
		//are these valid ips?
		$first_ip_obj	= new Thelist_Deviceinformation_ipaddressentry($first_ip);
		$last_ip_obj	= new Thelist_Deviceinformation_ipaddressentry($last_ip);
		
		//validate that the first ipaddress is smaller than the last
		if ($first_ip_obj->get_ipv4_first_octet() > $last_ip_obj->get_ipv4_first_octet()) {
			throw new exception("first ip address is larger than last ip", 7903);
		} elseif ($first_ip_obj->get_ipv4_second_octet() > $last_ip_obj->get_ipv4_second_octet()) {
			throw new exception("first ip address is larger than last ip", 7904);
		} elseif ($first_ip_obj->get_ipv4_third_octet() > $last_ip_obj->get_ipv4_third_octet()) {
			throw new exception("first ip address is larger than last ip", 7905);
		} elseif ($first_ip_obj->get_ipv4_fourth_octet() > $last_ip_obj->get_ipv4_fourth_octet()) {
			throw new exception("first ip address is larger than last ip", 7906);
		}

		$quad1 = explode(".",$first_ip);
		$quad2 = explode(".",$last_ip);
	
		reset ($quad1);
		while (list ($key, $val) = each ($quad1))
		{
			$quad1[$key] = intval($val);
			if ($quad1[$key] < 0 || $quad1[$key] > 255) return array(-2);
		}
		reset ($quad2);
		while (list ($key, $val) = each ($quad2))
		{
			$quad2[$key] = intval($val);
			if ($quad2[$key] < 0 || $quad2[$key] > 255) return array(-2);
		}
	
		$startIP_long = sprintf("%u",ip2long($first_ip));
		$endIP_long = sprintf("%u",ip2long($last_ip));
		$difference = $endIP_long - $startIP_long;
		
		$ip = array();
		$k = 0;
		for ($i = $startIP_long; $i <= $endIP_long; $i++)
		{
		$temp = long2ip($i);
	
		//this is a total hack. there must be a better way.
		$thisQuad = explode(".",$temp);
		if ($thisQuad[3] >= 0 && $thisQuad[3] <= 255)
		$ip[$k++] = $temp;
		}
	
		return $ip;
		} 

}
?>