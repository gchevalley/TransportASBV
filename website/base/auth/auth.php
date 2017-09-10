<?php
	session_start();
	session_destroy();
	session_unset();
	setcookie('PHPSESSID','',time()-1);
	$_SESSION = array();

	require ('../../../admin/class.declaration.php');
	$load_needed_class_and_interface = load_class_and_interface(array('Filiale'));
	$array_filiale = Filiale::get_all_filiales();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<title>Transport ASBV</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<link rel="stylesheet" type="text/css" href="../../css/base.css" />

	<link rel="stylesheet" type="text/css" href="../../css/ui-lightness/jquery-ui-1.8.5.custom.css" />
	<link rel="stylesheet" type="text/css" href="../../css/MonthPicker/monthpicker.css" />

	<!--  jQuery -->
	<script type="text/javascript" src="../../js/jquery-1.4.2.min.js"></script>
	<script type="text/javascript" src="../../js/jquery-ui-1.8.5.custom.min.js"></script>

	<!--  datePicker -->
	<script type="text/javascript" src="../../js/jquery.ui.datepicker-fr-CH.js"></script>

	<!--  monthPicker -->
	<script type="text/javascript" src="../../js/MonthPicker/monthpicker.min.js"></script>


	<script type="text/javascript" src="../../js/script.js" charset="utf-8"></script>


</head>

<body>

	<div id="container">

		<?php
			//charge le help si existant
			if ( isset($action) ) {
				if (get_file_help_path(__FILE__, $action)) {
					//charge le lien pour afficher l'aide
					echo show_help_link(FALSE);
				}
			}
		?>

		<form id="auth_system" action="verification.php" method="post">
			<p>
				<label for="login">Login</label>
				<input class="disableAutoComplete" type="text" name="login" />
			</p>

			<p>
				<label for="password">Mot de passe</label>
				<input type="password" name="password" />
			</p>

			<p>
				<label for = "filiale">Filiale</label>
				<select id="filiale" name="filiale">
					<?php
						$i=0;

						foreach ($array_filiale as $key => $value) {
							if ($i==0) {
								echo '<option value="' . $value['nom'] . '" selected="selected">' . $value['nom'] . '</option>' . "\n";
							} else {
								echo '<option value="' . $value['nom'] . '">' . $value['nom'] . '</option>' . "\n";
							}
							$i++;
						}

					?>
				</select>
			</p>

			<p>
				<input id="submit_login" type="submit" value="S'identifier" />
			</p>
		</form>

		<?php
			if ( isset($action) ) {
				echo load_help_file_if_necessary(get_file_help_path(__FILE__, $action));
			}
		?>
	</div>

	<div id="div_dialog" class="hide">
		<p>
			<img src="../../img/waiting.gif"/>Chargement du système en cours, veuillez patienter
		</p>
	</div>
</body>
</html>
