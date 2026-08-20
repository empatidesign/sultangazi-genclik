<?php
namespace App\Controllers\Frontend;
use CodeIgniter\Controller;

use App\Models\Frontend\SitemapModel;

class Sitemap extends BaseController {

	protected $SitemapModel;

	public function __construct() {
		$this->SitemapModel = new SitemapModel();
	}

	public function index() {

		// Content Type
		$response = service('response');
		$response->setHeader('Content-type', 'text/xml');
		$response->noCache();

		return $this->twig->render(Frontend.'/sitemap.html', [
			'list' => [
				'pages' => $this->SitemapModel->pagesModel(),
				'services' => $this->SitemapModel->servicesModel(),
				'events' => $this->SitemapModel->eventsModel(),
				'president_contents' => $this->SitemapModel->presidentContentsModel(),
				'vice_presidents' => $this->SitemapModel->vicePresidentsModel(),
				'directorates' => $this->SitemapModel->directoratesModel(),
				'news' => $this->SitemapModel->newsModel(),
				'projects' => $this->SitemapModel->projectsModel(),
				'sultangazi_contents' => $this->SitemapModel->sultangaziContentsModel(),
				'city_guide_categories' => $this->SitemapModel->cityGuideCategoriesModel(),
				'city_guide_contents' => $this->SitemapModel->cityGuideContentsModel(),
				'gallery_categories' => $this->SitemapModel->galleryCategoriesModel(),
				'gallery_details' => $this->SitemapModel->galleryDetailModel()
			],
			'PARAMETER' => [
				'DATE' => nowDate('Y-m-d'),
				'WEB_URL_PRESIDENT_GALLERY' => WEB_URL_PRESIDENT_GALLERY,
				'WEB_URL_MUNICIPAL_COUNCILS' => WEB_URL_MUNICIPAL_COUNCILS,
				'WEB_URL_ORGANIZATION_CHART' => WEB_URL_ORGANIZATION_CHART,
				'WEB_URL_VICE_PRESIDENTS' => WEB_URL_VICE_PRESIDENTS,
				'WEB_URL_COUNCIL_MEMBERS' => WEB_URL_COUNCIL_MEMBERS,
				'WEB_URL_DIRECTORATES' => WEB_URL_DIRECTORATES,
				'WEB_URL_ACTIVITY_REPORT' => WEB_URL_ACTIVITY_REPORT,
				'WEB_URL_STRATEGIC_PLAN_AND_PERFORMANCA' => WEB_URL_STRATEGIC_PLAN_AND_PERFORMANCA,
				'WEB_URL_INTERNAL_CONTROL' => WEB_URL_INTERNAL_CONTROL,
				'WEB_URL_PLAN_AND_PROGRAM' => WEB_URL_PLAN_AND_PROGRAM,
				'WEB_URL_LOGOS' => WEB_URL_LOGOS,
				'WEB_URL_PARLIAMENTARY_AGENDA' => WEB_URL_PARLIAMENTARY_AGENDA,
				'WEB_URL_NEWS' => WEB_URL_NEWS,
				'WEB_URL_ANNOUNCEMENTS' => WEB_URL_ANNOUNCEMENTS,
				'WEB_URL_PROJECTS' => WEB_URL_PROJECTS,
				'WEB_URL_SULTANGAZI_GALLERY' => WEB_URL_SULTANGAZI_GALLERY,
				'WEB_URL_SULTANGAZI_CITY_GUIDES' => WEB_URL_SULTANGAZI_CITY_GUIDES,
				'WEB_URL_SULTANGAZI_CITY_GUIDES_DETAIL_PARAMETER' => WEB_URL_SULTANGAZI_CITY_GUIDES_DETAIL_PARAMETER,
				'WEB_URL_GALLERY' => WEB_URL_GALLERY,
				'WEB_URL_VIDEO_GALLERY' => WEB_URL_VIDEO_GALLERY,
				'WEB_URL_CONTACT' => WEB_URL_CONTACT,
				'WEB_URL_PAGES' => WEB_URL_PAGES,
				'WEB_URL_SERVICES' => WEB_URL_SERVICES,
				'WEB_URL_EVENTS' => WEB_URL_EVENTS,
				'WEB_URL_PRESIDENT' => WEB_URL_PRESIDENT,
				'WEB_URL_VICE_PRESIDENTS_DETAIL' => WEB_URL_VICE_PRESIDENTS_DETAIL,
				'WEB_URL_DIRECTORATES_DETAIL' => WEB_URL_DIRECTORATES_DETAIL,
				'WEB_URL_NEWS_DETAIL' => WEB_URL_NEWS_DETAIL,
				'WEB_URL_PROJECTS_DETAIL' => WEB_URL_PROJECTS_DETAIL,
				'WEB_URL_SULTANGAZI' => WEB_URL_SULTANGAZI,
				'WEB_URL_SULTANGAZI_CONTENTS' => WEB_URL_SULTANGAZI_CONTENTS,
				'WEB_URL_GALLERY_CATEGORY' => WEB_URL_GALLERY_CATEGORY,
				'WEB_URL_GALLERY_DETAIL' => WEB_URL_GALLERY_DETAIL
			]
		]);
	}
}
