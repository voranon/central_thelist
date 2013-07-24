<?php 

//exception codes 8000-8099

require_once 'Net/IPv4.php';

class thelist_model_ipsubnet
{
	private $_ip_subnet_id;
	private $_ip_subnet_master_id;
	private $_ip_subnet_address;
	private $_ip_broadcast_address;
	private $_ip_subnet_cidr_mask;
	private $_ip_subnet_dotted_decimal_mask=null;
	private $_ip_addresses=null;
	private $_child_subnets=null;
	private $_inuse_host_ips=null;
	private $_available_host_ips=null;
	private $_child_subnets_recursively=null;
	private $_mapped_ips=null;

	public function __construct($ip_subnet_id)
	{
		$this->_ip_subnet_id = $ip_subnet_id;
		

		
		$sql = 	"SELECT * FROM ip_subnets 
				WHERE ip_subnet_id='".$this->_ip_subnet_id."'
				";
		
		$ip_subnet_detail  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

		$this->_ip_subnet_master_id				= $ip_subnet_detail['ip_subnet_master_id'];
		$this->_ip_subnet_address				= $ip_subnet_detail['ip_subnet_address'];
		$this->_ip_subnet_cidr_mask				= $ip_subnet_detail['ip_subnet_cidr_mask'];

		$ip_calc = new Net_IPv4();
		$ip_calc->ip 		= $this->_ip_subnet_address;
		$ip_calc->bitmask 	= $this->_ip_subnet_cidr_mask;
			
		$error = $ip_calc->calculate();
		
		if (!is_object($error)) {

		$this->_ip_broadcast_address = $ip_calc->broadcast;
		
		}
	}
	
	
	public function get_ip_broadcast_address()
	{
		return $this->_ip_broadcast_address;
	}
	public function get_ip_subnet_id()
	{
		return $this->_ip_subnet_id;
	}
	
	public function get_public_or_private()
	{
		$private_subnets = array(
		'10.0.0.0/8',
		'192.168.0.0/16',
		'127.0.0.0/8',
		'172.16.0.0/20'
		);
		
		$ip_calc = new Net_IPv4();
		
		foreach($private_subnets as $private_subnet) {
			
			if($ip_calc->ipInNetwork($this->_ip_subnet_address, $private_subnet)){
				
				$sql = 	"SELECT item_id FROM items 
						WHERE item_type='ip_address_type'
						AND item_name='private'
						";
				
				$ip_type  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
				
				return $ip_type;
				
			}
		}
		
		$sql = 	"SELECT item_id FROM items
				WHERE item_type='ip_address_type'
				AND item_name='public'
				";
		
		$ip_type  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		return $ip_type;
				
	}
	
	public function get_ip_subnet_master_id()
	{
		return $this->_ip_subnet_master_id;
	}
	public function get_ip_subnet_address()
	{
		return $this->_ip_subnet_address;
	}
	public function get_ip_subnet_cidr_mask()
	{
		return $this->_ip_subnet_cidr_mask;
	}
	public function get_ip_subnet_dotted_decimal_mask()
	{
		if ($this->_ip_subnet_dotted_decimal_mask == null) {
			$ip_converter 							= new Thelist_Utility_ipconverter();
			$this->_ip_subnet_dotted_decimal_mask 	= $ip_converter->convert_cidr_subnet_to_dotted($this->_ip_subnet_cidr_mask);
		}
		
		return $this->_ip_subnet_dotted_decimal_mask;
	}
	
// 	public function get_subdivided_unused_address_space($subnet_cidr)
// 	{
		
// 		if (is_int($subnet_cidr)) {
			
// 			if ($subnet_cidr > 30) {
// 				throw new exception("/30 is the smallest possible subnet. You wanted: /".$subnet_cidr." ", 8800);
// 			}

		////	remember /23 is larger than a /24. i keep re-evaluating the <= validation, and this comment hopefully stops that :)
		////	not much point in trying to find a subnet that is larger than the original range.
// 			if ($this->_ip_subnet_cidr_mask <= $subnet_cidr) {
				
