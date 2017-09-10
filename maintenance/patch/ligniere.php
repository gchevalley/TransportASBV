<?php

require ('../../admin/class.declaration.php');
$load_needed_class_and_interface = load_class_and_interface(array('Lieu', 'Transport', 'Filiale', 'Trajet_Pre_Defini'));

$_SESSION['benevole']['id'] = 44;
$_SESSION['filiale']['id'] = 1;


global $dbh;

$sql = "SELECT * ";
$sql .= " FROM transport ";

$sth = $dbh->query($sql);
$result = $sth->fetchAll(PDO::FETCH_ASSOC);


foreach ($result as $row) {
	
	$tmp_lieu = new Lieu(34);
	
	
	$arrivee = unserialize($row['point_arrivee']);
	
	if ($arrivee['type'] == 'lieu' && $arrivee['id'] == 34) {
		$point_arrivee = serialize($tmp_lieu->get_adresse());
		
		$sql = "UPDATE transport ";
		$sql .= " SET point_arrivee=" . $dbh->quote($point_arrivee);
		$sql .= " WHERE id=" . $row['id'];
		
		$dbh->exec($sql);
		
		
		$tmp_transport = new Transport($row['id']);
		
		$tmp_transport->updateDistanceAndCost();
		
		
	}
}

?>