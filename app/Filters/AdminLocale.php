<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\Backend\GeneralModel;

class AdminLocale implements FilterInterface {
	public function before(RequestInterface $request, $arguments = NULL) {

		if ($request->getUri()->getSegment(1) == BACKEND_URL) {
			// Library
			helper('request');
			$language = \Config\Services::language();

			// Model
			$this->GeneralModel = new GeneralModel();

			if (session()->has('lang')) {

				$language->setLocale(session()->get('lang'));
				setlocale(LC_TIME, session()->get('lang').'.UTF-8');

			} else {

				// Default Lang
				$sql = $this->GeneralModel->languagesListModel();
				if (isNotNull($sql)) {
					foreach ($sql as $row) {
						if ($row->lang_id == $row->backend_lang_default) {

							session()->set('lang', $row->lang_code);
							$language->setLocale($row->lang_code);
							setlocale(LC_TIME, $row->lang_code.'.UTF-8');

						}
					}
				}

			}
		}

	}

	public function after(RequestInterface $request, ResponseInterface $response, $arguments = NULL) {

	}
}
