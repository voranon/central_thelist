<?php

//exception codes 8900-8999

class thelist_routeros_command_uploadfile implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_local_file_path=null;
	private $_local_file_name=null;
	private $_destination_file_path=null;
	private $_destination_file_name=null;
	
	public function __construct($device, $local_file_path, $local_file_name, $destination_file_path, $destination_file_name)
	{	
		//the destination is null because routeros does not support destination, but maybe in the future
		//we need to know the file size because we are relaying through a web server and this class cannot gauge the 
		//file size over http just yet
		$this->_device 								= $device;
		$this->_local_file_path 					= $local_file_path;
		$this->_local_file_name 					= $local_file_name;
		$this->_destination_file_path 				= $destination_file_path;
		$this->_destination_file_name 				= $destination_file_name;
	}

	public function execute()
	{
		$upload_file_to_device	= 'yes';
		
		//does the local file exist?
		if (!file_exists($this->_local_file_path ."/". $this->_local_file_name)) {
			throw new exception("we are asked to upload a file from local to device: '".$this->_device->get_fqdn()."' but the local file does not exist in the provided path:'".$this->_local_file_path ."/". $this->_file_name."'", 8900);
		}
		
		//local file size
		$file_size = filesize($this->_local_file_path ."/". $this->_local_file_name);
				
		//is the file already on the device?
		$get_file_list	= new Thelist_Routeros_command_getfilelist($this->_device, null);
		$existing_file	= $get_file_list->get_file($this->_destination_file_name, true);

		if ($existing_file !== false) {
			
			//file is already on the device
			if ($file_size == $existing_file['bytes']) {
				$upload_file_to_device	= 'no';
			}
		}

		
		if ($upload_file_to_device == 'yes') {
			//we dont want the app server to be the server that the devices download from, theis is why we dont use this webserver.
			
			//does the device have room for the file on the hdd
			$mem_stats	= new Thelist_Routeros_command_getmemorystats($this->_device);
	
			//if there is no room we throw exception
			if ($mem_stats->get_available_non_volatile_memory() < $file_size) {
				throw new exception('there is not enough room on the device hard disk to store the file you want to upload', 8903);
			}
			
			//file is not already on the device
			$copy_success 	= false;

			//this part depends on a local file being copied to a webserver before having routeros download it
			$copy_to_http_host_success = false;
			
			//some time the files come from the local host and are destined for another location of the same local
			//host, we can do this much quicker with copy than sftp from local to local store
			if ($_SERVER['SERVER_NAME'] == Thelist_Utility_staticvariables::get_http_config_server1()) {
			
				$remote_file_path	= Thelist_Utility_staticvariables::get_http_config_server1_folder_path() . "/routeros";
				
				//same server for everything, just copy
				$copy_ok	= copy($this->_local_file_path."/".$this->_local_file_name, $remote_file_path."/".$this->_destination_file_name);
			
				if ($copy_ok === false) {
					$copy_to_http_host_success = false;
				} else {
					$copy_to_http_host_success = true;
				}
			}
				
			//if server did not match or if permissions are off we use sftp for the transfer to the http server
			if ($copy_to_http_host_success === false) {
				
				$remote_file_path	= Thelist_Utility_staticvariables::get_http_config_server1_folder_path() . "/routeros";
					
				//first copy the file to the webserver that will serve the file
				//verify that the file exists and then move it to the http server, mikrotik needs http
				$credential = new Thelist_Model_deviceauthenticationcredential();
				$credential->set_device_user_name(Thelist_Utility_staticvariables::get_http_config_server1_ssh_user());
				$credential->set_device_password(Thelist_Utility_staticvariables::get_http_config_server1_ssh_pass());
				$credential->set_api_name('sftp');
					
				$http_config_server1 = new Thelist_Model_device(Thelist_Utility_staticvariables::get_http_config_server1(), $credential);
				$http_config_server1->copy_file_to_device($this->_local_file_path, $this->_local_file_name, $remote_file_path, $this->_destination_file_name);
				
				
			}
			
			//the file is now on the http server, lets get it to the device
			//routeros wants ip, so conver the hostname to ip.
			$host_name_as_ip = gethostbyname(Thelist_Utility_staticvariables::get_http_config_server1());
			
			$mikrotik_download_path = "http://" . $host_name_as_ip . "/" . Thelist_Utility_staticvariables::get_http_config_server1_base_url() . "/routeros/" . $this->_destination_file_name;
			
			/*
			//for routeros version 4 and below all ssh is broken so we try telnet when we can get it to work
			//if we are running ssh and the
			if ($this->_device->get_device_authentication_credentials()->get_device_api_name() == 'ssh') {
				
				//get original values
				$original_connect_class		= $this->_device->get_device_authentication_credentials()->get_specific_connect_class();
				$original_connect_api		= $this->_device->get_device_authentication_credentials()->get_device_api_name();
			
				//change the specified connect class
				$this->_device->get_device_authentication_credentials()->set_specific_connect_class('routeros');
				//change the api to telnet
				$this->_device->get_device_authentication_credentials()->set_api_name('telnet');
			
				//if possible create a new device based on the old device but using telnet
				$temp_device	= new Thelist_Model_device($this->_device->get_fqdn(), $this->_device->get_device_authentication_credentials());

				//change the api back to ssh
				$this->_device->get_device_authentication_credentials()->set_api_name('ssh');
			
				//change the connect class back
				$this->_device->get_device_authentication_credentials()->set_specific_connect_class($original_connect_class);
			
			} else {
			}
			
			*/
			
			//now that we have sent the file to the http server, now we can have the mikrotik download it.
			$device_reply = $this->_device->execute_command("/tool fetch url=".$mikrotik_download_path." mode=http");
			
			//did it complete
			if (!preg_match("/status: (finished)/", $device_reply->get_message())) {
				throw new exception("we attempted to upload a file named: '".$this->_destination_file_name."' to host: '".$this->_device->get_fqdn()."' via http, but routeros did not report trying to get the file", 8903);
			}
			

			//is the file on the device?
			if ($get_file_list->get_file($this->_destination_file_name, true) === false) {
				throw new exception("we uploaded a file named: '".$this->_destination_file_name."' to host: '".$this->_device->get_fqdn()."' but the remote and local file sizes do not match", 8901);
			}
			
		}
	}
}
	