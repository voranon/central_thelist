<?php

require_once 'Zend/Log.php';

class thelist_html_element_mainmenu{
	
	private $database;
	private $perspective=0;
	private $htmlmainmenu='';
	
	function __construct(){
		

	}
	
	private function createmenu($perspective){
		$this->perspective	= $perspective;
		$select=Zend_Registry::get('database')->get_menus()->select()->where("role_id=".$this->perspective);
		
		$rows=Zend_Registry::get('database')->get_menus()->fetchAll($select);
		
		$this->htmlmainmenu.="<table><tr>";
		
		foreach($rows as $value){
		
			$this->htmlmainmenu.="<td>".$value['menu_name']."</td>";
			$this->htmlmainmenu.="<td>";
		
			$this->htmlmainmenu.="<select id='menu' name='menu' menu_id='".$value['menu_id']."'>";
		
			$sql = 	"SELECT p.page_id,page_name,controller,action
				 	FROM menuitems m
				 	LEFT OUTER JOIN htmlpages p ON m.page_id=p.page_id
				 	WHERE menu_id=".$value['menu_id']
					;

			$menuitems=Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
			$this->htmlmainmenu.="<option value='0'>----Select One----</option>";
			foreach($menuitems as $memuitem)
			{
				$this->htmlmainmenu.="<option value='".$memuitem['page_id'].'*'.$memuitem['controller'].'*'.$memuitem['action']."'>".$memuitem['page_name']."</option>";
			}
		
			$this->htmlmainmenu.="</select>";
			$this->htmlmainmenu.="</td>";
		}

		$this->htmlmainmenu.="</tr></table>";
	}
	
	
	public function get_htmlmainmenu(){
		return 	$this->htmlmainmenu;
	}
	public function set_htmlmainmenu($perspective){
		$this->htmlmainmenu='';
		$this->createmenu($perspective);
	}
}
?>