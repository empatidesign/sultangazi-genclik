<?php
namespace App\Controllers\Backend\Multimedia;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Multimedia\VideoGalleryModel;
use App\Models\Backend\DatatableModel;

class VideoGallery extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $filePath;
	protected $VideoGalleryModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'video_gallery';
		$this->tableLang = 'video_gallery_lang';
		$this->pageUrl = ADMIN_URL_MULTIMEDIA.'/'.ADMIN_URL_MULTIMEDIA_VIDEO_GALLERY;
		$this->filePath = FILE_PATH_VIDEO_GALLERY;
		$this->VideoGalleryModel = new VideoGalleryModel();
		$this->DatatableModel = new DatatableModel();
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
			$column = ['status', NULL, 'video_gallery_name', 'video_gallery_date', 'video_gallery_created_date', 'video_gallery_updated_date', NULL];
			$search = [];
			$orderBy = ['video_gallery_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = set_image($this->filePath.'/'.$row->video_gallery_image, 100);
					$array[] = $row->video_gallery_name;
					$array[] = dateFormat($row->video_gallery_date, 'd-m-Y');
					$array[] = dateFormat($row->video_gallery_created_date, 'd-m-Y H:i:s');
					$array[] = $row->video_gallery_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->video_gallery_updated_date, 'd-m-Y H:i:s') : NULL;
					$array[] = action_links($row->video_gallery_id, ['edit', 'delete'], $this->pageUrl);
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
			'page_url' => $this->pageUrl
		]);
	}

	public function insert() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules = [
					'form.status' => [
						'label' => lang('AdminMultimedia.videoGallery.general.status'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.video_gallery_name' => [
						'label' => lang('AdminMultimedia.videoGallery.general.name'),
						'rules' => 'required'
					],
					'form.video_gallery_link' => [
						'label' => lang('AdminMultimedia.videoGallery.general.link'),
						'rules' => 'required'
					],
					'form.video_gallery_date' => [
						'label' => lang('AdminMultimedia.videoGallery.general.date'),
						'rules' => 'required'
					]
				];

				/*****************************************************/

				// Image Upload Validation
				$file = $this->request->getFile('video_gallery_image');
				if (isNotNull($file)) {
					$rulesFile = [
						'video_gallery_image' => [
							'label' => lang('AdminMultimedia.videoGallery.general.image'),
							'rules' => [
								'uploaded[video_gallery_image]',
								'mime_in[video_gallery_image,'.IMAGE_UPLOAD_MIME.']',
								'max_size[video_gallery_image,'.IMAGE_UPLOAD_SIZE.']'
							]
						]
					];

					$rules = array_merge($rules, $rulesFile);
				}

				/*****************************************************/

				if ($this->validate($rules)) {

					// File Upload
					$fileName = '';
					if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
						$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][video_gallery_name]')).'_'.$file->getRandomName();
						$file->move($this->filePath, $fileName);
					}

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['video_gallery_date'] = dateFormat($this->request->getVar('form[video_gallery_date]'), 'Y-m-d');
						$data['video_gallery_link'] = trim($this->request->getVar('form[video_gallery_link]'));
						$data['video_gallery_image'] = $fileName;
						$data['video_gallery_created_date'] = nowDate();
					}

					$result = $this->general->insertModel($this->table, $data);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {
								$lang_data = [
									'video_gallery_id' => $result,
									'lang_id' => $lang_id,
									'video_gallery_name' => trim($value['video_gallery_name'])
								];

								$this->general->insertModel($this->tableLang, $lang_data);
							}
						}

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminMultimedia.result.add.videoGallery', [$this->request->getVar('lang['.$this->defaultLangId.'][video_gallery_name]')]));

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

	public function edit(int $video_gallery_id) {
		$sql = $this->VideoGalleryModel->videoGalleryInfoModel($video_gallery_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->VideoGalleryModel->videoGalleryLangModel($video_gallery_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['video_gallery_name'] = $row->video_gallery_name;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'video_gallery_id' => $sql->video_gallery_id,
					'status' => $sql->status,
					'video_gallery_link' => $sql->video_gallery_link,
					'video_gallery_date' => dateFormat($sql->video_gallery_date, 'd/m/Y'),
					'image' => isNotNull($sql->video_gallery_image) ? base_url($this->filePath.'/'.$sql->video_gallery_image) : NULL,
					'image_remove' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-image/'.$sql->video_gallery_id)
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $video_gallery_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->VideoGalleryModel->videoGalleryInfoModel($video_gallery_id);
				if (isNotNull($sql)) {

					$rules = [
						'form.status' => [
							'label' => lang('AdminMultimedia.videoGallery.general.status'),
							'rules' => 'required'
						],
						'lang.'.$this->defaultLangId.'.video_gallery_name' => [
							'label' => lang('AdminMultimedia.videoGallery.general.name'),
							'rules' => 'required'
						],
						'form.video_gallery_link' => [
							'label' => lang('AdminMultimedia.videoGallery.general.link'),
							'rules' => 'required'
						],
						'form.video_gallery_date' => [
							'label' => lang('AdminMultimedia.videoGallery.general.date'),
							'rules' => 'required'
						]
					];

					/*****************************************************/

					// File Upload Validation
					$file = $this->request->getFile('video_gallery_image');
					if (isNotNull($file)) {
						$rulesFile = [
							'video_gallery_image' => [
								'label' => lang('AdminMultimedia.videoGallery.general.image'),
								'rules' => [
									'uploaded[video_gallery_image]',
									'mime_in[video_gallery_image,'.IMAGE_UPLOAD_MIME.']',
									'max_size[video_gallery_image,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];

						$rules = array_merge($rules, $rulesFile);
					}

					/*****************************************************/

					if ($this->validate($rules)) {

						// File Upload
						$fileName = $sql->video_gallery_image;
						if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->video_gallery_image);

							$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][video_gallery_name]')).'_'.$file->getRandomName();
							$file->move($this->filePath, $fileName);
						}

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['video_gallery_link'] = trim($this->request->getVar('form[video_gallery_link]'));
							$data['video_gallery_date'] = dateFormat($this->request->getVar('form[video_gallery_date]'), 'Y-m-d');
							$data['video_gallery_image'] = $fileName;
							$data['video_gallery_updated_date'] = nowDate();
						}

						$result = $this->general->updateModel($this->table, $data, ['video_gallery_id' => $video_gallery_id]);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {
									$lang_data = [
										'video_gallery_id' => $video_gallery_id,
										'lang_id' => $lang_id,
										'video_gallery_name' => trim($value['video_gallery_name'])
									];

									$langControlModel = $this->VideoGalleryModel->videoGalleryLangControlModel($video_gallery_id, $lang_id);
									if (isNotNull($langControlModel)) {
										$this->general->updateModel($this->tableLang, $lang_data, ['video_gallery_id' => $video_gallery_id, 'lang_id' => $lang_id]);
									} else {
										$this->general->insertModel($this->tableLang, $lang_data);
									}
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminMultimedia.result.edit.videoGallery', [$this->request->getVar('lang['.$this->defaultLangId.'][video_gallery_name]')]));

							$ajax_message['success'] = TRUE;

							if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
								$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$video_gallery_id);
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
					$ajax_message['error'] = lang('Admin.error.noRecord');
				}

			} else {
				$ajax_message['error'] = lang('Admin.error.description');
			}
		} else {
			$ajax_message['error'] = lang('Admin.error.ajax');
		}

		return $this->response->setJSON($ajax_message);
	}

	public function delete(int $video_gallery_id) {
		$sql = $this->VideoGalleryModel->videoGalleryInfoModel($video_gallery_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['video_gallery_id' => $video_gallery_id]);
			if ($delete) {
				// Unlink
				unlinkFile($this->filePath, $sql->video_gallery_image);

				// Lang
				$lang = $this->VideoGalleryModel->videoGalleryLangModel($video_gallery_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['video_gallery_id' => $row->video_gallery_id]);
					}
				}

				$ajax_message['success'] = TRUE;
			} else {
				$ajax_message['error'] = lang('Admin.error.delete');
			}

		} else {
			$ajax_message['error'] = lang('Admin.error.noRecord');
		}

		return $this->response->setJSON($ajax_message);
	}

	public function removeImage(int $video_gallery_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$result = $this->general->removeDropifyImageModel($this->table, ['video_gallery_image' => ''], ['video_gallery_id' => $video_gallery_id], $this->filePath);
				if ($result == TRUE) {
					$ajax_message['success'] = TRUE;
				} else {
					$ajax_message['error'] = lang('Admin.error.description');
				}

			} else {
				$ajax_message['error'] = lang('Admin.error.description');
			}
		} else {
			$ajax_message['error'] = lang('Admin.error.ajax');
		}

		return $this->response->setJSON($ajax_message);
	}
}
