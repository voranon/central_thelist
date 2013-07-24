<?php

require APPLICATION_PATH.'/models/html_elements/forms/purchasingforms/purchaseorderitemform.php';

class thelist_model_purchase_order_items{
	
	private $database;
	private $log;
	
	private $_po_item_id;
	private $_po_id;
	private $_eq_type_id;
	private $_quantity;
	private $_piece_cost;
	private $_deliver_by;
	private $_po_item_note;
	private $_creator;
	private $_createdate;
	private $_account;
	private $_po_lock;
	private $_eq_type_serialized;
	private $_not_serialized_amount_received;
	private $_canceled_amount;
	private $_canceled_desc;	
	
	
	public function __construct($po_item_id, $po_locked=null)
	{
		
		$this->_po_item_id=$po_item_id;
		$this->_po_lock=$po_locked;
		
		
		$this->log	= Zend_Registry::get('logs');
		$this->user_session = new Zend_Session_Namespace('userinfo');

				
		$po_item=Zend_Registry::get('database')->get_purchase_order_items()->fetchRow('po_item_id='.$this->_po_item_id);
		
		$this->_po_id								=	$po_item['po_id'];
		$this->_eq_type_id							=	$po_item['eq_type_id'];
		$this->_quantity							=	$po_item['quantity'];
		$this->_piece_cost							=	$po_item['piece_cost'];
		$this->_account								=	$po_item['account'];
		$this->_deliver_by							=	$po_item['deliver_by'];
		$this->_po_item_note						=	$po_item['po_item_note'];
		$this->_creator								=	$po_item['creator'];
		$this->_createdate							=	$po_item['createdate'];
		$this->_not_serialized_amount_received		=	$po_item['not_serialized_amount_received'];
		$this->_canceled_amount						=	$po_item['canceled_amount'];
		$this->_canceled_desc						=	$po_item['canceled_desc'];
		
		$equipment_type_detail=Zend_Registry::get('database')->get_equipment_types()->fetchRow('eq_type_id='.$this->_eq_type_id);
		
		$this->_eq_type_serialized					=	$equipment_type_detail['eq_type_serialized'];
		
	}
	
	public function get_po_item_id(){
		return $this->_po_item_id;
	}
	public function get_po_id(){
		return $this->_po_id;
	}
	public function get_eq_type_id(){
		return $this->_eq_type_id;
	}
	public function get_quantity(){
		return $this->_quantity;
	}
	public function get_piece_cost(){
		return $this->_piece_cost;
	}
	public function get_account(){
		return $this->_account;
	}
	public function get_deliver_by(){
		return $this->_deliver_by;
	}
	public function get_po_item_note(){
		return $this->_po_item_note;
	}
	public function get_creator(){
		return $this->_creator;
	}
	public function get_createdate(){
		return $this->_createdate;
	}
	
	public function get_remaining_amount(){
		
		$amount = $this->_quantity - ($this->_canceled_amount + $this->get_amount_received());
		
		return $amount;
	}
	
	public function get_amount_received(){
		
		if ($this->_eq_type_serialized == 1) {
			
			return $this->get_number_of_equipments();
			
		} else if ($this->_eq_type_serialized == 0) {
			
			return $this->_not_serialized_amount_received;
			
		}

	}
	public function get_canceled_amount(){
		return $this->_canceled_amount;
	}
	public function get_canceled_desc(){
		return $this->_canceled_desc;
	}
	
	public function get_item_serialized(){
		
		if ($this->_eq_type_serialized == 1) {
			
			return true;
			
		} else if ($this->_eq_type_serialized == 0) {
			
			return false;
			
		}

	}
	

	
	//number of devices received already
	public function get_number_of_equipments(){
		
		if ($this->_eq_type_serialized == 1) {
			
			$sql = "SELECT COUNT(eq_id) AS number_of_eq FROM equipments
							WHERE po_item_id='".$this->_po_item_id."'
							";
			
			return Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
			
		} else if ($this->_eq_type_serialized == 0) {
			
			return $this->_not_serialized_amount_received;
			
		}
		
	}
	
	public function del_item()
	{
		if ($this->_po_lock == 1) {
			return false;
		}
		return $this->delete_single_row($this->_po_item_id, 'purchase_order_items');

	}

	public function set_po_id($po_id){
		if ($this->_po_lock == 1) {
			return false;
		}
		$this->set_single_attribute($this->_po_item_id, 'purchase_order_items', 'po_id', $po_id);
		
	}
	public function set_eq_type_id($eq_type_id){
		if ($this->_po_lock == 1) {
			return false;
		}
		$this->set_single_attribute($this->_po_item_id, 'purchase_order_items', 'eq_type_id', $eq_type_id);
	
	}
	public function set_quantity($quantity){
		if ($this->_po_lock == 1) {
			return false;
		}
		$this->set_single_attribute($this->_po_item_id, 'purchase_order_items', 'quantity', $quantity);
	
	}
	public function set_piece_cost($piece_cost){
		if ($this->_po_lock == 1) {
			return false;
		}
		$this->set_single_attribute($this->_po_item_id, 'purchase_order_items', 'piece_cost', $piece_cost);
	
	}
	public function set_account($account){
		if ($this->_po_lock == 1) {
			return false;
		}
		$this->set_single_attribute($this->_po_item_id, 'purchase_order_items', 'account', $account);
	
	}
	public function set_deliver_by($deliver_by){
		if ($this->_po_lock == 1) {
			return false;
		}
		$this->set_single_attribute($this->_po_item_id, 'purchase_order_items', 'deliver_by', $deliver_by);
	
	}
	public function set_po_item_note($po_item_note){
		if ($this->_po_lock == 1) {
			return false;
		}
		$this->set_single_attribute($this->_po_item_id, 'purchase_order_items', 'po_item_note', $po_item_note);
	
	}
	
