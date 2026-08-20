<?php
namespace App\Models\Backend\Contents;
use CodeIgniter\Model;

class ServicesModel extends Model {

	var $table = 'services';
	var $tableLang = 'services_lang';

	public function servicesInfoModel(int $service_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('service_id', $service_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function servicesLangModel(int $service_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.service_id,
						'.$this->tableLang.'.service_name,
						'.$this->tableLang.'.service_link,
						'.$this->tableLang.'.service_short_description,
						'.$this->tableLang.'.service_description,
						'.$this->tableLang.'.service_meta_title,
						'.$this->tableLang.'.service_meta_keywords,
						'.$this->tableLang.'.service_meta_description,
						'.$this->tableLang.'.service_slug,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.service_id', $service_id);
		$query->orderBy($this->tableLang.'.service_id', 'ASC');

		return $query->get()->getResult();
	}

	public function servicesLangControlModel(int $service_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('service_id');

		$query->where('service_id', $service_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
