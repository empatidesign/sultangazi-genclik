<?php
namespace App\Models\Frontend\Contents;
use CodeIgniter\Model;

class ParliamentaryAgendaModel extends Model {

	var $table = 'parliamentary_agenda';
	var $tableLang = 'parliamentary_agenda_lang';

	public function parliamentaryAgendaListModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.*,
						'.$this->tableLang.'.parliamentary_agenda_name');
		$query->join($this->tableLang, $this->tableLang.'.parliamentary_agenda_id = '.$this->table.'.parliamentary_agenda_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->table.'.parliamentary_agenda_year DESC, '.$this->table.'.parliamentary_agenda_month DESC');

		return $query->get()->getResult();
	}
}
