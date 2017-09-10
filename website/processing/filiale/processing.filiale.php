<?php

load_class_and_interface(array('Filiale'));

$variables_to_clean = array();

$variables_to_clean['id'] = array('value' => $_POST['id'], 'type' => 'id', 'class' => 'filiale');
$variables_to_clean['nom'] = array('value' => ($_POST['nom']), 'type' => 'text', 'sub_type' => 'nom', 'option' => array('required' => TRUE), 'class' => 'filiale');
$variables_to_clean['adresse'] = array('value' => ($_POST['adresse']), 'type' => 'text', 'class' => 'filiale');
$variables_to_clean['adresse_complement'] = array('value' => ($_POST['adresse_complement']), 'type' => 'text', 'class' => 'filiale');
$variables_to_clean['npa'] = array('value' => $_POST['npa'], 'type' => 'text', 'sub_type' => 'nombre', 'class' => 'filiale');
$variables_to_clean['ville'] = array('value' => ($_POST['ville']), 'type' => 'text', 'class' => 'filiale');
$variables_to_clean['tel_permanence'] = array('value' => $_POST['tel_permanence'], 'option' => array('required' => TRUE), 'type' => 'tel', 'class' => 'filiale');
$variables_to_clean['tel_fax'] = array('value' => $_POST['tel_fax'], 'type' => 'tel', 'class' => 'filiale');
$variables_to_clean['email_permanence'] = array('value' => $_POST['email_permanence'], 'type' => 'email', 'class' => 'filiale');
$variables_to_clean['email_backup'] = array('value' => $_POST['email_backup'], 'type' => 'email', 'class' => 'filiale');

//glm weights a regrouper
foreach ($_POST as $index => $post) {
	if (strpos('entry' . $index, 'glm_weight')) {
		$tmp_array[str_replace('glm_weight_', '', $index)] = (double) $post;
	}
}

$variables_to_clean['glm_weights'] =  array('value' => json_encode($tmp_array), 'type' => 'text', 'class' => 'filiale');

$variables_to_clean['default_km_cost'] = array('value' => $_POST['default_km_cost'], 'type' => 'double', 'option' => array('required' => TRUE), 'class' => 'filiale');
$variables_to_clean['default_compensation_rate'] = array('value' => $_POST['default_compensation_rate'], 'type' => 'double', 'option' => array('required' => TRUE), 'class' => 'filiale');
$variables_to_clean['default_min_cost'] = array('value' => $_POST['default_min_cost'], 'type' => 'double', 'option' => array('required' => TRUE), 'class' => 'filiale');
$variables_to_clean['facturation_header_line_1'] = array('value' => $_POST['facturation_header_line_1'], 'type' => 'text', 'class' => 'filiale');
$variables_to_clean['facturation_footer_line_1'] = array('value' => $_POST['facturation_footer_line_1'], 'type' => 'text', 'class' => 'filiale');
$variables_to_clean['facturation_footer_line_2'] = array('value' => $_POST['facturation_footer_line_2'], 'type' => 'text', 'class' => 'filiale');

$variables_to_clean['facturation_month'] = array('value' => $_POST['month_picker_month'], 'type' => 'double', 'class' => 'facturation');
$variables_to_clean['facturation_year'] = array('value' => $_POST['month_picker_year'], 'type' => 'double', 'class' => 'facturation');



$variables_to_clean['zip_file_to_restore'] = array('value' => ($_POST['zip_file_to_restore']), 'type' => 'text');

if (isset($_FILES['upload_file']) && stripos($_FILES['upload_file']['type'], 'zip')) {
	global $base_dir;
	$dest_folder = $base_dir . '/';
	
	if (!is_dir($dest_folder)) {
		mkdir($dest_folder);
	}
	
	move_uploaded_file($_FILES['upload_file']['tmp_name'], $dest_folder . $_FILES['upload_file']['name'] );
	
	
	$variables_to_clean['zip_file_to_restore'] = array('value' => $dest_folder . $_FILES['upload_file']['name'] , 'type' => 'text');
}


$variables_to_clean = clean_variables($variables_to_clean);

