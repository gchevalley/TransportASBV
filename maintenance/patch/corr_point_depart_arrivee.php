<?php

require ('../../admin/class.declaration.php');
$load_needed_class_and_interface = load_class_and_interface(array('Lieu', 'Beneficiaire'));

global $dbh;


$class_a_corriger = 'point_arrivee';


$sql = "SELECT id, " . $class_a_corriger . " FROM transport";
$sth = $dbh->query($sql);

$result = $sth->fetchAll(PDO::FETCH_ASSOC);

$i=0;

foreach($result as $row) {
	$test = unserialize($row[$class_a_corriger]);
	
	if ($test === false) {
		$i++;

		
		$test = unserialize(utf8_decode($row[$class_a_corriger]));
		
		
		//les parties avec des accents ne sont plus lisible mais la class concernee ainsi que l'id sont tout a fait accessible pour reconstruire le tableau
		if ($test['type'] == 'lieu') {
			$tmp_obj = new Lieu($test['id']);
		} elseif ($test['type'] == 'beneficiaire') {
			$tmp_obj = new Beneficiaire($test['id']);
		}
		
		$new_point = ((serialize($tmp_obj->get_adresse())));
		
		$sql = "UPDATE transport SET " . $class_a_corriger . "=" . $dbh->quote($new_point);
		$sql .= " WHERE id=" . $row['id'];
		
		$query_status = $dbh->exec($sql);

	} else {
			
	}
}

echo $i;

?>