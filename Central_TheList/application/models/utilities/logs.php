<?php
require_once 'Zend/Log.php';
class thelist_utility_logs
{
	private $app_logger;
	private $user_logger;
	private $equipment_logger;
	
	function __construct()
	{
		//Zend_Registry::get('database') = new database();

   		$this->user_session = new Zend_Session_Namespace('userinfo');
   	
   		$columnMapping = array('priority' => 'priority', 'message' => 'message');
   		$app_write = new Zend_Log_Writer_Db(Zend_Registry::get('database')->get_thelist_adapter(), 'app_event_logs', $columnMapping);
   		
   		
   		$this->app_logger		= new Zend_Log($app_write);
   		$this->user_logger  	= Zend_Registry::get('database')->get_user_event_logs();
   		$this->equipment_logger = Zend_Registry::get('database')->get_equipment_logs();
   		
   	}
   	
   	public function get_app_logger(){
   		return $this->app_logger;
   	}
   	public function get_user_logger(){
   		return $this->user_logger;
   	}
   	public function get_equipment_logger(){
   		return $this->equipment_logger;
   	}
   	
   	public function user_log($event=null,
   							 $class=null,
   							 $method=null,
   							 $primary_key_name=null,
   							 $primary_key_value=null,
   							 $old=null,
   							 $new=null,
   							 $message1=null,
   							 $message2=null)
    {
    	
    	
	
    	$data =array(
				'uid'				=>			$this->user_session->uid,
				'page_name'			=>			'http://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"],
				'event'             =>          $event,
				'class_name'        =>          $class,
				'method_name'       =>          $method,
				'primary_key_name'  =>          $primary_key_name,
				'primary_key_value' =>			$primary_key_value,
				'message_1'			=>			$message1,
				'message_2'			=>			$message2,
				'xml_message_1'		=>          $this->array_as_xml($old),
				'xml_message_2'		=>			$this->array_as_xml($new),
				'ip_address'		=>			$_SERVER['REMOTE_ADDR']
				);
    	
   		Zend_Registry::get('database')->get_user_event_logs()->insert($data);
   		
   	}
   	
   	public function loglocation($user_location_type=null,
							    $latitude=null,
							    $longitude=null,
							    $accuracy=null,
							    $altitude=null,
							    $altitudeaccuracy=null,
							    $heading=null,
							    $speed=null, 
							    $ip_address=null
							    )
    {
    	
    	if ($latitude != null && $longitude != null) {

	    	//before login it seems we get the server ip as remote address
	    	//so lets replace it with the remote request
	    	if ($ip_address == null) {	
	    		$ip_address = $_SERVER['REMOTE_ADDR'];
	    	}
	    	
	    	$data =array(
	    	
					'user_location_type'		=>			$user_location_type,		
					'latitude'					=>			$latitude,
					'longitude'             	=>          $longitude,
			    	'accuracy'             		=>          $accuracy,
			    	'altitude'             		=>          $altitude,
			    	'altitudeaccuracy'         	=>          $altitudeaccuracy,
			    	'heading'             		=>          $heading,
			    	'speed'             		=>          $speed,
					'user_id'       			=>			$this->user_session->uid,
					'ip_address'       			=>          $ip_address
					
	    	);
	    	
	   		Zend_Registry::get('database')->get_user_locations()->insert($data);

    	}
   	}

   	public function get_database(){
   		return Zend_Registry::get('database');
   	}
   	
   	public function array_as_xml($data=null)
   	{ 

   	  		$xmlDoc = new DOMDocument();
	
			$head = $xmlDoc->appendChild(
			$xmlDoc->createElement("data"));

			if(is_array($data)){
   				foreach($data as $key => $value){
   					
   					//there is an issue with '&' not being escaped in XML (https://bugs.php.net/bug.php?id=36795&edit=1)
   					$safe_value = preg_replace('/&(?!\w+;)/', '&amp;', $value);
   					$safe_key = preg_replace('/&(?!\w+;)/', '&amp;', $key);
   					
   					$head->appendChild(
   					$xmlDoc->createElement($safe_key, $safe_value));
   				}
			} else {
				
				$head->appendChild(
				$xmlDoc->createElement('empty', 'value'));
				
			}
   			$xmlDoc->formatOutput = true;
			
 	   		return $xmlDoc->saveXML();
   		
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