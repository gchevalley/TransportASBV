<?php

require ('../../admin/class.declaration.php');
$load_needed_class_and_interface = load_class_and_interface(array('Lieu', 'Transport', 'Filiale'));


////////////////////////////////
$id_beneficiaire_to_delete = 84 ;
///////////////////////////////


global $dbh;
//suppression si repondant
$sql = "DELETE FROM repondant WHERE id_beneficiaire=" . $id_beneficiaire_to_delete;
$status_query_repondant = $dbh->exec($sql);


//suppression des contraintes beneficiaire
$sql = "DELETE FROM contrainte_transporteur_beneficiaire WHERE id_beneficiaire=" . $id_beneficiaire_to_delete;
$status_query_contrainte = $dbh->exec($sql);

//suppresion des transports deja attribué
$sql = "DELETE FROM transport_transporteur ";
$sql .= " WHERE id_transport IN (";
	$sql .= "SELECT id FROM transport WHERE id_beneficiaire=" . $id_beneficiaire_to_delete;
$sql .= " )";
$status_query_transport_transporteur = $dbh->exec($sql);

//suppression des transports
$sql = "DELETE FROM transport WHERE id_beneficiaire=" . $id_beneficiaire_to_delete;
$status_query_transport = $dbh->exec($sql);

//suppression du beneficiaire
$sql = "DELETE FROM beneficiaire WHERE id=" .$id_beneficiaire_to_delete;
$status_query_beneficiaire = $dbh->exec($sql);

echo 'terminé';


?>