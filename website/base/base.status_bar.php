<?php

	$now_date = date('d-m-Y');
	$now_time = date('H:i:s');
	
	if (isset($_POST['status_bar_message'])) {
		$message = $_POST['status_bar_message'];
		unset($_POST['status_bar_message']);
		
		if (isset($_POST['status_bar_level'])) {
			$status_bar_level = $_POST['status_bar_level'];
			unset($_POST['status_bar_level']);
		}
	} else {
		$message = "$now_date - $now_time";
		$status_bar_level = 'status_bar_level_normal';
	}
	
	echo '<p class="' . $status_bar_level . '">';
		echo $message;
	echo '</p>';
?>