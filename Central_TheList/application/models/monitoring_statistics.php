<?php 

require_once APPLICATION_PATH.'/models/equipments.php';
require_once APPLICATION_PATH.'/models/monitoring_guids.php';

//by martin
class thelist_model_monitoringstatistic
{

	private $database;
	private $_time;
	private $_xml_store_repo_request=null;
	private $_tracker_0;
	private $_tracker_array_1=array();
	private $_tracker_array_2=array();
	private $_return_xml;
	private $_return_array=array();
	
	public function __construct()
	{


		$this->_time			= Zend_Registry::get('time');
	}
	
	
	public function get_single_graph_url($monitoring_guid_id, $datasource_name, $rra_name, $end_time, $start_time, $return_format)
	{
		$monitoringguid = new monitoringguid($monitoring_guid_id);
		
		//$this->create_xml_for_storage_repo($monitoringguid->get_monitoring_guid_id(), $monitoringguid()->get_datasource($monitoring_ds_id)->get_rrd_ds_name(), $monitoringguid()->get_datasource($monitoring_ds_id)->get_rratype($monitoring_rra_type_id)->get_consolidation_function(), $start_time, $end_time, 'add');
		$this->create_xml_for_storage_repo($monitoringguid->get_monitoring_guid_id(), $datasource_name, $rra_name, $start_time, $end_time, 'add');
		
		$this->create_xml_for_storage_repo(null, null, null, null, null, 'finish');
		
		$storageclient = new SoapClient("http://storage-repo.belairinternet.com/wsdl/data_request_service.wsdl",array("trace" => 1, "exceptions" => 1));
		$return_data = $storageclient->submitMonitorData($this->_xml_store_repo_request->saveXML());
		
		$this->write_image_files_in_xml_response($return_data, $return_format);
		
		if ($return_format == 'array') {
		
			return $this->_return_array;
		
		} elseif ($return_format == 'xml') {
		
			return $this->_return_xml;
				
		}
		
		return $this->write_image_files_in_xml_response($return_data, $return_format);
	}

