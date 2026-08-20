<?php
namespace App\Libraries;

use App\Models\Frontend\GeneralModel;
use App\Models\Frontend\Api\Mobile\PushNotificationsModel;

class PushNotifications {

	protected $table;
	protected $baseUrl;
	protected $general;
	protected $PushNotificationsModel;

	public function __construct() {
		$this->table = 'push_notification_tokens';
		$this->baseUrl = 'https://exp.host/--/api/v2/push/send';
		$this->general = new GeneralModel();
		$this->PushNotificationsModel = new PushNotificationsModel();
	}

	public function index(string $title, string $body = NULL) {
		$sql = $this->PushNotificationsModel->pushNotificationListModel();
		if (isNotNull($sql)) {
			foreach ($sql as $row) {

				// Api
				$payload = [
					'to' => $row->token,
					'sound' => 'default',
					'title' => $title,
					'body' => isNotNull($body) ? $body : ''
				];

				$curl = curl_init();

				curl_setopt_array($curl, [
					CURLOPT_URL => $this->baseUrl,
					CURLOPT_RETURNTRANSFER => TRUE,
					CURLOPT_ENCODING => '',
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 30,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => 'POST',
					CURLOPT_POSTFIELDS => json_encode($payload),
					CURLOPT_HTTPHEADER => [
						'Accept: application/json',
						'Accept-Encoding: gzip, deflate',
						'Content-Type: application/json',
						'cache-control: no-cache',
						'host: exp.host'
					]
				]);

				$response = curl_exec($curl);
				$err = curl_error($curl);

				curl_close($curl);

				if ($err) {

					// Error Update
					$this->general->updateModel($this->table, ['error' => $err], ['token' => $row->token]);

				} else {

					// Error Update
					$result = json_decode($response, TRUE);
					if (isNotNull($result)) {
						if ($result['data']['status'] == 'error') {
							$this->general->updateModel($this->table, ['error' => $result['data']['message']], ['token' => $row->token]);
						}
					}

				}

			}
		}
	}
}
