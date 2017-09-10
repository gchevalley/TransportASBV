<?php
	$html_code = '';
	
	if (checkInternetConnection()) {
		$connection_state = 'active';
	} else {
		$connection_state = 'inactive';
	}
	
	$html_code .= '<p>';
		//$html_code .= 'SuperID : ' . $_SESSION['benevole']['id'] . ' - ' . $_SESSION['benevole']['prenom'] . ' ' . $_SESSION['benevole']['nom'] . ' auth : ' . $_SESSION['auth']['date'] . ' à ' . $_SESSION['auth']['time'] . ' @Filiale:' . $_SESSION['filiale']['id'] . ' ' . $_SESSION['filiale']['nom'] . ' avec id_benevole_filiale : ' . $_SESSION['benevole']['id_benevole_filiale'];
		//$html_code .= ' | Internet connection state : ' . $connection_state;
		
		//permanencier ou permanenciere ?
		if (mb_strtoupper(stripAccents($_SESSION['benevole']['nom_complet']['titre'])) == 'MADAME' || mb_strtoupper(stripAccents($_SESSION['benevole']['nom_complet']['titre'])) == 'MADEMOISELLE' ) {
			$permanencier_txt = 'Permanencière';
		} elseif (mb_strtoupper(stripAccents($_SESSION['benevole']['nom_complet']['titre'])) == 'MONSIEUR' ) {
			$permanencier_txt = 'Permanencier';
		}
	
		
		$html_code .= $permanencier_txt . ' : ' . $_SESSION['benevole']['prenom'] . ' ' . mb_strtoupper(stripAccents($_SESSION['benevole']['nom']));
		
		$html_code .= ' | <a href="?module=benevole&amp;action=change_password">Changer le mot de passe</a>';
		
		$html_code .= ' | <a href="./base/auth/logout.php">Déconnexion</a>';
	$html_code .= '</p>';
	
	
	//si de l'info doit etre presentée
	$html_code .= '<div id="header_info">';
		
	$html_code .= '</div>';
	
	//echo utf8_encode($html_code);
	echo ($html_code);
?>