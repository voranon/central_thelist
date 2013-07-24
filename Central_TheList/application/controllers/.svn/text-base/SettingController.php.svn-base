<?php

class SettingController extends Zend_Controller_Action
{
	private $database;
	private $logs;
	private $user_session;
	private $acl;
	
	private $menuid;
	private $menuitemid;
	
	public function init()
	{
		/* Initialize action controller here */
		//keep on top so we send users that are not logged in to the login page
		$this->logs				= Zend_Registry::get('logs');
		
		$this->user_session 	= new Zend_Session_Namespace('userinfo');
		
		if($this->user_session->uid=='')
		{
			$this->logs->get_app_logger()->log("User don't log in", Zend_Log::ERR);
			header('Location: /');
			exit;
		}
				

		$this->user_session = new Zend_Session_Namespace('userinfo');
		
		$this->view->firstname	=$this->user_session->firstname;
		$this->view->lastname 	=$this->user_session->lastname;
		$this->view->title	  	=$this->user_session->title;
		$this->view->department	=$this->user_session->department;
			
		/* Main menu */
			
		$this->acl= new Thelist_Utility_acl($this->user_session->role_id);
		
		$auth = Zend_Auth::getInstance();
		$identity=$auth->getIdentity();
			
		$this->_helper->layout->setLayout('setting_layout');
	
		 
	}
	public function preDispatch(){
		$this->logs->get_user_logger()->insert(
		array(
			'uid'=>$this->user_session->uid,
			'page_name'=>$this->view->url(),
			'event'=>'changepage',
			'message_1'=>'',
			'message_2'=>''
			)
		);
	}
	public function postDispatch(){
		
	}
	public function indexAction(){
		
	}
	
	public function menuAction(){
		
		
		                      
		$select=Zend_Registry::get('database')->get_acl_roles()->select()->where("role_default=1");
		$rows=Zend_Registry::get('database')->get_acl_roles()->fetchAll($select);
		$this->view->perspective.="<option value='0'>------Select One------</option>";
		
		foreach($rows as $value){
				$this->view->perspective.="<option value='".$value['role_id']."'>".$value['role_name']."</option>";
		}
	}
	public function logsAction(){
		
		$users=Zend_Registry::get('database')->get_users()->fetchAll();
			
		foreach($users as $user){
			$this->view->user_list.="<option value='".$user['uid']."'>".$user['firstname']." ".$user['lastname']."</option>";
		}
	}
	public function accesscontrolAction(){
		$roles=Zend_Registry::get('database')->get_acl_roles()->fetchAll();
		
		foreach($roles as $role){
			$this->view->role_tab.="<li><a href='#".$role['role_id']."'>".$role['role_name']."</a></li>";
		}
		
		
		foreach($roles as $role){
			$this->view->role_area.="<div id='".$role['role_id']."'>";
			
		
		
		    $resources ='';	
			$resources = Zend_Registry::get('database')->get_acl_resources()->fetchAll();
			
			$this->view->role_area.="<table class='display'>";
			
			foreach($resources as $resource){
				$this->view->role_area.="<tr class='header'><td align='left' colspan='100%'>".$resource['resource_name']."</td></tr>";
				$query="SELECT acl_p.privilege_id,acl_p.privilege_name,acl.role_id
						FROM acl_privileges  acl_p
						LEFT OUTER JOIN acl_access_control_list acl ON acl_p.privilege_id = acl.privilege_id 
						AND acl.role_id=".$role['role_id']."
						WHERE acl_p.resource_id=".$resource['resource_id'];
				$privilegges = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($query);
				$this->view->role_area.="<tr>";
				foreach($privilegges as $privilege){
					if($privilege['role_id'] != $role['role_id']){
						$this->view->role_area.="<td align='left'>
													<input type='checkbox' id='privilege' 
													role_id='".$role['role_id']."' 
													resource_id='".$resource['resource_id']."' 
													privilege_id='".$privilege['privilege_id']."'></input>".$privilege['privilege_name'].
												"</td>";
					}
					else{
						$this->view->role_area.="<td align='left'>
													<input type='checkbox' id='privilege' 
													role_id='".$role['role_id']."' 
													resource_id='".$resource['resource_id']."'
													privilege_id='".$privilege['privilege_id']."' checked></input>".$privilege['privilege_name'].
												"</td>";
					}
				}
				$this->view->role_area.="</tr>";
			}
			
			$this->view->role_area.="</table>";
			
			
								
			$this->view->role_area.="</div>";
		}
		
		
	}
	
	//////////// search box ////////////////////
	public function searchboxAction()
	{

		$searchboxes = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll('SELECT searchbox_id,searchbox_name FROM searchboxes');
		$searchbox_list;
		foreach($searchboxes as $searchbox){
			$searchbox_list.="<tr>
								<td><input type='radio' name='searchbox_radio' id='searchbox_radio' searchbox_id='".$searchbox['searchbox_id']."'></input></td>
								<td>".$searchbox['searchbox_name']."</td>
								<td><img name='delete_searchbox' id='delete_searchbox' searchbox_id='".$searchbox['searchbox_id']."' src='/images/red_cross_no.jpg' width='15' height='15' align='right'></td>
							  </tr>";			
		}
		
		$this->view->searchbox_list = $searchbox_list;
		
		
		$tables = Zend_Registry::get('database')->get_thelist_information_schema_adapter()->fetchAll("SELECT table_name 
							 														   FROM TABLES
																			 		   WHERE table_schema='thelist'");
		foreach($tables as $table){
			$this->view->table_names .="<option>".$table['table_name']."</option>";  
		}
	}
	
	public function searchboxajaxAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$searchbox_id  =  $_GET['searchbox_id'];
		
		
	}
	
