<?php

load_class_and_interface(array('Beneficiaire', 'Repondant_Categorie'));

$variables_to_clean = array();

$variables_to_clean['id'] = array('value' => $_POST['id'], 'type' => 'id', 'class' => 'beneficiaire');
$variables_to_clean['titre'] = array('value' => $_POST['titre'], 'type' => 'text', 'sub_type' => 'nom', 'option' => array('required' => TRUE), 'class' => 'beneficiaire');
$variables_to_clean['nom'] = array('value' => ucfirst($_POST['nom']), 'type' => 'text', 'sub_type' => 'nom', 'option' => array('required' => TRUE), 'class' => 'beneficiaire');
$variables_to_clean['prenom'] = array('value' => ucfirst($_POST['prenom']), 'type' => 'text', 'sub_type' => 'nom', 'option' => array('required' => TRUE), 'class' => 'beneficiaire');
$variables_to_clean['adresse'] = array('value' => $_POST['adresse'], 'type' => 'text', 'option' => array('required' => TRUE), 'class' => 'beneficiaire');
$variables_to_clean['adresse_complement'] = array('value' => $_POST['adresse_complement'], 'type' => 'text', 'class' => 'beneficiaire');
$variables_to_clean['npa'] = array('value' => $_POST['npa'], 'type' => 'text', 'sub_type' => 'nombre', 'option' => array('required' => TRUE), 'class' => 'beneficiaire');
$variables_to_clean['ville'] = array('value' => ucfirst($_POST['ville']), 'type' => 'text', 'option' => array('required' => TRUE), 'class' => 'beneficiaire');
$variables_to_clean['pays'] = array('value' => ucfirst($_POST['pays']), 'type' => 'text', 'option' => array('required' => TRUE), 'class' => 'beneficiaire');
$variables_to_clean['tel_fixe'] = array('value' => $_POST['tel_fixe'], 'type' => 'tel', 'class' => 'beneficiaire');
$variables_to_clean['tel_mobile'] = array('value' => $_POST['tel_mobile'], 'type' => 'tel', 'class' => 'beneficiaire');
$variables_to_clean['info_diverses'] = array('value' => $_POST['info_diverses'], 'type' => 'text', 'class' => 'beneficiaire');
$variables_to_clean['toujours_2'] = array('value' => $_POST['toujours_2'], 'type' => 'bool', 'class' => 'beneficiaire');


//autre adresse pour la facturation
$variables_to_clean['autre_adresse_facturation'] = array('value' => $_POST['autre_adresse_facturation'], 'type' => 'bool', 'class' => 'beneficiaire');
$variables_to_clean['facturation_nom'] = array('value' => ucfirst($_POST['facturation_nom']), 'type' => 'text', 'sub_type' => 'nom', 'class' => 'beneficiaire');
$variables_to_clean['facturation_prenom'] = array('value' => ucfirst($_POST['facturation_prenom']), 'type' => 'text', 'sub_type' => 'nom', 'class' => 'beneficiaire');
$variables_to_clean['facturation_adresse'] = array('value' => $_POST['facturation_adresse'], 'type' => 'text', 'class' => 'beneficiaire');
$variables_to_clean['facturation_adresse_complement'] = array('value' => $_POST['facturation_adresse_complement'], 'type' => 'text', 'class' => 'beneficiaire');
$variables_to_clean['facturation_npa'] = array('value' => $_POST['facturation_npa'], 'type' => 'text', 'sub_type' => 'nombre', 'class' => 'beneficiaire');
$variables_to_clean['facturation_ville'] = array('value' => ucfirst($_POST['facturation_ville']), 'type' => 'text', 'class' => 'beneficiaire');
$variables_to_clean['facturation_pays'] = array('value' => ucfirst($_POST['facturation_pays']), 'type' => 'text', 'class' => 'beneficiaire');

	//si décoché on vide les variables
	if ($variables_to_clean['autre_adresse_facturation']['value'] == 0 && $variables_to_clean['autre_adresse_facturation']['value'] != 'on') {
		$variables_to_clean['facturation_nom']['value'] = '';
		$variables_to_clean['facturation_prenom']['value'] = '';
		$variables_to_clean['facturation_adresse']['value'] = '';
		$variables_to_clean['facturation_adresse_complement']['value'] = '';
		$variables_to_clean['facturation_npa']['value'] = '';
		$variables_to_clean['facturation_ville']['value'] = '';
		$variables_to_clean['facturation_pays']['value'] = '';
	}



