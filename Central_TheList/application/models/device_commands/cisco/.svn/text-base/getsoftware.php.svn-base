<?php

//exception codes 11700-11799

class thelist_cisco_command_getsoftware implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	
	//in memory
	private $_running_software_name=null;
	private $_running_software_version=null;
	private $_running_software_manufacturer=null;
	private $_software_package_architecture=null;
	private $_running_software_obj=null;
	
	//on disk
	private $_configured_software_version=null;
	
	public function __construct($device)
	{
		$this->_device 					= $device;
	}

	public function execute()
	{
		//set the terminal so it does not page
		$set_terminal	= new Thelist_Cisco_command_setterminal($this->_device, 80, 0);
		$set_terminal->execute();

		//get the root folder
		$get_connection_root_folder	= new Thelist_Cisco_command_placedeviceconnectioninrootfolder($this->_device);
		$get_connection_root_folder->execute();
		
		//issue command
		$device_reply 	= $this->_device->execute_command("show version");
		
		//set the terminal back to standard
		$set_terminal	= new Thelist_Cisco_command_setterminal($this->_device, 80, 25);
		$set_terminal->execute();
		
		//version
		preg_match("/Version (.*), /", $device_reply->get_message(), $software_version_raw);
		
		if (isset($software_version_raw['0'])) {
			
			$this->_running_software_name 			= 'IOS';
			$this->_running_software_version		= $software_version_raw['1'];
			$this->_running_software_manufacturer	= 'Cisco';

		} else {
			throw new exception("could not determine software version for device: ".$this->_device->get_fqdn()." ", 11700);
		}	

		//architecture
		preg_match("/(C3500XL|C3550|C2900XL|C2960S) Software/", $device_reply->get_message(), $software_package_architecture_raw);

		if (isset($software_package_architecture_raw['0'])) {
			$this->_software_package_architecture = $software_package_architecture_raw['1'];
		} else {
			throw new exception("could not determine software architecture for device: ".$this->_device->get_fqdn()." ", 11701);
		}
	}
	
	public function get_running_software_name() 
	{
		if ($this->_running_software_name == null) {
			$this->execute();
		}
		return $this->_running_software_name;
	}
	
	public function get_running_software_version()
	{
		if ($this->_running_software_version == null) {
			$this->execute();
		}
		return $this->_running_software_version;
	}
	
	public function get_running_software_manufacturer()
	{
		if ($this->_running_software_manufacturer == null) {
			$this->execute();
		}
		return $this->_running_software_manufacturer;
	}
	
	public function get_software_package_architecture()
	{
		if ($this->_software_package_architecture == null) {
			$this->execute();
		}
		return $this->_software_package_architecture;
	}
	
	public function get_running_software_obj()
	{
		if ($this->_running_software_obj == null) {

			if ($this->_running_software_name == null || $this->_running_software_version == null  || $this->_running_software_manufacturer == null || $this->_software_package_architecture == null) {
				$this->execute();
			}
			
			$sql = 	"SELECT software_package_id FROM software_packages
					WHERE software_package_name='".$this->_running_software_name."'
					AND software_package_version='".$this->_running_software_version."'
					AND software_package_manufacturer='".$this->_running_software_manufacturer."'
					AND software_package_architecture='".$this->_software_package_architecture."'
					";
			
			$swid = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
				
			if (isset($swid['software_package_id'])) {
			
				$this->_running_software_obj = new Thelist_Model_softwarepackage($swid);
			
			} else {
			
				$equipment_type		= new Thelist_Cisco_command_getequipmenttype($this->_device);
				$equipment_type_obj = $equipment_type->get_eq_type_obj();

				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);
				
				//setup the unknown software
				$data = array(
								'software_package_manufacturer'    	=>  $this->_running_software_manufacturer,
								'software_package_name'   			=>  $this->_running_software_name,
								'software_package_architecture'    	=>  $this->_software_package_architecture,
								'software_package_version'   		=>  $this->_running_software_version,
								'software_package_server'    		=>  'none',
								'software_package_path'   			=>  'none',
								'software_package_file_name'		=>  'none',
				);
			
				$new_software_pkg = Zend_Registry::get('database')->insert_single_row('software_packages',$data,$class,$method);
			
				$this->_running_software_obj =  new Thelist_Model_softwarepackage($new_software_pkg);
			}
		}
		
		return $this->_running_software_obj;
	}
}