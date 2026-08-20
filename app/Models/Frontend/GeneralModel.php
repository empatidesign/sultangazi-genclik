<?php
namespace App\Models\Frontend;
use CodeIgniter\Model;

class GeneralModel extends Model {
	public function getSettingsModel(int $lang_id) {
		$query = $this->db->table('settings');
		$query->select('settings.*,
						settings_lang.site_footer,
						settings_lang.cookies_description,
						contracts_lang.contract_id,
						contracts_lang.contract_slug');
		$query->join('settings_lang', 'settings_lang.setting_id = settings.setting_id AND settings_lang.lang_id = '.$lang_id, 'left');
		$query->join('contracts_lang', 'contracts_lang.contract_id = settings.cookies_link', 'left');
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function getDesignSettingsModel(int $lang_id) {
		$query = $this->db->table('design_settings');
		$query->select('design_settings.*,
										design_settings_lang.header_facilities_link,
										design_settings_lang.footer_promotion_film');
		$query->join('design_settings_lang', 'design_settings_lang.design_setting_id = design_settings.design_setting_id AND design_settings_lang.lang_id = '.$lang_id, 'left');

		$query->limit(1);

		return $query->get()->getRow();
	}

	public function getSocialMediaModel() {
		$query = $this->db->table('social_media');
		$query->select('*');
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function getTrackingCodesModel() {
		$query = $this->db->table('tracking_codes');
		$query->select('*');
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function getContactInformationModel(int $lang_id) {
		$appDb = db_connect('application');
		$query = $appDb->table('contact_information');
		$query->select('contact_information.*,
						contact_information_lang.contact_title,
						contact_information_lang.contact_address,
						contact_information_lang.contact_working_hours');
		$query->join('contact_information_lang', 'contact_information_lang.contact_id = contact_information.contact_id AND contact_information_lang.lang_id = '.$lang_id, 'left');

		$query->where('contact_information.contact_default', FORM_ACTIVE_NUMBER);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function getPresidentGeneralInformationsModel(int $lang_id) {
		$query = $this->db->table('president_general_information');
		$query->select('president_general_information.*,
						president_general_information_lang.president_general_information_sub_title,
						president_general_information_lang.president_general_information_link');
		$query->join('president_general_information_lang', 'president_general_information_lang.president_general_information_id = president_general_information.president_general_information_id AND president_general_information_lang.lang_id = '.$lang_id, 'left');
		$query->where('president_general_information.president_general_information_id', PRESIDENT_GENERAL_INFORMATION_ID);
		$query->limit(1);

		return $query->get()->getRow();
	}

	/*****************************************************/

	public function countryListModel() {
		$query = $this->db->table('countries');
		$query->select('country_id,
						country_default,
						country_name');

		$query->orderBy('country_name', 'ASC');

		return $query->get()->getResult();
	}

	public function countryInfoModel(int $country_id) {
		$query = $this->db->table('countries');
		$query->select('country_name');

		$query->where('country_id', $country_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function citiesListModel(int $country_id) {
		$query = $this->db->table('cities');
		$query->select('city_id,
						city_name');

		$query->where('status', FORM_ACTIVE_NUMBER);
		$query->where('country_id', $country_id);
		$query->orderBy('city_name', 'ASC');

		return $query->get()->getResult();
	}

	public function citiesInfoModel(int $city_id) {
		$query = $this->db->table('cities');
		$query->select('city_name');

		$query->where('city_id', $city_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function districtsListModel(int $city_id) {
		$query = $this->db->table('districts');
		$query->select('district_id,
						district_name');

		$query->where('status', FORM_ACTIVE_NUMBER);
		$query->where('city_id', $city_id);
		$query->orderBy('district_name', 'ASC');

		return $query->get()->getResult();
	}

	public function districtsInfoModel(int $district_id) {
		$query = $this->db->table('districts');
		$query->select('district_name');

		$query->where('district_id', $district_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	/*****************************************************/

	public function languagesInfoModel(string $lang_code = NULL) {
		$query = $this->db->table('languages');
		$query->select('languages.lang_id,
						LOWER(languages.lang_code) AS lang_code,
						languages.lang_set_locale,
						settings.frontend_lang_default');
		$query->join('settings', 'settings.frontend_lang_default = languages.lang_id', 'left');

		if (isNotNull($lang_code)) {
			$query->where('languages.lang_code', $lang_code);
		} else {
			$query->where('languages.lang_id = (select frontend_lang_default from settings where frontend_lang_default = languages.lang_id)');
		}

		$query->where('languages.lang_status', FORM_ACTIVE_NUMBER);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function languagesDefaultModel() {
		$query = $this->db->table('languages');
		$query->select('languages.*');
		$query->join('settings', 'settings.frontend_lang_default = languages.lang_id', 'left');

		if (session()->has('webLang')) {
			$query->where('languages.lang_code', session()->get('webLang'));
		} else {
			$query->where('languages.lang_id = (select frontend_lang_default from settings where frontend_lang_default = languages.lang_id)');
		}

		$query->where('languages.lang_status', FORM_ACTIVE_NUMBER);
		$query->orderBy('languages.lang_id', 'ASC');
		$query->limit(1);

		$result = $query->get()->getRow();

		if ($result) {
			return [
				$result->lang_id,
				$result->lang_code,
				$result->lang_title,
				$result->lang_percentage_location,
				$result->lang_icon
			];
		}
	}

	public function languagesListModel() {
		$query = $this->db->table('languages');
		$query->select('languages.*,
						LOWER(languages.lang_code) AS lang_code,
						settings.frontend_lang_default,
						(CASE WHEN languages.lang_id = settings.frontend_lang_default THEN ""
																																					ELSE languages.lang_code END) AS lang_code_end');
		$query->join('settings', 'settings.frontend_lang_default = languages.lang_id', 'left');

		$query->where('languages.lang_status', FORM_ACTIVE_NUMBER);
		$query->orderBy('languages.lang_id', 'ASC');

		return $query->get()->getResult();
	}

	public function languagesFrontendDefaultModel() {
		$query = $this->db->table('languages');
		$query->select('languages.lang_code');
		$query->join('settings', 'settings.frontend_lang_default = languages.lang_id', 'left');

		$query->limit(1);

		return $query->get()->getRow();
	}

	public function langPagesInfoModel(int $lang_id = NULL, int $page_id = NULL) {
		$query = $this->db->table('pages_lang');
		$query->select('page_id,
						page_slug');

		if (isNotNull($lang_id)) {
			$query->where('lang_id', $lang_id);
		}

		if (isNotNull($page_id)) {
			$query->where('page_id', $page_id);
		}

		$query->limit(1);
		return $query->get()->getRow();
	}

	public function langSultangaziContentsInfoModel(int $lang_id = NULL, int $content_id = NULL) {
		$query = $this->db->table('sultangazi_contents_lang');
		$query->select('content_id,
						content_slug');

		if (isNotNull($lang_id)) {
			$query->where('lang_id', $lang_id);
		}

		if (isNotNull($content_id)) {
			$query->where('content_id', $content_id);
		}

		$query->limit(1);
		return $query->get()->getRow();
	}

	public function langContractsInfoModel(int $lang_id = NULL, int $contract_id = NULL) {
		$query = $this->db->table('contracts_lang');
		$query->select('contract_id,
						contract_slug');

		if (isNotNull($lang_id)) {
			$query->where('lang_id', $lang_id);
		}

		if (isNotNull($contract_id)) {
			$query->where('contract_id', $contract_id);
		}

		$query->limit(1);
		return $query->get()->getRow();
	}

	public function langServicesDetailInfoModel(int $lang_id = NULL, int $service_id = NULL) {
		$query = $this->db->table('services_lang');
		$query->select('services_lang.service_id,
						services_lang.service_slug');
		$query->join('services', 'services.service_id = services_lang.service_id', 'left');

		$query->where('services.service_type', SERVICES_TYPE_3);

		if (isNotNull($lang_id)) {
			$query->where('services_lang.lang_id', $lang_id);
		}

		if (isNotNull($service_id)) {
			$query->where('services_lang.service_id', $service_id);
		}

		$query->limit(1);
		return $query->get()->getRow();
	}

	public function langNewsDetailInfoModel(int $lang_id = NULL, int $news_id = NULL) {
		$query = $this->db->table('news_lang');
		$query->select('news_id,
						news_slug');

		if (isNotNull($lang_id)) {
			$query->where('lang_id', $lang_id);
		}

		if (isNotNull($news_id)) {
			$query->where('news_id', $news_id);
		}

		$query->limit(1);
		return $query->get()->getRow();
	}

	public function langProjectCategoryDetailInfoModel(int $lang_id = NULL, int $project_category_id = NULL) {
		$query = $this->db->table('project_category_lang');
		$query->select('project_category_id,
						project_category_slug');

		if (isNotNull($lang_id)) {
			$query->where('lang_id', $lang_id);
		}

		if (isNotNull($project_category_id)) {
			$query->where('project_category_id', $project_category_id);
		}

		$query->limit(1);
		return $query->get()->getRow();
	}

	public function langProjectDetailInfoModel(int $lang_id = NULL, int $project_id = NULL) {
		$query = $this->db->table('projects_lang');
		$query->select('project_id,
						project_slug');

		if (isNotNull($lang_id)) {
			$query->where('lang_id', $lang_id);
		}

		if (isNotNull($project_id)) {
			$query->where('project_id', $project_id);
		}

		$query->limit(1);
		return $query->get()->getRow();
	}

	public function langEventsDetailInfoModel(int $lang_id = NULL, int $event_id = NULL) {
		$query = $this->db->table('events_lang');
		$query->select('event_id,
						event_slug');

		if (isNotNull($lang_id)) {
			$query->where('lang_id', $lang_id);
		}

		if (isNotNull($event_id)) {
			$query->where('event_id', $event_id);
		}

		$query->limit(1);
		return $query->get()->getRow();
	}

	public function langDirectoratesDetailInfoModel(int $lang_id = NULL, int $directorates_id = NULL) {
		$query = $this->db->table('directorates_lang');
		$query->select('directorates_id,
						directorates_slug');

		if (isNotNull($lang_id)) {
			$query->where('lang_id', $lang_id);
		}

		if (isNotNull($directorates_id)) {
			$query->where('directorates_id', $directorates_id);
		}

		$query->limit(1);
		return $query->get()->getRow();
	}

	public function langVicePresedentDetailInfoModel(int $lang_id = NULL, int $vice_president_id = NULL) {
		$query = $this->db->table('vice_presidents_lang');
		$query->select('vice_president_id,
						vice_president_slug');

		if (isNotNull($lang_id)) {
			$query->where('lang_id', $lang_id);
		}

		if (isNotNull($vice_president_id)) {
			$query->where('vice_president_id', $vice_president_id);
		}

		$query->limit(1);
		return $query->get()->getRow();
	}

	public function langGalleryCategoriesDetailInfoModel(int $lang_id = NULL, int $gallery_category_id = NULL) {
		$query = $this->db->table('gallery_categories_lang');
		$query->select('gallery_category_id,
						gallery_category_name');

		if (isNotNull($lang_id)) {
			$query->where('lang_id', $lang_id);
		}

		if (isNotNull($gallery_category_id)) {
			$query->where('gallery_category_id', $gallery_category_id);
		}

		$query->limit(1);
		return $query->get()->getRow();
	}

	public function langGalleryDetailInfoModel(int $lang_id = NULL, int $gallery_id = NULL) {
		$query = $this->db->table('gallery_lang');
		$query->select('gallery_id,
						gallery_name');

		if (isNotNull($lang_id)) {
			$query->where('lang_id', $lang_id);
		}

		if (isNotNull($gallery_id)) {
			$query->where('gallery_id', $gallery_id);
		}

		$query->limit(1);
		return $query->get()->getRow();
	}

	public function langAnnouncementDetailInfoModel(int $lang_id = NULL, int $announcement_id = NULL) {
		$query = $this->db->table('announcements_lang');
		$query->select('announcement_id,
						announcement_slug');

		if (isNotNull($lang_id)) {
			$query->where('lang_id', $lang_id);
		}

		if (isNotNull($announcement_id)) {
			$query->where('announcement_id', $announcement_id);
		}

		$query->limit(1);
		return $query->get()->getRow();
	}

	public function langCityGuideCategoriesInfoModel(int $lang_id = NULL, int $city_guide_category_id = NULL) {
		$query = $this->db->table('city_guide_categories_lang');
		$query->select('city_guide_category_id,
						city_guide_category_slug');

		if (isNotNull($lang_id)) {
			$query->where('lang_id', $lang_id);
		}

		if (isNotNull($city_guide_category_id)) {
			$query->where('city_guide_category_id', $city_guide_category_id);
		}

		$query->limit(1);
		return $query->get()->getRow();
	}

	public function langCityGuideContentsInfoModel(int $lang_id = NULL, int $city_guide_content_id = NULL) {
		$query = $this->db->table('city_guide_contents_lang');
		$query->select('city_guide_content_id,
						city_guide_content_slug');

		if (isNotNull($lang_id)) {
			$query->where('lang_id', $lang_id);
		}

		if (isNotNull($city_guide_content_id)) {
			$query->where('city_guide_content_id', $city_guide_content_id);
		}

		$query->limit(1);
		return $query->get()->getRow();
	}

	/*****************************************************/

	public function ebelediyeServicesModel(int $lang_id) {
		$query = $this->db->table('services');
		$query->select('services.service_id,
						services.service_icon,
						services_lang.service_name,
						services_lang.service_link,
						services_lang.service_slug');
		$query->join('services_lang', 'services_lang.service_id = services.service_id AND services_lang.lang_id = '.$lang_id, 'left');

		$query->where('services.status', FORM_ACTIVE_NUMBER);
		$query->where('services.service_type', SERVICES_TYPE_2);
		$query->where('services_lang.service_name !=', '');
		$query->orderBy('services.service_order', 'ASC');

		return $query->get()->getResult();
	}

	/*****************************************************/

	public function popupModuleModel(int $lang_id) {
		$query = $this->db->table('popup_module');
		$query->select('popup_module.*,
						popup_module_lang.popup_module_html,
						popup_module_lang.popup_module_image,
						popup_module_lang.popup_module_image_link');
		$query->join('popup_module_lang', 'popup_module_lang.popup_module_id = popup_module.popup_module_id AND popup_module_lang.lang_id = '.$lang_id, 'left');

		$query->where('popup_module.status', FORM_ACTIVE_NUMBER);
		$query->where('if(popup_module.popup_module_type = "'.POPUP_TYPE_HTML.'", popup_module_lang.popup_module_html != "", popup_module_lang.popup_module_image != "")');
		$query->orderBy('popup_module.popup_module_id', 'ASC');

		return $query->get()->getResult();
	}

	/*****************************************************/

	public function maintenanceModeModel() {
		$query = $this->db->table('settings');
		$query->select('site_title,
						maintenance_mode_title,
						maintenance_mode_text');

		$query->where('maintenance_mode_status', FORM_ACTIVE_NUMBER);
		$query->limit(1);

		return $query->get()->getRow();
	}

	/*****************************************************/

	public function lastIDModel(string $table, int $columnID) {
		$query = $this->db->table($table);
		$query->select($columnID);

		$query->orderBy($columnID, 'DESC');
		$query->limit(1);

		$result = $query->get()->getRow();

		if (isNotNull($result)) {
			$last_id = $result->$columnID + 1;
		} else {
			$last_id = 1;
		}

		return $last_id;
	}

	public function totalRecordModal(string $table, array $where = NULL) {
		$query = $this->db->table($table);

		if (isNotNull($where)) {
			$query->where($where);
		}

		return $query->countAllResults();
	}

	public function insertModel(string $table, array $where) {
		$this->db->transStart();
		$this->db->transStrict(FALSE);
		$this->db->table($table)->insert($where);
		$last_id = $this->db->insertID();
		$this->db->transComplete();

		if ($this->db->transStatus() === FALSE) {
			$this->db->transRollback();
			return FALSE;
		} else {
			$this->db->transCommit();
			return $last_id;
		}
	}

	public function updateModel(string $table, array $where, array $data) {
		$this->db->transBegin();
		$this->db->table($table)->update($where, $data);

		if ($this->db->transStatus() === FALSE) {
			$this->db->transRollback();
			return FALSE;
		} else {
			$this->db->transCommit();
			return TRUE;
		}
	}

	public function deleteModel(string $table, array $where) {
		$delete = $this->db->table($table)->delete($where);
		$this->tableReset($table);
		return $delete;
	}

	public function tableReset(string $table) {
		$this->db->query("ALTER TABLE `$table` AUTO_INCREMENT = 1");
	}
}
