<?php
namespace App\Controllers\Frontend;
use CodeIgniter\Controller;

use App\Models\Frontend\Contents\ContractsModel;

class Callback extends BaseController {

	protected $ContractsModel;

	public function __construct() {
		$this->ContractsModel = new ContractsModel();
	}

	public function index() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				// Select Auto Change
				if ($this->request->getVar('action') == 'select-auto-change') {
					$array = [];
					$parent = $this->request->getVar('parent');
					$get = $this->request->getVar('get');

					// Cities
					if ($get == 'city_id') {
						$cities = $this->general->citiesListModel($parent);
						if (isNotNull($cities)) {
							foreach ($cities as $row) {

								$array[] = [
									'id' => $row->city_id,
									'name' => $row->city_name
								];

							}
						}
					}

					// Districts
					if ($get == 'district_id') {
						$districts = $this->general->districtsListModel($parent);
						if (isNotNull($districts)) {
							foreach ($districts as $row) {

								$array[] = [
									'id' => $row->district_id,
									'name' => $row->district_name
								];

							}
						}
					}

					$ajax_message['list'] = $array;
				}

				/*****************************************************/

				// Agreement Popup
				if ($this->request->getVar('action') == 'agreement-popup') {
					$contract_info = $this->ContractsModel->contractsInfoModel(NULL, $this->request->getVar('value'), $this->defaultLangId);
					if (isNotNull($contract_info)) {

						$data = [
							'name' => $contract_info->contract_name,
							'description' => $contract_info->contract_description
						];

						$ajax_message['success'] = $data;

					} else {
						$ajax_message['error'] = lang('Web.error.description');
					}

				}

				return $this->response->setJSON($ajax_message);

			}
		}
	}
}
