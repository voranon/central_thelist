<?php

require APPLICATION_PATH.'/models/purchase_order_items.php';

class thelist_model_purchase_orders{
	private $database;
	
	private $_po_id;
	private $_po_number;
	private $_po_subject;
	private $_order_date;
	private $_createdate;
	private $_creator;
	private $_po_status;
	private $_po_freight;
	private $_po_terms;
	private $_vendor_name;
	private $_po_items;
	private $_numberofitems;
	private $_shipping_cost;
	private $_po_lock;
		
	private $log;
	private $user_session;	
    
	public function __construct($po_id){
		$this->_po_id		= $po_id;	

		$this->log			= Zend_Registry::get('logs');
		$this->user_session = new Zend_Session_Namespace('userinfo');
		
		$sql="SELECT * FROM purchase_orders
				WHERE po_id='".$this->_po_id."'
				";
		
		$purchase_order = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
	
		$this->_po_number		=$purchase_order['po_number'];
		$this->_po_subject		=$purchase_order['po_subject'];
		$this->_order_date		=$purchase_order['order_date'];
		$this->_createdate		=$purchase_order['createdate'];
		$this->_creator			=$purchase_order['creator'];
		$this->_po_status		=$purchase_order['po_status'];
		$this->_po_freight		=$purchase_order['po_freight'];
		$this->_po_terms		=$purchase_order['po_terms'];
		$this->_vendor_id		=$purchase_order['vendor_id'];
		$this->_shipping_cost	=$purchase_order['shipping_cost'];
		$this->_po_lock			=$purchase_order['po_lock'];

		$po_items = Zend_Registry::get('database')->get_purchase_order_items()->fetchAll("po_id=".$this->_po_id);
		
		foreach($po_items as $po_item){
			$this->_numberofitems++;
			$this->_po_items[$po_item['po_item_id']] = new purchase_order_items($po_item['po_item_id'], $this->_po_lock	);
			
		}

	}

	public function get_po_id(){
		return $this->_po_id;
	}	
	public function get_po_number(){
		return $this->_po_number;
	}
	public function get_po_subject(){
		return $this->_po_subject;
	}
	public function get_order_date(){
		return $this->_order_date;
	}
	public function get_createdate(){
		return $this->_createdate;
	}
	public function get_creator(){
		return $this->_creator;
	}
	public function get_po_status(){
		return $this->_po_status;
	}
	public function get_po_freight(){
		return $this->_po_freight;
	}
	public function get_po_terms(){
		return $this->_po_terms;
	}
	public function get_vendor_id(){
		return $this->_vendor_id;
	}
	public function get_po_items(){
		return $this->_po_items;
	}
	public function get_po_item($item_id){
		
	$po_item = new purchase_order_items("$item_id", $this->_po_lock);
		
	return $po_item;
	

	}
	public function get_numberofitems(){
		return $this->_numberofitems;
	}
	public function get_shipping_cost(){
		return $this->_shipping_cost;
	}
	public function get_po_lock(){
		return $this->_po_lock;
	}

	
	public function set_po_subject($new_value){

		if ($this->_po_lock == 1) {
			return false;
		}
		$this->set_single_attribute($this->_po_id, 'purchase_orders', 'po_subject', $new_value);
	}
	public function set_order_date($new_value){
		if ($this->_po_lock == 1) {
			return false;
		}
		$this->set_single_attribute($this->_po_id, 'purchase_orders', 'order_date', $new_value);
	}
	public function set_creator($new_value){
		if ($this->_po_lock == 1) {
			return false;
		}
		$this->set_single_attribute($this->_po_id, 'purchase_orders', 'creator', $new_value);
	}
	public function set_po_status($new_value){
		if ($this->_po_lock == 1) {
			return false;
		}
		$this->set_single_attribute($this->_po_id, 'purchase_orders', 'po_status', $new_value);
	}
	public function set_po_freight($new_value){
		if ($this->_po_lock == 1) {
			return false;
		}
		$this->set_single_attribute($this->_po_id, 'purchase_orders', 'po_freight', $new_value);
	}
	public function set_po_terms($new_value){
		if ($this->_po_lock == 1) {
			return false;
		}
		$this->set_single_attribute($this->_po_id, 'purchase_orders', 'po_terms', $new_value);
	}
	public function set_vendor_id($new_value){
		if ($this->_po_lock == 1) {
			return false;
		}
		$this->set_single_attribute($this->_po_id, 'purchase_orders', 'vendor_id', $new_value);
	}
	public function set_shipping_cost($new_value){
		if ($this->_po_lock == 1) {
			return false;
		}
		$this->set_single_attribute($this->_po_id, 'purchase_orders', 'shipping_cost', $new_value);
	}
	private function lock_po(){
	
		$this->set_single_attribute($this->_po_id, 'purchase_orders', 'po_lock', '1');
	}
	
