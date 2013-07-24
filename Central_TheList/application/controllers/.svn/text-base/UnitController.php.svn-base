<?php

class UnitController extends Zend_Controller_Action
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
	
	public function postDispatch(){
	
	}
	
	public function getunitsfrombuildingdropdownajaxAction()
	{
		//get all units in a building for a dropdown
		
		$this->_helper->layout->disableLayout();
		
		$this->_helper->viewRenderer->setNoRender(true);
		
		if (isset($_GET['building_id'])) {
			
			$building_obj	= new Thelist_Model_building($_GET['building_id']);
			$units			= $building_obj->get_units();
			
			if ($units != null) {
				$return = '';
				foreach ($units as $unit) {
					$return .= "<OPTION value='".$unit->get_unit_id()."'>".$unit->get_unit_name()."</OPTION>";
				}
				echo $return;
			}
		}
	}
	
	public function getinfrastructureunitsfrombuildingdropdownajaxAction()
	{
	
	//get all units in a building for a dropdown
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		if (isset($_GET['building_id'])) {
			
			$building_obj	= new Thelist_Model_building($_GET['building_id']);
			$units			= $building_obj->get_units();
			
			$return = '';
			
			if ($units != null) {
				
				foreach ($units as $unit) {
					
					if ($unit->get_unit_groups() != null) {
						foreach ($unit->get_unit_groups() as $unit_group) {
							if ($unit_group->get_unit_group_name() == 'Infrastructure') {
								$return .= "<OPTION value='".$unit->get_unit_id()."'>".$unit->get_unit_name()."</OPTION>";
							}
						}
					}	
				}
				
				if ($return != '') {
					echo $return;
				} else {
					echo "<OPTION value=''>--- None Available ---</OPTION>";
				}
				
			}
		}
	}
	
	public function getactiveendusersinunitsearchajaxAction()
	{
		$this->_helper->layout->disableLayout();
								
		if (isset($_GET['unit_id'])) {
			
			
			$unit_obj 			= new Thelist_Model_unit($_GET['unit_id']);
			$building_obj		= new Thelist_Model_building($unit_obj->get_building_id());
			$end_user_services	= $unit_obj->get_end_user_services();

			$building_detail['building_name']	= $building_obj->get_building_name();
			
			$unit_detail['unit_name']			= $unit_obj->get_unit_name();
			$unit_detail['unit_id']				= $unit_obj->get_unit_id();
		
			if ($end_user_services != null) 
			{
				
				foreach ($end_user_services as $end_user_service) {
					
					$end_users[$end_user_service->get_end_user_service_id()]['end_user_service_id']			= $end_user_service->get_end_user_service_id();
					//$end_users[$end_user_service->get_end_user_service_id()]['primary_contact_firstname']	= 'voranon';
					$end_users[$end_user_service->get_end_user_service_id()]['primary_contact_firstname']	= $end_user_service->get_primary_contact()->get_first_name();
					$end_users[$end_user_service->get_end_user_service_id()]['primary_contact_lastname']	= $end_user_service->get_primary_contact()->get_last_name();
				}
				
			}			
			
		}
		
		if (isset($end_users)) {
			$this->view->end_users		= $end_users;
		}

		$this->view->unit			= $unit_detail;
		$this->view->building		= $building_detail;
		
	}
	
	
		
	//////////////////////   temp from matthew     ////////////////////////////
	
// 	public function validateinstallAction(){
// 		$index_value		= 0;
// 	}
	
// 	public function enableAction()
// 	{
	
// 	}
// 	public function getswitchesAction(){
// 		$this->_helper->layout->disableLayout();
// 		$this->_helper->viewRenderer->setNoRender(true);
// 		$property		= $this->_request->getParam('property');		
// 		$sql			= "SELECT edge_switch FROM temp_unit_distribution_switch
// 						   WHERE property = '$property'";
// 		$records		= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
// 		$xml_to_return	= '<switches>';
		
// 		foreach ($records as $record){
			
// 			$switch			= "<edge_switch><hostname>" . $record['edge_switch'] . "</hostname><serial_number>" . $record['edge_switch'] . "</serial_number></edge_switch>";
// 			$xml_to_return	.= $switch;
			
// 		}
		
// 		$xml_to_return	.= '</switches>';
// 		header( "content-type: application/xml; charset=ISO-8859-15" );
// 		echo $xml_to_return;		
		
