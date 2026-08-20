<?php
namespace App\Controllers\Frontend;
use CodeIgniter\Controller;

class NotFound extends BaseController {
	public function index() {
		echo $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/not-found.html', [
			'head' => [
				'title' => lang('Web.pageNotFound.title'),
				'keywords' => $this->settings->site_keywords,
				'description' => $this->settings->site_description
			]
		]);

		$this->response->setStatusCode(404);
	}
}
