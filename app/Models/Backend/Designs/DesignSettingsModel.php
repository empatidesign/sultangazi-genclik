<?php
namespace App\Models\Backend\Designs;
use CodeIgniter\Model;

class DesignSettingsModel extends Model {

	var $table = 'design_settings';
	var $tableLang = 'design_settings_lang';

	public function designSettingsInfoModel(int $design_setting_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('design_setting_id', $design_setting_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function designSettingsLangModel() {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.design_setting_id,
						'.$this->tableLang.'.header_facilities_link,
						'.$this->tableLang.'.footer_promotion_film,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->orderBy($this->tableLang.'.lang_id');

		return $query->get()->getResult();
	}

	public function designSettingsLangControlModel(int $design_settings_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('design_setting_id');

		$query->where('design_setting_id', $design_settings_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
