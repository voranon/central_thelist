<?php

class serviceplaniftooptionmapform extends Zend_Form{

	public function __construct($function_type, $variable)
	{
		parent::__construct($function_type, $variable);

	$service_plan_option_map = new Zend_Form_Element_Select('service_plan_option_map_id',
								array(
										'label'      	=> 'Service Plan Option:',
										'decorators' 	=> array(new serviceplaniftooptionmapdecorator($function_type, $variable)
	)
	)
	);
	$service_plan_option_map->setRegisterInArrayValidator(false);
	$service_plan_option_map->setRequired(true);

	$service_plan_eq_type_map = new Zend_Form_Element_Select('service_plan_eq_type_map_id',
	array(
										'label'      	=> 'Equipment:',
										'decorators' 	=> array(new serviceplaniftooptionmapdecorator($function_type, $variable)
	)
	)
	);
	$service_plan_eq_type_map->setRegisterInArrayValidator(false);

	$static_if_type = new Zend_Form_Element_Select('static_if_type_id',
	array(
										'label'      	=> 'Interface:',
										'decorators' 	=> array(new serviceplaniftooptionmapdecorator($function_type, $variable)
	)
	)
	);
	$static_if_type->setRegisterInArrayValidator(false);
		
	if ($function_type == 'add') {	
		
	$create = new Zend_Form_Element_Submit('create',
	array(
										'label' => 'Create', 'value' => 'C',
										'decorators' => array(new serviceplaniftooptionmapdecorator($function_type, $variable)
	)
	)
	);
	
			$this->addElements(array(
			$service_plan_option_map,
			$service_plan_eq_type_map,
			$static_if_type,
			$create)
			);
		
	} elseif ($function_type == 'edit') {
		
	$edit = new Zend_Form_Element_Submit('edit',
	array(
										'label' => 'Edit', 'value' => 'E',
										'decorators' => array(new serviceplaniftooptionmapdecorator($function_type, $variable)
	)
	)
	);
		
	$delete = new Zend_Form_Element_Submit('delete',
	array(
										'label' => 'Delete', 'value' => 'D',
										'decorators' => array(new serviceplaniftooptionmapdecorator($function_type, $variable)
	)
	)
	);
		
			$this->addElements(array(
			$service_plan_option_map,
			$service_plan_eq_type_map,
			$static_if_type,
			$edit,
			$delete)
			);
	
		}	
	}
}


class serviceplaniftooptionmapdecorator extends Zend_Form_Decorator_Abstract
{
	
	private $database;
	private $_function_type;
	private $_variable;
	
	public function __construct($function_type=null, $variable=null){


		
		if ($function_type != null && $variable != null) {
				
			$this->_function_type = $function_type;
			$this->_variable = $variable;

		}
	
	}
	
	public function render($content)
	{
		$element = $this->getElement();
		$messages = $element->getMessages();
		$name    = htmlentities($element->getFullyQualifiedName());
		$label   = htmlentities($element->getLabel());
		$id      = htmlentities($element->getId());
		$value   = htmlentities($element->getValue());

		if ($this->_function_type == 'edit') {
				
			$sql=	"SELECT * FROM service_plan_eq_type_if_option_mapping spetom
					LEFT OUTER JOIN service_plan_eq_type_mapping etm ON etm.service_plan_eq_type_map_id=spetom.service_plan_eq_type_map_id
					LEFT OUTER JOIN equipment_types et ON et.eq_type_id=etm.eq_type_id
					LEFT OUTER JOIN service_plan_option_mapping spom ON spom.service_plan_option_map_id=spetom.service_plan_option_map_id
					LEFT OUTER JOIN service_plan_options spo ON spo.service_plan_option_id=spom.service_plan_option_id
					WHERE spetom.service_plan_eq_type_if_option_map_id='".$this->_variable."'
					";
				
			$service_plan_eq_if_option_map = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
			$service_plan_id = $service_plan_eq_if_option_map['service_plan_id'];
			
		} else if ($this->_function_type == 'add') {
			
			$service_plan_id = $this->_variable;
		}
		
		if($name == 'service_plan_option_map_id'){

			$format=	"<fieldset><legend>Create Service Plan Option To Interface Mapping</legend><table style='width:750px'>
					<tr><td>%s</td><td>
					<select name='%s' id='%s' style='width: 500px'>";
			
			if ($this->_function_type == 'edit') {
				
				$sql1 = "SELECT * FROM service_plan_options
						WHERE service_plan_option_id='".$service_plan_eq_if_option_map['service_plan_option_id']."'
						";
									
				$service_plan_option = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql1);
				
				//remove all empty and too detailed coulumns from the result				
					foreach ($service_plan_option as $key => $value) {
							
						if ($key == 'activate' || $key == 'deactivate' || $value == '' || $value == null) {

							unset ($service_plan_option[$key]);
								
						}
							
					}
					
					$format.= "<option value='".$service_plan_eq_if_option_map['service_plan_option_map_id']."'>";
					
					foreach ($service_plan_option as $key => $value) {
					
						if ($key == 'service_plan_option_type') {
								
							$sql2 =	"SELECT item_value FROM items
									WHERE item_id='".$value."'
													";
								
							$option_type_name = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
								
							$format.="".$option_type_name." - ";
								
						} elseif (preg_match("/internet/", $key, $empty) || preg_match("/directv/", $key, $empty)) {
					
							$format.= "".$value." | ";
					
						} elseif ($key != 'service_plan_option_map_id' && $key != 'service_plan_option_id') {
					
							$format.= "".$key." - ".$value." | ";
								
						}
					}
					$format.= "</option>";
	
			} elseif ($this->_function_type == 'add') {
				
				$format.=	"<option value=''>-----Select One----</option>";
				
			}

			$sql1 = "SELECT spom.service_plan_option_map_id, spo.* FROM service_plan_option_mapping spom
					LEFT OUTER JOIN service_plan_options spo ON spo.service_plan_option_id=spom.service_plan_option_id
					WHERE spom.service_plan_id='".$service_plan_id."'
					";
		
			$service_plan_options = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql1);
			