			////	get the ips in use already. if we carve out another subnet and use ips from that one its ok,
			////	technically they are still on this subnet, but the subnet id will be different and as such not
			////	subject to the gateway problem. (if hosts are assigned out of this subnet, the gateway would have to change in the field)
// 				$inuse_ips		= $this->get_inuse_host_ips();
	
// 				if ($inuse_ips == null) {
					
// 					$child_subnets	= $this->get_child_subnets();
					
// 					if ($this->_ip_subnet_cidr_mask == $subnet_cidr) {
						
// 						if ($child_subnets == null) {
							
				////			return self if there is nothing in use
// 							return $this;
// 						} else {
			////				since this subnet is the correct size but we have child subnets
			////				this will not work
// 							return false;
// 						}
// 					}
	
			////		remove all the ranges that are already carved out if there is any.
// 					if ($child_subnets != null) {
	
				////		make a range out of this allocation
// 						$ip_converter 	= new Thelist_Utility_ipconverter();
// 						$range_of_ips = $ip_converter->get_all_ips_in_range($this->_ip_subnet_address, $this->_ip_broadcast_address);
						
				////		change the $range_of_ips array so the index is the ipaddress as well that will cut down on processing time
// 						foreach($range_of_ips as $master_range_ip) {
								
// 							$master_modified[$master_range_ip] = $master_range_ip;
								
// 						}
	
// 						foreach($child_subnets as $child_subnet) {
							
// 							$child_range_of_ips = $ip_converter->get_all_ips_in_range($child_subnet->get_ip_subnet_address(), $child_subnet->get_ip_broadcast_address());
							
// 							foreach ($child_range_of_ips as $child_ip) {
									
// 								if (isset($master_modified[$child_ip])) {
									
					////				unset that ip from the master range
					////				because it is already taken
// 									unset($master_modified[$child_ip]);
									
// 								}
// 							}						
// 						}
						
// 						$available_subnets = $ip_converter->get_all_possible_subnets_from_ips($master_modified);
	
// 					} else {
						
					////	minic the format returned by the max cidr function
// 						$available_subnets['0']['subnet_address'] 	= $this->_ip_subnet_address;
// 						$available_subnets['0']['subnet_cidr'] 		= $this->_ip_subnet_cidr_mask;
// 					}
	
				////	fake mask to match against
// 					$best_mask_match_found['mask'] = 0;
				////	now test each subnet returned and find out if it is the right size for this request
// 					foreach($available_subnets as $available_subnet) {
						
					////	standalone ips does not have the $available_subnet['subnet_cidr'] index
// 						if (isset($available_subnet['subnet_cidr'])) {
							
					////		if there is an avaliable subnet that matches the size we are seeking then return it.
// 							if ($available_subnet['subnet_cidr'] == $subnet_cidr) {
	
// 								return $this->set_child_subnet($available_subnet['subnet_address'], $available_subnet['subnet_cidr']);
								
// 							} else {
					////			find the smallest subnet that is at least one size larger than the one we are looking for
					////			and remember /23 is larger than a /24
	
// 								if ($available_subnet['subnet_cidr'] < $subnet_cidr && $available_subnet['subnet_cidr'] > $best_mask_match_found['mask']) {
	
// 									$best_mask_match_found['subnet']	= $available_subnet['subnet_address'];
// 									$best_mask_match_found['mask'] 		= $available_subnet['subnet_cidr'];
// 								}
// 							}
// 						}
// 					}
					
				////	we did not find a subnet that fit our size requirement excactly
				////	so now we use the best match to carve out a new subnet if there is one
// 					if ($best_mask_match_found['mask'] == 0) {
						
				////		no subnet big enough to accomodate the subnet
// 						return false;
						
// 					} else {
						
				////		we found one and all that is left to do is create a child for the 
				////		range we carved out and then add a child to that subnet,
					////	then return the second child
// 						$parent	= $this->set_child_subnet($best_mask_match_found['subnet'], $best_mask_match_found['mask']);
						
