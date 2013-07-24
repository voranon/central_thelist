<?php

class thelist_purchasingform_purchaseorderitem extends Zend_Form{
	
	public function __construct($function_type, $variable)
	{
		parent::__construct($function_type, $variable);

		$eq_type_id = new Zend_Form_Element_Select('eq_type_id',
		array(
											'label'      => 'Equipment Type:',
											'decorators' => array(
		new purchaseorderitemdecorator($function_type, $variable)
		)
		)
		);
		$eq_type_id->setRegisterInArrayValidator(false);
		$eq_type_id->setRequired(true);
	
		$quantity   = new Zend_Form_Element_Textarea('quantity',
		array(
													'label'      => 'Quantity:',
													'decorators' => array(
		new purchaseorderitemdecorator($function_type, $variable)
		)
		)
		);
		$quantity->setRequired(true);
		
		$piece_cost   = new Zend_Form_Element_Textarea('piece_cost',
		array(
															'label'      => 'Per Piece cost:',
															'decorators' => array(
		new purchaseorderitemdecorator($function_type, $variable)
		)
		)
		);
		$piece_cost->setRequired(true);
		
		$deliver_by   = new Zend_Form_Element_Textarea('deliver_by',
		array(
																	'label'      => 'Deliver by:',
																	'decorators' => array(
		new purchaseorderitemdecorator($function_type, $variable)
		)
		)
		);
		$deliver_by->setRequired(true);
		
		$po_item_note   = new Zend_Form_Element_Textarea('po_item_note',
		array(
																	'label'      => 'Item Note:',
																	'decorators' => array(
		new purchaseorderitemdecorator($function_type, $variable)
		)
		)
		);

		
		//is this an add or edit/delete function
		if ($function_type == 'add') {
			
			$create     	   = new Zend_Form_Element_Submit('create',
			array(
														'label'      =>'Create',
														'value'		 =>'D',
														'decorators' => array(
			new purchaseorderitemdecorator($function_type, $variable)
			)
			)
			);
			
			$this->addElements(array($eq_type_id,$quantity,$piece_cost,$deliver_by,$po_item_note,$create));
			
		} elseif ($function_type == 'edit') {
			
			$edit     	   = new Zend_Form_Element_Submit('edit',
			array(
																	'label'      =>'Edit',
																	'value'		 =>'E',
																	'decorators' => array(
			new purchaseorderitemdecorator($function_type, $variable)
			)
			)
			);
			
			$delete     	   = new Zend_Form_Element_Submit('delete',
			array(
																	'label'      =>'Delete',
																	'value'		 =>'R',
																	'decorators' => array(
			new purchaseorderitemdecorator($function_type, $variable)
			)
			)
			);
				
			$this->addElements(array($eq_type_id,$quantity,$piece_cost,$deliver_by,$po_item_note,$edit,$delete));
	
		}


	}
}


class purchaseorderitemdecorator extends Zend_Form_Decorator_Abstract
{
	
	private $database;
	private $_function_type;
	private $_variable;

	public function __construct($function_type=null, $variable=null){
		

		
		if ($function_type != null && $variable != null) {
			
			$this->_function_type = $function_type;
			$this->_variable = $variable;
			
		}

	}
	
