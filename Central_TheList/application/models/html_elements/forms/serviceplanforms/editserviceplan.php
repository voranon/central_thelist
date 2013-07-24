<?php
class thelist_serviceplanform_editserviceplan extends Zend_Form{
	
	private $database;
	private $time;
	
	public function __construct($options = null,$service_plan_id=null)
	{
		parent::__construct($options);

		$this->time				 = Zend_Registry::get('time');
		$service_plan_id=$service_plan_id;
		
		$service_plan 	= Zend_Registry::get('database')->get_service_plans()->fetchRow('service_plan_id='.$service_plan_id);
		
		$sql = 'SELECT COUNT(*) AS exist
				FROM service_plans
				WHERE NOW() BETWEEN activate AND deactivate
				AND service_plan_id='.$service_plan_id;
		
		$exist = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		
		
		
		
		$service_plan_name   = new Zend_Form_Element_Text('serviceplan_name',
														array(
															'label'      => 'Service Plan Name:',
															'value'      => $service_plan['service_plan_name'],
															'decorators' => array(
																				new editserviceplan_decorator($service_plan_id)
														 						 )
														 	)
													     );
		//$service_plan_name->setRequired(true);
		
		
		
		$service_plan_type   = new Zend_Form_Element_Select('service_plan_type',
														array(
															'label'      => 'Service Plan Type:',
															'value'      => $service_plan['service_plan_type'],
															'decorators' => array(
																				new editserviceplan_decorator($service_plan_id)
																				 )
															 )
														    );
		$service_plan_type->setRegisterInArrayValidator(false);
		
		
		
		$service_plan_desc   = new Zend_Form_Element_Textarea('serviceplan_desc',
														array(
														   'label'      => 'Service Plan Description:',
														   'value'      => $service_plan['service_plan_desc'],
														   'decorators' => array(
																				new editserviceplan_decorator($service_plan_id)
																				)
															 )
															 );
		
		
		
		$permanent_install   =  new Zend_Form_Element_Select('service_plan_permanent_install',
														array(
															'label'      => 'Permanent install only:',
															'value'      =>  $service_plan['service_plan_permanent_install_only'],
															'decorators' => array(
																				new editserviceplan_decorator($service_plan_id)
																				 )
															)
															);
		$permanent_install->setRegisterInArrayValidator(false);
		
		
		$install_time        =  new Zend_Form_Element_Text('install_time',
														array(
															'label'      => 'Install required time:',
															'value'      => $service_plan['service_plan_install_required_time'],
															'decorators' => array(
																				new editserviceplan_decorator($service_plan_id)
																				 )
															)
														    );
		$install_time->setRequired(true);
		
		
		$mrc				=  new Zend_Form_Element_Text('mrc',
														array(
															'label'      => 'MRC:',
															'value'      => $service_plan['service_plan_default_mrc'],
															'decorators' => array(
																				new editserviceplan_decorator($service_plan_id)
																				 )
															 )
														  );
		$mrc->setRequired(true);
		
		$nrc				=  new Zend_Form_Element_Text('nrc',
														array(
															'label'      => 'NRC:',
															'value'      => $service_plan['service_plan_default_nrc'],
															'decorators' => array(
																				new editserviceplan_decorator($service_plan_id)
																				 )
															)
														  );
		$nrc->setRequired(true);
		
		$mrc_term			=  new Zend_Form_Element_Text('mrc_term',
														array(
															'label'      => 'MRC Term:',
															'value'      => $service_plan['service_plan_default_mrc_term'],
															'decorators' => array(
																				new editserviceplan_decorator($service_plan_id)
																				 )
															 )
															);
		$mrc_term->setRequired(true);
		
		
		$activate_date		= new Zend_Form_Element_Text('activate_date',
														array(
															'label'      => 'Activate date:',
															'value'      => $this->time->convert_mysql_datetime_to_datepicker($service_plan['activate']),
															'decorators' => array(
																				new editserviceplan_decorator($service_plan_id)
																				 )
															 )
														);
		//$activate_date->setRequired(true);
		
		$deactivate_date	= new Zend_Form_Element_Text('deactivate_date',
														array(
															'label'      => 'De-activate date:',
															'value'      => $this->time->convert_mysql_datetime_to_datepicker($service_plan['deactivate']),
															'decorators' => array(
																				 new editserviceplan_decorator($service_plan_id)
																				   )
															)
														);
		//$deactivate_date->setRequired(true);
		$validator_serviceplan_deactivatedate = new Thelist_Serviceplanvalidator_deactivatedate();
		$deactivate_date->addValidator($validator_serviceplan_deactivatedate);
		
		
		$edit_serviceplan     = new Zend_Form_Element_Submit('edit_serviceplan',
															array(
																'label'      =>'',
																'value'		 =>'Edit',
																'decorators' => array(
																					new editserviceplan_decorator($service_plan_id)
																					 )
															 )
														   );
		
		
																						   
		$this->addElements(array( $service_plan_name,$service_plan_type,$service_plan_desc,$permanent_install,$install_time,$mrc,$nrc,$mrc_term,$activate_date,$deactivate_date,$edit_serviceplan));
		
	}
	
}



