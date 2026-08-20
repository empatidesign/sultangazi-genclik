<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;

use App\Models\Frontend\GeneralModel;

class WebLang implements FilterInterface {
	public function before(RequestInterface $request, $arguments = NULL) {

		$segment1 = $request->getUri()->getSegment(1);
		if ($segment1 != BACKEND_URL) {

			// Model
			$this->GeneralModel = new GeneralModel();

			$info = $this->GeneralModel->languagesInfoModel();
			if (isNotNull($info)) {
				if ($info->lang_code == $segment1) {
					return redirect()->to('/');
				}
			}

		}

	}

	public function after(RequestInterface $request, ResponseInterface $response, $arguments = NULL) {

	}
}
