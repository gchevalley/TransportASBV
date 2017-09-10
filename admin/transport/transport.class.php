<?php

require_once( str_replace ( '\\', '/', dirname(dirname(__FILE__))) . '/class.declaration.php' );
load_class_and_interface(array('Beneficiaire', 'Lieu', 'Transporteur', 'Permanencier', 'Benevole', 'Benevole_Disponibilite_Categorie', 'Transport_Categorie', 'Transport_Type_Calcul_Distance'));


class Transport {
	private $id = 0;

	private $id_beneficiaire = 0;
	private $beneficiaire; // object

	private $id_transporteur = 0;
	private $transporteur; // object

	private $id_filiale = 0;
	private $filiale; // object

	private $id_categorie = 0;
	private $categorie; // object

	private $date_transport = '';
	private $heure_debut = '';
	private $duree_approximative = 0; // en heure et base 10 : 1H30 -> 1.5
	/**
	 * non utilisé
	 * le modèle résonne en terme de demie-journée
	 * si un transporteur a un transport le matin, il est considéré
	 * comme non disponible pour le restant de la matinée
	 *
	 * @deprecated
	 */
	private $heure_approximative_fin = '';

	private $id_type_calcul_distance = 0;
	private $type_calcul_distance; // object

	/**
	 * n'utilise pas l'adresse du passager ou du lieu car ces informations
	 * peuvent ne pas être stable dans le temps.
	 * Il est donc important de la figer lors de l'insertion du transport
	 * dans la base de données
	 */
	private $point_depart = array(); //array serialize -> doit etre indep de calcul distance pour ne pas perdre l'information
	private $point_arrivee = array(); //array serialize

	private $nbre_kilometres = 0.0;
	private $aller_retour = 1; // (0=FALSE, 1=TRUE)
	private $cout_trajet = 0.0; // chf
	private $cout_variable = 0.0;
	private $taux_remboursement_transporteur = 100; // en %

	private $info_diverses = '';

	private $is_cloture = 0;
	private $is_archive = 0;
	private $is_annule = 0;
	private $raison_annulation = '';


	function __construct($id_transport, $id_beneficiaire=0, $id_categorie=0, $date_transport='', $heure_debut='', $duree_approximative=0, $id_type_calcul_distance=0, $point_depart='', $point_arrivee='', $nbre_kilometres=0, $aller_retour=1, $cout_trajet=0, $taux_remboursement_transporteur=100, $info_diverses='', $is_cloture=0, $id_transporteur=0) {
		if (is_numeric($id_transport) && Transport::id_exists($id_transport)) {

			$this->id = $id_transport;
			$this->mountAttributsFromDB();

		} else { //creation de la nouvelle entite

			//second check pour s'assurer qu'il n'existe pas un transport proche/similaire

			$this->addEntryDB($id_beneficiaire, $id_categorie, $date_transport, $heure_debut, $duree_approximative, $id_type_calcul_distance, $point_depart, $point_arrivee, $nbre_kilometres, $aller_retour, $cout_trajet, $taux_remboursement_transporteur, $info_diverses, $is_cloture, $id_transporteur);
		}
	} // class.Transport.func.__construct

	private function mountAttributsFromDB() {

		//charge les donnees direct depuis la DB
		global $dbh;

		//mount la totalite des donnees
		$sql = "SELECT * FROM transport WHERE id=" .$this->id;

		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		//s'assurer qu'un resultat est retourne bien alloue les donnees aux attributs de l'object
		$this->id_beneficiaire = $result['id_beneficiaire'];
		$this->id_filiale = $result['id_filiale'];
		$this->id_categorie = $result['id_categorie'];
		$this->date_transport = $result['date_transport'];
		$this->heure_debut = $result['heure_debut'];
		$this->duree_approximative = $result['duree_approximative'];
		$this->id_type_calcul_distance = $result['id_type_calcul_distance'];
		$this->point_depart = unserialize($result['point_depart']);
		$this->point_arrivee = unserialize($result['point_arrivee']);
		$this->nbre_kilometres = $result['nbre_kilometres'];
		$this->aller_retour = $result['aller_retour'];
		$this->cout_trajet = $result['cout_trajet'];
		$this->cout_variable = $result['cout_variable'];
		$this->taux_remboursement_transporteur = $result['taux_remboursement_transporteur'];
		$this->info_diverses = $result['info_diverses'];
		$this->is_cloture = $result['is_cloture'];
		$this->is_archive = $result['is_archive'];
		$this->is_annule = $result['is_annule'];
		$this->raison_annulation = $result['raison_annulation'];

		$this->mountBeneficiaire();
		$this->mountFiliale();
		$this->mountCategorie();
		$this->mountTypeCalculDistance();
		$this->mountTransporteur();

	} // class.Lieu.func.mountAttributsFromDB


