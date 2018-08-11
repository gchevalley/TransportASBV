<?php

require_once( str_replace ( '\\', '/', dirname(dirname(__FILE__))) . '/class.declaration.php' );
$load_needed_class_and_interface = load_class_and_interface(array('Contact', 'Filiale'));

class Benevole implements Contact {
	private $id = 0;
	private $super_login = '';
	private $super_password = '';
	private $titre = '';
	private $nom = '';
	private $prenom = '';
	private $nom_complet = array();
	private $array_adresse = '';
	private $adresse = '';
	private $adresse_complement = '';
	private $npa = '';
	private $ville = '';
	private $pays = '';
	private $array_telephone = '';
	private $tel_fixe = '';
	private $tel_professionnel = '';
	private $tel_mobile = '';
	private $email = '';
	private $iban = '';
	private $ccp = '';
	private $info_diverses = '';
	private $is_super_admin = 0;


	private $array_participation_in_filiale = array();
		private $current_filiale = ''; //object filiale

// *************************** a detruire *************************** -<noter ref utilisation
		private $current_filiale_found = FALSE;
		private $current_filiale_benevole_id = 0;
		private $current_filiale_id = 0;
		private $current_filiale_nom = '';
// ******************************************************************

		//rights current filiale
		private $current_filiale_is_permanencier = FALSE;
		private $current_filiale_is_transporteur = FALSE;
		private $current_filiale_is_administrateur = FALSE;



	function __construct($id_benevole, $super_password='', $titre='', $nom='', $prenom='', $adresse='', $adresse_complement='', $npa='', $ville='', $pays='', $tel_fixe='', $tel_professionnel='', $tel_mobile='', $email='', $iban='', $ccp='', $info_diverses='', $is_super_admin=0) {

		if (is_numeric($id_benevole) && Benevole::id_exists($id_benevole)) {

			$this->id = $id_benevole;
			$this->mountAttributsFromDB();

		} else { //creation de la nouvelle entite
			if ($id_benevole === 0) {
				$this->addEntryDB($super_password, $titre, $nom, $prenom, $adresse, $adresse_complement, $npa, $ville, $pays, $tel_fixe, $tel_professionnel, $tel_mobile, $email, $iban, $ccp, $info_diverses, $is_super_admin);
			} else {
				//erreur au niveau de l'id
			}
		}
	} // class.Benevole.func___construct

	private function mountAttributsFromDB() {

		global $dbh;
		$sql = "SELECT * FROM benevole WHERE id=" . $this->id;

		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		$this->super_login = $result['super_login'];
		$this->super_password = $result['super_password'];
		$this->titre = $result['titre'];
		$this->nom = $result['nom'];
		$this->prenom = $result['prenom'];
		$this->adresse = $result['adresse'];
		$this->adresse_complement = $result['adresse_complement'];
		$this->npa = $result['npa'];
		$this->ville = $result['ville'];
		$this->pays = $result['pays'];
		$this->tel_fixe = $result['tel_fixe'];
		$this->tel_professionnel = $result['tel_professionnel'];
		$this->tel_mobile = $result['tel_mobile'];
		$this->email = $result['email'];
		$this->iban = $result['iban'];
		$this->ccp = $result['ccp'];
		$this->info_diverses = $result['info_diverses'];
		$this->is_super_admin = $result['is_super_admin'];

		$this->mountParticipationInFiliale();


	} // class.Benevole.func.mountAttributsFromDB

	private function addEntryDB($super_password, $titre, $nom, $prenom, $adresse, $adresse_complement, $npa, $ville, $pays, $tel_fixe, $tel_professionnel, $tel_mobile, $email, $iban, $ccp, $info_diverses, $is_super_admin) {

		if (Benevole::id_exists($_SESSION['benevole']['id'])) {
			$tmp_benevole = new Benevole($_SESSION['benevole']['id']);

			if ($tmp_benevole->checkIsSuperAdmin()) {
				//continue l'execution de la function
			} else {
				if (Filiale::id_exists($_SESSION['filiale']['id'])) {
					if ($tmp_benevole->checkIsAdminOfFiliale($_SESSION['filiale']['id'])) {
						//continue l'execution de la function
					} else {
						die();
					}
				} else {
					die();
				}
			}
		} else {
			die();
		}

		global $dbh;

		//ajoute l'entree geocode
		if (checkInternetConnection('maps.google.com')) {
			$load_needed_class_and_interface = load_class_and_interface(array('Geocode'));
			$array_coor = Geocode::find_combination($adresse, $npa, $ville, $pays, TRUE);
		}

		//processing de nettoyage des args de la fonction
		$titre = $dbh->quote($titre);
		$adresse = $dbh->quote($adresse);
		$adresse_complement = $dbh->quote($adresse_complement);
		$npa = $dbh->quote($npa);
		$ville = $dbh->quote($ville);
		$pays = $dbh->quote($pays);
		$tel_fixe = $dbh->quote($tel_fixe);
		$tel_professionnel = $dbh->quote($tel_professionnel);
		$tel_mobile = $dbh->quote($tel_mobile);
		$email = $dbh->quote($email);
		$iban = $dbh->quote($iban);
		$ccp = $dbh->quote($ccp);
		$info_diverses = $dbh->quote($info_diverses);


		//creation des login et cryptage du password
		$super_login = substr(mb_strtolower(stripAccents($prenom)),0,1);
		$super_login .= mb_strtolower(stripAccents($nom));
		$super_login = str_replace(' ', '', $super_login);
		$super_login = str_replace('-', '', $super_login);

		$nom = $dbh->quote($nom);
		$prenom = $dbh->quote($prenom);

			//s'assure que le login est unique sinon l'incremente de 1
			$sql = "SELECT * FROM benevole WHERE super_login LIKE '" . $super_login . "%'";
			$sth = $dbh->query($sql);
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);

			if (count($result)>0) {
				$super_login .= (count($result)+1);
			} else {
				//le super_login est dispo
			}

			//$super_password = crypt($super_password, $super_login);
			$super_password = md5($super_password);

			$super_login = $dbh->quote($super_login);
			$super_password = $dbh->quote($super_password);


		$today_date = $dbh->quote(date('Y-m-d'));
		$today_time = $dbh->quote(date('H:i:s'));


		//creation de la nouvelle entite dans la db
		$sql = "INSERT INTO benevole (super_login, super_password, titre, nom, prenom, adresse, adresse_complement, npa, ville, pays, tel_fixe, tel_professionnel, tel_mobile, email, iban, ccp, info_diverses, is_super_admin, insert_date, insert_time, insert_benevole_user, last_update_date, last_update_time, last_update_benevole_user) ";
		$sql .= "VALUES ($super_login, $super_password, $titre, $nom, $prenom, $adresse, $adresse_complement, $npa, $ville, $pays, $tel_fixe, $tel_professionnel, $tel_mobile, $email, $iban, $ccp, $info_diverses, $is_super_admin, $today_date, $today_time, " . $tmp_benevole->get_id() . ", $today_date, $today_time, " . $tmp_benevole->get_id() . ")";

		$statut_query = $dbh->exec($sql);

		//mount l'object
		$this->id = $dbh->lastInsertId();
		$this->mountAttributsFromDB();


