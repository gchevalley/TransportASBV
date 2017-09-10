<?php

if (!isset($module) || !isset($action)) {
		//sinon chargement du dashboard
		require('./processing/dashboard.php');
		
} else {
	//chargement de la page concernee grace au module
	switch ($module) {
		case "beneficiaire":
			require('./processing/beneficiaire/processing.beneficiaire.php');
			break;
		case "benevole":
			require('./processing/benevole/processing.benevole.php');
			break;
		case "transporteur":
			require('./processing/benevole/processing.transporteur.php');
			break;
		case "transport":
			require('./processing/transport/processing.transport.php');
			break;
		case "transport_categorie":
			require('./processing/transport/processing.transport_categorie.php');
			break;
		case "lieu":
			require('./processing/lieu/processing.lieu.php');
			break;
		case "filiale":
			require('./processing/filiale/processing.filiale.php');
			break;
		case "help":
			require('./processing/help/processing.help.php');
			break;
		default:
			require('./processing/dashboard.php');
			break;
	}
}

?>