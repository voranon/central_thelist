<?php
class thelist_projectform_createproject extends Zend_Form{

	public function __construct($options = null)
	{
		parent::__construct($options);

		$project_name   = new Zend_Form_Element_Textarea('project_name',
								array(
									'label'      => 'Project Name:',
									'decorators' => array(
													new createprojectdecorator()
														 )
									 )
														 );
		
		$project_entity = new Zend_Form_Element_Select('project_entity',
								array(
									'label'      => 'Project Entity:',
									'decorators' => array(
													new createprojectdecorator()
														 )
									  )
														 );
		$project_entity->setRegisterInArrayValidator(false);
		$project_entity->setRequired(true);
		
		
		$project_address   = new Zend_Form_Element_Textarea('project_address',
								 array(
								   'label'      => 'Address:',
								   'decorators' => array(
												   new createprojectdecorator()
														)
									  )
														);
		
		$project_mustknow  = new Zend_Form_Element_Textarea('project_mustknow',
								 array(
									'label'      => 'Must know:',
									'decorators' => array(
													new createprojectdecorator()
														  )
									  )
														  );
		
		
		$create     	   = new Zend_Form_Element_Submit('create',
								 array(
									'label'      =>'Create',
									'value'		 =>'D',
									'decorators' => array(
													new createprojectdecorator()
														 )
									  )
														 );
		
		
		
		$this->addElements(array($project_name,$project_entity,$project_address,$project_mustknow,$create));

	}
}


class createprojectdecorator extends Zend_Form_Decorator_Abstract
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
		
		if($name == 'project_name'){
		$format="<fieldset>
					<legend>Create Project</legend>
						<table style='width:505px'>
							<tr>
								<td style='width:100px'>%s</td>
								<td style='width:150px'><input name='%s' id='%s' type='text' class='text'></input></td>
								<td>&nbsp;</td>
							</tr>";
			
			return sprintf($format,$label,$name,$name);
		}else if($name == 'project_entity'){
		$format="			<tr>
								<td>%s</td>
								<td>
								<select name='%s' id='%s' style='width: 145px'>";
		$format.=					"<option value=''>-----Select One----</option>";
			$project_entities = Zend_Registry::get('database')->get_project_entities()->fetchAll();
								foreach($project_entities as $project_entity){
		$format.=					"<option value='".$project_entity['project_entity_id']."'>".$project_entity['project_entity_name']."</option>";
								}
		
		
		$format.="				</select>
								</td>
								<td>%s</td>
							</tr>";
			
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
		}else if($name == 'project_address'){
		$format="			<tr>
								<td colspan='3'>%s</td>
							</tr>
							<tr>
								<td colspan='3'>
								<textarea name='%s' id='%s' rows='4' cols='60' style='resize: none'></textarea>
								</td>
							</tr>";
			
			return sprintf($format,$label,$name,$name);
		}else if($name == 'project_mustknow'){
		$format="			<tr>
								<td colspan='3'>%s</td>
							</tr>
							<tr>
								<td colspan='3'>
								<textarea name='%s' id='%s' rows='4' cols='60' style='resize: none'></textarea>
								</td>
							</tr>";
							
			
			return sprintf($format,$label,$name,$name);
		}else if($name == 'create'){
		$format="			<tr>
								<td colspan='3' align='center'>
								<input name='%s' id='%s' type='submit' class='button' value='Create'></input>
								</td>
							</tr>
						</table>
					</fieldset>";	
		return sprintf($format,$name,$name);
		}
		
	
	}
		
}		



/*
 <fieldset>
			<legend>Create Project</legend>
			<table style='width:505px'>
				<tr>
					<td style='width:100px'>Project:</td>
					<td style='width:150px'><input type='text' class='text'></input></td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>Project Entity</td>
					<td>
						<select style='width: 145px'>
							<option>1</option>
							<option>2</option>
						</select>
					</td>
					<td></td>
				</tr>
				<tr>
					<td colspan='3'>Address</td>
				</tr>
				<tr>
					<td colspan='3'>
						<textarea rows='4' cols='60'></textarea>
					</td>
				</tr>
				<tr>
					<td colspan='3'>Must know</td>
				</tr>
				<tr>
					<td colspan='3'>
						<textarea rows='4' cols='60'></textarea>
					</td>
				</tr>
				<tr>
					<td colspan='3' align='center'>
						<input type='submit' class='button' value='Create'></input>
					</td>
				</tr>
			</table>
		</fieldset>
 */