<?php

$check_loading_class = load_class_and_interface(array('Transport', 'Transport_Categorie', 'Benevole', 'Transporteur', 'Beneficiaire'));

$variables_to_clean = array();

if ( array_key_exists('id', $_POST) ) {
	$variables_to_clean['id'] = array('value' => $_POST['id'], 'type' => 'id', 'class' => 'transport');
}

//si reload, alors prend le benevole de la session stocke
if ( isset($_SESSION['last_page']['data_to_display']['id_beneficiaire']) && Beneficiaire::id_exists($_SESSION['last_page']['data_to_display']['id_beneficiaire']) ) {
	$variables_to_clean['id_beneficiaire'] = array('value' => $_SESSION['last_page']['data_to_display']['id_beneficiaire'], 'type' => 'id', 'option' => array('required' => TRUE), 'class' => 'transport');

	//une fois recuperee destruction de la variable
	unset($_SESSION['last_page']['data_to_display']['id_beneficiaire']);
} elseif ( array_key_exists('id_beneficiaire', $_POST) ) {
		$variables_to_clean['id_beneficiaire'] = array('value' => $_POST['id_beneficiaire'], 'type' => 'id', 'option' => array('required' => TRUE), 'class' => 'transport');
}


if (isset($_SESSION['last_page']['data_to_display']['id_categorie']) && Transport_Categorie::id_exists($_SESSION['last_page']['data_to_display']['id_categorie'])) {
	$variables_to_clean['id_categorie'] = array('value' => $_SESSION['last_page']['data_to_display']['id_categorie'], 'type' => 'id', 'option' => array('required' => TRUE), 'class' => 'transport');

	unset($_SESSION['last_page']['data_to_display']['id_categorie']);
} elseif ( array_key_exists('id_categorie', $_POST) ) {
	$variables_to_clean['id_categorie'] = array('value' => $_POST['id_categorie'], 'type' => 'id', 'option' => array('required' => TRUE), 'class' => 'transport');
}

if ( array_key_exists('date_transport', $_POST) ) {
	$variables_to_clean['date_transport'] = array('value' => $_POST['date_transport'], 'type' => 'date', 'option' => array('required' => TRUE), 'class' => 'transport');

	//convertir la date en format mysql si necessaire
	if (strpos($variables_to_clean['date_transport']['value'], '.') !== false) {
		$date_txt = explode('.', $variables_to_clean['date_transport']['value']);
		$variables_to_clean['date_transport']['value'] = $date_txt[2] . '-' . $date_txt[1] . '-' . $date_txt[0];
	}
}

if ( array_key_exists('heure_debut_heure', $_POST) ) {
	$variables_to_clean['heure_debut'] = array('value' => $_POST['heure_debut_heure'] . ':' . $_POST['heure_debut_minute'], 'type' => 'time', 'option' => array('required' => TRUE), 'class' => 'transport');
}

if ( array_key_exists('duree_approximative', $_POST) ) {
	$variables_to_clean['duree_approximative'] = array('value' => $_POST['duree_approximative'], 'option' => array('required' => TRUE), 'type' => 'double', 'class' => 'transport');
}

if ( array_key_exists('nbre_kilometres', $_POST) ) {
	$variables_to_clean['nbre_kilometres'] = array('value' => $_POST['nbre_kilometres'], 'type' => 'double', 'class' => 'transport');
}

if ( array_key_exists('aller_retour', $_POST) ) {
	$variables_to_clean['aller_retour'] = array('value' => $_POST['aller_retour'], 'type' => 'bool', 'option' => array('required' => TRUE), 'class' => 'transport');
}

if ( array_key_exists('cout_trajet', $_POST) ) {
	$variables_to_clean['cout_trajet'] = array('value' => $_POST['cout_trajet'], 'type' => 'double', 'class' => 'transport');
}

if ( array_key_exists('cout_variable', $_POST) ) {
	$variables_to_clean['cout_variable'] = array('value' => $_POST['cout_variable'], 'type' => 'double', 'class' => 'transport');
}

