<?php

//require_once APPLICATION_PATH.'/models/html_elements/forms/contactforms/decorators/contactformdecorators.php';

class thelist_contactform_addcontact extends Zend_Form{
	
	
	
	public function __construct($options = null,$mode=null)
	{
		
		parent::__construct($options);
		
		 
		
		// existing contact
		$existcontact = new Zend_Form_Element_Select('contact',
												array(
												'label'      => 'Select from existing contacts:',
												'decorators' => array(
																	new contactexist_decorator(),
																	new contactheadtable_decorator()
		   					 										 ),
												)
											 );
		$existcontact->setRegisterInArrayValidator(false);
		
		
		
		// contact title
		
		$title = new Zend_Form_Element_Select('title',
												array(
												'label'      => 'Title:',
												'decorators' => array(
																	new contacttitle_decorator(),
																	 ),
												)
											 );
		$title->setRegisterInArrayValidator(false);
		
		// first name
		$firstname  = new Zend_Form_Element_Text('firstname',
												array(
		
													'label'      => 'First Name:',
		   						 					'decorators' => array(new contacttext_decorator()),
												)
										);
		
		
		
		// last name
		$lastname  = new Zend_Form_Element_Text('lastname',
												array(
		
															'label'      => 'Last Name:',
				   						 					'decorators' => array(new contacttext_decorator()),
												)
										);
				
		// streetnumber
		$streetnumber  = new Zend_Form_Element_Text('streetnumber',
												array(
		
																	'label'      => 'Street Number:',
						   						 					'decorators' => array(new contacttext_decorator()),
												)
		);
				
		// street name
		$streetname  = new Zend_Form_Element_Text('streetname',
												array(
		
																			'label'      => 'Street Name:',
								   						 					'decorators' => array(new contacttext_decorator()),
												)
		);
				
		// street type
		$streettype  = new Zend_Form_Element_Text('streettype',
		array(
		
																					'label'      => 'Street Type:',
										   						 					'decorators' => array(new contacttext_decorator()),
		)
		);
		
		
		// city
		$city  = new Zend_Form_Element_Text('city',
											array(
		
																					'label'      => 'City:',
										   						 					'decorators' => array(new contacttext_decorator()),
											)
		);
		
		
		// state
		
		$state = new Zend_Form_Element_Select('state',
											  array(
																					'label'      => 'State:',
																					'decorators' => array(new contactstate_decorator())
												)
		);
		$state->setRegisterInArrayValidator(false);
				
		// zip
		$zip  = new Zend_Form_Element_Text('zip',
		array(
		
																							'label'      => 'Zip:',
												   						 					'decorators' => array(new contacttext_decorator()),
		)
		);
				
		// cellphone
		$cellphone  = new Zend_Form_Element_Text('cellphone',
		array(
		
																									'label'      =>'Cell Phone Number:',
														   						 					'decorators' => array(new contacttext_decorator()),
		)
		);
				
		// homephone
		$homephone  = new Zend_Form_Element_Text('homephone',
		array(
		
																											'label'      =>'Home Phone Number:',
																   						 					'decorators' => array(new contacttext_decorator()),
		)
		);
		// officephone
		$officephone  = new Zend_Form_Element_Text('officephone',
		array(
		
																															'label'      =>'Office Phone Number:',
																				   						 					'decorators' => array(new contacttext_decorator()),
		)
		);
		
		
		// fax
		$fax  = new Zend_Form_Element_Text('fax',
		array(
		
																													'label'      =>'Fax:',
																		   						 					'decorators' => array(new contacttext_decorator()),
		)
		);
		
		//email
		$email  = new Zend_Form_Element_Text('email',
		array(
		
																															'label'      =>'Email:',
																				   						 					'decorators' => array(new contacttext_decorator()),
		)
		);
		
		
		$add_contact = new Zend_Form_Element_Submit('add_contact',array(
																'label'      =>'',
																'value'		 =>'Add',
					   						 					'decorators' => array(new contactsubmit_decorator(),
					   						 										  new contacttailtable_decorator()),
															  )
															);
		
		
		
		
		if($mode=='project'){
			//$existcontact->setRequired(true)->addValidator('NotEmpty', true);
			//$title->setRequired(true)->addValidator('NotEmpty', true);
			//$firstname->setRequired(true);
			//$firstname->addValidator(new Zend_Validate_Alpha());
			//$firstname->addValidator('NotEmpty');
			//$lastname->setRequired(true);
			//$lastname->addValidator('NotEmpty');
			//$streetnumber->setRequired(true);
			//$streetnumber->addValidator('NotEmpty');
			//$streetname->setRequired(true);
			//$streetname->addValidator('NotEmpty');
			//$streettype->setRequired(true);
			//$streettype->addValidator('NotEmpty');
			//$city->setRequired(true);
			//$city->addValidator('NotEmpty');
			//$state->setRequired(true);
			//$state->addValidator('NotEmpty', true);
			//$zip->setRequired(true);
			//$zip->addValidator('NotEmpty');
			//$cellphone->setRequired(true);
			//$cellphone->addValidator('NotEmpty');
			//$homephone->setRequired(true);
			//$homephone->addValidator('NotEmpty');
			//$fax->setRequired(true);
			//$fax->addValidator('NotEmpty');
			//$email->setRequired(true);
			//$email->addValidator('NotEmpty');
		}
		
		$this->addElements(array($existcontact,$title,$firstname,$lastname,$streetnumber,$streetname,$streettype,$city,$state,$zip,$cellphone,$homephone,$officephone,$fax,$email,$add_contact));
		
		
	}
	
	
}



