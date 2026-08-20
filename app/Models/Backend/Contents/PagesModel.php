<?php
namespace App\Models\Backend\Contents;
use CodeIgniter\Model;

class PagesModel extends Model {

	var $table = 'pages';
	var $tableLang = 'pages_lang';

	public function pagesInfoModel(int $page_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('page_id', $page_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function pagesLangModel(int $page_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.page_id,
						'.$this->tableLang.'.page_name,
						'.$this->tableLang.'.page_description,
						'.$this->tableLang.'.page_meta_title,
						'.$this->tableLang.'.page_meta_keywords,
						'.$this->tableLang.'.page_meta_description,
						'.$this->tableLang.'.page_slug,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.page_id', $page_id);
		$query->orderBy($this->tableLang.'.page_id', 'ASC');

		return $query->get()->getResult();
	}

	public function pagesLangControlModel(int $page_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('page_id');

		$query->where('page_id', $page_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
