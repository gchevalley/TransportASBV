<?php

//include('../../config/auth/secure.php');
require_once( str_replace ( '\\', '/', dirname(dirname(__FILE__))) . '/class.declaration.php' );
$load_needed_class_and_interface = load_class_and_interface(array('Benevole', 'Jour_Semaine', 'Periode_Journee', 'Benevole_Disponibilite_Categorie', 'Filiale'));


class Transporteur extends Benevole {
	private $id_transporteur = 0;
	private $in_which_filiale = 0;
	private $do_transports_locaux = 0;
	private $do_transports_geneve = 0;
	private $do_transports_lausanne = 0;
	private $do_transports_holidays = 0;
	
	private $array_disponibilite_standard_transport = array(); //array d'object a definir
	private $array_disponibilite_standard_appel = array(); //array d'object a definir
	
	private $array_non_disponibilite_transport_date = array(); //array d'object a definir
	private $array_non_disponibilite_appel_date = array(); //array d'object a definir
	
	private $array_transport = array();
	private $array_future_transport = array();
	
	
	function __construct() { //receptionne soit id benevole general avec l'id de la filiale ou alors un id benevole de filiale
		
		$input_args = func_get_args();
		
		if (func_num_args() == 2) { //super_id.benevole + id.filiale
			$id_benevole = $input_args[0]; //au niveau BENEVOLE et non filiale !
			$id_filiale = $input_args[1];
			
			if (Transporteur::id_exists($id_benevole, $id_filiale)) {
				parent::__construct($id_benevole);
				$this->mountAttributsFromDB($id_filiale);
			} else {
				die();
			}
		} elseif (func_num_args() == 1) { // id.filiale.benvole
			
			$id_benevole_filiale = $input_args[0]; // au niveau filiale !!
			
			if(Transporteur::id_exists($id_benevole_filiale)) {
				parent::__construct(Benevole::get_super_id_benvole_from_id_benevole_filiale($id_benevole_filiale));
				$id_filiale = Benevole::get_id_filiale_from_id_benevole_filiale($id_benevole_filiale);
				$this->mountAttributsFromDB($id_filiale);
			} else {
				die();
			}
		}
		
	} // class.Transporteur.func.__construct
	
	
	
	private function mountAttributsFromDB($id_filiale) {
		
		global $dbh;
		$sql = "SELECT * FROM benevole_participation_filiale WHERE id_benevole=" . $this->get_id() . " AND id_filiale=$id_filiale";
		
		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);
		
