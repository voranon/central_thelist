<?php 
require_once ('Zend/Db.php');
class thelist_utility_database
{
	private $thelist_adapter;
	private $thelist_information_schema_adapter;
	private $log;
	 
	//for ip flows database
	private $ip_flows_adapter;
	
	//for soa dns database 
	private $mydns_adapter;
	
	//for syslog database
	private $syslog_adapter;
	
	//for syslog database
	private $tracking_adapter;
	
	//table names
	private $users;
	private $app_event_logs;
	private $user_event_logs;
	private $acl_access_control_list;
	private $acl_resources;
	private $acl_privileges;
	private $acl_roles;
	private $menus;
	private $menuitems;
	private $htmlpages;
	private $buildings;
	private $projects;
	private $project_entities;
	private $contacts;
	private $project_contact_mapping;
	private $tasks;
	private $project_task_mapping;
	private $units;
	private $project_entity_mapping;
	private $items;
	private $queues;
	private $queue_groups;
	private $notes;
	private $note_attachments;
	private $task_note_mapping;
	private $building_contact_mapping;
	private $building_task_mapping;
	private $user_queue_mapping;
	private $homerun_types;
	private $unit_homerun_mapping;
	private $unit_service_plan_mapping;
	private $unit_service_point_mapping;
	private $service_points;
	private $unit_groups;
	private $unit_group_mapping;
	private $equipment_logs;
	private $actions;
	private $end_user_services;
	private $end_user_service_contact_mapping;
	private $searchboxes;
	private $service_plan_groups;
	private $equipment_type_groups;
	private $eq_type_group_mapping;
	private $service_plan_help;
	private $service_plan_service_point_interface_feature_mapping;
	private $interface_task_mapping;
	private $end_user_note_mapping;
	private $equipment_default_provisioning_plans;
	private $interface_type_configurations;
	private $equipment_type_application_metrics;
	private $eq_type_allowed_metric_values;
	private $eq_type_applications;
	
	private $service_plan_temp_quote_mapping;
	private $service_plan_temp_quote_eq_type_mapping;
	private $service_plan_temp_quote_option_mapping;
	
	private $contact_addresses;
	private $contact_email_addresses;
	private $contact_phone_numbers;
	
	
	

	//MARTIN TABLES
	private $equipments;
	private $equipment_types;
	private $interfaces;
	private $interface_relationships;
	private $connection_queues;
	private $interface_type_allowed_config_values;
	
	//private $connection_queue_types;
	private $frame_matches;
	private $frame_headers;
	private $connection_queue_filters;
	private $apis;
	private $equipment_apis;
	private $equipment_auths;
	private $interface_types;
	private $equipment_roles;
	private $equipment_role_mapping;
	private $homerun_group_eq_type_mapping;
	private $mac_address_prefixes;
	private $service_plan_if_type_mapping;
	private $user_locations;
	
	
	// no longer needed private $features;
	private $interface_features;
	private $interface_feature_mapping;
	private $interface_type_feature_mapping;
	private $purchase_order_items;
	private $purchase_orders;
	private $purchase_request_items;
	private $purchase_request_to_po_mapping;
	private $purchase_requests;
	private $vendors;
	private $static_if_types;
	private $eq_type_serial_match;
	private $equipment_mapping;

	private $service_plans;
	private $service_plan_option_mapping;
	private $service_plan_eq_type_mapping;
	private $service_plan_options;
	private $service_plan_option_groups;
	private $service_plan_eq_type_groups;
	private $service_plan_eq_type_if_option_mapping;
	private $service_plan_group_mapping;
	
	private $sales_quotes;
	private $service_plan_quote_eq_type_mapping;
	private $service_plan_quote_option_mapping;
	private $service_plan_quote_mapping;
	private $sales_quote_eq_type_map_equipment_mapping;
	private $service_plan_quote_task_mapping;
	
	private $end_user_task_mapping;
	
	//device functions by martin
	private $device_functions;
	private $device_function_mapping;
	private $device_commands;
	private $device_command_parameters;
	private $device_command_parameter_tables;
	private $device_command_parameter_columns;
	private $device_command_mapping;
	private $software_packages;
	private $equipment_software_upgrades;
	private $equipment_type_software_mapping;
	private $command_regex_parameters;
	private $command_regex_mapping;
	private $command_regexs;
	
	//ipaddresses by martin
	private $ip_address_mapping;
	private $ip_addresses;
	private $ip_subnets;
	
	
	//monitor by martin
	private $monitoring_data_sources;
	private $monitoring_guid_ds_mapping;
	private $monitoring_guids;
	private $monitoring_poller_cache;
	private $monitoring_poller_command_cache;
	private $monitoring_rra_type_mapping;
	private $monitoring_rra_types;
	
	//config interfaces / equipment by martin
	private $interface_configuration_mapping;
	private $interface_configurations;
	
	//calendar by martin
	private $calendar_appointments;
	private $calendar_appointment_task_mapping;
	
	//mydns
	private $rr;
	private $soa;
	
	private $service_plan_quote_ip_address_mapping;
	private $ip_routes;
	private $ip_route_gateways;
	private $service_plan_quote_connection_queue_filter_mapping;
	private $connection_queue_relationships;
	
	//equipment applications
	private $equipment_application_mapping;
	private $equipment_application_metric_mapping;
	private $equipment_application_metrics;
	private $equipment_applications;
	
	//traffic rules
	private $ip_protocols;
	private $ip_protocol_ports;
	private $ip_protocol_port_mapping;
	private $ip_traffic_rule_interface_mapping;
	private $ip_traffic_rules;
	private $ip_traffic_rule_ip_subnets;
	private $ip_traffic_rule_ip_subnet_mapping;
	private $ip_traffic_rule_chains;
	private $ip_traffic_rule_actions;
	private $ip_traffic_rule_if_roles;
	
