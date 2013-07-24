<?php

//by martin
//exception codes 1600-1699

class EquipmentconfigurationController extends Zend_Controller_Action
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
			//not a perspective controller
			$layout_manager = new Thelist_Utility_layoutmanager($this->_user_session->current_perspective, $this->_helper);
			$layout_manager->set_layout();
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
	
	public function massdefaultconfigureequipmentAction()
	{	
		$this->_helper->layout->disableLayout();
		if ($this->getRequest()->isPost()) {

			//set a high execution time, 2 hours
			ini_set('max_execution_time', 7200);
				
			
			if ($_POST['software_package_id'] != '') {
				
				//the id passed down does not reflect the correct archtechture or version we check that later
				$perliminary_sw_pkg		= new Thelist_Model_softwarepackage($_POST['software_package_id']);
				
				foreach ($_POST as $index => $value) {
					
					if ($index != 'update_selected' && $index != 'software_package_id' && $index != 'default_config_file') {

						if (is_numeric($index)) {
							
							//recreate the variable
							$eq_ids[$value['eq_id']]['eq_id'] = $value['eq_id'];
							
							if (isset($value['update'])) {
								
								if ($value['update'] == 1) {
									
									$software_pkg_to_deploy = null;
									
									try {

										$equipment_obj 			= new Thelist_Model_equipments($value['eq_id']);
										$current_software		= $equipment_obj->get_running_software_package();
										$all_available_sw_pkgs	= $equipment_obj->get_all_compatible_software_packages();
										
										if (count($all_available_sw_pkgs) > 0) {
											foreach ($all_available_sw_pkgs as $software_pkg) {
												
												//if an avaliable package has the same name and version
												if (
												$software_pkg->get_software_package_name() == $perliminary_sw_pkg->get_software_package_name() 
												&& $software_pkg->get_software_package_version() == $perliminary_sw_pkg->get_software_package_version()
												) {
													$software_pkg_to_deploy = $software_pkg;
												}
											}
										}
										
										if ($software_pkg_to_deploy != null) {
											
											if (isset($value['force'])) {
												
												if ($value['force'] == 1) {
													$downgrade = true;
												} else {
													$downgrade = false;
												}
												
											} else {
												$downgrade = false;
											}
											
											$device				= $equipment_obj->get_device();
											$device->change_running_os_package($software_pkg_to_deploy, $downgrade);
										
											//this is a mass upgrade and we are not validating if the devices come back up
											$eq_ids[$value['eq_id']]['error']			= 0;
											$eq_ids[$value['eq_id']]['new_software']	= $software_pkg_to_deploy->get_software_package_id();
											$status[$value['eq_id']]['eq_id'] 			= $value['eq_id'];

											// kill the device connection
											unset($device);
											
										} else {
											
											$eq_ids[$value['eq_id']]['error']					= 1;
											$eq_ids[$value['eq_id']]['new_software']			= 0;	
											$status[$value['eq_id']]['eq_id'] 					= $value['eq_id'];
											$status[$value['eq_id']]['software_error_reason'] 	= "Missing Correct Software Package in Repository";
										}

									} catch (Exception $e) {
											
										switch($e->getCode()) {
									
											case 1203;
											$eq_ids[$value['eq_id']]['error']					= 1;
											$eq_ids[$value['eq_id']]['new_software']			= $software_pkg_to_deploy->get_software_package_id();
											$status[$value['eq_id']]['eq_id'] 					= $value['eq_id'];
											$status[$value['eq_id']]['software_error_reason'] 	= "Device, Wrong Credentials";
											break;
											case 1202;
											$eq_ids[$value['eq_id']]['error']					= 1;
											$eq_ids[$value['eq_id']]['new_software']			= $software_pkg_to_deploy->get_software_package_id();
											$status[$value['eq_id']]['eq_id'] 					= $value['eq_id'];
											$status[$value['eq_id']]['software_error_reason']	= "Device, Host Timed Out";
											break;
											default;
											throw $e;
									
										}
									}
								}
							}
						}
					}
				}
				
				//now we validate that all devices came back up
				if (count($eq_ids) > 0) {
					
					foreach ($eq_ids as $eq_after) {
						
						if (isset($eq_after['error'])) {
						
							if ($eq_after['error'] == 0) {
								
								$device_back_online = false;
									
								$i=0;
								while ($i < 80 && $device_back_online === false) {
								
									try {
										
										$equipment_obj 			= new Thelist_Model_equipments($eq_after['eq_id']);
										
										//try and create a new device
										$device				= $equipment_obj->get_device();
										$api_back_up 		= $device->api_functional();
							
										if ($api_back_up === true) {
											//success
											$running_sw_pkg	= $device->get_running_os_package();
											
											if ($running_sw_pkg->get_software_package_id() == $eq_after['new_software']) {
		
												//update the database if the software was updated correctly
												$equipment_obj->set_current_software_package($running_sw_pkg);
												
												$status[$eq_after['eq_id']]['eq_id'] 			= $eq_after['eq_id'];
												$status[$eq_after['eq_id']]['software_status'] 	= "Successfully Changed Firmware";
												
											} else {
												$status[$eq_after['eq_id']]['software_status'] 	= "Failed To Change Firmware";
											}
											
											$device_back_online = true;
										}
										
									} catch (Exception $e) {
								
										switch($e->getCode()){
								
											case 7108;
											//connection closed, expected
											break;
											case 1202;
											//connection timed out, expected
											break;
											case 1203;
											//authentication failed, not quite expected, but if the device is in the process of shutting down we do get this error
											//even though the password is correct
											break;
											default;
											throw $e;
								
										}
									}
								
									sleep(5);
									$i++;
								}
								
								if ($device_back_online !== true) {
									$status[$eq_after['eq_id']]['software_status'] 				= "Failed To Come Back Online";
									$status[$eq_after['eq_id']]['software_error_reason']		= "Device, Host Timed Out";
								}
						
							}
						}
					}
				}
			}
			
			if (isset($_POST['default_config_file'])) {

				foreach ($_POST as $index => $value) {
						
					if ($index != 'update_selected' && $index != 'software_package_id' && $index != 'default_config_file') {
				
						if (is_numeric($index)) {
							
							$deploy_config = 'yes';
							
							//if we already tried to upload software to the device and failed no need to try again
							//however if no software was tried, then do all selected
							if (isset($eq_ids)) {
								
								//was the upload of software successful?
								if (!isset($eq_ids[$index])) {
									$deploy_config = 'no';
								}
							}
							
							if ($deploy_config == 'yes') {
								
								if (isset($value['update'])) {
					
									if ($value['update'] == 1) {
	
										try {
					
											$equipment_obj 			= new Thelist_Model_equipments($value['eq_id']);
											$device					= $equipment_obj->get_device();
											$device->reset_config($_POST['default_config_file']);
											
											$status[$value['eq_id']]['eq_id'] 					= $value['eq_id'];
											$status[$value['eq_id']]['config_status'] 			= 'Executed New Configuration';
																						
										} catch (Exception $e) {
												
											switch($e->getCode()) {
													
												case 1203;
												$eq_ids[$value['eq_id']]['error']					= 1;
												$status[$value['eq_id']]['eq_id'] 					= $value['eq_id'];
												$status[$value['eq_id']]['config_error_reason'] 	= 'Device, Wrong Credentials';
												break;
												case 1202;
												$eq_ids[$value['eq_id']]['error']			= 1;
												$status[$value['eq_id']]['eq_id'] 			= $value['eq_id'];
												$status[$value['eq_id']]['config_error_reason'] 	= 'Device, Wrong Credentials';
												break;
												default;
												throw $e;
													
											}
										}
									}
								}
							}
						}
					}
				}
				
				//we dont validate that devices come back after a config change, there is no guerentee that it will ever resurface because the config changed
			}	
		} 
		
		//get information
		if (isset($_GET['eq_ids'])) {
			
			$eq_ids = explode(',', $_GET['eq_ids']);
			
			if (isset($eq_ids['0'])) {
				
				foreach ($eq_ids as $eq_id) {
					
					$equipment_obj = new Thelist_Model_equipments($eq_id);
					
					$return_view[$eq_id]['eq_id'] 				= $equipment_obj->get_eq_id();
					$return_view[$eq_id]['fqdn'] 				= $equipment_obj->get_eq_fqdn();
					$return_view[$eq_id]['model'] 				= $equipment_obj->get_eq_type()->get_eq_model_name();
					$return_view[$eq_id]['manufacturer'] 		= $equipment_obj->get_eq_type()->get_eq_manufacturer();
					$return_view[$eq_id]['software_name']		= $equipment_obj->get_running_software_package()->get_software_package_name();
					$return_view[$eq_id]['software_version']	= $equipment_obj->get_running_software_package()->get_software_package_version();
				}
				
				//get all available software packages
				$sql = "SELECT software_package_id, software_package_name, software_package_version FROM software_packages
						GROUP BY software_package_name, software_package_version
						ORDER BY software_package_name DESC, CAST(software_package_version AS SIGNED) DESC
						";
				
				$sw_pkgs	= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
				
				if (isset($sw_pkgs['0'])) {
					
					$software_packages = '';
					
					//we are not intending on using the accual sw_package id but this way we can conway the type of software
					
					foreach ($sw_pkgs as $sw_pkg) {
						$software_packages .= "<OPTION value='".$sw_pkg['software_package_id']."'>".$sw_pkg['software_package_name']." -- ".$sw_pkg['software_package_version']."</OPTION>";
					}
					
					$this->view->new_software_versions = $software_packages;
				}
				
				//config files
				
				$config_file_names	= '<OPTION>--SELECT--</OPTION>';

				$routeros_configs	= scandir('/zend/thelist/application/configs/device_configs/routeros');
				
				if (isset($routeros_configs['0'])) {
					
					$config_file_names .= "<OPTION>--RouterOS Configs--</OPTION>";
					foreach ($routeros_configs as $routeros_config) {
						
						if (!preg_match("/^\./", $routeros_config)) {
							$config_file_names .= "<OPTION value='".$routeros_config."'>$routeros_config</OPTION>";
						}
					}
				}
				
				$bairos_configs	= scandir('/zend/thelist/application/configs/device_configs/bairos');
				
				if (isset($bairos_configs['0'])) {
						
					$config_file_names .= "<OPTION>--BAIROS Configs--</OPTION>";
					foreach ($bairos_configs as $bairos_config) {
				
						if (!preg_match("/^\./", $bairos_config)) {
							$config_file_names .= "<OPTION value='".$bairos_config."'>$bairos_config</OPTION>";
						}
					}
				}
				
				$cisco_configs	= scandir('/zend/thelist/application/configs/device_configs/cisco');
				
				if (isset($cisco_configs['0'])) {
				
					$config_file_names .= "<OPTION>--Cisco Configs--</OPTION>";
					foreach ($cisco_configs as $cisco_config) {
				
						if (!preg_match("/^\./", $cisco_config)) {
							$config_file_names .= "<OPTION value='".$cisco_config."'>$cisco_config</OPTION>";
						}
					}
				}
				
				//if this is taking place after an upgrade was issued, show the result of the update
				if (isset($status)) {

					foreach ($status as $updated_eq) {
						
						$equipment_obj = new Thelist_Model_equipments($updated_eq['eq_id']);
						
						if (isset($updated_eq['software_status'])) {
							$update_result[] = 	"Firmware Result: " . $equipment_obj->get_eq_fqdn() . ", " . $updated_eq['software_status'];
						}
						
						if (isset($updated_eq['config_status'])) {
							$update_result[] = 	"Configuration Result: " . $equipment_obj->get_eq_fqdn() . ", " . $updated_eq['config_status'];
						}
					}
					
					$this->view->update_result	= $update_result;
				}

				$this->view->new_config_file	= $config_file_names;
				$this->view->equipment 			= $return_view;
			}
		}
	}
	
	public function uploaddeviceconfigfileAction()
	{
		$this->_helper->layout->disableLayout();
		
		if ($this->getRequest()->isPost()) {

			if (isset($_FILES['deviceconfigfile']) && $_POST['device_type'] != '' && $_POST['deviceconfigfilename'] != '') {
				
				if (isset($_FILES['deviceconfigfile']['error'])) {
					
					if ($_FILES['deviceconfigfile']['error'] == 0) {
				
						try {
							
							//read the file
							$file_content	= file_get_contents($_FILES['deviceconfigfile']['tmp_name']);
							
							//should we override existing file?
							if (isset($_POST['overrideexistingfile'])) {
								$save_file	= new Thelist_Utility_savefiletoserver($_POST['deviceconfigfilename'], true);
							} else {
								$save_file	= new Thelist_Utility_savefiletoserver($_POST['deviceconfigfilename'], false);
							}

							
							$save_file->create_device_config_file_from_content($_POST['device_type'], $file_content);

						} catch (Exception $e) {
						
							switch($e->getCode()){
						
								case 21801;
								//config file for routeros, must end with extension 21801
								$error		= 'Configuration Files for Routeros must end with .rsc, yours does not, Try Again';
								break;
								
								default;
								throw $e;
						
							}
						}
					}
				}
				
				if (!isset($error)) {
					echo "<script>
					window.close();
					window.opener.location.reload();
					</script>";
				}
				
			} else {
				$error		= 'Please select a device type, name the file and upload';
			}
			
		} 

		if (isset($error)) {
			 $this->view->error = $error;
		}

		$sql = "SELECT software_package_manufacturer, software_package_name FROM software_packages
				GROUP BY software_package_manufacturer, software_package_name
				ORDER BY software_package_manufacturer, software_package_name
				";
		$device_types	= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		if (isset($device_types['0'])) {
			$return = '';
			
			foreach ($device_types as $device_type) {
				
				$return .= "<OPTION value='".$device_type['software_package_name']."'>".$device_type['software_package_manufacturer']." - ".$device_type['software_package_name']."</OPTION>";
				
			}
			
			$this->view->devicetypes	= $return;
		}
	}
	
	public function managesoftwareAction()
	{
		$this->_helper->layout->disableLayout();
		
		if (isset($_GET['eq_id'])) {
			
			$equipment_obj = new Thelist_Model_equipments($_GET['eq_id']);
			
			$current_software = $equipment_obj->get_running_software_package();
			
			$view_return['current_name'] 			= $current_software->get_software_package_name();
			$view_return['current_architecture'] 	= $current_software->get_software_package_architecture();
			$view_return['current_version'] 		= $current_software->get_software_package_version();
			$view_return['current_manufacturer']	= $current_software->get_software_package_manufacturer();
			
			
			$available_software_pkgs = $equipment_obj->get_all_compatible_software_packages();
			
			foreach ($available_software_pkgs as $available_software_pkg) {
				
				if (!isset($view_return['available_software_versions'])) {
					$view_return['available_software_versions'] = "<OPTION value='".$available_software_pkg->get_software_package_id()."'>".$available_software_pkg->get_software_package_version()."</OPTION>";
				} else {
					$view_return['available_software_versions'] .= "<OPTION value='".$available_software_pkg->get_software_package_id()."'>".$available_software_pkg->get_software_package_version()."</OPTION>";
				}
			}
			
			//get the software history of this equipment
			
			$sql = "SELECT * FROM equipment_software_upgrades esu
					INNER JOIN software_packages sp ON sp.software_package_id=esu.software_package_id
					WHERE eq_id='".$equipment_obj->get_eq_id()."'
					ORDER BY esu.result_timestamp DESC
					";
			
			$sw_histories	= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			if (isset($sw_histories['0'])) {
				
				$time = new Thelist_Utility_time();
				
				$i=0;
				foreach ($sw_histories as $sw_history) {
					
					$view_return['sw_history'][$i]['result']								=  $sw_history['result'];
					$view_return['sw_history'][$i]['result_timestamp']						=  $time->format_date_time($sw_history['result_timestamp'], 'american');
					$view_return['sw_history'][$i]['software_package_manufacturer']			=  $sw_history['software_package_manufacturer'];
					$view_return['sw_history'][$i]['software_package_name']					=  $sw_history['software_package_name'];
					$view_return['sw_history'][$i]['software_package_architecture']			=  $sw_history['software_package_architecture'];
					$view_return['sw_history'][$i]['software_package_version']				=  $sw_history['software_package_version'];
					
					$i++;
				}

			}
			
			$this->view->software = $view_return;
		}
	
	}
	
	
	public function getallowedeqappmetricvaluesajaxAction()
	{
	
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

		if (isset($_GET['equipment_type_application_metric_id']) && isset($_GET['eq_id'])) {

			$sql2 = "SELECT * FROM equipment_type_application_metrics etam
					WHERE etam.equipment_type_application_metric_id='".$_GET['equipment_type_application_metric_id']."'
					";
			
			$app_metric_detail 	= Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql2);
			
			$metric_obj 		= new Thelist_Model_equipmentapplicationmetric($app_metric_detail['equipment_application_metric_id']);
			$valid_configs		= $metric_obj->get_valid_configuration_value_1($_GET['equipment_type_application_metric_id'], $_GET['eq_id']);

			if ($valid_configs !== false) {
		
				if ($valid_configs !== true) {
					$configs = "<OPTION value='0'>--- Select One ---</OPTION>";
									
					foreach ($valid_configs as $valid_config) {
			
						$configs .= "<OPTION value='".$valid_config."'>".$valid_config."</OPTION>";
					}
		
				} else {
					//all configs are allowed
					$configs = null;
				}
		
			} else {
				//some configs are allowed, but textbox needed
				$configs = null;
			}
		
			echo $configs;
	
		} else {
			return false;
		}
	}
	
	public function addeqapplicationmetricAction()
	{
		$this->_helper->layout->disableLayout();
	
		if (isset($_GET['equipment_application_map_id']) && !$this->getRequest()->isPost()) {
	
			$sql = "SELECT * FROM equipment_application_metrics eam
					INNER JOIN equipment_type_application_metrics etam ON etam.equipment_application_metric_id=eam.equipment_application_metric_id
					INNER JOIN eq_type_applications eta ON eta.eq_type_application_id=etam.eq_type_application_id
					INNER JOIN equipment_application_mapping eappm ON eappm.equipment_application_id=eta.equipment_application_id
					INNER JOIN equipments e ON e.eq_id=eappm.eq_id
					WHERE eappm.equipment_application_map_id='".$_GET['equipment_application_map_id']."'
					AND eta.eq_type_id=e.eq_type_id
					";
				
			$app_metrics 	= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
	
			if (isset($app_metrics['0'])) {
	
				$i=0;
				foreach($app_metrics as $app_metric) {

					$view_array[$i]['equipment_application_metric_id']			= $app_metric['equipment_application_metric_id'];
					$view_array[$i]['equipment_application_metric_name']		= $app_metric['equipment_application_metric_name'];
					$view_array[$i]['equipment_type_application_metric_id']		= $app_metric['equipment_type_application_metric_id'];
							
						$i++;
				}
			}
		
	
			if (isset($view_array)) {
				$this->view->available_metrics	= $view_array;
			} else {
				$this->view->error	= 'there are no available configs in the database';
			}
				
		} elseif ($this->getRequest()->isPost()) {
			
			$sql = "SELECT * FROM equipment_application_mapping eam
					WHERE eam.equipment_application_map_id='".$_POST['equipment_application_map_id']."' 
					";
				
			$app_detail 	= Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
			$sql = "SELECT * FROM equipment_type_application_metrics etam
					WHERE etam.equipment_type_application_metric_id='".$_POST['equipment_type_application_metric_id']."' 
					";
			
			$metric_type_detail 	= Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
			$application = new Thelist_Model_equipmentapplication($app_detail['equipment_application_id']);
			$application->fill_mapped_values($_POST['equipment_application_map_id']);
			
			//which value is set
			if ($_POST['value2'] != '') {
				$application->create_application_metric_mapping($metric_type_detail['equipment_application_metric_id'], 10, $_POST['value2'], null);
			} elseif (isset($_POST['value1'])) {
			
				if ($_POST['value1'] != '') {
					$application->create_application_metric_mapping($metric_type_detail['equipment_application_metric_id'], 10, $_POST['value1'], null);
				}
			}

			echo "<script>
			window.close();
			window.opener.location.reload();
			</script>";
		}
	}
	
	public function manageapplicationmetricsAction()
	{
		$this->_helper->layout->disableLayout();
		
		if ($this->getRequest()->isPost()) {

			$sql = 	"SELECT * FROM equipment_application_mapping
					WHERE equipment_application_map_id='".$_POST['equipment_application_map_id']."' 
					";
		
			$equipment_application_map 	= Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
			if (isset($equipment_application_map['equipment_application_id'])) {
		
				$application = new Thelist_Model_equipmentapplication($equipment_application_map['equipment_application_id']);
				$application->fill_mapped_values($_POST['equipment_application_map_id']);
					
			} else {
				throw new exception("invalid application map", 1635);
			}

			foreach($_POST as $index => $metric) {
		
				if ($index != 'equipment_application_map_id' && $index != 'just_update') {
		
					if (isset($metric['delete'])) {

						if ($metric['delete'] == 1) {
							$application->remove_metric_map($metric['equipment_application_metric_map_id']);
						}
		
					} else {
		
						$metric_obj = $application->get_metric_mapping($metric['equipment_application_metric_map_id']);
		
						$metric_obj->set_equipment_application_metric_index($metric['metric_index']);
						$metric_obj->set_equipment_application_metric_value($metric['equipment_application_metric_value']);
					}
				}
			}
		}
	
		if (!isset($_POST['just_update']) && $this->getRequest()->isPost()) {
			
			echo "<script>
			window.close();
			window.opener.location.reload();
			</script>";	
		}
		
		if (isset($_GET['equipment_application_map_id'])) {
	
			$sql = "SELECT * FROM equipment_application_mapping eam
					WHERE eam.equipment_application_map_id='".$_GET['equipment_application_map_id']."' 
					";
	
			$equipment_application_map 	= Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

			if (isset($equipment_application_map['eq_id'])) {
	
				$equipment_obj 			= new Thelist_Model_equipments($equipment_application_map['eq_id']);
				$application 			= $equipment_obj->get_application_mapping($equipment_application_map['equipment_application_map_id']);
				$eq_app_metrics			= $application->get_metric_mappings();
	
				if ($eq_app_metrics != null) {
						
					$i=0;
					foreach ($eq_app_metrics as $eq_app_metric) {
	
						$eq_metric_maps[$i]['equipment_application_metric_name']			= $eq_app_metric->get_equipment_application_metric_name();
						$eq_metric_maps[$i]['equipment_application_metric_index']			= $eq_app_metric->get_equipment_application_metric_index();
						$eq_metric_maps[$i]['equipment_application_metric_map_id']			= $eq_app_metric->get_equipment_application_metric_map_id();
						$eq_metric_maps[$i]['equipment_application_metric_value']			= $eq_app_metric->get_equipment_application_metric_value();
						$eq_metric_maps[$i]['equipment_application_metric_id']				= $eq_app_metric->get_equipment_application_metric_id();
						
						$i++;
					}
	
					$this->view->current_metrics = $eq_metric_maps;
					$this->view->eq_id = $equipment_obj->get_eq_id();
				}
	
			} else {
				throw new exception("invalid equipment application map", 1637);
			}
		}
	}
	
	public function addequipmentapplicationAction()
	{
		$this->_helper->layout->disableLayout();
	
		if (isset($_GET['eq_id']) && !$this->getRequest()->isPost()) {
	
			$sql = 	"SELECT * FROM equipment_applications ea
					INNER JOIN eq_type_applications eta ON eta.equipment_application_id=ea.equipment_application_id
					INNER JOIN equipments e ON e.eq_type_id=eta.eq_type_id
					WHERE e.eq_id='".$_GET['eq_id']."'
					";
				
			$equipment_applications 	= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
	
			if (isset($equipment_applications['0'])) {
	
				$i=0;
				foreach($equipment_applications as $equipment_application) {

					$view_array[$i]['equipment_application_id']		= $equipment_application['equipment_application_id'];
					$view_array[$i]['equipment_application_name']	= $equipment_application['equipment_application_name'];
						
					$i++;
				}
			}
	
			if (isset($view_array)) {
				$this->view->available_applications	= $view_array;
			} else {
				$this->view->error	= 'there are no available applications in the database';
			}
				
		} elseif ($this->getRequest()->isPost()) {

			if ($_POST['equipment_application_id'] != 0) {
				
				$equipment_obj 		= new Thelist_Model_equipments($_POST['eq_id']);
				$equipment_obj->create_application_mapping($_POST['equipment_application_id']);
				
			} else {
				throw new exception("you must select an application", 1635);
			}

			echo "<script>
			window.close();
			window.opener.location.reload();
			</script>";
		}
	}
	
	public function manageapplicationsAction()
	{
		$this->_helper->layout->disableLayout();
		if (isset($_GET['eq_id']) && !$this->getRequest()->isPost()) {
	
			$equipment_obj 		= new Thelist_Model_equipments($_GET['eq_id']);
			$current_applications = $equipment_obj->get_application_mappings();
	
			if ($current_applications != null) {
				$i=0;
				foreach ($current_applications as $current_application) {
						
					$current_apps[$i]['equipment_application_map_id'] 	= $current_application->get_equipment_application_map_id();
					$current_apps[$i]['equipment_application_id'] 		= $current_application->get_equipment_application_id();
					$current_apps[$i]['equipment_application_name'] 	= $current_application->get_equipment_application_name();
					$i++;
				}
	
				$this->view->applications = $current_apps;
			}
	
		} elseif ($this->getRequest()->isPost()) {

			$equipment_obj 		= new Thelist_Model_equipments($_POST['eq_id']);
				
			foreach($_POST as $index => $value) {
					
				if ($index != 'eq_id') {
						
					if (isset($value['delete'])) {
						if ($value['delete'] == 1) {
							$equipment_obj->remove_application_mapping($value['equipment_application_map_id']);
						}
					}
				}
			}
				
			echo "<script>
			window.close();
			window.opener.location.reload();
			</script>";
		}
	}
	
	public function addeqtypeapplicationallowedvalueAction()
	{
		$this->_helper->layout->disableLayout();
	
		if (isset($_GET['equipment_type_application_metric_id']) && !$this->getRequest()->isPost()) {
			//nothing, it all about adding
				
		} elseif ($this->getRequest()->isPost()) {

			$sql = "SELECT * FROM equipment_type_application_metrics
					WHERE equipment_type_application_metric_id='".$_POST['equipment_type_application_metric_id']."'
					";
	
			$equipment_type_application_metric = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

			$app_metric = new Thelist_Model_equipmentapplicationmetric($equipment_type_application_metric['equipment_application_metric_id']);
				
			//handle any special cases first
			if (isset($_POST['allow_all_interface_names'])) {
				$app_metric->add_eq_type_allowed_special_metric_value($_POST['equipment_type_application_metric_id'], 'allow_all_interface_names');

			} elseif ($_POST['eq_type_allowed_metric_value_end'] != '') {
					
				if (is_numeric($_POST['eq_type_allowed_metric_value_start']) && is_numeric($_POST['eq_type_allowed_metric_value_end'])) {
					$app_metric->add_eq_type_allowed_metric_value($_POST['equipment_type_application_metric_id'], $_POST['eq_type_allowed_metric_value_start'], $_POST['eq_type_allowed_metric_value_end']);
				} else {
					throw new exception("when doing a range both values must be numeric, if you cannot do that then add them induvidually", 1631);
				}
			} else {
				$app_metric->add_eq_type_allowed_metric_value($_POST['equipment_type_application_metric_id'], $_POST['eq_type_allowed_metric_value_start'], null);
			}
				
			echo "<script>
			window.close();
			window.opener.location.reload();
			</script>";
		}
	}
	
	public function manageeqtypeapplicationallowedvaluesAction()
	{
		$this->_helper->layout->disableLayout();
	
		if (isset($_GET['equipment_type_application_metric_id']) && !$this->getRequest()->isPost()) {
	
			$sql = "SELECT * FROM eq_type_allowed_metric_values
					WHERE equipment_type_application_metric_id='".$_GET['equipment_type_application_metric_id']."'
					";
	
			$equipment_type_application_allowed_config_values = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
	
			if (isset($equipment_type_application_allowed_config_values['0'])) {
					
				$i=0;
				foreach ($equipment_type_application_allowed_config_values as $current_value) {
	
					$eq_type_app_allowed_values[$i]['eq_type_allowed_metric_value_id']		= $current_value['eq_type_allowed_metric_value_id'];
					$eq_type_app_allowed_values[$i]['eq_type_allowed_metric_value_start']	= $current_value['eq_type_allowed_metric_value_start'];
					$eq_type_app_allowed_values[$i]['eq_type_allowed_metric_value_end']		= $current_value['eq_type_allowed_metric_value_end'];
	
					$i++;
				}
			}
	
			if (isset($eq_type_app_allowed_values)) {
				$this->view->eq_type_app_allowed_values	= $eq_type_app_allowed_values;
			}
	
		} elseif ($this->getRequest()->isPost()) {
			
			
			$sql = "SELECT * FROM equipment_type_application_metrics
					WHERE equipment_type_application_metric_id='".$_POST['equipment_type_application_metric_id']."'
					";
			
			$equipment_type_application_metric = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
			$app_metric = new Thelist_Model_equipmentapplicationmetric($equipment_type_application_metric['equipment_application_metric_id']);
				
			foreach($_POST as $index => $allowed_value) {
	
				if ($index != 'equipment_type_application_metric_id') {
	
					if (isset($allowed_value['delete'])) {
	
						if ($allowed_value['delete'] == 1) {
							$app_metric->remove_eq_type_allowed_metric_value($_POST['equipment_type_application_metric_id'], $allowed_value['eq_type_allowed_metric_value_id']);
						}
	
					} else {
	
						if ($allowed_value['eq_type_allowed_metric_value_end'] != '') {
								
							if (is_numeric($allowed_value['eq_type_allowed_metric_value_start']) && is_numeric($allowed_value['eq_type_allowed_metric_value_end'])) {
	
								$app_metric->update_eq_type_allowed_metric_value($_POST['equipment_type_application_metric_id'], $allowed_value['eq_type_allowed_metric_value_id'], $allowed_value['eq_type_allowed_metric_value_start'], $allowed_value['eq_type_allowed_metric_value_end']);
							} else {
								throw new exception("when doing a range both values must be numeric, if you cannot do that then add them induvidually", 1617);
							}
						} else {
							$app_metric->update_eq_type_allowed_metric_value($_POST['equipment_type_application_metric_id'], $allowed_value['eq_type_allowed_metric_value_id'], $allowed_value['eq_type_allowed_metric_value_start'], null);
						}
					}
				}
			}
			
			echo "<script>
				window.close();
				window.opener.location.reload();
				</script>";	
		}
	}
	
	public function addeqtypeapplicationmetricAction()
	{
		$this->_helper->layout->disableLayout();
	
		if (isset($_GET['eq_type_application_id']) && !$this->getRequest()->isPost()) {

			$sql = "SELECT * FROM equipment_application_metrics";
			
			$application_metrics 	= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
				
			if (isset($application_metrics['0'])) {
				
				$sql = "SELECT * FROM eq_type_applications
						WHERE eq_type_application_id='".$_GET['eq_type_application_id']."' 
						";
					
				$equipment_application_map 	= Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
				
				if (isset($equipment_application_map['equipment_application_id'])) {
					
					$equipment_type_obj 	= new Thelist_Model_equipmenttype($equipment_application_map['eq_type_id']);
					$application 			= $equipment_type_obj->get_equipment_type_application_map($equipment_application_map['equipment_application_id']);
					$eq_type_app_metrics	= $application->get_equipment_type_application_metrics();
					
				} else {
					throw new exception("invalid application map", 1630);
				}

				$i=0;
				foreach($application_metrics as $application_metric) {
					$inuse = 'no';
					
					if ($eq_type_app_metrics != null) {
						foreach ($eq_type_app_metrics as $eq_type_app_metric) {
							if ($application_metric['equipment_application_metric_id'] == $eq_type_app_metric->get_equipment_application_metric_id()) {
								$inuse = 'yes';
							}
						}
					}
					
					if ($inuse == 'no') {
						$view_array[$i]['equipment_application_metric_id']			= $application_metric['equipment_application_metric_id'];
						$view_array[$i]['equipment_application_metric_name']		= $application_metric['equipment_application_metric_name'];
					
						$i++;
					}
				}
			}

			if (isset($view_array)) {
				$this->view->available_metrics	= $view_array;
			} else {
				$this->view->error	= 'there are no available metrics in the database';
			}
			
		} elseif ($this->getRequest()->isPost()) {
			
			$sql = "SELECT * FROM eq_type_applications
					WHERE eq_type_application_id='".$_POST['eq_type_application_id']."' 
					";
				
			$equipment_application_map 	= Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
			if (isset($equipment_application_map['eq_type_application_id'])) {
					
				$equipment_type_obj 	= new Thelist_Model_equipmenttype($equipment_application_map['eq_type_id']);
				$application 			= $equipment_type_obj->get_equipment_type_application_map($equipment_application_map['equipment_application_id']);
				$application->add_equipment_type_application_metric($_POST['equipment_application_metric_id']);
					
			} else {
				throw new exception("invalid application map", 1631);
			}

			echo "<script>
			window.close();
			window.opener.location.reload();
			</script>";
		}
	}

	public function manageeqtypeapplicationmetricsAction()
	{
		$this->_helper->layout->disableLayout();
	
		if (isset($_GET['eq_type_application_id']) && !$this->getRequest()->isPost()) {
	
			$sql = "SELECT * FROM eq_type_applications
					WHERE eq_type_application_id='".$_GET['eq_type_application_id']."' 
					";
				
			$equipment_application_map 	= Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
			if (isset($equipment_application_map['equipment_application_id'])) {
				
				$equipment_type_obj 	= new Thelist_Model_equipmenttype($equipment_application_map['eq_type_id']);
				$application 			= $equipment_type_obj->get_equipment_type_application_map($equipment_application_map['equipment_application_id']);
				$eq_type_app_metrics	= $application->get_equipment_type_application_metrics();

				if ($eq_type_app_metrics != null) {
					
					$i=0;
					foreach ($eq_type_app_metrics as $eq_type_app_metric) {

						$type_metric_maps[$i]['eq_type_metric_conf_allow_edit']				= $eq_type_app_metric->get_eq_type_metric_allow_edit();
						$type_metric_maps[$i]['equipment_type_application_metric_id']		= $eq_type_app_metric->get_equipment_type_application_metric_id();
						$type_metric_maps[$i]['equipment_application_metric_id']			= $eq_type_app_metric->get_equipment_application_metric_id();
						$type_metric_maps[$i]['eq_type_metric_max_maps']					= $eq_type_app_metric->get_eq_type_metric_max_maps();
						$type_metric_maps[$i]['eq_type_metric_default_value_1']				= $eq_type_app_metric->get_eq_type_metric_default_value_1();
						$type_metric_maps[$i]['eq_type_metric_default_map']					= $eq_type_app_metric->get_eq_type_metric_default_map();
						$type_metric_maps[$i]['eq_type_metric_mandetory']					= $eq_type_app_metric->get_eq_type_metric_mandetory();
						$type_metric_maps[$i]['equipment_application_metric_name']			= $eq_type_app_metric->get_equipment_application_metric_name();
						
						$i++;
					}

					$this->view->current_metrics = $type_metric_maps;
				}

			} else {
				throw new exception("invalid application map", 1629);
			}
	
		} elseif ($this->getRequest()->isPost()) {

			$sql = "SELECT * FROM eq_type_applications
					WHERE eq_type_application_id='".$_POST['eq_type_application_id']."' 
					";
				
			$equipment_application_map 	= Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
			if (isset($equipment_application_map['equipment_application_id'])) {
				
				$equipment_type_obj 	= new Thelist_Model_equipmenttype($equipment_application_map['eq_type_id']);
				$application 			= $equipment_type_obj->get_equipment_type_application_map($equipment_application_map['equipment_application_id']);
			
			} else {
				throw new exception("invalid application map", 1632);
			}	
				
	
			foreach($_POST as $index => $metric) {
	
				if ($index != 'eq_type_application_id') {
	
					if (isset($metric['delete'])) {
	
						if ($metric['delete'] == 1) {
							$application->remove_eq_type_metric_map($metric['equipment_type_application_metric_id']);
						}
	
					} else {
	
						$metric_obj = $application->get_equipment_type_application_metric($metric['equipment_type_application_metric_id']);
	
						//config mandetory?
						if (isset($metric['eq_type_metric_mandetory'])) {
							if ($metric['eq_type_metric_mandetory'] == 1) {
								//if mandetory set both default and mandetory to 1
								$metric_obj->set_eq_type_metric_mandetory($metric_obj->get_equipment_type_application_metric_id(), 1);
								$metric_obj->set_eq_type_metric_default_map($metric_obj->get_equipment_type_application_metric_id(), 1);
	
							}
						} else {
								
							//else not manadetory
							$metric_obj->set_eq_type_metric_mandetory($metric_obj->get_equipment_type_application_metric_id(), 0);
								
							if (isset($metric['eq_type_metric_default_map'])) {
								if ($metric['eq_type_metric_default_map'] == 1) {
									$metric_obj->set_eq_type_metric_default_map($metric_obj->get_equipment_type_application_metric_id(), 1);
								}
							} else {
								$metric_obj->set_eq_type_metric_default_map($metric_obj->get_equipment_type_application_metric_id(), 0);
							}
						}
	
						if (isset($metric['eq_type_metric_conf_allow_edit'])) {
							if ($metric['eq_type_metric_conf_allow_edit'] == 1) {
								$metric_obj->set_eq_type_metric_allow_edit($metric_obj->get_equipment_type_application_metric_id(), 1);
							}
						} else {
							$metric_obj->set_eq_type_metric_allow_edit($metric_obj->get_equipment_type_application_metric_id(), 0);
						}
	
						//set the max maps
						if (is_numeric($metric['eq_type_metric_max_maps'])) {
							if ($metric['eq_type_metric_max_maps'] > 0) {
								$metric_obj->set_eq_type_metric_max_maps($metric_obj->get_equipment_type_application_metric_id(), $metric['eq_type_metric_max_maps']);
							} else {
								throw new exception("max maps must be 1 or higher", 1633);
							}
						} else {
							throw new exception("max maps must be a numeric value", 1634);
						}
	
						//set the default value
						if ($metric['eq_type_metric_default_value_1'] != '') {
							$metric_obj->set_eq_type_metric_default_value_1($metric_obj->get_equipment_type_application_metric_id(), $metric['eq_type_metric_default_value_1']);
						} else {
							$metric_obj->set_eq_type_metric_default_value_1($metric_obj->get_equipment_type_application_metric_id(), null);
						}
					}
				}
			}

			echo "<script>
				window.close();
				window.opener.location.reload();
				</script>";	
		}
	}
	
	
	public function addequipmenttypeapplicationAction()
	{
		$this->_helper->layout->disableLayout();
	
		if (isset($_GET['eq_type_id']) && !$this->getRequest()->isPost()) {

			$sql = "SELECT * FROM equipment_applications";
			
			$equipment_applications 	= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
				
			if (isset($equipment_applications['0'])) {
				
				$equipment_type_obj 		= new Thelist_Model_equipmenttype($_GET['eq_type_id']);
				$current_applications 		= $equipment_type_obj->get_equipment_type_application_maps();
				
				$i=0;
				foreach($equipment_applications as $equipment_application) {
					$inuse = 'no';
					
					if ($current_applications != null) {
						foreach ($current_applications as $current_application) {
							if ($equipment_application['equipment_application_id'] == $current_application->get_equipment_application_id()) {
								$inuse = 'yes';
							}
						}
					}
					
					if ($inuse == 'no') {
						$view_array[$i]['equipment_application_id']		= $equipment_application['equipment_application_id'];
						$view_array[$i]['equipment_application_name']	= $equipment_application['equipment_application_name'];
					
						$i++;
					}
				}
			}

			if (isset($view_array)) {
				$this->view->available_applications	= $view_array;
			} else {
				$this->view->error	= 'there are no available applications in the database';
			}
			
		} elseif ($this->getRequest()->isPost()) {

			if ($_POST['equipment_application_id'] != 0) {
			
				$equipment_type_obj 		= new Thelist_Model_equipmenttype($_POST['eq_type_id']);
				$equipment_type_obj->add_eq_type_application($_POST['equipment_application_id']);
				
			} else {
				throw new exception("you must select an application", 1636);
			}

			echo "<script>
				window.close();
				window.opener.location.reload();
				</script>";
		}
	}
	
	
	public function manageeqtypeapplicationsAction()
	{
		$this->_helper->layout->disableLayout();
		if (isset($_GET['eq_type_id']) && !$this->getRequest()->isPost()) {
	
			$equipment_type_obj 		= new Thelist_Model_equipmenttype($_GET['eq_type_id']);

			$current_applications = $equipment_type_obj->get_equipment_type_application_maps();

			if ($current_applications != null) {
				$i=0;
				foreach ($current_applications as $current_application) {
					
					$current_apps[$i]['equipment_application_id'] 	= $current_application->get_equipment_application_id();
					$current_apps[$i]['eq_type_application_id'] 	= $current_application->get_eq_type_application_id();
					$current_apps[$i]['equipment_application_name'] = $current_application->get_equipment_application_name();
					$i++;
				}
				
				$this->view->current_apps = $current_apps;
			}
				
		} elseif ($this->getRequest()->isPost()) {

			$equipment_type_obj 		= new Thelist_Model_equipmenttype($_POST['eq_type_id']);
			
			foreach($_POST as $index => $value) {
			
				if ($index != 'eq_type_id') {
			
					if (isset($value['delete'])) {
						if ($value['delete'] == 1) {
							$equipment_type_obj->remove_eq_type_application($value['equipment_application_id']);
						}
					}
				}
			}
			
			echo "<script>
				window.close();
				</script>";	
		}
	}
	
	public function syncdeviceapplicationAction()
	{
		$this->_helper->layout->disableLayout();
		if (isset($_GET['equipment_application_map_id']) && !$this->getRequest()->isPost()) {
		
			$sql = 	"SELECT * FROM equipment_application_mapping
					WHERE equipment_application_map_id='".$_GET['equipment_application_map_id']."' 
					";
			
			$equipment_application_map 	= Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

			$equipment_obj 		= new Thelist_Model_equipments($equipment_application_map['eq_id']);
			$application		= $equipment_obj->get_application_mapping($_GET['equipment_application_map_id']);
			
			
			$this->view->application_name		= $application->get_equipment_application_name();
			$this->view->equipment_fqdn			= $equipment_obj->get_eq_fqdn();
			$this->view->equipment_serial		= $equipment_obj->get_eq_serial_number();
			
			if ($application->get_equipment_application_id() == 1) {
				
				$metrics = $application->get_metric_mappings();
				
				if ($metrics != null) {
					foreach ($metrics as $metric) {
						
						if ($metric->get_equipment_application_metric_id() == 13) {
							$interface_name = $metric->get_equipment_application_metric_value();
						}
					}
				}
				
				$this->view->details				= "This will affect interface: '".$interface_name."'";
				
			} else {
				$this->view->details				= 'No additional details';
			}

		} elseif ($this->getRequest()->isPost()) {

			$sql = 	"SELECT * FROM equipment_application_mapping
					WHERE equipment_application_map_id='".$_POST['equipment_application_map_id']."' 
					";
		
			$equipment_application_map 	= Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
			if (isset($equipment_application_map['equipment_application_id'])) {
		
				$application = new Thelist_Model_equipmentapplication($equipment_application_map['equipment_application_id']);
				$application->fill_mapped_values($_POST['equipment_application_map_id']);
					
				$equipment_obj 		= new Thelist_Model_equipments($equipment_application_map['eq_id']);
				$device				= $equipment_obj->get_device();
				
				//execute
				$device->configure_application($application);

			} else {
				throw new exception("invalid application map", 1638);
			}
			
			echo "<script>
			window.close();
			</script>";	
		}
	}
	
	public function syncdeviceinterfaceconfigAction()
	{
		$this->_helper->layout->disableLayout();
		if (isset($_GET['if_id']) && !$this->getRequest()->isPost()) {
	
			$interface_obj 		= new Thelist_Model_equipmentinterface($_GET['if_id']);
			$equipment_obj 		= new Thelist_Model_equipments($interface_obj->get_eq_id());
				
			$this->view->interface_name			= $interface_obj->get_if_name();
			$this->view->equipment_fqdn			= $equipment_obj->get_eq_fqdn();
			$this->view->equipment_serial		= $equipment_obj->get_eq_serial_number();
				
		} elseif ($this->getRequest()->isPost()) {
				
			$interface_obj 		= new Thelist_Model_equipmentinterface($_GET['if_id']);
			$equipment_obj 		= new Thelist_Model_equipments($interface_obj->get_eq_id());
			$device				= $equipment_obj->get_device();
			$device->configure_interface($interface_obj);
				
			echo "<script>
				window.close();
				</script>";	
		}
	}
	
	public function syncdevicefirmwareAction()
	{
		$this->_helper->layout->disableLayout();
		if (isset($_GET['eq_id']) && isset($_GET['software_package_id']) && !$this->getRequest()->isPost()) {

			if ($_GET['software_package_id'] != 'undefined') {
			
				$equipment_obj 		= new Thelist_Model_equipments($_GET['eq_id']);
		
				$current_software 	= $equipment_obj->get_running_software_package();
				$new_software		= new Thelist_Model_softwarepackage($_GET['software_package_id']);
	
				$return_view['current_name'] 			= $current_software->get_software_package_name();
				$return_view['current_architecture'] 	= $current_software->get_software_package_architecture();
				$return_view['current_version'] 		= $current_software->get_software_package_version();
				$return_view['current_manufacturer']	= $current_software->get_software_package_manufacturer();
				$return_view['new_name'] 				= $new_software->get_software_package_name();
				$return_view['new_architecture'] 		= $new_software->get_software_package_architecture();
				$return_view['new_version'] 			= $new_software->get_software_package_version();
				$return_view['new_manufacturer']		= $new_software->get_software_package_manufacturer();
				
				$return_view['eq_id'] 					= $equipment_obj->get_eq_id();
				$return_view['eq_fqdn'] 				= $equipment_obj->get_eq_fqdn();
				$return_view['eq_serial_number'] 		= $equipment_obj->get_eq_serial_number();
				
				$this->view->software_sync = $return_view;
				
			} else {
				$this->view->error		= 'you dident select a new software package';
			}
			
	
		} elseif ($this->getRequest()->isPost()) {
	
			if (isset($_POST['upgrade'])) {
				
				
				$equipment_obj 		= new Thelist_Model_equipments($_POST['eq_id']);
				$new_software		= new Thelist_Model_softwarepackage($_POST['software_package_id']);
					
				
				$device_back_online = false;
					
				$i=0;
				while ($i < 80 && $device_back_online === false) { 

					try {
							
						if ($i == 0) {
							$device				= $equipment_obj->get_device();
							
							//we need the credentials so we can reconnect if the timeout during reboot is too long
							$credential 		= $device->get_device_authentication_credentials();
							$eq_fqdn 			= $device->get_fqdn();
							
							$device->change_running_os_package($new_software);
								
							//total wait is calculated based on how long it takes to upload the software, again upload to a 
							//751 to install new software, reboot and go through spanning tree on a cisco switch
							
						} else {
							
							$device			= new Thelist_Model_device($eq_fqdn, $credential);
							$api_back_up 	= $device->api_functional();
				
							if ($api_back_up === true) {
								//success
								$device_back_online = true;
							}
						}

					} catch (Exception $e) {
				
						switch($e->getCode()){
				
							case 7108;
							//connection closed, expected
							break;
							case 1202;
							//connection timed out, expected
							break;
							default;
							throw $e;
				
						}
					}

					sleep(5);
					$i++;
				}
				
				//if the upgrade was successful, change the running software
				if ($device_back_online === true) {
					
					$current_software	= $device->get_running_os_package();
					
				 	if ($current_software->get_software_package_id() == $new_software->get_software_package_id()) {

							$equipment_obj->set_current_software_package($new_software);
							
							echo "<script>
							window.close();
							window.opener.location.reload();
							</script>";
							
						} else {
							$this->view->error		= 'Failed to change firmware';
						}
					
				} else {
					$this->view->error		= 'Unknown, may have failed to change firmware, unit did not respond after reboot';
				}
			}
		}
	}
	
	public function syncdeviceallinterfaceconfigsAction()
	{
		$this->_helper->layout->disableLayout();
		if (isset($_GET['eq_id']) && !$this->getRequest()->isPost()) {
			$equipment_obj 		= new Thelist_Model_equipments($_GET['eq_id']);
				
			$this->view->equipment_fqdn			= $equipment_obj->get_eq_fqdn();
			$this->view->equipment_serial		= $equipment_obj->get_eq_serial_number();
				
		} elseif ($this->getRequest()->isPost()) {

			$equipment_obj 		= new Thelist_Model_equipments($_POST['eq_id']);
			$device				= $equipment_obj->get_device();
			
			$equipment_interfaces 	= $equipment_obj->get_interfaces();
			$device_interfaces		= $device->get_interfaces();
			
			
			
			//turn the running and configured interfaces into a single array of interface names
			if ($device_interfaces['running_interfaces'] != null) {

				foreach ($device_interfaces['running_interfaces'] as $dev_run_int) {
					if (!isset($device_interface_names[$dev_run_int['interface_name']])) {
						$device_interface_names[$dev_run_int['interface_name']] = $dev_run_int['interface_name'];
					}
				}
			}
					
			if ($device_interfaces['configured_interfaces'] != null) {
				foreach ($device_interfaces['configured_interfaces'] as $dev_conf_int) {
					if (!isset($device_interface_names[$dev_conf_int['interface_name']])) {
						$device_interface_names[$dev_conf_int['interface_name']] = $dev_conf_int['interface_name'];
					}
				}
			}
			
			if (isset($device_interface_names)) {
				//clean up the index
				$device_interface_names = array_values($device_interface_names);
			}
			
			
			if ($equipment_interfaces != null) {
				
				foreach ($equipment_interfaces as $equipment_interface) {
					
					//update the interface
					$device->configure_interface($equipment_interface);
					
					if (isset($device_interface_names)) {
						
						foreach ($device_interface_names as $dev_if_index => $dev_if_name) {
							
							if ($dev_if_name == $equipment_interface->get_if_name()) {
								
								//interface has been updated, unset it
								unset($device_interface_names[$dev_if_index]);
							}
						}
					}
				}
			}
			
			//we are done with all the interfaces, are there anything left in the device interface list
			if (isset($device_interface_names)) {
				
				if (count($device_interface_names) > 0) {
					
					foreach ($device_interface_names as $remove_dev_if_name) {
						
						//remove all interfaces except loopback
						if ($remove_dev_if_name != 'lo') {
							$device->remove_interface($remove_dev_if_name);
						}
					}
				}
			}

			echo "<script>
				window.close();
				</script>";	
		}
	}
	
	public function manualconfigureequipmentAction()
	{
		if (isset($_GET['eq_id']) && !$this->getRequest()->isPost()) {

			$this->view->eq_id	= $_GET['eq_id'];
			$equipment_obj = new Thelist_Model_equipments($_GET['eq_id']);
			//get all applications to the view
			
			$applications = $equipment_obj->get_application_mappings();

			if ($applications != null) {
				$i=0;
				foreach ($applications as $application) {
					
					$view_applications[$i]['app_name']				= $application->get_equipment_application_name();
					$view_applications[$i]['app_map_id']			= $application->get_equipment_application_map_id();

					$metrics	= $application->get_metric_mappings();

					if ($metrics != null) {
						$j=0;
						foreach ($metrics as $metric) {
							
							$view_applications[$i]['metrics'][$j]['metric_name'] 		= $metric->get_equipment_application_metric_name();
							$view_applications[$i]['metrics'][$j]['metric_value'] 		= $metric->get_equipment_application_metric_value();
							
							$j++;
						}
					}
					
					$i++;
				}
				
				$this->view->applications = $view_applications;
			}
		}
	}
	
	public function manualconfigureequipmentsinglepageAction()
	{
		$this->_helper->layout->disableLayout();
		
		if ($this->getRequest()->isPost()) {
			
			$equipment_obj = new Thelist_Model_equipments($_POST['eq_id']);
			
			if (isset($_POST['interfaces_db_only']) || isset($_POST['interfaces_db_and_device'])) {
				
				foreach($_POST as $index => $equipment_setting) {
				
					if ($index != 'interfaces_db_only' && $index != 'interfaces_db_and_device') {
						
						if ($index == 'interface') {
							
							foreach ($equipment_setting as $if_id_index => $interface) {
								
								$interface_obj = new Thelist_Model_equipmentinterface($interface['if_id']);
								
								if (isset($interface['delete'])) {
								
									if ($interface['delete'] == 1) {
										//run the remove as transaction safe
										$equipment_obj->remove_interface($interface['if_id'], true);
									}
								
								} else {
								
									$eq_interface	= $equipment_obj->get_interface($interface['if_id']);
										
									$eq_interface->set_if_name($interface['if_name']);
									$eq_interface->set_if_type_id($interface['interface_type']);
										
									//patch panels and other non addressable equipment have no mac address
									if ($interface['if_mac_address'] != 'na') {
										$eq_interface->set_if_mac_address($interface['if_mac_address']);
									}
								
									if ($interface['service_point_id'] == '') {
										$eq_interface->set_service_point_id(null);
									} else {
										$eq_interface->set_service_point_id($interface['service_point_id']);
									}
									
									
									//configurations
									if (isset($interface['config'])) {
										
										foreach ($interface['config'] as $index_ip_address_map_id => $interface_config) {
											
											if (isset($interface_config['delete'])) {
											
												if ($interface_config['delete'] == 1) {
													$interface_obj->remove_interface_configuration($interface_config['mapped_if_conf_map_id']);
												}
											
											} else {
											
												$current_configs	= $interface_obj->get_interface_configuration($interface_config['if_conf_id']);
											
												if ($current_configs != false) {
											
													foreach ($current_configs as $current_config) {
										
														if ($current_config->get_mapped_if_conf_map_id() == $interface_config['mapped_if_conf_map_id']) {
															$current_config->set_mapped_configuration_value_1($interface_config['if_conf_value_1']);
														}
													}
													
												} else {
													throw new exception("we cannot set the new value for if_conf_map_id: ".$interface_config['mapped_if_conf_map_id']." with new value: ".$interface_config['if_conf_value_1'].", because the interface says that config does not exist", 1616);
												}
											}
										}
									}
									
									if (isset($interface['ips'])) {
										
										foreach ($interface['ips'] as $index_ipaddress => $ipaddress) {
										
											try {
													
												//an error here should be rolled back
												Zend_Registry::get('database')->get_thelist_adapter()->beginTransaction();
										
												if (is_array($ipaddress)) {
										
													if (isset($ipaddress['if_id'])) {
										
														if (isset($ipaddress['delete'])) {

															$ip_address_obj = $interface_obj->get_ip_address($ipaddress['ip_address_id']);
															$interface_obj->remove_ip_address_map($ip_address_obj);
										
														} else {

															$ip_address_obj = $interface_obj->get_ip_address($ipaddress['ip_address_id']);
															$ip_address_obj->update_mapped_mapping_type($ipaddress['ip_address_map_type']);

														}
													}
												}
													
												//if we are successful commit the data
												Zend_Registry::get('database')->get_thelist_adapter()->commit();
										
											} catch (Exception $e) {
										
												switch($e->getCode()){
										
													default;
													Zend_Registry::get('database')->get_thelist_adapter()->rollback();
													throw $e;
										
												}
											}
										}
									}
								}
							}
						}
					}
				}
				
				if (isset($_POST['interfaces_db_and_device'])) {
				
					$device				= $equipment_obj->get_device();
					$device->configure_interface($interface_obj);
				
					if (isset($_POST['interface'])) {
				
						foreach ($_POST['interface'] as $update_interface) {
								
							$update_interface_obj = new Thelist_Model_equipmentinterface($update_interface['if_id']);
								
							if (!isset($update_interface['delete'])) {
									
								$device->configure_interface($update_interface_obj);
									
							}
						}
					}
				}
					
				echo "<script>
				window.close();
				window.opener.location.reload();
				</script>";	
				
			}
		}
		
		if (isset($_GET['eq_id']) && !$this->getRequest()->isPost()) {
		
			$this->view->eq_id	= $_GET['eq_id'];
			$this->view->interface_types = '';
			
			$equipment_obj = new Thelist_Model_equipments($_GET['eq_id']);
			
			$this->view->equipment_fqdn 		= $equipment_obj->get_eq_fqdn();
			$this->view->equipment_serial 		= $equipment_obj->get_eq_serial_number();
		
			$sql = "SELECT * FROM interface_types
							ORDER BY if_type, if_type_name
							";
		
			$interface_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
			foreach($interface_types as $interface_type) {
		
				$this->view->interface_types .= "<OPTION value='".$interface_type['if_type_id']."'>".$interface_type['if_type_name']."</OPTION>";
		
			}
		
			$this->view->service_points = '';
		
			$sql2 = "SELECT * FROM service_points
					ORDER BY service_point_name
					";
		
			$service_points = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
		
			$this->view->service_points .= "<OPTION value='0'>--- Select One ---</OPTION>";
		
			foreach($service_points as $service_point) {
					
				$this->view->service_points .= "<OPTION value='".$service_point['service_point_id']."'>".$service_point['service_point_name']."</OPTION>";
					
			}
			
			if (($interfaces = $equipment_obj->get_interfaces()) != null) {
		
				$i=0;
				foreach($interfaces as $interface) {
		
					if ($interface->get_service_point_id() != null) {
		
						$service_point_obj = new Thelist_Model_servicepoint($interface->get_service_point_id());
		
						$current_service_point	= "<OPTION value='".$service_point_obj->get_service_point_id()."'>".$service_point_obj->get_service_point_name()."</OPTION>";
		
					} else {
		
						$current_service_point	= "<OPTION value=''>--- Select One---</OPTION>";
		
					}
		
					$interface_details[$i] = array(
		
											'if_id' => $interface->get_if_id(), 
											'if_index' => $interface->get_if_index(), 
											'if_name' => $interface->get_if_name(), 
											'current_interface_type' => "<OPTION value='".$interface->get_if_type()->get_if_type_id()."'>".$interface->get_if_type()->get_if_type_name()."</OPTION>", 
											'if_mac_address' => $interface->get_if_mac_address(),
											'current_service_point' => $current_service_point,
		
					);
						
					$interface_configs 	= $interface->get_interface_configurations();
						
					if ($interface_configs != null) {
		
						$j=0;
						foreach($interface_configs as $interface_config) {
		
							$interface_details[$i]['configs'][$j]['mapped_if_conf_map_id'] 			= $interface_config->get_mapped_if_conf_map_id();
							$interface_details[$i]['configs'][$j]['if_conf_name'] 					= $interface_config->get_if_conf_name();
							$interface_details[$i]['configs'][$j]['if_conf_value_1'] 				= $interface_config->get_mapped_configuration_value_1();
							$interface_details[$i]['configs'][$j]['if_conf_id'] 					= $interface_config->get_if_conf_id();
								
							$j++;
						}
					}
						
					$this->view->ip_address_map_types = '';
		
					$sql2 = "SELECT * FROM items
							WHERE item_type='ip_address_map_type'
							";
		
					$ip_address_map_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
		
					foreach($ip_address_map_types as $ip_address_map_type) {
							
						$this->view->ip_address_map_types .= "<OPTION value='".$ip_address_map_type['item_id']."'>".$ip_address_map_type['item_value']."</OPTION>";
							
					}
		
						
					$ipaddresses = $interface->get_ip_addresses();
						
					if ($ipaddresses != null) {
		
						foreach($ipaddresses as $ipaddress) {
		
							$interface_details[$i]['ips'][] = array(
		
												'ip_address_map_id' => $ipaddress->get_ip_address_map_id(),
												'ipaddress' => $ipaddress->get_ip_address(),
												'subnetmask' => $ipaddress->get_ip_subnet_dotted_decimal_mask(),
												'current_ip_address_map_type' => "<OPTION value='".$ipaddress->get_ip_address_map_type()."'>".$ipaddress->get_ip_address_map_type_resolved()."</OPTION>",
												'ip_address_id' => $ipaddress->get_ip_address_id(),
							);
						}
					}
		
					$i++;
				}
		
				$this->view->interfaces = $interface_details;
			}		
		}
	}
	
	
	public function viewbackupAction()
	{
		$this->_helper->layout->disableLayout();
		if (isset($_GET['eq_log_id'])) {
		
			$sql = "SELECT el.desc FROM equipment_logs el
					WHERE el.eq_log_id='".$_GET['eq_log_id']."'
					";
		
			$backup = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);

		
			$this->view->backup = $backup;
		
		} else {
			//nothing
		}
	}
	
	public function assignmanagementfqdnAction()
	{
		$this->_helper->layout->disableLayout();

		if($this->getRequest()->isPost()) {
			
			
		} elseif (isset($_GET['eq_id'])) {
	
			$this->view->eq_id	= $_GET['eq_id'];
	
		}
	}
	
	public function managebackupsAction()
	{
		
		$this->_helper->layout->disableLayout();
		if (isset($_GET['eq_id'])) {
				
			$sql = "SELECT * FROM equipment_logs eql
					INNER JOIN actions a ON a.action_id=eql.action_id
					WHERE eql.action_id IN (11, 12,13)
					AND eql.eq_id='".$_GET['eq_id']."'
					ORDER BY eql.timestamp DESC
					";
				
			$backups = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
				
			$time = new Thelist_Utility_time();

			$completed_backups = '';
			
			if (isset($backups['0'])) {
				
				$completed_backups = "<tr><td>Time</td><td>View</td></tr>";

				foreach($backups as $backup) {	
					$completed_backups .= "<tr><td>".$time->convert_mysql_datetime_to_am_pm($backup['timestamp'])."</td><td>".$backup['action_name']."</td><td><a href=\"/equipmentconfiguration/viewbackup/?eq_log_id=".$backup['eq_log_id']."\">Show Backup</a></td></tr>";
				}
			}
				
			$this->view->backups = $completed_backups;
				
		} else {
			//nothing
		}
	
	}
	
	public function managerolesAction()
	{
	
		$this->_helper->layout->disableLayout();
		if (isset($_GET['eq_id']) && !$this->getRequest()->isPost()) {
	
			$sql = "SELECT er.equipment_role_name, erm.equipment_role_map_id FROM equipment_role_mapping erm
					INNER JOIN equipment_roles er ON er.equipment_role_id=erm.equipment_role_id
					WHERE erm.eq_id='".$_GET['eq_id']."'
					";
	
			$roles = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
	
			$this->view->roles = $roles;
	
		} elseif ($this->getRequest()->isPost()) {
			
			$equipment_obj = new Thelist_Model_equipments($_POST['eq_id']);

			foreach($_POST as $index => $value) {
				
				if ($index != 'eq_id') {
				
					if (isset($value['delete'])) {
						$equipment_obj->remove_equipment_role($value['equipment_role_map_id']);
					}
				}
			}
			echo "<script>
			window.close();
			window.opener.location.reload();
			</script>";	

		}
	
	}
	
	public function manageiproutegatewaysAction()
	{
	
		$this->_helper->layout->disableLayout();
		if (isset($_GET['eq_id']) && isset($_GET['ip_route_id']) && !$this->getRequest()->isPost()) {
	
			$sql = "SELECT iprg.ip_route_id, iprg.ip_route_gateway_id, ipa.ip_address FROM ip_route_gateways iprg
					INNER JOIN ip_address_mapping ipam ON ipam.ip_address_map_id=iprg.ip_address_map_id
					INNER JOIN ip_addresses ipa ON ipa.ip_address_id=ipam.ip_address_id
					INNER JOIN ip_routes ipr ON ipr.ip_route_id=iprg.ip_route_id
					WHERE ipr.eq_id='".$_GET['eq_id']."'
					AND ipr.ip_route_id='".$_GET['ip_route_id']."'
					";
	
			$gateways = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
	
			$this->view->gateways = $gateways;
	
		} elseif ($this->getRequest()->isPost()) {

			$equipment_obj = new Thelist_Model_equipments($_POST['eq_id']);
	
			foreach($_POST as $index => $value) {
	
				if ($index != 'eq_id') {
	
					if (isset($value['delete'])) {
						
						$ip_route = new Thelist_Model_iproute($value['ip_route_id']);
						$number_of_gws = $ip_route->number_of_gateways();
						
						if ($number_of_gws > 1) {
							$ip_route->remove_ip_route_gateway($value['ip_route_gateway_id']);
						} else {
							$equipment_obj->remove_ip_route($value['ip_route_id'], true);
						}
					}
				}
			}
			
			echo "<script>
				window.close();
				window.opener.location.reload();
				</script>";	
	
		}
	
	}
	
	public function addroleAction()
	{
		$this->_helper->layout->disableLayout();

		if (isset($_GET['eq_id']) && !$this->getRequest()->isPost()) {
			
			$sql = "SELECT GROUP_CONCAT(erm.equipment_role_id) FROM equipment_role_mapping erm
					WHERE erm.eq_id='".$_GET['eq_id']."'
					";
			
			$current_roles = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
			$sql2 = "SELECT er.equipment_role_id, er.equipment_role_name FROM equipment_roles er";
			
			if ($current_roles != null) {
				//filter out current roles
				$sql2 .= " WHERE equipment_role_id NOT IN (".$current_roles.")";
			} 
			
			$available_roles = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);

			if (isset($available_roles['0'])) {
				$result = '';
				
				foreach($available_roles as $available_role) {
					$result .= "<OPTION value=\"" . $available_role['equipment_role_id'] . "\">" . $available_role['equipment_role_name'] . "</OPTION>";
				}
			}
			
			$this->view->available_roles = $result;

		} elseif ($this->getRequest()->isPost()) {

			if ($_POST['equipment_role_id'] != null) {
				$equipment_obj = new Thelist_Model_equipments($_POST['eq_id']);
				$equipment_role = new Thelist_Model_equipmentrole($_POST['equipment_role_id']);
				$equipment_obj->set_new_equipment_role($equipment_role);
			}
			
			echo "<script>
				window.close();
				window.opener.location.reload();
				</script>";	
		}
	}
	
	public function ipsubnetdivideAction()
	{
		$this->_helper->layout->disableLayout();
	
		if (isset($_GET['ip_subnet_id']) && !$this->getRequest()->isPost()) {

			$ip_subnet_obj	= new Thelist_Model_ipsubnet($_GET['ip_subnet_id']);
			
			$child_subnets		= $ip_subnet_obj->get_child_subnets();
			$created_ips		= $ip_subnet_obj->get_ip_addresses();

			if ($child_subnets == null && $created_ips == null) {
				
				$subnet_detail['subnet_address']	= $ip_subnet_obj->get_ip_subnet_address();
				$subnet_detail['subnet_cidr']		= $ip_subnet_obj->get_ip_subnet_cidr_mask();
				
				//create dropdown of possible new subnet masks
				if ($ip_subnet_obj->get_ip_subnet_cidr_mask() < 30) {
					
					$subnet_detail['new_cidr_masks'] 	= '';
					$new_max_mask						= $ip_subnet_obj->get_ip_subnet_cidr_mask() +1;
					$possible_new_cidr_masks			= range($new_max_mask, 30);
					
					foreach ($possible_new_cidr_masks as $new_mask) {
						$subnet_detail['new_cidr_masks'] .= "<OPTION value=\"" . $new_mask . "\">" . $new_mask . "</OPTION>";
					}
					
				} else {
					$this->view->error	= 'This subnet is already a /30 and cannot be divided further';
				}

			} else {
				$this->view->error	= 'This subnet has either child subnets or ips have already been created, we cannot divide the subnet';
			}
			
			if (isset($subnet_detail)) {
				$this->view->subnet_detail = $subnet_detail;
			}

		} elseif ($this->getRequest()->isPost()) {

			$ip_subnet_obj	= new Thelist_Model_ipsubnet($_POST['ip_subnet_id']);
				
			$child_subnets	= $ip_subnet_obj->get_child_subnets();
			$created_ips		= $ip_subnet_obj->get_ip_addresses();

			if ($child_subnets == null && $created_ips == null) {

				//create dropdown of possible new subnet masks
				if ($ip_subnet_obj->get_ip_subnet_cidr_mask() < $_POST['new_cidr']) {
						
					$new_subnets	= $ip_subnet_obj->set_child_subnets($_POST['new_cidr']);
					
					echo "<script>
						window.close();
						window.opener.location.href='/equipmentconfiguration/manageipsubnets?ip_subnet_master_id=".$_POST['ip_subnet_id']."';
						</script>";

				} else {
					throw new exception("subnet id: ".$_POST['ip_subnet_id']." you are trying to subdivide to ".$_POST['new_cidr'].", but that is not possible because the subent is ".$ip_subnet_obj->get_ip_subnet_cidr_mask().", you bypassed frontend validations somehow", 1614);
				}

			} else {
				throw new exception("subnet id: ".$_POST['ip_subnet_id']." has either created ips or child subnets, you bypassed frontend validations somehow", 1614);
			}
		}
	}
	
	public function ipsubnetrouteAction()
	{
	$this->_helper->layout->disableLayout();
	
		if (isset($_GET['ip_subnet_id']) && !$this->getRequest()->isPost()) {

			$ip_subnet_obj	= new Thelist_Model_ipsubnet($_GET['ip_subnet_id']);
			
			$child_subnets	= $ip_subnet_obj->get_child_subnets();
			$inuse_ips		= $ip_subnet_obj->get_inuse_host_ips();
			
			//we can route as long as none of the ips are inuse
			//its ok that they have been created.
			if ($child_subnets == null && $inuse_ips == null) {
				
				$subnet_detail['subnet_address']	= $ip_subnet_obj->get_ip_subnet_address();
				$subnet_detail['subnet_cidr']		= $ip_subnet_obj->get_ip_subnet_cidr_mask();

				$sql = 	"SELECT DISTINCT(et.eq_type_id), et.eq_manufacturer, et.eq_model_name FROM equipments e
						INNER JOIN equipment_types et ON et.eq_type_id=e.eq_type_id
						WHERE e.eq_fqdn!=''
						AND e.eq_fqdn IS NOT NULL
						ORDER BY et.eq_manufacturer, et.eq_model_name
						";
				
				$eq_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
					
				$eq_type_dd = '';

				foreach ($eq_types as $eq_type) {
					$eq_type_dd .= "<OPTION value=\"" . $eq_type['eq_type_id'] . "\">" . $eq_type['eq_manufacturer'] . " - " . $eq_type['eq_model_name'] . "</OPTION>";
				}

			} else {
				$this->view->error	= 'This subnet has either child subnets or ips that are inuse, and cannot be routed';
			}
			
			if (isset($subnet_detail)) {
				$this->view->subnet_detail 	= $subnet_detail;
				$this->view->eq_types 		= $eq_type_dd;
			}

		} elseif ($this->getRequest()->isPost()) {

			$ip_subnet_obj	= new Thelist_Model_ipsubnet($_POST['ip_subnet_id']);
			$equipment_obj = new Thelist_Model_equipments($_POST['eq_id']);
			
			$child_subnets	= $ip_subnet_obj->get_child_subnets();
			$inuse_ips		= $ip_subnet_obj->get_inuse_host_ips();
			
			if ($child_subnets == null && $inuse_ips == null) {

				//create dropdown of possible new subnet masks
			if ($_POST['eq_type_id'] != 0 && $_POST['eq_id'] != 0 && $_POST['ip_address_map_id'] != 0) {
				
			} else {
				$this->view->error	= 'We are missing information, you must select equipment type, equipment and ip gateway';
			}

			$equipment_obj->add_new_route($ip_subnet_obj->get_ip_subnet_id(), $_POST['ip_address_map_id'], $_POST['gateway_cost']);

			echo "<script>
			window.close();
			window.opener.location.href.reload();
			</script>";


			} else {
				throw new exception("subnet id: ".$_POST['ip_subnet_id']." has either inuse ips or child subnets, you bypassed frontend validations somehow", 1615);
			}
		}
	}
	
	public function manageipsubnetsAction()
	{
		$this->_helper->layout->disableLayout();
	
		if (isset($_GET['ip_subnet_master_id']) && !$this->getRequest()->isPost()) {

			$ipsubnet_obj 		= new Thelist_Model_ipsubnet($_GET['ip_subnet_master_id']);

			if ($_GET['ip_subnet_master_id'] == 'null') {
				
				$sql = 	"SELECT * FROM ip_subnets
						WHERE ip_subnet_master_id IS NULL
						";
			} elseif (is_numeric($_GET['ip_subnet_master_id'])) {
				
				$sql = 	"SELECT * FROM ip_subnets
						WHERE ip_subnet_master_id='".$_GET['ip_subnet_master_id']."'
						";
			}

			$subnets = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			if (isset($subnets['0'])) {
				$this->view->subnets = $subnets;		
			} 
				
			$all_ips 		= $ipsubnet_obj->get_ip_addresses();

			if ($all_ips != null) {
				
				$mapped_ips		= $ipsubnet_obj->get_mapped_ips();
				
				$i=0;
				$j=0;
				foreach ($all_ips as $ip) {
					
					$ips_for_view[$i]['ip_address']	= $ip->get_ip_address();
					
					if ($mapped_ips != null) {
						
						foreach ($mapped_ips as $mapped_ip) {
							
							if ($ip->get_ip_address_id() == $mapped_ip->get_ip_address_id()) {
								$interface_obj = new Thelist_Model_equipmentinterface($mapped_ip->get_mapped_if_id());
								$equipment_obj	= new Thelist_Model_equipments($interface_obj->get_eq_id());
							
								$ips_for_view[$i]['maps'][$j]['interface_name']	= $interface_obj->get_if_name();
								$ips_for_view[$i]['maps'][$j]['eq_fqdn']		= $equipment_obj->get_eq_fqdn();
								
								$j++;
							}
						}
					}

					$i++;
				}
			}
			
			if (isset($ips_for_view)) {
				$this->view->ips	= $ips_for_view;
			}

		} elseif ($this->getRequest()->isPost()) {

			//need combine function
			echo "<script>
			window.close();
			window.opener.location.reload();
			</script>";	
		}
	}
	
	public function manageipsubnetrecursivelyAction()
	{
		$this->_helper->layout->disableLayout();
	
		if (isset($_GET['ip_subnet_id']) && !$this->getRequest()->isPost()) {

			if (is_numeric($_GET['ip_subnet_id'])) {
			
				$ipsubnet_obj 		= new Thelist_Model_ipsubnet($_GET['ip_subnet_id']);
				$all_child_subnets	= $ipsubnet_obj->get_child_subnets_recursively();
				
				if ($all_child_subnets != null) {
					
					$i=0;
					foreach ($all_child_subnets as $child_subnet) {
						
						$subnets[$i]['ip_subnet_id']	= $child_subnet->get_ip_subnet_id();
						$subnets[$i]['ip_subnet_address']	= $child_subnet->get_ip_subnet_address();
						$subnets[$i]['ip_subnet_cidr_mask']	= $child_subnet->get_ip_subnet_cidr_mask();
						$i++;
						
					}
				}
			}

			if (isset($subnets['0'])) {
				$this->view->subnets = $subnets;
			}
	
		} elseif ($this->getRequest()->isPost()) {
	
			//need combine function
			echo "<script>
				window.close();
				window.opener.location.reload();
				</script>";	
		}
	}
	
	public function getequipmentgatewaysajaxAction()
	{
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		if (isset($_GET['eq_id'])) {
			$equipment_obj = new Thelist_Model_equipments($_GET['eq_id']);
			
			//get all interfaces
			$gateway_ips	= $equipment_obj->get_all_reachable_host_ips();
				
			//create the drop down of ip address maps that can be used for gateways
			if ($gateway_ips != null) {
			
				$gateways = "<OPTION value='0'>--- Select One ---</OPTION>";
			
				foreach ($gateway_ips as $gateway_ip) {
						
					$gateways .= "<OPTION value='".$gateway_ip->get_ip_address_map_id()."'>".$gateway_ip->get_ip_address()."</OPTION>";
				}
			
			} else {
				$gateways = "<OPTION value='0'>-No Reachable Gateways-</OPTION>";
			}
			
			echo $gateways;
		}
	}
	
	public function addiprouteAction()
	{
		$this->_helper->layout->disableLayout();
	
		if (isset($_GET['eq_id']) && !$this->getRequest()->isPost()) {
			$subnets = '';
			$equipment_obj = new Thelist_Model_equipments($_GET['eq_id']);
			$default_route_included	= 'no';
			//get all current routes so we can add a gateway to them
			$current_routes						= $equipment_obj->get_ip_routes();
			
			if ($current_routes != null) {
				
				$subnets .= "<OPTION value='0'>--- Add Gateway Only ---</OPTION>";
			
				foreach ($current_routes as $current_route) {
						
					//is there already a default route?
					if ($current_route->get_ip_subnet()->get_ip_subnet_cidr_mask() == 0) {
						$default_route_included	= 'yes';
					}
						
					//we need to know that these are already subnets we append the value with IPSUBID_ and then the ip_subnet_id
					$subnets .= "<OPTION value='IPROUTEID_".$current_route->get_ip_route_id()."'>".$current_route->get_ip_subnet()->get_ip_subnet_address()."/".$current_route->get_ip_subnet()->get_ip_subnet_cidr_mask()."</OPTION>";
				}
			}
			
			//now we get all the subnets that are mapped to the equipment but not routed out
			$available_subnets	= $equipment_obj->get_available_subnets();
				
			if ($available_subnets != false) {
					
				if ($current_routes == null) {
					$subnets .= "<OPTION value='0'>--- New Routes ---</OPTION>";
				} else {
					$subnets .= "<OPTION value='0'></OPTION>";
					$subnets .= "<OPTION value='0'>--- New Routes ---</OPTION>";
				}
				
				if ($default_route_included	== 'no') {
					//include default route
					$subnets .= "<OPTION value='IPSUBNETID_1'>0.0.0.0/0</OPTION>";
				}
					
				foreach($available_subnets as $available_subnet) {
					$subnets .= "<OPTION value='IPSUBNETID_".$available_subnet->get_ip_subnet_id()."'>".$available_subnet->get_ip_subnet_address()."/".$available_subnet->get_ip_subnet_cidr_mask()."</OPTION>";
				}
					
			} else {
				
				if ($default_route_included	== 'no') {
					//include default route
					$subnets 	.= "<OPTION value='0'>--- New Routes ---</OPTION>";
					$subnets 	.= "<OPTION value='1'>0.0.0.0/0</OPTION>";
				} else {
					if ($current_routes == null) {
						$subnets .= "<OPTION value='0'>--- No Unused Subnets Available ---</OPTION>";
					} else {
						$subnets .= "<OPTION value='0'>--- No Unused Subnets Available ---</OPTION>";
					}
				}
			}

			//get all interfaces
			$gateway_ips	= $equipment_obj->get_all_reachable_host_ips();
			
			//create the drop down of ip address maps that can be used for gateways
			if ($gateway_ips != null) {
				
				$gateways = "<OPTION value='0'>--- Select One ---</OPTION>";
				
				foreach ($gateway_ips as $gateway_ip) {
					
					$gateways .= "<OPTION value='".$gateway_ip->get_ip_address_map_id()."'>".$gateway_ip->get_ip_address()."</OPTION>";
				}
				
			} else {
				$gateways = "<OPTION value='0'>-No Reachable Gateways-</OPTION>";
			}
			
			if (isset($subnets)) {

				$this->view->subnets 					= $subnets;
				$this->view->ip_address_map_ids 		= $gateways;
			}

		} elseif ($this->getRequest()->isPost()) {
			
			if ($_POST['subnet'] !== 0) {

				$equipment_obj 		= new Thelist_Model_equipments($_POST['eq_id']);

				if (preg_match("/(^IPSUBNETID_|^IPROUTEID_)([0-9]+)/", $_POST['subnet'], $new_subnet_raw)) {
				
					if ($new_subnet_raw['1'] == 'IPSUBNETID_') {
						//this is a brand new subnet
						
						if ($_POST['ip_address_map_id'] === 0) {
							throw new exception('you must select a gateway address', 1610);
						} elseif ($_POST['gateway_cost'] == '') {
							throw new exception('you must select a gateway cost', 1611);
						}
						
						//route the new subnet
						$subnet				= new Thelist_Model_ipsubnet($new_subnet_raw['2']);
						
						$equipment_obj->add_new_route($subnet->get_ip_subnet_id(), $_POST['ip_address_map_id'], $_POST['gateway_cost']);
						
					} elseif ($new_subnet_raw['1'] == 'IPROUTEID_') {
						//this is just adding a gateway
						$existing_route		= new Thelist_Model_iproute($new_subnet_raw['2']);
						$existing_route->add_ip_route_gateway($_POST['ip_address_map_id'], $_POST['gateway_cost']);
					}
				
				} else {
					throw new exception('we got un expected return from the frontend', 1608);
				}
				
				
			} else {
				throw new exception('you must select a subnet', 1607);
			}

			echo "<script>
					window.close();
					window.opener.location.reload();
					</script>";	
		}
	}
	
	public function manageiftypeconfigurationsAction()
	{
		$this->_helper->layout->disableLayout();
	
		if (isset($_GET['if_type_id']) && !$this->getRequest()->isPost()) {
	
			$if_type_obj = new Thelist_Model_interfacetype($_GET['if_type_id']);
				
			$if_type_conf_maps	= $if_type_obj->get_if_configuration_type_maps();
	
			if ($if_type_conf_maps != null) {
					
				$i=0;
				foreach ($if_type_conf_maps as $if_type_conf_map) {
						
					$sql = "SELECT * FROM interface_type_configurations
							WHERE if_type_id='".$_GET['if_type_id']."'
							AND if_conf_id='".$if_type_conf_map->get_if_conf_id()."'
							";
	
					$if_type_conf_map_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

					$type_conf_maps[$i]['if_conf_id']						= $if_type_conf_map->get_if_conf_id();
					$type_conf_maps[$i]['if_conf_name']						= $if_type_conf_map->get_if_conf_name();
					$type_conf_maps[$i]['interface_type_configuration_id']	= $if_type_conf_map_id['interface_type_configuration_id'];
					$type_conf_maps[$i]['if_conf_default_value_1']			= $if_type_conf_map_id['if_conf_default_value_1'];
					$type_conf_maps[$i]['if_conf_max_maps']					= $if_type_conf_map_id['if_conf_max_maps'];
					$type_conf_maps[$i]['if_conf_default_map']				= $if_type_conf_map_id['if_conf_default_map'];
					$type_conf_maps[$i]['if_conf_mandetory']				= $if_type_conf_map_id['if_conf_mandetory'];
					$type_conf_maps[$i]['if_type_conf_allow_edit']			= $if_type_conf_map_id['if_type_conf_allow_edit'];
					
					$i++;
				}
			}
				
			if (isset($type_conf_maps)) {
				$this->view->if_type_confs	= $type_conf_maps;
			}
	
		} elseif ($this->getRequest()->isPost()) {

			$if_type_obj = new Thelist_Model_interfacetype($_POST['if_type_id']);
	
			foreach($_POST as $index => $if_conf) {
	
				if ($index != 'if_type_id') {
						
					if (isset($if_conf['delete'])) {
	
						if ($if_conf['delete'] == 1) {
							$if_type_obj->remove_if_configuration_type_map($if_conf['if_conf_id']);
						}
	
					} else {
						
						$if_conf_obj = new Thelist_Model_interfaceconfiguration($if_conf['if_conf_id']);
						
						//config mandetory?
						if (isset($if_conf['if_conf_mandetory'])) {
							if ($if_conf['if_conf_mandetory'] == 1) {
								//if mandetory set both default and mandetory to 1
								$if_conf_obj->set_if_conf_mandetory($if_type_obj->get_if_type_id(), 1);
								$if_conf_obj->set_if_conf_default_map($if_type_obj->get_if_type_id(), 1);

							}
						} else {
							
							//else not manadetory
							$if_conf_obj->set_if_conf_mandetory($if_type_obj->get_if_type_id(), 0);
							
							if (isset($if_conf['if_conf_default_map'])) {
								if ($if_conf['if_conf_default_map'] == 1) {
									$if_conf_obj->set_if_conf_default_map($if_type_obj->get_if_type_id(), 1);
								}
							} else {
								$if_conf_obj->set_if_conf_default_map($if_type_obj->get_if_type_id(), 0);
							}
						}
						
						if (isset($if_conf['if_type_conf_allow_edit'])) {
							if ($if_conf['if_type_conf_allow_edit'] == 1) {
								$if_conf_obj->set_if_type_conf_allow_edit($if_type_obj->get_if_type_id(), 1);
							}
						} else {
							$if_conf_obj->set_if_type_conf_allow_edit($if_type_obj->get_if_type_id(), 0);
						}

						//set the max maps
						if (is_numeric($if_conf['if_conf_max_maps'])) {
							if ($if_conf['if_conf_max_maps'] > 0) {
								$if_conf_obj->set_if_conf_max_maps($if_type_obj->get_if_type_id(), $if_conf['if_conf_max_maps']);
							} else {
								throw new exception("max maps must be 1 or higher", 1627);
							}
						} else {
							throw new exception("max maps must be a numeric value", 1628);
						}
						
						//set the default value
						if ($if_conf['if_conf_default_value_1'] != '') {
							$if_conf_obj->set_mapped_configuration_type_default_value_1($if_type_obj->get_if_type_id(), $if_conf['if_conf_default_value_1']);
						} else {
							$if_conf_obj->set_mapped_configuration_type_default_value_1($if_type_obj->get_if_type_id(), null);
						}
					}
				}
			}
			
			echo "<script>
			window.close();
			window.opener.location.reload();
			</script>";	
		}
	
	}
	
	
	public function manageiftypeconfallowedvaluesAction()
	{
		$this->_helper->layout->disableLayout();
	
		if (isset($_GET['interface_type_configuration_id']) && !$this->getRequest()->isPost()) {
	
					$sql = "SELECT * FROM interface_type_allowed_config_values
							WHERE interface_type_configuration_id='".$_GET['interface_type_configuration_id']."'
							";
	
					$if_type_allowed_config_values = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
	
				if ($if_type_allowed_config_values != null) {
					
				$i=0;
				foreach ($if_type_allowed_config_values as $if_type_allowed_config_value) {

					$type_conf_allowed_values[$i]['if_type_allowed_config_value_id']		= $if_type_allowed_config_value['if_type_allowed_config_value_id'];
					$type_conf_allowed_values[$i]['if_type_allowed_config_value_start']		= $if_type_allowed_config_value['if_type_allowed_config_value_start'];
					$type_conf_allowed_values[$i]['if_type_allowed_config_value_end']		= $if_type_allowed_config_value['if_type_allowed_config_value_end'];

					$i++;
				}
			}
	
			if (isset($type_conf_allowed_values)) {
				$this->view->type_conf_allowed_values	= $type_conf_allowed_values;
			}
	
		} elseif ($this->getRequest()->isPost()) {
			
			$sql = "SELECT * FROM interface_type_configurations
					WHERE interface_type_configuration_id='".$_POST['interface_type_configuration_id']."'
					";
			
			$if_config = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			$if_conf_obj = new Thelist_Model_interfaceconfiguration($if_config['if_conf_id']);
			
			foreach($_POST as $index => $allowed_config) {
	
				if ($index != 'interface_type_configuration_id') {
						
					if (isset($allowed_config['delete'])) {
	
						if ($allowed_config['delete'] == 1) {
							$if_conf_obj->remove_if_type_config_allowed_value($_POST['interface_type_configuration_id'], $allowed_config['if_type_allowed_config_value_id']);
						}
	
					} else {
						
						if ($allowed_config['if_type_allowed_config_value_end'] != '') {
							
							if (is_numeric($allowed_config['if_type_allowed_config_value_start']) && is_numeric($allowed_config['if_type_allowed_config_value_end'])) {
								
								$if_conf_obj->update_if_type_config_allowed_value($_POST['interface_type_configuration_id'], $allowed_config['if_type_allowed_config_value_id'], $allowed_config['if_type_allowed_config_value_start'], $allowed_config['if_type_allowed_config_value_end']);
							} else {
								throw new exception("when doing a range both values must be numeric, if you cannot do that then add them induvidually", 1617);
							}
						} else {
							$if_conf_obj->update_if_type_config_allowed_value($_POST['interface_type_configuration_id'], $allowed_config['if_type_allowed_config_value_id'], $allowed_config['if_type_allowed_config_value_start'], null);
						}
					}
				}
			}
			echo "<script>
			window.close();
			window.opener.location.reload();
			</script>";	
		}
	}
	
	public function addinterfaceconfigallowedvalueAction()
	{
		$this->_helper->layout->disableLayout();
		
		if (isset($_GET['interface_type_configuration_id']) && !$this->getRequest()->isPost()) {
			//nothing, it all about adding
			
		} elseif ($this->getRequest()->isPost()) {
			
			$sql = "SELECT * FROM interface_type_configurations
					WHERE interface_type_configuration_id='".$_POST['interface_type_configuration_id']."'
					";
				
			$if_config = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			$if_conf_obj = new Thelist_Model_interfaceconfiguration($if_config['if_conf_id']);
			
			if ($_POST['if_type_allowed_config_value_end'] != '') {
					
				if (is_numeric($_POST['if_type_allowed_config_value_start']) && is_numeric($_POST['if_type_allowed_config_value_end'])) {
			
					$if_conf_obj->add_if_type_config_allowed_value($_POST['interface_type_configuration_id'], $_POST['if_type_allowed_config_value_start'], $_POST['if_type_allowed_config_value_end']);
				} else {
					throw new exception("when doing a range both values must be numeric, if you cannot do that then add them induvidually", 1617);
				}
			} else {
				$if_conf_obj->add_if_type_config_allowed_value($_POST['interface_type_configuration_id'], $_POST['if_type_allowed_config_value_start'], null);
			}
			
			echo "<script>
			window.close();
			window.opener.location.reload();
			</script>";
		}
	}
	
	public function addinterfacetypeconfigurationAction()
	{
		$this->_helper->layout->disableLayout();
	
		if (isset($_GET['if_type_id']) && !$this->getRequest()->isPost()) {

			$sql = "SELECT * FROM interface_configurations";
			
			$if_configs 	= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
				
			if (isset($if_configs['0'])) {
				
				$interface_type_obj 		= new Thelist_Model_interfacetype($_GET['if_type_id']);
				$available_interface_configurations	= $interface_type_obj->get_if_configuration_type_maps();
				
				$i=0;
				foreach($if_configs as $if_config) {
					$inuse = 'no';
					
					if ($available_interface_configurations != null) {
						foreach ($available_interface_configurations as $avail_conf) {
							if ($if_config['if_conf_id'] == $avail_conf->get_if_conf_id()) {
								$inuse = 'yes';
							}
						}
					}
					
					if ($inuse == 'no') {
						$view_array[$i]['if_conf_id']		= $if_config['if_conf_id'];
						$view_array[$i]['if_conf_name']		= $if_config['if_conf_name'];
					
						$i++;
					}
				}
			}

			if (isset($view_array)) {
				$this->view->available_configurations	= $view_array;
			} else {
				$this->view->error	= 'there are no available configs in the database';
			}
			
		} elseif ($this->getRequest()->isPost()) {

			$interface_type_obj 		= new Thelist_Model_interfacetype($_POST['if_type_id']);
			$interface_type_obj->add_if_configuration_type_map($_POST['if_conf_id']);
				
			echo "<script>
				window.close();
				window.opener.location.reload();
				</script>";
		}
	}
	
	public function manageiproutesAction()
	{
		$this->_helper->layout->disableLayout();
		$error = '';
	
		if($this->getRequest()->isPost()) {

			$equipment_obj = new Thelist_Model_equipments($_POST['eq_id']);
			
			foreach($_POST as $index => $value) {
				
				if ($index != 'eq_id') {
				
					if (isset($value['delete'])) {
						$equipment_obj->remove_ip_route($value['ip_route_id'], true);
					}
				}
			}
			
			echo "<script>
			window.close();
			window.opener.location.reload();
			</script>";	
			
		} elseif (isset($_GET['eq_id']) && !$this->getRequest()->isPost()) {
				
			$this->view->error = $error;
				
			$this->view->eq_id	= $_GET['eq_id'];
				
			$equipment_obj = new Thelist_Model_equipments($_GET['eq_id']);
			$ip_routes	= $equipment_obj->get_ip_routes();
			
			if ($ip_routes != null) {
				$return = '';
				foreach ($ip_routes as $ip_route) {
					$route_subnet = $ip_route->get_ip_subnet();
					
					$return[$ip_route->get_ip_route_id()]['ip_route_id']	= $ip_route->get_ip_route_id();
					$return[$ip_route->get_ip_route_id()]['subnet_address']	= $route_subnet->get_ip_subnet_address();
					$return[$ip_route->get_ip_route_id()]['subnet_cidr']	= $route_subnet->get_ip_subnet_cidr_mask();
					
				}
			}
			
			if (isset($return)) {
				
				$this->view->iproutes = $return;
			}
		}
	}
	
	public function addipaddressAction()
	{
		$this->_helper->layout->disableLayout();
		$this->view->error = '';
		
		if($this->getRequest()->isPost()) {
			
			$this->view->eq_id	= $_POST['eq_id'];
			$equipment_obj 				= new Thelist_Model_equipments($_POST['eq_id']);
	
			try {
		
				if ($_POST['ip_subnet_id'] != 0 && $_POST['ip_address_1'] != 0 && $_POST['ip_address_2'] != 0 && $_POST['mapping_type_item_id'] != 0) {
						
					//is this subnet already connected to some interface?
					$ip_subnet_obj 			= new Thelist_Model_ipsubnet($_POST['ip_subnet_id']);
					$connected_interface	= $equipment_obj->get_connected_subnet_interface($ip_subnet_obj);
					
					if ($connected_interface != false) {
					
						if ($_POST['if_id'] != 0) {
							
							if ($connected_interface->get_if_id() != $_POST['if_id']) {
								throw new exception("interface id ".$connected_interface->get_if_id()." already has this ip address, and user is asking to attach to if_id: ".$_POST['if_id']." same subnet cannot be on multiple interface in the same equipment", 1612);
							}
						} 
						
					} else {
						//subnet is not yet connected to any interface on this equipment
						
						if ($_POST['if_id'] != 0) {
							$connected_interface = new Thelist_Model_equipmentinterface($_POST['if_id']);
						} else {
							throw new exception("subnet is not already connected to an interface on the equipment, please select one", 1613);
						}
					}

					//resolve the mapping type 
					$sql = "SELECT item_name FROM items
							WHERE item_id='".$_POST['mapping_type_item_id']."'
							";
					
					$mapping_type_name = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
					//
					//this can still fail because we will be getting ips that could already exist on other equipment and we put rules in 
					//place to ensure that any ip address is only allowed to be mapped multiple times to VRRP interfaces
					//so we make this last part fransaction safe
					Zend_Registry::get('database')->get_thelist_adapter()->beginTransaction();
					
					$ip_addresses	= $ip_subnet_obj->create_ip_addresses($_POST['ip_address_1'], $_POST['ip_address_2']);
					
					foreach ($ip_addresses as $ip_addresse) {

						$connected_interface->map_new_ip_address($ip_addresse, $_POST['mapping_type_item_id']);
					}
					
					Zend_Registry::get('database')->get_thelist_adapter()->commit();

				} else {
					throw new exception('missing required information from selections', 1603);	
				}
		
		
			} catch (Exception $e) {
		
				switch($e->getCode()){
		
					case 1603;
					//1603, missing required information
					$this->view->error	= 'missing required information from selections';
					break;
					case 7903;
					case 7904;
					case 7905;
					case 7906;
					$this->view->error	= 'End ip address is before first ip address';
					break;
					case 1612;
					$interface_new 			= new Thelist_Model_equipmentinterface($_POST['if_id']);
					$this->view->error	= "You wanted to attach the ips to ".$interface_new->get_if_name().", but they are already attached to ".$connected_interface->get_if_name()." you must first remove them from the old interface ";
					break;
					case 1613;
					$this->view->error	= 'The subnet is not already connected to an interface on the equipment, please select one';
					break;
					case 1078;
					$this->view->error	= "one of the ips is already mapped to another interface and ".$interface_new->get_if_name()."  is not a VRRP interface";
					break;
					default;
					throw $e;
		
				}
			}
			if ($this->view->error == '') {
		
				echo "<script>
				window.close();
				window.opener.location.reload();
				</script>";
		
			}
			
		} elseif (isset($_GET['eq_id']) && !$this->getRequest()->isPost()) {

			$this->view->eq_id			= $_GET['eq_id'];
			$equipment_obj 				= new Thelist_Model_equipments($_GET['eq_id']);
			$ip_routes					= $equipment_obj->get_ip_routes();
			
			//now we get all the subnets that are mapped to the equipment
			$available_subnets	= $equipment_obj->get_available_subnets();
			$connected_subnets	= $equipment_obj->get_all_connected_subnets();

			if ($available_subnets != false) {
				
				$subnets = "<OPTION value='0'>--- Unused Subnets ---</OPTION>";
				
				foreach($available_subnets as $available_subnet) {
					$subnets .= "<OPTION value='".$available_subnet->get_ip_subnet_id()."'>".$available_subnet->get_ip_subnet_address()."/".$available_subnet->get_ip_subnet_cidr_mask()."</OPTION>";
				}
				
			} else {
				$subnets = "<OPTION value='0'>--- No Unused Subnets Available ---</OPTION>";
			}
			
			if ($connected_subnets != false) {

				$subnets .= "<OPTION value='0'></OPTION>";
				$subnets .= "<OPTION value='0'>--- In Use Subnets ---</OPTION>";
			
				foreach($connected_subnets as $connected_subnet) {
					$subnets .= "<OPTION value='".$connected_subnet->get_ip_subnet_id()."'>".$connected_subnet->get_ip_subnet_address()."/".$connected_subnet->get_ip_subnet_cidr_mask()."</OPTION>";
				}

			} else {
				$this->view->subnets = "<OPTION value='0'>--- No Available Subnets ---</OPTION>";
			}
			
			$this->view->subnets = 	$subnets;

			$sql3 = "SELECT * FROM items
					WHERE item_type='ip_address_map_type'
					ORDER BY item_value
					";
				
			$mapping_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql3);

			if (isset($mapping_types['0'])) {
				
				$this->view->mapping_types = "<OPTION value='0'>---Select One---</OPTION>";
				
				foreach($mapping_types as $mapping_type) {
					$this->view->mapping_types .= "<OPTION value='".$mapping_type['item_id']."'>".$mapping_type['item_value']."</OPTION>";
				}
				
			} else {
				$this->view->mapping_types = "<OPTION value='0'>---NO Mapping Types---</OPTION>";
			}

			$interfaces	= $equipment_obj->get_interfaces();

			if ($interfaces != null) {
				
				$this->view->interfaces = '<OPTION value=0>---Select One or None---</OPTION>';
				
				foreach($interfaces as $interface) {
						
					$this->view->interfaces .= "<OPTION value='".$interface->get_if_id()."'>".$interface->get_if_name()."</OPTION>";
						
				}
			}
		}
	}
	
	public function addinterfaceipaddressAction()
	{
		$this->_helper->layout->disableLayout();
		$this->view->error = '';
	
		if($this->getRequest()->isPost()) {

			$interface_obj 				= new Thelist_Model_equipmentinterface($_POST['if_id']);
			$equipment_obj 				= new Thelist_Model_equipments($interface_obj->get_eq_id());
			
			try {
	
				if ($_POST['ip_subnet_id'] != 0 && $_POST['ip_address_1'] != 0 && $_POST['ip_address_2'] != 0 && $_POST['mapping_type_item_id'] != 0) {
	
					//is this subnet already connected to some interface?
					$ip_subnet_obj 			= new Thelist_Model_ipsubnet($_POST['ip_subnet_id']);
					$connected_interface	= $equipment_obj->get_connected_subnet_interface($ip_subnet_obj);
						
					if ($connected_interface != false) {
							
						if ($_POST['if_id'] != 0) {
								
							if ($connected_interface->get_if_id() != $interface_obj->get_if_id()) {
								throw new exception("interface id ".$connected_interface->get_if_id()." already has this ip address, and user is asking to attach to if_id: ".$_POST['if_id']." same subnet cannot be on multiple interface in the same equipment", 1612);
							}
						}
					} else {
						
						$connected_interface = $interface_obj;
					}
	
					//resolve the mapping type

					Zend_Registry::get('database')->get_thelist_adapter()->beginTransaction();
						
					$ip_addresses	= $ip_subnet_obj->create_ip_addresses($_POST['ip_address_1'], $_POST['ip_address_2']);

					foreach ($ip_addresses as $ip_addresse) {
						$connected_interface->map_new_ip_address($ip_addresse, $_POST['mapping_type_item_id']);
					}
						
					Zend_Registry::get('database')->get_thelist_adapter()->commit();
	
				} else {
					throw new exception('missing required information from selections', 1603);
				}
	
	
			} catch (Exception $e) {
	
				switch($e->getCode()){
	
					case 1603;
					//1603, missing required information
					$this->view->error	= 'missing required information from selections';
					break;
					case 7903;
					case 7904;
					case 7905;
					case 7906;
					$this->view->error	= 'End ip address is before first ip address';
					break;
					case 1612;
					$interface_new 			= new Thelist_Model_equipmentinterface($_POST['if_id']);
					$this->view->error	= "You wanted to attach the ips to ".$interface_new->get_if_name().", but they are already attached to ".$connected_interface->get_if_name()." you must first remove them from the old interface ";
					break;
					case 1613;
					$this->view->error	= 'The subnet is not already connected to an interface on the equipment, please select one';
					break;
					case 1078;
					$this->view->error	= "one of the ips is already mapped to another interface and ".$interface_new->get_if_name()."  is not a VRRP interface";
					break;
					default;
					throw $e;
	
				}
			}
			if ($this->view->error == '') {
	
				echo "<script>
					window.close();
					window.opener.location.reload();
					</script>";
	
			}
				
		} elseif (isset($_GET['if_id']) && !$this->getRequest()->isPost()) {

			$interface_obj 				= new Thelist_Model_equipmentinterface($_GET['if_id']);
			$equipment_obj 				= new Thelist_Model_equipments($interface_obj->get_eq_id());
			$ip_routes					= $equipment_obj->get_ip_routes();
				
			//now we get all the subnets that are mapped to the equipment
			$available_subnets	= $equipment_obj->get_available_subnets();
			$connected_subnets	= $equipment_obj->get_all_connected_subnets();

			if ($available_subnets != false) {
	
				$subnets = "<OPTION value='0'>--- Unused Subnets ---</OPTION>";
	
				foreach($available_subnets as $available_subnet) {
					$subnets .= "<OPTION value='".$available_subnet->get_ip_subnet_id()."'>".$available_subnet->get_ip_subnet_address()."/".$available_subnet->get_ip_subnet_cidr_mask()."</OPTION>";
				}
	
			} else {
				$subnets = "<OPTION value='0'>--- No Unused Subnets Available ---</OPTION>";
			}
				
			if ($connected_subnets != false) {
	
				$subnets .= "<OPTION value='0'></OPTION>";
				$subnets .= "<OPTION value='0'>--- In Use Subnets ---</OPTION>";
					
				foreach($connected_subnets as $connected_subnet) {
					$subnets .= "<OPTION value='".$connected_subnet->get_ip_subnet_id()."'>".$connected_subnet->get_ip_subnet_address()."/".$connected_subnet->get_ip_subnet_cidr_mask()."</OPTION>";
				}
	
			} else {
				$this->view->subnets = "<OPTION value='0'>--- No Available Subnets ---</OPTION>";
			}
				
			$this->view->subnets = 	$subnets;
	
			$sql3 = "SELECT * FROM items
						WHERE item_type='ip_address_map_type'
						ORDER BY item_value
						";
	
			$mapping_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql3);
	
			if (isset($mapping_types['0'])) {
	
				$this->view->mapping_types = "<OPTION value='0'>---Select One---</OPTION>";
	
				foreach($mapping_types as $mapping_type) {
					$this->view->mapping_types .= "<OPTION value='".$mapping_type['item_id']."'>".$mapping_type['item_value']."</OPTION>";
				}
	
			} else {
				$this->view->mapping_types = "<OPTION value='0'>---NO Mapping Types---</OPTION>";
			}
		}
	}
	
	public function getsubnethostipoptionsajaxAction()
	{
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		$ips = '';

		if (isset($_GET['ip_subnet_id'])) {

			if (isset($_GET['get_host_ips'])) {
				
				$subnet 		= new Thelist_Model_ipsubnet($_GET['ip_subnet_id']);
				$ipconvert		= new Thelist_Utility_ipconverter();

				$ip_range = $ipconvert->get_all_ips_in_range($subnet->get_ip_subnet_address(), $subnet->get_ip_broadcast_address());
				
				//remove network
				unset($ip_range['0']);
				
				//remove broadcast
				array_pop($ip_range);
				
				foreach ($ip_range as $single_ip) {
					$ips	.= "<OPTION value='".$single_ip."'>".$single_ip."</OPTION>";
				}

				echo $ips;
			}	
		}
	}
	
	public function manageipaddressesAction()
	{
		$this->_helper->layout->disableLayout();
		$error = '';
		
		if($this->getRequest()->isPost()) {
			
			foreach ($_POST as $ipaddress) {
				
				try {
					
					//an error here should be rolled back
					Zend_Registry::get('database')->get_thelist_adapter()->beginTransaction();
				
					if (is_array($ipaddress)) {
	
						if (isset($ipaddress['if_id'])) {
						
							if (isset($ipaddress['delete'])) {
	
								$interface_obj = new Thelist_Model_equipmentinterface($ipaddress['if_id']);
								$ip_address_obj = $interface_obj->get_ip_address($ipaddress['ip_address_id']);
								$interface_obj->remove_ip_address_map($ip_address_obj);
								
							} else {
								
								$sql2 = "SELECT * FROM ip_address_mapping
										WHERE ip_address_map_id='".$ipaddress['ip_address_map_id']."'
										";
									
								$ip_address_map_detail = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql2);
								
								if ($ip_address_map_detail['if_id'] == $ipaddress['if_id']) {
									
									$interface_obj = new Thelist_Model_equipmentinterface($ipaddress['if_id']);
									$ip_address_obj = $interface_obj->get_ip_address($ipaddress['ip_address_id']);
									$ip_address_obj->update_mapped_mapping_type($ipaddress['ip_address_map_type']);
	
								} else {
									
									$trace  = debug_backtrace();
									$method = $trace[0]["function"];
									$class	= get_class($this);
									
									Zend_Registry::get('database')->set_single_attribute($ipaddress['ip_address_map_id'], 'ip_address_mapping', 'if_id', $ipaddress['if_id'], $class, $method);
									
									//interface is instanciated with each if statement because we change a mapped ip if_id above
									//this would mean we would have to reinstanciate the object since there is no way to do it inside the
									//object structure.
									//we cannot change the mapping if_id from inside the new interface selected by the user, because the ip will not have been
									//mapped at the time of instance.
									
									$interface_obj = new Thelist_Model_equipmentinterface($ipaddress['if_id']);
									$ip_address_obj = $interface_obj->get_ip_address($ipaddress['ip_address_id']);
									$ip_address_obj->update_mapped_mapping_type($ipaddress['ip_address_map_type']);
	
								}
							}
						}
					}
					
					//if we are successful commit the data
					Zend_Registry::get('database')->get_thelist_adapter()->commit();

				} catch (Exception $e) {
				
					switch($e->getCode()){
				
						default;
						Zend_Registry::get('database')->get_thelist_adapter()->rollback();
						throw $e;
				
					}
				}
			}
			
			echo "<script>
				window.close();
				window.opener.location.href='/equipmentconfiguration/manualconfigureequipment?eq_id=".$_POST['eq_id']."';
				</script>";	
		}
		
		if (isset($_GET['eq_id'])) {
			
			$this->view->error = $error;
			
			$this->view->eq_id	= $_GET['eq_id'];
			
			$equipment_obj = new Thelist_Model_equipments($_GET['eq_id']);
			
			
			$this->view->ip_address_map_types = '';
			
			$sql2 = "SELECT * FROM items
					WHERE item_type='ip_address_map_type'
					";
			
			$ip_address_map_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
			
			foreach($ip_address_map_types as $ip_address_map_type) {
					
				$this->view->ip_address_map_types .= "<OPTION value='".$ip_address_map_type['item_id']."'>".$ip_address_map_type['item_value']."</OPTION>";
					
			}
			
			$this->view->interfaces = '';
			
			if (($interfaces = $equipment_obj->get_interfaces()) != null) {
			
				foreach($interfaces as $interface) {
					
					$this->view->interfaces .= "<OPTION value='".$interface->get_if_id()."'>".$interface->get_if_name()."</OPTION>";
						
					if (($ipaddresses = $interface->get_ip_addresses()) != null) {
						
						foreach($ipaddresses as $ipaddress) {
							
							
							$ipaddress_details[] = array(
							
									'ip_address_map_id' => $ipaddress->get_ip_address_map_id(),
									'ipaddress' => $ipaddress->get_ip_address(), 
									'subnetmask' => $ipaddress->get_ip_subnet_dotted_decimal_mask(),
									'current_ip_address_map_type' => "<OPTION value='".$ipaddress->get_ip_address_map_type()."'>".$ipaddress->get_ip_address_map_type_resolved()."</OPTION>",
									'current_if_id' => "<OPTION value='".$interface->get_if_id()."'>".$interface->get_if_name()."</OPTION>",
									'ip_address_id' => $ipaddress->get_ip_address_id(),
							);
						}
					} 
				}
			
				if (isset($ipaddress_details)) {
					
					$this->view->ipaddresses = $ipaddress_details;
					
				}
			}
		}
	}
	
	public function ipaddressdnsmanagementAction()
	{
		$this->_helper->layout->disableLayout();
		$error = '';
		
		if($this->getRequest()->isPost()) {
			
			$this->view->eq_id	= $_POST['eq_id'];
			$this->view->ip_address_id	= $_POST['ip_address_id'];

			try {
				
			foreach($_POST as $dns_record) {
				
				if (is_array($dns_record)) {

					if (isset($dns_record['delete'])) {

						$ip_address_obj = new Thelist_Model_ipaddress($_POST['ip_address_id']);
						$ip_address_obj->delete_dns_record($dns_record['record_id']);

					}
					
					if (isset($dns_record['make_management'])) {
						
						$equipment_obj = new Thelist_Model_equipments($_POST['eq_id']);
						$dns_obj = new Thelist_Model_dnsrecord($dns_record['record_id']);
						
						if ($dns_obj->get_subdomain() != '' && $dns_obj->get_domain() != '' && $dns_obj->get_record_type() == 'A') {
							
							if ($dns_obj->is_unique()) {
								
								//only assigned to a single ip
								$equipment_obj->set_eq_fqdn("".$dns_obj->get_subdomain().".".$dns_obj->get_domain()."");
								
							} else {
								
								throw new exception('this dns record is not unique', 1602);
								
							}
							
						} else {

							throw new exception('the selected dns record for management does not fulfill the requirements to be management fqdn', 1600);
						}
					}
				}
			}	
			
			} catch (Exception $e) {
			
				switch($e->getCode()){
						
					case 1701;
					//1701, trying to delete dns record but it is used as management for equipment
					$error	= 'trying to delete dns record but it is used as management for equipment, first change the management then remove';
					break;
					case 1600;
					//1600, the selected dns record for management does not fulfill the requirements to be management fqdn
					$error	= 'the selected dns record for management does not fulfill the requirements to be management fqdn';
					break;
					case 1602;
					//1602, this dns record has multiple Ips attached, that is not allowed for a management address
					$error	= 'management dns record has multiple Ips attached, that is not allowed for a management fqdn, it must be unique';
					break;
					default;
					throw $e;
						
				}
			}
			
			if ($error == '') {
				
				echo "<script>
					window.close();
					window.opener.location.href='/equipmentconfiguration/manageipaddresses?eq_id=".$_POST['eq_id']."';
					</script>";	

			}
		}
		
		if (isset($_GET['eq_id'])) {

			$this->view->eq_id	= $_GET['eq_id'];
			$this->view->ip_address_id	= $_GET['ip_address_id'];
			$this->view->error = $error;
			
			$ip_address_obj = new Thelist_Model_ipaddress($_GET['ip_address_id']);
			$dns_records	= $ip_address_obj->get_dns_records();
			
			if ($dns_records != null) {
				
				foreach ($dns_records as $dns_record) {
					
					$dns_details[] = array(
						
							'eq_id' => $_GET['eq_id'], 
							'record_id' => $dns_record->get_record_id(),
							'subdomain' => $dns_record->get_subdomain(),
							'domain' => $dns_record->get_domain(), 
							'record_type' => $dns_record->get_record_type(),
					);
				}
			}
			
			if (isset($dns_details)) {
				
				$this->view->dns_records	= $dns_details;
				
			}
		}
	}
	
	public function adddnsrecordAction()
	{
		$this->_helper->layout->disableLayout();
		$error = '';
		
		if($this->getRequest()->isPost()) {
			
			try {
				
				if ($_POST['subdomain'] != '' && $_POST['domain'] != '' && $_POST['recordtype'] != '') {
					
					$ip_address_obj = new Thelist_Model_ipaddress($_POST['ip_address_id']);
					$new_dns_record	= $ip_address_obj->create_dns_record($_POST['subdomain'], $_POST['domain'], $_POST['recordtype']);
					
				} else {
					
					throw new exception('you must provide a domain and a subdomain and a type', 1601);
					
				}
			
			} catch (Exception $e) {
					
				switch($e->getCode()){
			
					case 1601;
					//1601, you must provide a domain and a subdomain
					$error	= 'you must provide a domain and a subdomain';
					break;
					case 1702;
					//1702, record exists
					$error	= 'This DNS record already exist';
					break;
					case 1703;
					//1703, This FQDN is already in use as a management fqdn, these must be unique, use another name
					$error	= 'This FQDN is already in use as a management fqdn, these must be unique, use another name';
					break;
					case 23000;
					//23000, sql integrity error record exists
					$error	= 'This DNS record already exist';
					break;
					default;
					throw $e;
			
				}
			}

		if ($error == '') {
				
				echo "<script>
					window.close();
					window.opener.location.href='/equipmentconfiguration/ipaddressdnsmanagement?eq_id=".$_POST['eq_id']."&ip_address_id=".$_POST['ip_address_id']."';
					</script>";	
				
			} 
		}
		
		if (isset($_GET['eq_id'])) {

			$this->view->eq_id	= $_GET['eq_id'];
			$this->view->ip_address_id	= $_GET['ip_address_id'];
			$this->view->error = $error;
			
			$this->view->dns_record_types = "
						
						<OPTION value='A'>A</OPTION>
						<OPTION value='PTR'>PTR</OPTION>
						";
			
			$sql = 	"SELECT * FROM soa";
				
			$domains	= Zend_Registry::get('database')->get_mydns_adapter()->fetchAll($sql);
			
			$this->view->domains = '';
			
			foreach ($domains as $domain) {
				
				$this->view->domains .= "<OPTION value='".$domain['id']."'>".substr($domain['origin'], 0, -1)."</OPTION>";

			}
		}
	}
	
	public function addinterfaceconfigAction()
	{
		$this->_helper->layout->disableLayout();
		
		if (isset($_GET['if_id']) && !$this->getRequest()->isPost()) {
			
			$interface_obj 		= new Thelist_Model_equipmentinterface($_GET['if_id']);
			
			$available_interface_configurations	= $interface_obj->get_if_type()->get_if_configuration_type_maps();
			
			if ($available_interface_configurations != null) {
				
				$i=0;
				foreach ($available_interface_configurations as $avail_conf) {
					
					$view_array[$i]['if_conf_id']		= $avail_conf->get_if_conf_id();
					$view_array[$i]['if_conf_name']		= $avail_conf->get_if_conf_name();
					
					$i++;
				}
			}
			
			if (isset($view_array)) {
				
				$this->view->available_configurations	= $view_array;
			} else {
				$this->view->error	= 'there are no configs for this interface type';
			}

		} elseif ($this->getRequest()->isPost()) {
				
			$interface_obj 		= new Thelist_Model_equipmentinterface($_POST['if_id']);
			
			//which value is set
			if ($_POST['value2'] != '') {
				$interface_obj->add_new_interface_configuration($_POST['if_conf_id'], $_POST['value2']);
			} elseif (isset($_POST['value1'])) {
				
				if ($_POST['value1'] != '') {
					$interface_obj->add_new_interface_configuration($_POST['if_conf_id'], $_POST['value1']);
				}
			}
			
			echo "<script>
				window.close();
				window.opener.location.reload();
				</script>";	
		}
		
	}
	
	public function getallowedifconfigsajaxAction()
	{
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		if (isset($_GET['if_id']) && isset($_GET['if_conf_id'])) {
			
			$interface_obj 		= new Thelist_Model_equipmentinterface($_GET['if_id']);
			$interface_config 	= new Thelist_Model_interfaceconfiguration($_GET['if_conf_id']);
			$valid_configs		= $interface_config->get_valid_configuration_value_1($interface_obj->get_if_type_id());
			
			if ($valid_configs !== false) {
				
				if ($valid_configs !== true) {
					$configs = "<OPTION value='0'>--- Select One ---</OPTION>";
					
					foreach ($valid_configs as $valid_config) {
					
						$configs .= "<OPTION value='".$valid_config."'>".$valid_config."</OPTION>";
					}
					
				} else {
					//all configs are allowed
					$configs = null;
				}

			} else {
				
				//some configs are allowed, but textbox needed
				$configs = null;
			}
			
			echo $configs;
			
		} else {
			return false;
		}
	}
	
	public function manageinterfaceconfigAction()
	{
		$this->_helper->layout->disableLayout();

		if ($this->getRequest()->isPost()) {

			//any error should be rolled back in the database
			Zend_Registry::get('database')->get_thelist_adapter()->beginTransaction();
			
			try {
				
				$interface_obj 		= new Thelist_Model_equipmentinterface($_POST['if_id']);
				
				foreach($_POST as $index => $interface_config_item) {
			
					if ($index != 'if_id' && $index != 'just_update') {
	
						if ($index == 'configs') {
							
							foreach ($interface_config_item as $single_config) {
								
								if (isset($single_config['delete'])) {
								
									if ($single_config['delete'] == 1) {
										$interface_obj->remove_interface_configuration($single_config['mapped_if_conf_map_id']);
									}
								
								} else {
								
									$current_configs	= $interface_obj->get_interface_configuration($single_config['if_conf_id']);
								
									if ($current_configs != false) {
								
										foreach ($current_configs as $current_config) {
							
											if ($current_config->get_mapped_if_conf_map_id() == $single_config['mapped_if_conf_map_id']) {
												$current_config->set_mapped_configuration_value_1($single_config['if_conf_value_1']);
											}
										}
								
									} else {
										throw new exception("we cannot set the new value for if_conf_map_id: ".$interface_config['mapped_if_conf_map_id']." with new value: ".$interface_config['if_conf_value_1'].", because the interface says that config does not exist", 1616);
									}
								}
							}
							
						} elseif ($index == 'ips') {
							
							foreach ($interface_config_item as $single_ip) {
	
								if (isset($single_ip['delete'])) {
									$ip_address_obj = $interface_obj->get_ip_address($single_ip['ip_address_id']);
									$interface_obj->remove_ip_address_map($ip_address_obj);
								} else {
									$ip_address_obj = $interface_obj->get_ip_address($single_ip['ip_address_id']);
									$ip_address_obj->update_mapped_mapping_type($single_ip['ip_address_map_type']);
								}
							}
						}
					}
				}
				
				//if we are successful commit the data
				Zend_Registry::get('database')->get_thelist_adapter()->commit();
				
			} catch (Exception $e) {
			
				switch($e->getCode()){
			
					default;
					Zend_Registry::get('database')->get_thelist_adapter()->rollback();
					throw $e;
			
				}
			}

			if (!isset($_POST['just_update'])) {
				echo "<script>
				window.close();
				window.opener.location.reload();
				</script>";	
			}
		}
		
		if (isset($_GET['if_id'])) {
		
			$interface_obj 		= new Thelist_Model_equipmentinterface($_GET['if_id']);
			$interface_configs 	= $interface_obj->get_interface_configurations();
	
			if ($interface_configs != null) {
				
				$i=0;
				foreach($interface_configs as $interface_config) {
				
					$configs[$i]['mapped_if_conf_map_id'] 			= $interface_config->get_mapped_if_conf_map_id();
					$configs[$i]['if_conf_name'] 					= $interface_config->get_if_conf_name();
					$configs[$i]['if_conf_value_1'] 				= $interface_config->get_mapped_configuration_value_1();
					$configs[$i]['if_conf_id'] 						= $interface_config->get_if_conf_id();
	
					$i++;
				}
			}
			
			$ipaddresses = $interface_obj->get_ip_addresses();
			
			if ($ipaddresses != null) {
				
				$sql2 = "SELECT * FROM items
						WHERE item_type='ip_address_map_type'
						";
				
				$ip_address_map_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
				
				foreach($ip_address_map_types as $ip_address_map_type) {
					$this->view->ip_address_map_types .= "<OPTION value='".$ip_address_map_type['item_id']."'>".$ip_address_map_type['item_value']."</OPTION>";
				}
			
				foreach($ipaddresses as $ipaddress) {
			
					$ips[] = array(
			
						'ip_address_map_id' => $ipaddress->get_ip_address_map_id(),
						'ipaddress' => $ipaddress->get_ip_address(),
						'subnetmask' => $ipaddress->get_ip_subnet_dotted_decimal_mask(),
						'current_ip_address_map_type' => "<OPTION value='".$ipaddress->get_ip_address_map_type()."'>".$ipaddress->get_ip_address_map_type_resolved()."</OPTION>",
						'ip_address_id' => $ipaddress->get_ip_address_id(),
					);
				}
			}
		
			if (isset($configs['0'])) {
				$this->view->configs = $configs;
			}
			
			if (isset($ips['0'])) {
				
				$this->view->ipaddresses = $ips;
			}
		}
	}

	public function manageinterfacesAction()
	{
		$this->_helper->layout->disableLayout();
		$error = '';
		
		if (isset($_GET['eq_id']) && !$this->getRequest()->isPost()) {
				
			$this->view->eq_id	= $_GET['eq_id'];
			$this->view->error = $error;
			$this->view->interface_types = '';
				
			$sql = "SELECT * FROM interface_types
					ORDER BY if_type, if_type_name
					";
				
			$interface_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
				
			foreach($interface_types as $interface_type) {
		
				$this->view->interface_types .= "<OPTION value='".$interface_type['if_type_id']."'>".$interface_type['if_type_name']."</OPTION>";
		
			}
				
			$this->view->service_points = '';
		
			$sql2 = "SELECT * FROM service_points
					ORDER BY service_point_name
					";
		
			$service_points = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
		
			$this->view->service_points .= "<OPTION value='0'>--- Select One ---</OPTION>";
			
			foreach($service_points as $service_point) {
					
				$this->view->service_points .= "<OPTION value='".$service_point['service_point_id']."'>".$service_point['service_point_name']."</OPTION>";
					
			}
				
			$equipment_obj = new Thelist_Model_equipments($_GET['eq_id']);
				
			if (($interfaces = $equipment_obj->get_interfaces()) != null) {
		
				foreach($interfaces as $interface) {
						
					if ($interface->get_service_point_id() != null) {
		
						$service_point_obj = new Thelist_Model_servicepoint($interface->get_service_point_id());
		
						$current_service_point	= "<OPTION value='".$service_point_obj->get_service_point_id()."'>".$service_point_obj->get_service_point_name()."</OPTION>";
		
					} else {
		
						$current_service_point	= "<OPTION value=''>--- Select One---</OPTION>";
		
					}
						
					$interface_details[] = array(
						
							'if_id' => $interface->get_if_id(), 
							'if_index' => $interface->get_if_index(), 
							'if_name' => $interface->get_if_name(), 
							'current_interface_type' => "<OPTION value='".$interface->get_if_type()->get_if_type_id()."'>".$interface->get_if_type()->get_if_type_name()."</OPTION>", 
							'if_mac_address' => $interface->get_if_mac_address(),
							'current_service_point' => $current_service_point,
						
					);
				}
		
				$this->view->interfaces = $interface_details;
			}
			
		} elseif ($this->getRequest()->isPost()) {

			$this->view->eq_id	= $_POST['eq_id'];
			$equipment_obj = new Thelist_Model_equipments($_POST['eq_id']);

			foreach($_POST as $index => $interface) {
				
				if ($index != 'eq_id') {
					
					if (isset($interface['delete'])) {
						
						if ($interface['delete'] == 1) {
							//run the remove as transaction safe
							$equipment_obj->remove_interface($interface['if_id'], true);
						}

					} else {
						
						$eq_interface	= $equipment_obj->get_interface($interface['if_id']);

						$eq_interface->set_if_type_id($interface['interface_type']);
							
						//patch panels and other non addressable equipment have no mac address
						if ($interface['if_mac_address'] != 'na') {
							$eq_interface->set_if_mac_address($interface['if_mac_address']);
						}
						
						if ($interface['service_point_id'] == 0) {
							$eq_interface->set_service_point_id(null);
						} else {
							$eq_interface->set_service_point_id($interface['service_point_id']);
						}
					}
				}
			}				
			echo "<script>
				window.close();
				window.opener.location.reload();
				</script>";	
		}
	}
	
	public function addinterfaceAction()
	{
		$this->_helper->layout->disableLayout();
		
		$error = '';
		
		if($this->getRequest()->isPost()) {

			$this->view->eq_id	= $_POST['eq_id'];
			
			$equipment_obj = new Thelist_Model_equipments($_POST['eq_id']);
			
			if ($_POST['service_point_id'] == 0) {
				$service_point_id = null;
			} else {
				$service_point_id = $_POST['service_point_id'];
			}
			
			if (is_numeric($_POST['vlan_id'])) {
				
				if ($_POST['master_if_id'] != 0) {
					
					//since this is a vlan  we need to maintain a naming convension, so we disregard the name from the user and create out own
					//same with mac address, interface type
					$master_interface = new Thelist_Model_equipmentinterface($_POST['master_if_id']);
					$new_vlan_if_name	= $master_interface->get_if_name() . "." . $_POST['vlan_id'];
					$new_interface = $equipment_obj->add_new_interface(null, $new_vlan_if_name, 95, $master_interface->get_if_mac_address(), $service_point_id, $master_interface->get_if_id(), true);
					$new_interface->update_default_interface_features();
					$new_interface->update_default_interface_configurations();
					$new_interface->add_new_interface_configuration(22, $_POST['vlan_id']);
					
				} else {
					throw new exception("when creating a vlan you must specify a master physical interface", 1604);
				}
				
				
			} elseif ($_POST['vlan_id'] != '') {
				throw new exception("if you want to create a vlan then the vlan id must be numberic, if not then dont put anything in the box", 1605);
			} else {
			
				if ($_POST['if_name'] != '' && $_POST['if_type_id'] != 0 && ($_POST['if_mac_address'] != '' || $_POST['if_mac_address'] == 'na' )) {
					
					$allow_duplicate_mac = false;
					
					if ($_POST['service_point_id'] == 0) {
						$service_point_id = null;
					} else {
						$service_point_id = $_POST['service_point_id'];
					}
					
					if ($_POST['master_if_id'] == 0) {
						$master_if_id = null;
					} else {
						$master_if_id = $_POST['master_if_id'];
						$allow_duplicate_mac = true;
					}

					try {
					
						//create the interface
						$equipment_obj->add_new_interface(null, $_POST['if_name'], $_POST['if_type_id'], $_POST['if_mac_address'], $service_point_id, $master_if_id, $allow_duplicate_mac);
						
						echo "<script>
							window.close();
							window.opener.location.href='/equipmentconfiguration/manageinterfaces?eq_id=".$_POST['eq_id']."';
							</script>";	
						
					} catch (Exception $e) {
							
						switch($e->getCode()){
					
							case 407;
							//407, duplicate name
							$error	= 'Duplicate Interface name';
							break;
							case 408;
							//408, duplicate mac
							$error	= 'Duplicate Mac address';
							break;
							case 409;
							//409, attempting to create vlan interface but type or id or master interface missing
							$error	= 'Attempting to create vlan interface but type or id or master interface missing';
							break;
							case 800;
							//800, mac address is not formatted correctly
							$error	= 'MAC address is not formatted correctly, only use numbers 0-9 and letters a-f';
							break;
							default;
							throw $e;
					
						}
					}
				}
			}
			echo "<script>
			window.close();
			window.opener.location.reload();
			</script>";	
				
		} elseif (isset($_GET['eq_id'])) {
			
			$this->view->error = $error;
			
			$this->view->eq_id	= $_GET['eq_id'];
			$equipment_obj = new Thelist_Model_equipments($_GET['eq_id']);
			
			$this->view->interfaces = '';
			
			if (($interfaces = $equipment_obj->get_interfaces()) != null) {
				
				foreach($interfaces as $interface) {
					
					$this->view->interfaces .= "<OPTION value='".$interface->get_if_id()."'>".$interface->get_if_name()."</OPTION>";
					
					
				}
			}

			$this->view->interface_types = '';
			
			$sql = "SELECT * FROM interface_types
					ORDER BY if_type, if_type_name
					";
				
			$interface_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
				
			foreach($interface_types as $interface_type) {
			
				$this->view->interface_types .= "<OPTION value='".$interface_type['if_type_id']."'>".$interface_type['if_type_name']."</OPTION>";
			
			}
				
			$this->view->service_points = '';
			
			$sql2 = "SELECT * FROM service_points
					ORDER BY service_point_name
					";
			
			$service_points = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
			
			foreach($service_points as $service_point) {
					
				$this->view->service_points .= "<OPTION value='".$service_point['service_point_id']."'>".$service_point['service_point_name']."</OPTION>";
					
			}
		}
	}
	
	
	public function managecredentialsAction()
	{
		
		$this->_helper->layout->disableLayout();
		
		if($this->getRequest()->isPost()) {

			$equipment_obj = new Thelist_Model_equipments($_POST['1']['eq_id']);
			
			foreach($_POST as $api_id => $api_details) {
				
				$device_cred	= new Thelist_Model_deviceauthenticationcredential();
				
				if (isset($api_details['eq_api_id'])) {

					$device_cred->fill_from_eq_api_id($api_details['eq_api_id']);
					
					if (isset($api_details['delete'])) {
						
						$equipment_obj->delete_api_auth($api_details['eq_api_id']);
						
					} elseif ($device_cred->get_device_username() != $api_details['username'] || $device_cred->get_device_password() != $api_details['password'] || $device_cred->get_device_enablepassword() != $api_details['enablepassword']) {
						
						$device_cred->set_device_user_name($api_details['username']);
						$device_cred->set_device_password($api_details['password']);
						$device_cred->set_device_enablepassword($api_details['enablepassword']);
						
						//update the credential
						$equipment_obj->create_api_auth($device_cred, true);
						
					}
				} else {
					
					if ($api_details['username'] != '' || $api_details['password'] != '' || $api_details['enablepassword'] != '') {
						
						$device_cred->set_api_name($api_details['api_name']);
						$device_cred->set_device_user_name($api_details['username']);
						$device_cred->set_device_password($api_details['password']);
						$device_cred->set_device_enablepassword($api_details['enablepassword']);
							
						//update the credential
						$equipment_obj->create_api_auth($device_cred, false);
						
					}
				}
			}
			
			echo "<script>
								window.close();
								window.opener.location.href='/equipmentconfiguration/manualconfigureequipment?eq_id=".$_POST['1']['eq_id']."';
								</script>";	

				
		} elseif (isset($_GET['eq_id'])) {
			
			$sql =	"SELECT * FROM apis";
			
			$apis = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			$eq_obj = new Thelist_Model_equipments($_GET['eq_id']);
			
			if (isset($apis['0'])) {
			
				$this->view->apis	= '';
				
				foreach ($apis as $api) {
					
					try {
					
						$credential	= $eq_obj->get_credential($api['api_id']);
						
						$this->view->apis	.= "	<tr>
													<td>".$credential->get_device_api_name()."</td>
													<td><input type='text' class='text' name='".$api['api_id']."[username]' value='".$credential->get_device_username()."'></input></td>
													<td><input type='text' class='text' name='".$api['api_id']."[password]' value='".$credential->get_device_password()."'></input></td>
													<td><input type='text' class='text' name='".$api['api_id']."[enablepassword]' value='".$credential->get_device_enablepassword()."'></input></td>
													<td><input type='checkbox' name='".$api['api_id']."[delete]' value='1'></input></td>
													<td><input type='hidden' class='text' name='".$api['api_id']."[eq_api_id]' value='".$credential->get_eq_api_id()."'></input></td>
													<td><input type='hidden' class='text' name='".$api['api_id']."[eq_id]' value='".$eq_obj->get_eq_id()."'></input></td>
													</tr>
													";
						
							
					} catch (Exception $e) {
							
						switch($e->getCode()){
								
						case 402;
						//credential does not exist
						$this->view->apis	.= "	<tr>
													<td>".$api['api_name']."</td>
													<td><input type='text' class='text' name='".$api['api_id']."[username]' value=''></input></td>
													<td><input type='text' class='text' name='".$api['api_id']."[password]' value=''></input></td>
													<td><input type='text' class='text' name='".$api['api_id']."[enablepassword]' value=''></input></td>
													<td></td>
													<td><input type='hidden' class='text' name='".$api['api_id']."[api_name]' value='".$api['api_name']."'></input></td>
													<td><input type='hidden' class='text' name='".$api['api_id']."[eq_id]' value='".$eq_obj->get_eq_id()."'></input></td>
													</tr>
													";
							
							
							break;
							default;
							throw $e;
								
						}
					}
				}
			}
		} 
	}
	
    
    public function getdevicecommandsAction()
    {
    	$this->_helper->layout->disableLayout();
    	
    	if (isset($_GET['device_function_name']) && isset($_GET['primary_key'])) {
    		
    		$device_command_generator_obj		=		new Thelist_Model_devicecommandgenerator();
    		
    		//$device_command_generator_obj->shortest_dynamic_join('equipments', 'interface_features');
    		
    		$this->view->devicecommandxml = $device_command_generator_obj->get_commands_by_function_name_xml($_GET['device_function_name'], $_GET['primary_key']);
    		
    	}

    }
    
    //martin methods
    
    public function devicefunctionsAction()
	{
		$sql = "SELECT * FROM device_functions df
				ORDER BY device_function_id DESC
				";
		$device_functions = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		$this->view->placeholder('device_functions_table')
		->append("<tr class='header'>
						<td class='display' style='width: 50px'>Edit</td>
						<td class='display' style='width: 400px'>Function Description</td>
				</tr>");
		
		foreach($device_functions as $device_function){
			
			$device_function_obj = new Thelist_Model_devicefunction($device_function['device_function_id']);
			
			$this->view->placeholder('device_functions_table')
			->append("<tr>
						<td class='display'><a href='/equipmentconfiguration/editdevicefunction?device_function_id=".$device_function_obj->get_device_function_id()."' >Edit</a></td>
						<td class='display'>".$device_function_obj->get_device_function_desc()."</td>
						</tr>");

			}

	}
    
	public function devicefunctionAction()
	{

		$this->_helper->layout->disableLayout();
		
		//form for adding new option map
		if (!isset($_GET['device_function_id']) && !isset($_POST['create'])) {
			
			$options = array('function_type' => 'add');
	
			$devicefunctionform = new devicefunctionform($options);
			$devicefunctionform->setAction('/equipmentconfiguration/devicefunction');
			$devicefunctionform->setMethod('post');
			$this->view->devicefunctionform=$devicefunctionform;
			
		//submitting a new function
		} else if($this->getRequest()->isPost() && isset($_POST['create'])) {

			$options = array('function_type' => 'add');
			
			$devicefunctionform = new devicefunctionform($options);
			$devicefunctionform->setAction('/equipmentconfiguration/devicefunction');
			$devicefunctionform->setMethod('post');
			$this->view->devicefunctionform=$devicefunctionform;
			
			if ($devicefunctionform->isValid($_POST) && isset($_POST['create'])) {

				$data = array(
		
								'device_function_name'							=>  $_POST['device_function_name'],
								'device_command_parameter_table_id'				=>  $_POST['device_command_parameter_table_id'],
								'device_function_desc' 							=>	$_POST['device_function_desc'],  
								
				);
				
				$trace = debug_backtrace();
				$method = $trace[0]["function"];
		
				$new_device_function = Zend_Registry::get('database')->insert_single_row('device_functions',$data,'DeviceController',$method);
		
				echo "	<script>
						window.close();
						window.opener.location.href='/equipmentconfiguration/editdevicefunction?device_function_id=".$new_device_function."';
						</script>";		
		
			}

		} 
	
	}
	
	public function editdevicefunctionAction()
	{

		$device_function = new Thelist_Model_devicefunction($_GET['device_function_id']);
		
		if (isset($_POST['save'])){
				
			$device_function->set_device_function_desc($_POST['device_function_desc']);
			$device_function->set_device_function_name($_POST['device_function_name']);
			$device_function->set_device_command_parameter_table_id($_POST['device_command_parameter_table_id']);
				
		} 
		
		$html_table = new Thelist_Html_element_htmltable();
		
		if (isset($_GET['device_function_id'])){
			
			$this->view->device_function_name 				= $device_function->get_device_function_name();
			$this->view->device_command_parameter_table_dd	= $html_table->parameter_table_dd($device_function->get_device_command_parameter_table_id());;
			$this->view->device_function_desc 				= $device_function->get_device_function_desc();
			
			$eq_type_software_function_maps = array();
			//header
			$eq_type_software_function_maps['header'] =		"<tr><td colspan='7' height='30px'></td></tr>";
			$eq_type_software_function_maps['header'].=		"<tr><td colspan='6' align='left'><b>Equipment Types and Their Software Versions:</b></td>";
			$eq_type_software_function_maps['header'].=		"<td align='right' style='width:50px'><input class='button' type='button' id='addeqtypesoftwaremap' value='Add Eq Type - Software'></input></td></tr>";
			$eq_type_software_function_maps['header'].=		"<tr class='header'><td>Edit</td><td bgcolor=#FFCC33>Equipment Manufacturer</td><td bgcolor=#FFCC33>Model</td><td bgcolor=#00CCFF>Software Manufacturer</td><td bgcolor=#00CCFF>Package Name</td><td bgcolor=#00CCFF>Architecture</td><td bgcolor=#00CCFF>Version</td></tr>";
			
			//fill the table with current maps
			$device_function_maps = $device_function->get_device_function_maps();
			
			if(is_array($device_function_maps)){
			
			$eq_type_software_function_maps['body'] = '';
			
				foreach($device_function_maps as $device_function_map){

					$eq_type_software_function_maps['body'] .= "<tr><td><a href='/equipmentconfiguration/editdevicefunctionmapping?device_function_map_id=".$device_function_map['device_function_map_id']."'>Edit</a></td>";
					$eq_type_software_function_maps['body'] .= "<td>".$device_function_map['eq_manufacturer']."</td>";
					$eq_type_software_function_maps['body'] .= "<td>".$device_function_map['eq_model_name']."</td>";
					$eq_type_software_function_maps['body'] .= "<td>".$device_function_map['software_package_manufacturer']."</td>";
					$eq_type_software_function_maps['body'] .= "<td>".$device_function_map['software_package_name']."</td>";
					$eq_type_software_function_maps['body'] .= "<td>".$device_function_map['software_package_architecture']."</td>";
					$eq_type_software_function_maps['body'] .= "<td>".$device_function_map['software_package_version']."</td>";
					$eq_type_software_function_maps['body'] .= "</tr>";
				
				}				
		
			}
			//header
			$eq_type_software_function_maps['body2'] = '';
			$eq_type_software_function_maps['body2'] =	"<tr><td colspan='7' height='30px'></td></tr>";
			$eq_type_software_function_maps['body2'].=	"<tr><td colspan='6' align='left'><b>Datasources:</b></td>";
			$eq_type_software_function_maps['body2'].=	"<td align='right' style='width:50px'><input class='button' type='button' device_function_id='".$device_function->get_device_function_id()."' id='adddatasource' value='Add Datasource'></input></td></tr>";
			$eq_type_software_function_maps['body2'].=	"<tr class='header'><td bgcolor=#99FF99>Edit</td><td bgcolor=#99FF99>Name</td><td bgcolor=#99FF99>Counter Type</td><td bgcolor=#99FF99>Step</td><td bgcolor=#99FF99>Heartbeat</td><td bgcolor=#99FF99>Max</td><td bgcolor=#99FF99>Min</td></tr>";

			$device_function_datasources = $device_function->get_monitoring_datasources();
			
			if(is_array($device_function_datasources)){
					
				foreach($device_function_datasources as $device_function_datasource){
			
					$eq_type_software_function_maps['body2'] .= "<tr><td align='right' style='width:50px'><input class='button' type='button' id='editdatasource' device_function_id='".$device_function->get_device_function_id()."' monitoring_ds_id='".$device_function_datasource->get_monitoring_ds_id()."' value='Edit DS'></input></a></td>";
					$eq_type_software_function_maps['body2'] .= "<td>".$device_function_datasource->get_rrd_ds_name()."</td>";
					$eq_type_software_function_maps['body2'] .= "<td>".$device_function_datasource->get_rrd_ds_type_counter()."</td>";
					$eq_type_software_function_maps['body2'] .= "<td>".$device_function_datasource->get_rrd_step()."</td>";
					$eq_type_software_function_maps['body2'] .= "<td>".$device_function_datasource->get_rrd_heartbeat()."</td>";
					$eq_type_software_function_maps['body2'] .= "<td>".$device_function_datasource->get_rrd_max_value()."</td>";
					$eq_type_software_function_maps['body2'] .= "<td>".$device_function_datasource->get_rrd_min_value()."</td>";
					$eq_type_software_function_maps['body2'] .= "</tr>";
			
					$eq_type_software_function_maps['body3'] = '';
					$eq_type_software_function_maps['body3'].=	"<tr><td></td><td colspan='4' align='left'><b>RRAs:</b></td>";
					$eq_type_software_function_maps['body3'].=	"<td align='right' style='width:150px'><input class='button' type='button' monitoring_ds_id='".$device_function_datasource->get_monitoring_ds_id()."' device_function_id='".$device_function->get_device_function_id()."' id='addrratypemap' value='Add RRA'></input></td></tr>";
					$eq_type_software_function_maps['body3'].=	"<tr><td></td><td bgcolor=#CCFF99>Edit</td><td bgcolor=#CCFF99>Consolidation Function</td><td bgcolor=#CCFF99>Acceptable Data Loss</td><td bgcolor=#CCFF99>Data Points Before Consolidation</td><td bgcolor=#CCFF99>Amount Of Data Points</td><td ></td></tr>";
						
					$rra_types = $device_function_datasource->get_rratypes();
					if(is_array($rra_types)){
							
						foreach($rra_types as $rra_type_map){
					
							$eq_type_software_function_maps['body3'] .= "<tr><td></td>";
							$eq_type_software_function_maps['body3'] .= "<td align='right' style='width:50px'><input class='button' type='button' id='editrratypemap' device_function_id='".$device_function->get_device_function_id()."' monitoring_ds_id='".$device_function_datasource->get_monitoring_ds_id()."' value='Edit DS'></input></a></td>";
							$eq_type_software_function_maps['body3'] .= "<td>".$rra_type_map->get_consolidation_function()."</td>";
							$eq_type_software_function_maps['body3'] .= "<td>".$rra_type_map->get_acceptable_data_loss()."</td>";
							$eq_type_software_function_maps['body3'] .= "<td>".$rra_type_map->get_data_points_before_consolidation()."</td>";
							$eq_type_software_function_maps['body3'] .= "<td>".$rra_type_map->get_amount_of_data_points()."</td>";
							$eq_type_software_function_maps['body3'] .= "<td></td>";
							$eq_type_software_function_maps['body3'] .= "</tr>";
					
						}
					
					}
					
			
				}
			
			}
			
			foreach ($eq_type_software_function_maps as $eq_type_software_function_map) {
			
				$this->view->eq_type_software_function_map_list .= $eq_type_software_function_map;
			
			}
			
		} else {
			
			throw new exception('There is not a device_function_id in the $_GET variable for editdevicefunctionAction');
			
		}

	}
	
	public function editdevicefunctionmappingAction()
	{
	
		if (isset($_GET['device_function_map_id'])){
				
			$sql = 	"SELECT * FROM device_function_mapping
					WHERE device_function_map_id='".$_GET['device_function_map_id']."'
					";
			
			$device_function_map = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
			$device_function = new Thelist_Model_devicefunction($device_function_map['device_function_id']);
			
			//get the function information to the front.
			$this->view->device_function_name 				= $device_function->get_device_function_name();
			$this->view->device_command_parameter_table		= $device_function->get_device_function_parameter_table_name();
			$this->view->device_function_desc 				= $device_function->get_device_function_desc();
			
			$eq_type_id = $device_function->get_eq_type_id_of_function_map($_GET['device_function_map_id']);
			
			//get the equipment type information for the front end.
			$eq_type_obj = new Thelist_Model_equipmenttype($eq_type_id);

			$this->view->eq_manufacturer					= $eq_type_obj->get_eq_manufacturer();;
			$this->view->eq_model_name 						= $eq_type_obj->get_eq_model_name();
			
			$software_package_id = $device_function->get_software_package_id_of_function_map($_GET['device_function_map_id']);
				
			//get the software package information to the front.
			$software_package_obj 							= new Thelist_Model_softwarepackage($software_package_id);
			$this->view->software_package_manufacturer 		= $software_package_obj->get_software_package_manufacturer();
			$this->view->software_package_name 				= $software_package_obj->get_software_package_name();
			$this->view->software_package_architecture		= $software_package_obj->get_software_package_architecture();
			$this->view->software_package_version			= $software_package_obj->get_software_package_version();
			
			$device_command_maps = array();
			//header
			$device_command_maps['header'] =		"<tr><td colspan='2' height='30px'></td></tr>";
			$device_command_maps['header'].=		"<tr><td colspan='2' align='left'><b>Commands, Parameters and Regex:</b></td>";
			$device_command_maps['header'].=		"<td align='right' style='width:50px'><input class='button' type='button' id='adddevicecommandmap' value='Add Cmd'></input></td></tr>";
							
			//fill the table with current command maps
			$device_function_command_maps = $device_function->get_device_function_command_maps = $device_function->get_device_function_command_maps($_GET['device_function_map_id']);
				
			if(isset($device_function_command_maps['0'])) {
					
				$device_command_maps['body'] = '';
				$i=0;
				foreach($device_function_command_maps as $device_function_command_map){
					$i++;
					$device_command_obj = new Thelist_Model_devicecommand($device_function_command_map['device_command_id']);
					
					//dont put a spacer on the first command
					if ($i!='1') {
						
						$device_command_maps['body'] .= "<tr><td height='30px'></td></tr>";
						
					}
					
					$device_command_maps['body'] .=	"<tr class='header'><td bgcolor='#006699'>Edit</td><td bgcolor='#006699'>Base Command</td><td bgcolor='#006699'>Order</td></tr>";
					$device_command_maps['body'] .= "<tr><td bgcolor='#0099CC'><input class='button' type='button' id='editdevicecommandmap' device_command_map_id='".$device_function_command_map['device_command_map_id']."' value='Edit Cmd'></input></td>";
					$device_command_maps['body'] .= "<td bgcolor='#0099CC'>".$device_command_obj->get_base_command()."</td>";
					$device_command_maps['body'] .= "<td bgcolor='#0099CC'>".$device_function_command_map['command_exe_order']."</td>";
					$device_command_maps['body'] .= "</tr>";
					
					//set the header so we can add
					$device_command_maps['body'].=	"<tr><td></td><td><table class='display' style='width:1000px;left:0px'><tr class='header'><td bgcolor='#00CCFF'>Edit Cmd Para</td><td bgcolor='#00CCFF'>Parameter Name</td><td bgcolor='#00CCFF'>Table</td><td bgcolor='#00CCFF'>Column</td><td bgcolor='#00CCFF'><input class='button' type='button' id='addcommandparameter' device_command_id='".$device_command_obj->get_device_command_id()."' value='Add Cmd Para'></input></td></tr>";					
					//now fill all the parameters used in this command
					$command_parameters = $device_command_obj->get_device_command_parameters();
					if(isset($command_parameters['0'])){
						
						foreach($command_parameters as $command_parameter){
							
							$device_command_maps['body'] .= "<tr><td><input class='button' type='button' id='editcommandparameter' device_command_parameter_id='".$command_parameter['device_command_parameter_id']."' value='Edit Cmd Para'></input></td>";
							$device_command_maps['body'] .= "<td>".$command_parameter['device_command_parameter_name']."</td>";
							$device_command_maps['body'] .= "<td>".$command_parameter['table_name']."</td>";
							$device_command_maps['body'] .= "<td>".$command_parameter['column_name']."</td>";
	
						}
												
					}
					//end internal table
					$device_command_maps['body'] .= "</table></td></tr>";
					
					//set the header so we at least can add.
					$device_command_maps['body'].=	"<tr><td></td><tr class='header'><td bgcolor='#009933'>Edit Regex</td><td bgcolor='#009933'>Base Regex</td><td bgcolor='#009933'><input class='button' type='button' id='addcommandregex' device_command_id='".$device_command_obj->get_device_command_id()."' value='Add Regex'></input></td></tr>";
					
					//now fill all the regex used in this command
					$command_regexs = $device_command_obj->get_command_regexs();
					
					if($command_regexs != null){
						
						foreach($command_regexs as $command_regex){

							$device_command_maps['body'] .= "<tr><td bgcolor='#00CC66'><input class='button' type='button' id='editcommandregex' command_regex_map_id='".$command_regex->get_command_regex_map_id()."' value='Edit Regex'></input></td>";
							$device_command_maps['body'] .= "<td bgcolor='#00CC66'>/".$command_regex->get_base_regex()."/</td><td bgcolor='#00CC66'></td></tr>";
							
							//set the header so we can add Parameters
							$device_command_maps['body'].=	"<tr><td></td><td><table class='display' style='width:1000px;left:0px'><tr class='header'><td bgcolor='#00FF99'>Edit Regex Para</td><td bgcolor='#00FF99'>Parameter Name</td><td bgcolor='#00FF99'>Table</td><td bgcolor='#00FF99'>Column</td><td bgcolor='#00FF99'><input class='button' type='button' id='addregexparameter' device_command_id='".$device_command_obj->get_device_command_id()."' command_regex_id='".$command_regex->get_command_regex_id()."' value='Add Regex Para'></input></td></tr>";
							//now fill all the parameters used in this regex
							$regex_parameters = $command_regex->get_command_regex_parameters();
							if(isset($regex_parameters['0'])){

								foreach($regex_parameters as $regex_parameter){
										
									$device_command_maps['body'] .= "<tr><td><input class='button' type='button' id='editregexparameter' device_command_id='".$device_command_obj->get_device_command_id()."' command_regex_parameter_id='".$regex_parameter['command_regex_parameter_id']."' value='Edit Regex Para'></input></td>";
									$device_command_maps['body'] .= "<td>".$regex_parameter['command_regex_parameter_name']."</td>";
									$device_command_maps['body'] .= "<td>".$regex_parameter['table_name']."</td>";
									$device_command_maps['body'] .= "<td>".$regex_parameter['column_name']."</td>";
							
								}
							
							}

							//end internal table
							$device_command_maps['body'] .= "</table></td></tr>";
							
						}
					
					}

				}
	
			}
				
			foreach ($device_command_maps as $device_command_map) {
					
				$this->view->device_command_map_table .= $device_command_map;
					
			}
				
		} else {
				
			throw new exception('There is not a device_function_map_id in the $_GET variable for editdevicefunctionmappingAction');
				
		}
		
		
	}
	
	public function devicecommandmappingAction() 
	{
		$this->_helper->layout->disableLayout();

		//form editing a command map THE FORM VALIDATIONS ARE NOT ENABLED NEED HELP UNDERSTANDING WHY THEY DO NOT WORK
		if (isset($_GET['device_command_map_id'])) {
				
			$options = array('function_type' => 'edit', 'variable' => "".$_GET['device_command_map_id']."");
			
			$devicecommandmapform = new Thelist_Equipmentconfigurationform_devicecommandmapform($options);
			$devicecommandmapform->setAction('/equipmentconfiguration/devicecommandmapping');
			$devicecommandmapform->setMethod('post');
			$this->view->devicecommandmapform=$devicecommandmapform;
				
			//submitting a new function
		} else if (isset($_GET['device_function_map_id'])) {
				
			$options = array('function_type' => 'add', 'variable' => "".$_GET['device_function_map_id']."");
			
			$devicecommandmapform = new Thelist_Equipmentconfigurationform_devicecommandmapform($options);
			$devicecommandmapform->setAction('/equipmentconfiguration/devicecommandmapping');
			$devicecommandmapform->setMethod('post');
			$this->view->devicecommandmapform=$devicecommandmapform;
				
			//submitting a new function
		} 

		if($this->getRequest()->isPost()) {
			
			if (isset($_POST['edit'])) {
				
				$options = array('function_type' => 'edit', 'variable' => "".$_POST['device_command_map_id']."");
				$devicecommandmapform = new Thelist_Equipmentconfigurationform_devicecommandmapform($options);
				
				if ($devicecommandmapform->isValid($_POST)) {

					$device_command_obj = new Thelist_Model_devicecommand($_POST['device_command_id']);
					
					//set the new value
					$device_command_obj->set_base_command($_POST['base_command']);
					$device_command_obj->set_command_exe_order($_POST['device_command_map_id'], $_POST['command_exe_order']);
					$device_command_obj->set_command_api($_POST['api_id']);
					
					echo 	"<script>
							window.close();
							window.opener.location.href='/equipmentconfiguration/editdevicefunctionmapping?device_function_map_id=".$_POST['device_function_map_id']."';
							</script>";	
					
					
				}

			} elseif (isset($_POST['delete'])) {
				
				$options = array('function_type' => 'edit', 'variable' => "".$_POST['device_command_map_id']."");
				$devicecommandmapform = new Thelist_Equipmentconfigurationform_devicecommandmapform($options);
				
				if ($devicecommandmapform->isValid($_POST)) {

					$device_command_obj = new Thelist_Model_devicecommand($_POST['device_command_id']);
					
					//delete the mapping
					$device_command_obj->remove_command_to_device_function_map($_POST['device_command_map_id']);
										
					echo 	"<script>
							window.close();
							window.opener.location.href='/equipmentconfiguration/editdevicefunctionmapping?device_function_map_id=".$_POST['device_function_map_id']."';
							</script>";	
					
					
				}

			} elseif (isset($_POST['create'])) {

				$options = array('function_type' => 'add', 'variable' => "".$_POST['device_function_map_id']."");
				$devicecommandmapform = new Thelist_Equipmentconfigurationform_devicecommandmapform($options);
				
				//a new command should win over a selection in the dropdown.
					if ($_POST['new_base_command'] != '') {
						
						//create the new command
						$data = array(
		
								'base_command'							=>  $_POST['new_base_command'],
								'api_id'								=>	$_POST['api_id'],
						);
						$new_device_command_id = Zend_Registry::get('database')->insert_single_row('device_commands',$data,'DeviceController','devicecommandmapping');
						
						//map the new command to the function map
						$device_command_obj = new Thelist_Model_devicecommand($new_device_command_id);
						$device_command_obj->map_command_to_device_function($_POST['device_function_map_id'], $_POST['command_exe_order']);
						
					} 

					echo 	"<script>
							window.close();
							window.opener.location.href='/equipmentconfiguration/editdevicefunctionmapping?device_function_map_id=".$_POST['device_function_map_id']."';
							</script>";	

			}
		}

	}
	
	public function commandparameterAction()
	{
		$this->_helper->layout->disableLayout();
	
		//form editing a command map
		if (isset($_GET['device_command_parameter_id'])) {
	
			$options = array('function_type' => 'edit', 'variable' => "".$_GET['device_command_parameter_id']."", 'device_function_map_id' => "".$_GET['device_function_map_id']."");
				
			$commandparameterform = new Thelist_Equipmentconfigurationform_commandparameterform($options);
			$commandparameterform->setAction('/equipmentconfiguration/commandparameter');
			$commandparameterform->setMethod('post');
			$this->view->commandparameterform=$commandparameterform;
	
			//submitting a new function
		} else if (isset($_GET['device_function_map_id'])) {
	
			$options = array('function_type' => 'add', 'variable' => "".$_GET['device_command_id']."", 'device_function_map_id' => "".$_GET['device_function_map_id']."");
				
			$commandparameterform = new Thelist_Equipmentconfigurationform_commandparameterform($options);
			$commandparameterform->setAction('/equipmentconfiguration/commandparameter');
			$commandparameterform->setMethod('post');
			$this->view->commandparameterform=$commandparameterform;
	
			//submitting a new function
		}
	
		if($this->getRequest()->isPost()) {
				
			if (isset($_POST['edit'])) {
	
					$device_command_obj = new Thelist_Model_devicecommand($_POST['device_command_id']);

					//update to the new values
					$device_command_obj->set_command_parameter($_POST['device_command_parameter_name'], $_POST['device_command_parameter_column_id'], $_POST['device_command_parameter_id']);

					echo 	"<script>
							window.close();
							window.opener.location.href='/equipmentconfiguration/editdevicefunctionmapping?device_function_map_id=".$_POST['device_function_map_id']."';
							</script>";	
	
			} elseif (isset($_POST['delete'])) {
	
					$device_command_obj = new Thelist_Model_devicecommand($_POST['device_command_id']);
					$device_command_obj->remove_command_parameter($_POST['device_command_parameter_id']);
					
					echo 	"<script>
							window.close();
							window.opener.location.href='/equipmentconfiguration/editdevicefunctionmapping?device_function_map_id=".$_POST['device_function_map_id']."';
							</script>";	
		
			} elseif (isset($_POST['create'])) {

				$device_command_obj = new Thelist_Model_devicecommand($_POST['device_command_id']);

					//create new parameter
					$device_command_obj->set_command_parameter($_POST['device_command_parameter_name'], $_POST['device_command_parameter_column_id']);

					echo 	"<script>
							window.close();
							window.opener.location.href='/equipmentconfiguration/editdevicefunctionmapping?device_function_map_id=".$_POST['device_function_map_id']."';
							</script>";	
			}
		}	
	}
	
	public function commandregexmappingAction()
	{
		$this->_helper->layout->disableLayout();
	
		//form editing a regex map
		if (isset($_GET['command_regex_map_id'])) {
	
			$options = array('function_type' => 'edit', 'variable' => "".$_GET['command_regex_map_id']."", 'device_function_map_id' => "".$_GET['device_function_map_id']."");
				
			$commandregexmapform = new Thelist_Equipmentconfigurationform_commandregexmapform($options);
			$commandregexmapform->setAction('/equipmentconfiguration/commandregexmapping');
			$commandregexmapform->setMethod('post');
			$this->view->commandregexmapform=$commandregexmapform;
	
		//submitting a new regex
		} else if (isset($_GET['device_command_id'])) {
	
			$options = array('function_type' => 'add', 'variable' => "".$_GET['device_command_id']."", 'device_function_map_id' => "".$_GET['device_function_map_id']."");
				
			$commandregexmapform = new Thelist_Equipmentconfigurationform_commandregexmapform($options);
			$commandregexmapform->setAction('/equipmentconfiguration/commandregexmapping');
			$commandregexmapform->setMethod('post');
			$this->view->commandregexmapform=$commandregexmapform;
	
			//submitting a new function
		}
	
		if($this->getRequest()->isPost()) {
			
			if (isset($_POST['edit'])) {
				
				$options = array('function_type' => 'edit', 'variable' => "".$_POST['command_regex_map_id']."", 'device_function_map_id' => "".$_POST['device_function_map_id']."");
				$commandregexmapform = new Thelist_Equipmentconfigurationform_commandregexmapform($options);

				if ($commandregexmapform->isValid($_POST)) {

					$device_command_obj = new Thelist_Model_devicecommand($_POST['device_command_id']);
						
					//set the new value
					$command_regexs = $device_command_obj->get_command_regexs();
					
					foreach ($command_regexs as $command_regex) {
						
						if ($command_regex->get_command_regex_map_id() == $_POST['command_regex_map_id']) {

							$command_regex->set_base_regex($_POST['base_regex']);
							$command_regex->set_command_regex_match($_POST['match_yes_or_no']);
							$command_regex->set_command_regex_replace($_POST['replace_yes_or_no']);
							
						}

					}
											
					echo 	"<script>
							window.close();
							window.opener.location.href='/equipmentconfiguration/editdevicefunctionmapping?device_function_map_id=".$_POST['device_function_map_id']."';
							</script>";	
						
						
				}
	
			} elseif (isset($_POST['delete'])) {
	
				$options = array('function_type' => 'edit', 'variable' => "".$_POST['command_regex_map_id']."", 'device_function_map_id' => "".$_POST['device_function_map_id']."", 'device_command_id' => "".$_POST['device_command_id']."");
				$commandregexmapform = new Thelist_Equipmentconfigurationform_commandregexmapform($options);
	
				if ($commandregexmapform->isValid($_POST)) {
	
					$device_command_obj = new Thelist_Model_devicecommand($_POST['device_command_id']);

					//delete the row
					$command_regexs = $device_command_obj->get_command_regexs();
						
					foreach ($command_regexs as $command_regex) {
					
						if ($command_regex->get_command_regex_map_id() == $_POST['command_regex_map_id']) {
							
							$command_regex->remove_regex_to_device_command_map($_POST['command_regex_map_id']);
								
						}
					
					}
	
					echo 	"<script>
								window.close();
								window.opener.location.href='/equipmentconfiguration/editdevicefunctionmapping?device_function_map_id=".$_POST['device_function_map_id']."';
								</script>";	
						
						
				}
	
			} elseif (isset($_POST['create'])) {

				$options = array('function_type' => 'add', 'variable' => "".$_POST['device_command_id']."", 'device_function_map_id' => "".$_POST['device_function_map_id']."");
				$devicecommandmapform = new Thelist_Equipmentconfigurationform_devicecommandmapform($options);
					
				//a new regex should win over a selection in the dropdown.
				if ($_POST['new_base_regex'] != '') {

					//create the new command
					$data = array(
	
									'base_regex'							=>  $_POST['new_base_regex'],
									'match_yes_or_no'						=>  $_POST['match_yes_or_no'],
									'replacement_regex'						=>	$_POST['replace_yes_or_no']
									);
					$new_command_regex_id = Zend_Registry::get('database')->insert_single_row('command_regexs',$data,'DeviceController','commandregexmapping');

					//map the new command to the function map
					$device_command_obj = new Thelist_Model_devicecommand($_POST['device_command_id']);
					$device_command_obj->map_new_regex_to_device_command($new_command_regex_id);
	
				} 
				
				echo 	"<script>
						window.close();
						window.opener.location.href='/equipmentconfiguration/editdevicefunctionmapping?device_function_map_id=".$_POST['device_function_map_id']."';
						</script>";	
	
			}
		}
	
	}
	
	public function commandregexparameterAction()
	{
		$this->_helper->layout->disableLayout();
	
		//form editing a command map
		if (isset($_GET['command_regex_parameter_id'])) {
	
			$options = array('function_type' => 'edit', 'variable' => "".$_GET['command_regex_parameter_id']."", 'device_function_map_id' => "".$_GET['device_function_map_id']."", 'device_command_id' => "".$_GET['device_command_id']."");
	
			$commandregexparameterform = new Thelist_Equipmentconfigurationform_commandregexparameterform($options);
			$commandregexparameterform->setAction('/equipmentconfiguration/commandregexparameter');
			$commandregexparameterform->setMethod('post');
			$this->view->commandregexparameterform=$commandregexparameterform;
	
			//submitting a new function
		} else if (isset($_GET['command_regex_id'])) {
	
			$options = array('function_type' => 'add', 'variable' => "".$_GET['command_regex_id']."", 'device_function_map_id' => "".$_GET['device_function_map_id']."", 'device_command_id' => "".$_GET['device_command_id']."");
	
			$commandregexparameterform = new Thelist_Equipmentconfigurationform_commandregexparameterform($options);
			$commandregexparameterform->setAction('/equipmentconfiguration/commandregexparameter');
			$commandregexparameterform->setMethod('post');
			$this->view->commandregexparameterform=$commandregexparameterform;
	
			//submitting a new function
		}
	
		if($this->getRequest()->isPost()) {
			
			if (isset($_POST['edit'])) {
	
				$device_command_obj = new Thelist_Model_devicecommand($_POST['device_command_id']);
	
				//update to the new values
				$device_command_obj->set_commandregex_parameter($_POST['command_regex_id'],$_POST['command_regex_parameter_name'], $_POST['device_command_parameter_column_id'], $_POST['command_regex_parameter_id']);
				
				echo 	"<script>
								window.close();
								window.opener.location.href='/equipmentconfiguration/editdevicefunctionmapping?device_function_map_id=".$_POST['device_function_map_id']."';
								</script>";	
	
			} elseif (isset($_POST['delete'])) {
	
				$device_command_obj = new Thelist_Model_devicecommand($_POST['device_command_id']);
				$device_command_obj->remove_commandregex_parameter($_POST['command_regex_id'], $_POST['command_regex_parameter_id']);
					
				echo 	"<script>
								window.close();
								window.opener.location.href='/equipmentconfiguration/editdevicefunctionmapping?device_function_map_id=".$_POST['device_function_map_id']."';
								</script>";	
	
			} elseif (isset($_POST['create'])) {

				$device_command_obj = new Thelist_Model_devicecommand($_POST['device_command_id']);
	
				//create new parameter
				$device_command_obj->set_commandregex_parameter($_POST['command_regex_id'],$_POST['command_regex_parameter_name'], $_POST['device_command_parameter_column_id']);
	
				echo 	"<script>
								window.close();
								window.opener.location.href='/equipmentconfiguration/editdevicefunctionmapping?device_function_map_id=".$_POST['device_function_map_id']."';
								</script>";	
			}
		}
	}
}
?>