<?php
namespace App\Controllers\Frontend\Api\Mobile;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Api\Mobile\ProjectsModel;

class Projects extends BaseController {

  protected $ProjectsModel;
  protected $filePathThumb;
  protected $filePathMedium;
  protected $filePathBig;

  public function __construct() {
		$this->ProjectsModel = new ProjectsModel();
    $this->filePathThumb = FILE_PATH_PROJECT_THUMB;
		$this->filePathMedium = FILE_PATH_PROJECT_MEDIUM;
		$this->filePathBig = FILE_PATH_PROJECT_BIG;
	}

  public function index() {
    $array = [];
    $sql = $this->ProjectsModel->projectsModel($this->defaultLangId);
    if (isNotNull($sql)) {
      foreach ($sql as $row) {

        // Gallery
        $gallery = [];
        $gallery_sql = $this->ProjectsModel->projectGalleryModel($row->project_id);
        if (isNotNull($gallery_sql)) {
          foreach ($gallery_sql as $value) {
            $gallery[] = [
              'thumb' => api_image_url($this->filePathThumb, $value->project_image),
              'medium' => api_image_url($this->filePathMedium, $value->project_image),
              'big' => api_image_url($this->filePathBig, $value->project_image)
            ];
          }
        }

        $array[] = [
          'name' => $row->project_name,
          'description' => $row->project_description,
          'category' => $row->project_category_name,
          'status' => $row->project_status_name,
          'neighbourhood' => $row->neighbourhood_name,
          'address' => $row->project_location_address,
          'responsible' => $row->project_responsible,
          'telephone' => $row->project_location_telephone,
          'dates' => [
            'start' => $row->project_start_date,
            'end' => $row->project_end_date
          ],
          'map' => [
            'url' => $row->project_location_map,
            'lat_coordinate' => $row->project_lat_coordinate,
            'long_coordinate' => $row->project_long_coordinate
          ],
          'gallery' => $gallery
        ];

      }
    }

    return json($array);
  }
}
