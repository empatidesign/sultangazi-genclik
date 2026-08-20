<?php
namespace App\Controllers\Frontend\Api\Mobile;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Libraries\MobileApiJWT;

class Authenticate extends BaseController {

  protected $MobileApiJWT;

  public function __construct() {
    $this->MobileApiJWT = new MobileApiJWT();
	}

  public function index() {

    $jwt = $this->MobileApiJWT->authenticate();
    $json = json_decode($jwt, TRUE);
    if ($json['code'] == 200) {
      echo $json['success'];
    } else {
      echo $jwt;
    }

  }
}
