<?php

class thelist_equipmentform_addvlantointerface extends Zend_Form{

	public function __construct($options=null)
	{
		parent::__construct($options);

		
	$vlan_id = new Zend_Form_Element_Select('vlan_id',
								array(
									'label'      	=> 'VLAN ID:',
									'decorators' 	=> array(new addvlantointerfacedecorator($options))
	)
	);
	$vlan_id->setRegisterInArrayValidator(false);
	$vlan_id->setRequired(true);
	
	$vlan_type = new Zend_Form_Element_Select('vlan_type',
	array(
										'label'      	=> 'VLAN Type:',
										'decorators' 	=> array(new addvlantointerfacedecorator($options))
	)
	);
	$vlan_type->setRegisterInArrayValidator(false);
	$vlan_type->setRequired(true);
		
	if ($options['function_type'] == 'add') {	
		
	$create = new Zend_Form_Element_Submit('create',
	array(
									'label' => 'Create', 'value' => 'C',
									'decorators' => array(new addvlantointerfacedecorator($options))
	)
	);
	
	$this->addElements(array(
	$vlan_id,
	$vlan_type,
	$create)
	);
		
	} elseif ($options['function_type'] == 'edit') {
		
	$edit = new Zend_Form_Element_Submit('edit',
	array(
										'label' => 'Edit', 'value' => 'E',
										'decorators' => array(new addvlantointerfacedecorator($options))
	
	)
	);
		
	$delete = new Zend_Form_Element_Submit('delete',
	array(
										'label' => 'Delete', 'value' => 'D',
										'decorators' => array(new addvlantointerfacedecorator($options))
	
		)
	);
		
	$this->addElements(array(
	$vlan_id,
	$vlan_type,
	$edit,
	$delete)
	);
	
		}
	
	}

}


class addvlantointerfacedecorator extends Zend_Form_Decorator_Abstract
{
	private $database;
	private $_function_type;
	private $_variable;
	private $_eq_id;
	private $_if_id;
	private $vlan_row;
	
	public function __construct($options=null){


		
		if (isset($options['function_type'])) {
				
			$this->_function_type = $options['function_type'];

		}
		if (isset($options['variable'])){
			
			$this->_variable = $options['variable'];
				
		}
		if (isset($options['eq_id'])){
				
			$this->_eq_id = $options['eq_id'];
		
		}
		if (isset($options['if_id'])){
		
			$this->_if_id = $options['if_id'];
		
		}
		
		if ($this->_function_type == 'edit') {
			
			$sql = 	"SELECT * FROM vlans
					WHERE vlan_pri_key_id='".$this->_variable."'
					";
			
			$this->vlan_row = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
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

		if ($name == 'vlan_id'){
			
			$format=	"<fieldset><legend>VLANS</legend><table style='width:500px'><tr><tr><td>%s</td><td>
						<select name='%s' id='%s' style='width: 200px'>";
			
			
			if ($this->_function_type == 'add') {
				
				$format.=	"<option value=''>-----Select One----</option>";
				$format.=	"<option value='2'>VLAN ID: 2</option>
										<option value='3'>VLAN ID: 3</option>
										<option value='4'>VLAN ID: 4</option>
										<option value='5'>VLAN ID: 5</option>
										<option value='6'>VLAN ID: 6</option>
										<option value='7'>VLAN ID: 7</option>
										<option value='8'>VLAN ID: 8</option>
										<option value='9'>VLAN ID: 9</option>
										<option value='10'>VLAN ID: 10</option>
										<option value='11'>VLAN ID: 11</option>
										<option value='12'>VLAN ID: 12</option>
										<option value='13'>VLAN ID: 13</option>
										";
				
			} else {
				
				$format.=	"<option value='".$this->vlan_row['vlan_id']."'>VLAN ID: ".$this->vlan_row['vlan_id']."</option>";
				
			}
			
			
			
			
			
			
			$format.="</select></td><td>%s</td></tr>";
			
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));

		} elseif ($name == 'vlan_type') {
			
			$format=	"<tr><td>%s</td><td>
						<select name='%s' id='%s' style='width: 200px'>";
			
			if ($this->_function_type == 'add') {
			
				$format.=	"<option value=''>-----Select One----</option>";
				$format.=	"<option value='trunk'>trunk</option>
							<option value='native'>native</option>
							";
			
			} else {
			
				$format.=	"<option value='".$this->vlan_row['vlan_type']."'>".$this->vlan_row['vlan_type']."</option>";
			
			}
			
				
			$format.="</select></td><td>%s</td></tr>";
				
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
			
			
		}
		
		if ($this->_function_type == 'add') {
			if($name == 'create'){
				$format="			<tr><td colspan='3' align='center'>
						
											<input name='%s' id='%s' type='submit' class='button' value='Create'></input>	
											<input name='eq_id' type='hidden' value='".$this->_eq_id."'></input>
											<input name='if_id' type='hidden' value='".$this->_variable."'></input>									
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