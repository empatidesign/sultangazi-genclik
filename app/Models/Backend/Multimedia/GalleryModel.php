<?php
namespace App\Models\Backend\Multimedia;
use CodeIgniter\Model;

class GalleryModel extends Model {

	var $table = 'gallery';
	var $tableLang = 'gallery_lang';
	var $tableCategories = 'gallery_categories';
	var $tableCategoriesLang = 'gallery_categories_lang';
	var $tablePictures = 'gallery_pictures';

	public function galleryInfoModel(int $gallery_id, int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.*,
						'.$this->tableCategoriesLang.'.gallery_category_name');
		$query->join($this->tableCategoriesLang, $this->tableCategoriesLang.'.gallery_category_id = '.$this->table.'.gallery_category_id AND '.$this->tableCategoriesLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.gallery_id', $gallery_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function galleryLangModel(int $gallery_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.gallery_id,
						'.$this->tableLang.'.gallery_name,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.gallery_id', $gallery_id);
		$query->orderBy($this->tableLang.'.gallery_id');

		return $query->get()->getResult();
	}

	public function galleryLangControlModel(int $gallery_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('gallery_id');

		$query->where('gallery_id', $gallery_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function galleryCategoriesInfoModel(int $gallery_category_id, int $lang_id) {
		$query = $this->db->table($this->tableCategories);
		$query->select($this->tableCategories.'.gallery_category_id,
						'.$this->tableCategoriesLang.'.gallery_category_name');
		$query->join($this->tableCategoriesLang, $this->tableCategoriesLang.'.gallery_category_id = '.$this->tableCategories.'.gallery_category_id AND '.$this->tableCategoriesLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->tableCategories.'.gallery_category_id', $gallery_category_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function galleryCategoriesListModel(int $lang_id) {
		$query = $this->db->table($this->tableCategories);
		$query->select($this->tableCategories.'.gallery_category_id,
						'.$this->tableCategoriesLang.'.gallery_category_name');
		$query->join($this->tableCategoriesLang, $this->tableCategoriesLang.'.gallery_category_id = '.$this->tableCategories.'.gallery_category_id AND '.$this->tableCategoriesLang.'.lang_id = '.$lang_id, 'left');

		$query->orderBy($this->tableCategoriesLang.'.gallery_category_name', 'ASC');

		return $query->get()->getResult();
	}

	/*****************************************************/

	public function galleryPictureListModel(int $gallery_id) {
		$query = $this->db->table($this->tablePictures);
		$query->select('gallery_picture_id,
						gallery_picture');

		$query->where('gallery_id', $gallery_id);
		$query->orderBy('gallery_picture_order', 'ASC');

		return $query->get()->getResult();
	}

	public function galleryPictureInfoModel(int $gallery_picture_id) {
		$query = $this->db->table($this->tablePictures);
		$query->select('*');

		$query->where('gallery_picture_id', $gallery_picture_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function galleryPictureOrderModel(int $gallery_id) {
		$query = $this->db->table($this->tablePictures);
		$query->select('gallery_picture_order');

		$query->where('gallery_id', $gallery_id);
		$query->orderBy('gallery_picture_order', 'DESC');
		$query->limit(1);

		return $query->get()->getRow();
	}
}
