<?php

//include('../../../config/auth/secure.php');
require_once( str_replace ( '\\', '/', dirname(dirname(dirname(__FILE__)))) . '/class.declaration.php' );


class Benevole_Disponibilite_Categorie {
	private $id = 0;
	private $nom = '';
	private $description = '';
	
	function __construct($id_categorie) {
		
		if (Benevole_Disponibilite_Categorie::id_exists($id_categorie)) {
			global $dbh;
			
			$sql = "SELECT * FROM benevole_disponibilite_categorie WHERE id=" .$id_categorie;
			$sth = $dbh->query($sql);
			
			$result = $sth->fetch(PDO::FETCH_ASSOC);
			
			if (count($result)>0) {
				$this->id = $result['id'];
				$this->nom = $result['nom'];
				$this->description = $result['description'];
				
			} else {
				return FALSE;
				exit();
			}
		} else {
			exit();
		}
		
	} // class.Benevole_Disponibilite_Categorie.func.__construct
	
	public function get_id() {
		return $this->id;
	} // class.Benevole_Disponibilite_Categorie.func.get_id
	
	public function get_nom() {
		return $this->nom;
	} // class.Benevole_Disponibilite_Categorie.func.get_nom
	
	static function get_id_from_nom($nom) {
		global $dbh;
		
		if (!str_word_count($nom, 0) == 1) {
			return FALSE;
		}
		
		$sql = "SELECT id FROM benevole_disponibilite_categorie WHERE nom='" . strtolower($nom) . "'";
		
		$sth = $dbh->query($sql);
			
		$result = $sth->fetch(PDO::FETCH_ASSOC);
			
		if ($result != false) {
			return $result['id'];
		} else {
			return FALSE;
		}
	} // class.Benevole_Disponibilite_Categorie.func.get_id_from_nom
	
	public static function id_exists($id_to_check) {
		if (checkID($id_to_check)) {
			global $dbh;
			$sql = "SELECT * FROM benevole_disponibilite_categorie WHERE id=" .$id_to_check;
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
	} //class.Benevole_Disponibilite_Categorie.func.id_exists
	
	public static function get_all_nom_in_array() {
		$tmp_array = array();
		
		global $dbh;
		$sql = "SELECT nom FROM benevole_disponibilite_categorie";
		
		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);
		
		foreach($result as $row) {
			$tmp_array[] = $row['nom'];
		}
		
		return $tmp_array;
	}
}


?>