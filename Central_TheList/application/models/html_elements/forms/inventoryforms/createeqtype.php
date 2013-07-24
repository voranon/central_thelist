<?php

class thelist_inventoryform_createeqtype extends Zend_Form{


	public function __construct($options = null)
	{
		parent::__construct($options);

		$eq_model_name   = new Zend_Form_Element_Textarea('eq_model_name',
		array(
											'label'      => 'Equipment model name:',
											'decorators' => array(
		new createeqtypedecorator()
		)
		)
		);
		
		$eq_manufacturer = new Zend_Form_Element_Select('eq_manufacturer',
		array(
											'label'      => 'Manufacturer:',
											'decorators' => array(
		new createeqtypedecorator()
		)
		)
		);
		$eq_manufacturer->setRegisterInArrayValidator(false);
		$eq_manufacturer->setRequired(true);
	
		
		$eq_type_name   = new Zend_Form_Element_Textarea('$eq_type_name',
		array(
											'label'      => 'Equipment Type name:',
											'decorators' => array(
		new createeqtypedecorator()
		)
		)
		);
		
		$eq_type_desc   = new Zend_Form_Element_Textarea('$eq_type_desc',
		array(
											'label'      => 'Equipment Type Desc:',
											'decorators' => array(
		new createeqtypedecorator()
		)
		)
		);
		
		
		$create     	   = new Zend_Form_Element_Submit('create',
		array(
											'label'      =>'Create',
											'value'		 =>'D',
											'decorators' => array(
		new createeqtypedecorator()
		)
		)
		);
		
		
		
		$this->addElements(array($eq_model_name,$eq_manufacturer,$eq_type_name,$eq_type_desc,$create));

	}
}


class createeqtypedecorator extends Zend_Form_Decorator_Abstract
{
	
	private $database;
	
	public function __construct($project_id=null){

	}
	
	public function render($content){
		$element = $this->getElement();
		$messages = $element->getMessages();
		$name    = htmlentities($element->getFullyQualifiedName());
		$label   = htmlentities($element->getLabel());
		$id      = htmlentities($element->getId());
		$value   = htmlentities($element->getValue());
		
		if($name == 'eq_model_name'){
		$format="<fieldset>
					<legend>Create Equipment Type</legend>
						<table style='width:505px'>
							<tr>
								<td style='width:200px'>%s</td>
								<td style='width:150px'><input name='%s' id='%s' type='text' class='text'></input></td>
								<td>&nbsp;</td>
							</tr>";
			
			return sprintf($format,$label,$name,$name);
			
		}else if($name == 'eq_manufacturer'){
		$format="			<tr>
								<td>%s</td>
								<td>
								<select name='%s' id='%s' style='width: 145px'>";
		$format.=					"<option value=''>-----Select One----</option>";
		
			$sql1 =	"SELECT DISTINCT(eq_manufacturer) FROM equipment_types";
		
			$eq_manufactuerers = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql1);

		foreach($eq_manufactuerers as $eq_manufacturer){
		$format.=					"<option value='".$eq_manufacturer['eq_manufacturer']."'>".$eq_manufacturer['eq_manufacturer']."</option>";
								}
		
		
		$format.="				</select>
								</td>
								<td>%s</td>
							</tr>";
			
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
		
		} else if ($name == 'eq_type_name'){
		$format="			<tr>
								<td style='width:200px'>%s</td>
								<td style='width:150px'><input name='%s' id='%s' type='text' class='text'></input></td>
								<td>&nbsp;</td>
							</tr>";
			
			return sprintf($format,$label,$name,$name);
			

		} else if ($name == 'eq_type_desc'){
			$format="		<tr>
								<td style='width:200px'>%s</td>
								<td style='width:150px'><input name='%s' id='%s' type='text' class='text'></input></td>
								<td>&nbsp;</td>
							</tr>";
				
			return sprintf($format,$label,$name,$name);
		
		
		
		}else if($name == 'create'){
		$format="			<tr>
								<td colspan='3' align='center'>
								<input name='%s' id='%s' type='submit' class='button' value='Create'></input>
								</td>
							</tr>
						</table>
					</fieldset>";	
		return sprintf($format,$name,$name);
		
		}
		
		
	
	}
		
}