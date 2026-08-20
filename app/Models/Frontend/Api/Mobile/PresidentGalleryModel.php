<?php
namespace App\Models\Frontend\Api\Mobile;
use CodeIgniter\Model;

class PresidentGalleryModel extends Model {

	var $table = 'president_gallery';

	public function presidentGalleryModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select('president_gallery_image,
						president_gallery_order');

		$query->where('status_mobile', FORM_ACTIVE_NUMBER);
		$query->orderBy('president_gallery_order', 'ASC');

		return $query->get()->getResult();
	}
}
