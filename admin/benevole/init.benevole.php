<?php

require_once( str_replace ( '\\', '/', dirname(dirname(__FILE__))) . '/class.declaration.php' );
$load_needed_class_and_interface = load_class_and_interface(array('Benevole', 'Transporteur', 'Permanencier'));

$_SESSION['filiale']['id'] = 1;
$_SESSION['benevole']['id'] = 1;

$balbinati = new Benevole(0, 'ba', 'Madame', 'Albinati', 'Barbara', 'Grand-Rue 48', '', '1196', 'Gland', '0229950020', '', '0794492349', '', '', '', 0);
$balbinati->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 1, 0, 0, 0);
	$balbinati_transporteur = new Transporteur($balbinati->get_id(), 1);
	for ($i=1; $i<=3; $i++) {
		$balbinati_transporteur->supprimerDisponibiliteStandard(1, 3, $i, 1);
		
	}
	
	for ($i=1; $i<=7; $i++) {
		$balbinati_transporteur->supprimerDisponibiliteStandard(1, $i, 3, 1);
	}


$cbadan = new Benevole(0, 'cb', 'Madame', 'Badan', 'Christiane', 'Chemin des Frutières 7', '', '1276', 'Gingins', '0223692185', '', '0796143604', '', '', '', 0);
$cbadan->ajouterParticipationDansFiliale(1, 1, 1, 1, 0, 0, 0, 0, 0);
	$cbadan_transporteur = new Transporteur($cbadan->get_id(), 1);
	$cbadan_permanencier = new Permanencier($cbadan->get_id(), 1);
	
	//pas atteignable lu/ma/ve l'apres-midi + soir
	for ($i=2; $i<=3; $i++) {
		$cbadan_transporteur->supprimerDisponibiliteStandard(3, 1, $i, 1);
		$cbadan_transporteur->supprimerDisponibiliteStandard(3, 2, $i, 1);
		$cbadan_transporteur->supprimerDisponibiliteStandard(3, 3, $i, 1);
	}
	
	//pas de transport le mardi
	for ($i=1; $i<=3; $i++) {
		$cbadan_transporteur->supprimerDisponibiliteStandard(1, 2, $i, 1);
	}
	
	//pas le mercredi matin
	$cbadan_transporteur->supprimerDisponibiliteStandard(1, 3, 1, 1);
	
	// pas le vendredi apres midi
	$cbadan_transporteur->supprimerDisponibiliteStandard(1, 5, 2, 1);
	
	
$jbalik = new Benevole(0, 'jb', 'Monsieur', 'Balik', 'Jean-Claude', 'Chemin des Plantaz 47', '', '1260', 'Nyon', '', '', '0774100072', '', '', '', 0);
$jbalik->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 1, 0, 0, 0);
	$jbalik_transporteur = new Transporteur($jbalik->get_id(), 1);
	//pas le mercredi
	for ($i=1; $i<=3; $i++) {
		$jbalik_transporteur->supprimerDisponibiliteStandard(1, 3, $i, 1);
	}

$abarrillier = new Benevole(0, 'ab', 'Madame', 'Barrillier', 'Antoinette', 'Route de Divonne 3', '', '1260', 'Nyon', '0223610459', '', '0774020158', '', '', '', 0);
$abarrillier->ajouterParticipationDansFiliale(1, 0, 1, 1, 0, 0, 0, 0, 0);
	$abarrillier_transporteur = new Transporteur($abarrillier->get_id(), 1);
	//pas le jeudi
	for ($i=1; $i<=3; $i++) {
		$abarrillier_transporteur->supprimerDisponibiliteStandard(1, 4, $i, 1);
	}


$fbohner = new Benevole(0, 'fb', 'Madame', 'Bohner', 'Francine', 'Route de Clémenty 43', '', '1260', 'Nyon', '0223615601', '', '', '', '', '', 0);
$fbohner->ajouterParticipationDansFiliale(1, 1, 1, 1, 0, 1, 0, 0, 0);
	$fbohner_transporteur = new Transporteur($fbohner->get_id(), 1);
	//pas le jeudi
	for ($i=1; $i<=3; $i++) {
		$fbohner_transporteur->supprimerDisponibiliteStandard(1, 4, $i, 1);
	}

