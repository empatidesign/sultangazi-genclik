<?php
namespace App\Controllers\Backend;
use CodeIgniter\Controller;

use App\Models\Backend\DashboardModel;

class Dashboard extends BaseController {

	protected $tableProducts;
	protected $tableContactRequests;
	protected $tablePresidentContactRequests;
	protected $DashboardModel;

	public function __construct() {
		$this->tableContactRequests = 'contact_requests';
		$this->tablePresidentContactRequests = 'president_contact_requests';
		$this->DashboardModel = new DashboardModel();
	}

	public function index() {

		// Contact Requests
		$contact_requests = [];
		$contact_requests_sql = $this->DashboardModel->contactRequestModel();
		if (isNotNull($contact_requests_sql)) {
			foreach ($contact_requests_sql as $row) {

				$contact_requests[] = [
					'contact_form_name' => $row->contact_form_name,
					'contact_form_surname' => $row->contact_form_surname,
					'contact_form_telephone' => $row->contact_form_telephone,
					'contact_form_created_date' => dateFormat($row->contact_form_created_date, 'd-m-Y H:i:s')
				];

			}
		}

		// President Contact Requests
		$president_contact_requests = [];
		$president_contact_requests_sql = $this->DashboardModel->presidentContactRequestModel();
		if (isNotNull($president_contact_requests_sql)) {
			foreach ($president_contact_requests_sql as $row) {

				$president_contact_requests[] = [
					'president_contact_request_name' => $row->president_contact_request_name,
					'president_contact_request_surname' => $row->president_contact_request_surname,
					'president_contact_request_telephone' => $row->president_contact_request_telephone,
					'president_contact_request_created_date' => dateFormat($row->president_contact_request_created_date, 'd-m-Y H:i:s')
				];

			}
		}

		return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/dashboard.html', [
			'total' => [
				'contact_requests' => $this->general->totalRecordModal($this->tableContactRequests),
				'president_contact_requests' => $this->general->totalRecordModal($this->tablePresidentContactRequests)
			],
			'list' => [
				'contact_requests' => $contact_requests,
				'president_contact_requests' => $president_contact_requests
			]
		]);
	}
}
