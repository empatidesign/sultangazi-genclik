<?php
namespace App\Models\Backend\Contents;
use CodeIgniter\Model;

class ReferancesModel extends Model {

	var $table = 'referances';
	var $tableLang = 'referances_lang';

	public function referancesInfoModel(int $referance_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('referance_id', $referance_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function referancesLangModel(int $referance_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.referance_id,
						'.$this->tableLang.'.referance_name,
						'.$this->tableLang.'.referance_link,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.referance_id', $referance_id);
		$query->orderBy($this->tableLang.'.referance_id', 'ASC');

		return $query->get()->getResult();
	}

	public function referancesLangControlModel(int $referance_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('referance_id');

		$query->where('referance_id', $referance_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
