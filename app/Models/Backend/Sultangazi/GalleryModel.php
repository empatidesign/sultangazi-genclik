<?php
namespace App\Models\Backend\Sultangazi;
use CodeIgniter\Model;

class GalleryModel extends Model {

	var $table = 'sultangazi_gallery';

	public function galleryInfoModel(int $sultangazi_gallery_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('sultangazi_gallery_id', $sultangazi_gallery_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function galleryListModel() {
		$query = $this->db->table($this->table);
		$query->select('sultangazi_gallery_id,
						sultangazi_gallery_image,
						sultangazi_gallery_created_date,
						sultangazi_gallery_updated_date');

		$query->orderBy('sultangazi_gallery_order', 'ASC');

		return $query->get()->getResult();
	}
}
