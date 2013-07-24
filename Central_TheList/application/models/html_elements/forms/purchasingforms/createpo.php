<?php

class thelist_purchasingform_createpo extends Zend_Form{

	public function __construct($options = null)
	{
		parent::__construct($options);

		$po_subject   = new Zend_Form_Element_Textarea('po_subject',
								array(
											'label'      => 'PO Subject:',
											'decorators' => array(
																new createpodecorator()
														 		 )
									 )
									 				  );
		
	$po_vendor = new Zend_Form_Element_Select('vendor_id',
								array(
											'label'      => 'Vendor:',
											'decorators' => array(
																new createpodecorator()
														 		 )
									 )
														 );
		$po_vendor->setRegisterInArrayValidator(false);
		$po_vendor->setRequired(true);
		
	$po_terms = new Zend_Form_Element_Select('po_terms',
								array(
											'label'      => 'Terms:',
											'decorators' => array(
																new createpodecorator()
														 		 )
									 					 )
									);
		$po_terms->setRegisterInArrayValidator(false);
		$po_terms->setRequired(true);
		
	$po_freight = new Zend_Form_Element_Select('po_freight',
								array(
											'label'      => 'Freight:',
											'decorators' => array(
																new createpodecorator()
																)
									 )
											  );
		$po_freight->setRegisterInArrayValidator(false);
		$po_freight->setRequired(true);
		
		
		$create     	   = new Zend_Form_Element_Submit('create',
								 array(
											'label'      =>'Create',
											'value'		 =>'D',
											'decorators' => array(
																new createpodecorator()
														 		)
								      )
														);
		
		
		
		$this->addElements(array($po_subject,$po_vendor,$po_terms,$po_freight,$create));

	}
}


class createpodecorator extends Zend_Form_Decorator_Abstract
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
		
		if($name == 'po_subject'){
		$format="<fieldset>
					<legend>Create Purchase Order</legend>
						<table style='width:505px'>
							<tr>
								<td style='width:100px'>%s</td>
								<td style='width:150px'><input name='%s' id='%s' type='text' class='text'></input></td>
								<td>&nbsp;</td>
							</tr>";
			
			return sprintf($format,$label,$name,$name);
			
		}else if($name == 'vendor_id'){
		$format="			<tr>
								<td>%s</td>
								<td>
								<select name='%s' id='%s' style='width: 145px'>";
		$format.=					"<option value=''>-----Select One----</option>";
		
			$sql1 = "SELECT * FROM vendors WHERE vendor_active='1'";
		
			$vendors = $eq_start_type = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql1);
			
								foreach($vendors as $vendor){
		$format.=					"<option value='".$vendor['vendor_id']."'>".$vendor['vendor_name']."</option>";
								}
		
		
		$format.="				</select>
								</td>
								<td>%s</td>
							</tr>";
			
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
		}else if($name == 'po_terms'){
		$format="			<tr>
								<td>%s</td>
								<td>
								<select name='%s' id='%s' style='width: 145px'>";
		$format.=					"<option value=''>-----Select One----</option>";
		
		$sql2 = "SELECT * FROM items WHERE item_type='po_terms' AND item_active='1'";
		
			$terms = $eq_start_type = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
								foreach($terms as $term){
		$format.=					"<option value='".$term['item_id']."'>".$term['item_value']."</option>";
								}
		
		
		$format.="				</select>
								</td>
								<td>%s</td>
							</tr>";
			
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
		}else if($name == 'po_freight'){
		$format="			<tr>
								<td>%s</td>
								<td>
								<select name='%s' id='%s' style='width: 145px'>";
		$format.=					"<option value=''>-----Select One----</option>";
		
			$sql3 = "SELECT * FROM items WHERE item_type='po_freight' AND item_active='1'";
		
			$freights = $eq_start_type = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql3);
								foreach($freights as $freight){
		$format.=					"<option value='".$freight['item_id']."'>".$freight['item_value']."</option>";
								}
		
		
		$format.="				</select>
								</td>
								<td>%s</td>
							</tr>";
			
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
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