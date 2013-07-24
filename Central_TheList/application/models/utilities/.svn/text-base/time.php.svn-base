<?php
 //exception codes 14400-14499
class thelist_utility_time
{
	private $_current_date_time;
		
	function __construct()
	{
   	}
   	
   	private function get_time()
   	{
   		date_default_timezone_set('America/Los_Angeles');
   		$this->_current_date_time = date("Y-m-d H:i:s");
   	}
   	
   	public function is_date_time($string)
   	{
   		//check if a string is a time / date or not
   		//should be made more specific so if only time or date is provided it returns false
   		if (date('Y-m-d H:i:s', strtotime($string)) == $string) {
   			return 'mysql_datetime_stamp';
   		} elseif (date("F j, Y, g:i a", strtotime($string)) == $string) {
   			return 'american_datetime_stamp';
   		} elseif (date("F j, Y", strtotime($string)) == $string) {
   			return 'american_date';
   		} elseif (date('Y-m-d', strtotime($string)) == $string) {
   			return 'mysql_date';
   		} else {
   			return false;
   		}
   	}
   	
   	//general time functions, not like further below, all should take string and convert to chosen format
   	//if input is not a date or time or datetime then should return false
   	//if input is recognized as a valid date time, but we still do not know how to conver it then return null
   	public function format_date_time($input_string, $output_format)
   	{
   		if ($input_format = $this->is_date_time($input_string)) {
   			
   			if ($input_format == 'mysql_datetime_stamp') {
   				
   				if ($output_format == 'american') {
   					return $this->convert_mysql_datetime_to_am_pm($input_string);
   				} elseif ($output_format == 'mysql') {
   					return $input_string;
   				} elseif ($output_format == 'epoch') {
   					return $this->convert_mysql_datetime_to_epoch($input_string);
   				} else {
   					//we cannot resolve the date, time or datetime
   					return null;
   				}
   				
   			} elseif ($input_format == 'american_datetime_stamp') {
   				
   				if ($output_format == 'american') {
   					return $input_string;
   				} elseif ($output_format == 'mysql') {
   					return $this->convert_am_pm_to_mysql_datetime($input_string);
   				} else {
   					//we cannot resolve the date, time or datetime
   					return null;
   				}
   				
   			} else {
   				//we cannot resolve the date, time or datetime
   				return null;
   			}
   			
   		} else {
   			return false;
   		}
   	}
   	
   	public function get_current_date_time(){
   		
   		$this->get_time();
   		
   		return $this->_current_date_time;
   		
   	}
   	public function get_current_date_time_as_epoch()
   	{
   		return time();
   	}
   	
   	public function get_current_date_time_mysql_format()
   	{
   		$this->get_time();
   		return $this->_current_date_time;
   	}
   	
   	public function convert_string_month_to_number($string)
   	{	
   		$search  = array('jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec');
   		$replace = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
   		
   		return str_replace($search, $replace, $string);
   	}
   	
   	public function convert_current_date_time_to_epoc()
   	{
   		$this->get_time();
   		return strtotime($this->_current_date_time);
   	}
   	
   	public function get_current_date_time_as_am_pm()
   	{
   		return date("F j, Y, g:i a");
   	}

   	public function convert_mysql_datetime_to_am_pm($mysql_datetime)
   	{
   		if (date('Y-m-d H:i:s', strtotime($mysql_datetime)) == $mysql_datetime) {
   			return date("F j, Y, g:i a", strtotime($mysql_datetime));
   		} else {
   			return false;
   		}
   	}
   	
   	public function convert_mysql_datetime_to_epoch($mysql_datetime)
   	{
   		if (date('Y-m-d H:i:s', strtotime($mysql_datetime)) == $mysql_datetime) {
   			return strtotime($mysql_datetime);
   		} else {
   			return false;
   		}
   	}
   	
   	public function convert_mysql_datetime_to_datepicker($mysql_datetime){
   		
   		
   		if( ($mysql_datetime == '0000-00-00 00:00:00') || ($mysql_datetime == null) ){
   			return '';
   		}else{
   			
   			date_default_timezone_set('America/Los_Angeles');
   			return date("m/d/Y",strtotime($mysql_datetime));
   		}
   		
   	}
   	
