<?php
namespace App\Models\Frontend\Api\Mobile;
use CodeIgniter\Model;

class ServicesModel extends Model {

	var $table = 'services';
	var $tableLang = 'services_lang';

	public function servicesModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.service_type,
						'.$this->table.'.service_image,
						'.$this->table.'.service_icon,
						'.$this->table.'.service_order,
						'.$this->tableLang.'.service_name,
						'.$this->tableLang.'.service_link,
						'.$this->tableLang.'.service_short_description,
						'.$this->tableLang.'.service_description');
		$query->join($this->tableLang, $this->tableLang.'.service_id = '.$this->table.'.service_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status_mobile', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->table.'.service_order', 'ASC');

		return $query->get()->getResult();
	}
}
