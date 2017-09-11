<?php

/**
 * CHECKLIST
 * Welcome dashboard
 * Tranport: new, new with errors, edit
 * Passager (Beneficiaire): new, new with errors, edit
 * Benevole: new, new with errors, edit
*/


/**
 * CHANGE LOG
 *
 * 2017-09
 * bascule de la gestion du code source sur github
 * integration dans le package git de tcpdf et phpxls
 * allocate locally more memory for the backup procedure
 * -> PHP 5.6
 * db_pdo: utf8
 * preporocessing: re-ecriture des fichiers pour mieux traiter les variables non definies
 * preprocessing: 2 branches: add & new au lieu d une
 * forms addnew/edit: re-ecriture des forms pour mieux traiter les variables non definies
 * db_structure: ALTER TABLE `beneficiaire` ADD `is_active` TINYINT(1) NOT NULL DEFAULT '1' AFTER `id`;
 * transport: ne plus proposer les passagers flaggue inactifs
 * (undetected bug) add hidden field for unchecked checkbox
 * passager: separer par premiere liste, ne plus afficher une liste integrale
 *
 *
 *
 * 2011-01-21
 * 	ajout d'une fonction d'arrondi (par ex: à 10cts)
 *
 *
 * 2011-01-20
 * 	rajout d'une waiting box lors du login
 *
 *
 * 2011-01-19
 * 	affichage, si existe, du répondant dans la suggestion de chauffeur
 * 	split des bénévoles actifs / passifs
 *
 *
 * 2010-01-18
 * 	ajout d'une fonction de reinit de password depuis la page Filiale->Admin
 * 	la fonction Benevole::changeLocalPassword ne contrôle plus l'actual password si elle est appelée par un bénévole de type admin/super-admin
 * 	il est possible de se logguer en rajoutant des espaces dans son login par ex ('g lagaffe' est traité comme 'glagaffe')
 * 	lors du login, wash des anciennes dates de la table benevole_non_disponibilite_date
 * 	ajout d'une 2 procédures de maintenance qui agissent directement sur la base de données sans passer par les classes :
 * 		1. suppression d'un bénéficiaire ainsi que de toutes ses différentes relations (transports, repondants, contraintes etc.)
 * 		2. archivage des transports jusqu'à une certaine date
 *
 * 2011-01-17
 * 	correction du bug d'auth qui entrainait un hang d'apache, provenant d'un bug de la fonction crypt sous Windows. La fonction de hashage est remplacée par md5 et les pass sont remplacés dans la DB
 *
 *
 * 2011-01-14
 * 	ébauche du système de mode d'emploi online, déclarer les entrées dans :
 * 		admin/help/topics.list.php
 * 	ajout de détails concernant les transports réguliers, pré-remplissage automatique du form d'add transport avec les info du dernier transport entré
 * 	ajout d'un champ admin backup email
 * 	correction d'un bug qui affichait le footer a chaque utilisation d'un fenetre dialog au premier plan
 *
 *
 * 2011-01-13
 * 	auto-backup au login (même si la fonction n'est pas dispo pour l'utilisateur dans le module de Filiale->Sauvegarde)
 * 	implémentation de la class Emergency, refresh toutes les 20min
 * 	autoload help files based on name from /doc/help/
 *
 *
 * 2011-01-12
 * 	export xls des transports de la page d'accueil dans /extract/transports_sans_chauffeur.xls & /extract/transports_avec_chauffeurs.xls
 * 	rajout d'un symbole de vacances si lors de la suggestion de chauffeurs, la personne est actuellement (date courante) non disponible
 *
 *
 * 2011-01-11
 * 	Ajout d'un champs abréviation pour l'objet lieu, utile à la facturation (Lieu::get_abreviation)
 * 	+ de détails dans les points de départ & arrivée dans la facturation
 */

echo '<small>';
	echo 'Système intégré de gestion de transports';
	echo ' | Version 0.9.8';
	echo ' | Dernière modification ' . date('d.m.Y H:i', filemtime(__FILE__));
	echo ' | Edition ASBV avec modèles d\'aide à la décision';
echo '</small>';
?>
