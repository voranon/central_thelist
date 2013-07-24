<?php

//by martin
//exception codes 8200-8299

class thelist_model_deviceauthenticationcredential
{
	
	private $_username=null;
	private $_password=null;
	private $_enablepassword=null;
	private $_passwordstrenght=null;
	private $_api_name=null;
	private $_connect_class=null;
	private $database=null;
	private $_eq_api_id=null;
	private $_api_id=null;
	private $_specific_connect_implentation=null;
	
	//there can be no database dependency for the constructor
	//this is a class so we can generate usernames and passwords.
	//also need methods to validate username and password strengths
	//we also want to gauge if the authentication requires a change before allowing the device to become equipment, rules would be here.

	public function get_equipment_credentials($eq_id, $api_id=null)
	{		
		if ($api_id != null) {
				
			$sql = 	"SELECT * FROM equipment_apis ea
					WHERE ea.eq_id='".$eq_id."'
					AND ea.api_id='".$api_id."'
					";
				
			$eq_api = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
			if(isset($eq_api['eq_api_id'])) {
				
				$api = $eq_api;
				
			}	
			
				
		} else {
				
			$sql = 	"SELECT * FROM equipment_apis ea
					WHERE ea.eq_id='".$eq_id."'
					";
				
			$eq_apis = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
		//give preferance to the secure connections over insecure

			if (isset($eq_apis['0'])) {
				
				foreach($eq_apis as $eq_api) {
					
					if ($eq_api['api_id'] == '1') {
						
						$secure = $eq_api;
					
					} else {
						
						$insecure = $eq_api;
						
					}
				}
				
				if (isset($secure)) {
					
					$api = $secure;
					
				} elseif (!isset($secure) && isset($insecure)) {
					
					$api = $insecure;
					
				}
			}
		}
			
		if (isset($api)) {
				
			$this->fill_from_eq_api_id($api['eq_api_id']);
				
		} else {
			
			throw new exception('api not defined for this equipment');
			
		}
	}
	
	public function set_device_user_name($username)
	{
		$this->_username = $username;
	}
	public function set_api_name($api_name)
	{
		$this->_api_name = $api_name;
	}
	
	public function set_device_password($password)
	{	
		$this->_password = $password;
		//create method to gauge strength.
		$this->_passwordstrenght = '10';
	}
	
	public function set_device_enablepassword($enablepassword)
	{
		$this->_enablepassword = $enablepassword;
	}
	
	public function get_device_username()
	{
		return $this->_username;
	}
	
	public function get_device_password()
	{
		return $this->_password;
	}
	
	public function get_device_api_name()
	{
		if ($this->_api_id != null) {
			$sql=	"SELECT api_name FROM apis
					WHERE api_id='".$this->_api_id."'
					";
				
			return Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
				
		} else {
			return $this->_api_name;
		}
	}
	
	public function get_device_enablepassword()
	{
		return $this->_enablepassword;
	}
	
	public function get_specific_connect_class()
	{
		return $this->_connect_class;
	}
	
	public function set_specific_connect_class($specific_connect_class)
	{
		$this->_connect_class = $specific_connect_class;
	}
	
	public function get_specific_connect_implentation()
	{
		return $this->_specific_connect_implentation;
	}
	
	public function set_specific_connect_implentation($specific_connect_implentation)
	{
		$this->_specific_connect_implentation = $specific_connect_implentation;
	}
	
	public function get_api_id()
	{
		if ($this->_api_name != null) {
			$sql=	"SELECT api_id FROM apis
					WHERE api_name='".$this->_api_name."'
					";
			
			return Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
		} else {
			return $this->_api_id;
		}
	}
	
	public function set_api_id($api_id)
	{
		if (is_numeric($api_id)) {
			$this->_api_id = $api_id;
		} else {
			throw new exception("api id must be numeric ", 8207);
		}
	}
	
	public function get_eq_api_id()
	{
		return $this->_eq_api_id;
		
	}
	
	public function fill_from_eq_api_id($eq_api_id)
	{

		$this->_eq_api_id	= $eq_api_id;
		
			//there is a left outer join in this query because there are apis that does not require authentication i.e. http
		
			$sql=	"SELECT eauth.auth_type, eauth.auth_value, a.api_name FROM equipments e
					INNER JOIN equipment_apis ea ON ea.eq_id=e.eq_id
					INNER JOIN apis a ON a.api_id=ea.api_id
					LEFT OUTER JOIN equipment_auths eauth ON eauth.eq_api_id=ea.eq_api_id
					WHERE ea.eq_api_id='".$eq_api_id."'
					";
		
			$authentication_values  = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
				
			//get the username, password, enablepassword
				foreach ($authentication_values as $authentication_value) {

				foreach ($authentication_value as $key => $value) {
						
					if ($key == 'auth_type' && $value == 'username') {
				
						$this->_username = $authentication_value['auth_value'];
				
					} elseif($key == 'auth_type' && $value == 'password'){
				
						$this->_password = $authentication_value['auth_value'];
							
					} elseif($key == 'auth_type' && $value == 'enablepassword'){
				
						$this->_enablepassword = $authentication_value['auth_value'];
							
					} elseif($key == 'api_name'){
				
						$this->_api_name = $value;
					}
						
				}
	
			}
	}
	
