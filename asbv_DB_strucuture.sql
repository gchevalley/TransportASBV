-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 13, 2011 at 11:26
-- Server version: 5.1.41
-- PHP Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `asbv_transport_local`
--
DROP DATABASE `asbv_transport_local`;
CREATE DATABASE `asbv_transport_local` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `asbv_transport_local`;

-- --------------------------------------------------------

--
-- Table structure for table `archive_transporteur_non_disponibilite_date`
--

DROP TABLE IF EXISTS `archive_transporteur_non_disponibilite_date`;
CREATE TABLE IF NOT EXISTS `archive_transporteur_non_disponibilite_date` (
  `id_transporteur` int(11) NOT NULL,
  `date_custom` date NOT NULL,
  `tag` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_transporteur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `beneficiaire`
--

DROP TABLE IF EXISTS `beneficiaire`;
CREATE TABLE IF NOT EXISTS `beneficiaire` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `titre` varchar(25) NOT NULL,
  `nom` varchar(45) NOT NULL,
  `prenom` varchar(45) NOT NULL,
  `adresse` varchar(75) NOT NULL,
  `adresse_complement` varchar(100) DEFAULT NULL,
  `npa` varchar(5) NOT NULL,
  `ville` varchar(45) NOT NULL,
  `pays` varchar(75) NOT NULL DEFAULT 'Suisse',
  `tel_fixe` varchar(25) DEFAULT NULL,
  `tel_mobile` varchar(25) DEFAULT NULL,
  `info_diverses` mediumtext NOT NULL,
  `toujours_2` tinyint(1) NOT NULL DEFAULT '0',
  `autre_adresse_facturation` tinyint(1) NOT NULL DEFAULT '0',
  `facturation_nom` varchar(45) NOT NULL,
  `facturation_prenom` varchar(45) NOT NULL,
  `facturation_adresse` varchar(75) NOT NULL,
  `facturation_adresse_complement` varchar(100) NOT NULL,
  `facturation_npa` varchar(5) NOT NULL,
  `facturation_ville` varchar(45) NOT NULL,
  `facturation_pays` varchar(75) NOT NULL,
  `insert_date` date NOT NULL,
  `insert_time` time NOT NULL,
  `insert_benevole_user` int(11) NOT NULL,
  `last_update_date` date NOT NULL,
  `last_update_time` time NOT NULL,
  `last_update_benevole_user` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_beneficiaire.insert_user_FROM_benevole` (`insert_benevole_user`),
  KEY `fk_beneficiaire.update_user_FROM_benevole` (`last_update_benevole_user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=161 ;

-- --------------------------------------------------------

--
-- Table structure for table `benevole`
--

DROP TABLE IF EXISTS `benevole`;
CREATE TABLE IF NOT EXISTS `benevole` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `super_login` varchar(45) NOT NULL,
  `super_password` varchar(30) NOT NULL,
  `titre` varchar(25) NOT NULL,
  `nom` varchar(45) DEFAULT NULL,
  `prenom` varchar(45) DEFAULT NULL,
  `adresse` varchar(100) DEFAULT NULL,
  `adresse_complement` varchar(100) DEFAULT NULL,
  `npa` varchar(5) DEFAULT NULL,
  `ville` varchar(75) DEFAULT NULL,
  `pays` varchar(75) NOT NULL DEFAULT 'Suisse',
  `tel_fixe` varchar(25) DEFAULT NULL,
  `tel_professionnel` varchar(25) DEFAULT NULL,
  `tel_mobile` varchar(25) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `iban` varchar(30) DEFAULT NULL,
  `ccp` varchar(20) DEFAULT NULL,
  `info_diverses` mediumtext NOT NULL,
  `is_super_admin` tinyint(1) NOT NULL DEFAULT '0',
  `insert_date` date NOT NULL,
  `insert_time` time NOT NULL,
  `insert_benevole_user` int(11) NOT NULL,
  `last_update_date` date NOT NULL,
  `last_update_time` time NOT NULL,
  `last_update_benevole_user` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_benevole.insert_user_FROM_benevole` (`insert_benevole_user`),
  KEY `fk_benevole.last_update_user_FROM_benevole` (`last_update_benevole_user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=61 ;

-- --------------------------------------------------------

--
-- Table structure for table `benevole_disponibilite_categorie`
--

DROP TABLE IF EXISTS `benevole_disponibilite_categorie`;
CREATE TABLE IF NOT EXISTS `benevole_disponibilite_categorie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(25) NOT NULL,
  `description` tinyblob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `benevole_disponibilite_standard`
--

DROP TABLE IF EXISTS `benevole_disponibilite_standard`;
CREATE TABLE IF NOT EXISTS `benevole_disponibilite_standard` (
  `id_benevole` int(11) NOT NULL,
  `id_categorie` int(11) NOT NULL COMMENT 'transport / permanence / appel',
  `id_jour_semaine` int(11) NOT NULL,
  `id_periode_journee` int(11) NOT NULL,
  `custom_heure_debut` time DEFAULT NULL,
  `custom_heure_fin` time DEFAULT NULL,
  `insert_date` date NOT NULL,
  `insert_time` time NOT NULL,
  `insert_user` int(11) NOT NULL,
  PRIMARY KEY (`id_benevole`,`id_jour_semaine`,`id_periode_journee`,`id_categorie`),
  KEY `fk_benevole_dispo_std.benevole_FROM_bnvl_prtcptn_filiale` (`id_benevole`),
  KEY `fk_benevole_dispo_std.jour_semaine_FROM_jour_semaine` (`id_jour_semaine`),
  KEY `fk_benevole_dispo_std.periode_journee_FROM_periode_journee` (`id_periode_journee`),
  KEY `fk_benevole_dispo_std.inser_user_FROM_bnvl_in_filiale` (`insert_user`),
  KEY `fk_benevole_dispo_std.categorie_FROM_bnvl_dispo_categorie` (`id_categorie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `benevole_non_disponibilite_date`
--

DROP TABLE IF EXISTS `benevole_non_disponibilite_date`;
CREATE TABLE IF NOT EXISTS `benevole_non_disponibilite_date` (
  `id_benevole` int(11) NOT NULL,
  `id_categorie` int(11) NOT NULL COMMENT 'transport / permanence / appel',
  `date_custom` date NOT NULL,
  `source` varchar(25) NOT NULL DEFAULT 'LOCAL' COMMENT 'local/distante\n',
  `need_exportation` tinyint(1) NOT NULL DEFAULT '1',
  `tag` varchar(100) DEFAULT NULL,
  `remarque` tinyblob,
  `insert_date` date NOT NULL,
  `insert_time` time NOT NULL,
  `insert_user` int(11) NOT NULL,
  PRIMARY KEY (`id_benevole`,`date_custom`,`id_categorie`),
  KEY `fk_bnvl_non_dispo_date.benevole_FROM_bnvl_in_filiale` (`id_benevole`),
  KEY `fk_bnvl_non_dispo_date.insert_user_FROM_bnvl_in_filiale` (`insert_user`),
  KEY `fk_bnvl_non_dispo_date.categorie_FROM_bnvl_dispo_categorie` (`id_categorie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `benevole_participation_filiale`
--

DROP TABLE IF EXISTS `benevole_participation_filiale`;
CREATE TABLE IF NOT EXISTS `benevole_participation_filiale` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `filiale_login` varchar(45) NOT NULL,
  `id_benevole` int(11) NOT NULL,
  `id_filiale` int(11) NOT NULL,
  `is_permanencier` tinyint(1) NOT NULL DEFAULT '0',
  `is_transporteur` tinyint(1) NOT NULL DEFAULT '0',
  `do_transports_locaux` tinyint(1) NOT NULL DEFAULT '0',
  `do_transports_geneve` tinyint(1) NOT NULL DEFAULT '0',
  `do_transports_lausanne` tinyint(1) NOT NULL DEFAULT '0',
  `do_transports_holidays` tinyint(1) NOT NULL DEFAULT '0',
  `is_administrateur_filiale` tinyint(1) NOT NULL DEFAULT '0',
  `has_external_login` tinyint(1) NOT NULL DEFAULT '0',
  `insert_date` date NOT NULL,
  `insert_time` time NOT NULL,
  `insert_benevole_user` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_benevole_participation_filiale.benevole_FROM_benevole` (`id_benevole`),
  KEY `fk_benevole_participation_filiale.filiale_FROM_filiale` (`id_filiale`),
  KEY `fk_benevole_participation_filiale.insert_user_FROM_benevole` (`insert_benevole_user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=60 ;

-- --------------------------------------------------------

--
-- Table structure for table `contrainte_transporteur_beneficiaire`
--

DROP TABLE IF EXISTS `contrainte_transporteur_beneficiaire`;
CREATE TABLE IF NOT EXISTS `contrainte_transporteur_beneficiaire` (
  `id_transporteur` int(11) NOT NULL,
  `id_beneficiaire` int(11) NOT NULL,
  PRIMARY KEY (`id_transporteur`,`id_beneficiaire`),
  KEY `fk.cnstnt_transporteur_FROM_bnvl_in_filiale` (`id_transporteur`),
  KEY `fk.cnstrnt_beneficiaire_FROM_beneficiaire` (`id_beneficiaire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `direction`
--

DROP TABLE IF EXISTS `direction`;
CREATE TABLE IF NOT EXISTS `direction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `depart_adresse` varchar(75) NOT NULL,
  `depart_npa` varchar(5) NOT NULL,
  `depart_ville` varchar(55) NOT NULL,
  `arrivee_adresse` varchar(75) NOT NULL,
  `arrivee_npa` varchar(5) NOT NULL,
  `arrivee_ville` varchar(55) NOT NULL,
  `nbre_kilometres` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=91 ;

-- --------------------------------------------------------

--
-- Table structure for table `filiale`
--

DROP TABLE IF EXISTS `filiale`;
CREATE TABLE IF NOT EXISTS `filiale` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `adresse` varchar(100) DEFAULT NULL,
  `adresse_complement` varchar(100) DEFAULT NULL,
  `npa` varchar(5) DEFAULT NULL,
  `ville` varchar(75) DEFAULT NULL,
  `tel_permanence` varchar(25) DEFAULT NULL,
  `tel_fax` varchar(25) DEFAULT NULL,
  `email_permanence` varchar(45) NOT NULL,
  `glm_weights` mediumtext NOT NULL COMMENT 'json',
  `default_km_cost` double NOT NULL,
  `default_compensation_rate` double NOT NULL,
  `default_min_cost` double NOT NULL,
  `facturation_header_line_1` mediumtext NOT NULL,
  `facturation_footer_line_1` mediumtext NOT NULL,
  `facturation_footer_line_2` mediumtext NOT NULL,
  `insert_date` date NOT NULL,
  `insert_time` time NOT NULL,
  `insert_user` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_filiale.insert_user_FROM_benevole` (`insert_user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `geocode`
--

DROP TABLE IF EXISTS `geocode`;
CREATE TABLE IF NOT EXISTS `geocode` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adresse` varchar(75) DEFAULT NULL,
  `adresse_complement` varchar(75) DEFAULT NULL,
  `npa` varchar(5) NOT NULL,
  `ville` varchar(75) NOT NULL,
  `pays` varchar(75) NOT NULL DEFAULT 'Suisse',
  `lat` double DEFAULT '0',
  `lng` double DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=271 ;

-- --------------------------------------------------------

--
-- Table structure for table `jour_semaine`
--

DROP TABLE IF EXISTS `jour_semaine`;
CREATE TABLE IF NOT EXISTS `jour_semaine` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom_long` varchar(15) NOT NULL,
  `nom_court` varchar(15) NOT NULL,
  `numero_jour` int(2) NOT NULL,
  `is_weekend` tinyint(1) NOT NULL DEFAULT '0',
  `has_compensation` tinyint(1) NOT NULL DEFAULT '0',
  `compensation` double NOT NULL DEFAULT '100',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Table structure for table `lieu`
--

DROP TABLE IF EXISTS `lieu`;
CREATE TABLE IF NOT EXISTS `lieu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) DEFAULT NULL,
  `id_categorie` int(11) NOT NULL,
  `abreviation` varchar(45) DEFAULT NULL,
  `adresse` varchar(100) DEFAULT NULL,
  `adresse_complement` varchar(100) DEFAULT NULL,
  `npa` varchar(5) DEFAULT NULL,
  `ville` varchar(55) NOT NULL,
  `pays` varchar(75) NOT NULL DEFAULT 'Suisse',
  `tel_fixe` varchar(25) DEFAULT NULL,
  `tel_fax` varchar(25) DEFAULT NULL,
  `tel_mobile` varchar(25) DEFAULT NULL,
  `numero_important` tinyint(1) NOT NULL DEFAULT '0',
  `insert_date` date NOT NULL,
  `insert_time` time NOT NULL,
  `insert_benevole_user` int(11) NOT NULL,
  `last_update_date` date NOT NULL,
  `last_update_time` time NOT NULL,
  `last_update_benevole_user` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_lieu.insert_user_FROM_benevole` (`insert_benevole_user`),
  KEY `fk_lieu.categorie_FROM_lieu_categorie` (`id_categorie`),
  KEY `fk_lieu.update_user_FROM_benevole` (`last_update_benevole_user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=82 ;

-- --------------------------------------------------------

--
-- Table structure for table `lieu_categorie`
--

DROP TABLE IF EXISTS `lieu_categorie`;
CREATE TABLE IF NOT EXISTS `lieu_categorie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categorie` varchar(45) NOT NULL,
  `description` tinyblob,
  `priorite` int(3) NOT NULL COMMENT 'notée sur 5\n',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Table structure for table `periode_journee`
--

DROP TABLE IF EXISTS `periode_journee`;
CREATE TABLE IF NOT EXISTS `periode_journee` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `periode` varchar(45) NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `has_compensation` tinyint(1) NOT NULL DEFAULT '0',
  `compensation` double NOT NULL DEFAULT '100',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `permanence`
--

DROP TABLE IF EXISTS `permanence`;
CREATE TABLE IF NOT EXISTS `permanence` (
  `id_filiale` int(11) NOT NULL,
  `id_permanencier` int(11) NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`date`,`id_filiale`,`id_permanencier`),
  KEY `fk_permanence.filiale_FROM_filiale` (`id_filiale`),
  KEY `fk_permanence.permanencier_FROM_bnvl_in_filiale` (`id_permanencier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `repondant`
--

DROP TABLE IF EXISTS `repondant`;
CREATE TABLE IF NOT EXISTS `repondant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_beneficiaire` int(11) NOT NULL,
  `lien_beneficiaire` varchar(45) NOT NULL,
  `id_categorie` int(11) NOT NULL DEFAULT '1',
  `ref_external` int(11) DEFAULT NULL,
  `nom` varchar(45) DEFAULT NULL,
  `prenom` varchar(45) DEFAULT NULL,
  `tel_fixe` varchar(25) DEFAULT NULL,
  `tel_mobile` varchar(25) DEFAULT NULL,
  `adresse` varchar(75) DEFAULT NULL,
  `adresse_complement` varchar(100) DEFAULT NULL,
  `npa` varchar(5) DEFAULT NULL,
  `ville` varchar(55) DEFAULT NULL,
  `insert_date` date NOT NULL,
  `insert_time` time NOT NULL,
  `insert_benevole_user` int(11) NOT NULL,
  `last_update_date` date NOT NULL,
  `last_update_time` time NOT NULL,
  `last_update_benevole_user` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_repondant.beneficiaire_FROM_beneficiaire` (`id_beneficiaire`),
  KEY `fk_repondant.insert_user_FROM_benevole` (`insert_benevole_user`),
  KEY `fk_repondant.last_update_user_FROM_benevole` (`last_update_benevole_user`),
  KEY `fk_repondant.id_categorie_FROM_repondant_cat` (`id_categorie`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=25 ;

-- --------------------------------------------------------

--
-- Table structure for table `repondant_categorie`
--

DROP TABLE IF EXISTS `repondant_categorie`;
CREATE TABLE IF NOT EXISTS `repondant_categorie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(45) NOT NULL,
  `auto_mount` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Table structure for table `trajet_pre_defini`
--

DROP TABLE IF EXISTS `trajet_pre_defini`;
CREATE TABLE IF NOT EXISTS `trajet_pre_defini` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lieu_1` varchar(45) NOT NULL,
  `lieu_2` varchar(45) NOT NULL,
  `distance` double NOT NULL,
  `distance_google_maps` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=310 ;

-- --------------------------------------------------------

--
-- Table structure for table `transport`
--

DROP TABLE IF EXISTS `transport`;
CREATE TABLE IF NOT EXISTS `transport` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_beneficiaire` int(11) NOT NULL,
  `id_filiale` int(11) NOT NULL,
  `id_categorie` int(11) DEFAULT NULL COMMENT 'consultation / loisir etc.\n',
  `date_transport` date NOT NULL,
  `heure_debut` time NOT NULL,
  `duree_approximative` double DEFAULT NULL,
  `id_type_calcul_distance` int(11) NOT NULL,
  `point_depart` mediumtext NOT NULL COMMENT 'serialize array',
  `point_arrivee` mediumtext NOT NULL COMMENT 'serialize array',
  `nbre_kilometres` double DEFAULT NULL,
  `aller_retour` tinyint(1) NOT NULL DEFAULT '1',
  `cout_trajet` double DEFAULT NULL,
  `cout_variable` double NOT NULL DEFAULT '0',
  `taux_remboursement_transporteur` double NOT NULL DEFAULT '100',
  `info_diverses` mediumtext NOT NULL,
  `is_cloture` tinyint(1) NOT NULL DEFAULT '0',
  `is_archive` tinyint(1) NOT NULL DEFAULT '0',
  `is_annule` tinyint(1) NOT NULL DEFAULT '0',
  `raison_annulation` mediumtext,
  `insert_date` date NOT NULL,
  `insert_time` time NOT NULL,
  `insert_user` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_transport.beneficiaire_FROM_beneficiaire` (`id_beneficiaire`),
  KEY `fk_trnsprt.typ_calc_dist_FROM_trnsprt_calc_dist` (`id_type_calcul_distance`),
  KEY `fk_trnsprt.categorie_FROM_trnsport_cat` (`id_categorie`),
  KEY `fk_transport.insert_user_FROM_bnvl_in_filiale` (`insert_user`),
  KEY `fk_transport.filiale_FROM_filiale` (`id_filiale`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=855 ;

-- --------------------------------------------------------

--
-- Table structure for table `transport_categorie`
--

DROP TABLE IF EXISTS `transport_categorie`;
CREATE TABLE IF NOT EXISTS `transport_categorie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(45) NOT NULL,
  `priorite` int(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `transport_log`
--

DROP TABLE IF EXISTS `transport_log`;
CREATE TABLE IF NOT EXISTS `transport_log` (
  `id_transport` int(11) NOT NULL,
  `update_time` time NOT NULL,
  `update_date` date NOT NULL,
  `update_id_permanencier` int(11) NOT NULL,
  `type_operation` varchar(15) NOT NULL COMMENT 'insert/edit',
  `donnee_concernee` varchar(45) NOT NULL COMMENT 'transport/transporteur',
  `nouvelle_valeur` varchar(45) NOT NULL,
  `ancienne_valeur` varchar(45) NOT NULL,
  PRIMARY KEY (`id_transport`),
  KEY `fk_transport_log.transport_FROM_transport` (`id_transport`),
  KEY `fk_transport_log.permanencier_FROM_bnvl_in_filiale` (`update_id_permanencier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `transport_transporteur`
--

DROP TABLE IF EXISTS `transport_transporteur`;
CREATE TABLE IF NOT EXISTS `transport_transporteur` (
  `id_transport` int(11) NOT NULL,
  `id_transporteur` int(11) NOT NULL,
  `insert_date` date NOT NULL,
  `insert_time` time NOT NULL,
  `insert_user` int(11) NOT NULL,
  PRIMARY KEY (`id_transport`,`id_transporteur`),
  KEY `fk_transport_transporteur.transport_FROM_transport` (`id_transport`),
  KEY `fk_transport_transporteur.transporteur_FROM_bnvl_in_filiale` (`id_transporteur`),
  KEY `fk_transport_transporteur.insert_user_FROM_bnvl_in_filiale` (`insert_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `transport_type_calcul_distance`
--

DROP TABLE IF EXISTS `transport_type_calcul_distance`;
CREATE TABLE IF NOT EXISTS `transport_type_calcul_distance` (
  `id` int(11) NOT NULL,
  `nom` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `beneficiaire`
--
ALTER TABLE `beneficiaire`
  ADD CONSTRAINT `fk_beneficiaire.insert_user_FROM_benevole` FOREIGN KEY (`insert_benevole_user`) REFERENCES `benevole` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_beneficiaire.update_user_FROM_benevole` FOREIGN KEY (`last_update_benevole_user`) REFERENCES `benevole` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `benevole`
--
ALTER TABLE `benevole`
  ADD CONSTRAINT `fk_benevole.insert_user_FROM_benevole` FOREIGN KEY (`insert_benevole_user`) REFERENCES `benevole` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_benevole.last_update_user_FROM_benevole` FOREIGN KEY (`last_update_benevole_user`) REFERENCES `benevole` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `benevole_disponibilite_standard`
--
ALTER TABLE `benevole_disponibilite_standard`
  ADD CONSTRAINT `fk_benevole_dispo_std.benevole_FROM_bnvl_prtcptn_filiale` FOREIGN KEY (`id_benevole`) REFERENCES `benevole_participation_filiale` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_benevole_dispo_std.categorie_FROM_bnvl_dispo_categorie` FOREIGN KEY (`id_categorie`) REFERENCES `benevole_disponibilite_categorie` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_benevole_dispo_std.inser_user_FROM_bnvl_in_filiale` FOREIGN KEY (`insert_user`) REFERENCES `benevole_participation_filiale` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_benevole_dispo_std.jour_semaine_FROM_jour_semaine` FOREIGN KEY (`id_jour_semaine`) REFERENCES `jour_semaine` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_benevole_dispo_std.periode_journee_FROM_periode_journee` FOREIGN KEY (`id_periode_journee`) REFERENCES `periode_journee` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `benevole_non_disponibilite_date`
--
ALTER TABLE `benevole_non_disponibilite_date`
  ADD CONSTRAINT `fk_bnvl_non_dispo_date.benevole_FROM_bnvl_in_filiale` FOREIGN KEY (`id_benevole`) REFERENCES `benevole_participation_filiale` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_bnvl_non_dispo_date.categorie_FROM_bnvl_dispo_categorie` FOREIGN KEY (`id_categorie`) REFERENCES `benevole_disponibilite_categorie` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_bnvl_non_dispo_date.insert_user_FROM_bnvl_in_filiale` FOREIGN KEY (`insert_user`) REFERENCES `benevole_participation_filiale` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `benevole_participation_filiale`
--
ALTER TABLE `benevole_participation_filiale`
  ADD CONSTRAINT `fk_benevole_participation_filiale.benevole_FROM_benevole` FOREIGN KEY (`id_benevole`) REFERENCES `benevole` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_benevole_participation_filiale.filiale_FROM_filiale` FOREIGN KEY (`id_filiale`) REFERENCES `filiale` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_benevole_participation_filiale.insert_user_FROM_benevole` FOREIGN KEY (`insert_benevole_user`) REFERENCES `benevole` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `contrainte_transporteur_beneficiaire`
--
ALTER TABLE `contrainte_transporteur_beneficiaire`
  ADD CONSTRAINT `fk.cnstnt_transporteur_FROM_bnvl_in_filiale` FOREIGN KEY (`id_transporteur`) REFERENCES `benevole_participation_filiale` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk.cnstrnt_beneficiaire_FROM_beneficiaire` FOREIGN KEY (`id_beneficiaire`) REFERENCES `beneficiaire` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `filiale`
--
ALTER TABLE `filiale`
  ADD CONSTRAINT `fk_filiale.insert_user_FROM_benevole` FOREIGN KEY (`insert_user`) REFERENCES `benevole` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `lieu`
--
ALTER TABLE `lieu`
  ADD CONSTRAINT `fk_lieu.categorie_FROM_lieu_categorie` FOREIGN KEY (`id_categorie`) REFERENCES `lieu_categorie` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_lieu.insert_user_FROM_benevole` FOREIGN KEY (`insert_benevole_user`) REFERENCES `benevole` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_lieu.update_user_FROM_benevole` FOREIGN KEY (`last_update_benevole_user`) REFERENCES `benevole` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `permanence`
--
ALTER TABLE `permanence`
  ADD CONSTRAINT `fk_permanence.filiale_FROM_filiale` FOREIGN KEY (`id_filiale`) REFERENCES `filiale` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_permanence.permanencier_FROM_bnvl_in_filiale` FOREIGN KEY (`id_permanencier`) REFERENCES `benevole_participation_filiale` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `repondant`
--
ALTER TABLE `repondant`
  ADD CONSTRAINT `fk_repondant.beneficiaire_FROM_beneficiaire` FOREIGN KEY (`id_beneficiaire`) REFERENCES `beneficiaire` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_repondant.id_categorie_FROM_repondant_cat` FOREIGN KEY (`id_categorie`) REFERENCES `repondant_categorie` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_repondant.insert_user_FROM_benevole` FOREIGN KEY (`insert_benevole_user`) REFERENCES `benevole` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_repondant.last_update_user_FROM_benevole` FOREIGN KEY (`last_update_benevole_user`) REFERENCES `benevole` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `transport`
--
ALTER TABLE `transport`
  ADD CONSTRAINT `fk_transport.beneficiaire_FROM_beneficiaire` FOREIGN KEY (`id_beneficiaire`) REFERENCES `beneficiaire` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_transport.filiale_FROM_filiale` FOREIGN KEY (`id_filiale`) REFERENCES `filiale` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_transport.insert_user_FROM_bnvl_in_filiale` FOREIGN KEY (`insert_user`) REFERENCES `benevole_participation_filiale` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_trnsprt.categorie_FROM_trnsport_cat` FOREIGN KEY (`id_categorie`) REFERENCES `transport_categorie` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_trnsprt.typ_calc_dist_FROM_trnsprt_calc_dist` FOREIGN KEY (`id_type_calcul_distance`) REFERENCES `transport_type_calcul_distance` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `transport_log`
--
ALTER TABLE `transport_log`
  ADD CONSTRAINT `fk_transport_log.permanencier_FROM_bnvl_in_filiale` FOREIGN KEY (`update_id_permanencier`) REFERENCES `benevole_participation_filiale` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_transport_log.transport_FROM_transport` FOREIGN KEY (`id_transport`) REFERENCES `transport` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `transport_transporteur`
--
ALTER TABLE `transport_transporteur`
  ADD CONSTRAINT `fk_transport_transporteur.insert_user_FROM_bnvl_in_filiale` FOREIGN KEY (`insert_user`) REFERENCES `benevole_participation_filiale` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_transport_transporteur.transporteur_FROM_bnvl_in_filiale` FOREIGN KEY (`id_transporteur`) REFERENCES `benevole_participation_filiale` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_transport_transporteur.transport_FROM_transport` FOREIGN KEY (`id_transport`) REFERENCES `transport` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
