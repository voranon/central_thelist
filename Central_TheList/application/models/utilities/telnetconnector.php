<?php

//exception codes 3700-3799

class thelist_utility_telnetconnector 
{

	    private $_hostname;
	    private	$_telnetusername;
	    private $_telnetpassword;
	    private $_enablepassword;
	    public 	$_connection;
	    private $_data='';
	    private $_timeout;
	    private $_prompt;
	    private $_device_type;	
	    
	    //track evens and errors
	    private $_track=null;
	    private $_track_var=0;
	
	    public function __construct($eq_fqdn, $eq_telnet_username, $eq_telnet_password, $eq_telnet_enablepassword, $device_type) 
	    {
	        $this->_hostname 			= $eq_fqdn;
	        $this->_telnetusername 		= $eq_telnet_username;
	        $this->_telnetpassword 		= $eq_telnet_password;
	        $this->_enablepassword 		= $eq_telnet_enablepassword;
	        $this->_device_type			= $device_type;
	        
	        if ($this->_device_type == 'cisco') {
	        	//connect
	        	$this->get_cisco_connection();
	        } elseif ($this->_device_type == 'routeros') {
	        	//connect
	        	$this->get_routeros_connection();
	        } else {
	        	throw new exception("telnet cannot connect to unknown device type: '".$this->_device_type."'", 3700);
	        }   
	    }
	    
	    private function get_cisco_connection()
	    {
	    	
	    	//during connect use error handle
	    	
	    	//trace errors
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
			$class	= get_class($this);
			
			$error_handler		= new Thelist_Utility_customerrorhandler();
			$error_handler->set_error_class_method($class, $method);
			
			//during connect use error handle
			set_error_handler(array($error_handler, 'device_error_handler'));
	    	
	    	$this->_prompt 			= '#';
	    	$this->_timeout 		= 10;
	    	
	    	$this->_connection = fsockopen($this->_hostname, 23, $errno, $errstr, $this->_timeout);
	    	
	    	if (!is_resource($this->_connection)) {
	    		throw new exception("telnet open connection did not return resource when connection to host: '".$this->_hostname."'", 3704);
	    	}
	    	//$this->_connection = fsockopen($this->_hostname, 23);
	    	//set the timeout
	    	stream_set_timeout($this->_connection, $this->_timeout);
	    	
	    	//authenticate
	    	$this->cisco_data_collect(':');
	    	fputs($this->_connection, $this->_telnetpassword . chr(13));
	    	$this->_data = '';
	    	$this->cisco_data_collect('>');
	    	fputs($this->_connection, 'enable' . chr(13));
	    	$this->_data = '';
	    	$this->cisco_data_collect(':');
	    	fputs($this->_connection, $this->_enablepassword . chr(13));
	    	$this->cisco_data_collect('#');
	    	$this->_data = '';

	    	//back to normal error handle
	    	restore_error_handler();
	    }
	    
	    private function get_routeros_connection()
	    {

	    	throw new exception("method not working yet, more work to do", 3711);
	    
	    }
	    
	    private function routeros_data_collect($command=null)
	    {
	    	//clear the buffer
	    	$this->_data 		= '';

    		$i=0;
    		while ($return_line = fgets($this->_connection, 1024)) {

    			switch($return_line) {

    				case 'Login:';
    				
    				$this->_data .= $return_line;
    				fputs($this->_connection, $this->_telnetusername."\r\n");
    				break;
    			
    				case 'Password:';
    				$this->_data .= $return_line;
    				fputs($this->_connection, $this->_telnetpassword."\r\n");
    				break;
    			
    				default:
    				fputs($this->_connection, $command . "\r\n");
    				$this->_data .= $return_line;
    				if (strrpos($this->_data, $this->_prompt)) {
    					echo "\n <pre> after  \n ";
    				print_r($this->_data);
    				echo "\n 2222 \n ";
    				//print_r($return_array);
    				echo "\n 3333 \n ";
    				//print_r();
    				echo "\n 4444 </pre> \n ";
    				die;
    				}
    			}
    		}	
	    }

