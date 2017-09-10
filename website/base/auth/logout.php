<?php

session_start();
session_destroy();
unset($_SESSION);
setcookie('PHPSESSID','',time()-1);
$_SESSION = array();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<title>Transport ASBV</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	
	
	<link rel="stylesheet" type="text/css" href="../../css/base.css" />
	
</head>

<body>
	<div id="container">
		<p>Déconnexion effectuée</p>
		<p><a href="../../index.php">Se connecter</a></p>
	</div>
</body>
</html>