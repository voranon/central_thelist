<?php

class PurchasingController extends Zend_controller_Action
{
	private $_user_session;
	
	public function init()
	{
		$this->_user_session 	= new Zend_Session_Namespace('userinfo');
	
		if($this->_user_session->uid == '') {
				
			//no uid, user not logged in
			Zend_Registry::get('logs')->get_app_logger()->log("User not logged in, return to index", Zend_Log::ERR);
			header('Location: /');
			exit;
				
		} else {
						
			//nothing not a perspective controller
			$layout_manager = new Thelist_Utility_layoutmanager($this->_user_session->current_perspective, $this->_helper);
			$layout_manager->set_layout();
			
			//create the head
			$main_menu     		= new Thelist_Html_element_mainmenu();
			$perspective_menu 	= new Thelist_Html_element_perspectivemenu();
			
			$main_menu->set_htmlmainmenu($this->_user_session->current_perspective);
			$perspective_menu->set_htmlperspectivemenu($this->_user_session->current_perspective);
				
			// create menu for main and perspective
			$this->view->placeholder('mainmenu')->append($main_menu->get_htmlmainmenu());
			$this->view->placeholder('perspective_menu')->append($perspective_menu->get_htmlperspectivemenu());
				
			// create homelink
			$this->view->placeholder('homelink')->append($this->_user_session->perspective);
		}
	}
	
	public function preDispatch()
	{
		$permission			= new Thelist_Utility_acl($this->_user_session->role_id);
		$controller 		= $this->getRequest()->getControllerName();
		$action 			= $this->getRequest()->getActionName();
	
		$clearance 			= $permission->acl_clearance($action, $controller);
	
		//log the page request
		$report	= array(
								'uid'					=> $this->_user_session->uid,
								'page_name'				=> $this->view->url(),
								'message_1'				=> '',
								'message_2'				=> '',
		);
	
		if ($clearance === true) {
	
			$report['event']	= 'page_change';
			Zend_Registry::get('database')->insert_single_row('user_event_logs', $report, $controller, $action);
	
		} else {
				
			$report['event']	= 'acl_deny';
			Zend_Registry::get('database')->insert_single_row('user_event_logs', $report, $controller, $action);
				
			throw new exception("'".$this->_user_session->firstname." ".$this->_user_session->lastname."'. You are trying to access controller name: '".$controller."' using Action name: '".$action."', but you are not allowed to access this page", 22500);
		}
	}
	
	public function postDispatch()
	{
	
	}
	
