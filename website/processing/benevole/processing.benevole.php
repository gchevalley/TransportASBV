<?php

load_class_and_interface(array('Benevole', 'Benevole_Disponibilite_Categorie'));

$variables_to_clean = array();

//id de filiale et non benevole id !!!
$variables_to_clean['id'] = array('value' => $_POST['id'], 'type' => 'id');
$variables_to_clean['super_login'] = array('value' => $_POST['super_login'], 'type' => 'text');
$variables_to_clean['super_password'] = array('value' => $_POST['super_password'], 'type' => 'text');
$variables_to_clean['titre'] = array('value' => $_POST['titre'], 'type' => 'text', 'sub_type' => 'nom', 'option' => array('required' => TRUE), 'class' => 'benevole');
$variables_to_clean['nom'] = array('value' => ucfirst($_POST['nom']), 'type' => 'text', 'sub_type' => 'nom', 'option' => array('required' => TRUE), 'class' => 'benevole');
$variables_to_clean['prenom'] = array('value' => ucfirst($_POST['prenom']), 'type' => 'text', 'sub_type' => 'nom', 'option' => array('required' => TRUE), 'class' => 'benevole');
$variables_to_clean['adresse'] = array('value' => $_POST['adresse'], 'type' => 'text', 'option' => array('required' => TRUE), 'class' => 'benevole');
$variables_to_clean['adresse_complement'] = array('value' => ($_POST['adresse_complement']), 'type' => 'text', 'class' => 'benevole');
$variables_to_clean['npa'] = array('value' => $_POST['npa'], 'type' => 'text', 'sub_type' => 'nombre', 'option' => array('required' => TRUE), 'class' => 'benevole');
$variables_to_clean['ville'] = array('value' => ucfirst($_POST['ville']), 'type' => 'text', 'option' => array('required' => TRUE), 'class' => 'benevole');
$variables_to_clean['pays'] = array('value' => ucfirst($_POST['pays']), 'type' => 'text', 'option' => array('required' => TRUE), 'class' => 'benevole');
$variables_to_clean['tel_fixe'] = array('value' => $_POST['tel_fixe'], 'type' => 'tel', 'class' => 'benevole');
$variables_to_clean['tel_professionnel'] = array('value' => $_POST['tel_professionnel'], 'type' => 'tel', 'class' => 'benevole');
$variables_to_clean['tel_mobile'] = array('value' => $_POST['tel_mobile'], 'type' => 'tel', 'class' => 'benevole');
$variables_to_clean['email'] = array('value' => $_POST['email'], 'type' => 'email', 'class' => 'benevole');
$variables_to_clean['iban'] = array('value' => $_POST['iban'], 'type' => 'text', 'class' => 'benevole');
$variables_to_clean['ccp'] = array('value' => $_POST['ccp'], 'type' => 'text', 'class' => 'benevole');
$variables_to_clean['info_diverses'] = array('value' => ($_POST['info_diverses']), 'type' => 'text', 'class' => 'benevole');
$variables_to_clean['is_super_admin'] = array('value' => $_POST['is_super_admin'], 'type' => 'bool', 'class' => 'benevole');

$variables_to_clean['password_actual'] = array('value' => $_POST['password_actual'], 'type' => 'text', 'class' => 'benevole_password');
$variables_to_clean['password_new'] = array('value' => $_POST['password_new'], 'type' => 'text', 'class' => 'benevole_password');
$variables_to_clean['password_new_confirm'] = array('value' => $_POST['password_new_confirm'], 'type' => 'text', 'class' => 'benevole_password');


