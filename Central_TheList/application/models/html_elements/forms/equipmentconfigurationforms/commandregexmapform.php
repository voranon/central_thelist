<?php

class thelist_equipmentconfigurationform_commandregexmapform extends Zend_Form{

	public function __construct($options=null)
	{
		parent::__construct($options);

	$base_regex	 = new Zend_Form_Element_Textarea('base_regex',
								array(
									'label'      	=> 'Base Regex:',
									'decorators' 	=> array(new commandregexmapdecorator($options))	
	)
	);
	$base_regex->setRequired(true);
	
	$match_yes_or_no = new Zend_Form_Element_Select('match_yes_or_no',
	array(
									'label'      	=> 'Match ?:',
									'decorators' 	=> array(new commandregexmapdecorator($options))
	)
	);
	$match_yes_or_no->setRegisterInArrayValidator(false);
	$match_yes_or_no->setRequired(true);
	
	$replace_yes_or_no = new Zend_Form_Element_Select('replace_yes_or_no',
	array(
										'label'      	=> 'Replace ?:',
										'decorators' 	=> array(new commandregexmapdecorator($options))
	)
	);
	$replace_yes_or_no->setRegisterInArrayValidator(false);
	$replace_yes_or_no->setRequired(true);
	
			
	if ($options['function_type'] == 'add') {	
		
	$create = new Zend_Form_Element_Submit('create',
	array(
									'label' => 'Create', 'value' => 'C',
									'decorators' => array(new commandregexmapdecorator($options))
	)
	);
	
	$this->addElements(array(
	$base_regex,
	$match_yes_or_no,
	$replace_yes_or_no,
	$create)
	);
		
	} elseif ($options['function_type'] == 'edit') {
		
	$edit = new Zend_Form_Element_Submit('edit',
	array(
										'label' => 'Edit', 'value' => 'E',
										'decorators' => array(new commandregexmapdecorator($options))
	
	)
	);
		
	$delete = new Zend_Form_Element_Submit('delete',
	array(
										'label' => 'Delete', 'value' => 'D',
										'decorators' => array(new commandregexmapdecorator($options))
	
		)
	);
		
	$this->addElements(array(
	$base_regex,
	$match_yes_or_no,
	$replace_yes_or_no,
	$edit,
	$delete)
	);
	
		}
	
	}

}


class commandregexmapdecorator extends Zend_Form_Decorator_Abstract
{
	private $database;
	private $_function_type;
	private $_variable;
	private $_row;
	private $_device_function_map_id;
	
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
		
		if ($this->_function_type == 'edit') {
			
			$sql =	"SELECT crm.command_regex_map_id, crm.device_command_id, cr.* FROM command_regex_mapping crm
					LEFT OUTER JOIN command_regexs cr ON cr.command_regex_id=crm.command_regex_id
					WHERE crm.command_regex_map_id='".$this->_variable."'
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

		if($name == 'base_regex'){
			
			if ($this->_function_type == 'edit') {
				
				$format =	"<fieldset><legend>Edit Regex</legend><table style='width:500px'><tr>
							<td>%s</td><td><input style='width: 300px' name='base_regex' id='base_regex' type='text' class='text' value='".$this->_row['base_regex']."'></input></td>
							</tr>";
					
				return sprintf($format,$label,$name,$name);
				
			} else {
				
				$format	=	"<fieldset><legend>Add Regex</legend><table style='width:500px'>";
			

				$format.=	"<tr><td>New Regex</td><td><input style='width: 300px' name='new_base_regex' id='new_base_regex' type='text' class='text' value=''></input></td>
							</tr>";
					
				return sprintf($format,$label,$name,$name);
			}
				
			} elseif ($name == 'match_yes_or_no'){
					
				if ($this->_function_type == 'edit') {
			
					$format	=	"<tr><td>%s</td><td>
										<select name='%s' id='%s' style='width: 300px'>";
					
					if ($this->_row['match_yes_or_no'] == '0') {
						
						$match = 'No';
						
					} elseif($this->_row['match_yes_or_no'] == '1') {
						
						$match = 'Match First';
						
					} elseif($this->_row['match_yes_or_no'] == '2') {
						
						$match = 'Match All';
						
					}
						
					$format.=	"<option value='".$this->_row['match_yes_or_no']."'>".$match."</option>";
			
				} else {
			
					$format	=	"<tr><td>%s</td><td>
										<select name='%s' id='%s' style='width: 300px'>";
						

					
				}

				$format.=	"<option value='1'>Match First</option>";
				$format.=	"<option value='2'>Match All</option>";
				$format.=	"<option value='0'>No</option>";
				$format.="</select></td><td>%s</td></tr>";
			
				return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
					
			} elseif ($name == 'replace_yes_or_no'){
					
				if ($this->_function_type == 'edit') {
			
					$format	=	"<tr><td>%s</td><td>
										<select name='%s' id='%s' style='width: 300px'>";
					
					if ($this->_row['replacement_regex'] == '0') {
						
						$match = 'No';
						
					} elseif($this->_row['replacement_regex'] == '1') {
						
						$match = 'Yes';
						
					}
						
					$format.=	"<option value='".$this->_row['replacement_regex']."'>".$match."</option>";
			
				} else {
			
					$format	=	"<tr><td>%s</td><td>
										<select name='%s' id='%s' style='width: 300px'>";
						

					
				}
				
				$format.=	"<option value='0'>No</option>";
				$format.=	"<option value='1'>Yes</option>";
				$format.="</select></td><td>%s</td></tr>";
			
				return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
					
			}
			
		
		if ($this->_function_type == 'add') {
			if($name == 'create'){
				$format="			<tr><td colspan='3' align='center'>
						
											<input name='%s' id='%s' type='submit' class='button' value='Create'></input>		
											<input name='device_command_id' type='hidden' value='".$this->_variable."'></input>	
											<input name='device_function_map_id' type='hidden' value='".$this->_device_function_map_id."'></input>								
											</td></tr></table></fieldset>
											";	
		
				return sprintf($format,$name,$name);
		
			}
				
		} else if ($this->_function_type == 'edit') {
				
			if($name == 'edit'){
				$format="			<tr><td colspan='3' align='center'>
						
											<input name='%s' id='%s' type='submit' class='button' value='Edit'></input>
											<input name='command_regex_map_id' type='hidden' value='".$this->_variable."'></input>
											<input name='device_command_id' type='hidden' value='".$this->_row['device_command_id']."'></input>
											<input name='device_function_map_id' type='hidden' value='".$this->_device_function_map_id."'></input>
											
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