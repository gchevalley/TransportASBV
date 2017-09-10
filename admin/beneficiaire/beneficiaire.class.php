<?php

/**
 * chargement du fichier qui contient la liste des noms des classes
 * ainsi que leur chemin d'accès
 *
 * il est ensuite possible d'utiliser la fonction load_class_and_interface
 * à laquelle il suffit de passer un array avec le nom des classes que
 * l'on souhaite utiliser
 *
 * str_replace est utilisé pour avoir des chemins d'accès UNIX style
 */
require_once( str_replace ( '\\', '/', dirname(dirname(__FILE__))) . '/class.declaration.php' );

/**
 * Pour pouvoir utilser cette fonction, il est impératif d'avoir chargé
 * précédemment le fichier /admin/class.declaration.php
 * @filesource /admin/class.declaration.php
 */
load_class_and_interface(array('Contact', 'Benevole'));

/**
 * classe des personnes transportées
 *
 * les bénéficiaires ne sont pas rattachés à une filiale
 *
 * le fonctionnement des classes de ce projet est toujours le même :
 * soit un seul argument (id) est passé au constructeur et
 * s'il est valide, l'objet est monté à l'aide des données présente dans
 * la base de données
 * ou alors la totalité des informations est passée à la fonction,
 * l'entrée est créee dans la base puis l'objet est monté
 *
 *
 * elle implemente l'interface Contact qui impose :
 * accessor : id / array(titre, nom, prenom) / array(adresse, adresse_complement, npa, ville) / array(tel_fixe, tel_mobile)
 *
 * @author Gregory Chevalley <gregory.chevalley[at]gmail.com>
 *
 */
class Beneficiaire implements Contact {

	private $id = 0;
	private $titre = '';
	private $nom_complet = array();
	private $nom = '';
	private $prenom = '';
	private $array_adresse = array();
	private $array_autre_adresse_facturation = array();
	private $adresse = '';
	private $adresse_complement = '';
	private $npa = '';
	private $ville = '';
	private $pays = '';
	private $array_telephone = array();
	private $tel_fixe = '';
	private $tel_mobile = '';
	private $info_diverses = '';

	/**
	 * Information non exploitée car il faudrait entrer et entretenir
	 * le nombre de places des véhicules des transporteurs.
	 *
	 * Il est préférable d'utiliser le champ info_diverses car cette
	 * information est beaucoup plus parlante pour un humain
	 *
	 * @deprecated
	 */
	private $toujours_2 = 0;


	private $autre_adresse_facturation = 0;
	private $facturation_nom = '';
	private $facturation_prenom = '';
	private $facturation_adresse = '';
	private $facturation_adresse_complement = '';
	private $facturation_npa = '';
	private $facturation_ville = '';
	private $facturation_pays = '';


	/**
	 * UN SEUL répondant est possible bien qu'il est ici
	 * stocké dans une matrice
	 */
	private $array_repondant = array();

	/**
	 *
	 * non utilisé pour des raisons de performance
	 * vu le nombre fois que des objets Beneficiaire sont instanciés
	 * sans que cette variable soit utilisée
	 * @deprecated
	 */
	private $array_transport = array();


	function __construct($id_beneficiaire, $titre='', $nom='', $prenom='', $adresse='', $adresse_complement='', $npa='', $ville='', $pays='', $tel_fixe='', $tel_mobile='', $info_diverses='', $toujours_2=0, $autre_adresse_facturation=0, $facturation_nom='', $facturation_prenom='', $facturation_adresse='', $facturation_adresse_complement='', $facturation_npa='', $facturation_ville='', $facturation_pays='') {

		if (Beneficiaire::id_exists($id_beneficiaire)) {

			$this->id = $id_beneficiaire;
			$this->mountAttributsFromDB();

		} else { //creation de la nouvelle entite

			if (is_numeric($id_beneficiaire)) {
				$this->addEntryDB($titre, $nom, $prenom, $adresse, $adresse_complement, $npa, $ville, $pays, $tel_fixe, $tel_mobile, $info_diverses, $toujours_2, $autre_adresse_facturation, $facturation_nom, $facturation_prenom, $facturation_adresse, $facturation_adresse_complement, $facturation_npa, $facturation_ville, $facturation_pays);
			}
		}
	} // class.Beneficiaire.func.__construct

	/**
	 *
	 * Fonction qui s'occupe de synchroniser les variables d'un objet
	 * avec les données présentes dans la base de données
	 *
	 * valeurs base de données -> objet
	 *
	 * les valeurs de la base font fois et écrase celle de l'objet
	 */
	private function mountAttributsFromDB() {

		/**
		 * variable pointant sur l'objet PDO (PHP DATA OBJECT)
		 * effectuant le pont avec la base de données
		 * @filesource /config/connect.db.inc.php
		 */
		global $dbh;

		//mount la totalite des champs pour le beneficiaire concerne
		$sql = "SELECT * FROM beneficiaire WHERE id=" .$this->id;

		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		/**
		 *
		 * Remarque : il est surement possible de procéder avec le
		 * parcours d'une boucle mais je ne suis pas parevenu à
		 * transformer les variables de classe en variable
		 * (argument après $this->)
		 */
		$this->titre = $result['titre'];
		$this->nom = $result['nom'];
		$this->prenom = $result['prenom'];
		$this->adresse = $result['adresse'];
		$this->adresse_complement = $result['adresse_complement'];
		$this->npa = $result['npa'];
		$this->ville = $result['ville'];
		$this->pays = $result['pays'];
		$this->tel_fixe = $result['tel_fixe'];
		$this->tel_mobile = $result['tel_mobile'];
		$this->info_diverses = $result['info_diverses'];
		$this->toujours_2 = $result['toujours_2'];

		$this->autre_adresse_facturation = $result['autre_adresse_facturation'];
		$this->facturation_nom = $result['facturation_nom'];
		$this->facturation_prenom = $result['facturation_prenom'];
		$this->facturation_adresse = $result['facturation_adresse'];
		$this->facturation_adresse_complement = $result['facturation_adresse_complement'];
		$this->facturation_npa = $result['facturation_npa'];
		$this->facturation_ville = $result['facturation_ville'];
		$this->facturation_pays = $result['facturation_pays'];

		/**
		 * il est bien sur possible de transformer cette fonction
		 * private->public et de l'excecuter uniquement si nécessaire
		 */
		$this->mountRepondant();

	} // class.Beneficiaire.func.mountAttributsFromDB


	private function mountRepondant() {
		load_class_and_interface(array('Repondant'));
		$this->array_repondant = array();

		global $dbh;
		$sql = "SELECT id FROM repondant WHERE id_beneficiaire=" . $this->id;
		$sth = $dbh->query($sql);

		$result_personne_de_reference = $sth->fetchAll(PDO::FETCH_ASSOC);

		//si array de retour non vide :
		if (count($result_personne_de_reference) > 0) {
			foreach ($result_personne_de_reference as $row) {
				$this->array_repondant[] = new Repondant($row['id']);
			}
		}
	} // class.Beneficiaire.func.mountPersonneDeReferenceFromDB


