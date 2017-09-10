<?php

require_once( str_replace ( '\\', '/', dirname(dirname(dirname(__FILE__)))) . '/class.declaration.php' );

class Transport_Type_Calcul_Distance {
	private $id = 0;
	private $nom = '';
	
	function __construct($id_transport_type_calcul_distance) {
		if (is_numeric($id_transport_type_calcul_distance) && Transport_Type_Calcul_Distance::id_exists($id_transport_type_calcul_distance)) {
			$this->id = $id_transport_type_calcul_distance;
			$this->mountAttributsFromDB();
		} else {
			// rien pour l'instant
		}
	} // class.Transport_Type_Calcul_Distance.func.__construct
	
	
	private function mountAttributsFromDB() {
		
		//charge les donnees direct depuis la DB
		global $dbh;
		
		//mount la totalite des donnees
		$sql = "SELECT * FROM transport_type_calcul_distance WHERE id=" .$this->id;
		
		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);
		
		$this->nom = $result['nom'];		
		
	} // class.Transport_Type_Calcul_Distance.func.mountAttributsFromDB
	
	
	public function get_id() {
		return $this->id;
	} // class.Transport_Type_Calcul_Distance.func.get_id
	
	public function get_nom() {
		return $this->nom;
	} // class.Transport_Type_Calcul_Distance.func.get_nom
	
	
	public static function get_id_from_nom($nom) {
		
		if (!str_word_count($nom, 0) == 1) {
			return FALSE;
		}
		
		global $dbh;
		
		$sql = "SELECT id FROM transport_type_calcul_distance WHERE nom='" . strtolower($nom) . "'";
		
		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);
		
		if ($result != false) {
			return $result['id'];
		} else {
			return FALSE;
		}
		
	} // class.Transport_Type_Calcul_Distance.func.get_id_from_nom
	
	
	public static function id_exists($id_to_check) {
		if (checkID($id_to_check)) {
			global $dbh;
			$sql = "SELECT * FROM transport_type_calcul_distance WHERE id=" .$id_to_check;
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
	} //class.Transport_Type_Calcul_Distance.func.id_exists
	
} // class.Transport_Type_Calcul_Distance

?>