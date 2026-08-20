<?php
namespace App\Models\Backend\Contents;
use CodeIgniter\Model;

class ContactInformationModel extends Model {

	var $table = 'contact_information';
	var $tableLang = 'contact_information_lang';

	public function contactInfoModel(int $contact_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('contact_id', $contact_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function contactLangModel(int $contact_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.contact_id,
						'.$this->tableLang.'.contact_title,
						'.$this->tableLang.'.contact_address,
						'.$this->tableLang.'.contact_working_hours,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.contact_id', $contact_id);
		$query->orderBy($this->tableLang.'.contact_id');

		return $query->get()->getResult();
	}

	public function contactLangControlModel(int $contact_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('contact_id');

		$query->where('contact_id', $contact_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
