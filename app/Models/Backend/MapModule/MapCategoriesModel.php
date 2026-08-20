<?php
namespace App\Models\Backend\MapModule;
use CodeIgniter\Model;

class MapCategoriesModel extends Model {

	var $table = 'map_categories';
	var $tableLang = 'map_categories_lang';

	public function mapCategoriesInfoModel(int $map_category_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('map_category_id', $map_category_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function mapCategoriesListModel() {
		$query = $this->db->table($this->table);
		$query->select('map_category_id');

		$query->orderBy('map_category_id', 'ASC');

		return $query->get()->getResult();
	}

	public function mapCategoriesLangModel(int $map_category_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.map_category_id,
						'.$this->tableLang.'.map_category_name,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.map_category_id', $map_category_id);
		$query->orderBy($this->tableLang.'.map_category_id', 'ASC');

		return $query->get()->getResult();
	}

	public function mapCategoriesLangControlModel(int $map_category_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('map_category_id');

		$query->where('map_category_id', $map_category_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
