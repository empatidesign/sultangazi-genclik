<?php
namespace App\Models\Frontend\Api\Mobile;
use CodeIgniter\Model;

class PresidentGeneralInformationModel extends Model {

	var $table = 'president_general_information';
	var $tableLang = 'president_general_information_lang';

	public function presidentGeneralInformationModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.president_name_surname,
						'.$this->table.'.president_image_mobile,
						'.$this->table.'.president_facebook,
						'.$this->table.'.president_twitter,
						'.$this->table.'.president_instagram,
						'.$this->table.'.president_youtube,
						'.$this->tableLang.'.president_general_information_sub_title');
		$query->join($this->tableLang, $this->tableLang.'.president_general_information_id = '.$this->table.'.president_general_information_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->orderBy($this->table.'.president_general_information_id', 'ASC');

		return $query->get()->getResult();
	}
}
