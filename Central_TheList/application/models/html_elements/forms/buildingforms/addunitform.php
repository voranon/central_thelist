<?php

class thelist_buildingform_addunitform extends Zend_Form
{

	public function __construct($options=null)
	{
		parent::__construct($options);

	$building_id = new Zend_Form_Element_Select('building_id',
	array(
			'label'      	=> 'Building:',
			'decorators' 	=> array(new addunitformdecorator($options))
	)
	);
	$building_id->setRegisterInArrayValidator(false);
	$building_id->setRequired(false);

	$unit_name	= new Zend_Form_Element_Textarea('unit_name',
	array(
			'label'      	=> 'Number / Name:',
			'decorators' 	=> array(new addunitformdecorator($options))	
	)
	);
	$unit_name->setRequired(false);
	
	$unit_type_group 		= new Zend_Form_Element_Select('unit_type_group',
	array(
			'label'      	=> 'Type:',
			'decorators' 	=> array(new addunitformdecorator($options))
	)
	);
	$unit_type_group->setRegisterInArrayValidator(false);
	$unit_type_group->setRequired(false);
	
	
		
	$create = new Zend_Form_Element_Submit('create',
	array(
			'label' => 'Create', 'value' => 'D',
			'decorators' => array(new addunitformdecorator($options))
	
	)
	);
		
	$this->addElements(
	array($building_id, $unit_name, $unit_type_group, $create)
	);

	}

}


class addunitformdecorator extends Zend_Form_Decorator_Abstract
{
	private $_building_id=null;
		
	public function __construct($options=null)
	{
		if (isset($options['building_id'])) {
			$this->_building_id = $options['building_id'];
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

		if ($name == 'building_id') {
		
			$format	=	"<fieldset><legend>Add a unit</legend><table style='width:500px'><tr>
						<tr><td>%s</td><td><select name='%s' id='%s' style='width: 300px'>";
			
			if ($this->_building_id != null) {
				
				$sql=	"SELECT building_id, building_name FROM buildings
						WHERE building_id='".$this->_building_id."'
						";
				
				$selected_building = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
				
				$format .= "<option value='".$selected_building['building_id']."'>".$selected_building['building_name']."</option>";
				
			} else {
				
				$format .= "<option value='0'>--- SELECT ONE ---</option>";
			}
		
			$sql2	= "SELECT building_id, building_name FROM buildings";
			
		
			$buildings = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
				
			if (isset($buildings['0'])) {
				foreach ($buildings as $building) {
				
					$format.= "<option value='".$building['building_id']."'>".$building['building_name']."</option>";
				
				}
			}

			$format.="</select></td><td>%s</td></tr>";
		
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
		
		}
		
		if ($name == 'unit_name'){

			$format	=	"<tr><td>%s</td><td><input style='width: 300px' name='unitname' type='text' class='text' value=''></input></td></tr>";

			return sprintf($format,$label,$name,$name);

		} elseif ($name == 'unit_type_group') {

			$format	=	"<tr><td>%s</td><td><select name='%s' id='%s' style='width: 300px'>";
		
				
			$sql=	"SELECT ug.unit_group_id, ug.unit_group_name FROM unit_groups ug
					INNER JOIN items itm ON itm.item_id=ug.unit_group_type
					WHERE itm.item_name='unit_type'
					";
				
			$unit_type_groups = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
				
			$format.= "<option value='0'>--- SELECT ONE ---</option>";
			
			foreach ($unit_type_groups as $unit_type_group) {
		
				$format.= "<option value='".$unit_type_group['unit_group_id']."'>".$unit_type_group['unit_group_name']."</option>";
		
			}
				
			$format.="</select></td><td>%s</td></tr>";
				
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));

		} 
				
		if($name == 'create'){
			$format = 	"<tr><td colspan='3' align='center'>
						<input name='%s' id='%s' type='submit' class='button' value='Create'></input>
						</td></tr></table></fieldset>
						";	
	
			return sprintf($format,$name,$name);
				
		}
		
		
		
	}
}