<?php
class thelist_buildingform_editbuilding extends Zend_Form{
	private $database;
	
	public function __construct($options = null,$project_id=null,$building_id=null)
	{
		parent::__construct($options);
		

		
		$building = Zend_Registry::get('database')->get_buildings()->fetchRow('building_id='.$building_id);
		
		
		
		
		$project 	  = new Zend_Form_Element_Select('project',
						array(
							'label'      => 'Project:',
							'value'		 => $project_id,
							'decorators' => array(
												new editbuilding_decorator()
												 )
							 )
												);
		$project->setRegisterInArrayValidator(false);
		
		
		/*
		$existbuilding = new Zend_Form_Element_Select('existbuilding',
						array(
							'label'      => 'Select from existing building:',
							'decorators' => array(
												new editbuilding_decorator()
												 )
							  )
												);
		$existbuilding->setRegisterInArrayValidator(false);
		*/
		
		
		$building_name   = new Zend_Form_Element_Textarea('building_name',
						array(
							'label'      => 'Building Name:',
							'value'      => $building['building_name'],
							'decorators' => array(
												new editbuilding_decorator()
												 )
							 )
											);
		$building_name->setRequired(true);
		
		
		
		$building_alias   = new Zend_Form_Element_Textarea('building_alias',
							array(
							'label'      => 'Building Alias:',
							'value'      => $building['building_alias'],
							'decorators' => array(
												new editbuilding_decorator()
												)
							 )
														);
		
		$save_building     = new Zend_Form_Element_Submit('save_building',
						 	array(
							'label'      =>'',
							'value'		 =>'Save',
							'decorators' => array(
												new editbuilding_decorator()
												)
								)
														);
		
		$delete_building     = new Zend_Form_Element_Submit('delete_building',
							array(
							'label'      =>'',
							'value'		 =>'Delete',
							'decorators' => array(
												new editbuilding_decorator()
												)
								  )
		);
		
		//$this->addElements(array($project,$existbuilding,$building_name,$building_alias,$add_building));
		$this->addElements(array($project,$building_name,$building_alias,$save_building,$delete_building));
		
	}
}

class editbuilding_decorator extends Zend_Form_Decorator_Abstract
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
						<select  name='%s' id='%s' >";
		
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
						<td style='width:290px'>&nbsp;</td>
					</tr>";
		return sprintf($format,$label,$name,$name);
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
						<input name='%s' id='%s' value='%s' type='text' class='text' style='width:320px'></input>
						</td>
						<td style='width:290px'>%s</td>
					</tr>";	
		return sprintf($format,$label,$name,$name,$value,$element->getView()->formErrors($messages));
		}
		else if($name == 'building_alias'){
		$format="	<tr>
						<td>%s</td>
						<td>
						<input name='%s' id='%s' value='%s' type='text' class='text' style='width:320px'></input>
						</td>
						<td style='width:290px'></td>
					</tr>";		
		return sprintf($format,$label,$name,$name,$value);
		}else if($name == 'save_building'){
		$format="	<tr>
						<td></td>
				 		<td align='center'>
				 		<input name='%s' id='%s' type='submit' class='button' value='Save'></input>
						</td>
						<td></td>
					</tr>";
		return sprintf($format,$name,$name);
		}else if($name == 'delete_building'){
		$format="	<tr>
						<td></td>
				 		<td align='center'>
				 		<input name='%s' id='%s' type='submit' class='button' value='Delete'></input>
						</td>
						<td></td>
					</tr>
					</table>
				</fieldset>";	
		return sprintf($format,$name,$name);
		}
		
		
		
		
		
		
		
	}

	
	
}	