class editserviceplan_decorator extends Zend_Form_Decorator_Abstract
{

	private $database;
	private $exist;
	private $readonly='';
	private $deactivate_readonly='';
	public function __construct($service_plan_id=null){


		$sql="SELECT activate,deactivate,service_plan_name
			  FROM service_plans
			  WHERE service_plan_id=".$service_plan_id;
		
		$service_plan = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		
		
		
		$sql = 'SELECT COUNT(*) AS exist
				FROM service_plans
				WHERE deactivate < NOW()
				AND service_plan_id='.$service_plan_id;
		
		$this->deactivation_past = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		
		$sql = 'SELECT COUNT(*) AS exist
				FROM service_plans
				WHERE activate < NOW()
				AND service_plan_id='.$service_plan_id;
		
		$this->activation_past = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		
		if( $service_plan ['activate'] != '0000-00-00 00:00:00' ){
			if($this->activation_past){
				$this->readonly ='readonly';
			}
		}
		
		if( $service_plan ['deactivate'] != '0000-00-00 00:00:00' ){
			
			if($this->deactivation_past){
				$this->deactivate_readonly ='readonly';
			}
		}
		
		
		
		
		
			
	}
	
	public function render($content){
	
		$element = $this->getElement();
		$messages = $element->getMessages();
		$name    = htmlentities($element->getFullyQualifiedName());
		$label   = htmlentities($element->getLabel());
		$id      = htmlentities($element->getId());
		$value   = htmlentities($element->getValue());
	  
		if($name == 'serviceplan_name')
		{
		
			$format ="<fieldset>
						<legend>Edit service plan</legend>
							<table border='0' style='position:relative;left:20px'>
								<tr>
									<td>%s</td>
									<td><input type='text' class='text' style='width:300px;' name='%s' value='%s' %s></input>
									</td>
									<td>%s</td>
								</tr>";
			
			return sprintf($format,$label,$name,$value,$this->readonly,$element->getView()->formErrors($messages));
			
		}else if($name=='service_plan_type'){
		
				$format ="			<tr>
													<td>%s</td>
													<td>
													<select name='%s'>";
				
				
				
			$serviceplan_type_query = "SELECT item_id,item_name,item_type,item_value
								 	   FROM items 
								 	   WHERE item_type='service_plan_type'
								 	   AND item_active=1";
			$serviceplan_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($serviceplan_type_query);
			$serviceplan_type_options='';
			
			foreach($serviceplan_types as $serviceplan_type){
				if($this->readonly=='readonly')
				{
					if($serviceplan_type['item_id'] == $value){
						$format.="<option value=".$serviceplan_type['item_id']." selected>".$serviceplan_type['item_value']."</option>";
					}	
				}else{
					if($serviceplan_type['item_id'] == $value){
						$format.="<option value=".$serviceplan_type['item_id']." selected>".$serviceplan_type['item_value']."</option>";
					}else{
						$format.="<option value=".$serviceplan_type['item_id'].">".$serviceplan_type['item_value']."</option>";
					}
				}
				
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
									<textarea style='width:400px;resize:none' name='%s' %s>%s
									</textarea>
									</td>
									<td>%s</td>
								</tr>";
			return sprintf($format,$label,$name,$this->readonly,$value,$element->getView()->formErrors($messages));
			
		}else if($name=='service_plan_permanent_install'){
			
			$format="			<tr>
									<td>%s</td>
									<td>
										<select name='%s' >";
			
									if($this->readonly=='readonly'){
										
										if($value==1){
											$format.="<option value='1'>No</option>";
										}else if($value==2){
											$format.="<option value='2'>Yes</option>";
										}
																	
									}else{
										$format.="<option value='1' ".($value==1?'selected':'').">No</option>
												  <option value='2' ".($value==2?'selected':'').">Yes</option>";
									}					
			$format.="
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
									<td><input type='text' class='text' style='width:300px;' name='%s' value='%s' %s></input></td>
									<td>%s</td>
								</tr>";
			return sprintf($format,$label,$name,$value,$this->readonly,$element->getView()->formErrors($messages));
		}else if($name=='mrc'){
			
			$format="			<tr>
									<td>%s</td>
									<td><input type='text' class='text' style='width:300px;' name='%s' value='%s' %s></input></td>
									<td>%s</td>
								</tr>";

			return sprintf($format,$label,$name,$value,$this->readonly,$element->getView()->formErrors($messages));
					
		}else if($name=='nrc'){
			
			$format="			<tr>
									<td>%s</td>
									<td><input type='text' class='text' style='width:300px;' name='%s' value='%s' %s></input></td>
									<td>%s</td>
								</tr>";

			return sprintf($format,$label,$name,$value,$this->readonly,$element->getView()->formErrors($messages));
					
		}else if($name=='mrc_term'){
			
			$format="			<tr>
									<td>%s</td>
									<td><input type='text' class='text' style='width:300px;' name='%s' value='%s' %s></input></td>
									<td>%s</td>
								</tr>";

			return sprintf($format,$label,$name,$value,$this->readonly,$element->getView()->formErrors($messages));
					
		}else if($name=='activate_date'){
			
			if($this->readonly=='readonly'){
				$id='';
			}else{
				$id=$name;
			}
			
			$format="<tr>
						<td>%s</td>
						<td><input type='text' class='text' style='width:120px;' id='%s' name='%s' value='%s' %s></input></td>
						<td>%s</td>
					 </tr>";
			
			return sprintf($format,$label,$id,$name,$value,$this->readonly,$element->getView()->formErrors($messages));
			
			
			
			
		}else if($name=='deactivate_date'){
						
		
			
			if( $this->deactivate_readonly == 'readonly' ){
				$id='';
			}else{
				$id=$name;
			}
			
		
			
			$format="<tr>
						<td>%s</td>
						<td><input type='text' class='text' style='width:120px;' id='%s' name='%s' value='%s' %s></input></td>
						<td>%s</td>
					 </tr>";
			return sprintf($format,$label,$id,$name,$value,$this->readonly,$element->getView()->formErrors($messages));
			
		}else if($name=='edit_serviceplan'){
		
			
			if($this->readonly=='readonly' && $this->deactivate_readonly == 'readonly'){
				$format="
												<tr>
													<td colspan='3' align='center'>
														<input type='submit' value='Edit' disabled></input>
													</td>
												</tr>
											</table>
									</fieldset>
							";
			}else{
				$format="
												<tr>
													<td colspan='3' align='center'>
														<input type='submit' value='Edit'></input>
													</td>
												</tr>
											</table>
									</fieldset>
							";
			}
			
		
		
			return sprintf($format,$value);
		}
		
	}
	
}	


?>