    function __construct ()
    {
		
		
		$config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/database.ini','thelist');
	    $info0 = array(
	    	  'host'      => $config->db->host,
  			  'username'  => $config->db->username,
  		  	  'password'  => $config->db->password,
  		  	  'dbname'    => $config->db->dbname,
		);
		
		$this->thelist_adapter = Zend_Db::factory($config->db->adapter, $info0);
		
		
		$ip_flows = new Zend_Config_Ini(APPLICATION_PATH.'/configs/database.ini','traffic_flow');
		$info2 = array(
			    	  'host'      => $ip_flows->db->host,
		  			  'username'  => $ip_flows->db->username,
		  		  	  'password'  => $ip_flows->db->password,
		  		  	  'dbname'    => $ip_flows->db->dbname,
		);
		
		$this->ip_flows_adapter = Zend_Db::factory($ip_flows->db->adapter, $info2);
		
		$mydns = new Zend_Config_Ini(APPLICATION_PATH.'/configs/database.ini','mydns');
		$info3 = array(
					   	'host'      => $mydns->db->host,
				  		'username'  => $mydns->db->username,
				  		'password'  => $mydns->db->password,
				  		'dbname'    => $mydns->db->dbname,
		);
		
		$this->mydns_adapter = Zend_Db::factory($mydns->db->adapter, $info3);
		
		$syslog = new Zend_Config_Ini(APPLICATION_PATH.'/configs/database.ini','syslog');
		$info4 = array(
						'host'      => $syslog->db->host,
						'username'  => $syslog->db->username,
						'password'  => $syslog->db->password,
						'dbname'    => $syslog->db->dbname,
		);
		
		$this->syslog_adapter = Zend_Db::factory($syslog->db->adapter, $info4);
		
		$tracking = new Zend_Config_Ini(APPLICATION_PATH.'/configs/database.ini','tracking');
		$info5 = array(
								'host'      => $tracking->db->host,
								'username'  => $tracking->db->username,
								'password'  => $tracking->db->password,
								'dbname'    => $tracking->db->dbname,
		);
		
		$this->tracking_adapter = Zend_Db::factory($tracking->db->adapter, $info5);
	
		$config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/database.ini','thelist_information_schema');
		$info = array(
				  'host'      => $config->db->host,
				  'username'  => $config->db->username,
			  	  'password'  => $config->db->password,
			  	  'dbname'    => $config->db->dbname,
		);
	
		$this->thelist_information_schema_adapter = Zend_Db::factory($config->db->adapter, $info);
		
		
		
		//define users table
		$this->users = new Zend_Db_Table(
    										array(
    											  'db' => $this->thelist_adapter,
    											  'name'=>'users'
    											  )
    									 );
    	
    	$this->app_event_logs = new Zend_Db_Table(
    										array(
    											  'db' => $this->thelist_adapter,
    											  'name'=>'app_event_logs'
    											  )
    									 );
    	$this->user_event_logs = new Zend_Db_Table(
    										array(
    											  'db' => $this->thelist_adapter,
    											  'name'=>'user_event_logs'
    											  )
    									 );
    	
    	$this->acl_access_control_list = new Zend_Db_Table(
    										array(
    	    									  'db' => $this->thelist_adapter,
    	    									  'name'=>'acl_access_control_list'
    										)
    									);
       	$this->acl_roles		= new Zend_Db_Table(
    										array(
    	    									  'db' => $this->thelist_adapter,
    	    									  'name'=>'acl_roles'
    										)
    									);
    	$this->acl_resources    = new Zend_Db_Table(
    										array(
    	    									  'db' => $this->thelist_adapter,
    	    									  'name'=>'acl_resources'
    										)
    									);
    	
    	$this->acl_privileges   = new Zend_Db_Table(
    										array(
    	    									  'db' => $this->thelist_adapter,
    	    									  'name'=>'acl_privileges'
    										)
    									);
    	
    	$this->menus   = new Zend_Db_Table(
    										array(
    	    	    							  'db' => $this->thelist_adapter,
    	    	    							  'name'=>'menus'
    											 )
    									);
    	
    	$this->menuitems   = new Zend_Db_Table(
    										array(
    	    	    	    					  'db' => $this->thelist_adapter,
    	    	    	    					  'name'=>'menuitems'
    											 )
    									);
    	
    	$this->htmlpages   = new Zend_Db_Table(
    										array(
    	    	    	    	    			  'db' => $this->thelist_adapter,
    	    	    	    	    			  'name'=>'htmlpages'
    											 )
    									);
    	
    	$this->projects  	 = new Zend_Db_Table(
    										array(
    	    	    	    	    	    	  'db' => $this->thelist_adapter,
    	    	    	    	    	    	  'name'=>'projects'
    											)
    										);
    	$this->project_entities   = new Zend_Db_Table(
    										array(
    	    	    	    	    	    	   'db' => $this->thelist_adapter,
    	    	    	    	    	    	   'name'=>'project_entities'
    											  )
    									);
    	
    	$this->contacts   = new Zend_Db_Table(
    										array(
    	    	    	    	    	       	   'db' => $this->thelist_adapter,
    	    	    	    	    	       	   'name'=>'contacts'
    											)
    									);
    	
    	
    	$this->project_contact_mapping = new Zend_Db_Table(
    										array(
    	    	    	    	    	       	   'db' => $this->thelist_adapter,
    	    	    	    	    	       	   'name'=>'project_contact_mapping'
    											)
    									);
    	$this->buildings 			   = new Zend_Db_Table(
    										array(
    	    	    	    	    	       	   'db' => $this->thelist_adapter,
    	    	    	    	    	       	   'name'=>'buildings'
    											)
    									);
    	$this->tasks 			   	   = new Zend_Db_Table(
    										array(
    	    	    	    	    	    	   'db' => $this->thelist_adapter,
    	    	    	    	    	    	   'name'=>'tasks'
    										)
    									);
    	 
    	$this->project_task_mapping	   = new Zend_Db_Table(
    										array(
    	    	    	    	       	    	   'db' => $this->thelist_adapter,
    	    	    	    	       	    	   'name'=>'project_task_mapping'
    										)
    									);
    	 
    	$this->units	   			   = new Zend_Db_Table(
    										array(
    	    	    	    	    	       	    	   'db' => $this->thelist_adapter,
    	    	    	    	    	       	    	   'name'=>'units'
    									    )
    									);
    	
    	$this->project_entity_mapping  = new Zend_Db_Table(
    										array(
    	    	    	    	    	    	       	    	   'db' => $this->thelist_adapter,
    	    	    	    	    	    	       	    	   'name'=>'project_entity_mapping'
    										)
    									);
    	
    	
    	$this->items  				   = new Zend_Db_Table(
    										array(
    	    	    	    	    	       	       	    	   'db' => $this->thelist_adapter,
    	    	    	    	    	       	       	    	   'name'=>'items'
    										)
    									);
    	
    	$this->queues  				   = new Zend_Db_Table(
    										array(
    	    	    	    	    	       	       	    	   'db' => $this->thelist_adapter,
    	    	    	    	    	       	       	    	   'name'=>'queues'
    										)
    									);
    	
    	$this->notes   				   = new Zend_Db_Table(
    										array(
    	    	    	    	    	       	       	    	   'db' => $this->thelist_adapter,
    	    	    	    	    	       	       	    	   'name'=>'notes'
    										)
    									);
    	
    	$this->note_attachments		   = new Zend_Db_Table(
    										array(
    	    	    	    	    	       	       	    	   'db' => $this->thelist_adapter,
    	    	    	    	    	       	       	    	   'name'=>'note_attachments'
    										)
    									);
    	
    	$this->task_note_mapping       = new Zend_Db_Table(
    										array(
    	    	    	    	    	       	       	    	   'db' => $this->thelist_adapter,
    	    	    	    	    	       	       	    	   'name'=>'task_note_mapping'
    										)
    									);
    	 	
    	$this->queue_groups		       = new Zend_Db_Table(
    										array(
    	    	    	    	    	    	       	       	   'db' => $this->thelist_adapter,
    	    	    	    	    	    	       	       	   'name'=>'queue_groups'
    											 )
    									);
    	
    	$this->building_contact_mapping= new Zend_Db_Table(
    										array(
    	    	    	    	    	    	       	       	   'db' => $this->thelist_adapter,
    	    	    	    	    	    	       	       	   'name'=>'building_contact_mapping'
    											 )
    									);
    	
    	$this->building_task_mapping	= new Zend_Db_Table(
    										array(
    	    	    	    	    	    	       	       	   'db' => $this->thelist_adapter,
    	    	    	    	    	    	       	       	   'name'=>'building_task_mapping'
    											 )
    									);
    	
    	$this->user_queue_mapping		= new Zend_Db_Table(
    										array(
    																'db' => $this->thelist_adapter,
    																'name'=>'user_queue_mapping'
    											  )
    									);
    	
    	$this->homerun_types			= new Zend_Db_Table(
    										array(
    	    														'db' => $this->thelist_adapter,
    	    														'name'=>'homerun_types'
    									  		 )
    								  	);
    	
    	$this->unit_homerun_mapping		= new Zend_Db_Table(
    										array(
    	    	    												'db' => $this->thelist_adapter,
    	    	    												'name'=>'unit_homerun_mapping'
    											 )
    									);
    	
    	$this->unit_service_plan_mapping= new Zend_Db_Table(
    										array(
    	    	    												'db' => $this->thelist_adapter,
    	    	    												'name'=>'unit_service_plan_mapping'
    											 )
    									);
    	
    	
    	$this->unit_service_point_mapping= new Zend_Db_Table(
    										array(
    	    	    	    										'db' => $this->thelist_adapter,
    	    	    	    										'name'=>'unit_service_point_mapping'
    											)
    									);
    	
    	$this->service_points			= new Zend_Db_Table(
    										array(
    	    	    	    										'db' => $this->thelist_adapter,
    	    	    	    										'name'=>'service_points'
    											)
    									);
    	
    	$this->unit_groups				= new Zend_Db_Table(
    										array(
    	    	    	    										'db' => $this->thelist_adapter,
    	    	    	    										'name'=>'unit_groups'
    											)
    									);
    	$this->unit_group_mapping		= new Zend_Db_Table(
    										array(
    	    	    	    										'db' => $this->thelist_adapter,
    	    	    	    										'name'=>'unit_group_mapping'
    											)
    									);
    	$this->equipment_logs           = new Zend_Db_Table(
    										array(
    	    	    	    										'db' => $this->thelist_adapter,
    	    	    	    										'name'=>'equipment_logs'
    											)
    									);
    	
    	$this->actions					= new Zend_Db_Table(
    										array(
    	    	    	    										'db' => $this->thelist_adapter,
    	    	    	    										'name'=>'actions'
    											)
    									);
    	
    	$this->end_user_servies         = new Zend_Db_Table(
    										array(
    	    	    	    	    								'db' => $this->thelist_adapter,
    	    	    	    	    								'name'=>'end_user_services'
    											  )
    									);
    	 
    	$this->end_user_service_contact_mapping         = new Zend_Db_Table(
    														array(
    	    	    	    	    	    						'db' => $this->thelist_adapter,
    	    	    	    	    	    						'name'=>'end_user_service_contact_mapping'
    															)
    									);
    	
    	$this->searchboxes				= new Zend_Db_Table(
    														array(
    																'db' => $this->thelist_adapter,
    	    	    	    	    								'name'=>'searchboxes'
    															)
    									);
    	
    	$this->end_user_services		= new Zend_Db_Table(
    														array(
    	    																'db' => $this->thelist_adapter,
    	    	    	    	    	    								'name'=>'end_user_services'
    															)
    									);
    	
    	
    	$this->service_plan_groups						= new Zend_Db_Table(
    														array(
    	    	    																'db' => $this->thelist_adapter,
    	    	    	    	    	    	    								'name'=>'service_plan_groups'
    															 )
    									);
    	
    	$this->equipment_type_groups	= new Zend_Db_Table(
    											array(
    	    	    									'db'  => $this->thelist_adapter,
    	    	    	    	    					'name'=>'equipment_type_groups'
    												 )
    									);
    	
    	$this->eq_type_group_mapping	= new Zend_Db_Table(
    											array(
    	    	    	    							'db'  => $this->thelist_adapter,
    	    	    	    							'name'=>'eq_type_group_mapping'
    												 )
    									);
    	
    	$this->mac_address_prefixes		= new Zend_Db_Table(
    											array(
    	    	    	    	    					'db'  => $this->thelist_adapter,
    	    	    	    	    					'name'=>'mac_address_prefixes'
    												 )
    									);

    	
    	$this->equipment_default_provisioning_plans = new Zend_Db_Table(
    													array(
	    	    	    	    	    					'db'  => $this->thelist_adapter,
    		    	    	    	    					'name'=>'equipment_default_provisioning_plans'
    														 )
    												);
    	
    	
    	$this->service_plan_temp_quote_mapping = new Zend_Db_Table(
    												array(
	    	    	    	    	    				'db'  => $this->thelist_adapter,
    		    	    	    	    				'name'=>'service_plan_temp_quote_mapping'
    													 )
    											);
    	
    	$this->service_plan_temp_quote_eq_type_mapping = new Zend_Db_Table(
    														array(
	    	    	    	    	    						'db'  => $this->thelist_adapter,
    		    	    	    	    						'name'=>'service_plan_temp_quote_eq_type_mapping'
    													 		 )
    													 );
    	
    	$this->service_plan_temp_quote_option_mapping = new Zend_Db_Table(
    														array(
    		    	    	    	    	    				'db'  => $this->thelist_adapter,
    	    		    	    	    	    				'name'=>'service_plan_temp_quote_option_mapping'
    															 )
    													);
    	
    	
    	
    	
    	
//MARTIN TABLES START
    	$this->equipments     		  = new Zend_Db_Table(
    	array(
    																		'db' => $this->thelist_adapter,
    																		'name'=>'equipments',
    																		'primary' => 'eq_id'
    	)
    	);
    	$this->equipment_types     		  = new Zend_Db_Table(
    	array(
    	    																		'db' => $this->thelist_adapter,
    	    																		'name'=>'equipment_types',
    	    																		
    	)
    	);
    	$this->interfaces      		= new Zend_Db_Table(
    	array(
    																		'db' => $this->thelist_adapter,
    																		'name'=>'interfaces'
    	)
    	);
    	$this->ip_addresses      	 = new Zend_Db_Table(
    	array(
    																		'db' => $this->thelist_adapter,
    																		'name'=>'ip_addresses'
    	)
    	);
    	 
    	$this->ip_subnets       = new Zend_Db_Table(
    	array(
    																		'db' => $this->thelist_adapter,
    																		'name'=>'ip_subnets'
    	)
    	);
    	 
    	$this->interface_relationships     			= new Zend_Db_Table(
    	array(
    																    	'db' => $this->thelist_adapter,
    																    	'name'=>'interface_relationships'
    	)
    	);
    	
    	$this->connection_queues     	  = new Zend_Db_Table(
    	array(
    																		'db' => $this->thelist_adapter,
    																		'name'=>'connection_queues'
    	)
    	);
    	 
//     	$this->connection_queue_types  	  = new Zend_Db_Table(
//     	array(
//     																		'db' => $this->thelist_adapter,
//     																		'name'=>'connection_queue_types'
//     	)
//     	);

    	$this->frame_matches     	  = new Zend_Db_Table(
    	array(
    	    							'db' => $this->thelist_adapter,
    	    							'name'=>'frame_matches'
    	)
    	);
    	
    	$this->frame_headers     	  = new Zend_Db_Table(
    	array(
    	    							'db' => $this->thelist_adapter,
    	    							'name'=>'frame_header'
    	)
    	);
    	
    	$this->connection_queue_filters  	  = new Zend_Db_Table(
    	array(
    																		'db' => $this->thelist_adapter,
    																		'name'=>'connection_queue_filters'
    	)
    	);
    	 
    	$this->apis  	  = new Zend_Db_Table(
    	array(
    	    																'db' => $this->thelist_adapter,
    	    																'name'=>'apis'
    	)
    	);
    	 
    	$this->equipment_apis  	  = new Zend_Db_Table(
    	array(
    	    																'db' => $this->thelist_adapter,
    	    																'name'=>'equipment_apis'
    	)
    	);
    	 
    	$this->equipment_auths  	  = new Zend_Db_Table(
    	array(
    	    																'db' => $this->thelist_adapter,
    	    																'name'=>'equipment_auths'
    	)
    	);
    	 
    	
    	$this->interface_types			= new Zend_Db_Table(
    	array(
    	    	    	    	    	    	    	       	       	   'db' => $this->thelist_adapter,
    	    	    	    	    	    	    	       	       	   'name'=>'interface_types'
    	)
    	);
    	
    	$this->interface_type_allowed_config_values			= new Zend_Db_Table(
    	array(
					'db' => $this->thelist_adapter,
					'name'=>'interface_type_allowed_config_values'
    	)
    	);
//     	$this->features			= new Zend_Db_Table(
//     	array(
//     	    	    	    	    	    	    	    	       	       	   'db' => $this->thelist_adapter,
//     	    	    	    	    	    	    	    	       	       	   'name'=>'features'
//     	)
//     	);
    	$this->interface_features			= new Zend_Db_Table(
    	array(
    	    	    	    	    	    	    	    	       	       	   'db' => $this->thelist_adapter,
    	    	    	    	    	    	    	    	       	       	   'name'=>'interface_features'
    	)
    	);
    	$this->interface_feature_mapping			= new Zend_Db_Table(
    	array(
										'db' => $this->thelist_adapter,
										'name'=>'interface_feature_mapping'
    	)
    	);
    	$this->interface_type_feature_mapping			= new Zend_Db_Table(
    	array(
										'db' => $this->thelist_adapter,
										'name'=>'interface_type_feature_mapping'
    	)
    	);
    	 
    	
    	$this->interface_connections			= new Zend_Db_Table(
    	array(
    	    	    	    	    	    	    	    	    	       	       	   'db' => $this->thelist_adapter,
    	    	    	    	    	    	    	    	    	       	       	   'name'=>'interface_connections'
    	)
    	);
    	
    	$this->purchase_order_items			= new Zend_Db_Table(
    	array(
    	    	    	    	    	    	    	    	    	    	       	       	   'db' => $this->thelist_adapter,
    	    	    	    	    	    	    	    	    	    	       	       	   'name'=>'purchase_order_items'
    	)
    	);
    	
    	$this->purchase_orders			= new Zend_Db_Table(
    	array(
    	    	    	    	    	    	    	    	    	    	       	       	   'db' => $this->thelist_adapter,
    	    	    	    	    	    	    	    	    	    	       	       	   'name'=>'purchase_orders'
    	)
    	);
    	
    	$this->purchase_request_items			= new Zend_Db_Table(
    	array(
    	    	    	    	    	    	    	    	    	    	       	       	   'db' => $this->thelist_adapter,
    	    	    	    	    	    	    	    	    	    	       	       	   'name'=>'purchase_request_items'
    	)
    	);
    	
    	$this->purchase_request_to_po_mapping			= new Zend_Db_Table(
    	array(
    	    	    	    	    	    	    	    	    	    	       	       	   'db' => $this->thelist_adapter,
    	    	    	    	    	    	    	    	    	    	       	       	   'name'=>'purchase_request_to_po_mapping'
    	)
    	);
    	
    	$this->purchase_requests			= new Zend_Db_Table(
    	array(
    	    	    	    	    	    	    	    	    	    	       	       	   'db' => $this->thelist_adapter,
    	    	    	    	    	    	    	    	    	    	       	       	   'name'=>'purchase_requests'
    	)
    	);
    	
    	$this->vendors			= new Zend_Db_Table(
    	array(
    	    	    	    	    	    	    	    	    	    	       	       	   'db' => $this->thelist_adapter,
    	    	    	    	    	    	    	    	    	    	       	       	   'name'=>'vendors'
    	)
    	);
    	
    	$this->static_if_types			= new Zend_Db_Table(
    	array(
    	    	    	    	    	    	    	    	    	    	    	       	       	   'db' => $this->thelist_adapter,
    	    	    	    	    	    	    	    	    	    	    	       	       	   'name'=>'static_if_types'
    	)
    	);
    	$this->eq_type_serial_match			= new Zend_Db_Table(
    	array(
    	    	    	    	    	    	    	    	    	    	    	    	       	       	   'db' => $this->thelist_adapter,
    	    	    	    	    	    	    	    	    	    	    	    	       	       	   'name'=>'eq_type_serial_match'
    	)
    	);
    	$this->equipment_mapping			= new Zend_Db_Table(
    	array(
    	    	    	    	    	    	    	    	    	    	    	    	    	       	       	   'db' => $this->thelist_adapter,
    	    	    	    	    	    	    	    	    	    	    	    	    	       	       	   'name'=>'equipment_mapping'
    	)
    	);
    	$this->service_plans									= new Zend_Db_Table(
    	array(
						'db' => $this->thelist_adapter,
						'name'=>'service_plans'
    	)
    	);
    	$this->service_plan_option_mapping						= new Zend_Db_Table(
    	array(
    					'db' => $this->thelist_adapter,
    					'name'=>'service_plan_option_mapping'
    	)
    	);
    	$this->service_plan_eq_type_mapping						= new Zend_Db_Table(
    	array(
    	    			'db' => $this->thelist_adapter,
    	    			'name'=>'service_plan_eq_type_mapping'
    	)
    	);
    	$this->service_plan_options								= new Zend_Db_Table(
    	array(
    	    	    	'db' => $this->thelist_adapter,
    	    	    	'name'=>'service_plan_options'
    	)
    	);
    	$this->service_plan_option_groups						= new Zend_Db_Table(
    	array(
						'db' => $this->thelist_adapter,
						'name'=>'service_plan_option_groups'
    	)
    	);
    	$this->service_plan_eq_type_groups						= new Zend_Db_Table(
    	array(
						'db' => $this->thelist_adapter,
						'name'=>'service_plan_eq_type_groups'
    	)
    	);
    	$this->service_plan_eq_type_if_option_mapping			= new Zend_Db_Table(
    	array(
    					'db' => $this->thelist_adapter,
    					'name'=>'service_plan_eq_type_if_option_mapping'
    	)
    	);
  
    	$this->service_plan_group_mapping			= new Zend_Db_Table(
    	array(
    	    	    	'db' => $this->thelist_adapter,
    	    	    	'name'=>'service_plan_group_mapping'
    	)
    	);
    	$this->device_functions					= new Zend_Db_Table(
    	array(
						'db' => $this->thelist_adapter,
						'name'=>'device_functions'
    	)
    	);
    	$this->device_function_mapping			= new Zend_Db_Table(
    	array(
    							'db' => $this->thelist_adapter,
    							'name'=>'device_function_mapping'
    	)
    	);
    	$this->device_commands					= new Zend_Db_Table(
    	array(
    							'db' => $this->thelist_adapter,
    							'name'=>'device_commands'
    	)
    	);
    	$this->device_command_parameters		= new Zend_Db_Table(
    	array(
    							'db' => $this->thelist_adapter,
    							'name'=>'device_command_parameters'
    	)
    	);
    	$this->device_command_parameter_tables	= new Zend_Db_Table(
    	array(
    							'db' => $this->thelist_adapter,
    							'name'=>'device_command_parameter_tables'
    	)
    	);
    	$this->device_command_parameter_columns	= new Zend_Db_Table(
    	array(
    							'db' => $this->thelist_adapter,
    							'name'=>'device_command_parameter_columns'
    	)
    	);
    	$this->device_command_mapping			= new Zend_Db_Table(
    	array(
    							'db' => $this->thelist_adapter,
    							'name'=>'device_command_mapping'
    	)
    	);
    	$this->software_packages				= new Zend_Db_Table(
    	array(
    							'db' => $this->thelist_adapter,
    							'name'=>'software_packages'
    	)
    	);
    	$this->equipment_software_upgrades		= new Zend_Db_Table(
    	array(
    							'db' => $this->thelist_adapter,
    							'name'=>'equipment_software_upgrades'
    	)
    	);
    	$this->equipment_type_software_mapping	= new Zend_Db_Table(
    	array(
    							'db' => $this->thelist_adapter,
    							'name'=>'equipment_type_software_mapping'
    	)
    	);
    	$this->command_regex_mapping	= new Zend_Db_Table(
    	array(
    	    					'db' => $this->thelist_adapter,
    	    					'name'=>'command_regex_mapping'
    	)
    	);
    	$this->command_regex_parameters	= new Zend_Db_Table(
    	array(
    	    					'db' => $this->thelist_adapter,
    	    					'name'=>'command_regex_parameters'
    	)
    	);
    	$this->command_regexs	= new Zend_Db_Table(
    	array(
    	    					'db' => $this->thelist_adapter,
    	    					'name'=>'command_regexs'
    	)
    	);
    	$this->ip_address_mapping	= new Zend_Db_Table(
    	array(
    	    	    			'db' => $this->thelist_adapter,
    	    	    			'name'=>'ip_address_mapping'
    	)
    	);

    	$this->monitoring_data_sources	= new Zend_Db_Table(
    	array(
    	    	    	    	'db' => $this->thelist_adapter,
    	    	    	    	'name'=>'monitoring_data_sources'
    	)
    	);
    	$this->monitoring_guid_ds_mapping	= new Zend_Db_Table(
    	array(
    	    	    	    	   'db' => $this->thelist_adapter,
    	    	    	    	   'name'=>'monitoring_guid_ds_mapping'
    	)
    	);
    	$this->monitoring_guids	= new Zend_Db_Table(
    	array(
    	    	    	    	   'db' => $this->thelist_adapter,
    	    	    	    	   'name'=>'monitoring_guids'
    	)
    	);
    	$this->monitoring_poller_cache	= new Zend_Db_Table(
    	array(
    	    	    	    	   'db' => $this->thelist_adapter,
    	    	    	    	   'name'=>'monitoring_poller_cache'
    	)
    	);
    	$this->monitoring_poller_command_cache	= new Zend_Db_Table(
    	array(
    	    	    	    	   'db' => $this->thelist_adapter,
    	    	    	    	   'name'=>'monitoring_poller_command_cache'
    	)
    	);
    	$this->monitoring_rra_type_mapping	= new Zend_Db_Table(
    	array(
    	    	    	    	   'db' => $this->thelist_adapter,
    	    	    	    	   'name'=>'monitoring_rra_type_mapping'
    	)
    	);
    	$this->monitoring_rra_types	= new Zend_Db_Table(
    	array(
    	    	    	    	   'db' => $this->thelist_adapter,
    	    	    	    	   'name'=>'monitoring_rra_types'
    	)
    	);
       	$this->interface_configuration_mapping	= new Zend_Db_Table(
    	array(
    								'db' => $this->thelist_adapter,
    								'name'=>'interface_configuration_mapping'
    	)
    	);
       	$this->interface_configurations	= new Zend_Db_Table(
    	array(
    								'db' => $this->thelist_adapter,
    								'name'=>'interface_configurations'
    	)
    	);
    	$this->sales_quotes	= new Zend_Db_Table(
    	array(
    	    	    				'db' => $this->thelist_adapter,
    	    	    				'name'=>'sales_quotes'
    	)
    	);
    	
    	$this->service_plan_quote_eq_type_mapping	= new Zend_Db_Table(
    	array(
    	    	    				'db' => $this->thelist_adapter,
    	    	    				'name'=>'service_plan_quote_eq_type_mapping'
    	)
    	);
    	$this->service_plan_quote_option_mapping	= new Zend_Db_Table(
    	array(
    	    	    	    		'db' => $this->thelist_adapter,
    	    	    	    		'name'=>'service_plan_quote_option_mapping'
    	)
    	);
    	 
    	$this->service_plan_quote_mapping	= new Zend_Db_Table(
    	array(
    	    	    	    		'db' => $this->thelist_adapter,
    	    	    	    		'name'=>'service_plan_quote_mapping'
    	)
    	);
   	
    	$this->sales_quote_eq_type_map_equipment_mapping	= new Zend_Db_Table(
    	array(
    	    	    	    	   	'db' => $this->thelist_adapter,
    	    	    	    	    'name'=>'sales_quote_eq_type_map_equipment_mapping'
    	)
    	);
    	
    	$this->calendar_appointments	= new Zend_Db_Table(
    	array(
    	    	    	    	    'db' => $this->thelist_adapter,
    	    	    	    	   	'name'=>'calendar_appointments'
    	)
    	);

    	$this->service_plan_quote_task_mapping	= new Zend_Db_Table(
    	array(
    	    	    	    	   	'db' => $this->thelist_adapter,
    	    	    				'name'=>'service_plan_quote_task_mapping'
    	)
    	);
	
    	$this->calendar_appointment_task_mapping	= new Zend_Db_Table(
    	array(
    	    	    	    	   	'db' => $this->thelist_adapter,
    	    	    	    		'name'=>'calendar_appointment_task_mapping'
    	)
    	);
    	
    	$this->end_user_task_mapping	= new Zend_Db_Table(
    	array(
    	    	    	    	    'db' => $this->thelist_adapter,
    	    	    	    	    'name'=>'end_user_task_mapping'
    	)
    	);

    	$this->service_plan_help	= new Zend_Db_Table(
    	array(
    	    	    	    	    'db' => $this->thelist_adapter,
    	    	    	    	    'name'=>'service_plan_help'
    	)
    	);
    	
    	$this->equipment_roles		= new Zend_Db_Table(
    	array(
    	    	    	    	    'db' => $this->thelist_adapter,
    	    	    	    	    'name'=>'equipment_roles'
    	)
    	);
    	
    	$this->equipment_role_mapping	= new Zend_Db_Table(
    	array(
									'db' => $this->thelist_adapter,
									'name'=>'equipment_role_mapping'
    	)
    	);
    	
    	$this->service_plan_service_point_interface_feature_mapping	= new Zend_Db_Table(
    	array(
    								'db' => $this->thelist_adapter,
    								'name'=>'service_plan_service_point_interface_feature_mapping'
    	)
    	);
    	
    	$this->interface_task_mapping	= new Zend_Db_Table(
    	array(
    	    						'db' => $this->thelist_adapter,
    	    						'name'=>'interface_task_mapping'
    	)
    	);
    	
    	$this->end_user_note_mapping    = new Zend_Db_Table(
    	array(
    								'db' => $this->thelist_adapter,
    								'name'=>'end_user_note_mapping'
    	)
    	);
    	
    	$this->homerun_group_eq_type_mapping	= new Zend_Db_Table(
    	array(
    	    	    				'db' => $this->thelist_adapter,
    	    	    				'name'=>'homerun_group_eq_type_mapping'
    	)
    	);
    	
    	$this->service_plan_if_type_mapping	= new Zend_Db_Table(
    	array(
    	    	    	    		'db' => $this->thelist_adapter,
    	    	    	    		'name'=>'service_plan_if_type_mapping'
    	)
    	);
    	
    	$this->user_locations	= new Zend_Db_Table(
    	array(
    	    	    	    	    'db' => $this->thelist_adapter,
    	    	    	    	    'name'=>'user_locations'
    	)
    	);
    	
    	$this->rr	= new Zend_Db_Table(
    	array(
    	    	    	    	    'db' => $this->mydns_adapter,
    	    	    	    	    'name'=>'rr'
    	)
    	);
    	
    	$this->soa	= new Zend_Db_Table(
    	array(
    	    	    	    	 	'db' => $this->mydns_adapter,
    	    	    	    	   	'name'=>'soa'
    	)
    	);
    	
    	$this->service_plan_quote_ip_address_mapping	= new Zend_Db_Table(
    	array(
    	    	    	    	   'db' => $this->thelist_adapter,
    	    	    	    	   'name'=>'service_plan_quote_ip_address_mapping'
    	)
    	);
    	
    	$this->ip_routes	= new Zend_Db_Table(
    	array(
    	    	    				'db' => $this->thelist_adapter,
    	    	    				'name'=>'ip_routes'
    	)
    	);
    	
    	$this->ip_route_gateways	= new Zend_Db_Table(
    	array(
    	    	    	    		'db' => $this->thelist_adapter,
    	    	    	    		'name'=>'ip_route_gateways'
    	)
    	);
    	
    	$this->service_plan_quote_connection_queue_filter_mapping	= new Zend_Db_Table(
    	array(		
    	    	    	    		'db' => $this->thelist_adapter,
    	    	    	   		 	'name'=>'service_plan_quote_connection_queue_filter_mapping'
    	)	
    	);
    	
    	$this->connection_queue_relationships	= new Zend_Db_Table(
    	array(
    	    	    	    		'db' => $this->thelist_adapter,
    	    	    	    		'name'=>'connection_queue_relationships'
    	)
    	);
    	
    	$this->equipment_application_mapping	= new Zend_Db_Table(
    	array(
									'db' => $this->thelist_adapter,
									'name'=>'equipment_application_mapping'
    	)
    	);
    	
    	
    	$this->equipment_application_metric_mapping	= new Zend_Db_Table(
    	array(
    								'db' => $this->thelist_adapter,
    								'name'=>'equipment_application_metric_mapping'
    	)
    	);
    	
    	$this->equipment_application_metrics	= new Zend_Db_Table(
    	array(
    	    						'db' => $this->thelist_adapter,
    	    						'name'=>'equipment_application_metrics'
    	)
    	);
    	
    	$this->equipment_applications	= new Zend_Db_Table(
    	array(
    	    						'db' => $this->thelist_adapter,
    	    						'name'=>'equipment_applications'
    	)
    	);
    	
    	//syslog adaptor
    	
    	$this->dhcp_request_track_raw	= new Zend_Db_Table(
    	array(
    	    	    	    	   	'db' => $this->syslog_adapter,
    	    	    	    	    'name'=>'dhcp_request_track_raw'
    	)
    	);
    	
    	//tracking adaptor
    	 
    	$this->device_tracking	= new Zend_Db_Table(
    	array(
    	    	    	  			'db' => $this->tracking_adapter,
    	    	    	    		'name'=>'device_tracking'
    	)
    	);
    	
    	$this->ip_protocols	= new Zend_Db_Table(
    	array(
									'db' => $this->thelist_adapter,
									'name'=>'ip_protocols'
    	)
    	);
    	
    	$this->ip_protocol_ports	= new Zend_Db_Table(
    	array(
    								'db' => $this->thelist_adapter,
    								'name'=>'ip_protocol_ports'
    	)
    	);
    	$this->ip_protocol_port_mapping	= new Zend_Db_Table(
    	array(
    								'db' => $this->thelist_adapter,
    								'name'=>'ip_protocol_port_mapping'
    	)
    	);
    	$this->ip_traffic_rule_interface_mapping	= new Zend_Db_Table(
    	array(
    								'db' => $this->thelist_adapter,
    								'name'=>'ip_traffic_rule_interface_mapping'
    	)
    	);
    	$this->ip_traffic_rules	= new Zend_Db_Table(
    	array(
    								'db' => $this->thelist_adapter,
    								'name'=>'ip_traffic_rules'
    	)
    	);
    	$this->ip_traffic_rule_ip_subnets	= new Zend_Db_Table(
    	array(
    								'db' => $this->thelist_adapter,
    								'name'=>'ip_traffic_rule_ip_subnets'
    	)
    	);
    	$this->ip_traffic_rule_ip_subnet_mapping	= new Zend_Db_Table(
    	array(
    								'db' => $this->thelist_adapter,
    								'name'=>'ip_traffic_rule_ip_subnet_mapping'
    	)
    	);
    	
    	$this->ip_traffic_rule_chains	= new Zend_Db_Table(
    	array(
    	    						'db' => $this->thelist_adapter,
    	    						'name'=>'ip_traffic_rule_chains'
    	)
    	);
    	
    	$this->ip_traffic_rule_actions	= new Zend_Db_Table(
    	array(
    	    						'db' => $this->thelist_adapter,
    	    						'name'=>'ip_traffic_rule_actions'
    	)
    	);
    	$this->ip_traffic_rule_if_roles	= new Zend_Db_Table(
    	array(
    	    	    				'db' => $this->thelist_adapter,
    	    	    				'name'=>'ip_traffic_rule_if_roles'
    	)
    	);
    	
    	$this->interface_type_configurations	= new Zend_Db_Table(
    	array(
    	    	    	    		'db' => $this->thelist_adapter,
    	    	    	    		'name'=>'interface_type_configurations'
    	)
    	);
    	
    	$this->equipment_type_application_metrics	= new Zend_Db_Table(
    	array(
    	    	    	    	    'db' => $this->thelist_adapter,
    	    	    	    	    'name'=>'equipment_type_application_metrics'
    	)
    	);
    	
    	$this->eq_type_allowed_metric_values	= new Zend_Db_Table(
    	array(
    	    	    	    	    'db' => $this->thelist_adapter,
    	    	    	    	    'name'=>'eq_type_allowed_metric_values'
    	)
    	);
    	
    	$this->eq_type_applications	= new Zend_Db_Table(
    	array(
    	    	    	    	   	'db' => $this->thelist_adapter,
    	    	    	    	    'name'=>'eq_type_applications'
    	)
    	);
    	
    	$this->contact_addresses	= new Zend_Db_Table(
    	array(
    	    	    	    	  	'db' => $this->thelist_adapter,
    	    	    	    	    'name'=>'contact_addresses'
    	)
    	);
    	
    	$this->contact_email_addresses	= new Zend_Db_Table(
    	array(
    	    	    	    	  	'db' => $this->thelist_adapter,
    	    	    	  			'name'=>'contact_email_addresses'
    	)
    	);
    	
    	$this->contact_phone_numbers	= new Zend_Db_Table(
    	array(
    	    	    	    		'db' => $this->thelist_adapter,
    	    	    	    	  	'name'=>'contact_phone_numbers'
    	)
    	);
    
    }
   