//propre a la filiale
$variables_to_clean['is_active'] = array('value' => $_POST['is_active'], 'type' => 'bool', 'class' => 'benevole_participation_filiale');
$variables_to_clean['filiale_login'] = array('value' => $_POST['filiale_login'], 'type' => 'text');
$variables_to_clean['id_benevole'] = array('value' => $_POST['id_benevole'], 'type' => 'id');
$variables_to_clean['id_filiale'] = array('value' => $_POST['id_filiale'], 'type' => 'id');
$variables_to_clean['is_permanencier'] = array('value' => $_POST['is_permanencier'], 'type' => 'bool', 'class' => 'benevole_participation_filiale');
$variables_to_clean['is_transporteur'] = array('value' => $_POST['is_transporteur'], 'type' => 'bool', 'class' => 'benevole_participation_filiale');
	$variables_to_clean['do_transports_locaux'] = array('value' => $_POST['do_transports_locaux'], 'type' => 'bool', 'class' => 'benevole_participation_filiale');
	$variables_to_clean['do_transports_geneve'] = array('value' => $_POST['do_transports_geneve'], 'type' => 'bool', 'class' => 'benevole_participation_filiale');
	$variables_to_clean['do_transports_lausanne'] = array('value' => $_POST['do_transports_lausanne'], 'type' => 'bool', 'class' => 'benevole_participation_filiale');
	$variables_to_clean['do_transports_holidays'] = array('value' => $_POST['do_transports_holidays'], 'type' => 'bool', 'class' => 'benevole_participation_filiale');
$variables_to_clean['is_administrateur_filiale'] = array('value' => $_POST['is_administrateur_filiale'], 'type' => 'bool', 'class' => 'benevole_participation_filiale');
$variables_to_clean['has_external_login'] = array('value' => $_POST['has_external_login'], 'type' => 'bool', 'class' => 'benevole_participation_filiale');


//contrainte
$variables_to_clean['benevole_contrainte_beneficiaire'] = array('value' => $_POST['benevole_contrainte_beneficiaire'], 'type' => 'id', 'class' => 'contrainte');

//disponibilite
//$categorie_dispo_array = array('transport', 'permanence', 'appel');
$categorie_dispo_array = Benevole_Disponibilite_Categorie::get_all_nom_in_array();

foreach ($categorie_dispo_array as $row) {
	$categorie_dispo_id_array[] = Benevole_Disponibilite_Categorie::get_id_from_nom($row);
}

foreach ($categorie_dispo_array as $index => $categorie_dispo) {
	for ($i=1; $i<=7; $i++) {
		for ($j=1; $j<=3; $j++) {
			$variables_to_clean['dispo_' . $categorie_dispo . '-jour_' . $i. '-periode_' . $j] = array('value' => $_POST['dispo_' . $categorie_dispo .'-jour_' . $i. '-periode_' . $j], 'type' => 'bool', 'nom_categorie' => $categorie_dispo , 'id_categorie' => $categorie_dispo_id_array[$index], 'id_jour_semaine' => $i, 'id_periode_journee' => $j, 'class' => 'benevole_disponibilite_standard');
		}
	}
}

$variables_to_clean = clean_variables($variables_to_clean);

