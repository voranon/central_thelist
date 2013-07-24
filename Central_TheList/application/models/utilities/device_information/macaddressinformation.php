<?php

//exception codes 800-899

require_once 'Net/MAC.php';

class thelist_deviceinformation_macaddressinformation 
{
	
	private $_macaddress;
	private $database=null;
	private $_valid_mac=null;
	
	public function __construct($macaddress, $update=null)
	{
		if ($update == 'yes') {
			//update the vendor database?
			$this->updatemacaddressvendordatabase();
		}
		
		//remove junk if there is any
		$patterns = array('\"', '-', '.', ':', ' ', "\r", "\r\n", "\n");
		$mac_address_clean = str_replace($patterns, '', $macaddress);
		$this->_macaddress 		= strtoupper($mac_address_clean);
		
		$this->_valid_mac = Net_MAC::check($this->_macaddress, '');
		
		if (!$this->_valid_mac) {
			throw new exception('mac address is not formatted correctly', 800);
		}
	}
	
	public function get_macaddress()
	{
		return $this->_macaddress;	
	}
	
	public function get_formatted_macaddress($delimiter)
	{
 		return Net_MAC::format($this->_macaddress, "$delimiter", true);
	}

	public function is_valid()
	{
		//legacy, redundant because of the validation in the constructor, but in use in sourcecode
		return $this->_valid_mac;
	}

	public function get_equipment_manufacturer()
	{
		
		$sql=	"SELECT vendorname FROM mac_address_prefixes
				WHERE prefix='".substr($this->_macaddress, 0, 6)."'
				";
		
		$vendor  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		if (isset($vendor['vendorname'])) {
	
			//many of the mac addresses vendors have double spaces
			//and duplicate entries with single space, so we turn all double speces into single space
			//and then we make them all upper case for consistency so we dont have to deal with case sensetive matching
			
			return strtoupper(str_replace('  ', ' ', $vendor));
	
		} else {
	
			return false;
	
		}
	}
	
	
	private function updatemacaddressvendordatabase()
	{
		$credential	= new Thelist_Model_deviceauthenticationcredential();
		$credential->fill_default_values('2');
		$credential->set_specific_connect_class('webserver');
	
		//use the ieee database
		$this->_device	= new Thelist_Model_device('standards.ieee.org', $credential);
		$device_reply = $this->_device->execute_command("develop/regauth/oui/oui.txt");
	
		if ($device_reply->get_code() == '1') {

			preg_match_all('/(.*)\s+\(base 16\)\s+(.*)/', $device_reply->get_message(), $matches);

			//make sure we got records before truncating the database
			if (isset($matches['1']['0'])) {
				
				//$truncate  = Zend_Registry::get('database')->get_thelist_adapter()->query('TRUNCATE TABLE mac_address_prefixes');
					
				$trace 		= debug_backtrace();
				$method 	= $trace[0]["function"];
				$class		= get_class($this);
				

				$patterns1 = array('\"', '.', "\r", "\r\n", "\n");
				$patterns2 = array(',');
				
				//now insert the bulk of records
				
				$i=0;
				foreach ($matches['1'] as $macprefix) {

					$single_mac_prefix	= mysql_real_escape_string($macprefix);
					
					$sql = "SELECT COUNT(mac_address_prefix_id) FROM mac_address_prefixes
							WHERE prefix='".$single_mac_prefix."'
							";
					
					$exist  = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
										
					if ($exist == 0) {
						
						//create the record if it does not exist
						$single_vendor_name = mysql_real_escape_string(str_replace($patterns2, ' ', str_replace($patterns1, '', $matches[2][$i])));
							
						$data = array(
							
													'prefix'    			=>  $single_mac_prefix,
													'vendorname'   			=>  $single_vendor_name,
							
						);
						
						$prefix = Zend_Registry::get('database')->insert_single_row('mac_address_prefixes',$data,$class,$method);
						
					}

					$i++;
					
				}
			}
		}
	}
	
}
?>