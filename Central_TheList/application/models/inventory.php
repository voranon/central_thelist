<?php

//by martin
//exception codes 200-299
//exception codes 80-99

class thelist_model_inventory
{
		
	private $database;
	private $logs;
	private $_time;
	private $_eq_manufacturer;
	private $_eq_fqdn;
	private $_eq_api_username;
	private $_eq_api_password;
	private $_custom_var;
	private $_eq_unit_id;
	private $_master_eq_id;
	private $_if_eq_id;
	private $user_session;
	private $_cache_14400;
	
	//below only required when adding a live device to inventory
	private $_device=null;
	private $_device_detail=null;
	
	public function __construct()
	{

		$this->logs					= Zend_Registry::get('logs');
		
		$this->_time				= Zend_Registry::get('time');
		
		$this->_cache_14400 		= Zend_Registry::get('filecache14400');
		
		$this->user_session 		= new Zend_Session_Namespace('userinfo');
		
	}

	public function missing_equipment_for_install($task_id, $equipment_type_groups)
	{
		//something call trouble shooter class.
		
	}
	
	public function is_directv_receiver($eq_type_obj)
	{
		$sql2 = "SELECT count(eq_type_group_map_id) FROM eq_type_group_mapping
				WHERE eq_type_group_id IN (1,2,3,4)
				AND eq_type_id='".$eq_type_obj->get_eq_type_id()."'
				";
			
		$count_is_dtv_receiver = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
			
		if ($count_is_dtv_receiver != 0) {
			return true;
		} else {
			return false;
		}
	}
	
	public function create_equipment_from_type($eq_type_obj, $serial_number)
	{

		if ($serial_number != 'no_serial') {
			
			$sql = 	"SELECT eq_id FROM equipments
					WHERE (eq_serial_number='".$serial_number."' OR eq_second_serial_number='".$serial_number."')
					";
				
			$exist = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
			
			//make sure this serial number does not already exist in the database
			if (isset($exist['eq_id'])) {
				
				//serial is in the database, return that equipment
				return new Thelist_Model_equipments($exist);
			} else {
				
				//equipment not in database
				//make sure this serial number conforms to the serial number standard for this type of equipment if it does not an exception is thrown by eq_type
				$eq_type_obj->validate_serial_number_format($serial_number);
			}

		} else {
			
			//if this is a non tracked equipment (no_serial) lets make sure it really is not tracked.
			if ($eq_type_obj->get_eq_type_serialized()) {
				
				throw new exception('you have asked to create equipment with no_serial, but we require this equipment type to be serialized ', 215);
				
			}
		}
		
		//if we get past all the validations lets insert the equipment.
		$data = array(
		
			'eq_type_id'   			=>  $eq_type_obj->get_eq_type_id(),
			'eq_serial_number' 		=>  $serial_number,
						
		);
		
		$trace 		= debug_backtrace();
		$method 	= $trace[0]["function"];
		$class		= get_class($this);
		
		$new_eq_id 	= Zend_Registry::get('database')->insert_single_row('equipments',$data,$class,$method);
		
		return new Thelist_Model_equipments($new_eq_id);
	}
	
	public function is_serial_in_database($serial_number)
	{
		
		if ($serial_number != 'no_serial') {
			
			$sql = 	"SELECT COUNT(eq_id) FROM equipments
					WHERE (eq_serial_number='".$serial_number."' OR eq_second_serial_number='".$serial_number."')
					";
			
			$exist = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
				
			if ($exist == '0') {	
				return false;
			} elseif ($exist == '1') {	
				return true;
			} else {	
				throw new exception('this equipment serial number is in the database more than once, how did that happen', 210);
			}
			
		} else {
			throw new exception('having fun are we? please explain to the programmers why you are looking for equipment that has serial: no_serial');
		}
	}
	
	public function is_fqdn_in_database($fqdn)
	{
		$sql = 	"SELECT COUNT(eq_id) FROM equipments
				WHERE eq_fqdn='".$fqdn."'
				";
	
		$exist = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
	
		if ($exist == '0') {
			
			return false;
			
		} elseif ($exist == '1') {
			
			return true;
			
		} else {
			
			throw new exception('this equipment fqdn number is in the database more than once, that is a problem', 211);
		}
	}
	
	
	
	
	public function validate_connect_post_create_equipment($post_array)
	{

		//this method will validate that all rules for the posted information are fulfilled
		
		//this stop in the install process will not be required once we convert to an application.
		//this check is here because we are posting information. in the app this logic will reside on the tablet device.
		
		//we want to make sure all unknown devices have been used, we cannot have extra receivers or routers that are visable.
		//butr not mapped to the unit, they will still be taking up resources
		
		//store the service_plan_map_ids that are matched by the tech to an unknown device in this variable
		$requirements_met = ',';
		
		//track that an unknow device is only used once to fill a requirement
		$used_serial_or_access_card = ',';

		if (isset($post_array['unknown_device'])) {
			
			foreach($post_array['unknown_device'] as $key => $unknown_device) {
				
				$used = 'no';
				
				//for all unidentified devices
				if ($key == 'others') {
				
					foreach($unknown_device as $unknown_other) {
						
						if (isset($post_array['unfulfilled_use_other_device'])) {
						
							foreach($post_array['unfulfilled_use_other_device'] as $use_other_device_key => $use_other_device) {
									
								$exploded_array =	explode('||', $use_other_device);
									
								if ($exploded_array['0'] == 'unknown_device') {
									
									//customer phones are not tied to another piece of equipment
									if ($post_array['unfulfilled_model'][$use_other_device_key] == 57) {
									
										//is the model selected for this requirement is phone, and we detected it, no good
										throw new exception('equipment was marked as phone and attached to detected equipment, ', 207);

									}
						
									if ($unknown_other['mac_address'] == $exploded_array['1'] && $unknown_other['ip_address'] == $exploded_array['2']) {
											
										$used = 'yes';
									
										if (preg_match( "/,".$exploded_array['1'].",/", $used_serial_or_access_card)) {
											
											throw new exception('we are filling 2 requirements with the same unknown equipment', 89);
											
										} else {
											
											$requirements_met				.= $use_other_device_key . ",";
											$used_serial_or_access_card		.= $exploded_array['1'] . ",";

										}		
									}
								} 
							}
						}
					}
				}
				
				//for all unknown receivers
				if ($key == 'receivers') {
					
					foreach($unknown_device as $unknown_receiver) {
						
						if (isset($post_array['unfulfilled_use_other_device'])) {
						
							foreach($post_array['unfulfilled_use_other_device'] as $use_other_device_key => $use_other_device) {
								
								//we require a serial number entered by the tech for all unknown receivers
								if ($post_array['unfulfilled_serial'][$use_other_device_key] == '') {
									
									throw new exception('we were not provided a serial number for an unknown receiver', 80);
									
								}
									
								$exploded_array =	explode('||', $use_other_device);
									
								if ($exploded_array['0'] == 'unknown_receiver') {
									
									//is the model selected for this requirement really a receiver model
									//check if the selected model fulfills the serviceplaneqmap requirement
									$sql44 = 	"SELECT COUNT(etgm.eq_type_group_id) FROM eq_type_group_mapping etgm
												WHERE etgm.eq_type_id='".$post_array['unfulfilled_model'][$use_other_device_key]."'
												AND etgm.eq_type_group_id IN (1,2,3,4)
												";
										
									$count_valid_receiver_model = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql44);
									
									if ($count_valid_receiver_model == '0') {
										
										throw new exception('we found an unknown receiver where the selected the model is not a receiver type', 206);
										
									}
						
									if ($unknown_receiver['access_card'] == $exploded_array['1'] && $unknown_receiver['receiver_id'] == $exploded_array['2']) {
											
										$used = 'yes';
										
										if (preg_match( "/,".$exploded_array['1'].",/", $used_serial_or_access_card)) {
											
											throw new exception('we are filling 2 requirements with the same unknown equipment', 89);

										} else {
											
											$requirements_met				.= $use_other_device_key . ",";
											$used_serial_or_access_card		.= $exploded_array['1'] . "," . $post_array['unfulfilled_serial'][$use_other_device_key] . ",";

										}	
									}
								} 
							}
						}
					}
				}
				
				//if this device was not used we cannot accept the post, we have equipment that is requesting IPs
				//directly from us and it is not on the sales quote
				if ($used == 'no') {
					
					throw new exception('there is an unknown device that was not attached to a requirement, we cannot have free floating equipment', 85);
					
				}
			}
		}
		
		//we want to make sure that any unfulfilled equipment has been handled by either using an
		//unknown device or if there are none, by adding a serial and maybe receiver id + accesscard if the device was a receiver
		if (isset($post_array['unfulfilled_serial'])) {
			
			foreach($post_array['unfulfilled_serial'] as $service_plan_map_id => $unfulfilled_serial_number) {
				
				if (!preg_match( "/,".$service_plan_map_id.",/", $requirements_met) && $unfulfilled_serial_number == '') {

					throw new exception('not all requirements where matched with an unknown device or receiver', 87);
					
				}
				
				//if receiver or access card and and the other device is filled we cannot accept the result
				//it must either be a serial and maybe receiver and access card or other device but not both 
				
				if (($post_array['unfulfilled_receiver_id'][$service_plan_map_id] != '' || $post_array['unfulfilled_access_card'][$service_plan_map_id] != '') && $post_array['unfulfilled_use_other_device'][$service_plan_map_id] != '') {
				
					throw new exception('we received both rid or access card and a device, can only be one or the other', 82);
						
				}
			}
		}
		
