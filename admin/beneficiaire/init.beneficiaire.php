<?php

require_once( str_replace ( '\\', '/', dirname(dirname(__FILE__))) . '/class.declaration.php' );
$load_needed_class_and_interface = load_class_and_interface(array('Beneficiaire'));


$_SESSION['filiale']['id'] = 1;
$_SESSION['benevole']['id'] = 1;

//construct(id,titre,nom,prenom,adresse,adr_comple,npa,ville,fixe,mobile,toujours_2)


?>