	/// finish constructor
	
	public function get_thelist_adapter(){
		return $this->thelist_adapter;
	}
	
	public function get_ip_flows_adapter(){
		return $this->ip_flows_adapter;
	}
	
	public function get_mydns_adapter(){
		return $this->mydns_adapter;
	}

	public function get_syslog_adapter(){
		return $this->syslog_adapter;
	}
	
	public function get_tracking_adapter(){
		return $this->tracking_adapter;
	}
	
	public function get_thelist_information_schema_adapter(){
		return $this->thelist_information_schema_adapter;
	}
	
	public function get_dhcp_request_track_raw(){
		return $this->dhcp_request_track_raw;
	}
	
	public function get_interface_type_configurations(){
		return $this->interface_type_configurations;
	}
	
	public function get_users(){
		return $this->users;
	}
	
	public function get_app_event_logs(){
		return $this->app_event_logs;
	}
	
	public function get_user_event_logs(){
		return $this->user_event_logs;
	}
	
	public function get_acl_access_control_list(){
		return $this->acl_access_control_list;
	}
	
	public function get_acl_resources(){
		return $this->acl_resources;
	}
	
	public function get_acl_privileges(){
		return $this->acl_privileges;
	}
	
	public function get_acl_roles(){
		return $this->acl_roles;
	}
	
