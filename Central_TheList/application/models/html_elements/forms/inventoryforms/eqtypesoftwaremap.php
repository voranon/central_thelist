<?php

class thelist_inventoryform_eqtypesoftwaremap extends Zend_Form{

	public function __construct($options=null)
	{
		parent::__construct($options);

		
	$eq_type_id = new Zend_Form_Element_Select('eq_type_id',
								array(
									'label'      	=> 'Equipment Type:',
									'decorators' 	=> array(new eqtypesoftwaremapdecorator($options))
	)
	);
	$eq_type_id->setRegisterInArrayValidator(false);
	$eq_type_id->setRequired(false);
	
	$software_package_id = new Zend_Form_Element_Select('software_package_id',
	array(
										'label'      	=> 'Software Package:',
										'decorators' 	=> array(new eqtypesoftwaremapdecorator($options))
	)
	);
	$software_package_id->setRegisterInArrayValidator(false);
	$software_package_id->setRequired(false);
		
	if ($options['function_type'] == 'add') {	
		
	$create = new Zend_Form_Element_Submit('create',
	array(
									'label' => 'Create', 'value' => 'C',
									'decorators' => array(new eqtypesoftwaremapdecorator($options))
	)
	);
	
	$this->addElements(array(
	$eq_type_id,
	$software_package_id,
	$create)
	);
		
	} elseif ($options['function_type'] == 'edit') {
		
	$edit = new Zend_Form_Element_Submit('edit',
	array(
										'label' => 'Edit', 'value' => 'E',
										'decorators' => array(new eqtypesoftwaremapdecorator($options))
	
	)
	);
		
	$delete = new Zend_Form_Element_Submit('delete',
	array(
										'label' => 'Delete', 'value' => 'D',
										'decorators' => array(new eqtypesoftwaremapdecorator($options))
	
		)
	);
		
	$this->addElements(array(
	$eq_type_id,
	$software_package_id,
	$edit,
	$delete)
	);
	
		}
	
	}

}


class eqtypesoftwaremapdecorator extends Zend_Form_Decorator_Abstract
{
	private $database;
	private $_function_type;
	private $_variable;
	
	public function __construct($options=null){


		
		if (isset($options['function_type'])) {
				
			$this->_function_type = $options['function_type'];

		}
		if (isset($options['variable'])){
			
			$this->_variable = $options['variable'];
				
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

		if ($name == 'eq_type_id'){
			
			$format=	"<fieldset><legend>Table</legend><table style='width:500px'>
						<tr><td>%s</td><td>
						<select name='%s' id='%s' style='width: 300px'>";
			
			$format.=	"<option value=''>-----Select One----</option>";
				
			$sql=	"SELECT * FROM equipment_types
					ORDER BY eq_manufacturer, eq_model_name DESC
					";
				
			$equipment_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			foreach ($equipment_types as $equipment_type) {
				
				$format.= "<option value='".$equipment_type['eq_type_id']."'>".$equipment_type['eq_manufacturer']." ".$equipment_type['eq_model_name']."";
				
			}
			
			$format.="</select></td><td>%s</td></tr>";
			
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));

		} elseif ($name == 'software_package_id'){
			
			$format=	"<tr><td>%s</td><td>
						<select name='%s' id='%s' style='width: 300px'>";
			
			$format.=	"<option value=''>-----Select One----</option>";
				
			$sql=	"SELECT * FROM software_packages
					ORDER BY software_package_manufacturer, software_package_name, software_package_architecture, software_package_version DESC
					";
				
			$software_packages = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			foreach ($software_packages as $software_package) {
				
				$format.= "<option value='".$software_package['software_package_id']."'>".$software_package['software_package_manufacturer']." ".$software_package['software_package_name']." ".$software_package['software_package_architecture']." ".$software_package['software_package_version']."";
				
			}
			
			$format.="</select></td><td>%s</td></tr>";
			
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));

		}
		
		if ($this->_function_type == 'add') {
			if($name == 'create'){
				$format="			<tr><td colspan='3' align='center'>
						
											<input name='%s' id='%s' type='submit' class='button' value='Create'></input>
											<input name='device_function_id' type='hidden' value='".$this->_variable."'></input>											
											</td></tr></table></fieldset>
											";	
		
				return sprintf($format,$name,$name);
		
			}
				
		} else if ($this->_function_type == 'edit') {
				
			if($name == 'edit'){
				$format="			<tr><td colspan='3' align='center'>
						
											<input name='%s' id='%s' type='submit' class='button' value='Edit'></input>
											<input name='device_function_id' type='hidden' value='".$this->_variable."'></input>
											
											</td></tr>
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