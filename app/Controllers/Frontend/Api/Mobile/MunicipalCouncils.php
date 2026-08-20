<?php
namespace App\Controllers\Frontend\Api\Mobile;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Api\Mobile\MunicipalCouncilsModel;

class MunicipalCouncils extends BaseController {

  protected $MunicipalCouncilsModel;
  protected $filePath;

  public function __construct() {
		$this->MunicipalCouncilsModel = new MunicipalCouncilsModel();
    $this->filePath = FILE_PATH_MUNICIPAL_COUNCILS;
	}

  public function index() {
    $array = [];
    $sql = $this->MunicipalCouncilsModel->municipalCouncilsModel($this->defaultLangId);
    if (isNotNull($sql)) {
      foreach ($sql as $row) {

        $array[] = [
          'name' => $row->municipal_council_name,
          'surname' => $row->municipal_council_surname,
          'sub_title' => $row->municipal_council_sub_title,
          'image' => isNotNull($row->municipal_council_image) ? api_image_url($this->filePath, $row->municipal_council_image) : NULL,
          'order' => $row->municipal_council_order
        ];

      }
    }

    return json($array);
  }
}
