<?php

class thelist_buildingform_addbuilding extends Zend_Form{
	
	public function __construct($options = null,$project_id=null)
	{
		parent::__construct($options);
		
		
		$project 	  = new Zend_Form_Element_Select('project',
						array(
							'label'      => 'Project:',
							'value'		 => $project_id,
							'decorators' => array(
												new addbuilding_decorator()
												 )
							 )
												);
		$project->setRegisterInArrayValidator(false);
		$project->setRequired(true);
		
		
		$existbuilding = new Zend_Form_Element_Select('existbuilding',
						array(
							'label'      => 'Select from existing building:',
							'decorators' => array(
												new addbuilding_decorator()
												 )
							  )
												);
		$existbuilding->setRegisterInArrayValidator(false);

		
		
		$building_name   = new Zend_Form_Element_Textarea('building_name',
						array(
							'label'      => 'Building Name:',
							'decorators' => array(
												new addbuilding_decorator()
												 )
							 )
											);
		$building_name->setRequired(true);
		
		
		
		$building_alias   = new Zend_Form_Element_Textarea('building_alias',
							array(
							'label'      => 'Building Alias:',
							'decorators' => array(
												new addbuilding_decorator()
												)
							 	)
							 );
							 
		$building_type    = new Zend_Form_Element_Select('building_type',
														array(
														'label'      => 'Building Type:',
														'decorators' => array(
															new addbuilding_decorator()
													 						  )
							 			 					)
							);				
		$building_type->setRegisterInArrayValidator(false);
													
		$add_building     = new Zend_Form_Element_Submit('add_building',
						 	array(
							'label'      =>'',
							'value'		 =>'Add',
							'decorators' => array(
												new addbuilding_decorator()
												)
							)
														);
		
		
		//$this->addElements(array($project,$existbuilding,$building_name,$building_alias,$add_building));
		$this->addElements(array($project,$building_name,$building_alias,$building_type,$add_building));
		
	}
}

class addbuilding_decorator extends Zend_Form_Decorator_Abstract
{

	private $database;

	public function __construct($project_id=null){
		

	}
	
	
	public function render($content){
		$element = $this->getElement();
		$messages = $element->getMessages();
		$name    = htmlentities($element->getFullyQualifiedName());
		$label   = htmlentities($element->getLabel());
		$id      = htmlentities($element->getId());
		$value   = htmlentities($element->getValue());
		
		
		
		if($name == 'project'){
		$format="<fieldset>
					<legend>Buildings:</legend>
					<table style='width:800px'>
					<tr>
						<td style='width:170px'>%s</td>
						<td style='width:250px'>
						<select  name='%s' id='%s' style='width:319px'>";
		
			$projects = Zend_Registry::get('database')->get_projects()->fetchAll();
		$format.="<option value='' selected>----------------------Select One----------------------</option>";
			foreach($projects as $project){
				if($value == $project['project_id']){
		$format.=		"<option value='".$project['project_id']."' selected>".$project['project_id']." ".$project['project_name']."</option>";
				}else{
		$format.=		"<option value='".$project['project_id']."'>".$project['project_id']." ".$project['project_name']."</option>";
				}
		
			}
		
		
		$format.="</select>
						</td>
						<td style='width:290px'>%s</td>
					</tr>";
		return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
		}else if($name == 'existbuilding'){
		$format="	<tr>
						<td style='width:170px'>%s</td>
						<td style='width:250px'>
						<select name='%s' id='%s' style='width:319px'>";
		$format.=			"<option value=''>----------------------Select One----------------------</option>";
		$buildings = Zend_Registry::get('database')->get_buildings()->fetchAll('project_id=0');
					foreach($buildings as $building){
		$format.=           "<option value='".$building['building_id']."'>".$building['building_id']." ".$building['building_name']."</option>";
					}
		$format.="		</select>
						</td>
						<td style='width:290px'>%s</td>
					</tr>";	
		return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
		}else if($name == 'building_name'){
		$format="	<tr>
						<td>%s</td>
						<td>
						<input name='%s' id='%s' type='text' class='text' style='width:320px'></input>
						</td>
						<td style='width:290px'>%s</td>
					</tr>";	
		return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
		}
		else if($name == 'building_alias'){
		$format="	<tr>
						<td>%s</td>
						<td>
						<input name='%s' id='%s' type='text' class='text' style='width:320px'></input>
						</td>
						<td style='width:290px'></td>
					</tr>";		
		return sprintf($format,$label,$name,$name);
		}else if($name == 'building_type'){
		$format="<tr>
						<td style='width:170px'>%s</td>
						<td style='width:250px'>
						<select  name='%s' id='%s' style='width:319px'>";
		
		$building_types = Zend_Registry::get('database')->get_items()->fetchAll("item_type='building_type'");
		
		foreach($building_types as $building_type){
			$format.="<option value='".$building_type['item_id']."'>".$building_type['item_value']."</option>";
		}
		
		$format.="		</select>
						</td>
						<td style='width:290px'></td>
				 </tr>";
		return sprintf($format,$label,$name,$name);
		}else if($name == 'add_building'){
		$format="	<tr>
						<td></td>
				 		<td align='center'>
				 		<input name='%s' id='%s' type='submit' class='button' value='Add'></input>
						</td>
						<td></td>
					</tr>
					</table>
				</fieldset>";	
		return sprintf($format,$name,$name);
		}
		
		
		
		
		
		
	}

	
	
}	


