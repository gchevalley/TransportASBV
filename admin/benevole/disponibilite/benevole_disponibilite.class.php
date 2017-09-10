<?php

require_once( str_replace ( '\\', '/', dirname(dirname(__FILE__))) . '/class.declaration.php' );
$load_needed_class_and_interface = load_class_and_interface(array('Benevole', 'Transporteur', 'Jour_Semaine', 'Periode_Journee', 'Benevole_Disponibilite_Categorie', 'Filiale'));

class Benevole_Disponibilite {
	private $id_benevole = 0;
	private $id_categorie = 0;
	private $id_jour_semaine = 0;
	private $id_periode_journee = 0;
	private $custom_heure_debut = '';
	private $custom_heure_fin = '';
	
	static function benvole_disponible($id_categorie, $id_jour_semaine, $id_periode_journee) {
		
	}
}

?>