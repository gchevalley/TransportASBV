<?php

require_once( str_replace ( '\\', '/', dirname(dirname(__FILE__))) . '/class.declaration.php' );
load_class_and_interface(array('Transport', 'Transporteur', 'Filiale'));


class Emergency {
	private $level = 0; // 0 -> 10
		private $level_transports_sans_chauffeur = 0;
		private $level_transports_avec_chauffeurs = 0;
		private $level_chauffeurs_dispo = 0;
	
	
	function __construct() {
		global $dbh;
		
		$list_transport_sans_chauffeur = Transport::get_transports_sans_chauffeur();
		
		
		//extraction des différentes données brutes
		
			//prochain jours nbre transports sans chauffeur
			$matrix_brut['transports_sans_chauffeur_0d'] = 0;
			$matrix_brut['transports_sans_chauffeur_1d'] = 0;
			$matrix_brut['transports_sans_chauffeur_2d'] = 0;
			
			$ponderation_transports_sans_chauffeur[0] = 0.6;
			$ponderation_transports_sans_chauffeur[1] = 0.3;
			$ponderation_transports_sans_chauffeur[2] = 0.1;
			
			if (count($list_transport_sans_chauffeur) > 0 && $list_transport_sans_chauffeur !== FALSE) {
				foreach ($list_transport_sans_chauffeur as $row) {
					
					$str_date = strtotime($row->get_date());
					
					$delta_days = diff_date_without_weekend(date('d'), date('m'), date('Y'), date('d', $str_date), date('m', $str_date), date('Y', $str_date));
					
					if ($delta_days == 0) {
						$matrix_brut['transports_sans_chauffeur_0d']++;
					} elseif ($delta_days == 1) {
						$matrix_brut['transports_sans_chauffeur_1d']++;
					} elseif ($delta_days == 2) {
						$matrix_brut['transports_sans_chauffeur_2d']++;
					} else {
						break;
					}
				}
			}
			
			
		//prochain jour, nbre trajets deja attribués
		$list_transport_avec_chauffeur = Transport::get_transport_avec_chauffeurs();

		//base comparative, nbre transports moyen journalier tiré d'une stat mensuel
		$sql = "SELECT YEAR(transport.date_transport) as year, MONTH(transport.date_transport) as month, COUNT(transport.id) AS avg ";
		$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport ";
		$sql .= " WHERE transport.id_filiale=" . $_SESSION['filiale']['id'];
		$sql .= " AND transport.is_annule=0";
		$sql .= " AND transport.date_transport<" . $dbh->quote(date('Y-m-d', mktime(0,0,0, date('m'), 1, date('Y'))));
		$sql .= " GROUP BY YEAR(transport.date_transport), MONTH(transport.date_transport)";
		
		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);
		
		$sum_transport = 0;
		foreach ($result as $row) {
			$sum_transport += $row['avg'];
		}
		
		if (count($result) > 0) {
			$daily_average_transports = (int) ($sum_transport / (20*count($result)));
		} else {
			$daily_average_transports = 0;
		}
		
		
			$matrix_brut['transports_avec_chauffeurs_0d'] = 0;
			$matrix_brut['transports_avec_chauffeurs_1d'] = 0;
			$matrix_brut['transports_avec_chauffeurs_2d'] = 0;
			
			$ponderation_transports_avec_chauffeur[0] = 0.6;
			$ponderation_transports_avec_chauffeur[1] = 0.3;
			$ponderation_transports_avec_chauffeur[2] = 0.1;
			
			if (count($list_transport_avec_chauffeur) > 0 && $list_transport_avec_chauffeur !== FALSE) {
				
				foreach ($list_transport_avec_chauffeur as $row) {
					$str_date = strtotime($row->get_date());
					
					$delta_days = diff_date_without_weekend(date('d'), date('m'), date('Y'), date('d', $str_date), date('m', $str_date), date('Y', $str_date));
					
					if ($delta_days == 0) {
						$matrix_brut['transports_avec_chauffeurs_0d']++;
					} elseif ($delta_days == 1) {
						$matrix_brut['transports_avec_chauffeurs_1d']++;
					} elseif ($delta_days == 2) {
						$matrix_brut['transports_avec_chauffeurs_2d']++;
					} else {
						break;
					}
				}
			}
			
			
		//prochain jour, nbre chauffeurs encore dispo -> idée a implémenter : split long/short distance
		
		$tmp_filiale = new Filiale($_SESSION['filiale']['id']);
		$nbre_transporteurs = count($tmp_filiale->get_list_transporteur());
		
		$j=0;
		for ($i=0;$i<=5;$i++) {
			if ($j <= 2) {
				$tmp_date = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') + $i, date('Y')));
				
				if (date('N', $tmp_date) < 6) {
					$matrix_brut['transporteurs_dispo_' . $j . 'd'] = count(Transporteur::get_transporteur_disponible_date_periode($tmp_date));
					$j++;
				}
			} else {
				break;
			}
			
		}
		
		
		//notation
		for ($i=0;$i<=3;$i++) {
			$test =  $ponderation_transports_sans_chauffeur[$i] * $matrix_brut['transports_sans_chauffeur_' . $i . 'd'];
			$this->level_transports_sans_chauffeur += $ponderation_transports_sans_chauffeur[$i] * $matrix_brut['transports_sans_chauffeur_' . $i . 'd'];
			
			
			
			$test = $daily_average_transports - $matrix_brut['transports_avec_chauffeurs_' . $i . 'd'];
			
			if ($test > 2) {
				$test = 0;
			} elseif ($test >= 0) {
				$test = 0.4;
			} else {
				$test = 0.9;
			}
			
			$this->level_transports_avec_chauffeurs += $ponderation_transports_sans_chauffeur[$i] * $test;
			
			
			if ($nbre_transporteurs > 0) {
				$test = $matrix_brut['transporteurs_dispo_' . $i . 'd'] / $nbre_transporteurs;
			} else {
				$test = 0;
			}
			
			
			if ($test > 0.7) {
				$test = 0;
			} elseif ($test >= 0.6) {
				$test = 0.4;
			} else {
				$test = 0.9;
			}
			
			$this->level_chauffeurs_dispo += $ponderation_transports_sans_chauffeur[$i] * $test;
		}
		
		//ponderation
		$this->level = 0.4 * $this->level_transports_sans_chauffeur + 0.2 * $this->level_transports_avec_chauffeurs + 0.4 * $this->level_chauffeurs_dispo;
		
	} // class.Emergency.function.__construct
	
	
	public function get_level() {
		return $this->level;
	} // class.Emergency.function.get_level
	
	public function get_levels() {
		return array('level_transports_sans_chauffeur' => $this->level_transports_sans_chauffeur, 'level_transports_avec_chauffeur' => $this->level_transports_avec_chauffeurs, 'level_chauffeurs_disponibles' => $this->level_chauffeurs_dispo);
	}
	
	
	public static function get_img_name($level) {
		if ($level >= 0.6 ) {
			return 'flag_red.png';
		} elseif ($level > 0.2 ) {
			return 'flag_orange.png';
		} else {
			return 'flag_green.png';
		}	
	}
	
} // class.Emergency
?>