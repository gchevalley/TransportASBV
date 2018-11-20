<?php

require_once( str_replace ( '\\', '/', dirname(dirname(dirname(__FILE__)))) . '/admin/class.declaration.php' );


class Geocode {
	private $id = 0;
	private $adresse = '';
	private $adresse_complement = '';
	private $npa = '';
	private $ville = '';
	private $pays = '';
	private $lat = 0;
	private $lng = 0;


	function __construct($id_geocode, $adresse='', $adresse_complement='', $npa='', $ville='', $pays='', $lat=0, $lng=0) {
		if (is_numeric($id_geocode) && Geocode::id_exists($id_geocode)) {

			$this->id = $id_geocode;
			$this->mountAttributsFromDB();

		} else { //creation de la nouvelle entite
			$this->addEntryDB($adresse, $adresse_complement, $npa, $ville, $pays, $lat, $lng);
		}
	} // class.Geocode.func___construct


	private function mountAttributsFromDB() {

		//charge les donnees direct depuis la DB
		global $dbh;

		//mount la totalite des donnees
		$sql = "SELECT * FROM geocode WHERE id=" .$this->id;

		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		//s'assurer qu'un resultat est retourne bien alloue les donnees aux attributs de l'object
		$this->adresse = $result['adresse'];
		$this->adresse_complement = $result['adresse_complement'];
		$this->npa = $result['npa'];
		$this->ville = $result['ville'];
		$this->pays = $result['pays'];
		$this->lat = $result['lat'];
		$this->lng = $result['lng'];

	} // class.Geocode.func.mountAttributsFromDB


	private function addEntryDB($adresse='', $adresse_complement='', $npa='', $ville='', $pays='', $lat=0, $lng=0) {

		$load_needed_class_and_interface = load_class_and_interface(array('Benevole'));

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

		global $dbh;

		//si les coordonnees ne sont pas fourni les telecharger avec google maps
		if ($lat == 0 || $lng == 0) {
			$coor[0]['adresse'] = $adresse;
			$coor[0]['adresse_complement'] = $adresse_complement;
			$coor[0]['npa'] = $npa;
			$coor[0]['ville'] = $ville;
			$coor[0]['pays'] = $pays;

			if (checkInternetConnection('maps.google.com')) {
				$array_coor = Geocode::gmap_geocoding($coor);
				$id_genocode = $array_coor[0]['id'];
				$lat = $array_coor[0]['lat'];
				$lng = $array_coor[0]['lng'];

				$this->id = $id_genocode;
				$this->mountAttributsFromDB();
			}


		//si des valeurs pour lat & lng sont dispo on insere ces donnees dans la DB
		} else {

			//processing de nettoyage des args de la fonction
			$adresse = $dbh->quote($adresse);
			$adresse_complement = $dbh->quote($adresse_complement);
			$npa = $dbh->quote($npa);
			$ville = $dbh->quote($ville);
			$pays = $dbh->quote($pays);

			//creation de la nouvelle entite dans la db
			$sql = "INSERT INTO geocode (adresse, adresse_complement, npa, ville, pays, lat, lng) ";
			$sql .= "VALUES (" . $adresse . ", " . $adresse_complement . ", " . $npa . ", " . $ville . ", " . $pays . ", " . $lat . ", " . $lng . ")";

			$statut_query = $dbh->exec($sql);

			//mount l'object
			$this->id = $dbh->lastInsertId();
			$this->mountAttributsFromDB();

		}


		//ajoute le lieu base sur la ville si n'existe pas deja
		if ($this->npa != '' && $this->ville != '' && $this->pays) {
			$load_needed_class_and_interface = load_class_and_interface(array('Lieu'));
			Lieu::ajouterVille($this->ville, $this->npa, $this->pays);
		}

	} // class.Geocode.func.addEntryDB


