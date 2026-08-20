<?php
namespace App\Models\Frontend\Multimedia;
use CodeIgniter\Model;

class GalleryModel extends Model {

	var $table = 'gallery';
	var $tableLang = 'gallery_lang';
	var $tablePictures = 'gallery_pictures';
	var $tableCategories = 'gallery_categories';
	var $tableCategoriesLang = 'gallery_categories_lang';

	public function galleryCategoriesInfoModel(int $gallery_category_id, int $lang_id) {
		$query = $this->db->table($this->tableCategories);
		$query->select($this->tableCategories.'.gallery_category_id,
						'.$this->tableCategoriesLang.'.gallery_category_name');
		$query->join($this->tableCategoriesLang, $this->tableCategoriesLang.'.gallery_category_id = '.$this->tableCategories.'.gallery_category_id AND '.$this->tableCategoriesLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->tableCategories.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableCategories.'.gallery_category_id', $gallery_category_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function galleryCategoriesListModel(int $lang_id, int $page_start = NULL, int $per_page = NULL) {
		$query = $this->db->table($this->tableCategories);
		$query->select($this->tableCategories.'.gallery_category_id,
						'.$this->tableCategories.'.gallery_category_image,
						'.$this->tableCategoriesLang.'.gallery_category_name');
		$query->join($this->tableCategoriesLang, $this->tableCategoriesLang.'.gallery_category_id = '.$this->tableCategories.'.gallery_category_id AND '.$this->tableCategoriesLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->tableCategories.'.status', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->tableCategories.'.gallery_category_created_date', 'DESC');

		if (isNotNull($per_page)) {
			$query->limit($per_page, $page_start);
		}

		return $query->get()->getResult();
	}

	/*****************************************************/

	public function galleryInfoModel(int $gallery_id, int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.gallery_id,
						'.$this->table.'.gallery_category_id,
						'.$this->tableLang.'.gallery_name,
						'.$this->tableCategoriesLang.'.gallery_category_name,
						'.$this->tablePictures.'.gallery_picture');
		$query->join($this->tableLang, $this->tableLang.'.gallery_id = '.$this->table.'.gallery_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');
		$query->join($this->tableCategoriesLang, $this->tableCategoriesLang.'.gallery_category_id = '.$this->table.'.gallery_category_id AND '.$this->tableCategoriesLang.'.lang_id = '.$lang_id, 'left');
		$query->join($this->tablePictures, $this->tablePictures.'.gallery_id = '.$this->table.'.gallery_id AND '.$this->tablePictures.'.gallery_picture_order = 0', 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->table.'.gallery_id', $gallery_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function galleryListModel(int $gallery_category_id = NULL, int $lang_id, int $page_start = NULL, int $per_page = NULL) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.gallery_id,
						'.$this->tableLang.'.gallery_name,
						'.$this->tablePictures.'.gallery_picture');
		$query->join($this->tableLang, $this->tableLang.'.gallery_id = '.$this->table.'.gallery_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');
		$query->join($this->tablePictures, $this->tablePictures.'.gallery_id = '.$this->table.'.gallery_id AND '.$this->tablePictures.'.gallery_picture_order = 0', 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);

		if (isNotNull($gallery_category_id)) {
			$query->where($this->table.'.gallery_category_id', $gallery_category_id);
		}

		$query->orderBy($this->table.'.gallery_created_date', 'DESC');

		if (isNotNull($per_page)) {
			$query->limit($per_page, $page_start);
		}

		return $query->get()->getResult();
	}

	public function galleryPictureListModel(int $gallery_id) {
		$query = $this->db->table($this->tablePictures);
		$query->select('gallery_id,
						gallery_picture');

		$query->where('gallery_id', $gallery_id);
		$query->orderBy('gallery_picture_order', 'ASC');

		return $query->get()->getResult();
	}
}
