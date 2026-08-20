<?php
namespace App\Controllers\Backend;
use CodeIgniter\Controller;

use App\Models\Backend\SettingModel;
use App\Models\Backend\Contents\DirectoratesModel;
use App\Models\Backend\News\NewsContentModel;
use App\Models\Backend\Events\EventsContentModel;

class Callback extends BaseController {

	protected $SettingModel;
	protected $DirectoratesModel;
	protected $NewsContentModel;
	protected $EventsContentModel;

	public function __construct() {
		$this->SettingModel = new SettingModel();
		$this->DirectoratesModel = new DirectoratesModel();
		$this->NewsContentModel = new NewsContentModel();
		$this->EventsContentModel = new EventsContentModel();
	}

	public function index() {

		// Select Auto Change
		if ($this->request->getVar('action') == 'select-auto-change') {
			$array = [];
			$parent = $this->request->getVar('parent');
			$get = $this->request->getVar('get');

			// Cities
			if ($get == 'city_id') {
				$cities = $this->SettingModel->citiesListModel($parent);
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
				$districts = $this->SettingModel->districtsListModel($parent);
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
			return $this->response->setJSON($ajax_message);
		}

		/*****************************************************/

		// Modal
		if ($this->request->getVar('action') == 'modal') {

			// Page URL
			$directoriesUrl = ADMIN_URL_CONTENTS.'/'.ADMIN_URL_DIRECTORATES;
			$newsUrl = ADMIN_URL_NEWS.'/'.ADMIN_URL_NEWS_CONTENT;
			$eventsUrl = ADMIN_URL_EVENTS.'/'.ADMIN_URL_EVENTS_CONTENT;

			// Directorates File Add
			if ($this->request->getVar('modalType') == 'directories_file_add') {
				$ajax_message['content'] = $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/layouts/modal/contents/directories-file-add.html', [
					'directorates_file_id' => $this->request->getVar('data'),
					'page_url' => $directoriesUrl,
					'list' => [
						'categories' => $this->DirectoratesModel->directorateCategoriesListModel($this->defaultLangId)
					]
				]);

				$ajax_message['title'] = lang('AdminContents.directorates.files.title');
				$ajax_message['success'] = TRUE;
			}

			/*****************************************************/

			// Directorates File Edit
			if ($this->request->getVar('modalType') == 'directories_file_edit') {

				$directorates_file_id = $this->request->getVar('data');
				$sql = $this->DirectoratesModel->directoratesFileInfoModel($directorates_file_id);
				if (isNotNull($sql)) {

					$lang_array = [];
					$lang = $this->DirectoratesModel->directoratesFileLangModel($sql->directorates_file_id);
					if (isNotNull($lang)) {
						foreach ($lang as $row) {
							$lang_array['data']['translations'][$row->lang_id]['directorates_file_name'] = $row->directorates_file_name;
						}
					}

					$ajax_message['content'] = $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/layouts/modal/contents/directories-file-edit.html', [
						'directorates_file_id' => $this->request->getVar('data'),
						'page_url' => $directoriesUrl,
						'lang' => $lang_array,
						'result' => [
							'directorate_category_id' => $sql->directorate_category_id,
							'file' => isNotNull($sql->directorates_file) ? base_url(FILE_PATH_DIRECTORATES.'/'.$sql->directorates_file) : NULL,
							'file_remove' => base_url(BACKEND_URL.'/'.$directoriesUrl.'/files/remove-file/'.$sql->directorates_file_id)
						],
						'list' => [
							'categories' => $this->DirectoratesModel->directorateCategoriesListModel($this->defaultLangId)
						]
					]);

					$ajax_message['title'] = lang('AdminContents.directorates.files.title');
					$ajax_message['success'] = TRUE;

				} else {
					$ajax_message['error'] = lang('Admin.error.description');
				}

			}

			/*****************************************************/

			// News Paragraphs Add
			if ($this->request->getVar('modalType') == 'news_paragraph_add') {
				$ajax_message['content'] = $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/layouts/modal/news/paragraph-add.html', [
					'news_paragraph_id' => $this->request->getVar('data'),
					'page_url' => $newsUrl
				]);

				$ajax_message['title'] = lang('AdminNews.news.paragraphs.title');
				$ajax_message['success'] = TRUE;
			}

			/*****************************************************/

			// News Paragraphs Edit
			if ($this->request->getVar('modalType') == 'news_paragraph_edit') {

				$news_paragraph_id = $this->request->getVar('data');
				$sql = $this->NewsContentModel->newsParagraphInfoModel($news_paragraph_id);
				if (isNotNull($sql)) {

					$lang_array = [];
					$lang = $this->NewsContentModel->newsParagraphLangModel($sql->news_paragraph_id);
					if (isNotNull($lang)) {
						foreach ($lang as $row) {
							$lang_array['data']['translations'][$row->lang_id]['news_paragraph_name'] = $row->news_paragraph_name;
							$lang_array['data']['translations'][$row->lang_id]['news_paragraph_description'] = $row->news_paragraph_description;
						}
					}

					$ajax_message['content'] = $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/layouts/modal/news/paragraph-edit.html', [
						'news_paragraph_id' => $this->request->getVar('data'),
						'page_url' => $newsUrl,
						'lang' => $lang_array,
						'result' => [
							'image' => isNotNull($sql->news_paragraph_image) ? base_url(FILE_PATH_NEWS.'/'.$sql->news_paragraph_image) : NULL,
							'image_remove' => base_url(BACKEND_URL.'/'.$newsUrl.'/paragraphs/remove-image/'.$sql->news_paragraph_id)
						]
					]);

					$ajax_message['title'] = lang('AdminNews.news.paragraphs.title');
					$ajax_message['success'] = TRUE;

				} else {
					$ajax_message['error'] = lang('Admin.error.description');
				}

			}

			/*****************************************************/

			// Events Paragraphs Add
			if ($this->request->getVar('modalType') == 'events_paragraph_add') {
				$ajax_message['content'] = $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/layouts/modal/events/paragraph-add.html', [
					'event_paragraph_id' => $this->request->getVar('data'),
					'page_url' => $eventsUrl
				]);

				$ajax_message['title'] = lang('AdminEvents.contents.paragraphs.title');
				$ajax_message['success'] = TRUE;
			}

			/*****************************************************/

			// Events Paragraphs Edit
			if ($this->request->getVar('modalType') == 'events_paragraph_edit') {

				$event_paragraph_id = $this->request->getVar('data');
				$sql = $this->EventsContentModel->eventParagraphInfoModel($event_paragraph_id);
				if (isNotNull($sql)) {

					$lang_array = [];
					$lang = $this->EventsContentModel->eventParagraphLangModel($sql->event_paragraph_id);
					if (isNotNull($lang)) {
						foreach ($lang as $row) {
							$lang_array['data']['translations'][$row->lang_id]['event_paragraph_name'] = $row->event_paragraph_name;
							$lang_array['data']['translations'][$row->lang_id]['event_paragraph_description'] = $row->event_paragraph_description;
						}
					}

					$ajax_message['content'] = $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/layouts/modal/events/paragraph-edit.html', [
						'event_paragraph_id' => $this->request->getVar('data'),
						'page_url' => $eventsUrl,
						'lang' => $lang_array,
						'result' => [
							'image' => isNotNull($sql->event_paragraph_image) ? base_url(FILE_PATH_EVENTS_BIG.'/'.$sql->event_paragraph_image) : NULL,
							'image_remove' => base_url(BACKEND_URL.'/'.$eventsUrl.'/paragraphs/remove-image/'.$sql->event_paragraph_id)
						]
					]);

					$ajax_message['title'] = lang('AdminEvents.contents.paragraphs.title');
					$ajax_message['success'] = TRUE;

				} else {
					$ajax_message['error'] = lang('Admin.error.description');
				}

			}

		}

		return $this->response->setJSON($ajax_message);

	}
}
