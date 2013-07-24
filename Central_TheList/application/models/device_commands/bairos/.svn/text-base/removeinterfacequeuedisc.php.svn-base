<?php

//exception codes 4900-4999

class thelist_bairos_command_removeinterfacequeuedisc implements Thelist_Commander_pattern_interface_idevicecommand 
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

		if ($running_queue_disc != false) {
			
			//if this is done using the object model then we down all the queues on the interface before we remove the qdisc
			//its not a real requirement just a more graceful way of shutting down rather than just deleting the qdisc
			if (is_object($this->_interface)) {
				
				if ($this->_interface->get_connection_queues() != null) {
					
					foreach ($this->_interface->get_connection_queues() as $queue) {
						$remove_queue	= new Thelist_Bairos_command_removeconnectionqueue($this->_device, $this->_interface, $queue);
						$remove_queue->execute();
					}
				}
			}
			
			
			//if we found the qdisc live then we remove it
			$this->_device->execute_command("sudo /sbin/tc qdisc del dev ".$interface_name." root");
			
			//and then we verify
			$running_queue_disc		= $interface_qdiscs->is_qdisc_active($interface_qdisc_id, true);
			
			if ($running_queue_disc != false) {
			
				//sometimes it takes the router a few seconds to remove the qdisc
				sleep(2);
				$running_queue_disc		= $interface_qdiscs->is_qdisc_active($interface_qdisc_id, true);
				if ($running_queue_disc != false) {
					throw new exception('we tried to remove a queue disc, but the device still sees it active', 4900);
				}
			}

		} else {
			//do nothing the qdisc is already running
		}
	}
}