	public function create_po_pdf()
	{
		
		if ($this->_numberofitems == 0) {
			
			echo '<center><br><br><H2>Dude this PO has no items, what are you trying to do?</H2></center> ';
			die;
		}

		require_once 'Zend/Loader/Autoloader.php';

		$loader = Zend_Loader_Autoloader::getInstance();
		
		try {
		  // create PDF
		  $pdf = new Zend_Pdf();
		  
		  
		  // create A4 page
		  $page = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_LETTER_LANDSCAPE);

		  $sql = "SELECT item_name, item_value FROM items WHERE item_type='bai_address'";
		  $bai_address = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		  
		  $sql2 = "SELECT * FROM vendors WHERE vendor_id='".$this->_vendor_id."'";
		  $vendor = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql2);
		  
		  //top left
		  $bai_logo = Zend_Pdf_Image::imageWithPath("".APPLICATION_PATH."/models/images/purchasingimages/bai_logo.jpg");
		  $page->drawImage($bai_logo, 20, 580, 140, 600);

		  $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		  $page->setFont($font, 10);
		  $page->drawText("".$bai_address['0']['item_value']." ".$bai_address['1']['item_value']." ".$bai_address['2']['item_value']."", 20, 550);
		  $page->drawText("".$bai_address['3']['item_value']." ".$bai_address['4']['item_value']." ".$bai_address['5']['item_value']."", 20, 540);
		  
		  //top right
		  $page->setFont($font, 14);
		  $page->drawText("Purchase Order Number: ".$this->_po_number."", ($page->getWidth() - 300), 550);
		  
		  
		  //top line
		  $page->drawLine(15, 535, ($page->getWidth() - 15), 535);
		  
		  //vendor
		  $page->setFont($font, 12);
		  $page->setFillColor(Zend_Pdf_Color_Html::color('#CCFFFF'));
		  $page->drawRectangle(30, 440, 320, 525);
		  $page->setFillColor(Zend_Pdf_Color_Html::color('#000000'));
		  $page->drawText("Vendor:", 35, 510);

		  $page->setFont($font, 12);
		  $page->setFillColor(Zend_Pdf_Color_Html::color('#000000'));
		  $page->drawText("Name:", 40, 487);
		  $page->drawText("".$vendor['vendor_name']."", 100, 487);
		  $page->drawText("Address:", 40, 473);
		  $page->drawText("".$vendor['vendor_street_number']." ".$vendor['vendor_street']." ".$vendor['vendor_street_type']."", 100, 473);
		  $page->drawText("City:", 40, 459);
		  $page->drawText("".$vendor['vendor_city'].", ".$vendor['vendor_state']." ".$vendor['vendor_zip']."", 100, 459);
		  $page->drawText("Phone:", 40, 445);
		  $page->drawText("".$vendor['vendor_phone']."", 100, 445);
		  
		  //Bai 
		  
		  $page->setFont($font, 12);
		  $page->setFillColor(Zend_Pdf_Color_Html::color('#CCFFCC'));
		  $page->drawRectangle(472, 440, 762, 525);
		  $page->setFillColor(Zend_Pdf_Color_Html::color('#000000'));
		  $page->drawText("Ship To:", 477, 510);