	private function addEntryDB($titre, $nom, $prenom, $adresse, $adresse_complement, $npa, $ville, $pays, $tel_fixe, $tel_mobile, $info_diverses, $toujours_2, $autre_adresse_facturation, $facturation_nom, $facturation_prenom, $facturation_adresse, $facturation_adresse_complement, $facturation_npa, $facturation_ville, $facturation_pays) {
		/**
		 * s'assure que l'authentification c'est bien déroulée
		 * et que l'utilisateur bénéficie des droits nécessaire
		 * pour cette opération
		 *
		 * A améliorer : en cas de problème c'est un die() qui interrompt
		 * tous les scripts en cours
		 */
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


		/**
		 * stock les coordonnées dans la table geocode
		 * cette information n'est pas exploitée car s'il faut afficher
		 * moins de 20 points sur une carte google maps, on peut directement
		 * passer une adresse en argument de la fonction javascript
		 *
		 */
		if (checkInternetConnection('maps.google.com')) {
			$load_needed_class_and_interface = load_class_and_interface(array('Geocode'));
			$array_coor = Geocode::find_combination($adresse, $npa, $ville, $pays, TRUE);
		}


		/**
		 * variable pointant sur l'objet PDO (PHP DATA OBJECT)
		 * effectuant le pont avec la base de données
		 * @filesource /config/connect.db.inc.php
		 */
		global $dbh;

		/**
		 * quote s'assure que l'argument est bien considéré comme une
		 * chaine de caractère pour éviter des sql injections
		 */
		$titre = $dbh->quote($titre);
		$nom = $dbh->quote($nom);
		$prenom = $dbh->quote($prenom);
		$adresse = $dbh->quote($adresse);
		$adresse_complement = $dbh->quote($adresse_complement);
		$npa = $dbh->quote($npa);
		$ville = $dbh->quote($ville);
		$pays = $dbh->quote($pays);
		$tel_fixe = $dbh->quote($tel_fixe);
		$tel_mobile = $dbh->quote($tel_mobile);
		$info_diverses = $dbh->quote($info_diverses);


		if (!is_numeric($toujours_2)) {
			$toujours_2 = 0;
		}

		if (!is_numeric($autre_adresse_facturation)) {
			$autre_adresse_facturation = 0;
		}

		$facturation_nom = $dbh->quote($facturation_nom);
		$facturation_prenom = $dbh->quote($facturation_prenom);
		$facturation_adresse = $dbh->quote($facturation_adresse);
		$facturation_adresse_complement = $dbh->quote($facturation_adresse_complement);
		$facturation_npa = $dbh->quote($facturation_npa);
		$facturation_ville = $dbh->quote($facturation_ville);
		$facturation_pays = $dbh->quote($facturation_pays);



		$today_date = $dbh->quote(date('Y-m-d'));
		$today_time = $dbh->quote(date('H:i:s'));


		//creation de la nouvelle entite dans la db
		$sql = "INSERT INTO beneficiaire (titre, nom, prenom, adresse, adresse_complement, npa, ville, pays, tel_fixe, tel_mobile, info_diverses, toujours_2, autre_adresse_facturation, facturation_nom, facturation_prenom, facturation_adresse, facturation_adresse_complement, facturation_npa, facturation_ville, facturation_pays, insert_date, insert_time, insert_benevole_user, last_update_date, last_update_time, last_update_benevole_user) ";
		$sql .= "VALUES ($titre, $nom, $prenom, $adresse, $adresse_complement, $npa, $ville, $pays, $tel_fixe, $tel_mobile, $info_diverses, $toujours_2, $autre_adresse_facturation, $facturation_nom, $facturation_prenom, $facturation_adresse, $facturation_adresse_complement, $facturation_npa, $facturation_ville, $facturation_pays, $today_date, $today_time, " . $tmp_benevole->get_id() . ", $today_date, $today_time, " . $tmp_benevole->get_id() .")";

		$statut_query = $dbh->exec($sql);


		$this->id = $dbh->lastInsertId();
		$this->mountAttributsFromDB();

		/**
		 * afin que la liste de points de départ et de destination
		 * s'étouffe automatiquement chaque nouvel entrée d'objet
		 * implémentant l'interface Contact (donc contenant une adresse)
		 * on crée systématiquement des points basés sur les villes
		 *
		 * La fontion statique Lieu::ajouterVille s'assure que l'entrée
		 * n'existe pas déjà, on peut donc sans autre lui faire appel sans
		 * création de doublon
		 */
		if ($this->npa != '' && $this->ville != '') {
			load_class_and_interface(array('Lieu'));
			Lieu::ajouterVille($this->ville, $this->npa, $this->pays);
		}

	} // class.Beneficiaire.func._addEntryDB


	/**
	 * un seul repondant à la fois, le précédent, s'il existe est détruit
	 * sans sauvegarde
	 *
	 * cette restriction est liée au code HTML qui supporte
	 * l'affichage d'un seul répondant uniquement
	 *
	 */
	public function ajouterRepondant($lien_beneficiaire, $id_categorie, $ref_external, $nom, $prenom, $tel_fixe, $tel_mobile, $adresse, $adresse_complement, $npa, $ville) {
		$load_needed_class_and_interface = load_class_and_interface(array('Repondant', 'Repondant_Categorie'));

		if (is_string($id_categorie) && !is_numeric($id_categorie)) {
			$id_categorie = Repondant_Categorie::get_id_from_nom($id_categorie);
		} elseif ($id_categorie instanceof Repondant_Categorie) {
			$id_categorie = $id_categorie->get_id();
		}

		global $dbh;
		$sql = "DELETE FROM repondant WHERE id_beneficiaire=" . $this->get_id();
		$query_status = $dbh->exec($sql);

		// creation du nouveau lien
		$tmp_repondant = new Repondant(0, $this->get_id(), $lien_beneficiaire, $id_categorie, $ref_external, $nom, $prenom, $tel_fixe, $tel_mobile, $adresse, $adresse_complement, $npa, $ville);
		$this->mountRepondant();
	}

	/**
	 *
	 * Fonction de mise à jour de la base de données
	 *
	 * soit on envoie le champs à editer avec sa nouvelle valeur
	 * ou alors
	 * un vecteur de champs avec un vecteur de nouvelles valeurs
	 *
	 * Une fois les valeurs modifiées, on relance la fonction
	 * de classe mountAttributsFromDB pour mettre à jours également
	 * les variables de classe de l'objet
	 *
	 *
	 * @param array/string $attr
	 * @param array ou valeur $new_value
	 */
	public function editerAttributs($attr, $new_value) {

		if (!is_numeric($_SESSION['benevole']['id']) || !Benevole::id_exists($_SESSION['benevole']['id'])) {
			die();
		}

		global $dbh;

		$sql = "UPDATE beneficiaire ";

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
	} // class.Beneficiaire.func.editerAttributs


	public function get_id() {
		return $this->id;
	}

/**
 *  Add this function to decode the Info diverses ( info related to passenger )
 *  M. Thevoz 20 Janvier 2012
*/

	public function get_info_div() {
		return $this->info_diverses;
	}


	/**
	 *
	 * Aucune vérification des données dans la base car la fonction
	 * MountRepondant est appelée à chaque construction d'objet
	 * il suffit donc de regarder si la variable de classe se
	 * rapportant au repondant est non nulle
	 */
	public function has_repondant() {
		if (isset($this->array_repondant[0]) && $this->array_repondant[0] instanceof Repondant) {
			//return TRUE;
			return $this->array_repondant[0]->get_id();
		} else {
			return FALSE;
		}
	}


	public function has_autre_adresse_facturation() {
		if ($this->autre_adresse_facturation == 1) {
			return TRUE;
		} else {
			return FALSE;
		}
	}


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


	/**
	 * peut être déprécié car la fonction has_repondant retourne
	 * l'id du répondant si un répondant est présent
	 * @
	 */
	public function get_id_repond() {
		return $this->array_repondant[0]->get_id();
	}


	/**
	 *
	 * transforme les différents éléments de l'adresse s'ils sont présents
	 * en un tableau
	 *
	 * Utilisé pour les points de départ/arrivée
	 *
	 * la clé 'type' est importante car elle permet de réperé si le
	 * point départ/arrivée et le domicile d'un passager
	 *
	 * REMARQUE IMPORTANTE :
	 * si le point de départ/arrivée est le domicile, il ne suffit pas
	 * sauvegarder 'type' & 'id' car la personne peut déménager.
	 */
	private function mountAdresseArray() {
		$this->array_adresse['type'] = 'beneficiaire';
		$this->array_adresse['id'] = $this->id;

		$this->array_adresse['nom_complet'] = $this->get_nom_complet();
		$this->array_adresse['adresse'] = $this->adresse;

		if ($this->adresse_complement != '') {
			$this->array_adresse['adresse_complement']= $this->adresse_complement;
		}

		$this->array_adresse['npa'] = $this->npa;
		$this->array_adresse['ville'] = $this->ville;
		$this->array_adresse['pays'] = $this->pays;

	} // class.Beneficiaire.func.mountAdresseArray


	private function mountAdresseFacturationArray() {

		if ($this->has_autre_adresse_facturation()) {
			$this->array_autre_adresse_facturation['type'] = 'beneficiaire';
			$this->array_autre_adresse_facturation['id'] = $this->id;

			$this->array_autre_adresse_facturation['nom_complet']['nom'] = $this->facturation_nom;
			$this->array_autre_adresse_facturation['nom_complet']['prenom'] = $this->facturation_prenom;

			$this->array_autre_adresse_facturation['adresse'] = $this->facturation_adresse;

			if ($this->facturation_adresse_complement != '') {
				$this->array_autre_adresse_facturation['adresse_complement'] = $this->facturation_adresse_complement;
			}

			$this->array_autre_adresse_facturation['npa'] = $this->facturation_npa;
			$this->array_autre_adresse_facturation['ville'] = $this->facturation_ville;
			$this->array_autre_adresse_facturation['pays'] = $this->facturation_pays;

			return $this->array_autre_adresse_facturation;
		} else {
			$this->mountAdresseArray();
			return $this->array_adresse;
		}

	} // class.Beneficiaire.func.mountAdresseFacturationArray

	public function get_adresse() {
		$this->mountAdresseArray();
		return $this->array_adresse;
	} // class.Beneficiaire.func.get_adresse


	public function get_adresse_facturation() {
		return $this->mountAdresseFacturationArray();
	} // class.Beneficiaire.func.get_adresse_facturation


	private function group_nom() {

		$this->nom_complet['titre'] = $this->titre;

		if ($this->prenom != '') {
			$this->nom_complet['prenom'] = $this->prenom;
		}

		$this->nom_complet['nom'] = $this->nom;

	} // class.Beneficiaire.func.group_nom


	public function get_nom_complet() {
		$this->group_nom();
		return $this->nom_complet;
	} // class.Beneficiaire.func.get_nom_complet


	private function group_telephone_into_array() {
		if ($this->tel_fixe != '') {
			$this->array_telephone['tel_fixe'] = $this->tel_fixe;
		}

		if ($this->tel_mobile != '') {
			$this->array_telephone['tel_mobile'] = $this->tel_mobile;
		}
	} // class.Beneficiaire.func.group_telephone_into_array


	public function get_telephone() {
		$this->group_telephone_into_array();
		return $this->array_telephone;
	} // class.Beneficiare.func.get_telephone


