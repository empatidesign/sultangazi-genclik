<?php
namespace App\Libraries;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class MobileApiJWT {

	protected $username;
	protected $password;
	protected $tokenExpireTime;
	protected $request;
	protected $response;

	public function __construct() {
		// Kimlik bilgileri .env'den okunur; tanimli degilse eski sabitler kullanilir.
		$this->username = (string) (env('mobileApi.username') ?: 'sultangazi');
		$this->password = (string) (env('mobileApi.password') ?: '1q2w3e@!');
		$this->tokenExpireTime = (int) (env('mobileApi.tokenExpire') ?: 7200); // varsayilan 2 saat
		$this->request = \Config\Services::request();
		$this->response = \Config\Services::response();
	}

	public function HttpStatus($code) {
		$status = [
			100 => 'Continue',
			101 => 'Switching Protocols',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			306 => '(Unused)',
			307 => 'Temporary Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported'
		];

		return $status[$code] ? $status[$code] : $status[500];
	}

	public function SetHeader($code) {
		header('HTTP/1.1 '.$code.' '.$this->HttpStatus($code));
		header('Content-Type: application/json; charset=utf-8');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: POST');
	}

	/**
	 * Token dogrulama.
	 *
	 * firebase/php-jwt 6.x ile decode() imzasi degisti: ucuncu parametre
	 * artik algoritma dizisi degil, referansla gecen $headers. Algoritma
	 * Key nesnesi icinde tasinir. Istisna siniflari da Firebase\JWT
	 * ad alanindadir (dosya basinda import edilmis).
	 */
	public function ValidateJWTtoken($jwt_token, $password) {
		try {
			// $password bir Key nesnesi degilse (eski cagrilar) sarmala
			$key = $password instanceof Key ? $password : new Key((string) $password, 'HS256');

			return [TRUE, JWT::decode($jwt_token, $key)];
		} catch (ExpiredException $e) {
			return [FALSE, lang('MobileApi.error.token.expired')];
		} catch (SignatureInvalidException $e) {
			return [FALSE, lang('MobileApi.error.token.signature')];
		} catch (BeforeValidException $e) {
			return [FALSE, lang('MobileApi.error.token.notValid')];
		} catch (\Exception $e) {
			return [FALSE, lang('MobileApi.error.token.invalid')];
		}
	}

	public function authenticate() {
		$ajax_message = [];
		if ($this->request->getMethod() == 'POST') {

			// POST: form-encoded veya JSON govde kabul edilir.
			// Mobil istemciler cogunlukla application/json gonderdigi icin
			// getPost() bos donuyordu; bu durumda JSON govdeye bakilir.
			$username = (string) $this->request->getPost('username');
			$password = (string) $this->request->getPost('password');

			if ($username === '' && $password === '') {
				$govde = $this->request->getJSON(TRUE);
				if (is_array($govde)) {
					$username = (string) ($govde['username'] ?? '');
					$password = (string) ($govde['password'] ?? '');
				}
			}

			$username = trim(strip_tags($username));
			$password = trim(strip_tags($password));

			if (isNotNull($username) || isNotNull($password)) {
				if ($username == $this->username && $password == $this->password) {

					$issued_at = time();
					$expiration_time = $issued_at + $this->tokenExpireTime;
					$payload = [
						'iat' => $issued_at,
						'sub' => $username,
						'exp' => $expiration_time
					];

					$jwt_token = JWT::encode($payload, $password, 'HS256');

					/*************************************************/

					$_code = 200;
					$ajax_message = [
						'access_token' => $jwt_token,
						'token_type' => 'bearer',
						'expires_in' => $this->tokenExpireTime
					];

					$ajax_message['success'] = json($ajax_message);

				} else {
					$_code = 401;
					$ajax_message['detail'] = lang('MobileApi.error.wrong');
				}

			} else {
				$_code = 401;
				$ajax_message['detail'] = lang('MobileApi.error.empty');
			}

		} else {
			$_code = 400;
			$ajax_message['detail'] = lang('MobileApi.error.post');
		}

		// Set Header
		$this->SetHeader($_code);

		// Message
		$ajax_message['code'] = $_code;
		$ajax_message['detail'] = $this->HttpStatus($_code);

		return json($ajax_message);
	}

	/**
	 * Authorization basligindaki Bearer token'i dogrular.
	 *
	 * index() yalnizca POST kabul ettigi icin GET uclarinda kullanilamiyordu;
	 * bu metot HTTP yontemi gozetmeksizin calisir ve filtre tarafindan kullanilir.
	 *
	 * @return array [gecerli mi, mesaj]
	 */
	public function verifyRequest(): array {
		$auth_header = $this->request->getServer('HTTP_AUTHORIZATION')
			?: $this->request->getHeaderLine('Authorization');

		if (!isNotNull($auth_header)) {
			return [FALSE, lang('MobileApi.error.token.notFound')];
		}

		$jwt_token = trim(str_ireplace('Bearer', '', $auth_header));

		if (!isNotNull($jwt_token)) {
			return [FALSE, lang('MobileApi.error.token.notFound')];
		}

		try {
			return $this->ValidateJWTtoken($jwt_token, new Key($this->password, 'HS256'));
		} catch (\Exception $e) {
			return [FALSE, lang('MobileApi.error.token.invalid')];
		}
	}

	public function index() {
		$ajax_message = [];
		if ($this->request->getMethod() == 'POST') {

			// Auth
			$auth_header = $this->request->getServer('HTTP_AUTHORIZATION');
			$jwt_token = str_replace('Bearer ', '', $auth_header);

			if (isNull($auth_header)) {
				$_code = 401;
				$ajax_message['code'] = $_code;
				$ajax_message['detail'] = $this->HttpStatus($_code);
			}

			/*************************************************/

			try {

				$decoded_payload = $this->ValidateJWTtoken($jwt_token, new Key($this->password, 'HS256'));
				if ($decoded_payload[0] == TRUE) {

					// Status Code
					$_code = 200;

					// Content Type
					$this->response->setHeader('Content-type', 'application/json');
					$this->response->noCache();

					// Message
					$ajax_message['code'] = $_code;

				} else {
					$_code = 400;
					$ajax_message['code'] = $_code;
					$ajax_message['detail'] = $decoded_payload[1];
				}

			} catch (Exception $e) {
				$_code = 401;
				$ajax_message['code'] = $_code;
				$ajax_message['detail'] = $this->HttpStatus($_code);
			}

		} else {
			$_code = 400;
			$ajax_message['code'] = $_code;
			$ajax_message['detail'] = lang('MobileApi.error.post');
		}

		if ($_code != 200) { // Not Success
			// Set Header
			$this->SetHeader($_code);

			// Message
			$ajax_message['code'] = $_code;
			$ajax_message['detail'] = $this->HttpStatus($_code);
		}

		return json($ajax_message);
	}
}
