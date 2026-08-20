<?php
namespace App\Models\Backend\Contents;
use CodeIgniter\Model;

class MunicipalCouncilsModel extends Model {

	var $table = 'municipal_councils';
	var $tableLang = 'municipal_councils_lang';

	public function municipalCouncilInfoModel(int $municipal_council_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('municipal_council_id', $municipal_council_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function municipalCouncilLangModel(int $municipal_council_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.municipal_council_id,
						'.$this->tableLang.'.municipal_council_sub_title,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.municipal_council_id', $municipal_council_id);
		$query->orderBy($this->tableLang.'.municipal_council_id', 'ASC');

		return $query->get()->getResult();
	}

	public function municipalCouncilLangControlModel(int $municipal_council_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('municipal_council_id');

		$query->where('municipal_council_id', $municipal_council_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
