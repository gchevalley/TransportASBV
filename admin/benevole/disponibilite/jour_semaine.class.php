<?php

//include('../../../config/auth/secure.php');
require_once( str_replace ( '\\', '/', dirname(dirname(dirname(__FILE__)))) . '/class.declaration.php' );

class Jour_Semaine {
	
	private $id=0;
	private $nom_long='';
	private $nom_court='';
	private $numero_jour=0;
	private $is_weekend=0;
	private $has_compensation=0;
	private $compensation=100;
	
	
	function __construct($id_jour_semaine) {
		
		if (checkID($id_jour_semaine) && $id_jour_semaine <= 7) {
			global $dbh;
			
			$sql = "SELECT * FROM jour_semaine WHERE id=" .$id_jour_semaine;
			$sth = $dbh->query($sql);
			
			$result = $sth->fetch(PDO::FETCH_ASSOC);
			
			if (count($result)>0) {
				$this->id = $result['id'];
				$this->nom_long = $result['nom_long'];
				$this->nom_court = $result['nom_court'];
				$this->numero_jour = $result['numero_jour'];
				$this->is_weekend = $result['is_weekend'];
				$this->has_compensation = $result['has_compensation'];
				$this->compensation = $result['compensation'];
				
			} else {
				return FALSE;
				exit();
			}
		} else {
			exit();
		}
		
	} // class.Jour_semaine.func.__construct
	
	
	/*
	private function mountAttributsFromDB() {
		
	}
	*/
	
	static function get_id_from_nom($nom) {
		global $dbh;
		
		if (!str_word_count($nom, 0) == 1) {
			return FALSE;
		}
		
		$sql = "SELECT id FROM jour_semaine WHERE nom_long='" .strtolower($nom) ."'";
		
		$sth = $dbh->query($sql);
			
		$result = $sth->fetch(PDO::FETCH_ASSOC);
			
		if ($result != false) {
			return $result['id'];
		} else {
			return FALSE;
		}
	}
	
	public function get_id() {
		return $this->id;
	}
	
	
	public function get_nom_long() {
		return $this->nom_long;
	}
	
	
	public function get_nom_court() {
		return $this->nom_court;
	}
	
	
	public function checkWeekend() {
		if ($this->is_weekend == 1) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	
	public function checkCompensation() {
		if ($this->has_compensation == 1) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	public function get_compensation() {
		return $this->compensation;
	}
	
	
	public static function id_exists($id_to_check) {
		if (checkID($id_to_check)) {
			global $dbh;
			$sql = "SELECT * FROM jour_semaine WHERE id=" .$id_to_check;
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
	} //class.Jour_Semaine.func.id_exists
	
	
}

?>