<?php

//include('../../config/auth/secure.php');
require_once( str_replace ( '\\', '/', dirname(dirname(__FILE__))) . '/class.declaration.php' );
$load_needed_class_and_interface = load_class_and_interface(array('Benevole'));


class Administrateur_Filiale extends Benevole {
	
	function __construct() { //receptionne soit id benevole general avec l'id de la filiale ou alors un id benevole de filiale
		$input_args = func_get_args();
		
		if (func_num_args() == 2) { //super_id.benevole + id.filiale
			$id_benevole = $input_args[0]; //au niveau BENEVOLE et non filiale !
			$id_filiale = $input_args[1];
			
			if (Administrateur_Filiale::id_exists($id_benevole, $id_filiale)) {
				parent::__construct($id_benevole);
			} else {
				die();
			}
		} elseif (func_num_args() == 1) { // id.filiale.benvole
			
			$id_benevole_filiale = $input_args[0]; // au niveau filiale !!
			
			if(Administrateur_Filiale::id_exists($id_benevole_filiale)) {
				parent::__construct(Benevole::get_super_id_benvole_from_id_benevole_filiale($id_benevole_filiale));
			} else {
				die();
			}
		}
	} // class.Administrateur_Filiale.func.__construct
	
	
	static function id_exists() { //controle si l'id est bien celui d'un ADMINISTRATEUR DE FILIALE
		
		$input_args = func_get_args();
		
		if (func_num_args() == 2) { //id.benevole + id.filiale
			$id_benevole = $input_args[0]; //au niveau benevole et non filiale !
			$id_filiale = $input_args[1];
			
			if (Benevole::id_exists($id_benevole) && Filiale::id_exists($id_filiale)) {
				global $dbh;
				
				$sql = "SELECT is_administrateur_filiale ";
				$sql .= "FROM benevole_participation_filiale ";
				$sql .= "WHERE id_benevole=" . $id_benevole . " ";
				$sql .= "AND id_filiale=" . $id_filiale;
				
				$sth = $dbh->query($sql);
				$result = $sth->fetch(PDO::FETCH_ASSOC);
				
				if ($result != false && $result['is_administrateur_filiale'] == 1) {
					return true;
				} else {
					return FALSE;
					exit();
				}
			} else {
				die();
			}
		} elseif (func_num_args() == 1) { // id.filiale.benvole
			
			$id_benevole = $input_args[0]; // au niveau filiale !!
			
			if (Benevole::id_benevole_filiale_exists($id_benevole)) {
				global $dbh;
				
				$sql = "SELECT is_administrateur_filiale ";
				$sql .= "FROM benevole_participation_filiale ";
				$sql .= "WHERE id=" . $id_benevole;
				
				$sth = $dbh->query($sql);
				$result = $sth->fetch(PDO::FETCH_ASSOC);
				
				if ($result =! false && $result['is_administrateur_filiale'] == 1) {
					return TRUE;
				} else {
					return FALSE;
					exit();
				}
			}
		} else {
			die();
		}
		
	} // class.Administrateur_Filiale.func.id_exists
	
} // class.Administrateur_Filiale

?>