	public function get_last_transport() {
		global $dbh;

		$sql = "SELECT transport.id ";
		$sql .= " FROM transport ";
		$sql .= " WHERE transport.id_beneficiaire=" . $this->get_id();
		$sql .= " ORDER BY transport.date_transport DESC, transport.heure_debut DESC ";
		$sql .= " LIMIT 1";

		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		if ($result) {
			load_class_and_interface(array('Transport'));
			$tmp_transport = new Transport($result['id']);
			return $tmp_transport;
		} else {
			return FALSE;
		}
	}


	public function get_last_insert_transport() {
		global $dbh;

		$sql = "SELECT transport.id ";
		$sql .= " FROM transport ";
		$sql .= " WHERE transport.id_beneficiaire=" . $this->get_id();
		$sql .= " ORDER BY transport.insert_date DESC, transport.insert_time DESC";
		$sql .= " LIMIT 1";

		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		if ($result) {
			load_class_and_interface(array('Transport'));
			$tmp_transport = new Transport($result['id']);
			return $tmp_transport;
		} else {
			return FALSE;
		}
	}


	public static function id_exists($id_to_check) {
		if (checkID($id_to_check)) {
			global $dbh;
			$sql = "SELECT * FROM beneficiaire WHERE id=" .$id_to_check;
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
	} //class.Beneficiaire.func.id_exists

	/**
	 *
	 * Fonction qui retourne les valeurs de l'objet sous forme d'
	 * array [key => value] qui va permettre d'etre affichées dans
	 * les différents formulaires web.
	 *
	 * Ce tableau est généralement appelé data_to_display dans les fonctions
	 * static form qui retourne le code HTML
	 *
	 */
	private function return_pair_key_value() {

		$tmp_array['id']['value']= $this->id;
		$tmp_array['titre']['value']= $this->titre;
		$tmp_array['nom']['value']= $this->nom;
		$tmp_array['prenom']['value']= $this->prenom;
		$tmp_array['adresse']['value']= $this->adresse;
		$tmp_array['adresse_complement']['value']= $this->adresse_complement;
		$tmp_array['npa']['value']= $this->npa;
		$tmp_array['ville']['value']= $this->ville;
		$tmp_array['pays']['value']= $this->pays;
		$tmp_array['tel_fixe']['value']= $this->tel_fixe;
		$tmp_array['tel_mobile']['value']= $this->tel_mobile;
		$tmp_array['info_diverses']['value']= $this->info_diverses;
		$tmp_array['toujours_2']['value']= $this->toujours_2;

		$tmp_array['autre_adresse_facturation']['value']= $this->autre_adresse_facturation;
		$tmp_array['facturation_nom']['value']= $this->facturation_nom;
		$tmp_array['facturation_prenom']['value']= $this->facturation_prenom;
		$tmp_array['facturation_adresse']['value']= $this->facturation_adresse;
		$tmp_array['facturation_adresse_complement']['value']= $this->facturation_adresse_complement;
		$tmp_array['facturation_npa']['value']= $this->facturation_npa;
		$tmp_array['facturation_ville']['value']= $this->facturation_ville;
		$tmp_array['facturation_pays']['value']= $this->facturation_pays;


		//repondant ne supporte que 1 seul pour l'instant
		foreach ($this->array_repondant as $tmp_repondant) {
			$tmp_array['repondant']['id']['value'] = $tmp_repondant->get_id();
			//$tmp_array['repondant']['id_beneficiaire']['value'] = $this->get_id();
			$tmp_array['repondant']['lien_beneficiaire']['value'] = $tmp_repondant->get_lien_beneficiaire();
			$tmp_array['repondant']['id_categorie']['value'] = $tmp_repondant->get_id_categorie();
			$tmp_array['repondant']['ref_external']['value'] = $tmp_repondant->get_ref_external();

			$load_needed_class_and_interface = load_class_and_interface(array('Repondant_Categorie'));
			$tmp_repondant_categorie = new Repondant_Categorie($tmp_array['repondant']['id_categorie']);
			//si la categorie et de type auto mount, prendre la reference de l'object
			if ($tmp_repondant_categorie->is_auto_mount()) {
				$tmp_array['repondant']['ref_external']['value'] = $tmp_repondant->get_ref_external();
			} else {
				$tmp_repondant_nom_complet = $tmp_repondant->get_nom_complet();
				if ($tmp_repondant_nom_complet['nom']['value'] != '') {
					$tmp_array['repondant']['nom']['value'] = $tmp_repondant_nom_complet['nom'];
				}

				if ($tmp_repondant_nom_complet['prenom']['value'] != '') {
					$tmp_array['repondant']['prenom']['value'] = $tmp_repondant_nom_complet['prenom'];
				}

				$tmp_repondant_adresse = $tmp_repondant->get_adresse();
				if ($tmp_repondant_adresse['adresse'] != '') {
					$tmp_array['repondant']['adresse']['value'] = $tmp_repondant_adresse['adresse'];
				}

				if ($tmp_repondant_adresse['adresse_complement'] != '') {
					$tmp_array['repondant']['adresse_complement']['value'] = $tmp_repondant_adresse['adresse_complement'];
				}

				if ($tmp_repondant_adresse['npa'] != '') {
					$tmp_array['repondant']['npa']['value'] = $tmp_repondant_adresse['npa'];
				}

				if ($tmp_repondant_adresse['ville'] != '') {
					$tmp_array['repondant']['ville']['value'] = $tmp_repondant_adresse['ville'];
				}

				$tmp_repondant_telephones = $tmp_repondant->get_telephone();
				if ($tmp_repondant_telephones['tel_fixe'] != '') {
					$tmp_array['repondant']['tel_fixe']['value'] = $tmp_repondant_telephones['tel_fixe'];
				}

				if ($tmp_repondant_telephones['tel_mobile'] != '') {
					$tmp_array['repondant']['tel_mobile']['value'] = $tmp_repondant_telephones['tel_mobile'];
				}
			}
		}



		return $tmp_array;
	} // class.Beneficiaire.func.return_pair_key_value


	/**
	 *
	 * Fonction static qui appelle les différentes méthodes private static
	 * qui retourne le code html de l'action demandée
	 *
	 * actions possibles :
	 * - add : retourne le form de base editable mais vide
	 * - edit : retourne le form de base editable avec les valeurs du passager choisi
	 * - view : descriptif figé de l'objet avec carte gmaps
	 * - list : retourne une page sous forme de tableau trié alphabetiquement
	 *
	 *
	 * @param string $action
	 * @param faculatif array/objet/id $data_to_display
	 *
	 *
	 * le second argument peut-être passer sous différentes formes :
	 * - id du bénéficiaire par ex. echo Beneficiaire::form('edit', 2)
	 * - un objet de type bénéficiaire : echo Beneficiaire::form('edit', $obj_beneficiaire)
	 * - un tableau obtenu par la fonction return_pair_key_value
	 *
	 */
	public static function form($action, $data_to_display='') {

		if (is_array($data_to_display)) {

		} elseif (is_numeric($data_to_display) && Beneficiaire::id_exists($data_to_display)) {
			//numero de beneficaire
			$tmp_beneficiaire = new Beneficiaire($data_to_display);
			unset($data_to_display);
			$data_to_display = $tmp_beneficiaire->return_pair_key_value();
		} elseif ($data_to_display instanceof Beneficiaire) {
			//convertir en un tableau data_to_display_habituel
			$data_to_display = $data_to_display->return_pair_key_value();
		} else {
			/**
			 * les fonctions private static qui retourne le code html
			 * s'attendent à trouver un tableau dans cette variable
			 * il est donc nécessaire d'en passer un même vide pour éviter
			 * des plantages du code
			 */
			$data_to_display = array();
		}

		switch ($action) {
			case "add":
				echo Beneficiaire::form_base($action, $data_to_display);
				break;
			case "view":
				//s'assure que le beneficiaire est connu sinon charge une listbox de selection
				if (isset($data_to_display['id']['value']) && Beneficiaire::id_exists($data_to_display['id']['value'])) {
					echo Beneficiaire::form_view($action, $data_to_display);
				} else {
					echo Beneficiaire::form_choose($action, $data_to_display);
				}
				break;
			case "edit":
				//s'assure que le beneficiaire est connu sinon charge une listbox de selection
				if (isset($data_to_display['id']['value']) && Beneficiaire::id_exists($data_to_display['id']['value'])) {
					echo Beneficiaire::form_base($action, $data_to_display);
				} else {
					echo Beneficiaire::form_choose($action, $data_to_display);
				}
				break;
			case "list":
				echo Beneficiaire::form_list($action);
				break;
			case "tarif":
				echo Beneficiaire::form_tarif($action, $data_to_display);
				break;
			case "ajax_get_beneficiaire_details":
				echo Beneficiaire::form_ajax_get_beneficiaire_details($action, $data_to_display);
				break;
			case "ajax_already_transport_same_date":
				echo Beneficiaire::form_ajax_already_transport_same_date($action, $data_to_display);
				break;
			case "ajax_already_transport_same_date_and_time":
				echo Beneficiaire::form_ajax_already_transport_same_date_and_time($action, $data_to_display);
				break;
			default:
				echo Beneficiaire::form_list($action);
				break;
		}

	} // class.Beneficiaire.func.form

	private static function form_base($action, $data_to_display='') {
		//retourne le code html du formulaire
		unset($_POST);
		$html_code = '';


		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}

		$action_title = '';
		if ($action == 'add') {
			$action_title = 'Ajouter';
		} elseif ($action == 'edit') {
			$action_title = 'Modifier';
		}

		if ($action_title != '') {
			$html_code .= '<h2>' . $action_title . ' un passager</h2>';
		}

		$html_code .= '<form class="disable_submit_form" id="beneficiaire_' . $action . '" action="" method="post">';
			$html_code .= '<fieldset id="beneficiaire_identite">';
				$html_code .= '<legend>Identité</legend>';

				$html_code .= '<p>';

					if (!isset($data_to_display['titre']['value'])) {
						$default_titre = 'Madame';
					} else {
						$default_titre = $data_to_display['titre']['value'];
					}

					$html_code .= add_FormElement_select('titre', 'required', array('Madame', 'Monsieur', 'Mademoiselle', 'Enfant'), $default_titre);

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

			$html_code .= '</fieldset>';

			$html_code .= '<fieldset id="beneficiaire_adresse">';
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

					if (!isset($data_to_display['pays']['value']) || $data_to_display['pays']['value'] == '') {
						$html_code .= add_FormElement_input('text', 'pays', array('input_pays', 'required'), 'Suisse');
					} else {
						$html_code .= add_FormElement_input('text', 'pays', array('input_pays', 'required'), $data_to_display['pays']['value']);
					}

				$html_code .= '</p>';


			$html_code .= '</fieldset>';


			// si autre adresse pour la facturation
			$html_code .= '<p>';
				$html_code .= '<label for="autre_adresse_facturation">Autre adresse pour la facturation</label>';
				if ( isset($data_to_display['autre_adresse_facturation']['value']) ) {
					$html_code .= add_FormElement_input('checkbox', 'autre_adresse_facturation', '',  $data_to_display['autre_adresse_facturation']['value']);
				} else {
					$html_code .= add_FormElement_input('checkbox', 'autre_adresse_facturation', '',  '');
				}
			$html_code .= '</p>';

			if ( isset($data_to_display['autre_adresse_facturation']['value']) ) {
				if ( $data_to_display['autre_adresse_facturation']['value'] == 1 || $data_to_display['autre_adresse_facturation']['value'] == TRUE) {
					$html_code .= '<div id="beneficiaire_adresse_facturation">';
				}
			} else {
				$html_code .= '<div class="hide" id="beneficiaire_adresse_facturation">';
			}


				$html_code .= '<fieldset id="beneficiaire_adresse_pour_facturation">';
					$html_code .= '<legend>Adresse facturation</legend>';

					$html_code .= '<p>';
						$html_code .= '<label for="facturation_nom">Nom</label>';
						if (isset($data_to_display['facturation_nom']['value'])) {
							$html_code .= add_FormElement_input('text', 'facturation_nom', array('input_nom', 'disableAutoComplete', 'required'), $data_to_display['facturation_nom']['value']);
						} else {
							$html_code .= add_FormElement_input('text', 'facturation_nom', array('input_nom', 'disableAutoComplete', 'required'), '');
						}
					$html_code .= '</p>';

					$html_code .= '<p>';
						$html_code .= '<label for="facturation_prenom">Prénom</label>';
						if ( isset($data_to_display['facturation_prenom']['value']) ) {
							$html_code .= add_FormElement_input('text', 'facturation_prenom', array('input_nom', 'disableAutoComplete'), $data_to_display['facturation_prenom']['value']);
						} else {
							$html_code .= add_FormElement_input('text', 'facturation_prenom', array('input_nom', 'disableAutoComplete'), '');
						}
					$html_code .= '</p>';

					$html_code .= '<p>';
						$html_code .= '<label for="facturation_adresse">Adresse</label>';
						if ( isset($data_to_display['facturation_adresse']['value']) ) {
							$html_code .= add_FormElement_input('text', 'facturation_adresse', array('input_adresse', 'disableAutoComplete', 'required'), $data_to_display['facturation_adresse']['value']);
						} else {
							$html_code .= add_FormElement_input('text', 'facturation_adresse', array('input_adresse', 'disableAutoComplete', 'required'), '');
						}
					$html_code .= '</p>';

					$html_code .= '<p>';
						$html_code .= '<label for="facturation_adresse_complement">Complément d\'adresse</label>';

						if ( isset($data_to_display['facturation_adresse_complement']['value']) ) {
							$html_code .= add_FormElement_input('text', 'facturation_adresse_complement', array('input_adresse', 'disableAutoComplete'), $data_to_display['facturation_adresse_complement']['value']);
						} else {
							$html_code .= add_FormElement_input('text', 'facturation_adresse_complement', array('input_adresse', 'disableAutoComplete'), '');
						}
					$html_code .= '</p>';

					$html_code .= '<p>';
						$html_code .= '<label for="facturation_npa">Code postal</label>';

						if ( isset($data_to_display['facturation_npa']['value']) ) {
							$html_code .= add_FormElement_input('text', 'facturation_npa', array('input_npa', 'disableAutoComplete', 'required'), $data_to_display['facturation_npa']['value']);
						} else {
							$html_code .= add_FormElement_input('text', 'facturation_npa', array('input_npa', 'disableAutoComplete', 'required'), '');
						}

						$html_code .= '<label for="facturation_ville">Ville</label>';
						if ( isset($data_to_display['facturation_npa']['value']) ) {
							$html_code .= add_FormElement_input('text', 'facturation_ville', array('input_ville', 'required'), $data_to_display['facturation_ville']['value']);
						} else {
							$html_code .= add_FormElement_input('text', 'facturation_ville', array('input_ville', 'required'), '');
						}

					$html_code .= '</p>';

					$html_code .= '<p>';
						$html_code .= '<label for="facturation_pays">Pays</label>';
						if ( isset($data_to_display['facturation_pays']['value']) ) {
							$html_code .= add_FormElement_input('text', 'facturation_pays', array('input_pays', 'required'), $data_to_display['facturation_pays']['value']);
						} else {
							$html_code .= add_FormElement_input('text', 'facturation_pays', array('input_pays', 'required'), '');
						}
					$html_code .= '</p>';


				$html_code .= '</fieldset>';

			$html_code .= '</div>';



			$html_code .= '<fieldset id="beneficiaire_telephone">';
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
					$html_code .= '<label for="tel_mobile">Téléphone mobile (natel)</label>';
					if ( isset($data_to_display['tel_fixe']['value']) ) {
						$html_code .= add_FormElement_input('text', 'tel_mobile', array('input_tel', 'disableAutoComplete'), format_tel($data_to_display['tel_mobile']['value']));
					} else {
						$html_code .= add_FormElement_input('text', 'tel_mobile', array('input_tel', 'disableAutoComplete'), '');
					}
				$html_code .= '</p>';
			$html_code .= '</fieldset>';


			$html_code .= '<fieldset id="beneficiaire_particularites">';
				$html_code .= '<legend>Particularités</legend>';

				$html_code .= '<p>';
					$html_code .= '<label for="toujours_2">Se déplace avec son épous(e)</label>';
					if ( isset($data_to_display['toujours_2']['value']) ) {
						$html_code .= add_FormElement_input('checkbox', 'toujours_2', '',  $data_to_display['toujours_2']['value']);
					} else {
						$html_code .= add_FormElement_input('checkbox', 'toujours_2', '',  '');
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


			$html_code .= '<div id="beneficiaire_repondant">';

				$html_code .= '<fieldset id="form_beneficiaire_repondant">';
					$html_code .= '<legend>Répondant</legend>';

					$html_code .= '<p>';
						$html_code .= '<label>Lien avec le passager</label>';
						if ( isset($data_to_display['repondant']['lien_beneficiaire']['value']) ) {
							$html_code .= add_FormElement_input('text', 'repondant_lien_beneficiaire', array('input_adresse', 'disableAutoComplete'), $data_to_display['repondant']['lien_beneficiaire']['value']);
						} else {
							$html_code .= add_FormElement_input('text', 'repondant_lien_beneficiaire', array('input_adresse', 'disableAutoComplete'), '');
						}
					$html_code .= '</p>';

					global $dbh;

					$sql = "SELECT * FROM repondant_categorie ORDER BY nom ";
					$sth = $dbh->query($sql);
					$result = $sth->fetchAll(PDO::FETCH_ASSOC);

					foreach ($result as $row) {
						$html_code .= '<p>';

						$html_code .= '<input type="radio" id="repondant_id_categorie" name="repondant_id_categorie" value="' . $row['id'] . '" ';

						if ( isset($data_to_display['repondant']['id_categorie']['value']) ) {
							if ($data_to_display['repondant']['id_categorie']['value'] == $row['id']) {
								$html_code .= 'checked="checked" />';
							} else {
								$html_code .= ' />';
							}
						} else {
							$html_code .= ' />';
						}

						$html_code .= '<label>';
							$html_code .= $row['nom'];
						$html_code .= '</label>';

						// si les donnees peuvent etre reprise du table, charger les possibilites
						if ($row['auto_mount'] == 1) {

							$table = stripAccents(mb_strtolower($row['nom'], 'UTF-8'));


							$sql = "SELECT * FROM " . $table . " ORDER BY nom";
							$sth = $dbh->query($sql);
							$result_table = $sth->fetchAll(PDO::FETCH_ASSOC);

							$html_code .= '<select id="repondant_ref_external_' . $table . '" name="repondant_ref_external_' . $table . '">';

								//empty line
								$html_code .= '<option></option>';

								foreach($result_table as $row_table) {
									$html_code .= '<option value="' . $row_table['id'] . '"';

										if ( isset($data_to_display['repondant']['id_categorie']['value']) ) {
											if ($data_to_display['repondant']['id_categorie']['value'] == $row['id'] && $data_to_display['repondant']['ref_external']['value'] == $row_table['id']) {
												$html_code .= 'selected="selected">';
											} else {
												$html_code .= '>';
											}
										} else {
											$html_code .= '>';
										}

										$html_code .= mb_strtoupper(stripAccents($row_table['nom']));

										if (isset($row_table['prenom']) && $row_table['prenom'] != '') {
											$html_code .= ', ' . $row_table['prenom'];
										}

									$html_code .= '</option>';
								}
							$html_code .= '</select>';
						}

						$html_code .= '</p>';
					}

					//si pas d'auto_mount pour la categorie choisie, charge le form autre
					$html_code .= '<p>';
						$html_code .= '<label>Nom du répondant</label>';
						if ( isset($data_to_display['repondant']['id']['value']) ) {
							$html_code .= add_FormElement_input('hidden', 'repondant_id', '', $data_to_display['repondant']['id']['value']);
						} else {
							$html_code .= add_FormElement_input('hidden', 'repondant_id', '', '');
						}

						if ( isset($data_to_display['repondant']['nom']['value']) ) {
							$html_code .= add_FormElement_input('text', 'repondant_nom', array('input_nom', 'disableAutoComplete'), $data_to_display['repondant']['nom']['value']);
						} else {
							$html_code .= add_FormElement_input('text', 'repondant_nom', array('input_nom', 'disableAutoComplete'), '');
						}
					$html_code .= '</p>';

					$html_code .= '<p>';
						$html_code .= '<label>Prénom du répondant</label>';
						if ( isset($data_to_display['repondant']['prenom']['value']) ) {
							$html_code .= add_FormElement_input('text', 'repondant_prenom', array('input_nom', 'disableAutoComplete'), $data_to_display['repondant']['prenom']['value']);
						} else {
							$html_code .= add_FormElement_input('text', 'repondant_prenom', array('input_nom', 'disableAutoComplete'), '');
						}
					$html_code .= '</p>';

					$html_code .= '<p>';
						$html_code .= '<label>Adresse du répondant</label>';
						if ( isset($data_to_display['repondant']['adresse']['value']) ) {
							$html_code .= add_FormElement_input('text', 'repondant_adresse', array('input_adresse', 'disableAutoComplete'), $data_to_display['repondant']['adresse']['value']);
						} else {
							$html_code .= add_FormElement_input('text', 'repondant_adresse', array('input_adresse', 'disableAutoComplete'), '');
						}
					$html_code .= '</p>';

					$html_code .= '<p>';
						$html_code .= '<label>Complément d\'Adresse du répondant</label>';
						if ( isset($data_to_display['repondant']['adresse_complement']['value']) ) {
							$html_code .= add_FormElement_input('text', 'repondant_adresse_complement', array('input_adresse', 'disableAutoComplete'), $data_to_display['repondant']['adresse_complement']['value']);
						} else {
							$html_code .= add_FormElement_input('text', 'repondant_adresse_complement', array('input_adresse', 'disableAutoComplete'), '');
						}
					$html_code .= '</p>';

					$html_code .= '<p>';
						$html_code .= '<label>NPA du répondant</label>';
						if ( isset($data_to_display['repondant']['npa']['value']) ) {
							$html_code .= add_FormElement_input('text', 'repondant_npa', array('input_npa', 'disableAutoComplete'), $data_to_display['repondant']['npa']['value']);
						} else {
							$html_code .= add_FormElement_input('text', 'repondant_npa', array('input_npa', 'disableAutoComplete'), '');
						}
					$html_code .= '</p>';

					$html_code .= '<p>';
						$html_code .= '<label>Ville du répondant</label>';
						if ( isset($data_to_display['repondant']['ville']['value']) ) {
							$html_code .= add_FormElement_input('text', 'repondant_ville', array('input_nom'), $data_to_display['repondant']['ville']['value']);
						} else {
							$html_code .= add_FormElement_input('text', 'repondant_ville', array('input_nom'), '');
						}
					$html_code .= '</p>';

					$html_code .= '<p>';
						$html_code .= '<label>Téléphone fixe du répondant</label>';
						if ( isset($data_to_display['repondant']['tel_fixe']['value']) ) {
							$html_code .= add_FormElement_input('text', 'repondant_tel_fixe', array('input_tel', 'disableAutoComplete'), format_tel($data_to_display['repondant']['tel_fixe']['value']));
						} else {
							$html_code .= add_FormElement_input('text', 'repondant_tel_fixe', array('input_tel', 'disableAutoComplete'), '');
						}
					$html_code .= '</p>';

					$html_code .= '<p>';
						$html_code .= '<label>Téléphone mobile du répondant</label>';
						if ( isset($data_to_display['repondant']['tel_mobile']['value']) ) {
							$html_code .= add_FormElement_input('text', 'repondant_tel_mobile', array('input_tel', 'disableAutoComplete'), format_tel($data_to_display['repondant']['tel_mobile']['value']));
						} else {
							$html_code .= add_FormElement_input('text', 'repondant_tel_mobile', array('input_tel', 'disableAutoComplete'), '');
						}
					$html_code .= '</p>';

				$html_code .= '</fieldset>';
			$html_code .= '</div>';

			$html_code .= '<p>';
				$html_code .= '<a id="show_beneficiaire_repondant" href="">';
					$html_code .= '<em>Afficher la partie concernant le répondant</em>';
				$html_code .= '</a>';
			$html_code .= '</p>';

			$html_code .= '<p>';

				if (isset($data_to_display['id']['value'])) {
					$html_code .= add_FormElement_input('hidden', 'id', '', $data_to_display['id']['value']);
				}

				$html_code .= add_FormElement_input('hidden', 'form', '', 'base');
				$html_code .= add_FormElement_input('hidden', 'module', '', 'beneficiaire');
				$html_code .= add_FormElement_input('hidden', 'sub_module', '', '""');
				$html_code .= add_FormElement_input('hidden', 'action', '', $action);


				$html_code .= '<input type="submit" value="Valider le nouveau passager et retour à l\'accueil" />';
			$html_code .= '</p>';

		$html_code .= '</form>';

		$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));