//repondant
$variables_to_clean['repondant']['id'] = array('value' => $_POST['repondant_id'], 'type' => 'id');
$variables_to_clean['repondant']['lien_beneficiaire'] = array('value' => $_POST['repondant_lien_beneficiaire'], 'type' => 'text');
$variables_to_clean['repondant']['id_categorie'] = array('value' => $_POST['repondant_id_categorie'], 'type' => 'id');

	$tmp_repondant_categorie = new Repondant_Categorie($variables_to_clean['repondant']['id_categorie']['value']);
	
	if ($tmp_repondant_categorie->is_auto_mount()) {
		$table = stripAccents(strtolower($tmp_repondant_categorie->get_nom()));
		$variables_to_clean['repondant']['ref_external'] = array('value' => $_POST['repondant_ref_external_' . $table], 'type' => 'id');
	}

$variables_to_clean['repondant']['nom'] = array('value' => ucfirst($_POST['repondant_nom']), 'type' => 'text', 'sub_type' => 'nom');
$variables_to_clean['repondant']['prenom'] = array('value' => ucfirst($_POST['repondant_prenom']), 'type' => 'text', 'sub_type' => 'nom');
$variables_to_clean['repondant']['adresse'] = array('value' => $_POST['repondant_adresse'], 'type' => 'text');
$variables_to_clean['repondant']['adresse_complement'] = array('value' => $_POST['repondant_adresse_complement'], 'type' => 'text');
$variables_to_clean['repondant']['npa'] = array('value' => $_POST['repondant_npa'], 'type' => 'text', 'sub_type' => 'nombre');
$variables_to_clean['repondant']['ville'] = array('value' => ucfirst($_POST['repondant_ville']), 'type' => 'text');
$variables_to_clean['repondant']['tel_fixe'] = array('value' => $_POST['repondant_tel_fixe'], 'type' => 'tel');
$variables_to_clean['repondant']['tel_mobile'] = array('value' => $_POST['repondant_tel_mobile'], 'type' => 'tel');


$variables_to_clean = clean_variables($variables_to_clean);

