<?php
namespace App\Libraries;

use App\Models\Frontend\GeneralModel;

class GoogleReCaptcha {

	protected $general;
	protected $settings;

	public function index($recaptcha_response) {
		$this->general = new GeneralModel();
		$request = \Config\Services::request();

		$this->defaultLangId = $this->general->languagesDefaultModel()[0];
		if (isNotNull($this->defaultLangId)) {
			$this->settings = $this->general->getSettingsModel($this->defaultLangId);
		}

    $result = FALSE;
  	$recaptchaResponse = trim($recaptcha_response);
  	$userIp = $request->getIPAddress();
  	$secret = $this->settings->google_recaptcha_secret;

  	$url = 'https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$recaptchaResponse.'&remoteip='.$userIp;

  	$ch = curl_init();
  	curl_setopt($ch, CURLOPT_URL, $url);
  	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  	$output = curl_exec($ch);
  	curl_close($ch);

    if (isNotNull($output)) {
    	$status = json_decode($output, TRUE);
    	if ($status['success']) {
    		$result = TRUE;
    	}
    }

    return $result;
	}
}
