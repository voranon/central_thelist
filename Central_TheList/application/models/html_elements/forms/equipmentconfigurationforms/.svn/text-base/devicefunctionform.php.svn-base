<?php

class thelist_equipmentconfigurationform_devicefunctionform extends Zend_Form{

	public function __construct($options=null)
	{
		parent::__construct($options);

	$devicefunctionname	 = new Zend_Form_Element_Textarea('device_function_name',
								array(
									'label'      	=> 'Function Name:',
									'decorators' 	=> array(new devicefunctiondecorator($options))	
	)
	);
	$devicefunctionname->setRequired(true);

	$devicefunctiondesc   = new Zend_Form_Element_Textarea('device_function_desc',
	array(
									'label'      => 'Description:',
									'decorators' => array(new devicefunctiondecorator($options))
	)
	);
	$devicefunctiondesc->setRequired(true);
		
	$device_command_parameter_table_id = new Zend_Form_Element_Select('device_command_parameter_table_id',
								array(
									'label'      	=> 'Table with Primary Key:',
									'decorators' 	=> array(new devicefunctiondecorator($options))
	)
	);
	$device_command_parameter_table_id->setRegisterInArrayValidator(false);
	$device_command_parameter_table_id->setRequired(false);
		
	if ($options['function_type'] == 'add') {	
		
	$create = new Zend_Form_Element_Submit('create',
	array(
									'label' => 'Create', 'value' => 'C',
									'decorators' => array(new devicefunctiondecorator($options))
	)
	);
	
	$this->addElements(array(
	$devicefunctionname,
	$devicefunctiondesc,
	$device_command_parameter_table_id,
	$create)
	);
		
	} elseif ($options['function_type'] == 'edit') {
		
	$edit = new Zend_Form_Element_Submit('edit',
	array(
										'label' => 'Edit', 'value' => 'E',
										'decorators' => array(new devicefunctiondecorator($options))
	
	)
	);
		
	$delete = new Zend_Form_Element_Submit('delete',
	array(
										'label' => 'Delete', 'value' => 'D',
										'decorators' => array(new devicefunctiondecorator($options))
	
		)
	);
		
	$this->addElements(array(
	$devicefunctionname,
	$devicefunctiondesc,
	$device_command_parameter_table_id,
	$edit,
	$delete)
	);
	
		}
	
	}

}


class devicefunctiondecorator extends Zend_Form_Decorator_Abstract
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

		if($name == 'device_function_name'){
			
			$format =	"<fieldset><legend>Table</legend><table style='width:500px'><tr>
						<td>%s</td><td><input style='width: 300px' name='device_function_name' id='device_function_name' type='text' class='text' value=''></input></td>
						</tr>";
			
				return sprintf($format,$label,$name,$name);

		} elseif ($name == 'device_function_desc'){
					
									
				$format =	"<tr>
									<td>%s</td><td><input style='width: 300px' name='device_function_desc' id='device_function_desc' type='text' class='text' value=''></input></td>
									</tr>";
			
				return sprintf($format,$label,$name,$name);
			
		} elseif ($name == 'device_command_parameter_table_id'){
			
			$format=	"<tr><td>%s</td><td>
						<select name='%s' id='%s' style='width: 300px'>";
			
			$format.=	"<option value=''>-----Select One----</option>";
				
			$sql=	"SELECT * FROM device_command_parameter_tables
					ORDER BY table_name DESC
					";
				
			$device_command_parameter_tables = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			foreach ($device_command_parameter_tables as $device_command_parameter_table) {
				
				$format.= "<option value='".$device_command_parameter_table['device_command_parameter_table_id']."'>".$device_command_parameter_table['table_name']."";
				
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