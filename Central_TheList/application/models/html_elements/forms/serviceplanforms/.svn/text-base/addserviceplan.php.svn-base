<?php
class thelist_serviceplanform_addserviceplan extends Zend_Form{
	
	private $database;

	
	public function __construct($options = null,$service_plan_id=null)
	{
		parent::__construct($options);

		
		
			
		
		
		$service_plan_name   = new Zend_Form_Element_Text('serviceplan_name',
														array(
															'label'      => 'Service Plan Name:',
															'value'      => '',
															'decorators' => array(
																				new addserviceplan_decorator()
														 						 )
														 	)
													  );
		$service_plan_name->setRequired(true);
		
		
		//$a = new Validator_s_a();//
		
		$validator_serviceplan_name = new Thelist_Serviceplanvalidator_name();
		$service_plan_name->addValidator($validator_serviceplan_name);
		
		
		$service_plan_type   = new Zend_Form_Element_Select('service_plan_type',
															array(
																'label'      => 'Service Plan Type:',
																'decorators' => array(
																					new addserviceplan_decorator()
																					)
																 )
															);
		$service_plan_type->setRegisterInArrayValidator(false);
		$service_plan_type->setRequired(true);
		
		$service_plan_desc   = new Zend_Form_Element_Textarea('serviceplan_desc',
														  array(
															  'label'      => 'Service Plan Description:',
															  'value'      => '',
															  'decorators' => array(
																				new addserviceplan_decorator()
																					 )
																)
														 );
		
		
		
		$permanent_install   =  new Zend_Form_Element_Select('service_plan_permamnent_install',
															array(
																'label'      => 'Permanent install only:',
																'decorators' => array(
																					new addserviceplan_decorator()
																					)
																 )
															);
		$permanent_install->setRegisterInArrayValidator(false);
		
		
		$install_time        =  new Zend_Form_Element_Text('install_time',
														array(
															'label'      => 'Install required time:',
															'value'      => '',
															'decorators' => array(
																				new addserviceplan_decorator()
														 						 )
														 	)
													  );
		$install_time->setRequired(true);
		
		
		$mrc				=  new Zend_Form_Element_Text('mrc',
														array(
															'label'      => 'MRC:',
															'value'      => '',
															'decorators' => array(
																				new addserviceplan_decorator()
														 						 )
														 	)
													  );
		$mrc->setRequired(true);
		
		$nrc				=  new Zend_Form_Element_Text('nrc',
														array(
																	'label'      => 'NRC:',
																	'value'      => '',
																	'decorators' => array(
																						new addserviceplan_decorator()
																						 )
															 )
														);
		$nrc->setRequired(true);
		
		$mrc_term			=  new Zend_Form_Element_Text('mrc_term',
														array(
																	'label'      => 'MRC Term:',
																	'value'      => '1',
																	'decorators' => array(
																						new addserviceplan_decorator()
																						 )
															  )
														);
		$mrc_term->setRequired(true);
		
		
		$service_plan_group  =  new Zend_Form_Element_Select('service_plan_group',
															array(
																	'label'      => 'Service plan group:',
																	'decorators' => array(
																						new addserviceplan_decorator()
																					 	 )	
																 )
															);
		$service_plan_group->setRegisterInArrayValidator(false);
		$service_plan_group->setRequired(true);
		
		
		$service_plan_eq_type_group		  =  new Zend_Form_Element_Select('service_plan_eq_type_group',
															array(
																	'label'      => 'Service plan type eq group:',
																	'decorators' => array(
																						new addserviceplan_decorator()
																						 )
																 )
															);
		$service_plan_eq_type_group->setRegisterInArrayValidator(false);
		$service_plan_eq_type_group->setRequired(true);
		
		$equipment_type   				 = new  Zend_Form_Element_Select('equipment_type_group',
															array(
																	'label'      => 'Equipment Type Group:',
																	'decorators' => array(
																						new addserviceplan_decorator()
																						 )
																 )
															);
		
		$equipment_type->setRegisterInArrayValidator(false);
		
		
		$service_plan_op_group			 = new  Zend_Form_Element_Select('service_plan_op_group',
															array(
																	'label'      => 'Service plan option group:',
																	'decorators' => array(
																						new addserviceplan_decorator()
																						 )
																 )
															);
		$service_plan_op_group->setRegisterInArrayValidator(false);
		
		
		$service_plan_option			 = new	Zend_Form_Element_Select('service_plan_op',
															array(
																	'label'      => 'Service plan option:',
																	'decorators' => array(
																						new addserviceplan_decorator()
																						 )
																 )
															);
		
		$service_plan_option->setRegisterInArrayValidator(false);
		
		$activate_date		= new Zend_Form_Element_Text('activate_date',
														array(
															'label'      => 'Activate date:',
															'value'      => '',
															'decorators' => array(
																				new addserviceplan_decorator()
														 						 )
														 	)
								);
		
		
		$deactivate_date		= new Zend_Form_Element_Text('deactivate_date',
														array(
															'label'      => 'De-activate date:',
															'value'      => '',
															'decorators' => array(
																				new addserviceplan_decorator()
																				 )
															)
								);
		
		$validator_serviceplan_deactivatedate = new Thelist_Serviceplanvalidator_deactivatedate();
		
	
		$deactivate_date->addValidator($validator_serviceplan_deactivatedate);
		
		
		$add_serviceplan     = new Zend_Form_Element_Submit('add_serviceplan',
														array(
																'label'      =>'',
																'value'		 =>'',
																'decorators' => array(
																					new addserviceplan_decorator()
																					)
															 )
														);
		
		
		
		
		
		
		
		
		/* by non 5/22/2012
		$this->addElements(array($service_plan_name,$service_plan_type,$service_plan_desc,$permanent_install,$install_time,$mrc,$nrc,$mrc_term,$service_plan_group,$service_plan_eq_type_group,$equipment_type,$service_plan_op_group,$service_plan_option,$add_serviceplan));
		*/
		
		
		$this->addElements(array($service_plan_name,$service_plan_type,$service_plan_desc,$permanent_install,$install_time,$mrc,$nrc,$mrc_term,$service_plan_group,$activate_date,$deactivate_date,$add_serviceplan));
		
		
		
		
	}
}



