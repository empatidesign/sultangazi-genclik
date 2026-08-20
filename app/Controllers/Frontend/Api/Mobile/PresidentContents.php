<?php
namespace App\Controllers\Frontend\Api\Mobile;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Api\Mobile\PresidentContentsModel;

class PresidentContents extends BaseController {

  protected $PresidentContentsModel;
  protected $filePath;

  public function __construct() {
		$this->PresidentContentsModel = new PresidentContentsModel();
    $this->filePath = FILE_PATH_PRESIDENT;
	}

  public function index() {
    $array = [];
    $sql = $this->PresidentContentsModel->presidentContentsModel($this->defaultLangId);
    if (isNotNull($sql)) {
      foreach ($sql as $row) {

        $array[] = [
          'name' => $row->president_content_name,
          'description' => $row->president_content_description,
          'image' => isNotNull($row->president_content_image) ? api_image_url($this->filePath, $row->president_content_image) : NULL
        ];

      }
    }

    return json($array);
  }
}
