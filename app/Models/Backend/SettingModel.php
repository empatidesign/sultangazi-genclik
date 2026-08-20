<?php
namespace App\Models\Backend;
use CodeIgniter\Model;

class SettingModel extends Model {
	protected $table = 'settings';
	protected $primaryKey = 'setting_id';
	protected $useAutoIncrement = TRUE;

	protected $returnType = 'array';
	protected $useSoftDeletes = TRUE;
	protected $protectFields = FALSE;

	public function settingsLangModel() {
		$query = $this->db->table('settings_lang');
		$query->select('settings_lang.site_footer,
						settings_lang.cookies_description,
						languages.lang_id');
		$query->join('languages', 'languages.lang_id = settings_lang.lang_id', 'left');

		$query->orderBy('settings_lang.lang_id');

		return $query->get()->getResult();
	}

	public function settingsLangControlModel(int $setting_id, int $lang_id) {
		$query = $this->db->table('settings_lang');
		$query->select('setting_id');

		$query->where('setting_id', $setting_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	/*****************************************************/

	public function socialMediaModel() {
		$query = $this->db->table('social_media');
		$query->select('*');
		$query->limit(1);

		return $query->get()->getRow();
	}

	/*****************************************************/

	public function trackingCodesModel() {
		$query = $this->db->table('tracking_codes');
		$query->select('*');
		$query->limit(1);

		return $query->get()->getRow();
	}

	/*****************************************************/

	public function countriesListModel() {
		$query = $this->db->table('countries');
		$query->select('country_id,
						country_name');

		$query->orderBy('country_name', 'ASC');

		return $query->get()->getResult();
	}

	public function countriesInfoModel(int $country_id) {
		$query = $this->db->table('countries');
		$query->select('*');

		$query->where('country_id', $country_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	/*****************************************************/

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
		$query->select('*');

		$query->where('city_id', $city_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function citiesSelectedModel(int $country_id) {
		$query = $this->db->table('cities');
		$query->select('city_id,
						city_name');

		$query->where('country_id', $country_id);
		$query->orderBy('city_name', 'ASC');

		return $query->get()->getResult();
	}

	/*****************************************************/

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
		$query->select('*');

		$query->where('district_id', $district_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function districtsSelectedModel(int $city_id) {
		$query = $this->db->table('districts');
		$query->select('district_id,
						district_name');

		$query->where('city_id', $city_id);
		$query->orderBy('district_name', 'ASC');

		return $query->get()->getResult();
	}

	/*****************************************************/

	public function neighbourhoodsInfoModel(int $neighbourhood_id) {
		$query = $this->db->table('neighbourhoods');
		$query->select('*');

		$query->where('neighbourhood_id', $neighbourhood_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	/*****************************************************/

	public function smtpModel() {
		$query = $this->db->table('settings');
		$query->select('smtp_email_title,
						smtp_host,
						smtp_email,
						smtp_password,
						smtp_port,
						smtp_crypto');

		$query->where('setting_id', SETTING_ID);

		return $query->get()->getRow();
	}

	/*****************************************************/

	public function emailTemplatesListModel(int $lang_id) {
		$query = $this->db->table('email_templates');
		$query->select('email_templates.email_template_id,
						email_templates_lang.email_template_name');
		$query->join('email_templates_lang', 'email_templates_lang.email_template_id = email_templates.email_template_id', 'left');

		$query->where('email_templates_lang.lang_id', $lang_id);
		$query->orderBy('email_templates_lang.email_template_name', 'ASC');

		return $query->get()->getResult();
	}

	public function emailTemplatesDetailModel(int $email_template_id = NULL, int $lang_id) {
		$query = $this->db->table('email_templates');
		$query->select('email_templates.*,
						email_templates_lang.email_template_name,
						email_templates_lang.email_template_description');
		$query->join('email_templates_lang', 'email_templates_lang.email_template_id = email_templates.email_template_id', 'left');

		$query->where('email_templates.email_template_id', $email_template_id);
		$query->where('email_templates_lang.lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function emailTemplatesLangModel(int $email_template_id = NULL) {
		$query = $this->db->table('email_templates_lang');
		$query->select('email_templates_lang.email_template_name,
						email_templates_lang.email_template_description,
						languages.lang_id');
		$query->join('languages', 'languages.lang_id = email_templates_lang.lang_id', 'left');

		$query->where('email_templates_lang.email_template_id', $email_template_id);
		$query->orderBy('email_templates_lang.lang_id');

		return $query->get()->getResult();
	}

	public function emailTemplatesLangControlModel(int $setting_id, int $lang_id) {
		$query = $this->db->table('email_templates_lang');
		$query->select('email_template_id');

		$query->where('email_template_id', $setting_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function emailTemplatesInfoModel(int $email_template_id, int $lang_id) {
		$query = $this->db->table('email_templates');
		$query->select('email_templates.*,
						email_templates_lang.email_template_name,
						email_templates_lang.email_template_description');
		$query->join('email_templates_lang', 'email_templates_lang.email_template_id = email_templates.email_template_id AND email_templates_lang.lang_id = '.$lang_id, 'left');

		$query->where('email_templates.email_template_id', $email_template_id);
		$query->where('email_templates.status', FORM_ACTIVE_NUMBER);
		$query->limit(1);

		return $query->get()->getRow();
	}

	/*****************************************************/

	public function languageManagementInfoModel(int $lang_id) {
		$query = $this->db->table('languages');
		$query->select('*');

		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function languageManagementSetLocaleModel() {
		$query = $this->db->table('languages_set_locale');
		$query->select('*');

		$query->orderBy('set_locale_name', 'ASC');

		return $query->get()->getResult();
	}

	/*****************************************************/

	public function managerAccountsUserTypesModel() {
		$query = $this->db->table('user_types');
		$query->select('user_type_id,
						user_type_name');
		$query->orderBy('user_type_name', 'ASC');

		return $query->get()->getResult();
	}

	public function managerAccountsInfoModel(int $user_id) {
		$query = $this->db->table('users');
		$query->select('*');

		$query->where('user_id', $user_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
