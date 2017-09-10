<?php

require_once( str_replace ( '\\', '/', dirname(dirname(__FILE__))) . '/class.declaration.php' );
$load_needed_class_and_interface = load_class_and_interface(array('Beneficiaire'));


$_SESSION['filiale']['id'] = 1;
$_SESSION['benevole']['id'] = 1;

//construct(id,titre,nom,prenom,adresse,adr_comple,npa,ville,fixe,mobile,toujours_2)
$eaeschbach = new Beneficiaire(0, "Monsieur", "Aeschbach", "Emmanuel", "Route de Gingins 2", "", "1275", "Chéserex", "0223672961", "", 0);
$jandrieu = new Beneficiaire(0, "Madame", "Andrieu", "Josette", "Rue du Village 26", "", "1273", "Arzier", "0223660218", "", 0);
$jbader = new Beneficiaire(0, "Madame", "Bader", "Jeanne-Marie", "Route de Clementy 41", "", "1260", "Nyon", "0223617000", "", 0);
$aberlie = new Beneficiaire(0, "Madame", "Berlié", "Arlette", "Route de l'Etraz 56", "", "1260", "Nyon", "0223630831", "", 0);
$abertrand = new Beneficiaire(0, "Madame", "Bertrand", "Amalia", "Rue Mauverney 16B", "", "1196", "Gland", "0223641099", "", 0);
$mbertrand = new Beneficiaire(0, "Monsieur", "Bertrand", "Michel", "Rue Mauverney 16B", "", "1196", "Gland", "0223641099", "", 0);
$fbeetschen = new Beneficiaire(0, "Madame", "Beetschen", "Françoise", "Route de Benex 6a", "", "1197", "Prangins", "0223631301", "", 0);
$mbenneton = new Beneficiaire(0, "Madame", "Benneton", "Monique", "Chemin des Chaux 4", "", "1197", "Prangins", "0223621386", "", 0);
$cbolay = new Beneficiaire(0, "Monsieur", "Bolay", "Charly", "Chemin du Carré 22", "", "1271", "Givrins", "0223691348", "", 0);
$fbovigny = new Beneficiaire(0, "Monsieur", "Bovigny", "Francis", "Route de Divonne 24", "", "1260", "Nyon", "0223622611", "0792119392", 0);
$aburri = new Beneficiaire(0, "Monsieur", "Burri", "André", "Chemin du Vernay 32", "", "1196", "Gland", "0223648986", "0796049901", 0);
$aciarleglio = new Beneficiaire(0, "Madame", "Ciarleglio", "Antonella", "Chemin des Vignes 18", "", "1196", "Gland", "0223646416", "0797029510", 0);
$mcrettenand = new Beneficiaire(0, "Madame", "Crettenand", "Marceline", "Ruelle de L'Ecusson", "", "1261", "Le Vaud", "0223664182", "", 0);
$ecrudo = new Beneficiaire(0, "Madame", "Crudo", "Elda", "Grand-Rue 8", "", "1196", "Gland", "0223645925", "0787378438", 0);
$rdorand = new Beneficiaire(0, "Monsieur", "Dorand", "René", "La Levratte 28", "", "1260", "Nyon", "0223618802", "", 0);
$rchevallaz = new Beneficiaire(0, "Madame", "Chevallaz", "Renée", "Route d'Oulteret 34", "", "1260", "Nyon", "0223611401", "", 0);
$eetienne = new Beneficiaire(0, "Madame", "Etienne", "Esther", "La Lignière", "7", "1196", "Gland", "0223644414", "", 0);
$getienne = new Beneficiaire(0, "Monsieur", "Etienne", "Georges", "La Lignière", "7", "1196", "Gland", "0223644414", "", 0);
$ifeser = new Beneficiaire(0, "Madame", "Feser", "Isabelle", "Mafroi 11", "", "1260", "Nyon", "0223614824", "", 0);
$sflorin = new Beneficiaire(0, "Madame", "Florin", "Sandra", "La Lignière", "Résidence Forest 7", "1196", "Gland", "0223640520", "", 0);
$igafner = new Beneficiaire(0, "Madame", "Gafner", "Isabelle", "Chemin de la Laiterie", "", "1261", "Marchissy", "0223682082", "0798522425", 0);
$cganzerli = new Beneficiaire(0, "Madame", "Ganzerli", "Carmen", "Chemin des Pâquerettes 17", "Maison de Bourgogne - appartement 62", "1260", "Nyon", "0223610973", "", 0);
$ygehri = new Beneficiaire(0, "Madame", "Gehri", "Yvonne", "Route de la Bossière 8", "", "1197", "Prangins", "0223615702", "", 0);
$cghadimy = new Beneficiaire(0, "Madame", "Ghadimy-Navay", "Chahla", "Chemin d'Eysins 36", "", "1260", "Nyon", "0223617588", "", 0);
$igolaz = new Beneficiaire(0, "Madame", "Golaz", "Irène", "Route d'Arzier 17", "", "1264", "St-Cergue", "0223600463", "0767398372", 0);
$jgoy = new Beneficiaire(0, "Monsieur", "Goy", "Jean-Claude", "Camping de Vendôme 47", "", "1278", "La Rippe", "", "0792008158", 0);
$ygenevaz = new Beneficiaire(0, "Madame", "Genevaz", "Yvonne", "Chemin des Pâquerettes 17", "Maison de Bourgogne", "1260", "Nyon", "0223617370", "", 0);
$jgranger = new Beneficiaire(0, "Monsieur", "Granger", "Jean-François", "Longemalle 4", "", "1262", "Eysins", "0223617232", "", 0);
$jhanggi = new Beneficiaire(0, "Madame", "Hänggi", "Jeanne", "Chemin d'Eysins 30", "", "1260", "Nyon", "0223619308", "0788485756", 0);
$hherger = new Beneficiaire(0, "Madame", "Herger", "Henriette", "Malagny 29", "", "1196", "Gland", "0223645190", "", 0);
$yhonore = new Beneficiaire(0, "Monsieur", "Honore", "Yannick", "Route de l'Etraz", "", "1260", "Nyon", "0223612183", "0774223700", 0);
$hloup = new Beneficiaire(0, "Madame", "Loup", "Hélène", "Chemin de la Chavanne 12", "", "1196", "Gland", "0223642438", "", 0);
$mlugrin = new Beneficiaire(0, "Madame", "Lugrin", "Monique", "Chemin du Prélaz 3", "", "1260", "Nyon", "0223614529", "", 0);
$jludinard = new Beneficiaire(0, "Monsieur", "Ludinard", "Jean-Pierre", "Avenue Alfred-Cortot 9D", "", "1260", "Nyon", "0223629828", "0788983007", 0);
$pmackenzie = new Beneficiaire(0, "Madame", "Mackenzie", "Pamela", "Route de la Porcelaine 10", "", "1260", "Nyon", "0223621840", "", 0);
$rmansour = new Beneficiaire(0, "Madame", "Mansour", "Rose", "Chemin de la Redoute 34", "", "1260", "Nyon", "0223610259", "", 0);
$cmartin = new Beneficiaire(0, "Monsieur", "Martin", "Claude", "Cité-Ouest 5", "", "1196", "Gland", "0223641605", "", 0);
$jmayor = new Beneficiaire(0, "Monsieur", "Mayor", "Jean", "Prélaz 16", "", "1260", "Nyon", "0223618841", "", 0);
$lmayor = new Beneficiaire(0, "Madame", "Mayor", "Léa", "Prélaz 16", "", "1260", "Nyon", "0223618841", "", 0);
$gmoll = new Beneficiaire(0, "Madame", "Moll", "Georgette", "Route de Bonmont 25", "", "1275", "Chéserex", "0223691208", "", 0);
$smonti = new Beneficiaire(0, "Madame", "Monti", "Sylvie", "Rue de Savoie 5", "", "1196", "Gland", "0223644621", "", 0);
$vmuller = new Beneficiaire(0, "Madame", "Müller", "Véronique", "Route de St-Cergue 22B", "", "1260", "Nyon", "0225668667", "0793100565", 0);
$gmury = new Beneficiaire(0, "Monsieur", "Mury", "Gérald", "Cité-Ouest 32", "", "1196", "Gland", "0223642543", "0796724090", 0);
$rpanchaud = new Beneficiaire(0, "Monsieur", "Panchaud", "Raymond", "Chemin du Jura 12", "", "1272", "Genolier", "0223663442", "", 0);
$rpanico = new Beneficiaire(0, "Madame", "Panico", "Rose", "Rue des Alpes 10B", "", "1196", "Gland", "0223648729", "", 0);
$fpeuch = new Beneficiaire(0, "Madame", "Peuch", "Francine", "Rue du Battoir 7", "", "1269", "Bassins", "0223662618", "", 0);
$cpfersich = new Beneficiaire(0, "Madame", "Pfersich", "Claudine", "Chemin des Plantaz 11", "", "1260", "Nyon", "0223619521", "", 0);
$apitari = new Beneficiaire(0, "Monsieur", "Pitari", "Agrippino", "Cité Ouest 5", "", "1196", "Gland", "0223640142", "", 0);
$jpuenzieux = new Beneficiaire(0, "Monsieur", "Puenzieux", "Jean-Claude", "Chemin des Plantaz 47", "Bel Automne", "1260", "Nyon", "0223621280", "", 0);
$aroda = new Beneficiaire(0, "Madame", "Roda", "Armida", "Chemin Communet 10A", "", "1196", "Gland", "0223641643", "", 0);
$arose = new Beneficiaire(0, "Madame", "Rosé", "Alice", "Route de Gingins 11", "", "1260", "Nyon", "0223613108", "", 0);
$srumasuglia = new Beneficiaire(0, "Monsieur", "Rumasuglia", "Salvatore", "Chemin de la Paix 12", "", "1260", "Nyon", "0223616845", "", 0);
$msalvador = new Beneficiaire(0, "Madame", "Salvador", "Margarita", "Route de Gingins 8", "", "1270", "Trélex", "", "0765546178", 0);
$gschacher = new Beneficiaire(0, "Monsieur", "Schacher", "Georges", "Route de St-Cergue 27", "", "1268", "Begnins", "0223663248", "", 0);
$yschacher = new Beneficiaire(0, "Madame", "Schacher", "Yvette", "Route de St-Cergue 27", "", "1268", "Begnins", "0223663248", "", 0);
$tschenk = new Beneficiaire(0, "Madame", "Schenk", "Thérèse", "Rue du Temple 1", "", "1260", "Nyon", "0223622607", "", 0);
$msellami = new Beneficiaire(0, "Madame", "Sellami", "Monique", "Rue du Jura 15", "", "1196", "Gland", "0223645324", "0774663889", 0);
$fselmani = new Beneficiaire(0, "Madame", "Selmani", "Fatima", "Chemin d'Eysins 32", "", "1260", "Nyon", "0223610731", "0797396075", 0);
$hselmani = new Beneficiaire(0, "Monsieur", "Selmani", "Hajrush", "Chemin d'Eysins 32", "", "1260", "Nyon", "0223610731", "0794396075", 0);
$ysekkiou = new Beneficiaire(0, "Madame", "Sekkiou", "Yvonne", "Route de Divonne 11", "", "1260", "Nyon", "0223613170", "", 0);
$nsudan = new Beneficiaire(0, "Monsieur", "Sudan", "Noël", "Chemin des Laurelles 56", "", "1196", "Gland", "", "0792507482", 0);
$gvaney = new Beneficiaire(0, "Madame", "Vaney", "Germaine", "Chemin de la Redoute 11", "", "1260", "Nyon", "0223614581", "", 0);
$gvigini = new Beneficiaire(0, "Madame", "Vigini", "Gandenzio", "Les Pralies", "CP 444", "1264", "St-Cergue", "0223601074", "", 0);
$pwaber = new Beneficiaire(0, "Monsieur", "Waber", "Peter", "Chemin de la Paix 4", "", "1260", "Nyon", "0223617210", "", 0);
$jwagnon = new Beneficiaire(0, "Monsieur", "Wagnon", "Jean-Blaise", "Tattes d'Oie 85", "", "1260", "Nyon", "0223616123", "", 0);
$lzryd = new Beneficiaire(0, "Madame", "Zryd", "Lelia", "Chemin de la Ladière 13", "", "1276", "Gingins", "0223691298", "", 0);
$jzimmermann = new Beneficiaire(0, "Madame", "Zimmermann", "Judith", "Perdtemps 11", "", "1260", "Nyon", "0227340647", "", 0);


?>