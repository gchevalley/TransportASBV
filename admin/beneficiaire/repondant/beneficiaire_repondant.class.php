<?php

require_once( str_replace ( '\\', '/', dirname(dirname(dirname(__FILE__)))) . '/class.declaration.php' );
$load_needed_class_and_interface = load_class_and_interface(array('Contact', 'Benevole', 'Beneficiaire', 'Repondant_Categorie'));

class Repondant implements Contact {
	private $id = 0;
	private $id_categorie = 0;
	private $ref_external = 0;
	private $ref_external_object;
	private $id_beneficiaire = 0;
	private $beneficiaire = array(); // array d'object beneficiaire
	private $lien_beneficiaire = '';
	private $nom = '';
	private $prenom = '';
	private $tel_fixe = '';
	private $tel_mobile = '';

	private $array_adresse = array();
	private $adresse = '';
	private $adresse_complement = '';
	private $npa = '';
	private $ville = '';
	private $array_telephone = array();


	function __construct($id_repondant, $id_beneficiaire=0, $lien_beneficiaire='', $id_categorie=1, $ref_external=NULL, $nom='', $prenom='', $tel_fixe='', $tel_mobile='', $adresse='', $adresse_complement='', $npa='', $ville='') {

		if ( is_numeric($id_repondant) && Repondant::id_exists($id_repondant)) {

			$this->id = $id_repondant;
			$this->mountAttributsFromDB();

		} else { //cr�ation du nouveau lien entre le beneficiaire et la personne de ref

		if (is_numeric($id_beneficiaire) && Beneficiaire::id_exists($id_beneficiaire)) {

				if (Benevole::id_exists($_SESSION['benevole']['id'])) {
					$tmp_benevole = new Benevole($_SESSION['benevole']['id']);

					//if ($tmp_benevole->checkIsSuperAdmin()) {
						$this->addEntryDB($id_beneficiaire, $lien_beneficiaire, $id_categorie, $ref_external, $nom, $prenom, $tel_fixe, $tel_mobile, $adresse, $adresse_complement, $npa, $ville);
					//} else {
						/*
						if (Filiale::id_exists($_SESSION['filiale']['id'])) {
							if ($tmp_benevole->checkIsPermanencier($_SESSION['filiale']['id']) || $tmp_benevole->checkIsAdminOfFiliale($_SESSION['filiale']['id'])) {
								$this->addEntryDB($id_beneficiaire, $lien_beneficiaire, $nom, $prenom, $tel_fixe, $tel_mobile, $adresse, $adresse_complement, $npa, $ville);
							}
						}
						*/
					//}
				}
			}
		}
	} // class.Repondant.func.__construct($id_beneficiaire)


	private function mountAttributsFromDB() {

		//charge les donn�es direct depuis la DB
		global $dbh;

		//mount la totalit� des donn�es
		$sql = "SELECT * FROM repondant WHERE id=" .$this->id;

		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		//s'assurer qu'un r�sultat est retourn� bien alloue les donn�es aux attributs de l'object
		$this->id_beneficiaire = $result['id_beneficiaire'];
		$this->id_categorie = $result['id_categorie'];
		$this->ref_external = $result['ref_external'];

			$tmp_repondant_categorie = new Repondant_Categorie($this->id_categorie);

			if ($tmp_repondant_categorie->is_auto_mount()) {
				$categorie_nom = stripAccents(strtolower($tmp_repondant_categorie->get_nom()));

				switch ($categorie_nom) {
					case 'benevole':
						$load_needed_class_and_interface = load_class_and_interface(array('Benevole'));
						$this->ref_external_object = new Benevole($this->ref_external);
						break;
					case 'beneficiaire':
						$load_needed_class_and_interface = load_class_and_interface(array('Beneficiaire'));
						$this->ref_external_object = new Beneficiaire($this->ref_external);
						break;
					case 'lieu':
						$load_needed_class_and_interface = load_class_and_interface(array('Lieu'));
						$this->ref_external_object = new Lieu($this->ref_external);
						break;
				}
			}


		$this->lien_beneficiaire = $result['lien_beneficiaire'];
		$this->nom = $result['nom'];
		$this->prenom = $result['prenom'];
		$this->adresse = $result['adresse'];
		$this->adresse_complement = $result['adresse_complement'];
		$this->npa = $result['npa'];
		$this->ville = $result['ville'];
		$this->tel_fixe = $result['tel_fixe'];
		$this->tel_mobile = $result['tel_mobile'];


	} // class.Repondant.func.mountAttributsFromDB


