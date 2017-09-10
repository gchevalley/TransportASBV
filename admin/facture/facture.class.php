<?php

require_once( str_replace ( '\\', '/', dirname(dirname(__FILE__))) . '/class.declaration.php' );
$load_needed_class_and_interface = load_class_and_interface(array('Filiale'));

//http://www.web-development-blog.com/archives/create-pdf-documents-online-with-tcpdf/
class Facture extends TCPDF {
	
	private $id_filiale = 0;
	private $filiale; //object filiale
	
	private $header;
	private $footer;
	
	public function __construct($id_filiale=0) {
		
		if ($id_filiale == 0) {
			$this->$id_filiale = $_SESSION['filiale']['id'];
		} else {
			$this->$id_filiale = $id_filiale;
		}
		
		
		$this->filiale = new Filiale($this->$id_filiale);
		
		$this->header = $this->filiale->get_facture_header();
		$this->footer = $this->filiale->get_facture_footer();
		
		parent::__construct();
	}
	
	
	public function Header() {
		$this->setJPEGQuality(90);
		$this->Image('img/asbv_logo_old.png', 20, 10, 0, 0, 'PNG', 'http://www.benevolat-vaud.ch/');
		$this->SetXY(20, 40);
		$this->SetFont(PDF_FONT_NAME_MAIN, '', 7);
		$this->MultiCell(30, 25, 'ASSOCIATION DES SERVICES BENEVOLES VAUDOIS ' . mb_strtoupper(stripAccents(str_replace('ASBV', '', $this->footer[0]))), false, 'C');
		
		if ($this->header != '') {
			$this->SetFont(PDF_FONT_NAME_MAIN, 'B', 8);
			$this->Cell(0, 10, $this->header, 0, false, 'C');
		}
		
	}
	
	
	public function Footer() {
		$this->SetY(-20);
		$this->SetFont(PDF_FONT_NAME_MAIN, 'B', 8);
		$this->Cell(0, 10, $this->footer[0], 0, false, 'L');
		
		$this->SetY(-22);
		$this->SetFont(PDF_FONT_NAME_MAIN, '', 8);
		$this->Cell(0, 21, $this->footer[1], 0, false, 'L');
		
	}
	
	
	public function CreateTextBox($textval, $x = 0, $y, $width = 0, $height = 10, $fontsize = 10, $fontstyle = '', $align = 'L') {
		$marginL = 20;
		$this->SetXY($x+$marginL, $y); // 20 = margin left
		$this->SetFont(PDF_FONT_NAME_MAIN, $fontstyle, $fontsize);
		$this->Cell($width, $height, $textval, 0, false, $align);
	}
}

?>