// 	}
// 	public function disableAction()
// 	{
// 		$this->_helper->layout->disableLayout();
// 		$this->_helper->viewRenderer->setNoRender(true);	
			
// 		$unit_num		= $this->_request->getParam('unit_number');
// 		$property		= $this->_request->getParam('property');
// 		$sql			=  "UPDATE temp_ip_addresses
// 							SET		is_active		= '0'
// 							WHERE 	unit_number		=	'$unit_num'
// 							AND		property		=	'$property'";
// 		if(Zend_Registry::get('database')->get_thelist_adapter()->query($sql)){
// 			echo "Unit $unit_num at $property has been successfully disabled.";
			
// 			$sql			= "SELECT ip_address, switch, switchport from temp_ip_addresses
// 										WHERE 	unit_number		=	'$unit_num'
// 										AND		property		=	'$property'";
			
// 			$records					= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
// 			$switch						= $records[0]['switch'];
// 			$switchport					= $records[0]['switchport'];
// 			$telnet_password			= 'WiGwOoU';
// 			$telnet_enable_password		=	'nitram';
// 			echo "Administratively shutting down $switchport on $switch";
// 			$switch_configuration_cmd	= array("config t", 
// 												"interface $switchport",
// 												"shut"
// 												);
			
// 			$url 						= $_SERVER['HTTP_HOST'] . "/Device/execute";
// 			$client 					= new Zend_Http_Client("http://" . $url);
// 			$client				-> setParameterPost(array(
// 												'command'			=>	$switch_configuration_cmd,
// 												'password'			=>	$telnet_password,
// 												'enable_password'	=>	$telnet_enable_password,
// 												'hostname'			=>	$switch
// 			))
// 			-> request('POST');		
// 			$response 			= $client->request();	
// 			echo $response->getBody();		
			//echo $response->getBody();
			
			
// 		}else{
// 			echo "Unable to process this action";
// 		}
		
	
		
		
		
		
		
// 	}
// 	public function indexAction()
// 	{
		
// 		$sql		=	"SELECT unit_number, property, ip_address, macaddress, switch, switchport, router, patch_panel FROM temp_ip_addresses
//         				WHERE unit_number IS NOT NULL AND unit_number != ''
//         				ORDER BY property, unit_number";
// 		$records	=	Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);

// 		$units		=	'';
// 		foreach ($records as $record){
////			$macaddress	=	$this->format_macaddress($record['macaddress'], ":");
// 			$hostname	=	$record['ip_address'];
// 			$macaddress	=	Thelist_Utility_iputility::formatmac($record['macaddress'],2,':');
// 			$unit_row	= "<tr><td class='unit_number'>" . $record['unit_number'] . "</td><td>" . $record['property'] . "</td><td class='ipaddress'><img class='info' src='/images/info.png'/><span>" . $record['ip_address'] . "</span></td><td>" . $macaddress . "</td><td>" . $record['switch'] . "</td><td>" . $record['patch_panel'] . "</td><td>" .$record['switchport']. "</td><td>" . $record['router'] . "</td><td><a href='/unit/getwireless?hostname=$hostname&username=admin&password=K11ne0ver%' class=wifi>Wireless</a></td></tr>\n";
// 			$units	   .= $unit_row;

// 		}


// 		$this->view->unit_table = $units;
		
// 	}
	
	
	    
// 	public function getwirelessAction()
// 	{
// 		$this->_helper->layout->disableLayout();
// 		$this->_helper->viewRenderer->setNoRender(true);
// 		$hostname 			= $this->_request->getParam('hostname');
// 		$username 			= $this->_request->getParam('username');
// 		$password 			= rawurlencode($this->_request->getParam('password'));
// 		$url 				= $_SERVER['HTTP_HOST'] . "/device/getwirelessinfo?hostname=$hostname&username=$username&password=$password";
// 		$client 			= new Zend_Http_Client("http://" . $url);
// 		$response 			= $client->request();
// 		header( "content-type: application/xml; charset=ISO-8859-15" );
// 		echo $response->getBody();
// 	}
// 	public function isportconfiguredAction()
// 	{
// 		$this->_helper->layout->disableLayout();
// 		$this->_helper->viewRenderer->setNoRender(true);		
// 		$switch 			= $this->_request->getParam('switch');
// 		$switchport			= $this->_request->getParam('switchport');
// 		$sql				=  "SELECT unit_number, property FROM temp_ip_addresses
// 								WHERE switch = '$switch' and switchport = '$switchport'";
// 		try {
			
