<?php

//exception codes 8900-8999

class thelist_linuxserver_command_uploadfile implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_local_file_path=null;
	private $_local_file_name=null;
	private $_destination_file_path=null;
	private $_destination_file_name=null;
	
	public function __construct($device, $local_file_path, $local_file_name, $destination_file_path, $destination_file_name)
	{
		$this->_device 								= $device;
		$this->_local_file_path 					= $local_file_path;
		$this->_local_file_name 					= $local_file_name;
		$this->_destination_file_path 				= $destination_file_path;
		$this->_destination_file_name 				= $destination_file_name;
	}

	public function execute()
	{

		$local_path = $this->_local_file_path . "/" . $this->_local_file_name;
		$remote_path = $this->_destination_file_path . "/" . $this->_destination_file_name;

		$result = $this->_device->upload_file($local_path, $remote_path);
			
	}
}
	