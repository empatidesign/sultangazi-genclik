<?php
namespace App\Models\Backend\MapModule;
use CodeIgniter\Model;

class MapLocationsModel extends Model {

	var $table = 'map_locations';
	var $tableLang = 'map_locations_lang';
	var $tableCategories = 'map_categories';
	var $tableCategoriesLang = 'map_categories_lang';
	var $tableProjects = 'projects';
	var $tableProjectsLang = 'projects_lang';

	public function mapLocationsInfoModel(int $map_location_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('map_location_id', $map_location_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function mapLocationsLangModel(int $map_location_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.map_location_id,
						'.$this->tableLang.'.map_location_name,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.map_location_id', $map_location_id);
		$query->orderBy($this->tableLang.'.map_location_id', 'ASC');

		return $query->get()->getResult();
	}

	public function mapLocationsLangControlModel(int $map_location_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('map_location_id');

		$query->where('map_location_id', $map_location_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function mapCategoriesListModel(int $lang_id) {
		$query = $this->db->table($this->tableCategories);
		$query->select($this->tableCategories.'.map_category_id,
						'.$this->tableCategoriesLang.'.map_category_name');
		$query->join($this->tableCategoriesLang, $this->tableCategoriesLang.'.map_category_id = '.$this->tableCategories.'.map_category_id AND '.$this->tableCategoriesLang.'.lang_id = '.$lang_id, 'left');

		$query->orderBy($this->tableCategoriesLang.'.map_category_name', 'ASC');

		return $query->get()->getResult();
	}

	/*****************************************************/

	public function projectListModel(int $lang_id) {
		$query = $this->db->table($this->tableProjects);
		$query->select($this->tableProjects.'.project_id,
						'.$this->tableProjectsLang.'.project_name');
		$query->join($this->tableProjectsLang, $this->tableProjectsLang.'.project_id = '.$this->tableProjects.'.project_id AND '.$this->tableProjectsLang.'.lang_id = '.$lang_id, 'left');

		$query->orderBy($this->tableProjectsLang.'.project_name', 'ASC');

		return $query->get()->getResult();
	}
}