$mbourezgue = new Benevole(0, 'mb', 'Monsieur', 'Bourezgue', 'Mohamed', 'Chemin d\'Arpey 12', '', '1276', 'Gingins', '0223690459', '', '0787585230', '', '', '', 0);
$mbourezgue->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 1, 0, 0, 0);
	$mbourezgue_transporteur = new Transporteur($mbourezgue->get_id(), 1);
	//pas le jeudi
	for ($i=1; $i<=3; $i++) {
		for ($j=1; $j<=7; $j++) {
			$mbourezgue_transporteur->supprimerDisponibiliteStandard(1, $j, $i, 1);
			$mbourezgue_transporteur->supprimerDisponibiliteStandard(3, $j, $i, 1);
		}
	}

$mbrocher = new Benevole(0, 'mb', 'Monsieur', 'Brocher', 'Michel', 'Route de Signy 4', '', '1274', 'Grens', '0223614378', '', '', '', '', '', 0);
$mbrocher->ajouterParticipationDansFiliale(1, 0, 1, 1, 0, 0, 0, 0, 0);


$jbudry = new Benevole(0, 'jb', 'Monsieur', 'Budry', 'Jean-Pierre', 'Chemin des Baules 36', '', '1268', 'Begnins', '0223662148', '', '', '', '', '', 0);
$jbudry->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 1, 0, 0, 0);
	$jbudry_transporteur = new Transporteur($jbudry->get_id(), 1);
	//pas le lundi & mercredi apres midi
	$jbudry_transporteur->supprimerDisponibiliteStandard(1, 1, 2, 1);
	$jbudry_transporteur->supprimerDisponibiliteStandard(3, 1, 2, 1);
	$jbudry_transporteur->supprimerDisponibiliteStandard(1, 3, 2, 1);
	$jbudry_transporteur->supprimerDisponibiliteStandard(3, 3, 2, 1);


$bburki = new Benevole(0, 'bb', 'Monsieur', 'Burki', 'Bernard', 'Chemin du Molard 5', '', '1266', 'Duillier', '0223612119', '', '', '', '', '', 0);
$bburki->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 1, 0, 0, 0);
	$bburki_transporteur = new Transporteur($bburki->get_id(), 1);
	//pas le jeudi
	for ($i=1; $i<=3; $i++) {
		$bburki_transporteur->supprimerDisponibiliteStandard(1, 4, $i, 1);
	}

	
$dburdevet = new Benevole(0, 'db', 'Monsieur', 'Burdevet', 'Didier', 'Route de Bogis 19', '', '1279', 'Chavannes-de-Bogis', '', '', '0786343182', '', '', '', 0);
$dburdevet->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 1, 0, 0, 0);
	$dburdevet_transporteur = new Transporteur($dburdevet->get_id(), 1);
	//pas le vendredi
	for ($i=1; $i<=3; $i++) {
		$dburdevet_transporteur->supprimerDisponibiliteStandard(1, 5, $i, 1);
	}

//(fili, perma, trans, loc, ge, la, holi, admini_filiale, external_login)
$mcerutti = new Benevole(0, 'mc', 'Madame', 'Cerutti', 'Michèle', 'Chemin de Recredoz 12', '', '1278', 'La Rippe', '0223640712', '', '0796307035', '', '', '', 0);
$mcerutti->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 1, 0, 0, 0);
	$mcerutti_transporteur = new Transporteur($mcerutti->get_id(), 1);
	$array_date = array_date_between_2_dates(1 , 9, 2010 , 31 , 10 , 2010);
	foreach ($array_date as $tmp_date) {
		$mcerutti_transporteur->ajouterNonDisponibiliteDate($tmp_date, 1, 1);
	}

$icharton = new Benevole(0, 'ic', 'Madame', 'Charton', 'Isabelle', 'Rue du Midi 9', '', '1196', 'Gland', '0223627885', '', '0792902794', '', '', '', 0);
$icharton->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 0, 0, 0, 0);
	$icharton_transporteur = new Transporteur($icharton->get_id(), 1);
	
	for ($j=1; $j<=7; $j++) {
		$icharton_transporteur->supprimerDisponibiliteStandard(1, $j, 3, 1);
		$icharton_transporteur->supprimerDisponibiliteStandard(3, $j, 3, 1);
	}
	
	
	for ($i=2; $i<=3; $i++) {
		$icharton_transporteur->supprimerDisponibiliteStandard(1, 1, $i, 1);
		$icharton_transporteur->supprimerDisponibiliteStandard(1, 2, $i, 1);
		$icharton_transporteur->supprimerDisponibiliteStandard(1, 4, $i, 1);
	}

	
