<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAuth implements FilterInterface {
	public function before(RequestInterface $request, $arguments = NULL) {

		if ($request->getUri()->getSegment(1) == BACKEND_URL) {
			if (!session()->get('adminIsLoggedIn')) {
				return redirect()->to(base_url(BACKEND_URL.'/login'));
			}
		}

	}

	public function after(RequestInterface $request, ResponseInterface $response, $arguments = NULL) {

	}
}
