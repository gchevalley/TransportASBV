<?php

load_class_and_interface(array('Benevole', 'Transporteur', 'Benevole_Disponibilite_Categorie'));

$variables_to_clean = array();
$variables_to_clean['date_transport'] = array('value' => $_POST['date_transport'], 'type' => 'date');
$variables_to_clean['periode_transport'] = array('value' => $_POST['periode_transport'], 'type' => 'id');
$variables_to_clean['is_geneve'] = array('value' => $_POST['is_geneve'], 'type' => 'bool');
$variables_to_clean['is_lausanne'] = array('value' => $_POST['is_lausanne'], 'type' => 'bool');


$variables_to_clean = clean_variables($variables_to_clean);
$data_to_display = $variables_to_clean[0];


switch ($action) {
	case "add":	
		switch ($sub_module) {
			case 'non_dispo_date_transport':
				if (isset($_GET['date_custom']) && isset($_GET['id'])) {
					$date_custom = $_GET['date_custom'];
					$id_filiale_transporteur = $_GET['id'];
					$tmp_transporteur = new Transporteur($id_filiale_transporteur);
					$tmp_transporteur->ajouterNonDisponibiliteDate($date_custom, Benevole_Disponibilite_Categorie::get_id_from_nom('transport'), $_SESSION['filiale']['id']);
				}
				
				if (isset($_GET['reload']) && $_GET['reload'] == 'false') {
					//appel ajax
				} else {
					echo Benevole::form('view', new Benevole(Benevole::get_super_id_benvole_from_id_benevole_filiale($_GET['id'])));
				}
				
				break;
			case 'area_non_dispo_dates_transport':
				if (isset($_GET['id']) && isset($_GET['date_from_day']) && isset($_GET['date_from_month']) && isset($_GET['date_from_year']) && isset($_GET['date_to_day']) && isset($_GET['date_to_month']) && isset($_GET['date_to_year']) && is_numeric($_GET['date_from_day']) && $_GET['date_from_day'] > 0  && is_numeric($_GET['date_from_month']) && $_GET['date_from_month'] > 0  && is_numeric($_GET['date_from_year']) && $_GET['date_from_year'] > 0  && is_numeric($_GET['date_to_day']) && $_GET['date_to_day'] > 0  && is_numeric($_GET['date_to_month']) && $_GET['date_to_month'] > 0  && is_numeric($_GET['date_to_year']) && $_GET['date_to_year'] > 0   ) {
					$array_non_dispo_date_transports = array_date_between_2_dates($_GET['date_from_day'], $_GET['date_from_month'], $_GET['date_from_year'], $_GET['date_to_day'], $_GET['date_to_month'], $_GET['date_to_year']);
					
					$tmp_transporteur = new Transporteur($_GET['id']);
					
					foreach ($array_non_dispo_date_transports as $date_non_dispo) {
						$tmp_transporteur->ajouterNonDisponibiliteDate($date_non_dispo, Benevole_Disponibilite_Categorie::get_id_from_nom('transport'), $_SESSION['filiale']['id']);
					}
					
				}
				
				
				echo Benevole::form('view', new Benevole(Benevole::get_super_id_benvole_from_id_benevole_filiale($_GET['id'])));
				
				break;
		}
		break;
	case 'delete':
		switch ($sub_module) {
			case 'non_dispo_date_transport':
				if (isset($_GET['date_custom']) && isset($_GET['id'])) {
					$date_custom = $_GET['date_custom'];
					$id_filiale_transporteur = $_GET['id'];
					$tmp_transporteur = new Transporteur($id_filiale_transporteur);
					$tmp_transporteur->supprimerNonDisponibiliteDate($date_custom, Benevole_Disponibilite_Categorie::get_id_from_nom('transport'), $_SESSION['filiale']['id']);
				}
				
				if (isset($_GET['reload']) && $_GET['reload'] == 'false') {
					//appel ajax
				} else {
					echo Benevole::form('view', new Benevole(Benevole::get_super_id_benvole_from_id_benevole_filiale($_GET['id'])));
				}
				
				break;
		}
		break;
	case "edit":
		break;
	case "view":
		/*
		if (isset($data_to_display['id']) && Benevole::id_exists(Benevole::get_super_id_benvole_from_id_benevole_filiale($data_to_display['id']['value']))) {
			echo Benevole::form('view', new Benevole(Benevole::get_super_id_benvole_from_id_benevole_filiale($data_to_display['id']['value'])));
		} else {
			if (isset($_GET['id'])) {
				//charge le beneficiaire de $_GET
				echo Benevole::form('view', new Benevole(Benevole::get_super_id_benvole_from_id_benevole_filiale($_GET['id'])));
			} else {
				//charge form_choose car le beneficiaire est inconnu 
				echo Benevole::form('view', $data_to_display);
			}
		}
		*/
		break;
	case "find_transporteur_dispo":
		echo Transporteur::form('find_transporteur_dispo', $data_to_display);
		break;
	case "city_near":
		echo Transporteur::form('city_near', $data_to_display);
		break;
	case "cancel_constraint_beneficiaire":
		$check_loading_class = load_class_and_interface(array('Beneficiaire'));
		if (isset($_GET['id_transporteur']) && Transporteur::id_exists($_GET['id_transporteur']) && isset($_GET['id_beneficiaire']) && Beneficiaire::id_exists($_GET['id_beneficiaire'])) {
			$id_transporteur = $_GET['id_transporteur'];
			$id_beneficiaire = $_GET['id_beneficiaire'];
			
			$tmp_transporteur = new Transporteur($id_transporteur);
			$tmp_transporteur->supprimerContrainteBeneficiaire($id_beneficiaire);
			
		}
		
		echo Benevole::form('list');
		
		break;
	case "unreachable_today":
		if (isset($_GET['id_transporteur']) && Transporteur::id_exists($_GET['id_transporteur'])) {
			$tmp_transporteur = new Transporteur($_GET['id_transporteur']);
			$date_today = date('Y-m-d');
			
			$tmp_transporteur->ajouterNonDisponibiliteDate($date_today, Benevole_Disponibilite_Categorie::get_id_from_nom('atteignable'), $_SESSION['filiale']['id']);
		}
		
		
		if (isset($_GET['reload']) && $_GET['reload'] == 'false') {
			//appel ajax
		} else {
			//recharge l'output GLM
			require('./base/base.reload_page.php');
		}
		
		break;
	case "transports_potentiels":
		if (isset($_GET['id']) && Transporteur::id_exists($_GET['id'])) {
			$data_to_display['id']['value'] = $_GET['id'];
			echo Transporteur::form('transports_potentiels', $data_to_display);
		}
		
		break;
	case "list":
		echo Benevole::form('list');
		break;
	default:
		break;
}

?>