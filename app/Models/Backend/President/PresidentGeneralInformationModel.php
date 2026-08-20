<?php
namespace App\Models\Backend\President;
use CodeIgniter\Model;

class PresidentGeneralInformationModel extends Model {

	var $table = 'president_general_information';
	var $tableLang = 'president_general_information_lang';

	public function presidentGeneralInformationModel() {
		$query = $this->db->table($this->table);
		$query->select('*');
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function presidentGeneralInformationLangModel(int $president_general_information_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.president_general_information_sub_title,
						'.$this->tableLang.'.president_general_information_link,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.president_general_information_id', $president_general_information_id);
		$query->orderBy($this->tableLang.'.president_general_information_id', 'ASC');

		return $query->get()->getResult();
	}

	public function presidentGeneralInformationLangControlModel(int $president_general_information_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('president_general_information_id');

		$query->where('president_general_information_id', $president_general_information_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
