<?php

namespace App\Controllers\Frontend\President;

use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\President\PresidentContentsModel;
use App\Models\Frontend\President\SultangaziPresidentModel;
use App\Models\Frontend\Contents\CorporateModel;

class PresidentContents extends BaseController
{

	protected $PresidentContentsModel;
	protected $SultangaziPresidentModel;
	protected $CorporateModel;
	protected $folder;

	public function __construct()
	{
		$this->PresidentContentsModel = new PresidentContentsModel();
		$this->SultangaziPresidentModel = new SultangaziPresidentModel();
		$this->CorporateModel = new CorporateModel();
		$this->folder = 'contents';
	}

	/**
	 * Baskan icerigi (ozgecmis, mesaj).
	 *
	 * Icerik Sultangazi Belediyesi ana sitesinin genel mobil servisinden
	 * cron ile yerel tabloya aktarilir (bkz. _tools/sync_president.php).
	 * Kayit yoksa eski yerel kaynaga dusulur; boylece senkron hic
	 * calismamis kurulumlarda da sayfa bos kalmaz.
	 */
	public function index($slug, $president_content_id)
	{
		$row = $this->SultangaziPresidentModel->content((string) $slug, (int) $president_content_id);

		if (isNotNull($row)) {
			return $this->twig->render($this->FRONTEND_TEMPLATE_PATH . '/president/president-contents.html', [
				'head' => [
					'title' => $row->name,
					'keywords' => $this->settings->site_keywords,
					'description' => isNotNull($row->description)
						? mb_substr(trim(strip_tags($row->description)), 0, 160)
						: $this->settings->site_description
				],
				'result' => [
					'president_content_name' => $row->name,
					'image' => [
						'normal' => $row->image_url,
						'base' => $row->image_url
					],
					'president_content_description' => $row->description,
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

		// Yedek: eski yerel kaynak
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

		return redirect()->to('404');
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
