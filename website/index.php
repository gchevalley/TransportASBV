<?php
	/**
	 * l'urgent est fait, 
	 * l'impossible est en cours, 
	 * pour les miracles prévoir un délai 
	 */
	require ('../admin/class.declaration.php');
	require ('./base/auth/secure.php');
?>
<?php 
	if (isset($_GET['reload']) && $_GET['reload'] == 'false' ) {
		/* pour accelerer les requetes ajax, rien n'est retourne au niveau
		 * de la page html, on passe directement au module et class
		 * pour le traitement de la requete
		 * 
		 * Cette astuce est également utilisée dans le cadre de la production
		 * de fichier PDF afin qu'il se charge directement dans la fenêtre
		 * du browser
		 */
		require('./base/base.load_page.php');
	} else {
		require ('./base/base.header_html.php');
		require ('./base/base.header_body.php');
	}
?>