	public function get_menus(){
		return $this->menus;
	}
	
	public function get_menuitems(){
		return $this->menuitems;
	}
	
	public function get_htmlpages(){
		return $this->htmlpages;
	}
	
	public function get_projects(){
		return $this->projects;
	}
	
	public function get_project_entities(){
		return $this->project_entities;
	}
	
	public function get_contacts(){
		return $this->contacts;
	}
	
	public function get_project_contact_mapping(){
		return $this->project_contact_mapping;
	}
	
	public function get_buildings(){
		return $this->buildings;
	}
	
	public function get_tasks(){
		return $this->tasks;
	}
	
	public function get_project_task_mapping(){
		return $this->project_task_mapping;
	}
	
	public function get_units(){
		return $this->units;
	}
	
	public function get_project_entity_mapping(){
		return $this->project_entity_mapping;
	}
	
	public function get_items(){
		return $this->items;
	}
	
	public function get_queues(){
		return $this->queues;
	}
	
	public function get_notes(){
		return $this->notes;
	}
	
	public function get_note_attachments(){
		return $this->note_attachments;
	}
	
	public function get_task_note_mapping(){
		return $this->task_note_mapping;
	}
	
	public function get_queue_groups(){
		return $this->queue_groups;
	}
	
	public function get_building_contact_mapping(){
		return $this->building_contact_mapping;
	}
	
