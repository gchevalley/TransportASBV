<?php


function clean_variables($variables_to_clean) {
	$count_error = 0;
	$found_error_in_datas = FALSE;
	$array_field_error = array();
	
	if (!is_array($variables_to_clean)) {
		return FALSE;
		die();
	}
	
	foreach ($variables_to_clean as $index => &$variable) {
		if (isset($variable['value']) && $variable['value'] != '') {
			$variable['value'] = clean_variable($variable['value'], $variable['type'], $variable['sub_type']);
		} elseif ($variable['type']=='bool') {
			$variable['value'] = 0;
		} else {
			if (isset($variable['option']['required'])) {
				$found_error_in_datas = TRUE;
				$array_field_error[] = $index;
				$count_error++;
				
				//destruction de l'entree problematique
				unset($variables_to_clean[$index]);
			}
		}
	}
	
	return array($variables_to_clean, $count_error, $array_field_error);
}



function clean_variable($value, $type, $sub_type='') {
	switch ($type) {
		case "text":
			switch($sub_type) {
				case "nom":
					return ucfirst($value);
					break;
				case "nombre":
					if (is_numeric($value)) {
						return '' . $value;
					}
					break;
				default:
					return $value;
					break;
			}
			break;
		case "int":
			if (is_numeric($value)) {
				return $value;
			}
			break;
		case "double":
			if (is_numeric($value)) {
				return (double) $value;
			} else {
				return 0;
			}
			break;
		case "bool":
			if (!isset($value)) {
				return 0;
			}
			
			if ($value == 1 || $value == TRUE || $value == 'checked') {
				return 1;
			} else {
				return 0;
			}
			break;
		case "tel":
			//$tmp_var = preg_replace('/[\\\/\. \(\)\-]/', '', $value);
			$tmp_var = preg_replace('/[^\d]/', '', $value);
			
			if (strlen($tmp_var) == 13) {
				return $tmp_var;
			} elseif (strlen($tmp_var) == 10) {
				return '0041' . substr($tmp_var, 1);
			} else {
				return $tmp_var;
			}
			
			break;
		case "id":
			if (is_numeric($value)) {
				return $value;
				break;
			}
			break;
		case "email":
			if (preg_match('/^[^@]+@[^@]+\.[^@]+$/', $value)) {
				return $value;
			} else {
				return FALSE;
			}
		case "object":
			if (is_object($value)) {
				return $value;
			} else {
				return FALSE;
			}
			break;
		case "array":
			if (is_array($value)) {
				return $value;
			} else {
				return FALSE;
			}
			break;
		case "date":
			if (is_date($value)) {
				return $value;
			}
			break;
		case "time":
			if (preg_match('/[0-9]{1,2}:[0-9]{1,2}/', $value)) {
				return $value;
			} else {
				return FALSE;
			}
			break;
		default:
			return $value;
			break;
	}
}



?>