	public function fill_default_values($version)
	{
		if ($version == '1') {
			
			//routeros 1
			$this->_username = 'admin';
			$this->_password = 'merlin3D';
			$this->_api_name = 'ssh';
			
		} elseif($version == '2') {
			
			//dtvstb 1
			$this->_api_name = 'http';
			
		} elseif($version == '3') {
			
			//routeros 2
			$this->_username = 'admin';
			$this->_password = 'K11ne0ver%';
			$this->_api_name = 'ssh';
			
		} elseif($version == '4') {
			
			//cisco 1
			$this->_password 		= 'WiGwOoU';
			$this->_api_name 		= 'telnet';
			$this->_enablepassword	= 'nitram';
			
		} elseif($version == '5') {
			
			//routeros 3
			$this->_username = 'admin';
			$this->_password = '';
			$this->_api_name = 'ssh';
			
		}
	}
	
	public function get_random_word()
	{
		$found_word = false;
			
		while ($found_word == false){
	
			//we have 29000 random words in the database
			$random_value = rand(0, 29000);
	
			$sql =	"SELECT word FROM random_words
					WHERE random_word_id='".$random_value."'
					";
	
			$word_result = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
	
			if (isset($word_result['word'])) {
				$found_word = true;
			}
		}
		
		return $word_result;
	}
	
	public function encryption_key_validation($key, $template_name)
	{
		if ($template_name == 'wpa_encryption_key') {
			
			$characters 				= $this->range_templates($template_name);
			
			//key must be 10 characters or more
			$key_length = strlen($key);
			
			if ($key_length < 10) {
				throw new exception("key is too short, must be 10 or more supplied key is: ".$key_length." characters ", 8202);
			}
			
			//Here is how many time we allow a character to be repeated
			//because the template contains x characters eventually there will be duplicates
			//over 25 the chance of getting 2 of the same in a random pool of 55 char is about 30%.
			//so we allow more duplicates 
			if ($key_length < 25) {
				$allowed_occurance_count 	= 2;				
			} else {
				$allowed_occurance_count	= ($key_length / 25) + 2;
			}

		} else {
			throw new exception("Unable to validate template name provided: ".$template_name." is an unknown encryption validation template", 8204);
		}
			
		
		//explode the key into induvidual characters
		$single_char_array = str_split($key);
		$highest_occurance_count = 0;
		
		//test that all included char are allowed
		foreach($single_char_array as $char) {
			
			//we count how many times the same character appears, this will find the most used
			//sometimes we do not want passwords that are just a repitition of a number or character 
			$occurance_count = substr_count($key, $char);
			
			if ($occurance_count > $highest_occurance_count) {
				//replace the current max
				$highest_occurance_count = $occurance_count;
			}

			//check that all charactrs 
			if (strpos($characters, $char) === false) {
				throw new exception("key contains character: ".$char." this is not allowed in template: ".$template_name." ", 8203);
			}
		}
		
		//was there a limit to how many times a character could be used?
		if (isset($allowed_occurance_count)) {
			
			if ($allowed_occurance_count < $highest_occurance_count) {
				throw new exception("key contains the same character too many times", 8205);
			}
		}
		
		//if we make it through
		return true;
	}
	
	public function get_random_string_value($random_string_length, $template_name)
	{
		if (is_numeric($random_string_length) && $random_string_length > 0 ) {
			
			//get a string containing the allowed characters, for the requested template
			$characters = $this->range_templates($template_name);

			$attempts = 0;
		
			//because the entire process is random there is a probability that too many of the same character 
			//is used or other rules of validation are violated. allowing us to try multiple times mitigates this issue
			//without sacrificing the random nature of key generation
			while ($attempts < 10) {
			
				$attempts++;

				try {

					$string = '';
					
					for ($i = 0; $i < $random_string_length; $i++) {
						$string .= $characters[rand(0, strlen($characters) - 1)];
					}
					
					$validation = $this->encryption_key_validation($string, $template_name);
					
					if ($validation == true) {
						
						//validated good return the string
						return $string;
					}
					
				} catch (Exception $e) {
						
					switch($e->getCode()) {
				
						case 8202;
						//key lenght is too short thats not an something we can fix by trying again
						throw $e;
						break;
						case 8204;
						//this type of template is not able to be validated, this is ok, not all random strings require validation, we return the string
						return $string;
						break;
						case 8203;
						//charactor not allowed, this should never happen, the template dictates the allowed char and is also used in validation
						throw $e;
						break;
						case 8205;
						//key contains the same character too many times, the proberbillity is low but if it happens we try again, 
						//the chances increase with the length of the string, and we mitigate by allowing more repeats as the string grows
						break;
						default;
						throw $e;
				
					}
				}
			}

			throw new exception("we tried ".$attempts." times, to get a random key that fulfilled the requirement and failed " , 8206);
			
		} else {
			throw new exception("random string length must be longer than 0 and must be numeric, you supplied: ".$random_string_length." ", 8201);
		}
	}
	
	private function range_templates($template_name)
	{
		$character_arrays = array();
		//this can be expanded to include templates, if there are char or numbers that are not allowed in a certain value
		//currently this is used for WPA/WPA2 key generation. we dont like 0 and 1 because thy look like letters O I
		if ($template_name == 'wpa_encryption_key') {
		
			//default ranges only have char that are easy to distinguis
			array_push($character_arrays, range('2', '9'), range('a', 'k'), range('m', 'n'), range('p', 'z'), range('A', 'H'), range('K', 'N'), range('P', 'Z'));
		
		} else {
				
			throw new exception("the template: ".$template_name." does not exist, we cannot generate a range without a valid template ", 8200);
		}
		
		//now turn the arrays into a nice long string
		$characters = '';
		foreach ($character_arrays as $character_array) {
			foreach($character_array as $char) {
				$characters .= $char;
			}
		}
		
		//return the string of allowed characters
		return $characters;
	}

}
?>