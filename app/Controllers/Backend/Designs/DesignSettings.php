<?php
namespace App\Controllers\Backend\Designs;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Designs\DesignSettingsModel;

class DesignSettings extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $filePath;
	protected $DesignSettingsModel;

	public function __construct() {
		$this->table = 'design_settings';
		$this->tableLang = 'design_settings_lang';
		$this->pageUrl = ADMIN_URL_DESIGNS.'/'.ADMIN_URL_DESIGN_SETTINGS;
		$this->filePath = FILE_PATH_MAIN;
		$this->DesignSettingsModel = new DesignSettingsModel();
	}

	public function index() {

		$lang_array = [];
		$lang = $this->DesignSettingsModel->designSettingsLangModel();
		if (isNotNull($lang)) {
			foreach ($lang as $row) {
				$lang_array['data']['translations'][$row->lang_id]['header_facilities_link'] = $row->header_facilities_link;
				$lang_array['data']['translations'][$row->lang_id]['footer_promotion_film'] = $row->footer_promotion_film;
			}
		}

		$sql = $this->designSettings;

		return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
			'page_url' => $this->pageUrl,
			'lang' => $lang_array,
			'result' => [
				'home_page_banner_web_image_width' => $sql->home_page_banner_web_image_width,
				'home_page_banner_web_image_height' => $sql->home_page_banner_web_image_height,
				'home_page_banner_mobile_image_width' => $sql->home_page_banner_mobile_image_width,
				'home_page_banner_mobile_image_height' => $sql->home_page_banner_mobile_image_height,
				'project_thumb_image_width' => $sql->project_thumb_image_width,
				'project_thumb_image_height' => $sql->project_thumb_image_height,
				'project_medium_image_width' => $sql->project_medium_image_width,
				'project_medium_image_height' => $sql->project_medium_image_height,
				'project_big_image_width' => $sql->project_big_image_width,
				'project_big_image_height' => $sql->project_big_image_height,
				'popup_image_width' => $sql->popup_image_width,
				'popup_image_height' => $sql->popup_image_height,
				'popup_mobile_image_width' => $sql->popup_mobile_image_width,
				'popup_mobile_image_height' => $sql->popup_mobile_image_height,
				'service_image_width' => $sql->service_image_width,
				'service_image_height' => $sql->service_image_height,
				'news_medium_image_width' => $sql->news_medium_image_width,
				'news_medium_image_height' => $sql->news_medium_image_height,
				'news_big_image_width' => $sql->news_big_image_width,
				'news_big_image_height' => $sql->news_big_image_height,
				'events_thumb_image_width' => $sql->events_thumb_image_width,
				'events_thumb_image_height' => $sql->events_thumb_image_height,
				'events_big_image_width' => $sql->events_big_image_width,
				'events_big_image_height' => $sql->events_big_image_height,
				'municipal_council_image_width' => $sql->municipal_council_image_width,
				'municipal_council_image_height' => $sql->municipal_council_image_height,
				'council_member_image_width' => $sql->council_member_image_width,
				'council_member_image_height' => $sql->council_member_image_height,
				'vice_president_image_width' => $sql->vice_president_image_width,
				'vice_president_image_height' => $sql->vice_president_image_height,
				'strategic_plan_and_performance_image_width' => $sql->strategic_plan_and_performance_image_width,
				'strategic_plan_and_performance_image_height' => $sql->strategic_plan_and_performance_image_height,
				'directorates_person_image_width' => $sql->directorates_person_image_width,
				'directorates_person_image_height' => $sql->directorates_person_image_height,
				'paging_count' => $sql->paging_count,
				'logo' => isNotNull($sql->logo) ? base_url($this->filePath.'/'.$sql->logo) : NULL,
				'favicon' => isNotNull($sql->favicon) ? base_url($this->filePath.'/'.$sql->favicon) : NULL,
				'logo_footer' => isNotNull($sql->logo_footer) ? base_url($this->filePath.'/'.$sql->logo_footer) : NULL,
				'virtual_tour_link' => $sql->virtual_tour_link,
				'slider_status' => $sql->slider_status,
				'slider_arrow_show' => $sql->slider_arrow_show,
				'slider_arrow_color' => $sql->slider_arrow_color,
				'slider_bullet_show' => $sql->slider_bullet_show,
				'image_remove' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-image')
			]
		]);
	}

	public function update() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->DesignSettingsModel->designSettingsInfoModel(DESIGN_SETTING_ID);
				if (isNotNull($sql)) {

					$rules1 = ['logo' => []];
					$rules2 = ['favicon' => []];
					$rules3 = ['logo_footer' => []];

					/*****************************************************/

					// Image Upload Validation
					$fileLogo = $this->request->getFile('logo');
					if (isNotNull($fileLogo)) {
						$rules1 = [
							'logo' => [
								'label' => lang('AdminDesigns.designSettings.design.siteLogo.title'),
								'rules' => [
									'uploaded[logo]',
									'mime_in[logo,'.IMAGE_UPLOAD_MIME.']',
									'max_size[logo,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];
					}

					$fileFavicon = $this->request->getFile('favicon');
					if (isNotNull($fileFavicon)) {
						$rules2 = [
							'favicon' => [
								'label' => lang('AdminDesigns.designSettings.design.favicon.title'),
								'rules' => [
									'uploaded[favicon]',
									'mime_in[favicon,'.IMAGE_UPLOAD_MIME.']',
									'max_size[favicon,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];
					}

					$fileLogoFooter = $this->request->getFile('logo_footer');
					if (isNotNull($fileLogoFooter)) {
						$rules3 = [
							'logo_footer' => [
								'label' => lang('AdminDesigns.designSettings.design.footer.logo'),
								'rules' => [
									'uploaded[logo_footer]',
									'mime_in[logo_footer,'.IMAGE_UPLOAD_MIME.']',
									'max_size[logo_footer,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];
					}

					/*****************************************************/

					$rules = array_merge_recursive($rules1, $rules2, $rules3);

					if ($this->validate($rules)) {

						// Image Upload
						// Logo
						$fileLogoName = $sql->logo;
						if (isNotNull($fileLogo) && $fileLogo->isValid() && !$fileLogo->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->logo);

							$fileLogoName = 'logo_'.$fileLogo->getRandomName();
							$fileLogo->move($this->filePath, $fileLogoName);
						}

						// Favicon
						$fileFaviconName = $sql->favicon;
						if (isNotNull($fileFavicon) && $fileFavicon->isValid() && !$fileFavicon->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->favicon);

							$fileFaviconName = 'favicon_'.$fileFavicon->getRandomName();
							$fileFavicon->move($this->filePath, $fileFaviconName);
						}

						// Footer Logo
						$fileLogoFooterName = $sql->logo_footer;
						if (isNotNull($fileLogoFooter) && $fileLogoFooter->isValid() && !$fileLogoFooter->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->logo_footer);

							$fileLogoFooterName = 'logo_'.$fileLogoFooter->getRandomName();
							$fileLogoFooter->move($this->filePath, $fileLogoFooterName);
						}

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['logo'] = $fileLogoName;
							$data['favicon'] = $fileFaviconName;
							$data['logo_footer'] = $fileLogoFooterName;
							$data['virtual_tour_link'] = trim($this->request->getVar('form[virtual_tour_link]'));
						}

						$result = $this->general->updateModel($this->table, $data, ['design_setting_id' => DESIGN_SETTING_ID]);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {
									$lang_data = [
										'design_setting_id' => DESIGN_SETTING_ID,
										'lang_id' => $lang_id,
										'header_facilities_link' => trim($value['header_facilities_link']),
										'footer_promotion_film' => trim($value['footer_promotion_film'])
									];

									$langControlModel = $this->DesignSettingsModel->designSettingsLangControlModel(DESIGN_SETTING_ID, $lang_id);
									if (isNotNull($langControlModel)) {
										$this->general->updateModel($this->tableLang, $lang_data, ['design_setting_id' => DESIGN_SETTING_ID, 'lang_id' => $lang_id]);
									} else {
										$this->general->insertModel($this->tableLang, $lang_data);
									}
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminDesigns.result.edit.designSettings'));

							$ajax_message['success'] = TRUE;
							$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);

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

	public function removeImage() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$data = [];
				if ($this->request->getVar('action') == 'logo') {
					$data = ['logo' => ''];
				} elseif ($this->request->getVar('action') == 'favicon') {
					$data = ['favicon' => ''];
				} elseif ($this->request->getVar('action') == 'logo_footer') {
					$data = ['logo_footer' => ''];
				} elseif ($this->request->getVar('action') == 'promotionImage') {
					$data = ['home_page_promotion_image' => ''];
				}

				$result = $this->general->removeDropifyImageModel($this->table, $data, ['design_setting_id' => DESIGN_SETTING_ID], $this->filePath);
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