			//remove all empty and too detailed coulumns from the result
			$i=0; 
			foreach ($service_plan_options as $service_plan_option) {
				
				foreach ($service_plan_option as $key => $value) {
					
					if ($key == 'activate' || $key == 'deactivate' || $value == '' || $value == null) {
					
						unset ($service_plan_options[$i][$key]);
							
					}
					
				}
				$i++;
			}
			//the new clear array can be turned into a dropdown.

			$i=0;
			foreach($service_plan_options as $service_plan_option){
		
				$format.= "<option value='".$service_plan_option['service_plan_option_map_id']."'>";
				
				foreach ($service_plan_option as $key => $value) {

					if ($key == 'service_plan_option_type') {
					
						$sql2 =	"SELECT item_value FROM items
								WHERE item_id='".$value."'
								";
							
						$option_type_name = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
							
						$format.="".$option_type_name." - ";
					
					} elseif (preg_match("/internet/", $key, $empty) || preg_match("/directv/", $key, $empty)) {
						
						$format.= "".$value." | ";
						
					} elseif ($key != 'service_plan_option_map_id' && $key != 'service_plan_option_id') {
						
						$format.= "".$key." - ".$value." | ";
					
					}

				}
				$format.= "</option>";
			}

				$format.="</select></td><td>%s</td></tr>";
			
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
			
		} else if($name == 'service_plan_eq_type_map_id'){

			$format=	"<tr><td>%s</td><td>
						<select name='%s' id='%s' style='width: 500px'>";
			
			if ($this->_function_type == 'edit') {

				$format.=	"<option value='".$service_plan_eq_if_option_map['service_plan_eq_type_map_id']."'>".$service_plan_eq_if_option_map['eq_manufacturer']." ".$service_plan_eq_if_option_map['eq_model_name']."</option>";

			} elseif ($this->_function_type == 'add') {
			
				$format.=	"<option value=''>-----Select One----</option>";
			
			}
		
			$sql1 = 	"SELECT * FROM service_plan_eq_type_mapping spetm
						LEFT OUTER JOIN equipment_types et ON et.eq_type_id=spetm.eq_type_id
						WHERE spetm.service_plan_id='".$service_plan_id."'
						ORDER BY et.eq_manufacturer ASC
						";
		
			$service_plan_eq_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql1);

			foreach ($service_plan_eq_types as $service_plan_eq_type) {
				
				$format.=	"<option value='".$service_plan_eq_type['service_plan_eq_type_map_id']."'>".$service_plan_eq_type['eq_manufacturer']." ".$service_plan_eq_type['eq_model_name']."</option>";
				
			}

				$format.="</select></td><td>%s</td></tr>";
			
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
		
		} else if($name == 'static_if_type_id'){
			$format=	"<tr><td>%s</td><td>
						<select name='%s' id='%s' style='width: 500px'>";
			
			if ($this->_function_type == 'edit') {
				
				$sql = "SELECT * FROM static_if_types sit
						LEFT OUTER JOIN interface_types it ON it.if_type_id=sit.if_type_id
						WHERE sit.static_if_type_id='".$service_plan_eq_if_option_map['static_if_type_id']."'
						";
				
				$interface_type_orig = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

				
				$format.="<option value='".$service_plan_eq_if_option_map['static_if_type_id']."'>".$interface_type_orig['if_type_name']."</option>";
								
			} else if ($this->_function_type == 'add') {
				
				$format.=	"<option value=''>-----Select One----</option>";
				
			}
			
// 			$sql15 = 	"SELECT * FROM static_if_types sit
// 						LEFT OUTER JOIN interface_types it ON it.if_type_id=sit.if_type_id
// 						LEFT OUTER JOIN equipment_types et ON et.eq_type_id=sit.eq_type_id
// 						WHERE et.eq_type_id='19'
// 						"; 
			
// 			$interface_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql15);
			
// 			foreach ($interface_types as $interface_type) {
				
// 				$format.="<option value='".$interface_type['static_if_type_id']."'>".$interface_type['if_type_name']."</option>";
				
// 			}
			
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
		}
			
		if ($this->_function_type == 'add') {
			if($name == 'create'){
				$format="			<tr><td colspan='3' align='center'>
						
											<input name='%s' id='%s' type='submit' class='button' value='Create'></input>
											<input name='service_plan_id' type='hidden' value='".$this->_variable."'></input>
											
											</td></tr></table></fieldset>
						";	
		
				return sprintf($format,$name,$name);
			}
			
		} else if ($this->_function_type == 'edit') {
			
			if($name == 'edit'){
				$format="			<tr><td colspan='3' align='center'>
				
									<input name='%s' id='%s' type='submit' class='button' value='Edit'></input>
									<input name='service_plan_eq_type_if_option_map_id' type='hidden' value='".$this->_variable."'></input>
									
									</td></tr></table></fieldset>
				";	
				
		return sprintf($format,$name,$name);
		
			} elseif($name == 'delete'){
			$format="			<tr><td colspan='3' align='center'>
						
											<input name='%s' id='%s' type='submit' class='button' value='Delete'></input>
											
											</td></tr></table></fieldset>
						";	
		
			return sprintf($format,$name,$name);
			
				}
		
			}
		
	}
}