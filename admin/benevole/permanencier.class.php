<?php

//include('../../config/auth/secure.php');
require_once( str_replace ( '\\', '/', dirname(dirname(__FILE__))) . '/class.declaration.php' );
$load_needed_class_and_interface = load_class_and_interface(array('Benevole', 'Filiale', 'Jour_Semaine', 'Periode_Journee'));

class Permanencier extends Benevole {
	
	private $array_disponibilite_standard_permanence = array();
	
	
	function __construct() { //receptionne soit id benevole general avec l'id de la filiale ou alors un id benevole de filiale
	$input_args = func_get_args();
		
		if (func_num_args() == 2) { //super_id.benevole + id.filiale
			$id_benevole = $input_args[0]; //au niveau BENEVOLE et non filiale !
			$id_filiale = $input_args[1];
			
			if (Permanencier::id_exists($id_benevole, $id_filiale)) {
				parent::__construct($id_benevole);
			} else {
				die();
			}
		} elseif (func_num_args() == 1) { // id.filiale.benvole
			
			$id_benevole_filiale = $input_args[0]; // au niveau filiale !!
			
			if(Permanencier::id_exists($id_benevole_filiale)) {
				parent::__construct(Benevole::get_super_id_benvole_from_id_benevole_filiale($id_benevole_filiale));
			} else {
				die();
			}
		}
	} // class.Permanencier.func.__construct
	
	
	public function ajouterDisponibiliteStandard($id_categorie, $id_jour_semaine, $id_periode_journee, $id_filiale, $custom_heure_debut='', $custom_heure_fin='') {
		global $dbh;
		
		//control categorie
		if (is_string($id_categorie) && str_word_count($id_categorie, 0) == 1) {
			$id_categorie = Benevole_Disponibilite_Categorie::get_id_from_nom(strtolower($id_categorie));
			
			if ($id_categorie == FALSE) {
				return FALSE;
			}
		} elseif (is_numeric($id_categorie)) {
			if (checkID($id_categorie)) {
				//continue le processus en conservant la valeur
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
		
		
		//control jour semaine
		if (is_string($id_jour_semaine) && str_word_count($id_jour_semaine, 0) == 1) {
			$id_jour_semaine = Jour_Semaine::get_id_from_nom(strtolower($id_jour_semaine));
			
			if ($id_jour_semaine == FALSE) {
				return FALSE;
			}
		} elseif (is_numeric($id_jour_semaine)) {
			if (checkID($id_jour_semaine)) {
				//continue le processus en conservant la valeur
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
		
		//control periode journee
		if (is_string($id_periode_journee) && str_word_count($id_periode_journee, 0) == 1) {
			$id_periode_journee = Periode_Journee::get_id_from_nom(strtolower($id_periode_journee));
			
			if ($id_periode_journee == FALSE) {
				return FALSE;
			}
		} elseif (is_numeric($id_periode_journee)) {
			if (checkID($id_periode_journee)) {
				//continue le processus en conservant la valeur
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
		
		
		//control filiale
		if (is_string($id_filiale) && str_word_count($id_filiale, 0) == 1) {
			$id_filiale = Filiale::get_id_from_nom(ucfirst(strtolower($id_filiale)));
			
			if ($id_filiale == FALSE) {
				return FALSE;
			}
		} elseif (is_numeric($id_filiale)) {
			if (checkID($id_filiale)) {
				//continue le processus en conservant la valeur
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
		
		// s'assure que l'entree n'existe pas deja
		$sql = "SELECT * ";
		$sql .= " FROM benevole_disponibilite_standard ";
		$sql .= " WHERE id_categorie=" . $id_categorie;
		$sql .= " AND id_benevole=" . $this->get_id($id_filiale);
		$sql .= " AND id_jour_semaine=" . $id_jour_semaine;
		$sql .= " AND id_periode_journee=" . $id_periode_journee;
		
		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);
		
		if (count($result) > 0) {
			// l'entree est deja presente !
			return TRUE; 
		} else {
			$today_date = $dbh->quote(date('Y-m-d'));
			$today_time = $dbh->quote(date('H:i:s'));
			
			if (Benevole::id_benevole_filiale_exists($_SESSION['benevole']['id_benevole_filiale'])) {
				$insert_benevole_id = $_SESSION['benevole']['id_benevole_filiale'];
			} else {
				$insert_benevole_id = 1; //rsaint
			}
			
			if ($custom_heure_debut != '') {
			$custom_heure_debut = $dbh->quote($custom_heure_debut);
			} else {
				$custom_heure_debut = 'NULL';
			}
			
			if ($custom_heure_fin != '') {
				$custom_heure_fin = $dbh->quote($custom_heure_fin);
			} else {
				$custom_heure_fin = 'NULL';
			}
			
			
			$sql = "INSERT INTO benevole_disponibilite_standard ";
			$sql .= " (id_benevole, id_categorie, id_jour_semaine, id_periode_journee, custom_heure_debut, custom_heure_fin, insert_date, insert_time, insert_user) ";
			$sql .= " VALUES (" .  $this->get_id($id_filiale) . ", $id_categorie, $id_jour_semaine, $id_periode_journee, $custom_heure_debut, $custom_heure_fin, $today_date, $today_time, $insert_benevole_id)";
			
			$query_status = $dbh->exec($sql);
			return $query_status;
		}
		
		
	} // class.Permanencier.func.ajouterDisponibiliterStandard
		
		
	public function supprimerDisponibiliteStandard($id_categorie, $id_jour_semaine, $id_periode_journee, $id_filiale) {
		global $dbh;
		
		
		//control categorie
		if (is_string($id_categorie) && str_word_count($id_categorie, 0) == 1) {
			$id_categorie = Benevole_Disponibilite_Categorie::get_id_from_nom(strtolower($id_categorie));
			
			if ($id_categorie == FALSE) {
				return FALSE;
			}
		} elseif (is_numeric($id_categorie)) {
			if (checkID($id_categorie)) {
				//continue le processus en conservant la valeur
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
		
		
		//control jour semaine
		if (is_string($id_jour_semaine) && str_word_count($id_jour_semaine, 0) == 1) {
			$id_jour_semaine = Jour_Semaine::get_id_from_nom(strtolower($id_jour_semaine));
			
			if ($id_jour_semaine == FALSE) {
				return FALSE;
			}
		} elseif (is_numeric($id_jour_semaine)) {
			if (checkID($id_jour_semaine)) {
				//continue le processus en conservant la valeur
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
		
		//control periode journee
		if (is_string($id_periode_journee) && str_word_count($id_periode_journee, 0) == 1) {
			$id_periode_journee = Periode_Journee::get_id_from_nom(strtolower($id_periode_journee));
			
			if ($id_periode_journee == FALSE) {
				return FALSE;
			}
		} elseif (is_numeric($id_periode_journee)) {
			if (checkID($id_periode_journee)) {
				//continue le processus en conservant la valeur
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
		
		
		//control filiale
		if (is_string($id_filiale) && str_word_count($id_filiale, 0) == 1) {
			$id_filiale = Filiale::get_id_from_nom(ucfirst(strtolower($id_filiale)));
			
			if ($id_filiale == FALSE) {
				return FALSE;
			}
		} elseif (is_numeric($id_filiale)) {
			if (checkID($id_filiale)) {
				//continue le processus en conservant la valeur
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
		
		global $dbh;
		
		$sql = "DELETE FROM benevole_disponibilite_standard ";
		$sql .= " WHERE id_categorie=" . $id_categorie;
		$sql .= " AND id_benevole=" . $this->get_id($id_filiale);
		$sql .= " AND id_jour_semaine=" . $id_jour_semaine;
		$sql .= " AND id_periode_journee=" . $id_periode_journee;
		
		$query_status = $dbh->exec($sql);
		return $query_status;
		
	} // class.Permanencier.func.supprimerDisponibiliteStandard
		
		
	public function ajouterNonDisponibiliteDate($date_non_disponibilite, $id_categorie, $id_filiale) {
		global $dbh;
		
		//control categorie
		if (is_string($id_categorie) && str_word_count($id_categorie, 0) == 1) {
			$id_categorie = Benevole_Disponibilite_Categorie::get_id_from_nom(strtolower($id_categorie));
			
			if ($id_categorie == FALSE) {
				return FALSE;
			}
		} elseif (is_numeric($id_categorie)) {
			if (checkID($id_categorie)) {
				//continue le processus en conservant la valeur
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
		
		
		//control filiale
		if (is_string($id_filiale) && str_word_count($id_filiale, 0) == 1) {
			$id_filiale = Filiale::get_id_from_nom(ucfirst(strtolower($id_filiale)));
			
			if ($id_filiale == FALSE) {
				return FALSE;
			}
		} elseif (is_numeric($id_filiale)) {
			if (checkID($id_filiale)) {
				//continue le processus en conservant la valeur
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
		
		
		// s'assure que l'entree n'existe pas deja
		$sql = "SELECT * ";
		$sql .= " FROM benevole_non_disponibilite_date ";
		$sql .= " WHERE id_categorie=" . $id_categorie;
		$sql .= " AND id_benevole=" . $this->get_id($id_filiale);
		$sql .= " AND date_custom ='" . $date_non_disponibilite . "'";
		
		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);
		
		if (count($result) > 0) {
			// l'entree est deja presente !
			return TRUE; 
		} else {
			$today_date = $dbh->quote(date('Y-m-d'));
			$today_time = $dbh->quote(date('H:i:s'));
			
			if (checkID($_SESSION['benevole']['id_benevole_filiale'])) {
				$insert_benevole_id = $_SESSION['benevole']['id_benevole_filiale'];
			} else {
				$insert_benevole_id = 1; //rsaint
			}
			
			$date_non_disponibilite = $dbh->quote($date_non_disponibilite);
			
			$sql = "INSERT INTO benevole_non_disponibilite_date ";
			$sql .= " (id_benevole, id_categorie, date_custom, source, need_exportation, tag, remarque, insert_date, insert_time, insert_user) ";
			$sql .= " VALUES (" .  $this->get_id($id_filiale) . ", $id_categorie, $date_non_disponibilite, 'LOCAL', TRUE, '', '', $today_date, $today_time, $insert_benevole_id)";
			
			$query_status = $dbh->exec($sql);
			return $query_status;
		}
	} // class.Permanencier.func.ajouterNonDisponibiliteDate
		
		
	public function supprimerNonDisponibiliteDate($date_non_disponibilite, $id_categorie, $id_filiale) {
		//control categorie
		if (is_string($id_categorie) && str_word_count($id_categorie, 0) == 1) {
			$id_categorie = Benevole_Disponibilite_Categorie::get_id_from_nom(strtolower($id_categorie));
			
			if ($id_categorie == FALSE) {
				return FALSE;
			}
		} elseif (is_numeric($id_categorie)) {
			if (checkID($id_categorie)) {
				//continue le processus en conservant la valeur
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
		
		
		//control filiale
		if (is_string($id_filiale) && str_word_count($id_filiale, 0) == 1) {
			$id_filiale = Filiale::get_id_from_nom(ucfirst(strtolower($id_filiale)));
			
			if ($id_filiale == FALSE) {
				return FALSE;
			}
		} elseif (is_numeric($id_filiale)) {
			if (checkID($id_filiale)) {
				//continue le processus en conservant la valeur
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
		
		global $dbh;
		
		
		//control les droit de l'utilsateur qui demande la requete
		if (checkID($_SESSION['benevole']['id_benevole_filiale'])) {
			$user_permanencier = new Benevole($_SESSION['benevole']['id_benevole_filiale']);
			
			if ($user_permanencier->checkIsPermanencier($id_filiale) || $user_permanencier->checkIsSuperAdmin() || $user_permanencier->checkIsAdminOfFiliale($id_filiale)) {
				//les droits min. requis sont ok
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
		
		
		$date_non_disponibilite = $dbh->quote($date_non_disponibilite);
		
		$sql = "DELETE FROM benevole_non_disponibilite_date ";
		$sql .= " WHERE id_categorie=" . $id_categorie;
		$sql .= " AND id_benevole=" . $this->get_id($id_filiale);
		$sql .= " AND date_custom=" . $date_non_disponibilite;
		
		$query_status = $dbh->exec($sql);
		return $query_status;
		
	} // class.Permanencier.func.supprimerNonDisponibiliteDate
	
	
	private function mountDisponibiliteStandardPermanence() {
		
		if (is_numeric($_SESSION['filiale']['id']) && Filiale::id_exists($_SESSION['filiale']['id'])) {
			
			global $dbh;
			
			$sql = "SELECT benevole_disponibilite_standard.id_jour_semaine, benevole_disponibilite_standard.id_periode_journee " ;
			$sql.= " FROM benevole_disponibilite_standard INNER JOIN benevole_disponibilite_categorie ON benevole_disponibilite_standard.id_categorie = benevole_disponibilite_categorie.id ";
			$sql .= " WHERE benevole_disponibilite_standard.id_benevole = " . Benevole::get_id_benevole_filiale_from_super_id_benevole_and_id_filiale($this->get_id(), $_SESSION['filiale']['id']);
			$sql .= " AND benevole_disponibilite_categorie.nom = 'permanence'";
			
			$sth = $dbh->query($sql);
			
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);
			
			$i=0;
			$this->array_disponibilite_standard_permanence = array();
			foreach ($result as $row) {
				$tmp_jour_semaine = new Jour_Semaine($row[id_jour_semaine]);
				$tmp_jour_semaine_txt = $tmp_jour_semaine->get_nom_long();
				
				$tmp_periode_journee = new Periode_Journee($row[id_periode_journee]);
				$tmp_periode_journee_txt = $tmp_periode_journee->get_periode();
				
				$this->array_disponibilite_standard_permanence[$i]['jour'] = $tmp_jour_semaine_txt;
				$this->array_disponibilite_standard_permanence[$i]['periode'] = $tmp_periode_journee_txt;
				
				$i++;
			}
		}
	} // class.Permanencier.func.mountDisponibiliteStandardPermanence
	
	
	public function get_DisponibiliteStandardPermanence() {
		$this->mountDisponibiliteStandardPermanence();
		return $this->array_disponibilite_standard_permanence;
	} // class.Permanencier.func.get_DisponibiliteStandardAppel
	
	
	public function get_id_permanencier() {
		return Benevole::get_id_benevole_filiale_from_super_id_benevole_and_id_filiale($this->get_id(), $_SESSION['filiale']['id']);
	} // class.Permanencier.func.get_id_permanencier
	
	
	public function AjouterPermanence($date) {
		global $dbh;
		
		//s'assure que l'emplacement est dispo pour eviter les conflits avec les cles primaires
		$sql = "DELETE FROM permanence ";
		$sql .= " WHERE id_filiale=" . $_SESSION['filiale']['id'];
		$sql .= " AND date=" . $dbh->quote($date);
		
		$dbh->exec($sql);
		
		$sql = "INSERT INTO permanence (id_filiale, id_permanencier, date) ";
		$sql .= " VALUES(" . $_SESSION['filiale']['id'] . ", " . $this->get_id_permanencier() . ", " . $dbh->quote($date) . ")";
		
		return $dbh->exec($sql);
		
	} // class.Permanencier.func.AjouterPermanence
	
	
	
	static function id_exists() { //controle si l'id est bien celui d'un PERMANENCIER
		
		$input_args = func_get_args();
		
		if (func_num_args() == 2) { //id.benevole + id.filiale
			$id_benevole = $input_args[0]; //au niveau benevole et non filiale !
			$id_filiale = $input_args[1];
			
			if (Benevole::id_exists($id_benevole) && Filiale::id_exists($id_filiale)) {
				global $dbh;
				
				$sql = "SELECT is_permanencier ";
				$sql .= "FROM benevole_participation_filiale ";
				$sql .= "WHERE id_benevole=" . $id_benevole . " ";
				$sql .= "AND id_filiale=" . $id_filiale;
				
				$sth = $dbh->query($sql);
				$result = $sth->fetch(PDO::FETCH_ASSOC);
				
				if ($result != false && $result['is_permanencier'] == 1) {
					return TRUE;
				} else {
					return FALSE;
				}
			} else {
				return FALSE;
			}
		} elseif (func_num_args() == 1) { // id.filiale.benvole
			
			$id_benevole = $input_args[0]; // au niveau filiale !!
			
			if (Benevole::id_benevole_filiale_exists($id_benevole)) {
				global $dbh;
				
				$sql = "SELECT is_permanencier ";
				$sql .= "FROM benevole_participation_filiale ";
				$sql .= "WHERE id=" . $id_benevole;
				
				$sth = $dbh->query($sql);
				$result = $sth->fetch(PDO::FETCH_ASSOC);
				
				if ($result =! false && $result['is_permanencier'] == 1) {
					return TRUE;
				} else {
					return FALSE;
				}
			}
		} else {
			return FALSE;
		}
		
	} // class.Permanencier.func.id_exists
	
} // class.Permanencier

?>