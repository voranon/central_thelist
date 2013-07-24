<?php

class TestController extends Zend_controller_Action
{
	
	public function indexAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		
		$test 		= new Thelist_Utility_jsonconverter();
		
		$end_user	= new Thelist_Model_enduserservice(1);
		
		$sp_temp_quote_map = $end_user->get_service_plan_temp_quote_map(1);
		
		/*
		add_service_plan_temp_quote_option($service_plan_option_map_id, 
										   $service_plan_temp_quote_option_actual_mrc_term, 
										   $service_plan_temp_quote_option_actual_mrc=null, 
										   $service_plan_temp_quote_option_actual_nrc=null)
		*/
		
		
		$options = $sp_temp_quote_map->remove_service_plan_temp_quote_option(6);
		
		
		
		
		$eq_types= $sp_temp_quote_map->remove_service_plan_temp_quote_eq_type(3);
		
		
		
		
		/*
		 //adding
		$sp_temp_quote_map->add_service_plan_temp_quote_option(1,0.00,5.00,0.00);
		
		$sp_temp_quote_map->add_service_plan_temp_quote_eq_type(1,0.00,5.00,0.00);
		*/
		
		//$end_user->add_service_plan_temp_quote_map(2, 0.00, 5.00, 5.00 ,null);
		
		
		
		
		
	
		$error['exception_code'] = 5;
		$error['error_string']   ='andrea';
		
		
	}
	
	
	public function martintestAction() 
	{
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		$equipment_obj 				= new Thelist_Model_equipments(354);
		$api 						= current($equipment_obj->get_apis());
		$api->set_specific_connect_implentation('ssh2lib_shell');
		
		
		$device 					= new Thelist_Model_device($equipment_obj->get_eq_fqdn(), $api);
		
		
			
		//$device_reply = $device->execute_command("/interface wireless scan wlan1 duration=10");
		$device_reply = $device->execute_command("/interface wireless scan wlan1 duration=10");
		
		echo "\n <pre> testcontroller  \n ";
		print_r($device_reply->get_message());
		echo "\n 2222 \n ";
		print_r($device_reply);
		echo "\n 3333 \n ";
		//print_r();
		echo "\n 4444 </pre> \n ";
		die;
		
		//$enduser_obj = new Thelist_Model_enduserservice(1);
		//$enduser_obj->add_end_user_service_contact(8, 'darling', null, false);
		
		
	}
}