	public function get_building_task_mapping(){
		return $this->building_task_mapping;
	}
	
	public function get_user_queue_mapping(){
		return $this->user_queue_mapping;
	}
	public function get_homerun_types(){
		return $this->homerun_types;
	}
	public function get_unit_homerun_mapping(){
		return $this->unit_homerun_mapping;
	}
	public function get_unit_service_plan_mapping(){
		return $this->unit_service_plan_mapping;
	}
	public function get_unit_service_point_mapping(){
		return $this->unit_service_point_mapping;
	}
	
	public function get_service_points(){
		return $this->service_points;
	}
	
	public function get_unit_groups(){
		return $this->unit_groups;	
	}
	
	public function get_unit_group_mapping(){
		return $this->unit_group_mapping;
	}
	
	public function get_equipment_logs(){
		return $this->equipment_logs;
	}
	
	public function get_actions(){
		return $this->actions;
	}
	
	public function get_searchboxes(){
		return $this->searchboxes;
	}
	
	public function get_end_user_services(){
		return $this->end_user_services;
	}
	
	public function get_end_user_service_contact_mapping(){
		return $this->end_user_service_contact_mapping;
	}
	
	public function get_service_plan_groups(){
		return $this->service_plan_groups;
	}
	public function get_service_plan_quote_task_mapping(){
		return $this->service_plan_quote_task_mapping;
	}
	public function get_end_user_task_mapping(){
		return $this->end_user_task_mapping;
	}
	public function get_equipment_type_groups(){
		return $this->equipment_type_groups;
	}
	public function get_service_plan_help(){
		return $this->service_plan_help;
	}
	public function get_equipment_role_mapping(){
		return $this->equipment_role_mapping;
	}
	public function get_equipment_roles(){
		return $this->equipment_roles;
	}
	public function get_interface_task_mapping(){
		return $this->interface_task_mapping;
	}
	
