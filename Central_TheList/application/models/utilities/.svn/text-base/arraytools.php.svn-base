<?php

//exception codes 14200-14299

class thelist_utility_arraytools
{
	private $_original_array1=null;
	private $_original_array2=null;
	
	private $_current_array_index_1=array();
	private $_current_array_index_2=array();
	
	private $_items_remaining_in_current_array_index_1=null;
	
	private $_completed_array_items=null;
	
	private $_counter=0;
	
	private $_time=null;
	
	function __construct()
	{
		
   	}
   	
   	public function sort_ip_subnets_by_cidr($subnets)
   	{
   		//method returns biggest first, smallest last in return array
   		
   		if (is_array($subnets)) {

   			$i=0;
   			while (count($subnets) > 0 && $i <= 32 ) {
   	
   				foreach ($subnets as $index => $subnet) {
   						
   					if ($subnet->get_ip_subnet_cidr_mask() == $i) {
   						
   						$return_array[] = $subnet;
   						unset($subnets[$index]);
   					}
   				}
   				$i++;
   			}
   				
   			return $return_array;
   				
   		} else {
   			throw new exception("method requires an array", 14201);
   		}
   	}
   	
   	public function convert_mixed_array_to_strings($array)
   	{
   		if (is_array($array)) {
   			
   			foreach($array as $key => $value) {
   				
   				$this->_current_array_index_1[] = $key;
   				
   				if (is_object($value)) {
   					
   					if (method_exists($value, 'toArray')) {
   						$this->set_completed_array_item($value->toArray());
   					} else {
   						$this->set_completed_array_item('CLASS IS MISSING toArray METHOD');
   					}
   					
   				} elseif (is_array($value)) {
   					$this->convert_mixed_array_to_strings($value); //recurse
   					
   				} else {
   					//its a string
   					$this->set_completed_array_item($value);
   				}
   				
   				array_pop($this->_current_array_index_1);
   				
   			}
   		}
   		
   		return $this->_completed_array_items;
   	}
   	
   	public function convert_occurrences_of_datetime_in_array($array, $time_format)
   	{
   		if ($this->_time == null) {
   			$this->_time	= new Thelist_Utility_time();
   			
   			//check if the input time format is supported by the time function
   			//using generic mysql_time_input
   			$format_suported = $this->_time->format_date_time('2012-01-01 01:01:01', $time_format);
   			
   			if ($format_suported == false) {
   				throw new exception("unknown date-time format", 14203);
   			}
   		}

   		if (is_array($array)) {
   	
   			foreach($array as $key => $value) {
   					
   				$this->_current_array_index_1[] = $key;
   					
   				if (is_object($value)) {
   					//we cant convert time in objects, but we dont get rid of them
   					$this->set_completed_array_item($value);
   	
   				} elseif (is_array($value)) {
   					$this->convert_occurrences_of_datetime_in_array($value, $time_format); //recurse
   	
   				} else {
   					//its a string
   					
   					if ($this->_time->is_date_time($value)) {
   						$this->set_completed_array_item($this->_time->format_date_time($value, $time_format));
   					} else {
   						$this->set_completed_array_item($value);
   					}
   				}
   					
   				array_pop($this->_current_array_index_1);
   			}
   		}
   		 
   		return $this->_completed_array_items;
   	}
   	
