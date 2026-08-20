<?php
namespace App\Models\Backend\Multimedia;
use CodeIgniter\Model;

class VideoGalleryModel extends Model {

	var $table = 'video_gallery';
	var $tableLang = 'video_gallery_lang';

	public function videoGalleryInfoModel(int $video_gallery_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('video_gallery_id', $video_gallery_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function videoGalleryLangModel(int $video_gallery_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.video_gallery_id,
						'.$this->tableLang.'.video_gallery_name,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.video_gallery_id', $video_gallery_id);
		$query->orderBy($this->tableLang.'.video_gallery_id');

		return $query->get()->getResult();
	}

	public function videoGalleryLangControlModel(int $video_gallery_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('video_gallery_id');

		$query->where('video_gallery_id', $video_gallery_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
