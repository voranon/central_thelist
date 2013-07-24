<?php 


//by martin
class thelist_model_monitoringguid
{
	private $_monitoring_guid_id;
	private $_table_name;
	private $_primary_key;
	private $_datasources;
	

	public function __construct($monitoring_guid_id)
	{
		$this->_monitoring_guid_id = $monitoring_guid_id;
		

		$this->_time				= Zend_Registry::get('time');
		
		$mon_guid = Zend_Registry::get('database')->get_monitoring_guids()->fetchRow('monitoring_guid_id='.$this->_monitoring_guid_id);

		$this->_table_name				= $mon_guid['table_name'];
		$this->_primary_key				= $mon_guid['primary_key'];
		
		
		$sql=	"SELECT * FROM monitoring_guid_ds_mapping
				WHERE monitoring_guid_id='".$this->_monitoring_guid_id."'
				";
		
		$datasources  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
				
		//get all the ips that belong to this vlan
		if (isset($datasources['0'])) {
				
			foreach($datasources as $datasource){
					
				$this->_datasources[$datasource['monitoring_ds_id']] = new Thelist_Model_monitoringdatasource($datasource['monitoring_ds_id']);
				$this->_datasources[$datasource['monitoring_ds_id']]->fill_mapping_data($datasource['monitoring_guid_ds_map_id']);
					
			}
		}
	}
	
	public function get_monitoring_guid_id()
	{
		return $this->_monitoring_guid_id;		
	}
	public function get_table_name()
	{
		return $this->_table_name;
	}
	public function get_primary_key()
	{
		return $this->_primary_key;
	}
	public function get_datasource($monitoring_ds_id)
	{
		return $this->_datasources[$monitoring_ds_id];
	}
	public function get_datasources()
	{
		return $this->_datasources;
	}
	
	public function map_default_datasources()
	{
		if ($this->_table_name == 'equipments') {
			
			$sql=	"SELECT mrtm.* FROM monitoring_equipment_type_default_rra_mapping meqtdrm
					INNER JOIN equipments e ON e.eq_type_id=meqtdrm.eq_type_id
					INNER JOIN monitoring_rra_type_mapping mrtm ON mrtm.monitoring_rra_type_map_id=meqtdrm.monitoring_rra_type_map_id
					INNER JOIN monitoring_rra_types mrt ON mrt.monitoring_rra_type_id=mrtm.monitoring_rra_type_id
					WHERE e.eq_id='".$this->_primary_key."'
					";
			
			$maps  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);

		} elseif($this->_table_name == 'interfaces') {
			
			$sql=	"SELECT mrtm.* FROM monitoring_interface_type_default_rra_mapping miftdrm
					INNER JOIN interfaces i ON i.if_type_id=miftdrm.if_type_id
					INNER JOIN monitoring_rra_type_mapping mrtm ON mrtm.monitoring_rra_type_map_id=miftdrm.monitoring_rra_type_map_id
					INNER JOIN monitoring_rra_types mrt ON mrt.monitoring_rra_type_id=mrtm.monitoring_rra_type_id
					WHERE i.if_id='".$this->_primary_key."'
					";
				
			$maps  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			
		} elseif($this->_table_name == 'ip_addresses') {
			
			//just ping ips from the core
			$new_datasource = $this->map_new_datasource(14, $this->_time->get_current_date_time_mysql_format());
			$new_datasource->map_new_rratype(1);
			$new_datasource->map_new_rratype(2);

		}
		
