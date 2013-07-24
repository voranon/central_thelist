<?php

class thelist_model_users
{
	
	private $database;
	private $_time;
	private $logs;
	
	private $firstname;
	private $lastname;
	private $title;
	private $department;
	private $role_id;
	private $cellphone;
	private $officephone;
	private $homephone;
	private $mail;
	
	
	
	public function __construct($uid)
	{
		$this->uid				= $uid;

		$this->_time			= Zend_Registry::get('time');
		$this->logs			    = Zend_Registry::get('logs');
		
		$sql='SELECT firstname,lastname,title,department,role_id,cellphone,officephone,homephone,email
			  FROM users
			  WHERE uid = '.$uid;

		$user = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		$this->firstname = $user['firstname']; 
		$this->lastname  = $user['lastname'];
		$this->title     = $user['title'];
		$this->department= $user['department'];
		$this->role_id	 = $user['role_id'];
		$this->cellphone = $user['cellphone'];
		$this->homephone = $user['homephone'];
		$this->email	 = $user['email'];
		
		
		
	}
	
	
	public function get_uid(){
		return $this->uid;
	}
	
	public function get_firstname(){
		return $this->firstname;
	}
	
	public function get_lastname(){
		return $this->lastname;
	}
	
	public function get_title(){
		return $this->title;
	}
	
	public function get_department(){
		return $this->department;
	}
	
	public function get_role_id(){
		return $this->role_id;
	}
	
	public function get_cellphone(){
		return $this->cellphone;
	}
	
	public function get_officephone(){
		return $this->officephone;
	}
	
	public function get_homephone(){
		return $this->homephone;
	}
	
	public function get_email(){
		return $this->email;
	}
	
	
	
	////////////////////////////////////
	public function set_firstname($firstname){
		
		if($this->firstname!=$firstname){
		
			$trace = debug_backtrace();
			$method = $trace[0]["function"];
			
			Zend_Registry::get('database')->set_single_attribute($this->uid,'users','firstname', $firstname,get_class($this),$method);
		
			$this->firstname=$firstname;
		}
		
		
	}
	
	public function set_lastname($lastname){
		
		if($this->lastname!=$lastname){
		
			$trace = debug_backtrace();
			$method = $trace[0]["function"];
			
			Zend_Registry::get('database')->set_single_attribute($this->uid,'users','lastname', $lastname,get_class($this),$method);
		
			$this->larstname=$lastname;
		}
	}
	
	public function set_title($title){
		
		if($this->title!=$title){
		
			$trace = debug_backtrace();
			$method = $trace[0]["function"];
			
			Zend_Registry::get('database')->set_single_attribute($this->uid,'users','title', $title,get_class($this),$method);
		
			$this->title=$title;
		}
	}
	
	public function set_department($department){
		if($this->department!=$department){
		
			$trace = debug_backtrace();
			$method = $trace[0]["function"];
			
			Zend_Registry::get('database')->set_single_attribute($this->uid,'users','department', $department,get_class($this),$method);
		
			$this->department=$department;
		}
	}
	
	public function set_role_id($role_id){
		
		if($this->role_id!=$role_id){
		
			$trace = debug_backtrace();
			$method = $trace[0]["function"];
			
			Zend_Registry::get('database')->set_single_attribute($this->uid,'users','role_id', $role_id,get_class($this),$method);
		
			$this->role_id=$role_id;
		}
	}
	
	public function set_cellphone($cellphone){
		
		if($this->cellphone!=$cellphone){
		
			$trace = debug_backtrace();
			$method = $trace[0]["function"];
			
			Zend_Registry::get('database')->set_single_attribute($this->uid,'users','cellphone', $cellphone,get_class($this),$method);
		
			$this->cellphone=$cellphone;
		}
	}
	
	public function set_officephone($officephone){
		
		if($this->officephone!=$officephone){
		
			$trace = debug_backtrace();
			$method = $trace[0]["function"];
			
			Zend_Registry::get('database')->set_single_attribute($this->uid,'users','officephone', $officephone,get_class($this),$method);
		
			$this->officephone=$officephone;
		}
	}
	
	public function set_homephone($homephone){
		
		if($this->homephone!=$homephone){
		
			$trace = debug_backtrace();
			$method = $trace[0]["function"];
			
			Zend_Registry::get('database')->set_single_attribute($this->uid,'users','homephone', $homephone,get_class($this),$method);
		
			$this->homephone=$homephone;
		}
	}
	
	public function set_email($email){
		
		if($this->email!=$email){
		
			$trace = debug_backtrace();
			$method = $trace[0]["function"];
			
			Zend_Registry::get('database')->set_single_attribute($this->uid,'users','email', $email,get_class($this),$method);
		
			$this->email=$email;
		}
	}

}
?>