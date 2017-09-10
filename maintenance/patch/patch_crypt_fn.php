<?php


require ('../../admin/class.declaration.php');
$load_needed_class_and_interface = load_class_and_interface(array('Benevole'));

global $dbh;


$sql = "SELECT id, nom, prenom FROM benevole";
$sth = $dbh->query($sql);

$result = $sth->fetchAll(PDO::FETCH_ASSOC);

foreach ($result as $row) {
	$nom = substr(mb_strtolower($row['nom']),0,1);
	$prenom = substr(mb_strtolower($row['prenom']),0,1);
	
	$password_non_crypte = $prenom . $nom;
	$password_crypte = md5($password_non_crypte);
	
	
	$sql = "UPDATE benevole SET super_password=" . $dbh->quote($password_crypte);
	$sql .= " WHERE id=" . $row['id'];
	
	$query_status = $dbh->exec($sql);
}


?>