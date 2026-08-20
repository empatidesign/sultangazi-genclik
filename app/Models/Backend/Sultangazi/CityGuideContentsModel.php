<?php
namespace App\Models\Backend\Sultangazi;
use CodeIgniter\Model;

class CityGuideContentsModel extends Model {

	var $table = 'city_guide_contents';
	var $tableLang = 'city_guide_contents_lang';

	public function cityGuideContentsInfoModel(int $city_guide_content_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('city_guide_content_id', $city_guide_content_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function cityGuideContentsLangModel(int $city_guide_content_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.city_guide_content_id,
						'.$this->tableLang.'.city_guide_content_name,
						'.$this->tableLang.'.city_guide_content_person_name_sub_title,
						'.$this->tableLang.'.city_guide_content_description,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.city_guide_content_id', $city_guide_content_id);
		$query->orderBy($this->tableLang.'.city_guide_content_id', 'ASC');

		return $query->get()->getResult();
	}

	public function cityGuideContentsLangControlModel(int $city_guide_content_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('city_guide_content_id');

		$query->where('city_guide_content_id', $city_guide_content_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
