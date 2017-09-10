<?php

require ('../../admin/class.declaration.php');
$load_needed_class_and_interface = load_class_and_interface(array('Lieu', 'Transport'));

global $dbh;

$sql = "SELECT transport.* ";
$sql .= " FROM transport ";
$sql .= " WHERE nbre_kilometres=0 ";
$sql .= " OR cout_trajet=0 ";
$sql .= " OR taux_remboursement_transporteur=0 ";

$sth = $dbh->query($sql);

$result = $sth->fetchAll(PDO::FETCH_ASSOC);

$_SESSION['filiale']['id'] = 1;

if (count($result) > 0) {
	if (checkInternetConnection()) {
		foreach ($result as $row) {
			$tmp_transport = new Transport($row['id']);
			$tmp_transport->updateDistanceAndCost();
			
		}
	}
}


?>