	public function get_end_user_note_mapping(){
		return $this->end_user_note_mapping;
	}
	
	public function get_homerun_group_eq_type_mapping(){
		return $this->homerun_group_eq_type_mapping;
	}
	public function get_eq_type_group_mapping(){
		return $this->eq_type_group_mapping;
	}
	public function get_mac_address_prefixes(){
		return $this->mac_address_prefixes;
	}
	public function get_service_plan_if_type_mapping(){
		return $this->service_plan_if_type_mapping;
	}
	
	public function get_equipment_default_provisioning_plans(){
		return $this->equipment_default_provisioning_plans;
	}
	
	public function get_service_plan_temp_quote_mapping()
	{
		return $this->service_plan_temp_quote_mapping;
	}
	
	public function get_service_plan_temp_quote_eq_type_mapping()
	{
		return $this->service_plan_temp_quote_eq_type_mapping;
	}
	
	public function get_service_plan_temp_quote_option_mapping(){
		return $this->service_plan_temp_quote_option_mapping;
	}
	
	//mydns
	public function get_rr(){
		return $this->rr;
	}
	public function get_soa(){
		return $this->soa;
	}
	
	

	
	//MARTIN
	public function get_equipments(){
		return $this->equipments;
	}
	public function get_equipment_types(){
		return $this->equipment_types;
	}
	public function get_interfaces(){
		return $this->interfaces;
	}
	public function get_ip_addresses(){
		return $this->ip_addresses;
	}
	public function get_ip_subnets(){
		return $this->ip_subnets;
	}
	public function get_interface_relationships(){
		return $this->interface_relationships;
	}
	public function get_connection_queues(){
		return $this->connection_queues;
	}
	public function get_interface_type_allowed_config_values(){
		return $this->interface_type_allowed_config_values;
	}
// 	public function get_connection_queue_types(){
// 		return $this->connection_queue_types;
// 	}

