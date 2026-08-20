<?php
namespace App\Controllers\Frontend\Api\Mobile;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Api\Mobile\BannerModel;

class Banner extends BaseController {

  protected $BannerModel;
  protected $filePath;

  public function __construct() {
		$this->BannerModel = new BannerModel();
    $this->filePath = FILE_PATH_BANNER_MANAGEMENT;
	}

  public function index() {
    $array = [];
    $sql = $this->BannerModel->bannerModel($this->defaultLangId);
    if (isNotNull($sql)) {
      foreach ($sql as $row) {

        $array[] = [
          'type' => $row->banner_type,
          'name' => $row->banner_name,
          'description' => $row->banner_description,
          'image' => [
            'web' => isNotNull($row->banner_web_image) ? api_image_url($this->filePath, $row->banner_web_image) : NULL,
            'mobile' => isNotNull($row->banner_mobile_image) ? api_image_url($this->filePath, $row->banner_mobile_image) : NULL
          ],
          'url' => [
            'link' => $row->banner_link,
            'target' => $row->banner_link_target
          ],
          'order' => $row->banner_order
        ];

      }
    }

    return json($array);
  }
}
