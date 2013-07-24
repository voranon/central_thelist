<?php

class thelist_bairos_command_getserialnumber implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_serial_number=null;
	
	public function __construct($device)
	{
		$this->_device = $device;
	}
	
	public function execute()
	{
		//we use the mac address of eth0 as our serial number
		$serial = new Thelist_Bairos_command_getinterfacemacaddress($this->_device, 'eth0');
		$this->_serial_number	= $serial->get_mac_address()->get_macaddress();
	}
	
	public function get_serial_number()
	{
		if ($this->_serial_number == null) {
			$this->execute();
		}
		return $this->_serial_number;
	}

}