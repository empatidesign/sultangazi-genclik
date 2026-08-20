<?php
namespace App\Models\Frontend\President;
use CodeIgniter\Model;

class PresidentContentsModel extends Model {

	var $table = 'president_contents';
	var $tableLang = 'president_contents_lang';

	public function presidentContentInfoModel(string $slug, int $president_content_id, int $lang_id) {
		$appdb = db_connect('application');
		$query = $appdb->table($this->table);
		$query->select($this->table.'.president_content_id,
						'.$this->table.'.president_content_image,
						'.$this->tableLang.'.president_content_name,
						'.$this->tableLang.'.president_content_description,
						'.$this->tableLang.'.president_content_meta_title,
						'.$this->tableLang.'.president_content_meta_keywords,
						'.$this->tableLang.'.president_content_meta_description');
		$query->join($this->tableLang, $this->tableLang.'.president_content_id = '.$this->table.'.president_content_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableLang.'.president_content_slug', $slug);
		$query->where($this->table.'.president_content_id', $president_content_id);
		$query->where($this->tableLang.'.president_content_name !=', '');
		$query->limit(1);

		return $query->get()->getRow();
	}
}