		$this->id_transporteur = Benevole::get_id_benevole_filiale_from_super_id_benevole_and_id_filiale($this->get_id(), $id_filiale);
		$this->in_which_filiale = $id_filiale;
		$this->do_transports_locaux = $result['do_transports_locaux'];
		$this->do_transports_geneve = $result['do_transports_geneve'];
		$this->do_transports_lausanne = $result['do_transports_lausanne'];
		$this->do_transports_holidays = $result['do_transports_holidays'];
		
	} // class.Trasporteur.func.mountAttributsFromDB
	
	
	public function editerAttributsTransporteur($attr, $new_value) { //2 matrix ou 2 valeurs
		
		if (is_numeric($_SESSION['filiale']['id']) && is_numeric($_SESSION['benevole']['id']) && Benevole::id_exists($_SESSION['benevole']['id']) && Filiale::id_exists($_SESSION['filiale']['id'])) {
			global $dbh;
			
			$sql = "UPDATE benevole_participation_filiale ";
			
			if (is_array($attr) && is_array($new_value)) { //2 tableaux receptionnes
				$nbre_attribut = count($attr);
				$nbre_new_value = count($new_value);
				
				if ($nbre_attribut != $nbre_new_value) {
					return FALSE;
				}
				
				$sql .= "SET ";
				
				foreach ($attr as $index=>$attribut_to_edit)  {
					if (is_numeric($new_value[$index])) {
						if(preg_match('`[0-9]{7,10}`',$new_value[$index])) {
							//numero de tel
							$n_value = $dbh->quote($new_value[$index]);
						} else {
							$n_value = $new_value[$index];
						}
					} else {
						$n_value = $dbh->quote($new_value[$index]);
					}
					
					$sql .= "$attribut_to_edit=$n_value";
					
					if ($index < ($nbre_attribut-1)) {
						$sql .= ',';
					} else {
						$sql .= ' ';
					}
				}
				
			} elseif (!is_array($attr) && !is_array($new_value)) { //1 seule valeur $attr=>$new_value receptionnee
				if (!is_numeric($new_value)) {
					$new_value = $dbh->quote($new_value);
				}
				
				$sql .= "SET $attr=$new_value ";
			
			} else {
				die();
			}
			
			$sql .= "WHERE id=" . $this->get_id($_SESSION['filiale']['id']);
			$statut_query = $dbh->exec($sql);
			
			//recharge avec les nouvelles donnees
			$this->mountAttributsFromDB($this->in_which_filiale);
		}
		
	} // class.Transporteur.func.editerAttributsTransporteur
	
	
	private function mountDisponibiliteStandardTransport() {
		
		if (is_numeric($_SESSION['filiale']['id']) && Filiale::id_exists($_SESSION['filiale']['id'])) {
			
			global $dbh;
			
			$sql = "SELECT benevole_disponibilite_standard.id_jour_semaine, benevole_disponibilite_standard.id_periode_journee " ;
			$sql.= " FROM benevole_disponibilite_standard INNER JOIN benevole_disponibilite_categorie ON benevole_disponibilite_standard.id_categorie = benevole_disponibilite_categorie.id ";
			$sql .= " WHERE benevole_disponibilite_standard.id_benevole = " . Benevole::get_id_benevole_filiale_from_super_id_benevole_and_id_filiale($this->get_id(), $_SESSION['filiale']['id']);
			$sql .= " AND benevole_disponibilite_categorie.nom = 'transport'";
			
			$sth = $dbh->query($sql);
			
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);
			
			$i=0;
			$this->array_disponibilite_standard_transport = array();
			foreach ($result as $row) {
				$tmp_jour_semaine = new Jour_Semaine($row[id_jour_semaine]);
				$tmp_periode_journee = new Periode_Journee($row[id_periode_journee]);
				
				$this->array_disponibilite_standard_transport[$i]['jour'] = $tmp_jour_semaine;
				$this->array_disponibilite_standard_transport[$i]['periode'] = $tmp_periode_journee;
				
				$i++;
			}
		}
	} // class.Transporteur.func.mountDisponibiliteStandardTransport
	
	public function get_DisponibiliteStandardTransport() {
		$this->mountDisponibiliteStandardTransport();
		
		
		$i=0;
		foreach ($this->array_disponibilite_standard_transport as $row) {
			$array_disponibilite_standard_transport[$i]['jour'] = $row['jour']->get_nom_long();
			$array_disponibilite_standard_transport[$i]['periode'] = $row['periode']->get_periode();
			
			$i++;
		}
		
		return $array_disponibilite_standard_transport;
		
	} // class.Transporteur.func.get_DisponibiliteStandardTransport
	
	
	private function mountNonDisponibiliteStandardDateTransport() {
		
		if (is_numeric($_SESSION['filiale']['id']) && Filiale::id_exists($_SESSION['filiale']['id'])) {
			
			global $dbh;
			
			$sql = "SELECT benevole_non_disponibilite_date.date_custom " ;
			$sql.= " FROM benevole_non_disponibilite_date INNER JOIN benevole_disponibilite_categorie ON benevole_non_disponibilite_date.id_categorie = benevole_disponibilite_categorie.id ";
			$sql .= " WHERE benevole_non_disponibilite_date.id_benevole = " . Benevole::get_id_benevole_filiale_from_super_id_benevole_and_id_filiale($this->get_id(), $_SESSION['filiale']['id']);
			$sql .= " AND benevole_disponibilite_categorie.nom = 'transport'";
			
			$sth = $dbh->query($sql);
			
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);
			
			$i=0;
			$this->array_non_disponibilite_transport_date = array();
			foreach ($result as $row) {
				
				//$this->array_non_disponibilite_transport_date[$i]['date'] = $row['date_custom'];
				$this->array_non_disponibilite_transport_date[] = $row['date_custom'];
				
				$i++;
			}
		}
	} // class.Transporteur.func.mountNonDisponibiliteStandardDateTransport
	
	public function get_NonDisponibiliteStandardDateTransport() {
		$this->mountNonDisponibiliteStandardDateTransport();
		return $this->array_non_disponibilite_transport_date;
	} // class.Transporteur.func.get_NonDisponibiliteStandardDateTransport
	
	
	private function mountDisponibiliteStandardAppel() {
		
		if (is_numeric($_SESSION['filiale']['id']) && Filiale::id_exists($_SESSION['filiale']['id'])) {
			
			global $dbh;
			
			$sql = "SELECT benevole_disponibilite_standard.id_jour_semaine, benevole_disponibilite_standard.id_periode_journee " ;
			$sql.= " FROM benevole_disponibilite_standard INNER JOIN benevole_disponibilite_categorie ON benevole_disponibilite_standard.id_categorie = benevole_disponibilite_categorie.id ";
			$sql .= " WHERE benevole_disponibilite_standard.id_benevole = " . Benevole::get_id_benevole_filiale_from_super_id_benevole_and_id_filiale($this->get_id(), $_SESSION['filiale']['id']);
			$sql .= " AND benevole_disponibilite_categorie.nom = 'appel'";
			
			$sth = $dbh->query($sql);
			
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);
			
			$i=0;
			$this->array_disponibilite_standard_appel = array();
			foreach ($result as $row) {
				$tmp_jour_semaine = new Jour_Semaine($row[id_jour_semaine]);
				$tmp_periode_journee = new Periode_Journee($row[id_periode_journee]);
				
				$this->array_disponibilite_standard_appel[$i]['jour'] = $tmp_jour_semaine;
				$this->array_disponibilite_standard_appel[$i]['periode'] = $tmp_periode_journee;
				
				$i++;
			}
		}
	} // class.Transporteur.func.mountDisponibiliteStandardAppel
	
	public function get_DisponibiliteStandardAppel() {
		$this->mountDisponibiliteStandardAppel();
		
		$i=0;
		foreach ($this->array_disponibilite_standard_appel as $row) {
			$array_disponibilite_standard_appel[$i]['jour'] = $row['jour']->get_nom_long();
			$array_disponibilite_standard_appel[$i]['periode'] = $row['periode']->get_periode();
			
			$i++;
		}
		
		return $array_disponibilite_standard_appel;
	} // class.Transporteur.func.get_DisponibiliteStandardAppel
	
	
	private function mountNonDisponibiliteStandardDateAppel() {
		
		if (is_numeric($_SESSION['filiale']['id']) && Filiale::id_exists($_SESSION['filiale']['id'])) {
			
			global $dbh;
			
			$sql = "SELECT benevole_non_disponibilite_date.date_custom " ;
			$sql.= " FROM benevole_non_disponibilite_date INNER JOIN benevole_disponibilite_categorie ON benevole_non_disponibilite_date.id_categorie = benevole_disponibilite_categorie.id ";
			$sql .= " WHERE benevole_non_disponibilite_date.id_benevole = " . Benevole::get_id_benevole_filiale_from_super_id_benevole_and_id_filiale($this->get_id(), $_SESSION['filiale']['id']);
			$sql .= " AND benevole_disponibilite_categorie.nom = 'appel'";
			
			$sth = $dbh->query($sql);
			
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);
			
			$i=0;
			$this->array_non_disponibilite_appel_date = array();
			foreach ($result as $row) {
				
				//$this->array_non_disponibilite_appel_date[$i]['date'] = $row['date_custom'];
				$this->array_non_disponibilite_appel_date[] = $row['date_custom'];
				
				$i++;
			}
		}
	} // class.Transporteur.func.mountNonDisponibiliteStandardDateAppel
	
	public function get_NonDisponibiliteStandardDateAppel() {
		$this->mountNonDisponibiliteStandardDateAppel();
		return $this->array_non_disponibilite_appel_date;
	} // class.Transporteur.func.get_NonDisponibiliteStandardDateAppel
		
		
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
					return FALSE;;
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
					return FALSE;;
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
			
			
		} // class.Transporteur.func.ajouterDisponibiliterStandard
		
		
		public function supprimerDisponibiliteStandard($id_categorie, $id_jour_semaine, $id_periode_journee, $id_filiale) {
			global $dbh;
			
			
			//control categorie
			if (is_string($id_categorie) && str_word_count($id_categorie, 0) == 1) {
				$id_categorie = Benevole_Disponibilite_Categorie::get_id_from_nom(strtolower($id_categorie));
				
				if ($id_categorie == FALSE) {
					die();
				}
			} elseif (is_numeric($id_categorie)) {
				if (checkID($id_categorie)) {
					//continue le processus en conservant la valeur
				} else {
					die();
				}
			} else {
				die();
			}
			
			
			//control jour semaine
			if (is_string($id_jour_semaine) && str_word_count($id_jour_semaine, 0) == 1) {
				$id_jour_semaine = Jour_Semaine::get_id_from_nom(strtolower($id_jour_semaine));
				
				if ($id_jour_semaine == FALSE) {
					die();
				}
			} elseif (is_numeric($id_jour_semaine)) {
				if (checkID($id_jour_semaine)) {
					//continue le processus en conservant la valeur
				} else {
					die();
				}
			} else {
				die();
			}
			
			//control periode journee
			if (is_string($id_periode_journee) && str_word_count($id_periode_journee, 0) == 1) {
				$id_periode_journee = Periode_Journee::get_id_from_nom(strtolower($id_periode_journee));
				
				if ($id_periode_journee == FALSE) {
					die();
				}
			} elseif (is_numeric($id_periode_journee)) {
				if (checkID($id_periode_journee)) {
					//continue le processus en conservant la valeur
				} else {
					die();
				}
			} else {
				die();
			}
			
			
			//control filiale
			if (is_string($id_filiale) && str_word_count($id_filiale, 0) == 1) {
				$id_filiale = Filiale::get_id_from_nom(ucfirst(strtolower($id_filiale)));
				
				if ($id_filiale == FALSE) {
					die();
				}
			} elseif (is_numeric($id_filiale)) {
				if (checkID($id_filiale)) {
					//continue le processus en conservant la valeur
				} else {
					die();
				}
			} else {
				die();
			}
			
			global $dbh;
			
			$sql = "DELETE FROM benevole_disponibilite_standard ";
			$sql .= " WHERE id_categorie=" . $id_categorie;
			$sql .= " AND id_benevole=" . $this->get_id($id_filiale);
			$sql .= " AND id_jour_semaine=" . $id_jour_semaine;
			$sql .= " AND id_periode_journee=" . $id_periode_journee;
			
			$dbh->exec($sql);
			
		} // class.Transporteur.func.supprimerDisponibiliteStandard
		
		
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
		} // class.Transporteur.func.ajouterNonDisponibiliteDate
		
		
		public function supprimerNonDisponibiliteDate($date_non_disponibilite, $id_categorie, $id_filiale) {
			//control categorie
			if (is_string($id_categorie) && str_word_count($id_categorie, 0) == 1) {
				$id_categorie = Benevole_Disponibilite_Categorie::get_id_from_nom(strtolower($id_categorie));
				
				if ($id_categorie == FALSE) {
					die();
				}
			} elseif (is_numeric($id_categorie)) {
				if (checkID($id_categorie)) {
					//continue le processus en conservant la valeur
				} else {
					die();
				}
			} else {
				die();
			}
			
			
			//control filiale
			if (is_string($id_filiale) && str_word_count($id_filiale, 0) == 1) {
				$id_filiale = Filiale::get_id_from_nom(ucfirst(strtolower($id_filiale)));
				
				if ($id_filiale == FALSE) {
					die();
				}
			} elseif (is_numeric($id_filiale)) {
				if (checkID($id_filiale)) {
					//continue le processus en conservant la valeur
				} else {
					die();
				}
			} else {
				die();
			}
			
			global $dbh;
			
			
			/*
			//control les droit de l'utilsateur qui demande la requete
			if (checkID($_SESSION['benevole']['id_benevole_filiale'])) {
				$user_permanencier = new Benevole($_SESSION['benevole']['id_benevole_filiale']);
				
				if ($user_permanencier->checkIsPermanencier($id_filiale) || $user_permanencier->checkIsSuperAdmin() || $user_permanencier->checkIsAdminOfFiliale($id_filiale)) {
					//les droits min. requis sont ok
				} else {
					die();
				}
			} else {
				die();
			}
			*/
			
			$date_non_disponibilite = $dbh->quote($date_non_disponibilite);
			
			$sql = "DELETE FROM benevole_non_disponibilite_date ";
			$sql .= " WHERE id_categorie=" . $id_categorie;
			$sql .= " AND id_benevole=" . $this->get_id($id_filiale);
			$sql .= " AND date_custom=" . $date_non_disponibilite;
			
			$dbh->exec($sql);
			
		} // class.Transporteur.func.supprimerNonDisponibiliteDate
		
		
		public function ajouterContrainteBeneficiaire($id_beneficiaire) {
			if (is_numeric($id_beneficiaire) && is_numeric($_SESSION['filiale']['id']) && Beneficiaire::id_exists($id_beneficiaire) && Filiale::id_exists($_SESSION['filiale']['id'])) {
				// s'assure que unique
				global $dbh;
				
				$id_transporteur = $this->get_id_transporteur();
				
				$sql = "SELECT * FROM contrainte_transporteur_beneficiaire ";
				$sql .= " WHERE id_transporteur=" . $id_transporteur;
				$sql .= " AND id_beneficiaire=" . $id_beneficiaire;
				
				$sth = $dbh->query($sql);
				
				$result = $sth->fetch(PDO::FETCH_ASSOC);
				
				if ($result != false) {
					return TRUE; //la contrainte est deja presente
				} else {
					$sql = "INSERT INTO contrainte_transporteur_beneficiaire ";
					$sql .= " (id_transporteur, id_beneficiaire) ";
					$sql .= " VALUES ($id_transporteur, $id_beneficiaire) ";
					
					$query_status = $dbh->exec($sql);
					return $query_status;
				}
			}
		} // class.Transporteur.func.ajouterContrainteBeneficiaire
		
		
		public function supprimerContrainteBeneficiaire($id_beneficiaire) {
			if (is_numeric($id_beneficiaire) && is_numeric($_SESSION['filiale']['id']) && Beneficiaire::id_exists($id_beneficiaire) && Filiale::id_exists($_SESSION['filiale']['id'])) {
				// s'assure que unique
				global $dbh;
				
				$id_transporteur = $this->get_id_transporteur();
				
				$sql = "DELETE FROM contrainte_transporteur_beneficiaire ";
				$sql .= " WHERE id_transporteur=$id_transporteur";
				$sql .= " AND id_beneficiaire=$id_beneficiaire";
				
				$query_statut = $dbh->exec($sql);
			}
		} // class.Transporteur.func.supprimerContrainteBeneficiaire
		
		
		public function get_id_transporteur() {
			return $this->id_transporteur;
		}
		
		
		public function check_transports_locaux() {
			if ($this->do_transports_locaux == 1) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
		
		
		public function check_transports_geneve() {
			if ($this->do_transports_geneve == 1) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
		
		
		public function check_transports_lausanne() {
			if ($this->do_transports_lausanne == 1) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
		
		
		public function check_transports_vacances() {
			if ($this->do_transports_holidays == 1) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
		
		
		public static function get_transporteur_disponible_date_periode($date, $id_periode_journee=0, $is_geneve=0, $is_lausanne=0) {
			
			//conversion de la date en jour de semaine
			$id_jour_semaine = date('N', strtotime($date));
			
			
			if (isset($id_periode_journee) && $id_periode_journee instanceof Periode_Journee) {
				$id_periode_journee = $id_periode_journee->get_id();
			}
			
			global $dbh;
			$sql = "SELECT DISTINCT benevole_participation_filiale.id "; //distinct est necessaire si on ne passe pas en argument la periode de la journée mais uniquement le jour de la semaine, pour chaque benevole il risque d'y avoir chaque fois jusque à 3x fois le meme id (matin/midi/soir)
			$sql .= " FROM benevole_participation_filiale INNER JOIN benevole_disponibilite_standard ON benevole_participation_filiale.id = benevole_disponibilite_standard.id_benevole ";
			$sql .= " WHERE benevole_participation_filiale.id_filiale=" . $_SESSION['filiale']['id'];
			$sql .= " AND benevole_disponibilite_standard.id_categorie=" . Benevole_Disponibilite_Categorie::get_id_from_nom('transport');
			$sql .= " AND benevole_disponibilite_standard.id_jour_semaine=$id_jour_semaine";
			
			if ($id_periode_journee != 0) {
				$sql .= " AND benevole_disponibilite_standard.id_periode_journee=$id_periode_journee";
			}
			
			if ($is_lausanne == 1 || $is_lausanne === TRUE) {
				$sql .= " AND benevole_participation_filiale.do_transports_lausanne = 1";
			}
			
			if ($is_geneve == 1 || $is_geneve === TRUE) {
				$sql .= " AND benevole_participation_filiale.do_transports_geneve = 1";
			}
			
			$sth = $dbh->query($sql);
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);
			
			$list_transporteur = array();
			
			if (count($result) > 0) {
				foreach ($result as $row) {
					$tmp_transporteur = new Transporteur($row['id']);
					
					if ($tmp_transporteur->check_disponibilite_date(Benevole_Disponibilite_Categorie::get_id_from_nom('transport'), $date) && !$tmp_transporteur->check_a_deja_un_transport($date)) {
						$list_transporteur[] = $tmp_transporteur;
					}
					
				}
				
				return $list_transporteur;
			} else {
				return FALSE;
			}
		} // class.Transporteur.func.get_transporteur_disponible_date_periode
		
		
		public function check_est_a_la_permanence($date, $periode_journee='') {
			if (!$this->checkIsPermanencier($_SESSION['filiale']['id'])) {
				return FALSE;
			} else {
				
				if ($periode_journee != '') {
					if ($periode_journee instanceof Periode_Journee) {
						$periode_journee = $periode_journee->get_id();
					} elseif (is_string($periode_journee)) {
						$periode_journee = Periode_Journee::get_id_from_nom($periode_journee);
					} elseif (is_numeric($periode_journee) && Periode_Journee::id_exists($periode_journee)) {
						
					} else {
						die();
					}
				}
				
				global $dbh;
				
				$sql = "SELECT permanence.* ";
				$sql .= " FROM permanence ";
				$sql .= " WHERE id_filiale=" . $_SESSION['filiale']['id'];
				$sql .= " AND date=" . $dbh->quote($date);
				$sql .= " AND id_permanencier=" . $this->get_id_transporteur();
				
				$sth = $dbh->query($sql);
				$result = $sth->fetch(PDO::FETCH_ASSOC);
				
				if ($result === FALSE) {
					return FALSE;
				} else {
					if ($periode_journee == '') {
						return TRUE;
					} else {
						if (mb_strtolower($periode_journee->get_periode) == 'matin') {
							return TRUE;
						} else {
							return FALSE;
						}
					}
				}
			}
		}
		
		public function check_disponibilite_date($id_categorie, $date) {
			
			if (is_numeric($id_categorie) && Benevole_Disponibilite_Categorie::id_exists($id_categorie)) {
				
			} elseif ($id_categorie instanceof Benevole_Disponibilite_Categorie) {
				$id_categorie = $id_categorie->get_id();
			} elseif (is_string($id_categorie)) {
				$tmp_categorie = new Benevole_Disponibilite_Categorie(Benevole_Disponibilite_Categorie::get_id_from_nom($id_categorie));
				$id_categorie = $tmp_categorie->get_id();
			}
			
			global $dbh;
			
			$sql = "SELECT benevole_non_disponibilite_date.* ";
			$sql .= " FROM benevole_non_disponibilite_date ";
			$sql .= " WHERE id_benevole=" . $this->get_id_transporteur();
			$sql .= " AND id_categorie=" . $id_categorie;
			$sql .= " AND date_custom=" . $dbh->quote($date);
			
			$sth = $dbh->query($sql);
			$result = $sth->fetch(PDO::FETCH_ASSOC);
			
			if ($result != FALSE) {
				return FALSE; //attention si resultat retourner, transporteur non disponible
			} else {
				return TRUE;
			}
			
		} // class.Transporteur.func.check_disponibilite_date
		
		
		public function check_a_deja_un_transport($date, $periode_journee='') {
			
			
			if ($periode_journee != '') {
				if ($periode_journee instanceof Periode_Journee) {
					$periode_journee = $periode_journee->get_id();
				} elseif (is_string($periode_journee)) {
					$periode_journee = Periode_Journee::get_id_from_nom($periode_journee);
				} elseif (is_numeric($periode_journee) && Periode_Journee::id_exists($periode_journee)) {
					
				} else {
					die();
				}
			}
			
			global $dbh;
			
			$sql = "SELECT transport_transporteur.*, transport.* ";
			$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport ";
			$sql .= " WHERE transport.date_transport=" . $dbh->quote($date);
			$sql .= " AND is_annule=0";
			$sql .= " AND transport_transporteur.id_transporteur=" . $this->get_id_transporteur();
			
			$sth = $dbh->query($sql);
			$result = $sth->fetch(PDO::FETCH_ASSOC);
			
			if ($result === false) {
				return FALSE;
			} else {
				if ($periode_journee == '') {
					return TRUE;
				} else {
					if($periode_journee  != Periode_Journee::get_id_periode_from_time($result['heure_debut'])) {
						return  FALSE;
					} else {
						return TRUE;
					}
				}
			}
		}
		
		public function check_disponibilite_standard($id_categorie, $id_jour_semaine, $id_periode_journee) {
			
			if (is_numeric($id_categorie) && Benevole_Disponibilite_Categorie::id_exists($id_categorie)) {
				
			} elseif ($id_categorie instanceof Benevole_Disponibilite_Categorie) {
				$id_categorie = $id_categorie->get_id();
			} elseif (is_string($id_categorie)) {
				$tmp_categorie = new Benevole_Disponibilite_Categorie(Benevole_Disponibilite_Categorie::get_id_from_nom($id_categorie));
				$id_categorie = $tmp_categorie->get_id();
			}
			
			
			if (is_numeric($id_jour_semaine) && Jour_Semaine::id_exists($id_jour_semaine)) {
				//cas ideal...
			} elseif ($id_jour_semaine instanceof Jour_Semaine) {
				$id_jour_semaine = $id_jour_semaine->get_id();
			} elseif (is_string($id_jour_semaine)) {
				$tmp_jour_semaine = new Jour_Semaine(Jour_Semaine::get_id_from_nom($id_jour_semaine));
				$id_jour_semaine = $tmp_jour_semaine->get_id();
			}
			
			
			if (is_numeric($id_periode_journee) && Periode_Journee::id_exists($id_periode_journee)) {
				
			} elseif ($id_periode_journee instanceof Periode_Journee) {
				$tmp_periode_journee = new Periode_Journee($id_periode_journee);
				$id_periode_journee = $tmp_periode_journee->get_id();
			}
			
			
			global $dbh;
			
			$sql = "SELECT benevole_disponibilite_standard.* ";
			$sql .= " FROM benevole_disponibilite_standard ";
			$sql .= " WHERE id_benevole=" . $this->get_id_transporteur();
			$sql .= " AND id_categorie=" . $id_categorie;
			$sql .= " AND id_jour_semaine=" . $id_jour_semaine;
			$sql .= " AND id_periode_journee=" . $id_periode_journee;
			
			$sth = $dbh->query($sql);
			$result = $sth->fetch(PDO::FETCH_ASSOC);
			
			if ($result != FALSE) {
				return TRUE;
			} else {
				return FALSE;
			}
			
		} //class.Transporteur.func.check_disponibilite_standard
		
		
		public function check_contrainte_beneficiaire($id_beneficiaire) {
			
			if (is_numeric($id_beneficiaire) && Beneficiaire::id_exists($id_beneficiaire)) {
				//ok
			} elseif ($id_beneficiaire instanceof Beneficiaire) {
				$id_beneficiaire = $id_beneficiaire->get_id();
			} else {
				die();
			}
			
			global $dbh;
			$sql = "SELECT contrainte_transporteur_beneficiaire.* ";
			$sql .= " FROM contrainte_transporteur_beneficiaire ";
			$sql .= " WHERE id_transporteur=" . $this->get_id_transporteur();
			$sql .= " AND id_beneficiaire=" . $id_beneficiaire;
			
			$sth = $dbh->query($sql);
			$result = $sth->fetch(PDO::FETCH_ASSOC);
			
			if ($result != false) {
				return TRUE;
			} else {
				return FALSE;
			}
			
		}
		
		
		public function check_est_sur_la_route($date='') {
			
			if ($date == '' || !is_date($date)) {
				$date = date('Y-m-d');
			}
			
			global $dbh;
			
			$sql = "SELECT transport.* ";
			$sql .= " FROM transport_transporteur INNER JOIN transport ON transport_transporteur.id_transport=transport.id ";
			$sql .= " WHERE transport.id_filiale=" . $_SESSION['filiale']['id'];
			$sql .= " AND transport.is_annule=0";
			$sql .= " AND transport.date_transport=" . $dbh->quote($date);
			$sql .= " AND transport_transporteur.id_transporteur=" . $this->get_id_transporteur();
			
			
			$sth = $dbh->query($sql);
			$result = $sth->fetch(PDO::FETCH_ASSOC);
			
			if ($result != false) {
				$heure_rdv = $result['heure_debut'];
				$duree = $result['duree_approximative'];
				
				$marge_mm_before_rdv = 30;
				$marge_mm_after_rdv = 60;
				
				
				$hh_rdv_from = date('G', strtotime($heure_rdv));
				$mm_rdv_from = (int) date('i', strtotime($heure_rdv));
				
				if (is_numeric($duree) && $duree > 0) {
					if ($duree < 1) {
						$hh_rdv_to = $hh_rdv_from;
						$mm_rdv_to = $mm_rdv_from + ($duree * 60);
					} else {
						$hh_rdv_to = $hh_rdv_from + ((int) $duree / 1); //division entiere
						$mm_rdv_to = $mm_rdv_from + (($duree % 1)*60); //modulo
					}
				}
				
				$time_before_rdv = date('Y-m-d H:i', mktime($hh_rdv_from, $mm_rdv_from - $marge_mm_before_rdv, 0, date('m'), date('d'), date('Y')));
				$time_after_rdv = date('Y-m-d H:i', mktime($hh_rdv_to, $mm_rdv_to + $marge_mm_after_rdv, 0, date('m'), date('d'), date('Y')));
				
				$time_now = date('Y-m-d H:i');
				
				if ($time_before_rdv <= $time_now && $time_now <= $time_after_rdv ) {
					return TRUE;
				} else {
					return FALSE;
				}
				
			} else {
				return FALSE;
			}
		}
		
		
		public function get_nbre_transports_between_2_dates($date_from, $date_to) {
			global $dbh;
			
			$sql = "SELECT COUNT(transport_transporteur.id_transport) AS nbre_transport ";
			$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport ";
			$sql .= " WHERE transport_transporteur.id_transporteur=" . $this->get_id_transporteur();
			$sql .= " AND transport.is_annule=0";
			$sql .= " AND transport.date_transport BETWEEN '" . $date_from . "'";
			$sql .= " AND '" . $date_to . "'";
			
			$sth = $dbh->query($sql);
			$result = $sth->fetch(PDO::FETCH_ASSOC);
			
			return $result['nbre_transport'];
		} // class.Transporteur.function.get_nbre_transports_between_2_dates
		
		
		public static function nbre_transporteurs_dispo_date($date) {
			
			//monte la liste des transporteurs dispo en générla pour ce jour de semaine
			
			
			//filtre pour ceux qui ont deja un transport
			
		} // class.Transporteur.function.nbre_transporteurs_dispo_date
		
		
		public static function wash_non_dispo_date() {
			global $dbh;
			
			$date_plancher = date('Y-m-d', mktime(0,0,0, date('m'), 1, date('Y')));
			
			$sql = "DELETE FROM benevole_non_disponibilite_date ";
			$sql .= " WHERE date_custom < " . $dbh->quote($date_plancher);
			
			$dbh->exec($sql);
		}
		
		
		public static function id_exists() { //controle si l'id est bien celui d'un ADMINISTRATEUR DE FILIALE
		
		$input_args = func_get_args();
		
		if (func_num_args() == 2) { //id.benevole + id.filiale
			$id_benevole = $input_args[0]; //au niveau benevole et non filiale !
			$id_filiale = $input_args[1];
			
			if (Benevole::id_exists($id_benevole) && Filiale::id_exists($id_filiale)) {
				global $dbh;
				
				$sql = "SELECT is_transporteur ";
				$sql .= "FROM benevole_participation_filiale ";
				$sql .= "WHERE id_benevole=" . $id_benevole . " ";
				$sql .= "AND id_filiale=" . $id_filiale;
				
				$sth = $dbh->query($sql);
				$result = $sth->fetch(PDO::FETCH_ASSOC);
				
				if ($result != false && $result['is_transporteur'] == 1) {
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
				
				$sql = "SELECT is_transporteur ";
				$sql .= "FROM benevole_participation_filiale ";
				$sql .= "WHERE id=" . $id_benevole;
				
				$sth = $dbh->query($sql);
				$result = $sth->fetch(PDO::FETCH_ASSOC);
				
				if ($result =! false && $result['is_transporteur'] == 1) {
					return TRUE;
				} else {
					return True;
					return FALSE;
					exit();
				}
			}
		} else {
			die();
		}
		
	} // class.Transporteur.func.id_exists
	
	public static function form($action, $data_to_display='') {
		
		switch ($action) {
			case "add":
				echo Benevole::form_base($action, $data_to_display);
				break;
			case "view":
				//s'assure que le beneficiaire est connu sinon charge une listbox de selection
				if (isset($data_to_display['id']['value']) && Benevole::id_exists(Benevole::get_super_id_benvole_from_id_benevole_filiale($_GET['id']))) {
					echo Benevole::form_view($action, $data_to_display);
				} else {
					echo Benevole::form_choose($action, $data_to_display);
				}
				break;
			case "edit":
				//s'assure que le beneficiaire est connu sinon charge une listbox de selection
				if (isset($data_to_display['id']['value']) && Benevole::id_exists(Benevole::get_super_id_benvole_from_id_benevole_filiale($data_to_display['id']['value']))) {
					echo Benevole::form_base($action, $data_to_display);
				} else {
					echo Benevole::form_choose($action, $data_to_display);
				}
				break;
			case "list":
				echo Benevole::form_list($action);
				break;
			case "find_transporteur_dispo":
				echo Transporteur::form_find_available_drivers($action, $data_to_display);
				break;
			case "city_near":
				echo Transporteur::form_city_near($action);
				break;
			case "transports_potentiels":
				echo Transporteur::form_transports_potentiels($action, $data_to_display);
				break;
			default:
				echo Benevole::form_list($action);
				break;
		}
			
	} // class.Transporteur.func.form
	
	
	private static function form_find_available_drivers($action, $data_to_display='') {
		if ($data_to_display == '') {
			$data_to_display = array();
		}
		
		global $dbh;
		$html_code = '';
		
		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}
		
		$html_code .= '<form action="" method="post">';
			//date transport
			$html_code .= '<p>';
				$html_code .= '<label for="date_transport">Date</label>';
				if (isset($data_to_display['date_transport']) && is_date($data_to_display['date_transport']['value'])) {
					$html_code .= add_FormElement_input('text', 'date_transport', array('date_picker_from_now', 'disableAutoComplete'), date('d.m.Y', strtotime($data_to_display['date_transport']['value'])));
				} else {
					$html_code .= add_FormElement_input('text', 'date_transport', array('date_picker_from_now', 'disableAutoComplete'), $data_to_display['date_transport']['value']);
				}
			$html_code .= '</p>';
			
			$html_code .= '<p>';
				$sql = "SELECT * FROM periode_journee";
				$sth = $dbh->query($sql);
				$result = $sth->fetchAll(PDO::FETCH_ASSOC);
				
				//id_periode
				$html_code .= '<label for="periode_transport">Période de la journée</label>';
				$html_code .= '<select id="periode_transport" name="periode_transport">';
					foreach($result as $row) {
						$html_code .= '<option value="' . $row['id'] . '" ';
							
							if ($row['id'] == $data_to_display['periode_transport']['value']) {
								$html_code .= 'selected="selected">';
							} else {
								$html_code .= '>';
							}
							
							$html_code .= $row['periode'];
						$html_code .= '</option>';
					}
				$html_code .= '</select>';
			$html_code .= '</p>';
			
			$html_code .= '<p>';
				$html_code .= '<label for="check_geneve">Genève</label>';
				$html_code .= add_FormElement_input('checkbox', 'is_geneve', '', $data_to_display['is_geneve']['value']);
			$html_code .= '</p>';
			
			$html_code .= '<p>';
				$html_code .= '<label for="check_lausanne">Lausanne</label>';
				$html_code .= add_FormElement_input('checkbox', 'is_lausanne', '', $data_to_display['is_lausanne']['value']);
			$html_code .= '</p>';
			
			
			
			$html_code .= '<p>';
				$html_code .= add_FormElement_input('hidden', 'form', '', 'choose_date');
				$html_code .= add_FormElement_input('hidden', 'module', '', 'transporteur');
				$html_code .= add_FormElement_input('hidden', 'sub_module', '', '""');
				$html_code .= add_FormElement_input('hidden', 'action', '', $action);
				
				$html_code .= '<input type="submit" value="Soumettre" />';
			$html_code .= '</p>';
			
		$html_code .= '</form>';
		
			
		if (isset($_POST['date_transport']) && $_POST['date_transport'] != '' && isset($_POST['periode_transport']) && Periode_Journee::id_exists($_POST['periode_transport'])) {
			//nettoyage des variables
			
			if (strpos($data_to_display['date_transport']['value'], '.') !== false) {
				$date_txt = explode('.', $data_to_display['date_transport']['value']);
				$date_transport = $date_txt[2] . '-' . $date_txt[1] . '-' . $date_txt[0];
			}
			
			
			if (isset($data_to_display['is_geneve']['value']) && $data_to_display['is_geneve']['value'] == 1) {
				$is_geneve = 1;
			} else {
				$is_geneve = 0;
			}
			
			if (isset($data_to_display['is_lausanne']['value']) && $data_to_display['is_lausanne']['value'] == 1) {
				$is_lausanne = 1;
			} else {
				$is_lausanne = 0;
			}
			
			
			//charge la liste des benevoles dispo
			$load_needed_class_and_interface = load_class_and_interface(array('GLM'));
			$tmp_glm = new GLM('', $date_transport, $_POST['periode_transport'], $is_geneve, $is_lausanne );
			$list_potentiel_transporteur = $tmp_glm->get_chauffeurs_potentiels();
			
			if (count($list_potentiel_transporteur) > 0) {
				
				
				//montre sur la map la position de tous les benevoles (disposant geocode)
				if (checkInternetConnection()) {
					
					$sql = "SELECT geocode.lat, geocode.lng, benevole.*, benevole_participation_filiale.*";
					$sql .= " FROM geocode, benevole_participation_filiale, benevole ";
					$sql .= " WHERE benevole.adresse = geocode.adresse ";
					$sql .= " AND benevole.npa = geocode.npa ";
					$sql .= " AND benevole.ville = geocode.ville ";
					$sql .= " AND benevole_participation_filiale.id_benevole = benevole.id";
					
					
					$sth = $dbh->query($sql);
					$benevole_geocode = $sth->fetchAll(PDO::FETCH_ASSOC);
					
					$html_code .= '<div id="map_benevoles" class="map_google"></div>';
					
					$html_code .= '<script type="text/javascript">';
						$html_code .= '$(document).ready(function() {';
							$html_code .= '$(\'#map_benevoles\').initGoogleMap();';
						
							foreach ($benevole_geocode as $row) {
								
								if ($row['is_transporteur'] == 1) {
									$type_transport = '';
									
									if ($row['do_transports_locaux'] == 1) {
										$type_transport .= '<strong>Locaux</strong>|';
									} else {
										$type_transport .= '-|';
									}
									
									if ($row['do_transports_geneve'] == 1) {
										$type_transport .= '<strong>Genève</strong>|';
									} else {
										$type_transport .= '--|';
									}
									
									if ($row['do_transports_lausanne'] == 1) {
										$type_transport .= '<strong>Lausanne</strong>|';
									} else {
										$type_transport .= '--|';
									}
									
									if ($row['do_transports_holidays'] == 1) {
										$type_transport .= '<strong>Vacances</strong>|';
									} else {
										$type_transport .= '-|';
									}
		
								} else {
									$type_transport ='';
								}
								
								
								//graph uniquement si doit etre liste
								foreach ($list_potentiel_transporteur as $transporteur) {
									$id_transporteur = $transporteur->get_id();
									if ($id_transporteur == $row['id']) {
										$html_code .= 'addMarkerWithGeocode(' . $row['lat'] . ',' . $row['lng'] . ',"' . $row['titre'] . ' ' . $row['nom'] . '<br /> ' . format_tel($row['tel_fixe']) . '<br />' . format_tel($row['tel_mobile']) . '<br />' . $type_transport . '");';
									}
								}
								
							}
						$html_code .= '});';
					$html_code .= '</script>';	
				}
				
				
				$html_code .= '<h1>Liste des transporteurs disponibles pour la periode choisie</h1>';
				
				$html_code .= '<table>';
					$html_code .= '<thead>';
						$html_code .= '<tr>';
							$html_code .= '<th></th>';
							$html_code .= '<th>Nom</th>';
							$html_code .= '<th>Ville</th>';
							$html_code .= '<th>Téléphone fixe</th>';
							$html_code .= '<th>Téléphone mobile</th>';
							$html_code .= '<th>Type de transport</th>';
							$html_code .= '<th><strong>Non</strong> disponible</th>';
						$html_code .= '</tr>';
					$html_code .= '</thead>';
					
					$html_code .= '<tbody>';
						foreach ($list_potentiel_transporteur as $transporteur) {
							
							$transporteur_nom_complet = $transporteur->get_nom_complet();
							$transporteur_adresse = $transporteur->get_adresse();
							$transporteur_telephones = $transporteur->get_telephone();
							
							$html_code .= '<tr>';
								
								$html_code .= '<td>';
									$html_code .= $transporteur_nom_complet['titre'];
								$html_code .= '</td>';
							
								$html_code .= '<td>';
									$html_code .= '<a class="link_dialog" href="?module=benevole&amp;action=view&amp;id=' . $transporteur->get_id_transporteur() . '">';
										$html_code .= mb_strtoupper(stripAccents($transporteur_nom_complet['nom']));
									$html_code .= '</a>';
								$html_code .= '</td>';
								
								$html_code .= '<td>';
									$html_code .= $transporteur_adresse['ville'];
								$html_code .= '</td>';
								
								$html_code .= '<td>';
									$html_code .= format_tel($transporteur_telephones['tel_fixe']);
								$html_code .= '</td>';
								
								$html_code .= '<td>';
									$html_code .= format_tel($transporteur_telephones['tel_mobile']);
								$html_code .= '</td>';
								
								//type de transport
								$type_transport = '';
								
								if ($transporteur->check_transports_locaux()) {
									$type_transport .= '<strong>L</strong>|';
								} else {
									$type_transport .= '-|';
								}
								
								if ($transporteur->check_transports_geneve()) {
									$type_transport .= '<strong>Ge</strong>|';
								} else {
									$type_transport .= '--|';
								}
								
								if ($transporteur->check_transports_lausanne()) {
									$type_transport .= '<strong>La</strong>|';
								} else {
									$type_transport .= '--|';
								}
								
								if ($transporteur->check_transports_vacances()) {
									$type_transport .= '<strong>Vacances</strong>|';
								} else {
									$type_transport .= '-|';
								}
								
								
								$html_code .= '<td>';
									$html_code .= $type_transport;
								$html_code .= '</td>';
								
								
								$html_code .= '<td>';
									$html_code .= '<a class="link_ajax_get" href="?module=transporteur&sub_module=non_dispo_date_transport&action=add&id=' . $transporteur->get_id_transporteur() . '&date_custom='. $date_transport . '">';
										$html_code .= 'Non disponible';
									$html_code .= '</a>';
								$html_code .= '</td>';
								
								
							$html_code .= '</tr>';
						}
					$html_code .= '</tbody>';
				$html_code .= '</table>';
				
			} else {
				
				$html_code .= '<h1>Personne n\'est disponible pour la date et la période choisie</h1>';
				
			}
			
		}
		
		$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));
		
		return $html_code;
	} // class.Transporteur.form.form_find_available_drivers
	
	
	private static function form_transports_potentiels($action, $data_to_display='') {
		
		$weekdays[1] = 'lundi';
		$weekdays[2] = 'mardi';
		$weekdays[3] = 'mercredi';
		$weekdays[4] = 'jeudi';
		$weekdays[5] = 'vendredi';
		$weekdays[6] = 'samedi';
		$weekdays[7] = 'dimanche';
		
		$html_code = '';
		
		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}
		
		$tmp_transporteur = new Transporteur($data_to_display['id']['value']);
		$tmp_transporteur_nom = $tmp_transporteur->get_nom_complet();
		
		$load_needed_class_and_interface = load_class_and_interface(array('GLMM'));
		
		$tmp_GLMM = new GLMM($tmp_transporteur, 10, FALSE);
		
		$array_transports_potentiels = $tmp_GLMM->get_transports_potentiels();
		
		if (count($array_transports_potentiels) > 0) {
			
			if ($tmp_transporteur->has_email()) {
				$tag_email = '<img src="./img/email.png" />';
			} else {
				$tag_email = '';
			}
			
			$html_code .= '<h3>Transports recommandés pour ' . format_titre($tmp_transporteur_nom['titre']) . ' ' . mb_strtoupper(stripAccents($tmp_transporteur_nom['nom'])) . $tag_email . '</h3>';
			
			$html_code .= '<table>';
				$html_code .= '<thead>';
					$html_code .= '<tr>';
						$html_code .= '<th>Date &amp; Heure</th>';
						//$html_code .= '<th>Heure</th>';
						$html_code .= '<th>Passager</th>';
						$html_code .= '<th>Départ</th>';
						$html_code .= '<th>Arrivée</th>';
						$html_code .= '<th>Tél. fixe</th>';
						$html_code .= '<th>Tél. mobile</th>';
						$html_code .= '<th></th>'; //attribuer
					$html_code .= '</tr>';
				$html_code .= '</thead>';
				
				$html_code .= '<tbody>';
			
					$last_date_txt = '';
				
					foreach ($array_transports_potentiels as $row) {
						
						if ($last_date_txt != $row->get_date()) {
						
							$weekday = date('N', strtotime($row->get_date()));
							
							foreach($weekdays as $idx_day => $day) {
								if ($idx_day == $weekday) {
									$txt_weekday = $day;
								}
							}
							
							$html_code .= '<tr>';
								$html_code .= '<th><a class="header_date" href="#top">' . date_yyyymmdd_to_ddmmyyyy($row->get_date()) . ' - ' . substr($txt_weekday,0 ,3) . '</a></th>';
							$html_code .= '</tr>';
							
							$last_date_txt = $row->get_date();
						}
						
						
						$html_code .= '<tr>';
							
							/*
							$html_code .= '<td>';
								$html_code .= '<a href="?module=transport&amp;action=view&amp;id=' . $row->get_id() . '" >';
									$html_code .= date_yyyymmdd_to_ddmmyyyy($row->get_date());
								$html_code .= '</a>';
							$html_code .= '</td>';
							*/
							
							$html_code .= '<td>';
								$html_code .= '<a href="?module=transport&amp;action=view&amp;id=' . $row->get_id() . '" >';
									$html_code .= time_hhmmss_to_hhmm($row->get_time());
								$html_code .= '</a>';
							$html_code .= '</td>';
							
							$tmp_beneficiaire = new Beneficiaire($row->get_id_beneficiaire());
							$tmp_beneficiaire_nom_complet = $tmp_beneficiaire->get_nom_complet();
							$tmp_beneficiaire_tel = $tmp_beneficiaire->get_telephone();
							
							$html_code .= '<td>';
								$html_code .= format_titre($tmp_beneficiaire_nom_complet['titre']) . ' ' . mb_strtoupper(stripAccents($tmp_beneficiaire_nom_complet['nom']));
							$html_code .= '</td>';
							
							$point_depart = $row->get_point_depart();
							$point_arrivee = $row->get_point_arrivee();
							
							// point depart
								if (isset($point_depart['adresse'])) {
									if ($point_depart['type'] == 'beneficiaire') {
										$html_code .= '<td title="Domicile : ' . $point_depart['adresse'] . '">';
									} elseif ($point_depart['type'] == 'lieu') {
										$html_code .= '<td title="' . $point_depart['nom_complet'] . ' - ' . $point_depart['adresse'] . '">';
									}
									
								} else {
									$html_code .= '<td>';
								}
								$html_code .= ucfirst(stripAccents($point_depart['ville']));
							$html_code .= '</td>';
							
							// point arrivee
							if (isset($point_arrivee['adresse'])) {
								if ($point_arrivee['type'] == 'beneficiaire') {
									$html_code .= '<td title="domicile : ' . $point_arrivee['adresse'] . '">';
								} elseif ($point_arrivee['type'] == 'lieu') {
									$html_code .= '<td title="' . $point_arrivee['nom_complet'] . ' - ' . $point_arrivee['adresse'] . '">';
								}
								
							} else {
								$html_code .= '<td>';
							}
							
								$html_code .= ucfirst(stripAccents($point_arrivee['ville']));
							$html_code .= '</td>';
							
							$html_code .= '<td class="tel_fixe">';
								$html_code .= format_tel($tmp_beneficiaire_tel['tel_fixe']);
							$html_code .= '</td>';
							
							$html_code .= '<td class="tel_mobile">';
								$html_code .= format_tel($tmp_beneficiaire_tel['tel_mobile']);
							$html_code .= '</td>';
							
							$html_code .= '<td>';
								$html_code .= '<a class="link_ajax_get" href="?module=transport&amp;action=link_driver&amp;id_transport=' . $row->get_id() . '&amp;id_transporteur=' . $tmp_transporteur->get_id_transporteur() . '">';
									$html_code .= 'Attribuer';
								$html_code .= '</a>';
							$html_code .= '</td>';
							
							
						$html_code .= '</tr>';
					}
				
				$html_code .= '</tbody>';
			$html_code .= '</table>';
		} else {
			$html_code .= '<h3>Pas de transport à recommander</h3>';
		}
		
		$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));
		
		return $html_code;
	}
	
	
	
	
	
	private static function form_city_near($action, $data_to_display='') {
		global $dbh;
		$html_code = '';
		
		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}
		
		if (isset($_GET['limit_km']) && is_numeric($_GET['limit_km']) && $_GET['limit_km'] > 0) {
			$limit_km = $_GET['limit_km'];
		} else {
			$limit_km = 5;
		}
		
		$sql = "SELECT DISTINCT ville ";
		$sql .= " FROM beneficiaire ";
		$sql .= " ORDER BY ville";
		
		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);
		
		
		//form selection de la ville
		$html_code .= '<form action="" method="GET">';
			
			$html_code .= '<label for="city_near">Choix de la ville</label>';
			
			$html_code .= '<select id="city_near" name="city_near">';
				
				$html_code .= '<option></option>';
				
				foreach($result as $row) {
					$html_code .= '<option value="' . $row['ville'] . '" ';
					
						//if ((utf8_decode($_GET['city_near'])) == $row['ville']) {
						if ((($_GET['city_near'])) == $row['ville']) {
							$html_code .= 'selected="selected">';
						} else {
							$html_code .= '>';
						}
						
						$html_code .= $row['ville'];
						
					$html_code .= '</option>';
				}
				
			$html_code .= '</select>';
			
			$html_code .= '<p>';
				$html_code .= '<label for="limit_km">Limite en kilomètres</label>';
				$html_code .= add_FormElement_input('text', 'limit_km', '', 5);
			$html_code .= '</p>';
			
			$html_code .= add_FormElement_input('hidden', 'form', '', 'city_near');
			$html_code .= add_FormElement_input('hidden', 'module', '', 'transporteur');
			$html_code .= add_FormElement_input('hidden', 'sub_module', '', '""');
			$html_code .= add_FormElement_input('hidden', 'action', '', $action);
			
			$html_code .= '<input type="submit" value="Soumettre" />';
			
		$html_code .= '</form>';
		
		
		if (isset($_GET['city_near']) && $_GET['city_near'] != '') {
			$array_distance_near_city = array();
			$distance_benevole = array();
			$city_near = ($_GET['city_near']);
			
			
			//construction du tableau des distances
			$sql = "SELECT benevole.* ";
			$sql .= " FROM benevole INNER JOIN benevole_participation_filiale ON benevole.id = benevole_participation_filiale.id_benevole ";
			$sql .= " WHERE benevole_participation_filiale.is_transporteur=1";
			$sql .= " AND benevole_participation_filiale.id_filiale=" . $_SESSION['filiale']['id'];
			$sql .= " AND benevole_participation_filiale.is_active=1";
			$sql .= " ORDER BY benevole.nom, benevole.prenom";
			
			$sth = $dbh->query($sql);
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);
			
			$load_needed_class_and_interface = load_class_and_interface(array('Trajet_Pre_Defini'));
			
			foreach ($result as $row) {
				
				if (in_array($row['ville'], $array_distance_near_city, TRUE)) {
					$distance_benevole[$row['id']] =  $array_distance_near_city[$row['ville']];
				} else {
					
					//meme ville
					if ($row['ville'] == $city_near) {
						$distance_benevole[$row['id']] = 0;
						$array_distance_near_city[$row['ville']] = 0;
					} else {
						//tente un trajet predefini
						$tmp_trajet_predefini_result = Trajet_Pre_Defini::find_combination($city_near, $row['ville']);
						if ($tmp_trajet_predefini_result) {
							$distance_benevole[$row['id']] = $tmp_trajet_predefini_result['distance'];
							$array_distance_near_city[$row['ville']] =  $tmp_trajet_predefini_result['distance'];
						} else {
							//google maps
							if (checkInternetConnection('maps.google.com')) {
								$distance_benevole[$row['id']] = Trajet_Pre_Defini::download_distance_from_google_maps('', $row['npa'], $row['ville'], '', '1111', $city_near);
								$array_distance_near_city[$row['ville']] = $distance_benevole[$row['id']];
							} else {
								//impossible de determine la distance
								$distance_benevole[$row['id']] = 100;
							}
						}
					}
				}
			}
			
			//tri ASC
			asort($distance_benevole);
			
			//carte google
			if (checkInternetConnection()) {
			
				//refresh des geocodes manquants
				$sql = "SELECT benevole.* ";
				$sql .= " FROM benevole ";
				$sql .= " WHERE id NOT IN (";
					$sql .= " SELECT benevole.id ";
					$sql .= " FROM benevole, geocode ";
					$sql .= " WHERE benevole.adresse = geocode.adresse ";
					$sql .= " AND benevole.npa = geocode.npa ";
					$sql .= " AND benevole.ville = geocode.ville ";
				$sql .= ")";
				$sql .= "LIMIT 10";
				
				$sth = $dbh->query($sql);
				$geocode_to_import = $sth->fetchAll(PDO::FETCH_ASSOC);
				
				//geocoding des adresses
				if (count($geocode_to_import) > 0) {
					$load_needed_class_and_interface = load_class_and_interface(array('Geocode'));
					$control_var = Geocode::gmap_geocoding($geocode_to_import);
				} else {
					//nothing to do
				}
				
				$sql = "SELECT geocode.lat, geocode.lng, benevole.*, benevole_participation_filiale.*";
				$sql .= " FROM geocode, benevole_participation_filiale, benevole ";
				$sql .= " WHERE benevole.adresse = geocode.adresse ";
				$sql .= " AND benevole.npa = geocode.npa ";
				$sql .= " AND benevole.ville = geocode.ville ";
				$sql .= " AND benevole_participation_filiale.id_benevole = benevole.id";
				
				
				$sth = $dbh->query($sql);
				$benevole_geocode = $sth->fetchAll(PDO::FETCH_ASSOC);
				
				$html_code .= '<div id="map_benevoles" class="map_google"></div>';
				
				$html_code .= '<script type="text/javascript">';
					$html_code .= '$(document).ready(function() {';
						$html_code .= '$(\'#map_benevoles\').initGoogleMap();';
					
						foreach ($benevole_geocode as $row) {
							
							if ($row['is_transporteur'] == 1) {
								$type_transport = '';
								
								if ($row['do_transports_locaux'] == 1) {
									$type_transport .= '<strong>Locaux</strong>|';
								} else {
									$type_transport .= '-|';
								}
								
								if ($row['do_transports_geneve'] == 1) {
									$type_transport .= '<strong>Genève</strong>|';
								} else {
									$type_transport .= '--|';
								}
								
								if ($row['do_transports_lausanne'] == 1) {
									$type_transport .= '<strong>Lausanne</strong>|';
								} else {
									$type_transport .= '--|';
								}
								
								if ($row['do_transports_holidays'] == 1) {
									$type_transport .= '<strong>Vacances</strong>|';
								} else {
									$type_transport .= '-|';
								}
	
							} else {
								$type_transport ='';
							}
							
							
							//graph uniquement si doit etre liste
							foreach ($distance_benevole as $index => $row_2) {
								if (Benevole::get_id_benevole_filiale_from_super_id_benevole_and_id_filiale($index, $_SESSION['filiale']['id']) == $row['id'] && isset($row_2) && $row_2 < $limit_km) {
									$html_code .= 'addMarkerWithGeocode(' . $row['lat'] . ',' . $row['lng'] . ',"' . $row['titre'] . ' ' . $row['nom'] . '<br /> ' . format_tel($row['tel_fixe']) . '<br />' . format_tel($row['tel_mobile']) . '<br />' . $type_transport . '");';
								}
							}
							
						}
					$html_code .= '});';
				$html_code .= '</script>';	
			}
			
			
			//presentation du resultat
			$html_code .= '<table>';
			
				$html_code .= '<thead>';
					$html_code .= '<tr>';
						$html_code .= '<th>Distance</th>';
						$html_code .= '<th>Nom</th>';
						$html_code .= '<th>Ville</th>'; 
						$html_code .= '<th>Tél. fixe</th>';
						$html_code .= '<th>Tél. mobile</th>';
					$html_code .= '</tr>';
				$html_code .= '</thead>';
				
				$html_code .= '<tbody>';
					
					foreach($distance_benevole as $index => $transporteur) {
						if (isset($transporteur) && $transporteur < $limit_km) {
							$tmp_transporteur = new Transporteur($index, $_SESSION['filiale']['id']);
							$tmp_transporteur_nom_complet = $tmp_transporteur->get_nom_complet();
							$tmp_transporteur_adresse = $tmp_transporteur->get_adresse();
							$tmp_transporteur_telephone = $tmp_transporteur->get_telephone();
							
							
							$html_code .= '<tr>';
								$html_code .= '<td>';
									if ($transporteur != 0) {
										$html_code .= round($transporteur, 0) . 'km';
									} else {
										$html_code .= 'même ville';
									}
									
								$html_code .= '</td>';
								
								$html_code .= '<td>';
									$html_code .= '<a class="link_dialog" href="?module=benevole&action=view&id=' . $tmp_transporteur->get_id() . '">';
										$html_code .= mb_strtoupper(stripAccents($tmp_transporteur_nom_complet['nom'])) . ', ' . $tmp_transporteur_nom_complet['prenom'];
									$html_code .= '</a>';
								$html_code .= '</td>';
								
								$html_code .= '<td>';
									$html_code .= mb_strtoupper(stripAccents($tmp_transporteur_adresse['ville']));
								$html_code .= '</td>';
								
								$html_code .= '<td class="tel_fixe">';
									$html_code .= format_tel($tmp_transporteur_telephone['tel_fixe']);
								$html_code .= '</td>';
								
								$html_code .= '<td class="tel_mobile">';
									$html_code .= format_tel($tmp_transporteur_telephone['tel_mobile']);
								$html_code .= '</td>';
							$html_code .= '</tr>';
						}
						
					}
					
				$html_code .= '</tbody>';
			$html_code .= '</table>';
			
		}
		
		$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));
		
		return ($html_code);
	} // class.Transporteur.form.city_near
	
	
} // class.Transporteur

?>