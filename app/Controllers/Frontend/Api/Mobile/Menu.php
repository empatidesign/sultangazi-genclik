<?php
namespace App\Controllers\Frontend\Api\Mobile;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Api\Mobile\MenuModel;

class Menu extends BaseController {

  protected $MenuModel;

  public function __construct() {
		$this->ApiMenuModel = new MenuModel();
	}

  public function index() {
    $array = [];
    $sql = $this->ApiMenuModel->menuListModel($this->defaultLangId);
    if (isNotNull($sql)) {
      foreach ($sql as $row) {

        $array[] = [
          'id' => $row->menu_id,
          'parent_id' => $row->menu_parent_id,
          'name' => $row->menu_name,
          'target' => $row->menu_target,
          'location' => $row->menu_location,
          'link' => $this->ApiMenuModel->menuLinkModel($row->menu_type, $row->menu_link, $row->menu_page_id, $row->menu_sultangazi_content_id, $row->menu_contract_id, $row->menu_service_id, $row->menu_president_content_id, $this->defaultLangId),
          'order' => $row->menu_order
        ];

      }
    }

    return json($array);
  }
}