		  $page->setFont($font, 12);
		  $page->setFillColor(Zend_Pdf_Color_Html::color('#000000'));
		  $page->drawText("Name:", 492, 487);
		  $page->drawText("".$bai_address['10']['item_value']."", 552, 487);
		  $page->drawText("Address:", 492, 473);
		  $page->drawText("".$bai_address['0']['item_value']." ".$bai_address['1']['item_value']." ".$bai_address['2']['item_value'].", ".$bai_address['9']['item_value']." #".$bai_address['8']['item_value']."", 552, 473);
		  $page->drawText("City:", 492, 459);
		  $page->drawText("".$bai_address['3']['item_value'].", ".$bai_address['4']['item_value']." ".$bai_address['5']['item_value']."", 552, 459);
		  $page->drawText("Phone:", 492, 445);
		  $page->drawText("".$bai_address['6']['item_value']."", 552, 445);
		  
		  //page 1
		  $j=0;				//where to start the item count
		  $i= $k = 410; 	//where to start
		  $u=25; 			//row height in pixels
		  $h=3;				//space in pixels between rows
		  $font_size = 12;	//The font Size for items item
		  $page->setFont($font, $font_size);
		  $page->drawText("Item #", 35, ($i + $h));
		  $page->drawText("Quantity:", 85, ($i + $h));
		  $page->drawText("Description:", 300, ($i + $h));
		  $page->drawText("Unit Price", 600, ($i + $h));
		  $page->drawText("Cost", 690, ($i + $h));
		  
		  $subtotal = 0;
		  if ($this->_numberofitems < 10) {

		  foreach ($this->_po_items as $single_item ) {
		  	$j++;

		  	$i-=($u +$h);
		  	
		  	if ($j % 2) {
		  	
		  		$page->setFillColor(Zend_Pdf_Color_Html::color('#CCFFCC'));
		  	
		  	} else {
		  		
		  		$page->setFillColor(Zend_Pdf_Color_Html::color('#CCFFFF'));
		  		
		  	}
		  	
		  	//generate subtotal
		  	$total_cost_of_this_item = "".$single_item->get_quantity() * $single_item->get_piece_cost()."";
		  	$subtotal += $total_cost_of_this_item;
		  	
		  			  	
		  	$page->drawRectangle(30, $i, ($page->getWidth() - 30), ($i + $u));
		  	$page->setFillColor(Zend_Pdf_Color_Html::color('#000000'));
		  	$page->setFont($font, $font_size);
		  	
		  	$sql2 = "SELECT eq_type_friendly_name FROM equipment_types
					WHERE eq_type_id='".$single_item->get_eq_type_id()."'
		  			";
		  	
		  	$eq_name_and_model = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql2);
		  	
		  	$page->drawText("$j", 40, ($i + (($u / 2) - ($font_size / 2))));
		  	$page->drawText($single_item->get_quantity(), 100, ($i + (($u / 2) - ($font_size / 2))));
		  	$page->drawText("".$eq_name_and_model['eq_type_friendly_name']."", 170, ($i + (($u / 2) - ($font_size / 2))));
		  	$page->drawText("$".$single_item->get_piece_cost()."", 600, ($i + (($u / 2) - ($font_size / 2))));
		  	$page->drawText("$".$total_cost_of_this_item."", 690, ($i + (($u / 2) - ($font_size / 2))));
		  	

		  	
		  }
		  //at this point $i is the bottom row of the items table dont reuse that var.
		  
		  //vertical lines
		  $page->drawLine(75, ($k - $h), 75, $i);
		  $page->drawLine(150, ($k - $h), 150, $i);
		  $page->drawLine(585, ($k - $h), 585, $i);
		  $page->drawLine(665, ($k - $h), 665, $i);
		  
