<?php

require_once( str_replace ( '\\', '/', dirname(dirname(__FILE__))) . '/class.declaration.php' );

/**
 * 
 * Class qui produit le code HTML pour le mode d'emploi online
 * 
 * @author Gregory Chevalley
 *
 */
class Help {
	
	
	public static function form($action, $data_to_display='') {
		
		switch ($action) {
			case "list_topics":
				echo Help::form_list_topics($action);
				break;
			case "show_topic":
				echo Help::form_show_topic($action, $data_to_display);
				break;
			default:
				echo Help::form_list_topics($action);
				break;
		}
	} // class.Help.func.form
	
	
	private static function form_list_topics($action, $data_to_display='') {
		load_class_and_interface(array('Topic'));
		global $cfg;
		$base_folder_help_files = $cfg['DIRECTORY']['help_files'];
		
		$list_topics = Topic::get_all_topics();
		
		
		$html_code = '';
		
		$html_code .= '<table>';
			$html_code .= '<thead>';
				$html_code .= '<tr>';
					$html_code .= '<th>Sujet</th>';
				$html_code .= '</tr>';
			$html_code .= '</thead>';
			
			$html_code .= '<tbody>';
				
				// affiche les liens
				$last_parent = '';
				foreach ($list_topics as $topic) {
						
					if ($last_parent != $topic->get_parent()) {
						$html_code .= '<tr>';
							$html_code .= '<th>';
								$html_code .= $topic->get_parent();
								$last_parent = $topic->get_parent();
							$html_code .= '</th>';
						$html_code .= '</tr>';
					}
						
					$html_code .= '<tr>';
					
						$html_code .= '<td>';
							$html_code .= '<a href="?module=help&amp;action=show_topic&amp;filename=' . $topic->get_filename() . '">';
								$html_code .= $topic->get_description();
							$html_code .= '</a>';
						$html_code .= '</td>';
						
					$html_code .= '</tr>';
				}

			$html_code .= '</tbody>';
		$html_code .= '</table>';
		
		
		return $html_code;
	} // class.Help.form.list_topics
	
	private static function form_show_topic($action, $data_to_display='') {
		global $cfg;
		$base_folder_help_files = $cfg['DIRECTORY']['help_files'];
		
		$html_code = '';
		
		if (isset($data_to_display['filename']['value'])) {
			if (file_exists('../' . $base_folder_help_files . $data_to_display['filename']['value'])) {
				$html_code .= load_help_file_if_necessary('../' . $base_folder_help_files . $data_to_display['filename']['value'], 'page');
			} else {
				$html_code .= Help::form('list_topics');
			}
		} else {
			$html_code .= Help::form('list_topics');
		}
		
		return $html_code;
	} // class.Help.form.show_topic
	
} // class.Help

?>