<?php

class thelist_inventoryform_addactiveequipmenttoinventoryform extends Zend_Form{

	public function __construct($options=null)
	{
		parent::__construct($options);

	$api_id = new Zend_Form_Element_Select('api_id',
	array(
										'label'      	=> 'API:',
										'decorators' 	=> array(new addactiveequipmenttoinventoryformdecorator($options))
	)
	);
	$api_id->setRegisterInArrayValidator(false);
	$api_id->setRequired(false);
	
	$unit_id = new Zend_Form_Element_Select('unit_id',
	array(
											'label'      	=> 'Unit:',
											'decorators' 	=> array(new addactiveequipmenttoinventoryformdecorator($options))
	)
	);
	$unit_id->setRegisterInArrayValidator(false);
	$unit_id->setRequired(false);
	
	$building_id = new Zend_Form_Element_Select('building_id',
	array(
												'label'      	=> 'Building:',
												'decorators' 	=> array(new addactiveequipmenttoinventoryformdecorator($options))
	)
	);
	$building_id->setRegisterInArrayValidator(false);
	$building_id->setRequired(false);
	
	$is_permanent_installation = new Zend_Form_Element_Select('is_permanent_installation',
	array(
											'label'      	=> 'Permanent Install:',
											'decorators' 	=> array(new addactiveequipmenttoinventoryformdecorator($options))
	)
	);
	$is_permanent_installation->setRegisterInArrayValidator(false);
	$is_permanent_installation->setRequired(false);
		
	$fqdn	 = new Zend_Form_Element_Textarea('fqdn',
	array(
										'label'      	=> 'FQDN:',
										'decorators' 	=> array(new addactiveequipmenttoinventoryformdecorator($options))	
	)
	);
	$fqdn->setRequired(false);
	
	$username	 = new Zend_Form_Element_Textarea('username',
	array(
										'label'      	=> 'Username:',
										'decorators' 	=> array(new addactiveequipmenttoinventoryformdecorator($options))	
	)
	);
	$username->setRequired(false);
	
	$password	 = new Zend_Form_Element_Textarea('password',
	array(
											'label'      	=> 'Password:',
											'decorators' 	=> array(new addactiveequipmenttoinventoryformdecorator($options))	
	)
	);
	$password->setRequired(false);
	
	$enablepassword	 = new Zend_Form_Element_Textarea('enablepassword',
	array(
											'label'      	=> 'Enable Password:',
											'decorators' 	=> array(new addactiveequipmenttoinventoryformdecorator($options))	
	)
	);
	$enablepassword->setRequired(false);
	
	
	if ($options['function_type'] == 'add') {
		
		$create = new Zend_Form_Element_Submit('create',
		array(
											'label' => 'Create', 'value' => 'D',
											'decorators' => array(new addactiveequipmenttoinventoryformdecorator($options))
		
		)
		);
			
		$this->addElements(array(
		$api_id,
		$building_id,
		$unit_id,
		$is_permanent_installation,
		$fqdn,
		$username,
		$password,
		$enablepassword,
		$create)
		);
	
		}
	
	}

}


class addactiveequipmenttoinventoryformdecorator extends Zend_Form_Decorator_Abstract
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

		if ($name == 'api_id'){
			
			if ($this->_function_type == 'add') {
			
				$format	=	"<fieldset><legend>Add active Equipment to Backup System</legend><table style='width:500px'><tr>
				<tr><td>%s</td><td><select name='%s' id='%s' style='width: 300px'>";
				
			
				$sql=	"SELECT * FROM apis";
					
				$apis = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
				$format.= "<option value='0'>--- SELECT ONE ---</option>";
				
				foreach ($apis as $api) {
						
					$format.= "<option value='".$api['api_id']."'>".strtoupper($api['api_name'])."</option>";
						
				}
			
				$format.="</select></td><td>%s</td></tr>";
			
				return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
				
			}
			
		} elseif ($name == 'building_id') {
			
			if ($this->_function_type == 'add') {
					
				$format	=	"<tr><td>%s</td><td><select name='%s' id='%s' style='width: 300px'>";
			
					
				$sql=	"SELECT building_id, building_name FROM buildings";
					
				$buildings = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
					
				$format.= "<option value='0'>--- SELECT ONE ---</option>";
				
				foreach ($buildings as $building) {
			
					$format.= "<option value='".$building['building_id']."'>".$building['building_name']."</option>";
			
				}
					
				$format.="</select></td><td>%s</td></tr>";
					
				return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
			
			}
			
			
		} elseif ($name == 'unit_id') {
			
			if ($this->_function_type == 'add') {
					
				$format	=	"<tr><td>%s</td><td><select name='%s' id='%s' style='width: 300px'>";
			
				//ajax populate	
				$format.= "<option value='0'>--- SELECT ONE ---</option>";
					
				$format.="</select></td><td>%s</td></tr>";
					
				return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
			
			}
			
			
		} elseif ($name == 'is_permanent_installation') {
			
			if ($this->_function_type == 'add') {
					
				$format	=	"<tr><td>%s</td><td><select name='%s' id='%s' style='width: 300px'>";
					
				$format.=	"<option value='1'>Yes</option>
							<option value='0'>No</option>";
					
				$format.="</select></td><td>%s</td></tr>";
					
				return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
			
			}
			
			
		} elseif ($name == 'fqdn'){
			
			if ($this->_function_type == 'add') {
				
				$format =	"<tr>
							<td>%s</td><td><input style='width: 300px' name='fqdn' type='text' class='text' value=''></input></td>
							</tr>";

			}
			
			return sprintf($format,$label,$name,$name);

		} elseif ($name == 'username'){
			
			if ($this->_function_type == 'add') {
				
				$format =	"<tr>
							<td>%s</td><td><input style='width: 300px' name='username' type='password' class='text' value=''></input></td>
							</tr>";

			}
			
			return sprintf($format,$label,$name,$name);

		} elseif ($name == 'password'){
			
			if ($this->_function_type == 'add') {
				
				$format =	"<tr>
							<td>%s</td><td><input style='width: 300px' name='password' type='password' class='text' value=''></input></td>
							</tr>";

			}
			
			return sprintf($format,$label,$name,$name);

		} elseif ($name == 'enablepassword'){
			
			if ($this->_function_type == 'add') {
				
				$format =	"<tr>
							<td>%s</td><td><input style='width: 300px' name='enablepassword' type='password' class='text' value=''></input></td>
							</tr>";

			}
			
			return sprintf($format,$label,$name,$name);

		}
		
		 if ($this->_function_type == 'add') {
				
			if($name == 'create'){
				$format="			<tr><td colspan='3' align='center'>
								
													<input name='%s' id='%s' type='submit' class='button' value='Create'></input>

													</td></tr></table></fieldset>
											";	
		
				return sprintf($format,$name,$name);
					
			}
		
		}
		
	}
}