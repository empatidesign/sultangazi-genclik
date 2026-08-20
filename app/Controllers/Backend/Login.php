<?php
namespace App\Controllers\Backend;
use CodeIgniter\Controller;

use App\Models\Backend\LoginModel;
use App\Models\Backend\GeneralModel;

class Login extends BaseController {

	protected $table;
	protected $LoginModel;
	protected $GeneralModel;

	public function __construct() {
		$this->table = 'users';
		$this->LoginModel = new LoginModel();
		$this->GeneralModel = new GeneralModel();
	}

	public function index() {
		helper(['cookie']);
		return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/login.html', [
			'cookie' => [
				'admin_email_address' => base64_decode(get_cookie('admin_email_address')),
				'admin_password' => base64_decode(get_cookie('admin_password'))
			],
			'PARAMETER' => [
				'ADMIN_URL_LOGIN_AUTH' => ADMIN_URL_LOGIN_AUTH
			]
		]);
	}

	public function loginAuth() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules = [
					'email_address' => [
						'label' => lang('Admin.form.emailAddress'),
						'rules' => 'required|min_length['.FORM_EMAIL_MIN_LENGTH.']|max_length['.FORM_EMAIL_MAX_LENGTH.']|valid_email'
					],
					'password' => [
						'label' => lang('Admin.form.password'),
						'rules' => 'required|min_length['.FORM_PASSWORD_MIN_LENGTH.']|max_length['.FORM_PASSWORD_MAX_LENGTH.']'
					]
				];

				helper(['cookie']);
				$email_address = removeWhiteSpaces($this->request->getVar('email_address'));
				$password = trim($this->request->getVar('password'));
				$remember_me = $this->request->getVar('remember_me');

				if ($this->validate($rules)) {
					$user = $this->LoginModel->userControlModel($email_address);
					if (isNotNull($user)) {

						$verify_pass = password_verify($password, $user->user_password);
						if ($verify_pass) {

							// Remember Me (Cookie)
							if ($remember_me) {
								$this->response->setCookie('admin_email_address', base64_encode($email_address), 31556926);
								$this->response->setCookie('admin_password', base64_encode($password), 31556926);
							} else {
								$this->response->deleteCookie('admin_email_address');
								$this->response->deleteCookie('admin_password');
							}

							$session_data = [
								'admin_user_id' => $user->user_id,
								'admin_user_name' => $user->user_name_surname,
								'admin_user_email' => $user->user_email_address,
								'adminIsLoggedIn' => TRUE
							];
							session()->set($session_data);

							// User Login Info Update
							$agent = $this->request->getUserAgent();

							if ($agent->isBrowser()) {
								$currentAgent = $agent->getBrowser().' '.$agent->getVersion();
							} elseif ($agent->isRobot()) {
								$currentAgent = $agent->getRobot();
							} elseif ($agent->isMobile()) {
								$currentAgent = $agent->getMobile();
							} else {
								$currentAgent = lang('Admin.unidentifiedUserAgent');
							}

							$user_login_data = [
								'user_last_login_date' => nowDate(),
								'user_last_login_ip' => $this->request->getIPAddress(),
								'user_last_login_info' => json_encode(['Browser: '.$currentAgent.', Platform: '.$agent->getPlatform()])
							];
							$this->general->updateModel($this->table, $user_login_data, ['user_id' => $user->user_id]);

							$ajax_message['success'] = TRUE;
							$ajax_message['url'] = base_url(BACKEND_URL.'/dashboard');

						} else {
							$ajax_message['error'] = lang('Admin.loginForm.alert.passwordError');
						}

					} else {
						$ajax_message['error'] = lang('Admin.loginForm.alert.emailError');
					}
				} else {
					$ajax_message['error'] = $this->validator->listErrors();
				}

			} else {
				$ajax_message['error'] = lang('Admin.error.description');
			}
		} else {
			$ajax_message['error'] = lang('Admin.error.ajax');
		}

		return $this->response->setJSON($ajax_message);
	}

	public function logout() {
		session()->remove(['admin_user_id', 'admin_user_name', 'admin_user_email', 'adminIsLoggedIn', 'lang']);
		return redirect()->to(BACKEND_URL.'/login');
	}
}