	public function get_all_graphs_from_eq_id($eq_id, $start_time, $end_time, $return_format)
	{
		//$return_format can be array or xml	
		$equipment = new equipments($eq_id);
		
		//$eq_guids = $equipment->get_monitoring_guids();

		foreach ($equipment->get_interfaces() as $interface) {
		
			$if_guids = $interface->get_monitoring_guids();
				
				if (isset($if_guids)) {
					
					foreach ($if_guids as $if_guid) {
						
						$this->create_xml_for_storage_repo($if_guid->get_monitoring_guid_id(), null, null, null, null, 'add');
						
						$if_datasources = $if_guid->get_datasources();
						
							foreach ($if_datasources as $if_datasource){
								
								$datasource_rras = $if_datasource->get_rratypes();
								
								foreach ($datasource_rras as $datasource_rra) {
									
									$this->create_xml_for_storage_repo($if_guid->get_monitoring_guid_id(), $if_datasource->get_rrd_ds_name(), $datasource_rra->get_consolidation_function(), $start_time, $end_time, 'add');
									
								}
							}
						}
					}
				}
			
				if ($this->_xml_store_repo_request != null) {
				
						$this->create_xml_for_storage_repo(null, null, null, null, null, 'finish');
						
						$storageclient = new SoapClient("http://storage-repo.belairinternet.com/wsdl/data_request_service.wsdl",array("trace" => 1, "exceptions" => 1));
						$return_data = $storageclient->submitMonitorData($this->_xml_store_repo_request->saveXML());
						
						$this->write_image_files_in_xml_response($return_data, $return_format);
						
							if ($return_format == 'array') {
									
								return $this->_return_array;
									
							} elseif ($return_format == 'xml') {
							
								return $this->_return_xml;
							
							}
				}
			
			
		}
		
		
		private function create_xml_for_storage_repo($monitoring_guid_id=null, $rrd_ds_name=null, $consolidation_function=null, $start_time=null, $end_time=null, $action)
		{
			
			if ($this->_xml_store_repo_request == null) {
				
				$this->_xml_store_repo_request = new DOMDocument();
					
				$this->_tracker_0 = $this->_xml_store_repo_request->appendChild(
				$this->_xml_store_repo_request->createElement("monitoring_data_request"));
				
				
			}
			
			if (!isset($this->_tracker_array_1[$monitoring_guid_id]) && $monitoring_guid_id != null && $action == 'add') {
				
				$this->_tracker_array_1[$monitoring_guid_id] = $monitoring_guid_id;
				
				$this->_tracker_array_1[$monitoring_guid_id] = $this->_tracker_0->appendChild(
				$this->_xml_store_repo_request->createElement("guid_data"));
				
				$this->_tracker_array_1[$monitoring_guid_id]->appendChild(
				$this->_xml_store_repo_request->createElement("guid_id", "$monitoring_guid_id"));
				
			}			

			if (!isset($this->_tracker_array_2["$rrd_ds_name$monitoring_guid_id$consolidation_function"]) && $monitoring_guid_id != null && $rrd_ds_name != null && $start_time != null && $end_time != null && $action == 'add') {
				
				$this->_tracker_array_2["$rrd_ds_name$monitoring_guid_id$consolidation_function"] = $rrd_ds_name;
			
				$this->_tracker_array_2["$rrd_ds_name$monitoring_guid_id$consolidation_function"] = $this->_tracker_array_1[$monitoring_guid_id]->appendChild(
				$this->_xml_store_repo_request->createElement("datasource"));
			
				$this->_tracker_array_2["$rrd_ds_name$monitoring_guid_id$consolidation_function"]->appendChild(
				$this->_xml_store_repo_request->createElement("rrd_ds_name", "$rrd_ds_name"));
				
				$this->_tracker_array_2["$rrd_ds_name$monitoring_guid_id$consolidation_function"]->appendChild(
				$this->_xml_store_repo_request->createElement("consolidation_function", "$consolidation_function"));
				
				$this->_tracker_array_2["$rrd_ds_name$monitoring_guid_id$consolidation_function"]->appendChild(
				$this->_xml_store_repo_request->createElement("start_time", "$start_time"));
				
				$this->_tracker_array_2["$rrd_ds_name$monitoring_guid_id$consolidation_function"]->appendChild(
				$this->_xml_store_repo_request->createElement("end_time", "$end_time"));
			
			}
			
			if ($action == 'finish') {
					
				$this->_xml_store_repo_request->formatOutput = true;
					
			}

		}
		
		private function write_image_files_in_xml_response($return_xml_data, $return_format)
		{
			
			$this->_xml_input_data = new SimpleXMLElement($return_xml_data);

			foreach ($this->_xml_input_data->xpath("/monitoring_data_response/guid_data") as $guid_data) {
				
				$guid = $guid_data->xpath("guid_id");
				
				$this->_return_array["$guid[0]"] = array();
				
				foreach($this->_xml_input_data->xpath("/monitoring_data_response/guid_data/datasource") as $datasource_request) {
						
					$rrd_ds_name = $datasource_request->xpath("rrd_ds_name");
					$start_time = $datasource_request->xpath("start_time");
					$end_time = $datasource_request->xpath("end_time");
					$consolidation_function = $datasource_request->xpath("consolidation_function");
					$image_format = $datasource_request->xpath("image_format");
					$image_data = $datasource_request->xpath("image_data");
				
					$filepath = APPLICATION_PATH."/../public/app_file_store/monitoring/rrd_graphs/";
					$filename = "guid_".$guid[0]."_".$rrd_ds_name[0]."_".$start_time[0]."_".$end_time[0]."_".$consolidation_function[0].".".$image_format[0]."";
				
					$create_path = "".$filepath."/".$filename."";
					
					file_put_contents($create_path, base64_decode($image_data[0]));
	
				}
			}
			
			if ($return_format == 'array') {
			
				$this->_return_array["$guid[0]"]["$rrd_ds_name[0]"]["$consolidation_function[0]"] = "http://".$_SERVER["SERVER_NAME"]."/app_file_store/monitoring/rrd_graphs/$filename";
			
			} elseif ($return_format == 'xml') {
			
			
			}
		}

}
?>