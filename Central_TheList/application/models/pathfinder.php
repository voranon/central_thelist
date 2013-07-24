<?php 

class thelist_model_pathfinder
{

	private $database;
	private $_successful_paths=null;
	private $_successful_path_count=0;
	private $_path_id=null;
	private $_general_path_limitation_array=array();
	private $_general_interface_feature_array=array();
	private $_path_array=array();
	private $test='0';
	
	public function __construct()
	{


		
	
		//$equipment_role_obj is a single role we are looking for
		//$start_interface_obj is array of interface objects that will be the starting point for the walk.
		
		//$interface_feature_obj is array of features that must have an originator in the path for it to be valid.
		//that implies that we continue paths that allow passthrough of a feature, but in the end the path will
		//only be returned if all required PHYSICAL features have an originator in the path. there cannot be a gap in these features
		//i.e. swm - swm tne no swn and then swm again. the path will be killed off at the no swm point.
		//however if dataframe and ethernet is required and we find the ethernet originator then we no longer care if ethernet is supported by the
		//devices in the remaining path, because we found the originator already.
		//however we still very much care that the dataframe feature is available all the way through because it is a SERVICE
		//this will allow us to locate physical traiths that must be present for the interface we hope to connect to.
		
		//$path_limitations an object of limits that will be expanded as we use this function. it will hold i.e. eq_type_ids or a
		//limit that states the path must not include any interfaces in a service point or only first interface can be service point interface.
		
		//most of the logic here is done using the database directly to keep the app light and fast, equipment objects are memory intensive and would be called alot.
		
	}
	
	private function new_path_tracking_array($interface_obj)
	{
		
		$this->_path_id++;
		//start the path for the new interface
		$this->_path_array[$this->_path_id]['path_id_number']	= $this->_path_id;
			
		//this variable will keep track of all the interface ids we start from when moving from interface to interface
		$this->_path_array[$this->_path_id]['ini_if_tracker']	= $interface_obj->get_if_id();
			
		//this variable will keep track of all the interface ids on the other side when moving from interface to interface
		$this->_path_array[$this->_path_id]['recv_if_tracker']	= '';
			
		//this variable will keep track of all the interface ids both initiating and receiving
		$this->_path_array[$this->_path_id]['total_if_tracker']	= $interface_obj->get_if_id();
			
		//this variable will keep track of all the eq ids we encounter
		$this->_path_array[$this->_path_id]['total_eq_tracker']	= $interface_obj->get_eq_id();
			
		//this variable will keep track of the last interface we encountered
		$this->_path_array[$this->_path_id]['last_if']	= $interface_obj->get_if_id();
			
		//this variable will keep track of the next interface we have to probe
		$this->_path_array[$this->_path_id]['next_if']	= '';
			
		//this variable will keep track of the last equipment we encountered
		$this->_path_array[$this->_path_id]['last_eq']	= $interface_obj->get_eq_id();
		
		//this variable will keep track of the next equipment we have to probe
		$this->_path_array[$this->_path_id]['next_eq']	= '';
			
		//this will tell us the status of a path
		$this->_path_array[$this->_path_id]['successful_path'] = 'no';
		
		//use the general variables from above
		if (isset($this->_general_interface_feature_array['physical'])) {
		
			$this->_path_array[$this->_path_id]['interface_features']['physical'] = $this->_general_interface_feature_array['physical'];
		
		}
			
		if (isset($this->_general_interface_feature_array['service'])) {
		
			$this->_path_array[$this->_path_id]['interface_features']['service'] = $this->_general_interface_feature_array['service'];
		}
		
		if (isset($this->_general_interface_feature_array['feature_ids_string'])) {
		
			$this->_path_array[$this->_path_id]['feature_ids_string']	= $this->_general_interface_feature_array['feature_ids_string'];
		
		}
		
		return $interface_obj;

	}
	
	
	
