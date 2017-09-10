<?php

require_once( str_replace ( '\\', '/', dirname(dirname(__FILE__))) . '/class.declaration.php' );
load_class_and_interface(array('Transport', 'Benevole_Disponibilite_Categorie', 'Transporteur', 'Periode_Journee', 'Trajet_Pre_Defini'));

/**
 * 
 * Modèle d'aide à la décision qui suggère des transports qui n'ont
 * pas encore trouvés preneur pour un chauffeur DONNE
 * 
 * Mode de fonctionnement
 * 1) le constructeur réceptionne :
 * 	a) le chauffeur sous la forme d'un id ou d'un objet transporteur
 * 	b) le nombre de suggestions à retourner
 * 	c) si google maps peut-être activer ou pas (TRUE/FALSE) pour le calcul
 * 		des distances ville->ville
 * 		Remarque : le système ne conseille que des transports proches
 * 			du chauffeur (5km). Pour accélerer le déroulement de la fonction,
 * 			on peut se contenter d'utiliser les données déjà présentes
 * 			dans la base de données pour les distances ville->ville
 * 2) liste les transports sans chauffeur
 * 3) on passe chaque transport de la liste à travers des filtres
 * 		pour savoir si le chauffeur pourrait faire l'affaire :
 * 		a) si le chauffeur est d'accord de transporter le passager
 * 		b) si le chauffeur n'est pas noté comme non disponible (par ex. vacances)
 * 		c) si le transport est de type Genève, s'assurer que le chauffeur
 * 			effectue des trajets d'au moins 22km
 * 		d) si le transport est de type Lausanne, s'assurer que le chauffeur
 * 			effectue des trajets d'au moins 40km
 * 		e) si le chauffeur est disponible au niveau de sa semaine standard
 * 			pour le jour de la semaine (1-7) ainsi que pour la période de
 * 			la journée (matin, après-midi ou soir)
 *		f) si le chauffeur n'a pas déjà un transport ce jour-là
 *			Remarque : capable de différencier 2 périodes différentes de la
 *				journée si celle-ci est précisée en second argument
 *				On peut donc suggérer un transport l'après-midi même si le
 *				chauffeur transporte déjà quelqu'un le matin
 * 		g) si le transport à lieu le matin, s'assure que le chauffeur 
 * 			n'est pas à la permanence
 * 		h) s'assure que le chauffeur se trouve au max. à 5km du point de 
 * 			départ du transport
 * 4) la liste des suggestions peut-être retournée depuis n'importe où dans 
 * 		le code à l'aide de la fonction public de class :
 * 			get_transports_potentiels
 * 				Remarque : les transports sont triés dans l'ordre 
 * 					chronologique
 * 
 * @author Gregory Chevalley <gregory.chevalley@gmail.com>
 *
 */
class GLMM {
	
	private $id_transporteur;
	private $id_benevole;
	private $transporteur;
	
		private $transporteur_ville;
		private $transporteur_pays;
	
