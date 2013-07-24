<?php

class thelist_inventoryform_confoption extends Zend_Form{

	public function __construct($options=null)
	{
		parent::__construct($options);
		
		$conf_value_friendly_name	 = new Zend_Form_Element_Textarea('conf_value_friendly_name',
		array(
												'label'      	=> 'Friendly Name:',
												'decorators' 	=> array(new confoptiondecorator($options))	
		)
		);
		$conf_value_friendly_name->setRequired(false);
		
		$conf_value	 = new Zend_Form_Element_Textarea('conf_value',
		array(
												'label'      	=> 'Value:',
												'decorators' 	=> array(new confoptiondecorator($options))	
		)
		);
		$conf_value->setRequired(false);

		$unique_random_word_value = new Zend_Form_Element_Select('unique_random_word_value',
		array(
											'label'      	=> 'Use Unique Word:',
											'decorators' 	=> array(new confoptiondecorator($options))
		)
		);
		$unique_random_word_value->setRegisterInArrayValidator(false);
		$unique_random_word_value->setRequired(true);
		
		$random_value = new Zend_Form_Element_Select('random_value',
		array(
													'label'      	=> 'Use Random Value:',
													'decorators' 	=> array(new confoptiondecorator($options))
		)
		);
		$random_value->setRegisterInArrayValidator(false);
		$random_value->setRequired(true);
		
		$make_default = new Zend_Form_Element_Select('make_default',
		array(
													'label'      	=> 'Default Value:',
													'decorators' 	=> array(new confoptiondecorator($options))
		)
		);
		$make_default->setRegisterInArrayValidator(false);
		$make_default->setRequired(false);
	
	if ($options['function_type'] == 'edit') {
		
		$edit = new Zend_Form_Element_Submit('edit',
		array(
											'label' => 'Edit', 'value' => 'E',
											'decorators' => array(new confoptiondecorator($options))
		
		)
		);
		
		$delete = new Zend_Form_Element_Submit('delete',
		array(
											'label' => 'Delete', 'value' => 'D',
											'decorators' => array(new confoptiondecorator($options))
		
		)
		);
			
		$this->addElements(array(
		$conf_value_friendly_name,
		$conf_value,
		$unique_random_word_value,
		$random_value,
		$make_default,
		$edit,
		$delete)
		);
	
		} elseif ($options['function_type'] == 'add') {
		
		$create = new Zend_Form_Element_Submit('create',
		array(
											'label' => 'Create', 'value' => 'C',
											'decorators' => array(new confoptiondecorator($options))
		
		)
		);
			
		$this->addElements(array(
		$conf_value_friendly_name,
		$conf_value,
		$unique_random_word_value,
		$random_value,
		$make_default,
		$create)
		);
	
		}
	
	}

}