	public function purchaseordersAction()
	{
		$sql = "SELECT po.po_lock, po.po_id, po.po_number, po.po_subject, po.order_date,u.firstname, u.lastname,i3.item_value AS po_status, i2.item_value AS po_freight, v.vendor_name FROM purchase_orders po
				LEFT OUTER JOIN vendors v ON v.vendor_id=po.vendor_id
				LEFT OUTER JOIN users u ON u.uid=po.creator
				LEFT OUTER JOIN items i2 ON i2.item_id=po.po_freight
				LEFT OUTER JOIN items i3 ON i3.item_id=po.po_status
				ORDER BY order_date ASC
			   ";
		$purchase_orders = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
		$this->view->placeholder('purchase_order_table')
		->append("<table class='display' style='width:900px;left:250px'>
					<tr>
						<td class='display' style='width: 100px'>Number</td>
						<td class='display' style='width: 200px'>Status</td>
						<td class='display' style='width: 200px'>Subject</td>
						<td class='display' style='width: 300px'>Ordered By</td>
						<td class='display' style='width: 300px'>Ordered Date</td>
						<td class='display' style='width: 150px'>Vendor</td>
						<td class='display' style='width: 150px'>Receiving</td>
					</tr>");
		
		foreach($purchase_orders as $purchase_order){
			$this->view->placeholder('purchase_order_table')
			->append("<tr>
						<td class='display'><a href='/purchasing/edit/?po_id=".$purchase_order['po_id']."' >".$purchase_order['po_number']."</a></td>
						<td class='display'>".$purchase_order['po_status']."</td>
						<td class='display'>".$purchase_order['po_subject']."</td>
						<td class='display'>".$purchase_order['firstname']." ".$purchase_order['lastname']."</td>
						<td class='display'>".$purchase_order['order_date']."</td>
						<td class='display'>".$purchase_order['vendor_name']."</td>
					 ");
			
			if ($purchase_order['po_lock'] == 1) {
				$this->view->placeholder('purchase_order_table')
				->append("
				<td class='display'><a href='/inventory/receiveequipment?po_id=".$purchase_order['po_id']."'>Receive Items</a></td>
			 	</tr>");
				
			} else if ($purchase_order['po_lock'] == 0) {
				
				$this->view->placeholder('purchase_order_table')
				->append("
				<td class='display'></td>
			 	</tr>");
			
			}
		}
			$this->view->placeholder('purchase_order_table')
			->append("</table>");
	}
	
	
	public function get_eq_types()
	{
	
		$sql =	"SELECT DISTINCT(eq_manufacturer) FROM equipment_types
							";
	
		$equipment_type = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
	
		$equipment_type_dd = '';
			
		$equipment_type_dd.="<SELECT NAME='eq_manufacturer'>";
		$equipment_type_dd.="<OPTION VALUE=''>Choose One</OPTION>";
	
		foreach ($equipment_type as $eq_type) {
	
			$eq_manufacturer=$eq_type['eq_manufacturer'];
			$equipment_type_dd.="<OPTION VALUE=\"$eq_manufacturer\">".$eq_manufacturer."</OPTION>";
		}
	
		$equipment_type_dd .= "</SELECT>";
			
		return $equipment_type_dd;
	
	}
	
	public function get_vendors_dd($vendor_id)
	{
	
		$sql =	"SELECT vendor_id, vendor_name FROM vendors
				WHERE vendor_active='1'
				";
	
		$vendors = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		$vendors_dd = '';
		$vendors_dd.="<SELECT NAME='vendor_id'>";
		
		if ($vendor_id == '') {
			
			$vendors_dd.="<OPTION VALUE=''>Choose One</OPTION>";
			
		} else {
			
			$sql2 =	"SELECT vendor_name FROM vendors
					WHERE vendor_id='".$vendor_id."'
					";
			
			$vendor_name = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
			$vendors_dd.="<OPTION VALUE='".$vendor_id."'>$vendor_name</OPTION>";
			
		}

		foreach ($vendors as $vendor) {
	
			$vendor_id		=	$vendor['vendor_id'];
			$vendor_name 	=	$vendor['vendor_name'];
			$vendors_dd.="<OPTION VALUE=\"$vendor_id\">".$vendor_name."</OPTION>";
		}
	
		$vendors_dd .= "</SELECT>";
			
		return $vendors_dd;
	
	}
	
	public function get_item_dd($item_type, $item_id)
	{
	
		$sql =	"SELECT item_id, item_value FROM items
				WHERE item_type='".$item_type."'
				";
	
		$items = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		$items_dd = '';
		$items_dd.="<SELECT NAME='".$item_type."'>";
	
		if ($item_id == '') {
				
			$items_dd.="<OPTION VALUE=''>Choose One</OPTION>";
				
		} else {
				
			$sql2 =	"SELECT item_value FROM items
						WHERE item_id='".$item_id."'
						";
				
			$item_value = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
			$items_dd.="<OPTION VALUE='".$item_id."'>$item_value</OPTION>";
				
		}
	
		foreach ($items as $item) {
	
			$item_id		=	$item['item_id'];
			$item_value 	=	$item['item_value'];
			$items_dd.="<OPTION VALUE=\"$item_id\">".$item_value."</OPTION>";
		}
	
		$items_dd .= "</SELECT>";
			
		return $items_dd;
	
	}
	
	public function get_item_row($item_id)
	{
	
		$sql =	"SELECT * FROM items
					WHERE item_id='".$item_id."'
					";
	
		$item = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
			
		return $item;
	
	}
	
	public function createAction(){
		$this->_helper->layout->disableLayout();
		
		$createpoform = new Thelist_Purchasingform_createpo();
		$createpoform->setAction('/purchasing/create');
		$createpoform->setMethod('post');
		$this->view->createpoform=$createpoform;
		
		$sql = "SELECT item_id FROM items WHERE item_type='po_status' AND item_name='justcreated'";
		
		$po_create_status 		= Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		if($this->getRequest()->isPost()){
			if ($createpoform->isValid($_POST)) { // it's valid
				
				$data = array(
								'po_subject'  			=>  $_POST['po_subject'],
								'vendor_id'				=>  $_POST['vendor_id'],
								'po_terms'				=>  $_POST['po_terms'],
								'po_freight' 			=>	$_POST['po_freight'],  
								'po_status'				=>	$po_create_status,
								'creator'				=>	$_POST['creator'],

				);
				
				$po_id = Zend_Registry::get('database')->get_purchase_orders()->insert($data);	
				
				$po_number = 5000 + $po_id;
				
				$data2 = array(
								'po_number'			=> $po_number,
								'creator'			=> $this->_user_session->uid,
								
				);

				Zend_Registry::get('database')->get_purchase_orders()->update($data2, "po_id='".$po_id."'");

				echo "<script>
						window.close();
						  window.opener.location.href='/purchasing/edit?po_id=".$po_id."';
						
					</script>";		
				
				
			}		
		}
	}
	
	public function editAction(){
	
		$po_id = $_GET['po_id'];
		$purchase_order = new Thelist_Model_purchase_orders($po_id);
	
	
		if($this->getRequest()->isPost()){
				
			$purchase_order->set_po_subject($_POST['po_subject']);
			$purchase_order->set_vendor_id($_POST['vendor_id']);
			$purchase_order->set_po_terms($_POST['po_terms']);
			$purchase_order->set_po_freight($_POST['po_freight']);
			$purchase_order->set_order_date($_POST['order_date']);
			$purchase_order->set_shipping_cost($_POST['shipping_cost']);
			
			if (isset($_POST['create_pdf'])) {

				$purchase_order->create_po_pdf();
				
			}

		}
		
		//po status resolve name
		$po_status_item = $this->get_item_row($purchase_order->get_po_status());

		$this->view->po_number			=	$purchase_order->get_po_number();
		$this->view->po_subject			=	$purchase_order->get_po_subject();
		$this->view->vendor_dd 			=	$this->get_vendors_dd($purchase_order->get_vendor_id());
		$this->view->po_terms_dd		=	$this->get_item_dd('po_terms', $purchase_order->get_po_terms());
		$this->view->po_freight_dd		=	$this->get_item_dd('po_freight', $purchase_order->get_po_freight());
		$this->view->po_status			=	$po_status_item['item_value'];
		$this->view->order_date			=	$purchase_order->get_order_date();
		$this->view->create_date		=	$purchase_order->get_createdate();
		$this->view->shipping_cost		=	$purchase_order->get_shipping_cost();
				
		if ($purchase_order->get_po_lock() == 1) {
				
			$this->view->po_lock = 'Locked';
			$this->view->download_create_po =	"<a href='http://".$_SERVER["SERVER_NAME"]."/app_file_store/purchase_orders/".$purchase_order->get_po_number().".pdf'>Download PO</a>";
		
		} else if ($purchase_order->get_po_lock() == 0) {
				
			$this->view->po_lock = 'Unlocked';
			//if we have set a order date then we can make a po
			if ($purchase_order->get_order_date() != '0000-00-00') {
				
				$this->view->download_create_po = "<input type='submit' name='create_pdf' class='button' value='Create PO'></input>";
				
			}
	
		}

		$sql = "SELECT firstname, lastname from users WHERE uid='".$purchase_order->get_creator()."'";
		$username 							= Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		$this->view->creator				=	"".$username['firstname']." ".$username['lastname']."";
				
				$po_item_list="				<table class='display' style='width:1000px'>
											<tr>
											<td colspan='6' align='left'>PO Items:</td>
											<td align='right' style='width:50px'><input class='button' type='button' id='addpurchaseorderitem' value='Add'></input></td>
											</tr>
											<tr class='header'>
											<td class='display'>Manufacturer:</td>
											<td class='display'>Vendor Description:</td>
											<td class='display'>Quantity:</td>
											<td class='display'>Piece Cost:</td>
											<td class='display'>Deliver By:</td>
											<td class='display'>Added By:</td>";
											
											if ($purchase_order->get_po_lock() == 1) { 
					
				$po_item_list.="			<td class='display'>Received:</td>	
											<td class='display'>Canceled:</td>				
											";
											}
											
				$po_item_list.="			<td class='display'>Edit</td>
											</tr>";

		$po_items = $purchase_order->get_po_items();
		if(is_array($po_items)){
			foreach($po_items as $po_item){
				
				$item_eq_type 	= Zend_Registry::get('database')->get_equipment_types()->fetchRow("eq_type_id='".$po_item->get_eq_type_id()."'");
				$account 		= Zend_Registry::get('database')->get_items()->fetchRow("item_id='".$po_item->get_account()."'");
				$sql			= "SELECT firstname, lastname from users WHERE uid='".$po_item->get_creator()."'";
				$username 		= Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
				$item_creator 	= "".$username['firstname']." ".$username['lastname']."";

				$po_item_list .="
										 <tr>
											<td class='display'>".$item_eq_type['eq_manufacturer']."</td>
										 	<td class='display'>".$item_eq_type['eq_type_friendly_name']."</td>
										 	<td class='display'>".$po_item->get_quantity()."</td>
										 	<td class='display'>$".$po_item->get_piece_cost()."</td>
										 	<td class='display'>".$po_item->get_deliver_by()."</td>
										 	<td class='display'>".$item_creator."</td>";
										 	
											if ($purchase_order->get_po_lock() == 1) { 
					
				$po_item_list.="			<td class='display'>".$po_item->get_number_of_equipments()."</td>	
											<td class='display'>".$po_item->get_canceled_amount()."</td>				
											";
					
					
											} 
											
				$po_item_list.="				<td class='display'>
										 		<input class='button' type='button' id='editpurchaseorderitem' po_item_id='".$po_item->get_po_item_id()."' value='Edit'></input>
										 	</td>
										 </tr>
										";
				
					
			}
		}
		//return the list of items
		$this->view->po_item_list = $po_item_list;
	}
	
	public function addpurchaseorderitemAction()
	{
		$this->_helper->layout->disableLayout();
	
		if (isset($_GET['po_id'])) {
				
			$purchaseorderitemform = new purchaseorderitemform('add', $_GET['po_id']);
			$purchaseorderitemform->setAction('/purchasing/addpurchaseorderitem');
			$purchaseorderitemform->setMethod('post');
			$this->view->purchaseorderitemform=$purchaseorderitemform;
				
		} else if($this->getRequest()->isPost()){
				
			$purchaseorderitemform = new purchaseorderitemform('add', $_POST['po_id']);
			$purchaseorderitemform->setAction('/purchasing/addpurchaseorderitem');
			$purchaseorderitemform->setMethod('post');
			$this->view->purchaseorderitemform=$purchaseorderitemform;
				
			if ($purchaseorderitemform->isValid($_POST)) {
				// it's valid
				$data = array(
											'po_id'					=>  $_POST['po_id'],
											'eq_type_id'			=>  $_POST['eq_type_id'],
											'quantity' 				=>	$_POST['quantity'],  
											'piece_cost' 			=>	$_POST['piece_cost'],
											'account' 				=>	$_POST['account'],
											'deliver_by' 			=>	$_POST['deliver_by'],
											'po_item_note' 			=>	$_POST['po_item_note'],
											'creator'				=> 	$this->_user_session->uid,
				);
	
				Zend_Registry::get('database')->get_purchase_order_items()->insert($data);
				
				echo "<script>
									window.close();
									  window.opener.location.href='/purchasing/edit?po_id=".$_POST['po_id']."';
									
								</script>";		
	
	
			}
		}
	}
	
	public function editpurchaseorderitemAction(){
		$this->_helper->layout->disableLayout();
	
		if (isset($_GET['po_item_id'])) {
	
			$purchaseorderitemform = new purchaseorderitemform('edit', $_GET['po_item_id']);
			$purchaseorderitemform->setAction('/purchasing/editpurchaseorderitem');
			$purchaseorderitemform->setMethod('post');
			$this->view->purchaseorderitemform=$purchaseorderitemform;
	
		} else if($this->getRequest()->isPost()) {
				
			$purchaseorderitemform = new purchaseorderitemform('edit', $_POST['po_item_id']);
			$purchaseorderitemform->setAction('/purchasing/editpurchaseorderitem');
			$purchaseorderitemform->setMethod('post');
			$this->view->purchaseorderitemform=$purchaseorderitemform;
			
			if ($purchaseorderitemform->isValid($_POST)) {
	
				$sql = "SELECT po_id FROM purchase_order_items
							WHERE po_item_id='".$_POST['po_item_id']."'
							";
	
				$po_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
	
				$purchase_order = new Thelist_Model_purchase_orders($po_id);
				$single_po_item = $purchase_order->get_po_item($_POST['po_item_id']);
				
				if (isset($_POST['delete'])) {
						
					$single_po_item->del_item();
					
				} else if (isset($_POST['edit'])) {

						$single_po_item->set_eq_type_id($_POST['eq_type_id']);
						$single_po_item->set_quantity($_POST['quantity']);
						$single_po_item->set_piece_cost($_POST['piece_cost']);
						$single_po_item->set_account($_POST['account']);
						$single_po_item->set_deliver_by($_POST['deliver_by']);
						$single_po_item->set_po_item_note($_POST['po_item_note']);

					
				}
	
				echo "<script>
										window.close();
										  window.opener.location.href='/purchasing/edit?po_id=".$po_id."';
										
									</script>";		
	
			}
		}
	}

} 
?>