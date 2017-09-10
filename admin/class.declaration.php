<?php

$current_dir = str_replace ( '\\', '/', dirname(__FILE__) ); //unix style
$base_dir = str_replace ( '\\', '/', dirname($current_dir));
$projet_name = substr($_SERVER['SCRIPT_NAME'], 1, (strpos($_SERVER['SCRIPT_NAME'], '/', 1))-1);
$server_name = $_SERVER['SERVER_NAME'];

class Class_and_Interface {
	private $type = '';
	private $nom = '';
	private $path = '';
	
	function __construct($type, $nom, $path) {
		global $base_dir;
		
		$this->type = $type;
		$this->nom = $nom;
		
		if (file_exists($base_dir . $path))  {
			$this->path = $path;
		} else {
			echo 'chemin d\'accès ' . $path . ' invalide';
		}
	}
	
	public function get_nom() {
		return $this->nom;
	}
	
	public function load() {
		global $base_dir;
		
		if ($this->type == 'Class') {
			if (!class_exists($this->nom)) {
				require_once($base_dir . $this->path);
			}
		} elseif ($this->type == 'Interface') {
		if (!interface_exists($this->nom)) {
				require_once($base_dir . $this->path);
			}
		}
	}
}


// declaration des objects class + interface
$class_and_interface[] = new Class_and_Interface('Class', 'Beneficiaire', '/admin/beneficiaire/beneficiaire.class.php');
	$class_and_interface[] = new Class_and_Interface('Class', 'Repondant', '/admin/beneficiaire/repondant/beneficiaire_repondant.class.php');
	$class_and_interface[] = new Class_and_Interface('Class', 'Repondant_Categorie', '/admin/beneficiaire/repondant/beneficiaire_repondant_categorie.class.php');

$class_and_interface[] = new Class_and_Interface('Class', 'Benevole', '/admin/benevole/benevole.class.php');
	$class_and_interface[] = new Class_and_Interface('Class', 'Administrateur_Filiale', '/admin/benevole/administrateur_filiale.class.php');
	$class_and_interface[] = new Class_and_Interface('Class', 'Administrateur', '/admin/benevole/administrateur.class.php');
	$class_and_interface[] = new Class_and_Interface('Class', 'Permanencier', '/admin/benevole/permanencier.class.php');
	$class_and_interface[] = new Class_and_Interface('Class', 'Transporteur', '/admin/benevole/transporteur.class.php');
		$class_and_interface[] = new Class_and_Interface('Class', 'Jour_Semaine', '/admin/benevole/disponibilite/jour_semaine.class.php');
		$class_and_interface[] = new Class_and_Interface('Class', 'Periode_Journee', '/admin/benevole/disponibilite/periode_journee.class.php');
		$class_and_interface[] = new Class_and_Interface('Class', 'Benevole_Disponibilite_Categorie', '/admin/benevole/disponibilite/benevole_disponibilite_categorie.class.php');
		$class_and_interface[] = new Class_and_Interface('Class', 'Benevole_Disponibilite', '/admin/benevole/disponibilite/benevole_disponibilite.class.php');
		$class_and_interface[] = new Class_and_Interface('Class', 'Benevole_non_Disponibilite_Date', '/admin/benevole/disponibilite/benevole_non_disponibilite_date.class.php');

$class_and_interface[] = new Class_and_Interface('Interface', 'Contact', '/admin/contact/contact.interface.php');

$class_and_interface[] = new Class_and_Interface('Class', 'Help', '/admin/help/help.class.php');
	$class_and_interface[] = new Class_and_Interface('Class', 'Topic', '/admin/help/topic.class.php');

$class_and_interface[] = new Class_and_Interface('Class', 'Filiale', '/admin/filiale/filiale.class.php');
	$class_and_interface[] = new Class_and_Interface('Class', 'Facture', '/admin/facture/facture.class.php');
	$class_and_interface[] = new Class_and_Interface('Class', 'Listing', '/admin/listing/listing.class.php');

$class_and_interface[] = new Class_and_Interface('Class', 'Lieu', '/admin/lieu/lieu.class.php');
	$class_and_interface[] = new Class_and_Interface('Class', 'Lieu_Categorie', '/admin/lieu/categorie/lieu_categorie.class.php');

$class_and_interface[] = new Class_and_Interface('Class', 'Trajet_Pre_Defini', '/admin/trajet_pre_defini/trajet_pre_defini.class.php');

$class_and_interface[] = new Class_and_Interface('Class', 'Transport', '/admin/transport/transport.class.php');
	$class_and_interface[] = new Class_and_Interface('Class', 'Transport_Type_Calcul_Distance', '/admin/transport/type_calcul_distance/transport_type_calcul_distance.class.php');
	$class_and_interface[] = new Class_and_Interface('Class', 'Transport_Categorie', '/admin/transport/categorie/transport_categorie.class.php');

$class_and_interface[] = new Class_and_Interface('Class', 'GLM', '/admin/models/glm.class.php');
$class_and_interface[] = new Class_and_Interface('Class', 'GLMM', '/admin/models/glmm.class.php');
$class_and_interface[] = new Class_and_Interface('Class', 'Emergency', '/admin/models/emergency.class.php');

$class_and_interface[] = new Class_and_Interface('Class', 'Direction', '/config/GoogleMapAPI/api.gmap.class.php');
$class_and_interface[] = new Class_and_Interface('Class', 'Geocode', '/config/GoogleMapAPI/api.gmap.class.php');	

$class_and_interface[] = new Class_and_Interface('Class', 'Rmail', '/config/Rmail/Rmail.php');
$class_and_interface[] = new Class_and_Interface('Class', 'PHPMailer', '/config/PHPMailer/class.phpmailer.php');
$class_and_interface[] = new Class_and_Interface('Class', 'PHPExcel', '/config/phpxls/PHPExcel.php');


$class_and_interface[] = new Class_and_Interface('Class', 'zip', '/config/zip/zip.class.php');

$class_and_interface[] = new Class_and_Interface('Class', 'pChart', '/config/pChart/pChart/pChart.class');
	$class_and_interface[] = new Class_and_Interface('Class', 'pData', '/config/pChart/pChart/pData.class');
	$class_and_interface[] = new Class_and_Interface('Class', 'pCache', '/config/pChart/pChart/pCache.class');

	
	
	
function load_class_and_interface($list_class_and_interface) { //receptionne un array
	global $class_and_interface; //donne l'acces a la matrix qui contient les objets class + interface
	
	foreach ($list_class_and_interface as $arg_class_and_interface) {
		foreach ($class_and_interface as $individual_class_and_interface) {
			
			if ($arg_class_and_interface == $individual_class_and_interface->get_nom()) {
				$individual_class_and_interface->load();
				$result[$arg_class_and_interface]['load'] = TRUE;
			}
		}
	}
	
	//return un tableau nom class + si reussi/echec
	//return $result;
}


// utilities
require_once($base_dir . '/config/connect.db.inc.php');
require_once($base_dir . '/config/utilities.php');
require_once($base_dir . '/backup/config.php');
require_once($base_dir . '/backup/backup.php');

// api & class
require_once($base_dir . '/config/tcpdf/config/lang/fra.php');
require_once($base_dir . '/config/tcpdf/tcpdf.php');


// helpers
require_once($base_dir . '/admin/helpers/form.helpers.php');
require_once($base_dir . '/admin/helpers/variable.helpers.php');

?>