<?php

require_once( str_replace ( '\\', '/', dirname(dirname(__FILE__))) . '/class.declaration.php' );
load_class_and_interface(array('Transport', 'Benevole_Disponibilite_Categorie', 'Transporteur', 'Periode_Journee', 'Trajet_Pre_Defini'));

/**
 *
 * Modèle d'aide à la décision pour la suggestion de chauffeurs pour un
 * transport donné
 *
 * Mode de fonctionnement :
 * 1. Un transport (sous forme d'objet) est réceptionné par le constructeur
 * 		Remarque: il est également possible de passer 2 arguments
 * 			une date et une période de journée, plutôt qu'un objet "transport",
 * 			avec comme limite que certains filtres ne peuvent pas être
 * 			appliqués car une partie des données nécessaires sont
 * 			normalement extraites de l'objet transport (par ex. les
 * 			contraintes de passagers).
 * 			La phase de ranking ne peut également pas fonctionner, la liste
 * 			des chauffeurs est alors ordonnée sur un seul critière qui est
 * 			le nombre de trajet à +/- 15 jours autour de la date en argument
 * 2. Le jour de la semaine (1 à 7) ainsi que la période de la journée (matin,
 * midi, soir) sont extraits de la date et de l'heure du transport
 * 3. La liste des transporteurs disponibles selon leur disponibilité standards
 * est remontée de la base de données (par ex: tous les chauffeurs disponibles
 * le jeudi après-midi)
 * 4. plusieurs filtres pour épurer la liste sont appliqués :
 * 	a) les transporteurs inactifs / passifs / démissions etc.
 * 	b) les transporteurs non disponibles (vacances etc.)
 * 	c) les chauffeurs qui ont déjà un transport ce jour là à la MEME PERIODE
 *  d) les gens tagué comme inatteignable (personnes que l'on a tentées de
 * 		contacter durant la journée en cours et qui ne repondent pas)
 * 	e) personne qui ne veulent pas transporter le passager
 * 	f) si le transport a lieu le matin et que le chauffeur est permanencier
 * 		ce jour là
 *
 * PHASE DE RANKING (uniquement si un objet transport est passé en args)
 * 5) Construction d'une matrice contenant pour chaque chauffeur :
 * 	a) la distance entre le domicile du transporteur et le point de départ
 * 	b) le nombre de fois que le transporteur a déjà véhiculé le passager
 * 	c) si le chauffeur a déjà un transport ce jour là (oui=1 / non =0)
 * 	d) le nombre de trajet faits/prévus à +/- 15 jours autour de la date
 * 		du transport
 * 	e) le nombre de kilomètres parcourus/prévus à +/- 15 jours autour de
 * 		la date du transport
 * 	f) pour favoriser les transports locaux aux chauffeurs locaux pour éviter
 * 		de bloquer un chauffeur qui effectue de longue distance.
 * 		Si il s'agit d'un transport de faible distance et que le chauffeur
 * 		effectue de longue distance il reçoit 0 sinon 1
 * 6) norme les vecteurs colonnes de la matrice précédente de 0 à 100
 * 	en inversant le résultat si nécessaire (par ex. plus la distance
 * 	ville->ville est faible meilleure sera la note)
 * 7) calcul d'une moyenne pondérée pour la note finale
 * 		certain poids sont renforcés/diminués par rapport à la distance
 * 		du trajet. (par ex: si la distance à parcourir est faible, le modèle
 * 		renforce le poids de la proximité géographique entre le passager
 * 		et celle du chauffeur potentiel)
 * 8) trie le résultat
 * 9) monte un array avec des objets transporteurs ordonnés que l'on peut
 * 	récupérer n'importe où à l'aide de la fonction public de class
 * 	get_chauffeurs_potentiels
 *
 * @author Gregory Chevalley <gregory.chevalley@gmail.com>
 *
 */
class GLM {

	private $transport; //object transport

	private $filiale; //object filiale concernee par la requete

	private $beneficiaire; //object beneficiaire

	private $id_jour_semaine;
	private $id_periode_journee;

	private $is_transport_local;


	private $array_transporteurs_disponible_date_and_periode = array();
	private $array_transporteurs_non_disponible_date = array();
	private $array_transporteurs_inactive = array();
	private $array_transporteurs_contrainte_beneficaire = array();
	private $array_transporteurs_deja_transporteurs_pour_date_et_periode_transport = array();
	private $array_transporteurs_deja_transporteurs_pour_date_transport = array();
	private $array_transporteurs_inatteignable_aujourdhui = array();
	private $array_transporteur_a_la_permanence = array();

