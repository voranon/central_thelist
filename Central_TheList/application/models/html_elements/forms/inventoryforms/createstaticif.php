<?php

class thelist_inventoryform_createstaticif extends Zend_Form{
	
	public function __construct($function_type, $variable)
	{
		parent::__construct($function_type, $variable);

		$if_index_number   = new Zend_Form_Element_Textarea('if_index_number',
		array(
											'label'      => 'Index:',
											'decorators' => array(
		new createstaticifdecorator($function_type, $variable)
		)
		)
		);
		$if_index_number->setRequired(true);
		
		$if_default_name   = new Zend_Form_Element_Textarea('if_default_name',
		array(
													'label'      => 'Default Name:',
													'decorators' => array(
		new createstaticifdecorator($function_type, $variable)
		)
		)
		);
		$if_default_name->setRequired(true);

		$if_type_id = new Zend_Form_Element_Select('if_type_id',
		array(
											'label'      => 'Interface Type:',
											'decorators' => array(
		new createstaticifdecorator($function_type, $variable)
		)
		)
		);
		$if_type_id->setRegisterInArrayValidator(false);
		$if_type_id->setRequired(true);
	
		//is this an add or edit/delete function
		if ($function_type == 'add') {
			
			$create     	   = new Zend_Form_Element_Submit('create',
			array(
														'label'      =>'Create',
														'value'		 =>'D',
														'decorators' => array(
			new createstaticifdecorator($function_type, $variable)
			)
			)
			);
			
			$this->addElements(array($if_index_number,$if_default_name,$if_type_id,$create));
			
		} elseif ($function_type == 'edit') {
			
			$edit     	   = new Zend_Form_Element_Submit('edit',
			array(
																	'label'      =>'Edit',
																	'value'		 =>'E',
																	'decorators' => array(
			new createstaticifdecorator($function_type, $variable)
			)
			)
			);
			
			$delete     	   = new Zend_Form_Element_Submit('delete',
			array(
																	'label'      =>'Delete',
																	'value'		 =>'R',
																	'decorators' => array(
			new createstaticifdecorator($function_type, $variable)
			)
			)
			);
				
			$this->addElements(array($if_index_number,$if_default_name,$if_type_id,$edit,$delete));
	
		}


	}
}


class createstaticifdecorator extends Zend_Form_Decorator_Abstract
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
		if($name == 'if_index_number'){
		$format="<fieldset>
					<legend>Create Static Interface</legend>
						<table style='width:505px'>
							<tr>
								<td style='width:200px'>%s</td>
								<td style='width:150px'><input name='%s' id='%s' type='text' class='text'></input></td>
								<td>&nbsp;</td>
							</tr>";
			
			return sprintf($format,$label,$name,$name);
			
		} elseif ($name == 'if_default_name'){
		$format="<tr>
								<td style='width:200px'>%s</td>
								<td style='width:150px'><input name='%s' id='%s' type='text' class='text'></input></td>
								<td>&nbsp;</td>
							</tr>";
			
			return sprintf($format,$label,$name,$name);
			
		} else if($name == 'if_type_id'){
		$format="			<tr>
								<td>%s</td>
								<td>
								<select name='%s' id='%s' style='width: 300px'>";
		$format.=					"<option value=''>-----Select One----</option>";
		
			$sql1 =	"SELECT if_type_id, if_type_name FROM interface_types";
		
			$if_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql1);

		foreach($if_types as $if_type){
		$format.=					"<option value='".$if_type['if_type_id']."'>".$if_type['if_type_name']."</option>";
								}
		
		
		$format.="				</select>
								</td>
								<td>%s</td>
							</tr>";
			
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));

		}

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
		
		//if we are editing	
		} else if ($this->_function_type == 'edit') {
				
			$sql = "SELECT sit.static_if_type_id, sit.if_type_id, sit.if_index_number, sit.if_default_name, it.if_type_name FROM static_if_types sit
					LEFT OUTER JOIN interface_types it ON it.if_type_id=sit.if_type_id
					WHERE sit.static_if_type_id='".$this->_variable."'
					";
			
			$static_if_type = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
			if($name == 'if_index_number'){
				$format="<fieldset>
								<legend>Edit Static Interface</legend>
									<table style='width:505px'>
										<tr>
											<td style='width:200px'>%s</td>
											<td style='width:150px'><input name='%s' id='%s' type='text' class='text' value='".$static_if_type['if_index_number']."'></input></td>
											<td>&nbsp;</td>
										</tr>";
					
				return sprintf($format,$label,$name,$name);
					
			} elseif ($name == 'if_default_name'){
					
				$format="<tr>
										<td style='width:200px'>%s</td>
										<td style='width:150px'><input name='%s' id='%s' type='text' class='text' value='".$static_if_type['if_default_name']."'></input></td>
										<td>&nbsp;</td>
									</tr>";
				
				return sprintf($format,$label,$name,$name);
				
			} else if($name == 'if_type_id'){
				$format="			<tr>
											<td>%s</td>
											<td>
											<select name='%s' id='%s' style='width: 300px'>";
				$format.=					"<option value='".$static_if_type['if_type_id']."'>".$static_if_type['if_type_name']."</option>";
			
				$sql1 =	"SELECT if_type_id, if_type_name FROM interface_types";
			
				$if_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql1);
			
				foreach($if_types as $if_type){
					$format.=					"<option value='".$if_type['if_type_id']."'>".$if_type['if_type_name']."</option>";
				}
			
			
				$format.="				</select>
											</td>
											<td>%s</td>
										</tr>";
					
				return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
			
			} else if($name == 'edit'){
				$format="			<tr>
											<td colspan='2' align='right'>
											<input name='%s' id='%s' type='submit' class='button' value='Edit'></input>
											<input name='static_if_type_id' type='hidden' value='".$this->_variable."'></input>
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
