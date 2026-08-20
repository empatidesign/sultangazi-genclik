<?php
namespace App\Controllers\Frontend\News;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\News\NewsModel;

class News extends BaseController {

	protected $NewsModel;
	protected $folder;

	public function __construct() {
		$this->NewsModel = new NewsModel();
		$this->folder = 'news';
	}

	public function index() {

		$pager = service('pager');
		$page = $this->request->getVar('page');
		if (!is_numeric($page) || $page <= 0) {
			$page = 1;
		}
		$page = (int) $page; 
		$per_page = 20;
		$page_start = $page ? ($page * $per_page) - $per_page : 1;
		$total = count($this->allNews());
		$pagination = $pager->makeLinks($page, $per_page, $total, 'classic');

		return $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/'.$this->folder.'/news.html', [
			'head' => [
				'title' => lang('WebNews.news.mainTitle'),
				'keywords' => $this->settings->site_keywords,
				'description' => $this->settings->site_description
			],
			'list' => [
				'all' => $this->allNews($page_start, $per_page)
			],
			'pagination' => [
				'list' => $pagination
			],
			'folder' => $this->folder,
			'PARAMETER' => [
				'WEB_URL_NEWS' => WEB_URL_NEWS,
				'WEB_URL_NEWS_DETAIL' => WEB_URL_NEWS_DETAIL,
				'WEB_URL_EVENTS' => WEB_URL_EVENTS,
				'WEB_URL_ANNOUNCEMENTS' => WEB_URL_ANNOUNCEMENTS,
				'WEB_URL_TENDER_ANNOUNCEMENTS' => WEB_URL_TENDER_ANNOUNCEMENTS
			]
		]);

	}

	public function detail($slug, $news_id) {
		$slug = esc($slug); 
		
		if (!is_numeric($news_id) || $news_id <= 0) {
			return redirect()->to('404');
		}
		
		$news_id = (int) $news_id; 
		
		$sql = $this->NewsModel->newsInfoModel($slug, $news_id, $this->defaultLangId);
		if (isNotNull($sql)) {

			return $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/'.$this->folder.'/news-detail.html', [
				'page_name' => 'news-detail',
				'head' => [
					'title' => isNotNull($sql->news_meta_title) ? $sql->news_meta_title : $sql->news_name,
					'keywords' => isNotNull($sql->news_meta_keywords) ? $sql->news_meta_keywords : $this->settings->site_keywords,
					'description' => isNotNull($sql->news_meta_description) ? $sql->news_meta_description : $this->settings->site_description
				],
				'result' => [
					'news_name' => $sql->news_name,
					'created_date' => substr(dateFormat($sql->news_created_date, 'd/m/Y'), 0, 10),
					'image' => [
						'format' => $this->sultanImageControl(FILE_PATH_NEWS, $sql->news_image)
					],
					'news_description' => $sql->news_description
				],
				'list' => [
					'paragraphs' => $this->paragraphs($news_id),
					'images' => $this->images($news_id),
					'all' => $this->allNews(0, 8)
				],
				'folder' => $this->folder,
				'PARAMETER' => [
					'WEB_URL_NEWS' => WEB_URL_NEWS,
					'WEB_URL_NEWS_DETAIL' => WEB_URL_NEWS_DETAIL,
					'WEB_URL_EVENTS' => WEB_URL_EVENTS,
					'WEB_URL_ANNOUNCEMENTS' => WEB_URL_ANNOUNCEMENTS,
					'WEB_URL_TENDER_ANNOUNCEMENTS' => WEB_URL_TENDER_ANNOUNCEMENTS
				]
			]);

		} else {
			return redirect()->to('404');
		}
	}

	public function allNews($page_start = NULL, $per_page = NULL) {
		$array = [];
		$sql = $this->NewsModel->newsListModel($this->defaultLangId, $page_start, $per_page);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'news_id' => $row->news_id,
					'news_name' => $row->news_name,
					'news_slug' => $row->news_slug,
					'image' => [
						'medium' => $this->sultanImageControl(FILE_PATH_NEWS, $row->news_image)
					],
					'created_date' => substr(dateFormat($row->news_created_date, 'd/m/Y'), 0, 10)
				];
			}
		}

		return $array;
	}

	public function paragraphs($news_id) {
		if (!is_numeric($news_id) || $news_id <= 0) {
			return [];
		}
		
		$news_id = (int) $news_id;
		
		$array = [];
		$sql = $this->NewsModel->newsParagraphsListModel($news_id, $this->defaultLangId);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'news_paragraph_name' => $row->news_paragraph_name,
					'news_paragraph_description' => $row->news_paragraph_description,
					'image' => [
						'normal' => $row->news_paragraph_image,
						'format' => $this->sultanImageControl(FILE_PATH_NEWS, $row->news_paragraph_image)
					]
				];
			}
		}

		return $array;
	}

	public function images($news_id) {
		if (!is_numeric($news_id) || $news_id <= 0) {
			return [];
		}
		
		$news_id = (int) $news_id;
		
		$array = [];
		$sql = $this->NewsModel->newsImageListModel($news_id);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'image' => [
						'normal' => $row->news_image,
						'format' => $this->sultanImageControl(FILE_PATH_NEWS_GALLERY, $row->news_image)
					]
				];
			}
		}

		return $array;
	}
}
