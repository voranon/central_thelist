<?php
class thelist_utility_hexconverter
{
	
	function __construct()
	{
		
   	}

   	public function hexdump($data, $htmloutput = true, $uppercase = false, $return = false)
   	{
   		// Init
   		$hexi   = '';
   		$ascii  = '';
   		$dump   = ($htmloutput === true) ? "<pre>\n" : '';
   		$offset = 0;
   		$len    = strlen($data);
   	
   		// Upper or lower case hexadecimal
   		$x = ($uppercase === false) ? 'x' : 'X';
   	
   		// Iterate string
   		for ($i = $j = 0; $i < $len; $i++)
   		{
   		// Convert to hexidecimal
   		$hexi .= sprintf("%02$x ", ord($data[$i]));
   	
   		// Replace non-viewable bytes with '.'
   		if (ord($data[$i]) >= 32) {
   		$ascii .= ($htmloutput === true) ?
   		htmlentities($data[$i]) :
   		$data[$i];
   		} else {
   		$ascii .= '.';
   		}
   	
   			// Add extra column spacing
   		if ($j === 7) {
   		$hexi  .= ' ';
   			$ascii .= ' ';
   		}
   	
   		// Add row
   		if (++$j === 16 || $i === $len - 1) {
   		// Join the hexi / ascii output
   			$dump .= sprintf("%04$x  %-49s  %s", $offset, $hexi, $ascii);
   	
   		// Reset vars
   		$hexi   = $ascii = '';
   		$offset += 16;
   		$j      = 0;
   	
   		// Add newline
   		if ($i !== $len - 1) {
   		$dump .= "\n";
   		}
   		}
   		}
   	
   		// Finish dump
   			$dump .= $htmloutput === true ?
   			
   			"\n</pre>" :
   			'';
    	
   			// Output method
		if ($return === false) {
			echo $dump;
	   	} else {
			return $dump;
	   	}
   	}
}
?>