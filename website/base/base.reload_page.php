<?php

if (isset($_SESSION['last_page'])) {
	unset($_POST);
	unset($_GET);
	if ( isset($_SESSION['last_page']['module']) ) {
		$module = $_SESSION['last_page']['module'];
	}

	if ( isset($_SESSION['last_page']['sub_module']) ) {
		$sub_module = $_SESSION['last_page']['sub_module'];
	}

	if ( isset($_SESSION['last_page']['action']) ) {
		$action = $_SESSION['last_page']['action'];
	}

	if ( isset($_SESSION['last_page']['id']) ) {
		$id = $_SESSION['last_page']['id'];
	}

	require('./base/base.module.php');

} else {
	require('./processing/dashboard.php');
}


?>
