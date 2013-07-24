<?php

//exception codes 9300-9399

class thelist_routeros_config_iproute implements Thelist_Commander_pattern_interface_ideviceconfiguration
{
	private $_equipment;
	private $_ip_route;
	
	public function __construct($equipment, $ip_route)
	{
		$this->_equipment 						= $equipment;
		$this->_ip_route						= $ip_route;
	}	

	public function generate_config_array()
	{
		//we are not interested in default routes that are being picked up via dhcp lease interfaces
		//we only want the routes that are static we do this by getting the interface the route 
		//points out of and get the ip address of the gateway for the route
		//then we look on the interface and find our own ip address on the same subnet
		//then we make sure that ip address is not mapped to the interface as a dhcp lease.
		//tricky is it not?

		$interfaces		= $this->_equipment->get_ip_route_interfaces($this->_ip_route);
		$gateways		= $this->_ip_route->get_ip_route_gateways();

		foreach ($interfaces as $interface) {
			
			$interface_ips	= $interface->get_ip_addresses();
		
			if ($interface_ips != null) {
				
				foreach($interface_ips as $interface_ip) {
					
					foreach($gateways as $gateway) {
	
						if ($interface_ip->get_ip_subnet_id() == $gateway['ip_address']->get_ip_subnet_id()) {
								
							if ($interface_ip->get_ip_address_map_type() == 88) {
								//we only want one gateway per subnet on the equipment interface
								$gateway_maps[$interface_ip->get_ip_subnet_id()] 		= $gateway;
							} elseif ($interface_ip->get_ip_address_map_type() == 91) {
								//leases
								$lease_present = 'yes';
							} else {
								
								echo "\n <pre> 1111  \n ";
								print_r($interface_ip);
								echo "\n 2222 \n ";
								print_r($this->_equipment->get_eq_id());
								echo "\n 3333 \n ";
								//print_r();
								echo "\n 4444 </pre> \n ";
								die;
								
								throw new exception("the gateway for the route is mapped using type: ".$interface_ip->get_ip_address_map_type_resolved().", only connected and dhcp lease interfaces should have routes", 9301);
							}
						}
					}
				}
			}
		}

		
		if (isset($gateway_maps)) {
			
			$return['configuration']['subnet_address']		= $this->_ip_route->get_ip_subnet()->get_ip_subnet_address();
			$return['configuration']['subnet_mask']			= $this->_ip_route->get_ip_subnet()->get_ip_subnet_cidr_mask();
			
			$i=0;
			foreach($gateway_maps as $gateway_map) {
				
				//we only do the connected ips as routes, because dhcp will provide the route to the equipment on a dynamic basis
				if ($gateway_map['ip_address']->get_ip_address_map_type() == 88) {
					$return['configuration']['gateway_addresses'][$i]['ipaddress']		= $gateway_map['ip_address']->get_ip_address();
					$return['configuration']['gateway_addresses'][$i]['cost']			= $gateway_map['cost'];
				}	
				$i++;
			}

		} elseif (isset($lease_present)) {
			//if there is a lease then we do nothing
			
		} else {
			throw new exception('this route does not have a gateway that is reachable from this equipment, there may be a logic error when adding routes somewhere', 9302);
		}

		if (isset($return)) {
			return $return;
		} else {
			return false;
		}
	}
	
	public function generate_config_device_syntax($config_array)
	{
		//set the variable for the return
		$return_conf = "/ip route";

		if (isset($config_array['configuration']['subnet_address'])) {
			
			$return_conf .= "\nadd dst-address=\"".$config_array['configuration']['subnet_address']."";
			$return_conf .= "/".$config_array['configuration']['subnet_mask']."\"";
			$return_conf .= " gateway=\"";
			
			$i=0;
			foreach ($config_array['configuration']['gateway_addresses'] as $gateway) {
				
				if ($i == 0) {
					$return_conf .= $gateway['ipaddress'];
				} else {
					$return_conf .= "," . $gateway['ipaddress'];
				}
				$i++;
			}
			$return_conf .= "\"";

			//add cost, only one cost, at some point this method has to return multible route entires if the cost per route differs
			$return_conf .= " distance=\"".$gateway['cost']."\"";
			
			
		}

		return $return_conf;
	}
}