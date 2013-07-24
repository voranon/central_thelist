<?php

//exception codes 16500-16599

class thelist_routeros_command_setsoftware implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_software;
	private $_force_reinstall;
	
	public function __construct($device, $software, $force_reinstall=false)
	{
		//$software
		//object	= software obj
		//string	= ['server_fqdn']
		//string	= ['server_path']
		//string	= ['software_name']
		//string	= ['software_architecture']
		//string	= ['software_version']
		//string	= ['software_manufacturer']
		
		$this->_device 				= $device;
		$this->_software 			= $software;
		$this->_force_reinstall 	= $force_reinstall;
	}
	
	public function execute()
	{
		if (is_object($this->_software)) {
				
			$software_name					= $this->_software->get_software_package_name();
			$software_architecture			= $this->_software->get_software_package_architecture();
			$software_version				= $this->_software->get_software_package_version();
			$software_manufacturer			= $this->_software->get_software_package_manufacturer();
			
			$software_server				= $this->_software->get_software_package_server();
			$software_path					= $this->_software->get_software_package_path();
			$software_file_name				= $this->_software->get_software_package_file_name();
		
		} else {
			
			$software_name					= $this->_software['software_name'];
			$software_architecture			= $this->_software['software_architecture'];
			$software_version				= $this->_software['software_version'];
			$software_manufacturer			= $this->_software['software_manufacturer'];
			
			$software_server				= $this->_software['server_fqdn'];
			$software_path					= $this->_software['server_path'];
			$software_file_name				= $this->_software['server_file_name'];
		}

		//get the current version
		$get_current_software	= new Thelist_Routeros_command_getsoftware($this->_device);
				
		$perform_install	= 'no';
		
		if ($this->_force_reinstall === true) {
			
			$perform_install = 'yes';
			
		} else {
			
		 	if (	$get_current_software->get_running_software_name() != $software_name 
				|| $get_current_software->get_running_software_version() != $software_version 
				|| $get_current_software->get_running_software_manufacturer() != $software_manufacturer 
				|| $get_current_software->get_software_package_architecture() != $software_architecture
 			 ) {
					$perform_install = 'yes';
			}
		}

		if ($perform_install == 'yes') {

			//then upload the file to the routeros board
			$upload_file = new Thelist_Routeros_command_uploadfile($this->_device, $software_path, $software_file_name, null, $software_file_name);
			$upload_file->execute();

			//reboot
			if ($this->_force_reinstall === true) {
				$device_reply = $this->_device->execute_command("/system package downgrade");
			} else {
				$device_reply = $this->_device->execute_command("/system reboot");
			}
			

			//the class that has issued the command to upgrade must keep track and validate that the device is ack up
			//there is no way to do it here because the device class is NOT instansiated there, and as a consequence when 
			//the device api fails we cant catch the exception here  
		}
	}
}