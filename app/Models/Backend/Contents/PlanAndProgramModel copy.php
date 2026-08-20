<?php
namespace App\Models\Backend\Contents;
use CodeIgniter\Model;

class PlanAndProgramModel extends Model {

	var $table = 'plan_and_program';
	var $tableLang = 'plan_and_program_lang';

	public function planAndProgramInfoModel(int $strategic_plan_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('strategic_plan_id', $strategic_plan_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function planAndProgramLangModel(int $strategic_plan_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.strategic_plan_id,
						'.$this->tableLang.'.strategic_plan_name,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.strategic_plan_id', $strategic_plan_id);
		$query->orderBy($this->tableLang.'.strategic_plan_id', 'ASC');

		return $query->get()->getResult();
	}

	public function planAndProgramLangControlModel(int $strategic_plan_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('strategic_plan_id');

		$query->where('strategic_plan_id', $strategic_plan_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
