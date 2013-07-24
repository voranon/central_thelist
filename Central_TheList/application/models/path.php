<?php


class thelist_model_path
{
	private $_hop_count=null;
	private $_path_string=null;
	private $_path_if_array=null;
	
	private $_first_interface_id=null;
	private $_last_interface_id=null;
	
	public function __construct($path_string)
	{	
		$this->_path_string				= $path_string;
		

		
		$this->_path_if_array = explode(',', $path_string);

		$this->_hop_count = count($this->_path_if_array);

		$i=0;
		foreach ($this->_path_if_array as $path_hop) {

			if ($i == 0) {
				
				$this->_first_interface_id = $path_hop;
			}
			
			if($i == ($this->_hop_count - 1)) {
				
				$this->_last_interface_id = $path_hop;
				
			}
			
			$i++;
		}
	}


	public function get_path_string()
	{
		return $this->_path_string;
	}
	public function get_first_interface_id()
	{
		return $this->_first_interface_id;
	}
	public function get_last_interface_id()
	{
		return $this->_last_interface_id;
	}
	public function get_first_interface()
	{
		return new Thelist_Model_equipmentinterface($this->_first_interface_id);
	}
	public function get_last_interface()
	{
		return new Thelist_Model_equipmentinterface($this->_last_interface_id);
	}
	public function get_hop_count()
	{
		return $this->_hop_count;
	}
	public function get_path_if_array()
	{
		return $this->_path_if_array;
	}
	public function get_path_equipment()
	{
		$equipment = array();
		$i=0;
		foreach ($this->_path_if_array as $path_hop) {

			$if = Zend_Registry::get('database')->get_interfaces()->fetchRow('if_id='.$path_hop);

			if (!isset($equipment[$if['eq_id']])) {

				$equipment[$if['eq_id']]['equipment']					= new Thelist_Model_equipments($if['eq_id']);

				if ($this->_first_interface_id == $if['if_id']) {
					
					//interface we exit the very first equipment from (it will not have an inbound interface) 
					$equipment[$if['eq_id']]['outbound_interface']		= $equipment[$if['eq_id']]['equipment']->get_interface($if['if_id']);
					
				} else {
					
					//interface id we enter a piece of equipment through
					$equipment[$if['eq_id']]['inbound_interface']		= $equipment[$if['eq_id']]['equipment']->get_interface($if['if_id']);
					
				}
				
			} else {
				
				//interface id we exit the equipment from
				$equipment[$if['eq_id']]['outbound_interface']			= $equipment[$if['eq_id']]['equipment']->get_interface($if['if_id']);
				
			}
		}

		return array_values($equipment);
	}
		
}
?>