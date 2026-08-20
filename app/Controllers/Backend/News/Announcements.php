<?php
namespace App\Controllers\Backend\News;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\News\AnnouncementsModel;
use App\Models\Backend\DatatableModel;
use App\Libraries\PushNotifications;

class Announcements extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $AnnouncementsModel;
	protected $DatatableModel;
	protected $PushNotifications;

	public function __construct() {
		$this->table = 'announcements';
		$this->tableLang = 'announcements_lang';
		$this->pageUrl = ADMIN_URL_NEWS.'/'.ADMIN_URL_ANNOUNCEMENTS;
		$this->AnnouncementsModel = new AnnouncementsModel();
		$this->DatatableModel = new DatatableModel();
		$this->PushNotifications = new PushNotifications();
	}

	public function index() {
		return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
			'page_name' => 'index',
			'page_url' => $this->pageUrl,
			'datatable_url' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/datatable')
		]);
	}

	public function datatable() {
		if ($this->request->isAJAX()) {
			$column = ['status', 'status_mobile', 'push_notification', 'announcement_name', 'announcement_created_date', 'announcement_updated_date', NULL];
			$search = [];
			$orderBy = ['announcement_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = set_status($row->status_mobile);
					$array[] = set_status($row->push_notification);
					$array[] = character_limiter($row->announcement_name, 70);
					$array[] = dateFormat($row->announcement_created_date, 'd-m-Y H:i:s');
					$array[] = $row->announcement_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->announcement_updated_date, 'd-m-Y H:i:s') : NULL;
					$array[] = action_links($row->announcement_id, ['edit', 'delete'], $this->pageUrl);
					$data[] = $array;
				}
			}

			$output = [
				'draw' => $this->request->getVar('draw'),
				'recordsTotal' => $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getNumRows'),
				'recordsFiltered' => $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'countAllResults'),
				'data' => $data
			];

			return $this->response->setJSON($output);
		}
	}

	public function add() {
		return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
			'page_name' => 'add',
			'page_url' => $this->pageUrl,
			'list' => [
				'directorates' => $this->AnnouncementsModel->directoratesListModel($this->defaultLangId)
			]
		]);
	}

	public function insert() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules1 = [
					'form.status' => [
						'label' => lang('AdminNews.announcements.general.status'),
						'rules' => 'required'
					],
					'form.status_mobile' => [
						'label' => lang('Admin.mobile'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.announcement_name' => [
						'label' => lang('AdminNews.announcements.general.name'),
						'rules' => 'required'
					]
				];

				$rules2 = [];
				if (isNotNull($this->request->getVar('lang['.$this->defaultLangId.'][announcement_link]'))) {
					$rules2 = [
						'lang.'.$this->defaultLangId.'.announcement_link' => [
							'label' => lang('AdminNews.announcements.general.link'),
							'rules' => 'max_length['.WEB_ADDRESS_CHARACTER_LIMITER.']|valid_url_strict'
						]
					];
				}

				$rules = array_merge($rules1, $rules2);

				if ($this->validate($rules)) {

					// Api
					if (isNotNull($this->request->getVar('form[push_notification]'))) {
						$this->PushNotifications->index(trim($this->request->getVar('lang['.$this->defaultLangId.'][announcement_mobile_name]')), trim($this->request->getVar('lang['.$this->defaultLangId.'][announcement_mobile_description]')));
					}

					/**************************************************/

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['push_notification'] = isNotNull($this->request->getVar('form[push_notification]')) ? $this->request->getVar('form[push_notification]') : FALSE;

						// Directorates
						$data['directorates_id'] = '';
						if (isNotNull($this->request->getVar('form[directorates_id]'))) {
							$directorates = NULL;
							foreach ($this->request->getVar('form[directorates_id]') as $row) {
								$directorates .= $row.',';
							}

							$data['directorates_id'] = reduce_multiples($directorates, ',', TRUE);
						}

						$data['announcement_created_date'] = nowDate();
					}

					$result = $this->general->insertModel($this->table, $data);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {

								// Slug
								if (isNotNull($this->request->getVar('lang['.$this->defaultLangId.'][announcement_slug]'))) {
									$slug = slug($value['announcement_slug']);
								} else {
									$slug = isNotNull($value['announcement_name']) ? slug($value['announcement_name']) : $this->request->getVar('lang['.$this->defaultLangId.'][announcement_name]');
								}

								$lang_data = [
									'announcement_id' => $result,
									'lang_id' => $lang_id,
									'announcement_name' => upper($value['announcement_name']),
									'announcement_link' => trim($value['announcement_link']),
									'announcement_department' => trim($value['announcement_department']),
									'announcement_description' => $value['announcement_description'],
									'announcement_slug' => $slug,
									'announcement_mobile_name' => $value['announcement_mobile_name'],
									'announcement_mobile_description' => $value['announcement_mobile_description']
								];

								$this->general->insertModel($this->tableLang, $lang_data);
							}
						}

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminNews.result.add.announcements', [$this->request->getVar('lang['.$this->defaultLangId.'][announcement_name]')]));

						$ajax_message['success'] = TRUE;
						$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);

					} else {
						$ajax_message['error'] = lang('Admin.error.insert');
					}

				} else {
					$ajax_message['error'] = $this->validator->listErrors();
				}

			} else {
				$ajax_message['error'] = lang('Admin.error.description');
			}
		} else {
			$ajax_message['error'] = lang('Admin.error.ajax');
		}

		return $this->response->setJSON($ajax_message);
	}

	public function edit(int $announcement_id) {
		$sql = $this->AnnouncementsModel->announcementInfoModel($announcement_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->AnnouncementsModel->announcementLangModel($announcement_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['announcement_name'] = $row->announcement_name;
					$lang_array['data']['translations'][$row->lang_id]['announcement_link'] = $row->announcement_link;
					$lang_array['data']['translations'][$row->lang_id]['announcement_department'] = $row->announcement_department;
					$lang_array['data']['translations'][$row->lang_id]['announcement_description'] = $row->announcement_description;
					$lang_array['data']['translations'][$row->lang_id]['announcement_slug'] = $row->announcement_slug;
					$lang_array['data']['translations'][$row->lang_id]['announcement_mobile_name'] = $row->announcement_mobile_name;
					$lang_array['data']['translations'][$row->lang_id]['announcement_mobile_description'] = $row->announcement_mobile_description;
				}
			}

			// Directorates (Selected)
			$directorates_id = [];
			if (isNotNull($sql->directorates_id)) {
				$explode = explode(',', $sql->directorates_id);
				foreach ($explode as $row) {
					$directorates_id[] = $row;
				}
			}

			// Directorates
			$directorates = [];
			$directorates_sql = $this->AnnouncementsModel->directoratesListModel($this->defaultLangId);
			if (isNotNull($directorates_sql)) {
				foreach ($directorates_sql as $row) {

					// Selected
					$selected = NULL;
					if (in_array($row->directorates_id, $directorates_id)) {
						$selected = 'selected';
					}

					$directorates[] = [
						'directorates_id' => $row->directorates_id,
						'directorates_name' => $row->directorates_name,
						'selected' => $selected
					];
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'announcement_id' => $sql->announcement_id,
					'status' => $sql->status,
					'status_mobile' => $sql->status_mobile,
					'push_notification' => $sql->push_notification
				],
				'list' => [
					'directorates' => $directorates
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $announcement_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules1 = [
					'form.status' => [
						'label' => lang('AdminNews.announcements.general.status'),
						'rules' => 'required'
					],
					'form.status_mobile' => [
						'label' => lang('Admin.mobile'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.announcement_name' => [
						'label' => lang('AdminNews.announcements.general.question'),
						'rules' => 'required'
					]
				];

				$rules2 = [];
				if (isNotNull($this->request->getVar('lang['.$this->defaultLangId.'][announcement_link]'))) {
					$rules2 = [
						'lang.'.$this->defaultLangId.'.announcement_link' => [
							'label' => lang('AdminNews.announcements.general.link'),
							'rules' => 'max_length['.WEB_ADDRESS_CHARACTER_LIMITER.']|valid_url_strict'
						]
					];
				}

				$rules = array_merge($rules1, $rules2);

				if ($this->validate($rules)) {

					// Api
					if (isNotNull($this->request->getVar('form[push_notification]'))) {
						$this->PushNotifications->index(trim($this->request->getVar('lang['.$this->defaultLangId.'][announcement_mobile_name]')), trim($this->request->getVar('lang['.$this->defaultLangId.'][announcement_mobile_description]')));
					}

					/**************************************************/

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['push_notification'] = isNotNull($this->request->getVar('form[push_notification]')) ? $this->request->getVar('form[push_notification]') : FALSE;

						// Directorates
						$data['directorates_id'] = '';
						if (isNotNull($this->request->getVar('form[directorates_id]'))) {
							$directorates = NULL;
							foreach ($this->request->getVar('form[directorates_id]') as $row) {
								$directorates .= $row.',';
							}

							$data['directorates_id'] = reduce_multiples($directorates, ',', TRUE);
						}

						$data['announcement_updated_date'] = nowDate();
					}

					$result = $this->general->updateModel($this->table, $data, ['announcement_id' => $announcement_id]);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {

								// Slug
								if (isNotNull($this->request->getVar('lang['.$this->defaultLangId.'][announcement_slug]'))) {
									$slug = slug($value['announcement_slug']);
								} else {
									$slug = slug($value['announcement_name']);
								}

								$lang_data = [
									'announcement_id' => $announcement_id,
									'lang_id' => $lang_id,
									'announcement_name' => upper($value['announcement_name']),
									'announcement_link' => trim($value['announcement_link']),
									'announcement_department' => trim($value['announcement_department']),
									'announcement_description' => $value['announcement_description'],
									'announcement_slug' => $slug,
									'announcement_mobile_name' => $value['announcement_mobile_name'],
									'announcement_mobile_description' => $value['announcement_mobile_description']
								];

								$langControlModel = $this->AnnouncementsModel->announcementLangControlModel($announcement_id, $lang_id);
								if (isNotNull($langControlModel)) {
									$this->general->updateModel($this->tableLang, $lang_data, ['announcement_id' => $announcement_id, 'lang_id' => $lang_id]);
								} else {
									$this->general->insertModel($this->tableLang, $lang_data);
								}
							}
						}

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminNews.result.edit.announcements', [$this->request->getVar('lang['.$this->defaultLangId.'][announcement_name]')]));

						$ajax_message['success'] = TRUE;

						if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
							$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$announcement_id);
						} else {
							$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);
						}

					} else {
						$ajax_message['error'] = lang('Admin.error.update');
					}

				} else {
					$ajax_message['error'] = $this->validator->listErrors();
				}

			} else {
				$ajax_message['error'] = lang('Admin.error.description');
			}
		} else {
			$ajax_message['error'] = lang('Admin.error.ajax');
		}

		return $this->response->setJSON($ajax_message);
	}

	public function delete(int $announcement_id) {
		$sql = $this->AnnouncementsModel->announcementInfoModel($announcement_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['announcement_id' => $announcement_id]);
			if ($delete) {

				// Lang
				$lang = $this->AnnouncementsModel->announcementLangModel($announcement_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['announcement_id' => $row->announcement_id]);
					}
				}

				$ajax_message['success'] = TRUE;
			} else {
				$ajax_message['error'] = lang('Admin.deleteError');
			}

		} else {
			$ajax_message['error'] = lang('Admin.noRecords');
		}

		return $this->response->setJSON($ajax_message);
	}
}