if ( array_key_exists('taux_remboursement_transporteur', $_POST) ) {
	$variables_to_clean['taux_remboursement_transporteur'] = array('value' => $_POST['taux_remboursement_transporteur'], 'type' => 'double', 'class' => 'transport');
}

if ( array_key_exists('info_diverses', $_POST) ) {
	$variables_to_clean['info_diverses'] = array('value' => $_POST['info_diverses'], 'type' => 'text', 'class' => 'transport');
}

if ( array_key_exists('id_transporteur', $_POST) ) {
	$variables_to_clean['id_transporteur'] = array('value' => $_POST['id_transporteur'], 'type' => 'id', 'class' => 'transport_transporteur');
}


if (isset($_POST['other_point_depart_ville']) && $_POST['other_point_depart_ville'] != '' && isset($_POST['other_point_depart_pays']) && $_POST['other_point_depart_pays'] != '' ) {

	// other point depart
	$npa = '0000';
	$ville = ucfirst(stripAccents($_POST['other_point_depart_ville']));
	$pays = ucfirst(stripAccents($_POST['other_point_depart_pays']));

	$test_ville_exist = Lieu::ville_exists($ville);

	if ($test_ville_exist) {
		$tmp_lieu = new Lieu($test_ville_exist);
	} else {
		$tmp_lieu = new Lieu(0, mb_strtoupper(stripAccents($ville)), Lieu_Categorie::get_id_from_categorie('ville'), '', '', '', $npa, $ville, $pays, '', '', '');
	}

	$test_ville_exist = FALSE;
	while (!$test_ville_exist) {
		$test_ville_exist = Lieu::ville_exists($ville);
	}
	$tmp_lieu = new Lieu($test_ville_exist);

	$variables_to_clean['point_depart']['value'] = $tmp_lieu->get_adresse();
	$variables_to_clean['point_depart']['type'] = 'array';
	$variables_to_clean['point_depart']['class'] = 'transport';

} elseif ( array_key_exists('point_depart', $_POST) ) {

	if ($_POST['point_depart'] == 0) {

		// alors domicile
		if (Beneficiaire::id_exists($_POST['id_beneficiaire'])) {
			$id_beneficiaire = $_POST['id_beneficiaire'];
			$tmp_beneficiaire = new Beneficiaire($_POST['id_beneficiaire']);

			$variables_to_clean['point_depart']['value'] = $tmp_beneficiaire->get_adresse();
			$variables_to_clean['point_depart']['type'] = 'array';
			$variables_to_clean['point_depart']['class'] = 'transport';

		} else {
			//error
		}


	} else {
		//lieu
		if (Lieu::id_exists($_POST['point_depart'])) {
			$id_lieu = $_POST['point_depart'];
			$tmp_lieu = new Lieu($_POST['point_depart']);

			$variables_to_clean['point_depart']['value'] = $tmp_lieu->get_adresse();
			$variables_to_clean['point_depart']['type'] = 'array';
			$variables_to_clean['point_depart']['class'] = 'transport';
		}

	}
}


