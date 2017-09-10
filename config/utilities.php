<?php

function stripAccents($string) {
	return str_replace(
        array(
            'à', 'â', 'ä', 'á', 'ã', 'å',
            'î', 'ï', 'ì', 'í',
            'ô', 'ö', 'ò', 'ó', 'õ', 'ø',
            'ù', 'û', 'ü', 'ú',
            'é', 'è', 'ê', 'ë',
            'ç', 'ÿ', 'ñ',
        ),
        array(
            'a', 'a', 'a', 'a', 'a', 'a',
            'i', 'i', 'i', 'i',
            'o', 'o', 'o', 'o', 'o', 'o',
            'u', 'u', 'u', 'u',
            'e', 'e', 'e', 'e',
            'c', 'y', 'n',
        ),
        $string
    );

}


function get_file_help_path($file_path, $action) {
	if (strpos($file_path, 'class')) {
		$file_path_retraite = str_replace ( '\\', '/', dirname($file_path));
	} else {
		$file_path_retraite = str_replace ( '\\', '/', $file_path);
	}


	global $base_dir;

	$file_help = str_replace($base_dir . '/', '', $file_path_retraite);

	$file_help = str_replace('/', '_', $file_help);

	if (strpos($file_path, 'class')) {

	} else {
		$file_help = substr($file_help, 0, -4);
	}


	if ($action != '') {
		$file_help .= '_' . $action;
	}

	$format_file = array('html', 'htm', 'txt');

	foreach ($format_file as $ext) {
		$file_help_test = $file_help . '.' . $ext;

		$test_if_file_exists = test_if_help_file_exists($file_help_test);

		if ($test_if_file_exists !== false) {
			return $test_if_file_exists;
		}
	}

	return FALSE;

}


function test_if_help_file_exists($help_file) {
	global $base_dir;

	$director_help_files = $base_dir . '/doc/help/';

	$test = file_exists($director_help_files . $help_file);

	if (file_exists($director_help_files . $help_file)) {
		return $director_help_files . $help_file;
	} else {

		return FALSE;
	}
}

function show_help_link($show_pics=TRUE) {
	$html_code = '';
	$html_code .= '<p class="show_help">';
		$html_code .= '<a class="show_help" href="">';

			if ($show_pics === TRUE) {
				$html_code .= '<img src="./img/help.png" />';
			}

			$html_code .= 'Afficher l\'aide';
		$html_code .= '</a>';
	$html_code .= '</p>';

	return $html_code;
}


function load_help_file_if_necessary($help_file_path, $format='dialog', $show_pics=TRUE) {
	$html_code = '';

	if (file_exists($help_file_path)) {
		$content_file = file_get_contents($help_file_path);

		if ($format == 'dialog') {
			$html_code .= '<div class="hide" id="help">';
		}
			//encodage dynamique si necessaire en utf8
			if (mb_detect_encoding($content_file, 'auto', TRUE) != 'UTF-8') {
				$html_code_page_help = mb_convert_encoding($content_file, 'UTF-8');
			} else {
				$html_code_page_help = $content_file;
			}


			//nettoyage si necssaire et isole la partie entre <body> et </body>
			if (strpos(mb_strtoupper($html_code_page_help), '<BODY')) {

				//repere le crochet de fermeture de body (si background etc.)
				$pos_open_body = strpos(mb_strtoupper($html_code_page_help), '<BODY');
				$start_pos = strpos($html_code_page_help, '>', strpos(mb_strtoupper($html_code_page_help), '<BODY'));

				$test_body_end = strpos(mb_strtoupper($html_code_page_help), '</BODY>');

				if ($test_body_end) {
					//$html_code_page_help = substr($html_code_page_help, (strpos(mb_strtoupper($html_code_page_help), '<BODY>')) + strlen('<BODY>'), (strpos(mb_strtoupper($html_code_page_help), '</BODY>'))-(strpos(mb_strtoupper($html_code_page_help), '<BODY>')+strlen('<BODY>')));
					$html_code_page_help = substr($html_code_page_help, (strpos($html_code_page_help, '>', strpos(mb_strtoupper($html_code_page_help), '<BODY')))+1, (strpos(mb_strtoupper($html_code_page_help), '</BODY>'))-(strpos($html_code_page_help, '>', strpos(mb_strtoupper($html_code_page_help), '<BODY')))+1);
				} else {
					//$html_code_page_help = substr($html_code_page_help, (strpos(mb_strtoupper($html_code_page_help), '<BODY>')) + strlen('<BODY>'));
					$html_code_page_help = substr($html_code_page_help, strpos($html_code_page_help, '>', strpos(mb_strtoupper($html_code_page_help), '<BODY'))+1);
				}
			}


			//si texte brut sans balisage
			if (strpos(' ' . $html_code_page_help, '<')) {
			} else {
				$html_code_page_help = '<pre>' . $html_code_page_help . '</pre>';
			}


			$html_code .= $html_code_page_help;

		if ($format == 'dialog') {
			$html_code .= '</div>';
		}
	}

	return $html_code;
}



