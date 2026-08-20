<?php
namespace App\Models\Backend\Contents;
use CodeIgniter\Model;

class VicePresidentsModel extends Model {

	var $table = 'vice_presidents';
	var $tableLang = 'vice_presidents_lang';
	var $tableDirectorates = 'directorates';
	var $tableDirectoratesLang = 'directorates_lang';

	public function vicePresidentInfoModel(int $vice_president_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('vice_president_id', $vice_president_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function vicePresidentLangModel(int $vice_president_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.vice_president_id,
						'.$this->tableLang.'.vice_president_sub_title,
						'.$this->tableLang.'.vice_president_description,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.vice_president_id', $vice_president_id);
		$query->orderBy($this->tableLang.'.vice_president_id', 'ASC');

		return $query->get()->getResult();
	}

	public function vicePresidentLangControlModel(int $vice_president_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('vice_president_id');

		$query->where('vice_president_id', $vice_president_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function directoratesListModel(int $lang_id) {
		$query = $this->db->table($this->tableDirectorates);
		$query->select($this->tableDirectorates.'.directorates_id,
						'.$this->tableDirectoratesLang.'.directorates_name');
		$query->join($this->tableDirectoratesLang, $this->tableDirectoratesLang.'.directorates_id = '.$this->tableDirectorates.'.directorates_id AND '.$this->tableDirectoratesLang.'.lang_id = '.$lang_id, 'left');

		$query->orderBy($this->tableDirectoratesLang.'.directorates_name');

		return $query->get()->getResult();
	}
}
