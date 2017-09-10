<?php

//include('../../config/auth/secure.php');
require_once( str_replace ( '\\', '/', dirname(dirname(dirname(__FILE__)))) . '/class.declaration.php' );
$load_needed_class_and_interface = load_class_and_interface(array('Benevole', 'Filiale'));

class Lieu_Categorie {
	private $id = 0;
	private $categorie = '';
	private $description = '';
	private $priorite = 0;
	
	private $array_lieu_from_categorie = array();
	
	function __construct($id_categorie, $categorie='', $description='', $priorite=0) {
		
		if (Lieu_Categorie::id_exists($id_categorie)) {	
			
			$this->id = $id_categorie;
			$this->mountAttributsFromDB();
		
		} else { //creation de la nouvelle entite si dispose des droits necessaire
			
			if (Benevole::id_exists($_SESSION['benevole']['id'])) {
				$tmp_benevole = new Benevole($_SESSION['benevole']['id']);
				
				if ($tmp_benevole->checkIsSuperAdmin()) {
					$this->addEntryDB($categorie, $description, $priorite);
				} else {
					if (Filiale::id_exists($_SESSION['filiale']['id'])) {
						if ($tmp_benevole->checkIsPermanencier($_SESSION['filiale']['id']) || $tmp_benevole->checkIsAdminOfFiliale($_SESSION['filiale']['id'])) {
							$this->addEntryDB($categorie, $description, $priorite);
						}
					}
				}
			}
		}
	} // class.Lieu_Categorie.func.__construct
	
	
	private function mountAttributsFromDB() {
		global $dbh;
		
		$sql = "SELECT * FROM lieu_categorie ";
		$sql .= "WHERE id=" . $this->id;
		
		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);
		
		$this->categorie = $result['categorie'];
		$this->description = $result['description'];
		$this->priorite = $result['priorite'];
		
		
	} // class.Lieu_Categorie.func.mountAttributsFromDB
	
	private function addEntryDB($categorie, $description='', $priorite=0) {
		global $dbh;
		
		$categorie = strtolower($categorie);
		
		//s'assure que unique
		if (Lieu_Categorie::get_id_from_categorie($categorie)) {
			//already found !
			exit;
		}
		
		$categorie = $dbh->quote($categorie);
		$description = $dbh->quote($description);
		
		if (!is_numeric($priorite)) {
			$priorite = 0;
		}
		
		$sql = "INSERT INTO lieu_categorie ";
		$sql .= " (categorie, description, priorite) ";
		$sql .= " VALUES ($categorie, $description, $priorite)";
		
		$result = $dbh->exec($sql);
		
		if ($result != 1) {
			//erreur
			return FALSE;
		} else {
			//successful
			$this->id = $dbh->lastInsertId();
			$this->mountAttributsFromDB();
			//return TRUE;
		}
		
	} // class.Lieu_Categorie.func.addEntryDB
	
	public static function get_id_from_categorie($categorie) {
		global $dbh;
		
		$categorie = strtolower($categorie);
		$categorie = $dbh->quote($categorie);
		
		$sql = "SELECT id FROM lieu_categorie ";
		$sql .= "WHERE categorie=" . $categorie;
		
		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);
		
		if ($result != false) {
			return $result['id'];
		} else {
			return FALSE;
		}
	} // class.Lieu_Categorie.func.get_id_from_categorie
	
	
	private function return_pair_key_value() {
		
		$tmp_array['id']['value']= $this->id;
		$tmp_array['categorie']['value']= $this->categorie;
		$tmp_array['description']['value'] = $this->description;
		$tmp_array['priorite']['value']= $this->priorite;
		
		return $tmp_array;
	} // class.Lieu_Categorie.func.return_pair_key_value
	
	
	public static function id_exists($id_to_check) {
		if (checkID($id_to_check)) {
			global $dbh;
			$sql = "SELECT * FROM lieu_categorie WHERE id=" .$id_to_check;
			$sth = $dbh->query($sql);
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);
			
			if (count($result) > 0) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			//bad id type
			return FALSE;
		}
	} //class.Lieu_Categorie.func.id_exists
	
	public function get_id() {
		return $this->id;
	}
	
	public function get_categorie() {
		return $this->categorie;
	}
	
	public function get_priorite() {
		return $this->priorite;
	}
} // class.Lieu_Categorie

?>