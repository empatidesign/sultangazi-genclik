<?php
namespace App\Models\Backend;
use CodeIgniter\Model;

class DashboardModel extends Model {

	var $tableContactRequests = 'contact_requests';
	var $tablePresidentContactRequests = 'president_contact_requests';

	public function contactRequestModel() {
		$query = $this->db->table($this->tableContactRequests);
		$query->select('contact_form_name,
						contact_form_surname,
						contact_form_telephone,
						contact_form_created_date');

		$query->where('status', FALSE);
		$query->orderBy('contact_form_created_date', 'ASC');
		$query->limit(10);

		return $query->get()->getResult();
	}

	public function presidentContactRequestModel() {
		$query = $this->db->table($this->tablePresidentContactRequests);
		$query->select('president_contact_request_name,
						president_contact_request_surname,
						president_contact_request_telephone,
						president_contact_request_created_date');

		$query->where('status', FORM_PASSIVE_NUMBER);
		$query->orderBy('president_contact_request_created_date', 'ASC');
		$query->limit(10);

		return $query->get()->getResult();
	}

}
