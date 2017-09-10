<?php

if (isset($_SESSION['last_page'])) {
	unset($_POST);
	unset($_GET);
	$module = $_SESSION['last_page']['module'];
	$sub_module = $_SESSION['last_page']['sub_module'];
	$action = $_SESSION['last_page']['action'];
	$id = $_SESSION['last_page']['id'];
	
	require('./base/base.module.php');
	
} else {
	require('./processing/dashboard.php');
}
	

?>