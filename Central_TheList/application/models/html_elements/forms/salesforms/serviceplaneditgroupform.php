<?php

class serviceplaneditgroupform extends Zend_Form{

	public function __construct($function_type, $variable)
	{
		parent::__construct($function_type, $variable);

	$service_plan_group_required_quantity   = new Zend_Form_Element_Textarea('service_plan_group_required_quantity',
	array(
										'label'      => 'Required Quantity:',
										'decorators' => array(new serviceplangroupeditdecorator($function_type, $variable)
	)
	)
	);
	$service_plan_group_required_quantity->setRequired(true);

	$service_plan_group_max_quantity   = new Zend_Form_Element_Textarea('service_plan_group_max_quantity',
	array(
										'label'      => 'Max Quantity:',
										'decorators' => array(new serviceplangroupeditdecorator($function_type, $variable)
	)
	)
	);
	$service_plan_group_max_quantity->setRequired(false);

	$service_plan_group_group_name   = new Zend_Form_Element_Textarea('service_plan_group_group_name',
	array(
										'label'      => 'Group Name:',
										'decorators' => array(new serviceplangroupeditdecorator($function_type, $variable)
	)
	)
	);
	$service_plan_group_group_name->setRequired(true);
		
	
	$edit = new Zend_Form_Element_Submit('edit',
	array(
										'label' => 'Edit', 'value' => 'E',
										'decorators' => array(new serviceplangroupeditdecorator($function_type, $variable)
	)
	)
	);
		
	$delete = new Zend_Form_Element_Submit('delete',
	array(
										'label' => 'Delete', 'value' => 'D',
										'decorators' => array(new serviceplangroupeditdecorator($function_type, $variable)
	)
	)
	);
		
			$this->addElements(array(
			$service_plan_group_required_quantity,
			$service_plan_group_max_quantity,
			$service_plan_group_group_name,
			$edit,
			$delete)
			);

	}
}


class serviceplangroupeditdecorator extends Zend_Form_Decorator_Abstract
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
		
		if ($this->_function_type == 'edit_option_group') {
			
			$sql=	"SELECT service_plan_option_required_quantity AS required_quantity, service_plan_option_max_quantity AS max_quantity, service_plan_option_group_name AS grp_name FROM service_plan_option_groups
					WHERE service_plan_option_group_id='".$this->_variable."'
					";
			
			$service_plan_group = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
		} elseif ($this->_function_type == 'edit_eq_type_group') {
			
			$sql=	"SELECT service_plan_eq_type_required_quantity AS required_quantity, service_plan_eq_type_max_quantity AS max_quantity, service_plan_eq_type_group_name AS grp_name FROM service_plan_eq_type_groups
					WHERE service_plan_eq_type_group_id='".$this->_variable."'
					";
				
			$service_plan_group = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
		}

		if ($name == 'service_plan_group_required_quantity'){
			
			$format=	"<fieldset><legend>Service Plan Group</legend><table style='width:500px'>";
			
			$format.=	"<tr>
						<td>%s</td><td><input name='service_plan_group_required_quantity' id='service_plan_group_required_quantity' type='text' class='text' value='".$service_plan_group['required_quantity']."'></input></td>
						</tr>";
				
			return sprintf($format,$label,$name,$name);
				
		} elseif ($name == 'service_plan_group_max_quantity'){
		
			$format=	"<tr>
						<td>%s</td><td><input name='service_plan_group_max_quantity' id='service_plan_group_max_quantity' type='text' class='text' value='".$service_plan_group['max_quantity']."'></input></td>
						</tr>";
			
			return sprintf($format,$label,$name,$name);
		
		} elseif ($name == 'service_plan_group_group_name'){
			
			$format=	"<tr>
						<td>%s</td><td><input name='service_plan_group_group_name' id='service_plan_group_group_name' type='text' class='text' value='".$service_plan_group['grp_name']."'></input></td>
						</tr>";
					
			return sprintf($format,$label,$name,$name);
		}
			
		if($name == 'edit'){
				$format="			<tr><td colspan='3' align='center'>
									<input name='%s' id='%s' type='submit' class='button' value='Edit Group'></input>
									<input name='service_plan_group_group_id' type='hidden' value='".$this->_variable."'></input>
									<input name='group_function_type' type='hidden' value='".$this->_function_type."'></input>
									</td></tr>
									";	
				
				return sprintf($format,$name,$name);
		
		} elseif ($name == 'delete'){
		$format="						<tr><td colspan='3' align='center'>
										<input name='%s' id='%s' type='submit' class='button' value='Delete Group and Maps'></input>
										</td></tr></table></fieldset>
										";	
	
		return sprintf($format,$name,$name);
		
		}

	}
}