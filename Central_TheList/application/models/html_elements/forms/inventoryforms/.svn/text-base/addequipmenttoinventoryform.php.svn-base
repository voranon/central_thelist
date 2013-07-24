<?php

class thelist_inventoryform_addequipmenttoinventoryform extends Zend_Form
{
	public function __construct($options=null)
	{
		parent::__construct($options);
	
	
		$eq_type_id = new Zend_Form_Element_Select('eq_type_id',
		array(
										'label'      	=> 'Equipment Type:',
										'decorators' 	=> array(new addequipmenttoinventorydecorator($options))
		)
		);
		$eq_type_id->setRegisterInArrayValidator(false);
		$eq_type_id->setRequired(true);
		
		$eq_role_id = new Zend_Form_Element_Select('eq_role_id',
		array(
												'label'      	=> 'Equipment Role:',
												'decorators' 	=> array(new addequipmenttoinventorydecorator($options))
		)
		);
		$eq_role_id->setRegisterInArrayValidator(false);
		$eq_role_id->setRequired(false);
	
		$serial_number	 = new Zend_Form_Element_Textarea('serial_number',
		array(
											'label'      	=> 'Serial Number:',
											'decorators' 	=> array(new addequipmenttoinventorydecorator($options))	
		)
		);
		$serial_number->setRequired(true);
	
		if ($options['function_type'] == 'add') {
	
			$create = new Zend_Form_Element_Submit('create',
			array(
										'label' => 'Create', 'value' => 'C',
										'decorators' => array(new addequipmenttoinventorydecorator($options))
			)
			);
	
			$this->addElements(array(
			$eq_type_id,
			$serial_number,
			$eq_role_id,
			$create)
			);
	
		} elseif ($options['function_type'] == 'edit') {
	
			$edit = new Zend_Form_Element_Submit('edit',
			array(
											'label' => 'Edit', 'value' => 'E',
											'decorators' => array(new addequipmenttoinventorydecorator($options))
	
			)
			);
	
			$delete = new Zend_Form_Element_Submit('delete',
			array(
											'label' => 'Delete', 'value' => 'D',
											'decorators' => array(new addequipmenttoinventorydecorator($options))
	
			)
			);
	
			$this->addElements(array(
			$eq_type_id,
			$serial_number,
			$eq_role_id,
			$edit,
			$delete)
			);
	
		}
	
	}
	
	}
	
	
	class addequipmenttoinventorydecorator extends Zend_Form_Decorator_Abstract
	{
		private $database;
		private $_time;
		private $_function_type;
		private $_eq_type_id=null;
	
		public function __construct($options=null){
	

			$this->_time				= Zend_Registry::get('time');
	
			if (isset($options['function_type'])) {
	
				$this->_function_type = $options['function_type'];
	
			}
			if (isset($options['eq_type_id'])){
					
				$this->_eq_type_id = $options['eq_type_id'];
	
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
								<option value=''>---SELECT ONE---</option>";
	
					//everything but receivers
					$sql = 		"SELECT et.eq_type_id, et.eq_type_friendly_name FROM equipment_types et
								INNER JOIN eq_type_group_mapping etgm ON etgm.eq_type_id=et.eq_type_id
								WHERE et.eq_type_serialized='1'
								AND etgm.eq_type_group_id NOT IN (1,2,3,4,6,7,8,11)
								";
						
					$serialized_eq_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);

					foreach($serialized_eq_types as $serialized_eq_type){
	
						$format.= "<option value='".$serialized_eq_type['eq_type_id']."'>".$serialized_eq_type['eq_type_friendly_name']."</option>";
	
					}
	
					$format.="</select></td><td>%s</td></tr>";
						
					return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
	
				} else {
	
	
	
				}
	
	
			}if ($name == 'eq_role_id'){
					
					
				if ($this->_function_type == 'add') {
	
					$format=	"<tr><td>%s</td><td><select name='%s' id='%s' style='width: 300px'>	
								<option value=''>---Select ONE or NONE---</option>";
	
					//everything but receivers
					$sql = 		"SELECT * FROM equipment_roles";
						
					$equipment_roles = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);

					foreach($equipment_roles as $equipment_role){
	
						$format.= "<option value='".$equipment_role['equipment_role_id']."'>".$equipment_role['equipment_role_name']."</option>";
	
					}
	
					$format.="</select></td><td>%s</td></tr>";
						
					return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
	
				} else {
	
	
	
				}
	
	
			} elseif ($name == 'serial_number') {
					
				$format="			<tr>
									<td style='width:200px'>%s</td>
									<td style='width:150px'><input name='%s' id='%s' type='text' class='text' value=''></input></td>
									<td>&nbsp;</td>
									</tr>";
					
				return sprintf($format,$label,$name,$name);
					
					
			} 
	
			if ($this->_function_type == 'add') {
				if($name == 'create'){
					$format="			<tr><td colspan='3' align='center'>
							
												<input name='%s' id='%s' type='submit' class='button' value='Create'></input>								
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
