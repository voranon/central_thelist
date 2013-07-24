<?php

// by non 9/7/2012

class thelist_model_homeruntypegroups{
	
	
	private $log=null;
	private $user_session;
	
	private $homerun_type_group_id;
	private $homerun_type_required_quantity;
	private $homerun_type_group_desc;
	private $homerun_type_group_name;
	
	public function __construct($homerun_type_group_id){
		
		$this->homerun_type_group_id = $homerun_type_group_id;
		
		$this->log			= Zend_Registry::get('logs');
		$this->user_session = new Zend_Session_Namespace('userinfo');
		
		$sql="SELECT homerun_type_group_id,homerun_type_required_quantity,homerun_type_group_desc,homerun_type_group_name
			  FROM homerun_type_group
			  WHERE homerun_type_group_id = ".$this->homerun_type_group_id;
		
		$homeruntypegroups	=	Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		$this->homerun_type_required_quantity  	= $homeruntypegroups['homerun_type_required_quantity'];
		$this->homerun_type_group_desc			= $homeruntypegroups['homerun_type_group_desc'];
		$this->homerun_type_group_name			= $homeruntypegroups['homerun_type_group_name'];
		 		
		
	}
	public function get_homerun_type_group_id(){
		return 0;
		//return $this->homerun_type_group_id;
	}
	
	public function get_homerun_type_required_quantity(){
		return $this->homerun_type_required_quantity;
	}
	public function get_type_group_desc(){
		return $this->homerun_type_group_desc;
	}	
	public function get_homerun_type_group_name(){
		return $this->homerun_type_group_name;
	}
	
	static public function get_all_homeruntypegroups(){
		
		$sql="SELECT homerun_type_group_id,homerun_type_required_quantity,homerun_type_group_desc,homerun_type_group_name
			  FROM homerun_type_group";
		$homeruntypegroups	=	Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		$output='';
		foreach($homeruntypegroups as $homeruntypegroup)
		{
			//$output[ $homeruntypegroup['homerun_type_group_id'] ] =  new static( $homeruntypegroup['homerun_type_group_id'] );
			$homeruntypegroups =  new static( $homeruntypegroup['homerun_type_group_id'] );
			
			$output[] = $homeruntypegroups->toArray(); 
		}
		return $output;
		
	}
	
	public function toArray()
	{
		$obj_content	= print_r($this, 1);
		$class_name		= get_class($this);
	
		//get all private variable names
		preg_match_all("/\[(.*):".$class_name.":private\]/", $obj_content, $matches);
	
		if (isset($matches['0']['0'])) {
			 
			$complete['private_variable_names'] = $matches['1'];
			 
			foreach ($matches['1'] as $index => $private_variable_name) {
	
				$one_variable	= $this->$private_variable_name;
				 
				if (is_array($one_variable)) {
					$complete['private_variable_type'][$index] = 'array';
				} elseif (is_object($one_variable)) {
					$complete['private_variable_type'][$index] = 'object';
				} else {
					$complete['private_variable_type'][$index] = 'string';
				}
			}
	
			foreach ($complete['private_variable_names'] as $private_index => $private_variable) {
					
				if ($complete['private_variable_type'][$private_index] == 'object') {
	
					if (method_exists($this->$private_variable, 'toArray')) {
						$return_array[$private_variable] = $this->$private_variable->toArray();
					} else {
						$return_array[$private_variable] = 'CLASS IS MISSING toArray METHOD';
					}
	
				} elseif ($complete['private_variable_type'][$private_index] == 'string') {
	
					$return_array[$private_variable] = $this->$private_variable;
	
				} elseif ($complete['private_variable_type'][$private_index] == 'array') {
						
					$array_tools	= new Thelist_Utility_arraytools();
					$return_array[$private_variable] = $array_tools->convert_mixed_array_to_strings($this->$private_variable);
	
				}
			}
		}
	
		return $return_array;
	}

}
?>