$data_to_display = $variables_to_clean[0];

		
switch ($action) {
	case "add":	
		if ($variables_to_clean[1] > 0) { //chech error ou empty
			echo Beneficiaire::form('add', $data_to_display);
			
		} else {
			//creation de la nouvelle entree dans la DB et reouveture du dashboard
			$tmp_beneficiaire = new Beneficiaire(0, $data_to_display['titre']['value'], $data_to_display['nom']['value'], $data_to_display['prenom']['value'], $data_to_display['adresse']['value'], $data_to_display['adresse_complement']['value'], $data_to_display['npa']['value'], $data_to_display['ville']['value'], $data_to_display['pays']['value'], $data_to_display['tel_fixe']['value'], $data_to_display['tel_mobile']['value'], $data_to_display['info_diverses']['value'], $data_to_display['toujours_2']['value'], $data_to_display['autre_adresse_facturation']['value'], $data_to_display['facturation_nom']['value'], $data_to_display['facturation_prenom']['value'], $data_to_display['facturation_adresse']['value'], $data_to_display['facturation_adresse_complement']['value'], $data_to_display['facturation_npa']['value'], $data_to_display['facturation_ville']['value'], $data_to_display['facturation_pays']['value']);
			
			//ajouter les repondants si necessaire
			if (isset($data_to_display['repondant']['lien_beneficiaire']) && $data_to_display['repondant']['lien_beneficiaire'] != '' && isset($data_to_display['repondant']['id_categorie']) && Repondant_Categorie::id_exists($data_to_display['repondant']['id_categorie']['value'])) {
				if ($tmp_repondant_categorie->is_auto_mount()) {
					if (is_numeric($data_to_display['repondant']['ref_external']['value'])) {
						$tmp_beneficiaire->ajouterRepondant($data_to_display['repondant']['lien_beneficiaire']['value'], $data_to_display['repondant']['id_categorie']['value'], $data_to_display['repondant']['ref_external']['value'], $data_to_display['repondant']['nom']['value'], $data_to_display['repondant']['prenom']['value'], $data_to_display['repondant']['tel_fixe']['value'], $data_to_display['repondant']['tel_mobile']['value'], $data_to_display['repondant']['adresse']['value'], $data_to_display['repondant']['adresse_complement']['value'], $data_to_display['repondant']['npa']['value'], $data_to_display['repondant']['ville']['value']);
					} else {
						//pas besoin de ref external
						$tmp_beneficiaire->ajouterRepondant($data_to_display['repondant']['lien_beneficiaire']['value'], $data_to_display['repondant']['id_categorie']['value'], NULL, $data_to_display['repondant']['nom']['value'], $data_to_display['repondant']['prenom']['value'], $data_to_display['repondant']['tel_fixe']['value'], $data_to_display['repondant']['tel_mobile']['value'], $data_to_display['repondant']['adresse']['value'], $data_to_display['repondant']['adresse_complement']['value'], $data_to_display['repondant']['npa']['value'], $data_to_display['repondant']['ville']['value']);
					}
				} else {
					$tmp_beneficiaire->ajouterRepondant($data_to_display['repondant']['lien_beneficiaire']['value'], $data_to_display['repondant']['id_categorie']['value'], NULL, $data_to_display['repondant']['nom']['value'], $data_to_display['repondant']['prenom']['value'], $data_to_display['repondant']['tel_fixe']['value'], $data_to_display['repondant']['tel_mobile']['value'], $data_to_display['repondant']['adresse']['value'], $data_to_display['repondant']['adresse_complement']['value'], $data_to_display['repondant']['npa']['value'], $data_to_display['repondant']['ville']['value']);
				}
			}
			
			if (isset($_SESSION['last_page']['module']) && $_SESSION['last_page']['module'] == 'transport' && $_SESSION['last_page']['action'] == 'add' ) {
				$_SESSION['last_page']['data_to_display']['id_beneficiaire'] = $tmp_beneficiaire->get_id();
			}
			
			require ('./base/base.reload_page.php');
			
		}
		break;
	
	case "edit":
		if ($variables_to_clean[1] > 0) {
			if (isset($data_to_display['id']) && Beneficiaire::id_exists($data_to_display['id']['value'])) {
					if ($_POST['form'] == 'base') {
						// le beneficiaire est connu, recharge les donnees car il y a des erreurs
						echo Beneficiaire::form('edit', $data_to_display);
						
					} elseif ($_POST['form'] == 'choose') {
						//form choose, charge les donnees actuelles presentes dans la DB
						echo Beneficiaire::form('edit', new Beneficiaire($data_to_display['id']['value']));
					}
			} else {
				if (isset($_GET['id'])) {
					//charge le beneficiaire de $_GET
					echo Beneficiaire::form('edit', new Beneficiaire($_GET['id']));
				} else {
					//charge form_choose car le beneficiaire est inconnu 
					echo Beneficiaire::form('edit', $data_to_display);
				}
			}
		} else {
			if (isset($data_to_display['id']) && Beneficiaire::id_exists($data_to_display['id']['value'])) {
				//edition de l'entree dans la DB
				$tmp_beneficiaire = new Beneficiaire($data_to_display['id']['value']);
				unset($data_to_display['id']);
				
				foreach ($data_to_display as $index => $row) {
					if ($row['class'] == 'beneficiaire') {
						$attr[]= $index;
						$new_value[]= $row['value'];
					}
				}
				
				$tmp_beneficiaire->editerAttributs($attr, $new_value);
				
				//ajouter les repondants si necessaire
				if (isset($data_to_display['repondant']['lien_beneficiaire']) && $data_to_display['repondant']['lien_beneficiaire'] != '' && isset($data_to_display['repondant']['id_categorie']) && Repondant_Categorie::id_exists($data_to_display['repondant']['id_categorie']['value'])) {
					if ($tmp_repondant_categorie->is_auto_mount()) {
						if (is_numeric($data_to_display['repondant']['ref_external']['value'])) {
							$tmp_beneficiaire->ajouterRepondant($data_to_display['repondant']['lien_beneficiaire']['value'], $data_to_display['repondant']['id_categorie']['value'], $data_to_display['repondant']['ref_external']['value'], $data_to_display['repondant']['nom']['value'], $data_to_display['repondant']['prenom']['value'], $data_to_display['repondant']['tel_fixe']['value'], $data_to_display['repondant']['tel_mobile']['value'], $data_to_display['repondant']['adresse']['value'], $data_to_display['repondant']['adresse_complement']['value'], $data_to_display['repondant']['npa']['value'], $data_to_display['repondant']['ville']['value']);
						} else {
							//pas besoin de ref external
							$tmp_beneficiaire->ajouterRepondant($data_to_display['repondant']['lien_beneficiaire']['value'], $data_to_display['repondant']['id_categorie']['value'], NULL, $data_to_display['repondant']['nom']['value'], $data_to_display['repondant']['prenom']['value'], $data_to_display['repondant']['tel_fixe']['value'], $data_to_display['repondant']['tel_mobile']['value'], $data_to_display['repondant']['adresse']['value'], $data_to_display['repondant']['adresse_complement']['value'], $data_to_display['repondant']['npa']['value'], $data_to_display['repondant']['ville']['value']);
						}
					} else {
						$tmp_beneficiaire->ajouterRepondant($data_to_display['repondant']['lien_beneficiaire']['value'], $data_to_display['repondant']['id_categorie']['value'], NULL, $data_to_display['repondant']['nom']['value'], $data_to_display['repondant']['prenom']['value'], $data_to_display['repondant']['tel_fixe']['value'], $data_to_display['repondant']['tel_mobile']['value'], $data_to_display['repondant']['adresse']['value'], $data_to_display['repondant']['adresse_complement']['value'], $data_to_display['repondant']['npa']['value'], $data_to_display['repondant']['ville']['value']);
					}
				}
				
				echo Beneficiaire::form('list');
				
			} else {
				echo Beneficiaire::form('edit', $data_to_display);
			}
		}
		break;
		
	case "view":
		if (isset($data_to_display['id']) && Beneficiaire::id_exists($data_to_display['id']['value'])) {
			echo Beneficiaire::form('view', new Beneficiaire($data_to_display['id']['value']));
		} else {
			if (isset($_GET['id'])) {
				//charge le beneficiaire de $_GET
				echo Beneficiaire::form('view', new Beneficiaire($_GET['id']));
			} else {
				//charge form_choose car le beneficiaire est inconnu 
				echo Beneficiaire::form('view', $data_to_display);
			}
		}
		break;
		
	case "list":
		echo Beneficiaire::form('list');
		break;
		
	case "new_transport":
		$check_loading_class = load_class_and_interface(array('Transport'));
		$data_to_display = array();
		$data_to_display['id_beneficiaire']['value'] = $_GET['id'];
		echo Transport::form('add', $data_to_display);
		break;
	case "tarif":
		load_class_and_interface(array('Lieu'));
		
		
		if (isset($_GET['id']) && is_numeric($_GET['id']) && Beneficiaire::id_exists($_GET['id'])) {
			$data_to_display['id']['value'] = $_GET['id'];
		}
		
		if (isset($_GET['id_depart']) && is_numeric($_GET['id_depart']) && Lieu::id_exists($_GET['id_depart'])) {
			$data_to_display['id_depart']['value'] = $_GET['id_depart'];
		}
		
		if (isset($_GET['depart_custom']) && is_string($_GET['depart_custom']) && $_GET['depart_custom'] != '') {
			$data_to_display['depart_custom']['value'] = $_GET['depart_custom'];
			
			if (isset($_GET['depart_custom_pays']) && is_string($_GET['depart_custom_pays']) && $_GET['depart_custom_pays'] != '') {
				$data_to_display['depart_custom_pays']['value'] = $_GET['depart_custom_pays'];
			} else {
				$data_to_display['depart_custom_pays']['value'] = 'Suisse';
			}
		}
		
		if (isset($_GET['id_destination']) && is_numeric($_GET['id_destination']) && Lieu::id_exists($_GET['id_destination'])) {
			$data_to_display['id_destination']['value'] = $_GET['id_destination'];
		}
		
		if (isset($_GET['arrivee_custom']) && is_string($_GET['arrivee_custom']) && $_GET['arrivee_custom'] != '') {
			$data_to_display['arrivee_custom']['value'] = $_GET['arrivee_custom'];
			
			if (isset($_GET['arrivee_custom_pays']) && is_string($_GET['arrivee_custom_pays']) && $_GET['arrivee_custom_pays'] != '') {
				$data_to_display['arrivee_custom_pays']['value'] = $_GET['arrivee_custom_pays'];
			} else {
				$data_to_display['arrivee_custom_pays']['value'] = 'Suisse';
			}
		}
		
		echo Beneficiaire::form($action, $data_to_display);
		
		break;
	case ajax_get_beneficiaire_details:
		
		if (isset($_GET['id_beneficiaire']) && Beneficiaire::id_exists($_GET['id_beneficiaire'])) {
			$data_to_display = array();
			$data_to_display['id_beneficiaire']['value'] = $_GET['id_beneficiaire'];

			echo Beneficiaire::form($action, $data_to_display);
		}
		
		break;
	case ajax_already_transport_same_date:
		if (isset($_GET['id_beneficiaire']) && Beneficiaire::id_exists($_GET['id_beneficiaire']) && $_GET['date_transport'] && is_date($_GET['date_transport']) ) {
			
			$data_to_display = array();
			$data_to_display['id_beneficiaire']['value'] = $_GET['id_beneficiaire'];
			
			if (strpos($_GET['date_transport'], '.')) {
				//date eu
				$data_to_display['date_transport']['value'] = substr($_GET['date_transport'], 6, 4) . '-' . substr($_GET['date_transport'], 3, 2) . '-' . substr($_GET['date_transport'], 0, 2);
			} else {
				$data_to_display['date_transport']['value'] = $_GET['date_transport'];
			}
			
			echo Beneficiaire::form($action, $data_to_display);
		}
		break;
	case "ajax_already_transport_same_date_and_time":
		if (isset($_GET['id_beneficiaire']) && Beneficiaire::id_exists($_GET['id_beneficiaire']) && $_GET['date_transport'] && is_date($_GET['date_transport']) && isset($_GET['heure_debut_heure']) && isset($_GET['heure_debut_minute']) ) {
			
			$data_to_display = array();
			$data_to_display['id_beneficiaire']['value'] = $_GET['id_beneficiaire'];
			
			if (strpos($_GET['date_transport'], '.')) {
				//date eu
				$data_to_display['date_transport']['value'] = substr($_GET['date_transport'], 6, 4) . '-' . substr($_GET['date_transport'], 3, 2) . '-' . substr($_GET['date_transport'], 0, 2);
			} else {
				$data_to_display['date_transport']['value'] = $_GET['date_transport'];
			}
			
			$data_to_display['heure_debut']['value'] = $_GET['heure_debut_heure'] . ':' . $_GET['heure_debut_minute'];
			
			echo Beneficiaire::form($action, $data_to_display);
		}
		break;
	default:
		//action inconnue
		echo Beneficiaire::form('list');
		break;
}

?>