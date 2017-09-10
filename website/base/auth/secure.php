<?php

require_once( str_replace ( '\\', '/', dirname(dirname(dirname(dirname(__FILE__))))) . '/admin/class.declaration.php' );
$load_needed_class_and_interface = load_class_and_interface(array('Benevole', 'Permanencier', 'Filiale'));

session_start();

if (count($_SESSION) > 0) {
	if (!isset($_SESSION['benevole']['id']) || !isset($_SESSION['benevole']['id_benevole_filiale']) || !isset($_SESSION['filiale']['id']) || !Benevole::id_exists($_SESSION['benevole']['id']) || !Permanencier::id_exists($_SESSION['benevole']['id_benevole_filiale']) || !Filiale::id_exists($_SESSION['filiale']['id']) ) {
		header('Location: http://localhost/TransportASBV/website/base/auth/auth.php');
	}
} else {
	header('Location: http://localhost/TransportASBV/website/base/auth/auth.php');
}
	
?>