   	public function convert_am_pm_to_mysql_datetime($am_pm_datetime)
   	{
   		date_default_timezone_set('America/Los_Angeles');
   		return date("Y-m-d H:i:s", strtotime($am_pm_datetime));
   	
   	}
   	   	

   	
   	public function get_current_date_time_date_picker(){
   		$this->get_time();
   		return date("m/d/Y",strtotime($this->_current_date_time));
   	}
   	
   	public function convert_string_to_mysql_datetime($datetime)
   	{
   		
   		if ($datetime != '') {
	   		date_default_timezone_set('America/Los_Angeles');
	   		return date("Y-m-d H:i:s", strtotime($datetime));
   		} else {
   			
   			return '0000-00-00 00:00:00';
   			
   		}
   	}
   	
   	public function convert_string_to_epoch_time($datetime)
   	{
   		return strtotime($datetime);
   	}
   	
   	public function convert_linux_process_run_time_to_seconds($ps_time)
   	{

   		if (preg_match("/^([0-9]+):([0-9]+)$/", $ps_time, $result1)) {
   			$number_of_elapsed_seconds = ($result1['1'] * 60) + $result1['2'];
   		} elseif(preg_match("/^([0-9]+):([0-9]+):([0-9]+)$/", $ps_time, $result1)) {
   			$number_of_elapsed_seconds = ($result1['1'] * 3600) + ($result1['2'] * 60) + $result1['3'];
   		} elseif(preg_match("/^([0-9]+)-([0-9]+):([0-9]+):([0-9]+)$/", $ps_time, $result1)) {
   			$number_of_elapsed_seconds = ($result1['1'] * 86400) + ($result1['2'] * 3600) + ($result1['3'] * 60) + $result1['4'];
   		} else {
   			throw new exception('cannot convert linux process start time', 14400);
   		}
   		
   		return $number_of_elapsed_seconds;
   	}
   	
   	public function convert_epoch_to_linux_log_formatted_date($epoch)
   	{
   		date_default_timezone_set('America/Los_Angeles');
   		$the_date	= date("M j H:i:s", $epoch);
   		
   		//linux makes a space for the date if it does not have 2 digits
   		//so we have to conpensate for that
   		//i.e. first has extra space
   		//Sep  3 17:29:45
   		//Sep 13 17:29:45
   		
   		if (preg_match("/(\w{3}) ([0-9]) ([0-9]+:[0-9]+:[0-9]+)/", $the_date, $result1)) {
   			
   			//make extra space
   			$formatted	= $result1['1']."  ".$result1['2']." ".$result1['3'];

   		} elseif (preg_match("/(\w{3}) ([0-9]+) ([0-9]+):([0-9]+):([0-9]+)/", $the_date, $result1)) {
   			
   			//no extra space needed
   			$formatted	= $the_date;
   		}
   		
   		return $formatted;	
   	}

   	public function convert_seconds_to_mikrotik_time_format($seconds)
   	{
   		
   		$start = $seconds;
   		$d = intval($seconds/86400);
   		$seconds -= $d*86400;

   		$h = intval($seconds/3600);
   		$seconds -= $h*3600;
   		
   		$m = intval($seconds/60);
   		$seconds -= $m*60;

   		if ($d){
   			if (!isset($str)) {
   				$str = '';
   			}
   			$str .= $d . 'd';
   		}
   		if ($h) {
   			if (!isset($str)) {
   				$str = '';
   			}
   			$str .= $h . 'h';
   		}
   		if ($m) {
   			if (!isset($str)) {
   				$str = '';
   			}
   			$str .= $m . 'm';
   		}
   		if ($seconds) {
   			if (!isset($str)) {
   				$str = '';
   			}
   			$str .= $seconds . 's';
   		}
   		
   		return $str;
   		
   	}
   	