// 			$records			= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
// 			if(!count($records)){
// 				echo 0;
// 			}else{
// 				echo 1;
// 			}
			
// 		} catch (Exception $e) {
// 			echo $e;
// 			}



		
// 	}
// 	public function blahAction()
// 	{
// 		$this->_helper->layout->disableLayout();
// 		$this->_helper->viewRenderer->setNoRender(true);
		
// 		$is_directtv_install 			= $this->_request->getParam('directtv');
// 		if ($is_directtv_install){
// 			echo "THere is a directtv install";
// 		}else{
// 			echo "There is no directtv install";
// 		}
// 	}
	
// 	public function generateAction()
// 	{
// 		$this->_helper->layout->disableLayout();
// 		$this->_helper->viewRenderer->setNoRender(true);
	
// 		$is_directtv_install 			= $this->configure_dhcp('1837w','root','Pump4k11ne&');
		
// 	}
	
	
// 	public function configureAction()
// 	{

		
// 		$form = new Application_Model_FormConfigureNewUnit;
// 		$this->view->form = $form;
// 		if ($this->getRequest()->isPost()){
// 			if ($form->isValid($this->_request->getPost()))
// 			{
// 				$unit_number 			= $this->_request->getParam('unit_number');
// 				$property 				= $this->_request->getParam('property');
// 				$switchport				= $this->_request->getParam('switchport');
// 				$switch 				= $this->_request->getParam('switches');				
// 				$url 					= $_SERVER['HTTP_HOST'] . "/Unit/isconfigured?unit_number=$unit_number&property=$property";
	////			Perform HTTP request
// 				$client 				= new Zend_Http_Client("http://" . $url);
// 				$response 				= $client->request();
// 				$if_port_conf_url		= $_SERVER['HTTP_HOST'] . "/Unit/isportconfigured?switch=$switch&switchport=$switchport";
// 				$if_port_conf_client	= new Zend_Http_Client("http://" . $if_port_conf_url);
// 				$if_port_conf_response	= $if_port_conf_client->request();

// 				$is_port_configured		= $if_port_conf_response->getBody();
// 				if ($is_port_configured == 1){
// 					echo "This port is already assigned</br>";
// 					return;
// 				}
				
// 				$is_unit_configured = $response->getBody();				
// 				if ($is_unit_configured == 1){
// 					echo "This unit is already configured";
// 					return;

// 				}else
// 				{

// 					$record				= $this->next_available_ip($property);
// 					$ipaddress			= $record['ip_address'];
// 					$pri_key			= $record['pri_key'];
// 					$router				= $record['router'];
					
// 					$router_uname		= 'root';
// 					$router_pass		= 'Pump4k11ne&';
// 					$telnet_password	= 'WiGwOoU';
// 					$telnet_enable_password	=	'nitram';
// 					$macaddress 	= $this->format_macaddress(trim($this->_request->getParam('macaddress')));
// 					$downloadspeed	= $this->_request->getParam('downloadspeed');
// 					$uploadspeed	= $this->_request->getParam('uploadspeed');

// 					$patch_panel	= $this->_request->getParam('patch_panel');

// 					$sql			=  "UPDATE temp_ip_addresses
// 										SET		unit_number	=	'$unit_number',
// 												property	=	'$property',
// 												switch		=	'$switch',
// 												switchport	=	'$switchport',
// 												router		=	'$router',
// 												download	=	'$downloadspeed',
// 												macaddress	=	'$macaddress',
// 												upload		=	'$uploadspeed',
// 												patch_panel	=	'$patch_panel'
// 										WHERE	pri_key		=	'$pri_key'
// 										";
						
						

// 					try {
// 						$sql_return			= Zend_Registry::get('database')->get_thelist_adapter()->query($sql);

// 					} catch (Exception $e) {
// 						echo $e;
// 					}
// 					$priv_ipaddress			= $record['private_subnet_address'] . "/28";
// 					$this->add_queue($property,$router, $router_uname,$router_pass,$ipaddress,$uploadspeed,$downloadspeed,$pri_key,$priv_ipaddress);
// 					$this->configure_dhcp($property,$router_uname,$router_pass);
// 					$comment				=	"Unit Number $unit_number";
// 					$this->configure_switch_interface($switch,$switchport,$telnet_password,$telnet_enable_password,$pri_key,$comment);
// 					$is_directtv_install	= $this->_request->getParam('directtv');
//  					if ($is_directtv_install){

