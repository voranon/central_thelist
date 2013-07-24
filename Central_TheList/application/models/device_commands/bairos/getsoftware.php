<?php

//exception codes 16900-16999

class thelist_bairos_command_getsoftware implements Thelist_Commander_pattern_interface_idevicecommand 
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
		$this->_device = $device;
	}
	
	public function execute()
	{
	
		$device_reply 	= $this->_device->execute_command('cat /etc/*release*');
		
		$this->_running_software_name 			= 'Centos';
		$this->_running_software_manufacturer	= 'Redhat';
		
		preg_match("/CentOS release +([0-9]+\.[0-9]+)/", $device_reply->get_message(), $software_raw);
		
		if (isset($software_raw['0'])) {
			$this->_running_software_version		= $software_raw['1'];
		} else {
			throw new exception("could not determine software version for device: ".$this->_device->get_fqdn()." ", 16900);
		}
		
		$device_reply2 	= $this->_device->execute_command('uname -a');
		
		if (preg_match("/x86_64/", $device_reply2->get_message(), $empty)){
			$this->_software_package_architecture = 'x86_64';
		} elseif (preg_match("/i386/", $device_reply2->get_message(), $empty)) {
			$this->_software_package_architecture = 'i386';
		}

		if ($this->_software_package_architecture == null) {
			throw new exception("could not determine software architecture for device: ".$this->_device->get_fqdn()." ", 16901);
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
					
				$equipment_type		= new Thelist_Bairos_command_getequipmenttype($this->_device);
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