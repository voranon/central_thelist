<?php

//require_once APPLICATION_PATH.'/models/equipment_types.php';

class serviceplaneqtypemapform extends Zend_Form{

	public function __construct($function_type, $variable)
	{
		parent::__construct($function_type, $variable);

	$eq_type	 = new Zend_Form_Element_Select('eq_type_id',
								array(
										'label'      	=> 'Service Plan Equipment:',
										'decorators' 	=> array(new serviceplaneqtypemapdecorator($function_type, $variable)
	)
	)
	);
	$eq_type->setRegisterInArrayValidator(false);
	$eq_type->setRequired(true);

	$service_plan_eq_type_map_master = new Zend_Form_Element_Select('service_plan_eq_type_map_master_id',
	array(
										'label'      	=> 'Service Plan Equipment Master:',
										'decorators' 	=> array(new serviceplaneqtypemapdecorator($function_type, $variable)
	)
	)
	);
	$service_plan_eq_type_map_master->setRegisterInArrayValidator(false);

	$service_plan_eq_type_group = new Zend_Form_Element_Select('service_plan_eq_type_group_id',
	array(
										'label'      	=> 'Service Plan Equipment Group:',
										'decorators' 	=> array(new serviceplaneqtypemapdecorator($function_type, $variable)
	)
	)
	);
	$service_plan_eq_type_group->setRegisterInArrayValidator(false);
		
	$service_plan_eq_type_additional_install_time   = new Zend_Form_Element_Textarea('service_plan_eq_type_additional_install_time',
	array(
										'label'      => 'Additional Install Time:',
										'decorators' => array(new serviceplaneqtypemapdecorator($function_type, $variable)
	)
	)
	);
	$service_plan_eq_type_additional_install_time->setRequired(true);
		
	$service_plan_eq_type_default_mrc   = new Zend_Form_Element_Textarea('service_plan_eq_type_default_mrc',
	array(
										'label'      => 'Default MRC:',
										'decorators' => array(new serviceplaneqtypemapdecorator($function_type, $variable)
	)
	)
	);
	$service_plan_eq_type_default_mrc->setRequired(true);
	
	$service_plan_eq_type_default_nrc   = new Zend_Form_Element_Textarea('service_plan_eq_type_default_nrc',
	array(
										'label'      => 'Default NRC:',
										'decorators' => array(new serviceplaneqtypemapdecorator($function_type, $variable)
	)
	)
	);
	$service_plan_eq_type_default_nrc->setRequired(true);
	
	$service_plan_eq_type_default_mrc_term   = new Zend_Form_Element_Textarea('service_plan_eq_type_default_mrc_term',
	array(
										'label'      => 'Default MRC Term:',
										'decorators' => array(new serviceplaneqtypemapdecorator($function_type, $variable)
	)
	)
	);
	$service_plan_eq_type_default_mrc_term->setRequired(true);
		
		
	
	if ($function_type == 'add') {	
		
	$create = new Zend_Form_Element_Submit('create',
	array(
										'label' => 'Create', 'value' => 'C',
										'decorators' => array(new serviceplaneqtypemapdecorator($function_type, $variable)
	)
	)
	);
	
			$this->addElements(array(
			$eq_type,
			$service_plan_eq_type_map_master,
			$service_plan_eq_type_group,
			$service_plan_eq_type_additional_install_time,
			$service_plan_eq_type_default_mrc, 
			$service_plan_eq_type_default_nrc, 
			$service_plan_eq_type_default_mrc_term, 
			$create)
			);
		
	} elseif ($function_type == 'edit') {
		
	$edit = new Zend_Form_Element_Submit('edit',
	array(
										'label' => 'Edit', 'value' => 'E',
										'decorators' => array(new serviceplaneqtypemapdecorator($function_type, $variable)
	)
	)
	);
		
	$delete = new Zend_Form_Element_Submit('delete',
	array(
										'label' => 'Delete', 'value' => 'D',
										'decorators' => array(new serviceplaneqtypemapdecorator($function_type, $variable)
	)
	)
	);
	
	$eqdependencies = new Zend_Form_Element_Submit('eqdependencies',
	array(
											'label' => 'Dependencies', 'value' => 'Dep',
											'decorators' => array(new serviceplaneqtypemapdecorator($function_type, $variable)
	)
	)
	);
		
			$this->addElements(array(
			$eq_type,
			$service_plan_eq_type_map_master,
			$service_plan_eq_type_group,
			$service_plan_eq_type_additional_install_time,
			$service_plan_eq_type_default_mrc, 
			$service_plan_eq_type_default_nrc, 
			$service_plan_eq_type_default_mrc_term, 
			$edit,
			$delete,
			$eqdependencies)
			);
	
		}	
	}
}


