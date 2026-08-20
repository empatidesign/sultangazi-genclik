<?php
namespace App\Controllers\Frontend\Api\Mobile;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Api\Mobile\VicePresidentsModel;

class VicePresidents extends BaseController {

  protected $VicePresidentsModel;
  protected $filePath;

  public function __construct() {
		$this->VicePresidentsModel = new VicePresidentsModel();
    $this->filePath = FILE_PATH_VICE_PRESIDENTS;
	}

  public function index() {
    $array = [];
    $sql = $this->VicePresidentsModel->vicePresidentsModel($this->defaultLangId);
    if (isNotNull($sql)) {
      foreach ($sql as $row) {

        $array[] = [
          'name' => $row->vice_president_name,
          'surname' => $row->vice_president_surname,
          'sub_title' => $row->vice_president_sub_title,
          'description' => $row->vice_president_description,
          'telephone' => $row->vice_president_telephone,
          'email_address' => $row->vice_president_email_address,
          'image' => isNotNull($row->vice_president_image) ? api_image_url($this->filePath, $row->vice_president_image) : NULL,
          'order' => $row->vice_president_order
        ];

      }
    }

    return json($array);
  }
}
