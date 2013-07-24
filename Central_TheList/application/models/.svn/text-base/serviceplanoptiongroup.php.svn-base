<?php
class thelist_model_serviceplanoptiongroup
{
	
	private $service_plan_option_group_id;
	private $service_plan_option_required_quantity;
	private $service_plan_option_max_quantity;
	private $service_plan_option_group_name;
	
	public function __construct($service_plan_op_type_group_id)
	{
		$this->service_plan_option_group_id = $service_plan_op_type_group_id;
		
		$sql =	"SELECT * FROM service_plan_option_groups
			  	WHERE service_plan_option_group_id=".$this->service_plan_option_group_id;
		
		$service_plan_op_type_group = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		$this->service_plan_option_required_quantity	= $service_plan_op_type_group['service_plan_option_required_quantity'];
		$this->service_plan_option_max_quantity			= $service_plan_op_type_group['service_plan_option_max_quantity'];
		$this->service_plan_option_group_name			= $service_plan_op_type_group['service_plan_option_group_name'];
		
	}
	
	public function get_service_plan_option_group_id()
	{
		return $this->service_plan_option_group_id;
	}
	
	public function get_service_plan_option_required_quantity()
	{
		return $this->service_plan_option_required_quantity;
	}
	
	public function get_service_plan_option_max_quantity()
	{
		return $this->service_plan_option_max_quantity;
	}
	
	public function get_service_plan_option_group_name()
	{
		return $this->service_plan_option_group_name;
	}

}
?>