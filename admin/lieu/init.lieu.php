<?php

require_once( str_replace ( '\\', '/', dirname(dirname(__FILE__))) . '/class.declaration.php' );
$load_needed_class_and_interface = load_class_and_interface(array('Lieu', 'Lieu_Categorie'));

$_SESSION['filiale']['id'] = 1;
$_SESSION['benevole']['id'] = 1;

//1	hôpital
//2	ems
//3 médecin
//4	ville

//const(id, nom, categorie, abr,adres, complem, npa, ville, fixe, fax, mobile

//hopitaux / cliniques
$chuv = new Lieu(0, 'CHUV Centre Hospitalier Universitaire Vaudois', 1, 'CHUV','Rue du Bugnon 46', '', '1011', 'Lausanne', '0213141111', '0213145510', '');
$hug = new Lieu(0, 'HUG Hôpitaux Universitaires de Genève', 1, 'HUG','Rue Gabrielle-Perret-Gentil 4 ', '', '1205', 'Genève', '0223723311', '0223476486', '');
$clinique_genolier = new Lieu(0, 'Genolier-Clinique de Genolier', 1, '','Route de Muids 5', '', '1272', 'Genolier', '0223669000', '0223669011', '');
$clinique_montchoisi = new Lieu(0, 'Montchoisi-Clinique de Montchoisi', 1, 'Montchoisi','Chemin des Allinges 10', '', '1006', 'Lausanne', '0216193939', '', '');
$clinique_ligniere = new Lieu(0, 'Lignière-Clinique La Lignière', 1, 'Lignière','', 'La Lignière', '1196', 'Gland', '0229996464', '', '');
$hopital_nyon = new Lieu(0, 'Hôpital de Nyon', 1, '','Chemin de Monastier 10', '', '1260', 'Nyon', '0229946161', '0229946213', '');
$hopital_gilly = new Lieu(0, 'Hôpital de Gilly', 1, '','Les Esserts', '', '1182', 'Gilly', '0218224700', '0218224701', '');
$hopital_morges = new Lieu(0, 'Hôpital de Morges', 1, '','Chemin du Crêt 2', '', '1110', 'Morges', '0218042211 ', '0218042800', '');
$plein_soleil = new Lieu(0, 'Plein-Soleil', 1, '','Chemin Isabelle-de-Montolieu 98', '', '1010', 'Lausanne', '0216512828', '', '');


//EMS
$maison_de_bourgogne = new Lieu(0, 'Maison de Bourgogne', 2, '','Chemin des Pâquerettes 17', '', '1260', 'Nyon', '0229947611', '', '');


?>