class contactexist_decorator extends Zend_Form_Decorator_Abstract
{

	private $database;
	protected $_format = "<tr>
							<td align='right' style='width:200px'>%s</td>
							<td style='width:210px'>
								<select name='%s' id='%s' style='width:205px'>
									%s
								</select>
							</td>
							<td>%s
							</td>
						  </tr>";
	public function render($content)
	{

		$element = $this->getElement();
		$messages = $element->getMessages();
		$name    = htmlentities($element->getFullyQualifiedName());
		$label   = htmlentities($element->getLabel());
		$id      = htmlentities($element->getId());

		$option	=	'<option value=0>----------select one----------</option>';
		$contacts = Zend_Registry::get('database')->get_contacts()->fetchAll();
		foreach($contacts as $contact){
			$option.="<option value='".$contact['contact_id']."'>".$contact['firstname']." ".$contact['lastname']."</option>";
		}
		$markup  = sprintf($this->_format,$label,$name,$name,$option,$element->getView()->formErrors($messages));
		return $markup;
	}
}


class contacttitle_decorator extends Zend_Form_Decorator_Abstract
{

	private $database;
	protected $_format = "<tr>
							<td align='right' style='width:200px'>%s</td>
							<td style='width:210px'>
								<select name='%s' id='%s' style='width:205px'>
									%s
								</select>
							</td>
							<td>%s
							</td>
						  </tr>";
	public function render($content)
	{

		$element = $this->getElement();
		$messages = $element->getMessages();
		$name    = htmlentities($element->getFullyQualifiedName());
		$label   = htmlentities($element->getLabel());
		$value   = htmlentities($element->getValue());
		$id      = htmlentities($element->getId());

		$option	=	'<option value=0>----------select one----------</option>';


		$titles = Zend_Registry::get('database')->get_items()->fetchAll("item_type='contact_titles'");


		foreach($titles as $title)
		{
			if($value==$title['item_id']){
				$option.="<option value='".$title['item_id']."' selected>".$title['item_value']."</option>";
			}else{
				$option.="<option value='".$title['item_id']."'>".$title['item_value']."</option>";
			}
				
		}
		$markup  = sprintf($this->_format,$label,$name,$name,$option,$element->getView()->formErrors($messages));
		return $markup;
	}
}



class contactstate_decorator extends Zend_Form_Decorator_Abstract
{


