<?php
namespace App\Controllers\Backend;
use CodeIgniter\Controller;

class Language extends BaseController {
	public function index() {

		// Remove Session Lang
		session()->remove(['lang']);

		$lang = [];
		$sql = $this->general->languagesListModel();
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$lang[] = $row->lang_code;
			}
		}

		if (in_array($this->request->getUri()->getSegment(3), $lang)) {
			session()->set('lang', $this->request->getUri()->getSegment(3));
		} else {

			// Default Lang
			if (isNotNull($sql)) {
				foreach ($sql as $row) {
					if ($row->lang_id == $this->settings->backend_lang_default) {
						session()->set('lang', $row->lang_code);
					}
				}
			}

		}

		$referrer_url = $this->request->getUserAgent()->getReferrer();
		return redirect()->to($referrer_url);

	}
}
