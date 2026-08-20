<?php
namespace App\Controllers\Backend\President;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\President\PresidentGeneralInformationModel;

class PresidentGeneralInformation extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $filePath;
	protected $PresidentGeneralInformationModel;

	public function __construct() {
		$this->table = 'president_general_information';
		$this->tableLang = 'president_general_information_lang';
		$this->pageUrl = ADMIN_URL_PRESIDENT.'/'.ADMIN_URL_PRESIDENT_GENERAL_INFORMATION;
		$this->filePath = FILE_PATH_PRESIDENT;
		$this->PresidentGeneralInformationModel = new PresidentGeneralInformationModel();
	}

	public function informations() {
		$data = [];
		$sql = $this->PresidentGeneralInformationModel->presidentGeneralInformationModel(PRESIDENT_GENERAL_INFORMATION_ID);
		if (isNotNull($sql)) {
			$data = [
				'president_name_surname' => $sql->president_name_surname,
				'president_image' => isNotNull($sql->president_image) ? base_url($this->filePath.'/'.$sql->president_image) : NULL,
				'president_image_mobile' => isNotNull($sql->president_image_mobile) ? base_url($this->filePath.'/'.$sql->president_image_mobile) : NULL,
				'image_remove' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-image/'.$sql->president_general_information_id),
				'president_facebook' => $sql->president_facebook,
				'president_twitter' => $sql->president_twitter,
				'president_instagram' => $sql->president_instagram,
				'president_youtube' => $sql->president_youtube
			];
		}

		return $data;
	}

	public function index() {

		$lang_array = [];
		$lang = $this->PresidentGeneralInformationModel->presidentGeneralInformationLangModel(PRESIDENT_GENERAL_INFORMATION_ID);
		if (isNotNull($lang)) {
			foreach ($lang as $row) {
				$lang_array['data']['translations'][$row->lang_id]['president_general_information_sub_title'] = $row->president_general_information_sub_title;
				$lang_array['data']['translations'][$row->lang_id]['president_general_information_link'] = $row->president_general_information_link;
			}
		}

		return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
			'page_url' => $this->pageUrl,
			'lang' => $lang_array,
			'result' => $this->informations()
		]);
	}

	public function update() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->PresidentGeneralInformationModel->presidentGeneralInformationModel(PRESIDENT_GENERAL_INFORMATION_ID);
				if (isNotNull($sql)) {

					$rules1 = [
						'president_name_surname' => [
							'label' => lang('AdminPresident.generalInformation.general.nameSurname'),
							'rules' => 'required'
						]
					];

					$rules2 = [];
					$file_web = $this->request->getFile('president_image');
					if (isNotNull($file_web)) {
						$rules2 = [
							'president_image' => [
								'label' => lang('AdminPresident.generalInformation.general.image.web'),
								'rules' => [
									'uploaded[president_image]',
									'mime_in[president_image,'.IMAGE_UPLOAD_MIME.']',
									'max_size[president_image,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];
					}

					$rules3 = [];
					$file_mobile = $this->request->getFile('president_image_mobile');
					if (isNotNull($file_mobile)) {
						$rules3 = [
							'president_image_mobile' => [
								'label' => lang('AdminPresident.generalInformation.general.image.mobile'),
								'rules' => [
									'uploaded[president_image_mobile]',
									'mime_in[president_image_mobile,'.IMAGE_UPLOAD_MIME.']',
									'max_size[president_image_mobile,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];
					}

					$rules = array_merge_recursive($rules1, $rules2, $rules3);

					/*****************************************************/

					if ($this->validate($rules)) {

						// Web Image Upload
						$fileWebName = $sql->president_image;
						$fileWebNameResult = '';
						if (isNotNull($file_web) && $file_web->isValid() && !$file_web->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->president_image);

							$fileWebName = slug($this->request->getVar('president_name_surname')).'_'.$file_web->getRandomName();
							$fileWebNameResult = $this->uploadSingleFile($file_web, $this->filePath, $fileWebName);
						}

						// Mobile Image Upload
						$fileMobileName = $sql->president_image_mobile;
						$fileMobileNameResult = '';
						if (isNotNull($file_mobile) && $file_mobile->isValid() && !$file_mobile->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->president_image_mobile);

							$fileMobileName = slug($this->request->getVar('president_name_surname')).'_'.$file_mobile->getRandomName();
							$file_mobile->move($this->filePath, $fileMobileName);
						}

						if ($fileWebNameResult == NULL) {

							$data = [
								'president_name_surname' => trim($this->request->getVar('president_name_surname')),
								'president_image' => $fileWebName,
								'president_image_mobile' => $fileMobileName,
								'president_facebook' => trim($this->request->getVar('president_facebook')),
								'president_twitter' => trim($this->request->getVar('president_twitter')),
								'president_instagram' => trim($this->request->getVar('president_instagram')),
								'president_youtube' => trim($this->request->getVar('president_youtube'))
							];

							$result = $this->general->updateModel($this->table, $data, ['president_general_information_id' => PRESIDENT_GENERAL_INFORMATION_ID]);
							if ($result !== FALSE) {

								// Lang
								if (isNotNull($this->request->getVar('lang'))) {
									foreach ($this->request->getVar('lang') as $lang_id => $value) {
										$lang_data = [
											'president_general_information_id' => PRESIDENT_GENERAL_INFORMATION_ID,
											'lang_id' => $lang_id,
											'president_general_information_sub_title' => trim($value['president_general_information_sub_title']),
											'president_general_information_link' => trim($value['president_general_information_link'])
										];

										$langControlModel = $this->PresidentGeneralInformationModel->presidentGeneralInformationLangControlModel(PRESIDENT_GENERAL_INFORMATION_ID, $lang_id);
										if (isNotNull($langControlModel)) {
											$this->general->updateModel($this->tableLang, $lang_data, ['president_general_information_id' => PRESIDENT_GENERAL_INFORMATION_ID, 'lang_id' => $lang_id]);
										} else {
											$this->general->insertModel($this->tableLang, $lang_data);
										}
									}
								}

								// Flash Data
								session()->setFlashdata('flashDataMessageSuccess', lang('AdminPresident.result.edit.generalInformation'));

								$ajax_message['success'] = TRUE;
								$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);

							} else {
								$ajax_message['error'] = lang('Admin.error.update');
							}

						} else {
							$ajax_message['error'] = $fileWebNameResult;
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

	public function removeImage(int $president_general_information_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$data = [];
				if ($this->request->getVar('action') == 'web') {
					$data = ['president_image' => ''];
				} elseif ($this->request->getVar('action') == 'mobile') {
					$data = ['president_image_mobile' => ''];
				}

				$result = $this->general->removeDropifyImageModel($this->table, $data, ['president_general_information_id' => $president_general_information_id], $this->filePath);
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