// POINT ARRIVEE \\
if (isset($_POST['other_point_arrivee_ville']) && $_POST['other_point_arrivee_ville'] != '' && isset($_POST['other_point_arrivee_pays']) && $_POST['other_point_arrivee_pays'] != '' ) {

	// other point depart
	$npa = '0000';
	$ville = ucfirst(stripAccents($_POST['other_point_arrivee_ville']));
	$pays = ucfirst(stripAccents($_POST['other_point_arrivee_pays']));

	$test_ville_exist = Lieu::ville_exists($ville);

	if ($test_ville_exist) {
		$tmp_lieu = new Lieu($test_ville_exist);
	} else {
		$tmp_lieu = new Lieu(0, mb_strtoupper(stripAccents($ville)), Lieu_Categorie::get_id_from_categorie('ville'), '', '', '', $npa, $ville, $pays, '', '', '');
	}

	$test_ville_exist = FALSE;
	while (!$test_ville_exist) {
		$test_ville_exist = Lieu::ville_exists($ville);
	}
	$tmp_lieu = new Lieu($test_ville_exist);

	$variables_to_clean['point_arrivee']['value'] = $tmp_lieu->get_adresse();
	$variables_to_clean['point_arrivee']['type'] = 'array';
	$variables_to_clean['point_arrivee']['class'] = 'transport';

} elseif ( array_key_exists('point_arrivee', $_POST) ) {
	if ($_POST['point_arrivee'] == 0) {
		//domicile
		if (Beneficiaire::id_exists($_POST['id_beneficiaire'])) {
			$id_beneficiaire = $_POST['id_beneficiaire'];
			$tmp_beneficiaire = new Beneficiaire($_POST['id_beneficiaire']);

			$variables_to_clean['point_arrivee']['value'] = $tmp_beneficiaire->get_adresse();
			$variables_to_clean['point_arrivee']['type'] = 'array';
			$variables_to_clean['point_arrivee']['class'] = 'transport';
		}

	} else {
		//lieu
		if (Lieu::id_exists($_POST['point_arrivee'])) {
			$id_lieu = $_POST['point_arrivee'];
			$tmp_lieu = new Lieu($_POST['point_arrivee']);

			$variables_to_clean['point_arrivee']['value'] = $tmp_lieu->get_adresse();
			$variables_to_clean['point_arrivee']['type'] = 'array';
			$variables_to_clean['point_arrivee']['class'] = 'transport';
		}

	}
}

if ( array_key_exists('month_picker_month', $_POST) ) {
	$variables_to_clean['archive_month'] = array('value' => $_POST['month_picker_month'], 'type' => 'double', 'class' => 'archive');
}

if ( array_key_exists('month_picker_year', $_POST) ) {
	$variables_to_clean['archive_year'] = array('value' => $_POST['month_picker_year'], 'type' => 'double', 'class' => 'archive');
}

$variables_to_clean = clean_variables($variables_to_clean);

$data_to_display = $variables_to_clean[0];

