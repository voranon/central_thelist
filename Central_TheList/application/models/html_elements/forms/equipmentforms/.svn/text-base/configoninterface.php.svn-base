<?php

class thelist_equipmentform_configoninterface extends Zend_Form{

	public function __construct($options=null)
	{
		parent::__construct($options);

		
	$config_name	 = new Zend_Form_Element_Textarea('config_name',
	array(
										'label'      	=> 'Name:',
										'decorators' 	=> array(new configoninterfacedecorator($options))	
	)
	);
	$config_name->setRequired(false);
	
	$conf_value	 = new Zend_Form_Element_Textarea('conf_value',
	array(
										'label'      	=> 'Value:',
										'decorators' 	=> array(new configoninterfacedecorator($options))	
	)
	);
	$conf_value->setRequired(false);
	
	
	if ($options['function_type'] == 'edit') {
		
		$edit = new Zend_Form_Element_Submit('edit',
		array(
											'label' => 'Edit', 'value' => 'E',
											'decorators' => array(new configoninterfacedecorator($options))
		
		)
		);
			
		$this->addElements(array(
		$config_name,
		$conf_value,
		$edit)
		);
	
		}
	
	}

}


class configoninterfacedecorator extends Zend_Form_Decorator_Abstract
{
	private $database;
	private $_function_type;
	private $_variable;
	private $_eq_id;
	private $_if_id;
	private $_config_map_row;
	private $_config_values;

	
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
				
			$sql = 	"SELECT * FROM configuration_interface_mapping cim
					INNER JOIN configurations ci ON ci.conf_id=cim.conf_id
					INNER JOIN interfaces i ON i.if_id=cim.if_id
					WHERE cim.conf_if_map_id='".$this->_variable."'
					";
				
			$this->_config_map_row = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
			$sql2 = 	"SELECT * FROM configuration_values civ
						INNER JOIN configuration_interface_type_mapping citm ON citm.conf_if_type_map_id=civ.conf_if_type_map_id
						WHERE citm.eq_type_software_map_id = '".$options['eq_type_software_map_id']."'
						AND citm.conf_id='".$this->_config_map_row['conf_id']."'
						AND citm.if_type_id='".$this->_config_map_row['if_type_id']."'
						";

			$this->_config_values = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
			
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

		if ($name == 'config_name'){
			
			if ($this->_function_type == 'edit') {
				
				$format =	"<fieldset><legend>Edit Configuration</legend><table style='width:500px'><tr>
							<td>%s</td><td><input style='width: 300px' type='readonly' class='text' value='".$this->_config_map_row['conf_name']."'></input></td>
							</tr>";

			}
			
			return sprintf($format,$label,$name,$name);

		} elseif ($name == 'conf_value'){
			
			if ($this->_function_type == 'edit') {

				if (!isset($this->_config_values['1'])) {
					
					$format =	"<tr>
								<td>%s</td><td><input style='width: 300px' name='conf_value' type='text' class='text' value='".$this->_config_map_row['conf_if_value']."'></input></td>
								</tr>";
					
				} else {
					
					$sql = "SELECT cv.conf_value_friendly_name FROM configuration_interface_mapping cim
							INNER JOIN configuration_interface_type_mapping citm ON citm.conf_id=cim.conf_id
							INNER JOIN configuration_values cv ON cv.conf_if_type_map_id=citm.conf_if_type_map_id
							WHERE cim.conf_if_map_id='".$this->_variable."'
							AND cv.conf_value='".$this->_config_map_row['conf_if_value']."'
							";
					
					$current_item_name = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
					$format =	"<tr><td>%s</td>
								<td><select name='%s' id='%s' style='width: 300px'>
								<option value='".$this->_config_map_row['conf_if_value']."'>".$current_item_name."</option>";
					
				foreach($this->_config_values as $config_value){
		
						$format.= "<option value='".$config_value['conf_value']."'>".$config_value['conf_value_friendly_name']."</option>";
		
						}

				$format.="</select></td><td>%s</td></tr>";
			
					return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
					
				}
				
				

			}
			
			return sprintf($format,$label,$name,$name);

		} 
		
		 if ($this->_function_type == 'edit') {
				
			if($name == 'edit'){
				$format="			<tr><td colspan='3' align='center'>
								
													<input name='%s' id='%s' type='submit' class='button' value='Edit'></input>
													<input name='conf_if_map_id' type='hidden' value='".$this->_variable."'></input>
													<input name='eq_id' type='hidden' value='".$this->_eq_id."'></input>
													<input name='if_id' type='hidden' value='".$this->_if_id."'></input>	
													</td></tr></table></fieldset>
											";	
		
				return sprintf($format,$name,$name);
					
			}
		
		}
		
	}
}