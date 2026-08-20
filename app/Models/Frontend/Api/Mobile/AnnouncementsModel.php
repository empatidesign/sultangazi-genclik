<?php
namespace App\Models\Frontend\Api\Mobile;
use CodeIgniter\Model;

class AnnouncementsModel extends Model {

	var $table = 'announcements';
	var $tableLang = 'announcements_lang';

	public function announcementsModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.announcement_created_date,
						'.$this->tableLang.'.announcement_name,
						'.$this->tableLang.'.announcement_link,
						'.$this->tableLang.'.announcement_description');
		$query->join($this->tableLang, $this->tableLang.'.announcement_id = '.$this->table.'.announcement_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status_mobile', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->table.'.announcement_created_date', 'DESC');

		return $query->get()->getResult();
	}
}
