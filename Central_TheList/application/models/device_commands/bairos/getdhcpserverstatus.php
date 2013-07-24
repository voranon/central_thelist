<?php

//exception codes 5600-5699

class thelist_bairos_command_getdhcpserverstatus implements Thelist_Commander_pattern_interface_idevicecommand
{

	private $_device;
	private $_interface;
	
	private $_dhcp_server_admin_status=null;
	private $_dhcp_server_operational_status=null;
	
	public function __construct($device, $interface)
	{
		//$interface
		//object	= interface_obj
		//string	= ['interface_name']
		
		$this->_device 		= $device;
		$this->_interface 	= $interface;
	}
	
	public function execute()
	{
		//even though the bairos is using isc dhcp and runs on a single config file, and by extension is really tied to  an interface
		//implicitly through the shared subnets, we still require an interface to make the commander class uniform as most other 
		//dhcp servers are explisitly interface specific
		
		//if we are dealing with an object
		if (is_object($this->_interface)) {
			$interface_name			= $this->_interface->get_if_name();
		} else {
			$interface_name			= $this->_interface;
		}

		$dhcp_process						= new Thelist_Bairos_command_getprocessstats($this->_device, '/usr/sbin/dhcpd');
		$get_dhcpd_process_start_time		= $dhcp_process->get_start_time(true);

		if ($get_dhcpd_process_start_time == null) {
			//service not running at all
			$this->_dhcp_server_operational_status	= null;
			
		} else {
			
			$time					= new Thelist_Utility_time();
			$start_epoch_time		= $time->convert_string_to_epoch_time($get_dhcpd_process_start_time);

			$log_time_clean['patterns']['0'] = $time->convert_epoch_to_linux_log_formatted_date($start_epoch_time - 1);
			$log_time_clean['patterns']['1'] = $time->convert_epoch_to_linux_log_formatted_date($start_epoch_time);
			$log_time_clean['patterns']['2'] = $time->convert_epoch_to_linux_log_formatted_date($start_epoch_time + 1);

			$file_list	= new Thelist_Bairos_command_getfilelist($this->_device, '/var/log/');
			
			//try all log files until there are no more, dhcpd sores in /var/log/messages 5 rotating files
			
			//base file name
			$base_file_name = 'messages';
			$i=0;
			while ($i < 6 && !isset($running_interface_names)) {
				
				if ($i == 0) {
					$get_file_name	= $file_list->get_file($base_file_name, false);
				} else {
					
					//after the current file the files are named .1 .2 etc there are 5 files total
					$rotated_log_name	= $base_file_name . "." . $i;
					$get_file_name	= $file_list->get_file($rotated_log_name, false);
				}
				
				if ($get_file_name != false) {
					
					//log file path
					$log_file_path = "/var/log/" . $get_file_name['file_name'];
					
					$log_file		= new Thelist_Bairos_command_getfilecontent($this->_device, $log_file_path, $log_time_clean);
					$content_array	= $log_file->get_content_array();

					if ($content_array != null) {

						foreach($content_array as $log_line) {
								
							if (preg_match("/Listening on LPF\/(.*?)\//", $log_line, $match)) {
								$running_interface_names[]	= $match['1'];
							}
						}
					}
				}
				$i++;
			}
			
			if (!isset($running_interface_names)) {

				$is_running = 'no';
				//if we got no running interfaces in the time span it could be because the log files are too old
				//we then do something drastic and reload the dhcpd server (verified running) and check that result
				//the set class will ask us for the status and that would become a unlimited nested loop.

				//we simply look in the last log file and see if there are addresses that have been issued by the interface
				//this is not safe, but i have to move on
					
				//look at the last hour of log entries
				$log_time_clean['patterns']['0'] = substr($time->convert_epoch_to_linux_log_formatted_date(time()), 0, -5);
				
				$log_file		= new Thelist_Bairos_command_getfilecontent($this->_device, '/var/log/messages', $log_time_clean);
				$content_array	= $log_file->get_content_array();

				if ($content_array != null) {
					
					foreach($content_array as $line) {
						
						if (preg_match("/ +via +".$interface_name."/", $line)) {
							$is_running = 'yes';
						}
					}
				}
				
				if ($is_running == 'yes') {
					$this->_dhcp_server_operational_status = 1;
				} else {
					$this->_dhcp_server_operational_status = 0;
				}
				
			} elseif (isset($running_interface_names)) {

				$is_running = 'no';

				foreach ($running_interface_names as $running_interface_name) {
						
					if($running_interface_name == $interface_name) {
						$is_running = 'yes';
					}
				}
				
				if ($is_running == 'yes') {
					$this->_dhcp_server_operational_status = 1;
				} else {
					$this->_dhcp_server_operational_status = 0;
				}
			}
		}

		//get admin status
		$is_admin_active = 'no';
		$main_file_path	= "/etc/dhcpd.conf";
		
		$dhcpd_file		= new Thelist_Bairos_command_getfilecontent($this->_device, $main_file_path);
		$content_array	= $dhcpd_file->get_content_array();
		
		
		if ($content_array != null) {
			
			foreach($content_array as $line) {
				
				//only includes
				if (preg_match("/include/", $line)) {
					//is there a line that matches our interface name
					if (preg_match("/\/".$interface_name."\";/", $line)) {
						$is_admin_active = 'yes';
					}
				}
			}
		} else {
			throw new exception("dhcp config file does not exist for: ".$this->_device->get_fqdn()." ", 5600);
		}
		
		if ($is_admin_active == 'yes') {
			$this->_dhcp_server_admin_status = 1;
		} else {
			$this->_dhcp_server_admin_status = 0;
		}
		
	}
	
	public function get_dhcp_server_admin_status($refresh=true)
	{
		
		if($this->_dhcp_server_admin_status == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
		
		return $this->_dhcp_server_admin_status;
	}
	
	public function get_dhcp_server_operational_status($refresh=true)
	{
	
		if($this->_dhcp_server_operational_status == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
	
		return $this->_dhcp_server_operational_status;
	}
}