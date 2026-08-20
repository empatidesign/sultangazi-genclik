<?php
namespace App\Models\Backend\Projects;
use CodeIgniter\Model;

class ProjectsContentModel extends Model {

	var $table = 'projects';
	var $tableLang = 'projects_lang';
	var $tableCategories = 'projects_category';
	var $tableCategoriesLang = 'projects_category_lang';
	var $tableStatus = 'projects_status';
	var $tableStatusLang = 'projects_status_lang';
	var $tableImages = 'projects_image';
	var $tableNeighbourhoods = 'neighbourhoods';

	public function projectInfoModel(int $project_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('project_id', $project_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function projectLangModel(int $project_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.project_id,
						'.$this->tableLang.'.project_name,
						'.$this->tableLang.'.project_description,
						'.$this->tableLang.'.project_meta_title,
						'.$this->tableLang.'.project_meta_keywords,
						'.$this->tableLang.'.project_meta_description,
						'.$this->tableLang.'.project_slug,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.project_id', $project_id);
		$query->orderBy($this->tableLang.'.project_id');

		return $query->get()->getResult();
	}

	public function projectLangControlModel(int $project_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('project_id');

		$query->where('project_id', $project_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function projectCategoryListModel(int $lang_id) {
		$query = $this->db->table($this->tableCategories);
		$query->select($this->tableCategories.'.project_category_id,
						'.$this->tableCategoriesLang.'.project_category_name');
		$query->join($this->tableCategoriesLang, $this->tableCategoriesLang.'.project_category_id = '.$this->tableCategories.'.project_category_id AND '.$this->tableCategoriesLang.'.lang_id = '.$lang_id, 'left');

		$query->orderBy($this->tableCategories.'.project_category_order', 'ASC');

		return $query->get()->getResult();
	}

	public function projectStatusListModel(int $lang_id) {
		$query = $this->db->table($this->tableStatus);
		$query->select($this->tableStatus.'.project_status_id,
						'.$this->tableStatusLang.'.project_status_name');
		$query->join($this->tableStatusLang, $this->tableStatusLang.'.project_status_id = '.$this->tableStatus.'.project_status_id AND '.$this->tableStatusLang.'.lang_id = '.$lang_id, 'left');

		$query->orderBy($this->tableStatus.'.project_status_order', 'ASC');

		return $query->get()->getResult();
	}

	/*****************************************************/

	public function projectImageListModel(int $project_id) {
		$query = $this->db->table($this->tableImages);
		$query->select('*');

		$query->where('project_id', $project_id);
		$query->orderBy('project_image_order', 'ASC');

		return $query->get()->getResult();
	}

	public function projectImageInfoModel(int $project_image_id) {
		$query = $this->db->table($this->tableImages);
		$query->select('*');

		$query->where('project_image_id', $project_image_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function projectImageOrderModel(int $project_id) {
		$query = $this->db->table($this->tableImages);
		$query->select('project_image_order');

		$query->where('project_id', $project_id);
		$query->orderBy('project_image_order', 'DESC');
		$query->limit(1);

		return $query->get()->getRow();
	}

	/*****************************************************/

	public function neighbourhoodListModel() {
		$query = $this->db->table($this->tableNeighbourhoods);
		$query->select('neighbourhood_id,
						neighbourhood_name');

		$query->where('status', FORM_ACTIVE_NUMBER);
		$query->orderBy('neighbourhood_name', 'ASC');

		return $query->get()->getResult();
	}
}
