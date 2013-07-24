<?php

class thelist_equipmentform_monguid extends Zend_Form{

	public function __construct($options=null)
	{
		parent::__construct($options);

		
	$new_mon_guid = new Zend_Form_Element_Select('new_mon_guid',
								array(
									'label'      	=> 'Create New ?:',
									'decorators' 	=> array(new monguiddecorator($options))
	)
	);
	$new_mon_guid->setRegisterInArrayValidator(false);
	$new_mon_guid->setRequired(true);
		
	if ($options['function_type'] == 'add') {	
		
	$create = new Zend_Form_Element_Submit('create',
	array(
									'label' => 'Create', 'value' => 'C',
									'decorators' => array(new monguiddecorator($options))
	)
	);
	
	$this->addElements(array(
	$new_mon_guid,
	$create)
	);
		
	} elseif ($options['function_type'] == 'edit') {
		
	$edit = new Zend_Form_Element_Submit('edit',
	array(
										'label' => 'Edit', 'value' => 'E',
										'decorators' => array(new monguiddecorator($options))
	
	)
	);
		
	$delete = new Zend_Form_Element_Submit('delete',
	array(
										'label' => 'Delete', 'value' => 'D',
										'decorators' => array(new monguiddecorator($options))
	
		)
	);
		
	$this->addElements(array(
	$new_mon_guid,
	$edit,
	$delete)
	);
	
		}
	
	}

}


class monguiddecorator extends Zend_Form_Decorator_Abstract
{
	private $database;
	private $_function_type;
	private $_eq_id;
	private $_if_id;

	
	public function __construct($options=null){


		
		if (isset($options['function_type'])) {
				
			$this->_function_type = $options['function_type'];

		}
		if (isset($options['eq_id'])){
				
			$this->_eq_id = $options['eq_id'];
		
		}
		if (isset($options['if_id'])){
		
			$this->_if_id = $options['if_id'];
		
		}
		
		if ($this->_function_type == 'edit') {
			
	
			
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

		if ($name == 'new_mon_guid'){
			
			$format=	"<fieldset><legend>Monitoring GUID</legend><table style='width:500px'><tr><tr><td>%s</td><td>
						<select name='%s' id='%s' style='width: 200px'>";
			
			
			if ($this->_function_type == 'add') {
				
				$format.=	"	<option value=''>-----Select One----</option>
								<option value='0'>No</option>
								<option value='1'>Yes</option>
								";
				
			} 		
			
			$format.="</select></td><td>%s</td></tr>";
			
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));

		} 
		
		if ($this->_function_type == 'add') {
			if($name == 'create'){
				$format="			<tr><td colspan='3' align='center'>
						
											<input name='%s' id='%s' type='submit' class='button' value='Create'></input>	
											<input name='eq_id' type='hidden' value='".$this->_eq_id."'></input>
											<input name='if_id' type='hidden' value='".$this->_if_id."'></input>									
											</td></tr></table></fieldset>
											";	
		
				return sprintf($format,$name,$name);
		
			}
				
		} else if ($this->_function_type == 'edit') {
				
			if($name == 'delete'){
				$format="			<tr><td colspan='3' align='center'>
								
													<input name='%s' id='%s' type='submit' class='button' value='Delete'></input>
													<input name='vlan_pri_key_id' type='hidden' value='".$this->_variable."'></input>
													<input name='eq_id' type='hidden' value='".$this->_eq_id."'></input>
													<input name='if_id' type='hidden' value='".$this->_if_id."'></input>	
													</td></tr></table></fieldset>
											";	
		
				return sprintf($format,$name,$name);
					
			}
		
		}
		
	}
}