	public static function gmap_geocoding($adresses) {
		global $dbh;

		// The daily limit on the Geocoder Web Service that you are using is 2,500 per day

		// increase 60 seconds to 1000 seconds for this
		set_time_limit(1000); // 1000 seconds


		define("MAPS_HOST", "maps.google.com");
		define("MAPS_OUTPUT", "xml");

		define("MAPS_STATUS_OK",   			   "OK");
		define("MAPS_STATUS_ZERO", 			   "ZERO_RESULTS");
		define("MAPS_STATUS_OVER_QUERY_LIMIT", "OVER_QUERY_LIMIT");
		define("MAPS_STATUS_REQUEST_DENIED",   "OK");
		define("MAPS_STATUS_INVALID_REQUEST",  "INVALID_REQUEST");

		/*
		"OK" indicates that no errors occurred; the address was successfully parsed and at least one geocode was returned.
		"ZERO_RESULTS" indicates that the geocode was successful but returned no results. This may occur if the geocode was passed a non-existent address or a latlng in a remote location.
		"ZERO_RESULTS" indicates that you are over your quota.
		"REQUEST_DENIED" indicates that your request was denied, generally because of lack of a sensor parameter.
		"INVALID_REQUEST" generally indicates that the query (address or latlng) is missing.
		*/


		// google url with false sensor and singapore bounds
		// Initialize delay in geocode speed
		//$delay = 100000; // 10seconds
		$delay = 20000;
		global $cfg;
		//$base_url = "http://" . MAPS_HOST . "/maps/api/geocode/". MAPS_OUTPUT ."?" . "&sensor=false";
		$base_url = "https://" . MAPS_HOST . "/maps/api/geocode/". MAPS_OUTPUT ."?" . "&sensor=false&key=" . $cfg['APIGOOG']['apikey'];

		$array_output = array();

		// Iterate through the rows, geocoding each address
		foreach($adresses as $row){

			$adresse = $dbh->quote($row['adresse']);
			$npa = $dbh->quote($row['npa']);
			$ville = $dbh->quote($row['ville']);
			$pays = $dbh->quote($row['pays']);

			$sql = "SELECT * FROM geocode ";
			$sql .= " WHERE adresse=" . $adresse;
			$sql .= " AND npa=" . $npa;
			$sql .= " AND ville=" . $ville;
			$sql .= " AND lat!=0.0";
			$sql .= " AND lng!=0.0";

			$sth = $dbh->query($sql);
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);

			if (count($result)> 0) {
				continue;
			}


			$geocode_pending = true;

			while ($geocode_pending) {

			//$address = stripAccents($row["adresse"]) . ',' . $row['npa'] . ',' . stripAccents($row['ville']);
			$address = stripAccents($row["adresse"]) . ',' . ',' . stripAccents($row['ville']);
			$id = $row['id'];


			$request_url = $base_url . "&address=" . urlencode($address).",Switzerland";
			$xml = simplexml_load_file($request_url);

			$status = $xml->status;

			if (strcmp($status, MAPS_STATUS_OK) == 0) {
				// Successful geocode
				$geocode_pending = false;
				$coordinates = $xml->result->geometry->location;
				// Format: Longitude, Latitude, Altitude
				$lat = (double) $coordinates->lat;
				$lng = (double) $coordinates->lng;

				// create db entry & data array
				unset($tmp_geocode);
				$tmp_geocode = new Geocode(0, $row['adresse'], '', $row['npa'], $row['ville'], $row['pays'], $lat, $lng);
				$array_output[] = array('id' => $tmp_geocode->get_id(), 'lat' => $lat, 'lng' => $lng);

			} else {
				// failure to geocode
				$geocode_pending = false;

				// slow down
				usleep($delay);

				//essai sans l'adresse
				//$address = $row['npa'] . ',' . stripAccents($row['ville']);
				$address = stripAccents($row['ville']) . ',' . stripAccents($row['pays']);
				$request_url = $base_url . "&address=" . urlencode($address);

				$status = $xml->status;

				if (strcmp($status, MAPS_STATUS_OK) == 0) {
					// Successful geocode
					$geocode_pending = false;
					$coordinates = $xml->result->geometry->location;
					// Format: Longitude, Latitude, Altitude
					$lat = (double) $coordinates->lat;
					$lng = (double) $coordinates->lng;

					// create data array

					unset($tmp_geocode);
					$tmp_geocode = new Geocode(0, $row['adresse'], '', $row['npa'], $row['ville'], $row['pays'], $lat, $lng);
					$array_output[] = array('id' => $row['id'] = $tmp_geocode->get_id(), 'lat' => $lat, 'lng' => $lng);

				} else {
					//impossible de trouver la positioin
				}

			}

			// failure to geocode
			$geocode_pending = false;

		  }
		}

