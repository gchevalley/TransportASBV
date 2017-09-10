<?php

require_once( str_replace ( '\\', '/', dirname(dirname(__FILE__))) . '/class.declaration.php' );
load_class_and_interface(array('Filiale'));

//http://www.web-development-blog.com/archives/create-pdf-documents-online-with-tcpdf/
class Listing extends TCPDF {
	
	private $id_filiale = 0;
	private $filiale; //object filiale
	
	private $type;
	
	private $header;
	private $footer;
	
	
	public function __construct($id_filiale=0, $type) {
		
		if ($id_filiale == 0) {
			$this->$id_filiale = $_SESSION['filiale']['id'];
		} else {
			$this->$id_filiale = $id_filiale;
		}
		
		$this->type = $type;
		
		$this->filiale = new Filiale($this->$id_filiale);
		
		
		parent::__construct();
	}
	
	
	public function Header() {
		$this->SetXY(20, 5);
		$this->SetFont(PDF_FONT_NAME_MAIN, '', 10);
		$this->Cell(0, 5, $this->filiale->get_nom(), 0, false, 'L');
		
		$this->SetXY(165, 5);
		$this->SetFont(PDF_FONT_NAME_MAIN, '', 8);
		$this->Cell(0, 5, 'Imprimé le ' . date('d.m.Y'), 0, false, 'L');
		
		
		$this->SetXY(20, 10);
		$this->SetFont(PDF_FONT_NAME_MAIN, '', 10);
		
		if (substr($this->type, -1) != 's') {
			$this->type = $this->type . 's';
		}
		$this->Cell(0, 5, 'Liste des ' . ucfirst($this->type), 0, false, 'L');
		
		$this->Line(10, 15, 205, 15);
		
		
	}
	
	
	public function Footer() {
		
	}
	
	
	public function CreateTextBox($textval, $x = 0, $y, $width = 0, $height = 10, $fontsize = 10, $fontstyle = '', $align = 'L') {
		$marginL = 20;
		$this->SetXY($x+$marginL, $y); // 20 = margin left
		$this->SetFont(PDF_FONT_NAME_MAIN, $fontstyle, $fontsize);
		$this->Cell($width, $height, $textval, 0, false, $align);
	}
}

?>