$jchablox = new Benevole(0, 'jc', 'Monsieur', 'Chabloz', 'Jacques', 'Chemin d\'Arpey 12', '', '1276', 'Gingins', '0223690459', '', '0793656310', '', '', '', 0);
$jchablox->ajouterParticipationDansFiliale(1, 1, 1, 1, 1, 1, 0, 0, 0);

$mcherpillod = new Benevole(0, 'mc', 'Madame', 'Cherpillod', 'Monique', 'Rue du Borgeaud 39', '', '1196', 'Gland', '0223642708', '', '0797824158', '', '', '', 0);
$mcherpillod->ajouterParticipationDansFiliale(1, 0, 1, 1, 0, 1, 0, 0, 0);
	$mcherpillod_transporteur = new Transporteur($mcherpillod->get_id(), 1);
	$mcherpillod_transporteur->supprimerDisponibiliteStandard(1, 5, 2, 1);
	$mcherpillod_transporteur->supprimerDisponibiliteStandard(1, 5, 3, 1);
	
	
	
$jclark = new Benevole(0, 'jc', 'Madame', 'Clark', 'Janet', 'En Bursinel 6', '', '1277', 'Borex', '0223671009', '', '0796442129', '', '', '', 0);
$jclark->ajouterParticipationDansFiliale(1, 0, 1, 1, 0, 0, 0, 0, 0);
	$jclark_transporteur = new Transporteur($jclark->get_id(), 1);
	$jclark_transporteur->supprimerDisponibiliteStandard(1, 1, 1, 1);
	$jclark_transporteur->supprimerDisponibiliteStandard(1, 3, 1, 1);

	
$hclerc = new Benevole(0, 'hc', 'Madame', 'Clerc', 'Heidi', 'Vy de l\'Etraz 22', '', '1276', 'Gingins', '0223691733', '', '', '', '', '', 0);
$hclerc->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 1, 0, 0, 0);


$mcretegny = new Benevole(0, 'mc', 'Madame', 'Cretegny', 'Micheline', 'Chemin de la Dôle 13', '', '1261', 'Le Vaud', '0223662632', '', '0794129444', '', '', '', 0);
$mcretegny->ajouterParticipationDansFiliale(1, 0, 1, 1, 0, 0, 0, 0, 0);
	$mcretegny_transporteur = new Transporteur($mcretegny->get_id(), 1);
	$mcretegny_transporteur->supprimerDisponibiliteStandard(1, 2, 2, 1);
	

//commibus - mardi apres-midi
$odiserens = new Benevole(0, 'od', 'Monsieur', 'Diserens', 'Olivier', 'Chemin du Couchant 14', '', '1260', 'Nyon', '', '', '0798254737', '', '', '', 0);
$odiserens->ajouterParticipationDansFiliale(1, 0, 1, 1, 0, 0, 0, 0, 0);
	$odiserens_transporteur = new Transporteur($odiserens->get_id(), 1);
	for ($j=1; $j<=1; $j++) {
		$odiserens_transporteur->supprimerDisponibiliteStandard(1, $j, 1, 1);
		$odiserens_transporteur->supprimerDisponibiliteStandard(1, $j, 2, 1);
		$odiserens_transporteur->supprimerDisponibiliteStandard(1, $j, 3, 1);
	}
	
	for ($j=2; $j<=2; $j++) {
		$odiserens_transporteur->supprimerDisponibiliteStandard(1, $j, 1, 1);
		$odiserens_transporteur->supprimerDisponibiliteStandard(1, $j, 3, 1);
	}
	
	for ($j=3; $j<=7; $j++) {
		$odiserens_transporteur->supprimerDisponibiliteStandard(1, $j, 1, 1);
		$odiserens_transporteur->supprimerDisponibiliteStandard(1, $j, 2, 1);
		$odiserens_transporteur->supprimerDisponibiliteStandard(1, $j, 3, 1);
	}


