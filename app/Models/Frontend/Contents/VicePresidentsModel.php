<?php
namespace App\Models\Frontend\Contents;
use CodeIgniter\Model;

class VicePresidentsModel extends Model {

	var $table = 'vice_presidents';
	var $tableLang = 'vice_presidents_lang';
	var $tableDirectorates = 'directorates';
	var $tableDirectoratesLang = 'directorates_lang';

	public function vicePresidentInfoModel(string $vice_president_slug, int $vice_president_id, int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.vice_president_name,
						'.$this->table.'.vice_president_surname,
						'.$this->table.'.vice_president_telephone,
						'.$this->table.'.vice_president_email_address,
						'.$this->table.'.vice_president_image,
						'.$this->table.'.directorates_id,
						'.$this->tableLang.'.vice_president_sub_title,
						'.$this->tableLang.'.vice_president_description');
		$query->join($this->tableLang, $this->tableLang.'.vice_president_id = '.$this->table.'.vice_president_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->table.'.vice_president_id', $vice_president_id);
		$query->where($this->tableLang.'.vice_president_slug', $vice_president_slug);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function vicePresidentListModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.*,
						'.$this->tableLang.'.vice_president_sub_title,
						'.$this->tableLang.'.vice_president_slug');
		$query->join($this->tableLang, $this->tableLang.'.vice_president_id = '.$this->table.'.vice_president_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->table.'.vice_president_order', 'ASC');

		return $query->get()->getResult();
	}

	public function directoratesListModel(string $directorates_id, int $lang_id) {

		$directorates = [];
		$explode = explode(',', $directorates_id);
		foreach ($explode as $row) {
			$directorates[] = $row;
		}

		$query = $this->db->table($this->tableDirectorates);
		$query->select($this->tableDirectorates.'.directorates_id,
						'.$this->tableDirectoratesLang.'.directorates_name,
						'.$this->tableDirectoratesLang.'.directorates_slug');
		$query->join($this->tableDirectoratesLang, $this->tableDirectoratesLang.'.directorates_id = '.$this->tableDirectorates.'.directorates_id AND '.$this->tableDirectoratesLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->tableDirectorates.'.status', FORM_ACTIVE_NUMBER);
		$query->whereIn($this->tableDirectorates.'.directorates_id', $directorates);
		$query->where($this->tableDirectoratesLang.'.directorates_name !=', '');
		$query->orderBy($this->tableDirectoratesLang.'.directorates_name', 'ASC');

		return $query->get()->getResult();
	}
}