function xlColumnValue($strColumnIndex){
	/*
	' ------------------------------------------------------------------------------
	'
	' These function changes the Column Number of a cell in character(s)
	' or vice versa.
	'
	' Return: Column Number or Character
	'
	' TALBI Anis - 04-2009 - msdos@free.fr
	'
	' ------------------------------------------------------------------------------
	*/
	$strColumnIndex = strtoupper($strColumnIndex);
	// Suppression des $ si présent
	if (strpos($strColumnIndex,"\$")>=0){
		$strColumnIndex = ereg_replace("\\$","",$strColumnIndex);
 	}

	switch (ord($strColumnIndex)){
		Case 48: // 0 in first character
			Return FALSE;
			break;
		Case (ord($strColumnIndex)>=49 and ord($strColumnIndex)<=57): // Number to Char
			If ($strColumnIndex<27){
				$xlColumnValue = Chr($strColumnIndex + 65 - 1);
			}Else{
				If ($strColumnIndex % 26 <> 0) {
					$xlColumnValue = Chr($strColumnIndex / 26 + 65 - 1) . Chr($strColumnIndex % 26 + 65 - 1);
				}Else{
					$xlColumnValue = Chr($strColumnIndex / 26 + 65 - 2) . Chr(90);
				}
			}
			return $xlColumnValue;
			break;
		Case (ord($strColumnIndex)>=65 and ord($strColumnIndex)<=90): // Char To Number
			$xlColumnValue = ord($strColumnIndex) - 65 + 1;
			If (strlen($strColumnIndex) > 1){
				$xlColumnValue = ($xlColumnValue * 26) + (ord(substr($strColumnIndex, -1)) - 65 + 1);
			}
			return $xlColumnValue;
			break;
		default:
			return false;
			break;
	}
}



function quote_if_necessary($str) {
	if (substr($str, 0, 1) != '\'' || substr($str, -1, 1) != '\'') {
		return $str = '\'' . $str . '\'';
	} else {
		return $str;
	}
}


function checkInternetConnection($url='www.google.com') {

	if (!$sock = @fsockopen($url, 80, $num, $error, 5)) {
		return FALSE;
	} else {
		return TRUE;
	}
}


function checkID($id) {
	if (isset($id) && is_numeric($id) && $id > 0) {
		return TRUE;
	} else {
		return FALSE;
	}
}


// $calcule la difference de jours entre deux dates
function diff_date($day_from , $month_from, $year_from , $day_to , $month_to , $year_to) {
	$timestamp2 = mktime(0, 0, 0, $month_from, $day_from, $year_from);
	$timestamp = mktime(0, 0, 0, $month_to, $day_to, $year_to);

	$diff = floor(($timestamp - $timestamp2) / (3600 * 24));
	return $diff;
}


function diff_date_without_weekend($day_from , $month_from, $year_from , $day_to , $month_to , $year_to) {
	$array_days = array_date_between_2_dates($day_from , $month_from, $year_from , $day_to , $month_to , $year_to);

	$i=0;

	foreach($array_days as $day) {
		if (date('N', strtotime($day)) == 6 || date('N', strtotime($day)) == 7 ) {

		} else {
			$i++;
		}
	}

	return $i-1;
}

function array_date_between_2_dates($day_from , $month_from, $year_from , $day_to , $month_to , $year_to) {
	$temp = array();

	for($j=0;$j<=diff_date($day_from , $month_from, $year_from , $day_to , $month_to , $year_to);$j++){
		$timestamp = mktime(0, 0, 0, $month_from, $day_from+$j, $year_from);
		$temp[$j] = date("Y-m-d", $timestamp);
	}

	return $temp;
}