	public function get_frame_matches(){
		return $this->frame_matches;
	}
	public function get_frame_headers(){
		return $this->frame_header;
	}
	public function get_connection_queue_filters(){
		return $this->connection_queue_filters;
	}
	public function get_apis(){
		return $this->apis;
	}
	public function get_equipment_apis(){
		return $this->equipment_apis;
	}
	public function get_equipment_auths(){
		return $this->equipment_auths;
	}
	public function get_interface_types(){
		return $this->interface_types;
	}
// 	public function get_features(){
// 		return $this->features;
// 	}
	public function get_interface_features(){
		return $this->interface_features;
	}	
	public function get_interface_feature_mapping(){
		return $this->interface_feature_mapping;
	}
	public function get_interface_type_feature_mapping(){
		return $this->interface_type_feature_mapping;
	}
	
	public function get_interface_connections(){
		return $this->interface_connections;
	}	
	public function get_purchase_order_items(){
		return $this->purchase_order_items;
	}
	public function get_purchase_orders(){
		return $this->purchase_orders;
	}
	public function get_purchase_request_items(){
		return $this->purchase_request_items;
	}
	public function get_purchase_request_to_po_mapping(){
		return $this->purchase_request_to_po_mapping;
	}
	public function get_purchase_requests(){
		return $this->purchase_requests;
	}
	public function get_vendors(){
		return $this->vendors;
	}
	public function get_static_if_types(){
		return $this->static_if_types;
	}
	public function get_eq_type_serial_match(){
		return $this->eq_type_serial_match;
	}
	public function get_equipment_mapping(){
		return $this->equipment_mapping;
	}
	public function get_service_plans(){
		return $this->service_plans;
	}
	public function get_service_plan_option_mapping(){
		return $this->service_plan_option_mapping;
	}
	public function get_service_plan_eq_type_mapping(){
		return $this->service_plan_eq_type_mapping;
	}
	public function get_service_plan_options(){
		return $this->service_plan_options;
	}
	public function get_service_plan_option_groups(){
		return $this->service_plan_option_groups;
	}
	
	public function get_service_plan_eq_type_groups(){
		return $this->service_plan_eq_type_groups;
	}
	public function get_service_plan_eq_type_if_option_mapping(){
		return $this->service_plan_eq_type_if_option_mapping;
	}
	public function get_service_plan_group_mapping(){
		return $this->service_plan_group_mapping;
	}
	public function get_device_functions(){
		return $this->device_functions;
	}
	public function get_device_function_mapping(){
		return $this->device_function_mapping;
	}
	public function get_device_commands(){
		return $this->device_commands;
	}
	public function get_device_command_parameters(){
		return $this->device_command_parameters;
	}
	public function get_device_command_parameter_tables(){
		return $this->device_command_parameter_tables;
	}
	public function get_device_command_parameter_columns(){
		return $this->device_command_parameter_columns;
	}
	public function get_device_command_mapping(){
		return $this->device_command_mapping;
	}
	public function get_software_packages(){
		return $this->software_packages;
	}
	public function get_equipment_software_upgrades(){
		return $this->equipment_software_upgrades;
	}
	public function get_equipment_type_software_mapping(){
		return $this->equipment_type_software_mapping;
	}
	public function get_command_regex_mapping(){
		return $this->command_regex_mapping;
	}
	public function get_command_regex_parameters(){
		return $this->command_regex_parameters;
	}
	public function get_command_regexs(){
		return $this->command_regexs;
	}
	public function get_ip_address_mapping(){
		return $this->ip_address_mapping;
	}
	public function get_monitoring_data_sources(){
		return $this->monitoring_data_sources;
	}
	public function get_monitoring_guid_ds_mapping(){
		return $this->monitoring_guid_ds_mapping;
	}
	public function get_monitoring_guids(){
		return $this->monitoring_guids;
	}
	public function get_monitoring_poller_cache(){
		return $this->monitoring_poller_cache;
	}
	public function get_monitoring_poller_command_cache(){
		return $this->monitoring_poller_command_cache;
	}
	public function get_monitoring_rra_type_mapping(){
		return $this->monitoring_rra_type_mapping;
	}
	public function get_monitoring_rra_types(){
		return $this->monitoring_rra_types;
	}
	public function get_interface_configuration_mapping(){
		return $this->interface_configuration_mapping;
	}
	public function get_interface_configurations(){
		return $this->interface_configurations;
	}
	public function get_sales_quotes(){
		return $this->sales_quotes;
	}
	public function get_service_plan_quote_eq_type_mapping(){
		return $this->service_plan_quote_eq_type_mapping;
	}
	public function get_service_plan_quote_option_mapping(){
		return $this->service_plan_quote_option_mapping;
	}
	public function get_service_plan_quote_mapping(){
		return $this->service_plan_quote_mapping;
	}
	public function get_sales_quote_eq_type_map_equipment_mapping(){
		return $this->sales_quote_eq_type_map_equipment_mapping;
	}
	public function get_calendar_appointments(){
		return $this->calendar_appointments;
	}
	public function get_calendar_appointment_task_mapping(){
		return $this->calendar_appointment_task_mapping;
	}
	public function get_service_plan_service_point_interface_feature_mapping(){
		return $this->service_plan_service_point_interface_feature_mapping;
	}
	public function get_user_locations(){
		return $this->user_locations;
	}
	