	private function addEntryDB($id_beneficiaire, $lien_beneficiaire='', $id_categorie, $ref_external, $nom='', $prenom='', $tel_fixe='', $tel_mobile='', $adresse='', $adresse_complement='', $npa='', $ville='') {


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

		if (!Beneficiaire::id_exists($id_beneficiaire)) {
			die();
		}

		if (!is_numeric($id_categorie)) {
			die();
		}

		if (!is_null($ref_external)) {
			if (!is_numeric($ref_external)) {
				die();
			}
		}

		global $dbh;

		//processing de nettoyage des args de la fonction
		$lien_beneficiaire = $dbh->quote($lien_beneficiaire);
		$nom = $dbh->quote($nom);
		$prenom = $dbh->quote($prenom);
		$tel_fixe = $dbh->quote($tel_fixe);
		$tel_mobile = $dbh->quote($tel_mobile);
		$adresse = $dbh->quote($adresse);
		$adresse_complement = $dbh->quote($adresse_complement);
		$npa = $dbh->quote($npa);
		$ville = $dbh->quote($ville);

		if (is_numeric($ref_external) && $ref_external != '') {

		} else {
			$ref_external = '';
			$ref_external = $dbh->quote($ref_external);
		}


		$today_date = $dbh->quote(date('Y-m-d'));
		$today_time = $dbh->quote(date('H:i:s'));


		//cr�ation de la nouvelle entit� dans la db
		$sql = "INSERT INTO repondant (id_beneficiaire, lien_beneficiaire, id_categorie, ref_external, nom, prenom, tel_fixe, tel_mobile, adresse, adresse_complement, npa, ville, insert_date, insert_time, insert_benevole_user, last_update_date, last_update_time, last_update_benevole_user) ";
		$sql .= "VALUES ($id_beneficiaire, $lien_beneficiaire, $id_categorie, $ref_external, $nom, $prenom, $tel_fixe, $tel_mobile, $adresse, $adresse_complement, $npa, $ville, $today_date, $today_time, ". $tmp_benevole->get_id() . ", $today_date, $today_time, " . $tmp_benevole->get_id() . ")";

		$statut_query = $dbh->exec($sql);

		//mount l'object
		$this->id = $dbh->lastInsertId();
		$this->mountAttributsFromDB();

	} // class.Repondant.func.addEntryDB



	public function editerAttributs($attr, $new_value) { //2 matrix ou 2 valeurs

		if (!is_numeric($_SESSION['benevole']['id']) || !Benevole::id_exists($_SESSION['benevole']['id'])) {
			die();
		}

		global $dbh;

		$sql = "UPDATE repondant ";

		if (is_array($attr) && is_array($new_value)) { //2 tableaux receptionn�s
			$nbre_attribut = count($attr);
			$nbre_new_value = count($new_value);

			if ($nbre_attribut != $nbre_new_value) {
				return FALSE;
			}

			$sql .= "SET ";

			foreach ($attr as $index=>$attribut_to_edit)  {
				if (is_numeric($new_value[$index])) {
					$n_value = $new_value[$index];
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

		} elseif (!is_array($attr) && !is_array($new_value)) { //1 seule valeur $attr=>$new_value receptionn�e
			if (!is_numeric($new_value)) {
				$new_value = $dbh->quote($new_value);
			}

			$sql .= "SET $attr=$new_value ";

		} else {
			die();
		}

		$sql .= "WHERE id=" . $this->id;
		$statut_query = $dbh->exec($sql);

		//recharge avec les nouvelles donn�es
		$this->mountAttributsFromDB();
	} // class.Repondant.func.editerAttributs


	public function get_id() {
		return $this->id;
	} // class.Repondant.func.get_id

	public function get_lien_beneficiaire() {
		return $this->lien_beneficiaire;
	}

	public function get_ref_external() {
		return $this->ref_external;
	}

	private function mountAdresseArray() {

		$this->array_adresse['lien_beneficiaire'] = $this->lien_beneficiaire;

		$this->array_adresse['nom_complet']['prenom'] = $this->prenom;
		$this->array_adresse['nom_complet']['nom'] = $this->nom;

		$this->array_adresse['adresse'] = $this->adresse;

		if ($this->adresse_complement != '') {
			$this->array_adresse['adresse_complement']= $this->adresse_complement;
		}

		$this->array_adresse['npa'] = $this->npa;
		$this->array_adresse['ville'] = $this->ville;

		//$this->array_adresse['pays'] = 'Suisse';
	} // class.Repondant.func.mountAdresseArray

	public function get_adresse() {
		$this->mountAdresseArray();
		return $this->array_adresse;
	} // class.Repondant.func.get_adresse


	private function group_nom() {

		if ($this->prenom != '') {
			$this->nom_complet['prenom'] = $this->prenom;
		}

		if ($this->nom != '') {
			$this->nom_complet['nom'] = $this->nom;
		}

	} // class.Repondant.func.group_nom

	public function get_nom_complet() {
		$this->group_nom();
		return $this->nom_complet;
	} // class.Repondant.func.get_nom_complet

	private function group_telephone_into_array() {
		if ($this->tel_fixe != '') {
			$this->array_telephone['tel_fixe'] = $this->tel_fixe;
		}

		if ($this->tel_mobile != '') {
			$this->array_telephone['tel_mobile'] = $this->tel_mobile;
		}
	} // class.Repondant.func.group_telephone_into_array

	public function get_telephone() {
		$this->group_telephone_into_array();
		return $this->array_telephone;
	} // class.Repondant.func.get_telephone


	public function has_tel_fixe() {
		$this->group_telephone_into_array();

		if (isset($this->array_telephone['tel_fixe'])) {
			return $this->array_telephone['tel_fixe'];
		} else {
			return FALSE;
		}
	}


	public function has_tel_mobile() {
		$this->group_telephone_into_array();

		if (isset($this->array_telephone['tel_mobile'])) {
			return $this->array_telephone['tel_mobile'];
		} else {
			return FALSE;
		}
	}



	public function get_id_categorie() {
		return $this->id_categorie;
	}


	public static function id_exists($id_to_check) {
		if (checkID($id_to_check)) {
			global $dbh;
			$sql = "SELECT * FROM repondant WHERE id=" .$id_to_check;
			$sth = $dbh->query($sql);
			$result = $sth->fetch(PDO::FETCH_ASSOC);

			if ($result =! false) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			//bad id type
			return FALSE;
		}
	} //class.Repondant.func.id_exists

} // class.Repondant

?>
