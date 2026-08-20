<?php
namespace App\Controllers\Frontend\Contents;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Contents\ParliamentaryAgendaModel;
use App\Models\Frontend\Contents\CorporateModel;

class ParliamentaryAgenda extends BaseController {

	protected $ParliamentaryAgendaModel;
	protected $CorporateModel;
	protected $folder;

	public function __construct() {
		$this->ParliamentaryAgendaModel = new ParliamentaryAgendaModel();
		$this->CorporateModel = new CorporateModel();
		$this->folder = 'contents';
	}

	public function index() {

		// Segment
		$segment = $this->request->getUri()->getTotalSegments() > 1 ? $this->request->getUri()->getSegment(2) : $this->request->getUri()->getSegment(1);

		return $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/contents/parliamentary-agenda.html', [
			'head' => [
				'title' => lang('WebParliamentaryAgenda.title'),
				'keywords' => $this->settings->site_keywords,
				'description' => $this->settings->site_description
			],
			'list' => [
				'parliamentary_agendas' => $this->allParliamentaryAgenda(),
				'left_menu' => $this->CorporateModel->leftMenuSlugInfoModel($segment, $this->defaultLangId)
			],
			'folder' => $this->folder
		]);
	}

	public function allParliamentaryAgenda() {
		$array = [];
		$sql = $this->ParliamentaryAgendaModel->parliamentaryAgendaListModel($this->defaultLangId);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'parliamentary_agenda_name' => $row->parliamentary_agenda_name,
					'file' => [
						'base' => base_url(FILE_PATH_PARLIAMENTARY_AGENDA.'/'.$row->parliamentary_agenda_file)
					]
				];
			}
		}

		return $array;
	}
}
