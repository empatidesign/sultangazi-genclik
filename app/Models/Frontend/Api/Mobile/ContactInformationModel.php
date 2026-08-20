<?php
namespace App\Models\Frontend\Api\Mobile;
use CodeIgniter\Model;

class ContactInformationModel extends Model {

	var $table = 'contact_information';
	var $tableLang = 'contact_information_lang';

	public function contactInformationModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.contact_default,
						'.$this->table.'.contact_telephone,
						'.$this->table.'.contact_telephone2,
						'.$this->table.'.contact_mobile,
						'.$this->table.'.contact_whatsapp,
						'.$this->table.'.contact_fax,
						'.$this->table.'.contact_fax2,
						'.$this->table.'.contact_email,
						'.$this->table.'.contact_email2,
						'.$this->table.'.contact_map_lat_coordinate,
						'.$this->table.'.contact_map_long_coordinate,
						'.$this->table.'.contact_map_marker,
						'.$this->table.'.contact_post_code,
						'.$this->table.'.contact_map_url,
						'.$this->tableLang.'.contact_title,
						'.$this->tableLang.'.contact_address,
						'.$this->tableLang.'.contact_working_hours');
		$query->join($this->tableLang, $this->tableLang.'.contact_id = '.$this->table.'.contact_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status_mobile', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->table.'.contact_id', 'DESC');

		return $query->get()->getResult();
	}
}