//  						$directtv_switch		= $this->_request->getParam('directtv_switch');
//  						$directtv_switchport	= $this->_request->getParam('directtv_switchport');
//  						try {
//  							$sql					=  "UPDATE temp_ip_addresses
//  							 											SET		directtv_switch				=	'$directtv_switch',
//  							 													directtv_switchport			=	'$directtv_switchport'
//  							 											WHERE	pri_key						=	'$pri_key'
//  							 																";		
 							
//  							$sql_return				= Zend_Registry::get('database')->get_thelist_adapter()->query($sql);
//  							$comment				=	"Unit Number $unit_number DirectTV";
//  							$this->configure_switch_interface($directtv_switch,$directtv_switchport,$telnet_password,$telnet_enable_password,$pri_key,$comment); 							
 							 							
//  						} catch (Exception $e) {
//  							echo "Unable to update temp_ip_addresses with directtv information";
//  						}

						
						
//  					}
// 					echo "Unit $unit_number has been successfully configured with the IP $ipaddress\n\n";						
						
						
// 				}
// 			}
				

// 		}
// 	}
////	predispatch will always rn
////	postdispatch
	
// 	public function isconfiguredAction()
// 	{
// 		$this->_helper->layout->disableLayout();
// 		$this->_helper->viewRenderer->setNoRender(true);

// 		$unit_number 		= $this->_request->getParam('unit_number');
// 		$property			= $this->_request->getParam('property');

// 		$sql_statement	 	= "
// 								SELECT * from temp_ip_addresses 
// 								WHERE unit_number = $unit_number 
// 								AND   property    = '$property'
// 										";
// 		$records			= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql_statement);

// 		if(!count($records)){
// 			echo 0;
// 		}else{
// 			echo 1;
// 		}

// 	}
// 	private function next_available_ip($property)
// 	{

// 		$sql_statement 		= "	SELECT pri_key, ip_address,router,private_subnet_address FROM temp_ip_addresses
// 								WHERE unit_number IS NULL and property = '$property' 
// 								LIMIT 1";

// 		$records			= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql_statement);
// 		return $records[0];

// 	}
// 	private function format_macaddress($unformatted_mac, $format = false)
// 	{
		
// 		$url 				= $_SERVER['HTTP_HOST'] . "/Device/formatmacaddress?macaddress=$unformatted_mac";
// 		if ($format){
// 			$url			   .= "&format=$format";
// 		}
// 		$client 			= new Zend_Http_Client("http://" . $url);
// 		$response 			= $client->request();
// 		$mac				= trim($response->getBody());
// 		return $mac;
// 	}
// 	private function create_dhcp_config($property,$router_username,$router_password)
// 	{
// 		$sql					=	"SELECT DISTINCT router FROM temp_ip_addresses
// 									WHERE property = '$property'";


// 		$records				= 	Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
// 		$router_name			=	$records[0]['router'];
// 		$dhcp_config			=
// 									'option domain-name "'.$router_name.'";'. "\n".
// 									"option domain-name-servers 68.170.70.150,98.159.94.150;\n" .
// 									"ddns-update-style none;\n".
// 									"default-lease-time 302400;\n" .
// 									"max-lease-time 604800;\n" .
// 									"authoritative;\n".
// 									"log-facility local1;\n\n";

			
// 		$sql					=	"SELECT DISTINCT public_subnet_address,public_subnet_mask,public_subnet_gateway
// 									FROM temp_ip_addresses
// 									WHERE property = '$property'";

// 		$records				=	Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);



// 		$public_subnet_address	=	$records[0]['public_subnet_address'];
// 		$public_subnet_mask		=	$records[0]['public_subnet_mask'];
// 		$public_subnet_gateway	=	$records[0]['public_subnet_gateway'];
// 		$public_shared_networks	=
// 									"shared-network networks_ether1 {\n" .
// 									"subnet $public_subnet_address netmask $public_subnet_mask {\n".
// 									"option routers $public_subnet_gateway;" .
// 									"}\n".
// 									"}\n\n";
// 		$dhcp_config			.=	$public_shared_networks;

