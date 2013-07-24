<?php
class thelist_utility_layoutmanager{
	
	private $perspective;
	private $helper;
	
	private $_user_session;
	
	function __construct($perspective,$helper)
	{
		$this->_user_session 	= new Zend_Session_Namespace('userinfo');
		$this->perspective = $perspective;
		$this->helper      = $helper;
	}
	
	public function set_layout(){
		
		if($this->perspective==1){
			$this->helper->layout->setLayout('residentialsaleperspective_layout');
		}else if($this->perspective==2){
			$this->helper->layout->setLayout('bussinesssaleperspective_layout');
		}else if($this->perspective==3){
			$this->helper->layout->setLayout('supportperspective_layout');
		}else if($this->perspective==4){
			$this->helper->layout->setLayout('engineerperspective_layout');
		}else if($this->perspective==5){
			$this->helper->layout->setLayout('engineerperspective_layout');
		}else if($this->perspective==6){
			$this->helper->layout->setLayout('executiveofficerperspective_layout');
		}else if($this->perspective==7){
			$this->helper->layout->setLayout('engineerperspective_layout');
		}else if($this->perspective==8){
			$this->helper->layout->setLayout('engineerperspective_layout');
		}else if($this->perspective==9){
			$this->helper->layout->setLayout('purchasingperspective_layout');
		}else{
			$this->helper->layout->setLayout('engineerperspective_layout');
		}
	}
}
?>