	private $array_transporteur = array();

	private $array_ville_distance = array();


	function __construct($obj_transport, $date_transport='', $periode_journee='', $is_geneve=0, $is_lausanne=0) {
		global $dbh;

		if (is_numeric($obj_transport) && Transport::id_exists($obj_transport)) {
			$obj_transport = new Transport($obj_transport);
		}

		if (!$obj_transport instanceof Transport) {

			//possible de recevoir une date et d'appliquer les contraites sur une date plutot que sur un transport
			if (!is_date($date_transport)) {
				die();
			}

			$id_filiale = $_SESSION['filiale']['id'];

			if (Filiale::id_exists($id_filiale)) {
				$this->filiale = new Filiale($id_filiale);
			} else {
				die();
			}

			$this->id_jour_semaine = date('N', strtotime($date_transport));

			if (is_numeric($periode_journee)) {
				if (Periode_Journee::id_exists($periode_journee)) {
					$this->id_periode_journee = $periode_journee;
				}
			} elseif ($periode_journee instanceof Periode_Journee) {
				$this->id_periode_journee = $periode_journee->get_id();
			} else {
				$this->id_periode_journee = Periode_Journee::get_id_from_nom($periode_journee);
			}


			//dispo
			$this->get_transporteur_disponible_date_periode(0, 0, $is_geneve, $is_lausanne);

			//puis * contraintes
			$this->get_transporteur_inactive();
			$this->get_transporeur_non_disponibilite_date($date_transport);
			$this->get_transporteur_inatteignable();
			$this->get_transporteur_already_transport_date_and_period($date_transport, $this->id_periode_journee);
			$this->get_transporteur_already_permanence_date($date_transport);

			$array_transporteur_id = array_diff($this->array_transporteurs_disponible_date_and_periode, $this->array_transporteurs_inactive, $this->array_transporteurs_non_disponible_date, $this->array_transporteurs_inatteignable_aujourdhui, $this->array_transporteurs_deja_transporteurs_pour_date_et_periode_transport, $this->array_transporteur_a_la_permanence);


			foreach ($array_transporteur_id as $transporteur_potentiel) {
				$array_transporteur_ABC[] = new Transporteur($transporteur_potentiel);
			}

			foreach ($array_transporteur_ABC as $transporteur) {
				$array_transporteur_Count_transport[$transporteur->get_id_transporteur()] = $transporteur->get_nbre_transports_between_2_dates(date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')-15,  date('Y'))), date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')+15,  date('Y'))));
			}

			asort($array_transporteur_Count_transport);

			$this->array_transporteur = array();
			foreach ($array_transporteur_Count_transport as $index => $transporteur) {
				$this->array_transporteur[] = new Transporteur($index);
			}

		} else {
			$this->transport = $obj_transport;

			$id_filiale = $this->transport->get_id_filiale();
			$this->filiale = new Filiale($id_filiale);

			$date_transport = $this->transport->get_date();
			$this->id_jour_semaine = date('N', strtotime($date_transport));

			$heure_debut = $this->transport->get_time();
			$this->id_periode_journee = Periode_Journee::get_id_periode_from_time($heure_debut);

			$id_beneficiaire = $this->transport->get_id_beneficiaire();
			$this->beneficiaire = new Beneficiaire($id_beneficiaire);

			//dispo
			$this->get_transporteur_disponible_date_periode();

			//puis * contraintes
			$this->get_transporteur_inactive();
			$this->get_transporeur_non_disponibilite_date();
			$this->get_transporteur_already_transport_date_and_period();
			$this->get_transporteur_inatteignable();
			$this->get_transporteur_contrainte_beneficaire();
			$this->get_transporteur_already_permanence_date();


			$array_transporteur_id = array_diff($this->array_transporteurs_disponible_date_and_periode, $this->array_transporteurs_inactive, $this->array_transporteurs_non_disponible_date, $this->array_transporteurs_inatteignable_aujourdhui, $this->array_transporteurs_contrainte_beneficaire, $this->array_transporteurs_deja_transporteurs_pour_date_et_periode_transport, $this->array_transporteur_a_la_permanence);
			
			foreach($array_transporteur_id as $transporteur) {
				$this->array_transporteur[] = new Transporteur($transporteur);
				$matrix_ranking[][0] = $transporteur;
			}


			// RANKING \\
			// split les transporteurs en 2 groupes (meme ville beneficiaire / autres villes)
			$array_adresse_beneficiaire = $this->transport->get_point_depart();

			foreach($this->array_transporteur as $index => $transporteur) {
				$array_adresse_transporteur = $transporteur->get_adresse();

				if (mb_strtoupper(stripAccents($array_adresse_transporteur['ville'])) == mb_strtoupper(stripAccents($array_adresse_beneficiaire['ville']))) {
					$array_transporteur_same_city_beneficiaire[] = $transporteur;
					$matrix_ranking[$index][1] = 0;
				} else {
					$array_transporteur_different_city_beneficiaire[] = $transporteur;

					if (!array_key_exists($array_adresse_transporteur['ville'], $this->array_ville_distance)) {

						$try_find_trajet_pre_defini = Trajet_Pre_Defini::find_combination($array_adresse_beneficiaire['ville'], $array_adresse_transporteur['ville']);

						if ($try_find_trajet_pre_defini) {
							$tmp_trajet_pre_defini = new Trajet_Pre_Defini($try_find_trajet_pre_defini['id']);
							$tmp_distance = $tmp_trajet_pre_defini->get_distance();
							$matrix_ranking[$index][1] = $tmp_distance;
							$this->array_ville_distance[$array_adresse_transporteur['ville']] = $tmp_distance;
						} else { //google maps
							//echo 'gmap:' . $array_adresse_beneficiaire['ville'] . '->' . $array_adresse_transporteur['ville'];
							$tmp_distance = Trajet_Pre_Defini::download_distance_from_google_maps('', $array_adresse_beneficiaire['npa'], $array_adresse_beneficiaire['ville'], $array_adresse_beneficiaire['pays'], '', $array_adresse_transporteur['npa'], $array_adresse_transporteur['ville'], $array_adresse_transporteur['pays']);
							$this->array_ville_distance[$array_adresse_transporteur['ville']] = $tmp_distance;
							$matrix_ranking[$index][1] = $tmp_distance;
						}
					} else {
						//recup direct dans array_ville_distance
						foreach ($this->array_ville_distance as $index_2 => $row_2) {
							if ($index_2 == $array_adresse_transporteur['ville']) {
								$tmp_distance = $row_2;
								$matrix_ranking[$index][1] = $tmp_distance;
								break;
							}
						}
					}

				}

			}


			//habitude
			$sql = "SELECT transport_transporteur.id_transporteur, COUNT(transport_transporteur.id_transporteur)";
			$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport ";
			$sql .= " WHERE transport.id_beneficiaire=" . $id_beneficiaire;
			$sql .= " AND is_annule=0";
			$sql .= " GROUP BY transport_transporteur.id_transporteur";
			$sql .= " ORDER BY transport_transporteur.id_transporteur";

			$sth = $dbh->query($sql);
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);


			foreach($matrix_ranking as $index => $transporteur) {
				foreach($result as $row) {
					if ($transporteur[0] == $row['id_transporteur']) {
						$matrix_ranking[$index][2] = $row['COUNT(transport_transporteur.id_transporteur)'];
						break;
					}
				}
			}

			foreach($matrix_ranking as $index => $transporteur) {
				if (!isset($transporteur[2])) {
					$matrix_ranking[$index][2] = 0;
				}
			}


			//deja un transport ce jour la
			$this->get_transporteur_already_transport_date();

			foreach($matrix_ranking as $index => $transporteur) {
				foreach($this->array_transporteurs_deja_transporteurs_pour_date_transport as $row) {
					if ($transporteur[0] == $row) {
						$matrix_ranking[$index][3] = 1;
					}
				}
			}

			foreach($matrix_ranking as $index => $transporteur) {
				if (!isset($transporteur[3])) {
					$matrix_ranking[$index][3] = 0;
				}
			}


			//nbre trajet du mois concerne
			$sql = "SELECT transport_transporteur.id_transporteur, COUNT(transport_transporteur.id_transporteur)";
			$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport ";
			$sql .= " WHERE is_annule=0";

			$sql .= " AND transport.date_transport>" . $dbh->quote(date('Y-m-d', mktime(0, 0, 0, date('m', strtotime($date_transport)), date('d', strtotime($date_transport))-15, date('Y', strtotime($date_transport)))));
			$sql .= " AND transport.date_transport<" . $dbh->quote(date('Y-m-d', mktime(0, 0, 0, date('m', strtotime($date_transport)), date('d', strtotime($date_transport))+15, date('Y', strtotime($date_transport)))));

			//$sql .= " AND MONTH(transport.date_transport)=" . $dbh->quote(date('n', strtotime($date_transport)));
			//$sql .= " AND YEAR(transport.date_transport)=" . $dbh->quote(date('Y', strtotime($date_transport)));
			$sql .= " GROUP BY transport_transporteur.id_transporteur";
			$sql .= " ORDER BY transport_transporteur.id_transporteur";

			$sth = $dbh->query($sql);
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);

			foreach($matrix_ranking as $index => $transporteur) {
				foreach($result as $row) {
					if ($transporteur[0] == $row['id_transporteur']) {
						$matrix_ranking[$index][4] = $row['COUNT(transport_transporteur.id_transporteur)'];
						break;
					}
				}
			}

			foreach($matrix_ranking as $index => $transporteur) {
				if (!isset($transporteur[4])) {
					$matrix_ranking[$index][4] = 0;
				}
			}



			//sum des km du mois concerne
			$sql = "SELECT transport_transporteur.id_transporteur, SUM(transport.nbre_kilometres)";
			$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport ";
			$sql .= " WHERE is_annule=0";

			$sql .= " AND transport.date_transport>" . $dbh->quote(date('Y-m-d', mktime(0, 0, 0, date('m', strtotime($date_transport)), date('d', strtotime($date_transport))-15, date('Y', strtotime($date_transport)))));
			$sql .= " AND transport.date_transport<" . $dbh->quote(date('Y-m-d', mktime(0, 0, 0, date('m', strtotime($date_transport)), date('d', strtotime($date_transport))+15, date('Y', strtotime($date_transport)))));

			//$sql .= " AND MONTH(transport.date_transport)=" . $dbh->quote(date('n', strtotime($date_transport)));
			//$sql .= " AND YEAR(transport.date_transport)=" . $dbh->quote(date('Y', strtotime($date_transport)));
			$sql .= " GROUP BY transport_transporteur.id_transporteur";
			$sql .= " ORDER BY transport_transporteur.id_transporteur";

			$sth = $dbh->query($sql);
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);

			foreach($matrix_ranking as $index => $transporteur) {
				foreach($result as $row) {
					if ($transporteur[0] == $row['id_transporteur']) {
						$matrix_ranking[$index][5] = $row['SUM(transport.nbre_kilometres)'];
						break;
					}
				}
			}

			foreach($matrix_ranking as $index => $transporteur) {
				if (!isset($transporteur[5])) {
					$matrix_ranking[$index][5] = 0;
				}
			}


			//privilegier les transporteurs locaux pour les transports locaux (eviter de bloquer un transporteur qui effectue des trajets Lausanne/Geneve sur un transport local)
			if (!($this->transport->checkLongDistance_Geneve()) && !($this->transport->checkLongDistance_Lausanne())) {
				$this->is_transport_local = TRUE;

				foreach($matrix_ranking as $index => $transporteur) {
					$tmp_transporteur = new Transporteur($matrix_ranking[$index][0]);

					if ($tmp_transporteur->check_transports_geneve() || $tmp_transporteur->check_transports_lausanne()) {
						$matrix_ranking[$index][6] = 1;
					} else {
						$matrix_ranking[$index][6] = 0;
					}

				}
			} else {
				$this->is_transport_local = FALSE;

				foreach($matrix_ranking as $index => $transporteur) {
					$matrix_ranking[$index][6] = 0;
				}
			}


			//export le resultat dans le CSV pour debug - données brutes
			$tmp_beneficiaire_nom_complet = $this->beneficiaire->get_nom_complet();

			$point_depart = $this->transport->get_point_depart();
			$point_arrivee = $this->transport->get_point_arrivee();

			$x=0;
			$matrix_export[$x][0] = 'Transport pour ' . mb_strtoupper(stripAccents($tmp_beneficiaire_nom_complet['nom'])) . ' '. stripAccents($tmp_beneficiaire_nom_complet['prenom']) . ' de ' . mb_strtoupper(stripAccents($point_depart['ville'])) . ' a ' . mb_strtoupper(stripAccents($point_arrivee['ville'])) . ', extraction des donnees brutes';

			$x++;

			$matrix_export[$x][0] = 'id';
			$matrix_export[$x][1] = 'nom_prenom';
			$matrix_export[$x][2] = 'distance_km_ville';
			$matrix_export[$x][3] = 'nbre_fois_deja_transporte_histo';
			$matrix_export[$x][4] = 'deja_transport_aujourdhui';
			$matrix_export[$x][5] = 'nbre_trajet_+-15_jours';
			$matrix_export[$x][6] = 'km_+-15_jours';
			$matrix_export[$x][7] = 'transport_locaux';

			$x++;

			$compteur = $x;
			foreach($matrix_ranking as $index => $row) {

				$matrix_export[$index + $x][0] = $matrix_ranking[$index][0];


				$tmp_transporteur_export = new Transporteur($matrix_ranking[$index][0]);
				$tmp_transporteur_nom_complet = $tmp_transporteur_export->get_nom_complet();

				$matrix_export[$index + $x][1] = mb_strtoupper(stripAccents($tmp_transporteur_nom_complet['nom'])) . '_' . stripAccents($tmp_transporteur_nom_complet['prenom']);


				for ($i=1; $i<=6; $i++) {
					$matrix_export[$index + $x][$i+1] = $matrix_ranking[$index][$i];
				}

				$compteur++;
			}


			for ($i=0; $i<=2; $i++) {
				$compteur++;
				$matrix_export[$compteur][0] = '';
			}






			//construit la matrix de ranking

			//norme sur 100
			foreach($matrix_ranking as $index => $transporteur) {
				foreach ($transporteur as $index_2 => $row_2) {

					if ($index_2 == 0) { //id et non rank
						continue;
					}

					if ( !isset($array_max[$index_2]) || $row_2 > $array_max[$index_2] ) {
						$array_max[$index_2] = $row_2;
					}

					if ( !isset($array_min[$index_2]) || $row_2 < $array_min[$index_2] ) {
						$array_min[$index_2] = $row_2;
					}

				}
			}


			//NORME
			foreach($matrix_ranking as $index_2 => $row) {
				$matrix_ranking_norme[$index_2][0] = $row[0];
			}

			$nbre_km_trajet = $this->transport->get_nbre_kilometres();

			if ($nbre_km_trajet == 0) {
				if (checkInternetConnection('maps.google.com')) {
					$this->transport->updateDistanceAndCost();

					$nbre_km_trajet = $this->transport->get_nbre_kilometres();
				}

				if ($nbre_km_trajet == 0) {
					$nbre_km_trajet = 10;
				}
			}



			//weight vec dynamique en prenant en compte la distance a parcourir
			$tmp_filiale = new Filiale($_SESSION['filiale']['id']);
			$glm_weights = $tmp_filiale->get_glm_weights();
			$vec_weight = array();

			foreach ($glm_weights as $index => $weight) {
				if (strpos('weight' . $index, 'distance')) {
					$ponderation_distance = $weight;
				}
			}

			foreach ($glm_weights as $index => $weight) {
				if (strpos('weight' . $index, 'ville')) {
					$vec_weight[1] = ($ponderation_distance/$nbre_km_trajet) * $weight;
				} elseif (strpos('weight' . $index, 'habitude')) {
					$vec_weight[2] = $weight;
				} elseif (strpos('weight' . $index, 'journee')) {
					$vec_weight[3] = $weight;
				} elseif (strpos('weight' . $index, 'trajet')) {
					$vec_weight[4] = $weight;
				} elseif (strpos('weight' . $index, 'kilo')) {
					$vec_weight[5] = $weight;
				} elseif (strpos('weight' . $index, 'locaux')) {
					$vec_weight[6] = ($ponderation_distance/$nbre_km_trajet)  * $weight;
				}
			}

			/*
			$vec_weight[1] = (10/$nbre_km_trajet) * 0.35; //ville
			$vec_weight[2] = 0.05; //habitude
			$vec_weight[3] = 0.25; //deja un transport la meme journee
			$vec_weight[4] = 0.25; //nbre transport meme mois
			$vec_weight[5] = 0.10; //nbre km meme mois
			$vec_weight[6] = ($nbre_km_trajet/10) * 0.20; //trajets locaux doivent si possible etre realises par des transporteurs qui effectuent des faibles distances
			*/

			foreach($matrix_ranking as $index_2 => $row) {
				//distance ville - small is best
				if ($array_max[1] == $array_min[1]) {
					$matrix_ranking_norme[$index_2][1] = 0;
				} else {
					$test = $row[1];
					$test = floor(100*(($row[1]-$array_min[1])/($array_max[1]-$array_min[1])));
					$matrix_ranking_norme[$index_2][1] = 100 - floor(100*(($row[1]-$array_min[1])/($array_max[1]-$array_min[1])));
				}


				//habitude big is best
				if ($array_max[2] == $array_min[2]) {
					$matrix_ranking_norme[$index_2][2] = 0;
				} else {
					$matrix_ranking_norme[$index_2][2] = floor(100*(($row[2]-$array_min[2])/($array_max[2]-$array_min[2])));
				}


				//deja un transport la meme journee small is best
				if ($array_max[3] == $array_min[3]) {
					$matrix_ranking_norme[$index_2][3] = 0;
				} else {
					$matrix_ranking_norme[$index_2][3] = 100 - floor(100*(($row[3]-$array_min[3])/($array_max[3]-$array_min[3])));
				}

				//nbre transports la meme mois small is best
				if ($array_max[4] == $array_min[4]) {
					$matrix_ranking_norme[$index_2][4] = 0;
				} else {
					$matrix_ranking_norme[$index_2][4] = 100 - floor(100*(($row[4]-$array_min[4])/($array_max[4]-$array_min[4])));
				}


				//nbre km la meme mois small is best
				if ($array_max[5] == $array_min[5]) {
					$matrix_ranking_norme[$index_2][5] = 0;
				} else {
					$matrix_ranking_norme[$index_2][5] = 100 - floor(100*(($row[5]-$array_min[5])/($array_max[5]-$array_min[5])));
				}


				//transports locaux small is best
				if ($array_max[6] == $array_min[6]) {
					$matrix_ranking_norme[$index_2][6] = 0;
				} else {
					$matrix_ranking_norme[$index_2][6] = 100 - floor(100*(($row[6]-$array_min[6])/($array_max[6]-$array_min[6])));
				}



				//overall ranking - produit matriciel
				for ($i=1; $i<=6; $i++) {
					if ($i == 1) {
						$matrix_ranking_norme[$index_2]['rank'] = 0;
					}

					$matrix_ranking_norme[$index_2]['rank'] = $matrix_ranking_norme[$index_2]['rank'] + $vec_weight[$i] * $matrix_ranking_norme[$index_2][$i];
				}

			}

			$x = $compteur;
			//export la matrix ranké pour avoir une idée des données
			$matrix_export[$x][0] = 'Note pour le transport pour ' . mb_strtoupper(stripAccents($tmp_beneficiaire_nom_complet['nom'])) . ' '. stripAccents($tmp_beneficiaire_nom_complet['prenom']) . ' de ' . mb_strtoupper(stripAccents($point_depart['ville'])) . ' a ' . mb_strtoupper(stripAccents($point_arrivee['ville']));

			$x++;


			$matrix_export[$x][0] = 'id';
			$matrix_export[$x][1] = 'nom_prenom';
			$matrix_export[$x][2] = 'note_ville';
			$matrix_export[$x][3] = 'note_habitude';
			$matrix_export[$x][4] = 'note_deja_transport_aujourdhui';
			$matrix_export[$x][5] = 'note_nbre_trajet';
			$matrix_export[$x][6] = 'note_km';
			$matrix_export[$x][7] = 'note_transport_locaux';
			$matrix_export[$x][8] = 'note_globale_ponderee';

			$x++;


			$matrix_export[$x][0] = '""';
			$matrix_export[$x][1] = '""';
			$matrix_export[$x][2] = $vec_weight[1];
			$matrix_export[$x][3] = $vec_weight[2];
			$matrix_export[$x][4] = $vec_weight[3];
			$matrix_export[$x][5] = $vec_weight[4];
			$matrix_export[$x][6] = $vec_weight[5];
			$matrix_export[$x][7] = $vec_weight[6];
			$matrix_export[$x][8] = '""';

			$x++;


			foreach($matrix_ranking_norme as $index => $row) {

				$matrix_export[$index + $x][0] = $matrix_ranking_norme[$index][0];


				$tmp_transporteur_export = new Transporteur($matrix_ranking_norme[$index][0]);
				$tmp_transporteur_nom_complet = $tmp_transporteur_export->get_nom_complet();

				$matrix_export[$index + $x][1] = mb_strtoupper(stripAccents($tmp_transporteur_nom_complet['nom'])) . '_' . stripAccents($tmp_transporteur_nom_complet['prenom']);


				for ($i=1; $i<=6; $i++) {
					$matrix_export[$index + $x][$i+1] = $matrix_ranking_norme[$index][$i];
				}
				$matrix_export[$index + $x][8] = $matrix_ranking_norme[$index]['rank'];
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


			//s'assure que le directory d'extract est deja existant
			global $cfg;
			if (!is_dir('../' . $cfg['DIRECTORY']['extract'])) {
				mkdir('../' . $cfg['DIRECTORY']['extract']);
			}

			$file_export_glm = fopen('../' . $cfg['DIRECTORY']['extract'] . '/export_glm.csv', 'wb');
			fwrite($file_export_glm, $str);


			//stock le resultat rank dans une nouvelle matrix
			foreach($matrix_ranking_norme as $index_2 => $row) {
				$array_to_sort[$row[0]] = $row['rank'];
			}

			//tri DESC
			arsort($array_to_sort);

			$this->array_transporteur = array();
			foreach ($array_to_sort as $index => $transporteur) {
				$this->array_transporteur[] = new Transporteur($index);
			}

		}

	} // class.GLM.func.__construct


	private function get_transporteur_disponible_date_periode($id_jour_semaine=0, $id_periode_journee=0, $is_geneve=0, $is_lausanne=0) {

		if (isset($id_jour_semaine) && $id_jour_semaine instanceof Jour_Semaine) {
			$id_jour_semaine = $id_jour_semaine->get_id();
		} else {
			$id_jour_semaine = $this->id_jour_semaine;
		}

		if (isset($id_periode_journee) && $id_periode_journee instanceof Periode_Journee) {
			$id_periode_journee = $id_periode_journee->get_id();
		} else {
			$id_periode_journee = $this->id_periode_journee;
		}

		global $dbh;
		$sql = "SELECT benevole_participation_filiale.id ";
		$sql .= " FROM benevole_participation_filiale INNER JOIN benevole_disponibilite_standard ON benevole_participation_filiale.id = benevole_disponibilite_standard.id_benevole ";
		$sql .= " WHERE benevole_participation_filiale.id_filiale=" . $this->filiale->get_id();
		$sql .= " AND benevole_participation_filiale.is_transporteur=1 ";
		$sql .= " AND benevole_disponibilite_standard.id_categorie=" . Benevole_Disponibilite_Categorie::get_id_from_nom('transport');
		$sql .= " AND benevole_disponibilite_standard.id_jour_semaine=$id_jour_semaine";
		$sql .= " AND benevole_disponibilite_standard.id_periode_journee=$id_periode_journee";


		if ($is_lausanne == 1 || ($this->transport instanceof Transport && $this->transport->checkLongDistance_Lausanne())) {
			$sql .= " AND benevole_participation_filiale.do_transports_lausanne = 1";
		}

		if ($is_geneve == 1 || ($this->transport instanceof Transport && $this->transport->checkLongDistance_Geneve())) {
			$sql .= " AND benevole_participation_filiale.do_transports_geneve = 1";
		}

		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		if (count($result) > 0) {
			foreach ($result as $row) {
				$this->array_transporteurs_disponible_date_and_periode[] = $row['id'];
			}
		}
	} // class.GLM.func.get_transporteur_disponible_date_periode


	private function get_transporteur_inactive() {
		global $dbh;

		$sql = "SELECT id ";
		$sql .= " FROM benevole_participation_filiale ";
		$sql .= " WHERE is_active=0";

		$sth = $dbh->query($sql);

		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		if (count($result) > 0) {
			foreach ($result as $row) {
				$this->array_transporteurs_inactive[] = $row['id'];
			}
		}
	} // class.GLM.func.get_transporteur_inactive


	private function get_transporeur_non_disponibilite_date($date='') {
		if ($date == '') {
			$date = $this->transport->get_date();
		}

		global $dbh;

		$date = $dbh->quote($date);

		$sql = "SELECT id_benevole ";
		$sql .= " FROM benevole_non_disponibilite_date ";
		$sql .= " WHERE id_categorie=" . Benevole_Disponibilite_Categorie::get_id_from_nom('transport');
		$sql .= " AND date_custom=$date";

		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		if (count($result) > 0) {
			foreach ($result as $row) {
				$this->array_transporteurs_non_disponible_date[] = $row['id_benevole'];
			}
		}
	} // class.GLM.func.get_transporteur_non_disponibilite_date


	private function get_transporteur_contrainte_beneficaire() {
		global $dbh;

		$sql = "SELECT id_transporteur FROM contrainte_transporteur_beneficiaire ";
		$sql .= " WHERE id_beneficiaire=" . $this->transport->get_id_beneficiaire();

		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		if (count($result) > 0) {
			foreach ($result as $row) {
				$this->array_transporteurs_contrainte_beneficaire[] = $row['id_transporteur'];
			}
		}
	} // class.GLM.func.get_transporteur_contrainte_beneficiaire


	private function get_transporteur_already_transport_date($date='', $id_periode_journee='') {

		//retourne les transporteurs qui ont deja un transport ce jour la mais a une periode differentes (reste donc potentiellement disponible)
		if ($date == '') {
			$date = $this->transport->get_date();
		}

		if ($id_periode_journee == '') {
			$id_periode_journee = $this->get_id_periode();
		} elseif ($id_periode_journee instanceof Periode_Journee) {
			$id_periode_journee = $id_periode_journee->get_id();
		}

		global $dbh;

		$date = $dbh->quote($date);

		$sql = "SELECT transport_transporteur.id_transporteur, heure_debut ";
		$sql .= " FROM transport_transporteur INNER JOIN transport on transport_transporteur.id_transport = transport.id ";
		$sql .= " WHERE transport.date_transport=$date";
		$sql .= " AND transport.is_annule=0";

		$sth = $dbh->query($sql);

		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		if (count($result) > 0) {
			$array_same_periode = array();

			foreach ($result as $row) {
				if (Periode_Journee::get_id_periode_from_time($row['heure_debut']) != $id_periode_journee) {

					if (!in_array($row['id_transporteur'], $this->array_transporteurs_deja_transporteurs_pour_date_transport)) {
						$this->array_transporteurs_deja_transporteurs_pour_date_transport[] = $row['id_transporteur'];
					}

				} else {
					$array_same_periode[] = $row['id_transporteur'];
				}

				$this->array_transporteurs_deja_transporteurs_pour_date_transport = array_diff($this->array_transporteurs_deja_transporteurs_pour_date_transport, $array_same_periode);
			}
		}

	} // class.GLM.func.get_transporteur_already_transport_date


	private function get_transporteur_already_transport_date_and_period($date='', $id_periode_journee='') {
		if ($date == '') {
			$date = $this->transport->get_date();
		}

		if ($id_periode_journee == '') {
			$id_periode_journee = $this->get_id_periode();
		} elseif ($id_periode_journee instanceof Periode_Journee) {
			$id_periode_journee = $id_periode_journee->get_id();
		}

		global $dbh;

		$date = $dbh->quote($date);

		$sql = "SELECT transport_transporteur.id_transporteur, transport.* ";
		$sql .= " FROM transport_transporteur INNER JOIN transport ON transport_transporteur.id_transport = transport.id ";
		$sql .= " WHERE transport.date_transport=$date";
		$sql .= " AND transport.is_annule=0";

		$sth = $dbh->query($sql);

		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		if (count($result) > 0) {
			foreach ($result as $row) {
				if (Periode_Journee::get_id_periode_from_time($row['heure_debut']) == $id_periode_journee) {
					$this->array_transporteurs_deja_transporteurs_pour_date_et_periode_transport[] = $row['id_transporteur'];
				}
			}
		}

	} // class.GLM.func.get_transporteur_already_transport_date_and_period


	private function get_transporteur_inatteignable() {
		$date = date('Y-m-d');

		global $dbh;

		$sql = "SELECT id_benevole ";
		$sql .= " FROM benevole_non_disponibilite_date ";
		$sql .= " WHERE date_custom=" . $dbh->quote(date('Y-m-d'));
		$sql .= " AND id_categorie=" . Benevole_Disponibilite_Categorie::get_id_from_nom('atteignable');

		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		if (count($result) > 0) {
			foreach ($result as $row) {
				$this->array_transporteurs_inatteignable_aujourdhui[] = $row['id_benevole'];
			}
		}
	}


	private function get_transporteur_already_permanence_date($date='') {
		if ($date == '') {
			$date = $this->transport->get_date();
		}

		global $dbh;

		$date = $dbh->quote($date);

		//uniquement si le matin
		if ($this->id_periode_journee == Periode_Journee::get_id_from_nom('matin')) {

			$sql = "SELECT id_permanencier ";
			$sql .= " FROM permanence ";
			$sql .= " WHERE id_filiale=" . $_SESSION['filiale']['id'];
			$sql .= " AND date=" . $date;

			$sth = $dbh->query($sql);
			$result = $sth->fetch(PDO::FETCH_ASSOC);

			if ($result != false) {
				$this->array_transporteur_a_la_permanence[] = $result['id_permanencier'];
			}

		}


	} // class.GLM.func.get_transporteur_already_permanence_date


	public function get_chauffeurs_potentiels() {
		return $this->array_transporteur;
	} // class.GLM.func.get_chauffeurs_potentiels


	private function get_id_periode() {
		return $this->id_periode_journee;
	} // class.GLM.func.get_id_periode
} // class.GLM

?>
