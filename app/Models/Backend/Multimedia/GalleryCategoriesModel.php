<?php
namespace App\Models\Backend\Multimedia;
use CodeIgniter\Model;

class GalleryCategoriesModel extends Model {

	var $table = 'gallery_categories';
	var $tableLang = 'gallery_categories_lang';

	public function galleryCategoriesInfoModel(int $gallery_category_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('gallery_category_id', $gallery_category_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function galleryCategoriesLangModel(int $gallery_category_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.gallery_category_id,
						'.$this->tableLang.'.gallery_category_name,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.gallery_category_id', $gallery_category_id);
		$query->orderBy($this->tableLang.'.gallery_category_id');

		return $query->get()->getResult();
	}

	public function galleryCategoriesLangControlModel(int $gallery_category_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('gallery_category_id');

		$query->where('gallery_category_id', $gallery_category_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