		return $array_output;
	}


	public static function find_combination($adresse, $npa, $ville, $pays, $create_if_not_found=FALSE) {
		global $dbh;

		$quote_adresse = $dbh->quote($adresse);
		$quote_npa = $dbh->quote($npa);
		$quote_ville = $dbh->quote($ville);
		$quote_pays = $dbh->quote($pays);

		$sql = "SELECT id, lat, lng ";
		$sql .= " FROM geocode ";
		$sql .= " WHERE adresse=" . $quote_adresse;
		$sql .= " AND npa =" . $quote_npa;
		$sql .= " AND ville =" . $quote_ville;
		$sql .= " AND pays =" . $quote_pays;

		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		if ($result != false ) {
			return array('lat' => $result['lat'], 'lng' => $result['lng']);
		} else {
			if ($create_if_not_found === TRUE) {
				$tmp_geocode = new Geocode(0, $adresse, '', $npa, $ville, $pays);
				return $tmp_geocode->get_coordonnees();
			} else {
				return FALSE;
			}
		}

	} // class.Geocode.func.find_combination


	public static function id_exists($id_to_check) {
		if (checkID($id_to_check)) {
			global $dbh;
			$sql = "SELECT * FROM geocode WHERE id=" .$id_to_check;
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
	} //class.Geocode.func.id_exists


	public function get_id() {
		return $this->id;
	} // class.Geocode.func.get_id


	public function get_coordonnees() {
		return array('lat' => $this->lat, 'lng' => $this->lng);
	}

} // class.Geocode


class Direction {
	private $id = 0;

	private $depart_adresse = '';
	private $depart_npa = '';
	private $depart_ville = '';

	private $arrivee_adresse = '';
	private $arrivee_npa = '';
	private $arrivee_ville = '';

	private $nbre_kilometres = 0;


	function __construct($id_direction, $depart_adresse='', $depart_npa='', $depart_ville='', $arrivee_adresse='', $arrivee_npa='', $arrivee_ville='', $nbre_kilometres=0) {
		if (is_numeric($id_direction) && Direction::id_exists($id_direction)) {

			$this->id = $id_direction;
			$this->mountAttributsFromDB();

		} else { //creation de la nouvelle entite
			$this->addEntryDB($depart_adresse, $depart_npa, $depart_ville, $arrivee_adresse, $arrivee_npa, $arrivee_ville, $nbre_kilometres);
		}
	} // class.Direction.func___construct


	private function mountAttributsFromDB() {

		//charge les donnees direct depuis la DB
		global $dbh;

		//mount la totalite des donnees
		$sql = "SELECT * FROM direction WHERE id=" .$this->id;

		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		//s'assurer qu'un resultat est retourne bien alloue les donnees aux attributs de l'object
		$this->depart_adresse = $result['depart_adresse'];
		$this->depart_npa = $result['depart_npa'];
		$this->depart_ville = $result['depart_ville'];
		$this->arrivee_adresse = $result['arrivee_adresse'];
		$this->arrivee_npa = $result['arrivee_npa'];
		$this->arrivee_ville = $result['arrivee_ville'];
		$this->nbre_kilometres = $result['nbre_kilometres'];

	} // class.Direction.func.mountAttributsFromDB