   	public function get_date_time_add_sec($number_of_seconds=30)
   	{
   	
   		//many times functions are running for more than a second and many validations are based on time but validations can fail randomly based on server load
   		//if a request takes more than 1 sec to complete. this method allows validations to use a time that matched the php.ini timeout or a custom value
   		$adjusted_time = time() + $number_of_seconds;
   		
   		date_default_timezone_set('America/Los_Angeles');
   		return date("Y-m-d H:i:s", $adjusted_time);
   	
   	}
   	
   	public function get_date_time_subtract_sec($number_of_seconds=30)
   	{
   	
   		//many times functions are running for more than a second and many validations are based on time but validations can fail randomly based on server load
   		//if a request takes more than 1 sec to complete. this method allows validations to use a time that matched the php.ini timeout or a custom value
   		$adjusted_time = time() - $number_of_seconds;
   		
   		date_default_timezone_set('America/Los_Angeles');
   		return date("Y-m-d H:i:s", $adjusted_time);
   	   	
   	}
   	
   	public function get_todays_date_mysql_format()
   	{
   	
   		$todays_date	=	$this->convert_string_to_mysql_datetime(date("Y-m-d"));
   		
   		return $todays_date;

   	}
   	public function get_tomorrows_date_mysql_format(){
   		
   		$tomorrow = mktime(0, 0, 0, date("m"), date("d")+1, date("y"));

   		$tomorrows_date	=	$this->convert_string_to_mysql_datetime(date("Y-m-d", $tomorrow));
   		
   		return $tomorrows_date;
   	
   	}
   	
   	public function add_minute($time,$minute){
   		
   		$temp = explode(':',$time);
   		
   		$time = $temp[0]*60*60 + $temp[1]*60 + $temp[2]+ ( ($minute) * 60 );
   		
   		
   		$hour =  floor($time / (60 * 60) );
   		
   		$divisor_for_minutes = $time % (60 * 60);
   		$minutes = floor($divisor_for_minutes / 60);
   		
   		$divisor_for_seconds = $divisor_for_minutes % 60 ;
   		$seconds = ceil($divisor_for_seconds);
   		return  str_pad($hour, 2, "0", STR_PAD_LEFT).':'.str_pad($minutes, 2, "0", STR_PAD_LEFT).':'.str_pad($seconds, 2, "0", STR_PAD_LEFT);
   	}
   	
   	public function subtract_minute($time,$minute){
   		$temp = explode(':',$time);
   		 
   		$time = $temp[0]*60*60 + $temp[1]*60 + $temp[2]- ( ($minute) * 60 );
   		 
   		 
   		$hour =  floor($time / (60 * 60) );
   		 
   		$divisor_for_minutes = $time % (60 * 60);
   		$minutes = floor($divisor_for_minutes / 60);
   		 
   		$divisor_for_seconds = $divisor_for_minutes % 60 ;
   		$seconds = ceil($divisor_for_seconds);
   		return  str_pad($hour, 2, "0", STR_PAD_LEFT).':'.str_pad($minutes, 2, "0", STR_PAD_LEFT).':'.str_pad($seconds, 2, "0", STR_PAD_LEFT);
   		
   	}
   	
   	public function get_current_year($format=null)
   	{
   		if ($format == null) {
   			return date("Y");
   		} elseif($format == 'last2digits') {
   			return date("y");
   		}
   	}
   	
   	public function subtract_time($time1,$time2){
   		
   		$temp1 = explode(':',$time1);
   		$time1 = $temp1[0]*60*60 + $temp1[1]*60 + $temp1[2];
   		
   		$temp2 = explode(':',$time2);
   		$time2 = $temp2[0]*60*60 + $temp2[1]*60 + $temp2[2];
   		
   		$outcome = $time1-$time2;
   		
   		$hour =  floor($outcome / (60 * 60) );
   		
   		$divisor_for_minutes = $outcome % (60 * 60);
   		$minutes = floor($divisor_for_minutes / 60);
   		
   		$divisor_for_seconds = $divisor_for_minutes % 60 ;
   		$seconds = ceil($divisor_for_seconds);
   		return  str_pad($hour, 2, "0", STR_PAD_LEFT).':'.str_pad($minutes, 2, "0", STR_PAD_LEFT).':'.str_pad($seconds, 2, "0", STR_PAD_LEFT);
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