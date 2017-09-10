<?php

interface Contact {
	public function get_id(); // return int
	public function get_nom_complet(); // return array regroupant si existe prenom+nom
	public function get_adresse(); // return un array avec adresse, (adresse_complement), npa, ville, (pays)
	public function get_telephone(); // return un array avec les differents numeros de telephone
}

?>