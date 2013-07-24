<?php 

//by voranon

class ServiceplanController extends Zend_Controller_Action
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
	
	
	public function indexAction()
	{
		
		
		
		$sql="SELECT sp.service_plan_id,sp.service_plan_name,sp.service_plan_install_required_time, sp.service_plan_default_mrc,sp.service_plan_default_nrc,sp.service_plan_default_mrc_term
			  FROM service_plan_group_mapping spgm
			  LEFT OUTER JOIN service_plan_groups spg ON spgm.service_plan_group_id = spg.service_plan_group_id
			  LEFT OUTER JOIN service_plans sp ON spgm.service_plan_id = sp.service_plan_id
			  WHERE spg.service_plan_group_name='Internet'
			  AND sp.service_plan_id IS NOT NULL";
		
		
		$serviceplans   =  Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		$this->view->internet_serviceplans = $serviceplans; 
		
		
		$sql="SELECT sp.service_plan_id,sp.service_plan_name,sp.service_plan_install_required_time, sp.service_plan_default_mrc,sp.service_plan_default_nrc,sp.service_plan_default_mrc_term
			  FROM service_plan_group_mapping spgm
			  LEFT OUTER JOIN service_plan_groups spg ON spgm.service_plan_group_id = spg.service_plan_group_id
			  LEFT OUTER JOIN service_plans sp ON spgm.service_plan_id = sp.service_plan_id
			  WHERE spg.service_plan_group_name='Phone'
			  AND sp.service_plan_id IS NOT NULL";
		
		
		$serviceplans   =  Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		$this->view->phone_serviceplans = $serviceplans;
		
		$sql="SELECT sp.service_plan_id,sp.service_plan_name,sp.service_plan_install_required_time, sp.service_plan_default_mrc,sp.service_plan_default_nrc,sp.service_plan_default_mrc_term
			  FROM service_plan_group_mapping spgm
			  LEFT OUTER JOIN service_plan_groups spg ON spgm.service_plan_group_id = spg.service_plan_group_id
			  LEFT OUTER JOIN service_plans sp ON spgm.service_plan_id = sp.service_plan_id
			  WHERE spg.service_plan_group_name='TV'
			  AND sp.service_plan_id IS NOT NULL";
		
		
		$serviceplans   =  Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		$this->view->tv_serviceplans = $serviceplans;
			
		
		
	}
	
	
	public function serviceplanaddAction(){
		$this->_helper->layout->disableLayout();
		$addservice_plan = new Thelist_Serviceplanform_addserviceplan();
		
	
		$this->view->addserviceplan_form = $addservice_plan;
		
		
			
		
		if($this->getRequest()->isPost()){
			
			if ( $addservice_plan->isValid($_POST) ) {
					
				$data = array(
								'service_plan_type'  						=>  $_POST['service_plan_type'],
								'service_plan_name'  						=>  $_POST['serviceplan_name'],
								'service_plan_desc'  						=>  $_POST['serviceplan_desc'],
								'service_plan_permanent_install_only'    	=>  $_POST['service_plan_permamnent_install'],
								'service_plan_install_required_time'		=>  $_POST['install_time'],
								'service_plan_default_mrc'  		        =>  $_POST['mrc'],
								'service_plan_default_nrc'  		        =>  $_POST['nrc'],
								'service_plan_default_mrc_term'				=>  $_POST['mrc_term'],
								'activate'									=>  $this->time->convert_string_to_mysql_datetime($_POST['activate_date']),
								'deactivate'								=>  $this->time->convert_string_to_mysql_datetime($_POST['deactivate_date'])
							 );
				
			
				$method = $trace[0]["function"];
				
				$service_plan_id = Zend_Registry::get('database')->insert_single_row('service_plans', $data, get_class($this), $method);
				
				//////////////////////////////////////////////////
				$data = array(
								'service_plan_group_id'                     => $_POST['service_plan_group'],
								'service_plan_id'							=> $service_plan_id
							 );
				
				Zend_Registry::get('database')->insert_single_row('service_plan_group_mapping', $data, get_class($this), $method);
				//////////////////////////////////////////////////
				
						
				echo "<script>
									window.opener.location.href = window.opener.location.href;
									window.close();
					  </script>";
				
			}else{
				
			//'service_plan_type'
			//'service_plan_group'	
			//$addservice_plan->getElement('service_plan_group')->setValue($_POST['service_plan_group']);
			//$addservice_plan->getElement('mrc')->setValue('5d');
			//$this->view->test = $_POST['service_plan_group'];
			
				
				
					
			}
		}
	}
	
	
	public function serviceplaneditAction(){
		$this->_helper->layout->disableLayout();
		$service_plan_id = $_GET['service_plan_id'];
		
		$editservice_plan = new Thelist_Serviceplanform_editserviceplan(null,$service_plan_id);
		
		$this->view->serverplanedit_form = $editservice_plan;
		
		
		
		if($this->getRequest()->isPost()){
				 
			if ( $editservice_plan->isValid($_POST)) {
				$service_plan = new Thelist_Model_serviceplan($service_plan_id);
					
				$service_plan->set_service_plan_name($_POST['serviceplan_name']);
				//$service_plan->set_service_plan_type($_POST['service_plan_type']);
				$service_plan->set_service_plan_desc($_POST['serviceplan_desc']);
				$service_plan->set_service_plan_permanent_install_only($_POST['service_plan_permanent_install']);
				$service_plan->set_service_plan_install_required_time($_POST['install_time']);
				$service_plan->set_service_plan_default_mrc($_POST['mrc']);
				$service_plan->set_service_plan_default_nrc($_POST['nrc']);
				$service_plan->set_service_plan_default_mrc_term($_POST['mrc_term']);
				$service_plan->set_activate(	$this->time->convert_string_to_mysql_datetime($_POST['activate_date'] ));
				$service_plan->set_deactivate(	$this->time->convert_string_to_mysql_datetime($_POST['deactivate_date'] ));
				
				$this->view->test = $this->time->convert_string_to_mysql_datetime($_POST['deactivate_date'] );
				
				
				echo "<script>
													window.opener.location.href = window.opener.location.href;
													
					  </script>";
								
			}
		}
	}
	
	public function serviceplaneditfeaturesAction(){
		
		$this->_helper->layout->disableLayout();
		$service_plan_id = $_GET['service_plan_id'];
		$service_plan = new Thelist_Model_serviceplan($service_plan_id);
		
	
		$this->view->editable=$service_plan->is_editable();
		
		
		$this->view->interface_features		=   Zend_Registry::get('database')->get_interface_features()->fetchAll();
		$service_plan->get_features();
		$service_plan_array = $service_plan->toArray();
	
		$this->view->service_plan_features = $service_plan_array['_sp_if_feature_requirement'];
		
			
	}
	
	public function addinterfacefeatureajaxAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		
		$service_plan            = new Thelist_Model_serviceplan($_GET['service_plan_id']);
		$interface_feature_id 	 = $_GET['interface_feature_id'];
		$interface_feature_value = $_GET['interface_feature_value'];
		
		echo $service_plan->add_features($interface_feature_id,$interface_feature_value);
		
		
	}
	
	public function updateinterfacefeatureajaxAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		
		$service_plan            = new Thelist_Model_serviceplan($_GET['service_plan_id']);
		$interface_feature_id	 =	$_GET['interface_feature_id'];
		$interface_feature_value =   $_GET['interface_feature_value'];
		
		$service_plan->update_features($interface_feature_id,$interface_feature_value);
		
		
	}
	
	public function deleteinterfacefeatureajaxAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		
		$service_plan            = new Thelist_Model_serviceplan($_GET['service_plan_id']);
		$interface_feature_id	 =	$_GET['interface_feature_id'];

		$service_plan->delete_features($interface_feature_id);
		
		echo 's';
	}
	
	
	public function serviceplanoptionAction(){
		
		$this->_helper->layout->disableLayout();
		$service_plan_id = $_GET['service_plan_id'];
		$service_plan = new Thelist_Model_serviceplan($service_plan_id);
		
		$this->view->service_plan = $service_plan->toArray();
		
		$this->view->editable = $service_plan->is_editable();
		
		$sql="SELECT spom.service_plan_option_map_id,
					 spo.service_plan_option_name,
					 spo.short_description,
					 spog.service_plan_option_group_name,
       				 spom.service_plan_option_additional_install_time,
       				 spom.service_plan_option_default_mrc,
       				 spom.service_plan_option_default_nrc,
       				 spom.service_plan_option_default_mrc_term,
       				 spo.short_description
			  FROM service_plan_option_mapping spom
			  LEFT OUTER JOIN service_plans sp ON spom.service_plan_id = sp.service_plan_id
			  LEFT OUTER JOIN service_plan_options spo ON spom.service_plan_option_id = spo.service_plan_option_id
			  LEFT OUTER JOIN service_plan_option_groups spog ON spom.service_plan_option_group_id = spog.service_plan_option_group_id
			  WHERE spom.service_plan_id=".$service_plan_id;
		
		$this->view->service_plan_options 			=  Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		$this->view->options			  			=  Zend_Registry::get('database')->get_service_plan_options()->fetchAll();
		$this->view->option_groups				  	=  Zend_Registry::get('database')->get_service_plan_option_groups()->fetchAll();
		
	}
	
	public function serviceplanifmappingAction(){
		
		$this->_helper->layout->disableLayout();
		
		$service_plan_id = $_GET['service_plan_id'];
		
		$service_plan = new Thelist_Model_serviceplan($service_plan_id);
		
		$this->view->service_plan 		  = $service_plan->toArray();
		
		$this->view->editable			  = $service_plan->is_editable();
		
		//$this->view->interface_type_as	  = $service_plan->get_interface_type_as();
				
		//$this->view->service_plan_if_maps = $service_plan->get_service_plan_if_type_maps();
		
	}
	
	public function serviceplanhomerunmappingAction(){
		
		$this->_helper->layout->disableLayout();
		$service_plan_id = $_GET['service_plan_id'];
		$service_plan = new Thelist_Model_serviceplan($service_plan_id);
				
		$service_plan->get_service_plan_eq_type_groups();
		$this->view->service_plan 		  		 = $service_plan->toArray();
		
		$this->view->editable					 = $service_plan->is_editable();
		
		// create static method to get all items in this table
		$this->view->homeruntypegroups           = Thelist_Model_homeruntypegroups::get_all_homeruntypegroups();
		
	}
	
	public function geteqtypeajaxAction(){
		
		$this->_helper->viewRenderer->setNoRender(true);
		
		$eq_type_group_id	= $_POST['eq_type_group_id'];
		
		$equipmenttypegroup = new Thelist_Model_equipmenttypegroup($eq_type_group_id);
		
		$service_plan_eq_types = $equipmenttypegroup->get_eq_types();
		
		$output="<table>";
		
		foreach(is_array($service_plan_eq_types) || is_object($service_plan_eq_types) ? $service_plan_eq_types : array() as $service_plan_eq_type){
		
			$output.="<tr><td><input type='radio' name='eq_type' id='eq_type' value='".$service_plan_eq_type->get_id()."' />".$service_plan_eq_type->get_eq_type_name()."</td></tr>";
			
		}
		
		$output.="</table>";
		
		echo $output;
	}
	
	public function getstaticiftypeajaxAction(){
		
		$this->_helper->viewRenderer->setNoRender(true);
		$eq_type_id			= $_POST['eq_type_id'];
		
		$eq_type            = new Thelist_Model_equipmenttype($eq_type_id);
		
		$static_if_types    = $eq_type->get_static_if_types();
		$output="<table>";
		foreach(is_array($static_if_types) || is_object($static_if_types) ? $static_if_types : array() as $static_if_type){
		
			// $static_if_type is array now, it should be class , wait for martin to answer
			
			$output.="<tr><td><input type='radio' name='if_static' id='if_static' value='".$static_if_type->get_static_if_type_id()."' />".$static_if_type->get_if_default_name()."</td></tr>";
		}
		
		$output.="</table>";
		echo $output;
	}
	
	public function maphomerunstaticifajaxAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		
		$homerun_type_group_id		 = $_POST['homerun_type_group_id'];
		$service_plan_eq_type_map_id = $_POST['service_plan_eq_type_map_id'];
		$if_static_id                = $_POST['if_static_id'];
		$eq_type_id					 = $_POST['eq_type_id'];
		
		
		
		
		$eq_type 	= new Thelist_Model_equipmenttype($eq_type_id);
		$eq_type->map_homerun_static_interface( $homerun_type_group_id, $service_plan_eq_type_map_id, $if_static_id );
		
		
	}
	
	
	
	public function ifbajaxAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$service_plan_id = $_GET['service_plan_id'];
		$if_type_a		 = $_GET['if_type_a'];
		$service_plan = new Thelist_Model_serviceplan($service_plan_id);
		
		$interface_type_bs = $service_plan->get_interface_type_bs($if_type_a);
		
		$output = '';
		
			foreach(is_array($interface_type_bs) || is_object($interface_type_bs) ? $interface_type_bs : array() as $interface_type_b)
			{
				$output.="<option value='".$interface_type_b->get_if_type_id()."'>".$interface_type_b->get_if_type_name()."</option>";
			}
	
			if(strlen($output)==0){
				$output="<option value='0'>---Select One---</option>";
			}
		
		//echo count($interface_type_bs);
		
		echo $output;
	}
	
	public function addifmappingajaxAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$service_plan_id = $_GET['service_plan_id'];
		$service_plan = new Thelist_Model_serviceplan($service_plan_id);
		$service_plan->add_interface_type_map( $_GET['if_type_a'] , $_GET['if_type_b'] );
		
	}
	
	public function deleteifmappingajaxAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$service_plan_id = $_GET['service_plan_id'];
		$service_plan = new Thelist_Model_serviceplan($service_plan_id);
	    $service_plan->delete_interface_type_map( $_GET['if_type_a'] , $_GET['if_type_b'] );
	}
	
	
	public function addoptionajaxAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		
		
		
	    
	    $service_plan_id 			= $_GET['service_plan_id'];
	    $service_plan_option 		= $_GET['service_plan_option'];
	    $service_plan_option_group  = $_GET['service_plan_option_group'];
	    
	    $install_time				= $_GET['install_time'];
	    $mrc						= $_GET['mrc'];
	    $nrc						= $_GET['nrc'];
	    $mrc_term					= $_GET['mrc_term'];
	    
	    
	    $sql="SELECT COUNT(*)
			  FROM service_plan_option_mapping 
			  WHERE service_plan_id=".$service_plan_id." 
			  AND service_plan_option_group_id=".$service_plan_option_group."
			  AND service_plan_option_id=".$service_plan_option;
	    $exist = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
	    
	    if($service_plan_option == 0 || $service_plan_option_group ==0)
	    	echo 'Option Name and Option Group need to be selected';
	    else if($exist){
	    	echo 'Option Name and Option Group already exist';
	    }else{
	    	
	    	$data = array(
	    					'service_plan_id'								=>	$service_plan_id,
	    					'service_plan_option_id'						=>  $service_plan_option,
	    					'service_plan_option_group_id' 					=>  $service_plan_option_group,
	    					'service_plan_option_additional_install_time'	=>  $install_time,
	    					'service_plan_option_default_mrc'				=>  $mrc,
	    					'service_plan_option_default_nrc'				=>  $nrc,
	    					'service_plan_option_default_mrc_term'			=>  $mrc_term
	    					
	    				 );
	    	
	  	    	
	    Zend_Registry::get('database')->insert_single_row('service_plan_option_mapping',$data,get_class($this),$method);
	      	
	    }
	}
	
	public function saveoptionajaxAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		
		$service_plan_id 							    = $_GET['service_plan_id'];
		$service_plan_option_mapping_id					= $_GET['service_plan_option_mapping_id'];
			
		$install_time									= $_GET['install_time'];
		$mrc											= $_GET['mrc'];
		$nrc											= $_GET['nrc'];
		$mrc_term										= $_GET['mrc_term'];
		
		Zend_Registry::get('database')->set_single_attribute($service_plan_option_mapping_id,
						'service_plan_option_mapping', 'service_plan_option_additional_install_time',$install_time,get_class($this),$method);
						
		Zend_Registry::get('database')->set_single_attribute($service_plan_option_mapping_id,
						'service_plan_option_mapping', 'service_plan_option_default_mrc',$mrc,get_class($this),$method);
						
		Zend_Registry::get('database')->set_single_attribute($service_plan_option_mapping_id,
						'service_plan_option_mapping', 'service_plan_option_default_nrc',$nrc,get_class($this),$method);
						
		Zend_Registry::get('database')->set_single_attribute($service_plan_option_mapping_id,
						'service_plan_option_mapping', 'service_plan_option_default_mrc_term',$mrc_term,get_class($this),$method);
		
		//echo 'test';
	}
	
	public function serviceplanequipmentAction(){
		
		$this->_helper->layout->disableLayout();
		$service_plan_id = $_GET['service_plan_id'];
		$service_plan = new Thelist_Model_serviceplan($service_plan_id);
		
		$this->view->service_plan_name 		= $service_plan->get_service_plan_name();
		$this->view->service_plan_desc 		= $service_plan->get_service_plan_name();
		$this->view->service_plan_id   		= $service_plan->get_id();
		$this->view->service_plan_is_active = $service_plan->is_active();
		
		$this->view->editable=$service_plan->is_editable();
		
		////////
		
		$sql="SELECT spetm.service_plan_eq_type_map_id, 
       				 etg.eq_type_group_name,
       				 spetg.service_plan_eq_type_group_name,
       				 spetm.service_plan_eq_type_additional_install_time,
       				 spetm.service_plan_eq_type_default_nrc,
       				 spetm.service_plan_eq_type_default_mrc,
       				 spetm.service_plan_eq_type_default_mrc_term,
       				 edpp.eq_default_prov_plan_name
       
			FROM service_plan_eq_type_mapping spetm
			LEFT OUTER JOIN service_plan_eq_type_groups spetg ON spetm.service_plan_eq_type_group_id = spetg.service_plan_eq_type_group_id
			LEFT OUTER JOIN equipment_type_groups etg ON spetm.eq_type_group_id = etg.eq_type_group_id
			LEFT OUTER JOIN equipment_default_provisioning_plans edpp ON spetm.eq_default_prov_plan_id = edpp.eq_default_prov_plan_id
			WHERE service_plan_id =".$service_plan_id;
		
		$this->view->service_plan_equipments 			=  Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		$this->view->equipment_types			  		=  Zend_Registry::get('database')->get_equipment_type_groups()->fetchAll();
		$this->view->equipment_type_groups				=  Zend_Registry::get('database')->get_service_plan_eq_type_groups()->fetchAll();
		
		$this->view->equipmentprovisioningplans			=  Zend_Registry::get('database')->get_equipment_default_provisioning_plans()->fetchAll();
		
	}
	
	public function addequipmentajaxAction(){
		$this->_helper->viewRenderer->setNoRender(true);
			
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		
		
		$service_plan_id 				= $_GET['service_plan_id'];
		$equipment_type_group_id 		= $_GET['equipment_type_group'];
		$service_plan_equipment_group  	= $_GET['service_plan_equipment_group'];
		$install_time					= $_GET['install_time'];
		$mrc							= $_GET['mrc'];
		$nrc							= $_GET['nrc'];
		$mrc_term						= $_GET['mrc_term'];
		$provision_plan					= $_GET['provision_plan'];
		
		
		
		$sql="SELECT COUNT(*)
			  FROM service_plan_eq_type_mapping
			  WHERE service_plan_id = ".$service_plan_id." 
			  AND eq_type_group_id = ".$equipment_type_group_id."
			  AND service_plan_eq_type_group_id = ".$service_plan_equipment_group;
		
		
		
		$exist = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		
		
		if($equipment_type_group_id == 0 || $service_plan_equipment_group == 0){
			echo 'Service Plan Equipment Type and Equipment Type Group need to be selected';
		}else if($exist){
			echo 'Service Plan Equipment Type and Equipment Type Group already exist';
		}else{
		
			$data = array(
			    					'service_plan_id'								=>	$service_plan_id,
			    					'eq_type_group_id'								=>  $equipment_type_group_id,
			    					'service_plan_eq_type_group_id'					=>  $service_plan_equipment_group,
			    					'service_plan_eq_type_additional_install_time'	=>  $install_time,
			    					'service_plan_eq_type_default_mrc'				=>  $mrc,
			    					'service_plan_eq_type_default_nrc'				=>  $nrc,
			    					'service_plan_eq_type_default_mrc_term'			=>  $mrc_term,
			    					'eq_default_prov_plan_id'						=>  $provision_plan
					 	);
			$service_plan_eq_type_map_id = Zend_Registry::get('database')->insert_single_row('service_plan_eq_type_mapping',$data,get_class($this),$method);
			
			
		}
		
	}
	
	public function saveequipmentajaxAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		
		$service_plan_id 							    = $_GET['service_plan_id'];
		$service_plan_option_mapping_id					= $_GET['service_plan_eq_type_mapping_id'];
			
		$install_time									= $_GET['install_time'];
		$mrc											= $_GET['mrc'];
		$nrc											= $_GET['nrc'];
		$mrc_term										= $_GET['mrc_term'];
		
		
		
		Zend_Registry::get('database')->set_single_attribute($service_plan_option_mapping_id,
								'service_plan_eq_type_mapping', 'service_plan_eq_type_additional_install_time',$install_time,get_class($this),$method);
		
		Zend_Registry::get('database')->set_single_attribute($service_plan_option_mapping_id,
								'service_plan_eq_type_mapping', 'service_plan_eq_type_default_mrc',$mrc,get_class($this),$method);
		
		Zend_Registry::get('database')->set_single_attribute($service_plan_option_mapping_id,
								'service_plan_eq_type_mapping', 'service_plan_eq_type_default_nrc',$nrc,get_class($this),$method);
		
		Zend_Registry::get('database')->set_single_attribute($service_plan_option_mapping_id,
								'service_plan_eq_type_mapping', 'service_plan_eq_type_default_mrc_term',$mrc_term,get_class($this),$method);
		
		
	}
	
	public function deleteserviceplaneqajaxAction(){
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		
		$this->_helper->viewRenderer->setNoRender(true);
		$service_plan_eq_type_mapping_id = $_GET['service_plan_eq_type_mapping_id'];
		
		Zend_Registry::get('database')->delete_single_row($service_plan_eq_type_mapping_id,'service_plan_eq_type_mapping',get_class($this),$method);
		
		//Zend_Registry::get('database')->get_service_plan_eq_type_mapping()->delete('service_plan_eq_type_map_id='.$service_plan_eq_type_mapping_id);
		
		
		
	}
	
	
	public function deleteserviceplanoptionajaxAction(){
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		$this->_helper->viewRenderer->setNoRender(true);
		$service_plan_option_mapping_id =  $_GET['service_plan_option_mapping_id'];
		
		Zend_Registry::get('database')->delete_single_row($service_plan_option_mapping_id,'service_plan_option_mapping',get_class($this),$method);
		
		//Zend_Registry::get('database')->get_service_plan_option_map_id()->delete('service_plan_option_map_id='.$service_plan_option_mapping_id);
	}
	
}
?>