<?php
namespace App\Models\Frontend\Api\Mobile;
use CodeIgniter\Model;

class PresidentContentsModel extends Model {

	var $table = 'president_contents';
	var $tableLang = 'president_contents_lang';

	public function presidentContentsModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.president_content_image,
						'.$this->tableLang.'.president_content_name,
						'.$this->tableLang.'.president_content_description');
		$query->join($this->tableLang, $this->tableLang.'.president_content_id = '.$this->table.'.president_content_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->orderBy($this->table.'.president_content_id', 'DESC');

		return $query->get()->getResult();
	}
}
