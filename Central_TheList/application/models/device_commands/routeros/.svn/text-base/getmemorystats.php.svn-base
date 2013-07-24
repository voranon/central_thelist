<?php

//exception codes 9400-9499

class thelist_routeros_command_getmemorystats implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_memory_stats=null;
	
	public function __construct($device)
	{
		$this->_device 								= $device;
	}

	public function execute()
	{
		//now that we have sent the file to the http server, now we can have the mikrotik download it.
		$device_reply = $this->_device->execute_command("/system resource print");
		
		//good reply?
		if (preg_match("/free-memory/", $device_reply->get_message())) {
			
			preg_match("/free-memory: ([0-9]+)(kB|KiB|MiB|GiB)/", $device_reply->get_message(), $free_ram_raw);
			preg_match("/total-memory: ([0-9]+)(kB|KiB|MiB|GiB)/", $device_reply->get_message(), $total_ram_raw);
			preg_match("/free-hdd-space: ([0-9]+)(kB|KiB|MiB|GiB)/", $device_reply->get_message(), $free_non_volatile_raw);
			preg_match("/total-hdd-space: ([0-9]+)(kB|KiB|MiB|GiB)/", $device_reply->get_message(), $total_non_volatile_raw);
			
			$multiplier	= new Thelist_Utility_multiplierconverter();

			$this->_memory_stats['available_ram']					= $multiplier->convert_to_int($free_ram_raw['1'], $free_ram_raw['2']);
			$this->_memory_stats['total_ram']						= $multiplier->convert_to_int($total_ram_raw['1'], $total_ram_raw['2']);
			$this->_memory_stats['available_non_volatile_memory']	= $multiplier->convert_to_int($free_non_volatile_raw['1'], $free_non_volatile_raw['2']);
			$this->_memory_stats['total_non_volatile_memory']		= $multiplier->convert_to_int($total_non_volatile_raw['1'], $total_non_volatile_raw['2']);
			
		} else {
			throw new exception('unable to get memory stats from device', 9400);
		}
	}
	
	public function get_available_non_volatile_memory($refresh=false)
	{
		if($this->_memory_stats == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}

		return $this->_memory_stats['available_non_volatile_memory'];
	}
}
	