<?php

//include('../../config/auth/secure.php');
require_once( str_replace ( '\\', '/', dirname(dirname(__FILE__))) . '/class.declaration.php' );
$load_needed_class_and_interface = load_class_and_interface(array('Contact', 'Benevole', 'Beneficiaire'));


class Trajet_Pre_Defini {
	private $id = 0;
	private $lieu_1 = ''; // ville_1
	private $lieu_2 = ''; // ville_2
	private $distance = 0.0; // double
	private $distance_google_maps = 0.0; //


	function __construct($id_trajet_pre_defini, $lieu_1='', $lieu_2='', $distance=0.0) {
		if (is_numeric($id_trajet_pre_defini) && Trajet_Pre_Defini::id_exists($id_trajet_pre_defini)) {

			$this->id = $id_trajet_pre_defini;
			$this->mountAttributsFromDB();

		} else { //creation de la nouvelle entite
			$this->addEntryDB($lieu_1, $lieu_2, $distance);
		}
	} // class.Trajet_Pre_Defini.func.__construct


	private function mountAttributsFromDB() {
		global $dbh;

		$sql = "SELECT * FROM trajet_pre_defini WHERE id=" . $this->id;

		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		$this->lieu_1 = $result['lieu_1'];
		$this->lieu_2 = $result['lieu_2'];
		$this->distance = $result['distance'];
		$this->distance_google_maps = $result['distance_google_maps'];

	} // class.Trajet_Pre_Defini.func.mountAttributsFromDB


	private function addEntryDB($lieu_1, $lieu_2, $distance, $src='') {

		if (Benevole::id_exists($_SESSION['benevole']['id'])) {
			$tmp_benevole = new Benevole($_SESSION['benevole']['id']);

			if ($tmp_benevole->checkIsSuperAdmin()) {
				// continue l'execution de la function
			} else {
				if (Filiale::id_exists($_SESSION['filiale']['id'])) {
					if ($tmp_benevole->checkIsPermanencier($_SESSION['filiale']['id']) || $tmp_benevole->checkIsAdminOfFiliale($_SESSION['filiale']['id'])) {
						// continue l'execution de la function
					} else {
						//echo 'issue with permanencier';
						die();
					}
				} else {
					//echo 'issue with filiale';
					die();
				}
			}
		} else {
			//echo 'issue with benevole';
			die();
		}

		if ($lieu_1 == '' || $lieu_2 == '' || $distance == 0 || !is_numeric($distance)) {
			//echo 'issue no lieu1/2/0 distance/no numeric distance';
			return;
			die();
		}

		global $dbh;

		// s'assure que unique (si trouve et que distance differentes -> mise a jour
		$result = Trajet_Pre_Defini::find_combination($lieu_1, $lieu_2);
		if ($result) {
			$tmp_trajet_pre_defini = new Trajet_Pre_Defini($result['id']);

			if ($distance != $tmp_trajet_pre_defini->get_distance()) {
				$sql = "UPDATE trajet_pre_defini ";
				$sql .= " SET distance=" . $distance;
				$sql .= " WHERE id=" . $tmp_trajet_pre_defini->get_id();

				$statut_query = $dbh->exec($sql);
				$this->id = $tmp_trajet_pre_defini->get_id();
				$this->mountAttributsFromDB();

				exit();
			} else {
				exit();
			}
		} else {
			// l'entree n'est pas presente
		}

		//processing de nettoyage des args de la fonction
		$lieu_1 = $dbh->quote(mb_strtoupper(stripAccents($lieu_1)));
		$lieu_2 = $dbh->quote(mb_strtoupper(stripAccents($lieu_2)));

		//creation de la nouvelle entite dans la db
		if ($src = '') {
			$sql = "INSERT INTO trajet_pre_defini (lieu_1, lieu_2, distance) ";
			$sql .= "VALUES ($lieu_1, $lieu_2, $distance)";
		} elseif ($src = 'gmaps') {
			$sql = "INSERT INTO trajet_pre_defini (lieu_1, lieu_2, distance, distance_google_maps) ";
			$sql .= "VALUES ($lieu_1, $lieu_2, $distance, $distance)";
		} else {

		}


		$statut_query = $dbh->exec($sql);

		//mount l'object
		$this->id = $dbh->lastInsertId();
		$this->mountAttributsFromDB();

	} // class.Trajet_Pre_Defini.func.addEntryDB


