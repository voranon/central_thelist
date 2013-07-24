<?php

class serviceplanservicemapform extends Zend_Form{

	public function __construct($function_type, $variable)
	{
		parent::__construct($function_type, $variable);

	$service_plan_group	 = new Zend_Form_Element_Select('service_plan_group_id',
								array(
										'label'      	=> 'Service Included:',
										'decorators' 	=> array(new serviceplanservicemapdecorator($function_type, $variable)
	)
	)
	);
	$service_plan_group->setRegisterInArrayValidator(false);
	$service_plan_group->setRequired(true);
	
	if ($function_type == 'add') {	
		
	$create = new Zend_Form_Element_Submit('create',
	array(
										'label' => 'Create', 'value' => 'C',
										'decorators' => array(new serviceplanservicemapdecorator($function_type, $variable)
	)
	)
	);
	
			$this->addElements(array(
			$service_plan_group,
			$create)
			);
		
	} elseif ($function_type == 'edit') {
		
	$edit = new Zend_Form_Element_Submit('edit',
	array(
										'label' => 'Edit', 'value' => 'E',
										'decorators' => array(new serviceplanservicemapdecorator($function_type, $variable)
	)
	)
	);
		
	$delete = new Zend_Form_Element_Submit('delete',
	array(
										'label' => 'Delete', 'value' => 'D',
										'decorators' => array(new serviceplanservicemapdecorator($function_type, $variable)
	)
	)
	);
		
			$this->addElements(array(
			$service_plan_group, 
			$edit,
			$delete)
			);
	
		}	
	}
}


class serviceplanservicemapdecorator extends Zend_Form_Decorator_Abstract
{
	
	private $database;
	private $_function_type;
	private $_variable;
	
	public function __construct($function_type=null, $variable=null){
	


		if ($function_type != null && $variable != null) {
				
			$this->_function_type = $function_type;
			$this->_variable = $variable;
				
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
		
		if($name == 'service_plan_group_id'){

			$format="<fieldset><legend>Create Service Mapping</legend><table style='width:750px'>
					<tr><td>%s</td><td>
					<select name='%s' id='%s' style='width: 500px'>";
			
			if ($this->_function_type == 'edit') {

				$sql=	"SELECT * FROM service_plan_group_mapping spgm
						LEFT OUTER JOIN service_plan_groups spg ON spg.service_plan_group_id=spgm.service_plan_group_id
						WHERE spgm.service_plan_group_map_id='".$this->_variable."'
						";
				
				$current_service = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
					
				$service_plan = $current_service['service_plan_id'];
				
				
				$format.= "<option value='".$current_service['service_plan_group_id']."'>".$current_service['service_plan_group_name']."</option>";
				$format.=	"<option value=''>-----Select One----</option>";
				
			} elseif ($this->_function_type == 'add') {
				
				$format.=	"<option value=''>-----Select One----</option>";
				
			}

			$sql1 = "SELECT * FROM service_plan_groups 
					ORDER BY service_plan_group_name DESC
					";
		
			$service_plan_services = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql1);
			
			foreach($service_plan_services as $service_plan_service){
		
				$format.= "<option value='".$service_plan_service['service_plan_group_id']."'>".$service_plan_service['service_plan_group_name']."</option>";

				}

				$format.="</select></td><td>%s</td></tr>";
			
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
			
		}
			
		if ($this->_function_type == 'add') {
			if($name == 'create'){
				$format="			<tr><td colspan='3' align='center'>
				
									<input name='%s' id='%s' type='submit' class='button' value='Create'></input>
									<input name='service_plan_id' type='hidden' value='".$this->_variable."'></input>
									
									</td></tr></table></fieldset>
				";	
				
		return sprintf($format,$name,$name);
		
			}
			
		} else if ($this->_function_type == 'edit') {
			
			if($name == 'edit'){
				$format="			<tr><td colspan='3' align='center'>
				
									<input name='%s' id='%s' type='submit' class='button' value='Edit'></input>
									<input name='service_plan_group_map_id' type='hidden' value='".$this->_variable."'></input>
									
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