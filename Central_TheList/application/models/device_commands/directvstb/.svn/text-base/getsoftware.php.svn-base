<?php

//exception codes 21700-21799 

class thelist_directvstb_command_getsoftware implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_running_software_name=null;
	private $_running_software_version=null;
	private $_running_software_manufacturer=null;
	private $_software_package_architecture=null;
	private $_running_software_obj=null;
	
	private $_device;
	private $_arp_entry;
	private $_eq_type_obj=null;
	
	public function __construct($device, $arp_entry)
	{
		$this->_device 		= $device;
		$this->_arp_entry 	= $arp_entry;
	}
	
	public function execute()
	{
		$device_reply = $this->_device->execute_command("info/getVersion");
		
		preg_match("/\"stbSoftwareVersion\": \"(.*)\",/", $device_reply->get_message(), $software_raw);
		
		if (isset($software_raw['0'])) {
			
			$this->_eq_type_obj			= new Thelist_Directvstb_command_getequipmenttype($this->_device, $this->_arp_entry);
			$model_name					= $this->_eq_type_obj->get_model_name(false);
				
			$patterns = array('\"', '-', ' ', "\r", "\r\n", "\n");
				
			$this->_running_software_name 			= 'directv';
			$this->_running_software_version		= str_replace($patterns, '', $software_raw['1']);
			$this->_running_software_manufacturer	= 'DirecTV';
			$this->_software_package_architecture 	= $model_name;
	
		} else {
			throw new exception("we could not determine software for device: ".$this->_device->get_fqdn()." ", 21700);
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