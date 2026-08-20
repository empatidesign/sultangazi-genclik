<?php
namespace App\Controllers\Frontend\Api\Mobile;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Api\Mobile\DirectoratesModel;

class Directorates extends BaseController {

  protected $DirectoratesModel;
  protected $filePath;

  public function __construct() {
		$this->DirectoratesModel = new DirectoratesModel();
    $this->filePath = FILE_PATH_DIRECTORATES;
	}

  public function index() {
    $array = [];
    $sql = $this->DirectoratesModel->directoratesModel($this->defaultLangId);
    if (isNotNull($sql)) {
      foreach ($sql as $row) {

        $array[] = [
          'name' => $row->directorates_name,
          'person' => [
            'name' => $row->directorates_person_name,
            'surname' => $row->directorates_person_surname,
            'sub_title' => $row->directorates_person_sub_title,
            'image' => isNotNull($row->directorates_person_image) ? api_image_url($this->filePath, $row->directorates_person_image) : NULL
          ],
          'contact' => [
            'telephone' => $row->directorates_telephone,
            'fax' => $row->directorates_fax,
            'email_address' => $row->directorates_email_address
          ]
        ];

      }
    }

    return json($array);
  }
}