	public function add_to_not_serialized_amount_received($amount){
		
		$new_amount = $this->_not_serialized_amount_received + $amount;
		
		if ($amount <= $this->get_remaining_amount()) {

			$this->set_single_attribute($this->_po_item_id, 'purchase_order_items', 'not_serialized_amount_received', $new_amount);
			
			$this->_not_serialized_amount_received = $new_amount;
			
			return true;
	
		} else {
			
			return false;
			
		}

	
	}
	
	public function set_canceled_amount($amount){

		//since we are setting the amount we need to add the current canceled amount to the total avail.
		$new_amount = $this->get_remaining_amount() + $this->_canceled_amount;
		
		if ($amount <= $new_amount) {
	
			$this->set_single_attribute($this->_po_item_id, 'purchase_order_items', 'canceled_amount', $amount);
				
			$this->_canceled_amount = $amount;
				
			return true;
	
		} else {
				
			return false;
				
		}
	
	
	}
	

	private function set_single_attribute($pri_key_to_update, $table_name, $column, $new_value)
	{
		$sql_find_pri_key = "SELECT COLUMN_NAME FROM information_schema.columns
												WHERE TABLE_SCHEMA = 'thelist' 
												AND TABLE_NAME = '".$table_name."' 
												AND extra = 'auto_increment'
												";
	
		$auto_increment_column = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql_find_pri_key);
	
		$private_format_of_column_name = "_".$column;
		$database_method = "get_".$table_name;

		$sql_old = "SELECT * FROM $table_name
						WHERE $auto_increment_column = '".$pri_key_to_update."'
						";
	
		$old = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql_old);

		if($this->$private_format_of_column_name != $new_value){
				
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
				
			$this->log->get_user_logger()->insert(
			array(
									'uid'					=>		$this->user_session->uid,
									'page_name'				=>		'http://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"],
									'event'            	 	=>      'change_single_column',
									'class_name'        	=>      get_class($this),
									'method_name'       	=>      $method,
									'primary_key_name'  	=>      $auto_increment_column,
									'primary_key_value' 	=>		$pri_key_to_update,
									'xml_message_1'			=>		$this->array_as_xml($old),
									'xml_message_2'			=>		$this->array_as_xml(array($column => $new_value)),
									'ip_address'			=>		$_SERVER['REMOTE_ADDR'],
			)
			);
	
		}
	
			
		$data= array(
			
							"$column"	=> $new_value
	
		);
	
		Zend_Registry::get('database')->$database_method()->update($data,"".$auto_increment_column."='".$pri_key_to_update."'");
	
		$this->$private_format_of_column_name=$new_value;
	
	}
	
	private function delete_single_row($pri_key_to_delete, $table_name)
	{
		$sql_find_pri_key = "SELECT COLUMN_NAME FROM information_schema.columns
								WHERE TABLE_SCHEMA = 'thelist' 
								AND TABLE_NAME = '".$table_name."' 
								AND extra = 'auto_increment'
								";
	
		$auto_increment_column = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql_find_pri_key);
	
		$database_method = "get_".$table_name;
	
		$sql_before_delete = "SELECT * FROM $table_name
									WHERE $auto_increment_column=$pri_key_to_delete"; 
	
		$old = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql_before_delete);
	
	
		if($old != false){
	
			$trace  = debug_backtrace();
			$method = $trace[0]["function"];
	
			$this->log->get_user_logger()->insert(
			array(
										'uid'				=>		$this->user_session->uid,
										'page_name'			=>		'http://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"],
										'event'             =>      'delete row',
										'class_name'        =>      get_class($this),
										'method_name'       =>      $method,
										'primary_key_name'  =>      $auto_increment_column,
										'primary_key_value' =>		$pri_key_to_delete,
										'xml_message_1'		=>		$this->array_as_xml($old),
										'ip_address'		=>		$_SERVER['REMOTE_ADDR'],
			)
			);
	
		}
	
		Zend_Registry::get('database')->$database_method()->delete("".$auto_increment_column."='".$pri_key_to_delete."'");
	
	}
	
	private function array_as_xml($data)
	{
		
		$xmlDoc = new DOMDocument();
	
		$head = $xmlDoc->appendChild(
		$xmlDoc->createElement("data"));

		while ($data_item = each($data)) {

			$head->appendChild(
			$xmlDoc->createElement($data_item['key'], $data_item['value']));
	
			next($data_item);
		}
		$xmlDoc->formatOutput = true;
	
		return $xmlDoc->saveXML();
	
	}
	
}
?>