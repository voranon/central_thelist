<?php 

//exception codes 1700-1799

class thelist_model_ipaddress
{
	private $_ip_address_id;
	private $_ip_address;
	private $_ip_subnet_id;
	private $_ip_subnet_cidr_mask;
	private $_ip_subnet_dotted_decimal_mask;
	private $_dns_records=null;
	private $_connection_filters=null;
	
	private $_monitoring_guids=null;
	
	
	//attributes only filled when mapped
	private $_ip_address_map_id=null;
	private $_mapped_if_id=null;
	private $_ip_address_map_type=null;
	private $_ip_address_map_type_resolved=null;

	public function __construct($ip_address_id)
	{
		$this->_ip_address_id = $ip_address_id;
		

		
		$sql = 	"SELECT ip_a.*, ip_sub.ip_subnet_address, ip_sub.ip_subnet_cidr_mask FROM ip_addresses ip_a
				INNER JOIN ip_subnets ip_sub ON ip_sub.ip_subnet_id=ip_a.ip_subnet_id
				WHERE ip_a.ip_address_id='".$this->_ip_address_id."'
				";
		
		$ip_addresse_detail  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

		$this->_ip_address				= $ip_addresse_detail['ip_address'];
		$this->_ip_subnet_id			= $ip_addresse_detail['ip_subnet_id'];
		$this->_ip_subnet_cidr_mask		= $ip_addresse_detail['ip_subnet_cidr_mask'];
	}
	
	
	public function get_ip_address_id()
	{
		return $this->_ip_address_id;
	}
	public function get_ip_address_map_type()
	{
		return $this->_ip_address_map_type;
	}
	public function get_ip_address_map_type_resolved()
	{
		return $this->_ip_address_map_type_resolved;
	}
	public function get_ip_address_map_id()
	{
		return $this->_ip_address_map_id;
	}
	public function get_ip_address()
	{
		return $this->_ip_address;
	}
	public function get_ip_subnet_id()
	{
		return $this->_ip_subnet_id;
	}
	public function get_ip_subnet_cidr_mask()
	{
		return $this->_ip_subnet_cidr_mask;
	}
	public function get_ip_subnet_dotted_decimal_mask()
	{
		$this->_ip_subnet_dotted_decimal_mask = ereg_replace('/0 ', "0.0.0.0", ereg_replace('/1 ', "128.0.0.0", ereg_replace('/2 ', "192.0.0.0", ereg_replace('/3 ', "224.0.0.0", ereg_replace('/4 ', "240.0.0.0", ereg_replace('/5 ', "248.0.0.0", ereg_replace('/6 ', "252.0.0.0", ereg_replace('/7 ', "254.0.0.0", ereg_replace('/8 ', "255.0.0.0", ereg_replace('/9 ', "255.128.0.0", ereg_replace('/10 ', "255.192.0.0", ereg_replace('/11 ', "255.224.0.0", ereg_replace('/12 ', "255.240.0.0", ereg_replace('/13 ', "255.248.0.0", ereg_replace('/14 ', "255.252.0.0", ereg_replace('/15 ', "255.254.0.0", ereg_replace('/16 ', "255.255.0.0", ereg_replace('/17 ', "255.255.128.0", ereg_replace('/18 ', "/255.255.192.0", ereg_replace('/19 ', "255.255.224.0", ereg_replace('/20 ', "255.255.240.0", ereg_replace('/21 ', "255.255.248.0", ereg_replace('/22 ', "255.255.252.0", ereg_replace('/23 ', "255.255.254.0", ereg_replace('/24 ', "255.255.255.0", ereg_replace('/25 ', "255.255.255.128", ereg_replace('/26 ', "255.255.255.192", ereg_replace('/27 ', "255.255.255.224", ereg_replace('/28 ', "255.255.255.240", ereg_replace('/29 ', "255.255.255.248", ereg_replace('/30 ', "255.255.255.252", ereg_replace('/31 ', "255.255.254.0", ereg_replace('/32 ', "255.255.255.255","/".$this->get_ip_subnet_cidr_mask()." ")))))))))))))))))))))))))))))))));
		
		return $this->_ip_subnet_dotted_decimal_mask;
	}
	
	public function ip_in_use()
	{
		$sql=	"SELECT COUNT(iam.ip_address_map_id) as count FROM ip_address_mapping iam
				INNER JOIN items itm ON itm.item_id=iam.ip_address_map_type
				WHERE iam.ip_address_id='".$this->_ip_address_id."'
				AND itm.item_type='ip_address_map_type'
				AND (itm.item_name='connected' OR itm.item_name='dhcp_range' OR itm.item_name='dhcp_lease' OR itm.item_name='static_assignment')
				";
		
		$ip_in_use  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		if ($ip_in_use == 0) {
			return false;
		} else {
			return true;
		}
	}
	
	
	public function set_ip_address_map_id($if_id)
	{

		$sql = 	"SELECT * FROM ip_address_mapping
				WHERE if_id='".$if_id."'
				AND ip_address_id='".$this->_ip_address_id."'
				";
		
		$ip_address_map_detail= Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		$this->_ip_address_map_id = $ip_address_map_detail['ip_address_map_id'];
		$this->_mapped_if_id = $ip_address_map_detail['if_id'];
		$this->_ip_address_map_type = $ip_address_map_detail['ip_address_map_type'];
		
		$sql = 	"SELECT item_value FROM items 
				WHERE item_id='".$this->_ip_address_map_type."'";
		
		$this->_ip_address_map_type_resolved = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		$this->_ip_address_map_type = $ip_address_map_detail['ip_address_map_type'];
	}

