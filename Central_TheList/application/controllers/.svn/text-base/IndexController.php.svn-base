<?php
class IndexController extends Zend_Controller_Action
{
	private $database;
	private $logs;
	private $user_session;
	
    public function init()
    {
        /* Initialize action controller here */
        

        
    	$this->logs= Zend_Registry::get('logs');
    	
    	$this->user_session = new Zend_Session_Namespace('userinfo');
        	
    }
		

    
    public function indexAction()
    {
        // action body
            	
       	if ($this->getRequest()->isPost()){

			$auth = Zend_Auth::getInstance();

    		try {
			  // Set up the authentication adapter, this may throw an error
    			$authAdapter	=	new Thelist_Utility_authAdapter();
     			$authAdapter->authLDAPAdapter->setIdentity($_POST['username']);
     			$authAdapter->authLDAPAdapter->setCredential($_POST['password']);
     			
  				$result = $auth->authenticate($authAdapter->authLDAPAdapter);
  				
			} catch (Exception $e){
				print_r($e);
				
			}
			
			
			if (!$result->isValid()) {// cant log in
			    
    			$this->view->message="Invalid Username or Password";
    		
			}
			
			else
			{// can log in
			
				 
				
				
    			//$this->view->message="Authentication is Ok";
    			   			    			
    			$identity=$auth->getIdentity();
    			$identity=str_replace("\\","\\\\",$identity);
    			
    			if ($this->getRequest()->getPost('keep')=='yes') {
    			
    				$expire=time()+60*60*24*30; //60 sec * 60 min * 24 hours * 30 days
    				setcookie("loggedIn", "yes", $expire,'/','.zend-dev.belairinternet.com');
    				setcookie("identity", $identity, $expire,'/','.zend-dev.belairinternet.com');
    			}
    			
    			$row=Zend_Registry::get('database')->get_users()->fetchRow("ad_identity='".$identity."'");
    			///////
				
    			$config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/LDAP.ini','production');
    			    			
   				$options = array(
        			'host'                   => $config->ldap->server2->host,
        			'useSsl'                 => false,
        			'username'               => $_POST['username'],
        			'password'               => $_POST['password'],
        			'accountDomainName'      => $config->ldap->server2->accountDomainName,
        			'accountDomainNameShort' => $config->ldap->server2->accountDomainNameShort,
        			'accountCanonicalForm'   => 4, // ACCT_FORM_PRINCIPAL
        			'baseDn'                 => $config->ldap->server2->baseDn,
    			);
   				
   				
				$ldap = new Zend_Ldap($options);
					// 		get account name in DN formnat
				$acctname = $ldap->getCanonicalAccountName($_POST['username'],Zend_Ldap::ACCTNAME_FORM_DN);
				
					// bind required DN format only
		        $ldap->bind($acctname, $_POST['password']);
		        
		      
		        $user_ad_obj = $ldap->getEntry($acctname);
		        
		        //most are not set in the AD and this creates errors, therfore we check 
		        if(isset($user_ad_obj['givenname'][0])) {
		        	$given_name = $user_ad_obj['givenname'][0];
		        } else {
		        	$given_name = 'NA';
		        }
		        if(isset($user_ad_obj['sn'][0])) {
		        	$sn = $user_ad_obj['sn'][0];
		        } else {
		        	$sn = 'NA';
		        }
		        if(isset($user_ad_obj['title'][0])) {
		        	$title = $user_ad_obj['title'][0];
		        } else {
		        	$title = 'NA';
		        }
		        if(isset($user_ad_obj['department'][0])) {
		        	$department = $user_ad_obj['department'][0];
		        } else {
		        	$department = 'NA';
		        }
		        if(isset($user_ad_obj['mobile'][0])) {
		        	$mobile = $user_ad_obj['mobile'][0];
		        } else {
		        	$mobile = 'NA';
		        }
		        if(isset($user_ad_obj['ipphone'][0])) {
		        	$ipphone = $user_ad_obj['ipphone'][0];
		        } else {
		        	$ipphone = 'NA';
		        }
		        if(isset($user_ad_obj['homephone'][0])) {
		        	$homephone = $user_ad_obj['homephone'][0];
		        } else {
		        	$homephone = 'NA';
		        }
		        if(isset($user_ad_obj['mail'][0])) {
		        	$mail = $user_ad_obj['mail'][0];
		        } else {
		        	$mail = 'NA';
		        }
		        if(isset($user_ad_obj['whencreated'][0])) {
		        	$whencreated = $user_ad_obj['whencreated'][0];
		        } else {
		        	$whencreated = 'NA';
		        }
		        
    			if ($row['ad_identity']==$auth->getIdentity()){ /// not first time log in
    				
    			
    				
    				$data = array(
    				    'firstname'        => $given_name,
    				    'lastname'		   => $sn,
    				    'title'			   => $title,
    				    'department'	   => $department,
    				    'cellphone'		   => $mobile,
    				    'officephone'	   => $ipphone,
    				    'homephone'		   => $homephone,
    				    'email'			   => $mail,
    				    'createdate'	   => $whencreated
    				);
    			   				
    				Zend_Registry::get('database')->get_users()->update($data,"ad_identity='".$identity."'");
    			   				
	   			}else{     ///  first time log in
	   				Zend_Registry::get('database')->get_users()->insert(
				
				array(
					'ad_identity'      =>$auth->getIdentity(),
					'firstname'        => $given_name,
					'lastname'		   => $sn,
					'title'			   => $title,
					'department'	   => $department,
					'cellphone'		   => $mobile,
					'officephone'	   => $ipphone,
					'homephone'		   => $homephone,
					'email'			   => $mail,
					'createdate'	   => $whencreated
					)
				);
	   				
	   			}
	   			
       			$user=Zend_Registry::get('database')->get_users()->fetchRow("ad_identity='".$identity."'");
       			
       			$role_id=$user['role_id'];
       			
       			while($role_id!=0){
       				$default_role_id=$role_id;
       				$role_id=Zend_Registry::get('database')->get_thelist_adapter()->fetchOne("SELECT role_supervisor
       			       														   FROM acl_roles 
       			       														   WHERE role_id=?",$default_role_id);
       			}
       			
       			if($default_role_id ==1){
       				$perspective='/residentialsaleperspective/index/';
       			}else if($default_role_id ==2){
       				$perspective='/bussinesssaleperspective/index/';
       			}else if($default_role_id ==3){
       				$perspective='/supportperspective/index/';
       			}else if($default_role_id ==4){
       				$perspective='/Engineerperspective/index/';
       			}else if($default_role_id ==5){
       				$perspective='/Sales/index/';
       			}else if($default_role_id ==6){
       				
       				//$perspective='/Engineerperspective/index/';
       				$perspective='/Executiveofficerperspective/index/';
       				
       			}else if($default_role_id ==7){
       				$perspective='/Sales/index/';
       			}else if($default_role_id ==8){
       				$perspective='/Sales/index/';
       			}else if($default_role_id ==9){
       				$perspective='/purchasingperspective/index/';
       			}else{
       				$perspective='/Main/index/';
       			}
       			
       			
       			
       			$this->user_session->uid				=$user['uid'];
       			$this->user_session->ad_identity		=$user['ad_identity'];
       			$this->user_session->firstname			=$user['firstname'];
       			$this->user_session->lastname			=$user['lastname'];
       			$this->user_session->title				=$user['title'];
       			$this->user_session->department			=$user['department'];
       			$this->user_session->cellphone			=$user['cellphone'];
       			$this->user_session->officephone		=$user['officephone'];
       			$this->user_session->homephone  		=$user['homephone'];
       			$this->user_session->email 				=$user['email'];
       			$this->user_session->role_id    		=$user['role_id'];
       			$this->user_session->default_role_id    =$default_role_id;
       			$this->user_session->perspective		=$perspective;
       			$this->user_session->createdate			=$user['createdate'];
       			$this->user_session->latitude			=$_POST['latitude'];
       			$this->user_session->longitude			=$_POST['longitude'];
       			$this->user_session->accuracy			=$_POST['accuracy'];
       			$this->user_session->altitude			=$_POST['altitude'];
       			$this->user_session->altitudeaccuracy	=$_POST['altitudeaccuracy'];
       			$this->user_session->heading			=$_POST['heading'];
       			$this->user_session->speed				=$_POST['speed'];
       			$this->user_session->browsercode		=$_POST['browsercode'];
       			$this->user_session->browsername		=$_POST['browsername'];
       			$this->user_session->browserversion		=$_POST['browserversion'];
       			$this->user_session->cookiesenabled		=$_POST['cookiesenabled'];
       			$this->user_session->platform			=$_POST['platform'];
       			$this->user_session->useragentheader	=$_POST['useragentheader'];
       			
       			$browser_info = array(
       			
       			           'browsercode' 		=> $this->user_session->browsercode,
       			           'browsername'		=> $this->user_session->browsername,
       			           'browserversion' 	=> $this->user_session->browserversion,
       			           'cookiesenabled'		=> $this->user_session->cookiesenabled,
       			           'platform' 			=> $this->user_session->platform,
       			           'useragentheader' 	=> $this->user_session->useragentheader,
       			
       			);
       			
       			
       			$this->logs->get_user_logger()->insert(
       												array(
       			    											'uid'=>$this->user_session->uid,
       			    											'page_name'=>$this->view->url(),
       			    											'event'=>'login',
       			    											'message_1'=> $this->logs->array_as_xml($browser_info),
       			    											'message_2'=>''
       												)
       												);
       					
       			
       			//////////////////////////////////////////////////////////////////////////////////////
       			
       			
       			
       			
       			
       			
	       		if(isset($_POST['login'])){
	       		
	       		
	       			if ($this->getRequest()->getPost('keep')=='yes') {
	       				
	       				$expire=time()+60*60*24*30; //60 sec * 60 min * 24 hours * 30 days
	       				setcookie("loggedIn", "y", $expire,'/','.zend-dev.belairinternet.com');
	       				setcookie("identity", $identity, $expire,'/','.zend-dev.belairinternet.com');
	       			}
	       			
	       			$sql = 	"SELECT item_id FROM items
       			       		WHERE item_type='user_location_type'
       			       		AND item_name='successful_login'
       			       		";
	       			$user_location_type  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
	       			
	       			$this->logs->loglocation(
										    	$user_location_type,
										    	$this->user_session->latitude,
										    	$this->user_session->longitude,
										    	$this->user_session->accuracy,
										    	$this->user_session->altitude,
										    	$this->user_session->altitudeaccuracy,
										    	$this->user_session->heading,
										    	$this->user_session->speed,
										    	$_SERVER['REMOTE_ADDR']
										    	);
	       		
	       			$this->_redirect($this->user_session->perspective);
		             			     			
	       		}else if(isset($_POST['setting']) && $this->user_session->role_id ==6 ){
	       			
	       			$sql = 	"SELECT item_id FROM items
	       			       	WHERE item_type='user_location_type'
	       			       	AND item_name='successful_login_settings'
	       			       	";
	       			$user_location_type  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
	       			 
	       			$this->logs->loglocation(
										    	$user_location_type,
										    	$this->user_session->latitude,
										    	$this->user_session->longitude,
										    	$this->user_session->accuracy,
										    	$this->user_session->altitude,
										    	$this->user_session->altitudeaccuracy,
										    	$this->user_session->heading,
										    	$this->user_session->speed,
										    	$_SERVER['REMOTE_ADDR']
										    	);
	       			
	       			$this->_redirect('Setting/index/');
	       		}else{
	       			$this->view->message="Invalid Username or Password for Executive";
	       		}
       			
    		}
    		
    	}
    	else
    	{
    		date_default_timezone_set('America/Los_Angeles');
    		$this->view->message=date("F j, Y, g:i a");     
    	}
    	
    	if (isset($_COOKIE['loggedIn'])){
    		if ($_COOKIE['loggedIn'] == "y") {
    	
    			$identity = $_COOKIE['identity'];
    			$user=Zend_Registry::get('database')->get_users()->fetchRow("ad_identity='".$identity."'");
       			
    		
    			$role_id=$user['role_id'];;
    			while($role_id!=0){
    				$default_role_id=$role_id;
    				$role_id=Zend_Registry::get('database')->get_thelist_adapter()->fetchOne("SELECT role_supervisor
    		      													 		   FROM acl_roles 
    		       													 		   WHERE role_id=?",default_role_id);
    			}
    		 
    			if($default_role_id ==1){
    				$perspective='/residentialsaleperspective/index/';
    			}else if($default_role_id ==2){
    				$perspective='/bussinesssaleperspective/index/';
    			}else if($default_role_id ==3){
    				$perspective='/supportperspective/index/';
    			}else if($default_role_id ==4){
    				$perspective='/Engineerperspective/index/';
    			}else if($default_role_id ==5){
    				$perspective='/Sales/index/';
    			}else if($default_role_id ==6){
    				$perspective='/residentialsaleperspective/index/';
    			}else if($default_role_id ==7){
    				$perspective='/Sales/index/';
    			}else if($default_role_id ==8){
    				$perspective='/Sales/index/';
    			}else if($default_role_id ==9){
    				$perspective='/Sales/index/';
    			}else{
    				$perspective='/Main/index/';
    			}
    		
    			$this->user_session->uid				=$user['uid'];
    	   		$this->user_session->ad_identity		=$user['ad_identity'];
 	      		$this->user_session->firstname			=$user['firstname'];
    	   		$this->user_session->lastname			=$user['lastname'];
       			$this->user_session->title				=$user['title'];
       			$this->user_session->department			=$user['department'];
	       		$this->user_session->cellphone			=$user['cellphone'];
    	   		$this->user_session->officephone		=$user['officephone'];
       			$this->user_session->homephone  		=$user['homephone'];
       			$this->user_session->email 				=$user['email'];
	       		$this->user_session->role_id    		=$user['role_id'];
    	   		$this->user_session->default_role_id    =$default_role_id;
       			$this->user_session->perspective		=$perspective;
       			$this->user_session->createdate			=$user['createdate'];
       			$this->user_session->latitude			=$_POST['latitude'];
       			$this->user_session->longitude			=$_POST['longitude'];
       			$this->user_session->accuracy			=$_POST['accuracy'];
       			$this->user_session->altitude			=$_POST['altitude'];
       			$this->user_session->altitudeaccuracy	=$_POST['altitudeaccuracy'];
       			$this->user_session->heading			=$_POST['heading'];
       			$this->user_session->speed				=$_POST['speed'];
    		
       		
       			$browser_info = array(
       			
       			           'browsercode' 		=> $this->user_session->browsercode,
       			           'browsername'		=> $this->user_session->browsername,
       			           'browserversion' 	=> $this->user_session->browserversion,
       			           'cookiesenabled'		=> $this->user_session->cookiesenabled,
       			           'platform' 			=> $this->user_session->platform,
       			           'useragentheader' 	=> $this->user_session->useragentheader,
       			
       			);
       			
       			
       			$this->logs->get_user_logger()->insert(
       												array(
       			    											'uid'=>$this->user_session->uid,
       			    											'page_name'=>$this->view->url(),
       			    											'event'=>'login',
       			    											'message_1'=> $this->logs->array_as_xml($browser_info),
       			    											'message_2'=>''
       												)
       												);
       			
       			$sql = 	"SELECT item_id FROM items
       					WHERE item_type='user_location_type'
       					AND item_name='successful_login'
       					";
       			$user_location_type  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
       			
				$this->logs->loglocation(
									    	$user_location_type,
									    	$this->user_session->latitude,
									    	$this->user_session->longitude,
									    	$this->user_session->accuracy,
									    	$this->user_session->altitude,
									    	$this->user_session->altitudeaccuracy,
									    	$this->user_session->heading,
									    	$this->user_session->speed,
									    	$_SERVER['REMOTE_ADDR']
									    	);
       			
       		
       			$this->_redirect($this->user_session->perspective);
       		}
    	}
    }
    
    public function logoutAction()
    {
    	
    	$sql = 	"SELECT item_id FROM items
    	       	WHERE item_type='user_location_type'
    	       	AND item_name='logout'
    	       	";
    	
    	$user_location_type  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
    	
    	$this->logs->loglocation(
							    	$user_location_type,
							    	$this->user_session->latitude,
							    	$this->user_session->longitude,
							    	$this->user_session->accuracy,
							    	$this->user_session->altitude,
							    	$this->user_session->altitudeaccuracy,
							    	$this->user_session->heading,
							    	$this->user_session->speed,
							    	$_SERVER['REMOTE_ADDR']
							    	);
    	
    	$expire=time()+60*60*24*30; //60 sec * 60 min * 24 hours * 30 days
    	setcookie("loggedIn", "no", $expire,'/','.zend-dev.belairinternet.com');
    	setcookie("identity","no", $expire,'/','.zend-dev.belairinternet.com');
    	$this->getHelper('viewRenderer')->setNoRender();

       			$browser_info = array(
       			
       			           'browsercode' 		=> $this->user_session->browsercode,
       			           'browsername'		=> $this->user_session->browsername,
       			           'browserversion' 	=> $this->user_session->browserversion,
       			           'cookiesenabled'		=> $this->user_session->cookiesenabled,
       			           'platform' 			=> $this->user_session->platform,
       			           'useragentheader' 	=> $this->user_session->useragentheader,
       			
       			);
       			
       			
       			$this->logs->get_user_logger()->insert(
       												array(
       			    											'uid'=>$this->user_session->uid,
       			    											'page_name'=>$this->view->url(),
       			    											'event'=>'login',
       			    											'message_1'=> $this->logs->array_as_xml($browser_info),
       			    											'message_2'=>''
       												)
       												);
    	Zend_Session::destroy(true);
    	$this->_redirect('/');
    }
    
    
    public function define_acl($role_id){
    	$acl= Zend_Registry::get('acl');
    	$parent = Zend_Registry::get('database')->get_acl_roles()->fetchRow('role_id='.$role_id);
    	//echo $parent['role_name'].'<br>';
    	
    	
    	$children = Zend_Registry::get('database')->get_acl_roles()->fetchAll("role_supervisor=".$role_id);
    	$havechild=0;
    	$childname='';
    	$childtemp='';
    	foreach( $children as $child){
    		$havechild=1;
    		$this->define_acl($child['role_id']);
    		$childname.=$child['role_name'].'-';
    		$childtemp[]=$child['role_id'];
    	}
    	if($havechild==0){
    		$acl->addRole( new Zend_Acl_Role( $parent['role_id'] ) );
    		//echo 'add child '.$parent['role_name'].'<br>' ;
    		
    		$privileges= Zend_Registry::get('database')->get_acl_access_control_list()->fetchAll('role_id='.$parent['role_id']);
    		foreach($privileges as $privilege){
    		$acl->allow( $privilege['role_id'],$privilege['resource_id'],$privilege['privilege_id'] );
    		//echo $privilege['role_id'].'-'.$privilege['resource_id'].'-'.$privilege['privilege_id'].'<br>';
    		}
    	}else if($havechild==1){
    		$acl->addRole( new Zend_Acl_Role( $parent['role_id'] ),$childtemp );
    		$privileges= Zend_Registry::get('database')->get_acl_access_control_list()->fetchAll('role_id='.$parent['role_id']);
    		foreach($privileges as $privilege){
    			$acl->allow( $privilege['role_id'],$privilege['resource_id'],$privilege['privilege_id'] );
    			//echo $privilege['role_id'].'-'.$privilege['resource_id'].'-'.$privilege['privilege_id'].'<br>';
    		}
    		//echo 'add '.$parent['role_name'].' leave '.$childname.'<br>' ;
    	}
    	Zend_Registry::set('acl', $acl);
    }
    
    public function postDispatch(){
    	
    	
    }
    
}
?>