<?php

class rratypemapform extends Zend_Form{

	public function __construct($options=null)
	{
		parent::__construct($options);


		
	$monitoring_rra_type_id = new Zend_Form_Element_Select('monitoring_rra_type_id',
								array(
									'label'      	=> 'RRA Function:',
									'decorators' 	=> array(new rratypemapdecorator($options))
	)
	);
	$monitoring_rra_type_id->setRegisterInArrayValidator(false);
	$monitoring_rra_type_id->setRequired(false);
		
	if ($options['function_type'] == 'add') {	
		
	$create = new Zend_Form_Element_Submit('create',
	array(
									'label' => 'Create', 'value' => 'C',
									'decorators' => array(new rratypemapdecorator($options))
	)
	);
	
	$this->addElements(array(
	$monitoring_rra_type_id,
	$create)
	);
		
	} elseif ($options['function_type'] == 'edit') {
		
	$edit = new Zend_Form_Element_Submit('edit',
	array(
										'label' => 'Edit', 'value' => 'E',
										'decorators' => array(new rratypemapdecorator($options))
	
	)
	);
		
	$delete = new Zend_Form_Element_Submit('delete',
	array(
										'label' => 'Delete', 'value' => 'D',
										'decorators' => array(new rratypemapdecorator($options))
	
		)
	);
		
	$this->addElements(array(
	$monitoring_rra_type_id,
	$edit,
	$delete)
	);
	
		}
	
	}

}


class rratypemapdecorator extends Zend_Form_Decorator_Abstract
{
	private $database;
	private $_function_type;
	private $_monitoring_ds_id;
	private $_device_function_id;
	
	public function __construct($options=null){


		
		if (isset($options['function_type'])) {
				
			$this->_function_type = $options['function_type'];

		}
		if (isset($options['monitoring_ds_id'])){
			
			$this->_monitoring_ds_id = $options['monitoring_ds_id'];
				
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

		if ($name == 'monitoring_rra_type_id'){
			
			
			if ($this->_function_type == 'add') {
			
				$format	=	"<fieldset><legend>Data Sources</legend><table style='width:500px'>
							<tr><td>%s</td><td>
							<select name='%s' id='%s' style='width: 300px'>";
				
				$format.=	"<option value=''>-----Select One----</option>";
					
				$sql=	"SELECT GROUP_CONCAT(monitoring_rra_type_id) FROM monitoring_rra_type_mapping
						WHERE monitoring_ds_id='".$this->_monitoring_ds_id."'
						";
					
				$exclude = Zend_Registry::get('database')->get_thelist_adapter()->fetchone($sql);
				
				
				if ($exclude != null) {
					
					$sql2=	"SELECT * FROM monitoring_rra_types
							WHERE monitoring_rra_type_id NOT IN (".$exclude.")
							";
						
					
					
				} else {
					
					$sql2=	"SELECT * FROM monitoring_rra_types
							";
					
				}

				$avail_rras = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);

				if (isset($avail_rras['0'])) {  
					foreach ($avail_rras as $avail_rra) {
						
						$format.= "<option value='".$avail_rra['monitoring_rra_type_id']."'>".$avail_rra['consolidation_function']." | ".$avail_rra['acceptable_data_loss']." | ".$avail_rra['data_points_before_consolidation']." | ".$avail_rra['amount_of_data_points']."</option>";
						
					}
				}
				
				$format.="</select></td><td>%s</td></tr>";
				
				return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));

			}
		}
		
		if ($this->_function_type == 'add') {
			if($name == 'create'){
				$format="			<tr><td colspan='3' align='center'>
						
											<input name='%s' id='%s' type='submit' class='button' value='Create'></input>	
											<input name='device_function_id' type='hidden' value='".$this->_device_function_id."'></input>
											<input name='monitoring_ds_id' type='hidden' value='".$this->_monitoring_ds_id."'></input>										
											</td></tr></table></fieldset>
											";	
		
				return sprintf($format,$name,$name);
		
			}
				
		} else if ($this->_function_type == 'edit') {
				
			if($name == 'edit'){
				$format="			<tr><td colspan='3' align='center'>
						
											<input name='%s' id='%s' type='submit' class='button' value='Edit'></input>
											<input name='device_function_id' type='hidden' value='".$this->_device_function_id."'></input>
											<input name='monitoring_ds_id' type='hidden' value='".$this->_monitoring_ds_id."'></input>
											
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
?>