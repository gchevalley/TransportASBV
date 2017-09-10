<?php 

session_start();
session_destroy();
unset($_SESSION);
setcookie('PHPSESSID','',time()-1);
$_SESSION = array();

	require ('../../../admin/class.declaration.php');
	load_class_and_interface(array('Benevole', 'Filiale', 'Transporteur'));
	
	if (isset($_POST['login']) && isset($_POST['password']) && isset($_POST['filiale'])) {
		$super_login= preg_replace('/\s/', '', $_POST['login']);
		$super_password = $_POST['password'];
		$filiale = $_POST['filiale'];
		
		$tmp_benevole = new Benevole(Benevole::get_id_from_super_login($super_login));
		$tmp_filiale = new Filiale(Filiale::get_id_from_nom($filiale));
		
		if ($tmp_benevole && $tmp_benevole->checkLocalPassword($super_password)) { //$tmp_benevole->checkLocalPassword($super_password)
			
			if (!$tmp_benevole->checkIsPermanencier($tmp_filiale->get_id())) {
				$message = "L'identifiant est correct mais ce benevole n'est pas permanencier dans cette filiale ($filiale) ";
				$message .= '<a href="auth.php">Retour</a>';
			
			} else {
				
				//on change l'id de session
				session_start();
				session_regenerate_id();
				
				//Charge les donnees du benevole et les stock en session
				$_SESSION['auth']['status'] = 'OK';
				$_SESSION['auth']['date'] = date('d-m-Y');
				$_SESSION['auth']['time'] = date('H:i:s');
				$_SESSION['benevole']['auth_status'] = "auth";
				$_SESSION['benevole']['id'] = $tmp_benevole->get_id();
				
				
				$array_nom_tmp_benevole = $tmp_benevole->get_nom_complet();
				
				$_SESSION['benevole']['nom_complet'] = $array_nom_tmp_benevole;
				
				if (isset($array_nom_tmp_benevole['nom'])) {
					$_SESSION['benevole']['nom'] = $array_nom_tmp_benevole['nom'];
				}
				
				if (isset($array_nom_tmp_benevole['prenom'])) {
					$_SESSION['benevole']['prenom'] = $array_nom_tmp_benevole['prenom'];
				}
				
				
				$_SESSION['benevole']['id_benevole_filiale']= $tmp_benevole->get_id($tmp_filiale->get_id());
				
				//Charge les donnees de la filiale
				$_SESSION['filiale']['id'] = $tmp_filiale->get_id();
				$_SESSION['filiale']['nom'] = $tmp_filiale->get_nom();
				
				//traitement sur la DB
				Transporteur::wash_non_dispo_date();
				
				$redirect_page = "/TransportASBV/website/index.php";
				header('Location: http://localhost' . $redirect_page);
				
				exit();
				
			}
			
		} else {
			$message = 'Informations de connexion incorrectes ';
			$message .= '<a href="auth.php">Retour</a>';
		}
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<title>Transport ASBV</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	
	<link rel="stylesheet" type="text/css" href="../../css/base.css" />
	
</head>

<body>

	<?php 
		echo $message;
	?>
</body>
</html>