<?php
	
include_once('config.inc.php');
	
	$user = $cfg['DATABASE']['user'];
	$pass = $cfg['DATABASE']['password'];
	$dsn = 'mysql:host=' . $cfg['DATABASE']['host'] .';dbname=' . $cfg['DATABASE']['database'] . ';charset=UTF-8';
	
	try {
		$dbh = new PDO($dsn, $user, $pass, array(PDO::ATTR_PERSISTENT => TRUE));
		
		//http://www.developpez.net/forums/d530665/bases-donnees/mysql/sql-procedural/pdo-pb-caracteres-accentues-base/
		$dbh->exec("SET CHARACTER SET utf8");
	} catch(PDOException $e) {
		die("Erreur ! : " . $e->getMessage());
	}
?>