	public function get_paths_to_equipment_role($equipment_role_obj, $start_interface_obj, $interface_feature_obj, $path_limitations)
	{

		//clear the variables
		$this->_successful_paths=array();
		
		$this->create_general_array_of_limitations($path_limitations);
		$this->create_general_array_of_interface_features($interface_feature_obj);

		$r=0;
		$this->_path_id=0;
		foreach ($start_interface_obj as $interface_obj) {
			
			//unset the variables used in the loop, many depend on isset and if we do not reset them
			//they continue, causing havoc
			if (isset($next_dst_hops)) {
				unset($next_dst_hops);
			}
			if (isset($if_available_features)) {
				unset($if_available_features);
			}
			if (isset($new_connecting_ifs)) {
				unset($new_connecting_ifs);
			}
			
			if($path_limitations->get_check_if_first_interface_is_originator() == '1') {

				$sql = "SELECT equipment_role_id FROM interfaces i
						INNER JOIN equipment_role_mapping erm ON erm.eq_id=i.eq_id
						WHERE i.if_id='".$interface_obj->get_if_id()."'
						";
				
				$roles	= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
				
				if (isset($roles['0'])) {
					
					foreach ($roles as $role) {
							
						if ($role['equipment_role_id'] == $equipment_role_obj->get_equipment_role_id()){
							
							$this->_successful_paths[$this->_successful_path_count]	= new Thelist_Model_path($interface_obj->get_if_id());
							
							$this->_successful_path_count++;
							
							//we dont want to find anymore interfaces because we have met our goal
							break;
						}
					}
				}
			}

			//create a new path tracker
			$interface = $this->new_path_tracking_array($interface_obj);
			
			//now lets find the next set of interfaces to probe
			$sql_start = 	"SELECT ifm.if_id AS dst_if_id, i.eq_id AS dst_eq, ifm.if_feature_id AS dst_feature_id, ifm.if_feature_value AS dst_feature_value, ic.if_id_b AS src_if_id, e.eq_id AS src_eq, ifm2.if_feature_id AS src_feature_id, ifm2.if_feature_value AS src_feature_value, erm.equipment_role_id AS src_role_id, erm2.equipment_role_id AS dst_role_id
							FROM interface_connections ic
							INNER JOIN interface_feature_mapping ifm ON ifm.if_id=ic.if_id_a
							INNER JOIN interface_feature_mapping ifm2 ON ifm2.if_id=ic.if_id_b
							INNER JOIN interfaces i ON i.if_id=ic.if_id_a
							INNER JOIN interfaces i2 ON i2.if_id=ic.if_id_b
							INNER JOIN equipments e ON e.eq_id=i2.eq_id
							INNER JOIN equipments e2 ON e2.eq_id=i.eq_id
							LEFT OUTER JOIN equipment_role_mapping erm ON erm.eq_id=e.eq_id
							LEFT OUTER JOIN equipment_role_mapping erm2 ON erm2.eq_id=e2.eq_id
							";
			
					//if we only allowing the path include equipment in certain unit groups
			if (isset($this->_general_path_limitation_array['unit_groups'])) {
			
				$sql_start .=	" INNER JOIN equipment_mapping em ON em.eq_id=e2.eq_id \n";
				$sql_start .=	" INNER JOIN unit_group_mapping ugm ON ugm.unit_id=em.unit_id \n";
			}
			
			//Where statement matching aganst ic b column	
			$sql_start .=	"WHERE ic.if_id_b='".$interface->get_if_id()."'";
			
			
			//if we are looking for features then append this
			if (isset($this->_path_array[$this->_path_id]['feature_ids_string'])) {
				
				$sql_start .= 	" AND ifm.if_feature_id IN (".$this->_path_array[$this->_path_id]['feature_ids_string'].") \n";
				$sql_start .= 	" AND ifm2.if_feature_id IN (".$this->_path_array[$this->_path_id]['feature_ids_string'].") \n";
				
			}
			
			//if we not allowing the path to traverse some types of equipment, then append this
			if (isset($this->_general_path_limitation_array['deny_eq_type_ids'])) {
					
				$sql_start .= 	" AND e2.eq_type_id NOT IN (".$this->_general_path_limitation_array['deny_eq_type_ids'].") \n";
	
			}
			 
			//if we not allowing the first interface to belong to some types of equipment, then append this. notice e and not e2
			if ($path_limitations->get_verify_first_interface_equipment_eq_type() == '1') {
				
				$sql_start .= 	" AND e.eq_type_id NOT IN (".$this->_general_path_limitation_array['deny_eq_type_ids'].") \n";
			
			}
			
			//if we only allowing the path include equipment in certain unit groups
			if (isset($this->_general_path_limitation_array['unit_groups'])) {
					
				$sql_start .= 	" AND ugm.unit_group_id IN (".$this->_general_path_limitation_array['unit_groups'].") \n";
				$sql_start .= 	" AND em.eq_map_deactivated IS NULL \n";	
			}
			
			//center of the union
			$sql_start .= 	"\n UNION ALL \n\n";
			
			$sql_start .=	"SELECT ifm.if_id AS dst_if_id, i.eq_id AS dst_eq, ifm.if_feature_id AS dst_feature_id, ifm.if_feature_value AS dst_feature_value, ic.if_id_a AS src_if_id, e.eq_id AS src_eq, ifm2.if_feature_id AS src_feature_id, ifm2.if_feature_value AS src_feature_value, erm.equipment_role_id AS src_role_id, erm2.equipment_role_id AS dst_role_id
							FROM interface_connections ic
							INNER JOIN interface_feature_mapping ifm ON ifm.if_id=ic.if_id_b
							INNER JOIN interface_feature_mapping ifm2 ON ifm2.if_id=ic.if_id_a
							INNER JOIN interfaces i ON i.if_id=ic.if_id_b
							INNER JOIN interfaces i2 ON i2.if_id=ic.if_id_a
							INNER JOIN equipments e ON e.eq_id=i2.eq_id
							INNER JOIN equipments e2 ON e2.eq_id=i.eq_id
							LEFT OUTER JOIN equipment_role_mapping erm ON erm.eq_id=e.eq_id
							LEFT OUTER JOIN equipment_role_mapping erm2 ON erm2.eq_id=e2.eq_id
							";
			
			//if we only allowing the path include equipment in certain unit groups
			if (isset($this->_general_path_limitation_array['unit_groups'])) {
			
				$sql_start .=	" INNER JOIN equipment_mapping em ON em.eq_id=e2.eq_id \n";
				$sql_start .=	" INNER JOIN unit_group_mapping ugm ON ugm.unit_id=em.unit_id \n";
			}
			
			//Where statement matching aganst ic a column		
			$sql_start .=	"WHERE ic.if_id_a='".$interface->get_if_id()."'";
			
			//if we are looking for features then append this
			if (isset($this->_path_array[$this->_path_id]['feature_ids_string'])) {
			
				$sql_start .= 	" AND ifm.if_feature_id IN (".$this->_path_array[$this->_path_id]['feature_ids_string'].") \n";
				$sql_start .= 	" AND ifm2.if_feature_id IN (".$this->_path_array[$this->_path_id]['feature_ids_string'].") \n";
			
			}
			
			//if we not allowing the path to traverse some types of equipment, then append this
			if (isset($this->_general_path_limitation_array['deny_eq_type_ids'])) {
					
				$sql_start .= 	" AND e2.eq_type_id NOT IN (".$this->_general_path_limitation_array['deny_eq_type_ids'].") \n";
	
			}
			
			//if we not allowing the first interface to belong to some types of equipment, then append this. notice e and not e2
			if ($path_limitations->get_verify_first_interface_equipment_eq_type() == '1') {
				
				$sql_start .= 	" AND e.eq_type_id NOT IN (".$this->_general_path_limitation_array['deny_eq_type_ids'].") \n";
			
			}
			
			//if we only allowing the path include equipment in certain unit groups
			if (isset($this->_general_path_limitation_array['unit_groups'])) {
					
				$sql_start .= 	" AND ugm.unit_group_id IN (".$this->_general_path_limitation_array['unit_groups'].") \n";
				$sql_start .= 	" AND em.eq_map_deactivated IS NULL \n";
			
			}
			
			$new_connecting_ifs	= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql_start);
			
			//turn the features for src and dst into strings per interface
			if (isset($new_connecting_ifs['0'])) {

				$i=0;
				foreach($new_connecting_ifs as $new_connecting_if){
				
					//have we found an originator in the source and does the limitation object tell us to look to the originator on the interface we feed the system?
					if ($new_connecting_if['src_feature_value'] != null && $path_limitations->get_check_if_first_interface_is_originator() == '1' && isset($this->_path_array[$this->_path_id]['interface_features']['physical'])) {
					
						foreach ($this->_path_array[$this->_path_id]['interface_features']['physical'] as $single_physical_feature_id => $single_physical_feature_value ) {
							
							if ($single_physical_feature_value == 'no_originator_found_yet' && $single_physical_feature_id == $new_connecting_if['src_feature_id']) 
							
								$this->_path_array[$this->_path_id]['interface_features']['physical'][$single_physical_feature_id] = $new_connecting_if['src_feature_value'];
							}
					}
					
					//have we found an originator in the equipment on the otherside (next hop)
					if ($new_connecting_if['dst_feature_value'] != null && isset($this->_path_array[$this->_path_id]['interface_features']['physical'])) {
							
						foreach ($this->_path_array[$this->_path_id]['interface_features']['physical'] as $single_physical_feature_id => $single_physical_feature_value ) {
								
							if ($single_physical_feature_value == 'no_originator_found_yet' && $single_physical_feature_id == $new_connecting_if['dst_feature_id'])
								
							$this->_path_array[$this->_path_id]['interface_features']['physical'][$single_physical_feature_id] = $new_connecting_if['dst_feature_value'];
						}
					}
					
					//create string of source roles equipment if there are any
					if ($new_connecting_if['src_role_id'] != null) {
						
						if (!isset($if_available_features[$new_connecting_if['src_if_id']]['src_eq_roles'])) {
								
							$if_available_features[$new_connecting_if['src_if_id']]['src_eq_roles']  = ',';
								
						}
						
						if (!preg_match("/,".$new_connecting_if['src_role_id'].",/", $if_available_features[$new_connecting_if['src_if_id']]['src_eq_roles'], $empty)) {
							$if_available_features[$new_connecting_if['src_if_id']]['src_eq_roles'] .= $new_connecting_if['src_role_id'].",";
						}
					}
					
					//create string of destination roles equipment if there are any
					if ($new_connecting_if['dst_role_id'] != null) {
						
						if (!isset($if_available_features[$new_connecting_if['dst_if_id']]['dst_eq_roles'])) {
								
							$if_available_features[$new_connecting_if['dst_if_id']]['dst_eq_roles']  = ',';
								
						}
						
						//limit the array to only include unique role entries
							
						if (!preg_match("/,".$new_connecting_if['dst_role_id'].",/", $if_available_features[$new_connecting_if['dst_if_id']]['dst_eq_roles'], $empty)) {
							$if_available_features[$new_connecting_if['dst_if_id']]['dst_eq_roles'] .= $new_connecting_if['dst_role_id'].",";
						}
					}

					//create string of available features for the interface if there are any
					if (!isset($if_available_features[$new_connecting_if['src_if_id']]['src_feats'])) {
						
						$if_available_features[$new_connecting_if['src_if_id']]['src_feats']  = ',';
						$if_available_features[$new_connecting_if['src_if_id']]['query_data'] = $new_connecting_if;
						
					}
					
					if (!isset($if_available_features[$new_connecting_if['dst_if_id']]['dst_feats'])) {
					
						$if_available_features[$new_connecting_if['dst_if_id']]['dst_feats']  = ',';
						$if_available_features[$new_connecting_if['dst_if_id']]['query_data'] = $new_connecting_if;
					
					}
					
					//limit the array to only include unique entries, we get many duplicates because of the query we are doing
					if (!preg_match("/,".$new_connecting_if['src_feature_id'].",/", $if_available_features[$new_connecting_if['src_if_id']]['src_feats'], $empty)) {
						$if_available_features[$new_connecting_if['src_if_id']]['src_feats'] .= $new_connecting_if['src_feature_id'].",";
					}
					if (!preg_match("/,".$new_connecting_if['dst_feature_id'].",/", $if_available_features[$new_connecting_if['dst_if_id']]['dst_feats'], $empty)) {
						$if_available_features[$new_connecting_if['dst_if_id']]['dst_feats'] .= $new_connecting_if['dst_feature_id'].",";
					}

				}

					foreach($if_available_features as $if => $has_features) {

						$missing_a_feature = '0';
						//now check that all the physical features are there
						if(isset($this->_path_array[$this->_path_id]['interface_features']['physical'])) {
						
							foreach($this->_path_array[$this->_path_id]['interface_features']['physical'] as $phy_feat => $phy_feat_status) {
		
								//make sure the source interface fulfilled the physical requirements of the path, only for physical features that have not yet been found
								if(isset($has_features['src_feats'])) {
									if (!preg_match("/,".$phy_feat.",/", $has_features['src_feats'], $empty) && $phy_feat_status == 'no_originator_found_yet') {
											
										$missing_a_feature = '1';
											
									}
								}
								
								if(isset($has_features['dst_feats'])) {	
									//make sure the destination interface fulfilled the physical requirements of the path, only for physical features that have not yet been found
									if (!preg_match("/,".$phy_feat.",/", $has_features['dst_feats'], $empty) && $phy_feat_status == 'no_originator_found_yet') {
									
										$missing_a_feature = '1';
									
									}
								}
							}
						}
						
						//now check that all the service features are there
						if(isset($this->_path_array[$this->_path_id]['interface_features']['service'])) {
							
							foreach($this->_path_array[$this->_path_id]['interface_features']['service'] as $service_feat) {
							
								if(isset($has_features['src_feats'])) {
									
									//make sure the source interface fulfilled the service requirements of the path
									if (!preg_match("/,".$service_feat.",/", $has_features['src_feats'], $empty)) {
								
										$missing_a_feature = '1';
								
									}
								}
								
								if(isset($has_features['dst_feats'])) {
									
									//make sure the destination interface fulfilled the service requirements of the path
									if (!preg_match("/,".$service_feat.",/", $has_features['dst_feats'], $empty)) {
											
										$missing_a_feature = '1';
											
									}
								}
							}
						}
						
						//if any feature was missing then remove the interface that failed from the array
						if ($missing_a_feature == '1') {
								
							unset($if_available_features[$if]);
								
						} else {
							
							
							//the source and destination both have all required features.
							//now lets check if the source has a role and if the limitations obj allows us to accept it.
							if (isset($has_features['src_eq_roles']) && $path_limitations->get_check_first_interface_equipment_role() == '1') {
							
								//we found a role on the source equipment and the limitations allow us to use this as a path, 
								//but we need to check if it is the one we are looking for and if it we fullfilled the physical requirements
								//before we declaere this a good path

								if(preg_match("/,".$equipment_role_obj->get_equipment_role_id().",/", $has_features['src_eq_roles'], $empty)) {
										
									if(isset($this->_path_array[$this->_path_id]['interface_features']['physical'])) {
							
										//we need to make sure we found all the originators of physical features we where looking for
										$missing_originator = '0';
										foreach($this->_path_array[$this->_path_id]['interface_features']['physical'] as $phy_feat) {
											
											if ($phy_feat == 'no_originator_found_yet') {
							
												$missing_originator = '1';
							
											}
										}
							
										if ($missing_originator == '0') {
												
											$proccessing_path['successful_path'] = 'yes';
																						
										}  else {
									
											//all our requirements are not fulfilled, but we can continue to look because the path fullfills everything else
											//we need to find another equipment that has the required role 
									
										}
									} else {
										
										//there are no physical requirements so the path is good
										$proccessing_path['successful_path'] = 'yes';
										
									}
									
									
								} else {
									
									//dident have the role we are looking for
									//we need to find another equipment that has the required role 
									
								}
							
							} elseif (isset($has_features['dst_eq_roles'])) {
							
								//we found a role on the destination equipment, but we need to check if it is the one we are looking for and if it we fullfilled the physical requirements
								//before we declaere this a good path
								if(preg_match("/,".$equipment_role_obj->get_equipment_role_id().",/", $has_features['dst_eq_roles'], $empty)) {
										
									if(isset($this->_path_array[$this->_path_id]['interface_features']['physical'])) {
							
										$missing_originator = '0';
										foreach($this->_path_array[$this->_path_id]['interface_features']['physical'] as $phy_feat) {
												
											if ($phy_feat == 'no_originator_found_yet') {
							
												$missing_originator = '1';
							
											}
										}
							
										if ($missing_originator == '0') {
												
											$proccessing_path['successful_path'] = 'yes';
											
										} else {
											
											//all our requirements are not fulfilled, but we can continue to look because the path fullfills everything else
											//we need to find another equipment that has the required role

										}
										
										
									} else {
										
										//there are no physical requirements so the path is good
										$proccessing_path['successful_path'] = 'yes';
										
										
									}
								} else {
									
									//dident have the role we are looking for
									//we need to find another equipment that has the required role
									
								}
							} 
						}
					}
				}
				
				//now filter out the original interface and clean up the data array
				if(isset($if_available_features)) {
					
					$next_dst_hops = array();
					$g=0;
					foreach($if_available_features as $if => $has_features) {
							
						//if the interface is not in the tracker yet then we allow it to continue as a new interface
						//in this case this only filters out the original interface because this is the first pass
						if (!strpos($this->_path_array[$this->_path_id]['total_if_tracker'], ",".$if.",")
						&& !preg_match("/^".$if.",/", $this->_path_array[$this->_path_id]['total_if_tracker'], $empty)
						&& !preg_match("/,".$if."$/", $this->_path_array[$this->_path_id]['total_if_tracker'], $empty)
						&& !preg_match("/^".$if."$/", $this->_path_array[$this->_path_id]['total_if_tracker'], $empty))
						{
							$next_dst_hops['valid_dst_if'][$g] 		= $if;
							$next_dst_hops['query_data'][$g]		= $has_features['query_data'];
								
							$g++;
								
						}	
					}
					
					unset($if_available_features);
					
				}
		
			if (isset($next_dst_hops['valid_dst_if']['0']) ){
				
				//copy the retireing path into a variable we need
				$retireing_path	= $this->_path_array[$this->_path_id];
				//now retire the original path, it dident succeed because this hop did not fulfill the requirement
				unset($this->_path_array[$retireing_path['path_id_number']]);
				
				
				foreach ($next_dst_hops['valid_dst_if'] as $next_hop_arr_index => $next_dst_hop) {
					
					//check if the dst interface is a successful path this would be one removed from the original interface
					//i.e. service point -> cpe_router

					if ($next_dst_hops['query_data'][$next_hop_arr_index]['dst_role_id'] == $equipment_role_obj->get_equipment_role_id()){
									
								preg_match("/^([0-9]+)/", $retireing_path['ini_if_tracker'], $first_int);
									
								$full_path = $retireing_path['total_if_tracker'] . "," . $next_dst_hop;
									
								$this->_successful_paths[$this->_successful_path_count]	= new Thelist_Model_path($full_path);
									
								$this->_successful_path_count++;
					
					} else {

						$sql2 = "SELECT i.if_id FROM interfaces i
								LEFT OUTER JOIN interface_feature_mapping ifm ON ifm.if_id=i.if_id
								WHERE eq_id='".$next_dst_hops['query_data'][$next_hop_arr_index]['dst_eq']."'
								AND i.if_id!='".$next_dst_hop."'
								";
						
					if (isset($this->_path_array[$this->_path_id]['feature_ids_string'])) {
						//UPDATE THIS QUERY SO IT CONSIDERS THE feature more accurately, not just matches one
							$sql2 .= 	" AND ifm.if_feature_id IN (".$retireing_path['feature_ids_string'].") \n";
							
	
						}
						
						$sql2 .= 	" GROUP BY i.if_id \n";
						
						$new_interfaces	= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
						
						if (isset($new_interfaces['0'])) {

							foreach ($new_interfaces as $new_interface) {
								
								//we save alot of time by checking the eq_id if the next hop of an interface
								//in most cases we have switches connected to patch panels, and in most cases patch panes are not allowed
								//without this check we explode the amount of connections that in reallyity go nowhere (out from patchpanel then to switch and back to patch). 
								//This check stops that and eliminates the exponential growth of paths destined to be remove on next hop.
								
								$sql4 = "SELECT e.eq_id, e.eq_type_id FROM interface_connections ic
										INNER JOIN interfaces i ON i.if_id=ic.if_id_a
										INNER JOIN equipments e ON i.eq_id=e.eq_id
										WHERE ic.if_id_b='".$new_interface['if_id']."'
										AND e.eq_id NOT IN (".$retireing_path['total_eq_tracker'].")
										";
								
								if (isset($this->_general_path_limitation_array['deny_eq_type_ids'])) {
										
									$sql4 .= 	" AND e.eq_type_id NOT IN (".$this->_general_path_limitation_array['deny_eq_type_ids'].") \n";
										
								}
	
								$sql4 .= 	"\n UNION ALL \n";
										
										
								$sql4 .= "SELECT e.eq_id, e.eq_type_id FROM interface_connections ic
										INNER JOIN interfaces i ON i.if_id=ic.if_id_b
										INNER JOIN equipments e ON i.eq_id=e.eq_id
										WHERE ic.if_id_a='".$new_interface['if_id']."'
										AND e.eq_id NOT IN (".$retireing_path['total_eq_tracker'].")
										";
								
								if (isset($this->_general_path_limitation_array['deny_eq_type_ids'])) {
										
									$sql4 .= 	" AND e.eq_type_id NOT IN (".$this->_general_path_limitation_array['deny_eq_type_ids'].") \n";
										
								}
	
								$next_hop_validation	= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql4);
								
								if (isset($next_hop_validation['0'])) {
								
									//create a new path_id
									$this->_path_id++;
		
									//copy the old path into the new
									$this->_path_array[$this->_path_id] = $retireing_path;
									
									//set the path number
									$this->_path_array[$this->_path_id]['path_id_number']		= $this->_path_id;
									
									//append
									$this->_path_array[$this->_path_id]['ini_if_tracker']		.= ",".$new_interface['if_id'];
									
									//set
									$this->_path_array[$this->_path_id]['recv_if_tracker']		= $next_dst_hop;
									
									//append
									$this->_path_array[$this->_path_id]['total_if_tracker']		.= ",".$next_dst_hop.",".$new_interface['if_id'];
									
									//append
									$this->_path_array[$this->_path_id]['total_eq_tracker']		.= ",".$next_dst_hops['query_data'][$next_hop_arr_index]['dst_eq'];
									
									//set
									$this->_path_array[$this->_path_id]['last_if']				= $next_dst_hop;
									
									//set
									$this->_path_array[$this->_path_id]['next_if']				= $new_interface['if_id'];
		
									//set
									$this->_path_array[$this->_path_id]['next_eq']				= $next_dst_hops['query_data'][$next_hop_arr_index]['dst_eq'];
									
									
									
								}
							}
						}	
					}
				}
				
			} else {
				
				//if we had no new destination interfaces that match our criteria.
				unset($this->_path_array[$this->_path_id]);
				
			}
		}  /// end foreach

		$this->find_equipment_role_loop($equipment_role_obj, $path_limitations);

		//after the loop return the paths
		return $this->_successful_paths;
		
	}
		

	private function find_equipment_role_loop($equipment_role_obj, $path_limitations)
	{
		$r=0;

		$stop=0;
		$finished = false;
		while ($finished == false) {
			
			foreach ($this->_path_array as $proccessing_path) {

				//unset the variables used in the loop, many depend on isset and if we do not reset them 
				//they continue, causing havoc
				if (isset($next_dst_hops)) {
					unset($next_dst_hops);
				}
				if (isset($if_available_features)) {	
					unset($if_available_features);
				}
				if (isset($new_connecting_ifs)) {
					unset($new_connecting_ifs);
				}

				
				//now lets find the next set of interfaces to probe
				$sql_start = 	"SELECT ifm.if_id AS dst_if_id, i.eq_id AS dst_eq, ifm.if_feature_id AS dst_feature_id, ifm.if_feature_value AS dst_feature_value, ic.if_id_b AS src_if_id, e.eq_id AS src_eq, ifm2.if_feature_id AS src_feature_id, ifm2.if_feature_value AS src_feature_value, erm.equipment_role_id AS src_role_id, erm2.equipment_role_id AS dst_role_id
								FROM interface_connections ic
								INNER JOIN interface_feature_mapping ifm ON ifm.if_id=ic.if_id_a
								INNER JOIN interface_feature_mapping ifm2 ON ifm2.if_id=ic.if_id_b
								INNER JOIN interfaces i ON i.if_id=ic.if_id_a
								INNER JOIN interfaces i2 ON i2.if_id=ic.if_id_b
								INNER JOIN equipments e ON e.eq_id=i2.eq_id
								INNER JOIN equipments e2 ON e2.eq_id=i.eq_id";
				
				//if we only allowing the path include equipment in certain unit groups
				if (isset($this->_general_path_limitation_array['unit_groups'])) {
						
					$sql_start .=	" INNER JOIN equipment_mapping em ON em.eq_id=e2.eq_id \n";
					$sql_start .=	" INNER JOIN unit_group_mapping ugm ON ugm.unit_id=em.unit_id \n";
									
				}
				
				
				$sql_start .=	"LEFT OUTER JOIN equipment_role_mapping erm ON erm.eq_id=e.eq_id
								LEFT OUTER JOIN equipment_role_mapping erm2 ON erm2.eq_id=e2.eq_id
								WHERE ic.if_id_b='".$proccessing_path['next_if']."'
								AND e2.eq_id NOT IN (".$proccessing_path['total_eq_tracker'].")
								";
					
				//if we are looking for features then append this
				if (isset($proccessing_path['feature_ids_string'])) {
				
					$sql_start .= 	" AND ifm.if_feature_id IN (".$proccessing_path['feature_ids_string'].") \n";
					$sql_start .= 	" AND ifm2.if_feature_id IN (".$proccessing_path['feature_ids_string'].") \n";
				
				}
					
				//if we not allowing the path to traverse some types of equipment, then append this
				if (isset($this->_general_path_limitation_array['deny_eq_type_ids'])) {
						
					$sql_start .= 	" AND e2.eq_type_id NOT IN (".$this->_general_path_limitation_array['deny_eq_type_ids'].") \n";
				
				}
					
				//if we not allowing the first interface to belong to some types of equipment, then append this
				if ($path_limitations->get_verify_first_interface_equipment_eq_type() == '1') {
				
					$sql_start .= 	" AND e.eq_type_id NOT IN (".$this->_general_path_limitation_array['deny_eq_type_ids'].") \n";
						
				}
				
				//if we only allowing the path include equipment in certain unit groups
				if (isset($this->_general_path_limitation_array['unit_groups'])) {
						
					$sql_start .= 	" AND ugm.unit_group_id IN (".$this->_general_path_limitation_array['unit_groups'].") \n";
					$sql_start .= 	" AND em.eq_map_deactivated IS NULL \n";
						
				}
					
				$sql_start .= 	"\n UNION ALL \n\n";
					
				$sql_start .=	"SELECT ifm.if_id AS dst_if_id, i.eq_id AS dst_eq, ifm.if_feature_id AS dst_feature_id, ifm.if_feature_value AS dst_feature_value, ic.if_id_a AS src_if_id, e.eq_id AS src_eq, ifm2.if_feature_id AS src_feature_id, ifm2.if_feature_value AS src_feature_value, erm.equipment_role_id AS src_role_id, erm2.equipment_role_id AS dst_role_id
											FROM interface_connections ic
											INNER JOIN interface_feature_mapping ifm ON ifm.if_id=ic.if_id_b
											INNER JOIN interface_feature_mapping ifm2 ON ifm2.if_id=ic.if_id_a
											INNER JOIN interfaces i ON i.if_id=ic.if_id_b
											INNER JOIN interfaces i2 ON i2.if_id=ic.if_id_a
											INNER JOIN equipments e ON e.eq_id=i2.eq_id
											INNER JOIN equipments e2 ON e2.eq_id=i.eq_id";
				
				//if we only allowing the path include equipment in certain unit groups
				if (isset($this->_general_path_limitation_array['unit_groups'])) {
				
					$sql_start .=	" INNER JOIN equipment_mapping em ON em.eq_id=e2.eq_id \n";
					$sql_start .=	" INNER JOIN unit_group_mapping ugm ON ugm.unit_id=em.unit_id \n";
						
				}
											
				$sql_start .=	"LEFT OUTER JOIN equipment_role_mapping erm ON erm.eq_id=e.eq_id
								LEFT OUTER JOIN equipment_role_mapping erm2 ON erm2.eq_id=e2.eq_id
								WHERE ic.if_id_a='".$proccessing_path['next_if']."'
								AND e2.eq_id NOT IN (".$proccessing_path['total_eq_tracker'].")
								";
		
				//if we are looking for features then append this
				if (isset($proccessing_path['feature_ids_string'])) {
						
					$sql_start .= 	" AND ifm.if_feature_id IN (".$proccessing_path['feature_ids_string'].") \n";
					$sql_start .= 	" AND ifm2.if_feature_id IN (".$proccessing_path['feature_ids_string'].") \n";
						
				}
					
				//if we not allowing the path to traverse some types of equipment, then append this
				if (isset($this->_general_path_limitation_array['deny_eq_type_ids'])) {
						
					$sql_start .= 	" AND e2.eq_type_id NOT IN (".$this->_general_path_limitation_array['deny_eq_type_ids'].") \n";
				
				}
					
				//if we not allowing the first interface to belong to some types of equipment, then append this
				if ($path_limitations->get_verify_first_interface_equipment_eq_type() == '1') {
				
					$sql_start .= 	" AND e.eq_type_id NOT IN (".$this->_general_path_limitation_array['deny_eq_type_ids'].") \n";
						
				}
				
				//if we only allowing the path include equipment in certain unit groups
				if (isset($this->_general_path_limitation_array['unit_groups'])) {
						
					$sql_start .= 	" AND ugm.unit_group_id IN (".$this->_general_path_limitation_array['unit_groups'].") \n";
					$sql_start .= 	" AND em.eq_map_deactivated IS NULL \n";
						
				}
					
				$new_connecting_ifs	= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql_start);
				
				//turn the features for src and dst into strings per interface
				if (isset($new_connecting_ifs['0'])) {
				
					$i=0;
					foreach($new_connecting_ifs as $new_connecting_if){

						//have we found an originator in the source and does the limitation object tell us to look to the originator on the interface we feed the system?
						if ($new_connecting_if['src_feature_value'] != null && $path_limitations->get_check_if_first_interface_is_originator() == '1' && isset($proccessing_path['interface_features']['physical'])) {
								
							foreach ($proccessing_path['interface_features']['physical'] as $single_physical_feature_id => $single_physical_feature_value ) {
									
								if ($single_physical_feature_value == 'no_originator_found_yet' && $single_physical_feature_id == $new_connecting_if['src_feature_id'])
									
								$proccessing_path['interface_features']['physical'][$single_physical_feature_id] = $new_connecting_if['src_if_id'];
							}
						}
							
						//have we found an originator in the equipment on the otherside (next hop)
						if ($new_connecting_if['dst_feature_value'] != null && isset($proccessing_path['interface_features']['physical'])) {

							foreach ($proccessing_path['interface_features']['physical'] as $single_physical_feature_id => $single_physical_feature_value ) {
				
								if ($single_physical_feature_value == 'no_originator_found_yet' && $single_physical_feature_id == $new_connecting_if['dst_feature_id'])
				
								$proccessing_path['interface_features']['physical'][$single_physical_feature_id] = $new_connecting_if['dst_if_id'];
							}
						}
							
						//create string of source roles equipment if there are any
						if ($new_connecting_if['src_role_id'] != null) {
				
							if (!isset($if_available_features[$new_connecting_if['src_if_id']]['src_eq_roles'])) {
				
								$if_available_features[$new_connecting_if['src_if_id']]['src_eq_roles']  = ',';
				
							}
				
							if (!preg_match("/,".$new_connecting_if['src_role_id'].",/", $if_available_features[$new_connecting_if['src_if_id']]['src_eq_roles'], $empty)) {
								$if_available_features[$new_connecting_if['src_if_id']]['src_eq_roles'] .= $new_connecting_if['src_role_id'].",";
							}
						}
							
						//create string of destination roles equipment if there are any
						if ($new_connecting_if['dst_role_id'] != null) {
				
							if (!isset($if_available_features[$new_connecting_if['dst_if_id']]['dst_eq_roles'])) {
				
								$if_available_features[$new_connecting_if['dst_if_id']]['dst_eq_roles']  = ',';
				
							}
				
							//limit the array to only include unique role entries
								
							if (!preg_match("/,".$new_connecting_if['dst_role_id'].",/", $if_available_features[$new_connecting_if['dst_if_id']]['dst_eq_roles'], $empty)) {
								$if_available_features[$new_connecting_if['dst_if_id']]['dst_eq_roles'] .= $new_connecting_if['dst_role_id'].",";
							}

						}
				
						//create string of available features for the interface if there are any
						if (!isset($if_available_features[$new_connecting_if['src_if_id']]['src_feats'])) {
				
							$if_available_features[$new_connecting_if['src_if_id']]['src_feats']  = ',';
							$if_available_features[$new_connecting_if['src_if_id']]['query_data'] = $new_connecting_if;
				
						}
							
						if (!isset($if_available_features[$new_connecting_if['dst_if_id']]['dst_feats'])) {
								
							$if_available_features[$new_connecting_if['dst_if_id']]['dst_feats']  = ',';
							$if_available_features[$new_connecting_if['dst_if_id']]['query_data'] = $new_connecting_if;
								
						}
							
						//limit the array to only include unique entries, we get many duplicates because of the query we are doing
						if (!preg_match("/,".$new_connecting_if['src_feature_id'].",/", $if_available_features[$new_connecting_if['src_if_id']]['src_feats'], $empty)) {
							$if_available_features[$new_connecting_if['src_if_id']]['src_feats'] .= $new_connecting_if['src_feature_id'].",";
						}
						if (!preg_match("/,".$new_connecting_if['dst_feature_id'].",/", $if_available_features[$new_connecting_if['dst_if_id']]['dst_feats'], $empty)) {
							$if_available_features[$new_connecting_if['dst_if_id']]['dst_feats'] .= $new_connecting_if['dst_feature_id'].",";
						}
				
					}
					
					foreach($if_available_features as $if => $has_features) {
				
						$missing_a_feature = '0';
						//now check that all the physical features are there
						if(isset($proccessing_path['interface_features']['physical'])) {
							
							foreach($proccessing_path['interface_features']['physical'] as $phy_feat => $phy_feat_status) {
				
								//make sure the source interface fulfilled the physical requirements of the path, only for physical features that have not yet been found
								if(isset($has_features['src_feats'])) {
									if (!preg_match("/,".$phy_feat.",/", $has_features['src_feats'], $empty) && $phy_feat_status == 'no_originator_found_yet') {
											
										$missing_a_feature = '1';
											
									}
								}
							}
				
								if(isset($has_features['dst_feats'])) {
									//make sure the destination interface fulfilled the physical requirements of the path, only for physical features that have not yet been found
									if (!preg_match("/,".$phy_feat.",/", $has_features['dst_feats'], $empty) && $phy_feat_status == 'no_originator_found_yet') {
											
										$missing_a_feature = '1';
											
									}
								}
							}
				
						//now check that all the service features are there
						if(isset($proccessing_path['interface_features']['service'])) {
								
							foreach($proccessing_path['interface_features']['service'] as $service_feat) {
									
								if(isset($has_features['src_feats'])) {
										
									//make sure the source interface fulfilled the service requirements of the path
									if (!preg_match("/,".$service_feat.",/", $has_features['src_feats'], $empty)) {
				
										$missing_a_feature = '1';
				
									}
								}

								if(isset($has_features['dst_feats'])) {
										
									//make sure the destination interface fulfilled the service requirements of the path
									if (!preg_match("/,".$service_feat.",/", $has_features['dst_feats'], $empty)) {
											
										$missing_a_feature = '1';
											
									}
								}
							}
						}
				
						//if any feature was missing then remove the interface that failed from the array
						if ($missing_a_feature == '1') {

							unset($if_available_features[$if]);
											
						} else {
								
							//the source and destination both have all required features.
							//lets see if it has the role that we need
							if (isset($has_features['dst_eq_roles'])) {
								
								//we found a role on the destination equipment, but we need to check if it is the one we are looking for and if it we fullfilled the physical requirements
								//before we declaere this a good path
								if(preg_match("/,".$equipment_role_obj->get_equipment_role_id().",/", $has_features['dst_eq_roles'], $empty)) {
				
									if(isset($proccessing_path['interface_features']['physical'])) {
											
										$missing_originator = '0';
										foreach($proccessing_path['interface_features']['physical'] as $phy_feat) {
				
											if ($phy_feat == 'no_originator_found_yet') {
													
												$missing_originator = '1';
													
											}
										}
										
										if ($missing_originator == '0') {
				
											$proccessing_path['successful_path'] = 'yes';
													
										} else {
												
											//all our requirements are not fulfilled, but we can continue to look because the path fullfills everything else
											//we need to find another equipment that has the required role
				
										}
				
				
									} else {
				
										//there are no physical requirements so the path is good
										$proccessing_path['successful_path'] = 'yes';
				
				
									}
								} else {
										
									//dident have the role we are looking for
									//we need to find another equipment that has the required role
										
								}
							}
						}
					}
				}
				
				//now filter out the original interface and clean up the data array
				if(isset($if_available_features)) {
						
					$next_dst_hops = array();
					$g=0;
					foreach($if_available_features as $if => $has_features) {
							
						//if the interface is not in the tracker yet then we allow it to continue as a new interface
						//in this case this only filters out the original interface because this is the first pass
						if (!strpos($proccessing_path['total_if_tracker'], ",".$if.",")
						&& !preg_match("/^".$if.",/", $proccessing_path['total_if_tracker'], $empty)
						&& !preg_match("/,".$if."$/", $proccessing_path['total_if_tracker'], $empty)
						&& !preg_match("/^".$if."$/", $proccessing_path['total_if_tracker'], $empty))
						{
							$next_dst_hops['valid_dst_if'][$g] 		= $if;
							$next_dst_hops['query_data'][$g]		= $has_features['query_data'];
				
							$g++;
				
						}
					}
				} 
				
				//if there are any new interfaces that
					if (isset($next_dst_hops['valid_dst_if']['0']) ){
						
							if($proccessing_path['successful_path'] == 'yes') {
								
								foreach ($next_dst_hops['valid_dst_if'] as $next_hop_arr_index => $next_dst_hop) {
									
									if ($next_dst_hops['query_data'][$next_hop_arr_index]['dst_role_id'] == $equipment_role_obj->get_equipment_role_id()){
										
										preg_match("/^([0-9]+)/", $proccessing_path['ini_if_tracker'], $first_int);
										
										$full_path = $proccessing_path['total_if_tracker'] . "," . $next_dst_hop;
										
										$this->_successful_paths[$this->_successful_path_count]	= new Thelist_Model_path($full_path);
										
										$this->_successful_path_count++;
									}
								}
								
								
							} else {
	
								foreach ($next_dst_hops['valid_dst_if'] as $next_hop_arr_index => $next_dst_hop) {
										
							
									$sql2 = "SELECT i.if_id FROM interfaces i
											LEFT OUTER JOIN interface_feature_mapping ifm ON ifm.if_id=i.if_id
											WHERE eq_id='".$next_dst_hops['query_data'][$next_hop_arr_index]['dst_eq']."'
											AND i.if_id!='".$next_dst_hop."'
											AND i.eq_id NOT IN (".$proccessing_path['total_eq_tracker'].")
											";
									
									if (isset($proccessing_path['feature_ids_string'])) {
										//UPDATE THIS QUERY SO IT CONSIDERS THE feature more accurately, not just matches one
										$sql2 .= 	" AND ifm.if_feature_id IN (".$proccessing_path['feature_ids_string'].") \n";

									}
									
									$sql2 .= 	" GROUP BY i.if_id \n";
										
									$new_interfaces	= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
										
									if (isset($new_interfaces['0'])) {
							
										$retireing_path	= $proccessing_path;
																	
										foreach ($new_interfaces as $new_interface) {
												
											//we save alot of time by checking the eq_id if the next hop of an interface
											//in most cases we have switches connected to patch panels, and in most cases patch panes are not allowed
											//without this check we explode the amount of connections that in reallyitygo nowhere.
											//This check stops that and eliminates the exponential growth of paths.
												
											$sql4 = "SELECT e.eq_id, e.eq_type_id FROM interface_connections ic
													INNER JOIN interfaces i ON i.if_id=ic.if_id_a
													INNER JOIN equipments e ON i.eq_id=e.eq_id
													WHERE ic.if_id_b='".$new_interface['if_id']."'
													AND e.eq_id NOT IN (".$proccessing_path['total_eq_tracker'].")
													";
												
											if (isset($this->_general_path_limitation_array['deny_eq_type_ids'])) {
													
												$sql4 .= 	" AND e.eq_type_id NOT IN (".$this->_general_path_limitation_array['deny_eq_type_ids'].") \n";
													
											}
											$sql4 .= 	"\n UNION ALL \n";
												
												
												
											$sql4 .= "SELECT e.eq_id, e.eq_type_id FROM interface_connections ic
													INNER JOIN interfaces i ON i.if_id=ic.if_id_b
													INNER JOIN equipments e ON i.eq_id=e.eq_id
													WHERE ic.if_id_a='".$new_interface['if_id']."'
													AND e.eq_id NOT IN (".$proccessing_path['total_eq_tracker'].")
													";
												
											if (isset($this->_general_path_limitation_array['deny_eq_type_ids'])) {
													
												$sql4 .= 	" AND e.eq_type_id NOT IN (".$this->_general_path_limitation_array['deny_eq_type_ids'].") \n";
													
											}
							
											$next_hop_validation	= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql4);
											
												
											if (isset($next_hop_validation['0'])) {
												
												//create a new path_id
												$this->_path_id++;
							
												//copy the old path into the new
												$this->_path_array[$this->_path_id] = $retireing_path;
							
												//set the path number
												$this->_path_array[$this->_path_id]['path_id_number']		= $this->_path_id;
							
												//append
												$this->_path_array[$this->_path_id]['ini_if_tracker']		.= ",".$new_interface['if_id'];
							
												//append
												$this->_path_array[$this->_path_id]['recv_if_tracker']		.= ",".$next_dst_hop;
							
												//append
												$this->_path_array[$this->_path_id]['total_if_tracker']		.= ",".$next_dst_hop.",".$new_interface['if_id'];
							
												//append
												$this->_path_array[$this->_path_id]['total_eq_tracker']		.= ",".$next_dst_hops['query_data'][$next_hop_arr_index]['dst_eq'];
							
												//set
												$this->_path_array[$this->_path_id]['last_if']				= $next_dst_hop;
							
												//set
												$this->_path_array[$this->_path_id]['next_if']				= $new_interface['if_id'];
							
												//set
												$this->_path_array[$this->_path_id]['last_eq']				= $this->_path_array[$this->_path_id]['next_eq'];
												
												//set
												$this->_path_array[$this->_path_id]['next_eq']				= $next_dst_hops['query_data'][$next_hop_arr_index]['dst_eq'];
													
											}
										}
										
										//retire the old path
										unset($this->_path_array[$retireing_path['path_id_number']]);
										
									} 
								}
							}
						}
				
				//the path has now either been turned into new paths or it is successful. in either case at this point there 
				//is no longer a use for the original path. so we kill it off
				unset($this->_path_array[$proccessing_path['path_id_number']]);
			}

			//check if the pool of paths is empty so we can end the search
			if (count($this->_path_array) == '0') {

				$finished = true;
				
			}
		}
	} 

	
		
	private function create_general_array_of_limitations($path_limitations)
	{
		
		//path limitations general:
		
		//reset the array
		$this->_general_path_limitation_array = null;
		
		//eq_type limits
		if ($path_limitations->get_deny_path_through_these_eq_types() != null){

			$i=0;
			foreach($path_limitations->get_deny_path_through_these_eq_types() as $eq_type_obj) {
		
				if ($i == 0) {
		
					$this->_general_path_limitation_array['deny_eq_type_ids'] = $eq_type_obj->get_eq_type_id();
		
				} else {
		
					$this->_general_path_limitation_array['deny_eq_type_ids'] .= ",".$eq_type_obj->get_eq_type_id();
		
				}
				$i++;
			}
		}
		
		//unit_group limits
		if ($path_limitations->get_equipment_unit_groups_allowed() != null){
			
			$i=0;
			foreach($path_limitations->get_equipment_unit_groups_allowed() as $unit_group_id) {
		
				if ($i == 0) {
		
					$this->_general_path_limitation_array['unit_groups'] = $unit_group_id;
		
				} else {
		
					$this->_general_path_limitation_array['unit_groups'] .= ",".$unit_group_id;
		
				}
				$i++;
			}
		}

	}
	
	private function create_general_array_of_interface_features($interface_feature_obj)
	{
		//if_features
		//add all the physical features that we must find originators for in the path, this way we can check them off as we find them
		if (is_array($interface_feature_obj)) {
				
			//reset the array
			$this->_general_interface_feature_array		= null;
			
			$i=0;
			foreach($interface_feature_obj as $interface_feature){
				
				if ($interface_feature != null) {
					
					if ($interface_feature->get_if_feature_type() == 'physical') {
			
						if (!isset($this->_general_interface_feature_array['physical'])) {
								
							$this->_general_interface_feature_array['physical'] = array();
						}
			
						$this->_general_interface_feature_array['physical'][$interface_feature->get_if_feature_id()] = 'no_originator_found_yet';
			
					} elseif ($interface_feature->get_if_feature_type() == 'service') {
			
						if (!isset($this->_general_interface_feature_array['service'])) {
								
							$this->_general_interface_feature_array['service'] = array();
						}
			
						$this->_general_interface_feature_array['service'][] = $interface_feature->get_if_feature_id();
					}
			
			
					//if we are looking for features lets create a comma seperated string of the values that we can use in the  where statement so it will narrow the result
					//this does in no way ensure that sql will only return the interfaces that match all feature criterias, it only removes all the ones that dont even match
					//one of them.				
					if ($i == 0) {
						
						$this->_general_interface_feature_array['feature_ids_string']	= $interface_feature->get_if_feature_id();
			
					} else {
			
						$this->_general_interface_feature_array['feature_ids_string']	.= ",".$interface_feature->get_if_feature_id();
			
					}
					$i++;
			
				}
			}
		}
	}
			
}
?>