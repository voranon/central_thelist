<?php
class thelist_entityform_addentity_to_project extends Zend_Form{
	
	public function __construct($options = null,$project_id=null)
	{
		parent::__construct($options);
		
		

		
		$existentity = new Zend_Form_Element_Select('existentity',
						array(
							'label'      => 'Select from existing entity:',
							'decorators' => array(
												new addbuilding_toprojectdecorator($project_id)
												 )
							  )
												);
		$existentity->setRegisterInArrayValidator(false);
		$existentity->setRequired(true);
		
		
		$entity_note   = new Zend_Form_Element_Textarea('entity_note',
						array(
							'label'      => 'Note:',
							'decorators' => array(
												new addbuilding_toprojectdecorator()
												 )
							 )
						);
		
		
		
				
		$add_entity     = new Zend_Form_Element_Submit('add_entity',
						 	array(
							'label'      =>'',
							'value'		 =>'Add',
							'decorators' => array(
												new addbuilding_toprojectdecorator()
												)
								)
							);
		
		
		//$this->addElements(array($existentity,$entity_note,$add_entity));
		$this->addElements(array($existentity,$add_entity));
		
	}
}

class addbuilding_toprojectdecorator extends Zend_Form_Decorator_Abstract
{

	private $database;
	private $project_id;

	public function __construct($project_id=null){
		$this->project_id = $project_id;

	}
	
	
	public function render($content){
		$element = $this->getElement();
		$messages = $element->getMessages();
		$name    = htmlentities($element->getFullyQualifiedName());
		$label   = htmlentities($element->getLabel());
		$id      = htmlentities($element->getId());
		$value   = htmlentities($element->getValue());
		
		
		
		if($name == 'existentity'){
			
		$format="
		<fieldset>
			<legend>Entity:</legend>
				<table width='590px' border='0'>
				<tr>
					<td style='width:160px'>%s</td>
					<td style='width:155px'>
						<select name='%s' id='%s' style='width:140px'>
						<option value=''>-----Select One-----</option>";
		
		
		$sql="SELECT pe.project_entity_id,pe.project_entity_name
			  FROM project_entities pe
			  LEFT OUTER JOIN (SELECT * 
		 					   FROM project_entity_mapping 
		 					   WHERE project_id=2)pem 
		 	  ON pe.project_entity_id = pem.project_entity_id
			  WHERE pem.project_entity_id IS NULL 
			 ";
		$entities = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		foreach($entities as $entity){
			$format.="<option value='".$entity['project_entity_id']."'>".$entity['project_entity_id'].' '.$entity['project_entity_name']."</option>";
		}

							
		$format.="		</select>
					</td>
					<td>%s</td>
				</tr>";
		return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
		}else if($name == 'entity_note'){
		$format="<tr>
				<td>%s</td>
				<td colspan='2'>
					<textarea name='%s' id='%s' rows='5' cols='48'></textarea>
				</td>
				</tr>";
		
		
		return sprintf($format,$label,$name,$name);
		}else if($name == 'add_entity'){
		$format="<tr>
					<td></td>
					<td align='center'>
						<input type='submit' class='button' style='width:80px' value='Add'></input>
					</td>
					<td></td>
				</tr>
			</table>
		</fieldset>";
		
		
		return sprintf($format,$name,$name);
		}
	}

	
	
}	

/*
<fieldset>
<legend>Entity:</legend>
		
		<table width='590px' border='0'>
			<tr>
				<td style='width:160px'>Entity Name:</td>
				<td style='width:155px'>
					
						<select style='width:140px'>
							<option>1</option>
							<option>2</option>
						</select>
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>Note:</td>
				<td colspan='2'>
					<textarea rows="5" cols="48"></textarea>
				</td>
			</tr>
			<tr>
				<td></td>
				<td align='center'>
					<input type='button' class='button' style='width:80px' value='Add'></input>
				</td>
				<td></td>
			</tr>
		</table>
</fieldset>
*/
