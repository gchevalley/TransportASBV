<?php
/*******************************************************************************
 *                      PHP Backup Script
 *******************************************************************************
 *      Author:     Vikas Patial
 *      Email:      admin@ngcoders.com
 *      Website:    http://www.ngcoders.com
 *
 *      File:       paypal.php
 *      Version:    1.0.0
 *      Copyright:  (c) 2007 - Vikas Patial
 *                  You are free to use, distribute, and modify this software
 *                  under the terms of the GNU General Public License.  See the
 *                  included license.txt file.
 *
 *******************************************************************************
 *      v1.0.0 [04.10.2007] - Initial Version
 *
 *******************************************************************************
 *  DESCRIPTION:
 *
 *      NOTE: See www.ngcoders.com for the most recent version of this script
 *      and its usage.
 *
 *******************************************************************************
*/

require_once( str_replace ( '\\', '/', dirname(dirname(__FILE__))) . '/admin/class.declaration.php' );

function backup_website_and_db() {

	ini_set('memory_limit', '1024M');
	
	include('config.php');
	include('functions.php');

	#$backupName = "backup-".date('Y-m-d H-i-s').'.zip';
	$backupName = "backup-".date('Y-m-d H-i-s').'.txt';

	$createZip = new createZip;

	if (isset($configBackup) && is_array($configBackup) && count($configBackup)>0)
	{

	    // Lets backup any files or folders if any

	    foreach ($configBackup as $dir)
	    {
	    	if (substr($dir, 0, 3) == '../') {
	        	//ne pas reproduire le schema ../ pour eviter que le zip remonte seul l'arborescence
	    		$basename = '.';
	        } else {
	        	$basename = basename($dir);
	        }

	        // dir basename
	        if (is_file($dir))
	        {
	            $fileContents = file_get_contents($dir);
	            $createZip->addFile($fileContents,$basename);
	        }
	        else
	        {

	            $createZip->addDirectory($basename."/");

	            $files = directoryToArray($dir,true);

	            $files = array_reverse($files);

	            foreach ($files as $file)
	            {

	                $zipPath = explode($dir,$file);
	                $zipPath = $zipPath[1];

	                // skip any if required

	                $skip =  false;
	                foreach ($configSkip as $skipObject)
	                {
	                    if (strpos($file,$skipObject) === 0)
	                    {
	                        $skip = true;
	                        break;
	                    }
	                }

	                if ($skip) {
	                    continue;
	                }


	                if (is_dir($file))
	                {
	                    $createZip->addDirectory($basename."/".$zipPath);
	                }
	                else
	                {
	                    $fileContents = file_get_contents($file);
	                    $createZip->addFile($fileContents,$basename."/".$zipPath);
	                }
	            }
	        }

	    }

	}

	if (isset($configBackupDB) && is_array($configBackupDB) && count($configBackupDB)>0)
	{

	     foreach ($configBackupDB as $db)
	     {
	         $backup = new MySQL_Backup();
	         $backup->server   = $db['server'];
	         $backup->username = $db['username'];
	         $backup->password = $db['password'];
	         $backup->database = $db['database'];
	         $backup->tables   = $db['tables'];

	         $backup->backup_dir = $configBackupDir;

	         $sqldump = $backup->Execute(MSB_STRING,"",FALSE);

	         $createZip->addFile($sqldump,$basename . '/' . $db['database'].'-sqldump.sql');

	     }

	}

	if (!is_dir(dirname(__FILE__) . '/' . $configBackupDir)) {
		mkdir(dirname(__FILE__) . '/' . $configBackupDir);
	}


	$fileName = dirname(__FILE__) . '/' . $configBackupDir.$backupName;
	$fd = fopen ($fileName, "wb");
	$out = fwrite ($fd, $createZip -> getZippedfile());
	fclose ($fd);

	// Dump done now lets email the user

	if (isset($configEmail) && !empty($configEmail)) {
		//mailAttachment($fileName,$configEmail,'noreply@gmail.com','Backup Script','noreply@gmail.com','Backup - '.$backupName,"Backup file is attached");

		global $cfg;
		$load_needed_class_and_interface = load_class_and_interface(array('Rmail', 'PHPMailer', 'Permanencier', 'Filiale'));
		//$mail = new Rmail();
		//$mail->setFrom('Backup_System_ASBV <' . $configEmailFrom . '>');
		//$mail->setHTMLCharset('UTF-8');
		//$mail->setTextCharset('UTF-8');
		//$mail->setHeadCharset('UTF-8');
		//$mail->setSubject($backupName);
		//$mail->setPriority('high');
		//$mail->setText($backupName);

		$tmp_permanencier = new Permanencier($_SESSION['benevole']['id'], $_SESSION['filiale']['id']);
			$tmp_permanencier_nom = $tmp_permanencier->get_nom_complet();
		$tmp_filiale = new Filiale($_SESSION['filiale']['id']);

		$html_text_body = '<strong>' . $backupName . '</strong>';
		$html_text_body .= '<p>Send by : ' . $tmp_permanencier_nom['prenom'] . ' ' . $tmp_permanencier_nom['nom'] . '</p>';
		$html_text_body .= '<p>From : ' . $tmp_filiale->get_nom() . '</p>';

		//$mail->setHTML($html_text_body);

		//$mail->setReceipt($configEmailFrom);
		//$mail->addAttachment(new fileAttachment($fileName));
		//$result  = $mail->send($configEmail); //sous forme d'array

		$mail = new PHPMailer;
		$mail->IsSMTP();
		$mail->Host = $cfg['MAILSERVER']['ip'];
		$mail->From = $configEmailFrom;
		$mail->FromName = 'Backup_System_ASBV';

		foreach ($configEmail as $tmpmail) {
			$mail->AddAddress($tmpmail);
		}

		$mail->AddReplyTo($configEmail[0], 'Backup_System_ASBV');
		$mail->WordWrap = 50;
		$mail->IsHTML(true);
		$mail->Subject = utf8_decode($backupName);
		$mail->Body    = utf8_decode($html_text_body);
		$mail->AddAttachment($fileName);
		$result = $mail->Send();


		if ($result) {
			return array('backupName' => $backupName, 'configBackupDir' => $configBackupDir, 'list_mailling' => $configEmail);
		} else {
			return FALSE;
		}
	}
}
?>
