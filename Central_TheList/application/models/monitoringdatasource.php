<?php 

//by martin
class thelist_model_monitoringdatasource
{
	private $_monitoring_ds_id;
	private $_device_function_id;
	private $_rrd_ds_name;
	private $_rrd_step;
	private $_rrd_ds_type_counter;
	private $_rrd_heartbeat;
	private $_rrd_max_value;
	private $_rrd_min_value;
	private $_data_source_description;
	
	//only filled when part of a map
	private $_rratypes=null;
	private $_monitoring_guid_ds_map_id=null;
	private $_monitoring_poller_command_cache_id=null;
	private $_created_on_disk=null;
	private $_regex=null;
	private $_activate=null;
	private $_deactivate=null;
	
	public function __construct($monitoring_ds_id)
	{
		$this->_monitoring_ds_id = $monitoring_ds_id;
		

		
		$mon_ds = Zend_Registry::get('database')->get_monitoring_data_sources()->fetchRow('monitoring_ds_id='.$this->_monitoring_ds_id);

		$this->_device_function_id				= $mon_ds['device_function_id'];
		$this->_rrd_ds_name						= $mon_ds['rrd_ds_name'];
		$this->_rrd_step						= $mon_ds['rrd_step'];
		$this->_rrd_ds_type_counter				= $mon_ds['rrd_ds_type_counter'];
		$this->_rrd_heartbeat					= $mon_ds['rrd_heartbeat'];
		$this->_rrd_max_value					= $mon_ds['rrd_max_value'];
		$this->_rrd_min_value					= $mon_ds['rrd_min_value'];
		$this->_data_source_description			= $mon_ds['data_source_description'];
		
		$sql=	"SELECT * FROM monitoring_rra_type_mapping
				WHERE monitoring_ds_id='".$this->_monitoring_ds_id."'
				";
		
		$rra_types  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		//get all the rras that belong to this datasource
		if (isset($rra_types['0'])) {
		
			foreach($rra_types as $rra_type){
				$this->_rratypes[$rra_type['monitoring_rra_type_map_id']] = new Thelist_Model_monitoringrratype($rra_type['monitoring_rra_type_id']);
				$this->_rratypes[$rra_type['monitoring_rra_type_map_id']]->fill_mapping_data($rra_type['monitoring_rra_type_map_id']);
			}
		}

	}
	
	
	
	public function set_monitoring_guid_ds_map_id($monitoring_guid_ds_map_id)
	{
		$this->_monitoring_guid_ds_map_id = $monitoring_guid_ds_map_id;
	}
	public function set_monitoring_poller_command_cache_id($monitoring_poller_command_cache_id)
	{
		$this->_monitoring_poller_command_cache_id = $monitoring_poller_command_cache_id;
	}
	public function set_created_on_disk($created_on_disk)
	{
		$this->_created_on_disk = $created_on_disk;
	}
	public function set_regex($regex)
	{
		$this->_regex = $regex;
	}
	public function set_activate($activate)
	{
		$this->_activate = $activate;
	}
	public function set_deactivate($deactivate)
	{
		$this->_deactivate = $deactivate;
	}
	
	public function fill_mapping_data($monitoring_guid_ds_map_id)
	{
		$sql = "SELECT * FROM monitoring_guid_ds_mapping
				WHERE monitoring_guid_ds_map_id='".$monitoring_guid_ds_map_id."'
				";
				
		$datasourcemap  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		if ($datasourcemap['monitoring_ds_id'] == $this->_monitoring_ds_id) {
			
			$this->_monitoring_poller_command_cache_id = $datasourcemap['monitoring_poller_command_cache_id'];
			$this->_created_on_disk = $datasourcemap['created_on_disk'];
			$this->_regex = $datasourcemap['regex'];
			$this->_activate = $datasourcemap['activate'];
			$this->_deactivate = $datasourcemap['deactivate'];
			
		} else {
			
			throw new exception('the provided monitoring_guid_ds_map_id is not based on this datasource');
			
		}

	}
	
	public function get_rratypes()
	{
		return $this->_rratypes;
	}
	public function get_rratype($monitoring_rra_type_id)
	{
		return $this->_rratypes[$monitoring_rra_type_id];
	}
	
	public function get_monitoring_ds_id()
	{
		return $this->_monitoring_ds_id;
	}
	public function get_device_function_id()
	{
		return $this->_device_function_id;
	}
	public function get_rrd_ds_name()
	{
		return $this->_rrd_ds_name;
	}
	public function get_rrd_step()
	{
		return $this->_rrd_step;
	}
	public function get_rrd_ds_type_counter()
	{
		return $this->_rrd_ds_type_counter;
	}
	public function get_rrd_heartbeat()
	{
		return $this->_rrd_heartbeat;
	}
	public function get_rrd_max_value()
	{
		return $this->_rrd_max_value;
	}
	public function get_rrd_min_value()
	{
		return $this->_rrd_min_value;
	}
	public function get_data_source_description()
	{
		return $this->_data_source_description;
	}
	public function get_activate()
	{
		return $this->_activate;
	}
	public function get_deactivate()
	{
		return $this->_deactivate;
	}
	public function get_monitoring_guid_ds_map_id()
	{
		return $this->_monitoring_guid_ds_map_id;
	}
	public function get_monitoring_poller_command_cache_id()
	{
		return $this->_monitoring_poller_command_cache_id;
	}
	public function get_created_on_disk()
	{
		return $this->_created_on_disk;
	}
	public function get_regex()
	{
		return $this->_regex;
	}
	
	public function map_new_rratype($monitoring_rra_type_id)
	{
	
		$new_rra_type = new Thelist_Model_monitoringrratype($monitoring_rra_type_id);
	
		
		//lets check if we alrady have an active rratype that is online
		if ($this->_rratypes != null) {
			foreach ($this->_rratypes as $rratype) {
	
				if ($rratype->get_monitoring_rra_type_id() == $monitoring_rra_type_id ) {
	
					return $rratype;
	
				}
			}
		} 
	
		$data = array(
							'monitoring_rra_type_id'		=> $monitoring_rra_type_id,
							'monitoring_ds_id'				=> $this->_monitoring_ds_id,

		);
		
		$trace 		= debug_backtrace();
		$method 	= $trace[0]["function"];
		$class		= get_class($this);
		
		$new_monitoring_rra_type_map_id = Zend_Registry::get('database')->insert_single_row('monitoring_rra_type_mapping',$data,$class,$method);
		
		$this->_rratypes[$new_monitoring_rra_type_map_id] = new Thelist_Model_monitoringrratype($monitoring_rra_type_id);
		$this->_rratypes[$new_monitoring_rra_type_map_id]->fill_mapping_data($new_monitoring_rra_type_map_id);
		
		return $this->_rratypes[$new_monitoring_rra_type_map_id];
	
	}

}
?>