class addserviceplan_decorator extends Zend_Form_Decorator_Abstract
{

	private $database;

	public function __construct($foo=null){


		
		
	}
	
	public function render($content){
		
		$element = $this->getElement();
		$messages = $element->getMessages();
		$name    = htmlentities($element->getFullyQualifiedName());
		$label   = htmlentities($element->getLabel());
		$id      = htmlentities($element->getId());
		$value   = htmlentities($element->getValue());
		
		
		if($name == 'serviceplan_name'){
			
			$format ="<fieldset>
						<legend>Add service plan</legend>
							<table border='0' style='position:relative;left:20px'>
								<tr>
									<td>%s</td>
									<td><input type='text' class='text' style='width:300px;' name='%s' value='%s'> </input>
									</td>
									<td>%s</td>
								</tr>";
			
			return sprintf($format,$label,$name,$value,$element->getView()->formErrors($messages));
		}else if($name=='service_plan_type'){
			
			$format ="			<tr>
									<td>%s</td>
									<td>
									<select name='%s'>
									<option value=''>---Select One---</option>
								";
			
			$serviceplan_type_query = "SELECT item_id,item_name,item_type,item_value
								 	   FROM items 
								 	   WHERE item_type='service_plan_type'
								 	   AND item_active=1";
			$serviceplan_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($serviceplan_type_query);
			$serviceplan_type_options='';
			foreach($serviceplan_types as $serviceplan_type){
				$format.="<option value=".$serviceplan_type['item_id'].">".$serviceplan_type['item_value']."</option>";
			}
			
			
			
			$format .="				</select>
									</td>
									<td>%s</td>
								</tr>";
			
			return sprintf($format,$label,$name,$element->getView()->formErrors($messages));
			
		}else if($name=='serviceplan_desc'){
			$format ="			<tr>
					  				<td>%s</td>
									<td>
									<textarea style='width:400px;resize:none' name='%s'>%s
									</textarea>
									</td>
									<td>%s</td>
								</tr>";
			return sprintf($format,$label,$name,$value,$element->getView()->formErrors($messages));
			
		}else if($name=='service_plan_permamnent_install'){
			
			$format="			<tr>
									<td>%s</td>
									<td>
									<select name='%s'>
										<option value='1'>No</option>
										<option value='2'>Yes</option>
									</select>
									</td>
									<td>
										%s
									</td>
								</tr>";
			return sprintf($format,$label,$name,$element->getView()->formErrors($messages));
		}else if($name=='install_time'){
			
			$format="			<tr>
									<td>%s</td>
									<td><input type='text' class='text' style='width:300px;' name='%s' value='%s'></input></td>
									<td>%s</td>
								</tr>";
			return sprintf($format,$label,$name,$value,$element->getView()->formErrors($messages));
		}else if($name=='mrc'){
			
			$format="			<tr>
									<td>%s</td>
									<td><input type='text' class='text' style='width:300px;' name='%s' value='%s'></input></td>
									<td>%s</td>
								</tr>";

			return sprintf($format,$label,$name,$value,$element->getView()->formErrors($messages));
					
		}else if($name=='nrc'){
			$format="			<tr>
									<td>%s</td>
									<td><input type='text' class='text' style='width:300px;' name='%s' value='%s'></input></td>
									<td>%s</td>
								</tr>";

			return sprintf($format,$label,$name,$value,$element->getView()->formErrors($messages));
		}else if($name=='mrc_term'){
			$format="			<tr>
									<td>%s</td>
									<td><input type='text' class='text' style='width:300px;' name='%s' value='%s' readonly></input></td>
									<td>%s</td>
								</tr>
					";

			return sprintf($format,$label,$name,$value,$element->getView()->formErrors($messages));
		}else if($name =='service_plan_group'){
			$format="
								<tr>
									<td>%s</td>
									<td>
									<select name='%s'>
									<option value=''>---Select One---</option>";
										
			$service_plan_groups =  Zend_Registry::get('database')->get_service_plan_groups()->fetchAll();
			foreach( $service_plan_groups as $service_plan_group){
				$format.="<option value='".$service_plan_group['service_plan_group_id']."'>".$service_plan_group['service_plan_group_name']."</option>";
			}
										
			$format.="				</select>
									</td>
									<td>
										%s
									</td>
								</tr>
					";
			
		
			
			return sprintf($format,$label,$name,$element->getView()->formErrors($messages));
		}else if($name == 'service_plan_eq_type_group'){
			
			$format="
								<tr>
									<td>%s</td>
									<td>
									<select name='%s'>";
			
			$results =  Zend_Registry::get('database')->get_service_plan_eq_type_groups()->fetchAll();
			foreach($results as $result){
				$format.="<option value=".$result['service_plan_eq_type_group_id'].">".$result['service_plan_eq_type_group_name']."</option>";
			}
			
			$format.="				</select>
									</td>
									<td>
										%s
									</td>
								</tr>
								";
				
			
				
			return sprintf($format,$label,$name,$element->getView()->formErrors($messages));
			
		}else if($name == 'equipment_type_group'){
				$format="
								<tr>
									<td>%s</td>
									<td>
									<select name='%s'>";
				
				$equipment_types = Zend_Registry::get('database')->get_equipment_type_groups()->fetchAll();
				foreach($equipment_types as $equipment_type){
					$format.="<option value='".$equipment_type['eq_type_group_id']."'>".$equipment_type['eq_type_group_name']."</option>";
				}
				
							
				$format.="			</select>
									</td>
									<td>
										%s
									</td>
								</tr>
								";
				return sprintf($format,$label,$name,$element->getView()->formErrors($messages));
				
		}else if($name == 'service_plan_op_group'){
			$format="
									<tr>
										<td>%s</td>
										<td>
											<select name='%s'>";
			
			$results =  Zend_Registry::get('database')->get_service_plan_option_groups()->fetchAll();
			foreach($results as $service_plan_op){
				$format.="<option value='".$service_plan_op['service_plan_option_group_id']."'>".$service_plan_op['service_plan_option_group_name']."</option>";
			}
			
			$format.="						</select>
										</td>
										<td>
											%s
										</td>
									</tr>
									";
			return sprintf($format,$label,$name,$element->getView()->formErrors($messages));
		}else if($name == 'service_plan_op'){
			$format="
										<tr>
											<td>%s</td>
											<td>
											<select name='%s'>";
			
			$results = Zend_Registry::get('database')->get_service_plan_options()->fetchAll();
			
			foreach($results as $service_plan_option){
				$format.="<option value='".$service_plan_option['service_plan_option_id']."'>".$service_plan_option['service_plan_option_name']."</option>";
			}	
			
			$format.="						</select>
											</td>
											<td>
											%s
											</td>
										</tr>
												";
			return sprintf($format,$label,$name,$element->getView()->formErrors($messages));
		}else if($name=='activate_date'){
			
			$format="<tr>
						<td>%s</td>
						<td><input type='text' class='text' style='width:120px;' id='%s' name='%s' value='%s'></input></td>
						<td>%s</td>
					 </tr>";
			
			return sprintf($format,$label,$name,$name,$value,$element->getView()->formErrors($messages));
		}else if($name=='deactivate_date'){
			
			$format="<tr>
						<td>%s</td>
						<td><input type='text' class='text' style='width:120px;' id='%s' name='%s' value='%s'></input></td>
						<td>%s</td>
					 </tr>";
			return sprintf($format,$label,$name,$name,$value,$element->getView()->formErrors($messages));
		}else if($name=='add_serviceplan'){
			$format="
								<tr>
									<td colspan='3' align='center'>
										<input type='submit' value='Add'></input>
									</td>
								</tr>
							</table>
					</fieldset>
			";
		
			return sprintf($format,$value);
		}
	}
}	
		
		








		
?>