	private $array_transport_sans_chauffeur = array();
	private $array_transports_potentiel_pour_chauffeur = array();
	
	
	function __construct($transporteur, $limit, $active_google_maps) {
		
		if ($transporteur instanceof  Transporteur) {
			$this->id_transporteur = $transporteur->get_id_transporteur();
			$this->transporteur = $transporteur;
		} elseif (is_numeric($transporteur) && Transporteur::id_exists($transporteur)) {
			$this->id_transporteur = $transporteur;
			$this->transporteur = new Transporteur($transporteur);
		} else {
			die();
		}
		
		//mount des parametres du chauffeur
		$tmp_transporteur_adresse = $this->transporteur->get_adresse();
		$this->transporteur_ville = $tmp_transporteur_adresse['ville'];
		$this->transporteur_pays = $tmp_transporteur_adresse['pays'];
		
		$this->mountTransportsSansChauffeur();
		
		foreach ($this->array_transport_sans_chauffeur as $transport_sans_chauffeur) {
			if (count($this->array_transports_potentiel_pour_chauffeur) < $limit) {
				
				//check contrainte beneficiaire
				if (!$this->transporteur->check_contrainte_beneficiaire($transport_sans_chauffeur->get_id_beneficiaire())) {
					
					//check si le transporteur est disponible a la date du transport
					if ($this->transporteur->check_disponibilite_date('transport', $transport_sans_chauffeur->get_date())) {
						
						//check distance type GENEVE
						if(($transport_sans_chauffeur->get_nbre_kilometres())/2 < 22 || $this->transporteur->check_transports_geneve() ) {
							
							//check distance type LAUSANNE
							if(($transport_sans_chauffeur->get_nbre_kilometres())/2 < 40 || $this->transporteur->check_transports_lausanne() ) {
								
								//check si le jour et l heure du transport sont dispo dans la semaine standard du chauffeur
								if ($this->transporteur->check_disponibilite_standard('transport', $transport_sans_chauffeur->get_jour_semaine(), $transport_sans_chauffeur->get_periode_journee())) {
									
									//le transporteur a-t-il deja un trajet
									if(!$this->transporteur->check_a_deja_un_transport($transport_sans_chauffeur->get_date(), $transport_sans_chauffeur->get_periode_journee())) {
										
										//le chauffeur se trouve-t-il a la permanence
										if (!$this->transporteur->check_est_a_la_permanence($transport_sans_chauffeur->get_date(), $transport_sans_chauffeur->get_periode_journee())) {
											
											
											//le chauffeur se trouve-t-il a proximite ?
											$point_depart = $transport_sans_chauffeur->get_point_depart();
											
											if (mb_strtoupper(stripAccents($point_depart['ville'])) == mb_strtoupper(stripAccents($this->transporteur_ville))) {
												$this->array_transports_potentiel_pour_chauffeur[] = $transport_sans_chauffeur;
											} else {
												
												$trajet_pre_defini = Trajet_Pre_Defini::find_combination($this->transporteur_ville, $point_depart['ville']);
												
												if ($trajet_pre_defini) {
													if ($trajet_pre_defini['distance'] <= 5) {
														$this->array_transports_potentiel_pour_chauffeur[] = $transport_sans_chauffeur;
													}
												} else {
													if ($active_google_maps === TRUE) {
														$distance = Trajet_Pre_Defini::download_distance_from_google_maps('', '', $this->transporteur_ville, $this->transporteur_pays, '', '', $point_depart['ville'], $point_depart['pays']);
														
														if ($distance <= 5) {
															$this->array_transports_potentiel_pour_chauffeur[] = $transport_sans_chauffeur;
														}
													}
												}
											}
										}
									}
								}
							}
						}		
					}
				}
			} else {
				break;
			}
		}
		
	} // class.GLMM.func.__construct
	
	
	private function mountTransportsSansChauffeur() {
		global $dbh;
		
		//transport sans chauffeur
		$sql = "SELECT transport.* ";
		$sql .= " FROM transport ";
		$sql .= " WHERE transport.id NOT IN ( ";
			
			$sql .= "SELECT transport.id";
			$sql .= " FROM transport INNER JOIN transport_transporteur ON transport.id = transport_transporteur.id_transport";
		
		$sql .= " )";
		
		$sql .= " AND transport.id_filiale=" . $_SESSION['filiale']['id'];
		$sql .= " AND transport.date_transport>=" . $dbh->quote(date('Y-m-d'));
		$sql .= " AND transport.is_annule=0 ";
		$sql .= " ORDER BY transport.date_transport, transport.heure_debut";
		
		$sth = $dbh->query($sql);
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);
		
		if (count($result) > 0) {
			foreach($result as $row) {
				$this->array_transport_sans_chauffeur[] = new Transport($row['id']);
			}
		}
	} // class.GLMM.func.mountTransportsSansChauffeur
	
	
	public function get_transports_potentiels() {
		return $this->array_transports_potentiel_pour_chauffeur;
	}
}

?>