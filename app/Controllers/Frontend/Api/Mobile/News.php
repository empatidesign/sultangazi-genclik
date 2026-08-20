<?php
namespace App\Controllers\Frontend\Api\Mobile;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Api\Mobile\NewsModel;

class News extends BaseController {

  protected $NewsModel;
  protected $filePath;
  protected $filePathGallery;

  public function __construct() {
		$this->NewsModel = new NewsModel();
    $this->filePath = FILE_PATH_NEWS;
    $this->filePathGallery = FILE_PATH_NEWS_GALLERY;
	}

  public function index() {
    $array = [];
    $sql = $this->NewsModel->newsModel($this->defaultLangId);
    if (isNotNull($sql)) {
      foreach ($sql as $row) {

        // Gallery
        $gallery = [];
        $gallery_sql = $this->NewsModel->newsGalleryModel($row->news_id);
        if (isNotNull($gallery_sql)) {
          foreach ($gallery_sql as $value) {
            $gallery[] = [
              'image' => api_image_url($this->filePathGallery, $value->news_image),
              'order' => $value->news_image_order
            ];
          }
        }

        $array[] = [
          'name' => $row->news_name,
          'cover_image' => isNotNull($row->news_image) ? api_image_url($this->filePath, $row->news_image) : NULL,
          'description' => $row->news_description,
          'gallery' => $gallery,
          'date' => $row->news_created_date
        ];

      }
    }

    return json($array);
  }
}
