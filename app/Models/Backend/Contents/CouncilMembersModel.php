<?php
namespace App\Models\Backend\Contents;
use CodeIgniter\Model;

class CouncilMembersModel extends Model {

	var $table = 'council_members';
	var $tableLang = 'council_members_lang';

	public function councilMembersInfoModel(int $council_member_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('council_member_id', $council_member_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function councilMembersLangModel(int $council_member_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.council_member_id,
						'.$this->tableLang.'.council_member_sub_title,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.council_member_id', $council_member_id);
		$query->orderBy($this->tableLang.'.council_member_id', 'ASC');

		return $query->get()->getResult();
	}

	public function councilMembersLangControlModel(int $council_member_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('council_member_id');

		$query->where('council_member_id', $council_member_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
