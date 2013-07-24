<?php

class thelist_inventoryform_addconfig extends Zend_Form{

	public function __construct($options=null)
	{
		parent::__construct($options);

	$conf_name	 = new Zend_Form_Element_Textarea('conf_name',
								array(
									'label'      	=> 'Config name:',
									'decorators' 	=> array(new addconfigdecorator($options))	
	)
	);
	$conf_name->setRequired(true);

	$conf_desc   = new Zend_Form_Element_Textarea('conf_desc',
	array(
									'label'      => 'Description:',
									'decorators' => array(new addconfigdecorator($options))
	)
	);
	$conf_desc->setRequired(true);
		
	$device_function_id = new Zend_Form_Element_Select('device_function_id',
								array(
									'label'      	=> 'Device Function:',
									'decorators' 	=> array(new addconfigdecorator($options))
	)
	);
	$device_function_id->setRegisterInArrayValidator(false);
	$device_function_id->setRequired(false);
		
	if ($options['function_type'] == 'add') {	
		
	$create = new Zend_Form_Element_Submit('create',
	array(
									'label' => 'Create', 'value' => 'C',
									'decorators' => array(new addconfigdecorator($options))
	)
	);
	
	$this->addElements(array(
	$conf_name,
	$conf_desc,
	$device_function_id,
	$create)
	);
		
	} elseif ($options['function_type'] == 'edit') {
		
	$edit = new Zend_Form_Element_Submit('edit',
	array(
										'label' => 'Edit', 'value' => 'E',
										'decorators' => array(new addconfigdecorator($options))
	
	)
	);
		
	$delete = new Zend_Form_Element_Submit('delete',
	array(
										'label' => 'Delete', 'value' => 'D',
										'decorators' => array(new addconfigdecorator($options))
	
		)
	);
		
	$this->addElements(array(
	$conf_name,
	$conf_desc,
	$device_function_id,
	$edit,
	$delete)
	);
	
		}
	
	}

}


class addconfigdecorator extends Zend_Form_Decorator_Abstract
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

		if($name == 'conf_name'){
			
			$format =	"<fieldset><legend>New Configuration</legend><table style='width:500px'><tr>
						<td>%s</td><td><input style='width: 300px' name='%s' id='device_function_name' type='text' class='text' value=''></input></td>
						</tr>";
			
				return sprintf($format,$label,$name,$name);

		} elseif ($name == 'conf_desc'){
					
									
				$format =	"<tr>
									<td>%s</td><td><input style='width: 300px' name='%s' id='device_function_desc' type='text' class='text' value=''></input></td>
									</tr>";
			
				return sprintf($format,$label,$name,$name);
			
		} elseif ($name == 'device_function_id'){
			
			$format=	"<tr><td>%s</td><td>
						<select name='%s' id='%s' style='width: 300px'>";
			
			$format.=	"<option value=''>-----Select One----</option>";
				
			$sql=	"SELECT * FROM device_functions
					ORDER BY device_function_name DESC
					";
				
			$device_functions = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			foreach ($device_functions as $device_function) {
				
				$format.= "<option value='".$device_function['device_function_id']."'>".$device_function['device_function_name']."";
				
			}
			
			$format.="</select></td><td>%s</td></tr>";
			
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));

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