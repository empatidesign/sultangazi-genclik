<?php
function info(bool $json = FALSE) {
	$data = [
		'GATEWAY_INTERFACE' => $_SERVER['GATEWAY_INTERFACE'] ?? NULL,
		'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? NULL,
		'REQUEST_TIME' => $_SERVER['REQUEST_TIME'] ?? NULL,
		'DATETIME' => nowDate(),
		'QUERY_STRING' => $_SERVER['QUERY_STRING'] ?? NULL,
		'HTTP_ACCEPT' => $_SERVER['HTTP_ACCEPT'] ?? NULL,
		'HTTP_REFERER' => $_SERVER['HTTP_REFERER'] ?? NULL,
		'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'] ?? NULL,
		'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'] ?? NULL,
		'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? NULL,
		'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? NULL,
		'HTTP_X_FORWARDED_FOR' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? NULL,
		'HTTP_CLIENT_IP' => $_SERVER['HTTP_CLIENT_IP'] ?? NULL,
		'POST_DATA' => filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING),
		'GET_DATA' => filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING),
		'HEADER' => headers_list(),
		'HEADER_STATUS' => http_response_code(),
		'SESSION' => $_SESSION ?? NULL,
		'SESSION_ID' => session_id(),
		'HTTP_VIA' => $_SERVER['HTTP_VIA'] ?? NULL,
		'HTTP_FORWARDED_FOR' => $_SERVER['HTTP_FORWARDED_FOR'] ?? NULL,
		'HTTP_X_FORWARDED' => $_SERVER['HTTP_X_FORWARDED'] ?? NULL,
		'HTTP_FORWARDED' => $_SERVER['HTTP_FORWARDED'] ?? NULL,
		'HTTP_FORWARDED_FOR_IP' => $_SERVER['HTTP_FORWARDED_FOR_IP'] ?? NULL,
		'VIA' => $_SERVER['VIA'] ?? NULL,
		'X_FORWARDED_FOR' => $_SERVER['X_FORWARDED_FOR'] ?? NULL,
		'FORWARDED_FOR' => $_SERVER['FORWARDED_FOR'] ?? NULL,
		'X_FORWARDED' => $_SERVER['X_FORWARDED'] ?? NULL,
		'FORWARDED' => $_SERVER['FORWARDED'] ?? NULL,
		'CLIENT_IP' => $_SERVER['CLIENT_IP'] ?? NULL,
		'FORWARDED_FOR_IP' => $_SERVER['FORWARDED_FOR_IP'] ?? NULL,
		'HTTP_PROXY_CONNECTION' => $_SERVER['HTTP_PROXY_CONNECTION'] ?? NULL
	];

	return $json == TRUE ? json($data, 200, FALSE) : $data;
}

/**
 * @return url
 * Aktif URL veya Dil URL döner.
 */
function web_url($link = NULL) {
	if (session()->has('webLang')) {

		$segment = \Config\Services::request();
		$lang = session()->get('webLang') != $segment->getUri()->getSegment(1) ? NULL : session()->get('webLang');
		return base_url($lang.'/'.$link);

	} else {
		if (isNotNull($link)){
			return base_url($link);
		} else {
			return base_url();
		}
	}
}

/**
 * @param string $input
 * @param bool $bypass
 * @param int $filter_type
 * @return mixed|null
 * Form ile gönderilen değeri döndürür.
 */
function get(string $input, bool $bypass = FALSE, int $filter_type = FILTER_SANITIZE_STRING) {
	$result = filter_input(INPUT_GET, $input, $filter_type);
	if($bypass == TRUE)
		$result = $_GET[$input] ?? NULL;

	return isNull($result);
}

/**
 * @param string $input
 * @param bool $bypass
 * @param int $filter_type
 * @return mixed|null
 * Form ile gönderilen değeri döndürür.
 */
function post(string $input, bool $bypass = FALSE, int $filter_type = FILTER_SANITIZE_STRING) {
	$result = filter_input(INPUT_POST, $input, $filter_type);
	if($bypass == TRUE)
		$result = $_POST[$input] ?? NULL;

    return isNull($result);
}

/**
 * @param $string
 * @return mixed|null
 * Gönderilen değerin boş olup olmadığını kontrol eder.
 */
function isNull($string) {
	return is_null($string) || empty($string) || $string == '';
}

/**
 * @param $string
 * @return mixed|null
 * Gönderilen değerin boş olmadığını kontrol eder.
 */
function isNotNull($string) {
	return isset($string) || !empty($string) || $string != '' || $string != NULL ? $string : NULL;
}

/**
 * @param $url
 * @return string
 * Geçerli Url Kontrol
 */
function validUrl($url) {
	if(!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $url)){
		return FALSE;
	}else{
		return TRUE;
	}
}

/**
 * @param $datas|$parent
 * @return array
 */
function parentMenuNested($datas = FALSE, $parent = CATEGORY_MANAGEMENT_PARENT_MENU) {
	if (isNotNull($datas)) {
		$nested = $datas[$parent];
		foreach ($nested as $key => $data) {
			if (isset($datas[$data['id']])) {
				$nested[$key]['children'] = parentMenuNested($datas, $data['id']);
			}
		}

		return $nested;
	}
}

/**
 * @param $str
 * @return string
 * HTML tag temizler.
 */
function stripHTMLtags($string) {
	$result = preg_replace('/<[^<|>]+?>/', '', htmlspecialchars_decode($string));
	$result = htmlentities($result, ENT_QUOTES, 'UTF-8');
	return $result;
}

/**
 * @param $input
 * @return array|string|string[]|null
 * Tüm boşlukları temizler.
 */
function removeWhiteSpaces($input) {
	return str_replace(' ', '', $input);
}

/**
 * @param $file
 * @return array|string|string[]
 * Dosya uzantısını döndürür.
 */
function fileExtension($file) {
	return pathinfo($file, PATHINFO_EXTENSION);
}

/**
 * @param $file
 * @param array $allowedTypes
 * @return bool|int|string
 * Dosya türünü döndürür.
 */
function fileMimeType($file, array $allowedTypes = []) {
	$result = false;
	if(function_exists('mime_content_type')){
		$result = mime_content_type($file);
	}elseif(function_exists('exif_imagetype')){
		$result = exif_imagetype($file);
	}

	if($allowedTypes != NULL){
		$result = fileMimeCheck($result, $allowedTypes);
	}

	return $result;
}

/**
 * @param $type
 * @param array $allowedTypes
 * @return bool
 * Gönderilen dosya türünün izin verilenler içinde olup olmadığını kontrol eder.
 */
function fileMimeCheck($type, array $allowedTypes = []): bool {
	return in_array($type, $allowedTypes);
}

/**
 * @param $path
 * @return bool
 * Dosya boyutunu döndürür.
 */
function fileSizeResult($path) {
	$size = 0;
	$file = glob($path, GLOB_NOSORT);
	foreach ($file as $each) {
		$size += is_file($each) ? filesize($each) : fileSizeResult($each);
	}

	$sizes = [' Bytes', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB'];

	if ($size == 0) {
		return(0);
	} else {
		return (round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i]);
	}
}