$jduc = new Benevole(0, 'jd', 'Monsieur', 'Duc', 'Jacques', 'Chemin des Rosiers 10', '', '1260', 'Nyon', '0223616205', '', '0797364783', '', '', '', 0);
$jduc->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 1, 0, 0, 0);
	$jduc_transporteur = new Transporteur($jduc->get_id(), 1);
	
	for ($i=1; $i<=3; $i++) {
		// pas le lundi & le mardi
		$jduc_transporteur->supprimerDisponibiliteStandard(1, 1, $i, 1);
		$jduc_transporteur->supprimerDisponibiliteStandard(1, 2, $i, 1);
	}
	
	
$rduc = new Benevole(0, 'rd', 'Madame', 'Duc', 'Rosmarie', 'Chemin des Rosiers 10', '', '1260', 'Nyon', '0223616205', '', '0792768825', '', '', '', 0);
$rduc->ajouterParticipationDansFiliale(1, 0, 1, 1, 0, 0, 0, 0, 0);
	$rduc_transporteur = new Transporteur($rduc->get_id(), 1);
	
	for ($i=1; $i<=3; $i++) {
		// pas le lundi & le mardi
		$rduc_transporteur->supprimerDisponibiliteStandard(1, 1, $i, 1);
		$rduc_transporteur->supprimerDisponibiliteStandard(1, 2, $i, 1);
	}

	
$idyens = new Benevole(0, 'id', 'Madame', 'Dyens', 'Isabelle', 'Route de Duillier 34', '', '1272', 'Genolier', '0223661105', '', '0792707254', '', '', '', 0);
$idyens->ajouterParticipationDansFiliale(1, 1, 1, 1, 0, 0, 0, 0, 0);
	$idyens_transporteur = new Transporteur($idyens->get_id(), 1);

	$idyens_transporteur->supprimerDisponibiliteStandard(1, 4, 1, 1); //jeudi matin
	$idyens_transporteur->supprimerDisponibiliteStandard(1, 3, 2, 1); //mercredi apres-midi
	$idyens_transporteur->supprimerDisponibiliteStandard(1, 5, 2, 1); //vendredi apres-midi
	
	$array_date = array_date_between_2_dates(14 , 10, 2010 , 27 , 10 , 2010);
	foreach ($array_date as $tmp_date) {
		$idyens_transporteur->ajouterNonDisponibiliteDate($tmp_date, 1, 1);
	}

	
$nfavre = new Benevole(0, 'nf', 'Madame', 'Favre', 'Nathalie', 'Les Landes 34B', '', '1299', 'Crans-près-Céligny', '0223627607', '', '0786321842', '', '', '', 0);
$nfavre->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 0, 0, 0, 0);
	$nfavre_transporteur = new Transporteur($nfavre->get_id(), 1);
	
	for ($j=1; $j<=5; $j++) {
		$nfavre_transporteur->supprimerDisponibiliteStandard(1, $j, 1, 1);
		$nfavre_transporteur->supprimerDisponibiliteStandard(1, $j, 2, 1);
	}

	
$mfritschy = new Benevole(0, 'mf', 'Madame', 'Fritschy', 'Marguerite', 'Chemin Très-chez-Roget', '', '1272', 'Genolier', '0223662436', '', '', '', '', '', 0);
$mfritschy->ajouterParticipationDansFiliale(1, 0, 1, 1, 0, 0, 0, 0, 0);
	$mfritschy_transporteur = new Transporteur($mfritschy->get_id(), 1);
	
	$mfritschy_transporteur->supprimerDisponibiliteStandard(1, 1, 1, 1); //lundi matin
	$mfritschy_transporteur->supprimerDisponibiliteStandard(1, 4, 1, 1); //jeudi matin
	$mfritschy_transporteur->supprimerDisponibiliteStandard(1, 5, 2, 1); //vendredi apres-midi


$egarcia = new Benevole(0, 'eg', 'Monsieur', 'Garcia', 'Eric', 'Rue du Battoir 7', '', '1269', 'Bassins', '0223664907', '', '0798821041', '', '', '', 0);
$egarcia->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 1, 0, 0, 0);
	$egarcia_transporteur = new Transporteur($egarcia->get_id(), 1);


$cgigandet = new Benevole(0, 'cg', 'Monsieur', 'Gigandet', 'Claude', 'Route du Curson 16', '', '1197', 'Prangins', '0223624913', '', '0796502111', '', '', '', 0);
$cgigandet->ajouterParticipationDansFiliale(1, 1, 1, 1, 1, 1, 0, 0, 0);
	$cgigandet_transporteur = new Transporteur($cgigandet->get_id(), 1);
	
	$cgigandet_transporteur->supprimerDisponibiliteStandard(1, 2, 1, 1);
	$cgigandet_transporteur->supprimerDisponibiliteStandard(1, 2, 2, 1);
	


