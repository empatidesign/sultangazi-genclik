<?php
namespace App\Models\Backend\Projects;
use CodeIgniter\Model;

class ProjectsStatusModel extends Model {

	var $table = 'projects_status';
	var $tableLang = 'projects_status_lang';
	var $tableProjects = 'projects';

	public function projectStatusInfoModel(int $project_status_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('project_status_id', $project_status_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function projectStatusLangModel(int $project_status_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.project_status_id,
						'.$this->tableLang.'.project_status_name,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.project_status_id', $project_status_id);
		$query->orderBy($this->tableLang.'.lang_id');

		return $query->get()->getResult();
	}

	public function projectStatusLangControlModel(int $project_status_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('project_status_id');

		$query->where('project_status_id', $project_status_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function projectsControlModel(int $project_status_id) {
		$query = $this->db->table($this->tableProjects);
		$query->select('project_status_id');

		$query->where('project_status_id', $project_status_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
