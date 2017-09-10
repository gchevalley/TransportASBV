<?php

//include('../../config/auth/secure.php');
require_once( str_replace ( '\\', '/', dirname(dirname(__FILE__))) . '/class.declaration.php' );
$load_needed_class_and_interface = load_class_and_interface(array('Contact', 'Filiale', 'Lieu_Categorie'));

class Lieu implements Contact {
	private $id = 0;
	private $nom = '';
	private $abreviation = '';
	private $id_categorie = 0;
	private $categorie = '';
	private $array_adresse = array();
	private $adresse = '';
	private $adresse_complement = '';
	private $npa = '';
	private $ville = '';
	private $pays = '';
	private $array_telephone = array();
	private $tel_fixe = '';
	private $tel_fax = '';
	private $tel_mobile = '';
	private $numero_important = 0;


	function __construct($id_lieu, $nom='', $id_categorie=0, $abreviation='', $adresse='', $adresse_complement='', $npa='', $ville='', $pays='', $tel_fixe='', $tel_fax='', $tel_mobile='') {
		if (is_numeric($id_lieu) && Lieu::id_exists($id_lieu)) {

			$this->id = $id_lieu;
			$this->mountAttributsFromDB();

		} else { //creation de la nouvelle entite
			$this->addEntryDB($nom, $id_categorie, $abreviation, $adresse, $adresse_complement, $npa, $ville, $pays, $tel_fixe, $tel_fax, $tel_mobile);
		}
	} // class.Lieu.func___construct


	private function mountAttributsFromDB() {

		//charge les donnees direct depuis la DB
		global $dbh;

		//mount la totalite des donnees
		$sql = "SELECT * FROM lieu WHERE id=" .$this->id;

		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		//s'assurer qu'un resultat est retourne bien alloue les donnees aux attributs de l'object
		$this->nom = $result['nom'];
		$this->id_categorie = $result['id_categorie'];
		$this->abreviation = $result['abreviation'];
		$this->adresse = $result['adresse'];
		$this->adresse_complement = $result['adresse_complement'];
		$this->npa = $result['npa'];
		$this->ville = $result['ville'];
		$this->pays = $result['pays'];
		$this->tel_fixe = $result['tel_fixe'];
		$this->tel_fax = $result['tel_fax'];
		$this->tel_mobile = $result['tel_mobile'];
		$this->numero_important = $result['numero_important'];

		$this->mountCategorie();

	} // class.Lieu.func.mountAttributsFromDB