switch ($action) {
	case "add":
		if (count($variables_to_clean[0]) == 0) { //new form
			//echo '-add new-';
			echo Transport::form('add', '');
		} elseif ($variables_to_clean[1] > 0) { //chech error
				//echo 'new with errors';
				echo Transport::form('add', $data_to_display);
		} else {
			unset($_SESSION['last_page']);
			$tmp_transport = new Transport(0, $data_to_display['id_beneficiaire']['value'], $data_to_display['id_categorie']['value'], $data_to_display['date_transport']['value'], $data_to_display['heure_debut']['value'], $data_to_display['duree_approximative']['value'], 1, $data_to_display['point_depart']['value'], $data_to_display['point_arrivee']['value'], 0, $data_to_display['aller_retour']['value'], 0, 0, $data_to_display['info_diverses']['value'], 0, $data_to_display['id_transporteur']['value']);

			$_SESSION['last_transport']['id'] = $tmp_transport->get_id();
			$_SESSION['last_transport']['id_beneficiaire'] = $data_to_display['id_beneficiaire']['value'];

			require('./processing/dashboard.php');
		}
		break;

	case "edit":
		if (count($variables_to_clean[0]) == 0) { // nothing as input + GET
			if (isset($_GET['id'])) {
				echo Transport::form('edit', new Transport($_GET['id']));
			}
		} elseif ($variables_to_clean[1] > 0) { // peut etre mix post/get
			if (isset($data_to_display['id']) && Transport::id_exists($data_to_display['id']['value'])) {
					if ($_POST['form'] == 'base') {
						// le transport est connu, recharge les donnees car il y a des erreurs
						echo Transport::form('edit', $data_to_display);

					} elseif ($_POST['form'] == 'choose') {
						//form choose, charge les donnees actuelles presentes dans la DB
						echo Transport::form('edit', new Transport($data_to_display['id']['value']));
					}
			} else {
				if (isset($_GET['id'])) {
					//charge le transport de $_GET
					echo Transport::form('edit', new Transport($_GET['id']));
				} else {
					//charge form_choose car le transport est inconnu
					echo Transport::form('list', $data_to_display);
				}
			}
		} else {
			if (isset($data_to_display['id']) && Transport::id_exists($data_to_display['id']['value'])) {

				//edition de l'entree dans la DB
				$tmp_transport = new Transport($data_to_display['id']['value']);
				unset($data_to_display['id']);

				foreach ($data_to_display as $index => $row) {
					if ($row['class'] == 'transport') {
						$attr[]= $index;
						$new_value[]= $row['value'];
					} elseif ($row['class'] == 'transport_transporteur') {
						$attr_transporteur[] = $index;
						$new_value_transporteur[] = $row['value'];
					}
				}

				$tmp_transport->editerAttributs($attr, $new_value);

				if (Transport::check_already_find_transporteur($tmp_transport->get_id())) {

					//repere la dimension id_transporteur
					foreach ($attr_transporteur as $index => $row) {
						if ($row == 'id_transporteur') {
							$dim_id_transporteur = $index;
							//break;
						}
					}

					if (isset($new_value_transporteur[$dim_id_transporteur]) && Transporteur::id_exists($new_value_transporteur[$dim_id_transporteur])) {

						if ($tmp_transport->get_id_filiale_transporteur() != $new_value_transporteur[$dim_id_transporteur]) {
							//remplacement par un autre chauffeur
							$tmp_transport->editerAttributsTransporteur($attr_transporteur, $new_value_transporteur);
							$tmp_transport->envoyer_email_chauffeur();
						}

					} else {
						//le chauffeur n'est plus disponible
						$tmp_transport->SupprimerChauffeur();
					}

				} else {
					//creation du lien
					$tmp_transport->addTransporteur($data_to_display['id_transporteur']['value']);
				}

				unset($_SESSION['last_page']);

				//require ('./processing/dashboard.php');
				echo Transport::form('list');

			} else {
				echo Transport::form('edit', $data_to_display);
			}

		}
		break;

	case "view":
		if (isset($data_to_display['id']) && Transport::id_exists($data_to_display['id']['value'])) {
			echo Transport::form('view', new Transport($data_to_display['id']['value']));
		} else {
			if (isset($_GET['id'])) {
				//charge le transport de $_GET
				echo Transport::form('view', new Transport($_GET['id']));
			} else {
				//charge form_choose car le transport est inconnu
				echo Transport::form('list');
			}
		}
		break;

	case "cancel":
		if (isset($_GET['id']) && Transport::id_exists($_GET['id'])) {
			$tmp_transport = new Transport($_GET['id']);
			$tmp_transport->editerAttributs('is_annule', 1);
			$tmp_transporteur_email = $tmp_transport->prevenir_chauffeur_email_annulation();

			$id_transporteur = Transport::check_already_find_transporteur($_GET['id']);

			if ($id_transporteur) {

				$tmp_transporteur = new Transporteur($id_transporteur);
				$tmp_transporteur_nom = $tmp_transporteur->get_nom_complet();
				$tmp_transporteur_adresse = $tmp_transporteur->get_adresse();
				$tmp_transporteur_tel = $tmp_transporteur->get_telephone();

			}

			//si query ajax, ne pas charger le contenu
			if (isset($_GET['reload']) && $_GET['reload'] == 'false' ) {

				if ($id_transporteur) {

					$html = '<p>';
						$html .= 'Ce transport était déjà attribué à un chauffeur.';
						$html .= '<br />';
						$html .= 'Il faut dès à présent le prevenir de son annulation';
					$html .= '</p>';

					$html .= '<p>';
						$html .= format_titre($tmp_transporteur_nom['titre']) . ' ' . mb_strtoupper(stripAccents($tmp_transporteur_nom['nom']));
					$html .= '</p>';

					$html .= '<p>';
						foreach ($tmp_transporteur_tel as $index => $row) {
							$html .= substr($index, 4) . ' : ' . format_tel($row) . '<br />';
						}
					$html .= '</p>';

					if ($tmp_transporteur->has_email()) {
						$html .= '<p>';
							$html .= 'Comme ce bénévole bénéficie d\'une adresse email, un courrier éléctronique a également été envoyé sur ' . $tmp_transporteur_email;
						$html .= '</p>';
					}

					$return_ajax['object'] = 'transport';
					$return_ajax['id'] = $_GET['id'];
					$return_ajax['msg'] = $html;

					$return_ajax = json_encode($return_ajax);
					echo $return_ajax;
				}
			} else {
				require('./base/base.reload_page.php');
			}
		}
		break;
	case "close":
		if (isset($_GET['id']) && Transport::id_exists($_GET['id'])) {
			$tmp_transport = new Transport($_GET['id']);
			$tmp_transport->editerAttributs('is_cloture', 1);

			//si query ajax, ne pas charger le contenu
			if (isset($_GET['reload']) && $_GET['reload'] == 'false' ) {

			} else {
				require('./base/base.reload_page.php');
			}

		}

		break;

	case "list":
		echo Transport::form('list');
		break;

	case "find_driver":
		if (isset($_GET['id']) && Transport::id_exists($_GET['id'])) {
			echo Transport::form('find_driver');
		} else {
			echo Transport::form('list');
		}
		break;

	case "link_driver":
		if(isset($_GET['id_transport']) && Transport::id_exists($_GET['id_transport']) && isset($_GET['id_transporteur']) && Transporteur::id_exists($_GET['id_transporteur'])) {
			$tmp_transport = new Transport($_GET['id_transport']);
			$tmp_transport->addTransporteur($_GET['id_transporteur']);
			require ('./processing/dashboard.php');
		} else {

		}
		break;
	case "archive":
		echo Transport::form('archive', $data_to_display);
		break;
	case "new_archive":
		unset ($_SESSION['last_page']);
		echo Transport::form('archive');
		break;
	case "new_like_last":
		$action = 'add';

		if (isset($_SESSION['last_transport']['id_beneficiaire']) && Beneficiaire::id_exists($_SESSION['last_transport']['id_beneficiaire'])) {
			$tmp_beneficiaire = new Beneficiaire($_SESSION['last_transport']['id_beneficiaire']);
			$last_transport = $tmp_beneficiaire->get_last_insert_transport();

			// mount les donnes dans la matrix data_to_display
			$data_to_display = array();

			$data_to_display['id_beneficiaire']['value'] = $last_transport->get_id_beneficiaire();
			$data_to_display['heure_debut']['value'] = $last_transport->get_time();
			$data_to_display['duree_approximative']['value'] = $last_transport->get_duree();

			$data_to_display['point_depart']['value'] = $last_transport->get_point_depart();
			$data_to_display['point_arrivee']['value'] = $last_transport->get_point_arrivee();

			$data_to_display['aller_retour']['value'] = $last_transport->get_aller_retour();
			$data_to_display['new_like_last']['value'] = TRUE;
			$data_to_display['id_categorie']['value'] = $last_transport->get_id_categorie();

			$id_transporteur = Transport::check_already_find_transporteur($last_transport->get_id());

			if ($id_transporteur) {
				$data_to_display['id_transporteur']['value'] = $id_transporteur;
			}

		}

		echo Transport::form($action, $data_to_display);
		break;


	case "recalc":

		if (isset($_GET['id']) && Transport::id_exists($_GET['id'])) {
			$tmp_transport = new Transport($_GET['id']);
			$tmp_transport->updateDistanceAndCost();

			//si query ajax, ne pas charger le contenu
			if (isset($_GET['reload']) && $_GET['reload'] == 'false' ) {
				$return_ajax['object'] = 'transport';
				$return_ajax['id'] = $_GET['id'];
				$return_ajax['msg'] = '<p>Mise à jour des kilomètres et des coûts</p>';

				$return_ajax = json_encode($return_ajax);
				echo $return_ajax;
			} else {
				require('./base/base.reload_page.php');
			}
		}
		break;


	default:
		break;
}

?>