$rgiller = new Benevole(0, 'rg', 'Madame', 'Giller', 'Rose-Marie', 'Chemin Bonmont 5', '', '1267', 'Vich', '0223642413', '', '0795131014', '', '', '', 0);
$rgiller->ajouterParticipationDansFiliale(1, 1, 1, 1, 1, 0, 0, 0, 0);
	$rgiller_transporteur = new Transporteur($rgiller->get_id(), 1);

	$rgiller_transporteur->supprimerDisponibiliteStandard(1, 1, 1, 1);
	
	$array_date = array_date_between_2_dates(2 , 10, 2010 , 13 , 10 , 2010);
	foreach ($array_date as $tmp_date) {
		$rgiller_transporteur->ajouterNonDisponibiliteDate($tmp_date, 1, 1);
	}


$ggretillat = new Benevole(0, 'gg', 'Monsieur', 'Grétillat', 'Gilbert', 'Malagny 4', '', '1196', 'Gland', '0223640079', '', '0774474788', '', '', '', 0);
$ggretillat->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 1, 0, 0, 0);
	$ggretillat_transporteur = new Transporteur($ggretillat->get_id(), 1);
	
	for ($j=1; $j<=5; $j++) {
		$ggretillat_transporteur->supprimerDisponibiliteStandard(1, $j, 1, 1);
		$ggretillat_transporteur->supprimerDisponibiliteStandard(1, $j, 2, 1);
	}
	
	
$fguignard = new Benevole(0, 'fg', 'Monsieur', 'Guignard', 'Fredy', 'Chemin de la Redoute 26', '', '1260', 'Nyon', '0225667750', '', '0765394215', '', '', '', 0);
$fguignard->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 1, 0, 0, 0);
	$fguignard_transporteur = new Transporteur($fguignard->get_id(), 1);


$rguignard = new Benevole(0, 'rg', 'Madame', 'Guignard', 'Rita', 'Chemin de la Redoute 26', '', '1260', 'Nyon', '0225667750', '', '', '', '', '', 0);
$rguignard->ajouterParticipationDansFiliale(1, 0, 1, 1, 0, 0, 0, 0, 0);
	$rguignard_transporteur = new Transporteur($rguignard->get_id(), 1);

	
$jhildbrand = new Benevole(0, 'jh', 'Monsieur', 'Hildbrand', 'Jean-Marc', 'Chemin des Vignes 15', '', '1291', 'Commugny', '0227769041', '', '0796294510', '', '', '', 0);
$jhildbrand->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 1, 0, 0, 0);
	$jhildbrand_transporteur = new Transporteur($jhildbrand->get_id(), 1);
	
	$jhildbrand_transporteur->supprimerDisponibiliteStandard(1, 2, 3, 1);
	$jhildbrand_transporteur->supprimerDisponibiliteStandard(1, 3, 3, 1);
	

$jhugi = new Benevole(0, 'jh', 'Madame', 'Hugi', 'Janine', 'Chemin du Levrioux 17', '', '1263', 'Crassier', '0223671605', '', '0793836569', '', '', '', 0);
$jhugi->ajouterParticipationDansFiliale(1, 1, 1, 1, 1, 0, 0, 0, 0);
	$jhugi_transporteur = new Transporteur($jhugi->get_id(), 1);
	
	$array_date = array_date_between_2_dates(11 , 10, 2010 , 16 , 10 , 2010);
	foreach ($array_date as $tmp_date) {
		$jhugi_transporteur->ajouterNonDisponibiliteDate($tmp_date, 1, 1);
	}


$ejaques = new Benevole(0, 'ej', 'Monsieur', 'Jaques', 'Eric', 'Mafroi 6', '', '1260', 'Nyon', '0223614617', '', '', '', '', '', 0);
$ejaques->ajouterParticipationDansFiliale(1, 0, 1, 1, 0, 0, 0, 0, 0);
	$ejaques_transporteur = new Transporteur($ejaques->get_id(), 1);

	$ejaques_transporteur->supprimerDisponibiliteStandard(1, 5, 2, 1);


