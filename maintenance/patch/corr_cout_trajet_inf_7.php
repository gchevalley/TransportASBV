<?php

require ('../../admin/class.declaration.php');
$load_needed_class_and_interface = load_class_and_interface(array('Lieu', 'Transport', 'Filiale'));

$_SESSION['benevole']['id'] = 44;
$_SESSION['filiale']['id'] = 1;

$tmp_filiale = new Filiale(1);
$cout_min = $tmp_filiale->get_standard_prix_forfait_min();

global $dbh;

$sql = "SELECT transport.* ";
$sql .= " FROM transport ";
$sql .= " WHERE cout_trajet<" . $cout_min;

$sth = $dbh->query($sql);

$result = $sth->fetchAll(PDO::FETCH_ASSOC);


if (count($result) > 0) {
	if (checkInternetConnection()) {
		foreach ($result as $row) {
			$tmp_transport = new Transport($row['id']);
			$tmp_transport->updateDistanceAndCost();
			
		}
	}
}


?>