class serviceplaneqtypemapdecorator extends Zend_Form_Decorator_Abstract
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

		if ($this->_function_type == 'edit') {
				
			$sql=	"SELECT * FROM service_plan_eq_type_mapping spem
					LEFT OUTER JOIN equipment_types et ON et.eq_type_id=spem.eq_type_id
					WHERE spem.service_plan_eq_type_map_id='".$this->_variable."'
					";
				
			$service_plan_eq_type_map = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
			$service_plan = $service_plan_eq_type_map['service_plan_id'];
			
			$sql1 = 	"SELECT * FROM service_plan_eq_type_mapping spem
									LEFT OUTER JOIN equipment_types et ON et.eq_type_id=spem.eq_type_id
									WHERE spem.service_plan_id='".$service_plan."'
									AND spem.service_plan_eq_type_map_id!='".$service_plan_eq_type_map['service_plan_eq_type_map_id']."'
									ORDER BY et.eq_manufacturer DESC
									";
			
			$service_plan_eq_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql1);
			
			
			
		} 
		
		if($name == 'eq_type_id'){

			$format="<fieldset><legend>Create Service Plan Equipment Mapping</legend><table style='width:750px'>
					<tr><td>%s</td><td>
					<select name='%s' id='%s' style='width: 500px'>";
			
			if ($this->_function_type == 'edit') {

				
				$format.= "<option value='".$service_plan_eq_type_map['eq_type_id']."'>".$service_plan_eq_type_map['eq_manufacturer']." - ".$service_plan_eq_type_map['eq_model_name']."</option>";
				$format.=	"<option value=''>-----Select One----</option>";
				
			} elseif ($this->_function_type == 'add') {
				
				$format.=	"<option value=''>-----Select One----</option>";
				
			}

			$sql1 = "SELECT * FROM equipment_types 
					ORDER BY eq_manufacturer DESC
					";
		
			$service_plan_eq_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql1);
			
			foreach($service_plan_eq_types as $service_plan_eq_type){
		
				$format.= "<option value='".$service_plan_eq_type['eq_type_id']."'>".$service_plan_eq_type['eq_manufacturer']." - ".$service_plan_eq_type['eq_model_name']."</option>";

				}

				$format.="</select></td><td>%s</td></tr>";
			
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
			
		} else if($name == 'service_plan_eq_type_map_master_id'){

			$format=	"<tr><td>%s</td><td>
						<select name='%s' id='%s' style='width: 500px'>";
			
			if ($this->_function_type == 'edit') {
				
				$sql=	"SELECT * FROM service_plan_eq_type_mapping spem
						LEFT OUTER JOIN equipment_types et ON et.eq_type_id=spem.eq_type_id
						WHERE spem.service_plan_eq_type_map_id='".$service_plan_eq_type_map['service_plan_eq_type_map_master_id']."'
						";
			
				$service_plan_eq_type_master = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

				
			if ($service_plan_eq_type_master != '') {
				
					
					$format.= "<option value='".$service_plan_eq_type_master['service_plan_eq_type_map_id']."'>".$service_plan_eq_type_master['eq_manufacturer']." - ".$service_plan_eq_type_master['eq_model_name']."</option>";
					$format.="<option value=''>-----Select None----</option>";
				
			} else {
				
				$format.=	"<option value=''>-----Select One----</option>";
				
			}
			
			} elseif ($this->_function_type == 'add') {
				
				$service_plan = $this->_variable;
				
				$sql1 = 	"SELECT * FROM service_plan_eq_type_mapping spem
										LEFT OUTER JOIN equipment_types et ON et.eq_type_id=spem.eq_type_id
										WHERE spem.service_plan_id='".$service_plan."'
										ORDER BY et.eq_manufacturer DESC
										";
				
				$service_plan_eq_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql1);
			
				$format.=	"<option value=''>-----Select One----</option>";
			
			}
		


			foreach ($service_plan_eq_types as $service_plan_eq_type) {
				
				$format.= "<option value='".$service_plan_eq_type['service_plan_eq_type_map_id']."'>".$service_plan_eq_type['eq_manufacturer']." - ".$service_plan_eq_type['eq_model_name']."</option>";
				
			}

			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
		
		} else if($name == 'service_plan_eq_type_group_id'){
			$format=	"<tr><td>%s</td><td>
						<select name='%s' id='%s' style='width: 500px'>";
			
			if ($this->_function_type == 'edit') {
				
				$sql = "SELECT * FROM service_plan_eq_type_groups
						WHERE service_plan_eq_type_group_id='".$service_plan_eq_type_map['service_plan_eq_type_group_id']."'
						";
				$service_plan_eq_type_group = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

				
				$format.="<option value='".$service_plan_eq_type_group['service_plan_eq_type_group_id']."'>".$service_plan_eq_type_group['service_plan_eq_type_group_name']."</option>";
				//add a blank in case user wants to unselect the current group and create a new one.
				$format.=	"<option value=''>-----Create New Group----</option>";
				
				
			} else if ($this->_function_type == 'add') {
				
				$format.=	"<option value=''>-----Create New Group----</option>";
				
			}

			$sql1 = "SELECT DISTINCT(spem.service_plan_eq_type_group_id), speg.* FROM service_plan_eq_type_mapping spem
					LEFT OUTER JOIN service_plan_eq_type_groups speg ON speg.service_plan_eq_type_group_id=spem.service_plan_eq_type_group_id
					WHERE spem.service_plan_id='".$this->_variable."'
					ORDER BY speg.service_plan_eq_type_group_name ASC
					";
		
			$service_plan_eq_type_groups = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql1);
			
			foreach($service_plan_eq_type_groups as $service_plan_eq_type_group){
			
				$format.="<option value='".$service_plan_eq_type_group['service_plan_eq_type_group_id']."'>".$service_plan_eq_type_group['service_plan_eq_type_group_name']."</option>";
								
			}

			$format.="</select></td><td>%s</td></tr>";
			
			//Form to make new group if required.
			$format.=	"<tr>
						<td></td><td>New Group Name: <input name='new_group_name' id='new_group_name' type='text' class='text'></input></td></tr>
						<tr><td></td><td>Required Quantity : <input name='new_required_quantity' id='new_required_quantity' type='text' class='text'></input></td></tr>
						<tr><td></td><td>Maximum Quantity: <input name='new_max_quantity' id='new_max_quantity' type='text' class='text'></input></td>
						</tr>";
			
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
			
		} elseif ($name == 'service_plan_eq_type_additional_install_time'){
			
			if ($this->_function_type == 'edit') {
				
				$value = $service_plan_eq_type_map['service_plan_eq_type_additional_install_time'];
				
			} else if ($this->_function_type == 'add') {
				
				$value = '0';
				
			}
				
				$format=	"<tr><td height='30px'></td></tr>";
				$format.=	"<tr class='header'><td colspan='3'><center>Mapping information:</center></td></tr>
							<tr><td></td><td>%s	<input name='service_plan_eq_type_additional_install_time' id='service_plan_eq_type_additional_install_time' type='text' class='text' value='".$value."'></input></td>
							</tr>";
					
				return sprintf($format,$label,$name,$name);
				
		} elseif ($name == 'service_plan_eq_type_default_mrc'){
			
			if ($this->_function_type == 'edit') {
			
				$value = $service_plan_eq_type_map['service_plan_eq_type_default_mrc'];
			
			} else if ($this->_function_type == 'add') {
			
				$value = '0';
			
			}

			$format=	"<tr><td></td><td>%s <input name='service_plan_eq_type_default_mrc' id='service_plan_eq_type_default_mrc' type='text' class='text' value='".$value."'></input></td>
						</tr>";
				
			return sprintf($format,$label,$name,$name);
				
		} elseif ($name == 'service_plan_eq_type_default_nrc'){
		
			if ($this->_function_type == 'edit') {
					
				$value = $service_plan_eq_type_map['service_plan_eq_type_default_nrc'];
					
			} else if ($this->_function_type == 'add') {
					
				$value = '0';
					
			}
			
			$format=	"<tr><td></td><td>%s <input name='service_plan_eq_type_default_nrc' id='service_plan_eq_type_default_nrc' type='text' class='text' value='".$value."'></input></td>
						</tr>";
		
			return sprintf($format,$label,$name,$name);
		
		} elseif ($name == 'service_plan_eq_type_default_mrc_term'){
		
			if ($this->_function_type == 'edit') {
					
				$value = $service_plan_eq_type_map['service_plan_eq_type_default_mrc_term'];
					
			} else if ($this->_function_type == 'add') {
					
				$value = '0';
					
			}
			
			$format=	"<tr><td></td><td>%s <input name='service_plan_eq_type_default_mrc_term' id='service_plan_eq_type_default_mrc_term' type='text' class='text' value='".$value."'></input></td>
						</tr>";
		
			return sprintf($format,$label,$name,$name);
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
									<input name='service_plan_eq_type_map_id' type='hidden' value='".$this->_variable."'></input>
									
									</td></tr>
				";	
				
		return sprintf($format,$name,$name);
		
			} elseif($name == 'delete'){
			$format="			<tr><td colspan='3' align='center'>
						
											<input name='%s' id='%s' type='submit' class='button' value='Delete'></input>
											
											</td></tr></table></fieldset>
									";	
		
			return sprintf($format,$name,$name);
			
			} elseif($name == 'eqdependencies'){

				//get all the dependencies and create a list with edit buttons
				
				$sql =	"SELECT * FROM service_plan_eq_type_mapping
						WHERE service_plan_eq_type_map_master_id='".$this->_variable."'
						";
				
					$service_plan_eq_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
					
					//set the header
					$format="<fieldset><legend>Equipment Dependencies</legend><table style='width:750px'>";
					
					$format.="<tr class='header'><td style='width: 50px'>Edit</td><td style='width: 450px'>Equipment Manufacturer</td><td style='width: 250px'>Equipment Model</td></tr>";
					foreach ($service_plan_eq_types as $service_plan_eq_type) {
						
						$eq_type = new equipment_types($service_plan_eq_type['eq_type_id']);
						
						$eq_type->set_sp_eq_type_map_attributes(
						$service_plan_eq_type['service_plan_eq_type_additional_install_time'],
						$service_plan_eq_type['service_plan_eq_type_default_mrc'],
						$service_plan_eq_type['service_plan_eq_type_default_nrc'],
						$service_plan_eq_type['service_plan_eq_type_default_mrc_term'],
						$service_plan_eq_type['service_plan_eq_type_map_id'],
						$service_plan_eq_type['service_plan_eq_type_group_id'],
						$service_plan_eq_type['service_plan_eq_type_map_master_id']
						);
						$spetm_group_detail = $eq_type->get_sp_eq_type_group_detail();
						
						$format.= 	"<tr class='header'>
									<td colspan='2' bgcolor='#66CCCC' class='display'>Group Name: '".$spetm_group_detail['name']."', Required Quantity: '".$spetm_group_detail['required_amount']."', Max Quantity: '".$spetm_group_detail['max_amount']."'</td>
									<td class='display' bgcolor='#66CCCC'><input class='button' type='button' service_plan_eq_type_group_id='".$eq_type->get_service_plan_eq_type_group_id()."'id='serviceplaneqtypegroup' value='Edit Group'></input></td></tr>
									";
						$format.="<tr><td><input class='button' type='button' id='editserviceplaneqtypemap' service_plan_eq_type_map_id='".$eq_type->get_service_plan_eq_type_map_id()."' value='Edit'></input></td>";
						$format.="<td class='display'>".$eq_type->get_eq_manufacturer()."</td><td class='display'>".$eq_type->get_eq_model_name()."</td></tr>";
					}
								
					$format.="</table></fieldset>";

				return sprintf($format,$name,$name);
					
			}
		
			}
		
	}
}