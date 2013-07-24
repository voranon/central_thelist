<?php
//exception codes 15700-15799
class InventoryController extends Zend_controller_Action
{
	private $_user_session;

	public function init()
	{
		$this->_user_session 	= new Zend_Session_Namespace('userinfo');
	
		if($this->_user_session->uid == '') {
				
			//no uid, user not logged in
			Zend_Registry::get('logs')->get_app_logger()->log("User not logged in, return to index", Zend_Log::ERR);
			header('Location: /');
			exit;
				
		} else {
			
			//nothing not a perspective controller
			$layout_manager = new Thelist_Utility_layoutmanager($this->_user_session->current_perspective, $this->_helper);
			$layout_manager->set_layout();
				
			//create the head
			$main_menu     		= new Thelist_Html_element_mainmenu();
			$perspective_menu 	= new Thelist_Html_element_perspectivemenu();
				
			$main_menu->set_htmlmainmenu($this->_user_session->current_perspective);
			$perspective_menu->set_htmlperspectivemenu($this->_user_session->current_perspective);
			
			// create menu for main and perspective
			$this->view->placeholder('mainmenu')->append($main_menu->get_htmlmainmenu());
			$this->view->placeholder('perspective_menu')->append($perspective_menu->get_htmlperspectivemenu());
			
			// create homelink
			$this->view->placeholder('homelink')->append($this->_user_session->perspective);

		}
	}
	
	public function preDispatch()
	{
		$permission			= new Thelist_Utility_acl($this->_user_session->role_id);
		$controller 		= $this->getRequest()->getControllerName();
		$action 			= $this->getRequest()->getActionName();
	
		$clearance 			= $permission->acl_clearance($action, $controller);
	
		//log the page request
		$report	= array(
								'uid'					=> $this->_user_session->uid,
								'page_name'				=> $this->view->url(),
								'message_1'				=> '',
								'message_2'				=> '',
		);
	
		if ($clearance === true) {
			
			$report['event']	= 'page_change';
			Zend_Registry::get('database')->insert_single_row('user_event_logs', $report, $controller, $action);
	
		} else {
				
			$report['event']	= 'acl_deny';
			Zend_Registry::get('database')->insert_single_row('user_event_logs', $report, $controller, $action);
				
			throw new exception("'".$this->_user_session->firstname." ".$this->_user_session->lastname."'. You are trying to access controller name: '".$controller."' using Action name: '".$action."', but you are not allowed to access this page", 22500);
		}
	}
	
	public function postDispatch()
	{
	
	}
	
	public function indexAction()
	{
		
	}
	

	
	public function checktechequipmentinventoryfortaskAction()
	{
		$this->_helper->layout->disableLayout();
		//eventually make this method check the tech inventory for the required items
		if($this->getRequest()->isPost()) {

			foreach($_POST as $key => $value) {
				
				if (preg_match("/missing_item_/", $key, $empty)) {
				
					if (!isset($missing_equipment_groups)) {
						
						$missing_equipment_groups = array();
					}
					
					$missing_equipment_groups[] = $value;
				
				}
			}
			
			if (isset($_POST['task_id'])) {
				if (isset($missing_equipment_groups)) {
					
					//$inventory_obj		= new thelist_model_inventory();
					//$inventory_obj->missing_equipment_for_install($_POST['task_id'], $missing_equipment_groups);
					//currently going to the same place in the future fix this so this catches a problem and lets us continue
					
					echo "<script>
					window.close();
					window.opener.location.href='/fieldinstallation/serviceplaninstallhelp?task_id=".$_POST['task_id']."';
					</script>";	
					
					
				} else {
					
					echo "<script>
					window.close();
					window.opener.location.href='/fieldinstallation/serviceplaninstallhelp?task_id=".$_POST['task_id']."';
					</script>";	
	
				}
			}
		}
		
		if (isset($_GET['task_id']) && !$this->getRequest()->isPost()) {
		
			$task_obj	= new Thelist_Model_tasks($_GET['task_id']);
			
			if ($task_obj->get_status() == '12') {
				
				$fieldxmlapi_obj							= new Thelist_Model_fieldxmlapi();
				$task_equipment_xml_obj						= $fieldxmlapi_obj->get_task_equipment($task_obj);
				$this->view->task_equipment_array			= $fieldxmlapi_obj->get_required_equipment_as_array($task_equipment_xml_obj);
				
				
			} else {
				
				$this->view->error							= 'Task Closed';
				
			}	
		}
	}
	
	public function verifytaskinstallAction()
	{
		$this->_helper->layout->disableLayout();
	
		if (isset($_GET['if_id']) && isset($_GET['action']) && isset($_GET['task_id']) && !$this->getRequest()->isPost()) {
			
			$task_obj				= new Thelist_Model_tasks($_GET['task_id']);
			$interface_obj			= new Thelist_Model_equipmentinterface($_GET['if_id']);
			
			if ($task_obj->get_status() == '12') {
			
				if ($_GET['action'] == 'Connect') {
					
					$use_caching = false;
					
					if (isset($_GET['error'])) {
						
						//we allow caching on errors
						$use_caching = true;
						
						if ($_GET['error'] == '80') {
		
							$this->view->validation_message = 'You must provide a serial number for each unknown receiver';
							
						} elseif ($_GET['error'] == '81') {
		
							$this->view->validation_message = 'You did not tie the customer router requirement to a device or provide a serial number';
							
						} elseif($_GET['error'] == '82') {
							
							$this->view->validation_message = 'You cannot provide a serial (maybe rid / ac) and tie an unknown device to the same requirement, either/or please';
							
						} elseif($_GET['error'] == '83') {
							
							$this->view->validation_message = 'You provided an equipment type for a missing requirement, but the provided type does not support the requirement';
							
						} elseif($_GET['error'] == '84') {
							
							$this->view->validation_message = 'You tied a requirement to an unknow receiver, but dident give us the serial number';
							
						} elseif($_GET['error'] == '85') {
							
							$this->view->validation_message = 'There is an unknown device in the unit that was not tied to a piece of equipment to be installed';
							
						} elseif($_GET['error'] == '86') {
							
							$this->view->validation_message = 'You did not provide a model number for all missing requirements';
							
						} elseif($_GET['error'] == '87') {
							
							$this->view->validation_message = 'At least one requirement was not matched with an unknown device or receiver.';
							
						} elseif($_GET['error'] == '88') {
							
							$this->view->validation_message = 'You indicated an unknown device as a receiver, but dident tie it to an unknown device or provide serial/rid/ac info';
							
						} elseif($_GET['error'] == '89') {
	
							$this->view->validation_message = 'You filled 2 requirements with the same unknown device';
							
						} elseif($_GET['error'] == '204') {
	
							$this->view->validation_message = 'Some Equipment is already mapped to another unit, this needs to be freed up from the old unit before you can use it on this install';
							
						} elseif($_GET['error'] == '206') {
	
							$this->view->validation_message = 'You selected a Receiver to fulfill a requirement that is not a receiver';
							
						} elseif($_GET['error'] == '207') {
	
							$this->view->validation_message = 'You selected a phone to fulfill the requirement, but the selected device has an ip address, a phone cannot have an ip';
							
						} elseif($_GET['error'] == '216') {
	
							$this->view->validation_message = 'We are trying to access a routerboard, but the firewall is keeping us from accessing the device, please load the standard config on the device';
							
						} elseif($_GET['error'] == '217') {
	
							$this->view->validation_message = 'We are trying to access a new routerboard, but it looks like an old arp entry is keeping us from accessing the device, please rescan the port';
							
						} elseif($_GET['error'] == '300') {
	
							$this->view->validation_message = 'There cannot be receivers in the pool of future equipment, perform tv install first';
							
						} elseif($_GET['error'] == '301') {
	
							$this->view->validation_message = 'The service point interface has changed, please exit this winow and get a new interface to connect to via the service port button';
							
						} elseif($_GET['error'] == '121') {
	
							$this->view->validation_message = 'The serial number you provided for one or more of the equipment installed does not match the format we are expecting, check the serial again';
							
						} elseif($_GET['error'] == '503') {
	
							$this->view->validation_message = 'The service plan you are installing has a piece of equipment that is tied to a homerun, only its the wrong equipment. As a result the service plan is invalid. error: 503';
							
						} else {
							
							$trace  = debug_backtrace();
							$method = $trace[0]["function"];
							$class	= get_class($this);
							
							$this->view->validation_message = "We received an unknown error code, please contact engineering to determine if this is serious, do not continue! Error: ".$_GET['error']." Class: ".$class." Method: ".$method." ";
						}
						
					} else {
						
						$this->view->validation_message = '';
					}
	
				//$task_equipment_xml_obj								= $this->_inventory->verify_task_service_point_install($task_obj, $interface_obj);
				$fieldxmlapi_obj										= new Thelist_Model_fieldxmlapi();
				$task_equipment_validation_xml_obj						= $fieldxmlapi_obj->verify_equipment_on_sp_interface($task_obj, $interface_obj, $use_caching);
				$return_array											= $fieldxmlapi_obj->task_equipment_validation_as_array($task_equipment_validation_xml_obj);
	
				//accessing equipment errors
				if (isset($return_array['error'])) {
					
					if($return_array['error'] == '216') {
					
						$this->view->error = 'We are trying to access a new routerboard, but the firewall is keeping us from accessing the device, please load the standard config on the new device';
						
					} else {
								
						$trace  = debug_backtrace();
						$method = $trace[0]["function"];
						$class	= get_class($this);
							
						$this->view->error = "We received an unknown error code while identifying equipment in install, please contact engineering to determine if this is serious, do not continue! Error: ".$_GET['error']." Class: ".$class." Method: ".$method." ";
					}

					
				} else {
					
					//we need to generate another dropdown of all the unknown access cards for the view
					$this->view->other_devices ="<OPTION VALUE=''>---SELECT ONE---</OPTION>";
					
					foreach($return_array as $task) {
						
						if (isset($task['unknown_receivers'])) {
						
							foreach($task['unknown_receivers'] as $unknown_receiver) {
								
								$this->view->other_devices .="<OPTION VALUE='unknown_receiver||".$unknown_receiver['access_card']."||".$unknown_receiver['receiver_id']."||".$unknown_receiver['ip_address']."||'>AC: ".$unknown_receiver['access_card']."</OPTION>";
								
							}
						}
						
						if (isset($task['unknown_device'])) {
						
							foreach($task['unknown_device'] as $unknown_device) {
						
								$this->view->other_devices .="<OPTION VALUE='unknown_device||".$unknown_device['mac_address']."||".$unknown_device['ip_address']."||'>MAC: ".$unknown_device['mac_address']."</OPTION>";
						
							}
						}
					}
				
					$this->view->task_equipment_validation_array			= $return_array;
				}
				
				$this->view->receiver_types	 ="<OPTION VALUE=''>---SELECT ONE---</OPTION>";
				
				$sql2 =		"SELECT et.eq_type_id, et.eq_manufacturer, et.eq_model_name FROM equipment_types et
							INNER JOIN eq_type_group_mapping etgm ON etgm.eq_type_id=et.eq_type_id
							WHERE etgm.eq_type_group_id IN (1,2,3,4,7,8,11)
							AND et.eq_type_id NOT IN (52,53,67,68)
							ORDER BY et.eq_manufacturer, et.eq_model_name
							";
				
				$receivers = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
				
				
				foreach ($receivers as $receiver) {
				
					$this->view->receiver_types .="<OPTION VALUE='".$receiver['eq_type_id']."'>".$receiver['eq_manufacturer']." ".$receiver['eq_model_name']."</OPTION>";
				}
					
				} elseif($_GET['action'] == 'Disconnect') {
					
					
					
				}

			} else {
				
				$this->view->error							= 'Task Closed';
				
			}
			
		} elseif ($this->getRequest()->isPost()) {
			
			if (isset($_POST['task_id']) && isset($_POST['if_id']) && isset($_POST['action'])) {

				$task_obj				= new Thelist_Model_tasks($_POST['task_id']);
				
				if ($task_obj->get_status() == '12') {
					
					if ($_POST['action'] == 'Connect') {
							
						if (isset($_POST['equipment_error'])) {
								
							//if somehow the tech was able to get past the frontend validations for the install even though there is an error
							//we catch it here
							throw new exception('Major violation, equipment that belong in other unit is visible on this interface and we still posted');
								
						}
	
						$fieldxmlapi_obj										= new Thelist_Model_fieldxmlapi();
						$validate_install_post_and_provision_xml_obj			= $fieldxmlapi_obj->validate_install_post_and_provision($_POST);
						$return_array											= $fieldxmlapi_obj->validate_install_post_and_provision_as_array($validate_install_post_and_provision_xml_obj);

						if (isset($return_array['error'])) {
							
							echo "<script>
							window.location.href='/inventory/verifytaskinstall/?task_id=".$_POST['task_id']."&if_id=".$_POST['if_id']."&action=".$_POST['action']."&error=".$return_array['error']."';
							</script>";	
							
						} elseif (isset($return_array['success'])) {
							
							//if we are successful
							echo "<script>
							window.close();
	 						window.opener.location.href='/fieldinstallation/serviceplaninstallhelp?task_id=".$_POST['task_id']."';
							</script>";

						}
					}
					
				} else {

					$this->view->error							= 'Task Closed';
					
				}
			}
		}
	}
	

	
	
