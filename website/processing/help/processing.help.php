<?php

load_class_and_interface(array('Help', 'Topic'));

switch ($action) {
	case "list_topics":	
		Help::form('list_topics');
		break;
	case "show_topic":
		if (isset($_GET['filename'])) {
			$data_to_display = array();
			$data_to_display['filename']['value'] = $_GET['filename'];
			
			Help::form('show_topic', $data_to_display);
		} else {
			Help::form('list_topics');
		}
		
		break;
	default:
		Help::form('list_topics');
		break;
}

?>