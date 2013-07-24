<?php 

//exception codes 8100-8199

class thelist_model_iptrafficrule
{
	private $_ip_traffic_rule_id;
	private $_ip_traffic_rule_desc=null;
	private $_ip_traffic_rule_chain_id=null;
	private $_ip_traffic_rule_action_id=null;
	private $_ip_traffic_rule_mark=null;
	private $_ip_traffic_rule_priority=null;
	private $_eq_id=null;
	private $_interfaces=null;
	private $_ip_traffic_rule_ip_ports=null;
	private $_ip_traffic_rule_ip_subnets=null;
	
	//resolved values
	private $_ip_traffic_rule_chain_name=null;
	private $_ip_traffic_rule_action_name=null;
	
	public function __construct($ip_traffic_rule_id)
	{
		$this->_ip_traffic_rule_id = $ip_traffic_rule_id;

		$sql = 	"SELECT * FROM ip_traffic_rules 
				WHERE ip_traffic_rule_id='".$this->_ip_traffic_rule_id."'
				";
		
		$ip_traffic_rule_detail  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

		$this->_ip_traffic_rule_desc				= $ip_traffic_rule_detail['ip_traffic_rule_desc'];
		$this->_ip_traffic_rule_chain_id			= $ip_traffic_rule_detail['ip_traffic_rule_chain_id'];
		$this->_ip_traffic_rule_action_id			= $ip_traffic_rule_detail['ip_traffic_rule_action_id'];
		$this->_ip_traffic_rule_mark				= $ip_traffic_rule_detail['ip_traffic_rule_mark'];
		$this->_ip_traffic_rule_priority			= $ip_traffic_rule_detail['ip_traffic_rule_priority'];
		$this->_eq_id								= $ip_traffic_rule_detail['eq_id'];

		//get the interfaces for this rule
		$sql1 = "SELECT * FROM ip_traffic_rule_interface_mapping
				WHERE ip_traffic_rule_id='".$this->_ip_traffic_rule_id."'
				";
		
		$ip_traffic_rule_interfaces  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql1);
		
		if (isset($ip_traffic_rule_interfaces['0'])) {

			foreach($ip_traffic_rule_interfaces as $ip_traffic_rule_interface) {
				
				$this->_interfaces[$ip_traffic_rule_interface['if_id']] = new Thelist_Model_equipmentinterface($ip_traffic_rule_interface['if_id']);
				$this->_interfaces[$ip_traffic_rule_interface['if_id']]->fill_ip_traffic_rule_attributes($ip_traffic_rule_interface['ip_traffic_rule_if_map_id'], $ip_traffic_rule_interface['ip_traffic_rule_if_role_id']);

			}
		}
		
		//get the ip_protocol_port_mappings for this rule
		$sql2 = "SELECT ippm.ip_protocol_port_map_id, ipp.ip_protocol_port_number, ipp.ip_protocol_port_direction, ipp.ip_protocol_port_id, ip_pro.ip_protocol_id, ip_pro.ip_protocol_name, ippm.ip_protocol_port_map_match FROM ip_protocol_port_mapping ippm
				INNER JOIN ip_protocol_ports ipp ON ipp.ip_protocol_port_id=ippm.ip_protocol_port_id
				INNER JOIN ip_protocols ip_pro ON ip_pro.ip_protocol_id=ipp.ip_protocol_id
				WHERE ippm.ip_traffic_rule_id='".$this->_ip_traffic_rule_id."'
				";
		
		$ip_traffic_rule_ip_ports  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
		
		if (isset($ip_traffic_rule_ip_ports['0'])) {
			
			foreach($ip_traffic_rule_ip_ports as $ip_traffic_rule_ip_port) {
				
				$this->_ip_traffic_rule_ip_ports[$ip_traffic_rule_ip_port['ip_protocol_port_map_id']] = $ip_traffic_rule_ip_port;
				
			}
		}
		