	private function addEntryDB($nom='', $id_categorie=0, $abreviation='', $adresse='', $adresse_complement='', $npa='', $ville, $pays, $tel_fixe='', $tel_fax='', $tel_mobile='') {

		if (Benevole::id_exists($_SESSION['benevole']['id'])) {
			$tmp_benevole = new Benevole($_SESSION['benevole']['id']);

			if ($tmp_benevole->checkIsSuperAdmin()) {
				// continue l'execution de la function
			} else {
				if (Filiale::id_exists($_SESSION['filiale']['id'])) {
					if ($tmp_benevole->checkIsPermanencier($_SESSION['filiale']['id']) || $tmp_benevole->checkIsAdminOfFiliale($_SESSION['filiale']['id'])) {
						// continue l'execution de la function
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

		//ajoute l'entree geocode
		if (checkInternetConnection('maps.google.com')) {
			$load_needed_class_and_interface = load_class_and_interface(array('Geocode'));
			$array_coor = Geocode::find_combination($adresse, $npa, $ville, $pays, TRUE);
		}


		if (!Lieu::ville_exists($ville) || $id_categorie != Lieu_Categorie::get_id_from_categorie('ville')) {

			global $dbh;

			//processing de nettoyage des args de la fonction
			$nom = $dbh->quote($nom);
			$abreviation = $dbh->quote($abreviation);
			$adresse = $dbh->quote($adresse);
			$adresse_complement = $dbh->quote($adresse_complement);
			$npa = $dbh->quote($npa);
			$ville = $dbh->quote($ville);
			$pays = $dbh->quote($pays);
			$tel_fixe = $dbh->quote($tel_fixe);
			$tel_fax = $dbh->quote($tel_fax);
			$tel_mobile = $dbh->quote($tel_mobile);

			$today_date = $dbh->quote(date('Y-m-d'));
			$today_time = $dbh->quote(date('H:i:s'));


			//creation de la nouvelle entite dans la db
			$sql = "INSERT INTO lieu (nom, id_categorie, abreviation, adresse, adresse_complement, npa, ville, pays, tel_fixe, tel_fax, tel_mobile, insert_date, insert_time, insert_benevole_user, last_update_date, last_update_time, last_update_benevole_user) ";
			$sql .= "VALUES ($nom, $id_categorie, $abreviation, $adresse, $adresse_complement, $npa, $ville, $pays, $tel_fixe, $tel_fax, $tel_mobile, $today_date, $today_time, " . $tmp_benevole->get_id() . ", $today_date, $today_time, " . $tmp_benevole->get_id() .")";

			$statut_query = $dbh->exec($sql);

			//mount l'object
			$test = $dbh->lastInsertId();
			$this->id = $dbh->lastInsertId();
			$this->mountAttributsFromDB();

			//ajoute le lieu base sur la ville si n'existe pas deja
			if ($this->npa != '' && $this->ville != '' && $this->pays) {
				load_class_and_interface(array('Lieu'));
				Lieu::ajouterVille($this->ville, $this->npa, $this->pays);
			}

		}
	} // class.Lieu.func.addEntryDB



	public function editerAttributs($attr, $new_value) { //2 matrix ou 2 valeurs
		if (!is_numeric($_SESSION['benevole']['id']) || !Benevole::id_exists($_SESSION['benevole']['id'])) {
			die();
		}

		global $dbh;

		$sql = "UPDATE lieu ";

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
	} // class.Lieu.func.editerAttributs


	private function mountCategorie() {
		$tmp_categorie = new Lieu_Categorie($this->id_categorie);
		$this->categorie = $tmp_categorie->get_categorie();
	} // class.Lieu.func.mountCategorie


	public function get_id() {
		return $this->id;
	} // class.Lieu.func.get_id


	/*
	public static function get_id_from_ville($ville) {
		if (Lieu::ville_exists($ville)) {

		} else {
			return FALSE;
		}
	}
	*/


	public function get_nom_complet() {
		if ($this->nom != '') {
			return array('nom' => $this->nom);
		} else {
			return array('nom' => $this->ville);
		}
	} // class.Lieu.func.get_nom_complet


	private function mountAdresseArray() {
		$this->array_adresse['type'] = 'lieu';
		$this->array_adresse['id'] = $this->id;

		if ($this->nom != '') {
			$this->array_adresse['nom_complet'] = $this->nom;
		}

		if ($this->adresse != '') {
			$this->array_adresse['adresse'] = $this->adresse;
		}

		if ($this->adresse_complement != '') {
			$this->array_adresse['adresse_complement']= $this->adresse_complement;
		}

		if ($this->npa != '') {
			$this->array_adresse['npa'] = $this->npa;
		}

		if ($this->ville != '') {
			$this->array_adresse['ville'] = $this->ville;
		}

		if ($this->pays != '') {
			$this->array_adresse['pays'] = $this->pays;
		}

	} // class.Lieu.func.mountAdresseArray


	public function get_adresse() {
		$this->mountAdresseArray();
		return $this->array_adresse;
	} // class.Lieu.func.get_adresse


	public function get_abreviation() {
		$suffixe = '';

		if ($this->abreviation != '') {
			if (strpos(' ' . mb_strtoupper(stripAccents($this->abreviation)), mb_strtoupper(stripAccents($this->ville)))) {
				$suffixe = '';
			} else {
				$suffixe = ' ('  . $this->ville . ')';
			}
			return $this->abreviation . $suffixe;
		} else {
			if (strpos(' ' . mb_strtoupper(stripAccents($this->nom)), mb_strtoupper(stripAccents($this->ville)))) {
				$suffixe = '';
			} else {
				$suffixe = ' ('  . $this->ville . ')';
			}
			return $this->nom . $suffixe;
		}
	}

	public function get_id_categorie() {
		return $this->id_categorie;
	}


	private function group_telephone_into_array() {
		if ($this->tel_fixe != '') {
			$this->array_telephone['tel_fixe'] = $this->tel_fixe;
		}

		if ($this->tel_fax != '') {
			$this->array_telephone['tel_fax'] = $this->tel_fax;
		}

		if ($this->tel_mobile != '') {
			$this->array_telephone['tel_mobile'] = $this->tel_mobile;
		}
	} // class.Beneficiaire.func.group_telephone_into_array


	public function get_telephone() {
		$this->group_telephone_into_array();
		return $this->array_telephone;
	} // class.Lieu.func.get_telephone

	public static function id_exists($id_to_check) {
		if (checkID($id_to_check)) {
			global $dbh;
			$sql = "SELECT * FROM lieu WHERE id=" .$id_to_check;
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
	} //class.Lieu.func.id_exists

	public static function ville_exists($ville) {
		if (is_string($ville) && $ville != '') {
			global $dbh;
			$ville = $dbh->quote(mb_strtoupper(stripAccents($ville)));

			$sql = "SELECT id ";
			$sql .= "FROM lieu ";
			$sql .= " WHERE id_categorie=" . Lieu_Categorie::get_id_from_categorie('ville');
			$sql .= " AND nom LIKE " . $ville;

			$sth = $dbh->query($sql);

			$result = $sth->fetch(PDO::FETCH_ASSOC);

			if ($result != false) {
				return $result['id'];
			} else {
				return FALSE;
			}
		}
	}


	public static function ajouterVille($ville, $npa, $pays) {
		if ($ville != '' && $npa != '' && $pays != '' && !Lieu::ville_exists($ville)) {
			$tmp_lieu = new Lieu(0, mb_strtoupper(stripAccents($ville)), Lieu_Categorie::get_id_from_categorie('ville'), '','', '', $npa, $ville, $pays, '', '', '');
		}
	}

	public function declarer_comme_numero_important() {
		global $dbh;

		$sql = "UPDATE Lieu (numero_important=1) WHERE id=" . $this->get_id();

		$query_status = $dbh->exec($sql);
	}


	private function return_pair_key_value() {

		$tmp_array['id']['value']= $this->id;
		$tmp_array['nom']['value']= $this->nom;
		$tmp_array['abreviation']['value']= $this->abreviation;
		$tmp_array['id_categorie']['value']= $this->id_categorie;
		$tmp_array['adresse']['value']= $this->adresse;
		$tmp_array['adresse_complement']['value']= $this->adresse_complement;
		$tmp_array['npa']['value']= $this->npa;
		$tmp_array['ville']['value']= $this->ville;
		$tmp_array['pays']['value']= $this->pays;
		$tmp_array['tel_fixe']['value']= $this->tel_fixe;
		$tmp_array['tel_fax']['value']= $this->tel_fax;
		$tmp_array['tel_mobile']['value']= $this->tel_mobile;
		$tmp_array['numero_important']['value'] = $this->numero_important;

		return $tmp_array;
	} // class.Lieu.func.return_pair_key_value


	public static function form($action, $data_to_display='') {

		if (is_array($data_to_display)) {

		} elseif (is_numeric($data_to_display) && Lieu::id_exists($data_to_display)) {
			//numero de beneficaire
			$tmp_lieu = new Lieu($data_to_display);
			unset($data_to_display);
			$data_to_display = $tmp_lieu->return_pair_key_value();
		} elseif ($data_to_display instanceof Lieu) {
			//convertir en un tableau data_to_display_habituel
			$data_to_display = $data_to_display->return_pair_key_value();
		} else {
			$data_to_display = array();
		}

		switch ($action) {
			case "add":
				echo Lieu::form_base($action, $data_to_display);
				break;
			case "view":
				//s'assure que le beneficiaire est connu sinon charge une listbox de selection
				if (isset($data_to_display['id']['value']) && Lieu::id_exists($data_to_display['id']['value'])) {
					echo Lieu::form_view($action, $data_to_display);
				} else {
					echo Lieu::form_choose($action, $data_to_display);
				}
				break;
			case "edit":
				//s'assure que le beneficiaire est connu sinon charge une listbox de selection
				if (isset($data_to_display['id']['value']) && Lieu::id_exists($data_to_display['id']['value'])) {
					echo Lieu::form_base($action, $data_to_display);
				} else {
					echo Lieu::form_choose($action, $data_to_display);
				}
				break;
			case "list":
					echo Lieu::form_list($action);
			default:
				//echo Beneficiaire::form_base();
		}

	} // class.Lieu.func.form


	private static function form_base($action, $data_to_display='') {
		//retourne le code html du formulaire
		unset($_POST);

		$html_code = '';

		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}

		$html_code .= '<form id="lieu_' . $action . '" action="" method="post">';
			$html_code .= '<fieldset id="lieu_identite">';
				$html_code .= '<legend>Identité</legend>';

				$html_code .= '<p>';
					$html_code .= '<label for="nom">Nom du lieu</label>';
						if ( isset($data_to_display['nom']['value']) ) {
							$html_code .= add_FormElement_input('text', 'nom', array('input_adresse', 'disableAutoComplete', 'required'), $data_to_display['nom']['value']);
						} else {
							$html_code .= add_FormElement_input('text', 'nom', array('input_adresse', 'disableAutoComplete', 'required'), '');
						}
				$html_code .= '</p>';


				$html_code .= '<p>';
					$html_code .= '<label for="abreviation">Abréviation pour la facturation</label>';
						if ( isset($data_to_display['abreviation']['value']) ) {
							$html_code .= add_FormElement_input('text', 'abreviation', array('input_adresse', 'disableAutoComplete'), $data_to_display['abreviation']['value']);
						} else {
							$html_code .= add_FormElement_input('text', 'abreviation', array('input_adresse', 'disableAutoComplete'), '');
						}
				$html_code .= '</p>';

			$html_code .= '</fieldset>';

			$html_code .= '<fieldset id="lieu_adresse">';
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

					if( !isset($data_to_display['pays']['value']) || $data_to_display['pays']['value'] == '') {
						$html_code .= add_FormElement_input('text', 'pays', array('input_pays', 'disableAutoComplete'), 'Suisse');
					} else {
						$html_code .= add_FormElement_input('text', 'pays', array('input_pays', 'disableAutoComplete'), $data_to_display['pays']['value']);
					}

				$html_code .= '</p>';

			$html_code .= '</fieldset>';


			$html_code .= '<fieldset id="lieu_telephone">';
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
					$html_code .= '<label for="tel_fax">Téléphone fax</label>';
					if ( isset($data_to_display['tel_fax']['value']) ) {
						$html_code .= add_FormElement_input('text', 'tel_fax', array('input_tel', 'disableAutoComplete'), format_tel($data_to_display['tel_fax']['value']));
					} else {
						$html_code .= add_FormElement_input('text', 'tel_fax', array('input_tel', 'disableAutoComplete'), '');
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


			$html_code .= '<fieldset id="lieu_categorie">';
				$html_code .= '<legend>Catégorie</legend>';

				global $dbh;
				$sql = "SELECT * ";
				$sql .= " FROM lieu_categorie ";

				$sth = $dbh->query($sql);
				$result = $sth->fetchAll(PDO::FETCH_ASSOC);

				$html_code .= '<p>';
					$html_code .= '<label for"id_categorie">Catégorie</label>';
					$html_code .= '<select id="id_categorie" name="id_categorie">';

						foreach ($result as $row) {
							$html_code .= '<option value="' . $row['id'] . '" ';

							if ( isset($data_to_display['id_categorie']['value']) ) {
								if ($row['id'] == $data_to_display['id_categorie']['value']) {
									$html_code .= 'selected="selected">';
								} else {
									$html_code .= '>';
								}
							} else {
								$html_code .= '>';
							}

								$html_code .= ucfirst(stripAccents($row['categorie']));
							$html_code .= '</option>';
						}

					$html_code .= '</select>';
				$html_code .= '</p>';

				$html_code .= '<p>';
					$html_code .= '<label for ="numero_important">Numéro important ?</label>';
					if ( isset($data_to_display['numero_important']['value']) ) {
						$html_code .= add_FormElement_input('checkbox', 'numero_important', '', $data_to_display['numero_important']['value']);
					} else {
						$html_code .= add_FormElement_input('checkbox', 'numero_important', '', '');
					}
				$html_code .= '</p>';

			$html_code .= '</fieldset>';

			$html_code .= '<p>';

				if (isset($data_to_display['id']['value'])) {
					$html_code .= add_FormElement_input('hidden', 'id', '', $data_to_display['id']['value']);
				}

				$html_code .= add_FormElement_input('hidden', 'form', '', 'base');
				$html_code .= add_FormElement_input('hidden', 'module', '', 'lieu');
				$html_code .= add_FormElement_input('hidden', 'sub_module', '', '""');
				$html_code .= add_FormElement_input('hidden', 'action', '', $action);

				$html_code .= '<input type="submit" value="Soumettre" />';
			$html_code .= '</p>';

		$html_code .= '</form>';

		$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));
		return $html_code;
	} //class.Lieu.form.base


	private static function form_choose($action, $data_to_display='') {
		unset($_POST);
		global $dbh;
		$sql = "SELECT * FROM lieu ORDER BY nom";
		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		$html_code = '<form id="lieu_choose" action="" method="post">';
			$html_code .= '<select id="id" name="id">';
				foreach ($result as $row) {
					$html_code .= '<option value="' . $row['id'] . '">';
					$html_code .= strtoupper($row['nom']);
					$html_code .= '</option>';
				}
			$html_code .= '</select>';

			$html_code .= '<p>';
				$html_code .= add_FormElement_input('hidden', 'form', '', 'choose');
				$html_code .= add_FormElement_input('hidden', 'module', '', 'lieu');
				$html_code .= add_FormElement_input('hidden', 'sub_module', '', '""');
				$html_code .= add_FormElement_input('hidden', 'action', '', $action);

				$html_code .= '<input type="submit" value="Soumettre" />';
			$html_code .= '</p>';
		$html_code .= '</form>';

		//return utf8_encode($html_code);
		return ($html_code);
	} //class.Lieu.form.choose


	private static function form_list($action) {
		global $dbh;

		if (isset($_POST['search'])) {
			$sql = "SELECT lieu_categorie.*, lieu.* ";
			$sql .= " FROM lieu INNER JOIN lieu_categorie ON lieu.id_categorie = lieu_categorie.id ";
			$sql .= " WHERE lieu.nom LIKE '%" . $_POST['search'] . "%'";
			$sql .= " OR lieu_categorie.categorie LIKE '%" . $_POST['search'] . "%'";
			$sql .= " ORDER BY lieu.nom";
		} else {
			$sql = "SELECT lieu_categorie.*, lieu.* ";
			$sql .= " FROM lieu INNER JOIN lieu_categorie ON lieu.id_categorie = lieu_categorie.id ";
			$sql .= " ORDER BY lieu.nom";

		}

		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		$html_code = '';

		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}

		$html_code .= '<form id="lieu_seach_list"  action="" method="post">';
			$html_code .= '<p>';
				$html_code .= '<input type="text" id="search" name="search" />';

				$html_code .= add_FormElement_input('hidden', 'form', '', 'search');
				$html_code .= add_FormElement_input('hidden', 'module', '', 'lieu');
				$html_code .= add_FormElement_input('hidden', 'sub_module', '', '""');
				$html_code .= add_FormElement_input('hidden', 'action', '', 'list');

				$html_code .= '<input type="submit" value="Soumettre" />';

			$html_code .= '</p>';

			$html_code .= '<p>';
				$html_code .= '<a href="?module=lieu&action=add">Nouveau lieu</a>';
			$html_code .= '</p>';
		$html_code .= '</form>';

		$html_code .= '<p>';
			$alpha_letter = array();

			foreach ($result as $row) {
				if (!in_array(strtoupper(substr($row['nom'], 0, 1)), $alpha_letter)) {
					$alpha_letter[] = strtoupper(substr($row['nom'], 0, 1));
				}
			}

			foreach ($alpha_letter as $letter) {
				$html_code .= '<a href="#' . $letter . '">' . $letter . '</a> | ';
			}

		$html_code .= '</p>';

		$html_code .= '<table id="list_all_lieu">';
		$html_code .= '<thead>';
			$html_code .= '<tr>';
				$html_code .= '<th>Catégorie</th>';
				$html_code .= '<th>Nom</th>';
				$html_code .= '<th>Adresse</th>';
				$html_code .= '<th>Ville</th>';
				$html_code .= '<th>Téléphone fixe</th>';
				$html_code .= '<th>Editer</th>';
			$html_code .= '</tr>';
		$html_code .= '</thead>';

		$html_code .= '<tbody>';

			$last_letter = '';

			foreach ($result as $row) {

				//Rajoute une ligne HEAD avec la première lettre du nom pour les regroupement
				if (strtoupper($last_letter) <> strtoupper(substr($row['nom'], 0, 1))) {
					//$html_code .= '</tbody>';

					$last_letter = strtoupper(substr($row['nom'], 0, 1));

					//$html_code .= '<thead>';
						$html_code .= '<tr>';
							$html_code .= '<th>';
								$html_code .= '<a name="' . $last_letter . '"></a>' . $last_letter;
							$html_code .= '</th>';
						$html_code .= '</tr>';
					//$html_code .= '</thead>';

					//$html_code .= '<tbody>';
				}

				$html_code .= '<tr>';
					$html_code .= '<td>' . $row['categorie'] .'</td>';
					$html_code .= '<td><a href="?module=lieu&id=' . $row['id'] . '&action=view">' . $row['nom'] .'</a></td>';
					$html_code .= '<td>' . $row['adresse'] .'</td>';
					//$html_code .= '<td>' . $row['adresse_complement'] .'</td>';
					//$html_code .= '<td>' . $row['npa'] .'</td>';
					$html_code .= '<td>' . $row['ville'] .'</td>';
					$html_code .= '<td>' . format_tel($row['tel_fixe']) .'</td>';
					//$html_code .= '<td>' . format_tel($row['tel_fax']) .'</td>';
					//$html_code .= '<td>' . format_tel($row['tel_mobile']) .'</td>';

					$html_code .= '<td><a href="?module=lieu&amp;id=' . $row['id'] .'&amp;action=edit">Editer</a></td>';
				$html_code .= '</tr>';
			}

		$html_code .= '</tbody>';
	$html_code .= '</table>';

	$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));
	return $html_code;

	} //class.Lieu.form.list


