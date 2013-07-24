<?php

//exception codes 16300-16399

class thelist_routeros_command_getserialnumber implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_serial_number=null;
	
	public function __construct($device)
	{
		$this->_device = $device;
	}
	
	public function execute()
	{
		//get the software, because x86 based systems do not have a regular serial number
		$get_equipment_software 				= new Thelist_Routeros_command_getsoftware($this->_device);
		$software_package_architecture 			= $get_equipment_software->get_software_package_architecture();
		
		
		if ($software_package_architecture == 'x86') {
			
			$device_reply = $this->_device->execute_command("/system note print");
			
			preg_match("/Your +Serial +is: +(.*)/", $device_reply->get_message(), $serial_raw);

			if (!isset($model['0'])) {
				
				$time = new Thelist_Utility_time();
				
				$years_in_serials	= range(2000, $time->get_current_year());
				
				$years = '';
				foreach ($years_in_serials as $years_in_serial) {
					
					if ($years_in_serial == $time->get_current_year()) {
						$years .= $years_in_serial;
					} else {
						$years .= $years_in_serial . "|";
					}
					
				}
				preg_match("/(E[0-9].*(".$years.").*)/", $device_reply->get_message(), $serial_raw);
			}
			
			if (isset($serial_raw['0'])) {
				
				$patterns = array('"', "\r", "\r\n", "\n");
				$serial_number = str_replace($patterns, '', $serial_raw['1']);
			}

		} else {
			
			$device_reply = $this->_device->execute_command("/system routerboard print");
			
			preg_match("/serial-number: +(.*)/", $device_reply->get_message(), $serial_raw);
			
			if (isset($serial_raw['0'])) {
					
				$patterns = array('"', ' ', "\r", "\r\n", "\n");
					
				$serial_number = str_replace($patterns, '', $serial_raw['1']);
			}
		}

		if (isset($serial_number)) {
			$this->_serial_number = $serial_number;
		} else {
			throw new exception("we could not determine serial number for device: ".$this->_device->get_fqdn()." ", 16300);
		}		
	}
	
	public function get_serial_number()
	{
		if ($this->_serial_number == null) {
			$this->execute();
		}

		return $this->_serial_number;
	}

}