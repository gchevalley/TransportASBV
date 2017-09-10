<?php

require ('../../admin/class.declaration.php');
$load_needed_class_and_interface = load_class_and_interface(array('Lieu', 'Transport', 'Filiale', 'Trajet_Pre_Defini'));

$_SESSION['benevole']['id'] = 44;
$_SESSION['filiale']['id'] = 1;


$taux = 0.20;
$trajet_problematique = array();

global $dbh;

$sql = "SELECT * ";
$sql .= " FROM transport ";
$sql .= " WHERE MONTH(date_transport)=12 ";
$sql .= " AND YEAR(date_transport)=2010";
$sql .= " AND is_annule=0";

$sth = $dbh->query($sql);
$result = $sth->fetchAll(PDO::FETCH_ASSOC);

foreach ($result as $row) {
	$depart = unserialize($row['point_depart']);
	$arrivee = unserialize($row['point_arrivee']);
	
	if ($depart['ville'] != $arrivee['ville']) {
		$tmp_trajet_pre_defini = Trajet_Pre_Defini::find_combination($depart['ville'], $arrivee['ville']);
		
		$depart_adresse = $depart['adresse'];
		$depart_ville = $depart['ville'];
		
		$arrivee_adresse = $arrivee['adresse'];
		$arrivee_ville = $arrivee['ville'];
		$nbre_km = $row['nbre_kilometres'];
		$distance_pre_defini = $tmp_trajet_pre_defini['distance'];
		
		if ($tmp_trajet_pre_defini) {
			if ($row['nbre_kilometres'] <((1-$taux)*(2*$tmp_trajet_pre_defini['distance'])) || $row['nbre_kilometres'] >((1+$taux)*(2*$tmp_trajet_pre_defini['distance'])) ) {
				$tmp_beneficiaire = new Beneficiaire($row['id_beneficiaire']);
				$tmp_beneficiaire_nom_complet = $tmp_beneficiaire->get_nom_complet();
				$trajet_problematique[] = array('id' => $row['id'], 'date' => date_yyyymmdd_to_ddmmyyyy($row['date_transport']), 'beneficiaire' => stripAccents($tmp_beneficiaire_nom_complet['nom']), 'depart_adresse' => stripAccents($depart_adresse), 'depart_ville' => stripAccents($depart_ville) , 'arrivee_adresse' => stripAccents($arrivee_adresse), 'arrivee_ville' => stripAccents($arrivee_ville), 'nbre_kilometres' => $row['nbre_kilometres'], 'trajet_pre_defini' => 2*$tmp_trajet_pre_defini['distance']);
			}
		}
	} else {
		
	}
	
}


//converti la matrix export en chaine
$str = '';
foreach ($trajet_problematique as $row) {
	
	foreach($row as $index => $column) {
		if ($index == 'id') {
			$str .= $column;
		} else {
			$str .= ',' . $column;	
		}
	}
	
	//new_line
	$str .= "\n";
}


$file_export_glm = fopen('./export_ecart.csv', 'wb');
fwrite($file_export_glm, $str);


?>