	    private function cisco_data_collect($prompt, $command=null) 
	    {
	    	$this->_track[]	= "command_start:" . time();
	    	$this->_track[]	= $prompt;
	    	$this->_track[]	= $command;

	    	//clear the buffer
	    	$this->_data 		= '';
	    	
	    	//exit does not require validation, because it could close the connection and thereby hang the while loop
	    	if ($command != 'exit') {
		    	
		    	$command_issued_done = false;
		    	$done = false;
	
		    	if ($command != null) {
	
		    		//start of command issueing (is that a word?)
		    		$command_issue_start_time = time();
		    		
		    		//char to replace
		    		$replacements = array(".","+","*","?","[","^","]","$","(",")","{","}","=","!","<",">","|",":","-","\\","/"," ","\r\n","\r","\n","\t");
		    		
		    		//pattern to find
		    		$pattern	= "/".substr(str_replace($replacements, '', $command), -10)."/";
		    		
		    		//validate that the command has been fully issued
		    		//before collecting the result
		    		fputs($this->_connection, $command . chr(13));
		    		
		    		while ($command_issued_done === false) {
		    			
		    			//remove all non visible char and replace char that need to be escaped
		    			$hay_stack = str_replace($replacements, '', preg_replace('/[^\r\n\t\x20-\x7E\xA0-\xFF]/', ' ', $this->_data));
		    			
		    			//commands have 5 sec to finish
		    			if (time() > ($command_issue_start_time + 5)) {
		    				throw new exception("telnet never finished issueing command: ".$command." on ".$this->_hostname."", 3703);
		    			}
	
		    			$this->_data 		.= fgetc($this->_connection);
		    			
		    			//if we by mistake issue i.e. end command when it is not valid then we get to wait while
		    			//the cisco device tries to lookup the command as a fqdn
		    			//or will say its invalid. 
		    				if (preg_match($pattern, $hay_stack)) {
		    				//we are only matching the last 10 char because the terminal on cisco does not show the entire command
		    				//the preg_quote function does not escape / so we string replace that char
		    				$command_issued_done = true;
		    				
		    			}
		    		}
		    	}
		    	
		    	$this->_track[]	= "command_end:" . time();
		    	$this->_track[]	= $this->_data;
		    	$this->_track[]	= "return_start:" . time();
		    	//clear the buffer
		    	$this->_data = '';
	
		    	$execution_start_time = time();
		    	
	 	    	while ($done == false) {
		            
	 	    		//excecution has 9 sec to finish, i.e. 'show run can take close to 10 sec to finish'
	 	    		if (time() > ($execution_start_time + 9)) {
	 	    			
	 	    			$this->_track[]	= $this->_data;
	 	    			
	 	    			
	 	    			if ($this->_track_var == 0) {
	 	    				$this->_track[]		= "Failed Once";
	 	    				$this->_track_var++;
	 	    				return;
	 	    			} elseif ($this->_track_var == 1) {
	 	    				$this->_track[]		= "Failed Two Times";
	 	    				$this->_track_var++;
	 	    				return;
	 	    			}
	 	    			
	 	    			throw new exception("telnet never finished issueing command: '".$command."' waiting for prompt: '".$prompt."' on ".$this->_hostname."", 3705);
	 	    		}

		    		$return_character = fgetc($this->_connection);
		    		
		    		//some cisco switches do not have a telnet password set
		    		//in that case we are looking for a : prompt when it really is >
		    		if ($return_character == '>' && $prompt == ':') {
		    			throw new exception("problem: no telnet password has been set on host: ".$this->_hostname."", 3710);
		    		}
		    		
		    		//add the returned char to the data buffer
		    		$this->_data 		.= $return_character;
		    		
		            if ($return_character == '-') {
		            	
		                if (substr($this->_data, -8) == '--More--'){
		                	
		                	//simulate a spacebar hit, when the terminal cannot contain all of the output in one go
		                	fputs($this->_connection, ' ');
		                }
		            }
		            
		            if ($return_character == $prompt) {
		            	$done = true;
		            } elseif ($prompt == '>' && $return_character == ':' && $command == null) {
		            	//we are throwing our own exception, no need for custom error handle
		            	restore_error_handler();
		            	//special case if this is during login and the telnet password is wrong
		            	throw new exception("Wrong Password", 3706);
		            } elseif ($prompt == '#' && $return_character == ':' && $command == null) {
		            	//we are throwing our own exception, no need for custom error handle
		            	restore_error_handler();
		            	//special case if this is during login and the enable password is wrong
		            	throw new exception("Wrong Enable Password", 3707);
		            } elseif (strrpos($this->_data, "Translating \"".$command."\"")) {
		            	//we are throwing our own exception, no need for custom error handle
		            	restore_error_handler();
		            	//if the entire command is being translated there is a problem issueing the command in that location
		    			throw new exception("command: '".$command."' on ".$this->_hostname." cannot be issued in the current location or has the wrong syntax, please review the command call", 3708);
		    		} elseif (strrpos($this->_data, "Invalid input detected at") && $command != 'show') {
		    			//we are throwing our own exception, no need for custom error handle
		    			restore_error_handler();
		            	//invalid input problem. we have an exception to this rule in that a pure show command is being issued by the "place in root folder" is just a show, and it needs to come back
		    			throw new exception("command: '".$command."' on ".$this->_hostname." cannot be issued in the current location or has the wrong syntax, please review the command call", 3709);
		    		}
		        }
		        
		        //remove the ---more--- lines
		        $this->_data = str_replace('--More--', "", $this->_data);
		        //remove backspace from the result
		        $this->_data = str_replace(chr(8), "", $this->_data);
		        
	    	} else {
	    		//on exit just run command
	    		fputs($this->_connection, $command . chr(13));
	    	}
	    	
	    	$this->_track[]	= $this->_data;
	    	$this->_track[]	= "return_end:" . time();
	    }

	    //martin
	    public function execute_cmd($command)
	    {	

	    	if ($this->_device_type == 'cisco') {

	    	 	$this->cisco_data_collect($this->_prompt, $command);
	    		
	    	} elseif ($this->_device_type == 'routeros') {
	    	
	    		$this->routeros_data_collect($command);
	    		
	    	} else {
	    		throw new exception('telnet cannot execute command on unknown device type', 3702);
	    	}
	    	
	    	$device_reply	= new Thelist_Model_devicereply($command, $this->_data);
	    	
	    	return $device_reply;
	    }

}
?>