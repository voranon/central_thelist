<?php

class serviceplanoptionmapform extends Zend_Form{

	public function __construct($function_type, $variable)
	{
		parent::__construct($function_type, $variable);

	$service_plan_option = new Zend_Form_Element_Select('service_plan_option_id',
								array(
										'label'      	=> 'Service Plan Option:',
										'decorators' 	=> array(new serviceplanoptionmapdecorator($function_type, $variable)
	)
	)
	);
	$service_plan_option->setRegisterInArrayValidator(false);
	$service_plan_option->setRequired(true);

	$service_plan_option_map_master = new Zend_Form_Element_Select('service_plan_option_map_master_id',
	array(
										'label'      	=> 'Service Plan Option Master:',
										'decorators' 	=> array(new serviceplanoptionmapdecorator($function_type, $variable)
	)
	)
	);
	$service_plan_option_map_master->setRegisterInArrayValidator(false);

	$service_plan_option_group = new Zend_Form_Element_Select('service_plan_option_group_id',
	array(
										'label'      	=> 'Service Plan Option Group:',
										'decorators' 	=> array(new serviceplanoptionmapdecorator($function_type, $variable)
	)
	)
	);
	$service_plan_option_group->setRegisterInArrayValidator(false);
		
	$service_plan_option_additional_install_time   = new Zend_Form_Element_Textarea('service_plan_option_additional_install_time',
	array(
										'label'      => 'Additional Install Time:',
										'decorators' => array(new serviceplanoptionmapdecorator($function_type, $variable)
	)
	)
	);
	$service_plan_option_additional_install_time->setRequired(true);
		
	$service_plan_option_default_mrc   = new Zend_Form_Element_Textarea('service_plan_option_default_mrc',
	array(
										'label'      => 'Default MRC:',
										'decorators' => array(new serviceplanoptionmapdecorator($function_type, $variable)
	)
	)
	);
	$service_plan_option_default_mrc->setRequired(true);
	
	$service_plan_option_default_nrc   = new Zend_Form_Element_Textarea('service_plan_option_default_nrc',
	array(
										'label'      => 'Default NRC:',
										'decorators' => array(new serviceplanoptionmapdecorator($function_type, $variable)
	)
	)
	);
	$service_plan_option_default_nrc->setRequired(true);
	
	$service_plan_option_default_mrc_term   = new Zend_Form_Element_Textarea('service_plan_option_default_mrc_term',
	array(
										'label'      => 'Default MRC Term:',
										'decorators' => array(new serviceplanoptionmapdecorator($function_type, $variable)
	)
	)
	);
	$service_plan_option_default_mrc_term->setRequired(true);
		
		
	
	if ($function_type == 'add') {	
		
	$create = new Zend_Form_Element_Submit('create',
	array(
										'label' => 'Create', 'value' => 'C',
										'decorators' => array(new serviceplanoptionmapdecorator($function_type, $variable)
	)
	)
	);
	
			$this->addElements(array(
			$service_plan_option,
			$service_plan_option_map_master,
			$service_plan_option_group,
			$service_plan_option_additional_install_time,
			$service_plan_option_default_mrc, 
			$service_plan_option_default_nrc, 
			$service_plan_option_default_mrc_term, 
			$create)
			);
		
	} elseif ($function_type == 'edit') {
		
	$edit = new Zend_Form_Element_Submit('edit',
	array(
										'label' => 'Edit', 'value' => 'E',
										'decorators' => array(new serviceplanoptionmapdecorator($function_type, $variable)
	)
	)
	);
		
	$delete = new Zend_Form_Element_Submit('delete',
	array(
										'label' => 'Delete', 'value' => 'D',
										'decorators' => array(new serviceplanoptionmapdecorator($function_type, $variable)
	)
	)
	);
	
	$optiondependencies = new Zend_Form_Element_Submit('optiondependencies',
	array(
												'label' => 'Dependencies', 'value' => 'Dep',
												'decorators' => array(new serviceplanoptionmapdecorator($function_type, $variable)
	)
	)
	);
		
			$this->addElements(array(
			$service_plan_option,
			$service_plan_option_map_master,
			$service_plan_option_group,
			$service_plan_option_additional_install_time,
			$service_plan_option_default_mrc, 
			$service_plan_option_default_nrc, 
			$service_plan_option_default_mrc_term, 
			$edit,
			$delete,
			$optiondependencies)
			);
	
		}	
	}
}


