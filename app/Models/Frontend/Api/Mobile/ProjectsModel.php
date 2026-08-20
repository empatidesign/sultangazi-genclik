<?php
namespace App\Models\Frontend\Api\Mobile;
use CodeIgniter\Model;

class ProjectsModel extends Model {

	var $table = 'projects';
	var $tableLang = 'projects_lang';
	var $tableStatusLang = 'projects_status_lang';
	var $tableCategoryLang = 'projects_category_lang';
	var $tableImages = 'projects_image';
	var $tableNeighbourhoods = 'neighbourhoods';

	public function projectsModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.project_id,
						'.$this->table.'.project_start_date,
						'.$this->table.'.project_end_date,
						'.$this->table.'.project_location_address,
						'.$this->table.'.project_responsible,
						'.$this->table.'.project_location_telephone,
						'.$this->table.'.project_location_map,
						'.$this->table.'.project_lat_coordinate,
						'.$this->table.'.project_long_coordinate,
						'.$this->tableLang.'.project_name,
						'.$this->tableLang.'.project_description,
						'.$this->tableNeighbourhoods.'.neighbourhood_name,
						'.$this->tableStatusLang.'.project_status_name,
						'.$this->tableCategoryLang.'.project_category_name');
		$query->join($this->tableLang, $this->tableLang.'.project_id = '.$this->table.'.project_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');
		$query->join($this->tableNeighbourhoods, $this->tableNeighbourhoods.'.neighbourhood_id = '.$this->table.'.project_neighbourhood_id', 'left');
		$query->join($this->tableStatusLang, $this->tableStatusLang.'.project_status_id = '.$this->table.'.project_status_id AND '.$this->tableStatusLang.'.lang_id = '.$lang_id, 'left');
		$query->join($this->tableCategoryLang, $this->tableCategoryLang.'.project_category_id = '.$this->table.'.project_category_id AND '.$this->tableCategoryLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status_mobile', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->table.'.project_id', 'DESC');

		return $query->get()->getResult();
	}

	public function projectGalleryModel(int $project_id) {
		$query = $this->db->table($this->tableImages);
		$query->select('project_image,
						project_image_default,
						project_image_order');

		$query->where('project_id', $project_id);
		$query->orderBy('project_image_order', 'ASC');

		return $query->get()->getResult();
	}
}