	public function addconnectionAction()
	{
		
		//for the establish connections java app

		$interfaceconnection		= new Thelist_Model_interfaceconnections();
		$interfaceconnection->create_interface_connection(new Thelist_Model_equipmentinterface($_POST['src_if_id']), new Thelist_Model_equipmentinterface($_POST['dst_if_id']));
		
		$this->_helper->layout->disableLayout();	
	}
	
	public function removeconnectionAction()
	{
	
		//for the establish connections java app
	
		$interfaceconnection		= new Thelist_Model_interfaceconnections();
		$interfaceconnection->remove_interface_connection(new Thelist_Model_equipmentinterface($_POST['src_if_id']), new Thelist_Model_equipmentinterface($_POST['dst_if_id']));
	
		$this->_helper->layout->disableLayout();
	}
	
	public function establishconnectionsAction()
	{
		//everything in the html
	}
	
	
	public function addequipmenttoinventoryAction()
	{
	
		if ($this->getRequest()->isPost()) {
	
			Zend_Registry::get('database')->get_thelist_adapter()->beginTransaction();
				
			if (isset($_POST['create'])) {
	
				$options = array('function_type' => 'add');
	
				$addequipmenttoinventoryform = new Thelist_Inventoryform_addequipmenttoinventoryform($options);
				$addequipmenttoinventoryform->setAction('/inventory/addequipmenttoinventory');
				$addequipmenttoinventoryform->setMethod('post');
				$this->view->addequipmenttoinventoryform=$addequipmenttoinventoryform;
	
				if ($addequipmenttoinventoryform->isValid($_POST)) {
						
					try {

						$inventory_obj		= new Thelist_Model_inventory();
						$equipment_type		= new Thelist_Model_equipmenttype($_POST['eq_type_id']);
						$new_equipment		= $inventory_obj->create_equipment_from_type($equipment_type, $_POST['serial_number']);
						$new_equipment->update_static_interfaces();
	
						if ($_POST['eq_role_id'] != '') {
								
							$role_obj	= new Thelist_Model_equipmentrole($_POST['eq_role_id']);
							$new_equipment->set_new_equipment_role($role_obj);
								
						}
	
						Zend_Registry::get('database')->get_thelist_adapter()->commit();
	
					} catch (Exception $e) {
							
						switch($e->getCode()) {
								
							case 121;
							//121, serial number is wrong
							Zend_Registry::get('database')->get_thelist_adapter()->rollback();
							$this->view->error	= 'The serial number provided does not match any known serial number patterns for this equipment type';
							break;
							case 214;
							//214, serial number exists
							Zend_Registry::get('database')->get_thelist_adapter()->rollback();
							$this->view->error	= 'This Serial Number is already in the database';
							break;
							default;
							throw $e;
								
						}
					}
				}
			}
	
		} else {
				
			$this->view->eq_added = 0;
				
			$options = array('function_type' => 'add');
				
			$addequipmenttoinventoryform = new Thelist_Inventoryform_addequipmenttoinventoryform($options);
			$addequipmenttoinventoryform->setAction('/inventory/addequipmenttoinventory');
			$addequipmenttoinventoryform->setMethod('post');
			$this->view->addequipmenttoinventoryform=$addequipmenttoinventoryform;
	
		}
	}
	
	public function assignequipmenttounitAction()
	{
		//for now only done for infrastructure units
		if (isset($_GET['unit_group_id'])) {

				$options = array('function_type' => 'add', 'unit_group_id' => $_GET['unit_group_id']);
				
				$assignequipmenttounitform = new Thelist_Inventoryform_assignequipmenttounitform($options);
				$assignequipmenttounitform->setAction('/inventory/assignequipmenttounit');
				$assignequipmenttounitform->setMethod('post');
				$this->view->assignequipmenttounitform=$assignequipmenttounitform;

		}

		if ($this->getRequest()->isPost()) {
			
			if (isset($_POST['create'])) {

				$options = array('function_type' => 'add', 'unit_group_id' => $_POST['unit_group_id']);
	
				$assignequipmenttounitform = new Thelist_Inventoryform_assignequipmenttounitform($options);
				$assignequipmenttounitform->setAction('/inventory/assignequipmenttounit');
				$assignequipmenttounitform->setMethod('post');
				$this->view->assignequipmenttounitform=$assignequipmenttounitform;
	
				if ($assignequipmenttounitform->isValid($_POST)) {
					
					try {

						//Zend_Registry::get('database')->get_thelist_adapter()->beginTransaction();
						
						$unit_obj 		= new Thelist_Model_unit($_POST['unit_id']);
						$equipment		= new Thelist_Model_equipments($_POST['eq_id']);
						
						if (isset($_POST['remap'])) {	
							$remap = true;	
						} else {
							$remap = false;
						}
						
						if (isset($_POST['permanent'])) {
							$is_permanent_installation = true;
						} else {
							$is_permanent_installation = false;
						}
						
						$inventory_obj			= new Thelist_Model_inventory();
						
						$inventory_obj->map_equipment_to_unit($unit_obj, $equipment, $is_permanent_installation, $remap);
	
						//Zend_Registry::get('database')->get_thelist_adapter()->commit();
						
					} catch (Exception $e) {
							
						switch($e->getCode()) {
	
							case 204;
							//204, already mapped and no remap requested
							$this->view->error	= 'This Equipment is already mapped to another unit';
							break;
							default;
							throw $e;
	
						}
					}
				}
			}
		} 
	}
	
