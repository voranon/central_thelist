<?php 

class thelist_model_dnsrecord
{
	private $_record_id;
	private $_rr_sys_userid;
	private $_rr_sys_groupid;
	private $_rr_sys_perm_user;
	private $_rr_sys_perm_group;
	private $_rr_sys_perm_other;
	private $_rr_zone;
	private $_rr_name;
	private $_rr_type;
	private $_rr_data;
	private $_rr_aux;
	private $_rr_ttl;
	private $_soa_origin;
	private $_soa_ns;
	private $_soa_mbox;
	private $_soa_serial;
	private $_soa_refresh;
	private $_soa_retry;
	private $_soa_expire;
	private $_soa_minimum;
	private $_soa_ttl;
	private $_soa_active;
	private $_soa_xfer;
	
	
	public function __construct($record_id)
	{
		$this->_record_id = $record_id;
		

		
		$sql = 	"SELECT * FROM rr r
				INNER JOIN soa s ON s.id=r.zone
				WHERE r.id='".$this->_record_id."'
				";
		
		$dns_record_detail  = Zend_Registry::get('database')->get_mydns_adapter()->fetchRow($sql);

		$this->_rr_sys_userid				= $dns_record_detail['sys_userid'];
		$this->_rr_sys_groupid				= $dns_record_detail['sys_groupid'];
		$this->_rr_sys_perm_user			= $dns_record_detail['sys_perm_user'];
		$this->_rr_sys_perm_group			= $dns_record_detail['sys_perm_group'];
		$this->_rr_sys_perm_other			= $dns_record_detail['sys_perm_other'];
		$this->_rr_zone						= $dns_record_detail['zone'];
		$this->_rr_name						= $dns_record_detail['name'];
		$this->_rr_type						= $dns_record_detail['type'];
		$this->_rr_data						= $dns_record_detail['data'];
		$this->_rr_aux						= $dns_record_detail['aux'];
		$this->_rr_ttl						= $dns_record_detail['ttl'];
		$this->_soa_origin					= $dns_record_detail['origin'];
		$this->_soa_ns						= $dns_record_detail['ns'];
		$this->_soa_mbox					= $dns_record_detail['mbox'];
		$this->_soa_serial					= $dns_record_detail['serial'];
		$this->_soa_refresh					= $dns_record_detail['refresh'];
		$this->_soa_retry					= $dns_record_detail['retry'];
		$this->_soa_expire					= $dns_record_detail['expire'];
		$this->_soa_minimum					= $dns_record_detail['minimum'];
		//$this->_soa_ttl						= $dns_record_detail['soa_ttl'];
		$this->_soa_active					= $dns_record_detail['active'];
		$this->_soa_xfer					= $dns_record_detail['xfer'];

	
	}
	
	
	public function get_record_id()
	{
		return $this->_record_id;
	}
	
	public function get_subdomain()
	{
		return $this->_rr_name;
	}
	
	public function get_domain()
	{
		return substr($this->_soa_origin, 0, -1);
	}
	
	public function get_record_type()
	{
		return $this->_rr_type;
	}
	
	public function is_unique()
	{
		$sql = 	"SELECT count(id) FROM rr r
				WHERE r.name='".$this->_rr_name."'
				AND r.zone='".$this->_rr_zone."'
				AND r.type='".$this->_rr_type."'
				";
		
		$dns_count  = Zend_Registry::get('database')->get_mydns_adapter()->fetchOne($sql);
		
		if ($dns_count == 1) {
			
			return true;
			
		} else {
			
			return false;
			
		}
	}
	


}



?>