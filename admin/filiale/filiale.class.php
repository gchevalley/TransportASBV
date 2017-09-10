<?php

require_once( str_replace ( '\\', '/', dirname(dirname(__FILE__))) . '/class.declaration.php' );
load_class_and_interface(array('Benevole'));


class Filiale {

	private $id = 0;
	private $nom = '';
	private $adresse = '';
	private $adresse_complement = '';
	private $npa = '';
	private $ville = '';
	private $tel_permanence = '';
	private $tel_fax = '';
	private $email_permanence = '';
	private $email_backup = '';
	private $glm_weights = array(); //array ou string json

	private $default_km_cost = 0;
	private $default_compensation_rate = 0;
	private $default_min_cost = 0;

	private $facturation_header_line_1 = '';
	private $facturation_footer_line_1 = '';
	private $facturation_footer_line_2 = '';

	private $array_benevole = array();
	private $array_administrateur = array();
	private $array_permanencier = array();
	private $array_transporteur = array();
	private $array_benevole_transporteur = array();


	function __construct($id_filiale, $nom='', $adresse='', $adresse_complement='', $npa='', $ville='', $tel_permanence='', $tel_fax='', $email_permanence='', $default_km_cost=0, $default_compensation_rate=0, $default_min_cost=0, $facturation_header_line_1='', $facturation_footer_line_1='', $facturation_footer_line_2='') {

		if (checkID($id_filiale)) {

			$this->id = $id_filiale;
			$this->mountAttributsFromDB();

		} else { //creation de la nouvelle entite

			if (is_numeric($id_filiale)) {
				if (checkID($_SESSION['benevole']['id'])) {
					$tmp_benevole = new Benevole($_SESSION['benevole']['id']);

					if ($tmp_benevole->checkIsSuperAdmin()) {
						$this->addEntryDB($nom, $adresse, $adresse_complement, $npa, $ville, $tel_permanence, $tel_fax, $email_permanence, $default_km_cost, $default_compensation_rate, $default_min_cost);
					}
				}
			}
		}

	} // class.Filiale.func.__construct


	private function mountAttributsFromDB() {

		global $dbh;
		$sql = "SELECT * FROM filiale WHERE id=" . $this->id;

		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		$this->nom = $result['nom'];
		$this->adresse = $result['adresse'];
		$this->adresse_complement = $result['adresse_complement'];
		$this->npa = $result['npa'];
		$this->ville = $result['ville'];
		$this->tel_permanence = $result['tel_permanence'];
		$this->tel_fax = $result['tel_fax'];
		$this->email_permanence = $result['email_permanence'];
		$this->email_backup = $result['email_backup'];

		$this->glm_weights = json_decode($result['glm_weights']);

		$this->default_km_cost = $result['default_km_cost'];
		$this->default_compensation_rate = $result['default_compensation_rate'];
		$this->default_min_cost = $result['default_min_cost'];

		$this->facturation_header_line_1 = $result['facturation_header_line_1'];
		$this->facturation_footer_line_1 = $result['facturation_footer_line_1'];
		$this->facturation_footer_line_2 = $result['facturation_footer_line_2'];


	} //class.Filliale.func._mountAttributsFromDB


	private function addEntryDB($nom, $adresse='', $adresse_complement='', $npa='', $ville='', $tel_permanence='', $tel_fax='', $email_permanence='', $email_backup='', $default_km_cost=0, $default_compensation_rate=0, $default_min_cost=0, $facturation_header_line_1='', $facturation_footer_line_1='', $facturation_footer_line_2='') {
		//controle le super admin
		if (checkID($_SESSION['benevole']['id'])) {

			$actual_controle_permanencier = new Benevole($_SESSION['benevole']['id']);

			if ($actual_controle_permanencier->checkIsSuperAdmin()) {

				global $dbh;

				$nom = $dbh->quote($nom);
				$adresse = $dbh->quote($adresse);
				$adresse_complement = $dbh->quote($adresse_complement);
				$npa = $dbh->quote($npa);
				$ville = $dbh->quote($ville);
				$tel_permanence = $dbh->quote($tel_permanence);
				$tel_fax = $dbh->quote($tel_fax);
				$email_permanence = $dbh->quote($email_permanence);
				$email_backup = $dbh->quote($email_backup);
				$facturation_header_line_1 = $dbh->quote($facturation_header_line_1);
				$facturation_footer_line_1 = $dbh->quote($facturation_footer_line_1);
				$facturation_header_line_2 = $dbh->quote($facturation_footer_line_2);


				//chargement des parametres par défaut du model
				$glm_weights = array();
				$glm_weights['ponderation_du_poids_par_rapport_a_la_distance_du_trajet'] = 10;
				$glm_weights['poids_ville'] = 0.35;
				$glm_weights['poids_habitude'] = 0.05;
				$glm_weights['poids_transport_meme_journee'] = 0.25;
				$glm_weights['poids_nombre_de_trajets_du_mois'] = 0.25;
				$glm_weights['poids_nombre_de_kilometres_du_mois'] = 0.1;
				$glm_weights['poids_favoriser_les_transports_locaux_pour_les_transporteurs_locaux'] = 0.2;

				$glm_weights = $dbh->quote(json_encode($glm_weights));

				$today_date = $dbh->quote(date('Y-m-d'));
				$today_time = $dbh->quote(date('H:i:s'));

				$sql = "INSERT INTO filiale ";
				$sql .= " (nom, adresse, adresse_complement, npa, ville, tel_permanence, tel_fax, email_permanence, email_backup, glm_weights, default_km_cost, default_compensation_rate, default_min_cost, insert_date, insert_time, insert_user, facturation_header_line_1, facturation_footer_line_1, $facturation_footer_line_2) ";
				$sql .= " VALUES ($nom, $adresse, $adresse_complement, $npa, $ville, $tel_permanence, $tel_fax, $email_permanence, $email_backup, $glm_weights, $default_km_cost, $default_compensation_rate, $default_min_cost, $today_date, $today_time, " . $actual_controle_permanencier->get_id() . ", $facturation_header_line_1, $facturation_footer_line_1, $facturation_header_line_2)";

				$statut_query = $dbh->exec($sql);

				/*
				//mount l'object
				$this->id = $dbh->lastInsertId();
				$this->mountAttributsFromDB()
				*/


			} else {
				//pas les droits de super admin
				return FALSE;
				exit();
			}
		} else {
			//la variable de session pour connaitre le benevole n'est pas disponible
			return FALSE;
			exit();
		}
	} // class.Filiale.func.AddEntryDB


	public function editerAttributs($attr, $new_value) { //2 matrix ou 2 valeurs

		if (!is_numeric($_SESSION['benevole']['id']) || !is_numeric($_SESSION['filiale']['id']) || !Benevole::id_exists($_SESSION['benevole']['id']) || !Filiale::id_exists($_SESSION['filiale']['id'])) {
			die();
		}

		// controle le super admin
		$tmp_benevole = new Benevole($_SESSION['benevole']['id']);

		if ($tmp_benevole->checkIsSuperAdmin()) {

		} else {
			if ($tmp_benevole->checkIsAdminOfFiliale($_SESSION['filiale']['id'])) {

			} else {
				die();
			}
		}


		global $dbh;

		$sql = "UPDATE filiale ";

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
	} // class.Filiale.func.editerAttributs


	private function mountListBenevole() {

		global $dbh;

		//charge les id (niveau benevole) des benevoles qui sont admin de la filiale
		//vont ensuite etre monter sous forme d'object qui chargeront les attributs
		$sql = "SELECT id_benevole ";
		$sql .= " FROM benevole_participation_filiale INNER JOIN benevole ON benevole_participation_filiale.id_benevole = benevole.id ";
		$sql .= " WHERE id_filiale=" . $this->id;
		$sql .= " ORDER BY benevole.nom, benevole.prenom";

		$sth = $dbh->query($sql);

		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		foreach ($result as $benevole) {
			$this->array_benevole[] = new Benevole($benevole['id_benevole']);
		}

	} // class.Filiale.func.mountListBenevole


	private function mountListAdmin() {

		global $dbh;

		//charge les id (niveau benevole) des benevoles qui sont admin de la filiale
		//vont ensuite etre monter sous forme d'object qui chargeront les attributs
		$sql = "SELECT id_benevole ";
		$sql .= "FROM benevole_participation_filiale ";
		$sql .= "WHERE id_filiale=" . $this->id;
		$sql .= " AND is_administrateur_filiale=1";

		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		foreach ($result as $benevole) {
			$this->array_administrateur[] = new Benevole($benevole['id_benevole']);
		}

	} // class.Filiale.func.mountListAdmin


	private function mountListPermanencier() {

		global $dbh;

		//charge les id (niveau benevole) des benevoles qui sont admin de la filiale
		//vont ensuite etre monter sous forme d'object qui chargeront les attributs
		$sql = "SELECT id_benevole ";
		$sql .= "FROM benevole_participation_filiale ";
		$sql .= "WHERE id_filiale=" . $this->id;
		$sql .= " AND is_permanencier=1";

		$sth = $dbh->query($sql);

		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		foreach ($result as $benevole) {
			$this->array_permanencier[] = new Benevole($benevole['id_benevole']);
		}

	} // class.Filiale.func.mountListPermanencier



	private function mountListTransporteur() {

		global $dbh;

		//charge les id (niveau benevole) des benevoles qui sont admin de la filiale
		//vont ensuite etre monter sous forme d'object qui chargeront les attributs
		$sql = "SELECT id_benevole ";
		$sql .= "FROM benevole_participation_filiale ";
		$sql .= "WHERE id_filiale=" . $this->id;
		$sql .= " AND is_transporteur=1";

		$sth = $dbh->query($sql);

		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		foreach ($result as $benevole) {
			$this->array_transporteur[] = new Benevole($benevole['id_benevole']);
			$this->array_benevole_transporteur[] = new Transporteur($benevole['id_benevole'], $this->get_id());
		}

	} // class.Filiale.func.mountListTransporteur


	static function get_id_from_nom($nom) {
		if (!str_word_count($nom, 0) == 1) {
			return FALSE;
		}

		global $dbh;

		$sql = "SELECT id FROM filiale WHERE nom='" . ucfirst(strtolower($nom)) . "'";

		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		if ($result != false) {
			return $result['id'];
		} else {
			return FALSE;
		}
	} // class.Filiale.func.get_id_from_nom

	public function get_id() {
		return $this->id;
	} // class.Filiale.func.get_id

	public function get_nom() {
		return $this->nom;
	} // class.Filiale.func_get_nom


	public function get_ville() {
		return $this->ville;
	} // class.Filiale.func_get_ville


	public function get_list_transporteur() {
		$this->mountListTransporteur();
		return $this->array_benevole_transporteur;
	}


	public function get_stats($month, $year) {
		global $dbh;

		$sql = "SELECT transport_transporteur.*, transport.* ";
		$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport ";
		$sql .= " WHERE transport.id_filiale=" . $this->get_id();
		$sql .= " AND transport.is_annule=0";
		$sql .= " AND MONTH(transport.date_transport)=" . $month;
		$sql .= " AND YEAR(transport.date_transport)=" . $year;

		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);


		if (count($result) > 0) {

			$count_trajet_AS = 0;
			$count_trajet_AR = 0;
			$count_trajet_DOUBLE = 0;
			$count_trajet = 0;
			$sum_cout = 0;
			$sum_remboursement = 0;
			$sum_km = 0;

			foreach ($result as $row) {

				// count_trajet
				if ($row['aller_retour'] == 1) {
					if ($row['duree_approximative'] > 2) {
						$count_trajet += 4;
						$count_trajet_DOUBLE++;
					} else {
						$count_trajet += 2;
						$count_trajet_AR++;
					}
				} else {
					$count_trajet += 2;
					$count_trajet_AS++;
				}

				// sum_cout
				$sum_cout += $row['cout_trajet'] + $row['cout_variable'];

				// sum_remboursement
				$sum_remboursement += (($row['taux_remboursement_transporteur'] / 100) * ($row['cout_trajet'])) + $row['cout_variable'];

				//sum km
				$sum_km += $row['nbre_kilometres'];
			}
		} else {
			return FALSE;
		}