	// $lieu_1 < $lieu_2
	public static function opti_find_combination($lieu_1, $lieu_2) { //autorise la reception d'un object contact

		global $dbh;

		$lieu_1 = $dbh->quote(mb_strtoupper(stripAccents($lieu_1)));
		$lieu_2 = $dbh->quote(mb_strtoupper(stripAccents($lieu_2)));

		$sql = "SELECT id, distance FROM trajet_pre_defini ";
		$sql .= " WHERE lieu_1=" . $lieu_1;
		$sql .= " AND lieu_2=" .$lieu_2;

		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		if ($result != false) {
			return $result;
		} else {
			return FALSE;
		}
	}



	public static function find_combination($lieu_1, $lieu_2) { //autorise la reception d'un object contact

		if ($lieu_1 instanceof Contact) {
			$array_lieu_1 = $lieu_1->get_adresse();

			if (isset($array_lieu_1['ville'])) {
				$lieu_1 = $array_lieu_1['ville'];
			} else {
				//echo 'issue no ville1';
				die();
			}
		}


		if ($lieu_2 instanceof Contact) {
			$array_lieu_2 = $lieu_2->get_adresse();

			if (isset($array_lieu_2['ville'])) {
				$lieu_2 = $array_lieu_2['ville'];
			} else {
				//echo 'issue no ville2';
				die();
			}
		}

		global $dbh;

		$lieu_1 = $dbh->quote(mb_strtoupper(stripAccents($lieu_1)));
		$lieu_2 = $dbh->quote(mb_strtoupper(stripAccents($lieu_2)));

		$sql = "SELECT id, distance FROM trajet_pre_defini WHERE (lieu_1=" . $lieu_1;
		$sql .= " AND lieu_2=" .$lieu_2;
		$sql .= " ) OR (lieu_1=" . $lieu_2;
		$sql .= " AND lieu_2=" . $lieu_1 . ")";

		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		if ($result != false) {
			return $result;
		} else {
			return FALSE;
		}
	}

