<?php
namespace App\Controllers\Frontend\Api\Mobile;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Api\Mobile\PushNotificationsModel;

class PushNotifications extends BaseController {

  protected $table;
  protected $tableNews;
  protected $tableAnnouncements;
  protected $tableEvents;
  protected $baseUrl;
  protected $PushNotificationsModel;

  public function __construct() {
    $this->table = 'push_notification_tokens';
    $this->tableNews = 'news';
    $this->tableAnnouncements = 'announcements';
    $this->tableEvents = 'events';
    $this->baseUrl = 'https://exp.host/--/api/v2/push/send';
    $this->PushNotificationsModel = new PushNotificationsModel();
	}

  public function index() {
    if (strtoupper($this->request->getMethod()) === 'POST') {

      // POST
      $token = (string) trim($this->request->getPost('token'));
      if (isNotNull($token)) {

        $sql = $this->PushNotificationsModel->pushNotificationInfoModel($token);
        if (isNotNull($sql)) {

          // Update
          if ($sql->status == 1) {
            $this->general->updateModel($this->table, ['status' => 0, 'updated_date' => nowDate()], ['token' => $token]);

            // Return
            $return = FALSE;
          } else {
            $this->general->updateModel($this->table, ['status' => 1, 'updated_date' => nowDate()], ['token' => $token]);

            // Return
            $return = TRUE;
          }

        } else {

          // Insert
          $this->general->insertModel($this->table, ['token' => $token, 'status' => 1, 'created_date' => nowDate()]);

          // Return
          $return = TRUE;

        }

        /**********************************************/

        if ($return == TRUE) {

          // News
          $news_array = [];
          $news_sql = $this->PushNotificationsModel->newsModel($this->defaultLangId);
          if (isNotNull($news_sql)) {
            foreach ($news_sql as $row) {
              $news_array[] = [
                'id' => $row->news_id,
                'type' => 'news',
                'title' => strip_tags($row->news_name),
                'body' => ''
              ];
            }
          }

          // Announcements
          $announcements_array = [];
          $announcements_sql = $this->PushNotificationsModel->announcementsModel($this->defaultLangId);
          if (isNotNull($announcements_sql)) {
            foreach ($announcements_sql as $row) {
              $announcements_array[] = [
                'id' => $row->announcement_id,
                'type' => 'announcements',
                'title' => strip_tags($row->announcement_name),
                'body' => ''
              ];
            }
          }

          // Events
          $events_array = [];
          $events_sql = $this->PushNotificationsModel->eventsModel($this->defaultLangId);
          if (isNotNull($events_sql)) {
            foreach ($events_sql as $row) {
              $event_date = isNotNull($row->event_date) && $row->event_date != '0000-00-00' ? dateFormat($row->event_date, 'd/m/Y') : NULL;
              $event_hour = isNotNull($row->event_hour) && $row->event_hour != '00:00:00' ? dateFormat($row->event_hour, 'H:i') : NULL;

              $events_array[] = [
                'id' => $row->event_id,
                'type' => 'events',
                'title' => strip_tags($row->event_name),
                'body' => $event_date.' - '.$event_hour
              ];
            }
          }

          $payload = array_merge_recursive($news_array, $announcements_array, $events_array);

          /**********************************************/

          if (isNotNull($payload) && is_array($payload)) {
            foreach ($payload as $value) {

              // Api
              $payload = [
                'to' => $token,
                'sound' => 'default',
                'title' => $value['title'],
                'body' => isNotNull($value['body']) ? $value['body'] : ''
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
                $this->general->updateModel($this->table, ['error' => $err], ['token' => $token]);

              } else {

                // Checkbox Status Update
                if ($value['type'] == 'news') {
                  $this->general->updateModel($this->tableNews, ['push_notification' => 0], ['news_id' => $value['id']]);
                } else if ($value['type'] == 'announcements') {
                  $this->general->updateModel($this->tableAnnouncements, ['push_notification' => 0], ['announcement_id' => $value['id']]);
                } else if ($value['type'] == 'events') {
                  $this->general->updateModel($this->tableEvents, ['push_notification' => 0], ['event_id' => $value['id']]);
                }

                // Error Update
                $result = json_decode($response, TRUE);
                if (isNotNull($result)) {
                  if ($result['data']['status'] == 'error') {
                    $this->general->updateModel($this->table, ['error' => $result['data']['message']], ['token' => $token]);
                  }
                }
              }

            }
          }

        }

      } else {
        echo lang('Web.error.token');
      }

    } else {
      echo lang('Web.error.post');
    }
  }
}
