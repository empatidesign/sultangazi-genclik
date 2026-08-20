<?php
/**
 * @param $path|$file
 * Unlink
 */
function unlinkFile(string $path, string $file = NULL) {
	$path = FCPATH.$path;
	if (isNotNull($file)) {
		if (file_exists($path.'/'.$file)) {
			unlink($path.'/'.$file);
		}

		// Webp
		$webp_image = $path.'/'.pathinfo($file, PATHINFO_FILENAME).'.webp';
		if (file_exists($webp_image)) {
			unlink($webp_image);
		}
	}
}

/**
 * Full URL
 */
function fullUrl() {
	return ($_SERVER['SERVER_PORT'] == 443 ? 'https' : 'http') . "://{$_SERVER['HTTP_HOST']}".$_SERVER['REQUEST_URI'];
}
