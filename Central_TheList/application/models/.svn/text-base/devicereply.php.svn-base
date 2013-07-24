<?php 

//by martin

class thelist_model_devicereply
{
		private $_command;
		private $_code;
		private $_message;
		private $_options=array();

		public function __construct($command, $message)
		{
			$this->_command		= $command;
			$this->_message		= $message;
		}
		
		public function get_command()
		{
			return $this->_command;
		}
		public function get_code()
		{
			return $this->_code;	
		}
		public function get_message()
		{
			return $this->_message;
		}
		public function get_options()
		{	
			return $this->_options;
		}
		
		public function set_code($code)
		{
			
			//code 1 is good
			//code 2 is incomplete
			//code 3 is error executing
			$this->_code = $code;
		}
	
		public function append_option($key, $option)
		{
			//we do not allow options to be overridden.
			if (!isset($this->_options[$key])) {
				
				$this->_options[$key]	= $option;
				
			} else {
				
				throw new exception('index already set', 5);
				
			}

		}
		
		public function set_message($new_message)
		{
			$this->_message = $new_message;
		}
}
?>