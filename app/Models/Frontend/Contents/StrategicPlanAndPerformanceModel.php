<?php
namespace App\Models\Frontend\Contents;
use CodeIgniter\Model;

class StrategicPlanAndPerformanceModel extends Model {

	var $table = 'strategic_plan_and_performance';
	var $tableLang = 'strategic_plan_and_performance_lang';

	public function strategicPlanAndPerformanceListModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.*,
						'.$this->tableLang.'.strategic_plan_name');
		$query->join($this->tableLang, $this->tableLang.'.strategic_plan_id = '.$this->table.'.strategic_plan_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->table.'.strategic_plan_year', 'DESC');

		return $query->get()->getResult();
	}
}
