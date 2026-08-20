<?php
namespace App\Models\Frontend\Api\Mobile;
use CodeIgniter\Model;

class CouncilMembersModel extends Model {

	var $table = 'council_members';
	var $tableLang = 'council_members_lang';

	public function councilMembersModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.council_member_name,
						'.$this->table.'.council_member_surname,
						'.$this->table.'.council_member_image,
						'.$this->table.'.council_member_order,
						'.$this->tableLang.'.council_member_sub_title');
		$query->join($this->tableLang, $this->tableLang.'.council_member_id = '.$this->table.'.council_member_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status_mobile', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->table.'.council_member_order', 'ASC');

		return $query->get()->getResult();
	}
}
