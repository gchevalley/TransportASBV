<?php

load_class_and_interface(array('Beneficiaire', 'Benevole', 'Transport'));

if (!isset($_SESSION['backup'])) {
	$backup_status = backup_website_and_db();

	$_SESSION['backup']['last_backup'] = mktime();
}

$weekdays[1] = 'lundi';
$weekdays[2] = 'mardi';
$weekdays[3] = 'mercredi';
$weekdays[4] = 'jeudi';
$weekdays[5] = 'vendredi';
$weekdays[6] = 'samedi';
$weekdays[7] = 'dimanche';

$html_code = '';
global $cfg;


//numeros importants
$sql = "SELECT lieu.* FROM lieu WHERE numero_important=1 ORDER BY nom";
$sth = $dbh->query($sql);
$result = $sth->fetchAll(PDO::FETCH_ASSOC);


//charge le help si existant
if ( isset($action) ) {
	if (get_file_help_path(__FILE__, $action)) {
		//charge le lien pour afficher l'aide
		$html_code .= show_help_link();
	}
}

$html_code .= '<div id="numeros_importants">';

	$html_code .= '<h1>Numéros importants</h1>';

	$html_code .= '<table>';
		$html_code .= '<thead>';
			$html_code .= '<tr>';
				$html_code .= '<th>Nom</th>';
				$html_code .= '<th>Tél fixe</th>';
			$html_code .= '</tr>';
		$html_code .= '</thead>';

		$html_code .= '<tbody>';
			foreach ($result as $row) {
				$html_code .= '<tr>';

					$html_code .= '<td>';
						$html_code .= $row['nom'];
					$html_code .= '</td>';

					$html_code .= '<td>';
						$html_code .= format_tel($row['tel_fixe']);
					$html_code .= '</td>';

				$html_code .= '</tr>';
			}
		$html_code .= '</tbody>';
	$html_code .= '</table>';
$html_code .= '</div>';

/*
$html_code .= '<p>';
	$html_code .= '<a href="" id="sh_numeros_importants">';
		$html_code .= 'Afficher les numéros importants';
	$html_code .= '</a>';
$html_code .= '</p>';
*/

$html_code .= '<p>';
	$html_code .= '<a href="../' . $cfg['DIRECTORY']['extract'] . '/transports_sans_chauffeur.xls">';
		$html_code .= '<strong>Imprimer</strong> la liste des transports sans chauffeur';
	$html_code .= '</a>';
$html_code .= '</p>';


//transport sans chauffeur
$sql = "SELECT beneficiaire.*, transport.* ";
$sql .= " FROM transport INNER JOIN beneficiaire ON transport.id_beneficiaire = beneficiaire.id ";
$sql .= " WHERE transport.id NOT IN ( ";

	$sql .= "SELECT transport.id";
	$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport";

$sql .= " )";

$sql .= " AND transport.id_filiale=" . $_SESSION['filiale']['id'] . " ";
$sql .= " AND transport.date_transport>='" . date('Y-m-d') . "' ";
$sql .= " AND transport.is_annule=0 ";
$sql .= " ORDER BY transport.date_transport, transport.heure_debut";

$sth = $dbh->query($sql);
$result = $sth->fetchAll(PDO::FETCH_ASSOC);

