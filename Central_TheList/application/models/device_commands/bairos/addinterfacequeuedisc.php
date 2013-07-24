<?php

//exception codes 4800-4899

class thelist_bairos_command_addinterfacequeuedisc implements Thelist_Commander_pattern_interface_idevicecommand 
{

	private $_device;
	private $_interface;
	
	public function __construct($device, $interface)
	{
		//$interface
		//object	= interface_obj
		//string	= interface name
		
		$this->_device 					= $device;
		$this->_interface 				= $interface;

	}
	
	public function execute()
	{
		
		if (is_object($this->_interface)) {
			$interface_name			= $this->_interface->get_if_name();
			$interface_qdisc_id		= $this->_interface->get_if_id();
		} else {
			$interface_name			= $this->_interface;
			preg_match("/eth([0-9]+)/", $interface_name, $result);
			$interface_qdisc_id		= $result['1'];
		}
		
		$interface_qdiscs		= new Thelist_Bairos_command_getinterfacequeuediscs($this->_device, $this->_interface);
		$running_queue_disc		= $interface_qdiscs->is_qdisc_active($interface_qdisc_id, true);

		if ($running_queue_disc == false) {
			
			//if we did not find the qdisc above then we set it up
			$this->_device->execute_command("sudo /sbin/tc qdisc add dev ".$interface_name." root handle ".$interface_qdisc_id.":0 htb default 2000 r2q 450");
			
			//and then we verify
			$running_queue_disc		= $interface_qdiscs->is_qdisc_active($interface_qdisc_id, true);
			
			if ($running_queue_disc == false) {
			
				//sometimes it takes the router a few seconds to implement the qdisc
				sleep(2);
				$running_queue_disc		= $interface_qdiscs->is_qdisc_active($interface_qdisc_id, true);
				if ($running_queue_disc == false) {
					throw new exception('we tried to setup a new queue disc, but the device does not see the disc active', 4800);
				}
			}
			
			//we have now setup the qdisc, if this is the object model then we now bring up all the queues as well
			$add_all_interface_queues		= new Thelist_Bairos_command_setinterfaceconnectionqueues($this->_device, $this->_interface);
			$add_all_interface_queues->execute();

		} else {
			//do nothing the qdisc is already running
		}
	}
}