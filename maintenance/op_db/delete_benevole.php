<?php

require ('../admin/class.declaration.php');
$load_needed_class_and_interface = load_class_and_interface(array('Lieu', 'Transport', 'Filiale', 'Permanencier', 'Transporteur'));


////////////////////////////////
$id_benevole_filiale = 84 ;
$id_benevole_global = Benevole::get_super_id_benvole_from_id_benevole_filiale($id_benevole_filiale);
///////////////////////////////


global $dbh;


//suppression des transports
$sql = "DELETE FROM transport_transporteur WHERE id_transporteur=" . $id_benevole_filiale;
$status_query_transport = $dbh->exec($sql);

//suppresion des heures effectuees a la permanence
$sql = "DELETE FROM permanence WHERE id_permanencier=" . $id_benevole_filiale;
$status_query_transport = $dbh->exec($sql);


//suppression si contrainte avec des transportes
$sql = "DELETE FROM contrainte_transporteur_beneficiaire WHERE id_transporteur=" . $id_benevole_filiale;
$status_query_repondant = $dbh->exec($sql);


//suppression des date de conge
$sql = "DELETE FROM benevole_non_disponibilite_date WHERE id_benevole=" . $id_benevole_filiale;
$status_query_repondant = $dbh->exec($sql);

//suppression des dispo standards
$sql = "DELETE FROM benevole_disponibilite_standard WHERE id_benevole=" . $id_benevole_filiale;
$status_query_repondant = $dbh->exec($sql);


//suppression des liens avec les filiales
$sql = "DELETE FROM benevole_participation_filiale WHERE id=" . $id_benevole_filiale;
$status_query_repondant = $dbh->exec($sql);


//suppresion des transports deja attribué
/*
$sql = "DELETE FROM transport_transporteur ";
$sql .= " WHERE id_transport IN (";
	$sql .= "SELECT id FROM transport WHERE id_beneficiaire=" . $id_beneficiaire_to_delete;
$sql .= " )";
$status_query_transport_transporteur = $dbh->exec($sql);
*/



//suppression du benevole
$sql = "DELETE FROM benevole WHERE id=" .$id_benevole_global;
$status_query_beneficiaire = $dbh->exec($sql);

echo 'terminé';

?>