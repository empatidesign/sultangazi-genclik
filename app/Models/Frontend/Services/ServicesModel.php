<?php
namespace App\Models\Frontend\Services;
use CodeIgniter\Model;

class ServicesModel extends Model {

	var $table = 'services';
	var $tableLang = 'services_lang';

	public function serviceListModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.service_id,
						'.$this->table.'.service_image,
						'.$this->table.'.service_created_date,
						'.$this->tableLang.'.service_name,
						'.$this->tableLang.'.service_short_description,
						'.$this->tableLang.'.service_slug');
		$query->join($this->tableLang, $this->tableLang.'.service_id = '.$this->table.'.service_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->table.'.service_type', SERVICES_TYPE_3);
		$query->where($this->tableLang.'.service_name !=', '');
		$query->orderBy($this->table.'.service_order', 'ASC');

		return $query->get()->getResult();
	}

	public function serviceInfoModel(string $slug, int $service_id, int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.*,
						'.$this->tableLang.'.service_name,
						'.$this->tableLang.'.service_description,
						'.$this->tableLang.'.service_meta_title,
						'.$this->tableLang.'.service_meta_keywords,
						'.$this->tableLang.'.service_meta_description');
		$query->join($this->tableLang, $this->tableLang.'.service_id = '.$this->table.'.service_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->table.'.service_id', $service_id);
		$query->where($this->tableLang.'.service_slug', $slug);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function serviceDefaultModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.service_id,
						'.$this->table.'.service_image,
						'.$this->tableLang.'.service_name,
						'.$this->tableLang.'.service_description');
		$query->join($this->tableLang, $this->tableLang.'.service_id = '.$this->table.'.service_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->table.'.service_type', SERVICES_TYPE_3);
		$query->orderBy($this->tableLang.'.service_id', 'ASC');
		$query->limit(1);

		return $query->get()->getRow();
	}
}
