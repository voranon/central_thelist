<?php

class thelist_equipmentform_featureoninterface extends Zend_Form{

	public function __construct($options=null)
	{
		parent::__construct($options);

		
	$feature_name	 = new Zend_Form_Element_Textarea('feature_name',
	array(
										'label'      	=> 'Feature Name:',
										'decorators' 	=> array(new featureoninterfacedecorator($options))	
	)
	);
	$feature_name->setRequired(false);
	
	$feature_value	 = new Zend_Form_Element_Textarea('feature_value',
	array(
										'label'      	=> 'Feature Value:',
										'decorators' 	=> array(new featureoninterfacedecorator($options))	
	)
	);
	$feature_value->setRequired(false);
	
	
	if ($options['function_type'] == 'edit') {
		
		$edit = new Zend_Form_Element_Submit('edit',
		array(
											'label' => 'Edit', 'value' => 'E',
											'decorators' => array(new featureoninterfacedecorator($options))
		
		)
		);
			
		$this->addElements(array(
		$feature_name,
		$feature_value,
		$edit)
		);
	
		}
	
	}

}


class featureoninterfacedecorator extends Zend_Form_Decorator_Abstract
{
	private $database;
	private $_function_type;
	private $_variable;
	private $_eq_id;
	private $_if_id;
	private $_feature_map_row;
	
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
			
			$sql = 	"SELECT * FROM interface_feature_mapping ifm
					INNER JOIN interface_features ifeat ON ifeat.if_feature_id=ifm.if_feature_id
					WHERE ifm.if_feature_map_id='".$this->_variable."'
					";
			
			$this->_feature_map_row = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
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

		if ($name == 'feature_name'){
			
			if ($this->_function_type == 'edit') {
				
				$format =	"<fieldset><legend>Edit Feature</legend><table style='width:500px'><tr>
							<td>%s</td><td><input style='width: 300px' type='readonly' class='text' value='".$this->_feature_map_row['if_feature_name']."'></input></td>
							</tr>";

			}
			
			return sprintf($format,$label,$name,$name);

		} elseif ($name == 'feature_value'){
			
			if ($this->_function_type == 'edit') {
				
				$format =	"<tr>
							<td>%s</td><td><input style='width: 300px' name='if_feature_value' type='text' class='text' value='".$this->_feature_map_row['if_feature_value']."'></input></td>
							</tr>";

			}
			
			return sprintf($format,$label,$name,$name);

		} 
		
		 if ($this->_function_type == 'edit') {
				
			if($name == 'edit'){
				$format="			<tr><td colspan='3' align='center'>
								
													<input name='%s' id='%s' type='submit' class='button' value='Edit'></input>
													<input name='if_feature_map_id' type='hidden' value='".$this->_variable."'></input>
													<input name='eq_id' type='hidden' value='".$this->_eq_id."'></input>
													<input name='if_id' type='hidden' value='".$this->_if_id."'></input>	
													</td></tr></table></fieldset>
											";	
		
				return sprintf($format,$name,$name);
					
			}
		
		}
		
	}
}