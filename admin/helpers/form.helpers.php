<?php

function add_argument_toFormElement($markup, $markup_type='', $argument_to_edit, $value) {
	if (!isnull($value)) {
		
	}
}


function add_FormElement_input($type, $id, $class='', $value, $option='') {
	$html_code = '<input ';
	
	// type
	$html_code .= 'type="' . $type . '" ';
	
	//id & name
	$html_code .= 'id="' . $id . '" ';
	$html_code .= 'name="' . $id . '" ';
				
	// class
	if ($class != '') {
		if (is_array($class)) {
			$html_code .= 'class="';
				
				foreach ($class as $row) {
					$html_code .= $row . ' ';
				}
				
			$html_code .= '"';
		} else {
			$html_code .= 'class="' . $class . '" ';
		}
	}
	
	
	switch($type) {
		
		case "text":
			//value
			if ($value != '') {
				$html_code .= 'value="' . $value . '" ';
			}
			break;
		
		case "hidden":
			//value
			if ($value != '') {
				$html_code .= 'value="' . $value . '" ';
			}
			break;
			
		case "checkbox":
			//value
			if ($value == 1 || $value == TRUE || $value != 0) {
				$html_code .= 'checked="' . "checked" . '" ';
			}
			break;
			
		default:
			//value
			if ($value != '') {
				$html_code .= 'value="' . $value . '" ';
			}
			break;
	}
	
		
		
		
	$html_code .= ' />';
	
	
	//$html_code .= '<span></span>';
	
	
	return $html_code;
}


function add_FormElement_select($id, $class='', $value, $selected, $option='') {
	$html_code .= '<select ';
	$html_code .= 'id="' . $id . '" ';
	$html_code .= 'name="' . $id . '" ';
	
	if ($class != '') {
		$html_code .= 'class="' . $class . '" ';
	}
	
	$html_code .= '>';
	
	foreach($value as $option) {
		$html_code .= '<option value="' . $option .'" ';
		
		if ($option==$selected) {
			$html_code .= 'selected="selected">';
		} else {
			$html_code .= '>';
		}
		
		$html_code .= $option;
		$html_code .= '</option>';
	}
	
	$html_code .= '</select>';
	
	return $html_code;
}

?>