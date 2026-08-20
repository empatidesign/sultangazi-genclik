<?php
namespace App\Models\Frontend\Contents;
use CodeIgniter\Model;

class ActivityReportModel extends Model {

	var $table = 'activity_report';
	var $tableLang = 'activity_report_lang';

	public function activityReportListModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.*,
						'.$this->tableLang.'.activity_report_name');
		$query->join($this->tableLang, $this->tableLang.'.activity_report_id = '.$this->table.'.activity_report_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->table.'.activity_report_year', 'DESC');

		return $query->get()->getResult();
	}
}