$data_to_display = $variables_to_clean[0];

		
switch ($action) {
	case "add":	
		
		if ($variables_to_clean[1] > 0) { //chech error ou empty
			echo Filiale::form('add', $data_to_display);
		} else {
			//creation de la nouvelle entree dans la DB et reouveture du dashboard
			$tmp_filiale = new Filiale(0, $data_to_display['nom']['value'], $data_to_display['adresse']['value'], $data_to_display['adresse_complement']['value'], $data_to_display['npa']['value'], $data_to_display['ville']['value'], $data_to_display['tel_permanence']['value'], $data_to_display['tel_fax']['value'], $data_to_display['email_permanence']['value'], $data_to_display['email_backup']['value'], $data_to_display['default_km_cost']['value'], $data_to_display['default_compensation_rate']['value'], $data_to_display['default_min_cost']['value'], $data_to_display['facturation_header_line_1']['value'], $data_to_display['facturation_footer_line_1']['value'], $data_to_display['facturation_footer_line_2']['value']);
			
			require ('./base/base.reload_page.php');
			
		}
		break;
	
	case "edit":
		if ($variables_to_clean[1] > 0) {
			if (isset($data_to_display['id']) && Filiale::id_exists($data_to_display['id']['value'])) {
					if ($_POST['form'] == 'base') {
						// il y a des erreurs
						echo Filiale::form('edit', $data_to_display);
						
					} elseif ($_POST['form'] == 'choose') {
						//form choose, charge les donnees actuelles presentes dans la DB
						echo Filiale::form('edit', new Filiale($data_to_display['id']['value']));
					}
			} else {
				if (isset($_GET['id'])) {
					//charge la filiale de $_GET
					echo Filiale::form('edit', new Filiale($_GET['id']));
				} else {
					//charge form_choose car le beneficiaire est inconnu 
					echo Filiale::form('edit', $data_to_display);
				}
			}
		} else {
			if (isset($data_to_display['id']) && Filiale::id_exists($data_to_display['id']['value'])) {
				//edition de l'entree dans la DB
				

				$tmp_filiale = new Filiale($data_to_display['id']['value']);
				unset($data_to_display['id']);
				
				foreach ($data_to_display as $index => $row) {
					if ($row['class'] == 'filiale') {
						$attr[]= $index;
						$new_value[]= $row['value'];
					}
				}
				
				$tmp_filiale->editerAttributs($attr, $new_value);
				require ('./processing/dashboard.php');
			} else {
				echo Filiale::form('edit', $data_to_display);
			}
		}
		break;
	case "view":
		if (isset($data_to_display['id']) && Filiale::id_exists($data_to_display['id']['value'])) {
			echo Filiale::form('view', new Filiale($data_to_display['id']['value']));
		} else {
			if (isset($_GET['id'])) {
				//charge le beneficiaire de $_GET
				echo Filiale::form('view', new Filiale($_GET['id']));
			} else {
				//charge form_choose car le beneficiaire est inconnu 
				echo Filiale::form('view', $data_to_display);
			}
		}
		break;
	case "facturation":
		echo Filiale::form('facturation', $data_to_display);
		break;
	case "new_facturation":
		unset($_SESSION['last_page']);
		unset($_GET);
		unset($_POST);
		echo Filiale::form('facturation');
		break;
	case "rapport":
		echo Filiale::form('rapport');
		break;
	case "pdf_facture":
		$check_loading_class = load_class_and_interface(array('Beneficiaire'));
		
		if (isset($_GET['reload']) && $_GET['reload'] == 'false') {
			$data_to_display['reload']['value'] = FALSE;
		}
		
		if (isset($_GET['id_beneficiaire']) && Beneficiaire::id_exists($_GET['id_beneficiaire'])) {
			//$check_loading_class = load_class_and_interface(array('Beneficiaire'));
			//$tmp_beneficiaire = new Beneficiaire($_GET['id_beneficiaire']);
			$data_to_display['id_beneficiaire']['value'] = $_GET['id_beneficiaire'];
			echo Filiale::form('pdf_facture', $data_to_display);
		} else {
			echo Filiale::form('pdf_facture', $data_to_display);
		}
		
		break;
	case "pdf_remboursement":
		$check_loading_class = load_class_and_interface(array('Transporteur'));
		
		if (isset($_GET['reload']) && $_GET['reload'] == 'false') {
			$data_to_display['reload']['value'] = FALSE;
		}
		
		if (isset($_GET['id_transporteur']) && Transporteur::id_exists($_GET['id_transporteur'])) {
			$data_to_display['id_transporteur']['value'] = $_GET['id_transporteur'];
			echo Filiale::form('pdf_remboursement', $data_to_display);
		} else {
			echo Filiale::form('pdf_remboursement', $data_to_display);
		}
		break;
	case "list":
		echo Filiale::form('list');
		break;
	case "permanence":
		if (isset($_POST['sub_module']) && $_POST['sub_module'] == 'completed' ) {
			
			$check_loading_class = load_class_and_interface(array('Permanencier'));
			
			global $dbh;
			
			//recuperation des dates completes
			foreach ($_POST as $index => $post) {
				if (is_date($index) && $post != '') {
					$list_date[$index] = $post;
				}
			}
			
			
			//destruction des donnes actuelles => seul moyen si un permanencider se desiste
			$date_from = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
		
			if (date('m') == 12) {
				$month_to = 1;
				$year_to = date('Y') + 1;
			} else {
				$month_to = date('m') + 1;
				$year_to = date('Y');
			}
			
			$date_to = lastday($month_to, $year_to);
			
			$sql = "DELETE FROM permanence ";
			$sql .= " WHERE id_filiale=" . $_SESSION['filiale']['id'];
			$sql .= " AND date BETWEEN " . $dbh->quote($date_from);
			$sql .= " AND " . $dbh->quote($date_to);
			
			$dbh->exec($sql);
			
			//mise a jour des donnes de la base de donnees
			foreach($list_date as $date => $id_permanencier) {
				$tmp_permanencier = new Permanencier($id_permanencier);
				$tmp_permanencier->AjouterPermanence($date);
			}
			
			require('./processing/dashboard.php');
		} else {
			echo Filiale::form('permanence');
		}
		
		break;
	case "backup":
		//charge le script de backup
		$check_backup = backup_website_and_db();
		
		if($check_backup) {
			$html_code = '<p>Sauvegarde terminée avec succès, les fichiers ont également été transmis par email.</p>';
			$html_code .= '<a href="../backup/' . $check_backup['configBackupDir'] . $check_backup['backupName'] . '">';
				$html_code .= 'Fichier de sauvegarde : <strong>' . $check_backup['backupName'] . '</strong>';
			$html_code .= '</a>';
			
			echo ($html_code);
		} else {
			//error
			echo ('<p>Erreur lors de la sauvegarde, les fichiers n\'ont pas pu être transmis par email.</p>');
		}
		
		require('./processing/dashboard.php');
		
		break;
	case "restore":
		echo Filiale::form('restore', $data_to_display);
		
		break;
	case "admin":
		echo Filiale::form('admin', $data_to_display);
		break;
	case "load_reference":
		$ref = $_POST['refrence'];
		$bug = FALSE;
		
		$test = preg_match('/(F|R)[\d]{6}[\d]{1,}/', $ref);
		
		if ($test > 0) {
			$type = mb_strtoupper(substr($ref, 0, 1));
			
			if ($type == 'F') {
				$data_to_display['ref_type']['value'] = 'facture';
			} elseif ($type == 'R') {
				$data_to_display['ref_type']['value'] = 'remboursement';
			}
			
			$date_year_txt = substr($ref, 1, 4);
			$date_month_txt = substr($ref, 5, 2);
			
			if (substr($date_month_txt, 0,1) == '0') {
				$date_month_txt = (int) substr($date_month_txt, 1, 1);
			}
			
			$id = (int) substr($ref, 7);
			
			$data_to_display['month']['value'] = $date_month_txt;
			$data_to_display['year']['value'] = $date_year_txt;
			$data_to_display['id']['value'] = $id;
		
			echo Filiale::form('load_reference', $data_to_display);
		} else {
			echo '<p>Erreur avec la référence entrée</p>';
		}
		
		break;
	default:
		break;
}

?>