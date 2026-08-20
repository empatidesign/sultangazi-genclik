<?php
namespace App\Models\Frontend\Contents;
use CodeIgniter\Model;

class CouncilMembersModel extends Model {

	var $table = 'council_members';
	var $tableLang = 'council_members_lang';

	public function councilMembersListModel(int $lang_id, int $page_start = NULL, int $per_page = NULL) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.*,
						'.$this->tableLang.'.council_member_sub_title');
		$query->join($this->tableLang, $this->tableLang.'.council_member_id = '.$this->table.'.council_member_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->table.'.council_member_order', 'ASC');

		if (isNotNull($per_page)) {
			$query->limit($per_page, $page_start);
		}

		return $query->get()->getResult();
	}
}
