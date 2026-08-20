<?php

namespace App\Controllers\Frontend\President;

use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\President\PresidentContentsModel;
use App\Models\Frontend\Contents\CorporateModel;

class PresidentContents extends BaseController
{

	protected $PresidentContentsModel;
	protected $CorporateModel;
	protected $folder;

	public function __construct()
	{
		$this->PresidentContentsModel = new PresidentContentsModel();
		$this->CorporateModel = new CorporateModel();
		$this->folder = 'contents';
	}

	public function index($slug, $president_content_id)
	{
		$sql = $this->PresidentContentsModel->presidentContentInfoModel($slug, $president_content_id, $this->defaultLangId);
		if (isNotNull($sql)) {

			return $this->twig->render($this->FRONTEND_TEMPLATE_PATH . '/president/president-contents.html', [
				'head' => [
					'title' => isNotNull($sql->president_content_meta_title) ? $sql->president_content_meta_title : $sql->president_content_name,
					'keywords' => isNotNull($sql->president_content_meta_keywords) ? $sql->president_content_meta_keywords : $this->settings->site_keywords,
					'description' => isNotNull($sql->president_content_meta_description) ? $sql->president_content_meta_description :  $this->settings->site_description
				],
				'result' => [
					'president_content_name' => $sql->president_content_name,
					'image' => [
						'normal' => $sql->president_content_image,
						'base' => $this->sultanImageControl(FILE_PATH_PRESIDENT, $sql->president_content_image)
					],
					'president_content_description' => $sql->president_content_description,
					'president' => [
						'informations' => $this->informations()
					]
				],
				'list' => [
					'left_menu' => $this->CorporateModel->leftMenuSlugInfoModel('president', $this->defaultLangId, NULL, $president_content_id)
				],
				'folder' => $this->folder
			]);
		}
	}

	public function informations()
	{
		$data = [];
		$sql = $this->general->getPresidentGeneralInformationsModel($this->defaultLangId);
		if (isNotNull($sql)) {
			$data = [
				'president_name_surname' => $sql->president_name_surname,
				'president_sub_title' => $sql->president_general_information_sub_title,
				'president_image' => [
					'base' => isNotNull($sql->president_image) ? $this->sultanImageControl('assets/images', 'sultangazi-belediye-baskani-abdurrahman-dursun.png') : NULL
				],
				'president_facebook' => $sql->president_facebook,
				'president_twitter' => $sql->president_twitter,
				'president_instagram' => $sql->president_instagram,
				'president_youtube' => $sql->president_youtube
			];
		}

		return $data;
	}
}
