<?php
namespace App\Models\Frontend\Multimedia;
use CodeIgniter\Model;

class VideoGalleryModel extends Model {

	var $table = 'video_gallery';
	var $tableLang = 'video_gallery_lang';

	public function videoGalleryListModel(int $lang_id, int $page_start = NULL, int $per_page = NULL) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.video_gallery_image,
						'.$this->table.'.video_gallery_link,
						'.$this->tableLang.'.video_gallery_name');
		$query->join($this->tableLang, $this->tableLang.'.video_gallery_id = '.$this->table.'.video_gallery_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->table.'.video_gallery_date', 'DESC');

		if (isNotNull($per_page)) {
			$query->limit($per_page, $page_start);
		}

		return $query->get()->getResult();
	}
}
