<?php

require ('../../admin/class.declaration.php');
$load_needed_class_and_interface = load_class_and_interface(array('Beneficiaire', 'Transport', 'Transporteur'));

// format date us yyyy-mm-dd
$date_plafond = '2010-11-30';

global $dbh;

$sql = "SELECT transport_transporteur.*, transport.* ";
$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport ";
$sql .= " WHERE transport.date_transport<=" . $dbh->quote($date_plafond);
$sth = $dbh->query($sql);
$result = $sth->fetchAll(PDO::FETCH_ASSOC);


//cree une entree dans la table d'archive
$bug = array();
$i = 0;
$sql = "INSERT INTO archives_transport (id_transport, id_transporteur, transporteur_nom_complet, id_beneficiaire, beneficiaire_nom_complet, id_filiale, id_categorie, date_transport, heure_debut, duree_approximative, point_depart, point_arrivee, nbre_kilometres, aller_retour, cout_trajet, cout_variable, taux_remboursement_transporteur, info_diverses, is_annule, is_cloture) VALUES ";
foreach ($result as $row) {
	
	//$sql = "INSERT INTO archives_transport (id_transport, id_transporteur, transporteur_nom_complet, id_beneficiaire, beneficiaire_nom_complet, id_filiale, id_categorie, date_transport, heure_debut, duree_approximative, point_depart, point_arrivee, nbre_kilometres, aller_retour, cout_trajet, cout_variable, taux_remboursement_transporteur, info_diverses, is_annule, is_cloture) VALUES ";
	
	$tmp_transporteur = new Transporteur($row['id_transporteur']);
		$tmp_transporteur_nom_complet = $tmp_transporteur->get_nom_complet();
	$tmp_beneficiaire = new Beneficiaire($row['id_beneficiaire']);
		$tmp_beneficiaire_nom_complet = $tmp_beneficiaire->get_nom_complet();
	
	$id_transport = $dbh->quote($row['id_transport']);
	$id_transporteur = $dbh->quote($row['id_transporteur']);
	$transporteur_nom_complet = $dbh->quote(json_encode($tmp_transporteur_nom_complet));
	$id_beneficiaire = $dbh->quote($row['id_beneficiaire']);
	$beneficiaire_nom_complet = $dbh->quote(json_encode($tmp_beneficiaire_nom_complet));
	$id_filiale = $dbh->quote($row['id_filiale']);
	$id_categorie = $dbh->quote($row['id_categorie']);
	$date_transport = $dbh->quote($row['date_transport']);
	$heure_debut = $dbh->quote($row['heure_debut']);
	$duree_approximative = $dbh->quote($row['duree_approximative']);
	$point_depart = $dbh->quote(json_encode(unserialize($row['point_depart'])));
	$point_arrivee = $dbh->quote(json_encode(unserialize($row['point_arrivee'])));
	$nbre_kilometres = $dbh->quote($row['nbre_kilometres']);
	$aller_retour = $dbh->quote($row['aller_retour']);
	$cout_trajet = $dbh->quote($row['cout_trajet']);
	$cout_variable = $dbh->quote($row['cout_variable']);
	$taux_remboursement_transporteur = $dbh->quote($row['taux_remboursement_transporteur']);
	$info_diverses = $dbh->quote($row['info_diverses']);
	$is_annule = $dbh->quote($row['is_annule']);
	$is_cloture = $dbh->quote($row['is_cloture']);
	
	if ($i > 0) {
		$sql .= ", ";
	}
	
	$sql .= "($id_transport, $id_transporteur, $transporteur_nom_complet, $id_beneficiaire, $beneficiaire_nom_complet, $id_filiale, $id_categorie, $date_transport, $heure_debut, $duree_approximative, $point_depart, $point_arrivee, $nbre_kilometres, $aller_retour, $cout_trajet, $cout_variable, $taux_remboursement_transporteur, $info_diverses, $is_annule, $is_cloture)";
	
	//$status_query = $dbh->query($sql);
	
	if ($status_query === false) {
		$test_1 = $sql;
		$test_2 = $row['id_transport'];
		$bug[] = $row['id_transport'];
		echo $row['id_transport'] . '\n';
	}
	
	$i++;
}

//deplacement des données dans la table d'archive
$sql .= ";";
$status_query_move = $dbh->exec($sql);

//suppression des entrées dans les tables principales
$sql = "DELETE FROM transport_transporteur ";
$sql .= " WHERE id_transport in (";
	$sql .= "SELECT id FROM transport WHERE date_transport<=" . $dbh->quote($date_plafond);
$sql .= ")";
$status_query_delete_transport_transporteur = $dbh->exec($sql);


$sql = "DELETE FROM transport ";
$sql .= " WHERE transport.date_transport<=" . $dbh->quote($date_plafond);
$status_query_delete_transport = $dbh->exec($sql);

echo 'terminé';

?>