$ejeangros = new Benevole(0, 'ej', 'Monsieur', 'Jeangros', 'Erhard', 'Chemin Valmont 352', '', '1260', 'Nyon', '0223618642', '', '', '', '', '', 0);
$ejeangros->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 0, 0, 0, 0);
	$ejeangros_transporteur = new Transporteur($ejeangros->get_id(), 1);
	
	$ejeangros_transporteur->supprimerDisponibiliteStandard(1, 2, 2, 1);
	$ejeangros_transporteur->supprimerDisponibiliteStandard(1, 4, 2, 1);
	

$jjent = new Benevole(0, 'jj', 'Monsieur', 'Jent', 'Jürg', 'Chemin Valmont 240', '', '1260', 'Nyon', '0223614676', '', '', '', '', '', 0);
$jjent->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 1, 0, 0, 0);
	$jjent_transporteur = new Transporteur($jjent->get_id(), 1);


$rjohnson = new Benevole(0, 'rj', 'Monsieur', 'Johnson', 'Robert', 'Chemin des Mélèzes 29', '', '1197', 'Prangins', '0223617601', '', '0797673017', '', '', '', 0);
$rjohnson->ajouterParticipationDansFiliale(1, 1, 1, 1, 1, 1, 1, 1, 0);
	$rjohnson_transporteur = new Transporteur($rjohnson->get_id(), 1);
	
	$array_date = array_date_between_2_dates(16 , 09, 2010 , 12 , 10 , 2010);
	foreach ($array_date as $tmp_date) {
		$rjohnson_transporteur->ajouterNonDisponibiliteDate($tmp_date, 1, 1);
	}


$tkermode = new Benevole(0, 'kt', 'Madame', 'Kermode', 'Trudi', 'Chemin des Vignettes 7', '', '1299', 'Crans-près-Céligny', '0229600288', '', '0787080288', '', '', '', 0);
$tkermode->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 1, 0, 0, 0);
	$tkermode_transporteur = new Transporteur($tkermode->get_id(), 1);
	
	for ($i=1; $i<=3; $i++) {
		// pas le mardi
		$tkermode_transporteur->supprimerDisponibiliteStandard(1, 2, $i, 1);
	}
	
	
	$array_date = array_date_between_2_dates(18 , 10, 2010 , 8 , 11 , 2010);
	foreach ($array_date as $tmp_date) {
		$tkermode_transporteur->ajouterNonDisponibiliteDate($tmp_date, 1, 1);
	}
	

$hlasser = new Benevole(0, 'hl', 'Madame', 'Lasser', 'Hélène', 'En Cotty', '', '1275', 'Chéserex', '0223692368', '', '0796267250', '', '', '', 0);
$hlasser->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 1, 0, 0, 0);
	$hlasser_transporteur = new Transporteur($hlasser->get_id(), 1);
	
	$hlasser_transporteur->supprimerDisponibiliteStandard(1, 1, 2, 1);


$jmadsen = new Benevole(0, 'jm', 'Monsieur', 'Madsen', 'Jan', 'Chemin de la Foge 10', '', '1291', 'Commugny', '0227762636', '', '0795457665', '', '', '', 0);
$jmadsen->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 1, 0, 0, 0);
	$jmadsen_transporteur = new Transporteur($jmadsen->get_id(), 1);

	for ($i=1; $i<=3; $i++) {
		// pas le mardi
		$jmadsen_transporteur->supprimerDisponibiliteStandard(1, 2, $i, 1);
	}


$hmeier = new Benevole(0, 'hm', 'Monsieur', 'Meier', 'Hans-Ruedi', 'Chemin de Bonmont 5', '', '1260', 'Nyon', '0223612339', '', '', '', '', '', 0);
$hmeier->ajouterParticipationDansFiliale(1, 1, 1, 1, 1, 1, 1, 1, 0);
	$hmeier_transporteur = new Transporteur($hmeier->get_id(), 1);


$ameinikoff = new Benevole(0, 'am', 'Madame', 'Meinikoff', 'Almut', 'Chemin des Vignettes 24', '', '1299', 'Crans-près-Céligny', '0227763407', '', '0793156358', '', '', '', 0);
$ameinikoff->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 1, 0, 0, 0);
	$ameinikoff_transporteur = new Transporteur($ameinikoff->get_id(), 1);