		return $html_code;
	} //class.Beneficiaire.form.base


	private static function form_choose($action, $data_to_display='') {
		unset($_POST);
		global $dbh;
		$sql = "SELECT * FROM beneficiaire ORDER BY nom";
		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		$html_code = '<form id="beneficiaire_choose" action="" method="post">';
			$html_code .= '<select id="id" name="id">';
				foreach ($result as $row) {
					$html_code .= '<option value="' . $row['id'] . '">';
					$html_code .= mb_strtoupper(stripAccents($row['nom'])) . ', ' . $row['prenom'];
					$html_code .= '</option>';
				}
			$html_code .= '</select>';

			$html_code .= '<p>';
				$html_code .= add_FormElement_input('hidden', 'form', '', 'choose');
				$html_code .= add_FormElement_input('hidden', 'module', '', 'beneficiaire');
				$html_code .= add_FormElement_input('hidden', 'sub_module', '', '""');
				$html_code .= add_FormElement_input('hidden', 'action', '', $action);

				$html_code .= '<input type="submit" value="Soumettre" />';
			$html_code .= '</p>';
		$html_code .= '</form>';

		return $html_code;
	} //class.Beneficiaire.form.choose

	private static function form_list($action) {
		//unset($_POST);
		global $dbh;

		if (isset($_POST['search'])) {
			$sql = "SELECT * FROM beneficiaire ";
			$sql .= " WHERE nom LIKE '%" . $_POST['search'] . "%'";
			$sql .= " OR prenom LIKE '%" . $_POST['search'] . "%'";
			$sql .= " OR ville LIKE '%" . $_POST['search'] . "%'";
			$sql .= " ORDER BY nom";
		} else {
			$sql = "SELECT * FROM beneficiaire ORDER BY nom";
		}

		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);


		$html_code = '';

		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}

		$html_code .= '<form id="beneficiaire_seach_list"  action="" method="post">';
			$html_code .= '<p>';
				$html_code .= '<input type="text" id="search" name="search" />';

				$html_code .= add_FormElement_input('hidden', 'form', '', 'search');
				$html_code .= add_FormElement_input('hidden', 'module', '', 'beneficiaire');
				$html_code .= add_FormElement_input('hidden', 'sub_module', '', '""');
				$html_code .= add_FormElement_input('hidden', 'action', '', 'list');

				$html_code .= '<input type="submit" value="Soumettre" />';

			$html_code .= '</p>';

			$html_code .= '<p>';
				$html_code .= '<a href="?module=beneficiaire&amp;action=add">Nouveau passager</a>';
			$html_code .= '</p>';
		$html_code .= '</form>';

		$html_code .= '<p>';
			$alpha_letter = array();

			foreach ($result as $row) {
				if (!in_array(mb_strtoupper(stripAccents(substr($row['nom'], 0, 1))), $alpha_letter)) {
					$alpha_letter[] = mb_strtoupper(stripAccents(substr($row['nom'], 0, 1)));
				}
			}

			foreach ($alpha_letter as $letter) {
				$html_code .= '<a href="#' . $letter . '">' . $letter . '</a> | ';
			}

		$html_code .= '</p>';

		$html_code .= '<table class="OddEven" id="list_all_beneficiaire">';
		$html_code .= '<thead>';
			$html_code .= '<tr>';
				$html_code .= '<th>Nom</th>';
				$html_code .= '<th>Prénom</th>';
				$html_code .= '<th>Adresse</th>';
				$html_code .= '<th>Ville</th>';
				$html_code .= '<th>Téléphone fixe</th>';
				$html_code .= '<th>Téléphone mobile</th>';
				$html_code .= '<th>Nouveau transport</th>';
				$html_code .= '<th>Modifier</th>';
			$html_code .= '</tr>';
		$html_code .= '</thead>';

		$html_code .= '<tbody>';

			$last_letter = '';

			foreach ($result as $row) {

				//Rajoute une ligne HEAD avec la premiere lettre du nom pour les regroupement
				if (mb_strtoupper(stripAccents($last_letter)) <> mb_strtoupper(stripAccents(substr($row['nom'], 0, 1)))) {

					$last_letter = mb_strtoupper(stripAccents(substr($row['nom'], 0, 1)));

						$html_code .= '<tr>';
							$html_code .= '<th>';
								$html_code .= '<a name="' . $last_letter . '"></a><a href="#top">' . $last_letter . '</a>';
							$html_code .= '</th>';
						$html_code .= '</tr>';
				}

				$html_code .= '<tr>';

					$html_code .= '<td><a href="?module=beneficiaire&amp;id=' . $row['id'] . '&amp;action=view">' . $row['nom'] .'</a></td>';
					$html_code .= '<td>' . $row['prenom'] .'</td>';
					$html_code .= '<td>' . format_adresse($row['adresse']) .'</td>';
					$html_code .= '<td>' . $row['ville'] .'</td>';
					$html_code .= '<td>' . format_tel($row['tel_fixe']) .'</td>';
					$html_code .= '<td>' . format_tel($row['tel_mobile']) .'</td>';


					$html_code .= '<td><a href="?module=beneficiaire&amp;id=' . $row['id'] .'&amp;action=new_transport">Nouveau transport</a></td>';
					$html_code .= '<td><a href="?module=beneficiaire&amp;id=' . $row['id'] .'&amp;action=edit">Modifier</a></td>';

				$html_code .= '</tr>';
			}

		$html_code .= '</tbody>';
	$html_code .= '</table>';

	$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));

	return $html_code;

	} //class.Beneficiaire.form.list


	private static function form_view($action, $data_to_display='') {

		$tmp_beneficiaire = new Beneficiaire($data_to_display['id']['value']);

		$html_code = '';

		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}

		$html_code .= '<h1>Passager</h1>';

		$html_code .= '<p>';
			$html_code .= '<a href="?module=beneficiaire&amp;action=edit&amp;id=' . $data_to_display['id']['value'] . '">Modifier le passager</a>';
		$html_code .= '</p>';


		$html_code .= '<p>';
			$html_code .= $data_to_display['titre']['value'] . '<br />';
			$html_code .= $data_to_display['prenom']['value'] . ' ' . $data_to_display['nom']['value'] . '<br />';

			if (isset($data_to_display['adresse']['value'])) {
				$html_code .= $data_to_display['adresse']['value'] . '<br />';
			}

			if (isset($data_to_display['adresse_complement']['value']) && $data_to_display['adresse_complement']['value'] != '') {
				$html_code .= $data_to_display['adresse_complement']['value'] . '<br />';
			}

			if (isset($data_to_display['npa']['value'])) {
				$html_code .= $data_to_display['npa']['value'] . ' ';
			}

			if (isset($data_to_display['ville']['value'])) {
				$html_code .= $data_to_display['ville']['value'];
			}

			$tmp_beneficiaire_telephone = $tmp_beneficiaire->get_telephone();

			foreach ($tmp_beneficiaire_telephone as $index => $row) {
				$html_code .= '<br />';
				$html_code .= str_replace('tel_', '', $index) . ' : ' . format_tel($row);
			}

		$html_code .= '</p>';

		//info diverses
		if ($data_to_display['info_diverses']['value'] != '') {
			$html_code .= '<p>';
				$html_code .= nl2br($data_to_display['info_diverses']['value']);
			$html_code .= '</p>';
		}



		if (checkInternetConnection()) {
			$html_code .= '<div id="map_beneficiaire" class="map_google"></div>';

			$html_code .= '<script type="text/javascript">';
				$html_code .= '$(document).ready(function() {';
					$html_code .= '$(\'#map_beneficiaire\').googleMap("' . $data_to_display['adresse']['value'] . ',' . $data_to_display['npa']['value'] . ',' . $data_to_display['ville']['value'] . '");';
				$html_code .= '});';
			$html_code .= '</script>';

		}


		//partie sur le repondant
		if ($tmp_beneficiaire->has_repondant()) {

			$html_code .= '<h1>Répondant</h1>';

			$tmp_repondant = new Repondant($tmp_beneficiaire->get_id_repond());
			$tmp_repondant_categorie = new Repondant_Categorie($tmp_repondant->get_id_categorie());

			$html_code .= '<p>';
				$html_code .= $tmp_repondant_categorie->get_nom();
			$html_code .= '</p>';


			if ($tmp_repondant_categorie->is_auto_mount()) {
				$table = mb_strtolower(stripAccents($tmp_repondant_categorie->get_nom()));
				$ref_external = $tmp_repondant->get_ref_external();

				switch ($table) {
					case "beneficiaire":
						$ref_external_object = new Beneficiaire($ref_external);
						break;
					case "benevole":
						$ref_external_object = new Benevole($ref_external);
						break;
					case "lieu":
						$ref_external_object = new Lieu($ref_external);
						break;
				}

				if (is_object($ref_external_object) && $ref_external_object instanceof Contact) {
					$ref_external_nom_complet = $ref_external_object->get_nom_complet();
					$ref_external_adresse = $ref_external_object->get_adresse();
					$ref_external_telephone = $ref_external_object->get_telephone();
				} else {
					$ref_external_nom_complet = array();
					$ref_external_adresse = array();
					$ref_external_telephone = array();
				}

				//inscription des donnees auto_mount

				$html_code .= '<p>';

					//prenom
					if (isset($ref_external_nom_complet['prenom'])) {
						$html_code .= $ref_external_nom_complet['prenom'] . ' ';
					}

					//nom
					if ($table == 'benevole') {
						$html_code .= '<a href="?module=' . $table . '&action=view&id=' . Benevole::get_id_benevole_filiale_from_super_id_benevole_and_id_filiale($ref_external_object->get_id(), $_SESSION['filiale']['id']) . '">';
					} else {
						$html_code .= '<a href="?module=' . $table . '&action=view&id=' . $ref_external_object->get_id() . '">';
					}
						$html_code .= $ref_external_nom_complet['nom'];
					$html_code .= '</a>';

					//adresse
					if (isset($ref_external_adresse['adresse'])) {
						$html_code .= '<br />';
						$html_code .= $ref_external_adresse['adresse'];
					}

					//adresse_complement
					if (isset($ref_external_adresse['adresse_complement'])) {
						$html_code .= '<br />';
						$html_code .= $ref_external_adresse['adresse_complement'];
					}

					//npa
					if (isset($ref_external_adresse['npa'])) {
						$html_code .= '<br />';
						$html_code .= $ref_external_adresse['npa'] . ' ';
					}

					//ville
					if (isset($ref_external_adresse['ville'])) {
						$html_code .= $ref_external_adresse['ville'];
					}

					foreach ($ref_external_telephone as $index => $row) {
						$html_code .= '<br />';
						$html_code .= str_replace('tel_', '', $index) . ' : ' . format_tel($row);
					}

			}


			//inscription des donnees supplementaires inscrite manuellement
			$html_code .= '<h1>Personne de contact</h1>';

			$html_code .= '<p>';
				$html_code .= $data_to_display['repondant']['lien_beneficiaire']['value'];
			$html_code .= '</p>';



			$html_code .= '<p>';

				if (isset($data_to_display['repondant']['prenom']['value'])) {
					$html_code .= $data_to_display['repondant']['prenom']['value'] . ' ';
				}

				$html_code .= $data_to_display['repondant']['nom']['value'];
					$html_code .= '<br />';

				if (isset($data_to_display['repondant']['adresse']['value'])) {
					$html_code .= $data_to_display['repondant']['adresse']['value'];
					$html_code .= '<br />';
				}

				if (isset($data_to_display['repondant']['npa']['value'])) {
					$html_code .= $data_to_display['repondant']['npa']['value'] . ' ';
				}

				if (isset($data_to_display['repondant']['ville']['value'])) {
					$html_code .= $data_to_display['repondant']['ville']['value'];
					$html_code .= '<br />';
				}

				if (isset($data_to_display['repondant']['tel_fixe']['value'])) {
					$html_code .= format_tel($data_to_display['repondant']['tel_fixe']['value']);
					$html_code .= '<br />';
				}

				if (isset($data_to_display['repondant']['tel_mobile']['value'])) {
					$html_code .= format_tel($data_to_display['repondant']['tel_mobile']['value']);
					$html_code .= '<br />';
				}

			$html_code .= '</p>';
		}


		//transport futurs deja prevu
		load_class_and_interface(array('Transport'));
		global $dbh;

		$sql = "SELECT transport.* ";
		$sql .= " FROM transport LEFT JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport "; //, benevole_participation_filiale, benevole ";
		$sql .= " WHERE transport.id_filiale=" . $_SESSION['filiale']['id'];
		$sql .= " AND transport.is_annule=0";
		$sql .= " AND transport.id_beneficiaire = " . $data_to_display['id']['value'];
		$sql .= " AND transport.date_transport>='" . date('Y-m-d') . "'";
		$sql .= " ORDER BY transport.date_transport, transport.heure_debut";

		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		$sql = "SELECT benevole.*, transport_transporteur.*, transport.* ";
		$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport, benevole_participation_filiale, benevole ";
		$sql .= " WHERE transport_transporteur.id_transporteur = benevole_participation_filiale.id ";
		$sql .= " AND benevole_participation_filiale.id_benevole = benevole.id";
		$sql .= " AND transport.id_filiale=" . $_SESSION['filiale']['id'];
		$sql .= " AND transport.is_annule=0";
		$sql .= " AND transport.id_beneficiaire = " . $data_to_display['id']['value'];
		$sql .= " AND transport.date_transport>='" . date('Y-m-d') . "'";
		$sql .= " ORDER BY transport.date_transport ASC";

		$sth = $dbh->query($sql);
		$result_transport_with_driver = $sth->fetchAll(PDO::FETCH_ASSOC);

		if (count($result) > 0) {

			$html_code .= '<br />';

			$html_code .= '<h1>Futurs transports</h1>';
			$html_code .= '<table class="OddEven">';
				$html_code .= '<thead>';
					$html_code .= '<th>Date</th>';
					$html_code .= '<th>Heure</th>';
					$html_code .= '<th>Transporteur</th>';
					$html_code .= '<th>Ville départ</th>';
					$html_code .= '<th>Ville arrivée</th>';
					$html_code .= '<th></th>'; // Modifier
					$html_code .= '<th></th>'; // Annuler
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

								//transporteur
								$find_driver = FALSE;

								foreach ($result_transport_with_driver as $row_2) {
									if ($row_2['id'] == $row['id']) {

										$tmp_transporteur = new Transporteur($row_2['id_transporteur']);
										$tmp_transporteur_nom_complet = $tmp_transporteur->get_nom_complet();

										$html_code .= '<a href="?module=benevole&amp;action=view&amp;id=' . $row_2['id_transporteur'] . '">';
											$html_code .= mb_strtoupper(stripAccents($tmp_transporteur_nom_complet['nom'])) . ', ' . $tmp_transporteur_nom_complet['prenom'];
										$html_code .= '</a>';

										$find_driver = TRUE;
										break;
									}
								}

								//si arrive a ce stade, le chauffeur n'a pas ete trouve
								if ($find_driver == FALSE) {
									$html_code .= '<a href="?module=transport&amp;action=find_driver&amp;id=' . $row['id'] . '">';
										$html_code .= '<strong>Chercher un chauffeur</strong>';
									$html_code .= '</a>';
								}

							$html_code .= '</td>';

							$point_depart = unserialize($row['point_depart']);
							$html_code .= '<td>';
								$html_code .= $point_depart['ville'];
							$html_code .= '</td>';

							$point_arrivee = unserialize($row['point_arrivee']);
							$html_code .= '<td>';
								$html_code .= $point_arrivee['ville'];
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
		}

		//10 derniers transports
		$nbre_histo_transport_a_afficher = 10;
		$sql = "SELECT benevole.*, transport_transporteur.*, transport.* ";
		$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport, benevole_participation_filiale, benevole ";
		$sql .= " WHERE transport_transporteur.id_transporteur = benevole_participation_filiale.id ";
		$sql .= " AND benevole_participation_filiale.id_benevole = benevole.id";
		$sql .= " AND transport.id_filiale=" . $_SESSION['filiale']['id'];
		$sql .= " AND transport.is_annule=0";
		$sql .= " AND transport.id_beneficiaire = " . $data_to_display['id']['value'];
		$sql .= " AND transport.date_transport<'" . date('Y-m-d') . "'";
		$sql .= " ORDER BY transport.date_transport DESC";
		$sql .= " LIMIT $nbre_histo_transport_a_afficher";


		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		if (count($result) > 0) {
			$html_code .= '<br />';
			$html_code .= '<h1>' . $nbre_histo_transport_a_afficher . ' derniers transports <em>effectués</em></h1>';
			$html_code .= '<table class="OddEven">';
				$html_code .= '<thead>';
					$html_code .= '<th>Date</th>';
					$html_code .= '<th>Heure</th>';
					$html_code .= '<th>Transporteur</th>';
					$html_code .= '<th>Ville départ</th>';
					$html_code .= '<th>Ville arrivée</th>';
					$html_code .= '<th></th>'; //Modifier
					$html_code .= '<th></th>'; //Annuler
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
								$html_code .= '<a href="?module=benevole&amp;action=view&amp;id=' . $row['id_transporteur'] . '">';

									$tmp_transporteur = new Transporteur($row['id_transporteur']);
									$tmp_transporteur_nom_complet = $tmp_transporteur->get_nom_complet();

									$html_code .= mb_strtoupper(stripAccents($tmp_transporteur_nom_complet['nom'])) . ', ' . $tmp_transporteur_nom_complet['prenom'];

								$html_code .= '</a>';
							$html_code .= '</td>';

							$point_depart = unserialize($row['point_depart']);
							$html_code .= '<td>';
								$html_code .= $point_depart['ville'];
							$html_code .= '</td>';

							$point_arrivee = unserialize($row['point_arrivee']);
							$html_code .= '<td>';
								$html_code .= $point_arrivee['ville'];
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
		}

		$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));

		return $html_code;
	} //class.Beneficiaire.form.view


	private static function form_tarif($action, $data_to_display='') {
		$html_code = '';

		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}

		if ( ( (isset($data_to_display['id']['value']) && $data_to_display['id']['value'] != '') || (isset($data_to_display['id_depart']['value']) && $data_to_display['id_depart']['value'] != '') || (isset($data_to_display['depart_custom']['value']) && $data_to_display['depart_custom']['value'] != '') )  && ( (isset($data_to_display['id_destination']['value']) && $data_to_display['id_destination']['value'] != '') || ( isset($data_to_display['arrivee_custom']['value']) && $data_to_display['arrivee_custom']['value'] != '') ) ) {

			//calcul le prix et l'affiche dans le form html
			if (isset($data_to_display['id']['value']) && Beneficiaire::id_exists($data_to_display['id']['value'])) {
				$tmp_beneficiaire = new Beneficiaire($data_to_display['id']['value']);
				$point_depart = $tmp_beneficiaire->get_adresse();
			} elseif (isset($data_to_display['id_depart']['value']) && Lieu::id_exists($data_to_display['id_depart']['value'])) {
				load_class_and_interface(array('Lieu'));
				$tmp_lieu = new Lieu($data_to_display['id_depart']['value']);
				$point_depart = $tmp_lieu->get_adresse();
			} else {
				$point_depart['ville'] = $data_to_display['depart_custom']['value'];
				$point_depart['pays'] = $data_to_display['depart_custom_pays']['value'];
			}


			if (isset($data_to_display['id_destination']['value']) && Lieu::id_exists($data_to_display['id_destination']['value'])) {
				load_class_and_interface(array('Lieu'));
				$tmp_lieu = new Lieu($data_to_display['id_destination']['value']);
				$point_arrivee = $tmp_lieu->get_adresse();
			} else {
				$point_arrivee['ville'] = $data_to_display['arrivee_custom']['value'];
				$point_arrivee['pays'] = $data_to_display['arrivee_custom_pays']['value'];
			}


			if (is_array($point_depart) && is_array($point_arrivee)) {
				load_class_and_interface(array('Filiale', 'Trajet_Pre_Defini'));

				if (isset($point_depart['adresse']) == FALSE) {
					$point_depart['adresse'] = '';
				}

				if (isset($point_arrivee['adresse']) == FALSE) {
					$point_arrivee['adresse'] = '';
				}

				$distance = ceil(Trajet_Pre_Defini::download_distance_from_google_maps($point_depart['adresse'], $point_depart['npa'], $point_depart['ville'], $point_depart['pays'], $point_arrivee['adresse'], $point_arrivee['npa'], $point_arrivee['ville'], $point_arrivee['pays'], FALSE));

				$tmp_filiale = new Filiale($_SESSION['filiale']['id']);
				$prix_km = $tmp_filiale->get_standard_prix_km();
				$tx_remboursement_chauffeur = $tmp_filiale->get_standard_taux_compenation();
				$forfait = $tmp_filiale->get_standard_prix_forfait_min();

				$html_code .= '<table>';
					$html_code .= '<thead>';
						$html_code .= '<th>Départ</th>';
						$html_code .= '<th>Destination</th>';
						$html_code .= '<th>Distance</th>';
						$html_code .= '<th>Prix du km</th>';
						$html_code .= '<th>Coût du trajet</th>';
						$html_code .= '<th>Remboursement</th>';
					$html_code .= '</thead>';

					$html_code .= '<tbody>';
						$html_code .= '<td>';
							$html_code .= mb_strtoupper(stripAccents($point_depart['ville']));
						$html_code .= '</td>';

						$html_code .= '<td>';
							$html_code .= mb_strtoupper(stripAccents($point_arrivee['ville']));
						$html_code .= '</td>';

						$html_code .= '<td>';
							$html_code .= '2x ' . $distance . ' km';
						$html_code .= '</td>';

						$html_code .= '<td>';
							$html_code .= $prix_km . ' CHF';
						$html_code .= '</td>';

						$html_code .= '<td>';
							if (2 * $prix_km * $distance < $forfait) {
								$html_code .= $forfait;
								$cout_total = $forfait;
							} else {
								$cout_total = number_format(2* $prix_km * $distance, 2);
								$html_code .= $cout_total . ' CHF';
							}
						$html_code .= '</td>';


						$html_code .= '<td>';
							$html_code .= number_format($cout_total * ($tx_remboursement_chauffeur/100),2) . ' CHF' . '(' . $tx_remboursement_chauffeur  . '%)';
						$html_code .= '</td>';

					$html_code .= '</tbody>';
				$html_code .= '</table>';
			}


		} else {
			//charge le form pour sélectionner le départ & l'arrivée
			$html_code .= '<form action="" method="get">';

				global $dbh;


				$html_code .= '<fieldset>';

					$html_code .= '<legend>Point de départ</legend>';

					//remonte et affiche la liste des passagers
					$sql = "SELECT id, nom, prenom FROM beneficiaire ORDER BY nom";
					$sth = $dbh->query($sql);
					$result = $sth->fetchAll(PDO::FETCH_ASSOC);

					$html_code .= '<p>';

						$html_code .= '<label for="id">Passager</label>';
						$html_code .= '<select id="id" name="id">';

							$html_code .= '<option></option>';

							foreach ($result as $row) {
								$html_code .= '<option value="' . $row['id'] . '">';
									$html_code .= mb_strtoupper(stripAccents($row['nom'])) . ', ' . $row['prenom'];
								$html_code .= '</option>';
							}

						$html_code .= '</select>';


					//remonte et affiche la liste des destinations
					$sql = "SELECT id, nom, ville FROM lieu ORDER BY ville, nom";
					$sth = $dbh->query($sql);
					$result = $sth->fetchAll(PDO::FETCH_ASSOC);

						$html_code .= '<p>';
							$html_code .= '<strong>ou</strong> ';


							$html_code .= '<label for="id_depart">point de départ préconfiguré</label>';
							$html_code .= '<select id="id_depart" name="id_depart">';

								$html_code .= '<option></option>';

								foreach ($result as $row) {
									$html_code .= '<option value="' . $row['id'] . '">';
										if (mb_strtoupper(stripAccents($row['ville'])) == mb_strtoupper(stripAccents($row['nom']))) {
											$html_code .= mb_strtoupper(stripAccents($row['ville']));
										} else {
											$html_code .= mb_strtoupper(stripAccents($row['ville'])) . ', ' . $row['nom'];
										}

									$html_code .= '</option>';
								}

							$html_code .= '</select>';
						$html_code .= '</p>';

						$html_code .= '<p>';
							$html_code .= '<strong>ou</strong> ';

							$html_code .= '<label for="depart_custom">ville personnalisée</label>';
							$html_code .= add_FormElement_input('text', 'depart_custom', 'disableAutoComplete', '');
							$html_code .= add_FormElement_input('text', 'depart_custom_pays', 'disableAutoComplete', 'Suisse');
						$html_code .= '</p>';

					$html_code .= '</p>';

				$html_code .= '</fieldset>';

				$html_code .= '<fieldset>';

					$html_code .= '<legend>Destination</legend>';

					$html_code .= '<p>';

						$html_code .= '<label for="id_destination">Destination</label>';
						$html_code .= '<select id="id_destination" name="id_destination">';

							$html_code .= '<option></option>';

								foreach ($result as $row) {
									$html_code .= '<option value="' . $row['id'] . '">';
										if (mb_strtoupper(stripAccents($row['ville'])) == mb_strtoupper(stripAccents($row['nom']))) {
											$html_code .= mb_strtoupper(stripAccents($row['ville']));
										} else {
											$html_code .= mb_strtoupper(stripAccents($row['ville'])) . ', ' . $row['nom'];
										}

									$html_code .= '</option>';
								}

						$html_code .= '</select>';


						$html_code .= '<p>';
							$html_code .= '<strong>ou</strong> ';

							$html_code .= '<label for="arrivee_custom">ville personnalisée</label>';
							$html_code .= add_FormElement_input('text', 'arrivee_custom', 'disableAutoComplete', '');
							$html_code .= add_FormElement_input('text', 'arrivee_custom_pays', 'disableAutoComplete', 'Suisse');

						$html_code .= '</p>';

					$html_code .= '</p>';
				$html_code .= '</fieldset>';

				$html_code .= '<input type="submit" value="Soumettre" />';

				$html_code .= add_FormElement_input('hidden', 'form', '', 'tarif');
				$html_code .= add_FormElement_input('hidden', 'module', '', 'beneficiaire');
				$html_code .= add_FormElement_input('hidden', 'sub_module', '', '""');
				$html_code .= add_FormElement_input('hidden', 'action', '', $action);

			$html_code .= '</form>';
		}

		$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));

		return $html_code;
	} // class.Beneficiaire.form.tarif


	private static function form_ajax_get_beneficiaire_details($action, $data_to_display='') {

		if (isset($data_to_display['id_beneficiaire']['value']) && Beneficiaire::id_exists($data_to_display['id_beneficiaire']['value'])) {
			$id_beneficiaire = $data_to_display['id_beneficiaire']['value'];

			$tmp_beneficiaire = new Beneficiaire($id_beneficiaire);
			$tmp_beneficiaire_nom_complet = $tmp_beneficiaire->get_nom_complet();
			$tmp_beneficiaire_adresse = $tmp_beneficiaire->get_adresse();
			$tmp_beneficiaire_tel = $tmp_beneficiaire->get_telephone();

			$html_code = '';
			$html_code .= '<p>';
				//nom & prenom
				//$html_code .= mb_strtoupper(stripAccents($tmp_beneficiaire_nom_complet['nom'])) . ', ' . $tmp_beneficiaire_nom_complet['prenom'] . '<br />';

				//adresse
				foreach( $tmp_beneficiaire_adresse as $index => $row) {
					if ($index == 'adresse' || $index == 'adresse_complement' || $index == 'ville' ) {
						$html_code .= $row . '<br />';
					} elseif ($index == 'npa') {
						$html_code .= $row . ' ';
					}
				}

				//tel
				foreach( $tmp_beneficiaire_tel as $index => $row) {
					$html_code .= substr($index, 4) . ' : ' . format_tel($row) . '<br />';
				}

			$html_code .= '</p>';

			return $html_code;

		} else {
			return '';
		}
	} // class.Beneficiaire.form.ajax_get_beneficiaire_details



	private static function form_ajax_already_transport_same_date($action, $data_to_display) {

		$id_beneficiaire = $data_to_display['id_beneficiaire']['value'];
		$date_transport = $data_to_display['date_transport']['value'];

		global $dbh;

		$sql = "SELECT * ";
		$sql .= " FROM transport ";
		$sql .= " WHERE id_filiale=" . $_SESSION['filiale']['id'];
		$sql .= " AND is_annule=0";
		$sql .= " AND id_beneficiaire=" . $id_beneficiaire;
		$sql .= " AND date_transport=" . $dbh->quote($date_transport);

		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		$html_code = '';

		if ($result != false) {
			$html_code .= '<p class="highlights">';
				$html_code .= 'Ce passager a déjà un transport ce jour-là, êtes-vous sûr de ne pas être en-train d\'inscrire un transport déjà entré dans le système ?';
			$html_code .= '</p>';
		} else {
			$html_code .= '';
		}

		return $html_code;

	} // class.Beneficiaire.form.ajax_already_transport_same_date


	private static function form_ajax_already_transport_same_date_and_time($action, $data_to_display) {

		$id_beneficiaire = $data_to_display['id_beneficiaire']['value'];
		$date_transport = $data_to_display['date_transport']['value'];
		$heure_debut = $data_to_display['heure_debut']['value'];

		global $dbh;

		$sql = "SELECT * ";
		$sql .= " FROM transport ";
		$sql .= " WHERE id_filiale=" . $_SESSION['filiale']['id'];
		$sql .= " AND is_annule=0";
		$sql .= " AND id_beneficiaire=" . $id_beneficiaire;
		$sql .= " AND date_transport=" . $dbh->quote($date_transport);
		$sql .= " AND heure_debut=" . $dbh->quote($heure_debut);

		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		$html_code = '';

		if ($result != false) {
			$html_code .= '<p class="highlights">';
				$html_code .= '<strong>Ce transport est déjà entré dans le système !</strong>';
			$html_code .= '</p>';
		} else {
			$html_code .= '';
		}

		return $html_code;

	} // class.Beneficiaire.form.ajax_already_transport_same_date


} // class.Beneficiaire

?>