   	private function set_completed_array_item($value)
   	{
   		//there must be a better way than this
   		$index_depth_in_array_1 = count($this->_current_array_index_1);

   		if ($index_depth_in_array_1 == 0) {
   			//do nothing
   		} elseif ($index_depth_in_array_1 == 1) {
   			$this->_completed_array_items[$this->_current_array_index_1['0']] = $value;
   		} elseif ($index_depth_in_array_1 == 2) {
   			$this->_completed_array_items[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']] = $value;
   		} elseif ($index_depth_in_array_1 == 3) {
   			$this->_completed_array_items[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']] = $value;
   		} elseif ($index_depth_in_array_1 == 4) {
   			$this->_completed_array_items[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']] = $value;
   		} elseif ($index_depth_in_array_1 == 5) {
   			$this->_completed_array_items[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']][$this->_current_array_index_1['4']] = $value;
   		} elseif ($index_depth_in_array_1 == 6) {
   			$this->_completed_array_items[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']][$this->_current_array_index_1['4']][$this->_current_array_index_1['5']] = $value;
   		} elseif ($index_depth_in_array_1 == 7) {
   			$this->_completed_array_items[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']][$this->_current_array_index_1['4']][$this->_current_array_index_1['5']][$this->_current_array_index_1['6']] = $value;
   		} elseif ($index_depth_in_array_1 == 8) {
   			$this->_completed_array_items[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']][$this->_current_array_index_1['4']][$this->_current_array_index_1['5']][$this->_current_array_index_1['6']][$this->_current_array_index_1['7']] = $value;
   		} elseif ($index_depth_in_array_1 == 9) {
   			$this->_completed_array_items[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']][$this->_current_array_index_1['4']][$this->_current_array_index_1['5']][$this->_current_array_index_1['6']][$this->_current_array_index_1['7']][$this->_current_array_index_1['8']] = $value;
   		} elseif ($index_depth_in_array_1 == 10) {
   			$this->_completed_array_items[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']][$this->_current_array_index_1['4']][$this->_current_array_index_1['5']][$this->_current_array_index_1['6']][$this->_current_array_index_1['7']][$this->_current_array_index_1['8']][$this->_current_array_index_1['9']] = $value;
   		} else {
   			throw new exception("need more depth in the array index for setting values", 14202);
   		}
   	}

	public function reset_index_values_recursive($array)
	{
		if (is_array($array)) {
			foreach ($array as $k => $val) {
				if (is_array($val))
					$array[$k] = $this->reset_index_values_recursive($val); //recurse
				}
			
			return array_values($array);
		} 
	}
	
	public function compare_multi_dimentional_arrays($array1, $array2)
	{	
		//expand to cover objects
		
		if (is_array($array1)) {
			
			//set the first array that is being provided
			if ($this->_original_array1 == null && $this->_original_array2 == null) {
				$this->_original_array1 = $array1;
			}
			
			if (is_array($array2)) {
				
				//set the second array that is being provided
				if ($this->_original_array2 == null) {
					$this->_original_array2 = $array2;
				}
				
				foreach($array1 as $index1 => $value1) {

					$this->_current_array_index_1[] = $index1;
					
					foreach($array2 as $index2 => $value2) {
						
						$this->_current_array_index_2[] = $index2;

						//if both indexes are numeric then compare all index1
						//to all index2
						if (is_numeric($index1) && is_numeric($index2)) {

							if (is_array($value1) && is_array($value2)) {
								
								$this->compare_multi_dimentional_arrays($value1, $value2); //recurse
								
							} else {
								
								if (!is_array($value1)) {

									if ($value1 == $value2) {

										$this->unset_original_array_1_item();
									}
								} 
							}
							
						} elseif ($index1 == $index2) {

							if (is_array($value1) && is_array($value2)) {
							
								$this->compare_multi_dimentional_arrays($value1, $value2); //recurse
							
							} else {
								
								if (!is_array($value1)) {

									if ($value1 == $value2) {
										$this->unset_original_array_1_item();
									}
								} 
							}
						}

						//remove last index
						array_pop($this->_current_array_index_2);
					}
					
					//remove last index
					array_pop($this->_current_array_index_1);
					
					//then check if the index is empty, and remove it if it is
					if ($this->_items_remaining_in_current_array_index_1 == 0) {
						$this->unset_original_array_1_item();
					}
				}
			}
		}
		
		if (isset($this->_original_array1)) {
			return $this->_original_array1;
		} else {
			return false;
		}
	}
	
	private function unset_original_array_1_item($index_depth_in_array_1=false)
	{
		//need to reset the index array so i can get to the correct index and unset it
		//there must be a better way than this
		array_values($this->_current_array_index_1);
		
		if ($index_depth_in_array_1 === false) {
			$index_depth_in_array_1 = count($this->_current_array_index_1);
		} 

		if ($index_depth_in_array_1 == 0) {
			
			if (isset($this->_original_array1)) {
				unset($this->_original_array1);
			}
			$this->_items_remaining_in_current_array_index_1 = 0;
			
		} elseif ($index_depth_in_array_1 == 1) {
			
			if (isset($this->_original_array1[$this->_current_array_index_1['0']])) {
				unset($this->_original_array1[$this->_current_array_index_1['0']]);
			}
			$this->_items_remaining_in_current_array_index_1 = count($this->_original_array1);
			
		} elseif ($index_depth_in_array_1 == 2) {
			
			if (isset($this->_original_array1[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']])) {
				unset($this->_original_array1[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']]);
			}
			$this->_items_remaining_in_current_array_index_1 = count($this->_original_array1[$this->_current_array_index_1['0']]);
			
		} elseif ($index_depth_in_array_1 == 3) {
			
			if (isset($this->_original_array1[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']])) {
				unset($this->_original_array1[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']]);
			}
			$this->_items_remaining_in_current_array_index_1 = count($this->_original_array1[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']]);
			
		} elseif ($index_depth_in_array_1 == 4) {
			
			if (isset($this->_original_array1[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']])) {
				unset($this->_original_array1[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']]);
			}
			$this->_items_remaining_in_current_array_index_1 = count($this->_original_array1[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']]);
			
		} elseif ($index_depth_in_array_1 == 5) {
			
			if (isset($this->_original_array1[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']][$this->_current_array_index_1['4']])) {
				unset($this->_original_array1[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']][$this->_current_array_index_1['4']]);
			}
			$this->_items_remaining_in_current_array_index_1 = count($this->_original_array1[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']]);
			
		} elseif ($index_depth_in_array_1 == 6) {
			
			if (isset($this->_original_array1[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']][$this->_current_array_index_1['4']][$this->_current_array_index_1['5']])) {
				unset($this->_original_array1[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']][$this->_current_array_index_1['4']][$this->_current_array_index_1['5']]);
			}
			$this->_items_remaining_in_current_array_index_1 = count($this->_original_array1[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']][$this->_current_array_index_1['4']]);
			
		} elseif ($index_depth_in_array_1 == 7) {
			
			if (isset($this->_original_array1[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']][$this->_current_array_index_1['4']][$this->_current_array_index_1['5']][$this->_current_array_index_1['6']])) {
				unset($this->_original_array1[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']][$this->_current_array_index_1['4']][$this->_current_array_index_1['5']][$this->_current_array_index_1['6']]);
			}
			$this->_items_remaining_in_current_array_index_1 = count($this->_original_array1[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']][$this->_current_array_index_1['4']][$this->_current_array_index_1['5']]);
			
		} elseif ($index_depth_in_array_1 == 8) {
			
			if (isset($this->_original_array1[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']][$this->_current_array_index_1['4']][$this->_current_array_index_1['5']][$this->_current_array_index_1['6']][$this->_current_array_index_1['7']])) {
				unset($this->_original_array1[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']][$this->_current_array_index_1['4']][$this->_current_array_index_1['5']][$this->_current_array_index_1['6']][$this->_current_array_index_1['7']]);
			}
			$this->_items_remaining_in_current_array_index_1 = count($this->_original_array1[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']][$this->_current_array_index_1['4']][$this->_current_array_index_1['5']][$this->_current_array_index_1['6']]);
			
		} elseif ($index_depth_in_array_1 == 9) {
			
			if (isset($this->_original_array1[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']][$this->_current_array_index_1['4']][$this->_current_array_index_1['5']][$this->_current_array_index_1['6']][$this->_current_array_index_1['7']][$this->_current_array_index_1['8']])) {
				unset($this->_original_array1[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']][$this->_current_array_index_1['4']][$this->_current_array_index_1['5']][$this->_current_array_index_1['6']][$this->_current_array_index_1['7']][$this->_current_array_index_1['8']]);
			}
			$this->_items_remaining_in_current_array_index_1 = count($this->_original_array1[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']][$this->_current_array_index_1['4']][$this->_current_array_index_1['5']][$this->_current_array_index_1['6']][$this->_current_array_index_1['7']]);
			
		} elseif ($index_depth_in_array_1 == 10) {
			
			if (isset($this->_original_array1[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']][$this->_current_array_index_1['4']][$this->_current_array_index_1['5']][$this->_current_array_index_1['6']][$this->_current_array_index_1['7']][$this->_current_array_index_1['8']][$this->_current_array_index_1['9']])) {
				unset($this->_original_array1[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']][$this->_current_array_index_1['4']][$this->_current_array_index_1['5']][$this->_current_array_index_1['6']][$this->_current_array_index_1['7']][$this->_current_array_index_1['8']][$this->_current_array_index_1['9']]);
			}
			$this->_items_remaining_in_current_array_index_1 = count($this->_original_array1[$this->_current_array_index_1['0']][$this->_current_array_index_1['1']][$this->_current_array_index_1['2']][$this->_current_array_index_1['3']][$this->_current_array_index_1['4']][$this->_current_array_index_1['5']][$this->_current_array_index_1['6']][$this->_current_array_index_1['7']][$this->_current_array_index_1['8']]);
			
		} else {
			throw new exception("need more depth in the array index for setting values", 14200);
		}
	}

}
?>