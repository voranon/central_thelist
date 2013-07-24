<?php

//exception codes 14300-14399

class thelist_bairos_command_getprocessstats implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_process;
	
	private $_pid=null;
	private $_executable_path=null;
	private $_run_time=null;
	private $_start_time=null;

	public function __construct($device, $process)
	{
		//$process
		//both models process id or service name other we will add
		
		$this->_device 					= $device;
		$this->_process 				= $process;
	}
	
	public function execute()
	{		
		//change this to be more accurate
		
		if (!is_numeric($this->_process)) {
			$device_reply1 = $this->_device->execute_command("ps -eo pid,cmd,etime | grep ".$this->_process." | grep -v grep");
		} else {
			//expand please
			throw new exception('cant handle a numeric search just yet, when looking for process on bairos, expand class please', 14300);
		}
		
		preg_match_all("/([0-9]+) +(.*) +(.*)/", $device_reply1->get_message(), $result1);
		
		if (isset($result1['1']['1'])) {
			throw new exception('result for process is ambigues when getting process on bairos, need to narrow the process input', 14301);
		} elseif (!isset($result1['1']['0'])) {
			
			//no process found and no result possible, null means process not running
			$this->_pid							= null;
			$this->_executable_path				= null;
			$this->_run_time					= null;
			$this->_start_time					= null;
			
		} else {
			
			$time	= new Thelist_Utility_time();
			$run_time	= $time->convert_linux_process_run_time_to_seconds($result1['3']['0']);
			
			$this->_pid							= $result1['1']['0'];
			$this->_executable_path				= $result1['2']['0'];
			$this->_run_time					= $run_time;
			$this->_start_time					= $time->get_date_time_subtract_sec($run_time);

		}
	}
	
	public function get_pid($refresh=true) 
	{
		if($this->_pid == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}

		return $this->_pid;
	}
	
	public function get_executable_path($refresh=true)
	{
		if($this->_executable_path == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
	
		return $this->_executable_path;
	}
	
	public function get_run_time($refresh=true)
	{
		if($this->_run_time == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
	
		return $this->_run_time;
	}
	
	public function get_start_time($refresh=true)
	{
		if($this->_start_time == null) {
			//if the function has never been executed since it was instanciated, we need to run it atleast once
			$this->execute();
		} elseif($refresh == false) {
			//do nothing we have a set of results and are asked not to renew it.
		} else {
			//the default is to run the function
			$this->execute();
		}
	
		return $this->_start_time;
	}
	
	
}