	public function get_dns_records()
	{
		if ($this->_dns_records == null) {
			
			$sql = 	"SELECT id FROM rr r
					WHERE r.data='".$this->_ip_address."'
					";
			
			$ids	= Zend_Registry::get('database')->get_mydns_adapter()->fetchAll($sql);
			
			if (isset($ids['0'])) {
				foreach ($ids as $id) {
					
					$this->_dns_records[$id['id']] = new Thelist_Model_dnsrecord($id['id']);
					
				}
			}
		}
		
		return $this->_dns_records;
	}
	
	public function update_mapped_mapping_type($new_mapping_type_id)
	{
		$sql2 = 	"SELECT item_type FROM items
					WHERE item_id='".$new_mapping_type_id."'
					";
			
		$item_type  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
	
		if ($item_type == 'ip_address_map_type') {
			
			if ($this->_ip_address_map_type != $new_mapping_type_id) {

				$trace  = debug_backtrace();
				$method = $trace[0]["function"];
				$class	= get_class($this);
					
				Zend_Registry::get('database')->set_single_attribute($this->_ip_address_map_id, 'ip_address_mapping', 'ip_address_map_type', $new_mapping_type_id, $class, $method);
			}
	
		} else {
			throw new exception('the new mapping type provided is not an item relating to ip mapping', 1706);
		}
	}
	
	public function get_mapped_if_id()
	{
		return $this->_mapped_if_id;
	}
	
	public function create_dns_record($subdomain, $soa_id, $record_type)
	{				
		
		//simplistic currently should be expanded to include options like ttl and priority
		//but that could also be done from the returned dns object
		$sql = 	"SELECT COUNT(r.id) FROM rr r
				INNER JOIN soa s ON s.id=r.zone
				WHERE r.name='".$subdomain."' 
				AND s.id='".$soa_id.".' 
				AND r.type='".$record_type."'
				AND r.data='".$this->_ip_address_id."'
				";
			
		$exist	= Zend_Registry::get('database')->get_mydns_adapter()->fetchOne($sql);
		
		if ($exist == 0) {
						
			//also make sure this domain / subdomain is not a management fqdn anywhere, we do not allow managment to have multiple records
			$sql = 	"SELECT origin FROM soa
					WHERE id='".$soa_id."' 
					";
			$domain  = substr(Zend_Registry::get('database')->get_mydns_adapter()->fetchOne($sql), 0, -1);
			
			
			$sql = 	"SELECT count(eq_fqdn) FROM equipments
					WHERE eq_fqdn='".$subdomain.".".$domain."'
					";
			
			$management_count  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);

			if ($management_count == 0) {
				
				$trace  = debug_backtrace();
				$method = $trace[0]["function"];
				$class	= get_class($this);
					
				$data = array(
												'sys_userid'					=>  '1',
												'sys_groupid'					=>  '1',
												'sys_perm_user' 				=>	'riud',  
												'sys_perm_group' 				=>	'riud',
												'sys_perm_other' 				=>	'',
												'zone' 							=>	$soa_id,
												'name' 							=>	$subdomain,
												'type'							=> 	$record_type,
												'data'							=> 	$this->_ip_address,
												'aux'							=> 	'0',
												'ttl'							=> 	'86400',
				);
					
				$new_dns_record_id = Zend_Registry::get('database')->insert_single_row('rr',$data,$class,$method);
					
				$this->_dns_records[$new_dns_record_id] = new Thelist_Model_dnsrecord($new_dns_record_id);
					
				return $this->_dns_records[$new_dns_record_id];
				
			} else {
				
				throw new exception('this fqdn is already in use as a management address, these records must be unique', 1703);
				
			}
			
		} else {
				
			throw new exception('dns record already exists', 1702);
				
		}	
	}
	
// 	public function get_mapped_connection_filters()
// 	{
// 		if ($this->_ip_address_map_id != null) {
			
// 			if ($this->_connection_filters == null) {
			
// 				$sql = 	"SELECT connection_queue_filter_id FROM connection_queue_filters
// 						WHERE ip_address_map_id='".$this->_ip_address_map_id."'
// 						";
			
// 				$connection_queue_filter_ids	= Zend_Registry::get('database')->get_mydns_adapter()->fetchAll($sql);
			
// 				if (isset($connection_queue_filter_ids['0'])) {
// 					foreach ($connection_queue_filter_ids as $connection_queue_filter_id) {
			
// 						$this->_connection_filters[$connection_queue_filter_id['connection_queue_filter_id']] = new Thelist_Model_connectionqueuefilter($connection_queue_filter_id['connection_queue_filter_id']);
			
