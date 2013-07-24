<?php

//exception codes 11800-11899

class thelist_bairos_command_getsubnetarpentries implements Thelist_Commander_pattern_interface_idevicecommand
{

	private $_device;
	private $_subnet_address;
	private $_subnet_mask;
	
	//when executed
	private $_arp_entries_for_subnet=null;
	
	public function __construct($device, $subnet_address, $subnet_mask)
	{
		//$subnet_address
		//object	= string subnet address
		//string	= string subnet address
		
		//$subnet_mask
		//object	= string subnet mask
		//string	= string subnet mask
		
		//is the ip and cidr valid?
		new Thelist_Deviceinformation_ipaddressentry($subnet_address, $subnet_mask);
		
		$this->_device 				= $device;
		$this->_subnet_address		= $subnet_address;
		$this->_subnet_mask			= $subnet_mask;
	}
	
	public function execute()
	{
		//scan the requested subnet
		$device_reply = $this->_device->execute_command("nmap -n --send-ip -T 5 -sP ".$this->_subnet_address."/".$this->_subnet_mask."");

		//if nmap is not installed, then install it.
		if (preg_match("/(nmap: command not found)/", $device_reply->get_message())){
				
			//if the repo is unreachable from the router, it will just run and run
			$device_reply_test_repo = $this->_device->execute_command("wget --timeout=2 --tries=1 -P /tmp/ http://".Thelist_Utility_staticvariables::get_repository_server_fqdn()."");
			
			if (preg_match("/Giving up/", $device_reply_test_repo->get_message())) {
				throw new exception("we are trying to install nmap, but device: ".$this->_device->get_fqdn().", does not have access to repo: ".Thelist_Utility_staticvariables::get_repository_server_fqdn()." ", 11802);
			}
			
			$device_reply_install = $this->_device->execute_command("yum clean all");
			$device_reply_install = $this->_device->execute_command("yum -y install nmap --enablerepo=baiadd");
				
			if (preg_match("/Error getting repository data for baiadd, repository not found/", $device_reply_install->get_message())) {
				
				//add repo to bairos device
				//does the default config exist on the device already?
				$localfile 		= APPLICATION_PATH."/configs/device_configs/bairos/default_bairos_yum_base_repo.conf";

				$repo_default_file 	= fopen($localfile, "r");
				$content			= fread($repo_default_file, filesize($localfile));

				$repo_file	= new Thelist_Bairos_command_setfilecontent($this->_device, '/etc/yum.repos.d/CentOS-Base.repo', $content, 'override');
				$repo_file->execute();
				
				//new repo content uploaded, clean it out
				//$this->_device->execute_command("yum clean all");
				//let it clean
				//sleep(10);

				$device_reply_install = $this->_device->execute_command("yum -y install nmap --enablerepo=baiadd");
				
			}
			
			//let installation run
			sleep(15);
			//now issue nmap command
			$device_reply = $this->_device->execute_command("nmap -n --send-ip -T 5 -sP ".$this->_subnet_address."/".$this->_subnet_mask."");
			
			if (preg_match("/(nmap: command not found)/", $device_reply->get_message())){
				throw new exception("nmap application is not installed", 11801);
			}
			
			
		}

		//get the result
		if (preg_match("/(Nmap done)/", $device_reply->get_message())) {
			
			//we got the nmap result, now we need the entire arp table right away, because the entries timeout quickly
			$get_arp_table 		= new Thelist_Bairos_command_getarptable($this->_device);
			$entire_arp_table 	= $get_arp_table->get_arp_table();

			preg_match_all("/MAC Address: (\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2})/",$device_reply->get_message(),$mac_address_array_raw);

			//now filter the general arp table so only the entries that match the subnet are made available   
			if (isset($mac_address_array_raw['1']['0'])) {
	
				//find the common mac addresses
				if ($entire_arp_table != false) {
					
					//turn the mac addresses in to objects
					foreach ($mac_address_array_raw['1'] as $one_raw_mac_address) {
						$mac_address_array[] = new Thelist_Deviceinformation_macaddressinformation($one_raw_mac_address);
					}
					
					foreach($entire_arp_table as $general_arp_entry) {
						
						foreach($mac_address_array as $subnet_specific_mac_address) {
							
							if ($subnet_specific_mac_address->get_macaddress() == $general_arp_entry->get_macaddress()) {
								$this->_arp_entries_for_subnet[]	= $general_arp_entry;
							}
						}
					}
				}
			}
		} else {
			throw new exception('nmap application never finished on bai router', 11800);
		}
	}
	
	public function get_arp_entries_for_subnet()
	{
		//used for validation, must be fresh result
		$this->execute();
		return $this->_arp_entries_for_subnet;
	}
}

