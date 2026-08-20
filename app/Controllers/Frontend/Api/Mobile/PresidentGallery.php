<?php
namespace App\Controllers\Frontend\Api\Mobile;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Api\Mobile\PresidentGalleryModel;

class PresidentGallery extends BaseController {

  protected $PresidentGalleryModel;
  protected $filePath;

  public function __construct() {
		$this->PresidentGalleryModel = new PresidentGalleryModel();
    $this->filePath = FILE_PATH_GALLERY;
	}

  public function index() {
    $array = [];
    $sql = $this->PresidentGalleryModel->presidentGalleryModel($this->defaultLangId);
    if (isNotNull($sql)) {
      foreach ($sql as $row) {

        $array[] = [
          'image' => isNotNull($row->president_gallery_image) ? api_image_url($this->filePath, $row->president_gallery_image) : NULL,
          'order' => $row->president_gallery_order
        ];

      }
    }

    return json($array);
  }
}