	protected $_format = "<tr>
							<td align='right'>%s</td>
							<td>
								<select name='%s' id='%s' style='width:205px'>
									 <option value='AL'>AL</option><option value='AK'>AK</option><option value='AZ'>AZ</option><option value='AR'>AR</option><option value='CA' selected>CA</option><option value='CO'>CO</option><option value='CT'>CT</option><option value='DE'>DE</option><option value='FL'>FL</option><option value='GA'>GA</option>
							   		 <option value='HI'>HI</option><option value='ID'>ID</option><option value='IL'>IL</option><option value='IN'>IN</option><option value='IA'>IA</option><option value='KS'>KS</option><option value='KY'>KY</option><option value='LA'>LA</option><option value='ME'>ME</option><option value='MD'>MD</option>															   <option value='Massachusetts'>MA</option>
							   		 <option value='MI'>MI</option><option value='MN'>MN</option><option value='MS'>MS</option><option value='MO'>MO</option><option value='MT'>MT</option><option value='NE'>NE</option><option value='NV'>NV</option><option value='NH'>NH</option><option value='NJ'>NJ</option><option value='NM'>NM</option>
							   		 <option value='NY'>NY</option><option value='NC'>NC</option><option value='ND'>ND</option><option value='OH'>OH</option><option value='OK'>OK</option><option value='OR'>OR</option><option value='PA'>PA</option><option value='RI'>RI</option><option value='SC'>SC</option><option value='SD'>SD</option>															   <option value='Tennessee'>TN</option>
							   		 <option value='TX'>TX</option><option value='UT'>UT</option><option value='VT'>VT</option><option value='VA'>VA</option><option value='WA'>WA</option><option value='WV'>WV</option><option value='WI'>WI</option><option value='WY'>WY</option>
								
								</select>
							</td>
							<td>%s</td>
						 </tr>";
	public function render($content)
	{

		$element = $this->getElement();
		$messages = $element->getMessages();
		$name    = htmlentities($element->getFullyQualifiedName());
		$label   = htmlentities($element->getLabel());
		$id      = htmlentities($element->getId());
		$value   = htmlentities($element->getValue());
		$markup  = sprintf($this->_format,$label,$name,$name,$element->getView()->formErrors($messages));
		return $markup;
	}
}



class contacttext_decorator extends Zend_Form_Decorator_Abstract
{
	protected $_format = "<tr>
    						<td align='right'>%s</td>
    						<td><input name='%s' id='%s' type='text' size='30' class='text' value='%s'/></td>
    						<td>%s</td>
    					  </tr>";
	public function render($content)
	{

		$element = $this->getElement();
		$messages = $element->getMessages();
		$name    = htmlentities($element->getFullyQualifiedName());
		$value   = htmlentities($element->getValue());
		$label   = htmlentities($element->getLabel());
		$id      = htmlentities($element->getId());


		$markup  = sprintf($this->_format,$label,$name,$name,$value,$element->getView()->formErrors($messages));
		return $markup;
	}
}

class contactsubmit_decorator extends Zend_Form_Decorator_Abstract
{
	protected $_format = "<tr>
    						<td>%s</td>
    						<td><input name='%s' id='%s' type='submit' size='30' value='%s' class='button'/></td>
    						<td>
    						</td>
    					  </tr>";
	public function render($content)
	{

		$element = $this->getElement();
		$name    = htmlentities($element->getFullyQualifiedName());
		$messages = $element->getMessages();
		$label   = htmlentities($element->getLabel());
		$id      = htmlentities($element->getId());
		$value   = htmlentities($element->getValue());

		$markup  = sprintf($this->_format,$label,$name,$id,$value);
		return $markup;
	}
}



class contactheadtable_decorator extends Zend_Form_Decorator_Abstract
{
	public function render($content)
	{
		return '<fieldset>
    				<legend>Contact:</legend>
					<table style="width:700px" border="0">'.$content;

	}
}

class contacttailtable_decorator extends Zend_Form_Decorator_Abstract
{
	public function render($content)
	{
		return $content.'</table>
						 </fieldset>';
	}
}



?>