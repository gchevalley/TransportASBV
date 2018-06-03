<?php

require_once( str_replace ( '\\', '/', dirname(dirname(__FILE__))) . '/class.declaration.php' );
$load_needed_class_and_interface = load_class_and_interface(array('Benevole', 'Transporteur', 'Permanencier'));

$_SESSION['filiale']['id'] = 1;
$_SESSION['benevole']['id'] = 1;



?>