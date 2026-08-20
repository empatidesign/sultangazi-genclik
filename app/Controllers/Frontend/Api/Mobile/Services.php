<?php
namespace App\Controllers\Frontend\Api\Mobile;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Api\Mobile\ServicesModel;

class Services extends BaseController {

  protected $ServicesModel;
  protected $filePath;

  public function __construct() {
		$this->ServicesModel = new ServicesModel();
    $this->filePath = FILE_PATH_SERVICES;
	}

  public function index() {
    $array = [];
    $sql = $this->ServicesModel->servicesModel($this->defaultLangId);
    if (isNotNull($sql)) {
      foreach ($sql as $row) {

        $array[] = [
          'type_id' => $row->service_type,
          'name' => $row->service_name,
          'link' => $row->service_link,
          'description' => [
            'short' => $row->service_short_description,
            'long' => $row->service_description
          ],
          'image' => isNotNull($row->service_image) ? api_image_url($this->filePath, $row->service_image) : NULL,
          'icon' => isNotNull($row->service_icon) ? api_image_url($this->filePath, $row->service_icon) : NULL,
          'order' => $row->service_order
        ];

      }
    }

    return json($array);
  }
}
