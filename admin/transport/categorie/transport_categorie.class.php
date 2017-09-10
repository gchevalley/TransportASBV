<?php

//include('../../config/auth/secure.php');
require_once( str_replace ( '\\', '/', dirname(dirname(dirname(__FILE__)))) . '/class.declaration.php' );
$load_needed_class_and_interface = load_class_and_interface(array('Benevole'));


class Transport_Categorie {
	private $id = 0;
	private $nom = '';
	private $priorite = 0;
	
	function __construct($id_transport_categorie, $nom='', $priorite='') {
		
		if (is_numeric($id_transport_categorie) && Transport_Categorie::id_exists($id_transport_categorie)) {	
			
			$this->id = $id_transport_categorie;
			$this->mountAttributsFromDB();
		
		} else { //creation de la nouvelle entite
			
			if ($nom != '') {
				$this->addEntryDB($nom, $priorite);
			}
		}
	} // class.Transport_Categorie.func.__construct
	
	
	private function mountAttributsFromDB() {
		
		//charge les donnees direct depuis la DB
		global $dbh;
		
		//mount la totalite des donnees
		$sql = "SELECT * FROM transport_categorie WHERE id=" .$this->id;
		
		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);
		
		//s'assurer qu'un resultat est retourne bien alloue les donnees aux attributs de l'object
		$this->nom = $result['nom'];
		$this->priorite = $result['priorite'];
		
	} // class.Transport_Categorie.func.mountAttributsFromDB
	
	
	private function addEntryDB($nom='', $priorite) {
		
		if (Benevole::id_exists($_SESSION['benevole']['id'])) {
			$tmp_benevole = new Benevole($_SESSION['benevole']['id']);
			
			if ($tmp_benevole->checkIsSuperAdmin()) {
				// continue l'execution de la function
			} else {
				if (Filiale::id_exists($_SESSION['filiale']['id'])) {
					if ($tmp_benevole->checkIsPermanencier($_SESSION['filiale']['id']) || $tmp_benevole->checkIsAdminOfFiliale($_SESSION['filiale']['id'])) {
						// continue l'execution de la function
					} else {
						die();
					}
				} else {
					die();
				}
			}
		} else {
			die();
		}
		
		if (!isset($nom) && !isset($nom) && !is_numeric($priorite)) {
			die();
		}
		
		global $dbh;
		
		//processing de nettoyage des args de la fonction
		$nom = $dbh->quote($nom);

		
		//creation de la nouvelle entite dans la db
		$sql = "INSERT INTO transport_categorie (nom, priorite) ";
		$sql .= "VALUES ($nom, $priorite)";
		
		$statut_query = $dbh->exec($sql);
		
		//mount l'object
		$this->id = $dbh->lastInsertId();
		$this->mountAttributsFromDB();
		
	} // class.Transport_Categorie.func.addEntryDB
	
	
	public function editerAttributs($attr, $new_value) { //2 matrix ou 2 valeurs
		
		if (!is_numeric($_SESSION['benevole']['id']) || !Benevole::id_exists($_SESSION['benevole']['id'])) {
			die();
		}
		
		global $dbh;
			
		$sql = "UPDATE transport_categorie ";
		
		if (is_array($attr) && is_array($new_value)) { //2 tableaux receptionnes
			$nbre_attribut = count($attr);
			$nbre_new_value = count($new_value);
			
			if ($nbre_attribut != $nbre_new_value) {
				return FALSE;
			}
			
			$sql .= "SET ";
			
			foreach ($attr as $index=>$attribut_to_edit)  {
				if (is_numeric($new_value[$index])) {
					if(preg_match('`[0-9]{7,10}`',$new_value[$index])) {
						//numero de tel
						$n_value = $dbh->quote($new_value[$index]);
					} else {
						$n_value = $new_value[$index];
					}
				} else {
					$n_value = $dbh->quote($new_value[$index]);
				}
				
				$sql .= "$attribut_to_edit=$n_value";
				
				if ($index < ($nbre_attribut-1)) {
					$sql .= ',';
				} else {
					$sql .= ' ';
				}
			}
			
		} elseif (!is_array($attr) && !is_array($new_value)) { //1 seule valeur $attr=>$new_value receptionnee
			if (!is_numeric($new_value)) {
				$new_value = $dbh->quote($new_value);
			}
			
			$sql .= "SET $attr=$new_value ";
		
		} else {
			die();
		}
		
		$sql .= "WHERE id=" . $this->id;
		$statut_query = $dbh->exec($sql);
		
		//recharge avec les nouvelles donnees
		$this->mountAttributsFromDB();
	} // class.Transport_Categorie.func.editerAttributs
	
	
	public function get_id() {
		return $this->id;
	} // class.Transport_Categorie.func.get_id
	
	
	public function get_nom() {
		return $this->nom;
	} // class.Transport_Categorie.func.get_nom
	
	
	public static function get_id_from_nom($nom) {
		
		if (!str_word_count($nom, 0) == 1) {
			return FALSE;
		}
		
		global $dbh;
		
		$sql = "SELECT id FROM transport_categorie WHERE nom='" . strtolower($nom) . "'";
		
		$sth = $dbh->query($sql);
		$result = $sth->fetch(PDO::FETCH_ASSOC);
		
		if ($result != false) {
			return $result['id'];
		} else {
			return FALSE;
		}
		
	} // class.Transport_Categorie.func.get_id_from_nom
	
	
	
	public static function id_exists($id_to_check) {
		if (checkID($id_to_check)) {
			global $dbh;
			$sql = "SELECT * FROM transport_categorie WHERE id=" .$id_to_check;
			$sth = $dbh->query($sql);
			$result = $sth->fetch(PDO::FETCH_ASSOC);
			
			if ($result != false) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			//bad id type
			return FALSE;
		}
	} //class.Transport_Categorie.func.id_exists
	
	
	private function return_pair_key_value() {
		
		$tmp_array['id']['value']= $this->id;
		$tmp_array['nom']['value']= $this->nom;
		$tmp_array['priorite']['value']= $this->priorite;
		
		return $tmp_array;
	} // class.Transport_Categorie.func.return_pair_key_value
	
	
	public static function form($action, $data_to_display='') {
		
		if (is_array($data_to_display)) {
		
		} elseif (is_numeric($data_to_display) && Transport_Categorie::id_exists($data_to_display)) {
			//numero de beneficaire
			$tmp_transport_categorie = new Transport_Categorie($data_to_display);
			unset($data_to_display);
			$data_to_display = $tmp_transport_categorie->return_pair_key_value();
		} elseif ($data_to_display instanceof Transport_Categorie) {
			//convertir en un tableau data_to_display_habituel
			$data_to_display = $data_to_display->return_pair_key_value();
		} else {
			$data_to_display = array();
		}
		
		switch ($action) {
			case "add":
				echo Transport_Categorie::form_base($action, $data_to_display);
				break;
			case "view":
				//s'assure que le transport est connu sinon charge une listbox de selection
				if (isset($data_to_display['id']['value']) && Transport_Categorie::id_exists($data_to_display['id']['value'])) {
					echo Transport_Categorie::form_view($action, $data_to_display);
				} else {
					echo Transport_Categorie::form_list($action);
				}
				
				break;
			case "edit":
				//s'assure que le beneficiaire est connu sinon charge une listbox de selection
				if (isset($data_to_display['id']['value']) && Transport_Categorie::id_exists($data_to_display['id']['value'])) {
					echo Transport_Categorie::form_base($action, $data_to_display);
				} else {
					echo Transport_Categorie::form_choose($action, $data_to_display);
				}
				
				break;
			case "list":
				echo Transport_Categorie::form_list($action);
				break;
			default:
				echo Transport_Categorie::form_list($action);
		}
			
	} // class.Transport_Categorie.func.form
	
	
	private static function form_base($action, $data_to_display='') {
		//retourne le code html du formulaire
		unset($_POST);
		global $dbh;
		
		$html_code = '';
		
		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}
		
		$html_code .= '<form id="transport_categorie_' . $action . '" action="" method="post">';
				$html_code .= '<legend>Catégorie de Transport</legend>';
				
				$html_code .= '<p>';
					$html_code .= '<label for="nom">Nom de la catégorie</label>';
					$html_code .= add_FormElement_input('text', 'nom', '', $data_to_display['nom']['value']);
				$html_code .= '</p>';
				
				
				$html_code .= '<p>';
					$html_code .= '<label for="priorite">Priorité</label>';
					
					$html_code .= '<select id="priorite" name="priorite">';
						for ($i=0; $i<=10; $i++) {
							$html_code .= '<option value="' . $i . '" ';
							
							if ($i == $data_to_display['priorite']['value']) {
								$html_code .= 'selected="selected">';
							} else {
								$html_code .= '>';
							}
							
							$html_code .= $i;
							
							$html_code .= '</option>';
							
						}
						
					$html_code .= '</select>';
				$html_code .= '</p>';
			
			
			$html_code .= '<p>';
				
				if (isset($data_to_display['id']['value'])) {
					$html_code .= add_FormElement_input('hidden', 'id', '', $data_to_display['id']['value']);
				}
				
				$html_code .= add_FormElement_input('hidden', 'form', '', 'base');
				$html_code .= add_FormElement_input('hidden', 'module', '', 'transport_categorie');
				$html_code .= add_FormElement_input('hidden', 'sub_module', '', '""');
				$html_code .= add_FormElement_input('hidden', 'action', '', $action);
				
				$html_code .= '<input type="submit" value="Soumettre" />';
			$html_code .= '</p>';
			
		$html_code .= '</form>';
		
		$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));
		return $html_code;
	} //class.Transport.form.base
	
	
	private static function form_choose($action, $data_to_display='') {
		unset($_POST);
		global $dbh;
		$sql = "SELECT * FROM transport_categorie ORDER BY nom";
		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);
		
		$html_code = '<form id="transport_categorie_choose" action="" method="post">';
			$html_code .= '<select id="id" name="id">';
				foreach ($result as $row) {
					$html_code .= '<option value="' . $row['id'] . '">';
					$html_code .= strtoupper($row['nom']);
					$html_code .= '</option>';
				}
			$html_code .= '</select>';
			
			$html_code .= '<p>';
				$html_code .= add_FormElement_input('hidden', 'form', '', 'choose');
				$html_code .= add_FormElement_input('hidden', 'module', '', 'transport_categorie');
				$html_code .= add_FormElement_input('hidden', 'sub_module', '', '""');
				$html_code .= add_FormElement_input('hidden', 'action', '', $action);
				
				$html_code .= '<input type="submit" value="Soumettre" />';
			$html_code .= '</p>';
		$html_code .= '</form>';
		
		//return utf8_encode($html_code);
		return ($html_code);
	} //class.Transport_Categorie.form.choose
	
	
	private static function form_list($action) {
		global $dbh;
		
		if (isset($_POST['search'])) {
			$sql = "SELECT * FROM transport_categorie ";
			$sql .= " WHERE nom LIKE '%" . $_POST['search'] . "%'";
			$sql .= " OR priorite =" . $_POST['search'];
			$sql .= " ORDER BY nom";
		} else {
			$sql = "SELECT * FROM transport_categorie ORDER BY nom";
		}
		
		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);
		
		$html_code = '';
		
		//charge le help si existant
		if (get_file_help_path(__FILE__, $action)) {
			//charge le lien pour afficher l'aide
			$html_code .= show_help_link();
		}
		
		$html_code .= '<form id="transport_categorie_seach_list"  action="" method="post">';
			$html_code .= '<p>';
				$html_code .= '<input type="text" id="search" name="search" />';
				
				$html_code .= add_FormElement_input('hidden', 'form', '', 'search');
				$html_code .= add_FormElement_input('hidden', 'module', '', 'transport_categorie');
				$html_code .= add_FormElement_input('hidden', 'sub_module', '', '""');
				$html_code .= add_FormElement_input('hidden', 'action', '', 'list');
				
				$html_code .= '<input type="submit" value="Soumettre" />';
				
			$html_code .= '</p>';
			
			$html_code .= '<p>';
				$html_code .= '<a href="?module=transport_categorie&action=add">Nouvelle catégorie</a>';
			$html_code .= '</p>';
		$html_code .= '</form>';
		
		
		$html_code .= '<table id="list_all_transport_categorie">';
		$html_code .= '<thead>';
			$html_code .= '<tr>';
				$html_code .= '<th>Nom</th>';
				$html_code .= '<th>Priorité</th>';
			$html_code .= '</tr>';
		$html_code .= '</thead>';
		
		
		
		$html_code .= '<tbody>';
		
			foreach ($result as $row) {
				
				$html_code .= '<tr>';
					$html_code .= '<td><a href="?module=transport_categorie&id=' . $row['id'] . '&action=view">' . $row['nom'] .'</a></td>';
					$html_code .= '<td>' . $row['priorite'] .'</td>';
				$html_code .= '</tr>';
			}

		$html_code .= '</tbody>';
		
	$html_code .= '</table>';

	$html_code .= load_help_file_if_necessary(get_file_help_path(__FILE__, $action));
	return $html_code;
	
	} //class.Transport_Categorie.form.list
	
	
} // class.Transport_Categorie

?>