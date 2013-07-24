<?php
class thelist_model_project_entities{

	private $database;
	
	private $project_entity_id;
	private $project_entity_name;
	private $note;
	private $createdate;
	
	public function __construct($project_entity_id){
		
		$this->project_entity_id=$project_entity_id;


		$project_entity=Zend_Registry::get('database')->get_project_entities()->fetchRow('project_entity_id='.$this->project_entity_id);
		
		$this->project_entity_name		= $project_entity['project_entity_name'];
		$this->note						= $project_entity['note'];
		$this->createdate				= $project_entity['createdate'];
	
	}
	
	
	public function get_project_entity_id(){
		return $this->project_entity_id;
	}
	
	public function get_project_entity_name(){
		return $this->project_entity_name;
	}
	public function get_note(){
		return $this->note;
	}
	public function get_createdate(){
		return $this->createdate;
	}
	
	
	
	
	
	public function set_project_entity_name($project_entity_name){
		$data	=array(
						'project_entity_name'	=>  $project_entity_name
						);
		Zend_Registry::get('database')->get_project_entities()->update($data,'project_entity_id='.$this->project_entity_id);
		$this->project_entity_name=$project_entity_name;
	}
	public function set_note($note){
		$data	=array(
						'note'	=>  $note
		);
		Zend_Registry::get('database')->get_project_entities()->update($data,'project_entity_id='.$this->project_entity_id);
		$this->note=$note;
	}
	
	
	
	
	
	
}
?>