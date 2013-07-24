<?php


class searchboxform extends Zend_Form{
	private $database;
	private $searchbox_id;
	
	public function __construct($searchbox_id)
	{
		parent::__construct($searchbox_id);
		

		$this->searchbox_id=$searchbox_id;
		
		$query="SELECT searchbox_id,searchbox_name
				FROM searchboxes
				WHERE searchbox_id=".$this->searchbox_id;
		
		$searchboxes = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($query);
		
	    $searchbox_name = $searchboxes['searchbox_name'];
			
		
	    $this->setAttrib('id','searchboxform');
	    $this->setName('searchboxform');
		$this->setAction('/search/search');
		$this->setMethod('post');
		
		
		$searchbox = new Zend_Form_Element_Text('searchbox',
												array(
													'decorators' => array(
																			new searchbox_decorator($searchbox_name)
													 					  ),
													 )
												);
		
		$pop_up    = new Zend_Form_Element_Checkbox('searchbox_popup',
												array(	
													
													'decorators' => array(
																			new searchbox_decorator()
																		 ),
													 )
												);
		
		
		$searchbox_id = new Zend_Form_Element_Hidden('searchbox_id',
													array(
														'value'     => $this->searchbox_id,
														'decorators' =>array(
																			 new searchbox_decorator()	
																			),
														
														)
												 	);
		
	
		$this->addElements(array($searchbox,$pop_up,$searchbox_id));
		
	}
}


class searchbox_decorator extends Zend_Form_Decorator_Abstract
{
	
	
	private $placeholder;

	public function __construct($placeholder=null)
	{
		$this->placeholder = $placeholder;
	}

	public function render($content)
	{
		

		$element = $this->getElement();
		$messages = $element->getMessages();
		$name    = htmlentities($element->getFullyQualifiedName());
		$label   = htmlentities($element->getLabel());
		$id      = htmlentities($element->getId());
		$value   = htmlentities($element->getValue());
		
		
		if($name =='searchbox'){
			$format="<input type='text' class='searchbox' name='%s' id='%s' maxlength='20' placeholder='%s'></input>";
			return sprintf($format,$name,$name,$this->placeholder);
		}else if($name =='searchbox_popup'){
			$format="<input type='checkbox' name='%s' id='%s'></input>";
			return sprintf($format,$name,$name);
		}else if($name == 'searchbox_id'){
			$format="<input type='hidden' name='searchbox_id' id='searchbox_id' value='%s'></input>";
			return sprintf($format,$value);
		}

	}
}

?>