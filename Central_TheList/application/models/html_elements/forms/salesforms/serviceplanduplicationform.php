<?php

class serviceplanduplicationform extends Zend_Form{

	public function __construct($options=null)
	{
		parent::__construct($options);

	$service_plan	 = new Zend_Form_Element_Select('service_plan_id',
								array(
									'label'      	=> 'Existing Service Plan:',
									'decorators' 	=> array(new serviceplanduplicationdecorator()
	)
	)
	);
	$service_plan->setRegisterInArrayValidator(false);
	$service_plan->setRequired(true);

	$new_service_plan_name   = new Zend_Form_Element_Textarea('new_service_plan_name',
	array(
										'label'      => 'Name:',
										'decorators' => array(new serviceplanduplicationdecorator()
	)
	)
	);
	$new_service_plan_name->setRequired(true);
		
	$duplicate = new Zend_Form_Element_Submit('duplicate',
	array(
									'label' => 'Duplicate', 'value' => 'D',
									'decorators' => array(new serviceplanduplicationdecorator()
	)
	)
	);
	
	$this->addElements(array(
	$service_plan,
	$new_service_plan_name,
	$duplicate
	)
	);
	
	}

}


class serviceplanduplicationdecorator extends Zend_Form_Decorator_Abstract
{
	private $database;
	
	public function __construct($service_plan_id=null)
	{
	

	
	}
	
	public function render($content)
	{

		$element = $this->getElement();
		$messages = $element->getMessages();
		$name    = htmlentities($element->getFullyQualifiedName());
		$label   = htmlentities($element->getLabel());
		$id      = htmlentities($element->getId());
		$value   = htmlentities($element->getValue());

		if($name == 'service_plan_id'){
			
			$format=	"<fieldset><legend>Duplicate Service Plan</legend><table style='width:500px'>
						<tr><td>%s</td><td>
						<select name='%s' id='%s' style='width: 300px'>";
			$format.=	"<option value=''>-----Select One----</option>";
				
			$sql=	"SELECT * FROM service_plans
					ORDER BY deactivate DESC
					";
				
			$service_plans = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			foreach ($service_plans as $service_plan) {
				
				$format.= "<option value='".$service_plan['service_plan_id']."'>".$service_plan['service_plan_name']."";
				
			}
			
			$format.="</select></td><td>%s</td></tr>";
			
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));

		} elseif ($name == 'new_service_plan_name'){
					
									
				$format =	"<tr>
									<td>%s</td><td><input style='width: 300px' name='service_plan_name' id='service_plan_name' type='text' class='text' value=''></input></td>
									</tr>";
			
				return sprintf($format,$label,$name,$name);
			
		} elseif($name == 'duplicate'){
				$format=	"<tr><td colspan='3' align='center'>
							<input name='%s' id='%s' type='submit' class='button' value='Duplicate'></input>
							</td></tr></table></fieldset>
							";	
				
		return sprintf($format,$name,$name);

		}
		
	}
}