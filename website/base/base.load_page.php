<?php
//extraction de module de $_POST ou $_GET
if (isset($_POST['module']) || isset($_GET['module'])) {
	if (isset($_POST['module'])) {
		$module = $_POST['module'];
	} elseif (isset($_GET['module'])) {
		$module = $_GET['module'];
	}
} else {
	
}

if (isset($_POST['sub_module']) || isset($_GET['sub_module'])) {
	if (isset($_POST['sub_module'])) {
		$sub_module = $_POST['sub_module'];
	} elseif (isset($_GET['sub_module'])) {
		$sub_module = $_GET['sub_module'];
	}
} else {
	
}

//extraction d'action de $_POST ou $_GET
if (isset($_POST['action']) || isset($_GET['action'])) {
	if (isset($_POST['action'])) {
		$action = $_POST['action'];
	} elseif (isset($_GET['action'])) {
		$action = $_GET['action'];
	}
} else {
	
}

require('./base/base.module.php');

?>