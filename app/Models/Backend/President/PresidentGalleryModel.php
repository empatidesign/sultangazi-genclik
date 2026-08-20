<?php
namespace App\Models\Backend\President;
use CodeIgniter\Model;

class PresidentGalleryModel extends Model {

	var $table = 'president_gallery';

	public function presidentGalleryInfoModel(int $president_gallery_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('president_gallery_id', $president_gallery_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function presidentGalleryListModel() {
		$query = $this->db->table($this->table);
		$query->select('president_gallery_id,
						president_gallery_image,
						president_gallery_created_date,
						president_gallery_updated_date');

		$query->orderBy('president_gallery_order', 'ASC');

		return $query->get()->getResult();
	}
}