		return array('trajets' => $count_trajet, 'trajet_details' => array('trajet_AS' => $count_trajet_AS, 'trajet_AR' => $count_trajet_AR, 'trajet_DOUBLE' => $count_trajet_DOUBLE), 'km' => $sum_km, 'cout' => $sum_cout, 'remboursement' => $sum_remboursement);
	}


	public static function get_all_filiales($fields='') {
		global $dbh;

		if ($fields == '') {
			$sql = "SELECT * FROM filiale";
		} else {
			$sql = "SELECT $fields FROM filiale";
		}

		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		if (count($result) > 0) {
			return $result;
		} else {
			return FALSE;
		}
	}


	public static function id_exists($id_to_check) {
		if (checkID($id_to_check)) {
			global $dbh;
			$sql = "SELECT * FROM filiale WHERE id=" .$id_to_check;
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
	} //class.Filiale.func.id_exists


	public function get_tel_permanence() {
		return $this->tel_permanence;
	}


	public function get_email_permanence() {
		return $this->email_permanence;
	}


	public function get_email_backup() {
		if ($this->email_backup != '') {
			return $this->email_backup;
		} else {
			return FALSE;
		}
	}


	public function get_glm_weights() {
		//retourne directement le tableau
		return $this->glm_weights;
	}


	public function get_standard_prix_km() {
		return $this->default_km_cost;
	}


	public function get_standard_taux_compenation() {
		return $this->default_compensation_rate;
	}


	public function get_standard_prix_forfait_min() {
		return $this->default_min_cost;
	}


	public function get_facture_header() {
		return $this->facturation_header_line_1;
	}


	public function get_facture_footer() {
		$tmp_array[] = $this->facturation_footer_line_1;
		$tmp_array[] = $this->facturation_footer_line_2;

		return $tmp_array;
	}


	private function return_pair_key_value() {

		$tmp_array['id']['value']= $this->id;
		$tmp_array['nom']['value']= $this->nom;
		$tmp_array['adresse']['value']= $this->adresse;
		$tmp_array['adresse_complement']['value']= $this->adresse_complement;
		$tmp_array['npa']['value']= $this->npa;
		$tmp_array['ville']['value']= $this->ville;
		$tmp_array['tel_permanence']['value']= $this->tel_permanence;
		$tmp_array['tel_fax']['value']= $this->tel_fax;
		$tmp_array['email_permanence']['value']= $this->email_permanence;
		$tmp_array['email_backup']['value']= $this->email_backup;

		$tmp_array['glm_weights']['value']= json_encode($this->glm_weights);

		$tmp_array['default_km_cost']['value']= $this->default_km_cost;
		$tmp_array['default_compensation_rate']['value']= $this->default_compensation_rate;
		$tmp_array['default_min_cost']['value']= $this->default_min_cost;
		$tmp_array['facturation_header_line_1']['value']= $this->facturation_header_line_1;
		$tmp_array['facturation_footer_line_1']['value']= $this->facturation_footer_line_1;
		$tmp_array['facturation_footer_line_2']['value']= $this->facturation_footer_line_2;

		return $tmp_array;
	}


	public static function form($action, $data_to_display='') {

		if (is_array($data_to_display)) {

		} elseif (is_numeric($data_to_display) && Filiale::id_exists($data_to_display)) {
			//numero de beneficaire
			$tmp_filiale = new Filiale($data_to_display);
			unset($data_to_display);
			$data_to_display = $tmp_filiale->return_pair_key_value();
		} elseif ($data_to_display instanceof Filiale) {
			//convertir en un tableau data_to_display_habituel
			$data_to_display = $data_to_display->return_pair_key_value();
		} else {
			$data_to_display = array();
		}

		switch ($action) {
			case "add":
				echo Filiale::form_base($action, $data_to_display);
				break;
			case "view":

				break;
			case "edit":
				//s'assure que le beneficiaire est connu sinon charge une listbox de selection
				if (isset($data_to_display['id']['value']) && Filiale::id_exists($data_to_display['id']['value'])) {
					echo Filiale::form_base($action, $data_to_display);
				} else {
					echo Filiale::form_choose($action, $data_to_display);
				}
				break;
			case "list":
					//super admin only
					break;
			case "permanence":
				echo Filiale::form_permanence($action);
				break;
			case "facturation":
				echo Filiale::form_facturation($action, $data_to_display);
				break;
			case "new_facturation":
				unset($_SESSION['last_page']);
				unset($_GET);
				unset($_POST);
				echo Filiale::form_facturation($action);
				break;
			case "rapport":

				if ($_GET['sub_module'] == 'beneficiaire') {
					echo Filiale::form_rapport_beneficiaire($action);
				} elseif ($_GET['sub_module'] == 'transporteur') {
					echo Filiale::form_rapport_transporteur($action);
				}

				break;
			case "pdf_facture":

				if ($_GET['sub_module'] == 'beneficiaire') {
					echo Filiale::form_pdf_facture_beneficiaire($action, $data_to_display);
				} else {
					//error
				}

				break;
			case "pdf_remboursement":
				if ($_GET['sub_module'] == 'transporteur') {
					echo Filiale::form_pdf_remboursement_transporteur($action, $data_to_display);
				} else {
					//error
				}

				break;
			case "restore":
				echo Filiale::form_restore($action, $data_to_display);
				break;
			case "admin":
				echo Filiale::form_admin($action, $data_to_display);
				break;
			case "load_reference":
				echo Filiale::form_load_reference($action, $data_to_display);
				break;
			default:
				//echo Beneficiaire::form_base();
		}
	} // class.Filiale.func.form


	private static function form_base($action, $data_to_display='') {
		//retourne le code html du formulaire
		unset($_POST);
		$html_code = '';

		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}

		$html_code .= '<form id="filiale_' . $action . '" action="" method="post">';
			$html_code .= '<fieldset id="filiale_identite">';
				$html_code .= '<legend>Identité</legend>';

				$html_code .= '<p>';
					$html_code .= '<label for="nom">Nom de la filiale</label>';
					if ( isset($data_to_display['nom']['value']) ) {
						$html_code .= add_FormElement_input('text', 'nom', array('input_nom', 'disableAutoComplete'), $data_to_display['nom']['value']);
					} else {
						$html_code .= add_FormElement_input('text', 'nom', array('input_nom', 'disableAutoComplete'), '');
					}
				$html_code .= '</p>';

			$html_code .= '</fieldset>';

			$html_code .= '<fieldset id="filiale_adresse">';
				$html_code .= '<legend>Adresse</legend>';

				$html_code .= '<p>';
					$html_code .= '<label for="adresse">Adresse</label>';
					if ( isset($data_to_display['adresse']['value']) ) {
						$html_code .= add_FormElement_input('text', 'adresse', array('input_adresse', 'disableAutoComplete'), $data_to_display['adresse']['value']);
					} else {
						$html_code .= add_FormElement_input('text', 'adresse', array('input_adresse', 'disableAutoComplete'), '');
					}
				$html_code .= '</p>';

				$html_code .= '<p>';
					$html_code .= '<label for="adresse_complement">Complément d\'adresse</label>';
					if ( isset($data_to_display['adresse_complement']['value']) ) {
						$html_code .= add_FormElement_input('text', 'adresse_complement', array('input_adresse','disableAutoComplete'), $data_to_display['adresse_complement']['value']);
					} else {
						$html_code .= add_FormElement_input('text', 'adresse_complement', array('input_adresse','disableAutoComplete'), '');
					}
				$html_code .= '</p>';

				$html_code .= '<p>';
					$html_code .= '<label for="npa">Code postal</label>';
					if ( isset($data_to_display['npa']['value']) ) {
						$html_code .= add_FormElement_input('text', 'npa', array('input_npa', 'disableAutoComplete'), $data_to_display['npa']['value']);
					} else {
						$html_code .= add_FormElement_input('text', 'npa', array('input_npa', 'disableAutoComplete'), '');
					}

					$html_code .= '<label for="ville">Ville</label>';
					if ( isset($data_to_display['ville']['value']) ) {
						$html_code .= add_FormElement_input('text', 'ville', 'input_ville', $data_to_display['ville']['value']);
					} else {
						$html_code .= add_FormElement_input('text', 'ville', 'input_ville', '');
					}
				$html_code .= '</p>';

			$html_code .= '</fieldset>';


			$html_code .= '<fieldset id="filiale_contact">';
				$html_code .= '<legend>Contact</legend>';

				$html_code .= '<p>';
					$html_code .= '<label for="tel_permanence">Téléphone de la permanence</label>';
					if ( isset($data_to_display['tel_permanence']['value']) ) {
						$html_code .= add_FormElement_input('text', 'tel_permanence', array('input_tel', 'disableAutoComplete'), format_tel($data_to_display['tel_permanence']['value']));
					} else {
						$html_code .= add_FormElement_input('text', 'tel_permanence', array('input_tel', 'disableAutoComplete'), '');
					}
				$html_code .= '</p>';

				$html_code .= '<p>';
					$html_code .= '<label for="tel_fax">FAX</label>';
					if ( isset($data_to_display['tel_fax']['value']) ) {
						$html_code .= add_FormElement_input('text', 'tel_fax', array('input_tel', 'disableAutoComplete'), format_tel($data_to_display['tel_fax']['value']));
					} else {
						$html_code .= add_FormElement_input('text', 'tel_fax', array('input_tel', 'disableAutoComplete'), '');
					}
				$html_code .= '</p>';

				$html_code .= '<p>';
					$html_code .= '<label for="email_permanence">Email permanence</label>';
					if ( isset($data_to_display['email_permanence']['value']) ) {
						$html_code .= add_FormElement_input('text', 'email_permanence', array('input_email', 'disableAutoComplete'), $data_to_display['email_permanence']['value']);
					} else {
						$html_code .= add_FormElement_input('text', 'email_permanence', array('input_email', 'disableAutoComplete'), '');
					}
				$html_code .= '</p>';

				$html_code .= '<p>';
					$html_code .= '<label for="email_backup">Email backup</label>';
					if ( isset($data_to_display['email_backup']['value']) ) {
						$html_code .= add_FormElement_input('text', 'email_backup', array('input_email', 'disableAutoComplete'), $data_to_display['email_backup']['value']);
					} else {
						$html_code .= add_FormElement_input('text', 'email_backup', array('input_email', 'disableAutoComplete'), '');
					}
				$html_code .= '</p>';

			$html_code .= '</fieldset>';


			$html_code .= '<fieldset id="filiale_default_values">';
				$html_code .= '<legend>Valeurs par défaut pour la facturation</legend>';

				$html_code .= '<p>';
					$html_code .= '<label for="default_km_cost">Prix du kilomètre</label>';
					if ( isset($data_to_display['default_km_cost']['value']) ) {
						$html_code .= add_FormElement_input('text', 'default_km_cost', array('input_npa', 'disableAutoComplete'), $data_to_display['default_km_cost']['value']) . 'CHF';
					} else {
						$html_code .= add_FormElement_input('text', 'default_km_cost', array('input_npa', 'disableAutoComplete'), '') . 'CHF';
					}
				$html_code .= '</p>';

				$html_code .= '<p>';
					$html_code .= '<label for="default_compensation_rate">Taux de remboursement des chauffeurs</label>';
					if ( isset($data_to_display['default_compensation_rate']['value']) ) {
						$html_code .= add_FormElement_input('text', 'default_compensation_rate', array('input_npa', 'disableAutoComplete'), $data_to_display['default_compensation_rate']['value']) . '%';
					} else {
						$html_code .= add_FormElement_input('text', 'default_compensation_rate', array('input_npa', 'disableAutoComplete'), '') . '%';
					}
				$html_code .= '</p>';

				$html_code .= '<p>';
					$html_code .= '<label for="default_min_cost">Coût minimal (forfait)</label>';
					if ( isset($data_to_display['default_min_cost']['value']) ) {
						$html_code .= add_FormElement_input('text', 'default_min_cost', array('input_npa', 'disableAutoComplete'), $data_to_display['default_min_cost']['value']). 'CHF';
					} else {
						$html_code .= add_FormElement_input('text', 'default_min_cost', array('input_npa', 'disableAutoComplete'), ''). 'CHF';
					}
				$html_code .= '</p>';

			$html_code .= '</fieldset>';



			if ($action != 'add') {
				$html_code .= '<fieldset id="filiale_model">';
					$html_code .= '<legend>Modèle</legend>';

					$glm_weights = json_decode($data_to_display['glm_weights']['value']);

					foreach ($glm_weights as $index => $weight) {
						$html_code .= '<p>';
							$html_code .= '<label for="glm_weight_' . $index . '">' . ucfirst(str_replace('_', ' ', $index)) . '</label>';
							$html_code .= add_FormElement_input('text', 'glm_weight_' . $index, array('input_npa', 'disableAutoComplete'), $weight);
						$html_code .= '</p>';
					}
			}

			$html_code .= '</fieldset>';


			$html_code .= '<fieldset id="filiale_format_facture">';
				$html_code .= '<legend>Facture</legend>';

				$html_code .= '<p>';
					$html_code .= '<label for="facturation_header_line_1">En-tête des factures</label>';
					if ( isset($data_to_display['facturation_header_line_1']['value']) ) {
						$html_code .= add_FormElement_input('text', 'facturation_header_line_1', array('input_adresse', 'disableAutoComplete'), $data_to_display['facturation_header_line_1']['value']);
					} else {
						$html_code .= add_FormElement_input('text', 'facturation_header_line_1', array('input_adresse', 'disableAutoComplete'), '');
					}
				$html_code .= '</p>';

				$html_code .= '<p>';
					$html_code .= '<label for="facturation_footer_line_1">Pied de page des factures - ligne 1</label>';
					if ( isset($data_to_display['facturation_footer_line_1']['value']) ) {
						$html_code .= add_FormElement_input('text', 'facturation_footer_line_1', array('input_adresse', 'disableAutoComplete'), $data_to_display['facturation_footer_line_1']['value']);
					} else {
						$html_code .= add_FormElement_input('text', 'facturation_footer_line_1', array('input_adresse', 'disableAutoComplete'), '');
					}
				$html_code .= '</p>';

				$html_code .= '<p>';
					$html_code .= '<label for="facturation_footer_line_2">Pied de page des factures - ligne 2</label>';
					if ( isset($data_to_display['facturation_footer_line_2']['value']) ) {
						$html_code .= add_FormElement_input('text', 'facturation_footer_line_2', array('input_adresse', 'disableAutoComplete'), $data_to_display['facturation_footer_line_2']['value']);
					} else {
						$html_code .= add_FormElement_input('text', 'facturation_footer_line_2', array('input_adresse', 'disableAutoComplete'), '');
					}
				$html_code .= '</p>';


			$html_code .= '</fieldset>';


			$html_code .= '<p>';

				if (isset($data_to_display['id']['value'])) {
					$html_code .= add_FormElement_input('hidden', 'id', '', $data_to_display['id']['value']);
				}

				$html_code .= add_FormElement_input('hidden', 'form', '', 'base');
				$html_code .= add_FormElement_input('hidden', 'module', '', 'filiale');
				$html_code .= add_FormElement_input('hidden', 'sub_module', '', '""');
				$html_code .= add_FormElement_input('hidden', 'action', '', $action);

				$html_code .= '<input type="submit" value="Soumettre" />';
			$html_code .= '</p>';

		$html_code .= '</form>';

		$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));

		return $html_code;
	} //class.Filiale.form.base


	private static function form_choose($action, $data_to_display='') {
		unset($_POST);
		global $dbh;
		$sql = "SELECT * FROM filiale ORDER BY nom";
		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		$html_code = '<form id="filiale_choose" action="" method="post">';
			$html_code .= '<select id="id" name="id">';
				foreach ($result as $row) {
					$html_code .= '<option value="' . $row['id'] . '">';
					$html_code .= strtoupper($row['nom']);
					$html_code .= '</option>';
				}
			$html_code .= '</select>';

			$html_code .= '<p>';
				$html_code .= add_FormElement_input('hidden', 'form', '', 'choose');
				$html_code .= add_FormElement_input('hidden', 'module', '', 'filiale');
				$html_code .= add_FormElement_input('hidden', 'sub_module', '', '""');
				$html_code .= add_FormElement_input('hidden', 'action', '', $action);

				$html_code .= '<input type="submit" value="Soumettre" />';
			$html_code .= '</p>';
		$html_code .= '</form>';

		return ($html_code);
	} //class.Filiale.form.choose


	private static function form_facturation($action, $data_to_display='') {

		if (isset($_SESSION['last_page']['facturation_month']) && isset($_SESSION['last_page']['facturation_year'])) {
			$data_to_display = array();
			$data_to_display['facturation_month']['value'] = $_SESSION['last_page']['facturation_month'];
			$data_to_display['facturation_year']['value'] = $_SESSION['last_page']['facturation_year'];
		}

		if ($data_to_display == '')  {
			$data_to_display = array();
		}

		$html_code = '';

		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}


		//nouvelle facturation
		$html_code .= '<p>';
			$html_code .= '<a href="?module=filiale&amp;action=new_facturation">';
				$html_code .= 'Nouvelle facturation';
			$html_code .= '</a>';
		$html_code .= '</p>';

		if (isset($data_to_display['facturation_month']['value']) && is_numeric($data_to_display['facturation_month']['value']) && $data_to_display['facturation_month']['value'] > 0 && $data_to_display['facturation_month']['value'] <= 12 && isset($data_to_display['facturation_year']['value']) && is_numeric($data_to_display['facturation_year']['value']) && $data_to_display['facturation_year']['value'] > 0) {
		//if (isset($data_to_display['facturation_month']['value']) && is_numeric($data_to_display['facturation_month']['value']) && isset($data_to_display['facturation_year']['value']) && is_numeric($data_to_display['facturation_year']['value'])) {
			//remonte la totalite des transports du mois et de l'annee concernee
			global $dbh;

			$sql = "SELECT transport_transporteur.*, transport.* ";
			$sql .= " FROM transport LEFT JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport ";
			$sql .= " WHERE id_filiale=" . $_SESSION['filiale']['id'];
			$sql .= " AND MONTH(transport.date_transport)=" . $data_to_display['facturation_month']['value'];
			$sql .= " AND YEAR(transport.date_transport)=" . $data_to_display['facturation_year']['value'];
			$sql .= " ORDER BY transport.date_transport, transport.heure_debut";

			$sth = $dbh->query($sql);
			//transport avec & sans chauffeur grace au left join!
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);


			//presentation des resultats des mois choisis avec les differentes fonctionnalites de comptabilite
			if (count($result) > 0) {

				//mise en session des donnees pour simplifier le rechargement de la page futur
				unset($_SESSION['last_page']);
				$_SESSION['last_page']['module'] = 'filiale';
				$_SESSION['last_page']['action'] = 'facturation';
				$_SESSION['last_page']['facturation_month'] = (double) $data_to_display['facturation_month']['value'];
				$_SESSION['last_page']['facturation_year'] = (double) $data_to_display['facturation_year']['value'];

				//chargement des class
				$load_needed_class_and_interface = load_class_and_interface(array('Beneficiaire', 'Transporteur', 'Transport'));

				$tmp_filiale = new Filiale($_SESSION['filiale']['id']);
				$filiale_forfait_min = $tmp_filiale->get_standard_prix_forfait_min();

				//preparation du rapport du mois
				$html_code .= '<p>';

					$html_code .= 'Pré-visualiser le rapport mensuel ';

					$html_code .= '<a href="?module=filiale&amp;sub_module=beneficiaire&amp;action=rapport">';
						$html_code .= 'Passager';
					$html_code .= '</a>';

					$html_code .= ' | ';

					$html_code .= '<a href="?module=filiale&amp;sub_module=transporteur&amp;action=rapport">';
						$html_code .= 'Transporteur';
					$html_code .= '</a>';

				$html_code .= '</p>';


				//preparation des factures du mois

				//header
				$html_code .= '<table class="OddEven">';
					$html_code .= '<thead>';
						$html_code .= '<tr>';
							$html_code .= '<th>Date &amp; Heure</th>';
							$html_code .= '<th>Passager</th>';
							$html_code .= '<th>Chauffeur</th>';
							$html_code .= '<th>Ville départ</th>';
							$html_code .= '<th>Ville arrivée</th>';
							//$html_code .= '<th>Trajet</th>';
							$html_code .= '<th>Nombre de kilomètres</th>';
							$html_code .= '<th>Coût du trajet</th>';
							$html_code .= '<th>% taux</th>';
							//$html_code .= '<th>Remboursement du chauffeur</th>';

							//functions
							$html_code .= '<th></th>'; //Confirmer
							$html_code .= '<th></th>'; //Annuler
							$html_code .= '<th></th>'; //recalculer


						$html_code .= '</tr>';
					$html_code .= '</thead>';

					$html_code .= '<tbody>';

					$last_date_txt = '';

					foreach ($result as $row) {

						//header date
						if ($last_date_txt != $row['date_transport']) {
							$html_code .= '<tr>';
								$html_code .= '<th>' . date_yyyymmdd_to_ddmmyyyy($row['date_transport']) . '</th>';
							$html_code .= '</tr>';

							$last_date_txt = $row['date_transport'];
						}

						$tmp_transport = new Transport($row['id']);

						/*si lors de sa création il y a eu une coupure
						 * avec la connexion internet, il est nécessaire
						 * de recalculer, la distance et les couts
						 */
						if ($tmp_transport->get_cout_trajet() < $filiale_forfait_min || $tmp_transport->get_taux_remboursement_transporteur() == 0) {
							$tmp_transport->updateDistanceAndCost();
						}


						if ($row['is_annule'] == 1) {
							$class = 'transport_annule';
						} else {
							if ($row['is_cloture'] == 1) {
								$class = 'transport_cloture';
							} else {
								$class = 'transport_pending';
							}
						}

						$html_code .= '<tr>';

							//heure
							$html_code .= '<td class="' . $class . '">';
								$html_code .= '<a href="?module=transport&amp;action=edit&amp;id=' . $row['id'] . '">';
									$html_code .= time_hhmmss_to_hhmm($row['heure_debut']);
								$html_code .= '</a>';
							$html_code .= '</td>';

							//beneficiaire
							$html_code .= '<td class="' . $class . '">';
								if (is_numeric($row['id_beneficiaire']) && Beneficiaire::id_exists($row['id_beneficiaire'])) {
									$tmp_beneficiaire = new Beneficiaire($row['id_beneficiaire']);
									$tmp_beneficiaire_nom_complet = $tmp_beneficiaire->get_nom_complet();


									$html_code .= '<a href="?module=beneficiaire&amp;action=view&amp;id=' .  $row['id_beneficiaire'] . '">';
										$html_code .= format_titre($tmp_beneficiaire_nom_complet['titre']) . ' ' . mb_strtoupper(stripAccents($tmp_beneficiaire_nom_complet['nom']));
									$html_code .= '</a>';
								}
							$html_code .= '</td>';

							//transporteur
							$html_code .= '<td class="' . $class . '">';
								if (isset($row['id_transporteur']) && is_numeric($row['id_transporteur']) && Benevole::id_benevole_filiale_exists($row['id_transporteur'])) {
									$tmp_transporteur = new Transporteur($row['id_transporteur']);
									$tmp_transporteur_nom_complet = $tmp_transporteur->get_nom_complet();

									$html_code .= '<a href="?module=benevole&amp;action=view&amp;id=' . $row['id_transporteur'] . '">';
										$html_code .= format_titre($tmp_transporteur_nom_complet['titre']) . ' ' . mb_strtoupper(stripAccents($tmp_transporteur_nom_complet['nom']));
									$html_code .= '</a>';
								}
							$html_code .= '</td>';

							//ville depart
							$tmp_transport_adresse_depart = $tmp_transport->get_point_depart();
							$html_code .= '<td class="' . $class . '">';
								$html_code .= $tmp_transport_adresse_depart['ville'];
							$html_code .= '</td>';

							//ville arrivee
							$tmp_transport_adresse_arrivee = $tmp_transport->get_point_arrivee();
							$html_code .= '<td class="' . $class . '">';
								$html_code .= $tmp_transport_adresse_arrivee['ville'];
							$html_code .= '</td>';


							//km
							$html_code .= '<td class="' . $class . '">';
								$html_code .= number_format($tmp_transport->get_nbre_kilometres(), 0);

								$html_code .= '(';
									$type_trajet = format_type_trajet($row['aller_retour'], $row['duree_approximative']);

									if ($type_trajet == 'Simple trajet') {
										$type_trajet = 'AS';
									} elseif ($type_trajet == 'Aller-retour') {
										$type_trajet = 'A-R';
									} else {

									}

									$html_code .= $type_trajet;
								$html_code .= ')';

							$html_code .= '</td>';

							//cout
							$cout_trajet = $tmp_transport->get_cout_trajet();

							if ($cout_trajet < $filiale_forfait_min) {
								$class_error = 'error';
							} else {
								$class_error = '';
							}

							if ($tmp_transport->get_duree() > 2) {
								$tag_duree = ' (Double)';
							} else {
								$tag_duree = '';
							}

							$html_code .= '<td class="' . $class . ' ' . $class_error . '">';
								$html_code .= number_format($cout_trajet, 2) . $tag_duree;
							$html_code .= '</td>';


							//taux de remboursement
							$html_code .= '<td class="' . $class . '">';
								$html_code .= $tmp_transport->get_taux_remboursement_transporteur();
							$html_code .= '</td>';

							//cloturer/confirmer/valider
							$html_code .= '<td class="' . $class . '">';

								if ($row['is_annule'] == 1) {

								} else {
									if ($row['is_cloture'] == 1) {

									} else {
										$html_code .= '<a class="link_ajax_get" href="?module=transport&amp;action=close&amp;id=' . $row['id'] . '">';
											//$html_code .= 'Confirmer';
											$html_code .= '<img src="./img/validate.png" />';
										$html_code .= '</a>';
									}
								}

							$html_code .= '</td>';

							//annuler
							$html_code .= '<td class="' . $class . '">';
								if ($row['is_annule'] == 1) {

								} else {
									if ($row['is_cloture'] == 1) {

									} else {
										$html_code .= '<a class="link_ajax_get" href="?module=transport&amp;action=cancel&amp;id=' . $row['id'] . '">';
											//$html_code .= 'Annuler';
											$html_code .= '<img src="./img/cancel.png" />';
										$html_code .= '</a>';
									}
								}
							$html_code .= '</td>';



							//recalculer cout + km
							$html_code .= '<td class="' . $class . '">';
								if ($row['is_annule'] == 1) {

								} else {
									if ($row['is_cloture'] == 1) {

									} else {
										$html_code .= '<a class="link_ajax_get" href="?module=transport&amp;action=recalc&amp;id=' . $row['id'] . '">';
											$html_code .= 'Recalculer';
										$html_code .= '</a>';
									}
								}
							$html_code .= '</td>';

						$html_code .= '</tr>';
					}

					$html_code .= '</tbody>';
				$html_code .= '</table>';

			}


		} else {
			//charge le month picker
			$html_code .= '<form action="" method="post">';
				$html_code .= '<div id="facturation_month_picker" class="MonthPicker"></div>';

				$html_code .= '<p>';
					$html_code .= '<input id="month_picker_month" name="month_picker_month" type="hidden" value="' . date('n') .  '"/>';
				$html_code .= '</p>';

				$html_code .= '<p>';
					$html_code .= '<input id="month_picker_year" name="month_picker_year" type="hidden" value="' . date('Y') .  '"/>';
				$html_code .= '</p>';

				$html_code .= add_FormElement_input('hidden', 'form', '', 'facturation');
				$html_code .= add_FormElement_input('hidden', 'module', '', 'filiale');
				$html_code .= add_FormElement_input('hidden', 'sub_module', '', '""');
				$html_code .= add_FormElement_input('hidden', 'action', '', $action);

				$html_code .= '<input type="submit" value="Voir les factures pour le mois et l\'année sélectionnés" />';
			$html_code .= '</form>';


			$html_code .= '<form action="" method="post">';
				$html_code .= add_FormElement_input('hidden', 'form', '', 'facturation');
				$html_code .= add_FormElement_input('hidden', 'module', '', 'filiale');
				$html_code .= add_FormElement_input('hidden', 'sub_module', '', '""');
				$html_code .= add_FormElement_input('hidden', 'action', '', 'load_reference');

				$html_code .= '<p>';
					$html_code .= '<label for="refrence">Référence</label>';
					$html_code .= '<input id="refrence" name="refrence" type="text" class="input_adresse" />';
				$html_code .= '</p>';

				$html_code .= '<input type="submit" value="Voir les transports liés à cette référence" />';
			$html_code .= '</form>';
		}

		$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));

		return $html_code;
	} // class.Filiale.form.facturation




	private static function form_rapport_transporteur($action, $data_to_display='') {

		if (isset($_SESSION['last_page']['facturation_month']) && isset($_SESSION['last_page']['facturation_year'])) {
			$data_to_display = array();
			$data_to_display['facturation_month']['value'] = $_SESSION['last_page']['facturation_month'];
			$data_to_display['facturation_year']['value'] = $_SESSION['last_page']['facturation_year'];
		}

		if ($data_to_display == '')  {
			$data_to_display = array();
		}

		$html_code = '';

		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}

		if (isset($data_to_display['facturation_month']['value']) && is_numeric($data_to_display['facturation_month']['value']) && $data_to_display['facturation_month']['value'] > 0 && $data_to_display['facturation_month']['value'] <= 12 && isset($data_to_display['facturation_year']['value']) && is_numeric($data_to_display['facturation_year']['value']) && $data_to_display['facturation_year']['value'] > 0) {
			//remonte la totalite des transports du mois et de l'annee concernee
			global $dbh;

			//COUNT les transports, SUM km & remboursement
			$sql = "SELECT transport_transporteur.id_transporteur, COUNT(transport_transporteur.id_transport) AS nbre_trajets, SUM(transport.nbre_kilometres) AS sum_km, SUM(transport.cout_trajet * ((transport.taux_remboursement_transporteur)/100) + cout_variable) AS sum_cout ";
			$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport, benevole_participation_filiale, benevole ";
			$sql .= " WHERE transport.id_filiale=" . $_SESSION['filiale']['id'];
			$sql .= " AND transport.is_cloture=1";
			$sql .= " AND transport.is_annule=0";

			$sql .= " AND transport_transporteur.id_transporteur = benevole_participation_filiale.id ";
			$sql .= " AND benevole_participation_filiale.id_benevole = benevole.id";

			$sql .= " AND MONTH(transport.date_transport)=" . $data_to_display['facturation_month']['value'];
			$sql .= " AND YEAR(transport.date_transport)=" . $data_to_display['facturation_year']['value'];
			$sql .= " GROUP BY transport_transporteur.id_transporteur";
			$sql .= " ORDER BY benevole.nom, benevole.prenom";

			$sth = $dbh->query($sql);
			//transport avec & sans chauffeur !
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);


			//presentation des resultats des mois choisis avec les differentes fonctionnalites de comptabilite
			if (count($result) > 0) {

				//mise en session des donnees pour simplifier le rechargement de la page futur
				unset($_SESSION['last_page']);
				global $cfg;
				$_SESSION['last_page']['module'] = 'filiale';
				$_SESSION['last_page']['action'] = 'rapport';
				$_SESSION['last_page']['facturation_month'] = (double) $data_to_display['facturation_month']['value'];
				$_SESSION['last_page']['facturation_year'] = (double) $data_to_display['facturation_year']['value'];

				//chargement des class
				$load_needed_class_and_interface = load_class_and_interface(array('Beneficiaire', 'Transporteur', 'Transport'));


				//creation des pdf
				$html_code .= '<p>';
					$html_code .= '<a href="?module=filiale&amp;sub_module=transporteur&amp;action=pdf_remboursement&amp;reload=false">';
						$html_code .= 'Création de la totalité des fiches de remboursement dans <em>un seul fichier</em> PDF';
					$html_code .= '</a>';
				$html_code .= '</p>';


				//voir le rapport en Excel
				$html_code .= '<p>';
					$html_code .= '<a href="../' . $cfg['DIRECTORY']['extract'] . '/export_rapport_chauffeurs.xls">';
						$html_code .= 'Rapport format Excel';
					$html_code .= '</a>';
				$html_code .= '</p>';


				//header
				$html_code .= '<table class="OddEven">';
					$html_code .= '<thead>';
						$html_code .= '<tr>';
							$html_code .= '<th>Transporteur</th>';
							$html_code .= '<th>Nombre de trajets</th>';
							$html_code .= '<th>Nombre de kilomètres</th>';
							$html_code .= '<th>A rembourser</th>';
							$html_code .= '<th>Fiche de remboursement individuelle</th>';


						$html_code .= '</tr>';
					$html_code .= '</thead>';

					$html_code .= '<tbody>';

					$last_date_txt = '';
					$total_a_rembourser = 0;
					$nbre_transport = 0;
					$sum_km = 0;

					$j = 0;

					$matrix_to_export[$j][0] = 'nom';
					$matrix_to_export[$j][1] = 'actes';
					$matrix_to_export[$j][2] = 'km';
					$matrix_to_export[$j][3] = 'remboursement';
					$j++;

					foreach ($result as $row) {
						$html_code .= '<tr>';

							//transporteur
							$html_code .= '<td>';
								if (is_numeric($row['id_transporteur']) && Transporteur::id_exists($row['id_transporteur'])) {
									$tmp_transporteur = new Transporteur($row['id_transporteur']);
									$tmp_transporteur_nom_complet = $tmp_transporteur->get_nom_complet();

									$matrix_to_export[$j][0] = mb_strtoupper(stripAccents($tmp_transporteur_nom_complet['nom'])) . '_' . $tmp_transporteur_nom_complet['prenom'];

									$html_code .= '<a href="?module=benevole&action=view&id=' . $row['id_transporteur'] . '">';
										$html_code .= $tmp_transporteur_nom_complet['titre'] . ' ' . $tmp_transporteur_nom_complet['nom'];
									$html_code .= '</a>';
								}
							$html_code .= '</td>';

							$html_code .= '<td>';

								$nbre_transport += $row['nbre_trajets'];
								$matrix_to_export[$j][1] = $row['nbre_trajets'];

								if ($row['nbre_trajets'] == 0) {
									$html_code .= 'aucun transport';
								} elseif ($row['nbre_trajets'] == 1) {
									$html_code .= $row['nbre_trajets'] . ' transport';
								} else {
									$html_code .= $row['nbre_trajets'] . ' transports';
								}
							$html_code .= '</td>';

							$html_code .= '<td>';
								$sum_km += $row['sum_km'];
								$matrix_to_export[$j][2] = $row['sum_km'];

								$html_code .= $row['sum_km'] . ' km';
							$html_code .= '</td>';

							$html_code .= '<td>';
								//$total_a_rembourser += $row['sum_cout'];
								//$matrix_to_export[$j][3] = $row['sum_cout'];
								$total_a_rembourser += arrondi($row['sum_cout'], 0.10);
								$matrix_to_export[$j][3] = arrondi($row['sum_cout'], 0.10);

								$html_code .= 'CHF ' . number_format(arrondi($row['sum_cout'], 0.10),2);
							$html_code .= '</td>';

							$html_code .= '<td>';
								$html_code .= '<a href="?module=filiale&amp;sub_module=transporteur&amp;action=pdf_remboursement&amp;reload=false&amp;id_transporteur=' . $row['id_transporteur'] . '">';
									$html_code .= 'Consulter la fiche de remboursement individuelle';
								$html_code .= '</a>';
							$html_code .= '</td>';

						$html_code .= '</tr>';

						$j++;
					}

					$matrix_to_export[$j][0] = 'TOTAL';
					$matrix_to_export[$j][1] = $nbre_transport;
					$matrix_to_export[$j][2] = $sum_km;
					$matrix_to_export[$j][3] = $total_a_rembourser;
					$j++;


					$html_code .= '</tbody>';
				$html_code .= '</table>';


				//creation du fichier xls
				load_class_and_interface(array('PHPExcel'));
				$objPHPExcel = new PHPExcel();
				$objPHPExcel->getProperties()->setCreator("ASBV Transport")
							 ->setLastModifiedBy("ASBV Transport")
							 ->setTitle("Excel5 rapport mensuel ASBV Transport")
							 ->setSubject("Excel5 rapport mensuel ASBV Transport")
							 ->setDescription("generated using PHP classes.")
							 ->setKeywords("asbv transport rapport remboursements")
							 ->setCategory("rapport");

				$objPHPExcel->setActiveSheetIndex(0);

				$i = 1;
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', 'Année : ' . $data_to_display['facturation_year']['value']);
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1', 'Mois : ' . $data_to_display['facturation_month']['value']);
				$i++;

				foreach ($matrix_to_export as $row) {
					foreach($row as $index => $column) {

						$column_xls = xlColumnValue($index+1);
						$row_xls = $i;

						$coor_xls = '' . $column_xls . '' . $row_xls;
						$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, $column);
					}
					$i++;
				}


				//autosize column
				foreach ($matrix_to_export as $row) {
					foreach($row as $index => $column) {

						$column_xls = xlColumnValue($index+1);
						$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($column_xls)->setAutoSize(true);
					}
					break;
				}


				if (!is_dir('../' . $cfg['DIRECTORY']['extract'])) {
					mkdir('../' . $cfg['DIRECTORY']['extract']);
				}

				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
				$objWriter->save('../' . $cfg['DIRECTORY']['extract'] . '/export_rapport_chauffeurs.xls');


				$html_code .= '<p>';
					$html_code .= '<strong>TOTAL A REMBOURSER POUR LES ' . $nbre_transport . ' ACTES : CHF ' . number_format($total_a_rembourser, 2, '.', '\'') . ' (' . $sum_km . ' km)</strong>';
				$html_code .= '</p>';


			} else {
				$html_code .= '<p>Aucun transport n\'est pointé</p>';
			}

		} else {
			//charge le month picker
			$html_code .= '<form action="" method="post">';
				$html_code .= '<div id="facturation_month_picker" class="MonthPicker"></div>';

				$html_code .= '<p>';
					$html_code .= '<label for="month_picker_month">Mois</label>';
					$html_code .= '<input id="month_picker_month" name="month_picker_month" type="text" />';
				$html_code .= '</p>';

				$html_code .= '<p>';
					$html_code .= '<label for="month_picker_year">Année</label>';
					$html_code .= '<input id="month_picker_year" name="month_picker_year" type="text" />';
				$html_code .= '</p>';

				$html_code .= add_FormElement_input('hidden', 'form', '', 'rapport');
				$html_code .= add_FormElement_input('hidden', 'module', '', 'filiale');
				$html_code .= add_FormElement_input('hidden', 'sub_module', '', '""');
				$html_code .= add_FormElement_input('hidden', 'action', '', $action);

				$html_code .= '<input type="submit" value="Soumettre" />';
			$html_code .= '</form>';
		}

		$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));

		return $html_code;
	} // class.Filiale.form.rapport_transporteur


	private static function form_rapport_beneficiaire($action, $data_to_display='') {

		if (isset($_SESSION['last_page']['facturation_month']) && isset($_SESSION['last_page']['facturation_year'])) {
			$data_to_display = array();
			$data_to_display['facturation_month']['value'] = $_SESSION['last_page']['facturation_month'];
			$data_to_display['facturation_year']['value'] = $_SESSION['last_page']['facturation_year'];
		}

		if ($data_to_display == '')  {
			$data_to_display = array();
		}

		$html_code = '';

		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}

		if (isset($data_to_display['facturation_month']['value']) && is_numeric($data_to_display['facturation_month']['value']) && $data_to_display['facturation_month']['value'] > 0 && $data_to_display['facturation_month']['value'] <= 12 && isset($data_to_display['facturation_year']['value']) && is_numeric($data_to_display['facturation_year']['value']) && $data_to_display['facturation_year']['value'] > 0) {
			//remonte la totalite des transports du mois et de l'annee concernee
			global $dbh;

			//COUNT les transports, SUM km & remboursement
			$sql = "SELECT transport.id_beneficiaire, COUNT(transport.id_beneficiaire) as nbre_trajets, SUM(transport.nbre_kilometres) AS sum_km, SUM(transport.cout_trajet + transport.cout_variable) AS sum_cout ";
			$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport, beneficiaire ";
			$sql .= " WHERE id_filiale=" . $_SESSION['filiale']['id'];
			$sql .= " AND transport.is_cloture=1";
			$sql .= " AND transport.is_annule=0";
			$sql .= " AND transport.id_beneficiaire = beneficiaire.id";
			$sql .= " AND MONTH(transport.date_transport)=" . $data_to_display['facturation_month']['value'];
			$sql .= " AND YEAR(transport.date_transport)=" . $data_to_display['facturation_year']['value'];
			$sql .= " GROUP BY transport.id_beneficiaire ";
			$sql .= " ORDER BY beneficiaire.nom, beneficiaire.prenom";

			$sth = $dbh->query($sql);
			//transport avec & sans chauffeur !
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);

			//presentation des resultats des mois choisis avec les differentes fonctionnalites de comptabilite
			if (count($result) > 0) {

				//mise en session des donnees pour simplifier le rechargement de la page futur
				unset($_SESSION['last_page']);
				global $cfg;
				$_SESSION['last_page']['module'] = 'filiale';
				$_SESSION['last_page']['action'] = 'rapport';
				$_SESSION['last_page']['facturation_month'] = (double) $data_to_display['facturation_month']['value'];
				$_SESSION['last_page']['facturation_year'] = (double) $data_to_display['facturation_year']['value'];

				//chargement des class
				$load_needed_class_and_interface = load_class_and_interface(array('Beneficiaire', 'Transporteur', 'Transport'));



				//creation des pdf
				$html_code .= '<p>';
					$html_code .= '<a href="?module=filiale&amp;sub_module=beneficiaire&amp;action=pdf_facture&amp;reload=false">';
						$html_code .= 'Création de la totalité des factures dans <em>un seul fichier</em> PDF';
					$html_code .= '</a>';
				$html_code .= '</p>';


				//voir le rapport en Excel
				$html_code .= '<p>';
					$html_code .= '<a href="../' . $cfg['DIRECTORY']['extract'] . '/export_rapport_passagers.xls">';
						$html_code .= 'Rapport format Excel';
					$html_code .= '</a>';
				$html_code .= '</p>';


				//header
				$html_code .= '<table class="OddEven">';
					$html_code .= '<thead>';
						$html_code .= '<tr>';
							$html_code .= '<th>Passager</th>';
							$html_code .= '<th>Nombre de trajets</th>';
							$html_code .= '<th>Nombre de kilomètres</th>';
							$html_code .= '<th>Coût mensuel</th>';
							$html_code .= '<th>Facture individuelle</th>';
						$html_code .= '</tr>';
					$html_code .= '</thead>';

					$html_code .= '<tbody>';

					$last_date_txt = '';
					$total_a_payer = 0;
					$nbre_transport = 0;
					$sum_km = 0;

					$j = 0;

					$matrix_to_export[$j][0] = 'nom';
					$matrix_to_export[$j][1] = 'actes';
					$matrix_to_export[$j][2] = 'km';
					$matrix_to_export[$j][3] = 'cout';
					$j++;

					foreach ($result as $row) {
						$html_code .= '<tr>';

							//beneficiaire
							$html_code .= '<td>';
								if (is_numeric($row['id_beneficiaire']) && Beneficiaire::id_exists($row['id_beneficiaire'])) {
									$tmp_beneficiaire = new Beneficiaire($row['id_beneficiaire']);
									$tmp_beneficiaire_nom_complet = $tmp_beneficiaire->get_nom_complet();

									$html_code .= '<a class="link_dialog" href="?module=beneficiaire&amp;action=view&amp;id=' . $row['id_beneficiaire'] . '">';
										$html_code .= $tmp_beneficiaire_nom_complet['titre'] . ' ' . $tmp_beneficiaire_nom_complet['nom'];
									$html_code .= '</a>';

									$matrix_to_export[$j][0] = mb_strtoupper(stripAccents($tmp_beneficiaire_nom_complet['nom'])) . '_' . ucfirst(stripAccents($tmp_beneficiaire_nom_complet['prenom']));
								}
							$html_code .= '</td>';

							$html_code .= '<td>';

								$nbre_transport += $row['nbre_trajets'];
								$matrix_to_export[$j][1] = $row['nbre_trajets'];

								if ($row['nbre_trajets'] == 0) {
									$html_code .= 'aucun transport';
								} elseif ($row['nbre_trajets'] == 1) {
									$html_code .= $row['nbre_trajets'] . ' transport';
								} else {
									$html_code .= $row['nbre_trajets'] . ' transports';
								}
							$html_code .= '</td>';

							$html_code .= '<td>';
								$sum_km += $row['sum_km'];
								$matrix_to_export[$j][2] = $row['sum_km'];

								$html_code .= $row['sum_km'] . ' km';
							$html_code .= '</td>';

							$html_code .= '<td>';
								$html_code .= 'CHF ' . number_format($row['sum_cout'],2);
/*
                     Old php code replaced under Greg request by M. Thevoz 20 Oct 2014
                          $total_a_payer += number_format($row['sum_cout'],2);
*/
                            $total_a_payer += arrondi($row['sum_cout'],0.10) ;

								$matrix_to_export[$j][3] = $row['sum_cout'];

							$html_code .= '</td>';

							$html_code .= '<td>';
								$html_code .= '<a href="?module=filiale&amp;sub_module=beneficiaire&amp;action=pdf_facture&amp;reload=false&amp;id_beneficiaire=' . $row['id_beneficiaire'] . '">';
									$html_code .= 'Consulter la facture individuelle';
								$html_code .= '</a>';
							$html_code .= '</td>';


						$html_code .= '</tr>';

						$j++;

					}

					$matrix_to_export[$j][0] = 'TOTAL';
					$matrix_to_export[$j][1] = $nbre_transport;
					$matrix_to_export[$j][2] = $sum_km;
					$matrix_to_export[$j][3] = $total_a_payer;
					$j++;


					$html_code .= '</tbody>';
				$html_code .= '</table>';


				//creation du fichier xls
				load_class_and_interface(array('PHPExcel'));
				$objPHPExcel = new PHPExcel();
				$objPHPExcel->getProperties()->setCreator("ASBV Transport")
							 ->setLastModifiedBy("ASBV Transport")
							 ->setTitle("Excel5 rapport mensuel ASBV Transport")
							 ->setSubject("Excel5 rapport mensuel ASBV Transport")
							 ->setDescription("generated using PHP classes.")
							 ->setKeywords("asbv transport rapport facture")
							 ->setCategory("rapport");

				$objPHPExcel->setActiveSheetIndex(0);

				$i = 1;
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', 'Année : ' . $data_to_display['facturation_year']['value']);
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1', 'Mois : ' . $data_to_display['facturation_month']['value']);
				$i++;

				foreach ($matrix_to_export as $row) {
					foreach($row as $index => $column) {

						$column_xls = xlColumnValue($index+1);
						$row_xls = $i;

						$coor_xls = '' . $column_xls . '' . $row_xls;
						$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, $column);
					}
					$i++;
				}



				//autosize column
				foreach ($matrix_to_export as $row) {
					foreach($row as $index => $column) {

						$column_xls = xlColumnValue($index+1);
						$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($column_xls)->setAutoSize(true);
					}
					break;
				}



				if (!is_dir('../' . $cfg['DIRECTORY']['extract'])) {
					mkdir('../' . $cfg['DIRECTORY']['extract']);
				}

				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
				$objWriter->save('../' . $cfg['DIRECTORY']['extract'] . '/export_rapport_passagers.xls');


				$html_code .= '<p>';
					$html_code .= '<strong>TOTAL A PAYER POUR LES ' . $nbre_transport . ' ACTES : CHF ' . number_format($total_a_payer, 2, '.', '\'') . ' (' . $sum_km . ' km)</strong>';
				$html_code .= '</p>';
			} else {
				$html_code .= '<p>Aucun transport n\'est pointé</p>';
			}


		} else {
			//charge le month picker
			$html_code .= '<form action="" method="post">';
				$html_code .= '<div id="facturation_month_picker" class="MonthPicker"></div>';

				$html_code .= '<p>';
					$html_code .= '<label for="month_picker_month">Mois</label>';
					$html_code .= '<input id="month_picker_month" name="month_picker_month" type="text" />';
				$html_code .= '</p>';

				$html_code .= '<p>';
					$html_code .= '<label for="month_picker_year">Année</label>';
					$html_code .= '<input id="month_picker_year" name="month_picker_year" type="text" />';
				$html_code .= '</p>';

				$html_code .= add_FormElement_input('hidden', 'form', '', 'rapport');
				$html_code .= add_FormElement_input('hidden', 'module', '', 'filiale');
				$html_code .= add_FormElement_input('hidden', 'sub_module', '', '""');
				$html_code .= add_FormElement_input('hidden', 'action', '', $action);

				$html_code .= '<input type="submit" value="Soumettre" />';
			$html_code .= '</form>';
		}

		$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));

		return $html_code;
	} // class.Filiale.form.rapport_beneficiaire



	private static function form_pdf_facture_beneficiaire($action, $data_to_display='') {
		load_class_and_interface(array('Lieu', 'Lieu_Categorie'));

		global $base_dir;

		if (isset($_SESSION['last_page']['facturation_month']) && isset($_SESSION['last_page']['facturation_year'])) {
			if (isset($data_to_display['id_beneficiaire']['value'])) {
				$id_beneficiaire = $data_to_display['id_beneficiaire']['value'];
			}

			if (isset($data_to_display['reload']['value'])) {
				$reload_status = $data_to_display['reload']['value'];
			}

			$data_to_display = array();
			$data_to_display['facturation_month']['value'] = $_SESSION['last_page']['facturation_month'];
			$data_to_display['facturation_year']['value'] = $_SESSION['last_page']['facturation_year'];

			if (isset($id_beneficiaire)) {
				$data_to_display['id_beneficiaire']['value'] = $id_beneficiaire;
			}

			if (isset($reload_status)) {
				$data_to_display['reload']['value'] = $reload_status;
			}

		}

		if ($data_to_display == '')  {
			$data_to_display = array();
		}

		$html_code = '';

		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}

		if (isset($data_to_display['facturation_month']['value']) && is_numeric($data_to_display['facturation_month']['value']) && $data_to_display['facturation_month']['value'] > 0 && $data_to_display['facturation_month']['value'] <= 12 && isset($data_to_display['facturation_year']['value']) && is_numeric($data_to_display['facturation_year']['value']) && $data_to_display['facturation_year']['value'] > 0) {
			//remonte la totalite des transports du mois et de l'annee concernee
			global $dbh;

			//COUNT les transports, SUM km & remboursement
			$sql = "SELECT transport.id_beneficiaire, COUNT(transport.id_beneficiaire), SUM(transport.nbre_kilometres), SUM(transport.cout_trajet + transport.cout_variable) ";
			$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport, beneficiaire ";
			$sql .= " WHERE id_filiale=" . $_SESSION['filiale']['id'];

			if (isset($data_to_display['id_beneficiaire']['value']) && Beneficiaire::id_exists($data_to_display['id_beneficiaire']['value'])) {
				$sql .= " AND transport.id_beneficiaire=" . $data_to_display['id_beneficiaire']['value'];
			}

			$sql .= " AND transport.id_beneficiaire = beneficiaire.id ";
			$sql .= " AND transport.is_cloture=1";
			$sql .= " AND transport.is_annule=0";
			$sql .= " AND MONTH(transport.date_transport)=" . $data_to_display['facturation_month']['value'];
			$sql .= " AND YEAR(transport.date_transport)=" . $data_to_display['facturation_year']['value'];
			$sql .= " GROUP BY transport.id_beneficiaire ";
			$sql .= " ORDER BY beneficiaire.nom, beneficiaire.prenom";

			$sth = $dbh->query($sql);
			//transport avec & sans chauffeur !
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);


			//presentation des resultats des mois choisis avec les differentes fonctionnalites de comptabilite
			if (count($result) > 0) {

				$tmp_filiale = new Filiale($_SESSION['filiale']['id']);

				//mise en session des donnees pour simplifier le rechargement de la page futur
				unset($_SESSION['last_page']);
				$_SESSION['last_page']['module'] = 'filiale';
				$_SESSION['last_page']['action'] = 'rapport';
				$_SESSION['last_page']['facturation_month'] = $data_to_display['facturation_month']['value'];
				$_SESSION['last_page']['facturation_year'] = $data_to_display['facturation_year']['value'];

				load_class_and_interface(array('Facture', 'Beneficiaire', 'Transport_Categorie'));

				// create a PDF object
				$pdf = new Facture(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

				//protection
				global $cfg;
				$pdf->SetProtection(array('copy', 'modify'), $cfg['PDF']['password']);

				// set document (meta) information
				$pdf->SetCreator(PDF_CREATOR);
				$pdf->SetAuthor('ASBV Section ' . ucfirst(stripAccents($tmp_filiale->get_nom())));
				$pdf->SetTitle('Facture passager ASBV');
				$pdf->SetSubject('Facture passager ASBV');
				$pdf->SetKeywords('ASBV, ' . ucfirst(stripAccents($tmp_filiale->get_nom())) . ', facture, passager');


				foreach ($result as $testtest => $facture_individuelle ) {

					$testtest = $testtest;

					//tableau des mois en francais
					$mois[1] = 'janvier';
					$mois[2] = 'février';
					$mois[3] = 'mars';
					$mois[4] = 'avril';
					$mois[5] = 'mai';
					$mois[6] = 'juin';
					$mois[7] = 'juillet';
					$mois[8] = 'août';
					$mois[9] = 'septembre';
					$mois[10] = 'octobre';
					$mois[11] = 'novembre';
					$mois[12] = 'décembre';

					//mount le beneficiaire
					$tmp_beneficiaire = new Beneficiaire($facture_individuelle['id_beneficiaire']);


					//charge les trajet du mois pour le beneficiaire concerne
					$sql = "SELECT transport.* ";
					$sql .= " FROM transport ";
					$sql .= " WHERE id_beneficiaire=" . $facture_individuelle['id_beneficiaire'];
					$sql .= " AND id_filiale=" . $_SESSION['filiale']['id'];
					$sql .= " AND transport.is_cloture=1";
					$sql .= " AND transport.is_annule=0";
					$sql .= " AND MONTH(date_transport)=" . $data_to_display['facturation_month']['value'];
					$sql .= " AND YEAR(date_transport)=" . $data_to_display['facturation_year']['value'];
					$sql .= " ORDER BY date_transport, heure_debut ";

					$sth = $dbh->query($sql);

					$transports_beneficaire_mois_facturation = $sth->fetchAll(PDO::FETCH_ASSOC);

					$date_year_txt = $data_to_display['facturation_year']['value'];

					if (strlen($data_to_display['facturation_month']['value']) == 1) {
						$date_month_txt = '0' . $data_to_display['facturation_month']['value'];
					} else {
						$date_month_txt = $data_to_display['facturation_month']['value'];
					}



					$total = 0;

					$first_page = TRUE;
					foreach ($transports_beneficaire_mois_facturation as $course_individuelle) {

						if ( $first_page === TRUE || $currY >= 245 ) {

							// add a page
							$pdf->AddPage();
							$first_page = FALSE;

							// create address box
							$posX_beneficiaire = 100;
							$posY_beneficiaire = 45;
							$tmp_beneficiaire_nom_complet = $tmp_beneficiaire->get_nom_complet();
							//$tmp_beneficiaire_adresse = $tmp_beneficiaire->get_adresse();
							$tmp_beneficiaire_adresse = $tmp_beneficiaire->get_adresse_facturation();

							$pdf->CreateTextBox($tmp_beneficiaire_adresse['nom_complet']['titre'], $posX_beneficiaire, $posY_beneficiaire, 80, 10, 10);
							$pdf->CreateTextBox(mb_strtoupper(stripAccents($tmp_beneficiaire_adresse['nom_complet']['nom'])) . ' ' . $tmp_beneficiaire_adresse['nom_complet']['prenom'], $posX_beneficiaire, $posY_beneficiaire+5, 80, 10, 10, 'B');

							if (isset($tmp_beneficiaire_adresse['adresse_complement']) && $tmp_beneficiaire_adresse['adresse_complement'] != '') {
								$pdf->CreateTextBox($tmp_beneficiaire_adresse['adresse_complement'], $posX_beneficiaire, $posY_beneficiaire+10, 80, 10, 10);
								$pdf->CreateTextBox($tmp_beneficiaire_adresse['adresse'], $posX_beneficiaire, $posY_beneficiaire+15, 80, 10, 10);
								$pdf->CreateTextBox($tmp_beneficiaire_adresse['npa'] . ' ' . $tmp_beneficiaire_adresse['ville'], $posX_beneficiaire, $posY_beneficiaire+20, 80, 10, 10);
							} else {
								$pdf->CreateTextBox($tmp_beneficiaire_adresse['adresse'], $posX_beneficiaire, $posY_beneficiaire+10, 80, 10, 10);
								$pdf->CreateTextBox($tmp_beneficiaire_adresse['npa'] . ' ' . $tmp_beneficiaire_adresse['ville'], $posX_beneficiaire, $posY_beneficiaire+15, 80, 10, 10);
							}


							// invoice title / number
							foreach ($mois as $index => $indiv_mois) {
								if ($index == $data_to_display['facturation_month']['value'] ) {
									$mois_txt = $indiv_mois;
									break;
								}
							}
							$pdf->CreateTextBox('Facture : ' . ucfirst($mois_txt) . ' ' . $data_to_display['facturation_year']['value'], 0, 90, 120, 20, 16);

							// date, order ref
							$pdf->CreateTextBox('Référence : F' . $date_year_txt . $date_month_txt . $tmp_beneficiaire->get_id(), 0, 100, 0, 10, 10, '', 'L');
							$pdf->CreateTextBox(ucfirst(stripAccents($tmp_filiale->get_ville())) . ', le '.date('d.m.Y'), $posX_beneficiaire, 100, 0, 10, 10, '', 'L');


							// info sur le passager
							$pdf->CreateTextBox('Transport pour : ' . mb_strtoupper(stripAccents($tmp_beneficiaire_nom_complet['nom'])) . ' ' . $tmp_beneficiaire_nom_complet['prenom'], 0, 110, 0, 10, 10, 'B', 'L');

							// list headers
							$c_date_heure = 10;
							$c_trajet = 50;
							$c_type_transport = 40;
							$c_prix = 150;

							$pdf->CreateTextBox('Date & Heure', $c_date_heure, 120, 30, 10, 10, 'B', 'C');
							$pdf->CreateTextBox('Trajet', $c_trajet, 120, 90, 10, 10, 'B');
							$pdf->CreateTextBox('Prix', $c_prix, 120, 30, 10, 10, 'B', 'L');

							// hLine
							$pdf->Line(20, 129, 195, 129);

							$currency = 'Fr. ';
							$currY = 128;

						}



						$pdf->CreateTextBox(date_yyyymmdd_to_ddmmyyyy($course_individuelle['date_transport']) . ' ' . time_hhmmss_to_hhmm($course_individuelle['heure_debut']), $c_date_heure, $currY, 20, 10, 10, '', 'L');

						$point_depart = unserialize($course_individuelle['point_depart']);
						$point_arrivee = unserialize($course_individuelle['point_arrivee']);
						$tmp_transport_categorie = new Transport_Categorie($course_individuelle['id_categorie']);

						if ($point_depart['type'] == 'lieu') {
							$tmp_lieu = new Lieu($point_depart['id']);

							if ($tmp_lieu->get_id_categorie() == Lieu_Categorie::get_id_from_categorie('ville')) {
								$depart = mb_strtoupper(stripAccents($point_depart['ville']));
							} else {
								$depart = $tmp_lieu->get_abreviation();
							}


						} else {
							//$depart = 'Domicile (' . mb_strtoupper(stripAccents($point_depart['ville'])) . ')';
							$depart =  mb_strtoupper(stripAccents($point_depart['ville']));
						}

						if ($point_arrivee['type'] == 'lieu') {
							$tmp_lieu = new Lieu($point_arrivee['id']);

							if ($tmp_lieu->get_id_categorie() == Lieu_Categorie::get_id_from_categorie('ville')) {
								$arrivee = mb_strtoupper(stripAccents($point_arrivee['ville']));
							} else {
								$arrivee = $tmp_lieu->get_abreviation();
							}


						} else {
							//$arrivee = 'Domicile (' . mb_strtoupper(stripAccents($point_arrivee['ville'])) . ')';
							$arrivee =  mb_strtoupper(stripAccents($point_arrivee['ville']));
						}

						//$pdf->CreateTextBox(strtoupper(stripAccents($point_depart['ville'])) . ' -> ' . strtoupper(stripAccents($point_arrivee['ville'])), $c_trajet, $currY, 20, 10, 10, '', 'L');
						$pdf->CreateTextBox($depart . ' -> ' . $arrivee, $c_trajet, $currY, 20, 10, 10, '', 'L');
						//$pdf->CreateTextBox($depart . ' -> ' . $arrivee . ' - '. ucfirst($tmp_transport_categorie->get_nom()), $c_trajet, $currY, 20, 10, 10, '', 'L');
						$pdf->CreateTextBox($currency, $c_prix, $currY, 20, 10, 10, '', 'L');
						$pdf->CreateTextBox(number_format($course_individuelle['cout_trajet'],2), $c_prix, $currY, 20, 10, 10, '', 'R');
							$total = $total + $course_individuelle['cout_trajet'];

							$currY += 5;
							$pdf->CreateTextBox(ucfirst($tmp_transport_categorie->get_nom()), $c_trajet, $currY, 20, 10, 10, '', 'L');


						if ($course_individuelle['duree_approximative'] > 2) {
							$pdf->CreateTextBox('Le trajet est compté double car la durée d\'attente dépasse deux heures', $c_trajet, $currY+5, 20, 10, 10, '', 'L');
							$currY += 5;
						}


						if ($course_individuelle['cout_variable'] > 0) {
							$pdf->CreateTextBox('Frais divers (parking etc.)', $c_trajet, $currY+5, 20, 10, 10, '', 'L');
							$pdf->CreateTextBox(number_format($course_individuelle['cout_variable'],2), $c_prix, $currY+5, 20, 10, 10, '', 'R');
							$total = $total + $course_individuelle['cout_variable'];

							$currY = $currY+15;
						} else {
							$currY = $currY+10;
						}
					}


					if ($currY >= 245) {
						$pdf->AddPage();
						$currY = 128;
					}

					// hLine
					$pdf->Line(20, $currY+4, 195, $currY+4);

					// output the total row

					$pdf->CreateTextBox('soit TOTAL', 20, $currY+5, $c_prix-25, 10, 10, 'B', 'R');
					$pdf->CreateTextBox($currency, $c_prix, $currY+5, 20, 10, 10, 'B', 'L');
					$pdf->CreateTextBox(number_format($total,2), $c_prix, $currY+5, 20, 10, 10, 'B', 'R');

				}

				//Close and output PDF document
				$projet_name = substr($_SERVER['SCRIPT_NAME'], 1, (strpos($_SERVER['SCRIPT_NAME'], '/', 1))-1);
				$server_name = $_SERVER['SERVER_NAME'];
				global $cfg;

				$directory_facturation = $cfg['DIRECTORY']['facturation'];


				//creation du dossier de stockage si necessaire
				if (!is_dir('../' . $directory_facturation)) {
					mkdir('../' . $directory_facturation);
				}


				if (!is_dir('../' . $directory_facturation . '/' . $data_to_display['facturation_year']['value'])) {
					mkdir('../' . $directory_facturation . '/' . $data_to_display['facturation_year']['value']);
				}


				if (!is_dir('../' . $directory_facturation . '/' . $data_to_display['facturation_year']['value'] . '/' . $data_to_display['facturation_month']['value'])) {
					mkdir('../' . $directory_facturation . '/' . $data_to_display['facturation_year']['value'] . '/' . $data_to_display['facturation_month']['value']);
				}

				if (!isset($data_to_display['id_beneficiaire']['value'])) {
					//facturation integral du mois
					$pdf_file = 'FacturationPassagers' . $data_to_display['facturation_year']['value'] . '_'. $data_to_display['facturation_month']['value'] . '.pdf';

					if (isset($data_to_display['reload']['value']) && $data_to_display['reload']['value'] === FALSE) {

						$pdf->Output( $base_dir . '/' . $directory_facturation . '/' . $data_to_display['facturation_year']['value'] . '/' . $data_to_display['facturation_month']['value'] . '/' . $pdf_file, 'FI');
					} else {
						$pdf->Output( $base_dir . '/' . $directory_facturation . '/' . $data_to_display['facturation_year']['value'] . '/' . $data_to_display['facturation_month']['value'] . '/' . $pdf_file, 'F');
						//retourne le lien puis recharge le rapport beneficiaire
						$html_code .= '<p>';
							$html_code .= '<label>Fichier nouvellement crée : </label>';
							$html_code .= '<a href="http://' . $server_name . '/' . $projet_name . '/' . $directory_facturation . '/' . $data_to_display['facturation_year']['value'] . '/' . $data_to_display['facturation_month']['value'] . '/' . $pdf_file . '">';
								$html_code .= $pdf_file;
							$html_code .= '</a>';
						$html_code .='</p>';
					}

				} else {

					//facture individuel
					$tmp_beneficiaire = new Beneficiaire($data_to_display['id_beneficiaire']['value']);
					$tmp_beneficiaire_nom_complet = $tmp_beneficiaire->get_nom_complet();
					$pdf_file = 'FacturePassager' . '_' . $data_to_display['facturation_year']['value'] . '_'. $data_to_display['facturation_month']['value'] . '_' . stripAccents($tmp_beneficiaire_nom_complet['nom']) . '_' . stripAccents($tmp_beneficiaire_nom_complet['prenom']) . '.pdf';

					if (isset($data_to_display['reload']['value']) && $data_to_display['reload']['value'] === FALSE) {
						$pdf->Output( $base_dir . '/' . $directory_facturation . '/' . $data_to_display['facturation_year']['value'] . '/' . $data_to_display['facturation_month']['value'] . '/' . $pdf_file, 'FI');
					} else {
						$pdf->Output( $base_dir . '/' . $directory_facturation . '/' . $data_to_display['facturation_year']['value'] . '/' . $data_to_display['facturation_month']['value'] . '/' . $pdf_file, 'F');

						//retourne le lien puis recharge le rapport beneficiaire
						$html_code .= '<p>';
							$html_code .= '<label>Fichier nouvellement crée : </label>';
							$html_code .= '<a href="http://' . $server_name . '/' . $projet_name . '/' . $directory_facturation . '/' . $data_to_display['facturation_year']['value'] . '/' . $data_to_display['facturation_month']['value'] . '/' . $pdf_file . '">';
								$html_code .= $pdf_file;
							$html_code .= '</a>';
						$html_code .= '</p>';
					}
				}
			}
		}

		if (isset($data_to_display['reload']['value']) && $data_to_display['reload']['value'] === FALSE) {
			//ne rien retourner car le pdf est chargé à la place de la page html
		} else {
			$html_code .= Filiale::form_rapport_beneficiaire($action);

			$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));

			return $html_code;
		}
	} // class.Filiale.form.pdf_facture_beneficiaire


	private static function form_pdf_remboursement_transporteur($action, $data_to_display='') {

		global $base_dir;

		if (isset($_SESSION['last_page']['facturation_month']) && isset($_SESSION['last_page']['facturation_year'])) {
			if (isset($data_to_display['id_transporteur']['value'])) {
				$id_transporteur = $data_to_display['id_transporteur']['value'];
			}

			if (isset($data_to_display['reload']['value'])) {
				$reload_status = $data_to_display['reload']['value'];
			}

			$data_to_display = array();
			$data_to_display['facturation_month']['value'] = $_SESSION['last_page']['facturation_month'];
			$data_to_display['facturation_year']['value'] = $_SESSION['last_page']['facturation_year'];

			if (isset($id_transporteur)) {
				$data_to_display['id_transporteur']['value'] = $id_transporteur;
			}

			if (isset($reload_status)) {
				$data_to_display['reload']['value'] = $reload_status;
			}
		}

		if ($data_to_display == '')  {
			$data_to_display = array();
		}

		$html_code = '';

		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}

		if (isset($data_to_display['facturation_month']['value']) && is_numeric($data_to_display['facturation_month']['value']) && $data_to_display['facturation_month']['value'] > 0 && $data_to_display['facturation_month']['value'] <= 12 && isset($data_to_display['facturation_year']['value']) && is_numeric($data_to_display['facturation_year']['value']) && $data_to_display['facturation_year']['value'] > 0) {
			$load_needed_class_and_interface = load_class_and_interface(array('Transporteur'));

			//remonte la totalite des transports du mois et de l'annee concernee
			global $dbh;

			//COUNT les transports, SUM km & remboursement
			$sql = "SELECT transport_transporteur.id_transporteur, COUNT(transport_transporteur.id_transport), SUM(transport.nbre_kilometres), SUM((transport.cout_trajet * (transport.taux_remboursement_transporteur)/100) + transport.cout_variable) ";
			$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport, benevole_participation_filiale, benevole ";
			$sql .= " WHERE transport.id_filiale=" . $_SESSION['filiale']['id'];

			if (isset($data_to_display['id_transporteur']['value']) && Transporteur::id_exists($data_to_display['id_transporteur']['value'])) {
				$sql .= " AND transport_transporteur.id_transporteur=" . $data_to_display['id_transporteur']['value'];
			}

			$sql .= " AND transport.is_cloture=1";
			$sql .= " AND transport.is_annule=0";
			$sql .= " AND MONTH(transport.date_transport)=" . $data_to_display['facturation_month']['value'];
			$sql .= " AND YEAR(transport.date_transport)=" . $data_to_display['facturation_year']['value'];

			$sql .= " AND transport_transporteur.id_transporteur = benevole_participation_filiale.id ";
			$sql .= " AND benevole_participation_filiale.id_benevole = benevole.id";

			$sql .= " GROUP BY transport_transporteur.id_transporteur ";
			$sql .= " ORDER BY benevole.nom, benevole.prenom ";

			$sth = $dbh->query($sql);
			//transport avec & sans chauffeur !
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);


			//presentation des resultats des mois choisis avec les differentes fonctionnalites de comptabilite
			if (count($result) > 0) {

				$tmp_filiale = new Filiale($_SESSION['filiale']['id']);

				//mise en session des donnees pour simplifier le rechargement de la page futur
				unset($_SESSION['last_page']);
				$_SESSION['last_page']['module'] = 'filiale';
				$_SESSION['last_page']['action'] = 'rapport';
				$_SESSION['last_page']['facturation_month'] = $data_to_display['facturation_month']['value'];
				$_SESSION['last_page']['facturation_year'] = $data_to_display['facturation_year']['value'];

				$load_needed_class_and_interface = load_class_and_interface(array('Facture', 'Beneficiaire', 'Transport_Categorie'));

				// create a PDF object
				$pdf = new Facture(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);


				//protection
				global $cfg;
				$pdf->SetProtection(array('copy', 'modify'), $cfg['PDF']['password']);

				// set document (meta) information
				$pdf->SetCreator(PDF_CREATOR);
				$pdf->SetAuthor('ASBV Section ' . ucfirst(stripAccents($tmp_filiale->get_nom())));
				$pdf->SetTitle('Remboursement transporteur ASBV');
				$pdf->SetSubject('Remboursement transporteur ASBV');
				$pdf->SetKeywords('ASBV, ' . ucfirst(stripAccents($tmp_filiale->get_nom())) . ', remboursement, transporteur');


				foreach ($result as $facture_individuelle ) {

					//tableau des mois en francais
					$mois[1] = 'janvier';
					$mois[2] = 'février';
					$mois[3] = 'mars';
					$mois[4] = 'avril';
					$mois[5] = 'mai';
					$mois[6] = 'juin';
					$mois[7] = 'juillet';
					$mois[8] = 'août';
					$mois[9] = 'septembre';
					$mois[10] = 'octobre';
					$mois[11] = 'novembre';
					$mois[12] = 'décembre';

					//mount le beneficiaire
					$tmp_transporteur = new Transporteur($facture_individuelle['id_transporteur']);


					//charge les trajet du mois pour le transporteur concerne
					$sql = "SELECT transport.* ";
					$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport ";
					$sql .= " WHERE transport_transporteur.id_transporteur=" . $facture_individuelle['id_transporteur'];
					$sql .= " AND id_filiale=" . $_SESSION['filiale']['id'];
					$sql .= " AND transport.is_cloture=1";
					$sql .= " AND MONTH(date_transport)=" . $data_to_display['facturation_month']['value'];
					$sql .= " AND YEAR(date_transport)=" . $data_to_display['facturation_year']['value'];
					$sql .= " ORDER BY date_transport, heure_debut ";

					$sth = $dbh->query($sql);

					$transports_beneficaire_mois_facturation = $sth->fetchAll(PDO::FETCH_ASSOC);

					$date_year_txt = $data_to_display['facturation_year']['value'];

					if (strlen($data_to_display['facturation_month']['value']) == 1) {
						$date_month_txt = '0' . $data_to_display['facturation_month']['value'];
					} else {
						$date_month_txt = $data_to_display['facturation_month']['value'];
					}


					$first_page = TRUE;
					$total = 0;
					foreach ($transports_beneficaire_mois_facturation as $course_individuelle) {

						if ($currY >= 268 || $first_page === TRUE) {
							$first_page = FALSE;
							$count_transport = 0;

							$pdf->AddPage();

							// create address box
							$posX_transporteur = 100;
							$posY_transporteur = 45;
							$tmp_transporteur_nom_complet = $tmp_transporteur->get_nom_complet();
							$tmp_transporteur_adresse = $tmp_transporteur->get_adresse();

							$pdf->CreateTextBox($tmp_transporteur_nom_complet['titre'], $posX_transporteur, $posY_transporteur, 80, 10, 10);
							$pdf->CreateTextBox(mb_strtoupper(stripAccents($tmp_transporteur_nom_complet['nom'])) . ' ' . $tmp_transporteur_nom_complet['prenom'], $posX_transporteur, $posY_transporteur+5, 80, 10, 10, 'B');

							if ($tmp_transporteur_adresse['adresse_complement'] != '') {
								$pdf->CreateTextBox($tmp_transporteur_adresse['adresse'], $posX_transporteur, $posY_transporteur+10, 80, 10, 10);
								$pdf->CreateTextBox($tmp_transporteur_adresse['adresse_complement'], $posX_transporteur, $posY_transporteur+15, 80, 10, 10);
								$pdf->CreateTextBox($tmp_transporteur_adresse['npa'] . ' ' . $tmp_transporteur_adresse['ville'], $posX_transporteur, $posY_transporteur+20, 80, 10, 10);
							} else {
								$pdf->CreateTextBox($tmp_transporteur_adresse['adresse'], $posX_transporteur, $posY_transporteur+10, 80, 10, 10);
								$pdf->CreateTextBox($tmp_transporteur_adresse['npa'] . ' ' . $tmp_transporteur_adresse['ville'], $posX_transporteur, $posY_transporteur+15, 80, 10, 10);
							}


							// invoice title / number
							foreach ($mois as $index => $indiv_mois) {
								if ($index == $data_to_display['facturation_month']['value'] ) {
									$mois_txt = $indiv_mois;
									break;
								}
							}
							$pdf->CreateTextBox('Remboursement : ' . ucfirst($mois_txt) . ' ' . $data_to_display['facturation_year']['value'], 0, 90, 120, 20, 16);

							// date, order ref
							$pdf->CreateTextBox('Référence : R' . $date_year_txt . $date_month_txt . $tmp_transporteur->get_id_transporteur(), 0, 100, 0, 10, 10, '', 'L');
							$pdf->CreateTextBox(ucfirst(stripAccents($tmp_filiale->get_ville())) . ', le '.date('d.m.Y'), $posX_transporteur, 100, 0, 10, 10, '', 'L');


							// list headers
							$c_date_heure = 10;
							$c_trajet = 50;
							$c_type_transport = 40;
							$c_prix = 150;

							$pdf->CreateTextBox('Date & Heure', $c_date_heure, 120, 30, 10, 10, 'B', 'C');
							$pdf->CreateTextBox('Trajet', $c_trajet, 120, 90, 10, 10, 'B');
							$pdf->CreateTextBox('Prix', $c_prix, 120, 30, 10, 10, 'B', 'L');

							// hLine
							$pdf->Line(20, 129, 195, 129);

							$currency = 'Fr. ';
							$currY = 128;

						}


						// details des différents transport
						$pdf->CreateTextBox(date_yyyymmdd_to_ddmmyyyy($course_individuelle['date_transport']) . ' ' . time_hhmmss_to_hhmm($course_individuelle['heure_debut']), $c_date_heure, $currY, 20, 10, 10, '', 'L');

						$point_depart = unserialize($course_individuelle['point_depart']);
						$point_arrivee = unserialize($course_individuelle['point_arrivee']);
						$tmp_transport_categorie = new Transport_Categorie($course_individuelle['id_categorie']);

						$pdf->CreateTextBox(strtoupper(stripAccents($point_depart['ville'])) . ' -> ' . strtoupper(stripAccents($point_arrivee['ville'])) . ' ('. ucfirst($tmp_transport_categorie->get_nom()) . ')', $c_trajet, $currY, 20, 10, 10, '', 'L');
						$pdf->CreateTextBox($currency, $c_prix, $currY, 20, 10, 10, '', 'L');
						$pdf->CreateTextBox(number_format(arrondi($course_individuelle['cout_trajet'] * ($course_individuelle['taux_remboursement_transporteur'] / 100), 0.10), 2), $c_prix, $currY, 20, 10, 10, '', 'R');
							//$total = $total + $course_individuelle['cout_trajet'] * ($course_individuelle['taux_remboursement_transporteur'] / 100);
							$total += number_format(arrondi($course_individuelle['cout_trajet'] * ($course_individuelle['taux_remboursement_transporteur'] / 100), 0.10), 2);


						//$pdf->CreateTextBox(utf8_encode(ucfirst($tmp_transport_categorie->get_nom())), $c_trajet, $currY+5, 20, 10, 10, '', 'L');

						if ($course_individuelle['cout_variable'] > 0) {
							$pdf->CreateTextBox('Frais divers (parking etc.)', $c_trajet, $currY+5, 20, 10, 10, '', 'L');
							$pdf->CreateTextBox(number_format($course_individuelle['cout_variable'],2), $c_prix, $currY+5, 20, 10, 10, '', 'R');
							$total = $total + $course_individuelle['cout_variable'];

							$currY = $currY+15;
						} else {
							$currY = $currY+10;
						}

					}

					if ($currY > 268) {
						$pdf->AddPage();
						$currY = 128;
					}

					// hLine
					$pdf->Line(20, $currY+4, 195, $currY+4);

					// output the total row

					$pdf->CreateTextBox('soit TOTAL', 20, $currY+5, $c_prix-25, 10, 10, 'B', 'R');
					$pdf->CreateTextBox($currency, $c_prix, $currY+5, 20, 10, 10, 'B', 'L');
					$pdf->CreateTextBox(number_format($total,2), $c_prix, $currY+5, 20, 10, 10, 'B', 'R');


				}

				//Close and output PDF document
				$projet_name = substr($_SERVER['SCRIPT_NAME'], 1, (strpos($_SERVER['SCRIPT_NAME'], '/', 1))-1);
				$server_name = $_SERVER['SERVER_NAME'];

				global $cfg;
				$directory_facturation = $cfg['DIRECTORY']['remboursement'];


				//creation du dossier de stockage si necessaire
				if (!is_dir('../' . $directory_facturation)) {
					mkdir('../' . $directory_facturation);
				}


				if (!is_dir('../' . $directory_facturation . '/' . $data_to_display['facturation_year']['value'])) {
					mkdir('../' . $directory_facturation . '/' . $data_to_display['facturation_year']['value']);
				}


				if (!is_dir('../' . $directory_facturation . '/' . $data_to_display['facturation_year']['value'] . '/' . $data_to_display['facturation_month']['value'])) {
					mkdir('../' . $directory_facturation . '/' . $data_to_display['facturation_year']['value'] . '/' . $data_to_display['facturation_month']['value']);
				}

				if (!isset($data_to_display['id_transporteur']['value'])) {
					//facturation integral du mois
					$pdf_file = 'RemboursementTransporteurs' . $data_to_display['facturation_year']['value'] . '_'. $data_to_display['facturation_month']['value'] . '.pdf';

					if (isset($data_to_display['reload']['value']) && $data_to_display['reload']['value'] === FALSE) {
						$pdf->Output( $base_dir . '/' . $directory_facturation . '/' . $data_to_display['facturation_year']['value'] . '/' . $data_to_display['facturation_month']['value'] . '/' . $pdf_file, 'I');
					} else {
						$pdf->Output( $base_dir . '/' . $directory_facturation . '/' . $data_to_display['facturation_year']['value'] . '/' . $data_to_display['facturation_month']['value'] . '/' . $pdf_file, 'F');

						//retourne le lien puis recharge le rapport transporteur
						$html_code .= '<p>';
							$html_code .= '<label>Fichier nouvellement crée : </label>';
							$html_code .= '<a href="http://' . $server_name . '/' . $projet_name . '/' . $directory_facturation . '/' . $data_to_display['facturation_year']['value'] . '/' . $data_to_display['facturation_month']['value'] . '/' . $pdf_file . '">';
								$html_code .= $pdf_file;
							$html_code .= '</a>';
						$html_code .='</p>';
					}
				} else {
					//facture individuel
					$tmp_transporteur = new Transporteur($data_to_display['id_transporteur']['value']);
					$tmp_transporteur_nom_complet = $tmp_transporteur->get_nom_complet();
					$pdf_file = 'RemboursementTransporteur' . '_' . $data_to_display['facturation_year']['value'] . '_'. $data_to_display['facturation_month']['value'] . '_' . stripAccents($tmp_transporteur_nom_complet['nom']) . '_' . stripAccents($tmp_transporteur_nom_complet['prenom']) . '.pdf';

					if (isset($data_to_display['reload']['value']) && $data_to_display['reload']['value'] === FALSE) {
						$pdf->Output( $base_dir . '/' . $directory_facturation . '/' . $data_to_display['facturation_year']['value'] . '/' . $data_to_display['facturation_month']['value'] . '/' . $pdf_file, 'I');
					} else {

						$pdf->Output( $base_dir . '/' . $directory_facturation . '/' . $data_to_display['facturation_year']['value'] . '/' . $data_to_display['facturation_month']['value'] . '/' . $pdf_file, 'F');

						//retourne le lien puis recharge le rapport beneficiaire
						$html_code .= '<p>';
							$html_code .= '<label>Fichier nouvellement crée : </label>';
							$html_code .= '<a href="http://' . $server_name . '/' . $projet_name . '/' . $directory_facturation . '/' . $data_to_display['facturation_year']['value'] . '/' . $data_to_display['facturation_month']['value'] . '/' . $pdf_file . '">';
								$html_code .= $pdf_file;
							$html_code .= '</a>';
						$html_code .= '</p>';

					}
				}
			}
		}

		if (isset($data_to_display['reload']['value']) && $data_to_display['reload']['value'] === FALSE) {

		} else {
			$html_code .= Filiale::form_rapport_transporteur($action);
			$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));
			return $html_code;
		}
	} // class.Filiale.form.pdf_remboursement_transporteur


	private static function form_restore($action, $data_to_display='') {
		$html_code = '';

		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}

		if (isset($data_to_display['zip_file_to_restore']['value']) && stripos($data_to_display['zip_file_to_restore']['value'], '.zip')) {

			//extraction du fichier zip
			global $base_dir;
			unzip($data_to_display['zip_file_to_restore']['value'], $base_dir . '/', TRUE);

			//repere le dump sql
			$dmp_file_path = $base_dir . '/asbv_transport_local-sqldump.sql';

			global $cfg;
			set_time_limit(0); //pour que le script prenne le temps de bien tout restaurer
			executeSQLInstructionsFromFile($cfg['DATABASE']['host'], $cfg['DATABASE']['user'], $cfg['DATABASE']['password'], $cfg['DATABASE']['database'], $dmp_file_path);

			set_time_limit(30);


			$html_code .= '<script type="text/javascript">';
				$html_code .= '$(document).ready(function() {';
					$html_code .= '$( "#div_dialog" ).dialog({ disabled: true });';
					$html_code .= 'html = \'<div class="hide" id="div_dialog">Restauration terminée</div>\';';
					$html_code .= '$("#div_dialog").remove();';
					$html_code .= '$(".ui-dialog").remove();';
					$html_code .= '$("#container").append(html);';
					$html_code .= '$("#div_dialog").dialog({ height: 150, width: 350 });';
				$html_code .= '});';
			$html_code .= '</script>';


			$html_code .= '<strong>Restauration terminée</strong>';
		} else {
			//chargement du formulaire de restauration
			global $base_dir;
			global $configBackupDir;

			//pour eviter un probleme de listing des fichiers dans la ligne suivante
			if (!is_dir($base_dir . '/backup/' . $configBackupDir)) {
				mkdir($base_dir . '/backup/' . $configBackupDir);
			}

			$liste_files_backup_dir = list_file_in_directory($base_dir . '/backup/' . $configBackupDir);


			$html_code .= '<form id="form_restore" action="" method="post" enctype="multipart/form-data">';

				if (count($liste_files_backup_dir) > 0) {
					$html_code .= '<label for="zip_file_to_restore">Restauration d\'un fichier local</label>';
					$html_code .= '<select id="zip_file_to_restore" name="zip_file_to_restore">';

						$html_code .= '<option></option>';

						foreach($liste_files_backup_dir as $row) {

							if (stripos(basename($row), 'ackup') && stripos(basename($row), '.zip')) {
								$html_code .= '<option value="' . $row . '">';
									$html_code .= basename($row);
								$html_code .= '</option>';
							}
						}

					$html_code .= '</select>';
				}

				$html_code .= '<p>';
					$html_code .= '<input name="upload_file" type="file" size="50" maxlength="100000" accept="7ip">';
				$html_code .= '</p>';


				$html_code .= '<p>';

					if (isset($data_to_display['id']['value'])) {
						$html_code .= add_FormElement_input('hidden', 'id', '', $data_to_display['id']['value']);
					}

					$html_code .= add_FormElement_input('hidden', 'form', '', 'base');
					$html_code .= add_FormElement_input('hidden', 'module', '', 'filiale');
					$html_code .= add_FormElement_input('hidden', 'sub_module', '', '""');
					$html_code .= add_FormElement_input('hidden', 'action', '', $action);

					$html_code .= '<input type="submit" value="Soumettre" />';
				$html_code .= '</p>';


			$html_code .= '</form>';

		}

		$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));

		return $html_code;
	} // class.Filiale.form.restore


	private static function form_permanence($action, $data_to_display='') {

		$mois[1] = 'janvier';
		$mois[2] = 'février';
		$mois[3] = 'mars';
		$mois[4] = 'avril';
		$mois[5] = 'mai';
		$mois[6] = 'juin';
		$mois[7] = 'juillet';
		$mois[8] = 'août';
		$mois[9] = 'septembre';
		$mois[10] = 'octobre';
		$mois[11] = 'novembre';
		$mois[12] = 'décembre';

		$jours[1] = 'lundi';
		$jours[2] = 'mardi';
		$jours[3] = 'mercredi';
		$jours[4] = 'jeudi';
		$jours[5] = 'vendredi';
		$jours[6] = 'samedi';
		$jours[7] = 'dimanche';

		$date_from = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));

		if (date('m') == 12) {
			$month_to = 1;
			$year_to = date('Y') + 1;
		} else {
			$month_to = date('m') + 1;
			$year_to = date('Y');
		}

		$date_to = lastday($month_to, $year_to);

		$array_date = array_date_between_2_dates(1, date('m'), date('Y'), date('d', strtotime($date_to)), $month_to, $year_to );

		//remonte les donnees deja presente pour les 2 prochains mois
		global $dbh;

		$sql = "SELECT permanence.* ";
		$sql .= " FROM permanence ";
		$sql .= " WHERE id_filiale=" . $_SESSION['filiale']['id'];
		$sql .= " AND date BETWEEN " . $dbh->quote($date_from);
		$sql .= " AND " . $dbh->quote($date_to);

		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);
		$array_date_permanence = array();
		foreach( $result as $date) {
			$array_date_permanence[$date['date']] = $date['id_permanencier'];
		}


		$sql = "SELECT benevole_participation_filiale.id, benevole.nom, benevole.prenom ";
		$sql .= " FROM benevole_participation_filiale INNER JOIN benevole ON benevole_participation_filiale.id_benevole = benevole.id ";
		$sql .= " WHERE benevole_participation_filiale.id_filiale=" . $_SESSION['filiale']['id'];
		$sql .= " AND benevole_participation_filiale.is_active=1 ";
		$sql .= " AND benevole_participation_filiale.is_permanencier=1 ";
		$sql .= " ORDER BY benevole.nom";

		$sth = $dbh->query($sql);
		$list_permanencier = $sth->fetchAll(PDO::FETCH_ASSOC);

		$html_code = '';

		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}

		$last_month = 0;

		$html_code .= '<form action="" method="post">';
			foreach($array_date as $index => $date) {
				if (date('N', strtotime($date)) != 6 && date('N', strtotime($date)) != 7) {

					if ($last_month != date('m', strtotime($date))) {
						$html_code .= '<h2>';
							foreach($mois as $index_mois_txt => $mois_txt) {
								if (date('m', strtotime($date)) == $index_mois_txt) {
									$html_code .= ucfirst($mois_txt);
									$last_month = $index_mois_txt;
								}
							}
						$html_code .= '</h2>';
					}

					/*
					if ($index % 2 == 0) {
						$class = 'table_odd';
					} else {
						$class = 'table_even';
					}
					*/

					$html_code .= '<p>';
					//$html_code .= '<p class="' . $class . '">';
						$html_code .= '<label for="' . $date . '">';

							if (date('N', strtotime($date)) == 1) {
								$html_code .= '<br />';
							}

							$html_code .= date_yyyymmdd_to_ddmmyyyy($date);

							foreach($jours as $index_jour => $jour) {
								if (date('N', strtotime($date)) == $index_jour ) {
									$html_code .= ' - ' . $jour;
								}
							}

						$html_code .= '</label>';

						$html_code .= '<select id="' . $date . '" name="' . $date . '">';
							$html_code .= '<option></option>';

							foreach($list_permanencier as $permanencier) {
								$html_code .= '<option value="' . $permanencier['id'] . '" ';

									if (array_key_exists($date, $array_date_permanence) && $array_date_permanence[$date] == $permanencier['id']) {
										$html_code .= 'selected="selected">';
									} else {
										$html_code .= '>';
									}

									$html_code .= mb_strtoupper(stripAccents($permanencier['nom'])) . ' ' . $permanencier['prenom'];
								$html_code .= '</option>';
							}
						$html_code .= '</select>';

					$html_code .= '</p>';
				}
			}

			$html_code .= '<p>';


				$html_code .= add_FormElement_input('hidden', 'form', '', 'permanence');
				$html_code .= add_FormElement_input('hidden', 'module', '', 'filiale');
				$html_code .= add_FormElement_input('hidden', 'sub_module', '', 'completed');
				$html_code .= add_FormElement_input('hidden', 'action', '', $action);

				$html_code .= '<input type="submit" value="Valider et retour à l\'accueil" />';
			$html_code .= '</p>';


		$html_code .= '</form>';

		$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));

		return $html_code;

	} // class.Filiale.form.permanence


	private static function form_admin($action, $data_to_display='') {

		global $base_dir;

		$tmp_benevole = new Benevole($_SESSION['benevole']['id']);

		if ($tmp_benevole->checkIsAdminOfFiliale($_SESSION['filiale']['id']) || $tmp_benevole->checkIsSuperAdmin()) {

		} else {
			die();
		}

		$html_code = '';

		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}


		//stats filiale
		$tmp_filiale = new Filiale($_SESSION['filiale']['id']);
		global $dbh;

		$mois[1] = 'janvier';
		$mois[2] = 'février';
		$mois[3] = 'mars';
		$mois[4] = 'avril';
		$mois[5] = 'mai';
		$mois[6] = 'juin';
		$mois[7] = 'juillet';
		$mois[8] = 'août';
		$mois[9] = 'septembre';
		$mois[10] = 'octobre';
		$mois[11] = 'novembre';
		$mois[12] = 'décembre';

		$sql = "SELECT MONTH(transport.date_transport) AS month, YEAR(transport.date_transport) AS year, COUNT(transport.id) AS count_trajet, SUM(transport.nbre_kilometres) AS sum_km, SUM(transport.cout_trajet) AS sum_cout ";
		$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport ";
		$sql .= " WHERE transport.id_filiale=" . $_SESSION['filiale']['id'];
		$sql .= " AND transport.is_annule=0";
		$sql .= " GROUP BY MONTH(transport.date_transport), YEAR(transport.date_transport)";
		$sql .= " ORDER BY YEAR(transport.date_transport) DESC,  MONTH(transport.date_transport) DESC";
		$sql .= " LIMIT 13";

		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);


		if (count($result) > 0) {

			$html_code .= '<h1>Statistiques</h1>';

			$html_code .= '<table id="stat_filiale">';
				$html_code .= '<thead>';
					$html_code .= '<tr>';
						$html_code .= '<th>Mois</th>';
						$html_code .= '<th>Trajets</th>';
						$html_code .= '<th>Kilomètres</th>';
						$html_code .= '<th>Coûts</th>';
					$html_code .= '</tr>';
				$html_code .= '</thead>';

				$html_code .= '<tbody>';

					$last_year = '';

					foreach ($result as $row) {

						if ($row['month'] == date('m')) {
							$class_row = 'highlights';
						} else {
							$class_row = '';
						}

						if ($row['year'] != $last_year) {
							$html_code .= '<tr>';
								$html_code .= '<th>';
									$html_code .= $row['year'];
									$last_year = $row['year'];
								$html_code .= '</th>';
							$html_code .= '</tr>';
						}

						$html_code .= '<tr class="' . $class_row . '">';


							$html_code .= '<td>';
								foreach ($mois as $index => $mois_txt) {
									if ($row['month'] == $index) {
										$html_code .= ucfirst($mois_txt);
										break;
									}
								}
							$html_code .= '</td>';

							$tmp_stat = $tmp_filiale->get_stats($row['month'], $row['year']);
							$tag_title = 'Trajet simple : ' . $tmp_stat['trajet_details']['trajet_AS'] . ', Trajet aller-retour : ' . $tmp_stat['trajet_details']['trajet_AR'] . ', Trajet double : ' . $tmp_stat['trajet_details']['trajet_DOUBLE'];
							$html_code .= '<td title="' . $tag_title . '">';
								$html_code .= $tmp_stat['trajets'];
							$html_code .= '</td>';

							$html_code .= '<td>';
								$html_code .= number_format($row['sum_km'], 0, '.', '\'');
							$html_code .= '</td>';

							$html_code .= '<td>';
								$html_code .= number_format($row['sum_cout'], 0, '.', '\'');
							$html_code .= '</td>';
						$html_code .= '</tr>';
					}

				$html_code .= '</tbody>';

			$html_code .= '</table>';
		}


		// listings
		load_class_and_interface(array('Listing'));

		$html_code .= '<h1>Listings</h1>';

			// listing permanenciers
			$sql = "SELECT benevole.*, benevole_participation_filiale.* ";
			$sql .= " FROM benevole_participation_filiale INNER JOIN benevole ON benevole_participation_filiale.id_benevole = benevole.id ";
			$sql .= " WHERE benevole_participation_filiale.id_filiale=" . $_SESSION['filiale']['id'];
			$sql .= " AND benevole_participation_filiale.is_active=1";
			$sql .= " AND benevole_participation_filiale.is_permanencier=1";
			//$sql .= " AND benevole_participation_filiale.is_transporteur=1";
			$sql .= " ORDER BY benevole.nom, benevole.prenom";

			$sth = $dbh->query($sql);
			$array_permanencier = $sth->fetchAll(PDO::FETCH_ASSOC);


			// create a PDF object
			$pdf = new Listing(0, 'permanencier');

			//protection
			global $cfg;
			$pdf->SetProtection(array('copy', 'modify'), $cfg['PDF']['password']);


			// set document (meta) information
			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor('ASBV Section ' . ucfirst(stripAccents($tmp_filiale->get_nom())));
			$pdf->SetTitle('Liste permanenciers');
			$pdf->SetSubject('Liste permanenciers');
			$pdf->SetKeywords('ASBV, ' . ucfirst(stripAccents($tmp_filiale->get_nom())) . ', facture, passager');

			$pdf->AddPage();
			$marginL = 20;
			$marginB = 20;
			$posY = 20;
			$posX_nom = 35 - $marginL;
			$posX_npa = 40 - $marginL;
			$posX_ville = 55 - $marginL;
			$posX_adresse = 120 - $marginL;
			$posX_tel_1 = 140 - $marginL;

			foreach($array_permanencier as $index => $row) {

				if ($posY >= 290 - $marginB) {
					$pdf->AddPage();
					$posY = 20;
				}

				$pdf->CreateTextBox(mb_strtoupper(stripAccents($row['nom'])) . ' ' . $row['prenom'], $posX_nom, $posY, 25, 10, 10, 'B', 'L');

				$base=0;
				if ($row['tel_fixe'] != '') {
					$pdf->CreateTextBox(format_tel($row['tel_fixe']), $posX_tel_1, $posY, 15, 10, 10, '', 'L');
					$base = 30;
				}

				if ($row['tel_mobile'] != '') {
					$pdf->CreateTextBox(format_tel($row['tel_mobile']), $posX_tel_1 + $base, $posY, 15, 10, 10, '', 'L');
					$base = 30;
				}

				$posY += 5;

				$pdf->CreateTextBox($row['npa'], $posX_npa, $posY, 5, 10, 10, '', 'L');
				$pdf->CreateTextBox($row['ville'], $posX_ville, $posY, 30, 10, 10, '', 'L');
				$pdf->CreateTextBox($row['adresse'], $posX_adresse, $posY, 50, 10, 10, '', 'L');

				if (count($array_permanencier) > $index + 1) {
					$pdf->Line(10, $posY+10, 205, $posY+10);
				}

				$posY += 10;
			}

			if ($posY >= 290 - $marginB) {
				$pdf->AddPage();
				$posY = 20;
			}

			$pdf->SetLineWidth(0.5);
			$pdf->Line(10, $posY+10, 205, $posY+10);
			$pdf->CreateTextBox('TOTAL ' . count($array_permanencier) . ' permanenciers ', $posX_adresse, $posY+10, 50, 10, 10, 'B', 'L');


			$projet_name = substr($_SERVER['SCRIPT_NAME'], 1, (strpos($_SERVER['SCRIPT_NAME'], '/', 1))-1);
			$server_name = $_SERVER['SERVER_NAME'];
			global $cfg;

			$directory_listing = $cfg['DIRECTORY']['listing'];

			if (!is_dir('../' . $directory_listing)) {
				mkdir('../' . $directory_listing);
			}

			$file_listing_permanenciers = 'listing_permanenciers.pdf';
			$pdf->Output( $base_dir . '/' . $directory_listing . '/' . $file_listing_permanenciers, 'F');


			$html_code .= '<p>';
				$html_code .= '<a href="http://' . $server_name . '/' . $projet_name . '/' . $directory_listing . '/' . $file_listing_permanenciers . '">';
					$html_code .= 'Listing des permanenciers';
				$html_code .= '</a>';
			$html_code .= '</p>';



			// listings benevoles
			$sql = "SELECT benevole.*, benevole_participation_filiale.* ";
			$sql .= " FROM benevole_participation_filiale INNER JOIN benevole ON benevole_participation_filiale.id_benevole = benevole.id ";
			$sql .= " WHERE benevole_participation_filiale.id_filiale=" . $_SESSION['filiale']['id'];
			$sql .= " AND benevole_participation_filiale.is_active=1";
			//$sql .= " AND benevole_participation_filiale.is_permanencier=1";
			$sql .= " AND benevole_participation_filiale.is_transporteur=1";
			$sql .= " ORDER BY benevole.nom, benevole.prenom";

			$sth = $dbh->query($sql);
			$array_benevoles = $sth->fetchAll(PDO::FETCH_ASSOC);


			// create a PDF object
			$pdf = new Listing(0, 'bénévole');

			//protection
			global $cfg;
			$pdf->SetProtection(array('copy', 'modify'), $cfg['PDF']['password']);


			// set document (meta) information
			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor('ASBV Section ' . ucfirst(stripAccents($tmp_filiale->get_nom())));
			$pdf->SetTitle('Liste bénévoles');
			$pdf->SetSubject('Liste bénévoles');
			$pdf->SetKeywords('ASBV, ' . ucfirst(stripAccents($tmp_filiale->get_nom())) . ', listing, bénévoles');

			$pdf->AddPage();
			$marginL = 20;
			$marginB = 20;
			$posY = 20;
			$posX_nom = 35 - $marginL;
			$posX_npa = 40 - $marginL;
			$posX_ville = 55 - $marginL;
			$posX_adresse = 120 - $marginL;
			$posX_tel_1 = 140 - $marginL;

			foreach($array_benevoles as $index => $row) {

				if ($posY >= 290 - $marginB) {
					$pdf->AddPage();
					$posY = 20;
				}

				$pdf->CreateTextBox(mb_strtoupper(stripAccents($row['nom'])) . ' ' . $row['prenom'], $posX_nom, $posY, 25, 10, 10, 'B', 'L');

				$base=0;
				if ($row['tel_fixe'] != '') {
					$pdf->CreateTextBox(format_tel($row['tel_fixe']), $posX_tel_1, $posY, 15, 10, 10, '', 'L');
					$base = 30;
				}

				if ($row['tel_mobile'] != '') {
					$pdf->CreateTextBox(format_tel($row['tel_mobile']), $posX_tel_1 + $base, $posY, 15, 10, 10, '', 'L');
					$base = 30;
				}

				$posY += 5;

				$pdf->CreateTextBox($row['npa'], $posX_npa, $posY, 5, 10, 10, '', 'L');
				$pdf->CreateTextBox($row['ville'], $posX_ville, $posY, 30, 10, 10, '', 'L');
				$pdf->CreateTextBox($row['adresse'], $posX_adresse, $posY, 50, 10, 10, '', 'L');

				if (count($array_benevoles) > $index + 1) {
					$pdf->Line(10, $posY+10, 205, $posY+10);
				}

				$posY += 10;
			}

			if ($posY >= 290 - $marginB) {
				$pdf->AddPage();
				$posY = 20;
			}

			$pdf->SetLineWidth(0.5);
			$pdf->Line(10, $posY+10, 205, $posY+10);
			$pdf->CreateTextBox('TOTAL ' . count($array_benevoles) . ' bénévoles ', $posX_adresse, $posY+10, 50, 10, 10, 'B', 'L');


			$projet_name = substr($_SERVER['SCRIPT_NAME'], 1, (strpos($_SERVER['SCRIPT_NAME'], '/', 1))-1);
			$server_name = $_SERVER['SERVER_NAME'];
			global $cfg;

			$directory_listing = $cfg['DIRECTORY']['listing'];

			if (!is_dir('../' . $directory_listing)) {
				mkdir('../' . $directory_listing);
			}

			$file_listing_benevoles = 'listing_benevole.pdf';
			$pdf->Output( $base_dir . '/' . $directory_listing . '/' . $file_listing_benevoles, 'F');


			$html_code .= '<p>';
				$html_code .= '<a href="http://' . $server_name . '/' . $projet_name . '/' . $directory_listing . '/' . $file_listing_benevoles . '">';
					$html_code .= 'Listing des bénévoles';
				$html_code .= '</a>';
			$html_code .= '</p>';




		// membres + stats
		$tmp_filiale->mountListBenevole();
		Filiale::extract_csv_stats_transporteur();


		$periode_histo = 3;

		for ($i=0;$i<=$periode_histo;$i++) {

			if (date('n') - $i <= 0) {
				$date_m_1 = date('n') - $i + 12;
				$date_y_1 = date('Y') - 1;
			} else {
				$date_m_1 = date('n') - $i;
				$date_y_1 = date('Y');
			}

			$sql = "SELECT transport_transporteur.id_transporteur, COUNT(transport.id) AS count_trajet, SUM(transport.nbre_kilometres) AS sum_km, SUM(transport.cout_variable + ((transport.taux_remboursement_transporteur/100) * transport.cout_trajet)) AS sum_remboursement ";
			$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport ";
			$sql .= " WHERE transport.id_filiale=" . $_SESSION['filiale']['id'];
			$sql .= " AND transport.is_annule=0";
			$sql .= " AND MONTH(transport.date_transport)=" . $date_m_1;
			$sql .= " AND YEAR(transport.date_transport)=" . $date_y_1;
			$sql .= " GROUP BY transport_transporteur.id_transporteur";

			$sth = $dbh->query($sql);
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);

			if (count($result) == 0) {
				$stat_chauffeur_previous_months[$i] = array();
			} else {
				foreach ($result as $row) {
					$stat_chauffeur_previous_months[$i][$row['id_transporteur']] = array('trajet' => $row[count_trajet], 'km' => $row['sum_km'], 'remboursement' => $row['sum_remboursement']);
				}
			}
		}


		if (count($tmp_filiale->array_benevole) > 0) {

			$j = 1;

			$html_code .= '<h1>Membres et statistiques du mois en cours</h1>';

			$html_code .= '<table class="OddEven">';
				$html_code .= '<thead>';
					$html_code .= '<tr>';
						$html_code .= '<th></th>';
						$html_code .= '<th>Nom</th>';
						$html_code .= '<th></th>'; // reinit password
						$html_code .= '<th>Transporteur</th>';

						for ($i=0;$i<=$periode_histo;$i++) {
							$html_code .= '<th>';
								$html_code .= 'Trajets ';

								if (date('n') - $i <= 0) {
									$date_m_1 = date('n') - $i + 12;
								} else {
									$date_m_1 = date('n') - $i;
								}

								foreach($mois as $index => $row) {
									if ($date_m_1 == $index) {
										$html_code .= $row;
										break;
									}
								}

							$html_code .= '</th>';

						}

						$html_code .= '<th>Moyenne</th>';

					$html_code .= '</tr>';
					//fonctions

				$html_code .= '</thead>';

				$html_code .= '<tbody>';

					foreach ($tmp_filiale->array_benevole as $index => $row) {
						$tmp_benevole_nom_complet = $row->get_nom_complet();

						//class de benevole active/inactive
						if ($row->is_active_in_filiale($_SESSION['filiale']['id']) == 0) {
							$class_benevole = 'benevole_inactive';
						} else {
							$class_benevole = 'benevole_active';
						}

						$html_code .= '<tr>';

							// compteur
							$html_code .= '<td class="' . $class_benevole . '">';
								$html_code .= $j;
								$j++;
							$html_code .= '</td>';


							//nom + prenom
							$html_code .= '<td class="' . $class_benevole . '">';
								$html_code .= '<a href="?module=benevole&amp;action=edit&amp;id=' . $row->get_id($_SESSION['filiale']['id']) . '">';
									$html_code .= mb_strtoupper(stripAccents($tmp_benevole_nom_complet['nom'])) . ', ' . $tmp_benevole_nom_complet['prenom'];
								$html_code .= '</a>';
							$html_code .= '</td>';


							//reinit password
							$html_code .= '<td class="' . $class_benevole . '">';
								$html_code .= '<a href="?module=benevole&amp;action=reinit_password&amp;id_filiale_benevole=' . $row->get_id($_SESSION['filiale']['id']) . '">';
									$html_code .= '<img title="Réinitialiser le mot de passe"src="./img/key.png" />';
								$html_code .= '</a>';
							$html_code .= '</td>';

							//transporteur ?
							$html_code .= '<td class="' . $class_benevole . '">';
								if ($row->checkIsTransporteur($_SESSION['filiale']['id'])) {

									$tmp_transporteur = new Transporteur($row->get_id($_SESSION['filiale']['id']));

									if ($tmp_transporteur->check_transports_locaux()) {
										//$html_code .= '<strong>L</strong>|';
									} else {
										//$html_code .= '-|';
									}

									if ($tmp_transporteur->check_transports_geneve()) {
										//$html_code .= '<strong>Ge</strong>|';
										$html_code .= '<img src="./img/geneve.png" />';
									} else {
										//$html_code .= '--|';
									}

									if ($tmp_transporteur->check_transports_lausanne()) {
										//$html_code .= '<strong>La</strong>|';
										$html_code .= '<img src="./img/lausanne.png" />';
									} else {
										//$html_code .= '--|';
									}

									if ($tmp_transporteur->check_transports_vacances()) {
										//$html_code .= '<strong>V</strong>|';
										$html_code .= '<img src="./img/vacances.png" />';
									} else {
										//$html_code .= '-|';
									}

								} else {
									$html_code .= '';
								}
							$html_code .= '</td>';


							//histo trajet + km
							$count_trajet = 0;
							$sum_km = 0;
							for ($i=0;$i<=$periode_histo;$i++) {
								$html_code .= '<td class="' . $class_benevole . '">';
									if ($row->checkIsTransporteur($_SESSION['filiale']['id']) && key_exists($tmp_transporteur->get_id_transporteur(), $stat_chauffeur_previous_months[$i])) {
										$html_code .= $stat_chauffeur_previous_months[$i][$tmp_transporteur->get_id_transporteur()]['trajet'] . ' ('.  number_format($stat_chauffeur_previous_months[$i][$tmp_transporteur->get_id_transporteur()]['km'], 0) . ')';

										//évite de faire une moyenne sur le mois en cours
										if ($i > 0) {
											$count_trajet += $stat_chauffeur_previous_months[$i][$tmp_transporteur->get_id_transporteur()]['trajet'];
											$sum_km += $stat_chauffeur_previous_months[$i][$tmp_transporteur->get_id_transporteur()]['km'];
										}
									} else {
										$html_code .= '0';
									}
								$html_code .= '</td>';
							}


							//moyenne
							$moyenne_trajet = number_format(($count_trajet/($periode_histo)), 0);
							$moyenne_km = number_format(($sum_km/($periode_histo)), 0);

							if ($moyenne_trajet < 2 && $class_benevole == 'benevole_active') {
								$class = 'highlights';
							} else {
								$class = '';
							}

							$html_code .= '<td class="' . $class . '">';
								$html_code .= $moyenne_trajet . ' (' . $moyenne_km . ')' ;
							$html_code .= '</td>';


						$html_code .= '</tr>';
					}


					$html_code .= '</tbody>';

			$html_code .= '</table>';
		}

		$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));

		return $html_code;

	} // class.Filiale.form.admin


	private static function form_load_reference($action, $data_to_display='') {
		load_class_and_interface(array('Beneficiaire' , 'Transporteur'));

		global $dbh;
		$html_code = '';

		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}

		$sql = "SELECT transport_transporteur.*, transport.* ";
		$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport ";
		$sql .= " WHERE transport.id_filiale=" . $_SESSION['filiale']['id'];
		$sql .= " AND transport.is_annule=0";
		$sql .= " AND YEAR(transport.date_transport)=" . $data_to_display['year']['value'];
		$sql .= " AND MONTH(transport.date_transport)=" . $data_to_display['month']['value'];

		if ($data_to_display['ref_type']['value'] == 'facture') {
			$sql .= " AND transport.id_beneficiaire=" . $data_to_display['id']['value'];
		} elseif ($data_to_display['ref_type']['value'] == 'remboursement') {
			$sql .= " AND transport_transporteur.id_transporteur=" . $data_to_display['id']['value'];
		}

		$sql .= " ORDER BY transport.date_transport, transport.heure_debut ";

		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);


		if (count($result) > 0) {

			$mois[1] = 'janvier';
			$mois[2] = 'février';
			$mois[3] = 'mars';
			$mois[4] = 'avril';
			$mois[5] = 'mai';
			$mois[6] = 'juin';
			$mois[7] = 'juillet';
			$mois[8] = 'août';
			$mois[9] = 'septembre';
			$mois[10] = 'octobre';
			$mois[11] = 'novembre';
			$mois[12] = 'décembre';

			//detail sur la requete

			$html_code .= '<table>';
				$html_code .= '<thead>';
					$html_code .= '<tr>';


						if ($data_to_display['ref_type']['value'] == 'facture') {
							$html_code .= '<th>Passager</th>';
						} elseif ($data_to_display['ref_type']['value'] == 'remboursement') {
							$html_code .= '<th>Transporteur</th>';
						}


						$html_code .= '<th>Mois</th>';
						$html_code .= '<th>Année</th>';
					$html_code .= '</tr>';
				$html_code .= '</thead>';



				$html_code .= '<tbody>';
					$html_code .= '<tr>';

						$html_code .= '<td>';
							if ($data_to_display['ref_type']['value'] == 'facture') {
								$tmp_beneficiaire = new Beneficiaire($data_to_display['id']['value']);
								$tmp_beneficiaire_nom = $tmp_beneficiaire->get_nom_complet();

								$html_code .= '<a class="link_dialog" href="?module=beneficiaire&amp;action=view&amp;id=' . $data_to_display['id']['value'] . '">';
									$html_code .= mb_strtoupper(stripAccents($tmp_beneficiaire_nom['nom'])) . ', ' . $tmp_beneficiaire_nom['prenom'];
								$html_code .= '</a>';
							} elseif ($data_to_display['ref_type']['value'] == 'remboursement') {
								$tmp_transporteur = new Transporteur($data_to_display['id']['value']);
								$tmp_transporteur_nom = $tmp_transporteur->get_nom_complet();

								$html_code .= '<a class="link_dialog" href="?module=benevole&amp;action=view&amp;id=' . $data_to_display['id']['value'] . '">';
									$html_code .= mb_strtoupper(stripAccents($tmp_transporteur_nom['nom'])) . ', ' . $tmp_transporteur_nom['prenom'];
								$html_code .= '</a>';
							}
						$html_code .= '</td>';

						$html_code .= '<td>';
							//$html_code .= $data_to_display['month']['value'];
							foreach ($mois as $index => $mois_txt) {
								if ($index == $data_to_display['month']['value'] ) {
									$html_code .= ucfirst($mois_txt);
									break;
								}
							}
						$html_code .= '</td>';

						$html_code .= '<td>';
							$html_code .= $data_to_display['year']['value'];
						$html_code .= '</td>';

					$html_code .= '</tr>';
				$html_code .= '</tbody>';
			$html_code .= '</table>';


			for ($i=0;$i<2;$i++) {
				$html_code .= '<br />';
			}


			$html_code .= '<table class="OddEven">';
				$html_code .= '<thead>';
					$html_code .= '<tr>';
						$html_code .= '<th>Date</th>';
						$html_code .= '<th>Heure</th>';

						if ($data_to_display['ref_type']['value'] == 'facture') {
							$html_code .= '<th>Transporteur</th>';
						} elseif ($data_to_display['ref_type']['value'] == 'remboursement') {
							$html_code .= '<th>Passager</th>';
						}

						$html_code .= '<th>Départ</th>';
						$html_code .= '<th>Arrivée</th>';
						$html_code .= '<th>Modifier</th>';
						$html_code .= '<th>Annuler</th>';
					$html_code .= '</tr>';
				$html_code .= '</thead>';

				$html_code .= '<tbody>';

					foreach ($result as $row) {
						$html_code .= '<tr>';
							//date
							$html_code .= '<td>';
								$html_code .= date_yyyymmdd_to_ddmmyyyy($row['date_transport']);
							$html_code .= '</td>';

							//heure
							$html_code .= '<td>';
								$html_code .= time_hhmmss_to_hhmm($row['heure_debut']);
							$html_code .= '</td>';


							if ($data_to_display['ref_type']['value'] == 'facture') {
								//transporteur
								$html_code .= '<td>';
									$tmp_transporteur = new Transporteur($row['id_transporteur']);
									$tmp_transporteur_nom = $tmp_transporteur->get_nom_complet();

									$html_code .= '<a class="link_dialog" href="?module=benevole&amp;action=view&amp;id=' . $row['id_transporteur'] . '">';
										$html_code .= mb_strtoupper(stripAccents($tmp_transporteur_nom['nom'])) . ', ' . $tmp_transporteur_nom['prenom'];
									$html_code .= '</a>';

								$html_code .= '</td>';
							} elseif ($data_to_display['ref_type']['value'] == 'remboursement') {
								//beneficiaire
								$html_code .= '<td>';
									$tmp_beneficiaire = new Beneficiaire($row['id_beneficiaire']);
									$tmp_beneficiaire_nom = $tmp_beneficiaire->get_nom_complet();

									$html_code .= '<a class="link_dialog" href="?module=beneficiaire&amp;action=view&amp;id=' . $row['id_beneficiaire'] . '">';
										$html_code .= mb_strtoupper(stripAccents($tmp_beneficiaire_nom['nom'])) . ', ' . $tmp_beneficiaire_nom['prenom'];
									$html_code .= '</a>';

								$html_code .= '</td>';
							}



							$point_depart = unserialize($row['point_depart']);
							$point_arrivee = unserialize($row['point_arrivee']);

							$html_code .= '<td>';
								$html_code .= mb_strtoupper(stripAccents($point_depart['ville']));
							$html_code .= '</td>';

							$html_code .= '<td>';
								$html_code .= mb_strtoupper(stripAccents($point_arrivee['ville']));
							$html_code .= '</td>';

							//modifier
							$html_code .= '<td>';
								$html_code .= '<a href="?module=transport&amp;action=edit&amp;id=' . $row['id'] . '">';
									$html_code .= 'Modifier';
								$html_code .= '</a>';
							$html_code .= '</td>';


							//annuler
							$html_code .= '<td>';
								$html_code .= '<a class="link_ajax_get" href="?module=transport&amp;action=cancel&amp;id=' . $row['id'] . '">';
									$html_code .= 'Annuler';
								$html_code .= '</a>';
							$html_code .= '</td>';

						$html_code .= '</tr>';
					}

				$html_code .= '</tbody>';
			$html_code .= '</table>';

		} else {
			$html_code .= '<p>Aucun résultat</p>';
		}

		$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));

		return $html_code;

	} // class.Filiale.form.load_reference

	private static function extract_csv_stats_transporteur() {
		load_class_and_interface(array('Transporteur'));

		global $dbh;

		$sql = "SELECT DISTINCT YEAR(transport.date_transport) as year, MONTH(transport.date_transport) as month";
		$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport ";
		$sql .= " WHERE transport.id_filiale=" . $_SESSION['filiale']['id'];
		$sql .= " AND transport.is_annule=0";
		$sql .= " ORDER BY YEAR(transport.date_transport), MONTH(transport.date_transport)";

		$sth = $dbh->query($sql);
		$distinct_date = $sth->fetchAll(PDO::FETCH_ASSOC);



		$sql = "SELECT transport_transporteur.id_transporteur, YEAR(transport.date_transport) as year, MONTH(transport.date_transport) as month, COUNT(transport.id) AS count_trajet, SUM(transport.nbre_kilometres) AS sum_km, SUM(transport.cout_variable + ((transport.taux_remboursement_transporteur/100) * transport.cout_trajet)) AS sum_remboursement ";
		$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport ";
		$sql .= " WHERE transport.id_filiale=" . $_SESSION['filiale']['id'];
		$sql .= " AND transport.is_annule=0";
		$sql .= " GROUP BY transport_transporteur.id_transporteur, YEAR(transport.date_transport), MONTH(transport.date_transport)";

		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		if (count($result) > 0) {
			//$last_transporteur = $result[0]['id_transporteur'];
			$last_transporteur = 0;
			$i = 0;

			$matrix_export[$i]['id'] = 'id';
			$matrix_export[$i]['nom'] = ',nom';

			//creation des colonnes
			foreach ($distinct_date as $date) {
				$matrix_export[$i][$date['year'] . '-' . $date['month']] = $date['year'] . '-' . $date['month'];
			}


			// preparation de la matrix d'export
			foreach ($result as $row) {
				if ($row['id_transporteur'] == $last_transporteur) {

				} else {
					$i++;
					$last_transporteur = $row['id_transporteur'];
					$matrix_export[$i]['id'] = $row['id_transporteur'];

					$tmp_transporteur = new Transporteur($row['id_transporteur']);
					$tmp_transporteur_nom_complet = $tmp_transporteur->get_nom_complet();
					$matrix_export[$i]['nom'] = ',' . mb_strtoupper(stripAccents($tmp_transporteur_nom_complet['nom'])) . '_' . stripAccents($tmp_transporteur_nom_complet['prenom']);

					//creation des colonnes
					foreach ($distinct_date as $date) {
						$matrix_export[$i][$date['year'] . '-' . $date['month']] = 0;
					}

				}

				//remplisage de la bonne colonne
				$matrix_export[$i][$row['year'] . '-' . $row['month']] = $row['count_trajet'];

			}


			//converti la matrix export en chaine
			$str = '';
			foreach ($matrix_export as $row) {

				foreach($row as $index => $column) {
					if ($index == 0) {
						$str .= $column;
					} else {
						$str .= ',' . $column;
					}
				}

				//new_line
				$str .= "\n";
			}


			global $cfg;
			if (!is_dir('../' . $cfg['DIRECTORY']['extract'])) {
				mkdir('../' . $cfg['DIRECTORY']['extract']);
			}


			$file_export_stats = fopen('../' . $cfg['DIRECTORY']['extract'] . '/export_stats_chauffeur.csv', 'wb');
			fwrite($file_export_stats, $str);


		}


	} // class.Filiale.function.extract_csv_stats_transporteur


} // class.Filiale

?>
