<?php
require_once 'Zend/Log.php';

class thelist_html_element_perspectivemenu{
	
	private $database;
	private $perspective=0;
	private $htmlperspectivemenu='';
	
	function __construct(){

	}
	
	private function createmenu($perspective){
		$this->perspective	= $perspective;
		$select=Zend_Registry::get('database')->get_acl_roles()->select()->where("role_default=1");
		$rows=Zend_Registry::get('database')->get_acl_roles()->fetchAll($select);
		
		$this->htmlperspectivemenu.="<select name='mainperspective' id='mainperspective'>";
		
		foreach($rows as $value)
		{
			
			if($value['role_id']==$this->perspective){
				$this->htmlperspectivemenu.="<option value='".$value['role_id']."' selected>".$value['role_name']."</option>";
			}else{
				$this->htmlperspectivemenu.="<option value='".$value['role_id']."'>".$value['role_name']."</option>";
			}
		}
		
		$this->htmlperspectivemenu.="</select>";
		
	}
	
	
	public function get_htmlperspectivemenu(){
		return $this->htmlperspectivemenu;
	}
	public function set_htmlperspectivemenu($perspective){
		
		$this->htmlperspectivemenu='';
		$this->createmenu($perspective);
	}
}
?>