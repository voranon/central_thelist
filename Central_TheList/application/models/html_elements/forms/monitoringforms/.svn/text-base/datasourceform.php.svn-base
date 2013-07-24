<?php

class datasourceform extends Zend_Form{

	public function __construct($options=null)
	{
		parent::__construct($options);

		

	$rrd_ds_name	 = new Zend_Form_Element_Textarea('rrd_ds_name',
	array(
									'label'      	=> 'Name:',
									'decorators' 	=> array(new datasourcedecorator($options))	
	)
	);
	$rrd_ds_name->setRequired(true);
		
	$rrd_ds_type_counter = new Zend_Form_Element_Select('rrd_ds_type_counter',
	array(
									'label'      	=> 'Counter Type:',
									'decorators' 	=> array(new datasourcedecorator($options))
	)
	);
	$rrd_ds_type_counter->setRegisterInArrayValidator(false);
	$rrd_ds_type_counter->setRequired(true);
	
	$rrd_step	 = new Zend_Form_Element_Textarea('rrd_step',
	array(
									'label'      	=> 'Time Between Data Points:',
									'decorators' 	=> array(new datasourcedecorator($options))	
	)
	);
	$rrd_step->setRequired(false);
	
	$rrd_heartbeat	 = new Zend_Form_Element_Textarea('rrd_heartbeat',
	array(
									'label'      	=> 'Heartbeat:',
									'decorators' 	=> array(new datasourcedecorator($options))	
	)
	);
	$rrd_heartbeat->setRequired(false);
	
	$rrd_max_value	 = new Zend_Form_Element_Textarea('rrd_max_value',
	array(
									'label'      	=> 'Max Value:',
									'decorators' 	=> array(new datasourcedecorator($options))	
	)
	);
	$rrd_max_value->setRequired(false);
	
	$rrd_min_value	 = new Zend_Form_Element_Textarea('rrd_min_value',
	array(
									'label'      	=> 'Min Value:',
									'decorators' 	=> array(new datasourcedecorator($options))	
	)
	);
	$rrd_min_value->setRequired(false);
	
	$data_source_description	 = new Zend_Form_Element_Textarea('data_source_description',
	array(
									'label'      	=> 'Desc:',
									'decorators' 	=> array(new datasourcedecorator($options))	
	)
	);
	$data_source_description->setRequired(false);
	
	
	
	if ($options['function_type'] == 'add') {
		
		$create = new Zend_Form_Element_Submit('create',
		array(
											'label' => 'Create', 'value' => 'D',
											'decorators' => array(new datasourcedecorator($options))
		
		)
		);
			
		$this->addElements(array(
		$rrd_ds_name,
		$rrd_ds_type_counter,
		$rrd_step,
		$rrd_heartbeat,
		$rrd_max_value,
		$rrd_min_value,
		$data_source_description,
		$create)
		);
	
		}
	
	}

}


class datasourcedecorator extends Zend_Form_Decorator_Abstract
{
	private $database;
	private $_function_type;
	private $_device_function_id;
		
	public function __construct($options=null){


		
		if (isset($options['function_type'])) {
				
			$this->_function_type = $options['function_type'];

		}
		if (isset($options['device_function_id'])){
			
			$this->_device_function_id = $options['device_function_id'];
				
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
		
		if ($name == 'rrd_ds_name') {
			if ($this->_function_type == 'add') {
				
				$format	=	"<fieldset><legend>Data Sources</legend><table style='width:500px'>";
				
				$format .=	"<tr>
							<td>%s</td><td><input style='width: 300px' name='%s' type='text' class='text' value=''></input></td>
							</tr>";

			}
			
			return sprintf($format,$label,$name,$name);

		} elseif ($name == 'rrd_ds_type_counter') {
			
			if ($this->_function_type == 'add') {
					
				$format	=	"	<tr><td>%s</td><td><select name='%s' id='%s' style='width: 300px'>
								<option value=''>---SELECT ONE---</option>
								<option value='COUNTER'>COUNTER</option>
								<option value='GAUGE'>GAUGE</option>
								</select></td><td>%s</td></tr>";
					
				
			
			}
			
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
			
			
		} elseif ($name == 'rrd_step') {
			
			if ($this->_function_type == 'add') {
				
				$format =	"<tr>
							<td>%s</td><td><input style='width: 300px' name='%s' type='text' class='text' value='60'></input></td>
							</tr>";

			}
			
			return sprintf($format,$label,$name,$name);
			
		} elseif ($name == 'rrd_heartbeat'){

			if ($this->_function_type == 'add') {
			
				$format =	"<tr>
							<td>%s</td><td><input style='width: 300px' name='%s' type='text' class='text' value='600'></input></td>
							</tr>";
			
			}
				
			return sprintf($format,$label,$name,$name);

		} elseif ($name == 'rrd_max_value'){
			
			if ($this->_function_type == 'add') {
				
				$format =	"<tr>
							<td>%s</td><td><input style='width: 300px' name='%s' type='text' class='text' value='U'></input></td>
							</tr>";

			}
			
			return sprintf($format,$label,$name,$name);

		} elseif ($name == 'rrd_min_value'){
			
			if ($this->_function_type == 'add') {
				
				$format =	"<tr>
							<td>%s</td><td><input style='width: 300px' name='%s' type='text' class='text' value='U'></input></td>
							</tr>";

			}
			
			return sprintf($format,$label,$name,$name);

		} elseif ($name == 'data_source_description'){
			
			if ($this->_function_type == 'add') {
				
				$format =	"<tr>
							<td>%s</td><td><input style='width: 300px' name='%s' type='text' class='text' value=''></input></td>
							</tr>";

			}
			
			return sprintf($format,$label,$name,$name);
			
		}
		
		 if ($this->_function_type == 'add') {
				
			if($name == 'create'){
				
				$format="			<tr><td colspan='3' align='center'>
									<input name='%s' id='%s' type='submit' class='button' value='Create'></input>
									<input name='device_function_id' type='hidden' value='".$this->_device_function_id."'></input>
									</td></tr></table></fieldset>
											";	
		
				return sprintf($format,$name,$name);
					
			}
		
		}
		
	}
}
?>