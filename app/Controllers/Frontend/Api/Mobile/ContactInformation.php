<?php
namespace App\Controllers\Frontend\Api\Mobile;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Api\Mobile\ContactInformationModel;

class ContactInformation extends BaseController {

  protected $ContactInformationModel;
  protected $filePath;

  public function __construct() {
		$this->ContactInformationModel = new ContactInformationModel();
    $this->filePath = FILE_PATH_CONTACT;
	}

  public function index() {
    $array = [];
    $sql = $this->ContactInformationModel->contactInformationModel($this->defaultLangId);
    if (isNotNull($sql)) {
      foreach ($sql as $row) {

        $array[] = [
          'default' => $row->contact_default == FORM_ACTIVE_NUMBER ? 'true' : 'false',
          'title' => $row->contact_title,
          'address' => $row->contact_address,
          'telephone' => [
            'telephone1' => $row->contact_telephone,
            'telephone2' => $row->contact_telephone2,
            'mobile' => $row->contact_mobile,
            'whatsapp' => $row->contact_whatsapp,
          ],
          'fax' => [
            'fax1' => $row->contact_fax,
            'fax2' => $row->contact_fax2
          ],
          'email' => [
            'email1' => $row->contact_email,
            'email2' => $row->contact_email2
          ],
          'map' => [
            'lat_coordinate' => $row->contact_map_lat_coordinate,
            'long_coordinate' => $row->contact_map_long_coordinate,
            'map_url' => $row->contact_map_url,
            'map_marker' => isNotNull($row->contact_map_marker) ? api_image_url($this->filePath, $row->contact_map_marker) : NULL
          ],
          'post_code' => $row->contact_post_code,
          'working_hours' => $row->contact_working_hours
        ];

      }
    }

    return json($array);
  }
}
