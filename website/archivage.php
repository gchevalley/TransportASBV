<?php

require_once('../config/connect.db.inc.php');


///////////////////////
$year_limit = 2014 ; //
///////////////////////


global $dbh;
$datesql_limit = $year_limit . "-01-01";

// dans un premier temps, doit supprimer le lien transport <-> chauffeur si il a ete attribue
$sql = "DELETE FROM transport_transporteur WHERE id_transport IN ( SELECT id FROM transport WHERE insert_date < '$datesql_limit')";
$status_query_delete_transport_chauffeur = $dbh->exec($sql);

echo "<p>suppression du lien transport <-> chauffeur pour des transport crees avant $datesql_limit. Nombre d enregistrements affectes $status_query_delete_transport_chauffeur</p>";


//suppression effective des transports
$sql = "DELETE FROM transport WHERE insert_date < '$datesql_limit'";
$status_query_delete_transport = $dbh->exec($sql);

echo "<p>suppression des transports crees avant $datesql_limit. Nombre d enregistrements affectes $status_query_delete_transport</p>";

echo '<p>termine</p>';


?>