$i=1;
if (count($result) > 0) {

	load_class_and_interface(array('PHPExcel'));

	$html_code .= '<h1>';
		$html_code .= 'Transports sans chauffeur';
	$html_code .= '</h1>';


	$first_row = TRUE;
	foreach ($result as $row) {
		$nbre_jour_between_today_date_transport = diff_date_without_weekend(date('j'), date('n'), date('Y'), date('j', strtotime($row['date_transport'])), date('n', strtotime($row['date_transport'])), date('Y', strtotime($row['date_transport'])));

		if ($nbre_jour_between_today_date_transport > 5) {
			$class_transport = 'hide_transport_sans_chauffeur';

			if ($first_row === true) {
				//aucun transport a afficher !
				$html_code .= '<p class="dashboard_info_no_transport_sans_chauffeur_urgent">';
					$html_code .= 'Aucun transport urgent, le prochain est dans ' . $nbre_jour_between_today_date_transport . ' jours ouvrables';
				$html_code .= '</p>';
			}

			break;

		} else {
			$class_transport='show_transport_sans_chauffeur';
			break;
		}
	}

	$html_code .= '<table>';
		$html_code .= '<thead>';
			$html_code .= '<tr class="' . $class_transport . '">';
				$html_code .= '<th>Date &amp; Heure</th>';
				$html_code .= '<th>Passager</th>';
				$html_code .= '<th>Ville départ</th>';
				$html_code .= '<th>Ville arrivée</th>';
				$html_code .= '<th></th>'; // aller-retour
				$html_code .= '<th></th>'; // Chercher un chauffeur
				$html_code .= '<th></th>'; // Editer le transport
				$html_code .= '<th></th>'; // Annuler le transport
			$html_code .= '</tr>';
		$html_code .= '</thead>';


		//xls
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()->setCreator("ASBV Transport")
					 ->setLastModifiedBy("ASBV Transport")
					 ->setTitle("Excel5 transports sans chauffeurs ASBV Transport")
					 ->setSubject("Excel5 transports sans chauffeurs ASBV Transport")
					 ->setDescription("generated using PHP classes.")
					 ->setKeywords("asbv transport rapport")
					 ->setCategory("rapport");

		$xls_col_date = 1;
		$xls_col_heure = 2;
		$xls_col_passager = 3;
		$xls_col_depart = 4;
		$xls_col_arrivee = 5;
		$xls_col_type = 6;

		$row_xls = $i;

		$column_xls = xlColumnValue($xls_col_date);
		$coor_xls = '' . $column_xls . '' . $row_xls;
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, 'Date');

		$column_xls = xlColumnValue($xls_col_heure);
		$coor_xls = '' . $column_xls . '' . $row_xls;
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, 'Heure');

		$column_xls = xlColumnValue($xls_col_passager);
		$coor_xls = '' . $column_xls . '' . $row_xls;
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, 'Passager');

		$column_xls = xlColumnValue($xls_col_depart);
		$coor_xls = '' . $column_xls . '' . $row_xls;
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, 'Départ');

		$column_xls = xlColumnValue($xls_col_arrivee);
		$coor_xls = '' . $column_xls . '' . $row_xls;
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, 'Arrivée');

		$column_xls = xlColumnValue($xls_col_type);
		$coor_xls = '' . $column_xls . '' . $row_xls;
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, 'Type');


		$i++;

		$last_date_txt = '';
		$count_hide_transport_sans_chauffeur = 0;
		$count_show_transport_sans_chauffeur = 0;

		$html_code .= '<tbody>';

			//header date
			foreach ($result as $row) {

				$row_xls = $i;

				$tmp_beneficiaire = new Beneficiaire($row['id_beneficiaire']);
				$tmp_beneficiaire_tel = $tmp_beneficiaire->get_telephone();

				//calcul le niveau d'urgence
				$nbre_jour_between_today_date_transport = diff_date_without_weekend(date('j'), date('n'), date('Y'), date('j', strtotime($row['date_transport'])), date('n', strtotime($row['date_transport'])), date('Y', strtotime($row['date_transport'])));


				//affiche les transports que pour les 5 prochains jours ouvrable
				if ($nbre_jour_between_today_date_transport > 5) {
					$class_transport = 'hide_transport_sans_chauffeur';
					$count_hide_transport_sans_chauffeur++;
				} else {
					$class_transport = 'show_transport_sans_chauffeur';
					$count_show_transport_sans_chauffeur++;
				}

				if ($last_date_txt != $row['date_transport']) {

					$weekday = date('N', strtotime($row['date_transport']));

					foreach($weekdays as $idx_day => $day) {
						if ($idx_day == $weekday) {
							$txt_weekday = $day;
						}
					}

					$html_code .= '<tr class="' . $class_transport . '">';
						$html_code .= '<th><a class="header_date" href="#top">' . date_yyyymmdd_to_ddmmyyyy($row['date_transport']) . ' - ' . substr($txt_weekday,0 ,3) . '</a></th>';
					$html_code .= '</tr>';

					$last_date_txt = $row['date_transport'];
				}


				$column_xls = xlColumnValue($xls_col_date);
				$coor_xls = '' . $column_xls . '' . $row_xls;
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, date_yyyymmdd_to_ddmmyyyy($row['date_transport']));

				$column_xls = xlColumnValue($xls_col_heure);
				$coor_xls = '' . $column_xls . '' . $row_xls;
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, time_hhmmss_to_hhmm($row['heure_debut']));



				$html_code .= '<tr class="emergency emergency_' . $nbre_jour_between_today_date_transport . ' ' . $class_transport . '">';

						if ($row['info_diverses'] != '') {
							$html_code .= '<td title="' . $row['info_diverses'] . '">';
						} else {
							$html_code .= '<td>';
						}

						$html_code .= '<a class="link_dialog" href="?module=transport&amp;action=view&amp;id=' . $row['id'] . '">';
							$html_code .= time_hhmmss_to_hhmm($row['heure_debut']);
						$html_code .= '</a>';
					$html_code .= '</td>';

					$html_code .= '<td>';

						$tel_string = '';

						foreach ($tmp_beneficiaire_tel as $type_tel => $tel) {
							$tel_string .= str_replace('tel_', '', $type_tel) . ' : ' . format_tel($tel) . '   ';
						}

						$html_code .= '<a title="' . $tel_string . '" class="link_dialog" href="?module=beneficiaire&amp;action=view&amp;id=' . $row['id_beneficiaire'] . '">';
							$html_code .= mb_strtoupper(stripAccents($row['nom'])) . ', ' . $row['prenom'];
						$html_code .= '</a>';
					$html_code .= '</td>';


					$column_xls = xlColumnValue($xls_col_passager);
					$coor_xls = '' . $column_xls . '' . $row_xls;
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, mb_strtoupper(stripAccents($row['nom'])) . ', ' . $row['prenom']);



					$point_depart = unserialize(($row['point_depart']));
					$point_arrivee = unserialize($row['point_arrivee']);

					$column_xls = xlColumnValue($xls_col_depart);
					$coor_xls = '' . $column_xls . '' . $row_xls;
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, mb_strtoupper(format_ville(stripAccents($point_depart['ville']))));

					$column_xls = xlColumnValue($xls_col_arrivee);
					$coor_xls = '' . $column_xls . '' . $row_xls;
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, mb_strtoupper(format_ville(stripAccents($point_arrivee['ville']))));



					if (isset($point_depart['adresse'])) {
						if ($point_depart['type'] == 'beneficiaire') {
							$tag_title = 'Domicile : ' . $point_depart['adresse'];
						} elseif ($point_depart['type'] == 'lieu') {
							$tag_title = $point_depart['nom_complet'] . ' - ' . $point_depart['adresse'];
						}

					} else {
						$tag_title = '""';
					}


					$html_code .= '<td>';
						$html_code .= '<a title="' . $tag_title . '" class="link_dialog" href="?module=transporteur&action=city_near&city_near=' . $point_depart['ville'] . '">';
							$html_code .= mb_strtoupper(format_ville(stripAccents($point_depart['ville'])));
						$html_code .= '</a>';
					$html_code .= '</td>';



					if (isset($point_arrivee['adresse'])) {
						if ($point_arrivee['type'] == 'beneficiaire') {
							$tag_title = 'Domicile : ' . $point_arrivee['adresse'];
						} elseif ($point_arrivee['type'] == 'lieu') {
							$tag_title = $point_arrivee['nom_complet'] . ' - ' . $point_arrivee['adresse'];
						}

					} else {
						$tag_title = '""';
					}


					$html_code .= '<td title="' . $tag_title . '">';
						$html_code .= mb_strtoupper(format_ville(stripAccents($point_arrivee['ville'])));
					$html_code .= '</td>';

					$html_code .= '<td>';
						$html_code .= format_type_trajet($row['aller_retour'], $row['duree_approximative']);

						$column_xls = xlColumnValue($xls_col_type);
						$coor_xls = '' . $column_xls . '' . $row_xls;
						$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, preg_replace('/<(\/|)strong>/', '', format_type_trajet($row['aller_retour'], $row['duree_approximative'])));

					$html_code .= '</td>';

					$html_code .= '<td>';
						$html_code .= '<a href="?module=transport&amp;action=find_driver&amp;id=' . $row['id'] . '">';
							$html_code .= '<strong>Suggestion de chauffeurs</strong>';
						$html_code .= '</a>';
					$html_code .= '</td>';

					$html_code .= '<td>';
						$html_code .= '<a href="?module=transport&amp;action=edit&amp;id=' . $row['id'] . '">';
							$html_code .= 'Modifier';
						$html_code .= '</a>';
					$html_code .= '</td>';

					$html_code .= '<td>';
						$html_code .= '<a class="link_ajax_get" href="?module=transport&amp;action=cancel&amp;id=' . $row['id'] . '">';
							$html_code .= 'Annuler';
						$html_code .= '</a>';
					$html_code .= '</td>';

				$html_code .= '</tr>';

				$i++; //compteur tableau excel
			}


			$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('' . xlColumnValue($xls_col_date))->setAutoSize(true);
			$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('' . xlColumnValue($xls_col_heure))->setAutoSize(true);
			$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('' . xlColumnValue($xls_col_passager))->setAutoSize(true);
			$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('' . xlColumnValue($xls_col_depart))->setAutoSize(true);
			$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('' . xlColumnValue($xls_col_arrivee))->setAutoSize(true);
			$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('' . xlColumnValue($xls_col_type))->setAutoSize(true);

			global $cfg;
			if (!is_dir('../' . $cfg['DIRECTORY']['extract'])) {
				mkdir('../' . $cfg['DIRECTORY']['extract']);
			}

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$objWriter->save('../' . $cfg['DIRECTORY']['extract'] . '/transports_sans_chauffeur.xls');
			//$objWriter->save('transports_sans_chauffeur.xls');

		$html_code .= '</tbody>';
	$html_code .= '</table>';
}