	private static function form_view($action, $data_to_display='') {

		$html_code = '';

		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}

		$html_code .= '<p>';
			$html_code .= '<a href="?module=lieu&amp;action=edit&amp;id=' . $data_to_display['id']['value'] . '">Editer le lieu</a>';
		$html_code .= '</p>';

		$html_code .= '<p id="adresse">';
			$html_code .= $data_to_display['nom']['value'] . '<br />';

			if ($data_to_display['adresse']['value'] != '') {
				$html_code .= $data_to_display['adresse']['value'] . '<br />';
			}

			if ($data_to_display['adresse_complement']['value'] != '') {
				$html_code .= $data_to_display['adresse_complement']['value'] . '<br />';
			}

			if ($data_to_display['npa']['value'] != '') {
				$html_code .= $data_to_display['npa']['value'] . ' ';
			}

			if ($data_to_display['ville']['value'] != '') {
				$html_code .= $data_to_display['ville']['value'] . '<br />';
			}
		$html_code .= '</p>';

			$tmp_lieu = new Lieu($data_to_display['id']['value']);

			$tmp_lieu_array_telephone = $tmp_lieu->get_telephone();

		$html_code .= '<p id="telephone">';

			foreach ($tmp_lieu_array_telephone as $index => $row) {
				$html_code .= str_replace('tel_', '', $index) . ' : ' . format_tel($row) . '<br />';
			}

		$html_code .= '</p>';

		$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));
		return $html_code;
	} //class.Lieu.form.view

} // class.Lieu




?>