		//get the ip subnets for this rule
		$sql3 = "SELECT iptrism.ip_traffic_rule_ip_subnet_map_id, iptris.ip_traffic_rule_ip_subnet_id, iptris.ip_traffic_rule_ip_subnet_address, iptris.ip_traffic_rule_ip_subnet_cidr, iptrism.ip_traffic_rule_ip_subnet_map_match, iptrism.ip_traffic_rule_ip_subnet_map_direction FROM ip_traffic_rule_ip_subnet_mapping iptrism
				INNER JOIN ip_traffic_rule_ip_subnets iptris ON iptris.ip_traffic_rule_ip_subnet_id=iptrism.ip_traffic_rule_ip_subnet_id
				WHERE iptrism.ip_traffic_rule_id='".$this->_ip_traffic_rule_id."'
				";
		
		$ip_traffic_rule_ip_subnets  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql3);
		
		if (isset($ip_traffic_rule_ip_subnets['0'])) {
			
			foreach($ip_traffic_rule_ip_subnets as $ip_traffic_rule_ip_subnet) {
				
				$this->_ip_traffic_rule_ip_subnets[$ip_traffic_rule_ip_subnet['ip_traffic_rule_ip_subnet_map_id']] = $ip_traffic_rule_ip_subnet;
			
			}	
		}
	}
	
	//resolving functions
	public function get_ip_traffic_rule_chain_name()
	{
		if ($this->_ip_traffic_rule_chain_name == null) {
		
			$sql = "SELECT * FROM ip_traffic_rule_chains
					WHERE ip_traffic_rule_chain_id='".$this->_ip_traffic_rule_chain_id."'
					";
			
			$result = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			$this->_ip_traffic_rule_chain_name  = $result['ip_traffic_rule_chain_name'];

		}
		
		return $this->_ip_traffic_rule_chain_name;
	}
	
	public function get_ip_traffic_rule_action_name()
	{
		if ($this->_ip_traffic_rule_action_name == null) {
	
			$sql = "SELECT * FROM ip_traffic_rule_actions
					WHERE ip_traffic_rule_action_id='".$this->_ip_traffic_rule_action_id."'
					";
				
			$result = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			$this->_ip_traffic_rule_action_name  = $result['ip_traffic_rule_action_name'];
	
		}
	
		return $this->_ip_traffic_rule_action_name;
	}
	
	
	public function get_ip_traffic_rule_id()
	{
		return $this->_ip_traffic_rule_id;
	}
	public function get_ip_traffic_rule_desc()
	{
		return $this->_ip_traffic_rule_desc;
	}
	public function get_ip_traffic_rule_chain_id()
	{
		return $this->_ip_traffic_rule_chain_id;
	}
	public function get_ip_traffic_rule_action_id()
	{
		return $this->_ip_traffic_rule_action_id;
	}
	public function get_ip_traffic_rule_mark()
	{
		return $this->_ip_traffic_rule_mark;
	}
	public function get_ip_traffic_rule_priority()
	{
		return $this->_ip_traffic_rule_priority;
	}
	public function get_eq_id()
	{
		return $this->_eq_id;
	}
	public function get_interfaces()
	{
		return $this->_interfaces;
	}
	public function get_ip_traffic_rule_ip_ports()
	{
		return $this->_ip_traffic_rule_ip_ports;
	}
	public function get_ip_traffic_rule_ip_subnets()
	{
		return $this->_ip_traffic_rule_ip_subnets;
	}
	
	public function map_traffic_interface($interface_obj, $ip_traffic_rule_if_role_id)
	{

		if (is_object($interface_obj) && is_numeric($ip_traffic_rule_if_role_id)) {
				
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
		
			$sql = 	"SELECT ip_traffic_rule_if_map_id FROM ip_traffic_rule_interface_mapping
					WHERE ip_traffic_rule_id='".$this->_ip_traffic_rule_id."'
					AND if_id='".$interface_obj->get_if_id()."'
					AND ip_traffic_rule_if_role_id='".$ip_traffic_rule_if_role_id."'
					";
				
			$existing_if_map  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
				
			//check if this interface is alreay mapped to this rule
			if (isset($existing_if_map['ip_traffic_rule_if_map_id'])) {
		
				//already mapped return that
				return $existing_if_map;
			} else {
				
				$data = array(
													'if_id'								=> $interface_obj->get_if_id(),
													'ip_traffic_rule_id'				=> $this->_ip_traffic_rule_id,
													'ip_traffic_rule_if_role_id'		=> $ip_traffic_rule_if_role_id,
				);
				
				$new_ip_traffic_rule_if_map_id = Zend_Registry::get('database')->insert_single_row('ip_traffic_rule_interface_mapping',$data,$class,$method);
				
				//add it to the array, an interface can only be in one role per rule
				$this->_interfaces[$interface_obj->get_if_id()] = $interface_obj;
				$this->_interfaces[$interface_obj->get_if_id()]->fill_ip_traffic_rule_attributes($new_ip_traffic_rule_if_map_id, $ip_traffic_rule_if_role_id);
				
				return $new_ip_traffic_rule_if_map_id;
			}

		} else {
		
			throw new exception('you must provide interface object', 8102);
		}

	}
	
	public function remove_traffic_interface($interface_obj)
	{
		//an interface can only be in one role
		if (isset($this->_interfaces[$interface_obj->get_if_id()])) {
			
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
			
			Zend_Registry::get('database')->delete_single_row($this->_interfaces[$interface_obj->get_if_id()]->get_ip_traffic_rule_if_map_id(), 'ip_traffic_rule_interface_mapping', $class, $method);
			
			//unset it from here
			unset($this->_interfaces[$interface_obj->get_if_id()]);
			
		} else {
			throw new exception("you are trying to remove if_id: ".$interface_obj->get_if_id()." from traffic rule: ".$this->_ip_traffic_rule_id.", but it is not mapped to this rule  ", 8103);
		}
	}
	
	public function remove_traffic_ip_subnet($ip_traffic_rule_ip_subnet_map_id)
	{
		
		if (isset($this->_ip_traffic_rule_ip_subnets[$ip_traffic_rule_ip_subnet_map_id])) {
			
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
			
			//find out if this is the only map that is using the subnet

			$sql ="SELECT COUNT(ip_traffic_rule_ip_subnet_map_id) FROM ip_traffic_rule_ip_subnet_mapping
					WHERE ip_traffic_rule_ip_subnet_id=".$this->_ip_traffic_rule_ip_subnets[$ip_traffic_rule_ip_subnet_map_id]['ip_traffic_rule_ip_subnet_id']."
					AND ip_traffic_rule_id!='".$this->_ip_traffic_rule_id."'
					";
			
			$using_subnet_count  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
			//under all circumstances we are removing the map so we do that
			Zend_Registry::get('database')->delete_single_row($ip_traffic_rule_ip_subnet_map_id, 'ip_traffic_rule_ip_subnet_mapping', $class, $method);
			
			//if this is the last to use the subnet then remove the subnet as well
			if ($using_subnet_count == 0) {
				Zend_Registry::get('database')->delete_single_row($this->_ip_traffic_rule_ip_subnets[$ip_traffic_rule_ip_subnet_map_id]['ip_traffic_rule_ip_subnet_id'], 'ip_traffic_rule_ip_subnets', $class, $method);
			}
			
		} else {
			throw new exception("you are trying to remove ip_subnet_map_id: ".$ip_traffic_rule_ip_subnet_map_id." from traffic rule: ".$this->_ip_traffic_rule_id.", but it is not mapped to this rule  ", 8104);
		}
	}
	
	public function remove_traffic_ip_protocol_port_mapping($ip_protocol_port_map_id)
	{
	
		if (isset($this->_ip_traffic_rule_ip_ports[$ip_protocol_port_map_id])) {
				
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
				
			//find out if this is the only map that is using the subnet
	
			$sql ="SELECT COUNT(ip_protocol_port_map_id) FROM ip_protocol_port_mapping
					WHERE ip_protocol_port_id=".$this->_ip_traffic_rule_ip_ports[$ip_protocol_port_map_id]['ip_protocol_port_id']."
					AND ip_traffic_rule_id!='".$this->_ip_traffic_rule_id."'
					";
				
			$using_port_count  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
				
			//under all circumstances we are removing the map so we do that
			Zend_Registry::get('database')->delete_single_row($ip_protocol_port_map_id, 'ip_protocol_port_mapping', $class, $method);
				
			//if this is the last to use the port then remove it as well
			if ($using_port_count == 0) {
				Zend_Registry::get('database')->delete_single_row($this->_ip_traffic_rule_ip_ports[$ip_protocol_port_map_id]['ip_protocol_port_id'], 'ip_protocol_ports', $class, $method);
			}
				
		} else {
			throw new exception("you are trying to remove ip_protocol_port_map_id: ".$ip_protocol_port_map_id." from traffic rule: ".$this->_ip_traffic_rule_id.", but it is not mapped to this rule  ", 8105);
		}
	}
	
	
	public function map_traffic_ip_subnet($ip_subnet_address, $ip_subnet_cidr, $match, $direction)
	{
		if (is_numeric($ip_subnet_cidr)) {
			
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
	
			$sql = "SELECT ip_traffic_rule_ip_subnet_id FROM ip_traffic_rule_ip_subnets
					WHERE ip_traffic_rule_ip_subnet_address='".$ip_subnet_address."'
					AND ip_traffic_rule_ip_subnet_cidr='".$ip_subnet_cidr."'
					";
			
			$existing_subnet  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
			//check if this subnet is alreay mapped to this rule
			if (isset($existing_subnet['ip_traffic_rule_ip_subnet_id'])) {
				
				$sql = "SELECT ip_traffic_rule_ip_subnet_map_id FROM ip_traffic_rule_ip_subnet_mapping
						WHERE ip_traffic_rule_id='".$this->_ip_traffic_rule_id."'
						AND ip_traffic_rule_ip_subnet_id='".$existing_subnet."'
						AND ip_traffic_rule_ip_subnet_map_match='".$match."'
						AND ip_traffic_rule_ip_subnet_map_direction='".$direction."'
						";
				
				$existing_map  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
				
				
				if (isset($existing_map['ip_traffic_rule_ip_subnet_map_id'])) {
					
					//already mapped return that
					return $existing_map;
				}
			}
			
			
			if (isset($existing_subnet['ip_traffic_rule_ip_subnet_id'])) {
				
				$ip_traffic_rule_ip_subnet_id = $existing_subnet;
				
			} else {
				
				$data1 = array(
							'ip_traffic_rule_ip_subnet_address'		=> $ip_subnet_address,
							'ip_traffic_rule_ip_subnet_cidr'		=> $ip_subnet_cidr,
				);
				
				$ip_traffic_rule_ip_subnet_id = Zend_Registry::get('database')->insert_single_row('ip_traffic_rule_ip_subnets',$data1,$class,$method);
			}
			
			//now map that subnet to this rule
			$data2 = array(
							'ip_traffic_rule_id'						=> $this->_ip_traffic_rule_id,
							'ip_traffic_rule_ip_subnet_id'				=> $ip_traffic_rule_ip_subnet_id,
							'ip_traffic_rule_ip_subnet_map_match'		=> $match,
							'ip_traffic_rule_ip_subnet_map_direction'	=> $direction,
			);
	
				
			$new_ip_traffic_rule_ip_subnet_map_id = Zend_Registry::get('database')->insert_single_row('ip_traffic_rule_ip_subnet_mapping',$data2,$class,$method);
			
			
			//add this to the variable
			$sql3 = "SELECT iptrism.ip_traffic_rule_ip_subnet_map_id, iptris.ip_traffic_rule_ip_subnet_id, iptris.ip_traffic_rule_ip_subnet_address, iptris.ip_traffic_rule_ip_subnet_cidr, iptrism.ip_traffic_rule_ip_subnet_map_match, iptrism.ip_traffic_rule_ip_subnet_map_direction FROM ip_traffic_rule_ip_subnet_mapping iptrism
					INNER JOIN ip_traffic_rule_ip_subnets iptris ON iptris.ip_traffic_rule_ip_subnet_id=iptrism.ip_traffic_rule_ip_subnet_id
					WHERE iptrism.ip_traffic_rule_ip_subnet_map_id='".$new_ip_traffic_rule_ip_subnet_map_id."'
					";
			
			$ip_traffic_rule_ip_subnet  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql3);

			$this->_ip_traffic_rule_ip_subnets[$new_ip_traffic_rule_ip_subnet_map_id] = $ip_traffic_rule_ip_subnet;
			
			return $new_ip_traffic_rule_ip_subnet_map_id;

		} else {

			throw new exception('cidr mask must be numeric', 8100);
		}

	}
	
	public function map_traffic_protocol_port($ip_protocol, $port_number, $match, $direction)
	{
	
		if ((is_numeric($ip_protocol) && is_numeric($port_number)) && ($direction == 'src' || $direction == 'dst' || $direction == 'none')) {
			
			$trace 		= debug_backtrace();
			$method 	= $trace[0]["function"];
			$class		= get_class($this);
		
			$sql = "SELECT ip_protocol_port_id FROM ip_protocol_ports
					WHERE ip_protocol_id='".$ip_protocol."'
					AND ip_protocol_port_number='".$port_number."'
					AND ip_protocol_port_direction='".$direction."'
					";
		
			$existing_port  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
			//check if this port is already mapped to this rule
			if (isset($existing_port['ip_protocol_port_id'])) {
					
				$sql = "SELECT ip_protocol_port_map_id FROM ip_protocol_port_mapping
						WHERE ip_traffic_rule_id='".$this->_ip_traffic_rule_id."'
						AND ip_protocol_port_id='".$existing_port."'
						AND ip_protocol_port_map_match='".$match."'
						";
					
				$existing_map  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
					
					
				if (isset($existing_map['ip_protocol_port_map_id'])) {
		
					//already mapped return that
					return $existing_map;
				}
			}
		
		
			if (isset($existing_port['ip_protocol_port_id'])) {
					
				$ip_protocol_port_id = $existing_port;
					
			} else {
					
				$data1 = array(
								'ip_protocol_id'				=> $ip_protocol,
								'ip_protocol_port_number'		=> $port_number,
								'ip_protocol_port_direction'	=> $direction,
				);
					
				$ip_protocol_port_id = Zend_Registry::get('database')->insert_single_row('ip_protocol_ports',$data1,$class,$method);
			}
		
			//now map that subnet to this rule
			$data2 = array(
								'ip_traffic_rule_id'			=> $this->_ip_traffic_rule_id,
								'ip_protocol_port_id'			=> $ip_protocol_port_id,
								'ip_protocol_port_map_match'	=> $match,
			);
		
				
			$new_ip_protocol_port_map_id = Zend_Registry::get('database')->insert_single_row('ip_protocol_port_mapping',$data2,$class,$method);
			
			//get the ip_protocol_port_mappings for this rule
			$sql2 = "SELECT ippm.ip_protocol_port_map_id, ipp.ip_protocol_port_number, ipp.ip_protocol_port_direction, ipp.ip_protocol_port_id, ip_pro.ip_protocol_id, ip_pro.ip_protocol_name, ippm.ip_protocol_port_map_match FROM ip_protocol_port_mapping ippm
					INNER JOIN ip_protocol_ports ipp ON ipp.ip_protocol_port_id=ippm.ip_protocol_port_id
					INNER JOIN ip_protocols ip_pro ON ip_pro.ip_protocol_id=ipp.ip_protocol_id
					WHERE ippm.ip_protocol_port_map_id='".$new_ip_protocol_port_map_id."'
					";
			
			$ip_protocol_port_map  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql2);

			$this->_ip_traffic_rule_ip_ports[$new_ip_protocol_port_map_id] = $ip_protocol_port_map;
			
			return $new_ip_protocol_port_map_id;

		} else {
			throw new exception('ip_protocol and port_number must both be numeric and have either src or dst or none', 8101);
		}
	}

}
?>