		//ajoute le lieu base sur la ville si n'existe pas deja
		if ($this->npa != '' && $this->ville != '' && $this->pays != '') {
			$load_needed_class_and_interface = load_class_and_interface(array('Lieu'));
			Lieu::ajouterVille($this->ville, $this->npa, $this->pays);
		}


	} // class.Benvole.func.addEntry



	public function editerAttributs($attr, $new_value) { //2 matrix ou 2 valeurs

		if (is_numeric($_SESSION['benevole']['id']) && Benevole::id_exists($_SESSION['benevole']['id'])) {

			global $dbh;

			$sql = "UPDATE benevole ";

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

			$sql .= "WHERE id=" . $this->id;
			$statut_query = $dbh->exec($sql);

			//recharge avec les nouvelles donnees
			$this->mountAttributsFromDB();

		}

	} // class.Benevole.func.editerAttributs


	public function editerAttributsParticipationFiliale($attr, $new_value, $which_filiale='') { //2 matrix ou 2 valeurs

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
			$this->mountParticipationInFiliale();
		}

	} // class.Benevole.func.editerAttributsParticipationFiliale


	public function editerAttributsDisponibilite($array_disponibilite, $which_categorie_need_update) {
		if (is_array($array_disponibilite) && is_numeric($_SESSION['filiale']['id']) && is_numeric($_SESSION['benevole']['id']) && Benevole::id_exists($_SESSION['benevole']['id']) && Filiale::id_exists($_SESSION['filiale']['id'])) {

			$load_needed_class_and_interface = load_class_and_interface(array('Transporteur', 'Permanencier'));

			//transporteur : dispo transport
			if (in_array('transport', $which_categorie_need_update) && Transporteur::id_exists($this->get_id(), $_SESSION['filiale']['id'])) {
				$tmp_transporteur = new Transporteur($this->get_id(), $_SESSION['filiale']['id']);
				$id_transport = Benevole_Disponibilite_Categorie::get_id_from_nom('transport');


				foreach ($array_disponibilite as $row) {
					if ($row['id_categorie'] == $id_transport) {
						if ($row['value'] === TRUE || $row['value'] == 1) {
							$tmp_transporteur->ajouterDisponibiliteStandard($row['id_categorie'], $row['id_jour_semaine'], $row['id_periode_journee'], $_SESSION['filiale']['id']);
						} elseif ($row['value'] === FALSE || $row['value'] == 0) {
							$tmp_transporteur->supprimerDisponibiliteStandard($row['id_categorie'], $row['id_jour_semaine'], $row['id_periode_journee'], $_SESSION['filiale']['id']);
						}
					}
				}
			}

			//transporteur : dispo appel
			if (in_array('appel', $which_categorie_need_update) && Transporteur::id_exists($this->get_id(), $_SESSION['filiale']['id'])) {
				$tmp_transporteur = new Transporteur($this->get_id(), $_SESSION['filiale']['id']);
				$id_appel = Benevole_Disponibilite_Categorie::get_id_from_nom('appel');


				foreach ($array_disponibilite as $row) {
					if ($row['id_categorie'] == $id_appel) {
						if ($row['value'] === TRUE || $row['value'] == 1) {
							$tmp_transporteur->ajouterDisponibiliteStandard($row['id_categorie'], $row['id_jour_semaine'], $row['id_periode_journee'], $_SESSION['filiale']['id']);
						} elseif ($row['value'] === FALSE || $row['value'] == 0) {
							$tmp_transporteur->supprimerDisponibiliteStandard($row['id_categorie'], $row['id_jour_semaine'], $row['id_periode_journee'], $_SESSION['filiale']['id']);
						}
					}
				}
			}

			//permanencier : dispo permanence
			if (in_array('permanence', $which_categorie_need_update) && Permanencier::id_exists($this->get_id(), $_SESSION['filiale']['id'])) {
				$tmp_permanencier = new Permanencier($this->get_id(), $_SESSION['filiale']['id']);
				$id_permanence = Benevole_Disponibilite_Categorie::get_id_from_nom('permanence');

				foreach ($array_disponibilite as $row) {
					if ($row['id_categorie'] == $id_permanence) {
						if ($row['value'] === TRUE || $row['value'] == 1) {
							$tmp_permanencier->ajouterDisponibiliteStandard($row['id_categorie'], $row['id_jour_semaine'], $row['id_periode_journee'], $_SESSION['filiale']['id']);
						} elseif ($row['value'] === FALSE || $row['value'] == 0) {
							$tmp_permanencier->supprimerDisponibiliteStandard($row['id_categorie'], $row['id_jour_semaine'], $row['id_periode_journee'], $_SESSION['filiale']['id']);
						}
					}
				}
			}

		}
	}


	private function mountParticipationInFiliale() {

		$this->array_participation_in_filiale = array();

		global $dbh;
		$sql = "SELECT * FROM benevole_participation_filiale WHERE id_benevole=" . $this->id;
		$sth = $dbh->query($sql);

		$result_participation_in_filiale = $sth->fetchAll(PDO::FETCH_ASSOC);

		//si array de retour non vide :
		if (count($result_participation_in_filiale)>0) {
			if (isset($_SESSION['filiale']['id'])) {
				if (Filiale::id_exists($_SESSION['filiale']['id'])) {
					$tmp_filiale = new Filiale($_SESSION['filiale']['id']);
				} else {
					//souci au niveau de la session...
				}
			}

			$this->current_filiale_found = FALSE;

			foreach ($result_participation_in_filiale as $row) {
				foreach ($row as $column=>$value) {
					$this->array_participation_in_filiale[$row['id_filiale']][$column] = $value;

					if (is_object($tmp_filiale) && $this->current_filiale_found === FALSE) {
						if ($tmp_filiale->get_id() == $row['id_filiale']) {


							//creation de l'object filiale
							$this->current_filiale = new Filiale($row['id_filiale']);



							if ($row['is_permanencier'] == 1) {
								$this->current_filiale_is_permanencier = TRUE;
							}

							if ($row['is_transporteur'] == 1) {
								$this->current_filiale_is_transporteur = TRUE;
							}

							if ($row['is_administrateur_filiale'] == 1) {
								$this->current_filiale_is_administrateur = TRUE;
							}

							$this->current_filiale_benevole_id = $row['id'];
							$this->current_filiale_id = $this->current_filiale->get_id();
							$this->current_filiale_nom = $this->current_filiale->get_nom();
							$this->current_filiale_found = TRUE;
						}
					}
				}
			}
		}
	} // class.Benevole.func.mountParticipationInFiliale

	/*
	public function ajouterFiliale($nom, $adresse='', $adresse_complement='', $npa='', $ville='', $tel_permanence='') {

		if ($this->checkIsSuperAdmin()) {
			$tmp_filiale = new Filiale(0, $nom, $adresse, $adresse_complement, $npa, $ville, $tel_permanence);
		} else {
			return FALSE;
			exit();
		}
	} // class.Benevole.func.ajouterFiliale
	*/

	public function ajouterParticipationDansFiliale($id_filiale, $is_permanencier, $is_transporteur, $do_transports_locaux, $do_transports_geneve, $do_transports_lausanne, $do_transports_holidays, $is_administrateur_filiale, $has_external_login) {
		//controle les droits d'admin
		// soit super_admin ou admin_de_filiale

		if (Benevole::id_exists($_SESSION['benevole']['id']) && Filiale::id_exists($id_filiale)) {

			$actual_controle_permanencier = new Benevole($_SESSION['benevole']['id']);

			if (($actual_controle_permanencier->checkIsSuperAdmin()) || ($actual_controle_permanencier->checkIsAdminOfFiliale($id_filiale))) {
				//l'utilisateur a les droits necessaire
				global $dbh;

				//mount la filiale pour en connaitre les details et verifier son existence
				$tmp_filiale = new Filiale($id_filiale);

				$today_date = $dbh->quote(date('Y-m-d'));
				$today_time = $dbh->quote(date('H:i:s'));

				$sql = "INSERT INTO benevole_participation_filiale ";
				$sql .= " (filiale_login, id_benevole, id_filiale, is_permanencier, is_transporteur, do_transports_locaux, do_transports_geneve, do_transports_lausanne, do_transports_holidays, is_administrateur_filiale, has_external_login, insert_date, insert_time, insert_benevole_user) ";
				$sql .= " VALUES ('" . mb_strtolower(stripAccents($tmp_filiale->get_nom())) . '-' . "$this->super_login', $this->id, " . $tmp_filiale->get_id() . ", $is_permanencier, $is_transporteur, $do_transports_locaux, $do_transports_geneve, $do_transports_lausanne, $do_transports_holidays, $is_administrateur_filiale, $has_external_login, $today_date, $today_time, $actual_controle_permanencier->id)";

				$dbh->exec($sql);
				$new_benevole_id = $dbh->lastInsertId();

				if ($is_transporteur == 1) {
					//monter par defaut un calendrier de disponibilite
					$load_needed_class_and_interface = load_class_and_interface(array('Transporteur'));
					$tmp_transporteur = new Transporteur($new_benevole_id);

					for ($i=1; $i<=7; $i++) {
						for ($j=1; $j<=3; $j++) {
							$tmp_transporteur->ajouterDisponibiliteStandard(1, $i, $j, $id_filiale);
							$tmp_transporteur->ajouterDisponibiliteStandard(3, $i, $j, $id_filiale);
						}
					}
				}

				if ($is_permanencier == 1) {
					//monter par defaut un calendrier de disponibilite
					$load_needed_class_and_interface = load_class_and_interface(array('Permanencier'));

					$tmp_permanencier = new Permanencier($new_benevole_id);

					for ($i=1; $i<=7; $i++) {
						for ($j=1; $j<=3; $j++) {
							$tmp_permanencier->ajouterDisponibiliteStandard(2, $i, $j, $id_filiale);
						}
					}
				}

			} else {
				return FALSE;
				exit();
			}

		} else {
			return FALSE;
			exit();
		}

	} // class.Benevole.func.ajouterParticipationDansFiliale

	public function get_iban() {
		return $this->iban;
	}

	public function checkIsTransporteur($which_filiale=0) {
		$load_needed_class_and_interface = load_class_and_interface(array('Transporteur'));

		if ($which_filiale == 0 ) {
			if (Filiale::id_exists($_SESSION['filiale']['id']) && Transporteur::id_exists($this->get_id(), $_SESSION['filiale']['id'] ) ) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			if (Filiale::id_exists($which_filiale)) {
				foreach ($this->array_participation_in_filiale as $filiale) {
					if ($filiale['id_filiale'] == $which_filiale && $filiale['is_transporteur'] == 1) {
						return TRUE;
						break;
					}
				}
				return FALSE;
			}
		}
	}

	public function checkIsPermanencier($which_filiale=0) {
		$load_needed_class_and_interface = load_class_and_interface(array('Permanencier'));

		if ($which_filiale == 0 ) {
			if (Filiale::id_exists($_SESSION['filiale']['id']) && Permanencier::id_exists($this->get_id(), $_SESSION['filiale']['id'] ) ) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			if (Filiale::id_exists($which_filiale)) {
				foreach ($this->array_participation_in_filiale as $filiale) {
					if ($filiale['id_filiale'] == $which_filiale && $filiale['is_permanencier'] == 1) {
						return TRUE;
						break;
					}
				}
				return FALSE;
			}
		}
	}


	public function checkIsSuperAdmin() {
		if ($this->is_super_admin == 1) {
			return TRUE;
			exit();
		} else {
			return FALSE;
			exit();
		}
	} // class.Benevole.func.checkIsSuperAdmin


	public function checkIsAdminOfFiliale($which_filiale=0) {
		if ($which_filiale==0) {

			if (Filiale::id_exists($_SESSION['filiale']['id']) && $_SESSION['filiale']['id'] == $this->current_filiale_id && $this->current_filiale_is_administrateur === TRUE ) {
				return TRUE;
				exit();
			} else {
				return FALSE;
				exit();
			}

		} else {
			// passe en revue les participation

			if (Filiale::id_exists($which_filiale)) {

				/*
				$load_needed_class_and_interface = load_class_and_interface(array('Administrateur_Filiale'));

				if (Administrateur_Filiale::id_exists($this->id, $which_filiale)) {
					return TRUE;
				} else {
					return FALSE;
				}
				*/


				foreach ($this->array_participation_in_filiale as $index => $participation) {
					if ($index == $which_filiale) {
						if ($participation['is_administrateur_filiale'] == 1) {
							return TRUE;
						} else {
							return FALSE;
						}
					}
				}

			}

			return FALSE;
		}

	} // class.Benevole.func.checkIsAdminOfFiliale


	public function get_id() {
		if (func_num_args()==0) {
			return $this->id;
		} else {
			// l'id benvole au niveau filiale est demande et non l'id general
			// pas besoin de remonter les info de la db, prendre direct dans la matrix
			// participation in filiale
			$args = func_get_args();

			foreach ($this->array_participation_in_filiale as $filiale) {
				if ($filiale['id_filiale'] == $args[0]) {
					return $filiale['id'];
				}
			}

		}
	} // class.Benevole.func.get_id


	public function get_email() {
		return $this->email;
	} // class.Benevole.func.get_email


	private function mountAdresseArray() {
		$this->array_adresse['adresse'] = $this->adresse;

		if ($this->adresse_complement != '') {
			$this->array_adresse['adresse_complement']= $this->adresse_complement;
		}

		$this->array_adresse['npa'] = $this->npa;
		$this->array_adresse['ville'] = $this->ville;

		$this->array_adresse['pays'] = $this->pays;
	}


	public function get_adresse() {
		$this->mountAdresseArray();
		return $this->array_adresse;
	} // class.Benevole.func.get_adresse


	private function group_nom() {
		/*
		if ($this->prenom != '') {
			$this->nom_complet = $this->nom . ', ' . $this->prenom;
		} else {
			$this->nom_complet = $this->nom;
		}
		*/

		if ($this->titre != '') {
			$this->nom_complet['titre'] = $this->titre;
		}

		if ($this->prenom != '') {
			$this->nom_complet['prenom'] = $this->prenom;
		}

		$this->nom_complet['nom'] = $this->nom;


	} // class.Benevole.func.group_nom


	public function get_nom_complet() {
		$this->group_nom();
		return $this->nom_complet;
	} // class.Benevole.func.get_nom_complet


	private function group_telephone_into_array() {
		if ($this->tel_fixe != '') {
			$this->array_telephone['tel_fixe'] = $this->tel_fixe;
		}

		if ($this->tel_professionnel != '') {
			$this->array_telephone['tel_professionnel'] = $this->tel_professionnel;
		}

		if ($this->tel_mobile != '') {
			$this->array_telephone['tel_mobile'] = $this->tel_mobile;
		}
	} // class.Benevole.func.group_telephone_into_array


	public function get_telephone() {
		$this->group_telephone_into_array();
		return $this->array_telephone;
	} // class.Benevole.func.get_telephone


	public static function id_exists($id_to_check) {

		$input_args = func_get_args();

		if (func_num_args() == 1) { // cas standard, check au niveau de la table benevole

			if (checkID($id_to_check)) {
				global $dbh;
				$sql = "SELECT * FROM benevole WHERE id=" .$id_to_check;
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

		} elseif (func_num_args() == 2) { // check si super id benevole existe dans filiale

			$input_args = func_get_args();

			$id_benevole = $input_args[0]; //au niveau benevole et non filiale !
			$id_filiale = $input_args[1];

			if (Benevole::id_exists($id_benevole) && Filiale::id_exists($id_filiale)) {
				global $dbh;

				$sql = "SELECT id ";
				$sql .= "FROM benevole_participation_filiale ";
				$sql .= "WHERE id_benevole=" . $id_benevole . " ";
				$sql .= "AND id_filiale=" . $id_filiale;

				$sth = $dbh->query($sql);
				$result = $sth->fetch(PDO::FETCH_ASSOC);

				if ($result != false) {
					return true;
				} else {
					return FALSE;
					exit();
				}
			} else {
				die();
			}

		} else {
			//rien pour l'instant
		}

	} //class.Benevole.func.id_exists


	public static function id_benevole_filiale_exists($id_to_check) {

		if (checkID($id_to_check)) {
			global $dbh;
			$sql = "SELECT * FROM benevole_participation_filiale WHERE id=" .$id_to_check;
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
	} //class.Benevole.func.id_benevole_filiale_exists


	public static function get_super_id_benvole_from_id_benevole_filiale($id_benevole_filiale) {
		if (is_numeric($id_benevole_filiale) && Benevole::id_benevole_filiale_exists($id_benevole_filiale)) {
			global $dbh;

			$sql = "SELECT id_benevole, id_filiale ";
			$sql .= " FROM benevole_participation_filiale ";
			$sql .= " WHERE id=" . $id_benevole_filiale;

			$sth = $dbh->query($sql);
			$result = $sth->fetch(PDO::FETCH_ASSOC);

			if ($result != false) {
				return $result['id_benevole'];
			} else {
				return FALSE;
			}

		} else {
			return FALSE;
		}
	} // class.Benevole.func.get_super_id_benvole_from_id_benevole_filiale_benvole


	public static function get_id_benevole_filiale_from_super_id_benevole_and_id_filiale($id_benevole, $id_filiale) {
		if (is_numeric($id_benevole) && is_numeric($id_filiale) && Benevole::id_exists($id_benevole) && Filiale::id_exists($id_filiale)) {

			global $dbh;

			$sql = "SELECT id ";
			$sql .= "FROM benevole_participation_filiale ";
			$sql .= " WHERE id_benevole=" . $id_benevole;
			$sql .= " AND id_filiale=" . $id_filiale;

			$sth = $dbh->query($sql);
			$result = $sth->fetch(PDO::FETCH_ASSOC);

			if ($result != false) {
				return $result['id'];
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	} // class.Benevole.func.get_id_benevole_filiale_from_super_id_benevole_and_id_filiale


	public static function get_id_filiale_from_id_benevole_filiale($id_benevole_in_filiale) {
		if (is_numeric($id_benevole_in_filiale) && Benevole::id_benevole_filiale_exists($id_benevole_in_filiale)) {
			global $dbh;

			$sql = "SELECT id_filiale ";
			$sql .= " FROM benevole_participation_filiale ";
			$sql .= " WHERE id=$id_benevole_in_filiale";

			$sth = $dbh->query($sql);
			$result = $sth->fetch(PDO::FETCH_ASSOC);

			if ($result != false) {
				return $result['id_filiale'];
			} else {
				return FALSE;
			}
		}
	}

	private function mountLocalPassword() {
		global $dbh;

		$sql = "SELECT super_password FROM benevole WHERE id=" . $this->id;
		$sth = $dbh->query($sql);

		$result = $sth->fetch(PDO::FETCH_ASSOC);

		if ($result != false) {
			$this->super_password = $result['super_password'];
		} else {
			return FALSE;
		}
	}


	private function getLocalPassword() {
		$this->mountLocalPassword();
		return $this->super_password;
	}


	private function setLocalPassword($new_password_to_set) {
		$tmp_benevole = new Benevole($_SESSION['benevole']['id']);

		if ($tmp_benevole->checkIsSuperAdmin() || $tmp_benevole->checkIsAdminOfFiliale($_SESSION['filiale']['id']) || $_SESSION['auth']['status'] = 'OK') {

			global $dbh;
			$this->super_password = $new_password_to_set;

			$sql = "UPDATE benevole "
				. " SET super_password=" . $dbh->quote($new_password_to_set)
				. " WHERE id=" . $this->id;

			$dbh->exec($sql);
		}
	}


	private function newLocalPassword($new_password_to_set) {
		//$crypt = crypt($new_password_to_set, $this->super_login);
		//return $this->setLocalPassword($crypt);
		return $this->setLocalPassword(md5($new_password_to_set));
	}


	public function checkLocalPassword($local_password_to_check) {

		//if (strcmp($this->super_password, crypt($local_password_to_check, $this->super_login)) != 0) {
		//if ($this->super_password === md5($local_password_to_check)) {
		if ($this->super_password == md5($local_password_to_check)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}


	public function changeLocalPassword($actual_password, $new_password) {
		$tmp_benevole = new Benevole($_SESSION['benevole']['id']);

		if ($tmp_benevole->checkIsSuperAdmin() ||  $tmp_benevole->checkIsAdminOfFiliale($_SESSION['filiale']['id'])) {
			$this->newLocalPassword($new_password);
			return TRUE;
		} else {
			if ($this->checkLocalPassword($actual_password)) {
				$this->newLocalPassword($new_password);
				return TRUE;
			} else  {
				return FALSE;
			}
		}


	}


	public static function get_id_from_super_login($super_login) {

		global $dbh;
		$super_login = $dbh->quote($super_login);
		$sql = "SELECT id FROM benevole WHERE super_login=$super_login";

		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		if ($result != false) {
			return $result['id'];
		} else {
			return FALSE;
		}
	} // class.Benevole.func.get_id_from_super_login


	public function set_filiale_benevole_as_active() {

	}


	public function disponibilite_exists($categorie, $jour_semaine, $periode_journee) {
		$load_needed_class_and_interface = load_class_and_interface(array('Benevole_Disponibilite_Categorie', 'Periode_Journee', 'Jour_Semaine'));

		if ($categorie instanceof Benevole_Disponibilite_Categorie) {
			$categorie_nom = $categorie->get_nom();
			$categorie = $categorie->get_id();
		} elseif (is_numeric($categorie)) {
			$categorie_tmp = new Benevole_Disponibilite_Categorie($categorie);
			$categorie_nom = $categorie_tmp->get_nom();
		} else {
			$categorie = Benevole_Disponibilite_Categorie::get_id_from_nom($categorie);
		}

		if ($jour_semaine instanceof Jour_Semaine) {
			$jour_semaine = $jour_semaine->get_id();
		} elseif (is_numeric($jour_semaine)) {

		} else {
			die();
		}

		if ($periode_journee instanceof Periode_Journee) {
			$periode_journee = $periode_journee->get_id();
		} elseif (is_numeric($periode_journee)) {

		} else {
			die();
		}


		global $dbh;
		$sql = "SELECT * FROM benevole_disponibilite_standard ";
		$sql .= " WHERE benevole_disponibilite_standard.id_categorie=" . $categorie;
		$sql .= " AND benevole_disponibilite_standard.id_benevole=" . $this->get_id($_SESSION['filiale']['id']);
		$sql .= " AND benevole_disponibilite_standard.id_jour_semaine=" . $jour_semaine;
		$sql .= " AND benevole_disponibilite_standard.id_periode_journee=" . $periode_journee;

		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		if ($result != false) {
			return TRUE;
		} else {
			return FALSE;
		}
	} // class.Benevole.func.disponbilite_exists


	public function is_active_in_filiale($id_filiale) {
		if ($id_filiale instanceof Filiale) {
			$id_filiale = $id_filiale->get_id();
		} elseif (is_numeric($id_filiale) && Filiale::id_exists($id_filiale)) {

		} else {
			die();
		}

		global $dbh;

		$sql = "SELECT * ";
		$sql .= " FROM benevole_participation_filiale ";
		$sql .= " WHERE id_benevole=" . $this->id;
		$sql .= " AND id_filiale=" . $id_filiale;

		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		if ($result != false) {
			if ($result['is_active'] == 1) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}


	public function has_email() {

		if ($this->email != '') {
			return $this->email;
		} else {
			return FALSE;
		}

	}

	private function return_pair_key_value() {

		if (isset($_SESSION['filiale']['id']) && isset($_SESSION['benevole']['id']) && Benevole::id_exists($this->get_id(), $_SESSION['filiale']['id'])) {

			//$tmp_array['id']['value'] = $this->id;
			$tmp_array['super_login']['value'] = $this->super_login;
			$tmp_array['titre']['value'] = $this->titre;
			$tmp_array['nom']['value'] = $this->nom;
			$tmp_array['prenom']['value'] = $this->prenom;
			$tmp_array['adresse']['value'] = $this->adresse;
			$tmp_array['adresse_complement']['value'] = $this->adresse_complement;
			$tmp_array['npa']['value'] = $this->npa;
			$tmp_array['ville']['value'] = $this->ville;
			$tmp_array['pays']['value'] = $this->pays;
			$tmp_array['tel_fixe']['value'] = $this->tel_fixe;
			$tmp_array['tel_professionnel']['value'] = $this->tel_professionnel;
			$tmp_array['tel_mobile']['value'] = $this->tel_mobile;
			$tmp_array['email']['value'] = $this->email;
			$tmp_array['iban']['value'] = $this->iban;
			$tmp_array['ccp']['value'] = $this->ccp;
			$tmp_array['info_diverses']['value'] = $this->info_diverses;
			$tmp_array['is_super_admin']['value'] = $this->is_super_admin;

			//donnee des filiales
			foreach ($this->array_participation_in_filiale as $details_participation_filiale) {
				if ($details_participation_filiale['id_filiale'] == $_SESSION['filiale']['id']) {
					$tmp_array['id']['value'] = $details_participation_filiale['id'];
					$tmp_array['is_active']['value'] = $details_participation_filiale['is_active'];
					$tmp_array['filiale_login']['value'] = $details_participation_filiale['filiale_login'];
					$tmp_array['id_benevole']['value'] = $details_participation_filiale['id_benevole'];
					$tmp_array['is_permanencier']['value'] = $details_participation_filiale['is_permanencier'];
					$tmp_array['is_transporteur']['value'] = $details_participation_filiale['is_transporteur'];
					$tmp_array['do_transports_locaux']['value'] = $details_participation_filiale['do_transports_locaux'];
					$tmp_array['do_transports_geneve']['value'] = $details_participation_filiale['do_transports_geneve'];
					$tmp_array['do_transports_lausanne']['value'] = $details_participation_filiale['do_transports_lausanne'];
					$tmp_array['do_transports_holidays']['value'] = $details_participation_filiale['do_transports_holidays'];
					$tmp_array['is_administrateur_filiale']['value'] = $details_participation_filiale['is_administrateur_filiale'];
					$tmp_array['has_external_login']['value'] = $details_participation_filiale['has_external_login'];

					break;
				}
			}

			//donnees disponibilites
			$tmp_benevole = new Benevole(Benevole::get_super_id_benvole_from_id_benevole_filiale($tmp_array['id']['value']));

			global $dbh;
			$sql = "SELECT id, nom FROM benevole_disponibilite_categorie";
			$sth = $dbh->query($sql);
			$categorie_dispo_array = $sth->fetchAll(PDO::FETCH_ASSOC);

			foreach ($categorie_dispo_array as $categorie_dispo) {
				for ($i=1; $i<=7; $i++) {
					for ($j=1; $j<=3; $j++) {
						if ($tmp_benevole->disponibilite_exists($categorie_dispo['id'], $i, $j)) {
							$tmp_array['dispo_' . $categorie_dispo['nom'] . '-jour_' . $i. '-periode_' . $j]['value'] = 1;
						} else {
							$tmp_array['dispo_' . $categorie_dispo['nom'] . '-jour_' . $i. '-periode_' . $j]['value'] = 0;
						}

						$tmp_array['dispo_' . $categorie_dispo['nom'] . '-jour_' . $i. '-periode_' . $j]['class'] = 'benevole_disponibilite_standard';
						$tmp_array['dispo_' . $categorie_dispo['nom'] . '-jour_' . $i. '-periode_' . $j]['id_categorie'] = $categorie_dispo['id'];
						$tmp_array['dispo_' . $categorie_dispo['nom'] . '-jour_' . $i. '-periode_' . $j]['nom_categorie'] = $categorie_dispo['nom'];
						$tmp_array['dispo_' . $categorie_dispo['nom'] . '-jour_' . $i. '-periode_' . $j]['id_jour_semaine'] = $i;
						$tmp_array['dispo_' . $categorie_dispo['nom'] . '-jour_' . $i. '-periode_' . $j]['id_periode_journee'] = $j;

					}
				}
			}

			return $tmp_array;
		}
	} // class.Benevole.func.return_pair_key_value


	public static function form($action, $data_to_display='') {

		if (is_array($data_to_display)) {

		} elseif (is_numeric($data_to_display) && Benevole::id_exists(Benevole::get_super_id_benvole_from_id_benevole_filiale($data_to_display))) {
			//numero de beneficaire
			$tmp_benevole = new Benevole($data_to_display);
			unset($data_to_display);
			$data_to_display = $tmp_benevole->return_pair_key_value();
		} elseif ($data_to_display instanceof Benevole) {
			//convertir en un tableau data_to_display_habituel
			$data_to_display = $data_to_display->return_pair_key_value();
		} else {
			$data_to_display = array();
		}

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
			case "change_password":
				echo Benevole::form_change_password($action);
				break;
			default:
				//echo Benevole::form_base();
		}

	} // class.Benevole.func.form


	private static function form_base($action, $data_to_display='') {
		$tmp_benevole = new Benevole($_SESSION['benevole']['id']);
		//retourne le code html du formulaire
		unset($_POST);
		$html_code = '';

		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}

		$html_code .= '<form id="benevole_' . $action . '" action="" method="post">';
			$html_code .= '<fieldset id="benevole_identite">';
				$html_code .= '<legend>Identité</legend>';

				$html_code .= '<p>';
					$html_code .= '<label for="is_active">Actif</label>';
					$html_code .= '<input type="hidden" value="0" name="is_active">';
					if ( isset($data_to_display['is_active']['value']) ) {
						$html_code .= add_FormElement_input('checkbox', 'is_active', '',  $data_to_display['is_active']['value']);
					} else {
						$html_code .= add_FormElement_input('checkbox', 'is_active', '',  '');
					}
				$html_code .= '</p>';


				$html_code .= '<p>';

					if (!isset($data_to_display['titre']['value'])) {
						$default_titre = 'Madame';
					} else {
						$default_titre = $data_to_display['titre']['value'];
					}

					$html_code .= add_FormElement_select('titre', '', array('Madame', 'Monsieur', 'Mademoiselle'), $default_titre);

				$html_code .= '</p>';

				$html_code .= '<p>';
					$html_code .= '<label for="nom">Nom de famille</label>';
						if ( isset($data_to_display['nom']['value']) ) {
							$html_code .= add_FormElement_input('text', 'nom', array('input_nom', 'disableAutoComplete', 'required'), $data_to_display['nom']['value']);
						} else {
							$html_code .= add_FormElement_input('text', 'nom', array('input_nom', 'disableAutoComplete', 'required'), '');
						}
				$html_code .= '</p>';

				$html_code .= '<p>';
					$html_code .= '<label for="prenom">Prénom</label>';
					if ( isset($data_to_display['prenom']['value']) ) {
						$html_code .= add_FormElement_input('text', 'prenom', array('input_nom', 'disableAutoComplete', 'required'), $data_to_display['prenom']['value']);
					} else {
						$html_code .= add_FormElement_input('text', 'prenom', array('input_nom', 'disableAutoComplete', 'required'), '');
					}
				$html_code .= '</p>';


				if ($action == 'add') {
					$html_code .= '<p>';
					$html_code .= '<label for="super_password">Mot de passe désiré</label>';
					$html_code .= add_FormElement_input('password', 'super_password', array('disableAutoComplete', 'required'), '');
					$html_code .= '</p>';
				}

			$html_code .= '</fieldset>';

			$html_code .= '<fieldset id="benevole_adresse">';
				$html_code .= '<legend>Adresse</legend>';

				$html_code .= '<p>';
					$html_code .= '<label for="adresse">Adresse</label>';
					if ( isset($data_to_display['adresse']['value']) ) {
						$html_code .= add_FormElement_input('text', 'adresse', array('input_adresse', 'disableAutoComplete', 'required'), $data_to_display['adresse']['value']);
					} else {
						$html_code .= add_FormElement_input('text', 'adresse', array('input_adresse', 'disableAutoComplete', 'required'), '');
					}
				$html_code .= '</p>';

				$html_code .= '<p>';
					$html_code .= '<label for="adresse_complement">Complément d\'adresse</label>';
					if ( isset($data_to_display['adresse']['value']) ) {
						$html_code .= add_FormElement_input('text', 'adresse_complement', array('input_adresse', 'disableAutoComplete'), $data_to_display['adresse_complement']['value']);
					} else {
						$html_code .= add_FormElement_input('text', 'adresse_complement', array('input_adresse', 'disableAutoComplete'), '');
					}
				$html_code .= '</p>';

				$html_code .= '<p>';
					$html_code .= '<label for="npa">Code postal</label>';
					if ( isset($data_to_display['npa']['value']) ) {
						$html_code .= add_FormElement_input('text', 'npa', array('input_npa', 'disableAutoComplete', 'required'), $data_to_display['npa']['value']);
					} else {
						$html_code .= add_FormElement_input('text', 'npa', array('input_npa', 'disableAutoComplete', 'required'), '');
					}

					$html_code .= '<label for="ville">Ville</label>';
					if ( isset($data_to_display['ville']['value']) ) {
						$html_code .= add_FormElement_input('text', 'ville', array('input_ville', 'required'), $data_to_display['ville']['value']);
					} else {
						$html_code .= add_FormElement_input('text', 'ville', array('input_ville', 'required'), '');
					}
				$html_code .= '</p>';

				$html_code .= '<p>';
					$html_code .= '<label for="pays">Pays</label>';

					if (!isset( $data_to_display['pays']['value']) ||  $data_to_display['pays']['value'] == '') {
						$html_code .= add_FormElement_input('text', 'pays', array('input_pays', 'disableAutoComplete', 'required'), 'Suisse');
					} else {
						$html_code .= add_FormElement_input('text', 'pays', array('input_pays', 'disableAutoComplete', 'required'), $data_to_display['pays']['value']);
					}

				$html_code .= '</p>';

			$html_code .= '</fieldset>';


			$html_code .= '<fieldset id="benevole_telephone">';
				$html_code .= '<legend>Téléphone(s)</legend>';

				$html_code .= '<p>';
					$html_code .= '<label for="tel_fixe">Téléphone fixe</label>';
					if ( isset($data_to_display['tel_fixe']['value']) ) {
						$html_code .= add_FormElement_input('text', 'tel_fixe', array('input_tel', 'disableAutoComplete'), format_tel($data_to_display['tel_fixe']['value']));
					} else {
						$html_code .= add_FormElement_input('text', 'tel_fixe', array('input_tel', 'disableAutoComplete'), '');
					}
				$html_code .= '</p>';

				$html_code .= '<p>';
					$html_code .= '<label for="tel_professionnel">Téléphone professionnel</label>';
					if ( isset($data_to_display['tel_professionnel']['value']) ) {
						$html_code .= add_FormElement_input('text', 'tel_professionnel', array('input_tel', 'disableAutoComplete'), format_tel($data_to_display['tel_professionnel']['value']));
					} else {
						$html_code .= add_FormElement_input('text', 'tel_professionnel', array('input_tel', 'disableAutoComplete'), '');
					}
				$html_code .= '</p>';

				$html_code .= '<p>';
					$html_code .= '<label for="tel_mobile">Téléphone mobile (natel)</label>';
					if ( isset($data_to_display['tel_mobile']['value']) ) {
						$html_code .= add_FormElement_input('text', 'tel_mobile', array('input_tel', 'disableAutoComplete'), format_tel($data_to_display['tel_mobile']['value']));
					} else {
						$html_code .= add_FormElement_input('text', 'tel_mobile', array('input_tel', 'disableAutoComplete'), '');
					}
				$html_code .= '</p>';
			$html_code .= '</fieldset>';

			$html_code .= '<fieldset id="benevole_email">';
				$html_code .= '<legend>E-mail</legend>';

				$html_code .= '<p>';
					$html_code .= '<label for="email">Adresse e-mail</label>';
					if ( isset($data_to_display['email']['value']) ) {
						$html_code .= add_FormElement_input('text', 'email', array('input_adresse', 'disableAutoComplete'), $data_to_display['email']['value']);
					} else {
						$html_code .= add_FormElement_input('text', 'email', array('input_adresse', 'disableAutoComplete'), '');
					}
				$html_code .= '</p>';

			$html_code .= '</fieldset>';

			$tmp_filiale = new Filiale($_SESSION['filiale']['id']);

			$html_code .= '<fieldset id="benevole_affiliation">';
				$html_code .= '<legend>Affiliation avec la filiale : '. $tmp_filiale->get_nom() . '</legend>';

				$html_code .= '<p>';
					$html_code .= '<label for="is_permanencier">Permanencier</label>';
					$html_code .= '<input type="hidden" value="0" name="is_permanencier">';
					if ( isset($data_to_display['is_permanencier']['value']) ) {
						$html_code .= add_FormElement_input('checkbox', 'is_permanencier', '',  $data_to_display['is_permanencier']['value']);
					} else {
						$html_code .= add_FormElement_input('checkbox', 'is_permanencier', '',  '');
					}

					if ($action == 'edit' && $data_to_display['is_permanencier']['value'] == 1) {
						$html_code .= '<fieldset id="benevole_disponibilite_permanence">';
							$html_code .= '<legend>Disponibilités pour la permanence</legend>';
							$html_code .= Benevole::form_disponibilite($action, $data_to_display, 'permanence');
						$html_code .= '</fieldset>';
					}
				$html_code .= '</p>';

				$html_code .= '<p>';
					$html_code .= '<label for="is_transporteur">Transporteur</label>';
					$html_code .= '<input type="hidden" value="0" name="is_transporteur">';
					if ( isset($data_to_display['is_transporteur']['value']) ) {
						$html_code .= add_FormElement_input('checkbox', 'is_transporteur', '',  $data_to_display['is_transporteur']['value']);
					} else {
						$html_code .= add_FormElement_input('checkbox', 'is_transporteur', '',  '');
					}

					if ($action == 'edit' && $data_to_display['is_transporteur']['value'] == 1) {

						$html_code .= '<fieldset id="benevole_disponibilite_transport">';
							$html_code .= '<legend>Disponibilités pour les transports</legend>';
							$html_code .= Benevole::form_disponibilite($action, $data_to_display, 'transport');
						$html_code .= '</fieldset>';

						$html_code .= '<fieldset id="benevole_disponibilite_appel">';
							$html_code .= '<legend>Disponibilités pour les appels</legend>';
							$html_code .= Benevole::form_disponibilite($action, $data_to_display, 'appel');
						$html_code .= '</fieldset>';
					}

				$html_code .= '</p>';

					$html_code .= '<ul>';
						$html_code .= '<li>';
							$html_code .= '<label for="do_transport_locaux">Transports locaux</label>';
							$html_code .= '<input type="hidden" value="0" name="do_transports_locaux">';
							if ( isset($data_to_display['do_transports_locaux']['value']) ) {
								$html_code .= add_FormElement_input('checkbox', 'do_transports_locaux', '',  $data_to_display['do_transports_locaux']['value']);
							} else {
								$html_code .= add_FormElement_input('checkbox', 'do_transports_locaux', '',  '');
							}
						$html_code .= '</li>';

						$html_code .= '<li>';
							$html_code .= '<label for="do_transport_geneve">Transports Genève</label>';
							$html_code .= '<input type="hidden" value="0" name="do_transports_geneve">';
							if ( isset($data_to_display['do_transports_geneve']['value']) ) {
								$html_code .= add_FormElement_input('checkbox', 'do_transports_geneve', '',  $data_to_display['do_transports_geneve']['value']);
							} else {
								$html_code .= add_FormElement_input('checkbox', 'do_transports_geneve', '',  '');
							}
						$html_code .= '</li>';

						$html_code .= '<li>';
							$html_code .= '<label for="do_transport_lausanne">Transports Lausanne</label>';
							$html_code .= '<input type="hidden" value="0" name="do_transports_lausanne">';
							if ( isset($data_to_display['do_transports_lausanne']['value']) ) {
								$html_code .= add_FormElement_input('checkbox', 'do_transports_lausanne', '',  $data_to_display['do_transports_lausanne']['value']);
							} else {
								$html_code .= add_FormElement_input('checkbox', 'do_transports_lausanne', '',  '');
							}
						$html_code .= '</li>';

						$html_code .= '<li>';
							$html_code .= '<label for="do_transport_holidays">Transports pendant les vacances</label>';
							$html_code .= '<input type="hidden" value="0" name="do_transports_holidays">';
							if ( isset($data_to_display['do_transports_holidays']['value']) ) {
								$html_code .= add_FormElement_input('checkbox', 'do_transports_holidays', '',  $data_to_display['do_transports_holidays']['value']);
							} else {
								$html_code .= add_FormElement_input('checkbox', 'do_transports_holidays', '',  '');
							}
						$html_code .= '</li>';
					$html_code .= '</ul>';


				if ($action == 'add' || $tmp_benevole->checkIsSuperAdmin() || $tmp_benevole->checkIsAdminOfFiliale($_SESSION['filiale']['id'])) {
					$html_code .= '<p>';
						$html_code .= '<label for="is_administrateur_filiale">Administrateur de filiale</label>';
						$html_code .= '<input type="hidden" value="0" name="is_administrateur_filiale">';
						if ( isset($data_to_display['is_administrateur_filiale']['value']) ) {
							$html_code .= add_FormElement_input('checkbox', 'is_administrateur_filiale', '',  $data_to_display['is_administrateur_filiale']['value']);
						} else {
							$html_code .= add_FormElement_input('checkbox', 'is_administrateur_filiale', '',  '');
						}
					$html_code .= '</p>';

					$html_code .= '<p>';
						$html_code .= '<label for="has_external_login">Login externe possible</label>';
						$html_code .= '<input type="hidden" value="0" name="has_external_login">';
						if ( isset($data_to_display['has_external_login']['value']) ) {
							$html_code .= add_FormElement_input('checkbox', 'has_external_login', '',  $data_to_display['has_external_login']['value']);
						} else {
							$html_code .= add_FormElement_input('checkbox', 'has_external_login', '',  '');
						}
					$html_code .= '</p>';
				}

			$html_code .= '</fieldset>';


			//contraite beneficiaire
			if ($action == 'edit' && $data_to_display['is_transporteur']['value'] == 1) {

				$html_code .= '<fieldset>';
					$html_code .= '<legend>Contraintes passager</legend>';

				$load_needed_class_and_interface = load_class_and_interface(array('Beneficiaire'));

				global $dbh;

				$sql = "SELECT id, nom, prenom FROM beneficiaire ORDER BY nom";
				$sth = $dbh->query($sql);
				$result = $sth->fetchAll(PDO::FETCH_ASSOC);

				$html_code .= '<select id="benevole_contrainte_beneficiaire" name="benevole_contrainte_beneficiaire">';
					//empty line
					$html_code .= '<option></option>';

					foreach ($result as $row) {
						$html_code .= '<option value="' . $row['id'] . '">';
							$html_code .= mb_strtoupper(stripAccents($row['nom'])) . ', ' . $row['prenom'];
						$html_code .= '</option>';
					}

				$html_code .= '</select>';


				//contraite deja en place
				$sql = "SELECT * FROM contrainte_transporteur_beneficiaire ";
				$sql .= " WHERE id_transporteur=" . $data_to_display['id']['value'];

				$sth = $dbh->query($sql);

				$result = $sth->fetchAll(PDO::FETCH_ASSOC);

				if (count($result)>0) {
					$html_code .= '<table id="contraintes_beneficiaires">';
						$html_code .= '<thead>';
							$html_code .= '<tr>';
								$html_code .= '<th>Passager</th>';
								$html_code .= '<th>Annuler</th>';
							$html_code .= '</tr>';
						$html_code .= '</thead>';

						$html_code .= '<tbody>';

							foreach ($result as $row) {
								$tmp_beneficiaire = new Beneficiaire($row['id_beneficiaire']);
								$tmp_beneficiaire_nom_complet = $tmp_beneficiaire->get_nom_complet();

								$html_code .= '<tr>';

									$html_code .= '<td>';
										$html_code .= format_titre($tmp_beneficiaire_nom_complet['titre']) . ' ' . mb_strtoupper(stripAccents($tmp_beneficiaire_nom_complet['nom']));
									$html_code .= '</td>';

									$html_code .= '<td>';
										$html_code .= '<a href="?module=transporteur&action=cancel_constraint_beneficiaire&id_beneficiaire=' . $row['id_beneficiaire'] . '&id_transporteur=' . $data_to_display['id']['value'] . '">';
											$html_code .= 'Annuler';
										$html_code .= '</a>';
									$html_code .= '</td>';

								$html_code .= '</tr>';
							}

						$html_code .= '</tbody>';

					$html_code .= '</table>';
				}

				$html_code .= '</fieldset>';

			} // contrainte beneficiaire


			if ($action == 'add' || $tmp_benevole->checkIsSuperAdmin() || $tmp_benevole->checkIsAdminOfFiliale($_SESSION['filiale']['id'])) {
				$html_code .= '<fieldset id="benevole_rights">';
					$html_code .= '<legend>Particularités</legend>';

					$html_code .= '<p>';
						$html_code .= '<label for="is_super_admin">Super-Administrateur</label>';
						$html_code .= '<input type="hidden" value="0" name="is_super_admin">';
						if ( isset($data_to_display['is_super_admin']['value']) ) {
							$html_code .= add_FormElement_input('checkbox', 'is_super_admin', '',  $data_to_display['is_super_admin']['value']);
						} else {
							$html_code .= add_FormElement_input('checkbox', 'is_super_admin', '',  '');
						}
					$html_code .= '</p>';

					$html_code .= '<p>';
						$html_code .= '<label for="info_diverses">Informations diverses</label>';
						$html_code .= '<textarea id="info_diverses" name="info_diverses" rows="3" cols="50">';
							if ( isset($data_to_display['info_diverses']['value']) ) {
								$html_code .= $data_to_display['info_diverses']['value'];
							}
						$html_code .= '</textarea>';
					$html_code .= '</p>';
				$html_code .= '</fieldset>';
			}


			if ($action == 'add' || $tmp_benevole->checkIsSuperAdmin() || $tmp_benevole->checkIsAdminOfFiliale($_SESSION['filiale']['id'])) {
				$html_code .= '<fieldset id="benevole_relation_bancaire">';
					$html_code .= '<legend>Informations bancaires</legend>';

					$html_code .= '<p>';
						$html_code .= '<label for="iban">IBAN</label>';
						if ( isset($data_to_display['iban']['value']) ) {
							$html_code .= add_FormElement_input('text', 'iban', '', $data_to_display['iban']['value']);
						} else {
							$html_code .= add_FormElement_input('text', 'iban', '', '');
						}
					$html_code .= '</p>';

					$html_code .= '<p>';
						$html_code .= '<label for="ccp">CCP</label>';
						if ( isset($data_to_display['ccp']['value']) ) {
							$html_code .= add_FormElement_input('text', 'ccp', '', $data_to_display['ccp']['value']);
						} else {
							$html_code .= add_FormElement_input('text', 'ccp', '', '');
						}
					$html_code .= '</p>';

				$html_code .= '</fieldset>';
			}


			$html_code .= '<p>';

				if (isset($data_to_display['id']['value'])) {
					$html_code .= add_FormElement_input('hidden', 'id', '', $data_to_display['id']['value']);
				}

				$html_code .= add_FormElement_input('hidden', 'form', '', 'base');
				$html_code .= add_FormElement_input('hidden', 'module', '', 'benevole');
				$html_code .= add_FormElement_input('hidden', 'sub_module', '', '');
				$html_code .= add_FormElement_input('hidden', 'action', '', $action);

				$html_code .= '<input type="submit" value="Soumettre" />';
			$html_code .= '</p>';

		$html_code .= '</form>';

		$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));

		return $html_code;
	} //class.Benevole.form.base


	private static function form_choose($action, $data_to_display='') {
		unset($_POST);
		global $dbh;
		$sql = "SELECT benevole_participation_filiale.id, benevole.nom, benevole.prenom";
		$sql .= " FROM benevole_participation_filiale INNER JOIN benevole ON benevole_participation_filiale.id_benevole = benevole.id";
		$sql .= " WHERE benevole_participation_filiale.id_filiale=" . $_SESSION['filiale']['id'];
		$sql .= " ORDER BY nom";



		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		$html_code = '<form id="benevole_choose" action="" method="post">';
			$html_code .= '<select id="id" name="id">';
				foreach ($result as $row) {
					$html_code .= '<option value="' . $row['id'] . '">';
					$html_code .= mb_strtoupper(stripAccents($row['nom'])) . ', ' . $row['prenom'];
					$html_code .= '</option>';
				}
			$html_code .= '</select>';

			$html_code .= '<p>';
				$html_code .= add_FormElement_input('hidden', 'form', '', 'choose');
				$html_code .= add_FormElement_input('hidden', 'module', '', 'benevole');
				$html_code .= add_FormElement_input('hidden', 'sub_module', '', '""');
				$html_code .= add_FormElement_input('hidden', 'action', '', $action);

				$html_code .= '<input type="submit" value="Soumettre" />';
			$html_code .= '</p>';
		$html_code .= '</form>';

		return $html_code;
	} //class.Benevole.form.choose


	private static function form_list($action) {
		global $dbh;

		if (isset($_POST['search'])) {
			$sql = "SELECT benevole.*, benevole_participation_filiale.* ";
			$sql .= " FROM benevole_participation_filiale INNER JOIN benevole ON benevole_participation_filiale.id_benevole = benevole.id";
			$sql .= " WHERE (benevole.nom LIKE '%" . $_POST['search'] . "%' AND benevole_participation_filiale.id_filiale=" . $_SESSION['filiale']['id'] . ") ";
			$sql .= " OR (benevole.prenom LIKE '%" . $_POST['search'] . "%' AND benevole_participation_filiale.id_filiale=" . $_SESSION['filiale']['id'] . ") ";
			$sql .= " OR (benevole.ville LIKE '%" . $_POST['search'] . "%' AND benevole_participation_filiale.id_filiale=" . $_SESSION['filiale']['id'] . ") ";
			$sql .= " ORDER BY benevole.nom, benevole.prenom";
		} else {
			$sql = "SELECT benevole.*, benevole_participation_filiale.* ";
			$sql .= " FROM benevole_participation_filiale INNER JOIN benevole ON benevole_participation_filiale.id_benevole = benevole.id";
			$sql .= " WHERE benevole_participation_filiale.id_filiale=" . $_SESSION['filiale']['id'];
			$sql .= " ORDER BY benevole.nom, benevole.prenom";
		}

		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		$html_code = '';

		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}

		$html_code .= '<form id="benevole_seach_list"  action="" method="post">';
			$html_code .= '<p>';
				$html_code .= '<input type="text" id="search" name="search" />';

				$html_code .= add_FormElement_input('hidden', 'form', '', 'search');
				$html_code .= add_FormElement_input('hidden', 'module', '', 'benevole');
				$html_code .= add_FormElement_input('hidden', 'sub_module', '', '""');
				$html_code .= add_FormElement_input('hidden', 'action', '', 'list');

				$html_code .= '<input type="submit" value="Soumettre" />';

			$html_code .= '</p>';

			$tmp_permanencier = new Permanencier($_SESSION['benevole']['id_benevole_filiale']);
			if ($tmp_permanencier->checkIsAdminOfFiliale($_SESSION['filiale']['id'])) {
				$html_code .= '<p>';
					$html_code .= '<a href="?module=benevole&amp;action=add">Nouveau Bénévole</a>';
				$html_code .= '</p>';
			}

		$html_code .= '</form>';


		//liste des premieres lettres de nom de famille presentes
		$html_code .= '<p>';
			$alpha_letter = array();

			foreach ($result as $row) {
				if (!in_array(mb_strtoupper(substr(stripAccents($row['nom']), 0, 1)), $alpha_letter)) {
					$alpha_letter[] = mb_strtoupper(substr(stripAccents($row['nom']), 0, 1));
				}
			}

			foreach ($alpha_letter as $letter) {
				$html_code .= '<a href="#' . $letter . '">' . $letter . '</a> | ';
			}

		$html_code .= '</p>';


		//montre sur la map la position de tous les benevoles (disposant geocode)
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
				$sql .= " AND benevole.pays = geocode.pays ";
			$sql .= ")";
			$sql .= " LIMIT 10";

			$sth = $dbh->query($sql);
			$geocode_to_import = $sth->fetchAll(PDO::FETCH_ASSOC);

			//geocoding des adresses
			/*
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
			$sql .= " AND benevole.pays = geocode.pays ";
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
						foreach ($result as $row_2) {
							if ($row_2['id'] == $row['id']) {
								$html_code .= 'addMarkerWithGeocode(' . $row['lat'] . ',' . $row['lng'] . ',"' . $row['titre'] . ' ' . $row['nom'] . '<br /> ' . format_tel($row['tel_fixe']) . '<br />' . format_tel($row['tel_mobile']) . '<br />' . $type_transport . '");';
							}
						}

					}
				$html_code .= '});';
			$html_code .= '</script>';
			*/
		}


		$html_code .= '<table class="OddEven" id="list_all_benevole_active">';
			$html_code .= '<thead>';
				$html_code .= '<tr>';
					$html_code .= '<th>Nom</th>';
					$html_code .= '<th>Prénom</th>';
					$html_code .= '<th>Adresse</th>';
					$html_code .= '<th>Ville</th>';
					$html_code .= '<th>Téléphone fixe</th>';
					$html_code .= '<th>Téléphone mobile</th>';
					$html_code .= '<th>Transporteur</th>';
					$html_code .= '<th></th>'; //Editer le bénévole
				$html_code .= '</tr>';
			$html_code .= '</thead>';

		$html_code .= '<tbody>';

			$last_letter = '';

			foreach ($result as $row) {

				/*
				//class de benevole active/inactive
				if ($row['is_active'] == 0) {
					$class_benevole = 'benevole_inactive';
				} else {
					$class_benevole = 'benevole_active';
				}
				*/
				if ($row['is_active'] == 1) {

					//Rajoute une ligne HEAD avec la premiere lettre du nom pour les regroupement
					if (mb_strtoupper(stripAccents($last_letter)) <> mb_strtoupper(substr(stripAccents($row['nom']), 0, 1))) {

						$last_letter = mb_strtoupper(substr(stripAccents($row['nom']), 0, 1));

						$html_code .= '<tr>';
							$html_code .= '<th>';
								$html_code .= '<a name="' . $last_letter . '"></a><a href="#top">' . $last_letter . '</a>';
							$html_code .= '</th>';
						$html_code .= '</tr>';
					}

					//possede un mail ?
					if ($row['is_transporteur'] == 1 && $row['email'] != '') {
						$tag_email = '<img title="le chauffeur possède une adresse email" alt= title="le chauffeur possède une adresse email" src="./img/email.png" />';
					} else {
						$tag_email = '';
					}

					$class_benevole = 'benevole_active'; // comme les autres ne sont pas presentes
					$html_code .= '<tr>';
						$html_code .= '<td class="' . $class_benevole . '"><a class="" href="?module=benevole&amp;action=view&amp;id=' . $row['id'].  '">' . $row['nom'] . '</a>' . $tag_email . '</td>';
						$html_code .= '<td class="' . $class_benevole . '">' . $row['prenom'] .'</td>';
						$html_code .= '<td class="' . $class_benevole . '">' . format_adresse($row['adresse']) .'</td>';
						$html_code .= '<td class="' . $class_benevole . '">' . $row['ville'] .'</td>';
						$html_code .= '<td class="' . $class_benevole . '">' . format_tel($row['tel_fixe']) .'</td>';
						$html_code .= '<td class="' . $class_benevole . '">' . format_tel($row['tel_mobile']) .'</td>';


						if ($row['is_transporteur'] == 1) {
							$html_code .= '<td title="L=Locaux , Ge=Genève, La=Lausanne, V=Vacances" class="' . $class_benevole . '">';

							if ($row['do_transports_locaux'] == 1) {
								//$html_code .= '<strong>L</strong>|';
							} else {
								//$html_code .= '-|';
							}

							if ($row['do_transports_geneve'] == 1) {
								//$html_code .= '<strong>Ge</strong>|';
								$html_code .= '<img src="./img/geneve.png" />';
							} else {
								//$html_code .= '--|';
							}

							if ($row['do_transports_lausanne'] == 1) {
								//$html_code .= '<strong>La</strong>|';
								$html_code .= '<img src="./img/lausanne.png" />';
							} else {
								//$html_code .= '--|';
							}

							if ($row['do_transports_holidays'] == 1) {
								//$html_code .= '<strong>V</strong>|';
								$html_code .= '<img src="./img/vacances.png" />';
							} else {
								//$html_code .= '-|';
							}

							$html_code .='</td>';
						} else {
							$html_code .= '<td class="' . $class_benevole . '">' . '' .'</td>';
						}


						$html_code .= '<td><a href="?module=benevole&id=' . $row['id'] .'&action=edit">Modifier</a></td>';

					$html_code .= '</tr>';
				}
			}

		$html_code .= '</tbody>';
	$html_code .= '</table>';



	//benevoles inactifs
	$html_code .= '<p>';
		$html_code .= '<a href="" id="sh_benevoles_inactifs">';
			$html_code .= 'Afficher les bénévoles passifs';
		$html_code .= '</a>';
	$html_code .= '</p>';

	$html_code .= '<div id="benevoles_inactifs">';
		$html_code .= '<h1>Bénévoles passifs</h1>';

		$html_code .= '<table class="OddEven" id="list_all_benevole_inactive">';
				$html_code .= '<thead>';
					$html_code .= '<tr>';
						$html_code .= '<th>Nom</th>';
						$html_code .= '<th>Prénom</th>';
						$html_code .= '<th>Adresse</th>';
						$html_code .= '<th>Ville</th>';
						$html_code .= '<th>Téléphone fixe</th>';
						$html_code .= '<th>Téléphone mobile</th>';
						$html_code .= '<th>Transporteur</th>';
						$html_code .= '<th></th>'; //Editer le bénévole
					$html_code .= '</tr>';
				$html_code .= '</thead>';

			$html_code .= '<tbody>';

				$last_letter = '';

				foreach ($result as $row) {

					/*
					//class de benevole active/inactive
					if ($row['is_active'] == 0) {
						$class_benevole = 'benevole_inactive';
					} else {
						$class_benevole = 'benevole_active';
					}
					*/
					if ($row['is_active'] == 0) {

						//Rajoute une ligne HEAD avec la premiere lettre du nom pour les regroupement
						if (mb_strtoupper(stripAccents($last_letter)) <> mb_strtoupper(substr(stripAccents($row['nom']), 0, 1))) {

							$last_letter = mb_strtoupper(substr(stripAccents($row['nom']), 0, 1));

							$html_code .= '<tr>';
								$html_code .= '<th>';
									$html_code .= '<a name="' . $last_letter . '"></a><a href="#top">' . $last_letter . '</a>';
								$html_code .= '</th>';
							$html_code .= '</tr>';
						}

						//possede un mail ?
						if ($row['is_transporteur'] == 1 && $row['email'] != '') {
							$tag_email = '<img title="le chauffeur possède une adresse email" alt= title="le chauffeur possède une adresse email" src="./img/email.png" />';
						} else {
							$tag_email = '';
						}

						$class_benevole = 'benevole_inactive';
						$html_code .= '<tr>';
							$html_code .= '<td class="' . $class_benevole . '"><a class="" href="?module=benevole&amp;action=view&amp;id=' . $row['id'].  '">' . $row['nom'] . '</a>' . $tag_email . '</td>';
							$html_code .= '<td class="' . $class_benevole . '">' . $row['prenom'] .'</td>';
							$html_code .= '<td class="' . $class_benevole . '">' . format_adresse($row['adresse']) .'</td>';
							$html_code .= '<td class="' . $class_benevole . '">' . $row['ville'] .'</td>';
							$html_code .= '<td class="' . $class_benevole . '">' . format_tel($row['tel_fixe']) .'</td>';
							$html_code .= '<td class="' . $class_benevole . '">' . format_tel($row['tel_mobile']) .'</td>';


							if ($row['is_transporteur'] == 1) {
								$html_code .= '<td title="L=Locaux , Ge=Genève, La=Lausanne, V=Vacances" class="' . $class_benevole . '">';

								if ($row['do_transports_locaux'] == 1) {
									//$html_code .= '<strong>L</strong>|';
								} else {
									//$html_code .= '-|';
								}

								if ($row['do_transports_geneve'] == 1) {
									//$html_code .= '<strong>Ge</strong>|';
									$html_code .= '<img src="./img/geneve.png" />';
								} else {
									//$html_code .= '--|';
								}

								if ($row['do_transports_lausanne'] == 1) {
									//$html_code .= '<strong>La</strong>|';
									$html_code .= '<img src="./img/lausanne.png" />';
								} else {
									//$html_code .= '--|';
								}

								if ($row['do_transports_holidays'] == 1) {
									//$html_code .= '<strong>V</strong>|';
									$html_code .= '<img src="./img/vacances.png" />';
								} else {
									//$html_code .= '-|';
								}

								$html_code .='</td>';
							} else {
								$html_code .= '<td class="' . $class_benevole . '">' . '' .'</td>';
							}


							$html_code .= '<td><a href="?module=benevole&id=' . $row['id'] .'&action=edit">Modifier</a></td>';

						$html_code .= '</tr>';
					}
				}

			$html_code .= '</tbody>';
		$html_code .= '</table>';
	$html_code .= '</div>';




	$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));

	return $html_code;

	} //class.Benevole.form.list


	private static function form_view($action, $data_to_display='') {
		$html_code = '';

		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}

		$html_code .= '<p>';
			$html_code .= '<a href="?module=benevole&amp;action=edit&amp;id=' . $data_to_display['id']['value'] . '">Editer le bénévole</a>';
		$html_code .= '</p>';

		if ($data_to_display['is_transporteur']['value'] == 1) {
			$html_code .= '<p>';
				$html_code .= '<a href="?module=transporteur&amp;action=transports_potentiels&amp;id=' . $data_to_display['id']['value'] . '">Suggestion de transport pour ce bénévole</a>';
			$html_code .= '</p>';
		}

		$tmp_benevole = new Benevole(Benevole::get_super_id_benvole_from_id_benevole_filiale($data_to_display['id']['value']));

		$html_code .= '<p>';

			$tmp_benevole = new Benevole(Benevole::get_super_id_benvole_from_id_benevole_filiale($data_to_display['id']['value']));
				$tmp_benevole_tel = $tmp_benevole->get_telephone();

			$html_code .= $data_to_display['titre']['value'] . '<br />';
			$html_code .= $data_to_display['prenom']['value'] . ' ' . $data_to_display['nom']['value'] . '<br />';

			if ($data_to_display['adresse']['value'] != '') {
				$html_code .= $data_to_display['adresse']['value'] . '<br />';
			}

			$html_code .= $data_to_display['npa']['value'] . ' ' . $data_to_display['ville']['value'] . '<br />';

			foreach ($tmp_benevole_tel as $index => $row) {
				$html_code .= str_replace('tel_', '', $index) . ' : ' . format_tel($row) . '<br />';
			}

		$html_code .= '</p>';

		//info diverses
		if ($data_to_display['info_diverses']['value'] != '') {
			$html_code .= '<p>';
				$html_code .= nl2br($data_to_display['info_diverses']['value']);
			$html_code .= '</p>';
		}

		/*
		if (checkInternetConnection()) {
			$html_code .= '<div id="map_benevole" class="map_google"></div>';

			$html_code .= '<script type="text/javascript">';
				$html_code .= '$(document).ready(function() {';
					$html_code .= '$(\'#map_benevole\').googleMap("' . $data_to_display['adresse']['value'] . ',' . $data_to_display['npa']['value'] . ',' . $data_to_display['ville']['value'] . '");';
				$html_code .= '});';
			$html_code .= '</script>';
		}
		*/

		if ($data_to_display['is_transporteur']['value'] == 1) {
			$html_code .= Benevole::form_disponibilite($action, $data_to_display, 'transport', TRUE);

			$tmp_transporteur = new Transporteur($data_to_display['id']['value']);
			$tmp_transporteur_nom_complet = $tmp_transporteur->get_nom_complet();
			$array_non_dispo_date_transport = $tmp_transporteur->get_NonDisponibiliteStandardDateTransport();


			$html_code .= '<form id="add_area_non_dispo_date_transport" action="" method="get">';

				$html_code .='<fieldset>';
					$html_code .= '<legend>Ajouter une plage indisponibilité</legend>';

					$html_code .='<p>';
						$html_code .= '<label>De: dd-mm-yyyy</label>';

						$html_code .= '<select id="date_from_day" name="date_from_day" class="plage_date">';
							$html_code .= '<option></option>';
							for($i=1; $i<=31; $i++) {
								$html_code .= '<option value="' . $i . '">';
									$html_code .= $i;
								$html_code .= '</option>';
							}
						$html_code .= '</select>';

						$html_code .= '<select id="date_from_month" name="date_from_month" class="plage_date">';
							$html_code .= '<option></option>';
							for($i=1; $i<=12; $i++) {
								$html_code .= '<option value="' . $i . '">';
									$html_code .= $i;
								$html_code .= '</option>';
							}
						$html_code .= '</select>';

						$html_code .= '<select id="date_from_year" name="date_from_year" class="plage_date">';
							$html_code .= '<option></option>';
							for($i=date('Y'); $i<=(date('Y'))+1; $i++) {
								$html_code .= '<option value="' . $i . '">';
									$html_code .= $i;
								$html_code .= '</option>';
							}
						$html_code .= '</select>';

					$html_code .='</p>';


					$html_code .='<p>';
						$html_code .= '<label>A: dd-mm-yyyy</label>';

						$html_code .= '<select id="date_to_day" name="date_to_day" class="plage_date">';
							$html_code .= '<option></option>';
							for($i=1; $i<=31; $i++) {
								$html_code .= '<option value="' . $i . '">';
									$html_code .= $i;
								$html_code .= '</option>';
							}
						$html_code .= '</select>';

						$html_code .= '<select id="date_to_month" name="date_to_month" class="plage_date">';
							$html_code .= '<option></option>';
							for($i=1; $i<=12; $i++) {
								$html_code .= '<option value="' . $i . '">';
									$html_code .= $i;
								$html_code .= '</option>';
							}
						$html_code .= '</select>';

						$html_code .= '<select id="date_to_year" name="date_to_year" class="plage_date">';
							$html_code .= '<option></option>';
							for($i=date('Y'); $i<=(date('Y'))+1; $i++) {
								$html_code .= '<option value="' . $i . '">';
									$html_code .= $i;
								$html_code .= '</option>';
							}
						$html_code .= '</select>';

						$html_code .= '<input type="submit" id="submit_add_plage_non_dispo_benevole" value="Ajouter la plage de vacances" />';

					$html_code .='</p>';

					$html_code .= add_FormElement_input('hidden', 'id', '', $data_to_display['id']['value']);
					$html_code .= add_FormElement_input('hidden', 'module', '', 'transporteur');
					$html_code .= add_FormElement_input('hidden', 'sub_module', '', 'area_non_dispo_dates_transport');
					$html_code .= add_FormElement_input('hidden', 'action', '', 'add');


					//calendrier individuel
					$html_code .= '<div id="calendars_link" class="clear-after">';
						$html_code .= calendrier(date('n'), date('Y'), $array_non_dispo_date_transport, 'non_dispo_transport_current_month', 'transport', $data_to_display);
						$html_code .= calendrier(date('n')+1, date('Y'), $array_non_dispo_date_transport, 'non_dispo_transport_next_month', 'transport', $data_to_display);
					$html_code .= '</div>';


				$html_code .= '</fieldset>';

			$html_code .= '</form>';

		}

		if ($data_to_display['is_permanencier']['value'] == 1) {
			$html_code .= Benevole::form_disponibilite($action, $data_to_display, 'permanence', TRUE);
		}



		//tableau des contraites beneficiaires
		if ($data_to_display['is_transporteur']['value'] == 1) {

			$load_needed_class_and_interface = load_class_and_interface(array('Beneficiaire'));

			global $dbh;

			//contraite deja en place
			$sql = "SELECT * FROM contrainte_transporteur_beneficiaire ";
			$sql .= " WHERE id_transporteur=" . $data_to_display['id']['value'];

			$sth = $dbh->query($sql);

			$result = $sth->fetchAll(PDO::FETCH_ASSOC);

			if (count($result)>0) {

				$html_code .= '<fieldset>';
					$html_code .= '<legend>Contraintes passager</legend>';

					$html_code .= '<table class="OddEven" id="contraintes_beneficiaires">';
						$html_code .= '<thead>';
							$html_code .= '<tr>';
								$html_code .= '<th>Passager</th>';
								$html_code .= '<th>Annuler</th>';
							$html_code .= '</tr>';
						$html_code .= '</thead>';

						$html_code .= '<tbody>';

							foreach ($result as $row) {
								$tmp_beneficiaire = new Beneficiaire($row['id_beneficiaire']);
								$tmp_beneficiaire_nom_complet = $tmp_beneficiaire->get_nom_complet();

								$html_code .= '<tr>';

									$html_code .= '<td>';
										$html_code .= $tmp_beneficiaire_nom_complet['titre'] . ' ' . mb_strtoupper(stripAccents($tmp_beneficiaire_nom_complet['nom']));
									$html_code .= '</td>';

									$html_code .= '<td>';
										$html_code .= '<a href="?module=transporteur&action=cancel_constraint_beneficiaire&id_beneficiaire=' . $row['id_beneficiaire'] . '&id_transporteur=' . $data_to_display['id']['value'] . '">';
											$html_code .= 'Annuler';
										$html_code .= '</a>';
									$html_code .= '</td>';

								$html_code .= '</tr>';
							}

						$html_code .= '</tbody>';

					$html_code .= '</table>';
				$html_code .= '</fieldset>';
			}

		} // contrainte beneficiaire


		global $dbh;


		// * transports futurs
		$sql = "SELECT transport_transporteur.*, transport.* ";
		$sql .= " FROM transport INNER JOIN transport_transporteur ON transport_transporteur.id_transport = transport.id ";
		$sql .= " WHERE transport_transporteur.id_transporteur=" . $data_to_display['id']['value'];
		$sql .= " AND transport.id_filiale=" . $_SESSION['filiale']['id'];
		$sql .= " AND transport.is_annule=0";
		$sql .= " AND transport.date_transport>=" . $dbh->quote(date('Y-m-d'));
		$sql .= " ORDER BY transport.date_transport ASC, transport.heure_debut ASC ";

		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);


		if (count($result) > 0) {
			$html_code .= '<h1>Futurs transports</h1>';
			$html_code .= '<table class="OddEven">';
				$html_code .= '<thead>';
					$html_code .= '<th>Date</th>';
					$html_code .= '<th>Heure</th>';
					$html_code .= '<th>Passager</th>';
					$html_code .= '<th>Ville départ</th>';
					$html_code .= '<th>Ville arrivée</th>';
					$html_code .= '<th></th>'; // Modifier le transport
					$html_code .= '<th></th>'; // Annuler le transport
				$html_code .= '</thead>';

				$html_code .= '<tbody>';
					foreach ($result as $row) {
						$html_code .= '<tr>';
							$html_code .= '<td>';
								$html_code .= '<a href="?module=transport&amp;action=view&amp;id=' . $row['id'] . '">';
									$html_code .= date_yyyymmdd_to_ddmmyyyy($row['date_transport']);
								$html_code .= '</a>';
							$html_code .= '</td>';

							$html_code .= '<td>';
								$html_code .= time_hhmmss_to_hhmm($row['heure_debut']);
							$html_code .= '</td>';

							$html_code .= '<td>';
								$html_code .= '<a class="link_dialog" href="?module=beneficiaire&action=view&id=' . $row['id_beneficiaire'] . '">';
									$tmp_beneficiaire = new Beneficiaire($row['id_beneficiaire']);
									$tmp_beneficiaire_nom_complet = $tmp_beneficiaire->get_nom_complet();

									$html_code .= format_titre($tmp_beneficiaire_nom_complet['titre']) . ' ';

									$html_code .= mb_strtoupper(stripAccents($tmp_beneficiaire_nom_complet['nom']));
								$html_code .= '</a>';
							$html_code .= '</td>';

							$point_depart = unserialize($row['point_depart']);
							$html_code .= '<td>';
								$html_code .= mb_strtoupper(stripAccents($point_depart['ville']));
							$html_code .= '</td>';

							$point_arrivee = unserialize($row['point_arrivee']);
							$html_code .= '<td>';
								$html_code .= mb_strtoupper(stripAccents($point_arrivee['ville']));
							$html_code .= '</td>';

							$html_code .= '<td>';
								$html_code .= '<a href="?module=transport&amp;action=edit&amp;id=' . $row['id'] . '">';
									$html_code .= 'Modifier';
								$html_code .= '</a>';
							$html_code .= '</td>';

							$html_code .= '<td>';
								$html_code .= '<a class="link_ajax_get" href="?module=transport&amp;action=cancel&amp;id=' . $row['id'] . '">';
									$html_code .= 'Annuler';
								$html_code .= '</a>';
							$html_code .= '</td>';
						$html_code .= '</tr>';
					}
				$html_code .= '</tbody>';
			$html_code .= '</table>';
		}



		//10 derniers transports passes
		$nbre_histo_transport_a_afficher = 10;
		$sql = "SELECT beneficiaire.*, transport.* ";
		$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport, beneficiaire ";
		$sql .= " WHERE transport.id_beneficiaire = beneficiaire.id ";
		$sql .= " AND transport.id_filiale=" . $_SESSION['filiale']['id'];
		$sql .= " AND transport.is_annule=0";
		$sql .= " AND transport_transporteur.id_transporteur = " . $data_to_display['id']['value'];
		$sql .= " AND transport.date_transport<" . $dbh->quote(date('Y-m-d'));
		$sql .= " ORDER BY transport.date_transport DESC, transport.heure_debut ASC ";
		$sql .= " LIMIT $nbre_histo_transport_a_afficher";

		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);


		if (count($result) > 0) {
			$html_code .= '<h1>' . $nbre_histo_transport_a_afficher . ' derniers transports</h1>';
			$html_code .= '<table class="OddEven">';
				$html_code .= '<thead>';
					$html_code .= '<th>Date</th>';
					$html_code .= '<th>Heure</th>';
					$html_code .= '<th>Passager</th>';
					$html_code .= '<th>Ville départ</th>';
					$html_code .= '<th>Ville arrivée</th>';
					$html_code .= '<th></th>'; // Modifier le transport
					$html_code .= '<th></th>'; // Annuler le transport
				$html_code .= '</thead>';

				$html_code .= '<tbody>';
					foreach ($result as $row) {
						$html_code .= '<tr>';
							$html_code .= '<td>';
								$html_code .= '<a href="?module=transport&amp;action=view&amp;id=' . $row['id'] . '">';
									$html_code .= date_yyyymmdd_to_ddmmyyyy($row['date_transport']);
								$html_code .= '</a>';
							$html_code .= '</td>';

							$html_code .= '<td>';
								$html_code .= time_hhmmss_to_hhmm($row['heure_debut']);
							$html_code .= '</td>';

							$html_code .= '<td>';
								$html_code .= '<a class="link_dialog" href="?module=beneficiaire&action=view&id=' . $row['id_beneficiaire'] . '">';


									$html_code .= format_titre($row['titre']) . ' ';

									$html_code .= mb_strtoupper(stripAccents($row['nom']));
								$html_code .= '</a>';
							$html_code .= '</td>';

							$point_depart = unserialize($row['point_depart']);
							$html_code .= '<td>';
								$html_code .= mb_strtoupper(stripAccents($point_depart['ville']));
							$html_code .= '</td>';

							$point_arrivee = unserialize($row['point_arrivee']);
							$html_code .= '<td>';
								$html_code .= mb_strtoupper(stripAccents($point_arrivee['ville']));
							$html_code .= '</td>';

							$html_code .= '<td>';
								$html_code .= '<a href="?module=transport&amp;action=edit&amp;id=' . $row['id'] . '">';
									$html_code .= 'Modifier';
								$html_code .= '</a>';
							$html_code .= '</td>';

							$html_code .= '<td>';
								$html_code .= '<a href="?module=transport&amp;action=cancel&amp;id=' . $row['id'] . '">';
									$html_code .= 'Annuler';
								$html_code .= '</a>';
							$html_code .= '</td>';
						$html_code .= '</tr>';
					}
				$html_code .= '</tbody>';
			$html_code .= '</table>';
		}


		// stats + graph
		/*
		if ($data_to_display['is_transporteur']['value'] == 1) {

			$load_needed_class_and_interface = load_class_and_interface(array('pData', 'pChart'));


			//remonte les trajets effectues et les km parcouru pour ce transporteur
			$sql = "SELECT MONTH(transport.date_transport) as month, YEAR(transport.date_transport) as year, COUNT(transport_transporteur.id_transport) as nbre_trajet, SUM(transport.nbre_kilometres) as total_km ";
			$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport ";
			$sql .= " WHERE transport.id_filiale=" . $_SESSION['filiale']['id'];
			$sql .= " AND transport_transporteur.id_transporteur=" . $tmp_transporteur->get_id_transporteur();
			$sql .= " AND transport.is_annule=0";
			$sql .= " GROUP BY MONTH(transport.date_transport), YEAR(transport.date_transport)";
			$sql .= " ORDER BY YEAR(transport.date_transport), MONTH(transport.date_transport)";

			$sth = $dbh->query($sql);
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);

			if (count($result) > 0) {
				//array_trajet
				foreach ($result as $row) {
					$serie_trajet[] = $row['nbre_trajet'];
					$serie_km[] = $row['total_km'];

					$label_x[] = $row['month'] .'-' . $row['year'];
				}

				// Dataset definition
				$DataSet = new pData;
				$DataSet->AddPoint($serie_trajet, 'Serie1');
				$DataSet->AddPoint($serie_km, 'Serie2');

				//x label
				$DataSet->AddPoint($label_x, 'Serie3');

				$DataSet->AddSerie("Serie1");  //axe de gauche
				$DataSet->SetAbsciseLabelSerie("Serie3");   //x_label

				//nom des series
				$DataSet->SetSerieName('Trajet', "Serie1");
				$DataSet->SetSerieName('Kilometres', "Serie2");

				$pchart_path = '../config/pChart/';


				// Initialise the graph
				$Test = new pChart(540,230);
				$Test->drawGraphAreaGradient(1,102,72,20,TARGET_BACKGROUND);


				// Prepare the graph area
				$Test->setFontProperties("fonts/tahoma.ttf",8);
				$Test->setGraphArea(60,40,485,190);

				// Initialise graph area
				$Test->setFontProperties($pchart_path ."Fonts/tahoma.ttf",8);

				// Draw the 0 line
				$Test->setFontProperties($pchart_path . "Fonts/tahoma.ttf",6);
				$Test->drawTreshold(0,143,55,72);


				// Draw Serie1
				$DataSet->AddSerie("Serie1");
				$DataSet->SetYAxisName("Trajets");
				$Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,213,217,221,TRUE,0,2,TRUE);
				$Test->drawGraphAreaGradient(12,131,96,-50);
				$Test->drawGrid(4,TRUE,230,230,230,10);
				$Test->setShadowProperties(3,3,0,0,0,30,4);
				$Test->drawCubicCurve($DataSet->GetData(),$DataSet->GetDataDescription());
				$Test->clearShadow();
				$Test->drawFilledCubicCurve($DataSet->GetData(),$DataSet->GetDataDescription(),.1,30);
				$Test->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),3,2,255,255,255);

				// Clear the scale
				$Test->clearScale();


				// Draw Serie2
				$DataSet->RemoveSerie("Serie1");
				$DataSet->AddSerie("Serie2");
				$DataSet->SetYAxisName("Kilometres");
				$Test->drawRightScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,213,217,221,TRUE,0,2, TRUE);
				$Test->drawFilledCubicCurve($DataSet->GetData(),$DataSet->GetDataDescription(),.1,30);
				$Test->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),3,2,255,255,255);


				// Legend
				$Test->setFontProperties($pchart_path . "Fonts/tahoma.ttf",8);
				$Test->drawLegend(430,5,$DataSet->GetDataDescription(),0,0,0,0,0,0,255,255,255,FALSE);

				// Title
				$Test->setFontProperties($pchart_path . "Fonts/tahoma.ttf",10);
				$Test->setShadowProperties(1,1,0,0,0);
				$Test->drawTitle(0,0,stripAccents($tmp_transporteur_nom_complet['nom']) . ', ' . stripAccents($tmp_transporteur_nom_complet['prenom']),255,255,255,550,30,TRUE);
				$Test->clearShadow();
				$Test->Render("img/stat_transporteur.png");


				$html_code .= '<h1>Statistiques du chauffeur</h1>';

				$html_code .= '<p>';
					$html_code .= '<img src="img/stat_transporteur.png" />';
				$html_code .= '</p>';
			}
		}
		*/
		
		$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));

		return $html_code;
	} //class.Benevole.form.view


	private static function form_change_password($action, $data_to_display='') {
		$html_code = '';

		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}

		$html_code .= '<form action="" method="post">';

			$html_code .= '<p>';
				$html_code .= '<label for="password_actual">Mot de passe actuel</label>';
				$html_code .= add_FormElement_input('password', 'password_actual', array('input_nom', 'disableAutoComplete'), '');
			$html_code .= '</p>';

			$html_code .= '<p>';
				$html_code .= '<label for="password_new">Nouveau mot de passe désiré</label>';
				$html_code .= add_FormElement_input('password', 'password_new', array('input_nom', 'disableAutoComplete'), '');
			$html_code .= '</p>';

			$html_code .= '<p>';
				$html_code .= '<label for="password_new_confirm">Confirmer le nouveau mot de passe désiré</label>';
				$html_code .= add_FormElement_input('password', 'password_new_confirm', array('input_nom', 'disableAutoComplete'), '');
			$html_code .= '</p>';

			$html_code .= '<p>';
				$html_code .= add_FormElement_input('hidden', 'id', '', $_SESSION['benevole']['id']);
				$html_code .= add_FormElement_input('hidden', 'module', '', 'benevole');
				$html_code .= add_FormElement_input('hidden', 'action', '', $action);
				$html_code .= '<input type="submit" value="Valider le changement" />';
			$html_code .= '</p>';
		$html_code .= '</form>';

		$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));

		return $html_code;
	}


	private static function form_disponibilite($action, $data_to_display='', $categorie, $with_form_tag = FALSE) {
		$load_needed_class_and_interface = load_class_and_interface(array('Transporteur'));

		if ($action == 'view') {
			$tmp_benevole = new Benevole(Benevole::get_super_id_benvole_from_id_benevole_filiale($data_to_display['id']['value']));
		}

		$txt_jour = array("Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche");

		$html_code = '';
		if ($with_form_tag === TRUE) {
			$html_code .= '<form id="form_dispo_' . $categorie . '">';
				$html_code .= '<fieldset>';
					$html_code .= '<legend>Disponibilités pour : ' . $categorie . '</legend>';
		}

						$html_code .= add_FormElement_input('hidden', 'form_disponibilite_' . $categorie, '', $categorie);
						$html_code .= '<table>';
							$html_code .= '<thead>';
								$html_code .= '<tr>';
									$html_code .= '<th></th>';

									foreach($txt_jour as $jour) {
										$html_code .= '<th>';
										$html_code .= $jour;
										$html_code .= '</th>';
									}

								$html_code .= '</tr>';
							$html_code .= '</thead>';

							$html_code .= '<tbody>';

								for ($i=1; $i<=3; $i++) {
									$html_code .= '<tr>';
										$tmp_periode_journee = new Periode_Journee($i);
										$html_code .= '<th>' . ucfirst($tmp_periode_journee->get_periode()) . '</th>';

										for ($j=1; $j<=count($txt_jour); $j++) {
											$html_code .= '<td>';
											$html_code .= '<input type="hidden" value="0" name="dispo_' . $categorie . '-jour_' . $j . '-periode_' . $i . '">';
											$html_code .= '<input type="checkbox" ';
											$html_code .= 'id="dispo_' . $categorie . '-jour_' . $j . '-periode_' . $i . '"' ;
											$html_code .= 'name="dispo_' . $categorie . '-jour_' . $j . '-periode_' . $i . '"' ;

											if ($action == "view") {
												$html_code .= 'disabled="disabled" ';

												if ($tmp_benevole->disponibilite_exists($categorie, $j, $i)) {
													$html_code .= 'checked="checked"';
												}
											}

											if ($data_to_display['dispo_' . $categorie . '-jour_' . $j . '-periode_' . $i]['value'] == 1) {
												$html_code .= 'checked="checked"';
											}

											$html_code .= ' /></td>';
										}

									$html_code .= '</tr>';
								}

							$html_code .= '</tbody>';
						$html_code .= '</table>';

		if ($with_form_tag === TRUE) {
					$html_code .= '</fieldset>';
			$html_code .= '</form>';
		}

		return $html_code;
	} // class.Benevole.form.disponibilite

} // class.Benevole

?>
