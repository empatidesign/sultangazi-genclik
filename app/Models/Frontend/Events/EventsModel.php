<?php
namespace App\Models\Frontend\Events;
use CodeIgniter\Model;

class EventsModel extends Model {

	var $table = 'events';
	var $tableLang = 'events_lang';
	var $tableCategoryLang = 'events_category_lang';

	public function eventsListModel(int $lang_id, string $date = NULL, int $page_start = NULL, int $per_page = NULL) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.event_id,
						'.$this->table.'.event_date,
						'.$this->table.'.event_hour,
						'.$this->table.'.event_location,
						'.$this->table.'.event_image,
						'.$this->tableLang.'.event_name,
						'.$this->tableLang.'.event_slug,
						'.$this->tableCategoryLang.'.event_category_name');
		$query->join($this->tableLang, $this->tableLang.'.event_id = '.$this->table.'.event_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');
		$query->join($this->tableCategoryLang, $this->tableCategoryLang.'.event_category_id = '.$this->table.'.event_category_id AND '.$this->tableCategoryLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableLang.'.event_name !=', '');

		if (isNotNull($date)) {
			$query->where($this->table.'.event_date', $date);
		}

		$query->orderBy($this->table.'.event_date', 'DESC');

		if (isNotNull($per_page)) {
			$query->limit($per_page, $page_start);
		}

		return $query->get()->getResult();
	}

	public function eventsInfoModel(string $slug, int $event_id, int $lang_id) {
		$appDb = db_connect('application');
		$query = $appDb->table($this->table);
		$query->select($this->table.'.*,
						'.$this->tableLang.'.event_name,
						'.$this->tableLang.'.event_age_group,
						'.$this->tableLang.'.event_quota,
						'.$this->tableLang.'.event_description,
						'.$this->tableLang.'.event_meta_title,
						'.$this->tableLang.'.event_meta_keywords,
						'.$this->tableLang.'.event_meta_description');
		$query->join($this->tableLang, $this->tableLang.'.event_id = '.$this->table.'.event_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->table.'.event_id', $event_id);
		$query->where($this->tableLang.'.event_slug', $slug);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function otherEventsListModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.event_id,
						'.$this->table.'.event_date,
						'.$this->table.'.event_hour,
						'.$this->table.'.event_location,
						'.$this->table.'.event_image,
						'.$this->tableLang.'.event_name,
						'.$this->tableLang.'.event_slug,
						'.$this->tableCategoryLang.'.event_category_name');
		$query->join($this->tableLang, $this->tableLang.'.event_id = '.$this->table.'.event_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');
		$query->join($this->tableCategoryLang, $this->tableCategoryLang.'.event_category_id = '.$this->table.'.event_category_id AND '.$this->tableCategoryLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableLang.'.event_name !=', '');
		$query->orderBy($this->table.'.event_date', 'DESC');
		$query->limit(6);

		return $query->get()->getResult();
	}
}
