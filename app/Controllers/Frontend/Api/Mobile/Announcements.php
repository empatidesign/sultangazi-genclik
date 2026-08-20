<?php
namespace App\Controllers\Frontend\Api\Mobile;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Api\Mobile\AnnouncementsModel;
use App\Libraries\MobileApiJWT;

class Announcements extends BaseController {

  protected $AnnouncementsModel;
  protected $MobileApiJWT;

  public function __construct() {
		$this->AnnouncementsModel = new AnnouncementsModel();
    $this->MobileApiJWT = new MobileApiJWT();
	}

  public function index() {

    //$jwt = $this->MobileApiJWT->index();
    //$json = json_decode($jwt, TRUE);
    //if ($json['code'] == 200) {

      $array = [];
      $sql = $this->AnnouncementsModel->announcementsModel($this->defaultLangId);
      if (isNotNull($sql)) {
        foreach ($sql as $row) {

          $array[] = [
            'name' => $row->announcement_name,
            'link' => $row->announcement_link,
            'description' => $row->announcement_description,
            'date' => $row->announcement_created_date
          ];

        }
      }

      return json($array);

    //} else {
      //echo $jwt;
    //}

  }
}
