<?php

class thelist_inventoryform_assignequipmenttounitform extends Zend_Form
{
	public function __construct($options=null)
	{
		parent::__construct($options);
	
		$eq_type_id = new Zend_Form_Element_Select('eq_type_id',
		array(
												'label'      	=> 'Equipment Type:',
												'decorators' 	=> array(new assignequipmenttounitdecorator($options))
		)
		);
		$eq_type_id->setRegisterInArrayValidator(false);
		$eq_type_id->setRequired(true);
		
		$eq_id = new Zend_Form_Element_Select('eq_id',
		array(
														'label'      	=> 'Equipment:',
														'decorators' 	=> array(new assignequipmenttounitdecorator($options))
		)
		);
		$eq_id->setRegisterInArrayValidator(false);
		$eq_id->setRequired(false);
	
		$building_id = new Zend_Form_Element_Select('building_id',
		array(
										'label'      	=> 'Building:',
										'decorators' 	=> array(new assignequipmenttounitdecorator($options))
		)
		);
		$building_id->setRegisterInArrayValidator(false);
		$building_id->setRequired(true);
		
		$unit_id = new Zend_Form_Element_Select('unit_id',
		array(
												'label'      	=> 'Unit:',
												'decorators' 	=> array(new assignequipmenttounitdecorator($options))
		)
		);
		$unit_id->setRegisterInArrayValidator(false);
		$unit_id->setRequired(false);
		
		$remap = new Zend_Form_Element_Checkbox('remap',
		array(
														'label'      	=> 'Force Remapping?:',
														'decorators' 	=> array(new assignequipmenttounitdecorator($options))
		)
		);
		$remap->setRequired(false);
		
		$permanent = new Zend_Form_Element_Checkbox('permanent',
		array(
																'label'      	=> 'Permanent?:',
																'decorators' 	=> array(new assignequipmenttounitdecorator($options))
		)
		);
		$permanent->setRequired(false);
	
		if ($options['function_type'] == 'add') {
	
			$create = new Zend_Form_Element_Submit('create',
			array(
										'label' => 'Create', 'value' => 'C',
										'decorators' => array(new assignequipmenttounitdecorator($options))
			)
			);
	
			$this->addElements(array(
			$eq_type_id,
			$eq_id,
			$building_id,
			$unit_id,
			$remap,
			$permanent,
			$create)
			);
	
		} elseif ($options['function_type'] == 'edit') {
	
			$edit = new Zend_Form_Element_Submit('edit',
			array(
											'label' => 'Edit', 'value' => 'E',
											'decorators' => array(new assignequipmenttounitdecorator($options))
	
			)
			);
	
			$delete = new Zend_Form_Element_Submit('delete',
			array(
											'label' => 'Delete', 'value' => 'D',
											'decorators' => array(new assignequipmenttounitdecorator($options))
	
			)
			);
	
			$this->addElements(array(
			$eq_type_id,
			$eq_id,
			$building_id,
			$unit_id,
			$remap,
			$permanent,
			$edit,
			$delete)
			);
	
		}
	
	}
	
	}
	
	
	class assignequipmenttounitdecorator extends Zend_Form_Decorator_Abstract
	{
		private $database;
		private $_time;
		private $_function_type;
		private $_unit_group_id=null;
	
		public function __construct($options=null){
	

			$this->_time				= Zend_Registry::get('time');
	
			if (isset($options['function_type'])) {
	
				$this->_function_type = $options['function_type'];
	
			}
			
			if (isset($options['unit_group_id'])){
					
				$this->_unit_group_id = $options['unit_group_id'];
	
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
					
					
				if ($this->_function_type == 'add') {
	
					$format=	"<fieldset><legend>Equipment Types</legend><table style='width:500px'><tr><tr><td>%s</td><td>
								<select name='%s' id='%s' style='width: 300px'>	
								<option value='0'>---Select ONE---</option>";
	
					//everything but receivers
					$sql = 		"SELECT * FROM equipment_types
								WHERE eq_type_friendly_name!=''
								ORDER BY eq_type_friendly_name
								";
						
					$eq_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);

					foreach($eq_types as $eq_type){
	
						$format.= "<option value='".$eq_type['eq_type_id']."'>".$eq_type['eq_type_friendly_name']."</option>";
	
					}
	
					$format.="</select></td><td>%s</td></tr>";
						
					return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
	
				} else {
	
	
	
				}
	
	
			} elseif ($name == 'eq_id'){
					
					
				if ($this->_function_type == 'add') {
	
					$format=	"<tr><td>%s</td><td><select name='%s' id='%s' style='width: 300px'>	
								<option value='0'>---Select ONE---</option>";
	
					//ajax fill
	
					$format.="</select></td><td>%s</td></tr>";
						
					return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
	
				} else {
	
	
	
				}
			} elseif ($name == 'building_id'){
					
					
				if ($this->_function_type == 'add') {
	
					$format=	"<tr><td>%s</td><td><select name='%s' id='%s' style='width: 300px'>	
								<option value='0'>---Select ONE---</option>";
	
					//everything but receivers
					$sql = 		"SELECT * FROM buildings
								ORDER BY building_name
								";
						
					$buildings = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);

					foreach($buildings as $building){
	
						$format.= "<option value='".$building['building_id']."'>".$building['building_name']."</option>";
	
					}
	
					$format.="</select></td><td>%s</td></tr>";
						
					return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
	
				} else {
	
	
	
				}
			} elseif ($name == 'unit_id'){
					
					
				if ($this->_function_type == 'add') {
	
					$format=	"<tr><td>%s</td><td><select name='%s' id='%s' style='width: 300px'>	
								<option value='0'>---Select ONE---</option>";
	
					//ajax fill
	
					$format.="</select></td><td>%s</td></tr>";
						
					return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
	
				} else {
	
	
	
				}
			} elseif ($name == 'remap'){
					
					
				if ($this->_function_type == 'add') {
	
					$format=	"<tr><td colspan=2>%s</td><td><input type='checkbox' name='remap' value='1' />";
		
					$format.="</td></tr>";
						
					return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
	
				} else {
	
	
	
				}
			} elseif ($name == 'permanent'){
					
					
				if ($this->_function_type == 'add') {
	
					$format=	"<tr><td colspan=2>%s</td><td><input type='checkbox' name='permanent' value='1' />";
		
					$format.="</td></tr>";
						
					return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
	
				} else {
	
	
	
				}
			}
	
			if ($this->_function_type == 'add') {
				if($name == 'create'){
					$format="			<tr><td colspan='3' align='center'>
							
												<input name='%s' id='%s' type='submit' class='button' value='Create'></input>
												<input type='hidden' name='unit_group_id' value='".$this->_unit_group_id."' />							
												</td></tr></table></fieldset>
												";	
	
					return sprintf($format,$name,$name);
	
				}
	
			} else if ($this->_function_type == 'edit') {
	
				if($name == 'delete'){
					$format="			<tr><td colspan='3' align='center'>
									
														<input name='%s' id='%s' type='submit' class='button' value='Delete'></input>	
														</td></tr></table></fieldset>
												";	
	
					return sprintf($format,$name,$name);
						
				}
	
			}
	
		}
	
		
}
