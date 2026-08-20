<?php
namespace App\Models\Frontend\Api\Mobile;
use CodeIgniter\Model;

class VicePresidentsModel extends Model {

	var $table = 'vice_presidents';
	var $tableLang = 'vice_presidents_lang';

	public function vicePresidentsModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.vice_president_name,
						'.$this->table.'.vice_president_surname,
						'.$this->table.'.vice_president_telephone,
						'.$this->table.'.vice_president_email_address,
						'.$this->table.'.vice_president_order,
						'.$this->table.'.vice_president_image,
						'.$this->tableLang.'.vice_president_sub_title,
						'.$this->tableLang.'.vice_president_description');
		$query->join($this->tableLang, $this->tableLang.'.vice_president_id = '.$this->table.'.vice_president_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status_mobile', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->table.'.vice_president_order', 'ASC');

		return $query->get()->getResult();
	}
}
