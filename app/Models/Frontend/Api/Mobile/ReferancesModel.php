<?php
namespace App\Models\Frontend\Api\Mobile;
use CodeIgniter\Model;

class ReferancesModel extends Model {

	var $table = 'referances';
	var $tableLang = 'referances_lang';

	public function referancesModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.referance_image,
						'.$this->tableLang.'.referance_name,
						'.$this->tableLang.'.referance_link');
		$query->join($this->tableLang, $this->tableLang.'.referance_id = '.$this->table.'.referance_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status_mobile', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->table.'.referance_id', 'DESC');

		return $query->get()->getResult();
	}
}