	public function buildqueryajaxAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$tables = $_GET['tables'];	
		$columns= $_GET['columns'];
		$querybuilder = new Thelist_Utility_querybuilder();
		
		echo $querybuilder->build_query($columns,$tables,null,null);
	}
	
	public function buildcolumnajaxAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$tables = $_GET['tables'];
		$tables = substr_replace($tables ,"",-1);
		$tables = str_replace("," ,"','" ,$tables);
		$tables = "'".$tables."'";
		
		
		$query="SELECT CONCAT(table_name,'.',column_name) AS column_name
				FROM COLUMNS
				WHERE table_schema ='thelist'
				AND table_name IN(".$tables.")";
		
		$table_lists = Zend_Registry::get('database')->get_thelist_information_schema_adapter()->fetchAll($query);
		
		$output='';
		
		foreach($table_lists as $table_list){
			$output.='<tr>'.
						'<td align="left" width="270px" id="column">'.$table_list["column_name"].'</td>'.
						'<td width="170px" id="alias"> <input type="text" id="alias" class="text" style="width:150px" maxlength="20"></input></td>'.
						'<td width="6px">  <input type="checkbox" name="display_column" id="display_column"></input></td>'.
					 '</tr>';
		}
		
		echo $output;	
	}
	
	public function buildsearchoncolumnajaxAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$tables = $_GET['tables'];
		$tables = substr_replace($tables ,"",-1);
		$tables = str_replace("," ,"','" ,$tables);
		$tables = "'".$tables."'";
		
		
		$query="SELECT CONCAT(table_name,'.',column_name) AS column_name
						FROM COLUMNS
						WHERE table_schema ='thelist'
						AND table_name IN(".$tables.")";
		
		$table_lists = Zend_Registry::get('database')->get_thelist_information_schema_adapter()->fetchAll($query);
		
		$output='';
		
		foreach($table_lists as $table_list){
			$output.='<tr>'.
								'<td align="left" width="270px" id="column">'.$table_list["column_name"].'</td>'.
								'<td width="6px">  <input type="checkbox" name="searchon_column" id="searchon_column"></input></td>'.
					 '</tr>';
		}
		
		echo $output;
	} 
	
	public function buildscopeajaxAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$tables = $_GET['tables'];
		$tables = substr_replace($tables ,"",-1);
		$tables = str_replace("," ,"','" ,$tables);
		$tables = "'".$tables."'";
		
		
		$query="SELECT CONCAT(table_name,'.',column_name) AS column_name
								FROM COLUMNS
								WHERE table_schema ='thelist'
								AND table_name IN(".$tables.")";
		
		$table_lists = Zend_Registry::get('database')->get_thelist_information_schema_adapter()->fetchAll($query);
		
		$output='';
		
		foreach($table_lists as $table_list){
			$output.='<tr>'.
								'<td align="left" width="270px" id="column">'.$table_list["column_name"].'</td>'.
								'<td width="60px" id="alias"> <input type="text" id="scope" class="text" style="width:60px" maxlength="12"></input></td>'.
					 '</tr>';
		}
		
		echo $output;
	}
	
	
	
	////////////// end search box /////////////////////
	
	
	public function updateaclajaxAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$role_id 	 = $_GET['role_id'];
		$resource_id = $_GET['resource_id'];
		$privilege_id= $_GET['privilege_id'];
		$check       = $_GET['check'];
		
		if($check == 'true'){
			$insert = array(
							'role_id'		=> $role_id,
							'resource_id'	=> $resource_id,
							'privilege_id'	=> $privilege_id,
							);
			Zend_Registry::get('database')->get_acl_access_control_list()->insert($insert);
		}else if($check == 'false'){
			$delete = array(
							'role_id = ?'     => $role_id,
							'resource_id = ?' => $resource_id,
							'privilege_id = ?'=> $privilege_id,
							);
			Zend_Registry::get('database')->get_acl_access_control_list()->delete($delete);
		}
	}
	public function getuserlogajaxAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$date    = $_GET['date'];
		$user_id = $_GET['user_id'];
		
		$temp= explode('/',$date);
		$month = $temp[0];
		$day   = $temp[1];
		$year  = $temp[2];
		$date  =$year.'-'.$month.'-'.$day;
		$query="SELECT u_logid,page_name,event,class_name,method_name,primary_key_name,primary_key_value,message_1,message_2,timestamp
				FROM user_event_logs
				WHERE uid=".$user_id."
				AND DATE(TIMESTAMP)='".$date."'";
		$logs = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($query);
		
		$output='';
		$output.="<table class='display'>
					<tr class='header'>
						<td class='display'>Page_name </td>
						<td class='display'>Event</td>
						<td class='display'>Class</td>
						<td class='display'>Method </td>
						<td class='display'>Primary key name</td>
						<td class='display'>Primary key value</td>
						<td class='display'>Message1</td>
						<td class='display'>Message2</td>
						<td class='display'>Time stamp</td>
					</tr>";
				
		foreach($logs as $log){
			$output.="<tr>
						<td class='display'>".$log['page_name']."</td>
						<td class='display'>".$log['event']."</td>
						<td class='display'>".$log['class_name']."</td>
						<td class='display'>".$log['method_name']."</td>
						<td class='display'>".$log['primary_key_name']."</td>
						<td class='display'>".$log['primary_key_value']."</td>
						<td class='display'>".$log['message_1']."</td>
						<td class='display'>".$log['message_2']."</td>
						<td class='display'>".$log['timestamp']."</td>
					  </tr>";
			
			
		}
		echo $output.="</table>";
		
	}
	
	public function menuajaxAction(){
		
		$this->_helper->layout->disableLayout(); 
		$this->_helper->viewRenderer->setNoRender(true);
	
		
		$select=Zend_Registry::get('database')->get_menus()->select()->where("role_id=".$_GET['perspective']);
		$rows=Zend_Registry::get('database')->get_menus()->fetchAll($select);
	
		foreach($rows as $value){
		
		 	echo "<input type='radio' name='menu' id='menu' value='".$value['menu_id']."'/>".$value['menu_name'];
		 	echo "<img name='redcross' id='menu_delete' menu_id='".$value['menu_id']."' src='/images/red_cross_no.jpg' width='15' height='15' align='right'>";
		 	echo "<br>";
		}
		
	}
	
	public function addmenuajaxAction(){
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		$data = array(
					'menu_name' => $_GET['menu_name'],
					'role_id'   => $_GET['perspective']
			 		 );
		
		Zend_Registry::get('database')->get_menus()->insert($data);
		
		
		$select=Zend_Registry::get('database')->get_menus()->select()->where("role_id=".$_GET['perspective']);
		$rows=Zend_Registry::get('database')->get_menus()->fetchAll($select);
		
		foreach($rows as $value){
		
			echo "<input type='radio' name='menu' id='menu' value='".$value['menu_id']."'/>".$value['menu_name'];
		 	echo "<img name='redcross' id='menu_delete' menu_id='".$value['menu_id']."' src='/images/red_cross_no.jpg' width='15' height='15' align='right'>";
		 	echo "<br>";	
				
		
		}
		
		
		
		
	}
	
	public function menuitemajaxAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
	
		$this->menuid=$_GET['menuid'];
		$sql="SELECT p.page_id,p.page_name,menu_id  
			  FROM htmlpages p 
			  LEFT OUTER JOIN (
								SELECT page_id,menu_id
								FROM menuitems
								WHERE menu_id=".$_GET['menuid']."
							   )m ON p.page_id=m.page_id ";
		
		$rows=Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
	
		
		
		echo "<table border='0'>";
		foreach($rows as $value){
			echo "<tr><td align='left'>";
			if($value['menu_id']==$_GET['menuid']){
				echo "<input type='checkbox' id='page' menu_id='".$_GET['menuid']."' page_id='".$value['page_id']."' checked></input>";
			}else{
				echo "<input type='checkbox' id='page' menu_id='".$_GET['menuid']."' page_id='".$value['page_id']."'></input>";
			}
			echo $value['page_name'];
			echo "</td></tr>";
		}
		echo "</table>";
		
	}
	
	public function menueditajaxAction(){/// menu item edit
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		//$_GET['menuid']
		//$_GET['pageid']
		//$_GET['check']
		
		if($_GET['check']=='true'){
			$data = array(
    				'menu_id' => $_GET['menuid'],
    				'page_id' => $_GET['pageid']
    				  	 );
			Zend_Registry::get('database')->get_menuitems()->insert($data);
			echo $_GET['menuid'];
		}else if($_GET['check']=='false'){
			Zend_Registry::get('database')->get_menuitems()->delete("menu_id=".$_GET['menuid']." and page_id=".$_GET['pageid']);
			echo 'false';
		}
	}
	
	public function deletemenuajaxAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		Zend_Registry::get('database')->get_menus()->delete("menu_id=".$_GET['menu_id']);
		Zend_Registry::get('database')->get_menuitems()->delete("menu_id=".$_GET['menu_id']);
		
		
		
		$select=Zend_Registry::get('database')->get_menus()->select()->where("role_id=".$_GET['perspective']);
		$rows=Zend_Registry::get('database')->get_menus()->fetchAll($select);
		
		
		foreach($rows as $value){
		
			echo "<input type='radio' name='menu' id='menu' value='".$value['menu_id']."'/>".$value['menu_name'];
			echo "<img name='redcross' id='menu_delete' menu_id='".$value['menu_id']."' src='/images/red_cross_no.jpg' width='15' height='15' align='right'>";
			echo "<br>";
		
		
		}

	}

}
?>