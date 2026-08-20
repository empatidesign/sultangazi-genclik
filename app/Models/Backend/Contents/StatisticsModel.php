<?php
namespace App\Models\Backend\Contents;
use CodeIgniter\Model;

class StatisticsModel extends Model {

	var $table = 'statistics';
	var $tableLang = 'statistics_lang';

	public function statisticsInfoModel(int $statistic_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('statistic_id', $statistic_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function statisticsLangModel(int $statistic_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.statistic_id,
						'.$this->tableLang.'.statistic_name,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.statistic_id', $statistic_id);
		$query->orderBy($this->tableLang.'.statistic_id', 'ASC');

		return $query->get()->getResult();
	}

	public function statisticsLangControlModel(int $statistic_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('statistic_id');

		$query->where('statistic_id', $statistic_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
