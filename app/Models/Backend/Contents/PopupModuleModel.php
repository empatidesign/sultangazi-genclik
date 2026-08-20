<?php
namespace App\Models\Backend\Contents;
use CodeIgniter\Model;

class PopupModuleModel extends Model {

	var $table = 'popup_module';
	var $tableLang = 'popup_module_lang';

	public function popupInfoModel(int $popup_module_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('popup_module_id', $popup_module_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function popupLangModel(int $popup_module_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.popup_module_lang_id,
						'.$this->tableLang.'.popup_module_id,
						'.$this->tableLang.'.popup_module_html,
						'.$this->tableLang.'.popup_module_image,
						'.$this->tableLang.'.popup_module_mobile_image,
						'.$this->tableLang.'.popup_module_image_link,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.popup_module_id', $popup_module_id);
		$query->orderBy($this->tableLang.'.lang_id');

		return $query->get()->getResult();
	}

	public function popupLangControlModel(int $popup_module_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('popup_module_id,
						popup_module_image,
						popup_module_mobile_image');

		$query->where('popup_module_id', $popup_module_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
