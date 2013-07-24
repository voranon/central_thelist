<?php

//by martin
//exception codes 22400-22499

class thelist_utility_xmlconverter
{
	
	private $_xml_document=null;
		
	function __construct()
	{
		$this->_xml_document = new DOMDocument();
   	}
   	
	public function convert_service_plan_temp_quotes($service_plan_temp_objs=null, $error=null)
   	{
   		if ($service_plan_temp_objs != null || $error != null) {
   			
	   		//if this method should be reusable for JSON
	   		//the return MUST be contained under a single element
	   		$reply_element = $this->_xml_document->appendChild(
	   		$this->_xml_document->createElement("reply_data"));
	
	   		if ($error != null) {
	   			
	   			$error_element = $reply_element->appendChild(
	   			$this->_xml_document->createElement("error"));
	   			
	   			//validate that both indexes are present so the error is complete
	   			if (isset($error['exception_code'])) {
	   				
	   				$error_element->appendChild(
	   				$this->_xml_document->createElement("exception_code", $error['exception_code']));
	   				
	   			} else {
	   				throw new exception("an error was provided but we are missing the 'exception_code' index ", 22400);
	   			}
	   			
	   			if (isset($error['error_string'])) {
	   					
	   				$error_element->appendChild(
	   				$this->_xml_document->createElement("error_string", $error['error_string']));
	   				
	   			} else {
	   				throw new exception("an error was provided but we are missing the 'error_string' index ", 22401);
	   			}
	   		}
	   		
	   		if ($service_plan_temp_objs != null) {
	   			
	   			if (is_array($service_plan_temp_objs)) {
	   				
	   				if (count($service_plan_temp_objs) > 0) {
	   				
	   					//create the general service plan element
	   					$service_plans_element = $reply_element->appendChild(
	   					$this->_xml_document->createElement("serviceplantempquotes"));
	   				
		   				foreach ($service_plan_temp_objs as $service_plan_temp_obj) {
		
		   					if (is_object($service_plan_temp_obj)) {
		   						
		   						if (get_class($service_plan_temp_obj) == 'thelist_model_serviceplantempquotemap') {
	
		   							$spt_id	= $service_plan_temp_obj->get_service_plan_temp_quote_map_id();
		   							
		   							//create the specific service plan element
		   							$service_plan_temp_element[$spt_id] = $service_plans_element->appendChild(
	   								$this->_xml_document->createElement("spt"));
		   							
		   							//add global information for service plan
		   							
		   							//service plan name
		   							$service_plan_temp_element[$spt_id]->appendChild(
		   							$this->_xml_document->createElement("service_plan_name", $service_plan_temp_obj->get_service_plan()->get_service_plan_name()));
		   								
		   							//not id the view should group by either, but since Name provides more information than id i chose name (id would have to be resolved to name)
		   							//service plan group name
		   							$service_plan_temp_element[$spt_id]->appendChild(
		   							$this->_xml_document->createElement("service_plan_group_name", $service_plan_temp_obj->get_service_plan()->get_service_plan_group_name()));
	
		   							//add global information for specific service plan temp
		   							
		   							//spt id
		   							$service_plan_temp_element[$spt_id]->appendChild(
		   							$this->_xml_document->createElement("service_plan_temp_quote_map_id", $spt_id));
		   							
		   							//spt mrc
		   							$service_plan_temp_element[$spt_id]->appendChild(
		   							$this->_xml_document->createElement("service_plan_temp_quote_actual_mrc", $service_plan_temp_obj->get_service_plan_temp_quote_actual_mrc()));
		   							
		   							//spt nrc
		   							$service_plan_temp_element[$spt_id]->appendChild(
		   							$this->_xml_document->createElement("service_plan_temp_quote_actual_nrc", $service_plan_temp_obj->get_service_plan_temp_quote_actual_nrc()));
		   							
		   							//spt mrc term
		   							$service_plan_temp_element[$spt_id]->appendChild(
		   							$this->_xml_document->createElement("service_plan_temp_quote_actual_mrc_term", $service_plan_temp_obj->get_service_plan_temp_quote_actual_mrc_term()));
		   							
		   							//attach all options that are attached to this service plan temp already
		   							if ($service_plan_temp_obj->get_service_plan_temp_quote_options() != null) {
		   								
		   								//create the display element if it does not already exist
		   								if (!isset($service_plan_temp_display_element[$spt_id])) {
		   										
		   									//add display element
		   									$service_plan_temp_display_element[$spt_id] = $service_plan_temp_element[$spt_id]->appendChild(
		   									$this->_xml_document->createElement("display"));
		   								
		   								}
		   								
		   								//create the current options element
		   								$spt_options_display_element[$spt_id] = $service_plan_temp_display_element[$spt_id]->appendChild(
		   								$this->_xml_document->createElement("options"));
		   								
		   								foreach($service_plan_temp_obj->get_service_plan_temp_quote_options() as $spt_option_obj) {
		   									
		   									$spt_option_map_id	= $spt_option_obj->get_service_plan_temp_quote_option_map_id();
		   									
		   									//create the single option element foreach option
		   									$spt_option_element[$spt_option_map_id] = $spt_options_display_element[$spt_id]->appendChild(
		   									$this->_xml_document->createElement("option"));
		   									
		   									//add global information for service plan option
		   									
		   									$sp_option_map_group 	= $spt_option_obj->get_service_plan_option_map()->get_service_plan_option_group();
		   									$sp_option 				= $spt_option_obj->get_service_plan_option_map()->get_service_plan_option();
		   									
		   									$spt_option_element[$spt_option_map_id]->appendChild(
		   									$this->_xml_document->createElement("service_plan_option_short_desc", $sp_option->get_short_description()));
		   									
		   									//is this option mandetory?
		   									if ($sp_option_map_group->get_service_plan_option_required_quantity() == 1 && $sp_option_map_group->get_service_plan_option_max_quantity() == 1) {
		   										$sp_option_removable	= 0;
		   									} else {
		   										$sp_option_removable	= 1;
		   									}
		   									
		   									$spt_option_element[$spt_option_map_id]->appendChild(
		   									$this->_xml_document->createElement("service_plan_option_removable", $sp_option_removable));
		   									
		   									//add global information for specific service plan temp option
		   									
		   									//service_plan_temp_quote_option_map_id
		   									$spt_option_element[$spt_option_map_id]->appendChild(
		   									$this->_xml_document->createElement("service_plan_temp_quote_option_map_id", $spt_option_map_id));
		   									
		   									//mrc
		   									$spt_option_element[$spt_option_map_id]->appendChild(
		   									$this->_xml_document->createElement("service_plan_temp_quote_option_actual_mrc", $spt_option_obj->get_service_plan_temp_quote_option_actual_mrc()));
		   									
		   									//nrc
		   									$spt_option_element[$spt_option_map_id]->appendChild(
		   									$this->_xml_document->createElement("service_plan_temp_quote_option_actual_nrc", $spt_option_obj->get_service_plan_temp_quote_option_actual_nrc()));
		   									
		   									//term
		   									$spt_option_element[$spt_option_map_id]->appendChild(
		   									$this->_xml_document->createElement("service_plan_temp_quote_option_actual_mrc_term", $spt_option_obj->get_service_plan_temp_quote_option_actual_mrc_term()));
	
		   								}
		   							}
		   							
		   							//attach all eq types that are attached to this service plan temp already
		   							if ($service_plan_temp_obj->get_service_plan_temp_quote_eq_types() != null) {
		   							
		   								//create the display element if it does not already exist
		   								if (!isset($service_plan_temp_display_element[$spt_id])) {
		   								
		   									//add display element
		   									$service_plan_temp_display_element[$spt_id] = $service_plan_temp_element[$spt_id]->appendChild(
		   									$this->_xml_document->createElement("display"));
		   								
		   								}
		   								
		   								//create the current eq types element
		   								$spt_eq_types_display_element[$spt_id] = $service_plan_temp_display_element[$spt_id]->appendChild(
		   								$this->_xml_document->createElement("eq_types"));
		   							
		   								foreach($service_plan_temp_obj->get_service_plan_temp_quote_eq_types() as $spt_eq_type_obj) {
		   										
		   									$spt_eq_type_map_id	= $spt_eq_type_obj->get_service_plan_temp_quote_eq_type_map_id();
		   										
		   									//create the single eq_type element foreach eq type
		   									$spt_eq_type_element[$spt_eq_type_map_id] = $spt_eq_types_display_element[$spt_id]->appendChild(
		   									$this->_xml_document->createElement("eq_type"));
		   										
		   									//add global information for service plan eq type
		   										
		   									$sp_eq_type_map_group 	= $spt_eq_type_obj->get_service_plan_eq_type_map()->get_service_plan_eq_type_group();
		   									$sp_eq_type_group		= $spt_eq_type_obj->get_service_plan_eq_type_map()->get_eq_type_group();
		   										
		   									$spt_eq_type_element[$spt_eq_type_map_id]->appendChild(
		   									$this->_xml_document->createElement("service_plan_eq_type_short_desc", $sp_eq_type_group->get_eq_type_group_short_description()));
		   										
		   									//is this eq type mandetory?
		   									if ($sp_eq_type_map_group->get_service_plan_eq_type_required_quantity() == 1 && $sp_eq_type_map_group->get_service_plan_eq_type_max_quantity() == 1) {
		   										$sp_eq_type_removable	= 0;
		   									} else {
		   										$sp_eq_type_removable	= 1;
		   									}
		   										
		   									$spt_eq_type_element[$spt_eq_type_map_id]->appendChild(
		   									$this->_xml_document->createElement("service_plan_eq_type_removable", $sp_eq_type_removable));
		   										
		   									//add global information for specific service plan temp eq type
		   										
		   									//service_plan_temp_quote_eq_type_map_id
		   									$spt_eq_type_element[$spt_eq_type_map_id]->appendChild(
		   									$this->_xml_document->createElement("service_plan_temp_quote_eq_type_map_id", $spt_eq_type_map_id));
		   										
		   									//mrc
		   									$spt_eq_type_element[$spt_eq_type_map_id]->appendChild(
		   									$this->_xml_document->createElement("service_plan_temp_quote_eq_type_actual_mrc", $spt_eq_type_obj->get_service_plan_temp_quote_eq_type_actual_mrc()));
		   										
		   									//nrc
		   									$spt_eq_type_element[$spt_eq_type_map_id]->appendChild(
		   									$this->_xml_document->createElement("service_plan_temp_quote_eq_type_actual_nrc", $spt_eq_type_obj->get_service_plan_temp_quote_eq_type_actual_nrc()));
		   										
		   									//term
		   									$spt_eq_type_element[$spt_eq_type_map_id]->appendChild(
		   									$this->_xml_document->createElement("service_plan_temp_quote_eq_type_actual_mrc_term", $spt_eq_type_obj->get_service_plan_temp_quote_eq_type_actual_mrc_term()));
		   							
		   								}
		   							}
		   							
		   							//attach all sp options that have not reached their max allowed 
		   							if ($service_plan_temp_obj->get_service_plan()->get_service_plan_option_maps() != null) {
		   							
		   								foreach($service_plan_temp_obj->get_service_plan()->get_service_plan_option_maps() as $sp_option_map_obj) {
	
		   									$sp_option_map_id		= $sp_option_map_obj->get_service_plan_option_map_id();
		   									$option_fulfilled		= $service_plan_temp_obj->service_plan_option_map_requirement_fulfilled($sp_option_map_id);
		   									$sp_option_map_group_id = $sp_option_map_obj->get_service_plan_option_group()->get_service_plan_option_group_id();
		   									
		   									if ($option_fulfilled != 'max') {
	
		   										//create the choices element if it does not already exist
		   										if (!isset($sp_choices_element[$spt_id])) {
	
		   											$sp_choices_element[$spt_id] = $service_plan_temp_element[$spt_id]->appendChild(
		   											$this->_xml_document->createElement("choices"));
		   											
		   										}
		   										
		   										//create the OPTION choices element if it does not already exist
		   										if (!isset($sp_choice_options_element[$spt_id])) {
		   										
		   											$sp_choice_options_element[$spt_id] = $sp_choices_element[$spt_id]->appendChild(
		   											$this->_xml_document->createElement("options"));
		   												
		   										}
		   										
		   										if (!isset($sp_choice_option_group_element[$sp_option_map_group_id])) {
		   												
		   											$sp_choice_option_group_element[$sp_option_map_group_id] = $sp_choice_options_element[$spt_id]->appendChild(
		   											$this->_xml_document->createElement("option_group"));
		   												
		   										}
		   										
		   										//create the element for this single choice
		   										$sp_single_choice_option_element[$sp_option_map_id] = $sp_choice_option_group_element[$sp_option_map_group_id]->appendChild(
		   										$this->_xml_document->createElement("option"));
		   										
		   										//service_plan_option_map_id
		   										$sp_single_choice_option_element[$sp_option_map_id]->appendChild(
		   										$this->_xml_document->createElement("service_plan_option_map_id", $sp_option_map_id));
		   										
		   										//is this option requirement fulfilled
		   										if ($option_fulfilled == 'no') {
		   											$sp_option_fulfilled	= 0;
		   										} else {
		   											$sp_option_fulfilled	= 1;
		   										}
		   										
		   										//service_plan option desc
		   										$sp_single_choice_option_element[$sp_option_map_id]->appendChild(
		   										$this->_xml_document->createElement("service_plan_option_short_description", $sp_option_map_obj->get_service_plan_option()->get_short_description()));
		   										
	
		   										//service_plan_option_map_id
		   										$sp_single_choice_option_element[$sp_option_map_id]->appendChild(
		   										$this->_xml_document->createElement("service_plan_option_requirement_fulfilled", $sp_option_fulfilled));
		   										
		   										//mrc
		   										$sp_single_choice_option_element[$sp_option_map_id]->appendChild(
		   										$this->_xml_document->createElement("service_plan_option_default_mrc", $sp_option_map_obj->get_service_plan_option_default_mrc()));
		   										
		   										//nrc
		   										$sp_single_choice_option_element[$sp_option_map_id]->appendChild(
		   										$this->_xml_document->createElement("service_plan_option_default_nrc", $sp_option_map_obj->get_service_plan_option_default_nrc()));
		   										
		   										//term
		   										$sp_single_choice_option_element[$sp_option_map_id]->appendChild(
		   										$this->_xml_document->createElement("service_plan_option_default_mrc_term", $sp_option_map_obj->get_service_plan_option_default_mrc_term()));
	
		   									}
		   								}
		   							}
		   							
		   							//attach all sp eq types that have not reached their max allowed
		   							if ($service_plan_temp_obj->get_service_plan()->get_service_plan_eq_type_maps() != null) {
		   									
		   								foreach($service_plan_temp_obj->get_service_plan()->get_service_plan_eq_type_maps() as $sp_eq_type_map_obj) {
		   							
		   									$sp_eq_type_map_id			= $sp_eq_type_map_obj->get_service_plan_eq_type_map_id();
		   									$sp_eq_type_map_group_id 	= $sp_eq_type_map_obj->get_service_plan_eq_type_group()->get_service_plan_eq_type_group_id();
		   										
		   									$eq_type_fulfilled = $service_plan_temp_obj->service_plan_eq_type_map_requirement_fulfilled($sp_eq_type_map_id);
		   										
		   									if ($eq_type_fulfilled != 'max') {
		   							
		   										//create the choices element if it does not already exist
		   										if (!isset($sp_choices_element[$spt_id])) {
		   							
		   											$sp_choices_element[$spt_id] = $service_plan_temp_element[$spt_id]->appendChild(
		   											$this->_xml_document->createElement("choices"));
		   												
		   										}
		   							
		   										//create the EQ TYPE choices element if it does not already exist
		   										if (!isset($sp_choice_eq_types_element[$spt_id])) {
		   							
		   											$sp_choice_eq_types_element[$spt_id] = $sp_choices_element[$spt_id]->appendChild(
		   											$this->_xml_document->createElement("eq_types"));
		   							
		   										}
		   										
		   										if (!isset($sp_choice_eq_types_group_element[$sp_eq_type_map_group_id])) {
		   										
		   											$sp_choice_eq_types_group_element[$sp_eq_type_map_group_id] = $sp_choice_eq_types_element[$spt_id]->appendChild(
		   											$this->_xml_document->createElement("eq_type_group"));
		   										
		   										}
		   							
		   										//create the element for this single choice
		   										$sp_single_choice_eq_type_element[$sp_eq_type_map_id] = $sp_choice_eq_types_group_element[$sp_eq_type_map_group_id]->appendChild(
		   										$this->_xml_document->createElement("eq_type"));
		   							
		   										//service_plan_eq_type_map_id
		   										$sp_single_choice_eq_type_element[$sp_eq_type_map_id]->appendChild(
		   										$this->_xml_document->createElement("service_plan_eq_type_map_id", $sp_eq_type_map_id));
		   							
		   										//is this option requirement fulfilled
		   										if ($eq_type_fulfilled == 'no') {
		   											$sp_eq_type_fulfilled	= 0;
		   										} else {
		   											$sp_eq_type_fulfilled	= 1;
		   										}
		   										
		   										//service_plan_eq_type filfilled
		   										$sp_single_choice_eq_type_element[$sp_eq_type_map_id]->appendChild(
		   										$this->_xml_document->createElement("service_plan_eq_type_requirement_fulfilled", $sp_eq_type_fulfilled));
	
		   										//service_plan_eq_type group desc
		   										$sp_single_choice_eq_type_element[$sp_eq_type_map_id]->appendChild(
		   										$this->_xml_document->createElement("eq_type_group_short_description", $sp_eq_type_map_obj->get_eq_type_group()->get_eq_type_group_short_description()));
		   										
		   										
		   										//mrc
		   										$sp_single_choice_eq_type_element[$sp_eq_type_map_id]->appendChild(
		   										$this->_xml_document->createElement("service_plan_eq_type_default_mrc", $sp_eq_type_map_obj->get_service_plan_eq_type_default_mrc()));
		   							
		   										//nrc
		   										$sp_single_choice_eq_type_element[$sp_eq_type_map_id]->appendChild(
		   										$this->_xml_document->createElement("service_plan_eq_type_default_nrc", $sp_eq_type_map_obj->get_service_plan_eq_type_default_nrc()));
		   							
		   										//term
		   										$sp_single_choice_eq_type_element[$sp_eq_type_map_id]->appendChild(
		   										$this->_xml_document->createElement("service_plan_eq_type_default_mrc_term", $sp_eq_type_map_obj->get_service_plan_eq_type_default_mrc_term()));
		   							
		   									}
		   								}
		   							}
		   							
		   						} else {
		   							throw new exception("we expect the input 'service_plan_temp_objs' to be an array of 'serviceplantempquotemap' objects, atleast one element was a '".get_class($service_plan_temp_obj)."' object " , 22402);
		   						}
		   						
		   					} else {
		   						throw new exception("we expect the input 'service_plan_temp_objs' to be an array of 'serviceplantempquotemap' objects, atleast one element was not an object " , 22403);
		   					}
		   				}
		   				
	   				} else {
	   					throw new exception("we expect the input 'service_plan_temp_objs' to be an array of 'serviceplantempquotemap' objects, we got an empty array " , 22404);
	   				}
	   				
	   			} else {
	   				throw new exception("we expect the input 'service_plan_temp_objs' to be an array", 22405);
	   			}
	   			
	   		}
	
	   		//make it pretty
	   		$this->_xml_document->formatOutput = true;
	
	   		return $this->_xml_document->saveXML();
   		} else {
   			return null;
   		}
	}
}
?>