//afficher le lien pour les transports masques
if ( isset($count_hide_transport_sans_chauffeur) ) {
	if ($count_hide_transport_sans_chauffeur > 0) {
		$html_code .= '<p>';
			$html_code .= '<a id="show_remaining_transports_sans_chauffeur" href="">';
				$html_code .= '<em>Afficher les transports restants sans chauffeur</em>';
			$html_code .= '</a>';
		$html_code .= '</p>';
	}
}


//prochain transport (mois courant)
$sql = "SELECT transport_transporteur.*, transport.* ";
$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport ";
$sql .= " WHERE transport.id_filiale=" . $_SESSION['filiale']['id'] . " ";
$sql .= " AND transport.date_transport BETWEEN '" . date('Y-m-d') . "' ";

if (date('d') < 10) {
	$sql .= " AND '" . lastday(date('m'), date('Y')) . "'";
} else {
	$sql .= " AND '" . date('Y-m-d', mktime(0,0,0, date('m'), date('d') + 20, date('Y'))) . "'";
}

$sql .= " AND transport.is_annule=0 ";
$sql .= " ORDER BY transport.date_transport, transport.heure_debut";

$sth = $dbh->query($sql);
$result = $sth->fetchAll(PDO::FETCH_ASSOC);

$i = 1;
if (count($result) > 0) {

	load_class_and_interface(array('PHPExcel'));

	$nbre_jours_a_afficher = 5;

	//xls
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getProperties()->setCreator("ASBV Transport")
				 ->setLastModifiedBy("ASBV Transport")
				 ->setTitle("Excel5 transports sans chauffeurs ASBV Transport")
				 ->setSubject("Excel5 transports sans chauffeurs ASBV Transport")
				 ->setDescription("generated using PHP classes.")
				 ->setKeywords("asbv transport rapport")
				 ->setCategory("rapport");

	$xls_col_date = 1;
	$xls_col_heure = 2;
	$xls_col_passager = 3;
	$xls_cols_transporteur = 4;
	$xls_col_depart = 5;
	$xls_col_arrivee = 6;
	$xls_col_type = 7;

	$row_xls = $i;

	$column_xls = xlColumnValue($xls_col_date);
	$coor_xls = '' . $column_xls . '' . $row_xls;
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, 'Date');

	$column_xls = xlColumnValue($xls_col_heure);
	$coor_xls = '' . $column_xls . '' . $row_xls;
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, 'Heure');

	$column_xls = xlColumnValue($xls_col_passager);
	$coor_xls = '' . $column_xls . '' . $row_xls;
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, 'Passager');

	$column_xls = xlColumnValue($xls_cols_transporteur);
	$coor_xls = '' . $column_xls . '' . $row_xls;
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, 'Transporteur');

	$column_xls = xlColumnValue($xls_col_depart);
	$coor_xls = '' . $column_xls . '' . $row_xls;
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, 'Départ');

	$column_xls = xlColumnValue($xls_col_arrivee);
	$coor_xls = '' . $column_xls . '' . $row_xls;
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, 'Arrivée');

	$column_xls = xlColumnValue($xls_col_type);
	$coor_xls = '' . $column_xls . '' . $row_xls;
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, 'Type');

	$i++;



	$html_code .= '<h1>Prochains transports déjà attribués</h1>';

	/*
	$html_code .= '<p>';
		$html_code .= '<a href="?module=transport&amp;action=list">';
			$html_code .= 'Voir tous les transports du mois en cours';
		$html_code .= '</a>';
	$html_code .= '</p>';
	*/

	$html_code .= '<table class="OddEven">';

		$html_code .= '<thead>';
			$html_code .= '<tr>';
				$html_code .= '<th>Date &amp; Heure</th>';
				$html_code .= '<th>Passager</th>';
				$html_code .= '<th>Transporteur</th>';
				$html_code .= '<th>Ville départ</th>';
				$html_code .= '<th>Ville arrivée</th>';
				$html_code .= '<th></th>'; // Aller-retour
				$html_code .= '<th></th>'; // Editer le transport
				$html_code .= '<th></th>'; // Annuler le transport
			$html_code .= '</tr>';
		$html_code .= '</thead>';

		$html_code .= '<tbody>';

			$last_date_txt = '';
			$count_hide_transport = 0;
			foreach ($result as $row) {

				$row_xls = $i;

				if (diff_date_without_weekend(date('d'), date('m'), date('Y'), date('d', strtotime($row['date_transport'])), date('m', strtotime($row['date_transport'])), date('Y', strtotime($row['date_transport']))) > $nbre_jours_a_afficher) {
					$class_transport = 'hide_transport';
					$count_hide_transport++;
				} else {
					$class_transport = 'show_transport';
				}

				$transport_avec_chauffeur = FALSE;

				if (!is_null($row['id_transporteur']) && is_numeric($row['id_transporteur']) && Transporteur::id_exists($row['id_transporteur'])) {
					$tmp_transporteur = new Transporteur($row['id_transporteur']);
					$tmp_transporteur_nom_complet = $tmp_transporteur->get_nom_complet();
					$tmp_transporteur_tel = $tmp_transporteur->get_telephone();
					$transport_avec_chauffeur = TRUE;
				}

				$weekday = date('N', strtotime($row['date_transport']));

				foreach($weekdays as $idx_day => $day) {
					if ($idx_day == $weekday) {
						$txt_weekday = $day;
					}
				}

				$column_xls = xlColumnValue($xls_col_date);
				$coor_xls = '' . $column_xls . '' . $row_xls;
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, date_yyyymmdd_to_ddmmyyyy($row['date_transport']));

				$column_xls = xlColumnValue($xls_col_heure);
				$coor_xls = '' . $column_xls . '' . $row_xls;
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, time_hhmmss_to_hhmm($row['heure_debut']));


				if ($last_date_txt != $row['date_transport']) {
					$html_code .= '<tr class="' . $class_transport . '">';
						$html_code .= '<th><a class="header_date" href="#top">' . date_yyyymmdd_to_ddmmyyyy($row['date_transport']) . ' - ' . substr($txt_weekday, 0, 3) . '</a></th>';
					$html_code .= '</tr>';

					$last_date_txt = $row['date_transport'];
				}


				if ($transport_avec_chauffeur) {
					$html_code .= '<tr class="' . $class_transport . '">';
				} else {
					$nbre_jour_between_today_date_transport = diff_date_without_weekend(date('j'), date('n'), date('Y'), date('j', strtotime($row['date_transport'])), date('n', strtotime($row['date_transport'])), date('Y', strtotime($row['date_transport'])));
					$html_code .= '<tr class="emergency_' . $nbre_jour_between_today_date_transport . ' ' . $class_transport . '">';
				}




					$html_code .= '<td>';
						$html_code .= '<a class="link_dialog" href="?module=transport&amp;action=view&amp;id=' . $row['id'] . '">';
							$html_code .= time_hhmmss_to_hhmm($row['heure_debut']);
						$html_code .= '</a>';
					$html_code .= '</td>';

					$tmp_beneficiaire = new Beneficiaire($row['id_beneficiaire']);
					$tmp_array_nom_complet = $tmp_beneficiaire->get_nom_complet();
					$tmp_beneficiaire_tel = $tmp_beneficiaire->get_telephone();

					$column_xls = xlColumnValue($xls_col_passager);
					$coor_xls = '' . $column_xls . '' . $row_xls;
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, mb_strtoupper(stripAccents($tmp_array_nom_complet['nom'])) . ', ' . $tmp_array_nom_complet['prenom']);



					$html_code .= '<td>';

						$tel_string = '';

						foreach ($tmp_beneficiaire_tel as $type_tel => $tel) {
							$tel_string .= str_replace('tel_', '', $type_tel) . ' : ' . format_tel($tel) . '   ';
						}

						$html_code .= '<a title="' . $tel_string . '" class="link_dialog" href="?module=beneficiaire&amp;action=view&amp;id=' . $row['id_beneficiaire'] . '">';
							$html_code .= mb_strtoupper(stripAccents($tmp_array_nom_complet['nom'])) . ', ' . $tmp_array_nom_complet['prenom'];
						$html_code .= '</a>';
					$html_code .= '</td>';


					$html_code .= '<td>';
						if ($transport_avec_chauffeur) {

							$tel_string = '';

							foreach ($tmp_transporteur_tel as $type_tel => $tel) {
								$tel_string .= str_replace('tel_', '', $type_tel) . ' : ' . format_tel($tel) . '   ';
							}


							$column_xls = xlColumnValue($xls_cols_transporteur);
							$coor_xls = '' . $column_xls . '' . $row_xls;
							$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, mb_strtoupper(stripAccents($tmp_transporteur_nom_complet['nom'])) . ', ' . $tmp_transporteur_nom_complet['prenom']);


							$html_code .= '<a title="' . $tel_string . '" class="link_dialog" href="?module=benevole&amp;action=view&amp;id=' . $row['id_transporteur'] . '">';
								$html_code .= mb_strtoupper(stripAccents($tmp_transporteur_nom_complet['nom'])) . ', ' . $tmp_transporteur_nom_complet['prenom'];
							$html_code .= '</a>';
						} else {
							$html_code .= '<a href="?module=transport&amp;action=find_driver&amp;id=' . $row['id'] . '">';
								$html_code .= '<strong>Chercher un chauffeur</strong>';
							$html_code .= '</a>';
						}
					$html_code .= '</td>';


					$point_depart = unserialize($row['point_depart']);
					$point_arrivee = unserialize($row['point_arrivee']);

					$column_xls = xlColumnValue($xls_col_depart);
					$coor_xls = '' . $column_xls . '' . $row_xls;
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, mb_strtoupper(format_ville(stripAccents($point_depart['ville']))));

					$column_xls = xlColumnValue($xls_col_arrivee);
					$coor_xls = '' . $column_xls . '' . $row_xls;
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, mb_strtoupper(format_ville(stripAccents($point_arrivee['ville']))));

					if ($point_depart['type'] == 'beneficiaire') {
						$tag_title = 'Domicile du passager : ' . $point_depart['adresse'];
					} elseif ($point_depart['type'] == 'lieu') {
						$tag_title = $point_depart['nom_complet'];

						if (isset($point_depart['adresse']) && $point_depart['adresse'] != '') {
							$tag_title .= ' - ' . $point_depart['adresse'];
						}
					}


					$html_code .= '<td title="' . $tag_title . '">';
						$html_code .= mb_strtoupper(format_ville(stripAccents($point_depart['ville'])));
					$html_code .= '</td>';


					if ($point_arrivee['type'] == 'beneficiaire') {
						$tag_title = 'Domicile du passager : ' . $point_arrivee['adresse'];
					} elseif ($point_arrivee['type'] == 'lieu') {
						$tag_title = $point_arrivee['nom_complet'];

						if (isset($point_arrivee['adresse']) && $point_arrivee['adresse'] != '') {
							$tag_title .= ' - ' . $point_arrivee['adresse'];
						}
					}

					$html_code .= '<td title="' . $tag_title. '">';
						$html_code .= mb_strtoupper(stripAccents(format_ville($point_arrivee['ville'])));
					$html_code .= '</td>';

					$html_code .= '<td>';
						$html_code .= format_type_trajet($row['aller_retour'], $row['duree_approximative']);

						$column_xls = xlColumnValue($xls_col_type);
						$coor_xls = '' . $column_xls . '' . $row_xls;
						$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coor_xls, preg_replace('/<(\/|)strong>/', '', format_type_trajet($row['aller_retour'], $row['duree_approximative'])));

					$html_code .= '</td>';


					$html_code .= '<td>';
						$html_code .= '<a href="?module=transport&amp;action=edit&amp;id=' . $row['id'] . '">';
							$html_code .= 'Modifier';
						$html_code .= '</a>';
					$html_code .= '</td>';


					$html_code .= '<td>';
						$html_code .= '<a class="link_ajax_get" href="?module=transport&amp;action=cancel&amp;id=' . $row['id'] . '">';
							$html_code .= 'Annuler';
						$html_code .= '</a>';
					$html_code .= '</td>';

				$html_code .= '</tr>';
				$i++; //compteur tableau excel
			}

			$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('' . xlColumnValue($xls_col_date))->setAutoSize(true);
			$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('' . xlColumnValue($xls_col_heure))->setAutoSize(true);
			$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('' . xlColumnValue($xls_col_passager))->setAutoSize(true);
			$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('' . xlColumnValue($xls_cols_transporteur))->setAutoSize(true);
			$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('' . xlColumnValue($xls_col_depart))->setAutoSize(true);
			$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('' . xlColumnValue($xls_col_arrivee))->setAutoSize(true);
			$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('' . xlColumnValue($xls_col_type))->setAutoSize(true);

			global $cfg;
			if (!is_dir('../' . $cfg['DIRECTORY']['extract'])) {
				mkdir('../' . $cfg['DIRECTORY']['extract']);
			}

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$objWriter->save('../' . $cfg['DIRECTORY']['extract'] . '/transports_avec_chauffeurs.xls');




		$html_code .= '</tbody>';
	$html_code .= '</table>';

	//afficher le lien pour les transports masques
	if ($count_hide_transport > 0) {
		$html_code .= '<p>';
			$html_code .= '<a id="show_remaining_transports" href="">';
				$html_code .= '<em>Afficher les transports restants déjà attribués</em>';
			$html_code .= '</a>';
		$html_code .= '</p>';
	}

}

if ( isset($action) ) {
	$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));
}

echo $html_code;


?>