		  //footer
		  $page->drawLine(15, ($i - 15), ($page->getWidth() - 15), ($i - 15));
		  
		  } elseif ($this->_numberofitems >= 10 ) {

		   	$page2 = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_LETTER_LANDSCAPE);
		   	$page2->setFont($font, $font_size);
		   	$b=$c=550;
		   	
		   	foreach ($this->_po_items as $single_item ) {
		   	
		   		$j++;

		  	$i-=($u +$h);
	 
		   	if ($j % 2) {
		   			 
		   			$page->setFillColor(Zend_Pdf_Color_Html::color('#CCFFCC'));
		   			$page2->setFillColor(Zend_Pdf_Color_Html::color('#CCFFCC'));
		   			 
		   		} else {
		   	
		   			$page->setFillColor(Zend_Pdf_Color_Html::color('#CCFFFF'));
		   			$page2->setFillColor(Zend_Pdf_Color_Html::color('#CCFFFF'));
		   	
		   		}
		   		 
		   		//generate subtotal
		   		$total_cost_of_this_item = "".$single_item->get_quantity() * $single_item->get_piece_cost()."";
		   		$subtotal += $total_cost_of_this_item;
		   		 
		   	
		   	if ($j < 13) {
		   		
		   		$page->drawRectangle(30, $i, ($page->getWidth() - 30), ($i + $u));
		   		$page->setFillColor(Zend_Pdf_Color_Html::color('#000000'));
		   		$page->setFont($font, $font_size);
		   		 
		   		$sql2 = "SELECT eq_type_friendly_name FROM equipment_types
		   						WHERE eq_type_id='".$single_item->get_eq_type_id()."'
		   			  			";
		   		 
		   		$eq_name_and_model = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql2);
		   		 
		   		$page->drawText("$j", 40, ($i + (($u / 2) - ($font_size / 2))));
		   		$page->drawText($single_item->get_quantity(), 100, ($i + (($u / 2) - ($font_size / 2))));
		   		$page->drawText("".$eq_name_and_model['eq_type_friendly_name']."", 170, ($i + (($u / 2) - ($font_size / 2))));
		   		$page->drawText("$".$single_item->get_piece_cost()."", 600, ($i + (($u / 2) - ($font_size / 2))));
		   		$page->drawText("$".$total_cost_of_this_item."", 690, ($i + (($u / 2) - ($font_size / 2))));
		   		 
		   	} 
		   	
		   	if ($j == 12) {
		   			
	   			//vertical lines
	   			$page->drawLine(75, ($k - $h), 75, $i);
	   			$page->drawLine(150, ($k - $h), 150, $i);
	   			$page->drawLine(585, ($k - $h), 585, $i);
	   			$page->drawLine(665, ($k - $h), 665, $i);
	   			
	   			//footer
	   			$page->drawLine(15, ($i - 15), ($page->getWidth() - 15), ($i -15));
	   			//page name
	   			$page->drawText("Page: 1", 40, ($i - 110));
	   			//new header
	   			$page2->drawLine(15, ($page2->getHeight() - 30), ($page2->getWidth() - 30), ($page2->getHeight() - 30));

		   	}
		   	
		   	if ($j >= 12) {
		   		
		   		$b-=($u +$h);
		   		
		   		$page2->drawRectangle(30, $b, ($page->getWidth() - 30), ($b + $u));
		   		$page2->setFillColor(Zend_Pdf_Color_Html::color('#000000'));
		   		$page2->setFont($font, $font_size);
		   		 
		   		$sql3 = "SELECT eq_type_friendly_name FROM equipment_types
		   						WHERE eq_type_id='".$single_item->get_eq_type_id()."'
		   			  			";
		   		 
		   		$eq_name_and_model = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql3);
		   		 
		   		$page2->drawText("$j", 40, ($b + (($u / 2) - ($font_size / 2))));
		   		$page2->drawText($single_item->get_quantity(), 100, ($b + (($u / 2) - ($font_size / 2))));
		   		$page2->drawText("".$eq_name_and_model['eq_type_friendly_name']."", 170, ($b + (($u / 2) - ($font_size / 2))));
		   		$page2->drawText("$".$single_item->get_piece_cost()."", 600, ($b + (($u / 2) - ($font_size / 2))));
		   		$page2->drawText("$".$total_cost_of_this_item."", 690, ($b + (($u / 2) - ($font_size / 2))));
	 
		   	}
		   	
		   	
		   	}
		   	
		   	if ($this->_numberofitems > 10) {
		   		
		   		//vertical lines
		   		$page2->drawLine(75, ($c - $h), 75, $b);
		   		$page2->drawLine(150, ($c - $h), 150, $b);
		   		$page2->drawLine(585, ($c - $h), 585, $b);
		   		$page2->drawLine(665, ($c - $h), 665, $b);
	
		   	}

		   }

		  //this is a mess
		  if ($this->_numberofitems < 10) {

		  $sql3 = "SELECT * from items WHERE item_id='".$this->_po_terms."'";
		  $po_terms_value = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql3);
		  
		  $sql4 = "SELECT * from items WHERE item_id='".$this->_po_freight."'";
		  $po_freight_value = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql4);

		  //Payment / freight
		  $page->drawText("Payment Method: ".$po_terms_value['item_value']."", 40, ($i - 35));
		  $page->drawText("Freight Method: ".$po_freight_value['item_value']."", 40, ($i - 70));
		  
		  $page->drawText("Signature: ", 40, ($i - 105));
		  $page->drawLine(100, ($i - 105), 300, ($i - 105));
		  $page->drawText("Date: ", 315, ($i - 105));
		  $page->drawLine(350, ($i - 105), 450, ($i -  105));
		  //page number
		  $page->drawText("Page: 1", 40, ($i - 140));
		  //Subtotal / tax/ ship / Total boxes
		  $page->setFillColor(Zend_Pdf_Color_Html::color('#CCFFCC'));
		  $page->drawRectangle(665, ($i - $u), 762, ($i - $u*2));
		  $page->setFillColor(Zend_Pdf_Color_Html::color('#CCFFFF'));
		  $page->drawRectangle(665, ($i - $u*2), 762, ($i - $u*3));
		  $page->setFillColor(Zend_Pdf_Color_Html::color('#CCFFCC'));
		  $page->drawRectangle(665, ($i - $u*3), 762, ($i - $u*4));
		  $page->setFillColor(Zend_Pdf_Color_Html::color('#CCFFFF'));
		  $page->drawRectangle(665, ($i - $u*4), 762, ($i - $u*5));
		  
		  //Subtotal / tax/ ship / Total description

		  //footer fields 
		  $total_font_size = 12;	//The font Size for items item
		  $page->setFillColor(Zend_Pdf_Color_Html::color('#000000'));
		  $page->setFont($font, $total_font_size);
		  $page->drawText("Sub Total: ", 585, (($i - $u*2) + (($u / 2) - ($total_font_size / 2))));
		  $page->drawText("Shipping: ", 585, (($i - $u*3) + (($u / 2) - ($total_font_size / 2))));
		  $page->drawText("Tax: ", 585, (($i - $u*4) + (($u / 2) - ($total_font_size / 2))));
		  $page->drawText("Total: ", 585, (($i -  $u*5) + (($u / 2) - ($total_font_size / 2))));
		  
		  //footer values
		  $page->drawText("$".$subtotal."", 690, (($i - $u*2) + (($u / 2) - ($total_font_size / 2))));
		  $page->drawText("$".$this->_shipping_cost."", 690, (($i - $u*3) + (($u / 2) - ($total_font_size / 2))));
		  $page->drawText("", 690, (($i - $u*4) + (($u / 2) - ($total_font_size / 2))));
		  $page->drawText("$".$subtotal."", 690, (($i -  $u*5) + (($u / 2) - ($total_font_size / 2))));
		 
		  // add page to document
		  $pdf->pages[] = $page;
		  //mess continued, this should be done more elegant
		  } elseif ($this->_numberofitems >= 10 ) {
		  	
		  	$page2->setFillColor(Zend_Pdf_Color_Html::color('#000000'));
		  	$page2->setFont($font, $font_size);
		  	
		  	$sql3 = "SELECT * from items WHERE item_id='".$this->_po_terms."'";
		  	$po_terms_value = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql3);
		  	
		  	$sql4 = "SELECT * from items WHERE item_id='".$this->_po_freight."'";
		  	$po_freight_value = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql4);
		  	
		  	//Payment / freight
		  	$page2->drawText("Payment Method: ".$po_terms_value['item_value']."", 40, ($b - 35));
		  	$page2->drawText("Freight Method: ".$po_freight_value['item_value']."", 40, ($b - 70));
		  	
		  	$page2->drawText("Signature: ", 40, ($b - 105));
		  	$page2->drawLine(100, ($b - 105), 300, ($b - 105));
		  	$page2->drawText("Date: ", 315, ($b - 105));
		  	$page2->drawLine(350, ($b - 105), 450, ($b -  105));
		  	//page number
		  	$page2->drawText("Page: 2", 40, ($b - 140));
		  	//Subtotal / tax/ ship / Total boxes
		  	$page2->setFillColor(Zend_Pdf_Color_Html::color('#CCFFCC'));
		  	$page2->drawRectangle(665, ($b - $u), 762, ($b - $u*2));
		  	$page2->setFillColor(Zend_Pdf_Color_Html::color('#CCFFFF'));
		  	$page2->drawRectangle(665, ($b - $u*2), 762, ($b - $u*3));
		  	$page2->setFillColor(Zend_Pdf_Color_Html::color('#CCFFCC'));
		  	$page2->drawRectangle(665, ($b - $u*3), 762, ($b - $u*4));
		  	$page2->setFillColor(Zend_Pdf_Color_Html::color('#CCFFFF'));
		  	$page2->drawRectangle(665, ($b - $u*4), 762, ($b - $u*5));
		  	
		  	//Subtotal / tax/ ship / Total description
		  	
		  	//footer fields
		  	$total_font_size = 12;	//The font Size for items item
		  	$page2->setFillColor(Zend_Pdf_Color_Html::color('#000000'));
		  	$page2->setFont($font, $total_font_size);
		  	$page2->drawText("Sub Total: ", 585, (($b - $u*2) + (($u / 2) - ($total_font_size / 2))));
		  	$page2->drawText("Shipping: ", 585, (($b - $u*3) + (($u / 2) - ($total_font_size / 2))));
		  	$page2->drawText("Tax: ", 585, (($b - $u*4) + (($u / 2) - ($total_font_size / 2))));
		  	$page2->drawText("Total: ", 585, (($b -  $u*5) + (($u / 2) - ($total_font_size / 2))));
		  	
		  	//footer values
		  	$page2->drawText("$".$subtotal."", 690, (($b - $u*2) + (($u / 2) - ($total_font_size / 2))));
		  	$page2->drawText("$".$this->_shipping_cost."", 690, (($b - $u*3) + (($u / 2) - ($total_font_size / 2))));
		  	$page2->drawText("$0", 690, (($b - $u*4) + (($u / 2) - ($total_font_size / 2))));
		  	$page2->drawText("$".$subtotal."", 690, (($b -  $u*5) + (($u / 2) - ($total_font_size / 2))));
		  		
		  	// add page to document
		  	$pdf->pages[] = $page;
		  	$pdf->pages[] = $page2;

		  }
		  
		  
		  
		  //where we want to save.
		  $filepath = APPLICATION_PATH."/../public/app_file_store/purchase_orders/".$this->get_po_number().".pdf";
		  // save as file
		  $pdf->save("$filepath");
		 
		} catch (Zend_Pdf_Exception $e) {
		  die ('PDF error: ' . $e->getMessage());
		} catch (Exception $e) {
		  die ('Application error: ' . $e->getMessage());
		}
		//lock the PO so it can never be altered and set the status to open. DONT lock first
		$sql = "SELECT item_id FROM items WHERE item_type='po_status' AND item_name='open'";
		
		$po_open_status 		= Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		$this->set_po_status($po_open_status);
		$this->_po_status = $po_open_status;
		
		$this->lock_po();
		$this->_po_lock = 1;
		
		return $filepath;

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