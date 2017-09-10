<?php

require ('../../admin/class.declaration.php');
$load_needed_class_and_interface = load_class_and_interface(array('Lieu', 'Transport', 'Filiale', 'Trajet_Pre_Defini'));

$_SESSION['benevole']['id'] = 44;
$_SESSION['filiale']['id'] = 1;

$tmp_transport = new Transport(487);
$tmp_transport->updateDistanceAndCost();


?>