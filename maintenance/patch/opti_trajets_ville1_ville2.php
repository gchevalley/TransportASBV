<?php

require ('../../admin/class.declaration.php');
//$load_needed_class_and_interface = load_class_and_interface(array('Trajet_Pre_Defini'));



global $dbh;

$sql = "SELECT * ";
$sql .= " FROM trajet_pre_defini ";

$sth = $dbh->query($sql);

$result = $sth->fetchAll(PDO::FETCH_ASSOC);

$i = 0;
foreach ($result as $row) {
  if ($row['lieu_1'] > $row['lieu_2']) {
    $sql_update =  "UPDATE trajet_pre_defini SET lieu_1='" . $row['lieu_2'] . "', lieu_2='" . $row['lieu_1'] . "' WHERE id=" . $row['id'];
    //echo "\n" . $i . ": UPDATE trajet_pre_defini SET lieu_1='" . $row['lieu_2'] . "', lieu_2='" . $row['lieu_1'] . "' WHERE id=" . $row['id'];
    $i += 1;
    $sth_update = $dbh->query($sql_update);
  }

}

?>
