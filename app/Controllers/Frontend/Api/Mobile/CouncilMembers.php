<?php
namespace App\Controllers\Frontend\Api\Mobile;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Api\Mobile\CouncilMembersModel;

class CouncilMembers extends BaseController {

  protected $MunicipalCouncilsModel;
  protected $filePath;

  public function __construct() {
		$this->CouncilMembersModel = new CouncilMembersModel();
    $this->filePath = FILE_PATH_COUNCIL_MEMBERS;
	}

  public function index() {
    $array = [];
    $sql = $this->CouncilMembersModel->councilMembersModel($this->defaultLangId);
    if (isNotNull($sql)) {
      foreach ($sql as $row) {

        $array[] = [
          'name' => $row->council_member_name,
          'surname' => $row->council_member_surname,
          'sub_title' => $row->council_member_sub_title,
          'image' => isNotNull($row->council_member_image) ? api_image_url($this->filePath, $row->council_member_image) : NULL,
          'order' => $row->council_member_order
        ];

      }
    }

    return json($array);
  }
}