	public function equipmentdropdownajaxAction()
	{
		$this->view->equipments = Zend_Registry::get('database')->get_equipments()->fetchAll('eq_type_id='.$_GET['eq_type_id']);
	}
	
	public function getequipmentbyfqdnajaxAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		if (isset($_GET['eq_type_id'])) {
		
			$sql = 	"SELECT * FROM equipments e
					WHERE e.eq_type_id='".$_GET['eq_type_id']."'
					ORDER BY e.eq_fqdn
					";
			
			$equipments = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			if (isset($equipments['0'])) {
				$eqs = '<OPTION value=\"0\">---Select One---</OPTION>';
				foreach ($equipments as $equipment) {
					
					$eqs .= "<OPTION value=\"".$equipment['eq_id']."\">".$equipment['eq_fqdn']."</OPTION>";
				}
				echo $eqs;
			}
		}
	}
	
	public function getequipmentreachablefromrouterAction()
	{

		$sql = 	"SELECT * FROM equipments e
				WHERE (e.eq_type_id='3'	OR e.eq_type_id='48' OR e.eq_type_id='63' OR e.eq_type_id='74')
				ORDER BY e.eq_fqdn
				";
			
		$equipments = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
		if (isset($equipments['0'])) {
			$eqs = '<OPTION value=\"0\">---Select One---</OPTION>';
			foreach ($equipments as $equipment) {
					
				$eqs .= "<OPTION value=".$equipment['eq_id'].">".$equipment['eq_fqdn']."</OPTION>";
			}
			
			$this->view->routers	=  $eqs;
		}
			
		if ($this->getRequest()->isPost() && isset($_POST['get_equipment'])) {
			
			if (isset($_POST['eq_id'])) {
				
				//soemthing was selected
				if ($_POST['eq_id'] != 0) {
					
					$equipment_obj 		= new Thelist_Model_equipments($_POST['eq_id']);
					$device				= $equipment_obj->get_device();
					
					$all_interfaces	= $device->get_interfaces();
					
					if (isset($all_interfaces['running_interfaces']['0'])) {
						$arp_entries = array();
						foreach($all_interfaces['running_interfaces'] as $interface) {
							
							if (isset($interface['configuration']['interface_ip_addresses'])) {
								
								foreach ($interface['configuration']['interface_ip_addresses'] as $ip) {
									
									$new_arp =  $device->get_subnet_arp_entries($ip->get_ip_subnet_address() , $ip->get_ip_subnet_cidr());
									
									if ($new_arp != null) {
										
										$arp_entries =	array_merge($arp_entries, $new_arp);
									}
								}
							}
						}
					}
					
					if (isset($arp_entries)) {
						
						if (count($arp_entries) > 0) {
							
							$inventory_obj	= new Thelist_Model_inventory();
							$i=0;
							$j=0;
							foreach ($arp_entries as $arp_entry) {

								if (isset($arp_track[$arp_entry->get_macaddress()])) {
									//already did this mac address, we dont need to do it again
									
								} else {
									
									$arp_track[$arp_entry->get_macaddress()] = $arp_entry->get_macaddress();
									
									try {
	
										$single_equipment	= $inventory_obj->get_equipment_from_arp($arp_entry);
										
										if (is_object($single_equipment)) {
										
											$single_equipment->update_static_interfaces();
											$single_equipment->update_default_interface_configurations();
											$single_equipment->update_all_interface_configurations();
											$single_equipment->backup_device();
										}
	
									} catch (Exception $e) {
											
										switch($e->getCode()) {

											case 7804;
											//cannot update config that is non edit and already present.
											break;
											case 1203;
											$error = "Device, Wrong Credentials";
											break;
											case 1202;
											$error = "Device, Host Timed Out";
											break;
											case 34;
											$error = 'Device, Vendor Unknown';
											break;
											default;
											throw $e;
												
										}
									}
									
									if (is_object($single_equipment)) {

										$view_return['equipment'][$i]['manufacturer'] 				= $single_equipment->get_eq_type()->get_eq_manufacturer();
										$view_return['equipment'][$i]['model'] 						= $single_equipment->get_eq_type()->get_eq_model_name();
										$view_return['equipment'][$i]['fqdn'] 						= $single_equipment->get_eq_fqdn();
										$view_return['equipment'][$i]['eq_id'] 						= $single_equipment->get_eq_id();
										$i++;
										
										$view_return['ips'][$j]['ip_address']						= $arp_entry->get_ipaddress();
										$view_return['ips'][$j]['ip_status']						= 1;
										$view_return['ips'][$j]['mac_vendor']						= $arp_entry->get_macaddress_obj()->get_equipment_manufacturer();
										$view_return['ips'][$j]['ip_error']							= 'N/A';
										$j++;
										
									} else {
										
										$view_return['ips'][$j]['ip_address']						= $arp_entry->get_ipaddress();
										$view_return['ips'][$j]['ip_status']						= 0;
										$view_return['ips'][$j]['mac_vendor']						= $arp_entry->get_macaddress_obj()->get_equipment_manufacturer();
										$view_return['ips'][$j]['ip_error']							= $error;
										$j++;
									}
								}
								
								unset($single_equipment);
							}
						}
					}
					
					
					if (isset($view_return)) {
						$this->view->equipment		= $view_return;
					}
				}
			}
		}
	}
	
	
	public function newbaidiplexerAction()
	{
		$inventory_obj			= new Thelist_Model_inventory();
		$this->view->eq_if_xml 	= $inventory_obj->create_new_bai_diplexer();
		$this->_helper->layout->disableLayout();
	}
	
	public function geteqifxmlAction()
	{
		$inventory_obj			= new Thelist_Model_inventory();
		
		$this->_helper->layout->disableLayout();
		if (isset($_GET["eq_serial_number"])) {
					
			$eq_serial = $_GET["eq_serial_number"];
			$this->view->eq_if_xml = $inventory_obj->get_equipment_xml_via_serial($eq_serial);
	
		} elseif (isset($_GET['if_id']) && !isset($_GET['composite_equipment'])) {
	
			$if_obj				= new Thelist_Model_equipmentinterface($_GET['if_id']);
			$eq_obj				= new Thelist_Model_equipments($if_obj->get_eq_id());
			$this->view->eq_if_xml = $inventory_obj->equipments_as_xml(array($eq_obj));
			
		} else if (isset($_GET["if_id"]) && isset($_GET["composite_equipment"])) {
	
			$if_obj				= new Thelist_Model_equipmentinterface($_GET['if_id']);
			$eq_obj				= new Thelist_Model_equipments($if_obj->get_eq_id());
			$this->view->eq_if_xml = $inventory_obj->get_composite_eq_xml($eq_obj, $_GET['composite_equipment']);
	
		} else {
	
			echo "<center><br><br><H2>You have to give me something to work with here. There is nothing in the url that makes sense!</H2></center> ";
			die;
	
		}
	}

		
		

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	public function eqtransferAction(){
		
		$users = Zend_Registry::get('database')->get_users()->fetchAll();
		$user_list='';
		foreach($users as $user){
		 $user_list.="<option value='".$user['uid']."'>".$user['firstname']."  ".$user['lastname']."</option>";
		 
		}
		$this->view->user_list= $user_list;
		
	}
	
	public function eqtransferajaxAction()
	{	
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		
		
		$mode   		=  $_GET['mode'];
		
		
		if($mode=='query')
		{
			
			$barcode  		=  $_GET['barcode'];
			$new_owner_id	=  $_GET['new_owner'];
		
			$owner_sql=  "SELECT CONCAT(firstname,' ',lastname) AS 'name'
						  FROM users 
						  WHERE uid=".$new_owner_id;
		
			$new_owner = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($owner_sql);
		
			$sql = "SELECT e.eq_id,et.eq_type_name,e.eq_serial_number,CONCAT(u.firstname,' ',u.lastname) AS 'name'
					FROM equipments e
					LEFT OUTER JOIN equipment_types et ON e.eq_type_id=et.eq_type_id
					LEFT OUTER JOIN users u ON e.uid=u.uid
					WHERE eq_serial_number='".$barcode."'";
		
			$eq = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
			$output='';
			if($eq['eq_id']==''){
				$output.="not_found";
			}else{
				$output.="<tr id='eq' eq_id='".$eq['eq_id']."'>
								<td>".$eq['eq_id']."</td>
								<td>".$eq['eq_type_name']."</td>
								<td>".$eq['eq_serial_number']."</td>
								<td>".$eq['name']."</td>
								<td id='new_owner' new_owner='".$new_owner_id."'>".$new_owner['name']."</td>
								<td><input type='button' class='button' name='remove' eq_id='".$eq['eq_id']."' id='remove' value='Remove'></input></td>
						  </tr>";
			}
		
		
			echo $output;
		
		}else if($mode=='update')
		{
			$eq_id  		=  $_GET['eq_id'];
 			$new_owner_id	=  $_GET['new_owner_id'];
 			$eq             =  new Thelist_Model_equipments($eq_id);
 			
 			$eq->set_owner($new_owner_id);

		}
		
	}
	
	public function iftypefeatureAction()
	{
		$this->_helper->layout->disableLayout();
	
		if (isset($_GET['if_type_feature_map_id'])) {
	
			$options = array('function_type' => 'edit', 'variable' => "".$_GET['if_type_feature_map_id']."", 'if_type_id' => "".$_GET['if_type_id']."");
	
			$iftypefeatureform = new Thelist_Inventoryform_iftypefeature($options);
			$iftypefeatureform->setAction('/inventory/iftypefeature');
			$iftypefeatureform->setMethod('post');
			$this->view->iftypefeatureform=$iftypefeatureform;
	
			//submitting a new regex
		} else if (!isset($_GET['if_type_feature_map_id']) && isset($_GET['if_type_id'])) {
	
			$options = array('function_type' => 'add', 'variable' => "".$_GET['if_type_id']."");
	
			$iftypefeatureform = new Thelist_Inventoryform_iftypefeature($options);
			$iftypefeatureform->setAction('/inventory/iftypefeature');
			$iftypefeatureform->setMethod('post');
			$this->view->iftypefeatureform=$iftypefeatureform;

		}
		
	
		if($this->getRequest()->isPost()) {
			
			if (isset($_POST['delete'])) {
	
				$options = array('function_type' => 'edit', 'variable' => "".$_POST['if_type_feature_map_id']."", 'if_type_id' => "".$_POST['if_type_id']."");
				$iftypefeatureform = new Thelist_Inventoryform_iftypefeature($options);
					
				if ($iftypefeatureform->isValid($_POST)) {

										$interface_type = new Thelist_Model_interfacetype($_POST['if_type_id']);
										$interface_type->remove_map_if_type_feature($_POST['if_type_feature_map_id']);

					echo 	"<script>
							window.close();
							window.opener.location.href='/inventory/editiftype/?if_type_id=".$_POST['if_type_id']."';
							</script>";
					}
	
	
			} elseif (isset($_POST['create'])) {

				$options = array('function_type' => 'add', 'variable' => "".$_POST['if_type_id']."");
				$iftypefeatureform = new Thelist_Inventoryform_iftypefeature($options);
					
				if ($iftypefeatureform->isValid($_POST)) {

					$interface_type = new Thelist_Model_interfacetype($_POST['if_type_id']);
					$interface_type->map_if_type_feature($_POST['if_feature_id'], $_POST['if_type_feature_value']);

					echo 	"<script>
								window.close();
								window.opener.location.href='/inventory/editiftype/?if_type_id=".$_POST['if_type_id']."';
								</script>";	
				}
	
			}
		}
	
	}
	

	
	public function addactiveequipmentAction()
	{

		$this->_helper->layout->disableLayout();
		
		if (!$this->getRequest()->isPost()) {
		
			$options = array('function_type' => 'add');
		
			$addactiveequipmenttoinventoryform = new Thelist_Inventoryform_addactiveequipmenttoinventoryform($options);
			$addactiveequipmenttoinventoryform->setAction('/inventory/addactiveequipmenttoinventory');
			$addactiveequipmenttoinventoryform->setMethod('post');
			$this->view->addactiveequipmenttoinventoryform=$addactiveequipmenttoinventoryform;
		
			//submitting a new regex
		} elseif ($this->getRequest()->isPost()) {

				
			
			$options = array('function_type' => 'add');
			$addactiveequipmenttoinventoryform = new Thelist_Inventoryform_addactiveequipmenttoinventoryform($options);
			$addactiveequipmenttoinventoryform->setAction('/inventory/addactiveequipmenttoinventory');
			$addactiveequipmenttoinventoryform->setMethod('post');
			$this->view->addactiveequipmenttoinventoryform=$addactiveequipmenttoinventoryform;
				
			if ($addactiveequipmenttoinventoryform->isValid($_POST)) {

				$credential = new Thelist_Model_deviceauthenticationcredential();
				
				if(isset($_POST['username'])){
					$credential->set_device_user_name($_POST['username']);	
				}
				if(isset($_POST['password'])){
					$credential->set_device_password($_POST['password']);
				}
				if(isset($_POST['enablepassword'])){
					$credential->set_device_enablepassword($_POST['enablepassword']);
				}
				
				if(isset($_POST['api_id'])){				
					
					if ($_POST['api_id'] != 0) {
						$credential->set_api_id($_POST['api_id']);
					} else {
						throw new exception('you must select an api', 15700);
					}

				} else {
					throw new exception('you must select an api', 15701);
				}

				$device = new Thelist_Model_device($_POST['fqdn'], $credential);
			
				$device_type = $device->get_device_type();
				
				if ($device_type == 'cisco') {
					
					$get_equipment				= new Thelist_Cisco_command_getequipment($device);
					$equipment = $get_equipment->get_equipment();
					$equipment->backup_device();
					$equipment->update_static_interfaces();
					
				} elseif ($device_type == 'routeros') {
					
					$get_equipment				= new Thelist_Routeros_command_getequipment($device);
					$equipment = $get_equipment->get_equipment();
					
					$equipment->backup_device();
				
				} elseif ($device_type == 'bairos') {
					
					$get_equipment				= new Thelist_Bairos_command_getequipment($device);
					$equipment = $get_equipment->get_equipment();
					$equipment->backup_device();
					$equipment->update_static_interfaces();
					
				} elseif ($device_type == 'directvstb') {
					
					$get_equipment				= new Thelist_Directvstb_command_getequipment($device);
					$equipment = $get_equipment->get_equipment();
					$equipment->update_static_interfaces();
				
				} else {
					throw new exception("unknown device type ".$device_type." ", 15702);
				}
				
				if ($equipment->get_interfaces() != null) {
					foreach ($equipment->get_interfaces() as $interface) {
						$interface->update_default_interface_configurations();
					}
				}
				
				$inventory_obj			= new Thelist_Model_inventory();
				$unit_obj = new Thelist_Model_unit($_POST['unit_id']);
				
				$inventory_obj->map_equipment_to_unit($unit_obj, $equipment, $_POST['is_permanent_installation'], true);
				
				echo "	<script>
 						window.close();
 						window.opener.location.href='/equipmentconfiguration/manualconfigureequipment?eq_id=".$equipment->get_eq_id()."';
 						</script>
					";	

			}
		}
	}
	
 	public function equipmentininventoryAction()
 	{

 	$sql = "SELECT DISTINCT(e.eq_type_id) FROM equipments e
 			INNER JOIN equipment_types et ON et.eq_type_id=e.eq_type_id
 			ORDER BY et.eq_manufacturer DESC, et.eq_model_name DESC
			";
 	
		$equipment_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		$this->view->placeholder('equipment_type_inventory_table')
		->append("<tr class='header'>
						<td class='display' style='width: 100px'>Show Equipment</td>
						<td class='display' style='width: 200px'>Equipment Manufacturer</td>
						<td class='display' style='width: 200px'>Equipment Model</td>
				</tr>");
		
		foreach($equipment_types as $equipment_type){
			
			$eq_type_obj = new Thelist_Model_equipmenttype($equipment_type['eq_type_id']);
			
			$this->view->placeholder('equipment_type_inventory_table')
			->append("<tr>
						<td class='display'><a href='/inventory/showinventoryequipment?eq_type_id=".$eq_type_obj->get_eq_type_id()."' >Show Inventory</a></td>
						<td class='display'>".$eq_type_obj->get_eq_manufacturer()."</td>
						<td class='display'>".$eq_type_obj->get_eq_model_name()."</td>
						</tr>");

			}

 	}
 	
 	public function configurationsAction()
 	{
 		$html_table  = new Thelist_Html_element_html_table();
 		
 		$this->view->configurations_table 		= $html_table->configurations_table('conf_name ASC');
 		$this->view->configurations_menu_table	= $html_table->configurations_menu_table();
 		
 	}
 	
 	public function addconfigAction()
 	{
 	
 		$this->_helper->layout->disableLayout();
 	
 		//form for adding new option map
 		if (!isset($_GET['conf_id']) && !isset($_POST['create'])) {
 				
 			$options = array('function_type' => 'add');
 	
 			$addconfigform = new Thelist_Inventoryform_addconfig($options);
 			$addconfigform->setAction('/inventory/addconfig');
 			$addconfigform->setMethod('post');
 			$this->view->addconfigform=$addconfigform;
 				
 			//submitting a new function
 		} else if($this->getRequest()->isPost() && isset($_POST['create'])) {
 	
 			$options = array('function_type' => 'add');
 	
 			$addconfigform = new Thelist_Inventoryform_addconfig($options);
 			$addconfigform->setAction('/inventory/addconfig');
 			$addconfigform->setMethod('post');
 			$this->view->addconfigform=$addconfigform;
 				
 			if ($addconfigform->isValid($_POST) && isset($_POST['create'])) {

 				$data = array(
 	
 									'conf_name'					=>  $_POST['conf_name'],
 									'conf_desc'					=>  $_POST['conf_desc'],
 									'set_device_function' 		=>	$_POST['device_function_id'],  
 	
 				);
 	
				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);
 	
 				$new_config = Zend_Registry::get('database')->insert_single_row('configurations', $data, $class, $method);
 	
 				echo "	<script>
 						window.close();
 						window.opener.location.href='/inventory/showconfiguration?conf_id=".$new_config."';
 						</script>";		
 	
 			}
 	
 		}
 	
 	}
 	
 	public function addconfmapAction()
 	{
 	
 		$this->_helper->layout->disableLayout();
 	
 		//form for adding new option map
 		if (!isset($_POST['create'])) {
 				
 			$options = array('function_type' => 'add', 'if_type_id' => "".$_GET['if_type_id']."");
 	
 			$addconfmapform = new Thelist_Inventoryform_addconfmap($options);
 			$addconfmapform->setAction('/inventory/addconfmap');
 			$addconfmapform->setMethod('post');
 			$this->view->addconfmapform=$addconfmapform;
 				
 			//submitting a new function
 		} else if($this->getRequest()->isPost() && isset($_POST['create'])) {
 	
 			$options = array('function_type' => 'add', 'if_type_id' => "".$_POST['if_type_id']."");
 	
 			$addconfmapform = new Thelist_Inventoryform_addconfmap($options);
 			$addconfmapform->setAction('/inventory/addconfmap');
 			$addconfmapform->setMethod('post');
 			$this->view->addconfmapform=$addconfmapform;
 				
 			if ($addconfmapform->isValid($_POST) && isset($_POST['create']) && isset($_POST['if_type_id'])) {

 				$sql = "SELECT COUNT(conf_if_type_map_id) FROM configuration_interface_type_mapping
 						WHERE eq_type_software_map_id='".$_POST['eq_type_software_map_id']."'
 						AND conf_id='".$_POST['conf_id']."'
 						AND if_type_id='".$_POST['if_type_id']."'
 						";
 				$exists = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
 				
 				if ($exists > 0) {
 					
 					throw new exception('this config is already mapped to this softwareversions and interface type, you cant map a config more than once');
 					
 				}
 				
 				$data = array(
 	
 	 									'if_type_id'					=>  $_POST['if_type_id'],
 	 									'eq_type_software_map_id'		=>  $_POST['eq_type_software_map_id'],
 	 									'conf_id' 						=>	$_POST['conf_id'],
 										'conf_value_id'					=>  '0',
 				 	 					'conf_if_value_datatype' 		=>	$_POST['conf_if_value_datatype'],  
 	
 				);
 	
 				$trace 		= debug_backtrace();
 				$method 	= $trace[0]["function"];
 				$class		= get_class($this);
 	
 				$new_if_type_conf_map = Zend_Registry::get('database')->insert_single_row('configuration_interface_type_mapping', $data, $class, $method);
 				
 				$if_type_conf_map_obj = new thelist_model_configuration_interface_types($new_if_type_conf_map);
 				
 				$new_conf_value = $if_type_conf_map_obj->create_conf_value('default', 'changeme', '0', '0');
 	
 				$if_type_conf_map_obj->set_conf_default_value($new_conf_value->get_conf_value_id());
 				
 				echo "	<script>
 	 					window.close();
 	 					window.opener.location.href='/inventory/editiftype?if_type_id=".$_POST['if_type_id']."';
 	 					</script>";		
 	
 			}
 	
 		}
 	
 	}
 	
 	public function showconfigurationAction()
 	{
 
 			
 		$configuration_obj = new Thelist_model_configuration($_GET['conf_id']);
 		$html_table  = new Thelist_Html_element_html_table();

	 		if (isset($_POST['save'])) {
	 			
	 			$configuration_obj->set_set_device_function($_POST['device_function_id']);
	 			$configuration_obj->set_conf_name($_POST['conf_name']);
	 			$configuration_obj->set_conf_desc($_POST['conf_desc']);
	 			
	 			
	 			
	 		}
	 	$html_table  = new Thelist_Html_element_html_table();
	 	$this->view->configurations_menu_table	= $html_table->configurations_menu_table();
 		
 		$this->view->conf_name 				= $configuration_obj->get_conf_name();
 		$this->view->conf_desc 				= $configuration_obj->get_conf_desc();
 		$this->view->device_function_dd		= $html_table->device_function_dd($configuration_obj->get_set_device_function());
 		
 		
 		

 	}
 	
 	public function showinventoryequipmentAction()
 	{
 		if (isset($_GET['eq_type_id'])) {
 			
 			$eq_type_obj = new Thelist_Model_equipmenttype($_GET['eq_type_id']);
 			
 			$this->view->eq_manufacturer = $eq_type_obj->get_eq_manufacturer();
 			$this->view->eq_model_name = $eq_type_obj->get_eq_model_name();
 			
 			$sql = "SELECT e.eq_id, po.po_number, po.po_id FROM equipments e
 					LEFT OUTER JOIN purchase_order_items poi ON poi.po_item_id=e.po_item_id
 					LEFT OUTER JOIN purchase_orders po ON po.po_id=poi.po_id
 			 	 	WHERE e.eq_type_id='".$_GET['eq_type_id']."'
 			 	 	ORDER BY e.eq_id DESC
 			 		";
 			$equipments = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
 			
 			$this->view->placeholder('equipment_inventory_table')
 			->append("<tr class='header'>
 			 							<td class='display' style='width: 100px'>Show EQ</td>
 			 							<td class='display' style='width: 100px'>Show Master EQ</td>
 			 							<td class='display' style='width: 200px'>Serial Number</td>
 			 							<td class='display' style='width: 200px'>FQDN</td>
 			 							<td class='display' style='width: 50px'>From PO</td>
 			 					</tr>");
 			
 			foreach($equipments as $equipment){
 					
 				$eq = new Thelist_Model_equipments($equipment['eq_id']);
 					
 				$this->view->placeholder('equipment_inventory_table')
 				->append("<tr><td class='display'><a href='/equipmentconfiguration/manualconfigureequipment?eq_id=".$eq->get_eq_id()."'>Edit</a>");
 				
 				$this->view->placeholder('equipment_inventory_table')
 				->append(" <input type='button' class='button' name='manage_equipment_single_page' eq_id='".$eq->get_eq_id()."' id='manage_equipment_single_page' value='New'></input></td>");
 				
 				if ($eq->get_eq_master_id() != null) {
 					
 					$this->view->placeholder('equipment_inventory_table')
 					->append("<td class='display'><a href='/equipmentconfiguration/manualconfigureequipment?eq_id=".$eq->get_eq_master_id()."'>Edit Master</a></td>");
 					
 				} else {
 					
 					$this->view->placeholder('equipment_inventory_table')
 					->append("<td class='display'>No Master</td>");
 					
 				}
 				
 			 	$this->view->placeholder('equipment_inventory_table')
 				->append("					
 										<td class='display'>".$eq->get_eq_serial_number()."</td>
 			 							<td class='display'>".$eq->get_eq_fqdn()."</td>
 			 							<td class='display'><a href='/purchasing/edit/?po_id=".$equipment['po_id']."' >".$equipment['po_number']."</a></td>
 			 							</tr>");
 			
 			}

 		}

 	}
		
	public function receiveequipmentAction()
	{
		$barcode = new Thelist_Utility_barcodes();
		if(isset($_GET['po_id'])){

			$purchase_order = new Thelist_Model_purchase_orders($_GET['po_id']);
			
			if ($purchase_order->get_po_lock() == 1) {

			$po_items = $purchase_order->get_po_items();
			
			$po_item_list = "<table class='none' style='width:1100px;left:0px'>";
			$po_item_list .= "<tr><td class='display' width='200'><b>Item</b></td><td width='50' class='display'><b>Quantity</b></td><td width='50' class='display'><b>Quantity Received</b></td><td width='50' class='display'><b>Quantity Canceled</b></td><td width='50' class='display'><b>Remaining</b></td><td class='none' width='200'></td><td class='display' width='120'><b>Barcode</b></td></tr>";
			
				if(is_array($po_items))
				{
					foreach($po_items as $po_item){
			
						$item_eq_type 	= Zend_Registry::get('database')->get_Thelist_Model_equipmenttype()->fetchRow("eq_type_id='".$po_item->get_eq_type_id()."'");

						$eq_type = new Thelist_Model_equipmenttype($po_item->get_eq_type_id());
					
						$barcode_id = "belair_receiving_".$po_item->get_po_item_id();
					
						if ($po_item->get_remaining_amount() == 0) {
						
							$row_color = "#66FF33";
						
						} else {
						
							$row_color = "#FFFFFF";
							
						}
					
						$po_item_list .="
									 <tr>
										<td class='display' bgcolor='".$row_color."' height='70'>".$eq_type->get_eq_type_friendly_name()."</td>
									 	<td class='display' bgcolor='".$row_color."' height='70'>".$po_item->get_quantity()."</td>
									 	<td class='display' bgcolor='".$row_color."' height='70'>".$po_item->get_amount_received()."</td>
									 	<td class='display' bgcolor='".$row_color."' height='70'>".$po_item->get_canceled_amount()."</td>
									 	<td class='display' bgcolor='".$row_color."' height='70'>".$po_item->get_remaining_amount()."</td>
									 	<td class='none' bgcolor='".$row_color."' height='70'></td>
									 	<td class='none' bgcolor='".$row_color."' height='70'><img src='http://".$_SERVER["SERVER_NAME"]."/app_file_store/barcodes/".$barcode->render_barcode($barcode_id)."'/></td>
									 </tr>";
						
													 				
					
					}
			
					$po_item_list .='</table>';
					//return the list of items
					$this->view->po_item_list = $po_item_list;
			
				}

			} else {
				
				echo '<center><br><br><h2>This po_id is not yet a PO, Please dont try to circumvent the system</h2></center> ';
				die;
				
			}
		}
	}
	
	public function receiveequipmentitemAction()
	{
		$this->_helper->layout->disableLayout();
		if (isset($_POST['done'])) {
			
			echo "<script>
					window.close();
					  window.opener.location.href='/inventory/receiveequipment?po_id=".$_POST['po_id']."';
					
				</script>";	
			
			
		}
		
		//if serialized
		if(isset($_POST['eq_serial_number']) && !isset($_POST['done']) && $_POST['eq_serial_number'] != ''){
			
			$purchase_order = new Thelist_Model_purchase_orders($_POST['po_id']);
			preg_match("/belair_receiving_([0-9]+)/", $_POST['receive_barcode'], $matches);
			$po_item = $purchase_order->get_po_item($matches['1']);
			
		
			
		//validate that serial has correct format
			$eq_type = new Thelist_Model_equipmenttype($po_item->get_eq_type_id());
			
			if ($eq_type->validate_serial_format($_POST['eq_serial_number']) == false) {
					
					echo '<center><H2>This serial number does not match the correct format</H2></center> ';
					die;
			}

			
			//check that we need to insert more
			if ($po_item->get_remaining_amount() > 0) {
				
				$inventory_obj			= new Thelist_Model_inventory();
				
				$added_status = 	$inventory_obj->set_new_inv_eq($po_item->get_eq_type_id(), $_POST['eq_serial_number'], $po_item->get_po_item_id());
				
				
				
				if ($added_status == false) {
					
					echo '<center><H2>This item is already in inventory</H2></center> ';
					
				}else{
					
				}

			} else {
				
				echo '<center><H2>We have received the amount ordered, this item was not added</H2></center> ';
				
			}

		}
		if(isset($_POST['amount_received']) && !isset($_POST['done']))
		{	  //not serialized
			
			$purchase_order = new Thelist_Model_purchase_orders($_POST['po_id']);
			preg_match("/belair_receiving_([0-9]+)/", $_POST['receive_barcode'], $matches);
			$po_item = $purchase_order->get_po_item($matches['1']);
				
			if ($_POST['amount_received'] != '') {

				$adding = $po_item->add_to_not_serialized_amount_received($_POST['amount_received']);
				
				if ($adding == false) {
					
					echo '<center><br><br><H2>You entered an amount higher than we are expecting, build logic to handle this do not go back it triggers another add</H2></center> ';
					die;
					
				}
				
		}

		}
		
		//if 
		if (isset($_POST['amount_canceled'])) {
			
			if ($_POST['amount_canceled'] >= 0) {
			
				$set_canceled = $po_item->set_canceled_amount($_POST['amount_canceled']);
			
				if ($set_canceled == false) {
			
					echo '<center><br><br><H2>You entered a CANCELED amount higher than the total we are expecting, build logic to handle this do not go back it triggers another add</H2></center> ';
					die;
			
				}
			
			}

		}
		
		
		//items for page
		if(isset($_GET['po_id']) && isset($_GET['receive_barcode'])){
	
			$purchase_order = new Thelist_Model_purchase_orders($_GET['po_id']);
				
			if ($purchase_order->get_po_lock() == 1) {
				
				preg_match("/belair_receiving_([0-9]+)/", $_GET['receive_barcode'], $matches);
	
				$po_item = $purchase_order->get_po_item($matches['1']);
				
				$eq_type = new Thelist_Model_equipmenttype($po_item->get_eq_type_id());
					
				if ($po_item->get_item_serialized() == true) {
					
					$this->view->serialized = true;
					$this->view->eq_quantity = $po_item->get_quantity();
					$this->view->eq_remaining_amount = $po_item->get_remaining_amount();
					$this->view->eq_canceled_amount = $po_item->get_canceled_amount();
					$this->view->eq_friendly_name = $eq_type->get_eq_type_friendly_name();

					
				} else if ($po_item->get_item_serialized() == false) {
					
					$this->view->serialized = false;
					$this->view->eq_quantity = $po_item->get_quantity();
					$this->view->eq_remaining_amount = $po_item->get_remaining_amount();
					$this->view->eq_canceled_amount = $po_item->get_canceled_amount();
					$this->view->eq_friendly_name = $eq_type->get_eq_type_friendly_name();

				}
	
			} else {
	
				echo '<center><br><br><H2>This po_id is not yet a PO, Please dont try to circumvent the system</H2></center> ';
				die;
	
			}


		}
	}

	public function eqtypesAction()
	{
		$sql = "SELECT * FROM equipment_types 
				WHERE eq_type_active='1'
				ORDER BY eq_manufacturer
			   ";
		$eq_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		$this->view->placeholder('eq_types_table')
		->append("<table class='display' style='width:900px;left:250px'>
					<tr>
						<td class='display' style='width: 100px'>Model</td>
						<td class='display' style='width: 200px'>Manufacturer</td>
						<td class='display' style='width: 200px'>Description</td>
					</tr>");
		
		foreach($eq_types as $eq_type){
			$this->view->placeholder('eq_types_table')
			->append("<tr>
						<td class='display'><a href='/inventory/editeqtype/?eq_type_id=".$eq_type['eq_type_id']."' >".$eq_type['eq_model_name']."</a></td>
						<td class='display'>".$eq_type['eq_manufacturer']."</td>
						<td class='display'>".$eq_type['eq_type_desc']."</td>
					 </tr>");
		}
	}
	
	public function iftypesAction()
	{
		$sql = "SELECT * FROM interface_types
				ORDER BY if_type DESC, if_type_name DESC
				";
		$if_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		$this->view->placeholder('if_types_table')
		->append("<table class='display' style='width:900px;left:250px'>
							<tr>
								<td class='display' style='width: 300px'>Type Name</td>
								<td class='display' style='width: 100px'>Type</td>
							</tr>");
		
		foreach($if_types as $if_type){
			$this->view->placeholder('if_types_table')
			->append("<tr>
								<td class='display'><a href='/inventory/editiftype/?if_type_id=".$if_type['if_type_id']."' >".$if_type['if_type_name']."</a></td>
								<td class='display'>".$if_type['if_type']."</td>
							 </tr>");
		}
	
	}
	
	public function editiftypeAction()
	{
		if (isset($_GET['if_type_id']) && !$this->getRequest()->isPost()) {
			
			$if_type = new Thelist_Model_interfacetype($_GET['if_type_id']);
			
			$this->view->if_type_name			=	$if_type->get_if_type_name();
			$this->view->if_type 				=	$if_type->get_if_type();
			
			$if_features = $if_type->get_if_type_mapped_features();
			
			$this->view->if_type_features_list.="	<tr>
													<td colspan='2' align='left'><b>Interface Type Features:</b></td>
													</tr>
													<tr class='header'>
													<td class='display'>Edit</td>
													<td class='display'>Feature Name:</td>
													<td class='display'>Feature Value:</td>
													</tr>";
			
			
			if($if_features != null){
			
				foreach($if_features as $if_feature){
			
					$this->view->if_type_features_list		.= "<tr><td class='display'><input class='button' type='button' id='editiftypefeature' if_type_feature_map_id='".$if_feature->get_mapped_if_feature_map_id()."' value='Edit'></input></td>";
					$this->view->if_type_features_list		.= "<td class='display'>".$if_feature->get_if_feature_name()."</td>";
			
					if ($if_feature->get_mapped_if_feature_value() != null) {
							
						$this->view->if_type_features_list.="<td class='display'>".$if_feature->get_mapped_if_feature_value()."</td>";
			
					} else {
			
						$this->view->if_type_features_list.="<td class='display'>Passthrough</td>";
					}
				}
			} 

		} elseif($this->getRequest()->isPost()) {

			$if_type = new Thelist_Model_interfacetype($_POST['if_type_id']);
			$if_type->set_if_type_name($_POST['if_type_name']);
			$if_type->set_if_type_type($_POST['if_type']);
						
		}
	}
	
	
	public function confdatatypeAction()
	{
		$this->_helper->layout->disableLayout();
	
		if (isset($_GET['if_type_id']) || isset($_POST['if_type_id'])) {
			
			if(!$this->getRequest()->isPost()){
			
				$options = array('function_type' => 'edit', 'variable' => "".$_GET['conf_if_type_map_id']."", 'if_type_id' => "".$_GET['if_type_id']."");
				$confdatatypeform = new Thelist_Inventoryform_confdatatype($options);
				$confdatatypeform->setAction('/inventory/confdatatype');
				$confdatatypeform->setMethod('post');
				$this->view->confdatatypeform=$confdatatypeform;
			
			} elseif ($this->getRequest()->isPost()){
				
				$options = array('function_type' => 'edit', 'variable' => "".$_POST['conf_if_type_map_id']."", 'if_type_id' => "".$_POST['if_type_id']."");
				$confdatatypeform = new Thelist_Inventoryform_confdatatype($options);
				$confdatatypeform->setAction('/inventory/confdatatype');
				$confdatatypeform->setMethod('post');
				$this->view->confdatatypeform=$confdatatypeform;
			
			if($confdatatypeform->isValid($_POST)){

					if (isset($_POST['edit'])) {
					
						$configurationinterfacetype =  new thelist_model_configuration_interface_types($_POST['conf_if_type_map_id']);
						$configurationinterfacetype->set_conf_if_value_datatype($_POST['datatype']);
						
						echo "	<script>
								window.close();
								window.opener.location.href='/inventory/editiftype/?if_type_id=".$_POST['if_type_id']."';
								</script>";
						
					}
				}
			}
			
			
		} elseif (isset($_GET['eq_type_id']) || isset($_POST['eq_type_id'])) {
			
			
			//make config for equipmnent
			
			
		}
	}
	
	public function confoptionAction()
	{
		$this->_helper->layout->disableLayout();

		if (isset($_GET['if_type_id']) || isset($_POST['conf_if_type_map_id'])) {
				
			if(!$this->getRequest()->isPost() && isset($_GET['conf_value_id'])){
					
				$options = array('function_type' => 'edit', 'variable' => "".$_GET['conf_if_type_map_id']."", 'if_type_id' => "".$_GET['if_type_id']."",  'conf_value_id' => "".$_GET['conf_value_id']."");
				
				$confoptionform = new Thelist_Inventoryform_confoption($options);
				$confoptionform->setAction('/inventory/confoption');
				$confoptionform->setMethod('post');
				$this->view->confoptionform=$confoptionform;
					
			} elseif (!$this->getRequest()->isPost() && !isset($_GET['conf_value_id']) && isset($_GET['conf_if_type_map_id'])) {

				$options = array('function_type' => 'add', 'variable' => "".$_GET['conf_if_type_map_id']."", 'if_type_id' => "".$_GET['if_type_id']."");
				$confoptionform = new Thelist_Inventoryform_confoption($options);
				$confoptionform->setAction('/inventory/confoption');
				$confoptionform->setMethod('post');
				$this->view->confoptionform=$confoptionform;

			} elseif ($this->getRequest()->isPost() && (isset($_POST['edit']) || isset($_POST['delete']))) {
				
				$options = array('function_type' => 'edit', 'variable' => "".$_POST['conf_if_type_map_id']."", 'if_type_id' => "".$_POST['if_type_id']."",  'conf_value_id' => "".$_POST['conf_value_id']."");
				$confoptionform = new Thelist_Inventoryform_confoption($options);
				$confoptionform->setAction('/inventory/confoption');
				$confoptionform->setMethod('post');
				$this->view->confoptionform=$confoptionform;
					
				if($confoptionform->isValid($_POST)){

					if (isset($_POST['edit'])) {
							
						$configurationinterfacetype =  new thelist_model_configuration_interface_types($_POST['conf_if_type_map_id']);
						
						if ($_POST['make_default'] != '0') {
							
							$configurationinterfacetype->set_conf_default_value($_POST['conf_value_id']);
						}
						
						$configurationinterfacetype->set_conf_value($_POST['conf_value_id'], $_POST['conf_value']);
						
						$configurationinterfacetype->get_conf_value($_POST['conf_value_id'])->set_using_unique_random_word_value($_POST['unique_random_word_value']);
						$configurationinterfacetype->get_conf_value($_POST['conf_value_id'])->set_using_random_value($_POST['random_value']);
						$configurationinterfacetype->get_conf_value($_POST['conf_value_id'])->set_conf_value_friendly_name($_POST['conf_value_friendly_name']);
						
	
						echo "	<script>
								window.close();
								window.opener.location.href='/inventory/editiftype/?if_type_id=".$_POST['if_type_id']."';
								</script>";
	
					} elseif (isset($_POST['delete'])) {

						$configurationinterfacetype =  new thelist_model_configuration_interface_types($_POST['conf_if_type_map_id']);
					
						if ($configurationinterfacetype->get_conf_default_value()->get_conf_value_id() == $_POST['conf_value_id']) {
							
							throw new exception('you cannot delete the default config value, first assign another to default and then remove');
							
						} else {
							
							$trace 		= debug_backtrace();
							$method 	= $trace[0]["function"];
							$class		= get_class($this);
							Zend_Registry::get('database')->delete_single_row($_POST['conf_value_id'], 'configuration_values', $class, $method);
							
						}
					
						echo "	<script>
								window.close();
								window.opener.location.href='/inventory/editiftype/?if_type_id=".$_POST['if_type_id']."';
								</script>";
					
					} 
					
				}
			} elseif ($this->getRequest()->isPost() && isset($_POST['create'])) {
				
				$options = array('function_type' => 'add', 'variable' => "".$_POST['conf_if_type_map_id']."", 'if_type_id' => "".$_POST['if_type_id']."");
				$confoptionform = new Thelist_Inventoryform_confoption($options);
				$confoptionform->setAction('/inventory/confoption');
				$confoptionform->setMethod('post');
				$this->view->confoptionform=$confoptionform;
						
				
				if($confoptionform->isValid($_POST)){
					
					if (isset($_POST['create'])) {
						
						$configurationinterfacetype =  new thelist_model_configuration_interface_types($_POST['conf_if_type_map_id']);
						
						$new_conf_value_obj = $configurationinterfacetype->create_conf_value($_POST['conf_value'], $_POST['conf_value_friendly_name'], $_POST['unique_random_word_value'], $_POST['random_value']);
												
						if ($_POST['make_default'] != '0') {
								
							$configurationinterfacetype->set_conf_default_value($new_conf_value_obj->get_conf_value_id());
						}
						
						echo "	<script>
								window.close();
								window.opener.location.href='/inventory/editiftype/?if_type_id=".$_POST['if_type_id']."';
								</script>";
					
				}

				
				}

			}
				
				
		} elseif (isset($_GET['eq_type_id']) || isset($_POST['eq_type_id'])) {
				
				
			//make config for equipmnent
				
			
		}
	}
	
	public function createeqtypeAction(){
		$this->_helper->layout->disableLayout();
	
		$createeqtypeform = new Thelist_Inventoryform_createeqtype();
		$createeqtypeform->setAction('/inventory/createeqtype');
		$createeqtypeform->setMethod('post');
		$this->view->createeqtypeform=$createeqtypeform;
	
		if($this->getRequest()->isPost()){
			if ($createeqtypeform->isValid($_POST)) {
				// it's valid
	
				$data = array(
									'eq_model_name'			=>  $_POST['eq_model_name'],
									'eq_manufacturer'		=>  $_POST['eq_manufacturer'],
									'eq_type_name' 			=>	$_POST['eq_type_name'],  
									'eq_type_desc'			=>	$_POST['eq_type_desc'],
	
				);

				$eq_type_id = Zend_Registry::get('database')->get_Thelist_Model_equipmenttype()->insert($data);

				echo "	<script>
						window.close();
						window.opener.location.href='/inventory/eqtypes';
						</script>";	
	
	
			}
		}
	}
	
	public function eqtypesoftwaremapAction(){
		
		$this->_helper->layout->disableLayout();
	
		if($this->getRequest()->isPost()){
		$options = array('function_type' => 'add', 'variable' => "".$_POST['device_function_id']."");
		$eqtypesoftwaremapform = new Thelist_Inventoryform_eqtypesoftwaremap($options);
		$eqtypesoftwaremapform->setAction('/inventory/eqtypesoftwaremap');
		$eqtypesoftwaremapform->setMethod('post');
		$this->view->eqtypesoftwaremapform=$eqtypesoftwaremapform;
		
		if($this->getRequest()->isPost()){
			if ($eqtypesoftwaremapform->isValid($_POST)) {

				//get eq_type - software map id.
				$eq_type_obj = new Thelist_Model_equipmenttype($_POST['eq_type_id']);
				
				$eq_type_software_map_id = $eq_type_obj->map_eq_type_to_software($_POST['software_package_id']);
		
				$device_function_obj = new Thelist_Model_devicefunction($_POST['device_function_id']);
				$device_function_map_id = $device_function_obj->map_device_function_to_eq_type_software($eq_type_software_map_id);

				echo "	<script>
						window.close();
						window.opener.location.href='/equipmentconfiguration/editdevicefunctionmapping/?device_function_map_id=".$device_function_map_id."';
						</script>";
		
		
			}
		}
		//if this is just bringing up the form to create a new map
		} else {
			
			$options = array('function_type' => 'add', 'variable' => "".$_GET['device_function_id']."");
			$eqtypesoftwaremapform = new Thelist_Inventoryform_eqtypesoftwaremap($options);
			$eqtypesoftwaremapform->setAction('/inventory/eqtypesoftwaremap');
			$eqtypesoftwaremapform->setMethod('post');
			$this->view->eqtypesoftwaremapform=$eqtypesoftwaremapform;
			
			
		}


	}
	
	public function addstaticiftypeAction()
	{
		$this->_helper->layout->disableLayout();

		if (isset($_GET['eq_type_id'])) {
			
			$createstaticifform = new Thelist_Inventoryform_createstaticif('add', $_GET['eq_type_id']);
			$createstaticifform->setAction('/inventory/addstaticiftype');
			$createstaticifform->setMethod('post');
			$this->view->createstaticifform=$createstaticifform;
			
		} else if($this->getRequest()->isPost()){
			
			$createstaticifform = new Thelist_Inventoryform_createstaticif('add', $_POST['eq_type_id']);
			$createstaticifform->setAction('/inventory/addstaticiftype');
			$createstaticifform->setMethod('post');
			$this->view->createstaticifform=$createstaticifform;
			
			if ($createstaticifform->isValid($_POST)) {
				
				$sql = "SELECT * FROM  equipment_types
						WHERE eq_type_id='".$_POST['eq_type_id']."'
						";
				
				$static_if_detail = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
				
				if ($static_if_detail['0']['eq_type_protected'] == 1) {
						
					echo '<center><br><br><H2>This Equipment Type is protected and cannot be changed</H2></center> ';
					die;
						
				}
				// it's valid
				$data = array(
										'eq_type_id'			=>  $_POST['eq_type_id'],
										'if_index_number'		=>  $_POST['if_index_number'],
										'if_type_id' 			=>	$_POST['if_type_id'],
										'if_default_name' 		=>	$_POST['if_default_name'],
	
				);
	
				$static_if_type_id = Zend_Registry::get('database')->get_static_if_types()->insert($data);
	
				echo "<script>
								window.close();
								  window.opener.location.href='/inventory/editeqtype?eq_type_id=".$_POST['eq_type_id']."';
								
							</script>";		
	
	
			}
		}
	}
	
	public function addeqtyperegexAction()
	{
		$this->_helper->layout->disableLayout();
	
		if (isset($_GET['eq_type_id'])) {
				
			$serialregexform = new Thelist_Inventoryform_serialregex('add', $_GET['eq_type_id']);
			$serialregexform->setAction('/inventory/addeqtyperegex');
			$serialregexform->setMethod('post');
			$this->view->serialregexform=$serialregexform;
				
		} else if($this->getRequest()->isPost()){
				
			$serialregexform = new Thelist_Inventoryform_serialregex('add', $_POST['eq_type_id']);
			$serialregexform->setAction('/inventory/addeqtyperegex');
			$serialregexform->setMethod('post');
			$this->view->serialregexform=$serialregexform;
				
			if ($serialregexform->isValid($_POST)) {
	
				// it's valid
				$data = array(
					'regex'			=>  $_POST['serial_regex'],
					'eq_type_id'	=>  $_POST['eq_type_id'],
				);
	
				$static_if_type_id = Zend_Registry::get('database')->get_eq_type_serial_match()->insert($data);
	
				echo "<script>
									window.close();
									  window.opener.location.href='/inventory/editeqtype?eq_type_id=".$_POST['eq_type_id']."';
									
								</script>";		
	
	
			}
		}
	}
	
	public function editeqtyperegexAction(){
		$this->_helper->layout->disableLayout();
		
		if (isset($_GET['eq_type_serial_match_id'])) {
	
			$serialregexform = new Thelist_Inventoryform_serialregex('edit', $_GET['eq_type_serial_match_id']);
			$serialregexform->setAction('/inventory/editeqtyperegex');
			$serialregexform->setMethod('post');
			$this->view->serialregexform=$serialregexform;
	
		} else if($this->getRequest()->isPost()) {
				
			$serialregexform = new Thelist_Inventoryform_serialregex('edit', $_POST['eq_type_serial_match_id']);
			$serialregexform->setAction('/inventory/editeqtyperegex');
			$serialregexform->setMethod('post');
			$this->view->serialregexform=$serialregexform;
	
			if ($serialregexform->isValid($_POST)) {

				if (isset($_POST['delete'])) {
						
					Zend_Registry::get('database')->get_eq_type_serial_match()->delete("eq_type_serial_match_id='".$_POST['eq_type_serial_match_id']."'");
						
				} else if (isset($_POST['edit'])) {
					// it's valid
					$data = array(
	
										'regex'			=>  $_POST['serial_regex'],
					);
	
					Zend_Registry::get('database')->get_eq_type_serial_match()->update($data, "eq_type_serial_match_id='".$_POST['eq_type_serial_match_id']."'");
	
				}
	
				echo "<script>
				window.close();
				window.opener.location.href='/inventory/editeqtype?eq_type_id=".$_POST['eq_type_id']."';
				</script>";		
	
			}
		}
	}
	
	public function editstaticiftypeAction(){
		$this->_helper->layout->disableLayout();
	
		if (isset($_GET['static_if_type_id'])) {

			$createstaticifform = new Thelist_Inventoryform_createstaticif('edit', $_GET['static_if_type_id']);
			$createstaticifform->setAction('/inventory/editstaticiftype');
			$createstaticifform->setMethod('post');
			$this->view->editstaticifform=$createstaticifform;
				
		} else if($this->getRequest()->isPost()) {
			
			$createstaticifform = new Thelist_Inventoryform_createstaticif('edit', $_POST['static_if_type_id']);
			$createstaticifform->setAction('/inventory/editstaticiftype');
			$createstaticifform->setMethod('post');
			$this->view->editstaticifform=$createstaticifform;

			if ($createstaticifform->isValid($_POST)) {
				
				$sql = "SELECT * FROM static_if_types sit
						LEFT OUTER JOIN equipment_types et ON et.eq_type_id=sit.eq_type_id
						WHERE sit.static_if_type_id='".$_POST['static_if_type_id']."'
						";
				
				$static_if_detail = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);

				if ($static_if_detail['0']['eq_type_protected'] == 1) {
					
					echo '<center><br><br><H2>This Equipment Type is protected and cannot be changed</H2></center> ';
					die;
					
				}
				
				if (isset($_POST['delete'])) {
					
					Zend_Registry::get('database')->get_static_if_types()->delete("static_if_type_id='".$_POST['static_if_type_id']."'");
					
				} else if (isset($_POST['edit'])) {
				// it's valid
				$data = array(
				
											'if_index_number'		=>  $_POST['if_index_number'],
											'if_type_id' 			=>	$_POST['if_type_id'],
											'if_default_name' 			=>	$_POST['if_default_name'],
				);
	
				Zend_Registry::get('database')->get_static_if_types()->update($data, "static_if_type_id='".$_POST['static_if_type_id']."'");
	
				}

				echo "<script>
				window.close();
				window.opener.location.href='/inventory/editeqtype?eq_type_id=".$static_if_detail['0']['eq_type_id']."';
				</script>";		

			}
		}
	}
	
	public function editeqtypeAction(){
	
		$eq_type_id = $_GET['eq_type_id'];
		$eq_type = new Thelist_Model_equipmenttype($eq_type_id);
	
	
		if($this->getRequest()->isPost()){
	
			$eq_type->set_eq_model_name($_POST['eq_model_name']);
			$eq_type->set_eq_manufacturer($_POST['eq_manufacturer']);
			$eq_type->set_eq_type_name($_POST['eq_type_name']);
			$eq_type->set_eq_type_desc($_POST['eq_type_desc']);
			$eq_type->set_eq_type_friendly_name($_POST['eq_type_friendly_name']);
				
		}
	
		$this->view->eq_model_name			=	$eq_type->get_eq_model_name();
		$this->view->eq_manufacturer 		=	$eq_type->get_eq_manufacturer();
		$this->view->eq_type_name			=	$eq_type->get_eq_type_name();
		$this->view->eq_type_desc			=	$eq_type->get_eq_type_desc();
		$this->view->eq_type_friendly_name	=	$eq_type->get_eq_type_friendly_name();
		
		$static_if_types 					= $eq_type->get_static_if_types();
		
		if ($eq_type->get_eq_type_protected() == 1) {
			
			$this->view->eq_type_protected		= 'Yes';
			
		} else if ($eq_type->get_eq_type_protected() == 0) {
			
			$this->view->eq_type_protected		= 'No';
			
		}
		
		if ($eq_type->get_eq_type_serialized() == 1) {
				
			$this->view->eq_type_serialized		= 'Yes';
				
		} else if ($eq_type->get_eq_type_serialized() == 0) {
				
			$this->view->eq_type_serialized		= 'No';
				
		}
		

		if(is_array($static_if_types)){
			
			$this->view->static_if_types_list.="<table style='width:1000px'>
											<tr>
											<td colspan='3' align='left'>Static Interface Types:</td>
											</tr>
											<tr class='header'>
											<td class='display'>Interface Index Number:</td>
											<td class='display'>Type Name:</td>
											<td class='display'>Default Name:</td>
											<td class='display'>Edit</td>
											</tr>";
		
			foreach($static_if_types as $static_if_type){

				$this->view->static_if_types_list.="
											 <tr>
												<td class='display'>".$static_if_type->get_if_index_number()."</td>
											 	<td class='display'>".$static_if_type->get_if_type()->get_if_type_name()."</td>
											 	<td class='display'>".$static_if_type->get_if_default_name()."</td>
											 	<td class='display'>
											 	<input class='button' type='button' id='edit_static_if_type' static_if_type_id='".$static_if_type->get_static_if_type_id()."' value='Edit'></input>
											 	</td>
											 </tr>
											";
			
			}
			$this->view->static_if_types_list.="</table>";
		}
		
		//list of active regex
		$serial_regexs 					= $eq_type->get_serial_regex();

		if(is_array($serial_regexs)){
				
			$this->view->regex_list.="<table style='width:1000px'>
													<tr>
													<td colspan='2' align='left'>RegEx Serial Matching:</td>
													<td align='right' style='width:100px'><input class='button' type='button' id='add_regex' value='Add Regex'></input></td>
													</tr>
													<tr class='header'>
													<td class='display'>Regex Number:</td>
													<td class='display'>Regex:</td>
													<td class='display'>Edit</td>
													</tr>";
		
			foreach($serial_regexs as $serial_regex){
		
				$this->view->regex_list.="
													 <tr>
														<td class='display'>".$serial_regex['eq_type_serial_match_id']."</td>
													 	<td class='display'>".$serial_regex['regex']."</td>
													 	<td class='display'>
													 	<input class='button' type='button' id='edit_regex' eq_type_serial_match_id='".$serial_regex['eq_type_serial_match_id']."' value='Edit'></input>
													 	</td>
													 </tr>
													";
					
			}
			$this->view->regex_list.="</table>";
		}
	}
} 
?>