	public static function id_exists($id_to_check) {
		if (checkID($id_to_check)) {
			global $dbh;
			$sql = "SELECT * FROM trajet_pre_defini WHERE id=" .$id_to_check;
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
	} //class.Trajet_Pre_Defini.func.id_exists


	public function get_id() {
		return $this->id;
	} // class.Trajet_Pre_Defini.func.get_id

	public function get_distance() {
		return $this->distance;
	} // class.Trajet_Pre_Defini.func_get_distance

	public function get_distance_google_maps() {
		return $this->distance_google_maps;
	}


	private static function get_ville_from_adresse($adresse) {
		if ($adresse instanceof Contact) {
			$array_adresse = $adresse->get_adresse();

			if (isset($array_adresse['ville'])) {
				return $array_adresse['ville'];
			} else {
				return FALSE;
			}

		} elseif (is_array($adresse) && isset($adresse['ville'])) {
			return $adresse['ville'];
		} else {
			return FALSE;
		}
	}


	private static function get_npa_from_adresse($adresse) {
		if ($adresse instanceof Contact) {
			$array_adresse = $adresse->get_adresse();

			if (isset($array_adresse['npa'])) {
				return $array_adresse['npa'];
			} else {
				return FALSE;
			}

		} elseif (is_array($adresse) && isset($adresse['npa'])) {
			return $adresse['npa'];
		} else {
			return FALSE;
		}
	}


	private static function get_pays_from_adresse($adresse) {
		if ($adresse instanceof Contact) {
			$array_adresse = $adresse->get_adresse();

			if (isset($array_adresse['pays'])) {
				return $array_adresse['pays'];
			} else {
				return FALSE;
			}

		} elseif (is_array($adresse) && isset($adresse['pays'])) {
			return $adresse['pays'];
		} else {
			return FALSE;
		}
	}


	public static function create_from_2_addreses($adresse_1, $adresse_2, $distance) {
		if (is_numeric($distance)) {

			$ville1 = Trajet_Pre_Defini::get_ville_from_adresse($adresse_1);
			$ville2 = Trajet_Pre_Defini::get_ville_from_adresse($adresse_2);

			$npa1 = Trajet_Pre_Defini::get_npa_from_adresse($adresse_1);
			$npa2 = Trajet_Pre_Defini::get_npa_from_adresse($adresse_2);

			$pays1 = Trajet_Pre_Defini::get_pays_from_adresse($adresse_1);
			$pays2 = Trajet_Pre_Defini::get_pays_from_adresse($adresse_2);

			if ($ville1 === false || $ville2 === false) {
				//echo 'issue no villes';
				die();
			} else {
				if ($distance == 0 || $distance == '') {
					//recuperation avec gmaps
					if (checkInternetConnection('maps.google.com') && $npa1 != '' && $ville1 != '' && $npa2 != '' && $ville2 != '') {
						$distance = Trajet_Pre_Defini::download_distance_from_google_maps('', $npa1, $ville1, $pays1, '', $npa2, $ville2, $pays2);
					}

				}

				$tmp_trajet_pre_defini = new Trajet_Pre_Defini(0, $ville1, $ville2, $distance);
				return $tmp_trajet_pre_defini->get_id();
			}


		} else {
			return FALSE;
		}

	} // Class.Trajet_Pre_Defini.func.create_from_2_addresses


	public static function download_distance_from_google_maps($adresse1, $npa1, $ville1, $pays1, $adresse2, $npa2, $ville2, $pays2, $create_entry_trajet_pre_defini=TRUE) {

		if (checkInternetConnection('maps.google.com')) {

			// l'adresse est facultative
			if ($adresse1 != '') {
				$adresse1_without_accent = str_replace(' ', '+', stripAccents($adresse1)) . ',+';
			} else {
				$adresse1_without_accent = '';
			}

			if ($adresse2 != '') {
				$adresse2_without_accent = str_replace(' ', '+', stripAccents($adresse2)) . ',+';
			} else {
				$adresse2_without_accent = '';
			}

			if ($ville1 != '') {
				$ville1_without_accent = str_replace(' ', '+', stripAccents($ville1)) . ',+';
			}


			if ($ville2 != '') {
				$ville2_without_accent = str_replace(' ', '+', stripAccents($ville2)) . ',+';
			}


			if ($pays1 != '') {
				$pays1_without_accent = str_replace(' ', '+', stripAccents($pays1));
			}


			if ($pays2 != '') {
				$pays2_without_accent = str_replace(' ', '+', stripAccents($pays2));
			}

			global $cfg;
			//$url='http://maps.google.com/maps/api/directions/xml?language=fr&origin=' . $adresse1_without_accent . $ville1_without_accent . $pays1_without_accent . '&destination=' . $adresse2_without_accent . $ville2_without_accent . $pays2_without_accent . '&sensor=false';
			$url='https://maps.googleapis.com/maps/api/directions/xml?language=fr&origin=' . $adresse1_without_accent . $ville1_without_accent . $pays1_without_accent . '&destination=' . $adresse2_without_accent . $ville2_without_accent . $pays2_without_accent . '&sensor=false&key=' . $cfg['APIGOOG']['apikey'];

			$xml=file_get_contents($url);
			$root = simplexml_load_string($xml);

			$status_query = (string) $root->status;
			if ($status_query != 'OK') {
				return FALSE;
			}

			//s'assure que le calcul de la distance s'effectue bien sur la bonne ville de départ et d'arrivée
			$return_ville_gmaps_depart = (string) $root->route->leg->start_address;
			$return_ville_gmaps_arrivee = (string) $root->route->leg->end_address;

			$bug_direction = FALSE;
			$bug_direction_depart = FALSE;
			$bug_direction_arrivee = FALSE;


			// un prefix depart / arrivee est utilisé car si la chaine recherchée se trouve au tout début c'est la postion 0 qui sera retournée par strpos ce qui sera interprété comme FALSE par le if
			if (!strpos(mb_strtoupper(stripAccents('depart:' . $return_ville_gmaps_depart)), mb_strtoupper(stripAccents($ville1)))) {

				$array_output_gmap = array();
				$array_output_gmap = explode(',', $return_ville_gmaps_depart);

				foreach ($array_output_gmap as $index => $row) {
					$array_output_gmap[$index] = trim($row);
				}

				//retire le npa
				$ville_gmaps = preg_replace('/[0-9]{4,}/', '', $array_output_gmap[count($array_output_gmap) - 1 - 1]);
				$ville_gmaps = trim($ville_gmaps);

				$compact_adresse_input = preg_replace('/[\,\. \(\)\-]/', '', mb_strtolower(stripAccents($ville1)));
				$compact_adresse_output_gmail = preg_replace('/[\,\. \(\)\-]/', '', mb_strtolower(stripAccents($ville_gmaps)));

				$test =  levenshtein($compact_adresse_input , $compact_adresse_output_gmail);

				if ($test > 4) {
					$bug_direction = TRUE;
					$bug_direction_depart = TRUE;
				}

			}

			if (!strpos(mb_strtoupper(stripAccents('arrivee:' . $return_ville_gmaps_arrivee)), mb_strtoupper(stripAccents($ville2)))) {

				$array_output_gmap = array();
				$array_output_gmap = explode(',', $return_ville_gmaps_arrivee);

				foreach ($array_output_gmap as $index => $row) {
					$array_output_gmap[$index] = trim($row);
				}

				//retire le npa
				$ville_gmaps = preg_replace('/[0-9]{4,}/', '', $array_output_gmap[count($array_output_gmap) - 1 - 1]);
				$ville_gmaps = trim($ville_gmaps);

				$compact_adresse_input = preg_replace('/[\,\. \(\)\-]/', '', mb_strtolower(stripAccents($ville2)));
				$compact_adresse_output_gmail = preg_replace('/[\,\. \(\)\-]/', '', mb_strtolower(stripAccents($ville_gmaps)));

				$test =  levenshtein($compact_adresse_input , $compact_adresse_output_gmail);

				if ($test > 4) {
					$bug_direction = TRUE;
					$bug_direction_arrivee = TRUE;
				}
			}

			if ($bug_direction) {
				//nouvelle appel a gmaps necessaire

				if ($bug_direction_depart === TRUE) {
					$new_adresse1 = '';
				} else {
					$new_adresse1 = $adresse1;
				}

				if ($bug_direction_arrivee === TRUE) {
					$new_adresse2 = '';
				} else {
					$new_adresse2 = $adresse2;
				}

				// pour eviter de partir dans une boucle infinie
				if (!isset($_SESSION['GMAPS']['try'])) {
					$_SESSION['GMAPS']['try'] = TRUE;
					return Trajet_Pre_Defini::download_distance_from_google_maps($new_adresse1, $npa1, $ville1, $pays1, $new_adresse2, $npa2, $ville2, $pays2, FALSE);
				} else {
					/*la seconde requete conduit à nouveau à un bug
					 * impossible de calculer la distance
					 */

					unset($_SESSION['GMAPS']['try']);
					return FALSE;
				}

			}


			unset($_SESSION['GMAPS']['try']);
			$distance=$root->route->leg->distance->value;

			$output_array_google_maps = array(
				'distanceEnKm'=>(double) $distance/1000,
				'adresseDepart'=>$root->route->leg->start_address,
				'adresseArrivee'=>$root->route->leg->end_address
			);

			//creation d'une nouvelle entree dans la DB pour eviter de devoir redownloader une meme distance entre 2 villes
			if ($create_entry_trajet_pre_defini === TRUE) {
				if (!Trajet_Pre_Defini::find_combination($ville1, $ville2)) {
					if ((empty($adresse1) && empty($adresse2)) || ($adresse1 == '' && $adresse2 = '')) {
						$tmp_trajet_pre_defini = new Trajet_Pre_Defini(0, $ville1, $ville2, $output_array_google_maps['distanceEnKm'], 'gmaps');
					} else {
						if ($ville1 != $ville2) {
							//nouvelle requete de centre-ville depart a centre-ville arrivee
							$url='http://maps.google.com/maps/api/directions/xml?language=fr&origin=' . $ville1_without_accent . $pays1_without_accent . '&destination=' . $ville2_without_accent . $pays2_without_accent . '&sensor=false';
							$xml=file_get_contents($url);
							$root = simplexml_load_string($xml);
							$distance=$root->route->leg->distance->value;

							$output_array_google_maps_for_db = array('distanceEnKm'=> (double) $distance/1000);

							if ($output_array_google_maps_for_db['distanceEnKm'] > 0) {
								$tmp_trajet_pre_defini = new Trajet_Pre_Defini(0, $ville1, $ville2, $output_array_google_maps_for_db['distanceEnKm'], 'gmaps');
							}

						} else {
							$tmp_trajet_pre_defini = new Trajet_Pre_Defini(0, $ville1, $ville2, 5);
						}
					}
				}
			}

			return $output_array_google_maps['distanceEnKm'];

		} else {

			return FALSE;

		}

	}

} // Class.Trajet_Pre_Defini

?>
