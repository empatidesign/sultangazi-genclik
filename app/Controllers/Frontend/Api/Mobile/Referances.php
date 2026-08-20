<?php
namespace App\Controllers\Frontend\Api\Mobile;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Api\Mobile\ReferancesModel;

class Referances extends BaseController {

  protected $ReferancesModel;
  protected $filePath;

  public function __construct() {
		$this->ReferancesModel = new ReferancesModel();
    $this->filePath = FILE_PATH_REFERANCES;
	}

  public function index() {
    $array = [];
    $sql = $this->ReferancesModel->referancesModel($this->defaultLangId);
    if (isNotNull($sql)) {
      foreach ($sql as $row) {

        $array[] = [
          'name' => $row->referance_name,
          'image' => isNotNull($row->referance_image) ? api_image_url($this->filePath, $row->referance_image) : NULL,
          'link' => $row->referance_link
        ];

      }
    }

    return json($array);
  }
}