class confoptiondecorator extends Zend_Form_Decorator_Abstract
{
	private $database;
	private $_function_type;
	private $_variable;
	private $_if_type_id=null;
	private $_conf_value_id=null;
	private $_config_map_row;

	
	public function __construct($options=null){


		
		
		if (isset($options['function_type'])) {
		
			$this->_function_type = $options['function_type'];
		
		}
		if (isset($options['variable'])){
				
			$this->_variable = $options['variable'];
		
		}
		if (isset($options['if_type_id'])){
		
			$this->_if_type_id = $options['if_type_id'];
		
		}
		if (isset($options['conf_value_id'])){
		
			$this->_conf_value_id = $options['conf_value_id'];
		
		}
		
				
		if ($this->_function_type == 'edit' && isset($options['if_type_id'])) {
				
			$sql = 	"SELECT *, citm.conf_value_id AS default_value_id FROM configuration_values cv
					LEFT OUTER JOIN configuration_interface_type_mapping citm ON citm.conf_value_id=cv.conf_value_id
					WHERE cv.conf_value_id='".$this->_conf_value_id."'
					";
				
			$this->_config_map_row = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		} elseif ($this->_function_type == 'add' && isset($options['if_type_id'])) {
			
			$sql = 	"SELECT *, conf_value_id AS default_value_id FROM configuration_interface_type_mapping 
					WHERE conf_if_type_map_id='".$this->_variable."'
					";
			
			$this->_config_map_row = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
		} elseif ($this->_function_type == 'edit' && isset($options['eq_type_id'])) {
			
			
			
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

	if ($name == 'conf_value_friendly_name') {	
		
		if ($this->_function_type == 'edit' && $this->_if_type_id != null) {
			
			$format =	"<fieldset><legend>Edit Config Option</legend><table style='width:500px'><tr>
						<td>%s</td><td><input style='width: 300px' name='%s' type='text' class='text' value='".$this->_config_map_row['conf_value_friendly_name']."'></input></td>
						</tr>";

				
			return sprintf($format,$label,$name,$name);
			
		} elseif ($this->_function_type == 'add' && $this->_if_type_id != null) {
			
			$format =	"<fieldset><legend>Add Config Option</legend><table style='width:500px'><tr>
						<td>%s</td><td><input style='width: 300px' name='%s' type='text' class='text' value=''></input></td>
						</tr>";
			
			
			return sprintf($format,$label,$name,$name);
			
			
		}
		
		
	} elseif ($name == 'conf_value') {	
		
		if ($this->_function_type == 'edit' && $this->_if_type_id != null) {
			
			$format =	"<tr>
						<td>%s</td><td><input style='width: 300px' name='%s' type='text' class='text' value='".$this->_config_map_row['conf_value']."'></input></td>
						</tr>";

				
			return sprintf($format,$label,$name,$name);
			
		} elseif ($this->_function_type == 'add' && $this->_if_type_id != null) {
			
			$format =	"<tr>
						<td>%s</td><td><input style='width: 300px' name='%s' type='text' class='text' value=''></input></td>
						</tr>";

				
			return sprintf($format,$label,$name,$name);
			
		}
		
		
	} elseif ($name == 'unique_random_word_value'){
			
			if ($this->_function_type == 'edit' && $this->_if_type_id != null) {
				
				if ($this->_config_map_row['using_unique_random_word_value'] == '1') {
					$urv = 'Yes';
				} else {
					$urv = 'No';
				}
				
				$format =	"<tr><td>%s</td><td><select name='%s' id='%s' style='width: 300px'>
							<option value='".$this->_config_map_row['using_unique_random_word_value']."'>".$urv."</option>";
				
				$format.= "	<option value='1'>Yes</option>
							<option value='0'>No</option>
							";

				$format.="</select></td><td>%s</td></tr>";
			
				return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));

			} elseif ($this->_function_type == 'add' && $this->_if_type_id != null) {
				
				$format = "	<tr><td>%s</td><td><select name='%s' id='%s' style='width: 300px'>
							<option value='0'>No</option>
							<option value='1'>Yes</option>
							";

				$format.="</select></td><td>%s</td></tr>";
			
				return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));

			}

		} elseif ($name == 'random_value'){
			
			if ($this->_function_type == 'edit' && $this->_if_type_id != null) {
				
				if ($this->_config_map_row['using_random_value'] == '1') {
					$rv = 'Yes';
				} else {
					$rv = 'No';
				}
				
				$format =	"<tr><td>%s</td><td><select name='%s' id='%s' style='width: 300px'>
							<option value='".$this->_config_map_row['using_random_value']."'>".$rv."</option>";
				
				$format.= "	<option value='1'>Yes</option>
							<option value='0'>No</option>
							";

				$format.="</select></td><td>%s</td></tr>";
			
				return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));

			} elseif ($this->_function_type == 'add' && $this->_if_type_id != null) {
				
				$format = "	<tr><td>%s</td><td><select name='%s' id='%s' style='width: 300px'>
							<option value='0'>No</option>
							<option value='1'>Yes</option>
							";

				$format.="</select></td><td>%s</td></tr>";
			
				return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));

			}

		} elseif ($name == 'make_default'){
			
			if ($this->_function_type == 'edit' && $this->_if_type_id != null) {

				if ($this->_config_map_row['default_value_id'] == $this->_conf_value_id) {
					
					$format =	"<tr><td>%s</td><td><select name='%s' id='%s' style='width: 300px'>
								<option value='".$this->_conf_value_id."'>Yes</option>
								";
					

				} else {
					
					$format =	"<tr><td>%s</td><td><select name='%s' id='%s' style='width: 300px'>
								<option value='0'>No</option>
								<option value='1'>Yes</option>
								";
					
				}

				$format.="</select></td><td>%s</td></tr>";
			
				return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));

			} elseif ($this->_function_type == 'add' && $this->_if_type_id != null) {
				
				if ($this->_config_map_row['default_value_id'] == null) {
						
					$format = "		<tr><td>%s</td><td><select name='%s' id='%s' style='width: 300px'>
									<option value='1'>Yes</option>
									";
						
						
				} else {
						
					$format = "		<tr><td>%s</td><td><select name='%s' id='%s' style='width: 300px'>
									<option value='0'>No</option>
									<option value='1'>Yes</option>
									";
						
						
						
						
				}
				
				$format.="</select></td><td>%s</td></tr>";
				
			
				return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));

			}

		}  
	
		if($name == 'edit'){
				
				if ($this->_function_type == 'edit' && $this->_if_type_id != null) {

				$format="			<tr><td colspan='3' align='center'>
									<input name='%s' id='%s' type='submit' class='button' value='Edit'></input>
									<input name='conf_if_type_map_id' type='hidden' value='".$this->_variable."'></input>
									<input name='if_type_id' type='hidden' value='".$this->_if_type_id."'></input>
									<input name='conf_value_id' type='hidden' value='".$this->_conf_value_id."'></input>
									";	
		
				return sprintf($format,$name,$name);
					
			}
		
		} elseif($name == 'delete'){
				
				if ($this->_function_type == 'edit' && $this->_if_type_id != null) {

				$format="			<input name='%s' id='%s' type='submit' class='button' value='Delete'></input>
									<input name='conf_if_type_map_id' type='hidden' value='".$this->_variable."'></input>
									<input name='if_type_id' type='hidden' value='".$this->_if_type_id."'></input>
									<input name='conf_value_id' type='hidden' value='".$this->_conf_value_id."'></input>
									</td></tr></table></fieldset>
									";	
		
				return sprintf($format,$name,$name);
					
			}
		
		} elseif($name == 'create'){
				
				if ($this->_function_type == 'add' && $this->_if_type_id != null) {

				$format="			<tr><td colspan='3' align='center'>
									<input name='%s' id='%s' type='submit' class='button' value='Add'></input>
									<input name='conf_if_type_map_id' type='hidden' value='".$this->_variable."'></input>
									<input name='if_type_id' type='hidden' value='".$this->_if_type_id."'></input>
									</td></tr></table></fieldset>
									";	
		
				return sprintf($format,$name,$name);
					
			}
		
		}
		
	}
}