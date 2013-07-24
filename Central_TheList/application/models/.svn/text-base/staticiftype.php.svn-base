<?php

// by non 9/5/2012

class thelist_model_staticiftype
{
	
	private $_static_if_type_id;
	private $_eq_type_id;
	private $_if_type_id;
	private $_if_index_number;
	private $_if_default_name;	
	
	//common variables
	private $log;
	private $user_session;
	private $database;
	
	public function __construct($static_if_type_id)
	{
		$this->_static_if_type_id = $static_if_type_id;
		
		$this->_log			= Zend_Registry::get('logs');
		$this->_user_session = new Zend_Session_Namespace('userinfo');
		
		$sql="SELECT static_if_type_id,eq_type_id,if_index_number,if_type_id,if_default_name
			  FROM static_if_types
			  WHERE static_if_type_id = ".$this->_static_if_type_id;
		
		$static_if_type = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		$this->_eq_type_id		=	$static_if_type['eq_type_id'];
		$this->_if_type_id		= 	$static_if_type['if_type_id'];
		$this->_if_index_number  =   $static_if_type['if_index_number'];
		$this->_if_default_name	=	$static_if_type['if_default_name'];
		
	}
	public function get_static_if_type_id(){
		return $this->_static_if_type_id;
	}
	
	public function get_eq_type_id(){
		return $this->_eq_type_id;
	}
	
	public function get_if_type_id(){
		return $this->_if_type_id;
	}
	
	public function get_if_type(){
		
		return new Thelist_Model_interfacetype($this->_if_type_id);
	}
	
	public function get_if_index_number(){
		return $this->_if_index_number;
	}
	
	public function get_if_default_name(){
		return $this->_if_default_name;
	}
	
}
?>