function is_date( $str )
{
  $stamp = strtotime( $str );

  if (!is_numeric($stamp))
  {
     return FALSE;
  }
  $month = date( 'm', $stamp );
  $day   = date( 'd', $stamp );
  $year  = date( 'Y', $stamp );

  if (checkdate($month, $day, $year))
  {
     return TRUE;
  }

  return FALSE;
}


function time_hhmmss_to_hhmm($time) {
	return substr($time, 0, strlen($time) - 3);
}


function date_yyyymmdd_to_ddmmyyyy($date) {
	if (is_date($date)) {
		return date('d.m.Y', strtotime($date));
	} else {
		return $date;
	}
}


function lastday($month = '', $year = '') {
	if (empty($month)) {
		$month = date('m');
	}

	if (empty($year)) {
		$year = date('Y');
	}

	$result = strtotime("{$year}-{$month}-01");
	$result = strtotime('-1 second', strtotime('+1 month', $result));

	return date('Y-m-d', $result);
}



function calendrier($m, $a, $array_non_disponibilite=array(), $id='', $categorie='', $data_to_display=array(), $mode='dispo_benevole'){


	//if ($categorie != '' && $data_to_display != '') {
	if ($categorie != '') {

		switch ($categorie) {
			case 'transport':
				$link_module = 'transporteur';
				break;
			case 'appel':
				$link_module = 'transporteur';
				break;
			case 'permanence':
				$link_module = 'permanencier';
				break;
			default:
				$link_module = '';
				break;
		}


		//repere les jours ou le benevole n'est jamais dispo passe sur semaine standard
		$jour_non_dispo = array();

		/*
		foreach ($data_to_display as $row) {
			if ($row['class'] == 'benevole_disponibilite_standard' && $row['nom_categorie'] == $categorie) {
				if ($row['value'] == 0) {
					if (!in_array($row['id_jour_semaine'], $jour_non_dispo)) {
						$jour_non_dispo[] = $row['id_jour_semaine'];
						$count_periode_jour_non_dispo[$row['id_jour_semaine']] = 1;
					} else {
						$count_periode_jour_non_dispo[$row['id_jour_semaine']]++;
					}
				}
			}
		}
		*/

	} else {
		$data_to_display = array();
		$jour_non_dispo = array();
	}

	if ($id == '') {
		$id = 'calendar_non_dispo';
	}

	if ($m > 12) {
		$m -= 12;
		$a++;
	}

	if ($array_non_disponibilite == '') {
		$array_non_disponibilite = array();
	}

	// Tableau pour le noms des mois
	$mois = array();
	$mois[1] = "Janvier";
	$mois[2] = "Février";
	$mois[3] = "Mars";
	$mois[4] = "Avril";
	$mois[5] = "Mai";
	$mois[6] = "Juin";
	$mois[7] = "Juillet";
	$mois[8] = "Août";
	$mois[9] = "Septembre";
	$mois[10] = "Octobre";
	$mois[11] = "Novembre";
	$mois[12] = "Décembre";

	// Tableau pour le noms des jours
	$jours = array();
	$jours[1] = "Lu";
	$jours[2] = "Ma";
	$jours[3] = "Me";
	$jours[4] = "Je";
	$jours[5] = "Ve";
	$jours[6] = "Sa";
	$jours[7] = "Di";

	// Calcul du nombre de jours dans chaque mois en prenant compte des annees bisextiles. les tableaux PHP commencant a 0 et non a 1, le premier mois est un mois "factice"
	if (($a % 4) == 0){
		$nbrjour = array(0, 31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	} else {
		$nbrjour = array(0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	}

	// On cherche grace a cette fonction a quel jour de la semaine correspond le 1er du mois
	if (isset($CAL_FRENCH) == false) {
		$CAL_FRENCH = 0;
	}
	$premierdumois = jddayofweek(cal_to_jd($CAL_FRENCH, $m, 1, $a), 0);
	if($premierdumois == 0){
		$premierdumois = 7;
	}

	//Preparation du tableau avec le nom du mois et la liste des jours de la semaine
	//echo "<table border=1 bordercolor=\"#FFFFFF\"><tr><td class=\"fleches\">"
	$html_code = '<table id="' . $id . '" class="calendrier"><tr><td class="fleches">'
		.mois_precedent($m,$mois[$m],$a)
		.'</td><td class="nom_mois" colspan="5">' . $mois[$m] .' ' . $a .'</td><td class="fleches">'
		.mois_suivant($m,$a)
		.'</td></tr><tr class="noms_jours">'
		.'<td>' . $jours[1] . '</td><td>' . $jours[2] .'</td><td>' . $jours[3] . '</td><td>' . $jours[4] . '</td><td>' . $jours[5] . '</td><td>' . $jours[6] . '</td><td>' . $jours[7] . '</td></tr><tr>';

	$jour=1; //Cette variable est celle qui va afficher les jours de la semaine
	$joursmoisavant = $nbrjour[$m-1] - $premierdumois+2; //Celle-ci sert a afficher les jours du mois precedent qui apparaissent
	$jourmoissuivant = 1; //Et celle-ci les jours du mois suivant

	if($m == 1) {
		$joursmoisavant = $nbrjour[$m+11] - $premierdumois+2; //Si c'est janvier, le mois d'avant n'est pas a 0 mais 31 jours!
	}

	//Et c'est parti pour la boucle for qui va creer l'affichage de notre calendrier !
	for($i=1;$i<40;$i++){
		if($i < $premierdumois){ // Tant que la variable i ne correspond pas au premier jour du mois, on fait des cellules de tableau avec les derniers jours du mois precedent
			//$html_code .= '<td class="cases_vides">' . $joursmoisavant . '</td>';
			$html_code .= '<td class="cases_vides"></td>';
			$joursmoisavant++;
		}else{

			//formatage des cellule
			if ($jour < 10) {
				$jour_txt = "0" . $jour;
			} else {
					$jour_txt = '' . $jour;
			}

			if ($m < 10) {
				$mois_txt = "0" . $m;
			} else {
				$mois_txt = '' . $m;
			}

			if(in_array($a . '-' . $mois_txt . '-' . $jour_txt, $array_non_disponibilite)) {

				if ($mode == 'dispo_benevole') {
					$html_code .= '<td class="non_disponible">';
				} elseif($mode == 'link_date') {
					$html_code .= '<td>';
				} else {
					$html_code .= '<td>';
				}



				if($categorie != '') {
					if ($mode == 'dispo_benevole') {
						$html_code .= '<a class="update_calendar_remove_ajax_get" href="?module=' . $link_module . '&action=delete&sub_module=non_dispo_date_' . $categorie . '&id=' . $data_to_display['id']['value'] . '&date_custom=' . $a . '-' . $mois_txt . '-' . $jour_txt . '">';
							$html_code .= $jour_txt;
						$html_code .= '</a>';
					} elseif($mode == 'link_date') {
						$html_code .= '<a href="#' . $a . '-' . $mois_txt . '-' . $jour_txt . '">';
							$html_code .= $jour_txt;
						$html_code .= '</a>';
					} else {
						$html_code .= $jour_txt;
					}

				} else {
					$html_code .= $jour_txt;
				}


			} elseif (in_array(date('N', mktime(0, 0, 0, $m, $jour, $a)), $jour_non_dispo)) {


					if ($count_periode_jour_non_dispo[date('N', mktime(0, 0, 0, $m, $jour, $a))] == 1) {
						$html_code .= '<td class="non_dispo_jour_semaine_standard_1_period">';
					} elseif ($count_periode_jour_non_dispo[date('N', mktime(0, 0, 0, $m, $jour, $a))] == 2) {
						$html_code .= '<td class="non_dispo_jour_semaine_standard_2_periods">';
					} else {
						$html_code .= '<td class="non_dispo_jour_semaine_standard">';
					}

			} else {
				if($jour == date("d") && $m == date("n")){ //Si la variable $jour correspond a la date d'aujourd'hui, la case est d'une couleur differente
					$html_code .= '<td class="aujourdhui">';
				} else {
					$html_code .= '<td class="jours">';
				}


				if($categorie != '') {
					if ($mode == 'dispo_benevole') {
						$html_code .= '<a class="update_calendar_add_ajax_get" href="?module=' . $link_module . '&action=add&sub_module=non_dispo_date_' . $categorie . '&id=' . $data_to_display['id']['value'] . '&date_custom=' . $a . '-' . $mois_txt . '-' . $jour_txt . '">';
							$html_code .= $jour_txt;
						$html_code .= '</a>';
					} elseif ($mode == 'link_date') {
						$html_code .= $jour_txt;
					} else {
						$html_code .= $jour_txt;
					}

				} else {
					$html_code .= $jour_txt;
				}


			}


			$html_code .= '</td>';
			$jour++; //On passe au lendemain

			/*Si la variable $jour est plus elevee que le nombre de jours du mois, c'est que c'est la fin du mois!
			On remplit les cases vides avec les premiers jours des mois suivants
			Hop on ferme le tableau,
			et on met la variable $i a 41 pour sortir de la boucle */
			if($jour > ($nbrjour[$m])){
				while($i % 7 != 0){
					//$html_code .= '<td class="cases_vides">' . $jourmoissuivant. '</td>';
					$html_code .= '<td class="cases_vides"></td>';
					$i++;
					$jourmoissuivant++;
				}
				$html_code .= '</tr></table>';
				$i=41;
			}
		}

		// Si la variable i correspond a un dimanche (multiple de 7), on passe a la ligne suivante dans le tableau
		if($i % 7 == 0){
			$html_code .= '</tr><tr>';
		}

	}

	return $html_code;

}

	//FONCTION POUR AFFICHER LE MOINS SUIVANT
	function mois_suivant($m,$a){
		$m++; //mois suivant, donc on incremente de 1
		if($m==13){ //si le mois et 13 ca joue pas! cela veut dire qu'il faut augmenter l'annee de 1 et repasser le mois a 1
			$a++;
			$m=1;
		}
		//return '<a href="'.$_SERVER['PHP_SELF']."?m=$m&a=$a\"> &raquo; </a>";
	}

	//FONCTION POUR AFFICHER LE MOINS PRECEDENT
	function mois_precedent($m,$mois,$a){
		$m--;
		if($m==0){
			$a--;
			$m=12;
		}
	//return '<a href="'.$_SERVER['PHP_SELF']."?m=$m&a=$a\"> &laquo; </a>";
	}



function format_tel($str_tel) {

	if (strlen($str_tel) == 13) {
		//numero international
		if (substr($str_tel, 0, 4) != '0041') {
			return $str_tel;
		} else {
			return '0' . substr($str_tel, 4, 2) . ' ' . substr($str_tel, 6, 3) . ' ' . substr($str_tel, 9, 4);
		}
	} elseif (strlen($str_tel) == 10) {
		return substr($str_tel, 0, 3) . ' ' . substr($str_tel, 3, 3) . ' ' . substr($str_tel, 6, 2) . substr($str_tel, 8, 2);
	} elseif (strlen($str_tel) == 7) {
		return substr($str_tel, 0, 3) . ' ' . substr($str_tel, 3, 2) . substr($str_tel, 5, 2);
	} else {
		return $str_tel;
	}

}



function format_titre($str_titre) {
	if (strtoupper($str_titre) == "MADAME") {
		return 'Mme';
	} elseif (strtoupper($str_titre) == "MONSIEUR") {
		return 'M.';
	} elseif (strtoupper($str_titre) == "MADEMOISELLE") {
		return 'Mlle';
	} elseif (strtoupper($str_titre) == "ENFANT") {
		return 'Enfant';
	} else {
		return '';
	}
}


function format_adresse($str_adresse) {
	$str_adresse = str_replace('Chemin', 'chemin', $str_adresse);
	$str_adresse = str_replace('Route', 'route', $str_adresse);

	$str_adresse = str_replace('chemin', 'ch', $str_adresse);
	$str_adresse = str_replace('route', 'rte', $str_adresse);

	return ucfirst($str_adresse);
}


function format_duree($duree) {
	$duree_possibilites = array('30min' => 0.5, '45min' => 0.75, '1h' =>1, '1h 1/2' => 1.5, '1h 3/4' => 1.75, '2h' => 2, '+2h' => 3);

	foreach ($duree_possibilites as $index => $row) {
		if ($row == $duree) {
			return $index;
		}

	}

	return '';
}


function format_type_trajet($base, $duree) {
	if ($base === false || $base == 0) {
		return 'Simple trajet';
	} else {
		if ($duree <= 2) {
			return 'Aller-retour';
		} else {
			return '<strong>Double</strong>';
		}
	}
}


function format_ville($ville) {
	$ville = str_replace('Saint', 'St', $ville);
	$ville = str_replace('saint', 'St', $ville);
	$ville = str_replace('sur-', 's/', $ville);
	$ville = str_replace('Sur-', 's/', $ville);

	return $ville;
}


function unzip($file, $path='', $effacer_zip=false) {
	/*Methode qui permet de decompresser un fichier zip $file dans un repertoire de destination $path
	et qui retourne un tableau contenant la liste des fichiers extraits
	Si $effacer_zip est egal a true, on efface le fichier zip d'origine $file*/

	$tab_liste_fichiers = array(); //Initialisation

	$zip = zip_open($file);

	if ($zip) {

			while ($zip_entry = zip_read($zip)) { //Pour chaque fichier contenu dans le fichier zip

			if (zip_entry_filesize($zip_entry) > 0) {

				$complete_path = $path.dirname(zip_entry_name($zip_entry));

				/*On supprime les eventuels caracteres speciaux et majuscules*/
				$nom_fichier = zip_entry_name($zip_entry);

				/*
				$nom_fichier = stripAccent($nom_fichier;
				$nom_fichier = strtolower($nom_fichier);
				$nom_fichier = ereg_replace('[^a-zA-Z0-9.]','-',$nom_fichier);
				*/

				/*On ajoute le nom du fichier dans le tableau*/
				array_push($tab_liste_fichiers,$nom_fichier);

				$complete_name = $path.$nom_fichier; //Nom et chemin de destination

				if(!file_exists($complete_path)) {

					$tmp = '';

                    foreach(explode('/',$complete_path) AS $k) {

						$tmp .= $k.'/';

						if(!file_exists($tmp)) {
							mkdir($tmp, 0755);
						}
					}
				}

				/*On extrait le fichier*/
				if (zip_entry_open($zip, $zip_entry, "r")) {

					$fd = fopen($complete_name, 'w');

					fwrite($fd, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));

					fclose($fd);
					zip_entry_close($zip_entry);
				}
			}
		}

		zip_close($zip);

		/*On efface eventuellement le fichier zip d'origine*/
		if ($effacer_zip === true)
			unlink($file);
	}

	return $tab_liste_fichiers;
}


function list_file_in_directory($dir) {
	$dir_txt = $dir;

	if ($dir_txt[strlen($dir_txt)-1] != '/') {
		$dir_txt . '/';
	}

	$dir = opendir($dir);
	$array_files = array();

	while($file = readdir($dir)) {
		if($file != '.' && $file != '..' && !is_dir($dirname.$file)) {
			$array_files[] = $dir_txt . $file;
		}
	}

	closedir($dir);

	return $array_files;
}


function GetFileDir($php_self){
	$filename = explode("/", $php_self); // THIS WILL BREAK DOWN THE PATH INTO AN ARRAY

	for( $i = 0; $i < (count($filename) - 1); ++$i ) {
		$filename2 .= $filename[$i].'/';
	}
	return $filename2;
}


function executeSQLInstructionsFromFile($mysql_host, $mysql_username, $mysql_password, $mysql_database, $filename) {
	// Connect to MySQL server
	mysql_connect($mysql_host, $mysql_username, $mysql_password) or die('Error connecting to MySQL server: ' . mysql_error());
	// Select database
	mysql_select_db($mysql_database) or die('Error selecting MySQL database: ' . mysql_error());

	// Temporary variable, used to store current query
	$templine = '';
	// Read in entire file
	$lines = file($filename);

	// Loop through each line
	foreach ($lines as $line)
	{
		// Skip it if it's a comment
		if (substr($line, 0, 2) == '--' || $line == '')
			continue;

		// Add this line to the current segment
		$templine .= $line;
		// If it has a semicolon at the end, it's the end of the query
		if (substr(trim($line), -1, 1) == ';')
		{
			// Perform the query
			mysql_query($templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysql_error() . '<br /><br />');
			// Reset temp variable to empty
			$templine = '';
		}
	}

}

function arrondi($value, $base) {
	if (is_numeric($value) && is_numeric($base) && $base != 0) {
		return (round((1/$base) * $value))/(1/$base);
	} else {
		return FALSE;
	}

}

?>
