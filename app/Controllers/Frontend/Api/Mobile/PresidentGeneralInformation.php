<?php
namespace App\Controllers\Frontend\Api\Mobile;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Api\Mobile\PresidentGeneralInformationModel;

class PresidentGeneralInformation extends BaseController {

  protected $PresidentGeneralInformationModel;
  protected $filePath;

  public function __construct() {
		$this->PresidentGeneralInformationModel = new PresidentGeneralInformationModel();
    $this->filePath = FILE_PATH_PRESIDENT;
	}

  public function index() {
    $array = [];
    $sql = $this->PresidentGeneralInformationModel->presidentGeneralInformationModel($this->defaultLangId);
    if (isNotNull($sql)) {
      foreach ($sql as $row) {

        $array[] = [
          'name_surname' => $row->president_name_surname,
          'sub_title' => $row->president_general_information_sub_title,
          'image' => isNotNull($row->president_image_mobile) ? api_image_url($this->filePath, $row->president_image_mobile) : NULL,
          'social_media' => [
            'facebook' => $row->president_facebook,
            'twitter' => $row->president_twitter,
            'instagram' => $row->president_instagram,
            'youtube' => $row->president_youtube
          ]
        ];

      }
    }

    return json($array);
  }
}
