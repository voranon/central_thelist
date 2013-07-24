<?php
//require_once APPLICATION_PATH.'/models/utilities/acl.php';
class MainController extends Zend_Controller_Action
{
	private $database;
	private $logs;
	private $user_session;
	private $acl;
	
	public function init()
	{
		/* Initialize action controller here */
	
		

		
		$this->logs= Zend_Registry::get('logs');
		
		$this->user_session = new Zend_Session_Namespace('userinfo');
		
		/*
		$this->user_session->uid		=$user['uid'];
		$this->user_session->ad_identity=$user['ad_identity'];
		$this->user_session->firstname	=$user['firstname'];
		$this->user_session->lastname	=$user['lastname'];
		$this->user_session->title		=$user['title'];
		$this->user_session->department	=$user['department'];
		$this->user_session->cellphone	=$user['cellphone'];
		$this->user_session->officephone=$user['officephone'];
		$this->user_session->homephone  =$user['homephone'];
		$this->user_session->email 		=$user['email'];
		$this->user_session->role_id    =$user['role_id'];
		$this->user_session->createdate	=$user['createdate'];
		*/
		$this->view->firstname	=$this->user_session->firstname;
		$this->view->lastname 	=$this->user_session->lastname;
		$this->view->title	  	=$this->user_session->title;
		$this->view->department	=$this->user_session->department;
		
		
		$select=Zend_Registry::get('database')->get_acl_roles()->select()->where("role_default=1");
		$rows=Zend_Registry::get('database')->get_acl_roles()->fetchAll($select);
		
		foreach($rows as $value){
			$this->view->perspective.="<option value='".$value['role_id']."'>".$value['role_name']."</option>";
		}
		
		
		
		/* Main menu */
		
		if($this->user_session->role_id==1){
			$this->view->menu="
			<table width='100%'>
 			<tr>
 			<td><a href=''>Sales1</a></td>
 			<td><a href=''>Sales2</a></td>
 			<td><a href=''>Sales3</a></td>
 			<td><a href=''>Sales4</a></td>
 			</tr>
 			</table>
			";
		}else if($this->user_session->role_id==2){
			$this->view->menu="
			<table width='100%'>
 			<tr>
 			<td><a href=''>Bussiness1</a></td>
 			<td><a href=''>Bussiness2</a></td>
 			<td><a href=''>Bussiness3</a></td>
 			<td><a href=''>Bussiness4</a></td>
 			</tr>
 			</table>
			";
		}else if($this->user_session->role_id==3){
			$this->view->menu="
			<table width='100%'>
 			<tr>
 			<td><a href=''>Support1</a></td>
 			<td><a href=''>Support2</a></td>
 			<td><a href=''>Support3</a></td>
 			<td><a href=''>Support4</a></td>
 			</tr>
 			</table>
			";
		}else if($this->user_session->role_id==4){
			$this->view->menu="
			<table width='100%'>
 			<tr>
 			<td><a href=''>Engineer1</a></td>
 			<td><a href=''>Engineer2</a></td>
 			<td><a href=''>Engineer3</a></td>
 			<td><a href=''>Engineer4</a></td>
 			</tr>
 			</table>
			";
		}else if($this->user_session->role_id==5){
			$this->view->menu="
			<table width='100%'>
 			<tr>
 			<td><a href=''>Executive1</a></td>
 			<td><a href=''>Executive2</a></td>
 			<td><a href=''>Executive3</a></td>
 			<td><a href=''>Executive4</a></td>
 			</tr>
 			</table>
			";
		}else if($this->user_session->role_id==6){
			$this->view->menu="
			<table width='100%'>
 			<tr>
 			<td><a href=''>Installer1</a></td>
 			<td><a href=''>Installer2</a></td>
 			<td><a href=''>Installer3</a></td>
 			<td><a href=''>Installer4</a></td>
 			</tr>
 			</table>
			";
		}
		
		
		
		
		$this->acl= new Thelist_Utility_acl($this->user_session->role_id);
		
		$auth = Zend_Auth::getInstance();
		$identity=$auth->getIdentity();
		
		if($this->user_session->uid=='')
		{
			header('Location: /');
			exit;
		}
		
		$this->_helper->layout->setLayout('main_layout');
	
		 
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
	
	
	
	
	
	
	public function inventoryAction(){
		$this->getHelper('viewRenderer')->setNoRender();
		echo 'inventory';
		
		
		
		echo 'edit estimate='.($this->acl->allow(1, 1)?'allowed':'denied');
		echo '<br>';
		echo 'view credit card='.($this->acl->allow(1, 2)?'allowed':'denied');
		echo '<br>';
		/*
		echo 'edit estimate='.($this->acl->allow(2, 4)?'allowed':'denied');
		echo '<br>';
		echo 'view credit card='.($this->acl->allow(3, 5)?'allowed':'denied');
		echo '<br>';
		echo 'edit credit card='.($this->acl->allow(3, 6)?'allowed':'denied');
		echo '<br>';
		echo 'view ticket='.($this->acl->allow(1, 1)?'allowed':'denied');
		echo '<br>';
		echo 'edit ticket='.($this->acl->allow(1, 2)?'allowed':'denied');
		echo '<br>';
		echo 'view switch='.($this->acl->allow(4, 7)?'allowed':'denied');
		echo '<br>';
		*/
	}
	public function engineerAction(){
		$this->getHelper('viewRenderer')->setNoRender();
		echo 'engineer';
	}
	public function techsupportAction(){
		$this->getHelper('viewRenderer')->setNoRender();
		echo 'tech support';
	}
	public function fieldtechAction(){
		$this->getHelper('viewRenderer')->setNoRender();
		echo 'field tech';
	}
	public function markettingAction(){
		$this->getHelper('viewRenderer')->setNoRender();
		echo 'marketting';
	}
	public function accountingAction(){
		$this->getHelper('viewRenderer')->setNoRender();
		echo 'accounting';
	}
	public function saleAction(){
		$this->getHelper('viewRenderer')->setNoRender();
		echo 'sales';
	}
	public function officerAction(){
		$this->getHelper('viewRenderer')->setNoRender();
		echo 'officer';
	}
	public function adminAction(){
		$this->getHelper('viewRenderer')->setNoRender();
		echo 'admin';
	}
	public function settingAction(){
		$this->getHelper('viewRenderer')->setNoRender();
		echo 'settings';
	}
	
	public function mainAction(){
		$this->getHelper('viewRenderer')->setNoRender();
		echo 'test';
	}
	
	
}
?>