// 					}
// 				}
// 			}
			
// 			return $this->_connection_filters;
			
// 		} else {
			
// 			throw new exception('you cannot request filters for the ip object unless you have mapped it to an interface', 1704);
			
// 		}
// 	}
	
	public function delete_dns_record($record_id)
	{
		if ($this->_dns_records == null) {
			
			$this->get_dns_records();
		}
		
		if (isset($this->_dns_records[$record_id])) {
			
			//check if this is management fqdn anywhere
			$sql = "SELECT COUNT(eq_id) FROM equipments
					WHERE eq_fqdn='".$this->_dns_records[$record_id]->get_subdomain().".".$this->_dns_records[$record_id]->get_domain()."'
					";
			
			$management_count  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
			if ($management_count == 0) {
				
				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);
					
				//delete the eq_api
				Zend_Registry::get('database')->delete_single_row($record_id, 'rr', $class, $method);

				//unset it from this object
				unset($this->_dns_records[$record_id]);
				
			} else {
				
				throw new exception('trying to delete dns record but it is used as management for equipment', 1701);
				
			}
			
		} else {
			
			throw new exception('trying to delete dns record that does not belong to this ip address', 1700);
			
		}
	}
	
	public function update_default_monitoring()
	{
		if ($this->_monitoring_guids == null) {
				
			$this->get_monitoring_guids;
				
		}
		
		//if still null
		if ($this->_monitoring_guids == null) {
	
			$mon_guid = $this->create_new_monitoring_guid();
	
		} else {
	
			$mon_guid = array_shift(array_values($this->_monitoring_guids));
	
		}
	
		$mon_guid->map_default_datasources();
	
	}
	
	public function create_new_monitoring_guid()
	{
		$data = array(
						'table_name'							=> 'ip_addresses',
						'primary_key'							=> $this->_ip_address_id,
		);
	
		$trace 		= debug_backtrace();
		$method 	= $trace[0]["function"];
		$class		= get_class($this);
	
		$new_monitoring_guid_id = Zend_Registry::get('database')->insert_single_row('monitoring_guids',$data,$class,$method);
		$monitoring_guid_obj = $this->_monitoring_guids[$new_monitoring_guid_id] = new Thelist_Model_monitoringguid($new_monitoring_guid_id);
		return $monitoring_guid_obj;
	}
	
	public function get_monitoring_guid($monitoring_guid_id)
	{
		if ($this->_monitoring_guids == null) {
			
			$this->get_monitoring_guids;
			
		}
		
		return $this->_monitoring_guids[$monitoring_guid_id];
	
	}
	public function get_monitoring_guids()
	{
		
		if ($this->_monitoring_guids == null) {
				
			$sql2=	"SELECT * FROM monitoring_guids
					WHERE table_name='ip_addresses'
					AND primary_key='".$this->_ip_address_id."'
					";
			
			$monitoring_guids  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
			if (isset($monitoring_guids['0'])) {
				foreach($monitoring_guids as $monitoring_guid){
					$this->_monitoring_guids[$monitoring_guid['monitoring_guid_id']] = new Thelist_Model_monitoringguid($monitoring_guid['monitoring_guid_id']);
				}
			}	
		}
		
		return $this->_monitoring_guids;
	}
	
	public function remove_ip_route_gateway_maps()
	{
		//this will remove all gateway entries that use this ip address map as their gateway
		//if this is the last gateway then we remove the route as well.
		//this is tricky because those routes belong to equipment that has nothing to do with 
		//the interface that this ip is currently mapped to, but if we are removing an ip address map
		//it is better that the database does not have route entries with no gateways
		
		if ($this->_ip_address_map_id != null) {
			
			$sql = 	"SELECT iprg.ip_route_id, iprg.ip_route_gateway_id FROM ip_address_mapping ipam
					INNER JOIN ip_route_gateways iprg ON iprg.ip_address_map_id=ipam.ip_address_map_id
					WHERE ipam.ip_address_map_id='".$this->_ip_address_map_id."'
					";
			
			$ip_routes	= Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			if (isset($ip_routes['0'])) {
				
				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);
				
				foreach ($ip_routes as $ip_route) {
					
					//find out if there is more than a single gateway entry
					$sql2 = 	"SELECT COUNT(ip_route_gateway_id) AS other_maps FROM ip_route_gateways
								WHERE ip_address_map_id!='".$this->_ip_address_map_id."'
								AND ip_route_id='".$ip_route['ip_route_id']."'
								";
					
					$count	= Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);

					//we need to remove the gateway map for sure, so we remove it.
					Zend_Registry::get('database')->delete_single_row($ip_route['ip_route_gateway_id'], 'ip_route_gateways', $class, $method);
					
					//check if there are any other gateways, if not then get rid of the route as well
					if ($count == 0) {
						Zend_Registry::get('database')->delete_single_row($ip_route['ip_route_id'], 'ip_routes', $class, $method);
					}
				}
			}
		} else {
			throw new exception('please map the ip address to an interface before using this method', 1705);
		}
	}
}
?>