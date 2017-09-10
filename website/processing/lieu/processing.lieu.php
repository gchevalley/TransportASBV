<?php

$check_loading_class = load_class_and_interface(array('Lieu'));

$variables_to_clean = array();

$variables_to_clean['id'] = array('value' => $_POST['id'], 'type' => 'id');
$variables_to_clean['nom'] = array('value' => $_POST['nom'], 'type' => 'text', 'sub_type' => 'nom', 'option' => array('required' => TRUE));
$variables_to_clean['abreviation'] = array('value' => $_POST['abreviation'], 'type' => 'text');
$variables_to_clean['id_categorie'] = array('value' => $_POST['id_categorie'], 'type' => 'id');
$variables_to_clean['adresse'] = array('value' => $_POST['adresse'], 'type' => 'text');
$variables_to_clean['adresse_complement'] = array('value' => $_POST['adresse_complement'], 'type' => 'text');
$variables_to_clean['npa'] = array('value' => $_POST['npa'], 'type' => 'text', 'sub_type' => 'nombre', 'option' => array('required' => TRUE));
$variables_to_clean['ville'] = array('value' => $_POST['ville'], 'type' => 'text', 'option' => array('required' => TRUE));
$variables_to_clean['pays'] = array('value' => $_POST['pays'], 'type' => 'text', 'option' => array('required' => TRUE));
$variables_to_clean['tel_fixe'] = array('value' => $_POST['tel_fixe'], 'type' => 'tel');
$variables_to_clean['tel_fax'] = array('value' => $_POST['tel_fax'], 'type' => 'tel');
$variables_to_clean['tel_mobile'] = array('value' => $_POST['tel_mobile'], 'type' => 'tel');
$variables_to_clean['numero_important'] = array('value' => $_POST['numero_important'], 'type' => 'bool');


$variables_to_clean = clean_variables($variables_to_clean);

$data_to_display = $variables_to_clean[0];

		
switch ($action) {
	case "add":	
		if ($variables_to_clean[1] > 0) { //chech error ou empty
			echo Lieu::form('add', $data_to_display);
		} else {
			//creation de la nouvelle entree dans la DB et reouveture du dashboard
			$tmp_lieu = new Lieu(0, $data_to_display['nom']['value'], $data_to_display['id_categorie']['value'], $data_to_display['abreviation']['value'], $data_to_display['adresse']['value'], $data_to_display['adresse_complement']['value'], $data_to_display['npa']['value'], $data_to_display['ville']['value'], $data_to_display['pays']['value'], $data_to_display['tel_fixe']['value'], $data_to_display['tel_fax']['value'], $data_to_display['tel_mobile']['value']);
			
			//si le numero doit apparaitre sur la page d'accueil
			if (isset($data_to_display['numero_important']) && $data_to_display['numero_important']['value'] == 1) {
				$tmp_lieu->declarer_comme_numero_important();
			}
			
			require ('./base/base.reload_page.php');
			
		}
		break;
	
	case "edit":
		if ($variables_to_clean[1] > 0) {
			if (isset($data_to_display['id']) && Lieu::id_exists($data_to_display['id']['value'])) {
					if ($_POST['form'] == 'base') {
						// le lieu est connu, recharge les donnees car il y a des erreurs
						echo Lieu::form('edit', $data_to_display);
						
					} elseif ($_POST['form'] == 'choose') {
						//form choose, charge les donnees actuelles presentes dans la DB
						echo Lieu::form('edit', new Lieu($data_to_display['id']['value']));
					}
			} else {
				if (isset($_GET['id'])) {
					//charge le beneficiaire de $_GET
					echo Lieu::form('edit', new Lieu($_GET['id']));
				} else {
					//charge form_choose car le beneficiaire est inconnu 
					echo Lieu::form('edit', $data_to_display);
				}
			}
		} else {
			if (isset($data_to_display['id']) && Lieu::id_exists($data_to_display['id']['value'])) {
				//edition de l'entree dans la DB
				$tmp_lieu = new Lieu($data_to_display['id']['value']);
				unset($data_to_display['id']);
				
				foreach ($data_to_display as $index => $row) {
					$attr[]= $index;
					$new_value[]= $row['value'];
				}
				
				$tmp_lieu->editerAttributs($attr, $new_value);
				//require ('./processing/dashboard.php');
				echo Lieu::form('list');
				
			} else {
				echo Lieu::form('edit', $data_to_display);
			}
		}
		break;
	case "view":
		if (isset($data_to_display['id']) && Lieu::id_exists($data_to_display['id']['value'])) {
			echo Lieu::form('view', new Lieu($data_to_display['id']['value']));
		} else {
			if (isset($_GET['id'])) {
				//charge le beneficiaire de $_GET
				echo Lieu::form('view', new Lieu($_GET['id']));
			} else {
				//charge form_choose car le beneficiaire est inconnu 
				echo Lieu::form('view', $data_to_display);
			}
		}
		break;
	case "list":
		echo Lieu::form('list');
		break;
	default:
		break;
}

?>