// 		$sql					=
// 									"SELECT ip_address ".
// 									",macaddress ".
// 									",unit_number".
// 									",private_subnet_address ".
// 									",private_subnet_mask ".
// 									",private_subnet_begin ".
// 									",private_subnet_end ".
// 									",public_subnet_gateway ".
// 									",private_subnet_gateway ".
// 									"FROM temp_ip_addresses ".
// 									"WHERE router = '$router_name'".
// 									"AND unit_number IS NOT NULL";
// 		$records				=	Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);


// 		foreach ($records as $record){
				
// 			$ipaddress					=	$record['ip_address'];
// 			$macaddress					=	$this->format_macaddress($record['macaddress'],":");
// 			$unit_number				=	$record['unit_number'];
// 			$private_subnet_address 	=	$record['private_subnet_address'];
// 			$private_subnet_mask 		=	$record['private_subnet_mask'];
// 			$private_subnet_begin 		=	$record['private_subnet_begin'];
// 			$private_subnet_end 		=	$record['private_subnet_end'];
// 			$public_subnet_gateway 		=	$record['public_subnet_gateway'];
// 			$private_subnet_gateway 	=	$record['private_subnet_gateway'];

// 			$dhcp_unit_config			=	"#Unit $unit_number\n".
// 												"host $ipaddress {\n".
// 												"hardware ethernet $macaddress;\n".
// 												"fixed-address $ipaddress;\n".
// 												"}\n\n";
// 			$dhcp_config				.=	$dhcp_unit_config;

// 			$dhcp_private_subnet_config	=	"shared-network networks_$unit_number {\n".
// 												"subnet $private_subnet_address netmask $private_subnet_mask {\n".
// 												"range $private_subnet_begin $private_subnet_end;\n".
// 												"option routers $private_subnet_gateway;\n".
// 												"\t}\n".
// 												"}\n\n";

// 			$dhcp_config				.=	$dhcp_private_subnet_config;
// 		}


// 		return $dhcp_config;
// 	}
	
// 	private function configure_dhcp($property,$router_username,$router_password)
// 	{

// 		$dhcp_config						= $this->create_dhcp_config($property,$router_username,$router_password);

// 		$sql					=	"SELECT DISTINCT router FROM temp_ip_addresses
// 									WHERE property = '$property'";


// 		$records				= 	Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
// 		$router_name			=	$records[0]['router'];
// 		$dhcp_config			=
// 									'option domain-name "'.$router_name.'";'. "\n".
// 									"option domain-name-servers 68.170.70.150,98.159.94.150;\n" .
// 									"ddns-update-style none;\n".
// 									"default-lease-time 302400;\n" .
// 									"max-lease-time 604800;\n" .
// 									"authoritative;\n".
// 									"log-facility local1;\n\n";

			
// 		$sql					=	"SELECT DISTINCT public_subnet_address,public_subnet_mask,public_subnet_gateway
// 									FROM temp_ip_addresses
// 									WHERE property = '$property'";

// 		$records				=	Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);



// 		$public_subnet_address	=	$records[0]['public_subnet_address'];
// 		$public_subnet_mask		=	$records[0]['public_subnet_mask'];
// 		$public_subnet_gateway	=	$records[0]['public_subnet_gateway'];
// 		$public_shared_networks	=
// 									"shared-network networks_ether1 {\n" .
// 									"subnet $public_subnet_address netmask $public_subnet_mask {\n".
// 									"option routers $public_subnet_gateway;" .
// 									"}\n".
// 									"}\n\n";
// 		$dhcp_config			.=	$public_shared_networks;

// 		$sql					=
// 									"SELECT ip_address ".
// 									",macaddress ".
// 									",unit_number".
// 									",private_subnet_address ".
// 									",private_subnet_mask ".
// 									",private_subnet_begin ".
// 									",private_subnet_end ".
// 									",public_subnet_gateway ".
// 									",private_subnet_gateway ".
// 									"FROM temp_ip_addresses ".
// 									"WHERE router = '$router_name'".
// 									"AND unit_number IS NOT NULL";
// 		$records				=	Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);


// 		foreach ($records as $record){
				
