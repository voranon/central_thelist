<?php 

//by martin
class thelist_model_monitoringrratype
{
	private $_monitoring_rra_type_id;
	private $_consolidation_function;
	private $_acceptable_data_loss;
	private $_data_points_before_consolidation;
	private $_amount_of_data_points;

	//map filled
	private $_mapped_monitoring_ds_id=null;
	private $_monitoring_rra_type_map_id=null;
	
	public function __construct($monitoring_rra_type_id)
	{
		$this->_monitoring_rra_type_id = $monitoring_rra_type_id;
		

		
		$mon_rra = Zend_Registry::get('database')->get_monitoring_rra_types()->fetchRow('monitoring_rra_type_id='.$this->_monitoring_rra_type_id);

		$this->_consolidation_function					= $mon_rra['consolidation_function'];
		$this->_acceptable_data_loss					= $mon_rra['acceptable_data_loss'];
		$this->_data_points_before_consolidation		= $mon_rra['data_points_before_consolidation'];
		$this->_amount_of_data_points					= $mon_rra['amount_of_data_points'];

	}

	public function fill_mapping_data($monitoring_rra_type_map_id)
	{
		$sql = "SELECT * FROM monitoring_rra_type_mapping
				WHERE monitoring_rra_type_map_id='".$monitoring_rra_type_map_id."'
				";
				
		$rratypemap  = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		if ($rratypemap['monitoring_rra_type_id'] == $this->_monitoring_rra_type_id) {
			
			$this->_mapped_monitoring_ds_id = $rratypemap['monitoring_ds_id'];
			
		} else {
			
			throw new exception('the provided monitoring_rra_type_map_id is not based on this rratype');
			
		}

	}
	
	public function get_monitoring_rra_type_id()
	{
		return $this->_monitoring_rra_type_id;
	}
	public function get_consolidation_function()
	{
		return $this->_consolidation_function;
	}
	public function get_acceptable_data_loss()
	{
		return $this->_acceptable_data_loss;
	}
	public function get_data_points_before_consolidation()
	{
		return $this->_data_points_before_consolidation;
	}
	public function get_amount_of_data_points()
	{
		return $this->_amount_of_data_points;
	}
	public function get_mapped_monitoring_ds_id()
	{
		return $this->_mapped_monitoring_ds_id;
	}
	public function get_monitoring_rra_type_map_id()
	{
		return $this->_monitoring_rra_type_map_id;
	}
	
	
}
?>