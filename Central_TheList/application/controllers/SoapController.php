<?php

class SoapController extends Zend_controller_Action
{
	private $_WSDL_URI="http://martin-zend-dev.belairinternet.com/wsdl/fieldinstaller.wsdl";
	private $user_session;
	
	public function init()
	{
		
		$this->user_session = new Zend_Session_Namespace('userinfo');
		
		$this->user_session->uid				= '5';
		$this->user_session->ad_identity		= 'belairinternet\thelist';
		$this->user_session->firstname			= 'System';
		$this->user_session->lastname			= 'User';
		$this->user_session->title				= 'none';
		$this->user_session->department			= 'admin';
		$this->user_session->cellphone			= 'na';
		$this->user_session->officephone		= 'na';
		$this->user_session->homephone  		= 'na';
		$this->user_session->email 				= 'na';
		$this->user_session->role_id    		= '1';
		$this->user_session->default_role_id    = '1';
		$this->user_session->perspective		= '/residentialsaleperspective/index/';
		$this->user_session->createdate			= '2012-05-31 13:11:38';
		
		$autoLoader = Zend_Loader_Autoloader::getInstance();
		$autoLoader->setFallbackAutoloader(true);
		
	}
		
	public function indexAction()
	{
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

		Zend_Session::start();
		
		if(isset($_GET['wsdl'])) {
			//return the WSDL
			$this->autodiscoverWSDL();
		} else {
			//handle SOAP request
			$this->handleSOAP();
		}
	}
		
	private function handleSOAP() 
	{
		$soap = new Zend_Soap_Server($this->_WSDL_URI);
		$soap->setClass('Thelist_Model_publicfieldxmlapi');
		$soap->handle();
	}
	
	private function autodiscoverWSDL() {
		
		$autodiscover = new Zend_Soap_AutoDiscover();
		$autodiscover->setClass('Thelist_Model_publicfieldxmlapi');
		$autodiscover->handle();
		
	}
	
	public function runbackupAction()
	{
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		$sql = "SELECT * FROM equipments";
		
		$equipments = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		if (isset($equipments['0'])) {
			
			foreach ($equipments as $equipment) {
				
				$single_equipment =	new Thelist_Model_equipments($equipment['eq_id']);
				$single_equipment->backup_device();
			}
		}
	}

		
}
?>