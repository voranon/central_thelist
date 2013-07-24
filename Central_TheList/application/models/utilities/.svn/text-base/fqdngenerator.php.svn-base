<?php

//exception codes 8800-8899

class thelist_utility_fqdngenerator
{
		
	function __construct()
	{

   	}

   	public function fqdn_from_equipment($equipment_obj)
   	{
   		$fqdn = '';
   		
   		//get the type because the first part of the fqdn is always the modelname
   		$equipment_type	= $equipment_obj->get_eq_type();
   		
   		//clean up the model name
   		$remove_patterns 	= array(" ");
   		$model_name			= str_replace($remove_patterns, "", strtolower($equipment_type->get_eq_model_name()));
   		
   		if ($equipment_type->get_eq_manufacturer() == 'Mikrotik') {
   			$fqdn = $fqdn . "mt" . $model_name . ".";
   		} elseif ($equipment_type->get_eq_manufacturer() == 'Bel Air Internet') {
   			$fqdn = $fqdn . "bairos.";
   		} else {
   			$fqdn = $fqdn . $model_name;
   		}
   		
   		$equipment_unit	= $equipment_obj->currentEquipmentUnit();

   		//if the equipment is mapped to a unit, else the address is unknown.
   		if ($equipment_unit != false) {
   			
   			$fqdn = $fqdn . $equipment_unit->get_streetnumber() . strtolower(substr($equipment_unit->get_streetname(), 0, 1)) . ".";
   			
   			//last is the location in the building
   			if (preg_match("/^[0-9]+$/", $equipment_unit->get_number())) {
   				$fqdn = $fqdn . "u" . $equipment_unit->get_number() . "." . Thelist_Utility_staticvariables::get_company_domain();
   			} else {
   				$fqdn = $fqdn . 'unknown-location.' . Thelist_Utility_staticvariables::get_company_domain();
   			}

   		} else {
   			$fqdn = $fqdn . 'unknown-address.' . Thelist_Utility_staticvariables::get_company_domain();
   		}
   		
   		//validate the created FQDN
		$this->validate_fqdn($fqdn);
   		
		//if we made it this far, the fqdn is valid, we return it
		return $fqdn;
   	}
   	
   	public function validate_fqdn($fqdn)
   	{
   		//no part can be all numbers
   		$valid = preg_match('/(?=^.{1,254}$)(^(?:(?!\d+\.|-)[a-zA-Z0-9_\-]{1,63}(?<!-)\.?)+(?:[a-zA-Z]{2,})$)/i', $fqdn);

   		if ($valid > 0) {
   			return true;
   		} else {
   			throw new exception('FQDN is not valid', 8800);
   		}
   	}
}
?>