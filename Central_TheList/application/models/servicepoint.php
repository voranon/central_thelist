<?php 

class thelist_model_servicepoint
{
	private $_service_point_id;
	private $_service_point_name;
	private $_service_point_interfaces=null;
	private $_service_point_unused_interfaces=null;
	private $_service_point_in_use_interfaces=null;
	
	public function __construct($service_point_id)
	{
		$this->_service_point_id = $service_point_id;
		
		$service_point = Zend_Registry::get('database')->get_service_points()->fetchRow("service_point_id=".$this->_service_point_id);
		
		$this->_service_point_name		= $service_point['service_point_name'];
		
	}
	
	public function get_service_point_name()
	{
		return $this->_service_point_name;
	}
	
	public function get_service_point_id()
	{
		return $this->_service_point_id;
	}
	
	
	public function get_service_point_interfaces()
	{
		
		if ($this->_service_point_interfaces == null) {
			$interfaces = Zend_Registry::get('database')->get_interfaces()->fetchAll("service_point_id=".$this->_service_point_id);
			
			if (isset($interfaces['0'])) {
				
				foreach ($interfaces as $interface) {
					
					$this->_service_point_interfaces[$interface['if_id']]	= new Thelist_Model_equipmentinterface($interface['if_id']);
					
				}
			}
		}
			
		return $this->_service_point_interfaces;
	}
	
	public function get_service_point_unused_interfaces()
	{
		if ($this->_service_point_unused_interfaces == null) {
			
			$sql = "SELECT i.if_id, COUNT(i.if_id) AS number_of_connections FROM interfaces i
					INNER JOIN interface_connections ic ON (ic.if_id_a=i.if_id OR ic.if_id_b=i.if_id)
					WHERE service_point_id='".$this->_service_point_id."'
					GROUP BY i.if_id";
			
			$if_connection_counts = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
			if (isset($if_connection_counts['0'])) {
					
				foreach ($if_connection_counts as $if_connection_count) {
	
					if ($if_connection_count['number_of_connections'] == '1') {
						
						$this->_service_point_unused_interfaces[$if_connection_count['if_id']]	= new Thelist_Model_equipmentinterface($if_connection_count['if_id']);
						
					}
				}
			}
		}
			
			return $this->_service_point_unused_interfaces;
	}
	
	public function get_service_point_in_use_interfaces()
	{
		if ($this->_service_point_in_use_interfaces == null) {
			
		
			$sql = "SELECT i.if_id, COUNT(i.if_id) AS number_of_connections FROM interfaces i
						INNER JOIN interface_connections ic ON (ic.if_id_a=i.if_id OR ic.if_id_b=i.if_id)
						WHERE service_point_id='".$this->_service_point_id."'
						GROUP BY i.if_id";
		
			$if_connection_counts = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
	
			if (isset($if_connection_counts['0'])) {
		
				foreach ($if_connection_counts as $if_connection_count) {
		
					if ($if_connection_count['number_of_connections'] > '1') {
							
						$this->_service_point_in_use_interfaces[$if_connection_count['if_id']]	= new Thelist_Model_equipmentinterface($if_connection_count['if_id']);
							
					}
				}
			}
		}
			
		return $this->_service_point_in_use_interfaces;

	}
}
?>