		//check if the any of the models provided are receivers, if they are we need serial, receiver_id and access card.
		//also make sure that 
		if (isset($post_array['unfulfilled_model'])) {
			
			foreach ($post_array['unfulfilled_model'] as $service_plan_map_id => $equipment_type_id) {
				
				//check if all unknown equipments have a model selected by the user
				if ($equipment_type_id == '') {
						
					throw new exception('we did not get a eq_type_id for one of the unknown equipments', 86);
					
				} else {
				
					
					//check if the selected model fulfills the serviceplaneqmap requirement
					$sql = 	"SELECT COUNT(spqetm.service_plan_quote_eq_type_map_id) FROM service_plan_quote_eq_type_mapping spqetm
							INNER JOIN service_plan_eq_type_mapping spetm ON spetm.service_plan_eq_type_map_id=spqetm.service_plan_eq_type_map_id
							INNER JOIN eq_type_group_mapping etgm ON etgm.eq_type_group_id=spetm.eq_type_group_id
							WHERE etgm.eq_type_id='".$equipment_type_id."'
							AND spqetm.service_plan_quote_eq_type_map_id='".$service_plan_map_id."'
							";
						
					$count_valid_model = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
					
					if ($count_valid_model == '0') {
						
						throw new exception('the selected equipment type does not support the requirement', 83);
						
					}
					
					$eq_type	= new Thelist_Model_equipmenttype($equipment_type_id);
					
					$is_receiver	= $this->is_directv_receiver($eq_type);
					
					if ($is_receiver == true) {
						
						if (($post_array['unfulfilled_serial'][$service_plan_map_id] == '' || $post_array['unfulfilled_receiver_id'][$service_plan_map_id] == '' || $post_array['unfulfilled_access_card'][$service_plan_map_id] == '') && $post_array['unfulfilled_use_other_device'][$service_plan_map_id] == '') {
							
							throw new exception('this is reported as a receiver and we did not tie it to an unknown equipment nor did we provide serial, receiver_id and accesscard', 88);
							
						} elseif ($post_array['unfulfilled_access_card'][$service_plan_map_id] != '') {
							
							//make sure this provided access card is not already in use by one of the detected receivers
							if (!preg_match( "/,".$post_array['unfulfilled_access_card'][$service_plan_map_id].",/", $used_serial_or_access_card)) {
									
								throw new exception('we are filling 2 requirements with the same unknown equipment', 89);
							
							}
							//we cannot pull the serial number from receivers remotely, it must be provided by the tech
						} elseif ($post_array['unfulfilled_serial'][$service_plan_map_id] == '' && $post_array['unfulfilled_use_other_device'][$service_plan_map_id] != '') {
							
							throw new exception('we did not get a serial number for one of the requirements you tied to an unknown receiver', 84);
							
						}
					
					
						
					} else {
						
						//if this is not a receiver we need a serial number or tied to another device and this is not a customer phone, which is not something we need a serial number for
						if ($post_array['unfulfilled_serial'][$service_plan_map_id] == '' && $post_array['unfulfilled_use_other_device'][$service_plan_map_id] == '' && $post_array['unfulfilled_model'][$service_plan_map_id] != '57') {
						
							throw new exception('this is not reported as a receiver and we did not tie it to an unknown equipment nor did we provide serial', 81);
							
						} else {
							
							//make sure this provided serial number is not already in use by one of the detected devices
							if (preg_match( "/,".$post_array['unfulfilled_serial'][$service_plan_map_id].",/", $used_serial_or_access_card) && !preg_match( "/,".$service_plan_map_id.",/", $requirements_met)) {

								throw new exception('we are filling 2 requirements with the same unknown equipment', 89);
							
							}
						}
					}
				}
			}
		}
	}
	
	
	public function verify_task_service_point_install($task_obj, $interface_obj, $use_cache=false, $updated_cache=null)
	{
		//this method only deals with new service point interfaces that are just now getting connected to the system
		//there may be equipment that has already been verified attached, because this port is replacing another
		//We need to find all the equipment and the serials for that equipement.
		
		//the expectation is that all equipment under the task should be connected to this new port
		//in adddition if we see other existing equipments we make sure they are also connected to this interface.
		
		//use cashing if the user chooses
		$cache_id = "verify_task_service_point_install_" . $task_obj->get_task_id() ."_". $interface_obj->get_if_id();
		
		if ($use_cache === 'update') {

			//we are using the cache in methods that add interfaces or roles etc. to the equipment objects
			//if those methods throw exceptions and then restart once the user has fixed the problem
			//we end up with equipment objects that are not up to date.
			//the database will get updated by those methods but when when the method restarts
			//the equipment object is not reinstanciated so the private variables will hold no knowledge
			//of the additions.
			//Here we can update the cache before throwing the exception so when we get the cache again
			//it reflects the changes.

			$this->_cache_14400->save($updated_cache, $cache_id);
			return true;
			
		} 

		if ($use_cache == true) {

			if (($cashed_value = $this->_cache_14400->load($cache_id)) != false) {

				return $cashed_value;
			
			}
		} 

		//find all new equipment types that will require serial checks both existing equipment and new
		$sql=	"SELECT spqetm.service_plan_quote_eq_type_map_id, spetm.eq_type_group_id, 'null' AS eq_id, et.eq_type_id, et.eq_manufacturer FROM service_plan_quote_task_mapping spqtm
				INNER JOIN service_plan_quote_eq_type_mapping spqetm ON spqetm.service_plan_quote_map_id=spqtm.service_plan_quote_map_id
				INNER JOIN service_plan_eq_type_mapping spetm ON spetm.service_plan_eq_type_map_id=spqetm.service_plan_eq_type_map_id
				INNER JOIN equipment_type_groups etg ON etg.eq_type_group_id=spetm.eq_type_group_id
				INNER JOIN eq_type_group_mapping etgm ON etgm.eq_type_group_id=etg.eq_type_group_id
				INNER JOIN equipment_types et ON et.eq_type_id=etgm.eq_type_id
				WHERE spqtm.task_id='".$task_obj->get_task_id()."'
				AND et.eq_type_serialized='1'
				GROUP BY spqetm.service_plan_quote_eq_type_map_id
				UNION ALL
				SELECT sqetmem.service_plan_quote_eq_type_map_id, spetm.eq_type_group_id, e.eq_id AS eq_id, et.eq_type_id, et.eq_manufacturer FROM service_plan_quote_task_mapping spqtm
				INNER JOIN service_plan_quote_mapping spqm ON spqm.service_plan_quote_map_id=spqtm.service_plan_quote_map_id
				INNER JOIN sales_quotes sq ON sq.sales_quote_id=spqm.sales_quote_id
				INNER JOIN end_user_services eu ON eu.end_user_service_id=sq.end_user_service_id
				INNER JOIN equipment_mapping em ON em.unit_id=eu.unit_id
				INNER JOIN sales_quote_eq_type_map_equipment_mapping sqetmem ON sqetmem.equipment_map_id=em.equipment_map_id
				INNER JOIN service_plan_quote_eq_type_mapping spqetm ON spqetm.service_plan_quote_eq_type_map_id=sqetmem.service_plan_quote_eq_type_map_id
				INNER JOIN service_plan_eq_type_mapping spetm ON spetm.service_plan_eq_type_map_id=spqetm.service_plan_eq_type_map_id
				INNER JOIN equipments e ON e.eq_id=em.eq_id
				INNER JOIN equipment_types et ON et.eq_type_id=e.eq_type_id
				WHERE spqtm.task_id='".$task_obj->get_task_id()."'
				AND (em.eq_map_deactivated IS NULL OR em.eq_map_deactivated > NOW())
				AND et.eq_type_serialized='1'
				";
			
		$serialized_equipments  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);

		if (isset($serialized_equipments['0'])) {
			
			
			//we need to find all the active arp entries on the provisioning subnet
			//maybe in the future this needs to be modified to setup a temp dhcp server
			//and put the port in a temp provisioning vlan so we can find additions if. ie the customer 
			//is adding dtv on top of the internet connection or the otherway around
			$resource_locator			= new Thelist_Model_servicepointresourcelocator();
			
			//must do routers first because we dont want to reset the paths var in pathfinder, in order to get to the routers
			//we must go through the edge switches, so we can reuse the paths.
			$border_routers				= $resource_locator->get_border_routers($interface_obj);
			$edge_switches				= $resource_locator->get_edge_switches($interface_obj);
			
			//combine all boarder router arp tables
			if ($border_routers != false && $edge_switches != false) {
			
				$arp_table = array();
				foreach($border_routers as $border_index => $border_router){
					
					foreach($edge_switches as $edge_index => $edge_switch){

						//use any credential for each edge switch
						$e_credential = current($edge_switch['equipment']->get_apis());
						$e_device = new Thelist_Model_device($edge_switch['equipment']->get_eq_fqdn(), $e_credential);
						
						//add the edge switch device to the array for later use
						$edge_switches[$edge_index]['device'] = $e_device;

						//we need to make sure that all the edge switch ports are up, there is no point in trying to access information about devices
						//on the other side of a down port
						$interface_op_status	= $edge_switches[$edge_index]['device']->get_interface_operational_status($edge_switch['inbound_interface']);
						
						
						//if the interface is shutdown we bring it back up
						if ($interface_op_status != 1) {
							$interface_admin_status	= $edge_switches[$edge_index]['device']->get_interface_administrative_status($edge_switch['inbound_interface']);
							if ($interface_admin_status == true) {
								throw new exception("we are trying to get a list of devices on interface: '".$edge_switch['inbound_interface']->get_if_name()."' from device: '".$edge_switches[$edge_index]['device']->get_fqdn()."' but the port is not connected to anything and we checked that the interface is turned on", 223);
							} else {
								//enable the down interface
								$edge_switches[$edge_index]['device']->set_interface_administrative_status($edge_switch['inbound_interface'], 1);
							}
						}

						//get the native vlans for each of the switch ports
						//we need them when we want to look for the live equipment
						$native_vlan = $edge_switch['inbound_interface']->get_interface_configuration(25);

						if ($native_vlan != false) {
							
							//if the vlan on the interface is 30 that means the interface is not allowed to talk to anyone and
							//it means that we cannot scan the available equipment.
							//in this case we have to issue a temp vlan config to the router and the interface
							//on vlan 20 the provisioning vlan that all routers have and scan from 
							if ($native_vlan['0']->get_mapped_configuration_value_1() == 30) {
								
								$edge_switches[$edge_index]['device']->set_default_provisioning_on_switchport_interface($edge_switch['inbound_interface']);
								//append it to the scan list
								$native_vlans_on_edge_switches[] = 20;
								
							} else {
								
								//append it to the scan list
								$native_vlans_on_edge_switches[] = $native_vlan['0']->get_mapped_configuration_value_1();
							}

						} else {
							
							//no native vlan at all we then set it.
							$edge_switches[$edge_index]['device']->set_default_provisioning_on_switchport_interface($edge_switch['inbound_interface']);
							//append it to the scan list
							$native_vlans_on_edge_switches[] = 20;
						}

						//clear the mac addresses from the cam before sweeping this avoids the system seeing mac addresses
						//that may have been seen during the technician installation process.
						$edge_switches[$edge_index]['device']->remove_interface_cam_entries($edge_switch['inbound_interface']);
					
					}

					//get the active arp entries from the provisioning subnet on vlan 20
					//use any credential for each border router
					$r_credential 	= current($border_router['equipment']->get_apis());
					$r_device 		= new Thelist_Model_device($border_router['equipment']->get_eq_fqdn(), $r_credential);
					
					//add the edge switch device to the array for later use
					$border_routers[$border_index]['device'] = $r_device;
					
					if (isset($native_vlans_on_edge_switches)) {
						
						foreach ($native_vlans_on_edge_switches as $native_vlan_id) {
							
							if (!isset($prov_vlan_ids)) {
								$prov_vlan_ids = $native_vlan_id;
							} else {
								$prov_vlan_ids .= "," . $native_vlan_id;
							}
						}
					}

					//now find all provisioning subnet(s)
					$sql22 = 	"SELECT i.if_id, ipsub.ip_subnet_address, ipsub.ip_subnet_cidr_mask FROM ip_address_mapping ipam
								INNER JOIN ip_addresses ipa ON ipa.ip_address_id=ipam.ip_address_id
								INNER JOIN ip_subnets ipsub ON ipsub.ip_subnet_id=ipa.ip_subnet_id
								INNER JOIN interfaces i ON i.if_id=ipam.if_id
								INNER JOIN interface_configuration_mapping icm ON icm.if_id=ipam.if_id
								WHERE i.eq_id='".$border_router['equipment']->get_eq_id()."'
								AND icm.if_conf_id='22'
								AND icm.if_conf_value_1 IN (".$prov_vlan_ids.")
								GROUP BY ipsub.ip_subnet_address
								";
					
					$provisioning_subnets  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql22);

					if (isset($provisioning_subnets['0'])) {
						
						//here we first wait for all interfaces on the edge switches to come out of learning spanning tree
						//otherwise we will not able to ping any of the hosts behind the switch port
						$waited_for_spanning_tree = 'no';
						foreach ($edge_switches as $edge_switch) {

							$interface_op_status	= $edge_switch['device']->get_interface_operational_status($edge_switch['inbound_interface']);
							//we know the interface admin status is up, because if it was not at the to then it is now
							if ($interface_op_status != 1) {
								throw new exception("we are trying to get a list of devices on interface: '".$edge_switch['inbound_interface']->get_if_name()."' from device: '".$edge_switches[$edge_index]['device']->get_fqdn()."' but the port is not connected to anything and we checked that the interface is turned on", 224);
							}
							
							$forwarding = 0;
							$i=0;
							while ($i < 30 && $forwarding == 0) {
								
								$spanning_tree_status	= $edge_switch['device']->get_interface_spanning_tree_status($edge_switch['inbound_interface']);
								
								if ($spanning_tree_status['native_vlan_status'] != 'forwarding') {
									
									$waited_for_spanning_tree = 'yes';
									//it can take awhile for spanning tree to start forwarding, but because all the edge switches were configured at the same time
									//they will all be ready at the same time.
									sleep(2);
								} else {
									$forwarding = 1;
								}
								
								$i++;
							}
							
							if ($forwarding != 1) {
								//max execution time is 30 x 2 sec that will be longer than the script is allowed to run
								throw new exception("the edge switch port: ".$edge_switch['inbound_interface']->get_if_name()." on device: ".$edge_switch['device']->get_fqdn()." never came out of spanning tree, we cannot continue", 222);
							}
						}

						if ($waited_for_spanning_tree == 'yes') {
							//if we had to wait for spanning tree we should also give the dhcp clients a few seconds to ask for addresses
							//this should be refined based on the equipment we are expecting to see
							sleep(4);
						}

						foreach ($provisioning_subnets as $provisioning_subnet) {
							
							$dhcp_server_status	= $border_routers[$border_index]['device']->get_dhcp_server_status(new Thelist_Model_equipmentinterface($provisioning_subnet['if_id']));
							
							if ($dhcp_server_status != 1) {
								throw new exception('border router provisioning dhcp server is not running', 221);
							}
							
							$arp_entries = $border_routers[$border_index]['device']->get_subnet_arp_entries($provisioning_subnet['ip_subnet_address'], $provisioning_subnet['ip_subnet_cidr_mask']);
							
							if ($arp_entries != null) {
								
								if (!isset($all_arp_entries)) {
									$all_arp_entries = $arp_entries;
								} else {
									$all_arp_entries = array_merge($all_arp_entries, $arp_entries);
								}
							}
						}
						
					} else {
						throw new exception('border router does not have subnet faceing the edge interface', 220);
					}
				}

				//are there are any arp entries that fulfill the requirement of being on the provisioning subnet 
				if(isset($all_arp_entries)) {
					
					//now get cam tables for each of the edge switches
					foreach ($edge_switches as $single_edge_switch) {
						
						$get_cam_entries	= $single_edge_switch['device']->get_cam_table();
								
						if ($get_cam_entries != null) {
							
							foreach ($get_cam_entries as $single_cam) {
									
								//if this cam entry matches the interface name for the edge switch then it is a contender
								if ($single_cam->get_interface_name() == $single_edge_switch['inbound_interface']->get_if_name()) {
							
									//test if it matches a live arp entry on the router
									foreach($all_arp_entries as $single_arp) {
										
										if ($single_arp->get_macaddress() == $single_cam->get_macaddress()) {
											$active_arp_entries[]	= $single_arp;
										}
									}
								}
							}
						}
					}
				}
				
			} else {
				
				if ($border_routers == false) {
					throw new exception('we could not find any border routers', 218);
				} else {
					throw new exception('we could not find any edge switches', 219);
				}
			}

			if (isset($active_arp_entries)) {

				//tracking if the equipment has already been used
				$used_equipment_tracking = ',';
			
				//get the equipment from the arp entries
				foreach ($active_arp_entries as $arp_entry) {
					
					try {
						
						//first get the equipment
						$get_equipment	= $this->get_equipment_from_arp($arp_entry);
						
						//since this equipment could be in the database with an old management ip we reset it here.
						$get_equipment->set_eq_fqdn($arp_entry->get_ipaddress());

						//logout of the device, if the connection is cached it creates problems
						$get_equipment->read_from_device(array('deviceinformation' => 'logout'));
						
						//get the groups this equipment belongs to
						$eq_type_groups	=	Zend_Registry::get('database')->get_eq_type_group_mapping()->fetchAll('eq_type_id='.$get_equipment->get_eq_type_id());
						
						//first test if the equipment is already mapped to this unit
						$i=0;
						foreach($serialized_equipments as $required_equipment) {
										
							if (!preg_match("/,".$get_equipment->get_eq_id().",/", $used_equipment_tracking) && $required_equipment['eq_id'] == $get_equipment->get_eq_id()) {
								
								//if this is an old piece of equipment that is already been installed
								$serialized_equipments[$i]['status'] = '1';
								$used_equipment_tracking .= $get_equipment->get_eq_id().",";
								
								$equipment['install_previous_task'][]	= array('equipment_obj' => $get_equipment, 'service_plan_quote_eq_type_map_id' => $serialized_equipments[$i]['service_plan_quote_eq_type_map_id']);
								
							}
							$i++;
						}
						
						//if this was not an existing equipment, test against all groups of this equipment and see if this equipment can fulfill a requirement
						if (!preg_match("/,".$get_equipment->get_eq_id().",/", $used_equipment_tracking)) {

							$j=0;
							foreach($serialized_equipments as $required_equipment) {
								
								foreach ($eq_type_groups as $eq_type_group) {
									
									if (!preg_match("/,".$get_equipment->get_eq_id().",/", $used_equipment_tracking) && $serialized_equipments[$j]['eq_type_group_id'] == $eq_type_group['eq_type_group_id'] && !isset($serialized_equipments[$j]['status'])) {
										
										$serialized_equipments[$j]['status'] = '1';
										$used_equipment_tracking .= $get_equipment->get_eq_id().",";
										
										$equipment['install_this_task'][] = array('equipment_obj' => $get_equipment, 'service_plan_quote_eq_type_map_id' => $serialized_equipments[$j]['service_plan_quote_eq_type_map_id']);
										
									}
								}
								
								$j++;
							}
						}

			
						//we we dont find a match put it here
						if (!preg_match("/,".$get_equipment->get_eq_id().",/", $used_equipment_tracking)) {
							
							$equipment['other_equipment'][] = array('equipment_obj' => $get_equipment);
							$used_equipment_tracking .= $get_equipment->get_eq_id().",";
						}
					
					} catch (Exception $e) {
							
						switch($e->getCode()) {
					
							case 64;
							//64, cant create equipment, need equipment type for the receiver from tech
							$credential	= new Thelist_Model_deviceauthenticationcredential();
							$credential->fill_default_values('2');
							
							$device				= new Thelist_Model_device($arp_entry->get_ipaddress(), $credential);
							
							$get_serial1		= new Thelist_Directvstb_command_getserialnumber($device, 'receiver');
							$receiver_serial	= $get_serial1->execute();
							
							$get_serial2		= new Thelist_Directvstb_command_getserialnumber($device, 'accesscard');
							$accesscard_serial	= $get_serial2->execute();
							
							$equipment['unknown_receivers'][]	= array('receiver_id' => "$receiver_serial", 'access_card' => "$accesscard_serial", 'ip_address' => $arp_entry->get_ipaddress());
							break;
							case 34;
							$equipment['unknown_devices'][]		= array('ip_address' => $arp_entry->get_ipaddress(), 'mac_address' => $arp_entry->get_macaddress());
							break;
							case 1101;
							//1101, a receiver is refusing the connection
							$equipment['trouble_devices'][]		= array('ip_address' => $arp_entry->get_ipaddress(), 'mac_address' => $arp_entry->get_macaddress(), 'trouble' => 'Receiver refusing Connection', 'exception_id' => '1101');
							break;
							case 225;
							//225, a routeros device is refusing the connection with standard access credentials
							$equipment['trouble_devices'][]		= array('ip_address' => $arp_entry->get_ipaddress(), 'mac_address' => $arp_entry->get_macaddress(), 'trouble' => 'Routeros device refusing Connection', 'exception_id' => '225');
							break;
							default;
							throw $e;
					
						}
					}
				}
			}
			
			//add to the array all the serialized equipments we missed
			foreach($serialized_equipments as $requirement) {
					
				//dont add generic 9way splitter and any items that have found a match
				//also leave out any items from old sales quotes that do not show up.
				
				if (!isset($requirement['status']) && $requirement['eq_type_group_id'] != '9' && $requirement['eq_id'] == 'null') {
			
					$equipment['missing_requirements'][]	= new Thelist_Model_serviceplanquoteeqtypemap($requirement['service_plan_quote_eq_type_map_id']);
			
				}
			}

			$this->_cache_14400->save($equipment, $cache_id);
			return $equipment;
			
		} else {
			
			$this->_cache_14400->save(false, $cache_id);
			return false;

		}
	}
	
	
	public function create_equipment_from_unknown_devices($validation_array)
	{

		//all customer routers require role 4 (CPE router)
		$cust_router_role_obj	= new Thelist_Model_equipmentrole('4');
			
		//all receivers require role 5 (CPE receiver)
		$rec_role_obj	= new Thelist_Model_equipmentrole('5');
			
		//all customer phones require role 6 (CPE phone)
		$cust_phone_role_obj	= new Thelist_Model_equipmentrole('6');
			
		
		//if validation is successful we create any unknown equipment in the database.
		foreach($validation_array['unfulfilled_model'] as $service_plan_quote_eq_type_map_id => $eq_type_id) {
		
			$eq_type_obj 				= new Thelist_Model_equipmenttype($eq_type_id);
			$accesscard_eq_type_obj 	= new Thelist_Model_equipmenttype('66');
		
			//see if this is a receiver first
		
			$is_receiver = $this->is_directv_receiver($eq_type_obj);
		
			if ($is_receiver == true) {
								
				if ($validation_array['unfulfilled_use_other_device'][$service_plan_quote_eq_type_map_id] != '') {
		
					//if the equipment is going to be created from the device information
					$exploded_array =	explode('||', $validation_array['unfulfilled_use_other_device'][$service_plan_quote_eq_type_map_id]);
		
					$rec_serial 			= $validation_array['unfulfilled_serial'][$service_plan_quote_eq_type_map_id];
					$rec_second_serial 		= $exploded_array['2'];
					$rec_eq_type_id			= $eq_type_id;
					$ac_eq_type_id			= '66';
					$ac_serial				= $exploded_array['1'];
					$rec_ip_address			= $exploded_array['3'];
						
				} else {
		
					//if this is equipment that is not dicovered as device but a static entry.
					$rec_serial 			= $validation_array['unfulfilled_serial'][$service_plan_quote_eq_type_map_id];
					$rec_second_serial 		= $validation_array['unfulfilled_receiver_id'][$service_plan_quote_eq_type_map_id];
					$rec_eq_type_id			= $eq_type_id;
					$ac_eq_type_id			= '66';
					$ac_serial				= $validation_array['unfulfilled_access_card'][$service_plan_quote_eq_type_map_id];
					$rec_ip_address			= '';
		
				}
					
				//check if the receiver serial / second serial or the access card are already in the database
				//we need this check because we cannot tell if the tech entered information from an unknown customer receivers
				//or if we just could not access our own receiver and he entered it by hand.
		
				//receivers first.
				if ($this->is_serial_in_database($rec_serial) || $this->is_serial_in_database($rec_second_serial)) {
						
					$sql = "SELECT eq_id FROM equipments
							WHERE (eq_serial_number='".$rec_serial."' OR eq_second_serial_number='".$rec_second_serial."')
							";
						
					$existing_receiver_eq_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
					//because this is an existing equipment we have to make sure it does not have old access cards attached.
					$sql = "SELECT eq_id FROM equipments
							WHERE eq_master_id='".$existing_receiver_eq_id."'
							";
		
					$old_ac_eq_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
					if (isset($old_ac_eq_id['eq_id'])) {
							
						$old_access_card = new Thelist_Model_equipments($old_ac_eq_id);
						$old_access_card->set_eq_master_id(null);
							
					}
		
					$rec_equipment_obj = new Thelist_Model_equipments($existing_receiver_eq_id);
		
					//pile all the equipment into an array so we know all the equipment that will have to be connected for the install
					$equipment_to_be_connected[$service_plan_quote_eq_type_map_id]	= $rec_equipment_obj;
		
					$rec_equipment_obj->set_second_serial_number($rec_second_serial);
					$rec_equipment_obj->set_eq_fqdn($rec_ip_address);
					$rec_equipment_obj->set_new_equipment_role($rec_role_obj);
						
				} else {
		
					$rec_equipment_obj = $this->create_equipment_from_type($eq_type_obj, $rec_serial);
					$rec_equipment_obj->set_second_serial_number($rec_second_serial);
					$rec_equipment_obj->update_static_interfaces();
					$rec_equipment_obj->set_eq_fqdn($rec_ip_address);
					$rec_equipment_obj->set_new_equipment_role($rec_role_obj);
		
					//pile all the equipment into an array so we know all the equipment that will have to be connected for the install
					$equipment_to_be_connected[$service_plan_quote_eq_type_map_id]	= $rec_equipment_obj;
		
				}
				
				//now set the mac address of the receiver interface if we have an ip address
				if ($rec_ip_address != '') {
					
					$sql = 	"SELECT ipa.ip_address_id FROM ip_addresses ipa
							WHERE ipa.ip_address='".$rec_ip_address."'
							";
					
					$ip_addresse_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
					
					if (isset($ip_addresse_id['ip_address_id'])){
						
						$ip_addresse_obj	= new Thelist_Model_ipaddress($ip_addresse_id);
						$mac_address_obj	= new Thelist_Multipledevice_command_getmacfromip($ip_addresse_obj);
						
						if ($mac_address_obj != false) {
							
							$rec_interfaces	= $rec_equipment_obj->get_interfaces();
							
							if ($rec_interfaces != null) {
								
								foreach($rec_interfaces as $rec_interface){
									
									if ($rec_interface->get_if_type()->get_if_type() == 'swm' && !isset($found_rec_int)) {
										
										$rec_interface->set_if_mac_address($mac_address_obj->get_macaddress());
										$found_rec_int = 'yes';
									}
								}

								//remove so the next receiver can be tested
								unset($found_rec_int);
							}
						}	
					}
				}
					
				//now do the accesscard
				if ($this->is_serial_in_database($ac_serial)) {
		
					$sql = "SELECT eq_id FROM equipments
												WHERE eq_serial_number='".$ac_serial."'
												";
						
					$existing_access_card_eq_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
					$ac_equipment_obj = new Thelist_Model_equipments($existing_access_card_eq_id);
					$ac_equipment_obj->set_eq_master_id($rec_equipment_obj->get_eq_id());
						
				} else {
						
					$ac_equipment_obj = $this->create_equipment_from_type($accesscard_eq_type_obj, $ac_serial);
					$ac_equipment_obj->set_eq_master_id($rec_equipment_obj->get_eq_id());
		
				}
					
			} else {
				//if not a receiver
				
					if ($validation_array['unfulfilled_use_other_device'][$service_plan_quote_eq_type_map_id] != '') {
		
						//if the equipment is going to be created from the device information
						$exploded_array =	explode('||', $validation_array['unfulfilled_use_other_device'][$service_plan_quote_eq_type_map_id]);
		
						$other_serial 			= $exploded_array['1'];
						$other_eq_type_id		= $eq_type_id;
						$other_ip_address		= $exploded_array['2'];
							
		
					} else {
		
						//if this is equipment that is not dicovered as device but a static entry.
						$other_serial 			= $validation_array['unfulfilled_serial'][$service_plan_quote_eq_type_map_id];
						$other_eq_type_id		= $eq_type_id;
						$other_ip_address		= '';
					}

					//because we use mac addresses for serial number for customer owned routers we look at the interfaces table to see if we already know this equipment
					//also deca adaptors are installed using the serial
					$sql = "SELECT CASE
							WHEN e.eq_id IS NOT NULL THEN e.eq_id
							ELSE i.eq_id
							END AS eq_id
							FROM equipments e
							INNER JOIN interfaces i ON i.eq_id=e.eq_id
							WHERE (e.eq_serial_number='".$other_serial."' OR i.if_mac_address='".$other_serial."')
							GROUP BY eq_id
							";
					
					$existing_other_eq_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);

					//if customer router
					if ($validation_array['unfulfilled_model'][$service_plan_quote_eq_type_map_id] == '46') {
		
					if (isset($existing_other_eq_id['eq_id'])) {
		
						$other_equipment_obj = new Thelist_Model_equipments($existing_other_eq_id);
						$other_equipment_obj->set_eq_fqdn($other_ip_address);
						$other_equipment_obj->set_new_equipment_role($cust_router_role_obj);
							
						//pile all the equipment into an array so we know all the equipment that will have to be connected for the install
						$equipment_to_be_connected[$service_plan_quote_eq_type_map_id]	= $other_equipment_obj;
		
					} else {
						//if this is unknown equipment
						$other_equipment_obj = $this->create_equipment_from_type($eq_type_obj, $other_serial);
						$other_equipment_obj->update_static_interfaces();
						
						//we dont have device to deal with so we must update the interface mac address here.
						$number_of_interfaces	= count($other_equipment_obj->get_interfaces());
						
						//if there is only one interface then we assign that the mac address
						if ($number_of_interfaces == 1) {

							//get the first interface
							$cust_wan_interface	= array_shift(array_values($other_equipment_obj->get_interfaces()));
							$cust_wan_interface->set_if_mac_address($other_serial);
								
						} else {
								
							throw new exception('customer router does not have any interfaces or more than one', 202);
								
						}
						
						$other_equipment_obj->set_eq_fqdn($other_ip_address);
						$other_equipment_obj->set_new_equipment_role($cust_router_role_obj);
							
						//pile all the equipment into an array so we know all the equipment that will have to be connected for the install
						$equipment_to_be_connected[$service_plan_quote_eq_type_map_id]	= $other_equipment_obj;
					}
		
					//if the equipment is a customer phone
				} elseif ($validation_array['unfulfilled_model'][$service_plan_quote_eq_type_map_id] == '57') {
		
					$cust_phone_equipment_obj = $this->create_equipment_from_type($eq_type_obj, $other_serial);
					$cust_phone_equipment_obj->update_static_interfaces();
					$cust_phone_equipment_obj->set_new_equipment_role($cust_phone_role_obj);
		
					//pile all the equipment into an array so we know all the equipment that will have to be connected for the install
					$equipment_to_be_connected[$service_plan_quote_eq_type_map_id]	= $cust_phone_equipment_obj;
		
				} else {
					//all other equipment like Deca BB
					if (isset($existing_other_eq_id['eq_id'])) {
					
						$other_equipment_obj = new Thelist_Model_equipments($existing_other_eq_id);
							
						$other_equipment_obj->update_static_interfaces();
							
						//pile all the equipment into an array so we know all the equipment that will have to be connected for the install
						$equipment_to_be_connected[$service_plan_quote_eq_type_map_id]	= $other_equipment_obj;
					
					} else {
						
						//if this is unknown equipment
						$other_equipment_obj = $this->create_equipment_from_type($eq_type_obj, $other_serial);
						$other_equipment_obj->update_static_interfaces();
							
						//pile all the equipment into an array so we know all the equipment that will have to be connected for the install
						$equipment_to_be_connected[$service_plan_quote_eq_type_map_id]	= $other_equipment_obj;
					}
				}
			}
		}
		
		return $equipment_to_be_connected;
		
	}
	
	public function create_non_serialized_equipment_from_task($task_obj, $equipment_to_be_connected=null)
	{
		if ($equipment_to_be_connected == null) {
			
			$equipment_to_be_connected = array();
		
		}
		
		$sql		= 	"SELECT spqetm.service_plan_quote_eq_type_map_id, spetm.eq_type_group_id, 'null' AS eq_id, et.eq_type_id, et.eq_manufacturer FROM service_plan_quote_task_mapping spqtm
						INNER JOIN service_plan_quote_eq_type_mapping spqetm ON spqetm.service_plan_quote_map_id=spqtm.service_plan_quote_map_id
						INNER JOIN service_plan_eq_type_mapping spetm ON spetm.service_plan_eq_type_map_id=spqetm.service_plan_eq_type_map_id
						INNER JOIN equipment_type_groups etg ON etg.eq_type_group_id=spetm.eq_type_group_id
						INNER JOIN eq_type_group_mapping etgm ON etgm.eq_type_group_id=etg.eq_type_group_id
						INNER JOIN equipment_types et ON et.eq_type_id=etgm.eq_type_id
						WHERE spqtm.task_id='".$task_obj->get_task_id()."'
						AND et.eq_type_serialized='0'
						GROUP BY spqetm.service_plan_quote_eq_type_map_id
						UNION ALL
						SELECT sqetmem.service_plan_quote_eq_type_map_id, spetm.eq_type_group_id, e.eq_id AS eq_id, et.eq_type_id, et.eq_manufacturer FROM service_plan_quote_task_mapping spqtm
						INNER JOIN service_plan_quote_mapping spqm ON spqm.service_plan_quote_map_id=spqtm.service_plan_quote_map_id
						INNER JOIN sales_quotes sq ON sq.sales_quote_id=spqm.sales_quote_id
						INNER JOIN end_user_services eu ON eu.end_user_service_id=sq.end_user_service_id
						INNER JOIN equipment_mapping em ON em.unit_id=eu.unit_id
						INNER JOIN sales_quote_eq_type_map_equipment_mapping sqetmem ON sqetmem.equipment_map_id=em.equipment_map_id
						INNER JOIN service_plan_quote_eq_type_mapping spqetm ON spqetm.service_plan_quote_eq_type_map_id=sqetmem.service_plan_quote_eq_type_map_id
						INNER JOIN service_plan_eq_type_mapping spetm ON spetm.service_plan_eq_type_map_id=spqetm.service_plan_eq_type_map_id
						INNER JOIN equipments e ON e.eq_id=em.eq_id
						INNER JOIN equipment_types et ON et.eq_type_id=e.eq_type_id
						WHERE spqtm.task_id='".$task_obj->get_task_id()."'
						AND (em.eq_map_deactivated IS NULL OR em.eq_map_deactivated > NOW())
						AND et.eq_type_serialized='0'";
		
		$non_serialized_equipments  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		if (isset($non_serialized_equipments['0'])) {
			
			foreach($non_serialized_equipments as $equipment) {
				
				//we are matching database result so 'null' and not just null
				if ($equipment['eq_id'] != 'null') {

					$equipment_to_be_connected[$equipment['service_plan_quote_eq_type_map_id']] = new Thelist_Model_equipments($equipment['eq_id']);
				
				} else {
					
					$eq_type_obj		= new Thelist_Model_equipmenttype($equipment['eq_type_id']);
					$new_equipment_obj	= $this->create_equipment_from_type($eq_type_obj, 'no_serial');
					$new_equipment_obj->update_static_interfaces();
					
					$equipment_to_be_connected[$equipment['service_plan_quote_eq_type_map_id']] = $new_equipment_obj;
					
				}
			}
		}
		
		return $equipment_to_be_connected;

	}
	
	public function map_equipment_at_end_of_install($equipment)
	{
		//now map all equipment to the unit and sales quote eq map
		$j=0;
		foreach($equipment['equipment'] as $mapping_eq) {
		
			//now map all equipment to the unit and sales quote eq map
			$sql14	= 	"SELECT eus.unit_id, sp.service_plan_permanent_install_only AS permanent_install FROM service_plan_quote_eq_type_mapping spqetm
						INNER JOIN service_plan_quote_mapping spqm ON spqm.service_plan_quote_map_id=spqetm.service_plan_quote_map_id
						INNER JOIN service_plans sp ON sp.service_plan_id=spqm.service_plan_id
						INNER JOIN sales_quotes sq ON sq.sales_quote_id=spqm.sales_quote_id
						INNER JOIN end_user_services eus ON eus.end_user_service_id=sq.end_user_service_id
						WHERE spqetm.service_plan_quote_eq_type_map_id='".$equipment['service_plan_quote_eq_type_map'][$j]->get_service_plan_quote_eq_type_map_id()."'
						";
		
			$unit_and_install_type = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql14);

			if (!is_numeric($unit_and_install_type['unit_id'])) {

				throw new exception('one of the service plan quotes eq map does not belong to a unit', 200);
		
			} elseif (!is_numeric($unit_and_install_type['permanent_install'])) {
		
				throw new exception('service plan is not marked as permanent or not', 201);
		
			} else {
		
				$unit_to_have_eq_mapped_obj	= new Thelist_Model_units($unit_and_install_type['unit_id']);
		
				if ($unit_and_install_type['permanent_install'] == 0) {
						
					$new_equipment_map_id = $this->map_equipment_to_unit($unit_to_have_eq_mapped_obj, $mapping_eq, false, false);
						
				} else {
						
					$new_equipment_map_id = $this->map_equipment_to_unit($unit_to_have_eq_mapped_obj, $mapping_eq, true, false);
						
				}
		
				//map the service plan, allow equipment to be remapped but do not allow service plan remap (brand new, service plan)
				$igdi = $equipment['service_plan_quote_eq_type_map'][$j]->map_equipment_map($new_equipment_map_id, true, false);
			}
				
			$j++;
		}
	}
	
	public function map_equipment_to_unit($unit_obj, $equipment, $is_permanent_installation, $remap)
	{
	
		$sql = 		"SELECT COUNT(equipment_map_id) AS rowcount FROM equipment_mapping
						WHERE eq_id='".$equipment->get_eq_id()."'
						AND eq_map_deactivated IS NULL 
						OR eq_map_deactivated > NOW()
						";
			
		$currently_mapped_count = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
		//logging help
		$trace 		= debug_backtrace();
		$method 	= $trace[0]["function"];
		$class		= get_class($this);

		//if not currently mapped. This cannot be a remap because the count has to be 0
		if ($currently_mapped_count == 0) {
	
			if ($is_permanent_installation == true) {
					
				$data = array(
					
									'eq_id'    						=> $equipment->get_eq_id(),
									'unit_id'						=> $unit_obj->get_unit_id(),
									'eq_map_activated'				=> $this->_time->get_current_date_time_mysql_format(),
									'is_permanent_installation'		=> '1'
				);
	
			} else if ($is_permanent_installation == false) {
	
				$data = array(
	
									'eq_id'    						=> $equipment->get_eq_id(),
									'unit_id'						=> $unit_obj->get_unit_id(),
									'eq_map_activated'				=> $this->_time->get_current_date_time_mysql_format(),
									'is_permanent_installation'		=> '0'
				);
	
			}
	
			return Zend_Registry::get('database')->insert_single_row('equipment_mapping',$data,$class,$method);
	
		} else if ($currently_mapped_count == 1) {
	
			if ($remap == false) {
	
				throw new exception('this equipment is already mapped to a unit, and no remap request was requested.', 204);
	
			} else if ($remap == true) {

				//we need the > now clause in order to ensure that even in the case where this equipment is slated to be deactivated later, we catch it and deactivate it now.
				//because we are about to start a new map and there can only be one active at a time
				$sql2 = 	"SELECT * FROM equipment_mapping
							WHERE eq_id='".$equipment->get_eq_id()."'
							AND eq_map_deactivated IS NULL 
							OR eq_map_deactivated > NOW()
							";
	
				$current_map = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql2);
				
				if ($current_map['is_permanent_installation'] == 0) {
					$current_perm	= false;
				} elseif ($current_map['is_permanent_installation'] == 1) {
					$current_perm	= true;
				}

				if ($current_map['unit_id'] != $unit_obj->get_unit_id() || $is_permanent_installation != $current_perm) {
				
					Zend_Registry::get('database')->set_single_attribute($current_map['equipment_map_id'], 'equipment_mapping', 'eq_map_deactivated', $this->_time->get_current_date_time_mysql_format(), $class, $method);
		
						
					if ($is_permanent_installation == true) {
		
						$data = array(
		
											'eq_id'    						=> $equipment->get_eq_id(),
											'unit_id'						=> $unit_obj->get_unit_id(),
											'eq_map_activated'				=> $this->_time->get_current_date_time_mysql_format(),
											'is_permanent_installation'		=> '1'
						);
		
					} else if ($is_permanent_installation == false) {
		
						$data = array(
		
											'eq_id'    						=> $equipment->get_eq_id(),
											'unit_id'						=> $unit_obj->get_unit_id(),
											'eq_map_activated'				=> $this->_time->get_current_date_time_mysql_format(),
											'is_permanent_installation'		=> '0'
						);
		
					}
						
					return Zend_Registry::get('database')->insert_single_row('equipment_mapping',$data,$class,$method);
					
				} else {

					return $current_map['equipment_map_id'];
					
				}
			}
	
		} else if ($currently_mapped_count > 1) {
	
			throw new exception('this equipment is mapped to more than a single unit', 203);
	
		}
	
	}
	
	public function create_new_bai_diplexer()
	{
		//special composite device for filtering moca out
			
		$generic_splitter = new Thelist_Model_equipmenttype('22');
		$generic_diplexer = new Thelist_Model_equipmenttype('23');
			
		$equipments['splitter']		= $this->create_equipment_from_type($generic_splitter, 'no_serial');
		$equipments['splitter']->update_static_interfaces();
		$equipments['diplex_1']		= $this->create_equipment_from_type($generic_diplexer, 'no_serial');
		$equipments['diplex_1']->update_static_interfaces();
		$equipments['diplex_2']		= $this->create_equipment_from_type($generic_diplexer, 'no_serial');
		$equipments['diplex_2']->update_static_interfaces();
			
		$interfaceconnections	= new Thelist_Model_interfaceconnections();
	
		foreach ($equipments['splitter']->get_interfaces() as $split_interface) {
	
			if ($split_interface->get_index() == 1) {
	
				foreach($equipments['diplex_1']->get_interfaces() as $diplex_1_int) {
	
					if ($diplex_1_int->get_index() == 1) {
							
						$interfaceconnections->create_interface_connection($split_interface, $diplex_1_int);
							
					}
				}
					
			} elseif ($split_interface->get_index() == 2) {
					
				foreach($equipments['diplex_2']->get_interfaces() as $diplex_2_int) {
						
					if ($diplex_2_int->get_index() == 1) {
	
						$interfaceconnections->create_interface_connection($split_interface, $diplex_2_int);
	
					}
				}
			}
		}
	
		return $this->equipments_as_xml($equipments);
			
	}
	
	public function equipments_as_xml($equipment_objs)
	{
	
		$xmlDoc = new DOMDocument();
		$head = $xmlDoc->appendChild(
		$xmlDoc->createElement("equipments"));

		foreach ($equipment_objs as $single_equipment) {
	
	
			//create the root element
			$datasource = $head->appendChild(
			$xmlDoc->createElement("equipment"));
				
			//create the poll cycle id attribute
			$datasource->appendChild(
			$xmlDoc->createElement("eq_id", $single_equipment->get_eq_id()));
			$datasource->appendChild(
			$xmlDoc->createElement("eq_type_id", $single_equipment->get_eq_type()->get_eq_type_id()));
			$datasource->appendChild(
			$xmlDoc->createElement("eq_model_name", $single_equipment->get_eq_type()->get_eq_model_name()));
			$datasource->appendChild(
			$xmlDoc->createElement("eq_serial_number", $single_equipment->get_eq_serial_number()));
	
			if ($single_equipment->get_interfaces() != null) {
	
				foreach ($single_equipment->get_interfaces() as $interface) {
	
					$interface_element = $datasource->appendChild(
					$xmlDoc->createElement("interface"));
						
					$interface_element->appendChild(
					$xmlDoc->createElement("if_id", $interface->get_if_id()));
						
					$interface_element->appendChild(
					$xmlDoc->createElement("if_type_id", $interface->get_if_type()->get_if_type_id()));
						
					$interface_element->appendChild(
					$xmlDoc->createElement("if_type_name", $interface->get_if_type()->get_if_type_name()));
	
					$interface_element->appendChild(
					$xmlDoc->createElement("if_index", $interface->get_if_index()));
	
					$interface_element->appendChild(
					$xmlDoc->createElement("if_name", $interface->get_if_name()));
	
					if ($interface->get_if_features() != null) {
	
						$i=1;
						foreach ($interface->get_if_features() as $interface_feature) {
							$i++;
							$interface_feature_element[$i] = $interface_element->appendChild(
							$xmlDoc->createElement("interface_feature"));
	
							$interface_feature_element[$i]	->appendChild(
							$xmlDoc->createElement("if_feature_name", $interface_feature->get_if_feature_name()));
	
							if ($interface_feature->get_mapped_if_feature_value() != null) {
									
								$interface_feature_element[$i]	->appendChild(
								$xmlDoc->createElement("if_feature_value", $interface_feature->get_mapped_if_feature_value()));
									
							}
						}
	
					}
	
					$sql3 = 	"SELECT CASE
										WHEN if_id_a='".$interface->get_if_id()."' THEN if_id_b
										ELSE if_id_a
										END AS if_id
										FROM interface_connections
										WHERE (if_id_a='".$interface->get_if_id()."' OR if_id_b='".$interface->get_if_id()."')
										";
	
					$connections = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql3);
					
					if (isset($connections['0'])) {
	
						foreach ($connections as $single_connection) {
	
							$connection_element = $interface_element->appendChild(
							$xmlDoc->createElement("connection"));
								
							$connection_element->appendChild(
							$xmlDoc->createElement("if_connects_to", $single_connection['if_id']));
	
						}
					}
				}
			}
		}
		
		$xmlDoc->formatOutput = true;
	
		return $xmlDoc->saveXML();
	
	}
		
	public function get_equipment_from_arp($arp_entry)
	{
		
		//this method should probably not be in the inventory class. i cannot find a good home for it just yet, until then it is here. 
			
		//resolve the name of the vendor for the mac
		$macaddressinformation = new Thelist_Deviceinformation_macaddressinformation($arp_entry->get_macaddress());
		$manufacturer = $macaddressinformation->get_equipment_manufacturer();
		
		$sql = 	"SELECT CASE
				WHEN e.eq_id IS NOT NULL THEN e.eq_id
				ELSE i.eq_id
				END AS eq_id
				FROM equipments e
				INNER JOIN interfaces i ON i.eq_id=e.eq_id
				WHERE (e.eq_serial_number='".$arp_entry->get_macaddress()."' OR i.if_mac_address='".$arp_entry->get_macaddress()."')
				GROUP BY eq_id
				";

		$eq_id  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

		if (isset($eq_id['eq_id'])) {
			
			return new Thelist_Model_equipments($eq_id['eq_id']);
		
		} elseif ($manufacturer != false) {

		//receivers
			if (
					$manufacturer == 'HUMAX CO TLD'
					|| $manufacturer == 'THOMSON INC'
					|| $manufacturer == 'SAMSUNG ELECTRONICS'
			) {
					
				//this is the default values for a receiver
				$credential	= new Thelist_Model_deviceauthenticationcredential();
				$credential->fill_default_values('2');
					
				$device				= new Thelist_Model_device($arp_entry->get_ipaddress(), $credential);
				$receiver			= new Thelist_Directvstb_command_getequipment($device);
				
				//receivers require the arp entry in order to create the equipment
				$receiver->set_current_arp_entry($arp_entry);
				$equipment			= $receiver->get_equipment();
				
				//when doing this for many devices the sockets fill up, so we need to logout
				$device->logout_of_device();
				unset($device);
				
				return $equipment;
					
			} elseif (
						$manufacturer == 'ROUTERBOARD'
						|| $manufacturer == 'ROUTERBOARDCOM'
			) {
					
				//mikrotik requires authentication so first we try and find the device in the database
				//after that we use provisioning credentials
					
				$sql = 	"SELECT e.eq_id FROM interfaces i
						INNER JOIN equipments e ON i.eq_id=e.eq_id
						WHERE i.if_mac_address='".$arp_entry->get_macaddress()."'
						";
					
				$eq_id  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
				if (isset($eq_id['eq_id'])) {
					
					return new Thelist_Model_equipments($eq_id);
					
				} else {
					
					$device_access_granted = 'no';
					
					//fill_default_values($version) version numbers, that apply to routeros
					$possible_credential_versions = array('1', '5', '3');
	
					foreach ($possible_credential_versions as $version) {
							
						if ($device_access_granted == 'no') {
							
							try {
									
								//this is the default values for an unprovisioned routerboard, this is the only auth that should exist for devices that do not have equipement.
								$credential	= new Thelist_Model_deviceauthenticationcredential();
								$credential->fill_default_values($version);
									
								$device				= new Thelist_Model_device($arp_entry->get_ipaddress(), $credential);
						
								//if no exception is thrown
								$device_access_granted = 'yes';
						
							} catch (Exception $e) {
									
								switch($e->getCode()) {
						
									case 1203;
									//authentication failure using cred try again
						
									break;
									default;
									throw $e;
								}
							}
						}
					}
					
					if ($device_access_granted == 'yes') {
	
						//if we got in
						$router				= new Thelist_Routeros_command_getequipment($device);
						$equipment			= $router->get_equipment();
						
						//when doing this for many devices the sockets fill up, so we need to logout
						$device->logout_of_device();
						unset($device);
						return $equipment;
						
					} else {
						throw new exception("we cannot access ip '".$arp_entry->get_ipaddress()."', looks like it is a routeros device, but we cannot access it with any standard credentials", 225);
					}
				}	

			} elseif (
						$manufacturer == 'CISCO SYSTEMS'
						|| $manufacturer == 'CISCO SYSTEMS INC'
			) {
					
				//cisco devices
					
				$sql = 	"SELECT e.eq_id FROM interfaces i
						INNER JOIN equipments e ON i.eq_id=e.eq_id
						WHERE i.if_mac_address='".$arp_entry->get_macaddress()."'
						";
					
				$eq_id  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
				if (isset($eq_id['eq_id'])) {
					
					return new Thelist_Model_equipments($eq_id);
					
				} else {
					
					$device_access_granted = 'no';
					
					//fill_default_values($version) version numbers, that apply to cisco
					$possible_credential_versions = array('4');
	
					foreach ($possible_credential_versions as $version) {
							
						if ($device_access_granted == 'no') {
							
							try {
									
								//this is the default values for an unprovisioned routerboard, this is the only auth that should exist for devices that do not have equipement.
								$credential	= new Thelist_Model_deviceauthenticationcredential();
								$credential->fill_default_values($version);
									
								$device				= new Thelist_Model_device($arp_entry->get_ipaddress(), $credential);
						
								//if no exception is thrown
								$device_access_granted = 'yes';
						
							} catch (Exception $e) {
									
								switch($e->getCode()) {
						
									case 1203;
									//authentication failure using cred try again
						
									break;
									default;
									throw $e;
								}
							}
						}
					}
					
					if ($device_access_granted == 'yes') {
	
						//if we got in
						$cisco_device		= new Thelist_Cisco_command_getequipment($device);
						$equipment			= $cisco_device->get_equipment();
						
						//when doing this for many devices the sockets fill up, so we need to logout
						$device->logout_of_device();
						unset($device);
						
						return $equipment;
						
					} else {
						throw new exception("we cannot access ip '".$arp_entry->get_ipaddress()."', looks like it is a cisco device, but we cannot access it with any standard credentials", 226);
					}
				}	

			} else {
				
				//lets try to get the eq_type_from the device tracking database
				$devicetracking		= new Thelist_Model_devicetracking();
				$equipment_type_obj = $devicetracking->update_dhcp_request_tracking();
				
				//now that it is upto date we can query the database for the information
				$sql23 = 	"SELECT * FROM device_tracking
							WHERE mac_address='".$arp_entry->get_macaddress()."'
							";

				$request_detail = Zend_Registry::get('database')->get_tracking_adapter()->fetchRow($sql23);
				
				if (isset($request_detail['discovered_devices_id'])) {
						
					if ($request_detail['eq_type_id'] != 0) {
						
						$eq_type_obj	= new Thelist_Model_equipmenttype($request_detail['eq_type_id']);
						
						if ($request_detail['eq_type_id'] == 46) {
							
							//if this is a customer router then we can set it up.
							$inventory			= new Thelist_Model_inventory();
							$new_equipment		= $inventory->create_equipment_from_type($eq_type_obj, $arp_entry->get_macaddress());
							
							//this is the unmanaged package
							$software_package	= new Thelist_Model_softwarepackage(12);
							
							$new_equipment->set_current_software_package($software_package);
							$new_equipment->set_eq_fqdn($arp_entry->get_ipaddress());
							$new_equipment->update_static_interfaces();
							$new_equipment->set_new_equipment_role(new Thelist_Model_equipmentrole('4'));
							$number_of_interfaces	= count($new_equipment->get_interfaces());
							
							//if there is only one interface then we assign that the mac address
							if ($number_of_interfaces == 1) {
							
								//get the first interface
								$cust_wan_interface	= array_shift(array_values($new_equipment->get_interfaces()));
								$cust_wan_interface->set_if_mac_address($arp_entry->get_macaddress());
							
							} else {
							
								throw new exception('customer router does not have any interfaces or more than one', 208);
							
							}

							return $new_equipment;
						}
					}
				}

				//any other kind of device and we handle it with user input
				throw new exception("dont have instructions on how to handle this vendor: '".$manufacturer."' ", 34);
				
			}
		}
	}
		
	public function get_composite_eq_xml($input_equipment_obj, $eq_composite_name)
	{
		if ($eq_composite_name == 'baidiplexer') {

			//tracking
			$equipment_touched	= ",". $input_equipment_obj->get_eq_id().",";
			
			$interfaceconnection	= new Thelist_Model_interfaceconnections();
			//if we get a generic diplexer
			if ($input_equipment_obj->get_eq_type()->get_eq_type_id() == 23) {
				
				$equipment_in_composite[]		= $input_equipment_obj;
				
				if (($interfaces = $input_equipment_obj->get_interfaces()) != null) {
					
					foreach ($interfaces as $interface) {
						
						$connections	= $interfaceconnection->get_interface_connections($interface);
						
						if ($connections != false) {

							foreach($connections as $conn_interface) {
								
								$conn_equipment_obj	= new Thelist_Model_equipments($conn_interface->get_eq_id());
								
								if ($conn_equipment_obj->get_eq_type()->get_eq_type_id() == 22 && !preg_match("/,".$conn_equipment_obj->get_eq_id().",/", $equipment_touched)) {
									
									$equipment_in_composite[] 	= $conn_equipment_obj;
									$equipment_touched			.=  $conn_equipment_obj->get_eq_id().",";
									
									if (($splitter_interfaces = $conn_equipment_obj->get_interfaces()) != null) {
										
										foreach ($splitter_interfaces as $splitter_interface) {
											
											$spliter_connections	= $interfaceconnection->get_interface_connections($splitter_interface);
											
											if ($spliter_connections != false) {
													
												foreach($spliter_connections as $spliter_connection) {
													
													$split_conn_equipment_obj	= new Thelist_Model_equipments($spliter_connection->get_eq_id());
													
													if ($split_conn_equipment_obj->get_eq_type()->get_eq_type_id() == 23 && !preg_match("/,".$split_conn_equipment_obj->get_eq_id().",/", $equipment_touched)) {
														
														$equipment_in_composite[] 	= $split_conn_equipment_obj;
														$equipment_touched			.=  $split_conn_equipment_obj->get_eq_id().",";
														
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
				
			} elseif ($input_equipment_obj->get_eq_type()->get_eq_type_id() == 22) {
				
				$equipment_in_composite[]		= $input_equipment_obj;
						
				if (($splitter_interfaces = $input_equipment_obj->get_interfaces()) != null) {
			
					foreach ($splitter_interfaces as $splitter_interface) {
							
						$spliter_connections	= $interfaceconnection->get_interface_connections($splitter_interface);
							
						if ($spliter_connections != false) {
								
							foreach($spliter_connections as $spliter_connection) {
									
								$split_conn_equipment_obj	= new Thelist_Model_equipments($spliter_connection->get_eq_id());
									
								if ($split_conn_equipment_obj->get_eq_type()->get_eq_type_id() == 23 && !preg_match("/,".$split_conn_equipment_obj->get_eq_id().",/", $equipment_touched)) {
			
									$equipment_in_composite[] 	= $split_conn_equipment_obj;
									$equipment_touched			.=  $split_conn_equipment_obj->get_eq_id().",";
			
								}
							}
						}
					}
				}
			}

			if (count($equipment_in_composite) == 3) {

				return $this->equipments_as_xml($equipment_in_composite);
				
			} else {

				throw new exception('recreating bai diplexer failed', 205);
				
			}
		} //end bai diplexer
			
	}
	
	private function empty_eq_xml()
	{
	
		$xmlDoc = new DOMDocument();
	
		$head = $xmlDoc->appendChild(
		$xmlDoc->createElement("equipments"));
		$xmlDoc->formatOutput = true;
	
		return $xmlDoc->saveXML();
	
	}	

		
		
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
		
		
		
		//new production above, below old
		
		
		
		
		
// 		public function new_unmanaged_equipment_already_active($serial_number, $eq_type_id, $unit_id, $is_permanent_installation, $eq_master_id=null)
// 		{
		
// 			if ($this->is_serial_in_database($serial_number) == true) {
					
// 				throw new exception('serial number already in the database, cant have multiple equipments with the same serial number');
					
// 			} elseif ($serial_number == '' || $serial_number == null) {
					
// 				throw new exception('empty serial number returned', 3);
					
// 			}

// 			if (isset($eq_type_id) && isset($serial_number)) {
					
// 				$data = array(
					
// 							'eq_type_id'						=> $eq_type_id,
// 							'eq_master_id'						=> $eq_master_id,
// 							'eq_serial_number'					=> $serial_number,
// 							'eq_fqdn' 							=> 'unmanaged',
					
// 				);
					
// 				$trace 		= debug_backtrace();
// 				$method 	= $trace[0]["function"];
// 				$class		= get_class($this);
					
// 				$eq_id = Zend_Registry::get('database')->insert_single_row('equipments',$data,$class,$method);
				
// 				$equipment = new equipments($eq_id);
				////create the interfaces based on the static ifs.
// 				$equipment->update_static_interfaces();
				
// 				$unit = new units($unit_id);
// 				$unit->map_equipment_to_unit($eq_id, $is_permanent_installation, 'false');
				
// 			} else {
					
// 				throw new exception('missing information to add equipment either serial_number or the eq_type_was not provided');
					
// 			}
// 		}
		
// 		public function new_managed_equipment_already_active($fqdn, $device_authentication_credentials, $unit_id, $is_permanent_installation) 
// 		{

		////	fill the device_details
// 			$device_details = $this->device_detail($fqdn, $device_authentication_credentials);

// 			if ($this->is_fqdn_in_database($fqdn) == true) {
				
// 				throw new exception('fqdn already in the database, cant have multiple equipment with the same fqdn', 1);
// 			}

		////	we know we have a device online because we called device_details
// 			if ($this->_device == null) {
			
// 				$this->_device = new device($fqdn, $device_authentication_credentials);
					
// 			}
			
			////get commands based on this info
// 			$device_command_generator = new devicecommandgenerator();
// 			$xml_commands = $device_command_generator->get_commands_without_eq_id_as_xml($device_details['software_package_id'], $device_details['eq_type_id'], '7');

// 			$serial_number = $this->_device->get_last_command_regex_return_value($xml_commands);

// 			if ($this->is_serial_in_database($serial_number['1']['0']) == true) {
				
// 				throw new exception('serial number already in the database, cant have multiple equipments with the same serial number', 2);
				
// 			} elseif ($serial_number['1']['0'] == '' || $serial_number['1']['0'] == null) {
				
// 				throw new exception('empty serial number returned', 3);
				
// 			}
			
			
// 			if (isset($this->_device_detail['eq_type_id']) && isset($serial_number['1']['0']) && isset($fqdn)) {
				
// 				$data = array(
				
// 								'eq_type_id'						=> $this->_device_detail['eq_type_id'],
// 								'eq_serial_number'					=> $serial_number['1']['0'],
// 								'eq_fqdn' 							=> $fqdn,  
				
// 				);
				
// 				$trace 		= debug_backtrace();
// 				$method 	= $trace[0]["function"];
// 				$class		= get_class($this);
				
// 				$eq_id = Zend_Registry::get('database')->insert_single_row('equipments',$data,$class,$method);

// 			} else {
				
// 				throw new exception('missing information to add equipment either fqdn or serial_number or the eq_type_was not provided', 4);
				
// 			}
// 			$unit = new units($unit_id);
// 			$unit->map_equipment_to_unit($eq_id, $is_permanent_installation, 'false');
			
			
			
		////	setup the current software package in the eq software upgrades so we know what is running on it currently.
// 			$data2 = array(
				
// 							'eq_id'									=> $eq_id,
// 							'software_package_id'					=> $device_details['software_package_id'],
// 							'scheduled_upgrade_time' 				=> $this->_time->get_current_date_time_mysql_format(),  
// 							'result'								=> 'success',
// 							'result_timestamp' 						=> $this->_time->get_current_date_time_mysql_format(),
				
// 			);
			
// 			$trace 		= debug_backtrace();
// 			$method 	= $trace[0]["function"];
// 			$class		= get_class($this);
			
// 			Zend_Registry::get('database')->insert_single_row('equipment_software_upgrades',$data2,$class,$method);
			
// 			$equipment = new equipments($eq_id);
// 			$equipment->update_static_interfaces();
// 			$equipment->create_api_auth($device_authentication_credentials);
// 			$equipment->update_all_interface_configurations();
			
		////	create default monitoring
// 			$equipment->update_default_monitoring();
			
// 			if ($equipment->get_interfaces() != null) {
// 				foreach($equipment->get_interfaces() as $interface) {
					
// 					$interface->update_default_monitoring();
					
// 				}
// 			}
			
			
			////since it is mapped owner is now the system
// 			$equipment->set_owner('0');
			
// 			return $equipment;
			
// 		}
		


		public function get_equipment_xml_via_serial($eq_serial)
		{

			$sql = "SELECT eq_id from equipments
					WHERE eq_serial_number='".$eq_serial."'
					";
			
			$eq_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);

			if ($eq_id == false) {
				
			return $this->empty_eq_xml();

			} else {
				
				return $this->equipments_as_xml(array(new Thelist_Model_equipments($eq_id)));
			}

		}
		


		

// 		public function create_new_eq_no_serial_duplicate_interfaces($eq_type_id)
// 		{
// 			$new_eq['device'] = array();
// 			$new_eq['interfaces'] = array();
			
// 			$sql3 = "SELECT * FROM equipment_types
// 					WHERE eq_type_id='".$eq_type_id."'
// 					";
			
// 			$eq_type_detail = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql3);
			
// 			$data = array(
	
// 			'eq_type_id'			=> $eq_type_id,
// 			'eq_serial_number'		=> 'no_serial',
// 			'eq_fqdn'				=> 'no_fqdn',
				
// 			);
				
// 			$eq_id = Zend_Registry::get('database')->get_equipments()->insert($data);
			
// 			$sql = "SELECT * FROM static_if_types
// 					WHERE eq_type_id='".$eq_type_id."'
// 					";
			
// 			$interfaces = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);

// 			$equipment = new Thelist_Model_equipments($eq_id);
	////		create the interfaces based on the static ifs.
// 			$equipment->update_static_interfaces();
		
// 		return $eq_id;
// 		}
		
		
// 		public function addEquipmentNoSerial($eq_type_id){

// 				$data = array(

// 					'eq_type_id'			=> $eq_type_id,


				
// 				);
				
// 			$trace = debug_backtrace();
// 			$method = $trace[0]["function"];
				
// 			$eq_master_id = Zend_Registry::get('database')->insert_single_row('equipments',$data,'inventory',$method);
			
			
			//log the action if a po_item_id is provided, this means this is the first time we see this equipment

			
			
// 			$sql2 = "SELECT * FROM static_if_types
// 					WHERE eq_type_id='".$eq_type_id."'
// 					";

// 			$interfaces = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
			
// 			$equipment = new Thelist_Model_equipments($eq_master_id);
	////		create the interfaces based on the static ifs.
// 			$equipment->update_static_interfaces();
// 			return $eq_master_id;
				
				
			
// 		}

// 		public function set_new_inv_eq($eq_type_id, $eq_serial_number, $po_item_id=null)
// 		{
// 			$sql = "SELECT * FROM equipments
// 					WHERE eq_serial_number='".$eq_serial_number."'
// 					";
				
// 			$serial_exist = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
				
// 			if ($serial_exist == false) {

// 				$data = array(

// 					'eq_type_id'			=> $eq_type_id,
// 					'eq_serial_number'		=> $eq_serial_number,
// 					'po_item_id'			=> $po_item_id,

				
// 				);
				
// 			$trace = debug_backtrace();
// 			$method = $trace[0]["function"];
				
// 			$eq_master_id = Zend_Registry::get('database')->insert_single_row('equipments',$data,'inventory',$method);
			
			
			//log the action if a po_item_id is provided, this means this is the first time we see this equipment
// 			if ($po_item_id != null) {
				
// 				$trace  = debug_backtrace();
// 				$method = $trace[0]["function"];
				
// 				$this->logs->user_log_equipment_transfer($this->user_session->uid, 'Scan PO item into Inventory', get_class($this), $method, 'eq_id', $eq_master_id, '', '', '', '');
				
// 			}
			
			
// 			$sql2 = "SELECT * FROM static_if_types
// 					WHERE eq_type_id='".$eq_type_id."'
// 					";

// 			$interfaces = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
			
// 			$equipment = new Thelist_Model_equipments($eq_master_id);
			//create the interfaces based on the static ifs.
// 			$equipment->update_static_interfaces();

// 				return true;
				
				
// 			} else {
				
// 				return false;
				
// 			}
		
// 		}
		
// 		private function create_if_connection($if_id_a, $if_id_b)
// 		{
// 			$sql = "SELECT if_conn_id FROM interface_connections
// 					WHERE 
// 					(if_id_a='".$if_id_a."' AND if_id_b='".$if_id_b."')
// 					OR
// 					(if_id_a='".$if_id_b."' AND if_id_b='".$if_id_a."')
// 					";

// 			$if_conn_exists = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);

// 			if ($if_conn_exists == false) {
				
// 				$data = array(
				
// 				'if_id_a'			=> $if_id_a,
// 				'if_id_b'			=> $if_id_b,
					
// 				);
					
// 				$if_conn_id = Zend_Registry::get('database')->get_interface_connections()->insert($data);
				
				
// 			} else {
				
// 				$if_conn_id = $if_conn_exists;
				
// 			}

// 			return $if_conn_id;
		
// 		}
		
// 		private function create_row_update_row($where_statement, $table_name, $data)
// 		{
// 			$sql_find_pri_key = "SELECT COLUMN_NAME FROM information_schema.columns
// 									WHERE TABLE_SCHEMA = 'thelist' 
// 									AND TABLE_NAME = '".$table_name."' 
// 									AND extra = 'auto_increment'
// 									";

// 			$auto_increment_column = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql_find_pri_key);
			
// 			$sql = "SELECT $auto_increment_column FROM $table_name
// 					WHERE $where_statement
// 					";
		
// 			$fetch = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);

			
// 			$database_method = "get_".$table_name;
			
// 			if ($fetch == false) {
				
// 				$row_id = Zend_Registry::get('database')->$database_method()->insert($data);

// 			} else if ($fetch != false) {
				
// 				Zend_Registry::get('database')->$database_method()->update($data,"$auto_increment_column=$fetch");
// 				$row_id = $fetch;

// 			}
					
// 				return $row_id;
					
// 		}
		
// 		private function get_interface_type_id($if_type_name, $eq_id, $if_index)
// 		{
			
// 			if ($if_type_name == 'statically_assigned') {

// 				$sql_eq_type_id = 	"SELECT eq_type_id FROM equipments
// 									WHERE eq_id='".$eq_id."'
// 									";
					
// 				$eq_type_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql_eq_type_id);
		
// 				$table = 'static_if_types';
				
// 				$sql_if_type_id = 	"SELECT if_type_id FROM $table
// 											WHERE eq_type_id='".$eq_type_id."'
// 											AND if_index_number='".$if_index."'
// 											";
		
// 				$if_type_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql_if_type_id);
				
// 			} else {
					
// 				$table = 'interface_types';
					
// 				$sql_modular_if_type_id = 	"SELECT if_type_id FROM $table
// 													WHERE if_type_name='".$if_type_name."'
// 													";
				
// 				$if_type_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql_modular_if_type_id);
		
			
		
// 			}
		
// 			if ($if_type_id == false) {
		
// 				throw new exception("Interface of type ".$if_type_name." not defined in ".$table." table, this must be defined before we can continue");
		
// 			} else {
		
// 				return $if_type_id;
		
// 			}
		
		
// 		}
		
// 		private function device_detail($fqdn, $device_authentication_credentials)
// 		{
			//the inventory class needs to know the type of equipment and the running software version before it can add a live piece of equipment to inventory
			//these 2 pieces of information are needed if we want to use the command generator. the requirements should be held to an absolute minimum
			//since they are not coming from the generator.
// 			if ($this->_device == null) {
					
// 				$this->_device = new device($fqdn, $device_authentication_credentials);
		
// 			}
			
// 			$this->_device_detail['device_type'] = $this->_device->get_device_type();

			//ROUTEROS SETUP
// 			if ($this->_device_detail['device_type'] == 'routeros') {

// 				$software_raw = $this->_device->execute_command('/system package print');
// 				preg_match("/routeros-(.*) +([0-9]+[\.][0-9]+)/", $software_raw, $software);

// 				$this->_device_detail['software_version'] = str_replace(array("\r", "\r\n", "\n"), '', $software['2']);
// 				$this->_device_detail['software_architecture'] = str_replace(array("\r", "\r\n", "\n"), '', $software['1']);
					
				//get the software package
// 				$sql2 = 	"SELECT software_package_id FROM software_packages
// 							WHERE software_package_name='routeros'
// 							AND software_package_version='".$this->_device_detail['software_version']."'
// 							AND software_package_architecture='".$this->_device_detail['software_architecture']."'
// 							";
					
// 				$this->_device_detail['software_package_id'] = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
		
// 				$model_name_raw = $this->_device->execute_command('/system routerboard print');
// 				preg_match("/model: +(.*)/", $model_name_raw, $model_name2);
// 				$model_name3 = preg_replace('/\"/', '', $model_name2);

				//name comes with line breaks
// 				$this->_device_detail['model_name'] = str_replace(array("\r", "\r\n", "\n"), '', $model_name3['1']);

				//expand to allow for non routerboard to be found
		
			//BAIROS SETUP
// 			} elseif ($this->_device_detail['device_type'] == 'bairos') {
					
// 				$software_raw = $this->_device->execute_command('cat /etc/*release*');
// 				preg_match("/CentOS release +([0-9]+[\.][0-9]+)/", $software_raw, $software);
					
					
// 				$software2_raw = $this->_device->execute_command('uname -a');
// 				if (preg_match("/x86_64/", $software2_raw, $empty)){
		
// 					$this->_device_detail['software_architecture'] = 'x86_64';
		
// 				} elseif (preg_match("/i386/", $software2_raw, $empty)) {
		
// 					$this->_device_detail['software_architecture'] = 'x86';
		
// 				} else {
		
// 					throw new exception('inventory does not recognize this bairos software architecture');
		
// 				}
					
// 				$this->_device_detail['software_version'] = $software['1'];
					
// 				$sql2 = 	"SELECT software_package_id FROM software_packages
// 							WHERE software_package_name='centos'
// 							AND software_package_version='".$this->_device_detail['software_version']."'
// 							AND software_package_architecture='".$this->_device_detail['software_architecture']."'
// 							";
		
// 				$this->_device_detail['software_package_id'] = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
		
// 				$model_name_raw = $this->_device->execute_command('lspci -vb | grep "Micro-Star International"');
					
// 				if(preg_match("/Micro-Star International/", $model_name_raw, $empty)) {
					
// 					$this->_device_detail['model_name'] = 'msi-ixp';
					
// 				}

				//CISCO SETUP
// 				} elseif ($this->_device_detail['device_type'] == 'cisco') {
					

// 				$software_raw = $this->_device->execute_command('show version');
// 				preg_match("/Version (.*), /", $software_raw, $software);

// 				$this->_device_detail['software_architecture'] = 'ios';
					
// 				$this->_device_detail['software_version'] = $software['1'];
					
// 				$sql2 = 	"SELECT software_package_id FROM software_packages
// 							WHERE software_package_name='IOS'
// 							AND software_package_version='".$this->_device_detail['software_version']."'
// 							AND software_package_architecture='".$this->_device_detail['software_architecture']."'
// 							";
		
// 				$this->_device_detail['software_package_id'] = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
					
// 				preg_match("/Model number: +(.*)/", $software_raw, $model_name2);
									
				////name comes with line breaks
// 				$this->_device_detail['model_name'] = str_replace(array("\r", "\r\n", "\n"), '', $model_name2['1']);
					
// 			} else {
					
// 				throw new exception('inventory does not recognize this device type');
					
// 			}
			//get the equipment type
// 			$sql = 		"SELECT eq_type_id FROM equipment_types
// 						WHERE eq_model_name='".$this->_device_detail['model_name']."'
// 						";
		
// 			$this->_device_detail['eq_type_id'] = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
// 			return $this->_device_detail;
		
// 			}
}
?>