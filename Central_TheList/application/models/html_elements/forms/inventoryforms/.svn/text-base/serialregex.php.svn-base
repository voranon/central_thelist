<?php

class thelist_inventoryform_serialregex extends Zend_Form{
	
	public function __construct($function_type, $variable)
	{
		parent::__construct($function_type, $variable);

		$serial_regex   = new Zend_Form_Element_Textarea('serial_regex',
		array(
											'label'      => 'Serial Regular Expression:',
											'decorators' => array(
		new serialregexdecorator($function_type, $variable)
		)
		)
		);
		$serial_regex->setRequired(true);

		
		if ($function_type == 'add') {
				
			$create     	   = new Zend_Form_Element_Submit('create',
			array(
																'label'      =>'Create',
																'value'		 =>'D',
																'decorators' => array(
			new serialregexdecorator($function_type, $variable)
			)
			)
			);
				
			$this->addElements(array($serial_regex,$create));
				
		} elseif ($function_type == 'edit') {
				
			$edit     	   = new Zend_Form_Element_Submit('edit',
			array(
																			'label'      =>'Edit',
																			'value'		 =>'E',
																			'decorators' => array(
			new serialregexdecorator($function_type, $variable)
			)
			)
			);
				
			$delete     	   = new Zend_Form_Element_Submit('delete',
			array(
																			'label'      =>'Delete',
																			'value'		 =>'R',
																			'decorators' => array(
			new serialregexdecorator($function_type, $variable)
			)
			)
			);
		
			$this->addElements(array($serial_regex,$edit,$delete));

	}
}
}



class serialregexdecorator extends Zend_Form_Decorator_Abstract
{
	
	private $database;
	private $_function_type;
	private $_variable;

	public function __construct($function_type=null, $variable=null){
		

		
		if ($function_type != null && $variable != null) {
			
			$this->_function_type = $function_type;
			$this->_variable = $variable;
			
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
		
	if ($this->_function_type == 'add') {
		
		if($name == 'serial_regex'){
			$format="<fieldset>
							<legend>Create Serial Regex</legend>
								<table style='width:505px'>
									<tr>
										<td style='width:200px'>%s</td>
										<td style='width:150px'><input name='%s' id='%s' type='text' class='text'></input></td>
										<td>&nbsp;</td>
									</tr>";
				
			return sprintf($format,$label,$name,$name);
				
		}
	} else if ($this->_function_type == 'edit') {
		
		$sql = "SELECT * FROM eq_type_serial_match WHERE eq_type_serial_match_id='".$this->_variable."'";
			
		$eq_type_serial_match = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
		if($name == 'serial_regex'){
			$format="<fieldset>
												<legend>Edit Serial Regex</legend>
													<table style='width:505px'>
														<tr>
															<td style='width:200px'>%s</td>
															<td style='width:150px'><input name='%s' id='%s' type='text' class='text' value='".$eq_type_serial_match['regex']."'></input></td>
															<td>&nbsp;</td>
														</tr>";
		
			return sprintf($format,$label,$name,$name);
		
		
		
		}
	
	}

	if ($this->_function_type == 'add') {
		if($name == 'create'){
			$format="			<tr>
													<td colspan='3' align='center'>
													<input name='%s' id='%s' type='submit' class='button' value='Create'></input>
													<input name='eq_type_id' type='hidden' value='".$this->_variable."'></input>
													</td>
												</tr>
											</table>
										</fieldset>";	
			return sprintf($format,$name,$name);
		}
			
		} else if ($this->_function_type == 'edit') {
			
		
		if ($name == 'edit'){
				$format="			<tr>
											<td colspan='2' align='right'>
											<input name='%s' id='%s' type='submit' class='button' value='Edit'></input>
											<input name='eq_type_id' type='hidden' value='".$eq_type_serial_match['eq_type_id']."'></input>
											<input name='eq_type_serial_match_id' type='hidden' value='".$this->_variable."'></input>
											</td>";	
				return sprintf($format,$name,$name);
				
				
			} else if ($name == 'delete') {
				
				$format="					<td colspan='2' align='left'>
											<input name='%s' id='%s' type='submit' class='button' value='Delete'></input>
											</td>
										</tr>
									</table>
								</fieldset>";	
				return sprintf($format,$name,$name);
				
				
			}
		}
	}
}
		

