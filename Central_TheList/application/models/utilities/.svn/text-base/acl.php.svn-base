<?php

require_once APPLICATION_PATH.'/models/utilities/database.php';

//exception codes 22600-22699

class thelist_utility_acl
{
	//should be made private
	public $_acl;
	private $_role_id;
	
	function __construct ($role_id)
	{
		$this->_acl 		= new Zend_Acl();
		$this->_role_id 	= $role_id;
		
		$resources = Zend_Registry::get('database')->get_acl_resources()->fetchAll();
		
		foreach($resources as $resource) { 
			
			// add all resources to this user
			$this->_acl->add(new Zend_Acl_Resource($resource['resource_id']));
		}

		$this->define_acl($role_id);
	}
	
	public function acl_clearance($action_method_name, $controller_class_name)
	{
		//page
		$sql = 	"SELECT page_id FROM htmlpages
					WHERE controller='".$controller_class_name."'
					AND ACTION='".$action_method_name."'
					";
	
		$page_id	= Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
	
		if (isset($page_id['page_id'])) {
				
			//resource
			$sql2 =	"SELECT resource_id FROM acl_resources
						WHERE page_id='".$page_id."'
						";
				
			$resource_id	= Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
				
			if (isset($resource_id['resource_id'])) {
	
				//privilige
				$sql2 =	"SELECT privilege_id FROM acl_privileges
					 		WHERE resource_id='".$resource_id."'
							";
	
				$privilige_id	= Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
	
				if (isset($privilige_id['privilige_id'])) {
						
					$allowed = $this->_acl->isAllowed($this->_role_id, $resource_id , $privilege_id);
	
					if ($allowed === true) {
						return true;
					} elseif ($allowed === false) {
						return false;
					} else {
						throw new exception("We received an unknown response from the ACL isAllowed method", 22600);
					}

				} else {
					//not present in priviliges, allow use
					return true;
				}
	
			} else {
				//not present in acl resources, allow use
				return true;
			}
				
		} else {
			//not present in html pages, allow use
			return true;
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	function allow($resource_id,$privilege_id){
		//echo $this->_role_id.'-'.$resource_id.'-'.$privilege_id;
		return $this->_acl->isAllowed($this->_role_id, $resource_id,$privilege_id) ? true : false;
	}
	
	function role_allow($role_id,$resource_id,$privilege_id){
		$temp_acl= new acl($role_id);
		return $temp_acl->acl->isAllowed($role_id,$resource_id,$privilege_id)? true : false;
	
	}
	
	function define_acl($role_id){
		
		
			 
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
		
			
			$this->_acl->addRole( new Zend_Acl_Role( $parent['role_id'] ) );
			//echo 'add child '.$parent['role_name'].'<br>' ;
		
			$privileges= Zend_Registry::get('database')->get_acl_access_control_list()->fetchAll('role_id='.$parent['role_id']);
			foreach($privileges as $privilege){
				$this->_acl->allow( $privilege['role_id'],$privilege['resource_id'],$privilege['privilege_id'] );
				 
				//echo $privilege['role_id'].'-'.$privilege['resource_id'].'-'.$privilege['privilege_id'].'<br>';
			}
		
		}else if($havechild==1){
			
			
			 $this->_acl->addRole( new Zend_Acl_Role( $parent['role_id'] ),$childtemp );
			 $privileges= Zend_Registry::get('database')->get_acl_access_control_list()->fetchAll('role_id='.$parent['role_id']);
			 foreach($privileges as $privilege){
				$this->_acl->allow( $privilege['role_id'],$privilege['resource_id'],$privilege['privilege_id'] );
				//echo $privilege['role_id'].'-'.$privilege['resource_id'].'-'.$privilege['privilege_id'].'<br>';
			 }
			
			//echo 'add '.$parent['role_name'].' leave '.$childname.'<br>' ;
		}
 
	}
}
?>