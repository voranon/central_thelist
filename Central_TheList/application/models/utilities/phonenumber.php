<?php

class thelist_utility_phonenumber
{
	private $_current_date_time;
		
	function __construct()
	{

   	}
   	
	public function convert_mysql_number_to_standard_display($mysql_number)
   	{
		if (preg_match( '/^(\d{3})(\d{3})(\d{4})$/', $mysql_number,  $matches) ){

			$result = '('.$matches[1].') '.$matches[2].'-'.$matches[3];
			
			return $result;
		}
	}

   	
   	public function convert_standard_display_to_mysql_number($standard_display)
   	{
   	
   		return date("Y-m-d H:i:s", strtotime($am_pm_datetime));
   	
   	}
}
?>