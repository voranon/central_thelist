<?php

class thelist_model_queues{
	private $queue_id;
	private $queue_name;
	
	
	private $database;
	private $log;
	private $user_session;
	
	public function __construct($queue_id){
		$this->queue_id 		= $queue_id;

		$this->log				=   Zend_Registry::get('logs');
		$this->user_session 	= new Zend_Session_Namespace('userinfo');
		
		$queue = Zend_Registry::get('database')->get_queues()->fetchRow("queue_id=".$this->queue_id);
		
		$this->queue_name 		= $queue['queue_name'];
		
	}
	
	public function get_queue_name(){
		return $this->queue_name;	
	}
	
	public function set_queue_name($queue_name){
		
		if($queue_name != $this->queue_name){
			
			$trace = debug_backtrace();
			$method = $trace[0]["function"];
			
			Zend_Registry::get('database')->set_single_attribute($this->queue_id,'queues','queue_name', $queue_name,get_class($this),$method);
			
			
			/*
			$trace = debug_backtrace();
			$method = $trace[0]["function"];
			$this->log->get_user_logger()->insert(
							array(
								'uid'				=>			$this->user_session->uid,
								'page_name'			=>			'http://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"],
								'event'             =>          'update queue name',
								'class_name'        =>          get_class($this),
								'method_name'       =>          $method,
								'primary_key_name'  =>          'queue_id',
								'primary_key_value' =>			$this->queue_id,
								'message_1'			=>			'update queue name from '.$this->queue_name.' to '.$queue_name,
								'message_2'			=>			''
								 )
												);
		}
		
		$data = array(
						'queue_name'   => $queue_name
					 );
		
		
		Zend_Registry::get('database')->get_queues()->update($data,"queue_id=".$this->queue_id);
		*/
		$this->queue_name=$queue_name;
	}
	
	
	
	
	
	
}
?>