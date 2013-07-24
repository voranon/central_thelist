<?php

//by martin
//exception codes 22300-22399

class thelist_utility_jsonconverter
{
		
	function __construct()
	{

   	}
   	
	public function convert_service_plan_temp_quotes($service_plan_temp_objs=null, $error=null)
   	{
   		
   		if ($service_plan_temp_objs != null || $error != null) {
   			
   			$xml_converter		= new Thelist_Utility_xmlconverter();
   			$xml_obj			= $xml_converter->convert_service_plan_temp_quotes($service_plan_temp_objs, $error);

   			return $jsonContents = Zend_Json::fromXml($xml_obj, true);
   		} else {
   			return null;
   		}
	}
}
?>