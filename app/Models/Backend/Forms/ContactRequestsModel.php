<?php
namespace App\Models\Backend\Forms;
use CodeIgniter\Model;

class ContactRequestsModel extends Model {

	var $table = 'contact_requests';

	public function contactInfoModel(int $contact_form_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('contact_form_id', $contact_form_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
