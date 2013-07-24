<?php

class thelist_inventoryform_addconfmap extends Zend_Form{

	public function __construct($options=null)
	{
		parent::__construct($options);
		
		$eq_type_software_map_id = new Zend_Form_Element_Select('eq_type_software_map_id',
		array(
											'label'      	=> 'EQ Soft Map:',
											'decorators' 	=> array(new addconfmapdecorator($options))
		)
		);
		$eq_type_software_map_id->setRegisterInArrayValidator(false);
		$eq_type_software_map_id->setRequired(true);
		
		$conf_id = new Zend_Form_Element_Select('conf_id',
		array(
													'label'      	=> 'Config:',
													'decorators' 	=> array(new addconfmapdecorator($options))
		)
		);
		$conf_id->setRegisterInArrayValidator(false);
		$conf_id->setRequired(true);
		
		$conf_if_value_datatype = new Zend_Form_Element_Select('conf_if_value_datatype',
		array(
													'label'      	=> 'Datatype:',
													'decorators' 	=> array(new addconfmapdecorator($options))
		)
		);
		$conf_if_value_datatype->setRegisterInArrayValidator(false);
		$conf_if_value_datatype->setRequired(false);
	
	if ($options['function_type'] == 'edit') {
		
		$edit = new Zend_Form_Element_Submit('edit',
		array(
											'label' => 'Edit', 'value' => 'E',
											'decorators' => array(new addconfmapdecorator($options))
		
		)
		);
		
		$delete = new Zend_Form_Element_Submit('delete',
		array(
											'label' => 'Delete', 'value' => 'D',
											'decorators' => array(new addconfmapdecorator($options))
		
		)
		);
			
		$this->addElements(array(
		$eq_type_software_map_id,
		$conf_id,
		$conf_if_value_datatype,
		$edit,
		$delete)
		);
	
		} elseif ($options['function_type'] == 'add') {
		
		$create = new Zend_Form_Element_Submit('create',
		array(
											'label' => 'Create', 'value' => 'C',
											'decorators' => array(new addconfmapdecorator($options))
		
		)
		);
			
		$this->addElements(array(
		$eq_type_software_map_id,
		$conf_id,
		$conf_if_value_datatype,
		$create)
		);
	
		}
	
	}

}


class addconfmapdecorator extends Zend_Form_Decorator_Abstract
{
	private $database;
	private $_function_type;
	private $_variable;
	private $_if_type_id=null;

	
	public function __construct($options=null){


		
		
		if (isset($options['function_type'])) {
		
			$this->_function_type = $options['function_type'];
		
		}
		if (isset($options['variable'])){
				
			$this->_variable = $options['variable'];
		
		}
		if (isset($options['if_type_id'])){
		
			$this->_if_type_id = $options['if_type_id'];
		
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

	
		if ($name == 'eq_type_software_map_id'){
			
			if ($this->_function_type == 'add' && $this->_if_type_id != null) {
				
				$format = "	<fieldset><legend>Add Config Option</legend><table style='width:500px'><tr>
							<tr><td>%s</td><td><select name='%s' id='%s' style='width: 300px'>";
				$format.= "<option value=''>---SELECT ONE---</option>";
					
				$sql2 = 	"SELECT * FROM equipment_type_software_mapping etsm
							INNER JOIN equipment_types et ON et.eq_type_id=etsm.eq_type_id
							INNER JOIN software_packages sp ON sp.software_package_id=etsm.software_package_id
							INNER JOIN static_if_types sit ON sit.eq_type_id=et.eq_type_id
							WHERE sit.if_type_id='".$this->_if_type_id."'
							GROUP BY etsm.eq_type_software_map_id
							";
					
				$eq_soft_maps = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
					

				

							
				foreach ($eq_soft_maps as $eq_soft_map){
		
						$format.= "<option value='".$eq_soft_map['eq_type_software_map_id']."'>".$eq_soft_map['eq_manufacturer']." ".$eq_soft_map['eq_model_name']." ".$eq_soft_map['software_package_name']." ".$eq_soft_map['software_package_architecture']." ".$eq_soft_map['software_package_version']."</option>";
		
						}

				$format.="</select></td><td>%s</td></tr>";

				return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));

			}

		} elseif ($name == 'conf_id'){
			
			if ($this->_function_type == 'add' && $this->_if_type_id != null) {
				
				$format = "	<tr><td>%s</td><td><select name='%s' id='%s' style='width: 300px'>";
				$format.= "<option value=''>---SELECT ONE---</option>";
				
				$sql = "SELECT * FROM configurations";
				
				$configurations = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
					
				foreach ($configurations as $configuration){
				
					$format.= "<option value='".$configuration['conf_id']."'>".$configuration['conf_name']."</option>";
				
				}
				
				$format.="</select></td><td>%s</td></tr>";
				
				return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));

			}

		} elseif ($name == 'conf_if_value_datatype'){
			
		if ($this->_function_type == 'add' && $this->_if_type_id != null) {
				
				$format = "	<tr><td>%s</td><td><select name='%s' id='%s' style='width: 300px'>";
				$format.= "<option value=''>---SELECT ONE---</option>";
				
				
				$sql = "SELECT * FROM items
				WHERE item_type='datatypes'";
				
				$datatypes = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
					
				foreach ($datatypes as $datatype){
				
					$format.= "<option value='".$datatype['item_id']."'>".$datatype['item_value']."</option>";
				
				}
				
				$format.="</select></td><td>%s</td></tr>";
				
				return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));

			}

		}  
	
		if($name == 'edit'){
				
				if ($this->_function_type == 'edit' && $this->_if_type_id != null) {

				$format="			<tr><td colspan='3' align='center'>
									<input name='%s' id='%s' type='submit' class='button' value='Edit'></input>
									<input name='if_type_id' type='hidden' value='".$this->_if_type_id."'></input>
									";	
		
				return sprintf($format,$name,$name);
					
			}
		
		} elseif($name == 'delete'){
				
				if ($this->_function_type == 'edit' && $this->_if_type_id != null) {

				$format="			<input name='%s' id='%s' type='submit' class='button' value='Delete'></input>
									<input name='if_type_id' type='hidden' value='".$this->_if_type_id."'></input>
									</td></tr></table></fieldset>
									";	
		
				return sprintf($format,$name,$name);
					
			}
		
		} elseif($name == 'create'){
				
				if ($this->_function_type == 'add' && $this->_if_type_id != null) {

				$format="			<tr><td colspan='3' align='center'>
									<input name='%s' id='%s' type='submit' class='button' value='Add'></input>
									<input name='if_type_id' type='hidden' value='".$this->_if_type_id."'></input>
									</td></tr></table></fieldset>
									";	
		
				return sprintf($format,$name,$name);
					
			}
		
		}
		
	}
}