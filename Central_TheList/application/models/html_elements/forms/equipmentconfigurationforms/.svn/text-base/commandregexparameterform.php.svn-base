<?php

class thelist_equipmentconfigurationform_commandregexparameterform extends Zend_Form{

	public function __construct($options=null)
	{
		parent::__construct($options);

	$command_regex_parameter_name	 = new Zend_Form_Element_Textarea('command_regex_parameter_name',
								array(
									'label'      	=> 'Parameter Name:',
									'decorators' 	=> array(new commandregexparameterdecorator($options))	
	)
	);
	$command_regex_parameter_name->setRequired(true);

	$device_command_parameter_column_id = new Zend_Form_Element_Select('device_command_parameter_column_id',
								array(
									'label'      	=> 'Parameter Value:',
									'decorators' 	=> array(new commandregexparameterdecorator($options))
	)
	);
	$device_command_parameter_column_id->setRegisterInArrayValidator(false);
	$device_command_parameter_column_id->setRequired(true);
		
			
	if ($options['function_type'] == 'add') {	
		
	$create = new Zend_Form_Element_Submit('create',
	array(
									'label' => 'Create', 'value' => 'C',
									'decorators' => array(new commandregexparameterdecorator($options))
	)
	);
	
	$this->addElements(array(
	$command_regex_parameter_name,
	$device_command_parameter_column_id,
	$create)
	);
		
	} elseif ($options['function_type'] == 'edit') {
		
	$edit = new Zend_Form_Element_Submit('edit',
	array(
										'label' => 'Edit', 'value' => 'E',
										'decorators' => array(new commandregexparameterdecorator($options))
	
	)
	);
		
	$delete = new Zend_Form_Element_Submit('delete',
	array(
										'label' => 'Delete', 'value' => 'D',
										'decorators' => array(new commandregexparameterdecorator($options))
	
		)
	);
		
	$this->addElements(array(
	$command_regex_parameter_name,
	$device_command_parameter_column_id,
	$edit,
	$delete)
	);
	
		}
	
	}

}


class commandregexparameterdecorator extends Zend_Form_Decorator_Abstract
{
	private $database;
	private $_function_type;
	private $_variable;
	private $_row;
	private $_device_function_map_id;
	private $_device_command_id;
	
	public function __construct($options=null){


		
		if (isset($options['function_type'])) {
				
			$this->_function_type = $options['function_type'];

		}
		if (isset($options['variable'])){
			
			$this->_variable = $options['variable'];
				
		}
		if (isset($options['device_function_map_id'])){
				
			$this->_device_function_map_id = $options['device_function_map_id'];
		
		}
		if (isset($options['device_command_id'])){
		
			$this->_device_command_id = $options['device_command_id'];
		
		}
		
		if ($this->_function_type == 'edit') {
			
			$sql =	"SELECT crp.*, CONCAT(dcpt.table_name, '.', dcpc.column_name) AS parameter FROM command_regex_parameters crp
					INNER JOIN device_command_parameter_columns dcpc ON dcpc.device_command_parameter_column_id=crp.device_command_parameter_column_id
					INNER JOIN device_command_parameter_tables dcpt ON dcpt.device_command_parameter_table_id=dcpc.device_command_parameter_table_id
					WHERE crp.command_regex_parameter_id='".$this->_variable."'
					";
			
			$this->_row = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
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

		
		if ($name == 'command_regex_parameter_name'){
				
			if ($this->_function_type == 'edit') {
				
				$format =	"<fieldset><legend>Edit Regex Parameter</legend><table style='width:500px'><tr>
							<td>%s</td><td><input style='width: 300px' name='command_regex_parameter_name' id='command_regex_parameter_name' type='text' class='text' value='".$this->_row['command_regex_parameter_name']."'></input></td>
							</tr>";

			} else {
				
				$format =	"<fieldset><legend>Add Regex Parameter</legend><table style='width:500px'><tr>
							<td>%s</td><td><input style='width: 300px' name='command_regex_parameter_name' id='command_regex_parameter_name' type='text' class='text' value=''></input></td>
							</tr>";
			}
	
			return sprintf($format,$label,$name,$name);
				
		} elseif($name == 'device_command_parameter_column_id'){
			
			if ($this->_function_type == 'edit') {
				
				$format	=	"<tr><td>%s</td><td>
							<select name='%s' id='%s' style='width: 300px'>";
			
				$format.=	"<option value='".$this->_row['device_command_parameter_column_id']."'>".$this->_row['parameter']."</option>";
				
			} else {
				
				$format	=	"<tr><td>%s</td><td>
							<select name='%s' id='%s' style='width: 300px'>";
			
				$format.=	"<option value=''>-----Select One----</option>";
				
			}

			$sql=	"SELECT dcpc.device_command_parameter_column_id, CONCAT(dcpt.table_name, '.', dcpc.column_name) AS parameter FROM device_command_parameter_columns dcpc
					INNER JOIN device_command_parameter_tables dcpt ON dcpt.device_command_parameter_table_id=dcpc.device_command_parameter_table_id
					INNER JOIN items i ON i.item_id=dcpc.column_use
					WHERE i.item_name='command_parameter'
					ORDER BY dcpt.table_name ASC, dcpc.column_name ASC
					";
			
			$command_parameter_columns = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
				
			foreach ($command_parameter_columns as $command_parameter_column) {
			
				$format.= "<option value='".$command_parameter_column['device_command_parameter_column_id']."'>".$command_parameter_column['parameter']."";
			
			}
				
			$format.="</select></td><td>%s</td></tr>";
				
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
			
		}

		
		if ($this->_function_type == 'add') {
			if($name == 'create'){
				$format="			<tr><td colspan='3' align='center'>
						
											<input name='%s' id='%s' type='submit' class='button' value='Create'></input>		
											<input name='command_regex_id' type='hidden' value='".$this->_variable."'></input>	
											<input name='device_function_map_id' type='hidden' value='".$this->_device_function_map_id."'></input>	
											<input name='device_command_id' type='hidden' value='".$this->_device_command_id."'></input>							
											</td></tr></table></fieldset>
											";	
		
				return sprintf($format,$name,$name);
		
			}
				
		} else if ($this->_function_type == 'edit') {
				
			if($name == 'edit'){
				$format="			<tr><td colspan='3' align='center'>
						
											<input name='%s' id='%s' type='submit' class='button' value='Edit'></input>
											<input name='command_regex_parameter_id' type='hidden' value='".$this->_variable."'></input>
											<input name='command_regex_id' type='hidden' value='".$this->_row['command_regex_id']."'></input>
											<input name='device_function_map_id' type='hidden' value='".$this->_device_function_map_id."'></input>
											<input name='device_command_id' type='hidden' value='".$this->_device_command_id."'></input>
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