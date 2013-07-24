<?php
class thelist_entityform_editentity_to_project extends Zend_Form{
	private $project_entity_id;
	private $database;
	public function __construct($options = null,$project_entity_id=null)
	{
		parent::__construct($options);
		
		$this->project_entity_id = $project_entity_id;

	
		$project_entity = Zend_Registry::get('database')->get_project_entities()->fetchRow('project_entity_id='.$this->project_entity_id);
		
		$entity_name   = new Zend_Form_Element_Textarea('entity_name',
				array(
					'label'      => 'Entity Name:',
					'value'		 => $project_entity['project_entity_name'],
					'decorators' => array(
										new editbuilding_toprojectdecorator()
										 )
					)
							);
		
		
				
		$delete_entity     = new Zend_Form_Element_Submit('delete_entity',
				array(
					'label'      =>'',
					'value'		 =>'Delete',
					'decorators' => array(
										new editbuilding_toprojectdecorator()
										)
					)
							);
		
		$this->addElements(array($entity_name,$delete_entity));
		
	}
}

class editbuilding_toprojectdecorator extends Zend_Form_Decorator_Abstract
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
		
		
		
		if($name == 'entity_name'){
			
		$format="<fieldset>
				<legend>Entity:</legend>
					<table>
					<tr>
						<td>%s</td>
						<td><input type='text' name='%s' id='%s' value='%s' class='text' readonly></input></td>
						<td></td>
					</tr>";
		
		return sprintf($format,$label,$name,$name,$value);
		}else if($name == 'delete_entity'){
		$format="	<tr>
						<td></td>
						<td><input type='submit' name='%s' id='%s' value='Delete' class='button'></input></td>
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
			<table>
				<tr>
					<td>Entity Name:</td>
					<td><input class='text'></input></td>
					<td></td>
				</tr>
				<tr>
					<td></td>
					<td><input type='submit' value='Delete' class='button'></input></td>
					<td></td>
				</tr>
			</table>
</fieldset>
*/
