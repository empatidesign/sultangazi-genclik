<?php
/**
 * @param array $data
 * @param int $status
 * @param bool $echo
 * @return false|string
 */
function json(array $data, int $status = 200, bool $echo = TRUE) {
	$json = json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
	if($echo == TRUE){
		http_response_code($status);
		header('Content-Type: application/json; charset=utf-8');
		return $json;
    }else{
		return $json;
	}
}

/**
 * @param string $string
 * @return false|string
 * Verilen kelimedeki harfleri büyük hale getirir.
 */
function upper($string):string {
	$search = ['ç','i','ı','ğ','ö','ş','ü'];
	$replace = ['Ç','İ','I','Ğ','Ö','Ş','Ü'];
	$string = str_replace($search, $replace, $string);
	return mb_strtoupper($string, 'UTF-8');
}

/**
 * @param string $string
 * @return false|string
 * Verilen kelimedeki harfleri küçük hale getirir.
 */
function lowercase($string):string {
	$string = str_replace('Ç', 'ç', $string);
	$string = str_replace('Ğ', 'ğ', $string);
	$string = str_replace('I', 'ı', $string);
	$string = str_replace('İ', 'i', $string);
	$string = str_replace('Ö', 'ö', $string);
	$string = str_replace('Ş', 'ş', $string);
	$string = str_replace('Ü', 'ü', $string);
	return mb_strtolower($string, 'UTF-8');
}