					////	same subnet mask applies because we are using the first portion of the subnet.
// 						return $parent->set_child_subnet($best_mask_match_found['subnet'], $subnet_cidr);;
// 					}
// 				} else {
				////	there are ips in use
// 					return false;
// 				}
				
// 			} else {
			////	the requested mask is larger than this subnet
// 				return false;
// 			}
			
// 		} else {
// 			throw new exception("CIDR mask must be int you provided:".$subnet_cidr." ", 8801);
// 		}
// 	}
	
	
	
	
	public function get_mapped_ips($refresh=true)
	{
		if ($this->_mapped_ips == null || $refresh == true) {
	
			$this->_mapped_ips = null;
				
			$all_ip_addresses = $this->get_ip_addresses(true);
			
			if ($all_ip_addresses != null) {
					
				foreach($all_ip_addresses as $ip_addresse){
	
					//cannot be subnet or broadcast
					if ($this->_ip_subnet_address != $ip_addresse->get_ip_address() && $this->_ip_broadcast_address != $ip_addresse->get_ip_address()) {
						
						$sql =	"SELECT * FROM ip_address_mapping
								WHERE ip_address_id='".$ip_addresse->get_ip_address_id()."'
								";
							
						$ip_maps  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
							
						if (isset($ip_maps['0'])) {
							
							foreach ($ip_maps as $ip_map) {
							
								//we have to reinstanciate, because if there are multiple maps we would end up setting if_if on the same referance
								$this->_mapped_ips[$ip_map['ip_address_map_id']] = new Thelist_Model_ipaddress($ip_addresse->get_ip_address_id());
								$this->_mapped_ips[$ip_map['ip_address_map_id']]->set_ip_address_map_id($ip_map['if_id']);	
							}
						}
					}
				}
			}
		}
	
		return $this->_mapped_ips;
	}
	
	public function get_inuse_host_ips($refresh=false)
	{
		
		if ($this->get_child_subnets(true) == null) {

			if ($this->_inuse_host_ips == null || $refresh == true) {
	
				$this->_inuse_host_ips = null;
				
				$all_ip_addresses = $this->get_ip_addresses(true);
	
				if ($all_ip_addresses != null) {
				
					foreach($all_ip_addresses as $ip_addresse){
						
						if ($this->_ip_subnet_address != $ip_addresse->get_ip_address() && $this->_ip_broadcast_address != $ip_addresse->get_ip_address()) {
							
							$ip_in_use = $ip_addresse->ip_in_use();
				
							if ($ip_in_use === true) {
								$this->_inuse_host_ips[] = $ip_addresse;
							}
						}
					}
				}
			}
		}

		return $this->_inuse_host_ips;
	}
	
	public function get_available_host_ips($refresh=false)
	{
		if ($this->get_child_subnets(true) == null) {
			
			if ($this->_available_host_ips == null || $refresh == true) {
		
				$this->_available_host_ips = null;
					
				$all_ip_addresses = $this->get_ip_addresses(true);
		
				if ($all_ip_addresses != null) {
						
					foreach($all_ip_addresses as $ip_addresse){
							
						if ($this->_ip_subnet_address != $ip_addresse->get_ip_address() && $this->_ip_broadcast_address != $ip_addresse->get_ip_address()) {
		
							$ip_in_use = $ip_addresse->ip_in_use();
								
							if ($ip_in_use === false) {
								$this->_available_host_ips[] = $ip_addresse;
							}
						}
					}
				}
			}
		}
	
		return $this->_available_host_ips;
	}
	
	public function get_ip_addresses($refresh=true)
	{
		if ($this->_ip_addresses == null || $refresh == true) {
			
			$this->_ip_addresses = null;
			
			$sql=	"SELECT * FROM ip_addresses
					WHERE ip_subnet_id='".$this->_ip_subnet_id."'
					";
			
			$ip_addresses  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			if (isset($ip_addresses['0'])) {
			
				foreach($ip_addresses as $ip_addresse){
						
					$this->_ip_addresses[$ip_addresse['ip_address_id']] = new Thelist_Model_ipaddress($ip_addresse['ip_address_id']);
						
				}
			}
		}

		return $this->_ip_addresses;
	}
	
