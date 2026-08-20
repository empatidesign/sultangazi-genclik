<?php
namespace App\Models\Frontend\Projects;
use CodeIgniter\Model;

class ProjectsModel extends Model {

	var $table = 'projects';
	var $tableLang = 'projects_lang';
	var $tableImages = 'projects_image';
	var $tableCategories = 'projects_category';
	var $tableCategoriesLang = 'projects_category_lang';
	var $tableStatus = 'projects_status';
	var $tableStatusLang = 'projects_status_lang';
	var $tableNeighbourhoods = 'neighbourhoods';

	public function projectListModel($category = NULL, $neighbourhood = NULL, $status = NULL, int $lang_id, int $page_start = NULL, int $per_page = NULL) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.project_id,
						'.$this->table.'.project_start_date,
						'.$this->table.'.project_end_date,
						'.$this->table.'.project_location_address,
						'.$this->table.'.project_lat_coordinate,
						'.$this->table.'.project_long_coordinate,
						'.$this->table.'.project_responsible,
						'.$this->table.'.project_location_telephone,
						'.$this->tableLang.'.project_name,
						'.$this->tableLang.'.project_slug,
						'.$this->tableCategoriesLang.'.project_category_name,
						'.$this->tableCategoriesLang.'.project_category_id,
						'.$this->tableStatusLang.'.project_status_name,
						'.$this->tableImages.'.project_image');
		$query->join($this->tableLang, $this->tableLang.'.project_id = '.$this->table.'.project_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');
		$query->join($this->tableCategoriesLang, $this->tableCategoriesLang.'.project_category_id = '.$this->table.'.project_category_id AND '.$this->tableCategoriesLang.'.lang_id = '.$lang_id, 'left');
		$query->join($this->tableStatusLang, $this->tableStatusLang.'.project_status_id = '.$this->table.'.project_status_id AND '.$this->tableStatusLang.'.lang_id = '.$lang_id, 'left');
		$query->join($this->tableImages, $this->tableImages.'.project_id = '.$this->table.'.project_id AND '.$this->tableImages.'.project_image_default = 1', 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableLang.'.project_name !=', '');

		if (is_numeric($category) && $category > 0) {
			$query->where($this->table.'.project_category_id', (int)$category);
		}

		if (is_numeric($neighbourhood) && $neighbourhood > 0) {
			$query->where($this->table.'.project_neighbourhood_id', (int)$neighbourhood);
		}

		if (is_numeric($status) && $status > 0) {
			$query->where($this->table.'.project_status_id', (int)$status);
		}

		$query->orderBy($this->table.'.project_created_date', 'DESC');

		if (isNotNull($per_page)) {
			$query->limit($per_page, $page_start);
		}

		return $query->get()->getResult();
	}

	public function projectInfoModel(string $project_slug, int $project_id, int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.project_id,
						'.$this->table.'.project_start_date,
						'.$this->table.'.project_end_date,
						'.$this->table.'.project_location_address,
						'.$this->table.'.project_responsible,
						'.$this->table.'.project_location_telephone,
						'.$this->table.'.project_location_map,
						'.$this->tableLang.'.project_name,
						'.$this->tableLang.'.project_meta_title,
						'.$this->tableLang.'.project_meta_keywords,
						'.$this->tableLang.'.project_meta_description,
						'.$this->tableLang.'.project_description,
						'.$this->tableLang.'.project_slug,
						'.$this->tableCategoriesLang.'.project_category_name,
						'.$this->tableCategoriesLang.'.project_category_id,
						'.$this->tableStatusLang.'.project_status_name');
		$query->join($this->tableLang, $this->tableLang.'.project_id = '.$this->table.'.project_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');
		$query->join($this->tableCategoriesLang, $this->tableCategoriesLang.'.project_category_id = '.$this->table.'.project_category_id AND '.$this->tableCategoriesLang.'.lang_id = '.$lang_id, 'left');
		$query->join($this->tableStatusLang, $this->tableStatusLang.'.project_status_id = '.$this->table.'.project_status_id AND '.$this->tableStatusLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->table.'.project_id', $project_id);
		$query->where($this->tableLang.'.project_slug', $project_slug);
		$query->where($this->tableLang.'.project_name !=', '');
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function projectImageListModel(int $project_id) {
		$query = $this->db->table($this->tableImages);
		$query->select('project_image');

		$query->where('project_id', $project_id);
		$query->orderBy('project_image_order', 'ASC');

		return $query->get()->getResult();
	}

	public function projectCategoryListModel(int $lang_id) {
		$query = $this->db->table($this->tableCategories);
		$query->select($this->tableCategories.'.project_category_id,
						'.$this->tableCategoriesLang.'.project_category_name');
		$query->join($this->tableCategoriesLang, $this->tableCategoriesLang.'.project_category_id = '.$this->tableCategories.'.project_category_id AND '.$this->tableCategoriesLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->tableCategories.'.status', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->tableCategoriesLang.'.project_category_name', 'ASC');

		return $query->get()->getResult();
	}

	public function projectCategorySlugInfoModel(string $slug, int $lang_id) {
		$query = $this->db->table($this->tableCategories);
		$query->select($this->tableCategories.'.project_category_id,
						'.$this->tableCategoriesLang.'.project_category_name');
		$query->join($this->tableCategoriesLang, $this->tableCategoriesLang.'.project_category_id = '.$this->tableCategories.'.project_category_id AND '.$this->tableCategoriesLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->tableCategories.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableCategoriesLang.'.project_category_slug', $slug);
		$query->orderBy($this->tableCategoriesLang.'.project_category_name', 'ASC');

		return $query->get()->getRow();
	}

	public function projectStatusListModel(int $lang_id) {
		$query = $this->db->table($this->tableStatus);
		$query->select($this->tableStatus.'.project_status_id,
						'.$this->tableStatusLang.'.project_status_name');
		$query->join($this->tableStatusLang, $this->tableStatusLang.'.project_status_id = '.$this->tableStatus.'.project_status_id AND '.$this->tableStatusLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->tableStatus.'.status', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->tableStatusLang.'.project_status_name', 'ASC');

		return $query->get()->getResult();
	}

	/*****************************************************/

	public function neighbourhoodListModel() {
		$query = $this->db->table($this->tableNeighbourhoods);
		$query->select('neighbourhood_id,
						neighbourhood_name');

		$query->where('status', FORM_ACTIVE_NUMBER);
		$query->orderBy('neighbourhood_order', 'ASC');

		return $query->get()->getResult();
	}
}
