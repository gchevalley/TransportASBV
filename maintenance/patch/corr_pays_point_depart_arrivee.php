<?php

require ('../../admin/class.declaration.php');

global $dbh;


$sql = "SELECT id, point_depart, point_arrivee FROM transport";
$sth = $dbh->query($sql);

$result = $sth->fetchAll(PDO::FETCH_ASSOC);


foreach($result as $row) {
	$point_depart = unserialize($row['point_depart']);
		$point_depart['pays'] = 'Suisse';
	$point_arrivee = unserialize($row['point_arrivee']);
		$point_arrivee['pays'] = 'Suisse';
	
		
		
		
		
		$point_depart = $dbh->quote(serialize($point_depart));
		$point_arrivee = $dbh->quote(serialize($point_arrivee));
		
		$sql = "UPDATE transport SET point_depart=" . $point_depart . ', point_arrivee=' . $point_arrivee;
		$sql .= " WHERE id=" . $row['id'];
		
		$query_status = $dbh->exec($sql);


}


?>