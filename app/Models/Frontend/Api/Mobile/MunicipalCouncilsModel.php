<?php
namespace App\Models\Frontend\Api\Mobile;
use CodeIgniter\Model;

class MunicipalCouncilsModel extends Model {

	var $table = 'municipal_councils';
	var $tableLang = 'municipal_councils_lang';

	public function municipalCouncilsModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.municipal_council_name,
						'.$this->table.'.municipal_council_surname,
						'.$this->table.'.municipal_council_image,
						'.$this->table.'.municipal_council_order,
						'.$this->tableLang.'.municipal_council_sub_title');
		$query->join($this->tableLang, $this->tableLang.'.municipal_council_id = '.$this->table.'.municipal_council_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status_mobile', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->table.'.municipal_council_order', 'ASC');

		return $query->get()->getResult();
	}
}