		//if this is a sql query that returns rows
		if (isset($maps['0'])) {
		
			foreach($maps as $map) {

				$new_datasource = $this->map_new_datasource($map['monitoring_ds_id'], $this->_time->get_current_date_time_mysql_format());
				$new_datasource->map_new_rratype($map['monitoring_rra_type_id']);
					
			}
		}
	
	}

	public function map_new_datasource($monitoring_ds_id, $activate, $deactivate=null)
	{
		
		$new_data_source = new Thelist_Model_monitoringdatasource($monitoring_ds_id);
		
		$device_function = new Thelist_Model_devicefunction($new_data_source->get_device_function_id());
		
		//check that the tables match up
		if ($device_function->get_device_function_parameter_table_name() != $this->_table_name) {
			
			throw new exception('the table used by the device function supporting the datasource is not the same as the monitoring guid table');			
			
		}
		
		//lets check if we alrady have an active datasource that is online
		if ($this->_datasources != null) {
			foreach ($this->_datasources as $datasource) {

				if ($datasource->get_monitoring_ds_id() == $monitoring_ds_id && strtotime($datasource->get_activate()) < time() && (strtotime($datasource->get_deactivate()) > time() || $datasource->get_deactivate() == null)) {

					return $datasource;
				
				}
			}
		}
		
		$command_generator = new Thelist_Model_devicecommandgenerator();
		$device_function_obj = new Thelist_Model_devicefunction($new_data_source->get_device_function_id());
		
		$command_xml = new SimpleXMLElement($command_generator->get_commands_in_xml($device_function_obj, $this->_primary_key));
		$source_eq = $command_generator->resolve_source_eq($this->_table_name, $this->_primary_key);

		$command_regexs_xml = $command_xml->xpath('/equipment_commands/command_element');

		$api_id = $command_regexs_xml[0]->xpath('api_id');
	
		$monitoring_poller_cache_id = $this->new_poller_cache_item($source_eq, "$api_id[0]", $device_function_obj);

		$return_array = $this->new_poller_command_cache($monitoring_poller_cache_id, $command_regexs_xml);

		$data = array(
		
							'monitoring_guid_id'					=> $this->_monitoring_guid_id,
							'monitoring_ds_id'						=> $monitoring_ds_id,
							'monitoring_poller_command_cache_id'	=> $return_array['monitoring_poller_command_cache_id'],
							'regex'									=> $return_array['regex'],
							'activate'								=> $activate,
							'deactivate'							=> $deactivate,
		
		);

		$trace 		= debug_backtrace();
		$method 	= $trace[0]["function"];
		$class		= get_class($this);
		
		$monitoring_guid_ds_map_id = Zend_Registry::get('database')->insert_single_row('monitoring_guid_ds_mapping',$data,$class,$method);
		
		$sql = "select * from monitoring_guid_ds_mapping";
		
		$source_eq_detail  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);

		$this->_datasources[$monitoring_ds_id] = new Thelist_Model_monitoringdatasource($monitoring_ds_id);
		$this->_datasources[$monitoring_ds_id]->fill_mapping_data($monitoring_guid_ds_map_id);
		
		return $this->_datasources[$monitoring_ds_id];

	}
	
	private function new_poller_cache_item($source_eq, $api_id, $device_function_obj, $seconds_between_polls=60)
	{

		if ($device_function_obj->get_device_function_id() == 65) {
			//if the device function is 65 that implies that the poll is not being done from the client but from a central device
			//i.e. pinging from core to edge where the edge device cannot perform a ping (receivers, cpe routers etc.)
			
			//poller 2 is the central ping device
			return 2;
				
			
		} else {
			
			//use the database to find the correct poller
			
			$sql=	"SELECT e.*, eauth.auth_type, eauth.auth_value, a.api_name, ea.eq_api_id FROM equipments e
								INNER JOIN equipment_apis ea ON ea.eq_id=e.eq_id
								INNER JOIN apis a ON a.api_id=ea.api_id
								INNER JOIN equipment_auths eauth ON eauth.eq_api_id=ea.eq_api_id
								WHERE ea.api_id='".$api_id."'
								AND e.eq_id='".$source_eq->get_eq_id()."'
								";
			
			$source_eq_detail  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
			$auth_obj = new Thelist_Model_deviceauthenticationcredential();
			$auth = $auth_obj->fill_from_eq_api_id($source_eq_detail['eq_api_id']);
				
			
			//test if this already exists in the poller cache
				
			$sql2=	"SELECT monitoring_poller_cache_id FROM monitoring_poller_cache
								WHERE seconds_between_polls='".$seconds_between_polls."'
								AND fqdn='".$source_eq->get_eq_fqdn()."'
								AND api_name='".$source_eq_detail['api_name']."'
								";
				
			$existing_poller  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
			
			if ($existing_poller == '') {
			
				$data = array(
					
											'seconds_between_polls'						=> $seconds_between_polls,
											'fqdn'										=> $source_eq->get_eq_fqdn(),
											'api_name'									=> $auth_obj->get_device_api_name(),
											'username'									=> $auth_obj->get_device_username(),
											'password'									=> $auth_obj->get_device_password(),
											'enable_password'							=> $auth_obj->get_device_enablepassword(),
					
				);
					
				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);
					
				return Zend_Registry::get('database')->insert_single_row('monitoring_poller_cache',$data,$class,$method);
			
			
			
			} else {
			
				return $existing_poller;
					
			}
		}
	}
	
	private function new_poller_command_cache($monitoring_poller_cache_id, $command_regexs_xml)
	{
		
			foreach($command_regexs_xml as $command){
			
			$device_command = $command->xpath('device_command');
			$device_exe_order = $command->xpath('command_order_number');
			
			$sql = 	"SELECT monitoring_poller_command_cache_id FROM monitoring_poller_command_cache
					WHERE monitoring_poller_cache_id='".$monitoring_poller_cache_id."'
					AND command='".$device_command['0']."'
					AND command_exe_order='".$device_exe_order['0']."'
					";
			
			$existing_command  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
			if ($existing_command == '') {
				
				$data = array(
				
											'monitoring_poller_cache_id'			=> $monitoring_poller_cache_id,
											'command'								=> $device_command['0'],
											'command_exe_order'						=> $device_exe_order['0'],
				
				);
				
				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);
				
				$monitoring_poller_command_cache_id = Zend_Registry::get('database')->insert_single_row('monitoring_poller_command_cache',$data,$class,$method);

			} else {
				
				$monitoring_poller_command_cache_id = $existing_command;
				
			}
			

			if ($device_exe_order['0'] == '0') {
			
				$regex = $command->xpath('regex/command_regex');
				
				return array("monitoring_poller_command_cache_id" => "$monitoring_poller_command_cache_id", "regex" => "$regex[0]");
					
			}

		}

	}
	
	
}
?>