<?php
namespace App\Models\Backend\Sultangazi;
use CodeIgniter\Model;

class ContentsModel extends Model {

	var $table = 'sultangazi_contents';
	var $tableLang = 'sultangazi_contents_lang';

	public function contentsInfoModel(int $content_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('content_id', $content_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function contentsLangModel(int $content_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.content_id,
						'.$this->tableLang.'.content_name,
						'.$this->tableLang.'.content_description,
						'.$this->tableLang.'.content_meta_title,
						'.$this->tableLang.'.content_meta_keywords,
						'.$this->tableLang.'.content_meta_description,
						'.$this->tableLang.'.content_slug,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.content_id', $content_id);
		$query->orderBy($this->tableLang.'.content_id', 'ASC');

		return $query->get()->getResult();
	}

	public function contentsLangControlModel(int $content_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('content_id');

		$query->where('content_id', $content_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
