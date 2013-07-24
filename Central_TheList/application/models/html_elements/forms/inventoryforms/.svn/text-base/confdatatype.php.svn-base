<?php

class thelist_inventoryform_confdatatype extends Zend_Form{

	public function __construct($options=null)
	{
		parent::__construct($options);

		$datatype = new Zend_Form_Element_Select('datatype',
		array(
											'label'      	=> 'Data Type:',
											'decorators' 	=> array(new confdatatypedecorator($options))
		)
		);
		$datatype->setRegisterInArrayValidator(false);
		$datatype->setRequired(true);
	
	if ($options['function_type'] == 'edit') {
		
		$edit = new Zend_Form_Element_Submit('edit',
		array(
											'label' => 'Edit', 'value' => 'E',
											'decorators' => array(new confdatatypedecorator($options))
		
		)
		);
			
		$this->addElements(array(
		$datatype,
		$edit)
		);
	
		}
	
	}

}


class confdatatypedecorator extends Zend_Form_Decorator_Abstract
{
	private $database;
	private $_function_type;
	private $_variable;
	private $_if_type_id=null;
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
				
		if ($this->_function_type == 'edit' && isset($options['if_type_id'])) {
				
			$sql = 	"SELECT * FROM configuration_interface_type_mapping citm
					INNER JOIN items i ON i.item_id=citm.conf_if_value_datatype
					WHERE citm.conf_if_type_map_id='".$this->_variable."'
					";
				
			$this->_config_map_row = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		} elseif ($this->_function_type == 'edit' && isset($options['eq_type_id'])) {
			
			$sql = 	"SELECT * FROM configuration_equipment_type_mapping
					conf_eq_type_map_id='".$this->_variable."'
					";

			$this->_config_map_row = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
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

		if ($name == 'datatype'){
			
			$sql = "SELECT * FROM items 
					WHERE item_type='datatypes'
					";
			$datatypes = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			if ($this->_function_type == 'edit' && $this->_if_type_id != null) {
				
				$format =	"<fieldset><legend>Edit Configuration</legend><table style='width:500px'><tr><td>%s</td>
							<td><select name='%s' id='%s' style='width: 300px'>
							<option value='".$this->_config_map_row['item_id']."'>".$this->_config_map_row['item_value']."</option>";
					
				foreach ($datatypes as $datatype){
		
						$format.= "<option value='".$datatype['item_id']."'>".$datatype['item_value']."</option>";
		
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
													</td></tr></table></fieldset>
											";	
		
				return sprintf($format,$name,$name);
					
			}
		
		}
		
	}
}