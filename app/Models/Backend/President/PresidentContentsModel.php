<?php
namespace App\Models\Backend\President;
use CodeIgniter\Model;

class PresidentContentsModel extends Model {

	var $table = 'president_contents';
	var $tableLang = 'president_contents_lang';

	public function presidentContentInfoModel(int $president_content_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('president_content_id', $president_content_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function presidentContentLangModel(int $president_content_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.president_content_id,
						'.$this->tableLang.'.president_content_name,
						'.$this->tableLang.'.president_content_description,
						'.$this->tableLang.'.president_content_meta_title,
						'.$this->tableLang.'.president_content_meta_keywords,
						'.$this->tableLang.'.president_content_meta_description,
						'.$this->tableLang.'.president_content_slug,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.president_content_id', $president_content_id);
		$query->orderBy($this->tableLang.'.president_content_id', 'ASC');

		return $query->get()->getResult();
	}

	public function presidentContentLangControlModel(int $president_content_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('president_content_id');

		$query->where('president_content_id', $president_content_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