	public function get_possible_child_subnet_details()
	{
		
		//this method should return an array of available subnets for child subnets
		//with the largest cidr masks possible, then it will be up to the requesting class to
		//carve it up further if needed.
		
		//currently we dont allow this method for the main ranges 0.0.0.0/0 and ::/0
		if ($this->_ip_subnet_cidr_mask == 0) {
			throw new exception("currently we dont allow this method for the main ranges 0.0.0.0/0 and ::/0, why are you trying to kill the server?", 8803);
		} else {
			
			if ($this->get_inuse_host_ips() == null) {

				$ipconverter = new Thelist_Utility_ipconverter();
				
				//all ips in this subnet
				$main_subnet_ip_range = $ipconverter->get_all_ips_in_range($this->_ip_subnet_address, $this->_ip_broadcast_address);
				
				//then we subtract all the child subnets, we dont do this recursively, because we are only dealing with this subnet
				//if a child needs to be sub divided, then use their objects
				$child_subnets = $this->get_child_subnets();
				
				if ($child_subnets != null) {
						
					foreach ($child_subnets as $child_subnet) {
						
						$child_ips	= $ipconverter->get_all_ips_in_range($child_subnet->get_ip_subnet_address(), $child_subnet->get_ip_broadcast_address());
						
						//then remove all the child ips from the main range
						$main_subnet_ip_range = array_diff($main_subnet_ip_range, $child_ips);
						
						if (count($main_subnet_ip_range) == 0) {
							//there are no available ips
							return false;
						}
					}
				}
				
				//we now have the main range without all the children ips
				//we then convert them into possible subnets and return
				$new_possible_subnets = $ipconverter->get_all_possible_subnets_from_ips($main_subnet_ip_range);
				
				//if we have stand alone addresses in the return there is a problem
				//there is a subnet that is misaligned and does not conform to the cidr borders
				//either a child or this subnet
				
				if (!isset($new_possible_subnets['standalone'])) {
					
					//append the parent subnet id so we know where these belong
					foreach($new_possible_subnets as $index => $possible_subnet) {
						$return[$index]['parent_ip_subnet_id'] = $this->_ip_subnet_id;
						$return[$index]['subnet_address']	= $possible_subnet['subnet_address'];
						$return[$index]['subnet_cidr']	= $possible_subnet['subnet_cidr'];
					}
					
					return $return;
					
				} else {
					throw new exception("there is a subnet that is misaligned and does not conform to the cidr borders we are working on ip_subnet_id:".$this->_ip_subnet_id.", this is critical and must be fixed ", 8804);
				}
				 

			} else {
				//this is a connected subnet, there are ips that are inuse
				return false;
			}
		}
	}
	
