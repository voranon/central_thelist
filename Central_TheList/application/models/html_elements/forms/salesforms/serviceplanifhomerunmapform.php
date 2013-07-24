<?php

class serviceplanifhomerunmapform extends Zend_Form{

	public function __construct($function_type, $variable)
	{
		parent::__construct($function_type, $variable);

	$homerun_type_group = new Zend_Form_Element_Select('homerun_type_group_id',
								array(
										'label'      	=> 'Home Run Group:',
										'decorators' 	=> array(new serviceplanifhomerunmapdecorator($function_type, $variable)
	)
	)
	);
	$homerun_type_group->setRegisterInArrayValidator(false);
	$homerun_type_group->setRequired(true);

	$service_plan_eq_type_map = new Zend_Form_Element_Select('service_plan_eq_type_map_id',
	array(
										'label'      	=> 'Equipment:',
										'decorators' 	=> array(new serviceplanifhomerunmapdecorator($function_type, $variable)
	)
	)
	);
	$service_plan_eq_type_map->setRegisterInArrayValidator(false);

	$static_if_type = new Zend_Form_Element_Select('static_if_type_id',
	array(
										'label'      	=> 'Interface:',
										'decorators' 	=> array(new serviceplanifhomerunmapdecorator($function_type, $variable)
	)
	)
	);
	$static_if_type->setRegisterInArrayValidator(false);
		
	if ($function_type == 'add') {
		
	$create = new Zend_Form_Element_Submit('create',
	array(
										'label' => 'Create', 'value' => 'C',
										'decorators' => array(new serviceplanifhomerunmapdecorator($function_type, $variable)
	)
	)
	);
	
			$this->addElements(array(
			$homerun_type_group,
			$service_plan_eq_type_map,
			$static_if_type,
			$create)
			);
		
	} elseif ($function_type == 'edit') {
		
	$edit = new Zend_Form_Element_Submit('edit',
	array(
										'label' => 'Edit', 'value' => 'E',
										'decorators' => array(new serviceplanifhomerunmapdecorator($function_type, $variable)
	)
	)
	);
		
	$delete = new Zend_Form_Element_Submit('delete',
	array(
										'label' => 'Delete', 'value' => 'D',
										'decorators' => array(new serviceplanifhomerunmapdecorator($function_type, $variable)
	)
	)
	);
		
			$this->addElements(array(
			$homerun_type_group,
			$service_plan_eq_type_map,
			$static_if_type,
			$edit,
			$delete)
			);
	
		}	
	}
}


class serviceplanifhomerunmapdecorator extends Zend_Form_Decorator_Abstract
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
				
			$sql=	"SELECT it.*, et.*, sit.*, spetm.*, htm.homerun_type_group_id AS homerun_group, htm.homerun_type_quantity AS homerun_group_quantity, ht.homerun_name, htg.*, hretm.homerun_type_group_sp_eq_type_map_id FROM homerun_types ht
					LEFT OUTER JOIN homerun_type_mapping htm ON htm.homerun_type_id=ht.homerun_type_id
					LEFT OUTER JOIN homerun_type_group htg ON htg.homerun_type_group_id=htm.homerun_type_group_id
					LEFT OUTER JOIN homerun_type_group_sp_eq_type_mapping hretm ON hretm.homerun_type_group_id=htg.homerun_type_group_id
					LEFT OUTER JOIN static_if_types sit ON sit.static_if_type_id=hretm.static_if_type_id
					LEFT OUTER JOIN equipment_types et ON et.eq_type_id=sit.eq_type_id
					LEFT OUTER JOIN interface_types it ON it.if_type_id=sit.if_type_id
					LEFT OUTER JOIN service_plan_eq_type_mapping spetm ON spetm.service_plan_eq_type_map_id=hretm.service_plan_eq_type_map_id
					WHERE hretm.homerun_type_group_sp_eq_type_map_id='".$this->_variable."'
					GROUP BY htm.homerun_type_group_id
					";
				
			$current_homerun_map = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
			$service_plan_id = $current_homerun_map['service_plan_id'];
			
		} else if ($this->_function_type == 'add') {
			
			$service_plan_id = $this->_variable;
				
		}
		
		if($name == 'homerun_type_group_id'){

			$format=	"<fieldset><legend>Create Service Plan Homerun Mapping</legend><table style='width:750px'>
						<tr><td>%s</td><td>
						<select name='%s' id='%s' style='width: 500px'>";
			
			if ($this->_function_type == 'edit') {
				
				$format.= "<option value='".$current_homerun_map['homerun_type_group_id']."'>".$current_homerun_map['homerun_type_group_name']." | Required Quantity: ".$current_homerun_map['homerun_type_required_quantity']."</option>";
								
				$sql1 = "SELECT * FROM homerun_type_group
						WHERE homerun_type_group_id!='".$current_homerun_map['homerun_type_group_id']."'
						";
				
				$homerun_groups = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql1);
				
			} elseif ($this->_function_type == 'add') {
				
				$format.=	"<option value=''>-----Select One----</option>";
				
				$sql1 = "SELECT * FROM homerun_type_group
						";
				
				$homerun_groups = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql1);
				
			}

			$i=0;
			foreach($homerun_groups as $homerun_group){
		
				$format.= "<option value='".$homerun_group['homerun_type_group_id']."'>".$homerun_group['homerun_type_group_name']." | Required Quantity: ".$homerun_group['homerun_type_required_quantity']."</option>";

			}
				
				$format.="</select></td><td>%s</td></tr>";
			
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
			
		} else if($name == 'service_plan_eq_type_map_id'){

			$format=	"<tr><td>%s</td><td>
						<select name='%s' id='%s' style='width: 500px'>";
			
			if ($this->_function_type == 'edit') {

				$format.=	"<option value='".$current_homerun_map['service_plan_eq_type_map_id']."'>".$current_homerun_map['eq_manufacturer']." ".$current_homerun_map['eq_model_name']."</option>";

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
						WHERE sit.static_if_type_id='".$current_homerun_map['static_if_type_id']."'
						";
				
				$interface_type_orig = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

				
				$format.="<option value='".$current_homerun_map['static_if_type_id']."'>".$current_homerun_map['if_type_name']."</option>";
								
			} else if ($this->_function_type == 'add') {
				
				$format.=	"<option value=''>-----Select One----</option>";
				
			}
			
			
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
									<input name='homerun_type_group_sp_eq_type_map_id' type='hidden' value='".$this->_variable."'></input>
									
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