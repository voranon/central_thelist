<?php 

//exception codes 15600-15699

class BuildingController extends Zend_controller_Action
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
	
	public function indexAction(){
		
		$query="
				SELECT b.building_id,b.building_name,b.building_alias,b.createdate,p.project_name
				FROM buildings b 
				LEFT OUTER JOIN projects p ON b.project_id=p.project_id
			   ";
		$buildings = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($query);
		

		
		foreach($buildings as $building)
		{
			$this->view->placeholder('building_table')
			->append("<tr>
						<td class='display'><a href='/building/edit/?building_id=".$building['building_id']."' >".$building['building_name']."</a></td>
						<td class='display'>".$building['building_alias']."</td>
						<td class='display'>".$building['project_name']."</td>
						<td class='display'>".$building['createdate']."</td>
					  </tr>");
		}
	}
	
	public function addsingleunitAction()
	{
		$this->view->error	= '';
		$this->_helper->layout->disableLayout();
	
		if ($this->getRequest()->isPost()) {
	
			if (isset($_POST['building_id'])) {
				$options = array('building_id' => $_POST['building_id']);
			} else {
				throw new exception("when adding a unit we must have a building_id", 15600);
			}
	
			$addunitform = new Thelist_Buildingform_addunitform($options);
			$addunitform->setAction('/building/addsingleunit');
			$addunitform->setMethod('post');
			$this->view->addunitform = $addunitform;
	
			if ($addunitform->isValid($_POST)) {
					
				//form validation is not working, remove this once the validations are working
				if ($_POST['building_id'] == 0 || $_POST['unitname'] == '' || $_POST['unit_type_group'] == '0') {
	
					$this->view->error	= 'You must select building, unit name and group';
						
				} else {
					
					$building_obj 	= new Thelist_Model_building($_POST['building_id']);
					$new_unit		= $building_obj->create_unit($_POST['unitname']);
					$new_unit->map_new_unit_group($_POST['unit_type_group']);
					
					echo "<script>
					window.close();
					window.opener.location.reload();
					</script>";
				}

			} else {
				throw new exception("adding a single unit, form is not valid", 15601);
			}
	
		} else {
	
			if (isset($_GET['building_id'])) {
	
				if ($_GET['building_id'] != 'undefined') {
					$options = array('building_id' => $_GET['building_id']);
				} else {
					$options = array();
				}
	
			} else {
				$options = array();
			}
				
			$addunitform = new Thelist_Buildingform_addunitform($options);
			$addunitform->setAction('/building/addsingleunit');
			$addunitform->setMethod('post');
			$this->view->addunitform = $addunitform;
	
		}
	}
	
	
	
	
	
	
	/*
	
	
	public function createAction(){
		$this->_helper->layout->disableLayout();
		
		if(isset($_GET['project_id'])){
			$project_id=$_GET['project_id'];
		}else{
			$project_id=null;
		}
				
		$addbuildingform = new Thelist_Buildingform_addbuilding(null,$project_id);
		$addbuildingform->setAction('/building/create');
		$addbuildingform->setMethod('post');
		$this->view->addbuildingform=$addbuildingform;
		
		if($this->getRequest()->isPost()){
					
			if ($addbuildingform->isValid($_POST)){
				$project = new Thelist_Model_projects($_POST['project']);
				$project->add_building($_POST['building_name'],$_POST['building_alias'],$_POST['building_type']);
				
				echo "<script>
					  window.opener.location.href =	window.opener.location.href;
					  window.close();
					  
					  </script>";
			}
		}
	}
	
	
	public function editbuildingpopupAction(){
		$project_id=$_GET['project_id'];
		$building_id   =$_GET['building_id'];
	
		$this->_helper->layout->disableLayout();
	
		$editbuildingform = new Thelist_Buildingform_editbuilding(null,$project_id,$building_id);
		$editbuildingform->setAction('/building/editbuildingpopup?project_id='.$project_id.'&building_id='.$building_id);
		$editbuildingform->setMethod('post');
		$this->view->editbuildingform=$editbuildingform;
	
		if($this->getRequest()->isPost()){
			if ($editbuildingform->isValid($_POST)){
	
				$project = new Thelist_Model_projects($project_id);
				$buildings = $project->get_buildings();
				if(isset($_POST['save_building']))
				{
					$buildings[$building_id]->set_alias($_POST['building_alias']);
					$buildings[$building_id]->set_name($_POST['building_name']);
				}else if(isset($_POST['delete_building']))
				{
					$project->delete_building($building_id);
				}
	
				echo "<script>
					  window.opener.location.href =	window.opener.location.href;
					  window.close();
					  </script>";
			}
		}
	
	}
	
	
	
	
	public function editAction(){
		
		$building_id = $_GET['building_id'];
		$building = new Thelist_Model_buildings($building_id);
		$this->view->building_id = $building_id;
		
		if($this->getRequest()->isPost()){
			
			$building->set_name($_POST['building_name']);
			$building->set_note1($_POST['address']);
			$building->set_note2($_POST['mustknow']);
			$building->set_note3($_POST['numofunit']);
		}
		
		
		
		$this->view->building_name			=$building->get_name();
		$this->view->building_project_name	=$building->get_project_name();
		$this->view->building_note1			=$building->get_note1();
		$this->view->building_note2			=$building->get_note2();
		$this->view->building_note3			=$building->get_note3();
		$contacts = $building->get_contacts();
		if(is_array($contacts)){
			foreach($contacts as $key => $contact){
			
			$this->view->contact_list.="<tr>
											<td class='display'>".$contact->get_titlename()."</td>
											<td class='display'>".$contact->get_firstname().' '.$contact->get_lastname()."</td>
											<td class='display'>".$contact->get_cellphone()."</td>
											<td class='display'>".$contact->get_email()."</td>
											<td class='display'>
												<input class='button' type='button' id='edit_contact' contact_id='".$contact->get_contact_id()."' contact_type='building' contact_type_id='".$building_id."'   value='Edit'></input>
											</td>
										</tr>";
			}
		}
		
		$tasks =$building->get_tasks();
		if(is_array($tasks)){
			foreach($tasks as $key => $task){
			$this->view->task_list.="<tr>
										<td class='display'>".$task->get_name()."</td>
										<td class='display'>
											<input class='button' type='button' id='edit_task' task_id='".$task->get_task_id()."' value='Edit'></input>
										</td>
									</tr>";
			}
		}
		
		$units = $building->get_units();
		if(is_array($units)){
			foreach($units as $unit){
			$this->view->unit_list.="
									 <tr>
									 	<td class='display'>".$unit->get_number()."</td>
									 	<td class='display'>
									 		<input class='button' type='button' id='edit_unit' unit_id='".$unit->get_unit_id()."' value='Edit'></input>
									 	</td>
									 </tr>
									";
			
			}
		}
	}
	
	public function addcontactAction(){
		
		$this->_helper->layout->disableLayout();
		
		$building_id = $_GET['building_id'];
		
		$addcontactform = new Thelist_Contactform_addcontact(null,'building');
	
	
		$addcontactform->setAction('/building/addcontact?building_id='.$building_id);
		$addcontactform->setMethod('post');
		$this->view->addcontactform=$addcontactform;
		
		if($this->getRequest()->isPost()){
			if ($addcontactform->isValid($_POST)) {
				if($_POST['contact']==0){
					$contact_id=Zend_Registry::get('database')->get_contacts()->insert(
							array(
							'firstname'		=> $_POST['firstname'],
							'lastname'  	=> $_POST['lastname'],
							'streetnumber'  => $_POST['streetnumber'],
							'streetname'  	=> $_POST['streetname'],
							'streettype'  	=> $_POST['streettype'],
							'city'			=> $_POST['city'],
							'state'			=> $_POST['state'],
							'zip'			=> $_POST['zip'],
							'cellphone' 	=> $_POST['cellphone'],
							'homephone' 	=> $_POST['homephone'],
						    'officephone'   => $_POST['office'],
							'fax'			=> $_POST['fax'],
							'email'			=> $_POST['email'],
							'creator'       => $this->_user_session->uid
								 )
							);
				}else{
					$contact_id=$_POST['contact'];
				}
				
				$building = Thelist_Model_buildings($building_id);
				$building->add_contact($contact_id,$_POST['title']);
				
				echo "<script>
						window.opener.location.href = window.opener.location.href;
						window.close();
					  </script>";
				
				
				
			}
		}
		
	}
	
	public function editcontactAction(){
		
		$this->_helper->layout->disableLayout();
		$building_id = $_GET['building_id'];
		$contact_id=$_GET['contact_id'];
		
		$mode = array('building',$building_id);
		
		$editcontactform = new Thelist_Contactform_editcontact(null,$mode,$contact_id);
		
		$editcontactform->setAction('/building/editcontact?building_id='.$building_id.'&contact_id='.$contact_id);
		$editcontactform->setMethod('post');
		
		$this->view->editcontactform =$editcontactform;
		
		$building	= Thelist_Model_buildings($building_id);
		$contacts	= $building->get_contacts();
	
		if($this->getRequest()->isPost()){
			if(isset($_POST['save_contact'])){
		
				$building->set_contact_title($contact_id,$_POST['title']);
				$contacts[$contact_id]->set_firstname($_POST['firstname']);
				$contacts[$contact_id]->set_lastname($_POST['lastname']);
				$contacts[$contact_id]->set_streetnumber($_POST['streetnumber']);
				$contacts[$contact_id]->set_streetname($_POST['streetname']);
				$contacts[$contact_id]->set_streettype($_POST['streettype']);
				$contacts[$contact_id]->set_city($_POST['city']);
				$contacts[$contact_id]->set_state($_POST['state']);
				$contacts[$contact_id]->set_zip($_POST['zip']);
				$contacts[$contact_id]->set_cellphone($_POST['cellphone']);
				$contacts[$contact_id]->set_homephone($_POST['homephone']);
				$contacts[$contact_id]->set_officephone($_POST['officephone']);
				$contacts[$contact_id]->set_fax($_POST['fax']);
				$contacts[$contact_id]->set_email($_POST['email']);
		
			}else if(isset($_POST['delete_contact'])){
				$building->delete_contact($contact_id);
			}
			
			echo "<script>
					window.opener.location.href = window.opener.location.href;
					window.close();
				  </script>";
			
		}
		
	}

	

	public function addsingleunitAction()
	{
		$this->view->error	= '';
		$this->_helper->layout->disableLayout();

		if ($this->getRequest()->isPost()) {

			if (isset($_POST['building_id'])) {
				$options = array('building_id' => $_POST['building_id']);
			} else {
				throw new exception("when adding a unit we must have a building_id", 15600);
			}
				
			$addunitform = new Thelist_Buildingform_addunitform($options);
			$addunitform->setAction('/building/addsingleunit');
			$addunitform->setMethod('post');
			$this->view->addunitform = $addunitform;

		if ($addunitform->isValid($_POST)) {
			
			//form validation is not working, remove this once the validations are working
			if ($_POST['building_id'] == 0 || $_POST['unitnumber'] == '' || $_POST['unit_type_group'] == '0') {
				
				$this->view->error	= 'You must select building, unit name and group';
					
			} else {
				
				$sql = 	"SELECT * FROM units u
						WHERE building_id='".$_POST['building_id']."'
						AND number='".$_POST['unitnumber']."'
						";
					
				$existing_units = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
					
				if (isset($existing_units['0'])) {
				
					$this->view->error	= 'This Unit already exists';
				
				} else {

					
					$trace 		= debug_backtrace();
					$method 	= $trace[0]["function"];
					$class		= get_class($this);
					
					$data = array(
										'building_id'				=>  $_POST['building_id'],
										'number'					=>  $_POST['unitnumber'],
					);
				
					$new_unit_id = Zend_Registry::get('database')->insert_single_row('units', $data, $class, $method);
					
					$data2 = array(
															'unit_id'					=>  $new_unit_id,
															'unit_group_id'				=>  $_POST['unit_type_group'],
					);
					Zend_Registry::get('database')->insert_single_row('unit_group_mapping', $data2, $class, $method);

					echo "<script>
							window.opener.location.href = window.opener.location.href;
							window.close();
						  </script>";
				}
			}
			


		} else {
			throw new exception("adding a single unit, form is not valid", 15601);
		}

		} else {

			if (isset($_GET['building_id'])) {
				
				if ($_GET['building_id'] != 'undefined') {
					$options = array('building_id' => $_GET['building_id']);
				} else {
					$options = array();
				}
				
			} else {
				$options = array();
			}
			
			$addunitform = new Thelist_Buildingform_addunitform($options);
			$addunitform->setAction('/building/addsingleunit');
			$addunitform->setMethod('post');
			$this->view->addunitform = $addunitform;
		
		}
	}
	
	public function addunitAction(){
		$this->_helper->layout->disableLayout();
		$building_id = $_GET['building_id'];
	}
	
	public function addresourcesAction(){
		
		$this->_helper->layout->disableLayout();
		$building_id = $_GET['building_id'];
		
		// if submit and the start and stop are numeric value
		if(isset($_POST['list']) && is_numeric($_POST['start']) && is_numeric($_POST['stop'])   )
		{
			
			$query="SELECT unit_id,unit_name,number,streetnumber,streetname,city,zip 
					FROM units 
					WHERE building_id=".$building_id."
					AND number BETWEEN ".$_POST['start']." AND ".$_POST['stop'];	
			$units= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($query);
		
		}else{
			
			$units= Zend_Registry::get('database')->get_units()->fetchAll("building_id=".$building_id);
			
		}
		
		
		foreach($units as $unit)
		{
			$this->view->unit_list.="
									<tr>
										<td>
											<table border='1' id='unit' unit_id='".$unit['unit_id']."' class='display' style='width:800px'>
											<tr class='header'>
												<td align='center' class='display' style='width:80px'>Number</td>
												<td align='center' class='display'>Unit Name</td>
												<td align='center' class='display' style='width:110px'>Street Number</td>
												<td align='center' class='display'>Street Name</td>
												<td align='center' class='display'>City</td>
												<td align='center' class='display'>Zip</td>
												<td align='center' class='display' style='width:70px'>Selected</td>
											</tr>
											<tr>
												<td align='center' class='display'>".$unit['number']."&nbsp</td>
		  										<td align='center' class='display'>".$unit['unit_name']."&nbsp</td>
		  										<td align='center' class='display'>".$unit['streetnumber']."&nbsp</td>
		  										<td align='center' class='display'>".$unit['streetname']."&nbsp</td>
		  										<td align='center' class='display'>".$unit['city']."</td>
		  										<td align='center' class='display'>".$unit['zip']."</td>
		  										<td align='center' class='display'>
		  											<input type='checkbox' id='selected' name='selected' checked></input>
		  										</td>
		  							 		</tr>
		  							 		<tr>
		  							 			<td class='display'>Home Runs</td>
		  							 			<td colspan='6'>
		  							 ";
			
			
			
			
			 $query="SELECT ht.homerun_name,homerun_type_quantity 
					 FROM unit_homerun_mapping uhm
					 LEFT OUTER JOIN homerun_types ht ON uhm.homerun_type_id=ht.homerun_type_id
					 WHERE unit_id=".$unit['unit_id'];
			 //$this->view->unit_list.=$query;
			 $homerun_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($query);
			 foreach( $homerun_types as $homerun_type)
			 {
			 	$this->view->unit_list.=$homerun_type['homerun_name'].":".$homerun_type['homerun_type_quantity']."&nbsp   ";
			 }
		  	 $this->view->unit_list.="
		  							 			</td>
		  							 		</tr>
		  							 		<tr>
		  							 			<td class='display'>Service Plans</td>
		  							 			<td colspan='6'>";
		  	 
		  	 $query="SELECT sp.service_plan_name
		  	 		 FROM unit_service_plan_mapping uspm
		  	 		 LEFT OUTER JOIN service_plans sp ON uspm.service_plan_id = sp.service_plan_id
		  	 		 WHERE unit_id=".$unit['unit_id'];
		  	 $service_plans = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($query);
		  	 foreach($service_plans as $service_plan){
		  	 	$this->view->unit_list.=$service_plan['service_plan_name'].',';
		  	 }
		  	 
		  	 $this->view->unit_list.="          </td>
		  							 		</tr>
		  							 		<tr>
		  							 			<td class='display'>Service Points</td>
		  							 			<td colspan='6'>";
		  	 
		  	 $query="SELECT sp.service_point_name
		  	  		 FROM unit_service_point_mapping uspm
		  	  		 LEFT OUTER JOIN service_points sp ON uspm.service_point_id = sp.service_point_id
		  	  		 WHERE unit_id=".$unit['unit_id'];
		  	 $service_points = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($query);
		  	 foreach($service_points as $service_point){
		  	 	$this->view->unit_list.=$service_point['service_point_name'].',';
		  	 }
		  	 $this->view->unit_list.=" 			</td>
		  							 		</tr>
		  							 		<tr>
		  							 			<td class='display'>Unit Groups</td>
		  							 			<td colspan='6'>";
		  	 
		  	 $query="SELECT unit_group_name
					 FROM unit_group_mapping ugm
					 LEFT OUTER JOIN unit_groups ug ON ugm.unit_group_id=ug.unit_group_id
					 WHERE unit_id=".$unit['unit_id'];
		  	 
		  	 $unit_groups = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($query);
		  	 
		  	 foreach($unit_groups as $unit_group){
		  	 	$this->view->unit_list.=$unit_group['unit_group_name'].',';
		  	 }
		  	 
		  	
		  
		  	 
		  	 $this->view->unit_list.=" 			</td>
		  							 		</tr>
		  							 		
		  							 		</table>
		  							 	</td>
		  							 </tr>
									 ";
		}
		
		
 		$homerun_types = Zend_Registry::get('database')->get_homerun_types()->fetchAll();
 				
 		foreach($homerun_types as $homerun_type)
 		{
  			$this->view->homerun_type_list.=$homerun_type['homerun_name'].
  											"
  											<select name='homerun_types' id='homerun_types' homerun_type_id='".$homerun_type['homerun_type_id']."'>
  												<option value='0'>0</option>
  												<option value='1'>1</option>
  												<option value='2'>2</option>
  												<option value='3'>3</option>
  												<option value='4'>4</option>
  												<option value='5'>5</option>
  												<option value='6'>6</option>
  											</select>
  											&nbsp";
  		}
  		
  		$service_plans = Zend_Registry::get('database')->get_service_plans()->fetchAll();
  		foreach($service_plans as $service_plan){
  			//$service_plan['service_plan_name']
  			$this->view->service_plans.="<option value='".$service_plan['service_plan_id']."'>".$service_plan['service_plan_name']."
  										 </option>";
  										 
  		}
  						  
  		$service_points = Zend_Registry::get('database')->get_service_points()->fetchAll();
  		
  		foreach($service_points as $service_point){
   			$this->view->service_points.="<option value='".$service_point['service_point_id']."'>".$service_point['service_point_name']."
   										 </option>";
  		}
  		
  		$unit_groups = Zend_Registry::get('database')->get_unit_groups()->fetchAll();
  		foreach($unit_groups as $unit_group){
  			$this->view->unit_groups.="<option value='".$unit_group['unit_group_id']."'>".$unit_group['unit_group_name']."
  			   						   </option>";
  		}
  		
  	}
  	public function addresourcesajaxAction(){
  		$this->_helper->layout->disableLayout();
  		$this->_helper->viewRenderer->setNoRender(true);
  		$mode 	 = $_GET['mode'];
  		$unit_id = $_GET['unit_id']; 
  		//      new units($unit['unit_id']);
  		$unit = new Thelist_Model_units($unit_id);
  		if($mode=='address'){
  			//echo $mode;
  			$unit->set_streetnumber($_GET['streetnumber']);
  			$unit->set_streetname($_GET['streetname']);
  			$unit->set_streettype($_GET['streettype']);
  			$unit->set_city($_GET['city']);
  			$unit->set_state($_GET['state']);
  			$unit->set_zip($_GET['zip']);
  					
		}else if($mode=='homerun'){
			$unit->add_homerun( $_GET['homerun_type_id'], $_GET['quantity'] );
			
		}else if($mode=='serviceplan'){
			//$_GET['unit_id']
			//$_GET['service_plan_id']
			$unit->add_service_plan( $_GET['service_plan_id'] );
				
		}else if($mode=='servicepoint'){
			$unit->add_service_point( $_GET['service_point_id'] );
			
		
		}else if($mode=='unitgroup'){
			echo $unit->add_unit_group( $_GET['unit_grp_id'] );
			
		}
  		
  		
  		
  	
  		
  	}
  	
  	public function removeresourcesajaxAction(){
  		$this->_helper->layout->disableLayout();
  		$this->_helper->viewRenderer->setNoRender(true);
  		$mode 	 = $_GET['mode'];
  		$unit_id = $_GET['unit_id'];
  		//      new units($unit['unit_id']);
  		$unit = new Thelist_Model_units($unit_id);
  		if($mode=='address'){
  			
  				
  		}else if($mode=='homerun'){
  			$unit->remove_homerun( $_GET['homerun_type_id'], $_GET['quantity'] );
  				
  		}else if($mode=='serviceplan'){
  			$unit->remove_service_plan( $_GET['service_plan_id'] );
  		}else if($mode=='servicepoint'){
  			$unit->remove_service_point( $_GET['service_point_id'] );
  		}else if($mode=='unitgroup'){
  			$unit->remove_unit_group( $_GET['unit_grp_id'] );
  		}
   	}
	
	public function previewunitajaxAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		$from = $_GET['from'];
		$to   = $_GET['to'];
		$streetnumber = $_GET['streetnumber'];
		$streetname = $_GET['streetname'];
		$streettype = $_GET['streettype'];
		$building_id= $_GET['building_id'];
		$i=0;
		$output='';
		
		if( $from == ''){
			$from =0;
		}
		if( $to == ''){
			$to =0;
		}
		while($from <= $to && $from > 0){

			$query="SELECT COUNT(*) AS COUNT
					FROM units
					WHERE number=".$from."
					AND building_id=".$building_id;
			
			$exist = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($query);
			
 			if($exist){
				$output.=
						"<tr list_id='".$from."' style='background-color:#FF4444;'>
							<td align='center'>".$from."</td>
							<td align='center'>
								<input name='unitnumber' id='unitnumber' type='text' value='".$from."' style='width:100px' class='text'></input>
							</td>
							<td align='center'>
								<input name='streetnumber' id='streetnumber' type='text' value='".$streetnumber."' style='width:100px' class='text'></input>
							</td>
							<td align='center'>
								<input name='streetname' id='streetname' type='text' value='".$streetname."' style='width:100px' class='text'></input>
							</td>
							<td align='center'>
								<input name='streettype' id='streettype' type='text' value='".$streettype."' style='width:100px' class='text'></input>
							</td>
							<td align='center'>
								<input type='button' value='Remove' class='button' id='remove' list_id='".$from."'></input>
							</td>
						</tr>";						
 			}
			else{
				$output.=
						"<tr id='adding_list' list_id='".$from."' >
							<td align='center'>".$from."</td>
							<td align='center'>
								<input name='unitnumber' id='unitnumber' type='text' value='".$from."' style='width:100px' class='text'></input>
							</td>
							<td align='center'>
								<input name='streetnumber' id='streetnumber' type='text' value='".$streetnumber."' style='width:100px' class='text'></input>
							</td>
							<td align='center'>
								<input name='streetname' id='streetname' type='text' value='".$streetname."' style='width:100px' class='text'></input>
							</td>
							<td align='center'>
								<input name='streettype' id='streettype' type='text' value='".$streettype."' style='width:100px' class='text'></input>
							</td>
							<td align='center'>
								<input type='button' value='Remove' class='button' id='remove' list_id='".$from."'></input>
							</td>
						</tr>";
			}
			$from++;
		
		}
				
		echo $output;
	}
	
	public function addunitajaxAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		//$streetnumber = $_GET['streetnumber'];
		//$streetname = $_GET['streetname'];
		//$streettype = $_GET['streettype'];
		$building_id= $_GET['building_id'];
		$data       = $_GET['data'];
		
		
		$insert='';
		$data =substr($data,0,-1);
		
		$lines = explode('~',$data);
		
		if(is_array($lines)){
			foreach($lines as $line){
				$item = explode('^',$line);
				$data=array(
							'number'  		=>  $item[0],
							'streetnumber'  =>  $item[1],
							'streetname'	=>  $item[2],
							'streettype'    =>  $item[3],
							'city'			=>  $item[4],
							'state'			=>  $item[5],
							'zip'			=>  $item[6],
							'building_id'   =>  $building_id
						   );
				
			 	Zend_Registry::get('database')->get_units()->insert($data);
			}
			
		}
		
		echo $data;
	}
	
	public function editunitAction(){
		$this->_helper->layout->disableLayout();
		
		$unit_id = $_GET['unit_id'];
		$unit = new Thelist_Model_units($unit_id);
		$this->view->unit_name		=$unit->get_name();
		$this->view->unit_number	=$unit->get_number();
		$this->view->street_name	=$unit->get_streetname();
		$this->view->street_number	=$unit->get_streetnumber();
		$this->view->street_type	=$unit->get_streettype();
		$this->view->city       	=$unit->get_city();
		$this->view->state			=$unit->get_state();
		$this->view->zip        	=$unit->get_zip();
		$this->view->note			=$unit->get_note();
		if($this->getRequest()->isPost()){
			$unit->set_name($_POST['unit_name']);
			$unit->set_number($_POST['unit_number']);
			$unit->set_streetname($_POST['street_name']);
			$unit->set_streetnumber($_POST['street_number']);
			$unit->set_streettype($_POST['street_type']);
			$unit->set_city($_POST['city']);
			$unit->set_state($_POST['state']);
			$unit->set_zip($_POST['zip']);
			$unit->set_note($_POST['note']);
			echo "<script>
					window.opener.location.href =	window.opener.location.href;
					window.close();
				  </script>";
		}
		
	}
	
	*/
	
}	
?>