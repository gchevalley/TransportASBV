<?php

require ('../../admin/class.declaration.php');
$load_needed_class_and_interface = load_class_and_interface(array('Lieu', 'Transport', 'Filiale'));


global $dbh;

$class_to_corr = 'lieu'; //lieu, benevole

$sql = "SELECT * ";
$sql .= " FROM " .$class_to_corr;

$sth = $dbh->query($sql);
$result = $sth->fetchAll(PDO::FETCH_ASSOC);

foreach ($result as $row) {
	$tel_fixe = '0041' . substr($row['tel_fixe'], 1);
	//$tel_mobile = '0041' . substr($row['tel_mobile'], 1);
	//$tel_fax = '0041' . substr($row['tel_fax'], 1);
	
	
	$sql = "UPDATE " . $class_to_corr;
		
		$sql = " SET ";
		
		if ($row['tel_fixe'] != '') {
			$sql .= " SET tel_fixe=" . $dbh->quote($tel_fixe);
		}
		
	$sql .= " WHERE id=" . $row['id'];
	
	$query_status = $dbh->exec($sql);
	
}


?>