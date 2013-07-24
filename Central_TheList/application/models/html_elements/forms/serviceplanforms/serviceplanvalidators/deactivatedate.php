<?php

class thelist_serviceplanvalidator_deactivatedate extends Zend_Validate_Abstract
{

	private $database;
	private $activatedate;

	const DE_ACTIVATE ='invalid de-activate date';


	public function __construct()
	{
		
	
	}
	
	protected $_messageTemplates = array(
		self::DE_ACTIVATE => "De-activate date need to be later than activate date"
	);


	public function isValid($value,$context=null){
		
		$this->_setValue($value);//to insert tested value to the failure message 
		date_default_timezone_set('America/Los_Angeles');
		$activate_date   = strtotime($context['activate_date']);
		$deactivate_date = strtotime($value);
		
		
		if( $activate_date > $deactivate_date ) {
			$this->_error(self::DE_ACTIVATE);
			return false;
		}
		
		return true;
		
	}
}