<?php


class thelist_utility_barcodes
{
	
	private $database;
	private $user_session;
	
	private $barcodeOptions;
	private $rendererOptions;
	private $barcode;
	
	function __construct()
	{
		//Zend_Registry::get('database') = new database();

		$this->user_session = new Zend_Session_Namespace('userinfo');
		
		
		
		 
	}
	
	public function render_barcode($barcode_text)
	{

		// Only the text to draw is required
		$this->barcodeOptions = array('text' => $barcode_text);
			
		// No required options
		$this->rendererOptions = array();
			
		// Draw the barcode in a new image,
		// send the headers and the image
		$this->barcode = Zend_Barcode::draw('code128', 'image', $this->barcodeOptions, $this->rendererOptions);
	
		$filename = tempnam('/zend/thelist/public/app_file_store/barcodes', 'barcode').'.png';
		imagepng($this->barcode, $filename);
	
		preg_match("/barcodes\/(.*)/", $filename, $matches);
	
		return $matches['1'];
	}
	
}