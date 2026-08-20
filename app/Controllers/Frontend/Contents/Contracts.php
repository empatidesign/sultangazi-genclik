<?php
namespace App\Controllers\Frontend\Contents;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Contents\ContractsModel;
use App\Models\Frontend\Contents\CorporateModel;

class Contracts extends BaseController {

	protected $ContractsModel;
	protected $CorporateModel;
	protected $folder;

	public function __construct() {
		$this->ContractsModel = new ContractsModel();
		$this->CorporateModel = new CorporateModel();
		$this->folder = 'contents';
	}

	public function index($slug, $contract_id) {
		$sql = $this->ContractsModel->contractsInfoModel($slug, $contract_id, $this->defaultLangId);
		if (isNotNull($sql)) {

			return $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/contents/contracts.html', [
				'head' => [
					'title' => isNotNull($sql->contract_meta_title) ? $sql->contract_meta_title : $sql->contract_name,
					'keywords' => isNotNull($sql->contract_meta_keywords) ? $sql->contract_meta_keywords : $this->settings->site_keywords,
					'description' => isNotNull($sql->contract_meta_description) ? $sql->contract_meta_description :  $this->settings->site_description
				],
				'result' => $sql,
				'list' => [
 					'left_menu' => $this->CorporateModel->leftMenuSlugInfoModel('contracts', $this->defaultLangId, $contract_id)
				],
				'folder' => $this->folder
			]);

		}
	}
}
