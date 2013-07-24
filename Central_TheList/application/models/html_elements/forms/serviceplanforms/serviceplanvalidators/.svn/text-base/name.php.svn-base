<?php

class thelist_serviceplanvalidator_name extends Zend_Validate_Abstract
{
	
	private $database;
	
	const MSG_DUPLICATE ='msg_duplicate';
	
	
	public function __construct()
	{

	}
	
	
	protected $_messageTemplates = array(
		self::MSG_DUPLICATE => "a service plan named '%value%' is already exist"
	);
	
	
	public function isValid($value){
		$this->_setValue($value);
		
		$sql="SELECT COUNT(*)
			  FROM service_plans
			  WHERE service_plan_name ='".$value."'";
		
				
		$exist = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		if($exist){
			$this->_error(self::MSG_DUPLICATE);
			return false;
		}
		return true;
	}
}