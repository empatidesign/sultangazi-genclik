<?php
namespace App\Models\Backend\Sultangazi;
use CodeIgniter\Model;

class VideoGalleryModel extends Model {

	var $table = 'sultangazi_video_gallery';
	var $tableLang = 'sultangazi_video_gallery_lang';

	public function videoGalleryInfoModel(int $sultangazi_video_gallery_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('sultangazi_video_gallery_id', $sultangazi_video_gallery_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function videoGalleryLangModel(int $sultangazi_video_gallery_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.sultangazi_video_gallery_id,
						'.$this->tableLang.'.sultangazi_video_gallery_name,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.sultangazi_video_gallery_id', $sultangazi_video_gallery_id);
		$query->orderBy($this->tableLang.'.sultangazi_video_gallery_id');

		return $query->get()->getResult();
	}

	public function videoGalleryLangControlModel(int $sultangazi_video_gallery_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('sultangazi_video_gallery_id');

		$query->where('sultangazi_video_gallery_id', $sultangazi_video_gallery_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
