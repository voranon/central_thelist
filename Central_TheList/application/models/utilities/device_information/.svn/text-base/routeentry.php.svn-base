<?php

//exception codes 20200-20299

class thelist_deviceinformation_routeentry
{
	private $_subnet_prefix;
	private $_prefix_bitmask;
	private $_gateway;
	private $_cost=null;
	
	private $_learned_from_protocol_name=null;
	private $_route_type=null;
	
	
	public function __construct($subnet_prefix, $prefix_bitmask, $gateway, $cost=null)
	{
		if (preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $gateway)){
			
			//gateway validation here some day
			$this->_gateway				= $gateway;
			
		} elseif (preg_match("/connected/", $gateway)) {
			
			//connected route
			$this->set_route_type('connected');
			$this->_cost = 0;
		}
		
	
		$this->_subnet_prefix		= $subnet_prefix;
		$this->_prefix_bitmask		= $prefix_bitmask;
		$this->_cost				= $cost;
		
	}
	
	public function get_subnet_prefix()
	{
		return $this->_subnet_prefix;
	}
	public function get_prefix_bitmask()
	{
		return $this->_prefix_bitmask;	
	}
	public function get_gateway()
	{
		return $this->_gateway;
	}
	public function get_cost()
	{
		return $this->_cost;
	}
	
	public function set_route_type($type)
	{
		if ($this->_learned_from_protocol_name != null) {
			throw new exception("you are trying to set route type: '".$type."', for a route that has been learned through protocol '".$this->_learned_from_protocol_name."', that is not possible, once a route has been learned from a protocol it can only be dynamic type ", 20203);
		} elseif ($this->_learned_from_protocol_name == null && $type == 'dynamic') {
			throw new exception("you you cannot set type'dynamic' directly, it must be done through the set_learned_from_protocol method", 20203);
		}
		
		if ($type != 'static' && $type != 'connected') {
			throw new exception('route type is not corretly formatted', 20202);
		} else {
			$this->_route_type	= $type;
		}
	}
	
	public function get_route_type()
	{
		return $this->_route_type;
	}
	
	public function set_route_status($status)
	{
		if ($status != 'active' && $status != 'disabled' && $status != 'unreachable') {
			throw new exception('route type is not corretly formatted', 20201);
		} else {
			$this->_route_status	= $status;
		}
	}
	
	public function get_route_status()
	{
		return $this->_route_status;
	}
	
	public function set_learned_from_protocol($protocol_name)
	{
		if ($protocol_name != 'ospf' && $protocol_name != 'bgp4' && $protocol_name != 'dhcp') {
			throw new exception('routing protocol name is not corretly formatted', 20200);
		} else {
			//all learned routes are dynamic, and this is the only way to set them dynamic
			$this->_learned_from_protocol_name	= $protocol_name;
			$this->_route_type					= 'dynamic';
		}
	}
	
	public function get_learned_from_protocol()
	{
		return $this->_learned_from_protocol_name;
	}

}
?>