// 			$ipaddress					=	$record['ip_address'];
// 			$macaddress					=	$this->format_macaddress($record['macaddress'],":");
// 			$unit_number				=	$record['unit_number'];
// 			$private_subnet_address 	=	$record['private_subnet_address'];
// 			$private_subnet_mask 		=	$record['private_subnet_mask'];
// 			$private_subnet_begin 		=	$record['private_subnet_begin'];
// 			$private_subnet_end 		=	$record['private_subnet_end'];
// 			$public_subnet_gateway 		=	$record['public_subnet_gateway'];
// 			$private_subnet_gateway 	=	$record['private_subnet_gateway'];

// 			$dhcp_unit_config			=	"#Unit $unit_number\n".
// 												"host $ipaddress {\n".
// 												"hardware ethernet $macaddress;\n".
// 												"fixed-address $ipaddress;\n".
// 												"}\n\n";
// 			$dhcp_config				.=	$dhcp_unit_config;

// 			$dhcp_private_subnet_config	=	"shared-network networks_$unit_number {\n".
// 												"subnet $private_subnet_address netmask $private_subnet_mask {\n".
// 												"range $private_subnet_begin $private_subnet_end;\n".
// 												"option routers $private_subnet_gateway;\n".
// 												"\t}\n".
// 												"}\n\n";

// 			$dhcp_config				.=	$dhcp_private_subnet_config;
// 		}


// 		$router 							= new bai_device($router_name, $router_username, $router_password);

// 		$result								= $router-> execute_cmd("echo '" . $dhcp_config . "' > /etc/dhcpd.conf");
// 		$restart_dhcp_cmd					= new BaiRouterOsRestartDhcpCommand($router);
// 		$restart_dhcp_cmd->execute();

			

			
// 	}
// 	private function add_queue($property,$router,$username,$password,$ipaddress,$upload,$download,$pri_key,$private_address)
// 	{
// 		$speed_configuration_cmds 	= array();
////		classid is a composite of the last 2 digits of the property address and unit_number
////		i.e "1820w" and unit "301" is classid:20301;

// 		$classid					= 1000 + $pri_key;
// 		$download					= $download * 1.10;
// 		$upload						= $upload 	* 1.10;
// 		$url 						= $_SERVER['HTTP_HOST'] . "/Device/bairouterexecutecmds";
// 		$client 					= new Zend_Http_Client("http://" . $url);



// 		array_push($speed_configuration_cmds, "sudo /sbin/tc class add dev eth0 parent 1:0 classid 1:$classid htb rate $upload" . "kbit prio 1");
// 		array_push($speed_configuration_cmds, "sudo /sbin/tc filter add dev eth0 parent 1:0 protocol ip u32 flowid 1:$classid match ip src $ipaddress");
// 		array_push($speed_configuration_cmds, "sudo /sbin/tc filter add dev eth0 parent 1:0 protocol ip u32 flowid 1:$classid match ip src $private_address");
// 		array_push($speed_configuration_cmds, "sudo /sbin/tc class add dev eth1 parent 1:0 classid 1:$classid htb rate $download" . "kbit prio 1");
// 		array_push($speed_configuration_cmds, "sudo /sbin/tc filter add dev eth1 parent 1:0 protocol ip u32 flowid 1:$classid match ip dst $ipaddress");
// 		array_push($speed_configuration_cmds, "sudo /sbin/tc filter add dev eth1 parent 1:0 protocol ip u32 flowid 1:$classid match ip dst $private_address");



// 		$client				-> setParameterPost(array(
// 									'command'	=>	$speed_configuration_cmds,
// 									'hostname'	=>	$router,
// 									'username'	=>	$username,
// 									'password'	=>	$password
// 		))
// 		-> request('POST');

// 		$response 			= $client->request();

////		Need to perform validation on adding the queue
////		currently not implemented
	////	tc class show dev eth0 | grep $classid



// 	}

// 	private function configure_switch_interface($switch, $switchport, $telnet_password,$telnet_enable_password, $pri_key, $comment)
// 	{
// 		$sql						=	"SELECT distribution_switch from temp_ip_addresses as tip
// 										INNER JOIN temp_unit_distribution_switch ds
// 										ON tip.switch	=	ds.edge_switch
// 										WHERE pri_key = '$pri_key'";

// 		$records					=	Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);


// 		foreach ($records as $record){
// 			$distribution_switch		=	$record['distribution_switch'];
// 			$distribution_conf_cmd		=	array();
// 			$native_vlan				=	$pri_key + 100;
				