$mmoor = new Benevole(0, 'mm', 'Madame', 'Moor', 'Marinette', 'Rue du Midi 1', '', '1196', 'Gland', '0223641077', '', '0774195403', '', '', '', 0);
$mmoor->ajouterParticipationDansFiliale(1, 0, 1, 1, 0, 0, 0, 0, 0);
	$mmoor_transporteur = new Transporteur($mmoor->get_id(), 1);
	
	$mmoor_transporteur->supprimerDisponibiliteStandard(1, 3, 2, 1);
	$mmoor_transporteur->supprimerDisponibiliteStandard(1, 3, 3, 1);
	
	for ($i=1; $i<=3; $i++) {
		$mmoor_transporteur->supprimerDisponibiliteStandard(1, 4, $i, 1);
		$mmoor_transporteur->supprimerDisponibiliteStandard(1, 5, $i, 1);
		$mmoor_transporteur->supprimerDisponibiliteStandard(1, 6, $i, 1);
		$mmoor_transporteur->supprimerDisponibiliteStandard(1, 7, $i, 1);
	}


$aobeida = new Benevole(0, 'ao', 'Madame', 'Obeida', 'Anne-Catherine', 'La Levratte 14', '', '1260', 'Nyon', '0223612814', '', '', '', '', '', 0);
$aobeida->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 1, 0, 0, 0);
	$aobeida_transporteur = new Transporteur($aobeida->get_id(), 1);
	$aobeida_transporteur->supprimerDisponibiliteStandard(1, 2, 1, 1);
	$aobeida_transporteur->supprimerDisponibiliteStandard(1, 4, 1, 1);


$cpiaget = new Benevole(0, 'cp', 'Monsieur', 'Piaget', 'Christian', 'Au Village', '', '1268', 'Burtigny', '0223664283', '', '0794584768', '', '', '', 0);
$cpiaget->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 1, 0, 0, 0);
	$cpiaget_transporteur = new Transporteur($cpiaget->get_id(), 1);


$rsaint = new Benevole(0, 'rs', 'Monsieur', 'Saint', 'Ronald', 'Chemin du Léman 8', '', '1260', 'Nyon', '0229902051', '', '0797776757', '', '', '', 0);
$rsaint->ajouterParticipationDansFiliale(1, 1, 0, 0, 0, 0, 0, 1, 0);


$bsiebenthal = new Benevole(0, 'bs', 'Monsieur', 'Siebenthal', 'Bernard', 'Route de St-Cergue 22A', '', '1260', 'Nyon', '0223621939', '', '', '', '', '', 0);
$bsiebenthal->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 1, 0, 0, 0);
	$bsiebenthal_transporteur = new Transporteur($bsiebenthal->get_id(), 1);

	for ($i=1; $i<=3; $i++) {
		$bsiebenthal_transporteur->supprimerDisponibiliteStandard(1, 1, $i, 1);
		$bsiebenthal_transporteur->supprimerDisponibiliteStandard(1, 5, $i, 1);
		$bsiebenthal_transporteur->supprimerDisponibiliteStandard(1, 6, $i, 1);
		$bsiebenthal_transporteur->supprimerDisponibiliteStandard(1, 7, $i, 1);
		
	}


$aschwaar = new Benevole(0, 'as', 'Madame', 'Schwaar', 'Anne', 'Chemin des Sports', '', '1268', 'Begnins', '0223663709', '', '0795906226', '', '', '', 0);
$aschwaar->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 1, 0, 0, 0);
	$aschwaar_transporteur = new Transporteur($aschwaar->get_id(), 1);
	
	for ($i=1; $i<=3; $i++) {
		$aschwaar_transporteur->supprimerDisponibiliteStandard(1, 1, $i, 1);
		$aschwaar_transporteur->supprimerDisponibiliteStandard(1, 3, $i, 1);
		$aschwaar_transporteur->supprimerDisponibiliteStandard(1, 5, $i, 1);
		$aschwaar_transporteur->supprimerDisponibiliteStandard(1, 6, $i, 1);
		$aschwaar_transporteur->supprimerDisponibiliteStandard(1, 7, $i, 1);
	}
	
	$array_date = array_date_between_2_dates(1 , 10, 2010 , 31 , 10 , 2010);
	foreach ($array_date as $tmp_date) {
		$aschwaar_transporteur->ajouterNonDisponibiliteDate($tmp_date, 1, 1);
	}


