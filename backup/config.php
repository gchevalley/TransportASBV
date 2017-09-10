<?php

global $cfg;


// Which directory/files to backup ( directory should have trailing slash ) 
$configBackup = array('../');

// which directories to skip while backup 
$configSkip   = array('../.settings', '../backup/backup_zip', '../backup/restore_zip', '../facturation/', '../remboursement', '../doc/permanencier', '../doc/dev', '../config/tcpdf', '../config/phpxls', '../.buildpath', '../.project', '../asbv_transport_local-sqldump.sql', '../website/img/help');  

// Put backups in which directory 
$configBackupDir = 'backup_zip/';

//  Databses you wish to backup , can be many ( tables array if contains table names only those tables will be backed up ) 
$configBackupDB[] = array('server'=>$cfg['DATABASE']['host'], 'username'=>$cfg['DATABASE']['user'], 'password'=>$cfg['DATABASE']['password'], 'database'=>$cfg['DATABASE']['database'], 'tables'=>array());


// Put in a email ID if you want the backup emailed 
load_class_and_interface(array('Filiale'));
$tmp_filiale = new Filiale($_SESSION['filiale']['id']);

$configEmailFrom = $tmp_filiale->get_email_permanence();

$email_backup_admin_supp = $tmp_filiale->get_email_backup();
if ($email_backup_admin_supp) {
	//$configEmail = array($configEmailFrom, $email_backup_admin_supp);
	$configEmail = array($email_backup_admin_supp); // 2016-02-26 pour eviter l envoi dans l inbox du server mail de l avasad asbv.nyon@avasad.ch
} else {
	$configEmail = array($configEmailFrom);
}