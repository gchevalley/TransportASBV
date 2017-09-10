<?php

class Repondant_Categorie {
	private $id = 0;
	private $nom = '';
	private $auto_mount = 0;
	
	public function __construct($id_repondant_categorie, $nom='', $auto_mount=0) {
		
		if ( is_numeric($id_repondant_categorie) && Repondant_Categorie::id_exists($id_repondant_categorie)) {
			
			$this->id = $id_repondant_categorie;
			$this->mountAttributsFromDB();
		
		} else {
			
		}
	} // class.Repondant_Categorie.func.__construct($id_beneficiaire)
	
	
	private function mountAttributsFromDB() {
		
		//charge les donnees direct depuis la DB
		global $dbh;
		
		//mount la totalite des donnees
		$sql = "SELECT * FROM repondant_categorie WHERE id=" .$this->id;
		
		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);
		
		//s'assurer qu'un resultat est retourne bien alloue les donnees aux attributs de l'object
		$this->nom = $result['nom'];
		$this->auto_mount = $result['auto_mount'];
		
		
	} // class.Repondant_Categorie.func.mountAttributsFromDB
	
	
	public function get_id() {
		return $this->id;
	}
	
	
	public function get_nom() {
		return $this->nom;
	}
	
	
	public function get_auto_mount() {
		return $this->auto_mount;
	}
	
	public function is_auto_mount() {
		if ($this->auto_mount == 1 || $this->auto_mount === TRUE) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	
	public static function get_id_from_nom($nom) {
		global $dbh;
		
		$nom = $dbh->quote($nom);
		
		$sql = "SELECT id, nom FROM repondant_categorie ";
		$sql .= " WHERE nom=" . $nom;
		
		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);
		
		if ($result != false) {
			return $result['id'];
		} else {
			return FALSE;
		}
	} // class.Repondant_Categorie.func.get_id_from_nom
	
	
	public static function id_exists($id_to_check) {
		if (checkID($id_to_check)) {
			global $dbh;
			$sql = "SELECT * FROM repondant_categorie WHERE id=" .$id_to_check;
			$sth = $dbh->query($sql);
			$result = $sth->fetch(PDO::FETCH_ASSOC);
			
			if ($result != false) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			//bad id type
			return FALSE;
		}
	} //class.Repondant_Categorie.func.id_exists
	
}

?>