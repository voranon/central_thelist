<?php

class thelist_inventoryform_iftypefeature extends Zend_Form{

	public function __construct($options=null)
	{
		parent::__construct($options);

	$if_feature_id = new Zend_Form_Element_Select('if_feature_id',
	array(
										'label'      	=> 'Feature Name:',
										'decorators' 	=> array(new iftypefeaturedecorator($options))
	)
	);
	$if_feature_id->setRegisterInArrayValidator(false);
	$if_feature_id->setRequired(false);
	
	$if_type_feature_value	 = new Zend_Form_Element_Textarea('if_type_feature_value',
	array(
								'label'      	=> 'Feature Value:',
								'decorators' 	=> array(new iftypefeaturedecorator($options))	
	)
	);
	$if_type_feature_value->setRequired(false);
	
	if ($options['function_type'] == 'add') {
		
		$create = new Zend_Form_Element_Submit('create',
		array(
											'label' => 'Create', 'value' => 'D',
											'decorators' => array(new iftypefeaturedecorator($options))
		
		)
		);
			
		$this->addElements(array(
		$if_feature_id,
		$if_type_feature_value,
		$create)
		);
	
	} elseif ($options['function_type'] == 'edit') {
		
	$delete = new Zend_Form_Element_Submit('delete',
	array(
										'label' => 'Delete', 'value' => 'D',
										'decorators' => array(new iftypefeaturedecorator($options))
	
		)
	);
		
	$this->addElements(array(
	$if_feature_id,
	$if_type_feature_value,
	$delete)
	);
	
		}
	
	}

}


class iftypefeaturedecorator extends Zend_Form_Decorator_Abstract
{
	private $database;
	private $_function_type;
	private $_variable;
	private $_feature_type_map; 
		
	public function __construct($options=null){


		
		if (isset($options['function_type'])) {
				
			$this->_function_type = $options['function_type'];

		}
		if (isset($options['variable'])){
			
			$this->_variable = $options['variable'];
				
		}
		if ($this->_function_type == 'edit'){
				
			$sql =	"SELECT iftfm.if_type_feature_value, iftfm.if_type_id, ifeat.* FROM interface_type_feature_mapping iftfm
					INNER JOIN interface_features ifeat ON ifeat.if_feature_id=iftfm.if_feature_id
					WHERE iftfm.if_type_feature_map_id='".$this->_variable."'
					";
			
			$this->_feature_type_map = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
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

		if ($name == 'if_feature_id'){
			
			if ($this->_function_type == 'add') {
			
				$format	=	"<fieldset><legend>Map New Feature</legend><table style='width:500px'><tr>
							<tr><td>%s</td><td><select name='%s' id='%s' style='width: 300px'>";
				
			
				$sql=	"SELECT ifeat.* FROM interface_features ifeat";
					
				$features = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
				foreach ($features as $feature) {
						
					$format.= "<option value='".$feature['if_feature_id']."'>".$feature['if_feature_type']." - ".$feature['if_feature_name']."";
						
				}
			
				$format.="</select></td><td>%s</td></tr>";
			
				return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
				
			} elseif ($this->_function_type == 'edit') {
				
				$format	=	"<fieldset><legend>Delete Feature</legend><table style='width:500px'><tr>
							<tr><td>%s</td><td><select name='%s' id='%s' style='width: 300px'>";
				
				$format.= 	"<option value='".$this->_feature_type_map['if_feature_id']."'>".$this->_feature_type_map['if_feature_type']." - ".$this->_feature_type_map['if_feature_name']."";
				
				$format.="</select></td><td>%s</td></tr>";
					
				return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
				
			}
			
		} elseif ($name == 'if_type_feature_value'){
			
			if ($this->_function_type == 'add') {
				
				$format =	"<tr>
							<td>%s</td><td><input style='width: 300px' name='if_type_feature_value' type='text' class='text' value=''></input></td>
							</tr>";

				} elseif ($this->_function_type == 'edit') {
					
					$format =	"<tr>
								<td>%s</td><td><input style='width: 300px' name='if_type_feature_value' type='text' class='text' value='".$this->_feature_type_map['if_type_feature_value']."'></input></td>
								</tr>";
					
				}
			
			return sprintf($format,$label,$name,$name);

		}
		
		 if ($this->_function_type == 'add') {
				
			if($name == 'create'){
				
				$format="	<tr><td colspan='3' align='center'>
		 					<input name='%s' id='%s' type='submit' class='button' value='Create'></input>
		 					<input name='if_type_id' type='hidden' value='".$this->_variable."'></input>
		 					</td></tr></table></fieldset>
		 					";	
		 		
		 		return sprintf($format,$name,$name);
					
			} 
		 
		 } elseif ($this->_function_type == 'edit') {
		 	
		 	if ($name == 'delete') {
		 	
		 		$format="	<tr><td colspan='3' align='center'>
		 					<input name='%s' id='%s' type='submit' class='button' value='Delete'></input>
		 					<input name='if_type_feature_map_id' type='hidden' value='".$this->_variable."'></input>
		 					<input name='if_type_id' type='hidden' value='".$this->_feature_type_map['if_type_id']."'></input>
		 					</td></tr></table></fieldset>
		 					";	
		 		
		 		return sprintf($format,$name,$name);
		 	
		 	
		 	}

		}
		
	}
}