$data_to_display = $variables_to_clean[0];

		
switch ($action) {
	case "add":	
		if ($variables_to_clean[1] > 0) { //chech error ou empty
			echo Benevole::form('add', $data_to_display);
		} else {
			//creation de la nouvelle entree dans la DB et reouveture du dashboard
			$tmp_benevole = new Benevole(0, $data_to_display['super_password']['value'], $data_to_display['titre']['value'], $data_to_display['nom']['value'], $data_to_display['prenom']['value'], $data_to_display['adresse']['value'], $data_to_display['adresse_complement']['value'], $data_to_display['npa']['value'], $data_to_display['ville']['value'], $data_to_display['pays']['value'], $data_to_display['tel_fixe']['value'], $data_to_display['tel_professionnel']['value'], $data_to_display['tel_mobile']['value'], $data_to_display['email']['value'], $data_to_display['iban']['value'], $data_to_display['ccp']['value'], $data_to_display['info_diverses']['value'], $data_to_display['is_super_admin']['value']);
			
			//affiliation
			$tmp_benevole->ajouterParticipationDansFiliale($_SESSION['filiale']['id'], $data_to_display['is_permanencier']['value'], $data_to_display['is_transporteur']['value'], $data_to_display['do_transports_locaux']['value'], $data_to_display['do_transports_geneve']['value'], $data_to_display['do_transports_lausanne']['value'], $data_to_display['do_transports_holidays']['value'], $data_to_display['is_administrateur_filiale']['value'], $data_to_display['has_external_login']['value']);
			
			
			//contrainte si necessaire
			$check_loading_class = load_class_and_interface(array('Beneficiaire'));
			if (isset($data_to_display['benevole_contrainte_beneficiaire']) && is_numeric($data_to_display['benevole_contrainte_beneficiaire']['value']) && Beneficiaire::id_exists($data_to_display['benevole_contrainte_beneficiaire']['value'])) {
				$tmp_transporteur = new Transporteur($tmp_benevole->get_id(), $_SESSION['filiale']['id']);
				$tmp_transporteur->ajouterContrainteBeneficiaire($data_to_display['benevole_contrainte_beneficiaire']['value']);
				
			}
			
			echo Benevole::form('list');
		}
		break;
	
	case "edit":
		switch ($sub_module) {
			case "disponibilite_transport";
				break;
			default: 
				if ($variables_to_clean[1] > 0) {
					if (isset($data_to_display['id']) && Benevole::id_exists(Benevole::get_super_id_benvole_from_id_benevole_filiale($data_to_display['id']['value']))) {
							if ($_POST['form'] == 'base') {
								// le beneficiaire est connu, recharge les donnees car il y a des erreurs
								echo Benevole::form('edit', $data_to_display);
								
							} elseif ($_POST['form'] == 'choose') {
								//form choose, charge les donnees actuelles presentes dans la DB
								echo Benevole::form('edit', new Benevole(Benevole::get_super_id_benvole_from_id_benevole_filiale($data_to_display['id']['value'])));
							}
					} else {
						if (isset($_GET['id'])) {
							//charge le beneficiaire de $_GET
							echo Benevole::form('edit', new Benevole(Benevole::get_super_id_benvole_from_id_benevole_filiale($_GET['id'])));
						} else {
							//charge form_choose car le beneficiaire est inconnu 
							echo Benevole::form('edit', $data_to_display);
						}
					}
				} else {
					if (isset($data_to_display['id']) && Benevole::id_exists(Benevole::get_super_id_benvole_from_id_benevole_filiale($data_to_display['id']['value']))) {
						//edition de l'entree dans la DB
						
						$tmp_benevole = new Benevole(Benevole::get_super_id_benvole_from_id_benevole_filiale($data_to_display['id']['value']));
						
						//repartition des donnees
						foreach ($data_to_display as $index => $row) {
							if ($row['class'] == 'benevole') {
								$attr_benevole[] = $index;
								$new_value_benevole[] = $row['value'];
							} elseif ($row['class'] == 'benevole_participation_filiale') {
								$attr_benevole_participation_filiale[] = $index;
								$new_value_benevole_participation_filiale[] = $row['value'];
							}
						}
						
						$tmp_benevole->editerAttributs($attr_benevole, $new_value_benevole);
						$tmp_benevole->editerAttributsParticipationFiliale($attr_benevole_participation_filiale, $new_value_benevole_participation_filiale);
						
						
						//tableau de disponibilite{
						foreach ($categorie_dispo_array as $categorie_dispo) {
							if (isset($_POST['form_disponibilite_' . $categorie_dispo])){
								$array_dispo_need_update[] = $categorie_dispo;
							}
						}
							
						foreach($data_to_display as $row) {
							if ($row['class'] == 'benevole_disponibilite_standard' && isset($row['id_categorie']) && isset($row['id_jour_semaine']) && isset($row['id_periode_journee'])) {
								$array_disponibilite_benevole[] = array('value' => $row['value'], 'id_categorie' => $row['id_categorie'], 'id_jour_semaine' => $row['id_jour_semaine'], 'id_periode_journee' => $row['id_periode_journee']);
							}
						}
						$tmp_benevole->editerAttributsDisponibilite($array_disponibilite_benevole, $array_dispo_need_update);
						
						
						//contrainte si necessaire
						$check_loading_class = load_class_and_interface(array('Beneficiaire'));
						if (isset($data_to_display['benevole_contrainte_beneficiaire']) && is_numeric($data_to_display['benevole_contrainte_beneficiaire']['value']) && Beneficiaire::id_exists($data_to_display['benevole_contrainte_beneficiaire']['value'])) {
							$tmp_transporteur = new Transporteur($data_to_display['id']['value']);
							$tmp_transporteur->ajouterContrainteBeneficiaire($data_to_display['benevole_contrainte_beneficiaire']['value']);
						}
						
						
						echo Benevole::form('list');
						
					} else {
						
					}
				}	
		}
		
		break;
	case "view":
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
		break;
	case "list":
		echo Benevole::form('list');
		break;
	case "change_password":
		
		if (isset($data_to_display['password_actual']['value']) && isset($data_to_display['password_new']['value']) && isset($data_to_display['password_new_confirm']['value']) && $data_to_display['password_new']['value'] == $data_to_display['password_new_confirm']['value'] && $data_to_display['password_new_confirm']['value'] != '' && $data_to_display['password_actual']['value'] != '') {
			
			if (isset($_SESSION['benevole']['id']) && Benevole::id_exists($_SESSION['benevole']['id'])) {
				$tmp_benevole = new Benevole($_SESSION['benevole']['id']);
				$status_change_password = $tmp_benevole->changeLocalPassword($data_to_display['password_actual']['value'], $data_to_display['password_new']['value']);
				
				if ($status_change_password === TRUE) {
					$html_code = 'Changement de mot de passe effectué avec succès';
				} else {
					$html_code = 'Impossible de passer le check local password';
				}
			} else {
				$html_code = 'Problème avec le bénévole id en session';
			}
			
			echo $html_code;
			
		} else {
			echo Benevole::form('change_password');
		}
		
		break;
	case "reinit_password":
		$tmp_benevole = new Benevole($_SESSION['benevole']['id']);
		
		
		if ($tmp_benevole->checkIsSuperAdmin() || $tmp_benevole->checkIsAdminOfFiliale($_SESSION['filiale']['id'])) {
			if (isset($_GET['id_filiale_benevole']) && Benevole::id_exists(Benevole::get_super_id_benvole_from_id_benevole_filiale($_GET['id_filiale_benevole']))) {
				$benevole_to_change_psswd = new Benevole(Benevole::get_super_id_benvole_from_id_benevole_filiale($_GET['id_filiale_benevole']));
				
				$benevole_to_change_psswd_nom_complet = $benevole_to_change_psswd->get_nom_complet();
				$nom = substr(mb_strtolower($benevole_to_change_psswd_nom_complet['nom']), 0, 1);
				$prenom = substr(mb_strtolower($benevole_to_change_psswd_nom_complet['prenom']), 0, 1);
				
				$change_pssd_status = $benevole_to_change_psswd->changeLocalPassword('', $prenom . $nom);
			}
		} else {
			$change_pssd_status = FALSE;
		}
		
		if ($change_pssd_status === TRUE) {
			echo '<p>Changement de mot de passe effectué avec succès pour ' . format_titre($benevole_to_change_psswd_nom_complet['titre']) . ' ' . mb_strtoupper(stripAccents($benevole_to_change_psswd_nom_complet['nom'])) . '</p>';
		} else {
			echo '<p>Impossible de changer le mot de passe</p>';
		}
		
		//echo Benevole::form('list');
		break;
	default:
		echo Benevole::form('list');
		break;
}

?>