	private function addEntryDB($depart_adresse, $depart_npa, $depart_ville, $arrivee_adresse, $arrivee_npa, $arrivee_ville, $nbre_kilometres=0) {

		$load_needed_class_and_interface = load_class_and_interface(array('Benevole', 'Trajet'));

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

		global $dbh;


		// si les km ne sont pas precise, calcul de la distance avec google maps
		if ($nbre_kilometres == 0) {
			if (checkInternetConnection('maps.google.com')) {
				$nbre_kilometres = Trajet_Pre_Defini::download_distance_from_google_maps($depart_adresse, $depart_npa, $depart_ville, $arrivee_adresse, $arrivee_npa, $arrivee_ville);
			}
		}

		if (is_numeric($nbre_kilometres) && $nbre_kilometres !== false && $nbre_kilometres > 0) {

			//processing de nettoyage des args de la fonction
			$depart_adresse = $dbh->quote($depart_adresse);
			$depart_npa = $dbh->quote($depart_npa);
			$depart_ville = $dbh->quote($depart_ville);
			$arrivee_adresse = $dbh->quote($arrivee_adresse);
			$arrivee_npa = $dbh->quote($arrivee_npa);
			$arrivee_ville = $dbh->quote($arrivee_ville);

			//creation de la nouvelle entite dans la db
			$sql = "INSERT INTO direction (depart_adresse, depart_npa, depart_ville, arrivee_adresse, arrivee_npa, arrivee_ville, nbre_kilometres) ";
			$sql .= "VALUES (" . $depart_adresse . ", " . $depart_npa . ", " . $depart_ville . ", " . $arrivee_adresse . ", " . $arrivee_npa . ", " . $arrivee_ville . ", " . $nbre_kilometres . ")";

			$statut_query = $dbh->exec($sql);

			//mount l'object
			$this->id = $dbh->lastInsertId();
			$this->mountAttributsFromDB();
		}

		/*
		//ajoute le lieu base sur la ville si n'existe pas deja
		if ($this->depart_npa != '' && $this->depart_ville != '') {
			$load_needed_class_and_interface = load_class_and_interface(array('Lieu'));
			Lieu::ajouterVille($this->depart_ville, $this->depart_npa);
		}

		if ($this->arrivee_npa != '' && $this->arrivee_ville != '') {
			$load_needed_class_and_interface = load_class_and_interface(array('Lieu'));
			Lieu::ajouterVille($this->arrivee_ville, $this->arrivee_npa);
		}
		*/

	} // class.Direction.func.addEntryDB


	public static function find_combination($adresse1, $npa1, $ville1, $adresse2, $npa2, $ville2) {
		global $dbh;

		$adresse1 = $dbh->quote($adresse1);
		$npa1 = $dbh->quote($npa1);
		$ville1 = $dbh->quote($ville1);
		$adresse2 = $dbh->quote($adresse2);
		$npa2 = $dbh->quote($npa2);
		$ville2 = $dbh->quote($ville2);

		$sql = "SELECT id, nbre_kilometres ";
		$sql .= " FROM direction ";
		$sql .= " WHERE ( " ;
			$sql .= " depart_adresse=" . $adresse1;
			$sql .= " AND depart_npa=" . $npa1;
			$sql .= " AND depart_ville=" . $ville1;
			$sql .= " AND arrivee_adresse=" . $adresse2;
			$sql .= " AND arrivee_npa=" . $npa2;
			$sql .= " AND arrivee_ville=" . $ville2;
		$sql .= " ) ";
		$sql .= " OR ( ";
			$sql .= " depart_adresse=" . $adresse2;
			$sql .= " AND depart_npa=" . $npa2;
			$sql .= " AND depart_ville=" . $ville2;
			$sql .= " AND arrivee_adresse=" . $adresse1;
			$sql .= " AND arrivee_npa=" . $npa1;
			$sql .= " AND arrivee_ville=" . $ville1;
		$sql .= " ) ";

		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		if ($result != false ) {
			return $result['nbre_kilometres'];
		} else {
			return FALSE;
		}

	} // class.Direction.func.find_combination


	public static function id_exists($id_to_check) {
		if (checkID($id_to_check)) {
			global $dbh;
			$sql = "SELECT * FROM direction WHERE id=" .$id_to_check;
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
	} //class.Direction.func.id_exists


	public function get_id() {
		return $this->id;
	} // class.Direction.func.get_id

	public function get_nbre_kilometres() {
		return $this->nbre_kilometres;
	} // class.Direction.func.get_nbre_kilometres

} // class.Direction


?>
