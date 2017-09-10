<?php
	$load_needed_class_and_interface = load_class_and_interface(array('Benevole', 'Emergency'));
	$tmp_benevole = new Benevole($_SESSION['benevole']['id']);

	$html_code = '<ul id="toolbar">';
		$html_code .= '<li>';
			
			$html_code .= '<a name="top"></a>';
			
			$html_code .= '<a href="index.php">';
				$html_code .= 'Accueil';
			$html_code .= '</a>';
			
			$html_code .= '<ul>';
				$html_code .= '<li>';
					$html_code .= '<a href="?module=help&amp;action=list_topic">';
						$html_code .= 'Mode d\'emploi';
					$html_code .= '</a>';
				$html_code .= '</li>';
			$html_code .= '</ul>';
		$html_code .= '</li>';
		
		$html_code .= '<li>Transports';
			$html_code .= '<ul>';
				$html_code .= '<li>';
					$html_code .= '<a href="?module=transport&amp;action=add">';
						$html_code .= 'Nouveau';
					$html_code .= '</a>';
				$html_code .= '</li>';
				
				$html_code .= '<li>';
					$html_code .= '<a href="?module=transport&amp;action=list">';
						$html_code .= 'Liste';
					$html_code .= '</a>';
				$html_code .= '</li>';
				
				$html_code .= '<li>';
					$html_code .= '<a href="?module=transport&amp;action=archive">';
						$html_code .= 'Archives';
					$html_code .= '</a>';
				$html_code .= '</li>';
				
			$html_code .= '</ul>';
		$html_code .= '</li>';
		
		$html_code .= '<li>Lieux';
			$html_code .= '<ul>';
				$html_code .= '<li>';
					$html_code .= '<a href="?module=lieu&amp;action=add">';
						$html_code .= 'Nouveau';
					$html_code .= '</a>';
				$html_code .= '</li>';
				
				$html_code .= '<li>';
					$html_code .= '<a href="?module=lieu&amp;action=list">';
						$html_code .= 'Liste';
					$html_code .= '</a>';
				$html_code .= '</li>';
			$html_code .= '</ul>';
		$html_code .= '</li>';


		$html_code .= '<li>Passagers';
			$html_code .= '<ul>';
				$html_code .= '<li>';
					$html_code .= '<a href="?module=beneficiaire&amp;action=add">';
						$html_code .= 'Nouveau';
					$html_code .= '</a>';
				$html_code .= '</li>';
				
				$html_code .= '<li>';
					$html_code .= '<a href="?module=beneficiaire&amp;action=list">';
						$html_code .= 'Liste';
					$html_code .= '</a>';
				$html_code .= '</li>';
				
				$html_code .= '<li>';
					$html_code .= '<a href="?module=beneficiaire&amp;action=tarif">';
						$html_code .= 'Tarifs';
					$html_code .= '</a>';
				$html_code .= '</li>';
			$html_code .= '</ul>';
		$html_code .= '</li>';
	
	
		$html_code .= '<li>Bénévoles';
			$html_code .= '<ul>';
				
				if ($tmp_benevole->checkIsSuperAdmin() || $tmp_benevole->checkIsAdminOfFiliale($_SESSION['filiale']['id'])) {
					$html_code .= '<li>';
						$html_code .= '<a href="?module=benevole&amp;action=add">';
							$html_code .= 'Ajouter';
						$html_code .= '</a>';
					$html_code .= '</li>';
				}
				
				$html_code .= '<li>';
					$html_code .= '<a href="?module=benevole&amp;action=list">';
						$html_code .= 'Liste';
					$html_code .= '</a>';
				$html_code .= '</li>';
				
				/*
				$html_code .= '<li>';
					$html_code .= '<a href="?module=transporteur&amp;action=find_transporteur_dispo">';
						$html_code .= 'Disponibilité';
					$html_code .= '</a>';
				$html_code .= '</li>';
				*/
				
				
				/*
				$html_code .= '<li>';
					$html_code .= '<a href="?module=transporteur&amp;action=city_near">';
						$html_code .= 'Proche d\'une ville';
					$html_code .= '</a>';
				$html_code .= '</li>';
				*/
				
			$html_code .= '</ul>';
		$html_code .= '</li>';
	
	
		$html_code .= '<li>Filiale';
		
			$html_code .= '<ul>';
				
				$html_code .= '<li>';
					$html_code .= '<a href="?module=filiale&amp;action=permanence">';
						$html_code .= 'Permanence';
					$html_code .= '</a>';
				$html_code .= '</li>';
				
				if ($tmp_benevole->checkIsSuperAdmin() || $tmp_benevole->checkIsAdminOfFiliale($_SESSION['filiale']['id'])) {
					$html_code .= '<li>';
						$html_code .= '<a href="?module=filiale&amp;action=facturation&amp;id=' . $_SESSION['filiale']['id'] . '">';
							$html_code .= 'Facturation';
						$html_code .= '</a>';
					$html_code .= '</li>';
				}
				
				
				if ($tmp_benevole->checkIsSuperAdmin() || $tmp_benevole->checkIsAdminOfFiliale($_SESSION['filiale']['id'])) {
					$html_code .= '<li>';
						$html_code .= '<a href="?module=filiale&amp;action=backup">';
							$html_code .= 'Sauvegarde';
						$html_code .= '</a>';
					$html_code .= '</li>';
				}
				
				
				if ($tmp_benevole->checkIsSuperAdmin() || $tmp_benevole->checkIsAdminOfFiliale($_SESSION['filiale']['id'])) {
					$html_code .= '<li>';
						$html_code .= '<a href="?module=filiale&amp;action=restore">';
							$html_code .= 'Restauration';
						$html_code .= '</a>';
					$html_code .= '</li>';
				}
				
				
				if ($tmp_benevole->checkIsSuperAdmin() || $tmp_benevole->checkIsAdminOfFiliale($_SESSION['filiale']['id'])) {
					$html_code .= '<li>';
						$html_code .= '<a href="?module=filiale&amp;action=edit&amp;id=' . $_SESSION['filiale']['id'] . '">';
							$html_code .= 'Editer';
						$html_code .= '</a>';
					$html_code .= '</li>';
				}
				
				if ($tmp_benevole->checkIsSuperAdmin() || $tmp_benevole->checkIsAdminOfFiliale($_SESSION['filiale']['id'])) {
					$html_code .= '<li>';
						$html_code .= '<a href="?module=filiale&amp;action=admin&amp;id=' . $_SESSION['filiale']['id'] . '">';
							$html_code .= 'Admin';
						$html_code .= '</a>';
					$html_code .= '</li>';
				}
			
				
				if ($tmp_benevole->checkIsSuperAdmin() || $tmp_benevole->checkIsAdminOfFiliale($_SESSION['filiale']['id'])) {
					$html_code .= '<li>';
						$html_code .= '<a href="?module=filiale&amp;action=add">';
							$html_code .= 'Ajouter';
						$html_code .= '</a>';
					$html_code .= '</li>';
				}
				
			$html_code .= '</ul>';
		$html_code .= '</li>';
			
		
	$html_code .= '</ul>';
	
	//calendrier du mois en cours
	$html_code .= '<div id="calendar_current_month" class="clear-after mini-calendar">';
		$html_code .= calendrier(date('n'), date('Y'));
	$html_code .= '</div>';
	
	/*
	if (isset($_SESSION['emergency']['levels']) && is_array($_SESSION['emergency']['levels']) && (mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y')) - $_SESSION['emergency']['last_update_time']) < 1200 ) {
		$levels = $_SESSION['emergency']['levels'];
	} else {
		$tmp_emergency = new Emergency();
		$levels = $tmp_emergency->get_levels();
		$_SESSION['emergency']['levels'] = $levels;
		$_SESSION['emergency']['last_update_time'] = mktime();
	}
	
	$html_code .= '<div id="emergency_level" class="clear-after">';
		$html_code .= '<table>';
			$html_code .= '<tr>';
			
				$tag_title = 'Jauge qui indique le charge de travail pour les deux prochains jours en se basant sur le nombre de transports sans chauffeur. Si cet indicateur est rouge, il est recommandé de ne pas accepter d\'urgence de dernière minute';
				$html_code .= '<td title="' . $tag_title . '">';
					$html_code .= '<small>Transports sans chauffeur</small>';
				$html_code .= '</td>';
				
				$html_code .= '<td>';
					$html_code .= '<img title="' . $tag_title . '" src="./img/' . Emergency::get_img_name($levels['level_transports_sans_chauffeur']) . '" />';
				$html_code .= '</td>';
				
				$tag_title = 'Jauge qui renseigne le nombre de transports déjà enregistrés de ces deux prochains jours par rapport à une moyenne journalière';
				$html_code .= '<td title="' . $tag_title . '">';
					$html_code .= '<small> | Nombre de transports</small>';
				$html_code .= '</td>';
				
				$html_code .= '<td>';
					$html_code .= '<img title="' . $tag_title . '" src="./img/' . Emergency::get_img_name($levels['level_transports_avec_chauffeur']) . '" />';
				$html_code .= '</td>';
				
				$tag_title = 'Jauge qui indique le taux de disponibilté des chauffeurs, en prenant en compte les personnes qui ont un transport ou qui sont non disponibles (vacances etc.) pour les deux prochains jours. Il est recommandé de refuser une urgence de dernière minute si l\'indicateur est rouge, car il risque d\'être difficile de trouver un chauffeur';
				$html_code .= '<td title="' . $tag_title . '">';
					$html_code .= '<small> | Disponibilité de chauffeurs</small>';
				$html_code .= '</td>';
				
				$html_code .= '<td>';
					$html_code .= '<img title="' . $tag_title . '" src="./img/' . Emergency::get_img_name($levels['level_chauffeurs_disponibles']) . '" />';
				$html_code .= '</td>';
				
			$html_code .= '</tr>';
		$html_code .= '</table>';
	$html_code .= '</div>';
	*/
	
	echo $html_code;
?>