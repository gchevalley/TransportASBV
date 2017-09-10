<?php
/*
 Liste des  pages modes d'emploi disponibles 

 Mises a jour :  2 Mars 2011
 Auteur       :  Michel Thevoz 
 Fichier      :  C:/ASBV/TransportASBV/admin/help/topics.list.php 
 URL          :  http://localhost/TransportASBV/website/index.php?module=help&action=list_topic
*/

Topic::$list_topics[] = new Topic('website_processing_dashboard.html', 'Page d\'Accueil', 'Acceuil & Divers');


Topic::$list_topics[] = new Topic('Help_Nouveau_Transport.html', 'Ajouter un nouveau transport', 'Transport');
Topic::$list_topics[] = new Topic('Help_Liste_Transport.html', 'Liste des transports', 'Transport');
Topic::$list_topics[] = new Topic('Help_Archives_Transport.html', 'Archives des transports', 'Transport');

Topic::$list_topics[] = new Topic('Help_Nouveau_Lieu.html', 'Ajouter un nouveau lieu', 'Lieux');
Topic::$list_topics[] = new Topic('Help_Liste_Lieu.html', 'Liste des lieux', 'Lieux');

 
Topic::$list_topics[] = new Topic('Help_Nouveau_Passager.html', 'Ajouter un nouveau passager', 'Passagers');
Topic::$list_topics[] = new Topic('Help_Liste_Passager.html', 'Liste des passagers', 'Passagers');
Topic::$list_topics[] = new Topic('Help_Tarifs_Passager.html', 'Calcul des tarifs passagers', 'Passagers');

Topic::$list_topics[] = new Topic('Help_Nouveau_Benevole.html', 'Ajouter un nouveau chauffeur bénévole', 'Bénévoles');
Topic::$list_topics[] = new Topic('Help_Liste_Benevole.html', 'Liste des chauffeurs bénévoles', 'Bénévoles');

Topic::$list_topics[] = new Topic('Help_Permanence_Filiale.html', 'Affichage des permanenciers', 'Filiale');
Topic::$list_topics[] = new Topic('Help_Facturation_Filiale.html', 'Facturation des transports', 'Filiale');
Topic::$list_topics[] = new Topic('Help_Backup_procedures.html', 'Procedures de sauvetage et restauration', 'Filiale');
Topic::$list_topics[] = new Topic('Help_Editer_Filiale.html', 'Modification des paramètres de la filiale', 'Filiale');
Topic::$list_topics[] = new Topic('Help_Admin_Filiale.html', 'Administration de la filiale', 'Filiale');
Topic::$list_topics[] = new Topic('Help_Ajouter_Filiale.html', 'Ajouter une nouvelle filiale', 'Filiale');

Topic::$list_topics[] = new Topic('Help_Suggestion_Chauffeur_A.html', 'La demande de transport', 'Suggestion de chauffeur');
Topic::$list_topics[] = new Topic('Help_Suggestion_Chauffeur_B.html', 'Les Filtres ', 'Suggestion de chauffeur');
Topic::$list_topics[] = new Topic('Help_Suggestion_Chauffeur_C.html', 'Extraction et Normalisation', 'Suggestion de chauffeur');
Topic::$list_topics[] = new Topic('Help_Suggestion_Chauffeur_D.html', 'Moyennes pondérées', 'Suggestion de chauffeur');


Topic::$list_topics[] = new Topic('Help_FAQ.html', 'Questions Fréquentes', 'Recommendations et conseils');
Topic::$list_topics[] = new Topic('Help_startup.html', 'Démarrage du système', 'Recommendations et conseils');
Topic::$list_topics[] = new Topic('Help_shutdown.html', 'Arrêt du système', 'Recommendations et conseils');
Topic::$list_topics[] = new Topic('Help_email.html', 'Accès au courrier électronique', 'Recommendations et conseils');
Topic::$list_topics[] = new Topic('Help_internet.html', 'Controle de la connection Internet', 'Recommendations et conseils');
Topic::$list_topics[] = new Topic('Help_support.html', 'Contact pour le support ', 'Recommendations et conseils');

?>