$hschwegler = new Benevole(0, 'hs', 'Monsieur', 'Schwegler', 'Heinrich', 'Chemin des Morettes 10', '', '1197', 'Prangins', '0223615337', '', '', '', '', '', 0);
$hschwegler->ajouterParticipationDansFiliale(1, 1, 1, 1, 0, 0, 0, 0, 0);
	$hschwegler_transporteur = new Transporteur($hschwegler->get_id(), 1);

	for ($i=1; $i<=3; $i++) {
		$hschwegler_transporteur->supprimerDisponibiliteStandard(1, 3, $i, 1);
		$hschwegler_transporteur->supprimerDisponibiliteStandard(1, 4, $i, 1);
		
		$hschwegler_transporteur->supprimerDisponibiliteStandard(2, 3, $i, 1);
		$hschwegler_transporteur->supprimerDisponibiliteStandard(2, 4, $i, 1);
		
	}


$cstraessle = new Benevole(0, 'cs', 'Monsieur', 'Straessle', 'Christophe', 'Rue de la Prairie 8A', '', '1196', 'Gland', '', '0793321337', '0796238653', '', '', '', 0);
$cstraessle->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 1, 0, 0, 0);
	$cstraessle_transporteur = new Transporteur($cstraessle->get_id(), 1);

	for ($j=1; $j<=7; $j++) {
		$cstraessle_transporteur->supprimerDisponibiliteStandard(1, $j, 1, 1);
		$cstraessle_transporteur->supprimerDisponibiliteStandard(1, $j, 2, 1);
	}


$mtestuz = new Benevole(0, 'mt', 'Madame', 'Testuz', 'Micheline', 'Chemin de Bonmont 11K', '', '1260', 'Nyon', '0223629630', '', '0796536094', '', '', '', 0);
$mtestuz->ajouterParticipationDansFiliale(1, 1, 1, 1, 1, 0, 0, 0, 0);
	$mtestuz_transporteur = new Transporteur($mtestuz->get_id(), 1);
	
	$mtestuz_transporteur->supprimerDisponibiliteStandard(1, 4, 2, 1);

	
$mthevoz = new Benevole(0, 'mt', 'Monsieur', 'Thévoz', 'Michel', 'Chemin des Champs Blanc 72', '', '1279', 'Chavannes-de-Bogis', '0229600011', '', '0797982569', '', '', '', 0);
$mthevoz->ajouterParticipationDansFiliale(1, 0, 1, 1, 1, 1, 0, 0, 0);
	$mthevoz_transporteur = new Transporteur($mthevoz->get_id(), 1);
	
	for ($i=1; $i<=3; $i++) {
		$mthevoz_transporteur->supprimerDisponibiliteStandard(1, 1, $i, 1);
		$mthevoz_transporteur->supprimerDisponibiliteStandard(1, 5, $i, 1);
	}

$jturin = new Benevole(0, 'jt', 'Monsieur', 'Turin', 'Jean', 'Sur la Croix', '', '1261', 'Le Vaud', '0223664677', '', '', '', '', '', 0);
$jturin->ajouterParticipationDansFiliale(1, 0, 1, 1, 0, 0, 0, 0, 0);
	$jturin_transporteur = new Transporteur($jturin->get_id(), 1);


$fvautier = new Benevole(0, 'fv', 'Madame', 'Vautier', 'Françoise', 'Chemin Panlièvre 12', '', '1266', 'Duillier', '0223612295', '', '0794497139', '', '', '', 0);
$fvautier->ajouterParticipationDansFiliale(1, 0, 1, 1, 0, 0, 0, 0, 0);
	$fvautier_transporteur = new Transporteur($fvautier->get_id(), 1);

	for ($i=1; $i<=3; $i++) {
		$fvautier_transporteur->supprimerDisponibiliteStandard(1, 4, $i, 1);
	}
	

$pvautier = new Benevole(0, 'pv', 'Monsieur', 'Vautier', 'Philippe', 'Chemin Panlièvre 12', '', '1266', 'Duillier', '0223612295', '', '0794497138', '', '', '', 0);
$pvautier->ajouterParticipationDansFiliale(1, 0, 1, 1, 0, 0, 0, 0, 0);
	$pvautier_transporteur = new Transporteur($pvautier->get_id(), 1);

	for ($i=1; $i<=3; $i++) {
		$pvautier_transporteur->supprimerDisponibiliteStandard(1, 4, $i, 1);
	}
	



?>