	private function addEntryDB($id_beneficiaire=0, $id_categorie=0, $date_transport='', $heure_debut='', $duree_approximative=0, $id_type_calcul_distance=0, $point_depart='', $point_arrivee='', $nbre_kilometres=0, $aller_retour=1, $cout_trajet=0, $taux_remboursement_transporteur=100, $info_diverses='', $is_cloture=0, $id_transporteur=0) {

		if (Benevole::id_benevole_filiale_exists($_SESSION['benevole']['id_benevole_filiale'])) {
			$tmp_benevole = new Benevole(Benevole::get_super_id_benvole_from_id_benevole_filiale($_SESSION['benevole']['id_benevole_filiale']));
			$id_filiale = $_SESSION['filiale']['id'];
			$tmp_filiale = new Filiale($id_filiale);

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

		if ($id_beneficiaire instanceof Beneficiaire) {
			$id_beneficiaire = $id_beneficiaire->get_id();
		}

		if ($id_transporteur instanceof Transporteur) {
			$id_transporteur = $id_transporteur->get_id($_SESSION['filiale']['id']);
		}


		if (!Beneficiaire::id_exists($id_beneficiaire) || !Transport_Categorie::id_exists($id_categorie) || !Transport_Type_Calcul_Distance::id_exists($id_type_calcul_distance) || !is_date($date_transport) || !isset($point_depart) || !isset($point_arrivee)) {
			die();
		} else {
			//on continue
		}

		global $dbh;

		//processing de nettoyage des args de la fonction
		$date_transport = $dbh->quote($date_transport);
		$heure_debut = $dbh->quote($heure_debut);

		$info_diverses = $dbh->quote($info_diverses);

		if (is_object($point_depart) && $point_depart instanceof Contact) {
			$point_depart = $point_depart->get_adresse();
		}

		if (is_object($point_arrivee) && $point_arrivee instanceof Contact) {
			$point_arrivee = $point_arrivee->get_adresse();
		}


		//calcul des distances
		if ($nbre_kilometres == 0) {
			$load_needed_class_and_interface = load_class_and_interface(array('Trajet_Pre_Defini', 'Direction'));

			//controle si la db n'a pas deja les km d'un trajet deja effectue par le passe
			$nbre_kilometres = Direction::find_combination($point_depart['adresse'], $point_depart['npa'], $point_depart['ville'], $point_arrivee['adresse'], $point_arrivee['npa'], $point_arrivee['ville']);

			if (!$nbre_kilometres) {

				//si les km ne sont pas recu en argument (ou trouver dans Direction), calculer la distance par google maps
				if (checkInternetConnection('maps.google.com')) {
					$nbre_kilometres = Trajet_Pre_Defini::download_distance_from_google_maps($point_depart['adresse'], $point_depart['npa'], $point_depart['ville'], $point_depart['pays'], $point_arrivee['adresse'], $point_arrivee['npa'], $point_arrivee['ville'], $point_arrivee['pays']);

					//ajoute l'entree dans la table Direction pour eviter de devoir refaire le telechargement des donnees dans le futur
					$load_needed_class_and_interface = load_class_and_interface(array('Direction'));
					if ($nbre_kilometres && $nbre_kilometres > 0) {
						$tmp_direction = new Direction(0, $point_depart['adresse'], $point_depart['npa'], $point_depart['ville'], $point_arrivee['adresse'], $point_arrivee['npa'], $point_arrivee['ville'], $nbre_kilometres);
					}

				} else {
					if ($point_depart['ville'] == $point_arrivee['ville']) {
						$nbre_kilometres = ceil($tmp_filiale->get_standard_prix_forfait_min() / 2*$tmp_filiale->get_standard_prix_km());
					} else {
						$distance_trajet_pre_defini = Trajet_Pre_Defini::find_combination($point_depart['ville'], $point_arrivee['ville']);
						if ($distance_trajet_pre_defini && is_numeric($distance_trajet_pre_defini['distance'])) {
							$nbre_kilometres = ceil($distance_trajet_pre_defini['distance']);
						} else {
							//impossible de connaitre la distance

						}
					}
				}
			}
		}

		if ($nbre_kilometres === false) {
			$nbre_kilometres = 0;
		} else {
			$nbre_kilometres = ceil(2*$nbre_kilometres); //compte tjr aller-retour
		}


		//calcul des couts
		if ($cout_trajet == 0) {
			$arrondi = 0.10; //arrondir a 0.10
			$km_cost = $tmp_filiale->get_standard_prix_km();
			$cout_trajet = (round((1/$arrondi) * $km_cost * $nbre_kilometres))/(1/$arrondi);

			// cout >= prix forfait
			if ($cout_trajet < $tmp_filiale->get_standard_prix_forfait_min()) {
				$cout_trajet = $tmp_filiale->get_standard_prix_forfait_min();
			}

			if ($duree_approximative > 2) {
				$cout_trajet = 2 * $cout_trajet;
			}
		}

		//recupere le taux par defaut de compensation de la filiale si celui-ci n'est pas precise
		if ($taux_remboursement_transporteur == 0) {
			$taux_remboursement_transporteur = $tmp_filiale->get_standard_taux_compenation();
		}

		//transforme l'array point_depart + point_arrivee en string pour etre stocke dans DB
		$point_depart = $dbh->quote(serialize($point_depart));
		$point_arrivee = $dbh->quote(serialize($point_arrivee));


		$today_date = $dbh->quote(date('Y-m-d'));
		$today_time = $dbh->quote(date('H:i:s'));


		//creation de la nouvelle entite dans la db
		$sql = "INSERT INTO transport (id_beneficiaire, id_filiale, id_categorie, date_transport, heure_debut, duree_approximative, id_type_calcul_distance, point_depart, point_arrivee, nbre_kilometres, aller_retour, cout_trajet, taux_remboursement_transporteur, info_diverses, is_cloture, is_archive, is_annule, raison_annulation, insert_date, insert_time, insert_user) ";
		$sql .= " VALUES ($id_beneficiaire, $id_filiale, $id_categorie, $date_transport, $heure_debut, $duree_approximative, $id_type_calcul_distance, $point_depart, $point_arrivee, $nbre_kilometres, $aller_retour, $cout_trajet, $taux_remboursement_transporteur, $info_diverses, 0, 0, 0, '', $today_date, $today_time, " . $tmp_benevole->get_id($_SESSION['filiale']['id']) .")";

		$statut_query = $dbh->exec($sql);

		//mount l'object
		$this->id = $dbh->lastInsertId();
		$this->mountAttributsFromDB();

		if (Transporteur::id_exists($id_transporteur)) {
			// cette fonction inclu l'envoi du mail si dispo
			$this->addTransporteur($id_transporteur);

		}

	} // class.Transport.func.addEntryDB


	public function addTransporteur($id_transporteur) { //id_filiale_benevole
		if (Transporteur::id_exists($id_transporteur)) {
			global $dbh;

			$today_date = $dbh->quote(date('Y-m-d'));
			$today_time = $dbh->quote(date('H:i:s'));

			$tmp_benevole = new Benevole(Benevole::get_super_id_benvole_from_id_benevole_filiale($_SESSION['benevole']['id_benevole_filiale']));

			//retire la precedente allocation du transport pour eviter double transporteur
			$sql = "DELETE FROM transport_transporteur WHERE id_transport=" . $this->id;
			$status_query= $dbh->exec($sql);

			//creation de la ligne dans transport_transporteur
			$sql = "INSERT INTO transport_transporteur (id_transport, id_transporteur, insert_date, insert_time, insert_user) ";
			$sql .= " VALUES (" . $this->id . ", $id_transporteur, $today_date, $today_time, " . $tmp_benevole->get_id($_SESSION['filiale']['id']) . ")";
			$status_query = $dbh->exec($sql);

			//envoi du mail
			$this->envoyer_email_chauffeur();
		}
	} // class.Transport.func.addTransporteur



	public function editerAttributs($attr, $new_value) { //2 matrix ou 2 valeurs

		if (!Permanencier::id_exists($_SESSION['benevole']['id_benevole_filiale'])) {
			die();
		}

		global $dbh;

		$tmp_old_transport = new Transport($this->get_id());

		$sql = "UPDATE transport ";

		if (is_array($attr) && is_array($new_value)) { //2 tableaux receptionnes
			$nbre_attribut = count($attr);
			$nbre_new_value = count($new_value);

			if ($nbre_attribut != $nbre_new_value) {
				return FALSE;
			}

			$sql .= "SET ";

			foreach ($attr as $index=>$attribut_to_edit)  {
				if (is_numeric($new_value[$index])) {
					$n_value = $new_value[$index];
				} elseif( is_array($new_value[$index])) {
					$n_value = serialize($new_value[$index]);
					$n_value = $dbh->quote($n_value);
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

		//si le point de depart ou d'arrivee a ete modifie, il est necessaire d'editer distance + cout
		if ($this->point_depart != $tmp_old_transport->point_depart || $this->point_arrivee != $tmp_old_transport->point_arrivee || $this->duree_approximative != $tmp_old_transport->duree_approximative) {
			$this->updateDistanceAndCost();
		}



//  Essai de message pour la mise à jour ( M. Thevoz 30 Jan 2012 )
//  Pas de message si le transporteur n'est pas défini

        if ($this->id_transporteur == 0 ) {
			return ;
		}

		if ($this->is_annule == 0 ) {
            $this->envoyer_email_update_chauffeur();
		}

//  fin du code pour l'essai d'envoi email poue une mis à jour du transport


	} // class.Transport.func.editerAttributs


	public function updateDistanceAndCost() {
		global $dbh;

		//calcul des distances
		$load_needed_class_and_interface = load_class_and_interface(array('Trajet_Pre_Defini'));

		$tmp_filiale = new Filiale($_SESSION['filiale']['id']);
		$find_distance = FALSE;

		//si les km ne sont pas recu en argument, calculer la distance par google maps
		if (checkInternetConnection('maps.google.com')) {

			$this->nbre_kilometres = Trajet_Pre_Defini::download_distance_from_google_maps($this->point_depart['adresse'], $this->point_depart['npa'], $this->point_depart['ville'], $this->point_depart['pays'], $this->point_arrivee['adresse'], $this->point_arrivee['npa'], $this->point_arrivee['ville'], $this->point_arrivee['pays']);
			$this->nbre_kilometres = ceil(2*$this->nbre_kilometres); //compte tjr aller-retour

			$find_distance = TRUE;

		} else {
			if ($this->point_depart['ville'] == $this->point_arrivee['ville']) {
				$nbre_kilometres = ceil($tmp_filiale->get_standard_prix_forfait_min() / $tmp_filiale->get_standard_prix_km()) ;
				$find_distance = TRUE;
			} else {
				$distance_trajet_pre_defini = Trajet_Pre_Defini::find_combination($this->point_depart['ville'], $this->point_arrivee['ville']);
				if ($distance_trajet_pre_defini && is_numeric($distance_trajet_pre_defini['distance'])) {
					$this->nbre_kilometres = ceil(2*$distance_trajet_pre_defini['distance']);
					$find_distance = TRUE;
				} else {
					//impossible de connaitre la distance
				}
			}
		}


		//calcul des couts
		if ($find_distance === TRUE) {
			$arrondi = 0.10;

			$this->cout_trajet = (round((1/$arrondi)* $tmp_filiale->get_standard_prix_km() * $this->nbre_kilometres))/(1/$arrondi); //arrondir a 0.10

			if ($this->cout_trajet < $tmp_filiale->get_standard_prix_forfait_min()) {
				$this->cout_trajet = $tmp_filiale->get_standard_prix_forfait_min();
			}

			if ($this->duree_approximative > 2) {
				$this->cout_trajet = 2 * $this->cout_trajet;
			}



			$sql = "UPDATE transport ";
			$sql .= " SET nbre_kilometres=" . $this->nbre_kilometres . ", cout_trajet=" . $this->cout_trajet;

			if ($this->taux_remboursement_transporteur == 0) {
				$this->taux_remboursement_transporteur = $tmp_filiale->get_standard_taux_compenation();
				$sql .= ", taux_remboursement_transporteur=" . $this->taux_remboursement_transporteur;
			}

			$sql .= " WHERE id=" . $this->get_id();

			$status_query = $dbh->exec($sql);
			return $statut_query;
		}

	}

	public function editerAttributsTransporteur($attr, $new_value) { //2 matrix ou 2 valeurs

		if (!Permanencier::id_exists($_SESSION['benevole']['id_benevole_filiale'])) {
			die();
		}

		global $dbh;

		$sql = "UPDATE transport_transporteur ";

		if (is_array($attr) && is_array($new_value)) { //2 tableaux receptionnes
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

		} elseif (!is_array($attr) && !is_array($new_value)) { //1 seule valeur $attr=>$new_value receptionnee
			if (!is_numeric($new_value)) {
				$new_value = $dbh->quote($new_value);
			}

			$sql .= "SET $attr=$new_value ";

		} else {
			die();
		}

		$sql .= "WHERE id_transport=" . $this->id;
		$statut_query = $dbh->exec($sql);

		//recharge avec les nouvelles donnees
		$this->mountTransporteur();
	} // class.Transport.func.editerAttributsTransporteur


	public function SupprimerChauffeur() {

		if (Benevole::id_benevole_filiale_exists($_SESSION['benevole']['id_benevole_filiale'])) {
			$tmp_benevole = new Benevole(Benevole::get_super_id_benvole_from_id_benevole_filiale($_SESSION['benevole']['id_benevole_filiale']));

			if ($tmp_benevole->checkIsSuperAdmin()) {
				// continue l'execution de la function
			} else {
				if (Filiale::id_exists($_SESSION['filiale']['id'])) {
					if ($tmp_benevole->checkIsPermanencier($_SESSION['filiale']['id']) || $tmp_benevole->checkIsAdminOfFiliale($_SESSION['filiale']['id'])) {
						$id_filiale = $_SESSION['filiale']['id'];
						$tmp_filiale = new Filiale($id_filiale);
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


		global $dbh;

		$sql = "DELETE FROM transport_transporteur ";
		$sql .= " WHERE id_transport=" . $this->get_id();

		$status_query = $dbh->exec($sql);
		return $status_query;
	}


	public function prevenir_chauffeur_email_annulation() {

		$id_transporteur = Transport::check_already_find_transporteur($this->get_id());

		if ($id_transporteur) {

			$tmp_transporteur = new Transporteur($id_transporteur);

			if ($tmp_transporteur->has_email()) {
				$tmp_transporteur_email = $tmp_transporteur->get_email();

				$tmp_filiale = new Filiale($_SESSION['filiale']['id']);

				$html_email = '';

				$tmp_benevole_nom_complet = $tmp_transporteur->get_nom_complet();

				$tmp_beneficiaire = new Beneficiaire($this->get_id_beneficiaire());
				$tmp_beneficiaire_nom_complet = $tmp_beneficiaire->get_nom_complet();
				$tmp_beneficiaire_tel = $tmp_beneficiaire->get_telephone();

				$point_depart = $this->get_point_depart();
				$point_arrivee = $this->get_point_arrivee();

				$html_email = '';
				$html_email .= '<p>';

					if (mb_strtoupper($tmp_benevole_nom_complet['titre']) == 'MADAME' || mb_strtoupper($tmp_benevole_nom_complet['titre']) == 'MADEMOISELLE') {
						$html_email .= 'Chère';
					} else {
						$html_email .= 'Cher';
					}

					$html_email .= ' ' . ucfirst($tmp_benevole_nom_complet['titre']) . ' ' . $tmp_benevole_nom_complet['nom'] . ', un de vos transports a été annulé.';



				$html_email .= 'Il s\'agit de : </p>';


				$html_email .= '<table>';
					$html_email .= '<thead>';
						$html_email .= '<tr>';
							$html_email .= '<th>Date</th>';
							$html_email .= '<th>Heure RDV</th>';
							$html_email .= '<th>Passager</th>';
							$html_email .= '<th>Tél. fixe</th>';
							$html_email .= '<th>Tél. mobile</th>';
							$html_email .= '<th>Départ</th>';
							$html_email .= '<th>Destination</th>';
						$html_email .= '</tr>';
					$html_email .= '</thead>';


					$html_email .= '<tbody>';

						$html_email .= '<td>';
							$html_email .= date_yyyymmdd_to_ddmmyyyy($this->get_date());
						$html_email .= '</td>';

						$html_email .= '<td>';
							$html_email .= time_hhmmss_to_hhmm($this->get_time());
						$html_email .= '</td>';

						$html_email .= '<td>';
							$html_email .= format_titre($tmp_beneficiaire_nom_complet['titre']) . ' ' . mb_strtoupper(stripAccents($tmp_beneficiaire_nom_complet['nom']));
						$html_email .= '</td>';

						$html_email .= '<td>';
							$html_email .= format_tel($tmp_beneficiaire_tel['tel_fixe']);
						$html_email .= '</td>';

						$html_email .= '<td>';
							$html_email .= format_tel($tmp_beneficiaire_tel['tel_mobile']);
						$html_email .= '</td>';

						$html_email .= '<td>';
							$html_email .= mb_strtoupper(stripAccents($point_depart['ville']));
						$html_email .= '</td>';

						$html_email .= '<td>';
							$html_email .= mb_strtoupper(stripAccents($point_arrivee['ville']));
						$html_email .= '</td>';


					$html_email .= '</tbody>';
				$html_email .= '</table>';



				global $dbh;

				//rappel des prochains transport
				$sql = "SELECT transport_transporteur.*, transport.* ";
				$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport ";
				$sql .= " WHERE transport_transporteur.id_transporteur=" . $id_transporteur;
				$sql .= " AND transport.is_annule=0";
				$sql .= " AND transport.date_transport>'" . date('Y-m-d') . "'";
				$sql .= " ORDER BY date_transport, heure_debut";

				$sth = $dbh->query($sql);
				$result = $sth->fetchAll(PDO::FETCH_ASSOC);

				if (count($result) > 0) {
					$html_email .= '<h2>Rappel des prochains transports</h2>';

					$html_email .= '<table>';
					$html_email .= '<thead>';
					$html_email .= '<tr>';
					$html_email .= '<th>Date</th>';
					$html_email .= '<th>Heure</th>';
					$html_email .= '<th>Passager</th>';
					$html_email .= '<th>Tél. fixe</th>';
					$html_email .= '<th>Tél. mobile</th>';
					$html_email .= '<th>Départ</th>';
					$html_email .= '<th>Destination</th>';
					$html_email .= '</tr>';
					$html_email .= '</thead>';

					$html_email .= '<tbody>';

					foreach($result as $row) {
						$html_email .= '<tr>';

						$html_email .= '<td>';
						$html_email .= date_yyyymmdd_to_ddmmyyyy($row['date_transport']);
						$html_email .= '</td>';

						$html_email .= '<td>';
						$html_email .= time_hhmmss_to_hhmm($row['heure_debut']);
						$html_email .= '</td>';

						$tmp_beneficiaire = new Beneficiaire($row['id_beneficiaire']);
						$tmp_beneficiaire_nom_complet = $tmp_beneficiaire->get_nom_complet();
						$tmp_beneficiaire_tel = $tmp_beneficiaire->get_telephone();

						$html_email .= '<td>';
						$html_email .= format_titre($tmp_beneficiaire_nom_complet['titre']) . ' ' . mb_strtoupper(stripAccents($tmp_beneficiaire_nom_complet['nom']));
						$html_email .= '</td>';

						$html_email .= '<td>';
						$html_email .= format_tel($tmp_beneficiaire_tel['tel_fixe']);
						$html_email .= '</td>';

						$html_email .= '<td>';
						$html_email .= format_tel($tmp_beneficiaire_tel['tel_mobile']);
						$html_email .= '</td>';

						$point_depart = unserialize($row['point_depart']);
						$point_arrivee = unserialize($row['point_arrivee']);

						$html_email .= '<td>';
						$html_email .= mb_strtoupper(stripAccents($point_depart['ville']));
						$html_email .= '</td>';

						$html_email .= '<td>';
						$html_email .= mb_strtoupper(stripAccents($point_arrivee['ville']));
						$html_email .= '</td>';
						$html_email .= '</tr>';
					}

					$html_email .= '</tbody>';
					$html_email .= '</table>';
				}


				//proposition d'autres transports
				$load_needed_class_and_interface = load_class_and_interface(array('GLMM'));

				$tmp_glmm = new GLMM($id_transporteur, 3, FALSE);
				$list_transports_potentiels = $tmp_glmm->get_transports_potentiels();

				$tmp_filiale = new Filiale($_SESSION['filiale']['id']);


				if (count($list_transports_potentiels) > 0) {


					$html_email .= '<h3>Vous pourriez également être intéressé(e) par l\'un de ces transports. Il suffit de le confirmer à la permanence au ' . format_tel($tmp_filiale->get_tel_permanence()) . '</h3>';

					$html_email .= '<table>';
					$html_email .= '<thead>';
					$html_email .= '<tr>';
					$html_email .= '<th>Date</th>';
					$html_email .= '<th>Heure</th>';
					$html_email .= '<th>Passager</th>';
					$html_email .= '<th>Départ</th>';
					$html_email .= '<th>Arrivée</th>';
					$html_email .= '</tr>';
					$html_email .= '</thead>';

					$html_email .= '<tbody>';


					foreach($list_transports_potentiels as $transport_potentiel) {
						$html_email .= '<tr>';

						$html_email .= '<td>';
						$html_email .= date_yyyymmdd_to_ddmmyyyy($transport_potentiel->get_date());
						$html_email .= '</td>';

						$html_email .= '<td>';
						$html_email .= time_hhmmss_to_hhmm($transport_potentiel->get_time());
						$html_email .= '</td>';

						$tmp_passager = new Beneficiaire($transport_potentiel->get_id_beneficiaire());
						$tmp_passager_nom = $tmp_passager->get_nom_complet();

						$html_email .= '<td>';
						$html_email .= format_titre($tmp_passager_nom['titre']) . ' ' . mb_strtoupper(stripAccents($tmp_passager_nom['nom']));
						$html_email .= '</td>';

						$point_depart = $transport_potentiel->get_point_depart();
						$point_arrivee = $transport_potentiel->get_point_arrivee();

						$html_email .= '<td>';
						$html_email .= mb_strtoupper(stripAccents($point_depart['ville']));
						$html_email .= '</td>';

						$html_email .= '<td>';
						$html_email .= mb_strtoupper(stripAccents($point_arrivee['ville']));
						$html_email .= '</td>';


						$html_email .= '</tr>';
					}

					$html_email .= '</tbody>';
					$html_email .= '</table>';

					$html_email .= '<br /><br />';
				}


				$tmp_permanencier = new Benevole($_SESSION['benevole']['id']);
				$tmp_permanencier_nom_complet = $tmp_permanencier->get_nom_complet();

				$html_email .= '<p>';
				$html_email .= 'Avec nos salutations les meilleures.<br />' . $tmp_permanencier_nom_complet['prenom'] . ' ' .  mb_strtoupper(stripAccents($tmp_permanencier_nom_complet['nom']));
				$html_email .= '</p>';


				$filiale_footer = $tmp_filiale->get_facture_footer();

				$html_email .= '<small><strong>' . $filiale_footer[0] . '</strong></small><br />';
				$html_email .= '<small>' . $filiale_footer[1] . '</small>';


				$html_email .= '<small>';
				$html_email .='Ce message électronique est susceptible de contenir des informations CONFIDENTIELLES ou de NATURE PRIVILEGIEE. Il est destiné exclusivement aux personnes auxquelles il est adressé. L’utilisation, la diffusion, la copie ou le transfert non autorisés sont strictement interdits. Si vous avez reçu ce message par erreur, merci de le retourner à son émetteur et de supprimer toutes les copies du message.';
				$html_email .= '</small>';

				$html_email .= '<br />';

				$html_email .= '<small>';
				$html_email .= 'This email communication may contain CONFIDENTIAL, PRIVILEGED and/or LEGALLY PROTECTED information and is intended only for the named recipient(s). Any unauthorized use, dissemination, copying or forwarding is strictly prohibited. If you are not the intended recipient and have received this email communication in error, please notify the sender immediately, delete it and destroy all copies of this email.';
				$html_email .= '</small>';



				load_class_and_interface(array('Rmail', 'PHPMailer'));

				global $cfg;

				//$mail = new Rmail();
				//$mail->setHTMLCharset('UTF-8');
				//$mail->setTextCharset('UTF-8');
				//$mail->setHeadCharset('UTF-8');
				//$mail->setFrom($tmp_filiale->get_nom() . ' <' . $tmp_filiale->get_email_permanence() . '>');
				//$mail->setSubject('Annulation d\'un transport');
				//$mail->setPriority('high');
				//$mail->setText('Annulation d\'un transport');

				//$mail->setHTML($html_email);

				//$mail->setReceipt($tmp_filiale->get_email_permanence());
				//$result  = $mail->send(array($tmp_transporteur_email));

				$mail = new PHPMailer;
				$mail->IsSMTP();
				$mail->Host = $cfg['MAILSERVER']['ip'];
				$mail->From = $tmp_filiale->get_email_permanence();
				$mail->FromName = $tmp_filiale->get_nom();
				$mail->AddAddress($tmp_transporteur_email);
				$mail->AddReplyTo($tmp_filiale->get_email_permanence(), $tmp_filiale->get_nom());
				$mail->WordWrap = 50;
				$mail->IsHTML(true);
				$mail->Subject = utf8_decode('Annulation d\'un transport');
				$mail->Body    = utf8_decode($html_email);
				$mail->Send();

				return $tmp_transporteur_email;

			}

		} else {
			return FALSE;
		}


	}

	public function envoyer_email_chauffeur() {

		$id_transporteur = $this->get_id_filiale_transporteur();

		if (!Transporteur::id_exists($id_transporteur)) {
			exit();
		}

		$tmp_benevole = new Benevole(Benevole::get_super_id_benvole_from_id_benevole_filiale($id_transporteur));
		$tmp_benevole_nom_complet = $tmp_benevole->get_nom_complet();

		$email = $tmp_benevole->has_email();

		if ($email && $this->get_date() >= date('Y-m-d')) {

			$tmp_beneficiaire = new Beneficiaire($this->get_id_beneficiaire());
			$tmp_beneficiaire_nom_complet = $tmp_beneficiaire->get_nom_complet();
			$tmp_beneficiaire_tel = $tmp_beneficiaire->get_telephone();

			$point_depart = $this->get_point_depart();
			$point_arrivee = $this->get_point_arrivee();

			$html_email = '';
			$html_email .= '<p>';

				if (mb_strtoupper($tmp_benevole_nom_complet['titre']) == 'MADAME' || mb_strtoupper($tmp_benevole_nom_complet['titre']) == 'MADEMOISELLE') {
					$html_email .= 'Chère';
				} else {
					$html_email .= 'Cher';
				}

				$html_email .= ' ' . ucfirst($tmp_benevole_nom_complet['titre']) . ' ' . $tmp_benevole_nom_complet['nom'] . ', un nouveau transport vous a été attribué.';

			$html_email .= 'Il s\'agit de :</p>';


			$html_email .= '<table>';
				$html_email .= '<thead>';
					$html_email .= '<tr>';
						$html_email .= '<th>Date</th>';
						$html_email .= '<th>Heure RDV</th>';
						$html_email .= '<th>Passager</th>';
						$html_email .= '<th>Tél. fixe</th>';
						$html_email .= '<th>Tél. mobile</th>';
						$html_email .= '<th>Départ</th>';
						$html_email .= '<th>Destination</th>';
					$html_email .= '</tr>';
				$html_email .= '</thead>';


				$html_email .= '<tbody>';

					$html_email .= '<td>';
						$html_email .= date_yyyymmdd_to_ddmmyyyy($this->get_date());
					$html_email .= '</td>';

					$html_email .= '<td>';
						$html_email .= time_hhmmss_to_hhmm($this->get_time());
					$html_email .= '</td>';

					$html_email .= '<td>';
						$html_email .= format_titre($tmp_beneficiaire_nom_complet['titre']) . ' ' . mb_strtoupper(stripAccents($tmp_beneficiaire_nom_complet['nom']));
					$html_email .= '</td>';

					$html_email .= '<td>';
						if ( isset($tmp_beneficiaire_tel['tel_fixe']) ) {
							$html_email .= format_tel($tmp_beneficiaire_tel['tel_fixe']);
						}
					$html_email .= '</td>';

					$html_email .= '<td>';
						if ( isset($tmp_beneficiaire_tel['tel_mobile']) ) {
							$html_email .= format_tel($tmp_beneficiaire_tel['tel_mobile']);
						}
					$html_email .= '</td>';

					$html_email .= '<td>';
						$html_email .= mb_strtoupper(stripAccents($point_depart['ville']));
					$html_email .= '</td>';

					$html_email .= '<td>';
						$html_email .= mb_strtoupper(stripAccents($point_arrivee['ville']));
					$html_email .= '</td>';


				$html_email .= '</tbody>';
			$html_email .= '</table>';

	/*
	 * Add Info Diverses Field from bénéficiaire if this one exist
	 * Suppress br  to setup all informations on the same line
	 *
	 * M. Thevoz 23 Janvier 2012
	 *
	 */
            $tmp_beneficiaire_info = $tmp_beneficiaire->get_info_div();

            if ($tmp_beneficiaire_info != '') {
			$html_email .= '<h3>Informations relatives au passager :</h3>';
			$html_email .= '<PRE>' . $tmp_beneficiaire_info . '</PRE>' ;
	        }

	/*
	 * Add Info Diverses Field from this trsnaport one exist
	 * Suppress br  to setup all informations on the same line
	 *
	 * M. Thevoz 23 Janvier 2012
	 *
	 */

			if ($this->info_diverses != '') {
				$html_email .= '<h3>Informations spécifiques à ce transport :</h3>';
				$html_email .= '<PRE>';
					$html_email .= $this->info_diverses;
				$html_email .= '</PRE>';
			}

			$html_email .= '<h3>Point de départ :</h3>';

			$html_email .= '<p>';
				if ($point_depart['type'] == 'beneficiaire') {

					$html_email .= '<a href="http://maps.google.com/maps?f=q&source=s_q&hl=en&geocode=&q=' . str_replace(' ', '+', stripAccents($point_depart['adresse'])) . ',' . stripAccents($point_depart['ville']) . ',Switzerland">';
						$html_email .= 'Domicile du passager'  . '&nbsp;&nbsp;' ;
					$html_email .= '</a>';


					$html_email .= $point_depart['adresse'] . '&nbsp;&nbsp;' ;

					if ( isset($point_depart['adresse_complement']) &&  $point_depart['adresse_complement'] != '' ) {
						$html_email .= $point_depart['adresse_complement'] . '&nbsp;&nbsp;' ;
					}

					$html_email .= $point_depart['npa'] . ' ' . $point_depart['ville'] . '&nbsp;&nbsp;' ;

				} elseif ($point_depart['type'] == 'lieu') {

					if (isset($point_depart['adresse']) && isset($point_depart['ville'])) {
						$html_email .= '<a href="http://maps.google.com/maps?f=q&source=s_q&hl=en&geocode=&q=' . str_replace(' ', '+', stripAccents($point_depart['adresse'])) . ',' . stripAccents($point_depart['ville']) . ',Switzerland">';
							$html_email .= $point_depart['nom_complet'] . '&nbsp;&nbsp;' ;						$html_email .= '</a>';
					} else {
						$html_email .= $point_depart['nom_complet'] . '&nbsp;&nbsp;' ;
					}


					if (mb_strtoupper(stripAccents($point_depart['nom_complet'])) == mb_strtoupper(stripAccents($point_depart['ville']))) {

					} else {
						if (isset($point_depart['adresse'])) {
							$html_email .= $point_depart['adresse'] . '&nbsp;&nbsp;' ;
						}

						if (isset($point_depart['adresse_complement'])) {
							$html_email .= $point_depart['adresse_complement'] . '&nbsp;&nbsp;' ;
						}

						if (isset($point_depart['npa'])) {
							$html_email .= $point_depart['npa'] . ' ';
						}


						$html_email .= $point_depart['ville'];
					}
				}
			$html_email .= '</p>';


			$html_email .= '<h3>Point d\'arrivée :</h3>';

			$html_email .= '<p>';
				if ($point_arrivee['type'] == 'beneficiaire') {

					$html_email .= '<a href="http://maps.google.com/maps?f=q&source=s_q&hl=en&geocode=&q=' . str_replace(' ', '+', stripAccents($point_arrivee['adresse'])) . ',' . stripAccents($point_arrivee['ville']) . ',Switzerland">';
						$html_email .= 'Domicile du passager'  . '&nbsp;&nbsp;' ;
					$html_email .= '</a>';

					$html_email .= $point_arrivee['adresse'] . '&nbsp;&nbsp;' ;

					if ($point_arrivee['adresse_complement'] != '') {
						$html_email .= $point_arrivee['adresse_complement'] . '&nbsp;&nbsp;' ;
					}

					$html_email .= $point_arrivee['npa'] . ' ' . $point_arrivee['ville'] . '&nbsp;&nbsp;' ;

				} elseif ($point_arrivee['type'] == 'lieu') {

					if (isset($point_arrivee['adresse']) && isset($point_arrivee['ville'])) {
						$html_email .= '<a href="http://maps.google.com/maps?f=q&source=s_q&hl=en&geocode=&q=' . str_replace(' ', '+', stripAccents($point_arrivee['adresse'])) . ',' . stripAccents($point_arrivee['ville']) . ',Switzerland">';
							$html_email .= $point_arrivee['nom_complet'] . '&nbsp;&nbsp;' ;
						$html_email .= '</a>';
					} else {
						$html_email .= $point_arrivee['nom_complet'] . '&nbsp;&nbsp;' ;
					}

					if (mb_strtoupper(stripAccents($point_arrivee['nom_complet'])) == mb_strtoupper(stripAccents($point_arrivee['ville']))) {

					} else {
						if (isset($point_arrivee['adresse'])) {
							$html_email .= $point_arrivee['adresse'] . '&nbsp;&nbsp;' ;
						}

						if (isset($point_arrivee['adresse_complement'])) {
							$html_email .= $point_arrivee['adresse_complement'] . '&nbsp;&nbsp;' ;
						}

						if (isset($point_arrivee['npa'])) {
							$html_email .= $point_arrivee['npa'] . ' ';
						}

						$html_email .= $point_arrivee['ville'];
					}
				}
			$html_email .= '</p>';

			global $dbh;

			//rappel des prochains transport
			$sql = "SELECT transport_transporteur.*, transport.* ";
			$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport ";
			$sql .= " WHERE transport_transporteur.id_transporteur=" . $id_transporteur;
			$sql .= " AND transport.is_annule=0";
			$sql .= " AND transport.date_transport>'" . date('Y-m-d') . "'";
			$sql .= " ORDER BY date_transport, heure_debut";

			$sth = $dbh->query($sql);
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);

			if (count($result) > 0) {
				$html_email .= '<h2>Rappel des prochains transports</h2>';

				$html_email .= '<table>';
					$html_email .= '<thead>';
						$html_email .= '<tr>';
							$html_email .= '<th>Date</th>';
							$html_email .= '<th>Heure</th>';
							$html_email .= '<th>Passager</th>';
							$html_email .= '<th>Tél. fixe</th>';
							$html_email .= '<th>Tél. mobile</th>';
							$html_email .= '<th>Départ</th>';
							$html_email .= '<th>Destination</th>';
						$html_email .= '</tr>';
					$html_email .= '</thead>';

					$html_email .= '<tbody>';

						foreach($result as $row) {
							$html_email .= '<tr>';

								$html_email .= '<td>';
									$html_email .= date_yyyymmdd_to_ddmmyyyy($row['date_transport']);
								$html_email .= '</td>';

								$html_email .= '<td>';
									$html_email .= time_hhmmss_to_hhmm($row['heure_debut']);
								$html_email .= '</td>';

								$tmp_beneficiaire = new Beneficiaire($row['id_beneficiaire']);
								$tmp_beneficiaire_nom_complet = $tmp_beneficiaire->get_nom_complet();
								$tmp_beneficiaire_tel = $tmp_beneficiaire->get_telephone();

								$html_email .= '<td>';
									$html_email .= format_titre($tmp_beneficiaire_nom_complet['titre']) . ' ' . mb_strtoupper(stripAccents($tmp_beneficiaire_nom_complet['nom']));
								$html_email .= '</td>';

								$html_email .= '<td>';
									if ( isset($tmp_beneficiaire_tel['tel_fixe']) ) {
										$html_email .= format_tel($tmp_beneficiaire_tel['tel_fixe']);
									}
								$html_email .= '</td>';

								$html_email .= '<td>';
									if ( isset($tmp_beneficiaire_tel['tel_mobile']) ) {
										$html_email .= format_tel($tmp_beneficiaire_tel['tel_mobile']);
									}
								$html_email .= '</td>';

								$point_depart = unserialize($row['point_depart']);
								$point_arrivee = unserialize($row['point_arrivee']);

								$html_email .= '<td>';
									$html_email .= mb_strtoupper(stripAccents($point_depart['ville']));
								$html_email .= '</td>';

								$html_email .= '<td>';
									$html_email .= mb_strtoupper(stripAccents($point_arrivee['ville']));
								$html_email .= '</td>';
							$html_email .= '</tr>';
						}

					$html_email .= '</tbody>';
				$html_email .= '</table>';
			}


			//proposition d'autres transports
			$load_needed_class_and_interface = load_class_and_interface(array('GLMM'));

			$tmp_glmm = new GLMM($id_transporteur, 3, FALSE);
			$list_transports_potentiels = $tmp_glmm->get_transports_potentiels();

			$tmp_filiale = new Filiale($_SESSION['filiale']['id']);


			if (count($list_transports_potentiels) > 0) {


				$html_email .= '<h3>Vous pourriez également être intéressé(e) par l\'un de ces transports. Il suffit de le confirmer à la permanence au ' . format_tel($tmp_filiale->get_tel_permanence()) . '</h3>';

				$html_email .= '<table>';
					$html_email .= '<thead>';
						$html_email .= '<tr>';
							$html_email .= '<th>Date</th>';
							$html_email .= '<th>Heure</th>';
							$html_email .= '<th>Passager</th>';
							$html_email .= '<th>Départ</th>';
							$html_email .= '<th>Arrivée</th>';
						$html_email .= '</tr>';
					$html_email .= '</thead>';

					$html_email .= '<tbody>';


						foreach($list_transports_potentiels as $transport_potentiel) {
							$html_email .= '<tr>';

								$html_email .= '<td>';
									$html_email .= date_yyyymmdd_to_ddmmyyyy($transport_potentiel->get_date());
								$html_email .= '</td>';

								$html_email .= '<td>';
									$html_email .= time_hhmmss_to_hhmm($transport_potentiel->get_time());
								$html_email .= '</td>';

								$tmp_passager = new Beneficiaire($transport_potentiel->get_id_beneficiaire());
								$tmp_passager_nom = $tmp_passager->get_nom_complet();

								$html_email .= '<td>';
									$html_email .= format_titre($tmp_passager_nom['titre']) . ' ' . mb_strtoupper(stripAccents($tmp_passager_nom['nom']));
								$html_email .= '</td>';

								$point_depart = $transport_potentiel->get_point_depart();
								$point_arrivee = $transport_potentiel->get_point_arrivee();

								$html_email .= '<td>';
									$html_email .= mb_strtoupper(stripAccents($point_depart['ville']));
								$html_email .= '</td>';

								$html_email .= '<td>';
									$html_email .= mb_strtoupper(stripAccents($point_arrivee['ville']));
								$html_email .= '</td>';


							$html_email .= '</tr>';
						}

					$html_email .= '</tbody>';
				$html_email .= '</table>';

			}


			$tmp_permanencier = new Benevole($_SESSION['benevole']['id']);
			$tmp_permanencier_nom_complet = $tmp_permanencier->get_nom_complet();

			$html_email .= '<p>';
				$html_email .= 'Avec nos salutations les meilleures.<br />' . $tmp_permanencier_nom_complet['prenom'] . ' ' .  mb_strtoupper(stripAccents($tmp_permanencier_nom_complet['nom']));
			$html_email .= '</p>';

			$filiale_footer = $tmp_filiale->get_facture_footer();

			$html_email .= '<small><strong>' . $filiale_footer[0] . '</strong></small><br />';
			$html_email .= '<small>' . $filiale_footer[1] . '</small>';


			$html_email .= '<small>';
				$html_email .='Ce message électronique est susceptible de contenir des informations CONFIDENTIELLES ou de NATURE PRIVILEGIEE. Il est destiné exclusivement aux personnes auxquelles il est adressé. L’utilisation, la diffusion, la copie ou le transfert non autorisés sont strictement interdits. Si vous avez reçu ce message par erreur, merci de le retourner à son émetteur et de supprimer toutes les copies du message.';
			$html_email .= '</small>';

			$html_email .= '<br />';

			$html_email .= '<small>';
				$html_email .= 'This email communication may contain CONFIDENTIAL, PRIVILEGED and/or LEGALLY PROTECTED information and is intended only for the named recipient(s). Any unauthorized use, dissemination, copying or forwarding is strictly prohibited. If you are not the intended recipient and have received this email communication in error, please notify the sender immediately, delete it and destroy all copies of this email.';
			$html_email .= '</small>';

			global $cfg;
			load_class_and_interface(array('Rmail', 'PHPMailer'));
			//$mail = new Rmail();
			//$mail->setHTMLCharset('UTF-8');
			//$mail->setTextCharset('UTF-8');
			//$mail->setHeadCharset('UTF-8');
			//$mail->setFrom($tmp_filiale->get_nom() . ' <' . $tmp_filiale->get_email_permanence() . '>');
			//$mail->setSubject('Nouveau transport');
			//$mail->setPriority('high');
			//$mail->setText('Nouveau transport');

			//$mail->setHTML($html_email);

			//$mail->setReceipt($tmp_filiale->get_email_permanence());
			//$result  = $mail->send(array($email));

			$mail = new PHPMailer;
			$mail->IsSMTP();
			$mail->Host = $cfg['MAILSERVER']['ip'];
			$mail->From = $tmp_filiale->get_email_permanence();
			$mail->FromName = $tmp_filiale->get_nom();
			$mail->AddAddress($email);
			$mail->AddReplyTo($tmp_filiale->get_email_permanence(), $tmp_filiale->get_nom());
			$mail->WordWrap = 50;
			$mail->IsHTML(true);
			$mail->Subject = utf8_decode('Nouveau transport');
			$mail->Body    = utf8_decode($html_email);
			$mail->Send();

		}

	} // class.Transport.func.envoyer_email_chauffeur

// Debut  Email à envoyer en cas de mise à jour du transport
// Copie customisée de envoyer_email_chauffeur ajouteé par M. Thevoz 30 Janvier 2012

	public function envoyer_email_update_chauffeur() {

		$id_transporteur = $this->get_id_filiale_transporteur();

		if (!Transporteur::id_exists($id_transporteur)) {
			exit() ;
		}

		$tmp_benevole = new Benevole(Benevole::get_super_id_benvole_from_id_benevole_filiale($id_transporteur));
		$tmp_benevole_nom_complet = $tmp_benevole->get_nom_complet();

		$email = $tmp_benevole->has_email();

		if ($email && $this->get_date() >= date('Y-m-d')) {

			$tmp_beneficiaire = new Beneficiaire($this->get_id_beneficiaire());
			$tmp_beneficiaire_nom_complet = $tmp_beneficiaire->get_nom_complet();
			$tmp_beneficiaire_tel = $tmp_beneficiaire->get_telephone();

			$point_depart = $this->get_point_depart();
			$point_arrivee = $this->get_point_arrivee();

			$html_email = '';
			$html_email .= '<p>';

				if (mb_strtoupper($tmp_benevole_nom_complet['titre']) == 'MADAME' || mb_strtoupper($tmp_benevole_nom_complet['titre']) == 'MADEMOISELLE') {
					$html_email .= 'Chère';
				} else {
					$html_email .= 'Cher';
				}

				$html_email .= ' ' . ucfirst($tmp_benevole_nom_complet['titre']) . ' ' . $tmp_benevole_nom_complet['nom'] . ', un transport déjà attribué vient d\'être modifié.';

			$html_email .= 'Il s\'agit de :</p>';


			$html_email .= '<table>';
				$html_email .= '<thead>';
					$html_email .= '<tr>';
						$html_email .= '<th>Date</th>';
						$html_email .= '<th>Heure RDV</th>';
						$html_email .= '<th>Passager</th>';
						$html_email .= '<th>Tél. fixe</th>';
						$html_email .= '<th>Tél. mobile</th>';
						$html_email .= '<th>Départ</th>';
						$html_email .= '<th>Destination</th>';
					$html_email .= '</tr>';
				$html_email .= '</thead>';


				$html_email .= '<tbody>';

					$html_email .= '<td>';
						$html_email .= date_yyyymmdd_to_ddmmyyyy($this->get_date());
					$html_email .= '</td>';

					$html_email .= '<td>';
						$html_email .= time_hhmmss_to_hhmm($this->get_time());
					$html_email .= '</td>';

					$html_email .= '<td>';
						$html_email .= format_titre($tmp_beneficiaire_nom_complet['titre']) . ' ' . mb_strtoupper(stripAccents($tmp_beneficiaire_nom_complet['nom']));
					$html_email .= '</td>';

					$html_email .= '<td>';
						if ( isset($tmp_beneficiaire_tel['tel_fixe']) ) {
							$html_email .= format_tel($tmp_beneficiaire_tel['tel_fixe']);
						}
					$html_email .= '</td>';

					$html_email .= '<td>';
						if ( isset($tmp_beneficiaire_tel['tel_mobile']) ) {
							$html_email .= format_tel($tmp_beneficiaire_tel['tel_mobile']);
						}
					$html_email .= '</td>';

					$html_email .= '<td>';
						$html_email .= mb_strtoupper(stripAccents($point_depart['ville']));
					$html_email .= '</td>';

					$html_email .= '<td>';
						$html_email .= mb_strtoupper(stripAccents($point_arrivee['ville']));
					$html_email .= '</td>';


				$html_email .= '</tbody>';
			$html_email .= '</table>';

	/*
	 * Add Info Diverses Field from bénéficiaire if this one exist
	 * Suppress br  to setup all informations on the same line
	 *
	 * M. Thevoz 23 Janvier 2012
	 *
	 */
            $tmp_beneficiaire_info = $tmp_beneficiaire->get_info_div();

            if ($tmp_beneficiaire_info != '') {
			$html_email .= '<h3>Informations relatives au passager :</h3>';
			$html_email .= '<PRE>' . $tmp_beneficiaire_info . '</PRE>' ;
	        }

	/*
	 * Add Info Diverses Field from this trsnaport one exist
	 * Suppress br  to setup all informations on the same line
	 *
	 * M. Thevoz 23 Janvier 2012
	 *
	 */

			if ($this->info_diverses != '') {
				$html_email .= '<h3>Informations spécifiques à ce transport :</h3>';
				$html_email .= '<PRE>';
					$html_email .= $this->info_diverses;
				$html_email .= '</PRE>';
			}

			$html_email .= '<h3>Point de départ :</h3>';

			$html_email .= '<p>';
				if ($point_depart['type'] == 'beneficiaire') {

					$html_email .= '<a href="http://maps.google.com/maps?f=q&source=s_q&hl=en&geocode=&q=' . str_replace(' ', '+', stripAccents($point_depart['adresse'])) . ',' . stripAccents($point_depart['ville']) . ',Switzerland">';
						$html_email .= 'Domicile du passager'  . '&nbsp;&nbsp;' ;
					$html_email .= '</a>';


					$html_email .= $point_depart['adresse'] . '&nbsp;&nbsp;' ;

					if ( isset($point_depart['adresse_complement']) && $point_depart['adresse_complement'] != '') {
						$html_email .= $point_depart['adresse_complement'] . '&nbsp;&nbsp;' ;
					}

					$html_email .= $point_depart['npa'] . ' ' . $point_depart['ville'] . '&nbsp;&nbsp;' ;

				} elseif ($point_depart['type'] == 'lieu') {

					if (isset($point_depart['adresse']) && isset($point_depart['ville'])) {
						$html_email .= '<a href="http://maps.google.com/maps?f=q&source=s_q&hl=en&geocode=&q=' . str_replace(' ', '+', stripAccents($point_depart['adresse'])) . ',' . stripAccents($point_depart['ville']) . ',Switzerland">';
							$html_email .= $point_depart['nom_complet'] . '&nbsp;&nbsp;' ;						$html_email .= '</a>';
					} else {
						$html_email .= $point_depart['nom_complet'] . '&nbsp;&nbsp;' ;
					}


					if (mb_strtoupper(stripAccents($point_depart['nom_complet'])) == mb_strtoupper(stripAccents($point_depart['ville']))) {

					} else {
						if (isset($point_depart['adresse'])) {
							$html_email .= $point_depart['adresse'] . '&nbsp;&nbsp;' ;
						}

						if (isset($point_depart['adresse_complement'])) {
							$html_email .= $point_depart['adresse_complement'] . '&nbsp;&nbsp;' ;
						}

						if (isset($point_depart['npa'])) {
							$html_email .= $point_depart['npa'] . ' ';
						}


						$html_email .= $point_depart['ville'];
					}
				}
			$html_email .= '</p>';


			$html_email .= '<h3>Point d\'arrivée :</h3>';

			$html_email .= '<p>';
				if ($point_arrivee['type'] == 'beneficiaire') {

					$html_email .= '<a href="http://maps.google.com/maps?f=q&source=s_q&hl=en&geocode=&q=' . str_replace(' ', '+', stripAccents($point_arrivee['adresse'])) . ',' . stripAccents($point_arrivee['ville']) . ',Switzerland">';
						$html_email .= 'Domicile du passager'  . '&nbsp;&nbsp;' ;
					$html_email .= '</a>';

					$html_email .= $point_arrivee['adresse'] . '&nbsp;&nbsp;' ;

					if ($point_arrivee['adresse_complement'] != '') {
						$html_email .= $point_arrivee['adresse_complement'] . '&nbsp;&nbsp;' ;
					}

					$html_email .= $point_arrivee['npa'] . ' ' . $point_arrivee['ville'] . '&nbsp;&nbsp;' ;

				} elseif ($point_arrivee['type'] == 'lieu') {

					if (isset($point_arrivee['adresse']) && isset($point_arrivee['ville'])) {
						$html_email .= '<a href="http://maps.google.com/maps?f=q&source=s_q&hl=en&geocode=&q=' . str_replace(' ', '+', stripAccents($point_arrivee['adresse'])) . ',' . stripAccents($point_arrivee['ville']) . ',Switzerland">';
							$html_email .= $point_arrivee['nom_complet'] . '&nbsp;&nbsp;' ;
						$html_email .= '</a>';
					} else {
						$html_email .= $point_arrivee['nom_complet'] . '&nbsp;&nbsp;' ;
					}

					if (mb_strtoupper(stripAccents($point_arrivee['nom_complet'])) == mb_strtoupper(stripAccents($point_arrivee['ville']))) {

					} else {
						if (isset($point_arrivee['adresse'])) {
							$html_email .= $point_arrivee['adresse'] . '&nbsp;&nbsp;' ;
						}

						if (isset($point_arrivee['adresse_complement'])) {
							$html_email .= $point_arrivee['adresse_complement'] . '&nbsp;&nbsp;' ;
						}

						if (isset($point_arrivee['npa'])) {
							$html_email .= $point_arrivee['npa'] . ' ';
						}

						$html_email .= $point_arrivee['ville'];
					}
				}
			$html_email .= '</p>';

			global $dbh;

			//rappel des prochains transport
			$sql = "SELECT transport_transporteur.*, transport.* ";
			$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport ";
			$sql .= " WHERE transport_transporteur.id_transporteur=" . $id_transporteur;
			$sql .= " AND transport.is_annule=0";
			$sql .= " AND transport.date_transport>'" . date('Y-m-d') . "'";
			$sql .= " ORDER BY date_transport, heure_debut";

			$sth = $dbh->query($sql);
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);

			if (count($result) > 0) {
				$html_email .= '<h2>Rappel des prochains transports</h2>';

				$html_email .= '<table>';
					$html_email .= '<thead>';
						$html_email .= '<tr>';
							$html_email .= '<th>Date</th>';
							$html_email .= '<th>Heure</th>';
							$html_email .= '<th>Passager</th>';
							$html_email .= '<th>Tél. fixe</th>';
							$html_email .= '<th>Tél. mobile</th>';
							$html_email .= '<th>Départ</th>';
							$html_email .= '<th>Destination</th>';
						$html_email .= '</tr>';
					$html_email .= '</thead>';

					$html_email .= '<tbody>';

						foreach($result as $row) {
							$html_email .= '<tr>';

								$html_email .= '<td>';
									$html_email .= date_yyyymmdd_to_ddmmyyyy($row['date_transport']);
								$html_email .= '</td>';

								$html_email .= '<td>';
									$html_email .= time_hhmmss_to_hhmm($row['heure_debut']);
								$html_email .= '</td>';

								$tmp_beneficiaire = new Beneficiaire($row['id_beneficiaire']);
								$tmp_beneficiaire_nom_complet = $tmp_beneficiaire->get_nom_complet();
								$tmp_beneficiaire_tel = $tmp_beneficiaire->get_telephone();

								$html_email .= '<td>';
									$html_email .= format_titre($tmp_beneficiaire_nom_complet['titre']) . ' ' . mb_strtoupper(stripAccents($tmp_beneficiaire_nom_complet['nom']));
								$html_email .= '</td>';

								$html_email .= '<td>';
									if ( isset($tmp_beneficiaire_tel['tel_fixe']) ) {
										$html_email .= format_tel($tmp_beneficiaire_tel['tel_fixe']);
									}
								$html_email .= '</td>';

								$html_email .= '<td>';
									if ( isset($tmp_beneficiaire_tel['tel_mobile']) ) {
										$html_email .= format_tel($tmp_beneficiaire_tel['tel_mobile']);
									}
								$html_email .= '</td>';

								$point_depart = unserialize($row['point_depart']);
								$point_arrivee = unserialize($row['point_arrivee']);

								$html_email .= '<td>';
									$html_email .= mb_strtoupper(stripAccents($point_depart['ville']));
								$html_email .= '</td>';

								$html_email .= '<td>';
									$html_email .= mb_strtoupper(stripAccents($point_arrivee['ville']));
								$html_email .= '</td>';
							$html_email .= '</tr>';
						}

					$html_email .= '</tbody>';
				$html_email .= '</table>';
			}


			//proposition d'autres transports
			$load_needed_class_and_interface = load_class_and_interface(array('GLMM'));

			$tmp_glmm = new GLMM($id_transporteur, 3, FALSE);
			$list_transports_potentiels = $tmp_glmm->get_transports_potentiels();

			$tmp_filiale = new Filiale($_SESSION['filiale']['id']);


			if (count($list_transports_potentiels) > 0) {


				$html_email .= '<h3>Vous pourriez également être intéressé(e) par l\'un de ces transports. Il suffit de le confirmer à la permanence au ' . format_tel($tmp_filiale->get_tel_permanence()) . '</h3>';

				$html_email .= '<table>';
					$html_email .= '<thead>';
						$html_email .= '<tr>';
							$html_email .= '<th>Date</th>';
							$html_email .= '<th>Heure</th>';
							$html_email .= '<th>Passager</th>';
							$html_email .= '<th>Départ</th>';
							$html_email .= '<th>Arrivée</th>';
						$html_email .= '</tr>';
					$html_email .= '</thead>';

					$html_email .= '<tbody>';


						foreach($list_transports_potentiels as $transport_potentiel) {
							$html_email .= '<tr>';

								$html_email .= '<td>';
									$html_email .= date_yyyymmdd_to_ddmmyyyy($transport_potentiel->get_date());
								$html_email .= '</td>';

								$html_email .= '<td>';
									$html_email .= time_hhmmss_to_hhmm($transport_potentiel->get_time());
								$html_email .= '</td>';

								$tmp_passager = new Beneficiaire($transport_potentiel->get_id_beneficiaire());
								$tmp_passager_nom = $tmp_passager->get_nom_complet();

								$html_email .= '<td>';
									$html_email .= format_titre($tmp_passager_nom['titre']) . ' ' . mb_strtoupper(stripAccents($tmp_passager_nom['nom']));
								$html_email .= '</td>';

								$point_depart = $transport_potentiel->get_point_depart();
								$point_arrivee = $transport_potentiel->get_point_arrivee();

								$html_email .= '<td>';
									$html_email .= mb_strtoupper(stripAccents($point_depart['ville']));
								$html_email .= '</td>';

								$html_email .= '<td>';
									$html_email .= mb_strtoupper(stripAccents($point_arrivee['ville']));
								$html_email .= '</td>';


							$html_email .= '</tr>';
						}

					$html_email .= '</tbody>';
				$html_email .= '</table>';

			}


			$tmp_permanencier = new Benevole($_SESSION['benevole']['id']);
			$tmp_permanencier_nom_complet = $tmp_permanencier->get_nom_complet();

			$html_email .= '<p>';
				$html_email .= 'Avec nos salutations les meilleures.<br />' . $tmp_permanencier_nom_complet['prenom'] . ' ' .  mb_strtoupper(stripAccents($tmp_permanencier_nom_complet['nom']));
			$html_email .= '</p>';

			$filiale_footer = $tmp_filiale->get_facture_footer();

			$html_email .= '<small><strong>' . $filiale_footer[0] . '</strong></small><br />';
			$html_email .= '<small>' . $filiale_footer[1] . '</small>';


			$html_email .= '<small>';
				$html_email .='Ce message électronique est susceptible de contenir des informations CONFIDENTIELLES ou de NATURE PRIVILEGIEE. Il est destiné exclusivement aux personnes auxquelles il est adressé. L’utilisation, la diffusion, la copie ou le transfert non autorisés sont strictement interdits. Si vous avez reçu ce message par erreur, merci de le retourner à son émetteur et de supprimer toutes les copies du message.';
			$html_email .= '</small>';

			$html_email .= '<br />';

			$html_email .= '<small>';
				$html_email .= 'This email communication may contain CONFIDENTIAL, PRIVILEGED and/or LEGALLY PROTECTED information and is intended only for the named recipient(s). Any unauthorized use, dissemination, copying or forwarding is strictly prohibited. If you are not the intended recipient and have received this email communication in error, please notify the sender immediately, delete it and destroy all copies of this email.';
			$html_email .= '</small>';

			global $cfg;
			load_class_and_interface(array('Rmail', 'PHPMailer'));
			//$mail = new Rmail();
			//$mail->setHTMLCharset('UTF-8');
			//$mail->setTextCharset('UTF-8');
			//$mail->setHeadCharset('UTF-8');
			//$mail->setFrom($tmp_filiale->get_nom() . ' <' . $tmp_filiale->get_email_permanence() . '>');
			//$mail->setSubject('Mise à jour d\'un transport attribué');
			//$mail->setPriority('high');
			//$mail->setText('Mise à jour d\'un transport attribué');

			//$mail->setHTML($html_email);

			//$mail->setReceipt($tmp_filiale->get_email_permanence());
			//$result  = $mail->send(array($email));

			$mail = new PHPMailer;
			$mail->IsSMTP();
			$mail->Host = $cfg['MAILSERVER']['ip'];
			$mail->From = $tmp_filiale->get_email_permanence();
			$mail->FromName = $tmp_filiale->get_nom();
			$mail->AddAddress($email);
			$mail->AddReplyTo($tmp_filiale->get_email_permanence(), $tmp_filiale->get_nom());
			$mail->WordWrap = 50;
			$mail->IsHTML(true);
			$mail->Subject = utf8_decode('Mise à jour d\'un transport attribué');
			$mail->Body    = utf8_decode($html_email);
			$mail->Send();

		}

	} // class.Transport.func.envoyer_email_update_chauffeur



// Fin de la fonction envoyer en cas de mise à jour du transport
// Ajouté par M. Thevoz 30 Janvier 2012


	private function mountBeneficiaire() {
		if (Beneficiaire::id_exists($this->id_beneficiaire)) {
			$this->beneficiaire = new Beneficiaire($this->id_beneficiaire);
		}
	} // class.Transport.func.mountBeneficiaire


	private function mountFiliale() {
		if (Filiale::id_exists($this->id_filiale)) {
			$this->filiale = new Filiale($this->id_filiale);
		}
	} // class.Transport.func.mountFiliale


	private function mountCategorie() {
		if(Transport_Categorie::id_exists($this->id_categorie)) {
			$this->categorie = new Transport_Categorie($this->id_categorie);
		}
	} // class.Transport.func.mountCategorie


	private function mountTypeCalculDistance() {
		if(Transport_Type_Calcul_Distance::id_exists($this->id_type_calcul_distance)) {
			$this->type_calcul_distance = new Transport_Type_Calcul_Distance($this->id_type_calcul_distance);
		}
	} // class.Transport.func.mountTypeCalculDistance

	private function mountTransporteur() {

		$this->id_transporteur = Transport::check_already_find_transporteur($this->id);

		if ($this->id_transporteur) {
			$this->transporteur = new Transporteur($this->id_transporteur);
		}
	}


	public static function check_already_find_transporteur($id_transport) {
		global $dbh;

		if (is_numeric($id_transport) && Transport::id_exists($id_transport)) {
			global $dbh;

			$sql = "SELECT id_transporteur FROM transport_transporteur ";
			$sql .= " WHERE id_transport=" . $id_transport;

			$sth = $dbh->query($sql);

			$result = $sth->fetch(PDO::FETCH_ASSOC);

			if ($result != false ) {
				return $result['id_transporteur'];
			} else {
				return FALSE;
			}


		} else {
			return FALSE;
		}
	}


	public static function get_transports_sans_chauffeur() {
		global $dbh;

		$sql = "SELECT transport.id ";
		$sql .= " FROM transport ";
		$sql .= " WHERE transport.id NOT IN ( ";

			$sql .= "SELECT transport.id";
			$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport";

		$sql .= " )";

		$sql .= " AND transport.id_filiale=" . $_SESSION['filiale']['id'] . " ";
		$sql .= " AND transport.date_transport>='" . date('Y-m-d') . "' ";
		$sql .= " AND transport.is_annule=0 ";
		$sql .= " ORDER BY transport.date_transport, transport.heure_debut";

		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		if (count($result) > 0) {

			$list_transport = array();

			foreach ($result as $row) {
				$list_transport[] = new Transport($row['id']);
			}

			return $list_transport;

		} else {
			return FALSE;
		}

	} // class.Transport.function.get_transports_sans_chauffeur


	public static function get_transport_avec_chauffeurs() {
		global $dbh;

		$sql = "SELECT transport.id ";
		$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport ";
		$sql .= " WHERE transport.id_filiale=" . $_SESSION['filiale']['id'] . " ";
		$sql .= " AND transport.date_transport>='" . date('Y-m-d') . "' ";
		$sql .= " AND transport.is_annule=0 ";
		$sql .= " ORDER BY transport.date_transport, transport.heure_debut";

		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		if (count($result)) {

			$list_transport = array();

			foreach ($result as $row) {
				$list_transport[] = new Transport($row['id']);
			}

			return $list_transport;
		} else {
			return FALSE;
		}
	}


	public function get_id() {
		return $this->id;
	}


	public function get_id_beneficiaire() {
		return $this->id_beneficiaire;
	}

	public function get_super_id_transporteur() {
		$this->mountTransporteur();

		return $this->transporteur->get_id();
	}

	public function get_id_filiale_transporteur() {
		$this->mountTransporteur();

		return $this->transporteur->get_id_transporteur();
	}

	public function get_id_filiale() {
		return $this->id_filiale;
	}


	public function get_date() {
		return $this->date_transport;
	}

	public function get_time() {
		return $this->heure_debut;
	}

	public function get_duree() {
		return $this->duree_approximative;
	}


	public function get_aller_retour() {
		return $this->aller_retour;
	}


	public function is_aller_retour() {
		if ($this->aller_retour == 1) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function get_id_categorie() {
		return $this->id_categorie;
	}


	public function get_point_depart() {
		return $this->point_depart;
	}

	public function get_point_arrivee() {
		return $this->point_arrivee;
	}

	public function get_nbre_kilometres() {
		return $this->nbre_kilometres;
	}

	public function get_cout_trajet() {
		return $this->cout_trajet;
	}

	public function get_taux_remboursement_transporteur() {
		return $this->taux_remboursement_transporteur;
	}


	public function get_jour_semaine() {
		return date('N', strtotime($this->date_transport));
	}


	public function get_periode_journee() {
		return Periode_Journee::get_id_periode_from_time($this->heure_debut);
	}


	public function get_infos_complementaires() {
		if ($this->info_diverses != '') {
			return $this->info_diverses;
		} else {
			return FALSE;
		}
	}



	public function checkLongDistance_Geneve() {

		$array_ville_depart = $this->get_point_depart();
		$array_ville_arrivee = $this->get_point_arrivee();

		if (mb_strtoupper(stripAccents($array_ville_depart['ville'])) == 'GENEVE' || mb_strtoupper(stripAccents($array_ville_arrivee['ville'])) == 'GENEVE') {
			return TRUE;
		} else {
			if ((($this->nbre_kilometres)/2) > 15 && (($this->nbre_kilometres)/2) < 33) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	} // class.Transport.func.checkLongDistance_Geneve


	public function checkLongDistance_Lausanne() {

		$array_ville_depart = $this->get_point_depart();
		$array_ville_arrivee = $this->get_point_arrivee();

			if ((($this->nbre_kilometres)/2) > 32) {
				return TRUE;
			} else {
				return FALSE;
			}
	} // class.Transport.func.checkLongDistance_Lausanne


	public static function id_exists($id_to_check) {
		if (checkID($id_to_check)) {
			global $dbh;
			$sql = "SELECT * FROM transport WHERE id=" .$id_to_check;
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
	} //class.Transport.func.id_exists


	private function return_pair_key_value() {

		$tmp_array['id']['value']= $this->id;
		$tmp_array['id_beneficiaire']['value'] = $this->id_beneficiaire;
		$tmp_array['id_transporteur']['value'] = $this->id_transporteur;
		$tmp_array['id_categorie']['value'] = $this->id_categorie;
		$tmp_array['date_transport']['value'] = $this->date_transport;
		$tmp_array['heure_debut']['value'] = $this->heure_debut;
		$tmp_array['duree_approximative']['value'] = $this->duree_approximative;
		$tmp_array['point_depart']['value'] = $this->point_depart; //array -> tester le type pour domicile ou lieu
		$tmp_array['point_arrivee']['value'] = $this->point_arrivee; // array
		$tmp_array['nbre_kilometres']['value'] = $this->nbre_kilometres;
		$tmp_array['aller_retour']['value'] = $this->aller_retour;
		$tmp_array['cout_trajet']['value'] = $this->cout_trajet;
		$tmp_array['cout_variable']['value'] = $this->cout_variable;
		$tmp_array['taux_remboursement_transporteur']['value'] = $this->taux_remboursement_transporteur;
		$tmp_array['info_diverses']['value'] = $this->info_diverses;

		return $tmp_array;
	} // class.Transport.func.return_pair_key_value


	public static function form($action, $data_to_display='') {

		if (is_array($data_to_display)) {

		} elseif (is_numeric($data_to_display) && Transport::id_exists($data_to_display)) {
			//numero de beneficaire
			$tmp_transport = new Transport($data_to_display);
			unset($data_to_display);
			$data_to_display = $tmp_transport->return_pair_key_value();
		} elseif ($data_to_display instanceof Transport) {
			//convertir en un tableau data_to_display_habituel
			$data_to_display = $data_to_display->return_pair_key_value();
		} else {
			$data_to_display = array();
		}

		switch ($action) {
			case "add":
				echo Transport::form_base($action, $data_to_display);
				break;
			case "view":
				//s'assure que le transport est connu sinon charge une listbox de selection
				if (isset($data_to_display['id']['value']) && Transport::id_exists($data_to_display['id']['value'])) {
					echo Transport::form_view($action, $data_to_display);
				} else {
					echo Transport::form_list($action);
				}

				break;
			case "edit":
				//s'assure que le beneficiaire est connu sinon charge une listbox de selection
				if (isset($data_to_display['id']['value']) && Transport::id_exists($data_to_display['id']['value'])) {
					echo Transport::form_base($action, $data_to_display);
				} else {
					//echo Transport::form_list($action, $data_to_display);
				}

				break;
			case "list":
				echo Transport::form_list($action);
				break;
			case "find_driver":
				echo Transport::form_find_driver($action, $data_to_display);
				break;
			case "archive":
				echo Transport::form_archive($action, $data_to_display);
				break;
			default:
				echo Transport::form_list($action);
				break;
		}

	} // class.Transport.func.form

	/**
	 *
	 * Fonction qui produit le code HTML pour les actions add & edit
	 *
	 */
	private static function form_base($action, $data_to_display='') {
		//retourne le code html du formulaire
		unset($_POST);
		global $dbh;

		if (!isset($_SESSION['last_page']['facturation_month'])) {
			unset($_SESSION['last_page']);
			$_SESSION['last_page']['module'] = 'transport';
			$_SESSION['last_page']['action'] = $action;
		}

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
			$html_code .= '<h2>' . $action_title . ' un transport</h2>';
		}



		$html_code .= '<form class="disable_submit_form" id="transport_' . $action . '" action="" method="post">';
			// transport regulier
				if ($action == 'add' && isset($_SESSION['last_transport']['id_beneficiaire']) && Beneficiaire::id_exists($_SESSION['last_transport']['id_beneficiaire'])) {
					$tmp_beneficiaire = new Beneficiaire($_SESSION['last_transport']['id_beneficiaire']);
					$tmp_beneficiaire_nom = $tmp_beneficiaire->get_nom_complet();


					$html_code .= '<fieldset id="transport_regulier">';
						$html_code .= '<legend>Transport régulier</legend>';

						$html_code .= 'Pour créer un nouveau transport pour le même passager, il suffit de cliquer sur le lien si-dessous pour pré-remlir automatiquement les cases de ce formulaire, sinon passez à la case suivante (choix du passager) <strong>sans vous préoccuper</strong> du contenu de cette boîte';


						$html_code .= '<p>';
							$html_code .= '<a href="?module=transport&amp;action=new_like_last">';
								$html_code .= 'Nouveau transport pour ' . format_titre($tmp_beneficiaire_nom['titre']) . ' ' . $tmp_beneficiaire_nom['prenom'] . ' ' . mb_strtoupper(stripAccents($tmp_beneficiaire_nom['nom']));
							$html_code .= '</a>';
						$html_code .= '</p>';

					$html_code .= '</fieldset>';

				}



			$html_code .= '<fieldset id="transport_beneficiaire">';
				$html_code .= '<legend>Passager</legend>';

				$html_code .= '<p>';

					$html_code .= '<label for="id_beneficiaire">Passager</label>';

					$sql = "SELECT id, nom, prenom FROM beneficiaire ORDER BY nom";
					$sth = $dbh->query($sql);
					$result = $sth->fetchAll(PDO::FETCH_ASSOC);

					$html_code .= '<select id="id_beneficiaire" name="id_beneficiaire" class="required">';

						$html_code .= '<option></option>'; //champs vide

						foreach ($result as $row) {
							$html_code .= '<option value="' . $row['id'] .'" ';

							if (isset($data_to_display['id_beneficiaire']['value']) && $data_to_display['id_beneficiaire']['value'] == $row['id'] ) {
								$html_code .= 'selected="selected">';
							} else {
								$html_code .= '>';
							}

							$html_code .= mb_strtoupper(stripAccents($row['nom'])) . ', ' . $row['prenom'];
							$html_code .= '</option>';
						}

					$html_code .= '</select>';

					$html_code .= '<a href="?module=beneficiaire&amp;action=add">';
						$html_code .= 'Ajouter un <em>nouveau</em> passager car il n\'est <strong>pas présent</strong> dans la liste';
					$html_code .= '</a>';


				$html_code .= '</p>';

				$html_code .= '<div id="details_beneficiaire">';

				$html_code .= '</div>';

			$html_code .= '</fieldset>';

			$html_code .= '<fieldset id="date_heure">';
				$html_code .= '<legend>Date & Heure</legend>';

				$html_code .= '<p>';
					$html_code .= '<label for="date_transport">Date</label>';
						if ( isset($data_to_display['date_transport']['value']) == false ) {
							$html_code .= add_FormElement_input('text', 'date_transport', array('date_picker', 'disableAutoComplete', 'required'),  '');
						} else {
							if (is_date($data_to_display['date_transport']['value'])) {
								$html_code .= add_FormElement_input('text', 'date_transport', array('date_picker', 'disableAutoComplete', 'required'),  date('d.m.Y', strtotime($data_to_display['date_transport']['value'])));
							} else {
								$html_code .= add_FormElement_input('text', 'date_transport', array('date_picker', 'disableAutoComplete', 'required'),  $data_to_display['date_transport']['value']);
							}
						}

				$html_code .= '</p>';

				$html_code .= '<div id="warning_beneficiaire_already_transport_same_date">';

				$html_code .= '</div>';


				//separation heures / minutes
				if (isset($data_to_display['heure_debut']['value']) && $data_to_display['heure_debut']['value'] != ':' && $data_to_display['heure_debut']['value'] !== false) {
					$data_to_display['heure_debut_heure']['value'] = substr($data_to_display['heure_debut']['value'], 0, (strpos($data_to_display['heure_debut']['value'], ':')));
					$data_to_display['heure_debut_minute']['value'] = substr($data_to_display['heure_debut']['value'], strpos($data_to_display['heure_debut']['value'], ':')+1, 2 );
				} else {
	// Force default hour to midnight to make the need for correction more visible !! M. Thevoz 9 Dec 2013
					$data_to_display['heure_debut_heure']['value'] = '00';
					$data_to_display['heure_debut_minute']['value'] = '00';
				}

				$html_code .= '<p>';
					$html_code .= '<label>Heure du rendez-vous à valider avec le passager SVP </label>';

					$html_code .= '<select id="heure_debut_heure" name="heure_debut_heure" class="required">';

						for ($i=0; $i<24; $i++) {

							if ($i < 10) {
								$value = '0' . $i;
							} else {
								$value = '' . $i;
							}
							$html_code .= '<option value="' . $i . '" ';

								if ($value == $data_to_display['heure_debut_heure']['value']) {
									$html_code .= 'selected="selected">';
								} else {
									$html_code .= '>';
								}


								$html_code .= $value;
							$html_code .= '</option>';
						}
					$html_code .= '</select>';

					$html_code .= '<select id="heure_debut_minute" name="heure_debut_minute" class="required">';

						for ($i=0; $i<60; $i=$i+15) {

							if ($i < 10) {
								$value = '0' . $i;
							} else {
								$value = '' . $i;
							}

							$html_code .= '<option value="' . $i . '" ';

								if ($value == $data_to_display['heure_debut_minute']['value']) {
									$html_code .= 'selected="selected">';
								} else {
									$html_code .= '>';
								}

								$html_code .= $value;
							$html_code .= '</option>';
						}
					$html_code .= '</select>';

				$html_code .= '</p>';

				$html_code .= '<p>';
					$html_code .= '<label for="duree_approximative">Durée approximative</label>';

					/*
					if (isset($data_to_display['duree_approximative']['value'])) {
						$html_code .= add_FormElement_input('text', 'duree_approximative', array('required'), $data_to_display['duree_approximative']['value']);
					} else {
						$html_code .= add_FormElement_input('text', 'duree_approximative', array('required'), 1);
					}
					*/

					$html_code .= '<select class="required" id="duree_approximative" name="duree_approximative">';

						//array de key=>value : label=>valeur numérique en base 10 d'heure : 45min -> 0.75 heure
						$duree_possibilites = array('30 minutes' => 0.5, '45 minutes' => 0.75, '1 heure' =>1, '1 heure et demie' => 1.5, '1 heure trois quarts' => 1.75, '2 heures' => 2, 'Plus de 2 heures' => 3);

						foreach ($duree_possibilites as $label => $value) {
							if (isset($data_to_display['duree_approximative']['value'])) {
								if ($data_to_display['duree_approximative']['value'] == $value) {
									$html_code .= '<option selected="selected" value="' . $value . '">' . $label . '</option>';
								} else {
									$html_code .= '<option value="' . $value . '">' . $label . '</option>';
								}
							} else {
								if ($value == 1) {
									$html_code .= '<option selected="selected" value="' . $value . '">' . $label . '</option>';
								} else {
									$html_code .= '<option value="' . $value . '">' . $label . '</option>';
								}
							}

						}

					$html_code .= '</select>';

				$html_code .= '</p>';

			$html_code .= '</fieldset>';


			//point départ
			$html_code .= '<fieldset id="transport_trajet">';

				$lieu_id_categorie_ville = Lieu_Categorie::get_id_from_categorie('ville');
				$last_ville = '';

				$html_code .= '<legend>Détails du trajet</legend>';

				$html_code .= '<p>';
					$html_code .= '<label for="point_depart">Point de départ</label>';

					$sql = "SELECT id, nom, ville, id_categorie ";
					$sql .= " FROM lieu ";
					$sql .= " ORDER BY ville, nom";

					$sth = $dbh->query($sql);
					$result = $sth->fetchAll(PDO::FETCH_ASSOC);

					$sql = "SELECT id, nom, ville, id_categorie ";
					$sql .= " FROM lieu ";
					$sql .= " WHERE id_categorie = " . $lieu_id_categorie_ville;
					$sql .= " ORDER BY ville, nom";

					$sth = $dbh->query($sql);
					$array_ville = $sth->fetchAll(PDO::FETCH_ASSOC);

					$html_code .= '<select id="point_depart" name="point_depart" class="required">';
						$html_code .= '<option value="0" ';

						if ( isset($data_to_display['point_depart']['value']['type']) ) {
							if ($data_to_display['point_depart']['value']['type'] == 'beneficiaire' ) {
								$html_code .= 'selected="selected">';
							} else {
								$html_code .= '>';
							}
						} else {
							$html_code .= '>';
						}
						 $html_code .= 'Domicile';
						 $html_code .= '</option>';

						foreach ($result as $row) {

							if ($last_ville != mb_strtoupper(stripAccents($row['ville']))) {

								//nouvelle ville et la nouvelle entrée est de type ville
								if ($row['id_categorie'] == $lieu_id_categorie_ville) {
									$html_code .= '<option value="' . $row['id'] .'" ';

									if ( isset($data_to_display['point_depart']['value']['type']) ) {

										if ($data_to_display['point_depart']['value']['type'] == 'lieu' && $data_to_display['point_depart']['value']['id'] == $row['id'] ) {
											$html_code .= 'selected="selected">';
										} else {
											$html_code .= '>';
										}
									} else {
										$html_code .= '>';
									}

										if (mb_strtolower(stripAccents($row['nom'])) != mb_strtolower(stripAccents($row['ville']))) {
											$html_code .= mb_strtoupper(stripAccents($row['ville'])) . ', ' . $row['nom'];
										} else {
											$html_code .= mb_strtoupper(stripAccents($row['ville']));
										}

										$last_ville = mb_strtoupper(stripAccents($row['ville']));

									$html_code .= '</option>';
								} else {
									// nouvelle ville mais la premiere entrée n'est pas du type ville, il faut fouiller dans la liste pour inserer l'entrée de la ville en première position
									foreach ($array_ville as $row_2) {
										if (mb_strtoupper(stripAccents($row_2['ville'])) == mb_strtoupper(stripAccents($row['ville'])) && $row_2['id_categorie'] == $lieu_id_categorie_ville ) {

											$html_code .= '<option value="' . $row_2['id'] .'" ';
												if (isset($data_to_display['point_depart']['value']['id'])) {
													if ($data_to_display['point_depart']['value']['type'] == 'lieu' && $data_to_display['point_depart']['value']['id'] == $row_2['id'] ) {
														$html_code .= 'selected="selected">';
													} else {
														$html_code .= '>';
													}
												} else {
													$html_code .= '>';
												}

												if (mb_strtolower(stripAccents($row_2['nom'])) != mb_strtolower(stripAccents($row_2['ville']))) {
													$html_code .= mb_strtoupper(stripAccents($row_2['ville'])) . ', ' . $row_2['nom'];
												} else {
													$html_code .= mb_strtoupper(stripAccents($row_2['ville']));
												}

												$last_ville = mb_strtoupper(stripAccents($row_2['ville']));

											$html_code .= '</option>';

											break;
										}
									}

									//puis insertion de l'entrée
									$html_code .= '<option value="' . $row['id'] .'" ';

									if ( isset($data_to_display['point_depart']['value']['type']) ) {
										if ($data_to_display['point_depart']['value']['type'] == 'lieu' && $data_to_display['point_depart']['value']['id'] == $row['id'] ) {
											$html_code .= 'selected="selected">';
										} else {
											$html_code .= '>';
										}
									} else {
										$html_code .= '>';
									}

										if (mb_strtolower(stripAccents($row['nom'])) != mb_strtolower(stripAccents($row['ville']))) {
											$html_code .= mb_strtoupper(stripAccents($row['ville'])) . ', ' . $row['nom'];
										} else {
											$html_code .= mb_strtoupper(stripAccents($row['ville']));
										}

										$last_ville = mb_strtoupper(stripAccents($row['ville']));

									$html_code .= '</option>';

								}
							} else {
								//la ville est identique mais il s'agit de l'entrée de type ville qui a deja du etre ajouter à la liste
								if ($row['id_categorie'] != $lieu_id_categorie_ville) {
									$html_code .= '<option value="' . $row['id'] .'" ';

									if ( isset($data_to_display['point_depart']['value']['type']) ) {

										if ($data_to_display['point_depart']['value']['type'] == 'lieu' && $data_to_display['point_depart']['value']['id'] == $row['id'] ) {
											$html_code .= 'selected="selected">';
										} else {
											$html_code .= '>';
										}

									} else {
										$html_code .= '>';
									}

									if (mb_strtolower(stripAccents($row['nom'])) != mb_strtolower(stripAccents($row['ville']))) {
										$html_code .= mb_strtoupper(stripAccents($row['ville'])) . ', ' . $row['nom'];
									} else {
										$html_code .= mb_strtoupper(stripAccents($row['ville']));
									}

									$html_code .= '</option>';
								}
							}

						}

					$html_code .= '</select>';


					$html_code .= '<a id="show_other_transport_point_depart" href="?module=lieu&action=add">';
						$html_code .= 'Autre lieu';
					$html_code .= '</a>';


				$html_code .= '</p>';


				$html_code .= '<div id="other_point_depart" class="other_transport_point">';
					$html_code .= '<p>';

						$html_code .= '<label for="other_point_depart_ville">Autre ville départ</label>';
						$html_code .= add_FormElement_input('text', 'other_point_depart_ville', array('input_ville', 'disableAutoComplete'), '');

						$html_code .= '<label for="other_point_depart_pays">Autre pays départ</label>';
						$html_code .= add_FormElement_input('text', 'other_point_depart_pays', array('input_ville', 'disableAutoComplete'), 'Suisse');

					$html_code .= '</p>';
				$html_code .= '</div>';





				//point d'arrivee
				$html_code .= '<p>';
					$html_code .= '<label for="point_arrivee">Point d\'arrivée</label>';

					$html_code .= '<select id="point_arrivee" name="point_arrivee" class="required">';
						$html_code .= '<option></option>';

						$html_code .= '<option value="0" ';

						if ( isset($data_to_display['point_arrivee']['value']['type']) ) {
							if ($data_to_display['point_arrivee']['value']['type'] == 'beneficiaire' ) {
								$html_code .= 'selected="selected">';
							} else {
								$html_code .= '>';
							}
						} else {
							$html_code .= '>';
						}
						 $html_code .= 'Domicile';
						 $html_code .= '</option>';

						foreach ($result as $row) {

							if ($last_ville != mb_strtoupper(stripAccents($row['ville']))) {

								//nouvelle ville et la nouvelle entrée est de type ville
								if ($row['id_categorie'] == $lieu_id_categorie_ville) {
									$html_code .= '<option value="' . $row['id'] .'" ';

									if ( isset($data_to_display['point_arrivee']['value']['type']) ) {
										if ($data_to_display['point_arrivee']['value']['type'] == 'lieu' && $data_to_display['point_arrivee']['value']['id'] == $row['id'] ) {
											$html_code .= 'selected="selected">';
										} else {
											$html_code .= '>';
										}
									} else {
										$html_code .= '>';
									}

										if (mb_strtolower(stripAccents($row['nom'])) != mb_strtolower(stripAccents($row['ville']))) {
											$html_code .= mb_strtoupper(stripAccents($row['ville'])) . ', ' . $row['nom'];
										} else {
											$html_code .= mb_strtoupper(stripAccents($row['ville']));
										}

										$last_ville = mb_strtoupper(stripAccents($row['ville']));

									$html_code .= '</option>';
								} else {
									// nouvelle ville mais la premiere entrée n'est pas du type ville, il faut fouiller dans la liste pour inserer l'entrée de la ville en première position
									foreach ($array_ville as $row_2) {
										if (mb_strtoupper(stripAccents($row_2['ville'])) == mb_strtoupper(stripAccents($row['ville'])) && $row_2['id_categorie'] == $lieu_id_categorie_ville ) {

											$html_code .= '<option value="' . $row_2['id'] .'" ';

											if ( isset($data_to_display['point_arrivee']['value']['id']) ) {
												if ($data_to_display['point_arrivee']['value']['type'] == 'lieu' && $data_to_display['point_arrivee']['value']['id'] == $row_2['id'] ) {
													$html_code .= 'selected="selected">';
												} else {
													$html_code .= '>';
												}
											} else {
												$html_code .= '>';
											}

												if (mb_strtolower(stripAccents($row_2['nom'])) != mb_strtolower(stripAccents($row_2['ville']))) {
													$html_code .= mb_strtoupper(stripAccents($row_2['ville'])) . ', ' . $row_2['nom'];
												} else {
													$html_code .= mb_strtoupper(stripAccents($row_2['ville']));
												}

												$last_ville = mb_strtoupper(stripAccents($row_2['ville']));

											$html_code .= '</option>';

											break;
										}
									}

									//puis insertion de l'entrée
									$html_code .= '<option value="' . $row['id'] .'" ';

									if ( isset($data_to_display['point_arrivee']['value']['id']) ) {
										if ($data_to_display['point_arrivee']['value']['type'] == 'lieu' && $data_to_display['point_arrivee']['value']['id'] == $row['id'] ) {
											$html_code .= 'selected="selected">';
										} else {
											$html_code .= '>';
										}
									} else {
										$html_code .= '>';
									}

										if (mb_strtolower(stripAccents($row['nom'])) != mb_strtolower(stripAccents($row['ville']))) {
											$html_code .= mb_strtoupper(stripAccents($row['ville'])) . ', ' . $row['nom'];
										} else {
											$html_code .= mb_strtoupper(stripAccents($row['ville']));
										}

										$last_ville = mb_strtoupper(stripAccents($row['ville']));

									$html_code .= '</option>';

								}
							} else {
								//la ville est identique mais il s'agit de l'entrée de type ville qui a deja du etre ajouter à la liste
								if ($row['id_categorie'] != $lieu_id_categorie_ville) {
									$html_code .= '<option value="' . $row['id'] .'" ';

									if (isset($data_to_display['point_arrivee']['value']['id'])) {
										if ($data_to_display['point_arrivee']['value']['type'] == 'lieu' && $data_to_display['point_arrivee']['value']['id'] == $row['id'] ) {
											$html_code .= 'selected="selected">';
										} else {
											$html_code .= '>';
										}
									} else {
										$html_code .= '>';
									}

									if (mb_strtolower(stripAccents($row['nom'])) != mb_strtolower(stripAccents($row['ville']))) {
										$html_code .= mb_strtoupper(stripAccents($row['ville'])) . ', ' . $row['nom'];
									} else {
										$html_code .= mb_strtoupper(stripAccents($row['ville']));
									}

									$html_code .= '</option>';
								}
							}

						}

					$html_code .= '</select>';

					$html_code .= '<a id="show_other_transport_point_arrivee" href="?module=lieu&action=add">';
						$html_code .= 'Autre lieu';
					$html_code .= '</a>';

				$html_code .= '</p>';


				$html_code .= '<div id="other_point_arrivee" class="other_transport_point">';
					$html_code .= '<p>';

						$html_code .= '<label for="other_point_arrivee_ville">Autre ville destination</label>';
						$html_code .= add_FormElement_input('text', 'other_point_arrivee_ville', array('input_ville', 'disableAutoComplete'), '');

						$html_code .= '<label for="other_point_arrivee_pays">Autre pays destination</label>';
						$html_code .= add_FormElement_input('text', 'other_point_arrivee_pays', array('input_ville', 'disableAutoComplete'), 'Suisse');

					$html_code .= '</p>';
				$html_code .= '</div>';




			$html_code .= '</fieldset>';

			$html_code .= '<fieldset id="transport_info_diverses">';
				$html_code .= '<legend>Informations diverses</legend>';

				//$html_code .= '<label for="info_diverses">Informations diverses</label>';
				$html_code .= '<textarea id="info_diverses" name="info_diverses" rows="3" cols="50">';
					if ( isset($data_to_display['info_diverses']['value']) ) {
						$html_code .= $data_to_display['info_diverses']['value'];
					}
				$html_code .= '</textarea>';

			$html_code .= '</fieldset>';


			$html_code .= '<fieldset id="particularites">';
				$html_code .= '<legend>Particularités</legend>';

				$html_code .= '<p>';

					$html_code .= '<label for="aller_retour">Type de transport</label>';

					$html_code .= '<select id="aller_retour" name="aller_retour" class="required">';
						$html_code .= '<option value="1" ';

						if (($action == 'add' && !isset($data_to_display['new_like_last']['value']) ) || $data_to_display['aller_retour']['value'] == 1 || ($data_to_display['new_like_last']['value'] === TRUE && $data_to_display['aller_retour']['value'] == 1 )) {
							$html_code .= 'selected="selected">';
						} else {
							$html_code .= '>';
						}

						$html_code .= 'Trajet aller-retour</options>';


						$html_code .= '<option value="0" ';

						if (($action != 'add' && $data_to_display['aller_retour']['value'] == 0) || (isset($data_to_display['new_like_last']['value']) && $data_to_display['new_like_last']['value'] === TRUE &&  $data_to_display['aller_retour']['value'] == 0 )) {
							$html_code .= 'selected="selected">';
						} else {
							$html_code .= '>';
						}

						$html_code .= 'Trajet simple</options>';

					$html_code .= '</select>';
				$html_code .= '</p>';

				$sql = "SELECT id, nom FROM transport_categorie ORDER BY nom";
				$sth = $dbh->query($sql);
				$result = $sth->fetchAll(PDO::FETCH_ASSOC);

				$html_code .= '<p>';
					$html_code .= '<label for="id_categorie">Raison du transport</label>';

					$html_code .= '<select id="id_categorie" name="id_categorie">';

						//par defaut consultation
						$id_categorie_consultation = Transport_Categorie::get_id_from_nom('consultation');

						foreach ($result as $row) {
							$html_code .= '<option value="' . $row['id'] . '" ';

							if ( isset($data_to_display['id_categorie']['value']) ) {
								if ($data_to_display['id_categorie']['value'] == $row['id']) {
									$html_code .= 'selected="selected">';
								}
							} else {
								if (!isset($data_to_display['id_categorie']['value']) || $data_to_display['id_categorie']['value'] == '') {

									if ($row['id'] == $id_categorie_consultation) {
										$html_code .= 'selected="selected">';
									} else {
										$html_code .= '>';
									}

								} else {
									$html_code .= '>';
								}
							}

							$html_code .= ucfirst($row['nom']);
							$html_code .= '</option>';
						}

					$html_code .= '</select>';

					/*
					$html_code .= '<a href="?module=transport_categorie&action=add">';
						$html_code .= 'Ajouter une catégorie';
					$html_code .= '</a>';
					*/

				$html_code .= '</p>';

			$html_code .= '</fieldset>';


			if ($action != 'add') {
				$load_needed_class_and_interface = load_class_and_interface(array('Filiale'));
				$tmp_filiale = new Filiale($_SESSION['filiale']['id']);
				$tmp_benevole = new Benevole($_SESSION['benevole']['id']);

				if ($tmp_benevole->checkIsSuperAdmin() || $tmp_benevole->checkIsAdminOfFiliale($_SESSION['filiale']['id'])) {
					$tmp_permanencier_admin_filiale = TRUE;
				} else {
					$tmp_permanencier_admin_filiale = FALSE;
				}

				$html_code .= '<fieldset id="transport_km_couts">';
					$html_code .= '<legend>Kilomètres & coûts</legend>';


					if ($tmp_permanencier_admin_filiale) {
						$html_code .= '<p>';
							$html_code .= '<label for="nbre_kilometres">Nombre de kilomètres (aller + retour)</label>';
								$html_code .= add_FormElement_input('text', 'nbre_kilometres', '', $data_to_display['nbre_kilometres']['value']) . 'Km';
						$html_code .= '</p>';
					}

					if ($tmp_permanencier_admin_filiale) {
						$html_code .= '<p>';
							$html_code .= '<label for="cout_trajet">Coût du trajet (' . number_format($tmp_filiale->get_standard_prix_km(), 2) . ' CHF/Km</label>';
								$html_code .= add_FormElement_input('text', 'cout_trajet', '', number_format($data_to_display['cout_trajet']['value'], 2));
						$html_code .= '</p>';
					}

					$html_code .= '<p>';
						$html_code .= '<label for="cout_variable">Coût variable (parking etc.)</label>';
							$html_code .= add_FormElement_input('text', 'cout_variable', '', number_format($data_to_display['cout_variable']['value'], 2));
					$html_code .= '</p>';

					if ($tmp_permanencier_admin_filiale) {
						$html_code .= '<p>';
							$html_code .= '<label for="taux_remboursement_transporteur">Taux de remboursement pour le chauffeur</label>';
								$html_code .= add_FormElement_input('text', 'taux_remboursement_transporteur', '', $data_to_display['taux_remboursement_transporteur']['value']) . '%';
						$html_code .= '</p>';
					}

				$html_code .= '</fieldset>';
			}


			$html_code .= '<fieldset>';
				$html_code .= '<legend>Chauffeur</legend>';
				$html_code .= '<p>';
					$html_code .= 'Si le chauffeur de ce transport est déjà connu, il est possible de l\'indiquer sinon laisser vide';
				$html_code .= '</p>';

				$sql = "SELECT benevole_participation_filiale.id, benevole.nom, benevole.prenom ";
				$sql .= " FROM benevole_participation_filiale INNER JOIN benevole ON benevole_participation_filiale.id_benevole = benevole.id ";
				$sql .= " WHERE benevole_participation_filiale.is_transporteur=1";
				$sql .= " ORDER BY benevole.nom, benevole.prenom";

				$sth = $dbh->query($sql);
				$result = $sth->fetchAll(PDO::FETCH_ASSOC);

				$html_code .= '<label for="id_transporteur">Chauffeur</label>';

				$html_code .= '<select id="id_transporteur" name="id_transporteur">';

					$html_code .= '<option></option>';

					foreach ($result as $row) {
						$html_code .= '<option value="' . $row['id'] . '" ';

						if ( isset($data_to_display['id_transporteur']['value']) ) {
							if ($data_to_display['id_transporteur']['value'] == $row['id']) {
								$html_code .= 'selected="selected">';
							} else {
								$html_code .= '>';
							}
						} else {
							$html_code .= '>';
						}

						$html_code .= mb_strtoupper(stripAccents($row['nom'])) . ', ' . $row['prenom'];
						$html_code .= '</option>';
					}

				$html_code .= '</select>';

			$html_code .= '</fieldset>';

			$html_code .= '<p>';

				if (isset($data_to_display['id']['value'])) {
					$html_code .= add_FormElement_input('hidden', 'id', '', $data_to_display['id']['value']);
				}

				$html_code .= add_FormElement_input('hidden', 'form', '', 'base');
				$html_code .= add_FormElement_input('hidden', 'module', '', 'transport');
				$html_code .= add_FormElement_input('hidden', 'sub_module', '', '""');
				$html_code .= add_FormElement_input('hidden', 'action', '', $action);

				if ($action == 'add') {
					$html_code .= '<input type="submit" value="Valider le transport et retour à l\'accueil" />';
				} elseif ($action == 'edit') {
					$html_code .= '<input type="submit" value="Valider les modifications du transport et retour à l\'accueil" />';
				} else {
					$html_code .= '<input type="submit" value="Soumettre" />';
				}

			$html_code .= '</p>';

		$html_code .= '</form>';

		$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));

		return $html_code;
	} //class.Transport.form.base


	private static function form_list($action) {
		//unset($_POST);
		global $dbh;

		$html_code = '';

		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}

		$weekdays[1] = 'lundi';
		$weekdays[2] = 'mardi';
		$weekdays[3] = 'mercredi';
		$weekdays[4] = 'jeudi';
		$weekdays[5] = 'vendredi';
		$weekdays[6] = 'samedi';
		$weekdays[7] = 'dimanche';


		//liste des dates pour la construction des liens de calendrier
		$sql = "SELECT DISTINCT date_transport ";
		$sql .= " FROM transport ";
		$sql .= " WHERE id_filiale=" . $_SESSION['filiale']['id'];
		$sql .= " AND is_annule=0";
		$sql .= " AND date_transport>=" . $dbh->quote(date('Y-m-d', strtotime(date('Y') . '-' . date('m') . '-01')));

		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		$dates_link = array();
		$distinct_months = array();
		$calendars = array();


		foreach ($result as $row) {
			$dates_link[] = $row['date_transport'];

			if (!in_array(date('m', strtotime($row['date_transport'])), $distinct_months)) {
				$distinct_months[] = date('m', strtotime($row['date_transport']));

				$calendars[] = array('month' => date('n', strtotime($row['date_transport'])), 'year' => date('Y', strtotime($row['date_transport'])) );
			}
		}



		$html_code .= '<div id="calendars_link" class="clear-after">';
			foreach ($calendars as $row) {
				$html_code .= calendrier($row['month'], $row['year'], $dates_link, $row['month'] . '-' . $row['year'],'link_date','', 'link_date' );
			}
		$html_code .= '</div>';




		$sql = "SELECT beneficiaire.*, transport_transporteur.*, transport.* ";
		$sql .= " FROM transport LEFT JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport, beneficiaire ";
		$sql .= " WHERE transport.id_beneficiaire = beneficiaire.id ";
		$sql .= " AND transport.id_filiale=" . $_SESSION['filiale']['id'];
		$sql .= " AND transport.is_annule=0";
		$sql .= " AND transport.date_transport>=" . $dbh->quote(date('Y-m-d', strtotime(date('Y') . '-' . date('m') . '-01')));
		$sql .= " ORDER BY date_transport, heure_debut";

		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);


		if (count($result) > 0) {
			$html_code .= '<table class="OddEven">';
				$html_code .= '<thead>';
					$html_code .= '<tr>';
						$html_code .= '<th>Date &amp; Heure</th>';
						$html_code .= '<th>Passager</th>';
						$html_code .= '<th>Transporteur</th>';
						$html_code .= '<th>Départ</th>';
						$html_code .= '<th>Arrivée</th>';
						$html_code .= '<th>Type</th>'; // Aller-retour?
						$html_code .= '<th></th>'; // Editer
						$html_code .= '<th></th>'; // Annuler
					$html_code .= '</tr>';
				$html_code .= '</thead>';

				$html_code .= '<tbody>';
					$last_date_txt = '';
					foreach ($result as $row) {
						if ($last_date_txt != $row['date_transport']) {

							$weekday = date('N', strtotime($row['date_transport']));

							foreach($weekdays as $idx_day => $day) {
								if ($idx_day == $weekday) {
									$txt_weekday = $day;
									break;
								}
							}

							$html_code .= '<tr>';
								if ($row['date_transport'] == date('Y-m-d')) {
									$html_code .= '<th><a name="' . $row['date_transport'] . '"><a class="header_date_today" href="#top">' . date_yyyymmdd_to_ddmmyyyy($row['date_transport']) . ' - ' . substr($txt_weekday, 0, 3) . '</a></th>';
								} else {
									$html_code .= '<th><a name="' . $row['date_transport'] . '"><a class="header_date" href="#top">' . date_yyyymmdd_to_ddmmyyyy($row['date_transport']) . ' - ' . substr($txt_weekday, 0, 3) . '</a></th>';
								}

							$html_code .= '</tr>';

							$last_date_txt = $row['date_transport'];
						}

						$html_code .= '<tr>';

							$html_code .= '<td>';
								$html_code .= '<a href="?module=transport&amp;action=view&amp;id=' . $row['id'] . '">';
									$html_code .= time_hhmmss_to_hhmm($row['heure_debut']);
								$html_code .= '</a>';
							$html_code .= '</td>';

							$tmp_beneficiaire = new Beneficiaire($row['id_beneficiaire']);
							$tmp_beneficiaire_tel = $tmp_beneficiaire->get_telephone();

							$tel_string = '';
							foreach ($tmp_beneficiaire_tel as $type_tel => $tel) {
								$tel_string .= str_replace('tel_', '', $type_tel) . ' : ' . format_tel($tel) . '   ';
							}

							$html_code .= '<td title="' . $tel_string . '">';
								$html_code .= '<a class="link_dialog" href="?module=beneficiaire&amp;action=view&amp;id=' . $row['id_beneficiaire'] . '">';
								$html_code .= mb_strtoupper(stripAccents($row['nom'])) . ', ' . $row['prenom'];
								$html_code .= '</a>';
							$html_code .= '</td>';

							$html_code .= '<td>';
								if (is_numeric($row['id_transporteur'])) {
									//un chauffeur a deja ete trouve
									$tmp_transporteur = new Transporteur($row['id_transporteur']);
									$tmp_transporteur_nom_complet = $tmp_transporteur->get_nom_complet();
									$tmp_transporteur_tel = $tmp_transporteur->get_telephone();

									$tel_string = '';
									foreach ($tmp_transporteur_tel as $type_tel => $tel) {
										$tel_string .= str_replace('tel_', '', $type_tel) . ' : ' . format_tel($tel) . '   ';
									}


									$html_code .= '<a title="' . $tel_string . '" class="link_dialog" href="?module=benevole&amp;action=view&amp;id=' . $row['id_transporteur']. '">';
									$html_code .= mb_strtoupper(stripAccents($tmp_transporteur_nom_complet['nom'])) . ', ' . $tmp_transporteur_nom_complet['prenom'];
									$html_code .= '</a>';
								} else {
									$html_code .= '<a href="?module=transport&amp;action=find_driver&amp;id=' . $row['id'] . '"><strong>Trouver un chauffeur</strong></a>';
								}

							$html_code .= '</td>';


							$point_depart = unserialize($row['point_depart']);
							$point_arrivee = unserialize($row['point_arrivee']);



							$tag_title ='';
							if (isset($point_depart['adresse'])) {
								if ($point_depart['type'] == 'beneficiaire') {
									$tag_title = 'Domicile : ' . $point_depart['adresse'];
								} elseif ($point_depart['type'] == 'lieu') {
									$tag_title = $point_depart['nom_complet'] . ' - ' . $point_depart['adresse'];
								}

							} else {
								$tag_title = '""';
							}

							$html_code .= '<td title="' . $tag_title . '">';
								$html_code .= mb_strtoupper(stripAccents($point_depart['ville']));
							$html_code .= '</td>';


							$tag_title ='';
							if (isset($point_arrivee['adresse'])) {
								if ($point_arrivee['type'] == 'beneficiaire') {
									$tag_title = 'Domicile : ' . $point_arrivee['adresse'];
								} elseif ($point_arrivee['type'] == 'lieu') {
									$tag_title = $point_arrivee['nom_complet'] . ' - ' . $point_arrivee['adresse'];
								}

							} else {
								$tag_title = '""';
							}

							$html_code .= '<td title="' . $tag_title . '">';
								$html_code .= mb_strtoupper(stripAccents($point_arrivee['ville']));
							$html_code .= '</td>';


							$html_code .= '<td>';
								$html_code .= format_type_trajet($row['aller_retour'], $row['duree_approximative']);
							$html_code .= '</td>';

							$html_code .= '<td>';
								$html_code .= '<a href="?module=transport&amp;action=edit&amp;id=' . $row['id'] . '">Modifier</a>';
							$html_code .= '</td>';


							$html_code .= '<td>';
								$html_code .= '<a class="link_ajax_get" href="?module=transport&amp;action=cancel&amp;id=' . $row['id'] . '">Annuler</a>';
							$html_code .= '</td>';

						$html_code .= '</tr>';
					}
				$html_code .= '</tbody>';
			$html_code .= '</table>';
		}

		$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));

		return $html_code;
	} // class.Transport.form.list


	private static function form_view($action, $data_to_display='') {
		$html_code = '';

		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}

		$html_code .= '<p>';
			$html_code .= '<a href="?module=transport&amp;action=edit&amp;id=' . $data_to_display['id']['value'] . '">Editer le transport</a>';
		$html_code .= '</p>';


		$html_code .= '<h1 id="detail_transport">';
			$html_code .= 'Détail du transport';
		$html_code .= '</h1>';

		$tmp_transport = new Transport($data_to_display['id']['value']);

		$tmp_beneficiaire = new Beneficiaire($data_to_display['id_beneficiaire']['value']);
			$tmp_beneficiaire_nom_complet = $tmp_beneficiaire->get_nom_complet();
			$tmp_beneficiaire_adresse = $tmp_beneficiaire->get_adresse();
			$tmp_beneficiaire_telephone = $tmp_beneficiaire->get_telephone();

		//info diverses
		if ($data_to_display['info_diverses']['value'] != '') {
			$html_code .= '<p>';
				$html_code .= nl2br($data_to_display['info_diverses']['value']);
			$html_code .= '</p>';
		}

		$html_code .= '<table>';
			$html_code .= '<thead>';
				$html_code .= '<tr>';
					$html_code .= '<th>Date</th>';
					$html_code .= '<th>Passager</th>';
					$html_code .= '<th>De</th>';
					$html_code .= '<th>A</th>';
				$html_code .= '</tr>';
			$html_code .= '</thead>';


			$html_code .= '<tbody>';
				$html_code .= '<tr>';
					$html_code .= '<td>';
						$html_code .= date_yyyymmdd_to_ddmmyyyy($tmp_transport->get_date());
						$html_code .= '<br />';
						$html_code .= time_hhmmss_to_hhmm($tmp_transport->get_time());
					$html_code .= '</td>';


					//passager
					$html_code .= '<td>';
						$html_code .= $tmp_beneficiaire_nom_complet['titre'] . '<br />';

						$html_code .= '<a class="link_dialog" href="?module=beneficiaire&action=view&id=' . $data_to_display['id_beneficiaire']['value'] . '">';
							$html_code .= $tmp_beneficiaire_nom_complet['prenom'] . ' ' . $tmp_beneficiaire_nom_complet['nom'] . '<br />';
						$html_code .= '</a>';

						$html_code .= $tmp_beneficiaire_adresse['adresse'] . '<br />';

						if (isset($tmp_beneficiaire_adresse['adresse_complement'])) {
							$html_code .= $tmp_beneficiaire_adresse['adresse_complement'] . '<br />';
						}
						$html_code .= $tmp_beneficiaire_adresse['npa'] . ' ' . $tmp_beneficiaire_adresse['ville'] . '<br />';

						foreach ($tmp_beneficiaire_telephone as $index => $row) {
							$html_code .= str_replace('tel_', '', $index) . ' ' . format_tel($row) . '<br />';
						}

					$html_code .= '</td>';


					//de
					$html_code .= '<td>';
						if ($data_to_display['point_depart']['value']['type'] == 'beneficiaire') {

							if (isset($data_to_display['point_depart']['value']['id'])) {
								$html_code .= '<a class="link_dialog" href="?module=beneficiaire&action=view&id=' . $data_to_display['point_depart']['value']['id'] . '">';
							}
								$html_code .= 'Domicile du passager<br />';

							if (isset($data_to_display['point_depart']['value']['id'])) {
								$html_code .= '</a>';
							}

						} elseif ($data_to_display['point_depart']['value']['type'] == 'lieu') {

						} else {

						}

						foreach ($data_to_display['point_depart']['value'] as $index => $row) {
							if (!is_array($row) && $index != 'type' && $index != 'id' && $index != 'npa' && $row != '') {
								$html_code .= $row . '<br />';
							} elseif ($index == 'npa') {
								$html_code .= $row . ' ';
							}
						}

					$html_code .= '</td>';


					//a
					$html_code .= '<td>';

						if ($data_to_display['point_arrivee']['value']['type'] == 'beneficiaire') {

							if (isset($data_to_display['point_arrivee']['value']['id'])) {
								$html_code .= '<a class="link_dialog" href="?module=beneficiaire&action=view&id=' . $data_to_display['point_arrivee']['value']['id'] . '">';
							}
								$html_code .= 'Domicile du passager<br />';

							if (isset($data_to_display['point_arrivee']['value']['id'])) {
								$html_code .= '</a>';
							}

						} elseif ($data_to_display['point_arrivee']['value']['type'] == 'lieu') {

						} else {

						}

						foreach ($data_to_display['point_arrivee']['value'] as $index => $row) {
							if (!is_array($row) && $index != 'type' && $index != 'id' && $index != 'npa' && $row != '') {
								$html_code .= $row . '<br />';
							} elseif ($index == 'npa') {
								$html_code .= $row . ' ';
							}
						}

					$html_code .= '</td>';

				$html_code .= '</tr>';
			$html_code .= '</tbody>';
		$html_code .= '</table>';

		$html_code .= '<br />';

		$html_code .= '<table>';
			$html_code .= '<thead>';
				$html_code .= '<tr>';
					$html_code .= '<th>Transporteur</th>';
				$html_code .= '</tr>';
			$html_code .= '</thead>';

			$html_code .= '<tbody>';
				$html_code .= '<tr>';
					$html_code .= '<td>';

						if (isset($data_to_display['id_transporteur']['value']) && is_numeric($data_to_display['id_transporteur']['value'])) {
							$tmp_transporteur = new Transporteur($data_to_display['id_transporteur']['value']);
								$tmp_transporteur_nom_complet = $tmp_transporteur->get_nom_complet();
								$tmp_transporteur_adresse = $tmp_transporteur->get_adresse();
								$tmp_transporteur_telephone = $tmp_transporteur->get_telephone();

							$html_code .= $tmp_transporteur_nom_complet['titre'] . '<br />';

							$html_code .= '<a class="link_dialog" href="?module=benevole&action=view&id=' . $data_to_display['id_transporteur']['value'] . '">';
								$html_code .= $tmp_transporteur_nom_complet['prenom'] . ' ' . $tmp_transporteur_nom_complet['nom'] . '<br />';
							$html_code .= '</a>';

							$html_code .= $tmp_transporteur_adresse['adresse'] . '<br />';

							if (isset($tmp_transporteur_adresse['adresse_complement'])) {
								$html_code .= $tmp_transporteur_adresse['adresse_complement'] . '<br />';
							}
							$html_code .= $tmp_transporteur_adresse['npa'] . ' ' . $tmp_transporteur_adresse['ville'] . '<br />';

							foreach ($tmp_transporteur_telephone as $index => $row) {
								$html_code .= str_replace('tel_', '', $index) . ' ' . format_tel($row) . '<br />';
							}


						} else {
							//recherche un chauffeur
							$html_code .= '<a href="?module=transport&amp;action=find_driver&amp;id=' . $tmp_transport->get_id() . '">';
								$html_code .= '<strong>Rechercher un chauffeur</strong>';
							$html_code .= '</a>';
						}

					$html_code .= '</td>';
				$html_code .= '</tr>';
			$html_code .= '</tbody>';
		$html_code .= '</table>';


		if (checkInternetConnection()) {
			$html_code .= '<form>';
				$html_code .= add_FormElement_input('hidden', 'gmap_direction_start', '', $data_to_display['point_depart']['value']['adresse'] . ',' . $data_to_display['point_depart']['value']['npa'] . ','. $data_to_display['point_depart']['value']['ville'] );
				$html_code .= add_FormElement_input('hidden', 'gmap_direction_end', '', $data_to_display['point_arrivee']['value']['adresse'] . ',' . $data_to_display['point_arrivee']['value']['npa'] . ','. $data_to_display['point_arrivee']['value']['ville'] );
			$html_code .= '</form>';

			$html_code .= '<div id="map_direction" class="map_google"></div>';

			$html_code .= '<script type="text/javascript">';
				$html_code .= '$(document).ready(function() {';
					$html_code .= 'gmap_direction_initialize();';
				$html_code .= '});';
			$html_code .= '</script>';

		}

		$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));

