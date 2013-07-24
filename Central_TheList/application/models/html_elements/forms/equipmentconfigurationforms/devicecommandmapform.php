<?php

class thelist_equipmentconfigurationform_devicecommandmapform extends Zend_Form{

	public function __construct($options=null)
	{
		parent::__construct($options);

	$base_command	 = new Zend_Form_Element_Textarea('base_command',
								array(
									'label'      	=> 'Base Command:',
									'decorators' 	=> array(new devicecommandmapdecorator($options))	
	)
	);
	$base_command->setRequired(true);
	
	$api_id = new Zend_Form_Element_Select('api_id',
	array(
										'label'      	=> 'API:',
										'decorators' 	=> array(new devicecommandmapdecorator($options))
	)
	);
	$api_id->setRegisterInArrayValidator(false);
	$api_id->setRequired(true);

	$command_exe_order   = new Zend_Form_Element_Textarea('command_exe_order',
	array(
									'label'      => 'Order:',
									'decorators' => array(new devicecommandmapdecorator($options))
	)
	);
	$command_exe_order->setRequired(true);
		
			
	if ($options['function_type'] == 'add') {	
		
	$create = new Zend_Form_Element_Submit('create',
	array(
									'label' => 'Create', 'value' => 'C',
									'decorators' => array(new devicecommandmapdecorator($options))
	)
	);
	
	$this->addElements(array(
	$base_command,
	$command_exe_order,
	$api_id,
	$create)
	);
		
	} elseif ($options['function_type'] == 'edit') {
		
	$edit = new Zend_Form_Element_Submit('edit',
	array(
										'label' => 'Edit', 'value' => 'E',
										'decorators' => array(new devicecommandmapdecorator($options))
	
	)
	);
		
	$delete = new Zend_Form_Element_Submit('delete',
	array(
										'label' => 'Delete', 'value' => 'D',
										'decorators' => array(new devicecommandmapdecorator($options))
	
		)
	);
		
	$this->addElements(array(
	$base_command,
	$command_exe_order,
	$api_id,
	$edit,
	$delete)
	);
	
		}
	
	}

}


class devicecommandmapdecorator extends Zend_Form_Decorator_Abstract
{
	private $database;
	private $_function_type;
	private $_variable;
	private $_row;
	
	public function __construct($options=null){


		
		if (isset($options['function_type'])) {
				
			$this->_function_type = $options['function_type'];

		}
		if (isset($options['variable'])){
			
			$this->_variable = $options['variable'];
				
		}
		
		if ($this->_function_type == 'edit') {
			
			$sql =	"SELECT api.api_name, dcm.device_function_map_id, dcm.command_exe_order, dc.*  FROM device_command_mapping dcm
					LEFT OUTER JOIN device_commands dc ON dc.device_command_id=dcm.device_command_id
					LEFT OUTER JOIN apis api ON api.api_id=dc.api_id
					WHERE dcm.device_command_map_id='".$this->_variable."'
					";
			
			$this->_row = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
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

		if($name == 'base_command'){
			
			if ($this->_function_type == 'edit') {
				
				$format =	"<fieldset><legend>Command Edit</legend><table style='width:500px'><tr>
							<td>%s</td><td><textarea rows='10' style='width: 300px' name='base_command' id='base_command' type='text' class='text'>".$this->_row['base_command']."</textarea></td>
							</tr>";
					
				return sprintf($format,$label,$name,$name);
				
			} else {
				
				$format	=	"<fieldset><legend>Add Command</legend><table style='width:500px'>";

				$format.=	"<tr><td>New Command</td><td><textarea rows='10' style='width: 300px' name='new_base_command' id='new_base_command' type='text' class='text'></textarea></td>
							</tr>";
					
				return sprintf($format,$label,$name,$name);
				
			}
			
			} elseif($name == 'api_id'){
					
				if ($this->_function_type == 'edit') {
			
					$format	=	"<tr><td>%s</td><td>
										<select name='%s' id='%s' style='width: 300px'>";
						
					$format.=	"<option value='".$this->_row['api_id']."'>".$this->_row['api_name']."</option>";
			
				} else {
			
					$format	=	"<tr><td>%s</td><td>
										<select name='%s' id='%s' style='width: 300px'>";
						
					$format.=	"<option>-----Select One----</option>";
			
				}
			
				$sql=	"SELECT * FROM apis
						ORDER BY api_name
						";
									
				$apis = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
				foreach ($apis as $api) {
						
					$format.= "<option value='".$api['api_id']."'>".$api['api_name']."";
						
				}
			
				$format.="</select></td><td>%s</td></tr>";
			
				return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
					
			} elseif ($name == 'command_exe_order'){
					
			if ($this->_function_type == 'edit') {
				$command_exe_order = $this->_row['command_exe_order'];
			} else {
				$command_exe_order = '';
			}
									
				$format =	"<tr>
									<td>%s</td><td><input style='width: 300px' name='command_exe_order' id='command_exe_order' type='text' class='text' value='".$command_exe_order."'></input></td>
									</tr>";
			
				return sprintf($format,$label,$name,$name);
			
		}
		
		if ($this->_function_type == 'add') {
			if($name == 'create'){
				$format="			<tr><td colspan='3' align='center'>
						
											<input name='%s' id='%s' type='submit' class='button' value='Create'></input>		
											<input name='device_function_map_id' type='hidden' value='".$this->_variable."'></input>									
											</td></tr></table></fieldset>
											";	
		
				return sprintf($format,$name,$name);
		
			}
				
		} else if ($this->_function_type == 'edit') {
				
			if($name == 'edit'){
				$format="			<tr><td colspan='3' align='center'>
						
											<input name='%s' id='%s' type='submit' class='button' value='Edit'></input>
											<input name='device_command_map_id' type='hidden' value='".$this->_variable."'></input>
											<input name='device_command_id' type='hidden' value='".$this->_row['device_command_id']."'></input>
											<input name='device_function_map_id' type='hidden' value='".$this->_row['device_function_map_id']."'></input>
											
											</td></tr>
						";	
		
				return sprintf($format,$name,$name);
		
			} elseif($name == 'delete'){
				$format="			<tr><td colspan='3' align='center'>
								
													<input name='%s' id='%s' type='submit' class='button' value='Delete'></input>
													
													</td></tr></table></fieldset>
											";	
		
				return sprintf($format,$name,$name);
					
			}
		
		}
		
	}
}