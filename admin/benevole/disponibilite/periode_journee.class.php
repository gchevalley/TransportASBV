<?php

//include('../../../config/auth/secure.php');
require_once( str_replace ( '\\', '/', dirname(dirname(dirname(__FILE__)))) . '/class.declaration.php' );

class Periode_Journee {
	
	private $id=0;
	private $periode='';
	private $heure_debut='';
	private $heure_fin='';
	private $has_compensation=0;
	private $compensation=100;
	
	
	function __construct($id_periode_journee) {
		
		if (is_numeric($id_periode_journee) && $id_periode_journee > 0 ) {
			global $dbh;
			
			$sql = "SELECT * FROM periode_journee WHERE id=" .$id_periode_journee;
			$sth = $dbh->query($sql);
			
			$result = $sth->fetch(PDO::FETCH_ASSOC);
			
			if ($result != 0) {
				$this->id = $result['id'];
				$this->periode = $result['periode'];
				$this->heure_debut = $result['heure_debut'];
				$this->heure_fin = $result['heure_fin'];
				$this->has_compensation = $result['has_compensation'];
				$this->compensation = $result['compensation'];
				
			} else {
				return FALSE;
				exit();
			}
		} else {
			die();
		}
		
	}
	
	static function get_id_from_nom($nom) {
		global $dbh;
		
		if (!str_word_count($nom, 0) == 1) {
			return FALSE;
		}
		
		$sql = "SELECT id FROM periode_journee WHERE periode='" .strtolower($nom) ."'";
		
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
	
	
	public function get_periode() {
		return $this->periode;
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
	
	public static function get_id_periode_from_time($time) {
		global $dbh;
		
		$time = $dbh->quote($time);
		
		$sql = "SELECT id FROM periode_journee WHERE ";
		$sql .= " heure_debut<=$time AND heure_fin>$time";
		
		$sth = $dbh->query($sql);
		
		$result = $sth->fetch(PDO::FETCH_ASSOC);
		
		if ($result != false) {
			return $result['id'];
		} else {
			return 1;
		}
		
	}
	
	public static function id_exists($id_to_check) {
		if (checkID($id_to_check)) {
			global $dbh;
			$sql = "SELECT * FROM periode_journee WHERE id=" .$id_to_check;
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
	} //class.Periode_Journee.func.id_exists
	
	
}

?>