	public function get_service_plan_quote_ip_address_mapping(){
		return $this->service_plan_quote_ip_address_mapping;
	}
	public function get_ip_routes(){
		return $this->ip_routes;
	}
	public function get_ip_route_gateways(){
		return $this->ip_route_gateways;
	}
	public function get_service_plan_quote_connection_queue_filter_mapping(){
		return $this->service_plan_quote_connection_queue_filter_mapping;
	}
	public function get_connection_queue_relationships(){
		return $this->connection_queue_relationships;
	}
	
	public function get_equipment_application_mapping(){
		return $this->equipment_application_mapping;
	}
	public function get_equipment_application_metric_mapping(){
		return $this->equipment_application_metric_mapping;
	}
	public function get_equipment_application_metrics(){
		return $this->equipment_application_metrics;
	}
	public function get_equipment_applications(){
		return $this->equipment_applications;
	}
	
	public function get_device_tracking(){
		return $this->device_tracking;
	}
	public function get_ip_protocols(){
		return $this->ip_protocols;
	}
	public function get_ip_protocol_ports(){
		return $this->ip_protocol_ports;
	}
	public function get_ip_protocol_port_mapping(){
		return $this->ip_protocol_port_mapping;
	}
	public function get_ip_traffic_rule_interface_mapping(){
		return $this->ip_traffic_rule_interface_mapping;
	}
	public function get_ip_traffic_rules(){
		return $this->ip_traffic_rules;
	}
	public function get_ip_traffic_rule_ip_subnets(){
		return $this->ip_traffic_rule_ip_subnets;
	}
	public function get_ip_traffic_rule_ip_subnet_mapping(){
		return $this->ip_traffic_rule_ip_subnet_mapping;
	}

	public function get_ip_traffic_rule_chains(){
		return $this->ip_traffic_rule_chains;
	}
	public function get_ip_traffic_rule_actions(){
		return $this->ip_traffic_rule_actions;
	}
	public function get_ip_traffic_rule_if_roles(){
		return $this->ip_traffic_rule_if_roles;
	}
	public function get_equipment_type_application_metrics(){
		return $this->equipment_type_application_metrics;
	}
	public function get_eq_type_allowed_metric_values(){
		return $this->eq_type_allowed_metric_values;
	}
	public function get_eq_type_applications(){
		return $this->eq_type_applications;
	}
	public function get_contact_addresses(){
		return $this->contact_addresses;
	}
	public function get_contact_email_addresses(){
		return $this->contact_email_addresses;
	}
	public function get_contact_phone_numbers(){
		return $this->contact_phone_numbers;
	}

	//simple update and delete functions with logging
	
	//update a single cell
	public function set_single_attribute($pri_key_to_update, $table_name, $column, $new_value,$class=null,$method=null)
	{

		$this->log	= Zend_Registry::get('logs');
		
		if ($table_name != 'rr' && $table_name != 'soa') {
				
			$sql_find_pri_key = "SELECT COLUMN_NAME FROM information_schema.columns
							 					 WHERE TABLE_SCHEMA = 'thelist'
												 AND TABLE_NAME = '".$table_name."' 
												 AND extra = 'auto_increment'
												";
				
			$auto_increment_column = $this->get_thelist_adapter()->fetchOne($sql_find_pri_key);
				
		} else {
		
			$sql_find_pri_key = "SELECT COLUMN_NAME FROM information_schema.columns
							 					 WHERE TABLE_SCHEMA = 'mydns'
												 AND TABLE_NAME = '".$table_name."' 
												 AND extra = 'auto_increment'
												";
				
			$auto_increment_column = $this->get_mydns_adapter()->fetchOne($sql_find_pri_key);
		
		}

	
		$private_format_of_column_name = "_".$column;
		$database_method = "get_".$table_name;
	
		$sql_old = "SELECT * FROM $table_name
					WHERE $auto_increment_column = '".$pri_key_to_update."'
					";
	
		if ($table_name != 'rr' && $table_name != 'soa') {
			
			$old = $this->get_thelist_adapter()->fetchRow($sql_old);
			
		} else {
			
			$old = $this->get_mydns_adapter()->fetchRow($sql_old);
			
		}
		
		if($old[$column] != $new_value){
	
			//$trace  = debug_backtrace();
			//$method = $trace[0]["function"];
	
			//log it
			$this->log->user_log('change_single_column', $class, $method, $auto_increment_column, $pri_key_to_update, $old, array($column => $new_value), '', '');
	
			$data= array(
				
			"$column"	=> $new_value
			
			);
			
			$this->$database_method()->update($data,"".$auto_increment_column."='".$pri_key_to_update."'");
			
			$return = array();
			array_push($return, $private_format_of_column_name, $new_value);
			
			return $return;
			
		}
	
		//if no update was done because the old and new data is the same
		return false;
	
	}
	
	public function insert_single_row($table_name,$new_row,$class=null,$method=null)
	{
		$this->log	= Zend_Registry::get('logs');
		
		if ($table_name != 'rr' && $table_name != 'soa') {
			
			$sql_find_pri_key = "SELECT COLUMN_NAME FROM information_schema.columns
					 					 WHERE TABLE_SCHEMA = 'thelist'
										 AND TABLE_NAME = '".$table_name."' 
										 AND extra = 'auto_increment'
										";
			
			$auto_increment_column = $this->get_thelist_adapter()->fetchOne($sql_find_pri_key);
			
		} else {

			$sql_find_pri_key = "SELECT COLUMN_NAME FROM information_schema.columns
					 					 WHERE TABLE_SCHEMA = 'mydns'
										 AND TABLE_NAME = '".$table_name."' 
										 AND extra = 'auto_increment'
										";
			
			$auto_increment_column = $this->get_mydns_adapter()->fetchOne($sql_find_pri_key);

		}

		$database_method = "get_".$table_name;
		$pri_key = $this->$database_method()->insert($new_row);
		
		$this->log->user_log('insert_row to a table named '.$table_name, $class, $method, $auto_increment_column, $pri_key, $new_row, '', '', '');
		return $pri_key;
		
	}
	
	//delete a single row
	public function delete_single_row($pri_key_to_delete, $table_name,$class=null,$method=null)
	{
		$this->log	= Zend_Registry::get('logs');
		
			if ($table_name != 'rr' && $table_name != 'soa') {
			
			$sql_find_pri_key = "SELECT COLUMN_NAME FROM information_schema.columns
					 					 WHERE TABLE_SCHEMA = 'thelist'
										 AND TABLE_NAME = '".$table_name."' 
										 AND extra = 'auto_increment'
										";
			
			$auto_increment_column = $this->get_thelist_adapter()->fetchOne($sql_find_pri_key);
			
		} else {

			$sql_find_pri_key = "SELECT COLUMN_NAME FROM information_schema.columns
					 					 WHERE TABLE_SCHEMA = 'mydns'
										 AND TABLE_NAME = '".$table_name."' 
										 AND extra = 'auto_increment'
										";
			
			$auto_increment_column = $this->get_mydns_adapter()->fetchOne($sql_find_pri_key);

		}
				
		
		$database_method = "get_".$table_name;
		
		$sql_before_delete = 	"SELECT * FROM $table_name
								WHERE $auto_increment_column=$pri_key_to_delete";
		
		if ($table_name != 'rr' && $table_name != 'soa') {
			
			$old = $this->get_thelist_adapter()->fetchRow($sql_before_delete);
			
		} else {
			
			$old = $this->get_mydns_adapter()->fetchRow($sql_before_delete);
			
		}

		if($old != false){

		//log it
		$this->log->user_log('delete_row from a table named '.$table_name, $class, $method, $auto_increment_column, $pri_key_to_delete, $old, '', '', '');
		
		$this->$database_method()->delete("".$auto_increment_column."='".$pri_key_to_delete."'");
		return true;
		
		}
		//if row did not exist
		return false;
	}
}
?>