<?php
namespace App\Models\Backend\Projects;
use CodeIgniter\Model;

class ProjectsCategoryModel extends Model {

	var $table = 'projects_category';
	var $tableLang = 'projects_category_lang';
	var $tableProjects = 'projects';

	public function projectsCategoryInfoModel(int $project_category_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('project_category_id', $project_category_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function projectsCategoryLangModel(int $project_category_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.project_category_id,
						'.$this->tableLang.'.project_category_name,
						'.$this->tableLang.'.project_category_slug,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.project_category_id', $project_category_id);
		$query->orderBy($this->tableLang.'.lang_id');

		return $query->get()->getResult();
	}

	public function projectsCategoryLangControlModel(int $project_category_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('project_category_id');

		$query->where('project_category_id', $project_category_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function projectsControlModel(int $project_category_id) {
		$query = $this->db->table($this->tableProjects);
		$query->select('project_category_id');

		$query->where('project_category_id', $project_category_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
