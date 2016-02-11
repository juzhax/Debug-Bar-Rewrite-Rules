<?php
 	
//	file_put_contents(dirname(__FILE__).'/log.txt', serialize(stripslashes_deep($_POST)));
	$array = stripslashes_deep($_POST);
 //	$array = unserialize(file_get_contents( dirname(__FILE__).'/log.txt' ));

	if (isset($array['rules']) && count($array['rules']) > 0 && isset($array['search'])){
		foreach($array['rules'] as $k=>$rule){
 
			
			if ( preg_match("#^".$rule['rule']."#", $array['search'], $matches) ||
				preg_match("#^".$rule['rule']."#", urldecode($array['search']), $matches) ) {
					
					// Trim the query of everything up to the '?'.
					$query = preg_replace("!^.+\?!", '', $rule['match']);
					
					foreach($matches as $_k=>$_i){
						if (strpos($query, '$matches['.$_k.']') !== false){
							$query = str_replace('$matches['.$_k.']', $_i, $query);
						}
					}
					
					parse_str($query, $data);
					if (is_array($data) && count($data) > 0){
						foreach($data as $_k=>$_i){
							if (strpos($_i, '$matches') === false){
								$array['rules'][$k]['vars'][$_k] = $_i;
							}
						}
					}
					
				$array['rules'][$k]['result'] = true;
					
			} else {
				//unset($_POST['rules'][$k]);
				$array['rules'][$k]['result'] = false;
			}
		}
	}
	
	
	function stripslashes_deep( $value ) {
		if ( is_array($value) ) {
			$value = array_map('stripslashes_deep', $value);
		} elseif ( is_object($value) ) {
			$vars = get_object_vars( $value );
			foreach ($vars as $key=>$data) {
				$value->{$key} = stripslashes_deep( $data );
			}
		} elseif ( is_string( $value ) ) {
			$value = stripslashes($value);
		}

		return $value;
	}
	
 	//die(var_dump($array));
	header('Content-type: application/json');
	echo json_encode($array);
?>