// 			array_push($distribution_conf_cmd, "vlan database");
// 			array_push($distribution_conf_cmd, "vlan $native_vlan");
// 			array_push($distribution_conf_cmd, "exit");
// 			array_push($distribution_conf_cmd, "wr mem");
				
// 			$execute_url 				= $_SERVER['HTTP_HOST'] . "/Device/execute";
// 			$client 					= new Zend_Http_Client("http://" . $execute_url);
// 			$client						-> setParameterPost(array(
// 														'command'			=>	$distribution_conf_cmd,
// 														'password'			=>	$telnet_password,
// 														'enable_password'	=>	$telnet_enable_password,
// 														'hostname'			=>	$distribution_switch
// 			))
// 			-> request('POST');
// 			$response 			= $client->request();
// 		}



// 		$switch_configuration_cmd 	=	array();
// 		$interface					=	$switchport;
// 		array_push($switch_configuration_cmd, "config t");
// 		array_push($switch_configuration_cmd, "interface $interface");
// 		array_push($switch_configuration_cmd, "switchport trunk encapsulation dot1q");
// 		array_push($switch_configuration_cmd, "switchport mode trunk");
// 		array_push($switch_configuration_cmd, "switchport trunk native vlan $native_vlan");
// 		array_push($switch_configuration_cmd, "description $comment");
// 		array_push($switch_configuration_cmd, "end");
// 		array_push($switch_configuration_cmd, "vlan database");
// 		array_push($switch_configuration_cmd, "vlan $native_vlan");
// 		array_push($switch_configuration_cmd, "exit");
// 		array_push($switch_configuration_cmd, "wr mem");
// 		$url 						= $_SERVER['HTTP_HOST'] . "/Device/configureinterface";
// 		$client 					= new Zend_Http_Client("http://" . $url);
// 		$client				-> setParameterPost(array(
// 									'command'			=>	$switch_configuration_cmd,
// 									'password'			=>	$telnet_password,
// 									'enable_password'	=>	$telnet_enable_password,
// 									'hostname'			=>	$switch
// 		))
// 		-> request('POST');
// 		$response 			= $client->request();
// 		echo $response->getBody();

// 	}
// 	public function createswitchconfigAction()
// 	{
// 		$this->_helper->layout->disableLayout();
// 		$this->_helper->viewRenderer->setNoRender(true);
// 		$switch 		= $this->_request->getParam('switch');
// 		$this->create_switch_configuration($switch);
// 	}
// 	public function createdhcpconfigAction()
// 	{
// 		$this->_helper->layout->disableLayout();
// 		$this->_helper->viewRenderer->setNoRender(true);
// 		$property 			= $this->_request->getParam('property');
// 		$dhcp_config 		= $this->create_dhcp_config($property,'root','Pump4k11ne&');
// 		echo nl2br($dhcp_config);

// 	}

// 	public function createvlansconfigAction()
// 	{
// 		$this->_helper->layout->disableLayout();
// 		$this->_helper->viewRenderer->setNoRender(true);
// 		$property 				= $this->_request->getParam('property');
// 		$sql					=  "SELECT pri_key + 100 as vlan_id FROM temp_ip_addresses
// 									WHERE property = '$property'";

// 		$records				= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
// 		$vlan_config			=	'';
// 		foreach ($records as $record){
// 			$vlan				= "vlan " . $record['vlan_id'] . "<br/>";
// 			$vlan_config		.= $vlan;
// 		}
// 		echo $vlan_config;
// 	}
	
	
	
	
// 	private function create_switch_configuration($switch)
// 	{

// 		$sql					=	"SELECT switchport,unit_number,pri_key + 100 as native_vlan FROM temp_ip_addresses
// 									WHERE switch = '$switch'";

// 		$records				= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);



// 		$configuration = '';

// 		foreach ($records as $record){
// 			$interface_config	 = "interface " . $record['switchport'] . "</br>";
// 			$interface_config	.= "switchport trunk encapsulation dot1q" . "</br>";
// 			$interface_config	.= "switchport mode trunk" . "</br>";
// 			$interface_config	.= "switchport trunk native vlan " . $record['native_vlan'] . "</br>";
// 			$interface_config	.= "description Unit " . $record['unit_number'] . "</br></br>";
// 			$configuration		.= $interface_config;
// 		}
// 		echo $configuration;

// 	}
	


}
?>