	public function create_ip_addresses($first_ip_address=null, $last_ip_address=null)
	{
		
		if ($this->_child_subnets == null) {
			$this->get_child_subnets();
		}
		
		//we cannot have any child subnets if we are turning this into ips
		if ($this->_child_subnets == null) {

			$ip_converter 	= new Thelist_Utility_ipconverter();
			
			if ($first_ip_address != null && ($first_ip_address == $last_ip_address)) {
				//single ipaddress
				$range_of_ips['0']	= $first_ip_address;
			} elseif ($first_ip_address == null && $last_ip_address != null) {
				$range_of_ips = $ip_converter->get_all_ips_in_range($this->_ip_subnet_address, $last_ip_address);
			} elseif ($first_ip_address != null && $last_ip_address == null) {
				$range_of_ips = $ip_converter->get_all_ips_in_range($first_ip_address, $this->_ip_broadcast_address);
			} elseif ($first_ip_address == null && $last_ip_address == null) {
				$range_of_ips = $ip_converter->get_all_ips_in_range($this->_ip_subnet_address, $this->_ip_broadcast_address);
			} elseif ($first_ip_address != null && $last_ip_address != null) {
				$range_of_ips = $ip_converter->get_all_ips_in_range($first_ip_address, $last_ip_address);
			}
				
			//get all the addresses that are already in the database
			$existing_ips = $this->get_ip_addresses();
			
			//remove all the ones that have already been created
			if ($range_of_ips != null) {
					
				foreach($range_of_ips as $ip) {
					
					$existing = 'no';
					
					if ($existing_ips != null) {
			
						foreach($existing_ips as $existing_ip){
			
							if ($existing_ip->get_ip_address() == $ip) {
								$return_ips[] =  $existing_ip;
								$existing = 'yes';
							}
						}
					}
					
					if ($existing == 'no') {
						$create_ips[] = $ip;
					}
				}
				
			} else {
				throw new exception("this subnet id: ".$this->_ip_subnet_id.", has no ips that can be created, that makes no sense", 8802);
			}
			
			if (isset($create_ips)) {
				
				//get all ips, so we can append the variable
				if ($this->_ip_addresses == null) {
					$this->get_ip_addresses();
				}
				
				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);
				
				foreach ($create_ips as $new_ip_address) {
					
					$data = array(
													'ip_address'		=>  $new_ip_address,
													'ip_subnet_id'		=>  $this->_ip_subnet_id,
					);
					
					$new_ip_address_id = Zend_Registry::get('database')->insert_single_row('ip_addresses',$data,$class,$method);
					$this->_ip_addresses[$new_ip_address_id] = new Thelist_Model_ipaddress($new_ip_address_id);
					
					//append the ip to the array of ips we return
					$return_ips[]	= $this->_ip_addresses[$new_ip_address_id];

				}
			}

		} else {
			throw new exception("this subnet id: ".$this->_ip_subnet_id.", has child subnets and cannot be turned into ip addresses because of that", 8805);
		}
		return $return_ips;
	}
	
	public function get_unused_host_ip_addresses($ip_count)
	{
		if (!is_numeric($ip_count)) {
			throw new exception("ip count requested must be numeric", 8809);
		}
		
		//there cannot be child subnets because that means this subnet is not for connected allocations
		if ($this->get_child_subnets(true) == null) {

			$available_host_ips = $this->get_available_host_ips(true);

			if ($available_host_ips != null) {
		
				foreach($available_host_ips as $ip_addresse) {
					
					$return_ips[] = $ip_addresse;
						
					if (count($return_ips) == $ip_count) {
						return $return_ips;
					}
				}
			}
		}
		//if there are no open ips or not enough ips return false
		return false;
	
	}
	
	public function get_child_subnets($refresh=true)
	{
		
		//if you need the array ordered acording to cidr mask then use array_tools sort_ip_subnets_by_cidr
		
		if ($this->_child_subnets == null || $refresh == true) {
		
			$this->_child_subnets = null;
			
			$sql=	"SELECT ip_subnet_id FROM ip_subnets
					WHERE ip_subnet_master_id='".$this->_ip_subnet_id."'
					";
		
			$child_subnets  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
	
			if (isset($child_subnets['0'])) {
				foreach($child_subnets as $child_subnet) {
					$this->_child_subnets[] = new Thelist_Model_ipsubnet($child_subnet['ip_subnet_id']);
				}
			}
		}
		
		return $this->_child_subnets;
	}
	
	public function get_child_subnets_recursively($ip_subnet_obj=false)
	{
		//the array that is returned, if you need to remove the ones that
		//have, i.e. inuse ips, do that outside this class
		
		//if you need the array ordered acording to cidr mask then use array_tools sort_ip_subnets_by_cidr
		
		if ($ip_subnet_obj == false) {
			//use our selfs the first time
			$ip_subnet_obj = $this;
		}
			$child_subnets  = $ip_subnet_obj->get_child_subnets();
	
			if ($child_subnets != null) {
				
				foreach($child_subnets as $child_subnet) {
					
					$this->get_child_subnets_recursively($child_subnet);

				}
				
			} 
			
			$this->_child_subnets_recursively[] = $ip_subnet_obj;

		return $this->_child_subnets_recursively;
	}
	
	public function set_child_subnets($smallest_subnet_cidr)
	{
		//this method can only be run once on each subnet
		//if a subnet has child subnets then these must be removed before the 
		//subnet can be carved up again
		//using the child subnets the subnet can be carved up further, but if i.e. we need a /26 from a /24
		//then that will result in the subnet being divided into 1x /25 and 2x /26 
		//we could then carve the /25 into 2 more /26, but we cannot just remove the /25 and create 2x /26. 
		//at least not with this method. this ensures that the entire subnet is always in the table and we dont have to
		//account for "potential subnets" all over the code, we can just fetch the subnets and see if they have children	
		
		if (!is_numeric($smallest_subnet_cidr)) {
			//make better validation
			throw new exception("input must be number between 0-32", 8806);
		} elseif($this->get_ip_subnet_cidr_mask() > $smallest_subnet_cidr) {
			//check that the smallest subnet requested is smaller or equal to this subnet
			throw new exception("this subnet id: ".$this->_ip_subnet_id.", has is a /".$this->get_ip_subnet_cidr_mask().", you want to divide that into /".$smallest_subnet_cidr.", sorry cant do that for you ", 8806);
		} 

		if ($this->_child_subnets == null) {
			$this->get_child_subnets();
		}
		if ($this->_ip_addresses == null) {
			$this->get_ip_addresses();
		}
		
		if ($this->_ip_addresses == null) {

			if ($this->_child_subnets == null) {

				//if the user is requesting the same size as this subnet, we give him this subnet
				if ($this->get_ip_subnet_cidr_mask() == $smallest_subnet_cidr) {
					return $this;
				} else {
				
					$ipconverter = new Thelist_Utility_ipconverter();
					
					//all ips in this subnet
					$main_subnet_ip_range = $ipconverter->get_all_ips_in_range($this->_ip_subnet_address, $this->_ip_broadcast_address);
		
					//now remove just as many ips as the user is requesting
					$remove_count	= pow(2, (32 - $smallest_subnet_cidr));
					
					$i=0;
					while ($i < $remove_count) {
						
						$removed_ips[$i] = $main_subnet_ip_range[$i];
						unset($main_subnet_ip_range[$i]);
						$i++;
					}
					
					//now we have 2 ranges with the new child subnet ips we need to turn them into a single range 
					//and create subnets from them. this also means that the smallest cidr comes out on top
					$new_subnets1 = $ipconverter->get_all_possible_subnets_from_ips($removed_ips);
					$new_subnets2 = $ipconverter->get_all_possible_subnets_from_ips($main_subnet_ip_range);
					
					$all_new_subnets = array_merge($new_subnets1, $new_subnets2);

					
					$trace 		= debug_backtrace();
					$method 	= $trace[0]["function"];
					$class		= get_class($this);
					
					foreach ($all_new_subnets as $new_subnet) {
						
						//some validations would be good make sure none of the ips are already connected or allocated to another child that overlaps
						$data = array(
							
							'ip_subnet_master_id'	=>  $this->_ip_subnet_id,
							'ip_subnet_address'		=>  $new_subnet['subnet_address'],
							'ip_subnet_cidr_mask'	=>  $new_subnet['subnet_cidr'],
						
						);
						
						$new_ip_subnet_id = Zend_Registry::get('database')->insert_single_row('ip_subnets',$data,$class,$method);
							
						$new_subnet	= new Thelist_Model_ipsubnet($new_ip_subnet_id);
						$created_subnets[]	= $new_subnet;
						$this->_child_subnets[]	= $new_subnet;
					}
					
					return $created_subnets;
				}
				
			} else {
				throw new exception("this subnet id: ".$this->_ip_subnet_id.", has child subnets and cannot be carved up further because of that", 8807);
			}
			
		} else {
			throw new exception("this subnet id: ".$this->_ip_subnet_id.", has ip addresses defined cannot be carved up further because of that", 8808);
		}
	}
}
?>