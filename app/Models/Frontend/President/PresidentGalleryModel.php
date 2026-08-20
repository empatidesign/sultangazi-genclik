<?php
namespace App\Models\Frontend\President;
use CodeIgniter\Model;

class PresidentGalleryModel extends Model {

	var $table = 'president_gallery';

	public function presidentGalleryListModel() {
		$appdb = db_connect('application');
		$query = $appdb->table($this->table);
		$query->select('*');

		$query->where('status', FORM_ACTIVE_NUMBER);
		$query->orderBy('president_gallery_order', 'ASC');

		return $query->get()->getResult();
	}
}