	public function render($content)
	{
		$element = $this->getElement();
		$messages = $element->getMessages();
		$name    = htmlentities($element->getFullyQualifiedName());
		$label   = htmlentities($element->getLabel());
		$id      = htmlentities($element->getId());
		$value   = htmlentities($element->getValue());
		
		
	if ($this->_function_type == 'add') {	
		if($name == 'eq_type_id'){
		$format="<fieldset>
					<legend>Create Purchase Item</legend>
						<table style='width:700px'>
							<tr>
								<td style='width:200px'>%s</td>
								<td><select name='%s' id='%s' style='width: 300px'>
								<option value=''>-----Select One----</option>";
		
		$sql1 =	"SELECT eq_type_id, eq_type_friendly_name FROM equipment_types WHERE eq_type_active='1'";
		
		$eq_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql1);

		foreach($eq_types as $eq_type){
		$format.=					"<option value='".$eq_type['eq_type_id']."'>".$eq_type['eq_type_friendly_name']."</option>";
								}
		
		
		$format.="				</select>
								</td>
								<td>%s</td>
							</tr>";
			
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
			
		} else if ($name == 'quantity'){
		$format="			<tr>
								<td style='width:200px'>%s</td>
								<td style='width:150px'><input name='%s' id='%s' type='text' class='text'></input></td>
								<td>&nbsp;</td>
							</tr>";
			
			return sprintf($format,$label,$name,$name);
			
		} else if ($name == 'piece_cost'){
		$format="			<tr>
								<td style='width:200px'>%s</td>
								<td style='width:150px'><input name='%s' id='%s' type='text' class='text'></input></td>
								<td>&nbsp;</td>
							</tr>";
			
			return sprintf($format,$label,$name,$name);
			
		} else if ($name == 'deliver_by'){
		$format="			<tr>
								<td style='width:200px'>%s</td>
								<td style='width:150px'><input name='%s' id='%s' type='text' class='text'></input></td>
								<td>&nbsp;</td>
							</tr>";
			
			return sprintf($format,$label,$name,$name);
			
		} else if ($name == 'po_item_note'){
		$format="			<tr>
								<td style='width:200px'>%s</td>
								<td style='width:150px'><input name='%s' id='%s' type='text' class='text'></input></td>
								<td>&nbsp;</td>
							</tr>";
			
			return sprintf($format,$label,$name,$name);
			
		} else if($name == 'create'){
				$format="			<tr>
											<td colspan='3' align='center'>
											<input name='%s' id='%s' type='submit' class='button' value='Create'></input>
											<input name='po_id' type='hidden' value='".$this->_variable."'></input>
											</td>
										</tr>
									</table>
								</fieldset>";	
				return sprintf($format,$name,$name);
		}
		
		//if we are editing	
		} else if ($this->_function_type == 'edit') {
				
			$sql = "SELECT i.item_id, i.item_name, i.item_value, et.eq_type_id, et.eq_manufacturer, et.eq_model_name, poi.quantity, poi.piece_cost, poi.deliver_by, poi.po_item_note FROM purchase_order_items poi
					LEFT OUTER JOIN equipment_types et ON et.eq_type_id=poi.eq_type_id
					LEFT OUTER JOIN items i ON i.item_id=poi.account
					WHERE poi.po_item_id='".$this->_variable."'
					";
			
			

			$po_specific_item = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);

		if($name == 'eq_type_id'){
		$format="<fieldset>
					<legend>Edit Purchase Item</legend>
						<table style='width:700px'>
							<tr>
								<td style='width:200px'>%s</td>
								<td><select name='%s' id='%s' style='width: 300px'>
								<option value='".$po_specific_item['0']['eq_type_id']."'>".$po_specific_item['0']['eq_manufacturer']." - ".$po_specific_item['0']['eq_model_name']."</option>";
		
		$sql1 =	"SELECT eq_type_id, eq_type_name, eq_manufacturer FROM equipment_types WHERE eq_type_active='1'";
		
		$eq_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql1);

		foreach($eq_types as $eq_type){
		$format.=					"<option value='".$eq_type['eq_type_id']."'>".$eq_type['eq_manufacturer']." - ".$eq_type['eq_type_name']."</option>";
								}
		
		
		$format.="				</select>
								</td>
								<td>%s</td>
							</tr>";
			
			return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
			
		} else if ($name == 'quantity'){
		$format="			<tr>
								<td style='width:200px'>%s</td>
								<td style='width:150px'><input name='%s' id='%s' type='text' class='text' value='".$po_specific_item['0']['quantity']."'></input></td>
								<td>&nbsp;</td>
							</tr>";
			
			return sprintf($format,$label,$name,$name);
			
		} else if ($name == 'piece_cost'){
		$format="			<tr>
								<td style='width:200px'>%s</td>
								<td style='width:150px'><input name='%s' id='%s' type='text' class='text' value='".$po_specific_item['0']['piece_cost']."'></input></td>
								<td>&nbsp;</td>
							</tr>";
			
			return sprintf($format,$label,$name,$name);
			
		} else if ($name == 'deliver_by'){
		$format="			<tr>
								<td style='width:200px'>%s</td>
								<td style='width:150px'><input name='%s' id='%s' type='text' class='text' value='".$po_specific_item['0']['deliver_by']."'></input></td>
								<td>&nbsp;</td>
							</tr>";
			
			return sprintf($format,$label,$name,$name);
			
		} else if ($name == 'po_item_note'){
		$format="			<tr>
								<td style='width:200px'>%s</td>
								<td style='width:150px'><input name='%s' id='%s' type='text' class='text' value='".$po_specific_item['0']['po_item_note']."'></input></td>
								<td>&nbsp;</td>
							</tr>";
			
			return sprintf($format,$label,$name,$name);
			
		} else if($name == 'edit'){
			$format="			<tr>
										<td colspan='2' align='right'>
										<input name='%s' id='%s' type='submit' class='button' value='Edit'></input>
										<input name='po_item_id' type='hidden' value='".$this->_variable."'></input>
										</td>";	
			return sprintf($format,$name,$name);
			
			
		} else if ($name == 'delete') {
			
			$format="					<td colspan='2' align='left'>
										<input name='%s' id='%s' type='submit' class='button' value='Delete'></input>
										</td>
									</tr>
								</table>
							</fieldset>";	
			return sprintf($format,$name,$name);
			
			
		}
			
		}
	
	}
		
}
