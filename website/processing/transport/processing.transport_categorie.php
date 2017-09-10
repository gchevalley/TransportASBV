<?php

$check_loading_class = load_class_and_interface(array('Transport_Categorie'));

$variables_to_clean = array();

$variables_to_clean['id'] = array('value' => $_POST['id'], 'type' => 'id');
$variables_to_clean['nom'] = array('value' => ($_POST['nom']), 'type' => 'text', 'sub_type' => 'nom', 'option' => array('required' => TRUE));
$variables_to_clean['priorite'] = array('value' => $_POST['priorite'], 'type' => 'double', 'option' => array('required' => TRUE));


$variables_to_clean = clean_variables($variables_to_clean);

$data_to_display = $variables_to_clean[0];

		
switch ($action) {
	case "add":	
		if ($variables_to_clean[1] > 0) { //chech error ou empty
			echo Transport_Categorie::form('add', $data_to_display);
		} else {
			//creation de la nouvelle entree dans la DB et reouveture du dashboard
			$tmp_transport_categorie = new Transport_Categorie(0, $data_to_display['nom']['value'], $data_to_display['priorite']['value']);
			
			$_SESSION['last_page']['data_to_display']['id_categorie'] = $tmp_transport_categorie->get_id();
			
			require ('./base/base.reload_page.php');
			
		}
		break;
	
	case "edit":
		if ($variables_to_clean[1] > 0) {
			if (isset($data_to_display['id']) && Transport_Categorie::id_exists($data_to_display['id']['value'])) {
					if ($_POST['form'] == 'base') {
						// le transport_categorie est connu, recharge les donnees car il y a des erreurs
						echo Transport_Categorie::form('edit', $data_to_display);
						
					} elseif ($_POST['form'] == 'choose') {
						//form choose, charge les donnees actuelles presentes dans la DB
						echo Transport_Categorie::form('edit', new Transport_Categorie($data_to_display['id']['value']));
					}
			} else {
				if (isset($_GET['id'])) {
					//charge le transport_categorie de $_GET
					echo Transport_Categorie::form('edit', new Transport_Categorie($_GET['id']));
				} else {
					//charge form_choose car le transport_categorie est inconnu 
					echo Transport_Categorie::form('edit', $data_to_display);
				}
			}
		} else {
			if (isset($data_to_display['id']) && Transport_Categorie::id_exists($data_to_display['id']['value'])) {
				//edition de l'entree dans la DB
				$tmp_transport_categorie = new Transport_Categorie($data_to_display['id']['value']);
				unset($data_to_display['id']);
				
				foreach ($data_to_display as $index => $row) {
					$attr[]= $index;
					$new_value[]= $row['value'];
				}
				
				$tmp_transport_categorie->editerAttributs($attr, $new_value);
				require ('./processing/dashboard.php');
				
			} else {
				echo Transport_Categorie::form('edit', $data_to_display);
			}
		}
		break;
	case "view":
		if (isset($data_to_display['id']) && Transport_Categorie::id_exists($data_to_display['id']['value'])) {
			echo Transport_Categorie::form('view', new Transport_Categorie($data_to_display['id']['value']));
		} else {
			if (isset($_GET['id'])) {
				//charge le beneficiaire de $_GET
				echo Transport_Categorie::form('view', new Transport_Categorie($_GET['id']));
			} else {
				//charge form_choose car le beneficiaire est inconnu 
				echo Transport_Categorie::form('view', $data_to_display);
			}
		}
		break;
	case "list":
		echo Transport_Categorie::form('list');
		break;
	default:
		break;
}

?>