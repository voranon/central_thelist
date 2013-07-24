<?php

//exception codes 9200-9299

class thelist_routeros_command_resetconfig implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_file_name=null;

	
	public function __construct($device, $file_name=null)
	{
		//file_path is the path to a local file on the device that will provide the new config after reset
		//if it is empty a general file will be used
		$this->_device 						= $device;
		$this->_file_name 					= $file_name;
	}

	public function execute()
	{
		if($this->_file_name == null) {
			//does the default config exist on the device already?
			$this->_file_name 	= 'default_cpe_config.rsc';
				
		}

		//storage for all routeros configs
		$local_path 		= APPLICATION_PATH."/configs/device_configs/routeros";
		
		//does the local file exist?
		if (!file_exists($local_path ."/". $this->_file_name)) {
			throw new exception("we are asked to reset the device using an existing configuration, but the local file does not exist, make sure to upload it to: '".$local_path."'", 9200);
		}

		//upload the file to the ros board
		$upload_file = new Thelist_Routeros_command_uploadfile($this->_device, $local_path, $this->_file_name, null, $this->_file_name);
		$upload_file->execute();
		
		//everything above checked out and we now either have the user provided file on the device or our own default config
		//reset the device using that file
		$device_reply = $this->_device->execute_command("/system reset-configuration no-defaults=yes run-after-reset=".$this->_file_name."");
		
	}
}
	