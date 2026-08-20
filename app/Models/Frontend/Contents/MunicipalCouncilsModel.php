<?php
namespace App\Models\Frontend\Contents;
use CodeIgniter\Model;

class MunicipalCouncilsModel extends Model {

	var $table = 'municipal_councils';
	var $tableLang = 'municipal_councils_lang';

	public function municipalCouncilsListModel(int $lang_id, int $page_start = NULL, int $per_page = NULL) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.*,
						'.$this->tableLang.'.municipal_council_sub_title');
		$query->join($this->tableLang, $this->tableLang.'.municipal_council_id = '.$this->table.'.municipal_council_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->table.'.municipal_council_order', 'ASC');

		if (isNotNull($per_page)) {
			$query->limit($per_page, $page_start);
		}

		return $query->get()->getResult();
	}
}
