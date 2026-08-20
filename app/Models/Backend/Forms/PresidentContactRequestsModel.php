<?php
namespace App\Models\Backend\Forms;
use CodeIgniter\Model;

class PresidentContactRequestsModel extends Model {

	var $table = 'president_contact_requests';

	public function presidentContactRequestInfoModel(int $president_contact_request_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('president_contact_request_id', $president_contact_request_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
