<?php
namespace App\Models\Backend\Sultangazi;
use CodeIgniter\Model;

class CityGuideCategoriesModel extends Model {

	var $table = 'city_guide_categories';
	var $tableLang = 'city_guide_categories_lang';
	var $tableContents = 'city_guide_contents';

	public function cityGuideCategoriesInfoModel(int $city_guide_category_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('city_guide_category_id', $city_guide_category_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function cityGuideCategoriesLangModel(int $city_guide_category_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.city_guide_category_id,
						'.$this->tableLang.'.city_guide_category_name,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.city_guide_category_id', $city_guide_category_id);
		$query->orderBy($this->tableLang.'.city_guide_category_id', 'ASC');

		return $query->get()->getResult();
	}

	public function cityGuideCategoriesLangControlModel(int $city_guide_category_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('city_guide_category_id');

		$query->where('city_guide_category_id', $city_guide_category_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function cityGuideContentsControlModel(int $city_guide_category_id) {
		$query = $this->db->table($this->tableContents);
		$query->select('city_guide_content_category_id');

		$query->where('city_guide_content_category_id', $city_guide_category_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
