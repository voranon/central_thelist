<?php
require_once APPLICATION_PATH.'/models/utilities/database.php';
require_once APPLICATION_PATH.'/models/utilities/logs.php';
require_once APPLICATION_PATH.'/models/utilities/time.php';
require_once 'Zend/Loader/Autoloader.php';

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	
	private $_filecache_14400;
	
	
protected function _initCaches()
{
	$frontend_14400 	= array('lifetime' => '14400', 'automatic_serialization' => true);
	$backend_14400 		= array('cache_dir' => '/tmp/');
	$this->_filecache_14400 	= Zend_Cache::factory('Core', 'File', $frontend_14400, $backend_14400, true, true, true);
	
	Zend_Registry::set('filecache14400', $this->_filecache_14400);

}

	
protected function _initDocType()
{
	
        $this->bootstrap('View');
        $view = $this->getResource('View');
        
        $view->doctype('HTML5');
        $view->headTitle('Thelist beta version');
//        $view->headTitle('Thelist ' .$_SERVER['THELIST_ENV']);        
               
        
        $view->headLink()->prependStylesheet('/css/global/thelist.css');
        $view->headLink()->appendStylesheet('/css/global/jquery-ui/jquery-ui-1.8.17.custom.css');
        
        
        $view->headScript()->prependFile('/javascripts/global/jquery/jquery-1.4.4.min.js');
        $view->headScript()->appendFile('/javascripts/global/jquery-ui/jquery-ui-1.8.17.custom.min.js');
        $view->headScript()->appendFile('/javascripts/global/perspectivemenu.js');
        
        $view->headScript()->appendFile('/javascripts/global/tasks.js');
        $view->headScript()->appendFile('/javascripts/global/buildings.js');
        $view->headScript()->appendFile('/javascripts/global/contacts.js');
        
        $view->headScript()->appendFile('/javascripts/global/searchboxes.js');
        $view->headScript()->appendFile('/javascripts/global/javageturlvariables.js');

}        


protected function _initAutoload(){
	
	$autoLoader2	= Zend_Loader_AutoLoader::getInstance();
	$autoLoader2->registerNamespace('Thelist_');
	
	$autoLoader = new Zend_Loader_Autoloader_Resource(
														array(
						                                     'basePath'              => APPLICATION_PATH.'/models',
			       			                                 'namespace'             => 'Thelist'
															 )
													 );
	
	
	
	$autoLoader->addResourceType('models','','Model');
	$autoLoader->addResourceType('utilities','/utilities','utility');
	$autoLoader->addResourceType('device information','/utilities/device_information','deviceinformation');
	
	$autoLoader->addResourceType('html elements','/html_elements','html_element');
	$autoLoader->addResourceType('program interface','/program_interfaces','program_interface');
	$autoLoader->addResourceType('cisco commands','/device_commands/cisco','cisco_command');
	$autoLoader->addResourceType('bairos commands','/device_commands/bairos','bairos_command');
	$autoLoader->addResourceType('routeros commands','/device_commands/routeros','routeros_command');
	$autoLoader->addResourceType('directvstb commands','/device_commands/directvstb','directvstb_command');
	$autoLoader->addResourceType('linuxserver commands','/device_commands/linuxserver','linuxserver_command');
	
	$autoLoader->addResourceType('multiple device commands','/device_commands/multipledevice','multipledevice_command');
	$autoLoader->addResourceType('commander pattern interfaces','/commander_pattern_interfaces','commander_pattern_interface');
	
	$autoLoader->addResourceType('bairos config','/device_configuration/bairos','bairos_config');
	$autoLoader->addResourceType('routeros config','/device_configuration/routeros','routeros_config');
	$autoLoader->addResourceType('cisco config','/device_configuration/cisco','cisco_config');
	$autoLoader->addResourceType('multipledevice config','/device_configuration/multipledevice','multipledevice_config');
	
	
	
	
	$autoLoader->addResourceType('equipmentconfigurationforms','/html_elements/forms/equipmentconfigurationforms','equipmentconfigurationform');
	$autoLoader->addResourceType('contactforms'	 		,'/html_elements/forms/contactforms'   			,'contactform');
	$autoLoader->addResourceType('taskforms'     		,'/html_elements/forms/taskforms'	  			,'taskform');
	$autoLoader->addResourceType('buildingforms' 		,'/html_elements/forms/buildingforms'  			,'buildingform');
	$autoLoader->addResourceType('equipmentforms'		,'/html_elements/forms/equipmentforms' 			,'equipmentform');
	$autoLoader->addResourceType('equipmentconfigurationforms'		,'/html_elements/forms/equipmentconfigurationforms' 			,'equipmentconfigurationform');
	$autoLoader->addResourceType('inventoryforms'		,'/html_elements/forms/inventoryforms' 			,'inventoryform');
	$autoLoader->addResourceType('entityforms'			,'/html_elements/forms/entityforms' 			,'entityform');
	$autoLoader->addResourceType('projectforms'			,'/html_elements/forms/projectforms' 			,'projectform');
	$autoLoader->addResourceType('purchasingforms'		,'/html_elements/forms/purchasingforms' 		,'purchasingform');
	$autoLoader->addResourceType('serviceplanforms'		,'/html_elements/forms/serviceplanforms' 		,'serviceplanform');
	$autoLoader->addResourceType('serviceplanvalidators','/html_elements/forms/serviceplanforms/serviceplanvalidators' 		,'serviceplanvalidator');
	
	
	
	$autoLoader->addResourceType('Validator','models/html_elements/forms/serviceplanforms/validators','validator_serviceplan');
	$autoLoader->addResourceType('serviceplan_form','models/html_elements/forms/serviceplanforms','form');

	return $autoLoader;
}


protected  function _initDatabase()
{
	$db_php_file 	= APPLICATION_PATH."/models/utilities/database.php";
	$db_cache_id	= 'thelist_database_obj';
	
	$db_version		= filesize($db_php_file);

	if ($cashed_database = $this->_filecache_14400->load($db_cache_id)) {
		
		$sql = 		"SELECT item_value AS database_version FROM items
					WHERE item_type='last_database_version_id'
					";
			
		$last_db_version = $cashed_database->get_thelist_adapter()->fetchOne($sql);
		
		if ($last_db_version == $db_version) {
			
			$database = $cashed_database;
			
		} else {

			$database	= new Thelist_Utility_database();
			
			$this->_filecache_14400->save($database, $db_cache_id);
			
			$database->get_items()->update(array('item_value' => $db_version),"item_name='last_database_version_id'");
			
		}
		
	} else {
		
		$database	= new Thelist_Utility_database();
		$this->_filecache_14400->save($database, $db_cache_id);
		$database->get_items()->update(array('item_value' => $db_version),"item_name='last_database_version_id'");

	}
	

	Zend_Registry::set('database', $database);
	Zend_Registry::set('logs', new Thelist_Utility_logs());
	Zend_Registry::set('time', new Thelist_Utility_time());
	Zend_Session::start();

	}
}
?>