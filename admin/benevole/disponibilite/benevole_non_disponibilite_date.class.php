<?php

//include('../../config/auth/secure.php');
require_once( str_replace ( '\\', '/', dirname(dirname(dirname(__FILE__)))) . '/class.declaration.php' );
$load_needed_class_and_interface = load_class_and_interface(array('Benevole'));

class Benevole_non_Disponibilite_Date {
	private $id_benevole = 0;
	private $id_categorie = 0;
	private $date_custom = '';
	private $source = '';
	private $need_exportation = TRUE;
	private $tag = '';
	private $remarque = '';
	
	
	static function wash_old_date() {
		//control les droits
		if (Benevole::id_exists($_SESSION['benevole']['id'])) {
			$user_permanencier = new Benevole($_SESSION['benevole']['id']);
				
			if ($user_permanencier->checkIsPermanencier($id_filiale) || $user_permanencier->checkIsSuperAdmin() || $user_permanencier->checkIsAdminOfFiliale($id_filiale)) {
				//les droits min. requis sont ok
			} else {
				return FALSE;
				die();
			}
		} else {
			return FALSE;
			die();
		}
		
		global $dbh;
		
		$today_date = $dbh->quote(date('Y-m-d'));
		
		$sql = "DELETE FROM benevole_non_disponibilite_date ";
		$sql .= " WHERE date_custom <" . $today_date;
		
		$result = $dbh->exec($sql);
		
		return $result;
		
	}
	
}


?>