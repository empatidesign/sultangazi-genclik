<?php
namespace App\Models\Backend\Contents;
use CodeIgniter\Model;

class ParliamentaryAgendaModel extends Model {

	var $table = 'parliamentary_agenda';
	var $tableLang = 'parliamentary_agenda_lang';

	public function parliamentaryAgendaInfoModel(int $parliamentary_agenda_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('parliamentary_agenda_id', $parliamentary_agenda_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function parliamentaryAgendaLangModel(int $parliamentary_agenda_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.parliamentary_agenda_id,
						'.$this->tableLang.'.parliamentary_agenda_name,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.parliamentary_agenda_id', $parliamentary_agenda_id);
		$query->orderBy($this->tableLang.'.parliamentary_agenda_id', 'ASC');

		return $query->get()->getResult();
	}

	public function parliamentaryAgendaLangControlModel(int $parliamentary_agenda_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('parliamentary_agenda_id');

		$query->where('parliamentary_agenda_id', $parliamentary_agenda_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
