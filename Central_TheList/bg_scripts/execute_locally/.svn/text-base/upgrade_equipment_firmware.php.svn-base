<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

//Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap(array('database'));

require_once APPLICATION_PATH.'/models/utilities/database.php';
require_once APPLICATION_PATH.'/models/equipments.php';
require_once APPLICATION_PATH.'/models/softwarepackages.php';

 		
$database = new database();

		$sql = 	"SELECT * FROM equipment_software_upgrades
				WHERE scheduled_upgrade_time < NOW()
				AND result IS NULL
				AND result_timestamp IS NULL
				AND in_progress='0'
				ORDER BY scheduled_upgrade_time ASC
				";
			
		$equipment_upgrades = $database->get_thelist_adapter()->fetchAll($sql);
			
			if ($equipment_upgrades != null) {
				
				foreach($equipment_upgrades as $equipment_upgrade) {
				
					$software_pkg = new softwarepackage($equipment_upgrade['software_package_id']);
				
					$equipment = new equipments($equipment_upgrade['eq_id']);
					$equipment->upgrade_firmware($software_pkg);
					
					die;
				}
			}	
?>