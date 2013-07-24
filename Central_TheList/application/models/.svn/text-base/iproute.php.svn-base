<?php 

//exception codes 2600-2699

class thelist_model_iproute
{
	private $database;
	
	private $_ip_route_id;
	private $_eq_id;
	private $_ip_subnet_id;
	private $_ip_route_gateways=null;
	private $_gateway_equipment=null;

	public function __construct($ip_route_id)
	{
		

		$this->_ip_route_id = $ip_route_id;

		$sql = 	"SELECT * FROM ip_routes 
				WHERE ip_route_id='".$this->_ip_route_id."'
				";
		
		$ip_route_detail  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

		$this->_eq_id					= $ip_route_detail['eq_id'];
		$this->_ip_subnet_id			= $ip_route_detail['ip_subnet_id'];

	}
	
	public function get_ip_route_gateways()
	{
		if ($this->_ip_route_gateways == null) {
			
			$sql = 	"SELECT * FROM ip_route_gateways
					WHERE ip_route_id='".$this->_ip_route_id."'
					";
			
			$ip_route_gateway_details  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
				
			if (isset($ip_route_gateway_details['0'])) {
			
				foreach($ip_route_gateway_details as $gateway) {
			
					$sql = "SELECT * FROM ip_address_mapping
							WHERE ip_address_map_id='".$gateway['ip_address_map_id']."'
							";
			
					$ip_map_detail  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
					$ip_address	= new Thelist_Model_ipaddress($ip_map_detail['ip_address_id']);
					$ip_address->set_ip_address_map_id($ip_map_detail['if_id']);
			
					$this->_ip_route_gateways[$gateway['ip_route_gateway_id']]['ip_address'] 			= $ip_address;
					$this->_ip_route_gateways[$gateway['ip_route_gateway_id']]['cost'] 					= $gateway['ip_route_gateway_cost'];
					$this->_ip_route_gateways[$gateway['ip_route_gateway_id']]['ip_route_gateway_id'] 	= $gateway['ip_route_gateway_id'];
			
				}
			}
		}
		
		return $this->_ip_route_gateways;
	}

	public function get_ip_route_id()
	{
		return $this->_ip_route_id;
	}
	
	public function get_ip_subnet_id()
	{
		//cheaper function than the subnet object if all we need is the subnet id
		return $this->_ip_subnet_id;
	}
	
	public function get_ip_subnet()
	{
		return new Thelist_Model_ipsubnet($this->_ip_subnet_id);
	}
	
	public function get_gateway_equipment()
	{
		//public get
		if ($this->_gateway_equipment == null) {
			$this->refresh_gateway_equipment();
		}
		
		return $this->_gateway_equipment;
	}

	public function number_of_gateways()
	{
		//a route must have atleast one gateway to be valid
		//so this allows any modifying class to check if it should just remove a gateway of if it must remove the entire route
		
		if ($this->_ip_route_gateways == null) {
			$this->get_ip_route_gateways();
		}
		
		$number_of_gateways	= count($this->_ip_route_gateways);
	
		return $number_of_gateways;
	}
	
	public function remove_ip_route_gateway($ip_route_gateway_id)
	{
		//removing a gateway from a route is tricky.
		//if all gateways are removed the route is no longer valid and since a class should always be valid (otherwise we get data anomelies)
		if ($this->_ip_route_gateways == null) {
			$this->get_ip_route_gateways();
		}
		
		if (isset($this->_ip_route_gateways[$ip_route_gateway_id])) {
			
			//a route must have atleast one gateway to be valid
			//so we check if there is more than one gateway
			$number_of_gateways	= $this->number_of_gateways();
			
			if ($number_of_gateways > 1) {
				
				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);
				//then remove the route
				Zend_Registry::get('database')->delete_single_row($ip_route_gateway_id, 'ip_route_gateways', $class, $method);
				
				//unset it from here
				unset($this->_ip_route_gateways[$ip_route_gateway_id]);
				
			} else {
				throw new exception("you are removing ip_route_gateway_id: ".$ip_route_gateway_id." from ip_route_id: ".$this->_ip_route_id.", but this is the last gateway, if it is removed the route becomes invalid, please use method number_of_gateways to determine if you should remove route, use remove_ip_route method in equipments class, or just a gateway", 2601);
			}
			
			//refresh any result for gateway equipment, if there is already something there
			if ($this->_gateway_equipment != null) {
				$this->refresh_gateway_equipment();
			}
		} else {
			throw new exception("you are removing ip_route_gateway_id: ".$ip_route_gateway_id." from ip_route_id: ".$this->_ip_route_id.", but this route does not have that gateway ", 2600);
		}
	}
	
	public function add_ip_route_gateway($ip_address_map_id, $cost)
	{
		if ($this->_ip_route_gateways == null) {
			$this->get_ip_route_gateways();
		}
		
		if ($this->_ip_route_gateways != null) {
			foreach ($this->_ip_route_gateways as $current_gateway) {
				
				if ($current_gateway['ip_address']->get_ip_address_map_id() == $ip_address_map_id && $current_gateway['cost'] == $cost) {
					
					//it already exists so we return the existing
					return $current_gateway;
				}
			}
		}
		
		//setup the route
		$data = array(
						'ip_route_id'    					=>  $this->_ip_route_id,
						'ip_address_map_id'   				=>  $ip_address_map_id,
						'ip_route_gateway_cost'   			=>  $cost,
		);

		$ip_route_gateway_id = Zend_Registry::get('database')->insert_single_row('ip_route_gateways',$data,$class,$method);
		
		
		$sql = 		"SELECT * FROM ip_address_mapping
					WHERE ip_address_map_id='".$ip_route_gateway_id."'
					";

		$ip_map_detail  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

		$ip_address	= new Thelist_Model_ipaddress($ip_map_detail['ip_address_id']);
		$ip_address->set_ip_address_map_id($ip_map_detail['if_id']);

		$this->_ip_route_gateways[$gateway['ip_route_gateway_id']]['ip_address'] 			= $ip_address;
		$this->_ip_route_gateways[$gateway['ip_route_gateway_id']]['cost'] 					= $gateway['ip_route_gateway_cost'];
		$this->_ip_route_gateways[$gateway['ip_route_gateway_id']]['ip_route_gateway_id'] 	= $gateway['ip_route_gateway_id'];
		
		return $this->_ip_route_gateways[$gateway['ip_route_gateway_id']];
	}
	
	private function refresh_gateway_equipment()
	{
		
		//when making changes to gateways (adding or deleting) we need to be able to update the 
		//gateway equipment
		//if the normal function just checks for =null then that would not happen, this way we can force it
		
		//no array set check needed there must ALWAYS be a gateway
		foreach($this->get_ip_route_gateways() as $gateway) {
		
			$sql 	= 	"SELECT DISTINCT(i.eq_id) AS gateway_eq FROM ip_address_mapping iam
						INNER JOIN interfaces i ON i.if_id=iam.if_id
						INNER JOIN ip_routes ir ON ir.eq_id=i.eq_id
						WHERE iam.ip_address_map_id='".$gateway['ip_address']->get_ip_address_map_id()."'
						";
		
			$eq_id  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
			if (isset($eq_id['0'])) {
				foreach ($eq_id['gateway_eq'] as $equipment_id) {
					$this->_gateway_equipment[]	= new Thelist_Model_equipments($equipment_id);
		
				}
			}
		}
	}
}
?>