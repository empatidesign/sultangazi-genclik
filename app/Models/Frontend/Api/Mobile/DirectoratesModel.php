<?php
namespace App\Models\Frontend\Api\Mobile;
use CodeIgniter\Model;

class DirectoratesModel extends Model {

	var $table = 'directorates';
	var $tableLang = 'directorates_lang';

	public function directoratesModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.directorates_person_name,
						'.$this->table.'.directorates_person_surname,
						'.$this->table.'.directorates_person_image,
						'.$this->table.'.directorates_telephone,
						'.$this->table.'.directorates_fax,
						'.$this->table.'.directorates_email_address,
						'.$this->tableLang.'.directorates_name,
						'.$this->tableLang.'.directorates_person_sub_title');
		$query->join($this->tableLang, $this->tableLang.'.directorates_id = '.$this->table.'.directorates_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status_mobile', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->table.'.directorates_id', 'DESC');

		return $query->get()->getResult();
	}
}