class serviceplanoptionmapdecorator extends Zend_Form_Decorator_Abstract
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
				
			$sql=	"SELECT * FROM service_plan_option_mapping
					WHERE service_plan_option_map_id='".$this->_variable."'
					";
				
			$service_plan_option_map = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
			
			$service_plan = $service_plan_option_map['service_plan_id'];
			
			$sql1 = 	"SELECT spo.*, spom.service_plan_option_map_id FROM service_plan_option_mapping spom
						LEFT OUTER JOIN service_plan_options spo ON spo.service_plan_option_id=spom.service_plan_option_id
						WHERE spom.service_plan_id='".$service_plan."'
						AND spom.service_plan_option_map_id!='".$service_plan_option_map['service_plan_option_map_id']."'
						ORDER BY spo.service_plan_option_type ASC
						";
			
			$service_plan_options = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql1);
			
		} 
		
		if($name == 'service_plan_option_id'){

			$format=	"<fieldset><legend>Create Service Plan Option Mapping</legend><table style='width:750px'>
					<tr><td>%s</td><td>
					<select name='%s' id='%s' style='width: 500px'>";
			
			if ($this->_function_type == 'edit') {
				
				$sql1 = "SELECT * FROM service_plan_options
						WHERE service_plan_option_id='".$service_plan_option_map['service_plan_option_id']."'
						";
									
				$service_plan_option = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql1);
				
				//remove all empty and too detailed coulumns from the result				
					foreach ($service_plan_option as $key => $value) {
							
						if ($key == 'directv_plan_description' || $key == 'directv_discount_description' || $key == 'activate' || $key == 'deactivate' || $value == '' || $value == null) {

							unset ($service_plan_option[$key]);
								
						}
							
					}
					
					$format.= "<option value='".$service_plan_option['service_plan_option_id']."'>";
					
					foreach ($service_plan_option as $key => $value) {
					
						if ($key == 'service_plan_option_type') {
								
							$sql2 =	"SELECT item_value FROM items
									WHERE item_id='".$value."'
													";
								
							$option_type_name = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
								
							$format.="".$option_type_name." - ";
								
						} elseif (preg_match("/internet/", $key, $empty) || preg_match("/directv/", $key, $empty)) {
					
							$format.= "".$value." | ";
					
						} elseif ($key != 'service_plan_option_id') {
					
							$format.= "".$key." - ".$value." | ";
								
						}
					
					}
					$format.= "</option>";
	
			} elseif ($this->_function_type == 'add') {
				
				$format.=	"<option value=''>-----Select One----</option>";
				
			}

			$sql1 = "SELECT * FROM service_plan_options 
					WHERE activate < NOW()
					AND (deactivate > NOW() OR deactivate IS NULL)
					ORDER BY service_plan_option_type ASC
					";
		
			$service_plan_options = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql1);
			
			//remove all empty and too detailed coulumns from the result
			$i=0; 
			foreach ($service_plan_options as $service_plan_option) {
				
				foreach ($service_plan_option as $key => $value) {
					
					if ($key == 'directv_plan_description' || $key == 'directv_discount_description' || $key == 'activate' || $key == 'deactivate' || $value == '' || $value == null) {
					
						unset ($service_plan_options[$i][$key]);
							
					}
					
				}
				$i++;
			}
			//the new clear array can be turned into a dropdown.

			$i=0;
			foreach($service_plan_options as $service_plan_option){
		
				$format.= "<option value='".$service_plan_option['service_plan_option_id']."'>";
				
				foreach ($service_plan_option as $key => $value) {

					if ($key == 'service_plan_option_type') {
					
						$sql2 =	"SELECT item_value FROM items
								WHERE item_id='".$value."'
								";
							
						$option_type_name = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
							
						$format.="".$option_type_name." - ";
					
					} elseif (preg_match("/internet/", $key, $empty) || preg_match("/directv/", $key, $empty)) {
						
						$format.= "".$value." | ";
						
					} elseif ($key != 'service_plan_option_id') {
						
						$format.= "".$key." - ".$value." | ";
					
					}

				}
				$format.= "</option>";
			}

				$format.="</select></td><td>%s</td></tr>";
			
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
			
		} else if($name == 'service_plan_option_map_master_id'){

			$format=	"<tr><td>%s</td><td>
						<select name='%s' id='%s' style='width: 500px'>";
			
			if ($this->_function_type == 'edit') {
				
			$sql1 = 	"SELECT spo.*, spom.service_plan_option_map_id FROM service_plan_option_mapping spom
						LEFT OUTER JOIN service_plan_options spo ON spo.service_plan_option_id=spom.service_plan_option_id
						WHERE spom.service_plan_option_map_id='".$service_plan_option_map['service_plan_option_map_master_id']."'
						AND spom.service_plan_option_map_id!='".$service_plan_option_map['service_plan_option_map_master_id']."'
						ORDER BY spo.service_plan_option_type ASC
						";
		
				$service_plan_option = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql1);

			if ($service_plan_option != '') {
				
				//remove all empty and too detailed coulumns from the result
				foreach ($service_plan_option as $key => $value) {
						
					if ($key == 'directv_plan_description' || $key == 'directv_discount_description' || $key == 'activate' || $key == 'deactivate' || $value == '' || $value == null) {
			
						unset ($service_plan_option[$key]);
			
					}
						
				}
					
				$format.= "<option value='".$service_plan_option['service_plan_option_id']."'>";
					
				foreach ($service_plan_option as $key => $value) {
						
					if ($key == 'service_plan_option_type') {
					
						$sql2 =	"SELECT item_value FROM items
								WHERE item_id='".$value."'
								";
							
						$option_type_name = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
							
						$format.="".$option_type_name." - ";
					
					} elseif (preg_match("/internet/", $key, $empty) || preg_match("/directv/", $key, $empty)) {
						
						$format.= "".$value." | ";
						
					} elseif ($key != 'service_plan_option_id' && $key != 'service_plan_option_map_id') {
						
						$format.= "".$key." - ".$value." | ";
					
					}
						
				}
				$format.= "</option>";
				
				$format.=	"<option value=''>-----Select None----</option>";
				
			} else {
				
				$format.=	"<option value=''>-----Select One----</option>";
				
			}
			
			} elseif ($this->_function_type == 'add') {
				
				$service_plan = $this->_variable;
				$format.=	"<option value=''>-----Select One----</option>";
				
				$sql1 = 	"SELECT spo.*, spom.service_plan_option_map_id FROM service_plan_option_mapping spom
										LEFT OUTER JOIN service_plan_options spo ON spo.service_plan_option_id=spom.service_plan_option_id
										WHERE spom.service_plan_id='".$service_plan."'
										ORDER BY spo.service_plan_option_type ASC
										";
				
				$service_plan_options = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql1);
			
			}
		
			//remove all empty and too detailed coulumns from the result
			$i=0; 
			foreach ($service_plan_options as $service_plan_option) {
				
				foreach ($service_plan_option as $key => $value) {
					
					if ($key == 'directv_plan_description' || $key == 'directv_discount_description' || $key == 'activate' || $key == 'deactivate' || $value == '' || $value == null) {
					
						unset ($service_plan_options[$i][$key]);
							
					}
					
				}
				$i++;
			}
			//the new clear array can be turned into a dropdown.

			$i=0;
			foreach($service_plan_options as $service_plan_option){
		
				$format.= "<option value='".$service_plan_option['service_plan_option_map_id']."'>";
				
				foreach ($service_plan_option as $key => $value) {

					if ($key == 'service_plan_option_type') {
					
						$sql2 =	"SELECT item_value FROM items
								WHERE item_id='".$value."'
								";
							
						$option_type_name = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
							
						$format.="".$option_type_name." - ";
					
					} elseif (preg_match("/internet/", $key, $empty) || preg_match("/directv/", $key, $empty)) {
						
						$format.= "".$value." | ";
						
					} elseif ($key != 'service_plan_option_id' && $key != 'service_plan_option_map_id') {
						
						$format.= "".$key." - ".$value." | ";
					
					}

				}
				$format.= "</option>";
			}

				$format.="</select></td><td>%s</td></tr>";
			
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
		
		} else if($name == 'service_plan_option_group_id'){
			$format=	"<tr><td>%s</td><td>
						<select name='%s' id='%s' style='width: 500px'>";
			
			if ($this->_function_type == 'edit') {
				
				$sql = "SELECT * FROM service_plan_option_groups
						WHERE service_plan_option_group_id='".$service_plan_option_map['service_plan_option_group_id']."'
						";
				$service_plan_option_group = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);

				
				$format.="<option value='".$service_plan_option_group['service_plan_option_group_id']."'>".$service_plan_option_group['service_plan_option_group_name']."</option>";
				//add a blank in case user wants to unselect the current group and create a new one.
				$format.=	"<option value=''>-----Create New Group----</option>";
				
				
			} else if ($this->_function_type == 'add') {
				
				$format.=	"<option value=''>-----Create New Group----</option>";
				
			}

			$sql1 = "SELECT DISTINCT(spom.service_plan_option_group_id), spog.* FROM service_plan_option_mapping spom
					LEFT OUTER JOIN service_plan_option_groups spog ON spog.service_plan_option_group_id=spom.service_plan_option_group_id
					WHERE spom.service_plan_id='".$this->_variable."'
					ORDER BY spog.service_plan_option_group_name ASC
					";
		
			$service_plan_option_groups = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql1);
			
			foreach($service_plan_option_groups as $service_plan_option_group){
			
				$format.="<option value='".$service_plan_option_group['service_plan_option_group_id']."'>".$service_plan_option_group['service_plan_option_group_name']."</option>";
								
			}

			$format.="</select></td><td>%s</td></tr>";
			
			//Form to make new group if required.
			$format.=	"<tr>
						<td></td><td>New Group Name: <input name='new_group_name' id='new_group_name' type='text' class='text'></input></td></tr>
						<tr><td></td><td>Required Quantity : <input name='new_required_quantity' id='new_required_quantity' type='text' class='text'></input></td></tr>
						<tr><td></td><td>Maximum Quantity: <input name='new_max_quantity' id='new_max_quantity' type='text' class='text'></input></td>
						</tr>";
			
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
			
		} elseif ($name == 'service_plan_option_additional_install_time'){
			
			if ($this->_function_type == 'edit') {
				
				$value = $service_plan_option_map['service_plan_option_additional_install_time'];
				
			} else if ($this->_function_type == 'add') {
				
				$value = '0';
				
			}
				
				$format=	"<tr><td height='30px'></td></tr>";
				$format.=	"<tr class='header'><td colspan='3'><center>Mapping information:</center></td></tr>
							<tr><td></td><td>%s	<input name='service_plan_option_additional_install_time' id='service_plan_option_additional_install_time' type='text' class='text' value='".$value."'></input></td>
							</tr>";
					
				return sprintf($format,$label,$name,$name);
				
		} elseif ($name == 'service_plan_option_default_mrc'){
			
			if ($this->_function_type == 'edit') {
			
				$value = $service_plan_option_map['service_plan_option_default_mrc'];
			
			} else if ($this->_function_type == 'add') {
			
				$value = '0';
			
			}

			$format=	"<tr><td></td><td>%s <input name='service_plan_option_default_mrc' id='service_plan_option_default_mrc' type='text' class='text' value='".$value."'></input></td>
						</tr>";
				
			return sprintf($format,$label,$name,$name);
				
		} elseif ($name == 'service_plan_option_default_nrc'){
		
			if ($this->_function_type == 'edit') {
					
				$value = $service_plan_option_map['service_plan_option_default_nrc'];
					
			} else if ($this->_function_type == 'add') {
					
				$value = '0';
					
			}
			
			$format=	"<tr><td></td><td>%s <input name='service_plan_option_default_nrc' id='service_plan_option_default_nrc' type='text' class='text' value='".$value."'></input></td>
						</tr>";
		
			return sprintf($format,$label,$name,$name);
		
		} elseif ($name == 'service_plan_option_default_mrc_term'){
		
			if ($this->_function_type == 'edit') {
					
				$value = $service_plan_option_map['service_plan_option_default_mrc_term'];
					
			} else if ($this->_function_type == 'add') {
					
				$value = '0';
					
			}
			
			$format=	"<tr><td></td><td>%s <input name='service_plan_option_default_mrc_term' id='service_plan_option_default_mrc_term' type='text' class='text' value='".$value."'></input></td>
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
									<input name='service_plan_option_map_id' type='hidden' value='".$this->_variable."'></input>
									
									</td></tr>
				";	
				
		return sprintf($format,$name,$name);
		
		} elseif($name == 'delete'){
			$format="			<tr><td colspan='3' align='center'>
						
											<input name='%s' id='%s' type='submit' class='button' value='Delete'></input>
											
											</td></tr></table></fieldset>
						";	
		
			return sprintf($format,$name,$name);
			
		} elseif($name == 'optiondependencies'){

				//get all the dependencies and create a list with edit buttons
		$sql = 	"SELECT * FROM service_plan_option_mapping
				WHERE service_plan_option_map_master_id='".$this->_variable."'
				";
		
		$service_plan_options = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);

		//set the header
			$format="<fieldset><legend>Option Dependencies</legend><table style='width:750px'>";
					
			foreach ($service_plan_options as $service_plan_option) {
		
			$option = new serviceplanoption($service_plan_option['service_plan_option_id']);
		
			//set additional attributes when mapped 
			$option->set_sp_option_map_attributes(
					$service_plan_option['service_plan_option_additional_install_time'], 
					$service_plan_option['service_plan_option_default_mrc'],
					$service_plan_option['service_plan_option_default_nrc'],
					$service_plan_option['service_plan_option_default_mrc_term'],
					$service_plan_option['service_plan_option_map_id'],
					$service_plan_option['service_plan_option_group_id'],
					$service_plan_option['service_plan_option_map_master_id']
					);

					$spom_group_detail = $option->get_sp_option_group_detail();
						
						$format.= 	"<tr class='header'>
									<td colspan='3' bgcolor='#66CCCC' class='display'>Group Name: '".$spom_group_detail['name']."', Required Quantity: '".$spom_group_detail['required_amount']."', Max Quantity: '".$spom_group_detail['max_amount']."'</td>
									<td class='display' bgcolor='#66CCCC' ><input class='button' type='button' service_plan_option_group_id='".$option->get_service_plan_option_group_id()."'id='serviceplanoptiongroup' value='Edit Group'></input></td></tr>
									";

						//get the messy details of the option and strip out the junk
							
						$option_attributes = $option->get_service_plan_option_attributes();
						
						foreach ($option_attributes as $key => $value) {
						
							if ( $key == 'internet_subnet_private_or_public' || $key == 'internet_subnet_private_or_public' || $key == 'internet_subnet_allocation_dhcp_or_static' || $key == 'internet_subnet_connected_or_routed' || $key == 'service_plan_option_id' || $key == 'activate' || $key == 'deactivate' || $value == '' || $value == null) {
									
								unset ($option_attributes[$key]);
						
							}
						}
						$format.="<tr class='header'><td class='display' style='width: 50px'>Edit</td>";
						
						foreach($option_attributes as $key => $value){
							
							if ($key == 'service_plan_option_type') {
						
							$format.="<td class='display' style='width: 300px'>".ereg_replace('_', " ", $key)."</td>";
							
							} else {
							
								$format.="<td class='display' style='width: 250px'>".ereg_replace('_', " ", $key)."</td>";
								
							}
						
						}
						$format.="</tr><tr><td class='display'>
																		
								<input class='button' type='button' id='editserviceplanoptionmap' service_plan_option_map_id='".$option->get_service_plan_option_map_id()."' value='Edit'></input></td>
											
								";
						
						
							
						foreach($option_attributes as $key => $value){
						
							if ($key == 'service_plan_option_type') {
									
								$sql2 =	"SELECT item_value FROM items
																WHERE item_id='".$value."'
																";
									
								$option_type_name = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
									
								$format.="<td class='display'>".$option_type_name."</td>";
									
							} else {
									
								$format.="<td class='display'>".$value."</td>";
									
							}
					}

				}
				
				$format.="</table></fieldset>";
				
				return sprintf($format,$name,$name);
			}
		}
	}
}