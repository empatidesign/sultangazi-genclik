<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Kenjis\CI4Twig\Twig;

use App\Models\Frontend\GeneralModel;

class Maintenance implements FilterInterface {
	public function before(RequestInterface $request, $arguments = NULL) {

		$segment1 = $request->getUri()->getSegment(1);
		if ($segment1 != BACKEND_URL && !session()->get('adminIsLoggedIn')) {

			// Helper
			helper('html');

			// Model
			$this->GeneralModel = new GeneralModel();

			$info = $this->GeneralModel->maintenanceModeModel();
			if (isNotNull($info)) {

				$twigConfig = [
		      'functions_safe' => ['doctype']
		    ];

				$twig = new Twig($twigConfig);
				echo $twig->render(Frontend.'/maintenance.html', [
					'head' => [
						'title' => $info->site_title
					],
					'result' => [
						'maintenance_mode_title' => $info->maintenance_mode_title,
						'maintenance_mode_text' => $info->maintenance_mode_text
					],
					'PARAMETER' => [
						'FILE_PATH_ASSETS' => FILE_PATH_ASSETS,
						'FILE_PATH_IMAGES' => FILE_PATH_IMAGES
					]
				]);

				exit();

			}

		}

	}

	public function after(RequestInterface $request, ResponseInterface $response, $arguments = NULL) {

	}
}