		return $html_code;
	} // class.Transport.form.view


	private static function form_find_driver($action, $data_to_display='') {
		global $dbh;

		$weekdays[1] = 'lundi';
		$weekdays[2] = 'mardi';
		$weekdays[3] = 'mercredi';
		$weekdays[4] = 'jeudi';
		$weekdays[5] = 'vendredi';
		$weekdays[6] = 'samedi';
		$weekdays[7] = 'dimanche';

		$html_code = '';

		if (isset($_GET['id']) && Transport::id_exists($_GET['id'])) {
			$tmp_transport = new Transport($_GET['id']);
		} else {
			if (isset($_SESSION['last_page']['id']) && Transport::id_exists($_SESSION['last_page']['id'])) {
				$tmp_transport = new Transport($_SESSION['last_page']['id']);
			} else {
				die();
			}

		}

		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}

		load_class_and_interface(array('GLM', 'Beneficiaire', 'Transporteur'));

		$tmp_GLM = new GLM($tmp_transport);
		$list_potentiel_transporteur = $tmp_GLM->get_chauffeurs_potentiels();


		//mise en session des donnees pour faciliter le reload
		$_SESSION['last_page']['module'] = 'transport';
		$_SESSION['last_page']['action'] = 'find_driver';
		$_SESSION['last_page']['id'] = $tmp_transport->get_id();



		//quelques info sur le transport et le passager

		foreach($weekdays as $idx_day => $day) {
			if ($idx_day == date('N', strtotime($tmp_transport->get_date()))) {
				$txt_weekday = $day;
			}
		}

		$html_code .= '<div id="beneficiaire_info" class="highlights">';

			$id_beneficiaire = $tmp_transport->get_id_beneficiaire();

			if (isset($id_beneficiaire) && Beneficiaire::id_exists($id_beneficiaire)) {
				$tmp_beneficiaire = new Beneficiaire($id_beneficiaire);
				$tmp_beneficiaire_nom_complet = $tmp_beneficiaire->get_nom_complet();
			}

			$html_code .= '<table>';
				$html_code .= '<thead>';
					$html_code .= '<tr>';
						$html_code .= '<th>Date</th>';
						$html_code .= '<th>Heure</th>';
						$html_code .= '<th>Durée</th>';
						$html_code .= '<th>Type</th>';
						$html_code .= '<th>Passager</th>';
						$html_code .= '<th>Départ</th>';
						$html_code .= '<th>Destination</th>';

						if ($tmp_beneficiaire->has_tel_fixe()) {
							$html_code .= '<th>Tél. fixe</th>';
						}

						if ($tmp_beneficiaire->has_tel_mobile()) {
							$html_code .= '<th>Tél. mobile</th>';
						}
					$html_code .= '</tr>';
				$html_code .= '</thead>';

				$html_code .= '<tbody>';

					$html_code .= '<td>';
						$html_code .= '<a  href="?module=transport&amp;action=edit&amp;id=' . $tmp_transport->get_id() . '">';
					 		$html_code .= date_yyyymmdd_to_ddmmyyyy($tmp_transport->get_date()) . ' - ' . substr($txt_weekday, 0, 3);
					 	$html_code .= '</a>';
					 $html_code .= '</td>';

					$html_code .= '<td>' . time_hhmmss_to_hhmm($tmp_transport->get_time()) . '</td>';
					$html_code .= '<td>' . format_duree($tmp_transport->get_duree()) . '</td>';

					$html_code .= '<td>';
						$html_code .= format_type_trajet($tmp_transport->is_aller_retour(), $tmp_transport->get_duree());
					$html_code .= '</td>';

					$html_code .= '<td>';
						$html_code .= '<a class="link_dialog" href="?module=beneficiaire&amp;action=view&amp;id=' . $id_beneficiaire . '">';
							$html_code .= format_titre($tmp_beneficiaire_nom_complet['titre']) . ' ' . mb_strtoupper(stripAccents($tmp_beneficiaire_nom_complet['nom']));
						$html_code .= '</a>';
					$html_code .= '</td>';

					$transport_point_depart = $tmp_transport->get_point_depart();
					$transport_point_arrivee = $tmp_transport->get_point_arrivee();

					if ($transport_point_depart['adresse'] != '') {
						$html_code .= '<td>' . mb_strtoupper(stripAccents($transport_point_depart['ville'])) . ' (' . format_adresse($transport_point_depart['adresse']) .  ')</td>';
					} else {
						$html_code .= '<td>' . mb_strtoupper(stripAccents($transport_point_depart['ville'])) .  '</td>';
					}


					//info bulle avec + info concernant la destination
					if (isset($transport_point_arrivee['adresse'])) {
						if ($transport_point_arrivee['type'] == 'beneficiaire') {
							$tag_title = 'Domicile : ' . $transport_point_arrivee['adresse'];
						} elseif ($transport_point_arrivee['type'] == 'lieu') {
							$tag_title = $transport_point_arrivee['nom_complet'] . ' - ' . $transport_point_arrivee['adresse'];
						}

					} else {
						$tag_title = '""';
					}

					$html_code .= '<td title="' . $tag_title . '">' . mb_strtoupper(stripAccents($transport_point_arrivee['ville'])) .  '</td>';

					$tmp_beneficiaire_telephone = $tmp_beneficiaire->get_telephone();

					if ($tmp_beneficiaire->has_tel_fixe()) {
						$html_code .= '<td class="tel_fixe">' . format_tel($tmp_beneficiaire_telephone['tel_fixe']) . '</td>';
					}

					if ($tmp_beneficiaire->has_tel_mobile()) {
						$html_code .= '<td class="tel_mobile">' . format_tel($tmp_beneficiaire_telephone['tel_mobile']) . '</td>';
					}

				$html_code .= '</tbody>';

			$html_code .= '</table>';
		$html_code .= '</div>';


		//repondant
		$tmp_beneficiaire_repondant_id = $tmp_beneficiaire->has_repondant();
		if ($tmp_beneficiaire_repondant_id) {
			$html_code .= '<img src="./img/warning.png" />';

			$tmp_repondant = new Repondant($tmp_beneficiaire_repondant_id);
			$tmp_repondant_nom_complet = $tmp_repondant->get_nom_complet();
			$tmp_repondant_tel = $tmp_repondant->get_telephone();

			$tmp_repondant_str = '';
			if ($tmp_repondant->get_lien_beneficiaire() != '') {
				$tmp_repondant_str .= $tmp_repondant->get_lien_beneficiaire();
			}

			if ($tmp_repondant_nom_complet['nom'] != '' && $tmp_repondant_nom_complet['prenom'] != '') {
				 $tmp_repondant_str .= ' ' . ucfirst($tmp_repondant_nom_complet['prenom']) . ' ' . mb_strtoupper(stripAccents($tmp_repondant_nom_complet['nom']));
			}

			if (count($tmp_repondant_tel) > 0) {
				foreach ($tmp_repondant_tel as $row) {
					$tmp_repondant_str .= ' ' . format_tel($row);
				}
			}

			$html_code .= 'Ce passager a un répondant : ' . $tmp_repondant_str;
		}

		//infos diverses
		$info_diverses = $tmp_transport->get_infos_complementaires();

		if ($info_diverses) {
			$html_code .= '<p>';
				$html_code .= nl2br($info_diverses);
			$html_code .= '</p>';
		}

		// derniers transports programmes pour ce passager
		$nbre_histo_transport_a_afficher = 5;
		$sql = "SELECT transport_transporteur.*, transport.*";
		$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport ";
		$sql .= " WHERE transport.id_beneficiaire=" . $tmp_transport->get_id_beneficiaire();
		$sql .= " AND transport.is_annule=0";
		$sql .= " ORDER BY transport.date_transport DESC";
		$sql .= " LIMIT " . $nbre_histo_transport_a_afficher;

		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		if (count($result) > 0) {
			$html_code .= '<div id="find_driver_last_transports">';

				$html_code .= '<h2>Derniers transports programmés</h2>';

				$html_code .= '<table class="OddEven">';
					$html_code .= '<thead>';
						$html_code .= '<tr>';
							$html_code .= '<th>Date</th>';
							$html_code .= '<th>Destination</th>';
							$html_code .= '<th>Transporteur</th>';
							$html_code .= '<th>Ville</th>';
							$html_code .= '<th>Tél. fixe</th>';
							$html_code .= '<th>Tél. mobile</th>';
							$html_code .= '<th>Attribuer</th>';
						$html_code .= '</tr>';
					$html_code .= '</thead>';

					$html_code .= '<tbody>';
						foreach($result as $row) {
							$html_code .= '<tr>';
								$html_code .= '<td>';
									$html_code .= '<a class="link_dialog" href="?module=transport&amp;action=view&amp;id=' . $row['id_transport'] . '">';
										$html_code .= date_yyyymmdd_to_ddmmyyyy($row['date_transport']);
									$html_code .= '</a>';
								$html_code .= '</td>';

								$point_arrivee = unserialize($row['point_arrivee']);

								$html_code .= '<td>';
									$html_code .= mb_strtoupper(stripAccents($point_arrivee['ville']));
								$html_code .= '</td>';

								$tmp_transporteur = new Transporteur($row['id_transporteur']);
								$tmp_transporteur_nom = $tmp_transporteur->get_nom_complet();
								$tmp_transporteur_adresse = $tmp_transporteur->get_adresse();
								$tmp_transporteur_tel = $tmp_transporteur->get_telephone();

								$html_code .= '<td>';
									$html_code .= '<a class="link_dialog" href="?module=benevole&amp;action=view&amp;id=' . $tmp_transporteur->get_id_transporteur() . '">';
										$html_code .= format_titre($tmp_transporteur_nom['titre']) . ' ' . mb_strtoupper(stripAccents($tmp_transporteur_nom['nom']));
									$html_code .= '</a>';
								$html_code .= '</td>';

								$html_code .= '<td>';
									$html_code .= mb_strtoupper(stripAccents($tmp_transporteur_adresse['ville']));
								$html_code .= '</td>';


								$html_code .= '<td class="tel_fixe">';
									if ( isset($tmp_transporteur_tel['tel_fixe']) ) {
										$html_code .= format_tel($tmp_transporteur_tel['tel_fixe']);
									}
								$html_code .= '</td>';

								$html_code .= '<td class="tel_mobile">';
									if ( isset($tmp_transporteur_tel['tel_mobile']) ) {
										$html_code .= format_tel($tmp_transporteur_tel['tel_mobile']);
									}
								$html_code .= '</td>';

								$html_code .= '<td>';
									$html_code .= '<a href="?module=transport&amp;action=link_driver&amp;id_transport=' . $tmp_transport->get_id() . '&amp;id_transporteur=' . $tmp_transporteur->get_id_transporteur() . '">';
										$html_code .= 'Attribuer';
									$html_code .= '</a>';
								$html_code .= '</td>';

							$html_code .= '</tr>';
						}
					$html_code .= '</tbody>';
				$html_code .= '</table>';
			$html_code .= '</div>';

			$html_code .= '<p>';
				$html_code .= '<a id="show_find_driver_last_transports" href="">';
					$html_code .= 'Voir les derniers transports de ce passager';
				$html_code .= '</a>';
			$html_code .= '</p>';
		}


		//affichage du resultat
		if (count($list_potentiel_transporteur) > 0) {

			$html_code .= '<h1>Liste des transporteurs disponibles pour ce transport</h1>';

			$html_code .= '<table class="OddEven">';
				$html_code .= '<thead>';
					$html_code .= '<tr>';
						$html_code .= '<th></th>';
						$html_code .= '<th>Nom</th>';
						$html_code .= '<th>Ville</th>';
						$html_code .= '<th>Tél. fixe</th>';
						$html_code .= '<th>Tél. mobile</th>';
						$html_code .= '<th>Type</th>';
						$html_code .= '<th></th>'; //<em>Choisir</em> ce chauffeur
						//$html_code .= '<th></th>'; //Inatteignable
						$html_code .= '<th></th>'; //Marquer comme <strong>non</strong> disponible
						$html_code .= '<th></th>'; //recommander d'autres transports
					$html_code .= '</tr>';
				$html_code .= '</thead>';

				$html_code .= '<tbody>';
					$i = 1;
					$count_hide_transporteur = 0;
					foreach ($list_potentiel_transporteur as $transporteur) {

						$nbre_possibilite_transporteur_to_show = 15;

						if ($i <= $nbre_possibilite_transporteur_to_show) {
							$class_transporteur = 'show_transporteur';
						} else {
							$class_transporteur = 'hide_transporteur';
							$count_hide_transporteur++;
						}

						$on_the_road = FALSE;
						$on_the_road = $transporteur->check_est_sur_la_route();

						$is_on_holidays = !$transporteur->check_disponibilite_date(Benevole_Disponibilite_Categorie::get_id_from_nom('transport'), date('Y-m-d'));


						$transporteur_nom_complet = $transporteur->get_nom_complet();

						$transporteur_adresse = $transporteur->get_adresse();
						$transporteur_telephones = $transporteur->get_telephone();

						$html_code .= '<tr class="' . $class_transporteur . '">';

							$html_code .= '<td>';
								$html_code .= $i;
								$i++;
							$html_code .= '</td>';

							$html_code .= '<td>';


									if ($on_the_road === TRUE) {
										$html_code .= '<img src="./img/taxi.png" title="Le chauffeur est actuellement en-train de conduire un passager, il risque de ne pas être atteignable" />';
									}

									if ($is_on_holidays === TRUE) {
										$html_code .= '<img src="./img/holidays.png" title="Le chauffeur est actuellement marqué comme non disponible (vacances etc.)" />';
									}

								$html_code .= '<a class="link_dialog" href="?module=benevole&amp;action=view&amp;id=' . $transporteur->get_id_transporteur() . '">';
									$html_code .= format_titre($transporteur_nom_complet['titre']) . ' ' . mb_strtoupper(stripAccents($transporteur_nom_complet['nom']));
								$html_code .= '</a>';

								if ($transporteur->has_email()) {
									$html_code .= '<img title="le chauffeur possède une adresse email" alt= title="le chauffeur possède une adresse email" src="./img/email.png" />';
								}
							$html_code .= '</td>';

							$html_code .= '<td>';
								$html_code .= $transporteur_adresse['ville'];
							$html_code .= '</td>';

							$html_code .= '<td class="tel_fixe">';
								if ( isset($transporteur_telephones['tel_fixe']) ) {
									$html_code .= '<strong>' . format_tel($transporteur_telephones['tel_fixe']) . '</strong>';
								}
							$html_code .= '</td>';

							$html_code .= '<td class="tel_mobile">';
								if ( isset($transporteur_telephones['tel_mobile']) ) {
									$html_code .= '<strong>' . format_tel($transporteur_telephones['tel_mobile']) . '</strong>';
								}
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
								$type_transport .= '<strong>V</strong>|';
							} else {
								$type_transport .= '-|';
							}

							$html_code .= '<td title="L=Locaux , Ge=Genève, La=Lausanne, V=Vacances">';
								$html_code .= $type_transport;
							$html_code .= '</td>';

							$html_code .= '<td>';
								$html_code .= '<a href="?module=transport&amp;action=link_driver&amp;id_transport=' . $tmp_transport->get_id() . '&amp;id_transporteur=' . $transporteur->get_id_transporteur() . '">';
									$html_code .= 'Attribuer';
								$html_code .= '</a>';
							$html_code .= '</td>';


							/*
							$html_code .= '<td>';
								$html_code .= '<a class="link_ajax_get" href="?module=transporteur&amp;action=unreachable_today&amp;id_transporteur=' . $transporteur->get_id_transporteur() . '&amp;id=' . $tmp_transport->get_id() . '">';
									$html_code .= 'Inatteignable';
								$html_code .= '</a>';
							$html_code .= '</td>';
							*/


							$html_code .= '<td title="Le chauffeur n\'est pas disponible pour la date du ' . date_yyyymmdd_to_ddmmyyyy($tmp_transport->get_date()) . '" >';
								$html_code .= '<a class="link_ajax_get" href="?module=transporteur&amp;sub_module=non_dispo_date_transport&amp;action=add&amp;id=' . $transporteur->get_id_transporteur() . '&amp;date_custom='. $tmp_transport->get_date() . '">';
									$html_code .= 'Non disponible';
								$html_code .= '</a>';
							$html_code .= '</td>';

							$html_code .= '<td title="Recommander d\'autres transports pour ce chauffeur" >';
								$html_code .= '<a class="link_dialog" href="?module=transporteur&amp;action=transports_potentiels&amp;id=' . $transporteur->get_id_transporteur() . '" >';
									$html_code .= 'Autres transports';
								$html_code .= '</a>';
							$html_code .= '</td>';


						$html_code .= '</tr>';
					}
				$html_code .= '</tbody>';
			$html_code .= '</table>';

			if ($count_hide_transporteur > 0) {
				$html_code .= '<p>';
					$html_code .= '<a id="show_remaining_transporteur" href="" >';
						$html_code .= 'Afficher les transporteurs restants';
					$html_code .= '</a>';
				$html_code .= '</p>';
			}

		} else {

			$html_code .= '<h1>Personne n\'est disponible pour la date et la période choisie</h1>';

		}


		$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));

		return $html_code;
	} // class.Transport.form.find_driver



	private static function form_archive($action, $data_to_display='') {

		if (isset($_SESSION['last_page']['archive_month']) && isset($_SESSION['last_page']['archive_year'])) {
			$data_to_display = array();
			$data_to_display['archive_month']['value'] = $_SESSION['last_page']['archive_month'];
			$data_to_display['archive_year']['value'] = $_SESSION['last_page']['archive_year'];
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


		if (isset($data_to_display['archive_month']['value']) && is_numeric($data_to_display['archive_month']['value']) && $data_to_display['archive_month']['value'] > 0 && $data_to_display['archive_month']['value'] <= 12 && isset($data_to_display['archive_year']['value']) && is_numeric($data_to_display['archive_year']['value']) && $data_to_display['archive_year']['value'] > 0) {
			//remonte la totalite des transports du mois et de l'annee concernee
			global $dbh;

			$weekdays[1] = 'lundi';
			$weekdays[2] = 'mardi';
			$weekdays[3] = 'mercredi';
			$weekdays[4] = 'jeudi';
			$weekdays[5] = 'vendredi';
			$weekdays[6] = 'samedi';
			$weekdays[7] = 'dimanche';


			//liste des dates pour la construction des liens de calendrier
			$sql = "SELECT DISTINCT date_transport ";
			$sql .= " FROM transport ";
			$sql .= " WHERE id_filiale=" . $_SESSION['filiale']['id'];
			$sql .= " AND is_annule=0";
			$sql .= " AND MONTH(date_transport)=" . $data_to_display['archive_month']['value'];
			$sql .= " AND YEAR(date_transport)=" . $data_to_display['archive_year']['value'];

			$sth = $dbh->query($sql);
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);

			$dates_link = array();
			$distinct_months = array();
			$calendars = array();


			foreach ($result as $row) {
				$dates_link[] = $row['date_transport'];

				if (!in_array(date('m', strtotime($row['date_transport'])), $distinct_months)) {
					$distinct_months[] = date('m', strtotime($row['date_transport']));

					$calendars[] = array('month' => date('n', strtotime($row['date_transport'])), 'year' => date('Y', strtotime($row['date_transport'])) );
				}
			}


			$html_code .= '<div id="calendars_link" class="clear-after">';
				foreach ($calendars as $row) {
					$html_code .= calendrier($row['month'], $row['year'], $dates_link, $row['month'] . '-' . $row['year'],'link_date','', 'link_date' );
				}
			$html_code .= '</div>';


			$sql = "SELECT transport_transporteur.*, transport.* ";
			$sql .= " FROM transport LEFT JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport ";
			$sql .= " WHERE id_filiale=" . $_SESSION['filiale']['id'];
			$sql .= " AND is_annule=0";
			$sql .= " AND MONTH(transport.date_transport)=" . $data_to_display['archive_month']['value'];
			$sql .= " AND YEAR(transport.date_transport)=" . $data_to_display['archive_year']['value'];
			$sql .= " ORDER BY transport.date_transport, transport.heure_debut";

			$sth = $dbh->query($sql);
			//transport avec & sans chauffeur grace au left join!
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);


			//presentation des resultats des mois choisis avec les differentes fonctionnalites de comptabilite
			if (count($result) > 0) {

				$html_code .= '<table class="OddEven">';
					$html_code .= '<thead>';
						$html_code .= '<tr>';
							$html_code .= '<th>Date &amp; Heure</th>';
							$html_code .= '<th>Passager</th>';
							$html_code .= '<th>Transporteur</th>';
							$html_code .= '<th>Départ</th>';
							$html_code .= '<th>Arrivée</th>';
							$html_code .= '<th>Type</th>'; // Aller-retour?
							$html_code .= '<th></th>'; // Editer
							$html_code .= '<th></th>'; // Annuler
						$html_code .= '</tr>';
					$html_code .= '</thead>';

					$html_code .= '<tbody>';
						$last_date_txt = '';
						foreach ($result as $row) {
							if ($last_date_txt != $row['date_transport']) {

								$weekday = date('N', strtotime($row['date_transport']));

								foreach($weekdays as $idx_day => $day) {
									if ($idx_day == $weekday) {
										$txt_weekday = $day;
										break;
									}
								}

								$html_code .= '<tr>';
									if ($row['date_transport'] == date('Y-m-d')) {
										$html_code .= '<th><a name="' . $row['date_transport'] . '"><a class="header_date_today" href="#top">' . date_yyyymmdd_to_ddmmyyyy($row['date_transport']) . ' - ' . substr($txt_weekday, 0, 3) . '</a></th>';
									} else {
										$html_code .= '<th><a name="' . $row['date_transport'] . '"><a class="header_date" href="#top">' . date_yyyymmdd_to_ddmmyyyy($row['date_transport']) . ' - ' . substr($txt_weekday, 0, 3) . '</a></th>';
									}

								$html_code .= '</tr>';

								$last_date_txt = $row['date_transport'];
							}

							$html_code .= '<tr>';

								$html_code .= '<td>';
									$html_code .= '<a href="?module=transport&amp;action=view&amp;id=' . $row['id'] . '">';
										$html_code .= time_hhmmss_to_hhmm($row['heure_debut']);
									$html_code .= '</a>';
								$html_code .= '</td>';



								$tmp_beneficiaire = new Beneficiaire($row['id_beneficiaire']);
								$tmp_beneficiaire_nom_complet = $tmp_beneficiaire->get_nom_complet();
								$tmp_beneficiaire_tel = $tmp_beneficiaire->get_telephone();

								$tel_string = '';
								foreach ($tmp_beneficiaire_tel as $type_tel => $tel) {
									$tel_string .= str_replace('tel_', '', $type_tel) . ' : ' . format_tel($tel) . '   ';
								}

								$html_code .= '<td title="' . $tel_string . '">';


									$html_code .= '<a class="link_dialog" href="?module=beneficiaire&amp;action=view&amp;id=' . $row['id_beneficiaire'] . '">';
										$html_code .= mb_strtoupper(stripAccents($tmp_beneficiaire_nom_complet['nom'])) . ', ' . $tmp_beneficiaire_nom_complet['prenom'];
									$html_code .= '</a>';
								$html_code .= '</td>';

								$html_code .= '<td>';
									if (is_numeric($row['id_transporteur'])) {
										//un chauffeur a deja ete trouve
										$tmp_transporteur = new Transporteur($row['id_transporteur']);
										$tmp_transporteur_nom_complet = $tmp_transporteur->get_nom_complet();
										$tmp_transporteur_tel = $tmp_transporteur->get_telephone();

										$tel_string = '';
										foreach ($tmp_transporteur_tel as $type_tel => $tel) {
											$tel_string .= str_replace('tel_', '', $type_tel) . ' : ' . format_tel($tel) . '   ';
										}

										$html_code .= '<a title="' . $tel_string . '" class="link_dialog" href="?module=benevole&amp;action=view&amp;id=' . $row['id_transporteur']. '">';
											$html_code .= mb_strtoupper(stripAccents($tmp_transporteur_nom_complet['nom'])) . ', ' . $tmp_transporteur_nom_complet['prenom'];
										$html_code .= '</a>';
									} else {
										$html_code .= '<a href="?module=transport&amp;action=find_driver&amp;id=' . $row['id'] . '"><strong>Trouver un chauffeur</strong></a>';
									}

								$html_code .= '</td>';


								$point_depart = unserialize($row['point_depart']);
								$point_arrivee = unserialize($row['point_arrivee']);


								$tag_title ='';
								if (isset($point_depart['adresse'])) {
									if ($point_depart['type'] == 'beneficiaire') {
										$tag_title = 'Domicile : ' . $point_depart['adresse'];
									} elseif ($point_depart['type'] == 'lieu') {
										$tag_title = $point_depart['nom_complet'] . ' - ' . $point_depart['adresse'];
									}

								} else {
									$tag_title = '""';
								}

								$html_code .= '<td title="' . $tag_title . '">';
									$html_code .= mb_strtoupper(stripAccents($point_depart['ville']));
								$html_code .= '</td>';




								$tag_title ='';
								if (isset($point_arrivee['adresse'])) {
									if ($point_arrivee['type'] == 'beneficiaire') {
										$tag_title = 'Domicile : ' . $point_arrivee['adresse'];
									} elseif ($point_arrivee['type'] == 'lieu') {
										$tag_title = $point_arrivee['nom_complet'] . ' - ' . $point_arrivee['adresse'];
									}

								} else {
									$tag_title = '""';
								}

								$html_code .= '<td title="' . $tag_title . '">';
									$html_code .= mb_strtoupper(stripAccents($point_arrivee['ville']));
								$html_code .= '</td>';


								$html_code .= '<td>';
									$html_code .= format_type_trajet($row['aller_retour'], $row['duree_approximative']);
								$html_code .= '</td>';

								$html_code .= '<td>';
									$html_code .= '<a href="?module=transport&amp;action=edit&amp;id=' . $row['id'] . '">Modifier</a>';
								$html_code .= '</td>';


								$html_code .= '<td>';
									$html_code .= '<a class="link_ajax_get" href="?module=transport&amp;action=cancel&amp;id=' . $row['id'] . '">Annuler</a>';
								$html_code .= '</td>';

							$html_code .= '</tr>';
						}
					$html_code .= '</tbody>';
				$html_code .= '</table>';

			}


		} else {
			//charge le month picker
			$html_code .= '<form action="" method="post">';
				$html_code .= '<div id="archive_month_picker" class="MonthPicker"></div>';

				$html_code .= '<p>';
					$html_code .= '<input id="month_picker_month" name="month_picker_month" type="hidden" value="' . date('n') .  '"/>';
				$html_code .= '</p>';

				$html_code .= '<p>';
					$html_code .= '<input id="month_picker_year" name="month_picker_year" type="hidden" value="' . date('Y') .  '"/>';
				$html_code .= '</p>';

				$html_code .= add_FormElement_input('hidden', 'form', '', 'archive');
				$html_code .= add_FormElement_input('hidden', 'module', '', 'transport');
				$html_code .= add_FormElement_input('hidden', 'sub_module', '', '""');
				$html_code .= add_FormElement_input('hidden', 'action', '', $action);

				$html_code .= '<input type="submit" value="Soumettre" />';
			$html_code .= '</form>';
		}

		$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));

		return $html_code;
	} // class.Transport.form.archive


} // class.Transport
