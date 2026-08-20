<?php
namespace App\Models\Backend\Contents;
use CodeIgniter\Model;

class ActivityReportModel extends Model {

	var $table = 'activity_report';
	var $tableLang = 'activity_report_lang';

	public function activityReportInfoModel(int $activity_report_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('activity_report_id', $activity_report_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function activityReportLangModel(int $activity_report_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.activity_report_id,
						'.$this->tableLang.'.activity_report_name,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.activity_report_id', $activity_report_id);
		$query->orderBy($this->tableLang.'.activity_report_id', 'ASC');

		return $query->get()->getResult();
	}

	public function activityReportLangControlModel(int $activity_report_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('activity_report_id');

		$query->where('activity_report_id', $activity_report_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
