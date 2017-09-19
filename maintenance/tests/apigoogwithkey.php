<?php

require ('../../admin/class.declaration.php');
$load_needed_class_and_interface = load_class_and_interface(array('Trajet_Pre_Defini'));

$point_depart = ['npa' => '1197', 'ville' => 'Prangins', 'pays' => 'Suisse'];
$point_arrivee = ['npa' => '1260', 'ville' => 'Nyon', 'pays' => 'Suisse'];

$nbre_kilometres = Trajet_Pre_Defini::download_distance_from_google_maps('', $point_depart['npa'], $point_depart['ville'], $point_depart['pays'], '', $point_arrivee['npa'], $point_arrivee['ville'], $point_arrivee['pays']);

echo $nbre_kilometres;
