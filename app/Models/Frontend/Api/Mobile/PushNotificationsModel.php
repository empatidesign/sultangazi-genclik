<?php
namespace App\Models\Frontend\Api\Mobile;
use CodeIgniter\Model;

class PushNotificationsModel extends Model {

	var $table = 'push_notification_tokens';
	var $tableNews = 'news';
	var $tableNewsLang = 'news_lang';
	var $tableAnnouncements = 'announcements';
	var $tableAnnouncementsLang = 'announcements_lang';
	var $tableEvents = 'events';
	var $tableEventsLang = 'events_lang';

	public function pushNotificationInfoModel(string $token) {
		$query = $this->db->table($this->table);
		$query->select('status');

		$query->where('token', $token);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function newsModel(int $lang_id) {
		$query = $this->db->table($this->tableNews);
		$query->select($this->tableNews.'.news_id,
						'.$this->tableNewsLang.'.news_name');
		$query->join($this->tableNewsLang, $this->tableNewsLang.'.news_id = '.$this->tableNews.'.news_id AND '.$this->tableNewsLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->tableNews.'.push_notification', FORM_CHECKBOX_VALUE_NUMBER);
		$query->orderBy($this->tableNews.'.news_created_date', 'DESC');

		return $query->get()->getResult();
	}

	public function announcementsModel(int $lang_id) {
		$query = $this->db->table($this->tableAnnouncements);
		$query->select($this->tableAnnouncements.'.announcement_id,
						'.$this->tableAnnouncementsLang.'.announcement_name');
		$query->join($this->tableAnnouncementsLang, $this->tableAnnouncementsLang.'.announcement_id = '.$this->tableAnnouncements.'.announcement_id AND '.$this->tableAnnouncementsLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->tableAnnouncements.'.push_notification', FORM_CHECKBOX_VALUE_NUMBER);
		$query->orderBy($this->tableAnnouncements.'.announcement_created_date', 'DESC');

		return $query->get()->getResult();
	}

	public function eventsModel(int $lang_id) {
		$query = $this->db->table($this->tableEvents);
		$query->select($this->tableEvents.'.event_id,
						'.$this->tableEvents.'.event_date,
						'.$this->tableEvents.'.event_hour,
						'.$this->tableEventsLang.'.event_name');
		$query->join($this->tableEventsLang, $this->tableEventsLang.'.event_id = '.$this->tableEvents.'.event_id AND '.$this->tableEventsLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->tableEvents.'.push_notification', FORM_CHECKBOX_VALUE_NUMBER);
		$query->orderBy($this->tableEvents.'.event_created_date', 'DESC');

		return $query->get()->getResult();
	}
}
